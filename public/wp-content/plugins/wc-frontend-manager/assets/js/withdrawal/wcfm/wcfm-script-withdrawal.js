jQuery(document).ready(function($) {
		
	$wcfm_withdrawal_table = $('#wcfm-withdrawal').DataTable( {
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
										{ responsivePriority: 3 },
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
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action       = 'wcfm_ajax_controller',
				d.controller   = 'wcfm-withdrawal',
				d.start_date   = $filter_date_form,
				d.end_date     = $filter_date_to
				d.order        = 'asc'
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-withdrawal table refresh complete
				$( document.body ).trigger( 'updated_wcfm-withdrawal' );
			}
		}
	} );
	
	$('.bulk_action_checkbox_all').click(function() {
		if( $(this).is(':checked') ) {
			$('.bulk_action_checkbox_all').attr( 'checked', true );
			$('.select_withdrawal').attr( 'checked', true );
		}	else {
			$('.bulk_action_checkbox_all').attr( 'checked', false );
			$('.select_withdrawal').attr( 'checked', false );
		}
	});
	
	// Request Withdrawals
	$('#wcfm_withdrawal_request_button').click(function(event) {
	  event.preventDefault();
	  $('#wcfm_withdrawal_request_button').hide();
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
			controller                  : 'wcfm-withdrawal-request',
			wcfm_withdrawal_manage_form : $('#wcfm_withdrawal_manage_form').serialize(),
			status                      : 'submit'
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
				wcfm_notification_sound.play();
				if($response_json.status) {
					$('#wcfm_withdrawal_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					$wcfm_withdrawal_table.ajax.reload();	
				} else {
					$('#wcfm_withdrawal_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
				}
				wcfmMessageHide();
				$('#wcfm-content').unblock();
				$('#wcfm_withdrawal_request_button').show();
			}
		});
	});
	
	$( document.body ).on( 'wcfm-date-range-refreshed', function() {
		$wcfm_withdrawal_table.ajax.reload();
	});
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-withdrawal', function() {
		$.each(wcfm_withdrawal_screen_manage, function( column, column_val ) {
		  $wcfm_withdrawal_table.column(column).visible( false );
		} );
	});
} );