<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Transaction Details List View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/withdrawal/wcfm
 * @version   5.0.0
 */
 
global $WCFM, $WCFMmp, $wp, $wpdb;

$wcfm_is_allow_transaction_details = apply_filters( 'wcfm_is_allow_transaction_details', true );
if( !$wcfm_is_allow_transaction_details || !apply_filters( 'wcfm_is_allow_payments', true ) ) {
	wcfm_restriction_message_show( "Transaction Details" );
	return;
}

$transaction_id = 0;
if( isset( $wp->query_vars['wcfm-transaction-details'] ) && !empty( $wp->query_vars['wcfm-transaction-details'] ) ) {
	$transaction_id = $wp->query_vars['wcfm-transaction-details'];
}

if( !$transaction_id ) {
	wcfm_restriction_message_show( "Transaction ID Missing" );
	return;
}

$transaction = '';
$transaction_metas = array();
$paid_amount = 0;
$pay_currency = get_woocommerce_currency(); 

$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request';
$sql .= ' WHERE 1=1';
$sql .= " AND ID = " . $transaction_id;
$withdrawal_infos = $wpdb->get_results( $sql );
if( !empty( $withdrawal_infos ) ) {
	foreach( $withdrawal_infos as $withdrawal_info ) {
		$transaction = $withdrawal_info;
	}
}

if( !$transaction ) {
	wcfm_restriction_message_show( "Invalid Transaction ID" );
	return;
}

$paid_amount = (float)$transaction->withdraw_amount - (float)$transaction->withdraw_charges;

$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request_meta';
$sql .= ' WHERE 1=1';
$sql .= " AND `withdraw_id` = " . $transaction_id;
$withdrawal_metas = $wpdb->get_results( $sql );
if( !empty( $withdrawal_metas ) ) {
	foreach( $withdrawal_metas as $withdrawal_meta ) {
		if( in_array( $withdrawal_meta->key, array('sender_batch_id') ) ) continue;
		if( $withdrawal_meta->key == 'withdraw_amount' ) {
			$paid_amount = $withdrawal_meta->value;
		} elseif( $withdrawal_meta->key == 'currency' ) {
			$pay_currency = $withdrawal_meta->value;
		} else {
			$transaction_metas[$withdrawal_meta->key] = $withdrawal_meta->value;
		}
	}
}

if ( WC()->payment_gateways() ) {
	$payment_gateways = WC()->payment_gateways->payment_gateways();
} else {
	$payment_gateways = array();
}

$transaction_meta_labels = array(
																"ac_name"          => __('Account Name', 'wc-frontend-manager'), 
																"ac_number"        => __('Account Number', 'wc-frontend-manager'),
																"bank_name"        => __('Bank Name', 'wc-frontend-manager'), 
																"bank_addr"        => __('Bank Address', 'wc-frontend-manager'), 
																"routing_number"   => __('Routing Number', 'wc-frontend-manager'),
																"iban"             => __('IBAN', 'wc-frontend-manager'), 
																"swift"            => __('Swift Code', 'wc-frontend-manager'), 
																"ifsc"             => __('IFSC Code', 'wc-frontend-manager'), 
																"reciver_email"    => __('Payment Received Account', 'wc-frontend-manager'),
																"payout_batch_id"  => __('Transaction ID', 'wc-frontend-manager'),
																"batch_status"     => __('Transaction Status', 'wc-frontend-manager'),
																"sender_batch_id"  => __('Request ID', 'wc-frontend-manager'),
																"transaction_id"   => __('Transaction ID', 'wc-frontend-manager'),
																"transaction_ref"  => __('Transaction Ref', 'wc-frontend-manager'),
																"transaction_type" => __('Transfer Mode', 'wc-frontend-manager'),
																);

