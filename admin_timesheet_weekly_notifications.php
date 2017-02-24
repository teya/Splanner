<?php 
require( dirname( __FILE__ ) . '/wp-blog-header.php' );
$persons_table = $wpdb->prefix . "custom_person";
$table_name_timesheet = $wpdb->prefix . 'custom_timesheet';

$timezone = get_option('timezone_string'); 
date_default_timezone_set($timezone);

$persons = $wpdb->get_results('SELECT * FROM '.$persons_table .' WHERE ID != 2');

$person_lists = array();

foreach($persons as $person){
	// if($person->person_email_notification == 1){
		/* empty task */
		$person_total_working_hours = 0;
		$end_date = date("Y-m-d", strtotime("last friday"));
		$list_empty_dates = '';
		// $yesterday = date("d/m/Y", strtotime("yesterday"));
		$date = date_create($end_date);
		date_sub($date,date_interval_create_from_date_string("5 days"));
		$start_date = date_format($date, "Y-m-d");
		$date_range = date_range($start_date, $end_date);

		$persons = $wpdb->get_results("SELECT * FROM {$table_name_person}");
		$current_person = $wpdb->get_row("SELECT * FROM {$persons_table} WHERE wp_user_id = ".$person->wp_user_id);

		$empty_dates_array = array();
		$insufficient_hours_dates_array = array();
		$insufficient_hours_dates_list = '';
		$list_empty_dates = '';

		foreach($date_range as $date){
			$explode_date = explode('/', $date);
			$day = $explode_date[0];
			$month = $explode_date[1];
			$year = $explode_date[2];
			$date_format = $year."/".$month."/".$day;
			$day_number = date('w', strtotime($date_format));					
			if($day_number != 0 && $day_number != 6){
				$timesheet_empty_days = $wpdb->get_row("SELECT ROUND(SUM(time_to_sec(task_hour) / (60 * 60)), 2) as total_hours FROM {$table_name_timesheet} WHERE task_person = '$person->person_fullname' AND date_now = '$date'");			
				if($timesheet_empty_days->total_hours == 0){
					$empty_dates_array[] = $date;			
				}else{
					if($timesheet_empty_days->total_hours < $current_person->person_hours_per_day){
						$insufficient_hours_dates_array[] = $date .'-'. $timesheet_empty_days->total_hours;	
						$person_total_working_hours = $person_total_working_hours + $timesheet_empty_days->total_hours;		
					}
				}
			}
		}


		if(!empty($empty_dates_array) || $person_total_working_hours > 1){

			if(!empty($empty_dates_array)){
				$list_empty_dates = '<ul>';
				$days_count = 0;
				foreach($empty_dates_array as $date_empty){
					$days_count++;
					$date = DateTime::createFromFormat('d/m/Y', $date_empty);
					$list_empty_dates .= '<li>'. $date->format('M d, Y') .'</li>';
				}
				$list_empty_dates .="</ul>";
			}

			if(!empty($insufficient_hours_dates_array)){
				$insufficient_hours_dates_list = '<ul>';
				$count_insufficient_hours_dates_list = 0;
				foreach($insufficient_hours_dates_array as $day){
				$count_insufficient_hours_dates_lis++;
				$string = explode("-", $day);
				$date = DateTime::createFromFormat('d/m/Y', $string[0]);
					$insufficient_hours_dates_list .= '<li>'. $date->format('M d, Y') .' / '.substr(convertTime($string[1]),0,-3).' Hours Only.</li>';
				}
				$insufficient_hours_dates_list .= '</ul>';
			}
			array_push($person_lists, array('person' => $person->person_fullname, 'empty_dates' => $list_empty_dates, 'insufficient_hours_day' => $insufficient_hours_dates_list, 'total work hours' => $current_person->person_hours_per_day));	
		}

	// }
}

	$email_string = "";
	$person_count = 0;
	foreach($person_lists as $list){
		if($list['empty_dates'] != '' || $list['insufficient_hours_day'] != ''){
			$person_count++;
			$email_string .= '<h3>'.$list['person'] . '</h3>';
			if($list['empty_dates'] != ''){
				$email_string .= 'Empty Timesheet day/s';
				$email_string .= $list['empty_dates'];
			}
			if($list['insufficient_hours_day'] != ''){
				$email_string .= 'Insuficient Hours day/s';
				$email_string .= $list['insufficient_hours_day'];
			}
			$email_string .= '<hr/>';
		}
	}


		$admin_info = $wpdb->get_row('SELECT * FROM '.$persons_table .' WHERE ID = 2');


		if($email_string != ''){
			$body = '
			<h1>Dear '.$admin_info->person_fullname.',</h1>
			<p>There are '.$person_count.' persons did not complete timesheet or atleast did not complete the total working hours for a day.</p>
			<p>Here are the list:</p>	
			'.$email_string.'
			<br />
			<p>Thank you and have a good day.</p>
			<p>Regards,<br />
			Gray</p>
			<p><a href="http://admin.seowebsolutions.com/" target="_blank">Login to Splan Here Now!</a></p>
			<br />';
			$to = $admin_info->person_email;
			// $to ='gray.greecos@gmail.com';
			$admin_email = get_option( 'admin_email' ); 
			$subject = 'Admin Splan Timesheet Reminder';
			$headers = array('Content-Type: text/html; charset=UTF-8','From: Splan <'.$admin_email.'>');

			$email_status = wp_mail( $to, $subject, $body, $headers );
			echo $email_status;



		}
?>