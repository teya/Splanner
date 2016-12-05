<?php
/* ==================================== TIMESHEET IMPORT TASK ==================================== */
function import_task_kanban($date_hour_day_week){
	$date_hour_day_week_explode = explode("_",$date_hour_day_week);
	$import_date = $date_hour_day_week_explode[0];
	$import_total_hour = $date_hour_day_week_explode[1];
	$import_day = $date_hour_day_week_explode[2];
	$import_week = $date_hour_day_week_explode[3];
	$import_date_explode = explode("/",$import_date);
	$day = $import_date_explode[0];
	$month = $import_date_explode[1];
	$year = $import_date_explode[2];
	$format_import_date = $year."-".$month."-".$day;
	global $wpdb;
	$seconds = 0;
	set_time_limit($seconds);
	$token = "apiToken=5ca8e8ab49cd25f58fb7fa3fbe566c75";
	$counter = 1;
	$task_counter = 1;
	$curr_task = "";
	$cur_user = "";
	$tasks = array();
	$total_hour_decimal = "";	
	
	$current_user = wp_get_current_user();	
	$user_id = get_current_user_id();
	$current_user_fullname = $current_user->data->display_name;
	
	$table_name_person = $wpdb->prefix . "custom_person";
	$person_detail = $wpdb->get_row("SELECT * FROM $table_name_person WHERE person_fullname = '$current_user_fullname'");
	$person_kb_user_id = $person_detail->person_kb_user_id;
	
	$url= "https://kanbanflow.com/api/v1/board/events?from=".$format_import_date."T00:00Z&to=".$format_import_date."T23:59Z&" . $token;
	$result = file_get_contents($url);	
	$result_array = json_decode($result, true);
	// print_Var($result_array);
	$id_array = array();
	
	foreach($result_array['events'] as $key => $event_tasks){
		foreach($event_tasks['detailedEvents'] as $detailed_events){
			if($detailed_events['changedProperties'][0]['property'] == 'totalSecondsSpent'){
				$task_id = $detailed_events['taskId'];
				array_push($id_array,$task_id);
			}
		}	
	}	
	$ids = array_unique($id_array);
	
	if($ids == null){
		$tasks_data['no_task'] = 'no_task';
	}
	
	foreach($ids as $id){
		$get_task_item = "https://kanbanflow.com/api/v1/tasks/" . $id . "?&" . $token;
		$changed_task = file_get_contents($get_task_item);										
		$task_details = json_decode($changed_task, true);
		$reposible_user_id = $task_details['responsibleUserId'];
		
		if($reposible_user_id == $person_kb_user_id){
			$get_task_label = "https://kanbanflow.com/api/v1/tasks/". $id. "/labels" . "?&" . $token;
			$task_label_array = @file_get_contents($get_task_label);
			$task_label_details = json_decode($task_label_array, true);	
			
			$task_color = $task_details['color'];
			$task_color_detail = ucfirst($task_color);
			$table_color = $wpdb->prefix . "custom_project_color";
			$colors = $wpdb->get_row("SELECT * FROM {$table_color} WHERE project_color = '$task_color_detail'");
			
			$get_task_by_date = "https://kanbanflow.com/api/v1/tasks/". $id ."/events?from=".$format_import_date."T00:00Z&to=".$format_import_date."T23:59Z&" . $token;
			$task_by_date = @file_get_contents($get_task_by_date);
			$get_task_event = json_decode($task_by_date, true);		
			foreach($get_task_event['events'] as $key => $task_event){
				foreach($task_event['detailedEvents'] as $task_event['taskId'] => $detailed_events){
					if($detailed_events['changedProperties'][0]['property'] == 'totalSecondsSpent'){
						$old_hour = $detailed_events['changedProperties'][0]['oldValue'];
						$new_hour = $detailed_events['changedProperties'][0]['newValue'];
					}
				}	
			}


			$task_hour_diff = $new_hour - $old_hour;
			$task_hour_real = gmdate("H:i:s", $new_hour);
			$task_hour = time_format($task_hour_real);
			
			$task_name = $task_details['name'];		
			$task_label = $task_label_details[0]['name'];
			$task_person = $current_user_fullname;
			$task_description = $task_details['description'];
			$task_project_name = $colors->project_category;	

			$task_label_explode = explode(' ', $task_label);
			$last_word = array_pop($task_label_explode);
			if($last_word == "AB"){
				$client_name = str_replace(" AB","",$task_label);
			}else{
				$client_name = $task_label;
			}	

			if($task_label == 'SEOWeb'){
				$client_name = 'SEOWeb Solutions';
			}
			
			$task_hour_decimal = decimalHours($task_hour);
			$total_hour_decimal += $task_hour_decimal;		
			if($task_hour != null){
				$tasks_data[] = array(
				'task_name'			=> $task_name, 
				'task_hour'			=> $task_hour,
				'task_label'		=> $client_name,
				'task_person'		=> $task_person,
				'task_description' 	=> $task_description,
				'task_color' 		=> $task_color,
				'task_project_name' => $task_project_name,
				'user_id'			=> $user_id,
				'import_date'		=> $import_date,
				'import_day'		=> $import_day,
				'import_week' 		=> $import_week
				);
			}
		}
	}
	$import_total_hour_decimal = decimalHours($import_total_hour);	
	$current_total = $total_hour_decimal + $import_total_hour_decimal;	
	$total_hour_real =  convertTime($current_total);
	$total_hour = time_format($total_hour_real);
	$tasks_data['total_hour'] = $total_hour;
	return $tasks_data;
}
/* ==================================== END TIMESHEET IMPORT TASK ==================================== */

