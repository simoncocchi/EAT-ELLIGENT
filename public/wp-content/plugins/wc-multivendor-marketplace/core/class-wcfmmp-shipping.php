<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Shipping
 *
 * @author    WC Lovers
 * @package   wcfmmp/core
 * @version   1.1.0
 */
 
class WCFMmp_Shipping {
  
  public function __construct() {
    global $WCFM, $WCFMmp;
    
    //Load Vendor Shipping Settings
    add_action( 'wcfm_marketplace_shipping', array( &$this, 'wcfmmp_load_shipping_view' ) );
    
    //Save Vendor Shipping Settings
    add_action('wcfm_vendor_settings_before_update' , array( &$this,'wcfmmp_vendor_shipping_settings_update' ), 10, 2 );
    add_action('wcfm_vendor_shipping_settings_update' , array( &$this,'wcfmmp_vendor_shipping_settings_update' ), 10, 2 );
     
    // Single Product page Shipping Info
		add_action( 'woocommerce_single_product_summary',	array( &$this, 'wcfmmp_shipping_info' ), 32 );
    
    // split woocommerce shipping packages
    add_filter('woocommerce_cart_shipping_packages', array(&$this, 'wcfmmp_split_shipping_packages'), 0 );

    // Add extra vendor_id to shipping packages
    add_action('woocommerce_checkout_create_order_shipping_item', array(&$this, 'wcfmmp_add_meta_date_in_shipping_package'), 10, 3);

    // Rename woocommerce shipping packages
    add_filter('woocommerce_shipping_package_name', array(&$this, 'wcfmmp_shipping_package_name'), 500, 3 );

    //Hide Admin Shipping If vendor Shipping is available
    add_filter( 'woocommerce_package_rates', array(&$this, 'wcfmmp_hide_admin_shipping' ), 100, 2 );

    //Hide Order Shipping If local picup selected for all Vendors
    add_filter( 'woocommerce_order_needs_shipping_address', array(&$this, 'wcfmmp_hide_shipping_address_if_local_pickup' ), 100, 3  );
    
  }
  
