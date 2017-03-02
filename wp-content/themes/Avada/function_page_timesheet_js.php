<script>
var d = new Date();
var weekday = new Array(7);
weekday[0]=  "Sunday";
weekday[1] = "Monday";
weekday[2] = "Tuesday";
weekday[3] = "Wednesday";
weekday[4] = "Thursday";
weekday[5] = "Friday";
weekday[6] = "Saturday";
var day_now_id = weekday[d.getDay()].toLowerCase();

/* ==================================== IMPORT TASK ==================================== */
jQuery(document).on('click', '.import_kanban_task', function(){
	var div_id = jQuery(this).attr('id');		
	var div_id_split = div_id.split('_');
	var import_day = div_id_split[3];
	var current_hour = jQuery('#'+import_day+' .total_hours .task_total_hour h3').text();
	var day_not_current_capital = import_day.toLowerCase().replace(/\b[a-z]/g, function(letter) {
		return letter.toUpperCase();
	});		
	var import_date = jQuery('.' + import_day + '_date').val();
	var import_week = jQuery('.' + import_day + '_week').val();
	var date_hour_day_week = import_date +"_"+ current_hour +"_"+ import_day +"_"+ import_week;
	jQuery(".status_message").fadeIn( "slow", function() {
		jQuery(".status_message p").text("Importing data from Kanban. This will take some time. Please be patient.");
	});
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'import_task_kanban',
			'date_hour_day_week' : date_hour_day_week
		},
		success: function (data) {
			var parsed = jQuery.parseJSON(data);	
			var import_total_hours = timeStringToFloat(parsed.total_hour);
			var total_person_working_hours_per_day = jQuery('#current-quota-time').val();

			if(import_total_hours >= total_person_working_hours_per_day){
		    	jQuery('#tabs .'+import_day+' a').removeClass('red-day');
		    	jQuery('#tabs .'+import_day+' a').addClass('green-day');				
			}

			// return false;		
			jQuery.each(parsed, function(index, value){
				var task_name = (value.task_name == "") ? "--" : value.task_name;
				var task_hour = (value.task_hour == "") ? "--" : value.task_hour;
				var task_project_name = (value.task_project_name == "") ? "--" : value.task_project_name;
				var task_color = (value.task_color == "") ? "--" : value.task_color;
				var task_label = (value.task_label == "") ? "--" : value.task_label;
				var person_full_name = (value.task_person == "") ? "--" : value.task_person;
				var import_date = (value.import_date == "") ? "--" : value.import_date;
				var import_day = (value.import_day == "") ? "--" : value.import_day;
				var import_week = (value.import_week == "") ? "--" : value.import_week;
				var user_id = (value.user_id == "") ? "--" : value.user_id;
				var task_description = (value.task_description == "") ? "--" : value.task_description;
				var short_description = jQuery.trim(task_description).substring(0, 20).split(" ").slice(0, -1).join(" ") + "...";

				if(index != 'total_hour'){						
					jQuery("<li class='data_list_"+index+" data_list_"+import_day+"'><p>"+task_name+"</p></li>").insertBefore('#'+import_day+'.tab_content .task_name li:last');												
					jQuery("<li class='data_list_"+index+" data_list_"+import_day+"'><p>"+task_hour+"</p></li>").insertBefore('#'+import_day+'.tab_content .task_hour li:last');
					jQuery("<li class='data_list_"+index+" data_list_"+import_day+"'><p>"+task_label+"</p></li>").insertBefore('#'+import_day+'.tab_content .task_label li:last');
					jQuery("<li class='data_list_"+index+" data_list_"+import_day+"'><p>"+task_project_name+"</p></li>").insertBefore('#'+import_day+'.tab_content .task_color li:last');
					jQuery("<li class='data_list_"+index+" data_list_"+import_day+"'><p>"+person_full_name+"</p></li>").insertBefore('#'+import_day+'.tab_content .task_person li:last');
					jQuery("<div id='accordian_"+index+"' class='accordian'><h5 class='toggle'><a href='#'><li class='data_list_"+import_day+"'>"+short_description+"<span class='arrow'></span></li></a></h5></div>").insertBefore('#'+import_day+'.tab_content .task_description div:last');						
					jQuery('#'+import_day+'.tab_content .task_description #accordian_'+index).append("<div class='toggle-content' style='display: none;'>"+task_description+"</div>");
					
					jQuery('<li class="data_list_'+index+' data_list_'+import_day+'">&nbsp;</li>').insertBefore('#'+import_day+'.tab_content .task_edit li:last');
					jQuery('<li class="data_list_'+index+' data_list_'+import_day+'">&nbsp;</li>').insertBefore('#'+import_day+'.tab_content .task_delete li:last');
					jQuery('<li class="data_list_'+index+' data_list_'+import_day+'">&nbsp;</li>').insertBefore('#'+import_day+'.tab_content .task_done_today li:last');

					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='import_date' value='"+import_date+"' />");
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='import_day' value='"+import_day+"' />");
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='import_week' value='"+import_week+"' />");
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='task_name[]' value='"+task_name+"' />");
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='task_hour[]' value='"+task_hour+"' />");												
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='task_label[]' value='"+task_label+"' />");
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='task_project_name[]' value='"+task_project_name+"' />");
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='task_color[]' value='"+task_color+"' />");
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='task_person[]' value='"+person_full_name+"' />");												
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='user_id[]' value='"+user_id+"' />");												
					jQuery('#'+import_day+'.tab_content .import_save').append('<input type="hidden" name="task_description[]" value="'+task_description+'" />');			
					jQuery('#'+import_day+'.tab_content .import_save').append("<input type='hidden' name='task_color[]' value='"+task_color+"' />");
					var total_hours_worked = jQuery('.month_details .total_hours_worked').text();
					var hour_balance = jQuery('.month_details .hour_balance').text();
					jQuery('.tab_content.active .import_save').append("<input type='hidden' name='total_hours_worked' value='"+total_hours_worked+"' />");
					jQuery('.tab_content.active .import_save').append("<input type='hidden' name='hour_balance' value='"+hour_balance+"' />");
				}				
				jQuery('.import_message').show();
				jQuery(".status_message").delay(500).fadeOut('slow');
			});	
			
			if(parsed.no_task){
				var empty_task_date = change_date_format(import_date,'full_date');
				jQuery('<p class="no_task text_red">No Hours Detected for '+empty_task_date+'<span>You can add task manually by clicking "Add Task" button.</span></p>').insertBefore('#'+import_day+' .total_hours');
				jQuery(".no_task").delay(7000).fadeOut('slow');
			}
			jQuery('#'+import_day+'.tab_content .total_hours .task_total_hour h3').text(parsed.total_hour);
			jQuery('.clear_add_buttons').show();
			jQuery('#save_kanban_'+import_day).show();
			jQuery('#clear_kanban_'+import_day).show();
			trigger_accordion_toggle();
		},
		error: function (data) {
			alert('error');
		}				
	});
});
/* ==================================== END IMPORT TASK ==================================== */


/* ==================================== SPLAN CLOCK ==================================== */
jQuery(document).ready(function(){

// var thetime = jQuery("#serverClock").val();

// var thetime = '13:14:15';
// this would be something like:
var thetime = '<?php echo date('H:i:s') ;?>';
var arr_time = thetime.split(':');
var ss = arr_time[2];
var mm = arr_time[1];
var hh = arr_time[0];

var update_ss = setInterval(updatetime, 1000);

function updatetime() {
    ss++;
    if (ss < 10) {
        ss = '0' + ss;
    }
    if (ss == 60) {
        ss = '00';
        mm++;
        if (mm < 10) {
            mm = '0' + mm;
        }
        if (mm == 60) {
            mm = '00';
            hh++;
            if (hh < 10) {
                hh = '0' + hh;
            }
            if (hh == 24) {
                hh = '00';
            }
            jQuery("#splan_hours").html(hh);
        }
        jQuery("#splan_minutes").html(mm);
    }
    // $("#seconds").html(ss);
}

	// jQuery('.import_save_button').click(function(){

	// 	jQuery('.kanban_save_loader').show();

	// 	var save_timesheet_task_data = jQuery('.import_save').serialize();

	// 	jQuery.ajax({

	// 		type: "POST",

	// 		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',

	// 		data:{

	// 			'type' : 'task_save_timesheet',

	// 			'save_timesheet_task_data' : save_timesheet_task_data

	// 		},

	// 		success: function (data) {

	// 			jQuery('.kanban_save_loader').hide();

	// 			var parsed = jQuery.parseJSON(data);				

	// 			jQuery.each(parsed.id, function(index, value){

	// 				jQuery('.tab_content.active .task_delete').append("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='delete_kanban_"+day_now_id+"_"+value+"' class='button_1 confirm delete_button delete_kanban_"+day_now_id+"'>D</div></li>");

	// 				jQuery('.tab_content.active .task_edit').append("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='edit_kanban_"+day_now_id+"_"+value+"' class='button_1 edit_button edit_kanban_"+day_now_id+"'>E</div></li>");

	// 				jQuery('.tab_content.active .task_done_today').append("<li class=	'data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='done_today_kanban_"+day_now_id+"_"+value+"' class='button_1 done_today_button done_today_kanban_"+day_now_id+"'>Done Today</div></li>");

	// 				jQuery('.data_list_'+index).addClass('timesheet_data_id_'+value);

	// 				jQuery('.tab_content.active .task_description #accordian_'+index).addClass("accordian_"+value);					

	// 			});

	// 			jQuery('#save_kanban_'+day_now_id).hide();

	// 			jQuery('#clear_kanban_'+day_now_id).hide();

	// 			jQuery('#'+day_now_id+' .import_message').hide();

	// 			jQuery('.month_details .total_hours_worked').text(parsed.total_month_hours_worked);

	// 			jQuery('.month_details .hour_balance').text(parsed.total_month_hour_balance);				

	// 		},

	// 		error: function (data) {

	// 			alert('error');

	// 		}				

	// 	});

	// });

});