/* ==================================== TIMESHEET SAVE TASK ==================================== */
function save_task_timesheet($save_timesheet_task_data){
	$save_timesheet_form_data = array();
	parse_str($save_timesheet_task_data, $save_timesheet_form_data);
	$total_hours_worked = $save_timesheet_form_data['total_hours_worked'];	
	$hour_balance = $save_timesheet_form_data['hour_balance'];	
	global $wpdb;			
	$table_name = $wpdb->prefix . "custom_timesheet";
	$total_hour_decimal = "";
	foreach($save_timesheet_form_data['task_name'] as $key => $task_names){
		$result_task_name_explode = explode('-', $task_names);
		$task_name_import = trim($result_task_name_explode['0']);		
		$task_name_explode = explode(' ', $task_name_import);
		$task_name_array = array();
		foreach($task_name_explode as $exploded_task_name){			
			if(strtoupper($exploded_task_name) !== $exploded_task_name){
				$exploded_task_name = strtolower($exploded_task_name);
			}
			$task_name_array[] = $exploded_task_name;
		}		
		$task_name = implode(" ",$task_name_array);
		$task_suffix = trim($result_task_name_explode['1']);		
		$task_label = $save_timesheet_form_data['task_label'][$key];
		$task_hour = $save_timesheet_form_data['task_hour'][$key];
		$task_person = $save_timesheet_form_data['task_person'][$key];
		$task_description = $save_timesheet_form_data['task_description'][$key];
		$task_color = $save_timesheet_form_data['task_color'][$key];
		$task_project_name = $save_timesheet_form_data['task_project_name'][$key];
		$user_id = $save_timesheet_form_data['user_id'][$key];		
		$date_now = $save_timesheet_form_data['import_date'];
		$day_now = $save_timesheet_form_data['import_day'];
		$week_number = $save_timesheet_form_data['import_week'];					
		
		$user_id = $save_timesheet_form_data['user_id'][$key];
		
		$insert = $wpdb->insert( $table_name , array( 
			'task_name' => $task_name,
			'task_suffix' => $task_suffix,
			'date_now' => $date_now,
			'day_now' => ucfirst($day_now),
			'week_number' => $week_number,		
			'task_hour' => $task_hour,
			'task_label' => $task_label,	
			'task_person' => $task_person,
			'task_description' => htmlentities($task_description),
			'task_color' => $task_color,
			'task_project_name' => $task_project_name,
			'user_id' => $user_id,
			'status' => 1
		),	
		array( '%s', '%s' ));
		$submit_id = $wpdb->insert_id;
		$save_timesheet_form_data['id'][] = $submit_id;
		$task_hour_decimal = decimalHours($task_hour);
		$total_hour_decimal += $task_hour_decimal;
	}
	
	$total_hour_decimal_round = round($total_hour_decimal, 2);
	$total_month_hours_worked = $total_hours_worked + $total_hour_decimal_round;
	$total_month_hour_balance = $hour_balance + $total_hour_decimal_round;
	$save_timesheet_form_data['total_month_hours_worked'] = $total_month_hours_worked;
	$save_timesheet_form_data['total_month_hour_balance'] = $total_month_hour_balance;
	return $save_timesheet_form_data;
}
/* ==================================== END TIMESHEET SAVE TASK ==================================== */

