<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Marketplace Withdrawal Request Approve Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/withdrawal/wcfm
 * @version   5.0.0
 */

class WCFM_Withdrawal_Reverse_Approve_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$wcfm_withdrawal_manage_form_data = array();
	  parse_str($_POST['wcfm_withdrawal_manage_form'], $wcfm_withdrawal_manage_form_data);
	  
	  $commissions = array();
	  if( isset( $wcfm_withdrawal_manage_form_data['withdrawals'] ) && !empty( $wcfm_withdrawal_manage_form_data['withdrawals'] ) ) {
	  	$withdrawals = $wcfm_withdrawal_manage_form_data['withdrawals'];
	  	$withdraw_note = $wcfm_withdrawal_manage_form_data['reverse_withdraw_note'];
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_withdrawal_manage_form_data, 'withdrawal_reverse_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
	  	
			foreach( $withdrawals as $withdrawal_id ) {
				// Update reverse withdrawal status
				$WCFMmp->wcfmmp_withdraw->wcfmmp_reverse_withdraw_status_update( $withdrawal_id, 'completed', $withdraw_note );
				
				do_action( 'wcfmmp_reverse_withdrawal_request_completed', $withdrawal_id );
			}
			echo '{"status": true, "message": "' . __('Reverse Withdrawal Requests successfully approveed.', 'wc-frontend-manager') . '"}';
	  } else {
	  	echo '{"status": false, "message": "' . __('No reverse withdrawals selected for approve.', 'wc-frontend-manager') . '"}';
	  }
		
		die;
	}
}