<?php

/**
 * The Login Logout Register Menu Admin defines all functionality for the dashboard
 * of the plugin.
 *
 * This class defines the meta box used to display the post meta data and registers
 * the style sheet responsible for styling the content of the meta box.
 *
 * @package LLRM
 * @since    1.0.0
 */
class Login_Logout_Register_Menu_Admin {

	/**
	 * Global plugin option.
	 */
	public $options;

	/**
	 * A reference to the version of the plugin that is passed to this class from the caller.
	 *
	 * @access private
	 * @var    string    $version    The current version of the plugin.
	 */
	private $version;


	/**
	 * are we network activated?
	 */
	private $networkactive;

	/**
	 * Initializes this class and stores the current version of this plugin.
	 *
	 * @param    string    $version    The current version of this plugin.
	 */
	public function __construct( $version ) {
		$this->version = $version;
		$this->options = get_option( 'llrm' );
		$this->networkactive = ( is_multisite() && array_key_exists( plugin_basename( __FILE__ ), (array) get_site_option( 'active_sitewide_plugins' ) ) );
	}

	/**
	 * PHP 4 Compatible Constructor
	 *
	 */
	function Login_Logout_Register_Menu_Admin() {
		$this->__construct();
	}

	/**
	 * Loads plugin javascript and stylesheet files in the admin area
	 *
	 */
	function llrm_load_admin_assets(){

		wp_register_script( 'login-logout-register-menu-scripts', plugins_url( '/js/login-logout-register-menu-admin.js', __FILE__ ), array( 'jquery' ), '1.0', true  );

		wp_localize_script( 'login-logout-register-menu-scripts', 'llrm', array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		));

