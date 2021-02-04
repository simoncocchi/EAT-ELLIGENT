<?php

class wcfm_stripe_subs_ipn_handler {
	
	var $stripe_published_key_live;
	var $stripe_secret_key_live;
	var $stripe_published_key_test;
	var $stripe_secret_key_test;
	var $sandbox_mode = false;
	
	public function __construct() {
			
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
		$membership_payment_settings = array();
		if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
		$this->stripe_published_key_live = isset( $membership_payment_settings['stripe_published_key_live'] ) ? $membership_payment_settings['stripe_published_key_live'] : '';
		$this->stripe_secret_key_live = isset( $membership_payment_settings['stripe_secret_key_live'] ) ? $membership_payment_settings['stripe_secret_key_live'] : '';
		$this->stripe_published_key_test = isset( $membership_payment_settings['stripe_published_key_test'] ) ? $membership_payment_settings['stripe_published_key_test'] : '';
		$this->stripe_secret_key_test = isset( $membership_payment_settings['stripe_secret_key_test'] ) ? $membership_payment_settings['stripe_secret_key_test'] : '';
		$this->sandbox_mode = isset( $membership_payment_settings['paypal_sandbox'] ) ? true : false;
		
		$this->handle_stripe_ipn();
	}
	
	public function handle_stripe_ipn(){
		global $WCFM, $WCFMvm, $wpdb;
		
		wcfmvm_create_log("Stripe Buy Now IPN received. Processing request...");
		
		//Include the Stripe library.
		if( !class_exists( 'Stripe\Stripe' ) ) {
			include( $WCFMvm->plugin_path . 'includes/libs/stripe-gateway/init.php');
		}
		
		//Read and sanitize the request parameters.
		$membership_id = sanitize_text_field($_REQUEST['item_number']);
		$membership_id = absint($membership_id);
		$button_title = sanitize_text_field($_REQUEST['item_name']);
		
		$payment_amount = sanitize_text_field($_REQUEST['item_price']);
		$currency_code = sanitize_text_field($_REQUEST['currency_code']);
		$zero_cents_currency = array('JPY', 'MGA', 'VND', 'KRW');
		if (in_array(get_woocommerce_currency(), $zero_cents_currency)) {
			$price_in_cents = $payment_amount;
		} else {
			$price_in_cents = $payment_amount * 100; //The amount (in cents). This value is passed to Stripe API.
		}
		
		$stripe_token = sanitize_text_field($_POST['stripeToken']);
		$stripe_token_type = sanitize_text_field($_POST['stripeTokenType']);
		$stripe_email = sanitize_email($_POST['stripeEmail']);
			
		//Retrieve the CPT for this button
		$button_cpt = get_post($membership_id); 
		if(!$button_cpt){
			//Fatal error. Could not find this payment button post object.
			wcfmvm_create_log("Fatal Error! Failed to retrieve the membership post object for the given Membership ID: ". $membership_id);
			wp_die("Fatal Error! Membership (ID: ".$membership_id.") does not exist. This request will fail.");
		}
		
		$subscription = (array) get_post_meta( $membership_id, 'subscription', true );
		$plan_id = isset( $subscription['stripe_plan_id'] ) ? $subscription['stripe_plan_id'] : '';
    $descr = 'Subscription to "' . $plan_id . '" plan';
			
		//Validation passed. Go ahead with the charge.
		
		//Sandbox and other settings
		if( $this->sandbox_mode ) {
			wcfmvm_create_log("Sandbox payment mode is enabled. Using test API key details.");
			$secret_key = $this->stripe_secret_key_test; //Use sandbox API key
		} else {
			$secret_key = $this->stripe_secret_key_live; //Use live API key
		}

		//Set secret API key in the Stripe library
		\Stripe\Stripe::setApiKey($secret_key);
		
		// Get the credit card details submitted by the form
		$token = $stripe_token;
		
		// Create trial amount the charge on Stripe's servers - this will charge the user's card
		/*if( $price_in_cents ) {
			try {
					$charge = \Stripe\Charge::create(array(
									"amount" => $price_in_cents, //Amount in cents
									"currency" => strtolower($currency_code),
									"source" => $token,
									"description" => $button_title,
									"receipt_email" => $stripe_email,
					));
			} catch(\Stripe\Error\Card $e) {
					// The card has been declined
					wcfmvm_create_log("Stripe Charge Error! The card has been declined. ".$e->getMessage());
					$body = $e->getJsonBody();
					$error  = $body['error'];
					$error_string = print_r($error,true);
					wcfmvm_create_log("Error details: ".$error_string);
					wp_die("Stripe Charge Error! Card charge has been declined. " . $e->getMessage() . $error_string);
			}
		}*/
		
		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			$customer = \Stripe\Customer::create(array(
									'description' => $descr,
									'email' => $stripe_email,
									'source' => $token,
									'plan' => $plan_id,
			));
		} catch (Exception $e) {
			wcfmvm_create_log( "Error occured during Stripe Subscribe. " . $e->getMessage() );
			$body = $e->getJsonBody();
			$error = $body['error'];
			$error_string = print_r($error, true);
			wcfmvm_create_log( "Error details: " . $error_string );
			wp_die("Stripe Subscription Error! " . $e->getMessage() . $error_string);
		}

