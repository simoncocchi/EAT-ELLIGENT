<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Customer Details Orders Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/customers
 * @version   3.5.0
 */

class WCFM_Customers_Details_Orders_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		$customer_id = absint($_POST['customer_id']);
		
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '_customer_user',
							'meta_value'       => $customer_id,
							'post_type'        => 'shop_order',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => 'any',
							'suppress_filters' => 0 
						);
		
		$args = apply_filters( 'wcfm_customer_details_orders_args', $args );
		
		$wcfm_orders_array = get_posts( $args );
		
		// Get Product Count
		$order_count = 0;
		$filtered_order_count = 0;
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_all_orders_array = get_posts( $args );
		if(!empty($wcfm_orders_array)) {
			$order_count = count($wcfm_all_orders_array);
			$filtered_order_count = count($wcfm_all_orders_array);
		}
		
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
				
				if( wcfm_is_vendor() ) {
					$is_order_for_vendor = $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $wcfm_orders_single->ID );
					if( !$is_order_for_vendor ) continue;
				}
				
				$the_order = wc_get_order( $wcfm_orders_single->ID );
				if( !is_a( $the_order, 'WC_Order' ) ) continue;
				
				$order_currency = $the_order->get_currency();
				
				// Status
				$wcfm_orders_json_arr[$index][] =  '<span class="order-status tips wcicon-status-' . sanitize_title( $the_order->get_status() ) . ' text_tip" data-tip="' . wc_get_order_status_name( $the_order->get_status() ) . '"></span>';
				
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

				if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $wcfm_orders_single->order_id ) ) {
					$wcfm_orders_json_arr[$index][] =  '<a href="' . get_wcfm_view_order_url($wcfm_orders_single->ID, $the_order) . '" class="wcfm_dashboard_item_title">#' . esc_attr( $the_order->get_order_number() ) . '</a>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username;
				} else {
					$wcfm_orders_json_arr[$index][] =  '<span class="wcfm_dashboard_item_title">#' . esc_attr( $the_order->get_order_number() ) . '</span>' . ' ' . __( 'by', 'wc-frontend-manager' ) . ' ' . $username;
				}
				
				// Purchased
				$order_item_details = '<div class="order_items" cellspacing="0">';
				$items = $the_order->get_items( 'line_item' );
				$items = apply_filters( 'wcfm_valid_line_items', $items, $the_order->get_id() );
				$total_qty = 0;
				foreach ($items as $key => $item) {
					if( version_compare( WC_VERSION, '4.4', '<' ) ) {
						$product = $the_order->get_product_from_item( $item );
					} else {
						$product = $item->get_product();
					}
					$item_meta_html = strip_tags( wc_display_item_meta( $item, array(
																																					'before'    => "\n- ",
																																					'separator' => "\n- ",
																																					'after'     => "",
																																					'echo'      => false,
																																					'autop'     => false,
																																				) ) );
				
					$total_qty += $item->get_quantity();
					$order_item_details .= '<div class=""><span class="qty">' . $item->get_quantity() . 'x</span><span class="name">' . $item->get_name();
					if ( ! empty( $item_meta_html ) ) $order_item_details .= '<span class="img_tip" data-tip="' . $item_meta_html . '"></span>';
					$order_item_details .= '</td></div>';
				}
				$order_item_details .= '</div>';
				$wcfm_orders_json_arr[$index][] =  '<a href="#" class="show_order_items">' . apply_filters( 'woocommerce_admin_order_item_count', sprintf( _n( '%d item', '%d items', $total_qty, 'wc-frontend-manager' ), $total_qty ), $the_order ) . '</a>' . $order_item_details;
				
				// Gross Sales
				if( wcfm_is_vendor() ) {
					$gross_sales = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( apply_filters( 'wcfm_current_vendor_id', get_current_user_id() ), '', '', $the_order->get_id() );
					$total = '<span class="order_total">' . wc_price( $gross_sales ) . '</span>';
				} else {
					$total = '<span class="order_total">' . $the_order->get_formatted_order_total() . '</span>';
				}

				if ( $the_order->get_payment_method_title() ) {
					$total .= '<br /><small class="meta">' . __( 'Via', 'wc-frontend-manager' ) . ' ' . esc_html( $the_order->get_payment_method_title() ) . '</small>';
				}
				$wcfm_orders_json_arr[$index][] =  $total;
				
				// Date
				$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $the_order->order_date : $the_order->get_date_created();
				$wcfm_orders_json_arr[$index][] = date_i18n( wc_date_format(), strtotime( $order_date ) );
				
				// Action
				$actions = '';
				if( $wcfm_is_allow_order_status_update = apply_filters( 'wcfm_is_allow_order_status_update', true ) ) {
					$order_status = sanitize_title( $the_order->get_status() );
					if( !in_array( $order_status, array( 'failed', 'cancelled', 'refunded', 'completed' ) ) ) $actions = '<a class="wcfm_order_mark_complete wcfm-action-icon" href="#" data-orderid="' . $wcfm_orders_single->ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark as Complete', 'wc-frontend-manager' ) . '"></span></a>';
				}
  	
				if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $wcfm_orders_single->order_id ) ) {
					$actions .= '<a class="wcfm-action-icon" href="' . get_wcfm_view_order_url($wcfm_orders_single->ID, $the_order) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View Details', 'wc-frontend-manager' ) . '"></span></a>';
				}
				
				if( WCFM_Dependencies::wcfmu_plugin_active_check() && WCFM_Dependencies::wcfm_wc_pdf_invoices_packing_slips_plugin_active_check() ) {
					if( apply_filters( 'wcfm_is_allow_pdf_invoice', true ) && apply_filters( 'wcfm_is_allow_view_commission', true ) ) {
						$actions .= '<a class="wcfm_pdf_invoice wcfm-action-icon" href="#" data-orderid="' . $wcfm_orders_single->ID . '"><span class="wcfmfa fa-file-invoice text_tip" data-tip="' . esc_attr__( 'PDF Invoice', 'wc-frontend-manager' ) . '"></span></a>';
					}
					if( apply_filters( 'wcfm_is_allow_pdf_packing_slip', true ) ) {
						$actions .= '<a class="wcfm_pdf_packing_slip wcfm-action-icon" href="#" data-orderid="' . $wcfm_orders_single->ID . '"><span class="wcfmfa fa-file-powerpoint text_tip" data-tip="' . esc_attr__( 'PDF Packing Slip', 'wc-frontend-manager' ) . '"></span></a>';
					}
				} else {
					if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
						$actions .= '<a class="wcfm_pdf_invoice_dummy wcfm-action-icon" href="#" data-orderid="' . $wcfm_orders_single->ID . '"><span class="wcfmfa fa-file-invoice text_tip" data-tip="' . esc_attr__( 'PDF Invoice', 'wc-frontend-manager' ) . '"></span></a>';
					}
				}
				
				$wcfm_orders_json_arr[$index][] =  $actions; //apply_filters ( 'wcfm_orders_actions', $actions, $wcfm_orders_single, $the_order );
				
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


