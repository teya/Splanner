<?php /* Template Name: Person */ ?>
<?php get_header(); ?>
<?php 
$current_user = wp_get_current_user();
$current_user_name = $current_user->data->display_name;
$current_user_role = $current_user->roles['0'];
?>
<?php
if(isset($_GET['deleteID'])) {
	$id = $_GET['deleteID'];	
	global $wpdb;	
	$table_name = $wpdb->prefix . "custom_person";
	
	$wpdb->query( "DELETE FROM {$table_name} WHERE ID = '$id'" ) ;
}
?>
<div class="persons">
	<a id="create_person" class="button_1" href="<?php echo get_site_url(); ?>/add-people/">+ Add Person</a>
</div>
<div class="display_main persons">
<?php 
	$table_name = $wpdb->prefix . "custom_person"; 
	$invoice_tablename = $wpdb->prefix . "custom_invoice_table";
	$active_people = $wpdb->get_results("SELECT * FROM {$table_name} WHERE person_status='0'");
	$inactive_people = $wpdb->get_results("SELECT * FROM {$table_name} WHERE person_status='1'");
	$active_people_count = (count($active_people));	
	$inactive_people_count = (count($inactive_people));	
?>		
		<h2 class="display_title active_people_header">Active Employees (<span class="person_count"><?php echo $active_people_count; ?></span>)</h2>
		<div class="active_people_container">
			<?php 
				foreach($active_people as $person){
					$person_fullname = $person->person_first_name . ' ' . $person->person_last_name;
			?>
			<div class="display_section display_section_archive_<?php echo $person->ID; ?> delete_ajax_<?php echo $person->ID; ?>">
				<div class="display_list" onclick="window.open('<?php echo get_site_url(); ?>/person-information/?id=<?php echo $person->ID ?>');">
					<?php if($current_user_name == $person_fullname || $current_user_role == 'administrator'){ ?>
					<a class="button_2 display_button" href="<?php echo get_site_url(); ?>/edit-people/?editID=<?php echo $person->ID ?>">Edit</a>
					<?php } ?>
					<h3 id="name_<?php echo $person->ID; ?>" class="display_subtitle float_left"><?php echo $person_fullname; ?></h3>
					<p class="display_hourly_rate">(kr <?php echo $person->person_hourly_rate; ?>/hr)</p>
					<p class="display_permission"><?php echo $person->person_permission; ?></p>			
				</div>
				<div class="ajax_action_buttons">
					<?php if($current_user_name == $person_fullname || $current_user_role == 'administrator'){ ?>
						<div id="delete_person_<?php echo $person->ID; ?>" class="button_2 display_button float_right delete_person_button delete_ajax">Delete</div>
						<div id="archive_person_<?php echo $person->ID; ?>" class="button_2 display_button float_right archive_person_button">Archive</div>
					<?php } ?>

					<?php if($current_user->ID == 2 OR $current_user->ID == $person->wp_user_id){ 
							if($person->wp_user_id != 2){
					?>
						<div id="invoice_person_<?php echo $person->ID; ?>" class="regenerate-invoice" title="Regenerate Invoice"><i class="fa fa-repeat" aria-hidden="true"></i></div>
						<a  href="<?php echo get_site_url();  ?>/view-invoice/?user_id=<?php echo $person->wp_user_id; ?>" class="button_2 display_button float_right delete_person_button delete_ajax">View Invoice</a>

					<?php 
						}
					} 
					?>
				</div>
				<div class="display_separator"></div>
			</div>
			<?php } ?>
		</div>		
		<h2 class="display_title inactive_people_header">Inactive Employees (<span class="person_count"><?php echo $inactive_people_count; ?></span>)</h2>
		<div class="inactive_people_container">
			<?php 
				foreach($inactive_people as $person){
					$person_fullname = $person->person_first_name . ' ' . $person->person_last_name;
				?>
				<div class="display_section display_section_unarchive_<?php echo $person->ID; ?> delete_ajax_<?php echo $person->ID; ?>">
					<div class="display_list" onclick="window.open('/person-information/?id=<?php echo $person->ID ?>');">
						<?php if($current_user_name == $person_fullname || $current_user_role == 'administrator'){ ?>
							<a class="button_2 display_button" href="<?php echo get_site_url(); ?>/edit-people/?editID=<?php echo $person->ID ?>">Edit</a>
						<?php } ?>
						<h3 id="name_<?php echo $person->ID; ?>" class="display_subtitle float_left"><?php echo $person_fullname; ?></h3>
						<p class="display_hourly_rate">(kr <?php echo $person->person_hourly_rate; ?>/hr)</p>
						<p class="display_permission"><?php echo $person->person_permission; ?></p>			
					</div>
					<div class="ajax_action_buttons">
						<?php if($current_user_name == $person_fullname || $current_user_role == 'administrator'){ ?>
							<div id="delete_person_<?php echo $person->ID; ?>" class="button_2 display_button float_right delete_person_button delete_ajax">Delete</div>
							<div id="unarchive_person_<?php echo $person->ID; ?>" class="button_2 display_button float_right unarchive_person_button">Unarchive</div>
						<?php } ?>
					</div>
					<div class="display_separator"></div>
				</div>
				<?php } ?>
		</div>
