<?php

/**
 * WCFM plugin core
 *
 * WCFM Multivendor Marketplace Support
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   5.0.0
 */
 
class WCFM_Marketplace {
	
	public $vendor_id;
	
	public function __construct() {
    global $WCFM;
    
		$this->vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
		// Store Identity
		add_filter( 'wcfm_store_logo', array( &$this, 'wcfmmp_store_logo' ) );
		add_filter( 'wcfm_store_name', array( &$this, 'wcfmmp_store_name' ) );
		
		// Allow Vendor user to manage product from catalog
		add_filter( 'wcfm_allwoed_user_roles', array( &$this, 'allow_wcfmmp_vendor_role' ) );
		add_filter( 'wcfm_allwoed_vendor_user_roles', array( &$this, 'allow_wcfmmp_vendor_role' ) );
		
		// Filter Vendor Products
		add_filter( 'wcfm_products_args', array( &$this, 'wcfmmp_products_args' ) );
		add_filter( 'get_booking_products_args', array( $this, 'wcfmmp_products_args' ) );
		add_filter( 'get_appointment_products_args', array( $this, 'wcfmmp_products_args' ) );
		add_filter( 'wpjmp_job_form_products_args', array( &$this, 'wcfmmp_products_args' ) );
		add_filter( 'wpjmp_admin_job_form_products_args', array( &$this, 'wcfmmp_products_args' ) );
		
		// Listing Filter for specific vendor
		add_filter( 'wcfm_articles_args', array( &$this, 'wcfmmp_listing_args' ) );
		add_filter( 'wcfm_listing_args', array( $this, 'wcfmmp_listing_args' ), 20 );
		
		// Customers args
		if( apply_filters( 'wcfm_is_allow_order_customers_to_vendors', true ) ) {
			add_filter( 'wcfm_get_customers_args', array( &$this, 'wcfmmp_filter_customers' ), 20 );
		}
		
		// Orders Filter
		add_action( 'before_wcfm_orders', array( &$this, 'wcfmmp_orders_filter' ) );
		
		// Booking Filter
		add_filter( 'wcfm_wcb_include_bookings', array( &$this, 'wcfmmp_wcb_include_bookings' ) );
		
		// Tych Booking Filter
		add_filter( 'wcfm_wcb_include_tych_bookings', array( &$this, 'wcfmmp_wcb_include_tych_bookings' ) );
		
		// Manage Vendor Product Permissions
		add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfmmp_product_manage_vendor_association' ), 10, 2 );
		add_action( 'gmw_location_form_default_location', array( &$this, 'wcfmmp_geo_locator_default_address' ), 10, 2 );
		
		// Manage Vendor Product Export Permissions - 2.4.2
		add_filter( "woocommerce_product_export_product_query_args", array( &$this, 'wcfmmp_product_export_query_args' ), 100 );
		add_filter( 'woocommerce_product_export_row_data', array( &$this, 'wcfmmp_product_export_row_data' ), 100, 2 );
																																																											
		// Filter Vendor Coupons
		add_filter( 'wcfm_coupons_args', array( &$this, 'wcfmmp_coupons_args' ) );
		
		// Manage Vendor Coupon Permission
		add_filter( 'wcfm_coupon_types', array( &$this, 'wcfmmp_coupon_types' ) );
		
		// Manage Order Details Permission
		add_filter( 'wcfm_allow_order_details', array( &$this, 'wcfmmp_is_allow_order_details' ) );
		add_filter( 'wcfm_valid_line_items', array( &$this, 'wcfmmp_valid_line_items' ), 10, 2 );
		add_filter( 'wcfm_valid_shipping_items', array( &$this, 'wcfmmp_valid_shipping_items' ), 10, 2 );
		add_filter( 'wcfm_allow_order_customer_details', array( &$this, 'wcfmmp_is_allow_order_customer_details' ) );
		add_filter( 'wcfm_order_details_shipping_line_item', array( &$this, 'wcfmmp_is_allow_order_details_shipping_line_item' ) );
		add_filter( 'wcfm_order_details_tax_line_item', array( &$this, 'wcfmmp_is_allow_order_details_tax_line_item' ) );
		add_filter( 'wcfm_order_details_line_total_head', array( &$this, 'wcfmmp_is_allow_order_details_line_total_head' ) );
		add_filter( 'wcfm_order_details_line_total', array( &$this, 'wcfmmp_is_allow_order_details_line_total' ) );
		add_filter( 'wcfm_order_details_tax_total', array( &$this, 'wcfmmp_is_allow_order_details_tax_total' ) );
		add_filter( 'wcfm_order_details_fee_line_item', array( &$this, 'wcfmmp_is_allow_order_details_fee_line_item' ) );
		add_filter( 'wcfm_order_details_refund_line_item', array( &$this, 'wcfmmp_is_allow_order_details_refund_line_item' ) );
		add_filter( 'wcfm_order_details_coupon_line_item', array( &$this, 'wcfmmp_is_allow_order_details_coupon_line_item' ) );
		add_filter( 'wcfm_order_details_total', array( &$this, 'wcfmmp_is_allow_wcfm_order_details_total' ) );
		add_filter( 'wcfm_order_details_shipping_total', array( &$this, 'wcfmmp_is_allow_wcfm_order_details_total' ) );
		add_filter( 'wcfm_order_details_refund_total', array( &$this, 'wcfmmp_is_allow_wcfm_order_details_total' ) );
		add_action( 'wcfm_order_details_after_line_total_head', array( &$this, 'wcfmmp_after_line_total_head' ) );
		add_action( 'wcfm_after_order_details_line_total', array( &$this, 'wcfmmp_after_line_total' ), 10, 2 );
		add_action( 'wcfm_after_order_details_shipping_total', array( &$this, 'wcfmmp_after_shipping_total' ), 10, 2 );
		add_action( 'wcfm_after_order_details_refund_total', array( &$this, 'wcfmmp_after_refund_total' ), 10, 2 );
		add_action ( 'wcfm_order_totals_after_total', array( &$this, 'wcfmmp_order_total_commission' ) );
		//add_filter( 'wcfm_generate_csv_url', array( &$this, 'wcfmmp_generate_csv_url' ), 10, 2 );
		
		// Report Filter
		add_filter( 'wcfm_report_out_of_stock_query_from', array( &$this, 'wcfmmp_report_out_of_stock_query_from' ), 100, 2 );
		add_filter( 'woocommerce_reports_order_statuses', array( &$this, 'wcfmmp_reports_order_statuses' ) );
		add_filter( 'woocommerce_dashboard_status_widget_top_seller_query', array( &$this, 'wcfmmp_dashboard_status_widget_top_seller_query'), 100, 2 );
		//add_filter( 'woocommerce_reports_get_order_report_data', array( &$this, 'wcfmmp_reports_get_order_report_data'), 100 );
			
  }
  
