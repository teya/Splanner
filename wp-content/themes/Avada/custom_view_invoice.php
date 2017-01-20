<?php 
	/* Template Name: View Invoice */ 
	if (!is_user_logged_in()) {
	    wp_redirect( wp_login_url( $redirect ) );
	    exit();
	}
	

	if($current_user->ID != 2){
		if(!empty($_GET['user_id'])){
			$person_id = $_GET['user_id'];
		}else{
			$person_id = $current_user->ID;
		}
		if($current_user->ID != $person_id){
		    wp_redirect(get_site_url().'/view-invoice/');
		    exit();	
		}		
	}else{
		$person_id = $_GET['user_id'];
	}

?>
<?php get_header(); ?>

<?php
	global $wpdb;
	$current_user = wp_get_current_user();

	$persons_tablename = $wpdb->prefix.'custom_person';
	$invoice_tablename = $wpdb->prefix.'custom_invoice_table';

	$last_month_date = date('Y-m-d', strtotime("-1 month"));

	$month =  date("m", strtotime($last_month_date));
	$year =  date("Y", strtotime($last_month_date));

	if($current_user->ID == 2){
		$person_info = $wpdb->get_row('SELECT * FROM '.$persons_tablename.' WHERE wp_user_id = '.$person_id);
	}else{
		$person_info = $wpdb->get_row('SELECT * FROM '.$persons_tablename.' WHERE wp_user_id = '.$current_user->ID);

	}

	$month = date('m', strtotime('-1 Month'));
	$month_in_num = date('n', strtotime('-1 Month'));
	$year = date('Y', strtotime('-1 Month'));

	$get_invoice_last_id = $wpdb->get_row('SELECT MAX(ID) as ID FROM '.$invoice_tablename.' WHERE person_id = '. $person_info->wp_user_id);
	$invoice_info = $wpdb->get_row('SELECT * FROM '.$invoice_tablename.' WHERE ID = '.$get_invoice_last_id->ID.' AND person_id = '. $person_info->wp_user_id);
	$count_invoice = $wpdb->get_row("SELECT COUNT(*) as count FROM ".$invoice_tablename."  WHERE person_id = ".$person_info->wp_user_id);

	$hide_prev = ($count_invoice->count == 1)? 'hide' : '';

	$client_list_table = unserialize($invoice_info->clients_invoices_table);
	$invoice_comments = unserialize($invoice_info->comments);
	$total_client_hours = 0;

