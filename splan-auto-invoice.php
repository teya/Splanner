<?php 
	require( dirname( __FILE__ ) . '/wp-blog-header.php' );

	//Define tablename
	define("SPLAN_PERSONS", $wpdb->prefix . "custom_person");
	define("SPLAN_TIMESHEET", $wpdb->prefix . "custom_timesheet");
	define("SPLAN_TIMESHEET_INVOICE", $wpdb->prefix . "custom_invoice_table");

	//Get Wordpress Setting Timezone optuin value.
	$timezone = get_option('timezone_string'); 
	date_default_timezone_set($timezone);

	$date_today_string = date('M-d-Y');
	$day_today_string = strtotime($date_today_string);

	$today_today =  date("d", $day_today_string);

	$today_today = '18';

	if($today_today == '18'){

		//Get last month date.
		$last_month_date = date('Y-m-d', strtotime("-1 month"));

		$month =  date("m", strtotime($last_month_date));
		$year =  date("Y", strtotime($last_month_date));

		
		// Get All Person
		$persons = $wpdb->get_results('SELECT ID, wp_user_id, person_hours_per_day, person_fullname, person_email_notification, person_monthly_rate FROM '. SPLAN_PERSONS . ' WHERE wp_user_id NOT IN (2)');
		
		foreach($persons as $person){
			$total_client_hours = 0;
			$total_holiday_hours = 0;
			$total_sickness_hours = 0;
			$total_electric_hours = 0;
			$total_non_working_hours = 0;
			//If Email Notification Enable
			if($person->person_email_notification == 1){

				echo '<h2>'. $person->person_fullname . "</h2><br />";
				$total_client_hours = 0;
				$client_list_array = array();
	
				$timesheets = $wpdb->get_results('SELECT SUM(IF(task_name = "holiday", TIME_TO_SEC(task_hour)/3600, 0 )) as holiday, SUM(IF(task_name = "sickness", TIME_TO_SEC(task_hour)/3600, 0 )) as sickness, SUM(IF(task_name = "electric / internet problems", TIME_TO_SEC(task_hour)/3600, 0 )) as electric, SUM(TIME_TO_SEC(task_hour)/3600) as totalhours, task_label FROM '.SPLAN_TIMESHEET.' WHERE task_person = "'.$person->person_fullname.'" AND STR_TO_DATE(date_now, "%d/%m/%Y") BETWEEN STR_TO_DATE("01/'.$month.'/'.$year.'", "%d/%m/%Y") AND STR_TO_DATE("31/'.$month.'/'.$year.'", "%d/%m/%Y") GROUP BY  task_label');


				foreach($timesheets as $timesheet){
				// 	// echo round($timesheet->totalhours, 2) . " - " . $timesheet->task_label . "<br>";
					$total_holiday_hours += $timesheet->holiday;
					$total_sickness_hours += $timesheet->sickness;
					$total_electric_hours += $timesheet->electric;


					array_push($client_list_array, array('clientname' => $timesheet->task_label, 'total_hours' =>  round($timesheet->totalhours, 2), 'price' => '', 'total' => ''));
					$total_client_hours += round($timesheet->totalhours, 2);
				}

				$total_non_working_hours = $total_holiday_hours + $total_sickness_hours + $total_electric_hours;

				if($total_non_working_hours > 0){

					foreach($client_list_array as $key => $value){
						if($client_list_array[$key]['clientname'] == 'SEOWeb Solutions'){
							$client_list_array[$key]['total_hours'] = round($client_list_array[$key]['total_hours'] - $total_non_working_hours, 2);
						}
					}

					if($total_holiday_hours > 0){
						array_push($client_list_array, array('clientname' => 'Holidays', 'total_hours' =>  round($total_holiday_hours, 2), 'price' => '', 'total' => '' ));
					}
					if($total_sickness_hours > 0){
						array_push($client_list_array, array('clientname' => 'Sickness', 'total_hours' =>  round($total_sickness_hours, 2), 'price' => '', 'total' => '' ));
					}
					if($total_electric_hours > 0){
						array_push($client_list_array, array('clientname' => 'Electric & Internet Problem', 'total_hours' =>  round($total_electric_hours, 2), 'price' => '', 'total' => '' ));
					}
				}

				$total_working_days = countDays($year, $month, array(0, 6));

				$person_total_hr = $person->person_hours_per_day * $total_working_days; 

				//Salary Deduction if current hour not sufficient.
				if($person_total_hr > $total_client_hours){
					$salary_per_day = $person->person_monthly_rate / $total_working_days;
					$salary_per_hr = $salary_per_day / $person->person_hours_per_day;
					$remaining_hrs = $person_total_hr - $total_client_hours;
					$salary_deduction = $remaining_hrs * $salary_per_hr;
					$total_salary = $person->person_monthly_rate - $salary_deduction;
				}else{
					$total_salary = $person->person_monthly_rate;
				}

				$insert_invoice_table = $wpdb->insert(
						SPLAN_TIMESHEET_INVOICE,
						array(
							'person_id' 				=> $person->wp_user_id,
							'clients_invoices_table'	=> serialize($client_list_array),
							'date'						=> $month . '-' . $year,
							'active_viewing'			=> 1,
							'person_approval'			=> 0,
							'admin_approval'			=> 0,
							'status'					=> 'Reviewing',
							'total_hours'				=> $total_client_hours,
							'person_total_hr'			=> $person_total_hr,
							'non_working_hrs'			=> $total_non_working_hours,
							'salary'					=> $total_salary
						),
						array(
							'%s',
							'%s',
							'%s',
							'%d',
							'%d',
							'%d',		
							'%s',
							'%s',
							'%d',
							'%d',
							'%d'								
						)
					);

				// $wpdb->show_errors();
				// $wpdb->print_error();
				$dateObj   = DateTime::createFromFormat('!m', $month);
				$monthName = $dateObj->format('F');
				
				if($insert_invoice_table == 1){
					$body = '
					<h1>Hello '.$person->person_fullname.',</h1>
					<p>Your Invoice for the month of '. $monthName . '-' . $year .' is now available for viewing</p>
					<p><a href="http://admin.seowebsolutions.com/" target="_blank">Log In Here to Splan</a></p>
					';

					$user = get_user_by( 'ID', $person->wp_user_id );

					$to = $user->user_email;
					$subject = 'Splan Invoice Reminder';
					$headers = array('Content-Type: text/html; charset=UTF-8','From: Splan Auto Invoice <info@seowebsolutions.se');
					 
					$email_status = wp_mail( $to, $subject, $body, $headers );
					$email_message = ($email_status == 1)? 'Success Email Sent' : 'Failed Email Send';
					print_r($user->user_email . '<br />');
					print_r($email_message);
					
				}else{
					print_r('NOT SAVE');
				
				}			
			}
		}		
	}


?>