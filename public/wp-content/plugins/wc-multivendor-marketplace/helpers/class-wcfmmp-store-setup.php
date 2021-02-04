<?php
/**
 * WCFM Marketplace Vendor Store Setup Class
 * 
 * @since 1.0.0
 * @package wcfm/helpers
 * @author WC Lovers
 */
if (!defined('ABSPATH')) {
    exit;
}

class WCFMmp_Store_Setup {

	/** @var string Currenct Step */
	private $step = '';

	/** @var array Steps for the setup wizard */
	private $steps = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wcfm_admin_menus' ) );
		add_action( 'init', array( $this, 'wcfmmp_store_setup' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function wcfm_admin_menus() {
		add_dashboard_page( '', '', 'manage_products', 'store-setup', '' );
	}
	
	/**
	 * Show the setup wizard.
	 */
	public function wcfmmp_store_setup() {
		global $WCFM, $WCFMmp;
		//if ( filter_input(INPUT_GET, 'page') != 'store-setup') {
			//return;
		//}
		
		if ( function_exists('icl_object_id') ) {
			global $sitepress;
			$lang = filter_input(INPUT_GET, 'lang');
			if( $lang ) {
				$sitepress->switch_lang($lang);
			}
		}

		$default_steps = array(
				'introduction' => array(
					'name' => __('Introduction', 'wc-frontend-manager' ),
					'view' => array($this, 'wcfmmp_store_setup_introduction'),
					'handler' => '',
				),
				'store' => array(
					'name' => __('Store', 'wc-multivendor-marketplace'),
					'view' => array($this, 'wcfmmp_store_setup_store'),
					'handler' => array($this, 'wcfmmp_store_setup_store_save')
				),
				'payment' => array(
					'name' => __('Payment', 'wc-multivendor-marketplace'),
					'view' => array($this, 'wcfmmp_store_setup_payment'),
					'handler' => array($this, 'wcfmmp_store_setup_payment_save')
				),
				'policy' => array(
					'name' => __('Policies', 'wc-multivendor-marketplace'),
					'view' => array($this, 'wcfmmp_store_setup_policy'),
					'handler' => array($this, 'wcfmmp_store_setup_policy_save')
				),
				'support' => array(
					'name' => __('Customer Support', 'wc-multivendor-marketplace'),
					'view' => array($this, 'wcfmmp_store_setup_customer_support'),
					'handler' => array($this, 'wcfmmp_store_setup_customer_support_save')
				),
				'seo' => array(
					'name' => __('SEO', 'wc-multivendor-marketplace'),
					'view' => array($this, 'wcfmmp_store_setup_seo'),
					'handler' => array($this, 'wcfmmp_store_setup_seo_save')
				),
				'social' => array(
					'name' => __('Social', 'wc-multivendor-marketplace'),
					'view' => array($this, 'wcfmmp_store_setup_social'),
					'handler' => array($this, 'wcfmmp_store_setup_social_save')
				),
				'next_steps' => array(
					'name' => __('Ready!', 'wc-frontend-manager'),
					'view' => array($this, 'wcfmmp_store_setup_ready'),
					'handler' => '',
				),
		);
		
		if( !apply_filters( 'wcfm_is_allow_billing_settings', true )  || !apply_filters( 'wcfm_is_allow_setup_billing_settings', true ) ) {
			unset( $default_steps['payment'] );
		}
		
		if( !apply_filters( 'wcfm_is_pref_policies', true ) || !apply_filters( 'wcfm_is_allow_policy_settings', true ) || !apply_filters( 'wcfm_is_allow_show_policy', true ) || !apply_filters( 'wcfm_is_allow_setup_policy_settings', true ) ) {
			unset( $default_steps['policy'] );
		}
		
		if( !apply_filters( 'wcfm_is_allow_customer_support_settings', true ) || !apply_filters( 'wcfm_is_allow_customer_support', true ) || !apply_filters( 'wcfm_is_allow_setup_customer_support', true ) ) {
			unset( $default_steps['support'] );
		}
		
		if( !apply_filters( 'wcfm_is_allow_vseo_settings', true ) || !apply_filters( 'wcfm_is_allow_setup_seo_settings', true ) ) {
			unset( $default_steps['seo'] );
		}
		
		if( !apply_filters( 'wcfm_is_allow_social_profile', true ) || !apply_filters( 'wcfm_is_allow_setup_social_profile', true ) ) {
			unset( $default_steps['social'] );
		}
		
		$this->steps = apply_filters('wcfmmp_store_setup_steps', $default_steps);
		$current_step = filter_input(INPUT_GET, 'step');
		$this->step = $current_step ? sanitize_key($current_step) : current(array_keys($this->steps));
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array('jquery'), '2.70', true);
		//wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
		//wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_localize_script(
			'wc-enhanced-select',
			'wc_enhanced_select_params',
			array(
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			)
		);
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
		wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array('dashicons', 'install'), WC_VERSION);
		wp_enqueue_style( 'wcfm-setup', $WCFM->plugin_url . 'assets/css/setup/wcfm-style-dashboard-setup.css', array('wc-setup'), $WCFM->version );
		
		if( is_rtl() ) {
			wp_enqueue_style( 'wcfm-setup-rtl', $WCFM->plugin_url . 'assets/css/setup/wcfm-style-dashboard-setup-rtl.css', array('wcfm-setup'), $WCFM->version );
		}
		
