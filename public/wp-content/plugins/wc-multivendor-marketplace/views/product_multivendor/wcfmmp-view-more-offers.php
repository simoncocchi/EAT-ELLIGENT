<?php
/**
 * The Template for displaying product multivenor more offers.
 *
 * @package WCfM Markeplace Views More Offers
 *
 * For edit coping this to yourtheme/wcfm/product_multivendor 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $product, $wpdb;

if( !method_exists( $product, 'get_id' ) ) return;

$product_id = $product->get_id();

$parent_product_id = 0;
$multi_selling = get_post_meta( $product_id, '_has_multi_selling', true );
$multi_parent  = get_post_meta( $product_id, '_is_multi_parent', true );

if( $multi_parent ) {
	$parent_product_id = absint($multi_parent);
} elseif( $multi_selling ) {
	$parent_product_id = absint($multi_selling);
} elseif( !$multi_parent && !$multi_selling ) {
  _e( 'No more offers for this product!', 'wc-multivendor-marketplace' );
  return;
}


$more_offers = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` WHERE `parent_product_id` = $parent_product_id" );

$more_offers = apply_filters( 'wcfmmp_more_offers_products', $more_offers, $parent_product_id );

$button_style     = '';
$hover_color      = '';
$hover_text_color = '#ffffff';
$wcfm_options = $WCFM->wcfm_options;
$wcfm_store_color_settings = get_option( 'wcfm_store_color_settings', array() );
if( !empty( $wcfm_store_color_settings ) ) {
	if( isset( $wcfm_store_color_settings['button_bg'] ) ) { $button_style .= 'background: ' . $wcfm_store_color_settings['button_bg'] . ';border-bottom-color: ' . $wcfm_store_color_settings['button_bg'] . ';'; }
	if( isset( $wcfm_store_color_settings['button_text'] ) ) { $button_style .= 'color: ' . $wcfm_store_color_settings['button_text'] . ';'; }
	if( isset( $wcfm_store_color_settings['button_active_bg'] ) ) { $hover_color = $wcfm_store_color_settings['button_active_bg']; }
	if( isset( $wcfm_store_color_settings['button_active_text'] ) ) { $hover_text_color = $wcfm_store_color_settings['button_active_text']; }
} else {
	if( isset( $wcfm_options['wc_frontend_manager_button_background_color_settings'] ) ) { $button_style .= 'background: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';border-bottom-color: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';'; }
	if( isset( $wcfm_options['wc_frontend_manager_button_text_color_settings'] ) ) { $button_style .= 'color: ' . $wcfm_options['wc_frontend_manager_button_text_color_settings'] . ';'; }
	if( isset( $wcfm_options['wc_frontend_manager_base_highlight_color_settings'] ) ) { $hover_color = $wcfm_options['wc_frontend_manager_base_highlight_color_settings']; }
}

if( !empty( $more_offers ) ) {
	do_action( 'wcfmmp_more_offers_content_before', $parent_product_id );
	?>
	<div class="wcfmmp_product_mulvendor_container">		
	
	  <?php if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?>
	  	<?php do_action( 'wcfmmp_more_offers_sorting_form_before', $parent_product_id ); ?>
			<form class="woocommerce-spmv-ordering" method="POST">
				<select name="spmv_orderby" class="orderby" aria-label="Shop order" data-product_id="<?php echo $product_id; ?>">
					<option value="menu_order"><?php _e( 'Default sorting', 'woocommerce' ); ?></option>
					<option value="popularity"><?php _e( 'Sort by popularity', 'woocommerce' ); ?></option>
					<option value="date"><?php _e( 'Sort by latest', 'woocommerce' ); ?></option>
					<option value="price"><?php _e( 'Sort by price: low to high', 'woocommerce' ); ?></option>
					<option value="price-desc"><?php _e( 'Sort by price: high to low', 'woocommerce' ); ?></option>
				</select>
			</form>
			<?php do_action( 'wcfmmp_more_offers_sorting_form_after', $parent_product_id ); ?>
			<div class="wcfm-clearfix"></div>
	  <?php } ?>
	
	  <?php $WCFMmp->template->get_template( 'product_multivendor/wcfmmp-view-more-offers-loop.php', array( 'product_id' => $product_id, 'sorting' => 'price' ) ); ?>
		
		<?php if( $hover_color ) { ?>
			<style>a.wcfmmp_product_multivendor_action_button:hover{background: <?php echo $hover_color; ?> !important;background-color: <?php echo $hover_color; ?> !important;border-bottom-color: <?php echo $hover_color; ?> !important;color: <?php echo $hover_text_color; ?> !important;}</style>
		<?php } ?>
	</div>
	<?php
	do_action( 'wcfmmp_more_offers_content_after', $parent_product_id );
} else {
	_e( 'No more offers for this product!', 'wc-multivendor-marketplace' );
}