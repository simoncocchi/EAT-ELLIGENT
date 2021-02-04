<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/wcfm/emails/store-new-order.php
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/views/emails
 * @version   1.0.0
 */
if (!defined('ABSPATH'))
    return; // Exit if accessed directly
  
global $WCFM, $WCFMmp;

if( !is_a( $order , 'WC_Order' ) ) return;

do_action( 'woocommerce_email_header', $email_heading, $email );

// Get line items
$line_items          = $order->get_items( 'line_item' );
$line_items_fee      = $order->get_items( 'fee' );
$line_items_shipping = $order->get_items( 'shipping' );

$order_taxes = $classes_options = array();
if( $is_wcfm_order_details_tax_line_item = apply_filters( 'wcfm_order_details_tax_line_item', true ) ) {
	if ( wc_tax_enabled() ) {
		$order_taxes         = $order->get_taxes();
		$tax_classes         = WC_Tax::get_tax_classes();
		$classes_options[''] = __( 'Standard', 'wc-multivendor-marketplace' );
	
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

// Marketplace Filters
$line_items          = apply_filters( 'wcfm_valid_line_items', $line_items, $order->get_id() );
$line_items_shipping = apply_filters( 'wcfm_valid_shipping_items', $line_items_shipping, $order->get_id() );

$text_align = is_rtl() ? 'right' : 'left';
?>

<style>
.order-again, div.wc-booking-summary-actions, div.wc-appointment-summary-actions { display: none; }
table.wcfm-order-details-table th, table.shop_table th, table.wcfm-order-details-table td, table.shop_table td { border: 1px solid #e5e5e5; color: #636363; }
table.wcfm-order-total-table th{border-right:0px;}
table.wcfm-order-total-table td{border-left:0px;}
</style>

<p><?php printf(__('A new order was received from %s. Order details is as follows:', 'wc-multivendor-marketplace'), $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?></p>

<h2 class="woocommerce-order-details__title" style="color: #00798b;">
  <?php if( wcfm_vendor_has_capability( $vendor_id, 'view_orders' ) && wcfm_vendor_has_capability( $vendor_id, 'view_order_details' ) ) { ?>
  	<a href="<?php echo get_wcfm_view_order_url( $order->get_id() ); ?>"><?php _e('Order', 'wc-multivendor-marketplace'); ?> #<?php echo $order->get_order_number() . ' ('.date_i18n(wc_date_format(), strtotime($order->get_date_created())).')'; ?></a>
  <?php } else { ?>
  	<?php _e('Order', 'wc-multivendor-marketplace'); ?> #<?php echo $order->get_order_number() . ' ('.date_i18n(wc_date_format(), strtotime($order->get_date_created())).')'; ?>
  <?php } ?>
</h2>

<?php 
if( apply_filters( 'wcfm_is_allow_email_before_order_table', true ) ) {
	do_action('woocommerce_email_before_order_table', $order, true, false, $email ); 
}
?>

<div style="margin-bottom: 40px;">

	<table class="woocommerce-table wcfm-order-details-table woocommerce-table--order-details shop_table order_details" style="width: 100%; border: 1px solid #e5e5e5;" border="0" cellspacing="0" cellpadding="6">
		<thead>
			<tr>
				<th class="product" colspan="2"><?php _e('Product', 'wc-frontend-manager' ); ?></th>
				<th class="price"><?php _e('Price', 'wc-frontend-manager' ); ?></th>
				<?php if( $is_wcfm_order_details_line_total_head = apply_filters( 'wcfm_order_details_line_total_head', true ) ) { ?>
					<th class="line_cost"><?php _e( 'Total', 'wc-frontend-manager' ); ?></th>
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
								<th class="line_tax text_tip" data-tip="<?php echo esc_attr( $column_tip ); ?>">
									<?php echo esc_attr( $column_label ); ?>
								</th>
								<?php
							endforeach;
						endif;
					?>
				<?php } ?>
				<?php do_action( 'wcfm_order_details_after_line_total_head', $order ); ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $line_items as $item_id => $item ) : $_product  = $item->get_product(); ?>
			<tr>
				<td class="product" colspan="2">
				  <?php
				  // Show title/image etc.
				  $image         = $_product->get_image( array( 32, 32 ) );
					if ( apply_filters( 'wcfm_is_allow_store_order_email_product_thumb', true ) ) {
						echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) );
					}
					?>
					<span class="item-name"><a style="color: #00798b;" href="<?php echo get_permalink( $item->get_product_id() ); ?>"><?php echo wp_kses_post( apply_filters( 'wcfm_order_item_name', apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ), $item ) ); ?></a></span>
					<span class="item-meta">
					  <?php if( $_product && $_product->get_sku() ) : ?><div class="wc-order-item-variation"><strong><?php _e( 'SKU:', 'wc-multivendor-marketplace' ); ?></strong> <?php echo wp_kses_post( $_product->get_sku() ); ?></div><?php endif; ?>
						<?php
						if ( ! empty( $item->get_variation_id() ) ) {
								echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'wc-multivendor-marketplace' ) . '</strong> ';
								if ( ! empty( $item->get_variation_id() ) && 'product_variation' === get_post_type( $item->get_variation_id() ) ) {
									echo esc_html( $item->get_variation_id() );
								} elseif ( ! empty( $item->get_variation_id() ) ) {
									echo esc_html( $item->get_variation_id() ) . ' (' . __( 'No longer exists', 'wc-multivendor-marketplace' ) . ')';
								}
								echo '</div>';
							}
						?>
				
						<div class="view">
						  <?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, $_product ) ?>
							<?php
								do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );
								wc_display_item_meta( $item );
								do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
							?>
						</div>
					</span>
				</td>
				<td class="price">
					<?php
						if ( $item->get_total() ) {
							echo wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) );
		
							if ( $item->get_subtotal() != $item->get_total() ) {
								//echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $order->get_item_subtotal( $item, false, false ) - $order->get_item_total( $item, false, false ), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
							}
						}
					?>
					
						<?php
							echo '<small class="times">&times;</small> ' . ( $item->get_quantity() ? esc_html( $item->get_quantity() ) : '1' );
			
							if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
								echo '<small class="refunded">-' . ( $refunded_qty * -1 ) . '</small>';
							}
						?>
				</td>
				<?php if( $is_wcfm_order_details_line_total = apply_filters( 'wcfm_order_details_line_total', true ) ) { ?>
					<td class="line_cost" data-sort-value="<?php echo esc_attr( ( $item->get_total() ) ? $item->get_total() : '' ); ?>">
						<div class="view">
							<?php
								if ( $item->get_total() ) {
									echo wc_price( $item->get_subtotal(), array( 'currency' => $order->get_currency() ) );
								}
				
								if ( $item->get_subtotal() !== $item->get_total() ) {
									//echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $item->get_subtotal() - $item->get_total(), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
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
					if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
							foreach ( $order_taxes as $tax_item ) {
								$tax_item_id       = $tax_item['rate_id'];
								$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
								$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';
								?>
								<td class="line_tax">
									<div class="view">
										<?php
											if ( '' != $tax_item_total ) {
												echo wc_price( wc_round_tax_total( $tax_item_subtotal ), array( 'currency' => $order->get_currency() ) );
											} else {
												echo '&ndash;';
											}
				
											if ( $item->get_subtotal() !== $item->get_total() ) {
												//echo '<span class="wc-order-item-discount">-' . wc_price( wc_round_tax_total( $tax_item_subtotal - $tax_item_total ), array( 'currency' => $order->get_currency() ) ) . '</span>';
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
					?>
				<?php } ?>
				
				<?php do_action( 'wcfm_after_order_details_line_total', $item, $order ); ?>
									
			</tr>
			<?php endforeach; ?>
		</tbody>
		
		
		<?php if( $order->get_formatted_shipping_address() && apply_filters( 'wcfm_order_details_shipping_line_item', true ) ) { ?>
			<tbody id="order_shipping_line_items">
				<?php
				$shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
				foreach ( $line_items_shipping as $item_id => $item ) {
					?>
					<tr class="shipping <?php echo ( ! empty( $class ) ) ? $class : ''; ?>" data-order_item_id="<?php echo $item_id; ?>">
						<td class="name" colspan="3">
							<div class="view">
								<?php echo ! empty( $item->get_name() ) ? wc_clean( $item->get_name() ) : __( 'Shipping', 'wc-multivendor-marketplace' ); ?>
							</div>
					
							<div class="view">
							  <?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, null ) ?>
								<?php wc_display_item_meta( $item ); ?>
								<?php do_action( 'woocommerce_after_order_itemmeta', $item_id, $item, null ) ?>
							</div>
						</td>
					
						<?php do_action( 'woocommerce_admin_order_item_values', null, $item, absint( $item_id ) ); ?>
					
						<td class="line_cost">
							<div class="view">
								<?php
									echo ( isset( $item['cost'] ) ) ? wc_price( wc_round_tax_total( $item['cost'] ), array( 'currency' => $order->get_currency() ) ) : '';
					
									if ( $refunded = $order->get_total_refunded_for_item( $item_id, 'shipping' ) ) {
										echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
									}
								?>
							</div>
						</td>
					
						<?php if( $is_wcfm_order_details_tax_line_item = apply_filters( 'wcfm_order_details_tax_line_item', true ) ) { ?>
							<?php
								if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
									foreach ( $order_taxes as $tax_item ) {
										$tax_item_id    = $tax_item->get_rate_id();
										$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
										?>
											<td class="line_tax no_ipad no_mob" >
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
		
		<?php if( $is_wcfm_order_details_fee_line_item = apply_filters( 'wcfm_order_details_fee_line_item', true ) ) { ?>
			<tbody id="order_fee_line_items">
				<?php
					foreach ( $line_items_fee as $item_id => $item ) {
						?>
						<tr class="fee <?php echo ( ! empty( $class ) ) ? $class : ''; ?>" data-order_item_id="<?php echo $item_id; ?>">
							<td class="name" colspan="3">
								<div class="view">
									<?php echo ! empty( $item->get_name() ) ? esc_html( $item->get_name() ) : __( 'Fee', 'wc-multivendor-marketplace' ); ?>
								</div>
							</td>
						
							<?php do_action( 'woocommerce_admin_order_item_values', null, $item, absint( $item_id ) ); ?>
						
							<td class="line_cost">
								<div class="view">
									<?php
										echo ( $item->get_total() ) ? wc_price( wc_round_tax_total( $item->get_total() ), array( 'currency' => $order->get_currency() ) ) : '';
						
										if ( $refunded = $order->get_total_refunded_for_item( $item_id, 'fee' ) ) {
											echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
										}
									?>
								</div>
							</td>
						
							<?php if( $is_wcfm_order_details_tax_line_item = apply_filters( 'wcfm_order_details_tax_line_item', true ) ) { ?>
								<?php
									if ( empty( $legacy_order ) && wc_tax_enabled() ) :
										$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
										$tax_data      = maybe_unserialize( $line_tax_data );
							
										foreach ( $order_taxes as $tax_item ) :
											$tax_item_id       = $tax_item['rate_id'];
											$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
											?>
												<td class="line_tax no_ipad no_mob" >
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
		
	</table>

	<table class="woocommerce-table wcfm-order-details-table wcfm-order-total-table woocommerce-table--order-details shop_table order_details" style="width: 100%; border: 1px solid #e5e5e5; border-top: 4px solid #e5e5e5; text-align:right;" border="0" border="0" cellspacing="0" cellpadding="6">
		<tbody>
			<?php if( $is_wcfm_order_details_coupon_line_item = apply_filters( 'wcfm_order_details_coupon_line_item', true ) ) { ?>
				<tr style="width: 60%;">
					<td class="label description" colspan="2" style="text-align:right; width:75%; border-bottom: 1px solid #eee;"><span class="img_tip" data-tip="<?php _e( 'This is the total discount. Discounts are defined per line item.', 'wc-multivendor-marketplace' ) ; ?>"></span> <?php _e( 'Discount', 'wc-multivendor-marketplace' ); ?>:</td>
					<td class="total price" style="text-align:center; border-bottom: 1px solid #eee;">
						<?php echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) ); ?>
					</td>
				</tr>
			<?php } ?>
	
			<?php //do_action( 'woocommerce_admin_order_totals_after_discount', $order->get_id() ); ?>
	
			<?php if( $order->get_formatted_shipping_address() && apply_filters( 'wcfm_order_details_shipping_line_item', true ) && apply_filters( 'wcfm_order_details_shipping_total', true ) ) { ?>
				<tr>
					<td class="label description" colspan="2" style="text-align:right; width:75%; border-bottom: 1px solid #eee;"><span class="img_tip" data-tip="<?php _e( 'This is the shipping and handling total costs for the order.', 'wc-multivendor-marketplace' ) ; ?>"></span> <?php _e( 'Shipping', 'wc-multivendor-marketplace' ); ?>:</td>
					<td class="total price" style="text-align:center; border-bottom: 1px solid #eee;"><?php
						if ( ( $refunded = $order->get_total_shipping_refunded() ) > 0 ) {
							echo '<del>' . strip_tags( wc_price( $order->get_total_shipping(), array( 'currency' => $order->get_currency() ) ) ) . '</del> <ins>' . wc_price( $order->get_total_shipping() - $refunded, array( 'currency' => $order->get_currency() ) ) . '</ins>';
						} else {
							echo wc_price( $order->get_total_shipping(), array( 'currency' => $order->get_currency() ) );
						}
					?></td>
				</tr>
			<?php } ?>
	
			<?php //do_action( 'woocommerce_admin_order_totals_after_shipping', $order->get_id() ); ?>
	
			<?php if( $is_wcfm_order_details_tax_total = apply_filters( 'wcfm_order_details_tax_total', true ) ) { ?>
				<?php if ( wc_tax_enabled() ) : ?>
					<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
						<tr>
							<td class="label description" colspan="2" style="text-align:right; width:75%; border-bottom: 1px solid #eee;"><?php echo $tax->label; ?>:</td>
							<td class="total price" style="text-align:center; border-bottom: 1px solid #eee;"><?php
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
	
			<?php //do_action( 'woocommerce_admin_order_totals_after_tax', $order->get_id() ); ?>
	
			<?php if( apply_filters( 'wcfm_order_details_total', true ) ) { ?>
			<tr>
				<td class="label description" colspan="2" style="text-align:right; width:75%; border-bottom: 1px solid #eee;"><?php _e( 'Order Total', 'wc-multivendor-marketplace' ); ?>:</td>
				<td class="total price" style="text-align:center; border-bottom: 1px solid #eee;">
					<div class="view"><?php echo $order->get_formatted_order_total(); ?></div>
				</td>
			</tr>
			<?php } ?>
	
			<?php do_action( 'wcfm_order_totals_after_total', $order->get_id() ); ?>
	
			<?php if( apply_filters( 'wcfm_order_details_refund_line_item', true ) && apply_filters( 'wcfm_order_details_refund_total', true ) ) { ?>
				<?php if ( $order->get_total_refunded() ) : ?>
					<tr>
						<td class="label refunded-total description" colspan="2" style="text-align:right; width:75%; border-bottom: 1px solid #eee;"><?php _e( 'Refunded', 'wc-multivendor-marketplace' ); ?>:</td>
						<td class="total refunded-total price" style="text-align:center; border-bottom: 1px solid #eee;">-<?php echo wc_price( $order->get_total_refunded(), array( 'currency' => $order->get_currency() ) ); ?></td>
					</tr>
				<?php endif; ?>
			<?php } ?>
		</tbody>
	</table>
</div>
<br />

<?php if( wcfm_vendor_has_capability( $vendor_id, 'view_customers' ) && wcfm_vendor_has_capability( $vendor_id, 'view_email' ) ) { ?>
	<h2 class="woocommerce-order-details__title" style="color: #00798b;"><?php _e('Customer Details', 'wc-multivendor-marketplace'); ?></h2>
	<?php if ($order->get_billing_email() ) { ?>
		<p><strong><?php _e('Customer Name:', 'wc-multivendor-marketplace'); ?></strong> <?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></p>
		<p><strong><?php _e('Email:', 'wc-multivendor-marketplace'); ?></strong> <?php echo $order->get_billing_email(); ?></p>
	<?php } ?>
	<?php if ($order->get_billing_phone()) { ?>
		<p><strong><?php _e('Telephone:', 'wc-multivendor-marketplace'); ?></strong> <?php echo $order->get_billing_phone(); ?></p>
	<?php
	}
}
?>

<?php
if( apply_filters( 'wcfm_is_allow_wc_default_email_order_meta', true ) ) {
	do_action( 'woocommerce_email_order_meta', $order, 0, 0, $email );
}

if( apply_filters( 'wcfm_is_allow_wc_default_email_customer_details', false ) ) {
	do_action( 'woocommerce_email_customer_details', $order, 0, 0, $email );
} else {
	?>
	<?php if( apply_filters( 'wcfm_is_allow_order_details_after_order_table', true ) ) { do_action('woocommerce_order_details_after_order_table',  $order ); } ?>
	<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
		<tr>
			<?php if( wcfm_vendor_has_capability( $vendor_id, 'view_billing_details' ) ) { ?>
				<td style="text-align:<?php echo $text_align; ?>; border:0; padding:0 2px;" valign="top" width="50%">
					<h2><?php _e( 'Billing address', 'wc-multivendor-marketplace' ); ?></h2>
					<address class="address"><?php echo $order->get_formatted_billing_address(); ?></address>
				</td>
			<?php } ?>
			
			<?php if( ( $shipping = $order->get_formatted_shipping_address() ) && $order->needs_shipping_address() && wcfm_vendor_has_capability( $vendor_id, 'view_shipping_details' ) ) { ?>
				<td style="text-align:<?php echo $text_align; ?>; padding:0 2px;" valign="top" width="50%">
					<h2><?php _e( 'Shipping address', 'wc-multivendor-marketplace' ); ?></h2>
					<address class="address"><?php echo $shipping; ?></address>
				</td>
			<?php } ?>
		</tr>
	</table>
<?php } ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>