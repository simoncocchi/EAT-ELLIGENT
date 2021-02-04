<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCFMmp_Gateway_Bank_transfer extends WCFMmp_Abstract_Gateway {

	public $id;
	public $message = array();
	public $gateway_title;
	public $payment_gateway;

	public function __construct() {
		$this->id = 'bank_transfer';
		$this->gateway_title = __('Bank Transfer', 'wc-multivendor-marketplace');
		$this->payment_gateway = $this->id;
	}
	
	public function gateway_logo() { global $WCFMmp; return $WCFMmp->plugin_url . 'assets/images/'.$this->id.'.png'; }

	public function process_payment( $withdrawal_id, $vendor_id, $withdraw_amount, $withdraw_charges, $transaction_mode = 'auto' ) {
		global $WCFM, $WCFMmp;
		$this->withdrawal_id = $withdrawal_id;
		$this->vendor_id = $vendor_id;
		$this->withdraw_amount = $withdraw_amount;
		$this->currency = get_woocommerce_currency();
		$this->transaction_mode = $transaction_mode;
		$bank_details = $WCFMmp->wcfmmp_vendor->get_vendor_bank_details( $this->vendor_id );
		if ( $this->validate_request() ) {
			// Updating withdrawal meta
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'withdraw_amount', $this->withdraw_amount );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'currency', $this->currency );
			if( !empty( $bank_details ) ) {
				foreach( $bank_details as $bank_key => $bank_detail) {
					$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, $bank_key, $bank_detail );
				}
			}
			return array( 'status' => true, 'message' => __('New transaction has been initiated', 'wc-multivendor-marketplace') );
		} else {
			return $this->message;
		}
	}

	public function validate_request() {
		global $WCFMmp;
		
		return parent::validate_request();
	}
}