/* ==================================== TIMESHEET EDIT TASK ==================================== */
function task_edit_timesheet_task($data_id){
	$data_id_explode = explode("_", $data_id);
	$data_id = $data_id_explode[0];
	$current_task_hour = $data_id_explode[1];
	global $wpdb;			
	$table_name = $wpdb->prefix . "custom_timesheet";	
	$table_name_client = $wpdb->prefix . "custom_client"; 
	$clients = $wpdb->get_results("SELECT * FROM {$table_name_client}");	
	$table_name_project = $wpdb->prefix . "custom_project";
	$projects = $wpdb->get_results("SELECT * FROM {$table_name_project}");
	$table_name_task = $wpdb->prefix . "custom_task";
	$tasks = $wpdb->get_results("SELECT * FROM {$table_name_task}");
	
	$results_edit = $wpdb->get_row("SELECT * FROM $table_name WHERE ID = $data_id");
	$id = $results_edit->ID;
	$task_name = (isset($results_edit->task_name)) ? $results_edit->task_name : "";
	$task_suffix = (isset($results_edit->task_suffix)) ? $results_edit->task_suffix : "";
	$task_person = (isset($results_edit->task_person)) ? $results_edit->task_person : "";
	$task_description = (isset($results_edit->task_description)) ? $results_edit->task_description : "";
	$task_hour = (isset($results_edit->task_hour)) ? $results_edit->task_hour : "";
	$date_now = (isset($results_edit->date_now)) ? $results_edit->date_now : "";
	$day_now = (isset($results_edit->day_now)) ? $results_edit->day_now : "";
	$week_number = (isset($results_edit->week_number)) ? $results_edit->week_number : "";
	if($results_edit->user_id != ""){
		$user_id = $results_edit->user_id;
		$user_data = get_userdata($user_id);
		$user_name = $user_data->display_name;
	}else{
		$user_id = "";
		$user_name = "";
	}
	
	foreach ($clients as $client){
		$client_option .= "<optgroup label = ".$client->client_name.">";
		foreach ($projects as $project){
			if($client->client_name == $project->project_client){
				$client_option .= "<option>".$project->project_name."</option>";
			}
		}
		$client_option .= "</optgroup>";
	}
	
	foreach ($tasks as $task){
		if($task_name != $task->task_name){
			$task_name_option .= "<option>".format_task_name($task->task_name)."</option>";
		}
	}
	$html ='
	<form method="post" id="update_timesheet">
	<fieldset>
	<label class="modal_label">Task Name</label>
	<select class="modal_select" name="task_name">
	<option value="'.$task_name.'">'.format_task_name($task_name).'</option>
	'.$task_name_option.'
	</select>
	<label class="modal_label">Task Suffix</label>
	<input type="text" class="modal_input" name="task_suffix" value="'.$task_suffix.'">
	<div class="textareas">
	<textarea class="modal_textarea1" name="task_description" placeholder="Notes (optional)">'.$task_description.'</textarea>
	<textarea class="modal_textarea2" name="task_hour" placeholder="0:00">'.$task_hour.'</textarea>
	</div>
	<div id="update_kanban_'.$id.'" class="button_1 update_button">Update</div>
	<div style="display: none" class="loader"></div>
	<input type="hidden" name="task_person" value="'.$task_person.'" />
	<input type="hidden" name="user_id" value="'.$user_id.'" />
	<input type="hidden" name="date_now" value="'.$date_now.'" />
	<input type="hidden" name="day_now" value="'.$day_now.'" />
	<input type="hidden" name="week_number" value="'.$week_number.'" />
	<input type="hidden" name="id" value="'.$id.'" />
	<input type="hidden" name="current_task_hour" value="'.$current_task_hour.'" />
	</fieldset>
	</form>
	';
	
	return $html;
}
/* ==================================== END TIMESHEET EDIT TASK ==================================== */

/* ==================================== TIMESHEET UPDATE TASK ==================================== */
function update_task_timesheet($update_timesheet_data){
	global $wpdb;			
	$table_name = $wpdb->prefix . "custom_timesheet";
	$update_timesheet_form_data = array();
	parse_str($update_timesheet_data, $update_timesheet_form_data);
	$task_id			= $update_timesheet_form_data['id'];	
	$task_name			= $update_timesheet_form_data['task_name'];
	$task_hour_unformat	= $update_timesheet_form_data['task_hour'];
	$task_description	= $update_timesheet_form_data['task_description'];
	$task_suffix		= $update_timesheet_form_data['task_suffix'];
	
	$task_hour = time_format($task_hour_unformat);
	
	$update = $wpdb->update( $table_name , array( 
		'task_name'			=> $task_name,
		'task_hour'			=> $task_hour,
		'task_description'	=> $task_description,
		'task_suffix'		=> $task_suffix
	),
	array( 'ID' => $task_id ),
	array( '%s', '%s' ));
		
	$current_task_hour					= $update_timesheet_form_data['current_task_hour'];
	$current_task_hour_decimal 			= decimalHours($current_task_hour);
	$current_task_hour_decimal_round 	= round($current_task_hour_decimal, 2);
	
	$task_hour_decimal = decimalHours($task_hour);
	$task_hour_decimal_round = round($task_hour_decimal, 2);
	
	$current_total_hour		= $update_timesheet_form_data['current_total_hour'];
	$current_total_hour_decimal = decimalHours($current_total_hour);
	$current_total_hour_decimal_round 	= round($current_total_hour_decimal, 2);
	
	$current_total_hours_worked	= $update_timesheet_form_data['total_hours_worked'];
	$current_hour_balance		= $update_timesheet_form_data['hour_balance'];	
	
	if($current_task_hour_decimal_round > $task_hour_decimal_round){
		$task_hour_diff = $current_task_hour_decimal_round - $task_hour_decimal_round;
		$total_task_hour = $current_total_hour_decimal_round - $task_hour_diff;
		$total_hours_worked = $current_total_hours_worked - $task_hour_diff;
		$hour_balance = $current_hour_balance - $task_hour_diff;
	}elseif($current_task_hour_decimal_round < $task_hour_decimal_round){
		$task_hour_diff = $task_hour_decimal_round - $current_task_hour_decimal_round;
		$total_task_hour = $current_total_hour_decimal_round + $task_hour_diff;
		$total_hours_worked = $current_total_hours_worked + $task_hour_diff;
		$hour_balance = $current_hour_balance + $task_hour_diff;
	}
	$update_timesheet_form_data['task_hour'] = time_format($task_hour);
	$update_timesheet_form_data['total_task_hour'] = gmdate('H:i', floor($total_task_hour * 3600));
	$update_timesheet_form_data['total_hours_worked'] = $total_hours_worked;
	$update_timesheet_form_data['hour_balance'] = $hour_balance;
	return $update_timesheet_form_data;
}
/* ==================================== END TIMESHEET UPDATE TASK ==================================== */

