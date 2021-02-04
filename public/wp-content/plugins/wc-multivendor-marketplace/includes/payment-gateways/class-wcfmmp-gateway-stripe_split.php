<?php

if (!defined('ABSPATH')) {
   return;
}

use Stripe\Stripe as Stripe;
use Stripe\Customer as Stripe_Customer;
use Stripe\Source as Stripe_Source;
use Stripe\Charge as Stripe_Charge;
use Stripe\Transfer as Stripe_Transfer;
use Stripe\Token as Stripe_Token;

class WCFMmp_Gateway_Stripe_Split extends WC_Payment_Gateway {

	public  $customer;
	private $charge;
	private $vendor_disconnected;
	
	public  $gateway_title;
	public  $payment_gateway;
	public  $message = array();
	
	private $client_id;
	private $client_secret;
	private $published_key;
	private $is_3d_secure = false;
	
	private $is_testmode = false;
	private $payout_mode = 'true';
	
	private $reciver_email;
	
	private $api_endpoint;
	private $token_endpoint;
	private $access_token;
	private $token_type;

	public function __construct() {
		global $WCFM, $WCFMmp;

		$this->id = 'stripe_split';
		$this->has_fields = false;
		$this->method_title = __('Marketplace Stripe Split Pay', 'wc-multivendor-marketplace');
		$this->vendor_disconnected = false;

		$withdrawal_test_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['test_mode'] ) ? 'yes' : 'no';
		
		$this->is_3d_secure   = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_3d_secure'] ) ? true : false;
		
		if( $this->is_3d_secure ) {
			$this->supports           = array(
																				'products',
																				'refunds',
																				'subscriptions',
																				//'subscription_date_changes'
																			);
		} else {
			$this->supports           = array(
																				'products',
																				'subscriptions',
																				//'subscription_date_changes'
																			);
		}
		
		$this->init_form_fields();
		$this->init_settings();
		
		
		$this->client_id = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_client_id'] : '';
		$this->published_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_published_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_published_key'] : '';
		$this->secret_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_secret_key'] : '';
		
		if ( $withdrawal_test_mode == 'yes') {
			$this->is_testmode = true;
			$this->client_id = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_test_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_client_id'] : '';
			$this->published_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_test_published_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_published_key'] : '';
			$this->secret_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_test_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_secret_key'] : '';
		}
		