/* ==================================== SAVE NEW ENTRY ROW ==================================== */
jQuery(document).on('click', '.add_new_row_entry_btn', function(){
	var current_element = jQuery(this);
	var day = current_element.hide().next().show().closest('.tab_content.active').attr('id');
	var taskname = jQuery('#'+day).find('.new_row_entry_taskname').val();
	var hours = jQuery('#'+day).find('.new_row_entry_hours').val();
	var client = jQuery('#'+day).find('.new_row_entry_client').val();
	var project = jQuery('#'+day).find('.new_row_entry_project').val();
	var description = jQuery('#'+day).find('.new_row_entry_description').val();
	var date = jQuery('#'+day).find('.tab_date').val();
	var week =  jQuery('#'+day).find('.tab_week').val();
	var person_id = jQuery('#current-person-id').val();

	var remove_comma_hour = hours.replace(',','.');
	if(hours != ""){
		if(parseFloat(remove_comma_hour) < 1){
			var new_hour = '0'+remove_comma_hour;
		}else{
			var new_hour = remove_comma_hour;
		}

		var task_description_count = description.length;
		var isValidTime = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(new_hour);
		
		//Convert and validation for time format. Convert Decimal to hour format.
		if(isValidTime == true){
			var hour_input =  timesheet_format(new_hour);
		}else{
			var reg = /^\d+(\.\d{0,3})?$/.test(new_hour);
			//If Decimal format
			if(reg == true){
				var hour_input = convertToHHMM(new_hour);
			}else{
				jQuery(".status_message").fadeIn( "slow", function() {
					jQuery(".status_message p").html("<p class='error-msg'>Invalid Time Format.</p>");
				});
				jQuery(".status_message").delay(500).fadeOut('slow');
				current_element.show().next().hide()
				return false;
			}
		}
	}else{
		hour_input = "00:00";
	}


	var new_entry = {
		'taskname' : taskname,
		'hours' : hour_input,
		'client' : client,
		'project' : project,
		'description' : description,
		'day' : day,
		'date' : date,
		'week' : week,
		'person_id' : person_id
	}


	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'add_new_row_entry_timesheet',
			'data_info' : new_entry
		},
		success: function (data) {

			var parsed = jQuery.parseJSON(data);

			var task_description = (parsed.task_description == "") ? "--" : parsed.task_description;
			var short_description = jQuery.trim(task_description).substring(0, 20).split(" ").slice(0, -1).join(" ") + "...";

			//Update TOtal hours
			jQuery('#'+parsed.day_now+' .total_hours .task_total_hour h3').text(parsed.total_hours);
			//New Insert Taskname
			jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'>"+parsed.task_name+"</li>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_name li:last');
			//New Insert hours
			jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'>"+parsed.task_hour+"</li>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_hour li:last');
			//New Insert Client
			jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'>"+parsed.task_label+"</li>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_label li:last');
			//New Insert Project
			jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'>"+parsed.task_label+"</li>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_color li:last');
			//New Insert Description
			jQuery("<div class='accordian accordian_"+parsed.insert_id+"'><h5 class='toggle'><a href='#''><li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'>"+short_description+"<span class='arrow'></span></li></a></h5><div class='toggle-content' style='display: none;'>"+task_description+"</div></div>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_description  div:last');
			trigger_accordion_toggle();
			//New Insert Edit
			jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'><div id='edit_kanban_"+parsed.day_now+"_"+parsed.insert_id+"' class='button_1 edit_button edit_kanban'>E</div></li>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_edit li:last');
			//New Insert Delete
			jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'><div id='delete_kanban_"+parsed.day_now+"_"+parsed.insert_id+"' class='button_1 delete_button delete_edit_kanban'>D</div></li>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_delete li:last');
			//New Insert DOne Today
			if(jQuery('#'+parsed.day_now+' .person_task_timesheet .task_done_today li:last').length){
				jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'><div id='done_today_kanban_"+parsed.day_now+"_"+parsed.insert_id+"' class='button_1 done_today_button done_today_kanban'>Done Today</div></li>").insertBefore('#'+parsed.day_now+' .person_task_timesheet .task_done_today li:last');	
			}else{
				jQuery("<li class='data_list_"+parsed.day_now+" timesheet_data_id_"+parsed.insert_id+"'><div id='done_today_kanban_"+parsed.day_now+"_"+parsed.insert_id+"' class='button_1 done_today_button done_today_kanban'>Done Today</div></li>").insertAfter('#'+parsed.day_now+' .person_task_timesheet .task_done_today h5');	
			}
			
			jQuery('#'+parsed.day_now).find('.new_row_entry_taskname').prop('selectedIndex',0);
			jQuery('#'+parsed.day_now).find('.new_row_entry_hours').val('');
			jQuery('#'+parsed.day_now).find('.new_row_entry_client').prop('selectedIndex',0);
			jQuery('#'+parsed.day_now).find('.new_row_entry_project').prop('selectedIndex',0);;
			jQuery('#'+parsed.day_now).find('.new_row_entry_description').val('');

			if(parsed.green_day == 1){
				jQuery('#tabs li.'+parsed.day_now+' a').removeClass('red-day').addClass('green-day');
			}
			current_element.show().next().hide();;
		
		},
		error: function (data) {
			alert('error');
		}				
	});	


});

/* ==================================== SAVE IMPORT TASK ==================================== */
jQuery(document).ready(function(){
	jQuery('.import_save_button').click(function(){
		jQuery('.kanban_save_loader').show();
		var save_timesheet_task_data = jQuery('.import_save').serialize();
		jQuery.ajax({
			type: "POST",
			url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
			data:{
				'type' : 'task_save_timesheet',
				'save_timesheet_task_data' : save_timesheet_task_data
			},
			success: function (data) {
				jQuery('.kanban_save_loader').hide();
				var parsed = jQuery.parseJSON(data);
				var day_now_id = jQuery('.tabs_li.active a p:first-child()').text().toLowerCase();	
				jQuery.each(parsed.id, function(index, value){


					jQuery('.tab_content.active .task_edit .data_list_'+index).replaceWith("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='edit_kanban_"+day_now_id+"_"+value+"' class='button_1 edit_button edit_kanban'>E</div></li>");
					jQuery('.tab_content.active .task_delete .data_list_'+index).replaceWith("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='delete_kanban_"+day_now_id+"_"+value+"' class='button_1 confirm delete_button delete_kanban_"+day_now_id+"'>D</div></li>");
					jQuery('.tab_content.active .task_done_today .data_list_'+index).replaceWith("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='done_today_kanban_"+day_now_id+"_"+value+"' class='button_1 done_today_button done_today_kanban_"+day_now_id+"'>Done Today</div></li>");


					// jQuery('.tab_content.active .task_delete').append("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='delete_kanban_"+day_now_id+"_"+value+"' class='button_1 confirm delete_button delete_kanban_"+day_now_id+"'>D</div></li>");

					// jQuery('.tab_content.active .task_edit').append("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='edit_kanban_"+day_now_id+"_"+value+"' class='button_1 edit_button edit_kanban'>E</div></li>");

					// jQuery('.tab_content.active .task_done_today').append("<li class='data_list_"+day_now_id+" timesheet_data_id_"+value+"'><div id='done_today_kanban_"+day_now_id+"_"+value+"' class='button_1 done_today_button done_today_kanban_"+day_now_id+"'>Done Today</div></li>");

					jQuery('.data_list_'+index).addClass('timesheet_data_id_'+value);
					jQuery('#accordian_'+index+' h5.toggle a li').addClass('timesheet_data_id_'+value);
					jQuery('.tab_content.active .task_description #accordian_'+index).addClass("accordian_"+value);					
				});
				jQuery('#save_kanban_'+day_now_id).hide();
				jQuery('#clear_kanban_'+day_now_id).hide();
				jQuery('#'+day_now_id+' .import_message').hide();
				jQuery('.month_details .total_hours_worked').text(parsed.total_month_hours_worked);
				jQuery('.month_details .hour_balance').text(parsed.total_month_hour_balance);				
			},
			error: function (data) {
				alert('error');
			}				
		});
	});
});
/* ==================================== END SAVE IMPORT TASK ==================================== */

