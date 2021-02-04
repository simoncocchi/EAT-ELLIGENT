<?php
/**
 * WCFM plugin core
 *
 * WCFM Withdrawal core
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   3.3.0
 */
 
class WCFM_Withdrawal {

	public function __construct() {
		global $WCFM;
		
		  // WCFM WCMp Query Var Filter - 2.5.3
			add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_withdrawal_query_vars' ), 10 );
			add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_withdrawal_endpoint_title' ), 10, 2 );
			add_action( 'init', array( &$this, 'wcfm_withdrawal_init' ), 120 );
			
			// Withdrawal Endpoint Edit
			add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfm_withdrawal_endpoints_slug' ) );
			
    	// WCFMu WCMp Load WCFMu Scripts
			add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_withdrawal_load_scripts' ), 10 );
			
			// WCFMu WCMp Load WCFMu Styles
			add_action( 'wcfm_load_styles', array( &$this, 'wcfm_withdrawal_load_styles' ), 10 );
			
			// WCFMu WCMp Load WCFMu views
			add_action( 'wcfm_load_views', array( &$this, 'wcfm_withdrawal_load_views' ), 10 );
			
			// WCFMu Thirdparty Ajax Controller
			add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_withdrawal_ajax_controller' ) );
			
			add_filter( 'wcfm_menus', array( &$this, 'wcfm_withdrawal_menus' ), 30 );
		
	}
	
	/**
   * WCMp Query Var
   */
  function wcfm_withdrawal_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_wcmp_vars = array(
			'wcfm-payments'             => ! empty( $wcfm_modified_endpoints['wcfm-payments'] ) ? $wcfm_modified_endpoints['wcfm-payments'] : 'payments',
			'wcfm-withdrawal'           => ! empty( $wcfm_modified_endpoints['wcfm-withdrawal'] ) ? $wcfm_modified_endpoints['wcfm-withdrawal'] : 'withdrawal',
			'wcfm-withdrawal-requests'  => ! empty( $wcfm_modified_endpoints['wcfm-withdrawal-requests'] ) ? $wcfm_modified_endpoints['wcfm-withdrawal-requests'] : 'withdrawal-requests',
			'wcfm-withdrawal-reverse'   => ! empty( $wcfm_modified_endpoints['wcfm-withdrawal-reverse'] ) ? $wcfm_modified_endpoints['wcfm-withdrawal-reverse'] : 'withdrawal-reverse',
			'wcfm-transaction-details'  => ! empty( $wcfm_modified_endpoints['wcfm-transaction-details'] ) ? $wcfm_modified_endpoints['wcfm-transaction-details'] : 'transaction-details',
		);
		$query_vars = array_merge( $query_vars, $query_wcmp_vars );
		
