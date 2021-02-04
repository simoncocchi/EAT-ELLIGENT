<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Withdrawal List View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/wothdrawal/wcfm
 * @version   5.0.0
 */
 
global $WCFM, $woocommerce, $WCFMmp;

$wcfm_is_allow_withdrawal = apply_filters( 'wcfm_is_allow_withdrawal', true );
if( !$wcfm_is_allow_withdrawal ) {
	wcfm_restriction_message_show( "Withdrawal" );
	return;
}

$vendor_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
$withdrawal_limit = $WCFMmp->wcfmmp_withdraw->get_withdrawal_limit( $vendor_id );
$pending_withdrawal = $WCFM->wcfm_vendor_support->wcfm_get_pending_withdrawal_by_vendor( $vendor_id, 'all' );

$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
$withdrawal_reverse_limit = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse_limit'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse_limit'] : 0;
$reverse_balance    = 0;
if( $withdrawal_reverse ) {
	$reverse_balance = $WCFMmp->wcfmmp_withdraw->wcfm_get_pending_reverse_withdrawal_by_vendor( $vendor_id );
}

$generate_auto_withdrawal = isset( $WCFMmp->wcfmmp_withdrawal_options['generate_auto_withdrawal'] ) ? $WCFMmp->wcfmmp_withdrawal_options['generate_auto_withdrawal'] : 'no';
if( isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ) {
	$withdrawal_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] : '';
} elseif( $generate_auto_withdrawal == 'yes' ) {
	$withdrawal_mode = 'by_order_status';
} else {
	$withdrawal_mode = 'by_manual';
}
?>
<div class="collapse wcfm-collapse" id="wcfm_withdrawal_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-currency"><?php echo get_woocommerce_currency_symbol(); ?></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Withdrawal Request', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
		<div class="wcfm-container wcfm-top-element-container">
			<h2 style="text-align: left;">
				<?php _e( 'Pending Withdrawals: ', 'wc-frontend-manager' ); ?> 
				<span class=""><?php echo wc_price($pending_withdrawal); ?></span>
				<?php if( $withdrawal_limit ) { ?>
					<br />
					<?php _e( 'Threshold for withdrawals: ', 'wc-frontend-manager' ); ?> 
					<span class=""><?php echo wc_price($withdrawal_limit); ?></span>
				<?php } ?>
				<?php if( $withdrawal_reverse ) { ?>
					<br />
					<?php _e( 'Reverse pay balance ', 'wc-frontend-manager' ); ?>:
					<span class=""><?php echo wc_price($reverse_balance); ?>&nbsp; (<?php printf( __( 'Threshold Limit: %s', 'wc-frontend-manager'), wc_price($withdrawal_reverse_limit) ); ?> )</span>
				<?php } ?>
			</h2>
			<?php
			if( $wcfm_is_allow_payments = apply_filters( 'wcfm_is_allow_payments', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_payments_url().'" data-tip="'. __('Transaction History', 'wc-frontend-manager') .'"><span class="wcfmfa fa-credit-card"></span><span class="text">' . __('Transactions', 'wc-frontend-manager' ) . '</span></a>';
			}
			if( $withdrawal_reverse ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_reverse_url().'" data-tip="'. __('Reverse Withdrawal', 'wc-frontend-manager') .'"><span class="wcfmfa fa-currency">' . get_woocommerce_currency_symbol() . '</span><span class="text">' . __('Reverse Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <div class="wcfm_withdrawal_requests_filter_wrap wcfm_filters_wrap">
			<?php $WCFM->library->wcfm_date_range_picker_field(); ?>
		</div>
	  
	  <?php do_action( 'before_wcfm_withdrawal' ); ?>
		
		<form metod="post" id="wcfm_withdrawal_manage_form">
		  <div class="wcfm-container">
				<div id="wcfm_withdrawal_listing_expander" class="wcfm-content">
					<table id="wcfm-withdrawal" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>
									<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select all to send request', 'wc-frontend-manager' ); ?>" />
								</th>
								<th><?php printf( apply_filters( 'wcfm_commission_order_label', __( 'Order ID', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php _e( 'Commission ID', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'My Earnings', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Charges', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( apply_filters( 'wcfm_withdrawal_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>
									<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select all to send request', 'wc-frontend-manager' ); ?>" />
								</th>
								<th><?php printf( apply_filters( 'wcfm_commission_order_label', __( 'Order ID', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php _e( 'Commission ID', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'My Earnings', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Charges', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( apply_filters( 'wcfm_withdrawal_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							</tr>
						</tfoot>
					</table>
					<div class="wcfm-clearfix"></div>
				</div>	
			</div>	
			<div class="wcfm-clearfix"></div>
			
			<div class="withdrawal_charge_help">** <?php _e( 'Withdrawal charges will be re-calculated depending upon total withdrawal amount.', 'wc-frontend-manager' ); ?></div>
			<div class="wcfm-clearfix"></div>
			
			<?php if( $withdrawal_mode == 'by_manual' ) { ?>
				<div id="wcfm_products_simple_submit" class="wcfm_form_simple_submit_wrapper">
					<div class="wcfm-message" tabindex="-1"></div>
					
					<?php
						if ( (float) $pending_withdrawal >= (float) $withdrawal_limit ) {
							if( !$withdrawal_reverse || !$withdrawal_reverse_limit || ( $withdrawal_reverse  && ( (float) $reverse_balance < (float) $withdrawal_reverse_limit ) ) ) {
						?>
							<input type="submit" name="withdrawal-data" value="<?php _e( 'Request', 'wc-frontend-manager' ); ?>" id="wcfm_withdrawal_request_button" class="wcfm_submit_button" />
					<?php } else {
									echo '<div class="wcfm-message wcfm-error" tabindex="-1" style="display: block;"><span class="wcicon-status-cancelled"></span>'. __( 'Withdrawal disable due to high reverse balance.', 'wc-frontend-manager' ) .'</div>';
								}
						} else {
							echo '<div class="wcfm-message wcfm-error" tabindex="-1" style="display: block;"><span class="wcicon-status-cancelled"></span>'. __( 'Withdrawal disable due to low account balance.', 'wc-frontend-manager' ) .'</div>';
						} ?>
				</div>
			<?php } ?>
			<div class="wcfm-clearfix"></div>
		</form>
		<?php
		do_action( 'after_wcfm_withdrawal' );
		?>
	</div>
</div>