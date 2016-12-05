<?php /* Template name: Add People */ ?>
<?php get_header(); ?>
<?php include('custom_timezones.php'); 
global $wpdb;
$table_name = $wpdb->prefix . "custom_person";
$table_name_department = $wpdb->prefix . "custom_department";
$departments = $wpdb->get_col("SELECT DISTINCT department_name FROM {$table_name_department}");	
$permission_array = array("Administrator", "Project Manager", "User");
$birthmonths = array('January', 'February','March','April','May','June','July','August','September','October','November','December');
?>
<div class="add_person">
	<form action="" method="post" name="person" enctype="multipart/form-data" id="person">
		<div class="section">
			<div class="left">
				<p class="label">First Name</p>
			</div>
			<div class="right">
				<input type="text" class="person_first_name" name="person_first_name" />
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Last Name</p>
			</div>
			<div class="right">
				<input type="text" class="person_last_name" name="person_last_name" />
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Birthdate</p>
			</div>
			<div class="right">
				<select class="person_birthday" name="person_birthday">
					<?php for($day = 1; $day <= 31;  $day++){ ?>
						<option><?php echo $day; ?></option>
					<?php } ?>
				</select>
				<select class="person_birthmonth" name="person_birthmonth">
					<?php foreach($birthmonths as $birthmonth){?>
						<option><?php echo $birthmonth; ?></option>
					<?php } ?>
				</select>
				<select class="person_birthyear" name="person_birthyear">
					<?php
						$current_year = date('Y');
						for($year = 1965; $year <= $current_year;  $year++){ 
					?>
						<option><?php echo $year; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Address</p>
			</div>
			<div class="right">
				<textarea name="person_address" class="person_address"></textarea>
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Mobile Phone</p>
			</div>
			<div class="right">
				<input type="text" class="person_mobile" name="person_mobile" />
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Email</p>
			</div>
			<div class="right">
				<input type="text" class="person_email" name="person_email" />
				<input type="checkbox" name="person_email_notification" class="person_email_notification" />Enable Notification
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Skype Name</p>
			</div>
			<div class="right">
				<input type="text" class="person_skype" name="person_skype" />
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Person Status</p>
			</div>
			<div class="right">
				<select name="person_status" id="">
					<option value="0">Acitve</option>
					<option value="1">Inactive</option>
				</select>
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Title</p>
			</div>
			<div class="right">
				<input type="text" class="person_title" name="person_title" />
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Kanban ID</p>
			</div>
			<div class="right">
				<input type="text" class="person_kanban_id" name="person_kanban_id" />
			</div>
		</div>		
		<div class="section">
			<div class="left">
				<p class="label">Department</p>
			</div>
			<div class="right">
				<select class="person_department" name="person_department">
					<?php
						$person_department_array = array();
						foreach($departments as $department){ 
							$person_department_array[] = $department;
						}
						sort($person_department_array);						
					?>
					<?php foreach ($person_department_array as $person_department){ ?>
					<option><?php echo $person_department; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Hours/day:</p>
			</div>
			<div class="right">
				<input type="text" class="person_hours_per_day" name="person_hours_per_day" />
				<input type="checkbox" name="person_time_track" class="person_time_track" />Enable Time Tracking
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Hourly rate</p>
			</div>
			<div class="right">
				<p class="right_label">kr</p>
				<input type="text" class="person_hourly_rate" name="person_hourly_rate" />
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Monthly rate</p>
			</div>
			<div class="right">
				<p class="right_label">kr</p>
				<input type="text" class="person_monthly_rate" name="person_monthly_rate" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Permissions</p>
			</div>
			<div class="right">
				<select name="person_permission">
					<?php
						$person_permission_array = array();
						foreach($permission_array as $permission){ 
							$person_permission_array[] = $permission;
						}
						sort($person_permission_array);						
					?>
					<?php foreach ($person_permission_array as $person_permission){?>
					<option><?php echo $person_permission; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Timezone</p>
			</div>
			<div class="right">
				<select name="person_timezone">
					<?php foreach ($timezones as $time => $timezone): ?>
					<option value="<?php echo $time; ?>"><?php echo $time; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="section">
			<div class="left">
				<p class="label">Photo</p>
			</div>
			<div class="right">
				<input type="file" name="person_image" class="person_image">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Description</p>
			</div>
			<div class="right">
				<textarea name="person_description" class="person_description textarea_wide"></textarea>
			</div>
		</div>
		<input type="submit" name="submit" class="button_1" value="Add Person" />
		<a class="button_2" href="/people/">Cancel</a>
	</form>
</div>
<?php 
if(isset($_POST['submit'])):
	global $wpdb;
	$person_first_name			= (isset($_POST['person_first_name']) ? $_POST['person_first_name'] : '');
	$person_last_name			= (isset($_POST['person_last_name']) ? $_POST['person_last_name'] : '');
	$person_fullname 			= $person_first_name ." ". $person_last_name;
	$person_birthday			= (isset($_POST['person_birthday']) ? $_POST['person_birthday'] : '');
	$person_birthmonth			= (isset($_POST['person_birthmonth']) ? $_POST['person_birthmonth'] : '');
	$person_birthyear			= (isset($_POST['person_birthyear']) ? $_POST['person_birthyear'] : '');
	$person_address				= (isset($_POST['person_address']) ? $_POST['person_address'] : '');
	$person_mobile				= (isset($_POST['person_mobile']) ? $_POST['person_mobile'] : '');
	$person_email				= (isset($_POST['person_email']) ? $_POST['person_email'] : '');
	$person_email_notification 	= (isset($_POST['person_email_notification']) ? 1 : 0);
	$person_skype				= (isset($_POST['person_skype']) ? $_POST['person_skype'] : '');
	$person_title				= (isset($_POST['person_title']) ? $_POST['person_title'] : '');
	$person_kanban_id			= (isset($_POST['person_kanban_id']) ? $_POST['person_kanban_id'] : '');
	$person_description			= (isset($_POST['person_description']) ? $_POST['person_description'] : '');
	$person_department			= (isset($_POST['person_department']) ? $_POST['person_department'] : '');
	$person_hours_per_day		= (isset($_POST['person_hours_per_day']) ? $_POST['person_hours_per_day'] : '');
	$person_hourly_rate			= (isset($_POST['person_hourly_rate']) ? $_POST['person_hourly_rate'] : '');
	$person_time_track			= (isset($_POST['person_time_track']) ? 1 : 0);	
	$person_monthly_rate		= (isset($_POST['person_monthly_rate']) ? $_POST['person_monthly_rate'] : '');
	$person_permission 			= (isset($_POST['person_permission']) ? $_POST['person_permission'] : '');
	$person_timezone			= (isset($_POST['person_timezone']) ? $_POST['person_timezone'] : '');
	$person_image 				= (isset($_FILES["person_image"]["name"]) ? $_FILES["person_image"]["name"] : '');
	$person_status				= (isset($_POST['person_status']) ? $_POST['person_status'] : '');

	$insert = $wpdb->insert( $table_name , array(
	'person_first_name'			=> $person_first_name,
	'person_last_name'			=> $person_last_name,
	'person_fullname'			=> $person_fullname,
	'person_birthday'			=> $person_birthday,
	'person_birthmonth'			=> $person_birthmonth,
	'person_birthyear'			=> $person_birthyear,
	'person_address'			=> $person_address,
	'person_mobile'				=> $person_mobile,
	'person_email'				=> $person_email,
	'person_email_notification'	=> $person_email_notification,
	'person_skype'				=> $person_skype,
	'person_kb_user_id'			=> $person_title,
	'person_description'		=> $person_description,
	'person_department'			=> $person_department,
	'person_hours_per_day'		=> $person_hours_per_day,
	'person_time_track'			=> $person_time_track,
	'person_hourly_rate'		=> $person_hourly_rate,
	'person_monthly_rate'		=> $person_monthly_rate,
	'person_permission'			=> $person_permission,	
	'person_timezone'			=> $person_timezone,
	'person_image'				=> $person_image,
	'person_status'				=> $person_status
	), array( '%s', '%s' ));
	
	if($insert == 1):
		echo "<p class='message'>";
		echo "Person Added!";
	else:
		echo "Person was not successfully added.";
		echo "</p>";
	endif;		
endif;
$destination= get_home_path().'wp-content/uploads/person_image/';
$move =	move_uploaded_file($_FILES["person_image"]["tmp_name"], $destination.$_FILES["person_image"]["name"]);
?>
<?php get_footer(); ?>