		return $query_vars;
  }
  
  /**
   * WCMp End Point Title
   */
  function wcfm_withdrawal_endpoint_title( $title, $endpoint ) {
  	
  	switch ( $endpoint ) {
			case 'wcfm-payments' :
				$title = __( 'Payments History', 'wc-frontend-manager' );
			break;
			
			case 'wcfm-withdrawal' :
			case 'wcfm-withdrawal-requests' :
				$title = __( 'Withdrawal Request', 'wc-frontend-manager' );
			break;
			
			case 'wcfm-withdrawal-reverse' :
				$title = __( 'Withdrawal Reverse', 'wc-frontend-manager' );
			break;
			
			case 'transaction-details' :
			  $title = __( 'Transaction Details', 'wc-frontend-manager' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCMp Endpoint Intialize
   */
  function wcfm_withdrawal_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		//if( !get_option( 'wcfm_updated_end_point_payment' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_payment', 1 );
		//}
  }
  
  /**
	 * Withdrawal Endpoiint Edit
	 */
  function wcfm_withdrawal_endpoints_slug($endpoints ) {
		
		$withdrawal_endpoints = array(
													'wcfm-payments'               => 'payments',
													'wcfm-withdrawal'             => 'withdrawal',
													'wcfm-withdrawal-requests'    => 'withdrawal-requests',
													'wcfm-withdrawal-reverse'     => 'withdrawal-reverse',
													'wcfm-transaction-details'    => 'transaction-details',
													);
		
		$endpoints = array_merge( $endpoints, $withdrawal_endpoints );
		
		return $endpoints;
  }
  
	/**
   * WCFM wcmarketplace Menu
   */
  function wcfm_withdrawal_menus( $menus ) {
  	global $WCFM;
  	
  	if( wcfm_is_vendor() ) {
			if( apply_filters( 'wcfm_is_allow_payments', true ) ) {
				$menus = array_slice($menus, 0, 3, true) +
														array( 'wcfm-payments' => array( 'label'  => __( 'Payments', 'wc-frontend-manager' ),
																												 'url'        => wcfm_payments_url(),
																												 'icon'       => 'credit-card',
																												 'menu_for'   => 'vendor',
																												 'priority'   => 38
																												) )	 +
															array_slice($menus, 3, count($menus) - 3, true) ;
			} elseif( apply_filters( 'wcfm_is_allow_withdrawal', true ) ) {
				$menus = array_slice($menus, 0, 3, true) +
														array( 'wcfm-withdrawal' => array( 'label'  => __( 'Withdrawal', 'wc-frontend-manager' ),
																												 'url'        => wcfm_withdrawal_url(),
																												 'icon'       => 'credit-card',
																												 'menu_for'   => 'vendor',
																												 'priority'   => 38
																												) )	 +
															array_slice($menus, 3, count($menus) - 3, true) ;
			}
		} else {
			if( in_array( $WCFM->is_marketplace, array( 'dokan', 'wcfmmarketplace' ) ) ) {
				$menus = array_slice($menus, 0, 3, true) +
															array( 'wcfm-withdrawal-requests' => array( 'label'  => __( 'Withdrawal', 'wc-frontend-manager' ),
																													 'url'        => wcfm_withdrawal_requests_url(),
																													 'icon'       => 'credit-card',
																													 'menu_for'   => 'admin',
																													 'priority'   => 38
																													) )	 +
																array_slice($menus, 3, count($menus) - 3, true) ;
			}
			
			if( in_array( $WCFM->is_marketplace, array( 'wcfmmarketplace' ) ) ) {
				/*$menus = array_slice($menus, 0, 3, true) +
															array( 'wcfm-withdrawal-reverse' => array( 'label'  => __( 'Withdrawal', 'wc-frontend-manager' ),
																													 'url'        => wcfm_withdrawal_requests_url(),
																													 'icon'       => 'credit-card',
																													 'menu_for'   => 'admin',
																													 'priority'   => 38
																													) )	 +
																array_slice($menus, 3, count($menus) - 3, true) ;*/
			}
		}
  	return $menus;
  }
  
	/**
   * Withdrawal Scripts
   */
  public function wcfm_withdrawal_load_scripts( $end_point ) {
	  global $WCFM;
    
	  switch( $end_point ) {
      case 'wcfm-payments':
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_datepicker_lib();
      	$WCFM->library->load_datatable_download_lib();
      	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		wp_enqueue_script( 'wcfm_wcmp_payments_js', $WCFM->library->js_lib_url . 'withdrawal/wcmp/wcfm-script-payments.js', array('jquery'), $WCFM->version, true );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->library->load_daterangepicker_lib();
      		wp_enqueue_script( 'wcfm_wcfm_payments_js', $WCFM->library->js_lib_url . 'withdrawal/wcfm/wcfm-script-payments.js', array('jquery'), $WCFM->version, true );
      		
      		// Screen manager
					$wcfm_screen_manager = (array) get_option( 'wcfm_screen_manager' );
					$wcfm_screen_manager_data = array();
					if( isset( $wcfm_screen_manager['withdrawal-requests'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['withdrawal-requests'];
					if( !isset( $wcfm_screen_manager_data['admin'] ) ) {
						$wcfm_screen_manager_data['admin'] = $wcfm_screen_manager_data;
						$wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
					}
					if( wcfm_is_vendor() ) {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['vendor'];
					} else {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
					}
					$wcfm_screen_manager_data[3] = 'yes';
					if( apply_filters( 'wcfm_payments_additonal_data_hidden', true ) ) {
						$wcfm_screen_manager_data[8] = 'yes';
					}
					wp_localize_script( 'wcfm_wcfm_payments_js', 'wcfm_payments_screen_manage', $wcfm_screen_manager_data );
      	} elseif( $WCFM->is_marketplace == 'dokan' ) {
      		wp_enqueue_script( 'wcfm_dokan_payments_js', $WCFM->library->js_lib_url . 'withdrawal/dokan/wcfm-script-payments.js', array('jquery'), $WCFM->version, true );
      	}
      break;
      
      case 'wcfm-withdrawal':
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_datepicker_lib();
      	$WCFM->library->load_datatable_download_lib();
      	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		wp_enqueue_script( 'wcfm_wcmp_withdrawal_js', $WCFM->library->js_lib_url . 'withdrawal/wcmp/wcfm-script-withdrawal.js', array('jquery'), $WCFM->version, true );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->library->load_daterangepicker_lib();
      		wp_enqueue_script( 'wcfm_wcfm_withdrawal_js', $WCFM->library->js_lib_url . 'withdrawal/wcfm/wcfm-script-withdrawal.js', array('jquery'), $WCFM->version, true );
      		
      		// Screen manager
					$wcfm_screen_manager = (array) get_option( 'wcfm_screen_manager' );
					$wcfm_screen_manager_data = array();
					if( isset( $wcfm_screen_manager['withdrawal-requests'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['withdrawal-requests'];
					if( !isset( $wcfm_screen_manager_data['admin'] ) ) {
						$wcfm_screen_manager_data['admin'] = $wcfm_screen_manager_data;
						$wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
					}
					if( wcfm_is_vendor() ) {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['vendor'];
					} else {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
					}
					$wcfm_screen_manager_data[2] = 'yes';
					if( apply_filters( 'wcfm_withdrawal_additonal_data_hidden', true ) ) {
						$wcfm_screen_manager_data[6] = 'yes';
					}
					wp_localize_script( 'wcfm_wcfm_withdrawal_js', 'wcfm_withdrawal_screen_manage', $wcfm_screen_manager_data );
      	} elseif( $WCFM->is_marketplace == 'dokan' ) {
      		wp_enqueue_script( 'wcfm_dokan_withdrawal_js', $WCFM->library->js_lib_url . 'withdrawal/dokan/wcfm-script-withdrawal.js', array('jquery'), $WCFM->version, true );
      	}
      break;
      
      case 'wcfm-withdrawal-requests':
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_datepicker_lib();
      	$WCFM->library->load_datatable_download_lib();
      	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		//wp_enqueue_script( 'wcfm_wcmp_withdrawal_js', $WCFM->library->js_lib_url . 'withdrawal/wcmp/wcfm-script-withdrawal.js', array('jquery'), $WCFM->version, true );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->library->load_daterangepicker_lib();
      		wp_enqueue_script( 'wcfm_wcfm_withdrawal_js', $WCFM->library->js_lib_url . 'withdrawal/wcfm/wcfm-script-withdrawal-requests.js', array('jquery'), $WCFM->version, true );
      		
      		// Screen manager
					$wcfm_screen_manager = (array) get_option( 'wcfm_screen_manager' );
					$wcfm_screen_manager_data = array();
					if( isset( $wcfm_screen_manager['withdrawal-requests'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['withdrawal-requests'];
					if( !isset( $wcfm_screen_manager_data['admin'] ) ) {
						$wcfm_screen_manager_data['admin'] = $wcfm_screen_manager_data;
						$wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
					}
					if( wcfm_is_vendor() ) {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['vendor'];
						$wcfm_screen_manager_data[3] = 'yes';
					} else {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
					}
					$wcfm_screen_manager_data[7] = 'yes';
					if( apply_filters( 'wcfm_withdrawal_request_additonal_data_hidden', true ) ) {
						$wcfm_screen_manager_data[8] = 'yes';
					}
					wp_localize_script( 'wcfm_wcfm_withdrawal_js', 'wcfm_withdrawal_request_screen_manage', $wcfm_screen_manager_data );
      	} elseif( $WCFM->is_marketplace == 'dokan' ) {
      		wp_enqueue_script( 'wcfm_dokan_withdrawal_js', $WCFM->library->js_lib_url . 'withdrawal/dokan/wcfm-script-withdrawal-requests.js', array('jquery'), $WCFM->version, true );
      	}
      break;
      
      case 'wcfm-withdrawal-reverse':
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_datepicker_lib();
      	$WCFM->library->load_datatable_download_lib();
      	if( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->library->load_daterangepicker_lib();
      		wp_enqueue_script( 'wcfm_wcfm_withdrawal_reverse_js', $WCFM->library->js_lib_url . 'withdrawal/wcfm/wcfm-script-withdrawal-reverse.js', array('jquery'), $WCFM->version, true );
      		
      		// Screen manager
					$wcfm_screen_manager = (array) get_option( 'wcfm_screen_manager' );
					$wcfm_screen_manager_data = array();
					if( isset( $wcfm_screen_manager['withdrawal-reverse'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['withdrawal-reverse'];
					if( !isset( $wcfm_screen_manager_data['admin'] ) ) {
						$wcfm_screen_manager_data['admin'] = $wcfm_screen_manager_data;
						$wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
					}
					if( wcfm_is_vendor() ) {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['vendor'];
						$wcfm_screen_manager_data[2] = 'yes';
					} else {
						$wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
					}
					if( apply_filters( 'wcfm_withdrawal_reverse_additonal_data_hidden', true ) ) {
						$wcfm_screen_manager_data[6] = 'yes';
					}
					$wcfm_screen_manager_data[7] = 'yes';
					wp_localize_script( 'wcfm_wcfm_withdrawal_reverse_js', 'wcfm_withdrawal_reverse_screen_manage', $wcfm_screen_manager_data );
      	}
      break;
      
    	case 'wcfm-transaction-details':
    	  if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		//wp_enqueue_script( 'wcfm_wcmp_transaction_details_js', $WCFM->library->js_lib_url . 'withdrawal/wcmp/wcfm-script-transaction-details.js', array('jquery'), $WCFM->version, true );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		//wp_enqueue_script( 'wcfm_wcmp_transaction_details_js', $WCFM->library->js_lib_url . 'withdrawal/wcfm/wcfm-script-transaction-details.js', array('jquery'), $WCFM->version, true );
      	}
    	break;
	  }
	}
	
	/**
   * Withdrawal Styles
   */
	public function wcfm_withdrawal_load_styles( $end_point ) {
	  global $WCFM;
		
	  switch( $end_point ) {
	  	case 'wcfm-payments':
	  		if( $WCFM->is_marketplace == 'wcmarketplace' ) {
	  			wp_enqueue_style( 'wcfm_wcmp_payments_css',  $WCFM->library->css_lib_url . 'withdrawal/wcmp/wcfm-style-payments.css', array(), $WCFM->version );
	  		} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
	  			wp_enqueue_style( 'wcfm_wcfm_payments_css',  $WCFM->library->css_lib_url . 'withdrawal/wcfm/wcfm-style-payments.css', array(), $WCFM->version );
	  		} elseif( $WCFM->is_marketplace == 'dokan' ) {
	  			wp_enqueue_style( 'wcfm_dokan_payments_css',  $WCFM->library->css_lib_url . 'withdrawal/dokan/wcfm-style-payments.css', array(), $WCFM->version );
	  		}
		  break;
		  
		  case 'wcfm-withdrawal':
		  	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
		  		wp_enqueue_style( 'wcfm_wcmp_withdrawal_css',  $WCFM->library->css_lib_url . 'withdrawal/wcmp/wcfm-style-withdrawal.css', array(), $WCFM->version );
		  	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
		  		wp_enqueue_style( 'wcfm_wcfm_withdrawal_css',  $WCFM->library->css_lib_url . 'withdrawal/wcfm/wcfm-style-withdrawal.css', array(), $WCFM->version );
		  	} elseif( $WCFM->is_marketplace == 'dokan' ) {
		  		wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
		  		wp_enqueue_style( 'wcfm_dokan_withdrawal_css',  $WCFM->library->css_lib_url . 'withdrawal/dokan/wcfm-style-withdrawal.css', array(), $WCFM->version );
		  	}
		  break;
		  
		  case 'wcfm-withdrawal-requests':
		  	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
		  		//wp_enqueue_style( 'wcfm_wcmp_withdrawal_css',  $WCFM->library->css_lib_url . 'withdrawal/wcmp/wcfm-style-withdrawal.css', array(), $WCFM->version );
		  	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
		  		wp_enqueue_style( 'wcfm_wcfm_withdrawal_css',  $WCFM->library->css_lib_url . 'withdrawal/wcfm/wcfm-style-withdrawal-requests.css', array(), $WCFM->version );
		  	} elseif( $WCFM->is_marketplace == 'dokan' ) {
		  		wp_enqueue_style( 'wcfm_dokan_withdrawal_css',  $WCFM->library->css_lib_url . 'withdrawal/dokan/wcfm-style-withdrawal-requests.css', array(), $WCFM->version );
		  	}
		  break;
		  
		  case 'wcfm-withdrawal-reverse':
		  	if( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
		  		wp_enqueue_style( 'wcfm_wcfm_withdrawal_reverse_css',  $WCFM->library->css_lib_url . 'withdrawal/wcfm/wcfm-style-withdrawal-reverse.css', array(), $WCFM->version );
		  	}
		  break;
		  
			case 'wcfm-transaction-details':
				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
					wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
					wp_enqueue_style( 'wcfm_wcmp_transaction_details_css',  $WCFM->library->css_lib_url . 'withdrawal/wcmp/wcfm-style-transaction-details.css', array(), $WCFM->version );
				} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
					wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
					wp_enqueue_style( 'wcfm_wcmp_transaction_details_css',  $WCFM->library->css_lib_url . 'withdrawal/wcfm/wcfm-style-transaction-details.css', array(), $WCFM->version );
				}
			break;
	  }
	}
	
	/**
   * Withdrawal Views
   */
  public function wcfm_withdrawal_load_views( $end_point ) {
	  global $WCFM;
	  
	  switch( $end_point ) {
      case 'wcfm-payments':
      	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcmp/wcfm-view-payments.php' );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcfm/wcfm-view-payments.php' );
      	} elseif( $WCFM->is_marketplace == 'dokan' ) {
      		$WCFM->template->get_template( 'withdrawal/dokan/wcfm-view-payments.php' );
      	}
      break;
      
      case 'wcfm-withdrawal':
      	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcmp/wcfm-view-withdrawal.php' );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcfm/wcfm-view-withdrawal.php' );
      	} elseif( $WCFM->is_marketplace == 'dokan' ) {
      		$WCFM->template->get_template( 'withdrawal/dokan/wcfm-view-withdrawal.php' );
      	}
      break;
      
      case 'wcfm-withdrawal-requests':
      	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		//$WCFM->template->get_template( 'withdrawal/wcmp/wcfm-view-withdrawal.php' );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcfm/wcfm-view-withdrawal-requests.php' );
      	} elseif( $WCFM->is_marketplace == 'dokan' ) {
      		$WCFM->template->get_template( 'withdrawal/dokan/wcfm-view-withdrawal-requests.php' );
      	}
      break;
      
      case 'wcfm-withdrawal-reverse':
      	if( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcfm/wcfm-view-withdrawal-reverse.php' );
      	}
      break;
      
    	case 'wcfm-transaction-details':
    	  if( $WCFM->is_marketplace == 'wcmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcmp/wcfm-view-transaction-details.php' );
      	} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
      		$WCFM->template->get_template( 'withdrawal/wcfm/wcfm-view-transaction-details.php' );
      	}
    	break;
	  }
	}
	
	/**
   * Withdrawal Ajax Controllers
   */
  public function wcfm_withdrawal_ajax_controller() {
  	global $WCFM;
  	
  	$controllers_path = $WCFM->plugin_path . 'controllers/withdrawal/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = wc_clean( $_POST['controller'] );
  		switch( $controller ) {
  			case 'wcfm-payments':
  				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
						include_once( $controllers_path . 'wcmp/wcfm-controller-payments.php' );
						new WCFM_Payments_Controller();
					} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-payments.php' );
						new WCFM_Payments_Controller();
					} elseif( $WCFM->is_marketplace == 'dokan' ) {
						include_once( $controllers_path . 'dokan/wcfm-controller-payments.php' );
						new WCFM_Payments_Controller();
					}
  			break;
  			
  			case 'wcfm-withdrawal':
  				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
						include_once( $controllers_path . 'wcmp/wcfm-controller-withdrawal.php' );
						new WCFM_Withdrawal_Controller();
					} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal.php' );
						new WCFM_Withdrawal_Controller();
					} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal.php' );
						new WCFM_Withdrawal_Controller();
					}
  			break;
  			
  			case 'wcfm-withdrawal-request':
  				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
						include_once( $controllers_path . 'wcmp/wcfm-controller-withdrawal-request.php' );
						new WCFM_Withdrawal_Request_Controller();
					} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal-request.php' );
						new WCFM_Withdrawal_Request_Controller();
					} elseif( $WCFM->is_marketplace == 'dokan' ) {
						include_once( $controllers_path . 'dokan/wcfm-controller-withdrawal-request.php' );
						new WCFM_Withdrawal_Request_Controller();
					}
  			break;
  			
  			case 'wcfm-withdrawal-requests':
  				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
						//include_once( $controllers_path . 'wcmp/wcfm-controller-withdrawal-request.php' );
						//new WCFM_Withdrawal_Request_Controller();
					} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal-requests.php' );
						new WCFM_Withdrawal_Requests_Controller();
					} elseif( $WCFM->is_marketplace == 'dokan' ) {
						include_once( $controllers_path . 'dokan/wcfm-controller-withdrawal-requests.php' );
						new WCFM_Withdrawal_Requests_Controller();
					}
  			break;
  			
  			case 'wcfm-withdrawal-requests-approve':
  				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
						//include_once( $controllers_path . 'wcmp/wcfm-controller-withdrawal-request.php' );
						//new WCFM_Withdrawal_Request_Controller();
					} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal-requests-actions.php' );
						new WCFM_Withdrawal_Requests_Approve_Controller();
					} elseif( $WCFM->is_marketplace == 'dokan' ) {
						include_once( $controllers_path . 'dokan/wcfm-controller-withdrawal-requests-actions.php' );
						new WCFM_Withdrawal_Requests_Approve_Controller();
					}
  			break;
  			
  			case 'wcfm-withdrawal-requests-cancel':
  				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
						//include_once( $controllers_path . 'wcmp/wcfm-controller-withdrawal-request.php' );
						//new WCFM_Withdrawal_Request_Controller();
					} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal-requests-actions.php' );
						new WCFM_Withdrawal_Requests_Cancel_Controller();
					} elseif( $WCFM->is_marketplace == 'dokan' ) {
						include_once( $controllers_path . 'dokan/wcfm-controller-withdrawal-requests-actions.php' );
						new WCFM_Withdrawal_Requests_Cancel_Controller();
					}
  			break;
  			
  			case 'wcfm-withdrawal-reverse':
					if( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal-reverse.php' );
						new WCFM_Withdrawal_Reverse_Controller();
					}
  			break;
  			
  			case 'wcfm-withdrawal-reverse-approve':
					if( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
						include_once( $controllers_path . 'wcfm/wcfm-controller-withdrawal-reverse-actions.php' );
						new WCFM_Withdrawal_Reverse_Approve_Controller();
					}
  			break;
  		}
  	}
  }
	
	
}