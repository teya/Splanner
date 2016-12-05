<?php /* Template Name: Edit Project */ ?>
<?php get_header(); ?>
<?php  
global $wpdb;			
$table_name = $wpdb->prefix . "custom_project";
$table_name_clients = $wpdb->prefix . "custom_client";
$table_name_person = $wpdb->prefix . "custom_person";
$table_name_color = $wpdb->prefix . "custom_project_color";
$table_name_department = $wpdb->prefix . "custom_department";
$table_name_color = $wpdb->prefix . "custom_project_color";
$table_name_website = $wpdb->prefix . "custom_website";

$projects = $wpdb->get_results("SELECT * FROM {$table_name}");
$client_names = $wpdb->get_results("SELECT * FROM {$table_name_clients}");
$persons = $wpdb->get_results("SELECT * FROM {$table_name_person} WHERE person_status='0'");
$departments = $wpdb->get_results("SELECT * FROM {$table_name_department}");
$project_colors = $wpdb->get_results("SELECT * FROM {$table_name_color}");
$id = $_GET['editID'];
$results_edit = $wpdb->get_row("SELECT * FROM $table_name WHERE id=$id"); 
$colors = $wpdb->get_results("SELECT * FROM {$table_name_color}");
$websites = $wpdb->get_results("SELECT * FROM {$table_name_website}");
?>
<?php
if(isset($_POST['submit'])):
	$project_client					= $_POST['project_client'];
	$project_department				= $_POST['project_department'];
	$project_name					= $_POST['project_name'];
	$project_start_date				= $_POST['project_start_date'];
	$project_estimated_deadline		= $_POST['project_estimated_deadline'];
	$project_date_completed			= $_POST['project_date_completed'];
	$project_hour					= $_POST['project_hour'];
	$project_minute					= $_POST['project_minute'];
	$project_responsible_worker		= $_POST['project_responsible_worker'];
	$project_current_status			= $_POST['project_current_status'];
	$project_demo_url				= $_POST['project_demo_url'];
	$project_login_username			= $_POST['project_login_username'];
	$project_login_password			= $_POST['project_login_password'];
	$project_invoice_method			= $_POST['project_invoice_method'];
	if(isset($_POST['project_invoice_method'])):
	$project_invoice_method 		= $_POST['project_invoice_method'];
	else:
	$project_invoice_method 		= 0;
	endif;
	$project_invoiced_amount		= $_POST['project_invoiced_amount'];
	$project_invoice_date			= $_POST['project_invoice_date'];
	
	foreach($departments as $department){
		if($project_department == $department->department_name){
			$project_default_expenses = $department->department_default_expenses;
		}
	}
	
	$project_extra_expenses			= $_POST['project_extra_expenses'];	
	$project_budget					= $_POST['project_budget'];
	$project_description			= $_POST['project_description'];
	
	$update = $wpdb->update( $table_name , array( 
	'project_client'				=> $project_client,
	'project_department'			=> $project_department,
	'project_name'					=> $project_name,
	'project_start_date'			=> $project_start_date,
	'project_estimated_deadline'	=> $project_estimated_deadline,
	'project_date_completed'		=> $project_date_completed,
	'project_hour'					=> $project_hour,
	'project_minute'				=> $project_minute,
	'project_responsible_worker'	=> $project_responsible_worker,
	'project_current_status'		=> $project_current_status,
	'project_demo_url'				=> $project_demo_url,
	'project_login_username'		=> $project_login_username,
	'project_login_password'		=> $project_login_password,
	'project_invoice_method'		=> $project_invoice_method,
	'project_invoiced_amount'		=> $project_invoiced_amount,
	'project_default_expenses'		=> $project_default_expenses,
	'project_extra_expenses'		=> $project_extra_expenses,
	'project_budget'				=> $project_budget,
	'project_description'			=> $project_description	
	),	
	array( 'ID' => $id ),
	array( '%s', '%s' ));	
	
	if($update == 1):
		echo "<p class='message'>";
		echo "Project Updated!";
		echo "</p>";
	else:
		echo "<p class='message'>";
		echo "Project was not Updated.";
		echo "</p>";
	endif;	
