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
		$persons = $wpdb->get_results('SELECT ID, wp_user_id, person_fullname, person_email_notification FROM '. SPLAN_PERSONS);

		foreach($persons as $person){
			$total_client_hours = 0;
			//If Email Notification Enable
			if($person->person_email_notification == 1){
				// $query = $wpdb->prepare(');
				echo '<h2>'. $person->person_fullname . "</h2><br />";
				$total_client_hours = 0;
				$client_list_array = array();

				// $timesheet_month_stats = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$person_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('31/$month_number/$year', '%d/%m/%Y')"); 	
				$timesheets = $wpdb->get_results('SELECT SUM(TIME_TO_SEC(task_hour)/3600) as totalhours, task_label FROM '.SPLAN_TIMESHEET.' WHERE task_person = "'.$person->person_fullname.'" AND STR_TO_DATE(date_now, "%d/%m/%Y") BETWEEN STR_TO_DATE("01/'.$month.'/'.$year.'", "%d/%m/%Y") AND STR_TO_DATE("31/'.$month.'/'.$year.'", "%d/%m/%Y") GROUP BY  task_label');

				foreach($timesheets as $timesheet){
				// 	// echo round($timesheet->totalhours, 2) . " - " . $timesheet->task_label . "<br>";
					array_push($client_list_array, array('clientname' => $timesheet->task_label, 'total_hours' =>  round($timesheet->totalhours, 2) ));

					$total_client_hours += round($timesheet->totalhours, 2);
				}

			$insert_invoice_table = $wpdb->insert(
					SPLAN_TIMESHEET_INVOICE,
					array(
						'person_id' 				=> $person->wp_user_id,
						'clients_invoices_table'	=> serialize($client_list_array),
						'date'						=> $month . '-' . $year,
						'total_hours'				=> $total_client_hours,
						'active_viewing'			=> 1,
						'person_approval'			=> 0,
						'admin_approval'			=> 0
					),
					array(
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%d',		
						'%d'								
					)
				);
				$wpdb->show_errors();
				$wpdb->print_error();
				if($insert_invoice_table == 1){
					$body = '
					<h1>Hello '.$person->person_fullname.',</h1>
					<p>Your Invoice for the last month is now available for viewing</p>
					<p><a href="http://dplan.seowebsolutions.com/" target="_blank">Log In Here to Dplan</a></p>
					';

					$to = $person->person_email;
					$subject = 'Splan Invoice Reminder';
					$headers = array('Content-Type: text/html; charset=UTF-8');
					 
					// $email_status = wp_mail( $to, $subject, $body, $headers );
					
				}else{
					print_r('NOT SAVE');
				
				}
			// echo '<pre>';
			// print_r($arry);
			// echo '</pre>';				
			}


	



		}		
	}


?>