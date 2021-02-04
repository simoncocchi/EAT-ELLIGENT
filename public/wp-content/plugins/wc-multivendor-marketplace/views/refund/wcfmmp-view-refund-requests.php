<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Refund Requests List View
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/views/refund
 * @version   1.0.0
 */
 
global $WCFM, $WCFMmp;

if( !apply_filters( 'wcfm_is_allow_refund_requests', true ) ) {
	wcfm_restriction_message_show( "Refund Requests" );
	return;
}

?>
<div class="collapse wcfm-collapse" id="wcfm_payments_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-retweet"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Refund Requests', 'wc-multivendor-marketplace' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Refund Requests', 'wc-multivendor-marketplace' ); ?></h2>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
		<?php do_action( 'before_wcfm_refund_requests' ); ?>
		
		<div class="wcfm_refunds_filter_wrap wcfm_filters_wrap">
		  <select name="status_type" id="dropdown_status_type" style="width: 160px;">
				<option value=""><?php  _e( 'Show all ..', 'wc-frontend-manager' ); ?></option>
				<option value="completed"><?php  _e( 'Approved', 'wc-frontend-manager' ); ?></option>
				<option value="requested" selected><?php  _e( 'Requested', 'wc-frontend-manager' ); ?></option>
				<option value="cancelled"><?php  _e( 'Cancelled', 'wc-frontend-manager' ); ?></option>
			</select>
			
			<?php 
			if( !wcfm_is_vendor() ) {
				$vendor_arr = array();
				$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																									"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																									 ) );
			}
			?>
		</div>
			
		<form metod="post" id="wcfm_refund_requests_manage_form">
			<div class="wcfm-container">
				<div id="wcfm_refund_requests_listing_expander" class="wcfm-content">
					<table id="wcfm-refund-requests" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Requests', 'wc-multivendor-marketplace' ); ?>"></span></th>
								<th><?php _e( 'Request ID', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Order ID', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
								<th><?php _e( 'Amount', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Type', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Reason', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Date', 'wc-multivendor-marketplace' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Requests', 'wc-multivendor-marketplace' ); ?>"></span></th>
								<th><?php _e( 'Request ID', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Order ID', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
								<th><?php _e( 'Amount', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Type', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Reason', 'wc-multivendor-marketplace' ); ?></th>
								<th><?php _e( 'Date', 'wc-multivendor-marketplace' ); ?></th>
							</tr>
						</tfoot>
					</table>
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div>
			
			<?php if( apply_filters( 'wcfm_is_allow_refund_requests_action', true ) && !wcfm_is_vendor() ) { ?>
				<div class="wcfm-container">
					<div id="wcfm_withdrawal_requests_actions_expander" class="wcfm-content">
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_refund_requests_fields_wcfmmp', array(
																																																												"refund_note" => array('label' => __('Note', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea', 'label_class' => 'wcfm_title' ),
																																																											) ) );
						?>
						<div class="wcfm-clearfix"></div>
				
						<div id="wcfm_products_simple_submit" class="wcfm_form_simple_submit_wrapper">
							<div class="wcfm-message" tabindex="-1"></div>
							<input type="submit" name="refund-requests-data" value="<?php _e( 'Reject', 'wc-frontend-manager' ); ?>" id="wcfm_refund_requests_cancel_button" class="wcfm_submit_button" />
							<input type="submit" name="refund-requests-data" value="<?php _e( 'Approve', 'wc-multivendor-marketplace' ); ?>" id="wcfm_refund_requests_approve_button" class="wcfm_submit_button" />
						</div>
						<div class="wcfm-clearfix"></div>
					</div>
				</div>
			<?php } ?>
		</form>
		<?php
		do_action( 'after_wcfm_refund_requests' );
		?>
	</div>
</div>