/* ==================================== TIMESHEET ADD TASK ==================================== */
function timesheet_add_task($day_date_week){	
	global $wpdb;
	$table_name_task = $wpdb->prefix . "custom_task";
	$tasks = $wpdb->get_results("SELECT * FROM {$table_name_task}");
	$table_color = $wpdb->prefix . "custom_project_color";
	$colors = $wpdb->get_results("SELECT * FROM {$table_color}");
	$table_clients = $wpdb->prefix . "custom_client";
	$clients = $wpdb->get_results("SELECT * FROM {$table_clients}");
	
	$current_user = wp_get_current_user();
	$user_id = get_current_user_id();
	$current_user_name = $current_user->data->display_name;
	$current_user_role = $current_user->roles['0'];
	
	$day_date_week_explode = explode('_', $day_date_week);	
	$day_now = $day_date_week_explode[0];
	$date_now = $day_date_week_explode[1];
	$week_number  = $day_date_week_explode[2];
	
	$task_names = array();
	foreach($tasks as $task){ 
		$task_names[] = $task->task_name;
	}
	sort($task_names);
	foreach ($task_names as $task_name){
		$task_name_option .= "<option>".$task_name."</option>";
	}
	
	$project_categories = array();
	foreach($colors as $color){
		$project_categories[] = $color->project_category;
	}
	sort($project_categories);
	foreach($project_categories as $project_category){
		$color_name = $wpdb->get_results("SELECT project_color FROM {$table_color} WHERE project_category = '$project_category'");
		$task_color_option .= '<option value="' . $color_name[0]->project_color . '">'. $project_category.  '</option>';
	}
	
	$client_names = array();
	foreach($clients as $client){
		$client_names[] = $client->client_name;
	}
	sort($client_names);
	foreach($client_names as $client_name){
		$task_client_option .= '<option>'.$client_name.'</option>';
	}
	
	$html='
	<form method="post" id="add_task_timesheet">
	<fieldset>
	<div class="timesheet_task_name">
	<label class="modal_label">Task Name</label>
	<select class="modal_select" name="task_name">
	'.$task_name_option.'
	</select>
	</div>
	<div class="timesheet_task_suffix">
	<label class="modal_label">Task Suffix</label>
	<input type="text" class="modal_input" name="task_suffix" value="">	
	</div>	
	<div class="timesheet_task_label">
	<label class="modal_label">Client</label>
	<select class="modal_input" name="task_label">
	'.$task_client_option.'
	</select>
	</div>
	<div class="timesheet_task_color">
	<label class="modal_label">Project</label>
	<select class="modal_input" name="task_color">
	'.$task_color_option.'
	</select>
	</div>
	<div class="textareas">
	<div class="timesheet_task_description">
	<label class="modal_label">Description</label>
	<textarea class="modal_textarea1" name="task_description" placeholder="Notes (optional)"></textarea>
	</div>
	<div class="timesheet_task_hour">
	<label class="modal_label">Hours worked</label>
	<textarea class="modal_textarea2" name="task_hour" placeholder="00:00"></textarea>
	</div>
	</div>
	<div class="button_1 save_add_button button_import">Add Entry</div>
	<input type="hidden" name="task_person" value="'.$current_user_name.'" />
	<input type="hidden" name="user_id" value="'.$user_id.'" />
	<input type="hidden" name="date_now" value="'.$date_now.'" />
	<input type="hidden" name="day_now" value="'.$day_now.'" />
	<input type="hidden" name="week_number" value="'.$week_number.'" />
	<input type="hidden" name="current_hour" value="'.$current_hour.'" />
	</fieldset>
	</form>
	';
	return $html;
}

