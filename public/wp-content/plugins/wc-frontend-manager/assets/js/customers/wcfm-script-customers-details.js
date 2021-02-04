$wcfm_customers_orders_table = '';
$wcfm_customers_bookings_table = 
$wcfm_customers_appointments_table = '';

jQuery(document).ready(function($) {
		
	var customer_id = $('input[name="wcfm_customer_id"]').val();
	
	if( $('#dropdown_customer').length > 0 ) {
		$('#dropdown_customer').on('change', function() {
			var data = {
				action                  : 'customer_details_change_url',
				customer_details_change : $('#dropdown_customer').val()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.redirect) {
						window.location = $response_json.redirect;
					}
				}
			});
		}).select2();
	}	
	
	$wcfm_customers_orders_table = $('#wcfm-customers-details-orders').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"bFilter"   : false,
		"dom"       : 'Bfrtip',
		"language"  : $.parseJSON(dataTables_language),
    "buttons"   : $wcfm_datatable_button_args,
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 4 },
										{ responsivePriority: 5 },
										{ responsivePriority: 2 },
										{ responsivePriority: 3 },
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false },
										{ "targets": 3, "orderable" : false },
										{ "targets": 4, "orderable" : false }, 
										{ "targets": 5, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action       = 'wcfm_ajax_controller',
				d.controller   = 'wcfm-customers-details-orders',
				d.customer_id  = customer_id
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-appointments table refresh complete
				$( document.body ).trigger( 'updated_wcfm_customers_orders' );
			}
		}
	} );
	
		// Mark Order as Completed
	$( document.body ).on( 'updated_wcfm_customers_orders', function() {
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
		$('#wcfm_customers_orders_listing_expander').block({
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
				$wcfm_customers_orders_table.ajax.reload();
				$('#wcfm_customers_orders_listing_expander').unblock();
			}
		});
	}
	
	// Invoice Dummy
	$( document.body ).on( 'updated_wcfm_customers_orders', function() {
		$('.wcfm_pdf_invoice_dummy').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				alert( wcfm_dashboard_messages.pdf_invoice_upgrade_notice );
				return false;
			});
		});
	});
	
	// PDF Invoice
	$( document.body ).on( 'updated_wcfm_customers_orders', function() {
		$('.wcfm_pdf_invoice').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				downloadPDFInvoiceWCFMOrder($(this));
				return false;
			});
		});
	});
	
	function downloadPDFInvoiceWCFMOrder(item) {
		if (wcfm_params.ajax_url.indexOf("?") != -1) {
			url = wcfm_params.ajax_url + '&action=wcfm_order_pdf_invoice&template_type=invoice&order_id='+item.data('orderid');
		} else {
			url = wcfm_params.ajax_url + '?action=wcfm_order_pdf_invoice&template_type=invoice&order_id='+item.data('orderid')
		}
		window.open(url, '_blank');
	}
	
	// PDF Packing Slip
	$( document.body ).on( 'updated_wcfm_customers_orders', function() {
		$('.wcfm_pdf_packing_slip').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				downloadPDFPackingSlipWCFMOrder($(this));
				return false;
			});
		});
	});
	
	function downloadPDFPackingSlipWCFMOrder(item) {
		if (wcfm_params.ajax_url.indexOf("?") != -1) {
			url = wcfm_params.ajax_url + '&action=wcfm_order_pdf_packing_slip&template_type=packing_slip&order_id='+item.data('orderid');
		} else {
			url = wcfm_params.ajax_url + '?action=wcfm_order_pdf_packing_slip&template_type=packing-slip&order_id='+item.data('orderid')
		}
		window.open(url, '_blank');
	}
	
	
	//////////////////////////// Bookings Section //////////////////////////////
	
	$wcfm_customers_bookings_table = $('#wcfm-customers-details-bookings').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"bFilter"   : false,
		"dom"       : 'Bfrtip',
		"language"  : $.parseJSON(dataTables_language),
    "buttons"   : $wcfm_datatable_button_args,
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 4 },
										{ responsivePriority: 5 },
										{ responsivePriority: 2 },
										{ responsivePriority: 3 },
										{ responsivePriority: 3 },
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
				d.action       = 'wcfm_ajax_controller',
				d.controller   = 'wcfm-customers-details-bookings',
				d.customer_id  = customer_id
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-appointments table refresh complete
				$( document.body ).trigger( 'updated_wcfm_customers_bookings' );
			}
		}
	} );
	
	// Mark Booking as Confirmed
	$( document.body ).on( 'updated_wcfm_customers_bookings', function() {
		$('.wcfm_booking_mark_confirm').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.booking_mark_complete_confirm);
				if(rconfirm) markCompleteWCFMBooking($(this));
				return false;
			});
		});
	});
	
	function markCompleteWCFMBooking(item) {
		$('#wcfm_customers_bookings_listing_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfm_booking_mark_confirm',
			bookingid : item.data('bookingid')
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				$wcfm_customers_bookings_table.ajax.reload();
				$('#wcfm_customers_bookings_listing_expander').unblock();
			}
		});
	}
	
	///////////////////////////// Appointment Section ///////////////////////////
	
	$wcfm_customers_appointments_table = $('#wcfm-customers-details-appointments').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"bFilter"   : false,
		"dom"       : 'Bfrtip',
		"language"  : $.parseJSON(dataTables_language),
    "buttons"   : $wcfm_datatable_button_args,
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 1 },
										{ responsivePriority: 4 },
										{ responsivePriority: 5 },
										{ responsivePriority: 2 },
										{ responsivePriority: 3 },
										{ responsivePriority: 3 },
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
				d.action       = 'wcfm_ajax_controller',
				d.controller   = 'wcfm-customers-details-appointments',
				d.customer_id  = customer_id
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-appointments table refresh complete
				$( document.body ).trigger( 'updated_wcfm_customers_appointments' );
			}
		}
	} );
	
	// Mark Appointment as Confirmed
	$( document.body ).on( 'updated_wcfm_customers_appointments', function() {
		$('.wcfm_appointment_mark_confirm').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.appointment_mark_complete_confirm);
				if(rconfirm) markCompleteWCFMAppointment($(this));
				return false;
			});
		});
	});
	
	function markCompleteWCFMAppointment(item) {
		$('#wcfm_customers_appointments_listing_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfm_appointment_mark_confirm',
			appointmentid : item.data('appointmentid')
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				$wcfm_customers_appointments_table.ajax.reload();
				$('#wcfm_customers_appointments_listing_expander').unblock();
			}
		});
	}
	
	
	
	// Screen Manager
	/*$( document.body ).on( 'updated_wcfm_customers_orders', function() {
		$.each(wcfm_customers_screen_manage, function( column, column_val ) {
		  $wcfm_shop_customers_table.column(column).visible( false );
		} );
	});*/
} );