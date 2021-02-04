<?php
/**
 * WCFMu plugin view
 *
 * WCFM Enquiry My Account view
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/enquiry
 * @version   5.0.8
 */
 
global $WCFM, $wpdb;

if( !apply_filters( 'wcfm_is_pref_enquiry', true ) || !apply_filters( 'wcfm_is_allow_enquiry', true ) ) {
	wcfm_restriction_message_show( "Inqueries" );
	return;
}

define( 'WCFM_ENQUERY_LOOP', true );

$wcfm_myac_modified_endpoints = wcfm_get_option( 'wcfm_myac_endpoints', array() );
$wcfm_myaccount_inquiry_endpoint = ! empty( $wcfm_myac_modified_endpoints['inquiry'] ) ? $wcfm_myac_modified_endpoints['inquiry'] : 'inquiry';
$wcfm_myaccount_view_inquiry_endpoint = ! empty( $wcfm_myac_modified_endpoints['view-inquiry'] ) ? $wcfm_myac_modified_endpoints['view-inquiry'] : 'view-inquiry';

if( is_user_logged_in() ) {
	$enquiry_query = "SELECT * FROM {$wpdb->prefix}wcfm_enquiries AS commission";
	$enquiry_query .= " WHERE 1 = 1";
	$enquiry_query .= " AND `customer_id` = " . get_current_user_id();
	$enquiry_query .= " ORDER BY commission.`ID` DESC";
	
	$wcfm_inqueries = $wpdb->get_results( $enquiry_query );
	
	$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
	if ( $myaccount_page_id ) {
		$myaccount_page_url = trailingslashit( get_permalink( $myaccount_page_id ) );
	}
	?>
	<div class="touch-scroll-table">
		<table class="woocommerce-enquiry-table woocommerce-MyAccount-enquiry shop_table shop_table_responsive my_account_enquiry account-enquiry-table">
			<thead>
				<tr>
					<th class="woocommerce-enquiry-table__header woocommerce-enquiry-table__header-enquiry-number"><span class="nobr"><?php _e( 'Query', 'wc-frontend-manager' ); ?></span></th>
					<th class="woocommerce-enquiry-table__header woocommerce-enquiry-table__header-enquiry-category"><span class="nobr"><?php _e( 'Product', 'wc-frontend-manager' ); ?></span></th>
					<th class="woocommerce-enquiry-table__header woocommerce-enquiry-table__header-enquiry-priority"><span class="nobr"><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></span></th>
					<?php if( !wcfm_is_mobile() ) { ?>
					  <th class="woocommerce-enquiry-table__header woocommerce-enquiry-table__header-enquiry-priority"><span class="nobr"><?php _e( 'Additional Info', 'wc-frontend-manager' ); ?></span></th>
					<?php } ?>
					<th class="woocommerce-enquiry-table__header woocommerce-enquiry-table__header-enquiry-actions"><span class="nobr"><?php _e( 'Actions', 'wc-frontend-manager' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php if( !empty( $wcfm_inqueries ) ) { foreach( $wcfm_inqueries as $wcfm_enquiry ) { ?>
					<tr class="woocommerce-enquiry-table__row woocommerce-enquiry-table__row--status-completed enquiry">
						<td class="woocommerce-enquiry-table__cell woocommerce-enquiry-table__cell-enquiry-number" data-title="<?php _e( 'Query', 'wc-frontend-manager' ); ?>">
							<a href="<?php echo $myaccount_page_url . $wcfm_myaccount_view_inquiry_endpoint . '/' . $wcfm_enquiry->ID; ?>"><?php echo $wcfm_enquiry->enquiry; ?></a>
						</td>
						<td class="woocommerce-enquiry-table__cell woocommerce-enquiry-table__cell-enquiry-category" data-title="<?php _e( 'Product', 'wc-frontend-manager' ); ?>">
							<?php if( $wcfm_enquiry->product_id ) { echo '<a class="wcfm-enquiry-product" target="_blank" href="' . get_permalink($wcfm_enquiry->product_id) . '">' . get_the_title($wcfm_enquiry->product_id) . '</a>'; } else { echo '&ndash;'; } ?>
						</td>
						<td class="woocommerce-enquiry-table__cell woocommerce-enquiry-table__cell-enquiry-priority" data-title="<?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?>">
							<?php if( $WCFM->is_marketplace && $wcfm_enquiry->vendor_id ) { echo wcfm_get_vendor_store( $wcfm_enquiry->vendor_id ); } else { echo '&ndash;'; } ?>
						</td>
						<?php if( !wcfm_is_mobile() ) { ?>
							<td class="woocommerce-enquiry-table__cell woocommerce-enquiry-table__cell-enquiry-priority" data-title="<?php _e( 'Additional Info', 'wc-frontend-manager' ); ?>">
								<?php
								$additional_info = '';
								$wcfm_enquiry_meta_values = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcfm_enquiries_meta WHERE `enquiry_id` = " . $wcfm_enquiry->ID);
								if( !empty( $wcfm_enquiry_meta_values ) ) {
									foreach( $wcfm_enquiry_meta_values as $wcfm_enquiry_meta_value ) {
										$additional_info .= __( $wcfm_enquiry_meta_value->key, 'wc-frontend-manager' ) . ': ' . $wcfm_enquiry_meta_value->value . '<br />';
									}
								} else {
									$additional_info = '&ndash;';
								}
								echo $additional_info;
								?>
							</td>
						<?php } ?>
						<td class="woocommerce-enquiry-table__cell woocommerce-enquiry-table__cell-enquiry-actions" data-title="<?php _e( 'Actions', 'wc-frontend-manager' ); ?>">
							<a href="<?php echo $myaccount_page_url . $wcfm_myaccount_view_inquiry_endpoint . '/' . $wcfm_enquiry->ID; ?>" class="woocommerce-button button view"><?php _e( 'View', 'wc-frontend-manager' ); ?></a>													
						</td>
					</tr>
				<?php } } else { ?>
					<tr class="woocommerce-enquiry-table__row woocommerce-enquiry-table__row--status-completed enquiry">
						<td class="woocommerce-enquiry-table__cell woocommerce-enquiry-table__cell-enquiry-no" colspan="3" data-title="<?php _e( 'Query', 'wc-frontend-manager' ); ?>">
							<?php _e( 'You do not have any enquiry yet!', 'wc-frontend-manager' ); ?>
						</td>
						<td></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php
}