/* ==================================== END TIMESHEET ADD TASK ==================================== */

/* ==================================== TIMESHEET SAVE ADD TASK ==================================== */
function save_add_task_timesheet($save_add_timesheet_task_data){
	$save_add_timesheet_form_data = array();
	parse_str($save_add_timesheet_task_data, $save_add_timesheet_form_data);
	
	global $wpdb;
	$table_name = $wpdb->prefix . "custom_timesheet";
	$table_color = $wpdb->prefix . "custom_project_color";
	$colors = $wpdb->get_results("SELECT * FROM {$table_color}");	
	
	$task_name = $save_add_timesheet_form_data['task_name'];
	$task_suffix = $save_add_timesheet_form_data['task_suffix'];	
	$date_now = $save_add_timesheet_form_data['date_now'];
	$day_now = $save_add_timesheet_form_data['day_now'];
	$week_number = $save_add_timesheet_form_data['week_number'];
	$task_hour_unformat = $save_add_timesheet_form_data['task_hour'];
	$task_label = $save_add_timesheet_form_data['task_label'];
	$task_person = $save_add_timesheet_form_data['task_person'];
	$task_description = $save_add_timesheet_form_data['task_description'];
	$task_color = $save_add_timesheet_form_data['task_color'];
	$user_id = $save_add_timesheet_form_data['user_id'];
	
	$current_hour = $save_add_timesheet_form_data['current_hour'];
	$task_hour = time_format($task_hour_unformat);
	$task_hour_decimal = decimalHours($task_hour);
	$task_current_hour_decimal = decimalHours($current_hour);
	
	$sum = $task_current_hour_decimal + $task_hour_decimal;
	$total_hour =  gmdate('H:i', floor($sum * 3600));
	
	foreach($colors as $color){
		if($color->project_color == $task_color ){
			$task_category = $color->project_category;
		}
	}
	
	$insert = $wpdb->insert( $table_name , array( 
		'task_name' => $task_name,
		'task_suffix' => $task_suffix,
		'date_now' => $date_now,
		'day_now' => $day_now,
		'week_number' => $week_number,		
		'task_hour' => $task_hour,
		'task_label' => $task_label,	
		'task_person' => $task_person,
		'task_description' => $task_description,
		'task_color' => $task_color,
		'task_project_name' => $task_category,
		'user_id' => $user_id,
		'status' => 1
	),	
	array( '%s', '%s' ));
	$submit_id = $wpdb->insert_id;	
	$save_add_timesheet_form_data['id'] = $submit_id;
	$save_add_timesheet_form_data['task_category'] = $task_category;
	$save_add_timesheet_form_data['total_hour'] = $total_hour;
	$save_add_timesheet_form_data['task_hour_format'] = time_format($task_hour);
	
	return $save_add_timesheet_form_data;
}
/* ==================================== END TIMESHEET SAVE ADD TASK ==================================== */

/* ==================================== TIMESHEET DELETE TASK ==================================== */
function confirm_delete_task($delete_form_details){	
	parse_str($delete_form_details, $delete_form_data);
	global $wpdb;
	$task_id = $delete_form_data['timesheet_task_id'];
	$current_hour = $delete_form_data['timesheet_task_current_hour'];
	$total_hours_worked = $delete_form_data['timesheet_task_total_hours_worked'];
	$hour_balance = $delete_form_data['timesheet_task_hour_balance'];
	$timesheet_delete_day = $delete_form_data['timesheet_delete_day'];
	
	
	$table_name = $wpdb->prefix . "custom_timesheet";
	$timesheet_data = $wpdb->get_results("SELECT * FROM {$table_name} WHERE ID ='$task_id'");
	
	$task_hour = $timesheet_data[0]->task_hour;
	
	$task_hour_decimal = decimalHours($task_hour);
	$task_current_hour_decimal = decimalHours($current_hour);
	
	$difference = $task_current_hour_decimal - $task_hour_decimal;
	$total_hour =  gmdate('H:i', floor($difference * 3600));
	
	$wpdb->query( "DELETE FROM {$table_name} WHERE ID = '$task_id'" );
	
	$tasks_data[] = array(
	'task_id'	=> $task_id, 
	'task_hour'	=> $total_hour
	);
	$total_hour_decimal_round = round($task_hour_decimal, 2);
	$total_month_hours_worked = $total_hours_worked - $total_hour_decimal_round;
	$total_month_hour_balance = $hour_balance - $total_hour_decimal_round;
	$tasks_data['total_month_hours_worked'] = $total_month_hours_worked;
	$tasks_data['total_month_hour_balance'] = $total_month_hour_balance;
	$tasks_data['timesheet_delete_day'] = $timesheet_delete_day;
	return $tasks_data;
}
/* ==================================== END TIMESHEET DELETE TASK ==================================== */

