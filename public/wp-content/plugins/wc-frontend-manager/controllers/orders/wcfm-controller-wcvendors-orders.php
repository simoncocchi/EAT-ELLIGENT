<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Orders Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   1.0.0
 */

class WCFM_Orders_WCVendors_Controller {
	
	private $vendor_id;
	
	public function __construct() {
		global $WCFM;
		
		$this->vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $start_date, $end_date;
		
		if( !class_exists( 'WCV_Orders' ) ) {
			include_once( wcv_plugin_dir . 'classes/front/orders/class-orders.php');
			new WCV_Orders;
		}
		
		$length = 10;
		$offset = 0;
		
		if( isset( $_POST['length'] ) ) $length = wc_clean($_POST['length']);
		if( isset( $_POST['start'] ) ) $offset = wc_clean($_POST['start']);
		
		$user_id = $this->vendor_id;
		
		$can_view_orders = apply_filters( 'wcfm_is_allow_order_details', true );
		$group_manager_filter = apply_filters( 'wcfm_orders_group_manager_filter', '', 'vendor_id' );
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'order_id';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';

		$items_per_page = $length;

		$sql = 'SELECT COUNT(commission.id) FROM ' . $wpdb->prefix . 'pv_commission AS commission';

		$sql .= ' WHERE 1=1';

		if( $group_manager_filter ) {
			$sql .= $group_manager_filter;
		} else {
			$sql .= " AND `vendor_id` = {$this->vendor_id}";
		}

		// check if it is a search
		if ( ! empty( $_POST['search']['value'] ) ) {
			$order_id = absint( $_POST['search']['value'] );
			if( function_exists( 'wc_sequential_order_numbers' ) ) { $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id ); }

			$sql .= " AND `order_id` = {$order_id}";

		} else {
			if ( ! empty( $_POST['filter_date_form'] ) && ! empty( $_POST['filter_date_to'] ) ) {
				$start_date = date( 'Y-m-d', strtotime( wc_clean($_POST['filter_date_form']) ) );
				$end_date = date( 'Y-m-d', strtotime( wc_clean($_POST['filter_date_to']) ) );
				$time_filter = " AND DATE( commission.time ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
				$sql .= $time_filter;
			}

			if ( ! empty( $_POST['commission_status'] ) ) {
				$commission_status = wc_clean( $_POST['commission_status'] );

				$status_filter = " AND `status` = '{$commission_status}'";

				$sql .= $status_filter;
			}
		}
		
		$total_items = $wpdb->get_var( $sql );
		if( !$total_items ) $total_items = 0;

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'pv_commission AS commission';

		$sql .= ' WHERE 1=1';

		if( $group_manager_filter ) {
			$sql .= $group_manager_filter;
		} else {
			$sql .= " AND `vendor_id` = {$this->vendor_id}";
		}

		// check if it is a search
		if ( ! empty( $_POST['search']['value'] ) ) {
			$order_id = absint( $_POST['search']['value'] );
			if( function_exists( 'wc_sequential_order_numbers' ) ) { $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id ); }

