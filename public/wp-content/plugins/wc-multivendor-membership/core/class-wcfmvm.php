<?php

/**
 * WCFM Analytics plugin
 *
 * WCFM Analytics Core
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/core
 * @version   1.0.0
 */

class WCFMvm {

	public $plugin_base_name;
	public $plugin_url;
	public $plugin_path;
	public $version;
	public $token;
	public $text_domain;
	public $library;
	public $template;
	public $shortcode;
	public $admin;
	public $frontend;
	public $ajax;
	private $file;
	public $settings;
	public $wcfmvm_emails;
	public $WCFMvm_fields;
	public $is_marketplace;
	public $WCFMvm_marketplace;
	public $WCFMvm_capability;
	public $wcfmvm_non_ajax;
	public $pay_for_product;

	public function __construct($file) {

		$this->file = $file;
		$this->plugin_base_name = plugin_basename( $file );
		$this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
		$this->plugin_path = trailingslashit(dirname($file));
		$this->token = WCFMvm_TOKEN;
		$this->text_domain = WCFMvm_TEXT_DOMAIN;
		$this->version = WCFMvm_VERSION;
		
		// Installer Hook
		add_action( 'init', array( &$this, 'run_wcfmvm_installer' ) );
		
		add_action( 'wcfm_init', array( &$this, 'init_wcfmvm' ), 12 );
		
		add_action( 'woocommerce_loaded', array( $this, 'load_wcfmvm' ) );
		
		add_action( 'init', array( &$this, 'init_wcfmvm_ipn' ), 16 );
		
		add_action( 'wp', array( &$this, 'wcfmvm_init_after_wp' ), 50 );
		
		add_action( 'wcfmvm_membership_scheduler', array( $this, 'wcfmvm_membership_scheduler_check' ) );
		
		add_filter( 'wcfm_modules',  array( &$this, 'get_wcfmvm_modules' ) );
	}
	
	/**
	 * initilize plugin on WCFM init
	 */
	function init_wcfmvm() {
		global $WCFM, $WCFMvm;
		
		//if( !session_id() ) session_start();
		
		// Register Vendor Membership Post Type
		register_post_type( 'wcfm_memberships', array( 'public' => false ) );
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		if(!WCFMvm_Dependencies::woocommerce_plugin_active_check()) {
			add_action( 'admin_notices', 'wcfmvm_woocommerce_inactive_notice' );
			return;
		}
		
		if(!WCFMvm_Dependencies::wcfm_plugin_active_check()) {
			add_action( 'admin_notices', 'wcfmvm_wcfm_inactive_notice' );
			return;
		}
		
		// Capability Controller
		if (!is_admin() || defined('DOING_AJAX')) {
			$this->load_class( 'capability' );
			$this->wcfmvm_capability = new WCFMvm_Capability();
		}
		
		// Check Marketplace
		$this->is_marketplace = wcfm_is_marketplace();

		// Init library
		$this->load_class('library');
		$this->library = new WCFMvm_Library();

		// Init ajax
		if( defined('DOING_AJAX') ) {
			$this->load_class('ajax');
			$this->ajax = new WCFMvm_Ajax();
		}

		if (!is_admin() || defined('DOING_AJAX')) {
			$this->load_class('frontend');
			$this->frontend = new WCFMvm_Frontend();
		}
		
		if( apply_filters( 'wcfm_is_pref_pat_for_product', true ) ) {
			$this->load_class('pay-for-product');
			$this->pay_for_product = new WCFMvm_Pay_For_Product();
		}
		
		if( !defined('DOING_AJAX') ) {
			$this->load_class( 'non-ajax' );
			$this->wcfmvm_non_ajax = new WCFMvm_Non_Ajax();
		}
		
		// template loader
		$this->load_class( 'template' );
		$this->template = new WCFMvm_Template();
		
		// Init shortcode
		$this->load_class( 'shortcode' );
		$this->shortcode = new WCFMvm_Shortcode();
		
		$this->wcfmvm_fields = $WCFM->wcfm_fields;
		
    // WC Checkout for WCfM Membership products registration process
    add_action( 'woocommerce_order_status_processing', array( &$this, 'wcfmvm_registration_process_on_order_completed' ), 10, 1 );
    add_action( 'woocommerce_order_status_completed', array( &$this, 'wcfmvm_registration_process_on_order_completed' ), 10, 1 );
    
    // WCfM Membership status update on Subscription Status Update
    add_action( 'woocommerce_subscription_status_changed', array( &$this, 'wcfmvm_membership_update_on_subscription_status_changed' ), 10, 4 );
    
    // WCfM Membership next schedule update on Subscription Next Payment Date Update
    add_action( 'woocommerce_subscription_date_updated', array( &$this, 'wcfmvm_next_schedule_update_on_subscription_date_updated' ), 50, 3 );
    
    // ON WP delete user clean membership
    add_action( 'delete_user', array( &$this, 'wcfmvm_delete_user' ) );
    
    // On Vednor User Disable
    add_action( 'wcfm_membership_data_reset', array( &$this, 'wcfmvm_disable_vendor' ), 50 );
    
    // Remove Multi-Vendor My Account Registration option
    if( apply_filters( 'wcfm_is_allow_multivendor_registration_disable', true ) ) {
    	// WC Vendors
    	update_option( 'wcvendors_vendor_allow_registration', 'no' );
    	
    	// Dokan
			if( $this->is_marketplace == 'dokan' ) {
				remove_action( 'woocommerce_register_form', 'dokan_seller_reg_form_fields' );
				add_action( 'woocommerce_after_my_account', array( $this, 'disable_dokan_account_migration_button' ), 9 );
			}
		}
	}
	
	/**
	 * Load WCFM 
	 */
	function load_wcfmvm() {
		
		if( WCFMvm_Dependencies::woocommerce_plugin_active_check() ) {
			// WCFM Emails Load
			$this->load_class('emails');
			$this->wcfmvm_emails = new WCFMvm_Emails();
		}
	}
	
	/**
	 * Load IP Listner Class
	 */
	function init_wcfmvm_ipn() {
		// IPN listener
    $this->wcfmvm_ipn_listener();
	}
	
