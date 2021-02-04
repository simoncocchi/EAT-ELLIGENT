<?php
/**
 * WCFM plugin view
 *
 * WCFMvm Memberships Template
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/templates
 * @version   1.0.0
 */

global $WCFM, $WCFMvm;

$member_id = get_current_user_id();

$member_info = wp_get_current_user();

$user_email = $member_info->user_email;
$first_name = $member_info->user_firstname;
$last_name  = $member_info->user_lastname;

$membership_id = get_user_meta( $member_id, 'temp_wcfm_membership', true );

if( !$membership_id ) return;

$membership_post = get_post( $membership_id );
$title = $membership_post->post_title;
$description = $membership_post->post_excerpt;

$features = (array) get_post_meta( $membership_id, 'features', true );
		
$subscription = (array) get_post_meta( $membership_id, 'subscription', true );
$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';

$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
$membership_feature_lists = array();
if( isset( $wcfm_membership_options['membership_features'] ) ) $membership_feature_lists = $wcfm_membership_options['membership_features'];

$membership_payment_settings = array();
if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
$payment_methods = array( 'paypal' );
if( isset( $membership_payment_settings['payment_methods'] ) ) $payment_methods = $membership_payment_settings['payment_methods'];
$paypal_email = ( $membership_payment_settings['paypal_email'] ) ? $membership_payment_settings['paypal_email'] : '';
$paypal_sandbox = isset( $membership_payment_settings['paypal_sandbox'] ) ? 'yes' : 'no';
$bank_details = isset( $membership_payment_settings['bank_details'] ) ? $membership_payment_settings['bank_details'] : '';
$payment_terms = isset( $membership_payment_settings['payment_terms'] ) ? $membership_payment_settings['payment_terms'] : '';

$wcfm_membership_payment_methods = get_wcfm_membership_payment_methods();

if( ( $is_free != 'yes' ) && empty( $payment_methods ) ) {
	?>
	<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
		<p><span class="wcfmfa fa-warning"></span>
		<?php _e( 'Something Wrong: You are not allowed to access this page now. Please contact Store Admin for more details.', 'wc-multivendor-membership' ); ?></p>
	</div>
	<?php
	return;
}

?>

