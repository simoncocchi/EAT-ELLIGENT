<?php

if (!defined('ABSPATH')) {
   return;
}

use Moip\Moip;
use Moip\Auth\OAuth;
use  Moip\Auth\BasicAuth;
use Moip\Auth\Connect;

class WCFMmp_Gateway_Wirecard extends WC_Payment_Gateway {
	
	public  $customer;
	private $vendor_disconnected;
	
	public  $gateway_title;
	public  $payment_gateway;
	public  $message = array();
	
	private $token;
	private $key;
	private $public_key;
	
	private $is_testmode = false;
	private $wirecard_fee = 'vendor';
	private $payout_mode = 'true';
	
	private $reciver_email;
	
	private $api_endpoint;
	private $token_endpoint;
	private $access_token;
	private $token_type;
    
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$this->id                 = 'wirecard';
		$this->method_title       = __( 'Marketplace Wirecard', 'wc-multivendor-marketplace' );
		$this->method_description = __( 'Have your customers pay with credit card.', 'wc-multivendor-marketplace' );
		$this->has_fields         = true;
		$this->supports           = array( 'products', 'refunds' );

		// load form fields
		$this->init_form_fields();

		// load settings
		$this->init_settings();

		// get settings value
		$this->title           = $this->get_option( 'title' );
		$this->description     = $this->get_option( 'description' );
		$this->enabled         = $this->get_option( 'enabled' );
		//$$this->is_testmode        = $this->get_option( 'testmode' );
		$this->wirecard_fee    = $this->get_option( 'wirecard_fee' );
		