?>
<?php if(!empty($invoice_info)){ ?>
	<div id="invoice-wrapper">
		<div class="top-invoice-navigation">
			<button id="invoice-nav-left-btn" class="invoice-nav-left button_1 pull-left <?php echo $hide_prev ?>">&lt;&lt;</button>
			<button id="invoice-nav-right-btn"  disabled class="invoice-nav-right button_1 pull-right hide">&gt;&gt;</button>
		</div>
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
				<input id="invoice_person_id" type="hidden" value="<?php echo $invoice_info->person_id; ?>">
				<input id="logged-in-person-name" type="hidden" value="<?php echo $current_user->display_name; ?>">
				<input id="invoice-month" type="hidden" value="<?php echo $month_in_num; ?>">
				<input id="invoice-year" type="hidden" value="<?php echo $year ?>">
			</div>
		</div>
		<div>
			<table class="invoice-top-table">
				<tr>
					<td>Billed to:</td>
					<td><p>SEOWEB Solutions</p></td>
					<td></td>
					<td>Invoice No:</td>
					<td><p><?php echo $year ."-". $month_in_num ."-". $invoice_info->person_id; ?></p></td>
				</tr>
				<tr>
					<td>Address:</td>
					<td><p>Gärdsåsgatan 55A</p><p>415 16 Gothenburg, Sweden</p></td>
					<td></td>
					<td>Date:</td>
					<td><p id="invoice_date"><?php echo $month . '-' . $year; ?></p></td>
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
						<td><?php echo $row['price']; ?></td>
						<td><?php echo $row['total']; ?></td>
					</tr>
				<?php 

					// $total_client_hours += round($row['total_hours'], 2); 
				} ?>

				<?php
					//Calculate total working days.
					$workingdays = countDays($year, $month_in_num, array(0, 6));
					//Calculate Total working hours in a Month based on person's working hours.
					$total_working_hr = $person_info->person_hours_per_day * $workingdays;
				?>
			</tbody>
		</table>
		<div class="middle-invoice-table">
			<?php if($invoice_info->person_approval != 1 OR $current_user->id == 2){ ?>
				<div id="add_row_invoice" class="button_1">Add</div>
			<?php } ?>
			<div id="remove_add_row_invoice" class="button_1"  style="display: none;">Cancel</div>
			<div class="invoice_add_new_entry_loader" style="display: none;"></div>
		</div>
		<div class="bottom-invoice-table">
			<div class="pull-left">
				<?php if($invoice_info->person_approval == 1){ ?>
					<?php if($current_user->id == 2) { ?>
						<?php // if($invoice_info->admin_approval == 0){ ?>
							<div id="approve_invoice_by_admin" class="button_1">Approve Invoice</div>
						<?php } ?>
					<?php // } ?>
				<?php }else if($invoice_info->person_approval == 0){ ?>
					<?php  if($current_user->id != 2) { ?>
						<div id="approve_invoice" class="button_1">Approve Invoice</div>
					<?php } ?>
				<?php } ?>
			</div>
			<div class="pull-right">
				<p>Total Hours: <span id="bottom_invoice_total_hours"><?php echo $invoice_info->total_hours; ?></span></p>
				<p>Total Non-Working Hours: <span id="bottom_person_total_no_work_hours"><?php echo $invoice_info->non_working_hrs; ?></span></p>
				<p>Total Salary: <span id="bottom_person_total_salary"><?php echo $invoice_info->salary; ?></span></p>
				<p>Invoice Status: <span id="bottom_person_invoice_status"><?php echo $invoice_info->status; ?></span></p>
			</div>
		</div>
		<div id="invoice-comment-section">
			<h2>Comments:</h2>
			<div class="invoices-comments-wrapper">
				<ul class="invoices-comments">

				<?php if(!empty($invoice_comments)){ ?>
					<?php foreach($invoice_comments as $comment){ ?>
					<li>
						<div class="person-profile"><?php echo $comment['person_name']; ?>:</div>
						<div class="comment-date"><?php echo $comment['datetime']; ?></div>
						<div class="person-comment"><?php echo $comment['comment']; ?></div>
					</li>
					<?php } ?>
				<?php }else{ ?>
					<li class="no-comments">No Comments Yet.</li>
				<?php } ?>
				</ul>
			</div>
			<div id="invoice-comment-form">
				<form action="">
					<textarea></textarea>
					<div id="submit-comment" class="button_2">Post Comment</div>
					<div class="invoice-comment-loader" style="display: none;"></div>
				</form>
			</div>
		</div>
	</div>
	<!-- Dialog Boxes -->
	<!-- COnfirm dialog box on person -->
	<div style="display:none;" class="confirm_approval_invoice" id="confirm_approval_invoice" title="Approve Invoice">
		<form class="delete_action_ajax" id="delete_project">
			<p class="label">
				Are you sure you want to Approve this Invoice?<br />
				This Invoice will be sent to Patrik to review.<br />
				Please make sure if everything's is correct.
			</p>	
			<div id="confirmed_approve_invoice" class="button_1">Approve</div>
			<div style="display:none" class="loader approve_invoice_ajax_loader"></div>		
		</form>
	</div>

<?php }else{  ?>
	<h2>No Current Invoice Available.</h2>
<?php } ?>
<!-- COnfirm dialog box on admin -->
<div style="display:none;" class="confirm_approval_invoice_by_admin" id="confirm_approval_invoice_by_admin" title="Approve Invoice">
	<form class="delete_action_ajax" id="delete_project">
		<p class="label">
			Are you sure you want to Approve this Invoice?<br />
		</p>	
		<div id="confirmed_approve_invoice_by_admin" class="button_1">Approve</div>
		<div style="display:none" class="loader approve_invoice_by_admin_ajax_loader"></div>		
	</form>
