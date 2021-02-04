<?php

/**
 * WCFM plugin core
 *
 * Marketplace WC Vendors Support
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   1.0.1
 */
 
class WCFM_WCVendors {
	
	private $vendor_id;
	
	public function __construct() {
    global $WCFM;
    
    if( wcfm_is_vendor() ) {
    	
    	$this->vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    	
    	// Remove Date Range
    	add_filter( 'wcvendors_orders_date_range', array( &$this, 'wcvendors_orders_date_range' ) );
    	
    	// Store Identity
    	add_filter( 'wcfm_store_logo', array( &$this, 'wcvendors_store_logo' ) );
    	add_filter( 'wcfm_store_name', array( &$this, 'wcvendors_store_name' ) );
    	
    	// WCFM Menu Filter
    	add_filter( 'wcfm_menus', array( &$this, 'wcvendors_wcfm_menus' ), 30 );
    	add_filter( 'wcfm_add_new_product_sub_menu', array( &$this, 'wcvendors_add_new_product_sub_menu' ) );
    	add_filter( 'wcfm_add_new_coupon_sub_menu', array( &$this, 'wcvendors_add_new_coupon_sub_menu' ) );
    	
    	// WCFM Home Menu at WCV Dashboard
    	add_action( 'wcvendors_before_links', array( &$this, 'wcfm_home' ), 5 );
    	
    	// WCVendors Menu Fiter
    	add_filter( 'wcv_add_product_url', array( &$this, 'wcvendors_wcfm_add_product_url' ) );
    	add_filter( 'wcv_edit_product_url', array( &$this, 'wcvendors_wcfm_edit_product_url' ) );
    	
    	// WCVendors Pro Menu filter
    	add_filter( 'wcv_dashboard_quick_links', array( &$this, 'wcvendors_wcfm_dashboard_quick_links' ) );
    	add_filter( 'wcv_dashboard_pages_nav', array( &$this, 'wcvendors_wcfm_dashboard_pages_nav' ) );
    	
			// Allow Vendor user to manage product from catalog
			add_filter( 'wcfm_allwoed_user_roles', array( &$this, 'allow_wcvendors_vendor_role' ) );
			add_filter( 'wcfm_allwoed_vendor_user_roles', array( &$this, 'allow_wcvendors_vendor_role' ) );
			
			// Filter Vendor Products
			add_filter( 'wcfm_articles_args', array( &$this, 'wcvendors_products_args' ) );
			add_filter( 'wcfm_products_args', array( &$this, 'wcvendors_products_args' ) );
			add_filter( 'get_booking_products_args', array( $this, 'wcvendors_products_args' ) );
			add_filter( 'get_appointment_products_args', array( $this, 'wcvendors_products_args' ) );
			add_filter( 'wpjmp_job_form_products_args', array( &$this, 'wcvendors_products_args' ) );
			add_filter( 'wpjmp_admin_job_form_products_args', array( &$this, 'wcvendors_products_args' ) );
			
			// Listing Filter for specific vendor
    	add_filter( 'wcfm_listing_args', array( $this, 'wcvendors_listing_args' ), 20 );
    	add_filter( "woocommerce_product_export_product_query_args", array( &$this, 'wcvendors_listing_args' ), 100 );
    	
    	// Customers args
    	if( apply_filters( 'wcfm_is_allow_order_customers_to_vendors', true ) ) {
    		add_filter( 'wcfm_get_customers_args', array( &$this, 'wcvendors_filter_customers' ), 20 );
    	}
    	
    	// Booking Filter
			add_filter( 'wcfm_wcb_include_bookings', array( &$this, 'wcvendors_wcb_include_bookings' ) );
			
			// Manage Vendor Product Permissions
			add_filter( 'wcfm_product_types', array( &$this, 'wcvendors_is_allow_product_types'), 100 );
			add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcvendors_is_allow_fields_general' ), 100 );
			add_filter( 'wcfm_is_allow_inventory', array( &$this, 'wcvendors_is_allow_inventory' ) );
			add_filter( 'wcfm_is_allow_shipping', array( &$this, 'wcvendors_is_allow_shipping' ) );
			add_filter( 'wcfm_is_allow_tax', array( &$this, 'wcvendors_is_allow_tax' ) );
			add_filter( 'wcfm_is_allow_attribute', array( &$this, 'wcvendors_is_allow_attribute' ) );
			add_filter( 'wcfm_is_allow_variable', array( &$this, 'wcvendors_is_allow_variable' ) );
			add_filter( 'wcfm_is_allow_linked', array( &$this, 'wcvendors_is_allow_linked' ) );
			
			add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcvendors_product_manage_vendor_association' ), 10, 2 );
			add_action( 'gmw_location_form_default_location', array( &$this, 'wcvendors_geo_locator_default_address' ), 10, 2 );
			
			// Manage Vendor Product Export Permissions - 2.4.2
			add_filter( 'product_type_selector', array( $this, 'wcvendors_filter_product_types' ), 98 );
			add_filter( 'woocommerce_product_export_row_data', array( &$this, 'wcvendors_product_export_row_data' ), 100, 2 );
			
			// Filter Vendor Coupons
			add_filter( 'wcfm_coupons_args', array( &$this, 'wcvendors_coupons_args' ) );
			
			// Manage Vendor Coupon Permission
			add_filter( 'wcfm_coupon_types', array( &$this, 'wcvendors_coupon_types' ) );
			
			// Manage Order Details Permission
			add_filter( 'wcfm_allow_order_details', array( &$this, 'wcvendors_is_allow_order_details' ) );
			add_filter( 'wcfm_allow_order_customer_details', array( &$this, 'wcvendors_is_allow_order_customer_details' ) );
			add_filter( 'wcfm_valid_line_items', array( &$this, 'wcvendors_valid_line_items' ), 10, 3 );
			add_filter( 'wcfm_order_details_shipping_line_item', array( &$this, 'wcvendors_is_allow_order_details_shipping_line_item' ) );
			add_filter( 'wcfm_order_details_tax_line_item', array( &$this, 'wcvendors_is_allow_order_details_tax_line_item' ) );
			add_filter( 'wcfm_order_details_line_total_head', array( &$this, 'wcvendors_is_allow_order_details_line_total_head' ) );
			add_filter( 'wcfm_order_details_line_total', array( &$this, 'wcvendors_is_allow_order_details_line_total' ) );
			add_filter( 'wcfm_order_details_tax_total', array( &$this, 'wcvendors_is_allow_order_details_tax_total' ) );
			add_filter( 'wcfm_order_details_fee_line_item', array( &$this, 'wcvendors_is_allow_order_details_fee_line_item' ) );
			add_filter( 'wcfm_order_details_refund_line_item', array( &$this, 'wcvendors_is_allow_order_details_refund_line_item' ) );
			add_filter( 'wcfm_order_details_coupon_line_item', array( &$this, 'wcvendors_is_allow_order_details_coupon_line_item' ) );
			add_filter( 'wcfm_order_details_total', array( &$this, 'wcvendors_is_allow_wcfm_order_details_total' ) );
			add_action ( 'wcfm_order_details_after_line_total_head', array( &$this, 'wcvendors_after_line_total_head' ) );
			add_action ( 'wcfm_after_order_details_line_total', array( &$this, 'wcvendors_after_line_total' ), 10, 2 );
			add_action ( 'wcfm_order_totals_after_total', array( &$this, 'wcvendors_order_total_commission' ) );
			//add_filter( 'wcfm_generate_csv_url', array( &$this, 'wcvendors_generate_csv_url' ), 10, 2 );
			
			// Report Filter
			add_filter( 'wcfm_report_out_of_stock_query_from', array( &$this, 'wcvendors_report_out_of_stock_query_from' ), 100, 2 );
			add_filter( 'woocommerce_reports_order_statuses', array( &$this, 'wcvendors_reports_order_statuses' ) );
			add_filter( 'woocommerce_dashboard_status_widget_top_seller_query', array( &$this, 'wcvendors_dashboard_status_widget_top_seller_query'), 100 );
			//add_filter( 'woocommerce_reports_get_order_report_data', array( &$this, 'wcvendors_reports_get_order_report_data'), 100 );
		}
  }
  
