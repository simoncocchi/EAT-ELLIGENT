<?php 
global $wpdb, $WCFM, $WCFMmp; 

$parent_product_id = 0;
$multi_selling = get_post_meta( $product_id, '_has_multi_selling', true );
$multi_parent  = get_post_meta( $product_id, '_is_multi_parent', true );

if( $multi_parent ) {
	$parent_product_id = absint($multi_parent);
} elseif( $multi_selling ) {
	$parent_product_id = absint($multi_selling);
} elseif( !$multi_parent && !$multi_selling ) {
  return;
}

$sql = "SELECT GROUP_CONCAT(product_id) as products, parent_product_id FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` WHERE `parent_product_id` = $parent_product_id";
$more_offers = $wpdb->get_results( $sql );

$product_ids = '';
if( !empty( $more_offers ) ) {
	foreach ($more_offers as $key => $value) {
		$product_ids = $value->products . ',' . $value->parent_product_id;
		$sql = "SELECT product_id, stock_status, stock_quantity FROM {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup WHERE product_id IN ({$product_ids})";
	}
}

if( $product_ids && $sorting ) {
	switch( $sorting ) {
		case 'price':
			$sql .= " ORDER BY wc_product_meta_lookup.min_price ASC";
			break;
			
		case 'price-desc':
			$sql .= " ORDER BY wc_product_meta_lookup.max_price DESC";
			break;
			
		case 'popularity':
			$sql .= " ORDER BY wc_product_meta_lookup.average_rating DESC";
			break;
			
		case 'date':
			$sql .= " ORDER BY wc_product_meta_lookup.product_id DESC";
			break;
			
		default:
			$sql .= " ORDER BY wc_product_meta_lookup.product_id ASC";
			break;
	}
}

//echo $sql;
$more_offers = $wpdb->get_results( $sql );

$more_offers = apply_filters( 'wcfmmp_more_offers_products', $more_offers, $parent_product_id );

$button_style     = '';
$hover_color      = '';
$hover_text_color = '#ffffff';
$wcfm_options = $WCFM->wcfm_options;
$wcfm_store_color_settings = get_option( 'wcfm_store_color_settings', array() );
if( !empty( $wcfm_store_color_settings ) ) {
	if( isset( $wcfm_store_color_settings['button_bg'] ) ) { $button_style .= 'background: ' . $wcfm_store_color_settings['button_bg'] . ';border-bottom-color: ' . $wcfm_store_color_settings['button_bg'] . ';'; }
	if( isset( $wcfm_store_color_settings['button_text'] ) ) { $button_style .= 'color: ' . $wcfm_store_color_settings['button_text'] . ';'; }
} else {
	if( isset( $wcfm_options['wc_frontend_manager_button_background_color_settings'] ) ) { $button_style .= 'background: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';border-bottom-color: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';'; }
	if( isset( $wcfm_options['wc_frontend_manager_button_text_color_settings'] ) ) { $button_style .= 'color: ' . $wcfm_options['wc_frontend_manager_button_text_color_settings'] . ';'; }
}

?>

<div class="wcfmmp_product_mulvendor_table_container">	
	<?php do_action( 'wcfmmp_more_offers_content_head_before', $parent_product_id ); ?>

	<div class="wcfmmp_product_mulvendor_row wcfmmp_product_mulvendor_rowhead">
		<div class="wcfmmp_product_mulvendor_rowsub "><?php _e('Store', 'wc-multivendor-marketplace'); ?></div>
		<?php do_action( 'wcfmmp_more_offers_content_head_after_store', $parent_product_id ); ?>
		<div class="wcfmmp_product_mulvendor_rowsub"><?php _e('Price', 'wc-multivendor-marketplace'); ?></div>
		<?php do_action( 'wcfmmp_more_offers_content_head_after_price', $parent_product_id ); ?>
		<div class="wcfmmp_product_mulvendor_rowsub"><?php _e('Details', 'wc-multivendor-marketplace'); ?></div>
		<?php do_action( 'wcfmmp_more_offers_content_head_after_details', $parent_product_id ); ?>
		<div class="wcfm_clearfix"></div>
	</div>	
	
	<?php do_action( 'wcfmmp_more_offers_content_head_after', $parent_product_id ); ?>
	
	<?php
	foreach( $more_offers as $more_offer ) {
		$offer_product_id = absint($more_offer->product_id);
		if( $more_offer->stock_status == 'outofstock' ) continue;
		$post_status = get_post_status( $offer_product_id );
		if( $post_status != 'publish' ) continue;
		if( !apply_filters( 'wcfmmp_is_allow_product_for_more_offers', true, $offer_product_id ) ) continue;
		if( ( $product_id == $offer_product_id ) && apply_filters( 'wcfmmp_is_allow_skip_current_product_from_more_offers', true, $offer_product_id ) ) continue;
		$store_id         = wcfm_get_vendor_id_by_post( $offer_product_id );
		$WCFMmp->template->get_template( 'product_multivendor/wcfmmp-view-more-offer-single.php', array( 'offer_product_id' => $offer_product_id, 'store_id' => $store_id, 'button_style' => $button_style ) );
	}
	?>
</div>