endif;
?>
<?php $current_status_array = array('Monthly Ongoing', 'Quote sent', 'Planning', 'Setup', 'Design', 'Functionality', 'Adjustments', 'Invoiced', 'Cancelled'); ?>
<?php $results_edit = $wpdb->get_row("SELECT * FROM $table_name WHERE id=$id"); ?> 
<script>
	jQuery(document).ready(function(){
		jQuery('.project_start_date').datepicker();
		jQuery('.project_estimated_deadline').datepicker();
		jQuery('.project_date_completed').datepicker();
		jQuery('.project_invoice_date').datepicker();
	});
</script>
<div class="edit_project">
	<form action="" method="post" name="project" id="project">
		<div class="section">
			<div class="left">
				<p class="label">Client</p>
			</div>
			<div class="right">
				<select class="project_client" name="project_client">
					<option value="<?php echo (isset($results_edit->project_client)) ? $results_edit->project_client : '';  ?>"><?php echo $results_edit->project_client; ?></option>
					<?php 
						$project_client_array = array();
						foreach ($client_names as $client){
							$project_client_array[] = $client->client_name;;
						}
						sort($project_client_array);
					?>
					<?php 
					foreach($project_client_array as $project_client){
						if($project_client != $results_edit->project_client){
					?>
						<option><?php echo $project_client; ?></option>
					<?php 
						}
					}
					?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Department</p>
			</div>
			<div class="right">
				<select class="project_department" name="project_department">
					<option value="<?php echo (isset($results_edit->project_department)) ? $results_edit->project_department : '';  ?>"><?php echo $results_edit->project_department; ?></option>
					<?php 
						$project_department_array = array();
						foreach ($departments as $department){
							$project_department_array[] = $department->department_name;
						}
						sort($project_department_array);
					?>
					<?php 
						foreach($project_department_array as $project_department){
							if($project_department != $results_edit->project_department){
							?>
							<option><?php echo $project_department; ?></option>
							<?php 
							}
						}
					?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Project Name</p>
			</div>
			<div class="right">				
				<select name="project_name" class="project_name" >
					<option value="<?php echo (isset($results_edit->project_name)) ? $results_edit->project_name : '';  ?>"><?php echo $results_edit->project_name; ?></option>
					<?php 
						$project_color_array = array();
						foreach ($project_colors as $project_color){
							$project_color_array[] = $project_color->project_category;
						}
						sort($project_color_array);
					?>
					<?php 
						foreach($project_color_array as $project_color){ 
							if($project_color != $results_edit->project_name){
					?>
							<option><?php echo $project_color;  ?></option>
							<?php 
							}
						}
					?>
				</select>
				<div class="button_2 add_project_name">Add Project Name</div>
				<div style="display:none" class="add_project_category_color">
					<p class="label">Project Name</p>
					<input type="text" name="project_category" class="project_category" />
					<p class="label">Project Color</p>
					<input type="text" name="project_color" class="project_color" />
					<div class="button_1 save_project_category_color">Add</div>
				</div>
			</div>
		</div>
		<div class="border_separator"></div>		
		<div class="section">
			<div class="left">
				<p class="label">Start date</p>
			</div>
			<div class="right">
				<input type="text" name="project_start_date" class="project_start_date" value="<?php echo (isset($results_edit->project_start_date)) ? $results_edit->project_start_date : '';  ?>"/>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Estimated Deadline</p>
			</div>
			<div class="right">
				<input type="text" name="project_estimated_deadline" class="project_estimated_deadline" value="<?php echo (isset($results_edit->project_estimated_deadline)) ? $results_edit->project_estimated_deadline : '';  ?>"/>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Date Completed</p>
			</div>
			<div class="right">
				<input type="text" name="project_date_completed" class="project_date_completed" value="<?php echo (isset($results_edit->project_date_completed)) ? $results_edit->project_date_completed : '';  ?>"/>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Time Spent</p>
			</div>
			<div class="right">
				<input type="text" name="project_hour" class="project_hour" value="<?php echo (isset($results_edit->project_hour)) ? $results_edit->project_hour : '';  ?>"/> h
				<input type="text" name="project_minute" class="project_minute" value="<?php echo (isset($results_edit->project_minute)) ? $results_edit->project_minute : '';  ?>"/> m
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Responsible worker</p>
			</div>
			<div class="right">
				<select name="project_responsible_worker" class="project_responsible_worker">
					<option value="<?php echo (isset($results_edit->project_responsible_worker)) ? $results_edit->project_responsible_worker : '';?>"><?php echo $results_edit->project_responsible_worker; ?></option>
					<?php 
						$project_person_array = array();
						foreach ($persons as $person){
							$project_person_array[] = $person->person_first_name ." ".  $person->person_last_name;
						}
						sort($project_person_array);
					?>
					<?php 
						foreach ($project_person_array as $project_person){ 
							if($project_person != $results_edit->project_responsible_worker){
					?>					
						<option><?php echo $project_person; ?></option>
					<?php 
							}
						}
					?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Current Status</p>
			</div>
			<div class="right">
				<select name="project_current_status" class="project_current_status">
					<option value="<?php echo (isset($results_edit->project_current_status)) ? $results_edit->project_current_status : '';  ?>"><?php echo $results_edit->project_current_status;?></option>
					<?php 
						$project_current_status_array = array();
						foreach ($current_status_array as $current_status){
							$project_current_status_array[] = $current_status;
						}
						sort($project_current_status_array);
					?>
					<?php 
						foreach ($project_current_status_array as $project_current_status){ 
							if($project_current_status != $results_edit->project_current_status){
					?>
								<option><?php echo $project_current_status; ?></option>
					<?php 
							}
						}
					?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>		
		<div class="section">
			<div class="left">
				<p class="label">Website URL</p>
			</div>
			<div class="right">				
				<select name="project_site_url" class="project_site_url">
					<option value="<?php echo (isset($results_edit->project_site_url)) ? $results_edit->project_site_url : '';  ?>"><?php echo $results_edit->project_site_url;?></option>
					<?php 
						$project_website_array = array();
						foreach ($websites as $website){
							$project_website_array[] = $website->site_url;
						}
						sort($project_website_array);
					?>
					<?php 
						foreach ($project_website_array as $project_website){ 
							if($project_website != $results_edit->project_site_url ){
							?>
							<option><?php echo $project_website; ?></option>
							<?php 
							}
						}
					?>
				</select>				
				<div class="button_2 add_website_url">Add Website</div>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Invoice Method</p>
			</div>
			<div class="right">
				<input type="checkbox" name="project_invoice_method" value="<?php echo (isset($results_edit->project_invoice_method)) ? $results_edit->project_invoice_method : '';  ?>" class="project_invoice_method checkbox" checked>Billable
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Invoice Date</p>
			</div>
			<div class="right">
				<input type="text" name="project_invoice_date" value="<?php echo (isset($results_edit->project_invoice_date)) ? $results_edit->project_invoice_date : '';  ?>" class="project_invoice_date">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Invoice Amount</p>
			</div>
			<div class="right">
				<input type="text" name="project_invoiced_amount" value="<?php echo (isset($results_edit->project_invoiced_amount)) ? $results_edit->project_invoiced_amount : '';  ?>" class="project_invoiced_amount">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Extra Expenses</p>
			</div>
			<div class="right">
				<input type="text" name="project_extra_expenses" value="<?php echo (isset($results_edit->project_extra_expenses)) ? $results_edit->project_extra_expenses : '';  ?>" class="project_extra_expenses">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Default Expenses</p>
			</div>
			<div class="right">
				<input type="text" name="project_default_expenses" value="<?php echo (isset($results_edit->project_default_expenses)) ? $results_edit->project_default_expenses : '';  ?>" class="project_default_expenses">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Budget</p>
			</div>
			<div class="right">
				<p class="right_label">Estimated Project Budget</p>
				<input type="text" name="project_budget" class="project_budget input_float_left" value="<?php echo (isset($results_edit->project_budget)) ? $results_edit->project_budget : '';  ?>">
				<p class="right_label">[SEK]</p>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Description</p>
			</div>
			<div class="right">
				<textarea name="project_description" class="project_description textarea_wide" ><?php echo (isset($results_edit->project_description)) ? $results_edit->project_description : '';  ?></textarea>
			</div>
		</div>
		<input type="submit" name="submit" class="add_project button_1" value="Update" />
		<a class="button_2" href="/projects/">Cancel</a>
	</form>