</div>
<div style="display:none;" id="dialog_form_archive_people" title="Archive Person">
	<form id="archive_person">
		<p>Are you sure you want to archive <span class="span_bold"></span>?</p>	
		<div class="button_1 archive_person">Archive</div>
		<div style="display:none" class="loader"></div>
		<input type="hidden" class="person_name" name="person_name" />		
		<input type="hidden" class="person_id" name="person_id" />		
	</form>
</div>
<div style="display:none;" id="dialog_form_unarchive_people" title="Unarchive Person">
	<form id="unarchive_person">
		<p>Are you sure you want to unarchive <span class="span_bold"></span>?</p>	
		<div class="button_1 unarchive_person">Unarchive</div>
		<div style="display:none" class="loader"></div>
		<input type="hidden" class="person_name" name="person_name" />		
		<input type="hidden" class="person_id" name="person_id" />		
	</form>
</div>
<div style="display:none;" class="dialog_form_delete_ajax" id="dialog_form_delete_people" title="Delete Person">
	<form class="delete_action_ajax" id="delete_person">
		<p class="label">
			Are you sure you want to delete 
			<span class="delete_name"></span>
			<span style="display:none;" class="delete_prep">from</span>
			<span style="display:none;" class="delete_parent"></span>
		</p>	
		<div class="button_1 delete_button_ajax">Delete</div>
		<div style="display:none" class="loader delete_ajax_loader"></div>
		<input type="hidden" class="delete_id" name="delete_id" />
		<input type="hidden" class="delete_type" name="delete_type" value="Person" />		
	</form>
</div>
<!-- show comfirm regenerate invoice -->
<div style="display:none;" class="confirm_regenerate_invoice_dialog" id="confirm_regenerate_invoice_dialog" title="Regenerate Invoice">
	<form class="delete_action_ajax" id="delete_project">
		<p class="label">
			Are you sure you want to regenerate <span id="invoice-name-person"></span> Invoice's for the Month of <span id="regenerate-invoice-month"></span>?<br />
		</p>
		<input id="invoice_person_id" type="hidden">	
		<div id="confirmed_regenerate_invoice" class="button_1">Regenerate Invoice</div>
		<div style="display:none;" class="loader approve_invoice_by_admin_ajax_loader regenerate_invoice_loader"></div>		
	</form>
</div>
<!-- show successfully regenerate invoice message-->
<div style="display:none;" class="show_successfully_regenerate_invoice" id="show_successfully_regenerate_invoice" title="Regenerate Invoice">
	<form class="delete_action_ajax" id="delete_project">
		<p class="label">
			You have successfully Regenerate Invoice.
		</p>
		<div id="show_successfully_regenerate_invoice_close" class="button_1">OK</div>
		<div style="display:none;" class="loader approve_invoice_by_admin_ajax_loader"></div>		
	</form>
</div>
<script type="text/javascript">
 	jQuery(document).ready(function(){
 		jQuery(document).on('click','#confirmed_regenerate_invoice', function(){
 			jQuery(this).prop('disabled', true);
 			var person_id = jQuery('#invoice_person_id').val();
 			jQuery('.approve_invoice_by_admin_ajax_loader').show();

 			var data = {
 				'person_id' : person_id
 			}

 			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_info' : data,
						'type' : 'regenerate_invoice',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);


					if(parsed.status == 'successfully-regenerate-invoice'){
						jQuery('#confirm_regenerate_invoice_dialog').dialog('close');
						jQuery('#show_successfully_regenerate_invoice').dialog('open');
						jQuery('.approve_invoice_by_admin_ajax_loader').hide();
					}else{
						jQuery('#confirm_regenerate_invoice_dialog').dialog('close');
					}

					jQuery("#confirmed_regenerate_invoice").prop('disabled', false);
				},
				error: function (data) {
					alert('error');
				}				
			});	

 		});
 		
 		//Show confirmmation Regeneration dialog box.
 		jQuery(document).on('click', '.regenerate-invoice', function(){
 			var months = [ "January", "February", "March", "April", "May", "June", 
               "July", "August", "September", "October", "November", "December" ];
 			var string_id = jQuery(this).attr('id').split("_");
 			var name = jQuery('#name_'+string_id[2]).text();
 			jQuery('#invoice-name-person').text(name);
 			jQuery('#confirm_regenerate_invoice_dialog').dialog('open');	
			var makeDate = new Date();
			makeDate = new Date(makeDate.setMonth(makeDate.getMonth() - 1));
			var month = makeDate.getMonth();
			var year = makeDate.getFullYear();
			jQuery('#invoice_person_id').val(string_id[2]);
			jQuery('#regenerate-invoice-month').text(months[month]+"-"+year);
 		});

 		jQuery(document).on('click', '#show_successfully_regenerate_invoice_close', function(){
 			jQuery('#show_successfully_regenerate_invoice').dialog('close');
 		});
		//Show successfully Invoice dialog box
		jQuery( ".show_successfully_regenerate_invoice" ).dialog({
			autoOpen: false,
			height: 170,
			width: 350,
			modal: true,
			close: function() {
			}
		});	
		//Confirm Invoice
		jQuery( ".confirm_regenerate_invoice_dialog" ).dialog({
			autoOpen: false,
			height: 170,
			width: 350,
			modal: true,
			close: function() {
			}
		});	
 	});

</script>
<?php get_footer(); ?>