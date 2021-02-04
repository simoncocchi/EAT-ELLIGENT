<?php
/**
 * WCFM plugin view
 *
 * WCFM WC Vendors Settings View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view
 * @version   2.1.1
 */

global $WCFM;

$wcfm_is_allow_manage_settings = apply_filters( 'wcfm_is_allow_manage_settings', true );
if( !$wcfm_is_allow_manage_settings ) {
	wcfm_restriction_message_show( "Settings" );
	return;
}

$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );

$shop_name = get_user_meta( $user_id, 'pv_shop_name', true );
$logo = get_user_meta( $user_id, '_wcv_store_icon_id', true );

$paypal = get_user_meta( $user_id, 'pv_paypal', true );
$wcv_bank_account_name = get_user_meta( $user_id, 'wcv_bank_account_name', true );
$wcv_bank_account_number = get_user_meta( $user_id, 'wcv_bank_account_number', true );
$wcv_bank_name = get_user_meta( $user_id, 'wcv_bank_name', true );
$wcv_bank_routing_number = get_user_meta( $user_id, 'wcv_bank_routing_number', true );
$wcv_bank_iban = get_user_meta( $user_id, 'wcv_bank_iban', true );
$wcv_bank_bic_swift = get_user_meta( $user_id, 'wcv_bank_bic_swift', true );

$seller_info = get_user_meta( $user_id, 'pv_seller_info', true );
$shop_description = get_user_meta( $user_id, 'pv_shop_description', true );
$_wcv_company_url = get_user_meta( $user_id, '_wcv_company_url', true );
$_wcv_store_phone = get_user_meta( $user_id, '_wcv_store_phone', true );

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

$logo_image_url = wp_get_attachment_image_src( $logo, 'full' );
if ( !empty( $logo_image_url ) ) {
	$logo_image_url = $logo_image_url[0];
}

