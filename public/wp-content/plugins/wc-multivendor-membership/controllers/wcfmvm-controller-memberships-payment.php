<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Memberships Payment Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/controllers
 * @version   1.0.0
 */

class WCFMvm_Memberships_Payment_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMu;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMvm, $wpdb, $wcfm_membership_payment_form_data;
		
		$wcfm_membership_payment_form_data = array();
	  parse_str($_POST['wcfm_membership_payment_form'], $wcfm_membership_payment_form_data);
	  
	  $wcfm_membership_payment_messages = get_wcfmvm_membership_payment_messages();
	  $has_error = false;
	  
	  if(isset($wcfm_membership_payment_form_data['member_id']) && !empty($wcfm_membership_payment_form_data['member_id'])) {
			$member_id = absint( $wcfm_membership_payment_form_data['member_id'] );
			$member_user = new WP_User(absint($member_id));
			$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
			$shop_name = get_user_meta( $member_id, 'store_name', true );
			$paymode = $_POST['paymode'];
			
			if( $wcfm_membership ) {
				update_user_meta( $member_id, 'wcfm_membership_paymode', $paymode );
				$required_approval = get_post_meta( $wcfm_membership, 'required_approval', true ) ? get_post_meta( $wcfm_membership, 'required_approval', true ) : 'no';
				
				if( $required_approval != 'yes' ) {
					$has_error = $WCFMvm->register_vendor( $member_id );
					$WCFMvm->store_subscription_data( $member_id, $paymode, '', 'free_subscription', 'Completed', '' );
				} else {
					$WCFMvm->send_approval_reminder_admin( $member_id );
				}
			
				// Reset Membership Session
				if( WC()->session && WC()->session->get( 'wcfm_membership' ) ) {
					WC()->session->__unset( 'wcfm_membership' );
				}
			
				if(!$has_error) { echo '{"status": true, "message": "' . $wcfm_membership_payment_messages['subscription_success'] . '", "redirect": "' . apply_filters( 'wcfm_registration_thankyou_url', add_query_arg( 'vmstep', 'thankyou', get_wcfm_membership_url() ) ) . '"}'; }
				else { echo '{"status": false, "message": "' . $wcfm_membership_payment_messages['subscription_failed'] . '"}'; }
			} else {
				echo '{"status": false, "message": "' . $wcfm_membership_payment_messages['no_memberid'] . '"}';
			}
	  } else {
			echo '{"status": false, "message": "' . $wcfm_membership_payment_messages['no_memberid'] . '"}';
		}
		
		die;
	}
}