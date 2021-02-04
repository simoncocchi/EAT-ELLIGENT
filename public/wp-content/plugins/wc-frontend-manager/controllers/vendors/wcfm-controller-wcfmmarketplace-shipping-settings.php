<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Marketplace Vendor Manager Shipping Setings Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/vendor
 * @version   5.1.12
 */

class WCFM_Shipping_Settings_Marketplace_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wcfm_settings_form_data = array();
	  parse_str($_POST['wcfm_settings_form'], $wcfm_settings_form);
	  
	  $has_error = false;
	  
	  $user_id = absint( $wcfm_settings_form['store_id'] );
	  
		do_action( 'wcfm_vendor_shipping_settings_update', $user_id, $wcfm_settings_form );
		
		if( !$has_error ) {
			echo '{"status": true, "message": "' . __( 'Shipping Settings saved successfully', 'wc-frontend-manager' ) . '"}';
		}
		 
		die;
	}
}