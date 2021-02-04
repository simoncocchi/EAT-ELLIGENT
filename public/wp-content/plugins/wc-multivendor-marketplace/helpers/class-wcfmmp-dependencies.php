<?php
/**
 * WCFM Marketplace Dependency Checker
 *
 */
class WCFMmp_Dependencies {
	
	private static $active_plugins;
	
	static function init() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() )
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}
	
	// WooCommerce
	static function woocommerce_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
		return false;
	}
	
	// WC Frontend Manager
	static function wcfm_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'wc-frontend-manager/wc_frontend_manager.php', self::$active_plugins ) || array_key_exists( 'wc-frontend-manager/wc_frontend_manager.php', self::$active_plugins );
		return false;
	}
	
	// WC Frontend Manager - Membership
	static function wcfmvm_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'wc-multivendor-membership/wc-multivendor-membership.php', self::$active_plugins ) || array_key_exists( 'wc-multivendor-membership/wc-multivendor-membership.php', self::$active_plugins );
		return false;
	}
	
	// WC SMS Laert - India
	static function wcfm_sms_alert_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'sms-alert/SMSAlert-wc-order-sms.php', self::$active_plugins ) || array_key_exists( 'sms-alert/SMSAlert-wc-order-sms.php', self::$active_plugins );
		return false;
	}
	
	// WC SMS Laert - World
	static function wcfm_twilio_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'woocommerce-twilio-sms-notifications/woocommerce-twilio-sms-notifications.php', self::$active_plugins ) || array_key_exists( 'woocommerce-twilio-sms-notifications/woocommerce-twilio-sms-notifications.php', self::$active_plugins );
		return false;
	}
	
	// WC SMS Laert - World
	static function wcfm_msg91_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'msg91-woocommerce-sms-integration-lite/wc_sms_plugin.php', self::$active_plugins ) || array_key_exists( 'msg91-woocommerce-sms-integration-lite/wc_sms_plugin.php', self::$active_plugins );
		return false;
	}
	
	// BuddyPress Support
	static function wcfm_biddypress_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'buddypress/bp-loader.php', self::$active_plugins ) || array_key_exists( 'buddypress/bp-loader.php', self::$active_plugins );
		return false;
	}
	
	// GEO my Wp Support
	static function wcfm_geo_my_wp_plugin_active_check() {
		if ( ! self::$active_plugins ) self::init();
		return in_array( 'geo-my-wp/geo-my-wp.php', self::$active_plugins ) || array_key_exists( 'geo-my-wp/geo-my-wp.php', self::$active_plugins ) || class_exists( 'GEO_MY_WP' );
		return false;
	}
}