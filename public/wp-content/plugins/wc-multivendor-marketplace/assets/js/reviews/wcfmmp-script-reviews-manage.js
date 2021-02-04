jQuery(document).ready( function($) {
	$wcfm_messages_table = '';
	
	// TinyMCE intialize - Description
	if( $('#support_ticket_reply').length > 0 ) {
		if( typeof tinymce != 'undefined' ) {
			var descTinyMCE = tinymce.init({
																		selector: '#support_ticket_reply',
																		height: 75,
																		menubar: false,
																		plugins: [
																			'advlist autolink lists link charmap print preview anchor',
																			'searchreplace visualblocks code fullscreen',
																			'insertdatetime table contextmenu paste code directionality',
																			'autoresize'
																		],
																		toolbar: tinyMce_toolbar,
																		content_css: '//www.tinymce.com/css/codepen.min.css',
																		statusbar: false,
																		browser_spellcheck: true,
																	});
		}
	}
	
	// Save Settings
	$('#wcfm_reply_send_button').click(function(event) {
	  event.preventDefault();
	  
	  var support_ticket_reply = '';
	  if( typeof tinymce != 'undefined' ) {
	  	if( tinymce.get('support_ticket_reply') != null ) support_ticket_reply = tinymce.get('support_ticket_reply').getContent();
	  	else support_ticket_reply = $('#support_ticket_reply').val();
	  } else {
	  	support_ticket_reply = $('#support_ticket_reply').val();
	  }
  
	  // Validations
	  $is_valid = true;
	  
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		if(support_ticket_reply.length == 0) {
			$is_valid = false;
			$('#wcfm_support_ticket_reply_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_support_manage_messages.no_reply).addClass('wcfm-error').slideDown();
			audio.play();
		}
	  
	  if($is_valid) {
			$('#wcfm_support_ticket_reply_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                         : 'wcfm_ajax_controller',
				controller                     : 'wcfm-support-manage',
				support_ticket_reply           : support_ticket_reply,
				wcfm_support_ticket_reply_form : jQuery('#wcfm_support_ticket_reply_form').serialize()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						tinymce.get('support_ticket_reply').setContent('');
						$('#wcfm_support_ticket_reply_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" , function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_support_ticket_reply_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_support_ticket_reply_form').unblock();
				}
			});	
		}
	});
});