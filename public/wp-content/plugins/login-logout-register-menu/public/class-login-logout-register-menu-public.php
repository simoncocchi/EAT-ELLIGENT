<?php

/**
 * The Login Logout Register Menu Public defines all functionality of plugin
 * for the site front
 *
 * This class defines the meta box used to display the post meta data and registers
 * the style sheet responsible for styling the content of the meta box.
 *
 * @package LLRM
 * @since    1.0.0
 */
class Login_Logout_Register_Menu_Public {

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
	 * Initializes this class and stores the current version of this plugin.
	 *
	 * @param    string    $version    The current version of this plugin.
	 */
	public function __construct( $version ) {
		$this->version = $version;
		$this->options = get_option( 'llrm' );
	}

	/**
	 * PHP 4 Compatible Constructor
	 *
	 */
	function Login_Logout_Register_Menu_Public() {
		$this->__construct();
	}

	/* Returns the correct title for the double login/logout menu item */
	function llrm_loginlogout_title( $title ) {

		$titles = explode( '|', $title );

		if ( ! is_user_logged_in() ) {
			return esc_html( isset( $titles[0] ) ? $titles[0] : $title );
		} else {
			return esc_html( isset( $titles[1] ) ? $titles[1] : $title );
		}
	}

	/* Replaces the #keyword# by the correct links with nonce ect */
	function llrm_setup_nav_menu_item( $item ) {

		global $pagenow;
		$options = $this->options;
		$llrm_hide_on_mobile = true;

		if ( isset( $options['llrm_hide_on_mobile'] ) ) {
			$llrm_hide_on_mobile = wp_is_mobile() ? false : true;
		}

		if ( $llrm_hide_on_mobile && $pagenow != 'nav-menus.php' && ! defined( 'DOING_AJAX' ) && isset( $item->url ) && strstr( $item->url, '#llrm' ) != '' ) {

			$item_url = substr( $item->url, 0, strpos( $item->url, '#', 1 ) ) . '#';
			$item_redirect = str_replace( $item_url, '', $item->url );

			if ( $item_redirect == '%currentpage%' ) {
				$item_redirect = $_SERVER['REQUEST_URI'];
			}

			switch ( $item_url ) {
				case '#llrmloginlogout#' :

					$item_redirect = explode( '|', $item_redirect );

					if ( count( $item_redirect ) != 2 ) {
						$item_redirect[1] = $item_redirect[0];
					}

					for ( $i = 0; $i <= 1; $i++ ) {
						if ( '%currentpage%' == $item_redirect[ $i ] ) {
							$item_redirect[ $i ] = $_SERVER['REQUEST_URI'];
						}
					}

					if ( is_user_logged_in() ) {
						if ( isset( $options['llrm_logout_redirection'] ) && $options['llrm_logout_redirection'] != '' ) {
							$item_redirect[1] = $options['llrm_logout_redirection'];
						}
						$item->url = wp_logout_url( $item_redirect[1] );
					} else {

						if ( isset( $options['llrm_login_redirection'] ) && $options['llrm_login_redirection'] != '' ) {
							$item_redirect[0] = $options['llrm_login_redirection'];
						}

						if ( isset( $options['llrm_login_url'] ) && $options['llrm_login_url'] != '' ) {
							$item->url = $options['llrm_login_url'];
							$item->url .= ( $item_redirect[0] != '' ) ? '?redirect_to=' . $item_redirect[0] : '';
						} else {
							$item->url = wp_login_url( $item_redirect[0] );
						}
					}

					$item->title = $this->llrm_loginlogout_title( $item->title ) ;
					break;

				case '#llrmlogin#' :
					if ( is_user_logged_in() ) {
						return $item;
					}

					if ( isset( $options['llrm_login_redirection'] ) && $options['llrm_login_redirection'] != '' ) {
						$item_redirect = $options['llrm_login_redirection'];
					}

					if ( isset( $options['llrm_login_url'] ) && $options['llrm_login_url'] != '' ) {
						$item->url = $options['llrm_login_url'];
						$item->url .= ( $item_redirect != '' ) ? '?redirect_to=' . $item_redirect : '';
					} else {
						$item->url = wp_login_url( $item_redirect );
					}
					break;

				case '#llrmlogout#' :
					if ( ! is_user_logged_in() ) {
						return $item;
					}

					if ( isset( $options['llrm_logout_redirection'] ) && $options['llrm_logout_redirection'] != '' ) {
						$item_redirect = $options['llrm_logout_redirection'];
					}

					$item->url = wp_logout_url( $item_redirect );
					break;

				case '#llrmregister#' :

					if ( is_user_logged_in() ) {
						return $item;
					}

					if ( isset( $options['llrm_register_url'] ) && $options['llrm_register_url'] != '' ) {
						$item->url = $options['llrm_register_url'];
					} else {
						$item->url = wp_registration_url();
					}

					$item = apply_filters( 'llrm_register_item', $item );
					break;

				case '#llrmprofile#' :
					if ( ! is_user_logged_in() ) {
						return $item;
					}

					if ( isset( $options['llrm_profile_url'] ) && $options['llrm_profile_url'] != '' ) {
						$url = $options['llrm_profile_url'];
					} else if ( function_exists('bp_core_get_user_domain') ) {
						$url = bp_core_get_user_domain( get_current_user_id() );
					} else if ( function_exists('bbp_get_user_profile_url') ) {
						$url = bbp_get_user_profile_url( get_current_user_id() );
					} else if ( class_exists( 'WooCommerce' ) ) {
						$url = get_permalink( get_option('woocommerce_myaccount_page_id') );
					} else {
						$url = get_edit_user_link();
					}

					$item->url = esc_url( $url );
					break;
			}
			$item->url = esc_url( $item->url );
		}
		return $item;
	}