		// WCFM Custom CSS
		$upload_dir      = wp_upload_dir();
		$wcfm_style_custom = get_option( 'wcfm_style_custom' );
		if( $wcfm_style_custom && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfm_style_custom ) ) {
			wp_enqueue_style( 'wcfm_custom_css',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfm_style_custom, array( 'wcfm-setup' ), $WCFM->version );
		}
		
		wp_register_script('wcfm-setup', $WCFM->plugin_url . 'assets/js/setup/wcfm-script-setup.js', array('jquery'), $WCFM->version);
		//wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_register_script('wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup' . $suffix . '.js', array('jquery', 'jquery-blockui', 'wp-util', 'jquery-tiptip'), WC_VERSION);
		wp_localize_script('wc-setup', 'wc_setup_params', array(
				'locale_info' => json_encode(include( WC()->plugin_path() . '/i18n/locale-info.php' )),
		));
		
		wp_localize_script('wcfm-setup', 'wc_country_select_params', array(
				'countries'               => json_encode(WC()->countries->get_states()),
				'i18n_select_state_text'  => _x( 'Select an option...', 'woocommerce' )
		));
		
		//wp_enqueue_editor();
		
		$WCFM->library->load_collapsible_lib();
		$WCFM->library->load_select2_lib();
		$WCFM->library->load_upload_lib();
		
		wp_register_script( 'wcfm_menu_js', $WCFM->library->js_lib_url . 'wcfm-script-menu.js', array('jquery'), $WCFM->version, true );
		wp_localize_script( 'wcfm_menu_js', 'wcfm_params', array( 'ajax_url'    => WC()->ajax_url() ) );
    $wcfm_dashboard_messages = get_wcfm_dashboard_messages();
		wp_localize_script( 'wcfm_menu_js', 'wcfm_dashboard_messages', $wcfm_dashboard_messages );
		
		wp_register_script( 'collapsible_js', $WCFM->library->js_lib_url . 'jquery.collapsiblepanel.js', array('jquery'), $WCFM->version, true );
		wp_register_script( 'upload_js', $WCFM->plugin_url . 'includes/libs/upload/media-upload.js', array('jquery'), $WCFM->version, true );
		
		wp_register_script( 'wcfm_marketplace_settings_js', $WCFM->library->js_lib_url . 'settings/wcfm-script-wcfmmarketplace-settings.js', array('jquery'), $WCFM->version, true );
					
		$scheme  = is_ssl() ? 'https' : 'http';
		$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
		$WCFMmp->library->load_map_lib();
		
		wp_register_script( 'wcfm_settings_js', $WCFM->library->js_lib_url . 'settings/wcfm-script-settings.js', array('jquery'), $WCFM->version, true );
		wp_localize_script( 'wcfm_settings_js', 'wcfm_setting_options', array( 'default_tab' => apply_filters( 'wcfm_setting_default_tab', 'wcfm_settings_dashboard_head' ) ) );
		
		// Default Map Location
		$default_geolocation = isset( $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] ) ? $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] : array();
		$default_lat         = isset( $default_geolocation['lat'] ) ? esc_attr( $default_geolocation['lat'] ) : apply_filters( 'wcfmmp_map_default_lat', 30.0599153 );
		$default_lng         = isset( $default_geolocation['lng'] ) ? esc_attr( $default_geolocation['lng'] ) : apply_filters( 'wcfmmp_map_default_lng', 31.2620199 );
		$default_zoom        =  apply_filters( 'wcfmmp_map_default_zoom_level', 15 );
		
		$store_icon = apply_filters( 'wcfmmp_map_store_icon', $WCFMmp->plugin_url . 'assets/images/wcfmmp_map_icon.png', 0, '' );
		
		wp_localize_script( 'wcfm_marketplace_settings_js', 'wcfm_marketplace_setting_map_options', array( 'search_location' => __( 'Insert your address ..', 'wc-multivendor-marketplace' ), 'is_geolocate' => apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ), 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'store_icon' => $store_icon, 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_rtl' => is_rtl() ) );
		
		if (!empty($_POST['save_step']) && isset($this->steps[$this->step]['handler'])) {
				call_user_func($this->steps[$this->step]['handler'], $this);
		}

		ob_start();
		$this->wcfmmp_store_setup_header();
		$this->wcfmmp_store_setup_steps();
		$this->wcfmmp_store_setup_content();
		$this->wcfmmp_store_setup_footer();
		exit();
	}

	/**
	 * Get slug from path
	 * @param  string $key
	 * @return string
	 */
	private static function format_plugin_slug($key) {
			$slug = explode('/', $key);
			$slug = explode('.', end($slug));
			return $slug[0];
	}

	/**
	 * Get the URL for the next step's screen.
	 * @param string step   slug (default: current step)
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 1.0.0
	 */
	public function get_next_step_link($step = '') {
		if (!$step) {
			$step = $this->step;
		}

		$keys = array_keys($this->steps);
		if (end($keys) === $step) {
			return admin_url();
		}

		$step_index = array_search($step, $keys);
		if (false === $step_index) {
			return '';
		}

		return add_query_arg('step', $keys[$step_index + 1]);
	}

	/**
	 * Setup Wizard Header.
	 */
	public function wcfmmp_store_setup_header() {
		global $WCFM, $WCFMmp;
		
		$logo = get_option( 'wcfm_site_logo' ) ? get_option( 'wcfm_site_logo' ) : '';
		$logo_image_url = wcfm_get_attachment_url( $logo );
		
		if ( !$logo_image_url ) {
			$logo_image_url = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp-blue.png' );
		}
		
		$logo_image_url = apply_filters( 'wcfmmp_store_setup_logo', $logo_image_url );
		
		remove_action( 'wp_enqueue_scripts', 'et_builder_wc_load_scripts', 15 );
		
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e('Vendor Store &rsaquo; Setup Wizard', 'wc-multivendor-marketplace'); ?></title>
				
				<?php wp_head(); ?>
				
				<?php
				//wp_enqueue_script( 'wc-enhanced-select' );
				wp_enqueue_script( 'wc-setup' );
				wp_enqueue_script( 'wcfm-setup' );
				wp_enqueue_script( 'collapsible_js');
				wp_enqueue_script( 'media-editor' );
				wp_enqueue_script( 'upload_js' );
				wp_enqueue_script( 'wcfm_menu_js' );
				wp_enqueue_script( 'wcfm_marketplace_settings_js' );
				wp_enqueue_script( 'wcfm_settings_js' );
				wp_enqueue_script( 'wcfm-wcfmmarketplace-setting-google-maps' );
				?>
				
				<style type="text/css">
					.wc-setup-steps {
						justify-content: center;
					}
				</style>
			</head>
			<body class="wc-setup wcfm-store-setup wp-core-ui">
			 <h1 id="wc-logo"><a target="_blank" href="<?php echo site_url(); ?>"><img width="75" height="75" src="<?php echo $logo_image_url; ?>" alt="<?php echo get_bloginfo('title'); ?>" /><span><?php _e( 'Store Setup', 'wc-multivendor-marketplace' ); ?></span></a></h1>
			<?php
	}

	/**
	 * Output the steps.
	 */
	public function wcfmmp_store_setup_steps() {
		$ouput_steps = $this->steps;
		array_shift($ouput_steps);
		?>
		<ol class="wc-setup-steps">
			<?php foreach ($ouput_steps as $step_key => $step) : ?>
			  <li class="<?php
					if ($step_key === $this->step) {
							echo 'active';
					} elseif (array_search($this->step, array_keys($this->steps)) > array_search($step_key, array_keys($this->steps))) {
							echo 'done';
					}
					?>">
					<?php echo esc_html($step['name']); ?>
				</li>
		<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function wcfmmp_store_setup_content() {
		echo '<div class="wc-setup-content">';
		call_user_func($this->steps[$this->step]['view'], $this);
		echo '</div>';
	}

	/**
	 * Introduction step.
	 */
	public function wcfmmp_store_setup_introduction() {
		?>
		<h1><?php echo apply_filters( 'wcfm_store_setup_welcome_heading', sprintf( __("Welcome to %s!", 'wc-multivendor-marketplace'), get_bloginfo('title') ) ); ?></h1>
		<p><?php echo apply_filters( 'wcfm_store_setup_welcome_message', sprintf( __('Thank you for choosing %s! This quick setup wizard will help you to configure the basic settings and you will have your store ready in no time.', 'wc-multivendor-marketplace'), get_bloginfo('title') ) ); ?></p>
		<p><?php echo esc_html( apply_filters( 'wcfm_store_setup_welcome_footer', __( "If you don't want to go through the wizard right now, you can skip and return to the dashboard. You may setup your store from dashboard &rsaquo; setting anytime!", 'wc-multivendor-marketplace' ) ) ); ?></p>
		<p class="wc-setup-actions step">
			<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button-primary button button-large button-next wcfm_submit_button"><?php esc_html_e("Let's go!", 'wc-frontend-manager'); ?></a>
			<?php if( apply_filters( 'wcfm_is_allow_store_setup_step_skip', true ) ) { ?> 
			  <a href="<?php echo esc_url(get_wcfm_url()); ?>" class="button button-large"><?php esc_html_e('Not right now', 'wc-frontend-manager'); ?></a>
			<?php } ?>
		</p>
		<?php
	}

	/**
	 * Store setup content
	 */
	public function wcfmmp_store_setup_store() {
		global $WCFM, $WCFMmp;
		
		$user_id = $WCFMmp->vendor_id;

		$the_user = get_user_by( 'id', $user_id );
		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		// Store Genral
		$gravatar       = isset( $vendor_data['gravatar'] ) ? absint( $vendor_data['gravatar'] ) : 0;
		$banner         = isset( $vendor_data['banner'] ) ? absint( $vendor_data['banner'] ) : 0;
		$store_name     = isset( $vendor_data['store_name'] ) ? esc_attr( $vendor_data['store_name'] ) : '';
		$store_name     = empty( $store_name ) ? $the_user->display_name : $store_name;
		$store_email    = isset( $vendor_data['store_email'] ) ? esc_attr( $vendor_data['store_email'] ) : $the_user->user_email;
		$phone          = isset( $vendor_data['phone'] ) ? esc_attr( $vendor_data['phone'] ) : '';
		
		// Address
		$address         = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
		$street_1 = isset( $vendor_data['address']['street_1'] ) ? $vendor_data['address']['street_1'] : '';
		$street_2 = isset( $vendor_data['address']['street_2'] ) ? $vendor_data['address']['street_2'] : '';
		$city    = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
		$zip     = isset( $vendor_data['address']['zip'] ) ? $vendor_data['address']['zip'] : '';
		$country = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
		$state   = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
		
		// Location
		$store_location   = isset( $vendor_data['store_location'] ) ? esc_attr( $vendor_data['store_location'] ) : '';
		$map_address    = isset( $vendor_data['find_address'] ) ? esc_attr( $vendor_data['find_address'] ) : '';
		$store_lat    = isset( $vendor_data['store_lat'] ) ? esc_attr( $vendor_data['store_lat'] ) : 0;
		$store_lng    = isset( $vendor_data['store_lng'] ) ? esc_attr( $vendor_data['store_lng'] ) : 0;
		
		// Country -> States
		$country_obj   = new WC_Countries();
		$countries     = $country_obj->countries;
		$states        = $country_obj->states;
		$state_options = array();
		if( $state && isset( $states[$country] ) && is_array( $states[$country] ) ) {
			$state_options = $states[$country];
		}
		if( $state ) $state_options[$state] = $state;
		
		// Gravatar image
		$gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : '';
		
		// banner URL
		$banner_url = $banner ? wp_get_attachment_url( $banner ) : '';
		
		$store_banner_width = isset( $WCFMmp->wcfmmp_marketplace_options['store_banner_width'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_banner_width'] : '1650';
		$store_banner_height = isset( $WCFMmp->wcfmmp_marketplace_options['store_banner_height'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_banner_height'] : '350';
		$banner_help_text = sprintf(
				__('Upload a banner for your store. Banner size is (%sx%s) pixels.', 'wc-frontend-manager' ),
				$store_banner_width, $store_banner_height
		);
		
		// Shop Description
		$shop_description = wcfm_get_user_meta( $user_id, '_store_description', true );
		
		?>
		<h1><?php esc_html_e('Store setup', 'wc-multivendor-marketplace'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
					$settings_fields_general = apply_filters( 'wcfm_marketplace_settings_fields_general', array(
																																													"gravatar"    => array('label' => __('Store Logo', 'wc-frontend-manager') , 'type' => 'upload', 'in_table' => 'yes', 'name' => 'vendor_data[gravatar]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 150, 'value' => $gravatar_url ),
																																													"banner"      => array('label' => __('Banner', 'wc-frontend-manager') , 'type' => 'upload', 'in_table' => 'yes', 'name' => 'vendor_data[banner]', 'class' => 'wcfm-text wcfm_ele wcfm-banner-uploads', 'label_class' => 'wcfm_title', 'prwidth' => 250, 'value' => $banner_url, 'desc_class' => 'wcfm_page_options_desc', 'desc' => $banner_help_text ),
																																													"store_name"  => array('label' => __('Shop Name', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[store_name]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $store_name ),
																																													"store_email" => array('label' => __('Store Email', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[store_email]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $store_email ),
																																													"phone"       => array('label' => __('Store Phone', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[phone]', 'placeholder' => '+123456..', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $phone ),
																																													), $user_id );
					
					if( !apply_filters( 'wcfm_is_allow_store_logo', true ) ) {
						if( isset( $settings_fields_general['gravatar'] ) ) { unset( $settings_fields_general['gravatar'] ); }
					}
					
					if( !apply_filters( 'wcfm_is_allow_store_name', true ) ) {
						if( isset( $settings_fields_general['store_name'] ) ) { unset( $settings_fields_general['store_name'] ); }
					}
					
					if( !apply_filters( 'wcfm_is_allow_store_banner', true ) ) {
						if( isset( $settings_fields_general['banner'] ) ) { unset( $settings_fields_general['banner'] ); }
					}
					
					if( !apply_filters( 'wcfm_is_allow_store_email', true ) ) {
						if( isset( $settings_fields_general['store_email'] ) ) { unset( $settings_fields_general['store_email'] ); }
					}
					
					if( !apply_filters( 'wcfm_is_allow_store_phone', true ) ) {
						if( isset( $settings_fields_general['phone'] ) ) { unset( $settings_fields_general['phone'] ); }
					}
								
					$WCFM->wcfm_fields->wcfm_generate_form_field( $settings_fields_general );	
					
					if( apply_filters( 'wcfm_is_allow_store_address', true ) ) {
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_address', array(
																																															"street_1" => array('label' => __('Store Address 1', 'wc-multivendor-marketplace'), 'placeholder' => __('Street address', 'wc-frontend-manager'), 'name' => 'vendor_data[address][street_1]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $street_1 ),
																																															"street_2" => array('label' => __('Store Address 2', 'wc-multivendor-marketplace'), 'placeholder' => __('Apartment, suite, unit etc. (optional)', 'wc-frontend-manager'), 'name' => 'vendor_data[address][street_2]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $street_2 ),
																																															"city" => array('label' => __('Store City/Town', 'wc-multivendor-marketplace'), 'placeholder' => __('Town / City', 'wc-frontend-manager'), 'name' => 'vendor_data[address][city]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $city ),
																																															"zip" => array('label' => __('Store Postcode/Zip', 'wc-multivendor-marketplace'), 'placeholder' => __('Postcode / Zip', 'wc-frontend-manager'), 'name' => 'vendor_data[address][zip]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $zip, 'attributes' => array( 'min' => '1', 'step'=> '1' ) ),
																																															"country" => array('label' => __('Store Country', 'wc-multivendor-marketplace'), 'name' => 'vendor_data[address][country]', 'type' => 'country', 'in_table' => 'yes', 'wrapper_class' => 'store_address_wrap', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array(  'style' => 'width:100%' ), 'custom_attributes' => array( 'required' => true ), 'value' => $country ),
																																															"state" => array('label' => __('Store State/County', 'wc-multivendor-marketplace'), 'name' => 'vendor_data[address][state]', 'type' => 'select', 'in_table' => 'yes', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'custom_attributes' => array( 'required' => true ), 'attributes' => array(  'style' => 'width:100%' ),  'options' => $state_options, 'value' => $state ),
																																															), $user_id ) );
					
						$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
						$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
						if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
						if ( apply_filters( 'wcfm_is_allow_store_map_location', true ) && ( ( ($wcfm_map_lib == 'google') && !empty( $api_key ) ) || ($wcfm_map_lib == 'leaflet') ) ) {
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_location', array(
																																																				"find_address" => array( 'label' => __( 'Find Location', 'wc-frontend-manager' ), 'placeholder' => __( 'Type an address to find', 'wc-frontend-manager' ), 'name' => 'vendor_data[find_address]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $map_address ),
																																																				"withdrawal_setting_break_1" => array( 'label' => __('Store Location', 'wc-multivendor-marketplace'), 'type' => 'html', 'in_table' => 'yes', 'value' => '<div class="wcfm-marketplace-google-map" id="wcfm-marketplace-map"></div>' ),
																																																				"store_location" => array( 'type' => 'hidden', 'name' => 'vendor_data[store_location]', 'in_table' => 'yes', 'value' => $store_location ),
																																																				"store_lat" => array( 'type' => 'hidden', 'name' => 'vendor_data[store_lat]', 'in_table' => 'yes', 'value' => $store_lat ),
																																																				"store_lng" => array( 'type' => 'hidden', 'name' => 'vendor_data[store_lng]', 'in_table' => 'yes', 'value' => $store_lng ),
																																																				), $user_id ) );
						}
					}
				?>
			</table>
			<?php
			if( apply_filters( 'wcfm_is_allow_store_description', true ) ) {
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_brand', array(
					"shop_description" => array('label' => __('Shop Description', 'wc-frontend-manager') , 'type' => 'wpeditor', 'class' => 'wcfm-textarea wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title', 'value' => $shop_description, 'hints' => __( 'This is displayed on your shop page.', 'wc-frontend-manager' ) ),
					), $user_id ) );
			}
			?>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next wcfm_submit_button" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<?php if( apply_filters( 'wcfm_is_allow_store_setup_step_skip', true ) ) { ?>
				  <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php } ?>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<script type="text/javascript">
			var selected_state = '<?php echo $state; ?>';
			var input_selected_state = '<?php echo $state; ?>';
		</script>
		<?php
	}
	
	/**
	 * Payment setup content
	 */
	public function wcfmmp_store_setup_payment() {
		global $WCFM, $WCFMmp;
		
		$user_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		$the_user    = get_user_by( 'id', $user_id );
		
		$store_name     = isset( $vendor_data['store_name'] ) ? esc_attr( $vendor_data['store_name'] ) : '';
		$store_name     = empty( $store_name ) ? $the_user->display_name : $store_name;
		
		// Payment
		$payment_mode = isset( $vendor_data['payment']['method'] ) ? esc_attr( $vendor_data['payment']['method'] ) : '' ;
		$paypal = isset( $vendor_data['payment']['paypal']['email'] ) ? esc_attr( $vendor_data['payment']['paypal']['email'] ) : '' ;
		$skrill = isset( $vendor_data['payment']['skrill']['email'] ) ? esc_attr( $vendor_data['payment']['skrill']['email'] ) : '' ;
		$ac_name   = isset( $vendor_data['payment']['bank']['ac_name'] ) ? esc_attr( $vendor_data['payment']['bank']['ac_name'] ) : '';
		$ac_number = isset( $vendor_data['payment']['bank']['ac_number'] ) ? esc_attr( $vendor_data['payment']['bank']['ac_number'] ) : '';
		$bank_name      = isset( $vendor_data['payment']['bank']['bank_name'] ) ? esc_attr( $vendor_data['payment']['bank']['bank_name'] ) : '';
		$bank_addr      = isset( $vendor_data['payment']['bank']['bank_addr'] ) ? esc_textarea( $vendor_data['payment']['bank']['bank_addr'] ) : '';
		$routing_number = isset( $vendor_data['payment']['bank']['routing_number'] ) ? esc_attr( $vendor_data['payment']['bank']['routing_number'] ) : '';
		$iban           = isset( $vendor_data['payment']['bank']['iban'] ) ? esc_attr( $vendor_data['payment']['bank']['iban'] ) : '';
		$swift     = isset( $vendor_data['payment']['bank']['swift'] ) ? esc_attr( $vendor_data['payment']['bank']['swift'] ) : '';
		$ifsc     = isset( $vendor_data['payment']['bank']['ifsc'] ) ? esc_attr( $vendor_data['payment']['bank']['ifsc'] ) : '';
		
		?>
		<h1><?php esc_html_e('Payment setup', 'wc-multivendor-marketplace'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
					$wcfm_marketplace_withdrwal_payment_methods = get_wcfm_marketplace_active_withdrwal_payment_methods();
					if( isset( $wcfm_marketplace_withdrwal_payment_methods['stripe_split'] ) ) unset( $wcfm_marketplace_withdrwal_payment_methods['stripe_split'] );
					$wcfmmp_settings_fields_billing = apply_filters( 'wcfm_marketplace_settings_fields_billing', array(
																																													"payment_mode" => array('label' => __('Preferred Payment Method', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][method]', 'type' => 'select', 'in_table' => 'yes', 'options' => $wcfm_marketplace_withdrwal_payment_methods, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $payment_mode ),
																																													"paypal" => array('label' => __('PayPal Email', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][paypal][email]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_paypal', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_paypal', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_paypal', 'value' => $paypal ),
																																													"skrill" => array('label' => __('Skrill Email', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][skrill][email]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_skrill', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_skrill', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_skrill', 'value' => $skrill ),
																																													), $user_id );
					
					$WCFM->wcfm_fields->wcfm_generate_form_field( $wcfmmp_settings_fields_billing );
					
					if( in_array( 'bank_transfer', array_keys( $wcfm_marketplace_withdrwal_payment_methods ) ) ) {
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_billing_bank', array(
																																"ac_name" => array('label' => __('Account Name', 'wc-frontend-manager'), 'placeholder' => __('Your bank account name', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][bank][ac_name]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $ac_name ),
																																"ac_number" => array('label' => __('Account Number', 'wc-frontend-manager'), 'placeholder' => __('Your bank account number', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][bank][ac_number]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $ac_number ),
																																"bank_name" => array('label' => __('Bank Name', 'wc-frontend-manager'), 'placeholder' => __('Name of bank', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][bank][bank_name]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $bank_name ),
																																"bank_addr" => array('label' => __('Bank Address', 'wc-frontend-manager'), 'placeholder' => __('Address of your bank', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][bank][bank_addr]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $bank_addr ),
																																"routing_number" => array('label' => __('Routing Number', 'wc-frontend-manager'), 'placeholder' => __( 'Routing number', 'wc-frontend-manager' ), 'name' => 'vendor_data[payment][bank][routing_number]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $routing_number ),
																																"iban" => array('label' => __('IBAN', 'wc-frontend-manager'), 'placeholder' => __('IBAN', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][bank][iban]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $iban ),
																																"swift" => array('label' => __('Swift Code', 'wc-frontend-manager'), 'placeholder' => __('Swift code', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][bank][swift]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $swift ),
																																"ifsc" => array('label' => __('IFSC Code', 'wc-frontend-manager'), 'placeholder' => __('IFSC code', 'wc-frontend-manager'), 'name' => 'vendor_data[payment][bank][ifsc]', 'type' => 'text', 'in_table' => 'yes', 'wrapper_class' => 'paymode_field paymode_bank_transfer', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_bank_transfer', 'value' => $ifsc ),
																																), $user_id ) );
					}
				?>
			</table>
			
			<?php if( array_key_exists( 'stripe', $wcfm_marketplace_withdrwal_payment_methods ) && apply_filters( 'wcfm_is_allow_billing_stripe', true ) ) { ?>
				<div class="paymode_field paymode_stripe">
					<?php
					$testmode = isset( $WCFMmp->wcfmmp_withdrawal_options['test_mode'] ) ? true : false;
					$client_id = $testmode ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_client_id'] : $WCFMmp->wcfmmp_withdrawal_options['stripe_client_id'];
					$secret_key = $testmode ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_secret_key'] : $WCFMmp->wcfmmp_withdrawal_options['stripe_secret_key'];
					if (isset($client_id) && isset($secret_key)) {
						
						$is_stripe_connected = false;
						$stripe_user_id = get_user_meta( $user_id, 'stripe_user_id', true );
						$vendor_connected = get_user_meta( $user_id, 'vendor_connected', true );
						if( $stripe_user_id && $vendor_connected ) {
							$is_stripe_connected = true;
						}
						
						if (isset($_GET['code'])) {
							$code = sanitize_text_field($_GET['code']);
							
							$token_request_body = array(
								'grant_type' => 'authorization_code',
								'client_id' => $client_id,
								'code' => $code,
								'client_secret' => $secret_key
							);
							$req = curl_init('https://connect.stripe.com/oauth/token');
							curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($req, CURLOPT_POST, true);
							curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));
							curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
							curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 2);
							curl_setopt($req, CURLOPT_VERBOSE, true);
							// TODO: Additional error handling
							$respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
							$resp = json_decode(curl_exec($req), true);
							curl_close($req);
							if (!isset($resp['error'])) {
								update_user_meta( $user_id, 'vendor_connected', 1);
								update_user_meta( $user_id, 'admin_client_id', $client_id);
								update_user_meta( $user_id, 'access_token', $resp['access_token']);
								update_user_meta( $user_id, 'refresh_token', $resp['refresh_token']);
								update_user_meta( $user_id, 'stripe_publishable_key', $resp['stripe_publishable_key']);
								update_user_meta( $user_id, 'stripe_user_id', $resp['stripe_user_id']);
								$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
								$vendor_data['payment']['method'] = 'stripe';
								update_user_meta( $user_id, 'wcfmmp_profile_settings', $vendor_data );
								?>
								<script>
									window.location =  '<?php echo add_query_arg( array( 'store-setup' => 'yes', 'step' => 'payment' ), home_url() ); ?>';
								</script>
								<?php
							}
						}
						
						if ( get_user_meta($user_id, 'vendor_connected', true) == 1 ) {
							?>
							<div class="clear"></div>
							<div class="wcfmmp_stripe_connect">
								<table class="form-table">
									<tbody>
										<tr>
											<th style="width: 35%;">
												<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
											</th>
											<td>
												<label><?php _e('You are connected with Stripe', 'wc-frontend-manager'); ?></label>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						<?php 
							$is_stripe_connected = true;
						}
						
						if( !$is_stripe_connected ) {
						
							$user_email = $the_user->user_email;
							
							// Show OAuth link
							$authorize_request_body = apply_filters( 'wcfm_stripe_authorize_request_params', array(
																												'response_type' => 'code',
																												'scope' => 'read_write',
																												'client_id' => $client_id,
																												'redirect_uri' => add_query_arg( array( 'store-setup' => 'yes', 'step' => 'payment' ), home_url() ),
																												'state' => $user_id,
																												'stripe_user' => array( 
																																							'email'         => $user_email,
																																							'url'           => wcfmmp_get_store_url( $user_id ),
																																							'business_name' => $store_name,
																																							'first_name'    => $the_user->first_name,
																																							'last_name'     => $the_user->last_name
																																							)
																											), $user_id );
							if( apply_filters( 'wcfm_is_allow_stripe_express_api', true ) ) {
								$authorize_request_body['suggested_capabilities'] = array( 'transfers', 'card_payments' );
								$url = 'https://connect.stripe.com/express/oauth/authorize?' . http_build_query($authorize_request_body);
							} else {
								$url = 'https://connect.stripe.com/oauth/authorize?' . http_build_query($authorize_request_body);
							}
							$stripe_connect_url = $WCFM->plugin_url . 'assets/images/blue-on-light.png';
							
							?>
							<div class="clear"></div>
							<div class="wcfmmp_stripe_connect">
								<table class="form-table">
									<tbody>
										<tr>
											<th style="width: 35%;">
												<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
											</th>
											<td><?php _e('You are not connected with stripe.', 'wc-frontend-manager'); ?></td>
										</tr>
										<tr>
											<th></th>
											<td>
												<a href=<?php echo $url; ?> target="_self"><img src="<?php echo $stripe_connect_url; ?>" /></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						<?php
						}
					}
					?>
				</div>
			<?php } ?>
			
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next wcfm_submit_button" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<?php if( apply_filters( 'wcfm_is_allow_store_setup_step_skip', true ) ) { ?>
					<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php } ?>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}
	
	/**
	 * Policy setup content
	 */
	public function wcfmmp_store_setup_policy() {
		global $WCFM, $WCFMmp;
		
		$user_id = $WCFMmp->vendor_id;

		$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $user_id, 'wcfm_policy_vendor_options', true );
		
		$_wcfm_vendor_policy_tab_title = isset( $wcfm_policy_vendor_options['policy_tab_title'] ) ? $wcfm_policy_vendor_options['policy_tab_title'] : '';
		$_wcfm_vendor_shipping_policy = isset( $wcfm_policy_vendor_options['shipping_policy'] ) ? $wcfm_policy_vendor_options['shipping_policy'] : '';
		$_wcfm_vendor_refund_policy = isset( $wcfm_policy_vendor_options['refund_policy'] ) ? $wcfm_policy_vendor_options['refund_policy'] : '';
		$_wcfm_vendor_cancellation_policy = isset( $wcfm_policy_vendor_options['cancellation_policy'] ) ? $wcfm_policy_vendor_options['cancellation_policy'] : '';
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_policy_tab_title = isset( $wcfm_policy_options['policy_tab_title'] ) ? $wcfm_policy_options['policy_tab_title'] : '';
		if( wcfm_empty($_wcfm_vendor_policy_tab_title) ) $_wcfm_vendor_policy_tab_title = $_wcfm_policy_tab_title;
		$_wcfm_shipping_policy = isset( $wcfm_policy_options['shipping_policy'] ) ? $wcfm_policy_options['shipping_policy'] : '';
		if( wcfm_empty($_wcfm_vendor_shipping_policy) ) $_wcfm_vendor_shipping_policy = wcfm_strip_html( $_wcfm_shipping_policy );
		$_wcfm_refund_policy = isset( $wcfm_policy_options['refund_policy'] ) ? $wcfm_policy_options['refund_policy'] : '';
		if( wcfm_empty($_wcfm_vendor_refund_policy) ) $_wcfm_vendor_refund_policy = wcfm_strip_html( $_wcfm_refund_policy );
		$_wcfm_cancellation_policy = isset( $wcfm_policy_options['cancellation_policy'] ) ? $wcfm_policy_options['cancellation_policy'] : '';
		if( wcfm_empty($_wcfm_vendor_cancellation_policy) ) $_wcfm_vendor_cancellation_policy = wcfm_strip_html( $_wcfm_cancellation_policy );
		
		?>
		<h1><?php esc_html_e('Policy setup', 'wc-multivendor-marketplace'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendor_settings_fields_policies', array(
					                                                                        "wcfm_policy_tab_title" => array('label' => __('Policy Tab Label', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $_wcfm_vendor_policy_tab_title )
					                                                          ), $user_id ) );   
				?>
			</table>
			
			<?php
			$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
			$wpeditor = apply_filters( 'wcfm_is_allow_product_wpeditor', 'wpeditor' );
			if( $wpeditor && $rich_editor ) {
				$rich_editor = 'wcfm_wpeditor';
			} else {
				$wpeditor = 'textarea';
			}
			$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendor_settings_fields_policies', array(
																																							"wcfm_shipping_policy" => array('label' => __('Shipping Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title', 'value' => $_wcfm_vendor_shipping_policy ),
																																							"wcfm_refund_policy" => array('label' => __('Refund Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title', 'value' => $_wcfm_vendor_refund_policy ),
																																							"wcfm_cancellation_policy" => array('label' => __('Cancellation/Return/Exchange Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_title', 'value' => $_wcfm_vendor_cancellation_policy ),
																																							), $user_id ) );
			?>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next wcfm_submit_button" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<?php if( apply_filters( 'wcfm_is_allow_store_setup_step_skip', true ) ) { ?>
					<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php } ?>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}
	
	/**
	 * Support setup content
	 */
	public function wcfmmp_store_setup_customer_support() {
		global $WCFM, $WCFMmp;
		
		$user_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		// Customer Support
		$vendor_customer_phone = isset( $vendor_data['customer_support']['phone'] ) ? $vendor_data['customer_support']['phone'] : '';
		$vendor_customer_email = isset( $vendor_data['customer_support']['email'] ) ? $vendor_data['customer_support']['email'] : '';
		$vendor_csd_return_address1 = isset( $vendor_data['customer_support']['address1'] ) ? $vendor_data['customer_support']['address1'] : '';
		$vendor_csd_return_address2 = isset( $vendor_data['customer_support']['address2'] ) ? $vendor_data['customer_support']['address2'] : '';
		$vendor_csd_return_country = isset( $vendor_data['customer_support']['country'] ) ? $vendor_data['customer_support']['country'] : '';
		$vendor_csd_return_city = isset( $vendor_data['customer_support']['city'] ) ? $vendor_data['customer_support']['city'] : '';
		$vendor_csd_return_state = isset( $vendor_data['customer_support']['state'] ) ? $vendor_data['customer_support']['state'] : '';
		$vendor_csd_return_zip = isset( $vendor_data['customer_support']['zip'] ) ? $vendor_data['customer_support']['zip'] : '';
		
		// Country -> States
		$country_obj   = new WC_Countries();
		$countries     = $country_obj->countries;
		$states        = $country_obj->states;
		$state_options = array();
		if( $vendor_csd_return_state && isset( $states[$vendor_csd_return_country] ) && is_array( $states[$vendor_csd_return_country] ) ) {
			$state_options = $states[$vendor_csd_return_country];
		}
		if( $vendor_csd_return_state ) $state_options[$vendor_csd_return_state] = $vendor_csd_return_state;
		
		?>
		<h1><?php esc_html_e('Support setup', 'wc-multivendor-marketplace'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcmarketplace_settings_fields_customer_support', array(
																																																	"vendor_customer_phone" => array('label' => __('Phone', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][phone]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_customer_phone ),
																																																	"vendor_customer_email" => array('label' => __('Email', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][email]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_customer_email ),
																																																	"vendor_csd_return_address1" => array('label' => __('Address 1', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][address1]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_address1 ),
																																																	"vendor_csd_return_address2" => array('label' => __('Address 2', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][address2]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_address2 ),
																																																	"vendor_csd_return_country" => array('label' => __('Country', 'wc-frontend-manager') , 'type' => 'country', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][country]', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'wrapper_class' => 'customer_support_address_wrap', 'value' => $vendor_csd_return_country ),
																																																	"vendor_csd_return_city" => array('label' => __('City/Town', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][city]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_city ),
																																																	"vendor_csd_return_state" => array('label' => __('State/County', 'wc-frontend-manager') , 'type' => 'select', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][state]', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => $state_options, 'value' => $vendor_csd_return_state ),
																																																	"vendor_csd_return_zip" => array('label' => __('Postcode/Zip', 'wc-frontend-manager') , 'type' => 'text', 'in_table' => 'yes', 'name' => 'vendor_data[customer_support][zip]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_zip, 'attributes' => array( 'min' => '1', 'step'=> '1' ) )
																																																	), $user_id ) );
					
				?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next wcfm_submit_button" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<?php if( apply_filters( 'wcfm_is_allow_store_setup_step_skip', true ) ) { ?>
					<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php } ?>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<script type="text/javascript">
			var csd_selected_state = '<?php echo $vendor_csd_return_state; ?>';
			var input_csd_state = '<?php echo $vendor_csd_return_state; ?>';
		</script>
		<?php
	}
	
	/**
	 * SEO setup content
	 */
	public function wcfmmp_store_setup_seo() {
		global $WCFM, $WCFMmp;
		
		$vendor_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
		
		// SEO
		$wcfmmp_seo_meta_title        = isset( $vendor_data['store_seo']['wcfmmp-seo-meta-title'] ) ? $vendor_data['store_seo']['wcfmmp-seo-meta-title'] : '';
		$wcfmmp_seo_meta_desc         = isset( $vendor_data['store_seo']['wcfmmp-seo-meta-desc'] ) ? $vendor_data['store_seo']['wcfmmp-seo-meta-desc'] : '';
		$wcfmmp_seo_meta_keywords     = isset( $vendor_data['store_seo']['wcfmmp-seo-meta-keywords'] ) ? $vendor_data['store_seo']['wcfmmp-seo-meta-keywords'] : '';
		$wcfmmp_seo_og_title          = isset( $vendor_data['store_seo']['wcfmmp-seo-og-title'] ) ? $vendor_data['store_seo']['wcfmmp-seo-og-title'] : '';
		$wcfmmp_seo_og_desc           = isset( $vendor_data['store_seo']['wcfmmp-seo-og-desc'] ) ? $vendor_data['store_seo']['wcfmmp-seo-og-desc'] : '';
		$wcfmmp_seo_og_image          = isset( $vendor_data['store_seo']['wcfmmp-seo-og-image'] ) ? $vendor_data['store_seo']['wcfmmp-seo-og-image'] : 0;
		$wcfmmp_seo_twitter_title     = isset( $vendor_data['store_seo']['wcfmmp-seo-twitter-title'] ) ? $vendor_data['store_seo']['wcfmmp-seo-twitter-title'] : '';
		$wcfmmp_seo_twitter_desc      = isset( $vendor_data['store_seo']['wcfmmp-seo-twitter-desc'] ) ? $vendor_data['store_seo']['wcfmmp-seo-twitter-desc'] : '';
		$wcfmmp_seo_twitter_image     = isset( $vendor_data['store_seo']['wcfmmp-seo-twitter-image'] ) ? $vendor_data['store_seo']['wcfmmp-seo-twitter-image'] : 0;
		
		// Facebook image
		$wcfmmp_seo_og_image_url      = $wcfmmp_seo_og_image ? wp_get_attachment_thumb_url( $wcfmmp_seo_og_image ) : '';
		
		// Twitter URL
		$wcfmmp_seo_twitter_image_url = $wcfmmp_seo_twitter_image ? wp_get_attachment_thumb_url( $wcfmmp_seo_twitter_image ) : '';
		
		?>
		<h1><?php esc_html_e('Store SEO setup', 'wc-multivendor-marketplace'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_seo', array(
																																			"wcfmmp-seo-meta-title" => array('label' => __('SEO Title', 'wc-frontend-manager') , 'name' => 'vendor_data[store_seo][wcfmmp-seo-meta-title]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_meta_title, 'hints' => __('SEO Title is shown as the title of your store page', 'wc-frontend-manager') ),
																																			"wcfmmp-seo-meta-desc" => array('label' => __('Meta Description', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-meta-desc]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_meta_desc, 'hints' => __('The meta description is often shown as the black text under the title in a search result. For this to work it has to contain the keyword that was searched for and should be less than 156 chars.', 'wc-frontend-manager') ),
																																			"wcfmmp-seo-meta-keywords" => array('label' => __('Meta Keywords', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-meta-keywords]', 'type' => 'textarea', 'in_table' => 'yes', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_meta_keywords, 'hints' => __('Insert some comma separated keywords for better ranking of your store page.', 'wc-frontend-manager') ),
																																		 ), $vendor_id ) );
					
				?>
			</table>
			
			<?php if( apply_filters( 'wcfm_is_allow_vendor_seo_facebook', true ) ) { ?>
				<div class="wcfm_clearfix"></div><br />
				<div class=""><h1><?php _e( 'Facebook Setup', 'wc-frontend-manager' ); ?></h1></div>
				<div class="wcfm_clearfix"></div>
				<table class="form-table">
					<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_seo_facebook', array(
																																				"wcfmmp-seo-og-title" => array('label' => __('Facebook Title', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-og-title]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_og_title ),
																																				"wcfmmp-seo-og-desc" => array('label' => __('Facebook Description', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-og-desc]', 'type' => 'textarea', 'in_table' => 'yes', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_og_desc ),
																																				"wcfmmp-seo-og-image" => array('label' => __('Facebook Image', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-og-image]', 'type' => 'upload', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_og_image_url ),
																																			 ), $vendor_id ) );
						
					?>
				</table>
			<?php } ?>
			
			<?php if( apply_filters( 'wcfm_is_allow_vendor_seo_twitter', true ) ) { ?>
				<div class="wcfm_clearfix"></div><br />
				<div class=""><h1><?php _e( 'Twitter Setup', 'wc-frontend-manager' ); ?></h1></div>
				<div class="wcfm_clearfix"></div>
				<table class="form-table">
					<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_seo_twitter', array(
																																				"wcfmmp-seo-twitter-title" => array('label' => __('Twitter Title', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-twitter-title]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_twitter_title ),
																																				"wcfmmp-seo-twitter-desc" => array('label' => __('Twitter Description', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-twitter-desc]', 'type' => 'textarea', 'in_table' => 'yes', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_twitter_desc ),
																																				"wcfmmp-seo-twitter-image" => array('label' => __('Twitter Image', 'wc-frontend-manager'), 'name' => 'vendor_data[store_seo][wcfmmp-seo-twitter-image]', 'type' => 'upload', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfmmp_seo_twitter_image_url ),
																																			 ), $vendor_id ) );
						
					?>
				</table>
			<?php } ?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next wcfm_submit_button" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<?php if( apply_filters( 'wcfm_is_allow_store_setup_step_skip', true ) ) { ?>
					<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php } ?>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}
	
	/**
	 * Social setup content
	 */
	public function wcfmmp_store_setup_social() {
		global $WCFM, $WCFMmp;
		
		$vendor_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
		
		// Social
		$social_fields = isset( $vendor_data['social'] ) ? $vendor_data['social'] : array();
		$twitter       = isset( $social_fields['twitter'] ) ? $social_fields['twitter'] : '';
		$facebook      = isset( $social_fields['fb'] ) ? $social_fields['fb'] : '';
		$instagram     = isset( $social_fields['instagram'] ) ? $social_fields['instagram'] : '';
		$youtube       = isset( $social_fields['youtube'] ) ? $social_fields['youtube'] : '';
		$linkdin       = isset( $social_fields['linkedin'] ) ? $social_fields['linkedin'] : '';
		$google_plus   = isset( $social_fields['gplus'] ) ? $social_fields['gplus'] : '';
		$snapchat      = isset( $social_fields['snapchat'] ) ? $social_fields['snapchat'] : '';
		$pinterest     = isset( $social_fields['pinterest'] ) ? $social_fields['pinterest'] : '';
		
		?>
		<h1><?php esc_html_e('Store Social setup', 'wc-multivendor-marketplace'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_profile_fields_social', array(  
																																				"twitter" => array('label' => __('Twitter', 'wc-frontend-manager'), 'name' => 'vendor_data[social][twitter]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $twitter ),
																																				"facebook" => array('label' => __('Facebook', 'wc-frontend-manager'), 'name' => 'vendor_data[social][fb]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $facebook ),
																																				"instagram" => array('label' => __('Instagram', 'wc-frontend-manager'), 'name' => 'vendor_data[social][instagram]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $instagram ),
																																				"youtube" => array('label' => __('Youtube', 'wc-frontend-manager'), 'name' => 'vendor_data[social][youtube]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $youtube ),
																																				"linkdin" => array('label' => __('Linkedin', 'wc-frontend-manager'),  'name' => 'vendor_data[social][linkedin]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $linkdin ),
																																				"google_plus" => array('label' => __('Google Plus', 'wc-frontend-manager'),  'name' => 'vendor_data[social][gplus]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $google_plus ),
																																				"snapchat" => array('label' => __('Snapchat', 'wc-frontend-manager'), 'name' => 'vendor_data[social][snapchat]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $snapchat ),
																																				"pinterest" => array('label' => __('Pinterest', 'wc-frontend-manager'), 'name' => 'vendor_data[social][pinterest]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $pinterest ),
																																				), $vendor_id ) );
				?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next wcfm_submit_button" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<?php if( apply_filters( 'wcfm_is_allow_store_setup_step_skip', true ) ) { ?>
					<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php } ?>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}
	
	/**
	 * Ready to go content
	 */
	public function wcfmmp_store_setup_ready() {
		global $WCFM;
		?>
		<h1><?php esc_html_e( apply_filters( 'wcfm_store_setup_complete_heading', __( 'We are done!', 'wc-frontend-manager' ) ) ); ?></h1>
		<div class="woocommerce-message-wcfm woocommerce-tracker">
		<p><?php echo esc_html( apply_filters( 'wcfm_store_setup_complete_message', __( "Your store is ready. It's time to experience the things more Easily and Peacefully. Add your products and start counting sales, have fun!!", 'wc-multivendor-marketplace' ) ) ); ?></p>
		</div>
		<div class="wc-setup-next-steps">
		  <p class="wc-setup-actions step">
			  <a class="button button-primary button-large wcfm_submit_button" href="<?php echo apply_filters( 'wcfm_store_setup_complete_button_url', esc_url( get_wcfm_url() ) ); ?>"><?php esc_html_e( apply_filters( 'wcfm_store_setup_complete_button', __( "Let's go to the Dashboard", 'wc-frontend-manager' ) ) ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Save store settings
	 */
	public function wcfmmp_store_setup_store_save() {
		global $WCFM, $WCFMmp;
		
		check_admin_referer('wcfm-setup');
		
		$user_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		$wcfm_setup_data = wp_unslash( $_POST['vendor_data'] );
		
		// Set Gravatar
		if( apply_filters( 'wcfm_is_allow_store_logo', true ) ) {
			if(isset($wcfm_setup_data['gravatar']) && !empty($wcfm_setup_data['gravatar'])) {
				$wcfm_setup_data['gravatar'] = $WCFM->wcfm_get_attachment_id($wcfm_setup_data['gravatar']);
			} else {
				$wcfm_setup_data['gravatar'] = '';
			}
		}
		
		// Set Banner
		if( apply_filters( 'wcfm_is_allow_store_banner', true ) ) {
			if(isset($wcfm_setup_data['banner']) && !empty($wcfm_setup_data['banner'])) {
				$wcfm_setup_data['banner'] = $WCFM->wcfm_get_attachment_id($wcfm_setup_data['banner']);
			} else {
				$wcfm_setup_data['banner'] = '';
			}
		}
		
		if( isset( $_POST['address'] ) && isset( $_POST['address']['state'] ) ) {
			$wcfm_setup_data['address']['state'] = sanitize_text_field($_POST['address']['state']);
		}
		
		// merge the changes with existing settings
		if( $vendor_data ) {
			$wcfm_setup_data = array_merge( $vendor_data, $wcfm_setup_data );
		}
		
		// Save Store Address as User Meta
		if( isset( $wcfm_setup_data['address'] ) ) {
			foreach( $wcfm_setup_data['address'] as $address_field => $address_val ) {
				update_user_meta( $user_id, '_wcfm_' . $address_field, $address_val );
			}
		}
		
		// Save Store Name
		if(isset($wcfm_setup_data['store_name']) && !empty($wcfm_setup_data['store_name'])) {
			update_user_meta( $user_id, 'store_name', $wcfm_setup_data['store_name'] );
			update_user_meta( $user_id, 'wcfmmp_store_name', $wcfm_setup_data['store_name'] );
		}
		
		update_user_meta( $user_id, 'wcfmmp_profile_settings', $wcfm_setup_data );
		
		if( isset( $_POST['shop_description'] ) ) {
			$shop_description = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['shop_description'], ENT_QUOTES, 'UTF-8' ) ) );
			update_user_meta( $user_id, '_store_description', $shop_description );
		}
		
		do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_setup_data );
		do_action( 'wcfm_wcfmmp_settings_update', $user_id, $wcfm_setup_data );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save payment settings
	 */
	public function wcfmmp_store_setup_payment_save() {
		global $WCFM, $WCFMmp;
		
		check_admin_referer('wcfm-setup');
		
		$user_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		$wcfm_setup_data = wp_unslash( $_POST['vendor_data'] );
		
		// merge the changes with existing settings
		$wcfm_setup_data = array_merge( $vendor_data, $wcfm_setup_data );
		
		update_user_meta( $user_id, 'wcfmmp_profile_settings', $wcfm_setup_data );
		
		do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_setup_data );
		do_action( 'wcfm_wcfmmp_settings_update', $user_id, $wcfm_setup_data );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save policy settings
	 */
	public function wcfmmp_store_setup_policy_save() {
		global $WCFM, $WCFMmp;
		
		check_admin_referer('wcfm-setup');
		
		$user_id = $WCFMmp->vendor_id;

		$wcfm_policy_vendor_options = (array) get_user_meta( $user_id, 'wcfm_policy_vendor_options', true );
		
		if( isset( $_POST['wcfm_policy_tab_title'] ) ) {
			$wcfm_policy_vendor_options['policy_tab_title'] = sanitize_text_field( $_POST['wcfm_policy_tab_title'] );
		}
		
		if( isset( $_POST['wcfm_shipping_policy'] ) ) {
			$wcfm_policy_vendor_options['shipping_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['wcfm_shipping_policy'], ENT_QUOTES, 'UTF-8' ) ) );
		}
		
		if( isset( $_POST['wcfm_refund_policy'] ) ) {
			$wcfm_policy_vendor_options['refund_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['wcfm_refund_policy'], ENT_QUOTES, 'UTF-8' ) ) );
		}
		
		if( isset( $_POST['wcfm_cancellation_policy'] ) ) {
			$wcfm_policy_vendor_options['cancellation_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['wcfm_cancellation_policy'], ENT_QUOTES, 'UTF-8' ) ) );
		}
		
		update_user_meta( $user_id, 'wcfm_policy_vendor_options', $wcfm_policy_vendor_options );
		
		//do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_setup_data );
		//do_action( 'wcfm_wcfmmp_settings_update', $user_id, $wcfm_setup_data );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save customer support settings
	 */
	public function wcfmmp_store_setup_customer_support_save() {
		global $WCFM, $WCFMmp;
		
		check_admin_referer('wcfm-setup');
		
		$user_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		$wcfm_setup_data = wp_unslash( $_POST['vendor_data'] );
		
		if( isset( $_POST['customer_support'] ) && isset( $_POST['customer_support']['state'] ) ) {
			$wcfm_setup_data['customer_support']['state'] = sanitize_text_field($_POST['customer_support']['state']);
		}
		
		// merge the changes with existing settings
		$wcfm_setup_data = array_merge( $vendor_data, $wcfm_setup_data );
		
		update_user_meta( $user_id, 'wcfmmp_profile_settings', $wcfm_setup_data );
		
		do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_setup_data );
		do_action( 'wcfm_wcfmmp_settings_update', $user_id, $wcfm_setup_data );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save seo settings
	 */
	public function wcfmmp_store_setup_seo_save() {
		global $WCFM, $WCFMmp;
		
		check_admin_referer('wcfm-setup');
		
		$user_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		$wcfm_setup_data = wp_unslash( $_POST['vendor_data'] );
		
		if( !empty($wcfm_setup_data['store_seo']['wcfmmp-seo-og-image']) ) {
			$wcfm_setup_data['store_seo']['wcfmmp-seo-og-image'] = $WCFM->wcfm_get_attachment_id($wcfm_setup_data['store_seo']['wcfmmp-seo-og-image']);
		} else {
			$wcfm_setup_data['store_seo']['wcfmmp-seo-og-image'] = '';
		}
		
		if( !empty($wcfm_setup_data['store_seo']['wcfmmp-seo-twitter-image']) ) {
			$wcfm_setup_data['store_seo']['wcfmmp-seo-twitter-image'] = $WCFM->wcfm_get_attachment_id($wcfm_setup_data['store_seo']['wcfmmp-seo-twitter-image']);
		} else {
			$wcfm_setup_data['store_seo']['wcfmmp-seo-twitter-image'] = '';
		}
		
		// merge the changes with existing settings
		$wcfm_setup_data = array_merge( $vendor_data, $wcfm_setup_data );
		
		update_user_meta( $user_id, 'wcfmmp_profile_settings', $wcfm_setup_data );
		
		wcfm_update_user_meta( $user_id, 'wcfm_seo_vendor_options', $wcfm_setup_data['store_seo'] );
		
		do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_setup_data );
		do_action( 'wcfm_wcfmmp_settings_update', $user_id, $wcfm_setup_data );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save social settings
	 */
	public function wcfmmp_store_setup_social_save() {
		global $WCFM, $WCFMmp;
		
		check_admin_referer('wcfm-setup');
		
		$user_id = $WCFMmp->vendor_id;

		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		$wcfm_setup_data = wp_unslash( $_POST['vendor_data'] );
		
		// merge the changes with existing settings
		$wcfm_setup_data = array_merge( $vendor_data, $wcfm_setup_data );
		
		update_user_meta( $user_id, 'wcfmmp_profile_settings', $wcfm_setup_data );
		
		do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_setup_data );
		do_action( 'wcfm_wcfmmp_settings_update', $user_id, $wcfm_setup_data );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Setup Wizard Footer.
	 */
	public function wcfmmp_store_setup_footer() {
				if ('next_steps' === $this->step) :
				  if( apply_filters( 'wcfm_is_pref_knowledgebase', true ) && apply_filters( 'wcfm_is_allow_knowledgebase', true ) ) {
					?>
					<a target="_blank" class="wc-return-to-dashboard" href="<?php echo esc_url(get_wcfm_knowledgebase_url()); ?>"><?php esc_html_e('How to use dashboard?', 'wc-multivendor-marketplace'); ?></a>
					<?php }
					endif; ?>
			</body>
			<?php 
			wp_footer();
			?>
	</html>
	<?php
	}
}

new WCFMmp_Store_Setup();