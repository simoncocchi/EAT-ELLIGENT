<?php

/**
 * WCFM plugin core
 *
 * Marketplace WC Marketplace Support
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   1.1.0
 */
 
class WCFM_WCMarketplace {
	
	private $vendor_id;
	private $vendor_term;
	
	public function __construct() {
    global $WCFM;
    
    if( wcfm_is_vendor() ) {
    	
    	$this->vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    	$this->vendor_term = get_user_meta( $this->vendor_id, '_vendor_term_id', true );
    	
    	// My Account Dashboard Link
    	add_filter( 'wcmp_vendor_goto_dashboard', array( &$this, 'wcmarketplace_vendor_goto_dashboard' ) );
		
			// Store Identity
    	add_filter( 'wcfm_store_logo', array( &$this, 'wcmarketplace_store_logo' ) );
    	add_filter( 'wcfm_store_name', array( &$this, 'wcmarketplace_store_name' ) );
    	
			// Allow Vendor user to manage product from catalog
			add_filter( 'wcfm_allwoed_user_roles', array( &$this, 'allow_wcmarketplace_vendor_role' ) );
			add_filter( 'wcfm_allwoed_vendor_user_roles', array( &$this, 'allow_wcmarketplace_vendor_role' ) );
			
			// Filter Vendor Products
			add_filter( 'wcfm_products_args', array( &$this, 'wcmarketplace_products_args' ) );
			add_filter( 'get_booking_products_args', array( $this, 'wcmarketplace_products_args' ) );
			add_filter( 'get_appointment_products_args', array( $this, 'wcmarketplace_products_args' ) );
			add_filter( 'wpjmp_job_form_products_args', array( &$this, 'wcmarketplace_products_args' ) );
			add_filter( 'wpjmp_admin_job_form_products_args', array( &$this, 'wcmarketplace_products_args' ) );
			
			// Listing Filter for specific vendor
			add_filter( 'wcfm_articles_args', array( &$this, 'wcmarketplace_listing_args' ) );
    	add_filter( 'wcfm_listing_args', array( $this, 'wcmarketplace_listing_args' ), 20 );
    	add_filter( "woocommerce_product_export_product_query_args", array( &$this, 'wcmarketplace_listing_args' ), 100 );
    	
    	// Customers args
    	if( apply_filters( 'wcfm_is_allow_order_customers_to_vendors', true ) ) {
    		add_filter( 'wcfm_get_customers_args', array( &$this, 'wcmarketplace_filter_customers' ), 20 );
    	}
    	
    	// Booking Filter
			add_filter( 'wcfm_wcb_include_bookings', array( &$this, 'wcmarketplace_wcb_include_bookings' ) );
			
			// Payments Filter for specific vendor
    	add_filter( 'wcfm_payments_args', array( $this, 'wcmarketplace_payment_args' ), 20 );
    	
    	// Withdrawal Filter for specific vendor
    	add_filter( 'wcfm_withdrawal_args', array( $this, 'wcmarketplace_withdrawal_args' ), 20 );
			
			// Manage Vendor Product Permissions
			add_filter( 'wcfm_product_shipping_class', array( &$this, 'wcmarketplace_product_shipping_class'), 100 );
			
			add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcmarketplace_product_manage_vendor_association' ), 10, 2 );
			add_action( 'after_wcfm_product_duplicate', array( &$this, 'wcmarketplace_product_manage_vendor_association' ), 10, 2 );
			add_filter( 'wcfm_geo_locator_default_address', array( &$this, 'wcmarketplace_geo_locator_default_address' ) );
			add_filter( 'wcfm_geo_locator_default_latlng', array( &$this, 'wcmarketplace_geo_locator_default_latlng' ) );
			add_filter( 'gmw_location_form_default_location', array( &$this, 'wcmarketplace_geomywp_default_address' ), 10, 2 );
			
			// Manage Vendor Product Export Permissions - 2.4.2
			add_filter( 'woocommerce_product_export_row_data', array( &$this, 'wcmarketplace_product_export_row_data' ), 100, 2 );
			
			// Filter Vendor Coupons
			add_filter( 'wcfm_coupons_args', array( &$this, 'wcmarketplace_coupons_args' ) );
			
			// Manage Vendor Coupon Permission
			add_filter( 'wcmp_multi_vendor_coupon_types', array( &$this, 'wcmarketplace_multi_vendor_coupon_types' ) );
			add_filter( 'wcfm_coupon_types', array( &$this, 'wcmarketplace_coupon_types' ) );
			
			// Manage Order Details Permission
			add_filter( 'wcfm_allow_order_details', array( &$this, 'wcmarketplace_is_allow_order_details' ) );
			add_filter( 'wcfm_valid_line_items', array( &$this, 'wcmarketplace_valid_line_items' ), 10, 3 );
			add_filter( 'wcfm_order_details_shipping_line_item', array( &$this, 'wcmarketplace_is_allow_order_details_shipping_line_item' ) );
			add_filter( 'wcfm_order_details_tax_line_item', array( &$this, 'wcmarketplace_is_allow_order_details_tax_line_item' ) );
			add_filter( 'wcfm_order_details_line_total_head', array( &$this, 'wcmarketplace_is_allow_order_details_line_total_head' ) );
			add_filter( 'wcfm_order_details_line_total', array( &$this, 'wcmarketplace_is_allow_order_details_line_total' ) );
			add_filter( 'wcfm_order_details_tax_total', array( &$this, 'wcmarketplace_is_allow_order_details_tax_total' ) );
			add_filter( 'wcfm_order_details_fee_line_item', array( &$this, 'wcmarketplace_is_allow_order_details_fee_line_item' ) );
			add_filter( 'wcfm_order_details_refund_line_item', array( &$this, 'wcmarketplace_is_allow_order_details_refund_line_item' ) );
			add_filter( 'wcfm_order_details_coupon_line_item', array( &$this, 'wcmarketplace_is_allow_order_details_coupon_line_item' ) );
			add_filter( 'wcfm_order_details_total', array( &$this, 'wcmarketplace_is_allow_wcfm_order_details_total' ) );
			add_action( 'wcfm_order_details_after_line_total_head', array( &$this, 'wcmarketplace_after_line_total_head' ) );
			add_action( 'wcfm_after_order_details_line_total', array( &$this, 'wcmarketplace_after_line_total' ), 10, 2 );
			add_action( 'wcfm_order_totals_after_total', array( &$this, 'wcmarketplace_order_total_commission' ) );
			add_filter( 'wcfm_generate_csv_url', array( &$this, 'wcmarketplace_generate_csv_url' ), 10, 2 );
			
			// Report Filter
			add_filter( 'wcfm_report_out_of_stock_query_from', array( &$this, 'wcmarketplace_report_out_of_stock_query_from' ), 100, 2 );
			add_filter( 'woocommerce_reports_order_statuses', array( &$this, 'wcmarketplace_reports_order_statuses' ) );
			add_filter( 'woocommerce_dashboard_status_widget_top_seller_query', array( &$this, 'wcmarketplace_dashboard_status_widget_top_seller_query'), 100 );
			//add_filter( 'woocommerce_reports_get_order_report_data', array( &$this, 'wcmarketplace_reports_get_order_report_data'), 100 );
			
			// Knowledgebase
			add_action( 'before_wcfm_knowledgebase' , array( &$this, 'wcmarketplace_wcfm_knowledgebase' ) );
			
			// Single product multi-seller auto suggest - 3.3.7
			add_action( 'wp_ajax_wcfm_auto_search_product', array( &$this, 'wcmarketplace_auto_suggesion_product' ) );
			
			// Single product multi-seller association - 3.3.7
			add_action( 'wp_ajax_wcfm_product_multi_seller_associate', array( &$this, 'wcfm_product_multi_seller_associate' ) );
		}
  }
  
