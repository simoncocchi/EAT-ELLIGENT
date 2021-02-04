<?php
/**
 * WCFM Dashboard Setup Class
 * 
 * @since 3.1.3
 * @package wcfm/helpers
 * @author WC Lovers
 */
if (!defined('ABSPATH')) {
    exit;
}

class WCFM_Dashboard_Setup {

	/** @var string Currenct Step */
	private $step = '';

	/** @var array Steps for the setup wizard */
	private $steps = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wcfm_admin_menus' ) );
		add_action( 'admin_init', array( $this, 'wcfm_dashboard_setup' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function wcfm_admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wcfm-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function wcfm_dashboard_setup() {
		global $WCFM;
		if ( filter_input(INPUT_GET, 'page') != 'wcfm-setup') {
			return;
		}

		if ( isset($_POST['wcfm_install_wcfmmp']) ) {
			$this->install_wcfmmp();
			exit();
		}
		if ( isset($_POST['wcfm_install_wcfmvm']) ) {
			$this->install_wcfm_registration();
			exit();
		}
		$default_steps = array(
				'introduction' => array(
					'name' => __('Introduction', 'wc-frontend-manager' ),
					'view' => array($this, 'wcfm_setup_introduction'),
					'handler' => '',
				),
				'dashboard' => array(
					'name' => __('Dashboard Setup', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_dashboard'),
					'handler' => array($this, 'wcfm_setup_dashboard_save')
				),
				'marketplace' => array(
					'name' => __('Marketplace Setup', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_marketplace'),
					'handler' => array($this, 'wcfm_setup_marketplace_save')
				),
				'commission' => array(
					'name' => __('Commission Setup', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_commission'),
					'handler' => array($this, 'wcfm_setup_commission_save')
				),
				'withdrawal' => array(
					'name' => __('Withdrawal Setup', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_withdrawal'),
					'handler' => array($this, 'wcfm_setup_withdrawal_save')
				),
				'registration' => array(
					'name' => __('Registration Setup', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_registration'),
					'handler' => array($this, 'wcfm_setup_registration_save')
				),
				'style' => array(
					'name' => __('Style', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_style'),
					'handler' => array($this, 'wcfm_setup_style_save')
				),
				'capability' => array(
					'name' => __('Capability', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_capability'),
					'handler' => array($this, 'wcfm_setup_capability_save')
				),
				'next_steps' => array(
					'name' => __('Ready!', 'wc-frontend-manager'),
					'view' => array($this, 'wcfm_setup_ready'),
					'handler' => '',
				),
		);
		$is_marketplace = wcfm_is_marketplace();
		if( !$is_marketplace ) {
			unset( $default_steps['commission'] );
			unset( $default_steps['withdrawal'] );
			unset( $default_steps['registration'] );
			unset( $default_steps['capability'] );
		} elseif( $is_marketplace != 'wcfmmarketplace' ) {
			unset( $default_steps['commission'] );
			unset( $default_steps['withdrawal'] );
			unset( $default_steps['registration'] );
		} else
		if( WCFM_Dependencies::wcfmvm_plugin_active_check()) {
			unset( $default_steps['registration'] );
		}
		
		$this->steps = apply_filters('wcfm_dashboard_setup_steps', $default_steps);
		$current_step = filter_input(INPUT_GET, 'step');
		$this->step = $current_step ? sanitize_key($current_step) : current(array_keys($this->steps));
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array('jquery'), '2.70', true);
		wp_register_script('select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array('jquery'), '4.0.3');
		wp_register_script('wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array('jquery', 'select2'), WC_VERSION);
		wp_localize_script('wc-enhanced-select', 'wc_enhanced_select_params', array(
				'i18n_no_matches' => _x('No matches found', 'enhanced select', 'wc-frontend-manager'),
				'i18n_ajax_error' => _x('Loading failed', 'enhanced select', 'wc-frontend-manager'),
				'i18n_input_too_short_1' => _x('Please enter 1 or more characters', 'enhanced select', 'wc-frontend-manager'),
				'i18n_input_too_short_n' => _x('Please enter %qty% or more characters', 'enhanced select', 'wc-frontend-manager'),
				'i18n_input_too_long_1' => _x('Please delete 1 character', 'enhanced select', 'wc-frontend-manager'),
				'i18n_input_too_long_n' => _x('Please delete %qty% characters', 'enhanced select', 'wc-frontend-manager'),
				'i18n_selection_too_long_1' => _x('You can only select 1 item', 'enhanced select', 'wc-frontend-manager'),
				'i18n_selection_too_long_n' => _x('You can only select %qty% items', 'enhanced select', 'wc-frontend-manager'),
				'i18n_load_more' => _x('Loading more results&hellip;', 'enhanced select', 'wc-frontend-manager'),
				'i18n_searching' => _x('Searching&hellip;', 'enhanced select', 'wc-frontend-manager'),
				'ajax_url' => admin_url('admin-ajax.php'),
				'search_products_nonce' => wp_create_nonce('search-products'),
				'search_customers_nonce' => wp_create_nonce('search-customers'),
		));

		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
		wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array('dashicons', 'install'), WC_VERSION);
		wp_enqueue_style( 'wcfm-setup', $WCFM->plugin_url . '/assets/css/setup/wcfm-style-dashboard-setup.css', array('wc-setup'), $WCFM->version );
		wp_register_script('wcfm-setup', $WCFM->plugin_url . '/assets/js/setup/wcfm-script-setup.js', array('jquery'), $WCFM->version);
		wp_register_script('wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup' . $suffix . '.js', array('jquery', 'wc-enhanced-select', 'jquery-blockui'), WC_VERSION);
		wp_localize_script('wc-setup', 'wc_setup_params', array(
				'locale_info' => json_encode(include( WC()->plugin_path() . '/i18n/locale-info.php' )),
		));
		
		// Color Picker
		wp_enqueue_style( 'wp-color-picker' );
    wp_register_script( 'colorpicker_init', $WCFM->plugin_url . 'includes/libs/colorpicker/colorpicker.js', array( 'jquery', 'wp-color-picker' ), $WCFM->version );
		wp_register_script( 'iris', admin_url('js/iris.min.js'),array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch') );
		wp_register_script( 'wp-color-picker', admin_url('js/color-picker.min.js'), array('iris') );
		
		// Checkbox OFF-ON
		$WCFM->library->load_checkbox_offon_lib();
		
		$colorpicker_l10n = array('clear' => __('Clear'), 'defaultString' => __('Default'), 'pick' => __('Select Color'));
		wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
		
		if (!empty($_POST['save_step']) && isset($this->steps[$this->step]['handler'])) {
				call_user_func($this->steps[$this->step]['handler'], $this);
		}

		ob_start();
		$this->dashboard_setup_header();
		$this->dashboard_setup_steps();
		$this->dashboard_setup_content();
		$this->dashboard_setup_footer();
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
	 * @since 2.7.7
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
	public function dashboard_setup_header() {
		global $WCFM;
		$is_marketplace = wcfm_is_marketplace();
		
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
				<head>
						<meta name="viewport" content="width=device-width" />
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title><?php esc_html_e('WCFM &rsaquo; Setup Wizard', 'wc-frontend-manager'); ?></title>
						<?php wp_print_scripts('wc-setup'); ?>
						<?php wp_print_scripts('wcfm-setup'); ?>
						<?php do_action('admin_print_styles'); ?>
						<?php do_action('admin_head'); ?>
						<style type="text/css">
								.wc-setup-steps {
										justify-content: center;
								}
						</style>
				</head>
				<body class="wc-setup wp-core-ui">
				   <?php if( $is_marketplace == 'wcfmmarketplace' ) { ?>
						 <h1 id="wc-logo"><a href="http://wclovers.com/"><img src="<?php echo $WCFM->plugin_url; ?>assets/images/wcfmmp-75x75.png" alt="WCFM" /><span>WCFM Marketplace</span></a></h1>
					 <?php } else { ?> 
						 <h1 id="wc-logo"><a href="http://wclovers.com/"><img src="<?php echo $WCFM->plugin_url; ?>assets/images/wcfm-transparent.png" alt="WCFM" /><span>WC Frontend Manager</span></a></h1>
					<?php } ?>
						<?php
	}

	/**
	 * Output the steps.
	 */
	public function dashboard_setup_steps() {
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
							?>"><?php echo esc_html($step['name']); ?></li>
			<?php endforeach; ?>
			</ol>
			<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function dashboard_setup_content() {
			echo '<div class="wc-setup-content">';
			call_user_func($this->steps[$this->step]['view'], $this);
			echo '</div>';
	}

	/**
	 * Introduction step.
	 */
	public function wcfm_setup_introduction() {
		$is_marketplace = wcfm_is_marketplace();
		?>
		<?php if( $is_marketplace && ( $is_marketplace  == 'wcfmmarketplace' ) ) { ?>
			<h1><?php esc_html_e("Welcome to WooCommerce Multi-vendor Marketplace!", 'wc-frontend-manager'); ?></h1>
			<p><?php _e('Thank you for choosing WCFM Marketplace! This quick setup wizard will help you to configure the basic settings and you will have your marketplace ready in no time.', 'wc-frontend-manager'); ?></p>
		<?php } else { ?>
			<h1><?php esc_html_e("Let's experience the best ever WC Frontend Dashboard!!", 'wc-frontend-manager'); ?></h1>
			<p><?php _e('Thank you for choosing WCFM! This quick setup wizard will help you to configure the basic settings and you will have your dashboard ready in no time. <strong>It’s completely optional as WCFM already auto-setup.</strong>', 'wc-frontend-manager'); ?></p>
		<?php } ?>
		<p><?php esc_html_e("If you don't want to go through the wizard right now, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!", 'wc-frontend-manager'); ?></p>
		<p class="wc-setup-actions step">
			<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button-primary button button-large button-next"><?php esc_html_e("Let's go!", 'wc-frontend-manager'); ?></a>
			<a href="<?php echo esc_url(admin_url()); ?>" class="button button-large"><?php esc_html_e('Not right now', 'wc-frontend-manager'); ?></a>
		</p>
		<?php
	}

	/**
	 * Dashboard setup content
	 */
	public function wcfm_setup_dashboard() {
		global $WCFM;
		$wcfm_options = (array) get_option( 'wcfm_options' );
		$is_dashboard_full_view_disabled = isset( $wcfm_options['dashboard_full_view_disabled'] ) ? $wcfm_options['dashboard_full_view_disabled'] : 'no';
		$is_dashboard_theme_header_disabled = isset( $wcfm_options['dashboard_theme_header_disabled'] ) ? $wcfm_options['dashboard_theme_header_disabled'] : 'no';
		$is_slick_menu_disabled = isset( $wcfm_options['slick_menu_disabled'] ) ? $wcfm_options['slick_menu_disabled'] : 'no';
		$is_headpanel_disabled = isset( $wcfm_options['headpanel_disabled'] ) ? $wcfm_options['headpanel_disabled'] : 'no';
		$is_welcome_box_disabled = isset( $wcfm_options['welcome_box_disabled'] ) ? $wcfm_options['welcome_box_disabled'] : 'no';
		$is_checklist_view_disabled = isset( $wcfm_options['checklist_view_disabled'] ) ? $wcfm_options['checklist_view_disabled'] : 'no';
		$is_quick_access_disabled = isset( $wcfm_options['quick_access_disabled'] ) ? $wcfm_options['quick_access_disabled'] : 'yes';
		$is_responsive_float_menu_disabled = isset( $wcfm_options['responsive_float_menu_disabled'] ) ? $wcfm_options['responsive_float_menu_disabled'] : 'yes';
		$is_float_button_disabled = isset( $wcfm_options['float_button_disabled'] ) ? $wcfm_options['float_button_disabled'] : 'yes';
		?>
		<h1><?php esc_html_e('Dashboard setup', 'wc-frontend-manager'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_style', array(
																																												"dashboard_full_view_disabled" => array('label' => __('WCFM Full View', 'wc-frontend-manager') , 'name' => 'dashboard_full_view_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_dashboard_full_view_disabled),
																																												"dashboard_theme_header_disabled" => array('label' => __('Theme Header', 'wc-frontend-manager') , 'name' => 'dashboard_theme_header_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_dashboard_theme_header_disabled),
																																												"slick_menu_disabled" => array('label' => __('WCFM Slick Menu', 'wc-frontend-manager') , 'name' => 'slick_menu_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_slick_menu_disabled),
																																												"headpanel_disabled" => array('label' => __('WCFM Header Panel', 'wc-frontend-manager') , 'name' => 'headpanel_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_headpanel_disabled),
																																												"welcome_box_disabled" => array('label' => __('Welcome Box', 'wc-frontend-manager') , 'name' => 'welcome_box_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_welcome_box_disabled),
																																												"checklist_view_disabled" => array('label' => __('Category Checklist View', 'wc-frontend-manager') , 'name' => 'checklist_view_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_checklist_view_disabled, 'hints' => __( 'Disable this to have Product Manager Category/Custom Taxonomy Selector - Flat View.', 'wc-frontend-manager' ) ),
																																												"quick_access_disabled" => array('label' => __('Quick Access', 'wc-frontend-manager') , 'name' => 'quick_access_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_quick_access_disabled),
																																												"responsive_float_menu_disabled" => array('label' => __('Disable Responsive Float Menu', 'wc-frontend-manager') , 'name' => 'responsive_float_menu_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_responsive_float_menu_disabled),
																																												"float_button_disabled" => array('label' => __('Float Button', 'wc-frontend-manager') , 'name' => 'float_button_disabled','type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_float_button_disabled),
																																												) ) );
				?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}
	
	/**
	 * Marketplace step.
	 */
	public function wcfm_setup_marketplace() {
		global $WCFM, $WCFMmp;
		$is_marketplace = wcfm_is_marketplace();
		$multivendor_plugins = array( 'dokan' => 'Dokan Mutivendor', 'wcmarketplace' => 'WC Marketplace', 'wcvendors' => 'WC Vendors', 'wcpvendors' => 'WC Product Vendors' );
		?>
		<?php if( !$is_marketplace ) { ?>
			<style>
			  .wcfm-install-woocommerce {
						box-shadow: 0 1px 3px rgba(0,0,0,.13);
						padding: 24px 24px 0;
						margin: 0 0 20px;
						background: #fff;
						overflow: hidden;
						zoom: 1;
				}
				.wcfm-install-woocommerce .button-primary{
						font-size: 1.25em;
						padding: .5em 1em;
						line-height: 1em;
						margin-right: .5em;
						margin-bottom: 2px;
						height: auto;
				}
				.wcfm-install-woocommerce{
						font-family: sans-serif;
						text-align: center;    
				}
				.wcfm-install-woocommerce form .button-primary{
						color: #fff;
						background-color: #00798b;
						font-size: 16px;
						border: 1px solid #00798b;
						width: 230px;
						padding: 10px;
						margin: 25px 0 20px;
						cursor: pointer;
				}
				.wcfm-install-woocommerce form .button-primary:hover{
						background-color: #000000;
				}
			</style>
			<div class="wcfm-install-woocommerce">
				<p><?php _e('Do you want to setup a multi-vendor marketplace!', 'wc-frontend-manager'); ?></p>
				<form method="post" action="" name="wcfm_install_wcfmmarketplace">
					<?php submit_button(__('Install WCFM Marketplace', 'wc-frontend-manager'), 'primary', 'wcfm_install_wcfmmp'); ?>
					<?php wp_nonce_field('wcfm-install-wcfmmp'); ?>
				</form>
			</div>
			<p class="wc-setup-actions step">
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
			</p>
		<?php } elseif( $is_marketplace != 'wcfmmarketplace' ) { ?>
			<h1><?php esc_html_e("Welcome to WooCommerce Multi-vendor Marketplace!", 'wc-frontend-manager'); ?></h1>
			<p><?php printf( __('You have installed <b>%s</b> as your multi-vendor marketplace. Setup multi-vendor setting from plugin setup panel.', 'wc-frontend-manager'), $multivendor_plugins[$is_marketplace] ); ?></p>
			<p><?php printf( __( 'You may switch your multi-vendor to %s for having more features and flexibilities.', 'wc-frontend-manager' ), '<a href="https://wordpress.org/plugins/wc-multivendor-marketplace/" target="_blank">WCFM Marketplace</a>'); ?></p>
			<p class="wc-setup-actions step">
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e("Let's go!", 'wc-frontend-manager'); ?></a>
			</p>
		<?php } else { ?>
			<h1><?php esc_html_e('Marketplace setup', 'wc-frontend-manager'); ?></h1>
			<form method="post">
				<table class="form-table">
					<?php
					$wcfm_marketplace_options = get_option( 'wcfm_marketplace_options', array() );
					$wcfmmp_marketplace_shipping_options = get_option( 'woocommerce_wcfmmp_product_shipping_by_country_settings', array() );
					$wcfmmp_marketplace_shipping_enabled = ( !empty($wcfmmp_marketplace_shipping_options) && !empty($wcfmmp_marketplace_shipping_options['enabled']) ) ? 'no' : 'no';
				
					$wcfm_store_url = isset( $wcfm_marketplace_options['wcfm_store_url'] ) ? $wcfm_marketplace_options['wcfm_store_url'] : 'store';
					$vendor_sold_by = isset( $wcfm_marketplace_options['vendor_sold_by'] ) ? 'no' : 'no';
					$vendor_sold_by_template = isset( $wcfm_marketplace_options['vendor_sold_by_template'] ) ? $wcfm_marketplace_options['vendor_sold_by_template'] : 'advanced';
					$vendor_sold_by_position = isset( $wcfm_marketplace_options['vendor_sold_by_position'] ) ? $wcfm_marketplace_options['vendor_sold_by_position'] : 'bellow_atc';
					$store_name_position = isset( $wcfm_marketplace_options['store_name_position'] ) ? $wcfm_marketplace_options['store_name_position'] : 'on_banner';
					$product_mulivendor = isset( $wcfm_marketplace_options['product_mulivendor'] ) ? 'no' : 'no';
					$store_sidebar = isset( $wcfm_marketplace_options['store_sidebar'] ) ? 'no' : 'no';
					$wcfm_google_map_api = isset( $wcfm_marketplace_options['wcfm_google_map_api'] ) ? $wcfm_marketplace_options['wcfm_google_map_api'] : '';
					
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_store', array(
																																											"vendor_store_url" => array('label' => __('Vendor Store URL', 'wc-multivendor-marketplace') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'in_table' => 'yes', 'desc_class' => 'wcfm_page_options_desc', 'value' => $wcfm_store_url, 'desc' => sprintf( __( 'Define the seller store URL  (%s/[this-text]/[seller-name])', 'wc-multivendor-marketplace' ), get_site_url() )  ),
																																											"vendor_sold_by" => array('label' => __('Visible Sold By', 'wc-multivendor-marketplace'), 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $vendor_sold_by, 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'Uncheck this to disable Sold By display for products.', 'wc-multivendor-marketplace' ) ),
																																											"vendor_sold_by_template" => array('label' => __('Sold By Template', 'wc-multivendor-marketplace'), 'type' => 'select', 'in_table' => 'yes', 'options' => array( 'simple' => __( 'Simple', 'wc-multivendor-marketplace' ), 'advanced' => __( 'Advanced', 'wc-multivendor-marketplace' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $vendor_sold_by_template, 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'Single product page Sold By template.', 'wc-multivendor-marketplace' ) ),
																																											"sold_by_template_simple" => array( 'label' => '&nbsp;', 'type' => 'html', 'in_table' => 'yes', 'wrapper_class' => 'vendor_sold_by_type vendor_sold_by_type_simple', 'label_class' => 'wcfm_title wcfm_ele', 'value' => '<img src="'.$WCFMmp->plugin_url.'assets/images/sold_by_simple.png" />', 'attributes' => array( 'style' => 'border: 1px dotted #ccc;margin-bottom:15px;' ) ),
																																											"sold_by_template_advanced" => array( 'label' => '&nbsp;', 'type' => 'html', 'in_table' => 'yes', 'wrapper_class' => 'vendor_sold_by_type vendor_sold_by_type_advanced', 'label_class' => 'wcfm_title wcfm_ele', 'value' => '<img src="'.$WCFMmp->plugin_url.'assets/images/sold_by_advanced.png" />', 'attributes' => array( 'style' => 'border: 1px dotted #ccc;margin-bottom:15px;' ) ),
																																											"vendor_sold_by_position" => array( 'label' => __('Sold By Position', 'wc-multivendor-marketplace'), 'type' => 'select', 'in_table' => 'yes', 'options' => array( 'bellow_price' => __( 'Bellow Price', 'wc-multivendor-marketplace' ), 'bellow_sc' => __( 'Below Short Description', 'wc-multivendor-marketplace' ), 'bellow_atc' => __( 'Below Add to Cart', 'wc-multivendor-marketplace' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $vendor_sold_by_position, 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'Sold by display position at Single Product Page.', 'wc-multivendor-marketplace' ) ),
																																											"store_name_position" => array( 'label' => __('Store Name Position', 'wc-multivendor-marketplace'), 'type' => 'select', 'in_table' => 'yes', 'options' => array( 'on_banner' => __( 'On Banner', 'wc-multivendor-marketplace' ), 'on_header' => __( 'At Header', 'wc-multivendor-marketplace' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $store_name_position, 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'Store name position at Vendor Store Page.', 'wc-multivendor-marketplace' ) ),
																																											"store_sidebar" => array( 'label' => __('Store Sidebar', 'wc-multivendor-marketplace'), 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $store_sidebar, 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'Uncheck this to disable vendor store sidebar.', 'wc-multivendor-marketplace' ) ),
																																											"product_mulivendor" => array('label' => __('Product Mulivendor', 'wc-multivendor-marketplace'), 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $product_mulivendor, 'desc_class' => 'wcfm_page_options_desc','desc' => __( 'Enable this to allow vendors to sell other vendor products, single product multiple seller.', 'wc-multivendor-marketplace' ) ),
																																											"enable_marketplace_shipping" => array('label' => __('Marketplace Shipping', 'wc-multivendor-marketplace'), 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $wcfmmp_marketplace_shipping_enabled, 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'Enable this to allow your vendors to setup their own shipping by country.', 'wc-multivendor-marketplace' ) ),
																																											"wcfm_google_map_api" => array('label' => __('Google Map API Key', 'wc-multivendor-marketplace') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'in_table' => 'yes', 'desc_class' => 'wcfm_page_options_desc', 'value' => $wcfm_google_map_api, 'desc' => sprintf( __( '%sAPI Key%s is needed to display map on store page', 'wc-multivendor-marketplace' ), '<a target="_blank" href="https://developers.google.com/maps/documentation/javascript/">', '</a>' ) ),
																																											) ) );
					?>
				</table>
				<p class="wc-setup-actions step">
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
					<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
					<?php wp_nonce_field('wcfm-setup'); ?>
				</p>
			</form>
		<?php } ?>
		<?php
	}
	
	/**
	 * Install wcfm marketplace if not exist
	 * @throws Exception
	 */
	public function install_wcfmmp() {
		check_admin_referer('wcfm-install-wcfmmp');
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		WP_Filesystem();
		$skin = new Automatic_Upgrader_Skin;
		$upgrader = new WP_Upgrader($skin);
		$installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
		$plugin_slug = 'wc-multivendor-marketplace';
		$plugin = 'wc-multivendor-marketplace/wc-multivendor-marketplace.php';
		$installed = false;
		$activate = false;
		// See if the plugin is installed already
		if (in_array($plugin_slug, $installed_plugins)) {
				$installed = true;
				$activate = !is_plugin_active($plugin);
		}
		// Install this thing!
		if (!$installed) {
			// Suppress feedback
			ob_start();
	
			try {
				$plugin_information = plugins_api('plugin_information', array(
						'slug' => $plugin_slug,
						'fields' => array(
								'short_description' => false,
								'sections' => false,
								'requires' => false,
								'rating' => false,
								'ratings' => false,
								'downloaded' => false,
								'last_updated' => false,
								'added' => false,
								'tags' => false,
								'homepage' => false,
								'donate_link' => false,
								'author_profile' => false,
								'author' => false,
						),
				));

				if (is_wp_error($plugin_information)) {
					throw new Exception($plugin_information->get_error_message());
				}

				$package = $plugin_information->download_link;
				$download = $upgrader->download_package($package);

				if (is_wp_error($download)) {
					throw new Exception($download->get_error_message());
				}

				$working_dir = $upgrader->unpack_package($download, true);

				if (is_wp_error($working_dir)) {
					throw new Exception($working_dir->get_error_message());
				}

				$result = $upgrader->install_package(array(
						'source' => $working_dir,
						'destination' => WP_PLUGIN_DIR,
						'clear_destination' => false,
						'abort_if_destination_exists' => false,
						'clear_working' => true,
						'hook_extra' => array(
								'type' => 'plugin',
								'action' => 'install',
						),
				));

				if (is_wp_error($result)) {
					throw new Exception($result->get_error_message());
				}

				$activate = true;
			} catch (Exception $e) {
				printf(
						__('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'wc-frontend-manager'), 'WCFM Marketplace', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=wc-multivendor-marketplace'))
				);
				exit();
			}

			// Discard feedback
			ob_end_clean();
		}

		wp_clean_plugins_cache();
		// Activate this thing
		if ($activate) {
			try {
				$result = activate_plugin($plugin);

				if (is_wp_error($result)) {
					throw new Exception($result->get_error_message());
				}
			} catch (Exception $e) {
				printf(
					__('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'wc-frontend-manager'), 'WC Frontend Manager', admin_url('plugins.php')
				);
				exit();
			}
		}
		wp_safe_redirect(admin_url('index.php?page=wcfm-setup&step=marketplace'));
	}
	
	/**
	 * Registration step.
	 */
	public function wcfm_setup_registration() {
		global $WCFM;
		?>
		<style>
			.wcfm-install-woocommerce {
					box-shadow: 0 1px 3px rgba(0,0,0,.13);
					padding: 24px 24px 0;
					margin: 0 0 20px;
					background: #fff;
					overflow: hidden;
					zoom: 1;
			}
			.wcfm-install-woocommerce .button-primary{
					font-size: 1.25em;
					padding: .5em 1em;
					line-height: 1em;
					margin-right: .5em;
					margin-bottom: 2px;
					height: auto;
			}
			.wcfm-install-woocommerce{
					font-family: sans-serif;
					text-align: center;    
			}
			.wcfm-install-woocommerce form .button-primary{
					color: #fff;
					background-color: #00798b;
					font-size: 16px;
					border: 1px solid #00798b;
					width: 230px;
					padding: 10px;
					margin: 25px 0 20px;
					cursor: pointer;
			}
			.wcfm-install-woocommerce form .button-primary:hover{
					background-color: #000000;
			}
		</style>
		<div class="wcfm-install-woocommerce">
			<p><?php _e('Setup WCFM Maketplace vendor registration:', 'wc-frontend-manager'); ?></p>
			<form method="post" action="" name="wcfm_install_wcfmmembership">
				<?php submit_button(__('Setup Registration', 'wc-frontend-manager'), 'primary', 'wcfm_install_wcfmvm'); ?>
				<?php wp_nonce_field('wcfm-install-wcfmvm'); ?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Install wcfm marketplace if not exist
	 * @throws Exception
	 */
	public function install_wcfm_registration() {
		check_admin_referer('wcfm-install-wcfmvm');
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		WP_Filesystem();
		$skin = new Automatic_Upgrader_Skin;
		$upgrader = new WP_Upgrader($skin);
		$installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
		$plugin_slug = 'wc-multivendor-membership';
		$plugin = 'wc-multivendor-membership/wc-multivendor-membership.php';
		$installed = false;
		$activate = false;
		// See if the plugin is installed already
		if (in_array($plugin_slug, $installed_plugins)) {
				$installed = true;
				$activate = !is_plugin_active($plugin);
		}
		// Install this thing!
		if (!$installed) {
			// Suppress feedback
			ob_start();
	
			try {
				$plugin_information = plugins_api('plugin_information', array(
						'slug' => $plugin_slug,
						'fields' => array(
								'short_description' => false,
								'sections' => false,
								'requires' => false,
								'rating' => false,
								'ratings' => false,
								'downloaded' => false,
								'last_updated' => false,
								'added' => false,
								'tags' => false,
								'homepage' => false,
								'donate_link' => false,
								'author_profile' => false,
								'author' => false,
						),
				));

				if (is_wp_error($plugin_information)) {
					throw new Exception($plugin_information->get_error_message());
				}

				$package = $plugin_information->download_link;
				$download = $upgrader->download_package($package);

				if (is_wp_error($download)) {
					throw new Exception($download->get_error_message());
				}

				$working_dir = $upgrader->unpack_package($download, true);

				if (is_wp_error($working_dir)) {
					throw new Exception($working_dir->get_error_message());
				}

				$result = $upgrader->install_package(array(
						'source' => $working_dir,
						'destination' => WP_PLUGIN_DIR,
						'clear_destination' => false,
						'abort_if_destination_exists' => false,
						'clear_working' => true,
						'hook_extra' => array(
								'type' => 'plugin',
								'action' => 'install',
						),
				));

				if (is_wp_error($result)) {
					throw new Exception($result->get_error_message());
				}

				$activate = true;
			} catch (Exception $e) {
				printf(
						__('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'wc-frontend-manager'), 'WCFM Membership', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=wc-multivendor-membership'))
				);
				exit();
			}

			// Discard feedback
			ob_end_clean();
		}

		wp_clean_plugins_cache();
		// Activate this thing
		if ($activate) {
			try {
				$result = activate_plugin($plugin);

				if (is_wp_error($result)) {
					throw new Exception($result->get_error_message());
				}
			} catch (Exception $e) {
				printf(
					__('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'wc-frontend-manager'), 'WCFM Membership', admin_url('plugins.php')
				);
				exit();
			}
		}
		wp_safe_redirect(admin_url('index.php?page=wcfm-setup&step=style'));
	}
	
	/**
	 * Commission setup content
	 */
	public function wcfm_setup_commission() {
		global $WCFM;
		$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
		
		$wcfm_commission_types = get_wcfm_marketplace_commission_types();
		unset( $wcfm_commission_types['by_sales'] );
		unset( $wcfm_commission_types['by_products'] );
		
		$vendor_commission_for = isset( $wcfm_commission_options['commission_for'] ) ? $wcfm_commission_options['commission_for'] : 'vendor';
		$vendor_commission_mode = isset( $wcfm_commission_options['commission_mode'] ) ? $wcfm_commission_options['commission_mode'] : 'percent';
		$vendor_commission_fixed = isset( $wcfm_commission_options['commission_fixed'] ) ? $wcfm_commission_options['commission_fixed'] : '';
		$vendor_commission_percent = isset( $wcfm_commission_options['commission_percent'] ) ? $wcfm_commission_options['commission_percent'] : '90';
		$vendor_get_shipping = isset( $wcfm_commission_options['get_shipping'] ) ? 'no' : 'yes';
		$vendor_get_tax = isset( $wcfm_commission_options['get_tax'] ) ? 'no' : 'yes';
		$vendor_coupon_deduct = isset( $wcfm_commission_options['coupon_deduct'] ) ? 'no' : 'yes';
		?>
		<h1><?php esc_html_e('Commission setup', 'wc-frontend-manager'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_commission', array(
																																									"vendor_commission_for" => array('label' => __('Commission For', 'wc-multivendor-marketplace'), 'type' => 'select', 'in_table' => 'yes', 'options' => array( 'vendor' => __( 'Vendor', 'wc-multivendor-marketplace' ), 'admin' => __( 'Admin', 'wc-multivendor-marketplace' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_commission_for ),
					                                                                        "vendor_commission_mode" => array('label' => __('Commission Mode', 'wc-multivendor-marketplace'), 'type' => 'select', 'in_table' => 'yes', 'options' => $wcfm_commission_types, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_commission_mode, 'desc' => __( 'You may setup more commission rules (By Sales Total and Product Price) from setting panel.', 'wc-frontend-manager' ) ),
					                                                                        "vendor_commission_percent" => array('label' => __('Commission Percent(%)', 'wc-multivendor-marketplace'), 'type' => 'number', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'value' => $vendor_commission_percent, 'attributes' => array( 'min' => '1', 'step' => '0.1') ),
					                                                                        "vendor_commission_fixed" => array('label' => __('Commission Fixed', 'wc-multivendor-marketplace') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'value' => $vendor_commission_fixed, 'attributes' => array( 'min' => '1', 'step' => '0.1') ),
																																									"vendor_get_shipping" => array('label' => __('Shipping cost goes to vendor?', 'wc-multivendor-marketplace'), 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $vendor_get_shipping ),
																																									"vendor_get_tax" => array('label' => __('Tax goes to vendor?', 'wc-multivendor-marketplace'), 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $vendor_get_tax ),
																																									"vendor_coupon_deduct" => array('label' => __('Commission after deduct discounts?', 'wc-multivendor-marketplace'), 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox input-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $vendor_coupon_deduct, 'hints' => __( 'Generate vendor commission after deduct coupon or other discounts.', 'wc-multivendor-marketplace' ) ),
																																									) ) );
				?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}
	
	/**
	 * Withdrawal setup content
	 */
	public function wcfm_setup_withdrawal() {
		global $WCFM;
		
		$wcfm_withdrawal_options = get_option( 'wcfm_withdrawal_options', array() );
		
		$wcfm_marketplace_withdrwal_payment_methods = get_wcfm_marketplace_withdrwal_payment_methods();
		$wcfm_marketplace_withdrawal_order_status   = get_wcfm_marketplace_withdrwal_order_status();
		$wcfm_marketplace_disallow_order_payment_methods = get_wcfm_marketplace_disallow_order_payment_methods();
		
		$request_auto_approve = isset( $wcfm_withdrawal_options['request_auto_approve'] ) ? $wcfm_withdrawal_options['request_auto_approve'] : 'no';
		$payment_methods = isset( $wcfm_withdrawal_options['payment_methods'] ) ? $wcfm_withdrawal_options['payment_methods'] : array( 'paypal', 'bank_transfer' );
		
		$withdrawal_test_mode = isset( $wcfm_withdrawal_options['test_mode'] ) ? 'yes' : 'no';
		
		$withdrawal_paypal_client_id = isset( $wcfm_withdrawal_options['paypal_client_id'] ) ? $wcfm_withdrawal_options['paypal_client_id'] : '';
		$withdrawal_paypal_secret_key = isset( $wcfm_withdrawal_options['paypal_secret_key'] ) ? $wcfm_withdrawal_options['paypal_secret_key'] : '';
		$withdrawal_stripe_client_id = isset( $wcfm_withdrawal_options['stripe_client_id'] ) ? $wcfm_withdrawal_options['stripe_client_id'] : '';
		$withdrawal_stripe_published_key = isset( $wcfm_withdrawal_options['stripe_published_key'] ) ? $wcfm_withdrawal_options['stripe_published_key'] : '';
		$withdrawal_stripe_secret_key = isset( $wcfm_withdrawal_options['stripe_secret_key'] ) ? $wcfm_withdrawal_options['stripe_secret_key'] : '';
		
		$withdrawal_paypal_test_client_id = isset( $wcfm_withdrawal_options['paypal_test_client_id'] ) ? $wcfm_withdrawal_options['paypal_test_client_id'] : '';
		$withdrawal_paypal_test_secret_key = isset( $wcfm_withdrawal_options['paypal_test_secret_key'] ) ? $wcfm_withdrawal_options['paypal_test_secret_key'] : '';
		$withdrawal_stripe_test_client_id = isset( $wcfm_withdrawal_options['stripe_test_client_id'] ) ? $wcfm_withdrawal_options['stripe_test_client_id'] : '';
		$withdrawal_stripe_test_published_key = isset( $wcfm_withdrawal_options['stripe_test_published_key'] ) ? $wcfm_withdrawal_options['stripe_test_published_key'] : '';
		$withdrawal_stripe_test_secret_key = isset( $wcfm_withdrawal_options['stripe_test_secret_key'] ) ? $wcfm_withdrawal_options['stripe_test_secret_key'] : '';
		
		$order_status = isset( $wcfm_withdrawal_options['order_status'] ) ? $wcfm_withdrawal_options['order_status'] : array( 'wc-completed' );
		$disallow_order_payment_methods = isset( $wcfm_withdrawal_options['disallow_order_payment_methods'] ) ? $wcfm_withdrawal_options['disallow_order_payment_methods'] : array();
		$withdrawal_limit = isset( $wcfm_withdrawal_options['withdrawal_limit'] ) ? $wcfm_withdrawal_options['withdrawal_limit'] : '';
		$withdrawal_thresold = isset( $wcfm_withdrawal_options['withdrawal_thresold'] ) ? $wcfm_withdrawal_options['withdrawal_thresold'] : '';
		$withdrawal_charge_type = isset( $wcfm_withdrawal_options['withdrawal_charge_type'] ) ? $wcfm_withdrawal_options['withdrawal_charge_type'] : 'no';
		
		$withdrawal_charge               = isset( $wcfm_withdrawal_options['withdrawal_charge'] ) ? $wcfm_withdrawal_options['withdrawal_charge'] : array();
		$withdrawal_charge_paypal        = isset( $withdrawal_charge['paypal'] ) ? $withdrawal_charge['paypal'] : array();
		$withdrawal_charge_stripe        = isset( $withdrawal_charge['stripe'] ) ? $withdrawal_charge['stripe'] : array();
		$withdrawal_charge_skrill        = isset( $withdrawal_charge['skrill'] ) ? $withdrawal_charge['skrill'] : array();
		$withdrawal_charge_bank_transfer = isset( $withdrawal_charge['bank_transfer'] ) ? $withdrawal_charge['bank_transfer'] : array();
		?>
		<h1><?php esc_html_e('Withdrawal setup', 'wc-frontend-manager'); ?></h1>
		<form method="post">
			<div>
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_withdrawal', array(
					                                                                        "withdrawal_request_auto_approve" => array('label' => __('Request auto-approve?', 'wc-multivendor-marketplace'), 'type' => 'checkbox', 'name' => 'wcfm_withdrawal_options[request_auto_approve]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_spl_title checkbox_title', 'value' => 'yes', 'dfvalue' => $request_auto_approve, 'desc_class' => 'instructions', 'desc' => __( 'Check this to automatically disburse payments to vendors on request, no admin approval required. Auto disbursement only works for auto-payment gateways, e.g. PayPal, Stripe etc. Bank Transfer or other non-autopay mode always requires approval, as these are manual transactions.', 'wc-multivendor-membership' ) ),
					                                                                        "withdrawal_payment_methods" => array( 'label' => __( 'Withdraw Payment Methods', 'wc-multivendor-membership' ), 'name' => 'wcfm_withdrawal_options[payment_methods]', 'type' => 'checklist', 'class' => 'wcfm-checkbox wcfm_ele payment_options', 'label_class' => 'wcfm_title wcfm_full_title', 'options' => $wcfm_marketplace_withdrwal_payment_methods, 'value' => $payment_methods  ),
					                                                                        "withdrawal_test_mode" => array('label' => __('Enable Test Mode', 'wc-multivendor-marketplace'), 'type' => 'checkbox', 'name' => 'wcfm_withdrawal_options[test_mode]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title checkbox_spl_title', 'value' => 'yes', 'dfvalue' => $withdrawal_test_mode ),
					                                                                        ) ) );
				?>
			</div>	
		  <table class="form-table">	
		    <?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_withdrawal_payment', array(                                                                        
					                                                                        "withdrawal_paypal_client_id" => array('label' => __('PayPal Client ID', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[paypal_client_id]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_paypal withdrawal_mode_live', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_paypal withdrawal_mode_live', 'value' => $withdrawal_paypal_client_id ),
					                                                                        "withdrawal_paypal_secret_key" => array('label' => __('PayPal Secret Key', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[paypal_secret_key]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_paypal withdrawal_mode_live', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_paypal withdrawal_mode_live', 'value' => $withdrawal_paypal_secret_key ),
					                                                                        "withdrawal_stripe_client_id" => array('label' => __('Stripe Client ID', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[stripe_client_id]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_stripe withdrawal_mode_live', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_stripe withdrawal_mode_live', 'value' => $withdrawal_stripe_client_id ),
					                                                                        "withdrawal_stripe_published_key" => array('label' => __('Stripe Publish Key', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[stripe_published_key]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_stripe withdrawal_mode_live', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_stripe withdrawal_mode_live', 'value' => $withdrawal_stripe_published_key ),
					                                                                        "withdrawal_stripe_secret_key" => array('label' => __('Stripe Secret Key', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[stripe_secret_key]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_stripe withdrawal_mode_live', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_stripe withdrawal_mode_live', 'value' => $withdrawal_stripe_secret_key ),
					                                                                        
					                                                                        "withdrawal_paypal_test_client_id" => array('label' => __('PayPal Client ID', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[paypal_test_client_id]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_paypal withdrawal_mode_test', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_paypal withdrawal_mode_test', 'value' => $withdrawal_paypal_test_client_id ),
					                                                                        "withdrawal_paypal_test_secret_key" => array('label' => __('PayPal Secret Key', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[paypal_test_secret_key]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_paypal withdrawal_mode_test', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_paypal withdrawal_mode_test', 'value' => $withdrawal_paypal_test_secret_key ),
					                                                                        "withdrawal_stripe_test_client_id" => array('label' => __('Stripe Client ID', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[stripe_test_client_id]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_stripe withdrawal_mode_test', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_stripe withdrawal_mode_test', 'value' => $withdrawal_stripe_test_client_id ),
					                                                                        "withdrawal_stripe_test_published_key" => array('label' => __('Stripe Publish Key', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[stripe_test_published_key]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_stripe withdrawal_mode_test', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_stripe withdrawal_mode_test', 'value' => $withdrawal_stripe_test_published_key ),
					                                                                        "withdrawal_stripe_test_secret_key" => array('label' => __('Stripe Secret Key', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[stripe_test_secret_key]', 'type' => 'text', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_stripe withdrawal_mode_test', 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_stripe withdrawal_mode_test', 'value' => $withdrawal_stripe_test_secret_key ),
					                                                           ) ) ); 
				?>
			</table>
			<div>
			<?php
			$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_withdrawal_rules', array(	                                                   
																																								"withdrawal_order_status" => array( 'label' => __( 'Order Status for Withdraw', 'wc-multivendor-membership' ), 'name' => 'wcfm_withdrawal_options[order_status]', 'type' => 'checklist', 'class' => 'wcfm-checkbox wcfm_ele payment_options', 'label_class' => 'wcfm_title wcfm_full_title', 'options' => $wcfm_marketplace_withdrawal_order_status, 'value' => $order_status  ),
																																								"withdrawal_order_payment_methods" => array( 'label' => __( 'Disallow Order Payment Mothods for Withdraw', 'wc-multivendor-membership' ), 'name' => 'wcfm_withdrawal_options[disallow_order_payment_methods]', 'type' => 'checklist', 'class' => 'wcfm-checkbox wcfm_ele payment_options', 'label_class' => 'wcfm_title wcfm_full_title', 'options' => $wcfm_marketplace_disallow_order_payment_methods, 'value' => $disallow_order_payment_methods, 'desc' => __( 'Order Payment Mothods which are not applicable for vendor withdrawal request. e.g Order payment method COD and vendor receiving that amount directly from customers.', 'wc-multivendor-membership' )  ),
																																								) ) );
			?>	
			</div>
			<table class="form-table">	
			  <?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_withdrawal_limits', array(																																				
																																									"withdrawal_limit" => array('label' => __('Minimum Withdraw Limit', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[withdrawal_limit]', 'type' => 'number', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'desc_class'=> 'wcfm_page_options_desc', 'value' => $withdrawal_limit, 'attributes' => array( 'min' => '0.1', 'step' => '0.1'), 'desc' => __( 'Minimum balance required to make a withdraw request. Leave blank to set no minimum limits.', 'wc-multivendor-marketplace') ),
																																									"withdrawal_thresold" => array('label' => __('Withdraw Threshold', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[withdrawal_thresold]', 'type' => 'number', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'desc_class' => 'wcfm_page_options_desc', 'value' => $withdrawal_thresold , 'attributes' => array( 'min' => '1', 'step' => '1'), 'desc' => __('Withdraw Threshold Days, (Make order matured to make a withdraw request). Leave empty to inactive this option.', 'wc-multivendor-marketplace') ),
																																									"withdrawal_charge_type" => array('label' => __('Withdrawal Charges', 'wc-multivendor-marketplace'), 'name' => 'wcfm_withdrawal_options[withdrawal_charge_type]', 'type' => 'select', 'in_table' => 'yes', 'options' => array( 'no' => __( 'No Charge', 'wc-multivendor-marketplace' ), 'percent' => __( 'Percent', 'wc-multivendor-marketplace' ), 'fixed'   => __( 'Fixed', 'wc-multivendor-marketplace' ), 'percent_fixed' => __( 'Percent + Fixed', 'wc-multivendor-marketplace' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'desc_class' => 'wcfm_page_options_desc', 'value' => $withdrawal_charge_type , 'desc' => __('Charges applicable for each withdarwal.', 'wc-multivendor-marketplace') ),
																																									
																																									"withdrawal_charge_paypal" => array( 'label' => __('PayPal Charge', 'wc-multivendor-marketplace'), 'type' => 'multiinput', 'in_table' => 'yes', 'name' => 'wcfm_withdrawal_options[withdrawal_charge][paypal]', 'wrapper_class' => 'withdraw_charge_block withdraw_charge_paypal', 'label_class' => 'wcfm_title wcfm_ele wcfm_fill_ele withdraw_charge_block withdraw_charge_paypal', 'value' => $withdrawal_charge_paypal, 'custom_attributes' => array( 'limit' => 1 ), 'options' => array(
																																																											"percent" => array('label' => __('Percent Charge(%)', 'wc-multivendor-marketplace'),  'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"fixed" => array('label' => __('Fixed Charge', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"tax" => array('label' => __('Charge Tax', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1'), 'hints' => __( 'Tax for withdrawal charge, calculate in percent.', 'wc-multivendor-marketplace' ) ),
																																																											) ),
																																									"withdrawal_charge_stripe" => array( 'label' => __('Stripe Charge', 'wc-multivendor-marketplace'), 'type' => 'multiinput', 'in_table' => 'yes', 'name' => 'wcfm_withdrawal_options[withdrawal_charge][stripe]', 'wrapper_class' => 'withdraw_charge_block withdraw_charge_stripe', 'label_class' => 'wcfm_title wcfm_ele wcfm_fill_ele withdraw_charge_block withdraw_charge_stripe', 'value' => $withdrawal_charge_stripe, 'custom_attributes' => array( 'limit' => 1 ), 'options' => array(
																																																											"percent" => array('label' => __('Percent Charge(%)', 'wc-multivendor-marketplace'),  'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"fixed" => array('label' => __('Fixed Charge', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"tax" => array('label' => __('Charge Tax', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1'), 'hints' => __( 'Tax for withdrawal charge, calculate in percent.', 'wc-multivendor-marketplace' ) ),
																																																											) ),
																																									"withdrawal_charge_skrill" => array( 'label' => __('Skrill Charge', 'wc-multivendor-marketplace'), 'type' => 'multiinput', 'in_table' => 'yes', 'name' => 'wcfm_withdrawal_options[withdrawal_charge][skrill]', 'wrapper_class' => 'withdraw_charge_block withdraw_charge_skrill', 'label_class' => 'wcfm_title wcfm_ele wcfm_fill_ele withdraw_charge_block withdraw_charge_skrill', 'value' => $withdrawal_charge_skrill, 'custom_attributes' => array( 'limit' => 1 ), 'options' => array(
																																																											"percent" => array('label' => __('Percent Charge(%)', 'wc-multivendor-marketplace'),  'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"fixed" => array('label' => __('Fixed Charge', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"tax" => array('label' => __('Charge Tax', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1'), 'hints' => __( 'Tax for withdrawal charge, calculate in percent.', 'wc-multivendor-marketplace' ) ),
																																																											) ),
																																									"withdrawal_charge_bank_transfer" => array( 'label' => __('Bank Transfer Charge', 'wc-multivendor-marketplace'), 'type' => 'multiinput', 'in_table' => 'yes', 'name' => 'wcfm_withdrawal_options[withdrawal_charge][bank_transfer]', 'wrapper_class' => 'withdraw_charge_block withdraw_charge_bank_transfer', 'label_class' => 'wcfm_title wcfm_ele wcfm_fill_ele withdraw_charge_block withdraw_charge_bank_transfer', 'value' => $withdrawal_charge_bank_transfer, 'custom_attributes' => array( 'limit' => 1 ), 'options' => array(
																																																											"percent" => array('label' => __('Percent Charge(%)', 'wc-multivendor-marketplace'),  'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"fixed" => array('label' => __('Fixed Charge', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																																											"tax" => array('label' => __('Charge Tax', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1'), 'hints' => __( 'Tax for withdrawal charge, calculate in percent.', 'wc-multivendor-marketplace' ) ),
																																																											) )
																																									) ) );
				?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}
	
	/**
	 * Style setup content
	 */
	public function wcfm_setup_style() {
		global $WCFM;
		wp_print_scripts('wp-color-picker');
		wp_print_scripts('colorpicker_init');
		wp_print_scripts('iris');
		$wcfm_options = (array) get_option( 'wcfm_options' );
	  $color_options = $WCFM->wcfm_color_setting_options();
		?>
		<h1><?php esc_html_e('Dashboard Style', 'wc-frontend-manager'); ?></h1>
		<form method="post">
			<table class="form-table">
				<?php
					$color_options_array = array();
					foreach( $color_options as $color_option_key => $color_option ) {
						$color_options_array[$color_option['name']] = array( 'label' => $color_option['label'] , 'type' => 'colorpicker', 'in_table' => 'yes', 'class' => 'wcfm-text wcfm_ele colorpicker', 'label_class' => 'wcfm_title wcfm_ele', 'value' => ( isset($wcfm_options[$color_option['name']]) ) ? $wcfm_options[$color_option['name']] : $color_option['default'] );
					}
					$WCFM->wcfm_fields->wcfm_generate_form_field( $color_options_array );
				?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * capability setup content
	 */
	public function wcfm_setup_capability() {
		global $WCFM;
		$wcfm_capability_options = (array) get_option( 'wcfm_capability_options' );
		
		$vnd_wpadmin = ( isset( $wcfm_capability_options['vnd_wpadmin'] ) ) ? $wcfm_capability_options['vnd_wpadmin'] : 'yes';
		
		$submit_products = ( isset( $wcfm_capability_options['submit_products'] ) ) ? $wcfm_capability_options['submit_products'] : 'no';
		$publish_products = ( isset( $wcfm_capability_options['publish_products'] ) ) ? $wcfm_capability_options['publish_products'] : 'no';
		$edit_live_products = ( isset( $wcfm_capability_options['edit_live_products'] ) ) ? $wcfm_capability_options['edit_live_products'] : 'no';
		$delete_products = ( isset( $wcfm_capability_options['delete_products'] ) ) ? $wcfm_capability_options['delete_products'] : 'no';
		
		// Miscellaneous Capabilities
		$manage_booking = ( isset( $wcfm_capability_options['manage_booking'] ) ) ? $wcfm_capability_options['manage_booking'] : 'no';
		$manage_subscription = ( isset( $wcfm_capability_options['manage_subscription'] ) ) ? $wcfm_capability_options['manage_subscription'] : 'no';
		$associate_listings = ( isset( $wcfm_capability_options['associate_listings'] ) ) ? $wcfm_capability_options['associate_listings'] : 'no';
		
		$view_orders  = ( isset( $wcfm_capability_options['view_orders'] ) ) ? $wcfm_capability_options['view_orders'] : 'no';
		$order_status_update  = ( isset( $wcfm_capability_options['order_status_update'] ) ) ? $wcfm_capability_options['order_status_update'] : 'no';
		$view_reports  = ( isset( $wcfm_capability_options['view_reports'] ) ) ? $wcfm_capability_options['view_reports'] : 'no';
		?>
		<h1><?php esc_html_e('Capability', 'wc-frontend-manager'); ?></h1>
		<form method="post">
			<table class="form-table">
			  <?php
			  $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_vendor_access', array(  
																																						 "vnd_wpadmin" => array('label' => __('Backend Access', 'wc-frontend-manager') . ' (wp-admin)', 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vnd_wpadmin),
																															) ) );
			  
			  $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_vendor_products', array("submit_products" => array('label' => __('Submit Products', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_products),
																																																									 "publish_products" => array('label' => __('Publish Products', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_products),
																																																									 "edit_live_products" => array('label' => __('Edit Live Products', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_live_products),
																																																									 "delete_products" => array('label' => __('Delete Products', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_products)
																													) ) );
				
				if( wcfm_is_booking() ) {
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_vendor_booking', array(  "manage_booking" => array('label' => __('Manage Bookings', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_booking),
																											) ) );
				}
				
				if( wcfm_is_subscription() || wcfm_is_xa_subscription() ) {
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_vendor_subscription', array(  "manage_subscription" => array('label' => __('Manage Subscriptions', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_subscription),
																											) ) );
				}
				
				if( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() ) {
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_vendor_listings', array(  "associate_listings" => array('label' => __('Listings', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'desc' => __( 'by WP Job Manager.', 'wc-frontend-manager' ), 'dfvalue' => $associate_listings),
																											) ) );
				}
				
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_vendor_orders', array(  "view_orders" => array('label' => __('View Orders', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_orders),
																																																									 "order_status_update" => array('label' => __('Status Update', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $order_status_update),
																										) ) );
				
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_vendor_reports', array("view_reports" => array('label' => __('View Reports', 'wc-frontend-manager') , 'type' => 'checkboxoffon', 'in_table' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_reports),
																										 ) ) );
			  ?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'wc-frontend-manager'); ?>" name="save_step" />
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wc-frontend-manager'); ?></a>
				<?php wp_nonce_field('wcfm-setup'); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Ready to go content
	 */
	public function wcfm_setup_ready() {
		global $WCFM;
		$is_marketplace = wcfm_is_marketplace();
		?>
		<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo site_url(); ?>" data-text="Hey Guys! Our new e-commerce store is now live and ready to be ransacked! Check it out at" data-via="wcfmlovers" data-size="large">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		<h1><?php esc_html_e('We are done!', 'wc-frontend-manager'); ?></h1>
		<div class="woocommerce-message woocommerce-tracker">
		 <?php if( $is_marketplace && ( $is_marketplace  == 'wcfmmarketplace' ) ) { ?>
				<p><?php esc_html_e("Your marketplace is ready. It's time to experience the things more Easily and Peacefully. Also you will be a bit more relax than ever before, have fun!!", 'wc-frontend-manager') ?></p>
			<?php } else { ?>
				<p><?php esc_html_e("Your front-end dashboard is ready. It's time to experience the things more Easily and Peacefully. Also you will be a bit more relax than ever before, have fun!!", 'wc-frontend-manager') ?></p>
			<?php } ?>
		</div>
		<div class="wc-setup-next-steps">
				<div class="wc-setup-next-steps-first">
						<h2><?php esc_html_e( 'Next steps', 'wc-frontend-manager' ); ?></h2>
						<ul>
								<li class="setup-product"><a class="button button-primary button-large" href="<?php echo esc_url( get_wcfm_url() ); ?>"><?php esc_html_e( "Let's go to Dashboard", 'wc-frontend-manager' ); ?></a></li>
						</ul>
				</div>
				<div class="wc-setup-next-steps-last">
						<h2><?php _e( 'Learn more', 'wc-frontend-manager' ); ?></h2>
						<ul>
								<li class="video-walkthrough"><a target="_blank" href="https://www.youtube.com/channel/UCJ0c60fv3l1K9mBbHdmR-5Q"><?php esc_html_e( 'Watch the tutorial videos', 'wc-frontend-manager' ); ?></a></li>
								<li class="knowledgebase"><a target="_blank" href="https://wclovers.com/blog/woocommerce-frontend-manager/"><?php esc_html_e( 'WCFM - What & Why?', 'wc-frontend-manager' ); ?></a></li>
								<li class="learn-more"><a target="_blank" href="http://wclovers.com/blog/choose-best-woocommerce-multi-vendor-marketplace-plugin/"><?php esc_html_e( 'Choose your multi-vendor plugin', 'wc-frontend-manager' ); ?></a></li>
						</ul>
				</div>
		</div>
		<?php
	}

	/**
	 * Save dashboard settings
	 */
	public function wcfm_setup_dashboard_save() {
		global $WCFM;
		check_admin_referer('wcfm-setup');
		
		$options = get_option( 'wcfm_options' );
		
		$dashboard_full_view_disabled = filter_input(INPUT_POST, 'dashboard_full_view_disabled');
		$dashboard_theme_header_disabled = filter_input(INPUT_POST, 'dashboard_theme_header_disabled');
		$slick_menu_disabled = filter_input(INPUT_POST, 'slick_menu_disabled');
		$headpanel_disabled = filter_input(INPUT_POST, 'headpanel_disabled');
		$checklist_view_disabled = filter_input(INPUT_POST, 'checklist_view_disabled');
		
		$welcome_box_disabled = filter_input(INPUT_POST, 'welcome_box_disabled');
		$quick_access_disabled = filter_input(INPUT_POST, 'quick_access_disabled');
		$responsive_float_menu_disabled = filter_input(INPUT_POST, 'responsive_float_menu_disabled');
		$float_button_disabled = filter_input(INPUT_POST, 'float_button_disabled');
		
		// Menu Disabled
		if( !$dashboard_full_view_disabled ) $options['dashboard_full_view_disabled'] = 'no';
		else $options['dashboard_full_view_disabled'] = 'yes';
		
		// Theme Header Disabled
		if( !$dashboard_theme_header_disabled ) $options['dashboard_theme_header_disabled'] = 'no';
		else $options['dashboard_theme_header_disabled'] = 'yes';
		
		// Slick Menu Disabled
		if( !$slick_menu_disabled ) $options['slick_menu_disabled'] = 'no';
		else $options['slick_menu_disabled'] = 'yes';
		
		// Header Panel Disabled
		if( !$headpanel_disabled ) $options['headpanel_disabled'] = 'no';
		else $options['headpanel_disabled'] = 'yes';
		
		// Taxonomy Checklist view Disabled
		if( !$checklist_view_disabled ) $options['checklist_view_disabled'] = 'no';
		else $options['checklist_view_disabled'] = 'yes';
		
		// Dashboard Welcome Box Disabled
		if( !$welcome_box_disabled ) $options['welcome_box_disabled'] = 'no';
		else $options['welcome_box_disabled'] = 'yes';
		
		// WCFM Quick Access Disabled
		if( !$quick_access_disabled ) $options['quick_access_disabled'] = 'no';
		else $options['quick_access_disabled'] = 'yes';
		
		// Responsive Float Menu Disabled
		if( !$responsive_float_menu_disabled ) $options['responsive_float_menu_disabled'] = 'no';
		else $options['responsive_float_menu_disabled'] = 'yes';
		
		// Float Button Disabled
		if( !$float_button_disabled ) $options['float_button_disabled'] = 'no';
		else $options['float_button_disabled'] = 'yes';
		
		$options['module_options']['buddypress'] = 'yes';
		
		update_option( 'wcfm_options', $options );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save marketplace settings
	 */
	public function wcfm_setup_marketplace_save() {
		global $WCFM;
		check_admin_referer('wcfm-setup');
		
		$wcfm_marketplace_options = get_option( 'wcfm_marketplace_options', array() );
		
		$vendor_store_url = filter_input(INPUT_POST, 'vendor_store_url');
		$vendor_sold_by = filter_input(INPUT_POST, 'vendor_sold_by');
		$vendor_sold_by_template = filter_input(INPUT_POST, 'vendor_sold_by_template');
		$vendor_sold_by_position = filter_input(INPUT_POST, 'vendor_sold_by_position');
		$store_name_position = filter_input(INPUT_POST, 'store_name_position');
		$store_sidebar = filter_input(INPUT_POST, 'store_sidebar');
		$product_mulivendor = filter_input(INPUT_POST, 'product_mulivendor');
		$enable_marketplace_shipping = filter_input(INPUT_POST, 'enable_marketplace_shipping');
		$wcfm_google_map_api = filter_input(INPUT_POST, 'wcfm_google_map_api');
		
		$wcfm_marketplace_options['wcfm_store_url'] = $vendor_store_url;
		update_option( 'wcfm_store_url', $vendor_store_url );
		
		if( !$vendor_sold_by ) $wcfm_marketplace_options['vendor_sold_by'] = 'yes';
		else $wcfm_marketplace_options['vendor_sold_by'] = 'no';
		
		$wcfm_marketplace_options['vendor_sold_by_template'] = $vendor_sold_by_template;
		
		$wcfm_marketplace_options['vendor_sold_by_position'] = $vendor_sold_by_position;
		
		$wcfm_marketplace_options['store_name_position'] = $store_name_position;
		
		if( !$store_sidebar ) $wcfm_marketplace_options['store_sidebar'] = 'yes';
		else $wcfm_marketplace_options['store_sidebar'] = 'no';
		
		if( !$product_mulivendor ) $wcfm_marketplace_options['product_mulivendor'] = 'yes';
		else $wcfm_marketplace_options['product_mulivendor'] = 'no';
		
		$wcfm_marketplace_options['wcfm_google_map_api'] = $wcfm_google_map_api;
		
		update_option( 'wcfm_marketplace_options', $wcfm_marketplace_options );
		
		if( !$enable_marketplace_shipping ) $enable_marketplace_shipping = 'yes';
		else $enable_marketplace_shipping = 'no';
		
		$wcfmmp_marketplace_shipping_options = get_option( 'woocommerce_wcfmmp_product_shipping_by_country_settings', array() );
    $wcfmmp_marketplace_shipping_options['enabled'] = $enable_marketplace_shipping;
    update_option( 'woocommerce_wcfmmp_product_shipping_by_country_settings', $wcfmmp_marketplace_shipping_options );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save commission settings
	 */
	public function wcfm_setup_commission_save() {
		global $WCFM;
		check_admin_referer('wcfm-setup');
		
		$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
		
		$vendor_commission_for = filter_input(INPUT_POST, 'vendor_commission_for');
		$vendor_commission_mode = filter_input(INPUT_POST, 'vendor_commission_mode');
		$vendor_commission_fixed = filter_input(INPUT_POST, 'vendor_commission_fixed');
		$vendor_commission_percent = filter_input(INPUT_POST, 'vendor_commission_percent');
		
		$vendor_get_shipping = filter_input(INPUT_POST, 'vendor_get_shipping');
		$vendor_get_tax = filter_input(INPUT_POST, 'vendor_get_tax');
		$vendor_coupon_deduct = filter_input(INPUT_POST, 'vendor_coupon_deduct');
		
		if( $vendor_commission_for ) {
			$wcfm_commission_options['commission_for'] = $vendor_commission_for;
		}
		
		if( $vendor_commission_mode ) {
			$wcfm_commission_options['commission_mode'] = $vendor_commission_mode;
		}
		
		if( $vendor_commission_fixed ) {
			$wcfm_commission_options['commission_fixed'] = $vendor_commission_fixed;
		}
		
		if( $vendor_commission_percent ) {
			$wcfm_commission_options['commission_percent'] = $vendor_commission_percent;
		}
		
		if( !$vendor_get_shipping ) $wcfm_commission_options['get_shipping'] = 'yes';
		else $wcfm_commission_options['get_shipping'] = 'no';
		
		if( !$vendor_get_tax ) $wcfm_commission_options['get_tax'] = 'yes';
		else $wcfm_commission_options['get_tax'] = 'no';
		
		if( !$vendor_coupon_deduct ) $wcfm_commission_options['coupon_deduct'] = 'yes';
		else $wcfm_commission_options['coupon_deduct'] = 'no';
		
		update_option( 'wcfm_commission_options', $wcfm_commission_options );
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save withdrawal settings
	 */
	public function wcfm_setup_withdrawal_save() {
		global $WCFM;
		check_admin_referer('wcfm-setup');
		
		$wcfm_withdrawal_options = get_option( 'wcfm_withdrawal_options', array() );
		
		if( isset( $_POST['wcfm_withdrawal_options'] ) ) {
			update_option( 'wcfm_withdrawal_options', wc_clean( $_POST['wcfm_withdrawal_options'] ) );
		}
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}
	
	/**
	 * Save dashboard style settings
	 */
	public function wcfm_setup_style_save() {
		global $WCFM;
		check_admin_referer('wcfm-setup');
		
		$options = get_option( 'wcfm_options' );
		
		$color_options = $WCFM->wcfm_color_setting_options();
		foreach( $color_options as $color_option_key => $color_option ) {
			$color_value = filter_input( INPUT_POST, $color_option['name'] );
			if( $color_value ) { $options[$color_option['name']] = $color_value; } else { $options[$color_option['name']] = $color_option['default']; }
		}
		
		update_option( 'wcfm_options', $options );
		
		// Init WCFM Custom CSS file
		$wcfm_style_custom = $WCFM->wcfm_create_custom_css();
		
		wp_redirect(esc_url_raw($this->get_next_step_link()));
		exit;
	}

	/**
	 * save capability settings
	 * @global object $WCFM
	 */
	public function wcfm_setup_capability_save() {
			global $WCFM;
			check_admin_referer('wcfm-setup');

			$wcfm_capability_options = (array) get_option( 'wcfm_capability_options' );
			
			$vnd_wpadmin = filter_input(INPUT_POST, 'vnd_wpadmin');
			
			$submit_products = filter_input(INPUT_POST, 'submit_products');
			$publish_products = filter_input(INPUT_POST, 'publish_products');
			$edit_live_products = filter_input(INPUT_POST, 'edit_live_products');
			$delete_products = filter_input(INPUT_POST, 'delete_products');
			
			$manage_booking = filter_input(INPUT_POST, 'manage_booking');
			$manage_subscription = filter_input(INPUT_POST, 'manage_subscription');
			$associate_listings = filter_input(INPUT_POST, 'associate_listings');
			
			$view_orders = filter_input(INPUT_POST, 'view_orders');
			$view_reports = filter_input(INPUT_POST, 'view_reports');
			
			if( !$vnd_wpadmin ) $wcfm_capability_options['vnd_wpadmin'] = 'no';
			else $wcfm_capability_options['vnd_wpadmin'] = 'yes';
			
			if( !$submit_products ) $wcfm_capability_options['submit_products'] = 'no';
			else $wcfm_capability_options['submit_products'] = 'yes';
			
			if( !$publish_products ) $wcfm_capability_options['publish_products'] = 'no';
			else $wcfm_capability_options['publish_products'] = 'yes';
			
			if( !$edit_live_products ) $wcfm_capability_options['edit_live_products'] = 'no';
			else $wcfm_capability_options['edit_live_products'] = 'yes';
			
			if( !$delete_products ) $wcfm_capability_options['delete_products'] = 'no';
			else $wcfm_capability_options['delete_products'] = 'yes';
			
			if( !$manage_booking ) $wcfm_capability_options['manage_booking'] = 'no';
			else $wcfm_capability_options['manage_booking'] = 'yes';
			
			if( !$manage_subscription ) $wcfm_capability_options['manage_subscription'] = 'no';
			else $wcfm_capability_options['manage_subscription'] = 'yes';
			
			if( !$associate_listings ) $wcfm_capability_options['associate_listings'] = 'no';
			else $wcfm_capability_options['associate_listings'] = 'yes';
			
			if( !$view_orders ) $wcfm_capability_options['view_orders'] = 'no';
			else $wcfm_capability_options['view_orders'] = 'yes';
			
			if( !$view_reports ) $wcfm_capability_options['view_reports'] = 'no';
			else $wcfm_capability_options['view_reports'] = 'yes';
			
			update_option( 'wcfm_capability_options', $wcfm_capability_options );
			
			$WCFM->wcfm_vendor_support->vendors_capability_option_updates();

			wp_redirect(esc_url_raw($this->get_next_step_link()));
			exit;
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function dashboard_setup_footer() {
			if ('next_steps' === $this->step) :
					?>
					<a class="wc-return-to-dashboard" href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('Return to the WordPress Dashboard', 'wc-frontend-manager'); ?></a>
	<?php endif; ?>
			</body>
	</html>
	<?php
	}
}

new WCFM_Dashboard_Setup();
