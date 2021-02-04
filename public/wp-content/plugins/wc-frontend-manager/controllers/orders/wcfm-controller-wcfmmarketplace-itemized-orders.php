<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Orders Controller - WCfM Marketplace
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/orders/
 * @version   5.0.0
 */

class WCFM_Orders_WCFMMarketplace_Controller {
	
	private $vendor_id;
	private $is_vendor_get_tax;
	private $is_vendor_get_shipping;
	
	public function __construct() {
		global $wp, $WCFM, $WCFMmp;
		
		if( wcfm_is_vendor() ) {
			$this->vendor_id   =  $WCFMmp->vendor_id;
		} else {
			if( isset( $_POST['vendor_id'] ) && !empty( $_POST['vendor_id'] ) ) {
				$this->vendor_id = wc_clean($_POST['vendor_id']);
			}
		}
		
		$this->is_vendor_get_tax      =  $WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $this->vendor_id );
		$this->is_vendor_get_shipping =  $WCFMmp->wcfmmp_vendor->is_vendor_get_shipping( $this->vendor_id );
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMmp, $wpdb, $_POST;
		
		$length = 10;
		$offset = 0;
		
		if( isset( $_POST['length'] ) ) $length = wc_clean($_POST['length']);
		if( isset( $_POST['start'] ) ) $offset = wc_clean($_POST['start']);
		
		$user_id = $this->vendor_id;
		
		$can_view_orders = apply_filters( 'wcfm_is_allow_order_details', true );
		$group_manager_filter = apply_filters( 'wcfm_orders_group_manager_filter', '', 'vendor_id' );
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'order_id';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';
		$allowed_status      = get_wcfm_marketplace_active_withdrwal_order_status_in_comma();
		$allowed_status      = apply_filters( 'wcfmp_order_list_allowed_status', $allowed_status ); 

		$items_per_page = $length;

		$sql = 'SELECT COUNT(commission.ID) FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';

		$sql .= ' WHERE 1=1';

		if( $group_manager_filter ) {
			$sql .= $group_manager_filter;
		} else {
			$sql .= " AND `vendor_id` = {$this->vendor_id}";
		}
		if( apply_filters( 'wcfmmp_is_allow_order_status_filter', false ) ) {
			$sql .= " AND commission.order_status IN ({$allowed_status})";
		}
		$sql .= ' AND `is_trashed` = 0';
		
		$sql = apply_filters( 'wcfmmp_order_query', $sql );
		
		$status_filter = '';

		// check if it is a search
		if ( ! empty( $_POST['search']['value'] ) ) {
			$order_id = absint( $_POST['search']['value'] );
			if( function_exists( 'wc_sequential_order_numbers' ) ) { $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id ); }

