jQuery(document).ready(function($) {
	
	// Membership Price display set
	var wcfm_membership_price_width = 100;
	$('.wcfm_membership_price .amount').each(function() {
		if( $(this).outerWidth() > wcfm_membership_price_width ) {
			wcfm_membership_price_width = $(this).outerWidth();
		}
	});
	$('.wcfm_membership_price .amount').css( 'display', 'block' );
	$('.wcfm_membership_price .amount').css( 'width', wcfm_membership_price_width );
	$('.wcfm_membership_price .amount').css( 'height', wcfm_membership_price_width );
	$('.wcfm_membership_price .amount').css( 'padding-top', (wcfm_membership_price_width-30)/2 );
	
	// Membership table Title description height set
	var wcfm_membership_title_height = 0;
	$('.wcfm_membership_title').each(function() {
		if( $(this).outerHeight() > wcfm_membership_title_height ) {
			wcfm_membership_title_height = $(this).outerHeight();
		}
	});
	$('.wcfm_membership_title').css( 'height', wcfm_membership_title_height );
	
	// Membership table Price description height set
	var wcfm_membership_price_description_height = 0;
	$('.wcfm_membership_price_description').each(function() {
		if( $(this).outerHeight() > wcfm_membership_price_description_height ) {
			wcfm_membership_price_description_height = $(this).outerHeight();
		}
	});
	$('.wcfm_membership_price_description').css( 'height', wcfm_membership_price_description_height );
		
	// Membership table head description height set
	var wcfm_membership_description_height = 0;
	$('.wcfm_membership_description').each(function() {
		if( $(this).outerHeight() > wcfm_membership_description_height ) {
			wcfm_membership_description_height = $(this).outerHeight();
		}
	});
	$('.wcfm_membership_description').css( 'height', wcfm_membership_description_height );
	
	// Membership table head height set
	wcfm_membership_description_height -= 50;
	var wcfm_membership_box_head_height = 280;
	$('.wcfm_membership_box_head').each(function() {
		if( ( $(this).outerHeight() + wcfm_membership_description_height + (wcfm_membership_price_width/2) ) > wcfm_membership_box_head_height ) {
			wcfm_membership_box_head_height = $(this).outerHeight() + wcfm_membership_description_height + (wcfm_membership_price_width/2);
		}
	});
	if( wcfm_membership_box_head_height > 280 ) {
		$('.wcfm_membership_box_head').css( 'height', wcfm_membership_box_head_height );
		$('.wcfm_featured_membership_box .wcfm_membership_box_head').css( 'height', wcfm_membership_box_head_height+30 );
	}
	
	// Membership table element height set
	var wcfm_membership_element_height = 0;
	$('.wcfm_membership_element').each(function() {
		if( $(this).outerHeight() > wcfm_membership_element_height ) {
			wcfm_membership_element_height = $(this).outerHeight();
		}
	});
	$('.wcfm_membership_element').css( 'height', wcfm_membership_element_height );
} );