<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Dokan Setings Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   3.3.0
 */

class WCFM_Settings_Dokan_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		$vendor_data = get_user_meta( $user_id, 'dokan_profile_settings', true );
		if( !is_array($vendor_data) ) $vendor_data = array();
		
		$wcfm_settings_form_data = array();
	  parse_str($_POST['wcfm_settings_form'], $wcfm_settings_form);
	  
	  // WCFM form custom validation filter
		$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_settings_form, 'vendor_setting_manage' );
		if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
			$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
			if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
			echo '{"status": false, "message": "' . $custom_validation_error . '"}';
			die;
		}
	  
	  // sanitize
		//$wcfm_settings_form = array_map( 'sanitize_text_field', $wcfm_settings_form );
		//$wcfm_settings_form = array_map( 'stripslashes', $wcfm_settings_form );
		
		// Set Gravatar
		if( apply_filters( 'wcfm_is_allow_store_logo', true ) ) {
			if(isset($wcfm_settings_form['gravatar']) && !empty($wcfm_settings_form['gravatar'])) {
				$wcfm_settings_form['gravatar'] = $WCFM->wcfm_get_attachment_id($wcfm_settings_form['gravatar']);
			} else {
				$wcfm_settings_form['gravatar'] = '';
			}
		}
		
		// Set Banner
		if( apply_filters( 'wcfm_is_allow_store_banner', true ) ) {
			if(isset($wcfm_settings_form['banner']) && !empty($wcfm_settings_form['banner'])) {
				$wcfm_settings_form['banner'] = $WCFM->wcfm_get_attachment_id($wcfm_settings_form['banner']);
			} else {
				$wcfm_settings_form['banner'] = '';
			}
		}
		
		//if ( dokan_get_option( 'new_seller_enable_selling', 'dokan_selling', 'on' ) == 'off' ) {
			//update_user_meta( $user_id, 'dokan_enable_selling', 'no' );
		//} else {
			//update_user_meta( $user_id, 'dokan_enable_selling', 'yes' );
		//}
		//update_user_meta( $user_id, 'can_post_product', '1' );
		//update_user_meta( $user_id, 'dokan_enable_selling', 'yes' );
		
		// Shipping Type
		if( isset( $wcfm_settings_form['wcfm_dokan_regular_shipping'] ) ) {
			update_user_meta( $user_id, 'wcfm_dokan_regular_shipping', 'yes' );
		} else {
			update_user_meta( $user_id, 'wcfm_dokan_regular_shipping', 'no' );
		}
		
		// Checkboxes 
		if( !isset( $wcfm_settings_form['show_email'] ) ) $wcfm_settings_form['show_email'] = 'no';
		if( !isset( $wcfm_settings_form['show_more_ptab'] ) ) $wcfm_settings_form['show_more_ptab'] = 'no';
		if( !isset( $wcfm_settings_form['enable_tnc'] ) ) $wcfm_settings_form['enable_tnc'] = 'no';
		
		// Vacation Settings
		if( !isset( $wcfm_settings_form['wcfm_vacation_mode'] ) ) $wcfm_settings_form['wcfm_vacation_mode'] = 'no';
		if( !isset( $wcfm_settings_form['wcfm_disable_vacation_purchase'] ) ) $wcfm_settings_form['wcfm_disable_vacation_purchase'] = 'no';
		
		// merge the changes with existing settings
		$wcfm_settings_form = array_merge( $vendor_data, $wcfm_settings_form );
		
		update_user_meta( $user_id, 'dokan_profile_settings', $wcfm_settings_form );
		
		do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_settings_form );
		do_action( 'wcfm_dokan_settings_update', $user_id, $wcfm_settings_form );
		
		echo '{"status": true, "message": "' . __( 'Settings saved successfully', 'wc-frontend-manager' ) . '"}';
		 
		die;
	}
}