?>
<div class="collapse wcfm-collapse" id="wcfm_transaction_details_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-credit-card"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Transaction Details', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Transaction #', 'wc-frontend-manager' ); echo sprintf( '%06u', $transaction_id ); ?></h2>
			<span class="transaction-status transaction-status-<?php echo sanitize_title( $transaction->withdraw_status ); ?>"><?php echo wcfmmp_status_labels( $transaction->withdraw_status ); ?></span>
			
			<?php
			if( wcfm_is_vendor() ) {
				if( $wcfm_is_allow_withdrawal = apply_filters( 'wcfm_is_allow_withdrawal', true ) ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_url().'" data-tip="'. __('Withdrawal Request', 'wc-frontend-manager') .'"><span class="wcfmfa fa-currency">' . get_woocommerce_currency_symbol() . '</span><span class="text">' . __('Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
				}
				if( $wcfm_is_allow_payments = apply_filters( 'wcfm_is_allow_payments', true ) ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_payments_url().'" data-tip="'. __('Transaction History', 'wc-frontend-manager') .'"><span class="wcfmfa fa-credit-card"></span></a>';
				}
			} else {
				if( $wcfm_is_allow_withdrawal = apply_filters( 'wcfm_is_allow_withdrawal', true ) ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_requests_url().'" data-tip="'. __('Withdrawal Request', 'wc-frontend-manager') .'"><span class="wcfmfa fa-credit-card"></span><span class="text">' . __('Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
				}
				
				if( apply_filters( 'wcfm_is_pref_vendor_invoice', true ) && WCFM_Dependencies::wcfmu_plugin_active_check() && WCFM_Dependencies::wcfm_wc_pdf_invoices_packing_slips_plugin_active_check() ) {
					$invoice_url = add_query_arg( array( 'withdraw_id' => $transaction_id, 'action' => 'store_payment_invoice' ), WC()->ajax_url() );
					echo '<a class="wcfm_pdf_invoice wcfm-action-icon withdrawal_quick_action add_new_wcfm_ele_dashboard" href="'.$invoice_url.'" data-withdrawalid="' . $transaction_id . '"><span class="wcfmfa fa-file-pdf text_tip" data-tip="' . esc_attr__( 'Invoice', 'wc-frontend-manager' ) . '"></span></a>';
				}
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
		<?php do_action( 'before_wcfm_transaction_details' ); ?>
			
		<div class="wcfm-container">
			<div id="wcfm_transaction_details_listing_expander" class="wcfm-content">
				<?php 
					$amount = (float) $transaction->withdraw_amount;
					$vendor = $transaction->vendor_id;
					?>
					<table class="table table-bordered">
						<thead>
						  <tr>
								<th><?php _e( 'Order ID(s)', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Commission ID(s)', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Payment Method', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Status', 'wc-frontend-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
						  <tr>
						    <td><?php echo $transaction->order_ids; ?></td>
						    <td><?php echo $transaction->commission_ids; ?></td>
						    <td>
						      <?php 
						      	$wcfm_marketplace_withdrwal_payment_methods = get_wcfm_marketplace_withdrwal_payment_methods();
						      	if ( isset( $wcfm_marketplace_withdrwal_payment_methods[$transaction->payment_method] ) ) {
											_e( $wcfm_marketplace_withdrwal_payment_methods[$transaction->payment_method], 'wc-frontend-manager' );
										} else {
											echo ( isset( $payment_gateways[ $transaction->payment_method ] ) ? esc_html( $payment_gateways[ $transaction->payment_method ]->get_title() ) : esc_html( $transaction->payment_method ) );
										} 
									?>
						    </td>
						    <td><?php echo wcfmmp_status_labels( $transaction->withdraw_status ); ?></td>
						  </tr>
							<tr>
								<td colspan="3"><?php _e( 'Pay Mode', 'wc-frontend-manager' ); ?></td>
								<td><?php 
								if( $transaction->is_auto_withdrawal ) {
									_e( 'Auto Withdrawal', 'wc-frontend-manager' ) . "<br/>";
								} else {
									if( $transaction->withdraw_mode == 'by_paymode' ) {
										_e( 'By Payment Type', 'wc-frontend-manager' );
									} elseif( $transaction->withdraw_mode == 'by_request' ) {
										_e( 'By Request', 'wc-frontend-manager' );
									} elseif( $transaction->withdraw_mode == 'by_auto_request' ) {
										_e( 'By Auto Request', 'wc-frontend-manager' );
									} elseif( $transaction->withdraw_mode == 'by_schedule' ) {
										_e( 'By Schedule Request', 'wc-frontend-manager' );
									} elseif( $transaction->withdraw_mode == 'by_split_pay' ) {
										 _e( 'By Split Pay', 'wc-frontend-manager' );
									} elseif( $transaction->withdraw_mode == 'by_wirecard' ) {
										 _e( 'By Wirecard', 'wc-frontend-manager' );
									}
								} 
								?></td>
							</tr>
						  <tr>
								<td colspan="3"><?php _e( 'Total Amount', 'wc-frontend-manager' ); ?></td>
								<td><?php echo wc_price( $transaction->withdraw_amount, array( 'currency' => $pay_currency ) ); ?></td>
							</tr>
							<tr>
								<td colspan="3"><?php _e( 'Withdrawal Charges', 'wc-frontend-manager' ); ?></td>
								<td><?php echo wc_price( $transaction->withdraw_charges, array( 'currency' => $pay_currency ) ); ?></td>
							</tr>
							<tr>
								<td colspan="3"><?php if( $transaction->withdraw_status == 'completed' ) { _e( 'Paid Amount', 'wc-frontend-manager' ); } else { _e( 'Payable Amount', 'wc-frontend-manager' ); } ?></td>
								<td><strong><?php echo wc_price( $paid_amount, array( 'currency' => $pay_currency ) ); ?></strong></td>
							</tr>
							<?php if( !empty( $transaction_metas ) ) { ?>
								<?php foreach( $transaction_metas as $transaction_meta_key => $transaction_meta ) { ?>
									<?php if( $transaction_meta ) { ?>
										<tr>
											<td colspan="3"><?php echo ( isset( $transaction_meta_labels[$transaction_meta_key] ) ) ? $transaction_meta_labels[$transaction_meta_key] : ucfirst( str_replace( '_', ' ', $transaction_meta_key ) ); ?></td>
											<td><?php echo $transaction_meta; ?></td>
										</tr>
									<?php } ?>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_transaction_details' );
		?>
	</div>
</div>