<div id="wcfm_membership_container">
  <div class="wcfm_membership_review_pay">
		<div class="wcfm_membership_review_plan">
			<div class="wcfm_review_plan_welcome"><?php _e( 'Review your plan:', 'wc-multivendor-membership' ); ?></div>
			<div class="wcfm_review_plan_title"><?php _e( $title, 'wc-multivendor-membership' ); ?></div>
			<div class="wcfm_review_plan_description"><?php _e( $description, 'wc-multivendor-membership' ); ?></div>
			<div class="wcfm_review_plan_features">
			 <?php
			 if( !empty( $membership_feature_lists ) ) {
					foreach( $membership_feature_lists as $membership_feature_key => $membership_feature_list ) {
						if( isset( $membership_feature_list['feature'] ) && !empty( $membership_feature_list['feature'] ) ) {
							$feature_val = 'x';
							$feature_name = sanitize_title($membership_feature_list['feature']);
							if( !empty( $features ) && isset( $features[$feature_name] ) && !empty( $features[$feature_name] ) ) $feature_val = $features[$feature_name];
							if( !empty( $features ) && !$feature_val && isset( $features[$membership_feature_list['feature']] ) ) $feature_val = $features[$membership_feature_list['feature']];
							if( !$feature_val ) $feature_val = 'x';
							?>
							<div class="wcfm_review_plan_feature"><?php echo wcfm_removeslashes( __( $membership_feature_list['feature'], 'WCfM' ) ); ?></div>
							<div class="wcfm_review_plan_feature_val"><?php echo wcfm_removeslashes( __( $feature_val, 'WCfM' ) ); ?></div>
							<?php
						}
					}
			 }
			 ?>
		  </div>
		</div>
		
		<div class="wcfm_membership_pay">
			<?php
			if( $is_free == 'yes' ) {
				?>
				<div class="wcfm_review_pay_welcome"><?php _e( 'Confirmation', 'wc-multivendor-membership' ); ?>:</div>
				<form id="wcfm_membership_payment_form_free" class="wcfm wcfm_membership_payment_form">
					<div class="wcfm_review_pay_free"><?php echo apply_filters( 'wcfm_free_plan_payment_message', __( 'FREE Plan!!! There is no payment option for this.', 'wc-multivendor-membership' ) ); ?></div>
					<input type="hidden" name="member_id" value="<?php echo $member_id; ?>" />
					<div class="wcfm-clearfix"></div>
					<div class="wcfm-message" tabindex="-1"></div>
					
					<div id="wcfm_membership_payment_submit" class="wcfm_form_simple_submit_wrapper">
						<input type="submit" name="save-data" value="<?php _e( 'Proceed', 'wc-multivendor-membership' ); ?>" id="wcfm_membership_payment_button_free" class="wcfm_membership_payment_button wcfm_submit_button" />
					</div>
					<div class="wcfm-clearfix"></div>
				</form>
					<?php
				} else {
					?>
					<div class="wcfm_review_pay_welcome"><?php _e( 'Payment options:', 'wc-multivendor-membership' ); ?></div>
					<?php
					$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
					$one_time_amt = isset( $subscription['one_time_amt'] ) ? $subscription['one_time_amt'] : '1';
					$stripe_plan_id = isset( $subscription['stripe_plan_id'] ) ? $subscription['stripe_plan_id'] : '';
					$trial_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '';
					$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
					$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
					$billing_amt = isset( $subscription['billing_amt'] ) ? $subscription['billing_amt'] : '1';
					$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
					$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
					$period_options = array( 'D' => __( 'Day(s)', 'wc-multivendor-membership' ), 'M' => __( 'Month(s)', 'wc-multivendor-membership' ), 'Y' => __( 'Year(s)', 'wc-multivendor-membership' ) );
					
					foreach( $payment_methods as $payment_method ) {
						
						echo '<div class="wcfm_review_pay_non_free wcfm_review_pay_'.$payment_method.'"><input class="wcfm_subscription_paymode" name="wcfm_subscription_paymode" type="radio" value="'.$payment_method.'" />' . $wcfm_membership_payment_methods[$payment_method] .' - '; 
						if( $subscription_type == 'one_time' ) {
							echo wc_price( wcfmvm_membership_tax_price( $one_time_amt ) );
							wcfmvm_membership_table_tax_display( 'span' );
							echo '<span class="wcfm_membership_price_description"> (' . __( 'One time payment', 'wc-multivendor-membership' ) . ')</span>';
						} else {
							if( !empty( $trial_period ) && !empty( $trial_amt ) ) {
								if( $payment_method == 'stripe' ) {
									echo wc_price(0);
									$price_description = sprintf( __( '%s for each %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $billing_amt, $billing_period, $period_options[$billing_period_type] );
									$price_description .= ' ' . sprintf( __( 'with %s %s free trial', 'wc-multivendor-membership' ), $trial_period, $period_options[$trial_period_type] );
								} else {
									echo wc_price( wcfmvm_membership_tax_price( $trial_amt ) );
									wcfmvm_membership_table_tax_display( 'span' );
									$price_description = ' ' . sprintf( __( '%s for first %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $trial_amt, $trial_period, $period_options[$trial_period_type] );
									$price_description .= ' ' . sprintf( __( 'and then %s for each %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $billing_amt, $billing_period, $period_options[$billing_period_type] );
								}
							} elseif( !empty( $trial_period ) && empty( $trial_amt ) ) {
								if( $payment_method == 'paypal' ) {
									echo wc_price( wcfmvm_membership_tax_price( 1 ) );
									wcfmvm_membership_table_tax_display( 'span' );
									$price_description = ' ' . sprintf( __( '%s for first %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . 1, $trial_period, $period_options[$trial_period_type] );
									$price_description .= ' ' . sprintf( __( 'and then %s for each %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $billing_amt, $billing_period, $period_options[$billing_period_type] );
								} else {
									echo wc_price(0);
									$price_description = sprintf( __( '%s for each %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $billing_amt, $billing_period, $period_options[$billing_period_type] );
									$price_description .= ' ' . sprintf( __( 'with %s %s free trial', 'wc-multivendor-membership' ), $trial_period, $period_options[$trial_period_type] );
								}
							} elseif( empty( $trial_period ) ) {
								echo wc_price( wcfmvm_membership_tax_price( $billing_amt ) );
								wcfmvm_membership_table_tax_display( 'span' );
								$price_description = sprintf( __( '%s for each %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $billing_amt, $billing_period, $period_options[$billing_period_type] );
							}
							echo '<span class="wcfm_membership_price_description"> (' . $price_description . ')</span>';
						}
						echo "</div>";
					
						if( $payment_method == 'paypal' ) {
							$WCFMvm->frontend->generate_paypal_request_form( $membership_id, $member_id );
							?>
							<div class="wcfm-clearfix"></div>
							<div class="wcfm-message" tabindex="-1"></div>
							
							<div id="wcfm_membership_payment_submit" class="wcfm_form_simple_submit_wrapper">
								<input type="submit" name="save-data" value="<?php _e( 'Proceed', 'wc-multivendor-membership' ); ?>" id="wcfm_membership_payment_button_paypal" class="wcfm_membership_payment_button wcfm_submit_button" />
							</div>
							<div class="wcfm-clearfix"></div>
						</form>
						<?php
						} elseif( $payment_method == 'stripe' ) {
							$stripe_form = $WCFMvm->frontend->generate_stripe_request_form( $membership_id, $member_id );
							if( $stripe_form ) {
								?>
								<div class="wcfm-clearfix"></div>
								<div class="wcfm-message" tabindex="-1"></div>
								
								<div id="wcfm_membership_payment_submit" class="wcfm_form_simple_submit_wrapper">
									<input type="submit" name="save-data" value="<?php _e( 'Proceed', 'wc-multivendor-membership' ); ?>" id="wcfm_membership_payment_button_stripe" class="wcfm_submit_button" />
								</div>
								<div class="wcfm-clearfix"></div>
							</form>
							<?php
							}
						} else {
							?>
							<form id="wcfm_membership_payment_form_bank_transfer" class="wcfm wcfm_membership_payment_form wcfm_membership_payment_form_non_free">
								<input type="hidden" name="member_id" value="<?php echo $member_id; ?>" />
								<div class="wcfm_payment_option_details wcfm_payment_option_bank_transfer_deails">
									<?php echo str_replace( "\n", "<br />", $bank_details ); ?>
								</div>
								<div class="wcfm-clearfix"></div>
								<div class="wcfm-message" tabindex="-1"></div>
								
								<div id="wcfm_membership_payment_submit" class="wcfm_form_simple_submit_wrapper">
									<input type="submit" name="save-data" value="<?php _e( 'Proceed', 'wc-multivendor-membership' ); ?>" id="wcfm_membership_payment_button_bank_transfer" class="wcfm_membership_payment_button wcfm_submit_button" />
								</div>
								<div class="wcfm-clearfix"></div>
							</form>
							<?php
						}
					}
					
					if( $payment_terms ) {
						?>
						<div class="wcfm-clearfix"></div><br />
						<div class="wcfmvm_payment_terms">
							<?php echo $payment_terms; ?>
						</div>
						<div class="wcfm-clearfix"></div><br />
						<?php
					}
				}
				?>
				
				
		</div>
	</div>
</div>
