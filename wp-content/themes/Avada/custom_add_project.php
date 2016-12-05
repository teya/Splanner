<?php /* Template name: Add Project */ ?>
<?php get_header(); ?>
<?php
global $wpdb;
$table_name = $wpdb->prefix . "custom_project";			
$table_name_client = $wpdb->prefix . "custom_client";
$table_name_person = $wpdb->prefix . "custom_person";
$table_name_department = $wpdb->prefix . "custom_department";
$table_name_color = $wpdb->prefix . "custom_project_color";
$table_name_website = $wpdb->prefix . "custom_website";
$table_name_hosting_domain = $wpdb->prefix . "custom_hosting_domain";
$client_names = $wpdb->get_col("SELECT DISTINCT client_name FROM {$table_name_client}");	
$persons = $wpdb->get_results("SELECT * FROM {$table_name_person} WHERE person_status='0'");
$departments = $wpdb->get_col("SELECT DISTINCT department_name FROM {$table_name_department}");
$project_colors = $wpdb->get_results("SELECT * FROM {$table_name_color}");
$websites = $wpdb->get_results("SELECT * FROM {$table_name_website}");
$hosting_domain = $wpdb->get_results("SELECT * FROM $table_name_hosting_domain");
?>
<?php 
if(isset($_POST['submit'])):
	global $wpdb;
	foreach ($_POST['project_client'] as $client){		
		$project_client					= $client;
		$project_name					= (isset($_POST['project_name']) ? $_POST['project_name'] : '');
		$project_department				= (isset($_POST['project_department']) ? $_POST['project_department'] : '');
		$project_start_date				= (isset($_POST['project_start_date']) ? $_POST['project_start_date'] : '');
		$project_estimated_deadline		= (isset($_POST['project_estimated_deadline']) ? $_POST['project_estimated_deadline'] : '');
		$project_date_completed			= (isset($_POST['project_date_completed']) ? $_POST['project_date_completed'] : '');
		$project_hour					= (isset($_POST['project_hour']) ? $_POST['project_hour'] : '');
		$project_minute					= (isset($_POST['project_minute']) ? $_POST['project_minute'] : '');
		$project_responsible_worker		= (isset($_POST['project_responsible_worker']) ? $_POST['project_responsible_worker'] : '');
		$project_current_status			= (isset($_POST['project_current_status']) ? $_POST['project_current_status'] : '');
		$project_site_url				= (isset($_POST['project_site_url']) ? $_POST['project_site_url'] : '');
		$project_demo_url				= (isset($_POST['project_demo_url']) ? $_POST['project_demo_url'] : '');
		$project_login_username			= (isset($_POST['project_login_username']) ? $_POST['project_login_username'] : '');
		$project_login_password			= (isset($_POST['project_login_password']) ? $_POST['project_login_password'] : '');
		$project_invoice_method			= $_POST['project_invoice_method'];
		if(isset($_POST['project_invoice_method'])):
			$project_invoice_method 	= $_POST['project_invoice_method']; (isset($_POST['project_department']) ? $_POST['project_department'] : '');
		else:
			$project_invoice_method 	= 0;
		endif;
		
		$project_invoiced_amount		= (isset($_POST['project_invoiced_amount']) ? $_POST['project_invoiced_amount'] : '');
		$project_extra_expenses			= (isset($_POST['project_extra_expenses']) ? $_POST['project_extra_expenses'] : '');
		$project_invoice_date			= (isset($_POST['project_invoice_date']) ? $_POST['project_invoice_date'] : '');
		$project_budget					= (isset($_POST['project_budget']) ? $_POST['project_budget'] : '');
		$project_description			= (isset($_POST['project_description']) ? $_POST['project_description'] : '');
		$project_status					= 'unarchived';
		
		$department_budget = $wpdb->get_row("SELECT * FROM $table_name_department WHERE department_name='$project_department'");
		$project_default_expenses		= $department_budget->department_default_expenses;
		
		$insert = $wpdb->insert( $table_name , array( 
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
		'project_site_url'				=> $project_site_url,
		'project_demo_url'				=> $project_demo_url,
		'project_login_username'		=> $project_login_username,
		'project_login_password'		=> $project_login_password,
		'project_invoice_method'		=> $project_invoice_method,
		'project_invoiced_amount'		=> $project_invoiced_amount,
		'project_default_expenses'		=> $project_default_expenses,
		'project_extra_expenses'		=> $project_extra_expenses,
		'project_invoice_date'			=> $project_invoice_date,
		'project_budget'				=> $project_budget,
		'project_description'			=> $project_description,
		'project_status'				=> $project_status
		), array( '%s', '%s' ));
	}
	// $wpdb->show_errors();
	// $wpdb->print_error();
	if($insert == 1):
		echo "<p class='message'>";
		echo "Project Added!";
	else:
		echo "Project was not successfully added.";
		echo "</p>";
	endif;	
