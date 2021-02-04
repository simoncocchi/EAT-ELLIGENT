jQuery(document).ready(function($) {
		
	$wcfm_notice_table = $('#wcfm-notice').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
										{ "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action       = 'wcfm_ajax_controller',
				d.controller   = 'wcfm-notices'
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-groups table refresh complete
				$( document.body ).trigger( 'updated_wcfm-notice' );
			}
		}
	} );
	
		// Archive knowledgebase
	$( document.body ).on( 'updated_wcfm-notice', function() {
		$('.wcfm_notice_archive').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
					jQuery('#wcfm_notice_listing_expander').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
				var data = {
					action   : 'archive_wcfm_notice',
					noticeid : $(this).data('noticeid')
				}	
				jQuery.ajax({
					type:		'POST',
					url: wcfm_params.ajax_url,
					data: data,
					success:	function(response) {
						if($wcfm_notice_table) $wcfm_notice_table.ajax.reload();
						jQuery('#wcfm_notice_listing_expander').unblock();
					}
				});
				return false;
			});
		});
	});
	
	// Publish knowledgebase
	$( document.body ).on( 'updated_wcfm-notice', function() {
		$('.wcfm_notice_publish').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
					jQuery('#wcfm_notice_listing_expander').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
				var data = {
					action   : 'publish_wcfm_notice',
					noticeid : $(this).data('noticeid')
				}	
				jQuery.ajax({
					type:		'POST',
					url: wcfm_params.ajax_url,
					data: data,
					success:	function(response) {
						if($wcfm_notice_table) $wcfm_notice_table.ajax.reload();
						jQuery('#wcfm_notice_listing_expander').unblock();
					}
				});
				return false;
			});
		});
	});
	
	// Delete notice
	$( document.body ).on( 'updated_wcfm-notice', function() {
		$('.wcfm_notice_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm("Are you sure and want to delete this 'Topic'?\nYou can't undo this action ...");
				if(rconfirm) deleteWCFMNotice($(this));
				return false;
			});
		});
	});
	
	function deleteWCFMNotice(item) {
		jQuery('#wcfm_notice_listing_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action   : 'delete_wcfm_notice',
			noticeid : item.data('noticeid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_notice_table) $wcfm_notice_table.ajax.reload();
				jQuery('#wcfm_notice_listing_expander').unblock();
			}
		});
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-notice', function() {
		$.each(wcfm_notices_screen_manage, function( column, column_val ) {
		  $wcfm_notice_table.column(column).visible( false );
		} );
	});
} );