<?php
/**
 * WCFM Marketplace Setup Class
 * 
 * @since 1.0.0
 * @package wcfmmp/helpers
 * @author WC Lovers
 */
if (!defined('ABSPATH')) {
    exit;
}

class WCFMmp_Marketplace_Setup {

	/** @var string Currenct Step */
	private $step = '';

	/** @var array Steps for the setup wizard */
	private $steps = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wcfm_admin_menus' ) );
		add_action( 'admin_init', array( $this, 'wcfmmp_dashboard_setup' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function wcfm_admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wcfmmp-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function wcfmmp_dashboard_setup() {
		global $WCFMmp;
		if ( filter_input(INPUT_GET, 'page') != 'wcfmmp-setup') {
			return;
		}

		if (!WCFMmp_Dependencies::woocommerce_plugin_active_check()) {
			if (isset($_POST['wcfmmp_install_woocommerce'])) {
				$this->install_woocommerce();
			}
			$this->install_woocommerce_view();
			exit();
		}
		if (!WCFMmp_Dependencies::wcfm_plugin_active_check()) {
			if (isset($_POST['wcfmmp_install_wcfm'])) {
				$this->install_wcfm();
			}
			$this->install_wcfm_view();
			exit();
		}
		if (!WCFMmp_Dependencies::wcfmvm_plugin_active_check()) {
			if (isset($_POST['wcfmmp_install_wcfmvm'])) {
				$this->install_wcfmvm();
			}
			$this->install_wcfmvm_view();
			exit();
		}
		
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
		wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array('dashicons', 'install'), WC_VERSION);
		//wp_enqueue_style( 'wcfm-setup', $WCFMmp->plugin_url . '/assets/css/setup/wcfm-style-dashboard-setup.css', array('wc-setup'), $WCFMmp->version );
		wp_register_script('wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup' . $suffix . '.js', array('jquery', 'wc-enhanced-select', 'jquery-blockui'), WC_VERSION);
		wp_localize_script('wc-setup', 'wc_setup_params', array(
				'locale_info' => json_encode(include( WC()->plugin_path() . '/i18n/locale-info.php' )),
		));
		
		wp_safe_redirect( admin_url( 'index.php?page=wcfm-setup' ) );
		exit();
	}

	/**
	 * Content for install woocommerce view
	 */
	public function install_woocommerce_view() {
		global $WCFMmp;
		
		set_current_screen();
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
					<head>
							<meta name="viewport" content="width=device-width" />
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title><?php esc_html_e('WCFM Marketplace &rsaquo; Setup Wizard', 'wc-multivendor-marketplace'); ?></title>
							<?php do_action('admin_print_styles'); ?>
							<?php do_action('admin_head'); ?>
							<style type="text/css">
									body {
											margin: 100px auto 24px;
											box-shadow: none;
											background: #f1f1f1;
											padding: 0;
											max-width: 700px;
									}
									#wc-logo {
											border: 0;
											margin: 0 0 24px;
											padding: 0;
											text-align: center;
									}
									#wc-logo a {
										color: #00897b;
										text-decoration: none;
									}
									
