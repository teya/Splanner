<?php /* Template name: Timesheet */ ?>
<?php get_header(); ?>
<?php

$timezone = get_option('timezone_string'); 
date_default_timezone_set($timezone); 


$date_now_week = date('Y/m/d');
$duedt = explode("/", $date_now_week);
$date  = mktime(0, 0, 0, $duedt[1], $duedt[2], $duedt[0]);
$week_number  = (int)date('W', $date);
	
$date_now = date('d/m/Y');
$day_now = date('l');
$display_date_now = date('l d M');
$num_date = date('d');
$compare_date_now = str_replace('/', '-', $date_now);

// $compare_date_now = '27-02-2017';

$user_id = get_current_user_id();
$user_data = get_userdata($user_id);
$user_name = $user_data->display_name;

$current_user = wp_get_current_user();
$current_user_name = $current_user->data->display_name;
$current_user_role = $current_user->roles['0'];
?>
<script>
	jQuery(document).ready(function(){
		jQuery('#prev').click(function(e){
			var current_tab = jQuery('.tabs_li.active a').text();
			var current_number = 1;		
			switch(current_tab){
				case "Monday":
					current_number = 1;
				break;
				case "Tuesday":
					current_number = 1;
				break;
				case "Wednesday":
					current_number = 2;
				break;
				case "Thursday":
					current_number = 3;
				break;
				case "Friday":
					current_number = 4;
				break;
				case "Saturday":
					current_number = 5;
				break;
				case "Sunday":
					current_number = 6;
				break;				
			}			
			
			jQuery('.tabs-container .tab_content').hide();
			jQuery('.tabs_li').removeClass("active");
			jQuery('.tabs_li:nth-child('+current_number+')').addClass("active");
			jQuery('.tab-box div:nth-child('+current_number+')').show();			
			jQuery('.tab-box div:nth-child('+current_number+') .clear_add_buttons').hide();
			jQuery('.tab-box div:nth-child('+current_number+') .import_message').hide();
			jQuery('.tab-box div:nth-child('+current_number+') .toggle-content').hide();
			jQuery('.tab-box div:nth-child('+current_number+') .timesheet_loader').hide();
			// var count_titles = jQuery('.header_titles').length;
			// alert(count_titles);
		});
		
		jQuery('#next').click(function(e){			
			var current_tab = jQuery('.tabs_li.active a').text();			
			var current_number = 1;
			switch(current_tab){
				case "Monday":
				current_number = 2;
				break;
				case "Tuesday":
				current_number = 3;
				break;
				case "Wednesday":
				current_number = 4;
				break;
				case "Thursday":
				current_number = 5;
				break;
				case "Friday":
				current_number = 6;
				break;
				case "Saturday":
				current_number = 7;
				break;
				case "Sunday":
				current_number = 7;
				break;				
			}			
			
			jQuery('.tabs-container .tab_content').hide();
			jQuery('.tabs_li').removeClass("active");
			jQuery('.tabs_li:nth-child('+current_number+')').addClass("active");			
			jQuery('.tab-box div:nth-child('+current_number+')').show();
			jQuery('.tab-box div:nth-child('+current_number+') .clear_add_buttons').hide();
			jQuery('.tab-box div:nth-child('+current_number+') .import_message').hide();
			jQuery('.tab-box div:nth-child('+current_number+') .toggle-content').hide();
			jQuery('.tab-box div:nth-child('+current_number+') .timesheet_loader').hide();
		});		
		
		// Tabs
		//When page loads...
		jQuery('.tabs-wrapper').each(function() {
			jQuery(this).find(".tab_content").hide();
			if(document.location.hash && jQuery(this).find("ul.tabs li a[href='"+document.location.hash+"']").length >= 1) {				
				jQuery(this).find(".tab_content.active").show(); 
			} else {
				jQuery(this).find(".tab_content.active").show(); 
			}
		});
		
		//On Click Event
		jQuery("ul.tabs li").click(function(e) {
			jQuery(this).parents('.tabs-wrapper').find("ul.tabs li").removeClass("active");
			jQuery(this).addClass("active");
			jQuery(this).parents('.tabs-wrapper').find(".tab_content").hide().removeClass("active");
			var activeTab = jQuery(this).find("a").attr("href"); 
			jQuery(this).parents('.tabs-wrapper').find(activeTab).show().addClass("active"); 
			
			jQuery(this).parents('.tabs-wrapper').find(activeTab).find('.content-boxes').each(function() {
				var cols = jQuery(this).find('.col').length;
				jQuery(this).addClass('columns-'+cols);
			});
			
			jQuery(this).parents('.tabs-wrapper').find(activeTab).find('.columns-3 .col:nth-child(3n), .columns-4 .col:nth-child(4n)').css('margin-right', '0px');
			
			jQuery(this).parents('.tabs-wrapper').find(activeTab).find('.portfolio-wrapper').isotope('reLayout');
			
			jQuery(this).parents('.tabs-wrapper').find(activeTab).find('.content-boxes-icon-boxed').each(function() {
				jQuery(this).find('.col').equalHeights();
			});
			
			jQuery(this).parents('.tabs-wrapper').find(activeTab).find('.shortcode-map').each(function() {
				jQuery("#"+this.id).goMap();
				marker = jQuery.goMap.markers[0];
				if(marker) {
					info = jQuery.goMap.getInfo(marker);
					jQuery.goMap.setInfo(marker, info);
				}			
				var center = jQuery.goMap.getMap().getCenter();
				google.maps.event.trigger(jQuery.goMap.getMap(), 'resize');
				jQuery.goMap.getMap().setCenter(center);
			});
			
			generateCarousel();
			
			e.preventDefault();
		});
		
		jQuery("ul.tabs li a").click(function(e) {
			e.preventDefault();
		});
	});
</script>
<?php 
	global $wpdb;			
	$table_name = $wpdb->prefix . "custom_timesheet";
	$table_name_client = $wpdb->prefix . "custom_client"; 
	$clients = $wpdb->get_results("SELECT * FROM {$table_name_client} WHERE client_status = 1 ORDER BY client_name ASC");
	$table_name_task = $wpdb->prefix . "custom_task";
	$tasks = $wpdb->get_results("SELECT * FROM {$table_name_task} ORDER BY task_name ASC");
	$table_name_person = $wpdb->prefix . "custom_person";
	$persons = $wpdb->get_results("SELECT * FROM {$table_name_person} WHERE person_status='0'");
	$current_person = $wpdb->get_row("SELECT * FROM {$table_name_person} WHERE wp_user_id = ".$current_user->id);
	$quota_time = decimalHours($current_person->person_hours_per_day);
	$table_name_project = $wpdb->prefix . "custom_project";
	$projects = $wpdb->get_results("SELECT * FROM {$table_name_project}");
	$table_color = $wpdb->prefix . "custom_project_color";
	$colors = $wpdb->get_results("SELECT * FROM {$table_color} ORDER BY project_category ASC");
?>
<?php 
	$timesheet_entries = $wpdb->get_results("SELECT * FROM {$table_name}");
	foreach($timesheet_entries as $timesheet_entry){
		if($timesheet_entry->week_number != $week_number){
			$entry_id = $timesheet_entry->ID;
			$wpdb->update($table_name,array(
			'status' => 0
			),
			array( 'ID' => $entry_id ),
			array( '%s', '%s' ));	
		}
	}
?>
<?php 
if(isset($_POST['modal_save'])){
	global $wpdb;
	$date_now = $_POST['date_now'];
	$day_now = $_POST['day_now'];
	$week_number = $_POST['week_number'];
	$task_name = $_POST['task_name'];
	$task_notes = $_POST['task_notes'];
	$task_hour = $_POST['task_hour'];
	$task_label = $_POST['task_label'];
	$task_person = $_POST['task_person'];	
	$user_id = $_POST['user_id'];	
	
	$insert = $wpdb->insert( $table_name , array( 
		'date_now' => $date_now,
		'day_now' => $day_now,
		'week_number' => $week_number,
		'task_name' => strtolower($task_name),
		'task_notes' => $task_notes,
		'task_hour' => $task_hour,
		'task_label' => $task_label,	
		'task_person' => $task_person,
		'user_id' => $user_id,
		'status' => 1
	),	
	array( '%s', '%s' ));
}

?>
<script>
	jQuery(document).ready(function(){
		jQuery('.ui-dialog[aria-describedby="dialog_form_timesheet_add"] .ui-dialog-titlebar').append('<div class="modal_header"><p class="modal_title">New Time Entry</p><p class="modal_date"><?php echo date('l, d M'); ?></p></div>');
	});
</script>
<?php if ( is_user_logged_in() ): ?>
<div style="display:none;" id="dialog_form_timesheet_add" title=""></div>
<div style="display:none;" id="dialog_form_timesheet_add_kanban_task" title=""></div>
<div style="display:none;" id="dialog_form_timesheet_edit_task" title="Edit Task"></div>
<div style="display:none;" id="dialog_form_timesheet_delete_task" title="Delete Task">
	<h3>Are you sure you want to continue?</h3>
	<form id="timesheet_delete_task_form">
		<input type="hidden" name="timesheet_task_id" id="timesheet_task_id" />
		<input type="hidden" name="timesheet_delete_day" id="timesheet_delete_day" />
		<input type="hidden" name="timesheet_task_current_hour" id="timesheet_task_current_hour" />
		<input type="hidden" name="timesheet_task_total_hours_worked" id="timesheet_task_total_hours_worked" />
		<input type="hidden" name="timesheet_task_hour_balance" id="timesheet_task_hour_balance" />
	</form>
	<div class="confirm_delete_buttons">
		<div style="display: none" class="loader"></div>
		<div class="button_2 delete_cancel">No</div>
		<div class="button_1 delete_confirm">Yes</div>
	</div>
</div>
<div style="display:none;" id="dialog_form_timesheet_done_today" title="Done Today"></div>
<?php
if(isset($_POST['clear_days'])){
	$wpdb->update($table_name,array(
	'status' => 0
	),
	array( 'day_now' => $_POST['clear_day'] ),
	array( '%s', '%s' ));	
}
if(isset($_GET['deleteID'])) {
	$id = $_GET['deleteID'];	
	$wpdb->query( "DELETE FROM {$table_name} WHERE ID = '$id'" ) ;
}	
	$week_number_range = date('W');
	$year = date('Y');
	$week = getStartAndEndDate($week_number_range, $year);
	$start_num = $week[0];
	$end_num = $week[1];
	$start = date("d M Y", strtotime($start_num));
	$end = date("d M Y", strtotime($end_num));