  // WCFM WCV Date Range
  function wcvendors_orders_date_range( $date ) {
  	global $start_date, $end_date;
  	if( is_wcfm_page() || defined('DOING_AJAX') ) {
  		if( $start_date > strtotime( '-30 DAY', strtotime( date( 'Ymd', current_time( 'timestamp' ) ) ) ) ) {
  			$start_date = strtotime( '-30 DAY', strtotime( date( 'Ymd', current_time( 'timestamp' ) ) ) );
  			$date['after'] = date( 'Y-m-d', strtotime( '-30 DAY', strtotime( date( 'Ymd', current_time( 'timestamp' ) ) ) ) );
  		}
  	}
  	return $date;
  }
  
  // WCFM WCV Store Logo
  function wcvendors_store_logo( $store_logo ) {
  	$user_id = $this->vendor_id;
  	$logo = get_user_meta( $user_id, '_wcv_store_icon_id', true );
  	$logo_image_url = wp_get_attachment_image_src( $logo, 'thumbnail' );

		if ( !empty( $logo_image_url ) ) {
			$store_logo = $logo_image_url[0];
			$shop_link       = WCV_Vendors::get_vendor_shop_page( wp_get_current_user()->user_login );
			if( $shop_link ) {
				$store_logo = '<a class="wcfm_store_logo_icon" href="' . $shop_link . '" target="_blank"><img src="' . $store_logo . '" alt="Store Logo" /></a>';
			}
		}
  	return $store_logo;
  }
  
