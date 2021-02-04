<?php
/**
 * WCFMmp Shipping Gateway for shipping by Distance
 *
 * Plugin Shipping Gateway
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/includes
 * @version   1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}

class WCFMmp_Shipping_By_Distance extends WC_Shipping_Method {
  /**
  * Constructor for your shipping class
  *
  * @access public
  *
  * @return void
  */
  public function __construct() {
    $this->id                 = 'wcfmmp_product_shipping_by_distance';
    $this->method_title       = __( 'Marketplace Shipping by Distance', 'wc-multivendor-marketplace' );
    $this->method_description = __( 'Enable vendors to set marketplace shipping by distance range', 'wc-multivendor-marketplace' );

    $this->enabled      = $this->get_option( 'enabled' );
    $this->title        = $this->get_option( 'title' );
    $this->tax_status   = $this->get_option( 'tax_status' );
    
    if( !$this->title ) $this->title = __( 'Shipping Cost', 'wc-multivendor-marketplace' );

    $this->init();
  }


  /**
  * Init your settings
  *
  * @access public
  * @return void
  */
  function init() {
     // Load the settings API
     $this->init_form_fields();
     $this->init_settings();

     // Save settings in admin if you have any defined
     add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
  }
  
  public function calculate_shipping( $package = array() ) {
  	global $wcfmmp_radius_lat, $wcfmmp_radius_lng;
  	
  	if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) ) return; 
		
		$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return;
		
		$radius_unit   = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';
  	
    $amount = 0.0;

    if ( ! $this->is_method_enabled() ) {
       return;
    }
    $vendor_id = isset($package['vendor_id']) ? $package['vendor_id'] : '';
    if ( !self::is_shipping_enabled_for_seller( $vendor_id ) ) {
      return;
    }
    
    $products = $package['contents'];
    $wcfmmp_user_location     = isset( $package['wcfmmp_user_location'] ) ? $package['wcfmmp_user_location'] : '';
    $wcfmmp_user_location_lat = isset( $package['wcfmmp_user_location_lat'] ) ? $package['wcfmmp_user_location_lat'] : '';
    $wcfmmp_user_location_lng = isset( $package['wcfmmp_user_location_lng'] ) ? $package['wcfmmp_user_location_lng'] : '';
    
    if( !$wcfmmp_user_location ) {
    	return;
    }
    
    $wcfmmp_radius_lat = $wcfmmp_user_location_lat;
    $wcfmmp_radius_lng = $wcfmmp_user_location_lng;
    
    $distance = wcfmmp_get_user_vendor_distance( $vendor_id );
    
    if( !$distance ) {
    	return;
    }
    
    //wcfm_log( $wcfmmp_radius_lat . "::" . $wcfmmp_radius_lng . "::" . json_encode( $distance ) );
    
    $wcfmmp_shipping_by_distance = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_distance', true );
				 
	  $wcfmmp_free_shipping_amount = isset($wcfmmp_shipping_by_distance['_free_shipping_amount']) ? $wcfmmp_shipping_by_distance['_free_shipping_amount'] : '';
	  $wcfmmp_free_shipping_amount = apply_filters( 'wcfmmp_free_shipping_minimum_order_amount', $wcfmmp_free_shipping_amount, $vendor_id );
    
    $wcfmmp_shipping_by_distance_rates = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_distance_rates', true );
    
    $max_distance = isset($wcfmmp_shipping_by_distance['_max_distance']) ? $wcfmmp_shipping_by_distance['_max_distance'] : '';
    
    if( $max_distance && ( $distance > $max_distance ) ) {
    	wc_add_notice( __( 'Some cart item(s) are not deliverable to your location.', 'wc-multivendor-marketplace' ), "error" );
    	return;
    }
    
    $default_cost = isset($wcfmmp_shipping_by_distance['_default_cost']) ? $wcfmmp_shipping_by_distance['_default_cost'] : 0;
    
     if ( $products ) {
				$amount = $this->calculate_per_seller( $products, $distance, $default_cost, $wcfmmp_shipping_by_distance_rates, $wcfmmp_free_shipping_amount );
	
			 $tax_rate  = ( $this->tax_status == 'none' ) ? false : '';
			 $tax_rate  = apply_filters( 'wcfmmp_is_apply_tax_on_shipping_rates', $tax_rate );
			 
			 if( !$amount ) {
				 $this->title = __('Free Shipping', 'wc-multivendor-marketplace');
			 }
	
			 $rate = array(
					 'id'    => $this->id . ':1',
					 'label' => $this->title, // . ' (' . __( 'Distance', 'wc-multivendor-marketplace') . ' ' . $distance . $radius_unit . ')',
					 'cost'  => $amount,
					 'taxes' => $tax_rate
			 );
			 
			 // Register the rate
			 $this->add_rate( $rate );
			 
			 // Local Pickup Method Check
			 $enable_local_pickup = isset($wcfmmp_shipping_by_distance['_enable_local_pickup']) ? 'yes' : '';
			 $local_pickup_cost = isset($wcfmmp_shipping_by_distance['_local_pickup_cost']) ? $wcfmmp_shipping_by_distance['_local_pickup_cost'] : '';
			 if( $enable_local_pickup ) {
			 	 $address = wcfm_get_vendor_store_address_by_vendor( $vendor_id );
			 	 $rate = array(
						 'id'    => 'local_pickup:1',
						 'label' => apply_filters( 'wcfmmp_local_pickup_shipping_option_label', __('Pickup from Store', 'wc-multivendor-marketplace') . ' ('.$address.')', $vendor_id ),
						 'cost'  => $local_pickup_cost,
						 'taxes' => $tax_rate
				 );
		
				 // Register the rate
				 $this->add_rate( $rate );
			 }
			 
			 // Free Shipping Method Check
			 if( $amount ) {
			 	 $amount = $this->calculate_per_seller( $products, $distance, $default_cost, $wcfmmp_shipping_by_distance_rates, $wcfmmp_free_shipping_amount, true );
			 	 
			 	 if( !$amount ) {
			 	 	 $rate = array(
							 'id'    => 'free_shipping:1',
							 'label' => __('Free Shipping', 'wc-multivendor-marketplace'),
							 'cost'  => $amount,
							 'taxes' => $tax_rate
					 );
			
					 // Register the rate
					 $this->add_rate( $rate );
			 	 }
			 }
		 }
  }
  
  /**
  * Checking is gateway enabled or not
  *
  * @return boolean [description]
  */
  public function is_method_enabled() {
     return $this->enabled == 'yes';
  }

  /**
  * Initialise Gateway Settings Form Fields
  *
  * @access public
  * @return void
  */
  function init_form_fields() {

     $this->form_fields = array(
      'enabled' => array(
          'title'         => __( 'Enable/Disable', 'wc-multivendor-marketplace' ),
          'type'          => 'checkbox',
          'label'         => __( 'Enable Shipping', 'wc-multivendor-marketplace' ),
          'default'       => 'yes'
      ),
      'title' => array(
          'title'         => __( 'Method Title', 'wc-multivendor-marketplace' ),
          'type'          => 'text',
          'description'   => __( 'This controls the title which the user sees during checkout.', 'wc-multivendor-marketplace' ),
          'default'       => __( 'Shipping Cost', 'wc-multivendor-marketplace' ),
          'desc_tip'      => true,
      ),
      'tax_status' => array(
          'title'         => __( 'Tax Status', 'wc-multivendor-marketplace' ),
          'type'          => 'select',
          'default'       => 'taxable',
          'options'       => array(
              'taxable'   => __( 'Taxable', 'wc-multivendor-marketplace' ),
              'none'      => _x( 'None', 'Tax status', 'wc-multivendor-marketplace' )
          ),
      ),

     );
  }
  
  /**
  * Check if shipping for this product is enabled
  *
  * @param  integet  $product_id
  *
  * @return boolean
  */
  public static function is_shipping_enabled_for_seller( $vendor_id ) {
    global  $WCFMmp;
    $vendor_shipping_details = get_user_meta( $vendor_id, '_wcfmmp_shipping', true );
    if( !empty($vendor_shipping_details) ) {
      $enabled = $vendor_shipping_details['_wcfmmp_user_shipping_enable'];
      $type = !empty( $vendor_shipping_details['_wcfmmp_user_shipping_type'] ) ? $vendor_shipping_details['_wcfmmp_user_shipping_type'] : '';
      if ( ( !empty($enabled) && $enabled == 'yes' ) && ( !empty($type) ) && 'by_distance' === $type ) {
          return true;
      }
    }
    return false;
  }
  
  /**
  * Calculate shipping per seller
  *
  * @param  array $products
  * @param  array $destination
  *
  * @return float
  */
  public function calculate_per_seller( $products, $total_distance, $default_cost, $wcfmmp_shipping_by_distance_rates, $wcfmmp_free_shipping_amount = '', $is_consider_free_threshold = false ) {
    $amount = !empty( $default_cost ) ? $default_cost : 0;
    $price = array();
    
    $seller_products = array();
    //$total_distance = 0.0;

    foreach ( $products as $product ) {
			$vendor_id                     = get_post_field( 'post_author', $product['product_id'] );
			$seller_products[$vendor_id][] = $product;
    }
    
    if ( $seller_products ) {
      foreach ( $seller_products as $vendor_id => $products ) {

        if ( !self::is_shipping_enabled_for_seller( $vendor_id ) ) {
          continue;
        }

        $products_total_cost = 0;
        foreach ( $products as $product ) {
					$line_subtotal      = (float) $product['line_subtotal'];
					$line_total         = (float) $product['line_total'];
					$discount_total     = $line_subtotal - $line_total;
					$line_subtotal_tax  = (float) $product['line_subtotal_tax'];
					$line_total_tax     = (float) $product['line_tax'];
					$discount_tax_total = $line_subtotal_tax - $line_total_tax;
					
					if( apply_filters( 'wcfmmp_free_shipping_threshold_consider_tax', true ) ) {
						$total = $line_subtotal + $line_subtotal_tax;
					} else {
						$total = $line_subtotal;
					}
					
					if ( WC()->cart->display_prices_including_tax() ) {
					 $products_total_cost += round( $total - ( $discount_total + $discount_tax_total ), wc_get_price_decimals() );
					} else {
					 $products_total_cost += round( $total - $discount_total, wc_get_price_decimals() );
					}
        }
        
        if( $is_consider_free_threshold && $wcfmmp_free_shipping_amount && ( $wcfmmp_free_shipping_amount <= $products_total_cost ) ) {
				 return apply_filters( 'wcfmmp_shipping_distance_calculate_amount', 0, $products, $total_distance, $default_cost, $wcfmmp_shipping_by_distance_rates );
			 }
      }
    }
    
    $matched_rule_distance = 0;
    $selected_rule['price'] = 0;
    
		foreach ( $wcfmmp_shipping_by_distance_rates as $each_distance_rule ) {
			$rule_distance = $each_distance_rule['wcfmmp_distance_unit'];
			$rule = $each_distance_rule['wcfmmp_distance_rule'];
			$rule_price = isset( $each_distance_rule['wcfmmp_distance_price'] ) ? $each_distance_rule['wcfmmp_distance_price'] : 0;
			
			if( ( $rule == 'up_to' ) && ( (float)$total_distance <= (float)$rule_distance ) && ( !$matched_rule_distance || ( (float)$rule_distance <= (float)$matched_rule_distance ) ) ) {
				$matched_rule_distance = $rule_distance;
				$selected_rule = array( 'price' => $rule_price );
			} elseif( ( $rule == 'more_than' ) && ( (float)$total_distance > (float)$rule_distance ) && ( !$matched_rule_distance || ( (float)$rule_distance >= (float)$matched_rule_distance ) ) ) {
				$matched_rule_distance = $rule_distance;
				$selected_rule = array( 'price' => $rule_price );
			}
		}
    
		if( !empty( $selected_rule['price'] ) ) {
			$amount += $selected_rule['price'];
		} 
		
    return apply_filters( 'wcfmmp_shipping_distance_calculate_amount', $amount, $products, $total_distance, $default_cost, $wcfmmp_shipping_by_distance_rates );
  }
  
}