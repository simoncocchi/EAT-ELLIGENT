jQuery(document).ready(function($) {
		
	$start_date = '';
	$end_date = '';
	$status_type = $('#dropdown_status_type').val();
	
	$wcfm_payments_table = $('#wcfm-payments').DataTable( {
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
										{ responsivePriority: 5 },
										{ responsivePriority: 4 },
										{ responsivePriority: 1 },
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
										{ responsivePriority: 3 },
										{ responsivePriority: 6 },
										{ responsivePriority: 2 }
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
				d.action            = 'wcfm_ajax_controller',
				d.controller        = 'wcfm-payments',
				d.transaction_id    = GetURLParameter( 'transaction_id' )
				d.start_date        = $filter_date_form,
				d.end_date          = $filter_date_to
				d.status_type       = $status_type
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-payments table refresh complete
				$( document.body ).trigger( 'updated_wcfm-payments' );
			}
		}
	} );
	
	$( document.body ).on( 'wcfm-date-range-refreshed', function() {
		$('.trans_start_date').text($filter_date_form);
		$('.trans_end_date').text($filter_date_to);
		$wcfm_payments_table.ajax.reload();
	});
	
	$('#dropdown_status_type').change(function() {
		$status_type = $(this).val();
		$wcfm_payments_table.ajax.reload();
	});
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-payments', function() {
		$.each(wcfm_payments_screen_manage, function( column, column_val ) {
		  $wcfm_payments_table.column(column).visible( false );
		} );
	});
} );