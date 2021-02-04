<?php
/**
 * WCFM plugin view
 *
 * WCFM WC Product Vendors Settings View
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

$user_id = apply_filters( 'wcfm_current_vendor_id', WC_Product_Vendors_Utils::get_logged_in_vendor() );
$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_from_user();

// logo image
$logo = ! empty( $vendor_data['logo'] ) ? $vendor_data['logo'] : '';

$logo_image_url = wp_get_attachment_image_src( $logo, 'full' );

if ( !empty( $logo_image_url ) ) {
	$logo_image_url = $logo_image_url[0];
}

$vendor_term = get_term( WC_Product_Vendors_Utils::get_logged_in_vendor(), WC_PRODUCT_VENDORS_TAXONOMY );

$shop_name         = ! empty( $vendor_data['shop_name'] ) ? $vendor_data['shop_name'] : $vendor_term->name;
$profile           = ! empty( $vendor_data['profile'] ) ? $vendor_data['profile'] : '';
$email             = ! empty( $vendor_data['email'] ) ? $vendor_data['email'] : '';
$paypal            = ! empty( $vendor_data['paypal'] ) ? $vendor_data['paypal'] : '';
$vendor_commission = ! empty( $vendor_data['commission'] ) ? $vendor_data['commission'] : get_option( 'wcpv_vendor_settings_default_commission', '0' );
$tzstring          = ! empty( $vendor_data['timezone'] ) ? $vendor_data['timezone'] : '';
$wcfm_vacation_mode = isset( $vendor_data['wcfm_vacation_mode'] ) ? $vendor_data['wcfm_vacation_mode'] : 'no';
$wcfm_disable_vacation_purchase = isset( $vendor_data['wcfm_disable_vacation_purchase'] ) ? $vendor_data['wcfm_disable_vacation_purchase'] : 'no';
$wcfm_vacation_mode_type = isset( $vendor_data['wcfm_vacation_mode_type'] ) ? $vendor_data['wcfm_vacation_mode_type'] : 'instant';
$wcfm_vacation_start_date = isset( $vendor_data['wcfm_vacation_start_date'] ) ? $vendor_data['wcfm_vacation_start_date'] : '';
$wcfm_vacation_end_date = isset( $vendor_data['wcfm_vacation_end_date'] ) ? $vendor_data['wcfm_vacation_end_date'] : '';
$wcfm_vacation_mode_msg = ! empty( $vendor_data['wcfm_vacation_mode_msg'] ) ? $vendor_data['wcfm_vacation_mode_msg'] : '';

$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
$wpeditor = apply_filters( 'wcfm_is_allow_settings_wpeditor', 'wpeditor' );
if( $wpeditor && $rich_editor ) {
	$rich_editor = 'wcfm_wpeditor';
} else {
	$wpeditor = 'textarea';
}
if( !$rich_editor ) {
	$breaks = array("<br />","<br>","<br/>"); 
	
	$profile = str_ireplace( $breaks, "\r\n", $profile );
	$profile = strip_tags( $profile );
}

$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );

if ( empty( $tzstring ) ) {
	$tzstring = WC_Product_Vendors_Utils::get_default_timezone_string();
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
	  
		<?php do_action( 'before_wcfm_wcpvendors_settings' ); ?>
		
		<form id="wcfm_settings_form" class="wcfm">
	
			<?php do_action( 'begin_wcfm_wcpvendors_settings_form' ); ?>
			
			<div class="wcfm-tabWrap">
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_dashboard_head">
					<label class="wcfmfa fa-shopping-bag"></label>
				  <?php _e('Store Settings', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_style_expander" class="wcfm-content">
						<?php
							$settings_fields_store = apply_filters( 'wcfm_wcpvendors_settings_fields_store', array(
																																																"wcfm_logo" => array('label' => __('Logo', 'wc-frontend-manager') , 'type' => 'upload', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 150, 'value' => $logo_image_url),
																																																"shop_name" => array('label' => __('Shop Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $shop_name, 'hints' => __( 'Your shop name is public and must be unique.', 'wc-frontend-manager' ) ),
																																																"email" => array('label' => __('Vendor Email', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $email, 'hints' => __( 'Enter the email for this vendor. This is the email where all notifications will be send such as new orders and customer inquiries. You may enter more than one email separating each with a comma.', 'wc-frontend-manager' ) ),
																																																"shop_description" => array('label' => __('Profile', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele ' . $rich_editor, 'label_class' => 'wcfm_title', 'value' => $profile, 'hints' => __( 'Enter the profile information you would like for customer to see.', 'wc-frontend-manager' ) ),
																																																) );
																																																
							if( !apply_filters( 'wcfm_is_allow_store_logo', true ) ) {
								if( isset( $settings_fields_store['wcfm_logo'] ) ) { unset( $settings_fields_store['wcfm_logo'] ); }
							}
							
							if( !apply_filters( 'wcfm_is_allow_store_name', true ) ) {
								if( isset( $settings_fields_store['shop_name'] ) ) { unset( $settings_fields_store['shop_name'] ); }
							}
							
							if( !apply_filters( 'wcfm_is_allow_store_description', true ) ) {
								if( isset( $settings_fields_store['shop_description'] ) ) { unset( $settings_fields_store['shop_description'] ); }
							}
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( $settings_fields_store );
						?>
						<br />
						<p class="tzstring wcfm_title wcfm_ele"><strong><?php _e('Timezone', 'wc-frontend-manager'); ?></strong><span class="img_tip" data-tip="<?php _e('Set the local timezone.', 'wc-frontend-manager'); ?>" data-hasqtip="4"></span></p>
						<label class="screen-reader-text" for="tzstring"><?php _e('Timezone', 'wc-frontend-manager'); ?></label>
						<select id="timezone" name="timezone" class="wcfm-select wcfm_ele" style="width: 60%;">
							<?php echo wp_timezone_choice( $tzstring ); ?>
						</select>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<!-- collapsible -->
				<?php if( apply_filters( 'wcfm_is_pref_vendor_vacation', true ) && apply_filters( 'wcfm_is_allow_vacation_settings', true ) ) { ?>
					<div class="page_collapsible" id="wcfm_settings_form_vacation_head">
						<label class="wcfmfa fa-tripadvisor"></label>
						<?php _e('Vacation Mode', 'wc-frontend-manager'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="wcfm_settings_form_vacation_expander" class="wcfm-content">
							<?php
							if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_vacation', array(
																																																													"wcfm_vacation_mode" => array('label' => __('Enable Vacation Mode', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $wcfm_vacation_mode ),
																																																													"wcfm_disable_vacation_purchase" => array('label' => __('Disable Purchase During Vacation', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $wcfm_disable_vacation_purchase ),
																																																													"wcfm_vacation_mode_type" => array('label' => __('Vacation Type', 'wc-frontend-manager') , 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'instant' => __( 'Instantly Close', 'wc-frontend-manager' ), 'date_wise' => __( 'Date wise close', 'wc-frontend-manager' ) ), 'value' => $wcfm_vacation_mode_type ),
																																																													"wcfm_vacation_start_date" => array('label' => __('From', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __( 'From', 'wc-frontend-manager' ) . ' ... YYYY-MM-DD', 'class' => 'wcfm-text wcfm_ele date_wise_vacation_ele', 'label_class' => 'wcfm_title wcfm_ele date_wise_vacation_ele', 'value' => $wcfm_vacation_start_date),
																																																													"wcfm_vacation_end_date" => array('label' => __('Upto', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __( 'To', 'wc-frontend-manager' ) . ' ... YYYY-MM-DD', 'class' => 'wcfm-text wcfm_ele date_wise_vacation_ele', 'label_class' => 'wcfm_title wcfm_ele date_wise_vacation_ele', 'value' => $wcfm_vacation_end_date),
																																																													"wcfm_vacation_mode_msg" => array('label' => __('Vacation Message', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfm_vacation_mode_msg )
																																																												 ) ) );
							} else {
								//if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
									wcfmu_feature_help_text_show( __( 'Vacation Mode', 'wc-frontend-manager' ) );
								//}
							}
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
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
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wcpvendors_settings_fields_billing', array(
																																															"paypal" => array('label' => __('Paypal Email', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $paypal, 'hints' => __( 'PayPal email account where you will receive your commission.', 'wc-frontend-manager' ) ),
																																															"vendor_commission" => array('label' => __('Commission', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'disabled' => 'disabled' ), 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_commission, 'hints' => __( 'Default commission you will receive per product sale. Please note product level commission can override this. Check your product to confirm.', 'wc-frontend-manager' ) ),
																																															) ) );
							do_action( 'wcfm_wcpvendors_billing_settings_fields', $user_id );
							?>
											<div class="paymode_field paymode_stripe_masspay">
							  <?php
							  if( WCFM_Dependencies::wcfm_wcmp_stripe_connect_active_check() && apply_filters( 'wcfm_is_allow_billing_stripe', true ) ) {
									global $WCMp_Stripe_Gateway;
									//$vendor = get_wcmp_vendor($user_id);
									//if ($vendor) {
										$stripe_settings = get_option('woocommerce_stripe_settings');
										if (isset($stripe_settings) && !empty($stripe_settings)) {
											if (isset($stripe_settings['enabled']) && $stripe_settings['enabled'] == 'yes') {
												$testmode = $stripe_settings['testmode'] === "yes" ? true : false;
												$client_id = $testmode ? get_wcmp_stripe_gateway_settings('test_client_id', 'payment', 'stripe_gateway') : get_wcmp_stripe_gateway_settings('live_client_id', 'payment', 'stripe_gateway');
												$secret_key = $testmode ? $stripe_settings['test_secret_key'] : $stripe_settings['secret_key'];
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
																update_user_meta($user_id, 'vendor_connected', 1);
																update_user_meta($user_id, 'admin_client_id', $client_id);
																update_user_meta($user_id, 'access_token', $resp['access_token']);
																update_user_meta($user_id, 'refresh_token', $resp['refresh_token']);
																update_user_meta($user_id, 'stripe_publishable_key', $resp['stripe_publishable_key']);
																update_user_meta($user_id, 'stripe_user_id', $resp['stripe_user_id']);
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
																				<th>
																					<label><?php _e('Stripe', 'saved-cards'); ?></label>
																				</th>
																				<td>
																					<label><?php _e('You are connected with Stripe', 'saved-cards'); ?></label>
																				</td>
																			</tr>
																			<tr>
																				<th></th>
																				<td>
																					<input type="submit" class="button" name="disconnect_stripe" value="Disconnect Stripe Account" />
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
																				<th>
																					<label><?php _e('Stripe', 'saved-cards'); ?></label>
																				</th>
																				<td>
																					<label><?php _e('Please Retry!!!', 'saved-cards'); ?></label>
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
																	<th>
																		<label><?php _e('Stripe', 'saved-cards'); ?></label>
																	</th>
																	<td>
																		<label><?php _e('Please Retry!!!', 'saved-cards'); ?></label>
																	</td>
																</tr>
															</tbody>
														</table>
													</div>
													<?php
												} else {
													
													if (isset($_GET['disconnect_stripe'])) {
														//$vendor = get_wcmp_vendor($user_id);
														$stripe_settings = get_option('woocommerce_stripe_settings');
														$stripe_user_id = get_user_meta($user_id, 'stripe_user_id', true);
														if (isset($stripe_settings['enabled']) && $stripe_settings['enabled'] == 'no' && empty($stripe_user_id)) {
																return;
														}
														$testmode = $stripe_settings['testmode'] === "yes" ? true : false;
														$client_id = $testmode ? get_wcmp_stripe_gateway_settings('test_client_id', 'payment', 'stripe_gateway') : get_wcmp_stripe_gateway_settings('live_client_id', 'payment', 'stripe_gateway');
														$secret_key = $testmode ? $stripe_settings['test_secret_key'] : $stripe_settings['secret_key'];
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
														if ( !is_wp_error( $response ) ) {
															$resp = (array) json_decode( $response['body'] );
															if ( ( isset($resp['error']) && ( $resp['error'] == 'invalid_client' ) )  || isset( $resp['stripe_user_id'] ) ) {
																delete_user_meta($user_id, 'vendor_connected');
																delete_user_meta($user_id, 'admin_client_id');
																delete_user_meta($user_id, 'access_token');
																delete_user_meta($user_id, 'refresh_token');
																delete_user_meta($user_id, 'stripe_publishable_key');
																delete_user_meta($user_id, 'stripe_user_id');
																//wc_add_notice(__('Your account has been disconnected', 'marketplace-stripe-gateway'), 'success');
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
																			<th>
																					<label><?php _e('Stripe', 'saved-cards'); ?></label>
																			</th>
																			<td>
																					<label><?php _e('You are connected with Stripe', 'saved-cards'); ?></label>
																			</td>
																		</tr>
																		<tr>
																			<th></th>
																			<td>
																					<input type="submit" class="button" name="disconnect_stripe" value="Disconnect Stripe Account" />
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
															'state' => $user_id
														);
														$url = 'https://connect.stripe.com/oauth/authorize?' . http_build_query($authorize_request_body);
														$stripe_connect_url = $WCMp_Stripe_Gateway->plugin_url . 'assets/images/blue-on-light.png';
		
														if (!$status) {
															?>
															<div class="clear"></div>
															<div class="wcmp_stripe_connect">
																<table class="form-table">
																	<tbody>
																		<tr>
																			<th>
																				<label><?php _e('Stripe', 'saved-cards'); ?></label>
																			</th>
																			<td><?php _e('You are not connected with stripe.', 'saved-cards'); ?></td>
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
																				<th>
																					<label><?php _e('Stripe', 'saved-cards'); ?></label>
																				</th>
																				<td><?php _e('Please connected with stripe again.', 'saved-cards'); ?></td>
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
									//}
								}
							  ?>
							</div>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
				<?php } ?>
				<!-- end collapsible -->
				
				<?php do_action( 'end_wcfm_vendor_settings', $user_id ); ?>
				
		    <?php do_action( 'end_wcfm_wcpvendors_settings', $vendor_data ); ?>
		  </div>
			
			<div id="wcfm_settings_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="save-data" value="<?php _e( 'Save', 'wc-frontend-manager' ); ?>" id="wcfm_settings_save_button" class="wcfm_submit_button" />
			</div>
			
		
		</form>
		<?php
		do_action( 'after_wcfm_wcpvendors_settings' );
		?>
	</div>
</div>