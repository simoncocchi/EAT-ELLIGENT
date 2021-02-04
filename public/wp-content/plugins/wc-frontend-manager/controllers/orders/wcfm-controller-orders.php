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

class WCFM_Orders_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
		$filtering_on = false;
		
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => 'shop_order',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => 'any',
							'suppress_filters' => 0 
						);
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			$wc_order_ids = wc_order_search( $_POST['search']['value'] );
			if( !empty( $wc_order_ids ) ) {
				$args['post__in'] = $wc_order_ids;
			} else {
				$args['post__in'] = array(0);
			}
			$filtering_on = true;
		} else {
			if ( ! empty( $_POST['filter_date_form'] ) && ! empty( $_POST['filter_date_to'] ) ) {
				$fyear  = absint( substr( $_POST['filter_date_form'], 0, 4 ) );
				$fmonth = absint( substr( $_POST['filter_date_form'], 5, 2 ) );
				$fday   = absint( substr( $_POST['filter_date_form'], 8, 2 ) );
				
				$tyear  = absint( substr( $_POST['filter_date_to'], 0, 4 ) );
				$tmonth = absint( substr( $_POST['filter_date_to'], 5, 2 ) );
				$tday   = absint( substr( $_POST['filter_date_to'], 8, 2 ) );
				
				$args['date_query'] = array(
																		'after' => array(
																											'year'  => $fyear,
																											'month' => $fmonth,
																											'day'   => $fday,
																										),
																		'before' => array(
																											'year'  => $tyear,
																											'month' => $tmonth,
																											'day'   => $tday,
																										),
																		'inclusive' => true
																);
				$filtering_on = true;
			}
			
			if ( ! empty( $_POST['order_vendor'] ) ) {
				$sql  = "SELECT order_id FROM {$wpdb->prefix}wcfm_marketplace_orders";
				$sql .= " WHERE 1=1";
				$sql .= " AND `vendor_id` = " . wc_clean($_POST['order_vendor']);
			
				$vendor_orders_list = $wpdb->get_results( $sql );
				if( !empty( $vendor_orders_list ) ) {
					$vendor_orders = array();
					foreach( $vendor_orders_list as $vendor_order_list ) {
						$vendor_orders[] = $vendor_order_list->order_id;
					}
					$args['post__in'] = $vendor_orders;
				} else {
					$args['post__in'] = array(0);
				}
				$filtering_on = true;
			}
		}
		
		if ( ! empty( $_POST['delivery_boy'] ) ) {
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => '_wcfm_delivery_boys',
					'value'   => wc_clean($_POST['delivery_boy']),
					'compare' => 'LIKE'
				)
			);
			$filtering_on = true;
		}
		
		$args = apply_filters( 'wcfm_orders_args', $args );
		
		$wcfm_orders_array = get_posts( $args );
		
		// Get Order Count
		$order_count = 0;
		$filtered_order_count = 0;
		
		$wcfm_orders_counts = wp_count_posts('shop_order');
		foreach($wcfm_orders_counts as $wcfm_orders_count ) {
			$order_count += $wcfm_orders_count;
		}
		
		if ( $filtering_on ) {
			$args['offset'] = 0;
			$args['posts_per_page'] = -1;
			$args['fields'] = 'ids';
			$wcfm_orders_count_array = get_posts( $args );
			$filtered_order_count = count( $wcfm_orders_count_array );
		} else {
			$order_status = ! empty( $_POST['order_status'] ) ? sanitize_text_field( $_POST['order_status'] ) : 'all';
			if( $order_status == 'all' ) {
				$filtered_order_count = $order_count;
			} else {
				foreach($wcfm_orders_counts as $wcfm_orders_count_status => $wcfm_orders_count ) {
					if( $wcfm_orders_count_status == 'wc-' . $order_status ) {
						$filtered_order_count = $wcfm_orders_count;
					}
				}
			}
		}

		if( defined('WCFM_REST_API_CALL') ) {
      return $wcfm_orders_array;
    }
		
		$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
		
		// Generate Products JSON
		$wcfm_orders_json = '';
		$wcfm_orders_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $order_count . ',
															"recordsFiltered": ' . $filtered_order_count . ',
															"data": ';
		if(!empty($wcfm_orders_array)) {
			$index = 0;
			$wcfm_orders_json_arr = array();
			foreach($wcfm_orders_array as $wcfm_orders_single) {
				$the_order = wc_get_order( $wcfm_orders_single );
				
				if( !is_a( $the_order, 'WC_Order' ) ) continue;
				
				$order_currency = $the_order->get_currency();
				
				$order_status = sanitize_title( $the_order->get_status() );
				
				// Status
				$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_order_status_display', '<span class="order-status tips wcicon-status-default wcicon-status-' . sanitize_title( $order_status ) . ' text_tip" data-tip="' . wc_get_order_status_name( $order_status ) . '"></span>', $the_order );
				
				// Custom Column Support After
				$wcfm_orders_json_arr = apply_filters( 'wcfm_orders_custom_columns_data_after', $wcfm_orders_json_arr, $index, $wcfm_orders_single->ID, $wcfm_orders_single, $the_order );
				
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
					
					$username = apply_filters( 'wcfm_order_by_user', $username, $wcfm_orders_single->ID );
				} else {
					$username = __( 'Guest', 'wc-frontend-manager' );
				}
				
				$username = '<span class="wcfm_order_by_customer">' . $username . '</span>';

				if( $wcfm_is_allow_order_details = apply_filters( 'wcfm_is_allow_order_details', true ) ) {
					$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfmmp_order_label_display', '<a href="' . get_wcfm_view_order_url($wcfm_orders_single->ID, $the_order) . '" class="wcfm_dashboard_item_title">#' . esc_attr( $the_order->get_order_number() ) . '</a>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username, $wcfm_orders_single->ID );
				} else {
					$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfmmp_order_label_display', '<span class="wcfm_dashboard_item_title">#' . esc_attr( $the_order->get_order_number() ) . '</span>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username, $wcfm_orders_single->ID );
				}
				
				// Purchased
				$order_item_details = '<div class="order_items order_items_visible" cellspacing="0">';
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
				
					$order_item_details .= '<div class=""><span class="qty">' . $item->get_quantity() . 'x</span><span class="name">' . apply_filters( 'wcfm_order_item_name', $item->get_name(), $item );
					if ( $product && $product->get_sku() ) {
						$order_item_details .= ' (' . __( 'SKU:', 'wc-frontend-manager' ) . ' ' . esc_html( $product->get_sku() ) . ')';
					}
					if ( ! empty( $item_meta_html ) && apply_filters( 'wcfm_is_allow_order_list_item_meta', false ) ) $order_item_details .= '<br />(' . $item_meta_html . ')';
					$order_item_details .= '</span></div>';
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
				$wcfm_orders_json_arr[$index][] = "<div style='text-align:left;'>" . apply_filters( 'wcfm_orderlist_billing_address', $billing_address, $wcfm_orders_single->ID ) . "</div>"; 
				
				// Shipping Address
				$shipping_address = '&ndash;';
				if( apply_filters( 'wcfm_allow_customer_shipping_details', true ) ) {
					if ( ( $the_order->needs_shipping_address() && $the_order->get_formatted_shipping_address() ) || apply_filters( 'wcfm_is_force_shipping_address', false ) ) {
						$shipping_address = wp_kses( $the_order->get_formatted_shipping_address(), array( 'br' => array() ) );
					}
				}
				$wcfm_orders_json_arr[$index][] = "<div style='text-align:left;'>" . apply_filters( 'wcfm_orderlist_shipping_address', $shipping_address, $wcfm_orders_single->ID ) . "</div>";
				
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
				
				// Commission && Commission Amount
				$commission = 0;
				if( $marketplece = wcfm_is_marketplace() ) {
					if( !in_array( $order_status, array( 'failed', 'cancelled', 'refunded', 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) {
						$commission = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_order( $wcfm_orders_single->ID );
						if( $commission ) {
							if( $admin_fee_mode || ( $marketplece == 'dokan' ) ) {
								$commission = $gross_sales - $total_refund - $commission;
							}
							$wcfm_orders_json_arr[$index][] =  wc_price( $commission, array( 'currency' => $order_currency ) );
							$wcfm_orders_json_arr[$index][] =  $commission;
						} else {
							$wcfm_orders_json_arr[$index][] =  __( 'N/A', 'wc-frontend-manager' );
							$wcfm_orders_json_arr[$index][] =  '';
						}
					} else {
						$wcfm_orders_json_arr[$index][] =  __( 'N/A', 'wc-frontend-manager' );
						$wcfm_orders_json_arr[$index][] =  '';
					}
				} else {
					$wcfm_orders_json_arr[$index][] =  wc_price( $commission, array( 'currency' => $order_currency ) );
					$wcfm_orders_json_arr[$index][] =  $commission;
				}
				
				// Additional Info
				$wcfm_orders_json_arr[$index][] = apply_filters( 'wcfm_orders_additonal_data', '&ndash;', $wcfm_orders_single->ID );
				
				// Custom Column Support Before
				$wcfm_orders_json_arr = apply_filters( 'wcfm_orders_custom_columns_data_before', $wcfm_orders_json_arr, $index, $wcfm_orders_single->ID, $wcfm_orders_single, $the_order );
				
				// Date
				$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $the_order->order_date : $the_order->get_date_created();
			if( $order_date ) {
				$wcfm_orders_json_arr[$index][] = $order_date->date_i18n( wc_date_format() . ' ' . wc_time_format() );
			} else {
				$wcfm_orders_json_arr[$index][] = '&ndash;';
			}
				
				// Action
				$actions = '';
				if( $wcfm_is_allow_order_status_update = apply_filters( 'wcfm_is_allow_order_status_update', true ) ) {
					if( !in_array( $order_status, array( 'failed', 'cancelled', 'refunded', 'completed', 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) $actions = '<a class="wcfm_order_mark_complete wcfm-action-icon" href="#" data-orderid="' . $wcfm_orders_single->ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark as Complete', 'wc-frontend-manager' ) . '"></span></a>';
				}
  	
				if( $wcfm_is_allow_order_details = apply_filters( 'wcfm_is_allow_order_details', true ) ) {
					$actions .= '<a class="wcfm-action-icon" href="' . get_wcfm_view_order_url($wcfm_orders_single->ID, $the_order) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View Details', 'wc-frontend-manager' ) . '"></span></a>';
				}
				
				if( !WCFM_Dependencies::wcfmu_plugin_active_check() || !WCFM_Dependencies::wcfm_wc_pdf_invoices_packing_slips_plugin_active_check() ) {
					if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
						$actions .= '<a class="wcfm_pdf_invoice_dummy wcfm-action-icon" href="#" data-orderid="' . $wcfm_orders_single->ID . '"><span class="wcfmfa fa-file-invoice text_tip" data-tip="' . esc_attr__( 'PDF Invoice', 'wc-frontend-manager' ) . '"></span></a>';
					}
				}
				
				$actions = apply_filters ( 'wcfm_orders_module_actions', $actions, $wcfm_orders_single->ID, $the_order );
				
				$wcfm_orders_json_arr[$index][] =  apply_filters ( 'wcfm_orders_actions', $actions, $wcfm_orders_single, $the_order );
				
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