		$this->title        = apply_filters( 'wcfmmp_stripe_split_pay_title', __('Credit or Debit Card (Stripe)', 'wc-multivendor-marketplace') );
		$this->description  = __('Pay with your credit or debit card via Stripe.', 'wc-multivendor-marketplace');
		$this->charge_type  = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_split_pay_mode'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_split_pay_mode'] : 'direct_charges';
		$this->debug        = false; //$this->is_testmode;
		
		if( !class_exists("Stripe\Stripe") ) {
			require_once( $WCFMmp->plugin_path . 'includes/Stripe/init.php' );
		}
		
		if( !class_exists("WCFM_Stripe_API") ) {
			require_once( $WCFMmp->plugin_path . 'includes/wcfm-stripe/class-wcfm-stripe-api.php' );
			
			if( $this->is_3d_secure ) {
				$this->charge_type  = 'transfers_charges';
				require_once( $WCFMmp->plugin_path . 'includes/wcfm-stripe/class-wcfm-stripe-helper.php' );
				require_once( $WCFMmp->plugin_path . 'includes/wcfm-stripe/class-wcfm-stripe-order-handler.php' );
			}
		}
		
		WCFM_Stripe_API::set_secret_key( $this->secret_key );
		
		// Init Access Token
		$this->init_stripe_access_token();
		
		// Register WC Payment gateway
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_stripe_split_pay_gateway' ) );
		
		// Process Refund
		add_action( 'wcfmmp_refund_status_completed', array( &$this, 'wcfmmp_stripe_split_process_refund' ), 50, 3 );
		
		// De-register WCFMmp Auto-withdrawal Gateway
		add_filter( 'wcfm_marketplace_disallow_active_order_payment_methods', array( $this, 'wcfmmp_auto_withdrawal_stipe_pay' ), 750 );

		add_action( 'wp_enqueue_scripts', array( &$this, 'wcfmmp_stripe_split_scripts' ) );
		
		if( $this->is_3d_secure ) {
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'prepare_order_pay_page' ) );
			add_action( 'woocommerce_account_view-order_endpoint', array( $this, 'check_intent_status_on_order_page' ), 1 );
			add_filter( 'woocommerce_payment_successful_result', array( $this, 'modify_successful_payment_result' ), 99999, 2 );
			add_action( 'set_logged_in_cookie', array( $this, 'set_cookie_on_current_request' ) );
			
			add_action( 'wc_ajax_wc_stripe_verify_intent', array( $this, 'verify_intent' ) );
		}
	}
	
	public function add_stripe_split_pay_gateway($methods) {
		$methods[] = 'WCFMmp_Gateway_Stripe_Split';
		return $methods;
	}
	
	public function wcfmmp_auto_withdrawal_stipe_pay( $auto_withdrawal_methods ) {
		if( isset( $auto_withdrawal_methods['stripe_split'] ) )
			unset( $auto_withdrawal_methods['stripe_split'] );
		return $auto_withdrawal_methods;
	}
	
	/**
	 * Init Stripe access token.
	 *
	 * @access public
	 */
	public function init_stripe_access_token() {
		if ($this->secret_key == "") {
			add_action( 'admin_notices', array( &$this, 'stripe_access_token_error') );
			//wcfm_stripe_log('Stripe secret_key is not set. Kindly set that from WCFM Dashboard => Settings => Withdrawal Setting');
		} else {
			// Stripe initialize
			Stripe::setApiKey($this->secret_key);
		}
	}

	public function get_icon() {
		global $WCFM, $WCFMmp;

		$imgaes_url = $WCFMmp->plugin_url . 'assets/images/';

		return apply_filters( 'wcfmmp_stripe_split_pay_icons', '<br /><img src="' . $imgaes_url . 'gateway/visa.svg" class="stripe-visa-icon stripe-icon" alt="Visa" />' .
						'<img src="' . $imgaes_url . 'gateway/amex.svg" class="stripe-amex-icon stripe-icon" alt="American Express" />' .
						'<img src="' . $imgaes_url . 'gateway/mastercard.svg" class="stripe-mastercard-icon stripe-icon" alt="Mastercard" />' .
						'<img src="' . $imgaes_url . 'gateway/discover.svg" class="stripe-discover-icon stripe-icon" alt="Discover" />' .
						'<img src="' . $imgaes_url . 'gateway/diners.svg" class="stripe-diners-icon stripe-icon" alt="Diners" />' .
						'<img src="' . $imgaes_url . 'gateway/jcb.svg" class="stripe-jcb-icon stripe-icon" alt="JCB" />'
		);
	}

	public function wcfmmp_stripe_split_scripts() {
		global $WCFM, $WCFMmp, $woocommerce, $wp;
		
		if ( ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
			return;
		}
		$vendors = array();
		
		// Generating Vendors List
		if ( is_checkout() ) {
			if ( isset( $_GET['pay_for_order'] ) && 'true' === $_GET['pay_for_order'] ) { // wpcs: csrf ok.
				$order_id = wc_get_order_id_by_order_key( urldecode( $_GET['key'] ) ); // wpcs: csrf ok, sanitization ok, xss ok.
				$order    = wc_get_order( $order_id );
				if ( is_a( $order, 'WC_Order' ) ) {
					$items = $order->get_items( 'line_item' );
					if( !empty( $items ) ) {
						foreach( $items as $item_id => $item ) {
							$order_item_id = $item->get_id();
							$line_item = new WC_Order_Item_Product( $item );
							$product_id = $line_item->get_product_id();
							if( $product_id ) {
								$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
								if ( $vendor_id && !in_array( $vendor_id, $vendors ) ) {
									$vendors[] = $vendor_id;
								}
							}
						}
					}
			
					$wcfmmp_stripe_split_pay_params['billing_first_name'] = $order->get_billing_first_name();
					$wcfmmp_stripe_split_pay_params['billing_last_name']  = $order->get_billing_last_name();
					$wcfmmp_stripe_split_pay_params['billing_address_1']  = $order->get_billing_address_1();
					$wcfmmp_stripe_split_pay_params['billing_address_2']  = $order->get_billing_address_2();
					$wcfmmp_stripe_split_pay_params['billing_state']      = $order->get_billing_state();
					$wcfmmp_stripe_split_pay_params['billing_city']       = $order->get_billing_city();
					$wcfmmp_stripe_split_pay_params['billing_postcode']   = $order->get_billing_postcode();
					$wcfmmp_stripe_split_pay_params['billing_country']    = $order->get_billing_country();
				}
			} else {
				$items = WC()->cart->get_cart();
				foreach ($items as $item) {
					if (isset($item['product_id'])) {
						$vendor_id = wcfm_get_vendor_id_by_post( $item['product_id'] );
						if ( $vendor_id && !in_array( $vendor_id, $vendors ) ) {
							$vendors[] = $vendor_id;
						}
					}
				}
			}
		}
		
		$script_path = $WCFMmp->plugin_url . 'assets/';
		$script_path = str_replace(array('http:', 'https:'), '', $script_path);

		wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', '', '3.0', true );
		wp_enqueue_script( 'wcfmmp_stripe_split_pay', $script_path . 'js/gateway/stripe.js', array('jquery-payment', 'stripe'), $WCFMmp->version, true );

		$wcfmmp_stripe_split_pay_params['key']                       = $this->published_key;
		$wcfmmp_stripe_split_pay_params['elements_options']          = apply_filters( 'wcfmmp_stripe_split_pay_elements_options', array());
		$wcfmmp_stripe_split_pay_params['is_checkout']               = ( is_checkout() && empty($_GET['pay_for_order']) ) ? 'yes' : 'no';
		$wcfmmp_stripe_split_pay_params['is_pay_for_order_page']     = is_wc_endpoint_url( 'order-pay' ) ? 'yes' : 'no';
		$wcfmmp_stripe_split_pay_params['ajaxurl']                   = WC_AJAX::get_endpoint('%%endpoint%%');
		$wcfmmp_stripe_split_pay_params['stripe_nonce']              = wp_create_nonce('_wcfmmp_stripe_split_pay_nonce');
		$wcfmmp_stripe_split_pay_params['no_of_vendor']              = !empty( $vendors ) ? count($vendors) : 1;
		$wcfmmp_stripe_split_pay_params['is_3d_secure']              = $this->is_3d_secure;

		wp_localize_script( 'wcfmmp_stripe_split_pay', 'wcfmmp_stripe_split_pay_params', apply_filters( 'wcfmmp_stripe_split_pay_params', $wcfmmp_stripe_split_pay_params ) );

		wp_enqueue_style( 'wcfmmp_stripe_split_pay_css', $script_path . 'css/gateway/stripe.css', array(), $WCFMmp->version, 'all' );
	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
		$description = $this->description;
		if ($description) {
			if ($this->is_testmode) {
				$test_card = '4242424242424242';
				if( $this->is_3d_secure ) {
					$test_card = '4000000000003220';
				}
				/* translators: link to Stripe testing page */
				$description .= ' ' . sprintf(__('TEST MODE ENABLED. In test mode, you can use the card number %s with any CVC and a valid expiration date or check the <a href="%s" target="_blank">Testing Stripe documentation</a> for more card numbers.', 'wc-multivendor-marketplace'), $test_card, 'https://stripe.com/docs/testing');
				$description = trim($description);
			}

			echo apply_filters( 'wcfmmp_stripe_split_pay_description', wpautop(wp_kses_post($description)), $this->id);
		}
		ob_start();
		?>
		<fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form" class="wcfmmp-credit-card-form wc-payment-form" style="background:transparent;">
		  <?php do_action('wcfmmp_stripe_split_pay_credit_card_form_start', $this->id); ?>

			<?php if ( is_user_logged_in() && ( $credit_cards = get_user_meta( get_current_user_id(), 'wcfmmp_stripe_customer_saved_cards', true ) ) ) : ?>
			  <!---
				<p class="form-row form-row-wide">

					<?php foreach ( $credit_cards as $i => $credit_card ) : if ( empty($credit_card['last4']) ) continue; ?>
							<input type="radio" id="wcfm_stripe_card_<?php echo $i; ?>" name="wcfmmp_stripe_customer_id" style="width:auto;" value="<?php echo $i; ?>" data-last4="<?php echo $credit_card['last4']; ?>" data-exp_month="<?php echo $credit_card['exp_month']; ?>" data-exp_year="<?php echo $credit_card['exp_year']; ?>" />
							<label style="display:inline;" for="stripe_customer_<?php echo $i; ?>"><?php _e( 'Card ending with', 'wc-multivendor-marketplace' ); ?> <?php echo $credit_card['last4']; ?> (<?php echo $credit_card['exp_month'] . '/' . $credit_card['exp_year'] ?>)</label><br />
					<?php endforeach; ?>

					<input type="radio" id="new" name="wcfmmp_stripe_customer_id" style="width:auto;" <?php checked( 1, 1 ) ?> value="new" /> <label style="display:inline;" for="new"><?php _e( 'Use a new credit card', 'wc-multivendor-marketplace' ); ?></label>

				</p>
				<div class="clear"></div>
				-->
			<?php endif; ?>

			<div class="wcfmmp_stripe_new_card">
				<div class="form-row form-row-wide">
					<label for="wcfmmp-stripe-split-pay-card-element"><?php esc_html_e('Card Number', 'wc-multivendor-marketplace'); ?> <span class="required">*</span></label>
					<div class="wcfmmp-stripe-split-pay-card-group">
						<div id="wcfmmp-stripe-split-pay-card-element" class="wc-wcfmmp-stripe-split-pay-elements-field">
								<!-- a Stripe Element will be inserted here. -->
						</div>
						<i class="stripe-credit-card-brand stripe-card-brand" alt="Credit Card"></i>
					</div>
				</div>

				<div class="form-row form-row-first">
					<label for="wcfmmp-stripe-split-pay-exp-element"><?php esc_html_e('Expiry Date', 'wc-multivendor-marketplace'); ?> <span class="required">*</span></label>
	
					<div id="wcfmmp-stripe-split-pay-exp-element" class="wc-wcfmmp-stripe-split-pay-elements-field">
							<!-- a Stripe Element will be inserted here. -->
					</div>
				</div>

				<div class="form-row form-row-last">
					<label for="wcfmmp-stripe-split-pay-cvc-element"><?php esc_html_e('Card Code (CVC)', 'wc-multivendor-marketplace'); ?> <span class="required">*</span></label>
					<div id="wcfmmp-stripe-split-pay-cvc-element" class="wc-wcfmmp-stripe-split-pay-elements-field">
							<!-- a Stripe Element will be inserted here. -->
					</div>
				</div>
			</div>
			<div class="clear"></div>

			<!-- Used to display form errors -->
			<div class="wcfmmp-stripe-split-pay-source-errors" role="alert"></div>
			
			<?php do_action('wcfmmp_stripe_split_pay_credit_card_form_end', $this->id); ?>
			
			<div class="wcfm-clearfix"></div>
		</fieldset>
		<?php
		ob_end_flush();
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id
	 *
	 * @return array
	 */
	public function process_payment($order_id) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$order = wc_get_order($order_id);
		$error_message = __('An error has occurred while processing your payment, please try again. Or contact us for assistance.', 'wc-multivendor-marketplace');
		
		if( !is_a( $order, 'WC_Order' ) ) return;

		//$WCFMmp->wcfmmp_commission->wcfmmp_checkout_order_processed( $order_id );
		
		$user = wp_get_current_user();
		$this->prepare_customer_obj( $user, $_POST, $order );
		
		// Update userdata
		if ( is_a( $user, 'WP_User' ) && isset($this->customer->id)) {
			update_user_meta( get_current_user_id(), 'wcfmmp_stripe_split_pay_customer_id', $this->customer->id );
		}
		
		
		if( $this->is_3d_secure ) {
			if ( ! empty( $_POST['stripe_source'] ) ) {
				$source_object = WCFM_Stripe_API::retrieve( 'sources/' . wc_clean( $_POST['stripe_source'] ) );
				$source_id     = $source_object->id;
				
				// Prepare Source data
				$prepared_source = (object) array(
																					'token_id'      => false,
																					'customer'      => $this->customer->id,
																					'source'        => $source_id,
																					'source_object' => $source_object,
																				);
				
				// save Source to Order
				$order->update_meta_data( '_wcfmmp_stripe_split_pay_source_id', $source_id );
				$order->save();
				
				// Create/Update Order Intent
				$intent = $this->get_intent_from_order( $order );
				if ( $intent ) {
					$intent = $this->update_existing_intent( $intent, $order, $prepared_source );
				} else {
					$intent = $this->create_intent( $order, $prepared_source );
				}
				
				wcfm_stripe_log( json_encode( (array) $intent ) );
	
				// Confirm the intent after locking the order to make sure webhooks will not interfere.
				if ( empty( $intent->error ) ) {
					$this->lock_order_payment( $order, $intent );
					$intent = $this->confirm_intent( $intent, $order, $prepared_source );
				}
				
				if ( ! empty( $intent->error ) ) {
					$this->unlock_order_payment( $order );
					
					wcfm_stripe_log( "Stripe Split Pay Error: " . $intent->error->message);
					wc_add_notice(__("Stripe Split Pay Error: ", 'wc-multivendor-marketplace') .$intent->error->message, 'error');
					
					return array(
						'result' => 'fail',
					);
				}
				
				if ( ! empty( $intent ) ) {
					// Use the last charge within the intent to proceed.
					$response = end( $intent->charges->data );
	
					// If the intent requires a 3DS flow, redirect to it.
					if ( 'requires_action' === $intent->status ) {
						$this->unlock_order_payment( $order );
	
						if ( is_wc_endpoint_url( 'order-pay' ) ) {
							$redirect_url = add_query_arg( 'wcfm-stripe-confirmation', 1, $order->get_checkout_payment_url( false ) );
	
							return array(
								'result'   => 'success',
								'redirect' => $redirect_url,
							);
						} else {
							/**
							 * This URL contains only a hash, which will be sent to `checkout.js` where it will be set like this:
							 * `window.location = result.redirect`
							 * Once this redirect is sent to JS, the `onHashChange` function will execute `handleCardPayment`.
							 */
	
							return array(
								'result'        => 'success',
								'redirect'      => $this->get_return_url( $order ),
								'intent_secret' => $intent->client_secret,
							);
						}
					}
				}
				
				// Process valid response.
				$this->process_response( $response, $order );
				
				// Remove cart.
				WC()->cart->empty_cart();
	
				// Unlock the order.
				$this->unlock_order_payment( $order );
	
				// Return thank you page redirect.
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}
		}
		
		
		$wcfmmp_stripe_split_pay_list = $WCFMmp->wcfmmp_commission->wcfmmp_split_pay_vendor_list( $order, $_POST, 'stripe' );
		
		wcfm_stripe_log( json_encode($wcfmmp_stripe_split_pay_list));
		
		$vendor_gross_sales = 0;
		$total_gross_sales  = $wcfmmp_stripe_split_pay_list['total_amount'];
		
		$card_charged = false;
		$all_success = array();
		
		$order->update_meta_data('wcfmmp_stripe_split_pay_charge_type', $this->charge_type);

		
		switch ($this->charge_type) {
			case 'direct_charges':
				
				if(isset($wcfmmp_stripe_split_pay_list['distribution_list']) && is_array($wcfmmp_stripe_split_pay_list['distribution_list']) && count($wcfmmp_stripe_split_pay_list['distribution_list']) > 0) {
					$i = 0;
					foreach($wcfmmp_stripe_split_pay_list['distribution_list'] as $vendor_id => $distribution_info) {
						
						if( !isset( $wcfmmp_stripe_split_pay_list['stripe_token'][$i] ) ) continue;
						
						$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($vendor_id) );
						
						// Fetching Total Commission from Vendor Order Newly 
						$re_total_commission = $wpdb->get_var("SELECT SUM(total_commission) as total_commission FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
						
						// Directly Charge the Customer and collect the application_fee
						try {
							$charge_data = array(
												"amount"          => $this->get_stripe_amount($distribution_info['gross_sales']),
												"currency"        => $wcfmmp_stripe_split_pay_list['currency'],
												"source"          => $wcfmmp_stripe_split_pay_list['stripe_token'][$i],
												"application_fee" => $this->get_stripe_amount($distribution_info['gross_sales'] - $re_total_commission),
												"description"     => $wcfmmp_stripe_split_pay_list['description'],
											);
							$vendor_stripe_account = array(
																							"stripe_account" => $distribution_info['destination']
																						 );
							$charge_data = apply_filters('wcfmmp_stripe_split_pay_create_direct_charges', $charge_data);
							$i++;
							
							if ($this->debug)
								wcfm_stripe_log("Stripe Charge Data before Processing: " . serialize($charge_data));
							
							$this->charge = Stripe_Charge::create($charge_data, $vendor_stripe_account);
							
							if ($this->debug)
								wcfm_stripe_log("Stripe Charge Data after Processing: " . serialize($this->charge));
					
							if (isset($this->charge['failure_message']) && empty($this->charge['failure_message'])) {
								if (isset($this->charge->id)) {
									$order->update_meta_data('wcfmmp_stripe_split_pay_charge_id_'.$vendor_id, $this->charge->id);
									$order->update_meta_data('wcfmmp_stripe_split_pay_charge_type_'.$vendor_id, $this->charge_type);
									$order->payment_complete();
																																							
									// Create vendor withdrawal Instance
									$commission_id_list = $wpdb->get_col("SELECT ID FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
									
									$withdrawal_id = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_processed( $vendor_id, $order_id, implode( ',', $commission_id_list ), 'stripe_split', $distribution_info['gross_sales'], $re_total_commission, 0, 'pending', 'by_split_pay', 0 );
									
									// Wwithdrawal Processing
									$WCFMmp->wcfmmp_withdraw->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, 'completed', __( 'Stripe Split Pay', 'wc-multivendor-marketplace' ) );
									
									// Withdrawal Meta
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'withdraw_amount', $re_total_commission );
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'currency', $order->get_currency() );
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_id', $this->charge->id );
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_type', $this->charge_type );
									
									do_action( 'wcfmmp_withdrawal_request_approved', $withdrawal_id );
									
									wcfm_stripe_log( sprintf( '#%s - %s payment processing complete via %s for order %s. Amount: %s', sprintf( '%06u', $withdrawal_id ), $store_name, 'Stripe Split Pay', $order_id, $re_total_commission . ' ' . $order->get_currency() ), 'info' );
								}
								$card_charged = true;
								$all_success[$vendor_id] = "true";
								if( isset( $distribution_info['wcfmmvp_gross_sales'] ) ) {
									$vendor_gross_sales += $distribution_info['wcfmmvp_gross_sales'];
								} else {
									$vendor_gross_sales += $distribution_info['gross_sales'];
								}
							} else {
								wcfm_stripe_log( $store_name . " Stripe Charge Error: " . $this->charge['failure_message']);
								wc_add_notice(__("Stripe Charge Error: ", 'wc-multivendor-marketplace') . $this->charge['failure_message'], 'error');
								$all_success[$vendor_id] = "false";
								return false;
							}
						} catch (Exception $ex) {
							wcfm_stripe_log( $store_name . " Stripe Split Pay Error: " . $ex->getMessage());
							wc_add_notice(__("Stripe Split Pay Error: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error');
							$this->delete_vendor_commission($order_id);
							//$stripe_cust = Stripe_Customer::retrieve($this->customer->id);
							//$stripe_cust->delete();
							$all_success[$vendor_id] = "false";
							return array(
								'result' => 'fail',
							);
						}
					}
				}
				
				// Remaining Amount Pay to Admin
				$remaining_sales_amount = $total_gross_sales - $vendor_gross_sales;
				if( $remaining_sales_amount && ( absint($remaining_sales_amount) >= 1 ) ) {
					try {
						$charge_data = array(
																"amount"         => $this->get_stripe_amount( $remaining_sales_amount ),
																"currency"       => $wcfmmp_stripe_split_pay_list['currency'],
																"source"         => $wcfmmp_stripe_split_pay_list['stripe_source'],
																"customer"       => $this->customer->id,
																"description"    => sprintf( __( 'Payment for Order #%s', 'wc-multivendor-marketplace' ), $order_id ),
																);
						$this->charge = Stripe_Charge::create($charge_data);
						
						if (isset($this->charge['failure_message']) && empty($this->charge['failure_message'])) {
							if (isset($this->charge->id)) {
								$order->update_meta_data('wcfmmp_stripe_split_pay_charge_id_admin', $this->charge->id);
								$order->update_meta_data('wcfmmp_stripe_split_pay_charge_type_admin', $this->charge_type);
							}
							wcfm_stripe_log( "Stripe Remaining Amount Paid for Order #" . $order_id . " => " . $remaining_sales_amount );
						} else {
							wcfm_stripe_log( "Stripe Remaining Pay Error: " . $this->charge['failure_message'] );
							$all_success[0] = "false";
							return false;
						}
					} catch (Exception $ex) {
						wcfm_stripe_log( "Stripe Split Pay Remaining Pay Error: " . $ex->getMessage() );
						$all_success[0] = "false";
					}
				}
			break;
			
			case 'destination_charges':
				if(isset($wcfmmp_stripe_split_pay_list['distribution_list']) && is_array($wcfmmp_stripe_split_pay_list['distribution_list']) && count($wcfmmp_stripe_split_pay_list['distribution_list']) > 0) {
					$i = 0;
					foreach($wcfmmp_stripe_split_pay_list['distribution_list'] as $vendor_id => $distribution_info ) {
						
						if( !isset( $wcfmmp_stripe_split_pay_list['stripe_token'][$i] ) ) continue;
						
						$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($vendor_id) );
						
						// Fetching Total Commission from Vendor Order Newly 
						$re_total_commission = $wpdb->get_var("SELECT SUM(total_commission) as total_commission FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
						
						// Directly Charge the Customer and collect the application_fee
						try {
							$charge_data = array(
												"amount"       => $this->get_stripe_amount($distribution_info['gross_sales']),
												"currency"     => $wcfmmp_stripe_split_pay_list['currency'],
												"source"       => $wcfmmp_stripe_split_pay_list['stripe_token'][$i],
												"destination"  => array(
													"amount"     => $this->get_stripe_amount($re_total_commission),
													"account"    => $distribution_info['destination'],
												),
												"description"    => $wcfmmp_stripe_split_pay_list['description'],
											);
							$charge_data = apply_filters('wcfmmp_stripe_split_pay_destination_charges', $charge_data);
							$i++;
							
							if ($this->debug)
								wcfm_stripe_log("Stripe Charge Data before Processing: " . serialize($charge_data));
							
							$this->charge = Stripe_Charge::create($charge_data);
							
							if ($this->debug)
								wcfm_stripe_log("Stripe Charge Data after Processing: " . serialize($this->charge));
							
							if (isset($this->charge['failure_message']) && empty($this->charge['failure_message'])) {
								if (isset($this->charge->id)) {
									$order->update_meta_data('wcfmmp_stripe_split_pay_charge_id_'.$vendor_id, $this->charge->id);
									$order->update_meta_data('wcfmmp_stripe_split_pay_charge_type_'.$vendor_id, $this->charge_type);
									$order->payment_complete();
																																							
									// Create vendor withdrawal Instance
									$commission_id_list = $wpdb->get_col("SELECT ID FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
									
									$withdrawal_id = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_processed( $vendor_id, $order_id, implode( ',', $commission_id_list ), 'stripe_split', $distribution_info['gross_sales'], $re_total_commission, 0, 'pending', 'by_split_pay', 0 );
									
									// Withdrawal Processing
									$WCFMmp->wcfmmp_withdraw->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, 'completed', __( 'Stripe Split Pay', 'wc-multivendor-marketplace' ) );
									
									// Withdrawal Meta
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'withdraw_amount', $re_total_commission );
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'currency', $order->get_currency() );
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_id', $this->charge->id );
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_type', $this->charge_type );
									
									do_action( 'wcfmmp_withdrawal_request_approved', $withdrawal_id );
									
									wcfm_stripe_log( sprintf( '#%s - %s payment processing complete via %s for order %s. Amount: %s', sprintf( '%06u', $withdrawal_id ), $store_name, 'Stripe Split Pay', $order_id, $re_total_commission . ' ' . $order->get_currency() ), 'info' );
								}
								$card_charged = true;
								$all_success[$vendor_id] = "true";
								if( isset( $distribution_info['wcfmmvp_gross_sales'] ) ) {
									$vendor_gross_sales += $distribution_info['wcfmmvp_gross_sales'];
								} else {
									$vendor_gross_sales += $distribution_info['gross_sales'];
								}
							} else {
								wcfm_stripe_log( $store_name . " Stripe Charge Error: " . $this->charge['failure_message']);
								wc_add_notice(__("Stripe Charge Error: ", 'wc-multivendor-marketplace') . $this->charge['failure_message'], 'error');
								$all_success[$vendor_id] = "false";
								return false;
							}
						} catch (Exception $ex) {
							wcfm_stripe_log( $store_name . " Stripe Split Pay Error: " . $ex->getMessage());
							wc_add_notice(__("Stripe Split Pay Error: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error');
							$this->delete_vendor_commission($order_id);
							//$stripe_cust = Stripe_Customer::retrieve($this->customer->id);
							//$stripe_cust->delete();
							$all_success[$vendor_id] = "false";
							return array(
								'result' => 'fail',
							);
						}
					}
				}
				
				// Remaining Amount Pay to Admin
				$remaining_sales_amount = $total_gross_sales - $vendor_gross_sales;
				if( $remaining_sales_amount && ( absint($remaining_sales_amount) >= 1 ) ) {
					try {
						$charge_data = array(
																"amount"         =>  $this->get_stripe_amount( $remaining_sales_amount ),
																"currency"       => $wcfmmp_stripe_split_pay_list['currency'],
																"source"         => $wcfmmp_stripe_split_pay_list['stripe_source'],
																"customer"       => $this->customer->id,
																"description"    => sprintf( __( 'Payment for Order #%s', 'wc-multivendor-marketplace' ), $order_id ),
																);
						$this->charge = Stripe_Charge::create($charge_data);
						
						if (isset($this->charge['failure_message']) && empty($this->charge['failure_message'])) {
							if (isset($this->charge->id)) {
								$order->update_meta_data('wcfmmp_stripe_split_pay_charge_id_admin', $this->charge->id);
								$order->update_meta_data('wcfmmp_stripe_split_pay_charge_type_admin', $this->charge_type);
							}
							wcfm_stripe_log( "Stripe Remaining Amount Paid for Order #" . $order_id . " => " . $remaining_sales_amount );
						} else {
							wcfm_stripe_log( "Stripe Remaining Pay Error: " . $this->charge['failure_message'] );
							$all_success[0] = "false";
							return false;
						}
					} catch (Exception $ex) {
						wcfm_stripe_log( "Stripe Split Pay Remaining Pay Error: " . $ex->getMessage() );
						$all_success[0] = "false";
					}
				}
			break;
		
			case 'transfers_charges':
				try {
					$charge_data = array(
															"amount"         =>  $this->get_stripe_amount( $wcfmmp_stripe_split_pay_list['total_amount'] ),
															"currency"       => $wcfmmp_stripe_split_pay_list['currency'],
															"source"         => $wcfmmp_stripe_split_pay_list['stripe_source'],
															"customer"       => $this->customer->id,
															"transfer_group" => $wcfmmp_stripe_split_pay_list['transfer_group'],
															"description"    => $wcfmmp_stripe_split_pay_list['description'],
															);
					$charge_data = apply_filters( 'wcfmmp_stripe_split_pay_create_destination_charges', $charge_data, $_POST );
					
					if ($this->debug)
						wcfm_stripe_log("Stripe Charge Data before Processing: " . serialize($charge_data));
					
					$this->charge = Stripe_Charge::create($charge_data);
					
					if ($this->debug)
						wcfm_stripe_log("Stripe Charge Data after Processing: " . serialize($this->charge));
					
					if (isset($this->charge['failure_message']) && empty($this->charge['failure_message'])) {
						if (isset($this->charge->id)) {
							$order->update_meta_data( 'wcfmmp_stripe_split_pay_charge_id_admin', $this->charge->id );
							$order->update_meta_data( 'wcfmmp_stripe_split_pay_charge_type_admin', $this->charge_type );
							$order->payment_complete();
						}
						$card_charged = true;
					} else {
						wcfm_stripe_log( "Stripe Charge Error: " . $this->charge['failure_message'] );
						wc_add_notice( __("Stripe Charge Error: ", 'wc-multivendor-marketplace') . $this->charge['failure_message'], 'error' );
						$all_success[0] = "false";
						return false;
					}
				} catch (Exception $ex) {
					wcfm_stripe_log( "Stripe Split Pay Error: " . $ex->getMessage() );
					wc_add_notice( __("Stripe Split Pay Error: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error' );
					$this->delete_vendor_commission($order_id);
					//$stripe_cust = Stripe_Customer::retrieve( $this->customer->id );
					//$stripe_cust->delete();
					$all_success[0] = "false";
					return array(
						'result' => 'fail',
					);
				}
					
				
				if($card_charged && isset( $wcfmmp_stripe_split_pay_list['distribution_list'] ) && is_array( $wcfmmp_stripe_split_pay_list['distribution_list'] ) && count( $wcfmmp_stripe_split_pay_list['distribution_list'] ) > 0 ) {
					foreach( $wcfmmp_stripe_split_pay_list['distribution_list'] as $vendor_id => $distribution_info ) {
						$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($vendor_id) );
						
						// Dristribute among vendors
						$source_transaction = apply_filters( 'wcfmmp_stripe_split_pay_source_transaction_enabled', true, $vendor_id );
						try {
							$transfer_data = array(
																			"destination" => $distribution_info['destination'],
																			"description" => $wcfmmp_stripe_split_pay_list['transfer_group'],
																			);
							if($source_transaction) $transfer_data['source_transaction'] = $this->charge->id;
							$transfer_data = apply_filters('wcfmmp_stripe_split_pay_create_transfer', $transfer_data, $_POST);
							
							if ($this->debug)
								wcfm_stripe_log("Before creating transfer with Stripe. Stripe Transfer Data: " . serialize($transfer_data));
							
							$commission_id_list = $wpdb->get_col("SELECT ID FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
							
							// Fetching Total Commission from Vendor Order Newly 
							$re_total_commission = $wpdb->get_var("SELECT SUM(total_commission) as total_commission FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
							
							// Creating Withdrawal Instance
							$withdrawal_id = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_processed( $vendor_id, $order_id, implode( ',', $commission_id_list ), 'stripe_split', $distribution_info['gross_sales'], $re_total_commission, 0, 'pending', 'by_split_pay', 0 );
							
							// Processing 
							$transfer_response = $WCFMmp->wcfmmp_gateways->payment_gateways['stripe']->process_payment( $withdrawal_id, $vendor_id, $re_total_commission, 0, 'auto', $transfer_data );
							
							// Update withdrawal status
							if ($transfer_response) {
								if( isset( $transfer_response['status'] ) && $transfer_response['status'] ) {
									$all_success[$vendor_id] = "true";
									
									if( isset( $transfer_response['transfer_data'] ) ) {
										$transfer_data = $transfer_response['transfer_data']->jsonSerialize();
										$order->update_meta_data( 'wcfmmp_stripe_split_pay_transaction_id_'.$vendor_id, $transfer_data['id'] );
									}
									
									// Withdrawal Processing
									$WCFMmp->wcfmmp_withdraw->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, 'completed', __( 'Stripe Split Pay', 'wc-multivendor-marketplace' ) );
									
									// Withdrawal Meta
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_type', $this->charge_type );
									
									do_action( 'wcfmmp_withdrawal_request_approved', $withdrawal_id );
									
									wcfm_stripe_log( sprintf( '#%s - %s payment processing complete via %s for order %s. Amount: %s', sprintf( '%06u', $withdrawal_id ), $store_name, 'Stripe Split Pay', $order_id, $re_total_commission . ' ' . $order->get_currency() ), 'info' );
								} else {
									$all_success[$vendor_id] = "false";
									foreach ($transfer_response as $message) {
										wcfm_stripe_log( sprintf( '#%s - %s payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), $store_name, $message['message'] ), 'error' );
									}
								}
							} else {
								wcfm_stripe_log( sprintf( '#%s - %s payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), $store_name, __('Something went wrong please try again later.', 'wc-multivendor-marketplace') ), 'error' );
							}
							
							
							if ($this->debug)
								wcfm_stripe_log("After creating transfer with Stripe. Stripe Transfer Response: " . serialize($transfer_response));
						
						} catch (Exception $ex) {
							wcfm_stripe_log( $store_name . " Error creating transfer record with Stripe: " . $ex->getMessage());
							wc_add_notice(__("Error creating transfer record with Stripe: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error');
							$all_success[$vendor_id] = "false";
							return array(
								'result' => 'fail',
							);
						}
					}
				}
			break;
			default:
		}
		
		// Update Customer Store
		$this->update_customer_obj( $user, $_POST );

		//if ((is_array($all_success) && in_array( "false", $all_success )) || $this->vendor_disconnected) {
		if( is_array( $all_success ) && in_array( "false", $all_success ) ) {
			$order->update_status( apply_filters( 'wcfmmp_stripe_split_pay_failed_order_status', 'failed', $order ) );
			
			wc_add_notice( __("Stripe Payment Error", 'wc-multivendor-marketplace'), 'error' );
			
			return array(
				'result' => 'fail',
				'redirect' => $this->get_return_url($order)
			);
		} else {
			$order->update_status( apply_filters( 'wcfmmp_stripe_split_pay_completed_order_status', 'processing', $order ) );
			
			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			);
		}
		
		// Set Cart Empty
		// WC()->cart->empty_cart();
	}
	
	/**
	 * Store extra meta data for an order from a Stripe Response.
	 */
	public function process_response( $response, $order ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		wcfm_stripe_log( 'Processing response: ' . json_encode( (array) $response ) );

		$order_id = $order->get_id();
		$captured = ( isset( $response->captured ) && $response->captured ) ? 'yes' : 'no';

		// Store charge data.
		$order->update_meta_data( '_stripe_charge_captured', $captured );

		if ( isset( $response->balance_transaction ) ) {
			$this->update_fees( $order, is_string( $response->balance_transaction ) ? $response->balance_transaction : $response->balance_transaction->id );
		}

		if ( 'yes' === $captured ) {
			/**
			 * Charge can be captured but in a pending state. Payment methods
			 * that are asynchronous may take couple days to clear. Webhook will
			 * take care of the status changes.
			 */
			if ( 'pending' === $response->status ) {
				$order_stock_reduced = $order->get_meta( '_order_stock_reduced', true );

				if ( ! $order_stock_reduced ) {
					wc_reduce_stock_levels( $order_id );
				}

				$order->set_transaction_id( $response->id );
				/* translators: transaction id */
				$order->update_status( 'on-hold', sprintf( __( 'Stripe charge awaiting payment: %s.', 'wc-multivendor-marketplace' ), $response->id ) );
			}

			if ( 'succeeded' === $response->status ) {
				
				// Process Connected Vendor's Commissions
				$wcfmmp_stripe_split_pay_list = $WCFMmp->wcfmmp_commission->wcfmmp_split_pay_vendor_list( $order, array(), 'stripe' );
				
				if( isset( $wcfmmp_stripe_split_pay_list['distribution_list'] ) && is_array( $wcfmmp_stripe_split_pay_list['distribution_list'] ) && count( $wcfmmp_stripe_split_pay_list['distribution_list'] ) > 0 ) {
					foreach( $wcfmmp_stripe_split_pay_list['distribution_list'] as $vendor_id => $distribution_info ) {
						$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($vendor_id) );
						
						// Dristribute among vendors
						$source_transaction = apply_filters( 'wcfmmp_stripe_split_pay_source_transaction_enabled', true, $vendor_id );
						try {
							$transfer_data = array(
																			"destination" => $distribution_info['destination'],
																			"description" => $wcfmmp_stripe_split_pay_list['transfer_group'],
																			);
							if($source_transaction) $transfer_data['source_transaction'] = $response->id;
							$transfer_data = apply_filters('wcfmmp_stripe_split_pay_create_transfer', $transfer_data, $_POST);
							
							if ($this->debug)
								wcfm_stripe_log("Before creating transfer with Stripe. Stripe Transfer Data: " . serialize($transfer_data));
							
							$commission_id_list = $wpdb->get_col("SELECT ID FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
							
							// Fetching Total Commission from Vendor Order Newly 
							$re_total_commission = $wpdb->get_var("SELECT SUM(total_commission) as total_commission FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
							
							// Creating Withdrawal Instance
							$withdrawal_id = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_processed( $vendor_id, $order_id, implode( ',', $commission_id_list ), 'stripe_split', $distribution_info['gross_sales'], $re_total_commission, 0, 'pending', 'by_split_pay', 0 );
							
							// Processing 
							$transfer_response = $WCFMmp->wcfmmp_gateways->payment_gateways['stripe']->process_payment( $withdrawal_id, $vendor_id, $re_total_commission, 0, 'auto', $transfer_data );
							
							// Update withdrawal status
							if ($transfer_response) {
								if( isset( $transfer_response['status'] ) && $transfer_response['status'] ) {
									$all_success[$vendor_id] = "true";
									
									if( isset( $transfer_response['transfer_data'] ) ) {
										$transfer_data = $transfer_response['transfer_data']->jsonSerialize();
										$order->update_meta_data( 'wcfmmp_stripe_split_pay_transaction_id_'.$vendor_id, $transfer_data['id'] );
									}
									
									// Withdrawal Processing
									$WCFMmp->wcfmmp_withdraw->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, 'completed', __( 'Stripe Split Pay', 'wc-multivendor-marketplace' ) );
									
									// Withdrawal Meta
									$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_type', 'transfers_charges' );
									
									do_action( 'wcfmmp_withdrawal_request_approved', $withdrawal_id );
									
									wcfm_stripe_log( sprintf( '#%s - %s payment processing complete via %s for order %s. Amount: %s', sprintf( '%06u', $withdrawal_id ), $store_name, 'Stripe Split Pay', $order_id, $re_total_commission . ' ' . $order->get_currency() ), 'info' );
								} else {
									$all_success[$vendor_id] = "false";
									foreach ($transfer_response as $message) {
										wcfm_stripe_log( sprintf( '#%s - %s payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), $store_name, $message['message'] ), 'error' );
									}
								}
							} else {
								wcfm_stripe_log( sprintf( '#%s - %s payment processing failed: %s', sprintf( '%06u', $withdrawal_id ), $store_name, __('Something went wrong please try again later.', 'wc-multivendor-marketplace') ), 'error' );
							}
							
							
							if ($this->debug)
								wcfm_stripe_log("After creating transfer with Stripe. Stripe Transfer Response: " . serialize($transfer_response));
						
						} catch (Exception $ex) {
							wcfm_stripe_log( $store_name . " Error creating transfer record with Stripe: " . $ex->getMessage());
							wc_add_notice(__("Error creating transfer record with Stripe: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error');
							$all_success[$vendor_id] = "false";
						}
					}
				}
				
				$order->update_meta_data( 'wcfmmp_stripe_split_pay_charge_type', 'transfers_charges' );
				
				
				$order->payment_complete( $response->id );

				/* translators: transaction id */
				$message = sprintf( __( 'Stripe charge complete (Charge ID: %s)', 'wc-multivendor-marketplace' ), $response->id );
				$order->add_order_note( $message );
			}

			if ( 'failed' === $response->status ) {
				$localized_message = __( 'Payment processing failed. Please retry.', 'wc-multivendor-marketplace' );
				$order->add_order_note( $localized_message );
				throw new Exception( print_r( $response, true ), $localized_message );
			}
		} else {
			$order->set_transaction_id( $response->id );

			if ( $order->has_status( array( 'pending', 'failed' ) ) ) {
				wc_reduce_stock_levels( $order_id );
			}

			/* translators: transaction id */
			$order->update_status( 'on-hold', sprintf( __( 'Stripe charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', 'wc-multivendor-marketplace' ), $response->id ) );
		}

		if ( is_callable( array( $order, 'save' ) ) ) {
			$order->save();
		}

		do_action( 'wc_gateway_stripe_process_response', $response, $order );

		return $response;
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'wc-multivendor-marketplace'),
				'type' => 'checkbox',
				'default' => 'yes'
			)
		);
	}

	protected function delete_vendor_commission( $order_id ) {
		global $wpdb;
		
		$wpdb->delete( $wpdb->prefix . 'wcfm_marketplace_orders', array( 'order_id' => $order_id ), array( '%d' ) );
		delete_post_meta( $order_id, '_wcfmmp_order_processed' );
		delete_post_meta( $order_id, '_wcfm_store_invoices' );
		
		// Order Item Meta Reset - Order rest already performing this
		$order        = wc_get_order( $order_id );
		$line_items = $order->get_items( 'line_item' );
		if( !empty( $line_items ) ) {
			foreach( $line_items as $item_id => $item ) {
				$order_item_id = $item->get_id();
				wc_delete_order_item_meta( $order_item_id, '_wcfmmp_order_item_processed' );
			}
		}
	}

	/**
	 * Prepare & assign stripe customer object
	 *
	 * @since 1.0.0
	 * @param object $user
	 * @param array $postData
	 *
	 * @throws Exception When card was not added or for and invalid card.
	 * @return object
	 */
	public function prepare_customer_obj( $user, $postData, $order ) {
		
		if( !isset( $postData['stripe_source'] ) || empty( $postData['stripe_source'] ) ) {
			wcfm_stripe_log("Error creating customer record with Stripe: Stripe Source Missing");
			return false;
		}
		
		// Create stripe customer
		try {
			
			$source_object = sanitize_text_field( $postData['stripe_source'] );
			if ( ! empty( $postData['stripe_source'] ) ) {
				if( $this->is_3d_secure ) {
					$source_object = WCFM_Stripe_API::retrieve( 'sources/' . wc_clean( $_POST['stripe_source'] ) );
					$source_object = $source_object->id;
				}
			}
			
			if( is_wc_endpoint_url( 'order-pay' ) ) {
				$customer_data = apply_filters('wcfmmp_stripe_split_pay_customer_data', array(
																									"name"   => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
																									"email"  => sanitize_email( $order->get_billing_email() ),
																									"phone"  => sanitize_text_field( $order->get_billing_phone() ),
																									"source" => $source_object,
																									"description" => __( 'Customer for', 'wc-multivendor-marketplace' ) . ' ' . get_bloginfo(),
								                       ), $user, $postData);
			} else {
				$customer_data = apply_filters('wcfmmp_stripe_split_pay_customer_data', array(
																					"name"   => sanitize_text_field( $postData['billing_first_name'] ) . ' ' . sanitize_text_field( $postData['billing_last_name'] ),
																					"email"  => sanitize_email( $postData['billing_email'] ),
																					"phone"  => sanitize_text_field( $postData['billing_phone'] ),
																					"source" => $source_object,
																					"description" => __( 'Customer for', 'wc-multivendor-marketplace' ) . ' ' . get_bloginfo(),
								                      ), $user, $postData);
			}
			if ($this->debug)
			  wcfm_stripe_log("Before creating customer record with Stripe. Stripe Data: " . serialize($customer_data));
			
			
			$stripe_customer_id = '';
			
			if( is_a( $user, 'WP_User' ) ) {
				$stripe_customer_id = get_user_meta( $user->ID, 'wcfmmp_stripe_split_pay_customer_id', true );
			}
			
			if( $stripe_customer_id ) {
				$this->customer = Stripe_Customer::retrieve( $stripe_customer_id );
				
				if( !$this->customer || !$this->customer->id || isset( $this->customer->deleted ) ) {
					$this->customer = Stripe_Customer::create( $customer_data );
				} else {
					Stripe_Customer::update( $this->customer->id, $customer_data );
				}
			} else {
				$this->customer = Stripe_Customer::create( $customer_data );
			}
			
			$order->update_meta_data( '_stripe_customer_id', $this->customer->id );
			$order->save();
		} catch (Exception $ex) {
			wcfm_stripe_log("Error creating customer record with Stripe: " . $ex->getMessage());
			wc_add_notice(__("Error creating customer record with Stripe: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error');
			return false;
		}
	}
	
	/**
	 * Update stripe customer object
	 *
	 * @since 1.0.0
	 * @param object $user
	 * @param array $postData
	 *
	 * @throws Exception When card was not added or for and invalid card.
	 * @return object
	 */
	public function update_customer_obj( $user, $postData ) {
		try {
			$stripe_customer_id = '';
			
			if( is_a( $user, 'WP_User' ) ) {
				$stripe_customer_id = get_user_meta( $user->ID, 'wcfmmp_stripe_split_pay_customer_id', true );
			}
			
			if( $stripe_customer_id ) {
				$this->customer = Stripe_Customer::retrieve( $stripe_customer_id );
				
				// Saving User Card for Future Use
				if( $this->customer && $this->customer->id && !isset( $this->customer->deleted ) && $this->customer->sources ) {
					$stripe_saved_cards = get_user_meta( $user->ID, 'wcfmmp_stripe_customer_saved_cards', true );
					if( !$stripe_saved_cards ) $stripe_saved_cards = array();
					$stripe_saved_cards[$this->customer->sources->data[0]->id] = array( 'last4' => $this->customer->sources->data[0]->card->last4, 'exp_month' => $this->customer->sources->data[0]->card->exp_month, 'exp_year' => $this->customer->sources->data[0]->card->exp_year );
					update_user_meta( $user->ID, 'wcfmmp_stripe_customer_saved_cards', $stripe_saved_cards );
				}
			}
		} catch (Exception $ex) {
			wcfm_stripe_log("Error update customer cards with Stripe: " . $ex->getMessage());
			wc_add_notice(__("Error update customer cards with Stripe: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error');
			return false;
		}
	}
	
	/**
	 * Stripe Split Charges Refund
	 */
	public function wcfmmp_stripe_split_process_refund( $refund_id, $order_id, $vendor_id ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( $WCFMmp->refund_processed ) return;
		
		if( !$refund_id ) return;
		if( !$order_id ) return;
		//if( !$vendor_id ) return;
		
		$order = wc_get_order( $order_id );
		if( !is_a( $order, 'WC_Order' ) ) return;
		
		if( $order->get_payment_method() != 'stripe_split' ) return;
		
		$vendor_token           = '';
		$stripe_charge_id       = '';
		$refund_application_fee = true;
		$charge_type            = $order->get_meta( "wcfmmp_stripe_split_pay_charge_type" );
		
		if( !$this->is_3d_secure ) {
			if( $vendor_id && ( $charge_type != 'transfers_charges' ) ) {
				$vendor_token     = get_user_meta( $vendor_id, 'access_token', true );
				if( $vendor_token ) {
					$stripe_charge_id = $order->get_meta( "wcfmmp_stripe_split_pay_charge_id_{$vendor_id}" );
					if( !$stripe_charge_id ) return;
				} else {
					$refund_application_fee = false;
					$stripe_charge_id = $order->get_meta( "wcfmmp_stripe_split_pay_charge_id_admin" );
					if( !$stripe_charge_id ) return;
				}
			} else {
				$refund_application_fee = false;
				$stripe_charge_id = $order->get_meta( "wcfmmp_stripe_split_pay_charge_id_admin" );
				if( !$stripe_charge_id ) return;
			}
		}
		
		$reverse_transfer = false;
		if( $vendor_id && $vendor_token && ( $charge_type != 'direct_charges' ) ) {
			$vendor_token = '';
			$reverse_transfer = true;
		}
    
    $split_pay_refund_id = $order->get_meta( "wcfmmp_stripe_split_pay_refund_id_{$refund_id}" );
    if( $split_pay_refund_id ) return;
    
    $is_split_pay_refund_processed = $order->get_meta( "wcfmmp_stripe_split_pay_refund_processed_{$refund_id}" );
    if( $is_split_pay_refund_processed ) return;
    
    $WCFMmp->refund_processed = true;
    
    $sql = "SELECT ID, item_id, commission_id, vendor_id, order_id, is_partially_refunded, refunded_amount, refund_reason FROM {$wpdb->prefix}wcfm_marketplace_refund_request";
		$sql .= " WHERE 1=1";
		$sql .= " AND ID = {$refund_id}";
		$refund_infos = $wpdb->get_results( $sql );
		if( !empty( $refund_infos ) ) {
			foreach( $refund_infos as $refund_info ) {
				$stripe_refund_id      = '';
				$refunded_amount       = (float) $refund_info->refunded_amount;
				$refund_reason         = $refund_info->refund_reason;
				$is_partially_refunded = $refund_info->is_partially_refunded;
				
				if( $is_partially_refunded ) $refund_application_fee = false;
				$refund_application_fee = apply_filters( 'wcfm_is_allow_stripe_refund_application_fee', $refund_application_fee, $refund_id, $order_id, $vendor_id );
				
				if( $this->is_3d_secure ) {
					$stripe_refund_id = $order->get_meta( '_stripe_refund_id' );//$this->process_refund( $refund_info->order_id, $refunded_amount, $refund_reason );
				} else {
				
					\Stripe\Stripe::setApiKey( $this->secret_key );
					
					try {
							$refund = \Stripe\Refund::create( [
									'charge'                 => $stripe_charge_id,
									'amount'                 => $this->get_stripe_amount( round($refunded_amount,2) ),
									'reason'                 => 'requested_by_customer',
									'refund_application_fee' => $refund_application_fee,
									'reverse_transfer'       => $reverse_transfer
							], $vendor_token );
							
							if ( $refund->id ) {
								$stripe_refund_id = $refund->id;
							}
							
					} catch( Exception $e ) {
						wcfm_stripe_log( "Stripe Split Pay refund error: " . $e->getMessage() );
					}
				}
					
				if ( $stripe_refund_id ) {
					
					// Create Transfer Charge Reversal
					try {
						if( $vendor_id && ( $charge_type == 'transfers_charges' ) ) {
							$vendor_token     = get_user_meta( $vendor_id, 'access_token', true );
							if( $vendor_token ) {
								$vendor_trasfer_id = $order->get_meta( 'wcfmmp_stripe_split_pay_transaction_id_'.$vendor_id );
								if( $vendor_trasfer_id ) {
									$trasfer = Stripe_Transfer::retrieve($vendor_trasfer_id);
									$trasfer->reverse();
									
									// Cancel Withdrwal Request
									$commission_ids = '';
									$marketplace_orders = $wpdb->get_results(  $wpdb->prepare( "SELECT ID from {$wpdb->prefix}wcfm_marketplace_orders WHERE order_id = %d AND vendor_id = %d", $order_id, $vendor_id ) );
									foreach( $marketplace_orders as $marketplace_order ) {
										if( $commission_ids ) $commission_ids .= ',';
										$commission_ids .= $marketplace_order->ID;
									}
									if( $commission_ids ) {
									  $withdrawals = $wpdb->get_results(  $wpdb->prepare( "SELECT ID from {$wpdb->prefix}wcfm_marketplace_withdraw_request WHERE commission_ids = %s AND vendor_id = %d", $commission_ids, $vendor_id ) );
									  if( !empty( $withdrawals ) ) {
									  	foreach( $withdrawals as $withdrawal ) {
									  		$WCFMmp->wcfmmp_withdraw->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal->ID, 'cancelled', __( 'Split Pay Reversal', 'wc-multivendor-marketplace' ) );
									  	}
									  }
									}
									
									$order->delete_meta_data( 'wcfmmp_stripe_split_pay_transaction_id_'.$vendor_id );
									$order->save();
									
									wcfm_stripe_log( "Stripe Split Pay transfer reversal successful for #{$refund_id}. Vendors ID => {$vendor_id} Transaction ID => {$vendor_trasfer_id} Stripe refund ID => " . $stripe_refund_id );
								}
							}
						}
					} catch( Exception $e ) {
						 $order->delete_meta_data( 'wcfmmp_stripe_split_pay_transaction_id_'.$vendor_id );
						 $order->save();
						 
						 wcfm_stripe_log( "Stripe Split Pay transfer reversal error: " . $e->getMessage() );
					}
					
					wcfm_stripe_log( "Stripe Split Pay refund successful for #{$refund_id}. Stripe refund ID => " . $stripe_refund_id );
					$order->update_meta_data( 'wcfmmp_stripe_split_pay_refund_id_'.$refund_id, $stripe_refund_id );
					$order->update_meta_data( 'wcfmmp_stripe_split_pay_refund_processed_'.$refund_id, 'yes' );
					$order->add_order_note( sprintf( __( 'Refund Processed Via Stripe ( Refund ID: #%s )', 'wc-multivendor-marketplace' ), $refund_id ) );
				} else {
					wcfm_stripe_log( "Stripe Split Pay refund failed #" . $refund_id );
				}
			}
		}
	}
	
	/**
	 * Refund a charge.
	 *
	 * @since 3.2.0
	 * @param  int $order_id
	 * @param  float $amount
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		$request = array();

		$order_currency = $order->get_currency();
		$captured       = $order->get_meta( '_stripe_charge_captured', true );
		$charge_id      = $order->get_transaction_id();

		if ( ! $charge_id ) {
			return false;
		}

		if ( ! is_null( $amount ) ) {
			$request['amount'] = $this->get_stripe_amount( $amount, $order_currency );
		}

		// If order is only authorized, don't pass amount.
		if ( 'yes' !== $captured ) {
			unset( $request['amount'] );
		}

		if ( $reason ) {
			$request['metadata'] = array(
				'reason' => $reason,
			);
		}

		$request['charge'] = $charge_id;
		wcfm_stripe_log( "Info: Beginning refund for order {$charge_id} for the amount of {$amount}" );

		$request = apply_filters( 'wc_stripe_refund_request', $request, $order );

		$intent = $this->get_intent_from_order( $order );
		$intent_cancelled = false;
		if ( $intent ) {
			// If the order has a Payment Intent pending capture, then the Intent itself must be refunded (cancelled), not the Charge
			if ( ! empty( $intent->error ) ) {
				$response = $intent;
				$intent_cancelled = true;
			} elseif ( 'requires_capture' === $intent->status ) {
				$result = WCFM_Stripe_API::request(
					array(),
					'payment_intents/' . $intent->id . '/cancel'
				);
				$intent_cancelled = true;

				if ( ! empty( $result->error ) ) {
					$response = $result;
				} else {
					$charge = end( $result->charges->data );
					$response = end( $charge->refunds->data );
				}
			}
		}

		if ( ! $intent_cancelled ) {
			$response = WCFM_Stripe_API::request( $request, 'refunds' );
		}

		if ( ! empty( $response->error ) ) {
			wcfm_stripe_log( 'Error: ' . $response->error->message );

			return false;

		} elseif ( ! empty( $response->id ) ) {
			$order->update_meta_data( '_stripe_refund_id', $response->id );

			$amount = wc_price( $response->amount / 100 );

			if ( in_array( strtolower( $order->get_currency() ), $this->no_decimal_currencies() ) ) {
				$amount = wc_price( $response->amount );
			}

			if ( isset( $response->balance_transaction ) ) {
				$this->update_fees( $order, $response->balance_transaction );
			}

			/* translators: 1) dollar amount 2) transaction id 3) refund message */
			$refund_message = ( isset( $captured ) && 'yes' === $captured ) ? sprintf( __( 'Refunded %1$s - Refund ID: %2$s - Reason: %3$s', 'wc-multivendor-marketplace' ), $amount, $response->id, $reason ) : __( 'Pre-Authorization Released', 'wc-multivendor-marketplace' );

			$order->add_order_note( $refund_message );
			wcfm_stripe_log( 'Success: ' . html_entity_decode( wp_strip_all_tags( $refund_message ) ) );

			return $response->id;
		}
	}
	
	/**
	 * Loads the order from the current request.
	 *
	 * @since 4.2.0
	 * @throws WC_Stripe_Exception An exception if there is no order ID or the order does not exist.
	 * @return WC_Order
	 */
	protected function get_order_from_request() {
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'wc_stripe_confirm_pi' ) ) {
			throw new Exception( 'missing-nonce', __( 'CSRF verification failed.', 'wc-multivendor-marketplace' ) );
		}

		// Load the order ID.
		$order_id = null;
		if ( isset( $_GET['order'] ) && absint( $_GET['order'] ) ) {
			$order_id = absint( $_GET['order'] );
		}

		// Retrieve the order.
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			throw new Exception( 'missing-order', __( 'Missing order ID for payment confirmation', 'wc-multivendor-marketplace' ) );
		}

		return $order;
	}
	
	/**
	 * Handles successful PaymentIntent authentications.
	 *
	 * @since 3.2.0
	 */
	public function verify_intent() {
		global $woocommerce;

		try {
			$order = $this->get_order_from_request();
		} catch ( Exception $es ) {
			/* translators: Error message text */
			$message = sprintf( __( 'Payment verification error: %s', 'wc-multivendor-marketplace' ), $ex->getMessage() );
			
			wcfm_stripe_log( "Stripe Split Pay Error: " . esc_html( $message ) );
			wc_add_notice( __("Stripe Split Pay Error: ", 'wc-multivendor-marketplace') . esc_html( $message ), 'error' );

			$redirect_url = $woocommerce->cart->is_empty()
				? get_permalink( woocommerce_get_page_id( 'shop' ) )
				: wc_get_checkout_url();

			if ( isset( $_GET['is_ajax'] ) ) {
				exit;
			}
	
			wp_safe_redirect( $redirect_url );
		}

		try {
			$this->verify_intent_after_checkout( $order );

			if ( ! isset( $_GET['is_ajax'] ) ) {
				$redirect_url = isset( $_GET['redirect_to'] ) // wpcs: csrf ok.
					? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) // wpcs: csrf ok.
					: $gateway->get_return_url( $order );

				wp_safe_redirect( $redirect_url );
			}

			exit;
		} catch ( Exception $ex ) {
			
			wcfm_stripe_log( "Stripe Split Pay Error: " . esc_html( $ex->getMessage() ) );
			wc_add_notice( __("Stripe Split Pay Error: ", 'wc-multivendor-marketplace') . esc_html( $ex->getMessage() ), 'error' );
			
			wp_safe_redirect( $this->get_return_url( $order ) );
		}
	}
	
	/**
	 * Adds the necessary hooks to modify the "Pay for order" page in order to clean
	 * it up and prepare it for the Stripe PaymentIntents modal to confirm a payment.
	 *
	 * @since 3.2
	 * @param WC_Payment_Gateway[] $gateways A list of all available gateways.
	 * @return WC_Payment_Gateway[]          Either the same list or an empty one in the right conditions.
	 */
	public function prepare_order_pay_page( $gateways ) {
		if ( ! is_wc_endpoint_url( 'order-pay' ) || ! isset( $_GET['wcfm-stripe-confirmation'] ) ) { // wpcs: csrf ok.
			return $gateways;
		}

		add_filter( 'woocommerce_checkout_show_terms', '__return_false' );
		add_filter( 'woocommerce_pay_order_button_html', '__return_false' );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, '__return_empty_array' ) );
		add_filter( 'woocommerce_no_available_payment_methods_message', array( $this, 'change_no_available_methods_message' ) );
		add_action( 'woocommerce_pay_order_after_submit', array( $this, 'render_payment_intent_inputs' ) );

		return array();
	}
	
	/**
	 * Changes the text of the "No available methods" message to one that indicates
	 * the need for a PaymentIntent to be confirmed.
	 *
	 * @since 4.2
	 * @return string the new message.
	 */
	public function change_no_available_methods_message() {
		return wpautop( __( "Almost there!\n\nYour order has already been created, the only thing that still needs to be done is for you to authorize the payment with your bank.", 'wc-multivendor-marketplace' ) );
	}

	/**
	 * Renders hidden inputs on the "Pay for Order" page in order to let Stripe handle PaymentIntents.
	 *
	 * @since 4.2
	 */
	public function render_payment_intent_inputs() {
		$order     = wc_get_order( absint( get_query_var( 'order-pay' ) ) );
		$intent    = $this->get_intent_from_order( $order );

		$verification_url = add_query_arg(
			array(
				'order'            => $order->get_id(),
				'nonce'            => wp_create_nonce( 'wc_stripe_confirm_pi' ),
				'redirect_to'      => rawurlencode( $this->get_return_url( $order ) ),
				'is_pay_for_order' => true,
			),
			WC_AJAX::get_endpoint( 'wc_stripe_verify_intent' )
		);

		echo '<input type="hidden" id="stripe-intent-id" value="' . esc_attr( $intent->client_secret ) . '" />';
		echo '<input type="hidden" id="stripe-intent-return" value="' . esc_attr( $verification_url ) . '" />';
	}
	
	/**
	 * Attempt to manually complete the payment process for orders, which are still pending
	 * before displaying the View Order page. This is useful in case webhooks have not been set up.
	 *
	 * @since 4.2.0
	 * @param int $order_id The ID that will be used for the thank you page.
	 */
	public function check_intent_status_on_order_page( $order_id ) {
		if ( empty( $order_id ) || absint( $order_id ) <= 0 ) {
			return;
		}

		$order = wc_get_order( absint( $order_id ) );
		$this->verify_intent_after_checkout( $order );
	}

	/**
	 * Attached to `woocommerce_payment_successful_result` with a late priority,
	 * this method will combine the "naturally" generated redirect URL from
	 * WooCommerce and a payment intent secret into a hash, which contains both
	 * the secret, and a proper URL, which will confirm whether the intent succeeded.
	 *
	 * @since 4.2.0
	 * @param array $result   The result from `process_payment`.
	 * @param int   $order_id The ID of the order which is being paid for.
	 * @return array
	 */
	public function modify_successful_payment_result( $result, $order_id ) {
		// Only redirects with intents need to be modified.
		if ( ! isset( $result['intent_secret'] ) ) {
			return $result;
		}

		// Put the final thank you page redirect into the verification URL.
		$verification_url = add_query_arg(
			array(
				'order'       => $order_id,
				'nonce'       => wp_create_nonce( 'wc_stripe_confirm_pi' ),
				'redirect_to' => rawurlencode( $result['redirect'] ),
			),
			WC_AJAX::get_endpoint( 'wc_stripe_verify_intent' )
		);

		// Combine into a hash.
		$redirect = sprintf( '#confirm-pi-%s:%s', $result['intent_secret'], rawurlencode( $verification_url ) );

		return array(
			'result'   => 'success',
			'redirect' => $redirect,
		);
	}

	/**
	 * Proceed with current request using new login session (to ensure consistent nonce).
	 */
	public function set_cookie_on_current_request( $cookie ) {
		$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
	}

	/**
	 * Executed between the "Checkout" and "Thank you" pages, this
	 * method updates orders based on the status of associated PaymentIntents.
	 *
	 * @since 3.2.0
	 * @param WC_Order $order The order which is in a transitional state.
	 */
	public function verify_intent_after_checkout( $order ) {
		$payment_method = $order->get_payment_method();
		if ( $payment_method !== $this->id ) {
			// If this is not the payment method, an intent would not be available.
			return;
		}

		$intent = $this->get_intent_from_order( $order );
		if ( ! $intent ) {
			// No intent, redirect to the order received page for further actions.
			return;
		}

		// A webhook might have modified or locked the order while the intent was retreived. This ensures we are reading the right status.
		clean_post_cache( $order->get_id() );
		$order = wc_get_order( $order->get_id() );

		if ( 'pending' !== $order->get_status() && 'failed' !== $order->get_status() ) {
			// If payment has already been completed, this function is redundant.
			return;
		}

		if ( $this->lock_order_payment( $order, $intent ) ) {
			return;
		}

		if ( 'succeeded' === $intent->status || 'requires_capture' === $intent->status ) {
			// Proceed with the payment completion.
			$this->process_response( end( $intent->charges->data ), $order );
		} else if ( 'requires_payment_method' === $intent->status ) {
			// `requires_payment_method` means that SCA got denied for the current payment method.
			$this->failed_sca_auth( $order, $intent );
		}

		$this->unlock_order_payment( $order );
	}

	/**
	 * Checks if the payment intent associated with an order failed and records the event.
	 *
	 * @since 4.2.0
	 * @param WC_Order $order  The order which should be checked.
	 * @param object   $intent The intent, associated with the order.
	 */
	public function failed_sca_auth( $order, $intent ) {
		// If the order has already failed, do not repeat the same message.
		if ( 'failed' === $order->get_status() ) {
			return;
		}

		// Load the right message and update the status.
		$status_message = ( $intent->last_payment_error )
			/* translators: 1) The error message that was received from Stripe. */
			? sprintf( __( 'Stripe SCA authentication failed. Reason: %s', 'wc-multivendor-marketplace' ), $intent->last_payment_error->message )
			: __( 'Stripe SCA authentication failed.', 'wc-multivendor-marketplace' );
		$order->update_status( 'failed', $status_message );

		$this->send_failed_order_email( $order->get_id() );
	}
	
	/**
	 * Sends the failed order email to admin.
	 *
	 * @since 3.1.0
	 * @version 4.0.0
	 * @param int $order_id
	 * @return null
	 */
	public function send_failed_order_email( $order_id ) {
		$emails = WC()->mailer()->get_emails();
		if ( ! empty( $emails ) && ! empty( $order_id ) ) {
			$emails['WC_Email_Failed_Order']->trigger( $order_id );
		}
	}
	
	/**
	 * Updates Stripe fees/net.
	 * e.g usage would be after a refund.
	 *
	 * @since 3.2.0
	 * @param object $order The order object
	 * @param int $balance_transaction_id
	 */
	public function update_fees( $order, $balance_transaction_id ) {
		$order_id = $order->get_id();

		$balance_transaction = WCFM_Stripe_API::retrieve( 'balance/history/' . $balance_transaction_id );

		if ( empty( $balance_transaction->error ) ) {
			if ( isset( $balance_transaction ) && isset( $balance_transaction->fee ) ) {
				// Fees and Net needs to both come from Stripe to be accurate as the returned
				// values are in the local currency of the Stripe account, not from WC.
				$fee_refund = ! empty( $balance_transaction->fee ) ? WCFM_Stripe_Helper::format_balance_fee( $balance_transaction, 'fee' ) : 0;
				$net_refund = ! empty( $balance_transaction->net ) ? WCFM_Stripe_Helper::format_balance_fee( $balance_transaction, 'net' ) : 0;

				// Current data fee & net.
				$fee_current = WCFM_Stripe_Helper::get_stripe_fee( $order );
				$net_current = WCFM_Stripe_Helper::get_stripe_net( $order );

				// Calculation.
				$fee = (float) $fee_current + (float) $fee_refund;
				$net = (float) $net_current + (float) $net_refund;

				WCFM_Stripe_Helper::update_stripe_fee( $order, $fee );
				WCFM_Stripe_Helper::update_stripe_net( $order, $net );

				$currency = ! empty( $balance_transaction->currency ) ? strtoupper( $balance_transaction->currency ) : null;
				WCFM_Stripe_Helper::update_stripe_currency( $order, $currency );

				if ( is_callable( array( $order, 'save' ) ) ) {
					$order->save();
				}
			}
		} else {
			wcfm_stripe_log( "Unable to update fees/net meta for order: {$order_id}" );
		}
	}

	public function get_stripe_amount($total, $currency = '', $reverse = false) {
		if (!$currency) {
			$currency = get_woocommerce_currency();
		}
		
		$total = round($total, 2); 

		if (in_array(strtolower($currency), $this->no_decimal_currencies())) {
			return absint($total);
		} else {
			if ($reverse) {
				return absint(wc_format_decimal(( (float) $total / 100), wc_get_price_decimals())); // actual.
			} else {
				return absint(wc_format_decimal(( (float) $total * 100), wc_get_price_decimals())); // In cents.
			}
		}
	}
	
	public static function clean_statement_descriptor( $statement_descriptor = '' ) {
		$disallowed_characters = array( '<', '>', '"', "'" );

		// Remove special characters.
		$statement_descriptor = str_replace( $disallowed_characters, '', $statement_descriptor );

		$statement_descriptor = substr( trim( $statement_descriptor ), 0, 22 );

		return $statement_descriptor;
	}
	
	/**
	 * Generate the request for the payment.
	 *
	 * @since 3.2.0
	 * @param  WC_Order $order
	 * @param  object $prepared_source
	 * @return array()
	 */
	public function generate_payment_request( $order, $prepared_source ) {
		$statement_descriptor  = $this->clean_statement_descriptor( get_bloginfo( 'name' ) );
		$capture               = true;
		$post_data             = array();
		$post_data['currency'] = $order->get_currency();
		$post_data['amount']   = $this->get_stripe_amount( $order->get_total(), $post_data['currency'] );
		/* translators: 1) blog name 2) order number */
		$post_data['description'] = sprintf( __( '%1$s - Order %2$s', 'wc-multivendor-marketplace' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() );
		$billing_email            = $order->get_billing_email();
		$billing_first_name       = $order->get_billing_first_name();
		$billing_last_name        = $order->get_billing_last_name();

		if ( ! empty( $billing_email ) && apply_filters( 'wc_stripe_send_stripe_receipt', false ) ) {
			$post_data['receipt_email'] = $billing_email;
		}
		
		$post_data['statement_descriptor'] = $statement_descriptor;

		$post_data['expand[]'] = 'balance_transaction';

		$metadata = array(
			__( 'customer_name', 'wc-multivendor-marketplace' ) => sanitize_text_field( $billing_first_name ) . ' ' . sanitize_text_field( $billing_last_name ),
			__( 'customer_email', 'wc-multivendor-marketplace' ) => sanitize_email( $billing_email ),
			'order_id' => $order->get_order_number(),
		);

		$post_data['metadata'] = apply_filters( 'wc_stripe_payment_metadata', $metadata, $order, $prepared_source );

		if ( $prepared_source->customer ) {
			$post_data['customer'] = $prepared_source->customer;
		}

		if ( $prepared_source->source ) {
			$post_data['source'] = $prepared_source->source;
		}

		return apply_filters( 'wc_stripe_generate_payment_request', $post_data, $order, $prepared_source );
	}
	
	/**
	 * Create a new PaymentIntent.
	 *
	 * @param WC_Order $order           The order that is being paid for.
	 * @param object   $prepared_source The source that is used for the payment.
	 * @return object                   An intent or an error.
	 */
	public function create_intent( $order, $prepared_source ) {
		// The request for a charge contains metadata for the intent.
		$full_request = $this->generate_payment_request( $order, $prepared_source );

		$request = array(
			'source'               => $prepared_source->source,
			'amount'               => $this->get_stripe_amount( $order->get_total() ),
			'currency'             => strtolower( $order->get_currency() ),
			'description'          => $full_request['description'],
			'metadata'             => $full_request['metadata'],
			'statement_descriptor' => $full_request['statement_descriptor'],
			'capture_method'       => 'automatic',
			'payment_method_types' => array(
				'card',
			),
		);

		if ( $prepared_source->customer ) {
			$request['customer'] = $prepared_source->customer;
		}

		// Create an intent that awaits an action.
		$intent = WCFM_Stripe_API::request( $request, 'payment_intents' );
		if ( ! empty( $intent->error ) ) {
			return $intent;
		}

		$order_id = $order->get_id();
		wcfm_stripe_log( "Stripe PaymentIntent $intent->id initiated for order $order_id" );

		// Save the intent ID to the order.
		$this->save_intent_to_order( $order, $intent );

		return $intent;
	}

	/**
	 * Updates an existing intent with updated amount, source, and customer.
	 *
	 * @param object   $intent          The existing intent object.
	 * @param WC_Order $order           The order.
	 * @param object   $prepared_source Currently selected source.
	 * @return object                   An updated intent.
	 */
	public function update_existing_intent( $intent, $order, $prepared_source ) {
		$request = array();

		if ( $prepared_source->source !== $intent->source ) {
			$request['source'] = $prepared_source->source;
		}

		$new_amount = $this->get_stripe_amount( $order->get_total() );
		if ( $intent->amount !== $new_amount ) {
			$request['amount'] = $new_amount;
		}

		if ( $prepared_source->customer && $intent->customer !== $prepared_source->customer ) {
			$request['customer'] = $prepared_source->customer;
		}

		if ( empty( $request ) ) {
			return $intent;
		}

		return WCFM_Stripe_API::request( $request, "payment_intents/$intent->id" );
	}

	/**
	 * Confirms an intent if it is the `requires_confirmation` state.
	 *
	 * @since 3.2.0
	 * @param object   $intent          The intent to confirm.
	 * @param WC_Order $order           The order that the intent is associated with.
	 * @param object   $prepared_source The source that is being charged.
	 * @return object                   Either an error or the updated intent.
	 */
	public function confirm_intent( $intent, $order, $prepared_source ) {
		if ( 'requires_confirmation' !== $intent->status ) {
			return $intent;
		}

		// Try to confirm the intent & capture the charge (if 3DS is not required).
		$confirm_request = array(
			'source' => $prepared_source->source,
		);

		$confirmed_intent = WCFM_Stripe_API::request( $confirm_request, "payment_intents/$intent->id/confirm" );

		if ( ! empty( $confirmed_intent->error ) ) {
			return $confirmed_intent;
		}

		// Save a note about the status of the intent.
		$order_id = $order->get_id();
		if ( 'succeeded' === $confirmed_intent->status ) {
			wcfm_stripe_log( "Stripe PaymentIntent $intent->id succeeded for order $order_id" );
		} elseif ( 'requires_action' === $confirmed_intent->status ) {
			wcfm_stripe_log( "Stripe PaymentIntent $intent->id requires authentication for order $order_id" );
		}

		return $confirmed_intent;
	}

	/**
	 * Saves intent to order.
	 *
	 * @since 3.2.0
	 * @param WC_Order $order For to which the source applies.
	 * @param stdClass $intent Payment intent information.
	 */
	public function save_intent_to_order( $order, $intent ) {
		$order_id = $order->get_id();

		$order->update_meta_data( '_wcfmmp_stripe_intent_id', $intent->id );

		if ( is_callable( array( $order, 'save' ) ) ) {
			$order->save();
		}
	}

	/**
	 * Retrieves the payment intent, associated with an order.
	 *
	 * @since 3.2.0
	 * @param WC_Order $order The order to retrieve an intent for.
	 * @return obect|bool     Either the intent object or `false`.
	 */
	public function get_intent_from_order( $order ) {
		$order_id = $order->get_id();
		
		$intent_id = $order->get_meta( '_wcfmmp_stripe_intent_id' );

		if ( ! $intent_id ) {
			return false;
		}

		return WCFM_Stripe_API::request( array(), "payment_intents/$intent_id", 'GET' );
	}

	/**
	 * Locks an order for payment intent processing for 5 minutes.
	 *
	 * @since 4.2
	 * @param WC_Order $order  The order that is being paid.
	 * @param stdClass $intent The intent that is being processed.
	 * @return bool            A flag that indicates whether the order is already locked.
	 */
	public function lock_order_payment( $order, $intent ) {
		$order_id       = $order->get_id();
		$transient_name = 'wcfmmp_stripe_processing_intent_' . $order_id;
		$processing     = get_transient( $transient_name );

		// Block the process if the same intent is already being handled.
		if ( $processing === $intent->id ) {
			return true;
		}

		// Save the new intent as a transient, eventually overwriting another one.
		set_transient( $transient_name, $intent->id, 5 * MINUTE_IN_SECONDS );

		return false;
	}

	/**
	 * Unlocks an order for processing by payment intents.
	 *
	 * @since 3.2.0
	 * @param WC_Order $order The order that is being unlocked.
	 */
	public function unlock_order_payment( $order ) {
		$order_id = $order->get_id();
		delete_transient( 'wcfmmp_stripe_processing_intent_' . $order_id );
	}

	/**
	 * List of currencies supported by Stripe that has no decimals.
	 *
	 * @return array $currencies
	 */
	public function no_decimal_currencies() {
		return apply_filters('wcfmmp_stripe_split_pay_no_decimal_currencies', array(
				'bif', // Burundian Franc
				'djf', // Djiboutian Franc
				'jpy', // Japanese Yen
				'krw', // South Korean Won
				'pyg', // Paraguayan GuaranÃ­
				'vnd', // Vietnamese Ä�á»“ng
				'xaf', // Central African Cfa Franc
				'xpf', // Cfp Franc
				'clp', // Chilean Peso
				'gnf', // Guinean Franc
				'kmf', // Comorian Franc
				'mga', // Malagasy Ariary
				'rwf', // Rwandan Franc
				'vuv', // Vanuatu Vatu
				'xof', // West African Cfa Franc
		));
	}

	public function stripe_access_token_error() { ?>
		<div id="message tes" class="error">
				<p><?php printf( __( "<strong>Stripe Gateway is disabled.</strong> Please re-check %swithdrawal setting panel%s. This occurs mostly due to absence of Stripe Secret Key", 'wc-multivendor-marketplace' ), '<a href="'.get_wcfm_settings_url().'#wcfm_settings_form_withdrawal_head">', '</a>' ); ?></p>
		</div>
	<?php }
}