	/**
	 * Set User Session for Free Registration
	 */
	function wcfmvm_init_after_wp() {
		global $WCFM, $WCFMvm, $wp, $WCFM_Query;
		
		if ( is_wcfm_registration_page() ) {
			if( !wcfm_is_vendor() && ( wcfm_is_allowed_membership() || current_user_can( 'administrator' ) || current_user_can( 'shop_manager' ) ) ) {
				$application_status = '';
				if( is_user_logged_in() ) {
					$member_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
					$application_status = get_user_meta( $member_id, 'wcfm_membership_application_status', true );
				}
				
				if( $application_status && ( $application_status == 'pending' ) ) {
					//$WCFMvm->template->get_template('vendor_thankyou.php');
				} else {
					if( ($wcfm_free_membership = get_wcfm_free_membership()) && apply_filters( 'wcfm_is_pref_membership', true ) && apply_filters( 'wcfmvm_is_allow_by_default_free_registration', true ) ) {
						// Session store
						if( WC()->session ) {
							do_action( 'woocommerce_set_cart_cookies', true );
							WC()->session->set( 'wcfm_membership', $wcfm_free_membership );
							WC()->session->set( 'wcfm_membership_mode', 'new' );
							WC()->session->set( 'wcfm_membership_free_registration', $wcfm_free_membership );
						}
					} else {
						// Session store
						if( WC()->session ) {
							do_action( 'woocommerce_set_cart_cookies', true );
							WC()->session->set( 'wcfm_membership', -1 );
							WC()->session->set( 'wcfm_membership_free_registration', -1 );
						}
					}
				}
			}
		}
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
		$locale = apply_filters( 'plugin_locale', $locale, 'wc-multivendor-membership' );

		//load_textdomain( 'wc-multivendor-membership', WP_LANG_DIR . "/wc-frontend-manager-vendor-membership/wc-multivendor-membership-$locale.mo");
		load_textdomain( 'wc-multivendor-membership', $this->plugin_path . "/lang/wc-multivendor-membership-$locale.mo");
		load_textdomain( 'wc-multivendor-membership', ABSPATH . "wp-content/languages/plugins/wc-multivendor-membership-$locale.mo");
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
	static function activate_wcfmvm() {
		global $WCFM, $WCFMvm, $wp_roles;
		
		require_once ( $WCFMvm->plugin_path . 'helpers/class-wcfmvm-install.php' );
		$WCFMvm_Install = new WCFMvm_Install();
		
		// Init Membership Scheduler
		/*$data = get_option( 'wcfmvm_membership_scheduler', array() );

		if ( empty( $data['updated'] ) || ( time() - DAY_IN_SECONDS ) > $data['updated'] ) {
			$next = WC()->queue()->get_next( 'wcfmvm_membership_scheduler' );
			if ( ! $next ) {
				WC()->queue()->cancel_all( 'wcfmvm_membership_scheduler' );
				WC()->queue()->schedule_recurring( time(), DAY_IN_SECONDS, 'wcfmvm_membership_scheduler', array(), 'WCFM' );
			}
		}*/
		
		update_option('wcfmvm_installed', 1);
	}
	
	/**
	 * Check Installer upon load.
	 *
	 * @access public
	 * @return void
	 */
	function run_wcfmvm_installer() {
		global $WCFM, $WCFMvm, $wp_roles;
		
		if ( !get_option("wcfmvm_page_install") || !get_option("wcfmvm_installed") ) {
			require_once ( $WCFMvm->plugin_path . 'helpers/class-wcfmvm-install.php' );
			$WCFMvm_Install = new WCFMvm_Install();
			
			update_option('wcfmvm_installed', 1);
		} 
		
		// Restore Membership Pages
		if( WCFMvm_Dependencies::woocommerce_plugin_active_check() && WCFMvm_Dependencies::wcfm_plugin_active_check() ) {
			$array_pages = get_option( 'wcfm_page_options', array() );
			$membership_page_id = get_option( 'wcfm_vendor_membership_page_id', 0 );
			if( $membership_page_id && ( !isset( $array_pages['wcfm_vendor_membership_page_id'] ) || ( isset( $array_pages['wcfm_vendor_membership_page_id'] ) && empty( $array_pages['wcfm_vendor_membership_page_id'] ) ) ) ) {
				$array_pages['wcfm_vendor_membership_page_id'] = $membership_page_id;
			}
			$registration_page_id = get_option( 'wcfm_vendor_registration_page_id', 0 );
			if( $registration_page_id && ( !isset( $array_pages['wcfm_vendor_registration_page_id'] ) || ( isset( $array_pages['wcfm_vendor_registration_page_id'] ) && empty( $array_pages['wcfm_vendor_registration_page_id'] ) ) ) ) {
				$array_pages['wcfm_vendor_registration_page_id'] = $registration_page_id;
			}
			update_option( 'wcfm_page_options', $array_pages );
			
			// Init Membership Scheduler
			$data = get_option( 'wcfmvm_membership_scheduler', array() );
	
			if ( empty( $data['updated'] ) || ( time() - DAY_IN_SECONDS ) > $data['updated'] ) {
				$next = WC()->queue()->get_next( 'wcfmvm_membership_scheduler' );
				if ( ! $next ) {
					WC()->queue()->cancel_all( 'wcfmvm_membership_scheduler' );
					WC()->queue()->schedule_recurring( time(), DAY_IN_SECONDS, 'wcfmvm_membership_scheduler', array(), 'WCFM' );
				}
			}
		}
	}
	
	/**
	 * List of WCFMvm modules
	 */
	function get_wcfmvm_modules( $wcfm_modules ) {
		
		$wcfmvm_module_index = array_search( 'catalog', array_keys( $wcfm_modules ) );
		if( !$wcfmvm_module_index ) { $wcfmvm_module_index = 10; } else { $wcfmvm_module_index += 1; }
		
		$wcfmvm_modules = array(
			                    'pay_for_product'  => array( 'label' => __( 'Pay for Product', 'wc-multivendor-membership' ) ),
													);
		
		$wcfm_modules = array_slice($wcfm_modules, 0, $wcfmvm_module_index, true) +
																$wcfmvm_modules +
																array_slice($wcfm_modules, $wcfmvm_module_index, count($wcfm_modules) - 1, true) ;
		
		
		return $wcfm_modules;
	}

	/**
	 * UnInstall upon deactivation.
	 *
	 * @access public
	 * @return void
	 */
	static function deactivate_wcfmvm() {
		global $WCFM, $WCFMvm;
		
		// Delete Membership Scheduler
		if( WCFMvm_Dependencies::woocommerce_plugin_active_check() ) {
			$next = WC()->queue()->get_next( 'wcfmvm_membership_scheduler' );
			if ( $next ) {
				WC()->queue()->cancel_all( 'wcfmvm_membership_scheduler' );
			}
		}
		
		delete_option('wcfmvm_installed');
	}
	
	function wcfmvm_membership_color_setting_options() {
		global $WCFM;
		
		$color_options = apply_filters( 'wcfmvm_membership_color_setting_options', array( 
																																				 'wcfmvm_field_base_highlight_color' => array( 'label' => __( 'Progress Bar Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_progress_bar_color_settings', 'default' => '#2a3344', 'element' => '.wcfm-membership-wrapper .wc-progress-steps li.done, .wcfm-membership-wrapper .wc-progress-steps li.active', 'style' => 'color', 'element2' => '.wcfm-membership-wrapper .wc-progress-steps li.done, .wcfm-membership-wrapper .wc-progress-steps li.active, .wcfm-membership-wrapper .wc-progress-steps li.done::before, .wcfm-membership-wrapper .wc-progress-steps li.active::before', 'style2' => 'border-color', 'element3' => '.wcfm-membership-wrapper .wc-progress-steps li.done::before', 'style3' => 'background' ),
																																				 'wcfmvm_field_table_head_title_bg_color' => array( 'label' => __( 'Membership Title Background Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_head_title_bg_color_settings', 'default' => '#2a3344', 'element' => '#wcfm-main-contentainer .wcfm_membership_box_head .wcfm_membership_title', 'style' => 'background-color' ),
																																				 'wcfmvm_field_table_head_title_color' => array( 'label' => __( 'Membership Title Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_head_title_color_settings', 'default' => '#ffffff', 'element' => '#wcfm-main-contentainer .wcfm_membership_box_head .wcfm_membership_title, #wcfm-main-contentainer .wcfm_membership_review_plan .wcfm_review_plan_title', 'style' => 'color' ),
																																				 'wcfmvm_field_table_head_bg_color' => array( 'label' => __( 'Table Body Background Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_head_bg_color_settings', 'default' => '#17a2b8', 'element' => '#wcfm-main-contentainer .wcfm_membership_box_head', 'style' => 'background-color' ),
																																				 'wcfmvm_field_table_head_price_color' => array( 'label' => __( 'Membership Price Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_head_price_color_settings', 'default' => '#ffffff', 'element' => '#wcfm-main-contentainer .wcfm_membership_box_head .wcfm_membership_price .amount', 'style' => 'color', 'element2' => '#wcfm-main-contentainer .wcfm_membership_box_head .wcfm_membership_price .amount', 'style2' => 'border-color' ),
																																				 'wcfmvm_field_table_head_price_desc_color' => array( 'label' => __( 'Membership Price Description Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_head_price_desc_color_settings', 'default' => '#111111', 'element' => '#wcfm-main-contentainer .wcfm_membership_box_head .wcfm_membership_price_description', 'style' => 'color' ),
																																				 'wcfmvm_field_table_head_description_color' => array( 'label' => __( 'Membership Description Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_head_description_color_settings', 'default' => '#ffffff', 'element' => '#wcfm-main-contentainer .wcfm_membership_box_head .wcfm_membership_description, #wcfm-main-contentainer .wcfm_membership_review_plan .wcfm_review_plan_description', 'style' => 'color' ),
																																				 'wcfmvm_field_table_border_color' => array( 'label' => __( 'Table Border Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_border_color_settings', 'default' => '#cccccc', 'element' => '#wcfm-main-contentainer .wcfm_membership_box_head, #wcfm-main-contentainer .wcfm_membership_box_body, #wcfm-main-contentainer .wcfm_membership_box_foot', 'style' => 'border-color' ),
																																				 'wcfmvm_field_table_text_color' => array( 'label' => __( 'Table Text Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_text_color_settings', 'default' => '#111111', 'element' => '#wcfm-main-contentainer .wcfm_membership_boxes', 'style' => 'color' ),
																																				 'wcfmvm_field_table_bg_color' => array( 'label' => __( 'Table Background Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_bg_color_settings', 'default' => '#ffffff', 'element' => '#wcfm-main-contentainer .wcfm_membership_element:nth-child(odd)', 'style' => 'background-color' ),
																																				 'wcfmvm_field_table_bg_heighlighter_color' => array( 'label' => __( 'Table Cell Heighlighter Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_bg_heighlighter_color_settings', 'default' => '#d3eef6', 'element' => '#wcfm-main-contentainer .wcfm_membership_element:nth-child(even), #wcfm-main-contentainer .wcfm_membership_box_wrraper .wcfm_membership_box_head .wcfm_membership_featured_top', 'style' => 'background-color' ),
																																				 'wcfmvm_field_button_color' => array( 'label' => __( 'Button Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_button_bg_color_settings', 'default' => '#2a3344', 'element' => '.wcfm_membership_subscribe_button_wrapper input.wcfm_submit_button, #wcfm_membership_container input.wcfm_submit_button, #wcfm_membership_container a.wcfm_submit_button', 'style' => 'background-color' ),
																																				 'wcfmvm_field_button_hover_color' => array( 'label' => __( 'Button Hover Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_button_bg_hover_color_settings', 'default' => '#17a2b8', 'element' => '.wcfm_membership_subscribe_button_wrapper input.wcfm_submit_button:hover, #wcfm_membership_container input.wcfm_submit_button:hover, #wcfm_membership_container a.wcfm_submit_button:hover', 'style' => 'background-color' ),
																																				 'wcfmvm_field_button_bg_color' => array( 'label' => __( 'Button Text Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_table_button_color_settings', 'default' => '#ffffff', 'element' => '.wcfm_membership_subscribe_button_wrapper input.wcfm_submit_button, #wcfm_membership_container input.wcfm_submit_button, #wcfm_membership_container a.wcfm_submit_button', 'style' => 'color' ),
																																				 'wcfmvm_field_preview_plan_bg_color' => array( 'label' => __( 'Preview Box Background Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_preview_plan_color_settings', 'default' => '#63c2de', 'element' => '#wcfm-main-contentainer .wcfm_membership_review_plan, #wcfm-main-contentainer .wcfm_membership_thankyou_content_wrapper', 'style' => 'background-color' ),
																																				 'wcfmvm_field_preview_plan_text_color' => array( 'label' => __( 'Preview Box Text Color', 'wc-multivendor-membership' ), 'name' => 'wcfmvm_membership_preview_plan_text_color_settings', 'default' => '#555', 'element' => '#wcfm-main-contentainer .wcfm_membership_review_plan, #wcfm-main-contentainer .wcfm_membership_thankyou_content_wrapper', 'style' => 'color', 'element2' => '#wcfm-main-contentainer .wcfm_membership_review_plan .wcfm_review_plan_welcome', 'style2' => 'color', 'element3' => '#wcfm-main-contentainer .wcfm_membership_review_plan .wcfm_review_plan_features, #wcfm-main-contentainer .wcfm_membership_review_plan .wcfm_review_plan_feature, #wcfm-main-contentainer .wcfm_membership_review_plan .wcfm_review_plan_feature_val', 'style3' => 'border-color' ),
																																			) );
		
		return $color_options;
	}
	
	/**
	 * Create WCFMvm custom CSS
	 */
	function wcfmvm_create_membership_css() {
		global $WCFM, $WCFMvm;
		
		$wcfm_membership_options = get_option('wcfm_membership_options');
		$wcfmvm_color_options = array();
		if( isset( $wcfm_membership_options['membership_color_settings'] ) ) $wcfmvm_color_options = $wcfm_membership_options['membership_color_settings'];
		$color_options = $WCFMvm->wcfmvm_membership_color_setting_options();
		$custom_color_data = '';
		$custom_button_color_data = '';
		foreach( $color_options as $color_option_key => $color_option ) {
		  $custom_color_data .= $color_option['element'] . '{ ' . "\n";
			$custom_color_data .= "\t" . $color_option['style'] . ': ';
			if( isset( $wcfmvm_color_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfmvm_color_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
			$custom_color_data .= ';' . "\n";
			$custom_color_data .= '}' . "\n\n";
			
			if( in_array( $color_option_key, array( 'wcfmvm_field_button_color', 'wcfmvm_field_button_hover_color', 'wcfmvm_field_button_bg_color' ) ) ) {
				$custom_button_color_data .= $color_option['element'] . '{ ' . "\n";
				$custom_button_color_data .= "\t" . $color_option['style'] . ': ';
				if( isset( $wcfmvm_color_options[ $color_option['name'] ] ) ) { $custom_button_color_data .= $wcfmvm_color_options[ $color_option['name'] ]; } else { $custom_button_color_data .= $color_option['default']; }
				$custom_button_color_data .= ';' . "\n";
				$custom_button_color_data .= '}' . "\n\n";
			}
			
			if( isset( $color_option['element2'] ) && isset( $color_option['style2'] ) ) {
				$custom_color_data .= $color_option['element2'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style2'] . ': ';
				if( isset( $wcfmvm_color_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfmvm_color_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
			
			if( isset( $color_option['element3'] ) && isset( $color_option['style3'] ) ) {
				$custom_color_data .= $color_option['element3'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style3'] . ': ';
				if( isset( $wcfmvm_color_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfmvm_color_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
			
			if( isset( $color_option['element4'] ) && isset( $color_option['style4'] ) ) {
				$custom_color_data .= $color_option['element4'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style4'] . ': ';
				if( isset( $wcfmvm_color_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfmvm_color_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
			
			if( isset( $color_option['element5'] ) && isset( $color_option['style5'] ) ) {
				$custom_color_data .= $color_option['element5'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style5'] . ': ';
				if( isset( $wcfmvm_color_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfmvm_color_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
		}
		
		$upload_dir      = wp_upload_dir();

		$files = array(
			array(
				'key'     => 'wcfmvm_style_custom',
				'base' 		=> $upload_dir['basedir'] . '/wcfm',
				'file' 		=> 'wcfmvm-style-custom-' . time() . '.css',
				'content' => $custom_color_data,
			),
			array(
				'key'     => 'wcfmvm_style_custom_subscribe_button',
				'base' 		=> $upload_dir['basedir'] . '/wcfm',
				'file' 		=> 'wcfmvm-style-custom-subscribe-button-' . time() . '.css',
				'content' => $custom_button_color_data,
			)
		);

		$wcfmvm_style_custom = get_option( 'wcfmvm_style_custom' );
		if(  $wcfmvm_style_custom && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmvm_style_custom ) ) {
			unlink( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmvm_style_custom );
		}
		
		$wcfmvm_style_custom_subscribe_button = get_option( 'wcfmvm_style_custom_subscribe_button' );
		if(  $wcfmvm_style_custom_subscribe_button && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmvm_style_custom_subscribe_button ) ) {
			unlink( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmvm_style_custom_subscribe_button );
		}
		
		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					$wcfmvm_style_custom = $file['file'];
					update_option( $file['key'], $file['file'] );
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
		return $wcfmvm_style_custom;
	}
	
	/**
	 * Payment Gateway IPN listener 
	 */
  public function wcfmvm_ipn_listener() {
  	global $WCFM;
  	
		// Listen and handle PayPal IPN
		$wcfmvm_process_ipn = filter_input( INPUT_GET, 'wcfmvm_process_ipn' );
		if( $wcfmvm_process_ipn ) {
			wcfmvm_create_log( "WCfMvm IPN Received -->" );
			if ( $wcfmvm_process_ipn == 'paypal_ipn' ) {
				wcfmvm_create_log( "PayPal IPN Process Start -->" );
				include( $this->plugin_path . 'ipn/wcfmvm-handle-pp-ipn.php' );
				exit;
			}

			// Listen and handle Stripe Buy Now IPN
			if ( $wcfmvm_process_ipn == 'stripe_ipn' ) {
				wcfmvm_create_log( "Stripe IPN Process Start -->" );
				include( $this->plugin_path . 'ipn/wcfmvm-handle-stripe-ipn.php' );
				exit;
			}
	
			// Listen and handle Stripe Subscription IPN
			if ( $wcfmvm_process_ipn == 'stripe_subs_ipn' ) {
					include( $this->plugin_path . 'ipn/wcfmvm-handle-stripe-subs-ipn.php' );
					exit;
			}
			
			// Listen and handle Stripe SCA One Time Pay IPN
			if ( $wcfmvm_process_ipn == 'stripe_sca_ipn' ) {
					include( $this->plugin_path . 'ipn/wcfmvm-handle-stripe-sca-ipn.php' );
					exit;
			}
			
			// Listen and handle Stripe SCA Subscription IPN
			if ( $wcfmvm_process_ipn == 'stripe_sca_subs_ipn' ) {
					include( $this->plugin_path . 'ipn/wcfmvm-handle-stripe-sca-subs-ipn.php' );
					exit;
			}
			
			
	
			// Listen and handle Braintree Buy Now IPN
			/*$swpm_process_braintree_buy_now = filter_input(INPUT_GET, 'swpm_process_braintree_buy_now');
			if ($swpm_process_braintree_buy_now == '1') {
					include(SIMPLE_WP_MEMBERSHIP_PATH . 'ipn/swpm-braintree-buy-now-ipn.php');
					exit;
			}*/
		}
	}
	
	function register_vendor( $member_id ) {
		global $WCFM, $WCFMvm, $wpdb;
		
		$has_error = false;
		$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
		$shop_name = get_user_meta( $member_id, 'store_name', true );
			
		if( $wcfm_membership ) {
			$member_user = new WP_User(absint($member_id));
			if( ( $wcfm_membership != -1 ) && ( $wcfm_membership != '-1' ) ) {
				$wcfm_membership = absint( $wcfm_membership );
			}
			
			// Member static field data - 1.0.6
			$wcfmvm_registration_static_fields = wcfm_get_option( 'wcfmvm_registration_static_fields', array() );
			$wcfmvm_static_infos = (array) get_user_meta( $member_id, 'wcfmvm_static_infos', true );
			
			// Membership Commission Data
			if( ( $wcfm_membership != -1 ) && ( $wcfm_membership != '-1' ) ) {
				$commission = (array) get_post_meta( $wcfm_membership, 'commission', true );
				$commission_type = isset( $commission['type'] ) ? $commission['type'] : 'percent';
				$commission_value = isset( $commission['value'] ) ? $commission['value'] : '';
			}
			
			$wcfm_register_member = get_user_meta( $member_id, 'wcfm_register_member', true );
			$is_membership_renewal= false;
				
			$is_marketplace = wcfm_is_marketplace();
			
			if( $is_marketplace == 'wcmarketplace' ) {
				// Update user role
				$member_user->set_role('dc_vendor');
				
				// Creating Vendor Store
				$vendor = get_wcmp_vendor( $member_id );
				if ( $vendor ) {
					if( !$wcfm_register_member ) {
						$vendor->generate_term();
						$vendor->generate_shipping_class();
						$vendor->update_page_title( wc_clean( $shop_name ) );
						
						// Set Vendor Store
						update_user_meta( $member_id, '_vendor_page_title', $shop_name );
						
						// Set Vendor Static field data
						if( !empty( $wcfmvm_registration_static_fields ) && !empty( $wcfmvm_static_infos ) ) {
							foreach( $wcfmvm_registration_static_fields as $wcfmvm_registration_static_field => $wcfmvm_registration_static_field_val ) {
								$field_value = array();
								$field_name = 'wcfmvm_static_infos[' . $wcfmvm_registration_static_field . ']';
								
								if( !empty( $wcfmvm_static_infos ) ) {
									$field_value = isset( $wcfmvm_static_infos[$wcfmvm_registration_static_field] ) ? $wcfmvm_static_infos[$wcfmvm_registration_static_field] : array();
								}
								
								switch( $wcfmvm_registration_static_field ) {
									case 'address':
										if( isset($field_value['addr_1']) ) {
											$store_address_fields = array( 	
																						'_vendor_address_1'  => 'addr_1',
																						'_vendor_address_2'  => 'addr_2',
																						'_vendor_country'    => 'country',
																						'_vendor_city'       => 'city',
																						'_vendor_state'      => 'state',
																						'_vendor_postcode'   => 'zip',
																					);
			
											foreach( $store_address_fields as $store_address_field_key => $store_address_field ) {
												if( isset( $field_value[$store_address_field] ) ) {
													update_user_meta( $member_id, $store_address_field_key, $field_value[$store_address_field] );
												}
											}
										}
									break;
									
									case 'phone':
										update_user_meta( $member_id, '_vendor_phone', $field_value );
									break;
									
									default:
										do_action( 'wcfmvm_registration_static_field_data_store', $member_id, $wcfmvm_registration_static_field, $field_value );
									break;
								}
							}
						}
					}
					
					// Set Vendor commission
					if( ( $wcfm_membership != -1 ) && ( $wcfm_membership != '-1' ) ) {
						if( $commission_type && $commission_value ) {
							if( $commission_type == 'percent' ) {
								update_user_meta( $member_id, '_vendor_commission', $commission_value );
								update_user_meta( $member_id, '_vendor_commission_percentage', $commission_value );
							} else {
								update_user_meta( $member_id, '_vendor_commission', $commission_value );
								update_user_meta( $member_id, '_vendor_commission_percentage', $commission_value );
							}
						}
					}
				} else {
					$has_error = true;
				}
			} elseif( $is_marketplace == 'wcpvendors' ) {
				if( !WC_Product_Vendors_Utils::is_vendor( $member_id ) ) {
					// Creating Vendor Store
					$term = wp_insert_term( $shop_name, WC_PRODUCT_VENDORS_TAXONOMY );
					if ( ! is_wp_error( $term ) ) {
						// Set Vendor commission
						$vendor_data = array();
						$vendor_data['admins']               = $member_id;
						$vendor_data['email']                = $member_user->user_email;
						$vendor_data['per_product_shipping'] = 'yes';
						$vendor_data['commission_type']      = ( $commission_type == 'percent' ) ? 'percentage' : 'fixed';
						$vendor_data['commission']           = $commission_value;
						
						update_term_meta( $term['term_id'], 'vendor_data', $vendor_data );
						
						// Update user role
						$member_user->set_role('wc_product_vendors_admin_vendor');
						update_user_meta( $member_id, '_wcpv_active_vendor', $term['term_id'] );
					} else {
						$has_error = true;
					}
				}
			} elseif( $is_marketplace == 'wcvendors' ) {
				
				// Update user role
				$member_user->set_role('vendor');
				
				if( !$wcfm_register_member ) {
					// Set Vendor Store
					update_user_meta( $member_id, 'pv_shop_name', $shop_name );
					update_user_meta( $member_id, 'pv_shop_slug', sanitize_title( $shop_name ) );
					
					// Set Vendor Static field data
					if( !empty( $wcfmvm_registration_static_fields ) && !empty( $wcfmvm_static_infos ) ) {
						foreach( $wcfmvm_registration_static_fields as $wcfmvm_registration_static_field => $wcfmvm_registration_static_field_val ) {
							$field_value = array();
							$field_name = 'wcfmvm_static_infos[' . $wcfmvm_registration_static_field . ']';
							
							if( !empty( $wcfmvm_static_infos ) ) {
								$field_value = isset( $wcfmvm_static_infos[$wcfmvm_registration_static_field] ) ? $wcfmvm_static_infos[$wcfmvm_registration_static_field] : array();
							}
							
							switch( $wcfmvm_registration_static_field ) {
								case 'address':
									if( isset($field_value['addr_1']) ) {
										$store_address_fields = array( 	
																					'_wcv_store_address1'   => 'addr_1',
																					'_wcv_store_address2'   => 'addr_2',
																					'_wcv_store_country'    => 'country',
																					'_wcv_store_city'       => 'city',
																					'_wcv_store_state'      => 'state',
																					'_wcv_store_postcode'   => 'zip',
																				);
		
										foreach( $store_address_fields as $store_address_field_key => $store_address_field ) {
											if( isset( $field_value[$store_address_field] ) ) {
												update_user_meta( $member_id, $store_address_field_key, $field_value[$store_address_field] );
											}
										}
									}
								break;
								
								case 'phone':
									update_user_meta( $member_id, '_wcv_store_phone', $field_value );
								break;
								
								default:
									do_action( 'wcfmvm_registration_static_field_data_store', $member_id, $wcfmvm_registration_static_field, $field_value );
								break;
							}
						}
					}
				}
				
				// Set Vendor commission
				if( ( $wcfm_membership != -1 ) && ( $wcfm_membership != '-1' ) ) {
					if( $commission_type && $commission_value ) {
						update_user_meta( $member_id, 'pv_custom_commission_rate', $commission_value );
						if( $commission_type == 'percent' ) {
							update_user_meta( $member_id, '_wcv_commission_type', 'percent' );
							update_user_meta( $member_id, '_wcv_commission_percent', $commission_value );
						} else {
							update_user_meta( $member_id, '_wcv_commission_type', 'fixed' );
							update_user_meta( $member_id, '_wcv_commission_amount', $commission_value );
						}
					}
				}
			} elseif( $is_marketplace == 'dokan' ) {
				// Update user role
				$member_user->set_role('seller');
				
				$dokan_settings = (array) get_user_meta( $member_id, 'dokan_profile_settings', true );
				if( $dokan_settings && !empty( $dokan_settings ) ) $wcfm_register_member = true;
				
				if( !$wcfm_register_member ) {
					$dokan_settings = array(
							'store_name'     => strip_tags( $shop_name ),
							'social'         => array(),
							'payment'        => array(),
							'phone'          => '',
							'show_email'     => 'no',
							'location'       => '',
							'find_address'   => '',
							'dokan_category' => '',
							'banner'         => 0,
					);
			
					update_user_meta( $member_id, 'dokan_profile_settings', $dokan_settings );
					
					// Set Vendor Store
					update_user_meta( $member_id, 'dokan_store_name', $shop_name );
					
					// Set Vendor Static field data
					if( !empty( $wcfmvm_registration_static_fields ) && !empty( $wcfmvm_static_infos ) ) {
						foreach( $wcfmvm_registration_static_fields as $wcfmvm_registration_static_field => $wcfmvm_registration_static_field_val ) {
							$field_value = array();
							$field_name = 'wcfmvm_static_infos[' . $wcfmvm_registration_static_field . ']';
							
							if( !empty( $wcfmvm_static_infos ) ) {
								$field_value = isset( $wcfmvm_static_infos[$wcfmvm_registration_static_field] ) ? $wcfmvm_static_infos[$wcfmvm_registration_static_field] : array();
							}
							
							switch( $wcfmvm_registration_static_field ) {
								case 'address':
									if( isset($field_value['addr_1']) ) {
										$dokan_settings['address'] = array();
										$store_address_fields = array( 	
																					'street_1'   => 'addr_1',
																					'street_2'   => 'addr_2',
																					'country'    => 'country',
																					'city'       => 'city',
																					'state'      => 'state',
																					'zip'        => 'zip',
																				);
		
										foreach( $store_address_fields as $store_address_field_key => $store_address_field ) {
											if( isset( $field_value[$store_address_field] ) ) {
												$dokan_settings['address'][$store_address_field_key] = $field_value[$store_address_field];
											}
										}
									}
								break;
								
								case 'phone':
									$dokan_settings['phone'] = $field_value;
								break;
								
								default:
									do_action( 'wcfmvm_registration_static_field_data_store', $member_id, $wcfmvm_registration_static_field, $field_value );
								break;
							}
						}
						update_user_meta( $member_id, 'dokan_profile_settings', $dokan_settings );
					}
				}
				
				// Set Vendor commission
				if( ( $wcfm_membership != -1 ) && ( $wcfm_membership != '-1' ) ) {
					if( $commission_type && $commission_value ) {
						if( $commission_type == 'percent' ) {
							update_user_meta( $member_id, 'dokan_admin_percentage_type', 'percentage' );
							update_user_meta( $member_id, 'dokan_admin_percentage', $commission_value );
						} else {
							update_user_meta( $member_id, 'dokan_admin_percentage_type', 'flat' );
							update_user_meta( $member_id, 'dokan_admin_percentage', $commission_value );
						}
					}
				}
				
				update_user_meta( $member_id, 'dokan_enable_selling', 'yes' );
				update_user_meta( $member_id, 'dokan_publishing', 'yes' );
				update_user_meta( $member_id, 'can_post_product', '1' );
				do_action( 'dokan_new_seller_created', $member_id, $dokan_settings );
			} elseif( $is_marketplace == 'wcfmmarketplace' ) {
				// Update user role
				if( !wcfm_is_vendor( $member_id ) ) {
					if( ( function_exists( 'wcfm_is_affiliate' ) && wcfm_is_affiliate( $member_id ) ) || apply_filters( 'wcfm_is_allow_merge_vendor_role', false ) ) {
						$member_user->add_role('wcfm_vendor');
					} else {
						$member_user->set_role('wcfm_vendor');
					}
				}
				
				$wcfmmp_settings = get_user_meta( $member_id, 'wcfmmp_profile_settings', true );
				if( $wcfmmp_settings && !empty( $wcfmmp_settings ) ) $wcfm_register_member = true;
				
				if( !$wcfm_register_member ) {
					$wcfmmp_settings = array(
							'store_name'       => strip_tags( $shop_name ),
							'social'           => array(),
							'payment'          => array(),
							'phone'            => '',
							'show_email'       => 'no',
							'location'         => '',
							'find_address'     => '',
							'banner'           => 0,
							'customer_support' => array()
					);
			
					update_user_meta( $member_id, 'wcfmmp_profile_settings', $wcfmmp_settings );
					
					// Set Vendor Store
					update_user_meta( $member_id, 'wcfmmp_store_name', $shop_name );
					
					// Set Vendor Shipping
					$wcfmmp_shipping = array ( '_wcfmmp_user_shipping_enable' => 'yes', '_wcfmmp_user_shipping_type' => 'by_zone' );
					update_user_meta( $member_id, '_wcfmmp_shipping', $wcfmmp_shipping );
					
					$wcfmmp_settings['customer_support']['email'] = $member_user->user_email;
					
					// Set Vendor Static field data
					if( !empty( $wcfmvm_registration_static_fields ) && !empty( $wcfmvm_static_infos ) ) {
						foreach( $wcfmvm_registration_static_fields as $wcfmvm_registration_static_field => $wcfmvm_registration_static_field_val ) {
							$field_value = array();
							$field_name = 'wcfmvm_static_infos[' . $wcfmvm_registration_static_field . ']';
							
							if( !empty( $wcfmvm_static_infos ) ) {
								$field_value = isset( $wcfmvm_static_infos[$wcfmvm_registration_static_field] ) ? $wcfmvm_static_infos[$wcfmvm_registration_static_field] : array();
							}
							
							switch( $wcfmvm_registration_static_field ) {
								case 'address':
									if( isset($field_value['addr_1']) ) {
										$wcfmmp_settings['address'] = array();
										$store_address_fields = array( 	
																					'street_1'   => 'addr_1',
																					'street_2'   => 'addr_2',
																					'country'    => 'country',
																					'city'       => 'city',
																					'state'      => 'state',
																					'zip'        => 'zip',
																				);
		
										foreach( $store_address_fields as $store_address_field_key => $store_address_field ) {
											if( isset( $field_value[$store_address_field] ) ) {
												$wcfmmp_settings['address'][$store_address_field_key] = $field_value[$store_address_field];
											}
										}
										
										// Customer Support Address
										$store_address_fields = array( 	
																					'address1'   => 'addr_1',
																					'address2'   => 'addr_2',
																					'country'    => 'country',
																					'city'       => 'city',
																					'state'      => 'state',
																					'zip'        => 'zip',
																				);
		
										foreach( $store_address_fields as $store_address_field_key => $store_address_field ) {
											if( isset( $field_value[$store_address_field] ) ) {
												$wcfmmp_settings['customer_support'][$store_address_field_key] = $field_value[$store_address_field];
											}
										}
									}
								break;
								
								case 'phone':
									$wcfmmp_settings['phone'] = $field_value;
									$wcfmmp_settings['customer_support']['phone'] = $field_value;
								break;
								
								default:
									do_action( 'wcfmvm_registration_static_field_data_store', $member_id, $wcfmvm_registration_static_field, $field_value );
								break;
							}
						}
						update_user_meta( $member_id, 'wcfmmp_profile_settings', $wcfmmp_settings );
					}
				}
				
				// Set Vendor commission
				if( ( $wcfm_membership != -1 ) && ( $wcfm_membership != '-1' ) ) {
					if( !$wcfmmp_settings ) $wcfmmp_settings = array();
					$commission_data = (array) get_post_meta( $wcfm_membership, 'commission', true );
					$wcfmmp_settings['commission'] = $commission_data;
					update_user_meta( $member_id, 'wcfmmp_profile_settings', $wcfmmp_settings );
				}
				
				do_action( 'wcfmmp_new_store_created', $member_id, $wcfmmp_settings );
			}
			
			if( !$has_error ) {
			
				// Remove Disable Vendor tag
				delete_user_meta( $member_id, '_disable_vendor' );
				delete_user_meta( $member_id, 'expired_wcfm_membership' );
				
				$WCFM->wcfm_vendor_support->reset_vendor_product_status( $member_id, 'publish', 'archived' );
				
				$current_time = strtotime( 'midnight', current_time( 'timestamp' ) );
				
				if( !$wcfm_register_member ) {
					// WCFM Unique IDs
					update_user_meta( $member_id, '_wcfmmp_profile_id', $member_id );
					update_user_meta( $member_id, '_wcfmmp_unique_id', current_time( 'timestamp' ) );
				
					update_user_meta( $member_id, 'wcfm_register_on', $current_time );
					update_user_meta( $member_id, '_wcfmmp_avg_review_rating', 0 );
					update_user_meta( $member_id, 'wcfm_vendor_store_hours_migrated', 'yes' );
				}
				
				if( ( $wcfm_membership != -1 ) && ( $wcfm_membership != '-1' ) ) {
					// Check and release from old membership
					$vendor_old_membership = get_user_meta( $member_id, 'wcfm_membership', true );
					if( $vendor_old_membership && wcfm_is_valid_membership( $vendor_old_membership ) && ( $vendor_old_membership != $wcfm_membership ) ) {
						// Old Membership list update
						$old_membership_users = (array) get_post_meta( $vendor_old_membership, 'membership_users', true );
						if( !empty( $old_membership_users ) ) {
							if( ( $key = array_search( $member_id, $old_membership_users ) ) !== false ) {
								unset( $old_membership_users[$key] );
							}
							update_post_meta( $vendor_old_membership, 'membership_users', $old_membership_users );
						}
						
						// Old Group vendor list update
						$old_associated_group = get_post_meta( $vendor_old_membership, 'associated_group', true );
						if( $old_associated_group ) {
							$old_group_vendors = (array) get_post_meta( $old_associated_group, '_group_vendors', true );
							if( !empty( $old_group_vendors ) ) {
								if( ( $key = array_search( $member_id, $old_group_vendors ) ) !== false ) {
									unset( $old_group_vendors[$key] );
								}
								update_post_meta( $old_associated_group, '_group_vendors', $old_group_vendors );
							}
						}
					}
					
					// User membership set
					update_user_meta( $member_id, 'wcfm_membership', $wcfm_membership );
					
					// Membership Scheduler
					$subscription = (array) get_post_meta( $wcfm_membership, 'subscription', true );
					$is_restricted = isset( $subscription['is_restricted'] ) ? 'yes' : 'no';
					$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
					$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
					$one_time_amt = isset( $subscription['one_time_amt'] ) ? floatval($subscription['one_time_amt']) : '1';
					$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
					$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
					$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
					$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
					$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : 999;
					if( !$billing_period_count ) $billing_period_count = 999;
					$free_expiry_period = isset( $subscription['free_expiry_period'] ) ? $subscription['free_expiry_period'] : '';
					$free_expiry_period_type = isset( $subscription['free_expiry_period_type'] ) ? $subscription['free_expiry_period_type'] : 'M';
					$period_options = array( 'D' => 'days', 'M' => 'months', 'Y' => 'years' );
					
					// Restricted Membership
					if( $is_restricted == 'yes' ) {
						$wcfm_restricted_memberships = get_user_meta( $member_id, 'wcfm_restricted_memberships', true ); 
						if( !$wcfm_restricted_memberships ) $wcfm_restricted_memberships = array();
						$wcfm_restricted_memberships[] = $wcfm_membership;
						update_user_meta( $member_id, 'wcfm_restricted_memberships', $wcfm_restricted_memberships );
					}
					
					// Renewal Current Time
					update_user_meta( $member_id, 'wcfm_membership_subscribe_on', $current_time );
					if( apply_filters( 'wcfm_is_allow_expiry_as_per_next_schedule', true ) && $vendor_old_membership && wcfm_is_valid_membership( $vendor_old_membership ) && ( $vendor_old_membership == $wcfm_membership ) ) {
						$next_schedule = get_user_meta( $member_id, 'wcfm_membership_next_schedule', true );
						if( $next_schedule ) {
							$date = date( 'Y-m-d', $current_time );
							$renewal_date = date( 'Y-m-d', $next_schedule );
							$datetime1 = new DateTime( $date );
							$datetime2 = new DateTime( $renewal_date );
							$interval = $datetime1->diff( $datetime2 );
							$interval = $interval->format( '%r%a' );
							if( (int) $interval > 0 ) {
								$current_time = $next_schedule;
							}
						}
						
						$is_membership_renewal = true;
					}
					
					if( ( $is_free == 'no' ) && ( $subscription_type != 'one_time' ) ) {
						if( !empty( $trial_period ) ) {
							$next_payment_time = strtotime( '+' . $trial_period . ' ' . $period_options[$trial_period_type], $current_time );
							update_user_meta( $member_id, 'wcfm_membership_billing_cycle', 0 );
						} elseif( !empty( $billing_period ) ) {
							$next_payment_time = strtotime( '+' . $billing_period . ' ' . $period_options[$billing_period_type], $current_time );
							update_user_meta( $member_id, 'wcfm_membership_billing_cycle', 1 );
						}
						update_user_meta( $member_id, 'wcfm_membership_next_schedule', $next_payment_time );
						update_user_meta( $member_id, 'wcfm_membership_billing_period', $billing_period_count );
					} elseif( $free_expiry_period ) {
						$next_payment_time = strtotime( '+' . $free_expiry_period . ' ' . $period_options[$free_expiry_period_type], $current_time );
						update_user_meta( $member_id, 'wcfm_membership_billing_cycle', 1 );
						update_user_meta( $member_id, 'wcfm_membership_next_schedule', $next_payment_time );
						update_user_meta( $member_id, 'wcfm_membership_billing_period', 1 );
					} else {
						delete_user_meta( $member_id, 'wcfm_membership_billing_cycle' );
						delete_user_meta( $member_id, 'wcfm_membership_next_schedule' );
						delete_user_meta( $member_id, 'wcfm_membership_billing_period' ); 
					}
						
					// Membership user update
					$membership_users = (array) get_post_meta( $wcfm_membership, 'membership_users', true );
					$membership_users[] = $member_id;
					update_post_meta( $wcfm_membership, 'membership_users', $membership_users );
						
					// Group user update
					if( WCFM_Dependencies::wcfmgs_plugin_active_check() && !$is_membership_renewal ) {
						$associated_group = get_post_meta( $wcfm_membership, 'associated_group', true );
						if( $associated_group ) {
							$associated_group = absint( $associated_group );
							$group_vendors = (array) get_post_meta( $associated_group, '_group_vendors', true );
							$group_vendors[] = $member_id;
							update_post_meta( $associated_group, '_group_vendors', $group_vendors );
							
							// User Group update
							$wcfm_vendor_groups = array( $associated_group );
							update_user_meta( $member_id, '_wcfm_vendor_group', $wcfm_vendor_groups  );
							update_user_meta( $member_id, '_wcfm_vendor_group_list', implode( ",", array_unique( $wcfm_vendor_groups ) ) );
						}
					}
				
					do_action( 'wcfm_membership_subscription_complete', $member_id );
					
					// Plan Details Table
					$wcfm_plan_details = wcfm_membership_features_table( $wcfm_membership );
				
					if( !defined( 'DOING_WCFM_EMAIL' ) ) 
						define( 'DOING_WCFM_EMAIL', true );
					
					// Sending Mail to new subscriber
					if( !$wcfm_register_member ) {
						$subscription_welcome_email_subject = wcfm_get_post_meta( $wcfm_membership, 'subscription_welcome_email_subject', true );
						$subscription_welcome_email_content = wcfm_get_post_meta( $wcfm_membership, 'subscription_welcome_email_content', true );
						
						if( !$subscription_welcome_email_subject ) {
							$subscription_welcome_email_subject = wcfm_get_option( 'wcfm_membership_subscription_welcome_email_subject', '[{site_name}] Successfully Subscribed' );
						}
						if( !$subscription_welcome_email_content ) {
							$subscription_welcome_email_content = wcfm_get_option( 'wcfm_membership_subscription_welcome_email_content', '' );
							if( !$subscription_welcome_email_content ) {
								$subscription_welcome_email_content = "Hi {first_name},
																			<br /><br />
																			You have successfully registered as a vendor for <b>{site_name}</b>. 
																			<br /><br />
																			Your account has been setup and it is ready to be configured.
																			<br /><br />
																			{plan_details}
																			<br /><br />
																			Kindly follow the link below to visit your dashboard and start selling.
																			<br /><br />
																			Dashboard: {dashboard_url} 
																			<br /><br />
																			Thank You";
							}
						}
					} elseif( $is_membership_renewal ) {
						$subscription_welcome_email_subject = wcfm_get_option( 'wcfm_membership_renewal_notication_subject', '[{site_name}] Membership Subscription Successfully Renewed' );
						$subscription_welcome_email_content = wcfm_get_option( 'wcfm_membership_renewal_notication_content', '' );
						if( !$subscription_welcome_email_content ) {
							$subscription_welcome_email_content = "Hi {first_name},
																						<br /><br />
																						You have successfully renewed membership plan <b>{membership_plan}</b>. 
																						<br /><br />
																						{plan_details}
																						<br /><br />
																						Kindly follow the below the link to visit your dashboard.
																						<br /><br />
																						Dashboard: {dashboard_url} 
																						<br /><br />
																						Thank You";
						}
					} else {
						$subscription_welcome_email_subject = wcfm_get_option( 'wcfm_membership_switch_notication_subject', '[{site_name}] Membership Subscription Successfully Changed' );
						$subscription_welcome_email_content = wcfm_get_option( 'wcfm_membership_switch_notication_content', '' );
						if( !$subscription_welcome_email_content ) {
							$subscription_welcome_email_content = "Hi {first_name},
																						<br /><br />
																						You have successfully changed membership plan to <b>{membership_plan}</b>. 
																						<br /><br />
																						Your account already setup and it is ready to be configured.
																						<br /><br />
																						{plan_details}
																						<br /><br />
																						Kindly follow the below the link to visit your dashboard.
																						<br /><br />
																						Dashboard: {dashboard_url} 
																						<br /><br />
																						Thank You";
						}
					}
																	 
					$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $subscription_welcome_email_subject );
					$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
					$message = str_replace( '{dashboard_url}', '<a href="' . get_wcfm_url() . '">' . __( 'Visit now ...', 'wc-multivendor-membership' ) . '</a>', $subscription_welcome_email_content );
					$message = str_replace( '{first_name}', $member_user->first_name, $message );
					$message = str_replace( '{site_name}', get_bloginfo( 'name' ), $message );
					$message = str_replace( '{plan_details}', $wcfm_plan_details, $message );
					$message = str_replace( '{store}', wcfm_get_vendor_store( $member_id ), $message );
					$message = str_replace( '{store_name}', wcfm_get_vendor_store_name( $member_id ), $message );
					$message = str_replace( '{store_url}', wcfmmp_get_store_url( $member_id ), $message );
					$message = str_replace( '{membership_plan}', get_the_title( $wcfm_membership ), $message );
					if( !$wcfm_register_member ) {
						$message = apply_filters( 'wcfm_email_content_wrapper', $message, apply_filters( 'wcfm_welcome_email_header', __( 'Welcome to the store!', 'wc-multivendor-membership' ) ) );
					} elseif( $is_membership_renewal ) {
						$message = apply_filters( 'wcfm_email_content_wrapper', $message, apply_filters( 'wcfm_membership_renewal_header', __( 'Membership Subscription Renewed', 'wc-multivendor-membership' ) ) );
					} else {
						$message = apply_filters( 'wcfm_email_content_wrapper', $message, apply_filters( 'wcfm_membership_change_header', __( 'Membership Subscription Change', 'wc-multivendor-membership' ) ) );
					}
					$attachments = apply_filters( 'wcfm_membership_subscription_attachment', '', $wcfm_membership, $member_id, $wcfm_register_member );
					
					if( apply_filters( 'wcfm_is_allow_vendor_welcome_email', true, $member_id, $wcfm_register_member, $is_membership_renewal ) ) {
						wp_mail( $member_user->user_email, $subject, $message, '', $attachments );
					}
					
					// Sending Mail to Admin
					if( !$wcfm_register_member ) {
						$subscription_admin_notication_subject = wcfm_get_option( 'wcfm_membership_subscription_admin_notication_subject', '[{site_name}] A new vendor registered' );
						$subscription_admin_notication_content = wcfm_get_option( 'wcfm_membership_subscription_admin_notication_content', '' );
						if( !$subscription_admin_notication_content ) {
							$subscription_admin_notication_content = "Dear Admin,
																												<br /><br />
																												A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) successfully subscribed to membership plan <b>{membership_plan}</b>. 
																												<br /><br />
																												{plan_details}
																												<br /><br />
																												Kindly follow the below the link to more details.
																												<br /><br />
																												Dashboard: {vendor_url} 
																												<br /><br />
																												Thank You";
						}
					} elseif( $is_membership_renewal ) {
						$subscription_admin_notication_subject = wcfm_get_option( 'wcfm_membership_renewal_admin_notication_subject', '[{site_name}] Vendor Membership Subscription Renewed' );
						$subscription_admin_notication_content = wcfm_get_option( 'wcfm_membership_renewal_admin_notication_content', '' );
						if( !$subscription_admin_notication_content ) {
							$subscription_admin_notication_content = "Dear Admin,
																												<br /><br />
																												<b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) has renewed membership plan <b>{membership_plan}</b>.
																												<br /><br />
																												{plan_details} 
																												<br /><br />
																												Kindly follow the below the link to more details.
																												<br /><br />
																												Dashboard: {vendor_url} 
																												<br /><br />
																												Thank You";
									}
					} else {
						$subscription_admin_notication_subject = wcfm_get_option( 'wcfm_membership_switch_admin_notication_subject', '[{site_name}] Vendor Membership Subscription Change' );
						$subscription_admin_notication_content = wcfm_get_option( 'wcfm_membership_switch_admin_notication_content', '' );
						if( !$subscription_admin_notication_content ) {
							$subscription_admin_notication_content = "Dear Admin,
																												<br /><br />
																												<b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) has changed membership plan to <b>{membership_plan}</b>.
																												<br /><br />
																												{plan_details} 
																												<br /><br />
																												Kindly follow the below the link to more details.
																												<br /><br />
																												Dashboard: {vendor_url} 
																												<br /><br />
																												Thank You";
									}
					}
																	 
					$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $subscription_admin_notication_subject );
					$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
					$message = str_replace( '{dashboard_url}', get_wcfm_url(), $subscription_admin_notication_content );
					$message = str_replace( '{vendor_name}', $member_user->first_name, $message );
					$message = str_replace( '{plan_details}', $wcfm_plan_details, $message );
					$message = str_replace( '{vendor_store}', $shop_name, $message );
					$message = str_replace( '{store}', wcfm_get_vendor_store( $member_id ), $message );
					$message = str_replace( '{store_name}', wcfm_get_vendor_store_name( $member_id ), $message );
					$message = str_replace( '{store_url}', wcfmmp_get_store_url( $member_id ), $message );
					$message = str_replace( '{membership_plan}', get_the_title( $wcfm_membership ), $message );
					$message = str_replace( '{vendor_url}', '<a href="' . get_wcfm_vendors_manage_url($member_id) . '">' . __( 'Visit now ...', 'wc-multivendor-membership' ) . '</a>', $message );
					if( !$wcfm_register_member ) {
						$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'New Vendor', 'wc-multivendor-membership' ) );
					} elseif( $is_membership_renewal ) {
						$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Membership Subscription Renewed', 'wc-multivendor-membership' ) );
					} else {
						$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Membership Subscription Change', 'wc-multivendor-membership' ) );
					}
					
					wp_mail( apply_filters( 'wcfm_admin_email_notification_receiver', get_bloginfo( 'admin_email' ), 'membership' ), $subject, $message, '', $attachments ); 
					
					// Admin Desktop Notification
					if( !$wcfm_register_member ) {
						$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) subscribed for <b>%s</b> membership plan.', 'wc-multivendor-membership' ), '<a href="' . get_wcfm_vendors_manage_url($member_id) . '" target="_blank">' . $member_user->first_name . '</a>', $shop_name, get_the_title( $wcfm_membership ) );
					} elseif( $is_membership_renewal ) {
						$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) has renewed membership plan to <b>%s</b>.', 'wc-multivendor-membership' ), '<a href="' . get_wcfm_vendors_manage_url($member_id) . '" target="_blank">' . $member_user->first_name . '</a>', $shop_name, get_the_title( $wcfm_membership ) );
					} else {
						$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) has changed membership plan to <b>%s</b>.', 'wc-multivendor-membership' ), '<a href="' . get_wcfm_vendors_manage_url($member_id) . '" target="_blank">' . $member_user->first_name . '</a>', $shop_name, get_the_title( $wcfm_membership ) );
					}
					$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'membership', false );
					
					// Vendor Desktop Notification
					if( !$wcfm_register_member ) {
						$wcfm_messages = sprintf( __( 'You have subscribed for <b>%s</b> membership plan.', 'wc-multivendor-membership' ), get_the_title( $wcfm_membership ) );
					} elseif( $is_membership_renewal ) {
						$wcfm_messages = sprintf( __( 'You have renewed membership plan to <b>%s</b>.', 'wc-multivendor-membership' ), get_the_title( $wcfm_membership ) );
					} else {
						$wcfm_messages = sprintf( __( 'You have changed membership plan to <b>%s</b>.', 'wc-multivendor-membership' ), get_the_title( $wcfm_membership ) );
					}
					$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'membership', false );
				} else {
					// Non membership registration
					
					if( !defined( 'DOING_WCFM_EMAIL' ) ) 
						define( 'DOING_WCFM_EMAIL', true );
					
					// Vendor Notification
					$registration_email_subject = wcfm_get_option( 'wcfm_non_membership_welcome_email_subject', '[{site_name}] Successfully Registered' );
					$registration_email_content = wcfm_get_option( 'wcfm_non_membership_welcome_email_content', '' );
					if( !$registration_email_content ) {
						$registration_email_content = "Hi {first_name},
																							<br /><br />
																							You have successfully registered as a vendor for <b>{site_name}</b>. 
																							<br /><br />
																							Your account has been setup and it is ready to be configured.
																							<br /><br />
																							Kindly follow the link below to visit your dashboard and start selling.
																							<br /><br />
																							Dashboard: {dashboard_url} 
																							<br /><br />
																							Thank You";
					}
																						
					$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $registration_email_subject );
					$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
					$message = str_replace( '{dashboard_url}', '<a href="' . get_wcfm_url() . '">' . __( 'Visit now ...', 'wc-multivendor-membership' ) . '</a>', $registration_email_content );
					$message = str_replace( '{first_name}', $member_user->first_name, $message );
					$message = str_replace( '{site_name}', get_bloginfo( 'name' ), $message );
					$message = str_replace( '{store}', wcfm_get_vendor_store( $member_id ), $message );
					$message = str_replace( '{store_name}', wcfm_get_vendor_store_name( $member_id ), $message );
					$message = str_replace( '{store_url}', wcfmmp_get_store_url( $member_id ), $message );
					$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Welcome to the store!', 'wc-multivendor-membership' ) );
					
					if( apply_filters( 'wcfm_is_allow_vendor_welcome_email', true, $member_id, false, false ) ) {
						wp_mail( $member_user->user_email, $subject, $message );
					}
					
					// Admin Notification
					$registration_admin_notication_subject = wcfm_get_option( 'wcfm_registration_admin_notication_subject', __( '[{site_name}] A new vendor registered', 'wc-multivendor-membership' ) );
					$registration_admin_notication_content = wcfm_get_option( 'wcfm_registration_admin_notication_content', '' );
					if( !$registration_admin_notication_content ) {
						$registration_admin_notication_content = "Dear Admin,
																											<br /><br />
																											A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) successfully registered to our site. 
																											<br /><br />
																											Kindly follow the below the link to more details.
																											<br /><br />
																											Dashboard: {vendor_url} 
																											<br /><br />
																											Thank You";
					}
					
					$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $registration_admin_notication_subject );
					$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
					$message = str_replace( '{dashboard_url}', get_wcfm_url(), $registration_admin_notication_content );
					$message = str_replace( '{vendor_name}', $member_user->first_name, $message );
					$message = str_replace( '{vendor_store}', $shop_name, $message );
					$message = str_replace( '{store}', wcfm_get_vendor_store( $member_id ), $message );
					$message = str_replace( '{store_name}', wcfm_get_vendor_store_name( $member_id ), $message );
					$message = str_replace( '{store_url}', wcfmmp_get_store_url( $member_id ), $message );
					$message = str_replace( '{vendor_url}', '<a href="' . get_wcfm_vendors_manage_url($member_id) . '">' . __( 'Visit now ...', 'wc-multivendor-membership' ) . '</a>', $message );
					$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'New Vendor', 'wc-multivendor-membership' ) );
					
					wp_mail( apply_filters( 'wcfm_admin_email_notification_receiver', get_bloginfo( 'admin_email' ), 'registration' ), $subject, $message ); 
					
					// Admin Desktop Notification
					$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) successfully registered to our site.', 'wc-multivendor-membership' ), '<a class="wcfm_dashboard_item_title" href="' . get_wcfm_vendors_manage_url($member_id) . '" target="_blank">' . $member_user->first_name . '</a>', $shop_name );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'registration', false );
					
					// Vendor Desktop Notification
					$wcfm_messages = __( 'You have successfully registered to our site.', 'wc-multivendor-membership' );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'registration', false );
				}
							
				delete_user_meta( $member_id, 'temp_wcfm_membership' );
				delete_user_meta( $member_id, 'wcfm_membership_application_status' );
				delete_user_meta( $member_id, 'wcfm_is_send_approval_reminder_admin' );
				update_user_meta( $member_id, 'wcfm_register_member', 'yes' );
				update_user_meta( $member_id, 'wcemailverified', 'true' );	
			}
		}
		return $has_error;
	}
	
	function store_subscription_data( $member_id, $paymode, $transaction_id, $transaction_type, $transaction_status, $transaction_details ) {
		global $WCFM, $WCFMvm, $wpdb;
		
		$has_error = false;
		$member_id = absint( $member_id );
		$membership_id = get_user_meta( $member_id, 'wcfm_membership', true );
		
		if( !$membership_id ) {
			$membership_id = get_user_meta( $member_id, 'temp_wcfm_membership', true );
		}
			
		if( $membership_id ) {
			$member_user = new WP_User( $member_id );
			$membership_id = absint( $membership_id );
			
			$subscription = (array) get_post_meta( $membership_id, 'subscription', true );
			$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
			$period_options = array( 'D' => __( 'Day(s)', 'wc-multivendor-membership' ), 'M' => __( 'Month(s)', 'wc-multivendor-membership' ), 'Y' => __( 'Year(s)', 'wc-multivendor-membership' ) );
			$subscription_type = '';
			$subscription_amt = 0;
			$subscription_interval = '';
			
			// Update quick info in User Profile
			if( $transaction_status != 'Cancelled' ) {
				update_user_meta( $member_id, 'wcfm_transaction_id', $transaction_id );
				update_user_meta( $member_id, 'wcfm_subscription_status', 'active' );
				update_user_meta( $member_id, 'wcfm_membership_paymode', $paymode );
				delete_user_meta( $member_id, 'wcfm_membership_application_status' );
			} else {
				update_user_meta( $member_id, 'wcfm_subscription_status', 'cancelled' );
				delete_user_meta( $member_id, 'wcfm_membership_application_status' );
			}
				
			if( $is_free == 'yes' ) {
				$subscription_type = 'free';
				update_user_meta( $member_id, 'wcfm_subscription_status', 'active' );
			} else {
				$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
				
				if( ( $paymode == 'paypal' || $paymode == 'stripe' || $paymode == 'bank_transfer' ) && ( $subscription_type == 'one_time' ) ) {
					$subscription_amt = isset( $subscription['one_time_amt'] ) ? floatval($subscription['one_time_amt']) : '1';
					$subscription_interval = 0;
				} elseif( ( $paymode == 'paypal' || $paymode == 'stripe' || $paymode == 'bank_transfer' ) && ( $subscription_type == 'recurring' ) ) {
					$subscription_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '';
					$subscription_interval = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
					$subscription_interval .= ' ' . $period_options[$subscription['trial_period_type']];
				} elseif( ( $paymode == 'paypal_subs' || $paymode == 'stripe_subs' || $paymode == 'bank_transfer_subs' ) && ( $subscription_type == 'recurring' ) ) {
					$subscription_amt = isset( $subscription['billing_amt'] ) ? floatval($subscription['billing_amt']) : '1';
					$subscription_interval = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '';
					$subscription_interval .= ' ' . $period_options[$subscription['billing_period_type']];
					
					update_user_meta( $member_id, 'wcfm_subscription_profile_id', $transaction_id );
					if( $transaction_status != 'Completed' ) {
						update_user_meta( $member_id, 'wcfm_subscription_status', 'blocked' );
					}
				}
			}
			$subscription_amt = absint($subscription_amt);
			
			// Update quick info in User Profile
			update_user_meta( $member_id, 'wcfm_subscription_type', $subscription_type );
			
			// Membership Subscription Query
			$wcfm_membership_subscription = "INSERT into {$wpdb->prefix}wcfm_membership_subscription 
																			(`vendor_id`, `membership_id`, `subscription_type`, `subscription_amt`, `subscription_interval`, `event`, `pay_mode`, `transaction_id`, `transaction_type`, `transaction_status`, `transaction_details`)
																			VALUES
																			( $member_id, $membership_id, '{$subscription_type}', {$subscription_amt}, '{$subscription_interval}', '{$transaction_type}', '{$paymode}', '{$transaction_id}', '{$transaction_type}', '{$transaction_status}', '{$transaction_details}')
																			ON DUPLICATE KEY UPDATE
																			`subscription_type`     = '{$subscription_type}',
																			`subscription_amt`      =  {$subscription_amt},
																			`subscription_interval` = '{$subscription_interval}',
																			`event`                 = '{$transaction_type}',
																			`pay_mode`              = '{$paymode}',
																			`transaction_status`    = '{$transaction_status}',
																			`transaction_details`   = '{$transaction_details}'
																			";
			$wpdb->query($wcfm_membership_subscription);
		}
	}
	
	/**
	 * WC Checkout membership purchase registration process on Order complete
	 */
	function wcfmvm_registration_process_on_order_completed( $order_id ) {
		global $WCFM, $WCFMvm, $wpdb;
		$wcfm_subcription_products = get_option( 'wcfm_subcription_products', array() );
		
		$wcfm_membership_order_processed = get_post_meta( $order_id, '_wcfm_membership_order_processed', true );
		if( $wcfm_membership_order_processed ) return;
		
		if( !empty( $wcfm_subcription_products ) ) {
			$order         = new WC_Order( $order_id );
			$line_items    = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
			foreach ( $line_items as $item_id => $item ) {
				$product_id = $item->get_product_id();
				
				// WPML Support
				if ( $product_id && defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
				  global $sitepress;
				  $default_language = $sitepress->get_default_language();
				  $product_id = icl_object_id( $product_id, 'product', false, $default_language );
				}
				
				if( in_array( $product_id , $wcfm_subcription_products ) ) {
					$member_id       = absint( $order->get_user_id() );
					$member_user     = new WP_User( absint( $member_id ) );
					$shop_name       = get_user_meta( $member_id, 'store_name', true );
					$paymode         = $order->get_payment_method();
					
					// Fetching Membership Membership Plan
					$is_renewal = false;
					$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
					if( !$wcfm_membership ) {
						$is_renewal = true;
						$wcfm_membership = get_user_meta( $member_id, 'wcfm_membership', true );
					}
					if( !$wcfm_membership ) {
						$is_renewal = true;
						$wcfm_membership = get_user_meta( $member_id, 'expired_wcfm_membership', true );
					}
					
					if( $wcfm_membership ) {
						update_user_meta( $member_id, 'wcfm_membership_paymode', $paymode );
						update_user_meta( $member_id, 'wcfm_membership_order', $order_id );
						
						// Fetch child Subscriptions
						$args = array(
							'post_parent'    => $order_id,
							'post_type'      => 'shop_subscription', 
							'posts_per_page' => -1,
							'offset'         => 0,
							'post_status'    => 'any' 
						);
						$shop_subscriptions = get_posts( $args );
						if( !empty( $shop_subscriptions ) ) {
							foreach( $shop_subscriptions as $shop_subscription ) {
								update_user_meta( $member_id, 'wcfm_membership_subscription', $shop_subscription->ID );
							}
						}
						
						
						$required_approval = get_post_meta( $wcfm_membership, 'required_approval', true ) ? get_post_meta( $wcfm_membership, 'required_approval', true ) : 'no';
						
						if( $is_renewal || ($required_approval != 'yes') ) {
							update_user_meta( $member_id, 'temp_wcfm_membership', $wcfm_membership );
							$has_error = $WCFMvm->register_vendor( $member_id );
							if( !$has_error ) $WCFMvm->store_subscription_data( $member_id, $paymode, '', $paymode.'_subscription', 'Completed', '' );
						} else {
							$WCFMvm->send_approval_reminder_admin( $member_id );
						}
					}
					break;
				}
			}
		}
		update_post_meta( $order_id, '_wcfm_membership_order_processed', 'yes' );
	}
	
	/**
	 * WCFM Membrship update on WC Subscription Status update
	 */
	function wcfmvm_membership_update_on_subscription_status_changed( $subscription_id, $status_to, $status_from, $subscription ) {
		global $WCFM, $WCFMvm, $wpdb;
		
		$membership_cancel_subscription_status = apply_filters( 'wcfm_membership_cancel_subscription_status', array( 'cancelled', 'pending-cancel', 'expired', 'failed' ) );
		$membership_active_subscription_status = apply_filters( 'wcfm_membership_active_subscription_status', array( 'completed', 'active' ) );
		
		$wcfm_subcription_products = get_option( 'wcfm_subcription_products', array() );
		
		$order_id = $subscription->get_parent();
		
		if( $order_id && !empty( $wcfm_subcription_products ) ) {
			$order         = new WC_Order( $order_id );
			$line_items    = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
			foreach ( $line_items as $item_id => $item ) {
				if( in_array( $item->get_product_id(), $wcfm_subcription_products ) ) {
					$member_id       = absint( $order->get_user_id() );
					$member_user     = new WP_User( absint( $member_id ) );
					$shop_name       = get_user_meta( $member_id, 'store_name', true );
					$paymode         = $order->get_payment_method();
					
					// Fetching Membership Membership Plan
					$is_renewal = false;
					$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
					if( !$wcfm_membership ) {
						$is_renewal = true;
						$wcfm_membership = get_user_meta( $member_id, 'wcfm_membership', true );
					}
					if( !$wcfm_membership ) {
						$is_renewal = true;
						$wcfm_membership = get_user_meta( $member_id, 'expired_wcfm_membership', true );
					}
					
					if( $wcfm_membership ) {
					
						// Activate Membership on Subscription Status Change
						
						if( in_array( $status_to, $membership_active_subscription_status ) ) {
							update_user_meta( $member_id, 'wcfm_membership_paymode', $paymode );
							update_user_meta( $member_id, 'wcfm_membership_order', $order_id );
							update_user_meta( $member_id, 'wcfm_membership_subscription', $subscription_id );
							
							if( apply_filters( 'wcfm_membership_active_on_subscription_active', false ) ) {
								$required_approval = get_post_meta( $wcfm_membership, 'required_approval', true ) ? get_post_meta( $wcfm_membership, 'required_approval', true ) : 'no';
								
								if( $is_renewal || ($required_approval != 'yes') ) {
									update_user_meta( $member_id, 'temp_wcfm_membership', $wcfm_membership );
									$has_error = $WCFMvm->register_vendor( $member_id );
									if( !$has_error ) $WCFMvm->store_subscription_data( $member_id, $paymode, '', $paymode.'_subscription', 'Completed', '' );
								} else {
									$WCFMvm->send_approval_reminder_admin( $member_id );
								}
							}
						}
						
						// Cancel Membership on Susbcription Status Change
						if( apply_filters( 'wcfm_membership_cancel_on_subscription_cancel', true ) ) {
							if( in_array( $status_to, $membership_cancel_subscription_status ) ) {
								$this->wcfmvm_vendor_membership_cancel( $member_id, $wcfm_membership );
								$this->store_subscription_data( $member_id, $paymode, '', $paymode.'_subscription_cancel', 'Cancelled', __(  'WC Subscription Cancellation', 'wc-multivendor-membership' ) );
							}
						}
						
						break;
					}
				}
			}
		}
	}
	
	/**
	 * WCFM Membership Next Schedule Date update on Subscription Next Payment Date Update
	 */
	function wcfmvm_next_schedule_update_on_subscription_date_updated( $subscription, $date_type, $datetime ) {
		global $WCFM, $WCFMvm, $wpdb;
		
		if( $date_type == 'next_payment' ) {
			$wcfm_subcription_products = get_option( 'wcfm_subcription_products', array() );
			$order_id = $subscription->get_parent();
			
			if( $order_id && !empty( $wcfm_subcription_products ) ) {
				$order         = new WC_Order( $order_id );
				$line_items    = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
				foreach ( $line_items as $item_id => $item ) {
					if( in_array( $item->get_product_id(), $wcfm_subcription_products ) ) {
						$member_id       = absint( $order->get_user_id() );
						$wcfm_membership = get_user_meta( $member_id, 'wcfm_membership', true );
						
						if( $wcfm_membership ) {
							update_user_meta( $member_id, 'wcfm_membership_next_schedule', wcs_date_to_time( $datetime ) );
							
							wcfm_log( "Next payment date updated:: " . $member_id . " => " . date_i18n( wc_date_format(), wcs_date_to_time( $datetime ) ) ); // Keep Log
						}
					}
				}
			}
		}
	}
	
	function send_approval_reminder_admin( $member_id ) {
		global $WCFM, $WCFMvm, $wpdb;
		
		if( !$member_id ) return;
		
		$member_id       = absint( $member_id );
		$member_user     = new WP_User( absint( $member_id ) );
		$shop_name       = get_user_meta( $member_id, 'store_name', true );
		$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
		
		if( !$wcfm_membership ) return;
		
		if( apply_filters( 'wcfm_is_allow_notification_email', true, 'vendor_approval' ) ) {
			if( !defined( 'DOING_WCFM_EMAIL' ) ) 
				define( 'DOING_WCFM_EMAIL', true );
			
			// Vendor Approval Admin Email Notification
			$onapproval_admin_notication_subject = get_option( 'wcfm_membership_onapproval_admin_notication_subject', '[{site_name}] A vendor application waiting for approval' );
			$onapproval_admin_notication_content = get_option( 'wcfm_membership_onapproval_admin_notication_content', '' );
			if( !$onapproval_admin_notication_content ) {
				if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
					$onapproval_admin_notication_content = "Dear Admin,
																									<br /><br />
																									A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) has just applied to our membership plan <b>{membership_plan}</b>. 
																									<br /><br />
																									Kindly follow the below the link to approve/reject the application.
																									<br /><br />
																									Application: {notification_url} 
																									<br /><br />
																									Thank You";
				} else {
					$onapproval_admin_notication_content = "Dear Admin,
																									<br /><br />
																									A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) has just applied.
																									<br /><br />
																									Kindly follow the below the link to approve/reject the application.
																									<br /><br />
																									Application: {notification_url} 
																									<br /><br />
																									Thank You";
				}
			}
															 
			$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $onapproval_admin_notication_subject );
			$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
			$message = str_replace( '{dashboard_url}', get_wcfm_url(), $onapproval_admin_notication_content );
			$message = str_replace( '{vendor_name}', $member_user->first_name, $message );
			$message = str_replace( '{vendor_store}', $shop_name, $message );
			if( ( $wcfm_membership == -1 ) || ( $wcfm_membership == '-1' ) ) {
				$message = str_replace( '{membership_plan}', __( 'No Membership', 'wc-multivendor-membership' ), $message );
			} else {
				$message = str_replace( '{membership_plan}', get_the_title( $wcfm_membership ), $message );
			}
			$message = str_replace( '{notification_url}', '<a href="' . get_wcfm_messages_url() . '">' . __( 'Check Here ...', 'wc-multivendor-membership' ) . '</a>', $message );
			$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Vendor Approval', 'wc-multivendor-membership' ) );
			
			wp_mail( apply_filters( 'wcfm_admin_email_notification_receiver', get_bloginfo( 'admin_email' ), 'vendor_approval' ), $subject, $message ); 
		}
		
		// Vendor Approval Admin Desktop Notification
		if( apply_filters( 'wcfm_is_allow_notification_message', true, 'vendor_approval' ) ) {
			$author_id = $member_id;
			$author_is_admin = 0;
			$author_is_vendor = 1;
			$message_to = 0;
			$is_notice = 0;
			$is_direct_message = 1;
			$wcfm_messages_type = 'vendor_approval';
			if( ( $wcfm_membership == -1 ) || ( $wcfm_membership == '-1' ) ) {
				$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) vendor application waiting for approval.', 'wc-multivendor-membership' ), $member_user->first_name, $shop_name );
			} else {
				$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) subscription for <b>%s</b> plan waiting for approval.', 'wc-multivendor-membership' ), $member_user->first_name, $shop_name, get_the_title( $wcfm_membership ) );
			}
			$wcfm_messages = esc_sql( $wcfm_messages );
			$wcfm_create_message     = "INSERT into {$wpdb->prefix}wcfm_messages 
															(`message`, `author_id`, `author_is_admin`, `author_is_vendor`, `is_notice`, `is_direct_message`, `message_to`, `message_type`)
															VALUES
															('{$wcfm_messages}', {$author_id}, {$author_is_admin}, {$author_is_vendor}, {$is_notice}, {$is_direct_message}, {$message_to}, '{$wcfm_messages_type}')";
												
			$wpdb->query($wcfm_create_message);
		}
		
		update_user_meta( $member_id, 'wcfm_subscription_status', 'pending' );
		update_user_meta( $member_id, 'wcfm_membership_application_status', 'pending' );
		
		do_action( 'wcfm_approval_reminder_admin_after', $member_id, $wcfm_membership );
	}
	
	/**
	 * WCFMvm Vendor Membership Cancel
	 */
	function wcfmvm_vendor_membership_cancel( $member_id, $wcfm_membership_id = 0 ) {
		global $WCFM, $WCFMvm, $wpdb;
		if( $member_id ) {
			if( !$wcfm_membership_id ) $wcfm_membership_id = get_user_meta( $member_id, 'wcfm_membership', true );
			if( $wcfm_membership_id && wcfm_is_valid_membership( $wcfm_membership_id ) ) {
				$member_user = new WP_User( $member_id );
				$shop_name = get_user_meta( $member_id, 'store_name', true );
				$paymode = get_user_meta( $member_id, 'wcfm_membership_paymode', true );
				
				do_action( 'wcfmvm_before_membership_cancel', $member_id, $wcfm_membership_id );
				
				$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
				
				$membership_cancel_rules = array();
				if( isset( $wcfm_membership_options['membership_cancel_rules'] ) ) $membership_cancel_rules = $wcfm_membership_options['membership_cancel_rules'];
				$cancel_member_status = isset( $membership_cancel_rules['member_status'] ) ? $membership_cancel_rules['member_status'] : 'basic';
				$cancel_member_product_status = isset( $membership_cancel_rules['product_status'] ) ? $membership_cancel_rules['product_status'] : 'same';
				
				$membership_type_settings = array();
				if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
				$basic_membership = isset( $membership_type_settings['free_membership'] ) ? $membership_type_settings['free_membership'] : '';
				
				// Membership Cancel Vendor Email Notification
				if( !defined( 'DOING_WCFM_EMAIL' ) ) 
				  define( 'DOING_WCFM_EMAIL', true );
				
				$cancel_notication_subject = wcfm_get_option( 'wcfm_membership_cancel_notication_subject', '{site_name}: Membership Subscription Cancelled' );
				$cancel_notication_content = wcfm_get_option( 'wcfm_membership_cancel_notication_content', '' );
				if( !$cancel_notication_content ) {
					$cancel_notication_content = "Hi {vendor_name},
																				<br /><br />
																				Your membership plan (<strong>{membership_plan}</strong>) has been cancelled. 
																				<br /><br />
																				Thank You";
				}
				
				$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $cancel_notication_subject );
				$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
				$message = str_replace( '{dashboard_url}', get_wcfm_url(), $cancel_notication_content );
				$message = str_replace( '{vendor_name}', $member_user->first_name, $message );
				$message = str_replace( '{membership_plan}', get_the_title( $wcfm_membership_id ), $message );
				$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Membership Subscription Cancel', 'wc-multivendor-membership' ) );
	
				wp_mail( $member_user->user_email, $subject, $message ); 
				
				// Membership Cancel Admin Desktop Notification
				if( apply_filters( 'wcfm_is_allow_admin_membership_cancel_notification', true ) ) {
					$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) membership plan <b>%s</b> has been cancelled.', 'wc-multivendor-membership' ), $member_user->first_name, $shop_name, get_the_title( $wcfm_membership_id ) );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'membership-cancel', false );
				}
				
				// Vendor Notification
				if( apply_filters( 'wcfm_is_allow_vendor_membership_cancel_notification', true ) ) {
					$wcfm_messages = sprintf( __( 'Your membership plan <b>%s</b> has been cancelled.', 'wc-multivendor-membership' ), get_the_title( $wcfm_membership_id ) );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'membership-cancel', false );
				}
				
				// Recurring Subscription Profile Cacellation Trigger
				$this->wcfmvm_recurring_subscription_profile_cancel( $member_id, $paymode );
				
				// WC Subscription Cancel
				$wc_subscription = get_user_meta( $member_id, 'wcfm_membership_subscription', true );
				if( $wc_subscription ) {
					$subscription = wcs_get_subscription( $wc_subscription );
					if( $subscription ) {
						$subscription->update_status( 'expired' );
					}
					delete_user_meta( $member_id, 'wcfm_membership_order' );
					delete_user_meta( $member_id, 'wcfm_membership_subscription' );
				}
				
				if( ( $cancel_member_status == 'basic' ) && $basic_membership ) {
					update_user_meta( $member_id, 'temp_wcfm_membership', $basic_membership );
					// Subscribe user to default membership
					$WCFMvm->register_vendor( $member_id );
					$WCFMvm->store_subscription_data( $member_id, 'free', '', 'free_subscription', 'Completed', '' );
				} else {
					if( ( function_exists( 'wcfm_is_affiliate' ) && wcfm_is_affiliate( $member_id ) ) || apply_filters( 'wcfm_is_allow_merge_vendor_role', false ) ) {
						$member_user->remove_role('wcfm_vendor');
						$member_user->add_role('disable_vendor');
					} else {
						$member_user->set_role('disable_vendor');
					}
					
					update_user_meta( $member_id, '_disable_vendor', true );
					update_user_meta( $member_id, 'expired_wcfm_membership', $wcfm_membership_id );
					
					// Delete Membership Data
					do_action( 'wcfm_membership_data_reset', $member_id );
					
					// Membership Disable Admin Desktop Notification
					if( apply_filters( 'wcfm_is_allow_admin_membership_cancel_notification', true ) ) {
						$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) has been disabled.', 'wc-frontend-manager' ), $member_user->first_name, $shop_name );
						$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'vendor-disable', false );
					}
					
					// Vendor Notification
					if( apply_filters( 'wcfm_is_allow_vendor_membership_cancel_notification', true ) ) {
						$wcfm_messages = sprintf( __( 'Your Store: <b>%s</b> has been disabled.', 'wc-frontend-manager' ), $shop_name );
						$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'vendor-disable', false );
					}
				}
				
				
				// Set vendor's product status as per rule
				if( $cancel_member_product_status != 'same' ) {
					$WCFM->wcfm_vendor_support->reset_vendor_product_status( $member_id, $cancel_member_product_status );
				}
				
				do_action( 'wcfmvm_after_membership_cancel', $member_id, $wcfm_membership_id );
			}
		}
	}
	
	/**
	 * WCFMvm Vendor Membership Expire
	 */
	function wcfmvm_vendor_membership_expire( $member_id, $wcfm_membership_id = 0 ) {
		global $WCFM, $WCFMvm, $wpdb;
		if( $member_id ) {
			if( !$wcfm_membership_id ) $wcfm_membership_id = get_user_meta( $member_id, 'wcfm_membership', true );
			if( $wcfm_membership_id && wcfm_is_valid_membership( $wcfm_membership_id ) ) {
				$member_user = new WP_User( $member_id );
				$shop_name = get_user_meta( $member_id, 'store_name', true );
				$paymode = get_user_meta( $member_id, 'wcfm_membership_paymode', true );
				
				do_action( 'wcfmvm_before_membership_expire', $member_id, $wcfm_membership_id );
				
				$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
				
				$membership_expire_rules = array();
				if( isset( $wcfm_membership_options['membership_expire_rules'] ) ) $membership_expire_rules = $wcfm_membership_options['membership_expire_rules'];
				$expire_member_status = isset( $membership_expire_rules['member_status'] ) ? $membership_expire_rules['member_status'] : 'disable';
				$expire_product_status = isset( $membership_expire_rules['product_status'] ) ? $membership_expire_rules['product_status'] : 'archived';
				
				$membership_type_settings = array();
				if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
				$basic_membership = isset( $membership_type_settings['free_membership'] ) ? $membership_type_settings['free_membership'] : '';
				$basic_membership = apply_filters( 'wcfm_membership_expire_basic_membership', $basic_membership, $member_id );
				
				
				// Membership Expired Vendor Email Notification
				if( !defined( 'DOING_WCFM_EMAIL' ) ) 
					define( 'DOING_WCFM_EMAIL', true );
				
				$expire_notication_subject = wcfm_get_option( 'wcfm_membership_expire_notication_subject', '[{site_name}] Membership Subscription Expired' );
				$expire_notication_content = wcfm_get_option( 'wcfm_membership_expire_notication_content', '' );
				if( !$expire_notication_content ) {
					$expire_notication_content = "Hi {vendor_name},
																				<br /><br />
																				Your membership plan (<strong>{membership_plan}</strong>) has been expired. 
																				<br /><br />
																				Thank You";
				}
				
				$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $expire_notication_subject );
				$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
				$message = str_replace( '{dashboard_url}', get_wcfm_url(), $expire_notication_content );
				$message = str_replace( '{vendor_name}', $member_user->first_name, $message );
				$message = str_replace( '{membership_plan}', get_the_title( $wcfm_membership_id ), $message );
				$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Membership Subscription Expired', 'wc-multivendor-membership' ) );
	
				wp_mail( $member_user->user_email, $subject, $message ); 
				
				// Membership Expired Admin Desktop Notification
				if( apply_filters( 'wcfm_is_allow_admin_membership_expiry_notification', true ) ) {
					$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) membership plan <b>%s</b> has been expired.', 'wc-multivendor-membership' ), $member_user->first_name, $shop_name, get_the_title( $wcfm_membership_id ) );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'membership-expired', false );
				}
				
				// Membership Expired Vendor Desktop Notification
				if( apply_filters( 'wcfm_is_allow_vendor_membership_expiry_notification', true ) ) {
					$wcfm_messages = sprintf( __( 'Your membership plan <b>%s</b> has been expired.', 'wc-multivendor-membership' ), get_the_title( $wcfm_membership_id ) );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'membership-expired', false );
				}
				
				// Recurring Subscription Profile Cacellation Trigger
				$this->wcfmvm_recurring_subscription_profile_cancel( $member_id, $paymode );
				
				// WC Subscription Cancel
				$wc_subscription = get_user_meta( $member_id, 'wcfm_membership_subscription', true );
				if( $wc_subscription ) {
					$subscription = wcs_get_subscription( $wc_subscription );
					$subscription->update_status( 'expired' );
					delete_user_meta( $member_id, 'wcfm_membership_order' );
					delete_user_meta( $member_id, 'wcfm_membership_subscription' );
				}
				
				if( ( $expire_member_status == 'basic' ) && $basic_membership ) {
					update_user_meta( $member_id, 'temp_wcfm_membership', $basic_membership );
					// Subscribe user to default membership
					$WCFMvm->register_vendor( $member_id );
					$WCFMvm->store_subscription_data( $member_id, 'free', '', 'free_subscription', 'Completed', '' );
				} else {
					if( ( function_exists( 'wcfm_is_affiliate' ) && wcfm_is_affiliate( $member_id ) ) || apply_filters( 'wcfm_is_allow_merge_vendor_role', false ) ) {
						$member_user->remove_role('wcfm_vendor');
						$member_user->add_role('disable_vendor');
					} else {
						$member_user->set_role('disable_vendor');
					}
					
					update_user_meta( $member_id, '_disable_vendor', true );
					update_user_meta( $member_id, 'expired_wcfm_membership', $wcfm_membership_id );
					
					// Delete Membership Data
					do_action( 'wcfm_membership_data_reset', $member_id );
					
					// Membership Disable Admin Desktop Notification
					if( apply_filters( 'wcfm_is_allow_admin_membership_expiry_notification', true ) ) {
						$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) has been disabled.', 'wc-frontend-manager' ), $member_user->first_name, $shop_name );
						$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'vendor-disable', false );
					}
					
