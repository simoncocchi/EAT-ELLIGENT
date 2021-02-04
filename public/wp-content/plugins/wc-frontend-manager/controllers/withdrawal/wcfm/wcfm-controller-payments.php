<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Marketplace Payments Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/withdrawal/wcfm
 * @version   5.0.0
 */

class WCFM_Payments_Controller {
	
	private $vendor_id;
	
	public function __construct() {
		global $WCFM;
		
		$this->vendor_id  = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
		$start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
    
    if( isset($_POST['start_date']) && !empty($_POST['start_date']) ) {
    	$start_date = date('Y-m-d', strtotime(wc_clean($_POST['start_date'])) );
    }
    
    if( isset($_POST['end_date']) && !empty($_POST['end_date']) ) {
    	$end_date = date('Y-m-d', strtotime(wc_clean($_POST['end_date'])) );
    }
    
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'ID';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';
		
		$transaction_id = ! empty( $_POST['transaction_id'] ) ? sanitize_text_field( $_POST['transaction_id'] ) : '';
		
		$status_filter = 'completed';
    if( isset($_POST['status_type']) ) {
    	$status_filter = wc_clean($_POST['status_type']);
    }
    if( $status_filter ) {
    	$status_filter = " AND `withdraw_status` = '" . $status_filter . "'";
    }

		$sql = 'SELECT COUNT(commission.ID) FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `vendor_id` = {$this->vendor_id}";
		if( $transaction_id ) $sql .= " AND commission.ID = $transaction_id";
		$sql .= $status_filter; 
		//$sql .= " AND commission.withdraw_status IN ('pending','completed','requested')";
		$sql .= " AND DATE( commission.created ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		
		$filtered_payment_count = $wpdb->get_var( $sql );
		if( !$filtered_payment_count ) $filtered_payment_count = 0;

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `vendor_id` = {$this->vendor_id}";
		if( $transaction_id ) $sql .= " AND commission.ID = $transaction_id";
		//$sql .= " AND commission.withdraw_status IN ('pending','completed','requested')";
		$sql .= $status_filter; 
		$sql .= " AND DATE( commission.created ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";
		$sql .= " LIMIT {$length}";
		$sql .= " OFFSET {$offset}";
		
		$wcfm_payments_array = $wpdb->get_results( $sql );
		
		if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
		} else {
			$payment_gateways = array();
		}
		