endif;
?>
<?php $current_status_array = array('Monthly Ongoing', 'Quote sent', 'Planning', 'Setup', 'Design', 'Functionality', 'Adjustments', 'Invoiced', 'Cancelled'); ?>
<script>
	jQuery(document).ready(function(){
		jQuery('.project_start_date').datepicker();
		jQuery('.project_estimated_deadline').datepicker();
		jQuery('.project_date_completed').datepicker();
		jQuery('.project_invoice_date').datepicker();
	});
</script>
<div class="add_project">
	<form action="" method="post" name="project" id="project">
		<div class="section">
			<div class="left">
				<p class="label">Client</p>
			</div>
			<div class="right">
				<select multiple class="project_client" name="project_client[]">
					<?php 
						$project_client_array = array();
						foreach ($client_names as $client_name){
							$project_client_array[] = $client_name;
						}
						sort($project_client_array);
					?>
					<?php foreach($project_client_array as $project_client){ ?>
					<option><?php echo $project_client; ?></option>
					<?php } ?>
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
					<?php 
						$project_department_array = array();
						foreach ($departments as $department){
							$project_department_array[] = $department;
						}
						sort($project_department_array);
					?>
					<?php foreach ($project_department_array as $project_department){ ?>
					<option><?php echo $project_department; ?></option>
					<?php } ?>
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
					<?php 
						$project_color_array = array();
						foreach ($project_colors as $project_color){
							$project_color_array[] = $project_color->project_category;
						}
						sort($project_color_array);
					?>
					<?php foreach($project_color_array as $project_color){ ?>
					<option><?php echo $project_color;  ?></option>
					<?php } ?>
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
				<input type="text" name="project_start_date" class="project_start_date" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Estimated Deadline</p>
			</div>
			<div class="right">
				<input type="text" name="project_estimated_deadline" class="project_estimated_deadline" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Date Completed</p>
			</div>
			<div class="right">
				<input type="text" name="project_date_completed" class="project_date_completed" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Time Spent</p>
			</div>
			<div class="right">
				<input type="text" name="project_hour" class="project_hour" /> h
				<input type="text" name="project_minute" class="project_minute" /> m
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Responsible worker</p>
			</div>
			<div class="right">
				<select name="project_responsible_worker" class="project_responsible_worker">
					<?php 
						$project_person_array = array();
						foreach ($persons as $person){
							$project_person_array[] = $person->person_first_name ." ".  $person->person_last_name;
						}
						sort($project_person_array);
					?>
					<?php foreach ($project_person_array as $project_person){ ?>
					<option><?php echo $project_person; ?></option>
					<?php } ?>
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
					<?php 
						$project_current_status_array = array();
						foreach ($current_status_array as $current_status){
							$project_current_status_array[] = $current_status;
						}
						sort($project_current_status_array);
					?>
					<?php foreach ($project_current_status_array as $project_current_status){ ?>
						<option><?php echo $project_current_status; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Website URL</p>
			</div>
			<div class="right">				
				<select name="project_site_url" class="project_site_url" >
					<?php 
						$project_website_array = array();
						foreach ($websites as $website){
							if($website->site_type == 'Main site' || $website->site_type == 'Secondary site'){
								$project_website_array[] = $website->site_url;
							}
						}
						sort($project_website_array);
					?>
					<?php foreach($project_website_array as $project_website){ ?>
						<option><?php echo $project_website;  ?></option>
					<?php } ?>
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
				<input type="checkbox" name="project_invoice_method" value="1" class="project_invoice_method checkbox" checked>Billable
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Invoice Date</p>
			</div>
			<div class="right">
				<input type="text" name="project_invoice_date" class="project_invoice_date">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Invoice Amount</p>
			</div>
			<div class="right">
				<input type="text" name="project_invoiced_amount" class="project_invoiced_amount">
			</div>
		</div>
		<div class="border_separator"></div>		
		<div class="section">	
			<div class="left">
				<p class="label">Extra Expenses</p>
			</div>
			<div class="right">
				<input type="text" name="project_extra_expenses" class="project_extra_expenses">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">	
			<div class="left">
				<p class="label">Default Expenses</p>
			</div>
			<div class="right">
				<input type="text" name="project_default_expenses" class="project_default_expenses">
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Budget</p>
			</div>
			<div class="right">
				<p class="right_label">Estimated Project Budget</p>
				<input type="text" name="project_budget" class="project_budget input_float_left">
				<p class="right_label">[SEK]</p>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Description</p>
			</div>
			<div class="right">
				<textarea name="project_description" class="project_description textarea_wide"></textarea>
			</div>
		</div>
		<input type="submit" name="submit" class="button_1" value="Add Project" />
		<a class="button_2" href="/projects/">Cancel</a>
	</form>
</div>
<div style="display:none;" id="dialog_form_website_add" title="Add Website">
	<?php 
		$table_name_client = $wpdb->prefix . "custom_client";
		$clients = $wpdb->get_results("SELECT * FROM $table_name_client");
		$site_types = array('Main site', 'Secondary site', 'Demo site', 'PBN Site');
		$site_platforms = array('Wordpress', 'Drupal', 'Web2.0');
		$site_domain_owners = array('Customer', 'SEOWEB');
	?>
	<div class="add_website website_style">
		<form action="" method="post" name="website" id="website">
			<div class="section first_section">
				<div class="left">
					<p class="label">Site URL:</p>
				</div>
				<div class="right">
					<input type="text" name="site_url" class="site_url" placeholder="http://">	
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
			<div class="section three_column last">
				<select name="site_platform">
					<option disabled selected>-- Site Platform --</option>
					<?php foreach($site_platforms as $site_platform){ ?>
						<option><?php echo $site_platform; ?></option>
					<?php } ?>
				</select>		
			</div>
			<div class="border_separator"></div>
			<div class="section four_column">
				<input type="text" name="site_login_url" placeholder="Site Login URL" />
				<input type="text" class="site_username input_float_left" name="site_username" placeholder="Site Login Username" />
				<input type="text" class="site_password input_float_left" name="site_password" placeholder="Site Login Password" />					
			</div>
			<div class="section four_column">
				<input type="text" class="site_mysql_url" name="site_mysql_url" placeholder="MySQL URL" />
				<input type="text" class="site_mysql_username input_float_left" name="site_mysql_username" placeholder="MySQL Username" />
				<input type="text" class="site_mysql_password input_float_left" name="site_mysql_password" placeholder="MySQL Password" />
			</div>
			<div class="section four_column">
				<input type="text" class="site_database_name input_float_left" name="site_database_name" placeholder="Database Name" />
				<input type="text" class="site_database_username input_float_left" name="site_database_username" placeholder="Database Username" />
				<input type="text" class="site_database_password input_float_left" name="site_database_password" placeholder="Database Password" />
			</div>
			<div class="section four_column last">
				<input type="text" class="site_ftp_server input_float_left" name="site_ftp_server" placeholder="FTP server" />
				<input type="text" class="site_ftp_username input_float_left" name="site_ftp_username" placeholder="FTP Username" />
				<input type="text" class="site_ftp_password input_float_left" name="site_ftp_password" placeholder="FTP Password" />
			</div>
			<div class="border_separator"></div>
			<div class="section two_column">
				<div class="left">
					<select class="site_hosting_name" name="site_hosting_name">
						<option>Unknown</option>
						<?php 
							foreach($hosting_domain as $hosting){
								$hosting_name = $hosting->site_hosting_name;
								if($hosting_name != null){
								?>
								<option><?php echo $hosting_name; ?></option>
								<?php	
								}
							}
						?>
					</select>
					<div class="button_2 add_hosting add_other_hosting_domain">Add Hosting</div>
					<div style="display: none;" class="add_hosting_url add_hosting_domain_div">
						<div class="add_url_details">
							<div class="hosting_domain_input">
							<input type="text" name="add_site_hosting_name" class="add_site_hosting_name add_hosting_domain_input" placeholder="Hosting Name" />			
							<input type="text" name="add_site_hosting_url" class="add_site_hosting_url add_hosting_domain_input" placeholder="Hosting URL" />
							</div>
							<div class="hosting_domain_input">
							<input type="text" name="add_site_hosting_username" class="add_site_hosting_username add_hosting_domain_input" placeholder="Hosting Username" />
							<input type="text" name="add_site_hosting_password" class="add_site_hosting_password add_hosting_domain_input" placeholder="Hosting Password" />
							</div>
							<div class="button_1 save_hosting_url">Add</div>
						</div>
						<div style="display: none;" class="loader hosting_domain_loader"></div>
					</div>
				</div>	
			</div>
			<div class="section two_column last">
				<div class="left">
					<?php $domain_count = count($domains); ?>
					<select class="site_domain_name" name="site_domain_name">
						<option>Unknown</option>
						<optgroup label = "Domain Registrars">
							<?php 
								foreach($hosting_domain as $domain){
									$domain_name = $domain->site_domain_name;
									if($domain_name != null){ 
									?>
									<option><?php echo $domain_name; ?></option>
									<?php	
									}
								}
							?>
						</optgroup>
						<optgroup label = "Hosting">
							<?php 
								foreach($hosting_domain as $hosting){
									$hosting_name = $hosting->site_hosting_name;
									if($hosting_name != null){
									?>
									<option><?php echo $hosting_name; ?></option>
									<?php	
									}
								}
							?>
						</optgroup>
					</select>
					<div class="button_2 add_domain add_other_hosting_domain">Add Domain</div>
					<div style="display: none;" class="add_domain_url add_hosting_domain_div">
						<div class="add_url_details">
							<div class="hosting_domain_input">
								<input type="text" name="add_site_domain_name" class="add_site_domain_name add_hosting_domain_input" placeholder="Domain Name" />
								<input type="text" name="add_site_domain_url" class="add_site_domain_url add_hosting_domain_input" placeholder="Domain URL" />
							</div>
							<div class="hosting_domain_input">
								<input type="text" name="add_site_domain_username" class="add_site_domain_username add_hosting_domain_input" placeholder="Domain Username" />
								<input type="text" name="add_site_domain_password" class="add_site_domain_password add_hosting_domain_input" placeholder="Domain Password" />
							</div>
							<div class="button_1 save_domain_url">Add</div>
						</div>
						<div style="display: none;" class="loader hosting_domain_loader"></div>
					</div>
				</div>			
			</div>
			<div class="border_separator"></div>
			<div class="section three_column">
				<select name="site_domain_owner">
					<option disabled selected>-- Domain Owner --</option>
					<?php foreach($site_domain_owners as $site_domain_owner){	?>
						<option><?php echo $site_domain_owner; ?></option>
					<?php } ?>
				</select>			
			</div>
			<div class="section three_column">
				<input type="text" name="site_renewal_date" class="site_renewal_date" placeholder="Renewal date" />						
			</div>
			<div class="section three_column">
				<input type="text" name="site_cost" class="site_cost" placeholder="Cost" />	
			</div>
			<div class="border_separator"></div>
			<div class="section">
				<div class="left">
					<p class="label">Additional Information:</p>
				</div>
				<div class="right">
					<textarea name="site_additional_info" class="site_additional_info textarea_wide"></textarea>
				</div>
			</div>				
			<div class="add_website_buttons">				
				<div class="save_website button_1" />Add Website</div>
				<div style="display:none;" class="add_site_loader"></div>
			</div>
		</form>
	</div>
</div>
<?php get_footer(); ?>