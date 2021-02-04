<?php
/**
 * WCFM plugin view
 *
 * WCFM Orders Dashboard View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view
 * @version   1.0.0
 */

global $WCFM;

$wcfm_is_allow_orders = apply_filters( 'wcfm_is_allow_orders', true );
if( !$wcfm_is_allow_orders ) {
	wcfm_restriction_message_show( "Orders" );
	return;
}

$order_vendor = ! empty( $_GET['order_vendor'] ) ? sanitize_text_field( $_GET['order_vendor'] ) : '';

//include_once( $WCFM->plugin_path . 'controllers/orders/wcfm-controller-wcmarketplace-orders.php' );
//new WCFM_Orders_WCMarketplace_Controller();

$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
?>

<div class="collapse wcfm-collapse" id="wcfm_orders_listing">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-shopping-cart"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Orders', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		
		<div class="wcfm-container wcfm-top-element-container">
			<?php
			if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
				?>
				<h2><?php _e('Orders Listing', 'wc-frontend-manager' ); ?></h2>
				<?php
			}
			
			if( !wcfm_is_vendor() ) {
				?>
				<div class="wcfm_orders_filter_wrap wcfm_filters_wrap">
					<?php $WCFM->library->wcfm_date_range_picker_field(); ?>
					<?php
					$is_marketplace = wcfm_is_marketplace();
					if( $is_marketplace && ( $is_marketplace == 'wcfmmarketplace' ) ) {
						$vendor_arr = array(); //$WCFM->wcfm_vendor_support->wcfm_get_vendor_list();
						if( $order_vendor ) $vendor_arr = array( $order_vendor => wcfm_get_vendor_store_name($order_vendor) );
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'value' => $order_vendor, 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
					}
					?>
					<?php do_action( 'wcfm_after_orders_filter_wrap' ); ?>
				</div>
				<?php
			}
			
			do_action( 'before_wcfm_orders' );
			
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
						?>
						<span class="wcfm_screen_manager_dummy text_tip" data-tip="<?php wcfmu_feature_help_text_show( 'Screen Manager', false, true ); ?>"><span class="wcfmfa fa-tv"></span></span>
						<?php
					}
				} else {
					?>
					<a class="wcfm_screen_manager text_tip" href="#" data-screen="order" data-tip="<?php _e( 'Screen Manager', 'wc-frontend-manager' ); ?>"><span class="wcfmfa fa-tv"></span></a>
					<?php
				}
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('edit.php?post_type=shop_order'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
				<?php
			}
			
			do_action( 'wcfm_orders_quick_actions' );
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'before_wcfm_orders_container' ); ?>
	  
		<div class="wcfm-container">
			<div id="wwcfm_orders_listing_expander" class="wcfm-content">
				<table id="wcfm-orders" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>                                                                                      
							<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
							<?php do_action( 'wcfm_order_columns_after' ); ?>
							<th><?php printf( apply_filters( 'wcfm_order_label', __( 'Order', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Purchased', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Quantity', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Billing Address', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Shipping Address', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Gross Sales', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Gross Sales Amount', 'wc-frontend-manager' ); ?></th>
							<?php if( $admin_fee_mode ) { ?>
								<th><?php _e( 'Admin Fee', 'wc-frontend-manager' ); ?></th>
							<?php } elseif( wcfm_is_vendor() ) { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_label', __( 'Earning', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } else { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_commission_label', __( 'Vendor Earning', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } ?>
							<?php if( $admin_fee_mode ) { ?>
								<th><?php echo __( 'Admin Fee', 'wc-frontend-manager' ) . ' ' . __( 'Amount', 'wc-frontend-manager'); ?></th>
							<?php } elseif( wcfm_is_vendor() ) { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_label', __( 'Earning', 'wc-frontend-manager' ) ) ); _e( ' Amount', 'wc-frontend-manager'); ?></th>
							<?php } else { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_commission_label', __( 'Vendor Earning', 'wc-frontend-manager' ) ) ); _e( ' Amount', 'wc-frontend-manager'); ?></th>
							<?php } ?>
							<th><?php printf( apply_filters( 'wcfm_orders_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<?php do_action( 'wcfm_order_columns_before' ); ?>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							<th><?php printf( apply_filters( 'wcfm_order_action_label', __( 'Actions', 'wc-frontend-manager' ) ) ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
							<?php do_action( 'wcfm_order_columns_after' ); ?>
							<th><?php printf( apply_filters( 'wcfm_order_label', __( 'Order', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Purchased', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Quantity', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Billing Address', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Shipping Address', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Gross Sales', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Gross Sales Amount', 'wc-frontend-manager' ); ?></th>
							<?php if( $admin_fee_mode ) { ?>
								<th><?php _e( 'Fees', 'wc-frontend-manager' ); ?></th>
							<?php } elseif( wcfm_is_vendor() ) { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_label', __( 'Earning', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } else { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_commission_label', __( 'Vendor Earning', 'wc-frontend-manager' ) ) ); ?></th>
							<?php } ?>
							<?php if( $admin_fee_mode ) { ?>
								<th><?php echo __( 'Admin Fee', 'wc-frontend-manager' ) . ' ' . __( 'Amount', 'wc-frontend-manager'); ?></th>
							<?php } elseif( wcfm_is_vendor() ) { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_label', __( 'Earning', 'wc-frontend-manager' ) ) ); _e( ' Amount', 'wc-frontend-manager'); ?></th>
							<?php } else { ?>
								<th><?php printf( apply_filters( 'wcfm_vendors_earned_commission_label', __( 'Vendor Earning', 'wc-frontend-manager' ) ) ); _e( ' Amount', 'wc-frontend-manager'); ?></th>
							<?php } ?>
							<th><?php printf( apply_filters( 'wcfm_orders_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<?php do_action( 'wcfm_order_columns_before' ); ?>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							<th><?php printf( apply_filters( 'wcfm_order_action_label', __( 'Actions', 'wc-frontend-manager' ) ) ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_orders' );
		?>
	</div>
</div>