			$sql .= " AND `order_id` = {$order_id}";

		} else {

			if ( ! empty( $_POST['filter_date_form'] ) && ! empty( $_POST['filter_date_to'] ) ) {
				$sql .= $time_filter;
			}

			if ( ! empty( $_POST['commission_status'] ) ) {
				$sql .= $status_filter;
			}
		}

		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";

		$sql .= " LIMIT {$items_per_page}";

		$sql .= " OFFSET {$offset}";

		$data = $wpdb->get_results( $sql );
		
		$order_summary = $data;
		
		// Generate Products JSON
		$wcfm_orders_json = '';
		$wcfm_orders_json = '{
														"draw": ' . wc_clean($_POST['draw']). ',
														"recordsTotal": ' . $total_items . ',
														"recordsFiltered": ' . $total_items . ',
														"data": ';
		
		if ( !empty( $order_summary ) ) {
			$index = 0;
			$totals = 0;
			$wcfm_orders_json_arr = array();
			
			foreach ( $order_summary as $order ) {
				// Order exists check
				$order_post_title = get_the_title( $order->order_id );
				if( !$order_post_title ) continue;
				
				$the_order = wc_get_order( $order->order_id );
				
				if( !is_a( $the_order, 'WC_Order' ) ) continue;
				
				//$the_order = wc_get_order( $order );
				$valid_items = WCV_Queries::get_products_for_order( $the_order->get_id() );
				$item_product_id = 0;
				$valid = array();
				$needs_shipping = false; 
				$order_currency = $the_order->get_currency();
	
				$items = $the_order->get_items();
	
				foreach ($items as $key => $value) {
					if ( in_array( $value->get_variation_id(), $valid_items) || in_array( $value->get_product_id(), $valid_items ) ) {
						if( ( $order->product_id == $value->get_variation_id() ) || ( $order->product_id == $value->get_product_id() ) ) {
							$valid[$key] = $value;
							$item_product_id = $value->get_product_id();
						}
					} elseif( $value->get_product_id() == 0 ) {
						$_product_id = wc_get_order_item_meta( $key, '_product_id', true );
						$_variation_id = wc_get_order_item_meta( $key, '_variation_id', true );
						if ( in_array( $_product_id, $valid_items ) || in_array( $_variation_id, $valid_items ) ) {
							$valid[$key] = $value;
							$item_product_id = $_product_id;
						}
					}
				}
				
				// Status
				$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_order_status_display', '<span class="order-status tips wcicon-status-' . sanitize_title( $the_order->get_status() ) . ' text_tip" data-tip="' . wc_get_order_status_name( $the_order->get_status() ) . '"></span>', $the_order );
				
				// Custom Column Support After
				$wcfm_orders_json_arr = apply_filters( 'wcfm_orders_custom_columns_data_after', $wcfm_orders_json_arr, $index, $order->ID, $order, $the_order );
				
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
				
					$username = apply_filters( 'wcfm_order_by_user', $username, $the_order->get_id() );
				} else {
					$username = __( 'Guest', 'wc-frontend-manager' );
				}
	
				if( $can_view_orders )
					$wcfm_orders_json_arr[$index][] =  '<a href="' . get_wcfm_view_order_url($the_order->get_id(), $the_order) . '" class="wcfm_order_title">#' . esc_attr( $the_order->get_order_number() ) . '</a>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username;
				else
					$wcfm_orders_json_arr[$index][] =  '#' . esc_attr( $the_order->get_order_number() ) . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username;
				
				// Purchased
				$order_item_details = '<div class="order_items" cellspacing="0">';
				$product_id = '';       
				$total_quatity = 0;
				foreach ($valid as $key => $item) {
					$total_quatity += $item->get_quantity();
					
					// Get variation data if there is any. 
					$variation_detail = !empty( $value->get_variation_id() ) ? WCV_Orders::get_variation_data( $value->get_variation_id() ) : ''; 
				
					$order_item_details .= '<div class=""><span class="qty">' . $item->get_quantity() . 'x</span><span class="name">' . $item->get_name();
					if ( !empty( $variation_detail ) ) $order_item_details .= '<span class="img_tip" data-tip="' . $variation_detail . '"></span>';
					$order_item_details .= '</span></div>';
				}
				$order_item_details .= '</div>';
				$wcfm_orders_json_arr[$index][] = '<a href="#" class="show_order_items">' . sprintf( _n( '%d item', '%d items', $order->qty, 'wc-frontend-manager' ), $order->qty ) . '</a>' . $order_item_details;
				
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
				$gross_sales = 0;
				try {
					foreach( $items as $key => $line_item ) {
						if( $line_item->get_product_id() == 0 ) {
							$_product_id = wc_get_order_item_meta( $key, '_product_id', true );
							$_variation_id = wc_get_order_item_meta( $key, '_variation_id', true );
							if ( ( $_product_id == $order->product_id ) || ( $_variation_id == $order->product_id ) ) {
								$gross_sales += (float) sanitize_text_field( $line_item->get_total() );
								if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
									if(WC_Vendors::$pv_options->get_option( 'give_tax' )) {
										$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
									}
									if(WC_Vendors::$pv_options->get_option( 'give_shipping' )) {
										$gross_sales += (float) $order->total_shipping;
									}
								} else {
									if(get_option('wcvendors_vendor_give_taxes')) {
										$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
									}
									if(get_option('wcvendors_vendor_give_shipping')) {
										$gross_sales += (float) $order->total_shipping;
									}
								}
							}
						} elseif ( ( $line_item->get_variation_id() == $order->product_id ) || ( $line_item->get_product_id() == $order->product_id ) ) {
							$gross_sales += (float) sanitize_text_field( $line_item->get_total() );
							if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
								if(WC_Vendors::$pv_options->get_option( 'give_tax' )) {
									$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
								}
								if(WC_Vendors::$pv_options->get_option( 'give_shipping' )) {
									$gross_sales += (float) $order->total_shipping;
								}
							} else {
								if(get_option('wcvendors_vendor_give_taxes')) {
									$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
								}
								if(get_option('wcvendors_vendor_give_shipping')) {
									$gross_sales += (float) $order->total_shipping;
								}
							}
						}
					}
				} catch (Exception $e) {
					unset( $wcfm_orders_json_arr[$index] );
					continue;
				}
				$wcfm_orders_json_arr[$index][] =  wc_price( $gross_sales, array( 'currency' => $order_currency ) );
				
				// Gross Sales Amount
				$wcfm_orders_json_arr[$index][] =  $gross_sales;
				
				// Commission
				$status = __( 'N/A', 'woocommerce-product-vendors' );

				if ( 'due' === $order->status ) {
					$status = '<span class="wcpv-unpaid-status">' . esc_html__( 'DUE', 'wc-frontend-manager' ) . '</span>';
				}

				if ( 'paid' === $order->status ) {
					$status = '<span class="wcpv-paid-status">' . esc_html__( 'PAID', 'wc-frontend-manager' ) . '</span>';
				}

				if ( 'reversed' === $order->status ) {
					$status = '<span class="wcpv-void-status">' . esc_html__( 'REVERSED', 'wc-frontend-manager' ) . '</span>';
				}
				
				$total = $order->total_due; 
				if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
					if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) {
						$total += $order->total_shipping;
					}
					if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) {
						$total += $order->tax;
					}
				} else {
					if ( get_option('wcvendors_vendor_give_shipping') ) {
						$total += $order->total_shipping;
					}
					if ( get_option('wcvendors_vendor_give_taxes') ) {
						$total += $order->tax;
					}
				}
				$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_vendor_order_total', wc_price( $total, array( 'currency' => $order_currency ) ) . '<br />' . $status, $order->order_id, $order->product_id, $total, $status );
				
				// Commission Amount
				$wcfm_orders_json_arr[$index][] =  $total;
				
				// Additional Info
				$wcfm_orders_json_arr[$index][] = apply_filters( 'wcfm_orders_additonal_data', '&ndash;', $the_order->get_id() );
				
				// Custom Column Support Before
				$wcfm_orders_json_arr = apply_filters( 'wcfm_orders_custom_columns_data_before', $wcfm_orders_json_arr, $index, $order->ID, $order, $the_order );
				
				// Date
				$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $the_order->order_date : $the_order->get_date_created();
				$wcfm_orders_json_arr[$index][] = date_i18n( wc_date_format(), strtotime( $order_date ) );
				
				// Action
				$actions = '';
				if( $wcfm_is_allow_order_status_update = apply_filters( 'wcfm_is_allow_order_status_update', true ) ) {
					$order_status = sanitize_title( $the_order->get_status() );
					if( !in_array( $order_status, array( 'failed', 'cancelled', 'refunded', 'completed' ) ) ) $actions = '<a class="wcfm_order_mark_complete wcfm-action-icon" href="#" data-orderid="' . $order->order_id . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark as Complete', 'wc-frontend-manager' ) . '"></span></a>';
				}
				
				if( $can_view_orders )
					$actions .= '<a class="wcfm-action-icon" href="' . get_wcfm_view_order_url($the_order->get_id(), $the_order) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View Details', 'wc-frontend-manager' ) . '"></span></a>';
				  
				if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
						$actions .= '<a class="wcfm_wcvendors_order_mark_shipped_dummy wcfm-action-icon" href="#" data-orderid="' . $the_order->get_id() . '"><span class="wcfmfa fa-truck text_tip" data-tip="' . esc_attr__( 'Mark Shipped', 'wc-frontend-manager' ) . '"></span></a>';
					}
				}
				  
				$actions = apply_filters ( 'wcfm_orders_module_actions', $actions, $the_order->get_id(), $the_order );
				
				$wcfm_orders_json_arr[$index][] =  apply_filters ( 'wcvendors_orders_actions', $actions, $user_id, $the_order, $item_product_id );
				
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