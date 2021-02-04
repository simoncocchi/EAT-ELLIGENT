<?php

/**
 * WCFM plugin core
 *
 * Plugin intiate
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   1.0.0
 */
 
class WCFM {

	public $plugin_base_name;
	public $plugin_url;
	public $plugin_path;
	public $version;
	public $token;
	public $text_domain;
	public $wcfm_query;
	public $library;
	public $template;
	public $shortcode;
	public $admin;
	public $frontend;
	public $ajax;
	public $non_ajax;
	public $file;
	public $wcfm_emails;
	public $wcfm_fields;
	public $is_marketplace;
	public $wcfm_marketplace;
	public $wcfm_capability;
	public $wcfm_preferences;
	public $wcfm_customer;
	public $wcfm_article;
	public $wcfm_vendor_support;
	public $wcfm_wcbooking;
	public $wcfm_wcsubscriptions;
	public $wcfm_xasubscriptions;
	public $wcfm_integrations;
	public $wcfm_customfield_support;
	public $wcfm_enquiry;
	public $wcfm_catalog;
	public $wcfm_policy;
	public $wcfm_withdrawal;
	public $wcfm_notification;
	public $wcfm_buddypress;
	public $wcfm_product_popup;
	public $wcfm_has_catalog;
	
	public $wcfm_options;

	public function __construct($file) {

		$this->file = $file;
		$this->plugin_base_name = plugin_basename( $file );
		$this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
		$this->plugin_path = trailingslashit(dirname($file));
		$this->token = WCFM_TOKEN;
		$this->text_domain = WCFM_TEXT_DOMAIN;
		$this->version = WCFM_VERSION;
		
		// Updation Hook
		add_action( 'init', array( &$this, 'update_wcfm' ) );
		
		// Cleanup page settings conflict
		add_action( 'init', array( &$this, 'clean_wcfm_page_setting_conflict' ) );

		add_action( 'init', array( &$this, 'init' ), 10 );
		
		add_action( 'woocommerce_loaded', array( $this, 'load_wcfm' ) );
		
		// WC Vendors shop_order_vendor - register post type fix - since 2.0.4
		add_filter( 'woocommerce_register_post_type_shop_order_vendor', array( &$this, 'wcvendors_register_post_type_shop_order_vendor' ) );
		
		// WCFM User Capability Load
		add_filter( 'wcfm_capability_options_rules', array( &$this, 'wcfm_capability_options_rules' ) );
		
		// WCfM Email Subject wrapper
		add_filter( 'wcfm_email_subject_wrapper', array( &$this, 'wcfm_email_subject_wrapper' ), 10 );
		
		// WCfM Email wrapper
		add_filter( 'wcfm_email_content_wrapper', array( &$this, 'wcfm_email_content_wrapper' ), 10, 2 );
		
		// Hide Admin Bar for Vendors
		add_action( 'admin_print_scripts-profile.php', array( &$this, 'wcfm_hide_admin_bar_prefs' ) );
		add_filter( 'show_admin_bar', array( &$this, 'wcfm_show_admin_bar' ) );
		
		// WCfM Formated Menus
 		if( apply_filters( 'wcfm_is_pref_menu_manager', true ) ) {
 			add_filter( 'wcfm_formeted_menus', array(&$this, 'wcfm_formeted_menus' ), 500 );
 		}
		
	}
	
	/**
	 * initilize plugin on WP init
	 */
	function init() {
		global $WCFM;
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		// Load WCFM Setting
		$this->wcfm_options = get_option( 'wcfm_options', array() );
		
		// Load WCFM Dashbaord setup class
		// http://localhost/wcfm/wp-admin/?page=wcfm-setup&step=dashboard
		if ( is_admin() ) {
			$current_page = filter_input( INPUT_GET, 'page' );
			if ( $current_page && $current_page == 'wcfm-setup' ) {
				require_once $this->plugin_path . 'helpers/class-wcfm-setup.php';
			}
		}
		
		// Register Knowledgebase Post Type
		register_post_type( 'wcfm_knowledgebase', array( 'public' => false ) );
		
		// Register Knowledgebase Category
		register_taxonomy(
			'wcfm_knowledgebase_category',
			array( 'wcfm_knowledgebase' ),
			apply_filters(
				'wcfm_knowledgebase_category_taxonomy_args', array(
					'hierarchical'      => true,
					'show_ui'           => false,
					'show_in_nav_menus' => false,
					'query_var'         => is_admin(),
					'rewrite'           => false,
					'public'            => false,
				)
			)
		);
		
		// Register Notice Post Type - 3.0.6
		register_post_type( 'wcfm_notice', array( 'public' => false ) );
		
		// Register Custom Post Status for Products - 6.2.5
		register_post_status('archived', apply_filters(
				'wcfm_product_archive_post_status_args', array(
					'label'                     => _x( 'Archived', 'wc-frontend-manager' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>' )
				)
			) 
		);
		
		// Vendor wp-admin restrict
		if( is_admin() && wcfm_is_vendor() && !defined('DOING_AJAX') ) {
			$this->restrict_wcfm_vendor_backend();
		}
		
		//if (!is_admin() || defined('DOING_AJAX')) {
			$this->load_class( 'preferences' );
			$this->wcfm_preferences = new WCFM_Preferences();
		//}
		
		//if (!is_admin() || defined('DOING_AJAX')) {
			$this->load_class( 'capability' );
			$this->wcfm_capability = new WCFM_Capability();
		//}
		
		$this->load_class( 'vendor-support' );
		$this->wcfm_vendor_support = new WCFM_Vendor_Support();
		
		// template loader
		$this->load_class( 'template' );
		$this->template = new WCFM_Template();
		
		// Check Marketplace
		$this->is_marketplace = wcfm_is_marketplace();
		
		if (!is_admin() || defined('DOING_AJAX') || defined('WCFM_REST_API_CALL') ) {
			if( $this->is_marketplace ) {
				if( wcfm_is_vendor() ) {
					$this->load_class( $this->is_marketplace );
					if( $this->is_marketplace == 'wcvendors' ) $this->wcfm_marketplace = new WCFM_WCVendors();
					elseif( $this->is_marketplace == 'wcmarketplace' ) $this->wcfm_marketplace = new WCFM_WCMarketplace();
					elseif( $this->is_marketplace == 'wcpvendors' ) $this->wcfm_marketplace = new WCFM_WCPVendors();
					elseif( $this->is_marketplace == 'dokan' ) $this->wcfm_marketplace = new WCFM_Dokan();
					elseif( $this->is_marketplace == 'wcfmmarketplace' ) $this->wcfm_marketplace = new WCFM_Marketplace();
				}
			}
		}  
		
		// Load withdrawal module
		if( apply_filters( 'wcfm_is_pref_withdrawal', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				if( $this->is_marketplace && in_array( $this->is_marketplace, array( 'dokan', 'wcmarketplace', 'wcfmmarketplace' ) ) ) {
					$this->load_class( 'withdrawal' );
					$this->wcfm_withdrawal = new WCFM_Withdrawal();
				}
			}
		}
		
		// Check WC Booking
		if( wcfm_is_booking() ) {
			if (!is_admin() || ( defined('DOING_AJAX') || defined('WCFM_REST_API_CALL') ) ) {
				$this->load_class('wcbookings');
				$this->wcfm_wcbooking = new WCFM_WCBookings();
			}
		} else {
			if( get_option( 'wcfm_updated_end_point_wc_bookings' ) ) {
				delete_option( 'wcfm_updated_end_point_wc_bookings' );
			}
		}
		
		// Check WC Subscription
		if( wcfm_is_subscription() ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class('wcsubscriptions');
				$this->wcfm_wcsubscriptions = new WCFM_WCSubscriptions();
			}
		}
		
		// Check XA Subscription
		if( wcfm_is_xa_subscription() ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class('xasubscriptions');
				$this->wcfm_xasubscriptions = new WCFM_XASubscriptions();
			}
		}
		
