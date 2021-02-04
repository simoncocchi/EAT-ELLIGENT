<?php
/**
 * WCFM plugin view
 *
 * WCFMgs Memberships Settings View
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/view
 * @version   1.0.0
 */
 
global $wp, $WCFM, $WCFMvm;

$wcfm_is_allow_membership = apply_filters( 'wcfm_is_allow_membership', true );
if( !$wcfm_is_allow_membership || !apply_filters( 'wcfm_is_allow_manage_groups', true ) ) {
	wcfm_restriction_message_show( "Memberships" );
	return;
}

$wcfm_membership_payment_methods = get_wcfm_membership_payment_methods();

$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );

$membership_type_settings = array();
if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
$email_verification = isset( $membership_type_settings['email_verification'] ) ? 'yes' : 'no';
$sms_verification = isset( $membership_type_settings['sms_verification'] ) ? 'yes' : 'no';
$wcfm_custom_plan_page = isset( $membership_type_settings['wcfm_custom_plan_page'] ) ? $membership_type_settings['wcfm_custom_plan_page'] : '';
$wcfm_custom_thankyou_page = isset( $membership_type_settings['wcfm_custom_thankyou_page'] ) ? $membership_type_settings['wcfm_custom_thankyou_page'] : '';

$first_step = isset( $membership_type_settings['first_step'] ) ? $membership_type_settings['first_step'] : 'plan';
$free_membership = isset( $membership_type_settings['free_membership'] ) ? $membership_type_settings['free_membership'] : '';
$featured_membership = isset( $membership_type_settings['featured_membership'] ) ? $membership_type_settings['featured_membership'] : '';
$subscribe_button_label = isset( $membership_type_settings['subscribe_button_label'] ) ? $membership_type_settings['subscribe_button_label'] : __( "Subscribe Now", 'wc-multivendor-membership' );

$membership_payment_settings = array();
if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
$payment_methods = array( 'paypal' );
if( isset( $membership_payment_settings['payment_methods'] ) ) $payment_methods = $membership_payment_settings['payment_methods'];
$paypal_email = isset( $membership_payment_settings['paypal_email'] ) ? $membership_payment_settings['paypal_email'] : '';
$paypal_api_username       = isset( $membership_payment_settings['paypal_api_username'] ) ? $membership_payment_settings['paypal_api_username'] : '';
$paypal_api_password       = isset( $membership_payment_settings['paypal_api_password'] ) ? $membership_payment_settings['paypal_api_password'] : '';
$paypal_api_signature      = isset( $membership_payment_settings['paypal_api_signature'] ) ? $membership_payment_settings['paypal_api_signature'] : '';
$paypal_sandbox            = isset( $membership_payment_settings['paypal_sandbox'] ) ? 'yes' : 'no';
$stripe_published_key_live = isset( $membership_payment_settings['stripe_published_key_live'] ) ? $membership_payment_settings['stripe_published_key_live'] : '';
$stripe_secret_key_live    = isset( $membership_payment_settings['stripe_secret_key_live'] ) ? $membership_payment_settings['stripe_secret_key_live'] : '';
$stripe_published_key_test = isset( $membership_payment_settings['stripe_published_key_test'] ) ? $membership_payment_settings['stripe_published_key_test'] : '';
$stripe_secret_key_test    = isset( $membership_payment_settings['stripe_secret_key_test'] ) ? $membership_payment_settings['stripe_secret_key_test'] : '';
$bank_details              = isset( $membership_payment_settings['bank_details'] ) ? $membership_payment_settings['bank_details'] : '';
$payment_terms             = isset( $membership_payment_settings['payment_terms'] ) ? $membership_payment_settings['payment_terms'] : '';

// Membership Tax Setting
$membership_tax_settings = array();
if( isset( $wcfm_membership_options['membership_tax_settings'] ) ) $membership_tax_settings = $wcfm_membership_options['membership_tax_settings'];
$tax_enable  = isset( $membership_tax_settings['enable'] ) ? 'yes' : 'no';
$tax_name    = isset( $membership_tax_settings['name'] ) ? $membership_tax_settings['name'] : '';
$tax_percent = isset( $membership_tax_settings['percent'] ) ? $membership_tax_settings['percent'] : '';

$switch_admin_notication_subject = wcfm_get_option( 'wcfm_membership_switch_admin_notication_subject', '[{site_name}] Vendor Membership Subscription Change' );
$switch_admin_notication_content = wcfm_get_option( 'wcfm_membership_switch_admin_notication_content', '' );
if( !$switch_admin_notication_content ) {
	$switch_admin_notication_content = "Dear Admin,
																			<br /><br />
																			<b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) has changed membership plan to <b>{membership_plan}</b>. 
																			<br /><br />
																			{plan_details}
																			<br /><br />
																			Kindly follow the below the link to more details.
																			<br /><br />
																			Dashboard: {vendor_url} 
																			<br /><br />
																			Thank You";
}

$switch_notication_subject = wcfm_get_option( 'wcfm_membership_switch_notication_subject', '[{site_name}] Membership Subscription Successfully Changed' );
$switch_notication_content = wcfm_get_option( 'wcfm_membership_switch_notication_content', '' );
if( !$switch_notication_content ) {
	$switch_notication_content = "Hi {first_name},
																<br /><br />
																You have successfully changed membership plan to <b>{membership_plan}</b>. 
																<br /><br />
																Your account already setup and ready to configure.
																<br /><br />
																{plan_details}
																<br /><br />
																Kindly follow the below the link to visit your dashboard.
																<br /><br />
																Dashboard: {dashboard_url} 
																<br /><br />
																Thank You";
}

$membership_next_payment = array();
if( isset( $wcfm_membership_options['membership_next_payment'] ) ) $membership_next_payment = $wcfm_membership_options['membership_next_payment'];
$first_next_payment = isset( $membership_next_payment['first_next_payment'] ) ? $membership_next_payment['first_next_payment'] : '5';
$second_next_payment = isset( $membership_next_payment['second_next_payment'] ) ? $membership_next_payment['second_next_payment'] : '2';

