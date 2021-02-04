<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Marketplace Reverse Withdrawal Requests Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/withdrawal/wcfm
 * @version   5.0.0
 */

class WCFM_Withdrawal_Reverse_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
		$start_date = '';
    $end_date = '';
    
    if( isset($_POST['start_date']) && !empty($_POST['start_date']) ) {
    	$start_date = date('Y-m-d', strtotime(wc_clean($_POST['start_date'])) );
    }
    
    if( isset($_POST['end_date']) && !empty($_POST['end_date']) ) {
    	$end_date = date('Y-m-d', strtotime(wc_clean($_POST['end_date'])) );
    }
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'ID';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';
		
		$withdrawal_vendor_filter = '';
		if ( ! empty( $_POST['withdrawal_vendor'] ) ) {
			$withdrawal_vendor = esc_sql( $_POST['withdrawal_vendor'] );
			$withdrawal_vendor_filter = " AND commission.`vendor_id` = {$withdrawal_vendor}";
		}
		
		if( wcfm_is_vendor() ) {
			$withdrawal_vendor_filter = " AND commission.`vendor_id` = {$WCFMmp->vendor_id}";
		}
		
		$status_filter = 'pending';
    if( isset($_POST['status_type']) ) {
    	$status_filter = wc_clean($_POST['status_type']);
    }
    if( $status_filter ) {
    	$status_filter = " AND `withdraw_status` = '" . $status_filter . "'";
    }

		$sql = 'SELECT COUNT(commission.ID) FROM ' . $wpdb->prefix . 'wcfm_marketplace_reverse_withdrawal AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= $status_filter; 
		if ( $withdrawal_vendor_filter ) {
			$sql .= $withdrawal_vendor_filter;
		} else {
			$sql .= " AND commission.vendor_id != 0";
		}
		if( $start_date && $end_date ) {
			$sql .= " AND DATE( commission.created ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		}
		
		$filtered_withdrawal_requests_count = $wpdb->get_var( $sql );
		if( !$filtered_withdrawal_requests_count ) $filtered_withdrawal_requests_count = 0;

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wcfm_marketplace_reverse_withdrawal AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= $status_filter; 
		if ( $withdrawal_vendor_filter ) {
			$sql .= $withdrawal_vendor_filter;
		} else {
			$sql .= " AND commission.vendor_id != 0";
		}
		if( $start_date && $end_date ) {
			$sql .= " AND DATE( commission.created ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		}
		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";
		$sql .= " LIMIT {$length}";
		$sql .= " OFFSET {$offset}";
		
		$wcfm_reverse_withdrawal_requests_array = $wpdb->get_results( $sql );
		
		if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
		} else {
			$payment_gateways = array();
		}
		
		// Generate Payments JSON
		$wcfm_payments_json = '';
		$wcfm_payments_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $filtered_withdrawal_requests_count . ',
															"recordsFiltered": ' . $filtered_withdrawal_requests_count . ',
															"data": ';
		if(!empty($wcfm_reverse_withdrawal_requests_array)) {
			$index = 0;
			$wcfm_reverse_withdrawal_requests_json_arr = array();
			foreach( $wcfm_reverse_withdrawal_requests_array as $wcfm_reverse_withdrawal_request_single ) {
				
				// Status
				if( $wcfm_reverse_withdrawal_request_single->withdraw_status == 'completed' ) {
					$wcfm_reverse_withdrawal_requests_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-completed text_tip" data-tip="' . __( 'Reverse Pay Completed', 'wc-frontend-manager') . '"></span>';
				} elseif( $wcfm_reverse_withdrawal_request_single->withdraw_status == 'cancelled' ) {
					$wcfm_reverse_withdrawal_requests_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-cancelled text_tip" data-tip="' . __( 'Reverse Pay Cancelled', 'wc-frontend-manager') . '"></span>';
				} else {
					if( wcfm_is_vendor() ) {
						$wcfm_reverse_withdrawal_requests_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-processing text_tip" data-tip="' . __( 'Reverse Pay Processing', 'wc-frontend-manager') . '"></span>';
					} else {
						$wcfm_reverse_withdrawal_requests_json_arr[$index][] =  '<input name="withdrawals[]" value="' . $wcfm_reverse_withdrawal_request_single->ID . '" class="wcfm-checkbox select_withdrawal_requests" type="checkbox" >';
					}
				}
				
				// Order ID
				$wcfm_reverse_withdrawal_requests_json_arr[$index][] =  '<a class="wcfm_dashboard_item_title transaction_order_id" target="_blank" href="'. get_wcfm_view_order_url( $wcfm_reverse_withdrawal_request_single->order_id ) .'">#'.  wcfm_get_order_number( $wcfm_reverse_withdrawal_request_single->order_id ) . '</a>';
				
				// Store
				$wcfm_reverse_withdrawal_requests_json_arr[$index][] = wcfm_get_vendor_store( absint($wcfm_reverse_withdrawal_request_single->vendor_id) );
				
				// Gross Total
				$amount = wc_price( $wcfm_reverse_withdrawal_request_single->gross_total );  
				$amount .= '<br /><small class="meta">' . __( 'Via', 'wc-frontend-manager' ) . ' ';
				$amount .= ( isset( $payment_gateways[ $wcfm_reverse_withdrawal_request_single->payment_method ] ) ? esc_html( $payment_gateways[ $wcfm_reverse_withdrawal_request_single->payment_method ]->get_title() ) : esc_html( $wcfm_reverse_withdrawal_request_single->payment_method ) );
				$amount .= '</small>';
				$wcfm_reverse_withdrawal_requests_json_arr[$index][] = apply_filters( 'wcfm_withdrawal_reverse_gross_amount', $amount, $wcfm_reverse_withdrawal_request_single );
				
				// Commission
				$wcfm_reverse_withdrawal_requests_json_arr[$index][] = wc_price( $wcfm_reverse_withdrawal_request_single->commission );
				
				// Balance
				$wcfm_reverse_withdrawal_requests_json_arr[$index][] = wc_price( $wcfm_reverse_withdrawal_request_single->balance );
				
				// Additional Info
				$wcfm_reverse_withdrawal_requests_json_arr[$index][] = apply_filters( 'wcfm_withdrawal_reverse_additonal_data', '&ndash;', $wcfm_reverse_withdrawal_request_single->ID, $wcfm_reverse_withdrawal_request_single->order_id, $wcfm_reverse_withdrawal_request_single->vendor_id, $wcfm_reverse_withdrawal_request_single );
				
				// Note
				if( $wcfm_reverse_withdrawal_request_single->withdraw_note ) {
					$wcfm_reverse_withdrawal_requests_json_arr[$index][] = $wcfm_reverse_withdrawal_request_single->withdraw_note;
				} else {
					$wcfm_reverse_withdrawal_requests_json_arr[$index][] = '&ndash;';
				}
				
				// Date
				if( in_array( $wcfm_reverse_withdrawal_request_single->withdraw_status, array('completed', 'cancelled') ) ) {
					$wcfm_reverse_withdrawal_requests_json_arr[$index][] = apply_filters( 'wcfm_withdrawal_reverse_date', date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_reverse_withdrawal_request_single->withdraw_paid_date ) ), $wcfm_reverse_withdrawal_request_single );
				} else {
					$wcfm_reverse_withdrawal_requests_json_arr[$index][] = apply_filters( 'wcfm_withdrawal_reverse_date', date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_reverse_withdrawal_request_single->created ) ), $wcfm_reverse_withdrawal_request_single );
				}
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_reverse_withdrawal_requests_json_arr) ) $wcfm_payments_json .= json_encode($wcfm_reverse_withdrawal_requests_json_arr);
		else $wcfm_payments_json .= '[]';
		$wcfm_payments_json .= '
													}';
													
		echo $wcfm_payments_json;
	}
}