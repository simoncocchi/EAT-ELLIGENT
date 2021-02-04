jQuery(document).ready(function($) {
		
	$media_vendor = '';
		
	$wcfm_media_table = $('#wcfm-media').DataTable( {
		"processing": true,
		"serverSide": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"bFilter"   : false,
		"responsive": true,
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 5 },
										{ responsivePriority: 4 },
										{ responsivePriority: 2 },
										{ responsivePriority: 3 },
										{ responsivePriority: 1 }
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
										{ "targets": 3, "orderable" : false },
										{ "targets": 4, "orderable" : false },
										{ "targets": 5, "orderable" : false },
										{ "targets": 6, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action          = 'wcfm_ajax_controller',
				d.controller      = 'wcfm-media',
				d.media_vendor    = $media_vendor
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-media table refresh complete
				$( document.body ).trigger( 'updated_wcfm-media' );
			}
		}
	} );
	
	// Media Delete
	$( document.body ).on( 'updated_wcfm-media', function() {
		$('.wcfm_media_dalete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.review_status_update_confirm);
				if(rconfirm) mediaDelete($(this));
				return false;
			});
		});
	});
	
	function mediaDelete(item) {
		jQuery('#wcfm-media_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfmmp_media_delete',
			mediaid : item.data('mediaid'),
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_media_table) $wcfm_media_table.ajax.reload();
				jQuery('#wcfm-media_wrapper').unblock();
			}
		});
	}
	
	// Media Bulk Delete
	$('.bulk_action_checkbox_all').click(function() {
		if( $(this).is(':checked') ) {
			$('.bulk_action_checkbox_all').attr( 'checked', true );
			$('.bulk_action_checkbox_single').attr( 'checked', true );
		}	else {
			$('.bulk_action_checkbox_all').attr( 'checked', false );
			$('.bulk_action_checkbox_single').attr( 'checked', false );
		}
	});
		
	// Message mark read in bulk
	$('#wcfm_bulk_mark_delete').click( function( event ) {
		event.preventDefault();
		
		$('#wcfm-media_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
		$selected_media = [];
		$('.bulk_action_checkbox_single').each(function() {
		  if( $(this).is(':checked') ) {
		  	$selected_media.push( $(this).val() );
		  }
		});
		
		if ( $selected_media.length === 0 ) {
			alert( wcfm_dashboard_messages.wcfm_bulk_action_no_option );
			$('#wcfm-media_wrapper').unblock();
			return false;
		}
		
		var rconfirm = confirm( wcfm_dashboard_messages.wcfm_bulk_action_confirm );
		if(rconfirm) { 
			var data = {
				action            : 'wcfmmp_bulk_media_delete',
				selected_media : $selected_media
			}	
			
			$.ajax({
				type    :		'POST',
				url     : wcfm_params.ajax_url,
				data    : data,
				success :	function(response) {  
					if( response ) {
						if($wcfm_media_table) $wcfm_media_table.ajax.reload();
					}
					$('#wcfm-media_wrapper').unblock();
				}
			});
		} else {
			$('#wcfm-media_wrapper').unblock();
		}
	});
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$media_vendor = $('#dropdown_vendor').val();
			$wcfm_media_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-media', function() {
		$.each(wcfm_media_screen_manage, function( column, column_val ) {
		  $wcfm_media_table.column(column).visible( false );
		} );
	});
} );