  // WCFM WCV Store Name
  function wcvendors_store_name( $store_name ) {
  	$user_id = $this->vendor_id;
  	$shop_name = wcfm_get_option( 'wcfm_my_store_label', __( 'My Store', 'wc-frontend-manager' ) );  //get_user_meta( $user_id, 'pv_shop_name', true );
  	if( $shop_name ) $store_name = $shop_name;
  	$shop_link       = WCV_Vendors::get_vendor_shop_page( wp_get_current_user()->user_login );
  	if( $shop_name ) { $store_name = '<a target="_blank" href="' . apply_filters('wcv_vendor_shop_permalink', $shop_link) . '">' . $shop_name . '</a>'; }
  	else { $store_name = '<a target="_blank" href="' . apply_filters('wcv_vendor_shop_permalink', $shop_link) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
  	return $store_name;
  }
  
  // WCFM WCVendors Menu
  function wcvendors_wcfm_menus( $menus ) {
  	global $WCFM;
  	
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
			$can_view_orders = WC_Vendors::$pv_options->get_option( 'can_show_orders' );
			$can_view_sales = WC_Vendors::$pv_options->get_option( 'can_view_frontend_reports' );
		} else {
			$can_view_orders = get_option('wcvendors_capability_orders_enabled');
			$can_view_sales = get_option('wcvendors_capability_frontend_reports');
		}
  	
  	
  	//if( !current_user_can( 'edit_products' ) ) unset( $menus['wcfm-products'] );
  	//if( !current_user_can( 'edit_shop_coupons' ) ) unset( $menus['wcfm-coupons'] );
  	if( !$can_view_orders ) unset( $menus['wcfm-orders'] );
  	if( !$can_view_sales ) unset( $menus['wcfm-reports'] );
  	
  	return $menus;
  }
  
  // WCV Add New Product Sub menu
  function wcvendors_add_new_product_sub_menu( $has_new ) {
  	//if( !current_user_can( 'edit_products' ) ) $has_new = false;
  	return $has_new;
  }
  
  // WCV Add New Coupon Sub menu
  function wcvendors_add_new_coupon_sub_menu( $has_new ) {
  	//if( !current_user_can( 'edit_shop_coupons' ) ) $has_new = false;
  	return $has_new;
  }
  
  // WCFM Home Menu at WCV Dashboard
  function wcfm_home() {
  	global $WCFM;
  	
  	echo '<a href="' . get_wcfm_page() . '"><img class="text_tip" data-tip="' . __( 'WCFM Home', 'wc-frontend-manager' ) . '" id="wcfm_home" src="' . $WCFM->plugin_url . '/assets/images/wcfm-30x30.png" alt="' . __( 'WCFM Home', 'wc-frontend-manager' ) . '" /></a>';
  }
  
