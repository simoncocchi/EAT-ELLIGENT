jQuery(document).ready( function($) {
	// Collapsible
  $('.page_collapsible').collapsible({
		//defaultOpen: 'wcfm_vendor_manage_form_profile_head',
		speed: 'slow',
		loadOpen: function (elem) { //replace the standard open state with custom function
			elem.next().show();
		},
		loadClose: function (elem, opts) { //replace the close state with custom function
			elem.next().hide();
		},
		animateOpen: function(elem, opts) {
			elem_id = elem.attr('id');
			if( elem_id != 'groups_manage_capability_head' ) {
				$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
				elem.addClass('collapse-open');
				$('#wcfm-vendor-manager-wrapper .wcfm-container:not(:first)').stop(true, true).slideUp(opts.speed);
			}
			elem.next().stop(true, true).slideDown(opts.speed);
		}
	});
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			var data = {
				action                : 'vendor_manager_change_url',
				vendor_manager_change : $('#dropdown_vendor').val()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.redirect) {
						window.location = $response_json.redirect;
					}
				}
			});
		}).select2( $wcfm_vendor_select_args );
	}	
	
	// Direct Message Send
	$('#wcfm_messages_save_button').click(function(event) {
	  event.preventDefault();
	  
	  var wcfm_messages = getWCFMEditorContent( 'wcfm_messages' );
	  var direct_to = $('#direct_to').val();
	  
	  if( !wcfm_messages ) return false;
  
	  // Validations
	  $is_valid = true; //wcfm_coupons_manage_form_validate();
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_form_message_expander').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action             : 'wcfm_ajax_controller',
				controller         : 'wcfm-message-sent',
				wcfm_messages      : wcfm_messages,
				direct_to          : direct_to
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						if( typeof tinymce != 'undefined' ) {
							tinymce.get('wcfm_messages').setContent('');
						} else {
							$('#wcfm_messages').val('');
						}
						$('#wcfm_vendor_manage_form_message_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						$('#wcfm_vendor_manage_form_message_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_form_message_expander').unblock();
				}
			});	
		}
	});
	
	// Profile Update
	$('#wcfm_profile_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_profile_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_form_profile_expander').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                          : 'wcfm_ajax_controller',
				controller                      : 'wcfm-vendors-manage-profile',
				wcfm_vendor_manage_profile_form : $('#wcfm_vendor_manage_profile_form').serialize(),
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_profile_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_profile_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_form_profile_expander').unblock();
				}
			});	
		}
	});
	
	if( $(".wcfm_multi_select").length > 0 ) {
		$(".wcfm_multi_select").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	// WCfM Marketplace Settings Update
	$('#wcfm-main-contentainer #country').select2();
	$('#wcfm_store_setting_save_button, #wcfm_store_general_setting_save_button, #wcfm_store_address_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  var profile = getWCFMEditorContent( 'shop_description' );
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_store_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_store_setting_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_store_setting_form').serialize(),
				profile                   : profile,
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_setting_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_setting_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_store_setting_form').unblock();
				}
			});	
		}
	});
	
	// WCfM Marketplace Shipping Settings Update
	$('#wcfm_store_shipping_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_store_shipping_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_form_store_shipping_setting_expander').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-shipping-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_store_shipping_setting_form').serialize(),
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_shipping_setting_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_shipping_setting_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_form_store_shipping_setting_expander').unblock();
				}
			});	
		}
	});
	
	// WCfM Marketplace Commission Settings Update
	$('#wcfm_store_commission_setting_save_button, #wcfm_store_transaction_setting_save_button, #wcfm_store_withdrawal_setting_save_button, #wcfm_store_payment_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_store_commission_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_store_commission_setting_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_store_commission_setting_form').serialize()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_commission_setting_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_commission_setting_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_store_commission_setting_form').unblock();
				}
			});	
		}
	});
	
	// WCfM Marketplace Store Hours & Vacation Settings Update
	$('#wcfm_store_hours_setting_save_button, #wcfm_store_vacation_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_store_hours_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_store_hours_setting_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_store_hours_setting_form').serialize()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_hours_setting_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_hours_setting_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_store_hours_setting_form').unblock();
				}
			});	
		}
	});
	
	// WCfM Marketplace Delivery Time Settings Update
	$('#wcfm_delivery_time_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_delivery_time_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_delivery_time_setting_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_delivery_time_setting_form').serialize()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_settings_form_delivery_time_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_settings_form_delivery_time_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_delivery_time_setting_form').unblock();
				}
			});	
		}
	});
	
	// WCfM Marketplace Store Invoice Settings Update
	$('#wcfm_store_invoice_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_store_invoice_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_store_invoice_setting_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_store_invoice_setting_form').serialize()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_vendor_invoice_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_vendor_invoice_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_store_invoice_setting_form').unblock();
				}
			});	
		}
	});
	
	// WCfM Marketplace Store SEO & Social Settings Update
	$('#wcfm_store_seo_setting_save_button, #wcfm_store_social_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_store_seo_social_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_store_seo_social_setting_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_store_seo_social_setting_form').serialize()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_seo_social_setting_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_seo_social_setting_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_store_seo_social_setting_form').unblock();
				}
			});	
		}
	});
	
	// WCfM Marketplace Store Policy & Customer Support Settings Update
	$('#wcfm-main-contentainer #vendor_csd_return_country').select2();
	$('#wcfm_store_policy_setting_save_button, #wcfm_store_customer_support_setting_save_button').click(function(event) {
	  event.preventDefault();
	  
	  var shipping_policy = getWCFMEditorContent( 'wcfm_shipping_policy' );
		
		var refund_policy = getWCFMEditorContent( 'wcfm_refund_policy' );
		
		var cancellation_policy = getWCFMEditorContent( 'wcfm_cancellation_policy' );
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_store_policy_support_setting_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_store_policy_support_setting_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                    : 'wcfm_ajax_controller',
				controller                : 'wcfm-vendors-manage-marketplace-settings',
				wcfm_settings_form        : $('#wcfm_vendor_manage_store_policy_support_setting_form').serialize(),
				shipping_policy           : shipping_policy,
				refund_policy             : refund_policy,
				cancellation_policy       : cancellation_policy
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_policy_support_setting_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_store_policy_support_setting_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_store_policy_support_setting_form').unblock();
				}
			});	
		}
	});
	
	// Vendor Disable
	$('#wcfm_vendor_disable_button').click(function( event ) {
		event.preventDefault();
		
		$('#vendors_manage_general_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action       : 'wcfm_vendor_disable',
			memberid     : $('#wcfm_vendor_disable_button').data('memberid'),
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				if($response_json.status) {
					window.location = window.location.href;
				}
				$('#vendors_manage_general_expander').unblock();
			}
		});
	});
	
	// Vendor Enable
	$('#wcfm_vendor_enable_button').click(function( event ) {
		event.preventDefault();
		
		$('#vendors_manage_general_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action       : 'wcfm_vendor_enable',
			memberid     : $('#wcfm_vendor_enable_button').data('memberid'),
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				if($response_json.status) {
					window.location = window.location.href;
				}
				$('#vendors_manage_general_expander').unblock();
			}
		});
	});
	
	// Verification Update
	$('#wcfm_vendor_verification_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_verification_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_form_verification_expander').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                                : 'wcfmu_vendors_manage_verification',
				wcfm_vendor_manage_verification_form  : $('#wcfm_vendor_manage_verification_form').serialize(),
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_verification_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_verification_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_verification_response_note').val('');
					$('#wcfm_vendor_manage_form_verification_expander').unblock();
				}
			});	
		}
	});
	
	// Badges Update
	$('#wcfm_vendor_badges_save_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendor_manage_badges_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_vendor_manage_form_badges_expander').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                          : 'wcfm_ajax_controller',
				controller                      : 'wcfm-vendors-manage-badges',
				wcfm_vendor_manage_badges_form  : $('#wcfm_vendor_manage_badges_form').serialize(),
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_badges_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_vendor_manage_form_badges_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_vendor_manage_form_badges_expander').unblock();
				}
			});	
		}
	});
	
	$('.wcfm_vendor_badges_manage_link').click(function(event) {
		event.preventDefault();
		$('.wcfm_vendor_badges_manage').slideDown();
		$(this).hide();
		return false;
	});
});