jQuery(document).ready( function($) {
	// Collapsible
  $('.page_collapsible').collapsible({
		defaultOpen: 'memberships_features_manage_head',
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
			$('.collapse-close').find('span').removeClass('fa-arrow-circle-o-right block-indicator');
			elem.find('span').addClass('fa-arrow-circle-o-right block-indicator');
			$('.wcfm-tabWrap').find('.wcfm-container').stop(true, true).slideUp(opts.speed);
			elem.next().stop(true, true).slideDown(opts.speed);
		},
		animateClose: function(elem, opts) {
			elem.find('span').removeClass('fa-arrow-circle-o-up block-indicator');
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
		$('.wcfm-tabWrap').find('.page_collapsible:first').click();
	}, 500 );
	
	// Tabheight  
	$('.page_collapsible').each(function() {
		if( !$(this).hasClass('wcfm_head_hide') ) {
			collapsHeight += $(this).height() + 20;
		}
	}); 
	
	if( $("#subscription_product").length > 0 ) {
		$("#subscription_product").select2( $wcfm_product_select_args );
	}
	
	$('#is_free').click(function() {
	  if( $(this).is(':checked') ) {
	  	$('.subscription_options').addClass('wcfm_ele_hide');
	  	$('.free_expiry_period_wrapper').removeClass('wcfm_ele_hide');
	  } else {
	  	if( $('#subscription_type').val() != 'one_time' ) {
	  		$('.free_expiry_period_wrapper').addClass('wcfm_ele_hide');
	  	}
	  	$('.subscription_options').removeClass('wcfm_ele_hide');
	  	resetCollapsHeight($('#subscription_type'));
	  }
	});
	
	$('#subscription_type').change(function() {
	  if( $(this).val() == 'one_time' ) {
	  	$('.subscription_recurring_options').addClass('wcfm_ele_hide');
	  	$('.subscription_one_time_options').removeClass('wcfm_ele_hide');
	  	$('.free_expiry_period_wrapper').removeClass('wcfm_ele_hide');
	  } else {
	  	$('.subscription_one_time_options').addClass('wcfm_ele_hide');
	  	$('.free_expiry_period_wrapper').addClass('wcfm_ele_hide');
	  	$('.subscription_recurring_options').removeClass('wcfm_ele_hide');
	  }
	  resetCollapsHeight($('#subscription_type'));
	}).change();
	
	if( $('#is_free').is(':checked') ) {
		$('.subscription_options').addClass('wcfm_ele_hide');
		$('.free_expiry_period_wrapper').removeClass('wcfm_ele_hide');
	} else {
		if( $('#subscription_type').val() != 'one_time' ) {
			$('.free_expiry_period_wrapper').addClass('wcfm_ele_hide');
		}
		$('.subscription_options').removeClass('wcfm_ele_hide');
		resetCollapsHeight($('#subscription_type'));
	}
	
	$('#subscription_pay_mode').change(function() {
	  if( $(this).val() == 'by_wc' ) {
	  	$('.stripe_plan_ele').addClass('wcfm_ele_hide');
	  	$('.subscription_product_ele').removeClass('wcfm_ele_hide');
	  	$('.subscription_product_ele').next('.select2').removeClass('wcfm_ele_hide');
	  } else {
	  	$('.subscription_product_ele').addClass('wcfm_ele_hide');
	  	$('.subscription_product_ele').next('.select2').addClass('wcfm_ele_hide');
	  	$('.stripe_plan_ele').removeClass('wcfm_ele_hide');
	  }
	  resetCollapsHeight($('#subscription_type'));
	}).change();
	
	if( $("#membership_vendors").length > 0 ) {
		$("#membership_vendors").select2({
			placeholder: "Choose Vendors ..."
		});
	}
	
	function wcfm_memberships_manage_form_validate() {
		$is_valid = true;
		$('.wcfm-message').html('').removeClass('wcfm-error').slideUp();
		var title = $.trim($('#wcfm_memberships_manage_form').find('#title').val());
		if(title.length == 0) {
			$is_valid = false;
			$('#wcfm_memberships_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_memberships_manage_messages.no_title).addClass('wcfm-error').slideDown();
			audio.play();
		}
		return $is_valid;
	}
	
	// Submit Membership
	$('#wcfm_Membership_manager_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  var free_thankyou_content = getWCFMEditorContent( 'free_thankyou_content' );
		var subscription_thankyou_content = getWCFMEditorContent( 'subscription_thankyou_content' );
		var subscription_welcome_email_content = getWCFMEditorContent( 'subscription_welcome_email_content' );
	  
	  // Validations
	  $is_valid = wcfm_memberships_manage_form_validate();
	  
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
				controller                            : 'wcfm-memberships-manage',
				wcfm_memberships_manage_form          : $('#wcfm_memberships_manage_form').serialize(),
				free_thankyou_content                 : free_thankyou_content,
				subscription_thankyou_content         : subscription_thankyou_content,
				subscription_welcome_email_content    : subscription_welcome_email_content,
				status                                : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.status) {
						audio.play();
						$('#wcfm_memberships_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						audio.play();
						$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
						$('#wcfm_memberships_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#membership_id').val($response_json.id);
					$('#wcfm-content').unblock();
				}
			});
		}
	});
} );