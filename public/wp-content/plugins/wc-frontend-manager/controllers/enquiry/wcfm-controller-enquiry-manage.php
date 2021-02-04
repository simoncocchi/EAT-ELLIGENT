<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Enquiry Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/enquiry
 * @version   3.2.8
 */

class WCFM_Enquiry_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		if( !defined('WCFM_REST_API_CALL') ) {
			$this->processing();
		}
	}
	
	public function processing() {
		global $WCFM, $wpdb;
		
		$wcfm_enquiry_reply_form_data = array();
	  if( !defined('WCFM_REST_API_CALL') ) {
	  	parse_str($_POST['wcfm_inquiry_reply_form'], $wcfm_enquiry_reply_form_data);
	  } else {
	  	$wcfm_enquiry_reply_form_data = wc_clean($_POST['wcfm_inquiry_reply_form']);
	  }
	  
	  $wcfm_enquiry_messages = get_wcfm_enquiry_manage_messages();
	  $has_error = false;
	  
	  if(isset($_POST['inquiry_reply']) && !empty($_POST['inquiry_reply'])) {
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_enquiry_reply_form_data, 'enquiry_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
			
			// Handle Attachment Uploads - 6.1.5
			$attchments = wcfm_handle_file_upload();
	  	
	  	$inquiry_reply           = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['inquiry_reply'], ENT_QUOTES, 'UTF-8' ) ) );
	  	$inquiry_reply_by        = apply_filters( 'wcfm_message_author', get_current_user_id() );
	  	$inquiry_id              = absint( $wcfm_enquiry_reply_form_data['inquiry_id'] );
	  	$inquiry_product_id      = absint( $wcfm_enquiry_reply_form_data['inquiry_product_id'] );
	  	$inquiry_vendor_id       = absint( $wcfm_enquiry_reply_form_data['inquiry_vendor_id'] );
	  	$inquiry_customer_id     = absint( $wcfm_enquiry_reply_form_data['inquiry_customer_id'] );
	  	$inquiry_customer_name   = $wcfm_enquiry_reply_form_data['inquiry_customer_name'];
	  	$inquiry_customer_email  = $wcfm_enquiry_reply_form_data['inquiry_customer_email'];
	  	
	  	$inquiry_reply           = apply_filters( 'wcfm_enquiry_reply_content', $inquiry_reply, $inquiry_product_id, $inquiry_vendor_id, $inquiry_customer_id );
	  	$inquiry_reply_mail      = $inquiry_reply;
	  	$inquiry_reply           = esc_sql( $inquiry_reply );
	  	
	  	$current_time = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
	  	
	  	if( !defined( 'DOING_WCFM_EMAIL' ) ) 
	  		define( 'DOING_WCFM_EMAIL', true );
	  	
			$wcfm_create_enquiry_reply = "INSERT into {$wpdb->prefix}wcfm_enquiries_response 
																	(`reply`, `enquiry_id`, `product_id`, `vendor_id`, `customer_id`, `customer_name`, `customer_email`, `reply_by`, `posted`)
																	VALUES
																	('{$inquiry_reply}', {$inquiry_id}, {$inquiry_product_id}, {$inquiry_vendor_id}, {$inquiry_customer_id}, '{$inquiry_customer_name}', '{$inquiry_customer_email}', {$inquiry_reply_by}, '{$current_time}')";
													
			$wpdb->query($wcfm_create_enquiry_reply);
			$enquiry_reply_id = $wpdb->insert_id;
			
			if( $enquiry_reply_id ) {
			
				// Attachment Update
				$mail_attachments = array();
				if( !empty( $attchments ) && isset( $attchments['inquiry_attachments'] ) && !empty( $attchments['inquiry_attachments'] ) ) {
					$inquiry_attachments = maybe_serialize( $attchments['inquiry_attachments'] );
					$wcfm_enuquiry_meta_update = "INSERT into {$wpdb->prefix}wcfm_enquiries_response_meta 
																			(`enquiry_response_id`, `key`, `value`)
																			VALUES
																			({$enquiry_reply_id}, 'attchment', '{$inquiry_attachments}' )";
					$wpdb->query($wcfm_enuquiry_meta_update);
					
					// Prepare Mail Attachment
					$upload_dir = wp_upload_dir();
					foreach( $attchments['inquiry_attachments'] as $inquiry_attachment ) {
						if (empty($upload_dir['error'])) {
							$upload_base = trailingslashit( $upload_dir['basedir'] );
							$upload_url = trailingslashit( $upload_dir['baseurl'] );
							$inquiry_attachment = str_replace( $upload_url, $upload_base, $inquiry_attachment );
							$mail_attachments[] = $inquiry_attachment;
						}
					}
				}
			
				if(isset($wcfm_enquiry_reply_form_data['inquiry_stick']) && !empty($wcfm_enquiry_reply_form_data['inquiry_stick'])) {
					$wcfm_update_enquiry    = "UPDATE {$wpdb->prefix}wcfm_enquiries 
																		SET 
																		`reply` = '{$inquiry_reply}',
																		`reply_by` = {$inquiry_reply_by},
																		`is_private` = 0, 
																		`replied` = '{$current_time}'
																		WHERE 
																		`ID` = {$inquiry_id}";
																	
					$wpdb->query($wcfm_update_enquiry);
				} else {
					$wcfm_update_enquiry    = "UPDATE {$wpdb->prefix}wcfm_enquiries 
																		SET 
																		`reply` = '{$inquiry_reply}',
																		`reply_by` = {$inquiry_reply_by},
																		`replied` = '{$current_time}'
																		WHERE 
																		`ID` = {$inquiry_id}";
																	
					$wpdb->query($wcfm_update_enquiry);
				}
			
				// Send mail to customer
				if( $inquiry_customer_email ) {
					$enquiry_for_label =  __( 'Store', 'wc-frontend-manager' );
					if( $inquiry_vendor_id ) $enquiry_for_label = wcfm_get_vendor_store_name( $inquiry_vendor_id ) . ' ' . apply_filters( 'wcfm_sold_by_label', $inquiry_vendor_id, __( 'Store', 'wc-frontend-manager' ) );
					if( $inquiry_product_id ) $enquiry_for_label = get_the_title( $inquiry_product_id );
					
					$enquiry_for =  __( 'Store', 'wc-frontend-manager' );
					if( $inquiry_vendor_id ) $enquiry_for = wcfm_get_vendor_store( $inquiry_vendor_id );
					if( $inquiry_product_id ) $enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_permalink( $inquiry_product_id ) . '">' . get_the_title( $inquiry_product_id ) . '</a>';
					
					$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
					$myaccount_page_url = '';
					if ( $myaccount_page_id ) {
						$myaccount_page_url = trailingslashit( get_permalink( $myaccount_page_id ) );
					}
					$wcfm_myac_modified_endpoints = wcfm_get_option( 'wcfm_myac_endpoints', array() );
					$wcfm_myaccount_view_inquiry_endpoint = ! empty( $wcfm_myac_modified_endpoints['view-inquiry'] ) ? $wcfm_myac_modified_endpoints['view-inquiry'] : 'view-inquiry';
					$enquiry_url = $myaccount_page_url .$wcfm_myaccount_view_inquiry_endpoint.'/' . $inquiry_id;
				
				
					if( !defined( 'DOING_WCFM_EMAIL' ) ) 
						define( 'DOING_WCFM_EMAIL', true );
					
					$reply_mail_subject = "{site_name}: " . __( "Reply for your Inquiry", "wc-frontend-manager" ) . " - {enquiry_for}";
					$reply_mail_body    =    '<br/>' . __( 'Hi', 'wc-frontend-manager' ) . ' {first_name}' .
																	 ',<br/><br/>' . 
																	 sprintf( __( 'We recently have a enquiry from you regarding "%s". Please see our response below: ', 'wc-frontend-manager' ), '{enquiry_for}' ) .
																	 '<br/><br/><strong><i>' . 
																	 '"{inquiry_reply}"' . 
																	 '</i></strong><br/><br/>';
																	 
					if( $inquiry_customer_id )											 
						$reply_mail_body    .=   sprintf( __( 'See details %shere%s.', 'wc-frontend-manager' ), '<a href="{enquiry_url}">', '</a>' );
					
					$reply_mail_body    .=   '<br /><br/>' . __( 'Thank You', 'wc-frontend-manager' ) .
																	 '<br/><br/>';
																	 
					$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $reply_mail_subject );
					$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
					$subject = str_replace( '{enquiry_for}', $enquiry_for_label, $subject );
					$message = str_replace( '{enquiry_for}', $enquiry_for, $reply_mail_body );
					$message = str_replace( '{first_name}', $inquiry_customer_name, $message );
					$message = str_replace( '{enquiry_url}', $enquiry_url, $message );
					$message = str_replace( '{inquiry_reply}', $inquiry_reply_mail, $message );
					$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Inquiry Reply', 'wc-frontend-manager' ) );
					
					$vendor_reply = false;
					if( wcfm_is_marketplace() ) {
						if( $inquiry_vendor_id ) {
							$is_allow_enquiry = $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $inquiry_vendor_id, 'enquiry' );
							if( $is_allow_enquiry && apply_filters( 'wcfm_is_allow_enquiry_vendor_notification', true ) ) {
								$vendor_email = wcfm_get_vendor_store_email_by_vendor( $inquiry_vendor_id );
								if( $vendor_email ) {
									if( apply_filters( 'wcfm_is_allow_enquiry_customer_reply', true ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $inquiry_vendor_id, 'view_email' ) ) {
										$vendor_reply = true;
										$headers[] = 'Reply-to: ' . wcfm_get_vendor_store_name( $inquiry_vendor_id ) . ' ' . apply_filters( 'wcfm_sold_by_label', $inquiry_vendor_id, __( 'Store', 'wc-frontend-manager' ) ) . ' <' . $vendor_email . '>';
									}
								}
							}
							
							// Vendor Transient Clear
							$cache_key = 'wcfm-notification-enquiry-' . $inquiry_vendor_id;
							delete_transient( $cache_key );
						}
					}
					
					if( $vendor_reply ) {
						wp_mail( $inquiry_customer_email, $subject, $message, $headers, $mail_attachments );
					} else {
						wp_mail( $inquiry_customer_email, $subject, $message, '', $mail_attachments );
					}
				}
				
				// Admin Direct message
				if( wcfm_is_vendor() ) {
					if( apply_filters( 'wcfm_is_allow_notification_message', true, 'enquiry', 0 ) ) {
						$wcfm_messages = sprintf( __( 'New reply posted for Inquiry <b>%s</b>', 'wc-frontend-manager' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_manage_url( $inquiry_id ) . '">#' . sprintf( '%06u', $inquiry_id ) . '</a>' );
						$WCFM->wcfm_notification->wcfm_send_direct_message( $inquiry_vendor_id, 0, 0, 1, $wcfm_messages, 'enquiry', false );
					}
				}
				
				// Admin Transient Clear
				$cache_key = 'wcfm-notification-enquiry-0';
				delete_transient( $cache_key );
			}
			
			if( defined('WCFM_REST_API_CALL') ) {
	      return $enquiry_reply_id;
	    }
				
			echo '{"status": true, "message": "' . $wcfm_enquiry_messages['enquiry_reply_saved'] . '", "redirect": "' . get_wcfm_enquiry_manage_url( $inquiry_id ) . '#inquiry_reply_' . $enquiry_reply_id . '"}';
		} else {
			echo '{"status": false, "message": "' . $wcfm_enquiry_messages['no_reply'] . '"}';
		}
		
		die;
	}
}

