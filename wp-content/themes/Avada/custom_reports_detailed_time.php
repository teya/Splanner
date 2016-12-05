<?php /* Template Name: Reports Detailed Time */ ?>
<?php get_header(); ?>
<?php 
	$table_name_color = $wpdb->prefix . "custom_project_color";
	$projects = $wpdb->get_results("SELECT * FROM {$table_name_color}");
	$table_name_client = $wpdb->prefix . "custom_client";
	$clients = $wpdb->get_results("SELECT * FROM {$table_name_client}");
	$table_name_person = $wpdb->prefix . "custom_person";
	$persons = $wpdb->get_results("SELECT * FROM {$table_name_person} WHERE person_status='0'");
?>
<div class="detailed_time">
	<div class="top_navi">
		<div class="left">
			<?php 
				$week_number = date('W');
				$month_number = date('m');				
				$month_name = date('F');
				$year = date('Y');
			?>
			<div class="timeframe_navigation">
				<form id="filter_details">
					<input type="hidden" class="current_week" value="<?php echo $week_number; ?>" />
					<input type="hidden" class="current_month" value="<?php echo $month_number; ?>" />
					<input type="hidden" class="current_year" value="<?php echo $year; ?>" />
					<input type="hidden" class="filter_current_month" value="<?php echo $month_number; ?>" />
					<input type="hidden" class="filter_current_week" value="<?php echo $week_number; ?>" />							
					<input type="hidden" class="filter_current_year" value="<?php echo $year; ?>" />							
				</form>
				<div class="detailed_time_nav_week">
					<div class="button_2"><div class="detailed_time_nav_previous"></div></div>
					<div class="button_2"><div class="detailed_time_nav_next"></div></div>
				</div>
				<div style="display:none;" class="detailed_time_nav_month">
					<div class="button_2"><div class="detailed_time_nav_previous"></div></div>
					<div class="button_2"><div class="detailed_time_nav_next"></div></div>
				</div>				
			</div>
			<?php
				$week = getStartAndEndDate($week_number, $year);
				$start_num = $week[0];
				$end_num = $week[1];
				$start = date("d M Y", strtotime($start_num));
				$end = date("d M Y", strtotime($end_num));
			?>
			<div class="report_top_label"><h1><?php echo "Week " . $week_number . " : " . $start . "-" . $end; ?></h1></div>
		</div>
		<div class="right">			
			<select id="detailed_time_custom_filter">
				<option>Week</option>
				<option>Month</option>				
				<option>Custom</option>				
			</select>
			<div style="display: none;" class="loader custom_filter_loader"></div>
			<div style="display:none;" class="custom_date_filter">
				<div class="one_half">
					<select class="project_name" name="project_name">
						<option>Any Project</option>
						<?php 
						$project_name_array = array();
						foreach($projects as $project){
							$project_name_array[] = $project->project_category;
						}
						sort($project_name_array);
						foreach($project_name_array as $project_name){						
						?>
						<option><?php echo $project_name; ?></option>	
						<?php } ?>
					</select>
					<select class="client_name" name="client_name">
						<option>Any Client</option>
						<?php 
							$client_name_array = array();
							foreach($clients as $client){
								$client_name_array[] = $client->client_name;
							}
							sort($client_name_array);
							foreach($client_name_array as $client_name){ 
						?>
							<option><?php echo $client_name; ?></option>	
						<?php } ?>
					</select>
					<select class="person_name" name="person_name">
						<option>Any Person</option>
						<?php 
							$person_name_array = array();
							foreach($persons as $person){
								$person_name_array[] = $person->person_fullname;
							}
							sort($person_name_array);
							foreach($person_name_array as $person_name){
						?>
							<option><?php echo $person_name; ?></option>	
						<?php } ?>
					</select>
				</div>
				<div class="one_half last">
					<div class="filter_date_inputs">
						<div class="filter_date_container">
							<p class="label">From:</p>
							<input type="text" class="from_date" />
						</div>
						<div class="filter_date_container">
							<p class="label">To:</p>
							<input type="text" class="to_date" />
						</div>
					</div>
					<div class="button_2"><div class="custom_date_filter_go"></div></div>					
				</div>
			</div>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="header_titles">
		<div class="first_column column">
			<p class="table_header">Client</p>
		</div>
		<div class="column_group">
			<div class="second_column column">
				<p class="table_header">Project</p>
			</div>
			<div class="third_column column">
				<p class="table_header">Task</p>
			</div>
			<div class="fourth_column column">
				<p class="table_header">Person</p>
			</div>
			<div class="fifth_column column">
				<p class="table_header">Hours</p>
			</div>
		</div>
	</div>
	<?php 		
		$week_start = date("d/m/Y", strtotime($start_num));
		$week_end = date("d/m/Y", strtotime($end_num));
		global $wpdb;
		$week = date('W');
		$table_name = $wpdb->prefix . "custom_timesheet";
		$filter = "STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('$week_start', '%d/%m/%Y') AND STR_TO_DATE('$week_end', '%d/%m/%Y') AND week_number = '$week'";
		$timesheets = $wpdb->get_results("SELECT * FROM {$table_name} WHERE $filter ORDER BY STR_TO_DATE(date_now, '%d/%m/%Y') ASC");
		
		$total_date_now_array = array();
		foreach($timesheets as $timesheet){
			$total_date_now = $timesheet->date_now;
			$total_date_now_array[] = $total_date_now;
		}
		$total_task_dates = array_unique($total_date_now_array);
		foreach($total_task_dates as $total_task_date){ 
			$total_timesheets = $wpdb->get_results("SELECT * FROM {$table_name} WHERE date_now = '$total_task_date'");
			$total_day_hours_total = "";
			foreach($total_timesheets as $total_timesheet){
				$total_task_hour = $total_timesheet->task_hour;
				$total_task_hour_decimal 	= round(decimalHours($total_task_hour), 2);
				$total_day_hours_total	+= $total_task_hour_decimal;				
			}
			$total_hours += $total_day_hours_total;
		}
	?>
	<h3 class="detailed_total_hours" style="float: left; margin-bottom: 5px;">Total Hours: <?php echo round_quarter($total_hours); ?></h3>
	<div class="detailed_time_details">
		<?php
			$date_now_array = array();
			foreach($timesheets as $timesheet){
				$date_now = $timesheet->date_now;
				$date_now_array[] = $date_now;
			}
			$task_dates = array_unique($date_now_array);
			foreach($task_dates as $task_date){ 
				$timesheets = $wpdb->get_results("SELECT * FROM {$table_name} WHERE date_now = '$task_date'");
				$total_day_hours = "";
				foreach($timesheets as $timesheet){
					$task_hour = $timesheet->task_hour;
					$task_hour_decimal 	= round(decimalHours($task_hour), 2);
					$total_day_hours	+= $task_hour_decimal;
					$task_description = $timesheet->task_description;				
				}				
			?>
				
				<div class="date_header"><p><?php echo $task_date; ?></p><p class="total_day_hour"><?php echo round_quarter($total_day_hours); ?></p></div>
			<?php				
				foreach($timesheets as $timesheet){
					$task_id = $timesheet->ID;
					$client_name = $timesheet->task_label;
					$project_name = $timesheet->task_project_name;
					$task_suffix = $timesheet->task_suffix;
					if($task_suffix != null){
					$task_name = format_task_name($timesheet->task_name) ." - ". $task_suffix;
					}else{
					$task_name = format_task_name($timesheet->task_name);
					}
					$person_name = $timesheet->task_person;
					$task_hour = $timesheet->task_hour;
					$task_hour_decimal 	= round(decimalHours($task_hour), 2);
					$task_description = $timesheet->task_description;
					$tasks_done_today_serialize = $timesheet->task_done_today;
					$tasks_done_today = unserialize($tasks_done_today_serialize);
					?>						
						<div class="info_div">						
							<div id="client_name_<?php echo $task_id; ?>" class="first_column column edit_client_name">
								<p class="client_info"><?php echo $client_name; ?></p>
								<div style="display:none;" class="client_name_edit_container">
									<select name="client_name_edit" class="client_name_edit">
										<option><?php echo $client_name; ?></option>
										<?php 
											foreach($clients as $client){ 
												if($client_name != $client->client_name){
												?>
												<option><?php echo $client->client_name; ?></option>
												<?php 
												} 
											}
										?>
									</select>										
									<div id="check_edit_<?php echo $task_id; ?>" class="check_edit_client_name"></div>
								</div>
								<div style="display:none;" id="client_name_loader_<?php echo $task_id ?>" class="loader"></div>
							</div>
							<div class="column_group">
								<div id="project_name_<?php echo $task_id; ?>" class="second_column column edit_project_name">
									<p><?php echo $project_name; ?></p>
									<div style="display:none;" class="project_name_edit_container">
										<select name="project_name_edit" class="project_name_edit">
											<option><?php echo $project_name; ?></option>
										<?php 
											foreach($projects as $project){ 
												if($project_name != $project->project_category){
										?>
												<option><?php echo $project->project_category; ?></option>
										<?php 
												} 
											}
										?>
										</select>										
										<div id="check_edit_<?php echo $task_id; ?>" class="check_edit_project_name"></div>
									</div>
									<div style="display:none;" id="project_name_loader_<?php echo $task_id ?>" class="loader"></div>
								</div>								
								<div class="third_column column"><p><?php echo $task_name; ?></p></div>
								<div class="fourth_column column"><p><?php echo $person_name; ?></p></div>
								<div class="fifth_column column"><p><?php echo round_quarter($task_hour_decimal); ?></p></div>
								<div class="full_width task_description"><p><?php echo $task_description; ?></p></div>
								<?php if($tasks_done_today != null){ ?>
								<div class="accordian task_done_today_display full_width">
									<h5 class="toggle">
										<a href="#"><li class="">Done Today<span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<div class="full_width">
											<div class="header_titles">
												<div class="three_fourth"><p class="table_header">Task Description</p></div>
												<div class="one_fourth last"><p class="table_header">Task Hour</p></div>											
											</div>
											<div class="task_done_today_display_container">
											<?php  
												foreach($tasks_done_today as $task_done_today){
													$task_done_today_explode = explode("_",$task_done_today);
											?>
													<div class="task_done_today_border">
														<div class="three_fourth"><p><?php echo $task_done_today_explode[0]; ?></p></div>
														<div class="one_fourth last"><p><?php echo $task_done_today_explode[1]; ?></p></div>		
													</div>												
											<?php }	?>
											</div>
										</div>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
			<?php
				}
				$GLOBALS['total_day_hour'] = $total_day_hours;
			}	
			?>					
	</div>
</div>
<div style="display:none;" class="dialog_client_information" id="dialog_client_information" title="Client Information">
	<div class="full_width">
		<div class="one_half">
			<p class="label">Customer Info:</p>
			<p class="client_name"></p>
			<p class="client_address"></p>
			<p class="label">Contact Person:</p>
			<p class="client_contact_person"></p>
			<p class="client_contact_phone"></p>
			<p class="client_contact_email"></p>
		</div>
		<div class="one_half last">
			<div class="full_width">
				<p class="label">Monthly Plan: </p>
				<p class="client_monthly_plan"></p>
			</div>
			<div class="full_width">
				<p class="label">Customer Satisfaction: </p>
				<p class="client_satisfaction"></p>
			</div>
			<div class="full_width">
				<p class="label">Current Active WebDev Projects: </p>
				<p class="current_active_webdev_projects"></p>
			</div>
			<div class="full_width">
				<p class="label">Monthly Ongoing Stat: </p>
				<p class="monthly_ongoing_stat"></p>
			</div>
		</div>
	</div>
	<div class="full_width">
		<h3>Customer Sites</h3>
		<div class="header_titles">
			<div class="first_column column">URL</div>
			<div class="second_column column">Site Type</div>
			<div class="third_column column">Platform</div>
			<div class="fourth_column column">Version</div>
			<div class="fifth_column column">Username</div>
			<div class="sixth_column column">Password</div>
			<div class="seventh_column column">L</div>
		</div>
		<div class="site_container"></div>
	</div>
</div>
<?php get_footer(); ?>