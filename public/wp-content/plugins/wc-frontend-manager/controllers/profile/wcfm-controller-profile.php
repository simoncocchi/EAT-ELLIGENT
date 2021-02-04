<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Profile Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   2.2.5
 */

class WCFM_Profile_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $blog_id;
		
		$user_id = get_current_user_id();
		
		$wcfm_profile_default_fields = array( 'first_name'          => 'first_name',
																					'last_name'           => 'last_name',
																					//'billing_email'       => 'email',
																					'billing_phone'       => 'phone',
																					'billing_first_name'  => 'bfirst_name',
																					'billing_last_name'   => 'blast_name',
																					'billing_address_1'   => 'baddr_1',
																					'billing_address_2'   => 'baddr_2',
																					'billing_country'     => 'bcountry',
																					'billing_city'        => 'bcity',
																					'billing_state'       => 'bstate',
																					'billing_postcode'    => 'bzip'
																			  );
		
		$wcfm_profile_shipping_fields = array( 
																					'shipping_first_name'  => 'sfirst_name',
																					'shipping_last_name'   => 'slast_name',
																					'shipping_address_1'   => 'saddr_1',
																					'shipping_address_2'   => 'saddr_2',
																					'shipping_country'     => 'scountry',
																					'shipping_city'        => 'scity',
																					'shipping_state'       => 'sstate',
																					'shipping_postcode'    => 'szip'
																			  );
		
		$wcfm_profile_billing_shipping_fields = array( 
																					'shipping_first_name'  => 'bfirst_name',
																					'shipping_last_name'   => 'blast_name',
																					'shipping_address_1'   => 'baddr_1',
																					'shipping_address_2'   => 'baddr_2',
																					'shipping_country'     => 'bcountry',
																					'shipping_city'        => 'bcity',
																					'shipping_state'       => 'bstate',
																					'shipping_postcode'    => 'bzip'
																			  );
		
		$wcfm_profile_form_data = array();
	  parse_str($_POST['wcfm_profile_form'], $wcfm_profile_form);
	  
	  if( !defined('WCFM_REST_API_CALL') ) {
	  	if( isset( $wcfm_profile_form['wcfm_nonce'] ) && !empty( $wcfm_profile_form['wcfm_nonce'] ) ) {
	  		if( !wp_verify_nonce( $wcfm_profile_form['wcfm_nonce'], 'wcfm_profile' ) ) {
	  			echo '{"status": false, "message": "' . __( 'Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager' ) . '"}';
	  			die;
	  		}
	  	}
	  }
	  
	  // WCFM form custom validation filter
		$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_profile_form, 'profile_manage' );
		if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
			$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
			if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
			echo '{"status": false, "message": "' . $custom_validation_error . '"}';
			die;
		}
	  
	  // sanitize
		//$wcfm_profile_form = array_map( 'sanitize_text_field', $wcfm_profile_form );
		//$wcfm_profile_form = array_map( 'stripslashes', $wcfm_profile_form );
		
		$description = ! empty( $_POST['about'] ) ? stripslashes( html_entity_decode( $_POST['about'], ENT_QUOTES, 'UTF-8' ) ) : '';
		update_user_meta( $user_id, 'description', apply_filters( 'wcfm_editor_content_before_save', $description ) );
		
		// Password
		if( isset( $wcfm_profile_form['password'] ) && !empty( $wcfm_profile_form['password'] ) ) {
			wp_set_password( trim($wcfm_profile_form['password']), $user_id );
		}
		
		// Locale
		if( isset( $wcfm_profile_form['locale'] ) && !empty( $wcfm_profile_form['locale'] ) ) {
			if( $wcfm_profile_form['locale'] != 'site-default' ) {
				update_user_meta( $user_id, 'locale', $wcfm_profile_form['locale'] );
			} else {
				delete_user_meta( $user_id, 'locale' );
			}
		}
		
		// Set User Avatar
		if(isset($wcfm_profile_form['wp_user_avatar']) && !empty($wcfm_profile_form['wp_user_avatar'])) {
			$wp_user_avatar = $WCFM->wcfm_get_attachment_id($wcfm_profile_form['wp_user_avatar']);
			// Remove old attachment postmeta
      delete_metadata( 'post', null, '_wp_attachment_wp_user_avatar', $user_id, true );
      // Create new attachment postmeta
      add_post_meta( $wp_user_avatar, '_wp_attachment_wp_user_avatar', $user_id );
      // Update usermeta
			update_user_meta( $user_id, $wpdb->get_blog_prefix($blog_id).'user_avatar', $wp_user_avatar );
		} else {
			delete_user_meta( $user_id, $wpdb->get_blog_prefix($blog_id).'user_avatar' );
		}
		
		foreach( $wcfm_profile_default_fields as $wcfm_profile_default_key => $wcfm_profile_default_field ) {
			if( isset( $wcfm_profile_form[$wcfm_profile_default_field] ) ) {
				update_user_meta( $user_id, $wcfm_profile_default_key, $wcfm_profile_form[$wcfm_profile_default_field] );
			}
		}
		
		if( isset( $wcfm_profile_form['same_as_billing'] ) ) {
			update_user_meta( $user_id, 'same_as_billing', 'yes' );
			foreach( $wcfm_profile_billing_shipping_fields as $wcfm_profile_shipping_key => $wcfm_profile_shipping_field ) {
				if( isset( $wcfm_profile_form[$wcfm_profile_shipping_field] ) ) {
					update_user_meta( $user_id, $wcfm_profile_shipping_key, $wcfm_profile_form[$wcfm_profile_shipping_field] );
				}
			}
		} else {
			update_user_meta( $user_id, 'same_as_billing', 'no' );
			foreach( $wcfm_profile_shipping_fields as $wcfm_profile_shipping_key => $wcfm_profile_shipping_field ) {
				if( isset( $wcfm_profile_form[$wcfm_profile_shipping_field] ) ) {
					update_user_meta( $user_id, $wcfm_profile_shipping_key, $wcfm_profile_form[$wcfm_profile_shipping_field] );
				}
			}
		}
		
		if( apply_filters( 'wcfm_is_allow_social_profile', true ) ) {
			if( wcfm_is_vendor() ) {
				$is_marketplace = wcfm_is_marketplace();
				if( $is_marketplace == 'wcvendors' )  {
					$wcfm_profile_social_fields = array( 
																							'_wcv_twitter_username'    => 'twitter',
																							'_wcv_facebook_url'        => 'facebook',
																							'_wcv_instagram_username'  => 'instagram',
																							'_wcv_youtube_url'         => 'youtube',
																							'_wcv_linkedin_url'        => 'linkdin',
																							'_wcv_googleplus_url'      => 'google_plus',
																							'_wcv_snapchat_username'   => 'snapchat',
																							'_wcv_pinterest_url'       => 'pinterest',
																							'googleplus'               => 'google_plus',
																							'twitter'                  => 'twitter',
																							'facebook'                 => 'facebook',
																						);
				} elseif( $is_marketplace == 'wcmarketplace' )  {
					$wcfm_profile_social_fields = array( 
																							'_vendor_twitter_profile'      => 'twitter',
																							'_vendor_fb_profile'           => 'facebook',
																							'_vendor_instagram'            => 'instagram',
																							'_vendor_youtube'              => 'youtube',
																							'_vendor_linkdin_profile'      => 'linkdin',
																							'_vendor_google_plus_profile'  => 'google_plus',
																							'_vendor_snapchat'             => 'snapchat',
																							'_vendor_pinterest'            => 'pinterest',
																							'googleplus'                   => 'google_plus',
																							'twitter'                      => 'twitter',
																							'facebook'                     => 'facebook',
																						);
				} elseif( $is_marketplace == 'dokan' )  {
					$wcfm_profile_social_fields = array( 
																							'twitter'    => 'twitter',
																							'fb'         => 'facebook',
																							'instagram'  => 'instagram',
																							'youtube'    => 'youtube',
																							'linkedin'   => 'linkdin',
																							'gplus'      => 'google_plus',
																							'snapchat'   => 'snapchat',
																							'flickr'     => 'flickr',
																							'pinterest'  => 'pinterest',
																							'googleplus' => 'google_plus',
																							'twitter'    => 'twitter',
																							'facebook'   => 'facebook',
																						);
					$social_fields = array();
					foreach( $wcfm_profile_social_fields as $wcfm_profile_social_key => $wcfm_profile_social_field ) {
						$social_fields[$wcfm_profile_social_key] = $wcfm_profile_form[$wcfm_profile_social_field];
					}
					$vendor_data = get_user_meta( $user_id, 'dokan_profile_settings', true );
					$vendor_data['social'] = $social_fields;
					update_user_meta( $user_id, 'dokan_profile_settings', $vendor_data );
				} elseif( $is_marketplace == 'wcfmmarketplace' )  {
					$wcfm_profile_social_fields = apply_filters( 'wcfm_profile_social_types', array( 
																																													'twitter'    => 'twitter',
																																													'fb'         => 'facebook',
																																													'instagram'  => 'instagram',
																																													'youtube'    => 'youtube',
																																													'linkedin'   => 'linkdin',
																																													'gplus'      => 'google_plus',
																																													'snapchat'   => 'snapchat',
																																													'pinterest'  => 'pinterest',
																																													'googleplus' => 'google_plus',
																																													'twitter'    => 'twitter',
																																													'facebook'   => 'facebook',
																																												) );
					$social_fields = array();
					foreach( $wcfm_profile_social_fields as $wcfm_profile_social_key => $wcfm_profile_social_field ) {
						$social_fields[$wcfm_profile_social_key] = $wcfm_profile_form[$wcfm_profile_social_field];
					}
					$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
					$vendor_data['social'] = $social_fields;
					update_user_meta( $user_id, 'wcfmmp_profile_settings', $vendor_data );
				} else {
					$wcfm_profile_social_fields = array( 
																							'_twitter_profile'      => 'twitter',
																							'_fb_profile'           => 'facebook',
																							'_instagram'            => 'instagram',
																							'_youtube'              => 'youtube',
																							'_linkdin_profile'      => 'linkdin',
																							'_google_plus_profile'  => 'google_plus',
																							'_snapchat'             => 'snapchat',
																							'_pinterest'            => 'pinterest',
																							'googleplus'            => 'google_plus',
																							'twitter'               => 'twitter',
																							'facebook'              => 'facebook',
																							'instagram'             => 'instagram',
																							'pinterest'             => 'pinterest',
																							'linkdin'               => 'linkdin',
																						);
				}
			} else {
				$wcfm_profile_social_fields = array( 
																							'_twitter_profile'      => 'twitter',
																							'_fb_profile'           => 'facebook',
																							'_instagram'            => 'instagram',
																							'_youtube'              => 'youtube',
																							'_linkdin_profile'      => 'linkdin',
																							'_google_plus_profile'  => 'google_plus',
																							'_snapchat'             => 'snapchat',
																							'_pinterest'            => 'pinterest',
																							'googleplus'            => 'google_plus',
																							'twitter'               => 'twitter',
																							'facebook'              => 'facebook',
																							'instagram'             => 'instagram',
																							'pinterest'             => 'pinterest',
																							'linkdin'               => 'linkdin',
																						);
			}
			foreach( $wcfm_profile_social_fields as $wcfm_profile_social_key => $wcfm_profile_social_field ) {
				update_user_meta( $user_id, $wcfm_profile_social_key, $wcfm_profile_form[$wcfm_profile_social_field] );
			}
		}
		
		$has_error = false;
		if( wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_email_verification', true ) && isset( $_POST['user_email'] ) ) {
			$email_verified = false;
			$user_email     = wc_clean($_POST['user_email']);
			$email_verified = get_user_meta( $user_id, '_wcfm_email_verified', true );
			$wcfm_email_verified_for = get_user_meta( $user_id, '_wcfm_email_verified_for', true );
			if( $email_verified && ( $user_email != $wcfm_email_verified_for ) ) $email_verified = false;
			
			if( !$email_verified ) {
				$wcfm_email_verified_input = isset( $wcfm_profile_form['wcfm_email_verified_input'] ) ? $wcfm_profile_form['wcfm_email_verified_input'] : '';
				if( $wcfm_email_verified_input ) {
					$verification_code = get_post_meta( $user_id, '_wcfm_email_verification_code', true );
					if( $verification_code ) {
						if( $verification_code == $wcfm_email_verified_input ) {
							update_user_meta( $user_id, '_wcfm_email_verified', true );
							update_user_meta( $user_id, '_wcfm_email_verified_for', $user_email );
							delete_post_meta( $user_id, '_wcfm_email_verification_code' );
						} else {
							$has_error = true;
							echo '{"status": false, "message": "' . __( 'Email verification code invalid.', 'wc-frontend-manager' ). '"}';
						}
					}
				}
			}
		}
		
		do_action( 'wcfm_profile_update', $user_id, $wcfm_profile_form );
		
		if( !$has_error ) {
			echo '{"status": true, "message": "' . __( 'Profile saved successfully', 'wc-frontend-manager' ) . '"}';
		}
		
		die;
	}
}