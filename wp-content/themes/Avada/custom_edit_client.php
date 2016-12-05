<?php /* Template Name: Edit Client */ ?>
<?php get_header();
global $wpdb;			
$table_name = $wpdb->prefix . "custom_client";
$table_name_monthly_plan = $wpdb->prefix . "custom_monthly_plan";
$monthly_plans = $wpdb->get_results("SELECT * FROM {$table_name_monthly_plan}");
$id = $_GET['editID'];
$query = "SELECT * FROM $table_name WHERE id=$id";


 ?>
<?php
if(isset($_POST['submit'])):
	$client_name				= $_POST['client_name'];
	$client_address				= $_POST['client_address'];
	$client_contact_person		= $_POST['client_contact_person'];
	$client_contact_phone		= $_POST['client_contact_phone'];
	$client_contact_email		= $_POST['client_contact_email'];
	$client_monthly_plan		= $_POST['client_monthly_plan'];
	$client_satisfaction		= $_POST['client_satisfaction'];
	
	$update = $wpdb->update( $table_name , array( 
	'client_name'				=> $client_name,
	'client_address'			=> $client_address,
	'client_contact_person'		=> $client_contact_person,
	'client_contact_phone'		=> $client_contact_phone,
	'client_contact_email'		=> $client_contact_email,
	'client_monthly_plan'		=> $client_monthly_plan,
	'client_satisfaction'		=> $client_satisfaction
	),
	array( 'ID' => $id ),
	array( '%s', '%s' ));	
	if($update == 1):
		echo "<p class='message'>";
		echo "Client Updated!";
		echo "</p>";
	else:
		echo "<p class='message'>";
		echo "Client was not successfully Updated.";
		echo "</p>";
	endif;		
endif;
$results_edit = $wpdb->get_row($query);
?>
<?php $client_satisfaction_array = array('Happy', 'Satisfied', 'Not Satisfied', 'Upset'); ?>
<div class="edit_client">
	<form action="" method="post" name="client" id="client">
		<div class="section">
			<div class="left">
				<p class="label">Name</p>
			</div>
			<div class="right">
				<input type="text" class="client_name" name="client_name" value="<?php echo (isset($results_edit->client_name)) ? $results_edit->client_name : '';  ?>"/>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Address</p>
			</div>
			<div class="right">
				<textarea name="client_address" class="client_address textarea_wide"><?php echo (isset($results_edit->client_address)) ? $results_edit->client_address : '';  ?></textarea>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Contact Person</p>
			</div>
			<div class="right">
				<input type="text" class="client_contact_person" name="client_contact_person" value="<?php echo (isset($results_edit->client_contact_person)) ? $results_edit->client_contact_person : '';  ?>"/>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Contact Phone</p>
			</div>
			<div class="right">
				<input type="text" class="client_contact_phone" name="client_contact_phone" value="<?php echo (isset($results_edit->client_contact_phone)) ? $results_edit->client_contact_phone : '';  ?>"/>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Contact E-mail</p>
			</div>
			<div class="right">
				<input type="text" class="client_contact_email" name="client_contact_email" value="<?php echo (isset($results_edit->client_contact_email)) ? $results_edit->client_contact_email : '';  ?>"/>
			</div>
		</div>
		<div class="border_separator"></div>
		<div class="section">
			<div class="left">
				<p class="label">Monthly Plan</p>
			</div>
			<div class="right">
				<select class="client_monthly_plan" name="client_monthly_plan">
					<?php if(isset($results_edit->client_monthly_plan)){ ?>
						<option value="<?php echo $results_edit->client_monthly_plan; ?>"><?php echo $results_edit->client_monthly_plan; ?></option>
						<option value="">- Select -</option>
						<?php }else{ ?>
						<option value="">- Select -</option>
					<?php } ?>
					<?php 
						$client_monthly_plan_array = array();
						foreach ($monthly_plans as $monthly_plan){
							$client_monthly_plan_array[] = $monthly_plan->monthly_name;
						}
						sort($client_monthly_plan_array);
					?>					
					<?php 
						foreach($client_monthly_plan_array as $client_monthly_plan){
							if($client_monthly_plan != $results_edit->client_monthly_plan){
							?>
							<option><?php echo $client_monthly_plan; ?></option>
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
				<p class="label">Customer Satisfaction</p>
			</div>
			<div class="right">
				<select class="client_satisfaction" name="client_satisfaction">
					<?php if(isset($results_edit->client_satisfaction)){ ?>
						<option value="<?php echo $results_edit->client_satisfaction; ?>"><?php echo $results_edit->client_satisfaction; ?></option>					
					<?php
							foreach($client_satisfaction_array as $client_satisfaction){
								if($client_satisfaction != $results_edit->client_satisfaction){
								?>
								<option><?php echo $client_satisfaction; ?></option>
					<?php 
								}
							}
						}else{
					?>
						<option selected value="">- Select -</option>
						<?php foreach ($client_satisfaction_array as $client_satisfaction){	?>					
							<option><?php echo $client_satisfaction; ?></option>
						<?php }?>
					<?php } ?>
				</select>				
			</div>
		</div>
		<div class="border_separator"></div>		
		<input type="submit" name="submit" class="add_client button_1" value="Edit Client" />
		<a class="button_2" href="/client/">Cancel</a>
	</form>
</div>

<?php get_footer(); ?>