  // WCFM WCVendors Add product URL
  function wcvendors_wcfm_add_product_url( $submit_link ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$can_submit = WC_Vendors::$pv_options->get_option( 'can_submit_products' );
  	} else {
  		$can_submit = get_option('wcvendors_capability_products_enabled');
  	}
  	if( $can_submit ) $submit_link = get_wcfm_edit_product_url();
  	return $submit_link;
  }
  
  // WCFM WCVendors Edit product URL
  function wcvendors_wcfm_edit_product_url( $edit_link ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$can_submit = WC_Vendors::$pv_options->get_option( 'can_submit_products' );
  	} else {
  		$can_submit = get_option('wcvendors_capability_products_enabled');
  	}
  	if( $can_submit ) $edit_link = get_wcfm_products_url();
  	return $edit_link;
  }
  
  // WCFM WCVendors Pro Quick Links
  function wcvendors_wcfm_dashboard_quick_links( $quick_links ) {
  	if( isset( $quick_links['shop_coupon'] ) ) $quick_links['shop_coupon']['url'] = get_wcfm_coupons_manage_url();
  	return $quick_links;
  }
  
  // WCFM WCVendors Pro Dasboard Menu
  function wcvendors_wcfm_dashboard_pages_nav( $navs ) {
  	
  	if( isset( $navs['product'] ) ) $navs['product']['slug'] = get_wcfm_products_url();
  	if( isset( $navs['shop_coupon'] ) ) $navs['shop_coupon']['slug'] = get_wcfm_coupons_url();
  	if( isset( $navs['order'] ) ) $navs['order']['slug'] = get_wcfm_orders_url();
  	
  	return $navs;
  }
  
  function allow_wcvendors_vendor_role( $allowed_roles ) {
  	if( wcfm_is_vendor() ) $allowed_roles[] = 'vendor';
  	return $allowed_roles;
  }
  
  function wcvendors_products_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // WCV Listing args
	function wcvendors_listing_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  /**
   * WC Vendors filter customers
   */
  function wcvendors_filter_customers( $args ) {
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
  	$sql = 'SELECT order_id FROM ' . $wpdb->prefix . 'pv_commission';
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
   * WC Vendors Bookings
   */
  function wcvendors_wcb_include_bookings( ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$vendor_products = $this->wcv_get_vendor_products( $this->vendor_id );
		
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
  
  // Product Types
  function wcvendors_is_allow_product_types( $product_types ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$types = (array) WC_Vendors::$pv_options->get_option( 'hide_product_types' );
  	} else {
  		$types = get_option( 'wcvendors_capability_product_types', array() );
  	}
  	foreach ( $product_types as $key => $value ) {
			if ( !empty( $types[ $key ] ) ) {
				unset( $product_types[ $key ] );
			}
		}
		if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
			$product_panel = (array) WC_Vendors::$pv_options->get_option( 'hide_product_panel' );
		} else {
			$product_panel = get_option( 'wcvendors_capability_product_data_tabs', array() );
		}
  	if( !empty( $product_panel['attribute'] ) ) unset( $product_types['variable'] );
  	
		return $product_types;
  }
  
  // General Fields
  function wcvendors_is_allow_fields_general( $general_fields ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$product_misc = (array) WC_Vendors::$pv_options->get_option( 'hide_product_misc' );
  		if( !empty( $product_misc['sku'] ) ) unset( $general_fields['sku'] );
  	} else {
  		$product_misc = get_option( 'wcvendors_capability_product_sku', '' );
  		if( $product_misc ) unset( $general_fields['sku'] );
  	}
  		
  	return $general_fields;
  }
  
  // Inventory
  function wcvendors_is_allow_inventory( $allow ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$product_panel = (array) WC_Vendors::$pv_options->get_option( 'hide_product_panel' );
  	} else {
  		$product_panel = get_option( 'wcvendors_capability_product_data_tabs', array() );
  	}
  	if( !empty( $product_panel['inventory'] ) ) return false;
  	return $allow;
  }
  
  // Shipping
  function wcvendors_is_allow_shipping( $allow ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$product_panel = (array) WC_Vendors::$pv_options->get_option( 'hide_product_panel' );
  	} else {
  		$product_panel = get_option( 'wcvendors_capability_product_data_tabs', array() );
  	}
  	if( !empty( $product_panel['shipping'] ) ) return false;
  	return $allow;
  }
  
  // Tax
  function wcvendors_is_allow_tax( $allow ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$product_misc = (array) WC_Vendors::$pv_options->get_option( 'hide_product_misc' );
  		if( !empty( $product_misc['taxes'] ) ) return false;
  	} else {
  		$product_misc = wc_string_to_bool( get_option( 'wcvendors_capability_product_taxes', 'no' ) );
  		if( $product_misc ) return false;
  	}
  	
  	return $allow;
  }
  
  // Attributes
  function wcvendors_is_allow_attribute( $allow ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$product_panel = (array) WC_Vendors::$pv_options->get_option( 'hide_product_panel' );
  	} else {
  		$product_panel = get_option( 'wcvendors_capability_product_data_tabs', array() );
  	}
  	if( !empty( $product_panel['attribute'] ) ) return false;
  	return $allow;
  }
  
  // Variable
  function wcvendors_is_allow_variable( $allow ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$product_panel = (array) WC_Vendors::$pv_options->get_option( 'hide_product_panel' );
  	} else {
  		$product_panel = get_option( 'wcvendors_capability_product_data_tabs', array() );
  	}
  	if( !empty( $product_panel['attribute'] ) ) return false;
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$types = (array) WC_Vendors::$pv_options->get_option( 'hide_product_types' );
  	} else {
  		$types = get_option( 'wcvendors_capability_product_types', array() );
  	}
  	if( !empty( $types['variable'] ) ) return false;
  	return $allow;
  }
  
  // Linked
  function wcvendors_is_allow_linked( $allow ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$product_panel = (array) WC_Vendors::$pv_options->get_option( 'hide_product_panel' );
  	} else {
  		$product_panel = get_option( 'wcvendors_capability_product_data_tabs', array() );
  	}
  	if( !empty( $product_panel['linked_product'] ) ) return false;
  	return $allow;
  }
  
  // Product Vendor association on Product save
  function wcvendors_product_manage_vendor_association( $new_product_id, $wcfm_products_manage_form_data ) {
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
  function wcvendors_geo_locator_default_address( $gmw_saved_location, $args ) {
  	global $WCFM;
  	
  	if ( empty( $gmw_saved_location ) ) {
			$user_id = $this->vendor_id;
			
			$addr_1  = get_user_meta( $user_id, '_wcv_store_address1', true );
			$addr_2  = get_user_meta( $user_id, '_wcv_store_address2', true );
			$country  = get_user_meta( $user_id, '_wcv_store_country', true );
			$city  = get_user_meta( $user_id, '_wcv_store_city', true );
			$state  = get_user_meta( $user_id, '_wcv_store_state', true );
			$zip  = get_user_meta( $user_id, '_wcv_store_postcode', true );
					
			$address  = $addr_1;
			if( $addr_2 ) $address  .= ' ' . $addr_2;
			if( $city ) $address  .= ', ' . $city;
			if( $state ) $address  .= ', ' . $state;
			if( $zip ) $address  .= ' ' . $zip;
			if( $country ) $address  .= ', ' . $country;
			
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
				  'latitude'      	=> 0,
					'longitude'     	=> 0,
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
					'address' 			  => $address,
					'formatted_address' => $address
			);
		}
		
  	return $gmw_saved_location;
  }
  
  // Remove WC Vendors Buggy filter
  function wcvendors_filter_product_types( $types ) {
  	remove_all_filters( 'product_type_selector', 99 );
  	return $types;
  }
  
  // Product Export Data Filter
  function wcvendors_product_export_row_data( $row, $product ) {
  	global $WCFM;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->wcv_get_vendor_products();
		
		if( !in_array( $product->get_ID(), $products ) ) return array();
		
		return $row;
  }
  
  // Coupons Args
  function wcvendors_coupons_args( $args ) {
  	if( wcfm_is_vendor() ) $args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // Coupon Types
  function wcvendors_coupon_types( $types ) {
  	$wcmp_coupon_types = array( 'percent', 'fixed_product' );
  	foreach( $types as $type => $label ) 
  		if( !in_array( $type, $wcmp_coupon_types ) ) unset( $types[$type] );
  	return $types;
  } 
  
  // Order Status details
  function wcvendors_is_allow_order_details( $allow ) {
  	return false;
  }
  
  // Order Customer Details
  function wcvendors_is_allow_order_customer_details( $allow ) {
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
  		$can_view_emails = WC_Vendors::$pv_options->get_option( 'can_view_order_emails' );
  	} else {
  		$can_view_emails = get_option( 'wcvendors_capability_order_customer_email' );
  	}
  	
  	if( !$can_view_emails ) return false;
  	return $allow;
  }
  
  // Filter Order Details Line Items as Per Vendor
  function wcvendors_valid_line_items( $items, $order_id ) {
  	global $WCFM, $wpdb;
  	
  	$valid_items = (array) WCV_Queries::get_products_for_order( $order_id );
  	
  	$valid = array();
  	foreach ($items as $key => $value) {
			if ( in_array( $value->get_variation_id(), $valid_items ) || in_array( $value->get_product_id(), $valid_items ) ) {
				$valid[$key] = $value;
			} elseif( $value->get_product_id() == 0 ) {
				$_product_id = wc_get_order_item_meta( $key, '_product_id', true );
				$_variation_id = wc_get_order_item_meta( $key, '_variation_id', true );
				if ( in_array( $_product_id, $valid_items ) || in_array( $_variation_id, $valid_items ) ) {
					$valid[$key] = $value;
				}
			}
		}
  	return $valid;
  }
  
  // Order Details Shipping Line Item
  function wcvendors_is_allow_order_details_shipping_line_item( $allow ) {
  	//if ( !WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) $allow = false;
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Tax Line Item
  function wcvendors_is_allow_order_details_tax_line_item( $allow ) {
  	//if ( !WC_Vendors::$pv_options->get_option( 'give_tax' ) ) $allow = false;
  	$allow = false;
  	return $allow;
  }
  
  // Order Total Line Item Head
  function wcvendors_is_allow_order_details_line_total_head( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Total Line Item
  function wcvendors_is_allow_order_details_line_total( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Tax Total
  function wcvendors_is_allow_order_details_tax_total( $allow ) {
  	//if ( !WC_Vendors::$pv_options->get_option( 'give_tax' ) ) $allow = false;
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Fee Line Item
  function wcvendors_is_allow_order_details_fee_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Coupon Line Item
  function wcvendors_is_allow_order_details_coupon_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Refunded Line Item
  function wcvendors_is_allow_order_details_refund_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Total
  function wcvendors_is_allow_wcfm_order_details_total( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // WCVendors After Order Total Line Head
  function wcvendors_after_line_total_head( $order ) {
  	global $WCFM;
  	?>
  	<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
		  <th class="line_cost sortable" data-sort="float"><?php _e( 'Commission', 'wc-frontend-manager' ); ?></th>
		<?php } ?>
  	<?php
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
			if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) {
				?>
				<th class="line_cost sortable no_ipad no_mob" data-sort="float"><?php _e( 'Shipping', 'wc-frontend-manager' ); ?></th>
				<?php
			}
			
			if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) {
				?>
				<th class="line_cost sortable no_ipad no_mob" data-sort="float"><?php _e( 'Tax', 'wc-frontend-manager' ); ?></th>
				<th class="line_cost sortable no_ipad no_mob"></th>
				<?php
			}
		} else {
			if ( get_option('wcvendors_vendor_give_shipping') ) {
				?>
				<th class="line_cost sortable no_ipad no_mob" data-sort="float"><?php _e( 'Shipping', 'wc-frontend-manager' ); ?></th>
				<?php
			}
			
			if ( get_option('wcvendors_vendor_give_taxes') ) {
				?>
				<th class="line_cost sortable no_ipad no_mob" data-sort="float"><?php _e( 'Tax', 'wc-frontend-manager' ); ?></th>
				<th class="line_cost sortable no_ipad no_mob"></th>
				<?php
			}
		}
		if( apply_filters( 'wcfm_is_allow_total', true ) ) {
  	?>
  	<th class="line_cost sortable no_ipad no_mob"><?php _e( 'Total', 'wc-frontend-manager' ); ?></th>
  	<?php
  	}
  }
  
  // WCVendors after Order total Line item
  function wcvendors_after_line_total( $item, $order ) {
  	global $WCFM, $wpdb;
  	$order_currency = $order->get_currency();
  	$commission_rate = WCV_Commission::get_commission_rate( $item['product_id'] );
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
			SELECT total_due as line_total, total_shipping, tax 
			FROM {$wpdb->prefix}pv_commission
			WHERE   (product_id = " . $product_id . " OR product_id = " . $variation_id . ")
			AND     order_id = " . $order->get_id() . "
			AND     vendor_id = " . $this->vendor_id;
		$order_line_due = $wpdb->get_results( $sql );
		if( !empty( $order_line_due ) ) {
			$line_total += $order_line_due[0]->total_shipping; 
		  $line_total += $order_line_due[0]->tax;
		?>
		  <?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
				<td class="line_cost" width="1%">
					<div class="view"><?php echo wc_price( $order_line_due[0]->line_total, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			<?php } ?>
			<?php if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) { ?>
			<?php if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) { ?>
				<td class="line_cost no_ipad no_mob" width="1%">
					<div class="view"><?php echo wc_price( $order_line_due[0]->total_shipping, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<?php } ?>
				<?php if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) { ?>
				<td class="line_cost no_ipad no_mob">
					<div class="view"><?php echo wc_price( $order_line_due[0]->tax, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<td class="line_cost no_ipad no_mob">
					<div class="view"></div>
				</td>
				<?php } ?>
			<?php } else { ?>
				<?php if ( get_option('wcvendors_vendor_give_shipping') ) { ?>
				<td class="line_cost no_ipad no_mob" width="1%">
					<div class="view"><?php echo wc_price( $order_line_due[0]->total_shipping, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<?php } ?>
				<?php if ( get_option('wcvendors_vendor_give_taxes') ) { ?>
				<td class="line_cost no_ipad no_mob">
					<div class="view"><?php echo wc_price( $order_line_due[0]->tax, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<td class="line_cost no_ipad no_mob">
					<div class="view"></div>
				</td>
				<?php } ?>
			<?php } ?>
		<?php
		} else {
			?>
			<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
				<td class="line_cost" width="1%">
					<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			<?php } ?>
			<?php if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) { ?>
				<?php if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) { ?>
				<td class="line_cost" width="1%">
					<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<?php } ?>
				<?php if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) { ?>
				<td class="line_cost" width="1%">
					<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<td class="line_cost no_ipad no_mob">
					<div class="view"></div>
				</td>
				<?php } ?>
			<?php } else { ?>
				<?php if ( get_option('wcvendors_vendor_give_shipping') ) { ?>
				<td class="line_cost" width="1%">
					<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<?php } ?>
				<?php if ( get_option('wcvendors_vendor_give_taxes') ) { ?>
				<td class="line_cost" width="1%">
					<div class="view"><?php echo wc_price( 0, array( 'currency' => $order_currency ) ); ?></div>
				</td>
				<td class="line_cost no_ipad no_mob">
					<div class="view"></div>
				</td>
				<?php } ?>
			<?php } ?>
			<?php
		}
		if( apply_filters( 'wcfm_is_allow_total', true ) ) {
		?>
		<td class="line_cost total_cost no_ipad no_mob"><?php echo wc_price( $line_total, array( 'currency' => $order_currency ) ); ?></td>
		<?php
		}
  }
  
  // WCVendors Order Total Commission
  function wcvendors_order_total_commission( $order_id ) {
  	global $WCFM, $wpdb;
  	$gross_sale_order = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $this->vendor_id, '', '', $order_id );
  	$order = wc_get_order( $order_id );
  	$order_currency = $order->get_currency();
  	
  	$sql = "
  	SELECT SUM(total_due) as line_total,
	   SUM(total_shipping) as shipping,
       SUM(tax) as tax
       FROM {$wpdb->prefix}pv_commission
       WHERE order_id = " . $order_id . "
       AND vendor_id = " . $this->vendor_id;
    $order_due = $wpdb->get_results( $sql );
  	$total = $order_due[0]->line_total; 
  	if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
			if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) {
				$total += $order_due[0]->shipping; 
			}
			if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) {
				$total += $order_due[0]->tax; 
			}
		} else {
			if ( get_option('wcvendors_vendor_give_shipping') ) {
				$total += $order_due[0]->shipping; 
			}
			if ( get_option('wcvendors_vendor_give_taxes') ) {
				$total += $order_due[0]->tax; 
			}
  	}
		?>
		<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
			<tr>
				<td class="label"><?php _e( 'Line Commission', 'wc-frontend-manager' ); ?>:</td>
				<td>
					
				</td>
				<td class="total">
					<div class="view"><?php echo wc_price( $order_due[0]->line_total, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			</tr>
		<?php } ?>
		<?php if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) { ?>
			<?php if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) { ?>
			<tr>
				<td class="label"><?php _e( 'Shipping', 'wc-frontend-manager' ); ?>:</td>
				<td>
					
				</td>
				<td class="total">
					<div class="view"><?php echo wc_price( $order_due[0]->shipping, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			</tr>
			<?php } ?>
			<?php if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) { ?>
			<tr>
				<td class="label"><?php _e( 'Tax', 'wc-frontend-manager' ); ?>:</td>
				<td>
					
				</td>
				<td class="total">
					<div class="view"><?php echo wc_price( $order_due[0]->tax, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			</tr>
			<?php } ?>
		<?php } else { ?>
			<?php if ( get_option('wcvendors_vendor_give_shipping') ) { ?>
			<tr>
				<td class="label"><?php _e( 'Shipping', 'wc-frontend-manager' ); ?>:</td>
				<td>
					
				</td>
				<td class="total">
					<div class="view"><?php echo wc_price( $order_due[0]->shipping, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			</tr>
			<?php } ?>
			<?php if ( get_option('wcvendors_vendor_give_taxes') ) { ?>
			<tr>
				<td class="label"><?php _e( 'Tax', 'wc-frontend-manager' ); ?>:</td>
				<td>
					
				</td>
				<td class="total">
					<div class="view"><?php echo wc_price( $order_due[0]->tax, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
		<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
			<tr>
				<td class="label"><?php _e( 'Total Earning', 'wc-frontend-manager' ); ?>:</td>
				<td>
					
				</td>
				<td class="total">
					<div class="view"><?php echo wc_price( $total, array( 'currency' => $order_currency ) ); ?></div>
				</td>
			</tr>
		<?php } ?>
		<?php if( apply_filters( 'wcfm_is_allow_gross_total', true ) ) { ?>
			<tr>
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
  function wcvendors_generate_csv_url( $url, $order_id ) {
  	//$url = admin_url('admin.php?action=wcvendors_csv_download_per_order&orders_for_product=' . $order_id . '&nonce=' . wp_create_nonce('wcmp_vendor_csv_download_per_order'));
  	return $url;
  }
  
  // Report Vendor Filter
  function wcvendors_report_out_of_stock_query_from( $query_from, $stock ) {
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
  function wcvendors_reports_order_statuses( $order_status ) {
  	$order_status = array( 'completed', 'processing' );
  	return $order_status;
  }
  
  // WCVendor dashboard top seller query
  function wcvendors_dashboard_status_widget_top_seller_query( $query ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->wcv_get_vendor_products();
		if( empty($products) ) return array(0);
		$query['where'] .= "AND order_item_meta_2.meta_value in (" . implode( ',', $products ) . ")";
  	
  	return $query;
  }
  
  // Report Data Filter as per Vendor
  function wcvendors_reports_get_order_report_data( $result ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->wcv_get_vendor_products();
  	
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
  function wcv_get_vendor_products( $vendor_id = 0 ) {
  	if( !$vendor_id ) $vendor_id = $this->vendor_id;
  	
  	$post_count = 9999; //count_user_posts( $vendor_id, 'product' );
  	$post_loop_offset = 0;
  	$products_arr = array(0);
  	while( $post_loop_offset < $post_count ) {
			$args = array(
								'posts_per_page'   => apply_filters( 'wcfm_break_loop_offset', 10 ),
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
			$products = get_posts( $args );
			if(!empty($products)) {
				foreach($products as $product) {
					$products_arr[] = $product;
				}
			} else {
				break;
			}
			$post_loop_offset += apply_filters( 'wcfm_break_loop_offset', 10 );
		}
		
		return $products_arr;
  }
}