$next_payment_notication_subject = wcfm_get_option( 'wcfm_membership_next_payment_notication_subject', '{site_name}: Membership Subscription Recurring Next Payment' );
$next_payment_notication_content = wcfm_get_option( 'wcfm_membership_next_payment_notication_content', '' );
if( !$next_payment_notication_content ) {
	$next_payment_notication_content = "Hi {vendor_name},
																			<br /><br />
																			Your membership plan (<strong>{membership_plan}</strong>) subscription next billing date <strong>{next_payment_day}</strong>. 
																			<br /><br />
																			Kindly pay now now to keep your account active.
																			<br /><br />
																			Thank You";
}

$membership_reminder = array();
if( isset( $wcfm_membership_options['membership_reminder'] ) ) $membership_reminder = $wcfm_membership_options['membership_reminder'];
$first_remind = isset( $membership_reminder['first_remind'] ) ? $membership_reminder['first_remind'] : '5';
$second_remind = isset( $membership_reminder['second_remind'] ) ? $membership_reminder['second_remind'] : '2';

$reminder_notication_subject = wcfm_get_option( 'wcfm_membership_reminder_notication_subject', '{site_name}: Membership Subscription Renewal Reminder' );
$reminder_notication_content = wcfm_get_option( 'wcfm_membership_reminder_notication_content', '' );
if( !$reminder_notication_content ) {
	$reminder_notication_content = "Hi {vendor_name},
																<br /><br />
																Your membership plan (<strong>{membership_plan}</strong>) will expire <strong>{reminder_day}</strong>. 
																<br /><br />
																Kindly renew now to keep your account active.
																<br /><br />
																Thank You";
}

$membership_cancel_rules = array();
if( isset( $wcfm_membership_options['membership_cancel_rules'] ) ) $membership_cancel_rules = $wcfm_membership_options['membership_cancel_rules'];
$member_cancel_status = isset( $membership_cancel_rules['member_status'] ) ? $membership_cancel_rules['member_status'] : 'basic';
$product_cancel_status = isset( $membership_cancel_rules['product_status'] ) ? $membership_cancel_rules['product_status'] : 'same';

$cancel_notication_subject = wcfm_get_option( 'wcfm_membership_cancel_notication_subject', '[{site_name}] Membership Subscription Cancelled' );
$cancel_notication_content = wcfm_get_option( 'wcfm_membership_cancel_notication_content', '' );
if( !$cancel_notication_content ) {
	$cancel_notication_content = "Hi {vendor_name},
																<br /><br />
																Your membership plan (<strong>{membership_plan}</strong>) has been cancelled. 
																<br /><br />
																Thank You";
}

$membership_expire_rules = array();
if( isset( $wcfm_membership_options['membership_expire_rules'] ) ) $membership_expire_rules = $wcfm_membership_options['membership_expire_rules'];
$member_expiry_status = isset( $membership_expire_rules['member_status'] ) ? $membership_expire_rules['member_status'] : 'disable';
$product_expiry_status = isset( $membership_expire_rules['product_status'] ) ? $membership_expire_rules['product_status'] : 'archived';

$expire_notication_subject = wcfm_get_option( 'wcfm_membership_expire_notication_subject', '[{site_name}] Membership Subscription Expired' );
$expire_notication_content = wcfm_get_option( 'wcfm_membership_expire_notication_content', '' );
if( !$expire_notication_content ) {
	$expire_notication_content = "Hi {vendor_name},
																<br /><br />
																Your membership plan (<strong>{membership_plan}</strong>) has been expired. 
																<br /><br />
																Thank You";
}

$membership_reject_rules = array();
if( isset( $wcfm_membership_options['membership_reject_rules'] ) ) $membership_reject_rules = $wcfm_membership_options['membership_reject_rules'];
$required_approval = isset( $membership_reject_rules['required_approval'] ) ? $membership_reject_rules['required_approval'] : 'no';
$vendor_reject_rule = isset( $membership_reject_rules['vendor_reject_rule'] ) ? $membership_reject_rules['vendor_reject_rule'] : 'same';
$send_notification = isset( $membership_reject_rules['send_notification'] ) ? $membership_reject_rules['send_notification'] : 'yes';

$reject_notication_subject = wcfm_get_option( 'wcfm_membership_reject_notication_subject', '[{site_name}] Vendor Application Rejected' );
$reject_notication_content = wcfm_get_option( 'wcfm_membership_reject_notication_content', '' );
if( !$reject_notication_content ) {
	$reject_notication_content = "Hi {first_name},
																<br /><br />
																Sorry to inform you that, your vendor application has been rejected. 
																<br /><br />
																<strong><i>{rejection_reason}</i></strong>
																<br /><br />
																Thank You";
}

$wcfmvm_color_options = array();
if( isset( $wcfm_membership_options['membership_color_settings'] ) ) $wcfmvm_color_options = $wcfm_membership_options['membership_color_settings'];

$membership_visibility_priority = array();
if( isset( $wcfm_membership_options['membership_visibility_priority'] ) ) $membership_visibility_priority = $wcfm_membership_options['membership_visibility_priority'];

$free_thankyou_content = '';
$free_thankyou_content = wcfm_get_option( 'wcfm_membership_free_thankyou_content', '' );
if( !$free_thankyou_content ) {
	$free_thankyou_content = "<strong>Welcome,</strong>
														<br /><br />
														You have successfully subscribed to our membership plan. 
														<br /><br />
														Your account already setup and ready to configure.
														<br /><br />
														Kindly follow the below the link to visit your dashboard.
														<br /><br />
														Thank You";
}

$subscription_thankyou_content = '';
$subscription_thankyou_content = wcfm_get_option( 'wcfm_membership_subscription_thankyou_content', '' );
if( !$subscription_thankyou_content ) {
	$subscription_thankyou_content = "<strong>Welcome,</strong>
																		<br /><br />
																		You have successfully submitted your Vendor Account request. 
																		<br /><br />
																		Your Vendor application is still under review.
																		<br /><br />
																		You will receive details about our decision in your email very soon!
																		<br /><br />
																		Thank You";
}

