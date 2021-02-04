<?php

/**
 * WCFM plugin core
 *
 * Marketplace Dokan Support
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   3.3.0
 */
 
class WCFM_Dokan {
	
	private $vendor_id;
	
	public function __construct() {
    global $WCFM;
    
    if( wcfm_is_vendor() ) {
    	
    	$this->vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    	
    	// Store Identity
    	add_filter( 'wcfm_store_logo', array( &$this, 'dokan_store_logo' ) );
    	add_filter( 'wcfm_store_name', array( &$this, 'dokan_store_name' ) );
    	
    	// WCFM Menu Filter
    	add_filter( 'wcfm_menus', array( &$this, 'dokan_wcfm_menus' ), 30 );
    	add_filter( 'wcfm_add_new_product_sub_menu', array( &$this, 'dokan_add_new_product_sub_menu' ) );
    	add_filter( 'wcfm_add_new_coupon_sub_menu', array( &$this, 'dokan_add_new_coupon_sub_menu' ) );
    	
			// Allow Vendor user to manage product from catalog
			add_filter( 'wcfm_allwoed_user_roles', array( &$this, 'allow_dokan_vendor_role' ) );
			add_filter( 'wcfm_allwoed_vendor_user_roles', array( &$this, 'allow_dokan_vendor_role' ) );
			
			// Filter Vendor Products
			add_filter( 'wcfm_products_args', array( &$this, 'dokan_products_args' ) );
			add_filter( 'get_booking_products_args', array( $this, 'dokan_products_args' ) );
			add_filter( 'get_appointment_products_args', array( $this, 'dokan_products_args' ) );
			add_filter( 'wpjmp_job_form_products_args', array( &$this, 'dokan_products_args' ) );
			add_filter( 'wpjmp_admin_job_form_products_args', array( &$this, 'dokan_products_args' ) );
			
			// Listing Filter for specific vendor
			add_filter( 'wcfm_articles_args', array( &$this, 'dokan_listing_args' ) );
    	add_filter( 'wcfm_listing_args', array( $this, 'dokan_listing_args' ), 20 );
    	add_filter( "woocommerce_product_export_product_query_args", array( &$this, 'dokan_listing_args' ), 100 );
    	
    	// Customers args
    	if( apply_filters( 'wcfm_is_allow_order_customers_to_vendors', true ) ) {
    		add_filter( 'wcfm_get_customers_args', array( &$this, 'dokan_filter_customers' ), 20 );
    	}
    	
    	// Booking Filter
			add_filter( 'wcfm_wcb_include_bookings', array( &$this, 'dokan_wcb_include_bookings' ) );
			
			// Manage Vendor Product Permissions
			add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'dokan_product_manage_vendor_association' ), 10, 2 );
			add_action( 'gmw_location_form_default_location', array( &$this, 'dokan_geo_locator_default_address' ), 10, 2 );
			
			// Manage Vendor Product Export Permissions - 2.4.2
			add_filter( 'product_type_selector', array( $this, 'dokan_filter_product_types' ), 98 );
			add_filter( 'woocommerce_product_export_row_data', array( &$this, 'dokan_product_export_row_data' ), 100, 2 );
			
			// Vendor Status restriction
			add_filter( 'wcfm_is_allow_add_products', array( &$this, 'dokan_is_allow_add_products' ), 600 );
			add_filter( 'wcfm_is_allow_edit_products', array( &$this, 'dokan_is_allow_add_products' ), 600 );
			
			// Vendor Publish Product Status check
			add_filter( 'wcfm_is_allow_publish_products', array( &$this, 'dokan_is_allow_publish_products' ), 600 );
			
			// Filter Vendor Coupons
			add_filter( 'wcfm_coupons_args', array( &$this, 'dokan_coupons_args' ) );
			
			// Manage Vendor Coupon Permission
			add_filter( 'wcfm_coupon_types', array( &$this, 'dokan_coupon_types' ) );
			
