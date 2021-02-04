<?php

/**
 * WCFM Marketplace plugin
 *
 * WCFM Marketplace Core
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */

class WCFMmp {

	public $plugin_base_name;
	public $plugin_url;
	public $plugin_path;
	public $version;
	public $token;
	public $text_domain;
	public $vendor_id;
	public $library;
	public $template;
	public $shortcode;
	public $admin;
	public $frontend;
	public $ajax;
	private $file;
	public $wcfmmp_fields;
	public $wcfmmp_rewrite;
	public $wcfmmp_settings;
	public $wcfmmp_notification_manager;
	public $wcfmmp_commission;
	public $wcfmmp_withdraw;
	public $wcfmmp_refund;
	public $wcfmmp_reviews;
	public $wcfmmp_store;
	public $wcfmmp_store_seo;
	public $wcfmmp_vendor;
	public $wcfmmp_product;
	public $wcfmmp_emails;
	public $wcfmmp_shipping;
  public $wcfmmp_shipping_gateways;
  public $wcfmmp_shipping_zone;
	public $wcfmmp_gateways;
	public $wcfmmp_abstract_gateway;
	public $wcfmmp_product_multivendor;
	public $wcfmmp_non_ajax;
	public $wcfmmp_media;
	public $wcfmmp_sidebar_widgets;
	public $wcfmmp_shortcodes;
	public $wcfmmp_ledger;
	public $wcfmmp_store_hours;
	public $wcfm_store_url;
	public $wcfmmp_marketplace_options;
	public $wcfmmp_commission_options;
	public $wcfmmp_withdrawal_options;
	public $wcfmmp_review_options;
	public $wcfmmp_refund_options;
	public $wcfmmp_notification_options;
	public $wcfmmp_store_endpoints;
	public $head_titlse_set = false;
	public $wcfm_is_store_close = false;
	public $store_template_loaded = false;
	public $store_query_filtered = false;
	public $refund_processed = false;

	public function __construct($file) {

		$this->file = $file;
		$this->plugin_base_name = plugin_basename( $file );
		$this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
		$this->plugin_path = trailingslashit(dirname($file));
		$this->token = WCFMmp_TOKEN;
		$this->text_domain = WCFMmp_TEXT_DOMAIN;
		$this->version = WCFMmp_VERSION;
		
		// Installer Hook
		add_action( 'init', array( &$this, 'run_wcfmmp_installer' ) );
		
		add_action( 'init', array( &$this, 'init' ), 8 );
		
		add_action( 'wcfm_init', array( &$this, 'init_wcfmmp' ), 11 );
		
		add_action( 'woocommerce_loaded', array( $this, 'load_wcfmmp' ) );
		
		add_filter( 'wcfm_modules',  array( &$this, 'get_wcfmmp_modules' ) );
		
		// Generating Marketplace Order for Subscription Renewal Order
		add_filter( 'wcs_renewal_order_created', array(&$this, 'wcfmmp_renewal_order_processed'), 20, 2 );
		
		// Periodic Withdrawal Scheduler Check
		add_action( 'wcfmmp_withdrawal_periodic_scheduler', array( &$this, 'wcfmmp_withdrawal_periodic_scheduler_check' ) );
		
		// Periodic Data Cleanup Scheduler Check
		add_action( 'wcfmmp_data_cleanup_periodic_scheduler', array( &$this, 'wcfmmp_data_cleanup_periodic_scheduler_check' ) );
	}
	
	/**
	 * Initilize plugin on WP init
	 */
	function init() {
		global $WCFM, $WCFMmp;
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		
		$this->vendor_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
		if( function_exists( 'wcfm_get_option' ) ) {
			$this->wcfm_store_url               = wcfm_get_option( 'wcfm_store_url', 'store' );
			$this->wcfmmp_marketplace_options   = wcfm_get_option( 'wcfm_marketplace_options', array() );
			$this->wcfmmp_store_endpoints       = wcfm_get_option( 'wcfm_store_endpoints', array() );
		} else {
			$this->wcfm_store_url               = get_option( 'wcfm_store_url', 'store' );
			$this->wcfmmp_marketplace_options   = get_option( 'wcfm_marketplace_options', array() );
			$this->wcfmmp_store_endpoints       = get_option( 'wcfm_store_endpoints', array() );
		}
		$this->wcfmmp_commission_options    = get_option( 'wcfm_commission_options', array() );
		$this->wcfmmp_withdrawal_options    = get_option( 'wcfm_withdrawal_options', array() );
		$this->wcfmmp_review_options        = get_option( 'wcfm_review_options', array() );
		$this->wcfmmp_refund_options        = get_option( 'wcfm_refund_options', array() );
		$this->wcfmmp_notification_options  = get_option( 'wcfmmp_notification_options', array() );
		
		// Load WCFM Marketplace setup class
		// http://localhost/wwd/wp-admin/?page=wcfmmp-setup&step=dashboard
		if ( is_admin() ) {
			$current_page = filter_input( INPUT_GET, 'page' );
			if ( $current_page && $current_page == 'wcfmmp-setup' ) {
				require_once $this->plugin_path . 'helpers/class-wcfmmp-setup.php';
			}
		}
			
		if( function_exists( 'wcfm_is_vendor' ) && wcfm_is_vendor() ) {
			$current_page = filter_input( INPUT_GET, 'store-setup' );
			if ( $current_page ) {
				require_once $this->plugin_path . 'helpers/class-wcfmmp-store-setup.php';
			}
		}
		
		// Init Admin class
		if ( is_admin() ) {
			$this->load_class( 'admin' );
			$this->admin = new WCFMmp_Admin();
		}
		
		// Rewrite rules loader
		if( !class_exists( 'WCFMmp_Rewrites' ) ) {
			$this->load_class( 'rewrite' );
			$this->wcfmmp_rewrite = new WCFMmp_Rewrites();
		}
		
		// Marketplace Abstract Gateway Load
		$this->load_class('abstract-gateway');
	}
	
