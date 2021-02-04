<?php
/**
 * WCFM plugin view
 *
 * WCFM Shop Customers View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/customers
 * @version   3.5.0
 */

global $WCFM;

$wcfm_is_allow_manage_customer = apply_filters( 'wcfm_is_allow_manage_customer', true );
if( !$wcfm_is_allow_manage_customer ) {
	wcfm_restriction_message_show( "Customers" );
	return;
}

?>

<div class="collapse wcfm-collapse" id="wcfm_shop_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-circle fa-user-tie"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Customers', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Manage Customers', 'wc-frontend-manager' ); ?></h2>
			
			<?php
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('users.php?role=customer'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
				<?php
			}
			
			if( $has_new = apply_filters( 'wcfm_add_new_customer_sub_menu', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_customers_manage_url().'" data-tip="' . __('Add New Customer', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			
			<?php	echo apply_filters( 'wcfm_customers_limit_label', '' ); ?>
			
			<?php do_action( 'wcfm_customers_quick_actions' ); ?>
			
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <div class="wcfm_customers_filter_wrap wcfm_products_filter_wrap  wcfm_filters_wrap">
			<?php	
			if( apply_filters( 'wcfm_is_customers_vendor_filter', true ) ) {
				$is_marketplace = wcfm_is_marketplace();
				if( $is_marketplace ) {
					if( !wcfm_is_vendor() ) {
						$vendor_arr = array(); //$WCFM->wcfm_vendor_support->wcfm_get_vendor_list();
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
					}
				}
			}
			?>
		</div>
			
		<?php do_action( 'before_wcfm_customers' ); ?>
		
		<div class="wcfm-container">
			<div id="wwcfm_customers_expander" class="wcfm-content">
				<table id="wcfm-shop-customers" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php _e( 'Name', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Username', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Email', 'wc-frontend-manager' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Location', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Orders', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Bookings', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Appointment', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Money Spent', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Last Order', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_customers_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><?php _e( 'Name', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Username', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Email', 'wc-frontend-manager' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Location', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Orders', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Bookings', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Appointment', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Money Spent', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Last Order', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_customers_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_customers' );
		?>
	</div>
</div>