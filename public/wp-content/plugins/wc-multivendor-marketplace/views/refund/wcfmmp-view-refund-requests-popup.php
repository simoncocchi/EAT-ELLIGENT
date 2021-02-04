<?php
/**
 * WCFM plugin view
 *
 * WCfM Refund popup View
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/views/refund
 * @version   1.0.0
 */
 
global $wp, $WCFM, $WCFMmp, $_POST, $wpdb;

$order_id      = sanitize_text_field( $_POST['order_id'] );
$order_id = str_replace( '#', '', $order_id );

if( !$order_id ) return;

$item_id = 0;
if( isset( $_POST['item_id'] ) ) {
	$item_id       = sanitize_text_field( $_POST['item_id'] );
}

$commission_id = 0;
if( isset( $_POST['commission_id'] ) ) {
	$commission_id = sanitize_text_field( $_POST['commission_id'] );
}

$customer_refund = 'no';
if( isset( $_POST['customer_refund'] ) ) {
	$customer_refund = sanitize_text_field( $_POST['customer_refund'] );
}


$order                  = wc_get_order( $order_id );
$order_taxes            = $order->get_taxes();
$currency               = $order->get_currency();

$line_items             = $order->get_items( 'line_item' );
if( $customer_refund != 'yes' ) {
	$line_items             = apply_filters( 'wcfm_valid_line_items', $line_items, $order_id );
}

$product_items          = array();
foreach ( $line_items as $item_id => $item ) {
	$order_item_id = $item->get_id();
	
	$refunded_amount = $order->get_total_refunded_for_item( $order_item_id );
	$refunded_qty    = $order->get_qty_refunded_for_item( $order_item_id );
	if( $refunded_qty ) $refunded_qty = ( $refunded_qty * -1 );
	
	$sql  = "SELECT ID, withdraw_status, vendor_id, refund_status, is_partially_refunded, is_refunded FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
	$sql .= " WHERE 1=1";
	$sql .= " AND `item_id` = " . $order_item_id;
	//$sql .= " AND (`is_refunded` = 1 OR `is_partially_refunded` = 1)";
	//$sql .= " AND `refund_status` in ('completed', 'requested')";
	$commissions = $wpdb->get_results( $sql );
	if( !empty( $commissions ) ) {
		foreach( $commissions as $commission ) {
			if( ( $commission->is_refunded != 1 ) && ( $commission->refund_status != 'requested' ) && ( ( $commission->is_partially_refunded != 1 ) || ( ( $commission->is_partially_refunded == 1 ) && ( $commission->refund_status == 'completed' ) ) ) ) {
				$product_items[$order_item_id] = array( 'name' => $item->get_name(), 'cost' => $order->get_item_subtotal( $item, false, true ), 'qty' => ( $item->get_quantity() - $refunded_qty ), 'total' => ( $item->get_total() - $refunded_amount ), 'tax' => $item->get_taxes() );
			}
		}
	} else {
		$product_items[$order_item_id] = array( 'name' => $item->get_name(), 'cost' => $order->get_item_subtotal( $item, false, true ), 'qty' => ( $item->get_quantity() - $refunded_qty ), 'total' => ( $item->get_total() - $refunded_amount ), 'tax' => $item->get_taxes() );
	}
}

$request_mode = apply_filters( 'wcfm_refund_request_default_mode', 'partial' );
?>