									#wc-logo a span {
										padding-left: 10px;
										padding-top: 23px;
										display: inline-block;
										vertical-align: top;
										font-weight: 700;
									}
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
									.wcfm-install-woocommerce p{
											line-height: 1.6;
									}

							</style>
					</head>
					<body class="wcfm-setup wp-core-ui">
							<h1 id="wc-logo"><a href="http://wclovers.com/"><img src="<?php echo $WCFMmp->plugin_url; ?>assets/images/wcfmmp-75x75.png" alt="WCFM" /><span>WCFM Marketplace</span></a></h1>
							<div class="wcfm-install-woocommerce">
									<p><?php _e('WCFM Marketplace requires WooCommerce plugin to be active!', 'wc-multivendor-marketplace'); ?></p>
									<form method="post" action="" name="wcfm_install_woocommerce">
											<?php submit_button(__('Install WooCommerce', 'wc-multivendor-marketplace'), 'primary', 'wcfmmp_install_woocommerce'); ?>
											<?php wp_nonce_field('wcfmmp-install-woocommerce'); ?>
									</form>
							</div>
					</body>
			</html>
			<?php
	}

	/**
	 * Install woocommerce if not exist
	 * @throws Exception
	 */
	public function install_woocommerce() {
		check_admin_referer('wcfmmp-install-woocommerce');
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		WP_Filesystem();
		$skin = new Automatic_Upgrader_Skin;
		$upgrader = new WP_Upgrader($skin);
		$installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
		$plugin_slug = 'woocommerce';
		$plugin = $plugin_slug . '/' . $plugin_slug . '.php';
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
							__('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WooCommerce', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=woocommerce'))
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
							__('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WooCommerce', admin_url('plugins.php')
				);
				exit();
			}
		}
		if( !WCFMmp_Dependencies::wcfm_plugin_active_check() || !WCFMmp_Dependencies::wcfmvm_plugin_active_check() ) {
			wp_safe_redirect(admin_url('index.php?page=wcfmmp-setup'));
		} else {
			wp_safe_redirect(admin_url('index.php?page=wcfm-setup'));
		}
	}
	
	/**
	 * Content for install wcfm membership view
	 */
	public function install_wcfmvm_view() {
		global $WCFMmp;
		
		set_current_screen();
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
					<head>
							<meta name="viewport" content="width=device-width" />
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title><?php esc_html_e('WCFM Marketplace &rsaquo; Setup Wizard', 'wc-multivendor-marketplace'); ?></title>
							<?php do_action('admin_print_styles'); ?>
							<?php do_action('admin_head'); ?>
							<style type="text/css">
									body {
											margin: 100px auto 24px;
											box-shadow: none;
											background: #f1f1f1;
											padding: 0;
											max-width: 700px;
									}
									#wc-logo {
											border: 0;
											margin: 0 0 24px;
											padding: 0;
											text-align: center;
									}
									#wc-logo a {
										color: #00897b;
										text-decoration: none;
									}
									
									#wc-logo a span {
										padding-left: 10px;
										padding-top: 23px;
										display: inline-block;
										vertical-align: top;
										font-weight: 700;
									}
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
									.wcfm-install-woocommerce p{
											line-height: 1.6;
									}

							</style>
					</head>
					<body class="wcfm-setup wp-core-ui">
						<h1 id="wc-logo"><a href="http://wclovers.com/"><img src="<?php echo $WCFMmp->plugin_url; ?>assets/images/wcfmmp-75x75.png" alt="WCFM" /><span>WCFM Marketplace</span></a></h1>
						<div class="wcfm-install-woocommerce">
							<p><?php _e('Setup WCFM Maketplace vendor registration:', 'wc-multivendor-marketplace'); ?></p>
							<form method="post" action="" name="wcfmmp_install_wcfm">
								<?php submit_button(__('Setup Registration', 'wc-multivendor-marketplace' ), 'primary', 'wcfmmp_install_wcfmvm'); ?>
								<?php wp_nonce_field('wcfmmp-install-wcfmvm'); ?>
							</form>
						</div>
					</body>
			</html>
			<?php
	}

	/**
	 * Install wcfm if not exist
	 * @throws Exception
	 */
	public function install_wcfmvm() {
		check_admin_referer('wcfmmp-install-wcfmvm');
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
						__('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WCFM Mebership', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=wc-multivendor-membership'))
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
					__('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WCFM Membership', admin_url('plugins.php')
				);
				exit();
			}
		}
		if( !WCFMmp_Dependencies::wcfm_plugin_active_check() ) {
			wp_safe_redirect(admin_url('index.php?page=wcfmmp-setup'));
		} else {
			wp_safe_redirect(admin_url('index.php?page=wcfm-setup'));
		}
	}
	
	/**
	 * Content for install wcfm view
	 */
	public function install_wcfm_view() {
		global $WCFMmp;
		
		set_current_screen();
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
					<head>
							<meta name="viewport" content="width=device-width" />
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title><?php esc_html_e('WCFM Marketplace &rsaquo; Setup Wizard', 'wc-multivendor-marketplace'); ?></title>
							<?php do_action('admin_print_styles'); ?>
							<?php do_action('admin_head'); ?>
							<style type="text/css">
									body {
											margin: 100px auto 24px;
											box-shadow: none;
											background: #f1f1f1;
											padding: 0;
											max-width: 700px;
									}
									#wc-logo {
											border: 0;
											margin: 0 0 24px;
											padding: 0;
											text-align: center;
									}
									#wc-logo a {
										color: #00897b;
										text-decoration: none;
									}
									
									#wc-logo a span {
										padding-left: 10px;
										padding-top: 23px;
										display: inline-block;
										vertical-align: top;
										font-weight: 700;
									}
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
									.wcfm-install-woocommerce p{
											line-height: 1.6;
									}

							</style>
					</head>
					<body class="wcfm-setup wp-core-ui">
						<h1 id="wc-logo"><a href="http://wclovers.com/"><img src="<?php echo $WCFMmp->plugin_url; ?>assets/images/wcfmmp-75x75.png" alt="WCFM" /><span>WCFM Marketplace</span></a></h1>
						<div class="wcfm-install-woocommerce">
							<p><?php _e('WCFM Maketplace requires WCFM Core plugin to be active!', 'wc-multivendor-marketplace'); ?></p>
							<form method="post" action="" name="wcfmmp_install_wcfm">
								<?php submit_button(__('Install WCFM Core', 'wc-multivendor-marketplace' ), 'primary', 'wcfmmp_install_wcfm'); ?>
								<?php wp_nonce_field('wcfmmp-install-wcfm'); ?>
							</form>
						</div>
					</body>
			</html>
			<?php
	}

	/**
	 * Install wcfm if not exist
	 * @throws Exception
	 */
	public function install_wcfm() {
		check_admin_referer('wcfmmp-install-wcfm');
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		WP_Filesystem();
		$skin = new Automatic_Upgrader_Skin;
		$upgrader = new WP_Upgrader($skin);
		$installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
		$plugin_slug = 'wc-frontend-manager';
		$plugin = 'wc-frontend-manager/wc_frontend_manager.php';
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
						__('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WC Frontend Manager', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=wc-frontend-manager'))
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
							__('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WC Frontend Manager', admin_url('plugins.php')
						);
						exit();
				}
		}
		if( !WCFMmp_Dependencies::wcfmvm_plugin_active_check() ) {
			wp_safe_redirect(admin_url('index.php?page=wcfmmp-setup'));
		} else {
			wp_safe_redirect(admin_url('index.php?page=wcfm-setup'));
		}
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
}

new WCFMmp_Marketplace_Setup();