		// Init library
		$this->load_class( 'library' );
		$this->library = new WCFM_Library();

		if ( is_admin() ) {
			$this->load_class( 'admin' );
			$this->admin = new WCFM_Admin();
		}
		
		if ( defined('DOING_AJAX') || defined('WCFM_REST_API_CALL')  ) {
			$this->load_class( 'ajax' );
			$this->ajax = new WCFM_Ajax();
		}

		if( !defined('DOING_AJAX') ) {
			$this->load_class( 'non-ajax' );
			$this->non_ajax = new WCFM_Non_Ajax();
		}
		
		if ( !is_admin() || defined('DOING_AJAX') ) {
			$this->load_class( 'frontend' );
			$this->frontend = new WCFM_Frontend();
		}
		
		// Load Third Party integration modules
		if ( !is_admin() || defined('DOING_AJAX') ) {
			$this->load_class( 'integrations' );
			$this->wcfm_integrations = new WCFM_Integrations();
		}
		
		// Load Custom Field Module
		if( apply_filters( 'wcfm_is_pref_custom_field', true ) ) {
			if ( !is_admin() || defined('DOING_AJAX') ) {
				$this->load_class( 'customfield-support' );
				$this->wcfm_customfield_support = new WCFM_Custom_Field_Support();
			}
		}
		
		// Load Enquiry Module
		if( apply_filters( 'wcfm_is_pref_enquiry', true ) ) {
			if ( !is_admin() || defined('DOING_AJAX') || defined('WCFM_REST_API_CALL') ) {
				$this->load_class( 'enquiry' );
				$this->wcfm_enquiry = new WCFM_Enquiry();
			}
		}
		
		// Load Catalog Module
		if( apply_filters( 'wcfm_is_pref_enquiry', true ) && apply_filters( 'wcfm_is_pref_catalog', true ) ) {
			if ( !is_admin() || defined('DOING_AJAX') ) {
				$this->load_class( 'catalog' );
				$this->wcfm_catalog = new WCFM_Catalog();
			}
		}
		
		// Load Notification Module
		$this->load_class( 'notification' );
		$this->wcfm_notification = new WCFM_Notification();
		
		// Load Customer Module
		if( apply_filters( 'wcfm_is_pref_customer', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class( 'customer' );
				$this->wcfm_customer = new WCFM_Customer();
			}
		}
		
		// Load Article Module
		if( apply_filters( 'wcfm_is_pref_article', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class( 'article' );
				$this->wcfm_article = new WCFM_Article();
			}
		}
		
		// Load Policies Module
		if( apply_filters( 'wcfm_is_pref_policies', true ) ) {
			//if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class( 'policy' );
				$this->wcfm_policy = new WCFM_Policy();
			//}
		}
		
		// Load BuddyPress Module
		if( apply_filters( 'wcfm_is_pref_buddypress', true ) && WCFM_Dependencies::wcfm_biddypress_plugin_active_check() ) {
			if ( !is_admin() || defined('DOING_AJAX') ) {
				$this->load_class( 'buddypress' );
				$this->wcfm_buddypress = new WCFM_Buddypress();
			}
		}
		
		// Load Product Popup Module
		if( apply_filters( 'wcfm_is_pref_product_popup', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class( 'product-popup' );
				$this->wcfm_product_popup = new WCFM_Product_Popup();
			}
		}
		
		// init shortcode
		$this->load_class( 'shortcode' );
		$this->shortcode = new WCFM_Shortcode();
		
		// WCFM Fields Lib
		$this->wcfm_fields = $this->library->load_wcfm_fields();
		
