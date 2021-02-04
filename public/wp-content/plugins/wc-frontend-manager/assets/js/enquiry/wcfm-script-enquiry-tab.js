$wcfm_enquiry_submited = false;

jQuery(document).ready(function($) {
		
	if( $('.enquiry-form').length > 1 ) {
		$('.enquiry-form')[1].remove();
	}
	
	$inquiryFormLoaded = false;
	if( $('.add_enquiry').length > 0 ) {
		loadInquiryForm();
	}
	if( $('.wcfm_catalog_enquiry').length > 0 ) {
		if( !$inquiryFormLoaded ) loadInquiryForm();
	}
	if( $('.wcfm_store_enquiry').length > 0 ) {
		if( !$inquiryFormLoaded ) loadInquiryForm();
	}
		
	function loadInquiryForm() {
		var data = {
			action  : 'wcfm_enquiry_form_content',
			store   : 0,
			product : 0
		}	
		
		jQuery.ajax({
			type    :		'POST',
			url     : wcfm_params.ajax_url,
			data    : data,
			success :	function(response) {
				$('body').append(response);
				
				$('#enquiry_form').find('.wcfm_datepicker').each(function() {
					$(this).datepicker({
						closeText: wcfm_datepicker_params.closeText,
						currentText: wcfm_datepicker_params.currentText,
						monthNames: wcfm_datepicker_params.monthNames,
						monthNamesShort: wcfm_datepicker_params.monthNamesShort,
						dayNames: wcfm_datepicker_params.dayNames,
						dayNamesShort: wcfm_datepicker_params.dayNamesShort,
						dayNamesMin: wcfm_datepicker_params.dayNamesMin,
						firstDay: wcfm_datepicker_params.firstDay,
						isRTL: wcfm_datepicker_params.isRTL,
						dateFormat: wcfm_datepicker_params.dateFormat,
						changeMonth: true,
						changeYear: true
					});
				});
				initiateTip();
				
				$('#wcfm_enquiry_submit_button').off('click').on('click', function(event) {
					event.preventDefault();
					wcfm_enquiry_form_submit($('#wcfm_enquiry_form'));
				});
				$inquiryFormLoaded = true;
			}
		});
	}
	
	$wcfm_anr_loaded = false;
	$('.add_enquiry, .wcfm_catalog_enquiry, .wcfm_store_enquiry').each(function() {
		$(this).click(function(event) {
			event.preventDefault();
			
			if( !$inquiryFormLoaded ) return false;
			
			$store   = $(this).data('store');
			$product = $(this).data('product');
			
			$.colorbox( { inline:true, href: "#enquiry_form_wrapper", width: $popup_width,
				onComplete:function() {
					
					$('#wcfm_enquiry_form').find('#enquiry_vendor_id').val($store);
					$('#wcfm_enquiry_form').find('#enquiry_product_id').val($product);
					
					if( jQuery('.anr_captcha_field').length > 0 ) {
						if (typeof grecaptcha != "undefined") {
							if( $wcfm_anr_loaded ) {
								grecaptcha.reset();
							} else {
								wcfm_anr_onloadCallback();
							}
							$wcfm_anr_loaded = true;
						}
					}
					
				}
			});
		});
	});
	
	function wcfm_enquiry_form_validate($enquiry_form) {
		$is_valid = true;
		jQuery('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
		var enquiry_comment = jQuery.trim($enquiry_form.find('#enquiry_comment').val());
		if(enquiry_comment.length == 0) {
			$is_valid = false;
			$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_enquiry).addClass('wcfm-error').slideDown();
		}
		
		if( $enquiry_form.find('#enquiry_author').length > 0 ) {
			var enquiry_author = jQuery.trim($enquiry_form.find('#enquiry_author').val());
			if(enquiry_author.length == 0) {
				if( $is_valid )
					$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_name).addClass('wcfm-error').slideDown();
				else
					$enquiry_form.find('.wcfm-message').append('<br /><span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_name).addClass('wcfm-error').slideDown();
				
				$is_valid = false;
			}
		}
		
		if( $enquiry_form.find('#enquiry_email').length > 0 ) {
			var enquiry_email = jQuery.trim($enquiry_form.find('#enquiry_email').val());
			if(enquiry_email.length == 0) {
				if( $is_valid )
					$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_email).addClass('wcfm-error').slideDown();
				else
					$enquiry_form.find('.wcfm-message').append('<br /><span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_email).addClass('wcfm-error').slideDown();
				
				$is_valid = false;
			}
		}
		
		$wcfm_is_valid_form = $is_valid;
		$( document.body ).trigger( 'wcfm_form_validate', $enquiry_form );
		$is_valid = $wcfm_is_valid_form;
		
		return $is_valid;
	}
	
	function wcfm_enquiry_form_submit($enquiry_form) {
		
		// Validations
		$is_valid = wcfm_enquiry_form_validate($enquiry_form);
		
		if($is_valid) {
			$('#enquiry_form_wrapper').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var data = {
				action                   : 'wcfm_ajax_controller',
				controller               : 'wcfm-enquiry-tab',
				wcfm_enquiry_tab_form    : $enquiry_form.serialize(),
				status                   : 'submit'
			}	
			jQuery.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = jQuery.parseJSON(response);
					$enquiry_form.find('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" );
						setTimeout(function() {
							$.colorbox.remove();
							$enquiry_form.find('#enquiry_comment').val('');
							jQuery('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
						}, 2000 );
					} else {
						$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if( jQuery('.wcfm_gglcptch_wrapper').length > 0 ) {
						if (typeof grecaptcha != "undefined") {
							grecaptcha.reset();
						}
					}
					$('#enquiry_form_wrapper').unblock();
				}
			});
		}
	}
});