/**
 * WCFM plugin controllers
 *
 * Plugin Customer Details Bookings Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/customers
 * @version   3.5.0
 */
class WCFM_Customers_Details_Bookings_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wc_get_booking_status_name = array( 'paid' => __('Paid & Confirmed', 'wc-frontend-manager' ), 'pending-confirmation' => __('Pending Confirmation', 'wc-frontend-manager' ), 'unpaid' => __('Un-paid', 'wc-frontend-manager' ), 'cancelled' => __('Cancelled', 'wc-frontend-manager' ), 'complete' => __('Complete', 'wc-frontend-manager' ), 'confirmed' => __('Confirmed', 'wc-frontend-manager' ) );
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		$customer_id = absint($_POST['customer_id']);
		
		$include_bookings = apply_filters( 'wcfm_wcb_include_bookings', '' );
		
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'bookingby'        => 'date',
							'booking'          => 'DESC',
							'include'          => $include_bookings,
							'exclude'          => '',
							'meta_key'         => '_booking_customer_id',
							'meta_value'       => $customer_id,
							'post_type'        => 'wc_booking',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array( 'complete', 'paid', 'confirmed', 'pending-confirmation', 'cancelled', 'unpaid' ),
							//'suppress_filters' => 0 
						);
		
		$args = apply_filters( 'wcfm_bookings_args', $args );
		
		$wcfm_bookings_array = get_posts( $args );
		
		// Get Product Count
		$booking_count = 0;
		$filtered_booking_count = 0;
		$wcfm_bookings_count = wp_count_posts('wc_booking');
		$booking_count = count($wcfm_bookings_array);
		// Get Filtered Post Count
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_filterd_bookings_array = get_posts( $args );
		$filtered_booking_count = count($wcfm_filterd_bookings_array);
		
		
		// Generate Products JSON
		$wcfm_bookings_json = '';
		$wcfm_bookings_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $booking_count . ',
															"recordsFiltered": ' . $filtered_booking_count . ',
															"data": ';
		if(!empty($wcfm_bookings_array)) {
			$index = 0;
			$wcfm_bookings_json_arr = array();
			foreach($wcfm_bookings_array as $wcfm_bookings_single) {
				$the_booking = new WC_Booking( $wcfm_bookings_single->ID );
				$product_id  = $the_booking->get_product_id( 'edit' );
				$product     = $the_booking->get_product( $product_id );
				$the_order   = $the_booking->get_order();
				
				if ( $the_booking->has_status( array( 'was-in-cart', 'in-cart' ) ) ) continue;
				
				// Status
				$wcfm_bookings_json_arr[$index][] =  '<span class="booking-status tips wcicon-status-' . sanitize_title( $the_booking->get_status( ) ) . ' text_tip" data-tip="' . $wc_get_booking_status_name[$the_booking->get_status()] . '"></span>';
				
				// Booking
				$booking_label =  '<a href="' . get_wcfm_view_booking_url($wcfm_bookings_single->ID, $the_booking) . '" class="wcfm_booking_title">' . __( '#', 'wc-frontend-manager' ) . $wcfm_bookings_single->ID . '</a>';
				
				$customer = $the_booking->get_customer();
				if ( ! isset( $customer->user_id ) || 0 == $customer->user_id ) {
					$booking_label .= ' by ';
					if( $customer->name ) {
						$guest_name = $customer->name;
						$guest_name = apply_filters( 'wcfm_booking_by_user', $guest_name, $wcfm_bookings_single->ID, $the_order->get_order_number() ); 
						if( apply_filters( 'wcfm_allow_view_customer_email', true ) && $customer->email ) {
							$booking_label .= '<a href="mailto:' .  $customer->email . '">' . $guest_name . '</a>';
						} else {
							$booking_label .= $guest_name;
						}
					} else {
						$booking_label .= sprintf( _x( 'Guest (%s)', 'Guest string with name from booking order in brackets', 'wc-frontend-manager' ), '&ndash;' );
					}
				} elseif ( $customer ) {
					if( $the_order ) {
						$guest_name = apply_filters( 'wcfm_booking_by_user', $customer->name, $wcfm_bookings_single->ID, $the_order->get_order_number() );
						$booking_label .= ' by ';
						if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
							$booking_label .= '<a href="mailto:' .  $customer->email . '">' . $guest_name . '</a>';
						} else {
							$booking_label .= $guest_name;
						}
					} else {
						$guest_name = $customer->name;
						$booking_label .= ' by ';
						if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
							$booking_label .= '<a href="mailto:' .  $customer->email . '">' . $guest_name . '</a>';
						} else {
							$booking_label .= $guest_name;
						}
					}
				}
				$wcfm_bookings_json_arr[$index][] = $booking_label;
				
				// Product
				//$resource = $the_booking->get_resource();

				if ( $product ) {
					$product_post = get_post($product->get_ID());
					$wcfm_bookings_json_arr[$index][] = $product_post->post_title;
					//if ( $resource ) {
						//$wcfm_bookings_json_arr[$index][] = $resource->post_title;
					//}
				} else {
					$wcfm_bookings_json_arr[$index][] = '&ndash;';
				}
				
				// #of Persons
				/*$persons = get_post_meta( $wcfm_bookings_single->ID, '_booking_persons', true );
				$total_persons = 0;
				if ( ! empty( $persons ) && is_array( $persons ) ) {
					foreach ( $persons as $person_count ) {
						$total_persons = $total_persons + $person_count;
					}
				}

				$wcfm_bookings_json_arr[$index][] =  esc_html( $total_persons );*/
				
				// Order
				if ( $the_order ) {
					if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $the_order->get_order_number() ) ) {
						$wcfm_bookings_json_arr[$index][] = '<span class="booking-orderno"><a href="' . get_wcfm_view_order_url( $the_order->get_order_number(), $the_order ) . '">#' . $the_order->get_order_number() . '</a></span><br />' . esc_html( wc_get_order_status_name( $the_order->get_status() ) );
					} else {
						$wcfm_bookings_json_arr[$index][] = '<span class="booking-orderno">#' . $the_order->get_order_number() . '</span><br /> ' . esc_html( wc_get_order_status_name( $the_order->get_status() ) );
					}
				} else {
					$wcfm_bookings_json_arr[$index][] = '&ndash;';
				}
				
				// Start Date
				$wcfm_bookings_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), $the_booking->get_start( 'edit' ) );
				
				// End Date
				$wcfm_bookings_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), $the_booking->get_end( 'edit' ) );
				
				// Action
				$actions = '';
				if ( current_user_can( 'manage_bookings_settings' ) || current_user_can( 'manage_bookings' ) ) {
					if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					 if ( in_array( $the_booking->get_status(), array( 'pending-confirmation' ) ) ) $actions = '<a class="wcfm_booking_mark_confirm wcfm-action-icon" href="#" data-bookingid="' . $wcfm_bookings_single->ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark as Confirmed', 'wc-frontend-manager' ) . '"></span></a>';
					}
				}
				$actions .= apply_filters ( 'wcfm_bookings_actions', '<a class="wcfm-action-icon" href="' . get_wcfm_view_booking_url( $wcfm_bookings_single->ID, $the_booking ) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View Details', 'wc-frontend-manager' ) . '"></span></a>', $wcfm_bookings_single, $the_booking );
				$wcfm_bookings_json_arr[$index][] = $actions;  
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_bookings_json_arr) ) $wcfm_bookings_json .= json_encode($wcfm_bookings_json_arr);
		else $wcfm_bookings_json .= '[]';
		$wcfm_bookings_json .= '
													}';
													
		echo $wcfm_bookings_json;
	}
}