  public function wcfmmp_load_shipping_view( $user_id = 99999 ) {
    global $WCFM, $WCFMmp;
    
    if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) || !apply_filters( 'wcfm_is_allow_vshipping_settings', true ) ) return; 
    
    $wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return;
    
    $WCFMmp->template->get_template( 'shipping/wcfmmp-view-shipping-settings.php', array( 'user_id' => $user_id ) );
  }
  
  public function wcfmmp_vendor_shipping_settings_update( $user_id, $wcfm_settings_form ) {
  	
  	if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) || !apply_filters( 'wcfm_is_allow_vshipping_settings', true ) || !isset( $wcfm_settings_form['wcfmmp_shipping'] ) ) return; 
    
    if(!isset($wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_enable'])) {
      $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_enable'] = 'no';
    }

    update_user_meta($user_id, '_wcfmmp_shipping', $wcfm_settings_form['wcfmmp_shipping']);
    
    // By Country settings save
    if(!empty( $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type']) && $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type'] == 'by_country') {
      //print_r($wcfm_settings_form);
      if(isset($wcfm_settings_form['wcfmmp_shipping_by_country'])) {
        update_user_meta($user_id, '_wcfmmp_shipping_by_country', $wcfm_settings_form['wcfmmp_shipping_by_country']);
      }
      // Shipping Rates
      if(isset($wcfm_settings_form['wcfmmp_shipping_rates']) && !empty($wcfm_settings_form['wcfmmp_shipping_rates'])) {
        $wcfmmp_country_rates = array();
        $wcfmmp_state_rates   = array(); 
        foreach( $wcfm_settings_form['wcfmmp_shipping_rates'] as $wcfmmp_shipping_rates ) {
          if( isset( $wcfmmp_shipping_rates['wcfmmp_country_to'] ) ) {
            if( isset( $wcfmmp_shipping_rates['wcfmmp_shipping_state_rates'] ) && !empty( $wcfmmp_shipping_rates['wcfmmp_shipping_state_rates'] ) ) {
              foreach( $wcfmmp_shipping_rates['wcfmmp_shipping_state_rates'] as $wcfmmp_shipping_state_rates ) {
                if( isset( $wcfmmp_shipping_state_rates['wcfmmp_state_to'] ) ) {
                  $wcfmmp_state_rates[$wcfmmp_shipping_rates['wcfmmp_country_to']][$wcfmmp_shipping_state_rates['wcfmmp_state_to']] = $wcfmmp_shipping_state_rates['wcfmmp_state_to_price'];
                }
              }
            }
            $wcfmmp_country_rates[$wcfmmp_shipping_rates['wcfmmp_country_to']] = $wcfmmp_shipping_rates['wcfmmp_country_to_price'];
          }
        }
        update_user_meta( $user_id, '_wcfmmp_country_rates', $wcfmmp_country_rates );
        update_user_meta( $user_id, '_wcfmmp_state_rates', $wcfmmp_state_rates );
      }
    }
    
    // By zone settings save
    if( !empty( $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type'] ) && $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type'] == 'by_zone') {

      //print_r($wcfm_settings_form);
      $all_allowed_countries = WC()->countries->get_allowed_countries();
      $location = array();
      $zone_id = 0;
      //print_r($all_allowed_countries);
      if(!empty($wcfm_settings_form['wcfmmp_shipping_zone'])) {
        foreach( $wcfm_settings_form['wcfmmp_shipping_zone'] as $shipping_zone ) {
          if( isset($shipping_zone['_zone_id'] ) && $shipping_zone['_zone_id'] != 0 ) {
            $zone_id = $shipping_zone['_zone_id'];
            if(isset( $shipping_zone['_limit_zone_location'] ) && $shipping_zone['_limit_zone_location'] ) {
              if(!empty($shipping_zone['_select_zone_country'])) {
                $country_array = array();
                foreach ( $shipping_zone['_select_zone_country'] as $zone_country ) {
                  $country_array[] = array(
                    'code' => $zone_country,
                    'type'  => 'country'
                  );
                }
                $location = array_merge( $location, $country_array );
              }
              if(!empty($shipping_zone['_select_zone_states'])) {
                $state_array = array();
                foreach ( $shipping_zone['_select_zone_states'] as $zone_state ) {
                  $state_array[] = array(
                    'code' => $zone_state,
                    'type'  => 'state'
                  );
                }
                $location = array_merge( $location, $state_array );
              }
                            
              if(!empty($shipping_zone['_select_zone_city'])) {
                $city_array = array();
                foreach ( $shipping_zone['_select_zone_city'] as $zone_city ) {
                  $city_array[] = array(
                    'code' => $zone_city,
                    'type'  => 'city'
                  );
                }
                $location = array_merge( $location, $city_array );
              }
              
              if(!empty($shipping_zone['_select_zone_postcodes'])) {
                $postcode_array = array();
                $zone_postcodes = array_map('trim', explode(',', $shipping_zone['_select_zone_postcodes'] ));
                foreach ( $zone_postcodes as $zone_postcode ) {
                  $postcode_array[] = array(
                    'code' => $zone_postcode,
                    'type'  => 'postcode'
                  );
                }
                $location = array_merge( $location, $postcode_array );
              }
              
            }
          }
        }
        //print_r($location);
      }
      WCFMmp_Shipping_Zone::save_location( $location, $zone_id, $user_id );
    }
    // By weight settings save
    if(!empty( $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type']) && $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type'] == 'by_weight') {
      //print_r($wcfm_settings_form);
      
      if( isset( $wcfm_settings_form['wcfmmp_shipping_by_weight'] ) ) {
				update_user_meta( $user_id, '_wcfmmp_shipping_by_weight', $wcfm_settings_form['wcfmmp_shipping_by_weight'] );
			}
      
      $wcfmmp_country_weight_rates   = array(); 
      $wcfmmp_country_weight_mode = array();
      $wcfmmp_country_weight_unit_cost = array();
      $wcfmmp_country_weight_default_costs = array();
      foreach( $wcfm_settings_form['wcfmmp_shipping_rates_by_weight'] as $wcfmmp_shipping_rates_by_weight ) {
        if( $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_to'] ) {
          if( $wcfmmp_shipping_rates_by_weight['wcfmmp_shipping_country_weight_settings'] && !empty( $wcfmmp_shipping_rates_by_weight['wcfmmp_shipping_country_weight_settings'] ) ) {
            $wcfmmp_country_weight_rates[$wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_to']] = $wcfmmp_shipping_rates_by_weight['wcfmmp_shipping_country_weight_settings'];
            $wcfmmp_country_weight_mode[$wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_to']] = ( $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_mode'] || $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_mode'] != "" ) ? $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_mode'] : 'by_rule';
            $wcfmmp_country_weight_unit_cost[$wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_to']] = ( $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_per_unit_cost'] || $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_per_unit_cost'] != "" ) ? $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_per_unit_cost'] : 0;
            $wcfmmp_country_weight_default_costs[$wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_to']] = ( $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_default_cost'] || $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_default_cost'] != "" ) ? $wcfmmp_shipping_rates_by_weight['wcfmmp_weightwise_country_default_cost'] : 0;
          }
        }
      }
      
      update_user_meta( $user_id, '_wcfmmp_country_weight_rates', $wcfmmp_country_weight_rates );
      update_user_meta( $user_id, '_wcfmmp_country_weight_mode', $wcfmmp_country_weight_mode );
      update_user_meta( $user_id, '_wcfmmp_country_weight_unit_cost', $wcfmmp_country_weight_unit_cost );
      update_user_meta( $user_id, '_wcfmmp_country_weight_default_costs', $wcfmmp_country_weight_default_costs );
      //print_r($wcfmmp_country_rates);
      
    }
    
    // By Distance settings save
    if(!empty( $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type']) && $wcfm_settings_form['wcfmmp_shipping']['_wcfmmp_user_shipping_type'] == 'by_distance') {
      //print_r($wcfm_settings_form);
      
      if( isset( $wcfm_settings_form['wcfmmp_shipping_by_distance'] ) ) {
				update_user_meta( $user_id, '_wcfmmp_shipping_by_distance', $wcfm_settings_form['wcfmmp_shipping_by_distance'] );
			}
			
			if( isset( $wcfm_settings_form['wcfmmp_shipping_by_distance_rates'] ) ) {
				update_user_meta( $user_id, '_wcfmmp_shipping_by_distance_rates', $wcfm_settings_form['wcfmmp_shipping_by_distance_rates'] );
			}
    }
  }
  
  /**
   * WCFM Shipping info at Single Product Page 
   */
  function wcfmmp_shipping_info() {
  	global $WCFM, $WCFMmp, $post;
  	
  	if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) ) return; 
		if( !apply_filters( 'wcfm_is_allow_shipping_processing_time_info', true ) ) return;
		
		$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return;
			
		$vendor_id = 0;
		$product_id = 0;
		if( is_product() && $post && is_object( $post ) ) {
			$product_id = $post->ID;
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		}
		if( !$product_id ) return;
		if( !$WCFM->frontend->is_wcfm_needs_shipping( $product_id ) ) return;
		if( !$vendor_id ) return;
		
		if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by( $vendor_id ) ) {
			$WCFMmp->template->get_template( 'shipping/wcfmmp-view-shipping-info.php', array( 'vendor_id' => $vendor_id, 'product_id' => $product_id ) );
    }
  }
  
  /**
    * split woocommerce shipping packages 
    * @since 1.0.0
    * @param array $packages
    * @return array
    */
  public function wcfmmp_split_shipping_packages($packages) {
    // Reset all packages
    global $WCFM;
    
    if( apply_filters( 'wcfm_is_allow_store_shipping', true ) && class_exists( 'WCFMmp_Store' ) ) {
    
			$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
			$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
			if( $wcfmmp_store_shipping_enabled == 'yes' ) {
    
				$packages              = array();
				$split_packages        = array();
				$processing_times      = wcfmmp_get_shipping_processing_times();
				
				foreach (WC()->cart->get_cart() as $item) {
					if ($item['data']->needs_shipping()) {
						$product_id = $item['product_id'];
		
						$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
						if ($vendor_id && wcfmmp_is_shipping_enabled($vendor_id)) {
							$split_packages[$vendor_id][] = $item;
						} else {
							$split_packages[0][] = $item;
						}
					}
				}
		
				foreach ($split_packages as $vendor_id => $split_package) {
					
					$processing_time = '-';
					$store_address   = '';
					if( $vendor_id ) {
						$wcfmmp_shipping          = get_user_meta( $vendor_id, '_wcfmmp_shipping', true );
						$processing_time          = isset($wcfmmp_shipping['_wcfmmp_pt']) ? $wcfmmp_shipping['_wcfmmp_pt'] : '';
						if( !empty( $split_package ) && isset( $split_package[0] ) && isset( $split_package[0]['product_id'] ) ) {
							$processing_time        = get_post_meta( $split_package[0]['product_id'], '_wcfmmp_processing_time', true ) ? get_post_meta( $split_package[0]['product_id'], '_wcfmmp_processing_time', true ) : $processing_time;
						}
						$processing_time          = ( $processing_time && isset( $processing_times[$processing_time] ) ) ? $processing_times[$processing_time] : '-';
						$store_user               = wcfmmp_get_store( $vendor_id );
						$store_address            = $store_user->get_address_string();
				
						$packages[$vendor_id] = array(
							'contents'        => $split_package,
							'contents_cost'   => array_sum(wp_list_pluck($split_package, 'line_total')),
							'applied_coupons' => WC()->cart->get_applied_coupons(),
							'user' => array(
								 'ID'           => apply_filters( 'wcfm_current_vendor_id', get_current_user_id() ),
							),
							'destination'     => array(
								'country'       => WC()->customer->get_shipping_country(),
								'state'         => WC()->customer->get_shipping_state(),
								'postcode'      => WC()->customer->get_shipping_postcode(),
								'city'          => WC()->customer->get_shipping_city(),
								'address'       => WC()->customer->get_shipping_address(),
								'address_2'     => WC()->customer->get_shipping_address_2()
							),
							'vendor_id'       => $vendor_id,
							'pickup_address'  => $store_address,
							'processing_time' => $processing_time
						);
						
					} else {
						$packages[$vendor_id] = array(
							'contents'        => $split_package,
							'contents_cost'   => array_sum(wp_list_pluck($split_package, 'line_total')),
							'applied_coupons' => WC()->cart->get_applied_coupons(),
							'user' => array(
								 'ID'           => apply_filters( 'wcfm_current_vendor_id', get_current_user_id() ),
							),
							'destination'     => array(
								'country'       => WC()->customer->get_shipping_country(),
								'state'         => WC()->customer->get_shipping_state(),
								'postcode'      => WC()->customer->get_shipping_postcode(),
								'city'          => WC()->customer->get_shipping_city(),
								'address'       => WC()->customer->get_shipping_address(),
								'address_2'     => WC()->customer->get_shipping_address_2()
							)
						);
					}
					
					if( apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
						$wcfmmp_user_location     = WC()->session->get( '_wcfmmp_user_location' );
						$wcfmmp_user_location_lat = WC()->session->get( '_wcfmmp_user_location_lat' );
						$wcfmmp_user_location_lng = WC()->session->get( '_wcfmmp_user_location_lng' );
						if( $wcfmmp_user_location ) {
							$packages[$vendor_id]['wcfmmp_user_location']     = $wcfmmp_user_location;
							$packages[$vendor_id]['wcfmmp_user_location_lat'] = $wcfmmp_user_location_lat;
							$packages[$vendor_id]['wcfmmp_user_location_lng'] = $wcfmmp_user_location_lng;
						}
					}
				}
			}
		}
		
    return apply_filters('wcfmmp_split_shipping_packages', $packages);
  }
  
  /**
   * 
   * @param object $item
   * @param sting $package_key as $vendor_id
   */
  public function wcfmmp_add_meta_date_in_shipping_package( $item, $package_key, $package ) {
  	
  	if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) ) return; 
		
		$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return;
  	
    $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' ); 
    $ship_method_id = $chosen_shipping_methods[$package_key];
    $id = explode( ":", $ship_method_id, 2 );
    $id = $id[0];
    
    // Shipping Vendor Reference
    if( $package_key && wcfm_is_vendor( $package_key ) ) {
    	$item->add_meta_data( 'vendor_id', $package_key, true );
    } else { // Fetch Vendor Reference from Item
    	if( apply_filters( 'wcfmmp_is_allow_shipping_package_meta_from_item', true ) ) {
				if( isset( $package['contents'] ) ) {
					foreach( $package['contents'] as $key => $pkg_values ) {
						if( isset( $pkg_values['product_id'] ) ) {
							$product_id = $pkg_values['product_id'];
							$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
							if( $vendor_id ) {
								$item->add_meta_data( 'vendor_id', $vendor_id, true );
							}
						}
					}
				}
			}
    }
    
    $package_qty = array_sum( wp_list_pluck( $package['contents'], 'quantity' ) );
    $item->add_meta_data( 'package_qty', $package_qty, true );
    $item->add_meta_data( 'method_slug', $id, true );
    
    // Local Pickup Address
    if( ( $id == 'local_pickup' ) && isset( $package['pickup_address'] ) && apply_filters( 'wcfmmp_is_allow_local_pickup_address', true ) ) {
    	$item->add_meta_data( 'pickup_address', $package['pickup_address'], true );
    }
    
    // Processing Time
    if( isset( $package['processing_time'] ) && apply_filters( 'wcfm_is_allow_shipping_processing_time_info', true ) ) {
    	$item->add_meta_data( 'processing_time', $package['processing_time'], true );
    }
    
    do_action( 'wcfmmp_add_shipping_package_meta_data', $id, $package_key, $package, $item );
  }
  
  /**
     * Rename shipping packages 
     * @since 1.0.0
     * @param string $package_name
     * @param string $vendor_id
     * @param array $package
     * @return string
     */
  public function wcfmmp_shipping_package_name($package_name, $vendor_id, $package) {
    global $WCFM, $WCFMmp;
    
    if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) ) return $package_name; 
		
		$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return $package_name;
		
		if( apply_filters( 'wcfmmp_is_allow_shipping_package_name_from_item', true ) ) {
			if( !$vendor_id || ( $vendor_id != 0 ) || ( $vendor_id && !wcfm_is_vendor( $vendor_id ) ) ) {
				if( isset( $package['contents'] ) ) {
					foreach( $package['contents'] as $key => $pkg_values ) {
						if( isset( $pkg_values['product_id'] ) ) {
							$product_id = $pkg_values['product_id'];
							$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
						}
					}
				}
			}
		}
    
    if ($vendor_id && $vendor_id != 0) {
			if( wcfm_is_vendor( $vendor_id ) && $WCFMmp->wcfmmp_vendor->is_vendor_sold_by( $vendor_id ) ) {
				
				$min_amount   = 0;
				$is_available = false;
				
				$vendor_shipping_details = get_user_meta( $vendor_id, '_wcfmmp_shipping', true );
				$type = !empty( $vendor_shipping_details['_wcfmmp_user_shipping_type'] ) ? $vendor_shipping_details['_wcfmmp_user_shipping_type'] : '';
				
				$Store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( $vendor_id );
				$shipping_label_str = $Store_name . ' ' . __('Shipping', 'wc-multivendor-marketplace' );
				
				if( !empty($type) ) {
					
					if( apply_filters( 'wcfmmp_is_allow_free_shipping_coupon', true ) ) {
						$has_free_coupon = false;
						$coupons = WC()->cart->get_coupons();
						if ( $coupons ) {
							foreach ( $coupons as $code => $coupon ) {
								if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
									$coupon_post   = get_post( $coupon->get_id() );
									$coupon_author = $coupon_post->post_author;
									if( ( $coupon_author == $vendor_id ) || apply_filters( 'wcfmmp_is_allow_admin_free_shipping_coupon', false ) ) {
										$has_free_coupon = true;
										break;
									}
								}
							}
						}
						if( $has_free_coupon ) {
							return esc_html($shipping_label_str);
						}
					}
					
					$line_subtotal      = wp_list_pluck( $package['contents'], 'line_subtotal', null );
					$line_total         = wp_list_pluck( $package['contents'], 'line_total', null );
					$discount_total     = array_sum( $line_subtotal ) - array_sum( $line_total );
					$line_subtotal_tax  = wp_list_pluck( $package['contents'], 'line_subtotal_tax', null );
					$line_total_tax     = wp_list_pluck( $package['contents'], 'line_tax', null );
					$discount_tax_total = array_sum( $line_subtotal_tax ) - array_sum( $line_total_tax );
		
					if( apply_filters( 'wcfmmp_free_shipping_threshold_consider_tax', true ) ) {
						$total = array_sum( $line_subtotal ) + array_sum( $line_subtotal_tax );
					} else {
						$total = array_sum( $line_subtotal );
					}
		
					if ( WC()->cart->display_prices_including_tax() ) {
						$total = round( $total - ( $discount_total + $discount_tax_total ), wc_get_price_decimals() );
					} else {
						$total = round( $total - $discount_total, wc_get_price_decimals() );
					}
				
					if( $type == 'by_zone' ) {
						$zone = WC_Shipping_Zones::get_zone_matching_package( $package );
						$shipping_methods = WCFMmp_Shipping_Zone::get_shipping_methods( $zone->get_id(), $vendor_id );
						foreach ( $shipping_methods as $key => $method ) {
							if ( 'free_shipping' == $method['id'] && 'yes' == $method['enabled'] ) {
								$is_available = true;
								$min_amount = (isset( $method['settings']['min_amount'] ) ) ? $method['settings']['min_amount'] : 0;
								$min_amount = apply_filters( 'wcfmmp_free_shipping_minimum_order_amount', $min_amount, $vendor_id );
							}
						}
					}
				
					if( $type == 'by_country' ) {
					  $wcfmmp_shipping_by_country = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_country', true );
					  $is_available = true;
					  $min_amount = isset($wcfmmp_shipping_by_country['_free_shipping_amount']) ? $wcfmmp_shipping_by_country['_free_shipping_amount'] : 0;
					  $min_amount = apply_filters( 'wcfmmp_free_shipping_minimum_order_amount', $min_amount, $vendor_id );
					}
					
					if( $type == 'by_weight' ) {
					  $wcfmmp_shipping_by_weight = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_weight', true );
					  $is_available = true;
					  $min_amount = isset($wcfmmp_shipping_by_weight['_free_shipping_amount']) ? $wcfmmp_shipping_by_weight['_free_shipping_amount'] : 0;
					  $min_amount = apply_filters( 'wcfmmp_free_shipping_minimum_order_amount', $min_amount, $vendor_id );
					}
					
					if( $type == 'by_distance' ) {
					  $wcfmmp_shipping_by_distance = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_distance', true );
					  $is_available = true;
					  $min_amount = isset($wcfmmp_shipping_by_distance['_free_shipping_amount']) ? $wcfmmp_shipping_by_distance['_free_shipping_amount'] : 0;
					  $min_amount = apply_filters( 'wcfmmp_free_shipping_minimum_order_amount', $min_amount, $vendor_id );
					  
					  $distance = wcfmmp_get_user_vendor_distance( $vendor_id );
					  $radius_unit   = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';
					  
					  $wcfmmp_shipping_by_distance = get_user_meta( $vendor_id, '_wcfmmp_shipping_by_distance', true );
					  $max_distance = isset($wcfmmp_shipping_by_distance['_max_distance']) ? $wcfmmp_shipping_by_distance['_max_distance'] : '';
					  
					  $shipping_label_str .= '&#10;';
						$shipping_label_str .= '(';
						if( $distance ) {
							$shipping_label_str .= __( 'Distance', 'wc-multivendor-marketplace') . ': ' . $distance . $radius_unit;
						}
						if( $max_distance ) {
							if( $distance ) {
								$shipping_label_str .= ', ';
							}
							$shipping_label_str .= __( 'Deliver upto', 'wc-multivendor-marketplace') . ': ' . $max_distance . $radius_unit;
						}
						$shipping_label_str .= ')';
					}
					
					$more_to_free_shipping_text_enable = apply_filters( 'wcfmmp_more_to_free_shipping_text_enable', true );
					if( !empty($type) && $more_to_free_shipping_text_enable && $is_available && ( ( $min_amount > 0 ) && ($total < $min_amount ) ) ) {
						$shipping_label_str .= '&#10;';
						$shipping_label_str .= '(' . sprintf( __( 'Shop for %s%s more to get free shipping', 'wc-multivendor-marketplace' ), get_woocommerce_currency_symbol(), round( ($min_amount - $total), 2 ) ) . ')';
					}
				}
				
				return esc_html($shipping_label_str);
			}
    }
    return $package_name;
  }
  
  /**
   * Rename vendor shipping for an order 
   * @since 1.0.0
   * @param object $order
   * @return array
   */
  public function get_order_vendor_shipping( $order ) {
    global $WCFM, $WCFMmp;
    
    $vendor_shipping = array();
    
    if( !$order ) return $vendor_shipping;
    
    if( is_numeric( $order ) ) {
      $order = wc_get_order( $order );
    }
    
    $shipping_items = $order->get_items('shipping');
    foreach ($shipping_items as $shipping_item_id => $shipping_item) {
      $order_item_shipping = new WC_Order_Item_Shipping($shipping_item_id);
      $shipping_vendor_id = $order_item_shipping->get_meta('vendor_id', true);
      if( !$shipping_vendor_id ) $shipping_vendor_id = 0;
      
      $refunded_shipping_amount = $order->get_total_refunded_for_item( $shipping_item_id, 'shipping' );
      $refunded_shipping_tax    = 0;
      
      if ( ( $tax_data = $order_item_shipping->get_taxes() ) && wc_tax_enabled() ) {
      	$order_taxes   = $order->get_taxes();
				foreach ( $order_taxes as $tax_item ) {
					$tax_item_id    = $tax_item->get_rate_id();
					$refunded_shipping_tax += $order->get_tax_refunded_for_item( $shipping_item_id, $tax_item_id, 'shipping' );
				}
			}
      
      $vendor_shipping[$shipping_vendor_id] = array(
            'shipping'            => $order_item_shipping->get_total()
          , 'shipping_tax'        => $order_item_shipping->get_total_tax()
          , 'package_qty'         => $order_item_shipping->get_meta('package_qty', true)
          , 'shipping_item_id'    => $shipping_item_id
          , 'refunded_amount'     => $refunded_shipping_amount
          , 'refunded_tax'        => $refunded_shipping_tax
      );
    }
    return $vendor_shipping;
  }

  /**
   * Hide Admin Shipping If vendor Shipping is available callback
   * @since 1.0.2
   * @param array $rates
   * @return array
   */
  public function wcfmmp_hide_admin_shipping( $rates, $package ) {
    //print_r($rates); die;
    $free_shipping_available = false;
    $wcfmmp_shipping = array();
    
    if( apply_filters( 'wcfm_is_allow_hide_admin_shipping_for_vendor_shipping', true ) ) {
			if( isset( $package['vendor_id'] ) ) {
				foreach ( $rates as $rate_id => $rate ) {
					if ( 'wcfmmp_product_shipping_by_country' === $rate->method_id || 'wcfmmp_product_shipping_by_zone' === $rate->method_id || 'wcfmmp_product_shipping_by_weight' === $rate->method_id || 'wcfmmp_product_shipping_by_distance' === $rate->method_id ) {
						$id = explode(":", $rate_id, 2);
						$id = $id[0];
						if($id === 'free_shipping') {
							$free_shipping_available = apply_filters( 'wcfm_is_allow_hide_other_shipping_if_free', true );
						}
						$wcfmmp_shipping[ $rate_id ] = $rate;  
					}
				}
				
				if($free_shipping_available) {
					foreach ( $wcfmmp_shipping as $rate_id => $rate ) { 
						$id = explode(":", $rate_id, 2);
						$id = $id[0];
						if( !in_array( $id, array( 'free_shipping', 'local_pickup' ) ) ) {
							unset($wcfmmp_shipping[$rate_id]);
						}
					}
				}
				
				if( !apply_filters( 'wcfm_is_allow_admin_shipping_if_no_vendor_shipping', true ) ) {
					$rates = array();
				}
			}
		}
    
    return ! empty( $wcfmmp_shipping ) ? $wcfmmp_shipping : $rates;
  }


  /**
   * Hide Order Shipping If local picup selected for all Vendors
   * @since 1.1.0
   * @return boolean
   */
  public function wcfmmp_hide_shipping_address_if_local_pickup ( $needs_address, $hide, $order_object ) {
    $local_pickup_all = false;
    foreach ( $order_object->get_shipping_methods() as $shipping_method ) {
      $shipping_method_slug = $shipping_method->get_meta( 'method_slug' );
      if( $shipping_method_slug && $shipping_method_slug == 'local_pickup' ) {
      	$local_pickup_all = true;
      } elseif( $shipping_method_slug && $shipping_method_slug != 'local_pickup' ) {
        $local_pickup_all = false;
        break;
      } else {
      	$local_pickup_all = false;
        break;
      }
    }
    
    if( $local_pickup_all ) {
      $needs_address = false;
    }
    
    return apply_filters( 'wcfmmp_order_needs_shipping_address', $needs_address, $hide, $order_object );
  }
  
}