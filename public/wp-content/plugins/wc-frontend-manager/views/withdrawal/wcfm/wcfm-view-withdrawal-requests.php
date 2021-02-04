<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Wthdrawal Requests List View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/withdrawal/wcfm
 * @version   5.0.0
 */
 
global $WCFM, $WCFMmp;

if( wcfm_is_vendor() || !apply_filters( 'wcfm_is_allow_withdrawal_requets', true ) ) {
	wcfm_restriction_message_show( "Withdrawal Requests" );
	return;
}

$default_status_type = apply_filters( 'wcfm_withdrawal_default_status_type', 'requested' );
$withdrawal_negative = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
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
			<?php
			if( $withdrawal_negative ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_reverse_url().'" data-tip="'. __('Reverse Withdrawal', 'wc-frontend-manager') .'"><span class="wcfmfa fa-currency">' . get_woocommerce_currency_symbol() . '</span><span class="text">' . __('Reverse Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <div class="wcfm_withdrawal_requests_filter_wrap wcfm_filters_wrap">
			<select name="status_type" id="dropdown_status_type" style="width: 160px;">
				<option value="" <?php selected( $default_status_type, "" ); ?>><?php  _e( 'Show all ..', 'wc-frontend-manager' ); ?></option>
				<option value="completed" <?php selected( $default_status_type, "completed" ); ?>><?php  _e( 'Approved', 'wc-frontend-manager' ); ?></option>
				<option value="requested" <?php selected( $default_status_type, "requested" ); ?>><?php  _e( 'Requested', 'wc-frontend-manager' ); ?></option>
				<option value="cancelled" <?php selected( $default_status_type, "cancelled" ); ?>><?php  _e( 'Cancelled', 'wc-frontend-manager' ); ?></option>
			</select>
			
			<?php 
			$vendor_arr = array();
			$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																								"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																								 ) );
			?>
			<?php $WCFM->library->wcfm_date_range_picker_field(); ?>
		</div>
	  
		<?php do_action( 'before_wcfm_withdrawal_requests' ); ?>
			
		<form metod="post" id="wcfm_withdrawal_requests_manage_form">
			<div class="wcfm-container">
				<div id="wcfm_withdrawal_requests_listing_expander" class="wcfm-content">
					<table id="wcfm-withdrawal-requests" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>
									<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select all to approve or cancel', 'wc-frontend-manager' ); ?>" />
								</th>
								<th><?php _e( 'Invoice ID', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Order IDs', 'wc-frontend-manager' ); ?></th>
								<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
								<th><?php _e( 'Amount', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Charges', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Commission IDs', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( apply_filters( 'wcfm_withdrawal_request_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>
									<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select all to approve or cancel', 'wc-frontend-manager' ); ?>" />
								</th>
								<th><?php _e( 'Invoice ID', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Order IDs', 'wc-frontend-manager' ); ?></th>
								<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
								<th><?php _e( 'Amount', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Charges', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Payment', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Commission IDs', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( apply_filters( 'wcfm_withdrawal_request_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							</tr>
						</tfoot>
					</table>
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div><br/>
			
			<?php if( apply_filters( 'wcfm_is_allow_withdrawal_requets_action', true ) ) { ?>
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
							<input type="submit" name="withdrawal-requests-data" value="<?php _e( 'Reject', 'wc-frontend-manager' ); ?>" id="wcfm_withdrawal_requests_cancel_button" class="wcfm_submit_button" />
							<input type="submit" name="withdrawal-requests-data" value="<?php _e( 'Approve', 'wc-frontend-manager' ); ?>" id="wcfm_withdrawal_requests_approve_button" class="wcfm_submit_button" />
						</div>
						<div class="wcfm-clearfix"></div>
					</div>
				</div>
			<?php } ?>
		</form>
		<?php
		do_action( 'after_wcfm_withdrawal_requests' );
		?>
	</div>
</div>