<?php
/**
 * WCFMmp Shipping Gateway for shipping by country
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

class WCFMmp_Shipping_By_Weight extends WC_Shipping_Method {
  /**
  * Constructor for your shipping class
  *
  * @access public
  *
  * @return void
  */
  public function __construct() {
    $this->id                 = 'wcfmmp_product_shipping_by_weight';
    $this->method_title       = __( 'Marketplace Shipping by Weight', 'wc-multivendor-marketplace' );
    $this->method_description = __( 'Enable vendors to set marketplace shipping by weight range', 'wc-multivendor-marketplace' );

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
  	
  	if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) ) return; 
		
		$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return;
  	
    $products = $package['contents'];
    $destination_country = isset( $package['destination']['country'] ) ? $package['destination']['country'] : '';
    $destination_state = isset( $package['destination']['state'] ) ? $package['destination']['state'] : '';

    $amount = 0.0;

    if ( ! $this->is_method_enabled() ) {
       return;
    }
    $vendor_id = isset($package['vendor_id']) ? $package['vendor_id'] : '';
    if ( !self::is_shipping_enabled_for_seller( $vendor_id ) ) {
      return;
    }
    
    $wcfmmp_shipping_by_weight = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_weight', true );
				 
	  $wcfmmp_free_shipping_amount = isset($wcfmmp_shipping_by_weight['_free_shipping_amount']) ? $wcfmmp_shipping_by_weight['_free_shipping_amount'] : '';
	  $wcfmmp_free_shipping_amount = apply_filters( 'wcfmmp_free_shipping_minimum_order_amount', $wcfmmp_free_shipping_amount, $vendor_id );
    
    $wcfmmp_country_weight_rates = get_user_meta( $vendor_id, '_wcfmmp_country_weight_rates', true );
    
    $wcfmmp_country_weight_mode  = get_user_meta( $vendor_id, '_wcfmmp_country_weight_mode', true );
    if( !$wcfmmp_country_weight_mode ) $wcfmmp_country_weight_mode = array();
    $wcfmmp_country_weight_unit_cost  = get_user_meta( $vendor_id, '_wcfmmp_country_weight_unit_cost', true );
    if( !$wcfmmp_country_weight_unit_cost ) $wcfmmp_country_weight_unit_cost = array();
    $wcfmmp_country_weight_default_costs  = get_user_meta( $vendor_id, '_wcfmmp_country_weight_default_costs', true );
    if( !$wcfmmp_country_weight_default_costs ) $wcfmmp_country_weight_default_costs = array();
    
    //print_r($wcfmmp_country_weight_rates); die;
    
    if ( array_key_exists( $destination_country, $wcfmmp_country_weight_rates )  ) {
      $weight_array_for_country = $wcfmmp_country_weight_rates[ $destination_country ];
      $weight_mode_for_country = !empty( $wcfmmp_country_weight_mode[ $destination_country ] ) ? $wcfmmp_country_weight_mode[ $destination_country ] : 'by_rule';
      $unit_cost_for_country = !empty( $wcfmmp_country_weight_unit_cost[ $destination_country ] ) ? $wcfmmp_country_weight_unit_cost[ $destination_country ] : 0;
      $default_cost_for_country = !empty( $wcfmmp_country_weight_default_costs[ $destination_country ] ) ? $wcfmmp_country_weight_default_costs[ $destination_country ] : 0;
    } elseif( array_key_exists( 'everywhere', $wcfmmp_country_weight_rates ) ) {
      $weight_array_for_country = $wcfmmp_country_weight_rates[ 'everywhere' ];
      $weight_mode_for_country = !empty( $wcfmmp_country_weight_mode[ 'everywhere' ] ) ? $wcfmmp_country_weight_mode[ 'everywhere' ] : 'by_rule';
      $unit_cost_for_country = !empty( $wcfmmp_country_weight_unit_cost[ 'everywhere' ] ) ? $wcfmmp_country_weight_unit_cost[ 'everywhere' ] : 0;
      $default_cost_for_country = !empty( $wcfmmp_country_weight_default_costs[ 'everywhere' ] ) ? $wcfmmp_country_weight_default_costs[ 'everywhere' ] : 0;
    } else {
      return;
    }
    

     if ( $products ) {
				$amount = $this->calculate_per_seller( $products, $destination_country, $destination_state, $weight_array_for_country, $default_cost_for_country, $weight_mode_for_country, $unit_cost_for_country, $wcfmmp_free_shipping_amount );
	
			 $tax_rate  = ( $this->tax_status == 'none' ) ? false : '';
			 $tax_rate  = apply_filters( 'wcfmmp_is_apply_tax_on_shipping_rates', $tax_rate );
			 
			 if( !$amount ) {
				 $this->title = __('Free Shipping', 'wc-multivendor-marketplace');
			 }
	
			 $rate = array(
					 'id'    => $this->id . ':1',
					 'label' => $this->title,
					 'cost'  => $amount,
					 'taxes' => $tax_rate
			 );
			 
			 // Register the rate
			 $this->add_rate( $rate );
			 
			 // Local Pickup Method Check
			 $wcfmmp_shipping_by_weight = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_weight', true );
			 $enable_local_pickup = isset($wcfmmp_shipping_by_weight['_enable_local_pickup']) ? 'yes' : '';
			 $local_pickup_cost = isset($wcfmmp_shipping_by_weight['_local_pickup_cost']) ? $wcfmmp_shipping_by_weight['_local_pickup_cost'] : '';
			 if( $enable_local_pickup ) {
			 	 $address = wcfm_get_vendor_store_address_by_vendor( $vendor_id );
			 	 $rate = array(
						 'id'    => 'local_pickup:1',
						 'label' => apply_filters( 'wcfmmp_local_pickup_shipping_option_label', __('Pickup from Store', 'wc-multivendor-marketplace')  . ' ('.$address.')', $vendor_id ),
						 'cost'  => $local_pickup_cost,
						 'taxes' => $tax_rate
				 );
		
				 // Register the rate
				 $this->add_rate( $rate );
			 }
			 
			 // Free Shipping Method Check
			 if( $amount ) {
			 	 $amount = $this->calculate_per_seller( $products, $destination_country, $destination_state, $weight_array_for_country, $default_cost_for_country, $weight_mode_for_country, $unit_cost_for_country, $wcfmmp_free_shipping_amount, true );
			 	 
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
      if ( ( !empty($enabled) && $enabled == 'yes' ) && ( !empty($type) ) && 'by_weight' === $type ) {
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
  public function calculate_per_seller( $products, $destination_country, $destination_state, $weight_array_for_country, $default_cost_for_country, $weight_mode_for_country, $unit_cost_for_country, $wcfmmp_free_shipping_amount = '', $is_consider_free_threshold = false ) {
    $amount = $default_cost_for_country;
    $price = array();
    
    $seller_products = array();
    $total_weight = 0.0;

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
          if($product['data']->has_weight())
            $total_weight += ( $product['data']->get_weight() * $product['quantity'] );
          
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
				 return apply_filters( 'wcfmmp_shipping_weight_calculate_amount', 0, $price, $products, $destination_country, $destination_state, $weight_array_for_country, $default_cost_for_country, $total_weight, $weight_mode_for_country, $unit_cost_for_country );
			 }
      }
    }
    
    $matched_rule_weight = 0;
    $selected_rule['price'] = 0;
    
    if( $weight_mode_for_country == 'by_unit' ) {
    	$matched_rule_weight = $total_weight;
    	$selected_rule['price'] = (float)$total_weight * (float) $unit_cost_for_country;
    } else {
			foreach ( $weight_array_for_country as $each_weight_rule ) {
				$rule_weight = $each_weight_rule['wcfmmp_weight_unit'];
				$rule = $each_weight_rule['wcfmmp_weight_rule'];
				
				if( ( $rule == 'up_to' ) && ( (float)$total_weight <= (float)$rule_weight ) && ( !$matched_rule_weight || ( (float)$rule_weight <= (float)$matched_rule_weight ) ) ) {
					$matched_rule_weight = $rule_weight;
					$selected_rule = array( 'price' => $each_weight_rule['wcfmmp_weight_price'] );
				} elseif( ( $rule == 'more_than' ) && ( (float)$total_weight > (float)$rule_weight ) && ( !$matched_rule_weight || ( (float)$rule_weight >= (float)$matched_rule_weight ) ) ) {
					$matched_rule_weight = $rule_weight;
					$selected_rule = array( 'price' => $each_weight_rule['wcfmmp_weight_price'] );
				}
			}
		}
    
		if( !empty( $selected_rule['price'] ) ) {
			$amount = $selected_rule['price'];
		} 
		
    return apply_filters( 'wcfmmp_shipping_weight_calculate_amount', $amount, $price, $products, $destination_country, $destination_state, $weight_array_for_country, $default_cost_for_country, $total_weight, $weight_mode_for_country, $unit_cost_for_country );
  }
  
}