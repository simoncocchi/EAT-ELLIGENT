<?php
/**
 * WCFM plugin view
 *
 * WCFM Order Details View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view
 * @version   1.0.0
 */

global $wp, $WCFM, $WCFMmp, $theorder, $wpdb;
 
$wcfm_is_allow_orders = apply_filters( 'wcfm_is_allow_orders', true );
if( !$wcfm_is_allow_orders ) {
	wcfm_restriction_message_show( "Orders" );
	return;
}


if( isset( $wp->query_vars['wcfm-orders-details'] ) && !empty( $wp->query_vars['wcfm-orders-details'] ) ) {
	$order_id = $wp->query_vars['wcfm-orders-details'];
}

if( !$order_id ) return;

if( wcfm_is_vendor() ) {
	$is_order_for_vendor = $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $order_id );
	if( !$is_order_for_vendor ) {
		if( apply_filters( 'wcfm_is_show_order_restrict_message', true, $order_id ) ) {
			wcfm_restriction_message_show( "Restricted Order" );
		} else {
			echo apply_filters( 'wcfm_show_custom_order_restrict_message', '', $order_id );
		}
		return;
	}
}

$theorder = wc_get_order( $order_id );

if( !is_a( $theorder, 'WC_Order' ) ) {
	wcfm_restriction_message_show( "Invalid Order" );
	return;
}

if( !$theorder ) return;

$post = get_post($order_id);
$order = $theorder;

$WCFM->library->init_address_fields();

if ( WC()->payment_gateways() ) {
	$payment_gateways = WC()->payment_gateways->payment_gateways();
} else {
	$payment_gateways = array();
}

if( !is_a( $order, 'WC_Order' ) ) $payment_method = '';
else $payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';

$order_type_object = get_post_type_object( $post->post_type );

// Get line items
$line_items          = $order->get_items( 'line_item' );
$line_items_fee      = $order->get_items( 'fee' );
$line_items_shipping = $order->get_items( 'shipping' );

$order_taxes = $classes_options = array();
if( $is_wcfm_order_details_tax_line_item = apply_filters( 'wcfm_order_details_tax_line_item', true ) ) {
	if ( wc_tax_enabled() ) {
		$order_taxes         = $order->get_taxes();
		$tax_classes         = WC_Tax::get_tax_classes();
		$classes_options[''] = __( 'Standard', 'wc-frontend-manager' );
	
		if ( ! empty( $tax_classes ) ) {
			foreach ( $tax_classes as $class ) {
				$classes_options[ sanitize_title( $class ) ] = $class;
			}
		}
	
		// Older orders won't have line taxes so we need to handle them differently :(
		$tax_data = '';
		if ( $line_items ) {
			$check_item = current( $line_items );
			$tax_data   = maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
		} elseif ( $line_items_shipping ) {
			$check_item = current( $line_items_shipping );
			$tax_data = maybe_unserialize( isset( $check_item['taxes'] ) ? $check_item['taxes'] : '' );
		} elseif ( $line_items_fee ) {
			$check_item = current( $line_items_fee );
			$tax_data   = maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
		}
		
		$legacy_order     = ! empty( $order_taxes ) && empty( $tax_data ) && ! is_array( $tax_data );
		$show_tax_columns = ! $legacy_order || sizeof( $order_taxes ) === 1;
	}
}

$wcfm_generate_csv_url = apply_filters( 'wcfm_generate_csv_url', '', $order_id );

$statuses = apply_filters( 'wcfm_allowed_order_status', wc_get_order_statuses(), $order_id );
$current_order_status = apply_filters( 'wcfm_current_order_status', $order->get_status(), $order->get_id() );

$status_update_block_statuses = apply_filters( 'wcfm_status_update_block_statuses', array( 'refunded', 'cancelled', 'failed' ), $order_id );

// Marketplace Filters
$line_items          = apply_filters( 'wcfm_valid_line_items', $line_items, $order->get_id() );
$line_items_shipping = apply_filters( 'wcfm_valid_shipping_items', $line_items_shipping, $order->get_id() );

do_action( 'before_wcfm_orders_details', $order_id );

?>

