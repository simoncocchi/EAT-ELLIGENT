$wcfm_coupons_table = '';
$coupon_type = '';	
$coupon_vendor = '';
	
jQuery(document).ready(function($) {
	
	$wcfm_coupons_table = $('#wcfm-coupons').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 4 },
										{ responsivePriority: 2 },
										{ responsivePriority: 3 },
										{ responsivePriority: 6 },
										{ responsivePriority: 5 },
										{ responsivePriority: 1 }
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
										{ "targets": 3, "orderable" : false }, 
										{ "targets": 4, "orderable" : false }, 
										{ "targets": 5, "orderable" : false },
										{ "targets": 6, "orderable" : false } 
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action          = 'wcfm_ajax_controller',
				d.controller      = 'wcfm-coupons',
				d.coupon_type     = $coupon_type,
				d.coupon_vendor   = $coupon_vendor
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-coupons table refresh complete
				$( document.body ).trigger( 'updated_wcfm-coupons' );
			}
		}
	} );
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-coupons', function() {
		$.each(wcfm_coupons_screen_manage, function( column, column_val ) {
		  $wcfm_coupons_table.column(column).visible( false );
		} );
	});
	
} );