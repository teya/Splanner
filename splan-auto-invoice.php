<?php 
	require( dirname( __FILE__ ) . '/wp-blog-header.php' );
	//Load PHPExcel
	require( dirname( __FILE__ ) . '/Classes/PHPExcel.php' );
	require( dirname( __FILE__ ) . '/Classes/PHPExcel/IOFactory.php' );
	require( dirname( __FILE__ ) . '/Classes/PHPExcel/Writer/Excel2007.php' );

	//Define tablename
	define("SPLAN_PERSONS", $wpdb->prefix . "custom_person");
	define("SPLAN_TIMESHEET", $wpdb->prefix . "custom_timesheet");

	//Get Wordpress Setting Timezone optuin value.
	$timezone = get_option('timezone_string'); 
	date_default_timezone_set($timezone);

	$date_today_string = date('M-d-Y');
	$day_today_string = strtotime($date_today_string);

	$today_today =  date("d", $day_today_string);
	// $today_today = '18';

	// if($today_today == '18'){


		$fileType = 'Excel2007';
		//Get last month date.
		$last_month_date = date('Y-m-d', strtotime("-1 month"));

		$month =  date("m", strtotime($last_month_date));
		$year =  date("Y", strtotime($last_month_date));

		
		// Get All Person
		$persons = $wpdb->get_results('SELECT ID, wp_user_id, person_fullname, person_email_notification, person_email, person_address FROM '. SPLAN_PERSONS);

		foreach($persons as $person){
			$total_client_hours = 0;
			//If Email Notification Enable
			if($person->person_email_notification == 1){

				//Load the Excel Template
				$fileName = 'persons_invoice/auto-invoice-template.xlsx';
				$objPHPExcel = PHPExcel_IOFactory::createReader($fileType);
				$objPHPExcel = $objPHPExcel->load($fileName);		
				$total_client_hours = 0;
				$person_address = str_replace("\r\n", "", $person->person_address);

				$objPHPExcel->getActiveSheet()->setCellValue('C3', $person->person_fullname); //Print Person Name to excel
				$objPHPExcel->getActiveSheet()->setCellValue('F6', 'Paypal email:  '.$person->person_email); //Print Person Email to excel
				$objPHPExcel->getActiveSheet()->setCellValue('C4', $person_address); //Print Person Address to excel
				$objPHPExcel->getActiveSheet()->setCellValue('J9', $date_today_string); //Print today's Date
				$objPHPExcel->getActiveSheet()->setCellValue('C5', 'Contact Number:  '.$person->person_mobile);
	
				//Get All total last month timesheet.
				$timesheets = $wpdb->get_results('SELECT SUM(TIME_TO_SEC(task_hour)/3600) as totalhours, task_label FROM '.SPLAN_TIMESHEET.' WHERE task_person = "'.$person->person_fullname.'" AND STR_TO_DATE(date_now, "%d/%m/%Y") BETWEEN STR_TO_DATE("01/'.$month.'/'.$year.'", "%d/%m/%Y") AND STR_TO_DATE("31/'.$month.'/'.$year.'", "%d/%m/%Y") GROUP BY  task_label');

				if(!empty($timesheets)){
					//Loop Each client
					$start_column = 13; 
					foreach($timesheets as $timesheet){
						$client_column_number = 'C'.$start_column;
						$client_column_total_hours_number = 'G'.$start_column;
						$client_total_hours = round($timesheet->totalhours, 2);
						$objPHPExcel->getActiveSheet()->setCellValue($client_column_number, $timesheet->task_label);//Client Name
						$objPHPExcel->getActiveSheet()->setCellValue($client_column_total_hours_number, $client_total_hours); // Client Total Hours
		
						//Add All clients total hours
						$total_client_hours += round($timesheet->totalhours, 2);
						$start_column++;
					}
					// echo 'Total Hours - '. $total_client_hours .'<br />';
					$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

					$time = strtotime($last_month_date);

					$last_month = date("M",$time);
					$last_month_year = date("Y",$time);

					$filename = 'persons_invoice/'.$person->person_fullname.'-'.$last_month.'-'.$last_month_year.'-invoice.xlsx';

					$objWriter->save($filename);


					//Email Template
					$body = '
					<h1>Dear '.$person->person_fullname.',</h1>
					<p>Here is your Invoice for '.$last_month.'-'.$last_month_year.'</p>
					<br />
					<p>Please Check the attach PDF file.</p>
					<p>Thank you and have a good day.</p>
					<p>Regards,<br />
					Gray</p>
					<br />
					';

					//Email Header Information.
					$to = $person->person_email;
					$admin_email = get_option( 'admin_email' ); 
					$subject = 'Splan Monthly Invoice';
					$headers = array('Content-Type: text/html; charset=UTF-8','From: Splan <'.$admin_email.'>');

					$attachment = array( dirname( __FILE__ ) .'/'. $filename);
					 
					$email_status = wp_mail( $to, $subject, $body, $headers, $attachment );

					echo $email_status . '<br />';
				}

			}
		}		
	// }


?>