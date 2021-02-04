<?php
/**
 * WCFM Markeplace plugin core
 *
 * Plugin Ajax Controler
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Ajax {
	
	public $controllers_path;

	public function __construct() {
		global $WCFM, $WCFMa;
		
		// Vendor Order Status Update
		add_action( 'before_wcfm_order_status_update', array( &$this, 'wcfmmp_vendor_order_status_update' ), 10, 2 );
			
		// Store List Search
		add_action( 'wp_ajax_wcfmmp_stores_list_search', array($this, 'wcfmmp_stores_list_search') );
    add_action( 'wp_ajax_nopriv_wcfmmp_stores_list_search', array($this, 'wcfmmp_stores_list_search') );
    
    // Store List Map markers
		add_action( 'wp_ajax_wcfmmp_stores_list_map_markers', array($this, 'wcfmmp_stores_list_map_markers') );
    add_action( 'wp_ajax_nopriv_wcfmmp_stores_list_map_markers', array($this, 'wcfmmp_stores_list_map_markers') );
    
    // Zone Shipping Ajax
    add_action( 'wp_ajax_wcfmmp-get-shipping-zone', array( $this, 'wcfmmp_get_shipping_zone' ) );
    add_action( 'wp_ajax_wcfmmp-add-shipping-method', array( $this, 'wcfmmp_add_shipping_method' ) );
    add_action( 'wp_ajax_wcfmmp-toggle-shipping-method', array( $this, 'wcfmmp_toggle_shipping_method' ) );
    add_action( 'wp_ajax_wcfmmp-delete-shipping-method', array( $this, 'wcfmmp_delete_shipping_method' ) );
    add_action( 'wp_ajax_wcfmmp-update-shipping-method', array( $this, 'wcfmmp_update_shipping_method' ) );
		
    add_action( 'wp_ajax_wcfmmp-remove-cart-vendor-product', array( $this, 'wcfmmp_remove_cart_vendor_product' ) );
    add_action( 'wp_ajax_nopriv_wcfmmp-remove-cart-vendor-product', array( $this, 'wcfmmp_remove_cart_vendor_product' ) );
    
    // Vendor Store Offline
    add_action( 'wp_ajax_wcfm_vendor_store_offline', array( &$this, 'wcfm_vendor_store_offline' ) );
    
    // Vendor Store Online
    add_action( 'wp_ajax_wcfm_vendor_store_online', array( &$this, 'wcfm_vendor_store_online' ) );
	}
	
	/**
	 * Vendor Order - Commission Status Update
	 */
	function wcfmmp_vendor_order_status_update( $order_id, $order_status ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !wcfm_is_vendor() ) return;
		
		if( !$order_id ) {
			echo '{"status": false, "message": "' . __( 'No Order ID found.', 'wc-frontend-manager' ) . '"}';
			die;
		}
		
		if( $order_status == 'wc-refunded' ) {
			echo '{"status": false, "message": "' . __( 'This status not allowed, please go through Refund Request.', 'wc-multivendor-marketplace' ) . '"}';
			die;
		}
		
		if( $order_status == 'wc-shipped' ) {
			echo '{"status": false, "message": "' . __( 'This status not allowed, please go through Shipment Tracking.', 'wc-multivendor-marketplace' ) . '"}';
			die;
		}
		
		$wcfmmp_marketplace_options   = wcfm_get_option( 'wcfm_marketplace_options', array() );
		$order_sync  = isset( $wcfmmp_marketplace_options['order_sync'] ) ? $wcfmmp_marketplace_options['order_sync'] : 'no';
		if( $order_sync == 'yes' ) return;
		
		$vendor_id = $WCFMmp->vendor_id;
		
		if( $vendor_id ) {
			$order = wc_get_order( $order_id );
			$status = str_replace('wc-', '', $order_status);
			$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('commission_status' => $status), array('order_id' => $order_id, 'vendor_id' => $vendor_id), array('%s'), array('%d', '%d') );
			
			// Withdrawal Threshold check by Order Completed date 
			if( apply_filters( 'wcfm_is_allow_withdrwal_check_by_order_complete_date', false ) && ( $status == 'completed' ) ) {
				$wpdb->update( "{$wpdb->prefix}wcfm_marketplace_orders", array( 'created' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) ), array( 'order_id' => $order_id, 'vendor_id' => $vendor_id ), array('%s'), array('%d', '%d') );
			}
			
			
			do_action( 'wcfmmp_vendor_order_status_updated', $order_id, $order_status, $vendor_id );
			
			// Add Order Note for Log
			if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
				$shop_name = wcfm_get_vendor_store( absint($vendor_id) );
			} else {
				$shop_name = wcfm_get_vendor_store_name( absint($vendor_id) );
			}
			
			// Fetch Product ID
			$is_all_complete = true;
			if( apply_filters( 'wcfm_is_allow_itemwise_notification', true ) ) {
				$sql = 'SELECT product_id  FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
				$sql .= ' WHERE 1=1';
				$sql .= " AND `order_id` = " . $order_id;
				$sql .= " AND `vendor_id` = " . $vendor_id;
				$commissions = $wpdb->get_results( $sql );
				$product_id = 0;
				if( !empty( $commissions ) ) {
					foreach( $commissions as $commission ) {
						$product_id = $commission->product_id;
					
						$wcfm_messages = sprintf( __( 'Order item <b>%s</b> status updated to <b>%s</b> by <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_permalink($product_id) . '">' . get_the_title( $product_id ) . '</a>', $WCFMmp->wcfmmp_vendor->wcfmmp_vendor_order_status_name( $order_status ), $shop_name );
						
						add_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
						$is_customer_note = apply_filters( 'wcfm_is_allow_order_update_note_for_customer', '1' );
						$comment_id = $order->add_order_note( apply_filters( 'wcfm_order_item_status_update_message', $wcfm_messages, $order_id, $vendor_id, $product_id ), $is_customer_note );
						add_comment_meta( $comment_id, '_vendor_id', $vendor_id );
						remove_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
						
						if( apply_filters( 'wcfm_is_allow_order_update_note_for_admin', true ) ) {
							$wcfm_messages = apply_filters( 'wcfm_order_item_status_update_admin_message', sprintf( __( '<b>%s</b> order item <b>%s</b> status updated to <b>%s</b> by <b>%s</b>', 'wc-multivendor-marketplace' ), '#<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . wcfm_get_order_number( $order_id ) . '</a>', '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_permalink($product_id) . '">' . get_the_title( $product_id ) . '</a>', $WCFMmp->wcfmmp_vendor->wcfmmp_vendor_order_status_name( $order_status ), $shop_name ), $order_id, $vendor_id, $product_id );
							$WCFM->wcfm_notification->wcfm_send_direct_message( $vendor_id, 0, 0, 1, $wcfm_messages, 'status-update' );
						}
					}
				}
			} else {
				$wcfm_messages = sprintf( __( 'Order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), $WCFMmp->wcfmmp_vendor->wcfmmp_vendor_order_status_name( $order_status ), $shop_name );
				
				add_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
				$is_customer_note = apply_filters( 'wcfm_is_allow_order_update_note_for_customer', '1' );
				$comment_id = $order->add_order_note( apply_filters( 'wcfm_order_item_status_update_message', $wcfm_messages, $order_id, $vendor_id, 0 ), $is_customer_note);
				add_comment_meta( $comment_id, '_vendor_id', $vendor_id );
				remove_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
				
				if( apply_filters( 'wcfm_is_allow_order_update_note_for_admin', true ) ) {
					$wcfm_messages = apply_filters( 'wcfm_order_item_status_update_admin_message', sprintf( __( '<b>%s</b> order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), '#<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>', $WCFMmp->wcfmmp_vendor->wcfmmp_vendor_order_status_name( $order_status ), $shop_name ), $order_id, $vendor_id, 0 );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'status-update' );
				}
			}
			
			// Update Main Order status on all Commission Order Status Update
			if( in_array( $status, apply_filters( 'wcfm_change_main_order_on_child_order_statuses', array( 'completed', 'processing' ) ) ) && apply_filters( 'wcfm_is_allow_mark_complete_main_order_on_all_child_order_complete', true ) ) {
				if ( wc_is_order_status( 'wc-'.$status ) && $order_id ) {
					
					// Check is all vendor orders completed or not
					$is_all_complete = true;
					$sql = 'SELECT commission_status  FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
					$sql .= ' WHERE 1=1';
					$sql .= " AND `order_id` = " . $order_id;
					$commissions = $wpdb->get_results( $sql );
					if( !empty( $commissions ) ) {
						foreach( $commissions as $commission ) {
							if( $commission->commission_status != $status ) {
								$is_all_complete = false;
							}
						}
					}
					
					if( $is_all_complete ) {
						$order->update_status( $status, '', true );
						
						// Add Order Note for Log
						$wcfm_messages = sprintf( __( '<b>%s</b> order status updated to <b>%s</b>', 'wc-multivendor-marketplace' ), '#' . $order->get_order_number(), wc_get_order_status_name( $status ) );
						$is_customer_note = apply_filters( 'wcfm_is_allow_order_update_note_for_customer', '1' );
						
						$comment_id = $order->add_order_note( $wcfm_messages, $is_customer_note );
						
						$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'status-update' );
						
						do_action( 'woocommerce_order_edit_status', $order_id, $status );
						do_action( 'wcfm_order_status_updated', $order_id, $status );
					}
				}
			}
			
			echo '{"status": true, "message": "' . __( 'Order status updated.', 'wc-frontend-manager' ) . '"}';
			die;
		}
	}
	
	function wcfmmp_stores_list_search() {
		global $WCFM, $WCFMmp, $wpdb;
		
		//if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wcfmmp-stores-list-search' ) ) {
			//wp_send_json_error( __( 'Error: Nonce verification failed', 'wc-multivendor-marketplace' ) );
		//}


		$search_term     = isset( $_REQUEST['search_term'] ) ? sanitize_text_field( $_REQUEST['search_term'] ) : '';
		$search_category = isset( $_REQUEST['wcfmmp_store_category'] ) ? sanitize_text_field( $_REQUEST['wcfmmp_store_category'] ) : '';
		$pagination_base = isset( $_REQUEST['pagination_base'] ) ? sanitize_text_field( $_REQUEST['pagination_base'] ) : '';
		$paged           = 1; //isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
		$per_row         = isset( $_REQUEST['per_row'] ) ? absint( $_REQUEST['per_row'] ) : 3;
		$per_page        = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 10;
		$includes        = isset( $_REQUEST['includes'] ) ? sanitize_text_field( $_REQUEST['includes'] ) : '';
		$excludes        = isset( $_REQUEST['excludes'] ) ? sanitize_text_field( $_REQUEST['excludes'] ) : '';
		$orderby         = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'newness_asc';
		$has_orderby     = isset( $_REQUEST['has_orderby'] ) ? sanitize_text_field( $_REQUEST['has_orderby'] ) : '';
		$has_product     = isset( $_REQUEST['has_product'] ) ? sanitize_text_field( $_REQUEST['has_product'] ) : '';
		$sidebar         = isset( $_REQUEST['sidebar'] ) ? sanitize_text_field( $_REQUEST['sidebar'] ) : '';
		$theme           = isset( $_REQUEST['theme'] ) ? sanitize_text_field( $_REQUEST['theme'] ) : 'simple';
		$search_data     = array();
		
		if( isset( $_REQUEST['search_data'] ) )
			parse_str($_REQUEST['search_data'], $search_data);
		
		$length  = absint( $per_page );
		$offset  = ( $paged - 1 ) * $length;
		
		$search_data['excludes'] = $excludes;
		
		if( $includes ) $includes = explode(",", $includes);
		else $includes = array();

		$stores = $WCFMmp->wcfmmp_vendor->wcfmmp_search_vendor_list( true, $offset, $length, $search_term, $search_category, $search_data, $has_product, $includes );

		$template_args = apply_filters( 'wcfmmp_stores_args', array(
				'stores'          => $stores,
				'limit'           => $length,
				'offset'          => $offset,
				'paged'           => $paged,
				'includes'        => $includes,
				'excludes'        => $excludes,
				'image_size'      => 'full',
				'filter'          => 'yes',
				'search'          => 'yes',
				'category'        => 'yes',
				'country'         => 'yes',
				'state'           => 'yes',
				'has_product'     => $has_product,
				'search_query'    => $search_term,
				'search_category' => $search_category,
				'search'          => $search_term,
				'pagination_base' => $pagination_base,
				'orderby'         => $orderby,
				'has_orderby'     => $has_orderby,
				'per_row'         => $per_row,
				'sidebar'         => $sidebar,
				'theme'           => $theme,
				'search_data'     => $search_data
		), $_REQUEST, $search_data );
		
		ob_start();
		$WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-loop.php', $template_args );
		$content = ob_get_clean();

		wp_send_json_success( $content );
	}
	
	function wcfmmp_stores_list_map_markers() {
		global $WCFM, $WCFMmp, $wpdb;
		
		$search_term     = isset( $_REQUEST['search_term'] ) ? sanitize_text_field( $_REQUEST['search_term'] ) : '';
		$search_category = isset( $_REQUEST['wcfmmp_store_category'] ) ? sanitize_text_field( $_REQUEST['wcfmmp_store_category'] ) : '';
		$pagination_base = isset( $_REQUEST['pagination_base'] ) ? sanitize_text_field( $_REQUEST['pagination_base'] ) : '';
		$per_row         = isset( $_REQUEST['per_row'] ) ? sanitize_text_field( $_REQUEST['per_row'] ) : '3';
		$includes        = isset( $_REQUEST['includes'] ) ? sanitize_text_field( $_REQUEST['includes'] ) : '';
		$excludes        = isset( $_REQUEST['excludes'] ) ? sanitize_text_field( $_REQUEST['excludes'] ) : '';
		$has_product     = isset( $_REQUEST['has_product'] ) ? sanitize_text_field( $_REQUEST['has_product'] ) : '';
		$sidebar         = isset( $_REQUEST['sidebar'] ) ? sanitize_text_field( $_REQUEST['sidebar'] ) : '';
		//$filter_vendor   = isset( $_REQUEST['filter_vendor'] ) ? sanitize_text_field( $_REQUEST['filter_vendor'] ) : '';
		$search_data     = array();
		
		if( isset( $_POST['search_data'] ) )
			parse_str($_POST['search_data'], $search_data);
		
		$search_data['excludes'] = $excludes;
		
		if( $includes ) $includes = explode(",", $includes);
		else $includes = array();
		
		//if( $filter_vendor ) {
			//$includes = array( $filter_vendor );
		//}

		$stores = $WCFMmp->wcfmmp_vendor->wcfmmp_search_vendor_list( true, '', '', $search_term, $search_category, $search_data, $has_product, $includes );
		
		$store_list_markers = '';
		if ( !empty( $stores )  ) {
			foreach ( $stores as $store_id => $store_name ) {
				$store_user      = wcfmmp_get_store( $store_id );
				$store_info      = $store_user->get_shop_info();
				
				$store_name      = wcfm_get_vendor_store_name( $store_id );
				$store_name      = apply_filters( 'wcfmmp_store_title', esc_attr($store_name), $store_id );
				$store_url       = wcfmmp_get_store_url( $store_id );
				$store_address   = $store_user->get_address_string(); 
				$store_description = $store_user->get_shop_description();
				$gravatar        = $store_user->get_avatar();
				
				if( $store_address && ( ( $store_info['store_hide_address'] == 'yes' ) || !wcfm_vendor_has_capability( $store_id, 'vendor_address' ) ) ) {
					$store_address = '';
				}
				
				$store_lat    = isset( $store_info['store_lat'] ) ? esc_attr( $store_info['store_lat'] ) : 0;
				$store_lng    = isset( $store_info['store_lng'] ) ? esc_attr( $store_info['store_lng'] ) : 0;
				
				$info_window_content =  apply_filters( 'wcfmmp_map_store_info', "<div class='wcfm_map_info_wrapper'>" .
																																				"<a class='wcfm_map_info_logo' target='_blank' href='".$store_url."'><img width='80' src='".$gravatar."' /></a>" .
																																				"<div class='wcfm_map_info_content'>" .
																																				"<a class='wcfm_map_info_store' target='_blank' href='".$store_url."'>".$store_name."</a>" .
																																				"<p class='wcfm_map_info_addr'>".$store_address."</p>" .
																																				"</div>" .
																																				"</div>", $store_id, $store_user );
				
				$store_icon = apply_filters( 'wcfmmp_map_store_icon', $WCFMmp->plugin_url . 'assets/images/wcfmmp_map_icon.png', $store_id, $store_user );
				
				if( $store_lat && $store_lng ) {
					if( $store_list_markers ) $store_list_markers .= ", ";
					$store_list_markers .= '{"name": "' . $store_name . '", "lat": "' . $store_lat . '", "lang": "' . $store_lng. '", "url": "' . $store_url . '", "address": "' . $store_address . '", "gravatar": "' . $gravatar . '", "info_window_content": "'.$info_window_content.'", "icon": "'.$store_icon.'"}';
					
				}
			}
		}
		$store_list_markers = '['.$store_list_markers.']';
		
		wp_send_json_success( $store_list_markers );
	}
	
  /**
   * Get shipping zone
   *
   * @since 1.0.0
   *
   * @return void
   */
  public function wcfmmp_get_shipping_zone() {

    global $WCFM, $WCFMmp;
    if ( isset( $_POST['zoneID'] ) ) {
      $zones = WCFMmp_Shipping_Zone::get_zone( sanitize_text_field($_POST['zoneID']), sanitize_text_field($_POST['userID']) );
      //print_r($zones); die;
    } 
    $show_post_code_list = $show_state_list = $show_post_code_list = false; 
    //print_r($zones);die;
    $zone_id = $zones['data']['id']; 
    $user_id =  sanitize_text_field($_POST['userID']);
    $zone_locations = $zones['data']['zone_locations'];
    //print_r($zone_locations);
    $zone_location_types = array_column(array_map('wcfmmp_convert_to_array', $zone_locations) , 'type' , 'code');
    //print_r($zone_location_types);
    $selected_continent_codes = array_keys($zone_location_types, 'continent');
    if( !$selected_continent_codes ) $selected_continent_codes = array();
    
    $all_continents = WC()->countries->get_continents();
    $all_allowed_countries = WC()->countries->get_allowed_countries();

    $countries_key_by_continent = array_intersect_key($all_continents, array_flip($selected_continent_codes));
    if( $countries_key_by_continent ) {
    	$countries_key_by_continent = call_user_func_array('array_merge',array_column( $countries_key_by_continent, 'countries' ));
    } else {
    	$countries_key_by_continent = array();
    }
    
    $countries_by_continent = array_intersect_key($all_allowed_countries, array_flip($countries_key_by_continent));
    //print_r($all_allowed_countries);
    $selected_country_codes = array_keys($zone_location_types, 'country');
    $all_states = WC()->countries->get_states();
    
    $state_key_by_country = array();
    $state_key_by_country = array_intersect_key($all_states, array_flip($selected_country_codes));
    //print_r($state_key_by_country);die;
    array_walk($state_key_by_country, 'wcfmmp_state_key_alter');
    
    if( $state_key_by_country )
    	$state_key_by_country = call_user_func_array('array_merge', $state_key_by_country);
    
    
    
    if( apply_filters('wcfmmp_city_select_dropdown_enabled', false ) ) {
      global $wc_city_select;
      $all_cities = $wc_city_select->get_cities();
      $city_key_by_state = array();
      //print_r($all_cities);
      $selected_state_codes = array_keys($zone_location_types, 'state');
      //$selected_countywise_states = array();
      foreach ( $selected_state_codes as $key => $value ) {
        $country_state_arr = explode(':', $value);
        $exploded_country = $country_state_arr[0];
        $exploded_state =  $country_state_arr[1];
        if( isset( $all_cities[$exploded_country][$exploded_state] ) ) {
          $city_key_by_state = array_merge($city_key_by_state, $all_cities[$exploded_country][$exploded_state] );
        }

      }
      $city_key_by_state = array_combine($city_key_by_state, $city_key_by_state);
    }
    

    $show_limit_location_link = apply_filters( 'show_limit_location_link', (!in_array('postcode', $zone_location_types)) );
    $vendor_shipping_methods  = $zones['shipping_methods'];
    $show_country_list        = false; 
    $show_city_list           = apply_filters('wcfmmp_city_select_dropdown_enabled', false);

    if($show_limit_location_link) {
      if ( in_array('state', $zone_location_types) ) {
        $show_post_code_list = true;
      } elseif ( in_array('country', $zone_location_types) ) {
        $show_state_list = true;
        $show_post_code_list = true;
      } elseif (in_array('continent', $zone_location_types)) {
        $show_country_list = true;
        $show_state_list = true;
        $show_post_code_list = true;
          
      }
    }

    $want_to_limit_location = !empty($zones['locations']);
    $countries = $states = $cities = $postcodes = array();

    if($want_to_limit_location) {
       
      foreach($zones['locations'] as $each_location ) {
        switch ($each_location['type']) {
          case 'country':
            $countries[] = $each_location['code'];  
          break;
          case 'state':
            $states[] = $each_location['code'];
          break;
          case 'city':
            $cities[] = $each_location['code'];
          break;
          case 'postcode':
            $postcodes[] = $each_location['code'];
          break;
          default:
            break;
        }
      }
      $postcodes = implode(',', $postcodes);
    }    
    //print_r($states);

      ob_start();
    ?>

    <div class="zone-component">
      <div class="return-to-zone-list">
        <p>
          <a href="#" >&larr; <?php  _e('Back to Zone List', 'wc-multivendor-marketplace'); ?></a>
        </p>
      </div>
      <form action="" method="post">
        <div class="wcfmmp-form-group wcfmmp-clearfix">
          <p class="wcfm_title">
            <strong><?php  _e('Zone Name', 'wc-multivendor-marketplace'); ?></strong>
          </p>
          <label for="" class="screen-reader-text">
            <?php  _e('Zone Name', 'wc-multivendor-marketplace'); ?>
          </label>
          <p class="wcfm_title"> 
            <?php  _e($zones['data']['zone_name'], 'wc-multivendor-marketplace'); ?>
          </p>
        </div>
        <div class="wcfmmp-form-group wcfmmp-clearfix">
          <p class="wcfm_title">
          <strong>
            <?php  _e('Zone Location', 'wc-multivendor-marketplace'); ?>
          </strong>
          </p>
          <label for="" class="screen-reader-text">
            <?php  _e('Zone Location', 'wc-multivendor-marketplace'); ?>
          </label>
          <p class="wcfm_title">
            <?php  _e($zones['formatted_zone_location'], 'wc-multivendor-marketplace'); ?>
          </p>
        </div>
        
        <?php 
          $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array (
                "zone_id" => array(
                  'name' => 'wcfmmp_shipping_zone['. $zone_id .'][_zone_id]',
                  'type' => 'hidden', 
                  'class' => 'wcfm-hidden input-hidden wcfm_ele', 
                  'value' => $zone_id                    
                )
              )
            );
          $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array (
                "user_vendor_id" => array(
                  'name' => 'wcfmmp_shipping_zone['. $user_id .'][_user_id]',
                  'type' => 'hidden', 
                  'class' => 'wcfm-hidden input-hidden wcfm_ele', 
                  'value' => $user_id                    
                )
              )
            );
          if( $show_limit_location_link && $zone_id !== 0 ) {
            
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array (
                "limit_zone_location" => array(
                    'label' => __('Limit Zone Location', 'wc-multivendor-marketplace') ,
                    'name' => 'wcfmmp_shipping_zone['. $zone_id .'][_limit_zone_location]',
                    'type' => 'checkbox', 
                    'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 
                    'value' => 1, 
                    'label_class' => 'wcfm_title checkbox_title', 
                    'dfvalue' => $want_to_limit_location
                    )
              )
            );
          }
          if( $show_country_list ) {
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array(
                "select_zone_country" => array(
                  'label' => __('Select Specific Countries', 'wc-multivendor-marketplace') , 
                  'name' => 'wcfmmp_shipping_zone['. $zone_id .'][_select_zone_country]',
                  'type' => 'select', 
                  'class' => 'wcfm-select wcfm-select2 wcfm_ele select_zone_country_select hide_if_zone_not_limited', 
                  'label_class' => 'wcfm_title select_title hide_if_zone_not_limited', 
                  'attributes' => array( 'multiple' => 'multiple' ),
                  'options' => $countries_by_continent,
                  'value' => $countries
                )
              )
            );
          }
          if(  $show_state_list ) {
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array(
                "select_zone_states" => array(
                  'label' => __('Select Specific States', 'wc-multivendor-marketplace') , 
                  'name' => 'wcfmmp_shipping_zone['. $zone_id .'][_select_zone_states]',
                  'type' => 'select', 
                  'class' => 'wcfm-select wcfm-select2 wcfm_ele select_zone_states_select hide_if_zone_not_limited', 
                  'label_class' => 'wcfm_title select_title hide_if_zone_not_limited', 
                  'attributes' => array( 'multiple' => 'multiple' ),
                  'options' => $state_key_by_country,
                  'value' => $states
                )
              )
            );
          }
          
          if( $show_city_list ) {
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array(
                "select_zone_city" => array(
                  'label' => __('Select Specific City', 'wc-multivendor-marketplace'), 
                  'name' => 'wcfmmp_shipping_zone['. $zone_id .'][_select_zone_city]',
                  'type' => 'select', 
                  'class' => 'wcfm-select wcfm-select2 wcfm_ele select_zone_city_select hide_if_zone_not_limited', 
                  'label_class' => 'wcfm_title select_title hide_if_zone_not_limited', 
                   'attributes' => array( 'multiple' => 'multiple' ),
                  'options' => $city_key_by_state,
                  'value' => $cities 
                )
              )
            );
          }

          if( $show_post_code_list ) {
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array(
                "select_zone_postcodes" => array(
                  'label' => __('Set your postcode', 'wc-multivendor-marketplace'), 
                  'name' => 'wcfmmp_shipping_zone['. $zone_id .'][_select_zone_postcodes]',
                  'type' => 'text', 
                  'class' => 'wcfm-text wcfm_ele hide_if_zone_not_limited', 
                  'label_class' => 'wcfm_title wcfm_ele hide_if_zone_not_limited', 
                  'desc' => __('Postcodes containing wildcards (e.g. CB23*) or fully numeric ranges (e.g. 90210...99000) are also supported.', 'wc-multivendor-marketplace'),
                  'desc_class' => 'instructions hide_if_zone_not_limited',
                  'value' => $postcodes  
                )
              )
            );
          }
        ?>
      </form>
      <div class="wcfmmp-zone-method-wrapper">
        <div class="wcfmmp-zone-method-heading">
          <h2>
            <i aria-hidden="true" class="wcfmfa fa-truck"></i>
            <?php  _e('Shipping Method', 'wc-multivendor-marketplace'); ?>
          </h2> 
          <div class="wcfm-clearfix"></div>
          <span>
            <?php _e('Add your shipping method for appropiate zone', 'wc-multivendor-marketplace'); ?>
          </span> 
          <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfmmp-zone-method-content">
          <table class="wcfmmp-table zone-method-table">
            <thead>
              <tr>
                <th class="title"><?php  _e('Method Title', 'wc-multivendor-marketplace'); ?></th>
                <th class="enabled"><?php  _e('Status', 'wc-multivendor-marketplace'); ?></th> 
                <th class="description"><?php  _e('Description', 'wc-multivendor-marketplace'); ?></th>
              </tr>
            </thead> 
            <tbody>
              <?php 
                if(empty($vendor_shipping_methods)) { ?> 
                  <tr>
                    <td colspan="3">
                      <?php _e('No shipping method found', 'wc-multivendor-marketplace'); ?>
                    </td>
                  </tr>
                <?php 
                } else { 
                  //print_r($vendor_shipping_methods);
                  foreach ( $vendor_shipping_methods as $vendor_shipping_method ) {
                  ?>
                  <tr>
                    <td>
                      <?php _e($vendor_shipping_method['title'], 'wc-multivendor-marketplace' ); ?>
                      <div 
                        data-instance_id="<?php echo $vendor_shipping_method['instance_id']; ?>" 
                        data-method_id="<?php echo $vendor_shipping_method['id']; ?>" 
                        data-method-settings='<?php echo esc_attr( json_encode($vendor_shipping_method) ); ?>'
                        class="row-actions edit_del_actions"
                        
                      >
                        <span class="edit">
                          <a href="#" class="edit_shipping_method">
                            <?php _e('Edit', 'wc-multivendor-marketplace' ); ?>
                          </a> |
                        </span> 
                        <span class="delete">
                          <a class="delete_shipping_method" href="#">
                            <?php _e('Delete', 'wc-multivendor-marketplace' ); ?>
                          </a>
                        </span>
                      </div>
                    </td>
                    <td>
                      <?php 
                        $WCFM->wcfm_fields->wcfm_generate_form_field ( 
                          array (
                            "method_status" => array(
                                'label' => false ,
                                'name' => 'method_status',
                                'type' => 'checkbox', 
                                'class' => 'wcfm-checkbox method_status input-checkbox wcfm_ele', 
                                'value' => $vendor_shipping_method['instance_id'],
                                'dfvalue' => ( $vendor_shipping_method['enabled'] == "yes" ) ? $vendor_shipping_method['instance_id'] : 0
                                )
                          )
                        );
                      ?>
                    </td>
                    <td>
                      <?php _e($vendor_shipping_method['settings']['description'], 'wc-multivendor-marketplace' ); ?>
                    </td>
                  </tr>
                <?php 
                  }
                }
              ?>
            </tbody>
          </table>
        </div>
        <div class="wcfmmp-zone-method-footer">
          <a href="#" class="wcfmmp-btn wcfmmp-btn-theme wcfmmp-zone-method-add-btn">
            <i class="wcfmfa fa-plus"></i> 
            <?php _e('Add Shipping Method', 'wc-multivendor-marketplace') ?>
          </a>
        </div>
        <?php 
          $WCFMmp->template->get_template( 'shipping/wcfmmp-view-edit-method-popup.php' );
          $WCFMmp->template->get_template( 'shipping/wcfmmp-view-add-method-popup.php' );
        ?>
      </div>
    </div>


    <?php    
    $zone_html['html'] = ob_get_clean();
    $zone_html['states'] = json_encode($states);
    $zone_html['cities'] = json_encode($cities);
    wp_send_json_success( $zone_html );
  }
  
  /**
    * Add shipping Method
    *
    * @since 1.0.0
    *
    * @return void
    */
  public function wcfmmp_add_shipping_method() {
    $data = array(
                'zone_id'   => sanitize_text_field($_POST['zoneID']),
                'method_id' => sanitize_text_field($_POST['method']),
                'user_id' => isset($_POST['userID']) ? sanitize_text_field($_POST['userID']) : 0
            );

    $result = WCFMmp_Shipping_Zone::add_shipping_methods( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() , 'wc-multivendor-marketplace' );
		}

		wp_send_json_success( __( 'Shipping method added successfully', 'wc-multivendor-marketplace' ) );
    
  }
  
  /**
    * Toggle shipping Method
    *
    * @since 1.0.0
    *
    * @return void
    */
  public function wcfmmp_toggle_shipping_method() {
    //print_r($_POST);
    $data = array(
       'instance_id' => sanitize_text_field($_POST['instance_id']),
       'zone_id'     => sanitize_text_field($_POST['zoneID']),
       'user_id'     => sanitize_text_field($_POST['userID']),
       'checked'     => ( $_POST['checked'] == 'true' ) ? 1 : 0
    );
    $result = WCFMmp_Shipping_Zone::toggle_shipping_method( $data );
    if ( is_wp_error( $result ) ) {
      wp_send_json_error( $result->get_error_message() );
    }
    $message = $data['checked'] ? __( 'Shipping method enabled successfully',  'wc-multivendor-marketplace' ) : __( 'Shipping method disabled successfully',  'wc-multivendor-marketplace' );
    wp_send_json_success( $message );
  }
  
  /**
    * Delete shipping Method
    *
    * @since 1.0.0
    *
    * @return void
    */
  public function wcfmmp_delete_shipping_method() {
    $data = array(
      'zone_id'     => sanitize_text_field($_POST['zoneID']),
      'instance_id' => sanitize_text_field($_POST['instance_id']),
      'user_id'     => sanitize_text_field($_POST['userID'])
    );

    $result = WCFMmp_Shipping_Zone::delete_shipping_methods( $data );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message() , 'wc-multivendor-marketplace' );
    }
    $resp['msg'] = __( 'Shipping method deleted', 'wc-multivendor-marketplace' );
    $resp['user_id'] = $data['user_id'];
    wp_send_json_success( $resp );
  }
  
  /**
    * Update shipping Method
    *
    * @since 1.0.0
    *
    * @return void
    */
  public function wcfmmp_update_shipping_method() {
    //print_r($_POST); die;
    $args =  wp_unslash($_POST['args']);
    if ( empty( $args['settings']['title'] ) ) {
      wp_send_json_error( __( 'Shipping title must be required', 'wc-multivendor-marketplace' ) );
    }

    $result = WCFMmp_Shipping_Zone::update_shipping_method( $args );
    if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() , 'wc-multivendor-marketplace' );
    }
    $resp['msg'] = __( 'Shipping method updated', 'wc-multivendor-marketplace' );
    $resp['user_id'] = $args['user_id'];
    wp_send_json_success( $resp );
  }
  
  public function wcfmmp_remove_cart_vendor_product() {
    global $WCFM;
    $removed_products = array();
    
    foreach ( WC()->cart->get_cart() as $cart_item_key => $item) {
      if ($item['data']->needs_shipping()) {
        //print_r($item['data']->name); die;
        $product_id = $item['product_id'];
        $vendor_id = wcfm_get_vendor_id_by_post( $product_id );
        //WC()->cart->remove_cart_item($cart_item_key);
        $vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
        if( $vendor_data && !empty( $vendor_data['shop_shipping_countries'] ) ) {
          $shop_shipping_countries = $vendor_data['shop_shipping_countries'];
          $cust_shipping_country = WC()->customer->get_shipping_country();
          if( !in_array( $cust_shipping_country, $shop_shipping_countries )  ) {
            $removed_products[] = $item['data']->name;
            WC()->cart->remove_cart_item($cart_item_key);
            continue;
          }
        }
      }
    }
    $response = array ('items_removed'=> false );
    if(!empty($removed_products)) {
      $response['removed_products'] = implode(', ', $removed_products);
      $response['items_removed'] = true;
    }
    
    wp_send_json_success($response);
    //die;
  }
  
  /**
	 * Vendor Store Offline
	 */
	function wcfm_vendor_store_offline() {
		global $WCFM, $_POST, $wpdb;
		
		if( isset( $_POST['memberid'] ) ) {
			$member_id = absint( $_POST['memberid'] );
			$vendor_store = wcfm_get_vendor_store( $member_id );
			
			update_user_meta( $member_id, '_wcfm_store_offline', 'yes' );
			
			// Vendor Notification
			$wcfm_messages = sprintf( __( 'Your Store: <b>%s</b> has been set off-line.', 'wc-multivendor-marketplace' ), $vendor_store );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'vendor-disable' );
			
			do_action( 'wcfm_store_offline_after', $member_id );
			
			echo '{"status": true, "message": "' . __( 'Vendor Store Off-line.', 'wc-multivendor-marketplace' ) . '"}';
			die;
		}
	}
	
	/**
	 * Vendor Store Offline
	 */
	function wcfm_vendor_store_online() {
		global $WCFM, $_POST, $wpdb;
		
		if( isset( $_POST['memberid'] ) ) {
			$member_id = absint( $_POST['memberid'] );
			$vendor_store = wcfm_get_vendor_store( $member_id );
			
			delete_user_meta( $member_id, '_wcfm_store_offline' );
			
			// Vendor Notification
			$wcfm_messages = sprintf( __( 'Your Store: <b>%s</b> has been set on-line.', 'wc-multivendor-marketplace' ), $vendor_store );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'vendor-enable' );
			
			do_action( 'wcfm_store_online_after', $member_id );
			
			echo '{"status": true, "message": "' . __( 'Vendor Store On-line.', 'wc-multivendor-marketplace' ) . '"}';
			die;
		}
	}
}