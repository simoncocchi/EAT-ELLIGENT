$wcfm_listings_table = '';
$listing_vendor = '';
	
jQuery(document).ready(function($) {
	
	$wcfm_listings_table = $('#wcfm-listings').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 4 },
										{ responsivePriority: 3 },
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
										{ responsivePriority: 3 },
										{ responsivePriority: 6 },
										{ responsivePriority: 5 },
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
										{ "targets": 9, "orderable" : false },
										{ "targets": 10, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action     = 'wcfm_ajax_controller',
				d.controller = 'wcfm-listings',
				d.listing_status   = GetURLParameter( 'listing_status' ),
				d.listing_vendor   = $listing_vendor
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-listings table refresh complete
				$( document.body ).trigger( 'updated_wcfm-listings' );
			}
		}
	} );
	
	// Mark Featured - 5.4.4
	$( document.body ).on( 'updated_wcfm-listings', function() {
		$('.wcfm_listing_featured').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				jQuery('#wcfm_listings_listing_expander').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
				var data = {
					action   : 'wcfm_listing_featured',
					listid   : $(this).data('listid'),
					featured : $(this).data('featured')
				}	
				jQuery.ajax({
					type:		'POST',
					url: wcfm_params.ajax_url,
					data: data,
					success:	function(response) {
						$wcfm_listings_table.ajax.reload();
						jQuery('#wcfm_listings_listing_expander').unblock();
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
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$listing_vendor = $('#dropdown_vendor').val();
			$wcfm_listings_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-listings', function() {
		$.each(wcfm_listings_screen_manage, function( column, column_val ) {
		  $wcfm_listings_table.column(column).visible( false );
		} );
	});
	
} );