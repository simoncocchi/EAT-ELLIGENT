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
 
class WCFM_Emails {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		add_filter('woocommerce_email_classes', array( &$this, 'wcfm_email_classes' ) );
		
		add_filter( 'woocommerce_template_directory', array( &$this, 'wcfm_template_directory' ), 50, 2 );
	}
	
	function wcfm_email_classes( $emails ) {
		global $WCFM, $WCFMmp;
		
		$wcfm_emails = get_wcfm_emails();
		foreach( $wcfm_emails as $wcfm_email => $wcfm_email_label ) {
			$email = 'WCFM_Email_' . ucfirst( str_replace( '-', '_', $wcfm_email ) );
			if( !class_exists( $email ) ) {
				$this->load_email_class( $wcfm_email );
			}
			$emails[ $email ] = new $email( $wcfm_email, $wcfm_email_label );
		}
		
		return $emails;
	}
	
	function wcfm_template_directory( $template_dir, $template ) {
		
		if( in_array( $template, array( 'emails/new-enquiry.php', 'emails/plain/new-enquiry.php' ) ) ) {
			$template_dir = 'wcfm';
		}
		return $template_dir;
	}
	
	public function load_email_class($wcfm_email = '') {
		global $WCFM, $WCFMmp;
		if ( '' != $wcfm_email ) {
			if( file_exists( $WCFM->plugin_path . 'includes/emails/class-wcfm-email-' . esc_attr($wcfm_email) . '.php' ) ) {
				require_once ( $WCFM->plugin_path . 'includes/emails/class-wcfm-email-' . esc_attr($wcfm_email) . '.php' );
			}
		} // End If Statement
	}
}