<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Support Manage Form Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmu/controllers/support
 * @version   4.0.3
 */

class WCFMu_Support_Manage_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMu;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMu, $wpdb, $_POST;
		
		$wcfm_support_reply_form_data = array();
	  parse_str($_POST['wcfm_support_ticket_reply_form'], $wcfm_support_reply_form_data);
	  
	  $wcfm_support_messages = get_wcfm_support_manage_messages();
	  $has_error = false;
	  
	  if(isset($_POST['support_ticket_reply']) && !empty($_POST['support_ticket_reply'])) {
	  	$support_categories    = $WCFMu->wcfmu_support->wcfm_support_categories();
	  	
	  	$support_reply       = esc_sql( wp_unslash( $_POST['support_ticket_reply'] ) );
	  	$support_priority    = $wcfm_support_reply_form_data['support_priority'];
	  	$support_status      = $wcfm_support_reply_form_data['support_status'];
	  	$support_reply_by    = apply_filters( 'wcfm_message_author', get_current_user_id() );
	  	$support_ticket_id   = absint( $wcfm_support_reply_form_data['support_ticket_id'] );
	  	$support_order_id    = absint( $wcfm_support_reply_form_data['support_order_id'] );
	  	$support_item_id     = absint( $wcfm_support_reply_form_data['support_item_id'] );
	  	$support_product_id  = absint( $wcfm_support_reply_form_data['support_product_id'] );
	  	$support_vendor_id   = absint( $wcfm_support_reply_form_data['support_vendor_id'] );
	  	$support_customer_id = absint( $wcfm_support_reply_form_data['support_customer_id'] );
	  	$support_customer_email = $wcfm_support_reply_form_data['support_customer_email'];
	  	
	  	if( !defined( 'DOING_WCFM_EMAIL' ) ) 
	  		define( 'DOING_WCFM_EMAIL', true );
	  	
	  	$wcfm_create_support_reply = "INSERT into {$wpdb->prefix}wcfm_support_response 
																	(`reply`, `support_id`, `order_id`, `item_id`, `product_id`, `vendor_id`, `customer_id`, `reply_by`)
																	VALUES
																	('{$support_reply}', {$support_ticket_id}, {$support_order_id}, {$support_item_id}, {$support_product_id}, {$support_vendor_id}, {$support_customer_id}, {$support_reply_by})";
													
			$wpdb->query($wcfm_create_support_reply);
			$support_ticket_reply_id = $wpdb->insert_id;
			
			$wcfm_support_update = "UPDATE {$wpdb->prefix}wcfm_support SET `priority` = '{$support_priority}', `status` = '{$support_status}' 
															WHERE `ID` = {$support_ticket_id}";
									
			$wpdb->query($wcfm_support_update);
			
			$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
			if ( $myaccount_page_id ) {
				$myaccount_page_url = trailingslashit( get_permalink( $myaccount_page_id ) );
			}
			$support_ticket_url = $myaccount_page_url .'view-support-ticket/' . $support_ticket_id;
			
			// Send mail to Customer
			$mail_to = get_bloginfo( 'admin_email' );
			$reply_mail_subject = '{site_name}: ' . __( 'Support Ticket Reply', 'wc-multivendor-marketplace' ) . ' - ' . __( 'Ticket', 'wc-multivendor-marketplace' ) . ' #{support_ticket_id}';
			$reply_mail_body =   '<br/>' .  __( 'Hi', 'wc-multivendor-marketplace' ) .
													 ',<br/><br/>' . 
													 sprintf( __( 'You have received reply for your "%s" support request. Please check below for the details: ', 'wc-multivendor-marketplace' ), '{product_title}' ) .
													 '<br/><br/><strong><i>' . 
													 '"{support_reply}"' . 
													 '</i></strong><br/><br/>' .
													 __( 'Check more details here', 'wc-multivendor-marketplace' ) . ': <a href="{support_url}">' . __( 'Ticket', 'wc-multivendor-marketplace' ) . ' #{support_ticket_id}</a>' .
													 '<br /><br/>' . __( 'Thank You', 'wc-multivendor-marketplace' ) .
													 '<br/><br/>';
			
			$headers[] = 'From: [' . get_bloginfo( 'name' ) . '] ' . __( 'Support Ticket Reply', 'wc-multivendor-marketplace' );
			$headers[] = 'Cc: ' . $mail_to;
			$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $reply_mail_subject );
			$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
			$subject = str_replace( '{support_url}', $support_ticket_url, $subject );
			$subject = str_replace( '{support_ticket_id}', $support_ticket_id, $subject );
			$subject = str_replace( '{product_title}', get_the_title( $support_product_id ), $subject );
			$message = str_replace( '{product_title}', get_the_title( $support_product_id ), $reply_mail_body );
			$message = str_replace( '{support_url}', $support_ticket_url, $message );
			$message = str_replace( '{support_reply}', $support_reply, $message );
			$message = str_replace( '{support_ticket_id}', $support_ticket_id, $message );
			$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Reply to Support Ticket', 'wc-multivendor-marketplace' ) . ' #' . $support_ticket_id );
			
			wp_mail( $support_customer_email, $subject, $message, $headers );
			
			// Direct message
			/*$wcfm_messages = sprintf( __( 'You have received reply for Support Ticket <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_support_manage_url( $support_ticket_id ) . '">#' . $support_ticket_id . '</a>' );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'support' );
			
			// Semd email to vendor
			if( wcfm_is_marketplace() ) {
				if( $support_vendor_id ) {
					$vendor_email = $WCFM->wcfm_vendor_support->wcfm_get_vendor_email_from_product( $support_product_id );
					if( $vendor_email ) {
						wp_mail( $vendor_email, $subject, $message, $headers );
					}
					
					// Direct message
					$wcfm_messages = sprintf( __( 'You have received reply for Support Ticket <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_support_manage_url( $support_ticket_id ) . '">#' . $support_ticket_id . '</a>' );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $support_vendor_id, 1, 0, $wcfm_messages, 'support' );
				}
	  	}*/
			
			echo '{"status": true, "message": "' . $wcfm_support_messages['support_reply_saved'] . '", "redirect": "' . get_wcfm_support_manage_url( $support_ticket_id ) . '#support_ticket_reply_' . $support_ticket_reply_id . '"}';
		} else {
			echo '{"status": false, "message": "' . $wcfm_support_messages['no_reply'] . '"}';
		}
		
		die;
	}
}

