jQuery(document).ready( function($) {
	// Vendor approval Response
	$( document.body ).on( 'updated_wcfm-messages', function() {
		$('.wcfm_messages_vendor_approval').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				membershipVendorApproval($(this));
				return false;
			});
		});
	});
	
	function membershipVendorApproval(item) {
		/*$('#wcfm-messages_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});*/
		var data = {
			action     : 'wcfmvm_vendor_approval_html',
			messageid  : item.data('messageid'),
			member_id  : item.data('vendorid')
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				// Intialize colorbox
				$.colorbox( { html: response, innerWidth: '525',
				  onComplete:function() {
				  	$(".wcfm_linked_attached").colorbox({iframe:true, width: '75%', innerHeight:390});
						// Intialize Quick Update Action
						jQuery('#wcfm_vendor_approval_response_button').click(function() {
							jQuery('#wcfm_vendor_approval_response_form').block({
								message: null,
								overlayCSS: {
									background: '#fff',
									opacity: 0.6
								}
							});
							var data = {
								action     : 'wcfmvm_vendor_approval_response_update',
								wcfm_vendor_approval_response_form : jQuery('#wcfm_vendor_approval_response_form').serialize()
							}	
							jQuery.post(wcfm_params.ajax_url, data, function(response) {
								if(response) {
									jQueryresponse_json = jQuery.parseJSON(response);
									jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
									if(jQueryresponse_json.status) {
										jQuery('#wcfm_vendor_approval_response_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + jQueryresponse_json.message).addClass('wcfm-success').slideDown();
										jQuery('#wcfm_vendor_approval_response_button').hide();
									} else {
										jQuery('#wcfm_vendor_approval_response_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + jQueryresponse_json.message).addClass('wcfm-error').slideDown();
									}
									jQuery('#wcfm_vendor_approval_response_form').unblock();
									setTimeout(function() {
										if($wcfm_messages_table) $wcfm_messages_table.ajax.reload();
										jQuery.colorbox.remove();
									}, 2000);
								}
							} );
						});
					}
				});
			}
		});
	}
});