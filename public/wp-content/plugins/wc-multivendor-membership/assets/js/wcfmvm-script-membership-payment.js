jQuery(document).ready(function($) {
		
	$('input[name=wcfm_subscription_paymode]').click(function() {
		var paymode = $('input[name=wcfm_subscription_paymode]:checked').val();
		$('.wcfm_payment_option_details').slideUp();
		$('.wcfm_membership_payment_form_non_free').slideUp();
		$('.wcfm_payment_option_'+paymode+'_deails').slideDown();
		$('#wcfm_membership_payment_form_'+paymode).slideDown();
	});
		
	// Membership Payment
	$('.wcfm_membership_payment_button').click(function(event) {
	  
	  // Validations
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
	  $wcfm_is_valid_form = true;
	  $( document.body ).trigger( 'wcfm_form_validate', $('.wcfm_membership_payment_form') );
	  $is_valid = $wcfm_is_valid_form;
	  
		$('#wcfm_membership_container').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
		$form_ajax_submit = false;
		var paymode = 'free';
		if( $('.wcfm_subscription_paymode').length > 0 ) {
			if( $('input[name=wcfm_subscription_paymode]').is(':checked') ) {
				paymode = $('input[name=wcfm_subscription_paymode]:checked').val();
				if( paymode == 'paypal' ) {
					$('#wcfm_membership_payment_form_paypal').submit();
				} else {
					event.preventDefault();
					$form_ajax_submit = true;
				}
			} else {
				event.preventDefault();
				alert( "Please select a payment option first." );
			}
		} else {
			event.preventDefault();
			$form_ajax_submit = true;
		}
		
		if( $is_valid && $form_ajax_submit ) {
			var data = {
				action                             : 'wcfm_ajax_controller',
				controller                         : 'wcfm-memberships-payment',
				wcfm_membership_payment_form       : $('.wcfm_membership_payment_form').serialize(),
				status                             : 'submit',
				paymode                            : paymode
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.status) {
						$('.wcfm_membership_payment_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );	
					} else {
						$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
						$('.wcfm_membership_payment_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_membership_container').unblock();
				}
			});
		} else {
			$('#wcfm_membership_container').unblock();
		}
	});
} );