		// Enqueued script with localized data.
		wp_enqueue_script( 'login-logout-register-menu-scripts' );
	}

	/**
	 * Add a link to the settings page to the plugins list
	 *
	 * @param array  $links array of links for the plugins, adapted when the current plugin is found.
	 * @param string $file  the filename for the current plugin, which the filter loops through.
	 *
	 * @return array $links
	 */
	function llrm_settings_link( $links, $file ) {

		if ( false !== strpos( $file, 'login-logout-register-menu' ) ){
			$mylinks = array(
				'<a href="http://freewptp.com/forum/wordpress-plugins-forum/login-logout-register-menu/">' . __( 'Get Support', 'login-logout-register-menu' ) . '</a>',
				'<a href="options-general.php?page=llrm">' . __( 'Settings', 'login-logout-register-menu' ) . '</a>'
			);
			$links = array_merge( $mylinks, $links );
		}
		return $links;
	}

	/**
	 * Displays plugin configuration notice in admin area
	 *
	 */
	function llrm_setup_notice(){

		if ( strpos( get_current_screen()->id, 'settings_page_llrm' ) === 0 )
			return;

		$hascaps = $this->networkactive ? is_network_admin() && current_user_can( 'manage_network_plugins' ) : current_user_can( 'manage_options' );

		if ( $hascaps ) {
			$url = is_network_admin() ? network_site_url() : site_url( '/' );
			echo '<div class="notice notice-info is-dismissible login-logout-register-menu"><p>' . sprintf( __( 'To configure <em>Login Logout Register Menu plugin</em> please visit its <a href="%1$s">configuration page</a> and to get plugin support contact us on <a href="%2$s">plugin support forum</a> or <a href="%3$s">contact us page</a>.', 'login-logout-register-menu' ), $url . 'wp-admin/options-general.php?page=llrm', 'http://freewptp.com/forum/wordpress-plugins-forum/login-logout-register-menu/', 'http://freewptp.com/contact/' ) . '</p></div>';
		}
	}

	/**
	 * Handles plugin notice dismiss functionality using AJAX
	 *
	 */
	function llrm_notice_dismiss() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$options = $this->options;
			$options['dismiss_admin_notices'] = 1;
			update_option( 'llrm', $options );
		}
		die();
	}

	/* Registers menu item */
	function llrm_admin_menu_setup(){
		add_submenu_page( 'options-general.php', __( 'Login Logout Register Menu Settings', 'login-logout-register-menu' ), __( 'Login Logout Register', 'login-logout-register-menu' ), 'manage_options', 'llrm', array( $this, 'llrm_admin_page_screen' ) );
	}

	/* Displays plugin admin page content */
	function llrm_admin_page_screen() { ?>
		<div class="wrap">
			<form id="llrm_options" action="options.php" method="post">
				<?php
					settings_fields( 'llrm' );
					do_settings_sections( 'llrm' );
					submit_button( 'Save Options', 'primary', 'llrm_options_submit' );
				?>
				<div id="after-submit">
					<p>
						<?php esc_html_e( 'Like Login Logout Register Menu?', 'login-logout-register-menu' ); ?> <a href="https://wordpress.org/support/plugin/login-logout-register-menu/reviews/?filter=5#new-post" target="_blank"><?php esc_html_e( 'Give us a rating', 'login-logout-register-menu' ); ?></a>
					</p>
					<p>
						<?php esc_html_e( 'Need Help or Have Suggestions?', 'login-logout-register-menu' ); ?> <?php esc_html_e( "contact us on", 'login-logout-register-menu' ); ?> <a href="http://freewptp.com/forum/wordpress-plugins-forum/login-logout-register-menu/" target="_blank"><?php esc_html_e( 'Plugin support forum', 'login-logout-register-menu' ); ?></a> <?php esc_html_e( "or", 'login-logout-register-menu' ); ?> <a href="http://freewptp.com/contact/" target="_blank"><?php esc_html_e( 'Contact us page', 'login-logout-register-menu' ); ?></a>
					</p>
					<p>
						<?php esc_html_e( 'Access Plugin Documentation on', 'login-logout-register-menu' ); ?> <a href="http://freewptp.com/plugins/login-logout-register-menu/" target="_blank">http://freewptp.com/plugins/login-logout-register-menu/</a>
					</p>
				</div>
			 </form>
		</div>
		<?php
	}

	/* Registers settings */
	function llrm_settings_init(){

		add_settings_section( 'llrm_section', __( 'Login Logout Register Menu Settings', 'login-logout-register-menu' ),  array( $this, 'llrm_section_desc' ), 'llrm' );

		add_settings_field( 'llrm_login_url', __( 'Login URL : ', 'login-logout-register-menu' ),  array( $this, 'llrm_login_url' ), 'llrm', 'llrm_section' );
		add_settings_field( 'llrm_register_url', __( 'Register URL : ', 'login-logout-register-menu' ),  array( $this, 'llrm_register_url' ), 'llrm', 'llrm_section' );
		add_settings_field( 'llrm_profile_url', __( 'Profile URL : ', 'login-logout-register-menu' ),  array( $this, 'llrm_profile_url' ), 'llrm', 'llrm_section' );
		add_settings_field( 'llrm_login_redirection', __( 'Login Redirection URL : ', 'login-logout-register-menu' ),  array( $this, 'llrm_login_redirection' ), 'llrm', 'llrm_section' );
		add_settings_field( 'llrm_logout_redirection', __( 'Logout Redirection URL : ', 'login-logout-register-menu' ),  array( $this, 'llrm_logout_redirection' ), 'llrm', 'llrm_section' );
		add_settings_field( 'llrm_register_redirection', __( 'Register Redirection URL : ', 'login-logout-register-menu' ),  array( $this, 'llrm_register_redirection' ), 'llrm', 'llrm_section' );
		add_settings_field( 'llrm_hide_on_mobile', __( 'Hide Links on Mobile : ', 'login-logout-register-menu' ),  array( $this, 'llrm_hide_on_mobile' ), 'llrm', 'llrm_section' );

		register_setting( 'llrm', 'llrm' );

	}

	/* Displays plugin description text */
	function llrm_section_desc(){
		echo '<p>' . __( 'Configure the Login Logout Register Menu plugin settings here.', 'login-logout-register-menu' ) . '</p>';
	}

	/* login logout register menu login url field output */
	function llrm_login_url() {

		$options = $this->options;
		$options['llrm_login_url'] = isset( $options['llrm_login_url'] ) ? $options['llrm_login_url'] : ''; ?>
		<input type="text" id="llrm_login_url" name="llrm[llrm_login_url]" value="<?php esc_attr_e( $options['llrm_login_url'] ); ?>" size="50" />
		<br /><label for="llrm_login_url" style="font-size: 10px;"><?php esc_html_e( 'Only use this field if your site uses custom login URL.', 'login-logout-register-menu' ); ?></label>
	<?php }

	/* login logout register menu register url field output */
	function llrm_register_url() {

		$options = $this->options;
		$options['llrm_register_url'] = isset( $options['llrm_register_url'] ) ? $options['llrm_register_url'] : ''; ?>
		<input type="text" id="llrm_register_url" name="llrm[llrm_register_url]" value="<?php esc_attr_e( $options['llrm_register_url'] ); ?>" size="50" />
		<br /><label for="llrm_register_url" style="font-size: 10px;"><?php esc_html_e( 'Only use this field if your site uses custom register URL.', 'login-logout-register-menu' ); ?></label>
	<?php }

	/* login logout register menu profile url field output */
	function llrm_profile_url() {

		$options = $this->options;
		$options['llrm_profile_url'] = isset( $options['llrm_profile_url'] ) ? $options['llrm_profile_url'] : ''; ?>
		<input type="text" id="llrm_profile_url" name="llrm[llrm_profile_url]" value="<?php esc_attr_e( $options['llrm_profile_url'] ); ?>" size="50" />
		<br /><label for="llrm_profile_url" style="font-size: 10px;"><?php esc_html_e( 'Only use this field if your site uses custom profile URL.', 'login-logout-register-menu' ); ?></label>
	<?php }

	/* login logout register menu login redirection field output */
	function llrm_login_redirection() {

		$options = $this->options;
		$options['llrm_login_redirection'] = isset( $options['llrm_login_redirection'] ) ? $options['llrm_login_redirection'] : ''; ?>
		<input type="text" id="llrm_login_redirection" name="llrm[llrm_login_redirection]" value="<?php esc_attr_e( $options['llrm_login_redirection'] ); ?>" size="50" />
		<br /><label for="llrm_login_redirection" style="font-size: 10px;"><?php esc_html_e( 'Add URL where you want users to redirect after logging in.', 'login-logout-register-menu' ); ?></label>
	<?php }

	/* login logout register menu logout redirection field output */
	function llrm_logout_redirection() {

		$options = $this->options;
		$options['llrm_logout_redirection'] = isset( $options['llrm_logout_redirection'] ) ? $options['llrm_logout_redirection'] : ''; ?>
		<input type="text" id="llrm_logout_redirection" name="llrm[llrm_logout_redirection]" value="<?php esc_attr_e( $options['llrm_logout_redirection'] ); ?>" size="50" />
		<br /><label for="llrm_logout_redirection" style="font-size: 10px;"><?php esc_html_e( 'Add URL where you want users to redirect after log out.', 'login-logout-register-menu' ); ?></label>
	<?php }

	/* login logout register menu register redirection field output */
	function llrm_register_redirection() {

		$options = $this->options;
		$options['llrm_register_redirection'] = isset( $options['llrm_register_redirection'] ) ? $options['llrm_register_redirection'] : ''; ?>
		<input type="text" id="llrm_register_redirection" name="llrm[llrm_register_redirection]" value="<?php esc_attr_e( $options['llrm_register_redirection'] ); ?>" size="50" />
		<br /><label for="llrm_register_redirection" style="font-size: 10px;"><?php esc_html_e( 'Add URL where you want users to redirect after registration.', 'login-logout-register-menu' ); ?></label>
	<?php }

	 /* login logout register menu hide on mobile field output */
	function llrm_hide_on_mobile() {

		$options = $this->options;

		$check_value = isset( $options['llrm_hide_on_mobile'] ) ? $options['llrm_hide_on_mobile'] : 0; ?>
		<input type="checkbox" id="llrm_hide_on_mobile" name="llrm[llrm_hide_on_mobile]" value="hide-on-mobile" <?php echo checked( 'hide-on-mobile', $check_value, false ); ?>/>
		<label for="llrm_hide_on_mobile"> <?php esc_html_e( 'Toggle Display', 'login-logout-register-menu' ); ?></label>

		<br /><label for="llrm_title" style="font-size: 10px;"><?php esc_html_e( 'If checked, the links added in menu don\'t display on mobile devices.', 'login-logout-register-menu' ); ?></label>
		<br /><br />

	<?php }

	/* Registers Login/Logout/Register Links Metabox */
	function add_llrm_metabox() {
		add_meta_box( 'llrm', __( 'Login / Logout / Register links', 'login-logout-register-menu' ), array( $this, 'llrm_metabox' ), 'nav-menus', 'side', 'default' );
	}

	/* Displays Login/Logout/Register Links Metabox */
	function llrm_metabox() {
		global $nav_menu_selected_id;

		$elems = array(
			'#llrmlogin#'	    => __( 'Log In', 'login-logout-register-menu' ),
			'#llrmlogout#'	    => __( 'Log Out', 'login-logout-register-menu' ),
			'#llrmloginlogout#' => __( 'Log In', 'login-logout-register-menu' ) . ' | ' . __( 'Log Out', 'login-logout-register-menu' ),
			'#llrmregister#'    => __( 'Register', 'login-logout-register-menu' ),
			'#llrmprofile#'     => __( 'Profile', 'login-logout-register-menu' )
		);
		$logitems = array(
			'db_id' => 0,
			'object' => 'bawlog',
			'object_id',
			'menu_item_parent' => 0,
			'type' => 'custom',
			'title',
			'url',
			'target' => '',
			'attr_title' => '',
			'classes' => array(),
			'xfn' => '',
		);

		$elems_obj = array();
		foreach ( $elems as $value => $title ) {
			$elems_obj[ $title ] 		= (object) $logitems;
			$elems_obj[ $title ]->object_id	= esc_attr( $value );
			$elems_obj[ $title ]->title	= esc_attr( $title );
			$elems_obj[ $title ]->url	= esc_attr( $value );
		}

		$walker = new Walker_Nav_Menu_Checklist( array() );
		?>
		<div id="login-links" class="loginlinksdiv">

			<div id="tabs-panel-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
				<ul id="login-linkschecklist" class="list:login-links categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $elems_obj ), 0, (object) array( 'walker' => $walker ) ); ?>
				</ul>
			</div>

			<p class="button-controls">
				<span class="list-controls hide-if-no-js">
					<a href="javascript:void(0);" class="help" onclick="jQuery( '#help-login-links' ).toggle();"><?php _e( 'Usage Information', 'login-logout-register-menu' ); ?></a>
					<span class="hide-if-js" id="help-login-links"><br /><a name="help-login-links"></a>
						<?php
							echo '&#9725;' . esc_html__( 'To redirect user after login/logout/register just add a relative link after the link\'s keyword, example :', 'login-logout-register-menu' ) . ' <br /><code>#llrmloginlogout#index.php</code><br /><code>#llrmloginlogout#index.php|in.php</code><br /><code>#llrmloginlogout#/news|/blog</code>.';
							echo '<br /><br />&#9725;' . esc_html__( 'You can also use', 'login-logout-register-menu' ) . ' <code>%currentpage%</code> ' . esc_html__( 'to redirect the user on the current visited page after login/logout/register, example :', 'login-logout-register-menu' ) . ' <code>#llrmloginlogout#%currentpage%</code>.<br /><br />';
							echo sprintf( __( 'To configure <em>Login Logout Register Menu plugin</em> please visit its <a href="%1$s">configuration page</a> and to get plugin support contact us on <a href="%2$s" target="_blank">plugin support forum</a> or <a href="%3$s" target="_blank">contact us page</a>.', 'login-logout-register-menu'), 'options-general.php?page=llrm', 'http://freewptp.com/forum/wordpress-plugins-forum/login-logout-register-menu/', 'http://freewptp.com/contact/' ) . '<br /><br />';
						?>
					</span>
				</span>

				<span class="add-to-menu">
					<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'login-logout-register-menu' ); ?>" name="add-login-links-menu-item" id="submit-login-links" />
					<span class="spinner"></span>
				</span>
			</p>

		</div>
		<?php
	}

}