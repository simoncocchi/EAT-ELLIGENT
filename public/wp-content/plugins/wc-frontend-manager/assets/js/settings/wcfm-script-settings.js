jQuery(document).ready( function($) {
	// Collapsible
  $('.page_collapsible:not(.page_collapsible_dummy)').collapsible({
		defaultOpen: wcfm_setting_options.default_tab,
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
			elem.find('span').removeClass('fa-arrow-circle-up block-indicator');
			elem.next().stop(true, true).slideUp(opts.speed);
		}
	});
	$('.page_collapsible').each(function() {
		$(this).html('<div class="page_collapsible_content_holder">' + $(this).html() + '</div>');
		$(this).find('.page_collapsible_content_holder').after( $(this).find('span') );
	});
	$('.page_collapsible').find('span').addClass('wcfmfa');
	$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
	$('.wcfm-tabWrap').find('.wcfm-container').hide();
	setTimeout(function() {
		if(window.location.hash) {
			if( $('.wcfm_dokan_store_settings').length > 0 ) {
				$('.wcfm-tabWrap').find('.page_collapsible:first').click();
			} else {
				$('.wcfm-tabWrap').find(window.location.hash).click();
			}
		} else {
			$('.wcfm-tabWrap').find('.page_collapsible:first').click();
		}
	}, 500 );
	
	// Tabheight  
	$('.page_collapsible').each(function() {
		if( !$(this).hasClass('wcfm_head_hide') ) {
			collapsHeight += $(this).height() + 50;
		}
	});
  
	if( $(".country_select").length > 0 ) {
		$(".country_select").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	if( $("#timezone").length > 0 ) {
		$("#timezone").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	if( $(".wcfm_product_type_categories").length > 0 ) {
		$(".wcfm_product_type_categories").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	if( $(".wcfm_category_attributes_mapping").length > 0 ) {
		$(".wcfm_category_attributes_mapping").select2({
			placeholder: wcfm_dashboard_messages.category_attribute_mapping + ' ...'
		});
	}
	
	if( $(".wcfm_product_type_toolset_fields").length > 0 ) {
		$(".wcfm_product_type_toolset_fields").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	if( $(".exclude_product_types").length > 0 ) {
		$(".exclude_product_types").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	if( $("#wc_frontend_manager_page_id").length > 0 ) {
		$("#wc_frontend_manager_page_id").select2( $wcfm_page_select_args );
	}
	
	if( $("#wcfm_vendor_membership_page_id").length > 0 ) {
		$("#wcfm_vendor_membership_page_id").select2( $wcfm_page_select_args );
	}
	
	if( $("#wcfm_vendor_registration_page_id").length > 0 ) {
		$("#wcfm_vendor_registration_page_id").select2( $wcfm_page_select_args );
	}
	
	if( $("#wcfm_affiliate_registration_page_id").length > 0 ) {
		$("#wcfm_affiliate_registration_page_id").select2( $wcfm_page_select_args );
	}
	
	if( $("#terms_page").length > 0 ) {
		$("#terms_page").select2( $wcfm_page_select_args );
	}
	
	// WC Vendors MangoPay paymode settings options
	if( $('#vendor_account_type').length > 0 ) {
		$('#vendor_account_type').change(function() {
			$payment_mode = $(this).val();
			$('.mangopay_acc_type').addClass('wcfm_ele_hide');
			$('.mangopay_acc_type_' + $payment_mode).removeClass('wcfm_ele_hide');
			resetCollapsHeight($('#vendor_account_type'));
		}).change();
	}
	
	// WCfM Menu Manager
	$('#wcfm_menu_manager').find('.multi_input_block').each(function() {
		$multi_input_block = $(this);
		$multi_input_block.prepend('<span class="fields_collapser menu_manager_collapser wcfmfa fa-arrow-circle-down" title="'+wcfm_dashboard_messages.wcfm_multiblick_collapse_help+'"></span>');
	  $multi_input_block.find('input[data-name="enable"]').off('change').on('change', function() {
	  	if( $(this).is(':checked') ) {
	      $(this).parent().find('.wcfm_ele:not(.menu_manager_ele), .wcfm_title, .select2').removeClass('menu_manager_ele_hide');
	      $(this).parent().find('input[data-name="enable"]').attr( 'checked', true ).removeClass('collapsed_checkbox');
				$(this).parent().find('.menu_manager_collapser').addClass('fa-arrow-circle-up');
	  	} else {
	  		$(this).parent().find('.wcfm_ele:not(.menu_manager_ele), .wcfm_title, .select2').addClass('menu_manager_ele_hide');
	  		$(this).parent().find('input[data-name="enable"]').attr( 'checked', false ).addClass('collapsed_checkbox');
				$(this).parent().find('.menu_manager_collapser').removeClass('fa-arrow-circle-up');
			}
			resetCollapsHeight($('#wcfm_menu_manager'));
	  });
	  
	  $multi_input_block.children('.add_multi_input_block').on('click', function() {
	  	initMenuManagerCollapser(false);
	  	$('.menu_manager_collapser').click();
			$('#wcfm_menu_manager').children('.multi_input_block:last').find('input[data-name="enable"]').click();
			$('#wcfm_menu_manager').children('.multi_input_block:last').find('.menu_manager_collapser').click();
			$('#wcfm_menu_manager').children('.multi_input_block:last').find('input[data-name="label"]').focus();
	  });
	})
	function initMenuManagerCollapser($newClass) {
		$('#wcfm_menu_manager').children('.multi_input_block').children('.menu_manager_collapser').each(function() {
			if($newClass) { $(this).addClass('fa-arrow-circle-up'); }
			$(this).off('click').on('click', function() {
				$(this).parent().find('.wcfm_ele:not(.menu_manager_ele), .wcfm_title, .select2').toggleClass('menu_manager_ele_hide');
				$(this).parent().find('input[data-name="enable"]').toggleClass('collapsed_checkbox');
				$(this).toggleClass('fa-arrow-circle-up');
				resetCollapsHeight($('#wcfm_menu_manager'));
			} ).click();
		} );
	}
	initMenuManagerCollapser(true);
	
	// TinyMCE intialize - Description
	if( $('#shop_description').length > 0 ) {
		if( $('#shop_description').hasClass('rich_editor') ) {
			if( typeof tinymce != 'undefined' ) {
				var descTinyMCE = tinymce.init({
																			selector: '#shop_description',
																			height: 75,
																			menubar: false,
																			plugins: [
																				'advlist autolink lists link charmap print preview anchor',
																				'searchreplace visualblocks code fullscreen',
																				'insertdatetime image media table paste code directionality',
																				'autoresize'
																			],
																			toolbar: tinyMce_toolbar,
																			content_css: '//www.tinymce.com/css/codepen.min.css',
																			statusbar: false,
																			browser_spellcheck: true,
																			entity_encoding: "raw"
																		});
			}
		}
	}
	
	// Dashboard Style Settings Reset to Default
	if( $('#wcfm_color_setting_reset_button').length > 0 ) {
		$('#wcfm_color_setting_reset_button').click(function(event) {
			event.preventDefault();
			$.each(wcfm_color_setting_options, function( wcfm_color_setting_option, wcfm_color_setting_option_values ) {
				//$('#' + wcfm_color_setting_option_values.name).val( wcfm_color_setting_option_values.default );	
				$('#' + wcfm_color_setting_option_values.name).iris( 'color', wcfm_color_setting_option_values.default );
			} );
			$('#wcfm_settings_save_button').click();
		});
	}
	
	// Menu Manager
	$('#wcfm_menu_manager').find('.multi_input_block').each(function() {
		$multi_input_block = $(this);
		$is_custom = $multi_input_block.find('input[data-name="custom"]').val();
		if( $is_custom && ( $is_custom == 'no' ) ) $multi_input_block.find('.remove_multi_input_block').addClass('wcfm_custom_hide');
		 $multi_input_block.children('.add_multi_input_block').on('click', function() {
		 	 $(this).parent().find('.remove_multi_input_block').removeClass('wcfm_custom_hide'); 
		 });
	});
	
	// Store Hours
	if( $("#wcfm_default_store_hours_off_days").length > 0 ) {
		$("#wcfm_default_store_hours_off_days").select2();
		
		$("#wcfm_default_store_hours_off_days").change(function() {
			$('.wcfm_store_hours_fields').removeClass('wcfm_ele_hide');
			if( $(this).val() ) {
				$.each($(this).val(), function( $i, $off_days ) {
					$('.wcfm_store_hours_fields_'+$off_days).addClass('wcfm_ele_hide');	
				});
			}
		}).change();
	}
	
	// Delivery Times
	if( $("#wcfm_default_delivery_time_off_days").length > 0 ) {
		$("#wcfm_default_delivery_time_off_days").select2();
		
		$("#wcfm_default_delivery_time_off_days").change(function() {
			$('.wcfm_delivery_time_fields').removeClass('wcfm_ele_hide');
			if( $(this).val() ) {
				$.each($(this).val(), function( $i, $off_days ) {
					$('.wcfm_delivery_time_fields_'+$off_days).addClass('wcfm_ele_hide');	
				});
			}
		}).change();
	}
	
	// Live Chat
	if( $("#wcfm_chatbox_setting_lib").length > 0 ) {
		$("#wcfm_chatbox_setting_lib").change(function() {
			$('.wcfm_chatbox_field').addClass('wcfm_ele_hide');
			$('.wcfm_chatbox_field_'+$(this).val()).removeClass('wcfm_ele_hide');	
		}).change();
	}
	
	// Save Settings
	$('#wcfm_settings_save_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  var profile = getWCFMEditorContent( 'shop_description' );
		
		var shipping_policy = getWCFMEditorContent( 'wcfm_shipping_policy' );
		
		var refund_policy = getWCFMEditorContent( 'wcfm_refund_policy' );
		
		var cancellation_policy = getWCFMEditorContent( 'wcfm_cancellation_policy' );
		
		// WCFM Custom field Editor support
		if( $('.wcfm_custom_field_editor').length > 0 ) {
			$('.wcfm_custom_field_editor').each(function() {
				$wcfm_custom_field_editor = $(this);
				if( $wcfm_custom_field_editor.hasClass('rich_editor') || $wcfm_custom_field_editor.hasClass('wcfm_wpeditor') ) {
					$('#'+$wcfm_custom_field_editor.attr('id')).val(getWCFMEditorContent( $wcfm_custom_field_editor.attr('id') ));
				}
			});
		}
  
	  // Validations
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
	  $wcfm_is_valid_form = true;
	  $( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_settings_form') );
	  $is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#wcfm_settings_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action               : 'wcfm_ajax_controller',
				controller           : 'wcfm-settings',
				wcfm_settings_form   : $('#wcfm_settings_form').serialize(),
				profile              : profile,
				shipping_policy      : shipping_policy,
				refund_policy        : refund_policy,
				cancellation_policy  : cancellation_policy
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_settings_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
						if( $response_json.file ) $('#wcfm_custom_css-css').attr( 'href', $response_json.file );
					} else {
						$('#wcfm_settings_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					wcfmMessageHide();
					$('#wcfm_settings_form').unblock();
					$('.wcfm_submit_button').show();
				}
			});	
		} else {
			$('.wcfm_submit_button').show();
		}
	});
});