<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class WCFMmp_Abstract_Gateway {
	
	public $payment_gateway;
	public $gateway_title = '';
	
	public $withdrawal_id;
	public $vendor_id;
	
	public $withdraw_amount = 0;
	
	public $currency;
	public $transaction_mode;

	public function gateway_logo() { global $WCFMmp; return $WCFMmp->plugin_url . 'assets/images/gateway_logo.png'; }
	
	public function validate_request() {
		return true;
	}

	public function process_payment( $withdrawal_id, $vendor_id, $withdraw_amount, $withdraw_charges, $transaction_mode = 'auto' ) {
		return array();
	}
}