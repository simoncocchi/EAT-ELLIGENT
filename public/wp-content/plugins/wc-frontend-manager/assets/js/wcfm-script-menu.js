$wcfm_search_products_list = [];

var $wcfm_page_select_args = {
			allowClear:  true,
			placeholder: wcfm_dashboard_messages.search_page_select2,
			minimumInputLength: '3',
			language: {
				inputTooShort: function ( args ) {
					var remainingChars = args.minimum - args.input.length;
          var message = wcfm_dashboard_messages.select2_minimum_input + remainingChars;
          return message;
				},
				noResults: function () {
          return wcfm_dashboard_messages.select2_no_result;
        },
        searching: function () {
          return wcfm_dashboard_messages.select2_searching;
        },
        loadingMore: function () {
          return wcfm_dashboard_messages.select2_loading_more;
        },
			},
			escapeMarkup: function( m ) {
				return m;
			},
			ajax: {
				url:         wcfm_params.ajax_url,
				dataType:    'json',
				delay:       250,
				data:        function( params ) {
					return {
						term:     params.term,
						action:   'wcfm_json_search_pages',
						exclude:  jQuery( this ).data( 'exclude' ),
						include:  jQuery( this ).data( 'include' ),
						limit:    jQuery( this ).data( 'limit' )
					};
				},
				processResults: function( data ) {
					var terms = [];
					if ( data ) {
						jQuery.each( data, function( id, text ) {
							terms.push( { id: id, text: text } );
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			}
		};

var $wcfm_product_select_args = {
			allowClear:  true,
			placeholder: wcfm_dashboard_messages.search_product_select2,
			minimumInputLength: '3',
			language: {
				inputTooShort: function ( args ) {
					var remainingChars = args.minimum - args.input.length;
          var message = wcfm_dashboard_messages.select2_minimum_input + remainingChars;
          return message;
				},
				noResults: function () {
          return wcfm_dashboard_messages.select2_no_result;
        },
        searching: function () {
          return wcfm_dashboard_messages.select2_searching;
        },
        loadingMore: function () {
          return wcfm_dashboard_messages.select2_loading_more;
        },
			},
			escapeMarkup: function( m ) {
				return m;
			},
			ajax: {
				url:         wcfm_params.ajax_url,
				dataType:    'json',
				delay:       250,
				data:        function( params ) {
					return {
						term:     params.term,
						action:   'wcfm_json_search_products_and_variations',
						exclude:  jQuery( this ).data( 'exclude' ),
						include:  jQuery( this ).data( 'include' ),
						limit:    jQuery( this ).data( 'limit' )
					};
				},
				processResults: function( data ) {
					var terms = [];
					if ( data ) {
						jQuery.each( data, function( id, text ) {
							terms.push( { id: id, text: text } );
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			}
		};
		
var $wcfm_simple_product_select_args = {
			allowClear:  true,
			placeholder: wcfm_dashboard_messages.search_product_select2,
			minimumInputLength: '3',
			language: {
				inputTooShort: function ( args ) {
					var remainingChars = args.minimum - args.input.length;
          var message = wcfm_dashboard_messages.select2_minimum_input + remainingChars;
          return message;
				},
				noResults: function () {
          return wcfm_dashboard_messages.select2_no_result;
        },
        searching: function () {
          return wcfm_dashboard_messages.select2_searching;
        },
        loadingMore: function () {
          return wcfm_dashboard_messages.select2_loading_more;
        },
			},
			escapeMarkup: function( m ) {
				return m;
			},
			ajax: {
				url:         wcfm_params.ajax_url,
				dataType:    'json',
				delay:       250,
				data:        function( params ) {
					return {
						term:     params.term,
						action:   'wcfm_json_search_products_with_variations',
						exclude:  jQuery( this ).data( 'exclude' ),
						include:  jQuery( this ).data( 'include' ),
						limit:    jQuery( this ).data( 'limit' )
					};
				},
				processResults: function( data ) {
					var terms = [];
					$wcfm_search_products_list = [];
					if ( data ) {
						$wcfm_search_products_list = data;
						jQuery.each( data, function( id, product ) {
							terms.push( { id: id, text: product.label } );
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			}
		};
		
var $wcfm_taxonomy_select_args = {
			allowClear:  true,
			placeholder: wcfm_dashboard_messages.search_taxonomy_select2,
			minimumInputLength: '3',
			language: {
				inputTooShort: function ( args ) {
					var remainingChars = args.minimum - args.input.length;
          var message = wcfm_dashboard_messages.select2_minimum_input + remainingChars;
          return message;
				},
				noResults: function () {
          return wcfm_dashboard_messages.select2_no_result;
        },
        searching: function () {
          return wcfm_dashboard_messages.select2_searching;
        },
        loadingMore: function () {
          return wcfm_dashboard_messages.select2_loading_more;
        },
			},
			escapeMarkup: function( m ) {
				return m;
			},
			ajax: {
				url:         wcfm_params.ajax_url,
				dataType:    'json',
				delay:       250,
				data:        function( params ) {
					return {
						term:     params.term,
						action:   'wcfm_json_search_taxonomies',
						exclude:  jQuery( this ).data( 'exclude' ),
						include:  jQuery( this ).data( 'include' ),
						limit:    jQuery( this ).data( 'limit' ),
						taxonomy: jQuery( this ).data( 'taxonomy' ),
						parent:   jQuery( this ).data( 'parent' ),
					};
				},
				processResults: function( data ) {
					var terms = [];
					if ( data ) {
						jQuery.each( data, function( id, text ) {
							terms.push( { id: id, text: text } );
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			}
		};
		
var $wcfm_vendor_select_args = {
			allowClear:  true,
			placeholder: wcfm_dashboard_messages.choose_vendor_select2,
			minimumInputLength: '3',
			language: {
				inputTooShort: function ( args ) {
					var remainingChars = args.minimum - args.input.length;
          var message = wcfm_dashboard_messages.select2_minimum_input + remainingChars;
          return message;
				},
				noResults: function () {
          return wcfm_dashboard_messages.select2_no_result;
        },
        searching: function () {
          return wcfm_dashboard_messages.select2_searching;
        },
        loadingMore: function () {
          return wcfm_dashboard_messages.select2_loading_more;
        },
			},
			escapeMarkup: function( m ) {
				return m;
			},
			ajax: {
				url:         wcfm_params.ajax_url,
				dataType:    'json',
				delay:       250,
				data:        function( params ) {
					return {
						term:     params.term,
						action:   'wcfm_json_search_vendors',
						exclude:  jQuery( this ).data( 'exclude' ),
						include:  jQuery( this ).data( 'include' ),
						limit:    jQuery( this ).data( 'limit' )
					};
				},
				processResults: function( data ) {
					var terms = [];
					if ( data ) {
						jQuery.each( data, function( id, text ) {
							terms.push( { id: id, text: text } );
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			}
		};
		
$wcfm_datatable_button_args = [
																{
																	extend: 'print',
																	exportOptions: {
																		columns: ':visible'
																	}
																},
																{
																	extend: 'pdfHtml5',
																	orientation: 'landscape',
																	pageSize: 'LEGAL',
																	exportOptions: {
																		columns: ':visible',
																		//stripHtml: false,
																		//stripNewlines: false,
																	}
																},
																{
																	extend: 'excelHtml5',
																	exportOptions: {
																		columns: ':visible'
																	}
																}, 
																{
																	extend: 'csv',
																	exportOptions: {
																		columns: ':visible'
																	}
																}
															];


jQuery( document ).ready( function( $ ) {
	// Removing loader slowly
	/*if( wcfm_noloader == 'yes' ) {
		$('#wcfm_page_load').remove();
		$('.wcfm-collapse-content').css( 'opacity', '1' );
	} else {
		$opacity = 9;
		$content_opaticy = 1;
		function removingLoader() {
			if( $opacity == 0 ) {
				$('#wcfm_page_load').fadeOut("slow", function() {  $('#wcfm_page_load').remove(); $('.wcfm-collapse-content').css( 'opacity', '1' ); } );
			} else {
				setTimeout( function() { 
					$('#wcfm_page_load').css( 'opacity', '0.' + $opacity );
					$('.wcfm-collapse-content').css( 'opacity', '0.' + $content_opaticy );
					$opacity -= 1;
					$content_opaticy += 1;
					removingLoader();
				}, 250);
			}
		}
		removingLoader();
	}*/
	
	
	// Responsive
	// WCFM Responsive Menu Toggler
	if( wcfm_params.is_mobile || wcfm_params.is_tablet ) {
		if( $('#wcfm-main-contentainer .wcfm_responsive_menu_toggler').length > 0 ) {
			//$('#wcfm_menu').removeClass('wcfm_menu_toggle');
			$('#wcfm_menu').addClass('wcfm_responsive_menu_toggle');
			//$('#wcfm_menu').css( 'height', $('#wcfm-content').height() );
			$('#wcfm-main-contentainer .wcfm_responsive_menu_toggler').click(function() {
				if( $('#wcfm_menu').hasClass('wcfm_menu_toggle') ) {
					$('#wcfm_menu').removeClass('wcfm_menu_toggle');
				} else {
					$('#wcfm_menu').addClass('wcfm_menu_toggle');
				}
			});
			
			/*$('#wcfm-main-contentainer .wcfm_responsive_menu_toggler').hover(function() {
				$('#wcfm_menu').addClass('wcfm_responsive_menu_toggle');
				if( $('#wcfm_menu').hasClass('wcfm_menu_toggle') ) {
					$('#wcfm_menu').removeClass('wcfm_menu_toggle');
				} else {
					$('#wcfm_menu').addClass('wcfm_menu_toggle');
				}
			});*/
			
			$(window).scroll(function() {    
				var scroll = $(window).scrollTop();
				if (scroll >= 300) {
					$("#wcfm_menu").addClass("wcfm-menu-fixed");
					$(".wcfm-page-headig").addClass("wcfm-page-headig-fixed");
					$('#wcfm-content').css( 'z-index', 40 );
				} else {
					$("#wcfm_menu").removeClass("wcfm-menu-fixed");
					$(".wcfm-page-headig").removeClass("wcfm-page-headig-fixed");
					$('#wcfm-content').css( 'z-index', 8 );
				}
			});
		}
	
		if( !$('#wcfm_menu').hasClass('wcfm_responsive_menu_toggle') ) {
			$('.wcfm_form_simple_submit_wrapper').css( 'bottom', $('#wcfm_menu').height() );
			$('.wcfm-message').css( 'bottom', ($('#wcfm_menu').height() + 60) );
		}
	}
	if( wcfm_params.is_mobile ) {
		$('#wcfm-main-contentainer').css( 'max-width', $(window).width() );
		$( window ).resize(function() {
			$('#wcfm-main-contentainer').css( 'max-width', $(window).width() );
		});
		$('#wcfm-main-contentainer').parents().each(function() {
		  $(this).addClass('no-margin');
		});
		//$container_width = $(window).width() - 10;
		//$('.wcfm-container').css( 'width', $container_width );
		//$('.wcfm-content').css( 'width', $container_width );
		/*if ($(window).width() > 414 ) {
			$container_width = $(window).width() - 145;
			$('.wcfm-container').css( 'width', $container_width );
			$('.wcfm-content').css( 'width', $container_width );
		} else if ($(window).width() <= 414 ) {
			$container_width = $(window).width() - 102;
			$('.wcfm-container').css( 'width', $container_width );
			$('.wcfm-content').css( 'max-width', $container_width );
		}*/
	}
	
	// Select wrapper fix
	function unwrapSelect() {
		$('#wcfm-main-contentainer').find('input[type="checkbox"]').each(function() {
			if ( $(this).parent().hasClass( "icheckbox_minimal" ) ) {
			  $(this).iCheck( 'destroy' );
			}
			if ( $(this).parent().is( "span" ) ) {
			  $(this).unwrap( "span" );
			}
			if ( $(this).parent().is( "label" ) ) {
			  $(this).unwrap( "label" );
			}
		});
		$('#wcfm-main-contentainer').find('select').each(function() {
			if ( $(this).parent().is( "span" ) ) {
			  $(this).unwrap( "span" );
			}
			if ( $(this).parent().is( "label" ) ) {
			  $(this).unwrap( "label" );
			}
			if ( $(this).parent().hasClass( "select-option" ) || $(this).parent().hasClass( "buddyboss-select-inner" ) || $(this).parent().hasClass( "buddyboss-select" ) ) {
				$(this).parent().find('.ti-angle-down').remove();
				$(this).parent().find('span').remove();
			  $(this).unwrap( "div" );
			}
		});
		setTimeout( function() {  unwrapSelect(); }, 500 );
	}
	
	function restrictNonNegativeInput() {
	  $('.wcfm_non_negative_input').each(function() {
	  	$(this).on("contextmenu",function(){
				 return false;
			}); 
	    $(this).on('change, keypress', function() {
	    	$nval = $(this).val();
	    	if( $nval < 0 ) $(this).val(0);
	    });
	    $(this).on('keydown', function(e) {
	    	//console.log(e.keyCode);
				if( !( ( e.keyCode > 95 && e.keyCode < 106 )
								|| ( e.keyCode > 47 && e.keyCode < 58 ) 
								|| e.keyCode == 8
								|| e.keyCode == 9
								|| e.keyCode == 37
								|| e.keyCode == 39
								|| e.keyCode == 46
								|| e.keyCode == 110
								|| e.keyCode == 188
								|| e.keyCode == 190 ) ) {
									return false;
								}
			});
	  });
	  setTimeout( function() {  restrictNonNegativeInput(); }, 500 );
	}
	
	function restrictNameInput() {
	  $('.wcfm_name_input').each(function() {
	  	$(this).on("contextmenu",function(){
				 return false;
			}); 
	    $(this).on('keydown', function(e) {
	    	//console.log(e.keyCode);
				if( !( ( e.keyCode > 95 && e.keyCode < 106 )
								|| ( e.keyCode > 47 && e.keyCode < 58 ) 
							  || ( e.keyCode > 64 && e.keyCode < 91 ) 
								|| e.keyCode == 8
								|| e.keyCode == 9
								|| e.keyCode == 32
								|| e.keyCode == 37
								|| e.keyCode == 39
								|| e.keyCode == 46
								|| e.keyCode == 189 ) ) {
									return false;
								}
			});
	  });
	  setTimeout( function() {  restrictNameInput(); }, 500 );
	}
	
	function restrictSlugInput() {
	  $('.wcfm_slug_input').each(function() {
	  	$(this).on("contextmenu",function(){
				 return false;
			}); 
	    $(this).on('keydown', function(e) {
	    	//console.log(e.keyCode);
				if( !( ( e.keyCode > 95 && e.keyCode < 106 )
								|| ( e.keyCode > 47 && e.keyCode < 58 ) 
							  || ( e.keyCode > 64 && e.keyCode < 91 ) 
								|| e.keyCode == 8
								|| e.keyCode == 9
								|| e.keyCode == 37
								|| e.keyCode == 39
								|| e.keyCode == 46
								|| e.keyCode == 189 ) ) {
									return false;
								}
			});
	  });
	  setTimeout( function() {  restrictSlugInput(); }, 500 );
	}
	
	setTimeout( function() {
		$('#wcfm-main-contentainer').find('select').each(function() {
			if ( $(this).parent().is( "span" ) || $(this).parent().is( "label" ) ) {
			  $(this).css( 'padding', '5px' ).css( 'min-width', '15px' ).css( 'min-height', '35px' ).css( 'padding-top', '5px' ).css( 'padding-right', '5px' ); //.change();
			}
		});
		unwrapSelect();
		
		restrictNonNegativeInput();
		restrictNameInput();
		restrictSlugInput();
	}, 500 );
	
	// Menu Tip
  jQuery('.menu_tip').each(function() {                                                  
		jQuery(this).qtip({
			content: jQuery(this).attr('data-tip'),
			position: {
				my: 'center right',
				at: 'center left',
				viewport: jQuery(window)
			},
			show: {
				event: 'mouseover',
				solo: true
			},
			hide: {
				inactive: 6000,
				fixed: true
			},
			style: {
				classes: 'qtip-dark qtip-shadow qtip-rounded qtip-wcfm-menu-css'
			}
		});
	});
	
	$( '#wcfm_menu .wcfm_menu_item' ).each( function() {
		$(this).mouseover( function() {
			var hideTime;
			$hover_block = $(this).find( '.wcfm_sub_menu_items' );
			clearTimeout(hideTime);
			$hover_block.show( 'slow', function() {
				hideTime = setTimeout(function() {
					$( '.wcfm_sub_menu_items' ).hide( 'slow' );
					$hover_block.removeClass( 'moz_class' );
				}, 30000);  
			} );
		} );
	} );
	
	// WCFM Menu Toggler
	$('#wcfm-main-contentainer .wcfm_menu_toggler').click(function() {
		$toggle_state = 'no';
	  if( $('#wcfm_menu').hasClass('wcfm_menu_toggle') ) {
	  	$('#wcfm_menu').removeClass('wcfm_menu_toggle');
	  } else {
	  	$toggle_state = 'yes';
	  	$('#wcfm_menu').addClass('wcfm_menu_toggle');
	  }
	  var data = {
			action       : 'wcfm_menu_toggler',
			toggle_state : $toggle_state
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				
			}
		});
	});
} );