		//Everything went ahead smoothly with the charge.
		wcfmvm_create_log("Stripe Subscription successful.");
		
		//let's add button_id to metadata
		$customer->metadata = array('membership_id' => $membership_id);
		try {
			$customer->save();
		} catch (Exception $e) {
			wcfmvm_create_log("Error occured during Stripe customer metadata update. " . $e->getMessage());
			$body = $e->getJsonBody();
			wcfmvm_create_log("Error details: " . $error_string);
		}
			
		//Grab the charge ID and set it as the transaction ID.
		$txn_id = $customer->id;//$charge->balance_transaction;
		//Grab subscription ID
		$subscr_id = $customer->subscriptions->data[0]->id;
		//The charge ID can be used to retrieve the transaction details using hte following call.
		//\Stripe\Charge::retrieve($charge->id);
		$member_id = sanitize_text_field($_REQUEST['custom']);
		
		$wcfm_membership = get_user_meta( $member_id, 'temp_wcfm_membership', true );
		if( $wcfm_membership ) {
			update_user_meta( $member_id, 'wcfm_membership_paymode', 'stripe' );
			update_user_meta( $member_id, 'wcfm_stripe_subscription_id', $subscr_id );
			$required_approval = get_post_meta( $wcfm_membership, 'required_approval', true ) ? get_post_meta( $wcfm_membership, 'required_approval', true ) : 'no';
			if( $required_approval != 'yes' ) {
				$WCFMvm->register_vendor( $member_id );
				$WCFMvm->store_subscription_data( $member_id, 'stripe', $txn_id, 'stripe_reccuring_subscription', 'Completed', $token );
				$WCFMvm->store_subscription_data( $member_id, 'stripe_subs', $subscr_id, 'stripe_reccuring_subscription', 'Completed', $token );
			} else {
				$WCFMvm->send_approval_reminder_admin( $member_id );
				$WCFMvm->store_subscription_data( $member_id, 'stripe', $txn_id, 'stripe_reccuring_subscription', 'Completed', $token );
				$WCFMvm->store_subscription_data( $member_id, 'stripe_subs', $subscr_id, 'stripe_reccuring_subscription', 'Completed', $token );
			}
		}
		
		// Reset Membership Session
		if( WC()->session && WC()->session->get( 'wcfm_membership' ) ) {
			WC()->session->__unset( 'wcfm_membership' );
		}
		
		wcfmvm_create_log('Transaction data saved.');
		
		// Trigger the stripe IPN processed action hook (so other plugins can can listen for this event).
		do_action('wcfmvm_stripe_ipn_processed', $token);
		
		do_action('wcfmvm_payment_ipn_processed', $token);
		
		// Redirect the user to the return URL (or to the homepage if a return URL is not specified for this payment button).
		$return_url = apply_filters( 'wcfm_registration_thankyou_url', add_query_arg( 'vmstep', 'thankyou', get_wcfm_membership_url() ) );
		wcfmvm_create_log("Redirecting customer to: ".$return_url);
		wcfmvm_create_log("End of Stripe Buy Now IPN processing.");
		wp_safe_redirect( $return_url );
	}
}

$wcfm_stripe_subs_ipn = new wcfm_stripe_subs_ipn_handler();