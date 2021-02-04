jQuery(document).ready(function($) {
	wcfmInitLoginPopup();
});

function wcfmInitLoginPopup() {
  jQuery('.wcfm_login_popup').each( function() {
  	jQuery(this).click( function( event ) {
			event.preventDefault();
			jQuerylogin_popup = jQuery(this);
			
			// Ajax Call for Fetching Quick Edit HTML
			jQuery('body').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action  : 'wcfm_login_popup_form'
			}	
			
			jQuery.ajax({
				type    :		'POST',
				url     : wcfm_params.ajax_url,
				data    : data,
				success :	function(response) {
					// Intialize colorbox
					jQuery.colorbox( { html: response, width: $popup_width,
						onComplete:function() {
					
							// Intialize Quick Update Action
							jQuery('#wcfm_login_popup_button').click(function() {
								$wcfm_is_valid_form = true;
								jQuery('#wcfm_login_popup_form').block({
									message: null,
									overlayCSS: {
										background: '#fff',
										opacity: 0.6
									}
								});
								jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
								if( jQuery('input[name=wcfm_login_popup_username]').val().length == 0 ) {
									jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_login_messages.no_username).addClass('wcfm-error').slideDown();
									wcfm_notification_sound.play();
									jQuery('#wcfm_login_popup_form').unblock();
								} else if( jQuery('input[name=wcfm_login_popup_password]').val().length == 0 ) {
									jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_login_messages.no_password).addClass('wcfm-error').slideDown();
									wcfm_notification_sound.play();
									jQuery('#wcfm_login_popup_form').unblock();
								} else {
									jQuery( document.body ).trigger( 'wcfm_form_validate', jQuery('#wcfm_login_popup_form') );
									if( !$wcfm_is_valid_form ) {
										wcfm_notification_sound.play();
										jQuery('#wcfm_login_popup_form').unblock();
									} else {
										jQuery('#wcfm_login_popup_button').hide();
										var data = {
											action : 'wcfm_login_popup_submit', 
											wcfm_login_popup_form : jQuery('#wcfm_login_popup_form').serialize()
										}	
										jQuery.post(wcfm_params.ajax_url, data, function(response) {
											if(response) {
												jQueryresponse_json = jQuery.parseJSON(response);
												wcfm_notification_sound.play();
												jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
												if(jQueryresponse_json.status) {
													jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + jQueryresponse_json.message).addClass('wcfm-success').slideDown();
													window.location = window.location.href;
												} else {
													jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + jQueryresponse_json.message).addClass('wcfm-error').slideDown();
													jQuery('#wcfm_login_popup_button').show();
													jQuery('#wcfm_login_popup_form').unblock();
												}
											}
										} );
									}
								}
							});
						}
					});
					jQuery('body').unblock();
				}
			});
			
			return false;
		} );
  } );
}