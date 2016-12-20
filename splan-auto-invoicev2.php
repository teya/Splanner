<?php 
	require( dirname( __FILE__ ) . '/wp-blog-header.php' );

	//Define tablename
	define("SPLAN_PERSONS", $wpdb->prefix . "custom_person");
	define("SPLAN_TIMESHEET", $wpdb->prefix . "custom_timesheet");

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
				// $timesheet_month_stats = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$person_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('31/$month_number/$year', '%d/%m/%Y')"); 	
				$timesheets = $wpdb->get_results('SELECT SUM(TIME_TO_SEC(task_hour)/3600) as totalhours, task_label FROM '.SPLAN_TIMESHEET.' WHERE task_person = "'.$person->person_fullname.'" AND STR_TO_DATE(date_now, "%d/%m/%Y") BETWEEN STR_TO_DATE("21/11/2016", "%d/%m/%Y") AND STR_TO_DATE("20/12/2016", "%d/%m/%Y") GROUP BY  task_label');
				foreach($timesheets as $timesheet){
					echo round($timesheet->totalhours, 2) . " - " . $timesheet->task_label . "<br>";
					$total_client_hours += round($timesheet->totalhours, 2);
				}
				echo 'Total Hours - '. $total_client_hours .'<br />';
			}
			echo "<br /><br />";
		}		
	}


?>