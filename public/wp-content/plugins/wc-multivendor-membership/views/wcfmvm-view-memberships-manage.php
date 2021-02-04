<?php
/**
 * WCFM plugin view
 *
 * WCFMgs Memberships Manage View
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

$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );

$membership_type_settings = array();
if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
$default_subscribe_button = isset( $membership_type_settings['subscribe_button_label'] ) ? $membership_type_settings['subscribe_button_label'] : __( "Subscribe Now", 'wc-multivendor-membership' );

$membership_id = 0;
$is_wcfm_membership_disable = 'no';
$is_wcfm_membership_plan_disable = 'no';
$title = '';
$subscribe_button_label = $default_subscribe_button;
$description = '';
$features = array();
$is_restricted = 'no';
$is_free = 'no';
$subscription_type = 'one_time';
$subscription_pay_mode = 'by_wcfm';
$subscription_product = '';
$one_time_amt = '';
$stripe_plan_id = '';
$trial_amt = '';
$trial_period = '';
$trial_period_type = '';
$billing_amt = '';
$billing_period = '';
$billing_period_type = '';
$billing_period_count = '';
$re_attempt = 'no';

$free_expiry_period = '';
$free_expiry_period_type = '';

$commission_type = 'percent';
$commission_value = '10';

$membership_reject_rules = array();
if( isset( $wcfm_membership_options['membership_reject_rules'] ) ) $membership_reject_rules = $wcfm_membership_options['membership_reject_rules'];
$required_approval = isset( $membership_reject_rules['required_approval'] ) ? $membership_reject_rules['required_approval'] : 'no';
$vendor_reject_rule = isset( $membership_reject_rules['vendor_reject_rule'] ) ? $membership_reject_rules['vendor_reject_rule'] : 'same';


$global_free_thankyou_content = wcfm_get_option( 'wcfm_membership_free_thankyou_content', '' );
if( !$global_free_thankyou_content ) {
	$global_free_thankyou_content = "<strong>Welcome,</strong>
														<br /><br />
														You have successfully subscribed to our membership plan. 
														<br /><br />
														Your account already setup and ready to configure.
														<br /><br />
														Kindly follow the below the link to visit your dashboard.
														<br /><br />
														Thank You";
}
$free_thankyou_content = $global_free_thankyou_content;

$global_subscription_thankyou_content = wcfm_get_option( 'wcfm_membership_subscription_thankyou_content', '' );
if( !$global_subscription_thankyou_content ) {
	$global_subscription_thankyou_content = "<strong>Welcome,</strong>
																		<br /><br />
																		You have successfully submitted your Vendor Account request. 
																		<br /><br />
																		Your Vendor application is still under review.
																		<br /><br />
																		You will receive details about our decision in your email very soon!
																		<br /><br />
																		Thank You";
}
$subscription_thankyou_content = $global_subscription_thankyou_content;

$global_subscription_welcome_email_subject = wcfm_get_option( 'wcfm_membership_subscription_welcome_email_subject', '[{site_name}] Successfully Subscribed' );
$global_subscription_welcome_email_content = wcfm_get_option( 'wcfm_membership_subscription_welcome_email_content', '' );
if( !$global_subscription_welcome_email_content ) {
	$global_subscription_welcome_email_content = "Dear {first_name},
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
$subscription_welcome_email_subject = $global_subscription_welcome_email_subject;
$subscription_welcome_email_content = $global_subscription_welcome_email_content;

$associated_group = '';

if( isset( $wp->query_vars['wcfm-memberships-manage'] ) && !empty( $wp->query_vars['wcfm-memberships-manage'] ) ) {
	$membership_post = get_post( $wp->query_vars['wcfm-memberships-manage'] );
	
	// Fetching Membership Data
	if($membership_post && !empty($membership_post)) {
		
		if( $membership_post->post_type != 'wcfm_memberships' ) {
			wcfm_restriction_message_show( "Invalid Membership" );
			return;
		}
		
		
		$membership_id = $wp->query_vars['wcfm-memberships-manage'];
		
		$is_wcfm_membership_disable = get_post_meta( $membership_id, 'is_wcfm_membership_disable', true ) ? 'yes' : 'no';
		$is_wcfm_membership_plan_disable = get_post_meta( $membership_id, 'is_wcfm_membership_plan_disable', true ) ? 'yes' : 'no';
		
		$title = $membership_post->post_title;
		$description = $membership_post->post_excerpt;
		
		$subscribe_button_label = get_post_meta( $membership_id, 'subscribe_button_label', true );
		if( !$subscribe_button_label ) $subscribe_button_label = $default_subscribe_button;
		
		$features = (array) get_post_meta( $membership_id, 'features', true );
		
		$subscription = (array) get_post_meta( $membership_id, 'subscription', true );
		$is_restricted = isset( $subscription['is_restricted'] ) ? 'yes' : 'no';
		$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
		$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
		$subscription_pay_mode = isset( $subscription['subscription_pay_mode'] ) ? $subscription['subscription_pay_mode'] : 'by_wcfm';
		$subscription_product = isset( $subscription['subscription_product'] ) ? $subscription['subscription_product'] : '';
		$one_time_amt = isset( $subscription['one_time_amt'] ) ? $subscription['one_time_amt'] : '';
		$stripe_plan_id = isset( $subscription['stripe_plan_id'] ) ? $subscription['stripe_plan_id'] : '';
		$trial_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '';
		$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
		$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
		$billing_amt = isset( $subscription['billing_amt'] ) ? $subscription['billing_amt'] : '';
		$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
		$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
		$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : '1';
		$re_attempt = isset( $subscription['re_attempt'] ) ? 'yes' : 'no';
		
		$free_expiry_period = isset( $subscription['free_expiry_period'] ) ? $subscription['free_expiry_period'] : '';
		$free_expiry_period_type = isset( $subscription['free_expiry_period_type'] ) ? $subscription['free_expiry_period_type'] : 'M';
		
		$commission = (array) get_post_meta( $membership_id, 'commission', true );
		$commission_type = isset( $commission['type'] ) ? $commission['type'] : 'percent';
		$commission_value = isset( $commission['value'] ) ? $commission['value'] : '10';
		
		$required_approval = get_post_meta( $membership_id, 'required_approval', true ) ? get_post_meta( $membership_id, 'required_approval', true ) : 'no';
		$vendor_reject_rule = get_post_meta( $membership_id, 'vendor_reject_rule', true ) ? get_post_meta( $membership_id, 'vendor_reject_rule', true ) : 'same';
		
		$associated_group = get_post_meta( $membership_id, 'associated_group', true );
		
		if( ( $subscription_pay_mode == 'by_wc' ) && $subscription_product ) {
			$subscription_pro = wc_get_product( $subscription_product );
			if( !$subscription_pro || is_wp_error( $subscription_pro ) || !is_object( $subscription_pro ) )  {
				$subscription_pay_mode = 'by_wcfm';
				$subscription_product = '';
				
			}
		}
		
		$free_thankyou_content = wcfm_get_post_meta( $membership_id, 'free_thankyou_content', true );
		if( !$free_thankyou_content ) $free_thankyou_content = $global_free_thankyou_content;
		$subscription_thankyou_content = wcfm_get_post_meta( $membership_id, 'subscription_thankyou_content', true );
		if( !$subscription_thankyou_content ) $subscription_thankyou_content = $global_subscription_thankyou_content;
		$subscription_welcome_email_subject = wcfm_get_post_meta( $membership_id, 'subscription_welcome_email_subject', true );
		if( !$subscription_welcome_email_subject ) $subscription_welcome_email_subject = $global_subscription_welcome_email_subject;
		$subscription_welcome_email_content = wcfm_get_post_meta( $membership_id, 'subscription_welcome_email_content', true );
		if( !$subscription_welcome_email_content ) $subscription_welcome_email_content = $global_subscription_welcome_email_content;
	} else {
		wcfm_restriction_message_show( "Invalid Membership" );
		return;
	}
}

$products_array = array();
if( $subscription_product ) {
	$products_array[$subscription_product] = get_the_title( $subscription_product );
}


$group_arr = array( '' => __( '- Choose a Group -', 'wc-multivendor-membership' ) );
$is_marketplace = wcfm_is_marketplace();
if( WCFM_Dependencies::wcfmgs_plugin_active_check() ) {
	$args = array(
							'posts_per_page'   => -1,
							'offset'           => 0,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => 'wcfm_vendor_groups',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array('draft', 'pending', 'publish'),
							'suppress_filters' => true 
						);
	$wcfm_groups_array = get_posts( $args );
	if( !empty( $wcfm_groups_array ) ) {
		foreach( $wcfm_groups_array as $wcfm_group ) {
			$group_arr[$wcfm_group->ID] = $wcfm_group->post_title;
		}
	}
}

$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
$membership_feature_lists = array();
if( isset( $wcfm_membership_options['membership_features'] ) ) $membership_feature_lists = $wcfm_membership_options['membership_features'];

$membership_payment_settings = array();
if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
$payment_methods = array( 'paypal' );
if( isset( $membership_payment_settings['payment_methods'] ) ) $payment_methods = $membership_payment_settings['payment_methods'];

$period_options = array( 'D' => __( 'Day(s)', 'wc-multivendor-membership' ), 'M' => __( 'Month(s)', 'wc-multivendor-membership' ), 'Y' => __( 'Year(s)', 'wc-multivendor-membership' ) );

$commission_type_arr = array( 'percent' => __( 'Percent', 'wc-multivendor-membership' ), 'fixed' => __( 'Fixed', 'wc-multivendor-membership' ) );

do_action( 'before_wcfm_memberships_manage' );

?>

<div class="collapse wcfm-collapse">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-plus"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Manage Membership', 'wc-multivendor-membership' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
	   <h2><?php if( $membership_id ) { _e('Edit Membership', 'wc-multivendor-membership' ); } else { _e('Add Membership', 'wc-multivendor-membership' ); } ?></h2>
			
			<?php
			echo '<a class="wcfm_gloabl_settings text_tip" href="'.get_wcfm_memberships_settings_url().'" data-tip="' . __('Settings', 'wc-frontend-manager') . '"><span class="wcfmfa fa-cog"></span></a>';
			echo '<a id="add_new_Membership_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_memberships_url().'" data-tip="' . __('Memberships', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Memberships', 'wc-multivendor-membership') . '</span></a>';
			if( $has_new = apply_filters( 'wcfm_add_new_membership_sub_menu', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_memberships_manage_url().'" data-tip="' . __('Add New Membership', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-user-plus"></span></a>';
			}
			echo '<a class="add_new_wcfm_ele_dashboard text_tip" target="_blank" href="'.get_wcfm_membership_url().'" data-tip="' . __('Membership Plan Table', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-eye"></span></a>';
			echo '<a class="add_new_wcfm_ele_dashboard wcfm_tutorials text_tip" target="_blank" href="https://www.youtube.com/embed/0l9RAgUpV2w" data-tip="' . __('Tutorial', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-video"></span></a>';
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'begin_wcfm_memberships_manage' ); ?>
	  
		<form id="wcfm_memberships_manage_form" class="wcfm">
		
			<?php do_action( 'begin_wcfm_memberships_manage_form' ); ?>
			
			<!-- collapsible -->
			<div class="wcfm-container">
				<div id="memberships_manage_general_expander" class="wcfm-content">
						<?php
							$membership_manager_fields_general = apply_filters( 'membership_manager_fields_general', array(  
																																															"is_wcfm_membership_disable" => array('label' => __('Disable Membership', 'wc-multivendor-membership') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $is_wcfm_membership_disable ),
																																															"is_wcfm_membership_plan_disable" => array('label' => __('Hide from Plan Table', 'wc-multivendor-membership') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'hints' => __( 'Set this ON to hide this mebership level from Membership plan table.', 'wc-multivendor-membership' ), 'dfvalue' => $is_wcfm_membership_plan_disable ),
																																															'is_restricted' => array( 'label' => __( 'One Time Subscription', 'wc-multivendor-membership' ), 'name' => 'subscription[is_restricted]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $is_restricted, 'hints' => __( 'Enable this to restrict users from subscribe more than once to this plan.', 'wc-multivendor-membership' ) ),
																																															"title" => array('label' => __('Name', 'wc-multivendor-membership') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $title),
																																															"subscribe_button_label" => array( 'label' => __( 'Subscribe Button Label', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'custom_attributes' => array( 'required' => true ), 'value' => $subscribe_button_label ),
																																															"excerpt" => array('label' => __('Description', 'wc-multivendor-membership') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $description),
																																															"subscribe_button" => array('label' => __('Subscribe Button Shortcode', 'wc-multivendor-membership') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'attributes' => array( 'readonly' => true ), 'label_class' => 'wcfm_title', 'value' => '[wcfmvm_subscribe id="'.$membership_id.'"]', 'desc' => __( 'Add this short code anywhere to your site to show subscribe button for this membership plan. Default button label `Subscribe Now`, change using parameter `subscribe_now`. e.g. [wcfmvm_subscribe id="599" subscribe_now="Register Now"]', 'wc-multivendor-membership' ) ),
																																															"subscribe_button_url" => array('label' => __('Subscribe Button URL', 'wc-multivendor-membership') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'attributes' => array( 'readonly' => true ), 'label_class' => 'wcfm_title', 'value' => add_query_arg( array( 'action' => 'wcfm_choose_membership', 'membership' => $membership_id, 'method' => 'by_url' ), WC()->ajax_url() ), 'desc' => __( 'You may use this URL to your custom subscription button or link.', 'wc-multivendor-membership' ) ),
																																															"membership_id" => array('type' => 'hidden', 'value' => $membership_id)
																																					), $membership_id );
							
							if( !$membership_id ) {
								unset( $membership_manager_fields_general['subscribe_button'] );
								unset( $membership_manager_fields_general['subscribe_button_url'] );
							}
							 
							$WCFM->wcfm_fields->wcfm_generate_form_field( $membership_manager_fields_general );
						?>
				</div>
			</div>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<div class="wcfm-tabWrap">
				<!-- collapsible -->
				<div class="page_collapsible" id="memberships_features_manage_head">
					<label class="wcfmfa fa-sun"></label>
					<?php _e('Features', 'wc-multivendor-membership'); ?>
				</div> 
				<div class="wcfm-container">
					<div id="memberships_features_manage_expander" class="wcfm-content">
							<?php
							  $has_feature = false;
								if( !empty( $membership_feature_lists ) ) {
									foreach( $membership_feature_lists as $membership_feature_key => $membership_feature_list ) {
										if( isset( $membership_feature_list['feature'] ) && !empty( $membership_feature_list['feature'] ) ) {
											$has_feature = true;
											$feature_name = sanitize_title($membership_feature_list['feature']);
											$feature_val = '';
											if( !empty( $features ) && isset( $features[$feature_name] ) ) $feature_val = $features[$feature_name];
											if( !empty( $features ) && !$feature_val && isset( $features[$membership_feature_list['feature']] ) ) $feature_val = $features[$membership_feature_list['feature']];
											$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_features', array(  
																																																			$feature_name => array( 'label' => $membership_feature_list['feature'], 'name' => 'features[' . $feature_name . ']', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $feature_val ),
																																									) ) );
										}
									}
								} else {
									echo __( 'First setup membership basic settings', 'wc-multivendor-membership' ) . ' <a href="' . get_wcfm_memberships_settings_url() . '">' . __( 'here', 'wc-multivendor-membership' ) . '</a>.' ;
								}
								if( !$has_feature ) {
									echo __( 'Define feature list from membership basic settings', 'wc-multivendor-membership' ) . ' <a href="' . get_wcfm_memberships_settings_url() . '">' . __( 'here', 'wc-multivendor-membership' ) . '</a>.' ;
								}
							?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
			
				<!-- collapsible -->
				<div class="page_collapsible" id="memberships_subscription_manage_head">
					<label class="wcfmfa fa-money-bill-alt"></label>
					<?php _e('Subscription', 'wc-multivendor-membership'); ?>
				</div> 
				<div class="wcfm-container">
					<div id="memberships_subscription_manage_expander" class="wcfm-content">
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_subscription', array(  
																																					'is_free' => array( 'label' => __( 'Free Membership', 'wc-multivendor-membership' ), 'name' => 'subscription[is_free]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $is_free ),
																																				) ) );  
					 ?>
					 <div class="wcfm_subs_fields subscription_options">
						 <?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_subscription_types', array(  
																																						'subscription_type'      => array( 'label' => __( 'Subscription Type', 'wc-multivendor-membership' ), 'name' => 'subscription[subscription_type]', 'type' => 'select', 'options' => array( 'one_time' => __( 'One Time', 'wc-multivendor-membership' ), 'recurring' => __( 'Recurring', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $subscription_type ),
																																						'subscription_pay_mode'  => array( 'label' => __( 'Payment Mode', 'wc-multivendor-membership' ), 'name' => 'subscription[subscription_pay_mode]', 'type' => 'select', 'options' => array( 'by_wc' => __( 'WC Checkout', 'wc-multivendor-membership' ), 'by_wcfm' => __( 'Integrate Payment Options', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $subscription_pay_mode, 'hints' => __( 'WC Checkout option will allow to process payment using WC default checkout page, all WC payment gateways supported.', 'wc-multivendor-membership' ) ),
																																						'subscription_product'   => array('label' => __('WC Product', 'wc-frontend-manager'), 'name' => 'subscription[subscription_product]', 'type' => 'select', 'attributes' => array( 'style' => 'width: 60%;' ), 'class' => 'wcfm-select wcfm_ele subscription_product_ele', 'label_class' => 'wcfm_title subscription_product_ele', 'options' => $products_array, 'value' => $subscription_product, 'hints' => __( 'This product will be used at WC checkout for membership purchase.', 'wc-multivendor-membership' ) )
																																					) ) );  
						 ?>
						 <div class="wcfm_clearfix"></div>
						 <?php wcfm_video_tutorial( 'https://www.youtube.com/embed/SfOMIxNfr3w' ); ?>
						 <div class="wcfm_clearfix"></div>
						 <div class="subscription_one_time_options">
							 <?php
							 $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_subscription_one_time', array(  
																																							'one_time_amt' => array( 'label' => __( 'Payment Amount', 'wc-multivendor-membership' ), 'name' => 'subscription[one_time_amt]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $one_time_amt, 'hints' => __( 'Enter payment amount. Example values: 10.00 or 19.50 or 299.95 etc (do not put currency symbol).', 'wc-multivendor-membership' ) ),
																																						) ) );
								?>
						 </div>
						 <div class="wcfm_clearfix"></div>
						 <div class="subscription_recurring_options">
						   <?php
						   $trial_amount_desc = '';
						   if( in_array( 'paypal', $payment_methods ) ) {
						   	 $trial_amount_desc = sprintf( __( '<b>PayPal</b> does not support Free trial, if leave empty it will be considered as <b>%s1</b>.', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() );
						   }
						   if( in_array( 'stripe', $payment_methods ) ) {
						   	 if( $trial_amount_desc ) $trial_amount_desc .= "<br />";
						   	 $trial_amount_desc .= __( '<b>Stripe</b> does not support trial amount, trial period will be always <b>FREE</b>.', 'wc-multivendor-membership' );
						   	 $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_subscription_stripe_plan', array(  
																																							'stripe_plan_id' => array( 'label' => __( 'Stripe Plan ID', 'wc-multivendor-membership' ), 'name' => 'subscription[stripe_plan_id]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele stripe_plan_ele', 'label_class' => 'wcfm_title stripe_plan_ele', 'desc_class' => 'stripe_plan_ele', 'value' => $stripe_plan_id, 'hints' => __( 'ID of the plan that you want subscribers to be assigned to.', 'wc-multivendor-membership' ), 'desc' => sprintf( __( '%sHow can I have this?%s', 'wc-multivendor-membership' ), '<a target="_blank" href="https://wclovers.com/blog/how-can-i-have-stripe-plan-id-for-recurring-membership-plan/">', '</a>' ) ),
																																						) ) );
						   }
						   ?>
							 <div class="wcfm_clearfix"></div>
							 <h2><?php _e( 'Trial Billing Details (Leave empty if you are not offering a trial period)', 'wc-multivendor-membership' ); ?></h2>
							 <div class="wcfm_clearfix"></div>
							 <?php
							 $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_subscription_trial', array(  
																																							'trial_period' => array( 'label' => __( 'Trial Billing Period', 'wc-multivendor-membership' ), 'name' => 'subscription[trial_period]', 'type' => 'number', 'class' => 'wcfm-text trial_period_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $trial_period, 'hints' => __( 'Length of the trial period. Leave empty to disable trial period for this subscription.', 'wc-multivendor-membership' ), 'attributes' => array( 'min' => '', 'step' => '1') ),
																																							'trial_period_type' => array( 'options' => $period_options, 'name' => 'subscription[trial_period_type]', 'type' => 'select', 'class' => 'wcfm-select trial_period_type_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $trial_period_type ),
																																							'trial_amt' => array( 'label' => __( 'Trial Billing Amount', 'wc-multivendor-membership' ), 'name' => 'subscription[trial_amt]', 'type' => 'number', 'class' => 'wcfm-text trial_amt_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $trial_amt, 'dfvalue' => '0', 'hints' => __( 'Amount to be charged for the trial period. Leave empty if you want to offer a free trial period.', 'wc-multivendor-membership' ), 'attributes' => array( 'min' => '0', 'step' => '0.1'), 'desc' => $trial_amount_desc ),
																																						) ) );
								?>
								<div class="wcfm_clearfix"></div>
								<h2><?php _e( 'Billing Details', 'wc-multivendor-membership' ); ?></h2>
								<div class="wcfm_clearfix"></div>
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_subscription_billing', array(  
																																							'billing_amt' => array( 'label' => __( 'Billing Amount Each Cycle', 'wc-multivendor-membership' ), 'name' => 'subscription[billing_amt]', 'type' => 'number', 'class' => 'wcfm-text billing_amt_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $billing_amt, 'hints' => __( 'Amount to be charged on every billing cycle. If used with a trial period then this amount will be charged after the trial period is over. Example values: 10.00 or 19.50 or 299.95 etc (do not put currency symbol).', 'wc-multivendor-membership' ), 'attributes' => array( 'min' => '1', 'step' => '0.1') ),
																																							'billing_period' => array( 'label' => __( 'Billing Cycle', 'wc-multivendor-membership' ), 'name' => 'subscription[billing_period]', 'type' => 'number', 'class' => 'wcfm-text billing_period_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $billing_period, 'hints' => __( 'Set the interval of the recurring payment. Example value: 1 Month (if you want to charge every month)', 'wc-multivendor-membership' ), 'attributes' => array( 'min' => '1', 'step' => '1') ),
																																							'billing_period_type' => array( 'options' => $period_options, 'name' => 'subscription[billing_period_type]', 'type' => 'select', 'class' => 'wcfm-select trial_period_type_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $billing_period_type ),
																																							'billing_period_count' => array( 'label' => __( 'Billing Cycle Count', 'wc-multivendor-membership' ), 'name' => 'subscription[billing_period_count]', 'type' => 'number', 'class' => 'wcfm-text billing_period_count_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $billing_period_count, 'hints' => __( 'After how many cycles should billing stop. Leave this field empty (or enter 0) if you want the payment to continue until the subscription is canceled.', 'wc-multivendor-membership' ), 'attributes' => array( 'min' => '', 'step' => '1') ),
																																							're_attempt' => array( 'label' => __( 'Re-attempt on Failure', 'wc-multivendor-membership' ), 'name' => 'subscription[re_attempt]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $re_attempt, 'hints' => __( 'When checked, the payment will be re-attempted two more times if the payment fails. After the third failure, the subscription will be canceled.', 'wc-multivendor-membership' ) ),
																																						) ) );
								?>
							</div>
						</div>
						<div class="wcfm_subs_fields free_expiry_period_wrapper">
						  <?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_free_expiry_period', array(  
																																							'free_expiry_period' => array( 'label' => __( 'Expire After', 'wc-multivendor-membership' ), 'name' => 'subscription[free_expiry_period]', 'type' => 'number', 'class' => 'wcfm-text trial_period_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $free_expiry_period, 'hints' => __( 'Length of the subscripton period. Set `Never Expire` leave this field empty.', 'wc-multivendor-membership' ), 'attributes' => array( 'min' => '1', 'step' => '1') ),
																																							'free_expiry_period_type' => array( 'options' => $period_options, 'name' => 'subscription[free_expiry_period_type]', 'type' => 'select', 'class' => 'wcfm-select trial_period_type_box wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $free_expiry_period_type ),
																																						) ) );
							?>
						</div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<div class="page_collapsible" id="memberships_commission_manage_head">
					<label class="wcfmfa fa-check-circle"></label>
					<?php _e('Approval', 'wc-multivendor-membership'); ?>
				</div> 
				<div class="wcfm-container">
					<div id="memberships_approval_manage_expander" class="wcfm-content">
					  <?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_approval', array(  
																																				"required_approval" => array( 'label' => __( 'Required Approval', 'wc-multivendor-membership' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $required_approval, 'hints' => __( 'Whether user required Admin Approval to become vendor or not!', 'wc-multivendor-membership' ) ),
																																				"vendor_reject_rule" => array( 'label' => __( 'If Application Rejected?', 'wc-multivendor-membership'), 'type' => 'select', 'options' => array( 'same' => __( 'Keep as normal user', 'wc-multivendor-membership') , 'delete' => __( 'Delete user from system', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'This rule will be applicable if a vendor application reject by Admin.', 'wc-multivendor-membership' ), 'value' => $vendor_reject_rule ),
																																				) ) );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_form_thankyou_head">
					<label class="wcfmfa fa-thumbs-up"></label>
					<?php _e('Thank You', 'wc-multivendor-membership'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_form_thankyou_expander" class="wcfm-content">
					  <h2><?php _e('Thank You Page Content', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_thankyou_fields', array(  
																																				'free_thankyou_content' => array( 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'desc_class' => 'instructions', 'value' => $free_thankyou_content, 'desc' => __( 'Please don\'t include Dashboard URL, it will be automatically append with the content.', 'wc-multivendor-membership' ) ),
																																				) ) );
						?>
						<div class="wcfm_clearfix"></div><br />
						<h2><?php _e('On Approval Thank You Page Content', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_thankyou_approval_fields', array(  
																																				'subscription_thankyou_content' => array( 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'desc_class' => 'instructions', 'value' => $subscription_thankyou_content, 'desc' => __( 'This content will be visible when user will require Admin approval to become vendor.', 'wc-multivendor-membership' ) )
																																				) ) );
						?>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<div class="page_collapsible" id="membership_form_thankyou_head">
					<label class="wcfmfa fa-envelope"></label>
					<?php _e('Welcome Email', 'wc-multivendor-membership'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="membership_form_thankyou_expander" class="wcfm-content">
					  <h2><?php _e('Vendor Welcome Email', 'wc-multivendor-membership'); ?></h2>
					  <div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_welcome_email_fields', array(  
																																				'subscription_welcome_email_subject' => array( 'label' => __( 'Notification Subject', 'wc-multivendor-membership' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $subscription_welcome_email_subject ),
																																				'subscription_welcome_email_content' => array( 'label' => __( 'Notification Content', 'wc-multivendor-membership' ), 'type' => 'wpeditor', 'class' => 'wcfm-text wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'value' => $subscription_welcome_email_content )
																																				) ) );
						?>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<div class="page_collapsible" id="memberships_approval_manage_head">
					<label class="wcfmfa fa-percent"></label>
					<?php _e('Commission', 'wc-multivendor-membership'); ?>
				</div> 
				<div class="wcfm-container">
					<div id="memberships_commission_manage_expander" class="wcfm-content">
					  <?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_commission', array(  
																																				"commission_type" => array( 'label' => __( 'Commission Type', 'wc-multivendor-membership' ), 'type' => 'select', 'name' => 'commission[type]', 'options' => $commission_type_arr, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $commission_type ),
																																				"commission_value" => array( 'label' => __( 'Commission Value', 'wc-multivendor-membership' ), 'type' => 'text', 'name' => 'commission[value]', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $commission_value ),
																																				) ) );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php do_action( 'wcfm_memberships_manage_form_after_commission', $membership_id ); ?>
			
				<!-- collapsible -->
				<div class="page_collapsible" id="memberships_groups_manage_capability_head">
					<label class="wcfmfa fa-user fa-user-times"></label>
					<?php _e('Capability', 'wc-multivendor-membership'); ?>
				</div> 
				<div class="wcfm-container">
					<div id="memberships_groups_manage_capability_expander" class="wcfm-content">
					  <h2><?php _e('Capability Controller', 'wc-multivendor-membership'); ?></h2>
						<?php wcfm_video_tutorial( 'https://www.youtube.com/embed/pO4_9xEzWic' ); ?>
						<div class="wcfm_clearfix"></div>
					  <?php
					  if( WCFM_Dependencies::wcfmgs_plugin_active_check() ) {
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_manager_fields_groups', array(  
																																					"associated_group" => array( 'label' => __( 'Associate Group', 'wc-multivendor-membership' ), 'type' => 'select', 'options' => $group_arr, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $associated_group ),
																																					) ) );
						} else {
							if( $is_wcfmgs_inactive_notice_show = apply_filters( 'is_wcfmgs_inactive_notice_show', true ) ) {
								wcfmgs_feature_help_text_show( __( 'Associate Capability', 'wc-multivendor-membership' ) );
							}
						} 
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<?php if( apply_filters( 'wcfm_is_pref_vendor_badges', true ) ) { ?>
					<div class="page_collapsible" id="memberships_badges_manage_head">
						<label class="wcfmfa fa-certificate"></label>
						<?php _e('Badges', 'wc-multivendor-membership'); ?>
					</div> 
					<div class="wcfm-container">
						<div id="memberships_badges_manage_expander" class="wcfm-content">
							<?php
							if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
								do_action( 'wcfm_membership_badges', $membership_id );
							} else {
								if( apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
									wcfmu_feature_help_text_show( __( 'Member Badges', 'wc-multivendor-membership' ) );
								}
								printf( __( '<a target="_blank" href="%s">Know more about WCFM Badges.</a>', 'wc-multivendor-membership' ), 'https://wclovers.com/knowledgebase/wcfm-vendor-badges/' );
							} 
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
				<?php } ?>
				<!-- end collapsible -->
			
				<?php do_action( 'end_wcfm_memberships_manage_form', $membership_id ); ?>
			</div>
			
			<div id="wcfm_Membership_manager_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="membership-manager-data" value="<?php _e( 'Submit', 'wc-frontend-manager' ); ?>" id="wcfm_Membership_manager_submit_button" class="wcfm_submit_button" />
			</div>
			<?php
			do_action( 'after_wcfm_memberships_manage' );
			?>
		</form>
	</div>
</div>