<?php

class wcfmvm_paypal_ipn_handler {

	var $last_error;                 // holds the last error encountered
	var $ipn_log = false;            // bool: log IPN results to text file?
	var $ipn_response;               // holds the IPN response from paypal
	var $ipn_data = array();         // array contains the POST values for IPN
	var $fields = array();           // array holds the fields to submit to paypal
	var $sandbox_mode = false;

	function __construct() {
		$this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
		$this->last_error = '';
		$this->ipn_response = '';
		
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
 		$membership_payment_settings = array();
		if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
		$paypal_email = ( $membership_payment_settings['paypal_email'] ) ? $membership_payment_settings['paypal_email'] : '';
		$this->sandbox_mode = isset( $membership_payment_settings['paypal_sandbox'] ) ? true : false;
	}

  function wcfmvm_validate_and_create_membership() {
  	global $WCFM, $WCFMvm, $wpdb;
  	
		// Check Product Name , Price , Currency , Receivers email ,
		$error_msg = "";

		// Read the IPN and validate
		$gross_total = $this->ipn_data['mc_gross'];
		$transaction_type = $this->ipn_data['txn_type'];
		$txn_id = $this->ipn_data['txn_id'];        
		$payment_status = $this->ipn_data['payment_status'];
			
		//Check payment status
		if (!empty($payment_status)) {
			if ($payment_status == "Denied") {
				wcfmvm_create_log("Payment status for this transaction is DENIED. You denied the transaction... most likely a cancellation of an eCheque. Nothing to do here.");
				return false;
			}
			if ($payment_status == "Canceled_Reversal") {
				wcfmvm_create_log("This is a dispute closed notification in your favour. The plugin will not do anyting.");
				return true;
			}
			if ($payment_status != "Completed" && $payment_status != "Processed" && $payment_status != "Refunded" && $payment_status != "Reversed") {
				$error_msg .= 'Funds have not been cleared yet. Transaction will be processed when the funds clear!';
				wcfmvm_create_log($error_msg);
				return false;
			}
		}

		//Check txn type
		if ($transaction_type == "new_case") {
			wcfmvm_create_log('This is a dispute case. Nothing to do here.');
			return true;
		}
			
		$member_id = urldecode($this->ipn_data['custom']);
		$this->ipn_data['custom'] = $member_id;
		wcfmvm_create_log('Member ID: ' . $member_id);
			
		//Handle refunds
		if ( $gross_total < 0 ) {
			// This is a refund or reversal
			wcfmvm_create_log('This is a refund notification. Refund amount: '.$gross_total);
			return true;
		}
		if (isset($this->ipn_data['reason_code']) && $this->ipn_data['reason_code'] == 'refund') {
			wcfmvm_create_log('This is a refund notification. Refund amount: '.$gross_total);
			return true;            
		}

		if (($transaction_type == "subscr_signup")) {
			wcfmvm_create_log('Subscription signup IPN received... (handled by the subscription IPN handler)');

			$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
			if( $wcfm_membership ) {
				update_user_meta( $member_id, 'wcfm_membership_paymode', 'paypal' );
				update_user_meta( $member_id, 'wcfm_paypal_subscription_id', $this->ipn_data['subscr_id'] );
				$required_approval = get_post_meta( $wcfm_membership, 'required_approval', true ) ? get_post_meta( $wcfm_membership, 'required_approval', true ) : 'no';
				if( $required_approval != 'yes' ) {
					$WCFMvm->register_vendor( $member_id );
				} else {
					$wcfm_is_send_approval_reminder_admin = get_user_meta( $member_id, 'wcfm_is_send_approval_reminder_admin', true );
					if( !$wcfm_is_send_approval_reminder_admin ) {
						$WCFMvm->send_approval_reminder_admin( $member_id );
						update_user_meta( $member_id, 'wcfm_is_send_approval_reminder_admin', 'yes' );
					}
				}
			}
			$WCFMvm->store_subscription_data( $member_id, 'paypal_subs', $this->ipn_data['subscr_id'], $transaction_type, 'Completed', $this->post_string );
			
			//wcfmvm_handle_subsc_signup_stand_alone( $member_id, $this->ipn_data );
			return true;
		} else if (($transaction_type == "subscr_cancel") || ($transaction_type == "subscr_eot") || ($transaction_type == "subscr_failed")) {
			// Code to handle the IPN for subscription cancellation
			wcfm_log('Subscription cancellation PayPal IPN received...');
			wcfm_log( "Membership Expiry by PayPal :: " . $member_id . " <=> " . $wcfm_membership_id . " <=> " . $transaction_type );
			$WCFMvm->wcfmvm_vendor_membership_cancel( $member_id, $wcfm_membership_id );
			$WCFMvm->store_subscription_data( $member_id, 'paypal_subs', $this->ipn_data['subscr_id'], $transaction_type, 'Cancelled', $this->post_string );
			return true;
		} else {
			$cart_items = array();
			wcfmvm_create_log('Transaction Type: Buy Now/Subscribe');
			$item_number = $this->ipn_data['item_number'];
			$item_name = $this->ipn_data['item_name'];
			$quantity = $this->ipn_data['quantity'];
			$mc_gross = $this->ipn_data['mc_gross'];
			$mc_currency = $this->ipn_data['mc_currency'];

			$current_item = array(
					'item_number' => $item_number,
					'item_name' => $item_name,
					'quantity' => $quantity,
					'mc_gross' => $mc_gross,
					'mc_currency' => $mc_currency,
			);

			array_push($cart_items, $current_item);
			
			$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
			if( $wcfm_membership ) {
				update_user_meta( $member_id, 'wcfm_membership_paymode', 'paypal' );
				$required_approval = get_post_meta( $wcfm_membership, 'required_approval', true ) ? get_post_meta( $wcfm_membership, 'required_approval', true ) : 'no';
				if( $required_approval != 'yes' ) {
					$WCFMvm->register_vendor( $member_id );
				} else {
					$wcfm_is_send_approval_reminder_admin = get_user_meta( $member_id, 'wcfm_is_send_approval_reminder_admin', true );
					if( !$wcfm_is_send_approval_reminder_admin ) {
						$WCFMvm->send_approval_reminder_admin( $member_id );
						update_user_meta( $member_id, 'wcfm_is_send_approval_reminder_admin', 'yes' );
					}
				}
			}
			$WCFMvm->store_subscription_data( $member_id, 'paypal', $txn_id, $transaction_type, $payment_status, $this->post_string );
		}

		/*** Do Post payment operation and cleanup ***/
		//Save the transaction data
		wcfmvm_create_log('Saving transaction data to the database table.');
		$this->ipn_data['gateway'] = 'paypal';
		$this->ipn_data['status'] = $this->ipn_data['payment_status'];
		wcfmvm_create_log('Transaction data saved.');
		
		//Trigger the PayPal IPN processed action hook (so other plugins can can listen for this event).
		do_action('wcfmvm_paypal_ipn_processed', $this->ipn_data);
		
		do_action('wcfmvm_payment_ipn_processed', $this->ipn_data);
						
		return true;
	}