	/**
	 * Load WCFMmp
	 */
	function load_wcfmmp() {
		
		if(WCFMmp_Dependencies::woocommerce_plugin_active_check() && WCFMmp_Dependencies::wcfm_plugin_active_check()) {
			// Sidebar and Widgets loader
			$this->load_class( 'sidebar-widgets' );
			$this->wcfmmp_sidebar_widgets = new WCFMmp_Sidebar_Widgets();
	
			// Marketplace Shipping Load
			$this->load_class('shipping');
			$this->wcfmmp_shipping = new WCFMmp_Shipping();
			
			// Marketplace Shipping Gateway Load
			$this->load_class('shipping-gateway');
			$this->wcfmmp_shipping_gateways = new WCFMmp_Shipping_Gateway();
	
			// Marketplace Shipping Zone Load
			$this->load_class( 'shipping-zone' );
			$this->wcfmmp_shipping_zone = new WCFMmp_Shipping_Zone();
			
			// Marketplace Emails Load
			$this->load_class('emails');
			$this->wcfmmp_emails = new WCFMmp_Emails();
			
			// Marketplace Store SEO Load
			$this->load_class('store-seo');
			$this->wcfmmp_store_seo = new WCFMmp_Store_SEO();
		}
		
		do_action( 'wcfmmp_loaded' );
	}
	
	/**
	 * Initilize plugin on WCFM init
	 */
	function init_wcfmmp() {
		global $WCFM, $WCFMmp;
		
		if(!WCFMmp_Dependencies::woocommerce_plugin_active_check()) {
			add_action( 'admin_notices', 'wcfmmp_woocommerce_inactive_notice' );
			return;
		} 
		
		if(!WCFMmp_Dependencies::wcfm_plugin_active_check()) {
			add_action( 'admin_notices', 'wcfmmp_wcfm_inactive_notice' );
			return;
		}
		
		// Init library
		$this->load_class('library');
		$this->library = new WCFMmp_Library();
		
		// Init ajax
		if (defined('DOING_AJAX')) {
			$this->load_class('ajax');
			$this->ajax = new WCFMmp_Ajax();
		}
		
		// Marketplace Setting Load
		if (!is_admin() || defined('DOING_AJAX')) {
			$this->load_class('settings');
			$this->wcfmmp_settings = new WCFMmp_Settings();
		}
		
		if( apply_filters( 'wcfm_is_pref_notification', true ) ) {
			$this->load_class('notification-manager');
			$this->wcfmmp_notification_manager = new WCFMmp_Notification_Manager();
		}
		
		// Marketplace Commission Load
		$this->load_class('commission');
		$this->wcfmmp_commission = new WCFMmp_Commission();
		
		
		// Marketplace Withdrawal Load
		$this->load_class('withdraw');
		$this->wcfmmp_withdraw = new WCFMmp_Withdraw();
		
		// Marketplace Refund module Load
		if( apply_filters( 'wcfm_is_pref_refund', true ) ) {
			$this->load_class('refund');
			$this->wcfmmp_refund = new WCFMmp_Refund();
		}
		
		// Marketplace Reviews module Load
		if( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) ) {
			$this->load_class( 'reviews' );
			$this->wcfmmp_reviews = new WCFMmp_Reviews();
		}
		
		// Marketplace Vendor Load
		$this->load_class('vendor');
		$this->wcfmmp_vendor = new WCFMmp_Vendor();
		
		// Marketplace Store Load
		$this->load_class('store');
		$this->wcfmmp_store = new WCFMmp_Store();
		
		// Marketplace Store SEO Load
		//$this->load_class('store-seo');
		//$this->wcfmmp_store_seo = new WCFMmp_Store_SEO();
		