</div>
<div style="display:none;" id="dialog_form_website_add" title="Add Website">
	<?php 
		$table_name_client = $wpdb->prefix . "custom_client";
		$clients = $wpdb->get_results("SELECT * FROM $table_name_client");
		$site_types = array('Main site', 'Secondary site', 'Demo site');
		$site_platforms = array('Wordpess', 'Drupal');
	?>
	<div class="add_website website_style">
		<form action="" method="post" name="website" id="website">
			<div class="section first_section">
				<div class="left">
					<p class="label">Site URL:</p>
				</div>
				<div class="right">
					<input type="text" name="site_url" class="site_url required" placeholder="http://">	
					<div class="error_message"><p></p></div>
					<div class="get_details_buttons">
						<div class="button_1 get_wp_details">Get WP Details</div>
						<div class="button_1 get_theme_details">Get Theme Details</div>	
						<div style="display:none;" class="loader"></div>
					</div>
				</div>				
			</div>
			<div class="border_separator"></div>		
			<div style="display:none" class="section wp_readme_details"></div>
			<div class="section wp_version"></div>
			<div class="section theme_details"></div>
			<div class="section three_column">
				<select name="site_type">
					<option disabled selected>-- Site Type --</option>
					<?php foreach($site_types as $site_type){	?>
						<option><?php echo $site_type; ?></option>
					<?php } ?>
				</select>			
			</div>
			<div class="section three_column">
				<select name="site_client">
					<option disabled selected>-- Site Client --</option>
					<?php foreach($clients as $client){	?>
						<option><?php echo $client->client_name; ?></option>
					<?php } ?>
				</select>		
			</div>
			<div class="section three_column">
				<select name="site_platform">
					<option disabled selected>-- Site Platform --</option>
					<?php foreach($site_platforms as $site_platform){ ?>
						<option><?php echo $site_platform; ?></option>
					<?php } ?>
				</select>		
			</div>
			<div class="border_separator"></div>
			<div class="section two_column">
				<input type="text" name="site_login_url" placeholder="Site Login URL" />
				<input type="text" class="site_username input_float_left" name="site_username" placeholder="Site Login Username" />
				<input type="text" class="site_password input_float_left" name="site_password" placeholder="Site Login Password" />					
			</div>
			<div class="section two_column">
				<input type="text" class="site_hosting_url" name="site_hosting_url" placeholder="Hosting URL" />
				<input type="text" class="site_hosting_username input_float_left" name="site_hosting_username" placeholder="Hosting Username" />
				<input type="text" class="site_hosting_password input_float_left" name="site_hosting_password" placeholder="Hosting Password" />
			</div>
			<div class="border_separator"></div>
			<div class="section two_column">
				<input type="text" class="site_mysql_url" name="site_mysql_url" placeholder="MySQL URL" />
				<input type="text" class="site_mysql_username input_float_left" name="site_mysql_username" placeholder="MySQL Username" />
				<input type="text" class="site_mysql_password input_float_left" name="site_mysql_password" placeholder="MySQL Password" />
			</div>
			<div class="section two_column">
				<input type="text" class="site_database_name input_float_left" name="site_database_name" placeholder="Database Name" />
				<input type="text" class="site_database_password input_float_left" name="site_database_password" placeholder="Database Password" />
			</div>
			<div class="border_separator"></div>
			<div class="add_website_buttons">				
				<div class="save_website button_1" />Add Website</div>
				<div style="display:none;" class="add_site_loader"></div>
			</div>
		</form>
	</div>
</div>
<?php get_footer(); ?>