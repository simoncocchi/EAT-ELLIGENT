<?php
/**
 * WCFM plugin view
 *
 * WCFM WC Marketplace Settings View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view
 * @version   2.2.5
 */

global $WCFM, $wpdb, $WCMp;

$wcfm_is_allow_manage_settings = apply_filters( 'wcfm_is_allow_manage_settings', true );
if( !$wcfm_is_allow_manage_settings ) {
	wcfm_restriction_message_show( "Settings" );
	return;
}

$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
$vendor = new WCMp_Vendor( $user_id );
$shop_name = get_user_meta( $user_id, '_vendor_page_title', true );
$logo_image_url = get_user_meta( $user_id, '_vendor_image', true );
$shop_description = get_user_meta( $user_id, '_vendor_description', true );

$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
$wpeditor = apply_filters( 'wcfm_is_allow_settings_wpeditor', 'wpeditor' );
if( $wpeditor && $rich_editor ) {
	$rich_editor = 'wcfm_wpeditor';
} else {
	$wpeditor = 'textarea';
}
if( !$rich_editor ) {
	$breaks = array("<br />","<br>","<br/>"); 
	
	$shop_description = str_ireplace( $breaks, "\r\n", $shop_description );
	$shop_description = strip_tags( $shop_description );
}

$wcfm_vacation_mode = ( get_user_meta( $user_id, 'wcfm_vacation_mode', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_mode', true ) : 'no';
$wcfm_disable_vacation_purchase = ( get_user_meta( $user_id, 'wcfm_disable_vacation_purchase', true ) ) ? get_user_meta( $user_id, 'wcfm_disable_vacation_purchase', true ) : 'no';
$wcfm_vacation_mode_type = ( get_user_meta( $user_id, 'wcfm_vacation_mode_type', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_mode_type', true ) : 'instant';
$wcfm_vacation_start_date = ( get_user_meta( $user_id, 'wcfm_vacation_start_date', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_start_date', true ) : '';
$wcfm_vacation_end_date = ( get_user_meta( $user_id, 'wcfm_vacation_end_date', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_end_date', true ) : '';
$wcfm_vacation_mode_msg = get_user_meta( $user_id, 'wcfm_vacation_mode_msg', true );

$banner_image_url = get_user_meta( $user_id, '_vendor_banner', true );
$shop_phone = get_user_meta( $user_id, '_vendor_phone', true );
//$shop_email = get_user_meta( $user_id, '_vendor_email', true );

$addr_1  = get_user_meta( $user_id, '_vendor_address_1', true );
$addr_2  = get_user_meta( $user_id, '_vendor_address_2', true );
$country  = get_user_meta( $user_id, '_vendor_country', true );
$city  = get_user_meta( $user_id, '_vendor_city', true );
$state  = get_user_meta( $user_id, '_vendor_state', true );
$zip  = get_user_meta( $user_id, '_vendor_postcode', true );

// Country -> States
$country_obj   = new WC_Countries();
$countries     = $country_obj->countries;
$states        = $country_obj->states;
$state_options = array();
if( $state && isset( $states[$country] ) && is_array( $states[$country] ) ) {
	$state_options = $states[$country];
}
if( $state ) $state_options[$state] = $state;

// Billing Details
$_vendor_payment_mode = get_user_meta( $user_id, '_vendor_payment_mode', true );
if (isset($_GET['code'])) {
	$_vendor_payment_mode = 'stripe_masspay';
} elseif (isset($_GET['disconnect_stripe'])) {
	$_vendor_payment_mode = '';
}
$paypal_email = get_user_meta( $user_id, '_vendor_paypal_email', true );
$_vendor_bank_account_type = get_user_meta( $user_id, '_vendor_bank_account_type', true );
$_vendor_bank_account_number = get_user_meta( $user_id, '_vendor_bank_account_number', true );
$_vendor_bank_name = get_user_meta( $user_id, '_vendor_bank_name', true );
$_vendor_aba_routing_number = get_user_meta( $user_id, '_vendor_aba_routing_number', true );
$_vendor_bank_address = get_user_meta( $user_id, '_vendor_bank_address', true );
$_vendor_destination_currency = get_user_meta( $user_id, '_vendor_destination_currency', true );
$_vendor_iban = get_user_meta( $user_id, '_vendor_iban', true );
$_vendor_account_holder_name = get_user_meta( $user_id, '_vendor_account_holder_name', true );

$payment_admin_settings = get_option('wcmp_payment_settings_name');
$payment_mode = array('' => __('Payment Mode', 'dc-woocommerce-multi-vendor'));
if (isset($payment_admin_settings['payment_method_paypal_masspay']) && $payment_admin_settings['payment_method_paypal_masspay'] = 'Enable') {
	$payment_mode['paypal_masspay'] = __('PayPal Masspay', 'dc-woocommerce-multi-vendor');
}
if (isset($payment_admin_settings['payment_method_paypal_payout']) && $payment_admin_settings['payment_method_paypal_payout'] = 'Enable') {
	$payment_mode['paypal_payout'] = __('PayPal Payout', 'dc-woocommerce-multi-vendor');
}
if (isset($payment_admin_settings['payment_method_stripe_masspay']) && $payment_admin_settings['payment_method_stripe_masspay'] = 'Enable') {
	$payment_mode['stripe_masspay'] = __('Stripe Connect', 'dc-woocommerce-multi-vendor');
}
if (isset($payment_admin_settings['payment_method_direct_bank']) && $payment_admin_settings['payment_method_direct_bank'] = 'Enable') {
	$payment_mode['direct_bank'] = __('Direct Bank', 'dc-woocommerce-multi-vendor');
}
$wcfm_vendor_payment_modes = apply_filters('wcmp_vendor_payment_mode', $payment_mode);

$is_marketplace = wcfm_is_marketplace();

$wcmp_payment_settings_name = get_option('wcmp_payment_settings_name');

$template_options = apply_filters('wcmp_vendor_shop_template_options', array('template1' => $WCMp->plugin_url.'assets/images/template1.png', 'template2' => $WCMp->plugin_url.'assets/images/template2.png', 'template3' => $WCMp->plugin_url.'assets/images/template3.png'));
$shop_template = get_wcmp_vendor_settings('wcmp_vendor_shop_template', 'vendor', 'dashboard', 'template1');
$shop_template = get_user_meta( $user_id, '_shop_template', true ) ? get_user_meta( $user_id, '_shop_template', true ) : $shop_template;

$current_offset = get_user_meta( $user_id, 'gmt_offset', true);
$tzstring = get_user_meta( $user_id, 'timezone_string', true );
// Remove old Etc mappings. Fallback to gmt_offset.
if (false !== strpos($tzstring, 'Etc/GMT')) {
	$tzstring = '';
}

if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists
	$check_zone_info = false;
	if (0 == $current_offset) {
		$tzstring = 'UTC+0';
	} elseif ($current_offset < 0) {
		$tzstring = 'UTC' . $current_offset;
	} else {
		$tzstring = 'UTC+' . $current_offset;
	}
}

$map_address = get_user_meta( $user_id, '_find_address', true ) ? get_user_meta( $user_id, '_find_address', true ) : '';
$store_location = get_user_meta( $user_id, '_store_location', true ) ? get_user_meta( $user_id, '_store_location', true ) : '';
$store_lat = get_user_meta( $user_id, '_store_lat', true ) ? get_user_meta( $user_id, '_store_lat', true ) : 0;
$store_lng = get_user_meta( $user_id, '_store_lng', true ) ? get_user_meta( $user_id, '_store_lng', true ) : 0;
?>

<div class="collapse wcfm-collapse" id="">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-cogs"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Settings', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
	  	<h2><?php _e('Store Settings', 'wc-frontend-manager' ); ?></h2>
	  	
	  	<?php 
	  	do_action( 'wcfm_vendor_setting_header_before', $user_id );
			if( apply_filters( 'wcfm_is_allow_social_profile', true ) ) {
				echo '<a id="wcfm_social_settings" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_profile_url().'#sm_profile_form_social_head" data-tip="' . __( 'Social', 'wc-frontend-manager' ) . '"><span class="wcfmfa fa-users"></span><span class="text">' . __( 'Social', 'wc-frontend-manager' ) . '</span></a>';
			}
			do_action( 'wcfm_vendor_setting_header_after', $user_id );
			?>
	  	<div class="wcfm-clearfix"></div>
		</div>	
	  <div class="wcfm-clearfix"></div><br />
	  
		<?php do_action( 'before_wcfm_wcmarketplace_settings' ); ?>
		
		<form id="wcfm_settings_form" class="wcfm">
	
			<?php do_action( 'begin_wcfm_wcmarketplace_settings_form' ); ?>
			
			<div class="wcfm-tabWrap">
			  <!-- collapsible - Store -->
				<div class="page_collapsible" id="wcfm_settings_dashboard_head">
					<label class="wcfmfa fa-shopping-bag"></label>
					<?php _e('Store', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_vendor_expander" class="wcfm-content">
						<?php
							$settings_fields_general = apply_filters( 'wcfm_wcmarketplace_settings_fields_general', array(
																																																"wcfm_logo" => array('label' => __('Logo', 'wc-frontend-manager') , 'type' => 'upload', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 150, 'value' => $logo_image_url, 'hints' => __( 'Preferred logo should be 200x200 px.', 'wc-frontend-manager' ) ),
																																																"shop_name" => array('label' => __('Shop Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor->page_title, 'hints' => __( 'Your shop name is public and must be unique.', 'wc-frontend-manager' ) ),
																																																"shop_slug" => array('label' => __('Shop Slug', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor->page_slug, 'hints' => __( 'Your shop slug is public and must be unique.', 'wc-frontend-manager' ) ),
																																																"shop_description" => array('label' => __('Shop Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele ' . $rich_editor, 'label_class' => 'wcfm_title', 'value' => $shop_description, 'hints' => __( 'This is displayed on your shop page.', 'wc-frontend-manager' ) ),
																																																), $user_id );
						  if( !apply_filters( 'wcfm_is_allow_store_logo', true ) ) {
								if( isset( $settings_fields_general['wcfm_logo'] ) ) { unset( $settings_fields_general['wcfm_logo'] ); }
							}
							
							if( !apply_filters( 'wcfm_is_allow_store_name', true ) ) {
								if( isset( $settings_fields_general['shop_name'] ) ) { unset( $settings_fields_general['shop_name'] ); }
								if( isset( $settings_fields_general['shop_slug'] ) ) { unset( $settings_fields_general['shop_slug'] ); }
							}
							
							if( !apply_filters( 'wcfm_is_allow_store_description', true ) ) {
								if( isset( $settings_fields_general['shop_description'] ) ) { unset( $settings_fields_general['shop_description'] ); }
							}
							
						  $WCFM->wcfm_fields->wcfm_generate_form_field( $settings_fields_general );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible - Brand -->
				<?php if( $wcfm_is_allow_brand_settings = apply_filters( 'wcfm_is_allow_brand_settings', true ) ) { ?>
					<div class="page_collapsible" id="wcfm_settings_form_identity_head">
						<label class="wcfmfa fa-id-card-o"></label>
						<?php _e('Branding', 'wc-frontend-manager'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="wcfm_settings_form_identity_expander" class="wcfm-content">
							<?php
							 $settings_fields_identity = apply_filters( 'wcfm_wcmarketplace_settings_fields_identity', array(
																																						"banner" => array('label' => __('Banner', 'wc-frontend-manager') , 'type' => 'upload', 'class' => 'wcfm-text wcfm-banner-uploads wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'prwidth' => 250, 'value' => $banner_image_url, 'hints' => __( 'Preferred banner should be 1200x245 px.', 'wc-frontend-manager' ) ),
																																						//"shop_email" => array('label' => __('Shop Email', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $shop_email, 'hints' => __( 'Your store Email address.', 'wc-frontend-manager' ) ),
																																						"shop_phone" => array('label' => __('Shop Phone', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $shop_phone, 'hints' => __( 'Your store phone no.', 'wc-frontend-manager' ) ),
																																						), $user_id );
							 
							if( !apply_filters( 'wcfm_is_allow_store_banner', true ) ) {
								if( isset( $settings_fields_identity['banner'] ) ) { unset( $settings_fields_identity['banner'] ); }
							}
							
							if( !apply_filters( 'wcfm_is_allow_store_phone', true ) ) {
								if( isset( $settings_fields_identity['shop_phone'] ) ) { unset( $settings_fields_identity['shop_phone'] ); }
							}
									
							$WCFM->wcfm_fields->wcfm_generate_form_field( $settings_fields_identity );		
							if( apply_filters( 'wcfm_is_allow_store_address', true ) ) {
							?>
						
								<div class="wcfm_clearfix"></div>
								<div class="wcfm_vendor_settings_heading"><h3><?php _e( 'Store Address', 'wc-frontend-manager' ); ?></h3></div>
								<div class="store_address">
									<?php
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcmarketplace_settings_fields_address', array(
																																																			"addr_1" => array('label' => __('Address 1', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $addr_1 ),
																																																			"addr_2" => array('label' => __('Address 2', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $addr_2 ),
																																																			"country" => array('label' => __('Country', 'wc-frontend-manager') , 'type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'style' => 'width: 60%;' ), 'value' => $country ),
																																																			"state" => array('label' => __('State/County', 'wc-frontend-manager') , 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => $state_options, 'value' => $state ),
																																																			"city" => array('label' => __('City/Town', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $city ),
																																																			"zip" => array('label' => __('Postcode/Zip', 'wc-frontend-manager') , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $zip, 'attributes' => array( 'min' => '1', 'step'=> '1' ) ),
																																																			), $user_id ) );
										
									?>
								</div>
							  <?php
							}
							?>
							
							
							<p class="tzstring wcfm_title wcfm_ele"><strong><?php _e('Timezone', 'wc-frontend-manager'); ?></strong><span class="img_tip" data-tip="<?php _e('Set the local timezone.', 'wc-frontend-manager'); ?>"></span></p>
							<label class="screen-reader-text" for="tzstring"><?php _e('Timezone', 'wc-frontend-manager'); ?></label>
							<select id="timezone" name="timezone" class="wcfm-select wcfm_ele" style="width: 60%;">
								<?php echo wp_timezone_choice( $tzstring, get_user_locale() ); ?>
							</select>
						
							<?php if( apply_filters( 'wcfm_is_allow_store_address', true ) ) { ?>
								<div class="wcfm_clearfix"></div>
								<div class="wcfm_vendor_settings_heading"><h3><?php _e( 'Store Location', 'wc-frontend-manager' ); ?></h3></div>
								<div class="store_address store_location_wrap">
									<?php 
									$api_key = get_wcmp_vendor_settings('google_api_key');
									if ( !empty( $api_key ) ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcmarketplace_settings_fields_location', array(
																																																		"find_address" => array( 'label' => __( 'Find Location', 'wc-frontend-manager' ), 'placeholder' => __( 'Type an address to find', 'wc-frontend-manager' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $map_address ),
																																																		"store_location" => array( 'type' => 'hidden', 'value' => $store_location ),
																																																		"store_lat" => array( 'type' => 'hidden', 'value' => $store_lat ),
																																																		"store_lng" => array( 'type' => 'hidden', 'value' => $store_lng ),
																																																		), $user_id ) );
									
										?>
										<div class="wcfm_clearfix"></div><br />
										<div class="wcfm-wcmarketplace-google-map" id="wcfm-wcmarketplace-map"></div>
										<div class="wcfm_clearfix"></div><br />
									<?php
									} else {
										echo __('Please contact your administrator to enable Google map feature.', 'wc-frontend-manager');
									}
									?>
								</div>
							<?php } ?>
							
							<?php if( get_wcmp_vendor_settings( 'can_vendor_edit_shop_template', 'vendor', 'dashboard', false ) ) { ?>
								<div class="wcfm_clearfix"></div>
								<div class="wcfm_vendor_settings_heading"><h3><?php _e( 'Shop Template', 'wc-frontend-manager' ); ?></h3></div>
								<div class="store_address">
									<ul class="wcfm_wcmp_template_list">
										<?php foreach ($template_options as $template => $template_image): ?>
											<li>
												<label>
													<input type="radio" <?php checked($template, $shop_template); ?> name="shop_template" value="<?php echo $template; ?>" />  
													<i class="wcfmfa fa-square-o" aria-hidden="true"></i>
													<img src="<?php echo $template_image; ?>" />
												</label>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php } ?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
				<?php } ?>
				<!-- end collapsible -->
				
				<!-- collapsible - Billing -->
				<?php if( $wcfm_is_allow_billing_settings = apply_filters( 'wcfm_is_allow_billing_settings', true ) ) { ?>
					<div class="page_collapsible" id="wcfm_settings_form_payment_head">
						<label class="wcfmfa fa-money fa-money-bill-alt"></label>
						<?php _e('Payment', 'wc-frontend-manager'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="wcfm_settings_form_payment_expander" class="wcfm-content">
							<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcmarketplace_settings_fields_billing', array(
																																				"_vendor_payment_mode" => array('label' => __('Payment Method', 'dc-woocommerce-multi-vendor') , 'type' => 'select', 'options' => $wcfm_vendor_payment_modes, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $_vendor_payment_mode ),
																																				"paypal_email" => array('label' => __('PayPal Email', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_paypal_payout paymode_paypal_masspay paymode_paypal_adaptive', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_paypal_payout paymode_paypal_masspay paymode_paypal_adaptive', 'value' => $paypal_email ),
																																				"_vendor_bank_account_type" => array('label' => __('Account Type', 'wc-frontend-manager') , 'type' => 'select', 'options' => array( 'current' => __( 'Current', 'wc-frontend-manager'), 'savings' => __( 'Savings', 'wc-frontend-manager') ), 'class' => 'wcfm-select wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_bank_account_type ),
																																				"_vendor_bank_account_number" => array('label' => __('Account Number', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_bank_account_number ),
																																				"_vendor_bank_name" => array('label' => __('Bank Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_bank_name ),
																																				"_vendor_aba_routing_number" => array('label' => __('ABA Routing Number', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_aba_routing_number ),
																																				"_vendor_bank_address" => array('label' => __('Bank Address', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_bank_address ),
																																				"_vendor_destination_currency" => array('label' => __('Destination Currency', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_destination_currency ),
																																				"_vendor_iban" => array('label' => __('Account IBAN', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_iban ),
																																				"_vendor_account_holder_name" => array('label' => __('Account Holder Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_direct_bank', 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_direct_bank', 'value' => $_vendor_account_holder_name ),
																																				), $user_id ) );
							do_action( 'wcfm_wcmarketplace_billing_settings_fields', $user_id );
							?>
							<div class="paymode_field paymode_stripe_masspay">
							  <?php
							  if( apply_filters( 'wcfm_is_allow_billing_stripe', true ) ) {
									global $WCMp_Stripe_Gateway;
									$vendor = get_wcmp_vendor($user_id);
									if ($vendor) {
										if (isset($payment_admin_settings['payment_method_stripe_masspay']) && $payment_admin_settings['payment_method_stripe_masspay'] = 'Enable') {
											$testmode = get_wcmp_vendor_settings('testmode', 'payment', 'stripe_gateway') === "Enable" ? true : false;
											$client_id = $testmode ? get_wcmp_vendor_settings('test_client_id', 'payment', 'stripe_gateway') : get_wcmp_vendor_settings('live_client_id', 'payment', 'stripe_gateway');
											$secret_key = $testmode ? get_wcmp_vendor_settings('test_secret_key', 'payment', 'stripe_gateway') : get_wcmp_vendor_settings('live_secret_key', 'payment', 'stripe_gateway');
											if (isset($client_id) && isset($secret_key)) {
												if (isset($_GET['code'])) {
													$code = $_GET['code'];
													if (!is_user_logged_in()) {
														if (isset($_GET['state'])) {
															$user_id = wc_clean($_GET['state']);
														}
													}
													$token_request_body = array(
														'grant_type' => 'authorization_code',
														'client_id' => $client_id,
														'code' => $code,
														'client_secret' => $secret_key
													);
													
													$target_url = 'https://connect.stripe.com/oauth/token';
													$headers = array(
														'User-Agent'    => 'WCFM Marketplace Stripe Split Pay',
														'Authorization' => 'Bearer ' . $secret_key,
													);
													$response    = wp_remote_post( $target_url, array(
																																					'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
																																					'timeout'     => 70,
																																					'redirection' => 5,
																																					'blocking'    => true,
																																					'headers'     => $headers,
																																					'body'        => $token_request_body
																																					)
																																				);
													if ( !is_wp_error( $response ) ) {
														$resp = (array) json_decode( $response['body'] );
														if ( !isset($resp['error'] ) ) {
															update_user_meta( $user_id, 'vendor_connected', 1);
															update_user_meta( $user_id, 'admin_client_id', $client_id);
															update_user_meta( $user_id, 'access_token', $resp['access_token']);
															update_user_meta( $user_id, 'refresh_token', $resp['refresh_token']);
															update_user_meta( $user_id, 'stripe_publishable_key', $resp['stripe_publishable_key']);
															update_user_meta( $user_id, 'stripe_user_id', $resp['stripe_user_id']);
															update_user_meta( $user_id, '_vendor_payment_mode', 'stripe_masspay' );
															?>
															<script>
																window.location =  '<?php echo get_wcfm_settings_url() . '#wcfm_settings_form_payment_head'; ?>';
															</script>
															<?php
														}
													}
													if (isset($resp['access_token']) || get_user_meta($user_id, 'vendor_connected', true) == 1) {
														update_user_meta($user_id, 'vendor_connected', 1);
														?>
														<div class="clear"></div>
														<div class="wcmp_stripe_connect">
															<form action="" method="POST">
																<table class="form-table">
																	<tbody>
																		<tr>
																			<th style="width: 35%;">
																				<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
																			</th>
																			<td>
																				<label><?php _e('You are connected with Stripe', 'wc-frontend-manager'); ?></label>
																			</td>
																		</tr>
																		<tr>
																			<th></th>
																			<td>
																				<input type="submit" class="button" name="disconnect_stripe" value="<?php _e( 'Disconnect Stripe Account', 'wc-frontend-manager' ); ?>" />
																			</td>
																		</tr>
																	</tbody>
																</table>
															</form>
														</div>
														<?php
													} else {
														update_user_meta($user_id, 'vendor_connected', 0);
														?>
														<div class="clear"></div>
														<div class="wcmp_stripe_connect">
															<form action="" method="POST">
																<table class="form-table">
																	<tbody>
																		<tr>
																			<th style="width: 35%;">
																				<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
																			</th>
																			<td>
																				<label><?php _e('Please Retry!!!', 'wc-frontend-manager'); ?></label>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</form>
														</div>
														<?php
												}
											} else if (isset($_GET['error'])) { // Error
												update_user_meta($user_id, 'vendor_connected', 0);
												?>
												<div class="clear"></div>
												<div class="wcmp_stripe_connect">
													<table class="form-table">
														<tbody>
															<tr>
																<th style="width: 35%;">
																	<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
																</th>
																<td>
																	<label><?php _e('Please Retry!!!', 'wc-frontend-manager'); ?></label>
																</td>
															</tr>
														</tbody>
													</table>
												</div>
												<?php
											} else {
												
												if (isset($_GET['disconnect_stripe'])) {
													$vendor = get_wcmp_vendor($user_id);
													$stripe_settings = get_wcmp_vendor_settings( 'payment_method_stripe_masspay', 'payment' );
													$stripe_user_id = get_user_meta($user_id, 'stripe_user_id', true);
													if (isset($stripe_settings) && $stripe_settings != 'Enable' && empty($stripe_user_id)) {
															return;
													}
													$testmode = get_wcmp_vendor_settings('testmode', 'payment', 'stripe_gateway') === "Enable" ? true : false;
													$client_id = $testmode ? get_wcmp_vendor_settings('test_client_id', 'payment', 'stripe_gateway') : get_wcmp_vendor_settings('live_client_id', 'payment', 'stripe_gateway');
													$secret_key = $testmode ? get_wcmp_vendor_settings('test_secret_key', 'payment', 'stripe_gateway') : get_wcmp_vendor_settings('live_secret_key', 'payment', 'stripe_gateway');
													$token_request_body = array(
															'client_id' => $client_id,
															'stripe_user_id' => $stripe_user_id,
															'client_secret' => $secret_key
													);
													
													$target_url = 'https://connect.stripe.com/oauth/deauthorize';
													$headers = array(
														'User-Agent'    => 'WCFM Marketplace Stripe Split Pay',
														'Authorization' => 'Bearer ' . $secret_key,
													);
													$response    = wp_remote_post( $target_url, array(
																																					'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
																																					'timeout'     => 70,
																																					'redirection' => 5,
																																					'blocking'    => true,
																																					'headers'     => $headers,
																																					'body'        => $token_request_body
																																					)
																																				);
													if ( $vendor && !is_wp_error( $response ) ) {
														$resp = (array) json_decode( $response['body'] );
														if ( ( isset($resp['error']) && ( $resp['error'] == 'invalid_client' ) )  || isset( $resp['stripe_user_id'] ) ) {
																delete_user_meta( $user_id, 'vendor_connected');
																delete_user_meta( $user_id, 'admin_client_id');
																delete_user_meta( $user_id, 'access_token');
																delete_user_meta( $user_id, 'refresh_token');
																delete_user_meta( $user_id, 'stripe_publishable_key');
																delete_user_meta( $user_id, 'stripe_user_id');
																update_user_meta( $user_id, '_vendor_payment_mode', '' );
																//wc_add_notice(__('Your account has been disconnected', 'marketplace-stripe-gateway'), 'success');
																?>
																<script>
																	window.location =  '<?php echo get_wcfm_settings_url() . '#wcfm_settings_form_payment_head'; ?>';
																</script>
																<?php
														} else {
																//wc_add_notice(__('Unable to disconnect your account pleease try again', 'marketplace-stripe-gateway'), 'error');
														}
													} else {
															//wc_add_notice(__('Unable to disconnect your account pleease try again', 'marketplace-stripe-gateway'), 'error');
													}
												}
												
												
												$vendor_connected = get_user_meta($user_id, 'vendor_connected', true);
												$connected = true;
	
												if (isset($vendor_connected) && $vendor_connected == 1) {
													$admin_client_id = get_user_meta($user_id, 'admin_client_id', true);
	
													if ($admin_client_id == $client_id) {
														?>
														<div class="clear"></div>
														<div class="wcmp_stripe_connect">
															<table class="form-table">
																<tbody>
																	<tr>
																		<th style="width: 35%;">
																				<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
																		</th>
																		<td>
																				<label><?php _e('You are connected with Stripe', 'wc-frontend-manager'); ?></label>
																		</td>
																	</tr>
																	<tr>
																		<th></th>
																		<td>
																				<input type="submit" class="button" name="disconnect_stripe" value="<?php _e('Disconnect Stripe Account', 'wc-frontend-manager'); ?>" />
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<?php
													} else {
														$connected = false;
													}
												} else {
														$connected = false;
												}
	
												if (!$connected) {
	
													$status = delete_user_meta($user_id, 'vendor_connected');
													$status = delete_user_meta($user_id, 'admin_client_id');
	
													// Show OAuth link
													$authorize_request_body = array(
														'response_type' => 'code',
														'scope' => 'read_write',
														'client_id' => $client_id,
														'redirect_uri' => get_wcfm_settings_url(),
														'state' => $user_id
													);
													$url = 'https://connect.stripe.com/oauth/authorize?' . http_build_query($authorize_request_body);
													$stripe_connect_url = $WCMp->plugin_url . 'assets/images/blue-on-light.png';
	
													if (!$status) {
														?>
														<div class="clear"></div>
														<div class="wcmp_stripe_connect">
															<table class="form-table">
																<tbody>
																	<tr>
																		<th style="width: 35%;">
																			<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
																		</th>
																		<td><?php _e('You are not connected with stripe.', 'wc-frontend-manager'); ?></td>
																	</tr>
																	<tr>
																		<th></th>
																		<td>
																			<a href=<?php echo $url; ?> target="_self"><img src="<?php echo $stripe_connect_url; ?>" /></a>
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<?php
													} else {
															?>
														<div class="clear"></div>
															<div class="wcmp_stripe_connect">
																<table class="form-table">
																	<tbody>
																		<tr>
																			<th style="width: 35%;">
																				<label><?php _e('Stripe', 'wc-frontend-manager'); ?></label>
																			</th>
																			<td><?php _e('Please connected with stripe again.', 'wc-frontend-manager'); ?></td>
																		</tr>
																		<tr>
																			<th></th>
																			<td>
																					<a href=<?php echo $url; ?> target="_self"><img src="<?php echo $stripe_connect_url; ?>" /></a>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
															<?php
														}
													}
												}
											}
										}
									}
								}
							  ?>
							</div>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
				<?php } ?>
				<!-- end collapsible -->
				
				<!-- collapsible - Shipping -->
				<?php if( apply_filters( 'wcfm_is_allow_shipping', true ) && apply_filters( 'wcfm_is_allow_vshipping_settings', true ) ) { ?>
					<?php if (isset($wcmp_payment_settings_name['give_shipping']) ) { ?>
						<div class="page_collapsible" id="wcfm_settings_form_shipping_head">
							<label class="wcfmfa fa-truck"></label>
							<?php _e('Shipping', 'wc-frontend-manager'); ?><span></span>
						</div>
						<div class="wcfm-container">
							<div id="wcfm_settings_form_shipping_expander" class="wcfm-content">
								<?php
									$vendor_data = get_wcmp_vendor($user_id);
									$vendor_shipping_data = get_user_meta($user_id, 'vendor_shipping_data', true);
									$shipping_class_id = get_user_meta($user_id, 'shipping_class_id', true);
									if (!$shipping_class_id) {
										$shipping_term = get_term_by('slug', $vendor_data->user_data->user_login . '-' . $user_id, 'product_shipping_class', ARRAY_A);
										if (!$shipping_term) {
											$shipping_term = wp_insert_term($vendor_data->user_data->user_login . '-' . $user_id, 'product_shipping_class');
										}
										if (!is_wp_error($shipping_term)) {
											$shipping_term_id = $shipping_term['term_id'];
											update_user_meta($user_id, 'shipping_class_id', $shipping_term['term_id']);
											add_woocommerce_term_meta($shipping_term['term_id'], 'vendor_id', $user_id);
											add_woocommerce_term_meta($shipping_term['term_id'], 'vendor_shipping_origin', get_option('woocommerce_default_country'));
										}
									}
									$shipping_class_id = $shipping_term_id = get_user_meta($user_id, 'shipping_class_id', true);
									$raw_zones = WC_Shipping_Zones::get_zones();
									$raw_zones[] = array('id' => 0);
									$has_shipping_settings_option = false;
									foreach ($raw_zones as $raw_zone) {
										$zone = new WC_Shipping_Zone($raw_zone['id']);
										$raw_methods = $zone->get_shipping_methods();
										foreach ($raw_methods as $raw_method) {
											if ($raw_method->id == 'flat_rate' && isset($raw_method->instance_form_fields["class_cost_" . $shipping_class_id])) {
												echo "<h2>" . __( 'Shipping Zone', 'wc-frontend-manager' ) . ': ' . $zone->get_zone_name() . "</h2><div class='wcfm_clearfix'></div>";
												$instance_field = $raw_method->instance_form_fields["class_cost_" . $shipping_class_id];
												$instance_settings = $raw_method->instance_settings["class_cost_" . $shipping_class_id];
												$option_name = 'woocommerce_' . $raw_method->id . "_" . $raw_method->instance_id . "_settings_class_cost_" . $shipping_class_id;
												$WCFM->wcfm_fields->wcfm_generate_form_field( array( $option_name => array('label' => $instance_field['title'] . ' - ' . $raw_method->title, 'name' => 'wcfm_vendor_shipping_data[' . $option_name . ']', 'placeholder' => $instance_field['placeholder'], 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => esc_attr($instance_settings), 'hints' => strip_tags( $instance_field['description'] ), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) )
																																		) );
												$has_shipping_settings_option = true;
											} elseif ($raw_method->id == 'table_rate') {
												if( WCFM_Dependencies::wcfm_wc_table_rates_active_check() && WCFM_Dependencies::wcfm_wcmp_advanced_shipping_active_check() ) {
													echo "<h2>" . __( 'Shipping Zone', 'wc-frontend-manager' ) . ': ' . $zone->get_zone_name() . "</h2><div class='wcfm_clearfix'></div>";
													
													$table_rate_shipping_rules = array();
													$table_rates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE `rate_class` = {$shipping_class_id} AND `shipping_method_id` = {$raw_method->instance_id} order by 'shipping_method_id' ", OBJECT);
							
													if( !empty($table_rates) ) {
														foreach ( $table_rates as $table_rate ) {
															$table_rate_shipping_rules[] = array(  'rate_condition'   => esc_attr( $table_rate->rate_condition ), 
																																		 'rate_min'     => esc_attr( $table_rate->rate_min ),
																																		 'rate_max'  => esc_attr( $table_rate->rate_max ),
																																		 'rate_cost'      => esc_attr( $table_rate->rate_cost ),
																																		 'rate_cost_per_item' => esc_attr( $table_rate->rate_cost_per_item ),
																																		 'rate_cost_per_weight_unit' => esc_attr( $table_rate->rate_cost_per_weight_unit ),
																																		 'rate_cost_percent' => esc_attr( $table_rate->rate_cost_percent ),
																																		 'rate_label' => esc_attr( $table_rate->rate_label ),
																																		 'rate_id'   => $table_rate->rate_id,
																																		 );
														}
													}
													
													$table_rate_shipping_fields = array( 
																															"wcfm_table_rate_shipping_rules"  => array( 'label' => __('Shipping Rules', 'wc-frontend-manager') , 'type' => 'multiinput', 'name' => 'wcfm_table_rate_shipping_rules['.$raw_method->instance_id.']', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'value' => $table_rate_shipping_rules, 'options' => array(
																																																			"rate_condition" => array('label' => __('Condition', 'wcmp-advance-shipping'), 'type' => 'select', 'options' => array('' => __('None', 'wcmp-advance-shipping'), 'price' => __('Price', 'wcmp-advance-shipping'), 'weight' => __('Weight', 'wcmp-advance-shipping'), 'items' => __('Item count', 'wcmp-advance-shipping')), 'class' => 'wcfm-select wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title' ),
																																																			"rate_label" => array('label' => __('Label', 'wcmp-advance-shipping'), 'type' => 'text', 'class' => 'wcfm-text wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title' ),
																																																			"rate_min" => array('label' => __('Min', 'wcmp-advance-shipping'), 'type' => 'number', 'class' => 'wcfm-text wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title', 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																																			"rate_max" => array('label' => __('Max', 'wcmp-advance-shipping'), 'type' => 'number', 'class' => 'wcfm-text wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title', 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																																			"rate_cost" => array('label' => __('Row cost', 'wcmp-advance-shipping'), 'type' => 'number', 'class' => 'wcfm-text wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title', 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																																			"rate_cost_per_item" => array('label' => __('Item cost', 'wcmp-advance-shipping'), 'type' => 'number', 'class' => 'wcfm-text wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title', 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																																			"rate_cost_per_weight_unit" => array('label' => __('lbs cost', 'wcmp-advance-shipping'), 'type' => 'number', 'class' => 'wcfm-text wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title', 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																																			"rate_cost_percent" => array('label' => __('% cost', 'wcmp-advance-shipping'), 'type' => 'number', 'class' => 'wcfm-text wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title', 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																																			"rate_id" => array( 'type' => 'hidden' )
																																																			)	)								
																														);
													$WCFM->wcfm_fields->wcfm_generate_form_field( $table_rate_shipping_fields );
													$has_shipping_settings_option = true;
												}
											}
										}
									}
									if( !$has_shipping_settings_option ) {
										_e( 'There is no shipping zone or Flat Rate shipping method not associated for the zones to set shipping prices, kindly contact your Store Admin.', 'wc-frontend-manager' );
									}
								?>
							</div>
						</div>
						<div class="wcfm_clearfix"></div>
					<?php } ?>
				<?php } ?>
				<!-- end collapsible -->
				
				<!-- collapsible - Customer Support -->
				<?php if( $wcfm_is_allow_customer_support_settings = apply_filters( 'wcfm_is_allow_customer_support_settings', true ) ) { ?>
					<?php if ( get_wcmp_vendor_settings ('is_customer_support_details', 'general') == 'Enable' ) { ?>
						<div class="page_collapsible" id="wcfm_settings_form_customer_support_head">
							<label class="wcfmfa fa-thumbs-up"></label>
							<?php _e('Customer Support', 'wc-frontend-manager'); ?><span></span>
						</div>
						<div class="wcfm-container">
							<div id="wcfm_settings_form_customer_support_expander" class="wcfm-content">
							  <?php
							    $vendor_customer_phone = get_user_meta( $user_id, '_vendor_customer_phone', true );
									$vendor_customer_email = get_user_meta( $user_id, '_vendor_customer_email', true );
									$vendor_csd_return_address1 = get_user_meta( $user_id, '_vendor_csd_return_address1', true );
									$vendor_csd_return_address2 = get_user_meta( $user_id, '_vendor_csd_return_address2', true );
									$vendor_csd_return_country = get_user_meta( $user_id, '_vendor_csd_return_country', true );
									$vendor_csd_return_city = get_user_meta( $user_id, '_vendor_csd_return_city', true );
									$vendor_csd_return_state = get_user_meta( $user_id, '_vendor_csd_return_state', true );
									$vendor_csd_return_zip = get_user_meta( $user_id, '_vendor_csd_return_zip', true );
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcmarketplace_settings_fields_customer_support', array(
																																																		"vendor_customer_phone" => array('label' => __('Phone', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_customer_phone ),
																																																		"vendor_customer_email" => array('label' => __('Email', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_customer_email ),
																																																		"vendor_csd_return_address1" => array('label' => __('Address 1', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_address1 ),
																																																		"vendor_csd_return_address2" => array('label' => __('Address 2', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_address2 ),
																																																		"vendor_csd_return_country" => array('label' => __('Country', 'wc-frontend-manager') , 'type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'style' => 'width: 60%;' ), 'value' => $vendor_csd_return_country ),
																																																		"vendor_csd_return_city" => array('label' => __('City/Town', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_city ),
																																																		"vendor_csd_return_state" => array('label' => __('State/County', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_state ),
																																																		"vendor_csd_return_zip" => array('label' => __('Postcode/Zip', 'wc-frontend-manager') , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_csd_return_zip, 'attributes' => array( 'min' => '1', 'step'=> '1' ) )
																																																		), $user_id ) );
								?>
							</div>
						</div>
						<div class="wcfm_clearfix"></div>
					<?php } ?>
				<?php } ?>
				<!-- end collapsible -->
				
				<?php do_action( 'end_wcfm_vendor_settings', $user_id ); ?>
				
				<!-- collapsible - Vacation -->
				<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() ) { ?>
					<?php if( apply_filters( 'wcfm_is_pref_vendor_vacation', true ) && apply_filters( 'wcfm_is_allow_vacation_settings', true ) ) { ?>
						<div class="page_collapsible" id="wcfm_settings_form_vacation_head">
							<label class="fab fa-tripadvisor"></label>
							<?php _e('Vacation Mode', 'wc-frontend-manager'); ?><span></span>
						</div>
						<div class="wcfm-container">
							<div id="wcfm_settings_form_vacation_expander" class="wcfm-content">
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_vacation', array(
																																																													"wcfm_vacation_mode" => array('label' => __('Enable Vacation Mode', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $wcfm_vacation_mode ),
																																																													"wcfm_disable_vacation_purchase" => array('label' => __('Disable Purchase During Vacation', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $wcfm_disable_vacation_purchase ),
																																																													"wcfm_vacation_mode_type" => array('label' => __('Vacation Type', 'wc-frontend-manager') , 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'instant' => __( 'Instantly Close', 'wc-frontend-manager' ), 'date_wise' => __( 'Date wise close', 'wc-frontend-manager' ) ), 'value' => $wcfm_vacation_mode_type ),
																																																													"wcfm_vacation_start_date" => array('label' => __('From', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __( 'From', 'wc-frontend-manager' ) . ' ... YYYY-MM-DD', 'class' => 'wcfm-text wcfm_ele date_wise_vacation_ele', 'label_class' => 'wcfm_title wcfm_ele date_wise_vacation_ele', 'value' => $wcfm_vacation_start_date),
																																																													"wcfm_vacation_end_date" => array('label' => __('Upto', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __( 'To', 'wc-frontend-manager' ) . ' ... YYYY-MM-DD', 'class' => 'wcfm-text wcfm_ele date_wise_vacation_ele', 'label_class' => 'wcfm_title wcfm_ele date_wise_vacation_ele', 'value' => $wcfm_vacation_end_date),
																																																													"wcfm_vacation_mode_msg" => array('label' => __('Vacation Message', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfm_vacation_mode_msg )
																																																												 ), $user_id ) );
								?>
							</div>
						</div>
						<div class="wcfm_clearfix"></div>
					<?php } ?>
				<?php
				}
				?>
				<!-- end collapsible -->
				
				<?php do_action( 'end_wcfm_wcmarketplace_settings', $user_id ); ?>
			</div>
			
			<div id="wcfm_settings_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="save-data" value="<?php _e( 'Save', 'wc-frontend-manager' ); ?>" id="wcfm_settings_save_button" class="wcfm_submit_button" />
			</div>
			
		</form>
		<?php
		do_action( 'after_wcfm_wcmarketplace_settings' );
		?>
	</div>
</div>
<script type="text/javascript">
	var selected_state = '<?php echo $state; ?>';
	var input_selected_state = '<?php echo $state; ?>';
</script>