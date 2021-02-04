<?php
/**
 * The template for email verification content.
 *
 * Override this template by copying it to yourtheme/wcfm/emails/email-verification.php
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/views/emails
 * @version   1.0.0
 */
if (!defined('ABSPATH'))
    return; // Exit if accessed directly
  
global $WCFM;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php esc_html_e( 'Hi,', 'wc-frontend-manager' ); ?></p>

<?php do_action( 'wcfm_email_verification_email_before', $verification_code, $user_email ); ?>

<p><?php echo apply_filters( 'wcfm_email_verification_mail_content', sprintf( __( 'Here is your email verification code - <b>%s</b>', 'wc-multivendor-membership' ), $verification_code ) ); ?></p>

<?php do_action( 'wcfm_email_verification_email_after', $verification_code, $user_email ); ?>

<p><?php esc_html_e( 'Thank You', 'wc-frontend-manager' ); ?></p>
										 

<?php do_action( 'woocommerce_email_footer', $email ); ?>