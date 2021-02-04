<?php

global $WCFM, $WCFMmp, $wpdb;

$wcfmmp_shipping          = get_user_meta( $vendor_id, '_wcfmmp_shipping', true );
$processing_times         = wcfmmp_get_shipping_processing_times();
$processing_time          = isset($wcfmmp_shipping['_wcfmmp_pt']) ? $wcfmmp_shipping['_wcfmmp_pt'] : '';
$processing_time          = get_post_meta( $product_id, '_wcfmmp_processing_time', true ) ? get_post_meta( $product_id, '_wcfmmp_processing_time', true ) : $processing_time;

if( isset( $wcfmmp_shipping['_wcfmmp_user_shipping_enable'] ) && $processing_time && isset( $processing_times[$processing_time] ) ) {
	echo '<div class="wcfm_clearfix"></div><div class="wcfmmp_shipment_processing_display">'. __( 'Item will be shipped in', 'wc-multivendor-marketplace' ) . ' ' . $processing_times[$processing_time] .'</div><div class="wcfm_clearfix"></div>';
}

if( !apply_filters( 'wcfm_is_allow_product_free_shipping_info', true ) ) return;

/*$type             = $wcfmmp_shipping['_wcfmmp_user_shipping_type'];
$is_free_shipping = false;
$min_amount       = 0;
foreach ( $shipping_methods as $key => $method ) {
	if ( 'free_shipping' == $method['id'] && 'yes' == $method['enabled'] ) {
		$is_free_shipping = true;
		$min_amount = (isset( $method['settings']['min_amount'] ) ) ? $method['settings']['min_amount'] : 0;
	}
}
if( ( !empty($type) && $type == 'by_zone' ) && $is_free_shipping && ( $min_amount > 0 ) ) {
	echo '<div class="wcfmmp_shipment_processing_display">'. sprintf( __ ('Free shipping available for shopping more than <b>%s%d</b>.', 'wc-multivendor-marketplace'), get_woocommerce_currency_symbol(), $min_amount ) .'</div>';
}*/

?>