<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Payments List View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/withdrawal/wcfm
 * @version   5.0.0
 */
 
global $WCFM, $WCFMmp;

$wcfm_is_allow_payments = apply_filters( 'wcfm_is_allow_payments', true );
if( !$wcfm_is_allow_payments ) {
	wcfm_restriction_message_show( "Payments" );
	return;
}

$start_date = date_i18n( wc_date_format(), strtotime( date('01-m-Y') ) );
$end_date = date_i18n( wc_date_format(), strtotime( date('t-m-Y') ) );

$default_status_type = apply_filters( 'wcfm_payment_default_status_type', 'completed' );

$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';

$withdrawal_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] : 'by_manual';
?>
<div class="collapse wcfm-collapse" id="wcfm_payments_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-credit-card"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Payments History', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2>
				<?php _e( 'Transactions for: ', 'wc-frontend-manager' ); ?> 
				<span class="trans_start_date"><?php echo $start_date; ?></span>
				<?php echo ' - '; ?>
				<span class="trans_end_date"><?php echo $end_date; ?>
			</h2>
			
			<?php
			if( ( $withdrawal_mode == 'by_manual' ) && apply_filters( 'wcfm_is_allow_withdrawal', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_url().'" data-tip="'. __('Withdrawal Request', 'wc-frontend-manager') .'"><span class="wcfmfa fa-currency">' . get_woocommerce_currency_symbol() . '</span><span class="text">' . __('Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
			}
			if( $withdrawal_reverse ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_reverse_url().'" data-tip="'. __('Reverse Withdrawal', 'wc-frontend-manager') .'"><span class="wcfmfa fa-currency">' . get_woocommerce_currency_symbol() . '</span><span class="text">' . __('Reverse Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <div class="wcfm_payments_filter_wrap wcfm_filters_wrap">
			<select name="status_type" id="dropdown_status_type" style="width: 160px;">
				<option value="" <?php selected( $default_status_type, "" ); ?>><?php  _e( 'Show all ..', 'wc-frontend-manager' ); ?></option>
				<option value="completed" <?php selected( $default_status_type, "completed" ); ?>><?php  _e( 'Approved', 'wc-frontend-manager' ); ?></option>
				<option value="requested" <?php selected( $default_status_type, "requested" ); ?>><?php  _e( 'Processing', 'wc-frontend-manager' ); ?></option>
				<option value="cancelled" <?php selected( $default_status_type, "cancelled" ); ?>><?php  _e( 'Cancelled', 'wc-frontend-manager' ); ?></option>
			</select>
			<?php $WCFM->library->wcfm_date_range_picker_field(); ?>
		</div>
		
		<?php do_action( 'before_wcfm_payments' ); ?>
			
		<div class="wcfm-container">
			<div id="wcfm_payments_listing_expander" class="wcfm-content">
				<table id="wcfm-payments" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'Invoice ID', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Order IDs', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Commission IDs', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Amount', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Charges', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Mode', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_payments_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'Invoice ID', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Order IDs', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Commission IDs', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Amount', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Charges', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Mode', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_payments_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_payments' );
		?>
	</div>
</div>