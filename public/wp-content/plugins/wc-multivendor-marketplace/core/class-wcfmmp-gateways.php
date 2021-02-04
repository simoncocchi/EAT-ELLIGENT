<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WCFMmp_Gateways {

	/** @var array Array of payment gateway classes. */
	public $payment_gateways = array();

	public function __construct() {
		
		$this->load_stripe_gateway();
		$this->load_wirecard_gateway();
		$this->init();
	}

	public function init() {
		global $WCFM, $WCFMmp;
		
		$active_payment_methods = get_wcfm_marketplace_active_withdrwal_payment_methods();
		foreach( $active_payment_methods as $payment_method => $payment_method_label ) {
			$gateway = 'WCFMmp_Gateway_' . ucfirst($payment_method);
			if( !class_exists( $gateway ) ) {
				$this->load_gateway( $payment_method );
			}
			$this->payment_gateways[ $payment_method ] = new $gateway();
		}
	}
	
	function load_stripe_gateway() {
		global $WCFM, $WCFMmp;
		
		$active_payment_methods = get_wcfm_marketplace_active_withdrwal_payment_methods();
		if( !array_key_exists( 'stripe', $active_payment_methods ) || !array_key_exists( 'stripe_split', $active_payment_methods ) ) return;
		
		$stripe_dependencies = array( 'state' => true, 'library' => '' );
		if ( version_compare( PHP_VERSION, '5.3.29', '<' ) ) {
			$stripe_dependencies['library'] = 'phpversion';
			$stripe_dependencies['state'] = false;
		}
		$modules = array( 'curl', 'mbstring', 'json' );

		foreach( $modules as $module ) {
			if( !extension_loaded($module) ) {
				$stripe_dependencies['library'] = $module;
				$stripe_dependencies['state'] = false;
			}
		}
		if( $stripe_dependencies['state'] ) {
			if( !class_exists("Stripe\Stripe") ) {
				require_once( $WCFMmp->plugin_path . 'includes/Stripe/init.php' );
			}
		} else{
			switch ( $stripe_dependencies['library'] ) {
				case 'phpversion':
					add_action( 'admin_notices', 'wcfmmp_stripe_phpversion_notice' );
					break;
				case 'curl':
					add_action( 'admin_notices', 'wcfmmp_stripe_curl_notice' );
					break;
				case 'mbstring':
					add_action( 'admin_notices', 'wcfmmp_stripe_mbstring_notice' );
					break;
				case 'json':
					add_action( 'admin_notices', 'wcfmmp_stripe_json_notice' );
					break;
				default:
					break;
			}
		}
	}
	
	function load_wirecard_gateway() {
		global $WCFM, $WCFMmp;
		
		$active_payment_methods = get_wcfm_marketplace_active_withdrwal_payment_methods();
		if( !array_key_exists( 'wirecard', $active_payment_methods ) ) return;
		
		require_once( $WCFMmp->plugin_path . 'includes/wirecard/vendor/autoload.php' );
		
	}
	
	public function load_gateway($payment_method = '') {
		global $WCFM, $WCFMmp;
		if ( '' != $payment_method ) {
			if( file_exists( $WCFMmp->plugin_path . 'includes/payment-gateways/class-wcfmmp-gateway-' . esc_attr($payment_method) . '.php' ) ) {
				require_once ( $WCFMmp->plugin_path . 'includes/payment-gateways/class-wcfmmp-gateway-' . esc_attr($payment_method) . '.php' );
			}
		} // End If Statement
	}
}