<div class="wcfm-clearfix"></div><br />
<div id="wcfm_refund_form_wrapper">
	<form action="" method="post" id="wcfm_refund_requests_form" class="refund-form wcfm_popup_wrapper" novalidate="">
		<div style="margin-bottom: 15px;"><h2 style="float: none;"><?php _e( 'Refund Request', 'wc-multivendor-marketplace' ); ?></h2></div>
		
		<?php if( !empty( $product_items ) ) { ?>
			
			<p class="wcfm-refund-form-request wcfm_popup_label">
				<label for="wcfm_refund_request"><strong><?php _e( 'Request Mode', 'wc-multivendor-marketplace' ); ?> <span class="required">*</span></strong></label> 
			</p>
			<?php $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_refund_fields_request', array( "wcfm_refund_request" => array( 'type' => 'select', 'class' => 'wcfm-select wcfm_ele wcfm_popup_input', 'label_class' => 'wcfm_title', 'options' => array( 'full' => __( 'Full Refund', 'wc-multivendor-marketplace' ), 'partial' => __( 'Partial Refund', 'wc-multivendor-marketplace' ) ), 'value' => $request_mode ) ) ) ); ?>
			
			<div class="wcfm_clearfix"></div>
			<div class="wcfm_refund_items_ele" style="margin-bottom: 15px;"><h2 style="float: none;"><?php _e( 'Refund by Item(s)', 'wc-multivendor-marketplace' ); ?></h2></div>
			<div class="wcfm_clearfix"></div>
			
			<table cellpadding="0" cellspacing="0" class="woocommerce_order_items wcfm_refund_items_ele">
				<thead>
					<tr>
						<th class="item sortable" data-sort="string-ins"><?php _e( 'Item', 'wc-frontend-manager' ); ?></th>
						<th class="item_cost sortable no_mob" data-sort="float" style="text-align:center;"><?php _e( 'Cost', 'wc-frontend-manager' ); ?></th>
						<th class="item_quantity wcfm_item_qty_heading sortable" data-sort="int" style="text-align:center;"><?php _e( 'Qty', 'wc-frontend-manager' ); ?></th>
						<th class="line_cost sortable" data-sort="float" style="text-align:center;"><?php _e( 'Total', 'wc-frontend-manager' ); ?></th>
						<?php
							if ( wc_tax_enabled() && ! empty( $order_taxes ) ) :
								foreach ( $order_taxes as $tax_id => $tax_item ) :
									$column_label   = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'wc-frontend-manager' );
									?>
									<th class="line_tax text_tip no_ipad no_mob" style="text-align:center;">
										<?php echo esc_attr( $column_label ); ?>
									</th>
									<?php
								endforeach;
							endif;
						?>
					</tr>
				</thead>
				<tbody id="order_line_items">
					<?php if( !empty( $product_items ) ) { ?>
						<?php foreach( $product_items as $item_id => $product_item ) { $order_item = new WC_Order_Item_Product( $item_id ); ?>
							<tr class="order_line_item_<?php echo $item_id; ?>">
								<td class="item sortable" data-sort="string-ins">
								  <?php 
								  echo $product_item['name']; 
								  do_action( 'woocommerce_order_item_meta_start', $item_id, $order_item, $order, false );
									wc_display_item_meta( $order_item );
									do_action( 'woocommerce_order_item_meta_end', $item_id, $order_item, $order, false );
								  ?>
								</td>
								
								<td class="item_cost sortable no_mob" data-sort="float" style="text-align:center;"><?php echo wc_price( $product_item['cost'], array( 'currency' => $currency ) ); ?></td>
								
								<td class="item_quantity wcfm_item_qty_heading sortable" data-sort="int" style="text-align:center;">
								  <?php echo $product_item['qty']; ?><br />
								  <select class="wcfm_popup_input wcfm_refund_input_qty wcfm_refund_input_ele" data-item="<?php echo $item_id; ?>" name="wcfm_refund_input[<?php echo $item_id; ?>][qty]">
								    <option value="">0</option>
								    <?php for( $h = 1; $h <= $product_item['qty']; $h++ ) { ?>
								    	<option value="<?php echo $h; ?>"><?php echo $h; ?></option>
								    <?php } ?>
								  </select>
								  <input type="hidden" value="<?php echo $item_id; ?>" name="wcfm_refund_input[<?php echo $item_id; ?>][item]">
								</td>
								
								<td class="line_cost sortable" data-sort="float" style="text-align:center;">
								  <?php echo wc_price( $product_item['total'], array( 'currency' => $currency ) ); ?><br />
								  <input type="number" class="wcfm_popup_input wcfm_refund_input_total wcfm_refund_input_ele" data-item_cost="<?php echo round( $product_item['total']/$product_item['qty'], 2 ); ?>" name="wcfm_refund_input[<?php echo $item_id; ?>][total]" min="0" step="1" max="<?php echo $product_item['total']; ?>" data-max_total="<?php echo $product_item['total']; ?>"  />
								</td>
								
								<?php
									if ( wc_tax_enabled() ) {
										$tax_data = $product_item['tax'];
										if ( ! empty( $tax_data ) ) {
											foreach ( $order_taxes as $tax_item ) {
												$tax_item_id       = $tax_item['rate_id'];
												$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : 0;
												if( !is_numeric( $tax_item_total ) ) $tax_item_total = 0;
												$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : 0;
												$refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id );
												if( !is_numeric( $refunded ) ) $refunded = 0;
												$tax_cost = ( $tax_item_total - $refunded );
												?>
												<td class="line_tax no_ipad no_mob" style="text-align:center;">
													<div class="view">
														<?php
															if ( '' != $tax_item_total ) {
																echo wc_price( wc_round_tax_total( $tax_cost ), array( 'currency' => $currency ) );
																?><br />
																<input type="number" class="wcfm_popup_input wcfm_refund_input_tax wcfm_refund_input_ele" data-item_tax="<?php echo round( $tax_cost/$product_item['qty'], 2 ); ?>" name="wcfm_refund_tax_input[<?php echo $item_id; ?>][<?php echo $tax_item_id; ?>]" min="0" step="1" max="<?php echo $tax_cost; ?>" data-max_tax="<?php echo $tax_cost; ?>"  />
																<?php
															} else {
																echo '&ndash;';
															}
														?>
													</div>
												</td>
												<?php
											}
										}
									}
								?>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
			
			<div class="wcfm_clearfix"></div>
			<p class="wcfm-refund-form-reason wcfm_popup_label">
				<label for="comment"><strong><?php _e( 'Refund Requests Reason', 'wc-multivendor-marketplace' ); ?> <span class="required">*</span></strong></label>
			</p>
			<textarea id="wcfm_refund_reason" name="wcfm_refund_reason" class="wcfm_popup_input wcfm_popup_textarea"></textarea>
			<div class="wcfm_clearfix"></div>
		
			<?php if ( function_exists( 'gglcptch_init' ) ) { ?>
			<div class="wcfm_clearfix"></div>
			<div class="wcfm_gglcptch_wrapper" style="float:right;">
				<?php echo apply_filters( 'gglcptch_display_recaptcha', '', 'wcfm_refund_request_form' ); ?>
			</div>
		<?php } elseif ( class_exists( 'anr_captcha_class' ) && function_exists( 'anr_captcha_form_field' ) ) { ?>
			<div class="wcfm_clearfix"></div>
			<div class="wcfm_gglcptch_wrapper" style="float:right;">
				<div class="anr_captcha_field"><div id="anr_captcha_field_9999"></div></div>
							
				<?php
					$site_key = trim( anr_get_option( 'site_key' ) );
					$theme    = anr_get_option( 'theme', 'light' );
					$size     = anr_get_option( 'size', 'normal' );
					$language = trim( anr_get_option( 'language' ) );
		
						$lang = '';
					if ( $language ) {
						$lang = "&hl=$language";
					}
		
				?>
				<script type="text/javascript">
					var wcfm_refund_anr_onloadCallback = function() {
						var anr_obj = {
						'sitekey' : '<?php echo esc_js( $site_key ); ?>',
						'size' : '<?php echo esc_js( $size ); ?>',
					};
					<?php
					if ( 'invisible' == $size ) {
						wp_enqueue_script( 'jquery' );
						?>
						anr_obj.badge = '<?php echo esc_js( anr_get_option( 'badge', 'bottomright' ) ); ?>';
					<?php } else { ?>
						anr_obj.theme = '<?php echo esc_js( $theme ); ?>';
					<?php } ?>
				
						var anr_captcha9999;
						
						<?php if ( 'invisible' == $size ) { ?>
							var anr_form9999 = jQuery('#anr_captcha_field_9999').closest('form')[0];
							anr_obj.callback = function(){ anr_form9999.submit(); };
							anr_obj["expired-callback"] = function(){ grecaptcha.reset(anr_captcha9999); };
							
							anr_form9999.onsubmit = function(evt){
								evt.preventDefault();
								//grecaptcha.reset(anr_captcha9999);
								grecaptcha.execute(anr_captcha9999);
							};
						<?php } ?>
						anr_captcha_9999 = grecaptcha.render('anr_captcha_field_9999', anr_obj );
					};
				</script>
				<script src="https://www.google.com/recaptcha/api.js?render=explicit<?php echo esc_js( $lang ); ?>"
					async defer>
				</script>
			</div>
		<?php } ?>
			<div class="wcfm_clearfix"></div>
			<div class="wcfm-message" tabindex="-1"></div>
			<div class="wcfm_clearfix"></div><br />
			
			<p class="form-submit">
				<input name="submit" type="submit" id="wcfm_refund_requests_submit_button" class="submit wcfm_popup_button" value="<?php _e( 'Submit', 'wc-multivendor-marketplace' ); ?>"> 
				<input type="hidden" name="wcfm_refund_order_id" value="<?php echo $order_id; ?>" id="wcfm_refund_order_id">
			</p>	
		<?php } else { ?>
			<div><?php _e( 'This order\'s item(s) are already requested for refund!', 'wc-multivendor-marketplace' ); ?></div>
		<?php } ?>
		<div class="wcfm-clearfix"></div>
	</form>
	<div class="wcfm-clearfix"></div>
</div>
<div class="wcfm-clearfix"></div>