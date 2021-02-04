$wcfm_orders_table = '';
$order_status = '';	
$filter_by_date = '';
$order_product = '';
$commission_status = '';
$order_vendor = '';
$delivery_boy = '';
var orderTableRefrsherTime = '';

jQuery(document).ready(function($) {
		
	$order_vendor = GetURLParameter( 'order_vendor' );
		
	// Dummy Mark Complete Dummy
	$( document.body ).on( 'updated_wcfm-orders', function() {
		$('.wcfm_order_mark_complete_dummy').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				alert( wcfm_dashboard_messages.wcfmu_upgrade_notice );
				return false;
			});
		});
	});
	
	// Invoice Dummy
	$( document.body ).on( 'updated_wcfm-orders', function() {
		$('.wcfm_pdf_invoice_dummy').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				alert( wcfm_dashboard_messages.pdf_invoice_upgrade_notice );
				return false;
			});
		});
	});
	
	// Invoice dummy - vendor
	$( document.body ).on( 'updated_wcfm-orders', function() {
		$('.wcfm_pdf_invoice_vendor_dummy').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				alert( wcfm_dashboard_messages.wcfmu_missing_feature );
				return false;
			});
		});
	});
	
	// Mark Shipped dummy - vendor
	$( document.body ).on( 'updated_wcfm-orders', function() {
		$('.wcfm_wcvendors_order_mark_shipped_dummy').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				alert( wcfm_dashboard_messages.wcfmu_missing_feature );
				return false;
			});
		});
	});
	
	if( dataTables_config.is_allow_hidden_export ) {
		$wcfm_datatable_button_args = [
																		{
																			extend: 'print',
																		},
																		{
																			extend: 'pdfHtml5',
																			orientation: 'landscape',
																			pageSize: 'LEGAL'
																		},
																		{
																			extend: 'excelHtml5',
																		}, 
																		{
																			extend: 'csv',
																		}
																	];
	}
	
	$wcfm_orders_table = $('#wcfm-orders').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"bFilter"   : wcfm_datatable_columns.bFilter,
		"pageLength": parseInt(dataTables_config.pageLength),
		"dom"       : 'Bfrtip',
		"language"  : $.parseJSON(dataTables_language),
    "buttons"   : $wcfm_datatable_button_args,
		"columns"   : $.parseJSON(wcfm_datatable_columns.priority),
		"columnDefs": $.parseJSON(wcfm_datatable_columns.defs),
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action            = 'wcfm_ajax_controller',
				d.controller        = 'wcfm-orders',
				d.order_status      = GetURLParameter( 'order_status' ),
				d.filter_date_form  = $filter_date_form,
				d.filter_date_to    = $filter_date_to,  
				d.order_product     = $order_product,  
				d.commission_status = $commission_status,
				d.order_vendor      = $order_vendor,
				d.delivery_boy      = $delivery_boy
			},
			"complete" : function () {
				initiateTip();
				
				$('.show_order_items').click(function(e) {
					e.preventDefault();
					$(this).next('div.order_items').toggleClass( "order_items_visible" );
					return false;
				});
				
				// Fire wcfm-orders table refresh complete
				$( document.body ).trigger( 'updated_wcfm-orders' );
			}
		}
	} );
	
	$( document.body ).on( 'wcfm-date-range-refreshed', function() {
		$wcfm_orders_table.ajax.reload();
	});
	
	// Product Filter
	if( $('#order_product').length > 0 ) {
		$('#order_product').on('change', function() {
		  $order_product = $('#order_product').val();
		  $wcfm_orders_table.ajax.reload();
		}).select2( $wcfm_product_select_args );
	}
	
	// Commission Status Filter
	if( $('#commission-status').length > 0 ) {
		$('#commission-status').on('change', function() {
			$commission_status = $('#commission-status').val();
			$wcfm_orders_table.ajax.reload();
		});
	}
	
	// Vendor Filter
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$order_vendor = $('#dropdown_vendor').val();
			$wcfm_orders_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Delivery Boy Filter
	if( $('#wcfm_delivery_boy').length > 0 ) {
		$('#wcfm_delivery_boy').on('change', function() {
			$delivery_boy = $('#wcfm_delivery_boy').val();
			$wcfm_orders_table.ajax.reload();
		});
	}
	
	// Order Table auto Refresher
	function orderTableRefrsher() {
		if( wcfm_orders_auto_refresher.is_allow ) {
			clearTimeout(orderTableRefrsherTime);
			orderTableRefrsherTime = setTimeout(function() {
				$wcfm_orders_table.ajax.reload();
				orderTableRefrsher();
			}, wcfm_orders_auto_refresher.duration  );
		}
	}
	orderTableRefrsher();
	
	// Mark Order as Completed
	$( document.body ).on( 'updated_wcfm-orders', function() {
		$('.wcfm_order_mark_complete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm( wcfm_dashboard_messages.order_mark_complete_confirm );
				if(rconfirm) markCompleteWCFMOrder($(this));
				return false;
			});
		});
	});
	
	function markCompleteWCFMOrder(item) {
		clearTimeout(orderTableRefrsherTime);
		$('#wcfm-orders_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfm_order_mark_complete',
			orderid : item.data('orderid')
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				$wcfm_orders_table.ajax.reload();
				$('#wcfm-orders_wrapper').unblock();
				orderTableRefrsher();
			}
		});
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-orders', function() {
		$.each(wcfm_orders_screen_manage, function( column, column_val ) {
		  $wcfm_orders_table.column(column).visible( false );
		} );
	});
	
	// Hidden Column
	$( document.body ).on( 'updated_wcfm-orders', function() {
		$.each(wcfm_orders_screen_manage_hidden, function( column, column_val ) {
		  $wcfm_orders_table.column(column).visible( false );
		} );
	});
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
} );