/* ==================================== DELETE TASK ==================================== */
jQuery(document).ready(function(){
	jQuery( "#dialog_form_timesheet_delete_task" ).dialog({
		autoOpen: false,
		height: 300,
		width: 350,
		modal: true,
		close: function() {
			jQuery('.loader').hide();
		}
	});
	jQuery( "#sickness_dialog_box" ).dialog({
		autoOpen: false,
		height: 300,
		width: 350,
		modal: true,
		close: function() {
			jQuery('.loader').hide();
		}
	});		
});

jQuery(document).on('click', '.delete_edit_kanban', function(){
	jQuery("#dialog_form_timesheet_delete_task").dialog("open");
	var div_id = jQuery(this).attr('id');
	var div_id_split = div_id.split("_");
	var data_day = div_id_split[2];
	var data_id = div_id_split[3];
			
	var current_hour = jQuery('#'+data_day+'.tab_content .total_hours .task_total_hour h3').text();
	var total_hours_worked = jQuery('.month_details .total_hours_worked').text();
	var hour_balance = jQuery('.month_details .hour_balance').text();
	jQuery("#dialog_form_timesheet_delete_task #timesheet_task_id").val(data_id);
	jQuery("#dialog_form_timesheet_delete_task #timesheet_task_current_hour").val(current_hour);
	jQuery("#dialog_form_timesheet_delete_task #timesheet_task_total_hours_worked").val(total_hours_worked);
	jQuery("#dialog_form_timesheet_delete_task #timesheet_delete_day").val(data_day);
	jQuery("#dialog_form_timesheet_delete_task #timesheet_task_hour_balance").val(hour_balance);
	jQuery('#loader_id_'+data_id).show();		
});

jQuery(document).on('click', '#dialog_form_timesheet_delete_task .delete_confirm', function(){
	jQuery('#dialog_form_timesheet_delete_task .loader').show();
	var delete_form_details = jQuery('#timesheet_delete_task_form').serialize();
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'confirm_delete_task',
			'delete_form_details' : delete_form_details
		},
		success: function (data) {
			jQuery('#dialog_form_timesheet_delete_task .loader').hide();
			jQuery("#dialog_form_timesheet_delete_task").dialog("close");
			var parsed = jQuery.parseJSON(data);
			var task_hour = parsed[0].task_hour;

			var total_person_work_hours = jQuery('#current-quota-time').val();

			var task_id = parsed[0].task_id;				
			var timesheet_delete_day = parsed.timesheet_delete_day;				
			jQuery('#'+timesheet_delete_day+' .total_hours .task_total_hour h3').html(task_hour);	
			jQuery("#loader_id_"+task_id).hide();
			jQuery('#'+timesheet_delete_day+' .timesheet_data_id_'+task_id).hide();
			jQuery('#'+timesheet_delete_day+' .task_description .accordian_'+task_id).hide();
			jQuery('.action_message p').text("Task Deleted");
			jQuery('.action_message').fadeIn( "slow", function() {
				jQuery(".action_message").delay(1000).fadeOut('slow');
			});
			jQuery('.month_details .total_hours_worked').text(parsed.total_month_hours_worked);
			jQuery('.month_details .hour_balance').text(parsed.total_month_hour_balance);

			if(total_person_work_hours > timeStringToFloat(task_hour)){
				jQuery('#tabs li.'+timesheet_delete_day+' a').removeClass('green-day').addClass('red-day');
			}

		},
		error: function (data) {
			alert('error');
		}				
	});
});
/* ==================================== END DELETE TASK ==================================== */

/* ==================================== EDIT TASK ==================================== */
jQuery(document).ready(function(){
	jQuery( "#dialog_form_timesheet_edit_task" ).dialog({
		autoOpen: false,
		height: 300,
		width: 350,
		modal: true,
		close: function() {
		}
	});
});

jQuery(document).on('click', '.edit_kanban', function(){
	var div_id = jQuery(this).attr('id');
	var div_id_split = div_id.split("_");
	var data_day = div_id_split[2];
	var data_div_id = div_id_split[3];	
	var current_task_hour = jQuery('#'+data_day+' .task_hour .timesheet_data_id_'+data_div_id).text();
	var data_id = data_div_id+"_"+current_task_hour;
	jQuery('#loader_id_'+data_div_id).show();
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'task_edit_timesheet_task',
			'data_id' : data_id
		},
		success: function (data) {
			jQuery('#loader_id_'+data_div_id).hide();
			jQuery('#dialog_form_timesheet_edit_task').dialog('open');
			jQuery('#dialog_form_timesheet_edit_task').html(data);
		},
		error: function (data) {
			alert('error');
		}				
	});
});
/* ==================================== END EDIT TASK ==================================== */

/* ==================================== UPDATE TASK ==================================== */
jQuery(document).on('click', '.update_button', function(){
	jQuery('.update_timesheet .loader').show();
	var current_total_hour = jQuery('.tab_content.active .total_hours .task_total_hour h3').text();
	var total_hours_worked = jQuery('.month_details .total_hours_worked').text();
	var hour_balance = jQuery('.month_details .hour_balance').text();
	var update_timesheet_task_data = jQuery('#update_timesheet').serialize() + "&current_total_hour=" + current_total_hour + "&total_hours_worked=" + total_hours_worked + "&hour_balance=" + hour_balance;
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'task_update_timesheet',
			'update_timesheet_task_data' : update_timesheet_task_data
		},
		success: function (data) {
			jQuery('.update_timesheet .loader').hide();
			var parsed = jQuery.parseJSON(data);

			console.log(parsed);
			jQuery(".action_message p").text("Task Updated");
				jQuery(".action_message").fadeIn( "slow", function() {
				jQuery(".action_message").delay(1000).fadeOut('slow');
			});
			var task_id = parsed.id;
			var task_name = parsed.task_name;
			var task_suffix = parsed.task_suffix;
			var task_hour = parsed.task_hour;
			
			if(task_suffix == ""){
				var task_name_suffix = task_name;
				}else{
				var task_name_suffix = task_name +' - '+ task_suffix;
			}
			var task_name_suffix_count = task_name_suffix.length;
			if(task_name_suffix_count <= 25){
				var task_name = task_name_suffix;
				}else{
				var task_name = jQuery.trim(task_name_suffix).substring(0, 25).split(" ").slice(0, -1).join(" ") + "...";
			}
										
			var task_description = parsed.task_description;
			var short_description = jQuery.trim(task_description).substring(0, 20).split(" ").slice(0, -1).join(" ") + "...";
			var total_day_hours = 0;

			jQuery('.tab_content.active .person_task_timesheet .task_hour li').each(function(i, obj) {
				var hour = jQuery(this).text();
				total_day_hours += timeStringToFloat(hour);
			});
			console.log(deciHours(total_day_hours));
			jQuery('.task_name .timesheet_data_id_'+task_id).text(task_name);
			jQuery('.task_hour .timesheet_data_id_'+task_id).text(task_hour);
			jQuery('info_div.same_user .timesheet_data_id_'+task_id+ '.second_column').text(task_hour);
			jQuery('.task_total_hour h3').text(parsed.total_task_hour);
			jQuery('.month_details .total_hours_worked').text(parsed.total_hours_worked);
			jQuery('.month_details .hour_balance').text(parsed.hour_balance);
			var toggle = jQuery('.accordian_'+task_id+' .timesheet_data_id_'+task_id).find('span');
			jQuery('.accordian_'+task_id+' .timesheet_data_id_'+task_id).text(short_description);
			jQuery('.accordian_'+task_id+' .timesheet_data_id_'+task_id).append(toggle);
			jQuery('.accordian_'+task_id+' .toggle-content').text(task_description);
			jQuery( "#dialog_form_timesheet_edit_task" ).dialog('close');
		},
		error: function (data) {
			alert('error');
		}
	});
});
/* ==================================== END UPDATE TASK ==================================== */

/* ==================================== DONE TODAY TASK ==================================== */
jQuery(document).ready(function(){
	jQuery( ".button_help" ).click(function() {
		jQuery( ".help_note" ).toggle("medium");
	});
	
	jQuery( "#dialog_form_timesheet_done_today" ).dialog({
		autoOpen: false,
		height: 200,
		width: 350,
		modal: true,
		close: function() {
		}
	});
});