class WCFMu_My_Account_Support_Manage_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMu;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMu, $wpdb, $_POST;
		
		$wcfm_support_reply_form_data = array();
	  parse_str($_POST['wcfm_support_ticket_reply_form'], $wcfm_support_reply_form_data);
	  
	  $wcfm_support_messages = get_wcfm_support_manage_messages();
	  $has_error = false;
	  
	  if(isset($_POST['support_ticket_reply']) && !empty($_POST['support_ticket_reply'])) {
	  	$support_categories    = $WCFMu->wcfmu_support->wcfm_support_categories();
	  	
	  	$support_reply       = esc_sql( wp_unslash( $_POST['support_ticket_reply'] ) );
	  	$support_priority    = $wcfm_support_reply_form_data['support_priority'];
	  	$support_status      = $wcfm_support_reply_form_data['support_status'];
	  	$support_reply_by    = apply_filters( 'wcfm_message_author', get_current_user_id() );
	  	$support_ticket_id   = absint( $wcfm_support_reply_form_data['support_ticket_id'] );
	  	$support_order_id    = absint( $wcfm_support_reply_form_data['support_order_id'] );
	  	$support_item_id     = absint( $wcfm_support_reply_form_data['support_item_id'] );
	  	$support_product_id  = absint( $wcfm_support_reply_form_data['support_product_id'] );
	  	$support_vendor_id   = absint( $wcfm_support_reply_form_data['support_vendor_id'] );
	  	$support_customer_id = absint( $wcfm_support_reply_form_data['support_customer_id'] );
	  	
	  	if( !defined( 'DOING_WCFM_EMAIL' ) ) 
	  		define( 'DOING_WCFM_EMAIL', true );
	  	
	  	$wcfm_create_support_reply = "INSERT into {$wpdb->prefix}wcfm_support_response 
																	(`reply`, `support_id`, `order_id`, `item_id`, `product_id`, `vendor_id`, `customer_id`, `reply_by`)
																	VALUES
																	('{$support_reply}', {$support_ticket_id}, {$support_order_id}, {$support_item_id}, {$support_product_id}, {$support_vendor_id}, {$support_customer_id}, {$support_reply_by})";
													
			$wpdb->query($wcfm_create_support_reply);
			$support_ticket_reply_id = $wpdb->insert_id;
			
			$wcfm_support_update = "UPDATE {$wpdb->prefix}wcfm_support SET `priority` = '{$support_priority}', `status` = '{$support_status}' 
															WHERE `ID` = {$support_ticket_id}";
									
			$wpdb->query($wcfm_support_update);
			
			// Send mail to admin
			$mail_to = get_bloginfo( 'admin_email' );
			$reply_mail_subject = '{site_name}: ' . __( 'Support Ticket Reply', 'wc-multivendor-marketplace' ) . ' - ' . __( 'Ticket', 'wc-multivendor-marketplace' ) . ' #{support_ticket_id}';
			$reply_mail_body =   '<br/>' . __( 'Hi', 'wc-multivendor-marketplace' ) .
													 ',<br/><br/>' . 
													 __( 'You have received reply for your "{product_title}" support request. Please check below for the details: ', 'wc-multivendor-marketplace' ) .
													 '<br/><br/><strong><i>' . 
													 '"{support_reply}"' . 
													 '</i></strong><br/><br/>' .
													 __( 'Check more details here', 'wc-multivendor-marketplace' ) . ': <a href="{support_url}">' . __( 'Ticket', 'wc-multivendor-marketplace' ) . ' #{support_ticket_id}</a>' .
													 '<br /><br/>' . __( 'Thank You', 'wc-multivendor-marketplace' ) .
													 '<br/><br/>';
			
			$headers[] = 'From: [' . get_bloginfo( 'name' ) . '] ' . __( 'Support Ticket Reply', 'wc-multivendor-marketplace' );
			$headers[] = 'Cc: ' . $mail_to;
			$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $reply_mail_subject );
			$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
			$subject = str_replace( '{support_url}', get_wcfm_support_manage_url( $support_ticket_id ), $subject );
			$subject = str_replace( '{support_ticket_id}', $support_ticket_id, $subject );
			$subject = str_replace( '{product_title}', get_the_title( $support_product_id ), $subject );
			$message = str_replace( '{product_title}', get_the_title( $support_product_id ), $reply_mail_body );
			$message = str_replace( '{support_url}', get_wcfm_support_manage_url( $support_ticket_id ), $message );
			$message = str_replace( '{support_reply}', $support_reply, $message );
			$message = str_replace( '{support_ticket_id}', $support_ticket_id, $message );
			$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Reply to Support Ticket', 'wc-multivendor-marketplace' ) . ' #' . $support_ticket_id );
			
			wp_mail( $mail_to, $subject, $message, $headers );
			
			// Direct message
			$wcfm_messages = sprintf( __( 'You have received reply for Support Ticket <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_support_manage_url( $support_ticket_id ) . '">#' . $support_ticket_id . '</a>' );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'support' );
			
			// Semd email to vendor
			if( wcfm_is_marketplace() ) {
				if( $support_vendor_id ) {
					$is_allow_support = $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $support_vendor_id, 'support_ticket_manage' );
					if( $is_allow_support && apply_filters( 'wcfm_is_allow_support_vendor_notification', true ) ) {
						$vendor_email = $WCFM->wcfm_vendor_support->wcfm_get_vendor_email_from_product( $support_product_id );
						if( $vendor_email ) {
							wp_mail( $vendor_email, $subject, $message, $headers );
						}
						
						// Direct message
						$wcfm_messages = sprintf( __( 'You have received reply for Support Ticket <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_support_manage_url( $support_ticket_id ) . '">#' . $support_ticket_id . '</a>' );
						$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $support_vendor_id, 1, 0, $wcfm_messages, 'support', false );
					}
				}
	  	}
			
	  	$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
			if ( $myaccount_page_id ) {
				$myaccount_page_url = trailingslashit( get_permalink( $myaccount_page_id ) );
			}
			echo '{"status": true, "message": "' . $wcfm_support_messages['support_reply_saved'] . '", "redirect": "' . $myaccount_page_url .'view-support-ticket/' . $support_ticket_id . '#support_ticket_reply_' . $support_ticket_reply_id . '"}';
		} else {
			echo '{"status": false, "message": "' . $wcfm_support_messages['no_reply'] . '"}';
		}
		
		die;
	}
}