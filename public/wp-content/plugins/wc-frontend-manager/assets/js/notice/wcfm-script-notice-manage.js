jQuery(document).ready( function($) {
		
	function wcfm_notice_manage_form_validate() {
		$is_valid = true;
		$('.wcfm-message').html('').removeClass('wcfm-error').slideUp();
		var title = $.trim($('#wcfm_notice_manage_form').find('#title').val());
		if(title.length == 0) {
			$is_valid = false;
			$('#wcfm_notice_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_notice_manage_messages.no_title).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
		return $is_valid;
	}
	
	// Submit Notice
	$('#wcfm_notice_manager_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
	  $is_valid = wcfm_notice_manage_form_validate();
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var wcfm_notice = getWCFMEditorContent( 'wcfm_notice' );
			
			var data = {
				action                   : 'wcfm_ajax_controller',
				controller               : 'wcfm-notice-manage',
				wcfm_notice_manage_form  : $('#wcfm_notice_manage_form').serialize(),
				content                  : wcfm_notice,
				status                   : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_notice_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_notice_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#notice_id').val($response_json.id);
					wcfmMessageHide();
					$('#wcfm-content').unblock();
				}
			});
		}
	});
} );