		$withdrawal_test_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['test_mode'] ) ? 'yes' : 'no';
		
		$this->token      = isset( $WCFMmp->wcfmmp_withdrawal_options['wirecard_token'] ) ? $WCFMmp->wcfmmp_withdrawal_options['wirecard_token'] : '';
		$this->key        = isset( $WCFMmp->wcfmmp_withdrawal_options['wirecard_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['wirecard_key'] : '';
		$this->public_key = isset( $WCFMmp->wcfmmp_withdrawal_options['wirecard_public_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['wirecard_public_key'] : '';
		
		if ( $withdrawal_test_mode == 'yes') {
			$this->is_testmode = true;
			$this->token       = isset( $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_token'] ) ? $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_token'] : '';
			$this->key         = isset( $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_key'] : '';
			$this->public_key  = isset( $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_public_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_public_key'] : '';
		}
		
		$this->api_endpoint   = ( $this->is_testmode ) ? 'https://sandbox.moip.com.br' : 'https://api.moip.com.br';
		
		// Register the Wirecard gateway
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_wirecard_gateway' ) );
		
		// De-register WCFMmp Auto-withdrawal Gateway
		add_filter( 'wcfm_marketplace_disallow_active_order_payment_methods', array( $this, 'wcfmmp_auto_withdrawal_wirecard' ), 750 );
		
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		//add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'get_wirecard_access_token' ) );
		
		// Process Refund
		add_action( 'wcfmmp_refund_status_completed', array( &$this, 'wcfmmp_wirecard_process_refund' ), 50, 3 );
		
		add_filter( 'woocommerce_credit_card_form_fields', array( $this, 'add_cpf_field' ), 10, 2 );
		
		// include js
		add_action( 'wp_enqueue_scripts', array( $this, 'include_wirecard_js' ) );
		
		add_action( 'wcfm_vendor_end_settings_payment', array( $this, 'wcfm_vendor_wirecard_settings_payment' ) );
	}
	
	public function add_wirecard_gateway($methods) {
		$methods[] = 'WCFMmp_Gateway_Wirecard';
		return $methods;
	}
	
	public function wcfmmp_auto_withdrawal_wirecard( $auto_withdrawal_methods ) {
		if( isset( $auto_withdrawal_methods['wirecard'] ) )
			unset( $auto_withdrawal_methods['wirecard'] );
		return $auto_withdrawal_methods;
	}

	/**
	 * Set form fields
	 *
	 * @return void;
	 */
	public function init_form_fields() {
			$this->form_fields = $this->load_form_fields();
	}

	/**
	 * Get form filds
	 *
	 * @return array
	 */
	public function load_form_fields() {
			$test_url       = 'https://conta-sandbox.moip.com.br/configurations/api_credentials';
			$production_url = 'https://conta.moip.com.br/configurations/api_credentials';

			return array(
				'enabled' => array(
						'title'       => __( 'Enable/Disable', 'wc-multivendor-marketplace' ),
						'label'       => __( 'Enable Wirecard', 'wc-multivendor-marketplace' ),
						'type'        => 'checkbox',
						'description' => '',
						'default'     => 'no'
				),
				'title' => array(
						'title'       => __( 'Title', 'wc-multivendor-marketplace' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'wc-multivendor-marketplace' ),
						'default'     => __( 'Wirecard Credit Card', 'wc-multivendor-marketplace' )
				),
				'description' => array(
						'title'       => __( 'Description', 'wc-multivendor-marketplace' ),
						'type'        => 'textarea',
						'description' => __( 'This controls the description which the user sees during checkout.', 'wc-multivendor-marketplace' ),
						'default'     => 'Pay with your credit card via Wirecard.'
				),
				'wirecard_fee' => array(
						'title'       => __( 'Wirecard Fee', 'wc-multivendor-marketplace' ),
						'type'        => 'select',
						'options'     => array(
								'admin'   => __( 'Admin', 'wc-multivendor-marketplace' ),
								'vendor'  => __( 'Vendor', 'wc-multivendor-marketplace' ),
						),
						'description' => __( 'Select who will bear the Wirecard transection fee.', 'wc-multivendor-marketplace' ),
						'default'     => 'vendor'
				),
			);
	}

	/**
	 * Get moip access token
	 *
	 * @return void
	 */
	public function get_wirecard_access_token() {
		$post_data = wp_unslash( $_POST );
		$field_key = "woocommerce_{$this->id}_";

		if ( ! isset( $post_data["{$field_key}enabled"] ) || $post_data["{$field_key}enabled"] != 1 ) {
				return;
		}

		$base_url   = ( $this->is_testmode ) ? esc_url( 'https://sandbox.moip.com.br/v2/channels' ) : esc_url( 'https://api.moip.com.br/v2/channels' );

		if ( empty( $this->key ) || empty( $this->token ) || empty( $this->public_key ) ) {
				return;
		}

		$body = array(
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'site'        => get_site_url(),
			'redirectUri' => add_query_arg( 'marketplace_wirecard', 'yes', get_wcfm_settings_url() )
		);

		$headers = array(
			'Content-Type: application/json',
			'Cache-Control: no-cache',
			'Authorization: Basic ' . base64_encode( $this->token . ':' . $this->key ),
		);

		$response    = wp_remote_post( $base_url, array(
																										'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
																										'timeout'     => 30,
																										'redirection' => 5,
																										'blocking'    => true,
																										'headers'     => $headers,
																										'body'        => json_encode( $body )
																										)
																									);

		if ( is_wp_error( $response ) ) {
			wcfm_wirecard_log( __( 'Wirecard APP: Access Token Generate - Something went wrong Wirecard!', 'wc-multivendor-marketplace' ), 'error' );
			return;
		}
		
		$response = json_decode( $response['body'] );

		if ( isset( $response->ERROR ) ) {
			wcfm_wirecard_log( 'Wirecard APP: Access Token Generate - ' . $response->ERROR, 'error' );
			//return wp_send_json_error( $response->ERROR );
		}

		if ( ! isset( $response->id, $response->secret, $response->accessToken ) ) {
			return;
		}
		
		wcfm_wirecard_log( 'Access Token Generated: ' . json_encode( (array) $response ), 'info' );

		update_option( 'wcfmmp_wirecard_app_id', $response->id );
		update_option( 'wcfmmp_wirecard_secret', $response->secret );
		update_option( 'wcfmmp_wirecard_access_token', $response->accessToken );
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
		
		$access_token        = get_option( 'wcfmmp_wirecard_access_token' );
		
		if ( empty( $access_token ) ) {
			$order->update_status( apply_filters('wcfmmp_wirecard_pay_failed_order_status', 'failed') );
			
			wcfm_wirecard_log( "Wirecard access token is not found.", 'error' );
			wc_add_notice( __("Wirecard access token is not found.", 'wc-multivendor-marketplace' ), 'error');
			
			return array(
				'result' => 'fail',
				'redirect' => $this->get_return_url($order)
			);
		}
		
		wcfm_wirecard_log( "Wirecard order processing start.", 'info' );
		
		$fee_bearer          = $this->wirecard_fee == 'vendor' ? true : false;
		
		$all_success = array();
		
		// get access token
		if ( $this->is_testmode ) {
			$wirecard = new Moip( new OAuth( $access_token ), Moip::ENDPOINT_SANDBOX );
		} else {
			$wirecard = new Moip( new OAuth( $access_token ), Moip::ENDPOINT_PRODUCTION );
		}
		
		// Wirecard customer holder data
		$wirecard_data = $this->set_wirecard_customer_holder( $wirecard, $order );
		
		if ( empty( $wirecard_data ) ) {
			$order->update_status( apply_filters('wcfmmp_wirecard_pay_failed_order_status', 'failed') );
			
			wcfm_wirecard_log( "Wirecard data is not found.", 'error' );
			wc_add_notice( __("Wirecard data is not found.", 'wc-multivendor-marketplace' ), 'error');
			
			return array(
				'result' => 'fail',
				'redirect' => $this->get_return_url($order)
			);
		}
		
		wcfm_wirecard_log( "Wirecard Data :: " . json_encode( $wirecard_data ), 'info' );
		
		if ( ! isset( $_POST['wirecard_hash'] ) || empty( $_POST['wirecard_hash'] ) ) {
			$order->update_status( apply_filters('wcfmmp_wirecard_pay_failed_order_status', 'failed') );
			
			wcfm_wirecard_log( "Credit card nout found.", 'error' );
			wc_add_notice( __("Credit card nout found.", 'wc-multivendor-marketplace' ), 'error');
			
			return array(
				'result' => 'fail',
				'redirect' => $this->get_return_url($order)
			);
		}

		// customer hashed credit card number
		$card_number = wc_clean( $_POST['wirecard_hash'] );
		
		wcfm_wirecard_log( "Wirecard Hash :: " . $card_number, 'info' );
		
		// Split Pay Vendors List
		$wcfmmp_wirecard_pay_list = $WCFMmp->wcfmmp_commission->wcfmmp_split_pay_vendor_list( $order, $_POST, 'wirecard' );
		
		wcfm_wirecard_log( "Wirecard Split List :: " . json_encode( $wcfmmp_wirecard_pay_list ), 'info' );
		
		try {
			
			$wirecard_order = $wirecard->orders()->setOwnId( uniqid() );
			
			// Add Order Items
			$items = $order->get_items();
			foreach ( $items as $item ) {
				$wirecard_order->addItem( $item->get_product_id(), 1, 'sku1', wc_format_decimal( $item->get_subtotal() ) * 100 );
			}
			
			// Add Shipping Cost
			$wirecard_order->setShippingAmount( wc_format_decimal( $order->get_shipping_total() ) * 100 );
				
			// Add Tax Cost
			$wirecard_order->setAddition( wc_format_decimal( $order->get_total_tax() ) * 100 );
				
			// Add Discount Cost
			$wirecard_order->setDiscount( wc_format_decimal( $order->get_discount_total() ) * 100 );
			
			// Set Customer
			$wirecard_order->setCustomer( $wirecard_data['customer'] );
			
			// Set Split Pay Receivers
			if( isset( $wcfmmp_wirecard_pay_list['distribution_list'] ) && is_array( $wcfmmp_wirecard_pay_list['distribution_list'] ) && count( $wcfmmp_wirecard_pay_list['distribution_list'] ) > 0 ) {
				foreach( $wcfmmp_wirecard_pay_list['distribution_list'] as $vendor_id => $distribution_info ) {
					$wirecard_order->addReceiver( $distribution_info['destination'], 'SECONDARY', wc_format_decimal( $distribution_info['commission'] ) * 100, null, $fee_bearer );
				}
			}
			
			wcfm_wirecard_log( "Wirecard Pre Order Obj :: " . json_encode( $wirecard_order ), 'info' );
			
			$wirecard_created_order = $wirecard_order->create();
			
			wcfm_wirecard_log( "Wirecard Order :: " . json_encode( $wirecard_created_order ), 'info' );
			
			wcfm_wirecard_log( "Wirecard Order ID :: " . $wirecard_created_order->getId() , 'info' );
			
			$order->update_meta_data( '_wirecard_order_id', $wirecard_created_order->getId() );
			
			$payment = $wirecard_created_order->payments()
																				->setCreditCardHash( $card_number, $wirecard_data['holder'] )
																				->setInstallmentCount(1)
																				->setStatementDescriptor( __("Order #", 'wc-multivendor-marketplace' ) . $order_id )
																				->execute();
	
			$payment_id = $payment->getId();
		
			if ( ! $payment_id ) {
				$order->update_status( apply_filters('wcfmmp_wirecard_pay_failed_order_status', 'failed') );
				
				wcfm_wirecard_log( "Wirecard payment processing failed.", 'error' );
				wc_add_notice( __("Wirecard payment processing failed.", 'wc-multivendor-marketplace' ), 'error');
				
				return array(
					'result' => 'fail',
					'redirect' => $this->get_return_url($order)
				);
			}
			
			wcfm_wirecard_log( "Wirecard Payment ID :: " . $payment_id , 'info' );
			
			$order->update_meta_data( '_wirecard_payment_id', $payment_id );
			
			$order->set_transaction_id( $payment_id );
		
			$order->payment_complete( $payment_id );
			
		} catch (Exception $ex) {
			wcfm_wirecard_log( "Wirecard order processing failed :: " . $ex->__toString(), 'error' );
			wc_add_notice( __( "Wirecard order processing failed :: ", 'wc-multivendor-marketplace') . $ex->__toString(), 'error');
			
			return array(
					'result' => 'fail',
					'redirect' => $this->get_return_url($order)
				);
		}

		
		if( isset( $wcfmmp_wirecard_pay_list['distribution_list'] ) && is_array( $wcfmmp_wirecard_pay_list['distribution_list'] ) && count( $wcfmmp_wirecard_pay_list['distribution_list'] ) > 0 ) {
			foreach( $wcfmmp_wirecard_pay_list['distribution_list'] as $vendor_id => $distribution_info ) {
				$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($vendor_id) );
				
				// Dristribute among vendors
				$source_transaction = apply_filters( 'wcfmmp_wirecard_pay_source_transaction_enabled', true, $vendor_id );
				try {
					$transfer_data = array(
																	"destination" => $distribution_info['destination'],
																	"description" => $wcfmmp_wirecard_pay_list['transfer_group'],
																	);
					$transfer_data['source_transaction'] = $payment_id;
					$transfer_data = apply_filters('wcfmmp_wirecard_pay_create_transfer', $transfer_data, $_POST);
					
					$all_success[$vendor_id] = "true";
					
					wcfm_wirecard_log("Before creating transfer with Wirecard. Wirecard Transfer Data: " . serialize($transfer_data));
					
					$commission_id_list = $wpdb->get_col("SELECT ID FROM `{$wpdb->prefix}wcfm_marketplace_orders` WHERE order_id =" . $order_id . " AND vendor_id = " . $vendor_id);
					
					// Creating Withdrawal Instance
					$withdrawal_id = $WCFMmp->wcfmmp_withdraw->wcfmmp_withdrawal_processed( $vendor_id, $order_id, implode( ',', $commission_id_list ), 'wirecard', $distribution_info['gross_sales'], $distribution_info['commission'], 0, 'pending', 'by_wirecard', 0 );
					
					// Vendor Transaction ID Store at Order Meta
					$order->update_meta_data( 'wcfmmp_stripe_split_pay_transaction_id_'.$vendor_id, $payment_id );
					
					// Withdrawal Processing
					$WCFMmp->wcfmmp_withdraw->wcfmmp_withdraw_status_update_by_withdrawal( $withdrawal_id, 'completed', __( 'Wirecard Pay', 'wc-multivendor-marketplace' ) );
					
					// Withdrawal Meta
					$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_id', $payment_id );
					$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $withdrawal_id, 'transaction_ref', $wirecard_created_order->getId() );
					
					do_action( 'wcfmmp_withdrawal_request_approved', $withdrawal_id );
					
					wcfm_wirecard_log( sprintf( '#%s - %s payment processing complete via %s for order %s. Amount: %s', sprintf( '%06u', $withdrawal_id ), $store_name, 'Wirecard Pay', $order_id, $distribution_info['commission'] . ' ' . $order->get_currency() ), 'info' );
						
				} catch (Exception $ex) {
					wcfm_wirecard_log( $store_name . " Error creating transfer record with Wirecard: " . $ex->getMessage());
					wc_add_notice(__("Error creating transfer record with Wirecard: ", 'wc-multivendor-marketplace') . $ex->getMessage(), 'error');
					$all_success[$vendor_id] = "false";
				}
			}
		}
		
		if( is_array( $all_success ) && in_array( "false", $all_success ) ) {
			$order->update_status( apply_filters('wcfmmp_wirecard_pay_failed_order_status', 'failed') );
			
			if ( is_callable( array( $order, 'save' ) ) ) {
				$order->save();
			}
			
			wc_add_notice( __("Stripe Payment Error", 'wc-multivendor-marketplace'), 'error' );
			
			return array(
				'result' => 'fail',
				'redirect' => $this->get_return_url($order)
			);
		} else {
			$order->update_status( apply_filters('wcfmmp_wirecard_pay_completed_order_status', 'processing') );
			
			if ( is_callable( array( $order, 'save' ) ) ) {
				$order->save();
			}
			
			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			);
		}
	}
	
	/**
	 * Format Wirecard customer and holder data
	 *
	 * @param object $moip
	 * @param object $order
	 *
	 * @return array
	 */
	public function set_wirecard_customer_holder( $wirecard, $order ) {
		// create customer info
		$customer_info = array();
		$customer_info['full_name']    = $order->get_formatted_billing_full_name();
		$customer_info['email']        = $order->get_billing_email();
		$customer_info['birthdate']    = '';
		$customer_info['tax_document'] = wc_clean( $_POST['billing_wirecard_cpf'] );
		$customer_info['phone_prefix'] = substr( $order->get_billing_phone(), 0, 2 );
		$customer_info['phone_sufix']  = substr( $order->get_billing_phone(), 2 );
		
		$wirecard_data = array();
		
		try {
			$customer = $wirecard->customers()->setOwnId( uniqid() )
							->setFullname( $customer_info['full_name'] )
							->setEmail( $customer_info['email'] )
							->setBirthDate( '1986-07-07' )
							->setTaxDocument( $customer_info['tax_document'] )
							->setPhone( $customer_info['phone_prefix'], $customer_info['phone_sufix'] )
							->create();
	
			$holder = $wirecard->holders()
							->setFullname( $customer_info['full_name'] )
							->setBirthDate( '1989-07-07' )
							->setTaxDocument( $customer_info['tax_document'] )
							->setPhone( $customer_info['phone_prefix'], $customer_info['phone_sufix'] );
	
		} catch (Exception $ex) {
			wcfm_wirecard_log( "Error creating Customer and Holder :: " . $ex->__toString(), 'error' );
			wc_add_notice( __( "Error creating Customer and Holder :: ", 'wc-multivendor-marketplace') . $ex->__toString(), 'error');
			return $wirecard_data;
		}
		
		$wirecard_data['customer'] = $customer;
		$wirecard_data['holder']   = $holder;

		return $wirecard_data;
	}
	
	/**
	 * Stripe Split Charges Refund
	 */
	public function wcfmmp_wirecard_process_refund( $refund_id, $order_id, $vendor_id ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( $WCFMmp->refund_processed ) return;
		
		if( !$refund_id ) return;
		if( !$order_id ) return;
		//if( !$vendor_id ) return;
		
		$order = wc_get_order( $order_id );
		if( !is_a( $order, 'WC_Order' ) ) return;
		
		if( $order->get_payment_method() != 'wirecard' ) return;
		
		$payment_id = $order->get_meta( "_wirecard_payment_id" );
		
		if( !$payment_id ) return; 
		
    $WCFMmp->refund_processed = true;
    
    $sql = "SELECT item_id, commission_id, vendor_id, order_id, is_partially_refunded, refunded_amount, refund_reason FROM {$wpdb->prefix}wcfm_marketplace_refund_request";
		$sql .= " WHERE 1=1";
		$sql .= " AND ID = {$refund_id}";
		$refund_infos = $wpdb->get_results( $sql );
		if( !empty( $refund_infos ) ) {
			foreach( $refund_infos as $refund_info ) {
				$stripe_refund_id      = '';
				$refunded_amount       = (float) $refund_info->refunded_amount;
				$refund_reason         = $refund_info->refund_reason;
				$is_partially_refunded = $refund_info->is_partially_refunded;
				
				$wireard_refund_id = $order->get_meta( '_wirecard_refund_id' ); //$this->process_refund( $refund_info->order_id, $refunded_amount, $refund_reason );
					
				if ( $wireard_refund_id ) {
					wcfm_wirecard_log( "Wirecard Pay refund successful for #{$refund_id}. Wirecard refund ID => " . $wireard_refund_id );
					$order->update_meta_data( 'wcfmmp_wirecard_pay_refund_id_'.$refund_id, $wireard_refund_id );
					$order->update_meta_data( 'wcfmmp_wirecard_pay_refund_processed_'.$refund_id, 'yes' );
					$order->add_order_note( sprintf( __( 'Refund Processed Via Wirecard ( Refund ID: #%s )', 'wc-multivendor-marketplace' ), $refund_id ) );
				} else {
					wcfm_wirecard_log( "Wirecard Pay refund failed #" . $refund_id );
				}
			}
		}
	}
	
	/**
	 * Refund a charge.
	 *
	 * @since 3.2.2
	 * @param  int $order_id
	 * @param  float $amount
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}
		
		$access_token  = get_option( 'wcfmmp_wirecard_access_token' );
		
		if( ! $access_token ) {
			return false;
		}
		
		$order_currency = $order->get_currency();
		$payment_id      = $order->get_transaction_id();

		if ( ! $payment_id ) {
			return false;
		}

		// get access token
		if ( $this->is_testmode ) {
			$wirecard = new Moip( new OAuth( $access_token ), Moip::ENDPOINT_SANDBOX );
		} else {
			$wirecard = new Moip( new OAuth( $access_token ), Moip::ENDPOINT_PRODUCTION );
		}

		wcfm_wirecard_log( "Beginning refund for order {$payment_id} for the amount of {$amount}", 'info' );
		
		try {
			$payment = $wirecard->payments()->get( $payment_id );
			
			$order_total = $order->get_total();
	
			if ( ! is_null( $amount ) && ( $order_total > $amount ) ) {
				$amount = wc_format_decimal( $amount ) * 100;
				wcfm_wirecard_log( "Partial Refund :: " . $amount );
				$refund = $payment->refunds()->creditCardPartial( $amount );
			} else {
				wcfm_wirecard_log( "Full Refund" ); 
				$refund = $payment->refunds()->creditCardFull();
			}
			
			$order->update_meta_data( '_wirecard_refund_id', $refund->getId() );
			
			if ( is_callable( array( $order, 'save' ) ) ) {
				$order->save();
			}
			
			/* translators: 1) dollar amount 2) transaction id 3) refund message */
			$refund_message = sprintf( __( 'Refunded %1$s - Refund ID: %2$s - Reason: %3$s', 'wc-multivendor-marketplace' ), $amount, $refund->getId(), $reason );

			$order->add_order_note( $refund_message );
			wcfm_wirecard_log( 'Success: ' . html_entity_decode( wp_strip_all_tags( $refund_message ) ) );
			
			return $refund->getId();
		
		} catch (Exception $ex) {
			wcfm_wirecard_log( "Wirecard Refund not precessed :: " . $ex->__toString(), 'error' );
			wc_add_notice( __( "Wirecard Refund not precessed :: ", 'wc-multivendor-marketplace') . $ex->__toString(), 'error');
			return false;
		}
		
		return false;
	}

	
	/**
	 * Add cpf field in the checkout page
	 *
	 * @param array $fileds
	 * @param string $id
	 *
	 * @return array
	 */
	public function add_cpf_field( $fields, $id ) {
		if ( $this->id == $id ) {
				$fields['cpf_field'] = '<p class="form-row form-row-wide">
						<label for="billing_wirecard_cpf" class="">' . esc_html__( 'CPF Number', 'wc-multivendor-marketplace' ) . '&nbsp;<span class="optional"><span style="color:red">*</span></span></label>

						<input type="number" minlength="11" maxlength="11" style="padding: 10px; font-size:16px" class="input-text" name="billing_wirecard_cpf" id="billing_wirecard_cpf" placeholder="' . esc_html__( 'CPF Number', 'wc-multivendor-marketplace' ) . '">
				</p>';
		}

		return $fields;
	}

	/**
	 * Include all the scripts
	 *
	 * @return void
	 */
	public function include_wirecard_js() {
		global $WCFM, $WCFMmp, $woocommerce, $wp;
		
		if ( ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
				return;
		}
		
		$script_path = $WCFMmp->plugin_url . 'assets/js/gateway/';
		$script_path = str_replace(array('http:', 'https:'), '', $script_path);

		wp_enqueue_script( 'wcfmmp-wirecard', $script_path . 'wirecard.js' , array(), false, false );

		$wcfmmp_wirecard_params = array(
				'public_key'   => $this->public_key,
				'card_error'   => __( 'Card number is not valid', 'wc-multivendor-marketplace' ),
				'expriy_error' => __( 'Card expriy date is not valid', 'wc-multivendor-marketplace' ),
				'cvc_error'    => __( 'Card CVC number is not valid', 'wc-multivendor-marketplace' ),
		);

		wp_enqueue_script( 'wcfmmp-wirecard-checkout', $script_path . 'wirecard-checkout.js' , array( 'jquery' ), false, false );
		wp_localize_script( 'wcfmmp-wirecard', 'wcfmmp_wirecard_params', $wcfmmp_wirecard_params );
	}

	/**
	 * Payment form on checkout page
	 *
	 * @return void
	 */
	public function payment_fields() {
		?>
		<fieldset>
			<?php
			if ( $this->description ) {
					echo wpautop( esc_html( $this->description ) );
			}
			if ( $this->is_testmode == 'yes' ) {
					echo '<p>' . __( 'TEST MODE ENABLED. In test mode, you can use the card number 4012001037141112 with any CVC and a valid expiration date.', 'wc-multivendor-marketplace' ) . '</p>';
			}
			?>
			<p class="form-row form-row-wide">

				<?php
					$cc_form = new WC_Payment_Gateway_CC;
					$cc_form->id       = $this->id;
					$cc_form->supports = $this->supports;
					$cc_form->form();
				?>
			</p>
			<div class="clear"></div>
		</fieldset>
		<?php
	}
	
	function wcfm_vendor_wirecard_settings_payment( $user_id ) {
		global $WCFM, $WCFMmp;
		
		$wcfm_marketplace_withdrwal_payment_methods = get_wcfm_marketplace_active_withdrwal_payment_methods();
		
		$vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
		
		if( array_key_exists( 'wirecard', $wcfm_marketplace_withdrwal_payment_methods ) && apply_filters( 'wcfm_is_allow_billing_wirecard', true ) ) { ?>
			<div class="paymode_field paymode_wirecard">
				<?php
				$testmode            = isset( $WCFMmp->wcfmmp_withdrawal_options['test_mode'] ) ? true : false;
				$wirecard_token      = $testmode ? sanitize_text_field( $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_token'] ) : sanitize_text_field( $WCFMmp->wcfmmp_withdrawal_options['wirecard_token'] );
				$wirecard_key        = $testmode ? sanitize_text_field( $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_key'] ) : sanitize_text_field( $WCFMmp->wcfmmp_withdrawal_options['wirecard_key'] );
				$wirecard_public_key = $testmode ? sanitize_text_field( $WCFMmp->wcfmmp_withdrawal_options['wirecard_test_public_key'] ) : sanitize_text_field( $WCFMmp->wcfmmp_withdrawal_options['wirecard_public_key'] );
				$wirecard_app_id     = get_option( 'wcfmmp_wirecard_app_id' );
				$wirecard_secret     = get_option( 'wcfmmp_wirecard_secret' );
				$access_token        = get_option( 'wcfmmp_wirecard_access_token' );
				$redirect_uri        = add_query_arg( 'marketplace_wirecard', 'yes', get_wcfm_settings_url() );
				
				if (!empty($wirecard_token) && !empty($wirecard_key) && !empty($wirecard_public_key)) {
					
					if ( isset( $_GET['disconnect_wirecard'] ) && $_GET['disconnect_wirecard'] == 'disconnect_wirecard' ) {
						delete_user_meta( $user_id, 'vendor_wirecard_token' );
						delete_user_meta( $user_id, 'vendor_wirecard_account' );
						
						$vendor_data['payment']['method'] = '';
						update_user_meta( $user_id, 'wcfmmp_profile_settings', $vendor_data );
						?>
						<script>
							window.location =  '<?php echo get_wcfm_settings_url() . '#wcfm_settings_form_payment_head'; ?>';
						</script>
						<?php
					}
					
					if ( isset( $_GET['marketplace_wirecard'] ) && $_GET['marketplace_wirecard'] == 'yes' ) {
						if ( isset( $_GET['code'] ) && !empty( $_GET['code'] ) ) {
							if ( $testmode ) {
								$connect = new Connect( $redirect_uri, $wirecard_app_id, true, Connect::ENDPOINT_SANDBOX );
							} else {
								$connect = new Connect( $redirect_uri, $wirecard_app_id, true, Connect::ENDPOINT_PRODUCTION );
							}
							
							$connect->setClientSecret( $wirecard_secret );
							$connect->setCode( wc_clean( $_GET['code'] ) );
			
							$authorize = $connect->authorize();
			
							$vendor_wirecard_token   = $authorize->access_token;
							$vendor_wirecard_account = $authorize->moipAccount->id;
			
							update_user_meta( get_current_user_id(), 'vendor_wirecard_token', $vendor_wirecard_token );
							update_user_meta( get_current_user_id(), 'vendor_wirecard_account', $vendor_wirecard_account );
							
							$vendor_data['payment']['method'] = 'wirecard';
							update_user_meta( $user_id, 'wcfmmp_profile_settings', $vendor_data );
							?>
							<script>
								window.location =  '<?php echo get_wcfm_settings_url() . '#wcfm_settings_form_payment_head'; ?>';
							</script>
							<?php
						}
					}
					
					
					$vendor_wirecard_token   = get_user_meta( $user_id, 'vendor_wirecard_token', true );
					$vendor_wirecard_account = get_user_meta( $user_id, 'vendor_wirecard_account', true );
					if( empty( $vendor_wirecard_account ) ) {
						if ( $testmode ) {
							$wirecard = new Moip( new BasicAuth( $wirecard_token, $wirecard_key ), Moip::ENDPOINT_SANDBOX );
						} else {
							$wirecard = new Moip( new BasicAuth( $wirecard_token, $wirecard_key ), Moip::ENDPOINT_PRODUCTION );
						}

						// Now it's time to create a URL then redirect your user to ask him permissions to create projects in his name
						if ( $testmode ) {
							$connect = new Connect( $redirect_uri, $wirecard_app_id, true, Connect::ENDPOINT_SANDBOX );
						} else {
							$connect = new Connect( $redirect_uri, $wirecard_app_id, true, Connect::ENDPOINT_PRODUCTION );
						}

						$connect->setScope(Connect::RECEIVE_FUNDS)
								->setScope(Connect::REFUND)
								->setScope(Connect::MANAGE_ACCOUNT_INFO)
								->setScope(Connect::RETRIEVE_FINANCIAL_INFO);

						$wirecard_connect_url = $connect->getAuthUrl();
						
						?>
						<div class="clear"></div>
							<div class="wcfmmp_wirecard_connect">
								<table class="form-table">
									<tbody>
										<tr>
											<th style="width: 35%;">
												<label><?php _e('Wirecard', 'wc-frontend-manager'); ?></label>
											</th>
											<td><?php _e('You are not connected with Wirecard.', 'wc-frontend-manager'); ?></td>
										</tr>
										<tr>
											<th></th>
											<td>
												<a href=<?php echo $wirecard_connect_url; ?> target="_self"><?php _e('Connect Wirecard Account', 'wc-multivendor-marketplace'); ?></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<?php
					} else {
						?>
						<div class="clear"></div>
						<div class="wcfmmp_wirecard_connect">
							<table class="form-table">
								<tbody>
									<tr>
										<th style="width: 35%;">
												<label><?php _e('Wirecard', 'wc-frontend-manager'); ?></label>
										</th>
										<td>
												<label><?php _e('You are connected with Wirecard', 'wc-frontend-manager'); ?></label>
										</td>
									</tr>
									<tr>
										<th></th>
										<td>
											<a class="wcfm_submit_button" style="float:none;" href=<?php echo add_query_arg( 'disconnect_wirecard', 'disconnect_wirecard', get_wcfm_settings_url() ); ?> target="_self"><?php _e('Disconnect Wirecard Account', 'wc-multivendor-marketplace'); ?></a>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php
					}
				} else {
					_e('Wirecard not setup properly, please contact your site admin.', 'wc-frontend-manager');
				}
				?>
			</div>
		<?php }
	}

}