			// Manage Order Details Permission
			add_filter( 'wcfm_allow_order_details', array( &$this, 'dokan_is_allow_order_details' ) );
			add_filter( 'wcfm_allow_order_customer_details', array( &$this, 'dokan_is_allow_order_customer_details' ) );
			add_filter( 'wcfm_order_details_shipping_line_item', array( &$this, 'dokan_is_allow_order_details_shipping_line_item' ) );
			add_filter( 'wcfm_order_details_tax_line_item', array( &$this, 'dokan_is_allow_order_details_tax_line_item' ) );
			add_filter( 'wcfm_order_details_line_total_head', array( &$this, 'dokan_is_allow_order_details_line_total_head' ) );
			add_filter( 'wcfm_order_details_line_total', array( &$this, 'dokan_is_allow_order_details_line_total' ) );
			add_filter( 'wcfm_order_details_tax_total', array( &$this, 'dokan_is_allow_order_details_tax_total' ) );
			add_filter( 'wcfm_order_details_fee_line_item', array( &$this, 'dokan_is_allow_order_details_fee_line_item' ) );
			add_filter( 'wcfm_order_details_refund_line_item', array( &$this, 'dokan_is_allow_order_details_refund_line_item' ) );
			add_filter( 'wcfm_order_details_coupon_line_item', array( &$this, 'dokan_is_allow_order_details_coupon_line_item' ) );
			add_filter( 'wcfm_order_details_total', array( &$this, 'dokan_is_allow_wcfm_order_details_total' ) );
			add_action ( 'wcfm_order_totals_after_total', array( &$this, 'dokan_order_total_commission' ) );
			//add_filter( 'wcfm_generate_csv_url', array( &$this, 'dokan_generate_csv_url' ), 10, 2 );
			
			// Report Filter
			add_filter( 'wcfm_report_out_of_stock_query_from', array( &$this, 'dokan_report_out_of_stock_query_from' ), 100, 2 );
			add_filter( 'woocommerce_reports_order_statuses', array( &$this, 'dokan_reports_order_statuses' ) );
			add_filter( 'woocommerce_dashboard_status_widget_top_seller_query', array( &$this, 'dokan_dashboard_status_widget_top_seller_query'), 100 );
			//add_filter( 'woocommerce_reports_get_order_report_data', array( &$this, 'dokan_reports_get_order_report_data'), 100 );
			
