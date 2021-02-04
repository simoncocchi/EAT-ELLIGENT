<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Enquiry Form Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/enquiry
 * @version   3.2.8
 */

class WCFM_Enquiry_Form_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb;
		
		$wcfm_enquiry_tab_form_data = array();
	  parse_str($_POST['wcfm_enquiry_tab_form'], $wcfm_enquiry_tab_form_data);
	  
	  $wcfm_enquiry_messages = get_wcfm_enquiry_manage_messages();
	  $has_error = false;
	  
	  // Google reCaptcha support
	  if ( function_exists( 'gglcptch_init' ) ) {
			if(isset($wcfm_enquiry_tab_form_data['g-recaptcha-response']) && !empty($wcfm_enquiry_tab_form_data['g-recaptcha-response'])) {
				$_POST['g-recaptcha-response'] = $wcfm_enquiry_tab_form_data['g-recaptcha-response'];
			}
			$check_result = apply_filters( 'gglcptch_verify_recaptcha', true, 'string', 'wcfm_enquiry_form' );
			if ( true === $check_result ) {
					/* do necessary action */
			} else { 
				echo '{"status": false, "message": "' . $check_result . '"}';
				die;
			}
		} elseif ( class_exists( 'anr_captcha_class' ) && function_exists( 'anr_captcha_form_field' ) ) {
			$check_result = anr_verify_captcha( $wcfm_enquiry_tab_form_data['g-recaptcha-response'] );
			if ( true === $check_result ) {
					/* do necessary action */
			} else { 
				echo '{"status": false, "message": "' . __( 'Captcha failed, please try again.', 'wc-frontend-manager' ) . '"}';
				die;
			}
		}
		
		if( !defined('WCFM_REST_API_CALL') ) {
	  	if( isset( $wcfm_enquiry_tab_form_data['wcfm_nonce'] ) && !empty( $wcfm_enquiry_tab_form_data['wcfm_nonce'] ) ) {
	  		if( !wp_verify_nonce( $wcfm_enquiry_tab_form_data['wcfm_nonce'], 'wcfm_enquiry' ) ) {
	  			echo '{"status": false, "message": "' . __( 'Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager' ) . '"}';
	  			die;
	  		}
	  	}
	  }
	  
	  if(isset($wcfm_enquiry_tab_form_data['enquiry']) && !empty($wcfm_enquiry_tab_form_data['enquiry'])) {
	  	
	  	$enquiry = apply_filters( 'wcfm_editor_content_before_save', wcfm_stripe_newline( strip_tags( $wcfm_enquiry_tab_form_data['enquiry'] ) ) );
	  	$reply = '';
	  	
	  	$author_id = 0;
	  	$product_id = 0;
	  	if( isset( $wcfm_enquiry_tab_form_data['product_id'] ) && !empty( $wcfm_enquiry_tab_form_data['product_id'] ) ) {
				$product_id = absint( $wcfm_enquiry_tab_form_data['product_id'] );
				if( $product_id ) {
					$product_post = get_post( $product_id );
					$author_id = $product_post->post_author;
				}
			}
	  	
	  	$vendor_id = 0;
	  	if( isset( $wcfm_enquiry_tab_form_data['vendor_id'] ) && !empty( $wcfm_enquiry_tab_form_data['vendor_id'] ) ) {
	  		$vendor_id = absint( $wcfm_enquiry_tab_form_data['vendor_id'] );
	  		$author_id = $vendor_id;
	  	} elseif( $author_id && wcfm_is_vendor( $author_id ) ) {
	  		$vendor_id = $author_id;
	  	}
	  	
	  	if( !is_user_logged_in() ) {
	  		$customer_id = 0;
	  		$customer_name = $wcfm_enquiry_tab_form_data['customer_name'];
	  		$customer_email = $wcfm_enquiry_tab_form_data['customer_email'];
	  	} else {
	  		$customer_id = get_current_user_id();
	  		$userdata = get_userdata( $customer_id );
				$first_name = $userdata->first_name;
				$last_name  = $userdata->last_name;
				$display_name  = $userdata->display_name;
				if( $first_name ) {
					$customer_name = $first_name . ' ' . $last_name;
				} else {
					$customer_name = $display_name;
				}
	  		$customer_email = $userdata->user_email;
	  	}
	  	
	  	$enquiry      = apply_filters( 'wcfm_enquiry_content', $enquiry, $product_id, $vendor_id, $customer_id );
	  	$enquiry_mail = $enquiry;
	  	$enquiry      = esc_sql( $enquiry );
	  	
	  	if( !defined( 'DOING_WCFM_EMAIL' ) ) 
	  		define( 'DOING_WCFM_EMAIL', true );
	  	
	  	$reply_by = 0;
	  	$is_private = 1;
	  	$current_time = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
	  	
	  	$wcfm_create_enquiry    = "INSERT into {$wpdb->prefix}wcfm_enquiries 
																(`enquiry`, `reply`, `author_id`, `product_id`, `vendor_id`, `customer_id`, `customer_name`, `customer_email`, `reply_by`, `is_private`, `posted`, `replied`)
																VALUES
																('{$enquiry}', '{$reply}', {$author_id}, {$product_id}, {$vendor_id}, {$customer_id}, '{$customer_name}', '{$customer_email}', {$reply_by}, {$is_private}, '{$current_time}', '{$current_time}')";
															
			$wpdb->query($wcfm_create_enquiry);
			$enquiry_id = $wpdb->insert_id;
			
			if($enquiry_id ) {
			
				$additional_info = '';
				$wcfm_options = $WCFM->wcfm_options;
				$wcfm_enquiry_custom_fields = isset( $wcfm_options['wcfm_enquiry_custom_fields'] ) ? $wcfm_options['wcfm_enquiry_custom_fields'] : array();
				$wcfm_enquiry_meta_values = array();
				if( isset( $wcfm_enquiry_tab_form_data['wcfm_enquiry_meta'] ) ) $wcfm_enquiry_meta_values = $wcfm_enquiry_tab_form_data['wcfm_enquiry_meta'];
				if( !empty( $wcfm_enquiry_custom_fields ) && !empty( $wcfm_enquiry_meta_values ) ) {
					foreach( $wcfm_enquiry_custom_fields as $wcfm_enquiry_custom_field ) {
						if( !isset( $wcfm_enquiry_custom_field['enable'] ) ) continue;
						if( !$wcfm_enquiry_custom_field['label'] ) continue;
						$wcfm_enquiry_custom_field['name'] = sanitize_title( $wcfm_enquiry_custom_field['label'] );
						if( isset( $wcfm_enquiry_meta_values[ $wcfm_enquiry_custom_field['name'] ] ) ) {
							$wcfm_create_enquiry_meta    = "INSERT into {$wpdb->prefix}wcfm_enquiries_meta 
																							(`enquiry_id`, `key`, `value`)
																							VALUES
																							({$enquiry_id}, '{$wcfm_enquiry_custom_field['label']}', '{$wcfm_enquiry_meta_values[ $wcfm_enquiry_custom_field['name'] ]}')";
							$wpdb->query($wcfm_create_enquiry_meta);
							$additional_info .= '<tr><td>' . __( $wcfm_enquiry_custom_field['label'], 'wc-frontend-manager' ) . '</td><td>' . $wcfm_enquiry_meta_values[ $wcfm_enquiry_custom_field['name'] ] . '</td>';
						}
					}
				}
				if( $additional_info ) $additional_info = '<u>' . __( 'Additional Info', 'wc-frontend-manager' ) . ':-</u><table border="1">' . $additional_info . '</table><br /><br />';
				
				$enquiry_for_label =  __( 'Store', 'wc-frontend-manager' );
				if( $vendor_id ) $enquiry_for_label = wcfm_get_vendor_store_name( $vendor_id ) . ' ' . __( 'Store', 'wc-frontend-manager' );
				if( $product_id ) $enquiry_for_label = get_the_title( $product_id );
				
				//$enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_url() . '">' . __( 'Store', 'wc-frontend-manager' ) . '</a>';
				//if( $vendor_id ) $enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_url() . '">' . wcfm_get_vendor_store_name( $vendor_id ) . ' ' . apply_filters( 'wcfm_sold_by_label', $vendor_id, __( 'Store', 'wc-frontend-manager' ) ) . '</a>';
				//if( $product_id ) $enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_url() . '">' . get_the_title( $product_id ) . '</a>';
				
				/*$mail_to = apply_filters( 'wcfm_admin_email_notification_receiver', get_bloginfo( 'admin_email' ), 'enquiry' );
				$reply_mail_subject = "{site_name}: " . __( "New enquiry for", "wc-frontend-manager" ) . " - {enquiry_for}";
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
				
				if( apply_filters( 'wcfm_is_allow_enquiry_by_customer', true ) ) {
					//define( 'DOING_WCFM_RESTRICTED_EMAIL', true );
					//$headers[] = 'From: [' . get_bloginfo( 'name' ) . '] ' . __( 'Enquiry', 'wc-frontend-manager' ) . ': ' . $customer_name . ' <' . $customer_email . '>';
					$headers[] = 'Reply-to: ' . $customer_name . ' <' . $customer_email . '>';
				}
				//$headers[] = 'Cc: ' . $customer_email;
				$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $reply_mail_subject );
				$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
				$subject = str_replace( '{enquiry_for}', $enquiry_for_label, $subject );
				$message = str_replace( '{enquiry_for}', $enquiry_for, $reply_mail_body );
				$message = str_replace( '{enquiry_url}', get_wcfm_enquiry_manage_url( $enquiry_id ), $message );
				$message = str_replace( '{enquiry}', $enquiry, $message );
				$message = str_replace( '{additional_info}', $additional_info, $message );
				$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'New Enquiry', 'wc-frontend-manager' ) );
				
				if( apply_filters( 'wcfm_is_allow_notification_email', true, 'enquiry', 0 ) ) {
					if( apply_filters( 'wcfm_is_allow_enquiry_customer_reply', true ) ) {
						wp_mail( $mail_to, $subject, $message, $headers );
					} else {
						wp_mail( $mail_to, $subject, $message );
					}
				}*/
				
				// Send mail to admin
				if( apply_filters( 'wcfm_is_allow_notification_email', true, 'enquiry', 0 ) ) {
					$wcfm_email = WC()->mailer()->emails['WCFM_Email_New_enquiry'];
					if( $wcfm_email ) {
						$wcfm_email->trigger( array( 'enquiry_id' => $enquiry_id, 'product_id' => $product_id, 'vendor_id' => $vendor_id, 'enquiry' => $enquiry_mail, 'additional_info' => $additional_info, 'customer_name' => $customer_name, 'customer_email' => $customer_email, 'is_admin' => true ) );
					}
				}
				
				// Direct message
				if( apply_filters( 'wcfm_is_allow_notification_message', true, 'enquiry', 0 ) ) {
					$wcfm_messages = sprintf( __( 'New Inquiry <b>%s</b> received for <b>%s</b>', 'wc-frontend-manager' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_manage_url( $enquiry_id ) . '">#' . sprintf( '%06u', $enquiry_id ) . '</a>', $enquiry_for_label );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'enquiry', false );
				}
				
				// Semd email to vendor
				if( wcfm_is_marketplace() ) {
					if( $vendor_id ) {
						$is_allow_enquiry = wcfm_vendor_has_capability( $vendor_id, 'enquiry' );
						if( $is_allow_enquiry && apply_filters( 'wcfm_is_allow_enquiry_vendor_notification', true ) ) {
							$vendor_email = wcfm_get_vendor_store_email_by_vendor( $vendor_id );
							if( $vendor_email && apply_filters( 'wcfm_is_allow_notification_email', true, 'enquiry', $vendor_id ) ) {
								$wcfm_email = WC()->mailer()->emails['WCFM_Email_New_enquiry'];
								if( $wcfm_email ) {
									$wcfm_email->trigger( array( 'enquiry_id' => $enquiry_id, 'product_id' => $product_id, 'vendor_id' => $vendor_id, 'enquiry' => $enquiry_mail, 'additional_info' => $additional_info, 'customer_name' => $customer_name, 'customer_email' => $customer_email, 'is_admin' => false ) );
								}
								/*if( apply_filters( 'wcfm_is_allow_enquiry_customer_reply', true ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'view_email' ) ) {
									wp_mail( $vendor_email, $subject, $message, $headers );
								} else {
									wp_mail( $vendor_email, $subject, $message );
								}*/
							}
							
							// Direct message
							if( apply_filters( 'wcfm_is_allow_notification_message', true, 'enquiry', $vendor_id ) ) {
								$wcfm_messages = sprintf( __( 'New Inquiry <b>%s</b> received for <b>%s</b>', 'wc-frontend-manager' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_manage_url( $enquiry_id ) . '">#' . sprintf( '%06u', $enquiry_id ) . '</a>', $enquiry_for_label );
								$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'enquiry', false );
							}
						}
						
						// Vendor Transient Clear
						$cache_key = 'wcfm-notification-enquiry-' . $vendor_id;
						delete_transient( $cache_key );
					}
				}
				
				// Admin Transient Clear
				$cache_key = 'wcfm-notification-enquiry-0';
				delete_transient( $cache_key );
				
				do_action( 'wcfm_after_enquiry_submit',  $enquiry_id, $customer_id, $product_id, $vendor_id, $enquiry, $wcfm_enquiry_tab_form_data );
			}
			
			echo '{"status": true, "message": "' . $wcfm_enquiry_messages['enquiry_saved'] . '"}';
		} else {
			echo '{"status": false, "message": "' . $wcfm_enquiry_messages['no_enquiry'] . '"}';
		}
		
		die;
	}
}