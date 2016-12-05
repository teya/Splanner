<?php /* Template name: Project Information */ ?>
<?php get_header(); 
$id = $_GET['id'];
$table_name = $wpdb->prefix . "custom_project"; 
$project = $wpdb->get_row("SELECT * FROM {$table_name} WHERE ID = '$id'");
$table_name_color = $wpdb->prefix . "custom_project_color";
$colors = $wpdb->get_results("SELECT * FROM {$table_name_color}");

?>
<div class="info_project">
	<div class="section">
		<div class="left">
			<p class="label">Client</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_client; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Department</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_department; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Project Name</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_name; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>	
	<div class="section">
		<div class="left">
			<p class="label">Start date</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_start_date; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Estimated Deadline</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_estimated_deadline; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Date Completed</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_date_completed; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Time Spent</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_hour .":". $project->project_minute; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Responsible worker</p>
		</div>		
		<div class="right">
			<p><?php echo $project->project_responsible_worker; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Current Status</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_current_status; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Color</p>
		</div>
		<div class="right">	
			<?php 
				foreach($colors as $color){
					if ($color->project_category == $project->project_category){
			?>		
					<p><?php echo $project->project_category; ?></p>
			<?php 
					} 
				} 
			 ?>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Demo url</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_demo_url; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Login credentials</p>
		</div>
		<div class="right">
			<div class="full_width">
				Username: <p><?php echo $project->project_login_username; ?></p>
			</div>
			<div class="full_width">
				Password: <p><?php echo $project->project_login_username; ?></p>
			</div>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">	
		<div class="left">
			<p class="label">Invoice Method</p>
		</div>
		<div class="right">
			<?php if($project->project_invoice_method == 1): ?>
			<p><?php echo "Billable"; ?></p>
			<?php else: ?>
			<p><?php echo "Non Billable"; ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">	
		<div class="left">
			<p class="label">Invoice Date</p>
		</div>
		<div class="right">
			<?php echo $project->project_invoice_date; ?>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">	
		<div class="left">
			<p class="label">Invoice Amount</p>
		</div>
		<div class="right">
			<?php echo $project->project_invoiced_amount; ?>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">	
		<div class="left">
			<p class="label">Extra Expenses</p>
		</div>
		<div class="right">
			<?php echo $project->project_extra_expenses; ?>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">	
		<div class="left">
			<p class="label">Default Expenses</p>
		</div>
		<div class="right">
			<?php echo $project->project_default_expenses; ?>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Budget</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_budget; ?></p>
		</div>
	</div>
	<div class="border_separator"></div>
	<div class="section">
		<div class="left">
			<p class="label">Description</p>
		</div>
		<div class="right">
			<p><?php echo $project->project_description; ?></p>
		</div>
	</div>
	<a class="button_2 display_button" href="/projects/">Return</a>
	<a id="create_projects" class="button_1 display_button padding_button" href="/add-project/">+ Add Project</a>
	<a class="button_2 display_button" href="/edit-project/?editID=<?php echo $project->ID ?>">Edit</a>
	<a class="button_2 display_button confirm" href="/projects/?deleteID=<?php echo $project->ID ?>">Delete</a>
</div>