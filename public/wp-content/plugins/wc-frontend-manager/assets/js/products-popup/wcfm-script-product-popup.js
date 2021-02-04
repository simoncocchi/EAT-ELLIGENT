product_manage_from_popup = true;
$is_wcfm_product_popup_on = false;
jQuery(document).ready(function($) {
	// Popup Content Next-Prev Controllers
  $('#wcfm_product_popup_container').find('.wcfm-container').each(function() {
    $(this).append('<div class="wcfm_clearfix"></div>');
  	$(this).append('<input type="button" class="wcfm_submit_button wcfm_product_popup_action wcfm_product_popup_action_next" value="'+wcfm_dashboard_messages.wcfm_product_popup_next+' >>" data-action="next" />');
		$(this).append('<input type="button" class="wcfm_submit_button wcfm_product_popup_action wcfm_product_popup_action_prev" value="<< '+wcfm_dashboard_messages.wcfm_product_popup_previous+'" data-action="prev" />');
		$(this).append('<div class="wcfm_clearfix"></div>');
  });
  $('#wcfm_product_popup_container').find('.wcfm-container:first').find('.wcfm_product_popup_action_prev').remove();
  $('#wcfm_product_popup_container').find('.wcfm-container:not(.wcfm_block_hide):last').find('.wcfm_product_popup_action_next').hide();
  // Product Type Change
	$( document.body ).on( 'wcfm_product_type_changed', function() {
		$('#wcfm_product_popup_container').find('.wcfm-container').find('.wcfm_product_popup_action_next').show();	
		$('#wcfm_product_popup_container').find('.wcfm-container:not(.wcfm_block_hide):last').find('.wcfm_product_popup_action_next').hide();
	});
  
  $('#wcfm_product_popup_container').find('.page_collapsible:not(:first)').addClass('wcfm_product_popup_hide');
  $('#wcfm_product_popup_container').find('.wcfm-container:not(:first)').addClass('wcfm_product_popup_hide');
  
  $('.wcfm_product_popup_action').click(function() {
  	$action = $(this).data('action');
  	
  	$('#wcfm_product_popup_container').find('.page_collapsible').addClass('wcfm_product_popup_hide');
  	$('#wcfm_product_popup_container').find('.wcfm-container').addClass('wcfm_product_popup_hide');
  	
  	if( $action == 'next' ) {
  		$(this).parent().nextAll('.page_collapsible:not(.wcfm_head_hide)').eq(0).removeClass('wcfm_product_popup_hide');
  		$(this).parent().nextAll('.page_collapsible:not(.wcfm_head_hide)').eq(0).next('.wcfm-container:not(.wcfm_block_hide)').removeClass('wcfm_product_popup_hide');
  		if( $(this).parent().nextAll('.page_collapsible:not(.wcfm_head_hide)').eq(0).hasClass('variations') ) {
  			$( document.body ).trigger( 'wcfm_product_popup_variations_option' );
  		} else if( $(this).parent().nextAll('.page_collapsible:not(.wcfm_head_hide)').eq(0).hasClass('products_manage_address_geocoder') ) {
  			$('#wcfm_products_manage_form_address_geocoder_head').click();
  		}
  	} else {
  		$(this).parent().prevAll('.wcfm-container:not(.wcfm_block_hide)').eq(0).removeClass('wcfm_product_popup_hide');
  		$(this).parent().prevAll('.wcfm-container:not(.wcfm_block_hide)').eq(0).prev('.page_collapsible:not(.wcfm_block_hide)').removeClass('wcfm_product_popup_hide');
  		if( $(this).parent().nextAll('.page_collapsible:not(.wcfm_head_hide)').eq(0).hasClass('variations') ) {
  			$( document.body ).trigger( 'wcfm_product_popup_variations_option' );
  		} else if( $(this).parent().nextAll('.page_collapsible:not(.wcfm_head_hide)').eq(0).hasClass('products_manage_address_geocoder') ) {
  			$('#wcfm_products_manage_form_address_geocoder_head').click();
  		}
  	}
  });
  
  $product_popup_width = '75%';
	if( jQuery(window).width() <= 960 ) {
		$product_popup_width = '95%';
	}
  
  // Popup Handler
  $('.wcfm_product_popup_button').click(function() {
    jQuery.colorbox( { inline:true, href: "#wcfm_product_popup_container", height: 525, width: $product_popup_width,
    		onComplete:function() {
    			$is_wcfm_product_popup_on = true;
    			$('#wcfm_product_popup_container').find('.wcfm-collapse-content').attr('id', 'wcfm-main-contentainer');
    		}
    });
  });
});