/* ==================================== TIMESHEET DONE TODAY ==================================== */
function done_today_edit($data_id){	
	global $wpdb;
	$table_name = $wpdb->prefix . "custom_timesheet";
	$task = $wpdb->get_row("SELECT * FROM {$table_name} WHERE ID='$data_id'");	
	$task_done_today = unserialize($task->task_done_today);
	$task_hour = $task->task_hour;
	$array_count = count($task_done_today);
	$task_exist = "";
	$counter = 1;
	if($task_done_today != null){
		foreach($task_done_today as $done_today_task){	
			if($counter == $array_count){
				$last_class = 'done_today_last';
			}
			$done_today_task_explode = explode('_', $done_today_task);
			$task_done_today_description = $done_today_task_explode[0];
			$task_done_today_hours = $done_today_task_explode[1];
			$task_exist .= '
			<li class="done_today_list '. $last_class .'" id="done_today_'. $counter .'">
			<div class="full_width">		
			<input type="hidden" id="hidden_list_'. $counter .'" name="submit_done_today[]" value="'. $task_done_today_description .'_' .$task_done_today_hours. '"/>		
			<div class="one_half"><p class="task_done_today_description">'. $task_done_today_description .'</p></div>
			<div class="one_fourth"><p class="task_done_today_hours">'. $task_done_today_hours .'</p></div>
			<div class="one_fourth last">
			<div id="done_today_edit_'. $counter .'" class="done_today_edit button_2 done_today_action_button">E</div>
			<div id="done_today_delete_'. $counter .'" class="confirm done_today_delete button_2 done_today_action_button">D</div>
			</div>
			</div>
			</li>
			<div class="edit_div" id="edit_div_'. $counter .'" style="display:none;">
			<div class="full_width">		
			<div class="one_half"><textarea type="text" id="done_today_description_edit_area_'. $counter .'" class="done_today_edit_area" /></textarea></div>
			<div class="one_fourth"><textarea type="text" id="done_today_task_hour_edit_area_'. $counter .'" class="done_today_edit_area" /></textarea></div>		
			<div class="one_fourth last">
			<div id="check_edit_'. $counter .'" class="check_edit"></div>
			</div>
			</div>
			</div>
			';
			$counter++;
		}
	}
	$html ='
	<form id="done_today_form">
	<input type="hidden" name="task_id" class="task_id" value="'. $data_id .'" />	
	<h3 class="task_hour">Task Hour: '. $task_hour .'</h3>
	<div class="full_width">
	<div class="done_today_task_container">
	'. $task_exist .'
	</div>
	<div class="one_half">
	<textarea class="task_done_today_description" name="task_done_today_description" placeholder="Notes (optional)"></textarea>
	</div>
	<div class="one_fourth">
	<textarea class="task_done_today_hours" name="task_done_today_hours" placeholder="0:00"></textarea>
	</div>
	<div class="one_fourth last">
	<div class="add_more_done_today button_2">Add More</div>
	</div>
	</div>
	<div class="button_1 add_task_done_today">Add</div>
	<div style="display: none;" class="loader"></div>
	</form>
	';
	
	return $html;
}

function task_done_today_save($done_today_form){
	global $wpdb;
	$table_name = $wpdb->prefix . "custom_timesheet";
	$save_add_done_today_form_data = array();
	parse_str($done_today_form, $save_add_done_today_form_data);
	$task_id = $save_add_done_today_form_data['task_id'];	
	$tasks_hour = $save_add_done_today_form_data['submit_done_today'];
	$task_hour_format_array = array();
	foreach($tasks_hour as $task_hour){
		$task_hour_explode = explode('_', $task_hour);
		$task = $task_hour_explode[0];
		$hour = $task_hour_explode[1];
		$format_hour = time_format($hour);	
		$task_hour_format_array[] = $task ."_". $format_hour ."_". $task_id;
	}
	
	$serialize = serialize($task_hour_format_array);
	
	$update = $wpdb->update( $table_name , array( 
	'task_done_today'			=> $serialize
	),
	array( 'ID' => $task_id ),
	array( '%s', '%s' ));	

}
/* ==================================== END TIMESHEET DONE TODAY ==================================== */

