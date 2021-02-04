jQuery(document).ready(function($) {
	// Booking Status Update
	$('#wcfm_modify_booking_status').click(function(event) {
		event.preventDefault();
		modifyWCFMBookingStatus();
		return false;
	});
	
	// Booking Confirm
	$('#wcfm_booking_confirmed_button').click(function(event) {
		event.preventDefault();
		var rconfirm = confirm(wcfm_dashboard_messages.booking_mark_complete_confirm);
		if( rconfirm ) {
			$('#wcfm_booking_status').val('confirmed');
			modifyWCFMBookingStatus();
			$('.wcfm_booking_confirmed_cancel_wrapper').remove();
		}
		return false;
	});
	
	// Booking Cancelled
	$('#wcfm_booking_declined_button').click(function(event) {
		event.preventDefault();
		var rconfirm = confirm(wcfm_dashboard_messages.booking_mark_decline_confirm);
		if( rconfirm ) {
			$('#wcfm_booking_status').val('cancelled');
			modifyWCFMBookingStatus();
			$('.wcfm_booking_confirmed_cancel_wrapper').remove();
		}
		return false;
	});
		
	function modifyWCFMBookingStatus() {
		$('#bookings_details_general_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action         : 'wcfm_modify_booking_status',
			booking_status : $('#wcfm_booking_status').val(),
			booking_id     : $('#wcfm_modify_booking_status').data('bookingid')
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
				if($response_json.status) {
					wcfm_notification_sound.play();
					$('#wcfm_booking_status_update_wrapper .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" );
				}
				$('#bookings_details_general_expander').unblock();
			}
		});
	}
	
	
	// Subscription BillingSchedule Update
	$('#wcfm_booking_schedule_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		$wcfm_is_valid_form = true;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_wcb_schedule_update_form') );
		$is_valid = $wcfm_is_valid_form;
	  
	  if($is_valid) {
			$('#bookings_details_booking_expander').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                                : 'wcfm_ajax_controller',
				controller                            : 'wcfm-booking-schedule-manage',
				wcfm_wcb_schedule_update_form         : $('#wcfm_wcb_schedule_update_form').serialize(),
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#bookings_details_booking_expander .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#bookings_details_booking_expander .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#bookings_details_booking_expander').unblock();
				}
			});	
		}
	});
	
	$( "#booking_start_date" ).datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
		onClose: function( selectedDate ) {
			$( "#booking_end_date" ).datepicker( "option", "minDate", selectedDate );
		}
	});
	$( "#booking_end_date" ).datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
		onClose: function( selectedDate ) {
			$( "#booking_start_date" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
	
});