		do_action( 'wcfm_init' );
	}
	
	/**
	 * Load WCFM 
	 */
	function load_wcfm() {
		
		if( WCFM_Dependencies::woocommerce_plugin_active_check() ) {
			// WCFM Emails Load
			$this->load_class('emails');
			$this->wcfm_emails = new WCFM_Emails();
		}
	}
	
	/**
	 * WCFM Capability Load as per User Role
	 */
	function wcfm_capability_options_rules( $wcfm_capability_options ) {
		$user = wp_get_current_user();
		
		if ( in_array( 'vendor', $user->roles ) ) {
			$wcfm_capability_options['wp_admin_view'] = 'yes';
			$wcfm_capability_options['manage_vendors'] = 'yes';
			$wcfm_capability_options['manage_commission'] = 'yes';
			$wcfm_capability_options['capability_controller'] = 'yes';
		} elseif ( in_array( 'seller', $user->roles ) ) {
			$wcfm_capability_options['wp_admin_view'] = 'yes';
			$wcfm_capability_options['manage_vendors'] = 'yes';
			$wcfm_capability_options['manage_commission'] = 'yes';
			$wcfm_capability_options['capability_controller'] = 'yes';
		} elseif ( in_array( 'dc_vendor', $user->roles ) ) {
			$wcfm_capability_options['wp_admin_view'] = 'yes';
			$wcfm_capability_options['manage_vendors'] = 'yes';
			$wcfm_capability_options['manage_commission'] = 'yes';
			$wcfm_capability_options['capability_controller'] = 'yes';
		} elseif ( in_array( 'wc_product_vendors_admin_vendor', $user->roles ) ) {
			$wcfm_capability_options['wp_admin_view'] = 'yes';
			$wcfm_capability_options['manage_vendors'] = 'yes';
			$wcfm_capability_options['manage_commission'] = 'yes';
			$wcfm_capability_options['capability_controller'] = 'yes';
		} elseif ( in_array( 'wc_product_vendors_manager_vendor', $user->roles ) ) {
			$wcfm_capability_options['wp_admin_view'] = 'yes';
			$wcfm_capability_options['manage_vendors'] = 'yes';
			$wcfm_capability_options['manage_commission'] = 'yes';
			$wcfm_capability_options['capability_controller'] = 'yes';
		} elseif ( in_array( 'wcfm_vendor', $user->roles ) ) {
			$wcfm_capability_options['wp_admin_view'] = 'yes';
			$wcfm_capability_options['manage_vendors'] = 'yes';
			$wcfm_capability_options['manage_commission'] = 'yes';
			$wcfm_capability_options['capability_controller'] = 'yes';
		} else {
			$wcfm_capability_options = array();
		}
		
		return $wcfm_capability_options;
	}
	
	/**
	 * WCFM email subject wrapper
	 */
	function wcfm_email_subject_wrapper( $subject ) {
		$subject = html_entity_decode($subject);
		return $subject;
	}
	
	/**
	 * WCFM email content wrapper
	 */
	function wcfm_email_content_wrapper( $content_body, $email_heading ) {
		global $WCFM;
		
		ob_start();
		wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
		$content_body_head = ob_get_clean();
		ob_start();
		wc_get_template( 'emails/email-footer.php' );
		$content_body_foot = ob_get_clean();
		
		include_once( dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php' );
		// include css inliner
		//if ( ! class_exists( 'Emogrifier' ) && class_exists( 'DOMDocument' ) ) {
			//include_once( dirname( WC_PLUGIN_FILE ) . '/includes/libraries/class-emogrifier.php' );
		//}
		$wcemail  = new WC_Email();
		$content_body  = $content_body_head . $content_body . $content_body_foot;
		$content_body  = str_replace( '{site_title}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $content_body );
		$content_body  = apply_filters( 'woocommerce_mail_content', $wcemail->style_inline( $content_body ) );
		
		return $content_body;
	}
	
	/**
	 * Hide WP admin bar for vendors
	 */
	function wcfm_hide_admin_bar_prefs() {
		if( wcfm_is_vendor() ) {
			?>
			<style type="text/css">
				.show-admin-bar {display: none;}
			</style>
			<?php
	  }
	}
	
	/**
	 * Hide WP admin bar for vendors
	 */
	function wcfm_show_admin_bar( $allow ) {
		if( wcfm_is_vendor() ) {
			$allow = false;
		}
		return $allow;
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
		$locale = apply_filters( 'plugin_locale', $locale, 'wc-frontend-manager' );
		
		//load_plugin_textdomain( 'wc-frontend-manager' );
		//load_textdomain( 'wc-frontend-manager', WP_LANG_DIR . "/wc-frontend-manager/wc-frontend-manager-$locale.mo");
		load_textdomain( 'wc-frontend-manager', $this->plugin_path . "lang/wc-frontend-manager-$locale.mo");
		load_textdomain( 'wc-frontend-manager', ABSPATH . "wp-content/languages/plugins/wc-frontend-manager-$locale.mo");
	}

	public function load_class($class_name = '') {
		if ('' != $class_name && '' != $this->token) {
			require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}
	
	/**
	 * Restrict vendors to access wp-admin
	 */
	function restrict_wcfm_vendor_backend(){
	  global $WCFM, $_GET;
		if( is_user_logged_in() ) {
			$wcfm_capability_options = apply_filters( 'wcfm_capability_options_rules', get_option( 'wcfm_capability_options' ) );
			$wcfm_vnd_wpadmin = ( isset( $wcfm_capability_options['vnd_wpadmin'] ) ) ? $wcfm_capability_options['vnd_wpadmin'] : 'no';
			$is_setup  = false;
			$is_export = false;
			$is_import = false;
			if( isset($_GET['page']) && ( $_GET['page'] == 'product_exporter' ) ) { $is_export = true; }
			if( isset($_GET['page']) && ( $_GET['page'] == 'product_importer' ) ) { $is_import = true; }
			if( isset($_GET['page']) && ( $_GET['page'] == 'store-setup' ) ) { $is_setup = true; }
			
			if( ( 'yes' == $wcfm_vnd_wpadmin ) && !$is_export && !$is_import && !$is_setup ) {
				if( isset( $_GET['wc_gcal_oauth'] ) || isset( $_GET['wc_gcal_logout'] ) ) {
					// WC Appointments Gcal OAuth support
					wp_redirect( get_wcfm_profile_url() . '#wcfm_profile_manage_form_apt_gcal_sync_head' );
				} else {
					wp_redirect( get_wcfm_url() );
				}
				exit;
			}
		}
	}

	// End load_class()
	
	// WCV Shop Vendor 
	function wcvendors_register_post_type_shop_order_vendor( $shop_order_vendor ) {
		$shop_order_vendor['exclude_from_order_reports'] = true;
		return $shop_order_vendor;
	}

	/**
	 * Install upon activation.
	 *
	 * @access public
	 * @return void
	 */
	static function activate_wcfm() {
		global $WCFM;

		require_once ( $WCFM->plugin_path . 'helpers/class-wcfm-install.php' );
		$WCFM_Install = new WCFM_Install();
		
		// Disable Vendor role - 4.0.2
		add_role( 'disable_vendor', __( 'Disable Vendor', 'wc-frontend-manager' ), array( 'level_0' => true ) );
        
		wcfm_check_php_mail( true );
		
		update_option( 'wcfm_disable_vendor_installed', 1 );
		update_option( 'wcfm_updated_6_5_2', 1 );
		update_option( 'wcfm_updated_6_5_5', 1 );
		update_option( 'wcfm_installed', 1 );
	}
	
	/**
	 * Check upon update.
	 *
	 * @access public
	 * @return void
	 */
	static function update_wcfm() {
		global $WCFM, $WCFM_Query, $wpdb;
		
		if( !get_option( 'wcfm_updated_6_5_2' ) ) {
			$options = get_option( 'wcfm_options', array() );
			$options['module_options']['shipstation'] = 'yes';
			update_option( 'wcfm_options', $options );
			update_option( 'wcfm_updated_6_5_2', 1 );
		}
		
		if( !get_option( 'wcfm_updated_6_5_5' ) ) {
			$options = get_option( 'wcfm_options', array() );
			$options['module_options']['facebook_marketplace'] = 'yes';
			update_option( 'wcfm_options', $options );
			update_option( 'wcfm_updated_6_5_5', 1 );
		}
		
		$wcfm_tables = $wpdb->query( "SHOW tables like '{$wpdb->prefix}wcfm_fbc_chat_rows'");
		if( !$wcfm_tables ) {
			delete_option( 'wcfm_updated_6_4_1' );
			delete_option( 'wcfm_table_install' );
		}
		if( !get_option( 'wcfm_updated_6_4_1' ) ) {
			delete_option( 'wcfm_table_install' );
			require_once ( $WCFM->plugin_path . 'helpers/class-wcfm-install.php' );
			$WCFM_Install = new WCFM_Install();
			update_option( 'wcfm_updated_6_4_1', 1 );
		}
		
		// Disable Vendor role - 4.0.2
		if( !get_option( 'wcfm_disable_vendor_installed' ) ) {
			add_role( 'disable_vendor', __( 'Disable Vendor', 'wc-frontend-manager' ), array( 'level_0' => true ) );
			update_option('wcfm_disable_vendor_installed', 1);
		}
	}
	
	/**
	 * Check and cean WCfM Dashboard page setting conflict with other plugins
	 */
	function clean_wcfm_page_setting_conflict() {
		$wcfm_page_options = get_option("wcfm_page_options", array());
		
		// Vendor Dashboard
		if( isset( $wcfm_page_options['wc_frontend_manager_page_id'] ) ) {
			$wcfm_dashboard = $wcfm_page_options['wc_frontend_manager_page_id'];
			
			// Check for WCMp
			if( class_exists('WCMp') ) {
				$wcmp_vendor_page_id = get_wcmp_vendor_settings( 'wcmp_vendor', 'vendor', 'general' );
				if( $wcfm_dashboard == $wcmp_vendor_page_id ) {
					update_option( 'wcmp_product_vendor_vendor_page_id', -1 );
					update_wcmp_vendor_settings( 'wcmp_vendor', -1, 'vendor', 'general' );
				}
			}
			
			// Check for WC Vendors
			if( class_exists('WC_Vendors') ) {
				$wc_prd_vendor_options = get_option( 'wc_prd_vendor_options', array() );
				if( !empty( $wc_prd_vendor_options ) ) {
					if( isset( $wc_prd_vendor_options['vendor_dashboard_page'] ) && ( $wcfm_dashboard == $wc_prd_vendor_options['vendor_dashboard_page'] ) ) {
						$wc_prd_vendor_options['vendor_dashboard_page'] = -1;
					}
					if( isset( $wc_prd_vendor_options['product_orders_page'] ) && ( $wcfm_dashboard == $wc_prd_vendor_options['product_orders_page'] ) ) {
						$wc_prd_vendor_options['product_orders_page'] = -1;
					}
					if( isset( $wc_prd_vendor_options['shop_settings_page'] ) && ( $wcfm_dashboard == $wc_prd_vendor_options['shop_settings_page'] ) ) {
						$wc_prd_vendor_options['shop_settings_page'] = -1;
					}
					if( isset( $wc_prd_vendor_options['terms_to_apply_page'] ) && ( $wcfm_dashboard == $wc_prd_vendor_options['terms_to_apply_page'] ) ) {
						$wc_prd_vendor_options['terms_to_apply_page'] = -1;
					}
					if( isset( $wc_prd_vendor_options['dashboard_page_id'] ) && ( $wcfm_dashboard == $wc_prd_vendor_options['dashboard_page_id'] ) ) {
						$wc_prd_vendor_options['dashboard_page_id'] = -1;
					}
					update_option( 'wc_prd_vendor_options', $wc_prd_vendor_options );
				}
			}
			
			// Check for Dokan
			if( class_exists('WeDevs_Dokan') ) {
				$dokan_pages = get_option( 'dokan_pages', array() );
				if( !empty( $dokan_pages ) ) {
					if( isset( $dokan_pages['dashboard'] ) && ( $wcfm_dashboard == $dokan_pages['dashboard'] ) ) {
						$dokan_pages['dashboard'] = -1;
					}
					if( isset( $dokan_pages['my_orders'] ) && ( $wcfm_dashboard == $dokan_pages['my_orders'] ) ) {
						$dokan_pages['my_orders'] = -1;
					}
					update_option( 'dokan_pages', $dokan_pages );
				}
			}
			
			// Check for Listings
			if( class_exists('WP_Job_Manager') ) {
				$job_manager_submit_job_form_page_id = get_option( 'job_manager_submit_job_form_page_id', false );
				$job_manager_job_dashboard_page_id = get_option( 'job_manager_job_dashboard_page_id', false );
				$job_manager_jobs_page_id = get_option( 'job_manager_jobs_page_id', false );
				$wp_job_manager_stats_page_id = get_option( 'wp_job_manager_stats_page_id', false );
				
				if( $job_manager_submit_job_form_page_id && ( $wcfm_dashboard ==  $job_manager_submit_job_form_page_id ) ) {
					update_option( 'job_manager_submit_job_form_page_id', -1 );
				}
				if( $job_manager_job_dashboard_page_id && ( $wcfm_dashboard ==  $job_manager_job_dashboard_page_id ) ) {
					update_option( 'job_manager_job_dashboard_page_id', -1 );
				}
				if( $job_manager_jobs_page_id && ( $wcfm_dashboard ==  $job_manager_jobs_page_id ) ) {
					update_option( 'job_manager_jobs_page_id', -1 );
				}
				if( $wp_job_manager_stats_page_id && ( $wcfm_dashboard ==  $wp_job_manager_stats_page_id ) ) {
					update_option( 'wp_job_manager_stats_page_id', -1 );
				}
			}
		}
		
		// Membership Dashboard
		if( isset( $wcfm_page_options['wcfm_vendor_membership_page_id'] ) ) {
			$wcfm_membership = $wcfm_page_options['wcfm_vendor_membership_page_id'];
			
			if( class_exists('WCMp') ) {
			$wcmp_registration_page_id = get_wcmp_vendor_settings( 'vendor_registration', 'vendor', 'general' );
				if( $wcfm_membership == $wcmp_registration_page_id ) {
					update_option( 'wcmp_product_vendor_registration_page_id', -1 );
					update_wcmp_vendor_settings( 'vendor_registration', -1, 'vendor', 'general' );
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
	static function deactivate_wcfm() {
		global $WCFM;
		
		wcfm_check_php_mail( false );
		delete_option('wcfm_installed');
	}
	
	function get_wcfm_menus() {
		global $WCFM;
		$wcfm_menus = apply_filters( 'wcfm_menus', array( 'wcfm-products' => array( 'label'  => __( 'Products', 'wc-frontend-manager'),
																																			 'url'        => get_wcfm_products_url(),
																																			 'icon'       => 'cube',
																																			 'has_new'    => 'yes',
																																			 'new_class'  => 'wcfm_sub_menu_items_product_manage',
																																			 'new_url'    => get_wcfm_edit_product_url(),
																																			 'capability' => 'wcfm_product_menu',
																																			 'submenu_capability' => 'wcfm_add_new_product_sub_menu',
																																			 'priority'   => 5
																																			),
																									'wcfm-coupons' => array(  'label'      => __( 'Coupons', 'wc-frontend-manager'),
																																			 'url'        => get_wcfm_coupons_url(),
																																			 'icon'       => 'gift',
																																			 'has_new'    => 'yes',
																																			 'new_class'  => 'wcfm_sub_menu_items_coupon_manage',
																																			 'new_url'    => get_wcfm_coupons_manage_url(),
																																			 'capability' => 'wcfm_coupon_menu',
																																			 'submenu_capability' => 'wcfm_add_new_coupon_sub_menu',
																																			 'priority'   => 40
																																			),
																									'wcfm-orders' => array(  'label'  => __( 'Orders', 'wc-frontend-manager'),
																																			 'url'        => get_wcfm_orders_url(),
																																			 'icon'       => 'shopping-cart',
																																			 'priority'   => 35
																																			),
																									'wcfm-reports' => array(  'label'      => __( 'Reports', 'wc-frontend-manager'),
																																			 'url'        => get_wcfm_reports_url(),
																																			 'icon'       => 'chart-pie',
																																			 'priority'   => 70
																																			),
																									'wcfm-settings' => array( 'label'      => __( 'Settings', 'wc-frontend-manager'),
																																			 'url'        => get_wcfm_settings_url(),
																																			 'icon'       => 'cogs',
																																			 'priority'   => 75
																																			),
																									'wcfm-capability' => array(   'label'  => __( 'Capability', 'wc-frontend-manager'),
																																			 'url'      => get_wcfm_capability_url(),
																																			 'icon'     => 'user-times',
																																			 'priority' => 80
																																			)
																								)
														);
		
		if ( !function_exists( 'wc_coupons_enabled' ) || ( function_exists( 'wc_coupons_enabled' ) && !wc_coupons_enabled() ) || !apply_filters( 'wcfm_is_pref_coupon', true ) ) unset( $wcfm_menus['wcfm-coupons'] );
		
		uasort( $wcfm_menus, array( &$this, 'wcfm_sort_by_priority' ) );
		
		return $wcfm_menus;
	}
	
	/**
	 * WCfM Formatted Menus
	 */
	function wcfm_formeted_menus( $wcfm_menus ) {
		global $WCFM;
		
		$wcfm_managed_menus = wcfm_get_option( 'wcfm_managed_menus', array() );
		$wcfm_formeted_menus = array(); 
		if( empty( $wcfm_managed_menus ) ) {
			$wcfm_formeted_menus = $wcfm_menus;
		} else {
			foreach( $wcfm_menus as $wcfm_menu_key => $wcfm_menu ) {
				$has_managed = false;
				foreach( $wcfm_managed_menus as $wcfm_managed_menu_key => $wcfm_managed_menu ) {
					if( !empty( $wcfm_managed_menu['custom'] ) && ( $wcfm_managed_menu['custom'] == 'no' ) ) {
						if( !empty( $wcfm_managed_menu['slug'] ) && ( $wcfm_menu_key == $wcfm_managed_menu['slug'] ) ) {
							if( isset( $wcfm_managed_menu['enable'] ) ) {
								$wcfm_menu['priority'] = $wcfm_managed_menu_key;
								$wcfm_menu['label'] = $wcfm_managed_menu['label'];
								if( ( $wcfm_menu_key == 'wcfm-vendors' ) && apply_filters( 'wcfm_is_allow_vendors_menu_label_update_by_sold_label', true ) ) $wcfm_menu['label'] = apply_filters( 'wcfm_sold_by_label', '', $wcfm_managed_menu['label'] ) . ' ' . __( 'Vendors', 'wc-frontend-manager');
								$wcfm_menu['icon'] = wcfm_replace_unsupported_icons( $wcfm_managed_menu['icon'] );
								$wcfm_menu['menu_for'] = 'both';
								$wcfm_menu['new_tab'] = 'no';
								if( isset( $wcfm_managed_menu['menu_for'] ) ) $wcfm_menu['menu_for'] = $wcfm_managed_menu['menu_for'];
								if( isset( $wcfm_managed_menu['new_tab'] ) ) $wcfm_menu['new_tab'] = $wcfm_managed_menu['new_tab'];
								$wcfm_formeted_menus[$wcfm_menu_key] = $wcfm_menu;
							}
							$has_managed = true;
						}
					}
				}
				if( !$has_managed ) {
					$wcfm_formeted_menus[$wcfm_menu_key] = $wcfm_menu;
				}
			}
			
			// Custom Menus 
			foreach( $wcfm_managed_menus as $wcfm_managed_menu_key => $wcfm_managed_menu ) {
				if( !empty( $wcfm_managed_menu['custom'] ) && ( $wcfm_managed_menu['custom'] == 'yes' ) ) {
					if( !empty( $wcfm_managed_menu['slug'] ) ) {
						if( isset( $wcfm_managed_menu['enable'] ) ) {
							$wcfm_managed_menu['priority'] = $wcfm_managed_menu_key;
							$wcfm_formeted_menus[$wcfm_managed_menu['slug']] = $wcfm_managed_menu;
						}
					}
				}
				
				if( is_admin() ) {
					/*if( !empty( $wcfm_managed_menu['custom'] ) && ( $wcfm_managed_menu['custom'] == 'no' ) ) {
						if( !empty( $wcfm_managed_menu['slug'] ) ) {
							if( isset( $wcfm_managed_menu['enable'] ) ) {
								if( !isset( $wcfm_formeted_menus[$wcfm_managed_menu['slug']] ) ) {
									if( isset( $wcfm_managed_menu['menu_for'] ) && in_array( $wcfm_managed_menu['menu_for'], array( 'both', 'admin' ) ) ) {
										$wcfm_managed_menu['priority'] = $wcfm_managed_menu_key;
										$wcfm_formeted_menus[$wcfm_managed_menu['slug']] = $wcfm_managed_menu;
									}
								}
							}
						}
					}*/
				}
			}
		}
		
		uasort( $wcfm_formeted_menus, array( $WCFM, 'wcfm_sort_by_priority' ) );
		
		return $wcfm_formeted_menus;
	}
	
	/**
	 * List of WCFM modules
	 */
	function get_wcfm_modules() {
		$wcfm_modules = array(
													'article'             => array( 'label' => __( 'Article', 'wc-frontend-manager' ) ),
													'customer'            => array( 'label' => __( 'Customer', 'wc-frontend-manager' ) ),
													'coupon'              => array( 'label' => __( 'Coupon', 'wc-frontend-manager' ) ),
													'policies'            => array( 'label' => __( 'Policies', 'wc-frontend-manager' ) ),
													'membership'          => array( 'label' => __( 'Membership', 'wc-frontend-manager' ) ),
													'profile'             => array( 'label' => __( 'Profile', 'wc-frontend-manager' ) ),
													'withdrawal'          => array( 'label' => __( 'Withdrawal', 'wc-frontend-manager' ) ),
													'refund'              => array( 'label' => __( 'Refund', 'wc-frontend-manager' ) ),
													'enquiry'             => array( 'label' => __( 'Enquiry', 'wc-frontend-manager' ) ),
													'enquiry_tab'         => array( 'label' => __( 'Enquiry Tab', 'wc-frontend-manager' ), 'hints' => __( 'If you just want to hide Single Product page `Enquiry Tab`, but keep enable `Enquiry Module` for `Catalog Mode`.', 'wc-frontend-manager' ) ),
													'catalog'             => array( 'label' => __( 'Catalog Mode', 'wc-frontend-manager' ), 'hints' => __( 'If you disable `Enquiry Module` then `Catalog Module` will stop working automatically.', 'wc-frontend-manager' ) ),
													'product_popup'       => array( 'label' => __( 'Popup Add Product', 'wc-frontend-manager' ) ),
													'custom_field'        => array( 'label' => __( 'Custom Field', 'wc-frontend-manager' ) ),
													'notification'        => array( 'label' => __( 'Notification', 'wc-frontend-manager' ) ),
													'direct_message'      => array( 'label' => __( 'Direct Message', 'wc-frontend-manager' ) ),
													'knowledgebase'       => array( 'label' => __( 'Knowledgebase', 'wc-frontend-manager' ) ),
													'notice'              => array( 'label' => __( 'Annoncement', 'wc-frontend-manager' ) ),
													//'menu_manager'        => array( 'label' => __( 'Menu Manager', 'wc-frontend-manager' ) ),
													//'submenu'             => array( 'label' => __( 'Sub-menu', 'wc-frontend-manager' ), 'hints' => __( 'This will disable `Add New` sub-menus on hover.', 'wc-frontend-manager' ) ),
													);
		
		if( WCFM_Dependencies::wcfm_biddypress_plugin_active_check() ) {
			$wcfm_modules['buddypress'] = array( 'label' => __( 'BuddyPress Integration', 'wc-frontend-manager' ) );
		}
			
		return apply_filters( 'wcfm_modules', $wcfm_modules );
	}
	
	/**
	 * Sorts array of custom fields by priority value.
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	function wcfm_sort_by_priority( $a, $b ) {
		if ( ! isset( $a['priority'] ) || ! isset( $b['priority'] ) || $a['priority'] === $b['priority'] ) {
				return 0;
		}
		return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
	}
	
	function wcfm_color_setting_options() {
		global $WCFM;
		
		$color_options = apply_filters( 'wcfm_color_setting_options', array( 'wcfm_field_base_highlight_color' => array( 'label' => __( 'Base Highlighter Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_base_highlight_color_settings', 'default' => '#17a2b8', 'element' => '.wcfm-page-headig .wcfm-page-heading-text, #wcfm-main-contentainer .wcfm-page-headig a.active .wcfmfa, .wcfm_dashboard_membership_details, div.wcfm-collapse-content h2, #wcfm_page_load .wcfmfa, #wcfm-main-contentainer .wcfm_header_panel a:hover, #wcfm-main-contentainer .wcfm_header_panel a.active, ul.wcfm_products_menus li a, ul.wcfm_listings_menus li a, #wcfm-main-contentainer .wcfm-container-box .wcfm-container .booking_dashboard_section_icon, #wcfm-main-contentainer .wcfm_bookings_gloabl_settings, #wcfm-main-contentainer .wcfm_gloabl_settings, #wcfm-main-contentainer .wcfm_screen_manager_dummy, #wcfm-main-contentainer .wcfm_screen_manager, #wcfm-main-contentainer .woocommerce-reports-wide .postbox div.stats_range ul li.active a, .wcfm_reports_menus li a, #wcfm-main-contentainer .sales_schedule, #wcfm-main-contentainer .woocommerce-exporter-wrapper .wc-progress-form-content .woocommerce-importer-done::before, #wcfm-main-contentainer .woocommerce-exporter-wrapper .woocommerce-exporter .woocommerce-importer-done::before, #wcfm-main-contentainer .woocommerce-exporter-wrapper .woocommerce-importer .woocommerce-importer-done::before, #wcfm-main-contentainer .woocommerce-importer-wrapper .wc-progress-form-content .woocommerce-importer-done::before, #wcfm-main-contentainer .woocommerce-importer-wrapper .woocommerce-exporter .woocommerce-importer-done::before, .woocommerce-importer-wrapper .woocommerce-importer .woocommerce-importer-done::before, .woocommerce-progress-form-wrapper .wc-progress-form-content .woocommerce-importer-done::before, .woocommerce-progress-form-wrapper .woocommerce-exporter .woocommerce-importer-done::before, .woocommerce-progress-form-wrapper .woocommerce-importer .woocommerce-importer-done::before, .woocommerce-exporter-wrapper .wc-progress-steps li.done, .woocommerce-importer-wrapper .wc-progress-steps li.done, .woocommerce-progress-form-wrapper .wc-progress-steps li.done, .woocommerce-exporter-wrapper .wc-progress-steps li.active, .woocommerce-importer-wrapper .wc-progress-steps li.active, #wcfm-main-contentainer ul.wcfm_orders_menus li a, ul.wcfm_bookings_menus li a, #wcfm-main-contentainer .wc_bookings_calendar_form .wc_bookings_calendar td .bookings ul li a strong, #wcfm-main-contentainer .wc_bookings_calendar_form .tablenav .views a, #wcfm-main-contentainer .wc_bookings_calendar_form .tablenav .date_selector a, #wcfm-main-contentainer ul.wcfm_appointments_menus li a, #wcfm-main-contentainer .wcfm-container-box .wcfm-container .appointment_dashboard_section_icon, #wcfm-main-contentainer .wcfm_appointment_gloabl_settings, #wcfm-main-contentainer .wc_appointments_calendar_form .wc_appointments_calendar td .appointments ul li a strong, #wcfm-main-contentainer .wc_appointments_calendar_form .calendar_wrapper ul li a strong, #wcfm-main-contentainer .wc_appointments_calendar_form .tablenav .views a, #wcfm-main-contentainer .wc_appointments_calendar_form .tablenav .date_selector a, #wcfm-main-contentainer .mapp-m-panel a, #wcfm-main-contentainer .woocommerce-reports-wide .postbox div.stats_range ul li.custom.active, #wcfm-main-contentainer .sub_checklist_toggler, .woocommerce-progress-form-wrapper .wc-progress-steps li.active, .wcfm_fetch_tag_cloud:hover, .wcfm_add_new_category:hover, .wcfm_fetch_tag_cloud a:hover, #wcfm-main-contentainer table thead td, #wcfm-main-contentainer table thead th, #wcfm-main-contentainer table tfoot td, #wcfm-main-contentainer table tfoot th, .wcfm_welcomebox_user_details h3, .wcfm_product_title, .wcfm_coupon_title, .wcfm_order_title, .wcfm_booking_title, .wcfm_appointment_title, .wcfm_auctions_title, .wcfm_listing_title, .wcfm_dashboard_item_title, .wcfmmp_sold_by_wrapper a, .wcfm-store-setup .wc-setup-steps li.active, .wcfm-store-setup .wc-setup-steps li.done, .wcfm-store-setup h1#wc-logo a', 'style' => 'color', 'element2' => '.woocommerce-exporter-wrapper .wc-progress-steps li.active::before, .woocommerce-importer-wrapper .wc-progress-steps li.active::before, .woocommerce-progress-form-wrapper .wc-progress-steps li.active::before, .woocommerce-exporter-wrapper .wc-progress-steps li.done::before, .woocommerce-importer-wrapper .wc-progress-steps li.done::before, .woocommerce-progress-form-wrapper .wc-progress-steps li.done::before,  .woocommerce-exporter-wrapper .wc-progress-steps li.done::before, .woocommerce-importer-wrapper .wc-progress-steps li.done::before, .woocommerce-progress-form-wrapper .wc-progress-steps li.done::before, .woocommerce-exporter-wrapper .wc-progress-steps li.done, .woocommerce-importer-wrapper .wc-progress-steps li.done, .woocommerce-exporter-wrapper .wc-progress-steps li.active, .woocommerce-importer-wrapper .wc-progress-steps li.active, .wcfm_vacation_msg, #wcfm-main-contentainer a.add_new_wcfm_ele_dashboard:hover, #wcfm-main-contentainer a.wcfm_import_export:hover, #wcfm_auto_suggest_product_title li a:hover, .wcfm-action-icon:hover, #wcfm-main-contentainer .wcfm_product_popup_button, .wcfm-store-setup .wc-setup-steps li.active:before, .wcfm-store-setup .wc-setup-steps li.done:before', 'style2' => 'background-color', 'element3' => '#wcfm-main-contentainer .woocommerce-reports-wide .button:hover, #mapp_e_search, #wcfm-main-contentainer #wcfm_quick_edit_button:hover, #wcfm-main-contentainer #wcfm_screen_manager_button:hover, .woocommerce-exporter-wrapper .wc-progress-steps li.done::before, .woocommerce-importer-wrapper .wc-progress-steps li.done::before, .woocommerce-progress-form-wrapper .wc-progress-steps li.done::before, #wcfm-main-contentainer .wcfm_admin_message .primary:hover, #wcfm-main-contentainer button.wcfm_submit_button:hover, #wcfm-main-contentainer input.wcfm_submit_button:hover, #wcfm-main-contentainer a.wcfm_submit_button:hover, #wcfm-main-contentainer .wcfm_add_category_bt:hover, #wcfm-main-contentainer .wcfm_add_attribute:hover, #wcfm-main-contentainer input.upload_button:hover, #wcfm-main-contentainer input.remove_button:hover, #wcfm-main-contentainer .multi_input_block_manupulate:hover, #wcfm-main-contentainer .dataTables_wrapper .dt-buttons .dt-button:hover, #wcfm_vendor_approval_response_button:hover, #wcfm_bulk_edit_button:hover, #wcfm_enquiry_submit_button:hover, #wcfm_tracking_button:hover', 'style3' => 'background', 'element4' => '#wcfm-main-contentainer .page_collapsible::before, #wcfm-main-contentainer input.wcfm_submit_button, #wcfm-main-contentainer a.wcfm_submit_button, #wcfm-main-contentainer .wcfm_add_category_bt, #wcfm-main-contentainer .wcfm_add_attribute, #wcfm-main-contentainer input.upload_button, #wcfm-main-contentainer input.remove_button, #wcfm-main-contentainer a.add_new_wcfm_ele_dashboard, #wcfm-main-contentainer a.wcfm_import_export, #wcfm_menu .wcfm_menu_items a.wcfm_menu_item::before, #wcfm-main-contentainer .wcfm-page-headig::before, .wcfm_dashboard_welcome_content::before, .wcfm_dashboard_stats_block, .wcfm-container-box .wcfm-container, .wcfm-collapse .wcfm-container, .wcfm-tabWrap, .wcfm-action-icon, #wcfm_vendor_approval_response_button, #wcfm_bulk_edit_button, #wcfm_enquiry_submit_button, #wcfm_tracking_button', 'style4' => 'border-bottom-color', 'element5' => '.woocommerce-progress-form-wrapper .wc-progress-steps li.active, .woocommerce-exporter-wrapper .wc-progress-steps li.active::before, .woocommerce-importer-wrapper .wc-progress-steps li.active::before, .woocommerce-progress-form-wrapper .wc-progress-steps li.active::before, .wcfm_header_panel a.wcfm_header_panel_profile.active img, .wcfm-store-setup .wc-setup-steps li.active, .wcfm-store-setup .wc-setup-steps li.done, .wcfm-store-setup .wc-setup-steps li.active:before, .wcfm-store-setup .wc-setup-steps li.done:before', 'style5' => 'border-color' ),
																																				 'wcfm_field_header_bg_color' => array( 'label' => __( 'Top Bar Background Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_header_bg_color_settings', 'default' => '#1C2B36', 'element' => '#wcfm-main-contentainer .wcfm-page-headig, .wcfm_menu_logo, .wcfm_menu_no_logo', 'style' => 'background' ),
																																				 'wcfm_field_header_text_color' => array( 'label' => __( 'Top Bar Text Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_header_text_color_settings', 'default' => '#ffffff', 'element' => '.wcfm_menu_logo h4, .wcfm_menu_logo h4 a, #wcfm-main-contentainer .wcfm-page-headig, #wcfm-main-contentainer .wcfm-page-headig .wcfmfa, .wcfm_menu_logo, .wcfm_menu_no_logo', 'style' => 'color' ),
																																				 'wcfm_field_dashboard_bg_color' => array( 'label' => __( 'Dashboard Background Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_dashboard_bg_color_settings', 'default' => '#eceef2', 'element' => '#wcfm-main-contentainer .wcfm-collapse', 'style' => 'background', 'element2' => '#wcfm_menu .wcfm_menu_items a.active::after', 'style2' => 'border-right-color' ),
																																				 'wcfm_field_container_color' => array( 'label' => __( 'Container Background Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_container_background_color_settings', 'default' => '#ffffff', 'element' => '.wcfm-collapse .wcfm-container, #wcfm-main-contentainer div.wcfm-content', 'style' => 'background' ),
																																				 'wcfm_field_primary_bg_color' => array( 'label' => __( 'Container Head Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_primary_bg_color_settings', 'default' => '#1C2B36', 'element' => '.page_collapsible, .collapse-close, .wcfm-collapse a.page_collapsible_dummy', 'style' => 'background' ),
																																				 'wcfm_field_primary_font_color' => array( 'label' => __( 'Container Head Text Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_primary_font_color_settings', 'default' => '#ffffff', 'element' => '.page_collapsible, .page_collapsible label, .collapse-close, .wcfm-collapse a.page_collapsible_dummy', 'style' => 'color' ),
																																				 'wcfm_field_secondary_bg_color' => array( 'label' => __( 'Container Head Active Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_secondary_bg_color_settings', 'default' => '#1C2B36', 'element' => '.collapse-open', 'style' => 'background' ),
																																				 'wcfm_field_secondary_font_color' => array( 'label' => __( 'Container Head Active Text Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_secondary_font_color_settings', 'default' => '#17a2b8', 'element' => '.collapse-open, .page_collapsible:hover label, .page_collapsible.collapse-open label', 'style' => 'color' ),
																																				 'wcfm_field_menu_bg_color' => array( 'label' => __( 'Menu Background Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_menu_bg_color_settings', 'default' => '#1C2B36', 'element' => '#wcfm_menu, #wcfm_menu span.wcfm_sub_menu_items', 'style' => 'background' ),
																																				 'wcfm_field_menu_icon_color' => array( 'label' => __( 'Menu Item Text Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_menu_icon_color_settings', 'default' => '#ffffff', 'element' => '#wcfm_menu .wcfm_menu_item span, #wcfm_menu span.wcfm_sub_menu_items a, .wcfm_menu_no_logo h4, .wcfm_menu_no_logo h4 a', 'style' => 'color' ),
																																				 'wcfm_field_menu_icon_active_bg_color' => array( 'label' => __( 'Menu Active Item Background', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_menu_icon_active_bg_color_settings', 'default' => '#17a2b8', 'element' => '#wcfm_menu .wcfm_menu_items a.active', 'style' => 'background', 'element2' => '#wcfm_menu .wcfm_menu_items:hover a span.wcfmfa, #wcfm_menu .wcfm_menu_items a:hover span', 'style2' => 'color', 'element3' => '#wcfm_menu .wcfm_menu_items a.wcfm_menu_item:hover:after', 'style3' => 'border-right-color' ),
																																				 'wcfm_field_menu_icon_active_color' => array( 'label' => __( 'Menu Active Item Text Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_menu_icon_active_color_settings', 'default' => '#ffffff', 'element' => '#wcfm_menu .wcfm_menu_items a.active span, #wcfm_menu .wcfm_menu_items a.active:hover span', 'style' => 'color' ),
																																				 'wcfm_field_button_color' => array( 'label' => __( 'Button Background Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_button_background_color_settings', 'default' => '#1C2B36', 'element' => '#wcfm-main-contentainer a.add_new_wcfm_ele_dashboard, #wcfm-main-contentainer a.wcfm_import_export, #wcfm-main-contentainer input.wcfm_submit_button, #wcfm-main-contentainer button.wcfm_submit_button, #wcfm-main-contentainer a.wcfm_submit_button, #wcfm-main-contentainer .wcfm_add_category_bt, #wcfm-main-contentainer .wcfm_add_attribute, #wcfm-main-contentainer input.upload_button, #wcfm-main-contentainer input.remove_button, #wcfm-main-contentainer .dataTables_wrapper .dt-buttons .dt-button, #wcfm_vendor_approval_response_button, #wcfm_bulk_edit_button, #wcfm_enquiry_submit_button, #wcfm_tracking_button, #submit-job-form input[type="submit"], #job_preview input[type="submit"], .wcfm-store-setup .wc-setup-actions .wcfm_submit_button', 'style' => 'background', 'default2' => '#1C2B36', 'element2' => '.wcfm-store-setup .wc-setup-actions .wcfm_submit_button', 'style2' => 'border-color' ),
																																				 'wcfm_field_button_text_color' => array( 'label' => __( 'Button Text Color', 'wc-frontend-manager' ), 'name' => 'wc_frontend_manager_button_text_color_settings', 'default' => '#b0bec5', 'element' => '#wcfm-main-contentainer a.add_new_wcfm_ele_dashboard, #wcfm-main-contentainer a.wcfm_import_export, #wcfm-main-contentainer input.wcfm_submit_button, #wcfm-main-contentainer button.wcfm_submit_button, #wcfm-main-contentainer a.wcfm_submit_button, #wcfm-main-contentainer .wcfm_add_category_bt, #wcfm-main-contentainer .wcfm_add_attribute, #wcfm-main-contentainer input.upload_button, #wcfm-main-contentainer input.remove_button, #wcfm-main-contentainer .dataTables_wrapper .dt-buttons .dt-button, #submit-job-form input[type="submit"], #job_preview input[type="submit"], .wcfm-store-setup .wc-setup-actions .wcfm_submit_button', 'style' => 'color' ),
																																			) );
		
		return $color_options;
	}
	
	/**
	 * Create WCFM custom CSS
	 */
	function wcfm_create_custom_css() {
		global $WCFM;
		
		$wcfm_options  = $WCFM->wcfm_options;
		$color_options = $WCFM->wcfm_color_setting_options();
		$custom_color_data = '';
		foreach( $color_options as $color_option_key => $color_option ) {
			
			if( substr( $wcfm_options[ $color_option['name'] ], 0, 1 ) !== "#" ) { $wcfm_options[ $color_option['name'] ] = '#' . $wcfm_options[ $color_option['name'] ]; } 
			
		  $custom_color_data .= $color_option['element'] . '{ ' . "\n";
			$custom_color_data .= "\t" . $color_option['style'] . ': ';
			if( isset( $wcfm_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfm_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
			$custom_color_data .= ';' . "\n";
			$custom_color_data .= '}' . "\n\n";
			
			if( isset( $color_option['element2'] ) && isset( $color_option['style2'] ) ) {
				$custom_color_data .= $color_option['element2'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style2'] . ': ';
				if( isset( $wcfm_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfm_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
			
			if( isset( $color_option['element3'] ) && isset( $color_option['style3'] ) ) {
				$custom_color_data .= $color_option['element3'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style3'] . ': ';
				if( isset( $wcfm_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfm_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
			
			if( isset( $color_option['element4'] ) && isset( $color_option['style4'] ) ) {
				$custom_color_data .= $color_option['element4'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style4'] . ': ';
				if( isset( $wcfm_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfm_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
			
			if( isset( $color_option['element5'] ) && isset( $color_option['style5'] ) ) {
				$custom_color_data .= $color_option['element5'] . '{ ' . "\n";
				$custom_color_data .= "\t" . $color_option['style5'] . ': ';
				if( isset( $wcfm_options[ $color_option['name'] ] ) ) { $custom_color_data .= $wcfm_options[ $color_option['name'] ]; } else { $custom_color_data .= $color_option['default']; }
				$custom_color_data .= ';' . "\n";
				$custom_color_data .= '}' . "\n\n";
			}
		}
		
		$upload_dir      = wp_upload_dir();

		$files = array(
			array(
				'base' 		=> $upload_dir['basedir'] . '/wcfm',
				'file' 		=> 'wcfm-style-custom-' . time() . '.css',
				'content' 	=> $custom_color_data,
			)
		);

		$wcfm_style_custom = get_option( 'wcfm_style_custom' );
		if( file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfm_style_custom ) ) {
			unlink( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfm_style_custom );
		}
		
		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					$wcfm_style_custom = $file['file'];
					update_option( 'wcfm_style_custom', $file['file'] );
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
		return $wcfm_style_custom;
	}
	
	function wcfm_get_attachment_id($attachment_url) {
		global $wpdb;
		$upload_dir_paths = wp_upload_dir();
		$attachment_id = 0;
		
		if( $attachment_url && is_numeric( $attachment_url ) )
			return $attachment_url;
		
		$attachment_url = apply_filters( 'wcfm_attachment_url', $attachment_url );
		
		/*if( class_exists('WPH') ) {
			global $wph;
			
			$attachment_id = (int) attachment_url_to_postid( $attachment_url );
			if( !$attachment_id ) {
				$new_upload_path        =   $wph->functions->untrailingslashit_all(    $wph->functions->get_module_item_setting('new_upload_path')  );
        $new_content_path       =   $wph->functions->untrailingslashit_all(    $wph->functions->get_module_item_setting('new_content_path')  );
				
				if( $new_upload_path ) {
					$attachment_url = str_replace( $new_upload_path, 'wp-content/uploads', $attachment_url );
				} elseif( $new_content_path ) {
					$attachment_url = str_replace( $new_content_path, 'wp-content', $attachment_url );
				}
			} else {
				return $attachment_id; 
			}
		}
		
		
		if( class_exists('HideMyWP') ) {
			global $HideMyWP;
			
			$attachment_id = (int) attachment_url_to_postid( $attachment_url );
			if( !$attachment_id ) {
				$new_upload_path = trim($HideMyWP->opt('new_upload_path'), '/ ');
				$new_content_path = trim($HideMyWP->opt('new_content_path'), '/ ');
				if( $new_upload_path ) {
					$attachment_url = str_replace( $new_upload_path, 'uploads', $attachment_url );
				}
				if( $new_content_path ) {
					$attachment_url = str_replace( $new_content_path, 'wp-content', $attachment_url );
				}
			}
			//$attachment_url = str_replace( $new_content_path, '/wp-content', str_replace( $new_upload_path, '/uploads', $attachment_url ) );
		}*/
		
		//if( function_exists( 'ud_get_stateless_media' ) ) {
			//$bucketLink = ud_get_stateless_media()->get_gs_host();
      //$bucketLink = apply_filters('wp_stateless_bucket_link', $bucketLink);
      //$attachment_url = str_replace( $bucketLink . '/', '', $attachment_url );
		//}
		
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		/*if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] . '/' ) ) {
			$attachment_id = (int) attachment_url_to_postid( $attachment_url );
		} elseif( class_exists( 'Amazon_S3_And_CloudFront' ) ) {
			global $as3cf;
			$scheme = $as3cf->get_url_scheme();
			$bucket = $as3cf->get_setting( 'bucket' );
			$region = $as3cf->get_setting( 'region' );
			if ( is_wp_error( $region ) ) {
				$region = '';
			}
	
			$domain = $as3cf->get_provider()->get_url_domain( $bucket, $region, null, array(), true );
			$amazon_s3_url_domain = $scheme . '://' . $domain . '/';
			
			$attachment_url = str_replace( $amazon_s3_url_domain, '', $attachment_url );
		
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = 'amazonS3_info' AND wpostmeta.meta_value LIKE '%s' AND wposts.post_type = 'attachment'", '%' . $attachment_url . '%' ) );
			
			if( !$attachment_id ) {
				$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attachment_metadata' AND wpostmeta.meta_value LIKE '%s' AND wposts.post_type = 'attachment'", '%' . $attachment_url . '%' ) );
			}
		} else {
			$attachment_id = (int) attachment_url_to_postid( $attachment_url );
		}*/
		
		$attachment_id = (int) attachment_url_to_postid( $attachment_url );
		
		return apply_filters( 'wcfm_attachment_id', $attachment_id, $attachment_url ); 
	}
	
	/**
	 * Prepare Chart Data
	 */
	function wcfm_prepare_chart_data( $chart_datas ) {
		
		$chart_data_label = '';
		$chart_data_set = '';
		
		if( !empty( $chart_datas ) ) {
			$chart_data_label .= '';
			$chart_data_set .= '';
			foreach( $chart_datas as $chart_data_key => $chart_data ) {
				if( $chart_data_label != '' ) $chart_data_label .= ',';
				if( $chart_data_set != '' ) $chart_data_set .= ',';
				
				$chart_data_label .= '"' . date( 'm d y', ($chart_data_key/1000) ) . '"';
				$chart_data_set   .= '"' . $chart_data[1] . '"';
				
			}
			$chart_data_sets = '{"labels" : [' . $chart_data_label . '], "datas" : [' . $chart_data_set . ']}';
		}
		
		return $chart_data_sets;
	}
	
	/** Cache Helpers ******************************************************** */

	/**
	 * Sets a constant preventing some caching plugins from caching a page. Used on dynamic pages
	 *
	 * @access public
	 * @return void
	 */
	function nocache() {
		wc_maybe_define_constant( 'DONOTCACHEPAGE', true );
		wc_maybe_define_constant( 'DONOTCACHEOBJECT', true );
		wc_maybe_define_constant( 'DONOTCACHEDB', true );
		
		if (!defined('DONOTCACHEPAGE'))
				define("DONOTCACHEPAGE", "true");
		// WP Super Cache constant
	}

}