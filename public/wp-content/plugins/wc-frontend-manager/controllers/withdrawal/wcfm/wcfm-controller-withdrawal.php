<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Marketplace Withdrawal Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/withdrawal/wcfm
 * @version   5.0.0
 */

class WCFM_Withdrawal_Controller {
	
	private $vendor_id;
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$this->vendor_id  = $WCFMmp->vendor_id;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$generate_auto_withdrawal = isset( $WCFMmp->wcfmmp_withdrawal_options['generate_auto_withdrawal'] ) ? $WCFMmp->wcfmmp_withdrawal_options['generate_auto_withdrawal'] : 'no';
		if( isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ) {
			$withdrawal_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] : '';
		} elseif( $generate_auto_withdrawal == 'yes' ) {
			$withdrawal_mode = 'by_order_status';
		} else {
			$withdrawal_mode = 'by_manual';
		}
		
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
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'order_id';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';

		$withdrawal_thresold = $WCFMmp->wcfmmp_withdraw->get_withdrawal_thresold( $this->vendor_id );

		$sql = 'SELECT COUNT(commission.ID) FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `vendor_id` = {$this->vendor_id}";
		$sql .= apply_filters( 'wcfm_order_status_condition', '', 'commission' );
		$sql .= " AND commission.withdraw_status IN ('pending', 'cancelled')";
		$sql .= " AND commission.refund_status != 'requested'";
		$sql .= ' AND `is_withdrawable` = 1 AND `is_auto_withdrawal` = 0 AND `is_refunded` = 0 AND `is_trashed` = 0';
		if( $withdrawal_thresold ) $sql .= " AND commission.created <= NOW() - INTERVAL {$withdrawal_thresold} DAY";
		if( $start_date && $end_date ) {
			$sql .= " AND DATE( commission.created ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		}
		
		$filtered_withdrawal_count = $wpdb->get_var( $sql );
		if( !$filtered_withdrawal_count ) $filtered_withdrawal_count = 0;

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `vendor_id` = {$this->vendor_id}";
		$sql .= apply_filters( 'wcfm_order_status_condition', '', 'commission' );
		$sql .= " AND commission.withdraw_status IN ('pending', 'cancelled')";
		$sql .= " AND commission.refund_status != 'requested'";
		$sql .= ' AND `is_withdrawable` = 1 AND `is_auto_withdrawal` = 0 AND `is_refunded` = 0 AND `is_trashed` = 0';
		if( $withdrawal_thresold ) $sql .= " AND commission.created <= NOW() - INTERVAL {$withdrawal_thresold} DAY";
		if( $start_date && $end_date ) {
			$sql .= " AND DATE( commission.created ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		}
		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";
		$sql .= " LIMIT {$length}";
		$sql .= " OFFSET {$offset}";
		
		$wcfm_withdrawals_array = $wpdb->get_results( $sql );
		
		// Generate Withdrawals JSON
		$wcfm_withdrawals_json = '';
		$wcfm_withdrawals_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $filtered_withdrawal_count . ',
															"recordsFiltered": ' . $filtered_withdrawal_count . ',
															"data": ';
		if(!empty($wcfm_withdrawals_array)) {
			$index = 0;
			$wcfm_withdrawals_json_arr = array();
			foreach($wcfm_withdrawals_array as $wcfm_withdrawals_single) {
				$order_id = $wcfm_withdrawals_single->order_id;
				
				$order = wc_get_order( $order_id );
				if( !is_a( $order , 'WC_Order' ) ) continue;
				
				try {
				  $line_item = new WC_Order_Item_Product( absint( $wcfm_withdrawals_single->item_id ) );
				  
				  // Refunded Items Skipping
				  if( $refunded_qty = $order->get_qty_refunded_for_item( absint( $wcfm_withdrawals_single->item_id ) ) ) {
				  	$refunded_qty = $refunded_qty * -1;
				  	if( $line_item->get_quantity() == $refunded_qty ) {
				  		continue;
				  	}
				  }
				}  catch (Exception $e) {
					continue;
				}
				
				if( apply_filters( 'wcfm_is_show_commission_restrict_check', false, $order_id, $wcfm_withdrawals_single ) ) continue;
				
				// Status
				if( $withdrawal_mode == 'by_manual' ) {
					$wcfm_withdrawals_json_arr[$index][] =  '<input name="commissions[]" value="' . $wcfm_withdrawals_single->ID . '" class="wcfm-checkbox select_withdrawal" type="checkbox" >';
				} else {
					$wcfm_withdrawals_json_arr[$index][] =  '&ndash;';
				}
				
				// Order ID
				$wcfm_withdrawals_json_arr[$index][] = apply_filters( 'wcfm_commission_order_label_display', '<a class="wcfm_dashboard_item_title withdrawal_order_ids" target="_blank" href="'. get_wcfm_view_order_url( $order_id ) .'"># ' . wcfm_get_order_number( $order_id ) . '</a>', $order_id, $wcfm_withdrawals_single );
				
				// Commission ID
				$wcfm_withdrawals_json_arr[$index][] = '<span class="wcfm_dashboard_item_title"># ' . $wcfm_withdrawals_single->ID . '</span>'; 
				
				// My Earnings
				$wcfm_withdrawals_json_arr[$index][] = wc_price( $wcfm_withdrawals_single->total_commission );  
				
				// Charges
				$wcfm_withdrawals_json_arr[$index][] = wc_price( $wcfm_withdrawals_single->withdraw_charges );  
				
				// Payment
				$wcfm_withdrawals_json_arr[$index][] = wc_price( (float) $wcfm_withdrawals_single->total_commission - (float) $wcfm_withdrawals_single->withdraw_charges );  
				
				// Additional Info
				$wcfm_withdrawals_json_arr[$index][] = apply_filters( 'wcfm_withdrawal_additonal_data', '&ndash;', $wcfm_withdrawals_single->ID, $wcfm_withdrawals_single->order_id, $wcfm_withdrawals_single->vendor_id );
				
				// Date
				$wcfm_withdrawals_json_arr[$index][] = apply_filters( 'wcfm_commission_date_display', date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_withdrawals_single->created ) ), $order_id, $wcfm_withdrawals_single );
				
				$index++;
			}												
		}
		if( !empty($wcfm_withdrawals_json_arr) ) $wcfm_withdrawals_json .= json_encode($wcfm_withdrawals_json_arr);
		else $wcfm_withdrawals_json .= '[]';
		$wcfm_withdrawals_json .= '
													}';
													
		echo $wcfm_withdrawals_json;
	}
}