$non_membership_welcome_email_subject = wcfm_get_option( 'wcfm_non_membership_welcome_email_subject', '[{site_name}] Successfully Registered' );
$non_membership_welcome_email_content = wcfm_get_option( 'wcfm_non_membership_welcome_email_content', '' );
if( !$non_membership_welcome_email_content ) {
	$non_membership_welcome_email_content = "Dear {first_name},
																<br /><br />
																You have successfully registered as a vendor for <b>{site_name}</b>.
																<br /><br />
																Your account has been setup and it is ready to be configured.
																<br /><br />
																Kindly follow the link below to visit your dashboard and start selling.
																<br /><br />
																Dashboard: {dashboard_url} 
																<br /><br />
																Thank You";
}

$subscription_welcome_email_subject = wcfm_get_option( 'wcfm_membership_subscription_welcome_email_subject', '[{site_name}] Successfully Subscribed' );
$subscription_welcome_email_content = wcfm_get_option( 'wcfm_membership_subscription_welcome_email_content', '' );
if( !$subscription_welcome_email_content ) {
	$subscription_welcome_email_content = "Dear {first_name},
																<br /><br />
																You have successfully registered as a vendor for <b>{site_name}</b>.
																<br /><br />
																Your account has been setup and it is ready to be configured.
																<br /><br />
																{plan_details}
																<br /><br />
																Kindly follow the link below to visit your dashboard and start selling.
																<br /><br />
																Dashboard: {dashboard_url} 
																<br /><br />
																Thank You";
}

$registration_admin_notication_subject = wcfm_get_option( 'wcfm_registration_admin_notication_subject', __( '[{site_name}] A new vendor registered', 'wc-multivendor-membership' ) );
$registration_admin_notication_content = wcfm_get_option( 'wcfm_registration_admin_notication_content', '' );
if( !$registration_admin_notication_content ) {
	$registration_admin_notication_content = "Dear Admin,
																						<br /><br />
																						A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) successfully registered to our site. 
																						<br /><br />
																						Kindly follow the below the link to more details.
																						<br /><br />
																						Dashboard: {vendor_url} 
																						<br /><br />
																						Thank You";
}

$subscription_admin_notication_subject = wcfm_get_option( 'wcfm_membership_subscription_admin_notication_subject', '[{site_name}] A new vendor registered' );
$subscription_admin_notication_content = wcfm_get_option( 'wcfm_membership_subscription_admin_notication_content', '' );
if( !$subscription_admin_notication_content ) {
	$subscription_admin_notication_content = "Dear Admin,
																						<br /><br />
																						A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) successfully subscribed to membership plan <b>{membership_plan}</b>. 
																						<br /><br />
																						{plan_details}
																						<br /><br />
																						Kindly follow the below the link to more details.
																						<br /><br />
																						Dashboard: {vendor_url} 
																						<br /><br />
																						Thank You";
}

$onapproval_admin_notication_subject = wcfm_get_option( 'wcfm_membership_onapproval_admin_notication_subject', '[{site_name}] A vendor application waiting for approval' );
$onapproval_admin_notication_content = wcfm_get_option( 'wcfm_membership_onapproval_admin_notication_content', '' );
if( !$onapproval_admin_notication_content ) {
	if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
		$onapproval_admin_notication_content = "Dear Admin,
																						<br /><br />
																						A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) has just applied to our membership plan <b>{membership_plan}</b>. 
																						<br /><br />
																						Kindly follow the below the link to approve/reject the application.
																						<br /><br />
																						Application: {notification_url} 
																						<br /><br />
																						Thank You";
	} else {
		$onapproval_admin_notication_content = "Dear Admin,
																					<br /><br />
																					A new vendor <b>{vendor_name}</b> (Store: <b>{vendor_store}</b>) has just applied.
																					<br /><br />
																					Kindly follow the below the link to approve/reject the application.
																					<br /><br />
																					Application: {notification_url} 
																					<br /><br />
																					Thank You";
	}
}

$membership_feature_lists = array();
if( isset( $wcfm_membership_options['membership_features'] ) ) $membership_feature_lists = $wcfm_membership_options['membership_features'];

$wcfm_memberships_list = get_wcfm_memberships();
$wcfm_memberships_array = array( '' => __( '- Choose Membership -', 'wc-multivendor-membership' ) );
if( !empty( $wcfm_memberships_list ) ) {
	foreach( $wcfm_memberships_list as $wcfm_membership_list ) {
		$wcfm_memberships_array[$wcfm_membership_list->ID] = $wcfm_membership_list->post_title;
	}
}

$wcfmvm_registration_static_fields = wcfm_get_option( 'wcfmvm_registration_static_fields', array() );
$enable_address = isset( $wcfmvm_registration_static_fields['address'] ) ? 'yes' : '';

$field_types = apply_filters( 'wcfm_registration_custom_filed_types', array( 'text' => 'Text', 'number' => 'Number', 'textarea' => 'textarea', 'datepicker' => 'Date Picker', 'timepicker' => 'Time Picker', 'checkbox' => 'Check Box', 'select' => 'Select / Drop Down', 'mselect' => 'Multi-Select Drop Down', 'upload' => 'File/Image', 'html' => 'HTML' ) );
$wcfmvm_registration_custom_fields = wcfm_get_option( 'wcfmvm_registration_custom_fields', array() );

do_action( 'before_wcfm_membership_settings_manage' );

?>

