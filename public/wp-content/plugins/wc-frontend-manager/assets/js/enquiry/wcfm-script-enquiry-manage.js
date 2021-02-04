jQuery(document).ready( function($) {
		
	$('#wcfm_inquiry_reply_send_button').click(function(event) {
	  event.preventDefault();
	
	  var inquiry_reply = getWCFMEditorContent( 'inquiry_reply' );
	  
	  // Validations
	  $is_valid = true;
	  
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		if( wcfmstripHtml(inquiry_reply).length <= 1) {
			$is_valid = false;
			$('#wcfm_inquiry_reply_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_reply).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
	  
	  if($is_valid) {
			$('#wcfm_inquiry_reply_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			$form_data = new FormData( document.getElementById('wcfm_inquiry_reply_form') );
			$form_data.append( 'inquiry_reply', inquiry_reply );
			$form_data.append( 'wcfm_inquiry_reply_form', $('#wcfm_inquiry_reply_form').serialize() ); 
			$form_data.append( 'action', 'wcfm_ajax_controller' ); 
			$form_data.append( 'controller', 'wcfm-enquiry-manage' ); 
			
			$.ajax({
				type         : 'POST',
				url          : wcfm_params.ajax_url,
				data         : $form_data,
				contentType  : false,
				cache        : false,
				processData  :false,
				success: function(response) {
					if(response) {
						$response_json = $.parseJSON(response);
						$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
						wcfm_notification_sound.play();
						if($response_json.status) {
							$('#wcfm_inquiry_reply_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" , function() {
								if( $response_json.redirect ) window.location = $response_json.redirect;
								else window.location = window.location.href;
							} );
						} else {
							$('#wcfm_inquiry_reply_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
						}
						$('#wcfm_inquiry_reply_form').unblock();
					}
				}
			});
		}
	});
	
	// Enquiry Response Delete
	$('.wcfm_enquiry_response_delete').each(function() {
		$(this).click(function(event) {
			event.preventDefault();
			var rconfirm = confirm(wcfm_dashboard_messages.enquiry_delete_confirm);
			if(rconfirm) deleteWCFMEnquiryResponse($(this));
			return false;
		});
	});
	
	function deleteWCFMEnquiryResponse(item) {
		$enquiryresponseid = item.data('enquiryresponseid');
		jQuery('.wcfm-collapse').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action     : 'delete_wcfm_enquiry_response',
			responseid : $enquiryresponseid
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				jQuery('#inquiry_reply_'+$enquiryresponseid).parent().remove();
				jQuery('.wcfm-collapse').unblock();
			}
		});
	}
} );