jQuery(document).on('click', '.done_today_button', function(){
	jQuery('.timesheet_loader').show();
	var div_id = jQuery(this).attr('id');
	var div_id_split = div_id.split('_');
	var data_id = div_id_split[4];	
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'done_today_edit',
			'data_id' : data_id
		},
		success: function (data) {
			jQuery('.timesheet_loader').hide();
			jQuery( "#dialog_form_timesheet_done_today" ).dialog('open');
			jQuery( "#dialog_form_timesheet_done_today" ).html(data);
		},
		error: function (data) {
			alert('error');
		}
	});
});

var div_id_last = jQuery('.done_today_last').attr('id');	
if(div_id_last != null && div_id_last != 'undefined'){
	var div_id_last_split = div_id_last.split('_');
	var last_counter = div_id_last_split[2];	
	var counter = parseInt(last_counter, 10) + 1;
}else{
	var counter = 1;
}	

jQuery(document).on('click', '.add_more_done_today', function(){
	var task_hour = jQuery().text();
	var task_done_today_description = jQuery('textarea.task_done_today_description').val();
	var task_done_today_hours = jQuery('textarea.task_done_today_hours').val();	
	jQuery('.done_today_task_container').append('<li class="done_today_list" id="done_today_'+counter+'">'
	+'<div class="full_width">'		
	+'<input type="hidden" id="hidden_list_'+counter+'" name="submit_done_today[]" value="'+task_done_today_description+'_'+task_done_today_hours+'"/>'		
	+'<div class="one_half"><p class="task_done_today_description">'+task_done_today_description+'</p></div>'
	+'<div class="one_fourth"><p class="task_done_today_hours">'+task_done_today_hours+'</p></div>'		
	+'<div class="one_fourth last">'		
	+'<div id="done_today_edit_'+counter+'" class="done_today_edit button_2 done_today_action_button">E</div>'
	+'<div id="done_today_delete_'+counter+'" class="confirm done_today_delete button_2 done_today_action_button">D</div>'
	+'</div>'
	+'</div>'
	+'</li>'
	+'<div class="edit_div" id="edit_div_'+counter+'" style="display:none;">'
	+'<div class="full_width">'		
	+'<div class="one_half"><textarea type="text" id="done_today_description_edit_area_'+counter+'" class="done_today_edit_area" /></textarea></div>'
	+'<div class="one_fourth"><textarea type="text" id="done_today_task_hour_edit_area_'+counter+'" class="done_today_edit_area" /></textarea></div>'		
	+'<div class="one_fourth last">'
	+'<div id="check_edit_'+counter+'" class="check_edit"></div>'
	+'</div>'
	+'</div>'
	+'</div>'
	);
	jQuery(".task_done_today_description").val("");
	jQuery(".task_done_today_hours").val("");
	counter++;
});	

jQuery(document).on('click', '.add_task_done_today', function(){
	jQuery('#done_today_form .loader').show();
	var done_today_form = jQuery('#done_today_form').serialize();
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'task_done_today_save',
			'done_today_form' : done_today_form
		},
		success: function (data) {
			jQuery('#done_today_form .loader').hide();
			jQuery( "#dialog_form_timesheet_done_today" ).dialog('close');
		},
		error: function (data) {
			alert('error');
		}
	});
});
/* DONE TODAY ACTIONS */
jQuery(document).on('click', '.done_today_edit', function(){
		var div_id = jQuery(this).attr('id');
		var div_id_split = div_id.split('_');
		var data_id = div_id_split[3];
		var edit_task_done_today_description = jQuery('#done_today_'+data_id+' p.task_done_today_description').text();
		var edit_task_done_today_hours = jQuery('#done_today_'+data_id+' p.task_done_today_hours').text();
		jQuery('#done_today_'+data_id).hide();
		jQuery('#edit_div_'+data_id).css('display', 'block');
		jQuery('#done_today_description_edit_area_'+data_id).text(edit_task_done_today_description);
		jQuery('#done_today_task_hour_edit_area_'+data_id).text(edit_task_done_today_hours);
});

jQuery(document).on('click', '.check_edit', function(){
	var div_id = jQuery(this).attr('id');
	var div_id_split = div_id.split('_');
	var data_id = div_id_split[2];	
	var edit_task_done_today_description = jQuery('#done_today_description_edit_area_'+data_id).val();
	var edit_task_done_today_hours = jQuery('#done_today_task_hour_edit_area_'+data_id).val();
	jQuery('#edit_div_'+data_id).css('display', 'none');
	jQuery('#done_today_'+data_id).show();
	jQuery('#done_today_'+data_id+' p.task_done_today_description').text(edit_task_done_today_description);
	jQuery('#done_today_'+data_id+' p.task_done_today_hours').text(edit_task_done_today_hours);			
	jQuery('#hidden_list_'+data_id).val(edit_task_done_today_description +"_"+ edit_task_done_today_hours);
});		

jQuery(document).on('click', '.done_today_delete', function(){
	var div_id = jQuery(this).attr('id');
	var div_id_split = div_id.split('_');
	var data_id = div_id_split[3];	
	jQuery('#done_today_'+data_id).remove();
	jQuery('#edit_div_'+data_id).remove();
});
/* END DONE TODAY ACTIONS */
/* ==================================== END DONE TODAY TASK ==================================== */

/* ==================================== CLEAR TASK ==================================== */
jQuery(document).ready(function(){
	jQuery('#clear_kanban_'+day_now_id).click(function(){
		jQuery('#'+day_now_id+' form.import_save').contents().remove();
		jQuery('#'+day_now_id+' .data_list_'+day_now_id).remove();
		jQuery('#'+day_now_id+' .task_description .accordian').remove();
		jQuery('#'+day_now_id+' .task_total_hour h3').html("00:00");
		jQuery('#'+day_now_id+' .clear_add_buttons').hide();
		jQuery('#'+day_now_id+' .import_message').hide();
	});
});
/* ==================================== END CLEAR TASK ==================================== */

/* ==================================== ADD ENTRY TASK ==================================== */
jQuery(document).ready(function(){
	jQuery( "#dialog_form_timesheet_add_kanban_task" ).dialog({
		autoOpen: false,
		height: 300,
		width: 350,
		modal: true,
		close: function() {
		}
	});
});

jQuery(document).on('click', '.add_task', function(){
	var div_id = jQuery(this).attr('id');		
	var div_id_split = div_id.split('_');
	var add_day = div_id_split[3];
	var current_hour = jQuery('#'+add_day+' .total_hours .task_total_hour h3').text();
	var day_not_current_capital = add_day.toLowerCase().replace(/\b[a-z]/g, function(letter) {
		return letter.toUpperCase();
	});
	var add_date = jQuery('.' + add_day + '_date').val();
	var date_format = change_date_format(add_date, "dd/M");
	var add_week = jQuery('#week_number').val();
	var day_date_week = add_day +"_"+ add_date +"_"+ add_week +"_"+ current_hour;
	jQuery(".top_loader").show();
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'timesheet_add_task',
			'day_date_week' : day_date_week
		},
		success: function (data) {
			jQuery(".top_loader").hide();			
			var check_modal_title = jQuery('div[aria-describedby^="dialog_form_timesheet_add_kanban_task"] div.modal_header p.modal_date').length;
			if(check_modal_title == 0){
				jQuery('div[aria-describedby^="dialog_form_timesheet_add_kanban_task"] .ui-widget-header').append('<div class="modal_header"><p class="modal_title">New Time Entry</p><p class="modal_date">'+day_not_current_capital +", "+ date_format+'</p></div>');
			}else{
				jQuery('div[aria-describedby^="dialog_form_timesheet_add_kanban_task"] div.modal_header p.modal_date').text(day_not_current_capital +", "+ date_format);
			}
			jQuery("#dialog_form_timesheet_add_kanban_task").html(data);
			jQuery('#dialog_form_timesheet_add_kanban_task').dialog('open');
		},
		error: function (data) {
			alert('error');
		}				
	});
});
/* ==================================== END ADD ENTRY TASK ==================================== */