<div class="collapse wcfm-collapse">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-cogs"></span>
		<?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
			<span class="wcfm-page-heading-text"><?php _e( 'Membership Settings', 'wc-multivendor-membership' ); ?></span>
		<?php } else { ?>
				<span class="wcfm-page-heading-text"><?php _e( 'Registration Setting', 'wc-multivendor-membership' ); ?></span>
			<?php } ?>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
	    <?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
				<h2><?php _e('Membership General Options', 'wc-multivendor-membership' ); ?></h2>
				
				<?php
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_memberships_url().'" data-tip="' . __('Memberships', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Memberships', 'wc-multivendor-membership') . '</span></a>';
				if( $has_new = apply_filters( 'wcfm_add_new_membership_sub_menu', true ) ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_memberships_manage_url().'" data-tip="' . __('Add New Membership', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-user-plus"></span></a>';
				}
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" target="_blank" href="'.get_wcfm_membership_url().'" data-tip="' . __('Membership Plan Table', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-eye"></span></a>';
				echo '<a class="add_new_wcfm_ele_dashboard wcfm_tutorials text_tip" target="_blank" href="https://www.youtube.com/embed/0l9RAgUpV2w" data-tip="' . __('Tutorial', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-video"></span></a>';
				?>
			<?php } else { ?>
				<h2><?php _e('Registration Advanced Setting', 'wc-multivendor-membership' ); ?></h2>
			<?php } ?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'begin_wcfm_membership_settings' ); ?>
	  
		<form id="wcfm_membership_settings_form" class="wcfm">
		
			<?php do_action( 'begin_wcfm_membership_settings_form' ); ?>
			
			<div class="wcfm-tabWrap">
			
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_settings_form_visibility_head">
					<label class="wcfmfa fa-globe"></label>
					<?php _e('General', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_settings_form_visibility_expander" class="wcfm-content">
					  <?php
						 $pages_array = array();
						 if( $wcfm_custom_plan_page ) {
						 	 $pages_array[$wcfm_custom_plan_page] = get_post( $wcfm_custom_plan_page )->post_title; 
						 }
						 if( $wcfm_custom_thankyou_page ) {
						 	 $pages_array[$wcfm_custom_thankyou_page] = get_post( $wcfm_custom_thankyou_page )->post_title; 
						 }
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_approval_fields', array(  
																																				"required_approval" => array( 'label' => __( 'Required Approval', 'wc-multivendor-membership' ), 'type' => 'checkbox', 'name' => 'membership_reject_rules[required_approval]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $required_approval, 'hints' => __( 'Whether user required Admin Approval to become vendor or not!', 'wc-multivendor-membership' ) ),
																																				) ) );
							
							if( function_exists( 'wcfm_is_store_page' ) ) {
								if( WCFMmp_Dependencies::wcfm_sms_alert_plugin_active_check() || WCFMmp_Dependencies::wcfm_twilio_plugin_active_check() || WCFMmp_Dependencies::wcfm_msg91_plugin_active_check() || function_exists( 'netgsm_sendSMS_oneToMany' ) || apply_filters( 'wcfm_is_allow_custom_otp_verification', false ) ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( array(  
																																			"sms_verification" => array( 'label' => __( 'OPT Verification on Registration', 'wc-multivendor-membership' ), 'name' => 'membership_type_settings[sms_verification]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $sms_verification )
																																			) );
								}
							}
					  
					  	$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					  																											'email_verification' => array( 'label' => __( 'Email Verification on Registration', 'wc-multivendor-membership' ), 'name' => 'membership_type_settings[email_verification]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $email_verification )
					  																											) );
					  	
					  	if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
								$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																																		'wcfm_custom_plan_page' => array( 'label' => __('Custom Plan Page', 'wc-multivendor-membership'), 'type' => 'select', 'name' => 'membership_type_settings[wcfm_custom_plan_page]', 'options' => $pages_array, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_custom_plan_page, 'attributes' => array( 'style' => 'width:60%' ), 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'You have created your own membership plan page then set that here. It is important when vendors will change plan.', 'wc-multivendor-membership') ),
																																		'wcfm_custom_thankyou_page' => array( 'label' => __('Custom Thank You Page', 'wc-multivendor-membership'), 'type' => 'select', 'name' => 'membership_type_settings[wcfm_custom_thankyou_page]', 'options' => $pages_array, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_custom_thankyou_page, 'attributes' => array( 'style' => 'width:60%' ), 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'You have created your own thank you page then set that here.', 'wc-multivendor-membership') ),
																																		"memberships_setting_break_1" => array( 'type' => 'html', 'value' => '<div style="height: 15px;"></div>' ),
																																		
																																		'first_step' => array( 'label' => __( 'Subscription First Step', 'wc-multivendor-membership' ), 'name' => 'membership_type_settings[first_step]', 'type' => 'select', 'options' => array( 'plan' => __( 'Choose Plan', 'wc-multivendor-membership' ), 'registration' => __( 'Registration', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'hints' => __( 'This will ensure which will be first step of membership subscription - Plan selection or Registration.', 'wc-multivendor-membership' ), 'value' => $first_step ),
																																		'free_membership' => array( 'label' => __( 'Basic Membership', 'wc-multivendor-membership' ), 'name' => 'membership_type_settings[free_membership]', 'type' => 'select', 'options' => $wcfm_memberships_array, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'hints' => __( 'This membership will auto-assign to vendors when subscription expired or recurring subscription cancelled.', 'wc-multivendor-membership' ), 'value' => $free_membership ),
																																		'featured_membership' => array( 'label' => __( 'Featured Membership', 'wc-multivendor-membership' ), 'name' => 'membership_type_settings[featured_membership]', 'type' => 'select', 'options' => $wcfm_memberships_array, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $featured_membership ),
																																		'subscribe_button_label' => array( 'label' => __( 'Subscribe Button Label', 'wc-multivendor-membership' ), 'name' => 'membership_type_settings[subscribe_button_label]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'custom_attributes' => array( 'required' => true ), 'value' => $subscribe_button_label )
																																		) );
							}
					  ?>
					  
					  <?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
							<h2><?php _e( 'Membership plan display visibility priority (Left to Right)', 'wc-multivendor-membership' ); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
								if( !empty( $wcfm_memberships_list ) ) {
									$counter = 1;
									foreach( $wcfm_memberships_list as $wcfm_membership_list ) {
										$priority_membership = 0;
										if( !empty( $membership_visibility_priority ) && isset( $membership_visibility_priority[$counter] ) ) $priority_membership = $membership_visibility_priority[$counter];
										$WCFM->wcfm_fields->wcfm_generate_form_field( array(  
																																				'priority_'.$counter => array( 'label' => __( 'Priority', 'wc-multivendor-membership' ) .' ' . $counter, 'name' => 'membership_visibility_priority[' . $counter . ']', 'type' => 'select', 'options' => $wcfm_memberships_array, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $priority_membership ),
																																	) );
										$counter++;
									}
								} else {
									_e( 'Please define some membership level first.', 'wc-multivendor-membership' );
								}
							?>
						<?php } ?>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
			
				<?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_features_head">
						<label class="wcfmfa fa-sun"></label>
						<?php _e('Features', 'wc-multivendor-membership'); ?>
					</div> 
					<div class="wcfm-container">
						<div id="membership_settings_features_expander" class="wcfm-content">
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_features_fields', array(  
																																																"membership_features" => array( 'label' => __( 'Features List', 'wc-multivendor-membership' ), 'type' => 'multiinput', 'value' => $membership_feature_lists, 'options' => array(
																																																	'feature' => array( 'label' => __( 'Feature', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title' ),
																																																	'help'    => array( 'label' => __( 'Help Message', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'  )
																																						) ) ) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				<?php } ?>
				
				<!-- collapsible - 1.0.5 -->
				<div class="page_collapsible" id="membership_settings_form_custom_field_head">
					<label class="fab fa-superpowers"></label>
					<?php _e('Registration Fields', 'wc-multivendor-membership'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_custom_field_expander" class="wcfm-content">
					  <h2><?php _e( 'Registration Form Fields', 'wc-multivendor-membership' ); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
						$pages_array = array();
						$terms_page = isset( $wcfmvm_registration_static_fields['terms_page'] ) ? $wcfmvm_registration_static_fields['terms_page'] : '';
						if( $terms_page ) {
							 $pages_array[$terms_page] = get_post( $terms_page )->post_title; 
						}
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfmvm_registration_static_fields', array(
							                                                                                                    "first_name"  => array( 'label' => __( 'First Name', 'wc-frontend-manager' ), 'type' => 'checkbox', 'name' => 'wcfmvm_registration_static_fields[first_name]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => isset( $wcfmvm_registration_static_fields['first_name'] ) ? 'yes' : '' ),
							                                                                                                    "last_name"   => array( 'label' => __( 'Last Name', 'wc-frontend-manager' ), 'type' => 'checkbox', 'name' => 'wcfmvm_registration_static_fields[last_name]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => isset( $wcfmvm_registration_static_fields['last_name'] ) ? 'yes' : '' ),
							                                                                                                    "user_name"   => array( 'label' => __( 'User Name', 'wc-frontend-manager' ), 'type' => 'checkbox', 'name' => 'wcfmvm_registration_static_fields[user_name]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => isset( $wcfmvm_registration_static_fields['user_name'] ) ? 'yes' : '' ),
							                                                                                                    "address"     => array( 'label' => __( 'Store Address', 'wc-frontend-manager' ), 'type' => 'checkbox', 'name' => 'wcfmvm_registration_static_fields[address]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => isset( $wcfmvm_registration_static_fields['address'] ) ? 'yes' : '' ),
							                                                                                                    "phone"       => array( 'label' => __( 'Store Phone', 'wc-frontend-manager' ), 'type' => 'checkbox', 'name' => 'wcfmvm_registration_static_fields[phone]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => isset( $wcfmvm_registration_static_fields['phone'] ) ? 'yes' : '' ),
							                                                                                                    "terms"       => array( 'label' => __( 'Terms & Conditions', 'wc-frontend-manager' ), 'type' => 'checkbox', 'name' => 'wcfmvm_registration_static_fields[terms]', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => isset( $wcfmvm_registration_static_fields['terms'] ) ? 'yes' : '' ),
							                                                                                                    "terms_page"  => array( 'label' => __( 'Terms Page', 'wc-frontend-manager' ), 'type' => 'select', 'name' => 'wcfmvm_registration_static_fields[terms_page]', 'attributes' => array( 'style' => 'width:60%' ), 'options' => $pages_array, 'class' => 'wcfm-select wcfm_ele terms_page_ele', 'label_class' => 'wcfm_title terms_page_ele', 'value' => isset( $wcfmvm_registration_static_fields['terms_page'] ) ? $wcfmvm_registration_static_fields['terms_page'] : '' )
							                                                                                                    )
							  																												) );
						
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_membership_registration_custom_fields', array(
																																																"wcfmvm_registration_custom_fields" => array('label' => __( 'Registration Form Custom Fields', 'wc-multivendor-membership'), 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfmvm_registration_custom_fields, 'options' => array(
																																																								"enable"   => array('label' => __('Enable', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes'),
																																																								"type" => array( 'label' => __('Field Type', 'wc-frontend-manager'), 'type' => 'select', 'options' => $field_types, 'class' => 'wcfm-select wcfm_ele field_type_options', 'label_class' => 'wcfm_title'),           
																																																								"label" => array( 'label' => __('Label', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'),
																																																								"options" => array( 'label' => __('Options', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele field_type_select_options field_type_dropdown_options', 'label_class' => 'wcfm_title field_type_select_options field_type_dropdown_options', 'placeholder' => __( 'Insert option values | separated', 'wc-frontend-manager' ) ),
																																																								"content" => array( 'label' => __('Content', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele field_type_html_options', 'label_class' => 'wcfm_title field_type_html_options' ),
																																																								"help_text" => array( 'label' => __('Help Content', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title' ),
																																																								"required" => array( 'label' => __('Required?', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes' ),
																																																	) )
																																													) ) );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_form_payment_head">
						<label class="wcfmfa fa-money-bill-alt"></label>
						<?php _e('Payment', 'wc-frontend-manager'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="membership_settings_form_payment_expander" class="wcfm-content">
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_payment_fields', array(  
																																						'payment_methods' => array( 'label' => __( 'Payment Methods', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[payment_methods]', 'type' => 'checklist', 'class' => 'wcfm-checkbox wcfm_ele payment_options', 'label_class' => 'wcfm_title checkbox_title', 'options' => $wcfm_membership_payment_methods, 'value' => $payment_methods  ),
																																						'paypal_sandbox' => array( 'label' => __( 'Enable Test Mode', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[paypal_sandbox]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $paypal_sandbox ),
																																						'paypal_email' => array( 'label' => __( 'PayPal Email', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[paypal_email]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele payment_fields paypal_payment_field', 'label_class' => 'wcfm_title payment_fields paypal_payment_field', 'value' => $paypal_email ),
																																						//'paypal_api_username' => array( 'label' => __( 'PayPal API Username', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[paypal_api_username]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele payment_fields paypal_payment_field', 'label_class' => 'wcfm_title payment_fields paypal_payment_field', 'value' => $paypal_api_username ),
																																						//'paypal_api_password' => array( 'label' => __( 'PayPal API Password', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[paypal_api_password]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele payment_fields paypal_payment_field', 'label_class' => 'wcfm_title payment_fields paypal_payment_field', 'value' => $paypal_api_password ),
																																						//'paypal_api_signature' => array( 'label' => __( 'PayPal API Signature', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[paypal_api_signature]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele payment_fields paypal_payment_field', 'label_class' => 'wcfm_title payment_fields paypal_payment_field', 'value' => $paypal_api_signature ),
																																						'stripe_published_key_live' => array( 'label' => __( 'Stripe Live Publishable Key', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[stripe_published_key_live]', 'type' => 'password', 'class' => 'wcfm-text wcfm_ele payment_fields stripe_payment_field live_payment_field', 'label_class' => 'wcfm_title payment_fields stripe_payment_field live_payment_field', 'value' => $stripe_published_key_live ),
																																						'stripe_secret_key_live' => array( 'label' => __( 'Stripe Live Secret Key', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[stripe_secret_key_live]', 'type' => 'password', 'class' => 'wcfm-text wcfm_ele payment_fields stripe_payment_field live_payment_field', 'label_class' => 'wcfm_title payment_fields stripe_payment_field live_payment_field', 'value' => $stripe_secret_key_live ),
																																						'stripe_published_key_test' => array( 'label' => __( 'Stripe Test Publishable Key', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[stripe_published_key_test]', 'type' => 'password', 'class' => 'wcfm-text wcfm_ele payment_fields stripe_payment_field test_payment_field', 'label_class' => 'wcfm_title payment_fields stripe_payment_field test_payment_field', 'value' => $stripe_published_key_test ),
																																						'stripe_secret_key_test' => array( 'label' => __( 'Stripe Test Secret Key', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[stripe_secret_key_test]', 'type' => 'password', 'class' => 'wcfm-text wcfm_ele payment_fields stripe_payment_field test_payment_field', 'label_class' => 'wcfm_title payment_fields stripe_payment_field test_payment_field', 'value' => $stripe_secret_key_test ),
																																						'bank_details' => array( 'label' => __( 'Bank Details', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[bank_details]', 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele payment_fields bank_transfer_payment_field', 'label_class' => 'wcfm_title payment_fields bank_transfer_payment_field', 'value' => $bank_details ),
																																						'payment_terms' => array( 'label' => __( 'Payment Terms', 'wc-multivendor-membership' ), 'name' => 'membership_payment_settings[payment_terms]', 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $payment_terms ),
																																						) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_form_payment_head">
						<label class="wcfmfa fa-money-check-alt"></label>
						<?php _e('Tax Setting', 'wc-multivendor-membership'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="membership_settings_form_payment_expander" class="wcfm-content">
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_tax_fields', array(  
																																						'enable_tax' => array( 'label' => __( 'Enable', 'wc-multivendor-membership' ), 'name' => 'membership_tax_settings[enable]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $tax_enable, 'hints' => __( 'Enable this to apply tax on subscription costs.', 'wc-multivendor-membership' ) ),
																																						'tax_name' => array( 'label' => __( 'Tax Label', 'wc-multivendor-membership' ), 'placeholder' => __( 'Tax', 'wc-multivendor-membership' ), 'name' => 'membership_tax_settings[name]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $tax_name ),
																																						'tax_percent' => array( 'label' => __( 'Tax Percent (%)', 'wc-multivendor-membership' ), 'name' => 'membership_tax_settings[percent]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input', 'label_class' => 'wcfm_title', 'value' => $tax_percent ),
																																						) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				<?php } ?>
				
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_settings_form_thankyou_head">
					<label class="wcfmfa fa-thumbs-up"></label>
					<?php _e('Thank You', 'wc-multivendor-membership'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_settings_form_thankyou_expander" class="wcfm-content">
					  <h2><?php _e('Thank You Page Content', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_thankyou_fields', array(  
																																				//'thankyou_title' => array( 'label' => __( 'Thank You page title', 'wc-multivendor-membership' ), 'name' => 'thankyou_content[title]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $thankyou_title ),
																																				'free_thankyou_content' => array( 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $free_thankyou_content, 'desc' => __( 'Please don\'t include Dashboard URL, it will be automatically append with the content.', 'wc-multivendor-membership' ) ),
																																				) ) );
						?>
						<div class="wcfm_clearfix"></div><br />
						<h2><?php _e('On Approval Thank You Page Content', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_thankyou_approval_fields', array(  
																																				'subscription_thankyou_content' => array( 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $subscription_thankyou_content, 'desc' => __( 'This content will be visible when user will require Admin approval to become vendor.', 'wc-multivendor-membership' ) )
																																				) ) );
						?>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_settings_form_thankyou_head">
					<label class="wcfmfa fa-envelope"></label>
					<?php _e('Welcome Email', 'wc-multivendor-membership'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_settings_form_thankyou_expander" class="wcfm-content">
					  <h2><?php _e('Vendor Welcome Email', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
						  $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'non_membership_setting_welcome_email_fields', array(  
																																				'non_membership_welcome_email_subject' => array( 'label' => __( 'Non-Membership Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $non_membership_welcome_email_subject ),
																																				'non_membership_welcome_email_content' => array( 'label' => __( 'Non-Membership Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $non_membership_welcome_email_content )
																																				) ) );
							
							
							if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_welcome_email_fields', array(  
																																					'subscription_welcome_email_subject' => array( 'label' => __( 'Membership Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $subscription_welcome_email_subject ),
																																					'subscription_welcome_email_content' => array( 'label' => __( 'Membership Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $subscription_welcome_email_content )
																																					) ) );
							}
						?>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_settings_form_admin_notication_head">
					<label class="wcfmfa fa-bell"></label>
					<?php _e('Admin Notification', 'wc-multivendor-membership'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_settings_form_admin_notication_expander" class="wcfm-content">
					  <h2><?php _e('New Registration Notification', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_admin_registration_notication_fields', array(  
																																				'registration_admin_notication_subject' => array( 'label' => __( 'Non-Membership Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $registration_admin_notication_subject ),
																																				'registration_admin_notication_content' => array( 'label' => __( 'Non-Membership Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $registration_admin_notication_content ),
																																				) ) );
						?>
						
						<?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
							<br /><div class="wcfm_clearfix"></div><br />
							<h2><?php _e('New Subscription Notification', 'wc-multivendor-membership'); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_admin_subscription_notication_fields', array(  
																																					'subscription_admin_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $subscription_admin_notication_subject ),
																																					'subscription_admin_notication_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $subscription_admin_notication_content ),
																																					) ) );
							?>
						<?php } ?>	
						
						<br /><div class="wcfm_clearfix"></div><br />
						<h2><?php _e('New Subscription Approval Notification', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_admin_approval_notication_fields', array(  
																																				'onapproval_admin_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-top: 15px;' ), 'value' => $onapproval_admin_notication_subject ),
																																				'onapproval_admin_notication_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $onapproval_admin_notication_content )
																																				) ) );
						?>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_form_switch_head">
						<label class="wcfmfa fa-retweet"></label>
						<?php _e('Membership Change', 'wc-multivendor-membership'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="membership_settings_form_switch_expander" class="wcfm-content">
							<h2><?php _e('Admin Notification', 'wc-multivendor-membership'); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_admin_switch_fields', array(  
																																						'switch_admin_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-bottom: 15px;' ), 'value' => $switch_admin_notication_subject ),
																																						'switch_admin_notication_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $switch_admin_notication_content )
																																						) ) );
							?>
							<br /><div class="wcfm_clearfix"></div><br />
							<h2><?php _e('Vendor Notification', 'wc-multivendor-membership'); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_switch_fields', array(  
																																						'switch_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-bottom: 15px;' ), 'value' => $switch_notication_subject ),
																																						'switch_notication_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $switch_notication_content )
																																						) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_form_next_payment_head">
						<label class="wcfmfa fa-clock"></label>
						<?php _e('Next Payment', 'wc-multivendor-membership'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="membership_settings_form_next_payment_expander" class="wcfm-content">
							<h2><?php _e('Recurring Next Payment Notification', 'wc-multivendor-membership'); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_next_payment_fields', array(  
																																						'membership_next_payment_1' => array( 'label' => __('First Reminder', 'wc-multivendor-membership'), 'name' => 'membership_next_payment[first_next_payment]', 'type' => 'select', 'options' => array( 'never' => __( 'Never', 'wc-multivendor-membership' ), '5' => __( 'Before 5 Days', 'wc-multivendor-membership'), '7' => __( 'Before 7 Days', 'wc-multivendor-membership' ), '10' => __( 'Before 10 Days', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'First next payment notification for recurring subscription.', 'wc-multivendor-membership' ), 'value' => $first_next_payment ),
																																						'membership_next_payment_2' => array( 'label' => __('Second Reminder', 'wc-multivendor-membership'), 'name' => 'membership_next_payment[second_next_payment]', 'type' => 'select', 'options' => array( 'never' => __( 'Never', 'wc-multivendor-membership' ), '3' => __( 'Before 3 Days', 'wc-multivendor-membership'), '2' => __( 'Before 2 Days', 'wc-multivendor-membership' ), '1' => __( 'Before 1 Day', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'Second next payment notification for recurring subscription.', 'wc-multivendor-membership' ), 'value' => $second_next_payment ),
																																						'next_payment_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-bottom: 15px;' ), 'value' => $next_payment_notication_subject ),
																																						'next_payment_notication_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $next_payment_notication_content )
																																						) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_form_reminder_head">
						<label class="wcfmfa fa-calendar"></label>
						<?php _e('Renewal Notification', 'wc-multivendor-membership'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="membership_settings_form_reminder_expander" class="wcfm-content">
							<h2><?php _e('Subscription Renewal Notification', 'wc-multivendor-membership'); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_reminder_fields', array(  
																																						'membership_reminder_1' => array( 'label' => __('First Reminder', 'wc-multivendor-membership'), 'name' => 'membership_reminder[first_remind]', 'type' => 'select', 'options' => array( 'never' => __( 'Never', 'wc-multivendor-membership' ), '5' => __( 'Before 5 Days', 'wc-multivendor-membership'), '7' => __( 'Before 7 Days', 'wc-multivendor-membership' ), '10' => __( 'Before 10 Days', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'First renewal notification for recurring subscription.', 'wc-multivendor-membership' ), 'value' => $first_remind ),
																																						'membership_reminder_2' => array( 'label' => __('Second Reminder', 'wc-multivendor-membership'), 'name' => 'membership_reminder[second_remind]', 'type' => 'select', 'options' => array( 'never' => __( 'Never', 'wc-multivendor-membership' ), '3' => __( 'Before 3 Days', 'wc-multivendor-membership'), '2' => __( 'Before 2 Days', 'wc-multivendor-membership' ), '1' => __( 'Before 1 Day', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'Second renewal notification for recurring subscription.', 'wc-multivendor-membership' ), 'value' => $second_remind ),
																																						'reminder_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-bottom: 15px;' ), 'value' => $reminder_notication_subject ),
																																						'reminder_notication_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $reminder_notication_content )
																																						) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_form_cancel_head">
						<label class="wcfmfa fa-user-times"></label>
						<?php _e('Cancel Rules', 'wc-multivendor-membership'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="membership_settings_form_cancel_expander" class="wcfm-content">
							<h2><?php _e('Cancel Rules Setup', 'wc-multivendor-membership'); ?></h2>
							<?php wcfm_video_tutorial( 'https://www.youtube.com/embed/pXIxqT4lU00' ); ?>
							<div class="wcfm_clearfix"></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_cancel_fields', array(  
																																						'membership_cancel_rules' => array( 'label' => __('If Membership Cancelled?', 'wc-multivendor-membership'), 'name' => 'membership_cancel_rules[member_status]', 'type' => 'select', 'options' => array( 'basic' => __( 'Assign Basic Membership', 'wc-multivendor-membership') , 'disable' => __( 'Disable Vendor User', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'This rule will be applicable if a vendor cancel his membership.', 'wc-multivendor-membership' ), 'value' => $member_cancel_status ),
																																						'membership_cancel_products' => array( 'label' => __('Product Status?', 'wc-multivendor-membership'), 'name' => 'membership_cancel_rules[product_status]', 'type' => 'select', 'options' => array( 'same' => __( 'Keep as same', 'wc-multivendor-membership'), 'archived' => __( 'Archived', 'wc-frontend-manager' ), 'draft' => __( 'Save as Draft', 'wc-multivendor-membership' ), 'trash' => __( 'Delete from site', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $product_cancel_status ),
																																						'cancel_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-bottom: 15px;' ), 'value' => $cancel_notication_subject ),
																																						'cancel_notication_content' => array( 'label' => __( 'Notification content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $cancel_notication_content )
																																						) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				
					<!-- collapsible -->
					<div class="page_collapsible" id="membership_settings_form_expire_head">
						<label class="wcfmfa fa-times-circle"></label>
						<?php _e('Expiry Rules', 'wc-multivendor-membership'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="membership_settings_form_expire_expander" class="wcfm-content">
							<h2><?php _e('Expiry Rules Setup', 'wc-multivendor-membership'); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_expire_fields', array(  
																																						'membership_expire_rules' => array( 'label' => __('If Membership Expireled?', 'wc-multivendor-membership'), 'name' => 'membership_expire_rules[member_status]', 'type' => 'select', 'options' => array( 'basic' => __( 'Assign Basic Membership', 'wc-multivendor-membership') , 'disable' => __( 'Disable Vendor User', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'This rule will be applicable if a vendor expire his membership.', 'wc-multivendor-membership' ), 'value' => $member_expiry_status ),
																																						'membership_expire_products' => array( 'label' => __('Product Status?', 'wc-multivendor-membership'), 'name' => 'membership_expire_rules[product_status]', 'type' => 'select', 'options' => array( 'same' => __( 'Keep as same', 'wc-multivendor-membership'), 'archived' => __( 'Archived', 'wc-frontend-manager' ), 'draft' => __( 'Save as Draft', 'wc-multivendor-membership' ), 'trash' => __( 'Delete from site', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $product_expiry_status ),
																																						'expire_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-bottom: 15px;' ), 'value' => $expire_notication_subject ),
																																						'expire_notication_content' => array( 'label' => __( 'Notification content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $expire_notication_content )
																																						) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
					
			  <?php } ?>
				
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_settings_form_rejection_head">
					<label class="wcfmfa fa-times"></label>
					<?php _e('Rejection Rules', 'wc-multivendor-membership'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_settings_form_cancel_expander" class="wcfm-content">
						<h2><?php _e('Application Rejection Notification', 'wc-multivendor-membership'); ?></h2>
						<div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_reject_fields', array(  
																																					'vendor_reject_rule' => array( 'label' => __( 'If Application Rejected?', 'wc-multivendor-membership'), 'name' => 'membership_reject_rules[vendor_reject_rule]', 'type' => 'select', 'options' => array( 'same' => __( 'Keep as normal user', 'wc-multivendor-membership') , 'delete' => __( 'Delete user from system', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'This rule will be applicable if a vendor application reject by Admin.', 'wc-multivendor-membership' ), 'value' => $vendor_reject_rule ),
																																					'send_notification' => array( 'label' => __('Is user notified on rejection?', 'wc-multivendor-membership'), 'name' => 'membership_reject_rules[send_notification]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $send_notification ),
																																					'reject_notication_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'attributes' => array( 'style' => 'margin-bottom: 15px;' ), 'value' => $reject_notication_subject ),
																																					'reject_notication_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $reject_notication_content )
																																					) ) );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
			
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_settings_form_style_head">
					<label class="wcfmfa fa-image"></label>
					<?php _e('Style', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_settings_form_style_expander" class="wcfm-content">
						<h2><?php _e( 'Membership plan display page styling', 'wc-multivendor-membership' ); ?></h2>
						<?php wcfm_video_tutorial( 'https://www.youtube.com/embed/3JuFD35BiWI' ); ?>
						<div class="wcfm_clearfix"></div>
						<?php
							$color_options = $WCFMvm->wcfmvm_membership_color_setting_options();
							$color_options_array = array();
			
							foreach( $color_options as $color_option_key => $color_option ) {
								$color_options_array[$color_option['name']] = array( 'label' => $color_option['label'] , 'name' => 'membership_color_settings['.$color_option['name'].']', 'type' => 'colorpicker', 'class' => 'wcfm-text wcfm_ele colorpicker', 'label_class' => 'wcfm_title wcfm_ele', 'value' => ( isset($wcfmvm_color_options[$color_option['name']]) ) ? $wcfmvm_color_options[$color_option['name']] : $color_option['default'] );
							}
							$WCFM->wcfm_fields->wcfm_generate_form_field( $color_options_array );
						?>
						<div class="wcfm_clearfix"></div>
						<input type="submit" name="reset-color-settings" value="<?php _e( 'Reset to Default', 'wc-frontend-manager' ); ?>" id="wcfmvm_color_setting_reset_button" class="wcfm_submit_button" />
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
					
				<?php do_action( 'end_wcfm_membership_settings_form' ); ?>
			</div>
			
			<div id="wcfm_membership_setting_manager_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="membership_setting-manager-data" value="<?php _e( 'Submit', 'wc-frontend-manager' ); ?>" id="wcfm_membership_setting_submit_button" class="wcfm_submit_button" />
			</div>
			<?php
			do_action( 'after_wcfm_membership_settings' );
			?>
		</form>
	</div>
</div>