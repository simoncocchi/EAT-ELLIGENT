<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCFMmp_Gateway_Paypal extends WCFMmp_Abstract_Gateway {

	public $id;
	public $gateway_title;
	public $payment_gateway;
	public $message = array();
	private $client_id;
	private $client_secret;
	private $is_testmode = false;
	private $payout_mode = 'true';
	private $reciver_email;
	private $api_endpoint;
	private $token_endpoint;
	private $access_token;
	private $token_type;

	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$this->id              = 'paypal';
		$this->gateway_title   = __('PayPal', 'wc-multivendor-marketplace');
		$this->payment_gateway = $this->id;
		$this->payout_mode     = 'false';
		
		$withdrawal_test_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['test_mode'] ) ? 'yes' : 'no';
		
		$this->api_endpoint = 'https://api.paypal.com/v1/payments/payouts?sync_mode='.$this->payout_mode;
		$this->token_endpoint = 'https://api.paypal.com/v1/oauth2/token';
		$this->client_id = isset( $WCFMmp->wcfmmp_withdrawal_options['paypal_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options['paypal_client_id'] : '';
		$this->client_secret = isset( $WCFMmp->wcfmmp_withdrawal_options['paypal_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['paypal_secret_key'] : '';
		
		if ( $withdrawal_test_mode == 'yes') {
			$this->is_testmode = true;
			$this->api_endpoint = 'https://api.sandbox.paypal.com/v1/payments/payouts?sync_mode='.$this->payout_mode;
			$this->token_endpoint = 'https://api.sandbox.paypal.com/v1/oauth2/token';
			$this->client_id = isset( $WCFMmp->wcfmmp_withdrawal_options['paypal_test_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options['paypal_test_client_id'] : '';
			$this->client_secret = isset( $WCFMmp->wcfmmp_withdrawal_options['paypal_test_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options['paypal_test_secret_key'] : '';
		}
	}
	
	public function gateway_logo() { global $WCFMmp; return $WCFMmp->plugin_url . 'assets/images/'.$this->id.'.png'; }

	public function process_payment( $withdrawal_id, $vendor_id, $withdraw_amount, $withdraw_charges, $transaction_mode = 'auto' ) {
		global $WCFM, $WCFMmp;
		
		$this->withdrawal_id = $withdrawal_id;
		$this->vendor_id = $vendor_id;
		$this->withdraw_amount = round( $withdraw_amount, 2 );
		$this->currency = get_woocommerce_currency();
		$this->transaction_mode = $transaction_mode;
		$this->reciver_email = $WCFMmp->wcfmmp_vendor->get_vendor_payment_account( $this->vendor_id, 'paypal' );
		if ($this->validate_request()) {
			$this->generate_access_token();
			$paypal_response = $this->process_paypal_payout();
			if ($paypal_response) {
				return array( 'status' => true, 'message' => __('New transaction has been initiated', 'wc-multivendor-marketplace') );
			} else {
				return false;
			}
		} else {
			return $this->message;
		}
	}

	public function validate_request() {
		global $WCFMmp;
		if (!$this->client_id || !$this->client_secret) {
			$this->message[] = array( 'status' => false, 'message' => __('PayPal Payout setting is not configured properly please contact site administrator', 'wc-multivendor-marketplace') );
			return false;
		} else if (!$this->reciver_email) {
			$this->message[] = array( 'status' => false, 'message' => __('Please update your PayPal email to receive commission', 'wc-multivendor-marketplace') );
			return false;
		}
		return parent::validate_request();
	}

	private function generate_access_token() {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Accept-Language: en_US'));
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $this->token_endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $this->client_id . ':' . $this->client_secret);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
		curl_setopt($curl, CURLOPT_SSLVERSION, 6);
		$response = curl_exec($curl);
		curl_close($curl);
		$response_array = json_decode($response, true);
		//wcfmmp_log( sprintf( '#%s - PayPal payment Access Token: %s %s %s', $this->withdrawal_id, json_encode($response_array) ) );
		$this->access_token = isset($response_array['access_token']) ? $response_array['access_token'] : '';
		$this->token_type = isset($response_array['token_type']) ? $response_array['token_type'] : '';
	}

	private function process_paypal_payout() {
		global $WCFM, $WCFMmp;
		$api_authorization = "Authorization: {$this->token_type} {$this->access_token}";
		$note = sprintf( __('Payment recieved from %1$s as commission at %2$s on %3$s', 'wc-multivendor-marketplace'), get_bloginfo('name'), date('H:i:s'), date('d-m-Y'));
		$request_params = '{
												"sender_batch_header": {
														"sender_batch_id":"' . uniqid() . '",
														"email_subject": "You have a payment",
														"recipient_type": "EMAIL"
												},
												"items": [
													{
														"recipient_type": "EMAIL",
														"amount": {
															"value": ' . $this->withdraw_amount . ',
															"currency": "' . $this->currency . '"
														},
														"receiver": "' . $this->reciver_email . '",
														"note": "' . $note . '",
														"sender_item_id": "' . $this->vendor_id . '"
													}
												]
											}';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type:application/json', $api_authorization));
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $this->api_endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request_params);
		curl_setopt($curl, CURLOPT_SSLVERSION, 6);
		$result = curl_exec($curl);
		curl_close($curl);
		$result_array = json_decode($result, true);
		$batch_status = $result_array['batch_header']['batch_status'];
		
		$batch_payout_status = apply_filters('wcfmmp_paypal_payout_batch_status', array('PENDING', 'PROCESSING', 'SUCCESS', 'NEW'));
		if (in_array($batch_status, $batch_payout_status) ) {
			// Updating withdrawal meta
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'withdraw_amount', $this->withdraw_amount );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'currency', $this->currency );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'reciver_email', $this->reciver_email );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'payout_batch_id', $result_array['batch_header']['payout_batch_id'] );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'batch_status', $batch_status );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'sender_batch_id', $result_array['batch_header']['sender_batch_header']['sender_batch_id'] );
			//wcfmmp_log( sprintf( '#%s - PayPal payment processing success: %s', $this->withdrawal_id, json_encode($result_array) ), 'info' );
			return $result_array;
		} else {
			wcfmmp_log( sprintf( '#%s - PayPal payment processing failed: %s', sprintf( '%06u', $this->withdrawal_id ), json_encode($result_array) ), 'error' );
			return false;
		}
  }
}