$wcfm_vacation_mode = ( get_user_meta( $user_id, 'wcfm_vacation_mode', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_mode', true ) : 'no';
$wcfm_disable_vacation_purchase = ( get_user_meta( $user_id, 'wcfm_disable_vacation_purchase', true ) ) ? get_user_meta( $user_id, 'wcfm_disable_vacation_purchase', true ) : 'no';
$wcfm_vacation_mode_type = ( get_user_meta( $user_id, 'wcfm_vacation_mode_type', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_mode_type', true ) : 'instant';
$wcfm_vacation_start_date = ( get_user_meta( $user_id, 'wcfm_vacation_start_date', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_start_date', true ) : '';
$wcfm_vacation_end_date = ( get_user_meta( $user_id, 'wcfm_vacation_end_date', true ) ) ? get_user_meta( $user_id, 'wcfm_vacation_end_date', true ) : '';
$wcfm_vacation_mode_msg = get_user_meta( $user_id, 'wcfm_vacation_mode_msg', true );

if( WCFM_Dependencies::wcvpro_plugin_active_check() ) {
	if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
		$banner = get_user_meta( $user_id, '_wcv_store_banner_id', true );
		$banner_image_url = wp_get_attachment_image_src( $banner, 'full' );
		if ( !empty( $banner_image_url ) ) {
			$banner_image_url = $banner_image_url[0];
		}
		
		$_wcv_company_url = get_user_meta( $user_id, '_wcv_company_url', true );
		$_wcv_store_phone = get_user_meta( $user_id, '_wcv_store_phone', true );
		
		$addr_1  = get_user_meta( $user_id, '_wcv_store_address1', true );
		$addr_2  = get_user_meta( $user_id, '_wcv_store_address2', true );
		$country  = get_user_meta( $user_id, '_wcv_store_country', true );
		$city  = get_user_meta( $user_id, '_wcv_store_city', true );
		$state  = get_user_meta( $user_id, '_wcv_store_state', true );
		$zip  = get_user_meta( $user_id, '_wcv_store_postcode', true );
		
		$wcv_shipping_rates = (array) get_user_meta( $user_id, '_wcv_shipping_rates', true );
		$wcv_shipping = (array) get_user_meta( $user_id, '_wcv_shipping', true );
		
		$national = ( isset( $wcv_shipping['national'] ) ) ? $wcv_shipping['national'] : '';
		$national_qty_override = ( isset( $wcv_shipping['national_qty_override'] ) ) ? $wcv_shipping['national_qty_override'] : '';
		$national_free = ( isset( $wcv_shipping['national_free'] ) ) ? $wcv_shipping['national_free'] : '';
		$national_disable = ( isset( $wcv_shipping['national_disable'] ) ) ? $wcv_shipping['national_disable'] : '';
		
		$international = ( isset( $wcv_shipping['international'] ) ) ? $wcv_shipping['international'] : '';
		$international_free = ( isset( $wcv_shipping['international_free'] ) ) ? $wcv_shipping['international_free'] : '';
		$international_qty_override = ( isset( $wcv_shipping['international_qty_override'] ) ) ? $wcv_shipping['international_qty_override'] : '';
		$international_disable = ( isset( $wcv_shipping['international_disable'] ) ) ? $wcv_shipping['international_disable'] : '';
		
		$max_charge = ( isset( $wcv_shipping['max_charge'] ) ) ? $wcv_shipping['max_charge'] : '';
		$min_charge = ( isset( $wcv_shipping['min_charge'] ) ) ? $wcv_shipping['min_charge'] : '';
		$free_shipping_order = ( isset( $wcv_shipping['free_shipping_order'] ) ) ? $wcv_shipping['free_shipping_order'] : '';
		$max_charge_product = ( isset( $wcv_shipping['max_charge_product'] ) ) ? $wcv_shipping['max_charge_product'] : '';
		$free_shipping_product = ( isset( $wcv_shipping['free_shipping_product'] ) ) ? $wcv_shipping['free_shipping_product'] : '';
		$product_handling_fee = ( isset( $wcv_shipping['product_handling_fee'] ) ) ? $wcv_shipping['product_handling_fee'] : '';
		$shipping_policy = ( isset( $wcv_shipping['shipping_policy'] ) ) ? $wcv_shipping['shipping_policy'] : '';
		$return_policy = ( isset( $wcv_shipping['return_policy'] ) ) ? $wcv_shipping['return_policy'] : '';
		
		$saddr_1  = ( isset( $wcv_shipping['shipping_address']['address1'] ) ) ? $wcv_shipping['shipping_address']['address1'] : '';
		$saddr_2  = ( isset( $wcv_shipping['shipping_address']['address2'] ) ) ? $wcv_shipping['shipping_address']['address2'] : '';
		$scountry  = ( isset( $wcv_shipping['shipping_address']['country'] ) ) ? $wcv_shipping['shipping_address']['country'] : '';
		$scity  = ( isset( $wcv_shipping['shipping_address']['city'] ) ) ? $wcv_shipping['shipping_address']['city'] : '';
		$sstate  = ( isset( $wcv_shipping['shipping_address']['state'] ) ) ? $wcv_shipping['shipping_address']['state'] : '';
		$szip  = ( isset( $wcv_shipping['shipping_address']['zip'] ) ) ? $wcv_shipping['shipping_address']['zip'] : '';
	}
}

$is_marketplace = wcfm_is_marketplace();
		
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
		
	  <?php do_action( 'before_wcfm_wcvendors_settings' ); ?>
		
	  <form id="wcfm_settings_form" class="wcfm">
	
			<?php do_action( 'begin_wcfm_wcvendors_settings_form' ); ?>
			
			<div class="wcfm-tabWrap">
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_dashboard_head">
					<label class="wcfmfa fa-shopping-bag"></label>
					<?php _e('Store', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_store_expander" class="wcfm-content">
						<?php
							$wcvendors_settings_fields_general = apply_filters( 'wcfm_wcvendors_settings_fields_general', array(
																																																"wcfm_logo" => array('label' => __('Logo', 'wc-frontend-manager') , 'type' => 'upload', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 150, 'value' => $logo_image_url ),
																																																"shop_name" => array('label' => __('Shop Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $shop_name, 'hints' => __( 'Your shop name is public and must be unique.', 'wc-frontend-manager' ) ),
																																																"seller_info" => array('label' => __('Seller Info', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $seller_info, 'hints' => __( 'This is displayed on each of your products.', 'wc-frontend-manager' ) ),
																																																"shop_description" => array('label' => __('Shop Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele ' . $rich_editor, 'label_class' => 'wcfm_title', 'value' => $shop_description, 'hints' => __( 'This is displayed on your shop page.', 'wc-frontend-manager' ) ),
																																																), $user_id );
							
							if( !apply_filters( 'wcfm_is_allow_store_logo', true ) ) {
								if( isset( $wcvendors_settings_fields_general['wcfm_logo'] ) ) { unset( $wcvendors_settings_fields_general['wcfm_logo'] ); }
							}
							
							if( !apply_filters( 'wcfm_is_allow_store_name', true ) ) {
								if( isset( $wcvendors_settings_fields_general['shop_name'] ) ) { unset( $wcvendors_settings_fields_general['shop_name'] ); }
							}
							
							if( !apply_filters( 'wcfm_is_allow_store_description', true ) ) {
								if( isset( $wcvendors_settings_fields_general['seller_info'] ) ) { unset( $wcvendors_settings_fields_general['seller_info'] ); }
								if( isset( $wcvendors_settings_fields_general['shop_description'] ) ) { unset( $wcvendors_settings_fields_general['shop_description'] ); }
							}
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( $wcvendors_settings_fields_general );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<?php if( WCFM_Dependencies::wcvpro_plugin_active_check() ) { ?>
					<?php if( apply_filters( 'wcfm_is_allow_brand_settings', true ) ) { ?>
						<div class="page_collapsible" id="wcfm_settings_form_identity_head">
							<label class="wcfmfa fa-id-card-o"></label>
							<?php _e('Branding', 'wc-frontend-manager'); ?><span></span>
						</div>
						<div class="wcfm-container">
							<div id="wcfm_settings_form_identity_expander" class="wcfm-content">
								<?php
								// WC Vendors Pro Settings
								if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									 $settings_fields_identity = apply_filters( 'wcfm_wcvendors_settings_fields_identity', array(
																																								"banner" => array('label' => __('Banner', 'wc-frontend-manager') , 'type' => 'upload', 'class' => 'wcfm-text wcfm-banner-uploads wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 250, 'value' => $banner_image_url ),
																																								"_wcv_company_url" => array('label' => __('Store Website / Blog URL', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $_wcv_company_url, 'hints' => __( 'Your company/blog URL here.', 'wc-frontend-manager' ) ),
																																								"_wcv_store_phone" => array('label' => __('Store Phone', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $_wcv_store_phone, 'hints' => __( 'This is your store contact number.', 'wc-frontend-manager' ) ),
																																								), $user_id );
									 
									 if( !apply_filters( 'wcfm_is_allow_store_banner', true ) ) {
											if( isset( $settings_fields_identity['banner'] ) ) { unset( $settings_fields_identity['banner'] ); }
										}
										
										if( !apply_filters( 'wcfm_is_allow_store_phone', true ) ) {
											if( isset( $settings_fields_identity['_wcv_store_phone'] ) ) { unset( $settings_fields_identity['_wcv_store_phone'] ); }
										}
						
									 $WCFM->wcfm_fields->wcfm_generate_form_field( $settings_fields_identity );
									 
									 if( apply_filters( 'wcfm_is_allow_store_address', true ) ) {
									 ?>
								
										<div class="wcfm_clearfix"></div>
										
										<div class="wcfm_vendor_settings_heading"><h3><?php _e( 'Store Address', 'wc-frontend-manager' ); ?></h3></div>
										<div class="store_address">
											<?php
												$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcvendors_settings_fields_address', array(
																																																					"addr_1" => array('label' => __('Address 1', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $addr_1 ),
																																																					"addr_2" => array('label' => __('Address 2', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $addr_2 ),
																																																					"country" => array('label' => __('Country', 'wc-frontend-manager') , 'type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'style' => 'width: 60%;' ), 'value' => $country ),
																																																					"city" => array('label' => __('City/Town', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $city ),
																																																					"state" => array('label' => __('State/County', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $state ),
																																																					"zip" => array('label' => __('Postcode/Zip', 'wc-frontend-manager') , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $zip, 'attributes' => array( 'min' => '1', 'step'=> '1' ) ),
																																																					), $user_id ) );
											?>
									</div>
								<?php
									 }
								} else {
									//if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
										wcfmu_feature_help_text_show( __( 'WCV Pro Settings', 'wc-frontend-manager' ) );
									//}
								}
								?>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<?php if( $wcfm_is_allow_billing_settings = apply_filters( 'wcfm_is_allow_billing_settings', true ) ) { ?>
					<div class="page_collapsible" id="wcfm_settings_form_payment_head">
						<label class="wcfmfa fa-money fa-money-bill-alt"></label>
						<?php _e('Payment', 'wc-frontend-manager'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="wcfm_settings_form_payment_expander" class="wcfm-content">
							<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcvendors_settings_fields_billing', array(
																																															"paypal" => array('label' => __('PayPal Email', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $paypal, 'hints' => __( 'Your PayPal address is used to send you your commission.', 'wc-frontend-manager' ) ),
																																															), $user_id ) );
							
							if ( apply_filters( 'wcfm_is_allow_billing_bank_settings', true ) && apply_filters( 'wcvendors_vendor_dashboard_bank_details_enable', true ) ) {
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcvendors_settings_fields_billing_bank', array(
																																															"wcv_bank_account_name" => array('label' => __('Account Name', 'wc-vendors') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcv_bank_account_name ),
																																															"wcv_bank_account_number" => array('label' => __('Account Number', 'wc-vendors') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcv_bank_account_number ),
																																															"wcv_bank_name" => array('label' => __('Bank Name', 'wc-vendors') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcv_bank_name ),
																																															"wcv_bank_routing_number" => array('label' => __('Routing Number', 'wc-vendors') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcv_bank_routing_number ),
																																															"wcv_bank_iban" => array('label' => __('IBAN', 'wc-vendors') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcv_bank_iban ),
																																															"wcv_bank_bic_swift" => array('label' => __('BIC / Swift', 'wc-vendors') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcv_bank_bic_swift ),
																																															), $user_id ) );
							}
							
							// For Stripe Connect
							if( WCFM_Dependencies::wcfm_wcv_stripe_plugin_active_check() && apply_filters( 'wcfm_is_allow_billing_stripe', true ) ) {
								do_action( 'wcvendors_settings_after_paypal', $user_id );
								//$stripe_access_key = get_user_meta( get_current_user_id(), '_stripe_connect_access_key', true );
								//$stripe_user_id = get_user_meta( get_current_user_id(), '_stripe_connect_user_id', true );
							}
							
							if( apply_filters( 'wcfm_is_allow_billing_mangopay_settings', true ) ) {
								if( WCFM_Dependencies::wcfm_wc_mangopay_plugin_active_check() ) {
									$account_types = array( '' => '&nbsp;&nbsp;', 'IBAN' => 'IBAN', 'GB' => 'GB', 'US' => 'US', 'CA' => 'CA', 'OTHER' => 'OTHER' );
									
									$vendor_account_type = get_user_meta( $user_id, 'vendor_account_type', true );
									
									$vendor_iban = get_user_meta( $user_id, 'vendor_iban', true );
									$vendor_bic = get_user_meta( $user_id, 'vendor_bic', true );
									
									$vendor_gb_accountnumber = get_user_meta( $user_id, 'vendor_gb_accountnumber', true );
									$vendor_gb_sortcode = get_user_meta( $user_id, 'venvendor_gb_sortcodedor_account_type', true );
									
									$vendor_us_accountnumber = get_user_meta( $user_id, 'vendor_us_accountnumber', true );
									$vendor_us_aba = get_user_meta( $user_id, 'vendor_us_aba', true );
									$vendor_us_datype = get_user_meta( $user_id, 'vendor_us_datype', true );
									
									$vendor_ca_bankname = get_user_meta( $user_id, 'vendor_ca_bankname', true );
									$vendor_ca_instnumber = get_user_meta( $user_id, 'vendor_ca_instnumber', true );
									$vendor_ca_branchcode = get_user_meta( $user_id, 'vendor_ca_branchcode', true );
									$vendor_ca_accountnumber = get_user_meta( $user_id, 'vendor_ca_accountnumber', true );
									
									$vendor_ot_country = get_user_meta( $user_id, 'vendor_ot_country', true );
									$vendor_ot_bic = get_user_meta( $user_id, 'vendor_ot_bic', true );
									$vendor_ot_accountnumber = get_user_meta( $user_id, 'vendor_ot_accountnumber', true );
									
									$vendor_account_name = get_user_meta( $user_id, 'vendor_account_name', true );
									$vendor_account_address1 = get_user_meta( $user_id, 'vendor_account_address1', true );
									$vendor_account_address2 = get_user_meta( $user_id, 'vendor_account_address2', true );
									$vendor_account_city = get_user_meta( $user_id, 'vendor_account_city', true );
									$vendor_account_postcode = get_user_meta( $user_id, 'vendor_account_postcode', true );
									$vendor_account_country = get_user_meta( $user_id, 'vendor_account_country', true );
									$vendor_account_region = get_user_meta( $user_id, 'vendor_account_region', true );
									
									?>
									<h2><?php _e( 'Bank Payment (Mangopay)', 'wc-frontend-manager' ); ?></h2>
									<?php
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcvendors_settings_fields_billing_mangopay', array(
																																			"vendor_account_type" => array('label' => __('Account Type', 'wc-frontend-manager') , 'type' => 'select', 'options' => $account_types, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_type ),
																																			
																																			"vendor_iban" => array('label' => __( 'IBAN', 'mangopay') , 'type' => 'text', 'placeholder'	=> '____ ____ ____ ____ ____', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_IBAN', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_IBAN', 'value' => $vendor_iban ),
																																			"vendor_bic" => array('label' => __( 'BIC', 'mangopay') , 'type' => 'text', 'placeholder'	=> '___________', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_IBAN', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_IBAN', 'value' => $vendor_bic ),
																																			
																																			"vendor_gb_accountnumber" => array('label' => __( 'Account Number', 'mangopay') , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_GB', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_GB', 'value' => $vendor_gb_accountnumber ),
																																			"vendor_gb_sortcode" => array('label' => __( 'Sort Code', 'mangopay') , 'type' => 'text', 'placeholder'	=> '______', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_GB', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_GB', 'value' => $vendor_gb_sortcode ),
																																			
																																			"vendor_us_accountnumber" => array('label' => __( 'Account Number', 'mangopay') , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_US', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_US', 'value' => $vendor_us_accountnumber ),
																																			"vendor_us_aba" => array('label' => __( 'ABA', 'mangopay') , 'type' => 'text', 'placeholder'	=> '_________', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_US', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_US', 'value' => $vendor_us_aba ),
																																			"vendor_us_datype" => array('label' => __( 'Deposit Account Type', 'mangopay') , 'type' => 'select', 'custom_attributes' => array( 'required' => 1 ), 'options' => array( 'CHECKING' => __( 'CHECKING', 'wc-frontend-manager'), 'SAVINGS' => __( 'SAVINGS', 'wc-frontend-manager') ), 'class' => 'wcfm-select wcfm_ele mangopay_acc_type mangopay_acc_type_US', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_US', 'value' => $vendor_us_datype ),
																																			
																																			"vendor_ca_bankname" => array('label' => __( 'Bank Name', 'mangopay') , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'value' => $vendor_ca_bankname ),
																																			"vendor_ca_instnumber" => array('label' => __( 'Institution Number', 'mangopay') , 'type' => 'text', 'placeholder'	=> '____', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'value' => $vendor_ca_instnumber ),
																																			"vendor_ca_branchcode" => array('label' => __( 'Branch Code', 'mangopay') , 'type' => 'text', 'placeholder'	=> '_____', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'value' => $vendor_ca_branchcode ),
																																			"vendor_ca_accountnumber" => array('label' => __( 'Account Number', 'mangopay') , 'type' => 'text', 'placeholder'	=> '____ ____ ____ ____ ____', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_CA', 'value' => $vendor_ca_accountnumber ),
																																			
																																			"vendor_ot_country" => array('label' => __( 'Country', 'mangopay') , 'type' => 'country', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_ot_country ),
																																			"vendor_ot_bic" => array('label' => __( 'BIC', 'mangopay') , 'type' => 'text', 'placeholder'	=> '___________', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_OTHER', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_OTHER', 'value' => $vendor_ot_bic ),
																																			"vendor_ot_accountnumber" => array('label' => __( 'Account Number', 'mangopay') , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele mangopay_acc_type mangopay_acc_type_OTHER', 'label_class' => 'wcfm_title wcfm_ele mangopay_acc_type mangopay_acc_type_OTHER', 'value' => $vendor_ot_accountnumber ),
																																			
																																			"vendor_account_name" => array('label' => __( 'Account holder&apos;s name', 'mangopay' ) , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_name ),
																																			"vendor_account_address1" => array('label' => __( 'Account holder&apos;s address', 'mangopay' ) , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_address1 ),
																																			"vendor_account_address2" => array('label' => __( 'Account holder&apos;s address', 'mangopay' ) , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_address2 ),
																																			"vendor_account_city" => array('label' => __( 'Account holder&apos;s city', 'mangopay' ) , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_city ),
																																			"vendor_account_postcode" => array('label' => __( 'Account holder&apos;s postal code', 'mangopay' ) , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_postcode ),
																																			"vendor_account_country" => array('label' => __( 'Account holder&apos;s country', 'mangopay' ) , 'type' => 'country', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_country ),
																																			"vendor_account_region" => array('label' => __( 'Account holder&apos;s region', 'mangopay' ) , 'type' => 'text', 'custom_attributes' => array( 'required' => 1 ), 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_account_region ),
																																			), $user_id ) );
								}
							}
							
							do_action( 'wcvendors_end_settings_billing', $user_id );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
				<?php } ?>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<?php if( WCFM_Dependencies::wcvpro_plugin_active_check() ) { ?>
					<?php if( apply_filters( 'wcfm_is_allow_shipping', true ) && apply_filters( 'wcfm_is_allow_vshipping_settings', true ) ) { ?>
						<div class="page_collapsible" id="wcfm_settings_form_shipping_head">
							<label class="wcfmfa fa-truck"></label>
							<?php _e('Shipping', 'wc-frontend-manager'); ?><span></span>
						</div>
						<div class="wcfm-container">
							<div id="wcfm_settings_form_shipping_expander" class="wcfm-content">
								<?php
								// WC Vendors Pro Settings
								if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
										$shipping_disabled	= wc_string_to_bool( WCVendors_Pro::get_option( 'shipping_management_cap' ) );
									} else {
										$shipping_disabled	= wc_string_to_bool( get_option( 'wcvendors_shipping_management_cap', 'no' ) );
									}
									if( !$shipping_disabled ) {
										$shipping_settings 		= get_option( 'woocommerce_wcv_pro_vendor_shipping_settings' ); 
										$store_shipping_type	= get_user_meta( $user_id, '_wcv_shipping_type', true ); 
										$shipping_type 			= ( $store_shipping_type != '' ) ? $store_shipping_type : $shipping_settings[ 'shipping_system' ]; 
										
										if ( $shipping_type == 'flat' ) { 
											
											$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_shipping_national_flat', array(
																																									"national" => array('label' => __('Default National Shipping Fee', 'wcvendors-pro'), 'placeholder' => '0', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $national, 'hints' => __('The default shipping fee within your country, this can be overridden on a per product basis.', 'wcvendors-pro'), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"national_free" => array('label' => __( 'Free national shipping', 'wcvendors-pro' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $national_free, 'hints' => __( 'Free national shipping.', 'wcvendors-pro' ) ),
																																									"national_qty_override" => array('label' => __( 'Charge once per product', 'wc-frontend-manager' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $national_qty_override, 'hints' => __( 'Charge once per product for national shipping, even if more than one is purchased.', 'wcvendors-pro' ) ),
																																									"national_disable" => array('label' => __( 'Disable national shipping', 'wcvendors-pro' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $national_disable, 'hints' => __( 'Disable national shipping.', 'wcvendors-pro' ) ),
																																								 ), $user_id ) );
											
											$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_shipping_international_flat', array(
																																									"international" => array('label' => __( 'Default International Shipping Fee', 'wcvendors-pro' ), 'placeholder' => '0', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $international, 'hints' => __( 'The default shipping fee outside your country, this can be overridden on a per product basis. ', 'wcvendors-pro' ), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"international_free" => array('label' => __( 'Free international shipping', 'wcvendors-pro' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $international_free, 'hints' => __( 'Free international shipping.', 'wcvendors-pro' ) ),
																																									"international_qty_override" => array('label' => __( 'Charge once per product', 'wc-frontend-manager' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $international_qty_override, 'hints' => __( 'Charge once per product for international shipping, even if more than one is purchased.', 'wcvendors-pro' ) ),
																																									"international_disable" => array('label' => __( 'Disable international shipping', 'wcvendors-pro' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $international_disable, 'hints' => __( 'Disable international shipping.', 'wcvendors-pro' ) ),
																																								 ), $user_id ) );
											
										} else {
											$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_shipping_rates', array( 
																																									"_wcv_shipping_rates"      => array('label' => __('Shipping Rates', 'wc-frontend-manager') , 'type' => 'multiinput', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'value' => $wcv_shipping_rates, 'options' => array(
																																																											"country" => array('label' => __('Country', 'wc-frontend-manager'), 'type' => 'country', 'class' => 'wcfm-select', 'label_class' => 'wcfm_title' ),
																																																											"state" => array( 'label' => __('State', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title' ),
																																																											"postcode" => array('label' => __('Postcode', 'wc-frontend-manager'), 'type' => 'number', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title' ),
																																																											"fee" => array('label' => __('Shipping Fee', 'wc-frontend-manager'), 'type' => 'number', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																																											"qty_override" => array('label' => __('Override Qty', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes' ),
																																																											)	)								
																																									), $user_id ) );
										}
										
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_shipping', array(
																																									"min_charge" => array('label' => __('Min Charge Order', 'wc-frontend-manager'), 'placeholder' => '0', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $min_charge, 'hints' => __('The minimum shipping fee charged for an order.', 'wc-frontend-manager'), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"max_charge" => array('label' => __('Max Charge Order', 'wc-frontend-manager'), 'placeholder' => '0', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $max_charge, 'hints' => __('The maximum shipping fee charged for an order.', 'wc-frontend-manager'), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"free_shipping_order" => array('label' => __('Free Shipping Order', 'wc-frontend-manager'), 'placeholder' => '0', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $free_shipping_order, 'hints' => __('Free shipping for order spends over this amount. This will override the max shipping charge above.', 'wc-frontend-manager'), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"max_charge_product" => array('label' => __('Max Charge Product', 'wc-frontend-manager'), 'placeholder' => '0', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $max_charge_product, 'hints' => __('The maximum shipping charged per product no matter the quantity.', 'wc-frontend-manager'), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"free_shipping_product" => array('label' => __('Free Shipping Product', 'wc-frontend-manager'), 'placeholder' => '0', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $free_shipping_product, 'hints' => __('Free shipping if the spend per product is over this amount. This will override the max shipping charge above.', 'wc-frontend-manager'), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"product_handling_fee" => array('label' => __('Product handling fee', 'wc-frontend-manager'), 'placeholder' => __( 'Leave empty to disable', 'wc-frontend-manager' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $product_handling_fee, 'hints' => __('The product handling fee, this can be overridden on a per product basis. Amount (5.00) or Percentage (5%).', 'wc-frontend-manager'), 'attributes' => array( 'min' => '0.01', 'step' => '0.01' ) ),
																																									"shipping_policy" => array('label' => __('Shipping Policy', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $shipping_policy ),
																																									"return_policy" => array('label' => __('Refund Policy', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $return_policy ),
																																								 ), $user_id ) );
									}
									?>
									<div class="wcfm_clearfix"></div>
									<div class="wcfm_vendor_settings_heading"><h3><?php _e( 'From Address', 'wc-frontend-manager' ); ?></h3></div>
									<div class="store_address">
										<?php
											$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcvendors_settings_fields_shipping_address', array(
																																									"saddr_1" => array('label' => __('Address 1', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $saddr_1 ),
																																									"saddr_2" => array('label' => __('Address 2', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $saddr_2 ),
																																									"scountry" => array('label' => __('Country', 'wc-frontend-manager') , 'type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'style' => 'width: 60%;' ), 'value' => $scountry ),
																																									"scity" => array('label' => __('City/Town', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $scity ),
																																									"sstate" => array('label' => __('State/County', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $sstate ),
																																									"szip" => array('label' => __('Postcode/Zip', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $szip ),
																																									), $user_id ) );
										?>
									</div>
									<?php
								} else {
									if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
										wcfmu_feature_help_text_show( __( 'WCV Pro Settings', 'wc-frontend-manager' ) );
									}
								}
								?>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
				<!-- end collapsible -->
				
				<?php do_action( 'end_wcfm_vendor_settings', $user_id ); ?>
			
				<!-- collapsible -->
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
				<?php } ?>
				<!-- end collapsible -->
				
				<?php do_action( 'end_wcfm_wcvendors_settings', $user_id ); ?>
			</div>
			
			<div id="wcfm_settings_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="save-data" value="<?php _e( 'Save', 'wc-frontend-manager' ); ?>" id="wcfm_settings_save_button" class="wcfm_submit_button" />
			</div>
			
		</form>
		<?php
		do_action( 'after_wcfm_wcvendors_settings' );
		?>
	</div>
</div>