<?php
/**
 * WCFM plugin core
 *
 * Plugin shortcode
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   1.0.0
 */
 
class WCFM_Shortcode {

	public $list_product;

	public function __construct() {
		// WC Frontend Manager Shortcode
		add_shortcode('wc_frontend_manager', array(&$this, 'wc_frontend_manager'));
		
		// WC Frontend Manager Endpoint as Shortcode
		add_shortcode('wcfm', array(&$this, 'wcfm_endpoint_shortcode'));
		
		// WC Frontend Manager Header Panel Notifications as Shortcode
		add_shortcode('wcfm_notifications', array(&$this, 'wcfm_notifications_shortcode'));
		
		// WCfM Enquiry Button Short code
		add_shortcode('wcfm_enquiry', array(&$this, 'wcfm_enquiry_shortcode'));
		add_shortcode('wcfm_inquiry', array(&$this, 'wcfm_enquiry_shortcode'));
		
		// WCFM Policies Short Code
		add_shortcode('wcfm_policy', array(&$this, 'wcfm_policy_shortcode'));
		
		// WCfM Store Follow Button Short code
		if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
			add_shortcode('wcfm_follow', array(&$this, 'wcfm_follow_shortcode'));
		}
	}

	public function wc_frontend_manager($attr) {
		global $WCFM;
		$WCFM->nocache();
		
		wc_nocache_headers();
		
		$this->load_class('wc-frontend-manager');
		return $this->shortcode_wrapper( array('WCFM_Frontend_Manager_Shortcode', 'output'), $attr );
	}
	
	/**
	 * WCFM End point as Short Code
	 */
	public function wcfm_endpoint_shortcode( $attr ) {
		global $WCFM, $wp, $WCFM_Query;
		$this->load_class('endpoint');
		return $this->shortcode_wrapper( array('WCFM_Endpoint_Shortcode', 'output'), $attr );
	}
		
	/**
	 * WC Frontend Manager Header Panel Notifications as Shortcode
	 */
	public function wcfm_notifications_shortcode( $attr ) {
		global $WCFM, $wp, $WCFM_Query;
		$this->load_class('notification');
		return $this->shortcode_wrapper( array('WCFM_Notification_Shortcode', 'output'), $attr );
	}
		
	/**
	 * WCfM Enquiry Ask a Question button short code
	 */
	function wcfm_enquiry_shortcode( $attr ) {
		global $WCFM;
		
		//if( !is_product() && ( function_exists( 'wcfmmp_is_store_page' ) && !wcfmmp_is_store_page() ) ) return;
		
		$this->load_class('enquiry');
		return $this->shortcode_wrapper( array('WCFM_Enquiry_Shortcode', 'output'), $attr);
	}
	
	/**
	 * WCFM Policy short code
	 */
	function wcfm_policy_shortcode( $attr ) {
		global $WCFM;
		
		//if( !is_product() && ( function_exists( 'wcfmmp_is_store_page' ) && !wcfmmp_is_store_page() ) ) return;
		
		$this->load_class('policy');
		return $this->shortcode_wrapper( array('WCFM_Policy_Shortcode', 'output'), $attr);
	}
	
	/**
	 * WCfM Follow button short code
	 */
	function wcfm_follow_shortcode( $attr ) {
		global $WCFM;
		$this->load_class('follow');
		return $this->shortcode_wrapper( array('WCFM_Follow_Shortcode', 'output'), $attr);
	}

	/**
	 * Helper Functions
	 */

	/**
	 * Shortcode Wrapper
	 *
	 * @access public
	 * @param mixed $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public function shortcode_wrapper($function, $atts = array()) {
		ob_start();
		call_user_func($function, $atts);
		return ob_get_clean();
	}

	/**
	 * Shortcode CLass Loader
	 *
	 * @access public
	 * @param mixed $class_name
	 * @return void
	 */
	public function load_class($class_name = '') {
		global $WCFM;
		if ('' != $class_name && '' != $WCFM->token) {
			require_once ( $WCFM->plugin_path . 'includes/shortcodes/class-' . esc_attr($WCFM->token) . '-shortcode-' . esc_attr($class_name) . '.php' );
		}
	}

}
?>