  // WCFM WCMp May Account Dashboard Link
  function wcmarketplace_vendor_goto_dashboard() {
  	return '<a href="' . get_wcfm_url() . '">' . __('Dashboard - manage your account here', 'dc-woocommerce-multi-vendor') . '</a>';
  }
  
  // WCFM WCMp Store Logo
  function wcmarketplace_store_logo( $store_logo ) {
  	$vendor = get_wcmp_vendor($this->vendor_id);
  	if ( $vendor->image ) {
			$store_logo = $vendor->image;
			if( $vendor->permalink ) {
				$store_logo = '<a class="wcfm_store_logo_icon" href="'.apply_filters('wcmp_vendor_shop_permalink', $vendor->permalink).'" target="_blank"><img src="' . $store_logo . '" alt="Store Logo" /></a>';
			}
		}
  	return $store_logo;
  }
  
  // WCFM WCMp Store Name
  function wcmarketplace_store_name( $store_name ) {
  	$vendor = get_wcmp_vendor( $this->vendor_id );
  	$shop_name = wcfm_get_option( 'wcfm_my_store_label', __( 'My Store', 'wc-frontend-manager' ) );  //get_user_meta( $this->vendor_id, '_vendor_page_title', true);
  	$vmstore_name = wcfm_get_option( 'wcfm_my_store_label', __( 'My Store', 'wc-frontend-manager' ) );  //get_user_meta( $this->vendor_id, 'store_name', true );
  	if( $shop_name ) { $store_name = '<a target="_blank" href="' . apply_filters('wcmp_vendor_shop_permalink', $vendor->permalink) . '">' . $shop_name . '</a>'; }
  	elseif( $vmstore_name ) { $store_name = '<a target="_blank" href="' . apply_filters('wcmp_vendor_shop_permalink', $vendor->permalink) . '">' . $vmstore_name . '</a>'; }
  	else { $store_name = '<a target="_blank" href="' . apply_filters('wcmp_vendor_shop_permalink', $vendor->permalink) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
  	return $store_name;
  }
  
  // WCMp user roles
  function allow_wcmarketplace_vendor_role( $allowed_roles ) {
  	if( wcfm_is_vendor() ) $allowed_roles[] = 'dc_vendor';
  	return $allowed_roles;
  }
  
  // Product args
  function wcmarketplace_products_args( $args ) {
  	if( wcfm_is_vendor() ) {
  		//$args['author'] = $this->vendor_id;
  		$vendor_term = get_user_meta( $this->vendor_id, '_vendor_term_id', true );
  		$vendor_term = absint( $vendor_term );
  		$args['tax_query'][] = array(
																		'taxonomy' => 'dc_vendor_shop',
																		'field' => 'id',
																		'terms' => $vendor_term,
																		'operator' => 'IN'
																	);
		}
  	return $args;
  }
  
  // WCMp Listing args
	function wcmarketplace_listing_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  /**
   * WCMp filter customers
   */
  function wcmarketplace_filter_customers( $args ) {
  	global $WCFM, $wpdb;
  	
  	$vendor_customers  = array();
  	// Own Customers
  	$wcfm_customers_array = get_users( $args );
  	if(!empty($wcfm_customers_array)) {
			foreach( $wcfm_customers_array as $wcfm_customers_single ) {
				$vendor_customers[$wcfm_customers_single->ID] = $wcfm_customers_single->ID;
			}
		}
  	
		// Order Customers
  	$sql = 'SELECT order_id FROM ' . $wpdb->prefix . 'wcmp_vendor_orders';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `vendor_id` = {$this->vendor_id}";
		$wcfm_orders_array = $wpdb->get_results( $sql );
		if(!empty($wcfm_orders_array)) {
			foreach($wcfm_orders_array as $wcfm_orders_single) {
				$the_order = wc_get_order( $wcfm_orders_single->order_id );
				if ( $the_order && is_object( $the_order ) && $the_order->get_user_id() ) {
					$vendor_customers[$the_order->get_user_id()] = $the_order->get_user_id();
				}
			}
		}
		if( !empty( $vendor_customers ) ) {
			$args['include'] = array_keys( array_unique( $vendor_customers ) );
		} else {
			$args['include'] = array(0);
		}
		if( isset( $args['meta_key'] ) ) unset( $args['meta_key'] );
		if( isset( $args['meta_value'] ) ) unset( $args['meta_value'] );
		return $args;
  }
  
  /**
   * WC Marketplace Bookings
   */
  function wcmarketplace_wcb_include_bookings( ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$products = $this->wcmarketplace_get_vendor_products();
		if( empty($products) ) return array(0);
		
  	$query = "SELECT ID FROM {$wpdb->posts} as posts
							INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
							WHERE 1=1
							AND posts.post_type IN ( 'wc_booking' )
							AND postmeta.meta_key = '_booking_product_id' AND postmeta.meta_value in (" . implode(',', $products) . ")";
		
		$vendor_bookings = $wpdb->get_results($query);
		if( empty($vendor_bookings) ) return array(0);
		$vendor_bookings_arr = array();
		foreach( $vendor_bookings as $vendor_booking ) {
			$vendor_bookings_arr[] = $vendor_booking->ID;
		}
		if( !empty($vendor_bookings_arr) ) return $vendor_bookings_arr;
		return array(0);
  }
  
  // WCMp Payments Transaction args
  function wcmarketplace_payment_args( $vendor_term_id ) {
  	$vendor_term_id = $this->vendor_term;
  	return $vendor_term_id;
  }
  
  // WCMp Withdrawal args
  function wcmarketplace_withdrawal_args( $args ) {
  	$args['meta_query'][] = array(
																'key' => '_commission_vendor',
																'value' => absint( $this->vendor_term ),
																'compare' => '='
														);
  	return $args;
  }
  
  // Shipping Class filtering as Per vendor
  function wcmarketplace_product_shipping_class( $product_shipping_class ) {
  	$vendor_shipping_class_id = get_user_meta( $this->vendor_id, 'shipping_class_id', true );
  	$filtered_product_shipping_class = array();
  	
  	foreach($product_shipping_class as $product_shipping) {
			if( $vendor_shipping_class_id != $product_shipping->term_id ) continue;
			$filtered_product_shipping_class[$product_shipping->term_id] = $product_shipping;
		}
  	
  	return $filtered_product_shipping_class;
  }
  
  // Product Vendor association on Product save
  function wcmarketplace_product_manage_vendor_association( $new_product_id, $wcfm_products_manage_form_data ) {
  	global $WCFM, $WCMp;
  	
  	$vendor_term = get_user_meta( $this->vendor_id, '_vendor_term_id', true );
		$vendor_term = absint( $vendor_term );
		wp_delete_object_term_relationships( $new_product_id, 'dc_vendor_shop' );
		wp_set_object_terms( $new_product_id, $vendor_term, 'dc_vendor_shop', true );
		
		// Admin Message for Pending Review
		$product_status = get_post_status( $new_product_id );
		if( $product_status == 'pending' ) {
			$WCFM->wcfm_notification->wcfm_admin_notification_product_review( $this->vendor_id, $new_product_id );
		} else {
			$WCFM->wcfm_notification->wcfm_admin_notification_new_product( $this->vendor_id, $new_product_id );
		}
  }
  
  // Geo Locator default address - 3.2.8
  function wcmarketplace_geo_locator_default_address( $store_address ) {
		$map_address = get_user_meta( $this->vendor_id, '_find_address', true ) ? get_user_meta( $this->vendor_id, '_find_address', true ) : '';
  	return $map_address;
  }
  
  // Geo Locator default address - 3.2.8
  function wcmarketplace_geo_locator_default_latlng( $store_latlng ) {
		$store_lat = get_user_meta( $this->vendor_id, '_store_lat', true ) ? get_user_meta( $this->vendor_id, '_store_lat', true ) : 0;
		$store_lng = get_user_meta( $this->vendor_id, '_store_lng', true ) ? get_user_meta( $this->vendor_id, '_store_lng', true ) : 0;
		if( $store_lat && $store_lng ) $store_latlng = '('. $store_lat . ',' . $store_lng . ')';
  	return $store_latlng;
  }
  
  // GEO my WP default address - 4.0.7
  function wcmarketplace_geomywp_default_address( $gmw_saved_location, $args ) {
  	
  	if ( empty( $gmw_saved_location ) ) {
  		
			$map_address = get_user_meta( $this->vendor_id, '_find_address', true ) ? get_user_meta( $this->vendor_id, '_find_address', true ) : '';
			$store_location = get_user_meta( $this->vendor_id, '_store_location', true ) ? get_user_meta( $this->vendor_id, '_store_location', true ) : '';
			$store_lat = get_user_meta( $this->vendor_id, '_store_lat', true ) ? get_user_meta( $this->vendor_id, '_store_lat', true ) : 0;
			$store_lng = get_user_meta( $this->vendor_id, '_store_lng', true ) ? get_user_meta( $this->vendor_id, '_store_lng', true ) : 0;
	
			$addr_1  = get_user_meta( $this->vendor_id, '_vendor_address_1', true );
			$addr_2  = get_user_meta( $this->vendor_id, '_vendor_address_2', true );
			$country  = get_user_meta( $this->vendor_id, '_vendor_country', true );
			$city  = get_user_meta( $this->vendor_id, '_vendor_city', true );
			$state  = get_user_meta( $this->vendor_id, '_vendor_state', true );
			$zip  = get_user_meta( $this->vendor_id, '_vendor_postcode', true );
					
			// Country -> States
			$country_obj   = new WC_Countries();
			$countries     = $country_obj->countries;
			$states        = $country_obj->states;
			$country_name = '';
			$state_name = '';
			if( $country ) $country_name = $country;
			if( $state ) $state_name = $state;
			if( $country && isset( $countries[$country] ) ) {
				$country_name = $countries[$country];
			}
			if( $state && isset( $states[$country] ) && is_array( $states[$country] ) ) {
				$state_name = ($states[$country][$state]) ? $states[$country][$state] : '';
			}
		
			$gmw_saved_location = array(
				  'ID'            	=> 0,
				  'latitude'      	=> $store_lat,
					'longitude'     	=> $store_lng,
					'street_number' 	=> $addr_1,
					'street_name'   	=> $addr_2,
					'street'        	=> $addr_1,
					'premise'       	=>  '',
					'neighborhood'  	=>  '',
					'city'          	=> $city,
					'county'        	=> $country,
					'region_name'    	=> $state_name,
					'region_code'    	=> $state,
					'postcode'        => $zip,
					'country_name'  	=> $country_name,
					'country_code' 		=> $country,
					'address' 			  => $map_address,
					'formatted_address' => $map_address
			);
		}
  	
  	return $gmw_saved_location;
  }
  
  // Product Export Data Filter - 2.4.2
  function wcmarketplace_product_export_row_data( $row, $product ) {
  	global $WCFM, $WCMp;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->wcmarketplace_get_vendor_products();
		
		if( !in_array( $product->get_ID(), $products ) ) return array();
		
		return $row;
  }
  
  // Coupons Args
  function wcmarketplace_coupons_args( $args ) {
  	if( wcfm_is_vendor() ) $args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // WCMp Restricted Coupon Types
  function wcmarketplace_multi_vendor_coupon_types( $types ) {
  	$types = array( 'fixed_cart' );
  	return $types;
  }
  
  // Coupon Types
  function wcmarketplace_coupon_types( $types ) {
  	$wcmp_coupon_types = array( 'percent', 'fixed_product' );
  	foreach( $types as $type => $label ) 
  		if( !in_array( $type, $wcmp_coupon_types ) ) unset( $types[$type] );
  	return $types;
  } 
  
  // Order Status details
  function wcmarketplace_is_allow_order_details( $allow ) {
  	return false;
  }
  
  // Filter Order Details Line Items as Per Vendor
  function wcmarketplace_valid_line_items( $items, $order_id ) {
  	global $WCFM, $wpdb;
  	
  	$sql = "SELECT `product_id` FROM {$wpdb->prefix}wcmp_vendor_orders WHERE `vendor_id` = {$this->vendor_id} AND `order_id` = {$order_id}";
  	$valid_products = $wpdb->get_results($sql);
  	$valid_items = array();
  	if( !empty($valid_products) ) {
  		foreach( $valid_products as $valid_product ) {
  			$valid_items[] = $valid_product->product_id;
  		}
  	}
  	
  	$valid = array();
  	foreach ($items as $key => $value) {
			if ( in_array( $value->get_variation_id(), $valid_items ) || in_array( $value->get_product_id(), $valid_items ) ) {
				$valid[$key] = $value;
			} elseif( $value->get_product_id() == 0 ) {
				$_product_id = wc_get_order_item_meta( $key, '_product_id', true );
				if ( in_array( $_product_id, $valid_items ) ) {
					$valid[$key] = $value;
				}
			}
		}
  	return $valid;
  }
  
  // Order Details Shipping Line Item
  function wcmarketplace_is_allow_order_details_shipping_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Tax Line Item
  function wcmarketplace_is_allow_order_details_tax_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Total Line Item Head
  function wcmarketplace_is_allow_order_details_line_total_head( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Total Line Item
  function wcmarketplace_is_allow_order_details_line_total( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Tax Total
  function wcmarketplace_is_allow_order_details_tax_total( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Fee Line Item
  function wcmarketplace_is_allow_order_details_fee_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Coupon Line Item
  function wcmarketplace_is_allow_order_details_coupon_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Refunded Line Item
  function wcmarketplace_is_allow_order_details_refund_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Total
  function wcmarketplace_is_allow_wcfm_order_details_total( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // wcmarketplace After Order Total Line Head
  function wcmarketplace_after_line_total_head( $order ) {
  	global $WCFM, $WCMp;
  	$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
  	
  	if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) {
			if( $admin_fee_mode ) {
			?>
				<th class="line_cost sortable" data-sort="float"><?php _e( 'Fees', 'wc-frontend-manager' ); ?></th>
			<?php } else { ?>
				<th class="line_cost sortable" data-sort="float"><?php _e( 'Commission', 'wc-frontend-manager' ); ?></th>
			<?php
			}
		}
		
  	if ( $WCMp->vendor_caps->vendor_payment_settings('give_shipping') ) {
  		?>
  		<th class="line_cost sortable no_ipad no_mob" data-sort="float"><?php _e( 'Shipping', 'wc-frontend-manager' ); ?></th>
  		<?php
  	}
  	
  	if ( $WCMp->vendor_caps->vendor_payment_settings('give_tax') ) {
  		?>
  		<th class="line_cost sortable no_ipad no_mob"><?php _e( 'Tax', 'wc-frontend-manager' ); ?></th>
  		<th class="line_cost sortable no_ipad no_mob"><?php _e( 'Shipping Tax', 'wc-frontend-manager' ); ?></th>
  		<?php
  	}
  	if( apply_filters( 'wcfm_is_allow_total', true ) ) {
  	?>
  	<th class="line_cost total_cost sortable no_ipad no_mob"><?php _e( 'Total', 'wc-frontend-manager' ); ?></th>
  	<?php
  	}
  }
  
  // wcmarketplace after Order total Line item
  function wcmarketplace_after_line_total( $item, $order ) {
  	global $WCFM, $wpdb, $WCMp;
  	
  	$order_currency = $order->get_currency();
  	$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
  	
  	$qty = ( isset( $item['qty'] ) ? esc_html( $item['qty'] ) : '1' );
  	$line_total = $item->get_total();
  	
  	if( $item->get_product_id() ) {
			$product_id = $item->get_product_id();
			$variation_id = $item->get_variation_id();
		} else {
			$product_id = wc_get_order_item_meta( $item->get_id(), '_product_id', true );
			$variation_id = wc_get_order_item_meta( $item->get_id(), '_variation_id', true );
		}
		
		$sql = "
			SELECT commission_id, commission_amount AS line_total, shipping AS total_shipping, tax, shipping_tax_amount 
			FROM {$wpdb->prefix}wcmp_vendor_orders
			WHERE (product_id = " . $product_id . " OR product_id = " . $variation_id . ")
			AND   order_id = " . $order->get_id() . "
			AND   `vendor_id` = " . $this->vendor_id;
		$order_line_due = $wpdb->get_results( $sql );
		
		if( !empty( $order_line_due ) ) {
		?>
		  <?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
				<td class="line_cost" width="1%">
					<div class="view">
						<?php 
						if( $order_line_due[0]->commission_id ) {
							if( $admin_fee_mode ) {
								echo wc_price( ( $line_total - $order_line_due[0]->line_total ), array( 'currency' => $order_currency ) );
							} else {
								echo wc_price( $order_line_due[0]->line_total, array( 'currency' => $order_currency ) );
							}
						 } else { _e( 'N/A', 'wc-frontend-manager' ); }
						 if( $order_line_due[0]->total_shipping != 'NAN' ) $line_total += $order_line_due[0]->total_shipping;
						 if( $order_line_due[0]->tax != 'NAN' ) $line_total += $order_line_due[0]->tax; 
						 if( $order_line_due[0]->shipping_tax_amount != 'NAN' ) $line_total += $order_line_due[0]->shipping_tax_amount;
						?>
					</div>
				</td>
			<?php } ?>
			
			<?php if ( $WCMp->vendor_caps->vendor_payment_settings('give_shipping') ) { ?>
			<td class="line_cost no_ipad no_mob" width="1%">
				<div class="view"><?php echo ( $order_line_due[0]->total_shipping == 'NAN' ) ? wc_price( 0, array( 'currency' => $order_currency ) ) : wc_price( $order_line_due[0]->total_shipping, array( 'currency' => $order_currency ) ); ?></div>
			</td>
			<?php } ?>
			<?php if ( $WCMp->vendor_caps->vendor_payment_settings('give_tax') ) { ?>
			<td class="line_cost no_ipad no_mob">
				<div class="view"><?php echo ( $order_line_due[0]->tax == 'NAN' ) ? wc_price( 0, array( 'currency' => $order_currency ) ) : wc_price( $order_line_due[0]->tax, array( 'currency' => $order_currency ) ); ?></div>
			</td>
			<td class="line_cost shipping_tax_cost no_ipad no_mob">
				<div class="view"><?php echo ( $order_line_due[0]->shipping_tax_amount == 'NAN' ) ? wc_price( 0, array( 'currency' => $order_currency ) ) : wc_price( $order_line_due[0]->shipping_tax_amount, array( 'currency' => $order_currency ) ); ?></div>
			</td>
			<?php } ?>
		<?php
		} else {
			?>
			<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
				<td class="line_cost" width="1%">
					<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			<?php } ?>
			<?php if ( $WCMp->vendor_caps->vendor_payment_settings('give_shipping') ) { ?>
			<td class="line_cost" width="1%">
				<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
			</td>
			<?php } ?>
			<?php if ( $WCMp->vendor_caps->vendor_payment_settings('give_tax') ) { ?>
			<td class="line_cost" width="1%">
				<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
			</td>
			<td class="line_cost" width="1%">
				<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
			</td>
			<td class="line_cost" width="1%"></td>
			<?php } ?>
			<?php
		}
		if( apply_filters( 'wcfm_is_allow_total', true ) ) {
		?>
		<td class="line_cost total_cost no_ipad no_mob"><?php echo wc_price( $line_total, array( 'currency' => $order_currency ) ); ?></td>
		<?php
		}
  }
  
  // WC marketplace Order Total Commission
  function wcmarketplace_order_total_commission( $order_id ) {
  	global $WCFM, $wpdb, $WCMp;
  	
  	$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
  	$gross_sale_order = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $this->vendor_id, '', '', $order_id );
  	$order = wc_get_order( $order_id );
  	$order_currency = $order->get_currency();
  	
  	$sql = "
  	SELECT SUM(commission_amount) as line_total,
	     SUM(shipping) as shipping,
       SUM(tax) as tax,
       SUM(	shipping_tax_amount) as shipping_tax_amount,
       commission_id
       FROM {$wpdb->prefix}wcmp_vendor_orders
       WHERE order_id = " . $order_id . "
       AND `vendor_id` = " . $this->vendor_id;
    $order_due = $wpdb->get_results( $sql );
    $total = 0;
    
    if( $order_due[0]->commission_id ) {
			$total = $order_due[0]->line_total; 
			if ( $WCMp->vendor_caps->vendor_payment_settings('give_shipping') ) {
				$total += ( $order_due[0]->shipping == 'NAN' ) ? 0 : $order_due[0]->shipping; 
			}
			if ( $WCMp->vendor_caps->vendor_payment_settings('give_tax') ) {
				$total += ( $order_due[0]->tax == 'NAN' ) ? 0 : $order_due[0]->tax;
				$total += ( $order_due[0]->shipping_tax_amount == 'NAN' ) ? 0 : $order_due[0]->shipping_tax_amount;
			}
		}
		?>
		<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
			<?php if( !$admin_fee_mode ) { ?>
				<tr>
					<td class="label"><?php _e( 'Line Commission', 'wc-frontend-manager' ); ?>:</td>
					<td>
						
					</td>
					<td class="total">
						<div class="view"><?php if( $order_due[0]->commission_id ) { echo wc_price( $order_due[0]->line_total, array( 'currency' => $order_currency ) ); } else { _e( 'N/A', 'wc-frontend-manager' ); } ?></div>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
		<?php if ( $WCMp->vendor_caps->vendor_payment_settings('give_shipping') ) { ?>
		<tr>
			<td class="label"><?php _e( 'Shipping', 'wc-frontend-manager' ); ?>:</td>
			<td>
				
			</td>
			<td class="total">
				<div class="view"><?php echo ( $order_due[0]->shipping == 'NAN' ) ? wc_price( 0, array( 'currency' => $order_currency ) ) : wc_price( $order_due[0]->shipping, array( 'currency' => $order_currency ) ); ?></div>
			</td>
		</tr>
		<?php } ?>
		<?php if ( $WCMp->vendor_caps->vendor_payment_settings('give_tax') ) { ?>
		<tr>
			<td class="label"><?php _e( 'Tax', 'wc-frontend-manager' ); ?>:</td>
			<td>
				
			</td>
			<td class="total">
				<div class="view"><?php echo ( $order_due[0]->tax == 'NAN' ) ? wc_price( 0, array( 'currency' => $order_currency ) ) : wc_price( $order_due[0]->tax, array( 'currency' => $order_currency ) ); ?></div>
			</td>
		</tr>
		<tr>
			<td class="label"><?php _e( 'Shipping Tax', 'wc-frontend-manager' ); ?>:</td>
			<td>
				
			</td>
			<td class="total">
				<div class="view"><?php echo ( $order_due[0]->shipping_tax_amount == 'NAN' ) ? wc_price( 0, array( 'currency' => $order_currency ) ) : wc_price( $order_due[0]->shipping_tax_amount, array( 'currency' => $order_currency ) ); ?></div>
			</td>
		</tr>
		<?php } ?>
		<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
			<?php
			if( $admin_fee_mode ) {
				?>
				<tr>
					<td class="label"><?php _e( 'Total Fees', 'wc-frontend-manager' ); ?>:</td>
					<td>
						
					</td>
					<td class="total">
						<div class="view">
							<?php 
							if( $order_due[0]->commission_id ) {
								echo wc_price( ($gross_sale_order - $total), array( 'currency' => $order_currency ) );
							} else { _e( 'N/A', 'wc-frontend-manager' ); }
							?>
						</div>
					</td>
				</tr>
				<?php
			} else {
				?>
				<tr>
					<td class="label"><?php _e( 'Total Earning', 'wc-frontend-manager' ); ?>:</td>
					<td>
						
					</td>
					<td class="total">
						<div class="view"><?php if( $order_due[0]->commission_id ) { echo wc_price( $total, array( 'currency' => $order_currency ) ); } else { _e( 'N/A', 'wc-frontend-manager' ); } ?></div>
					</td>
				</tr>
			<?php
			}
		}
		if( apply_filters( 'wcfm_is_allow_gross_total', true ) ) {
			?>
			<tr class="total_cost">
				<td class="label"><?php _e( 'Gross Total', 'wc-frontend-manager' ); ?>:</td>
				<td>
					
				</td>
				<td class="total">
					<div class="view">
						<?php 
						echo wc_price( $gross_sale_order, array( 'currency' => $order_currency ) ); 
						?>
					</div>
				</td>
			</tr>
			<?php
		}
  }
  
  // CSV Export URL
  function wcmarketplace_generate_csv_url( $url, $order_id ) {
  	$url = admin_url('admin-ajax.php?action=wcmp_vendor_csv_download_per_order&order_id=' . $order_id . '&nonce=' . wp_create_nonce('wcmp_vendor_csv_download_per_order'));
  	return $url;
  }
  
  // Report Vendor Filter
  function wcmarketplace_report_out_of_stock_query_from( $query_from, $stock ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	$query_from = "FROM {$wpdb->posts} as posts
			INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
			WHERE 1=1
			AND posts.post_type IN ( 'product', 'product_variation' )
			AND posts.post_status = 'publish'
			AND posts.post_author = {$user_id}
			AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
			AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
		";
		
		return $query_from;
  }
  
  // Report Order Data Status
  function wcmarketplace_reports_order_statuses( $order_status ) {
  	$order_status = array( 'completed', 'processing' );
  	return $order_status;
  }
  
  // WCVendor dashboard top seller query
  function wcmarketplace_dashboard_status_widget_top_seller_query( $query ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
    $products = $this->wcmarketplace_get_vendor_products();
    if( empty($products) ) return array(0);
		$query['where'] .= "AND order_item_meta_2.meta_value in (" . implode( ',', $products ) . ")";
  	
  	return $query;
  }
  
  // Report Data Filter as per Vendor
  function wcmarketplace_reports_get_order_report_data( $result ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->wcmarketplace_get_vendor_products();
  	
  	if( !empty( $result ) && is_array( $result ) ) {
  		foreach( $result as $result_key => $result_val ) {
  			if( !in_array( $result_val->product_id, $products ) ) unset( $result[$result_key] );
  		}
  	}
  	
  	return $result;
  }
  
  // Return vendor product ids
  function wcmarketplace_get_vendor_products( $vendor_id = 0 ) {
  	if( !$vendor_id ) $vendor_id = $this->vendor_id;
  	
  	$vendor = get_wcmp_vendor($vendor_id);
    $vendor_products = $vendor->get_products();
  	$products = array();
		foreach ($vendor_products as $vendor_product) {
			$products[] = $vendor_product->ID;
			if( $vendor_product->post_type == 'product_variation' ) $products[] = $vendor_product->post_parent;
		}
  	
		return $products;
  }
  
  // Showing WCMp Knowledgebases
  function wcmarketplace_wcfm_knowledgebase() {
  	global $WCFM, $WCMp;
  	
  	$args = array(
							'posts_per_page'   => -1,
							'offset'           => 0,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => 'wcmp_university',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array('publish'),
							'suppress_filters' => 0 
						);
		$wcmp_knowledgebases = get_posts( $args );
		
		if( !empty( $wcmp_knowledgebases ) ) {
  	  foreach( $wcmp_knowledgebases as $wcmp_knowledgebase ) {
  	  	?>
  	  	<div class="page_collapsible" id="wcfm_knowledgebase_listing_head-<?php echo $wcmp_knowledgebase->ID; ?>">
					<label class="wcfmfa fa-bookmark"></label>
					<?php echo $wcmp_knowledgebase->post_title; ?><span></span>
				</div>
  	  	<div class="wcfm-container">
					<div id="wcfm_knowledgebase_listing_expander-<?php echo $wcmp_knowledgebase->ID; ?>" class="wcfm_knowledgebase wcfm-content">
						<?php echo $wcmp_knowledgebase->post_content; ?>
					</div>
				</div>
				<div class="wcfm-clearfix"></div><br />
  	  	<?php
  	  }
		}
  }
  
  /**
	 * Single product multi-seller auto sugges 
	 *
	 * @since 3.3.7
	 *
	 * @return void
	 */
  function wcmarketplace_auto_suggesion_product() {
		global $WCFM, $WCMp, $wpdb;
		$searchstr = $_POST['protitle'];
		$querystr = "select DISTINCT post_title, ID from {$wpdb->prefix}posts where post_title like '{$searchstr}%' and post_status = 'publish' and post_type = 'product' GROUP BY post_title order by post_title  LIMIT 0,10";
		$results = $wpdb->get_results($querystr);
		if ( count( $results ) > 0 ) {
			echo "<ul>";
			foreach ($results as $result) {
				echo '<li data-element="' . $result->ID . '"><a class="wcfm_product_multi_seller_associate" href="#" data-proid="' . $result->ID . '">' . $result->post_title . '</a></li>';
			}
			echo "</ul>";
		}
		die;
	}
	
	/**
	 * Single product multi-seller association
	 */
	public function wcfm_product_multi_seller_associate() {
		global $WCFM, $WCFMu, $_POST;
		
		if( !class_exists( 'WC_Admin_Duplicate_Product' ) ) {
			include( WC_ABSPATH . 'includes/admin/class-wc-admin-duplicate-product.php' );
		}
		$WC_Admin_Duplicate_Product = new WC_Admin_Duplicate_Product();
		
		if ( empty( $_POST['proid'] ) ) {
			echo '{"status": false, "message": "' .  __( 'No product to duplicate has been supplied!', 'woocommerce' ) . '"}';
		}

		$product_id = isset( $_POST['proid'] ) ? absint( $_POST['proid'] ) : '';

		//check_admin_referer( 'woocommerce-duplicate-product_' . $product_id );

		$product = wc_get_product( $product_id );

		if ( false === $product ) {
			/* translators: %s: product id */
			echo '{"status": false, "message": "' . sprintf( __( 'Product creation failed, could not find original product: %s', 'woocommerce' ), $product_id ) . '" }';
		}

		$duplicate = $WC_Admin_Duplicate_Product->product_duplicate( $product );
		
		// Update new product title
		$new_post = array(
			'ID'           => $duplicate->get_id(),
			'post_title'   => get_the_title( $product_id ),
		);
		wp_update_post( $new_post );
		
		update_post_meta( $duplicate->get_id(), '_wcfm_product_views', 0 );
		
		//update_post_meta( $duplicate->get_id(), '_wcmp_parent_product_id', $product->get_id() );
		$duplicate->set_parent_id($product->get_id());
    update_post_meta($duplicate->get_id(), '_wcmp_child_product', true);
    $duplicate->save();

		// Hook rename to match other woocommerce_product_* hooks, and to move away from depending on a response from the wp_posts table.
		do_action( 'woocommerce_product_duplicate', $duplicate, $product );
		do_action( 'after_wcfm_product_duplicate', $duplicate->get_id(), $product );

		// Redirect to the edit screen for the new draft page
		echo '{"status": true, "redirect": "' . get_wcfm_edit_product_url( $duplicate->get_id() ) . '", "id": "' . $duplicate->get_id() . '"}';
		
		die;
	}
}