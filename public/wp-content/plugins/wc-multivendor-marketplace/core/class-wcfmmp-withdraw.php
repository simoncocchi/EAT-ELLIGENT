<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Withdraw
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Withdraw {

	public function __construct() {
		global $WCFM, $WCFMmp;
		
		// Auto Withdrawal Request
		//add_action( 'woocommerce_order_edit_status', array( &$this, 'wcfmmp_auto_generate_withdrawal_request' ), 50, 2 );
		add_action( 'wcfmmp_vendor_order_status_updated', array( &$this, 'wcfmmp_auto_generate_withdrawal_request' ), 50, 3 );
		add_action( 'woocommerce_order_status_changed', array( &$this, 'wcfmmp_order_status_changed_auto_generate_withdrawal_request' ), 50, 3 );
		
		add_action( 'wcfmmp_order_item_processed', array( &$this, 'wcfmmp_order_item_auto_withdrawal_processed' ), 300, 9 ); 
		
		// Withdrawal Request Rest on Refund
		add_action( 'wcfmmp_commission_refund_status_completed', array( &$this, 'wcfmmp_withdrawal_requests_reset_on_refund' ), 50, 5 );
		
		// Reverse Withdrawal Request Rest on Refund
		add_action( 'wcfmmp_commission_refund_status_completed', array( &$this, 'wcfmmp_reverse_withdrawal_requests_reset_on_refund' ), 60, 5 );
	}
	
	/**
	 * Return Withdrawal request auto approve or not
	 * @return boolean
	 */
	function is_withdrawal_auto_approve( $vendor_id = 0 ) {
		global $WCFM, $WCFMmp;
		$request_auto_approve = isset( $WCFMmp->wcfmmp_withdrawal_options['request_auto_approve'] ) ? $WCFMmp->wcfmmp_withdrawal_options['request_auto_approve'] : 'no';
		if( $request_auto_approve == 'yes' ) return apply_filters( 'wcfmmp_is_withdrawal_auto_approve', true, $vendor_id );
		return apply_filters( 'wcfmmp_is_withdrawal_auto_approve', false, $vendor_id );
	}
	
	/**
	 * Return Withdrawal Limit
	 * @return boolean
	 */
	function get_withdrawal_limit( $vendor_id = 0 ) {
		global $WCFM, $WCFMmp;
		$withdrawal_limit = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_limit'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_limit'] : '';
		return apply_filters( 'wcfmmp_withdrawal_limit', $withdrawal_limit, $vendor_id );
	}
	
	/**
	 * Return Withdrawal Thresold
	 * @return boolean
	 */
	function get_withdrawal_thresold( $vendor_id = 0 ) {
		global $WCFM, $WCFMmp;
		$withdrawal_thresold = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_thresold'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_thresold'] : '';
		return apply_filters( 'wcfmmp_withdrawal_thresold', $withdrawal_thresold, $vendor_id );
	}
	
	/**
	 * Order Status Change Auto-withdrawal request
	 */
	function wcfmmp_order_status_changed_auto_generate_withdrawal_request( $order_id, $status_from, $status_to ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		// Store New Order Email to Vendors
		$wcfmmp_order_email_triggered = get_post_meta( $order_id, '_wcfmmp_order_email_triggered', true );
		if( !$wcfmmp_order_email_triggered ) {
			$store_new_order_email_allowed_order_status = get_wcfm_store_new_order_email_allowed_order_status();
			$current_order_status = 'wc-'.$status_to;
			if( isset( $store_new_order_email_allowed_order_status[$current_order_status] ) ) {
				$wcfmmp_email = WC()->mailer()->emails['WCFMmp_Email_Store_new_order'];
				if( $wcfmmp_email ) {
					$wcfmmp_email->trigger( $order_id );
					update_post_meta( $order_id, '_wcfmmp_order_email_triggered', 'yes' );
				}
			}
		}
		
		$this->wcfmmp_auto_generate_withdrawal_request( $order_id, $status_to );
	}
	
	/**
	 * Auto generate withdrawal request on order statue change
	 */
	function wcfmmp_auto_generate_withdrawal_request( $order_id, $order_status, $processed_vendor_id = 0 ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$generate_auto_withdrawal = isset( $WCFMmp->wcfmmp_withdrawal_options['generate_auto_withdrawal'] ) ? $WCFMmp->wcfmmp_withdrawal_options['generate_auto_withdrawal'] : 'no';
		if( isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ) {
			$withdrawal_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] : '';
		} elseif( $generate_auto_withdrawal == 'yes' ) {
			$withdrawal_mode = 'by_order_status';
		} else {
			$withdrawal_mode = 'by_manual';
		}
		
		if( $withdrawal_mode != 'by_order_status' ) return;
		
		$auto_withdrawal_status = isset( $WCFMmp->wcfmmp_withdrawal_options['auto_withdrawal_status'] ) ? $WCFMmp->wcfmmp_withdrawal_options['auto_withdrawal_status'] : 'wc-processing';
		$auto_withdrawal_status = str_replace( 'wc-', '', $auto_withdrawal_status );
		$auto_withdrawal_status = apply_filters( 'wcfmmp_auto_withdrawal_status', array( $auto_withdrawal_status ) );
		
		if( !in_array( $order_status, $auto_withdrawal_status ) ) return;
		
		// By Pass Stripe Split Pay
		$order = wc_get_order( $order_id );
		$order_payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
		if( in_array( $order_payment_method, apply_filters( 'wcfmmp_auto_withdrawal_exclude_payment_methods', array( 'stripe_split' ) ) ) ) return;
		
		$sql = 'SELECT GROUP_CONCAT(ID) commission_ids, COALESCE( SUM( commission.total_commission ), 0 ) AS total_commission, vendor_id, withdraw_status, order_status  FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `order_id` = " . $order_id;
		$sql .= " GROUP BY vendor_id";
		$commission_infos = $wpdb->get_results( $sql );
		
		if( !empty( $commission_infos ) ) {
			foreach( $commission_infos as $commission_info ) {
				
				if( $commission_info->withdraw_status != 'pending' ) continue;
				//if( $commission_info->order_status == $auto_withdrawal_status ) continue;
				
				$vendor_id = absint($commission_info->vendor_id);
				
				if( $processed_vendor_id && ( $processed_vendor_id != $vendor_id ) ) continue;
				
				$payment_method = $WCFMmp->wcfmmp_vendor->get_vendor_payment_method( $vendor_id );
				if( !$payment_method ) continue;
				
				if ( !array_key_exists( $payment_method, $WCFMmp->wcfmmp_gateways->payment_gateways ) ) continue;
				
				// Reset Commission withdrawal charges as per total withdrawal charge
				$withdraw_charges = $this->calculate_withdrawal_charges( $commission_info->total_commission, $vendor_id );
				
				// Update Commission withdrawal Status
				$commissions = explode( ",", $commission_info->commission_ids );
				$no_of_commission = count($commissions);
				$withdraw_charge_per_commission = (float)$withdraw_charges/$no_of_commission;
				foreach( $commissions as $commission_id ) {
					$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('withdraw_status' => 'requested', 'withdraw_charges' => round($withdraw_charge_per_commission,2)), array('ID' => $commission_id), array('%s', '%s'), array('%d'));
				}
				
				$withdraw_request_id = $this->wcfmmp_withdrawal_processed( $vendor_id, $order_id, $commission_info->commission_ids, $payment_method, 0, $commission_info->total_commission, $withdraw_charges, 'requested', 'by_auto_request' );
				
				if( $withdraw_request_id && !is_wp_error( $withdraw_request_id ) ) {
					$is_auto_approve = $this->is_withdrawal_auto_approve( $vendor_id );
					if( $is_auto_approve ) {
						$payment_processesing_status = $this->wcfmmp_withdrawal_payment_processesing( $withdraw_request_id, $vendor_id, $payment_method, $commission_info->total_commission, $withdraw_charges );
						if( $payment_processesing_status ) {
							//wcfm_log( __('Auto Withdrawal Request successfully processed.', 'wc-multivendor-marketplace') . ': #' . sprintf( '%06u', $withdraw_request_id ) );
						} else {
							wcfm_log( __('Auto Withdrawal Request processing failed, please contact Store Admin.', 'wc-multivendor-marketplace') . ': #' . sprintf( '%06u', $withdraw_request_id ) );
						}
					} else {
						// Admin Notification
						$shop_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( absint($vendor_id) );
						$wcfm_messages = sprintf( __( 'Vendor <b>%s</b> has placed a Withdrawal Request #%s.', 'wc-multivendor-marketplace' ), $shop_name, '<a target="_blank" class="wcfm_dashboard_item_title" href="' . add_query_arg( 'transaction_id', $withdraw_request_id, wcfm_withdrawal_requests_url() ) . '">' . sprintf( '%06u', $withdraw_request_id ) . '</a>' );
						$WCFM->wcfm_notification->wcfm_send_direct_message( $vendor_id, 0, 0, 1, $wcfm_messages, 'withdraw-request' );
						//wcfm_log( __('Auto withdrawal request successfully sent.', 'wc-frontend-manager') . ': #' . sprintf( '%06u', $withdraw_request_id ) );
					}
					
					do_action( 'wcfmmp_withdrawal_request_submited', $withdraw_request_id, $vendor_id );
				} else {
					wcfm_log( __('Auto withdrawal request failed, please try after sometime.', 'wc-multivendor-marketplace') );
				}
			}
		}
	}
	
	/**
	 * Auto withdrawal Order item process as withdrawal request
	 */
	function wcfmmp_order_item_auto_withdrawal_processed( $commission_id, $order_id, $order, $vendor_id, $product_id, $order_item_id, $grosse_total, $total_commission, $is_auto_withdrawal ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$order_id ) return;
		if( !$vendor_id ) return;
		if( !$commission_id ) return;
		if( !$is_auto_withdrawal ) {
			// Check Auto withdrawal by Order Status
			$this->wcfmmp_auto_generate_withdrawal_request( $order_id, $order->get_status(), $vendor_id );
			return;
		}
		
		$payment_method  = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
		$withdraw_mode   = 'by_paymode';
		$withdraw_status = 'pending';
		//$this->wcfmmp_withdrawal_processed( $vendor_id, $order_id, $commission_id, $payment_method, $grosse_total, $total_commission, 0, $withdraw_status, $withdraw_mode, $is_auto_withdrawal );
		
		// Reverse Withdrwal Process
		$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
		if( $withdrawal_reverse ) {
			$sql = 'SELECT commission.total_commission, vendor_id, withdraw_status, order_status  FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
			$sql .= ' WHERE 1=1';
			$sql .= " AND `ID` = " . $commission_id;
			$commission_infos = $wpdb->get_results( $sql );
			
			if( !empty( $commission_infos ) ) {
				foreach( $commission_infos as $commission_info ) {
					$this->wcfmmp_reverse_withdrawal_processed( $vendor_id, $order_id, $commission_id, $payment_method, $grosse_total, $commission_info->total_commission, $withdraw_status, $withdraw_mode, $is_auto_withdrawal );
				}
			}
		}
	}
	
	/**
	 * Withdrawal Request Reset on Refund
	 */
	function wcfmmp_withdrawal_requests_reset_on_refund( $refund_id, $commission_id, $order_id, $vendor_id, $refund ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$marketplace_withdrawals = $wpdb->get_results(  $wpdb->prepare( "SELECT ID, withdraw_status from {$wpdb->prefix}wcfm_marketplace_withdraw_request WHERE commission_ids = %s", $commission_id ) );
		foreach( $marketplace_withdrawals as $marketplace_withdrawal ) {
			if( !in_array( $marketplace_withdrawal->withdraw_status, array( 'completed', 'cancelled' ) ) ) {
				$this->wcfmmp_withdraw_status_update_by_withdrawal( $marketplace_withdrawal->ID, 'cancelled', __( 'Cancelled due to refund!', 'wc-multivendor-marketplace' ) );
			}
		}
		
	}
	
	/**
	 * Reverse Withdrawal Request Reset on Refund
	 */
	function wcfmmp_reverse_withdrawal_requests_reset_on_refund( $refund_id, $commission_id, $order_id, $vendor_id, $refund ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$reverse_withdrawals = $wpdb->get_results(  $wpdb->prepare( "SELECT ID, withdraw_status from {$wpdb->prefix}wcfm_marketplace_reverse_withdrawal WHERE commission_id = %s", $commission_id ) );
		foreach( $reverse_withdrawals as $reverse_withdrawal ) {
			if( !in_array( $reverse_withdrawal->withdraw_status, array( 'completed', 'cancelled' ) ) ) {
				$this->wcfmmp_reverse_withdraw_status_update( $reverse_withdrawal->ID, 'cancelled', __( 'Cancelled due to refund!', 'wc-multivendor-marketplace' ) );
				
				$sql = 'SELECT commission.total_commission, refunded_amount, vendor_id, withdraw_status, order_status, payment_method, is_auto_withdrawal  FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
				$sql .= ' WHERE 1=1';
				$sql .= " AND `ID` = " . $commission_id;
				$commission_infos = $wpdb->get_results( $sql );
				
				if( !empty( $commission_infos ) ) {
					foreach( $commission_infos as $commission_info ) {
						$withdraw_mode   = 'by_paymode';
						$withdraw_status = 'pending';
						if( apply_filters( 'wcfmmmp_gross_sales_respect_setting', true ) ) {
							$gross_sales = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_total' );
						} else {
							$gross_sales = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'gross_sales_total' );
						}
						$gross_sales -= (float) $commission_info->refunded_amount;
						$this->wcfmmp_reverse_withdrawal_processed( $vendor_id, $order_id, $commission_id, $commission_info->payment_method, $gross_sales, $commission_info->total_commission, $withdraw_status, $withdraw_mode, $commission_info->is_auto_withdrawal );
					}
				}
			}
		}
	}
	
	public function wcfmmp_withdrawal_processed( $vendor_id, $order_ids, $commission_ids, $payment_method, $grosse_total, $withdraw_amount, $withdraw_charges = 0, $withdraw_status = 'pending', $withdraw_mode = 'by_request', $is_auto_withdrawal = 0 ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$order_ids_array = explode( ",", $order_ids );
		$order_ids_array = array_unique( $order_ids_array );
		$order_ids       = implode( ",", $order_ids_array );
		
		$commission_ids_array = explode( ",", $commission_ids );
		$commission_ids_array = array_unique( $commission_ids_array );
		$commission_ids       = implode( ",", $commission_ids_array );
		
		$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_withdraw_request` 
									( vendor_id
									, order_ids
									, commission_ids
									, payment_method
									, withdraw_amount
									, withdraw_charges
									, withdraw_status
									, withdraw_mode
									, is_auto_withdrawal
									, created
									) VALUES ( %d
									, %s
									, %s
									, %s
									, %s
									, %s
									, %s 
									, %s
									, %d
									, %s
									) ON DUPLICATE KEY UPDATE `created` = %s"
							, $vendor_id
							, $order_ids
							, $commission_ids
							, $payment_method
							, $withdraw_amount
							, $withdraw_charges
							, $withdraw_status
							, $withdraw_mode
							, $is_auto_withdrawal
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
			)
		);
		$withdraw_request_id = $wpdb->insert_id;
		do_action( 'wcfmmp_withdraw_request_processed', $withdraw_request_id, $vendor_id, $order_ids, $commission_ids, $withdraw_amount, $withdraw_charges, $withdraw_status, $withdraw_mode, $is_auto_withdrawal );
		return $withdraw_request_id;
	}
	
	public function wcfmmp_reverse_withdrawal_processed( $vendor_id, $order_id, $commission_id, $payment_method, $grosse_total, $withdraw_amount, $withdraw_status = 'pending', $withdraw_mode = 'by_request', $is_auto_withdrawal = 0 ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
		if( !$withdrawal_reverse ) return;
		
		$balance = (float) $grosse_total - (float) $withdraw_amount;
		$balance = round($balance, 2);
		
		$withdraw_note = ''; //__( 'Reverse pay for auto withdrawal.', 'wc-multivendor-marketplace' );
		
		$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_reverse_withdrawal` 
									( vendor_id
									, order_id
									, commission_id
									, payment_method
									, gross_total
									, commission
									, balance
									, withdraw_status
									, withdraw_note
									, created
									) VALUES ( %d
									, %d
									, %d
									, %s
									, %s
									, %s
									, %s 
									, %s
									, %s
									, %s
									) ON DUPLICATE KEY UPDATE `created` = %s"
							, $vendor_id
							, $order_id
							, $commission_id
							, $payment_method
							, $grosse_total
							, $withdraw_amount
							, $balance
							, $withdraw_status
							, $withdraw_note
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
							, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
			)
		);
		$reverse_withdraw_request_id = $wpdb->insert_id;
		do_action( 'wcfmmp_reverse_withdraw_request_processed', $reverse_withdraw_request_id, $vendor_id, $order_id, $commission_id, $grosse_total, $withdraw_amount, $balance, $withdraw_status, $withdraw_mode, $is_auto_withdrawal );
		return $reverse_withdraw_request_id;
	}
	
	/**
	 * Reverse Withdrawal amount for a vendor
	 */
	function wcfm_get_pending_reverse_withdrawal_by_vendor( $vendor_id ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$reverse_balance = 0;
		
		$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
		if( !$withdrawal_reverse ) return 0;
		
		$sql = "SELECT SUM( commission.balance ) AS total_due FROM {$wpdb->prefix}wcfm_marketplace_reverse_withdrawal AS commission";
		$sql .= " WHERE 1=1";
		$sql .= " AND commission.withdraw_status = 'pending'";
		$sql .= " AND commission.vendor_id = {$vendor_id}";
		$reverse_withdrawal = $wpdb->get_results( $sql );
		if( !empty( $reverse_withdrawal ) ) {
			foreach( $reverse_withdrawal as $reverse_withdrawa ) {
				$reverse_balance = $reverse_withdrawa->total_due;
			}
		}
		
		return $reverse_balance;
	}
	
	public function wcfmmp_update_withdrawal_meta( $withdrawal_id, $key, $value ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_withdraw_request_meta` 
									( withdraw_id
									, `key`
									, `value`
									) VALUES ( %d
									, %s
									, %s
									)"
							, $withdrawal_id
							, $key
							, $value
			)
		);
		$withdraw_meta_id = $wpdb->insert_id;
		return $withdraw_meta_id;
	}
	
	/**
	 * Withdrawal Payment Processing
	 */
	public function wcfmmp_withdrawal_payment_processesing( $withdrawal_id, $vendor_id, $payment_method, $withdraw_amount, $withdraw_charges = 0, $withdraw_note = '' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$withdraw_note ) {
			$withdraw_note = apply_filters( 'wcfmmp_auto_withdrawal_note', __( 'Payment Processed', 'wc-multivendor-marketplace' ) );
		}
		
		$payment_processesing_status = true;
		
		if( $vendor_id ) {
			if ( array_key_exists( $payment_method, $WCFMmp->wcfmmp_gateways->payment_gateways ) ) {
				if( $withdraw_charges ) {
					$withdraw_amount = (float)$withdraw_amount - (float)$withdraw_charges;
				}
				
				$response = $WCFMmp->wcfmmp_gateways->payment_gateways[$payment_method]->process_payment( $withdrawal_id, $vendor_id, $withdraw_amount, $withdraw_charges, 'manual' );
				if ($response) {
					if( isset( $response['status'] ) && $response['status'] ) {
						
						// Update withdrawal status
						$this->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, 'completed', $withdraw_note );
						
						do_action( 'wcfmmp_withdrawal_request_approved', $withdrawal_id );
						
						wcfmmp_log( sprintf( '#%s - payment processing complete via %s. Amount: %s', sprintf( '%06u', $withdrawal_id ), ucfirst( $payment_method ), $withdraw_amount . ' ' . get_woocommerce_currency() ), 'info' );
						
					} else {
						foreach ($response as $message) {
							wcfmmp_log( sprintf( '#%s - payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), $message['message'] ), 'error' );
						}
						$payment_processesing_status = false;
					}
				} else {
					wcfmmp_log( sprintf( '#%s - payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), __('Something went wrong please try again later.', 'wc-multivendor-marketplace') ), 'error' );
					$payment_processesing_status = false;
				}
			} else {
				wcfmmp_log( sprintf( '#%s - payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), __('Invalid payment method.', 'wc-multivendor-marketplace') ), 'error' );
				$payment_processesing_status = false;
			}
		} else {
			wcfmmp_log( sprintf( '#%s - payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), __('No vendor for payment processing.', 'wc-multivendor-marketplace') ), 'error' );
			$payment_processesing_status = false;
		}
		return $payment_processesing_status;
	}
	
	/**
	 * Withdraw status update by Withdrawal ID
	 */
	public function wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, $status = 'completed', $note = '' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$withdrawal_id ) return;
		
		$wpdb->update("{$wpdb->prefix}wcfm_marketplace_withdraw_request", array('withdraw_status' => $status, 'withdraw_note' => $note, 'withdraw_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('ID' => $withdrawal_id), array('%s', '%s', '%s'), array('%d'));
		
		
		$vendor_id = 0;
			
		// Commission table update
		$sql = 'SELECT commission_ids, vendor_id FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request';
		$sql .= ' WHERE 1=1';
		$sql .= " AND ID = " . $withdrawal_id;
		$withdrawal_infos = $wpdb->get_results( $sql );
		if( !empty( $withdrawal_infos ) ) {
			foreach( $withdrawal_infos as $withdrawal_info ) {
				$vendor_id = $withdrawal_info->vendor_id;
				$commission_ids = explode(",", $withdrawal_info->commission_ids );
				if( !empty( $commission_ids ) ) {
					foreach( $commission_ids as $commission_id ) {
						$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('withdraw_status' => $status, 'commission_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('ID' => $commission_id), array('%s', '%s'), array('%d'));
						
						// Update commission ledger status
						//$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $commission_id, $status );
						
						do_action( 'wcfmmp_withdraw_status_'.$status.'_by_commission', $withdrawal_id, $commission_id );
					}
				}
			}
			
			// Vendor Notification
			if( $vendor_id ) {
				$wcfm_messages = apply_filters( 'wcfmmp_withdrawal_update_message', sprintf( __( 'Your withdrawal request #%s %s.', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . wcfm_transaction_details_url( $withdrawal_id ) . '">' . sprintf( '%06u', $withdrawal_id ) . '</a>', wcfmmp_status_labels( $status ) ),  $withdrawal_id, $status );
				if( $note ) {
					$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $note;
				}
				$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'withdraw-request' );
			}
			
			// On withdrawal update ledge entry status update
			$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $withdrawal_id, $status, 'withdraw' );
			$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $withdrawal_id, $status, 'withdraw-charges' );
			
			do_action( 'wcfmmp_withdraw_status_'.$status, $withdrawal_id );
		}
	}
	
	/**
	 * Withdraw status update by commission ID
	 */
	public function wcfmmp_withdraw_status_update_by_commission( $commission_id, $status = 'completed', $note = '' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$commission_id ) return;
		
		$wpdb->update("{$wpdb->prefix}wcfm_marketplace_withdraw_request", array('withdraw_status' => $status, 'withdraw_note' => $note, 'withdraw_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('commission_ids' => $commission_id), array('%s', '%s', '%s'), array('%d'));
		
		// ledge entry status update
		$sql = 'SELECT ID, vendor_id  FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request AS withdraw';
		$sql .= ' WHERE 1=1';
		$sql .= " AND `commission_ids` = '" . $commission_id . "'";
		$withdrawals = $wpdb->get_results( $sql );
		
		if( !empty( $withdrawals ) ) {
			foreach( $withdrawals as $withdrawal ) {
				
				// Ledger Status Update
				$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $withdrawal->ID, $status, 'withdraw' );
				$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $withdrawal->ID, $status, 'withdraw-charges' );
				
				// Vendor Notification
				$wcfm_messages = apply_filters( 'wcfmmp_withdrawal_update_message', sprintf( __( 'Your withdrawal request #%s %s.', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . wcfm_transaction_details_url( $withdrawal->ID ) . '">' . sprintf( '%06u', $withdrawal->ID ) . '</a>', wcfmmp_status_labels( $status ) ), $withdrawal->ID, $status );
				if( $note ) {
					$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $note;
				}
				$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $withdrawal->vendor_id, 1, 0, $wcfm_messages, 'withdraw-request' );
				
				do_action( 'wcfmmp_withdraw_status_'.$status, $withdrawal->ID );
				do_action( 'wcfmmp_withdraw_status_'.$status.'_by_commission', $withdrawal->ID, $commission_id );
			}
		}
	}
	
	/**
	 * Reverse Withdraw status update by Withdrawal ID
	 */
	public function wcfmmp_reverse_withdraw_status_update( $reverse_withdrawal_id, $status = 'completed', $note = '' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$reverse_withdrawal_id ) return;
		
		$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
		if( !$withdrawal_reverse ) return;
		
		$wpdb->update("{$wpdb->prefix}wcfm_marketplace_reverse_withdrawal", array('withdraw_status' => $status, 'withdraw_note' => $note, 'withdraw_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('ID' => $reverse_withdrawal_id), array('%s', '%s', '%s'), array('%d'));
		
		
		$vendor_id = 0;
			
		// Commission table update
		$sql = 'SELECT order_id, commission_id, vendor_id FROM ' . $wpdb->prefix . 'wcfm_marketplace_reverse_withdrawal';
		$sql .= ' WHERE 1=1';
		$sql .= " AND ID = " . $reverse_withdrawal_id;
		$withdrawal_infos = $wpdb->get_results( $sql );
		if( !empty( $withdrawal_infos ) ) {
			foreach( $withdrawal_infos as $withdrawal_info ) {
				$vendor_id     = $withdrawal_info->vendor_id;
				$order_id      = $withdrawal_info->order_id;
				$commission_id = $withdrawal_info->commission_id;
				$order         = wc_get_order( $order_id );
				if( $commission_id ) {
					$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('withdraw_status' => $status, 'commission_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('ID' => $commission_id), array('%s', '%s'), array('%d'));
					
					// Vendor Notification
					if( $vendor_id ) {
						$wcfm_messages = sprintf( __( 'Reverse withdrawal for order #%s %s.', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">' . $order->get_order_number() . '</a>', wcfmmp_status_labels( $status ) );
						if( $note ) {
							$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $note;
						}
						$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'withdraw-request' );
					}
					
					// On withdrawal update ledge entry status update
					$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $reverse_withdrawal_id, $status, 'reverse-withdraw' );
					
					do_action( 'wcfmmp_reverse_withdraw_status_'.$status, $reverse_withdrawal_id );
				}
			}
		}
	}
	
	/**
	 * Reverse Withdraw status update by Withdrawal ID
	 */
	public function wcfmmp_reverse_withdraw_status_update_by_commission( $commission_id, $status = 'completed', $note = '' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$commission_id ) return;
		
		$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
		if( !$withdrawal_reverse ) return;
		
		$wpdb->update("{$wpdb->prefix}wcfm_marketplace_reverse_withdrawal", array('withdraw_status' => $status, 'withdraw_note' => $note, 'withdraw_paid_date' => date('Y-m-d H:i:s', current_time( 'timestamp', 0 ))), array('commission_id' => $commission_id), array('%s', '%s', '%s'), array('%d'));
		
		
		$vendor_id = 0;
			
		// Commission table update
		$sql = 'SELECT ID, order_id, vendor_id FROM ' . $wpdb->prefix . 'wcfm_marketplace_reverse_withdrawal';
		$sql .= ' WHERE 1=1';
		$sql .= " AND commission_id = " . $commission_id;
		$withdrawal_infos = $wpdb->get_results( $sql );
		if( !empty( $withdrawal_infos ) ) {
			foreach( $withdrawal_infos as $withdrawal_info ) {
				$vendor_id             = $withdrawal_info->vendor_id;
				$order_id              = $withdrawal_info->order_id;
				$reverse_withdrawal_id = $withdrawal_info->ID;
				$order                 = wc_get_order( $order_id );
				if( $commission_id ) {
					// Vendor Notification
					if( $vendor_id ) {
						$wcfm_messages = sprintf( __( 'Reverse withdrawal for order #%s %s.', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">' . $order->get_order_number() . '</a>', wcfmmp_status_labels( $status ) );
						if( $note ) {
							$wcfm_messages .= "<br /><b>" . __( 'Note', 'wc-multivendor-marketplace' ) . "</b>: " . $note;
						}
						$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'withdraw-request' );
					}
					
					// On withdrawal update ledge entry status update
					$WCFMmp->wcfmmp_ledger->wcfmmp_ledger_status_update( $reverse_withdrawal_id, $status, 'reverse-withdraw' );
					
					do_action( 'wcfmmp_reverse_withdraw_status_'.$status, $reverse_withdrawal_id );
				}
			}
		}
	}
	
	/**
	 * Calculate and Reture Withdrawal charges
	 */
	public function calculate_withdrawal_charges( $amount, $vendor_id = 0 ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$withdrawal_charges = 0;
		
		$payment_method = $WCFMmp->wcfmmp_vendor->get_vendor_payment_method( $vendor_id );
		if ( array_key_exists( $payment_method, $WCFMmp->wcfmmp_gateways->payment_gateways ) ) {
			if( $payment_method && $amount ) {
				$withdrawal_charge_type = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_charge_type'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_charge_type'] : 'no';
				$withdrawal_charge          = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_charge'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_charge'] : array();
				
				$withdrawal_charge_gateway  = isset( $withdrawal_charge[$payment_method] ) ? $withdrawal_charge[$payment_method][0] : array();
				$withdrawal_percent_charge  = isset( $withdrawal_charge_gateway['percent'] ) ? $withdrawal_charge_gateway['percent'] : 0;
				$withdrawal_fixed_charge    = isset( $withdrawal_charge_gateway['fixed'] ) ? $withdrawal_charge_gateway['fixed'] : 0;
				$withdrawal_charge_tax      = isset( $withdrawal_charge_gateway['tax'] ) ? $withdrawal_charge_gateway['tax'] : 0;
				
				// Vendor Wise Overrided Setting Check 
				
				switch( $withdrawal_charge_type ) {
					case 'no':
						$withdrawal_charges = 0;
					break;
					
					case 'fixed':
						$withdrawal_charges = (float) $withdrawal_fixed_charge;
					break;
					
					case 'percent':
						$withdrawal_charges = (float) $amount * ( (float)$withdrawal_percent_charge/100 );
					break;
					
					case 'percent_fixed':
						$withdrawal_charges  = (float) $amount * ( (float) $withdrawal_percent_charge/100 );
						$withdrawal_charges += (float) $withdrawal_fixed_charge;
					break;
					
					default:
						$withdrawal_charges = 0;
					break;
				}
				
				if( $withdrawal_charges && $withdrawal_charge_tax ) {
					$withdrawal_tax      = (float) $withdrawal_charges * ( (float) $withdrawal_charge_tax/100 );
					$withdrawal_charges += (float) $withdrawal_tax;
				}
			}
		}
		
		if( $withdrawal_charges ) {
			$withdrawal_charges = round( $withdrawal_charges, 2 );
		}
		
		return apply_filters( 'wcfmmp_withdrawal_charges', $withdrawal_charges, $amount, $vendor_id );
	}
}