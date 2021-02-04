<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Refund Form Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/controllers/refund
 * @version   1.0.0
 */

class WCFMmp_Refund_Requests_Form_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wcfm_refund_tab_form_data = array();
	  parse_str($_POST['wcfm_refund_requests_form'], $wcfm_refund_tab_form_data);
	  
	  $wcfm_refund_messages = get_wcfm_refund_requests_messages();
	  $has_error = false;
	  
	  // Google reCaptcha support
	  if ( function_exists( 'gglcptch_init' ) ) {
			if(isset($wcfm_refund_tab_form_data['g-recaptcha-response']) && !empty($wcfm_refund_tab_form_data['g-recaptcha-response'])) {
				$_POST['g-recaptcha-response'] = $wcfm_refund_tab_form_data['g-recaptcha-response'];
			}
			$check_result = apply_filters( 'gglcptch_verify_recaptcha', true, 'string', 'wcfm_refund_request_form' );
			if ( true === $check_result ) {
					/* do necessary action */
			} else { 
				echo '{"status": false, "message": "' . $check_result . '"}';
				die;
			}
		} elseif ( class_exists( 'anr_captcha_class' ) && function_exists( 'anr_captcha_form_field' ) ) {
			$check_result = anr_verify_captcha( $wcfm_refund_tab_form_data['g-recaptcha-response'] );
			if ( true === $check_result ) {
					/* do necessary action */
			} else { 
				echo '{"status": false, "message": "' . __( 'Captcha failed, please try again.', 'wc-frontend-manager' ) . '"}';
				die;
			}
		}
	  
	  if(isset($wcfm_refund_tab_form_data['wcfm_refund_reason']) && !empty($wcfm_refund_tab_form_data['wcfm_refund_reason'])) {
	  	
	  	$refund_reason          = strip_tags( wcfm_stripe_newline( $wcfm_refund_tab_form_data['wcfm_refund_reason'] ) );
	  	$refund_reason          = esc_sql( wp_unslash( $refund_reason ) );
	  	$order_id               = absint( $wcfm_refund_tab_form_data['wcfm_refund_order_id'] );
	  	$refund_request         = wc_clean( $wcfm_refund_tab_form_data['wcfm_refund_request'] );
	  	$wcfm_refund_inputs     = wc_clean( $wcfm_refund_tab_form_data['wcfm_refund_input'] );
	  	$wcfm_refund_tax_inputs = isset( $wcfm_refund_tab_form_data['wcfm_refund_tax_input'] ) ? wc_clean( $wcfm_refund_tab_form_data['wcfm_refund_tax_input'] ) : array();
	  	$refund_status          = 'pending';
	  	
	  	//if( ( $refund_request == 'partial' ) && !$refunded_amount ) {
	  		//echo '{"status": false, "message": "' . __( 'Refund should be a positive integer.', 'wc-multivendor-marketplace' ) . '"}';
	  		//die;
	  	//}
	  	
	  	$refund_request_processed = false;
	  	
	  	$order = wc_get_order( $order_id );
	  	
	  	foreach( $wcfm_refund_inputs as $wcfm_refund_input_id => $wcfm_refund_input ) {
	  		
	  		$refund_item_id = absint( $wcfm_refund_input['item'] );
	  		
	  		if( !$refund_item_id ) continue;
	  		
	  		$line_item           = new WC_Order_Item_Product( $refund_item_id );
	  		
	  		$product_id          = $line_item->get_product_id();
				$vendor_id           = wcfm_get_vendor_id_by_post( $product_id );
	  		$item_total          = $line_item->get_total();
	  		
	  		$old_refunded_amount = $order->get_total_refunded_for_item( $refund_item_id );
	  		$old_refunded_qty    = $order->get_qty_refunded_for_item( $refund_item_id );
	  		if( $old_refunded_qty ) $old_refunded_qty = ( $old_refunded_qty * -1 );
	  		
	  		$refunded_tax = array();
	  		
	  		if( $refund_request == 'full' ) {
	  		  $refunded_qty = ( $line_item->get_quantity() - $old_refunded_qty );
	  		  $refunded_amount = $item_total - (float)$old_refunded_amount;
	  		  
	  		  // Adding Item Tax to Refund Amount
	  		  if ( wc_tax_enabled() ) {
						$refunded_tax      = $line_item->get_taxes();
						if( !empty( $refunded_tax ) && is_array( $refunded_tax ) ) {
							if( isset( $refunded_tax['total'] ) ) {
								$refunded_tax = $refunded_tax['total'];
							}
							if( !empty( $refunded_tax ) && is_array( $refunded_tax ) ) {
								foreach( $refunded_tax as $refund_tax_id => $refund_tax_price ) {
									$old_refunded_tax   = $order->get_tax_refunded_for_item( $refund_item_id, $refund_tax_id );
									$refunded_tax[$refund_tax_id] = (float) $refund_tax_price - (float) $old_refunded_tax;
									//$refunded_amount += (float) $refund_tax_price;
								}
							}
						}
					}
	  		} else {
	  			$refunded_qty = absint( $wcfm_refund_input['qty'] );
	  			$refunded_amount = (float) $wcfm_refund_input['total'];
	  			
	  			if( (float)$refunded_amount > ((float)$item_total - (float)$old_refunded_amount) ) {
						echo '{"status": false, "message": "' . __('Refund request amount more than item value.', 'wc-multivendor-marketplace') . '"}';
						die;
					}
					
					// Adding Item Tax to Refund Amount
	  		  if ( wc_tax_enabled() ) {
	  		  	$refunded_tax     = isset( $wcfm_refund_tax_inputs[$refund_item_id] ) ? $wcfm_refund_tax_inputs[$refund_item_id] : array();
	  		  	$refunded_tax_amt = 0; 
	  		  	if( $refunded_tax && is_array( $refunded_tax ) && !empty( $refunded_tax ) ) {
							foreach( $refunded_tax as $tax_item_id => $tax_item_cost ) {
								$refunded_tax_amt += (float)$tax_item_cost;
							}
						}
	  		  	
						$actual_tax         = $line_item->get_taxes();
						$actual_tax_amount  = 0; 
						if( !empty( $actual_tax ) && is_array( $actual_tax ) ) {
							if( isset( $actual_tax['total'] ) ) {
								$actual_tax = $actual_tax['total'];
							}
							if( !empty( $actual_tax ) && is_array( $actual_tax ) ) {
								foreach( $actual_tax as $actual_tax_id => $actual_tax_price ) {
									$actual_tax_amount += (float) $actual_tax_price;
									$old_refunded_tax   = $order->get_tax_refunded_for_item( $refund_item_id, $actual_tax_id );
									$actual_tax_amount -= (float) $old_refunded_tax;
								}
							}
						}
						
						if( (float)$refunded_tax_amt > (float)$actual_tax_amount ) {
							echo '{"status": false, "message": "' . __('Refund request tax amount more than item actual tax value.', 'wc-multivendor-marketplace') . '"}';
							die;
						}
						
						//$refunded_amount += (float)$refunded_tax_amt;
					}
	  		}
	  		
	  		if( !$refunded_qty && !$refunded_amount ) continue;
	  		
				$sql = 'SELECT ID FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
				$sql .= ' WHERE 1=1';
				$sql .= " AND `order_id` = " . $order_id;
				$sql .= " AND `item_id`  = " . $refund_item_id;
				$commission_id = $wpdb->get_var( $sql );
				
				$refund_request_id = $WCFMmp->wcfmmp_refund->wcfmmp_refund_processed( $vendor_id, $order_id, $commission_id, $refund_item_id, $refund_reason, $refunded_amount, $refunded_qty, $refunded_tax, $refund_request );
				
				if( $refund_request_id && !is_wp_error( $refund_request_id ) ) {
					
					// Update Commissions Table Refund Status
					if( $commission_id ) {
						$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('refund_status' => 'requested'), array('ID' => $commission_id), array('%s'), array('%d'));
					}
					
					$refund_auto_approve = isset( $WCFMmp->wcfmmp_refund_options['refund_auto_approve'] ) ? $WCFMmp->wcfmmp_refund_options['refund_auto_approve'] : 'no';
					$wcfm_messages = '';
					if( ( $refund_auto_approve == 'yes' ) && $vendor_id && wcfm_is_vendor() ) {
						
						// Update refund status
						$refund_update_status = $WCFMmp->wcfmmp_refund->wcfmmp_refund_status_update_by_refund( $refund_request_id );
						
						if( $refund_update_status ) {
							// Admin Notification
							if( $refund_request == 'full' ) {
								if( !$refund_request_processed )
									$wcfm_messages = sprintf( __( 'Refund <b>%s</b> has been processed for Order <b>%s</b> by <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . add_query_arg( 'request_id', $refund_request_id, wcfm_refund_requests_url() ) . '">#' . $refund_request_id . '</a>', '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">#' . $order->get_order_number() . '</a>', wcfm_get_vendor_store( $vendor_id ) );
							} else {
								$wcfm_messages = sprintf( __( 'Refund <b>%s</b> has been processed for Order <b>%s</b> item <b>%s</b> by <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . add_query_arg( 'request_id', $refund_request_id, wcfm_refund_requests_url() ) . '">#' . $refund_request_id . '</a>', '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">#' . $order->get_order_number() . '</a>', get_the_title( $product_id ), wcfm_get_vendor_store( $vendor_id ) );
							}
							
							if( $wcfm_messages ) {
								$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'refund-request' );
								
								// Order Note
								$is_customer_note = apply_filters( 'wcfm_is_allow_refund_update_note_for_customer', '1' );
								add_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
								$comment_id = $order->add_order_note( strip_tags($wcfm_messages), $is_customer_note );
								add_comment_meta( $comment_id, '_vendor_id', $vendor_id );
								remove_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
							}
							
							do_action( 'wcfmmp_refund_request_approved', $refund_request_id );
							
							//echo '{"status": true, "message": "' . __('Refund requests successfully processed.', 'wc-multivendor-marketplace') . ' #' . $refund_request_id . '"}';
						} else {
							//echo '{"status": false, "message": "' . __('Refund processing failed, please contact site admin.', 'wc-multivendor-marketplace') . ' #' . $refund_request_id . '"}';
						}
					} else {
						// Admin Notification
						if( $refund_request == 'full' ) {
							if( !$refund_request_processed )
								$wcfm_messages = apply_filters( 'wcfmmp_refund_request_message',  sprintf( __( 'Refund Request <b>%s</b> received for Order <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . add_query_arg( 'request_id', $refund_request_id, wcfm_refund_requests_url() ) . '">#' . $refund_request_id . '</a>', '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">#' . $order->get_order_number() . '</a>' ), $refund_request_id, $order_id, $product_id );
						} else {
							$wcfm_messages = apply_filters( 'wcfmmp_refund_request_message',  sprintf( __( 'Refund Request <b>%s</b> received for Order <b>%s</b> item <b>%s</b>', 'wc-multivendor-marketplace' ), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . add_query_arg( 'request_id', $refund_request_id, wcfm_refund_requests_url() ) . '">#' . $refund_request_id . '</a>', '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url( $order_id ) . '">#' . $order->get_order_number() . '</a>', get_the_title( $product_id ) ), $refund_request_id, $order_id, $product_id );
						}
						
						if( $wcfm_messages ) {
							$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'refund-request' );
							
							// Send Vendor Notification
							if( $vendor_id && !wcfm_is_vendor() ) {
								$is_allow_refund = wcfm_vendor_has_capability( $vendor_id, 'refund-request' );
								if( $is_allow_refund && apply_filters( 'wcfm_is_allow_refund_vendor_notification', true ) ) {
									$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'refund-request' );
								}
							}
							
							// Order Note
							$is_customer_note = apply_filters( 'wcfm_is_allow_refund_request_note_for_customer', '1' );
							$comment_id = $order->add_order_note( strip_tags($wcfm_messages), $is_customer_note );
						}
						
						//echo '{"status": true, "message": "' . $wcfm_refund_messages['refund_requests_saved'] . ' #' . $refund_request_id . '"}';
					}
					
					do_action( 'wcfm_after_refund_request',  $refund_request_id, $order_id, $commission_id, $refund_item_id, $vendor_id, $refund_reason );
					
				} else {
					//echo '{"status": false, "message": "' . $wcfm_refund_messages['refund_requests_failed'] . '"}';
				}
				
				$refund_request_processed = true;
			}
		} else {
			echo '{"status": false, "message": "' . $wcfm_refund_messages['no_refund_reason'] . '"}';
		}
		
		if( !$refund_request_processed ) {
			echo '{"status": false, "message": "' . __( 'No item selected for refund request.', 'wc-multivendor-marketplace' ) . '"}';
		} else {
			echo '{"status": true, "message": "' . __('Refund requests successfully processed.', 'wc-multivendor-marketplace') . '"}';
		}
		
		die;
	}
}