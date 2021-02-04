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

class WCFM_Withdrawal_Requests_Approve_Controller {
	
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
	  	$withdrawals   = $wcfm_withdrawal_manage_form_data['withdrawals'];
	  	$withdraw_note = wcfm_stripe_newline( $wcfm_withdrawal_manage_form_data['withdraw_note'] );
	  	$withdraw_note = esc_sql( $withdraw_note );
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_withdrawal_manage_form_data, 'withdrawal_requests_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
	  	
			$withdrawal_update_status = true;
			foreach( $withdrawals as $withdrawal_id ) {
				$sql = 'SELECT vendor_id, payment_method, withdraw_amount, withdraw_charges FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request';
				$sql .= ' WHERE 1=1';
				$sql .= " AND ID = " . $withdrawal_id;
				$withdrawal_infos = $wpdb->get_results( $sql );
				if( !empty( $withdrawal_infos ) ) {
					foreach( $withdrawal_infos as $withdrawal_info ) {
						$vendor_id = $withdrawal_info->vendor_id;
						$payment_method = $withdrawal_info->payment_method;
						$withdraw_amount = $withdrawal_info->withdraw_amount;
						$withdraw_charges = $withdrawal_info->withdraw_charges;
						$payment_processesing_status = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_payment_processesing( $withdrawal_id, $vendor_id, $payment_method, $withdraw_amount, $withdraw_charges, $withdraw_note );
						if( !$payment_processesing_status )
							$withdrawal_update_status = false;
					}
				}
			}
			if( $withdrawal_update_status ) {
				echo '{"status": true, "message": "' . __('Withdrawal Requests successfully processed.', 'wc-frontend-manager') . '"}';
			} else {
				echo '{"status": false, "message": "' . __('Withdrawal Requests partially processed, check log for more details.', 'wc-frontend-manager') . '"}';
			}
	  } else {
	  	echo '{"status": false, "message": "' . __('No withdrawals selected for approval.', 'wc-frontend-manager') . '"}';
	  }
		
		die;
	}
}

class WCFM_Withdrawal_Requests_Cancel_Controller {
	
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
	  	$withdrawals   = $wcfm_withdrawal_manage_form_data['withdrawals'];
	  	$withdraw_note = wcfm_stripe_newline( $wcfm_withdrawal_manage_form_data['withdraw_note'] );
	  	$withdraw_note = esc_sql( $withdraw_note );
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_withdrawal_manage_form_data, 'withdrawal_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
	  	
			$order_ids = '';
			$commission_ids = '';
			$total_commission = 0;
			
			foreach( $withdrawals as $withdrawal_id ) {
				// Update withdrawal status
				$WCFMmp->wcfmmp_withdraw->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, 'cancelled', $withdraw_note );
				
				do_action( 'wcfmmp_withdrawal_request_cancelled', $withdrawal_id );
			}
			echo '{"status": true, "message": "' . __('Withdrawal Requests successfully cancelled.', 'wc-frontend-manager') . '"}';
	  } else {
	  	echo '{"status": false, "message": "' . __('No withdrawals selected for cancel.', 'wc-frontend-manager') . '"}';
	  }
		
		die;
	}
}