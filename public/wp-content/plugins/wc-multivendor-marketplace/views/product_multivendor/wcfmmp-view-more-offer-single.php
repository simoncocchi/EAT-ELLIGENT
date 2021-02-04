<?php
/**
 * The Template for displaying product multivenor more offer single.
 *
 * @package WCfM Markeplace Views More Offer Single
 *
 * For edit coping this to yourtheme/wcfm/product_multivendor 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $wpdb;

$_product_post = get_post($offer_product_id);
if( $_product_post->post_status != 'publish' ) return;

$_product         = wc_get_product( $offer_product_id );

if( $store_id ) {
	$store_user       = wcfmmp_get_store( $store_id );
	$store_info       = $store_user->get_shop_info();
}
?>

<div class="wcfmmp_product_mulvendor_row wcfmmp_product_mulvendor_rowbody">		

  <?php do_action( 'wcfmmp_more_offers_single_line_before', $offer_product_id ); ?>
  
	<div class="wcfmmp_product_mulvendor_rowsub ">
		<div class="vendor_name">
			<?php 
			if( $store_id ) {
				if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
					echo $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( absint($store_id) );
				} else {
					echo $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($store_id) );
				}
			} else {
				echo apply_filters( 'wcfmmp_more_offers_admin_product_soldby_label', get_bloginfo( 'name' ), $offer_product_id );
			}
			?>
		</div>
		<?php
		if( $store_id ) {
			if( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) ) {
				echo '<div class="wcfmmp_store_info">';
				$WCFMmp->wcfmmp_reviews->show_star_rating( 0, $store_id );
				echo '</div>';
			}
			do_action('wcfmmp_sold_by_label_more_offers_after', $store_id, $offer_product_id );
		}
		?>
	</div>
	<?php do_action( 'wcfmmp_more_offers_single_line_after_store', $offer_product_id ); ?>
	<div class="wcfmmp_product_mulvendor_rowsub">
		<?php echo $_product->get_price_html(); ?>
	</div>
	<?php do_action( 'wcfmmp_more_offers_single_line_after_price', $offer_product_id ); ?>
	<div class="wcfmmp_product_mulvendor_rowsub">
		<?php if( $_product->get_type() == 'simple' ) { ?>
			<a href="<?php echo '?add-to-cart='.$offer_product_id; ?>" class="buttongap button wcfmmp_product_multivendor_action_button" style="<?php echo $button_style; ?>"><?php echo apply_filters( 'add_to_cart_text', __( 'Add to Cart', 'wc-multivendor-marketplace') ); ?></a>
		<?php } ?>
		<a href="<?php echo get_permalink($offer_product_id); ?>" class="buttongap button wcfmmp_product_multivendor_action_button" style="<?php echo $button_style; ?>"><?php echo __( 'Details', 'wc-multivendor-marketplace' ); ?></a>
	</div>
	<?php do_action( 'wcfmmp_more_offers_single_line_after_details', $offer_product_id ); ?>
	
	<?php do_action( 'wcfmmp_more_offers_single_line_after', $offer_product_id ); ?>
	
	<div class="wcfm_clearfix"></div>							
</div>