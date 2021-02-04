$article_cat = '';
$article_vendor = '';

jQuery(document).ready(function($) {
	
	$wcfm_articles_table = $('#wcfm-articles').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
			              { responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
										{ responsivePriority: 4 },
										{ responsivePriority: 5 },
										{ responsivePriority: 2 },
										{ responsivePriority: 1 },
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
				d.action     = 'wcfm_ajax_controller',
				d.controller = 'wcfm-articles',
				d.article_cat      = $article_cat,
				d.article_vendor   = $article_vendor,
				d.article_status   = GetURLParameter( 'article_status' )
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-articles table refresh complete
				$( document.body ).trigger( 'updated_wcfm-articles' );
			}
		}
	} );
	
	if( $('.dropdown_article_cat').length > 0 ) {
		$('.dropdown_article_cat').on('change', function() {
			$article_cat = $('.dropdown_article_cat').val();
			$wcfm_articles_table.ajax.reload();
		});
	}
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$article_vendor = $('#dropdown_vendor').val();
			$wcfm_articles_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Delete Article
	$( document.body ).on( 'updated_wcfm-articles', function() {
		$('.wcfm_article_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.article_delete_confirm);
				if(rconfirm) deleteWCFMArticle($(this));
				return false;
			});
		});
	});
	
	function deleteWCFMArticle(item) {
		jQuery('#wcfm-articles_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action    : 'delete_wcfm_article',
			articleid : item.data('articleid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_articles_table) $wcfm_articles_table.ajax.reload();
				jQuery('#wcfm-articles_wrapper').unblock();
			}
		});
	}
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-articles', function() {
		$.each(wcfm_articles_screen_manage, function( column, column_val ) {
		  $wcfm_articles_table.column(column).visible( false );
		} );
	});
	
} );