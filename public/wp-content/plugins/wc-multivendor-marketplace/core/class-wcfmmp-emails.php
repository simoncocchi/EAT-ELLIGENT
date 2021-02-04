<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Emails
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Emails {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		add_filter('woocommerce_email_classes', array( &$this, 'wcmpmp_email_classes' ) );
		
		add_filter( 'woocommerce_template_directory', array( &$this, 'wcfmmp_template_directory' ), 50, 2 );
	}
	
	function wcmpmp_email_classes( $emails ) {
		global $WCFM, $WCFMmp;
		
		$wcfmmp_emails = get_wcfm_marketplace_emails();
		foreach( $wcfmmp_emails as $wcfmmp_email => $wcfmmp_email_label ) {
			$email = 'WCFMmp_Email_' . ucfirst( str_replace( '-', '_', $wcfmmp_email ) );
			if( !class_exists( $email ) ) {
				$this->load_email_class( $wcfmmp_email );
			}
			$emails[ $email ] = new $email();
		}
		
		return $emails;
	}
	
	function wcfmmp_template_directory( $template_dir, $template ) {
		
		if( in_array( $template, array( 'emails/store-new-order.php', 'emails/plain/store-new-order.php' ) ) ) {
			$template_dir = 'wcfm';
		}
		return $template_dir;
	}
	
	public function load_email_class($wcfmmp_email = '') {
		global $WCFM, $WCFMmp;
		if ( '' != $wcfmmp_email ) {
			if( file_exists( $WCFMmp->plugin_path . 'includes/store-emails/class-wcfmmp-email-' . esc_attr($wcfmmp_email) . '.php' ) ) {
				require_once ( $WCFMmp->plugin_path . 'includes/store-emails/class-wcfmmp-email-' . esc_attr($wcfmmp_email) . '.php' );
			}
		} // End If Statement
	}
}