<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Shop Customers Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmgs/controllers
 * @version   1.0.0
 */

class WCFM_Customers_Manage_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMu;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMu, $wpdb, $wcfm_customer_form_data;
		
		$wcfm_customer_form_data = array();
	  parse_str($_POST['wcfm_customers_manage_form'], $wcfm_customer_form_data);
	  
	  $wcfm_customer_messages = get_wcfm_customers_manage_messages();
	  $has_error = false;
	  
	  if( !defined('WCFM_REST_API_CALL') ) {
	  	if( isset( $wcfm_customer_form_data['wcfm_nonce'] ) && !empty( $wcfm_customer_form_data['wcfm_nonce'] ) ) {
	  		if( !wp_verify_nonce( $wcfm_customer_form_data['wcfm_nonce'], 'wcfm_customers_manage' ) ) {
	  			echo '{"status": false, "message": "' . __( 'Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager' ) . '"}';
	  			die;
	  		}
	  	}
	  }
	  
	  if(isset($wcfm_customer_form_data['user_name']) && !empty($wcfm_customer_form_data['user_name'])) {
	  	if(isset($wcfm_customer_form_data['user_email']) && !empty($wcfm_customer_form_data['user_email'])) {
				$customer_id = 0;
				$is_update = false;
				
				// WCFM form custom validation filter
				$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_customer_form_data, 'customer_manage' );
				if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
					$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
					if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
					echo '{"status": false, "message": "' . $custom_validation_error . '"}';
					die;
				}
				
				if ( ! is_email( $wcfm_customer_form_data['user_email'] ) ) {
					echo '{"status": false, "message": "' . __( 'Please provide a valid email address.', 'woocommerce' ) . '"}';
					die;
				}
				
				if ( ! validate_username( $wcfm_customer_form_data['user_name'] ) ) {
					echo '{"status": false, "message": "' . __( 'Please enter a valid account username.', 'woocommerce' ) . '"}';
					die;
				}
				
				if( isset($wcfm_customer_form_data['customer_id']) && $wcfm_customer_form_data['customer_id'] != 0 ) {
					$customer_id = absint( $wcfm_customer_form_data['customer_id'] );
					$is_update = true;
				} else {
					if( username_exists( $wcfm_customer_form_data['user_name'] ) ) {
						$has_error = true;
						echo '{"status": false, "message": "' . $wcfm_customer_messages['username_exists'] . '"}';
					} else {
						if( email_exists( $wcfm_customer_form_data['user_email'] ) == false ) {
							
						} else {
							$has_error = true;
							echo '{"status": false, "message": "' . $wcfm_customer_messages['email_exists'] . '"}';
						}
					}
				}
				
				$password = wp_generate_password( $length = 12, $include_standard_special_chars=false );
				if( !$has_error ) {
					$user_data = array( 'user_login'     => $wcfm_customer_form_data['user_name'],
															'user_email'     => $wcfm_customer_form_data['user_email'],
															'display_name'   => $wcfm_customer_form_data['user_name'],
															'nickname'       => $wcfm_customer_form_data['user_name'],
															'first_name'     => $wcfm_customer_form_data['first_name'],
															'last_name'      => $wcfm_customer_form_data['last_name'],
															'user_pass'      => $password,
															'role'           => apply_filters( 'wcfm_added_customer_user_role', 'customer' ),
															'ID'             => $customer_id
															);
					if( $is_update ) {
						unset( $user_data['user_login'] );
						unset( $user_data['display_name'] );
						unset( $user_data['nickname'] );
						unset( $user_data['user_pass'] );
						unset( $user_data['role'] );
						$customer_id = wp_update_user( $user_data ) ;
					} else {
						$customer_id = wp_insert_user( $user_data ) ;
						
						// Customer Real Author
						update_user_meta( $customer_id, '_wcfm_customer_author', get_current_user_id() );
					}
						
					if( !$customer_id ) {
						$has_error = true;
					} else {
						if( apply_filters( 'wcfm_allow_customer_billing_details', true ) ) {
							$wcfm_customer_billing_fields = array( 
																						'billing_first_name'  => 'bfirst_name',
																						'billing_last_name'   => 'blast_name',
																						'billing_company'     => 'bcompany_name',
																						'billing_phone'       => 'bphone',
																						'billing_address_1'   => 'baddr_1',
																						'billing_address_2'   => 'baddr_2',
																						'billing_country'     => 'bcountry',
																						'billing_city'        => 'bcity',
																						'billing_state'       => 'bstate',
																						'billing_postcode'    => 'bzip'
																					);
							foreach( $wcfm_customer_billing_fields as $wcfm_customer_default_key => $wcfm_customer_default_field ) {
								update_user_meta( $customer_id, $wcfm_customer_default_key, $wcfm_customer_form_data[$wcfm_customer_default_field] );
							}
						}
						
						if( apply_filters( 'wcfm_allow_customer_shipping_details', true ) ) {
							if( isset( $wcfm_customer_form_data['same_as_billing'] ) ) {
								update_user_meta( $customer_id, 'same_as_billing', 'yes' );
								$wcfm_customer_billing_shipping_fields = array( 
																					'shipping_first_name'  => 'bfirst_name',
																					'shipping_last_name'   => 'blast_name',
																					'shipping_company'     => 'bcompany_name',
																					'shipping_address_1'   => 'baddr_1',
																					'shipping_address_2'   => 'baddr_2',
																					'shipping_country'     => 'bcountry',
																					'shipping_city'        => 'bcity',
																					'shipping_state'       => 'bstate',
																					'shipping_postcode'    => 'bzip'
																			  );
								foreach( $wcfm_customer_billing_shipping_fields as $wcfm_customer_shipping_key => $wcfm_customer_shipping_field ) {
									update_user_meta( $customer_id, $wcfm_customer_shipping_key, $wcfm_customer_form_data[$wcfm_customer_shipping_field] );
								}
							} else {
								update_user_meta( $customer_id, 'same_as_billing', 'no' );
								$wcfm_customer_shipping_fields = array( 
																							'shipping_first_name'  => 'sfirst_name',
																							'shipping_last_name'   => 'slast_name',
																							'shipping_company'     => 'scompany_name',
																							'shipping_address_1'   => 'saddr_1',
																							'shipping_address_2'   => 'saddr_2',
																							'shipping_country'     => 'scountry',
																							'shipping_city'        => 'scity',
																							'shipping_state'       => 'sstate',
																							'shipping_postcode'    => 'szip'
																						);
								foreach( $wcfm_customer_shipping_fields as $wcfm_customer_shipping_key => $wcfm_customer_shipping_field ) {
									update_user_meta( $customer_id, $wcfm_customer_shipping_key, $wcfm_customer_form_data[$wcfm_customer_shipping_field] );
								}
							}
						}
						
						
						if( !$is_update ) {
							if( !defined( 'DOING_WCFM_EMAIL' ) ) 
								define( 'DOING_WCFM_EMAIL', true );
							
							// Sending Mail to new user
							$mail_to = $wcfm_customer_form_data['user_email'];
							$new_account_mail_subject = "{site_name}: New Account Created";
							$new_account_mail_body = '<br/>' . __( 'Dear', 'wc-frontend-manager' ) . ' {first_name}' .
																			 ',<br/><br/>' . 
																			 __( 'Your account has been created as {user_role}. Follow the bellow details to log into the system', 'wc-frontend-manager' ) .
																			 '<br/><br/>' . 
																			 __( 'Site', 'wc-frontend-manager' ) . ': {site_url}' . 
																			 '<br/>' .
																			 __( 'Login', 'wc-frontend-manager' ) . ': {username}' .
																			 '<br/>' . 
																			 __( 'Password', 'wc-frontend-manager' ) . ': {password}' .
																			 '<br /><br/>Thank You' .
																			 '<br/><br/>';
							$notification_mail_body = apply_filters( 'wcfm_notification_mail_content', $new_account_mail_body, 'customer_new_account_created', $wcfm_customer_form_data, $customer_id );
							
							$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $new_account_mail_subject );
							$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
							$message = str_replace( '{site_url}', get_bloginfo( 'url' ), $new_account_mail_body );
							$message = str_replace( '{first_name}', $wcfm_customer_form_data['first_name'], $message );
							$message = str_replace( '{username}', $wcfm_customer_form_data['user_name'], $message );
							$message = str_replace( '{password}', $password, $message );
							$message = str_replace( '{user_role}', 'Customer', $message );
							$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'New Account', 'wc-frontend-manager' ) );
							
							wp_mail( $mail_to, $subject, $message );
						
							update_user_meta( $customer_id, 'show_admin_bar_front', false );
							
							// Desktop notification message for new_customer
							$author_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
							$author_is_admin = 0;
							$author_is_vendor = 1;
							$message_to = 0;
							$wcfm_messages = sprintf( __( 'A new customer <b>%s</b> added to the store by <b>%s</b>', 'wc-frontend-manager' ), '<a class="wcfm_dashboard_item_title" href="' . get_wcfm_customers_details_url( $customer_id ) . '">' . $wcfm_customer_form_data['first_name'] . ' ' . $wcfm_customer_form_data['last_name'] . '</a>', get_user_by( 'id', $author_id )->display_name );
							$WCFM->wcfm_notification->wcfm_send_direct_message( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, 'new_customer' );
						}
						
						if( function_exists( 'wcfmmp_get_store_url' ) && !wcfm_is_vendor() ) {
							if( isset( $wcfm_customer_form_data['wcfm_vendor'] ) && !empty( $wcfm_customer_form_data['wcfm_vendor'] ) ) {
								update_user_meta( $customer_id, '_wcfm_vendor', $wcfm_customer_form_data['wcfm_vendor'] );
							} else {
								delete_user_meta( $customer_id, '_wcfm_vendor' );
							}
						}
						
						do_action( 'wcfm_customers_manage', $customer_id, $wcfm_customer_form_data );
					}
					
					if(!$has_error) { echo '{"status": true, "message": "' . $wcfm_customer_messages['customer_saved'] . '", "redirect": "' . apply_filters( 'wcfm_customer_manage_redirect', get_wcfm_customers_manage_url($customer_id), $customer_id ) . '"}'; }
					else { echo '{"status": false, "message": "' . $wcfm_customer_messages['customer_failed'] . '"}'; }
				}
			} else {
				echo '{"status": false, "message": "' . $wcfm_customer_messages['no_email'] . '"}';
			}
	  	
	  } else {
			echo '{"status": false, "message": "' . $wcfm_customer_messages['no_username'] . '"}';
		}
		
		die;
	}
}