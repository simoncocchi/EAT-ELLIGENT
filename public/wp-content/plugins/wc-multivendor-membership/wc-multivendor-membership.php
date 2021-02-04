<?php
/**
 * Plugin Name: WCFM - WooCommerce Multivendor Membership
 * Plugin URI: https://wclovers.com/product/woocommerce-multivendor-membership
 * Description: A simple membership plugin for your multi-vendor marketplace.
 * Author: WC Lovers
 * Version: 2.9.5
 * Author URI: https://wclovers.com
 *
 * Text Domain: wc-multivendor-membership
 * Domain Path: /lang/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 4.8.0
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! class_exists( 'WCFMvm_Dependencies' ) )
	require_once 'helpers/class-wcfmvm-dependencies.php';

require_once 'helpers/wcfmvm-core-functions.php';
require_once 'wc-multivendor-membership-config.php';

if(!defined('WCFMvm_TOKEN')) exit;
if(!defined('WCFMvm_TEXT_DOMAIN')) exit;


if(!class_exists('WCFMvm')) {
	include_once( 'core/class-wcfmvm.php' );
	global $WCFMvm;
	$WCFMvm = new WCFMvm( __FILE__ );
	$GLOBALS['WCFMvm'] = $WCFMvm;
	
	// Activation Hooks
	register_activation_hook( __FILE__, array('wcfmvm', 'activate_wcfmvm') );
	register_activation_hook( __FILE__, 'flush_rewrite_rules' );
	
	// Deactivation Hooks
	register_deactivation_hook( __FILE__, array('wcfmvm', 'deactivate_wcfmvm') );
}
?>