/* ==================================== SAVE ADD ENTRY TASK ==================================== */
jQuery(document).on('click', '.save_add_button', function(){
	var save_add_timesheet_task_data = jQuery('#add_task_timesheet').serialize();
	jQuery.ajax({
		type: "POST",
		url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
		data:{
			'type' : 'task_save_add_timesheet',
			'save_add_timesheet_task_data' : save_add_timesheet_task_data
		},
		success: function (data) {
			var parsed = jQuery.parseJSON(data);
			console.log(parsed);
			jQuery('#dialog_form_timesheet_add_kanban_task').dialog('close');
			jQuery(".action_message p").text("Task Added");
			jQuery(".action_message").fadeIn( "slow", function() {
				jQuery(".action_message").delay(1000).fadeOut('slow');
			});

			if(parsed.task_suffix == ""){
				var task_name_suffix = parsed.task_name;
				}else{
				var task_name_suffix = parsed.task_name +' - '+ parsed.task_suffix;
			}
			var total_hour = (parsed.total_hour == "") ? "--" : parsed.total_hour;
			var task_hour_format = (parsed.task_hour_format == "") ? "--" : parsed.task_hour_format;
			var task_label = (parsed.task_label == "") ? "--" : parsed.task_label;
			var task_category = (parsed.task_category == "") ? "--" : parsed.task_category;
			var task_person = (parsed.task_person == "") ? "--" : parsed.task_person;
			var task_description = (parsed.task_description == "") ? "--" : parsed.task_description;
			var description_length = task_description.length;
			if(description_length >= 20){
				var short_description = jQuery.trim(task_description).substring(0, 20).split(" ").slice(0, -1).join(" ") + "...";
			}else{
				var short_description = task_description +"...";
			}
			
							
			jQuery('.tab_content.active .task_name').append('<li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'">'+task_name_suffix+'</li>');
			jQuery('.tab_content.active .task_hour').append('<li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'">'+task_hour_format+'</li>');
			jQuery('.tab_content.active .task_label').append('<li class="client_info data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'">'+task_label+'</li>');
			jQuery('.tab_content.active .task_color').append('<li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'">'+task_category+'</li>');
			jQuery('.tab_content.active .task_person').append('<li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'">'+task_person+'</li>');
			jQuery('.tab_content.active .task_description').append('<div class="accordian accordian_'+parsed.id+'"><h5 class="toggle"><a href="#"><li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'">'+short_description+'<span class="arrow"></span></li></a></h5></div>');						
			jQuery('.tab_content.active .task_description .accordian_'+parsed.id).append("<div class='toggle-content' style='display: none;'>"+task_description+"</div>");
			jQuery('.tab_content.active .task_delete').append('<li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'"><div id="delete_kanban_'+day_now_id+'_'+parsed.id+'" class="button_1 confirm delete_button delete_kanban_'+day_now_id+'">Delete</div></li>');			
			jQuery('.tab_content.active .task_edit').append('<li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'"><div id="edit_kanban_'+day_now_id+'_'+parsed.id+'" class="button_1 edit_button edit_kanban">E</div></li>');			
			jQuery('.tab_content.active .task_edit').append("<div id='loader_id_"+parsed.id+"' style='display: none;' class='loader timesheet_loader'></div>");
			jQuery('.tab_content.active .task_done_today').append('<li class="data_list_'+day_now_id+' timesheet_data_id_'+parsed.id+'"><div id="done_today_kanban_'+day_now_id+'_'+parsed.id+'" class="button_1 done_today_button done_today_kanban_'+day_now_id+' done_today_not_current">Done Today</div></li>');
			jQuery('.tab_content.active .total_hours .task_total_hour h3').text(total_hour);

			var total_person_work_hours =  jQuery('#current-quota-time').val();

			if(total_person_work_hours <= timeStringToFloat(total_hour)){
				jQuery('#tabs li.'+parsed.day_now+' a').removeClass('red-day').addClass('green-day');
			}

			trigger_accordion_toggle();
		},
		error: function (data) {
			alert('error');
		}
	});
});
/* ==================================== END SAVE ADD ENTRY TASK ==================================== */
function day_sort(value, day, check_same_user){
	if(value.task_suffix == ""){
		var task_name_suffix = format_task_name(value.task_name);
	}else{
		var task_name_suffix = format_task_name(value.task_name) +' - '+ value.task_suffix;
	}
	var task_name_suffix_count = task_name_suffix.length;							
	if(task_name_suffix_count <= 25){
		var task_name = task_name_suffix;
	}else{
		var task_name = jQuery.trim(task_name_suffix).substring(0, 25).split(" ").slice(0, -1).join(" ") + "...";
	}
	var task_description = (value.task_description == "") ? "--" : value.task_description;
	var task_description_count = task_description.length;
	var task_hour = (value.task_hour == "") ? "--" : value.task_hour;
	var task_label = (value.task_label == "") ? "--" : value.task_label;
	var task_project_name = (value.task_project_name == "") ? "--" : value.task_project_name;
	var task_person = (value.task_person == "") ? "--" : value.task_person;
	if(task_description_count <= 25){
		var short_description = task_description;
	}else{
		var short_description = jQuery.trim(task_description).substring(0, 25).split(" ").slice(0, -1).join(" ") + "...";
	}
	
	//Taskname
	jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'">'+task_name+'</li>').insertBefore('#'+day+' .task_name li:last');
	//Hours
	jQuery('<li class="client_info data_list_'+day+' timesheet_data_id_'+value.ID+'">'+task_hour+'</li>').insertBefore('#'+day+' .task_hour li:last');
	//Client
	jQuery('<li class="client_info data_list_'+day+' timesheet_data_id_'+value.ID+'">'+task_label+'</li>').insertBefore('#'+day+' .task_label li:last');
	//Project
	jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'">'+task_project_name+'</li>').insertBefore('#'+day+' .task_color li:last');
	//Description
	jQuery('<div class="accordian accordian_'+value.ID+'"><h5 class="toggle"><a href="#"><li class="data_list_'+day+' timesheet_data_id_'+value.ID+'">'+short_description+'<span class="arrow"></span></li></a></h5><div class="toggle-content" style="display: none;">'+task_description+'</div></div>').insertBefore('#'+day+' .task_description div:last' );

	if(check_same_user == 'yes'){
		jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'"><div id="edit_kanban_'+day+'_'+value.ID+'" class="button_1 edit_button edit_kanban">E</div></li>').insertBefore('#'+day+' .task_edit li:last');

		jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'"><div id="delete_kanban_'+day+'_'+value.ID+'" class="button_1 delete_button delete_edit_kanban">D</div></li>').insertBefore('#'+day+' .task_delete li:last');
		jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'"><div id="done_today_kanban_'+day+'_'+value.ID+'" class="button_1 done_today_button done_today_kanban">Done Today</div></li>').insertBefore('#'+day+' .task_done_today li:last');
	}else{
		jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'">&nbsp;</li>').insertBefore('#'+day+' .task_edit li:last');
		jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'">&nbsp;</li>').insertBefore('#'+day+' .task_delete li:last');
		jQuery('<li class="data_list_'+day+' timesheet_data_id_'+value.ID+'">&nbsp;</li>').insertBefore('#'+day+' .task_done_today li:last');		
	}
}
/* ==================================== SEARCH PERSON TASK BY NAME ==================================== */
jQuery(document).ready(function(){
	jQuery('form.staff_timesheet_form .person_name').change(function() {
		jQuery('.timesheet .left_div .loader').show();
		console.log('change user');
		var current_tab_date = jQuery('.tab_content.active .tab_date').val();
		var date = change_date_format(current_tab_date, 'yyyy-month-dd');
		var start_date = new Date(date);
		var end_date = new Date(date);
		var index = start_date.getDay();
		if(index == 0) {
			start_date.setDate(start_date.getDate() - 6);   
			end_date.setDate(end_date.getDate() + 1);
        }else if(index == 1) {
			start_date.setDate(start_date.getDate());
			end_date.setDate(end_date.getDate() + 7);               
        }else if(index == 2) {
			start_date.setDate(start_date.getDate() - 1);
			end_date.setDate(end_date.getDate() + 6); 
		}else if(index == 3) {
			start_date.setDate(start_date.getDate() - 2);
			end_date.setDate(end_date.getDate() + 5); 
		}else if(index == 4) {
			start_date.setDate(start_date.getDate() - 3);
			end_date.setDate(end_date.getDate() + 4); 
		}else if(index == 5) {
			start_date.setDate(start_date.getDate() - 4);
			end_date.setDate(end_date.getDate() + 3); 
		}else if(index == 6) {
			start_date.setDate(start_date.getDate() - 5);
			end_date.setDate(end_date.getDate() + 2); 
		}
		
		var dates_range = get_date_range(start_date, end_date);
		
		var person_name = jQuery('.staff_timesheet_form .person_name').val();
		var week_number = jQuery('.staff_timesheet_form .week_number').val();
		var picked_year = jQuery('.staff_timesheet_form .picked_year').val();
		var picked_month = jQuery('.staff_timesheet_form .picked_month').val();
		var staff_timesheet_data = person_name +'_'+ week_number +'_'+ picked_year +'_'+ picked_month +'_'+ new_date_format(dates_range[0]) +'_'+ new_date_format(dates_range[6]);
		
		
		jQuery.ajax({
			type: "POST",
			url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
			data:{
				'type' : 'search_staff_timesheet',
				'staff_timesheet_data' : staff_timesheet_data				
			},
			success: function (data) {
				jQuery('.timesheet .left_div .loader').hide();
				var parsed = jQuery.parseJSON(data);
				var check_same_user = parsed.check_same_user;
				var month_name = parsed.month_name;
				var year_name = parsed.year_name;
				var rounded_total_month_hour = parsed.rounded_total_month_hour;
				var worked_hours = parsed.worked_hours;
				var total_hours_worked = parsed.total_hours_worked;
				var hour_balance = parsed.hour_balance;
				var holiday_hours = parsed.holiday_hours;
				var total_holiday_work = parsed.total_holiday_work;
				var total_month_hour = parsed.total_month_hour;
				var total_holiday_hour = parsed.total_holiday_hour;
				var total_hour_monday = parsed.total_hour_monday;
				var total_hour_tuesday = parsed.total_hour_tuesday;
				var total_hour_wednesday = parsed.total_hour_wednesday;
				var total_hour_thursday = parsed.total_hour_thursday;
				var total_hour_friday = parsed.total_hour_friday;
				var total_hour_saturday = parsed.total_hour_saturday;
				var total_hour_sunday = parsed.total_hour_sunday;
				var person_hours_per_day = parsed.person_hours_per_day;
				var person_id = parsed.person_id;	

				jQuery('#current-quota-time').val(person_hours_per_day);
				jQuery('#current-person-id').val(person_id);

				jQuery('.week_section h3.week').html('Week: '+change_date_format(parsed.week_start, 'dd/M/Y')+" - "+change_date_format(parsed.week_end, 'dd/M/Y'));
				jQuery('.header_person_name h1').html(parsed.person_name+'\'\s Timesheet');
								
				var days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"]; 

				jQuery.each(days, function(index, value){
					jQuery('#'+value+ ' .data_list_'+value).remove();
					jQuery('#'+value+ ' .data_title .accordian').remove();
					if(check_same_user != 'yes'){
						jQuery('.import_button').hide();
					}else{
						jQuery('.import_button').show();
					}
				});
				jQuery('.total_hours .task_total_hour h3').text('00:00');
					//Monday
				    time_array = parsed.total_hour_monday.split(":");
				    hh = time_array[0];
				    console.log('Monday:'+hh);
				    if(parseInt(hh) >= person_hours_per_day){
				    	jQuery('#tabs .monday a').removeClass('red-day');
				    	jQuery('#tabs .monday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .monday a').removeClass('green-day');
				    	jQuery('#tabs .monday a').addClass('red-day');
				    }

				    //Tuesday
				    time_array = parsed.total_hour_tuesday.split(":");
				    hh = time_array[0];
				    console.log('Tuesday:'+hh);
				    if(parseInt(hh) >= person_hours_per_day){
				    	jQuery('#tabs .tuesday a').removeClass('red-day');
				    	jQuery('#tabs .tuesday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .tuesday a').removeClass('green-day');
				    	jQuery('#tabs .tuesday a').addClass('red-day');
				    }

				    //Wednesday
				    time_array = parsed.total_hour_wednesday.split(":");
				    hh = time_array[0];
				    console.log('Wednesday:'+hh);
				    if(parseInt(hh) >= person_hours_per_day){
				    	jQuery('#tabs .wednesday a').removeClass('red-day');
				    	jQuery('#tabs .wednesday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .wednesday a').removeClass('green-day');
				    	jQuery('#tabs .wednesday a').addClass('red-day');
				    }

				    //Thursday
				    time_array = parsed.total_hour_thursday.split(":");
				    hh = time_array[0];
				    console.log('Thursday:'+hh);
				    if(parseInt(hh) >= person_hours_per_day){
				    	jQuery('#tabs .thursday a').removeClass('red-day');
				    	jQuery('#tabs .thursday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .thursday a').removeClass('green-day');
				    	jQuery('#tabs .thursday a').addClass('red-day');
				    }

				    //Friday
				    time_array = parsed.total_hour_friday.split(":");
				    hh = time_array[0];
				    console.log('Friday:'+hh);
				    if(parseInt(hh) >= person_hours_per_day){
				    	jQuery('#tabs .friday a').removeClass('red-day');
				    	jQuery('#tabs .friday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .friday a').removeClass('green-day');
				    	jQuery('#tabs .friday a').addClass('red-day');
				    }

				    //Saturday
				    time_array = parsed.total_hour_saturday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) > 0){
				    	jQuery('#tabs .saturday a').addClass('gray-day');
				    }else{
				    	jQuery('#tabs .saturday a').removeClass('gray-day');
				    }

				    //Sunday
				    time_array = parsed.total_hour_sunday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) > 0){
				    	jQuery('#tabs .saturday a').addClass('gray-day');
				    }else{
				    	jQuery('#tabs .saturday a').removeClass('gray-day');
				    }

			    //Remove status color if dates are in future.
			    var d = new Date();
				var curr_date = d.getDate();
				var curr_month = d.getMonth();
				var curr_year = d.getFullYear();
				curr_month = (curr_month < 10? '0' : '') + curr_month;
				var today_date = new Date(curr_year, curr_month, curr_date);

				var monday_string = jQuery('#monday .tab_date').val().split("/");
				var monday_date = new Date(monday_string[2], monday_string[1] - 1, monday_string[0]);

			    var tuesday_string = jQuery('#tuesday .tab_date').val().split("/");
			    var tuesday_date = new Date(tuesday_string[2], tuesday_string[1] - 1, tuesday_string[0]);

			    var wednesday_string = jQuery('#wednesday .tab_date').val().split("/");
			    var wednesday_date = new Date(wednesday_string[2], wednesday_string[1] - 1, wednesday_string[0]);

			    var thursday_string = jQuery('#thursday .tab_date').val().split("/");
			    var thursday_date = new Date(thursday_string[2], thursday_string[1] - 1, thursday_string[0]);

			    var friday_string = jQuery('#friday .tab_date').val().split("/");
			    var friday_date = new Date(friday_string[2], friday_string[1] - 1, friday_string[0]);

			    var saturday_string = jQuery('#saturday .tab_date').val().split("/");
			    var saturday_date = new Date(saturday_string[2], saturday_string[1] - 1, saturday_string[0]);

			    var sunday_string = jQuery('#sunday .tab_date').val().split("/");
				var sunday_date = new Date(sunday_string[2], sunday_string[1] - 1, sunday_string[0]);

			    if(today_date <= monday_date){
			    	jQuery('#tabs .monday a').removeClass('green-day red-day');
			    }

			    if(today_date <= tuesday_date){
			    	jQuery('#tabs .tuesday a').removeClass('green-day red-day');
			    }
			    if(today_date <= wednesday_date){
			    	jQuery('#tabs .wednesday a').removeClass('green-day red-day');
			    }

			    if(today_date <= thursday_date){
			    	jQuery('#tabs .thursday a').removeClass('green-day red-day');
			    }

			    if(today_date <= friday_date){
			    	jQuery('#tabs .friday a').removeClass('green-day red-day');
			    }	

			    if(today_date <= saturday_date){
			    	jQuery('#tabs .saturday a').removeClass('gray-day');
			    }

			    if(today_date <= sunday_date){
			    	jQuery('#tabs .sunday a').removeClass('gray-day');
			    }


				jQuery.each(parsed, function(index, value){					
					if(value){
						if(value.day_now){
							var day = value.day_now.toLowerCase();						
							if(day == 'monday'){
								day_sort(value, 'monday', check_same_user);
								jQuery('div#monday .total_hours .task_total_hour h3').html(total_hour_monday);
							}
							if(day == 'tuesday'){
								day_sort(value, 'tuesday', check_same_user);
								jQuery('div#tuesday .total_hours .task_total_hour h3').html(total_hour_tuesday);
							}
							if(day == 'wednesday'){
								day_sort(value, 'wednesday', check_same_user);
								jQuery('div#wednesday .total_hours .task_total_hour h3').html(total_hour_wednesday);
							}
							if(day == 'thursday'){
								day_sort(value, 'thursday', check_same_user);
								jQuery('div#thursday .total_hours .task_total_hour h3').html(total_hour_thursday);
							}
							if(day == 'friday'){
								day_sort(value, 'friday', check_same_user);
								jQuery('div#friday .total_hours .task_total_hour h3').html(total_hour_friday);
							}
							if(day == 'saturday'){
								day_sort(value, 'saturday', check_same_user);
								jQuery('div#saturday .total_hours .task_total_hour h3').html(total_hour_saturday);
							}
							if(day == 'sunday'){
								day_sort(value, 'sunday', check_same_user);
								jQuery('div#sunday .total_hours .task_total_hour h3').html(total_hour_sunday);
							}
						}
					}
					jQuery('.month_name').html(month_name +" - "+ year_name);
					
					
					jQuery('.total_month_hour').html(rounded_total_month_hour);
					
					if(rounded_total_month_hour < '176'){
						jQuery('.total_month_hour').addClass('text_red');
						var hour_balance_decimal = (176 - rounded_total_month_hour).toFixed(2);
					}else if(rounded_total_month_hour > '176'){
						jQuery('.total_month_hour').addClass('text_green');
					}else if(rounded_total_month_hour = '176'){
						jQuery('.total_month_hour').addClass('text_black');
					}
					jQuery('.total_holiday_hour').html(total_holiday_hour);
					
					jQuery('.month_stat .hour_balance').removeClass("text_red text_green");
					if(worked_hours  > total_hours_worked){
						jQuery('.month_stat .hour_balance').addClass("text_red");
						}else{
						jQuery('.month_stat .hour_balance').addClass("text_green");
					}
					jQuery('.month_stat .worked_hours').html(worked_hours);
					jQuery('.month_stat .total_hours_worked').html(total_hours_worked);					
					jQuery('.month_stat .hour_balance').html(hour_balance);
					jQuery('.month_stat .holiday_hours').html(holiday_hours);
					jQuery('.month_stat .holiday_balance').html(total_holiday_work);
					//jQuery('.month_summary').show();
				});
				trigger_accordion_toggle();
			},
			error: function (data) {
				
			}
		});
	});
});
/* ==================================== END SEARCH PERSON TASK BY NAME ==================================== */

