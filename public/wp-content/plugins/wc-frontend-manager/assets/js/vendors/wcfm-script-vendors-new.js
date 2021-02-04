jQuery(document).ready(function($) {
		
	// Collapsible
	$('.page_collapsible').collapsible({
		defaultOpen: 'wcfm_vendor_address_head',
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
		$('.wcfm-tabWrap').find('.page_collapsible:first').click();
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
		
	function wcfm_vendors_new_form_validate() {
		$is_valid = true;
		
		$( document.body ).trigger( 'wcfm_vendors_new_form_validate' );
		
		$wcfm_is_valid_form = $is_valid;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_vendors_new_form') );
		$is_valid = $wcfm_is_valid_form;
		
		return $is_valid;
	}
	
	// Submit Vendor
	$('#wcfm_vendor_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  var profile = getWCFMEditorContent( 'shop_description' );
	  
	  var shipping_policy = getWCFMEditorContent( 'wcfm_shipping_policy' );
		
		var refund_policy = getWCFMEditorContent( 'wcfm_refund_policy' );
		
		var cancellation_policy = getWCFMEditorContent( 'wcfm_cancellation_policy' );
	  
	  // Validations
	  $is_valid = wcfm_vendors_new_form_validate();
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                   : 'wcfm_ajax_controller',
				controller               : 'wcfm-vendors-new',
				wcfm_vendors_new_form    : $('#wcfm_vendors_new_form').serialize(),
				profile                  : profile,
				shipping_policy          : shipping_policy,
				refund_policy            : refund_policy,
				cancellation_policy      : cancellation_policy,
				status                   : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.redirect) {
						$('#wcfm_vendors_new_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_vendors_new_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#vendor_id').val($response_json.id);
					wcfmMessageHide();
					$('#wcfm-content').unblock();
				}
			});
		}
	});
} );