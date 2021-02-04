$wcfm_applications_table = '';
	
jQuery(document).ready(function($) {
	
	$wcfm_applications_table = $('#wcfm-applications').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 4 },
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 5 },
										{ responsivePriority: 4 },
										{ responsivePriority: 3 },
										{ responsivePriority: 2 },
										{ responsivePriority: 7 },
										{ responsivePriority: 1 }
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
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action       = 'wcfm_ajax_controller',
				d.controller   = 'wcfm-applications',
				d.listing_id   = GetURLParameter( 'listing_id' ),
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-applications table refresh complete
				$( document.body ).trigger( 'updated_wcfm-applications' );
			}
		}
	} );
	
	// Mark Featured - 5.4.4
	$( document.body ).on( 'updated_wcfm-applications', function() {
		$('.wcfm_application_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				jQuery('#wcfm_applications_listing_expander').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
				var data = {
					action          : 'wcfm_application_delete',
					applicationid   : $(this).data('applicationid'),
				}	
				jQuery.ajax({
					type:		'POST',
					url: wcfm_params.ajax_url,
					data: data,
					success:	function(response) {
						$wcfm_applications_table.ajax.reload();
						jQuery('#wcfm_applications_listing_expander').unblock();
					}
				});
				return false;
			});
		});
	});
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-applications', function() {
		$.each(wcfm_applications_screen_manage, function( column, column_val ) {
		  $wcfm_applications_table.column(column).visible( false );
		} );
	});
	
} );