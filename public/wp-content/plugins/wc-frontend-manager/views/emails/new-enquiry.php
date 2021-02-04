<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/wcfm/emails/new-enquiry.php
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/emails
 * @version   1.0.0
 */
if (!defined('ABSPATH'))
    return; // Exit if accessed directly
  
global $WCFM;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php esc_html_e( 'Hi,', 'wc-frontend-manager' ); ?></p>

<?php do_action( 'wcfm_enquiry_email_before', $enquiry_id ); ?>

<p><?php printf( esc_html__( 'You have a recent inquiry for %s.', 'wc-frontend-manager' ), $enquiry_for ); ?></p>

<?php do_action( 'wcfm_enquiry_email_before_enquiry', $enquiry_id ); ?>

<blockquote><strong><i><?php echo wpautop( wptexturize( make_clickable( $enquiry ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i></strong></blockquote>

<?php do_action( 'wcfm_enquiry_email_after_enquiry', $enquiry_id ); ?>

<?php
if ( $additional_info ) {
	do_action( 'wcfm_enquiry_email_before_additonal_info', $enquiry_id );
	echo '<p>' . wp_kses_post( wpautop( wptexturize( $additional_info ) ) ) . '<br/></p>';
	do_action( 'wcfm_enquiry_email_after_additonal_info', $enquiry_id );
}
?>

<p><?php printf( esc_html__( 'To respond this Inquiry, please %sClick Here%s.', 'wc-frontend-manager' ), '<a href="'.$enquiry_url.'">', '</a>' ); ?></p>

<?php do_action( 'wcfm_enquiry_email_after', $enquiry_id ); ?>

<p><?php esc_html_e( 'Thank You', 'wc-frontend-manager' ); ?></p>
										 

<?php do_action( 'woocommerce_email_footer', $email ); ?>