?>
<div class="timesheet">
		<div class="left_div">
			<form class="staff_timesheet_form">
				<div class="choose_person">
					<p class="label">Team Member: </p>
					 <input type="hidden" id="current-quota-time" value="<?php echo $quota_time; ?>">
					 <input type="hidden" id="current-person-id" value="<?php echo $current_person->wp_user_id; ?>">
					<select name="person_name" class="person_name">
						<option selected="" disabled="">-- Select Member --</option>
						<?php foreach($persons as $person){ 
								$person_name = $person->person_first_name ." ". $person->person_last_name;		
						?>		
						<option><?php echo $person_name; ?></option>
					<?php } ?>
					</select>
					<div style="display: none" class="loader"></div>
				</div>			
				<div id="week_number_calendar">	
					<?php 
						$week_number_date_picker  = date('W');
						$month_number = date('m');
					?>
					<input type="hidden" value="<?php echo $week_number_date_picker; ?>" name="week_number" class="week_number" id="week_number"/>
					<input type="hidden" value="<?php echo $year; ?>" name="picked_year" class="picked_year" id="picked_year"/>				
					<input type="hidden" value="<?php echo $month_number; ?>" name="picked_month" class="picked_month" id="picked_month"/>
				</div>
			</form>							
			<?php 
			$month_year = date ('F') ." ". $year;
			
			foreach($persons as $person){ 
				$person_name = $person->person_fullname;
				if($person_name == $current_user_name){
					$person_hour_per_day = $person->person_hours_per_day;
				}
			}
			
			if($person_hour_per_day != null ){
				$hour_per_day = $person_hour_per_day;
			}else{
				$hour_per_day = 8;
			}
			
			$today = date('d/m/Y');
			$check_today_timesheet = $wpdb->get_results("SELECT * FROM {$table_name} WHERE date_now = '$today' AND task_person = '$current_user_name'");
			if($check_today_timesheet != null){ /* TODAY STAT */
				$timesheet_month_details = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('31/$month_number/$year', '%d/%m/%Y')"); 
				
				$holiday_date = array();
				foreach($timesheet_month_details as $timesheet_month_detail){
					$task_name = format_task_name($timesheet_month_detail->task_name);
					if($task_name == 'Holiday'){
						$date = date('Y/m/d', strtotime($timesheet_month_detail->date_now));
						$holiday_date[] = $date;
					}
				}
				$holiday_count = count($holiday_date);
				$holiday_hours = $holiday_count * $hour_per_day;
				
				$date1 = "$year/$month_number/01";
				$date2 =  date('Y/m/d');
				$working_days = getWorkingDays($date1, $date2);
				$worked_hours = (($working_days * $hour_per_day) - $holiday_hours);
				
				$total_month_hours = 0;
				foreach($timesheet_month_details as $timesheet_month_detail){
					$task_name = format_task_name($timesheet_month_detail->task_name);
					if($task_name != 'Holiday'){					
						$task_hour 			= $timesheet_month_detail->task_hour;					
						$task_hour_decimal 	= round(decimalHours($task_hour), 2);					
						$total_month_hours	+= $task_hour_decimal;
					}					
				}
				$total_hours_worked = $total_month_hours;
				if(strpos($total_hours_worked, ".") === false){
					$format_total_hours_worked = $total_hours_worked;
					}else{
					$exploded_hours = explode(".",$total_hours_worked);				
					$hours = $exploded_hours[0];
					$minutes = $exploded_hours[1];
					$round_minutes = round_nearest($minutes, 5);
					if($round_minutes == 0){
						$format_total_hours_worked = $hours;
						}else{
						$format_total_hours_worked = $hours .".". $round_minutes;
					}
				}
				
				$hour_balance = ($format_total_hours_worked - $worked_hours);
				if($worked_hours  > $format_total_hours_worked){
					$p_class = "text_red";
					}else{
					$p_class = "text_green";
				}			
				
				foreach($timesheet_month_details as $timesheet_month_detail){
					$task_name = format_task_name($timesheet_month_detail->task_name);				
					if($task_name == 'Holiday Work'){
						$holiday_work_date = $timesheet_month_detail->date_now;
						$holiday_work_details = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('31/$month_number/$year', '%d/%m/%Y') AND date_now ='$holiday_work_date'"); 
						$holiday_work_hours = 0;
						foreach ($holiday_work_details as $holiday_work_detail){
							$task_hour = $holiday_work_detail->task_hour;
							$task_hour_decimal 	= round(decimalHours($task_hour), 2);
							$holiday_work_hours	+= $task_hour_decimal;
						}
					}					
				}
				if($holiday_work_hours != null){
					$total_holiday_work = $holiday_work_hours;
					}else{
					$total_holiday_work = 0;
				}
			}else{ /* YESTERDAY STAT */
				$yesterday = date('d/m/Y',strtotime("-1 days"));
				$yesterday_format = date('Y/m/d',strtotime("-1 days"));
				$timesheet_month_details = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('$yesterday', '%d/%m/%Y')"); 
				$holiday_date = array();
				foreach($timesheet_month_details as $timesheet_month_detail){
					$task_name = format_task_name($timesheet_month_detail->task_name);
					if($task_name == 'Holiday'){
						$date = date('Y/m/d', strtotime($timesheet_month_detail->date_now));
						$holiday_date[] = $date;
					}
				}
				$holiday_count = count($holiday_date);
				$holiday_hours = $holiday_count * $hour_per_day;
				
				$date1 = "$year/$month_number/01";
				$date2 =  $yesterday_format;
				$working_days = getWorkingDays($date1, $date2);
				$worked_hours = (($working_days * $hour_per_day) - $holiday_hours);
				
				$total_month_hours = 0;
				foreach($timesheet_month_details as $timesheet_month_detail){
					$task_name = format_task_name($timesheet_month_detail->task_name);
					if($task_name != 'Holiday'){					
						$task_hour 			= $timesheet_month_detail->task_hour;					
						$task_hour_decimal 	= round(decimalHours($task_hour), 2);					
						$total_month_hours	+= $task_hour_decimal;
					}					
				}
				$total_hours_worked = $total_month_hours;
				if(strpos($total_hours_worked, ".") === false){
					$format_total_hours_worked = $total_hours_worked;
					}else{
					$exploded_hours = explode(".",$total_hours_worked);				
					$hours = $exploded_hours[0];
					$minutes = $exploded_hours[1];
					$round_minutes = round_nearest($minutes, 5);
					if($round_minutes == 0){
						$format_total_hours_worked = $hours;
						}else{
						$format_total_hours_worked = $hours .".". $round_minutes;
					}
				}
				
				$hour_balance = ($format_total_hours_worked - $worked_hours);
				if($worked_hours  > $format_total_hours_worked){
					$p_class = "text_red";
					}else{
					$p_class = "text_green";
				}			
				
				foreach($timesheet_month_details as $timesheet_month_detail){
					$task_name = format_task_name($timesheet_month_detail->task_name);				
					if($task_name == 'Holiday Work'){
						$holiday_work_date = $timesheet_month_detail->date_now;
						$holiday_work_details = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('31/$month_number/$year', '%d/%m/%Y') AND date_now ='$holiday_work_date'"); 
						$holiday_work_hours = 0;
						foreach ($holiday_work_details as $holiday_work_detail){
							$task_hour = $holiday_work_detail->task_hour;
							$task_hour_decimal 	= round(decimalHours($task_hour), 2);
							$holiday_work_hours	+= $task_hour_decimal;
						}
					}					
				}
				if($holiday_work_hours != null){
					$total_holiday_work = $holiday_work_hours;
				}else{
					$total_holiday_work = 0;
				}
			}		
			?>
			<div class="month_stat">			
				<h1><?php echo $month_year; ?></h1>	
				<div class="month_details">
					<p class="label">Workable hours so far:</p>		
					<p class="hours worked_hours"><?php echo $worked_hours; ?></p>
				</div>
				<div class="month_details">
					<p class="label">Total hours worked:</p>
					<p class="hours total_hours_worked"><?php echo $format_total_hours_worked; ?></p>
				</div>
				<div class="month_details">
					<p class="label">Overtime/undertime Balance:</p>
					<p class="hours hour_balance <?php echo $p_class; ?>"><?php echo $hour_balance; ?></p>
				</div>
				<div class="month_details">
					<p class="label">Holiday hours so far:</p>
					<p class="hours holiday_hours"><?php echo $holiday_hours; ?></p>
				</div>
				<div class="month_details">
					<p class="label">Holiday Balance:</p>
					<p class="hours holiday_balance"><?php echo $total_holiday_work; ?></p>
				</div>
			</div>
		</div>
	
	<div class="right_div">
		<div class="header_person_name"><h1>Your Timesheet</h1></div>
		<div class="top_nav">
			<div class="week_section">
				<h3 class="week">Week: <?php echo $start .' '.'-'.' '. $end?> \ Time: <span id="splan_hours"><?php echo date('h'); ?></span>:<span id="splan_minutes"><?php echo date('i'); ?></span></span></h3>
			</div>
			<div style="display:none" class="status_message timesheet_message"><p></p><div class="loader"></div></div>
			<div style="display:none" class="action_message timesheet_message"><p></p></div>
			
			<div class="navigation_button">
				<div style="display: none" class="top_loader loader"></div>
				<button id="prev" class="button_1">Prev</button>
				<button id="next" class="button_1">Next</button>
			</div>
		</div>
		<?php 
		$date_range = date_range($start_num, $end_num);		 
		$monday_date_label = date("d M", strtotime(str_replace('/', '-', $date_range[0])));
		$tuesday_date_label = date("d M", strtotime(str_replace('/', '-', $date_range[1])));
		$wednesday_date_label = date("d M", strtotime(str_replace('/', '-', $date_range[2])));
		$thursday_date_label = date("d M", strtotime(str_replace('/', '-', $date_range[3])));
		$friday_date_label = date("d M", strtotime(str_replace('/', '-', $date_range[4])));
		$saturday_date_label = date("d M", strtotime(str_replace('/', '-', $date_range[5])));
		$sunday_date_label = date("d M", strtotime(str_replace('/', '-', $date_range[6])));
		?>
		<div class="tab-holder">
			<div class="tab-hold tabs-wrapper">
				<div class="full_width">				
					<ul id="tabs" class="tabset tabs">
						<li class="monday tabs_li <?php echo ($day_now == 'Monday') ? 'active' : ''; ?>"><a href="#monday"><p>Monday</p><p class="day_date"><?php echo $monday_date_label; ?></p></a></li>					
						<li class="tuesday tabs_li <?php echo ($day_now == 'Tuesday') ? 'active' : ''; ?>"><a href="#tuesday"><p>Tuesday</p><p class="day_date"><?php echo $tuesday_date_label; ?></p></a></li>					
						<li class="wednesday tabs_li <?php echo ($day_now == 'Wednesday') ? 'active' : ''; ?>"><a href="#wednesday"><p>Wednesday</p><p class="day_date"><?php echo $wednesday_date_label; ?></p></a></li>					
						<li class="thursday tabs_li <?php echo ($day_now == 'Thursday') ? 'active' : ''; ?>"><a href="#thursday"><p>Thursday</p><p class="day_date"><?php echo $thursday_date_label; ?></p></a></li>					
						<li class="friday tabs_li <?php echo ($day_now == 'Friday') ? 'active' : ''; ?>"><a href="#friday"><p>Friday</p><p class="day_date"><?php echo $friday_date_label; ?></p></a></li>					
						<li class="saturday tabs_li <?php echo ($day_now == 'Saturday') ? 'active' : ''; ?>"><a href="#saturday"><p>Saturday</p><p class="day_date"><?php echo $saturday_date_label; ?></p></a></li>					
						<li class="sunday tabs_li <?php echo ($day_now == 'Sunday') ? 'active' : ''; ?>"><a href="#sunday"><p>Sunday</p><p class="day_date"><?php echo $sunday_date_label; ?></p></a></li>
					</ul>
				</div>
				<div class="tab-box tabs-container">					
				<!-- MONDAY -->
					<div id="monday" class="tab tab_content <?php echo ($day_now == 'Monday') ? 'active' : ''; ?>" style="display: none;">
						<input type="hidden" class="tab_date monday_date" value="<?php echo $date_range[0]; ?>" />						
						<?php 

							$monday_date = str_replace('/', '-', $date_range[0]);
							$date_format = date("Y-m-d", strtotime($monday_date));
							$date = new DateTime($date_format);
							$week = $date->format("W");

							if(strtotime($monday_date) >= strtotime($compare_date_now)){
								$date_pass = true;
							}else{
								$date_pass = false;
							}
						?>
						<input type="hidden" class="tab_week monday_week datepicker_week" value="<?php echo $week; ?>" />
						<div class="person_task_timesheet">
							<div class="import_button">								
								<div id="import_kanban_task_monday" class="button_1 import_kanban_task button_import">Import</div>								
								<div id="add_kanban_task_monday" class="button_1 button_import add_task">Add Entry</div>
								<form class="import_save <?php echo ($day_now == 'Monday') ? '' : 'import_save_not_current' ?>"></form>
							</div>
							<?php 
								$filter = "Where ID!='' AND (day_now = 'Monday') AND (status = 1) AND (user_id = '$user_id') AND (date_now = '$date_range[0]')";
								$import_data = $wpdb->get_results("SELECT * FROM {$table_name} $filter"); 					
							?>
							<div class="task_label data_title header_titles">
								<h3 class="top_label">Client</h3>
								<?php foreach ($import_data as $import_item): ?>
								<li class="client_info data_list_monday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo (!empty($import_item->task_label)) ? $import_item->task_label : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_client">
										<?php 
											foreach ($clients as $client) {
												echo '<option>'.$client->client_name.'</option>';
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_color data_title header_titles">
								<h3 class="top_label">Project</h3>
								<?php 
									foreach ($import_data as $import_item){
										if($import_item->task_color != null){
											foreach($colors as $color){
												$task_color = ucfirst($import_item->task_color);
												if($color->project_color == $task_color){
													$task_category = $color->project_category;
												}
											}
										}else{
											$task_category = "--";
										}
								?>									
								<li class="data_list_monday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_category; ?></li>
								<?php } ?>
								<li class="">
									<select name="" id="" class="new_row_entry_project">
										<?php 
											foreach($colors as $project){
												echo "<option value='".$project->project_color."'>".$project->project_category."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_name data_title header_titles">
								<h3 class="top_label">Task</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_name) <= 25){
										$task_name_trimmed = $import_item->task_name;
										}else{
										$task_name_trimmed = substr($import_item->task_name, 0, 25) . "...";
									}
								?>
								<li class="data_list_monday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_taskname">
										<?php
											foreach($tasks as $task){
												echo "<option>".$task->task_name."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_details data_title header_titles">
								<h3 class="top_label">Task Details</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_suffix) <= 18){
										$task_name_trimmed = $import_item->task_suffix;
									}else{
										$task_name_trimmed = substr($import_item->task_suffix, 0, 18) . "...";
									}

									if(empty($import_item->task_suffix)){
										$task_name_trimmed = '...';
									}
								?>
								<li class="data_list_monday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_task_details">
								</li>
							</div>
							<div class="task_hour data_title header_titles">
								<h3 class="top_label">Hours</h3>
								<?php foreach ($import_data as $import_item):								
									$task_hms = $import_item->task_hour;
									$task_hour = time_format($task_hms);
								?>
								<li id="entry_timesheet_id_<?php echo $import_item->ID; ?>" class="data_list_monday timesheet_data_id_<?php echo $import_item->ID; ?> edit_entry_hours"><?php echo (!empty($task_hour)) ? $task_hour : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_entry_hours" placeholder="00:00">
								</li>
							</div>
							<!--<div class="task_person data_title header_titles">
								<h3 class="top_label">Done by</h3>
								<?php // foreach ($import_data as $import_item): ?>
								<li class="data_list_monday timesheet_data_id_<?php // echo $import_item->ID?>"><?php //echo (!empty($import_item->task_person)) ? $import_item->task_person : "--" ; ?></li>
								<?php // endforeach; ?>						
							</div>-->
							<div class="task_description data_title header_titles">
								<h3 class="top_label">Describe what was done</h3>
								<?php
									foreach ($import_data as $import_item):
									if(!empty($import_item->task_description)){
										$task_description = substr($import_item->task_description, 0, 20) . "...";
										
									}else{
										$task_description = "--";
									}
								?>						
								<div class="accordian accordian_<?php echo $import_item->ID?>">
									<h5 class="toggle">
										<a href="#"><li class="data_list_monday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_description ; ?><span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<?php echo stripslashes($import_item->task_description); ?>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="accordian_input">
									<textarea class="new_row_entry_description" row="2"></textarea>
								</div>
							</div>
							<div class="task_edit">
								<div class="top_label">&nbsp;<div style="display: none;" class="loader timesheet_loader"></div></div>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_monday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="edit_kanban_monday_<?php echo $import_item->ID?>" class="button_1 edit_button edit_kanban">E</div>
								</li>
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 add_new_row_entry_btn">+</div>
									<div style="display: none;" class="loader loader-add-new-entry"></div>
								</li>
							</div>	
							<div class="task_delete">
								<h3 class="top_label">&nbsp;&nbsp;&nbsp;&nbsp;</h3>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_monday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="delete_kanban_monday_<?php echo $import_item->ID?>" class="button_1 delete_button delete_edit_kanban">-</div>
								</li>
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 delete_new_row_entry_btn">-</div>
								</li>
							</div>
							<div class="task_done_today">
								<h5 class="top_label">Done Today<div class="button_help"></div></h5>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_monday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="done_today_kanban_monday_<?php echo $import_item->ID; ?>" class="button_1 done_today_button done_today_kanban">Done Today</div>
								</li>
								<?php endforeach; ?>
								<li>
									&nbsp;
								</li>
								<p style="display: none;" class="help_note">If task is not completely done, you can specify which task were done today.</p>
							</div>						
							<?php	
								// $all_hours = "";
								$total_hour_decimal = "";
								$timesheet_hours = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND day_now = 'Monday' AND week_number = '$week_number' AND status = '1' AND date_now = '$date_range[0]'");
								foreach($timesheet_hours as $timesheet_hour){
									$task_hour = $timesheet_hour->task_hour;
									$task_hour_decimal = decimalHours($task_hour);
									$total_hour_decimal += $task_hour_decimal;
									
								}
								
								$total_hour_unformat =  gmdate('H:i', floor($total_hour_decimal * 3600));
								$total_hour_explode = explode(":", $total_hour_unformat);
								$hour = $total_hour_explode[0];
								$minutes = $total_hour_explode[1];
								if(strlen($hour) == 1){
									$format_hour = "0" . $hour;
									}elseif(strlen($hour) == 2 ){
									$format_hour = $hour;
								}
								$round_minutes = round_nearest($minutes, 5);
								if(strlen($round_minutes) == 1){
									$rounded_minute = "0" . $round_minutes;
									}elseif(strlen($round_minutes) == 2){
									$rounded_minute = $round_minutes;
								}
								$total_hour = $format_hour .":". $rounded_minute;
								$total_hour_decimal = decimalHours($total_hour);
								$total_hour_format =  gmdate('H:i', floor($total_hour_decimal * 3600));

								if($date_pass == true){
									$color_status = "gray";
								}else{
									if($total_hour_format >= $quota_time){
										$color_status = "green";
									}else{
										$color_status = 'red';
									}
								}

							?>
						</div>
						<div class="total_hours">
							<div class="task_total"><h3>TOTAL</h3></div>
							<div class="task_total_hour">
								<h3><?php echo $total_hour_format; ?></h3>
								<input type="hidden" name="monday_status_color" value="<?php echo $color_status; ?>">
							</div>
						</div>						
						<div class="clear_add_buttons">							
							<div style="display:none;" id="clear_kanban_monday" class="button_1 button_import">Clear</div>
							<div style="display:none;" id="save_kanban_monday" class="button_1 button_import import_save_button <?php echo ($day_now == 'Monday') ? '' : 'import_save_not_current' ?>">Save</div>					
						</div>
						<div style="display:none;" class="import_message">
							<p>Press save if Imported time is correct, else clear.</p>
							<div style="display:none;" class="loader kanban_save_loader"></div>
						</div>						
					</div>
				<!-- TUESDAY -->
					<div id="tuesday" class="tab tab_content <?php echo ($day_now == 'Tuesday') ? 'active' : ''; ?>" style="display: none;">
						<input type="hidden" class="tab_date tuesday_date" value="<?php echo $date_range[1]; ?>" />
						<?php 
							$tuesday_date = str_replace('/', '-', $date_range[1]);
							$date_format = date("Y-m-d", strtotime($tuesday_date));
							$date = new DateTime($date_format);
							$week = $date->format("W");
							if(strtotime($tuesday_date) >= strtotime($compare_date_now)){
								$date_pass = true;
							}else{
								$date_pass = false;
							}
						?>
						<input type="hidden" class="tab_week tuesday_week datepicker_week" value="<?php echo $week; ?>" />
						<div class="person_task_timesheet">
							<div class="import_button">								
								<div id="import_kanban_task_tuesday" class="button_1 import_kanban_task button_import">Import</div>
								<div id="add_kanban_task_tuesday" class="button_1 button_import add_task">Add Entry</div>
								<form class="import_save <?php echo ($day_now == 'Tuesday') ? '' : 'import_save_not_current' ?>"></form>
							</div>
							<?php 
								$filter = "Where ID!='' AND (day_now = 'Tuesday') AND (status = 1) AND (user_id = '$user_id') AND (date_now = '$date_range[1]')";
								$import_data = $wpdb->get_results("SELECT * FROM {$table_name} $filter"); 					
							?>
							<div class="task_label data_title header_titles">
								<h3 class="top_label">Client</h3>
								<?php foreach ($import_data as $import_item): ?>
								<li class="client_info data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo (!empty($import_item->task_label)) ? $import_item->task_label : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_client">
										<?php 
											foreach ($clients as $client) {
												echo '<option>'.$client->client_name.'</option>';
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_color data_title header_titles">
								<h3 class="top_label">Project</h3>
								<?php 
									foreach ($import_data as $import_item){
										if($import_item->task_color != null){
											foreach($colors as $color){
												$task_color = ucfirst($import_item->task_color);
												if($color->project_color == $task_color){
													$task_category = $color->project_category;
												}
											}
											}else{
											$task_category = "--";
										}
								?>									
								<li class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_category; ?></li>
								<?php } ?>
								<li class="">
									<select name="" id="" class="new_row_entry_project">
										<?php 
											foreach($colors as $project){
												echo "<option value='".$project->project_color."'>".$project->project_category."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_name data_title header_titles">
								<h3 class="top_label">Task</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_name) <= 25){
										$task_name_trimmed = $import_item->task_name;
										}else{
										$task_name_trimmed = substr($import_item->task_name, 0, 25) . "...";
									}
								?>
								<li class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_taskname">
										<?php
											foreach($tasks as $task){
												echo "<option>".$task->task_name."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_details data_title header_titles">
								<h3 class="top_label">Task Details</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_suffix) <= 18){
										$task_name_trimmed = $import_item->task_suffix;
									}else{
										$task_name_trimmed = substr($import_item->task_suffix, 0, 18) . "...";
									}

									if(empty($import_item->task_suffix)){
										$task_name_trimmed = '...';
									}
								?>
								<li class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_task_details">
								</li>
							</div>
							<div class="task_hour data_title header_titles">
								<h3 class="top_label">Hours</h3>
								<?php foreach ($import_data as $import_item):
									$task_hms = $import_item->task_hour;
									$task_hour = time_format($task_hms);
								?>
								<li id="entry_timesheet_id_<?php echo $import_item->ID; ?>" class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?> edit_entry_hours"><?php echo (!empty($task_hour)) ? $task_hour : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_entry_hours" placeholder="00:00">
								</li>
							</div>
							<!--<div class="task_person data_title header_titles">
								<h3 class="top_label">Done by</h3>
								<?php // foreach ($import_data as $import_item): ?>
								<li class="data_list_tuesday timesheet_data_id_<?php //echo $import_item->ID?>"><?php // echo (!empty($import_item->task_person)) ? $import_item->task_person : "--" ; ?></li>
								<?php // endforeach; ?>
							</div>-->
							<div class="task_description data_title header_titles">
								<h3 class="top_label">Describe what was done</h3>
								<?php
									foreach ($import_data as $import_item):
									if(!empty($import_item->task_description)){
										$task_description = substr($import_item->task_description, 0, 20) . "...";
										
									}else{
										$task_description = "--";
									}
								?>						
								<div class="accordian accordian_<?php echo $import_item->ID?>">
									<h5 class="toggle">
										<a href="#"><li class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_description ; ?><span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<?php echo stripslashes($import_item->task_description); ?>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="accordian_input">
									<textarea class="new_row_entry_description" row="2"></textarea>
								</div>
							</div>
							<div class="task_edit">
								<div class="top_label">&nbsp;<div style="display: none;" class="loader timesheet_loader"></div></div>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="edit_kanban_tuesday_<?php echo $import_item->ID?>" class="button_1 edit_button edit_kanban">E</div>
								</li>								
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 add_new_row_entry_btn">+</div>
									<div style="display: none;" class="loader loader-add-new-entry"></div>
								</li>
							</div>													
							<div class="task_delete">
								<h3 class="top_label">&nbsp;&nbsp;&nbsp;&nbsp;</h3>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="delete_kanban_tuesday_<?php echo $import_item->ID?>" class="button_1 delete_button delete_edit_kanban">-</div>
								</li>
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 delete_new_row_entry_btn">-</div>
								</li>
							</div>
							<div class="task_done_today">
								<h5 class="top_label">Done Today<div class="button_help"></div></h5>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_tuesday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="done_today_kanban_tuesday_<?php echo $import_item->ID; ?>" class="button_1 done_today_button done_today_kanban">Done Today</div>
								</li>
								<?php endforeach; ?>
								<li>
									&nbsp;
								</li>
								<p style="display: none;" class="help_note">If task is not completely done, you can specify which task were done today.</p>
							</div>	
							<?php	
								// $all_hours = "";
								$total_hour_decimal = "";						
								$timesheet_hours = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND day_now = 'Tuesday' AND week_number = '$week_number' AND status = '1' AND date_now = '$date_range[1]'");
								foreach($timesheet_hours as $timesheet_hour){
									$task_hour = $timesheet_hour->task_hour;
									$task_hour_decimal = decimalHours($task_hour);
									$total_hour_decimal += $task_hour_decimal;
									
								}
								
								$total_hour_unformat =  gmdate('H:i', floor($total_hour_decimal * 3600));
								$total_hour_explode = explode(":", $total_hour_unformat);
								$hour = $total_hour_explode[0];
								$minutes = $total_hour_explode[1];
								if(strlen($hour) == 1){
									$format_hour = "0" . $hour;
									}elseif(strlen($hour) == 2 ){
									$format_hour = $hour;
								}
								$round_minutes = round_nearest($minutes, 5);
								if(strlen($round_minutes) == 1){
									$rounded_minute = "0" . $round_minutes;
									}elseif(strlen($round_minutes) == 2){
									$rounded_minute = $round_minutes;
								}
								$total_hour = $format_hour .":". $rounded_minute;
								$total_hour_decimal = decimalHours($total_hour);
								$total_hour_format =  gmdate('H:i', floor($total_hour_decimal * 3600));

								if($date_pass == true){
									$color_status = "gray";
								}else{
									if($total_hour_format >= $quota_time){
										$color_status = "green";
									}else{
										$color_status = 'red';
									}
								}
							?>
						</div>
						<div class="total_hours">
							<div class="task_total"><h3>TOTAL</h3></div>
							<div class="task_total_hour">
								<h3><?php echo $total_hour_format; ?></h3>
								<input type="hidden" name="tuesday_status_color" value="<?php echo $color_status; ?>">
							</div>
						</div>						
						<div class="clear_add_buttons">							
							<div style="display:none;" id="clear_kanban_tuesday" class="button_1 button_import">Clear</div>
							<div style="display:none;" id="save_kanban_tuesday" class="button_1 button_import import_save_button <?php echo ($day_now == 'Tuesday') ? '' : 'import_save_not_current' ?>">Save</div>					
						</div>
						<div style="display:none;" class="import_message">							
							<p>Press save if Imported time is correct, else clear.</p>
							<div style="display:none;" class="loader kanban_save_loader"></div>
						</div>	
					</div>
				<!-- WEDNESDAY -->
					<div id="wednesday" class="tab tab_content <?php echo ($day_now == 'Wednesday') ? 'active' : ''; ?>" style="display: none;">
						<input type="hidden" class="tab_date wednesday_date" value="<?php echo $date_range[2]; ?>" />
						<?php 
							$wednesday_date = str_replace('/', '-', $date_range[2]);
							$date_format = date("Y-m-d", strtotime($wednesday_date));
							$date = new DateTime($date_format);
							$week = $date->format("W");
							if(strtotime($wednesday_date) >= strtotime($compare_date_now)){
								$date_pass = true;
							}else{
								$date_pass = false;
							}
						?>
						<input type="hidden" class="tab_week wednesday_week datepicker_week" value="<?php echo $week; ?>" />
						<div class="person_task_timesheet">
							<div class="import_button">								
								<div id="import_kanban_task_wednesday" class="button_1 import_kanban_task button_import">Import</div>
								<div id="add_kanban_task_wednesday" class="button_1 button_import add_task">Add Entry</div>						
								<form class="import_save <?php echo ($day_now == 'Wednesday') ? '' : 'import_save_not_current' ?>"></form>
							</div>
							<?php 
								$filter = "Where ID!='' AND (day_now = 'Wednesday') AND (status = 1) AND (user_id = '$user_id') AND (date_now = '$date_range[2]')";
								$import_data = $wpdb->get_results("SELECT * FROM {$table_name} $filter"); 					
							?>
							<div class="task_label data_title header_titles">
								<h3 class="top_label">Client</h3>
								<?php foreach ($import_data as $import_item): ?>
								<li class="client_info data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo (!empty($import_item->task_label)) ? $import_item->task_label : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_client">
										<?php 
											foreach ($clients as $client) {
												echo '<option>'.$client->client_name.'</option>';
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_color data_title header_titles">
								<h3 class="top_label">Project</h3>
								<?php 
									foreach ($import_data as $import_item){
										if($import_item->task_color != null){
											foreach($colors as $color){
												$task_color = ucfirst($import_item->task_color);
												if($color->project_color == $task_color){
													$task_category = $color->project_category;
												}
											}
											}else{
											$task_category = "--";
										}
								?>									
								<li class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_category; ?></li>
								<?php } ?>
								<li class="">
									<select name="" id="" class="new_row_entry_project">
										<?php 
											foreach($colors as $project){
												echo "<option value='".$project->project_color."'>".$project->project_category."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_name data_title header_titles">
								<h3 class="top_label">Task</h3>
								<?php 
									foreach ($import_data as $import_item): 
									if($import_item->task_name != null && $import_item->task_suffix != null){
										$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
										}elseif($import_item->task_name != null && $import_item->task_suffix == null){
										$task_name = format_task_name($import_item->task_name);
										}elseif($import_item->task_name == null && $import_item->task_suffix != null){
										$task_name = $import_item->task_suffix;
										}elseif($import_item->task_name == null && $import_item->task_suffix == null){
										$task_name = "--";
									}
									if(strlen($task_name) <= 25){
										$task_name_trimmed = $task_name;
										}else{
										$task_name_trimmed = substr($task_name, 0, 25) . "...";
									}
								?>
								<li class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_taskname">
										<?php
											foreach($tasks as $task){
												echo "<option>".$task->task_name."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_details data_title header_titles">
								<h3 class="top_label">Task Details</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_suffix) <= 18){
										$task_name_trimmed = $import_item->task_suffix;
									}else{
										$task_name_trimmed = substr($import_item->task_suffix, 0, 18) . "...";
									}

									if(empty($import_item->task_suffix)){
										$task_name_trimmed = '...';
									}
								?>
								<li class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_task_details">
								</li>
							</div>
							<div class="task_hour data_title header_titles">
								<h3 class="top_label">Hours</h3>
								<?php foreach ($import_data as $import_item):
									$task_hms = $import_item->task_hour;
									$task_hour = time_format($task_hms);
								?>
								<li id="entry_timesheet_id_<?php echo $import_item->ID; ?>" class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?> edit_entry_hours"><?php echo (!empty($task_hour)) ? $task_hour : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_entry_hours" placeholder="00:00">
								</li>
							</div>
							<!--<div class="task_person data_title header_titles">
								<h3 class="top_label">Done by</h3>
								<?php //foreach ($import_data as $import_item): ?>
								<li class="data_list_wednesday timesheet_data_id_<?php //echo $import_item->ID?>"><?php //echo (!empty($import_item->task_person)) ? $import_item->task_person : "--" ; ?></li>
								<?php //endforeach; ?>						
							</div>-->
							<div class="task_description data_title header_titles">
								<h3 class="top_label">Describe what was done</h3>
								<?php
									foreach ($import_data as $import_item):
									if(!empty($import_item->task_description)){
										$task_description = substr($import_item->task_description, 0, 20) . "...";
										
										}else{
										$task_description = "--";
									}
								?>						
								<div class="accordian accordian_<?php echo $import_item->ID?>">
									<h5 class="toggle">
										<a href="#"><li class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_description ; ?><span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<?php echo stripslashes($import_item->task_description); ?>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="accordian_input">
									<textarea class="new_row_entry_description" row="2"></textarea>
								</div>
							</div>
							<div class="task_edit">
								<div class="top_label">&nbsp;<div style="display: none;" class="loader timesheet_loader"></div></div>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="edit_kanban_wednesday_<?php echo $import_item->ID?>" class="button_1 edit_button edit_kanban">E</div>
								</li>								
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 add_new_row_entry_btn">+</div>
									<div style="display: none;" class="loader loader-add-new-entry"></div>
								</li>
							</div>	
							<div class="task_delete">
								<h3 class="top_label">&nbsp;&nbsp;&nbsp;&nbsp;</h3>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="delete_kanban_wednesday_<?php echo $import_item->ID?>" class="button_1 delete_button delete_edit_kanban">-</div>
								</li>
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 delete_new_row_entry_btn">-</div>
								</li>
							</div>
							<div class="task_done_today">
								<h5 class="top_label">Done Today<div class="button_help"></div></h5>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_wednesday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="done_today_kanban_wednesday_<?php echo $import_item->ID; ?>" class="button_1 done_today_button done_today_kanban">Done Today</div>
								</li>
								<?php endforeach; ?>
								<li>
									&nbsp;
								</li>
								<p style="display: none;" class="help_note">If task is not completely done, you can specify which task were done today.</p>
							</div>		
							<?php	
								// $all_hours = "";
								$total_hour_decimal = "";
								$timesheet_hours = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND day_now = 'Wednesday' AND week_number = '$week_number' AND status = '1' AND date_now = '$date_range[2]'");
								foreach($timesheet_hours as $timesheet_hour){
									$task_hour = $timesheet_hour->task_hour;
									$task_hour_decimal = decimalHours($task_hour);
									$total_hour_decimal += $task_hour_decimal;
									
								}
								
								$total_hour_unformat =  gmdate('H:i', floor($total_hour_decimal * 3600));
								$total_hour_explode = explode(":", $total_hour_unformat);
								$hour = $total_hour_explode[0];
								$minutes = $total_hour_explode[1];
								if(strlen($hour) == 1){
									$format_hour = "0" . $hour;
									}elseif(strlen($hour) == 2 ){
									$format_hour = $hour;
								}
								$round_minutes = round_nearest($minutes, 5);
								if(strlen($round_minutes) == 1){
									$rounded_minute = "0" . $round_minutes;
								}elseif(strlen($round_minutes) == 2){
									$rounded_minute = $round_minutes;
								}
								$total_hour = $format_hour .":". $rounded_minute;
								$total_hour_decimal = decimalHours($total_hour);
								$total_hour_format =  gmdate('H:i', floor($total_hour_decimal * 3600));

								if($date_pass == true){
									$color_status = "gray";
								}else{
									if($total_hour_format >= $quota_time){
										$color_status = "green";
									}else{
										$color_status = 'red';
									}
								}

							?>
						</div>
						<div class="total_hours">
							<div class="task_total"><h3>TOTAL</h3></div>
							<div class="task_total_hour">
								<h3><?php echo $total_hour_format; ?></h3>
								<input type="hidden" name="wednesday_status_color" value="<?php echo $color_status; ?>">
							</div>
						</div>						
						<div class="clear_add_buttons">
							<div style="display:none;" id="clear_kanban_wednesday" class="button_1 button_import">Clear</div>
							<div style="display:none;" id="save_kanban_wednesday" class="button_1 button_import import_save_button <?php echo ($day_now == 'Wednesday') ? '' : 'import_save_not_current' ?>">Save</div>					
						</div>
						<div style="display:none;" class="import_message">							
							<p>Press save if Imported time is correct, else clear.</p>
							<div style="display:none;" class="loader kanban_save_loader"></div>
						</div>						
					</div>
				<!-- THURSDAY -->
					<div id="thursday" class="tab tab_content <?php echo ($day_now == 'Thursday') ? 'active' : ''; ?>" style="display: none;">
						<input type="hidden" class="tab_date thursday_date" value="<?php echo $date_range[3]; ?>" />
						<?php 
							$thursday_date = str_replace('/', '-', $date_range[3]);
							$date_format = date("Y-m-d", strtotime($thursday_date));
							$date = new DateTime($date_format);
							$week = $date->format("W");
							if(strtotime($thursday_date) >= strtotime($compare_date_now)){
								$date_pass = true;
							}else{
								$date_pass = false;
							}
						?>
						<input type="hidden" class="tab_week thursday_week datepicker_week" value="<?php echo $week; ?>" />
						<div class="person_task_timesheet">
							<div class="import_button">								
								<div id="import_kanban_task_thursday" class="button_1 import_kanban_task button_import">Import</div>
								<div id="add_kanban_task_thursday" class="button_1 button_import add_task">Add Entry</div>
								<form class="import_save <?php echo ($day_now == 'Thursday') ? '' : 'import_save_not_current' ?>"></form>
							</div>
							<?php 
								$filter = "Where ID!='' AND (day_now = 'Thursday') AND (status = 1) AND (user_id = '$user_id') AND (date_now = '$date_range[3]')";
								$import_data = $wpdb->get_results("SELECT * FROM {$table_name} $filter"); 					
							?>
							<div class="task_label data_title header_titles">
								<h3 class="top_label">Client</h3>
								<?php foreach ($import_data as $import_item): ?>
								<li class="client_info data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo (!empty($import_item->task_label)) ? $import_item->task_label : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_client">
										<?php 
											foreach ($clients as $client) {
												echo '<option>'.$client->client_name.'</option>';
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_color data_title header_titles">
								<h3 class="top_label">Project</h3>
								<?php 
									foreach ($import_data as $import_item){
										if($import_item->task_color != null){
											foreach($colors as $color){
												$task_color = ucfirst($import_item->task_color);
												if($color->project_color == $task_color){
													$task_category = $color->project_category;
												}
											}
											}else{
											$task_category = "--";
										}
								?>									
								<li class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_category; ?></li>
								<?php } ?>
								<li class="">
									<select name="" id="" class="new_row_entry_project">
										<?php 
											foreach($colors as $project){
												echo "<option value='".$project->project_color."'>".$project->project_category."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_name data_title header_titles">
								<h3 class="top_label">Task</h3>
								<?php 
									foreach ($import_data as $import_item): 
									if($import_item->task_name != null && $import_item->task_suffix != null){
										$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
										}elseif($import_item->task_name != null && $import_item->task_suffix == null){
										$task_name = format_task_name($import_item->task_name);
										}elseif($import_item->task_name == null && $import_item->task_suffix != null){
										$task_name = $import_item->task_suffix;
										}elseif($import_item->task_name == null && $import_item->task_suffix == null){
										$task_name = "--";
									}
									if(strlen($task_name) <= 25){
										$task_name_trimmed = $task_name;
									}else{
										$task_name_trimmed = substr($task_name, 0, 25) . "...";
									}
								?>
								<li class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_taskname">
										<?php
											foreach($tasks as $task){
												echo "<option>".$task->task_name."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_details data_title header_titles">
								<h3 class="top_label">Task Details</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_suffix) <= 18){
										$task_name_trimmed = $import_item->task_suffix;
									}else{
										$task_name_trimmed = substr($import_item->task_suffix, 0, 18) . "...";
									}

									if(empty($import_item->task_suffix)){
										$task_name_trimmed = '...';
									}
								?>
								<li class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_task_details">
								</li>
							</div>
							<div class="task_hour data_title header_titles">
								<h3 class="top_label">Hours</h3>
								<?php foreach ($import_data as $import_item):
									$task_hms = $import_item->task_hour;
									$task_hour = time_format($task_hms);
								?>
								<li id="entry_timesheet_id_<?php echo $import_item->ID; ?>" class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?> edit_entry_hours"><?php echo (!empty($task_hour)) ? $task_hour : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_entry_hours" placeholder="00:00">
								</li>
							</div>
							<!--<div class="task_person data_title header_titles">						
								<h3 class="top_label">Done by</h3>
								<?php //foreach ($import_data as $import_item): ?>
								<li class="data_list_thursday timesheet_data_id_<?php //echo $import_item->ID?>"><?php //echo (!empty($import_item->task_person)) ? $import_item->task_person : "--" ; ?></li>
								<?php //endforeach; ?>						
							</div>-->
							<div class="task_description data_title header_titles">
								<h3 class="top_label">Describe what was done</h3>
								<?php
									foreach ($import_data as $import_item):
									if(!empty($import_item->task_description)){
										$task_description = substr($import_item->task_description, 0, 20) . "...";
										
										}else{
										$task_description = "--";
									}
								?>						
								<div class="accordian accordian_<?php echo $import_item->ID?>">
									<h5 class="toggle">
										<a href="#"><li class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_description ; ?><span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<?php echo stripslashes($import_item->task_description); ?>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="accordian_input">
									<textarea class="new_row_entry_description" row="2"></textarea>
								</div>
							</div>
							<div class="task_edit">
								<div class="top_label">&nbsp;<div style="display: none;" class="loader timesheet_loader"></div></div>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="edit_kanban_thursday_<?php echo $import_item->ID?>" class="button_1 edit_button edit_kanban">E</div>
								</li>								
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 add_new_row_entry_btn">+</div>
									<div style="display: none;" class="loader loader-add-new-entry"></div>
								</li>
							</div>	
							<div class="task_delete">
								<h3 class="top_label">&nbsp;&nbsp;&nbsp;&nbsp;</h3>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="delete_kanban_thursday_<?php echo $import_item->ID?>" class="button_1 delete_button delete_edit_kanban">-</div>
								</li>
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 delete_new_row_entry_btn">-</div>
								</li>
							</div>
							<div class="task_done_today">
								<h5 class="top_label">Done Today<div class="button_help"></div></h5>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_thursday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="done_today_kanban_thursday_<?php echo $import_item->ID; ?>" class="button_1 done_today_button done_today_kanban">Done Today</div>
								</li>
								<?php endforeach; ?>
								<li>
									&nbsp;
								</li>
								<p style="display: none;" class="help_note">If task is not completely done, you can specify which task were done today.</p>
							</div>
							<?php	
								// $all_hours = "";
								$total_hour_decimal = "";
								$timesheet_hours = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND day_now = 'Thursday' AND week_number = '$week_number' AND status = '1' AND date_now = '$date_range[3]'");
								foreach($timesheet_hours as $timesheet_hour){
									$task_hour = $timesheet_hour->task_hour;									
									$task_hour_decimal = decimalHours($task_hour);
									$total_hour_decimal += $task_hour_decimal;
									
								}
								
								$total_hour_unformat =  gmdate('H:i', floor($total_hour_decimal * 3600));
								$total_hour_explode = explode(":", $total_hour_unformat);
								$hour = $total_hour_explode[0];
								$minutes = $total_hour_explode[1];
								if(strlen($hour) == 1){
									$format_hour = "0" . $hour;
									}elseif(strlen($hour) == 2 ){
									$format_hour = $hour;
								}
								$round_minutes = round_nearest($minutes, 5);
								if(strlen($round_minutes) == 1){
									$rounded_minute = "0" . $round_minutes;
									}elseif(strlen($round_minutes) == 2){
									$rounded_minute = $round_minutes;
								}
								$total_hour = $format_hour .":". $rounded_minute;
								$total_hour_decimal = decimalHours($total_hour);
								$total_hour_format =  gmdate('H:i', floor($total_hour_decimal * 3600));

								if($date_pass == true){
									$color_status = "gray";
								}else{
									if($total_hour_format >= $quota_time){
										$color_status = "green";
									}else{
										$color_status = 'red';
									}
								}
							?>
						</div>
						<div class="total_hours">
							<div class="task_total"><h3>TOTAL</h3></div>
							<div class="task_total_hour">
								<h3><?php echo $total_hour_format; ?></h3>
								<input type="hidden" name="thursday_status_color" value="<?php echo $color_status; ?>">
							</div>
						</div>
						<div class="clear_add_buttons">
							<div style="display:none;" id="clear_kanban_thursday" class="button_1 button_import">Clear</div>
							<div style="display:none;" id="save_kanban_thursday" class="button_1 button_import import_save_button <?php echo ($day_now == 'Thursday') ? '' : 'import_save_not_current' ?>">Save</div>					
						</div>
						<div style="display:none;" class="import_message">
							<p>Press save if Imported time is correct, else clear.</p>
							<div style="display:none;" class="loader kanban_save_loader"></div>
						</div>						
					</div>
				<!-- FRIDAY -->
					<div id="friday" class="tab tab_content <?php echo ($day_now == 'Friday') ? 'active' : ''; ?>" style="display: none;">
						<input type="hidden" class="tab_date friday_date" value="<?php echo $date_range[4]; ?>" />
						<?php 
							$friday_date = str_replace('/', '-', $date_range[4]);
							$date_format = date("Y-m-d", strtotime($friday_date));
							$date = new DateTime($date_format);
							$week = $date->format("W");

							if(strtotime($friday_date) >= strtotime($compare_date_now)){
								$date_pass = true;
							}else{
								$date_pass = false;
							}
						?>
						<input type="hidden" class="tab_week friday_week datepicker_week" value="<?php echo $week; ?>" />
						<div class="person_task_timesheet">
							<div class="import_button">								
								<div id="import_kanban_task_friday" class="button_1 import_kanban_task button_import">Import</div>
								<div id="add_kanban_task_friday" class="button_1 button_import add_task">Add Entry</div>
								<form class="import_save <?php echo ($day_now == 'Friday') ? '' : 'import_save_not_current' ?>"></form>
							</div>
							<?php 
								$filter = "Where ID!='' AND (day_now = 'Friday') AND (status = 1) AND (user_id = '$user_id') AND (date_now = '$date_range[4]')";
								$import_data = $wpdb->get_results("SELECT * FROM {$table_name} $filter"); 					
							?>
							<div class="task_label data_title header_titles">
								<h3 class="top_label">Client</h3>
								<?php foreach ($import_data as $import_item): ?>
								<li class="client_info data_list_friday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo (!empty($import_item->task_label)) ? $import_item->task_label : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_client">
										<?php 
											foreach ($clients as $client) {
												echo '<option>'.$client->client_name.'</option>';
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_color data_title header_titles">
								<h3 class="top_label">Project</h3>
								<?php 
									foreach ($import_data as $import_item){
										if($import_item->task_color != null){
											foreach($colors as $color){
												$task_color = ucfirst($import_item->task_color);
												if($color->project_color == $task_color){
													$task_category = $color->project_category;
												}
											}
											}else{
											$task_category = "--";
										}
								?>									
								<li class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_category; ?></li>
								<?php } ?>
								<li class="">
									<select name="" id="" class="new_row_entry_project">
										<?php 
											foreach($colors as $project){
												echo "<option value='".$project->project_color."'>".$project->project_category."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_name data_title header_titles">
								<h3 class="top_label">Task</h3>
								<?php 
									foreach ($import_data as $import_item): 
									if($import_item->task_name != null && $import_item->task_suffix != null){
										$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
										}elseif($import_item->task_name != null && $import_item->task_suffix == null){
										$task_name = format_task_name($import_item->task_name);
										}elseif($import_item->task_name == null && $import_item->task_suffix != null){
										$task_name = $import_item->task_suffix;
										}elseif($import_item->task_name == null && $import_item->task_suffix == null){
										$task_name = "--";
									}
									if(strlen($task_name) <= 25){
										$task_name_trimmed = $task_name;
										}else{
										$task_name_trimmed = substr($task_name, 0, 25) . "...";
									}
								?>
								<li class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_taskname">
										<?php
											foreach($tasks as $task){
												echo "<option>".$task->task_name."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_details data_title header_titles">
								<h3 class="top_label">Task Details</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_suffix) <= 18){
										$task_name_trimmed = $import_item->task_suffix;
									}else{
										$task_name_trimmed = substr($import_item->task_suffix, 0, 18) . "...";
									}

									if(empty($import_item->task_suffix)){
										$task_name_trimmed = '...';
									}
								?>
								<li class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_task_details">
								</li>
							</div>
							<div class="task_hour data_title header_titles">
								<h3 class="top_label">Hours</h3>
								<?php foreach ($import_data as $import_item):
									$task_hms = $import_item->task_hour;
									$task_hour = time_format($task_hms);
								?>
								<li id="entry_timesheet_id_<?php echo $import_item->ID; ?>" class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?> edit_entry_hours"><?php echo (!empty($task_hour)) ? $task_hour : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_entry_hours" placeholder="00:00">
								</li>
							</div>
							<!--<div class="task_person data_title header_titles">
								<h3 class="top_label">Done by</h3>
								<?php //foreach ($import_data as $import_item): ?>
								<li class="data_list_friday timesheet_data_id_<?php //echo $import_item->ID?>"><?php //echo (!empty($import_item->task_person)) ? $import_item->task_person : "--" ; ?></li>
								<?php //endforeach; ?>
							</div>-->
							<div class="task_description data_title header_titles">
								<h3 class="top_label">Describe what was done</h3>
								<?php
									foreach ($import_data as $import_item):
									if(!empty($import_item->task_description)){
										$task_description = substr($import_item->task_description, 0, 20) . "...";
										
									}else{
										$task_description = "--";
									}
								  ?>						
								<div class="accordian accordian_<?php echo $import_item->ID?>">
									<h5 class="toggle">
										<a href="#"><li class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_description ; ?><span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<?php echo stripslashes($import_item->task_description); ?>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="accordian_input">
									<textarea class="new_row_entry_description" row="2"></textarea>
								</div>
							</div>
							<div class="task_edit">
								<div class="top_label">&nbsp;<div style="display: none;" class="loader timesheet_loader"></div></div>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="edit_kanban_friday_<?php echo $import_item->ID?>" class="button_1 edit_button edit_kanban">E</div>
								</li>								
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 add_new_row_entry_btn">+</div>
									<div style="display: none;" class="loader loader-add-new-entry"></div>
								</li>
							</div>	
							<div class="task_delete">
								<h3 class="top_label">&nbsp;&nbsp;&nbsp;&nbsp;</h3>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="delete_kanban_friday_<?php echo $import_item->ID?>" class="button_1 delete_button delete_edit_kanban">-</div>
								</li>
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 delete_new_row_entry_btn">-</div>
								</li>
							</div>
							<div class="task_done_today">
								<h5 class="top_label">Done Today<div class="button_help"></div></h5>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_friday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="done_today_kanban_friday_<?php echo $import_item->ID; ?>" class="button_1 done_today_button done_today_kanban">Done Today</div>
								</li>
								<?php endforeach; ?>
								<li>
									&nbsp;
								</li>
								<p style="display: none;" class="help_note">If task is not completely done, you can specify which task were done today.</p>
							</div>
							<?php	
								// $all_hours = "";
								$total_hour_decimal = "";
								$timesheet_hours = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND day_now = 'Friday' AND week_number = '$week_number' AND status = '1' AND date_now = '$date_range[4]'");
								foreach($timesheet_hours as $timesheet_hour){
									$task_hour = $timesheet_hour->task_hour;
									$task_hour_decimal = decimalHours($task_hour);
									$total_hour_decimal += $task_hour_decimal;
									
								}
								
								$total_hour_unformat =  gmdate('H:i', floor($total_hour_decimal * 3600));
								$total_hour_explode = explode(":", $total_hour_unformat);
								$hour = $total_hour_explode[0];
								$minutes = $total_hour_explode[1];
								if(strlen($hour) == 1){
									$format_hour = "0" . $hour;
									}elseif(strlen($hour) == 2 ){
									$format_hour = $hour;
								}
								$round_minutes = round_nearest($minutes, 5);
								if(strlen($round_minutes) == 1){
									$rounded_minute = "0" . $round_minutes;
									}elseif(strlen($round_minutes) == 2){
									$rounded_minute = $round_minutes;
								}
								$total_hour = $format_hour .":". $rounded_minute;
								$total_hour_decimal = decimalHours($total_hour);
								$total_hour_format =  gmdate('H:i', floor($total_hour_decimal * 3600));

								if($date_pass == true){
									$color_status = "gray";
								}else{
									if($total_hour_format >= $quota_time){
										$color_status = "green";
									}else{
										$color_status = 'red';
									}
								}
							?>
						</div>
						<div class="total_hours">
							<div class="task_total"><h3>TOTAL</h3></div>
							<div class="task_total_hour">
								<h3><?php echo $total_hour_format; ?></h3>
								<input type="hidden" name="friday_status_color" value="<?php echo $color_status; ?>">
							</div>
						</div>						
						<div class="clear_add_buttons">
							<div style="display:none;" id="clear_kanban_friday" class="button_1 button_import">Clear</div>
							<div style="display:none;" id="save_kanban_friday" class="button_1 button_import import_save_button <?php echo ($day_now == 'Friday') ? '' : 'import_save_not_current' ?>">Save</div>					
						</div>
						<div style="display:none;" class="import_message">
							<p>Press save if Imported time is correct, else clear.</p>
							<div style="display:none;" class="loader kanban_save_loader"></div>
						</div>						
					</div>
				<!-- SATURDAY -->
					<div id="saturday" class="tab tab_content <?php echo ($day_now == 'Saturday') ? 'active' : ''; ?>" style="display: none;">
						<input type="hidden" class="tab_date saturday_date" value="<?php echo $date_range[5]; ?>" />
						<?php 
							$saturday_date = str_replace('/', '-', $date_range[5]);
							$date_format = date("Y-m-d", strtotime($saturday_date));
							$date = new DateTime($date_format);
							$week = $date->format("W");
							if(strtotime($saturday_date) >= strtotime($compare_date_now)){
								$date_pass = true;
							}else{
								$date_pass = false;
							}
						?>
						<input type="hidden" class="tab_week saturday_week datepicker_week" value="<?php echo $week; ?>" />
						<div class="person_task_timesheet">
							<div class="import_button">								
								<div id="import_kanban_task_saturday" class="button_1 import_kanban_task button_import">Import</div>
								<div id="add_kanban_task_saturday" class="button_1 button_import add_task">Add Entry</div>
								<form class="import_save <?php echo ($day_now == 'Saturday') ? '' : 'import_save_not_current' ?>"></form>
							</div>
							<?php 
								$filter = "Where ID!='' AND (day_now = 'Saturday') AND (status = 1) AND (user_id = '$user_id') AND (date_now = '$date_range[5]')";
								$import_data = $wpdb->get_results("SELECT * FROM {$table_name} $filter"); 					
							?>
							<div class="task_label data_title header_titles">
								<h3 class="top_label">Client</h3>
								<?php foreach ($import_data as $import_item): ?>
								<li class="client_info data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo (!empty($import_item->task_label)) ? $import_item->task_label : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_client">
										<?php 
											foreach ($clients as $client) {
												echo '<option>'.$client->client_name.'</option>';
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_color data_title header_titles">
								<h3 class="top_label">Project</h3>
								<?php 
									foreach ($import_data as $import_item){
										if($import_item->task_color != null){
											foreach($colors as $color){
												$task_color = ucfirst($import_item->task_color);
												if($color->project_color == $task_color){
													$task_category = $color->project_category;
												}
											}
											}else{
											$task_category = "--";
										}
								?>									
								<li class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_category; ?></li>
								<?php } ?>
								<li class="">
									<select name="" id="" class="new_row_entry_project">
										<?php 
											foreach($colors as $project){
												echo "<option value='".$project->project_color."'>".$project->project_category."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_name data_title header_titles">
								<h3 class="top_label">Task</h3>
								<?php 
									foreach ($import_data as $import_item): 
									if($import_item->task_name != null && $import_item->task_suffix != null){
										$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
										}elseif($import_item->task_name != null && $import_item->task_suffix == null){
										$task_name = format_task_name($import_item->task_name);
										}elseif($import_item->task_name == null && $import_item->task_suffix != null){
										$task_name = $import_item->task_suffix;
										}elseif($import_item->task_name == null && $import_item->task_suffix == null){
										$task_name = "--";
									}
									if(strlen($task_name) <= 25){
										$task_name_trimmed = $task_name;
										}else{
										$task_name_trimmed = substr($task_name, 0, 25) . "...";
									}
								?>
								<li class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_taskname">
										<?php
											foreach($tasks as $task){
												echo "<option>".$task->task_name."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_details data_title header_titles">
								<h3 class="top_label">Task Details</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_suffix) <= 18){
										$task_name_trimmed = $import_item->task_suffix;
									}else{
										$task_name_trimmed = substr($import_item->task_suffix, 0, 18) . "...";
									}

									if(empty($import_item->task_suffix)){
										$task_name_trimmed = '...';
									}
								?>
								<li class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_task_details">
								</li>
							</div>
							<div class="task_hour data_title header_titles">
								<h3 class="top_label">Hours</h3>
								<?php foreach ($import_data as $import_item):
									$task_hms = $import_item->task_hour;
									$task_hour = time_format($task_hms);
								?>
								<li id="entry_timesheet_id_<?php echo $import_item->ID; ?>" class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?> edit_entry_hours"><?php echo (!empty($task_hour)) ? $task_hour : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_entry_hours" placeholder="00:00">
								</li>
							</div>
							<!--<div class="task_person data_title header_titles">
								<h3 class="top_label">Done by</h3>
								<?php //foreach ($import_data as $import_item): ?>
								<li class="data_list_saturday timesheet_data_id_<?php //echo $import_item->ID?>"><?php// echo (!empty($import_item->task_person)) ? $import_item->task_person : "--" ; ?></li>
								<?php //endforeach; ?>
							</div>-->
							<div class="task_description data_title header_titles">
								<h3 class="top_label">Describe what was done</h3>
								<?php
									foreach ($import_data as $import_item):
									if(!empty($import_item->task_description)){
										$task_description = substr($import_item->task_description, 0, 20) . "...";
										
										}else{
										$task_description = "--";
									}
								?>						
								<div class="accordian accordian_<?php echo $import_item->ID?>">
									<h5 class="toggle">
										<a href="#"><li class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_description ; ?><span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<?php echo stripslashes($import_item->task_description); ?>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="accordian_input">
									<textarea class="new_row_entry_description" row="2"></textarea>
								</div>
							</div>
							<div class="task_edit">
								<div class="top_label">&nbsp;<div style="display: none;" class="loader timesheet_loader"></div></div>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="edit_kanban_saturday_<?php echo $import_item->ID?>" class="button_1 edit_button edit_kanban">E</div>
								</li>								
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 add_new_row_entry_btn">+</div>
									<div style="display: none;" class="loader loader-add-new-entry"></div>
								</li>
							</div>	
							<div class="task_delete">
								<h3 class="top_label">&nbsp;&nbsp;&nbsp;&nbsp;</h3>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>">									
									<div id="delete_kanban_saturday_<?php echo $import_item->ID?>" class="button_1 delete_button delete_edit_kanban">-</div>
								</li>
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 delete_new_row_entry_btn">-</div>
								</li>
							</div>
							<div class="task_done_today">
								<h5 class="top_label">Done Today<div class="button_help"></div></h5>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_saturday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="done_today_kanban_saturday_<?php echo $import_item->ID; ?>" class="button_1 done_today_button done_today_kanban">Done Today</div>
								</li>
								<?php endforeach; ?>
								<li>
									&nbsp;
								</li>
								<p style="display: none;" class="help_note">If task is not completely done, you can specify which task were done today.</p>
							</div>
							<?php	
								// $all_hours = "";
								$total_hour_decimal = "";
								$timesheet_hours = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND day_now = 'Saturday' AND week_number = '$week_number' AND status = '1' AND date_now = '$date_range[5]'");
								foreach($timesheet_hours as $timesheet_hour){
									$task_hour = $timesheet_hour->task_hour;
									$task_hour_decimal = decimalHours($task_hour);
									$total_hour_decimal += $task_hour_decimal;
									
								}
								
								$total_hour_unformat =  gmdate('H:i', floor($total_hour_decimal * 3600));
								$total_hour_explode = explode(":", $total_hour_unformat);
								$hour = $total_hour_explode[0];
								$minutes = $total_hour_explode[1];
								if(strlen($hour) == 1){
									$format_hour = "0" . $hour;
									}elseif(strlen($hour) == 2 ){
									$format_hour = $hour;
								}
								$round_minutes = round_nearest($minutes, 5);
								if(strlen($round_minutes) == 1){
									$rounded_minute = "0" . $round_minutes;
									}elseif(strlen($round_minutes) == 2){
									$rounded_minute = $round_minutes;
								}
								$total_hour = $format_hour .":". $rounded_minute;
								$total_hour_decimal = decimalHours($total_hour);
								$total_hour_format =  gmdate('H:i', floor($total_hour_decimal * 3600));

								if($date_pass == true){
									$color_status = "gray";
								}else{
									if($total_hour_decimal > 0){
										$color_status = "yellow";
									}else{
										// $color_status = 'red';
									}
								}
							?>
						</div>
						<div class="total_hours">
							<div class="task_total"><h3>TOTAL</h3></div>
							<div class="task_total_hour">
								<h3><?php echo $total_hour_format; ?></h3>
								<input type="hidden" name="saturday_status_color" value="<?php echo $color_status; ?>">
							</div>
						</div>
						<div class="clear_add_buttons">
							<div style="display:none;" id="clear_kanban_saturday" class="button_1 button_import">Clear</div>
							<div style="display:none;" id="save_kanban_saturday" class="button_1 button_import import_save_button <?php echo ($day_now == 'Saturday') ? '' : 'import_save_not_current' ?>">Save</div>					
						</div>
						<div style="display:none;" class="import_message">
							<p>Press save if Imported time is correct, else clear.</p>
							<div style="display:none;" class="loader kanban_save_loader"></div>
						</div>						
					</div>
				<!-- SUNDAY -->
					<div id="sunday" class="tab tab_content <?php echo ($day_now == 'Sunday') ? 'active' : ''; ?>" style="display: none;">
						<input type="hidden" class="tab_date sunday_date" value="<?php echo $date_range[6]; ?>">
						<?php 
							$sunday_date = str_replace('/', '-', $date_range[6]);
							$date_format = date("Y-m-d", strtotime($sunday_date));
							$date = new DateTime($date_format);
							$week = $date->format("W");
							if(strtotime($sunday_date) >= strtotime($compare_date_now)){
								$date_pass = true;
							}else{
								$date_pass = false;
							}
						?>
						<input type="hidden" class="tab_week sunday_week datepicker_week" value="<?php echo $week; ?>" />
						<div class="person_task_timesheet">
							<div class="import_button">								
								<div id="import_kanban_task_sunday" class="button_1 import_kanban_task button_import">Import</div>
								<div id="add_kanban_task_sunday" class="button_1 button_import add_task">Add Entry</div>
								<form class="import_save <?php echo ($day_now == 'Sunday') ? '' : 'import_save_not_current' ?>"></form>
							</div>
							<?php 
								$filter = "Where ID!='' AND (day_now = 'Sunday') AND (status = 1) AND (user_id = '$user_id') AND (date_now = '$date_range[6]')";
								$import_data = $wpdb->get_results("SELECT * FROM {$table_name} $filter"); 					
							?>
							<div class="task_label data_title header_titles">
								<h3 class="top_label">Client</h3>
								<?php foreach ($import_data as $import_item): ?>
								<li class="client_info data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo (!empty($import_item->task_label)) ? $import_item->task_label : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_client">
										<?php 
											foreach ($clients as $client) {
												echo '<option>'.$client->client_name.'</option>';
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_color data_title header_titles">
								<h3 class="top_label">Project</h3>
								<?php 
									foreach ($import_data as $import_item){
										if($import_item->task_color != null){
											foreach($colors as $color){
												$task_color = ucfirst($import_item->task_color);
												if($color->project_color == $task_color){
													$task_category = $color->project_category;
												}
											}
											}else{
											$task_category = "--";
										}
								?>									
								<li class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_category; ?></li>
								<?php } ?>
								<li class="">
									<select name="" id="" class="new_row_entry_project">
										<?php 
											foreach($colors as $project){
												echo "<option value='".$project->project_color."'>".$project->project_category."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_name data_title header_titles">
								<h3 class="top_label">Task</h3>
								<?php 
									foreach ($import_data as $import_item): 
									if($import_item->task_name != null && $import_item->task_suffix != null){
										$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
										}elseif($import_item->task_name != null && $import_item->task_suffix == null){
										$task_name = format_task_name($import_item->task_name);
										}elseif($import_item->task_name == null && $import_item->task_suffix != null){
										$task_name = $import_item->task_suffix;
										}elseif($import_item->task_name == null && $import_item->task_suffix == null){
										$task_name = "--";
									}
									if(strlen($task_name) <= 25){
										$task_name_trimmed = $task_name;
										}else{
										$task_name_trimmed = substr($task_name, 0, 25) . "...";
									}
								?>
								<li class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed; ?></li>
								<?php endforeach; ?>
								<li class="">
									<select name="" class="new_row_entry_taskname">
										<?php
											foreach($tasks as $task){
												echo "<option>".$task->task_name."</option>";
											}
										?>
									</select>
								</li>
							</div>
							<div class="task_details data_title header_titles">
								<h3 class="top_label">Task Details</h3>
								<?php 
									foreach ($import_data as $import_item): 
									// if($import_item->task_name != null && $import_item->task_suffix != null){
									// 	$task_name = format_task_name($import_item->task_name) ." - ". $import_item->task_suffix;
									// 	}elseif($import_item->task_name != null && $import_item->task_suffix == null){
									// 	$task_name = format_task_name($import_item->task_name);
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix != null){
									// 	$task_name = $import_item->task_suffix;
									// 	}elseif($import_item->task_name == null && $import_item->task_suffix == null){
									// 	$task_name = "--";
									// }
									if(strlen($import_item->task_suffix) <= 18){
										$task_name_trimmed = $import_item->task_suffix;
									}else{
										$task_name_trimmed = substr($import_item->task_suffix, 0, 18) . "...";
									}

									if(empty($import_item->task_suffix)){
										$task_name_trimmed = '...';
									}
								?>
								<li class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_name_trimmed ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_task_details">
								</li>
							</div>
							<div class="task_hour data_title header_titles">
								<h3 class="top_label">Hours</h3>
								<?php foreach ($import_data as $import_item):
									$task_hms = $import_item->task_hour;
									$task_hour = time_format($task_hms);
								?>
								<li id="entry_timesheet_id_<?php echo $import_item->ID; ?>" class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?> edit_entry_hours"><?php echo (!empty($task_hour)) ? $task_hour : "--" ; ?></li>
								<?php endforeach; ?>
								<li class="">
									<input type="text" class="new_row_entry_hours" placeholder="00:00">
								</li>
							</div>
							<!--<div class="task_person data_title header_titles">
								<h3 class="top_label">Done by</h3>
								<?php //foreach ($import_data as $import_item): ?>
								<li class="data_list_sunday timesheet_data_id_<?php //echo $import_item->ID?>"><?php //echo (!empty($import_item->task_person)) ? $import_item->task_person : "--" ; ?></li>
								<?php //endforeach; ?>
							</div>-->
							<div class="task_description data_title header_titles">
								<h3 class="top_label">Describe what was done</h3>
								<?php
									foreach ($import_data as $import_item):
									if(!empty($import_item->task_description)){
										$task_description = substr($import_item->task_description, 0, 20) . "...";
										
										}else{
										$task_description = "--";
									}
								?>						
								<div class="accordian accordian_<?php echo $import_item->ID?>">
									<h5 class="toggle">
										<a href="#"><li class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>"><?php echo $task_description ; ?><span class="arrow"></span></li></a>
									</h5>
									<div class="toggle-content" style="display: none;">
										<?php echo stripslashes($import_item->task_description); ?>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="accordian_input">
									<textarea class="new_row_entry_description" row="2"></textarea>
								</div>
							</div>
							<div class="task_edit">
								<div class="top_label">&nbsp;<div style="display: none;" class="loader timesheet_loader"></div></div>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="edit_kanban_sunday_<?php echo $import_item->ID?>" class="button_1 edit_button edit_kanban">E</div>
								</li>								
								<?php endforeach; ?>
								<li class="">
									<div class="button_1 add_new_row_entry_btn">+</div>
									<div style="display: none;" class="loader loader-add-new-entry"></div>
								</li>
							</div>	
							<div class="task_delete">
								<h3 class="top_label">&nbsp;&nbsp;&nbsp;&nbsp;</h3>
								<?php foreach ($import_data as $import_item): ?>								
								<li class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>">
									<div id="delete_kanban_sunday_<?php echo $import_item->ID?>" class="button_1 delete_button delete_edit_kanban">-</div>
								</li>
							<?php endforeach; ?>
								<li class="">
									<div class="button_1 delete_new_row_entry_btn">-</div>
								</li>
							</div>
							<div class="task_done_today">
								<h5 class="top_label">Done Today<div class="button_help"></div></h5>
									<?php foreach ($import_data as $import_item): ?>									
									<li class="data_list_sunday timesheet_data_id_<?php echo $import_item->ID?>">
										<div id="done_today_kanban_sunday_<?php echo $import_item->ID; ?>" class="button_1 done_today_button done_today_kanban">Done Today</div>
									</li>
									<?php endforeach; ?>
									<p style="display: none;" class="help_note">If task is not completely done, you can specify which task were done today.</p>
								</div>
							<?php	
								// $all_hours = "";
								$total_hour_decimal = "";
								$timesheet_hours = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$current_user_name' AND day_now = 'Sunday' AND week_number = '$week_number' AND status = '1' AND date_now = '$date_range[6]'");
								foreach($timesheet_hours as $timesheet_hour){
									$task_hour = $timesheet_hour->task_hour;
									$task_hour_decimal = decimalHours($task_hour);
									$total_hour_decimal += $task_hour_decimal;
									
								}
						
								$total_hour_unformat =  gmdate('H:i', floor($total_hour_decimal * 3600));
								$total_hour_explode = explode(":", $total_hour_unformat);
								$hour = $total_hour_explode[0];
								$minutes = $total_hour_explode[1];
								if(strlen($hour) == 1){
									$format_hour = "0" . $hour;
									}elseif(strlen($hour) == 2 ){
									$format_hour = $hour;
								}
								$round_minutes = round_nearest($minutes, 5);
								if(strlen($round_minutes) == 1){
									$rounded_minute = "0" . $round_minutes;
									}elseif(strlen($round_minutes) == 2){
									$rounded_minute = $round_minutes;
								}
								$total_hour = $format_hour .":". $rounded_minute;
								$total_hour_decimal = decimalHours($total_hour);
								$total_hour_format =  gmdate('H:i', floor($total_hour_decimal * 3600));
								if($date_pass == true){
									$color_status = "gray";
								}else{
									if($total_hour_format >= $quota_time){
										$color_status = "yellow";
									}else{
										// $color_status = 'red';
									}
								}
							?>
						</div>
						<div class="total_hours">
							<div class="task_total"><h3>TOTAL</h3></div>
							<div class="task_total_hour">
								<h3><?php echo $total_hour_format; ?></h3>
								<input type="hidden" name="sunday_status_color" value="<?php echo $color_status; ?>">
							</div>
						</div>
						<div class="clear_add_buttons">
							<div style="display:none;" id="clear_kanban_sunday" class="button_1 button_import">Clear</div>
							<div style="display:none;" id="save_kanban_sunday" class="button_1 button_import import_save_button <?php echo ($day_now == 'Sunday') ? '' : 'import_save_not_current' ?>">Save</div>					
						</div>
						<div style="display:none;" class="import_message">
							<p>Press save if Imported time is correct, else clear.</p>
							<div style="display:none;" class="loader kanban_save_loader"></div>
						</div>						
					</div>
				<!--END DAYS -->
				</div>
			</div>
		</div>
		<div id="splan-timesheet-buttons">
			<ul>
				<li>
					<div id="add_sickness" class="button_1 add_none_working_btn">Sick</div>
					<div style="display: none;" class="loader non-working-loader"></div>
				</li>
				<li>
					<div id="add_holiday" class="button_1 add_none_working_btn">Holiday</div>
					<div style="display: none;" class="loader non-working-loader"></div>
				</li>
				<li>
					<div id="add_issues" class="button_1 add_none_working_btn">Issues</div>
					<div style="display: none;" class="loader non-working-loader"></div>
				</li>
				<li>
					<div id="add_vacation" class="button_1 add_none_working_btn">Vacation</div>
					<div style="display: none;" class="loader non-working-loader"></div>
				</li>
			</ul>
		</div>
		<div style="display:none" class="month_summary">
			<h1>Monthly Summary</h1>
			<h1 class="month_name"></h1>
			<div class="column">
				<h2 class="label">Total Month's hour:</h2>		
				<h3 class="total_month_hour"></h3>
			</div>
			<div class="column">
				<h2 class="label">Total Holiday's hour:</h2>		
				<h3 class="total_holiday_hour"></h3>
			</div>
			<div class="column">
				<h2 class="label">Hour Balance:</h2>		
				<h3 class="hour_balance"></h3>
			</div>
		</div>
	</div>	
</div>
<div class="import_function">
<?php
$date_now = date('Y/m/d');
$duedt = explode("/", $date_now);
$date  = mktime(0, 0, 0, $duedt[1], $duedt[2], $duedt[0]);
$week_number  = (int)date('W', $date);
 
if(isset($_POST['import'])):
	$seconds = 50000;
	set_time_limit($seconds);
	$token = "apiToken=e1f2928c903625d1b6b2e7b00ec12031";
	$url="https://kanbanflow.com/api/v1/board?" . $token;	
	$result = file_get_contents($url);
	$api_count = 1;	
	$result_array = json_decode($result, true);	
	$counter = 1;
	$task_counter = 1;
	$curr_task = "";
	$cur_user = "";
	
		foreach($result_array['columns'] as $column){
			$get_task_by_column = "https://kanbanflow.com/api/v1/tasks?" . $token . "&columnId=" . $column['uniqueId'];
			$column_content = @file_get_contents($get_task_by_column);
			$api_count++;
			$task = json_decode($column_content, true);
			
			foreach($task as $task_col){
				if($task_col['swimlaneName'] == 'Website Dev'){
					if($counter == 1){
						$swimlane_name = $task_col['swimlaneName']; 											
					}
					$column_name = $task_col['columnName'];					
					foreach($task_col['tasks'] as $single_task){
						$get_task_by_date = "https://kanbanflow.com/api/v1/tasks/". $single_task['_id'] ."/events?from=".date("Y-m-d")."T00:00Z&" . $token;
						$task_by_date = @file_get_contents($get_task_by_date);
						$api_count++;
						$task_now = json_decode($task_by_date, true);
						
						foreach ($task_now as $item){							
							if($item != null){
								foreach($item as $event_item){									
									$current_user = wp_get_current_user();
									$current_user_name = $current_user->data->display_name;
									$current_user_role = $current_user->roles['0'];
									
									$get_task_person = "https://kanbanflow.com/api/v1/users" . "?&" . $token;
									$task_person_array = file_get_contents($get_task_person);
									$api_count++;
									$task_person_decode = json_decode($task_person_array, true);
									
									foreach ($task_person_decode as $task_persons){
										$person_full_name = $task_persons['fullName'];
										if($current_user_name == $person_full_name){
											$person_id = $task_persons['_id'];
											$event_user_id = $event_item['userId'];
											if($event_user_id == $person_id ){
												foreach($event_item['detailedEvents'] as $key => $items){																					
													$get_task_item = "https://kanbanflow.com/api/v1/tasks/" . $items['taskId'] . "?&" . $token;
													$changed_task = file_get_contents($get_task_item);
													$api_count++;										
													$task_decode = json_decode($changed_task, true);								
													
													$get_task_label = "https://kanbanflow.com/api/v1/tasks/". $task_decode['_id']. "/labels" . "?&" . $token;
													$task_label_array = @file_get_contents($get_task_label);
													$api_count++;
													$task_label_decode = json_decode($task_label_array, true);
													
													if($items['taskId'] == $task_decode['_id']){
														$array_merge = array_merge( $items, $task_decode, $task_label_decode);
														if($array_merge['changedProperties'][0]['property'] == 'totalSecondsSpent'){
															$task_name = $array_merge['name'];
															$old_hour = $array_merge['changedProperties'][0]['oldValue'];
															$new_hour = $array_merge['changedProperties'][0]['newValue'];												
															$task_hour_diff = $new_hour - $old_hour;
															$task_hour = gmdate("H:i", $task_hour_diff);
															$task_label = $array_merge[0]['name'];															
														?>
														<script>
															jQuery(document).ready(function(){	
																
																jQuery('.tab_content.active .task_name').append("<li><p><?php echo (!empty($task_name)) ? $task_name : "&nbsp;";?></p></li>");												
																jQuery('.tab_content.active .task_hour').append("<li><p><?php echo (!empty($task_hour)) ? $task_hour : "&nbsp;"; ?></p></li>");
																jQuery('.tab_content.active .task_label').append("<li class='client_info'><p><?php echo (!empty($task_label)) ? $task_label : "&nbsp;"; ?></p></li>");
																jQuery('.tab_content.active .task_person').append("<li><p><?php echo (!empty($person_full_name)) ? $person_full_name : "&nbsp;" ;?></p></li>");
																
																
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='date_now' value='<?php echo $date_now; ?>' />");
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='day_now' value='<?php echo $day_now; ?>' />");
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='week_number' value='<?php echo $week_number; ?>' />");
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='task_name[]' value='<?php echo $task_name; ?>'/>");
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='task_hour[]' value='<?php echo $task_hour; ?>'/>");												
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='task_label[]' value='<?php echo $task_label; ?>'/>");
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='task_person[]' value='<?php echo $person_full_name; ?>'/>");												
																jQuery('.tab_content.active .import_save').append("<input type='hidden' name='user_id[]' value='<?php echo $user_id; ?>'/>");												
																setTimeout(3000);
															});
														</script>
														<?php
														}										
													}
												}
											}
										}										
									}									
								}
							}
						}
					}
				}
			}
	$counter++;
		}
	//API Token Count
	$current_user = wp_get_current_user();
	$api_count_save = $wpdb->insert( 'wp_kanban_timesheet', 
	array( 
		'username' => $current_user->display_name, 
		'import_time' => date('Y-m-d H:i:s'),
		'import_type' => 'Template name: Timesheet',
		'api_count' => $api_count
	 ), 
	array( '%s', '%s', '%s', '%d' ) );
endif;

?>	
</div>
<?php
	else:
	echo "<div id='login_box' class='login_box'>";

	// if  (function_exists ('wplb_login'))   { wplb_login(); } 
		$args = array(
		        'echo' => true,
		        'redirect' => site_url( $_SERVER['REQUEST_URI'] ), 
		        'form_id' => 'loginform',
		        'label_username' => __( 'Username' ),
		        'label_password' => __( 'Password' ),
		        'label_remember' => __( 'Remember Me' ),
		        'label_log_in' => __( 'Log In' ),
		        'id_username' => 'user_login',
		        'id_password' => 'user_pass',
		        'id_remember' => 'rememberme',
		        'id_submit' => 'wp-submit',
		        'remember' => true,
		        'value_username' => NULL,
		        'value_remember' => false );

		wp_login_form( $args );
	echo "</div>";
	endif; 
?>
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
<?php 
	$SeoWebsolutions = $wpdb->get_row('SELECT client_name FROM '.SPLAN_CLIENT_LIST.' WHERE id = 15');
?>
<!-- Sickness dialog box -->
<div style="display:none;" class="sickness_dialog_box" id="sickness_dialog_box" title="">
	<div class="full_width">
		<div class="dialog-content">
			<table class="splan-table-form">
				<tr>
					<td>Client:</td>
					<td><p class="clientname"><?php echo $SeoWebsolutions->client_name;   ?></p></td>
				</tr>
				<tr>
					<td>Task Name:</td>
					<td>Sickness</td>
				</tr>
				<tr>
					<td>Project Name:</td>
					<td>Non Working Days</td>
				</tr>
				<tr>
					<td>Hours:</td>
					<td><input id="sickness_hours" type="text" name="sickness_hours" value="08:00"></td>
				</tr>
				<tr>
					<td>Description:</td>
					<td><textarea id="sickness_descriptions" name="sickness_descriptions"></textarea></td>
				</tr>
			</table>
		</div>
		<div class="footer-dialog">
			<ul>
				<li>
					<div style="display: none;" class="loader add-sickness-loader"></div>
				</li>
				<li>
					<div id="add-sickness-day" class="button_1">Add</div>
				</li>
				<li>
					<div class="button_1">Cancel</div>
				</li>
			</ul>
		</div>
	</div>
</div>
<?php get_footer(); ?>
