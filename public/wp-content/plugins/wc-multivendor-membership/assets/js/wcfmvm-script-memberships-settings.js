jQuery(document).ready( function($) {
	// Collapsible
  $('.page_collapsible').collapsible({
		defaultOpen: 'membership_settings_form_visibility_head',
		speed: 'slow',
		loadOpen: function (elem) { //replace the standard open state with custom function
				elem.next().show();
		},
		loadClose: function (elem, opts) { //replace the close state with custom function
				elem.next().hide();
		},
		animateOpen: function(elem, opts) {
			$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
			elem.addClass('collapse-open');
			$('.collapse-close').find('span').removeClass('fa-arrow-alt-circle-right block-indicator');
			elem.find('span').addClass('fa-arrow-alt-circle-right block-indicator');
			$('.wcfm-tabWrap').find('.wcfm-container').stop(true, true).slideUp(opts.speed);
			elem.next().stop(true, true).slideDown(opts.speed);
		},
		animateClose: function(elem, opts) {
			elem.find('span').removeClass('fa-arrow-alt-circle-right block-indicator');
			elem.next().stop(true, true).slideUp(opts.speed);
		}
	});
	$('.page_collapsible').each(function() {
		$(this).html('<div class="page_collapsible_content_holder">' + $(this).html() + '</div>');
		$(this).find('.page_collapsible_content_holder').after( $(this).find('span') );
	});
	$('.page_collapsible').find('span').addClass('fa');
	$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
	$('.wcfm-tabWrap').find('.wcfm-container').hide();
	setTimeout(function() {
		if(window.location.hash) {
			//$('.wcfm-tabWrap').find(window.location.hash).click();
		} else {
			$('.wcfm-tabWrap').find('.page_collapsible:first').click();
		}
	}, 100 );
	
	// Tabheight  
	$('.page_collapsible').each(function() {
		if( !$(this).hasClass('wcfm_head_hide') ) {
			collapsHeight += $(this).height() + 50;
		}
	});
	
	if( $("#wcfm_custom_plan_page").length > 0 ) {
		$("#wcfm_custom_plan_page").select2( $wcfm_page_select_args );
	}
	
	if( $("#wcfm_custom_thankyou_page").length > 0 ) {
		$("#wcfm_custom_thankyou_page").select2( $wcfm_page_select_args );
	}
	
	if( $("#terms_page").length > 0 ) {
		$("#terms_page").select2( $wcfm_page_select_args );
	}
	
	/*$('#terms').change(function() {
	  if($(this).is(':checked')) {
	  	$(this).parent().find('.terms_page_ele').removeClass('wcfm_ele_hide');
	  } else {
	  	$(this).parent().find('.terms_page_ele').addClass('wcfm_ele_hide');
	  }
	}).change();*/
	
	$('.payment_fields').addClass( 'wcfm_custom_hide' );
	$('.payment_options').each(function() {
	  $(this).change(function() {
	  	$pay_option = $(this).val();
	  	if($(this).is(':checked')) {
				$('.'+$pay_option+'_payment_field').removeClass('wcfm_custom_hide');
			} else {
				$('.'+$pay_option+'_payment_field').addClass('wcfm_custom_hide');
			}
	  }).change();
	});
	
	$('#paypal_sandbox').change(function() {
	  if($(this).is(':checked')) {
	  	$(this).parent().find('.live_payment_field').addClass('wcfm_ele_hide');
	  	$(this).parent().find('.test_payment_field').removeClass('wcfm_ele_hide');
	  } else {
	  	$(this).parent().find('.test_payment_field').addClass('wcfm_ele_hide');
	  	$(this).parent().find('.live_payment_field').removeClass('wcfm_ele_hide');
	  }
	}).change();
	
	// SMS Verification
	if( $('#sms_verification').length > 0 ) {
		$('#sms_verification').click(function() {
		  if( $(this).is(':checked') ) {
		  	$('#phone').attr( 'checked', true );
		  }
		});
	}
	
	// Style Settings Reset to Default
	if( $('#wcfmvm_color_setting_reset_button').length > 0 ) {
		$('#wcfmvm_color_setting_reset_button').click(function(event) {
			event.preventDefault();
			$.each(wcfmvm_color_setting_options, function( wcfmvm_color_setting_option, wcfmvm_color_setting_option_values ) {
				//$('#' + wcfm_color_setting_option_values.name).val( wcfm_color_setting_option_values.default );	
				$('#' + wcfmvm_color_setting_option_values.name).iris( 'color', wcfmvm_color_setting_option_values.default );
			} );
			$('#wcfm_membership_setting_submit_button').click();
		});
	}
	
	
	// Submit Memebrship Settings
	$('#wcfm_membership_setting_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  var free_thankyou_content = getWCFMEditorContent( 'free_thankyou_content' );
		var subscription_thankyou_content = getWCFMEditorContent( 'subscription_thankyou_content' );
		var non_membership_welcome_email_content = getWCFMEditorContent( 'non_membership_welcome_email_content' );
		var subscription_welcome_email_content = getWCFMEditorContent( 'subscription_welcome_email_content' );
		var registration_admin_notication_content = getWCFMEditorContent( 'registration_admin_notication_content' );
		var subscription_admin_notication_content = getWCFMEditorContent( 'subscription_admin_notication_content' );
		var onapproval_admin_notication_content = getWCFMEditorContent( 'onapproval_admin_notication_content' );
		var next_payment_notication_content = getWCFMEditorContent( 'next_payment_notication_content' );
		var reminder_notication_content = getWCFMEditorContent( 'reminder_notication_content' );
		var cancel_notication_content = getWCFMEditorContent( 'cancel_notication_content' );
		var expire_notication_content = getWCFMEditorContent( 'expire_notication_content' );
		var reject_notication_content = getWCFMEditorContent( 'reject_notication_content' );
		var switch_admin_notication_content = getWCFMEditorContent( 'switch_admin_notication_content' );
		var switch_notication_content = getWCFMEditorContent( 'switch_notication_content' );
	  
	  // Validations
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
	  $wcfm_is_valid_form = true;
	  $( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_membership_settings_form') );
	  $is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                                : 'wcfm_ajax_controller',
				controller                            : 'wcfm-memberships-settings',
				wcfm_membership_settings_form         : $('#wcfm_membership_settings_form').serialize(),
				free_thankyou_content                 : free_thankyou_content,
				subscription_thankyou_content         : subscription_thankyou_content,
				non_membership_welcome_email_content  : non_membership_welcome_email_content,
				subscription_welcome_email_content    : subscription_welcome_email_content,
				registration_admin_notication_content : registration_admin_notication_content,
				subscription_admin_notication_content : subscription_admin_notication_content, 
				onapproval_admin_notication_content   : onapproval_admin_notication_content,
				next_payment_notication_content       : next_payment_notication_content,
				reminder_notication_content           : reminder_notication_content,
				cancel_notication_content             : cancel_notication_content,
				expire_notication_content             : expire_notication_content,
				reject_notication_content             : reject_notication_content,
				switch_admin_notication_content       : switch_admin_notication_content,
				switch_notication_content             : switch_notication_content,
				status                                : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.status) {
						audio.play();
						$('#wcfm_membership_settings_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						audio.play();
						$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
						$('#wcfm_membership_settings_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#group_id').val($response_json.id);
					$('#wcfm-content').unblock();
				}
			});
		}
	});
} );