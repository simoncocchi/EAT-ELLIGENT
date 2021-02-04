<?php
/**
 * WCFM plugin view
 *
 * WCFM WCMp Transaction Details List View
 *
 * @author 		WC Lovers
 * @package 	wcfm/wcmp/view
 * @version   4.1.3
 */
 
global $WCFM, $WCMp, $wp;

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

$transaction = get_post($transaction_id);

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
			<h2><?php _e( 'Transaction #', 'wc-frontend-manager' ); echo $transaction_id; ?></h2>
			<span class="transaction-status transaction-status-<?php echo sanitize_title( $transaction->post_status ); ?>"><?php echo ucfirst( str_replace( 'wcmp_', '', $transaction->post_status ) ); ?></span>
			
			<?php
			if( $wcfm_is_allow_withdrawal = apply_filters( 'wcfm_is_allow_withdrawal', true ) ) {
				if( $wcmp_is_allow_withdrawal = apply_filters('wcmp_vendor_dashboard_menu_vendor_withdrawal_capability', false) ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_url().'" data-tip="'. __('Withdrawal Request', 'wc-frontend-manager') .'"><span class="wcfmfa fa-currency">' . get_woocommerce_currency_symbol() . '</span><span class="text">' . __('Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
				}
			}
			if( $wcfm_is_allow_payments = apply_filters( 'wcfm_is_allow_payments', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_payments_url().'" data-tip="'. __('Transaction History', 'wc-frontend-manager') .'"><span class="wcfmfa fa-credit-card"></span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
		<?php do_action( 'before_wcfm_transaction_details' ); ?>
			
		<div class="wcfm-container">
			<div id="wcfm_transaction_details_listing_expander" class="wcfm-content">
				<?php 
            $amount = (float) get_post_meta($transaction_id, 'amount', true) - (float) get_post_meta($transaction_id, 'transfer_charge', true) - (float) get_post_meta($transaction_id, 'gateway_charge', true);
            if (isset($transaction->post_type) && $transaction->post_type == 'wcmp_transaction') {
                $vendor = get_wcmp_vendor_by_term($transaction->post_author) ? get_wcmp_vendor_by_term($transaction->post_author) : get_wcmp_vendor($transaction->post_author);
                $commission_details = $WCMp->transaction->get_transaction_item_details($transaction_id);
            ?>
            <table class="table table-bordered">
                <?php if (!empty($commission_details['header'])) { 
                    echo '<thead><tr>';
                    foreach ($commission_details['header'] as $header_val) {
                        echo '<th>'.$header_val.'</th>';
                    }
                    echo '</tr></thead>';
                }
                echo '<tbody>';
                if (!empty($commission_details['body'])) {
                    
                    foreach ($commission_details['body'] as $commission_detail) {
                        echo '<tr>';
                        foreach ($commission_detail as $details) {
                            foreach ($details as $detail_key => $detail) {
                                echo '<td>'.$detail.'</td>';
                            }
                        }
                        echo '</tr>';
                    }
                    
                }
                if ($totals = $WCMp->transaction->get_transaction_item_totals($transaction_id, $vendor)) {
                    foreach ($totals as $total) {
                        echo '<tr><td colspan="3" >'.$total['label'].'</td><td>'.$total['value'].'</td></tr>';
                    }
                }
                echo '</tbody>';
                ?>
            </table>
        <?php } else { ?>
            <p class="wcmp_headding3"><?php printf(__('Hello,<br>Unfortunately your request for withdrawal amount could not be completed. You may try again later, or check you PayPal settings in your account page, or contact the admin at <b>%s</b>', 'dc-woocommerce-multi-vendor'), get_option('admin_email')); ?></p>
        <?php } ?>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_transaction_details' );
		?>
	</div>
</div>