/**
 * WCFM plugin controllers
 *
 * Plugin Customer Details Appointments Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/customers
 * @version   3.5.0
 */
class WCFM_Customers_Details_Appointments_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMu;
		
		$wc_get_appointment_status_name = array( 'paid' => __('Paid', 'wc-frontend-manager' ), 'pending-confirmation' => __('Pending Confirmation', 'wc-frontend-manager' ), 'unpaid' => __('Un-paid', 'wc-frontend-manager' ), 'cancelled' => __('Cancelled', 'wc-frontend-manager' ), 'complete' => __('Complete', 'wc-frontend-manager' ), 'confirmed' => __('Confirmed', 'wc-frontend-manager' ) );
		
		if ( class_exists( 'WC_Deposits' ) ) {
			$wc_get_appointment_status_name['wc-partial-payment'] = __( 'Partial Paid', 'wc-frontend-manager' );
		}
			
			
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		$customer_id = absint($_POST['customer_id']);
		
		$include_appointments = apply_filters( 'wcfm_wca_include_appointments', '' );
		
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => $include_appointments,
							'exclude'          => '',
							'meta_key'         => '_appointment_customer_id',
							'meta_value'       => $customer_id,
							'post_type'        => 'wc_appointment',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array_keys( $wc_get_appointment_status_name ),
							//'suppress_filters' => 0 
						);
		
		$args = apply_filters( 'wcfm_appointments_args', $args );
		$wcfm_appointments_array = get_posts( $args );
		
		// Get Product Count
		$appointment_count = 0;
		$filtered_appointment_count = 0;
		$wcfm_appointments_count = wp_count_posts('wc_appointment');
		$appointment_count = count($wcfm_appointments_array);
		// Get Filtered Post Count
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_filterd_appointments_array = get_posts( $args );
		$filtered_appointment_count = count($wcfm_filterd_appointments_array);
		
		
		// Generate Products JSON
		$wcfm_appointments_json = '';
		$wcfm_appointments_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $appointment_count . ',
															"recordsFiltered": ' . $filtered_appointment_count . ',
															"data": ';
		if(!empty($wcfm_appointments_array)) {
			$index = 0;
			$wcfm_appointments_json_arr = array();
			foreach($wcfm_appointments_array as $wcfm_appointments_single) {
				$the_appointment = new WC_Appointment( $wcfm_appointments_single->ID );
				$product_id  = $the_appointment->get_product_id( 'edit' );
				$product     = $the_appointment->get_product( $product_id );
				$the_order   = $the_appointment->get_order();
				if ( $the_appointment->has_status( array( 'was-in-cart', 'in-cart' ) ) ) continue;
				
				// Status
				$wcfm_appointments_json_arr[$index][] =  '<span class="appointment-status tips wcicon-status-' . sanitize_title( $the_appointment->get_status( ) ) . ' text_tip" data-tip="' . $wc_get_appointment_status_name[$the_appointment->get_status()] . '"></span>';
				
				// Appointment
				$appointment_label =  '<a href="' . get_wcfm_view_appointment_url($wcfm_appointments_single->ID, $the_appointment) . '" class="wcfm_appointment_title">#' . $wcfm_appointments_single->ID . '</a>';
				
				$customer = $the_appointment->get_customer();
				if ( ! isset( $customer->user_id ) || 0 == $customer->user_id ) {
					$appointment_label .= ' by ';
					if( $customer->full_name ) {
						$guest_name = $customer->full_name;
					} else {
						$guest_name = ' - ';
					}
					$appointment_label .= sprintf( _x( 'Guest (%s)', 'Guest string with name from appointment order in brackets', 'wc-frontend-manager' ), $guest_name );
				} elseif ( $customer ) {
					$appointment_label .= ' by ';
					if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
						$appointment_label .= '<a href="mailto:' .  $customer->email . '">' . $customer->full_name . '</a>';
					} else {
						$appointment_label .= $customer->full_name;
					}
				}
				$wcfm_appointments_json_arr[$index][] = $appointment_label;
				
				// Product
				//$resource = $the_appointment->get_resource();

				if ( $product ) {
					$product_post = get_post($product->get_ID());
					$wcfm_appointments_json_arr[$index][] = $product_post->post_title;
					//if ( $resource ) {
						//$wcfm_appointments_json_arr[$index][] = $resource->post_title;
					//}
				} else {
					$wcfm_appointments_json_arr[$index][] = '-';
				}
				
				// #of Persons
				/*$persons = get_post_meta( $wcfm_appointments_single->ID, '_appointment_persons', true );
				$total_persons = 0;
				if ( ! empty( $persons ) && is_array( $persons ) ) {
					foreach ( $persons as $person_count ) {
						$total_persons = $total_persons + $person_count;
					}
				}

				$wcfm_appointments_json_arr[$index][] =  esc_html( $total_persons );*/
				
				// Order
				if ( $the_order ) {
					if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $the_order->get_order_number() ) ) {
						$wcfm_appointments_json_arr[$index][] = '<span class="appointment-orderno"><a href="' . get_wcfm_view_order_url( $the_order->get_order_number(), $the_order ) . '">#' . $the_order->get_order_number() . '</a></span><br />' . esc_html( wc_get_order_status_name( $the_order->get_status() ) );
					} else  {
						$wcfm_appointments_json_arr[$index][] = '<span class="appointment-orderno">#' . $the_order->get_order_number() . '</span><br /> ' . esc_html( wc_get_order_status_name( $the_order->get_status() ) );
					}
				} else {
					$wcfm_appointments_json_arr[$index][] = '&ndash;';
				}
				
				// Start Date
				$wcfm_appointments_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), $the_appointment->get_start( 'edit' ) );
				
				// End Date
				$wcfm_appointments_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), $the_appointment->get_end( 'edit' ) );
				
				// Action
				$actions = '';
				if ( current_user_can( 'manage_appointments' ) ) {
					if ( in_array( $the_appointment->get_status(), array( 'pending-confirmation' ) ) ) $actions = '<a class="wcfm_appointment_mark_confirm wcfm-action-icon" href="#" data-appointmentid="' . $wcfm_appointments_single->ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark as Confirmed', 'wc-frontend-manager' ) . '"></span></a>';
				}
				$actions .= apply_filters ( 'wcfm_appointments_actions', '<a class="wcfm-action-icon" href="' . get_wcfm_view_appointment_url( $wcfm_appointments_single->ID, $the_appointment ) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View Details', 'wc-frontend-manager' ) . '"></span></a>', $wcfm_appointments_single, $the_appointment );
				$wcfm_appointments_json_arr[$index][] = $actions;  
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_appointments_json_arr) ) $wcfm_appointments_json .= json_encode($wcfm_appointments_json_arr);
		else $wcfm_appointments_json .= '[]';
		$wcfm_appointments_json .= '
													}';
													
		echo $wcfm_appointments_json;
	}
}