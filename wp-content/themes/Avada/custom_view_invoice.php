<?php 
/* Template Name: View Invoice */ 
	global $wpdb;
	$current_user = wp_get_current_user();
	$invoice_tablename = $wpdb->prefix.'custom_invoice_table';
	$invoice_id = $_GET['id'];
	$invoide_info = $wpdb->get_row('SELECT * FROM '.$invoice_tablename.' WHERE id = '.$invoice_id);
	$client_list_table = unserialize($invoide_info->clients_invoices_table);
?>
<?php get_header(); ?>

<?php  
	echo '<pre>';
	print_r($client_list_table);
	echo '</pre>';	
?>

<?php get_footer(); ?>