/* ==================================== SEARCH PERSON TASK BY NAME AND WEEK NUMBER ==================================== */
jQuery(document).ready(function(){
	jQuery('.timesheet #week_number_calendar').datepicker({
		dateFormat: 'yy-mm-dd',
		showWeek: true,
		firstDay: 1,
		markerClassName: 'hasDatepicker',
		onSelect: function (dateText, inst) {
			var start_date = new Date(dateText);
			var end_date = new Date(dateText);
			 var index = start_date.getDay();
			if(index == 0) {
				start_date.setDate(start_date.getDate() - 6);   
				end_date.setDate(end_date.getDate() + 1);
            }else if(index == 1) {
				start_date.setDate(start_date.getDate());
				end_date.setDate(end_date.getDate() + 7);               
            }else if(index == 2) {
				start_date.setDate(start_date.getDate() - 1);
				end_date.setDate(end_date.getDate() + 6);            
			}else if(index == 3) {
				start_date.setDate(start_date.getDate() - 2);
				end_date.setDate(end_date.getDate() + 5); 
			}else if(index == 4) {
				start_date.setDate(start_date.getDate() - 3);
				end_date.setDate(end_date.getDate() + 4); 
			}else if(index == 5) {
				start_date.setDate(start_date.getDate() - 4);
				end_date.setDate(end_date.getDate() + 3); 
			}else if(index == 6) {
				start_date.setDate(start_date.getDate() - 5);
				end_date.setDate(end_date.getDate() + 2); 
			}
			var dates_range = get_date_range(start_date, end_date);
			
			var weekday=new Array(7);
			weekday[0]="monday";
			weekday[1]="tuesday";
			weekday[2]="wednesday";
			weekday[3]="thursday";
			weekday[4]="friday";
			weekday[5]="saturday";
			weekday[6]="sunday";
			
			
			var week_date = jQuery(this).datepicker('getDate');
			var day_of_week = weekday[week_date.getUTCDay()];
			jQuery('.tabs_li').each(function(){
				jQuery(this).removeClass('active');
			});
			jQuery('li.tabs_li').find('a[href*="#'+day_of_week+'"]').parent().addClass('active');
			jQuery('.tab_content').each(function(){
				jQuery(this).removeClass('active');
				jQuery(this).attr('style', 'display:none');
			});
			jQuery('div#'+day_of_week+'.tab_content').addClass('active');
			jQuery('div#'+day_of_week+'.tab_content').attr('style', 'display:block');
			var weekNum = jQuery.datepicker.iso8601Week(new Date(dateText));
			var startDate = new Date(dateText);
			var picked_year = startDate.getFullYear();
			var picked_month_one = startDate.getMonth()+1;
			var picked_day = startDate.getDate();
			var count = picked_month_one.toString().length;
			if(count == 1){
				var picked_month = 0 +""+ picked_month_one;
			}else{
				var picked_month = picked_month_one;
			}			
			jQuery('#week_number').val(weekNum);
			jQuery('#picked_year').val(picked_year);
			jQuery('#picked_month').val(picked_month);
			jQuery('.timesheet .left_div .loader').show();
			var week_dates = writeDays(picked_year, weekNum);
			jQuery('.monday .day_date').text(change_date_format(new_date_format(dates_range[0]), 'dd/M'));
			jQuery('.tuesday .day_date').text(change_date_format(new_date_format(dates_range[1]), 'dd/M'));
			jQuery('.wednesday .day_date').text(change_date_format(new_date_format(dates_range[2]), 'dd/M'));
			jQuery('.thursday .day_date').text(change_date_format(new_date_format(dates_range[3]), 'dd/M'));
			jQuery('.friday .day_date').text(change_date_format(new_date_format(dates_range[4]), 'dd/M'));
			jQuery('.saturday .day_date').text(change_date_format(new_date_format(dates_range[5]), 'dd/M'));
			jQuery('.sunday .day_date').text(change_date_format(new_date_format(dates_range[6]), 'dd/M'));
			
			jQuery('#monday .monday_date').attr('value', new_date_format(dates_range[0]));
			jQuery('#tuesday .tuesday_date').attr('value', new_date_format(dates_range[1]));
			jQuery('#wednesday .wednesday_date').attr('value', new_date_format(dates_range[2]));
			jQuery('#thursday .thursday_date').attr('value', new_date_format(dates_range[3]));
			jQuery('#friday .friday_date').attr('value', new_date_format(dates_range[4]));
			jQuery('#saturday .saturday_date').attr('value', new_date_format(dates_range[5]));
			jQuery('#sunday .sunday_date').attr('value', new_date_format(dates_range[6]));
			jQuery('.tab_content .datepicker_week').attr('value', weekNum);
			var person_name = jQuery('.staff_timesheet_form .person_name').val();
			var week_number = jQuery('.staff_timesheet_form .week_number').val();
			var picked_year = jQuery('.staff_timesheet_form .picked_year').val();
			var picked_month = jQuery('.staff_timesheet_form .picked_month').val();
			var staff_timesheet_data = person_name +'_'+ week_number +'_'+ picked_year +'_'+ picked_month +'_'+ new_date_format(dates_range[0]) +'_'+ new_date_format(dates_range[6]);			
			jQuery.ajax({
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data:{
					'type' : 'search_staff_timesheet',
					'staff_timesheet_data' : staff_timesheet_data				
				},
				success: function (data) {										
					jQuery('.timesheet .left_div .loader').hide();
					var parsed = jQuery.parseJSON(data);					
					var check_same_user = parsed.check_same_user;
					var month_name = parsed.month_name;
					var year_name = parsed.year_name;
					var rounded_total_month_hour = parsed.rounded_total_month_hour;
					var worked_hours = parsed.worked_hours;
					var total_hours_worked = parsed.total_hours_worked;
					var hour_balance = parsed.hour_balance;
					var holiday_hours = parsed.holiday_hours;
					var total_holiday_work = parsed.total_holiday_work;
					var total_month_hour = parsed.total_month_hour;
					var total_holiday_hour = parsed.total_holiday_hour;
					var total_hour_monday = parsed.total_hour_monday;
					var total_hour_tuesday = parsed.total_hour_tuesday;
					var total_hour_wednesday = parsed.total_hour_wednesday;
					var total_hour_thursday = parsed.total_hour_thursday;
					var total_hour_friday = parsed.total_hour_friday;
					var total_hour_saturday = parsed.total_hour_saturday;
					var total_hour_sunday = parsed.total_hour_sunday;
				
					
					jQuery('.week_section h3.week').html('Week: '+change_date_format(parsed.week_start, 'dd/M/Y')+" - "+change_date_format(parsed.week_end, 'dd/M/Y'));
					jQuery('.header_person_name h1').html(parsed.person_name+'\'\s Timesheet');					
					
					var days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"]; 
					jQuery.each(days, function(index, value){						
						jQuery('#'+value+ ' .data_list_'+value).remove();
						jQuery('#'+value+ ' .data_title .accordian').remove();
						if(check_same_user != 'yes'){
							jQuery('.import_button').hide();
						}else{
							jQuery('.import_button').show();
						}
					});
					jQuery('.total_hours .task_total_hour h3').text('');


					//Monday
				    time_array = parsed.total_hour_monday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) >= 8){
				    	jQuery('#tabs .monday a').removeClass('red-day');
				    	jQuery('#tabs .monday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .monday a').removeClass('green-day');
				    	jQuery('#tabs .monday a').addClass('red-day');
				    }

				    //Tuesday
				    time_array = parsed.total_hour_tuesday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) >= 8){
				    	jQuery('#tabs .tuesday a').removeClass('red-day');
				    	jQuery('#tabs .tuesday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .tuesday a').removeClass('green-day');
				    	jQuery('#tabs .tuesday a').addClass('red-day');
				    }

				    //Wednesday
				    time_array = parsed.total_hour_wednesday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) >= 8){
				    	jQuery('#tabs .wednesday a').removeClass('red-day');
				    	jQuery('#tabs .wednesday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .wednesday a').removeClass('green-day');
				    	jQuery('#tabs .wednesday a').addClass('red-day');
				    }

				    //Thursday
				    time_array = parsed.total_hour_thursday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) >= 8){
				    	jQuery('#tabs .thursday a').removeClass('red-day');
				    	jQuery('#tabs .thursday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .thursday a').removeClass('green-day');
				    	jQuery('#tabs .thursday a').addClass('red-day');
				    }

				    //Friday
				    time_array = parsed.total_hour_friday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) >= 8){
				    	jQuery('#tabs .friday a').removeClass('red-day');
				    	jQuery('#tabs .friday a').addClass('green-day');

				    }else{
				    	jQuery('#tabs .friday a').removeClass('green-day');
				    	jQuery('#tabs .friday a').addClass('red-day');
				    }

				    //Saturday
				    time_array = parsed.total_hour_saturday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) > 0){
				    	jQuery('#tabs .saturday a').addClass('gray-day');
				    }else{
				    	jQuery('#tabs .saturday a').removeClass('gray-day');
				    }

				    //Sunday
				    time_array = parsed.total_hour_sunday.split(":");
				    hh = time_array[0];
				    if(parseInt(hh) > 0){
				    	jQuery('#tabs .saturday a').addClass('gray-day');
				    }else{
				    	jQuery('#tabs .saturday a').removeClass('gray-day');
				    }					
					jQuery.each(parsed, function(index, value){						
						if(value){
							if(value.day_now){
								var day = value.day_now.toLowerCase();							
								if(day == 'monday'){
									day_sort(value, 'monday', check_same_user);
									jQuery('div#monday .total_hours .task_total_hour h3').html(total_hour_monday);
								}
								if(day == 'tuesday'){
									day_sort(value, 'tuesday', check_same_user);
									jQuery('div#tuesday .total_hours .task_total_hour h3').html(total_hour_tuesday);
								}
								if(day == 'wednesday'){
									day_sort(value, 'wednesday', check_same_user);
									jQuery('div#wednesday .total_hours .task_total_hour h3').html(total_hour_wednesday);
								}
								if(day == 'thursday'){
									day_sort(value, 'thursday', check_same_user);
									jQuery('div#thursday .total_hours .task_total_hour h3').html(total_hour_thursday);
								}
								if(day == 'friday'){
									day_sort(value, 'friday', check_same_user);
									jQuery('div#friday .total_hours .task_total_hour h3').html(total_hour_friday);
								}
								if(day == 'saturday'){
									day_sort(value, 'saturday', check_same_user);
									jQuery('div#saturday .total_hours .task_total_hour h3').html(total_hour_saturday);
								}
								if(day == 'sunday'){
									day_sort(value, 'sunday', check_same_user);
									jQuery('div#sunday .total_hours .task_total_hour h3').html(total_hour_sunday);
								}
							}
						}
						jQuery('.month_name').html(month_name +" - "+ year_name);
					
						jQuery('.total_month_hour').html(rounded_total_month_hour);
						
						if(rounded_total_month_hour < '176'){
							jQuery('.total_month_hour').addClass('text_red');
							var hour_balance_decimal = (176 - rounded_total_month_hour).toFixed(2);
						}else if(rounded_total_month_hour > '176'){
							jQuery('.total_month_hour').addClass('text_green');
						}else if(rounded_total_month_hour = '176'){
							jQuery('.total_month_hour').addClass('text_black');
						}
						jQuery('.total_holiday_hour').html(total_holiday_hour);
						
						jQuery('.month_stat .hour_balance').removeClass("text_red text_green");
						if(worked_hours  > total_hours_worked){
							jQuery('.month_stat .hour_balance').addClass("text_red");
						}else{
							jQuery('.month_stat .hour_balance').addClass("text_green");
						}						
						jQuery('.month_stat .worked_hours').html(worked_hours);
						jQuery('.month_stat .total_hours_worked').html(total_hours_worked);
						jQuery('.month_stat .hour_balance').html(hour_balance);
						jQuery('.month_stat .holiday_hours').html(holiday_hours);
						jQuery('.month_stat .holiday_balance').html(total_holiday_work);
						//jQuery('.month_summary').show();
					});
					trigger_accordion_toggle();
				},
				error: function (data) {
					
				}
			});
		}	
	});		
});
/* ==================================== END SEARCH PERSON TASK BY NAME AND WEEK NUMBER ==================================== */

