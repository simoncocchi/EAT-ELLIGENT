<?php
/**
 * WCFM plugin view
 *
 * WCFM Dokan Wthdrawal Requests List View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/withdrawal/dokan
 * @version   4.2.3
 */
 
global $WCFM;

if( !apply_filters( 'wcfm_is_allow_withdrawal_requets', true ) ) {
	wcfm_restriction_message_show( "Withdrawal Requests" );
	return;
}

?>
<div class="collapse wcfm-collapse" id="wcfm_payments_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-credit-card"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Withdrawal Requests', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Withdrawal Requests', 'wc-frontend-manager' ); ?></h2>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <div class="wcfm_payments_filter_wrap wcfm_filters_wrap">
			<select name="status_type" id="dropdown_status_type" style="width: 160px;">
				<option value=""><?php  _e( 'Show all ..', 'wc-frontend-manager' ); ?></option>
				<option value="1"><?php  _e( 'Approved', 'wc-frontend-manager' ); ?></option>
				<option value="0" selected><?php  _e( 'Processing', 'wc-frontend-manager' ); ?></option>
				<option value="2"><?php  _e( 'Cancelled', 'wc-frontend-manager' ); ?></option>
			</select>
		</div>
	  
		<?php do_action( 'before_wcfm_withdrawal_requests' ); ?>
			
		<form metod="post" id="wcfm_withdrawal_requests_manage_form">
			<div class="wcfm-container">
				<div id="wcfm_withdrawal_requests_listing_expander" class="wcfm-content">
					<table id="wcfm-withdrawal-requests" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Requests', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Status', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Store', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Requests', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Status', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Store', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							</tr>
						</tfoot>
					</table>
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div>
			
			<div class="wcfm-container">
				<div id="wcfm_withdrawal_requests_actions_expander" class="wcfm-content">
					<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_withdrawal_requests_fields_wcfmmp', array(
																																																											"withdraw_note" => array('label' => __('Note to Vendor(s)', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea', 'label_class' => 'wcfm_title' ),
																																																										) ) );
					?>
					<div class="wcfm-clearfix"></div>
			
					<div id="wcfm_products_simple_submit" class="wcfm_form_simple_submit_wrapper">
						<div class="wcfm-message" tabindex="-1"></div>
						<input type="submit" name="withdrawal-requests-data" value="<?php _e( 'Cancel', 'wc-frontend-manager' ); ?>" id="wcfm_withdrawal_requests_cancel_button" class="wcfm_submit_button" />
						<input type="submit" name="withdrawal-requests-data" value="<?php _e( 'Approve', 'wc-frontend-manager' ); ?>" id="wcfm_withdrawal_requests_approve_button" class="wcfm_submit_button" />
					</div>
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div>
		</form>
		<?php
		do_action( 'after_wcfm_withdrawal_requests' );
		?>
	</div>
</div>