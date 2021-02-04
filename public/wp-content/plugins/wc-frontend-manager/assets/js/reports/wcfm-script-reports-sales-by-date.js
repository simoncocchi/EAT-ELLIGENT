jQuery(function( $ ) {
  $( document.body ).on( 'wcfm-date-range-refreshed', function() {
		$('input[name="start_date"]').val($filter_date_form);
		$('input[name="end_date"]').val($filter_date_to);
		$('input[name="end_date"]').parent().submit();
	});
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			var data = {
				action                : 'sales_by_vendor_change_url',
				vendor_manager_change : $('#dropdown_vendor').val()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.redirect) {
						window.location = $response_json.redirect;
					}
				}
			});
		}).select2( $wcfm_vendor_select_args );
	}	
	
	function statsGraphPrint(source) {
		return "<html><head><script>function step1(){\n" +
				"setTimeout('step2()', 10);}\n" +
				"function step2(){window.print();window.close()}\n" +
				"</scri" + "pt></head><body onload='step1()'>\n" +
				"<img src='" + source + "' /></body></html>";
	}
	
	$('#wcfm_report_print').click(function( event ) {
		event.preventDefault();
		
		var canvas    = $("#chart-placeholder-canvas").get(0);
		var canvasImg = canvas.toDataURL();
		
		Pagelink = "sales_report";
		var pwa = window.open(Pagelink, "_new");
		pwa.document.open();
		
		if( $('.wcfm-top-element-container h2').length > 0 ) {
			pwa.document.write( "<table><tr><td><img width='40' heigth='40' src='" + $('.wcfm-top-element-container img').attr('src') + "' /> &nbsp;</td><td><strong style='font-size:20px;'>" + $('.wcfm-top-element-container h2').html() + " - </strong></td><td><strong style='font-size:20px;'>" + $('.wcfm-page-heading-text').html() + "</strong></td></tr></table><br />" + $('#wcfm_reports_sales_by_date_expander .chart-legend').html() + "<br /><br />" + statsGraphPrint(canvasImg));
		} else {
			pwa.document.write( "<h2>" + $('.wcfm-page-heading-text').html() + "</h2>" + $('#wcfm_reports_sales_by_date_expander .chart-legend').html() + "<br /><br />" + statsGraphPrint(canvasImg));
		}
		pwa.document.close();
	});
});
