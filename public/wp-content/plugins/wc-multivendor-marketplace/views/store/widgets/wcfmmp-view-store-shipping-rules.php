<?php
/**
 * The Template for displaying store sidebar hours.
 *
 * @package WCfM Markeplace Views Store Sidebar Shipping Rules
 *
 * For edit coping this to yourtheme/wcfm/store/widgets
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;


if( !empty( $wcfmmp_shipping['_wcfmmp_user_shipping_type'] ) && $wcfmmp_shipping['_wcfmmp_user_shipping_type'] == 'by_zone') {
	$data_store = WC_Data_Store::load( 'shipping-zone' );
  $raw_zones  = $data_store->get_zones();
  
  foreach ( $raw_zones as $raw_zone ) {
		$zone             = new WC_Shipping_Zone( $raw_zone );
		$enabled_methods  = $zone->get_shipping_methods( true );
		$methods_id = wp_list_pluck( $enabled_methods, 'id' );
		
		if ( in_array( 'wcfmmp_product_shipping_by_zone', $methods_id ) ) {
			$shipping_methods = WCFMmp_Shipping_Zone::get_shipping_methods( $zone->get_id(), $store_id );
			
			if ( !empty( $shipping_methods ) ) {
				echo '<div style="margin-bottom:40px;"><div class="wcfmmp_shipment_rules_display"><span class="wcfmmp-store-shipping-rule" style="text-decoration:underline;">'. $zone->get_zone_name() . ' ' . __( 'Shipping Rules:' , 'wc-multivendor-marketplace') . '</span></div><br />'; 
				
				foreach ( $shipping_methods as $key => $method ) {
          $tax_rate = ( $method['settings']['tax_status'] == 'none' ) ? false : '';
          $has_costs     = false;

          if ( 'yes' != $method['enabled'] ) continue;

          if ( $method['id'] == 'flat_rate' ) {
					  $setting_cost = isset( $method['settings']['cost'] ) ? stripslashes_deep( $method['settings']['cost'] ) : '';
					  if( $setting_cost ) {
						  echo '<div class="wcfmmp_shipment_rules_display"><span class="wcfmmp-store-shipping-rule" style="color:#111;">'. $method['title'] . '</span>: ' . wc_price( $setting_cost ) .'</div>';
					  }
          } elseif( $method['id'] == 'free_shipping' ) {
          	$min_amount = ! empty( $method['settings']['min_amount'] ) ? $method['settings']['min_amount'] : 0;
          	if( $min_amount ) {
          		echo '<div class="wcfmmp_shipment_rules_display"><span class="wcfmmp-store-shipping-rule" style="color:#111;">'. $method['title'] . '</span>: ' . sprintf( __ ('Available for shopping more than <b>%s%d</b>.', 'wc-multivendor-marketplace'), get_woocommerce_currency_symbol(), $min_amount ) .'</div>';
          	}
          } elseif( $method['id'] == 'local_pickup' ) {
          	echo '<div class="wcfmmp_shipment_rules_display"><span class="wcfmmp-store-shipping-rule" style="color:#111;">'. $method['title'] . '</span>: ' . __ ('Available', 'wc-multivendor-marketplace');
          	$setting_cost = isset( $method['settings']['cost'] ) ? stripslashes_deep( $method['settings']['cost'] ) : '';
					  if( $setting_cost ) {
					  	echo ' (' . wc_price( $setting_cost ) . ')';
					  }
          	echo '</div>';
          }
        }
        echo '</div>';
      }
		}
	}
}

$processing_times         = wcfmmp_get_shipping_processing_times();
$processing_time          = isset($wcfmmp_shipping['_wcfmmp_pt']) ? $wcfmmp_shipping['_wcfmmp_pt'] : '';

if( $processing_time && isset( $processing_times[$processing_time] ) ) {
	echo '<br /><div class="wcfmmp_shipment_rules_display"><span class="wcfmmp-store-shipping-rule" style="color:#111;">'. __( 'Delivery Time', 'wc-multivendor-marketplace' ) . '</span>: ' . $processing_times[$processing_time] .'</div>';
}

?>