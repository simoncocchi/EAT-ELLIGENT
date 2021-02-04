jQuery(document).ready(function($) {
	// Pay for Product
	$('.wcfm_pay_for_product_button').click(function(event) {
	  event.preventDefault();
	  
		$('.wcfm_pay_for_product_container').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action         : 'wcfm_pay_for_product',
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				if($response_json.status) {
					if( $response_json.redirect ) window.location = $response_json.redirect;	
				}
				$('.wcfm_pay_for_product_container').unblock();
			}
		});
	});
});