	function llrm_wp_nav_menu_objects( $sorted_menu_items ) {

		foreach ( $sorted_menu_items as $k => $item ) {
			if ( strstr( $item->url, '#llrm' ) != '' ) {
				unset( $sorted_menu_items[ $k ] );
			}
		}
		return $sorted_menu_items;
	}

	/* [llrmlogin] shortcode */
	function llrm_shortcode_login( $atts, $content = null ) {

		if ( is_user_logged_in() ) {
			return '';
		}

		$options = $this->options;

		$item_redirect = esc_url( $_SERVER['REQUEST_URI'] );

		if ( isset( $options['llrm_login_redirection'] ) && $options['llrm_login_redirection'] != '' ) {
			$item_redirect = $options['llrm_login_redirection'];
		}

		$atts = shortcode_atts(array(
			'edit_tag' => '',
			'redirect' => $item_redirect
		), $atts, 'llrmlogin' );

		$edit_tag = esc_html( strip_tags( $atts['edit_tag'] ) );
		$href = wp_login_url( $atts['redirect'] );

		if ( $content == '' ) {
			$content = __( 'Log In', 'login-logout-register-menu' );
		}

		if ( isset( $options['llrm_login_url'] ) && $options['llrm_login_url'] != '' ) {
			$href = $options['llrm_login_url'];
			$href .= ( $atts['redirect'] != '' ) ? '?redirect_to=' . $atts['redirect'] : '';
		}

		return '<a href="' . esc_url( $href ) . '"' . $edit_tag . '>' . $content . '</a>';
	}

