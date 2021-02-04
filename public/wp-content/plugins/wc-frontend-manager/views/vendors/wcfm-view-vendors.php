<?php
global $WCFM;

$is_allow_vendors = apply_filters( 'wcfm_is_allow_vendors', true );
if( wcfm_is_vendor() || !$is_allow_vendors ) {
	wcfm_restriction_message_show( "Vendors" );
	return;
}

$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
?>

<div class="collapse wcfm-collapse" id="wcfm_vendors_listing">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-alt"></span>
		<span class="wcfm-page-heading-text"><?php echo apply_filters( 'wcfm_vendor_listing_title', apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendors', 'wc-frontend-manager') ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_vendors' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php echo apply_filters( 'wcfm_vendor_listing_heading', apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendors', 'wc-frontend-manager') . ' ' . __( 'Listing', 'wc-frontend-manager' ) ); ?></h2>
			<?php
			if( ($WCFM->is_marketplace == 'wcfmmarketplace' ) && apply_filters( 'wcfm_add_new_vendor_sub_menu', true ) ) {
				echo '<a id="add_new_vendor_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_vendors_new_url().'" data-tip="' . __('Add New', 'wc-frontend-manager') . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-alt"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager') . '</span></a>';
				echo '<a id="add_new_vendor_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_messages_url( 'vendor_approval' ).'" data-tip="' . __('Pending', 'wc-frontend-manager') . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendors', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-times"></span><span class="text">' . __( 'Pending', 'wc-frontend-manager') . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') . '</span></a>';
				
				do_action( 'wcfm_vendors_quick_actions' );
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
		
		<div class="wcfm_vendors_filter_wrap wcfm_filters_wrap">
			<?php
			$is_marketplace = wcfm_is_marketplace();
			if( $wcfm_is_products_vendor_filter = apply_filters( 'wcfm_is_vendors_vendor_filter', true ) ) {
				if( $is_marketplace ) {
					if( !wcfm_is_vendor() ) {
						$vendor_arr = array(); //$WCFM->wcfm_vendor_support->wcfm_get_vendor_list();
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
					}
				}
			}
			
			if( function_exists( 'get_wcfm_memberships' ) && apply_filters( 'wcfm_is_vendors_membership_filter', true ) ) {
				$wcfm_memberships_list = get_wcfm_memberships();
				if( count( $wcfm_memberships_list ) >= 1 ) {
					$membership_arr = array( '' => __( 'Show all ...', 'wc-frontend-manager' ) );
					if( !empty( $wcfm_memberships_list ) ) {
						foreach( $wcfm_memberships_list as $wcfm_membership_list ) {
							$membership_arr[$wcfm_membership_list->ID] = esc_html( $wcfm_membership_list->post_title );
						}
					}
					$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_membership" => array( 'type' => 'select', 'options' => $membership_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
				}
			}
			?>
			<?php $WCFM->library->wcfm_date_range_picker_field(); ?>
		</div>
		
		<div class="wcfm-container">
			<div id="wcfm_vendors_listing_expander" class="wcfm-content">
				<table id="wcfm-vendors" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'Verification', 'wc-frontend-manager' ); ?></th>
						  <th><?php _e( 'Profile', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Store', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Membership', 'wc-frontend-manager' ); ?></th>
							<th><span class="wcfmfa fa-cube text_tip" data-tip="<?php _e( 'Product Limit Stats', 'wc-frontend-manager' ); ?>"></span></th>
							<th><span class="wcfmfa fa-hdd text_tip" data-tip="<?php _e( 'Disk Space Usage Stats', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php printf( apply_filters( 'wcfm_vendors_gross_sales_label', __( 'Gross Sales', 'wc-frontend-manager' ) ) ); ?></th>
							<?php if( $admin_fee_mode ) { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_total_fees_label', __( 'Total Fees', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php printf( apply_filters( 'wcfm_vendors_paid_fees_label', __( 'Paid Fees', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } else { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_commission_label', __( 'Earnings', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php printf( apply_filters( 'wcfm_vendors_received_commission_label', __( 'Withdrawal', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } ?>
							<th><?php printf( apply_filters( 'wcfm_vendors_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Action', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
						  <th><?php _e( 'Verification', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Profile', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Store', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Membership', 'wc-frontend-manager' ); ?></th>
							<th><span class="wcfmfa fa-cube text_tip" data-tip="<?php _e( 'Product Limit Stats', 'wc-frontend-manager' ); ?>"></span></th>
							<th><span class="wcfmfa fa-hdd text_tip" data-tip="<?php _e( 'Disk Space Usage Stats', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php printf( apply_filters( 'wcfm_vendors_gross_sales_label', __( 'Gross Sales', 'wc-frontend-manager' ) ) ); ?></th>
							<?php if( $admin_fee_mode ) { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_total_fees_label', __( 'Total Fees', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php printf( apply_filters( 'wcfm_vendors_paid_fees_label', __( 'Paid Fees', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } else { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_commission_label', __( 'Earnings', 'wc-frontend-manager' ) ) ); ?></th>
								<th><?php printf( apply_filters( 'wcfm_vendors_received_commission_label', __( 'Withdrawal', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } ?>
							<th><?php printf( apply_filters( 'wcfm_vendors_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Action', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_vendors' );
		?>
	</div>
</div>