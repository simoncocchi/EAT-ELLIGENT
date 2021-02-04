<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Membership Settings Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/controllers
 * @version   1.0.0
 */

class WCFMvm_Memberships_Settings_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMvm, $_POST, $wpdb, $wcfm_membership_settings_form_data;
		
		$wcfm_membership_settings_form_data = array();
	  parse_str($_POST['wcfm_membership_settings_form'], $wcfm_membership_settings_form_data);
	  
	  update_option( 'wcfm_membership_options', $wcfm_membership_settings_form_data );
	  
	  if( isset( $_POST['free_thankyou_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_free_thankyou_content', stripslashes( html_entity_decode( $_POST['free_thankyou_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $_POST['subscription_thankyou_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_subscription_thankyou_content', stripslashes( html_entity_decode( $_POST['subscription_thankyou_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['non_membership_welcome_email_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_non_membership_welcome_email_subject', $wcfm_membership_settings_form_data['non_membership_welcome_email_subject'] );
	  }
	  
	  if( isset( $_POST['non_membership_welcome_email_content'] ) ) {
	  	wcfm_update_option( 'wcfm_non_membership_welcome_email_content', stripslashes( html_entity_decode( $_POST['non_membership_welcome_email_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['subscription_welcome_email_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_subscription_welcome_email_subject', $wcfm_membership_settings_form_data['subscription_welcome_email_subject'] );
	  }
	  
	  if( isset( $_POST['subscription_welcome_email_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_subscription_welcome_email_content', stripslashes( html_entity_decode( $_POST['subscription_welcome_email_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['registration_admin_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_registration_admin_notication_subject', $wcfm_membership_settings_form_data['registration_admin_notication_subject'] );
	  }
	  
	  if( isset( $_POST['registration_admin_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_registration_admin_notication_content', stripslashes( html_entity_decode( $_POST['registration_admin_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['subscription_admin_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_subscription_admin_notication_subject', $wcfm_membership_settings_form_data['subscription_admin_notication_subject'] );
	  }
	  
	  if( isset( $_POST['subscription_admin_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_subscription_admin_notication_content', stripslashes( html_entity_decode( $_POST['subscription_admin_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['onapproval_admin_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_onapproval_admin_notication_subject', $wcfm_membership_settings_form_data['onapproval_admin_notication_subject'] );
	  }
	  
	  if( isset( $_POST['onapproval_admin_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_onapproval_admin_notication_content', stripslashes( html_entity_decode( $_POST['onapproval_admin_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['switch_admin_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_switch_admin_notication_subject', $wcfm_membership_settings_form_data['switch_admin_notication_subject'] );
	  }
	  
	  if( isset( $_POST['switch_admin_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_switch_admin_notication_content', stripslashes( html_entity_decode( $_POST['switch_admin_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['switch_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_switch_notication_subject', $wcfm_membership_settings_form_data['switch_notication_subject'] );
	  }
	  
	  if( isset( $_POST['switch_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_switch_notication_content', stripslashes( html_entity_decode( $_POST['switch_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['next_payment_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_next_payment_notication_subject', $wcfm_membership_settings_form_data['next_payment_notication_subject'] );
	  }
	  
	  if( isset( $_POST['next_payment_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_next_payment_notication_content', stripslashes( html_entity_decode( $_POST['next_payment_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['reminder_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_reminder_notication_subject', $wcfm_membership_settings_form_data['reminder_notication_subject'] );
	  }
	  
	  if( isset( $_POST['reminder_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_reminder_notication_content', stripslashes( html_entity_decode( $_POST['reminder_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['reject_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_reject_notication_subject', $wcfm_membership_settings_form_data['reject_notication_subject'] );
	  }
	  
	  if( isset( $_POST['reject_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_reject_notication_content', stripslashes( html_entity_decode( $_POST['reject_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['cancel_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_cancel_notication_subject', $wcfm_membership_settings_form_data['cancel_notication_subject'] );
	  }
	  
	  if( isset( $_POST['cancel_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_cancel_notication_content', stripslashes( html_entity_decode( $_POST['cancel_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['expire_notication_subject'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_expire_notication_subject', $wcfm_membership_settings_form_data['expire_notication_subject'] );
	  }
	  
	  if( isset( $_POST['expire_notication_content'] ) ) {
	  	wcfm_update_option( 'wcfm_membership_expire_notication_content', stripslashes( html_entity_decode( $_POST['expire_notication_content'], ENT_QUOTES, 'UTF-8' ) ) );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['wcfmvm_registration_static_fields'] ) ) {
	  	wcfm_update_option( 'wcfmvm_registration_static_fields', $wcfm_membership_settings_form_data['wcfmvm_registration_static_fields'] );
	  } else {
	  	wcfm_update_option( 'wcfmvm_registration_static_fields', array() );
	  }
	  
	  if( isset( $wcfm_membership_settings_form_data['wcfmvm_registration_custom_fields'] ) ) {
	  	wcfm_update_option( 'wcfmvm_registration_custom_fields', $wcfm_membership_settings_form_data['wcfmvm_registration_custom_fields'] );
	  }
	  
	  // Init WCFM membership Display Custom CSS file
		$wcfmvm_style_custom = $WCFMvm->wcfmvm_create_membership_css();
		
		do_action( 'wcfm_membership_settings_update', $wcfm_membership_settings_form_data );
	  
	  echo '{"status": true, "message": "' . __( 'Settings saved successfully', 'wc-frontend-manager' ) . '"}';
		
		die;
	}
}