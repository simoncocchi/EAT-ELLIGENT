<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Dokan Withdrawal Requests Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/withdrawal/dokan
 * @version   4.2.3
 */

class WCFM_Withdrawal_Requests_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'ID';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';
		
		$transaction_id = ! empty( $_POST['transaction_id'] ) ? sanitize_text_field( $_POST['transaction_id'] ) : '';
		
		$status_filter = '';
    if( isset($_POST['status_type']) && ( $_POST['status_type'] != '' ) ) {
    	$status_filter = " AND `status` = " . wc_clean($_POST['status_type']);
    }

		$sql = 'SELECT COUNT(commission.id) FROM ' . $wpdb->prefix . 'dokan_withdraw AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND commission.user_id != 0";
		if( $transaction_id ) $sql .= " AND commission.id = $transaction_id";
		$sql .= $status_filter;
		
		$filtered_withdrawal_requests_count = $wpdb->get_var( $sql );
		if( !$filtered_withdrawal_requests_count ) $filtered_withdrawal_requests_count = 0;

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'dokan_withdraw AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND commission.user_id != 0";
		if( $transaction_id ) $sql .= " AND commission.id = $transaction_id";
		$sql .= $status_filter;
		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";
		$sql .= " LIMIT {$length}";
		$sql .= " OFFSET {$offset}";
		
		$wcfm_withdrawal_requests_array = $wpdb->get_results( $sql );
		
		// Generate Payments JSON
		$wcfm_payments_json = '';
		$wcfm_payments_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $filtered_withdrawal_requests_count . ',
															"recordsFiltered": ' . $filtered_withdrawal_requests_count . ',
															"data": ';
		if(!empty($wcfm_withdrawal_requests_array)) {
			$index = 0;
			$wcfm_withdrawal_requests_json_arr = array();
			foreach( $wcfm_withdrawal_requests_array as $wcfm_withdrawal_request_single ) {
				
				// Action
				if( $wcfm_withdrawal_request_single->status == 0 ) {
					$wcfm_withdrawal_requests_json_arr[$index][] =  '<input name="withdrawals[]" value="' . $wcfm_withdrawal_request_single->id . '" class="wcfm-checkbox select_withdrawal_requests" type="checkbox" >';
				} else {
					$wcfm_withdrawal_requests_json_arr[$index][] =  '&ndash;';
				}
				
				// Status
				if( $wcfm_withdrawal_request_single->status == 1 ) {
					$wcfm_withdrawal_requests_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-completed text_tip" data-tip="' . __( 'Withdrawal Approved', 'wc-frontend-manager') . '"></span>';
				} elseif( $wcfm_withdrawal_request_single->status == 2 ) {
					$wcfm_withdrawal_requests_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-cancelled text_tip" data-tip="' . __( 'Withdrawal Cancelled', 'wc-frontend-manager') . '"></span>';
				} else {
					$wcfm_withdrawal_requests_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-processing text_tip" data-tip="' . __( 'Withdrawal Pending', 'wc-frontend-manager') . '"></span>';
				}
				
				// Store
				$wcfm_withdrawal_requests_json_arr[$index][] = wcfm_get_vendor_store( absint($wcfm_withdrawal_request_single->user_id) );
				
				// Amount
				$amount = wc_price( $wcfm_withdrawal_request_single->amount );  
				$amount .= '<br /><small class="meta">' . __( 'Via', 'wc-frontend-manager' ) . ' ';
				$amount .= dokan_withdraw_get_method_title( $wcfm_withdrawal_request_single->method );
				$amount .= '</small>';
				$wcfm_withdrawal_requests_json_arr[$index][] = $amount;
				
				// Note
				$wcfm_withdrawal_requests_json_arr[$index][] =  $wcfm_withdrawal_request_single->note;
				
				// Date
				$wcfm_withdrawal_requests_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_withdrawal_request_single->date ) );
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_withdrawal_requests_json_arr) ) $wcfm_payments_json .= json_encode($wcfm_withdrawal_requests_json_arr);
		else $wcfm_payments_json .= '[]';
		$wcfm_payments_json .= '
													}';
													
		echo $wcfm_payments_json;
	}
}