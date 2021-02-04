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

$free_thankyou_content = '';
$subscription_thankyou_content = '';

$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
if( ( $wcfm_membership == -1 ) || ( $wcfm_membership == '-1' ) ) {
	$membership_reject_rules = array();
	if( isset( $wcfm_membership_options['membership_reject_rules'] ) ) $membership_reject_rules = $wcfm_membership_options['membership_reject_rules'];
	$required_approval = isset( $membership_reject_rules['required_approval'] ) ? $membership_reject_rules['required_approval'] : 'no';
} else {
	$required_approval = get_post_meta( $wcfm_membership, 'required_approval', true ) ? get_post_meta( $wcfm_membership, 'required_approval', true ) : 'no';
	
	$free_thankyou_content = wcfm_get_post_meta( $wcfm_membership, 'free_thankyou_content', true );
	$subscription_thankyou_content = wcfm_get_post_meta( $wcfm_membership, 'subscription_thankyou_content', true );
}


if( !$free_thankyou_content ) {
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
}

if( !$subscription_thankyou_content ) {
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
}
?>

<div id="wcfm_membership_container">
  <div class="wcfm_membership_thankyou_content_wrapper">
		<?php if( ( $required_approval != 'yes' ) && wcfm_is_vendor( $member_id ) ) { ?>
			<?php do_action('wcfmvm_after_thank_you', $member_id ); ?>
			<div class="wcfm_membership_thankyou_content">
				<?php echo $free_thankyou_content; ?>
			</div>
			<div class="wcfm-clearfix"></div>
			<a class="wcfm_submit_button wcfm_registration_thank_you_dashbord_button" href="<?php echo apply_filters( 'wcfm_thank_you_left_button_url', get_wcfm_url(), $member_id ); ?>"><?php echo apply_filters( 'wcfm_thank_you_left_button_label', __( 'Go to Dashboard', 'wc-multivendor-membership' ), $member_id ); ?> >></a>
			<a class="wcfm_submit_button wcfm_registration_thank_you_setup_button" href="<?php echo apply_filters( 'wcfm_thank_you_right_button_url', get_wcfm_settings_url(), $member_id ); ?>"><?php echo apply_filters( 'wcfm_thank_you_right_button_label', __( 'Setup your store', 'wc-multivendor-membership' ), $member_id ); ?> >></a>
			<div class="wcfm-clearfix"></div>
		<?php } else { ?>
			<div class="wcfm_membership_thankyou_content">
				<?php echo $subscription_thankyou_content; ?>
			</div>
		<?php } ?>
		<div class="wcfm-clearfix"></div>
		<?php do_action('wcfmvm_after_thank_you', $member_id ); ?>
	</div>
	<div class="wcfm-clearfix"></div>
</div>