/* ==================================== CHECK STATUS DAY TAB COLOR ==================================== */
jQuery(document).ready(function(){
	var monday = jQuery('#monday .total_hours .task_total_hour input').val();
	var tuesday = jQuery('#tuesday .total_hours .task_total_hour input').val();
	var wednesday = jQuery('#wednesday .total_hours .task_total_hour input').val();
	var thursday = jQuery('#thursday .total_hours .task_total_hour input').val();
	var friday = jQuery('#friday .total_hours .task_total_hour input').val();
	var saturday = jQuery('#saturday .total_hours .task_total_hour input').val();
	var sunday = jQuery('#sunday .total_hours .task_total_hour input').val();
	
	if(monday == 'green'){
		jQuery('#tabs .monday a').addClass('green-day');
	}else if(monday == 'red'){
		jQuery('#tabs .monday a').addClass('red-day');
	}
	if(tuesday == 'green'){
		jQuery('#tabs .tuesday a').addClass('green-day');
	}else if(tuesday == 'red'){
		jQuery('#tabs .tuesday a').addClass('red-day');
	}
	if(wednesday == 'green'){
		jQuery('#tabs .wednesday a').addClass('green-day');
	}else if(wednesday == 'red'){
		jQuery('#tabs .wednesday a').addClass('red-day');
	}
	if(thursday == 'green'){
		jQuery('#tabs .thursday a').addClass('green-day');
	}else if(thursday == 'red'){
		jQuery('#tabs .thursday a').addClass('red-day');
	}
	if(friday == 'green'){
		jQuery('#tabs .friday a').addClass('green-day');
	}else if(friday == 'red'){
		jQuery('#tabs .friday a').addClass('red-day');
	}
	if(saturday == 'yellow'){
		jQuery('#tabs .saturday a').addClass('yellow-day');
	}else if(saturday == 'red'){
		// jQuery('#tabs .saturday a').addClass('red-day');
	}
	if(sunday == 'yellow'){
		jQuery('#tabs .sunday a').addClass('yellow-day');
	}else if(sunday == 'red'){
		// jQuery('#tabs .sunday a').addClass('red-day');
	}

});