		// Marketplace Product Load
		$this->load_class('product');
		$this->wcfmmp_product = new WCFMmp_Product();
		
		// Marketplace Single Product Multiple Vendor
		if( apply_filters( 'wcfm_is_pref_product_multivendor', true ) ) {
			$this->load_class('product-multivendor');
			$this->wcfmmp_product_multivendor = new WCFMmp_Product_Multivendor();
		}
		
		// Marketplace Ledger Load
		$this->load_class('ledger');
		$this->wcfmmp_ledger = new WCFMmp_Ledger();
		
		// Marketplace Store Hours Load
		if( apply_filters( 'wcfm_is_pref_store_hours', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class('store-hours');
				$this->wcfmmp_store_hours = new WCFMmp_Store_Hours();
			}
		}
		
		// Load Frontend
		if ( !is_admin() || defined('DOING_AJAX') ) {
			$this->load_class('frontend');
			$this->frontend = new WCFMmp_Frontend();
		}
		
		// Load Non-ajax
		if( !defined('DOING_AJAX') ) {
			$this->load_class( 'non-ajax' );
			$this->wcfmmp_non_ajax = new WCFMmp_Non_Ajax();
		}
		
		
		// Load Media Manager
		if( apply_filters( 'wcfm_is_pref_media_manager', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class('media');
				$this->wcfmmp_media = new WCFMmp_Media();
			}
		}
		
		// Template loader
		$this->load_class( 'template' );
		$this->template = new WCFMmp_Template();
		
		// Short codes loader
		//if ( !is_admin() ) {
			$this->load_class( 'shortcode' );
			$this->wcfmmp_shortcodes = new WCFMmp_Shortcode();
		//}
		
		// Marketplace Gateways Load
		$this->load_class('gateways');
		$this->wcfmmp_gateways = new WCFMmp_Gateways();
		
