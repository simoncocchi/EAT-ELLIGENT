<?php
/**
 * WCFM plugin view
 *
 * WCFM Shop Customers Details View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/customers
 * @version   3.5.0
 */

global $WCFM, $wp;

if( !apply_filters( 'wcfm_is_allow_manage_customer', true ) || !apply_filters( 'wcfm_is_allow_view_customer', true ) ) {
	wcfm_restriction_message_show( "Customers" );
	return;
}

$customer_id = 0;
$user_name = '&ndash;';
$user_email = '&ndash;';
$first_name = '&ndash;';
$last_name = '&ndash;';
$company_name = '&ndash;';

if( isset( $wp->query_vars['wcfm-customers-details'] ) && !empty( $wp->query_vars['wcfm-customers-details'] ) ) {
	$customer_id = $wp->query_vars['wcfm-customers-details'];
	
	if( $customer_id ) {
		$customer_user = get_userdata( $customer_id );
		$user_name = $customer_user->user_login;
		$user_email = $customer_user->user_email;
		$first_name = $customer_user->first_name;
		$last_name = $customer_user->last_name;
		$company_name = get_user_meta( $customer_id, 'billing_company', true );
	} else {
		wcfm_restriction_message_show( "Invalid Customer" );
		return;
	}
} else {
	wcfm_restriction_message_show( "Invalid Customer" );
	return;
}

/*if( wcfm_is_vendor() ) {
	$is_customer_for_vendor = $WCFM->wcfm_vendor_support->wcfm_is_component_for_vendor( $customer_id, 'customer' );
	if( !$is_customer_for_vendor ) {
		if( apply_filters( 'wcfm_is_show_customer_restrict_message', true, $customer_id ) ) {
			wcfm_restriction_message_show( "Restricted Customer" );
		} else {
			echo apply_filters( 'wcfm_show_custom_customer_restrict_message', '', $customer_id );
		}
		return;
	}
}*/

?>

