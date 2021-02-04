jQuery(document).ready(function($) {
		
	$status_type = '0';
		
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
										{ responsivePriority: 3 },
										{ responsivePriority: 1 },
										{ responsivePriority: 4 },
										{ responsivePriority: 5 }
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
										{ "targets": 3, "orderable" : false }, 
										{ "targets": 4, "orderable" : false },
										{ "targets": 5, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action         = 'wcfm_ajax_controller',
				d.controller     = 'wcfm-withdrawal-requests',
				d.transaction_id = GetURLParameter( 'transaction_id' ),
				d.order          = 'desc',
				d.status_type    = $status_type
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-withdrawal table refresh complete
				$( document.body ).trigger( 'updated_wcfm-withdrawal-requests' );
			}
		}
	} );
	
	// Request Withdrawals Approve
	$('#wcfm_withdrawal_requests_approve_button').click(function(event) {
	  event.preventDefault();
	  
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
				$('#wcfm-content').unblock();
			}
		});
	});
	
	// Request Withdrawals Cancel
	$('#wcfm_withdrawal_requests_cancel_button').click(function(event) {
	  event.preventDefault();
	  
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
				$('#wcfm-content').unblock();
			}
		});
	});
	
	$('#dropdown_status_type').change(function() {
		$status_type = $(this).val();
		$wcfm_withdrawal_requests_table.ajax.reload();
	});
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
} );