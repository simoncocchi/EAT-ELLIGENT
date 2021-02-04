jQuery(document).ready(function($) {
		
	$refunds_vendor = '';
	$status_type    = 'requested';
		
	$wcfm_refund_requests_table = $('#wcfm-refund-requests').DataTable( {
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
										{ responsivePriority: 5 },
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
										{ responsivePriority: 4 },
										{ responsivePriority: 6 }
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
				d.action         = 'wcfm_ajax_controller',
				d.controller     = 'wcfm-refund-requests',
				d.refund_vendor  = $refunds_vendor,
				d.status_type    = $status_type,
				d.transaction_id = GetURLParameter( 'request_id' ),
				d.order          = 'asc'
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-refund table refresh complete
				$( document.body ).trigger( 'updated_wcfm-refund-requests' );
			}
		}
	} );
	
	// Request Refunds Approve
	$('#wcfm_refund_requests_approve_button').click(function(event) {
	  event.preventDefault();
	  
	  $('#wcfm_refund_requests_approve_button').hide();
	  $('#wcfm_refund_requests_cancel_button').hide();
	  
		$('#wcfm-content').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action                      : 'wcfm_ajax_controller',
			controller                  : 'wcfm-refund-requests-approve',
			wcfm_refund_manage_form     : $('#wcfm_refund_requests_manage_form').serialize(),
			status                      : 'submit'
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
				wcfm_notification_sound.play();
				if($response_json.status) {
					$('#wcfm_refund_requests_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					$wcfm_refund_requests_table.ajax.reload();	
				} else {
					$('#wcfm_refund_requests_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
				}
				$('#wcfm-content').unblock();
				$('#wcfm_refund_requests_approve_button').show();
				$('#wcfm_refund_requests_cancel_button').show();
			}
		});
	});
	
	// Request Refunds Cancel
	$('#wcfm_refund_requests_cancel_button').click(function(event) {
	  event.preventDefault();
	  
	  $('#wcfm_refund_requests_approve_button').hide();
	  $('#wcfm_refund_requests_cancel_button').hide();
	  
		$('#wcfm-content').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action                      : 'wcfm_ajax_controller',
			controller                  : 'wcfm-refund-requests-cancel',
			wcfm_refund_manage_form     : $('#wcfm_refund_requests_manage_form').serialize(),
			status                      : 'submit'
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
				wcfm_notification_sound.play();
				if($response_json.status) {
					$('#wcfm_refund_requests_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					$wcfm_refund_requests_table.ajax.reload();	
				} else {
					$('#wcfm_refund_requests_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
				}
				$('#wcfm-content').unblock();
				$('#wcfm_refund_requests_approve_button').show();
				$('#wcfm_refund_requests_cancel_button').show();
			}
		});
	});
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$refunds_vendor = $('#dropdown_vendor').val();
			$wcfm_refund_requests_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	$('#dropdown_status_type').change(function() {
		$status_type = $(this).val();
		$wcfm_refund_requests_table.ajax.reload();
	});
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-refund-requests', function() {
		$.each(wcfm_refund_screen_manage, function( column, column_val ) {
		  $wcfm_refund_requests_table.column(column).visible( false );
		} );
	});
} );