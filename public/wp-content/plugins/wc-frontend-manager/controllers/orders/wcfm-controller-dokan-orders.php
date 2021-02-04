<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Dokan Orders Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   3.3.0
 */

class WCFM_Orders_Dokan_Controller {
	
	private $vendor_id;
	
	public function __construct() {
		global $WCFM;
		
		$this->vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = 10;
		$offset = 0;
		
		if( isset( $_POST['length'] ) ) $length = wc_clean($_POST['length']);
		if( isset( $_POST['start'] ) ) $offset = wc_clean($_POST['start']);
		
		$user_id = $this->vendor_id;
		
		$can_view_orders = apply_filters( 'wcfm_is_allow_order_details', true );
		$group_manager_filter = apply_filters( 'wcfm_orders_group_manager_filter', '', 'seller_id' );
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'order_id';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';

		$items_per_page = $length;

		$sql = 'SELECT COUNT(commission.id) FROM ' . $wpdb->prefix . 'dokan_orders AS commission';

		$sql .= ' WHERE 1=1';
		
		if( $group_manager_filter ) {
			$sql .= $group_manager_filter;
		} else {
			$sql .= " AND `seller_id` = {$this->vendor_id}";
		}
		
		$order_status = ! empty( $_POST['order_status'] ) ? sanitize_text_field( $_POST['order_status'] ) : 'all';
		if( $order_status != 'all' ) {
			$sql .= " AND `order_status` = 'wc-{$order_status}'";
		}

		// check if it is a search
		if ( ! empty( $_POST['search']['value'] ) ) {
			$order_id = absint( $_POST['search']['value'] );
			if( function_exists( 'wc_sequential_order_numbers' ) ) { $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id ); }

			$sql .= " AND `order_id` = {$order_id}";

		} else {

			/*if ( ! empty( $_POST['m'] ) ) {

				$year  = absint( substr( $_POST['m'], 0, 4 ) );
				$month = absint( substr( $_POST['m'], 4, 2 ) );

				$time_filter = " AND MONTH( commission.time ) = {$month} AND YEAR( commission.time ) = {$year}";

				$sql .= $time_filter;
			}

			if ( ! empty( $_POST['commission_status'] ) ) {
				$commission_status = esc_sql( $_POST['commission_status'] );

				$status_filter = " AND `status` = '{$commission_status}'";

				$sql .= $status_filter;
			}*/
		}
		
		$total_items = $wpdb->get_var( $sql );
		if( !$total_items ) $total_items = 0;

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'dokan_orders AS commission';

		$sql .= ' WHERE 1=1';

		if( $group_manager_filter ) {
			$sql .= $group_manager_filter;
		} else {
			$sql .= " AND `seller_id` = {$this->vendor_id}";
		}
		
		$order_status = ! empty( $_POST['order_status'] ) ? sanitize_text_field( $_POST['order_status'] ) : 'all';
		if( $order_status != 'all' ) {
			$sql .= " AND `order_status` = 'wc-{$order_status}'";
		}

		// check if it is a search
		if ( ! empty( $_POST['search']['value'] ) ) {
			$order_id = absint( $_POST['search']['value'] );
			if( function_exists( 'wc_sequential_order_numbers' ) ) { $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id ); }

