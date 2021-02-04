<?php
/**
 * WCFM plugin core
 *
 * WCFM Ledger core
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Ledger {

	public function __construct() {
		global $WCFM, $WCFMmp;
		
		if( apply_filters( 'wcfm_is_pref_ledger_book', true ) && wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_ledger', true ) && ( !is_admin() || defined('DOING_AJAX') ) ) {
		
			// WCFM Ledger Query Var Filter
			add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_ledger_query_vars' ), 10 );
			add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_ledger_endpoint_title' ), 10, 2 );
			add_action( 'init', array( &$this, 'wcfm_ledger_init' ), 120 );
			
			// WCFMu Ledger Load WCFMu Scripts
			add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_ledger_load_scripts' ), 10 );
			add_action( 'after_wcfm_load_scripts', array( &$this, 'wcfm_ledger_load_scripts' ), 10 );
			
			// WCFMu Ledger Load WCFMu Styles
			add_action( 'wcfm_load_styles', array( &$this, 'wcfm_ledger_load_styles' ), 10 );
			add_action( 'after_wcfm_load_styles', array( &$this, 'wcfm_ledger_load_styles' ), 10 );
			
			// WCFMu Ledger Load WCFMu views
			add_action( 'wcfm_load_views', array( &$this, 'wcfm_ledger_load_views' ), 10 );
			
			// WCFMu Ledger Ajax Controller
			add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_ledger_ajax_controller' ) );
			
			// Ledger menu on WCfM dashboard
			add_filter( 'wcfm_menus', array( &$this, 'wcfm_ledger_menus' ), 30 );
		}
		
		// Vendor Ledger update on order process
		add_action( 'wcfmmp_order_item_processed', array( &$this, 'wcfmmp_order_item_processed_ledger_update' ), 10, 9 );
		
		// Vendor Ledger update on withdraw request process
		add_action( 'wcfmmp_withdraw_request_processed', array( &$this, 'wcfmmp_withdraw_request_processed_ledger_update' ), 10, 9 );
		
		// Vendor Ledger update on reverse withdraw request process
		add_action( 'wcfmmp_reverse_withdraw_request_processed', array( &$this, 'wcfmmp_reverse_withdraw_request_processed_ledger_update' ), 10, 10 );
		
		// Vendor Ledger update on refund request process
		add_action( 'wcfmmp_refund_request_processed', array( &$this, 'wcfmmp_refund_request_processed_ledger_update' ), 10, 6 );
	}
	
	/**
   * WCfM Ledger Query Var
   */
  function wcfm_ledger_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_wcfm_vars = array(
			'wcfm-ledger'        => ! empty( $wcfm_modified_endpoints['wcfm-ledger'] ) ? $wcfm_modified_endpoints['wcfm-ledger'] : 'ledger',
		);
		$query_vars = array_merge( $query_vars, $query_wcfm_vars );
		
		return $query_vars;
  }
  
  /**
   * WCfM Ledger End Point Title
   */
  function wcfm_ledger_endpoint_title( $title, $endpoint ) {
  	
  	switch ( $endpoint ) {
			case 'wcfm-ledger' :
				$title = __( 'Ledger Book', 'wc-multivendor-marketplace' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCfM Ledger Endpoint Intialize
   */
  function wcfm_ledger_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		//if( !get_option( 'wcfm_updated_end_point_payment' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_ledger', 1 );
		//}
  }
  
	/**
   * WCfM Ledger Ledger Menu
   */
  function wcfm_ledger_menus( $menus ) {
  	global $WCFM;
  		
		$menus = array_slice($menus, 0, 3, true) +
												array( 'wcfm-ledger' => array( 'label'  => __( 'Ledger Book', 'wc-multivendor-marketplace' ),
																										 'url'        => wcfm_ledger_url(),
																										 'icon'       => 'money-bill-alt',
																										 'menu_for'   => 'vendor',
																										 'priority'   => 69
																										) )	 +
													array_slice($menus, 3, count($menus) - 3, true) ;
  	return $menus;
  }
  
	/**
   * WCfM Ledger Scripts
   */
  public function wcfm_ledger_load_scripts( $end_point ) {
	  global $WCFM, $WCFMmp;
    
	  switch( $end_point ) {
      case 'wcfm-ledger':
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_datatable_scroll_lib();
      	wp_enqueue_script( 'wcfm_ledger_js', $WCFMmp->library->js_lib_url . 'ledger/wcfmmp-script-ledger.js', array('jquery'), $WCFMmp->version, true );
      break;
	  }
	}
	
	/**
   * WCfM Ledger Styles
   */
	public function wcfm_ledger_load_styles( $end_point ) {
	  global $WCFM, $WCFMmp;
		
	  switch( $end_point ) {
		  case 'wcfm-ledger':
				wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
				wp_enqueue_style( 'wcfm_dashboard_css',  $WCFM->library->css_lib_url . 'dashboard/wcfm-style-dashboard.css', array(), $WCFM->version );
				wp_enqueue_style( 'wcfm_ledger_css',  $WCFMmp->library->css_lib_url . 'ledger/wcfmmp-style-ledger.css', array( 'wcfm_dashboard_css' ), $WCFMmp->version );
		  break;
	  }
	}
	
	/**
   * WCfM Ledger Views
   */
  public function wcfm_ledger_load_views( $end_point ) {
	  global $WCFM, $WCFMmp;
	  
	  switch( $end_point ) {
      case 'wcfm-ledger':
      	$WCFMmp->template->get_template( 'ledger/wcfmmp-view-ledger.php' );
      break;
	  }
	}
	
	/**
   * WCfM Ledger Ajax Controllers
   */
  public function wcfm_ledger_ajax_controller() {
  	global $WCFM, $WCFMmp;
  	
  	$controllers_path = $WCFMmp->plugin_path . 'controllers/ledger/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = sanitize_text_field( $_POST['controller'] );
  		switch( $controller ) {
  			case 'wcfm-ledger':
					include_once( $controllers_path . 'wcfmmp-controller-ledger.php' );
					new WCFMmp_Ledger_Controller();
  			break;
  		}
  	}
  }
  
  /**
   * Reference Name
   */
  function wcfmmp_vendor_ledger_reference_name( $reference ) {
  	$ledger_references = apply_filters( 'wcfmmp_ledger_references', 
  																		 array(
  																		 	 			'order'               => __( 'Order', 'wc-multivendor-marketplace' ),
  																		 	 			'withdraw'            => __( 'Withdrawal', 'wc-multivendor-marketplace' ),
  																		 	 			'refund'              => __( 'Refunded', 'wc-multivendor-marketplace' ),
  																		 	 			'partial-refund'      => __( 'Partial Refunded', 'wc-multivendor-marketplace' ),
  																		 	 			'withdraw-charges'    => __( 'Charges', 'wc-multivendor-marketplace' )
  																		       ) );
  	
  	$reference_name = __( ucfirst( str_replace( '-', ' ', $reference ) ), 'wc-multivendor-marketplace' );
  	if( isset( $ledger_references[$reference] ) ) $reference_name = $ledger_references[$reference];
  	return $reference_name;
  }
  
  /**
	 * Vendor Ledger update on new commission processed
	 */
	function wcfmmp_order_item_processed_ledger_update( $commission_id, $order_id, $order, $vendor_id, $product_id, $order_item_id, $grosse_total, $total_commission, $is_auto_withdrawal ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$reference_details = sprintf( __( 'Earning for %s order #%s.', 'wc-multivendor-marketplace' ), '<b>' . get_the_title( $product_id ) . '</b>', '<b>' . $order->get_order_number() . '</b>' );
		$this->wcfmmp_ledger_update( $vendor_id, $commission_id, $total_commission, 0, 'order', $reference_details );
	}
	
	/**
	 * Vendor Ledger update on new withdrawal request processed
	 */
	function wcfmmp_withdraw_request_processed_ledger_update( $withdraw_request_id, $vendor_id, $order_ids, $commission_ids, $withdraw_amount, $withdraw_charges, $withdraw_status, $withdraw_mode, $is_auto_withdrawal ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		// Withdrawal Charges Ledger Entry
		if( $withdraw_charges ) {
			$reference_details = __( 'Withdrawal Charges.', 'wc-multivendor-marketplace' );
			$this->wcfmmp_ledger_update( $vendor_id, $withdraw_request_id, 0, $withdraw_charges, 'withdraw-charges', $reference_details );
			$withdraw_amount = (float)$withdraw_amount - (float)$withdraw_charges;
		}  
		
		if( $is_auto_withdrawal ) {
			$reference_details = sprintf( __( 'Auto withdrawal by paymode for order #%s.', 'wc-multivendor-marketplace' ), '<b>' . wcfm_get_order_number( $order_ids ) . '</b>' );
		} elseif( $withdraw_mode == 'by_split_pay' ) {
			$reference_details = sprintf( __( 'Withdrawal by Stripe Split Pay for order #%s.', 'wc-multivendor-marketplace' ), '<b>' . wcfm_get_order_number( $order_ids ) . '</b>' );
		} else {
			$reference_details = sprintf( __( 'Withdrawal by request for order(s) %s.', 'wc-multivendor-marketplace' ), '<b>' . wcfm_get_order_number( $order_ids ) . '</b>' );
		}
		$this->wcfmmp_ledger_update( $vendor_id, $withdraw_request_id, 0, $withdraw_amount, 'withdraw', $reference_details );
	}
	
	/**
	 * Vendor Ledger update on new reverse withdrawal request processed
	 */
	function wcfmmp_reverse_withdraw_request_processed_ledger_update( $reverse_withdraw_request_id, $vendor_id, $order_id, $commission_id, $grosse_total, $withdraw_amount, $balance, $withdraw_status, $withdraw_mode, $is_auto_withdrawal ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$order = wc_get_order( $order_id );
		$reference_details = sprintf( __( 'Reverse Withdrawal for order #%s.', 'wc-multivendor-marketplace' ), '<b>' .  $order->get_order_number() . '</b>' );
		$this->wcfmmp_ledger_update( $vendor_id, $reverse_withdraw_request_id, 0, $balance, 'reverse-withdraw', $reference_details );
	}
	
	/**
	 * Vendor Ledger update on new refund request processed
	 */
	function wcfmmp_refund_request_processed_ledger_update( $refund_request_id, $vendor_id, $order_id, $commission_id, $refunded_amount, $refund_type ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$order_id ) return;
		if( !$vendor_id ) return;
		if( !$commission_id ) return;
		
		$order = wc_get_order( $order_id );
		
		if( wcfm_is_vendor() ) {
			$reference_details = sprintf( __( 'Request by Vendor for order #%s.', 'wc-multivendor-marketplace' ), '<b>' .  $order->get_order_number() . '</b>' );
		} elseif( current_user_can('administrator') ) {
			$reference_details = sprintf( __( 'Request by Admin for order #%s.', 'wc-multivendor-marketplace' ), '<b>' .  $order->get_order_number() . '</b>' );
		} else {
			$reference_details = sprintf( __( 'Request by Customer for order #%s.', 'wc-multivendor-marketplace' ), '<b>' .  $order->get_order_number() . '</b>' );
		}
		$this->wcfmmp_ledger_update( $vendor_id, $refund_request_id, 0, $refunded_amount, $refund_type, $reference_details );
	}
	
	/**
	 * Vendor Ledger Update
	 */
	public function wcfmmp_ledger_update( $vendor_id, $reference_id, $credit = 0, $debit = 0, $reference = 'order', $reference_details = '', $reference_status = 'pending' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_vendor_ledger` 
									( vendor_id
									, credit
									, debit
									, reference_id
									, reference
									, reference_details
									, reference_status
									, created
									) VALUES ( %d
									, %s
									, %s
									, %d
									, %s
									, %s
									, %s 
									, %s
									) ON DUPLICATE KEY UPDATE `created` = %s"
							, $vendor_id
							, $credit
							, $debit
							, $reference_id
							, $reference
							, $reference_details
							, $reference_status
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
			)
		);
		$ledger_id = $wpdb->insert_id;
		do_action( 'after_wcfmmp_ledger_update', $ledger_id, $reference_id, $reference, $credit, $debit );
	}
	
	/**
	 * Vendor Ledger Entry Status Update
	 */
	public function wcfmmp_ledger_status_update( $reference_id, $reference_status  = 'completed', $reference = 'order' ) {
		global $WCFM, $WCFMmp, $wpdb;
		if( !$reference_id ) return;
		$wpdb->update("{$wpdb->prefix}wcfm_marketplace_vendor_ledger", array('reference_status' => $reference_status, 'reference_update_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('reference_id' => $reference_id, 'reference' => $reference), array('%s', '%s'), array('%d', '%s'));
		do_action( 'wcfmmp_ledger_status_updated', $reference_id, $reference_status );
	}
  
}