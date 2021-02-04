<?php
/**
 * WCFMu plugin view
 *
 * WCFM WP Job Manager Applications view
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/listings
 * @version   6.0.5
 */
 
global $WCFM;

$wcfm_is_allow_applications = apply_filters( 'wcfm_is_allow_listings', true );
if( !$wcfm_is_allow_applications ) {
	wcfm_restriction_message_show( "Applications" );
	return;
}
?>

<div class="collapse wcfm-collapse" id="wcfm_applications_listing">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-tie"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Applications', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_applications' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <div class="wcfm_applications_filter_wrap wcfm_filters_wrap">
			<?php
			if( apply_filters( 'wcfm_is_coupons_vendor_filter', true ) ) {
				$is_marketplace = wcfm_is_marketplace();
				if( $is_marketplace ) {
					if( !wcfm_is_vendor() ) {
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_vendor" => array( 'type' => 'select', 'options' => array(), 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
					}
				}
			}
			?>
		</div>
	  
		<div class="wcfm-container">
			<div id="wcfm_applications_listing_expander" class="wcfm-content">
				<table id="wcfm-applications" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php _e( 'Status', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Candidate', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Job', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Rating', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Notes', 'wp-job-manager' ); ?></th>
							<th><?php _e( 'Attachment(s)', 'wp-job-manager' ); ?></th>
							<th><?php _e( 'Posted', 'wp-job-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_applications_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Action', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><?php _e( 'Status', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Candidate', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Job', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Rating', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Notes', 'wp-job-manager' ); ?></th>
							<th><?php _e( 'Attachment(s)', 'wp-job-manager' ); ?></th>
							<th><?php _e( 'Posted', 'wp-job-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_applications_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Action', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_applications' );
		?>
	</div>
</div>