			add_action( 'pre_get_posts', array( &$this, 'restrict_dokan_filter_orders_for_current_vendor' ), 9 );
		}
  }
  
  // WCFM Dokan Store Logo
  function dokan_store_logo( $store_logo ) {
  	$user_id = $this->vendor_id;
  	$vendor_data = get_user_meta( $user_id, 'dokan_profile_settings', true );
  	$gravatar       = isset( $vendor_data['gravatar'] ) ? absint( $vendor_data['gravatar'] ) : 0;
  	$gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : '';

		if ( !empty( $gravatar_url ) ) {
			$store_logo = $gravatar_url;
			$shop_link       = dokan_get_store_url( $user_id );
			if( $shop_link ) {
				$store_logo = '<a class="wcfm_store_logo_icon" href="' . $shop_link . '" target="_blank"><img src="' . $store_logo . '" alt="Store Logo" /></a>';
			}
		}
  	return $store_logo;
  }
  
  // WCFM Dokan Store Name
  function dokan_store_name( $store_name ) {
  	$user_id = $this->vendor_id;
  	$vendor_data = get_user_meta( $user_id, 'dokan_profile_settings', true );
  	$shop_name     = wcfm_get_option( 'wcfm_my_store_label', __( 'My Store', 'wc-frontend-manager' ) );  //isset( $vendor_data['store_name'] ) ? esc_attr( $vendor_data['store_name'] ) : '';
  	if( $shop_name ) $store_name = $shop_name;
  	$shop_link       = dokan_get_store_url( $user_id );
  	if( $shop_name ) { $store_name = '<a target="_blank" href="' . apply_filters('dokan_vendor_shop_permalink', $shop_link) . '">' . $shop_name . '</a>'; }
  	else { $store_name = '<a target="_blank" href="' . apply_filters('dokan_vendor_shop_permalink', $shop_link) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
  	return $store_name;
  }
  
  // WCFM Dokanendors Menu
  function dokan_wcfm_menus( $menus ) {
  	global $WCFM;
  	
  	//if( !current_user_can( 'edit_products' ) ) unset( $menus['wcfm-products'] );
  	//if( !current_user_can( 'edit_shop_coupons' ) ) unset( $menus['wcfm-coupons'] );
  	
  	return $menus;
  }
  
  // Dokan Add New Product Sub menu
  function dokan_add_new_product_sub_menu( $has_new ) {
  	//if( !current_user_can( 'edit_products' ) ) $has_new = false;
  	return $has_new;
  }
  
  // Dokan Add New Coupon Sub menu
  function dokan_add_new_coupon_sub_menu( $has_new ) {
  	//if( !current_user_can( 'edit_shop_coupons' ) ) $has_new = false;
  	return $has_new;
  }
  
  function allow_dokan_vendor_role( $allowed_roles ) {
  	if( wcfm_is_vendor() ) $allowed_roles[] = 'seller';
  	return $allowed_roles;
  }
  
  function dokan_products_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // Dokan Listing args
	function dokan_listing_args( $args ) {
  	$args['author'] = $this->vendor_id;
  	return $args;
  }
  
  /**
   * Dokan filter customers
   */
  function dokan_filter_customers( $args ) {
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
  	$sql = 'SELECT order_id FROM ' . $wpdb->prefix . 'dokan_orders';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `seller_id` = {$this->vendor_id}";
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
  function dokan_wcb_include_bookings( ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$vendor_products = $this->dokan_get_vendor_products( $this->vendor_id );
		
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
  
  // Product Vendor association on Product save
  function dokan_product_manage_vendor_association( $new_product_id, $wcfm_products_manage_form_data ) {
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
  function dokan_geo_locator_default_address( $gmw_saved_location, $args ) {
  	global $WCFM;
  	
  	if ( empty( $gmw_saved_location ) ) {
			
			$vendor_data = get_user_meta( $this->vendor_id, 'dokan_profile_settings', true );
			$address         = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
			
			$street_1 = isset( $vendor_data['address']['street_1'] ) ? $vendor_data['address']['street_1'] : '';
			$street_2 = isset( $vendor_data['address']['street_2'] ) ? $vendor_data['address']['street_2'] : '';
			$city     = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
			$state    = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
			$zip      = isset( $vendor_data['address']['zip'] ) ? $vendor_data['address']['zip'] : '';
			$country  = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
			
			$map_location   = isset( $vendor_data['location'] ) ? esc_attr( $vendor_data['location'] ) : '';
			$map_address    = isset( $vendor_data['find_address'] ) ? esc_attr( $vendor_data['find_address'] ) : '';
			
			$store_lat = '';
			$store_lng = '';
			if( $map_location ) {
				$map_locations = explode( ",", $map_location );
				if( is_array( $map_locations ) ) {
					if( isset( $map_locations[0] ) ) $store_lat = $map_locations[0];
					if( isset( $map_locations[1] ) ) $store_lng = $map_locations[1];
				}
			}
			
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
  
  // Remove WC Vendors Buggy filter
  function dokan_filter_product_types( $types ) {
  	remove_all_filters( 'product_type_selector', 99 );
  	return $types;
  }
  
  // Product Export Data Filter
  function dokan_product_export_row_data( $row, $product ) {
  	global $WCFM;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->dokan_get_vendor_products();
		
		if( !in_array( $product->get_ID(), $products ) ) return array();
		
		return $row;
  }
  
  // Vendor Status check
  function dokan_is_allow_add_products( $is_allow ) {
  	if ( ! dokan_is_seller_enabled( $this->vendor_id ) ) {
			//echo dokan_seller_not_enabled_notice();
			$is_allow = false;
		}
  	return $is_allow;
  }
  
  // Vendor Product Publish Status check
  function dokan_is_allow_publish_products( $is_allow ) {
  	if( apply_filters( 'wcfm_is_allow_respect_dokan_publish_product_settings', false ) ) {
			if ( ! dokan_is_seller_trusted( $this->vendor_id ) ) {
				$is_allow = false;
			}
		}
  	return $is_allow;
  }
  
  // Coupons Args
  function dokan_coupons_args( $args ) {
  	if( wcfm_is_vendor() ) $args['author'] = $this->vendor_id;
  	return $args;
  }
  
  // Coupon Types
  function dokan_coupon_types( $types ) {
  	$wcmp_coupon_types = array( 'percent', 'fixed_product' );
  	foreach( $types as $type => $label ) 
  		if( !in_array( $type, $wcmp_coupon_types ) ) unset( $types[$type] );
  	return $types;
  } 
  
  // Order Status details
  function dokan_is_allow_order_details( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Customer Details
  function dokan_is_allow_order_customer_details( $allow ) {
  	return $allow;
  }
  
  // Order Details Shipping Line Item
  function dokan_is_allow_order_details_shipping_line_item( $allow ) {
  	//if ( !WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) $allow = false;
  	//$allow = false;
  	return $allow;
  }
  
  // Order Details Tax Line Item
  function dokan_is_allow_order_details_tax_line_item( $allow ) {
  	//if ( !WC_Vendors::$pv_options->get_option( 'give_tax' ) ) $allow = false;
  	//$allow = false;
  	return $allow;
  }
  
  // Order Total Line Item Head
  function dokan_is_allow_order_details_line_total_head( $allow ) {
  	//$allow = false;
  	return $allow;
  }
  
  // Order Total Line Item
  function dokan_is_allow_order_details_line_total( $allow ) {
  	//$allow = false;
  	return $allow;
  }
  
  // Order Details Tax Total
  function dokan_is_allow_order_details_tax_total( $allow ) {
  	//if ( !WC_Vendors::$pv_options->get_option( 'give_tax' ) ) $allow = false;
  	//$allow = false;
  	return $allow;
  }
  
  // Order Details Fee Line Item
  function dokan_is_allow_order_details_fee_line_item( $allow ) {
  	//$allow = false;
  	return $allow;
  }
  
  // Order Details Coupon Line Item
  function dokan_is_allow_order_details_coupon_line_item( $allow ) {
  	$allow = false;
  	return $allow;
  }
  
  // Order Details Refunded Line Item
  function dokan_is_allow_order_details_refund_line_item( $allow ) {
  	//$allow = false;
  	return $allow;
  }
  
  // Order Details Total
  function dokan_is_allow_wcfm_order_details_total( $allow ) {
  	//$allow = false;
  	return $allow;
  }
  
  // Dokanendors Order Total Commission
  function dokan_order_total_commission( $order_id ) {
  	global $WCFM, $wpdb;
  	
  	if( !apply_filters( 'wcfm_is_allow_view_commission', true ) ) return;
  	
  	$order = wc_get_order( $order_id );
  	$order_currency = $order->get_currency();
  	$get_commission_order = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_order( $order_id );
  	
  	?>
		<tr>
		  <?php if( apply_filters( 'wcfm_order_details_total_earning_invoice', false ) ) { ?>
		  <td class="no-borders"></td>
		  <?php } ?>
			<td class="label"><?php _e( 'Total Earning', 'wc-frontend-manager' ); ?>:</td>
			<td></td>
			<td class="total">
				<div class="view"><?php echo wc_price( $get_commission_order, array( 'currency' => $order_currency ) ); ?></div>
			</td>
		</tr>
		<?php
  }
  
  // CSV Export URL
  function dokan_generate_csv_url( $url, $order_id ) {
  	//$url = admin_url('admin.php?action=dokan_csv_download_per_order&orders_for_product=' . $order_id . '&nonce=' . wp_create_nonce('wcmp_vendor_csv_download_per_order'));
  	return $url;
  }
  
  // Report Vendor Filter
  function dokan_report_out_of_stock_query_from( $query_from, $stock ) {
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
  function dokan_reports_order_statuses( $order_status ) {
  	$order_status = array( 'completed', 'processing', 'on-hold' );
  	return $order_status;
  }
  
  // Dokanendor dashboard top seller query
  function dokan_dashboard_status_widget_top_seller_query( $query ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->dokan_get_vendor_products();
  	if( empty($products) ) return array(0);
		$query['where'] .= "AND order_item_meta_2.meta_value in (" . implode( ',', $products ) . ")";
  	
  	return $query;
  }
  
  // Report Data Filter as per Vendor
  function dokan_reports_get_order_report_data( $result ) {
  	global $WCFM, $wpdb, $_POST;
  	
  	$user_id = $this->vendor_id;
  	
  	$products = $this->dokan_get_vendor_products();
  	
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
  function dokan_get_vendor_products( $vendor_id = 0 ) {
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
  
  function restrict_dokan_filter_orders_for_current_vendor( $query ) {
  	remove_action( 'pre_get_posts', 'dokan_filter_orders_for_current_vendor' );
  	return $query;
  }
}