		//$this->wcfmmp_fields = $WCFM->wcfm_fields;
	}
	
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wc-multivendor-marketplace' );

		//load_textdomain( 'wc-multivendor-marketplace', WP_LANG_DIR . "/wc-multivendor-marketplace/wc-multivendor-marketplace-$locale.mo");
		load_textdomain( 'wc-multivendor-marketplace', $this->plugin_path . "lang/wc-multivendor-marketplace-$locale.mo");
		load_textdomain( 'wc-multivendor-marketplace', WP_LANG_DIR . "/plugins/wc-multivendor-marketplace-$locale.mo");
	}
	
	/**
	 * List of WCFM Marketplace modules
	 */
	function get_wcfmmp_modules( $wcfm_modules ) {
		
		$wcfmmp_module_index = array_search( 'refund', array_keys( $wcfm_modules ) );
		if( !$wcfmmp_module_index ) { $wcfmmp_module_index = 4; } else { $wcfmmp_module_index += 1; }
		
		$wcfmmp_modules = array(
			                    'reviews'             	=> array( 'label' => __( 'Reviews', 'wc-multivendor-marketplace' ) ),
			                    'store_hours'           => array( 'label' => __( 'Store Hours', 'wc-multivendor-marketplace' ) ),
			                    'media'             	  => array( 'label' => __( 'Media', 'wc-multivendor-marketplace' ) ),
			                    'ledger_book'           => array( 'label' => __( 'Vendor Ledger', 'wc-multivendor-marketplace' ) ),
			                    'product_mulivendor'    => array( 'label' => __( 'Product Multivendor', 'wc-multivendor-marketplace' ), 'hints' => __( "Keep this enable to allow vendors to sell other vendors' products, single product multiple seller.", 'wc-multivendor-marketplace' ) ),
			                    'sell_items_catalog'    => array( 'label' => __( 'Add to My Store Catalog', 'wc-multivendor-marketplace' ), 'hints' => __( "Other vendors' products catalog, vendors will able to add those directly to their store.", 'wc-multivendor-marketplace' ) ),
													);
		
		$wcfm_modules = array_slice($wcfm_modules, 0, $wcfmmp_module_index, true) +
																$wcfmmp_modules +
																array_slice($wcfm_modules, $wcfmmp_module_index, count($wcfm_modules) - 1, true) ;
		
		return $wcfm_modules;
	}
	
	/**
	 * Marketplace Order for WC Subscription Renewal Order
	 */
	function wcfmmp_renewal_order_processed( $renewal_order, $subscription ) {
		global $WCFM, $WCFMmp, $wpdb;
		wcfm_log( "RENEWAL ORDER CORE ::" . $renewal_order->get_id() );
		if( $renewal_order ) {
			$order_id = $renewal_order->get_id();
			$order_posted = get_post( $order_id );
			delete_post_meta( $order_id, '_wcfmmp_order_processed' );
			$WCFMmp->wcfmmp_commission->wcfmmp_checkout_order_processed( $order_id, $order_posted, $renewal_order );
			$WCFM->wcfm_notification->wcfm_message_on_new_order( $order_id, true );
		}
		
		return $renewal_order;
	}
	
	/**
	 * Periodic Withdrwal Scheduler Check 
	 */
	function wcfmmp_withdrawal_periodic_scheduler_check() {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wcfm_withdrawal_options = get_option( 'wcfm_withdrawal_options', array() );
		
		$withdrawal_mode         = isset( $wcfm_withdrawal_options['withdrawal_mode'] ) ? $wcfm_withdrawal_options['withdrawal_mode'] : '';
		$withdrawal_schedule     = isset( $wcfm_withdrawal_options['withdrawal_schedule'] ) ? $wcfm_withdrawal_options['withdrawal_schedule'] : 'week';
		
		$withdrawal_limit        = isset( $wcfm_withdrawal_options['withdrawal_limit'] ) ? $wcfm_withdrawal_options['withdrawal_limit'] : '';
		$withdrawal_thresold     = isset( $wcfm_withdrawal_options['withdrawal_thresold'] ) ? $wcfm_withdrawal_options['withdrawal_thresold'] : '';
		
		
		if( $withdrawal_mode && ( $withdrawal_mode == 'by_schedule' ) ) {
			wcfm_withdrawal_log( "PERIODIC WITHDRAWAL SCHEDULER START :: " . date_i18n( wc_date_format() . ' ' . wc_time_format(), current_time( 'timestamp', 0 ) ) ); // Start Log
			
			$args = array(
						'role__in'     => array( 'wcfm_vendor' ),
						'fields'       => array( 'ID', 'display_name' )
						
					 ); 
			$vendors = get_users( $args );
			if( !empty( $vendors ) ) {
				foreach ( $vendors as $vendor ) {
					$disable_vendor = get_user_meta( $vendor->ID, '_disable_vendor', true );
					if( !$disable_vendor ) {
						$shop_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($vendor->ID) );
						
						wcfm_withdrawal_log( "Periodic withdrawal start. Vendor :: " . $vendor->ID . ' Store :: ' . $shop_name );
						
						$payment_method = $WCFMmp->wcfmmp_vendor->get_vendor_payment_method( $vendor->ID );
						if( $payment_method ) {
						  if ( array_key_exists( $payment_method, $WCFMmp->wcfmmp_gateways->payment_gateways ) ) {
								$withdrawal_thresold = $WCFMmp->wcfmmp_withdraw->get_withdrawal_thresold( $vendor->ID );
								$withdrawal_limit    = $WCFMmp->wcfmmp_withdraw->get_withdrawal_limit( $vendor->ID );
								
								$sql  = "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
								$sql .= " WHERE 1=1";
								$sql .= " AND `vendor_id` = {$vendor->ID}";
								$sql .= apply_filters( 'wcfm_order_status_condition', '', 'commission' );
								$sql .= " AND commission.withdraw_status IN ('pending', 'cancelled')";
								$sql .= " AND commission.refund_status != 'requested'";
								$sql .= ' AND `is_withdrawable` = 1 AND `is_auto_withdrawal` = 0 AND `is_refunded` = 0 AND `is_trashed` = 0';
								if( $withdrawal_thresold ) $sql .= " AND commission.created <= NOW() - INTERVAL {$withdrawal_thresold} DAY";
								
								$wcfm_commissions = $wpdb->get_results( $sql );
								
								if( !empty( $wcfm_commissions ) ) {
									$order_ids = '';
									$commission_ids = '';
									$total_commission = 0;
									$no_of_commission = count( $wcfm_commissions );
									
									foreach( $wcfm_commissions as $wcfm_commission ) {
										$order = wc_get_order( $wcfm_commission->order_id );
										if( !is_a( $order , 'WC_Order' ) ) continue;
								
										try {
											$line_item = new WC_Order_Item_Product( absint( $wcfm_commission->item_id ) );
											
											// Refunded Items Skipping
											if( $refunded_qty = $order->get_qty_refunded_for_item( absint( $wcfm_commission->item_id ) ) ) {
												$refunded_qty = $refunded_qty * -1;
												if( $line_item->get_quantity() == $refunded_qty ) {
													continue;
												}
											}
										}  catch (Exception $e) {
											continue;
										}
										
										if( $order_ids ) $order_ids .= ',';
										$order_ids .= $wcfm_commission->order_id;
										
										if( $commission_ids ) $commission_ids .= ',';
										$commission_ids .= $wcfm_commission->ID;
										
										$total_commission += wc_format_decimal( $wcfm_commission->total_commission );
									}
									
									if ( $total_commission && ( (float) $total_commission >= (float) $withdrawal_limit ) ) {
										
										// Reset Commission withdrawal charges as per total withdrawal charge
										$withdraw_charges = $WCFMmp->wcfmmp_withdraw->calculate_withdrawal_charges( $total_commission, $vendor->ID );
										if( $withdraw_charges ) {
											$withdraw_charge_per_commission = (float)$withdraw_charges/$no_of_commission;
											foreach( $wcfm_commissions as $commission_info ) {
												$wpdb->update( "{$wpdb->prefix}wcfm_marketplace_orders", array( 'withdraw_charges' => wc_format_decimal($withdraw_charge_per_commission) ), array( 'ID' => $commission_info->ID ), array( '%s' ), array( '%d' ) );
											}
										}
										
										// Generate Withdrawal Request
										$withdraw_request_id = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_processed( $vendor->ID, $order_ids, $commission_ids, $payment_method, 0, $total_commission, $withdraw_charges, 'requested', 'by_schedule' );
					
										if( $withdraw_request_id && !is_wp_error( $withdraw_request_id ) ) {
											
											// Set Vendor Order Withdrawal Status Requested 
											foreach( $wcfm_commissions as $commission_info ) {
												$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('withdraw_status' => 'requested'), array('ID' => $commission_info->ID), array('%s'), array('%d'));
											}
											
											// If Auto-approve ON, process withdrawal request
											$is_auto_approve = $WCFMmp->wcfmmp_withdraw->is_withdrawal_auto_approve( $vendor->ID );
											if( $is_auto_approve ) {
												$payment_processesing_status = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_payment_processesing( $withdraw_request_id, $vendor->ID, $payment_method, $total_commission, $withdraw_charges );
												if( $payment_processesing_status ) {
													wcfm_withdrawal_log( 'Periodic withdrawal request successfully processed. Withdrawal ID :: ' . sprintf( '%06u', $withdraw_request_id ) . ' Vendor :: ' . $vendor->ID . ' Store :: ' . $shop_name );
												} else {
													wcfm_withdrawal_log( 'Periodic withdrawal request processing failed. Withdrawal ID :: ' . sprintf( '%06u', $withdraw_request_id ) . ' Vendor :: ' . $vendor->ID . ' Store :: ' . $shop_name );
												}
											} else {
												// Admin Notification
												$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( absint($vendor->ID) );
												$wcfm_messages = sprintf( __( 'Vendor <b>%s</b> has placed a Withdrawal Request #%s.', 'wc-frontend-manager' ), $store_name, '<a target="_blank" class="wcfm_dashboard_item_title" href="' . add_query_arg( 'transaction_id', $withdraw_request_id, wcfm_withdrawal_requests_url() ) . '">' . sprintf( '%06u', $withdraw_request_id ) . '</a>' );
												$WCFM->wcfm_notification->wcfm_send_direct_message( $vendor->ID, 0, 0, 1, $wcfm_messages, 'withdraw-request' );
												wcfm_withdrawal_log( 'Periodic withdrawal request successfully sent. Withdrawal ID :: ' . sprintf( '%06u', $withdraw_request_id ) . ' Vendor :: ' . $vendor->ID . ' Store :: ' . $shop_name );
											}
											
											do_action( 'wcfmmp_withdrawal_request_submited', $withdraw_request_id, $vendor->ID );
										} else {
											wcfm_withdrawal_log( 'Periodic withdrawal request failed. Vendor :: ' . $vendor->ID . ' Store :: ' . $shop_name );
										}
									} else {
										wcfm_withdrawal_log( "Periodic withdrawal commission less than withdrawal limit. Vendor :: " . $vendor->ID . ' Store :: ' . $shop_name );
									}
								} else {
									wcfm_withdrawal_log( "Periodic withdrawal no pending commission. Vendor :: " . $vendor->ID . ' Store :: ' . $shop_name );
								}
							} else {
								wcfm_withdrawal_log( "Periodic withdrawal payment method missing. Vendor :: " . $vendor->ID . ' Store :: ' . $shop_name );
							}
						} else {
							wcfm_withdrawal_log( "Periodic withdrawal payment method missing. Vendor :: " . $vendor->ID . ' Store :: ' . $shop_name );
						}
						wcfm_withdrawal_log( "Periodic withdrawal end. Vendor :: " . $vendor->ID . ' Store :: ' . $shop_name );
					}
				}
			}
			
			
			wcfm_withdrawal_log( "PERIODIC WITHDRAWAL SCHEDULER END :: " . date_i18n( wc_date_format() . ' ' . wc_time_format(), current_time( 'timestamp', 0 ) ) ); // End Log
		}
	}
	
	/**
	 * Periodic Data Cleanup Schduler Check
	 */
	function wcfmmp_data_cleanup_periodic_scheduler_check() {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wcfm_data_cleanup_options        = get_option( 'wcfm_data_cleanup_options', array() );
		
		$enable_data_cleanup              = isset( $wcfm_data_cleanup_options['enable_data_cleanup'] ) ? $wcfm_data_cleanup_options['enable_data_cleanup'] : 'no';
		
		$enable_data_cleanup_messages     = isset( $wcfm_data_cleanup_options['enable_data_cleanup_messages'] ) ? $wcfm_data_cleanup_options['enable_data_cleanup_messages'] : 'no';
		$messages_data_cleanup_more_than  = isset( $wcfm_data_cleanup_options['messages_data_cleanup_more_than'] ) ? $wcfm_data_cleanup_options['messages_data_cleanup_more_than'] : '90';
		
		$enable_data_cleanup_inquiry      = isset( $wcfm_data_cleanup_options['enable_data_cleanup_inquiry'] ) ? $wcfm_data_cleanup_options['enable_data_cleanup_inquiry'] : 'no';
		$inquiry_data_cleanup_more_than   = isset( $wcfm_data_cleanup_options['inquiry_data_cleanup_more_than'] ) ? $wcfm_data_cleanup_options['inquiry_data_cleanup_more_than'] : '90';
		
		$enable_data_cleanup_analytics    = isset( $wcfm_data_cleanup_options['enable_data_cleanup_analytics'] ) ? $wcfm_data_cleanup_options['enable_data_cleanup_analytics'] : 'no';
		$analytics_data_cleanup_more_than = isset( $wcfm_data_cleanup_options['analytics_data_cleanup_more_than'] ) ? $wcfm_data_cleanup_options['analytics_data_cleanup_more_than'] : '90';
		
		if( $enable_data_cleanup == 'yes' ) {
			wcfm_cleanup_log( "PERIODIC DATA CLEANUP SCHEDULER START :: " . date_i18n( wc_date_format() . ' ' . wc_time_format(), current_time( 'timestamp', 0 ) ) ); // Start Log
			
			// Notification data cleaup
			if( $enable_data_cleanup_messages == 'yes' ) {
				wcfm_cleanup_log( "PERIODIC NOTIFICATION DATA CLEANUP SCHEDULER START. Older than :: " . $messages_data_cleanup_more_than );
				
				// Fetching Old Messages
				$messages = $wpdb->get_results( "SELECT ID, created FROM {$wpdb->prefix}wcfm_messages WHERE `created` <= DATE_SUB(SYSDATE(), INTERVAL {$messages_data_cleanup_more_than} DAY)" );
				if( !empty( $messages ) ) {
					foreach( $messages as $message ) {
						$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_messages WHERE ID = {$message->ID}" );
						
						// Meta Cleanup
						$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_messages_modifier WHERE `message` = {$message->ID}" );
						
						wcfm_cleanup_log( "Notification data cleanup processed. ID :: " . $message->ID . " Created :: " . date_i18n( wc_date_format() . ' ' . wc_time_format() , strtotime( $message->created ) ) );
					}
				}
				
				wcfm_cleanup_log( "PERIODIC NOTIFICATION DATA CLEANUP SCHEDULER END. Older than :: " . $messages_data_cleanup_more_than );
			} else {
				wcfm_cleanup_log( "Notification data cleanup disabled." );
			}
			
			// Inquiry data cleaup
			if( $enable_data_cleanup_inquiry == 'yes' ) {
				wcfm_cleanup_log( "PERIODIC INQUIRY DATA CLEANUP SCHEDULER START. Older than :: " . $inquiry_data_cleanup_more_than );
				
				// Fetching Old Inquiries
				$inquiries = $wpdb->get_results( "SELECT ID, posted FROM {$wpdb->prefix}wcfm_enquiries WHERE `posted` <= DATE_SUB(SYSDATE(), INTERVAL {$inquiry_data_cleanup_more_than} DAY)" );
				if( !empty( $inquiries ) ) {
					foreach( $inquiries as $inquiry ) {
						$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_enquiries WHERE ID = {$inquiry->ID}" );
						
						// Meta Cleanup
						$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_enquiries_meta WHERE `enquiry_id` = {$inquiry->ID}" );
						
						// Inquiry Reply Cleanup
						$inquiry_replies = $wpdb->get_results( "SELECT ID FROM {$wpdb->prefix}wcfm_enquiries_response WHERE `enquiry_id` = {$inquiry->ID}" );
						if( !empty( $inquiry_replies ) ) {
							foreach( $inquiry_replies as $inquiry_reply ) {
								$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_enquiries_response WHERE `ID` = {$inquiry_reply->ID}" );
								
								// Reply Meta Cleanup
								$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_enquiries_response_meta WHERE `enquiry_response_id` = {$inquiry_reply->ID}" );
							}
						}
						
						wcfm_cleanup_log( "Inquiry data cleanup processed. ID :: " . $inquiry->ID . " Created :: " . date_i18n( wc_date_format() . ' ' . wc_time_format() , strtotime( $inquiry->posted ) ) );
					}
				}
				
				wcfm_cleanup_log( "PERIODIC INQUIRY DATA CLEANUP SCHEDULER END. Older than :: " . $inquiry_data_cleanup_more_than );
			} else {
				wcfm_cleanup_log( "Inquiry data cleanup disabled." );
			}
			
			// Analytics data cleaup
			//if( WCFM_Dependencies::wcfma_plugin_active_check() ) {
				if( $enable_data_cleanup_analytics == 'yes' ) {
					wcfm_cleanup_log( "PERIODIC ANALYTICS DATA CLEANUP SCHEDULER START. Older than :: " . $analytics_data_cleanup_more_than );
					
					// Daily Analytics Data Cleanup
					$analytics = $wpdb->get_results( "SELECT ID, visited FROM {$wpdb->prefix}wcfm_daily_analysis WHERE `visited` <= DATE_SUB(SYSDATE(), INTERVAL {$analytics_data_cleanup_more_than} DAY)" );
					if( !empty( $analytics ) ) {
						foreach( $analytics as $analytic ) {
							$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_daily_analysis WHERE ID = {$analytic->ID}" );
							wcfm_cleanup_log( "Daily Analytics data cleanup processed. ID :: " . $analytic->ID . " Created :: " . date_i18n( wc_date_format() . ' ' . wc_time_format() , strtotime( $analytic->visited ) ) );
						}
					}
					
					// Detailed Analytics Data Cleanup
					$analytics = $wpdb->get_results( "SELECT ID, visited FROM {$wpdb->prefix}wcfm_detailed_analysis WHERE `visited` <= DATE_SUB(SYSDATE(), INTERVAL {$analytics_data_cleanup_more_than} DAY)" );
					if( !empty( $analytics ) ) {
						foreach( $analytics as $analytic ) {
							$wpdb->query( "DELETE FROM {$wpdb->prefix}wcfm_detailed_analysis WHERE ID = {$analytic->ID}" );
							wcfm_cleanup_log( "Detailed Analytics data cleanup processed. ID :: " . $analytic->ID . " Created :: " . date_i18n( wc_date_format() . ' ' . wc_time_format() , strtotime( $analytic->visited ) ) );
						}
					}
							
					wcfm_cleanup_log( "PERIODIC ANALYTICS DATA CLEANUP SCHEDULER END. Older than :: " . $analytics_data_cleanup_more_than );
				} else {
					wcfm_cleanup_log( "Analytics data cleanup disabled." );
				}
			//}
			
			wcfm_cleanup_log( "PERIODIC DATA CLEANUP SCHEDULER END :: " . date_i18n( wc_date_format() . ' ' . wc_time_format(), current_time( 'timestamp', 0 ) ) ); // End Log
		}
	}

	public function load_class($class_name = '') {
		if ('' != $class_name && '' != $this->token) {
			require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}

	// End load_class()

	/**
	 * Install upon activation.
	 *
	 * @access public
	 * @return void
	 */
	static function activate_wcfmmp() {
		global $WCFM, $WCFMmp, $wp_roles;
		
		// Rewrite rules loader
		$WCFMmp->load_class( 'rewrite' );
		$WCFMmp->wcfmmp_rewrite = new WCFMmp_Rewrites();
		
		require_once ( $WCFMmp->plugin_path . 'helpers/class-wcfmmp-install.php' );
		$WCFMmp_Install = new WCFMmp_Install();
		
		update_option( 'wcfmmp_updated_3_3_10', 1 );
		update_option( 'wcfmmp_installed', 1 );
	}
	
	/**
	 * Check Installer upon load.
	 *
	 * @access public
	 * @return void
	 */
	function run_wcfmmp_installer() {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wcfm_marketplace_tables = $wpdb->query( "SHOW tables like '{$wpdb->prefix}wcfm_marketplace_reverse_withdrawal_meta'");
		if( !$wcfm_marketplace_tables ) {
			delete_option( 'wcfmmp_updated_3_3_10' );
			delete_option( 'wcfmmp_table_install' );
		}
		
		if( !get_option( 'wcfmmp_updated_3_3_10' ) ) {
			delete_option( 'wcfmmp_table_install' );
			require_once ( $WCFMmp->plugin_path . 'helpers/class-wcfmmp-install.php' );
			$WCFMmp_Install = new WCFMmp_Install();
			update_option( 'wcfmmp_updated_3_3_10', 1 );
		}
		
		if ( !get_option("wcfmmp_page_install") || !get_option("wcfmmp_table_install") ) {
			require_once ( $WCFMmp->plugin_path . 'helpers/class-wcfmmp-install.php' );
			$WCFMmp_Install = new WCFMmp_Install();
			
			update_option('wcfmmp_installed', 1);
		}
		
		// Removing old Schedule
		if ( class_exists( 'WooCommerce' ) ) {
			$next = WC()->queue()->get_next( 'wcfmmp_periodic_withdrawal_scheduler' );
			if ( $next ) {
				WC()->queue()->cancel_all( 'wcfmmp_periodic_withdrawal_scheduler' );
			}
			
			// Init Periodic Withdrawal Scheduler
			$wcfm_withdrawal_options = get_option( 'wcfm_withdrawal_options', array() );
			
			$withdrawal_mode         = isset( $wcfm_withdrawal_options['withdrawal_mode'] ) ? $wcfm_withdrawal_options['withdrawal_mode'] : '';
			$withdrawal_schedule     = isset( $wcfm_withdrawal_options['withdrawal_schedule'] ) ? $wcfm_withdrawal_options['withdrawal_schedule'] : 'week';
			if( $withdrawal_mode && ( $withdrawal_mode == 'by_schedule' ) ) {
				
				$period_interval    = 1;
				$period_starts_from = time();
				switch( $withdrawal_schedule ) {
					case 'week':
						$period_interval    = 7;
						$period_starts_from = strtotime( "next monday", time() );
					break;
					
					case '2weeks':
						$period_interval = 14;
						$period_starts_from = strtotime( "next monday", time() );
					break;
					
					case 'month':
						$period_interval = 30;
						$period_starts_from = strtotime('first day of next month');
					break;
					
					case '2months':
						$period_interval = 60;
						$period_starts_from = strtotime('first day of next month');
					break;
					
					case 'quarter':
						$period_interval = 90;
						$period_starts_from = strtotime('first day of next month');
					break;
				}
				
				$period_interval    = apply_filters( 'wcfm_schedule_period_interval', $period_interval, $withdrawal_schedule );
				$period_starts_from = apply_filters( 'wcfm_schedule_period_starts_from', $period_starts_from, $withdrawal_schedule );
				
		
				$next = WC()->queue()->get_next( 'wcfmmp_withdrawal_periodic_scheduler' );
				if ( ! $next ) {
					WC()->queue()->cancel_all( 'wcfmmp_withdrawal_periodic_scheduler' );
					WC()->queue()->schedule_recurring( $period_starts_from, ( $period_interval * DAY_IN_SECONDS ), 'wcfmmp_withdrawal_periodic_scheduler', array(), 'WCFM' );
				}
			} else {
				$next = WC()->queue()->get_next( 'wcfmmp_withdrawal_periodic_scheduler' );
				if ( $next ) {
					WC()->queue()->cancel_all( 'wcfmmp_withdrawal_periodic_scheduler' );
				}
			}
			
			// Init Periodic Data Cleanup Scheduler
			$wcfm_data_cleanup_options = get_option( 'wcfm_data_cleanup_options', array() );
			
			$enable_data_cleanup = isset( $wcfm_data_cleanup_options['enable_data_cleanup'] ) ? $wcfm_data_cleanup_options['enable_data_cleanup'] : 'no';
			if( $enable_data_cleanup == 'yes' ) {
				$next = WC()->queue()->get_next( 'wcfmmp_data_cleanup_periodic_scheduler' );
				if ( ! $next ) {
					WC()->queue()->cancel_all( 'wcfmmp_data_cleanup_periodic_scheduler' );
					WC()->queue()->schedule_recurring( time(), DAY_IN_SECONDS, 'wcfmmp_data_cleanup_periodic_scheduler', array(), 'WCFM' );
				}
			} else {
				$next = WC()->queue()->get_next( 'wcfmmp_data_cleanup_periodic_scheduler' );
				if ( $next ) {
					WC()->queue()->cancel_all( 'wcfmmp_data_cleanup_periodic_scheduler' );
				}
			}
		}
	}

	/**
	 * UnInstall upon deactivation.
	 *
	 * @access public
	 * @return void
	 */
	static function deactivate_wcfmmp() {
		global $WCFM, $WCFMmp;
		
		// Delete Periodic Scheduler
		if ( class_exists( 'WooCommerce' ) ) {
			$next = WC()->queue()->get_next( 'wcfmmp_withdrawal_periodic_scheduler' );
			if ( $next ) {
				WC()->queue()->cancel_all( 'wcfmmp_withdrawal_periodic_scheduler' );
			}
			
			$next = WC()->queue()->get_next( 'wcfmmp_data_cleanup_periodic_scheduler' );
			if ( $next ) {
				WC()->queue()->cancel_all( 'wcfmmp_data_cleanup_periodic_scheduler' );
			}
		}
		
		$wcfm_marketplace_options = get_option( 'wcfm_marketplace_options', array() );
		$delete_data_on_uninstall = isset( $wcfm_marketplace_options['delete_data_on_uninstall'] ) ? $wcfm_marketplace_options['delete_data_on_uninstall'] : 'no';
		
		if( $delete_data_on_uninstall == 'yes' ) {
			require_once ( $WCFMmp->plugin_path . 'helpers/class-wcfmmp-uninstall.php' );
			$WCFMmp_Uninstall = new WCFMmp_Uninstall();
		}
		
		delete_option('wcfmmp_installed');
	}
	
}