jQuery(document).ready(function($) {
		
	$reviews_vendor = '';
		
	$wcfm_reviews_table = $('#wcfm-reviews').DataTable( {
		"processing": true,
		"serverSide": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"bFilter"   : false,
		"responsive": true,
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 5 },
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
										{ responsivePriority: 4 },
										{ responsivePriority: 6 },
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
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action          = 'wcfm_ajax_controller',
				d.controller      = 'wcfm-reviews',
				d.reviews_vendor  = $reviews_vendor,
				d.status_type     = GetURLParameter( 'reviews_status' )
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-reviews table refresh complete
				$( document.body ).trigger( 'updated_wcfm-reviews' );
			}
		}
	} );
	
	// Review Status Update
	$( document.body ).on( 'updated_wcfm-reviews', function() {
		$('.wcfm_review_status_update').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.review_status_update_confirm);
				if(rconfirm) reviewStatusUpdate($(this));
				return false;
			});
		});
	});
	
	function reviewStatusUpdate(item) {
		jQuery('#wcfm-reviews_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfmmp_reviews_status_update',
			reviewid : item.data('reviewid'),
			status   : item.data('status')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_reviews_table) $wcfm_reviews_table.ajax.reload();
				jQuery('#wcfm-reviews_wrapper').unblock();
			}
		});
	}
	
	// Review Delete
	$( document.body ).on( 'updated_wcfm-reviews', function() {
		$('.wcfm_review_dalete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.review_status_update_confirm);
				if(rconfirm) reviewDelete($(this));
				return false;
			});
		});
	});
	
	function reviewDelete(item) {
		jQuery('#wcfm-reviews_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfmmp_reviews_delete',
			reviewid : item.data('reviewid'),
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_reviews_table) $wcfm_reviews_table.ajax.reload();
				jQuery('#wcfm-reviews_wrapper').unblock();
			}
		});
	}
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$reviews_vendor = $('#dropdown_vendor').val();
			$wcfm_reviews_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-reviews', function() {
		$.each(wcfm_reviews_screen_manage, function( column, column_val ) {
		  $wcfm_reviews_table.column(column).visible( false );
		} );
	});
} );