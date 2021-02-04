jQuery(document).ready(function($) {
		
	$reference_type = '';
	$status_type = '';
	
	$wcfm_ledger_table = $('#wcfm-ledger').DataTable( {
		"processing"     : true,
		"serverSide"     : true,
		"aFilter"        : false,
		"bFilter"        : false,
		"responsive"     : true,
		"deferRender"    : true,
		"scrollY"        : 500,
		"scrollCollapse" : true,
		"scroller"       : true,
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 2 },
										{ responsivePriority: 4 },
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
										{ "targets": 3, "orderable" : false },
										{ "targets": 4, "orderable" : false },
										{ "targets": 4, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action          = 'wcfm_ajax_controller',
				d.controller      = 'wcfm-ledger',
				d.status_type     = $status_type,
				d.type            = $reference_type
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-ledger table refresh complete
				$( document.body ).trigger( 'updated_wcfm-ledger' );
			}
		}
	} );
	
	if( $('#dropdown_status_type').length > 0 ) {
		$('#dropdown_status_type').on('change', function() {
			$status_type = $('#dropdown_status_type').val();
			$wcfm_ledger_table.ajax.reload();
		});
	}
	
	if( $('#dropdown_reference_type').length > 0 ) {
		$('#dropdown_reference_type').on('change', function() {
			$reference_type = $('#dropdown_reference_type').val();
			$wcfm_ledger_table.ajax.reload();
		});
	}
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		//$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
} );