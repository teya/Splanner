<?php 
/* Template Name: View Invoice */ 
?>
<?php get_header(); ?>

<?php
// echo "<pre>";
// print_r($current_user);
// echo "</pre>";

	global $wpdb;
	$current_user = wp_get_current_user();
	$invoice_id = $_GET['id'];
	$persons_tablename = $wpdb->prefix.'custom_person';
	$invoice_tablename = $wpdb->prefix.'custom_invoice_table';
	$invoice_info = $wpdb->get_row('SELECT * FROM '.$invoice_tablename.' WHERE id = '.$invoice_id);

	// print_r($current_user->id);
	if($current_user->id == 2){
		$person_info = $wpdb->get_row('SELECT * FROM '.$persons_tablename.' WHERE wp_user_id = '.$invoice_info->person_id);
	}else{
		$person_info = $wpdb->get_row('SELECT * FROM '.$persons_tablename.' WHERE wp_user_id = '.$current_user->ID);
	}
	
	$client_list_table = unserialize($invoice_info->clients_invoices_table);
	$total_client_hours = 0;
?>

<div id="invoice-wrapper">
	<div class="">
		<div class="invoice-pc-icon">
				<img src="<?php echo get_template_directory_uri() ?>/images/invoice-pc-icon.png" alt="Invoice PC Icon">
		</div>
		<div class="">
			<h1><?php echo $person_info->person_fullname; ?></h1>
			<p class="person-address"><?php echo $person_info->person_address; ?></p>
			<p>Contact Number: <?php echo $person_info->person_mobile ?></p>
			<p>TIN: <span class="email pull-right">Email: <?php echo $person_info->person_email; ?></span></p>
			<input id="invoice_id" type="hidden" value="<?php echo $invoice_info->id; ?>">
		</div>
		<div>
	</div>
	<div>
		<table class="invoice-top-table">
			<tr>
				<td>Billed to:</td>
				<td><p>SEOWEB Solutions</p></td>
				<td></td>
				<td>Invoice No:</td>
				<td>4</td>
			</tr>
			<tr>
				<td>Address:</td>
				<td><p>Gärdsåsgatan 55A</p><p>415 16 Gothenburg, Sweden</p></td>
				<td></td>
				<td>Date:</td>
				<td><p><?php echo date('F-Y', strtotime('-1 Month')); ?></p></td>
			</tr>
		</table>
	</div>
	<table id="invoice-table">
		<thead>
			<tr>
				<th>Unit</th>
				<th>Description</th>
				<th>#Hours for the Month</th>
				<th>PRICE $</th> 
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($client_list_table as $row){ ?>
				<tr>
					<td></td>
					<td class="clientname"><?php echo $row['clientname']; ?></td>
					<td><span class="total_hours_edit"><?php echo $row['total_hours']; ?></span></td>
					<td></td>
					<td></td>
				</tr>
			<?php 
				$total_client_hours += round($row['total_hours'], 2); 
			} ?>
		</tbody>
	</table>
	<div class="pull-right">
		<p>Total Hours: <span class="invoice_total_hours"><?php echo $total_client_hours; ?></span></p>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
		//SHow input text for udpating client total hours
		jQuery(document).on('dblclick', '.total_hours_edit', function(){
			var hours = jQuery(this).text();
			jQuery(this).html('<input type="text" class="update_client_hours" name="edit_hours" value="'+hours+'"><div class="update_client_total_hours" id=""></div><div class="invoice-row-update-loader" id="" style="display: none;"></div>');
		});

		//Updating the client total hours
		jQuery(document).on('click', '.update_client_total_hours', function(){

			var current_row = jQuery(this);
			current_row.closest('tr').find('.invoice-row-update-loader').show();
			var clientname = current_row.hide().closest('tr').children('.clientname').text();
			var update_hours = current_row.closest('.total_hours_edit').children('.update_client_hours').val();
			var invoice_id = jQuery('#invoice_id').val();

			var data = {
				'invoice_id' : invoice_id,
				'update_hours' : update_hours,
				'clientname' : clientname
			}

			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_info' : data,
						'type' : 'update_invoice_table',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);
					current_row.closest('tr').find('.total_hours_edit').text(parsed.update_hours);
				},
				error: function (data) {
					alert('error');
				}				
			});
		});
	});
</script>
<?php get_footer(); ?>