jQuery(document).ready( function($) {
	// Membership Cancel
	$('#wcfm_membership_cancel_button').click(function( event ) {
		event.preventDefault();
		var rconfirm = confirm( wcfm_memberships_cancel_messages.cancel_confirmation );
		if(rconfirm) cancelWCFMCustomer($(this));
	});
	
	function cancelWCFMCustomer( $element ) {
		if( $('#wcfm_profile_form').length > 0 ) {
			$('#wcfm_profile_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		} else {
			$('#wcfm_vendor_manage_form_membership_expander').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		}
		var data = {
			action       : 'wcfmvm_membership_cancel',
			memberid     : $('#wcfm_membership_cancel_button').data('memberid'),
			membershipid : $('#wcfm_membership_cancel_button').data('membershipid')
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				if($response_json.status) {
					if( $('#wcfm_profile_form').length > 0 ) {
						$('#wcfm_profile_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
							if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );	
					} else {
						$('#wcfm_vendor_manage_form_membership_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" );
						window.location = window.location.href;
					}
				} else {
					$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
					if( $('#wcfm_profile_form').length > 0 ) {
						$('#wcfm_profile_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					} else {
						$('#wcfm_vendor_manage_form_membership_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
				}
				$('#wcfm_profile_form').unblock();
				$('#wcfm_vendor_manage_form_membership_expander').unblock();
			}
		});
	}
	
	// Vendor Membership change by Admin
	$('#wcfm_modify_vendor_membership').click(function( event ) {
		event.preventDefault();
		$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
		if( !$('#wcfm_change_vendor_membership').val() ) return false;
		$('#wcfm_vendor_manage_form_membership_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action       : 'wcfmvm_membership_change',
			memberid     : $('#wcfm_modify_vendor_membership').data('memberid'),
			membershipid : $('#wcfm_change_vendor_membership').val()
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				if($response_json.status) {
					$('#wcfm_vendor_manage_form_membership_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						window.location = window.location.href;
					} );	
				} else {
					$('#wcfm_vendor_manage_form_membership_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
				}
				$('#wcfm_vendor_manage_form_membership_expander').unblock();
			}
		});
	});
	
	// Membership Schedule Update by Admin
	$('#wcfmvm_change_next_renewal').click(function(event) {
		event.preventDefault();
		var data = {
								action   : 'wcfmvm_change_next_renewal_html',
								schedule : $('#wcfmvm_change_next_renewal').data('schedule'),
								member   : $('#wcfmvm_change_next_renewal').data('member')
							 }
		jQuery.ajax({
			type    :		'POST',
			url     : wcfm_params.ajax_url,
			data    : data,
			success :	function(response) {
														 
				// Intialize colorbox
				$.colorbox( { html: response, height: 300, width: $popup_width,
					onComplete:function() {
						$('#wcfmvm_next_renewal').datepicker({
							changeMonth: true,
							changeYear: true
						});
  					
						$('#wcfmvm_change_next_renewal_button').click(function(e) {
							e.preventDefault();
							
							$('#wcfmvm_change_next_renewal_form').block({
								message: null,
								overlayCSS: {
									background: '#fff',
									opacity: 0.6
								}
							});
							
							var next_renewal = $('#wcfmvm_next_renewal').val();
							
							$('#wcfmvm_change_next_renewal_button').hide();
							$('#wcfmvm_change_next_renewal_form .wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
							
							var data = {
								action         : 'wcfmvm_change_next_renewal',
								next_renewal   : next_renewal,
								member         : $('#wcfmvm_change_next_renewal').data('member')
							}	
							$.ajax({
								type:		'POST',
								url: wcfm_params.ajax_url,
								data: data,
								success:	function(response) {
									$response_json = $.parseJSON(response);
									wcfm_notification_sound.play();
									$('.wcfmvm_next_renewal_display').text($response_json.next_renewal_display);
									$('#wcfmvm_change_next_renewal').data('schedule', $response_json.next_renewal_display);
									$('#wcfmvm_change_next_renewal_form').unblock();
									$('#wcfmvm_change_next_renewal_form .wcfm-message').html( '<span class="wcicon-status-completed"></span>' + $response_json.message ).addClass('wcfm-success').slideDown();
									setTimeout(function() {
										$.colorbox.remove();
									}, 2000);
								}
							});
						});
					}
				});
			}
		});
	});
});