					// Vendor Notification
					if( apply_filters( 'wcfm_is_allow_vendor_membership_expiry_notification', true ) ) {
						$wcfm_messages = sprintf( __( 'Your Store: <b>%s</b> has been disabled.', 'wc-frontend-manager' ), $shop_name );
						$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'vendor-disable', false );
					}
				}
				
				
				// Set vendor's product status as per rule
				if( $expire_product_status != 'same' ) {
					$WCFM->wcfm_vendor_support->reset_vendor_product_status( $member_id, $expire_product_status );
				}
				
				do_action( 'wcfmvm_after_membership_expire', $member_id, $wcfm_membership_id );
			}
		}
	}
	
	/**
	 * WCFMvm subscription recurring profile cancdel 
	 */
	function wcfmvm_recurring_subscription_profile_cancel( $member_id, $paymode ) {
		global $WCFM, $WCFMvm;
		
		if( !$paymode ) return;
		
		$subscription_id = get_user_meta( $member_id, 'wcfm_transaction_id', true );
		if( !$subscription_id ) return;
		
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
		$membership_payment_settings = array();
		if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
		$payment_sandbox = isset( $membership_payment_settings['paypal_sandbox'] ) ? 'yes' : 'no';
		
		
		
		switch( $paymode ) {
			case 'paypal_subs':
				$paypal_settings  = get_option( 'woocommerce_paypal_settings' );
				$api_endpoint     = ( $paypal_settings['testmode'] == 'no' ) ? 'https://api-3t.paypal.com/nvp' : 'https://api-3t.sandbox.paypal.com/nvp';
        $api_username     = ( isset( $paypal_settings['api_username'] ) ) ? $paypal_settings['api_username'] : '';
        $api_password     = ( isset( $paypal_settings['api_password'] ) ) ? $paypal_settings['api_password'] : '';
        $api_signature    = ( isset( $paypal_settings['api_signature'] ) ) ? $paypal_settings['api_signature'] : '';

        if( $api_username && $api_password && $api_signature ) {
					$request = wp_remote_post( $api_endpoint, array(
							'timeout'   => 15,
							'sslverify' => false,
							'body'      => array(
									'USER'      => $api_username,
									'PWD'       => $api_password,
									'SIGNATURE' => $api_signature,
									'VERSION'   => '76.0',
									'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
									'PROFILEID' => $subscription_id,
									'ACTION'    => 'Cancel',
									'NOTE'      => sprintf( __( 'Subscription cancelled at %s', 'wc-multivendor-membership' ), get_bloginfo( 'name' ) )
									)
							) );
	
					if ( is_wp_error( $request ) || $request['response']['code'] != 200 ) {
						wcfm_log( 'PayPal Recurring Subscription Cancel - HTTP error' );
					}
	
					$response = wp_remote_retrieve_body( $request );
					parse_str( $response, $parsed_response );
	
					if ( isset( $parsed_response['ACK'] ) && $parsed_response['ACK'] == 'Failure' ) {
						wcfm_log( "PayPal Recurring Subscription Cancel Error:: " . $parsed_response['L_LONGMESSAGE0'] );
					}
	
					if ( isset( $parsed_response['ACK'] ) && $parsed_response['ACK'] == 'Success' ) {
						delete_user_meta( $member_id, 'wcfm_paypal_subscription_id' );
					
						wcfm_log( "PayPal Recurring Subscription Cancelled:: " . $member_id . ' => ' . $subscription_id );
					}
				}
			break;
		
			case 'stripe_subs':
				//Include the Stripe library.
				if( !class_exists( 'Stripe\Stripe' ) ) {
					include( $WCFMvm->plugin_path . 'includes/libs/stripe-gateway/init.php');
				}
				
				$stripe_secret_key_live = isset( $membership_payment_settings['stripe_secret_key_live'] ) ? $membership_payment_settings['stripe_secret_key_live'] : '';
				$stripe_secret_key_test = isset( $membership_payment_settings['stripe_secret_key_test'] ) ? $membership_payment_settings['stripe_secret_key_test'] : '';
				if ($payment_sandbox == 'yes') {
					$secret_key = $stripe_secret_key_test;
				} else {
					$secret_key = $stripe_secret_key_live;
				}
				
				if( $secret_key ) {
					\Stripe\Stripe::setApiKey( $secret_key );
					$recurring_subscription = \Stripe\Subscription::retrieve( $subscription_id );
					$recurring_subscription->cancel();
					
					delete_user_meta( $member_id, 'wcfm_stripe_subscription_id' );
					
					wcfm_log( "Stripe Recurring Subscription Cancelled:: " . $member_id . ' => ' . $subscription_id );
				}
				
			break;
		}
		
		delete_user_meta( $member_id, 'wcfm_transaction_id' );
	}
	
	/**
	 * WCFMvm scheduler check for sending recurring reminder email
	 */
	function wcfmvm_membership_scheduler_check() {
		global $WCFM, $WCFMvm, $wpdb;
		
		// Update Schedule Execution TIme
		$data = get_option( 'wcfmvm_membership_scheduler', array() );
		$data['updated'] = time();
		update_option( 'wcfmvm_membership_scheduler', $data, false );
		
		
		$args = array(
						'role__in'     => apply_filters( 'wcfm_allwoed_user_roles', array( 'dc_vendor', 'vendor', 'seller', 'wcfm_vendor', 'wc_product_vendors_admin_vendor' ) ),
					 ); 
		$members = get_users( $args );

    foreach ( $members as $member ) {
    	$wcfm_membership = get_user_meta( $member->ID, 'wcfm_membership', true );
    	if( $wcfm_membership && wcfm_is_valid_membership( $wcfm_membership ) ) {
				$subscription = (array) get_post_meta( $wcfm_membership, 'subscription', true );
				$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
				$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
				$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
				$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
				$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : 999;
				if( !$billing_period_count ) $billing_period_count = 999;
				$free_expiry_period = isset( $subscription['free_expiry_period'] ) ? $subscription['free_expiry_period'] : '';
				$free_expiry_period_type = isset( $subscription['free_expiry_period_type'] ) ? $subscription['free_expiry_period_type'] : 'M';
				$period_options = array( 'D' => 'days', 'M' => 'months', 'Y' => 'years' );
				
				$free_expiry_reminder = false; 
				if( $free_expiry_period ) {
					if( ( $is_free != 'no' ) || ( $subscription_type == 'one_time' ) ) {
						$billing_period = $free_expiry_period;
						$billing_period_type = $free_expiry_period_type;
						$billing_period_count = 1;
						$free_expiry_reminder = true; 
					}
				}
				
				if( $free_expiry_reminder || ( ( $is_free == 'no' ) && ( $subscription_type != 'one_time' ) ) ) {
					$paymode = get_user_meta( $member->ID, 'wcfm_membership_paymode', true );
					$next_schedule = get_user_meta( $member->ID, 'wcfm_membership_next_schedule', true );
					$current_time = strtotime( 'midnight', current_time( 'timestamp' ) );
					
					// Bug fixing update
					update_user_meta( $member->ID, 'wcfm_membership_billing_period', $billing_period_count );
		
					if( $next_schedule ) {
						$send_reminder = false;
						$renewal_reminder = false;
						
						$member_billing_period = get_user_meta( $member->ID, 'wcfm_membership_billing_period', true );
						$member_billing_cycle = get_user_meta( $member->ID, 'wcfm_membership_billing_cycle', true );
						if( $member_billing_period ) $member_billing_period = absint( $member_billing_period );
						else $member_billing_period = absint( $billing_period_count );
						if( !$member_billing_cycle ) $member_billing_cycle = 1;
						if( $member_billing_cycle == $member_billing_period ) $renewal_reminder = true;
						
						$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
						
						if( !$renewal_reminder ) {
							$membership_next_payment = array();
							if( isset( $wcfm_membership_options['membership_next_payment'] ) ) $membership_next_payment = $wcfm_membership_options['membership_next_payment'];
							$first_remind = isset( $membership_next_payment['first_next_payment'] ) ? $membership_next_payment['first_next_payment'] : '5';
							$second_remind = isset( $membership_next_payment['second_next_payment'] ) ? $membership_next_payment['second_next_payment'] : '2';
							$reminder_notication_subject = wcfm_get_option( 'wcfm_membership_next_payment_notication_subject', '[{site_name}] Membership Subscription Recurring Next Payment' );
							$reminder_notication_content = wcfm_get_option( 'wcfm_membership_next_payment_notication_content', '' );
							if( !$reminder_notication_content ) {
								$reminder_notication_content = "Hi {vendor_name},
																										<br /><br />
																										Your membership plan (<strong>{membership_plan}</strong>) subscription next billing date <strong>{next_payment_day}</strong>. 
																										<br /><br />
																										Kindly pay now now to keep your account active.
																										<br /><br />
																										Thank You";
							}
						} else {
							$membership_reminder = array();
							if( isset( $wcfm_membership_options['membership_reminder'] ) ) $membership_reminder = $wcfm_membership_options['membership_reminder'];
							$first_remind = isset( $membership_reminder['first_remind'] ) ? $membership_reminder['first_remind'] : '5';
							$second_remind = isset( $membership_reminder['second_remind'] ) ? $membership_reminder['second_remind'] : '2';
							$reminder_notication_subject = wcfm_get_option( 'wcfm_membership_reminder_notication_subject', '[{site_name}] Membership Subscription Renewal Reminder' );
							$reminder_notication_content = wcfm_get_option( 'wcfm_membership_reminder_notication_content', '' );
							if( !$reminder_notication_content ) {
								$reminder_notication_content = "Dear {vendor_name},
																							<br /><br />
																							Your membership plan (<strong>{membership_plan}</strong>) will expire <strong>{reminder_day}</strong>. 
																							<br /><br />
																							Kindly renew now to keep your account active.
																							<br /><br />
																							Thank You";
							}
						}
						
						$date = date( 'Y-m-d', $current_time );
						$renewal_date = date( 'Y-m-d', $next_schedule );
						$datetime1 = new DateTime( $date );
						$datetime2 = new DateTime( $renewal_date );
						$interval = $datetime1->diff( $datetime2 );
						$interval = $interval->format( '%r%a' );
						
						$reminder_day = '';
						if( ( $first_remind != 'never' ) && ( (int) $interval > 0 ) && ( (int) $interval == (int) $first_remind ) ) { // First Reminder
							$send_reminder = true;
							$reminder_day = __( 'in', 'wc-multivendor-membership' ) . ' ' . $first_remind . ' ' . __( 'Days', 'wc-multivendor-membership' );
						} elseif( ( $second_remind != 'never' ) && ( (int) $interval > 0 ) && ( (int) $interval == (int) $second_remind ) ) { // Second Reminder
							$send_reminder = true;
							$reminder_day = __( 'in', 'wc-multivendor-membership' ) . ' ' . $second_remind . ' ' . __( 'Days', 'wc-multivendor-membership' );
						} elseif( (int) $interval <= 0 ) { // Expiry Day Remider
							if( $member_billing_cycle < $member_billing_period ) {
								$send_reminder = true;
								$reminder_day = __( 'Today', 'wc-multivendor-membership' );
							
								$member_billing_cycle++;
								update_user_meta( $member->ID, 'wcfm_membership_billing_cycle', $member_billing_cycle );
								
								// Set new next schedule
								$next_renewal_time = strtotime( '+' . $billing_period . ' ' . $period_options[$billing_period_type], $current_time );
								update_user_meta( $member->ID, 'wcfm_membership_next_schedule', $next_renewal_time );
							} else {
								wcfm_log( "Membership Expiry by Time :: " . $member->ID . " <=> " . $interval . " <=> " . $member_billing_cycle . " <=> " . $member_billing_period );
								$WCFMvm->wcfmvm_vendor_membership_expire( $member->ID, $wcfm_membership );
							}
						}
						
						if( $send_reminder ) {
							if( !defined( 'DOING_WCFM_EMAIL' ) ) 
							  define( 'DOING_WCFM_EMAIL', true );
							
							$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $reminder_notication_subject );
							$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
							$message = str_replace( '{dashboard_url}', get_wcfm_url(), $reminder_notication_content );
							$message = str_replace( '{vendor_name}', $member->first_name, $message );
							$message = str_replace( '{membership_plan}', get_the_title( $wcfm_membership ), $message );
							$message = str_replace( '{reminder_day}', $reminder_day, $message );
							$message = str_replace( '{next_payment_day}', $reminder_day, $message );
							if( $renewal_reminder ) {
								$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Membership Subscription Renewal', 'wc-multivendor-membership' ) );
							} else {
								$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Subscription Next Payment', 'wc-multivendor-membership' ) );
							}
				
							if( apply_filters( 'wcfm_is_allow_vendor_membership_renewal_notification', true ) ) {
								wp_mail( $member->user_email, $subject, $message ); 
							}
							
							// Admin Desktop Notification
							if( apply_filters( 'wcfm_is_allow_admin_membership_renewal_notification', true ) ) {
								if( $renewal_reminder ) {
									$wcfm_messages = sprintf( __( '<b>%s</b> membership plan (<strong>%s</strong>) will expire <b>%s</b>.', 'wc-multivendor-membership' ), $member->first_name, get_the_title( $wcfm_membership ), $reminder_day );
								} else {
									$wcfm_messages = sprintf( __( '<b>%s</b> membership plan (<strong>%s</strong>) subscription next billing date <b>%s</b>.', 'wc-multivendor-membership' ), $member->first_name, get_the_title( $wcfm_membership ), $reminder_day );
								}
								$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'membership-reminder', false );
							}
							
							// Vendor Desktop Notification
							if( apply_filters( 'wcfm_is_allow_vendor_membership_renewal_notification', true ) ) {
								if( $renewal_reminder ) {
									$wcfm_messages = sprintf( __( 'Your membership plan (<strong>%s</strong>) will expire <b>%s</b>.', 'wc-multivendor-membership' ), get_the_title( $wcfm_membership ), $reminder_day );
								} else {
									$wcfm_messages = sprintf( __( 'Your membership plan (<strong>%s</strong>) subscription next billing date <b>%s</b>.', 'wc-multivendor-membership' ), get_the_title( $wcfm_membership ), $reminder_day );
								}
								$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member->ID, 1, 0, $wcfm_messages, 'membership-reminder', false );
							}
						}
					}
				}
    	}
    }
	}
	
	/**
	 * ON WP delete user clean WCFM membership
	 */
	function wcfmvm_delete_user( $member_id ) {
		global $WCFM, $WCFMvm;
		
		$vendor_old_membership = get_user_meta( $member_id, 'wcfm_membership', true );
		if( $vendor_old_membership && wcfm_is_valid_membership( $vendor_old_membership ) ) {
			// Membership list update
			$old_membership_users = (array) get_post_meta( $vendor_old_membership, 'membership_users', true );
			if( !empty( $old_membership_users ) ) {
				if( ( $key = array_search( $member_id, $old_membership_users ) ) !== false ) {
					unset( $old_membership_users[$key] );
				}
				update_post_meta( $vendor_old_membership, 'membership_users', $old_membership_users );
			}
			
			// Group vendor list update
			$old_associated_groups = (array) get_user_meta( $member_id, '_wcfm_vendor_group', true );
			//$old_associated_group = get_post_meta( $vendor_old_membership, 'associated_group', true );
			if( $old_associated_groups ) {
				foreach( $old_associated_groups as $old_associated_group ) {
					$old_group_vendors = (array) get_post_meta( $old_associated_group, '_group_vendors', true );
					if( !empty( $old_group_vendors ) ) {
						if( ( $key = array_search( $member_id, $old_group_vendors ) ) !== false ) {
							unset( $old_group_vendors[$key] );
						}
						update_post_meta( $old_associated_group, '_group_vendors', $old_group_vendors );
					}
				}
			}
		}
		
		// Change vendor's product status if 
		if( apply_filters( 'wcfm_is_allow_disable_vendor_product_draft', true ) ) {
			$WCFM->wcfm_vendor_support->reset_vendor_product_status( $member_id );
		}
		
		$paymode = get_user_meta( $member_id, 'wcfm_membership_paymode', true );
		$WCFMvm->store_subscription_data( $member_id, $paymode, '', 'subscr_cancel', 'Cancelled', 'Manual Cancellation' );
	}
	
	/**
	 * ON disable vendor clean WCFM membership
	 */
	function wcfmvm_disable_vendor( $member_id ) {
		if( apply_filters( 'wcfm_is_allow_remove_membership_data_on_disable', true ) ) {
			$this->wcfmvm_delete_user( $member_id );
			
			$wcfm_membership_id = get_user_meta( $member_id, 'wcfm_membership', true );
			if( $wcfm_membership_id && wcfm_is_valid_membership( $wcfm_membership_id ) ) {
				update_user_meta( $member_id, 'expired_wcfm_membership', $wcfm_membership_id );
			}
			
			delete_user_meta( $member_id, 'wcfm_membership' );
			delete_user_meta( $member_id, '_wcfm_vendor_group' );
			delete_user_meta( $member_id, '_wcfm_vendor_group_list' );
			delete_user_meta( $member_id, 'wcfm_membership_next_schedule' );
			delete_user_meta( $member_id, 'wcfm_membership_billing_period' );
			delete_user_meta( $member_id, 'wcfm_membership_billing_cycle' );
			delete_user_meta( $member_id, 'wcfm_membership_subscribe_on' );
			delete_user_meta( $member_id, 'wcfm_membership_order' );
			delete_user_meta( $member_id, 'wcfm_membership_subscription' );
			delete_user_meta( $member_id, 'temp_wcfm_membership' );
		}
	}
	
	function disable_dokan_account_migration_button() {
		if( class_exists( 'Dokan_Pro' ) ) {
			$Dokan_Pro = dokan_pro();
			remove_action( 'woocommerce_after_my_account', array( $Dokan_Pro, 'dokan_account_migration_button' ) );
		}
	}
}