<div class="collapse wcfm-collapse" id="wcfm_order_details">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-cart-arrow-down"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Order Details', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Order #', 'wc-frontend-manager' ); echo $theorder->get_order_number(); ?></h2>
			<span class="order-status order-status-<?php echo sanitize_title( $current_order_status ); ?>"><?php _e( ucfirst(wc_get_order_status_name( $current_order_status )), 'wc-multivendor-marketplace' ); ?></span>
			
			<?php
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('post.php?post='.$order_id.'&action=edit'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
				<?php
			}
			?>
			
			<div id="order_quick_actions">
				<?php
				if( $wcfm_is_allow_export_csv = apply_filters( 'wcfm_is_allow_export_csv', true ) ) {
					if( $wcfm_generate_csv_url ) {
						echo '<a class="wcfm_csv_export order_quick_action add_new_wcfm_ele_dashboard" href="'.$wcfm_generate_csv_url.'" data-orderid="' . $order_id . '"><span class="wcfmfa fa-file-excel-o text_tip" data-tip="' . esc_attr__( 'CSV Export', 'wc-frontend-manager' ) . '"></span></a>';
					}
				}
				
				if( apply_filters( 'wcfm_is_allow_pdf_invoice', true ) || apply_filters( 'wcfm_is_allow_pdf_packing_slip', true ) ) {
					if( WCFM_Dependencies::wcfmu_plugin_active_check() && WCFM_Dependencies::wcfm_wc_pdf_invoices_packing_slips_plugin_active_check() ) {
						echo apply_filters ( 'wcfm_orders_module_actions', '', $order_id, $theorder );
					} else {
						if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
							if( wcfm_is_vendor() ) {
								echo '<a class="wcfm_pdf_invoice_vendor_dummy order_quick_action add_new_wcfm_ele_dashboard" href="#" data-orderid="' . $order_id . '"><span class="wcfmfa fa-file-invoice text_tip" data-tip="' . esc_attr__( 'PDF Invoice', 'wc-frontend-manager' ) . '"></span></a>';
							} else {
								echo '<a class="wcfm_pdf_invoice_dummy order_quick_action add_new_wcfm_ele_dashboard" href="#" data-orderid="' . $order_id . '"><span class="wcfmfa fa-file-invoice text_tip" data-tip="' . esc_attr__( 'PDF Invoice', 'wc-frontend-manager' ) . '"></span></a>';
							}
						}
					}
				}
				do_action( 'wcfm_after_order_quick_actions', $order_id );
				?>
			</div>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'begin_wcfm_orders_details', $order_id ); ?>
	  
		<div class="wcfm-container">
			<div id="orders_details_general_expander" class="wcfm-content">
	
				<p class="form-field form-field-wide"><label for="order_date"><?php _e( 'Order date:', 'wc-frontend-manager' ) ?></label>
					<?php echo date_i18n( wc_date_format() . ' @' . wc_time_format(), strtotime( $post->post_date ) ); ?>
				</p>
				
				<?php if( apply_filters( 'wcfm_is_allow_order_status_update', true ) ) { ?>
					<div id="wcfm_order_status_update_wrapper" class="wcfm_order_status_update_wrapper">
						<p class="form-field form-field-wide wc-order-status">
							<label for="order_status"><?php _e( 'Order status:', 'wc-frontend-manager' ) ?> 
								<?php
								if( $wcfm_is_allow_order_details = apply_filters( 'wcfm_allow_order_details', true ) ) {
									if ( $order->needs_payment() ) {
										printf( '<a target="_blank" href="%s">%s &rarr;</a>',
											esc_url( $order->get_checkout_payment_url() ),
											__( 'Customer payment page', 'wc-frontend-manager' )
										);
									}
								}
								?>
							</label>
							<?php
							  if( !in_array( $current_order_status, $status_update_block_statuses ) && apply_filters( 'wcfm_is_allow_order_status_change_active', true, $order_id, $order ) ) {
							  	$order_status = '<select id="wcfm_order_status" name="order_status">';
							  } else {
							  	$order_status = '<select id="wcfm_order_status" name="order_status" disabled>';
							  }
								foreach ( $statuses as $status => $status_name ) {
									$order_status .= '<option value="' . esc_attr( $status ) . '" ' . selected( $status, 'wc-' . $current_order_status, false ) . '>' . esc_html( $status_name ) . '</option>';
								}
								$order_status .= '</select>';
								if( !in_array( $current_order_status, $status_update_block_statuses ) && apply_filters( 'wcfm_is_allow_order_status_change_active', true, $order_id, $order ) ) {
									$order_status .= '<button class="wcfm_modify_order_status button" id="wcfm_modify_order_status" data-orderid="' . $order->get_id() . '">' .  __( 'Update', 'wc-frontend-manager' ) . '</button>';
								}
								echo $order_status;
							?>
						</p>
						
						<?php do_action( 'wcfm_after_order_status_edit_block', $order_id ); ?>
						
						<div class="wcfm-message" tabindex="-1"></div>
					</div>
			  <?php } ?>		
					
				<?php if ( apply_filters( 'wcfm_allow_order_customer_details', true ) ) { ?>
					<p class="form-field form-field-wide wc-customer-user">
						<label for="customer_user"><?php _e( 'Customer:', 'wc-frontend-manager' ) ?> <?php
							if ( $order->get_user_id() ) {
								$args = array( 'post_status' => 'all',
									'post_type'      => 'shop_order',
									'_customer_user' => absint( $order->get_user_id() )
								);
								printf( '<a target="_blank" href="%s">%s &rarr;</a>',
									esc_url( add_query_arg( $args, admin_url( 'edit.php' ) ) ),
									__( 'View other orders', 'wc-frontend-manager' )
								);
							}
						?></label>
						<?php
						$user_string = '';
						$user_id     = '';
						if ( $order->get_user_id() ) {
							$user_id     = absint( $order->get_user_id() );
							$user        = get_user_by( 'id', $user_id );
							if( $user ) {
								$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' - ' . esc_html( $user->user_email ) . ')';
							}
						}
						echo htmlspecialchars( $user_string );
						?>
					</p>
				<?php } ?>
				
				<?php 
				if( apply_filters( 'wcfm_is_allow_woocommerce_admin_order_data_after_order_details', true ) ) {
					do_action( 'woocommerce_admin_order_data_after_order_details', $order );
				}
				?>
		
				<p class="order_number">
					<?php
		
						if ( $payment_method ) {
							printf( __( 'Payment via %s', 'woocommerce' ), ( isset( $payment_gateways[ $payment_method ] ) ? esc_html( $payment_gateways[ $payment_method ]->get_title() ) : esc_html( $payment_method ) ) );
			
							if ( apply_filters( 'wcfm_allow_order_customer_details', true ) ) {
								if ( $transaction_id = $order->get_transaction_id() ) {
										if ( isset( $payment_gateways[ $payment_method ] ) && ( $url = $payment_gateways[ $payment_method ]->get_transaction_url( $order ) ) ) {
										echo ' (<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>)';
									} else {
										echo ' (' . esc_html( $transaction_id ) . ')';
									}
								}
							}
							echo '. ';
			
							if ( $order->get_date_paid() ) {
								/* translators: 1: date 2: time */
								printf( ' ' . __( 'Paid on %1$s @ %2$s', 'woocommerce' ), wc_format_datetime( $order->get_date_paid() ), wc_format_datetime( $order->get_date_paid(), get_option( 'time_format' ) ) );
							}
			
							echo '. ';
						}
			
						if ( apply_filters( 'wcfm_allow_order_customer_details', true ) ) {
							if ( $ip_address = $order->get_customer_ip_address() ) {
								echo __( 'Customer IP', 'wc-frontend-manager' ) . ': <span class="woocommerce-Order-customerIP">' . esc_html( $ip_address ) . '</span>';
							}
						}
					?>
				</p>
				
				<?php if( apply_filters( 'wcfm_allow_customer_billing_details', true ) || apply_filters( 'wcfm_allow_customer_shipping_details', true ) || apply_filters( 'wcfm_is_allow_view_customer', true ) ) { ?>
					<table>
						<thead>
							<tr>
							  <?php if( apply_filters( 'wcfm_allow_customer_billing_details', true ) || apply_filters( 'wcfm_is_allow_view_customer', true ) ) { ?>
									<th>
										<?php _e( 'Billing Details', 'wc-frontend-manager' ); ?>
									</th>
								<?php } ?>
								
								<?php if( apply_filters( 'wcfm_allow_customer_shipping_details', true ) || apply_filters( 'wcfm_is_allow_view_customer', true ) ) { ?>
									<?php if ( ( $order->needs_shipping_address() || $order->get_formatted_shipping_address() ) || ( ( !$order->needs_shipping_address() || !$order->get_formatted_shipping_address() ) && apply_filters( 'wcfm_is_allow_shipping_column_without_address', true ) ) ) { ?>
										<th>
											<?php _e( 'Shipping Details', 'wc-frontend-manager' ); ?>
										</th>
									<?php } ?>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr>
							  <?php if( apply_filters( 'wcfm_allow_customer_billing_details', true ) || apply_filters( 'wcfm_is_allow_view_customer', true ) ) { ?>
									<td>
										<?php
											// Display values
											echo '<div class="address">';
											
											if( apply_filters( 'wcfm_allow_customer_billing_details', true ) ) {
												if ( $order->get_formatted_billing_address() ) {
													echo '<p>' . wp_kses( $order->get_formatted_billing_address(), array( 'br' => array() ) ) . '</p>';
												} else {
													echo '<p class="none_set">' . __( 'No billing address set.', 'wc-frontend-manager' ) . '</p>';
												}
											}
				
											if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
												foreach ( $WCFM->library->billing_fields as $key => $field ) {
													if ( isset( $field['show'] ) && false === $field['show'] ) {
														continue;
													}
					
													$field_name = 'billing_' . $key;
													
													if( !apply_filters( 'wcfm_allow_view_customer_'.$field_name, true ) ) continue;
					
													if ( is_callable( array( $order, 'get_' . $field_name ) ) ) {
														$field_value = $order->{"get_$field_name"}( 'edit' );
													} else {
														$field_value = $order->get_meta( '_' . $field_name );
													}
				
													echo '<p><strong>' . esc_html( $field['label'] ) . ':</strong> ' . make_clickable( esc_html( $field_value ) ) . '</p>';
												}
											}
				
											echo '</div>';
				
											if( apply_filters( 'wcfm_is_allow_order_data_after_billing_address', false ) ) {
												do_action( 'woocommerce_admin_order_data_after_billing_address', $order );
											}
											?>
									</td>
								<?php } ?>
								
								<?php if( apply_filters( 'wcfm_allow_customer_shipping_details', true ) || apply_filters( 'wcfm_is_allow_view_customer', true ) ) { ?>
									<?php if ( ( $order->needs_shipping_address() || $order->get_formatted_shipping_address() ) || ( ( !$order->needs_shipping_address() || !$order->get_formatted_shipping_address() ) && apply_filters( 'wcfm_is_allow_shipping_column_without_address', true ) ) ) { ?>
										<td style="vertical-align:top;">
											<?php
												// Display values
												echo '<div class="address">';
												
													if( apply_filters( 'wcfm_allow_customer_shipping_details', true ) ) {
														if ( ( $order->needs_shipping_address() && $order->get_formatted_shipping_address() ) || apply_filters( 'wcfm_is_force_shipping_address', false ) ) {
															echo '<p>' . wp_kses( $order->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
														} else {
															echo '<p class="none_set">' . __( 'No shipping address set.', 'wc-frontend-manager' ) . '</p>';
														}
													}
					
													if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
														if ( ! empty( $WCFM->library->shipping_fields ) ) {
															foreach ( $WCFM->library->shipping_fields as $key => $field ) {
																if ( isset( $field['show'] ) && false === $field['show'] ) {
																	continue;
																}
						
																$field_name = 'shipping_' . $key;
																
																if( !apply_filters( 'wcfm_allow_view_customer_'.$field_name, true ) ) continue;
						
																if ( is_callable( array( $order, 'get_' . $field_name ) ) ) {
																	$field_value = $order->{"get_$field_name"}( 'edit' );
																} else {
																	$field_value = $order->get_meta( '_' . $field_name );
																}
						
																echo '<p><strong>' . esc_html( $field['label'] ) . ':</strong> ' . make_clickable( esc_html( $field_value ) ) . '</p>';
															}
														}
													}
					
													if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' == get_option( 'woocommerce_enable_order_comments', 'yes' ) ) && $post->post_excerpt ) {
														echo '<p><strong>' . __( 'Customer Provided Note', 'wc-frontend-manager' ) . ':</strong> ' . nl2br( esc_html( $post->post_excerpt ) ) . '</p>';
													}
													
												echo '</div>';
												
												if( apply_filters( 'wcfm_is_allow_order_data_after_shipping_address', false ) ) {
													do_action( 'woocommerce_admin_order_data_after_shipping_address', $order );
												}
												
												do_action( 'wcfm_order_details_after_shipping_address',  $order );
												?>
										</td>
									<?php } ?>
								<?php } ?>
							</tbody>
						</table>
					<?php } ?>
					
					<?php do_action( 'wcfm_order_details_after_address',  $order ); ?>
					
					<?php
					if( !wcfm_is_vendor() ) {
						if( !in_array( $current_order_status, apply_filters( 'wcfm_pdf_invoice_download_disable_order_status', array( 'failed', 'cancelled', 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) ) {
							$wcfm_store_invoices = get_post_meta( $order->get_id(), '_wcfm_store_invoices', true );
							if( $wcfm_store_invoices  && is_array( $wcfm_store_invoices ) && !empty( $wcfm_store_invoices ) ) {
								echo '<h2>' . __( 'Store Invoice(s)', 'wc-frontend-manager' ) . '</h2><div class="wcfm_clearfix"></div>';
								$upload_dir = wp_upload_dir();
								foreach( $wcfm_store_invoices as $vendor_id => $wcfm_store_invoice ) {
									if( file_exists( $wcfm_store_invoice ) ) {
										if (empty($upload_dir['error'])) {
											$upload_base = trailingslashit( $upload_dir['basedir'] );
											$upload_url = trailingslashit( $upload_dir['baseurl'] );
											$invoice_path = str_replace( $upload_base, $upload_url, $wcfm_store_invoice );
											
											$sold_by_text = __( 'Store', 'wc-frontend-manager-ultimate' );
											if( $WCFMmp ) {
												$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( absint($vendor_id) );
											}
											echo '<a id="wcfm-store-invoice-' . $vendor_id . '" target="_blank" class="add_new_wcfm_ele_dashboard text_tip" style="float:left!important;color:#ffffff!important;margin-right:10px;" href="' . $invoice_path . '" data-tip="' . __('Download Store Invoice', 'wcfm-gosend') . '"><span class="wacfmfa fa-currence">' .get_woocommerce_currency_symbol(). '</span><span class="">' . apply_filters( 'wcfm_store_invoice_download_label', wcfm_get_vendor_store_name( absint($vendor_id) ) . ' ' . $sold_by_text . ' ' . __( 'Invoice', 'wc-frontend-manager-ultimate'), $order->get_id(), $vendor_id ) . '</span></a>';
										}
									}
								}
								echo '<div class="wcfm_clearfix"></div><br />';
							}
						}
					}
					?>
					<?php if( apply_filters( 'wcfm_is_allow_order_details_after_order_table', true ) ) { do_action('woocommerce_order_details_after_order_table',  $order ); } ?>
					<?php do_action( 'wcfm_order_details_after_order_table',  $order ); ?>
					
					<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<!-- end collapsible -->
		
		<?php do_action( 'before_wcfm_order_items', $order_id ); ?>
		
		<div class="wcfm-clearfix"></div><br />
		<!-- collapsible -->
		<div class="page_collapsible orders_details_items" id="wcfm_orders_items_options"><?php _e('Order Items', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container">
			<div id="orders_details_items_expander" class="wcfm-content">
				<table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
					<thead>
						<tr>
							<th class="item-thumb no_mob" data-sort="string-ins"></th>
							<th class="item sortable" data-sort="string-ins"><?php _e( 'Item', 'wc-frontend-manager' ); ?></th>
							<?php do_action( 'woocommerce_admin_order_item_headers', $order ); ?>
							<th class="item_cost sortable no_mob" data-sort="float"><?php _e( 'Cost', 'wc-frontend-manager' ); ?></th>
							<th class="item_quantity wcfm_item_qty_heading sortable" data-sort="int"><?php _e( 'Qty', 'wc-frontend-manager' ); ?></th>
							<?php if( $is_wcfm_order_details_line_total_head = apply_filters( 'wcfm_order_details_line_total_head', true ) ) { ?>
								<th class="line_cost sortable" data-sort="float"><?php _e( 'Total', 'wc-frontend-manager' ); ?></th>
							<?php } ?>
							<?php if( $is_wcfm_order_details_tax_line_item = apply_filters( 'wcfm_order_details_tax_line_item', true ) ) { ?>
								<?php
									if ( empty( $legacy_order ) && ! empty( $order_taxes ) ) :
										foreach ( $order_taxes as $tax_id => $tax_item ) :
											$tax_class      = wc_get_tax_class_by_tax_id( $tax_item['rate_id'] );
											$tax_class_name = isset( $classes_options[ $tax_class ] ) ? $classes_options[ $tax_class ] : __( 'Tax', 'wc-frontend-manager' );
											$column_label   = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'wc-frontend-manager' );
											$column_tip     = $tax_item['name'] . ' (' . $tax_class_name . ')';
											?>
											<th class="line_tax text_tip no_ipad no_mob" data-tip="<?php echo esc_attr( $column_tip ); ?>">
												<?php echo esc_attr( $column_label ); ?>
												<input type="hidden" class="order-tax-id" name="order_taxes[<?php echo $tax_id; ?>]" value="<?php echo esc_attr( $tax_item['rate_id'] ); ?>">
												<a class="delete-order-tax" href="#" data-rate_id="<?php echo $tax_id; ?>"></a>
											</th>
											<?php
										endforeach;
									endif;
								?>
							<?php } ?>
							<?php do_action( 'wcfm_order_details_after_line_total_head', $order ); ?>
						</tr>
					</thead>
					<tbody id="order_line_items">
					<?php
						foreach ( $line_items as $item_id => $item ) {
							$_product  = $item->get_product();
							$product_link = '';
							do_action( 'woocommerce_before_order_item_' . $item->get_type() . '_html', $item_id, $item, $order );
							
							if( apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $item->get_product_id() ) && apply_filters( 'wcfm_is_allow_order_details_product_permalink', true ) ) {
								$product_link  = $_product ? get_wcfm_edit_product_url( $item->get_product_id(), $_product ) : '';
							} else {
								if( apply_filters( 'wcfm_is_allow_show_product_permalink', true ) && apply_filters( 'wcfm_is_allow_order_details_product_permalink', true ) ) {
									$product_link  = $_product ? get_permalink( $item->get_product_id() ) : '';
								}
							}
							$thumbnail     = $_product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $_product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
							$tax_data = $item->get_taxes();
							?>
							<tr class="item <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item, $order ); ?>" data-order_item_id="<?php echo $item_id; ?>">
								<td class="thumb no_mob">
									<?php
										echo '<div class="wc-order-item-thumbnail no_ipad">' . wp_kses_post( $thumbnail ) . '</div>';
									?>
								</td>
								<td class="name" data-sort-value="<?php echo esc_attr( $item->get_name() ); ?>">
									<?php
										echo $product_link ? '<a href="' . esc_url( $product_link ) . '" class="wc-order-item-name">' .  esc_html( apply_filters( 'wcfm_order_item_name', $item->get_name(), $item ) ) . '</a>' : '<div class="class="wc-order-item-name"">' . esc_html( apply_filters( 'wcfm_order_item_name', $item->get_name(), $item ) ) . '</div>';
							
										if ( $_product && $_product->get_sku() ) {
											echo '<div class="wc-order-item-sku"><strong>' . __( 'SKU:', 'wc-frontend-manager' ) . '</strong> ' . esc_html( $_product->get_sku() ) . '</div>';
										}
							
										if ( ! empty( $item->get_variation_id() ) ) {
											echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'wc-frontend-manager' ) . '</strong> ';
											if ( ! empty( $item->get_variation_id() ) && 'product_variation' === get_post_type( $item->get_variation_id() ) ) {
												echo esc_html( $item->get_variation_id() );
											} elseif ( ! empty( $item->get_variation_id() ) ) {
												echo esc_html( $item->get_variation_id() ) . ' (' . __( 'No longer exists', 'wc-frontend-manager' ) . ')';
											}
											echo '</div>';
										}
									?>
							
									<div class="view">
									  <?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, $_product ) ?>
									  <?php do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false ); ?>
										<?php wc_display_item_meta( $item ); ?>
										<?php 
										//if( !class_exists( 'WC_Deposits_Order_Item_Manager' ) || ( class_exists( 'WC_Deposits_Order_Item_Manager' ) && !WC_Deposits_Order_Item_Manager::is_deposit( $item ) ) ) {
											do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false ); 
										//}
										?>
										<?php 
										if( !class_exists( 'WC_Deposits_Order_Item_Manager' ) || ( class_exists( 'WC_Deposits_Order_Item_Manager' ) && !WC_Deposits_Order_Item_Manager::is_deposit( $item ) && empty( $item['original_order_id'] ) ) ) {
											do_action( 'woocommerce_after_order_itemmeta', $item_id, $item, $_product );
										}
										
										do_action( 'wcfm_after_order_itemmeta', $item_id, $item, $_product, $order );
										?>
									</div>
								</td>
							
								<?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>
							
								<td class="item_cost no_mob" width="1%" data-sort-value="<?php echo esc_attr( $order->get_item_subtotal( $item, false, true ) ); ?>">
									<div class="view">
										<?php
											if ( $item->get_total() ) {
												echo wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) );
							
												if ( $item->get_subtotal() != $item->get_total() ) {
													echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $order->get_item_subtotal( $item, false, false ) - $order->get_item_total( $item, false, false ), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
												}
											}
										?>
									</div>
								</td>
								<td class="wcfm_item_qty" width="1%">
									<div class="view">
										<?php
											echo '<small class="times">&times;</small> ' . ( $item->get_quantity() ? esc_html( $item->get_quantity() ) : '1' );
							
											if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
												echo '<small class="refunded">-' . ( $refunded_qty * -1 ) . $item_id . '</small>';
											}
										?>
									</div>
								</td>
								
								<?php if( $is_wcfm_order_details_line_total = apply_filters( 'wcfm_order_details_line_total', true ) ) { ?>
									<td class="line_cost" width="1%" data-sort-value="<?php echo esc_attr( ( $item->get_total() ) ? $item->get_total() : '' ); ?>">
										<div class="view">
											<?php
												if ( $item->get_total() ) {
													echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );
												}
								
												if ( $item->get_subtotal() !== $item->get_total() ) {
													echo '<span class="wc-order-item-discount">' . sprintf( esc_html__( '%s discount', 'woocommerce' ), wc_price( wc_format_decimal( $item->get_subtotal() - $item->get_total(), '' ), array( 'currency' => $order->get_currency() ) ) ) . '</span>';
												}
								
												if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
													echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
												}
											?>
										</div>
									</td>
								<?php } ?>
							
								<?php if( $is_wcfm_order_details_tax_line_item = apply_filters( 'wcfm_order_details_tax_line_item', true ) ) { ?>
									<?php
									if ( wc_tax_enabled() ) {
											if ( ! empty( $tax_data ) ) {
												foreach ( $order_taxes as $tax_item ) {
													$tax_item_id       = $tax_item['rate_id'];
													$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : 0;
													$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : 0;
													?>
													<td class="line_tax no_ipad no_mob" width="1%">
														<div class="view">
															<?php
																if ( '' != $tax_item_total ) {
																	echo wc_price( wc_round_tax_total( $tax_item_subtotal ), array( 'currency' => $order->get_currency() ) );
																} else {
																	echo '&ndash;';
																}
									
																if ( $tax_item_subtotal !== $tax_item_total ) {
																	echo '<span class="wc-order-item-discount">-' . wc_price( wc_round_tax_total( $tax_item_subtotal - $tax_item_total ), array( 'currency' => $order->get_currency() ) ) . '</span>';
																}
									
																if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id ) ) {
																	echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
																}
															?>
														</div>
													</td>
													<?php
												}
											}
										}
									?>
								<?php } ?>
								
								<?php do_action( 'wcfm_after_order_details_line_total', $item, $order ); ?>
							
							</tr>
	
							<?php
			
							do_action( 'woocommerce_order_item_' . $item->get_type() . '_html', $item_id, $item, $order );
						}
						do_action( 'woocommerce_admin_order_items_after_line_items', $order->get_id() );
					?>
					</tbody>
					
					<?php if( apply_filters( 'wcfm_order_details_shipping_line_item', true ) && !empty( $line_items_shipping ) ) { ?>
					<tbody id="order_shipping_line_items">
						<tr class="shipping">
							<td class="name" colspan="5"><h2><?php _e( 'Shipping Item(s)', 'wc-frontend-manager' ); ?></h2></td>
							<?php if ( wc_tax_enabled() ) : $total_taxes = count( $order_taxes ); ?>
								<?php for ( $i = 0;  $i < $total_taxes; $i++ ) : ?>
									<td class="line_tax no_ipad no_mob" width="1%"></td>
								<?php endfor; ?>
							<?php endif; ?>
							<?php do_action( 'wcfm_after_order_details_refund_total', '', $order ); ?>
						</tr>
						<?php
						$shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
						foreach ( $line_items_shipping as $item_id => $item ) {
							?>
							<tr class="shipping <?php echo ( ! empty( $class ) ) ? $class : ''; ?>" data-order_item_id="<?php echo $item_id; ?>">
								<td class="thumb no_ipad no_mob"><span class="wcfmfa fa-truck"></span></td>
							
								<td class="name" colspan="<?php echo wp_is_mobile() ? 2 : 3; ?>">
									<div class="view wcfm_order_details_shipping_method_name">
										<?php echo ! empty( $item->get_name() ) ? wc_clean( $item->get_name() ) : __( 'Shipping', 'wc-frontend-manager' ); ?>
									</div>
							
									<div class="view">
									  <?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, null ); ?>
										<?php wc_display_item_meta( $item ); ?>
										<?php do_action( 'woocommerce_after_order_itemmeta', $item_id, $item, null ) ?>
									</div>
								</td>
							
								<?php do_action( 'woocommerce_admin_order_item_values', null, $item, absint( $item_id ) ); ?>
							
								<td class="line_cost" width="1%">
									<div class="view">
										<?php
											echo ( isset( $item['cost'] ) ) ? wc_price( wc_round_tax_total( $item['cost'] ), array( 'currency' => $order->get_currency() ) ) : '';
							
											if ( $refunded = $order->get_total_refunded_for_item( $item_id, 'shipping' ) ) {
												echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
											}
										?>
									</div>
								</td>
							
								<?php if( apply_filters( 'wcfm_order_details_tax_line_item', true ) ) { ?>
									<?php
										if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
											foreach ( $order_taxes as $tax_item ) {
												$tax_item_id    = $tax_item->get_rate_id();
												$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
												?>
													<td class="line_tax no_ipad no_mob" width="1%">
														<div class="view">
															<?php
																echo ( '' != $tax_item_total ) ? wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) ) : '&ndash;';
								
																if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id, 'shipping' ) ) {
																	echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
																}
															?>
														</div>
													</td>
								
												<?php
											}
										}
									?>
								<?php } ?>
								
								<?php do_action( 'wcfm_after_order_details_shipping_total', $item, $order ); ?>
							
							</tr>
							<?php
						}
						do_action( 'woocommerce_admin_order_items_after_shipping', $order->get_id() );
					?>
					</tbody>
					<?php } ?>
					
					<?php if( apply_filters( 'wcfm_order_details_fee_line_item', true ) && !empty( $line_items_fee ) ) { ?>
					<tbody id="order_fee_line_items">
						<tr class="shippin">
							<td class="name" colspan="5"><h2><?php _e( 'Fee Item(s)', 'wc-frontend-manager' ); ?></h2></td>
							<?php if ( wc_tax_enabled() ) : $total_taxes = count( $order_taxes ); ?>
								<?php for ( $i = 0;  $i < $total_taxes; $i++ ) : ?>
									<td class="line_tax no_ipad no_mob" width="1%"></td>
								<?php endfor; ?>
							<?php endif; ?>
							<?php do_action( 'wcfm_after_order_details_refund_total', '', $order ); ?>
						</tr>
						<?php
						foreach ( $line_items_fee as $item_id => $item ) {
							?>
							<tr class="fee <?php echo ( ! empty( $class ) ) ? $class : ''; ?>" data-order_item_id="<?php echo $item_id; ?>">
								<td class="thumb no_ipad no_mob"><span class="wcfmfa fa-plus-circle"></span></td>
							
								<td class="name" colspan="3">
									<div class="view">
										<?php echo ! empty( $item->get_name() ) ? esc_html( $item->get_name() ) : __( 'Fee', 'wc-frontend-manager' ); ?>
									</div>
								</td>
							
								<?php do_action( 'woocommerce_admin_order_item_values', null, $item, absint( $item_id ) ); ?>
							
								<td class="line_cost" width="1%">
									<div class="view">
										<?php
											echo ( $item->get_total() ) ? wc_price( wc_round_tax_total( $item->get_total() ), array( 'currency' => $order->get_currency() ) ) : '';
							
											if ( $refunded = $order->get_total_refunded_for_item( $item_id, 'fee' ) ) {
												echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
											}
										?>
									</div>
								</td>
							
								<?php if( apply_filters( 'wcfm_order_details_tax_line_item', true ) ) { ?>
									<?php
										if ( empty( $legacy_order ) && wc_tax_enabled() ) :
											$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
											$tax_data      = maybe_unserialize( $line_tax_data );
								
											foreach ( $order_taxes as $tax_item ) :
												$tax_item_id       = $tax_item['rate_id'];
												$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
												?>
													<td class="line_tax no_ipad no_mob" width="1%">
														<div class="view">
															<?php
																echo ( '' != $tax_item_total ) ? wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) ) : '&ndash;';
								
																if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id, 'fee' ) ) {
																	echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
																}
															?>
														</div>
													</td>
								
												<?php
											endforeach;
										endif;
									?>
								<?php } ?>
							
							</tr>
							<?php
						}
						do_action( 'woocommerce_admin_order_items_after_fees', $order->get_id() );
					?>
					</tbody>
					<?php } ?>
					
					<?php if( apply_filters( 'wcfm_order_details_refund_line_item', true ) && !empty( $order->get_refunds() ) ) { ?>
					<tbody id="order_refunds">
					  <tr class="shippin">
							<td class="name" colspan="5"><h2 style="color: #a00;"><?php _e( 'Refund(s)', 'wc-frontend-manager' ); ?></h2></td>
							<?php if ( wc_tax_enabled() ) : $total_taxes = count( $order_taxes ); ?>
								<?php for ( $i = 0;  $i < $total_taxes; $i++ ) : ?>
									<td class="line_tax no_ipad no_mob" width="1%"></td>
								<?php endfor; ?>
							<?php endif; ?>
							<?php do_action( 'wcfm_after_order_details_refund_total', '', $order ); ?>
						</tr>
						<?php
						if ( $refunds = $order->get_refunds() ) {
							$cur_vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
							foreach ( $refunds as $refund ) {
								$who_refunded = new WP_User( $refund->get_refunded_by() );
								if( wcfm_is_vendor() && ( !$who_refunded || ( $who_refunded && ( $who_refunded->ID != $cur_vendor_id ) ) ) ) continue;
								?>
								<tr class="refund <?php echo ( ! empty( $class ) ) ? $class : ''; ?>" data-order_refund_id="<?php echo $refund->get_id(); ?>">
									<td class="thumb no_ipad no_mob"><span class="wcicon-status-refunded"></span></td>
								
									<td class="name" colspan="3">
										<?php
											/* translators: 1: refund id 2: date */
											printf( __( 'Refund #%1$s - %2$s', 'woocommerce' ), $refund->get_id(), wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) );
								
											if ( $who_refunded->exists() ) {
												echo '<div class="wcfm_clearfix"></div>' . esc_attr_x( 'by', 'Ex: Refund - $date >by< $username', 'woocommerce' ) . ' ' . '<abbr class="refund_by" title="' . sprintf( esc_attr__( 'ID: %d', 'woocommerce' ), absint( $who_refunded->ID ) ) . '">' . esc_attr( $who_refunded->display_name ) . '</abbr>' ;
											}
										?>
										<?php if ( $refund->get_reason() ) : ?>
											<div class="wcfm_clearfix"></div>
											<p class="description"><?php echo esc_html( $refund->get_reason() ); ?></p>
										<?php endif; ?>
									</td>
								
									<?php do_action( 'woocommerce_admin_order_item_values', null, $refund, $refund->get_id() ); ?>
								
									<td class="line_cost refunded-total" width="1%">
										<div class="view">
											<?php echo wc_price( '-' . $refund->get_amount() ); ?>
										</div>
									</td>
								
									<?php if ( wc_tax_enabled() ) : $total_taxes = count( $order_taxes ); ?>
										<?php for ( $i = 0;  $i < $total_taxes; $i++ ) : ?>
											<td class="line_tax no_ipad no_mob" width="1%"></td>
										<?php endfor; ?>
									<?php endif; ?>
									
									<?php do_action( 'wcfm_after_order_details_refund_total', $item, $order ); ?>
							 </tr>
									<?php
							}
							do_action( 'woocommerce_admin_order_items_after_refunds', $order->get_id() );
						}
					?>
					</tbody>
					<?php } ?>
				</table>
				
				<div class="wc-order-data-row wc-order-totals-items wc-order-items-editable">
					<?php //if( apply_filters( 'wcfm_order_details_coupon_line_item', true ) ) { ?>
						<?php
							$coupons = $order->get_items( array( 'coupon' ) );
							if ( $coupons ) {
								?>
								<div class="wc-used-coupons">
									<ul class="wc_coupon_list"><?php
										echo '<li><strong>' . __( 'Coupon(s) Used', 'wc-frontend-manager' ) . '</strong></li>';
										foreach ( $coupons as $item_id => $item ) {
											$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item->get_name() ) );
					
											if( apply_filters( 'wcfm_is_allow_show_only_vendor_coupon_to_vendors', true ) && wcfm_is_vendor() && ( !wcfm_get_vendor_id_by_post( $post_id ) || ( wcfm_get_vendor_id_by_post( $post_id ) && ($WCFMmp->vendor_id != wcfm_get_vendor_id_by_post( $post_id ) ) ) ) ) continue;
											
											$link = $post_id ? get_wcfm_coupons_manage_url( $post_id ) : get_wcfm_coupons_url();
											
											if( !apply_filters( 'wcfm_is_allow_manage_coupons', true ) || wcfm_is_vendor() ) { $link = '#'; }
					
											echo '<li class="code"><a target="_blank" href="' . esc_url( $link ) . '" class="img_tip" data-tip="' . esc_attr( wc_price( $item['discount_amount'], array( 'currency' => $order->get_currency() ) ) ) . '"><span>' . esc_html( $item->get_name() ). '</span></a></li>';
										}
									?></ul>
								</div>
								<?php
							}
						?>
					<?php //} ?>
					
					<table class="wc-order-totals">
						<?php if( apply_filters( 'wcfm_order_details_coupon_line_item', true ) ) { ?>
							<tr>
								<th class="label"><span class="wcfmfa fa-question no_mob img_tip" data-tip="<?php _e( 'This is the total discount. Discounts are defined per line item.', 'wc-frontend-manager' ) ; ?>"></span> <?php _e( 'Discount', 'wc-frontend-manager' ); ?>:</th>
								<td width="1%"></td>
								<td class="total">
									<?php echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) ); ?>
								</td>
							</tr>
						<?php } ?>
				
						<?php do_action( 'woocommerce_admin_order_totals_after_discount', $order->get_id() ); ?>
				
						<?php if( apply_filters( 'wcfm_order_details_shipping_line_item', true ) && apply_filters( 'wcfm_order_details_shipping_total', true ) && $order->get_formatted_shipping_address() ) { ?>
							<tr>
								<th class="label"><span class="wcfmfa fa-question no_mob img_tip" data-tip="<?php _e( 'This is the shipping and handling total costs for the order.', 'wc-frontend-manager' ) ; ?>"></span> <?php _e( 'Shipping', 'wc-frontend-manager' ); ?>:</th>
								<td width="1%"></td>
								<td class="total"><?php
									if ( ( $refunded = $order->get_total_shipping_refunded() ) > 0 ) {
										echo '<del>' . strip_tags( wc_price( $order->get_total_shipping(), array( 'currency' => $order->get_currency() ) ) ) . '</del> <ins>' . wc_price( $order->get_total_shipping() - $refunded, array( 'currency' => $order->get_currency() ) ) . '</ins>';
									} else {
										echo wc_price( $order->get_total_shipping(), array( 'currency' => $order->get_currency() ) );
									}
									//echo "<br /><small>";
									//_e(' via ', 'wc-frontend-manager');
									//echo $order->get_shipping_method();
									//echo "</small>";
								?></td>
							</tr>
						<?php } ?>
				
						<?php do_action( 'woocommerce_admin_order_totals_after_shipping', $order->get_id() ); ?>
				
						<?php if( apply_filters( 'wcfm_order_details_tax_total', true ) ) { ?>
							<?php if ( wc_tax_enabled() ) : ?>
								<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
									<tr>
										<th class="label"><?php echo $tax->label; ?>:</th>
										<td width="1%"></td>
										<td class="total"><?php
											if ( ( $refunded = $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ) ) > 0 ) {
												echo '<del>' . strip_tags( $tax->formatted_amount ) . '</del> <ins>' . wc_price( WC_Tax::round( $tax->amount, wc_get_price_decimals() ) - WC_Tax::round( $refunded, wc_get_price_decimals() ), array( 'currency' => $order->get_currency() ) ) . '</ins>';
											} else {
												echo $tax->formatted_amount;
											}
										?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php } ?>
				
						<?php do_action( 'woocommerce_admin_order_totals_after_tax', $order->get_id() ); ?>
				
						<?php if( $is_wcfm_order_details_total = apply_filters( 'wcfm_order_details_total', true ) ) { ?>
						<tr>
							<th class="label"><?php _e( 'Order Total', 'wc-frontend-manager' ); ?>:</th>
							<td width="1%"></td>
							<td class="total">
								<div class="view"><?php echo $order->get_formatted_order_total(); ?></div>
							</td>
						</tr>
						<?php } ?>
				
						<?php if( apply_filters( 'wcfm_order_details_refund_line_item', true ) && apply_filters( 'wcfm_order_details_refund_total', true ) ) { ?>
							<?php if ( $order->get_total_refunded() ) : ?>
								<tr>
									<th class="label refunded-total"><?php _e( 'Refunded', 'wc-frontend-manager' ); ?>:</th>
									<td width="1%"></td>
									<td class="total refunded-total">-<?php echo wc_price( $order->get_total_refunded(), array( 'currency' => $order->get_currency() ) ); ?></td>
								</tr>
							<?php endif; ?>
						<?php } ?>
						
						<?php if( ( $marketplece = wcfm_is_marketplace() ) && !wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_view_commission', true ) && apply_filters( 'wcfm_is_allow_commission_manage', true ) && !in_array( $current_order_status, array( 'failed', 'cancelled', 'refunded', 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) { ?>
						<tr>
							<th class="label"><?php _e( 'Vendor(s) Earning', 'wc-frontend-manager' ); ?>:</th>
							<td width="1%"></td>
							<td class="total">
								<div class="view">
								  <?php 
								  $commission = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_order( $order->get_id() );
									if( $commission ) {
										echo  wc_price( $commission, array( 'currency' => $order->get_currency() ) );
									} else {
										echo  __( 'N/A', 'wc-frontend-manager' );
									}
								  ?>
								 </div>
							</td>
						</tr>
						<tr>
							<th class="label"><?php _e( 'Admin Fee', 'wc-frontend-manager' ); ?>:</th>
							<td width="1%"></td>
							<td class="total">
								<div class="view">
								  <?php 
									if( $commission ) {
										$gross_sales  = (float) $order->get_total();
										$total_refund = (float) $order->get_total_refunded();
										//if( $admin_fee_mode || ( $marketplece == 'dokan' ) ) {
											$commission = $gross_sales - $total_refund - $commission;
										//}
										echo  wc_price( $commission, array( 'currency' => $order->get_currency() ) );
									} else {
										echo  __( 'N/A', 'wc-frontend-manager' );
									}
								  ?>
								 </div>
							</td>
						</tr>
						<?php } ?>
						
						<?php do_action( 'wcfm_order_totals_after_total', $order->get_id() ); ?>
				
						<?php 
						//do_action( 'woocommerce_admin_order_totals_after_refunded', $order->get_id() ); 
						?>
				
					</table>
					<div class="wcfm-clearfix"></div>
				</div>
				
				<?php do_action( 'after_wcfm_orders_details_items', $order_id, $order, $line_items ); ?>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<!-- end collapsible -->
		<?php do_action( 'end_wcfm_orders_details', $order_id ); ?>
	</div>
</div>


<?php
do_action( 'after_wcfm_orders_details', $order_id );
?>