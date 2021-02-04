<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Notification Manager
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Notification_Manager {

	public function __construct() {
		global $WCFM;
		
		// Notification Setting
		add_action( 'end_wcfm_settings_form_menu_manager', array( &$this, 'wcfm_notification_settings' ), 14 );
		add_filter( 'wcfm_settings_fields_email_from', array( &$this, 'wcfmmp_email_notification_settings' ), 14 );
		add_action( 'wcfm_settings_update', array( &$this, 'wcfm_notification_settings_update' ), 14 );
		
		// Admin Email Notification Address
		add_filter( 'wcfm_admin_email_notification_receiver', array( &$this, 'wcfmmp_admin_email_notification_receiver' ), 50, 2 );
		
		// Notification Manager - Message
		add_filter( 'wcfm_is_allow_notification_message', array( &$this, 'wcfmmp_is_allow_notification_message' ), 500, 3 );
		
		// Notification Manager - Email
		add_filter( 'wcfm_is_allow_notification_email', array( &$this, 'wcfmmp_is_allow_notification_email' ), 500, 3 );
		
		// Notification Manager - BuddyPress Notification
		add_action( 'after_wcfm_notification', array( &$this, 'wcfmmp_send_notification_buddypress' ), 500, 6 );
		
		// Notification Manager - SMS
		add_action( 'after_wcfm_notification', array( &$this, 'wcfmmp_send_notification_sms' ), 500, 6 );
		
		// SMS Alter Plugin SMS contains HTML Restriction
		add_filter( 'sa_before_send_sms', array( &$this, 'wcfmmp_strip_sa_sms' ), 500 );
		
		// Notification Manager - Mobile App
    add_action( 'after_wcfm_notification', array( &$this, 'wcfmmp_send_notification_onesignal' ), 500, 6 );
		
		// Delivery Boy Notification Manager - SMS
		add_action( 'wcfmd_after_delivery_boy_assigned', array( &$this, 'wcfmmp_delivery_boy_send_notification_sms' ), 500, 6 );
		
		// Notification - Sound
		add_filter( 'wcfm_is_allow_sound', array( &$this, 'wcfmmp_is_allow_sound' ), 500 );
		
	}
	
	function wcfm_notification_settings( $wcfm_options ) {
		global $WCFM, $WCFMmp;
		
		$message_types  = get_wcfm_message_types();
		$wcfmmp_notification_options = get_option( 'wcfmmp_notification_options', array() );
		
		$wcfm_notification_sound = isset( $wcfmmp_notification_options['notification_sound'] ) ? $wcfmmp_notification_options['notification_sound'] : '';
		
		?>
		<!-- collapsible -->
		<div class="page_collapsible" id="wcfm_settings_form_notification_settings_head">
			<label class="wcfmfa fa-bell"></label>
			<?php _e('Notification Manager', 'wc-multivendor-marketplace'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_notification_settings_expander" class="wcfm-content">
			  <h2><?php _e('Notification Manager', 'wc-multivendor-marketplace'); ?></h2>
				<div class="wcfm_clearfix"></div>
				
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																															'notification_sound' => array( 'label' => __( 'Notification Sound', 'wc-multivendor-marketplace' ), 'name' => 'wcfmmp_notification_options[notification_sound]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'wrapper_class' => 'wcfm_notification_checkbox', 'dfvalue' => $wcfm_notification_sound )
																														) );
				?>
				<div class="wcfm_clearfix"></div>
				
				<table class="table notification_setting_table table-bordered">
				  <thead>
				    <tr>
				      <th style="width:25%;"></th>
				      <th colspan="3"><?php _e( 'Admin Notification', 'wc-multivendor-marketplace' ); ?></th>
				      <th colspan="3"><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Vendor', 'wc-frontend-manager' ) ) . ' ' . __( 'Notification', 'wc-multivendor-marketplace' ); ?></th>
				    </tr>
				    <tr>
				      <th style="width:25%;text-align:left;"><?php _e( 'Notification Type', 'wc-multivendor-marketplace' ); ?></th>
				      <th style="width:12%;"><?php _e( 'Email', 'wc-multivendor-marketplace' ); ?></th>
				      <th style="width:12%;"><?php _e( 'Message', 'wc-multivendor-marketplace' ); ?></th>
				      <?php if( WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() || WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() || WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() || function_exists( 'netgsm_sendSMS_oneToMany' ) || apply_filters( 'wcfm_is_allow_custom_otp_verification', false ) ) { ?>
				      	<th style="width:12%;"><?php _e( 'SMS', 'wc-multivendor-marketplace' ); ?></th>
				      <?php } else { ?>
				      	<th style="width:12%;">&nbsp;</th>
				      <?php } ?>
				      <th style="width:12%;"><?php _e( 'Email', 'wc-multivendor-marketplace' ); ?></th>
				      <th style="width:12%;"><?php _e( 'Message', 'wc-multivendor-marketplace' ); ?></th>
				      <?php if( WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() || WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() || WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() || function_exists( 'netgsm_sendSMS_oneToMany' ) || apply_filters( 'wcfm_is_allow_custom_otp_verification', false ) ) { ?>
				      	<th style="width:12%;"><?php _e( 'SMS', 'wc-multivendor-marketplace' ); ?></th>
				      <?php } else { ?>
				      	<th style="width:12%;">&nbsp;</th>
				      <?php } ?>
				    </tr>
				  </thead>
				</table>
				<?php
				foreach( $message_types as $message_type => $message_type_label ) {
					$message_type_value_admin_email = isset( $wcfmmp_notification_options[$message_type]['admin']['email'] ) ? $wcfmmp_notification_options[$message_type]['admin']['email'] : 'no';
					$message_type_value_admin_message = isset( $wcfmmp_notification_options[$message_type]['admin']['message'] ) ? $wcfmmp_notification_options[$message_type]['admin']['message'] : 'no';
					$message_type_value_vendor_email = isset( $wcfmmp_notification_options[$message_type]['vendor']['email'] ) ? $wcfmmp_notification_options[$message_type]['vendor']['email'] : 'no';
					$message_type_value_vendor_message = isset( $wcfmmp_notification_options[$message_type]['vendor']['message'] ) ? $wcfmmp_notification_options[$message_type]['vendor']['message'] : 'no';
					$hints = '';
					$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																															$message_type.'_admin_email' => array( 'label' => $message_type_label, 'name' => 'wcfmmp_notification_options[' . $message_type . '][admin][email]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title notification_setting_label module_options_title', 'wrapper_class' => 'wcfm_notification_checkbox', 'dfvalue' => $message_type_value_admin_email ),
																															$message_type.'_admin_message' => array( 'name' => 'wcfmmp_notification_options[' . $message_type . '][admin][message]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'wrapper_class' => 'wcfm_notification_checkbox', 'dfvalue' => $message_type_value_admin_message )
																															) );
					
					if( WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() || WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() || WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() || function_exists( 'netgsm_sendSMS_oneToMany' ) || apply_filters( 'wcfm_is_allow_custom_otp_verification', false ) ) {
						$message_type_value_admin_sms = isset( $wcfmmp_notification_options[$message_type]['admin']['sms'] ) ? $wcfmmp_notification_options[$message_type]['admin']['sms'] : 'no';
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																															$message_type.'_admin_sms' => array( 'name' => 'wcfmmp_notification_options[' . $message_type . '][admin][sms]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'wrapper_class' => 'wcfm_notification_checkbox', 'dfvalue' => $message_type_value_admin_sms ),
																															) );
					} else {
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																															$message_type.'_admin_sms' => array( 'type' => 'html', 'class' => 'wcfm_notification_setting_dummy_div', 'value' => '' ),
																															) );
					}
					
					$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																															$message_type.'_vendor_email' => array(  'name' => 'wcfmmp_notification_options[' . $message_type . '][vendor][email]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'wrapper_class' => 'wcfm_notification_checkbox', 'dfvalue' => $message_type_value_vendor_email ),
																															$message_type.'_vendor_message' => array( 'name' => 'wcfmmp_notification_options[' . $message_type . '][vendor][message]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'wrapper_class' => 'wcfm_notification_checkbox', 'dfvalue' => $message_type_value_vendor_message ),
																															) );
					
					if( WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() || WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() || WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() || function_exists( 'netgsm_sendSMS_oneToMany' ) || apply_filters( 'wcfm_is_allow_custom_otp_verification', false ) ) {
						$message_type_value_vendor_sms = isset( $wcfmmp_notification_options[$message_type]['vendor']['sms'] ) ? $wcfmmp_notification_options[$message_type]['vendor']['sms'] : 'no';
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																															$message_type.'_vendor_sms' => array( 'name' => 'wcfmmp_notification_options[' . $message_type . '][vendor][sms]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'wrapper_class' => 'wcfm_notification_checkbox', 'dfvalue' => $message_type_value_vendor_sms ),
																															) );
					} else {
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																															$message_type.'_vendor_sms' => array( 'type' => 'html', 'class' => 'wcfm_notification_setting_dummy_div', 'value' => '' ),
																															) );
					}
					echo '<div class="wcfm-clearfix"></div>';
				}
				?>
				
			</div>
		</div>
		<?php
	}
	
	function wcfmmp_email_notification_settings( $email_fields ) {
		global $WCFM, $WCFMmp;
		
		$message_types  = get_wcfm_message_types();
		$wcfmmp_notification_email = get_option( 'wcfmmp_notification_email', array() );
		
		$wcfmmp_email_fields = array();
		foreach( $message_types as $message_type => $message_type_label ) {
			$message_type_notification_email = isset( $wcfmmp_notification_email[$message_type] ) ? $wcfmmp_notification_email[$message_type] : get_option('admin_email');
			$wcfmmp_email_fields[$message_type.'_notification_email'] = array( 'label' => $message_type_label, 'name' => 'wcfmmp_notification_email[' . $message_type . ']', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $message_type_notification_email );
		}
		$email_fields = array_merge( $email_fields, $wcfmmp_email_fields );
		
		return $email_fields;
	}
	
	function wcfm_notification_settings_update( $wcfm_settings_form ) {
		global $WCFM, $WCFMmp, $_POST;
		
		if( isset( $wcfm_settings_form['wcfmmp_notification_options'] ) ) {
			update_option( 'wcfmmp_notification_options', $wcfm_settings_form['wcfmmp_notification_options'] );
		} else {
			update_option( 'wcfmmp_notification_options', array() );
		}
		
		if( isset( $wcfm_settings_form['wcfmmp_notification_email'] ) ) {
			update_option( 'wcfmmp_notification_email', $wcfm_settings_form['wcfmmp_notification_email'] );
		}
	}
	
	public function wcfmmp_admin_email_notification_receiver( $email_receiver, $message_type ) {
		global $WCFM, $WCFMmp;
		$wcfmmp_notification_email = get_option( 'wcfmmp_notification_email', array() );
		$email_receiver = isset( $wcfmmp_notification_email[$message_type] ) ? $wcfmmp_notification_email[$message_type] : $email_receiver;
		return $email_receiver;
	}
	
	function wcfmmp_is_allow_notification_message( $is_allow, $message_type, $message_to = 0 ) {
		global $WCFM, $WCFMmp;
		
		if( $message_to != -1 && $message_to != '-1' ) {
			$message_to = absint($message_to);
			if( $message_to ) $message_to = 'vendor';
			else $message_to = 'admin';
			
			$is_notification_send = isset( $WCFMmp->wcfmmp_notification_options[$message_type][$message_to]['message'] ) ? $WCFMmp->wcfmmp_notification_options[$message_type][$message_to]['message'] : 'no';
			if( $is_notification_send == 'yes' ) $is_allow = false;
		}
		
		return $is_allow;
	}
	
	function wcfmmp_is_allow_notification_email( $is_allow, $message_type, $message_to = 0 ) {
		global $WCFM, $WCFMmp;
		
		if( $message_to != -1 && $message_to != '-1' ) {
			$message_to = absint($message_to);
			if( $message_to ) $message_to = 'vendor';
			else $message_to = 'admin';
			
			$is_notification_send = isset( $WCFMmp->wcfmmp_notification_options[$message_type][$message_to]['email'] ) ? $WCFMmp->wcfmmp_notification_options[$message_type][$message_to]['email'] : 'no';
			if( $is_notification_send == 'yes' ) $is_allow = false;
		}
		
		return $is_allow;
	}
	
	function wcfmmp_send_notification_buddypress( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, $message_type ) {
		global $WCFM, $WCFMmp;
		
		if( !apply_filters( 'wcfm_is_pref_buddypress', true ) || !apply_filters( 'wcfm_is_allow_notification_sync_to_buddypress', true ) || !WCFM_Dependencies::wcfm_biddypress_plugin_active_check() || !function_exists( 'messages_new_message' ) ) return;
		
		if( $message_to == -1 || $message_to == '-1' ) return;
		
		$message_to = absint($message_to);
		if( $message_to ) $message_to_user = 'vendor';
		else $message_to_user = 'admin';
		
		if( $message_to_user != 'vendor' ) return;
		
		if( !apply_filters( 'wcfm_is_allow_notification_email', true, $message_type, $message_to ) ) return;
		
		$message_label = '';
		$message_types  = get_wcfm_message_types();
		if( isset( $message_types[$message_type] ) ) $message_label = $message_types[$message_type];
		else $message_label = str_replace( '_', ' ',  ucfirst( $message_type ) );
		
		// Attempt to send the message.
		$send = messages_new_message( array(
			'recipients' => array( get_userdata($message_to)->user_nicename ),
			'subject'    => $message_label,
			'content'    => $wcfm_messages,
			'error_type' => 'wp_error'
		) );
	}
	
	function wcfmmp_send_notification_sms( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, $message_type ) {
		global $WCFM, $WCFMmp;
		
		if( !WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() && !WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() && !WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() && !function_exists( 'netgsm_sendSMS_oneToMany' ) ) return;
		
		if( $message_to == -1 || $message_to == '-1' ) return;
		
		$message_to = absint($message_to);
		if( $message_to ) $message_to_user = 'vendor';
		else $message_to_user = 'admin';
		
		$is_notification_send = isset( $WCFMmp->wcfmmp_notification_options[$message_type][$message_to_user]['sms'] ) ? $WCFMmp->wcfmmp_notification_options[$message_type][$message_to_user]['sms'] : 'no';
		if( $is_notification_send == 'yes' ) return;
		
		$sms_messages  = get_bloginfo( 'name' ) . ': ' . apply_filters( 'wcfm_' . $message_to_user . '_' . $message_type . '_sms_text', $wcfm_messages, $message_to, $message_type, $message_to_user );
		
		$sms_messages  = strip_tags( $sms_messages );
		$sms_messages  = esc_sql( $sms_messages );
		
		if( WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() ) {
			if( !class_exists( 'SmsAlertcURLOTP' ) ) return;
			
			$sms_data = array( 'number' => '' );
			if( $message_to_user == 'admin' ) {
				$sms_data['number']   = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
			} else {
				$store_user           = wcfmmp_get_store( $message_to );
				$sms_data['number']   = $store_user->get_phone();
			} 
			$sms_data['sms_body'] = $sms_messages;
			
			if( !empty( $sms_data['number'] ) ) {
				wcfm_log( "SMS:: " . $sms_data['number'] . " : " . $sms_messages );
				
				$admin_response       = SmsAlertcURLOTP::sendsms( $sms_data );
				$response             = json_decode($admin_response,true);
				if( $response['status'] == 'success' ) {
					//wcfm_log( "SMS:: " . $sms_data['number'] . ": " . __( 'SMS Sent Successfully.', 'smsalert' ) );
				} else {
					if( is_array( $response['description'] ) && array_key_exists( 'desc', $response['description'] ) ) {
						//wcfm_log( "SMS:: " . $sms_data['number'] . ": " . __($response['description']['desc'], 'smsalert' ) );
					}
					else {
						//wcfm_log( "SMS:: " . $sms_data['number'] . ": " . __($response['description'], 'smsalert' ) );
					}
				}
			}
		}
		
		if( WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() ) {
			if( !class_exists('WCFMmp_Twilio_SMS_Notification') ) {
				include_once( $WCFMmp->plugin_path . 'includes/sms-gateways/class-wcfmmp-twilio-sms-notification.php' );
			}
			
			$twillio_notification = new WCFMmp_Twilio_SMS_Notification( 9999 );
			
			if ( class_exists( 'WC_Twilio_SMS_URL_Shortener' ) && WC_Twilio_SMS_URL_Shortener::using_shortened_urls() ) {
				$sms_messages  = WC_Twilio_SMS_URL_Shortener::shorten_urls( $sms_messages );
			}
			
			if( $message_to_user == 'admin' ) {
				$country_obj   = new WC_Countries();
				$recipients = explode( ',', trim( get_option( 'wc_twilio_sms_admin_sms_recipients' ) ) );
				if ( ! empty( $recipients ) ) {
					foreach ( $recipients as $recipient ) {
						wcfm_log( "SMS:: " . $recipient . " : " . $sms_messages );
						$twillio_notification->send_sms( $recipient, $sms_messages, false, $country_obj->get_base_country() );
					}
				}
			} else {
				$store_user  = wcfmmp_get_store( $message_to );
				$recipient   = $store_user->get_phone();
				$address     = $store_user->get_address();
				if( $recipient ) {
					wcfm_log( "SMS:: " . $recipient . " : " . $sms_messages );
					$twillio_notification->send_sms( $recipient, $sms_messages, false, $address['country'] );
				}
			}
		}
		
		if( WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() ) {
			$sms_number = '';
			if( $message_to_user == 'admin' ) {
				$sms_number   = get_option('admin_phone_number');
			} else {
				$store_user   = wcfmmp_get_store( $message_to );
				$sms_number   = $store_user->get_phone();
			} 
			
			if( get_option('msg_api_key') && get_option('sms_sender_id') && $sms_number ) {
				wcfm_log( "SMS:: " . $sms_number . " : " . $sms_messages );
				$adminfile = @wp_remote_get("https://control.msg91.com/api/sendhttp.php?authkey=". get_option('msg_api_key') ."&mobiles=". $sms_number ."&message=".urlencode($sms_messages)."&sender=". get_option('sms_sender_id') ."&route=4&country=0")['body'];
				$output = fgets($adminfile);
				fclose($adminfile);
			}
		}
		
		if( function_exists( 'netgsm_sendSMS_oneToMany' ) ) {
			$sms_number = '';
			if( $message_to_user == 'admin' ) {
				$sms_number   = esc_html( get_option( 'netgsm_cf7_to_admin_no' ) );
			} else {
				$store_user   = wcfmmp_get_store( $message_to );
				$sms_number   = $store_user->get_phone();
			} 
			
			if( $sms_number ) {
				wcfm_log( "SMS:: " . $sms_number . " : " . $sms_messages );
				netgsm_sendSMS_oneToMany( $sms_number, $sms_messages );
			}
		}
	}
	
	function wcfmmp_strip_sa_sms( $fields ) {
		if( isset( $fields['text'] ) ) {
			$fields['text'] = strip_tags( $fields['text'] );
		}
		return $fields;
	}
	
	function wcfmmp_send_notification_onesignal( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, $message_type ) {

    $notification_message  = esc_sql( $wcfm_messages );
		$notification_message  = strip_tags( $wcfm_messages );    
    $message_to = absint($message_to);
		if( $message_to ) { 
      $message_to_user = 'vendor';
      $user_info = get_userdata( $message_to );
      $to_user = $user_info->user_email;
    } else {
      $message_to_user = 'admin';
    }
    
    if( $message_to_user === 'vendor' ) {
      //$to_user = 'test';
        $this->send_push_notification($notification_message, $to_user);
      //wcfm_log(json_encode($response));
    } 
    
  }

  function send_push_notification( $notification_message, $to_user ) {   
		$body = new stdClass();
		$one_signal_tokens = apply_filters( 'wcfm_one_signal_tokens', array(
			'app_id'       => "6fc23bb9-ad88-48ee-afd0-d2af425e466c",
			'rest_api_key' => "YzA3MWY0ZTctZmVkOC00OTE2LWJiMzEtMzM1YzJhNzc1M2Zh"
		) );
		$body->app_id = $one_signal_tokens['app_id'];
		$body->contents = array( 'en' => $notification_message );
		$body->include_external_user_ids = array($to_user);
		$bodyAsJson=json_encode($body);
		$response = wp_remote_post("https://onesignal.com/api/v1/notifications", array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array("Content-type" => "application/json;charset=UTF-8",
				"Authorization" => "Basic " . $one_signal_tokens['rest_api_key'] ),
			'body' => $bodyAsJson,
		  )
		);
	//return $response['body'];
	}
	
	function wcfmmp_delivery_boy_send_notification_sms( $order_id, $order_item_id, $wcfm_tracking_data, $product_id, $wcfm_delivery_boy, $wcfm_messages ) {
		global $WCFM, $WCFMmp;
		
		if( !$wcfm_delivery_boy ) return;
		if( !WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() && !WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() && !WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() ) return;
		
		$wcfm_delivery_boy_phone = get_user_meta( $wcfm_delivery_boy, 'billing_phone', true );
		if( !$wcfm_delivery_boy_phone ) return;
			
		$sms_messages  = get_bloginfo( 'name' ) . ': ' . apply_filters( 'wcfm_delivery_assign_sms_text', $wcfm_messages, $order_id, $order_item_id, $wcfm_tracking_data, $product_id, $wcfm_delivery_boy );
		
		$sms_messages  = esc_sql( $sms_messages );
		$sms_messages  = strip_tags( $sms_messages );
		
		wcfm_log( "Delivery By Assign SMS:: " . $wcfm_delivery_boy_phone . ": " . $sms_messages );
		
		if( WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() ) {
			if( !class_exists( 'SmsAlertcURLOTP' ) ) return;
			
			$sms_data = array( 'number' => '' );
			$sms_data['number']   = $wcfm_delivery_boy_phone;
			$sms_data['sms_body'] = $sms_messages;
			
			if( !empty( $sms_data['number'] ) ) {
				$admin_response       = SmsAlertcURLOTP::sendsms( $sms_data );
				$response             = json_decode($admin_response,true);
				if( $response['status'] == 'success' ) {
					//wcfm_log( "SMS:: " . $sms_data['number'] . ": " . __( 'SMS Sent Successfully.', 'smsalert' ) );
				} else {
					if( is_array( $response['description'] ) && array_key_exists( 'desc', $response['description'] ) ) {
						//wcfm_log( "SMS:: " . $sms_data['number'] . ": " . __($response['description']['desc'], 'smsalert' ) );
					}
					else {
						//wcfm_log( "SMS:: " . $sms_data['number'] . ": " . __($response['description'], 'smsalert' ) );
					}
				}
			}
		}
		
		if( WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() ) {
			if( !class_exists('WCFMmp_Twilio_SMS_Notification') ) {
				include_once( $WCFMmp->plugin_path . 'includes/sms-gateways/class-wcfmmp-twilio-sms-notification.php' );
			}
			
			$twillio_notification = new WCFMmp_Twilio_SMS_Notification( 9999 );
			if ( 'yes' === get_option( 'wc_twilio_sms_shorten_urls' ) ) {
				$sms_messages  = $twillio_notification->shorten_urls( $sms_messages );
			}
			
			$country_obj   = new WC_Countries();
			$twillio_notification->send_sms( $wcfm_delivery_boy_phone, $sms_messages, false, $country_obj->get_base_country() );
		}
		
		if( WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() ) {
			if( get_option('msg_api_key') && get_option('sms_sender_id') ) {
				$adminfile = @wp_remote_get("https://control.msg91.com/api/sendhttp.php?authkey=". get_option('msg_api_key') ."&mobiles=". $wcfm_delivery_boy_phone ."&message=".urlencode($sms_messages)."&sender=". get_option('sms_sender_id') ."&route=4&country=0")['body'];
				$output = fgets($adminfile);
				fclose($adminfile);
			}
		}
	}
	
	function wcfmmp_is_allow_sound( $is_allow ) {
		global $WCFM, $WCFMmp;
		
		$is_notification_sound = isset( $WCFMmp->wcfmmp_notification_options['notification_sound'] ) ? $WCFMmp->wcfmmp_notification_options['notification_sound'] : 'no';
		if( $is_notification_sound == 'yes' ) $is_allow = false;
		
		return $is_allow;
	}
}