<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Ledger List View
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/views/ledger/
 * @version   1.0.0
 */
 
global $WCFM, $WCFMmp;

$wcfm_is_allow_ledger = apply_filters( 'wcfm_is_allow_ledger', true );
if( !$wcfm_is_allow_ledger ) {
	wcfm_restriction_message_show( "Ledger" );
	return;
}

$vendor_id = $WCFMmp->vendor_id;

$earned     = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $vendor_id, 'all' );
$withdrawal = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $vendor_id, 'all', true );
if( apply_filters( 'wcfm_is_pref_refund', true ) ) {
	$refund     = $WCFMmp->wcfmmp_refund->wcfm_get_refund_by_vendor( $vendor_id, 'all' );
}

$withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';

?>
<div class="collapse wcfm-collapse" id="wcfm_ledger_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-money-bill-alt"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Ledger Book', 'wc-multivendor-marketplace' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2><?php echo __( 'Ledger Book', 'wc-multivendor-marketplace' ); ?></h2>
			<div class="wcfm_ledger_filter_wrap wcfm_filters_wrap">
				<select name="status_type" id="dropdown_status_type" style="width: 160px;display: inline-block;">
					<option value=""><?php  _e( 'Show all ..', 'wc-frontend-manager' ); ?></option>
					<option value="completed"><?php  _e( 'Completed', 'wc-multivendor-marketplace' ); ?></option>
					<option value="pending"><?php  _e( 'Pending', 'wc-frontend-manager' ); ?></option>
					<option value="refunded"><?php  _e( 'Refunded', 'wc-multivendor-marketplace' ); ?></option>
					<option value="cancelled"><?php  _e( 'Cancelled', 'wc-frontend-manager' ); ?></option>
				</select>
				<select name="reference_type" id="dropdown_reference_type" style="width: 160px;display: inline-block;">
					<option value=""><?php  _e( 'Show all ..', 'wc-frontend-manager' ); ?></option>
					<option value="order"><?php  _e( 'Order', 'wc-multivendor-marketplace' ); ?></option>
					<option value="withdraw"><?php  _e( 'Withdrawal', 'wc-multivendor-marketplace' ); ?></option>
					<?php if( $withdrawal_reverse ) { ?>
						<option value="reverse-withdraw"><?php  _e( 'Reverse Withdrawal', 'wc-multivendor-marketplace' ); ?></option>
					<?php } ?>
					<option value="refund"><?php  _e( 'Refunded', 'wc-multivendor-marketplace' ); ?></option>
					<option value="partial-refund"><?php  _e( 'Partial Refunded', 'wc-multivendor-marketplace' ); ?></option>
					<option value="withdraw-charges"><?php  _e( 'Charges', 'wc-multivendor-marketplace' ); ?></option>
				</select>
			</div>
			<?php do_action( 'wcfm_ledger_quick_actions' ); ?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'before_wcfm_ledger' ); ?>
	  
	  <div class="wcfm_dashboard_stats">
			<div class="wcfm_dashboard_stats_block">
				<a href="<?php echo get_wcfm_reports_url( ); ?>">
					<span class="wcfmfa fa-currency"><?php echo get_woocommerce_currency_symbol() ; ?></span>
					<div>
						<strong><?php echo apply_filters( 'wcfm_vendor_dashboard_commission', wc_price( $earned ) ); ?></strong><br />
						<?php _e( 'total earning', 'wc-multivendor-marketplace' ); ?>
					</div>
				</a>
			</div>
			<div class="wcfm_dashboard_stats_block">
			  <a href="<?php echo get_wcfm_reports_url( ); ?>">
					<span class="wcfmfa fa-money-bill-alt"></span>
					<div>
						<strong><?php echo apply_filters( 'wcfm_vendor_dashboard_commission_paid', wc_price( $withdrawal ) ); ?></strong><br />
						<?php _e( 'total withdrawal', 'wc-multivendor-marketplace' ); ?>
					</div>
				</a>
			</div>
			<?php if( apply_filters( 'wcfm_is_pref_refund', true ) ) { ?>
				<div class="wcfm_dashboard_stats_block">
					<a href="<?php echo get_wcfm_reports_url( ); ?>">
						<span class="wcfmfa fa-retweet"></span>
						<div>
							<strong><?php echo apply_filters( 'wcfm_vendor_dashboard_commission_paid', wc_price( $refund ) ); ?></strong><br />
							<?php _e( 'total refund', 'wc-multivendor-marketplace' ); ?>
						</div>
					</a>
				</div>
			<?php } ?>
		</div>
		<div class="wcfm-clearfix"></div>
	  
		<div class="wcfm-container">
			<div id="wcfm_ledger_listing_expander" class="wcfm-content">
				<table id="wcfm-ledger" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-multivendor-marketplace' ); ?>"></span></th>
						  <th><?php _e( 'Type', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Details', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Credit', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Debit', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Dated', 'wc-multivendor-marketplace' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-multivendor-marketplace' ); ?>"></span></th>
						  <th><?php _e( 'Type', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Details', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Credit', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Debit', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Dated', 'wc-multivendor-marketplace' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_ledger' );
		?>
	</div>
</div>