  // WCFM Marketplace Store Logo
  function wcfmmp_store_logo( $store_logo ) {
  	$user_id = $this->vendor_id;
  	$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
  	$gravatar       = isset( $vendor_data['gravatar'] ) ? absint( $vendor_data['gravatar'] ) : 0;
  	$gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : '';

		if ( !empty( $gravatar_url ) ) {
			$store_logo = $gravatar_url;
			$shop_link       = wcfmmp_get_store_url( $user_id );
			if( $shop_link ) {
				$store_logo = '<a class="wcfm_store_logo_icon" href="' . $shop_link . '" target="_blank"><img src="' . $store_logo . '" alt="Store Logo" /></a>';
			}
		}
  	return $store_logo;
  }
  
  // WCFM Marketplace Store Name
  function wcfmmp_store_name( $store_name ) {
  	$user_id = $this->vendor_id;
  	
  	$store_open_by = apply_filters( 'wcfm_shop_permalink_open_by', 'target="_blank"', $user_id );
  	
  	//$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
  	$shop_name     = wcfm_get_option( 'wcfm_my_store_label', $store_name );  //isset( $vendor_data['store_name'] ) ? esc_attr( $vendor_data['store_name'] ) : '';
  	if( $shop_name ) $store_name = $shop_name;
  	$shop_link       = wcfmmp_get_store_url( $user_id );
  	if( $shop_name ) { $store_name = '<a ' . $store_open_by . ' href="' . apply_filters('wcfmmp_vendor_shop_permalink', $shop_link) . '">' . $shop_name . '</a>'; }
  	else { $store_name = '<a ' . $store_open_by . ' href="' . apply_filters('wcfmmp_vendor_shop_permalink', $shop_link) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
  	return $store_name;
  }
  
  function allow_wcfmmp_vendor_role( $allowed_roles ) {
  	if( wcfm_is_vendor() ) $allowed_roles[] = 'wcfm_vendor';
  	return $allowed_roles;
  }
  
  function wcfmmp_products_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // WCFM Marketplace Listing args
	function wcfmmp_listing_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  /**
   * WCFM Marketplace filter customers
   */
  function wcfmmp_filter_customers( $args ) {
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
  	$sql = 'SELECT customer_id FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `vendor_id` = {$this->vendor_id}";
		$wcfm_orders_array = $wpdb->get_results( $sql );
		if(!empty($wcfm_orders_array)) {
			foreach($wcfm_orders_array as $wcfm_orders_single) {
				if ( $wcfm_orders_single->customer_id ) {
					$vendor_customers[$wcfm_orders_single->customer_id] = $wcfm_orders_single->customer_id;
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
  
  // Update Comment User as Vendor
  public function wcfm_update_comment_vendor( $commentdata, $order ) {
  	global $WCFM;
		$vendor_id = $this->vendor_id;

		$commentdata[ 'user_id' ]              = $vendor_id;
		$commentdata[ 'comment_author' ]       = wcfm_get_vendor_store_name( absint($vendor_id) );
		$commentdata[ 'comment_author_email' ] = wcfm_get_vendor_store_email_by_vendor( absint($vendor_id) );

		return $commentdata;
	}
  
  // Orders Filter
  function wcfmmp_orders_filter() {
  	global $WCFM, $WCFMu, $wpdb, $wp_locale;
  	?>
		<div class="wcfm_orders_filter_wrap wcfm_filters_wrap">
			<?php 
			// Date Range Filter
			$WCFM->library->wcfm_date_range_picker_field(); 
			
			// Product Filter
			$WCFM->wcfm_fields->wcfm_generate_form_field( array( "order_product" => array( 'type' => 'select', 'attributes' => array( 'style' => 'width: 150px;' ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'options' => array() ) ) );
			?>
			
			
			
			<select name="commission-status" id="commission-status" style="width: 150px;">
				<option value=''><?php esc_html_e( 'Show all', 'wc-frontend-manager' ); ?></option>
				<option value="pending"><?php esc_html_e( 'Unpaid', 'wc-frontend-manager' ); ?></option>
				<option value="requested"><?php esc_html_e( 'Requested', 'wc-frontend-manager' ); ?></option>
				<option value="completed"><?php esc_html_e( 'Paid', 'wc-frontend-manager' ); ?></option>
				<option value="cancelled"><?php esc_html_e( 'Cancelled', 'wc-frontend-manager' ); ?></option>
			</select>
			<?php do_action( 'wcfm_after_orders_filter_wrap' ); ?>
		</div>
  	<?php
  }
  
  /**
   * WC Vendors Bookings
   */
  function wcfmmp_wcb_include_bookings( ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$vendor_products = $this->wcfmmp_get_vendor_products( $this->vendor_id );
		
		if( empty($vendor_products) ) return array(0);
		
  	$query = "SELECT ID FROM {$wpdb->posts} as posts
							INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
							WHERE 1=1
							AND posts.post_type IN ( 'wc_booking' )
							AND postmeta.meta_key = '_booking_product_id' AND postmeta.meta_value in (" . implode(',', $vendor_products) . ")";
		
		$vendor_bookings = $wpdb->get_results($query);
		if( empty($vendor_bookings) ) return array(0);
		$vendor_bookings_arr = array();
		foreach( $vendor_bookings as $vendor_booking ) {
			$vendor_bookings_arr[] = $vendor_booking->ID;
		}
		if( !empty($vendor_bookings_arr) ) return $vendor_bookings_arr;
		return array(0);
  }
  
  /**
   * WC Tych Bookings
   */
  function wcfmmp_wcb_include_tych_bookings( ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$vendor_products = $this->wcfmmp_get_vendor_products( $this->vendor_id );
		
		if( empty($vendor_products) ) return array(0);
		
  	$query = "SELECT ID FROM {$wpdb->posts} as posts
							INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
							WHERE 1=1
							AND posts.post_type IN ( 'bkap_booking' )
							AND postmeta.meta_key = '_bkap_product_id' AND postmeta.meta_value in (" . implode(',', $vendor_products) . ")";
		
		$vendor_bookings = $wpdb->get_results($query);
		if( empty($vendor_bookings) ) return array(0);
		$vendor_bookings_arr = array();
		foreach( $vendor_bookings as $vendor_booking ) {
			$vendor_bookings_arr[] = $vendor_booking->ID;
		}
		if( !empty($vendor_bookings_arr) ) return $vendor_bookings_arr;
		return array(0);
  }
  
  // Product Vendor association on Product save
  function wcfmmp_product_manage_vendor_association( $new_product_id, $wcfm_products_manage_form_data ) {
  	global $WCFM, $WCMp;
  	
		// Admin Message for Pending Review
		$product_status = get_post_status( $new_product_id );
		if( $product_status == 'pending' ) {
			$WCFM->wcfm_notification->wcfm_admin_notification_product_review( $this->vendor_id, $new_product_id );
		} else {
			$WCFM->wcfm_notification->wcfm_admin_notification_new_product( $this->vendor_id, $new_product_id );
		}
  }
  
  // Geo Locator default address- 3.2.8
  function wcfmmp_geo_locator_default_address( $gmw_saved_location, $args ) {
  	global $WCFM;
  	
  	if ( empty( $gmw_saved_location ) ) {
			
			$vendor_data = get_user_meta( $this->vendor_id, 'wcfmmp_profile_settings', true );
			$address         = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
			
			$street_1 = isset( $vendor_data['address']['street_1'] ) ? $vendor_data['address']['street_1'] : '';
			$street_2 = isset( $vendor_data['address']['street_2'] ) ? $vendor_data['address']['street_2'] : '';
			$city     = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
			$state    = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
			$zip      = isset( $vendor_data['address']['zip'] ) ? $vendor_data['address']['zip'] : '';
			$country  = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
			
			$map_location   = isset( $vendor_data['store_location'] ) ? esc_attr( $vendor_data['store_location'] ) : '';
			$map_address    = isset( $vendor_data['find_address'] ) ? esc_attr( $vendor_data['find_address'] ) : '';
			
			$store_lat    = isset( $vendor_data['store_lat'] ) ? esc_attr( $vendor_data['store_lat'] ) : 0;
			$store_lng    = isset( $vendor_data['store_lng'] ) ? esc_attr( $vendor_data['store_lng'] ) : 0;
			
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
			if( $state && isset( $states[$country] ) && is_array( $states[$country] ) && isset($states[$country][$state]) ) {
				$state_name = ($states[$country][$state]) ? $states[$country][$state] : '';
			}
			
			$gmw_saved_location = array(
				  'ID'            	=> 0,
				  'latitude'      	=> $store_lat,
					'longitude'     	=> $store_lng,
					'street_number' 	=> $street_1,
					'street_name'   	=> $street_2,
					'street'        	=> $street_1,
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
  
  // Product Export Query args
  function wcfmmp_product_export_query_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // Product Export Data Filter
  function wcfmmp_product_export_row_data( $row, $product ) {
  	global $WCFM;
  	
  	if( $product->get_type() == 'variation' ) return $row;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->wcfmmp_get_vendor_products();
		
		if( !in_array( $product->get_ID(), $products ) ) return array();
		
		return $row;
  }
  
  // Coupons Args
  function wcfmmp_coupons_args( $args ) {
  	if( wcfm_is_vendor() ) $args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // Coupon Types
  function wcfmmp_coupon_types( $types ) {
  	$wcmp_coupon_types = array( 'percent', 'fixed_product' );
  	foreach( $types as $type => $label ) 
  		if( !in_array( $type, $wcmp_coupon_types ) ) unset( $types[$type] );
  	return $types;
  } 
  
  // Order Status details
  function wcfmmp_is_allow_order_details( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Filter Order Details Line Items as Per Vendor
  function wcfmmp_valid_line_items( $items, $order_id ) {
  	global $WCFM, $wpdb;
  	
  	$sql = "SELECT `product_id`, `item_id` FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE `vendor_id` = {$this->vendor_id} AND `order_id` = {$order_id}";
  	$valid_products = $wpdb->get_results($sql);
  	$valid_items = array();
  	if( !empty($valid_products) ) {
  		foreach( $valid_products as $valid_product ) {
  			$valid_items[] = $valid_product->item_id;
  			$valid_items[] = $valid_product->product_id;
  		}
  	}
  	
  	$valid = array();
  	foreach ($items as $key => $value) {
			if ( in_array( $value->get_variation_id(), $valid_items ) || in_array( $value->get_product_id(), $valid_items ) || in_array( $value->get_id(), $valid_items ) ) {
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
  
  // Filter Shipping Line Items as Per Vendor
  function wcfmmp_valid_shipping_items( $shipping_items, $order_id ) {
  	global $WCFM, $WCFMmp, $wpdb;
  	
  	$vendor_sipping_items = array();
  	
  	foreach ($shipping_items as $shipping_item_id => $shipping_item) {
			$order_item_shipping = new WC_Order_Item_Shipping($shipping_item_id);
			$shipping_vendor_id = $order_item_shipping->get_meta('vendor_id', true);
			if( $shipping_vendor_id && ( $shipping_vendor_id == $this->vendor_id ) ) {
				$vendor_sipping_items[$shipping_item_id] = $shipping_item;
			}
		}
		
		return $vendor_sipping_items;
  }
  
  // Order Customer Details
  function wcfmmp_is_allow_order_customer_details( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Shipping Line Item
  function wcfmmp_is_allow_order_details_shipping_line_item( $allow ) {
  	global $WCFM, $WCFMmp;
  	if ( !$WCFMmp->wcfmmp_vendor->is_vendor_get_shipping( $this->vendor_id ) ) {
  		$allow = false;
  	}
  	return $allow;
  }
  
  // Order Details Tax Line Item
  function wcfmmp_is_allow_order_details_tax_line_item( $allow ) {
  	global $WCFM, $WCFMmp;
  	if ( !$WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $this->vendor_id ) ) {
  		$allow = false;
  	}
  	return $allow;
  }
  
  // Order Total Line Item Head
  function wcfmmp_is_allow_order_details_line_total_head( $allow ) {
  	if( !apply_filters( 'wcfm_is_allow_total', true ) ) {
  		$allow = false;
  	}
  	return $allow;
  }
  
  // Order Total Line Item
  function wcfmmp_is_allow_order_details_line_total( $allow ) {
  	if( !apply_filters( 'wcfm_is_allow_total', true ) ) {
  		$allow = false;
  	}
  	return $allow;
  }
  
  // Order Details Tax Total
  function wcfmmp_is_allow_order_details_tax_total( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Fee Line Item
  function wcfmmp_is_allow_order_details_fee_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Coupon Line Item
  function wcfmmp_is_allow_order_details_coupon_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Refunded Line Item
  function wcfmmp_is_allow_order_details_refund_line_item( $allow ) {
  	$allow = true;
  	return $allow;
  }
  
  // Order Details Total
  function wcfmmp_is_allow_wcfm_order_details_total( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // WCFMmp After Order Total Line Head
  function wcfmmp_after_line_total_head( $order ) {
  	global $WCFM, $WCFMmp;
  	
  	if( wcfm_vendor_has_capability( $this->vendor_id, 'view_commission' ) ) {
			$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
			if( $admin_fee_mode ) {
			?>
				<th class="line_cost"><?php _e( 'Fees', 'wc-frontend-manager' ); ?></th>
			<?php } else { ?>
				<th class="line_cost"><?php _e( 'Earning', 'wc-frontend-manager' ); ?></th>
			<?php
			}
		}
  }
  
  // WCFMmp after Order total Line item
  function wcfmmp_after_line_total( $item, $order ) {
  	global $WCFM, $wpdb, $WCFMmp;
  	
  	if( !wcfm_vendor_has_capability( $this->vendor_id, 'view_commission' ) ) return;
  	
  	$order_currency = $order->get_currency();
  	$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
  	
  	$qty = ( isset( $item['qty'] ) ? esc_html( $item['qty'] ) : '1' );
  	
  	if ( $WCFMmp->wcfmmp_vendor->is_vendor_deduct_discount( $this->vendor_id, $order->get_id() ) ) {
  		$line_total = $item->get_total();
  	} else {
  		$line_total = $item->get_subtotal();
  	}
  	
  	if( $item->get_product_id() ) {
			$product_id = $item->get_product_id();
			$variation_id = $item->get_variation_id();
		} else {
			$product_id = wc_get_order_item_meta( $item->get_id(), '_product_id', true );
			$variation_id = wc_get_order_item_meta( $item->get_id(), '_variation_id', true );
		}
		
		$sql = "
			SELECT item_id, is_refunded, commission_amount AS line_total, shipping AS total_shipping, tax, shipping_tax_amount 
			FROM {$wpdb->prefix}wcfm_marketplace_orders
			WHERE (product_id = " . $product_id . " OR variation_id = " . $variation_id . ")
			AND   order_id    = " . $order->get_id() . "
			AND   item_id     = " . $item->get_id() . "
			AND   `vendor_id` = " . $this->vendor_id;
		$order_line_due = $wpdb->get_results( $sql );
		
		if( !empty( $order_line_due ) && !$order_line_due[0]->is_refunded ) {
			if ( $get_shipping = $WCFMmp->wcfmmp_vendor->is_vendor_get_shipping( $this->vendor_id ) ) {
				//$line_total += $order_line_due[0]->total_shipping;
			}
			if ( $WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $this->vendor_id ) ) {
				$line_total += $order_line_due[0]->tax; 
				$order_line_due[0]->line_total += $order_line_due[0]->tax;
				if( $get_shipping ) {
					//$line_total += $order_line_due[0]->shipping_tax_amount;
				}
			}
			?>
			<td class="line_cost">
				<div class="view">
				  <?php 
					if( $admin_fee_mode ) {
						$refunded = $order->get_total_refunded_for_item( $item->get_id() );
						echo wc_price( ( $line_total - $refunded - $order_line_due[0]->line_total ), array( 'currency' => $order_currency ) );
					} else {
						echo wc_price( $order_line_due[0]->line_total, array( 'currency' => $order_currency ) );
					}
				  ?>
				</div>
			</td>
		<?php
		} else {
			?>
			<td class="line_cost">
				<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
			</td>
			<?php
		}
  }
  
  function wcfmmp_after_shipping_total( $item, $order ) {
  	global $WCFM, $wpdb, $WCFMmp;
  	
  	if( !wcfm_vendor_has_capability( $this->vendor_id, 'view_commission' ) ) return;
  	
  	$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
  	
  	if ( !$admin_fee_mode && ($get_shipping = $WCFMmp->wcfmmp_vendor->is_vendor_get_shipping( $this->vendor_id ) ) ) {
  		?>
			<td class="line_cost">
			  <?php 
			  if( apply_filters( 'wcfmmp_is_allow_shipping_from_order', true ) && !apply_filters( 'wcfmmp_is_allow_commission_on_shipping', false ) ) {
					$shipping_commission = ( isset( $item['cost'] ) ) ? (float) $item['cost'] : 0; 
					
					if ( $refunded = $order->get_total_refunded_for_item( $item->get_id(), 'shipping' ) ) {
						$shipping_commission -= (float) $refunded;
					}
					
					if ( $WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $this->vendor_id ) ) {
						$order_taxes         = $order->get_taxes();
						if( ! empty( $order_taxes ) ) {
							if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
								foreach ( $order_taxes as $tax_item ) {
									$tax_item_id    = $tax_item->get_rate_id();
									$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
									$shipping_commission += ( '' != $tax_item_total ) ? (float) $tax_item_total : 0;
									$refunded = $order->get_tax_refunded_for_item( $item->get_id(), $tax_item_id, 'shipping' );
									$shipping_commission -= (float) $refunded;
									
								}
							}
						}
					}
				} else {
					$sql = "
							SELECT shipping, tax, shipping_tax_amount 
							FROM {$wpdb->prefix}wcfm_marketplace_orders
							WHERE 1 = 1
							AND   order_id    = " . $order->get_id() . "
							AND   `vendor_id` = " . $this->vendor_id;
					$order_line_shippings = $wpdb->get_results( $sql );
					
					$shipping_commission = 0;
					if( !empty( $order_line_shippings ) ) {
						foreach( $order_line_shippings as $order_line_shipping ) {
							$shipping_commission += $order_line_shipping->shipping;
							if ( $WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $this->vendor_id ) ) {
								$shipping_commission += $order_line_shipping->shipping_tax_amount;
							}
						}
					}
				}
				echo wc_price( $shipping_commission, array( 'currency' => $order->get_currency() ) );
			  ?>
			</td>
			<?php
  	} else {
			?>
			<td class="line_cost"></td>
			<?php
  	}
  }
  
  function wcfmmp_after_refund_total() {
  	?>
  	<td class="line_cost"></td>
  	<?php
  }
  
  // WCFM Marketplace Order Total Commission
  function wcfmmp_order_total_commission( $order_id ) {
  	global $WCFM, $wpdb, $WCFMmp;
  	
  	$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
  	$gross_sale_order = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $this->vendor_id, '', '', $order_id );
  	$order = wc_get_order( $order_id );
  	$order_currency = $order->get_currency();
  	
  	$order_status = sanitize_title( $order->get_status() );
  	
  	$td_style = '';
  	
  	$sql = "
  	SELECT GROUP_CONCAT(ID) as commission_ids,
  	   GROUP_CONCAT(item_id) as order_item_ids,
  	   SUM(commission_amount) as line_total,
  	   SUM(total_commission) as total_commission,
  	   SUM(item_total) as item_total,
  	   SUM(item_sub_total) as item_sub_total,
	     SUM(shipping) as shipping,
       SUM(tax) as tax,
       SUM(	shipping_tax_amount) as shipping_tax_amount,
       SUM(	refunded_amount) as refunded_amount,
       SUM(	discount_amount) as discount_amount
       FROM {$wpdb->prefix}wcfm_marketplace_orders
       WHERE order_id = " . $order_id . "
       AND `vendor_id` = " . $this->vendor_id . "
       AND `is_refunded` != 1";
    $order_due = $wpdb->get_results( $sql );
    if( !$order_due || !isset( $order_due[0] ) ) return;
    
    $total = 0;
    $subtotal = 0;
    $calculated_total   = 0;
    $total_tax          = 0;
    $total_shipping     = 0;
    $shipping_tax       = 0;
    $refund_total       = 0;
    $discount_total     = 0;
    $commission_tax     = 0;
    $aff_commission     = 0;
    $transaction_charge = 0;
    
    $commission_rule  = array();
    $aff_commission_rule = array();
    
    $total = $order_due[0]->total_commission;
    if( $WCFMmp->wcfmmp_vendor->is_vendor_deduct_discount( $this->vendor_id, $order_id ) ) {
    	$calculated_total = $order_due[0]->item_total;
    } else {
    	$calculated_total = $order_due[0]->item_sub_total;
    }
    //$calculated_total += ( float ) $order_due[0]->tax;
    
    // WC Refund Support - 3.0.4
    $commission_ids = explode( ",", $order_due[0]->commission_ids );
    foreach( $commission_ids as $commission_id ) {
    	if( method_exists( $WCFMmp->wcfmmp_commission, 'wcfmmp_get_commission_meta' ) ) {
				$total_tax          += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_tax_cost' );
				$total_shipping     += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_shipping_cost' );
				$shipping_tax       += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_shipping_tax' );
				$commission_tax     += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'commission_tax' );
				$aff_commission     += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, '_wcfm_affiliate_commission' );
				$transaction_charge += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'transaction_charge' );
				$commission_rule     = unserialize( $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'commission_rule' ) );
				//$aff_commission_rule = unserialize( $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, '_wcfm_affiliate_commission_rule' ) );
			}
    }
    
    //print_r($commission_rule );
    //print_r($aff_commission_rule);
    
    
    $calculated_total += ( float ) $total_tax;
    $calculated_total += ( float ) apply_filters( 'wcfmmmp_gross_sales_shipping_cost', $total_shipping, $this->vendor_id );
    $calculated_total += ( float ) $shipping_tax;
    	
    $refund_total = $order_due[0]->refunded_amount;
    $discount_total = $order_due[0]->discount_amount;
		if ( $get_shipping = $WCFMmp->wcfmmp_vendor->is_vendor_get_shipping( $this->vendor_id ) ) {
			//$total += ( float ) $order_due[0]->shipping; 
		}
		if ( $get_tax = $WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $this->vendor_id ) ) {
			//$total += (float) $order_due[0]->tax;
			if( $get_shipping ) {
				//$total += ( float ) $order_due[0]->shipping_tax_amount;
			}
		}
		?>
		<?php //if( !$admin_fee_mode ) { ?>
		  <?php do_action( 'wcfm_vendor_order_details_before_subtotal', $order_id, $this->vendor_id ); ?>
			<tr>
				<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Subtotal', 'wc-frontend-manager' ); ?>:</th>
				<td class="total" style="text-align:center; <?php echo $td_style; ?>">
					<div class="view">
					  <?php 
					    $subtotal = $calculated_total - ( (float) $refund_total + (float) $order_due[0]->shipping + (float) $order_due[0]->tax + (float) $order_due[0]->shipping_tax_amount );
							if( $refund_total && ( round( $subtotal, 2 ) != round( $order_due[0]->item_sub_total, 2 ) ) ) {
								echo "<del>" . wc_price( $order_due[0]->item_sub_total, array( 'currency' => $order_currency ) ) . "</del>";
								echo "<ins>" . wc_price( $subtotal, array( 'currency' => $order_currency ) ) . "</ins>";
							} else {
								echo wc_price( $order_due[0]->item_sub_total, array( 'currency' => $order_currency ) );
							}
						?>
					</div>
				</td>
			</tr>
			<?php do_action( 'wcfm_vendor_order_details_after_subtotal', $order_id, $this->vendor_id ); ?>
		<?php //} ?>
		<?php if ( $get_tax ) { ?>
			<?php
			if( apply_filters( 'wcfm_is_allow_vendor_order_details_tax_breakup', false ) ) {
				$order_taxes = $order->get_taxes();
				$tax_breakups = array();
				if ( ! empty( $order_taxes ) ) {
					foreach ( $order_taxes as $tax_id => $tax_item ) {
						$column_label   = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'wc-frontend-manager' );
						$tax_breakups[$tax_item->get_rate_id()] = array( 'label' => $column_label, 'subtotal' => 0, 'total' => 0 );
					}
				}
				
				if( !empty( $tax_breakups ) ) {
					$order_item_ids = explode( ",", $order_due[0]->order_item_ids );
					foreach( $order_item_ids as $order_item_id ) {
						$line_item       = new WC_Order_Item_Product( $order_item_id );
						if ( $tax_data = $line_item->get_taxes() ) {
							foreach ( $order_taxes as $tax_item ) {
								$tax_item_id    = $tax_item->get_rate_id();
								$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : 0;
								$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : 0;
								
								if( isset( $tax_breakups[$tax_item_id] ) ) {
									$tax_breakups[$tax_item_id]['subtotal'] += (float)$tax_item_subtotal;
									$tax_breakups[$tax_item_id]['total'] += (float)$tax_item_total;
								}
							}
						}
					}
					
					do_action( 'wcfm_vendor_order_details_before_tax_breakup', $order_id, $this->vendor_id );
					
					foreach( $tax_breakups as $tax_breakup_is => $tax_breakup ) {
						?>
						<tr>
							<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php echo $tax_breakup['label']; ?>:</th>
							<td class="total" style="text-align:center; <?php echo $td_style; ?>">
								<div class="view">
									<?php 
										if( apply_filters( 'wcfm_is_allow_vendor_order_details_commission_on_tax', false ) && ( round( $tax_breakup['total'], 2 ) != round( $tax_breakup['subtotal'], 2 ) ) ) {
											echo "<del>" . wc_price( $tax_breakup['total'], array( 'currency' => $order_currency ) ) . "</del>";
											echo "<ins>" . wc_price( $tax_breakup['subtotal'], array( 'currency' => $order_currency ) ) . "</ins>";
										} else {
											echo wc_price( $tax_breakup['subtotal'], array( 'currency' => $order_currency ) );
										}
									?>
								</div>
							</td>
						</tr>
						<?php
					}
					
					do_action( 'wcfm_vendor_order_details_after_tax_breakup', $order_id, $this->vendor_id );
				}
			}
			?>
			<?php if( apply_filters( 'wcfm_is_allow_vendor_order_details_tax_breakup_total', true ) ) { ?>
				
				<?php do_action( 'wcfm_vendor_order_details_before_tax', $order_id, $this->vendor_id ); ?>
				<tr>
					<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>">
						<?php if( apply_filters( 'wcfm_is_allow_vendor_order_details_tax_breakup', false ) ) { echo __( 'Total', 'wc-frontend-manager' ) . ' '; } ?>
						<?php _e( 'Tax', 'wc-frontend-manager' ); ?>:
					</th>
					<td class="total" style="text-align:center; <?php echo $td_style; ?>">
						<div class="view">
							<?php 
								if( apply_filters( 'wcfm_is_allow_vendor_order_details_commission_on_tax', false ) && (round( $total_tax, 2 ) != round( $order_due[0]->tax, 2 ) ) ) {
									echo "<del>" . wc_price( $total_tax, array( 'currency' => $order_currency ) ) . "</del>";
									echo "<ins>" . wc_price( $order_due[0]->tax, array( 'currency' => $order_currency ) ) . "</ins>";
								} else {
									echo wc_price( $order_due[0]->tax, array( 'currency' => $order_currency ) );
								}
							?>
						</div>
					</td>
				</tr>
				<?php do_action( 'wcfm_vendor_order_details_after_tax', $order_id, $this->vendor_id ); ?>
				
			<?php } ?>
		<?php } ?>
		<?php if ( $get_shipping && $order->get_formatted_shipping_address() ) { ?>
			
			<?php do_action( 'wcfm_vendor_order_details_before_shipping', $order_id, $this->vendor_id ); ?>
			<tr>
				<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Shipping', 'wc-frontend-manager' ); ?>:</th>
				<td class="total" style="text-align:center; <?php echo $td_style; ?>">
					<div class="view">
					  <?php 
						if( apply_filters( 'wcfm_is_allow_vendor_order_details_commission_on_shipping', false ) && ( round( $total_shipping, 2 ) != round( $order_due[0]->shipping, 2 ) ) ) {
							echo "<del>" . wc_price( $total_shipping, array( 'currency' => $order_currency ) ) . "</del>";
							echo "<ins>" . wc_price( $order_due[0]->shipping, array( 'currency' => $order_currency ) ) . "</ins>";
						} else {
							echo wc_price( apply_filters( 'wcfmmmp_gross_sales_shipping_cost', $order_due[0]->shipping, $this->vendor_id ), array( 'currency' => $order_currency ) );
						}
						?>
					</div>
				</td>
			</tr>
			<?php do_action( 'wcfm_vendor_order_details_after_shipping', $order_id, $this->vendor_id ); ?>
			
			<?php if( $get_tax ) { ?>
				<?php
				if( apply_filters( 'wcfm_is_allow_vendor_order_details_tax_breakup', false ) ) {
					$order_taxes = $order->get_taxes();
					$tax_breakups = array();
					if ( ! empty( $order_taxes ) ) {
						foreach ( $order_taxes as $tax_id => $tax_item ) {
							$column_label   = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'wc-frontend-manager' );
							$tax_breakups[$tax_item->get_rate_id()] = array( 'label' => $column_label, 'subtotal' => 0, 'total' => 0 );
						}
					}
					
					$line_items_shipping = $order->get_items( 'shipping' );
					$line_items_shipping = apply_filters( 'wcfm_valid_shipping_items', $line_items_shipping, $order_id );
					
					if( !empty( $tax_breakups ) ) {
						foreach ( $line_items_shipping as $shipping_item_id => $shipping_item ) {
							if ( $tax_data = $shipping_item->get_taxes() ) {
								foreach ( $order_taxes as $tax_item ) {
									$tax_item_id    = $tax_item->get_rate_id();
									$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : 0;
									
									if( isset( $tax_breakups[$tax_item_id] ) ) {
										$tax_breakups[$tax_item_id]['total'] += (float)$tax_item_total;
									}
								}
							}
						}
						
						do_action( 'wcfm_vendor_order_details_before_shipping_tax_breakup', $order_id, $this->vendor_id );
						
						foreach( $tax_breakups as $tax_breakup_is => $tax_breakup ) {
							?>
							<tr>
								<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php echo __( 'Shipping Tax', 'wc-frontend-manager' ) . ' ' . $tax_breakup['label']; ?>:</th>
								<td class="total" style="text-align:center; <?php echo $td_style; ?>">
									<div class="view">
										<?php 
										echo wc_price( $tax_breakup['total'], array( 'currency' => $order_currency ) );
										?>
									</div>
								</td>
							</tr>
							<?php
						}
						
						do_action( 'wcfm_vendor_order_details_after_shipping_tax_breakup', $order_id, $this->vendor_id );
					}
				}
				?>
				<?php if( apply_filters( 'wcfm_is_allow_vendor_order_details_tax_breakup_total', true ) ) { ?>
					<?php do_action( 'wcfm_vendor_order_details_before_shipping_tax', $order_id, $this->vendor_id ); ?>
					<tr>
						<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>">
							<?php if( apply_filters( 'wcfm_is_allow_vendor_order_details_tax_breakup', false ) ) { echo __( 'Total', 'wc-frontend-manager' ) . ' '; } ?> 
							<?php _e( 'Shipping Tax', 'wc-frontend-manager' ); ?>:
						</th>
						<td class="total" style="text-align:center; <?php echo $td_style; ?>">
							<div class="view">
								<?php 
								if( apply_filters( 'wcfm_is_allow_vendor_order_details_commission_on_tax', false ) && ( round( $shipping_tax, 2 ) != round( $order_due[0]->shipping_tax_amount, 2 ) ) ) {
									echo "<del>" . wc_price( $shipping_tax, array( 'currency' => $order_currency ) ) . "</del>";
									echo "<ins>" . wc_price( $order_due[0]->shipping_tax_amount, array( 'currency' => $order_currency ) ) . "</ins>";
								} else {
									echo wc_price( $order_due[0]->shipping_tax_amount, array( 'currency' => $order_currency ) );
								}
								?>
							</div>
						</td>
					</tr>
					<?php do_action( 'wcfm_vendor_order_details_after_shipping_tax', $order_id, $this->vendor_id ); ?>
				<?php } ?>
			<?php } ?>
		<?php } ?>
		
		<?php if( $refund_total ) { ?>
			<?php do_action( 'wcfm_vendor_order_details_before_refund', $order_id, $this->vendor_id ); ?>
		  <tr>
				<th class="label refunded-total" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Refunded', 'wc-frontend-manager' ); ?>:</th>
				<td class="total refunded-total" style="text-align:center; <?php echo $td_style; ?>">-<?php echo wc_price( $refund_total, array( 'currency' => $order_currency ) ); ?></td>
			</tr>
			<?php do_action( 'wcfm_vendor_order_details_before_refund', $order_id, $this->vendor_id ); ?>
		<?php } ?>
		
		<?php if( $discount_total && $WCFMmp->wcfmmp_vendor->is_vendor_deduct_discount( $this->vendor_id, $order_id ) ) { ?>
			<?php do_action( 'wcfm_vendor_order_details_before_discount', $order_id, $this->vendor_id ); ?>
		  <tr>
				<th class="label discount-total" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Discount', 'wc-frontend-manager' ); ?>:</th>
				<td class="total discount-total" style="text-align:center; <?php echo $td_style; ?>"><?php echo wc_price( $discount_total, array( 'currency' => $order_currency ) ); ?></td>
			</tr>
			<?php do_action( 'wcfm_vendor_order_details_after_discount', $order_id, $this->vendor_id ); ?>
		<?php } ?>
		
		<?php if( apply_filters( 'wcfm_is_allow_gross_total', true ) ) { ?>
			<?php do_action( 'wcfm_vendor_order_details_before_gross_total', $order_id, $this->vendor_id ); ?>
			<tr class="total_cost">
				<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Gross Total', 'wc-frontend-manager' ); ?>:</th>
				<td class="total" style="text-align:center; <?php echo $td_style; ?>">
					<div class="view">
						<?php 
						if( $refund_total ) {
							echo "<del>" . wc_price( $calculated_total, array( 'currency' => $order_currency ) ) . "</del>";
							echo "<ins>" . wc_price( $gross_sale_order, array( 'currency' => $order_currency ) ) . "</ins>";
						} else {
							echo wc_price( $gross_sale_order, array( 'currency' => $order_currency ) );
						}
						?>
					</div>
				</td>
			</tr>
			<?php do_action( 'wcfm_vendor_order_details_after_gross_total', $order_id, $this->vendor_id ); ?>
		<?php } ?>
		
		<?php
		if( apply_filters( 'wcfm_is_allow_order_details_commission_breakup_gross_earning', true ) && wcfm_vendor_has_capability( $this->vendor_id, 'view_commission' ) && !in_array( $order_status, array( 'failed', 'cancelled', 'refunded', 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) {
			if( !$admin_fee_mode || ( $admin_fee_mode && apply_filters( 'wcfm_is_allow_admin_fee_mode_commission_breakup', true ) ) ) {
				if( isset( $commission_rule['tax_enable'] ) && ( $commission_rule['tax_enable'] == 'yes' ) ) {
					?>
					
					<?php do_action( 'wcfm_vendor_order_details_before_gross_earning', $order_id, $this->vendor_id ); ?>
					<tr>
						<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Gross Earning', 'wc-frontend-manager' ); ?>:</th>
						<td class="total" style="text-align:center; <?php echo $td_style; ?>">
							<div class="view">
								<?php 
								echo wc_price( ( $total + $commission_tax + $aff_commission + $transaction_charge ), array( 'currency' => $order_currency ) ); 
								?>
							</div>
						</td>
					</tr>
					<?php do_action( 'wcfm_vendor_order_details_after_gross_earning', $order_id, $this->vendor_id ); ?>
					
					<?php
					if( $aff_commission && apply_filters( 'wcfm_is_allow_view_affiliate_commission', true ) ) {
					?>
					<?php do_action( 'wcfm_vendor_order_details_before_affiliate_commission', $order_id, $this->vendor_id ); ?>
					<tr>
						<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Affiliate Commission', 'wc-frontend-manager' ); ?>:</th>
						<td class="total" style="text-align:center; <?php echo $td_style; ?>">
							<div class="view">
								<?php 
								echo '-' .wc_price( $aff_commission, array( 'currency' => $order_currency ) ); 
								?>
							</div>
						</td>
					</tr>
					<?php do_action( 'wcfm_vendor_order_details_after_affiliate_commission', $order_id, $this->vendor_id ); ?>
					<?php
					}
					?>
					
					<?php do_action( 'wcfm_vendor_order_details_before_commission_tax', $order_id, $this->vendor_id ); ?>
					<tr>
						<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php echo $commission_rule['tax_name'] . ' ('. $commission_rule['tax_percent'] .'%)'; ?>:</th>
						<td class="total" style="text-align:center; <?php echo $td_style; ?>">
							<div class="view">
								<?php 
								echo '-' .wc_price( $commission_tax, array( 'currency' => $order_currency ) ); 
								?>
							</div>
						</td>
					</tr>
					<?php do_action( 'wcfm_vendor_order_details_after_commission_tax', $order_id, $this->vendor_id ); ?>
					
				  <?php
				} elseif( $aff_commission && apply_filters( 'wcfm_is_allow_view_affiliate_commission', true ) ) {
					?>
					
					<?php do_action( 'wcfm_vendor_order_details_before_gross_earning', $order_id, $this->vendor_id ); ?>
					<tr>
						<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Gross Earning', 'wc-frontend-manager' ); ?>:</th>
						<td class="total" style="text-align:center; <?php echo $td_style; ?>">
							<div class="view">
								<?php 
								echo wc_price( ( $total + $aff_commission + $transaction_charge ), array( 'currency' => $order_currency ) ); 
								?>
							</div>
						</td>
					</tr>
					<?php do_action( 'wcfm_vendor_order_details_after_gross_earning', $order_id, $this->vendor_id ); ?>
					
					<?php do_action( 'wcfm_vendor_order_details_before_affiliate_commission', $order_id, $this->vendor_id ); ?>
					<tr>
						<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Affiliate Commission', 'wc-frontend-manager' ); ?>:</th>
						<td class="total" style="text-align:center; <?php echo $td_style; ?>">
							<div class="view">
								<?php 
								echo '-' .wc_price( $aff_commission, array( 'currency' => $order_currency ) ); 
								?>
							</div>
						</td>
					</tr>
					<?php do_action( 'wcfm_vendor_order_details_after_affiliate_commission', $order_id, $this->vendor_id ); ?>
					
					<?php
				}
				?>
				
				<?php
				// Show Transaction Charges
				if( $transaction_charge && apply_filters( 'wcfm_is_allow_view_transaction_charge', true ) && isset( $commission_rule['transaction_charge_type'] ) && ( $commission_rule['transaction_charge_type'] != 'no' ) ) {
					?>
					
					<?php if( !$aff_commission && ( !isset( $commission_rule['tax_enable'] ) || ( isset( $commission_rule['tax_enable'] ) && ( $commission_rule['tax_enable'] != 'yes' ) ) ) ) { ?>
						
						<?php do_action( 'wcfm_vendor_order_details_before_gross_earning', $order_id, $this->vendor_id ); ?>
						<tr>
							<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Gross Earning', 'wc-frontend-manager' ); ?>:</th>
							<td class="total" style="text-align:center; <?php echo $td_style; ?>">
								<div class="view">
									<?php 
									echo wc_price( ( $total + $transaction_charge ), array( 'currency' => $order_currency ) ); 
									?>
								</div>
							</td>
						</tr>
						<?php do_action( 'wcfm_vendor_order_details_after_gross_earning', $order_id, $this->vendor_id ); ?>
					
					<?php } ?>
					
					<?php do_action( 'wcfm_vendor_order_details_before_transaction_charge', $order_id, $this->vendor_id ); ?>
					<tr>
						<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Transaction Charge', 'wc-frontend-manager' ); ?>:</th>
						<td class="total" style="text-align:center; <?php echo $td_style; ?>">
							<div class="view">
								<?php 
								echo '-' .wc_price( $transaction_charge, array( 'currency' => $order_currency ) ); 
								?>
							</div>
						</td>
					</tr>
					<?php do_action( 'wcfm_vendor_order_details_after_transaction_charge', $order_id, $this->vendor_id ); ?>
				 
					<?php
				}
				?>
				
				<?php do_action( 'wcfm_vendor_order_details_before_total_earning', $order_id, $this->vendor_id ); ?>
				<tr>
					<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Total Earning', 'wc-frontend-manager' ); ?>:</th>
					<td class="total" style="text-align:center; <?php echo $td_style; ?>">
						<div class="view">
						  <?php 
						  echo wc_price( $total, array( 'currency' => $order_currency ) ); 
						  if( apply_filters( 'wcfm_is_allow_earning_in_words', false ) ) {
						  	echo "<br/>" . wcfm_number_to_words($total);
						  }
						  ?>
						</div>
					</td>
				</tr>
				<?php do_action( 'wcfm_vendor_order_details_after_total_earning', $order_id, $this->vendor_id ); ?>
				
			  <?php
			}
			if( apply_filters( 'wcfm_is_allow_order_details_admin_fee', true ) ) {
				do_action( 'wcfm_vendor_order_details_before_admin_fee', $order_id, $this->vendor_id );
				?>
				<tr>
					<th class="label" colspan="2" style="text-align:right; <?php echo $td_style; ?>"><?php _e( 'Admin Fee', 'wc-frontend-manager' ); ?>:</th>
					<td class="total" style="text-align:center; <?php echo $td_style; ?>">
						<div class="view">
							<?php 
							echo wc_price( ($gross_sale_order - $total), array( 'currency' => $order_currency ) );
							if( apply_filters( 'wcfm_is_allow_earning_in_words', false ) ) {
						  	echo "<br/>" . wcfm_number_to_words(($gross_sale_order - $total));
						  }
							?>
						</div>
					</td>
				</tr>
				<?php
				do_action( 'wcfm_vendor_order_details_after_admin_fee', $order_id, $this->vendor_id );
			}
		}
  }
  
  // CSV Export URL
  function wcfmmp_generate_csv_url( $url, $order_id ) {
  	//$url = admin_url('admin.php?action=wcfmmp_csv_download_per_order&orders_for_product=' . $order_id . '&nonce=' . wp_create_nonce('wcmp_vendor_csv_download_per_order'));
  	return $url;
  }
  
  // Report Vendor Filter
  function wcfmmp_report_out_of_stock_query_from( $query_from, $stock ) {
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
  function wcfmmp_reports_order_statuses( $order_status ) {
  	$order_status = array( 'completed', 'processing', 'on-hold' );
  	return $order_status;
  }
  
  // WCFM Marketplace dashboard top seller query
  function wcfmmp_dashboard_status_widget_top_seller_query( $query, $limit = 5 ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	//$products = $this->wcfmmp_get_vendor_products();
  	//if( empty($products) ) return array(0);
		//$query['where'] .= "AND order_item_meta_2.meta_value in (" . implode( ',', $products ) . ")";
		
		$query            = array();
		$query['fields']  = "SELECT SUM( quantity ) as qty, product_id
			FROM {$wpdb->prefix}wcfm_marketplace_orders";
		$query['where']   = "WHERE vendor_id = {$user_id} ";
		$query['where']  .= "AND order_status IN ( '" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) ";
		$query['where']  .= "AND created >= '" . date( 'Y-m-d', strtotime( '-7 DAY', current_time( 'timestamp' ) ) ) . "' ";
		$query['where']  .= "AND created <= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . "' ";
		$query['groupby'] = "GROUP BY product_id";
		$query['orderby'] = "ORDER BY qty DESC";
		$query['limits']  = "LIMIT {$limit}";
  	
  	return $query;
  }
  
  // Report Data Filter as per Vendor
  function wcfmmp_reports_get_order_report_data( $result ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->wcfmmp_get_vendor_products();
  	
  	if( !empty( $result ) && is_array( $result ) ) {
  		foreach( $result as $result_key => $result_val ) {
  			if( !in_array( $result_val->product_id, $products ) ) unset( $result[$result_key] );
  		}
  	}
  	
  	return $result;
  }
  
  /**
   * WC Vendors current venndor products
   */
  function wcfmmp_get_vendor_products( $vendor_id = 0 ) {
  	if( !$vendor_id ) $vendor_id = $this->vendor_id;
  	
  	$post_count = 9999; //count_user_posts( $vendor_id, 'product' );
  	$post_loop_offset = 0;
  	$products_arr = array(0);
  	while( $post_loop_offset < $post_count ) {
			$args = array(
								'posts_per_page'   => apply_filters( 'wcfm_break_loop_offset', 100 ),
								'offset'           => $post_loop_offset,
								'category'         => '',
								'category_name'    => '',
								'orderby'          => 'date',
								'order'            => 'DESC',
								'include'          => '',
								'exclude'          => '',
								'meta_key'         => '',
								'meta_value'       => '',
								'post_type'        => 'product',
								'post_mime_type'   => '',
								'post_parent'      => '',
								//'author'	       => get_current_user_id(),
								'post_status'      => array('draft', 'pending', 'publish', 'private'),
								'suppress_filters' => 0, 
								'fields'           => 'ids'
							);
			
			$args = apply_filters( 'wcfm_products_args', $args );
			
			if( class_exists('WooCommerce_simple_auction') ) {
				remove_all_filters( 'pre_get_posts' );
			}
		
			$products = get_posts( $args );
			if(!empty($products)) {
				foreach($products as $product) {
					$products_arr[] = $product;
				}
			} else {
				break;
			}
			$post_loop_offset += apply_filters( 'wcfm_break_loop_offset', 100 );
		}
		
		return $products_arr;
  }
}