	function wcfmvm_validate_ipn() {
		//Generate the post string from the _POST vars aswell as load the _POST vars into an arry
		$post_string = '';
		foreach ($_POST as $field=>$value) {
			$this->ipn_data["$field"] = $value;
			$post_string .= $field.'='.urlencode(stripslashes($value)).'&';
		}

		$this->post_string = $post_string;
		wcfmvm_create_log('Post string : '. $this->post_string);

		//IPN validation check
		if($this->validate_ipn_using_remote_post()) {
			//We can also use an alternative validation using the validate_ipn_using_curl() function
			return true;
		} else {
			return false;
		}
  }

	function validate_ipn_using_remote_post() {
		wcfmvm_create_log( 'Checking if PayPal IPN response is valid');
		
		// Get received values from post data
		$validate_ipn = array( 'cmd' => '_notify-validate' );
		$validate_ipn += wp_unslash( $_POST );

		// Send back post vars to paypal
		$params = array(
						'body'        => $validate_ipn,
						'timeout'     => 60,
						'httpversion' => '1.1',
						'compress'    => false,
						'decompress'  => false,
						'user-agent'  => 'WCFM - WooCommerce Multivendor Membership',
		);

		// Post back to get a response.
		$connection_url = $this->sandbox_mode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		wcfmvm_create_log('Connecting to: ' . $connection_url);
		$response = wp_safe_remote_post( $connection_url, $params );

		//The following two lines can be used for debugging
		//wcfmvm_create_log( 'IPN Request: ' . print_r( $params, true ) , true);
		//wcfmvm_create_log( 'IPN Response: ' . print_r( $response, true ), true);

		// Check to see if the request was valid.
		if ( ! is_wp_error( $response ) && strstr( $response['body'], 'VERIFIED' ) ) {
			wcfmvm_create_log('IPN successfully verified.');
			return true;
		}

		// Invalid IPN transaction. Check the log for details.
		wcfmvm_create_log('IPN validation failed.', false);
		if ( is_wp_error( $response ) ) {
			wcfmvm_create_log('Error response: ' . $response->get_error_message());
		}
		return false;        
	}
}

// Start of IPN handling (script execution)
$ipn_handler_instance = new wcfmvm_paypal_ipn_handler();

// Validate the IPN
if ($ipn_handler_instance->wcfmvm_validate_ipn()) {
	wcfmvm_create_log('Creating product Information to send.');

	if(!$ipn_handler_instance->wcfmvm_validate_and_create_membership()) {
		wcfmvm_create_log('IPN product validation failed.');
	}
}
wcfmvm_create_log('Paypal class finished.');