			$sql .= " AND `order_id` = {$order_id}";

		} else {

			if ( ! empty( $_POST['filter_date_form'] ) && ! empty( $_POST['filter_date_to'] ) ) {
				$start_date = date( 'Y-m-d', strtotime( wc_clean($_POST['filter_date_form']) ) );
				$end_date = date( 'Y-m-d', strtotime( wc_clean($_POST['filter_date_to']) ) );
				$time_filter = " AND DATE( commission.created ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
				$sql .= $time_filter;
			}

			if ( ! empty( $_POST['commission_status'] ) ) {
				$commission_status = wc_clean( $_POST['commission_status'] );
				$status_filter = " AND `withdraw_status` = '{$commission_status}'";
			}
			
			if ( ! empty( $_POST['order_status'] ) ) {
				$order_status = wc_clean( $_POST['order_status'] );
				if( $order_status != 'all' ) {
					$status_filter .= " AND `commission_status` = '{$order_status}'";
				}
			}
			if( $status_filter ) $sql .= $status_filter;
		}
		
		$total_items = $wpdb->get_var( $sql );
		if( !$total_items ) $total_items = 0;
		$total_items = apply_filters( 'wcfm_orders_total_count', $total_items, $this->vendor_id );

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';

		$sql .= ' WHERE 1=1';

		if( $group_manager_filter ) {
			$sql .= $group_manager_filter;
		} else {
			$sql .= " AND `vendor_id` = {$this->vendor_id}";
		}
		if( apply_filters( 'wcfmmp_is_allow_order_status_filter', false ) ) {
			$sql .= " AND commission.order_status IN ({$allowed_status})";
		}
		$sql .= ' AND `is_trashed` = 0';
		
		$sql = apply_filters( 'wcfmmp_order_query', $sql );

		// check if it is a search
		if ( ! empty( $_POST['search']['value'] ) ) {
			$order_id = absint( $_POST['search']['value'] );
			if( function_exists( 'wc_sequential_order_numbers' ) ) { $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id ); }

			$sql .= " AND `order_id` = {$order_id}";

		} else {

			if ( ! empty( $_POST['filter_date_form'] ) && ! empty( $_POST['filter_date_to'] ) ) {
				$sql .= $time_filter;
			}

			if( $status_filter ) $sql .= $status_filter;
		}

		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";

		$sql .= " LIMIT {$items_per_page}";

		$sql .= " OFFSET {$offset}";
		
		$data = $wpdb->get_results( $sql );
		
		$order_summary = $data;
		
		$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
		
		$order_sync  = isset( $WCFMmp->wcfmmp_marketplace_options['order_sync'] ) ? $WCFMmp->wcfmmp_marketplace_options['order_sync'] : 'no';


		if( defined('WCFM_REST_API_CALL') ) {
      return $order_summary;
    }
		
		// Generate Products JSON
		$wcfm_orders_json = '';
		$wcfm_orders_json = '{
														"draw": ' . wc_clean($_POST['draw']) . ',
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
				
				if( apply_filters( 'wcfm_is_show_order_restrict_check', false, $order->order_id, $order->product_id, $order ) ) continue;
				
				$order_currency = $the_order->get_currency();
				$needs_shipping = false; 
	
				// Status
				if( $order_sync == 'yes' ) {
					$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_order_status_display', '<span class="order-status tips wcicon-status-default wcicon-status-' . sanitize_title( $the_order->get_status() ) . ' text_tip" data-tip="' . wc_get_order_status_name( $the_order->get_status() ) . '"></span>', $the_order );
				} else {
					$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_order_status_display', '<span class="order-status tips wcicon-status-default wcicon-status-' . sanitize_title( $order->commission_status ) . ' text_tip" data-tip="' . $WCFMmp->wcfmmp_vendor->wcfmmp_vendor_order_status_name( $order->commission_status ) . '"></span>', $the_order );
				}
				
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
					$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_order_label_display', '<a href="' . get_wcfm_view_order_url($the_order->get_id(), $the_order) . '" class="wcfm_order_title">#' . esc_attr( $the_order->get_order_number() ) . '</a>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username, $the_order->get_id(), $order->product_id, $order, $username );
				else
					$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_order_label_display', '<span class="wcfm_order_title">#' . esc_attr( $the_order->get_order_number() ) . '</span> ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username, $the_order->get_id(), $order->product_id, $order, $username );
				
				// Purchased
				$order_item_details = '<div class="order_items" cellspacing="0">';
				$gross_sales = 0;
				$item_qty = 1;
				try {
					$line_item = new WC_Order_Item_Product( $order->item_id );
					if( $WCFMmp->wcfmmp_vendor->is_vendor_deduct_discount( $order->vendor_id, $order->order_id ) ) {
						$gross_sales += (float) sanitize_text_field( $order->item_total );
					} else {
						$gross_sales += (float) sanitize_text_field( $order->item_sub_total );
					}
					if( $this->is_vendor_get_tax ) {
						$gross_sales += (float) $order->tax;
					}
					if( $this->is_vendor_get_shipping ) {
						$gross_sales += (float) apply_filters( 'wcfmmmp_gross_sales_shipping_cost', $order->shipping, $order->vendor_id );
						if( $this->is_vendor_get_tax ) {
							$gross_sales += (float) $order->shipping_tax_amount;
						}
					}
					$item_qty = $order->quantity; //$line_item->get_quantity();
					$order_item_details .= '<div class=""><span class="qty">' . $order->quantity . 'x</span><span class="name">' . $line_item->get_name();
					if ( ! empty( $line_item->get_variation_id() ) ) {
						//$item_meta      = new WC_Order_Item_Meta( $line_item, $line_item->get_product() );
						//$item_meta_html = $item_meta->display( true, true );
						//$order_item_details .= '<span class="img_tip" data-tip="' . $item_meta_html . '"></span>';
					}
					$order_item_details .= '</span></div>';
				} catch (Exception $e) {
					wcfm_log( "order List Error ::" . $order->order_id . " => " . $e->getMessage() );
					unset( $wcfm_orders_json_arr[$index] );
					continue;
				}
				$order_item_details .= '</div>';
				
				$wcfm_orders_json_arr[$index][] = '<a href="#" class="show_order_items">' . sprintf( _n( '%d item', '%d items', $item_qty, 'wc-frontend-manager' ), $item_qty ) . '</a>' . $order_item_details;
				
				// Quantity
				$wcfm_orders_json_arr[$index][] =  $item_qty;
				
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
					if ( $the_order->needs_shipping_address() && $the_order->get_formatted_shipping_address() ) {
						$shipping_address = wp_kses( $the_order->get_formatted_shipping_address(), array( 'br' => array() ) );
					}
				}
				$wcfm_orders_json_arr[$index][] = "<div style='text-align:left;float:left'>" . $shipping_address . "</div>";
				
				// Gross Sales
				$gross_sales_amt = $gross_sales;
				if( $order->is_partially_refunded ) {
					$refunded_gross_sales = $gross_sales - (float) $order->refunded_amount;
					$gross_sales = '<del>' . wc_price( $gross_sales, array( 'currency' => $order_currency ) ) . '</del>';
					$gross_sales .=  "<br/>" . wc_price( $refunded_gross_sales, array( 'currency' => $order_currency ) );
				} elseif( $order->is_refunded ) {
					$gross_sales = '<del>' . wc_price( $gross_sales, array( 'currency' => $order_currency ) ) . '</del>';
					$gross_sales .=  "<br/>" . wc_price( 0, array( 'currency' => $order_currency ) );
				} else {
					$gross_sales = wc_price( $gross_sales, array( 'currency' => $order_currency ) );
				}
				if ( $the_order->get_payment_method_title() ) {
					$gross_sales .= '<br /><small class="meta">' . __( 'Via', 'wc-frontend-manager' ) . ' ' . esc_html( $the_order->get_payment_method_title() ) . '</small>';
				}
				$wcfm_orders_json_arr[$index][] =  $gross_sales;
				
				// Gross Sales Amount
				$wcfm_orders_json_arr[$index][] =  $gross_sales_amt;
				
				// Commision && Commission Amount
				$status = __( 'N/A', 'wc-frontend-manager' );
				$total  = 0;
				if ( 'pending' === $order->withdraw_status ) {
					$status = '<span class="wcpv-unpaid-status">' . esc_html__( 'UNPAID', 'wc-frontend-manager' ) . '</span>';
				}

				if ( 'completed' === $order->withdraw_status ) {
					$status = '<span class="wcpv-paid-status">' . esc_html__( 'PAID', 'wc-frontend-manager' ) . '</span>';
				}
				
				if ( 'requested' === $order->withdraw_status ) {
					$status = '<span class="wcpv-pending-status">' . esc_html__( 'REQUESTED', 'wc-frontend-manager' ) . '</span>';
				}

				if ( 'cancelled' === $order->withdraw_status ) {
					$status = '<span class="wcpv-void-status">' . esc_html__( 'CANCELLED', 'wc-frontend-manager' ) . '</span>';
				}
				
				if( $order->is_refunded ) {
					$wcfm_orders_json_arr[$index][] = '&ndash;';
					$wcfm_orders_json_arr[$index][] = '';
				} else {
					$total = (float) $order->total_commission;
					if( $order->is_partially_refunded ) {
						$gross_sales_amt = $gross_sales_amt - (float) $order->refunded_amount;
					}
					if( $admin_fee_mode ) {
						$total = $gross_sales_amt - $total;
					}
					$wcfm_orders_json_arr[$index][] =  apply_filters( 'wcfm_vendor_order_total', wc_price( $total, array( 'currency' => $order_currency ) ) . '<br />' . $status, $order->order_id, $order->product_id, $gross_sales_amt, $total, $status, $order_currency );
					$wcfm_orders_json_arr[$index][] = $total;
				}
				
				// Additional Info
				$wcfm_orders_json_arr[$index][] = apply_filters( 'wcfm_orders_additonal_data', '&ndash;', $the_order->get_id() );
				
				// Custom Column Support Before
				$wcfm_orders_json_arr = apply_filters( 'wcfm_orders_custom_columns_data_before', $wcfm_orders_json_arr, $index, $order->ID, $order, $the_order );
				
				// Date
				$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $the_order->order_date : $the_order->get_date_created();
				$wcfm_orders_json_arr[$index][] = apply_filters( 'wcfm_order_date_display', date_i18n( wc_date_format(), strtotime( $order_date ) ), $order->order_id, $order );
				
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
						$actions .= '<a class="wcfm_wcvendors_order_mark_shipped_dummy wcfm-action-icon" href="#" data-orderid="' . $order->order_id . '"><span class="wcfmfa fa-truck text_tip" data-tip="' . esc_attr__( 'Mark Shipped', 'wc-frontend-manager' ) . '"></span></a>';
					}
				}
				  
				$actions = apply_filters ( 'wcfm_orders_module_actions', $actions, $order->order_id, $the_order, $this->vendor_id );
				
				$wcfm_orders_json_arr[$index][] =  apply_filters ( 'wcfmmarketplace_orders_actions', $actions, $user_id, $order, $the_order, $this->vendor_id );
				
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