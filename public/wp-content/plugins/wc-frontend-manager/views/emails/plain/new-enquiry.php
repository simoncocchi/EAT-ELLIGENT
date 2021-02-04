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

$reply_mail_body =   '<br/>' . __( 'Hi', 'wc-frontend-manager' ) .
										 ',<br/><br/>' . 
										 sprintf( __( 'You have a recent enquiry for %s.', 'wc-frontend-manager' ), '{enquiry_for}' ) .
										 '<br/><br/><strong><i>' . 
										 '"{enquiry}"' . 
										 '</i></strong><br/><br/>' .
										 '{additional_info}' .
										 sprintf( __( 'To respond to this Enquiry, please %sClick Here%s', 'wc-frontend-manager' ), '<a href="{enquiry_url}">', '</a>' ) .
										 '<br /><br/>' . __( 'Thank You', 'wc-frontend-manager' ) .
										 '<br /><br/>';
										 
echo $reply_mail_body;										 

do_action( 'woocommerce_email_footer', $email ); ?>