		// Generate Payments JSON
		$wcfm_payments_json = '';
		$wcfm_payments_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $filtered_payment_count . ',
															"recordsFiltered": ' . $filtered_payment_count . ',
															"data": ';
		if(!empty($wcfm_payments_array)) {
			$index = 0;
			$wcfm_payments_json_arr = array();
			foreach($wcfm_payments_array as $transaction_id => $wcfm_payments_single) {
				
				// Status
				if( $wcfm_payments_single->withdraw_status == 'completed' ) {
					$wcfm_payments_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-completed text_tip" data-tip="' . __( 'Withdrawal Completed', 'wc-frontend-manager') . '"></span>';
				} elseif( $wcfm_payments_single->withdraw_status == 'cancelled' ) {
					$wcfm_payments_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-cancelled text_tip" data-tip="' . __( 'Withdrawal Cancelled', 'wc-frontend-manager') . '"></span>';
				} else {
					$wcfm_payments_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-processing text_tip" data-tip="' . __( 'Withdrawal Processing', 'wc-frontend-manager') . '"></span>';
				}
				
				// Transc.ID
				$transaction_label = '';
				if( apply_filters( 'wcfm_is_allow_transaction_details', true ) ) {
					$transaction_label = '<a href="' . wcfm_transaction_details_url( $wcfm_payments_single->ID ) . '" class="wcfm_dashboard_item_title">#' . sprintf( '%06u', $wcfm_payments_single->ID ) . '</a>';
				} else {
					$transaction_label = '<span class="wcfm_dashboard_item_title"># ' . sprintf( '%06u', $wcfm_payments_single->ID ) . '</span>';
				}
				if( apply_filters( 'wcfm_is_pref_vendor_invoice', true ) && apply_filters( 'wcfm_is_allow_withdrawal_invoice', true ) && ( $wcfm_payments_single->withdraw_status != 'cancelled' ) && WCFM_Dependencies::wcfmu_plugin_active_check() && WCFM_Dependencies::wcfm_wc_pdf_invoices_packing_slips_plugin_active_check() ) {
					$invoice_url = add_query_arg( array( 'withdraw_id' => $wcfm_payments_single->ID, 'action' => 'store_payment_invoice' ), WC()->ajax_url() );
					$transaction_label .= '<br /><a class="wcfm_withdrawal_invoice wcfm-action-icon withdrawal_quick_action wcfmfa fa-file-pdf text_tip" href="'.$invoice_url.'" data-withdrawalid="' . $wcfm_payments_single->ID . '" data-tip="' . esc_attr__( 'Invoice', 'wc-frontend-manager' ) . '"></a>';
				}
				$wcfm_payments_json_arr[$index][] = $transaction_label;
				
				// Order IDs
				$withdrawal_order_ids = explode(',', $wcfm_payments_single->order_ids);
				$withdrawal_orders = '';
				if( !empty( $withdrawal_order_ids ) ) {
					foreach( $withdrawal_order_ids as $withdrawal_order_id ) {
						if( $withdrawal_order_id ) {
							if( $withdrawal_orders ) $withdrawal_orders .= ', ';
							$withdrawal_orders .= '<a class="wcfm_dashboard_item_title transaction_order_ids" target="_blank" href="'. get_wcfm_view_order_url( $withdrawal_order_id ) .'">#'.  wcfm_get_order_number( $withdrawal_order_id ) . '</a>';;
						}
					}
				}
				$wcfm_payments_json_arr[$index][] =  $withdrawal_orders;
				
				// Commission IDs
				$wcfm_payments_json_arr[$index][] =  '<span class="wcfm_dashboard_item_title transaction_commission_ids">#'.  $wcfm_payments_single->commission_ids . '</span>';
				
				// Amount
				$withdraw_amount = (float) $wcfm_payments_single->withdraw_amount; 
				$wcfm_payments_json_arr[$index][] = wc_price( $withdraw_amount );
				
				// Charges
				$withdraw_charges = (float) $wcfm_payments_single->withdraw_charges;  
				$wcfm_payments_json_arr[$index][] = wc_price( $withdraw_charges );
				
				// Payment
				$wcfm_marketplace_withdrwal_payment_methods = get_wcfm_marketplace_withdrwal_payment_methods();
				$amount = wc_price( $withdraw_amount - $withdraw_charges );  
				$amount .= '<br /><small class="meta">' . __( 'Via', 'wc-frontend-manager' ) . ' ';
				if ( isset( $wcfm_marketplace_withdrwal_payment_methods[$wcfm_payments_single->payment_method] ) ) {
					$amount .= __( $wcfm_marketplace_withdrwal_payment_methods[$wcfm_payments_single->payment_method], 'wc-multivendor-marketplace' );
				} else {
					$amount .= ( isset( $payment_gateways[ $wcfm_payments_single->payment_method ] ) ? esc_html( $payment_gateways[ $wcfm_payments_single->payment_method ]->get_title() ) : esc_html( $wcfm_payments_single->payment_method ) );
				}
				$billing_details = $WCFMmp->wcfmmp_vendor->wcfmmp_get_vendor_billing_details( $wcfm_payments_single->vendor_id, $wcfm_payments_single->payment_method );
				if( $billing_details ) $amount .= "<br />(".$billing_details.')';
				$amount .= '</small>';
				$wcfm_payments_json_arr[$index][] = $amount;
				
				// Withdrawal Mode
				$withdrawal_mode = '';
				if( $wcfm_payments_single->is_auto_withdrawal ) {
					$withdrawal_mode = __( 'Auto Withdrawal', 'wc-frontend-manager' ) . "<br/>";
				} else {
					if( $wcfm_payments_single->withdraw_mode == 'by_paymode' ) {
						$withdrawal_mode .= __( 'By Payment Type', 'wc-frontend-manager' );
					} elseif( $wcfm_payments_single->withdraw_mode == 'by_request' ) {
						 $withdrawal_mode .= __( 'By Request', 'wc-frontend-manager' );
					} elseif( $wcfm_payments_single->withdraw_mode == 'by_auto_request' ) {
						 $withdrawal_mode .= __( 'By Auto Request', 'wc-frontend-manager' );
					} elseif( $wcfm_payments_single->withdraw_mode == 'by_schedule' ) {
						 $withdrawal_mode .= __( 'By Schedule Request', 'wc-frontend-manager' );
					} elseif( $wcfm_payments_single->withdraw_mode == 'by_split_pay' ) {
						 $withdrawal_mode .= __( 'Split Pay', 'wc-frontend-manager' );
					} elseif( $wcfm_payments_single->withdraw_mode == 'by_wirecard' ) {
						 $withdrawal_mode .= __( 'Wirecard Pay', 'wc-frontend-manager' );
					}
				}
				$wcfm_payments_json_arr[$index][] = $withdrawal_mode;
				
				// Additional Info
				$wcfm_payments_json_arr[$index][] = apply_filters( 'wcfm_payments_additonal_data', '&ndash;', $wcfm_payments_single->ID, $wcfm_payments_single->order_ids, $wcfm_payments_single->commission_ids, $wcfm_payments_single->vendor_id );
				
				// Note
				if( $wcfm_payments_single->withdraw_note ) {
					$wcfm_payments_json_arr[$index][] = $wcfm_payments_single->withdraw_note;
				} else {
					$wcfm_payments_json_arr[$index][] = '&ndash;';
				}
				
				// Date
				if( in_array( $wcfm_payments_single->withdraw_status, array('completed', 'cancelled') ) ) {
					$wcfm_payments_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_payments_single->withdraw_paid_date ) );
				} else {
					$wcfm_payments_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_payments_single->created ) );
				}
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_payments_json_arr) ) $wcfm_payments_json .= json_encode($wcfm_payments_json_arr);
		else $wcfm_payments_json .= '[]';
		$wcfm_payments_json .= '
													}';
													
		echo $wcfm_payments_json;
	}
}