$product_type = '';	
$product_cat = '';
$product_taxonomy = {};
$product_vendor = '';

jQuery(document).ready(function($) {
	
	$wcfm_sell_items_catalog_table = $('#wcfm-sell_items_catalog').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
			              { responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 2 },
										{ responsivePriority: 4 },
										{ responsivePriority: 5 },
										{ responsivePriority: 6 },
										{ responsivePriority: 1 }
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
										{ "targets": 3, "orderable" : true }, 
										{ "targets": 4, "orderable" : false }, 
										{ "targets": 5, "orderable" : false },
										{ "targets": 6, "orderable" : false },
										{ "targets": 7, "orderable" : false }
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action           = 'wcfm_ajax_controller',
				d.controller       = 'wcfm-sell-items-catalog',
				d.product_type     = $product_type,
				d.product_cat      = $product_cat,
				d.product_taxonomy = $product_taxonomy
			},
			"complete" : function () {
				initiateTip();
				if (typeof intiateWCFMuQuickEdit !== 'undefined' && $.isFunction(intiateWCFMuQuickEdit)) intiateWCFMuQuickEdit();
				
				// Fire wcfm-sell_items_catalog table refresh complete
				$( document.body ).trigger( 'updated_wcfm-sell_items_catalog' );
			}
		}
	} );
	
	if( $('#dropdown_product_type').length > 0 ) {
		$('#dropdown_product_type').on('change', function() {
		  $product_type = $('#dropdown_product_type').val();
		  $wcfm_sell_items_catalog_table.ajax.reload();
		});
	}
	
	if( $('#dropdown_product_cat').length > 0 ) {
		$('#dropdown_product_cat').on('change', function() {
			$product_cat = $('#dropdown_product_cat').val();
			$wcfm_sell_items_catalog_table.ajax.reload();
		}).select2( $wcfm_taxonomy_select_args );
	}
	
	if( $('.dropdown_product_custom_taxonomy').length > 0 ) {
		$('.dropdown_product_custom_taxonomy').each(function() {
			$(this).on('change', function() {
				$product_taxonomy[$(this).data('taxonomy')] = $(this).val();
				$wcfm_sell_items_catalog_table.ajax.reload();
			}).select2();
		});
	}
	
	// Add to My Store
	$( document.body ).on( 'updated_wcfm-sell_items_catalog', function() {
		$('.wcfm_sell_this_item').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.sell_this_item_confirm);
				if(rconfirm) wcfmmp_product_multivendor_clone($(this));
				return false;
			});
		});
	});
	
	function wcfmmp_product_multivendor_clone(item) {
		jQuery('#wcfm-sell_items_catalog_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action     : 'wcfmmp_product_multivendor_clone',
			product_id : item.data('proid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_sell_items_catalog_table) $wcfm_sell_items_catalog_table.ajax.reload();
				jQuery('#wcfm-sell_items_catalog_wrapper').unblock();
			}
		});
	}
	
	// Bulk Add to My Store
	$('.bulk_action_checkbox_all').click(function() {
		if( $(this).is(':checked') ) {
			$('.bulk_action_checkbox_all').attr( 'checked', true );
			$('.bulk_action_checkbox_single').attr( 'checked', true );
		}	else {
			$('.bulk_action_checkbox_all').attr( 'checked', false );
			$('.bulk_action_checkbox_single').attr( 'checked', false );
		}
	});
		
	$('#wcfm_bulk_add_to_my_store, #wcfm_bulk_add_to_my_store_bottom').click( function( event ) {
		event.preventDefault();
		
		$selected_products = [];
		$('.bulk_action_checkbox_single').each(function() {
		  if( $(this).is(':checked') ) {
		  	$selected_products.push( $(this).val() );
		  }
		});
		
		if ( $selected_products.length === 0 ) {
			alert( wcfm_dashboard_messages.bulk_no_itm_selected );
			return false;
		}
		
		jQuery('#wcfm-sell_items_catalog_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action      : 'wcfmmp_product_multivendor_bulk_clone',
			product_ids : $selected_products
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_sell_items_catalog_table) $wcfm_sell_items_catalog_table.ajax.reload();
				jQuery('#wcfm-sell_items_catalog_wrapper').unblock();
			}
		});
		
		return false;
	} );
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-sell_items_catalog', function() {
		$.each(wcfm_sell_items_catalog_screen_manage, function( column, column_val ) {
		  $wcfm_sell_items_catalog_table.column(column).visible( false );
		} );
	});
	
} );