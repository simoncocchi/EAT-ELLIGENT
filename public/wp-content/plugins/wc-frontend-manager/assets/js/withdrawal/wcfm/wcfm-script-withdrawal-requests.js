jQuery(document).ready(function($) {
		
	$status_type = $('#dropdown_status_type').val();
	$withdrawal_vendor = '';
		
	$wcfm_withdrawal_requests_table = $('#wcfm-withdrawal-requests').DataTable( {
		"processing": true,
		"serverSide": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"bFilter"   : false,
		"dom"       : 'Bfrtip',
		"responsive": true,
		"language"  : $.parseJSON(dataTables_language),
		"buttons"   : $wcfm_datatable_button_args,
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 5 },
										{ responsivePriority: 6 },
										{ responsivePriority: 3 },
										{ responsivePriority: 7 },
										{ responsivePriority: 4 }
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
										{ "targets": 3, "orderable" : false }, 
										{ "targets": 4, "orderable" : false },
										{ "targets": 5, "orderable" : false },
										{ "targets": 6, "orderable" : false },
										{ "targets": 7, "orderable" : false },
										{ "targets": 8, "orderable" : false },
										{ "targets": 9, "orderable" : false },
										{ "targets": 10, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action              = 'wcfm_ajax_controller',
				d.controller          = 'wcfm-withdrawal-requests',
				d.transaction_id      = GetURLParameter( 'transaction_id' ),
				d.status_type         = $status_type,
				d.withdrawal_vendor   = $withdrawal_vendor,
				d.start_date          = $filter_date_form,
				d.end_date            = $filter_date_to
				d.order               = 'desc'
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-withdrawal table refresh complete
				$( document.body ).trigger( 'updated_wcfm-withdrawal-requests' );
			}
		}
	} );
	
	$('.bulk_action_checkbox_all').click(function() {
		if( $(this).is(':checked') ) {
			$('.bulk_action_checkbox_all').attr( 'checked', true );
			$('.select_withdrawal_requests').attr( 'checked', true );
		}	else {
			$('.bulk_action_checkbox_all').attr( 'checked', false );
			$('.select_withdrawal_requests').attr( 'checked', false );
		}
	});
	
	// Request Withdrawals Approve
	$('#wcfm_withdrawal_requests_approve_button').click(function(event) {
	  event.preventDefault();
	  $('#wcfm_withdrawal_requests_approve_button').hide();
	  $('#wcfm_withdrawal_requests_cancel_button').hide();
	  $('.bulk_action_checkbox_all').attr( 'checked', false );
	  
		$('#wcfm-content').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action                      : 'wcfm_ajax_controller',
			controller                  : 'wcfm-withdrawal-requests-approve',
			wcfm_withdrawal_manage_form : $('#wcfm_withdrawal_requests_manage_form').serialize(),
			status                      : 'submit'
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
				wcfm_notification_sound.play();
				if($response_json.status) {
					$('#wcfm_withdrawal_requests_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					$wcfm_withdrawal_requests_table.ajax.reload();	
				} else {
					$('#wcfm_withdrawal_requests_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
				}
				wcfmMessageHide();
				$('#withdraw_note').val('');
				$('#wcfm-content').unblock();
				$('#wcfm_withdrawal_requests_approve_button').show();
				$('#wcfm_withdrawal_requests_cancel_button').show();
			}
		});
	});
	
	// Request Withdrawals Cancel
	$('#wcfm_withdrawal_requests_cancel_button').click(function(event) {
	  event.preventDefault();
	  $('#wcfm_withdrawal_requests_approve_button').hide();
	  $('#wcfm_withdrawal_requests_cancel_button').hide();
		$('#wcfm-content').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action                      : 'wcfm_ajax_controller',
			controller                  : 'wcfm-withdrawal-requests-cancel',
			wcfm_withdrawal_manage_form : $('#wcfm_withdrawal_requests_manage_form').serialize(),
			status                      : 'submit'
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
				wcfm_notification_sound.play();
				if($response_json.status) {
					$('#wcfm_withdrawal_requests_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					$wcfm_withdrawal_requests_table.ajax.reload();	
				} else {
					$('#wcfm_withdrawal_requests_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
				}
				wcfmMessageHide();
				$('#withdraw_note').val('');
				$('#wcfm-content').unblock();
				$('#wcfm_withdrawal_requests_approve_button').show();
				$('#wcfm_withdrawal_requests_cancel_button').show();
			}
		});
	});
	
	$( document.body ).on( 'wcfm-date-range-refreshed', function() {
		$wcfm_withdrawal_requests_table.ajax.reload();
	});
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$withdrawal_vendor = $('#dropdown_vendor').val();
			$wcfm_withdrawal_requests_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	$('#dropdown_status_type').change(function() {
		$status_type = $(this).val();
		$wcfm_withdrawal_requests_table.ajax.reload();
	});
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-withdrawal-requests', function() {
		$.each(wcfm_withdrawal_request_screen_manage, function( column, column_val ) {
		  $wcfm_withdrawal_requests_table.column(column).visible( false );
		} );
	});
} );