<div class="collapse wcfm-collapse" id="wcfm_customers_listing">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-circle fa-user-tie"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Customer Details', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2>
			<?php 
			echo apply_filters( 'wcfm_customers_display_name_data',  $first_name . ' ' . $last_name, $customer_id ); 
			if( apply_filters( 'wcfm_allow_order_customer_details', true ) ) {
				echo '&nbsp('.$user_email.')';
			}
			?>
			</h2>
			
			<label class="wcfm_customer_details_change_customer">
				<?php
				$customer_user_role = apply_filters( 'wcfm_customer_user_role', array( 'customer', 'subscriber', 'client', 'bbp_participant' ) );
				$args = array(
								'role__in'     => $customer_user_role,
								'orderby'      => 'ID',
								'order'        => 'ASC',
								'offset'       => -1,
								'number'       => 0,
								'count_total'  => false
							 ); 
				$args = apply_filters( 'wcfm_get_customers_args', $args );
				$wcfm_customers_array = get_users( $args );
				$customers_arr = array();
				if(!empty($wcfm_customers_array)) {
					foreach( $wcfm_customers_array as $wcfm_customers_single ) {
						if ( $wcfm_customers_single->last_name && $wcfm_customers_single->first_name ) {
							$customers_arr[$wcfm_customers_single->ID] = apply_filters( 'wcfm_customers_display_name_data', $wcfm_customers_single->first_name . ' ' . $wcfm_customers_single->last_name, $wcfm_customers_single->ID );
						} else {
							$customers_arr[$wcfm_customers_single->ID] = $wcfm_customers_single->display_name;
						}
					}
				}
				$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																									"dropdown_customer" => array( 'type' => 'select', 'options' => $customers_arr, 'attributes' => array( 'style' => 'width: 250px;' ), 'value' => $customer_id )
																									 ) );
				?>
			</label>
			
			<?php
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('user-new.php'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
				<?php
			}
			
			echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_customers_url().'" data-tip="' . __('Manage Customers', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-circle"></span></a>';
			
			if( apply_filters( 'wcfm_is_allow_edit_customer', true ) && apply_filters( 'wcfm_is_vendor_customer', true, $customer_id ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_customers_manage_url($customer_id).'" data-tip="' . __('Edit Customer', 'wc-frontend-manager') . '"><span class="wcfmfa fa-edit"></span></a>';
			}
			
			if( apply_filters( 'wcfm_add_new_customer_sub_menu', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_customers_manage_url().'" data-tip="' . __('Add New Customer', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  <input type="hidden" name="wcfm_customer_id" value="<?php echo $customer_id; ?>" />
	  
	  <?php do_action( 'begin_wcfm_customers_details' ); ?>
	  
	  <?php if( apply_filters( 'wcfm_is_pref_stats_box', true ) ) { ?>
			<div class="wcfm_dashboard_stats">
				<div class="wcfm_dashboard_stats_block">
				  <a href="#" onclick="return false;">
						<span class="wcfmfa fa-money fa-money-bill-alt"></span>
						<div>
							<strong>
								<?php
								$customers_orders_stat = $WCFM->wcfm_customer->wcfm_get_customers_orders_stat( $customer_id );
								echo apply_filters( 'wcfm_customers_money_spent_data', wc_price( $customers_orders_stat['total_sales'] ), $customer_id );
								?>
							</strong><br />
							<?php _e( 'total money spent', 'wc-frontend-manager' ); ?>
						</div>
					</a>
				</div>
				
				<div class="wcfm_dashboard_stats_block">
				  <a href="#" onclick="return false;">
						<span class="wcfmfa fa-cart-plus"></span>
						<div>
							<?php 
							$total_order = apply_filters( 'wcfm_customers_total_orders_data', $customers_orders_stat['total_order'], $customer_id );
							printf( _n( "<strong>%s order</strong><br />", "<strong>%s orders</strong><br />", $total_order, 'wc-frontend-manager' ), $total_order ); 
							?>
							<?php _e( 'total order placed', 'wc-frontend-manager' ); ?>
						</div>
					</a>
				</div>
			</div>
			<div class="wcfm-clearfix"></div>
		<?php } ?>
		
		<?php do_action( 'begin_wcfm_customers_details_data' ); ?>
		
		<!-- collapsible -->
		<div class="wcfm-container">
			<div id="customers_details_general_expander" class="wcfm-content">
				<div class="wcfm_clearfix"></div>
				<?php
				do_action( 'before_wcfm_customer_fields_general', $customer_id );
				
				$customer_fields_general = apply_filters( 'wcfm_customer_fields_general', array(  
																																			"user_email" => array( 'label' => __('Email', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $user_email),
																																			"first_name" => array( 'label' => __('First Name', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $first_name),
																																			"last_name" => array( 'label' => __('Last Name', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $last_name),
																																			"company_name" => array( 'label' => __('Company Name', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $company_name),
																																			"customer_id" => array('type' => 'hidden', 'value' => $customer_id )
																																	 ), $customer_id );
				
				if( !apply_filters( 'wcfm_allow_view_customer_email', true ) || !apply_filters( 'wcfm_allow_view_customer_billing_email', true ) ) {
					unset( $customer_fields_general['user_email'] );
				}
			
				$WCFM->wcfm_fields->wcfm_generate_form_field( $customer_fields_general );
				
				
				do_action( 'after_wcfm_customer_general_details', $customer_id );
				
				?>
			</div>
		</div>
		<div class="wcfm_clearfix"></div><br />
		<!-- end collapsible -->
		
		<?php do_action( 'end_wcfm_customers_details_data' ); ?>
		
		<?php do_action( 'begin_wcfm_customers_details_bookings' ); ?>
	  
		<?php if( wcfm_is_booking() && ( current_user_can( 'manage_bookings_settings' ) || current_user_can( 'manage_bookings' ) ) && apply_filters( 'wcfm_is_allow_booking_list', true ) && apply_filters( 'wcfm_is_allow_customer_details_bookings', true ) ) { ?>
			<div class="page_collapsible" id="wcfm_customers_bookings_head"><span class="wcfmfa fa-calendar-check"></span><span class="dashboard_widget_head">&nbsp;<?php _e('Bookings', 'wc-frontend-manager'); ?></span></div>
			<div class="wcfm-container">
				<div id="wcfm_customers_bookings_listing_expander" class="wcfm-content">
					<table id="wcfm-customers-details-bookings" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Booking', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Product', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Order', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Start Date', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'End Date', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Booking', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Product', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Order', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Start Date', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'End Date', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
							</tr>
						</tfoot>
					</table>
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div><br />
		<?php } ?>
		
		<?php do_action( 'end_wcfm_customers_details_bookings' ); ?>
		
		<?php do_action( 'begin_wcfm_customers_details_appointments' ); ?>
	  
		<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() && WCFMu_Dependencies::wcfm_wc_appointments_active_check() && current_user_can( 'manage_appointments' ) && apply_filters( 'wcfm_is_allow_appointment_list', true ) && apply_filters( 'wcfm_is_allow_customer_details_appointments', true ) ) { ?>
			<div class="page_collapsible" id="wcfm_customers_appointments_head"><span class="wcfmfa fa-clock"></span><span class="dashboard_widget_head">&nbsp;<?php _e('Appointments', 'wc-frontend-manager'); ?></span></div>
			<div class="wcfm-container">
				<div id="wcfm_customers_appointments_listing_expander" class="wcfm-content">
					<table id="wcfm-customers-details-appointments" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Appointment', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Product', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Order', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Start Date', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'End Date', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Actions', 'wc-frontend-manager-ultimate' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Appointment', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Product', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Order', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Start Date', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'End Date', 'wc-frontend-manager-ultimate' ); ?></th>
								<th><?php _e( 'Actions', 'wc-frontend-manager-ultimate' ); ?></th>
							</tr>
						</tfoot>
					</table>
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div><br />
		<?php } ?>
		
		<?php do_action( 'end_wcfm_customers_details_appointments' ); ?>
		
		<?php do_action( 'begin_wcfm_customers_details_orders' ); ?>
	  
		<?php if( apply_filters( 'wcfm_is_allow_orders', true ) && apply_filters( 'wcfm_is_allow_customer_details_orders', true ) ) { ?>
			<div class="page_collapsible" id="wcfm_customers_orders_head"><span class="wcfmfa fa-cart-plus"></span><span class="dashboard_widget_head">&nbsp;<?php _e('Orders', 'wc-frontend-manager'); ?></span></div>
			<div class="wcfm-container">
				<div id="wcfm_customers_orders_listing_expander" class="wcfm-content">
					<table id="wcfm-customers-details-orders" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>                                                                                      
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Order', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Purchased', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Gross Sales', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
								<th><?php _e( 'Order', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Purchased', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Gross Sales', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
								<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
							</tr>
						</tfoot>
					</table>
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div><br />
		<?php } ?>
		
		<?php do_action( 'end_wcfm_customers_details_orders' ); ?>
		
		<?php do_action( 'after_wcfm_customers_details' ); ?>
	</div>
</div>