	/* [llrmloginlogout] shortcode */
	function llrm_shortcode_loginlogout( $atts, $content = null ) {

		$options = $this->options;
		$item_redirect = esc_url( $_SERVER['REQUEST_URI'] );

		if ( is_user_logged_in() ) {
			if ( isset( $options['llrm_logout_redirection'] ) && $options['llrm_logout_redirection'] != '' ) {
				$item_redirect = $options['llrm_logout_redirection'];
			}
		} else {
			if ( isset( $options['llrm_login_redirection'] ) && $options['llrm_login_redirection'] != '' ) {
				$item_redirect = $options['llrm_login_redirection'];
			}
		}

		$atts = shortcode_atts(array(
			'edit_tag' => '',
			'redirect' => $item_redirect
		), $atts, 'llrmloginlogout' );

		$edit_tag = strip_tags( $atts['edit_tag'] );
		$href = '';

		if ( is_user_logged_in() ) {
			$href = wp_logout_url( $atts['redirect'] );
		} else {

			if ( isset( $options['llrm_login_url'] ) && $options['llrm_login_url'] != '' ) {
				$href = $options['llrm_login_url'];
				$href .= ( $atts['redirect'] != '' ) ? '?redirect_to=' . $atts['redirect'] : '';
			} else {
				$href = wp_login_url( $atts['redirect'] );
			}
		}

		if ( $content && strstr( $content, '|' ) != '' ) { // the "|" char is used to split titles
			$temp = explode( '|', $content );
			$content = is_user_logged_in() ? $temp[1] : $temp[0];
		} else {
			$content = is_user_logged_in() ? __( 'Logout', 'login-logout-register-menu' ) : __( 'Log In', 'login-logout-register-menu' );
		}

		return '<a href="' . esc_url( $href ) . '"' . $edit_tag . '>' . $content . '</a>';
	}

	/* [llrmlogout] shortcode */
	function llrm_shortcode_logout( $atts, $content = null ) {

		if ( ! is_user_logged_in() ) {
			return '';
		}

		$options = $this->options;
		$item_redirect = esc_url( $_SERVER['REQUEST_URI'] );

		if ( isset( $options['llrm_logout_redirection'] ) && $options['llrm_logout_redirection'] != '' ) {
			$item_redirect = $options['llrm_logout_redirection'];
		}

		$atts = shortcode_atts(array(
			'edit_tag' => '',
			'redirect' => $item_redirect
		), $atts, 'llrmlogout' );

		$href = wp_logout_url( $atts['redirect'] );
		$edit_tag = esc_html( strip_tags( $atts['edit_tag'] ) );

		if ( $content == '' ) {
			$content = __( 'Logout', 'login-logout-register-menu' );
		}

		return '<a href="' . esc_url( $href ) . '"' . $edit_tag . '>' . $content . '</a>';
	}

	/* [llrmregister] shortcode */
	function llrm_shortcode_register( $atts, $content = null ) {

		if ( is_user_logged_in() ) {
			return '';
		}

		$options = $this->options;
		$href = '';

		if ( isset( $options['llrm_register_url'] ) && $options['llrm_register_url'] != '' ) {
			$href = $options['llrm_register_url'];
		} else {
			$href = wp_registration_url();
		}

		if ( $content == '' ) {
			$content = __( 'Register', 'login-logout-register-menu' );
		}
		$link = '<a href="' . $href. '">' . $content . '</a>';
		return $link;
	}

	/* [llrmprofile] shortcode */
	function llrm_shortcode_profile( $atts, $content = null ) {

		if ( ! is_user_logged_in() ) {
			return '';
		}

		$options = $this->options;
		$url = '';

		if ( isset( $options['llrm_profile_url'] ) && $options['llrm_profile_url'] != '' ) {
			$url = $options['llrm_profile_url'];
		} else if ( function_exists('bp_core_get_user_domain') ) {
			$url = bp_core_get_user_domain( get_current_user_id() );
		} else if ( function_exists('bbp_get_user_profile_url') ) {
			$url = bbp_get_user_profile_url( get_current_user_id() );
		} else if ( class_exists( 'WooCommerce' ) ) {
			$url = get_permalink( get_option('woocommerce_myaccount_page_id') );
		} else {
			$url = get_edit_user_link();
		}

		$content = $content != '' ? $content : __( 'Profile', 'login-logout-register-menu' );
		$link = '<a href="' . $url . '">' . $content . '</a>';
		return $link;
	}


	function llrm_redirect_after_registration( $registration_redirect ) {

		$options = $this->options;

		if ( isset( $options['llrm_register_redirection'] ) && $options['llrm_register_redirection'] != '' ) {
			$registration_redirect = $options['llrm_register_redirection'];
		}
		return $registration_redirect;
	}
}