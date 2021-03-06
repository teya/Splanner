<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;
?>
<li <?php post_class(); ?>>

	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<a href="<?php the_permalink(); ?>" class="product-images">

		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );
		?>

	</a>

	<div class="product-details">

		<div class="product-details-container">

			<h3 class="product-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

			<div class="clearfix">

				<?php
					/**
					 * woocommerce_after_shop_loop_item_title hook
					 *
					 * @hooked woocommerce_template_loop_price - 10
					 */
					do_action( 'woocommerce_after_shop_loop_item_title' );
				?>

			</div>

		</div>

	</div>

	<div class="product-buttons">

		<div class="product-buttons-container clearfix">

			<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>

			<a href="<?php the_permalink(); ?>" class="show_details_button"><?php _e('Show details', 'Avada'); ?></a>

		</div>

	</div>

</li>