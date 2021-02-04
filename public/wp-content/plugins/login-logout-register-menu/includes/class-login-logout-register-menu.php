<?php

/**
 * The Login Logout Register Menu is the core plugin class responsible for including and
 * instantiating all of the code that composes the plugin
 *
 * @package LLRM
 */

/**
 * The Login Logout Register Menu is the core plugin responsible for including and
 * instantiating all of the code that composes the plugin.
 *
 * The Login Logout Register Menu includes an instance to the Login Logout Register Menu
 * Loader which is responsible for coordinating the hooks that exist within the
 * plugin.
 *
 * It also maintains a reference to the plugin name which can be used in
 * internationalization, and a reference to the current version of the plugin
 * so that we can easily update the version in a single place to provide
 * cache busting functionality when including scripts and styles.
 *
 * @since    1.0.0
 */

class Login_Logout_Register_Menu {


	/**
	 * Global plugin option.
	 */
	 public $options;

	/**
	 * A reference to the loader class that coordinates the hooks and callbacks
	 * throughout the plugin.
	 *
	 * @access protected
	 * @var    Login_Logout_Register_Menu_Loader   $loader    Manages hooks between the WordPress hooks and the callback functions.
	 */
	protected $loader;

	/**
	 * Represents the name of hte plugin that can be used throughout the plugin
	 * for internationalization and other purposes.
	 *
	 * @access protected
	 * @var    string   $plugin_name    The single, hyphenated string used to identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * Maintains the current version of the plugin so that we can use it throughout
	 * the plugin.
	 *
	 * @access protected
	 * @var    string   $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * Instantiates the plugin by setting up the core properties and loading
	 * all necessary dependencies and defining the hooks.
	 *
	 * The constructor will define both the plugin name and the verison
	 * attributes, but will also use internal functions to import all the
	 * plugin dependencies, and will leverage the Login Logout Register Menu for
	 * registering the hooks and the callback functions used throughout the
	 * plugin.
	 */
	public function __construct() {

		$this->plugin_name = 'login-logout-register-menu';
		$this->version = '1.0.0';
		$this->options = get_option( 'llrm' );

		$this->load_dependencies();
		$this->set_locale();

		if ( is_admin() ) {
			$this->define_admin_hooks();
		} else {
			$this->define_public_hooks();
		}
	}


	/**
	 * PHP 4 Compatible Constructor
	 *
	 */
	function Login_Logout_Register_Menu() {
		$this->__construct();
	}


	/**
	 * Imports the Login Logout Register Menu administration classes, and the Login Logout Register Menu Loader.
	 *
	 * The Login Logout Register Menu Manager administration class defines all unique functionality for
	 * introducing custom functionality into the WordPress dashboard.
	 *
	 * The Login Logout Register Menu Loader is the class that will coordinate the hooks and callbacks
	 * from WordPress and the plugin. This function instantiates and sets the reference to the
	 * $loader class property.
	 *
	 * @access    private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-login-logout-register-menu-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-login-logout-register-menu-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-login-logout-register-menu-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the front end of site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-login-logout-register-menu-public.php';

		$this->loader = new Login_Logout_Register_Menu_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Login_Logout_Register_Menu_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Defines the hooks and callback functions that are used for setting up the plugin stylesheets
	 * and the plugin's admin options.
	 *
	 * This function relies on the Login Logout Register Menu Admin class and the Login Logout Register Menu
	 * Loader class property.
	 *
	 * @access    private
	 */
	private function define_admin_hooks() {

		$admin = new Login_Logout_Register_Menu_Admin( $this->get_version() );

		$options = $this->options;

		if ( ! isset( $options['dismiss_admin_notices'] ) || ! $options['dismiss_admin_notices'] ) {
			$this->loader->add_action( 'all_admin_notices', $admin, 'llrm_setup_notice' );
		}
		$this->loader->add_action( 'plugin_action_links', $admin, 'llrm_settings_link', 10, 2 );
		$this->loader->add_action( 'admin_menu', $admin, 'llrm_admin_menu_setup' ); //menu setup
		$this->loader->add_action( 'wp_ajax_nopriv_llrm_notice_dismiss', $admin, 'llrm_notice_dismiss' );
		$this->loader->add_action( 'wp_ajax_llrm_notice_dismiss', $admin, 'llrm_notice_dismiss' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'llrm_load_admin_assets' );
		$this->loader->add_action( 'admin_init', $admin, 'llrm_settings_init' );
		$this->loader->add_action( 'admin_head-nav-menus.php', $admin, 'add_llrm_metabox' );
	}

	/**
	 * Defines the hooks and callback functions that are used for executing plugin functionality
	 * in the front end of site.
	 *
	 * This function relies on the Login Logout Register Menu Admin class and the Login Logout Register Menu
	 * Loader class property.
	 *
	 * @access    private
	 */
	private function define_public_hooks() {

		$public = new Login_Logout_Register_Menu_Public( $this->get_version() );
		$this->loader->add_filter( 'wp_setup_nav_menu_item', $public, 'llrm_setup_nav_menu_item' );
		$this->loader->add_filter( 'wp_nav_menu_objects', $public, 'llrm_wp_nav_menu_objects' );
		add_shortcode( 'llrmlogin', array( $public, 'llrm_shortcode_login' ) );
		add_shortcode( 'llrmloginlogout', array( $public, 'llrm_shortcode_loginlogout' ) );
		add_shortcode( 'llrmlogout', array( $public, 'llrm_shortcode_logout' ) );
		add_shortcode( 'llrmregister', array( $public, 'llrm_shortcode_register' ) );
		add_shortcode( 'llrmprofile', array( $public, 'llrm_shortcode_profile' ) );
		$this->loader->add_filter( 'registration_redirect', $public, 'llrm_redirect_after_registration', 99 );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * Sets this class into motion.
	 *
	 * Executes the plugin by calling the run method of the loader class which will
	 * register all of the hooks and callback functions used throughout the plugin
	 * with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}


	/**
	 * Returns the current version of the plugin to the caller.
	 *
	 * @return    string    $this->version    The current version of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}