jQuery(document).on('click','.add_none_working_btn', function(){
	var add_day =jQuery('.tabs_li.active a p:first-child()').text().toLowerCase();
	var add_date = jQuery('.' + add_day + '_date').val();
	var date_format = change_date_format(add_date, "dd/M");
	var add_week = jQuery('#week_number').val();
	var current_hour = jQuery('#'+add_day+' .total_hours .task_total_hour h3').text();
	var day_not_current_capital = add_day.toLowerCase().replace(/\b[a-z]/g, function(letter) {
		return letter.toUpperCase();
	});
	var day_date_week = add_day +"_"+ add_date +"_"+ add_week +"_"+ current_hour;
	var check_modal_title = jQuery('div[aria-describedby^="sickness_dialog_box"] div.modal_header p.modal_date').length;
	if(check_modal_title == 0){
		jQuery('div[aria-describedby^="sickness_dialog_box"] .ui-widget-header').append('<div class="modal_header"><p class="modal_title">Add Sickness</p><p class="modal_date">'+day_not_current_capital +", "+ date_format+'</p></div>');
	}else{
		jQuery('div[aria-describedby^="sickness_dialog_box"] div.modal_header p.modal_date').text(day_not_current_capital +", "+ date_format);
	}
	jQuery('#sickness_dialog_box').dialog('open');
});


//Add Sickness day
jQuery(document).on('click', '#add-sickness-day', function(){
	jQuery('.loader add-sickness-loader').show();
	var sickness_hours = jQuery('#sickness_hours').val();
	var sickness_descriptions = jQuery('#sickness_descriptions').val();
	var current_day = jQuery('.tab_content.active').attr('id');



});
//Convert Hours format to Decimal Format.
function timeStringToFloat(time) {
  var hoursMinutes = time.split(/[.:]/);
  var hours = parseInt(hoursMinutes[0], 10);
  var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
  return hours + minutes / 60;
}
//Covert Decimal to hours
function deciHours(time) {
    return (function(i) {return i+(Math.round(((time-i)*60),10)/100);})(parseInt(time, 10));
}
function timesheet_format(str) {
    if ( !/:/.test( str ) ) { str += ':00'; }
    return str.replace(/^\d{1}:/, '0$&').replace(/:\d{1}$/, '$&0' );
}
function convertToHHMM(info) {
  var hrs = parseInt(Number(info));
  var min = Math.round((Number(info)-hrs) * 60);
  if(min <= 9){
  	var mins = '0'+min;
  }else{
  	var	mins = min;
  }
  if(hrs <= 9){
  	return '0'+hrs+':'+mins;
  }else{
  	return hrs+':'+mins;
  }
 
}
</script>