/* ==================================== STAFF TIMESHEET ==================================== */
function staff_timesheet($staff_timesheet_data){
	global $wpdb;
	// $staff_timesheet_data_array = array();
	// parse_str($staff_timesheet_data, $staff_timesheet_data_array);
	$staff_timesheet_data_explode = explode('_', $staff_timesheet_data);
	$current_user = wp_get_current_user();
	$current_user_name = $current_user->data->display_name;
	
	$person_name = ($staff_timesheet_data_explode[0] != 'null' ? $staff_timesheet_data_explode[0] : $current_user_name);
	$week_number = $staff_timesheet_data_explode[1];
	$picked_year = $staff_timesheet_data_explode[2];	
	$picked_month = $staff_timesheet_data_explode[3];
	$start_date = $staff_timesheet_data_explode[4];
	$end_date = $staff_timesheet_data_explode[5];
	
	$table_name = $wpdb->prefix . "custom_timesheet"; 
	$table_name_person = $wpdb->prefix . "custom_person";
	$persons = $wpdb->get_results("SELECT * FROM {$table_name_person} WHERE person_fullname ='$person_name'");	
	
	$year = date('Y');
	$month_number = date('m');
	$week = getStartAndEndDate($week_number, $year);

	

	// if($person_name == null || $person_name == 'null'){
		// $person_name = $current_user_name;
		// $check_same_user = 'yes';
	// }
	
	if($person_name == $current_user_name){
		$check_same_user = 'yes';
	}
	
		
	$timesheet_month_details = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$person_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$picked_month/$picked_year', '%d/%m/%Y') AND STR_TO_DATE('31/$picked_month/$picked_year', '%d/%m/%Y')");
	
	$timesheet_month_stats = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$person_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('31/$month_number/$year', '%d/%m/%Y')"); 
		
	foreach($persons as $person){
		$person_hour_per_day = $person->person_hours_per_day;
	}	
	
	if($person_hour_per_day != null ){
		$hour_per_day = $person_hour_per_day;
	}else{
		$hour_per_day = 8;
	}
	
	$holiday_date = array();
	foreach($timesheet_month_stats as $timesheet_month_stat){
		$task_name = format_task_name($timesheet_month_stat->task_name);
		if($task_name == 'Holiday'){
			$date = date('Y/m/d', strtotime($timesheet_month_stat->date_now));
			$holiday_date[] = $date;
		}
	}
	$holiday_count = count($holiday_date);
	$holiday_hours = $holiday_count * $hour_per_day;
	
	$date1 = "$year/$month_number/01";
	$date2 =  date('Y/m/d');
	$working_days = getWorkingDays($date1, $date2);
	$worked_hours = ($working_days * $hour_per_day) - $holiday_hours;
	
	$total_month_hours = 0;
	foreach($timesheet_month_stats as $timesheet_month_stat){
		$task_name = format_task_name($timesheet_month_stat->task_name);
		if($task_name != 'Holiday'){			
			$task_hour 			= $timesheet_month_stat->task_hour;
			$task_hour_decimal 	= round(decimalHours($task_hour),2);
			$total_month_hours	+= $task_hour_decimal;
		}					
	}
	$total_hours_worked = $total_month_hours;
	$hour_balance = ($total_hours_worked - $worked_hours);
	
	$month_total_hour_decimal = 0;
	$total_holiday_hour = 0;
	foreach($timesheet_month_details as $timesheet_month_detail){
		$task_hour 						= $timesheet_month_detail->task_hour;
		$task_hour_decimal 				= decimalHours($task_hour);
		$month_total_hour_decimal		+= $task_hour_decimal;
		$task_name						= format_task_name($timesheet_month_detail->task_name);
		
		if($task_name == 'Holiday'){
			if($timesheet_month_detail->task_hour != null){
				$task_hour = $timesheet_month_detail->task_hour;
				$holiday_hour 	= decimalHours($task_hour);
			}else{
				$holiday_hour = 8;			
			}
			$total_holiday_hour += $holiday_hour;
		}
	}
	$total_month_hour =  gmdate('H:i', floor($month_total_hour_decimal * 3600));
	
	foreach($timesheet_month_details as $timesheet_month_detail){
		$task_name = format_task_name($timesheet_month_detail->task_name);				
		if($task_name == 'Holiday Work'){
			$holiday_work_date = $timesheet_month_detail->date_now;
			$holiday_work_details = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$person_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('01/$month_number/$year', '%d/%m/%Y') AND STR_TO_DATE('31/$month_number/$year', '%d/%m/%Y') AND date_now ='$holiday_work_date'"); 
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
	
	$timesheet_details = $wpdb->get_results("SELECT * FROM {$table_name} WHERE task_person = '$person_name' AND STR_TO_DATE(date_now, '%d/%m/%Y') BETWEEN STR_TO_DATE('$start_date', '%d/%m/%Y') AND STR_TO_DATE('$end_date', '%d/%m/%Y') AND week_number = $week_number");	
	$monday_total_hour_decimal = 0;
	foreach($timesheet_details as $timesheet_detail){
		if(strtolower($timesheet_detail->day_now) == 'monday'){
			$task_hour 						= $timesheet_detail->task_hour;
			$task_hour_decimal 				= decimalHours($task_hour);
			$monday_total_hour_decimal		+= $task_hour_decimal;
		}
	}	
	$total_hour_monday =  gmdate('H:i', floor($monday_total_hour_decimal * 3600));
	
	$tuesday_total_hour_decimal = 0;
	foreach($timesheet_details as $timesheet_detail){
		if(strtolower($timesheet_detail->day_now) == 'tuesday'){
			$task_hour 						= $timesheet_detail->task_hour;
			$task_hour_decimal 				= decimalHours($task_hour);
			$tuesday_total_hour_decimal		+= $task_hour_decimal;
		}
	}	
	$total_hour_tuesday =  gmdate('H:i', floor($tuesday_total_hour_decimal * 3600));
	
	$wednesday_total_hour_decimal = 0;
	foreach($timesheet_details as $timesheet_detail){
		if(strtolower($timesheet_detail->day_now) == 'wednesday'){
			$task_hour 						= $timesheet_detail->task_hour;
			$task_hour_decimal 				= decimalHours($task_hour);
			$wednesday_total_hour_decimal	+= $task_hour_decimal;
		}
	}	
	$total_hour_wednesday =  gmdate('H:i', floor($wednesday_total_hour_decimal * 3600));
	
	$thursday_total_hour_decimal = 0;
	foreach($timesheet_details as $timesheet_detail){
		if(strtolower($timesheet_detail->day_now) == 'thursday'){
			$task_hour 						= $timesheet_detail->task_hour;
			$task_hour_decimal 				= decimalHours($task_hour);
			$thursday_total_hour_decimal	+= $task_hour_decimal;
		}
	}	
	$total_hour_thursday =  gmdate('H:i', floor($thursday_total_hour_decimal * 3600));
	
	$friday_total_hour_decimal = 0;
	foreach($timesheet_details as $timesheet_detail){
		if(strtolower($timesheet_detail->day_now) == 'friday'){
			$task_hour 						= $timesheet_detail->task_hour;
			$task_hour_decimal 				= decimalHours($task_hour);
			$friday_total_hour_decimal		+= $task_hour_decimal;
		}
	}	
	$total_hour_friday =  gmdate('H:i', floor($friday_total_hour_decimal * 3600));
	
	$saturday_total_hour_decimal = 0;
	foreach($timesheet_details as $timesheet_detail){
		if(strtolower($timesheet_detail->day_now) == 'saturday'){
			$task_hour 						= $timesheet_detail->task_hour;
			$task_hour_decimal 				= decimalHours($task_hour);
			$saturday_total_hour_decimal	+= $task_hour_decimal;
		}
	}	
	$total_hour_saturday =  gmdate('H:i', floor($saturday_total_hour_decimal * 3600));
	
	$sunday_total_hour_decimal = 0;
	foreach($timesheet_details as $timesheet_detail){
		if(strtolower($timesheet_detail->day_now) == 'sunday'){
			$task_hour 						= $timesheet_detail->task_hour;
			$task_hour_decimal 				= decimalHours($task_hour);
			$sunday_total_hour_decimal		+= $task_hour_decimal;
		}
	}	
	$total_hour_sunday =  gmdate('H:i', floor($sunday_total_hour_decimal * 3600));
	
	$month_name = date("F", strtotime($picked_month));
	
	
	$rounded_total_month_hour = round($month_total_hour_decimal, 2);
	
	$timesheet_details['check_same_user'] = $check_same_user;
	$timesheet_details['total_holiday_work'] = $total_holiday_work;
	$timesheet_details['worked_hours'] = $worked_hours;
	$timesheet_details['total_hours_worked'] = $total_hours_worked;
	$timesheet_details['hour_balance'] = round($hour_balance, 2);
	$timesheet_details['holiday_hours'] = $holiday_hours;
	$timesheet_details['month_name'] = $month_name;
	$timesheet_details['year_name'] = $picked_year;
	$timesheet_details['total_holiday_hour'] = $total_holiday_hour;
	$timesheet_details['rounded_total_month_hour'] = $rounded_total_month_hour;
	$timesheet_details['total_month_hour'] = $total_month_hour;
	$timesheet_details['total_hour_monday'] = $total_hour_monday;
	$timesheet_details['total_hour_tuesday'] = $total_hour_tuesday;
	$timesheet_details['total_hour_wednesday'] = $total_hour_wednesday;
	$timesheet_details['total_hour_thursday'] = $total_hour_thursday;
	$timesheet_details['total_hour_friday'] = $total_hour_friday;
	$timesheet_details['total_hour_saturday'] = $total_hour_saturday;
	$timesheet_details['total_hour_sunday'] = $total_hour_sunday;
	$timesheet_details['person_name'] = $person_name;
	$timesheet_details['week_start'] = $start_date;
	$timesheet_details['week_end'] = $end_date;
	// print_var($total_hour_monday);
	return $timesheet_details;
}
/* ==================================== END STAFF TIMESHEET ==================================== */
?>