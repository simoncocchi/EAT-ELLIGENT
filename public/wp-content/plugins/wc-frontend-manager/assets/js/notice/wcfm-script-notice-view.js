jQuery(document).ready( function($) {
	$wcfm_messages_table = '';
	
	// Save Settings
	$('#wcfm_reply_send_button').click(function(event) {
	  event.preventDefault();
	  
	  var topic_reply = getWCFMEditorContent( 'topic_reply' );
		
	  var topic_id = $('#topic_id').val();
  
	  // Validations
	  $is_valid = true;
	  
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		if(topic_reply.length == 0) {
			$is_valid = false;
			$('#wcfm_topic_reply_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_notice_view_messages.no_title).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
	  
	  if($is_valid) {
			$('#wcfm_topic_reply_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action             : 'wcfm_ajax_controller',
				controller         : 'wcfm-notice-reply',
				topic_reply        : topic_reply,
				topic_id           : topic_id
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						tinymce.get('topic_reply').setContent('');
						wcfm_notification_sound.play();
						$('#wcfm_topic_reply_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" , function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_topic_reply_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_topic_reply_form').unblock();
				}
			});	
		}
	});
});