</div>
<script type="text/javascript">
	jQuery(document).ready(function(){

		//Next Button for invoice
		jQuery(document).on('click', '#invoice-nav-right-btn', function(){
			var month = jQuery('#invoice-month').val();
			var year = jQuery('#invoice-year').val();
			var person_id = jQuery('#invoice_person_id').val();

			jQuery('#add_row_invoice').hide();
			jQuery('#approve_invoice').hide();
			jQuery('#submit-comment').hide();
			
			jQuery('#invoice-table tbody tr').fadeOut(2000);

			jQuery('#invoice-table tbody').html('<tr><td colspan="5">Loading</td></tr>');
			
			var data = {
				'month' : month,
				'year'	: year,
				'person_id' : person_id
			}

			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_info' : data,
						'type' : 'next_invoice_record',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);
					// console.log(parsed);
					jQuery('#invoice_date').html(parsed.invoice_info.date);

					jQuery('#invoice_id').val(parsed.invoice_info.id);
			
					var current_date = parsed.invoice_info.date;
					var current_date_array = new Array();
					current_date_array = current_date.split('-');

					jQuery('#invoice-month').val(current_date_array[0]);
					jQuery('#invoice-year').val(current_date_array[1]);

					if(parsed.end_next == 0){
						jQuery("#invoice-nav-right-btn").attr('disabled','disabled').addClass('hide');
						jQuery("#invoice-nav-left-btn").removeAttr('disabled').removeClass('hide');
					}else{
						jQuery("#invoice-nav-left-btn").removeAttr('disabled').removeClass('hide');
					}
					var rows_string = "";
				
					jQuery.each(parsed.invoice_info.clients_invoices_table, function(key,valueObj){
						rows_string += "<tr>";
						rows_string += "<td></td><td class='clientname'>"+valueObj.clientname+"</td><td><span class='total_hours_edit'>"+valueObj.total_hours+"</span></td><td>"+valueObj.price+"</td><td>"+valueObj.total+"</td>";
						rows_string += "</tr>";
					});
					console.log(parsed.person_approve);
					if(parsed.person_approve == 1){
						jQuery('#add_row_invoice').hide();
						jQuery('#approve_invoice').hide();
					}else{
						jQuery('#add_row_invoice').show();
						jQuery('#approve_invoice').show();
					}

					jQuery('#invoice-table tbody').html(rows_string);
					jQuery('#bottom_invoice_total_hours').text(parsed.invoice_info.total_hours);
					jQuery('#bottom_person_invoice_status').text(parsed.invoice_info.status);
					jQuery('#bottom_person_total_salary').text(parsed.invoice_info.salary);
					jQuery('#bottom_person_total_no_work_hours').text(parsed.invoice_info.non_working_hrs);
					jQuery('.invoices-comments').html(parsed.invoice_info.comments);

					jQuery('#submit-comment').show();
				},
				error: function (data) {
					alert('error');
				}				
			});		

		});


		//Previous Button for invoice
		jQuery(document).on('click', '#invoice-nav-left-btn', function(){
			var month = jQuery('#invoice-month').val();
			var year = jQuery('#invoice-year').val();
			var person_id = jQuery('#invoice_person_id').val();

			jQuery('#add_row_invoice').hide();
			jQuery('#approve_invoice').hide();
			jQuery('#submit-comment').hide();

			jQuery('#invoice-table tbody tr').fadeOut(2000);

			jQuery('#invoice-table tbody').html('<tr><td colspan="5">Loading</td></tr>');
			
			var data = {
				'month' : month,
				'year'	: year,
				'person_id' : person_id
			}

			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_info' : data,
						'type' : 'previous_invoice_record',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);
					jQuery('#invoice_date').html(parsed.invoice_info.date);

					jQuery('#invoice_id').val(parsed.invoice_info.id);
			
					var current_date = parsed.invoice_info.date;
					var current_date_array = new Array();
					current_date_array = current_date.split('-');

					jQuery('#invoice-month').val(current_date_array[0]);
					jQuery('#invoice-year').val(current_date_array[1]);

					if(parsed.end_previous == 0){
						jQuery("#invoice-nav-left-btn").attr('disabled','disabled').addClass('hide');
						jQuery("#invoice-nav-right-btn").removeAttr('disabled').removeClass('hide');
					}else{
						jQuery("#invoice-nav-right-btn").removeAttr('disabled').removeClass('hide');
					}
					var rows_string = "";

					jQuery.each(parsed.invoice_info.clients_invoices_table, function(key,valueObj){
						rows_string += "<tr>";
						rows_string += "<td></td><td class='clientname'>"+valueObj.clientname+"</td><td><span class='total_hours_edit'>"+valueObj.total_hours+"</span></td><td>"+valueObj.price+"</td><td>"+valueObj.total+"</td>";
						rows_string += "</tr>";
					});
					jQuery('#invoice-table tbody').html(rows_string);
					jQuery('#bottom_invoice_total_hours').text(parsed.invoice_info.total_hours);
					jQuery('#bottom_person_invoice_status').text(parsed.invoice_info.status);
					jQuery('#bottom_person_total_salary').text(parsed.invoice_info.salary);
					jQuery('#bottom_person_total_no_work_hours').text(parsed.invoice_info.non_working_hrs);
					jQuery('.invoices-comments').html(parsed.invoice_info.comments);

					// jQuery('#add_row_invoice').show();
					// jQuery('#approve_invoice').show();
					console.log(parsed.person_approve);
					jQuery('#submit-comment').show();

					if(parsed.person_approve == 1){
						jQuery('#add_row_invoice').hide();
						jQuery('#approve_invoice').hide();
					}else{
						jQuery('#add_row_invoice').show();
						jQuery('#approve_invoice').show();

					}
					jQuery('#submit-comment').show();
				},
				error: function (data) {
					alert('error');
				}				
			});		

		});
		//Add new row Entry
		jQuery(document).on('click', '#add_row_invoice', function(){
			jQuery("#invoice-table tbody tr:last").after("<tr id='new_entry_invoice_row'><td></td><td class='clientname'><input name='invoice_new_row_entry' class='invoice_new_row_entry'></td><td class='invoice_new_hours'><input name='invoice_new_row_entry_hours' class='invoice_new_row_entry_hours'></td><td class='invoice_new_price'><input name='invoice_new_row_entry_price' class='invoice_new_row_entry_price'></td><td class='invoice_new_total'><input name='invoice_new_row_entry_total' class='invoice_new_row_entry_total'></td></tr>"); 

			jQuery(this).text('Save').unbind().attr('id', 'save_new_invoice_row');
			jQuery("#remove_add_row_invoice").show();
		});

		//cancel new row entry
		jQuery(document).on('click', '#remove_add_row_invoice', function(){
			jQuery(this).hide();
			jQuery("#save_new_invoice_row").text('Add').unbind().attr('id', 'add_row_invoice');
			jQuery("#new_entry_invoice_row").remove();
		});

		//Save the new row entry
		jQuery(document).on('click', '#save_new_invoice_row', function(){
			jQuery(".invoice_add_new_entry_loader").css('display', 'inline-block');
			var current_row = jQuery("#new_entry_invoice_row");
			var new_entry_name = current_row.find(".invoice_new_row_entry").val();
			var invoice_new_row_entry_hours = current_row.find(".invoice_new_row_entry_hours").val();
			var invoice_new_row_entry_price = current_row.find(".invoice_new_row_entry_price").val();
			var invoice_new_row_entry_total = current_row.find(".invoice_new_row_entry_total").val();
			var invoice_id = jQuery("#invoice_id").val();
			var logged_in_id = jQuery("#invoice_person_id").val();

			var data = {
				'new_entry_name' : new_entry_name,
				'invoice_new_row_entry_hours' : invoice_new_row_entry_hours,
				'invoice_new_row_entry_price' : invoice_new_row_entry_price,
				'invoice_new_row_entry_total' : invoice_new_row_entry_total,
				'invoice_id' : invoice_id,
				'logged_in_id' : logged_in_id

			}

			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_info' : data,
						'type' : 'edit_invoice_data_table',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);

					if(parsed.editing_invoice_table_status == 'successfully_editing_invoice_table'){

						console.log(parsed);
						jQuery(".invoice_add_new_entry_loader").hide();
						var current_row = jQuery("#new_entry_invoice_row");
						current_row.find(".clientname").text(parsed.clientname);
						current_row.find(".invoice_new_hours").text(invoice_new_row_entry_hours)
						current_row.find(".invoice_new_price").text(invoice_new_row_entry_price)
						current_row.find(".invoice_new_total").text(invoice_new_row_entry_total)

						jQuery('#bottom_invoice_total_hours').text(parsed.total_hours);
						jQuery('#bottom_person_total_salary').text(parsed.total_salary);



						current_row.unbind().removeAttr('id');

						jQuery("#save_new_invoice_row").text('Add').unbind().attr('id', 'add_row_invoice');
						jQuery("#remove_add_row_invoice").hide();
						jQuery(".invoice_add_new_entry_loader").css('display', 'none');
					}

				},
				error: function (data) {
					alert('error');
				}				
			});		


		});

		jQuery('#confirmed_approve_invoice_by_admin').click(function(){
			jQuery(".approve_invoice_by_admin_ajax_loader").show();
			var invoice_id = jQuery('#invoice_id').val();

			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_id' : invoice_id,
						'type' : 'approve_invoice_by_admin',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);
					jQuery('#bottom_person_invoice_status').text(parsed.invoice_approve_by_admin_status);
					jQuery(".confirm_approval_invoice_by_admin").dialog("close");
					jQuery('#approve_invoice_by_admin').hide();
					jQuery(".approve_invoice_by_admin_ajax_loader").hide();
				},
				error: function (data) {
					alert('error');
				}				
			});					
			
		});

		//confirmed invoice by Person
		jQuery('#confirmed_approve_invoice').click(function(){
			jQuery('.approve_invoice_ajax_loader').show();

			var invoice_id = jQuery('#invoice_id').val();
			var invoice_person_id = jQuery('#invoice_person_id').val();
			var data = {
				'invoice_id' : invoice_id,
				'invoice_person_id' : invoice_person_id
			}

			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_info' : data,
						'type' : 'approve_invoice',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);
					console.log(parsed);
					jQuery('#bottom_person_invoice_status').text(parsed.invoice_status.toUpperCase());
					jQuery(".confirm_approval_invoice").dialog("close");
					jQuery("#approve_invoice").hide();
					jQuery("#add_row_invoice").hide();
				},
				error: function (data) {
					alert('error');
				}				
			});			
		});


		//Confirm Invoice
		jQuery( ".confirm_approval_invoice" ).dialog({
			autoOpen: false,
			height: 170,
			width: 350,
			modal: true,
			close: function() {
			}
		});		
		jQuery("#approve_invoice").click(function(){
			jQuery(".confirm_approval_invoice").dialog("open");
		});


		//Confirm Invoice
		jQuery( ".confirm_approval_invoice_by_admin" ).dialog({
			autoOpen: false,
			height: 170,
			width: 350,
			modal: true,
			close: function() {
			}
		});		
		jQuery("#approve_invoice_by_admin").click(function(){
			jQuery(".confirm_approval_invoice_by_admin").dialog("open");
		});


		//Submit a comment
		jQuery('#submit-comment').on('click', function(){
			jQuery('.invoice-comment-loader').show();
			var invoice_id = jQuery('#invoice_id').val();
			var comment = jQuery('#invoice-comment-form form textarea').val();
			var person_name = jQuery('#logged-in-person-name').val();

			if(comment == ''){
				jQuery('.invoice-comment-loader').hide();
				return false;
			}

			var data = {
				'person_name' : person_name,
				'comment' : comment,
				'invoice_id' : invoice_id,
				'logged_user_id' : <?php echo $current_user->ID; ?>
			}

			jQuery.ajax({				
				type: "POST",
				url: '<?php bloginfo("template_directory"); ?>/custom_ajax-functions.php',
				data: {
						'data_info' : data,
						'type' : 'submit_comments_invoice',
				},
				success: function (data) {
					var parsed = jQuery.parseJSON(data);
					jQuery('.no-comments').remove();
					jQuery('.invoice-comment-loader').hide();
					jQuery('#invoice-comment-section .invoices-comments-wrapper .invoices-comments').append('<li><div class="person-profile">'+parsed.person_name+':</div><div class="comment-date">'+parsed.datetime+'</div><div class="person-comment">'+parsed.comment+'</div></li>').slideDown();
					jQuery('#invoice-comment-form form textarea').val('');

				},
				error: function (data) {
					alert('error');
				}				
			});

		});


		//SHow input text for udpating client total hours
		// jQuery(document).on('dblclick', '.total_hours_edit', function(){
		// 	var hours = jQuery(this).text();
		// 	jQuery(this).html('<input type="text" class="update_client_hours" name="edit_hours" value="'+hours+'"><div class="update_client_total_hours" id=""></div><div class="invoice-row-update-loader" id="" style="display: none;"></div>');
		// 	jQuery(this).find('.update_client_hours').focus();
		// });

		//Updating the client total hours
		jQuery(document).on('click', '.update_client_total_hours', function(){

			var current_row = jQuery(this);
			current_row.closest('tr').find('.invoice-row-update-loader').show();
			var clientname = current_row.hide().closest('tr').children('.clientname').text();
			var update_hours = current_row.closest('.total_hours_edit').children('.update_client_hours').val();

			var reg = "/^[1-9]\d*(\.\d+)?$/";

			// if(reg.test(update_hours)){
			// 	current_row.prev().removeClass('invalid_input');
			// }else{
			// 	current_row.show().next().hide();
			// 	current_row.prev().addClass('invalid_input')
			// 	return false;				
			// }
			if(jQuery.isNumeric(update_hours)){
				current_row.prev().removeClass('invalid_input');
			}else{
				current_row.show().next().hide();
				current_row.prev().addClass('invalid_input')
				return false;
			}

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
					var total_hours = 0;
					current_row.closest('tr').find('.total_hours_edit').text(parsed.update_hours);
					jQuery('.total_hours_edit').each(function(){
						total_hours += parseFloat(jQuery(this).text());
					});
					console.log(total_hours);
					jQuery('.invoice_total_hours').text(total_hours);
				},
				error: function (data) {
					alert('error');
				}				
			});

		});
	});
</script>
<?php get_footer(); ?>