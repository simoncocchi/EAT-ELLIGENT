<?php
/**
 * WCFM plugin core
 *
 * WCfM Emails
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   1.0.0
 */
 
class WCFMvm_Emails {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		add_filter('woocommerce_email_classes', array( &$this, 'wcfmvm_email_classes' ) );
		
		add_filter( 'woocommerce_template_directory', array( &$this, 'wcfmvm_template_directory' ), 50, 2 );
	}
	
	function wcfmvm_email_classes( $emails ) {
		global $WCFM, $WCFMmp;
		
		$wcfm_emails = get_wcfmvm_emails();
		foreach( $wcfm_emails as $wcfm_email => $wcfm_email_label ) {
			$email = 'WCFMvm_Email_' . ucfirst( str_replace( '-', '_', $wcfm_email ) );
			if( !class_exists( $email ) ) {
				$this->load_email_class( $wcfm_email );
			}
			$emails[ $email ] = new $email( $wcfm_email, $wcfm_email_label );
		}
		
		return $emails;
	}
	
	function wcfmvm_template_directory( $template_dir, $template ) {
		
		if( in_array( $template, apply_filters( 'wcfmvm_email_templates', array( 'emails/email-verification.php', 'emails/plain/email-verification.php' ) ) ) ) {
			$template_dir = 'wcfm';
		}
		return $template_dir;
	}
	
	public function load_email_class($wcfm_email = '') {
		global $WCFM, $WCFMvm;
		if ( '' != $wcfm_email ) {
			if( file_exists( $WCFMvm->plugin_path . 'includes/emails/class-wcfmvm-email-' . esc_attr($wcfm_email) . '.php' ) ) {
				require_once ( $WCFMvm->plugin_path . 'includes/emails/class-wcfmvm-email-' . esc_attr($wcfm_email) . '.php' );
			}
		} // End If Statement
	}
}