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
		
		wcfmvm_create_log("Stripe SCA Subs IPN received. Processing request...");
		
		//Include the Stripe library.
		if( !class_exists( 'Stripe\Stripe' ) ) {
			include( $WCFMvm->plugin_path . 'includes/libs/stripe-gateway/init.php');
		}
		
		$ref_id = filter_input( INPUT_GET, 'ref_id', FILTER_SANITIZE_STRING );

		if ( empty( $ref_id ) ) {
			//no ref id provided, cannot proceed
			wcfmvm_create_log( 'Fatal Error! No ref_id provied.', false );
			wp_die( esc_html( 'Fatal Error! No ref_id provied.' ) );

		}
		
		$token = '';

		$trans_info = explode( '|', $ref_id );
		
		//Read and sanitize the request parameters.
		$membership_id = isset( $trans_info[1] ) ? absint( $trans_info[1] ) : false;
		$membership_id = absint($membership_id);
		
		$member_id = isset( $trans_info[2] ) ? absint( $trans_info[2] ) : false;
		
		if( $membership_id && $member_id ) {
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
	
			try {
				//Set secret API key in the Stripe library
				\Stripe\Stripe::setApiKey($secret_key);
	
				$events = \Stripe\Event::all(
					array(
						'type'    => 'checkout.session.completed',
						'created' => array(
							'gte' => time() - 60 * 60,
						),
					)
				);
	
				$sess = false;
	
				foreach ( $events->autoPagingIterator() as $event ) {
					$session = $event->data->object;
					if ( isset( $session->client_reference_id ) && $session->client_reference_id === $ref_id ) {
						$sess = $session;
						break;
					}
				}
	
				if ( false === $sess ) {
					// Can't find session.
					$error_msg = sprintf( "Fatal error! Payment with ref_id %s can't be found", $ref_id );
					wcfmvm_create_log( $error_msg, false );
					wp_die( esc_html( $error_msg ) );
				}
	
				$subscr_id = $sess->subscription;
	
				$sub = \Stripe\Subscription::retrieve( $subscr_id );
				
				// Grab the charge ID and set it as the transaction ID.
				$txn_id = $sub->customer;
				
				
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
			} catch ( Exception $e ) {
				$error_msg = 'Error occurred: ' . $e->getMessage();
				wcfmvm_create_log( $error_msg, false );
				wp_die( esc_html( $error_msg ) );
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