<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Dokan Withdrawal Request Approve Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/withdrawal/dokan
 * @version   4.2.3
 */

class WCFM_Withdrawal_Requests_Approve_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wcfm_withdrawal_manage_form_data = array();
	  parse_str($_POST['wcfm_withdrawal_manage_form'], $wcfm_withdrawal_manage_form_data);
	  
	  $commissions = array();
	  if( isset( $wcfm_withdrawal_manage_form_data['withdrawals'] ) && !empty( $wcfm_withdrawal_manage_form_data['withdrawals'] ) ) {
	  	$withdrawals = $wcfm_withdrawal_manage_form_data['withdrawals'];
	  	$withdraw_note = $wcfm_withdrawal_manage_form_data['withdraw_note'];
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_withdrawal_manage_form_data, 'withdrawal_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
	  	
			foreach( $withdrawals as $withdrawal_id ) {
				// Update withdrawal status
				$results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->dokan_withdraw}
            WHERE id = %d",
            $withdrawal_id
        ) );
        
        if( !empty($results) ) {
        	foreach( $results as $result ) {
						$user_id = absint($result->user_id);
						$amount = $result->amount;
						$method = $result->method;
					}
				}
        
        if ( dokan_get_seller_balance( $user_id, false ) < $amount ) {
					echo '{"status": false, "message": "' . __('Seller account balance not enough for this withdrawal.', 'wc-frontend-manager') . '"}';
					die;
				}
				
				$installed_version = get_option( 'dokan_theme_version' );
        if ( ! $installed_version || version_compare( $installed_version, '2.8.2', '>' ) ) {
				
					$balance_sql    = "SELECT * FROM `{$wpdb->prefix}dokan_vendor_balance` WHERE `trn_id`={$withdrawal_id} AND `trn_type` = 'dokan_withdraw'";
					$balance_result = $wpdb->get_row( $balance_sql );
	
					if ( empty( $balance_result ) ) {
							$wpdb->insert( $wpdb->prefix . 'dokan_vendor_balance',
									array(
											'vendor_id'     => $user_id,
											'trn_id'        => $withdrawal_id,
											'trn_type'      => 'dokan_withdraw',
											'perticulars'   => 'Approve withdraw request',
											'debit'         => 0,
											'credit'        => $amount,
											'status'        => 'approved',
											'trn_date'      => current_time( 'mysql' ),
											'balance_date'  => current_time( 'mysql' ),
									),
									array(
											'%d',
											'%d',
											'%s',
											'%s',
											'%f',
											'%f',
											'%s',
											'%s',
											'%s',
									)
							);
					}
				}
				
				$wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->dokan_withdraw}
            SET status = %d, note = %s WHERE user_id=%d AND id = %d",
            1, $withdraw_note, $user_id, $withdrawal_id
        ) );

        $cache_key     = 'dokan_seller_balance_' . $user_id;
        wp_cache_delete( $cache_key );
				
				do_action( 'dokan_withdraw_request_approved', $user_id, $amount, $method );
				
				do_action( 'dokan_withdraw_status_updated', 1, $user_id, $withdrawal_id );
			}
			echo '{"status": true, "message": "' . __('Withdrawal Requests successfully approved.', 'wc-frontend-manager') . '"}';
	  } else {
	  	echo '{"status": false, "message": "' . __('No withdrawals selected for approve.', 'wc-frontend-manager') . '"}';
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
		global $WCFM, $wpdb, $_POST;
		
		$wcfm_withdrawal_manage_form_data = array();
	  parse_str($_POST['wcfm_withdrawal_manage_form'], $wcfm_withdrawal_manage_form_data);
	  
	  $commissions = array();
	  if( isset( $wcfm_withdrawal_manage_form_data['withdrawals'] ) && !empty( $wcfm_withdrawal_manage_form_data['withdrawals'] ) ) {
	  	$withdrawals = $wcfm_withdrawal_manage_form_data['withdrawals'];
	  	$withdraw_note = $wcfm_withdrawal_manage_form_data['withdraw_note'];
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_withdrawal_manage_form_data, 'withdrawal_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
	  	
			foreach( $withdrawals as $withdrawal_id ) {
				
				$results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->dokan_withdraw}
            WHERE id = %d",
            $withdrawal_id
        ) );
        
        if( !empty($results) ) {
        	foreach( $results as $result ) {
						$user_id = absint($result->user_id);
						$amount = $result->amount;
						$method = $result->method;
					}
				}
				
				$wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->dokan_withdraw}
            SET status = %d, note = %s WHERE user_id=%d AND id = %d",
            2, $withdraw_note, $user_id, $withdrawal_id
        ) );

        $cache_key     = 'dokan_seller_balance_' . $user_id;
        wp_cache_delete( $cache_key );
				
				do_action( 'dokan_withdraw_request_cancelled', $user_id, $amount, $method, $note );
				
				do_action( 'dokan_withdraw_status_updated', 0, $user_id, $withdrawal_id );
			}
			echo '{"status": true, "message": "' . __('Withdrawal Requests successfully cancelled.', 'wc-frontend-manager') . '"}';
	  } else {
	  	echo '{"status": false, "message": "' . __('No withdrawals selected for cancel.', 'wc-frontend-manager') . '"}';
	  }
		
		die;
	}
}