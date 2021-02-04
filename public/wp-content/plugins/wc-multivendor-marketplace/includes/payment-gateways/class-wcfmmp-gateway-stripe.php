<?php

if (!defined('ABSPATH')) {
    return;
}
use Stripe\Stripe;
use Stripe\Transfer;

class WCFMmp_Gateway_Stripe extends WCFMmp_Abstract_Gateway {

	public $id;
	public $gateway_title;
	public $payment_gateway;
	public $message = array();
	
	private $client_id;
	private $client_secret;
	private $published_key;
	
	private $is_testmode = false;
	private $debug = false;
	private $payout_mode = 'true';
	private $reciver_email;
	
	private $api_endpoint;
	private $token_endpoint;
	private $access_token;
	private $token_type;

	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$this->id = 'stripe';
		$this->gateway_title = __('Stripe connect', 'wc-multivendor-marketplace');
		$this->payment_gateway = $this->id;
		
		$withdrawal_test_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['test_mode'] ) ? 'yes' : 'no';
		
		$this->client_id = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_client_id'] : '';
		$this->published_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_published_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_published_key'] : '';
		$this->secret_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_secret_key'] : '';
		
		if ( $withdrawal_test_mode == 'yes') {
			$this->is_testmode = true;
			$this->client_id = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_test_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_client_id'] : '';
			$this->published_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_test_published_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_published_key'] : '';
			$this->secret_key = isset( $WCFMmp->wcfmmp_withdrawal_options['stripe_test_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_secret_key'] : '';
		}
		
		$this->debug = $this->is_testmode;
		
		if( !class_exists("Stripe\Stripe") ) {
			require_once( $WCFMmp->plugin_path . 'includes/Stripe/init.php' );
		}
		
	}
	
	public function gateway_logo() { global $WCFMmp; return $WCFMmp->plugin_url . 'assets/images/'.$this->id.'.png'; }

	public function process_payment( $withdrawal_id, $vendor_id, $withdraw_amount, $withdraw_charges, $transaction_mode = 'auto', $args = array() ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$this->withdrawal_id = absint($withdrawal_id);
		$this->vendor_id = $vendor_id;
		$this->withdraw_amount = $withdraw_amount;
		$this->currency = get_woocommerce_currency();
		$this->transaction_mode = $transaction_mode;
		$this->is_connected = get_user_meta($this->vendor_id, 'vendor_connected', true);
		$this->stripe_user_id = get_user_meta($this->vendor_id, 'stripe_user_id', true);
		
		if ( $this->validate_request() ) {
			$transfer = $this->process_stripe_payment( $args );
			if( $transfer ) {
				$transfer_data = $transfer->jsonSerialize();
				
				// Updating Vendor Transaction ID at Order Meta
				if( isset( $transfer_data['id'] ) && !empty( $transfer_data['id'] ) ) {
					$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wcfm_marketplace_withdraw_request';
					$sql .= ' WHERE 1=1';
					$sql .= " AND ID = " . $this->withdrawal_id;
					$withdrawal_infos = $wpdb->get_results( $sql );
					if( !empty( $withdrawal_infos ) ) {
						foreach( $withdrawal_infos as $withdrawal_info ) {
							$wi_order_ids  = explode( ",", $withdrawal_info->order_ids );
							if( !empty( $wi_order_ids ) ) {
								foreach( $wi_order_ids as $wi_order_id ) {
									$order = wc_get_order( $wi_order_id );
									if( is_a( $order, 'WC_Order' ) ) {
										$order->update_meta_data( 'wcfmmp_stripe_split_pay_transaction_id_'.$this->vendor_id, $transfer_data['id'] );
										$order->save();
									}
								}
							}
						}
					}
				}
				
				return array( 'status' => true, 'message' => __('New transaction has been initiated', 'wc-multivendor-marketplace'), 'transfer_data' => $transfer );
			} else {
				return false;
			}
		} else {
			return $this->message;
		}
	}

	public function validate_request() {
		global $WCFM, $WCFMmp;
		if( !$this->is_connected && !$this->stripe_user_id ) {
			$this->message[] = array( 'status' => false, 'message' => __('Please connect with Stripe account', 'wc-multivendor-marketplace') );
			return false;
		} else if( !$this->secret_key ) {
			$this->message[] = array( 'status' => false, 'message' => __('Stripe setting is not configured properly please contact site administrator', 'wc-multivendor-marketplace') );
			return false;
		}
		return parent::validate_request();
	}

	private function process_stripe_payment( $args = array() ) {
		global $WCFM, $WCFMmp;
		try {
			Stripe::setApiKey($this->secret_key);
			$transfer_args = array(
					'amount'              => $this->get_stripe_amount(),
					'currency'            => $this->currency,
					'destination'         => $this->stripe_user_id,
					'description'         => __('Payout for withdrawal ID #', 'wc-multivendor-marketplace') . sprintf( '%06u', $this->withdrawal_id )
			);
			if( $this->transaction_mode == 'manual' ) {
				$transfer_args['transfer_group'] = __('Payout for withdrawal ID #', 'wc-multivendor-marketplace') . sprintf( '%06u', $this->withdrawal_id );
			}
			$transfer_args = wp_parse_args($args, $transfer_args);
			$transfer = Transfer::create($transfer_args);
			$result_array = $transfer->jsonSerialize();
			
			// Updating withdrawal meta
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'withdraw_amount', $this->withdraw_amount );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'currency', $this->currency );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'transaction_id', $result_array['id'] );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'transaction_ref', $result_array['balance_transaction'] );
			
			if ($this->debug)
				wcfm_stripe_log( sprintf( '#%s - Stripe payment processing success: %s', sprintf( '%06u', $this->withdrawal_id ), $transfer ), 'info' );
			return $transfer;
		} catch (\Stripe\Error\InvalidRequest $e) {
			wcfm_stripe_log( sprintf( '#%s - Stripe payment processing failed: %s', sprintf( '%06u', $this->withdrawal_id ), $e->getMessage() ), 'error' );
			return false;
		} catch (\Stripe\Error\Authentication $e) {
			wcfm_stripe_log( sprintf( '#%s - Stripe payment processing failed: %s', sprintf( '%06u', $this->withdrawal_id ), $e->getMessage() ), 'error' );
			return false;
		} catch (\Stripe\Error\ApiConnection $e) {
			wcfm_stripe_log( sprintf( '#%s - Stripe payment processing failed: %s', sprintf( '%06u', $this->withdrawal_id ), $e->getMessage() ), 'error' );
			return false;
		} catch (\Stripe\Error\Base $e) {
			wcfm_stripe_log( sprintf( '#%s - Stripe payment processing failed: %s', sprintf( '%06u', $this->withdrawal_id ), $e->getMessage() ), 'error' );
			return false;
		} catch (Exception $e) {
			wcfm_stripe_log( sprintf( '#%s - Stripe payment processing failed: %s', sprintf( '%06u', $this->withdrawal_id ), $e->getMessage() ), 'error' );
			return false;
		}
		return false;
	}
	
	private function get_stripe_amount() {
		switch( strtoupper( $this->currency ) ) {
			// Zero decimal currencies.
			case 'BIF' :
			case 'CLP' :
			case 'DJF' :
			case 'GNF' :
			case 'JPY' :
			case 'KMF' :
			case 'KRW' :
			case 'MGA' :
			case 'PYG' :
			case 'RWF' :
			case 'VND' :
			case 'VUV' :
			case 'XAF' :
			case 'XOF' :
			case 'XPF' :
				$amount_to_pay = absint( $this->withdraw_amount );
				break;
			default :
				$amount_to_pay = round( $this->withdraw_amount, 2 ) * 100; // In cents.
				break;
		}
		return $amount_to_pay;
	}
}