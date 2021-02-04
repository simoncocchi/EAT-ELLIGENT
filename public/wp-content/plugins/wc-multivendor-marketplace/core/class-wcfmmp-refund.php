<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Refund
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Refund {

	public function __construct() {
		global $WCFM, $WCFMmp;
		
		add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_refund_query_vars' ), 20 );
		add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_refund_endpoint_title' ), 20, 2 );
		add_action( 'init', array( &$this, 'wcfm_refund_init' ), 20 );
		
		// Refund Endpoint Edit
		add_filter( 'wcfm_endpoints_slug', array( $this, 'refund_wcfm_endpoints_slug' ) );
		
		// Refund menu on WCfM dashboard
		if( apply_filters( 'wcfm_is_allow_refund', true ) && apply_filters( 'wcfm_is_allow_refund_requests', true ) ) {
			add_filter( 'wcfm_menus', array( &$this, 'wcfm_refund_menus' ), 30 );
		}
		
		// Refund Load Scripts
		add_action( 'wcfm_load_scripts', array( &$this, 'load_scripts' ), 30 );
		add_action( 'after_wcfm_load_scripts', array( &$this, 'load_scripts' ), 30 );
		
		// Refund Load Styles
		add_action( 'wcfm_load_styles', array( &$this, 'load_styles' ), 30 );
		add_action( 'after_wcfm_load_styles', array( &$this, 'load_styles' ), 30 );
		
		// Refund Load views
		add_action( 'wcfm_load_views', array( &$this, 'load_views' ), 30 );
		
		// Refund Ajax Controllers
		add_action( 'after_wcfm_ajax_controller', array( &$this, 'ajax_controller' ) );
		add_action( 'wp_ajax_nopriv_wcfm_ajax_controller', array( &$this, 'ajax_controller' ) );
		
		// Generate Refund Form Html
    add_action('wp_ajax_wcfmmp_refund_requests_form_html', array( &$this, 'wcfmmp_refund_requests_form_html' ) );
		
    if( wcfm_is_vendor() ) {
    	add_filter( 'wcfmmarketplace_orders_actions', array( &$this, 'wcfmmp_refund_orders_actions' ), 100, 4 );
    }
    
		// WC My Account Order action - Refund
		add_filter( 'woocommerce_my_account_my_orders_actions', array( &$this, 'wcfmmp_myaccount_refund_order_action' ), 110, 2 );
		
		//enqueue scripts
		add_action('wp_enqueue_scripts', array(&$this, 'wcfm_refund_scripts'));
		//enqueue styles
		add_action('wp_enqueue_scripts', array(&$this, 'wcfm_refund_styles'));
	}
	
	/**
   * Refund Query Var
   */
  function wcfm_refund_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_refund_vars = array(
			'wcfm-refund-requests'                 => ! empty( $wcfm_modified_endpoints['wcfm-refund-requests'] ) ? $wcfm_modified_endpoints['wcfm-refund-requests'] : 'refund-requests',
		);
		
		$query_vars = array_merge( $query_vars, $query_refund_vars );
		
		return $query_vars;
  }
  
  /**
   * Refund End Point Title
   */
  function wcfm_refund_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-refund-requests' :
				$title = __( 'Refund Requests', 'wc-multivendor-marketplace' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * Refund Endpoint Intialize
   */
  function wcfm_refund_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_refund' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_refund', 1 );
		}
  }
  
  /**
	 * Refund Endpoiint Edit
	 */
	function refund_wcfm_endpoints_slug( $endpoints ) {
		
		$refund_endpoints = array(
													'wcfm-refund-requests'          => 'refund-requests',
													);
		
		$endpoints = array_merge( $endpoints, $refund_endpoints );
		
		return $endpoints;
	}
	
	/**
   * WCFM Refund Menu
   */
  function wcfm_refund_menus( $menus ) {
  	global $WCFM;
  		
  	//if( !wcfm_is_vendor() ) {
			$menus = array_slice($menus, 0, 3, true) +
													array( 'wcfm-refund-requests' => array( 'label'  => __( 'Refund', 'wc-multivendor-marketplace' ),
																																	 'url'        => wcfm_refund_requests_url(),
																																	 'icon'       => 'retweet',
																																	 'menu_for'   => 'admin',
																																	 'priority'   => 69.5
																																	) )	 +
														array_slice($menus, 3, count($menus) - 3, true) ;
		//}
  	return $menus;
  }  
  
  /**
   * Refund Scripts
   */
  public function load_scripts( $end_point ) {
	  global $WCFM, $WCFMmp;
    
	  switch( $end_point ) {
	  	case 'wcfm-refund-requests':
	  		$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_datatable_download_lib();
      	wp_enqueue_script( 'wcfmmp_refund_requests_js', $WCFMmp->library->js_lib_url . 'refund/wcfmmp-script-refund-requests.js', array('jquery'), $WCFMmp->version, true );
      	
      	$wcfm_screen_manager_data = array();
      	if( wcfm_is_vendor() ) {
      		$wcfm_screen_manager_data[3] = 'yes';
      	}
	    	$wcfm_screen_manager_data = apply_filters( 'wcfm_refund_screen_manage', $wcfm_screen_manager_data );
	    	$wcfm_screen_manager_data = apply_filters( 'wcfm_screen_manager_data_columns', $wcfm_screen_manager_data, 'refund' );
	    	wp_localize_script( 'wcfmmp_refund_requests_js', 'wcfm_refund_screen_manage', $wcfm_screen_manager_data );
      break;
      
      case 'wcfm-orders':
      	wp_enqueue_script( 'wcfmmp_refund_requests_form_js', $WCFMmp->library->js_lib_url . 'refund/wcfmmp-script-refund-requests-popup.js', array('jquery'), $WCFMmp->version, true );
      	// Localized Script
        $wcfm_messages = get_wcfm_refund_requests_messages();
			  wp_localize_script( 'wcfmmp_refund_requests_form_js', 'wcfm_refund_requests_messages', $wcfm_messages );
      break;
	  }
	}
	
	/**
   * Refund Styles
   */
	public function load_styles( $end_point ) {
	  global $WCFM, $WCFMmp;
		
	  switch( $end_point ) {
	  	case 'wcfm-refund-requests':
		    wp_enqueue_style( 'wcfmu_refund_requests_css',  $WCFMmp->library->css_lib_url . 'refund/wcfmmp-style-refund-requests.css', array(), $WCFMmp->version );
		  break;
		  
		  case 'wcfm-orders':
		  	wp_enqueue_style( 'wcfmmp_refund_requests_form_css',  $WCFMmp->library->css_lib_url . 'refund/wcfmmp-style-refund-requests-popup.css', array(), $WCFMmp->version );
		  break;
	  }
	}
	
	/**
   * Refund Views
   */
  public function load_views( $end_point ) {
	  global $WCFM, $WCFMmp;
	  
	  switch( $end_point ) {
      case 'wcfm-refund-requests':
        $WCFMmp->template->get_template( 'refund/wcfmmp-view-refund-requests.php' );
      break;
	  }
	}
	
	/**
   * Refund Ajax Controllers
   */
  public function ajax_controller() {
  	global $WCFM, $WCFMmp;
  	
  	$controllers_path = $WCFMmp->plugin_path . 'controllers/refund/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = sanitize_text_field( $_POST['controller'] );
  		
  		switch( $controller ) {
  			case 'wcfm-refund-requests':
					include_once( $controllers_path . 'wcfmmp-controller-refund-requests.php' );
					new WCFMmp_Refund_Requests_Controller();
				break;
				
				case 'wcfm-refund-requests-form':
					include_once( $controllers_path . 'wcfmmp-controller-refund-requests-form.php' );
					new WCFMmp_Refund_Requests_Form_Controller();
				break;
				
				case 'wcfm-refund-requests-approve':
					include_once( $controllers_path . 'wcfmmp-controller-refund-requests-actions.php' );
					new WCFMmp_Refund_Requests_Approve_Controller();
				break;
				
				case 'wcfm-refund-requests-cancel':
					include_once( $controllers_path . 'wcfmmp-controller-refund-requests-actions.php' );
					new WCFMmp_Refund_Requests_Cancel_Controller();
				break;
  		}
  	}
  }
  
  /**
   * Refund Requests Form HTML
   */
  function wcfmmp_refund_requests_form_html() {
  	global $WCFM, $WCFMmp, $_POST;
  	if( isset( $_POST['order_id'] ) && !empty( $_POST['order_id'] ) ) {
  		$WCFMmp->template->get_template( 'refund/wcfmmp-view-refund-requests-popup.php' );
  	}
  	die;
  }
	
	public function wcfmmp_refund_orders_actions( $actions, $vendor_id, $order, $the_order ) {
  	global $WCFM, $WCFMmp;
  	
  	$order_status = sanitize_title( $the_order->get_status() );
		if( in_array( $order_status, apply_filters( 'wcfm_refund_disable_order_status', array( 'failed', 'cancelled', 'refunded', 'pending', 'on-hold', 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) ) return $actions;
  	
  	if( !apply_filters( 'wcfm_is_allow_refund_requests', true ) ) return $actions;
		if( !apply_filters( 'wcfm_is_allow_paid_order_refund', false ) && ($order->withdraw_status != 'pending') && !in_array( $the_order->get_payment_method(), array( 'wirecard', 'stripe_split' ) ) ) return $actions;
		
		$refund_statuses = explode( ",", $order->refund_statuses );
		//if( in_array( 'requested', $refund_statuses ) ) return $actions;
		
		$is_refundeds = explode( ",", $order->is_refundeds );
		if( !in_array( 0, $is_refundeds ) ) return $actions;
		
		// Refund Threshold check
		$refund_threshold = isset( $WCFMmp->wcfmmp_refund_options['refund_threshold'] ) ? $WCFMmp->wcfmmp_refund_options['refund_threshold'] : '';
		if( $refund_threshold ) {
			$current_time = strtotime( 'midnight', current_time( 'timestamp' ) );
			$date = date( 'Y-m-d', $current_time );
			$created_date = date( 'Y-m-d', strtotime($order->created) );
			$datetime1 = new DateTime( $date );
			$datetime2 = new DateTime( $created_date );
			$interval = $datetime2->diff( $datetime1 );
			$interval = $interval->format( '%r%a' );
			if( ( (int) $interval >= 0 ) && ( (int) $interval > (int) $refund_threshold ) ) return $actions;
		}
		
		$actions .= '<a class="wcfmmp_order_refund_request wcfm-action-icon" href="#" data-item="' . $order->item_id . '" data-commission="' . $order->ID . '" data-order="' . $order->order_id . '"><span class="wcfmfa fa-retweet text_tip" data-tip="' . esc_attr__( 'Refund Request', 'wc-multivendor-marketplace' ) . '"></span></a>';
  	
  	return $actions;
  }
  
  /**
   * WCFM Refund action at My Account Order actions
   */
  function wcfmmp_myaccount_refund_order_action( $actions, $order ) {
  	global $WCFM, $WCFMmp, $wpdb;
  	
  	if( !apply_filters( 'wcfm_is_allow_customer_refund', true ) ) return $actions;
  	
  	$refund_by_customer = isset( $WCFMmp->wcfmmp_refund_options['refund_by_customer'] ) ? $WCFMmp->wcfmmp_refund_options['refund_by_customer'] : 'no';
  	if( $refund_by_customer == 'no' ) return $actions;
  		
  	$order_id = $order->get_id();
  	
  	$wcfm_refund_request = get_post_meta( $order_id, '_wcfm_refund_request', true );
  	if( $wcfm_refund_request ) return $actions;
  	
  	$order_status = sanitize_title( $order->get_status() );
		if( in_array( $order_status, apply_filters( 'wcfm_refund_disable_order_status', array( 'failed', 'cancelled', 'refunded', 'pending', 'on-hold', 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) ) return $actions;
  	
  	$withdraw_status  = 'pending';
  	$refund_status    = '';
		$vendor_id        = 0;
		$is_refunded      = 0;
		
		$sql = 'SELECT ID, withdraw_status, vendor_id, refund_status, is_refunded FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `order_id` = " . $order_id;
		$commissions = $wpdb->get_results( $sql );
		if( !empty( $commissions ) ) {
			foreach( $commissions as $commission ) {
				$withdraw_status  = $commission->withdraw_status;
				$vendor_id        = $commission->vendor_id;
				$refund_status    = $commission->refund_status;
				$is_refunded      = $commission->is_refunded;
				
				if( !$is_refunded && ( $refund_status != 'requested' ) ) break;
			}
		}
		
		//if( $withdraw_status != 'pending' ) return $actions;
		if( $refund_status == 'requested' ) return $actions;
		if( $is_refunded ) return $actions;
		
		// Refund Threshold check
		$refund_threshold = isset( $WCFMmp->wcfmmp_refund_options['refund_threshold'] ) ? $WCFMmp->wcfmmp_refund_options['refund_threshold'] : '';
		if( $refund_threshold ) {
			$current_time = strtotime( 'midnight', current_time( 'timestamp' ) );
			$date = date( 'Y-m-d', $current_time );
			$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->order_date : $order->get_date_created();
			$created_date = date( 'Y-m-d', strtotime($order_date) );
			$datetime1 = new DateTime( $date );
			$datetime2 = new DateTime( $created_date );
			$interval = $datetime2->diff( $datetime1 );
			$interval = $interval->format( '%r%a' );
			if( ( (int) $interval >= 0 ) && ( (int) $interval > (int) $refund_threshold ) ) return $actions;
		}
  	
  	$actions['wcfm-refund-action'] = array( 'name' => __( 'Refund', 'wc-multivendor-marketplace' ), 'url' => '#' . $order_id );
  	return $actions;
  }
  
  public function wcfmmp_refund_processed( $vendor_id, $order_id, $commission_id, $item_id, $refund_reason, $refunded_amount = 1, $refunded_qty = '', $refunded_tax = array(), $refund_request = 'full', $refund_status = 'requested'  ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$requested_by     = get_current_user_id();
		
		$is_partially_refunded = 0;
		$refund_type = 'refund';
		if( $refund_request == 'partial' ) {
			$is_partially_refunded = 1;
			$refund_type = 'partial-refund';
		}
		
		$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_refund_request` 
									( vendor_id
									, order_id
									, commission_id
									, item_id
									, requested_by
									, refunded_amount
									, refund_reason
									, is_partially_refunded
									, refund_status
									, created
									) VALUES ( %d
									, %d
									, %d
									, %d
									, %d
									, %s 
									, %s
									, %d
									, %s
									, %s
									) ON DUPLICATE KEY UPDATE `created` = %s"
							, $vendor_id
							, $order_id
							, $commission_id
							, $item_id
							, $requested_by
							, $refunded_amount
							, $refund_reason
							, $is_partially_refunded
							, $refund_status
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
			)
		);
		$refund_request_id = $wpdb->insert_id;
		
		$this->wcfmmp_update_refund_meta( $refund_request_id, 'refunded_qty', $refunded_qty );
		$this->wcfmmp_update_refund_meta( $refund_request_id, 'refunded_tax', serialize( $refunded_tax ) );
		
		// Set Order Meta
		update_post_meta( $order_id, '_wcfm_refund_request', 'yes' );
		
		do_action( 'wcfmmp_refund_request_processed', $refund_request_id, $vendor_id, $order_id, $commission_id, $refunded_amount, $refund_type );
		return $refund_request_id;
	}
	
	/**
	 * Update Refund metas
	 */
	public function wcfmmp_update_refund_meta( $refund_id, $key, $value ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_refund_request_meta` 
									( refund_id
									, `key`
									, `value`
									) VALUES ( %d
									, %s
									, %s
									)"
							, $refund_id
							, $key
							, $value
			)
		);
		$refund_meta_id = $wpdb->insert_id;
		return $refund_meta_id;
	}
	
	/**
	 * Get Refund metas
	 */
	public function wcfmmp_get_refund_meta( $refund_id, $key ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$commission_meta = $wpdb->get_var( 
						$wpdb->prepare(
							"SELECT `value` FROM `{$wpdb->prefix}wcfm_marketplace_refund_request_meta` 
							     WHERE 
							     `refund_id` = %d
									  AND `key` = %s
									"
							, $refund_id
							, $key
			)
		);
		return $commission_meta;
	}
	
	/**
	 * Refund status update by Refund ID
	 */
	public function wcfmmp_refund_status_update_by_refund( $refund_id, $status = 'completed', $refund_note = '' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$refund_id ) return;
		
		if( $status == 'completed' ) {
			$vendor_id = 0;
			$order_id = 0;
			
			// On complete Commission table update
			$sql = 'SELECT item_id, commission_id, vendor_id, order_id, is_partially_refunded, refunded_amount, refund_reason FROM ' . $wpdb->prefix . 'wcfm_marketplace_refund_request';
			$sql .= ' WHERE 1=1';
			$sql .= " AND ID = " . $refund_id;
			$refund_infos = $wpdb->get_results( $sql );
			if( !empty( $refund_infos ) ) {
				foreach( $refund_infos as $refund_info ) {
					$item_id           = absint( $refund_info->item_id );
					$vendor_id         = absint( $refund_info->vendor_id );
					$order_id          = absint( $refund_info->order_id );
					$commission_id     = absint( $refund_info->commission_id );
					$refunded_amount   = (float) $refund_info->refunded_amount;
					$c_refunded_amount = $refunded_amount;
					$c_refunded_qty    = absint( $this->wcfmmp_get_refund_meta( $refund_id, 'refunded_qty' ) ); 
					$refund_reason     = $refund_info->refund_reason;
					$is_partially_refunded = $refund_info->is_partially_refunded;
					$is_refunded = 0;
					if( !$is_partially_refunded ) $is_refunded = 1;
					
					$wc_refund_processed = true;
					
					// Create WC Refund Item
					if( $order_id ) {
						$order                  = wc_get_order( $order_id );
						
						// API Refund Check
						$api_refund             = false;
						if ( WC()->payment_gateways() ) {
							$payment_gateways     = WC()->payment_gateways->payment_gateways();
							if ( isset( $payment_gateways[ $order->get_payment_method() ] ) && $payment_gateways[ $order->get_payment_method() ]->supports( 'refunds' ) ) {
								$api_refund         = true;
							}
						}
						$api_refund             = apply_filters( 'wcfm_is_allow_api_refund', $api_refund, $order->get_payment_method() );
						
						$restock_refunded_items = 'true';
						$refund_tax             = $this->wcfmmp_get_refund_meta( $refund_id, 'refunded_tax' );
						if( !$refund_tax ) $refund_tax = array();
						else $refund_tax = unserialize( $refund_tax );
						
						$line_item = new WC_Order_Item_Product( $item_id );
						
						if( $is_refunded ) {
							$product         = $line_item->get_product();
							
							/*$refund_tax      = $line_item->get_taxes();
							$c_refunded_qty  = $line_item->get_quantity(); 
							if( !empty( $refund_tax ) && is_array( $refund_tax ) ) {
								if( isset( $refund_tax['total'] ) ) {
									$refund_tax = $refund_tax['total'];
								}
								if( !empty( $refund_tax ) && is_array( $refund_tax ) ) {
									foreach( $refund_tax as $refund_tax_id => $refund_tax_price ) {
										$refunded_amount += (float) $refund_tax_price;
									}
								}
							}*/
							
							// Item Shipping Refund
							$vendor_shipping = $WCFMmp->wcfmmp_shipping->get_order_vendor_shipping( $order_id );
							
							$shipping_cost = $shipping_tax = 0;
							if ( !empty($vendor_shipping) && isset($vendor_shipping[$vendor_id]) && $vendor_shipping[$vendor_id]['shipping_item_id'] && ( $product && $product->needs_shipping() ) ) {
								$shipping_item_id = $vendor_shipping[$vendor_id]['shipping_item_id'];
								$package_qty      = absint( $vendor_shipping[$vendor_id]['package_qty'] );
								if( !$package_qty ) $package_qty = $line_item->get_quantity();
								$shipping_item    = new WC_Order_Item_Shipping( $shipping_item_id );
								$refund_shipping_tax = $shipping_item->get_taxes();
								$shipping_tax_refund = array();
								if( !empty( $refund_shipping_tax ) && is_array( $refund_shipping_tax ) ) {
									if( isset( $refund_shipping_tax['total'] ) ) {
										$refund_shipping_tax = $refund_shipping_tax['total'];
									}
									if( !empty( $refund_shipping_tax ) && is_array( $refund_shipping_tax ) ) {
										foreach( $refund_shipping_tax as $refund_shipping_tax_id => $refund_shipping_tax_price ) {
											$refunded_amount += round( ((float) $refund_shipping_tax_price/$package_qty) * $line_item->get_quantity(), 2);
											$shipping_tax_refund[$refund_shipping_tax_id] = round( ((float) $refund_shipping_tax_price/$package_qty) * $line_item->get_quantity(), 2);
										}
									}
								}
								
								$shipping_cost = (float) round(($vendor_shipping[$vendor_id]['shipping'] / $package_qty) * $line_item->get_quantity(), 2);
								$refunded_amount += $shipping_cost;
								$line_items[ $shipping_item_id  ] = array(
									'refund_total' => $shipping_cost,
									'refund_tax'   => $shipping_tax_refund,
								);
							}
						} elseif( $c_refunded_qty ) {
							$item_qty  = $line_item->get_quantity(); 
							if( $item_qty == $c_refunded_qty ) {
								$is_partially_refunded = 0;
							}
						}
						
						if( !empty( $refund_tax ) && is_array( $refund_tax ) ) {
							foreach( $refund_tax as $refund_tax_id => $refund_tax_price ) {
								$refunded_amount += (float) $refund_tax_price;
							}
						}
				
						try {
							$line_items[ $item_id ] = array(
								'refund_total' => $c_refunded_amount,
								'refund_tax'   => $refund_tax,
							);
							
							//if( $is_refunded ) {
								$line_items[ $item_id ]['qty'] = $c_refunded_qty;
							//}
							
							$wcfm_create_refund_args = apply_filters( 'wcfm_create_refund_args', array(
																															'amount'         => round( $refunded_amount, 2 ),
																															'reason'         => $refund_reason,
																															'order_id'       => $order_id,
																															'line_items'     => $line_items,
																															'refund_payment' => $api_refund,
																															'restock_items'  => $restock_refunded_items,
																														), $refund_id, $order_id );
							
							//print_r($wcfm_create_refund_args);
				
							// Create the refund object.
							$refund = wc_create_refund( $wcfm_create_refund_args );
							
							if ( is_wp_error( $refund ) ) {
								$wc_refund_processed = false;
								wcfm_log( $refund->get_error_message() . " args => " . json_encode( $wcfm_create_refund_args ) );
							}
						} catch ( Exception $e ) {
							$wc_refund_processed = false;
							wcfm_log($e->getMessage());
						}
					}
					
					// Processing Vendor Commission Refund
					if( $wc_refund_processed ) {
						$commission_amount = 0;
						$total_commission = 0;
						$refunded_tax_amount = 0;
						
						if( !empty( $refund_tax ) && is_array( $refund_tax ) ) {
							foreach( $refund_tax as $refund_tax_id => $refund_tax_price ) {
								$refunded_tax_amount += (float) $refund_tax_price;
							}
						}
						
						if( $commission_id ) {
							
							$commission_tax     = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'commission_tax' );
							$transaction_charge = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'transaction_charge' );
							$aff_commission     = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, '_wcfm_affiliate_commission' );
						
							// Fetch Commission details & recalculate commission
							$sql = 'SELECT order_id, product_id, variation_id, item_sub_total, item_total, quantity, commission_amount, total_commission, refunded_amount, tax, shipping, shipping_tax_amount, commission_status FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders';
							$sql .= ' WHERE 1=1';
							$sql .= " AND ID = " . $commission_id;
							$sql .= " AND vendor_id = " . $vendor_id;
							$commission_infos = $wpdb->get_results( $sql );
							if( !empty( $commission_infos ) ) {
								foreach( $commission_infos as $commission_info ) {
									$commission_amount     = (float) $commission_info->commission_amount;
									$total_commission      = (float) $commission_info->total_commission;
									$total_commission      = ( $total_commission + $commission_tax + $aff_commission + $transaction_charge ) - $commission_amount;
									
									$tax                   = (float) $commission_info->tax;
									$gross_tax             = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_tax_cost' );
									$refunded_tax          = (float) ( $gross_tax - $tax );
									
									$shipping              = (float) $commission_info->shipping;
									$gross_shipping        = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_shipping_cost' );
									$refunded_shipping     = (float) ( $gross_shipping - $shipping );
									
									$shipping_tax          = (float) $commission_info->shipping_tax_amount;
									$gross_shipping_tax    = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_shipping_tax' );
									$refunded_shipping_tax = (float) ( $gross_shipping_tax - $shipping_tax );
									
									$commission_status     = $commission_info->commission_status;
									if( $is_partially_refunded ) {
										$commission_rule   = unserialize( $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'commission_rule' ) );
										if( isset( $commission_rule['coupon_deduct'] ) && ( $commission_rule['coupon_deduct'] == 'yes' ) ) {
											$item_total        = (float) ( $commission_info->item_total + $refunded_tax + $refunded_shipping + $refunded_shipping_tax ) - ( $c_refunded_amount + (float) $commission_info->refunded_amount );
										} else {
											$item_total        = (float) ( $commission_info->item_sub_total + $refunded_tax + $refunded_shipping + $refunded_shipping_tax ) - ( $c_refunded_amount + (float) $commission_info->refunded_amount );
										}
										$commission_amount = $WCFMmp->wcfmmp_commission->wcfmmp_get_order_item_commission( $commission_info->order_id, $vendor_id, $commission_info->product_id, $commission_info->variation_id, $item_total, $commission_info->quantity, $commission_rule );
										$total_commission += (float) $commission_amount;
										$total_commission -= (float) $refunded_tax_amount;
										$total_commission -= (float) $aff_commission;
										$total_commission -= (float) $transaction_charge; // Not right, have to recalculate
										
										// Commission Tax Calculation
										if( isset( $commission_rule['tax_enable'] ) && ( $commission_rule['tax_enable'] == 'yes' ) ) {
											$commission_tax = $total_commission * ( (float)$commission_rule['tax_percent'] / 100 );
											$commission_tax = apply_filters( 'wcfmmp_commission_deducted_tax', $commission_tax, $vendor_id, $commission_info->product_id, $commission_info->variation_id, $commission_info->order_id, $total_commission, $commission_rule );
											$total_commission -= (float) $commission_tax;
											
											$WCFMmp->wcfmmp_commission->wcfmmp_delete_commission_meta( $commission_id, 'commission_tax' );
											$WCFMmp->wcfmmp_commission->wcfmmp_update_commission_meta( $commission_id, 'commission_tax', round($commission_tax, 2) );
										}
										
										$c_refunded_amount     = $refunded_amount;
										$tax                   = (float)$tax - (float)$refunded_tax_amount;
										$WCFMmp->wcfmmp_commission->wcfmmp_update_commission_meta( $commission_id, 'gross_tax_cost', round($tax, 2) );
									} else {
										$c_refunded_amount = $refunded_amount; //(float) $commission_info->total_commission;
										$commission_amount = 0;
										$total_commission  = 0;
										$remaining_tax_amount = 
										$commission_status = 'refunded'; 
									}
									
									$refunded_amount       = $refunded_amount + (float) $commission_info->refunded_amount;
									
									$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('refunded_id' => $refund_id, 'refund_status' => $status, 'refunded_amount' => $refunded_amount, 'is_refunded' => $is_refunded, 'is_partially_refunded' => $is_partially_refunded, 'commission_amount' => $commission_amount, 'tax' => $tax, 'total_commission' => $total_commission, 'commission_status' => $commission_status ), array('ID' => $commission_id), array('%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s'), array('%d'));
								}
							}
							
							
							// Update commission ledger status - not sure
							if( $is_partially_refunded ) {
								$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $commission_id, 'partial-refunded' );
							} else {
								$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $commission_id, 'refunded' );
							}
							
							// Vendor Notification
							if( $vendor_id ) {
								$wcfm_messages = sprintf( __( 'Your Refund Request approved for Order <b>%s</b>.', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">#' . $order->get_order_number() . '</a>' );
								if( $refund_note ) {
									$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $refund_note;
								}
								$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'refund-request' );
							}
							
							// Refund Item Meta
							if ( !is_wp_error( $refund ) && $vendor_id ) {
								$refund->set_refunded_by( $vendor_id );
								$refund->save();
							}
					
							do_action( 'wcfmmp_commission_refund_status_completed', $refund_id, $commission_id, $order_id, $vendor_id, $refund );
						}
					
						// Refund Status Updated
						$wpdb->update("{$wpdb->prefix}wcfm_marketplace_refund_request", array('refund_status' => $status, 'refunded_amount' => $c_refunded_amount, 'refund_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('ID' => $refund_id), array('%s', '%s', '%s'), array('%d'));
						
						// On refund complete ledge entry status update
						if( $is_partially_refunded ) {
							$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $refund_id, 'completed', 'partial-refund' );
						} else {
							$wpdb->update("{$wpdb->prefix}wcfm_marketplace_vendor_ledger", array('debit' => $c_refunded_amount), array('reference_id' => $refund_id, 'reference' => 'refund'), array('%s'), array('%d', '%s'));	
							$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $refund_id, 'completed', 'refund' );
						}
						
						// Order Not Added
						$wcfm_refund_request_notified = get_post_meta( $order_id, '_wcfm_refund_request_notified', true );
						if( $is_partially_refunded ) {
							$wcfm_messages = sprintf( __( 'Partial Refund Request approved for Order <b>%s</b>.', 'wc-multivendor-marketplace' ), '#' . $order->get_order_number() );
						} else {
							$wcfm_messages = sprintf( __( 'Refund Request approved for Order <b>%s</b>.', 'wc-multivendor-marketplace' ), '#' . $order->get_order_number() );
							update_post_meta( $order_id, '_wcfm_refund_request_notified', 1 );
						}
						if( $refund_note ) {
							$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $refund_note;
						}
						
						if( $is_partially_refunded || !$wcfm_refund_request_notified ) {
							$comment_id = $order->add_order_note( $wcfm_messages, apply_filters( 'wcfm_is_allow_refund_update_note_for_customer', '1' ) );
							if( $vendor_id ) {
								add_comment_meta( $comment_id, '_vendor_id', $vendor_id );
							}
						}
						
						// Update Order Meta
						delete_post_meta( $order_id, '_wcfm_refund_request' );
						
						do_action( 'wcfmmp_refund_status_completed', $refund_id, $order_id, $vendor_id, $refund );
						
						return true;
					}
				}
			}
		} else {
			// Order Status Updated
			$sql = 'SELECT commission_id, vendor_id, order_id FROM ' . $wpdb->prefix . 'wcfm_marketplace_refund_request';
			$sql .= ' WHERE 1=1';
			$sql .= " AND ID = " . $refund_id;
			$refund_infos = $wpdb->get_results( $sql );
			if( !empty( $refund_infos ) ) {
				foreach( $refund_infos as $refund_info ) {
					$vendor_id         = absint( $refund_info->vendor_id );
					$order_id          = absint( $refund_info->order_id );
					$commission_id     = absint( $refund_info->commission_id );
					
					$order             = wc_get_order( $order_id );
					
					$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array( 'refund_status' => $status ), array('ID' => $commission_id), array('%s'), array('%d'));
					
					// Vendor Notification
					if( $vendor_id ) {
						$wcfm_messages = sprintf( __( 'Your Refund Request cancelled for Order <b>%s</b>.', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">#' . $order->get_order_number() . '</a>' );
						if( $refund_note ) {
							$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $refund_note;
						}
						$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'refund-request' );
					}
			
					do_action( 'wcfmmp_commission_refund_status_'.$status, $refund_id, $commission_id, $order_id );
					
					// Refund Status Updated
					$wpdb->update("{$wpdb->prefix}wcfm_marketplace_refund_request", array('refund_status' => $status, 'refund_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('ID' => $refund_id), array('%s', '%s'), array('%d'));
						
					// Ledger Status Update
					$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $refund_id, $status, 'refund' );
					$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $refund_id, $status, 'partial-refund' );
					
					// Order Not Added
					$wcfm_messages = sprintf( __( 'Refund Request cancelled for Order <b>%s</b>.', 'wc-multivendor-marketplace' ), '#' . $order->get_order_number() );
					if( $refund_note ) {
						$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $refund_note;
					}
					$comment_id = $order->add_order_note( $wcfm_messages, apply_filters( 'wcfm_is_allow_refund_update_note_for_customer', '1' ) );
					if( $vendor_id ) {
						add_comment_meta( $comment_id, '_vendor_id', $vendor_id );
					}
					
					// Update Order Meta
					delete_post_meta( $order_id, '_wcfm_refund_request' );
					
					do_action( 'wcfmmp_refund_status_'.$status, $refund_id, $order_id );
					
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Withdraw status update by commission status change
	 */
	public function wcfmmp_refund_status_update_by_commission( $commission_id, $status = 'cancelled' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$commission_id ) return;
		
		// Order Status Updated
		$sql = 'SELECT ID, vendor_id, order_id FROM ' . $wpdb->prefix . 'wcfm_marketplace_refund_request';
		$sql .= ' WHERE 1=1';
		$sql .= " AND commission_id = " . $commission_id;
		$refund_infos = $wpdb->get_results( $sql );
		if( !empty( $refund_infos ) ) {
			foreach( $refund_infos as $refund_info ) {
				$vendor_id         = absint( $refund_info->vendor_id );
				$order_id          = absint( $refund_info->order_id );
				$refund_id         = absint( $refund_info->ID );
				
				$wpdb->update("{$wpdb->prefix}wcfm_marketplace_refund_request", array('refund_status' => $status, 'refund_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('ID' => $refund_id), array('%s', '%s'), array('%d'));
				
				// Ledger Status Update
				$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $refund_id, $status, 'refund' );
				$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $refund_id, $status, 'partial-refund' );
				
				// Vendor Notification
				$wcfm_messages = sprintf( __( 'Your Refund Request cancelled for Order <b>%s</b>.', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">#' . $order_id . '</a>' );
				$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'refund-request' );
								
				// Update Order Meta
				delete_post_meta( $order_id, '_wcfm_refund_request' );
				
				do_action( 'wcfmmp_refund_status_'.$status, $refund_id, $order_id );
			}
		}
	}
	
	/**
	 * Get Refund amount by Vendor
	 */
	public function wcfm_get_refund_by_vendor( $vendor_id, $interval = '7day' ) {
		global $wpdb, $WCFM, $WCFMmp;
		
		$sql = "SELECT SUM(refunded_amount) AS total_refunded FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
		$sql .= " WHERE 1=1";
		$sql .= " AND commission.vendor_id = %d";
		$sql .= " AND commission.is_trashed != -1";
		$sql = wcfm_query_time_range_filter( $sql, 'created', $interval, '', '', 'commission' );

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $vendor_id ) );
		$refunded_amount = 0;
		foreach( $results as $data ) {
			$refunded_amount = $data->total_refunded;
		}
		
		return $refunded_amount;
	}
	
	/**
	 * WCFM My Account Refund JS
	 */
	function wcfm_refund_scripts() {
 		global $WCFM, $WCFMmp, $wp, $WCFM_Query;
 		
 		if( is_account_page() ) {
 			if( is_user_logged_in() ) {
				$WCFM->library->load_blockui_lib();
				wp_enqueue_script( 'wcfmmp_refund_requests_form_js', $WCFMmp->library->js_lib_url . 'refund/wcfmmp-script-refund-requests-popup.js', array('jquery'), $WCFMmp->version, true );
      	// Localized Script
        $wcfm_messages = get_wcfm_refund_requests_messages();
			  wp_localize_script( 'wcfmmp_refund_requests_form_js', 'wcfm_refund_requests_messages', $wcfm_messages );
			}
 		}
 	}
 	
 	/**
 	 * WCFM My Account Refund CSS
 	 */
 	function wcfm_refund_styles() {
 		global $WCFM, $WCFMmp, $wp, $WCFM_Query;
 		
 		if( is_account_page() ) {
 			if( is_user_logged_in() ) {
				wp_enqueue_style( 'wcfmmp_refund_requests_form_css',  $WCFMmp->library->css_lib_url . 'refund/wcfmmp-style-refund-requests-popup.css', array(), $WCFMmp->version );
 			}
 		}
 	}
}