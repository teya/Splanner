<?php /* Template name: Add Client */ ?>
<?php 
get_header(); 
global $wpdb;			
$table_name = $wpdb->prefix . "custom_client";
$table_name_monthly_plan = $wpdb->prefix . "custom_monthly_plan";
$monthly_plans = $wpdb->get_col("SELECT DISTINCT monthly_name FROM {$table_name_monthly_plan}");
$client_satisfaction_array = array('Happy', 'Satisfied', 'Not Satisfied', 'Upset');
?>
<div class="add_client">
	<form action="" method="post" name="client" id="client">
		<div class="section">
			<div class="left">
				<p class="label">Name</p>
			</div>
			<div class="right">
				<input type="text" class="client_name" name="client_name" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Address</p>
			</div>
			<div class="right">
				<textarea name="client_address" class="client_address textarea_wide"></textarea>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Contact Person</p>
			</div>
			<div class="right">
				<input type="text" class="client_contact_person" name="client_contact_person" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Contact Phone</p>
			</div>
			<div class="right">
				<input type="text" class="client_contact_phone" name="client_contact_phone" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Contact E-mail</p>
			</div>
			<div class="right">
				<input type="text" class="client_contact_email" name="client_contact_email" />
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Monthly Plan</p>
			</div>
			<div class="right">
				<select class="client_monthly_plan" name="client_monthly_plan">
					<option selected value="">- Select -</option>
					<?php 
						$client_monthly_plan_array = array();
						foreach ($monthly_plans as $monthly_plan){
							$client_monthly_plan_array[] = $monthly_plan;
						}
						sort($client_monthly_plan_array);
					?>
					<?php foreach($client_monthly_plan_array as $client_monthly_plan){ ?>
						<option><?php echo $client_monthly_plan; ?></option>
					<?php }?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Customer Satisfaction</p>
			</div>
			<div class="right">
				<select class="client_satisfaction" name="client_satisfaction">
					<option selected value="">Satisfied</option>
					<?php foreach ($client_satisfaction_array as $client_satisfaction){	?>
					<?php if($client_satisfaction != 'Satisfied'){ ?>
						<option><?php echo $client_satisfaction; ?></option>
					<?php }?>
					<?php }?>
				</select>
			</div>
		</div>
		<div class="border_separator"></div>
		<input type="submit" name="submit" class="button_1" value="Add Client" />
		<a class="button_2" href="/client/">Cancel</a>
	</form>
</div>
<?php
if(isset($_POST['submit'])):
	global $wpdb;
	$client_name				= (isset($_POST['client_name']) ? $_POST['client_name'] : '');
	$client_address				= (isset($_POST['client_address']) ? $_POST['client_address'] : '');
	$client_contact_person		= (isset($_POST['client_contact_person']) ? $_POST['client_contact_person'] : '');
	$client_contact_phone		= (isset($_POST['client_contact_phone']) ? $_POST['client_contact_phone'] : '');
	$client_contact_email		= (isset($_POST['client_contact_email']) ? $_POST['client_contact_email'] : '');
	$client_monthly_plan		= (isset($_POST['client_monthly_plan']) ? $_POST['client_monthly_plan'] : '');
	$client_status				= 1;
		
	$insert = $wpdb->insert( $table_name , array( 
	'client_name'				=> $client_name,
	'client_address'			=> $client_address,
	'client_contact_person'		=> $client_contact_person,
	'client_contact_phone'		=> $client_contact_phone,
	'client_contact_email'		=> $client_contact_email,
	'client_monthly_plan'		=> $client_monthly_plan,
	'client_satisfaction'		=> $client_satisfaction,
	'client_status'				=> $client_status
	), array( '%s', '%s' ));
	
	if($insert == 1):
		echo "<p class='message'>";
		echo "Client Added!";
	else:
		echo "Client was not successfully added.";
		echo "</p>";
	endif;
	
endif;
?>
<?php get_footer(); ?>