class WCFM_My_Account_Enquiry_Manage_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMu;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMu, $wpdb, $_POST;
		
		$wcfm_enquiry_reply_form_data = array();
	  parse_str($_POST['wcfm_inquiry_reply_form'], $wcfm_enquiry_reply_form_data);
	  
	  $wcfm_enquiry_messages = get_wcfm_enquiry_manage_messages();
	  $has_error = false;
	  
	  if(isset($_POST['inquiry_reply']) && !empty($_POST['inquiry_reply'])) {
	  	
	  	// Handle Attachment Uploads - 6.1.5
			$attchments = wcfm_handle_file_upload();
	  	
	  	$inquiry_reply           = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['inquiry_reply'], ENT_QUOTES, 'UTF-8' ) ) );
	  	$inquiry_reply_by        = apply_filters( 'wcfm_message_author', get_current_user_id() );
	  	$inquiry_id              = absint( $wcfm_enquiry_reply_form_data['inquiry_id'] );
	  	$inquiry_product_id      = absint( $wcfm_enquiry_reply_form_data['inquiry_product_id'] );
	  	$inquiry_vendor_id       = absint( $wcfm_enquiry_reply_form_data['inquiry_vendor_id'] );
	  	$inquiry_customer_id     = absint( $wcfm_enquiry_reply_form_data['inquiry_customer_id'] );
	  	$inquiry_customer_name   = $wcfm_enquiry_reply_form_data['inquiry_customer_name'];
	  	$inquiry_customer_email  = $wcfm_enquiry_reply_form_data['inquiry_customer_email'];
	  	
	  	$inquiry_reply           = apply_filters( 'wcfm_enquiry_reply_content', $inquiry_reply, $inquiry_product_id, $inquiry_vendor_id, $inquiry_customer_id );
	  	$inquiry_reply_mail      = $inquiry_reply;
	  	$inquiry_reply           = esc_sql( $inquiry_reply );
	  	
	  	$current_time = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
	  	
	  	$wcfm_myac_modified_endpoints = wcfm_get_option( 'wcfm_myac_endpoints', array() );
	  	$wcfm_myaccount_view_inquiry_endpoint = ! empty( $wcfm_myac_modified_endpoints['view-inquiry'] ) ? $wcfm_myac_modified_endpoints['view-inquiry'] : 'view-inquiry';
	  	
	  	if( !defined( 'DOING_WCFM_EMAIL' ) ) 
	  		define( 'DOING_WCFM_EMAIL', true );
	  	
	  	$wcfm_create_enquiry_reply = "INSERT into {$wpdb->prefix}wcfm_enquiries_response 
																	(`reply`, `enquiry_id`, `product_id`, `vendor_id`, `customer_id`, `customer_name`, `customer_email`, `reply_by`, `posted`)
																	VALUES
																	('{$inquiry_reply}', {$inquiry_id}, {$inquiry_product_id}, {$inquiry_vendor_id}, {$inquiry_customer_id}, '{$inquiry_customer_name}', '{$inquiry_customer_email}', {$inquiry_reply_by}, '{$current_time}')";
													
			$wpdb->query($wcfm_create_enquiry_reply);
			$enquiry_reply_id = $wpdb->insert_id;
			
			if( $enquiry_reply_id ) {
			
				// Inquiry Last Reply Update
				$wcfm_update_enquiry    = "UPDATE {$wpdb->prefix}wcfm_enquiries 
																	SET 
																	`reply` = '{$inquiry_reply}',
																	`reply_by` = {$inquiry_reply_by},
																	`replied` = '{$current_time}'
																	WHERE 
																	`ID` = {$inquiry_id}";
				$wpdb->query($wcfm_update_enquiry);
				
				// Attachment Update
				$mail_attachments = array();
				if( !empty( $attchments ) && isset( $attchments['inquiry_attachments'] ) && !empty( $attchments['inquiry_attachments'] ) ) {
					$inquiry_attachments = maybe_serialize( $attchments['inquiry_attachments'] );
					$wcfm_enuquiry_meta_update = "INSERT into {$wpdb->prefix}wcfm_enquiries_response_meta 
																			(`enquiry_response_id`, `key`, `value`)
																			VALUES
																			({$enquiry_reply_id}, 'attchment', '{$inquiry_attachments}' )";
					$wpdb->query($wcfm_enuquiry_meta_update);
					
					// Prepare Mail Attachment
					$upload_dir = wp_upload_dir();
					foreach( $attchments['inquiry_attachments'] as $inquiry_attachment ) {
						if (empty($upload_dir['error'])) {
							$upload_base = trailingslashit( $upload_dir['basedir'] );
							$upload_url = trailingslashit( $upload_dir['baseurl'] );
							$inquiry_attachment = str_replace( $upload_url, $upload_base, $inquiry_attachment );
							$mail_attachments[] = $inquiry_attachment;
						}
					}
				}
				
				// Send mail to admin
				$enquiry_for =  __( 'Store', 'wc-frontend-manager' );
				if( $inquiry_vendor_id ) $enquiry_for = wcfm_get_vendor_store( $inquiry_vendor_id );
				if( $inquiry_product_id ) $enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_permalink( $inquiry_product_id ) . '">' . get_the_title( $inquiry_product_id ) . '</a>';
					
				$mail_to = apply_filters( 'wcfm_admin_email_notification_receiver', get_bloginfo( 'admin_email' ), 'enquiry' );
				$reply_mail_subject = '{site_name}: ' . __( 'Inquiry Reply', 'wc-frontend-manager' ) . ' - ' . __( 'Inquiry', 'wc-frontend-manager' ) . ' #{enquiry_id}';
				$reply_mail_body =   '<br/>' . __( 'Hi', 'wc-frontend-manager' ) .
														 ',<br/><br/>' . 
														 __( 'You have received reply for your "{enquiry_for}" inquiry. Please see our response below: ', 'wc-frontend-manager' ) .
														 '<br/><br/><strong><i>' . 
														 '"{enquiry_reply}"' . 
														 '</i></strong><br/><br/>' .
														 __( 'See details here', 'wc-frontend-manager' ) . ': <a href="{support_url}">' . __( 'Inquiry', 'wc-frontend-manager' ) . ' #{enquiry_id}</a>' .
														 '<br /><br/>' . __( 'Thank You', 'wc-frontend-manager' ) .
														 '<br/><br/>';
				
				//$headers[] = 'From: [' . get_bloginfo( 'name' ) . '] ' . __( 'Inquiry Reply', 'wc-frontend-manager' );
				if( apply_filters( 'wcfm_is_allow_enquiry_by_customer', true ) ) {
					$headers[] = 'Reply-to: ' . $inquiry_customer_name . ' <' . $inquiry_customer_email . '>';
				}
				//$headers[] = 'Cc: ' . $mail_to;
				$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $reply_mail_subject );
				$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
				$subject = str_replace( '{enquiry_id}', sprintf( '%06u', $inquiry_id ), $subject );
				$message = str_replace( '{enquiry_for}', $enquiry_for, $reply_mail_body );
				$message = str_replace( '{support_url}', get_wcfm_enquiry_manage_url( $inquiry_id ), $message );
				$message = str_replace( '{enquiry_reply}', $inquiry_reply_mail, $message );
				$message = str_replace( '{enquiry_id}', sprintf( '%06u', $inquiry_id ), $message );
				$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Reply to Inquiry', 'wc-frontend-manager' ) . ' #' . sprintf( '%06u', $inquiry_id ) );
				
				if( apply_filters( 'wcfm_is_allow_notification_email', true, 'enquiry', 0 ) ) {
					if( apply_filters( 'wcfm_is_allow_enquiry_by_customer', true ) ) {
						wp_mail( $mail_to, $subject, $message, $headers, $mail_attachments );
					} else {
						wp_mail( $mail_to, $subject, $message, '', $mail_attachments );
					}
				}
				
				// Direct message
				if( apply_filters( 'wcfm_is_allow_notification_message', true, 'enquiry', 0 ) ) {
					$wcfm_messages = sprintf( __( 'New reply received for Inquiry <b>%s</b>', 'wc-frontend-manager' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_manage_url( $inquiry_id ) . '">#' . sprintf( '%06u', $inquiry_id ) . '</a>' );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'enquiry', false );
				}
				
				// Semd email to vendor
				if( wcfm_is_marketplace() ) {
					if( $inquiry_vendor_id ) {
						$is_allow_enquiry = $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $inquiry_vendor_id, 'enquiry' );
						if( $is_allow_enquiry && apply_filters( 'wcfm_is_allow_enquiry_vendor_notification', true ) ) {
							$vendor_email = wcfm_get_vendor_store_email_by_vendor( $inquiry_vendor_id );
							if( $vendor_email && apply_filters( 'wcfm_is_allow_notification_email', true, 'enquiry', $inquiry_vendor_id ) ) {
								if( apply_filters( 'wcfm_is_allow_enquiry_by_customer', true ) ) {
									wp_mail( $vendor_email, $subject, $message, $headers, $mail_attachments );
								} else {
									wp_mail( $vendor_email, $subject, $message, $headers, $mail_attachments );
								}
							}
							
							// Direct message
							if( apply_filters( 'wcfm_is_allow_notification_message', true, 'enquiry', $inquiry_vendor_id ) ) {
								$wcfm_messages = sprintf( __( 'New reply received for Inquiry <b>%s</b>', 'wc-frontend-manager' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_manage_url( $inquiry_id ) . '">#' . sprintf( '%06u', $inquiry_id ) . '</a>' );
								$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $inquiry_vendor_id, 1, 0, $wcfm_messages, 'enquiry', false );
							}
						}
					}
				}
				
			}
			
	  	$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
	  	$myaccount_page_url = '';
			if ( $myaccount_page_id ) {
				$myaccount_page_url = trailingslashit( get_permalink( $myaccount_page_id ) );
			}
			echo '{"status": true, "message": "' . $wcfm_enquiry_messages['enquiry_reply_saved'] . '", "redirect": "' . $myaccount_page_url .$wcfm_myaccount_view_inquiry_endpoint.'/' . $inquiry_id . '#inquiry_reply_' . $enquiry_reply_id . '"}';
		} else {
			echo '{"status": false, "message": "' . $wcfm_enquiry_messages['no_reply'] . '"}';
		}
		
		die;
	}
}