			$sql .= " AND `order_id` = {$order_id}";

		} else {

			/*if ( ! empty( $_POST['m'] ) ) {
				$sql .= $time_filter;
			}

			if ( ! empty( $_POST['commission_status'] ) ) {
				$sql .= $status_filter;
			}*/
		}

		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";

		$sql .= " LIMIT {$items_per_page}";

		$sql .= " OFFSET {$offset}";
		
		$wcfm_orders_array = $wpdb->get_results( $sql );
		
		$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
		
		// Generate Products JSON
		$wcfm_orders_json = '';
		$wcfm_orders_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $total_items . ',
															"recordsFiltered": ' . $total_items . ',
															"data": ';
		if(!empty($wcfm_orders_array)) {
			$index = 0;
			$wcfm_orders_json_arr = array();
			foreach($wcfm_orders_array as $wcfm_orders_single) {
				$the_order = wc_get_order( $wcfm_orders_single->order_id );
				
				if( !is_a( $the_order, 'WC_Order' ) ) continue;
				
				$order_currency = $the_order->get_currency();
				
				// Status
				$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_order_status_display', '<span class="order-status tips wcicon-status-' . sanitize_title( $the_order->get_status() ) . ' text_tip" data-tip="' . wc_get_order_status_name( $the_order->get_status() ) . '"></span>', $the_order );
				
				// Custom Column Support After
				$wcfm_orders_json_arr = apply_filters( 'wcfm_orders_custom_columns_data_after', $wcfm_orders_json_arr, $index, $wcfm_orders_single->order_id, $wcfm_orders_single, $the_order );
				
				// Order
				if( apply_filters( 'wcfm_allow_view_customer_name', true ) ) {
					$user_info = array();
					if ( $the_order->get_user_id() ) {
						$user_info = get_userdata( $the_order->get_user_id() );
					}
	
					if ( ! empty( $user_info ) ) {
	
						$username = '';
	
						if ( $user_info->first_name || $user_info->last_name ) {
							$username .= esc_html( sprintf( _x( '%1$s %2$s', 'full name', 'wc-frontend-manager' ), ucfirst( $user_info->first_name ), ucfirst( $user_info->last_name ) ) );
						} else {
							$username .= esc_html( ucfirst( $user_info->display_name ) );
						}
	
					} else {
						if ( $the_order->get_billing_first_name() || $the_order->get_billing_last_name() ) {
							$username = trim( sprintf( _x( '%1$s %2$s', 'full name', 'wc-frontend-manager' ), $the_order->get_billing_first_name(), $the_order->get_billing_last_name() ) );
						} else if ( $the_order->get_billing_company() ) {
							$username = trim( $the_order->get_billing_company() );
						} else {
							$username = __( 'Guest', 'wc-frontend-manager' );
						}
					}
					
					$username = apply_filters( 'wcfm_order_by_user', $username, $wcfm_orders_single->order_id );
				} else {
					$username = __( 'Guest', 'wc-frontend-manager' );
				}

				if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $wcfm_orders_single->order_id ) ) {
					$wcfm_orders_json_arr[$index][] =  '<a href="' . get_wcfm_view_order_url($wcfm_orders_single->order_id, $the_order) . '" class="wcfm_dashboard_item_title">#' . esc_attr( $the_order->get_order_number() ) . '</a>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username;
				} else {
					$wcfm_orders_json_arr[$index][] =  '<span class="wcfm_dashboard_item_title">#' . esc_attr( $the_order->get_order_number() ) . '</span>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username;
				}
				
				// Purchased
				$order_item_details = '<div class="order_items" cellspacing="0">';
				$items = $the_order->get_items();
				$total_quatity = 0;
				foreach ($items as $key => $item) {
					if( version_compare( WC_VERSION, '4.4', '<' ) ) {
						$product = $the_order->get_product_from_item( $item );
					} else {
						$product = $item->get_product();
					}
					$total_quatity += $item->get_quantity();
					$item_meta_html = strip_tags( wc_display_item_meta( $item, array(
																																					'before'    => "\n- ",
																																					'separator' => "\n- ",
																																					'after'     => "",
																																					'echo'      => false,
																																					'autop'     => false,
																																				) ) );
				
					$order_item_details .= '<div class=""><span class="qty">' . $item->get_quantity() . 'x</span><span class="name">' . $item->get_name();
					if ( ! empty( $item_meta_html ) ) $order_item_details .= '<span class="img_tip" data-tip="' . $item_meta_html . '"></span>';
					$order_item_details .= '</td></div>';
				}
				$order_item_details .= '</div>';
				$wcfm_orders_json_arr[$index][] =  '<a href="#" class="show_order_items">' . apply_filters( 'woocommerce_admin_order_item_count', sprintf( _n( '%d item', '%d items', $the_order->get_item_count(), 'wc-frontend-manager' ), $the_order->get_item_count() ), $the_order ) . '</a>' . $order_item_details;
				
				// Quantity
				$wcfm_orders_json_arr[$index][] =  $total_quatity;
				
				// Billing Address
				$billing_address = '&ndash;';
				if( apply_filters( 'wcfm_allow_customer_billing_details', true ) ) {
					if ( $the_order->get_formatted_billing_address() ) {
						$billing_address = wp_kses( $the_order->get_formatted_billing_address(), array( 'br' => array() ) );
					}
				}
				$wcfm_orders_json_arr[$index][] = "<div style='text-align:left;float:left'>" . $billing_address . "</div>"; 
				
				// Shipping Address
				$shipping_address = '&ndash;';
				if( apply_filters( 'wcfm_allow_customer_shipping_details', true ) ) {
					if ( $the_order->get_formatted_shipping_address() ) {
						$shipping_address = wp_kses( $the_order->get_formatted_shipping_address(), array( 'br' => array() ) );
					}
				}
				$wcfm_orders_json_arr[$index][] = "<div style='text-align:left;float:left'>" . $shipping_address . "</div>";
				
				// Gross Sales
				$gross_sales  = (float) $the_order->get_total();
				$total_refund = (float) $the_order->get_total_refunded();
				$total = '<span class="order_total">' . $the_order->get_formatted_order_total() . '</span>';

				if ( $the_order->get_payment_method_title() ) {
					$total .= '<br /><small class="meta">' . __( 'Via', 'wc-frontend-manager' ) . ' ' . esc_html( $the_order->get_payment_method_title() ) . '</small>';
				}
				$wcfm_orders_json_arr[$index][] =  $total;
				
				// Gross Sales Amount
				$wcfm_orders_json_arr[$index][] =  ($gross_sales - $total_refund);
				
				// Commission
				$commission = $wcfm_orders_single->net_amount;
				$wcfm_orders_json_arr[$index][] =  wc_price( $commission, array( 'currency' => $order_currency ) );
				
				// Commission Amount
				$wcfm_orders_json_arr[$index][] = $commission;
				
				// Additional Info
				$wcfm_orders_json_arr[$index][] = apply_filters( 'wcfm_orders_additonal_data', '&ndash;', $the_order->get_id() );
				
				// Custom Column Support Before
				$wcfm_orders_json_arr = apply_filters( 'wcfm_orders_custom_columns_data_before', $wcfm_orders_json_arr, $index, $wcfm_orders_single->order_id, $wcfm_orders_single, $the_order );
				
				// Date
				$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $the_order->order_date : $the_order->get_date_created();
				$wcfm_orders_json_arr[$index][] = date_i18n( wc_date_format(), strtotime( $order_date ) );
				
				// Action
				$actions = '';
				if( $wcfm_is_allow_order_status_update = apply_filters( 'wcfm_is_allow_order_status_update', true ) ) {
					$order_status = sanitize_title( $the_order->get_status() );
					if( !in_array( $order_status, array( 'failed', 'cancelled', 'refunded', 'completed' ) ) ) $actions = '<a class="wcfm_order_mark_complete wcfm-action-icon" href="#" data-orderid="' . $wcfm_orders_single->order_id . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark as Complete', 'wc-frontend-manager' ) . '"></span></a>';
				}
  	
				if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $wcfm_orders_single->order_id ) ) {
					$actions .= '<a class="wcfm-action-icon" href="' . get_wcfm_view_order_url($wcfm_orders_single->order_id, $the_order) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View Details', 'wc-frontend-manager' ) . '"></span></a>';
				}
				
				$actions = apply_filters ( 'wcfm_orders_module_actions', $actions, $wcfm_orders_single->order_id, $the_order );
				
				$wcfm_orders_json_arr[$index][] =  apply_filters ( 'dokan_orders_actions', $actions, $wcfm_orders_single, $the_order );
				
				$index++;
			}												
		}
		if( !empty($wcfm_orders_json_arr) ) $wcfm_orders_json .= json_encode($wcfm_orders_json_arr);
		else $wcfm_orders_json .= '[]';
		$wcfm_orders_json .= '
													}';
													
		echo $wcfm_orders_json;
	}
}