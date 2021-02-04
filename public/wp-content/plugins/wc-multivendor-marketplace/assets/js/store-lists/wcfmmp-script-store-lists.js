jQuery(document).ready(function($) {
		
	$current_location_fetched = false;
		
	// Set Bootstrap full width class
	if( $('#wcfmmp-stores-lists').parent().hasClass('col-md-8') ) {
		$('#wcfmmp-stores-lists').parent().removeClass('col-md-8');
		$('#wcfmmp-stores-lists').parent().addClass('col-md-12');
	}
	if( $('#wcfmmp-stores-lists').parent().hasClass('col-md-9') ) {
		$('#wcfmmp-stores-lists').parent().removeClass('col-md-9');
		$('#wcfmmp-stores-lists').parent().addClass('col-md-12');
	}
	if( $('#wcfmmp-stores-lists').parent().hasClass('col-sm-8') ) {
		$('#wcfmmp-stores-lists').parent().removeClass('col-sm-8');
		$('#wcfmmp-stores-lists').parent().addClass('col-md-12');
	}
	if( $('#wcfmmp-stores-lists').parent().hasClass('col-sm-9') ) {
		$('#wcfmmp-stores-lists').parent().removeClass('col-sm-9');
		$('#wcfmmp-stores-lists').parent().addClass('col-md-12');
	}
	$('#wcfmmp-stores-lists').parent().removeClass('col-sm-push-3');
	$('#wcfmmp-stores-lists').parent().removeClass('col-sm-push-4');
	$('#wcfmmp-stores-lists').parent().removeClass('col-md-push-3');
	$('#wcfmmp-stores-lists').parent().removeClass('col-md-push-4');
	
	// Store Sidebar
	if( $('.left_sidebar').length > 0 ) {
		if( $(window).width() > 768 ) {
			$left_sidebar_height = $('.left_sidebar').outerHeight();
			$right_side_height = $('.right_side').outerHeight();
			if( $left_sidebar_height < $right_side_height ) {
				$('.left_sidebar').css( 'height', $right_side_height + 50 );
			}
		}
	}
		
	// Store Box Height Set
	function storeBoxHeightManage() {
		var store_list_footer_height = 280;
		if( $('.wcfmmp-single-store').hasClass('coloum-2') ) {
			$('.wcfmmp-single-store .store-footer').each(function() {
				if( $(this).outerHeight() > store_list_footer_height ) {
					store_list_footer_height = $(this).outerHeight();
				}
			});
			$('.wcfmmp-single-store .store-footer').css( 'height', store_list_footer_height );
		}
		
		$('.wcfmmp-store-lists-sorting #wcfmmp_store_orderby').on('change', function() {
		  $(this).parent().submit();
		});
	}
	setTimeout(function() { storeBoxHeightManage(); }, 200 );
	
	if( $("#wcfmmp_store_country").length > 0 ) {
		$("#wcfmmp_store_country").select2({
			allowClear:  true,
			placeholder: wcfmmp_store_list_messages.choose_location + ' ...'
		});
	}
	
	if( $("#wcfmmp_store_category").length > 0 ) {
		$("#wcfmmp_store_category").select2({
			allowClear:  true,
			placeholder: wcfmmp_store_list_messages.choose_category + ' ...'
		});
	}
	
	if( $(".wcfm-custom-search-select-field").length > 0 ) {
		$(".wcfm-custom-search-select-field").each(function() {
			$title = $(this).data('title');
			$(this).select2({
				allowClear:  true,
				placeholder: $title + ' ...'
			});
		});
	}
		
		
	var form = $('.wcfmmp-store-search-form');
	var xhr;
	var timer = null;
	
	function refreshStoreList() {
	 data = {
			action                  : 'wcfmmp_stores_list_search',
			pagination_base         : form.find('#pagination_base').val(),
			paged                   : form.find('#wcfm_paged').val(),
			per_row                 : $per_row,
			per_page                : $per_page,
			includes                : $includes,
			excludes                : $excludes,
			orderby                 : $('#wcfmmp_store_orderby').val(),
			has_orderby             : $has_orderby,
			has_product             : $has_product,
			sidebar                 : $sidebar,
			theme                   : $theme,
			search_term             : $('.wcfmmp-store-search').val(),
			wcfmmp_store_category   : $('#wcfmmp_store_category').val(),
			search_data             : jQuery('.wcfmmp-store-search-form').serialize(),
			_wpnonce                : form.find('#nonce').val()
		};

		if (timer) {
			clearTimeout(timer);
		}

		if ( xhr ) {
			xhr.abort();
		}

		timer = setTimeout(function() {
			$('.wcfmmp-stores-listing').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			xhr = $.post(wcfm_params .ajax_url, data, function(response) {
				if (response.success) {
					$('.wcfmmp-stores-listing').unblock();

					var data = response.data;
					$('#wcfmmp-stores-wrap').html( $(data).find( '.wcfmmp-stores-content' ) );
					fetchMarkers();
					initiateTip();
					initEnquiryButton();
					setTimeout(function() { storeBoxHeightManage(); }, 200 );
				}
			});
		}, 500);
	}
	
	$wcfm_anr_loaded = false;
	function initEnquiryButton() {
		$('.wcfm_catalog_enquiry').each(function() {
			if( !$(this).hasClass( 'wcfm_login_popup' ) ) {
				$(this).off('click').on('click', function(event) {
					event.preventDefault();
					
					$store   = $(this).data('store');
					$product = $(this).data('product');
					
					$.colorbox( { inline:true, href: "#enquiry_form_wrapper", width: $popup_width,
						onComplete:function() {
							
							$('#wcfm_enquiry_form').find('#enquiry_vendor_id').val($store);
							$('#wcfm_enquiry_form').find('#enquiry_product_id').val($product);
							
							if( jQuery('.anr_captcha_field').length > 0 ) {
								if (typeof grecaptcha != "undefined") {
									if( $wcfm_anr_loaded ) {
										grecaptcha.reset();
									} else {
										wcfm_anr_onloadCallback();
									}
									$wcfm_anr_loaded = true;
								}
							}
						}
					});
				});
			} else {
				//wcfmInitLoginPopup();
				jQuery('.wcfm_login_popup').each( function() {
					jQuery(this).click( function( event ) {
						event.preventDefault();
						jQuerylogin_popup = jQuery(this);
						
						// Ajax Call for Fetching Quick Edit HTML
						jQuery('body').block({
							message: null,
							overlayCSS: {
								background: '#fff',
								opacity: 0.6
							}
						});
						var data = {
							action  : 'wcfm_login_popup_form'
						}	
						
						jQuery.ajax({
							type    :		'POST',
							url     : wcfm_params.ajax_url,
							data    : data,
							success :	function(response) {
								// Intialize colorbox
								jQuery.colorbox( { html: response, width: $popup_width,
									onComplete:function() {
								
										// Intialize Quick Update Action
										jQuery('#wcfm_login_popup_button').click(function() {
											$wcfm_is_valid_form = true;
											jQuery('#wcfm_login_popup_form').block({
												message: null,
												overlayCSS: {
													background: '#fff',
													opacity: 0.6
												}
											});
											jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
											if( jQuery('input[name=wcfm_login_popup_username]').val().length == 0 ) {
												jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_login_messages.no_username).addClass('wcfm-error').slideDown();
												wcfm_notification_sound.play();
												jQuery('#wcfm_login_popup_form').unblock();
											} else if( jQuery('input[name=wcfm_login_popup_password]').val().length == 0 ) {
												jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_login_messages.no_password).addClass('wcfm-error').slideDown();
												wcfm_notification_sound.play();
												jQuery('#wcfm_login_popup_form').unblock();
											} else {
												jQuery( document.body ).trigger( 'wcfm_form_validate', jQuery('#wcfm_login_popup_form') );
												if( !$wcfm_is_valid_form ) {
													wcfm_notification_sound.play();
													jQuery('#wcfm_login_popup_form').unblock();
												} else {
													jQuery('#wcfm_login_popup_button').hide();
													var data = {
														action : 'wcfm_login_popup_submit', 
														wcfm_login_popup_form : jQuery('#wcfm_login_popup_form').serialize()
													}	
													jQuery.post(wcfm_params.ajax_url, data, function(response) {
														if(response) {
															jQueryresponse_json = jQuery.parseJSON(response);
															wcfm_notification_sound.play();
															jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
															if(jQueryresponse_json.status) {
																jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + jQueryresponse_json.message).addClass('wcfm-success').slideDown();
																window.location = window.location.href;
															} else {
																jQuery('#wcfm_login_popup_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + jQueryresponse_json.message).addClass('wcfm-error').slideDown();
																jQuery('#wcfm_login_popup_button').show();
																jQuery('#wcfm_login_popup_form').unblock();
															}
														}
													} );
												}
											}
										});
									}
								});
								jQuery('body').unblock();
							}
						});
						
						return false;
					} );
				} );
			}
		});
	}
	
	if( $('.wcfmmp-store-search-form').length > 0 ) {
		
		if( wcfmmp_store_list_options.is_geolocate ) {
			if( ( $('#wcfmmp_radius_addr').length == 0 ) || !navigator.geolocation ) {
				refreshStoreList();
			}
		}
		
		form.on('keyup', '.wcfm-search-field', function() {
			refreshStoreList();
		} );

		form.on('keyup', '#search', function() {
			refreshStoreList();
		} );
		
		$('.wcfm-search-field').on('input',function(e){
			refreshStoreList();
		});

		$('#search').on('input',function(e){
			refreshStoreList();
		});
		
		// Custom Search Box
		$('.wcfm-custom-search-input-field').on('input',function(e){
			refreshStoreList();
		});
		
		// Category Filter
		form.on('change', '#wcfmmp_store_category', function() {
			refreshStoreList();
		} );
		
		// Custom Search Frop Down
		form.on('change', '.wcfm-custom-search-select-field', function() {
			refreshStoreList();
		} );
		
		// Country Filter
		//form.on('change', '#wcfmmp_store_country', function() {
		$( document.body ).on( 'wcfm_store_list_country_changed', function( event ) {
			refreshStoreList();
		} );
		
		// State Filter
		form.on('change', '#wcfmmp_store_state', function() {
			refreshStoreList();
		} );
		
		// State Filter
	  form.on('keyup', '#wcfmmp_store_state', function() {
			refreshStoreList();
		} );
		
		// Store Radius Search
		var searchControl = '';
		if( $('#wcfmmp_radius_addr').length > 0 ) {
			var max_radius = parseInt( wcfmmp_store_list_options.max_radius );
			var wcfmmp_radius_addr_input = document.getElementById("wcfmmp_radius_addr");
			
			if( wcfm_maps.lib == 'google' ) {
				var geocoder = new google.maps.Geocoder;
				var awcfmmp_radius_addr_autocomplete = new google.maps.places.Autocomplete(wcfmmp_radius_addr_input);
				awcfmmp_radius_addr_autocomplete.addListener("place_changed", function() {
					var place = awcfmmp_radius_addr_autocomplete.getPlace();
					$('#wcfmmp_radius_lat').val(place.geometry.location.lat());
					$('#wcfmmp_radius_lng').val(place.geometry.location.lng());
					refreshStoreList();
				});
				
				$('#wcfmmp_radius_addr').blur(function() {
					 if( $(this).val().length == 0 ) {
						 $('#wcfmmp_radius_lat').val('');
						 $('#wcfmmp_radius_lng').val('');
						 refreshStoreList();
					 }
				});
			} else {
				var searchControl = new L.Control.Search({
														container: 'wcfm_radius_filter_container',
														url: 'https://nominatim.openstreetmap.org/search?format=json&q={s}',
														jsonpParam: 'json_callback',
														propertyName: 'display_name',
														propertyLoc: ['lat','lon'],
														marker: L.marker([0,0]),
														moveToLocation: function(latlng, title, map) {
															$('#wcfmmp_radius_lat').val(latlng.lat);
															$('#wcfmmp_radius_lng').val(latlng.lng);
															refreshStoreList();
														},
														//autoCollapse: true,
														initial: false,
														collapsed:false,
														autoType: false,
														minLength: 2
													});
			
			}
			
			$('#wcfmmp_radius_range').on('input', function() {
				$('.wcfmmp_radius_range_cur').html(this.value+wcfmmp_store_list_options.radius_unit);
				if( wcfmmp_store_list_options.is_rtl ) {
					$('.wcfmmp_radius_range_cur').css( 'right', ((this.value/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
				} else {
					$('.wcfmmp_radius_range_cur').css( 'left', ((this.value/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
				}
				$wcfmmp_radius_lat = $('#wcfmmp_radius_lat').val();
				if( $wcfmmp_radius_lat ) {
					setTimeout(function() {refreshStoreList();}, 100);
				}
			});
			
			if( wcfmmp_store_list_options.is_rtl ) {
				$('.wcfmmp_radius_range_cur').css( 'right', (($('#wcfmmp_radius_range').val()/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
			} else {
				$('.wcfmmp_radius_range_cur').css( 'left', (($('#wcfmmp_radius_range').val()/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
			}
			
			if ( navigator.geolocation ) {
				$('.wcfmmmp_locate_icon').on( 'click', function () {
					setUser_CurrentLocation();
				});
				
				if( wcfmmp_store_list_options.is_geolocate ) {
					$('.wcfmmmp_locate_icon').click();
				}
				
				function setUser_CurrentLocation() {
					navigator.geolocation.getCurrentPosition( function( position ) {
						$current_location_fetched = true;
						console.log( position.coords.latitude, position.coords.longitude );
						if( wcfm_maps.lib == 'google' ) {
							geocoder.geocode( {
									location: {
											lat: position.coords.latitude,
											lng: position.coords.longitude
									}
							}, function ( results, status ) {
									if ( 'OK' === status ) {
										$('#wcfmmp_radius_addr').val( results[0].formatted_address );
										$('#wcfmmp_radius_lat').val( position.coords.latitude );
										$('#wcfmmp_radius_lng').val( position.coords.longitude );
										refreshStoreList();
									}
							} )
						} else {
							$.get('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+position.coords.latitude+'&lon='+position.coords.longitude, function(data) {
								$('#wcfmmp_radius_addr').val( data.address.road );
								$('#wcfmmp_radius_lat').val( position.coords.latitude );
								$('#wcfmmp_radius_lng').val( position.coords.longitude );
								refreshStoreList();
							});
						}
					});
				}
			}
		}
	}
	
	// Store List Filter Country -> State Dropdowns
	var wcfmmp_cs_filter_wrapper = $( '.wcfmmp-store-search-form' );
	var input_csd_state = '';
	var csd_selected_state = '';
	var wcfmmo_cs_filter_select = {
		init: function () {
			wcfmmp_cs_filter_wrapper.on( 'change', 'select#wcfmmp_store_country', this.state_select );
			//jQuery('select#wcfmmp_store_country').change();
		},
		state_select: function () {
			var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
					states = $.parseJSON( states_json ),
					$statebox = $( '#wcfmmp_store_state' ),
					value = $statebox.val(),
					country = $( this ).val(),
					$state_required = $statebox.data('required');

			if ( states[ country ] ) {

					if ( $.isEmptyObject( states[ country ] ) ) {

						if ( $statebox.is( 'select' ) ) {
							if( typeof $state_required != 'undefined') {
								$( 'select#wcfmmp_store_state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="wcfmmp_store_state" id="wcfmmp_store_state" placeholder="'+ wcfmmp_store_list_messages.choose_state +' ..." />' );
							} else {
								$( 'select#wcfmmp_store_state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="wcfmmp_store_state" id="wcfmmp_store_state" placeholder="'+ wcfmmp_store_list_messages.choose_state +' ..." />' );
							}
						}

						if( value ) {
							$( '#wcfmmp_store_state' ).val( value );
						} else {
							$( '#wcfmmp_store_state' ).val( '' );
						}

					} else {
							input_csd_state = '';

							var options = '',
									state = states[ country ];

							for ( var index in state ) {
									if ( state.hasOwnProperty( index ) ) {
											if ( csd_selected_state ) {
													if ( csd_selected_state == index ) {
															var selected_value = 'selected="selected"';
													} else {
															var selected_value = '';
													}
											}
											options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
									}
							}

							if ( $statebox.is( 'select' ) ) {
									$( 'select#wcfmmp_store_state' ).html( '<option value="">' + wcfmmp_store_list_messages.choose_state + ' ...</option>' + options );
							}
							if ( $statebox.is( 'input' ) ) {
								if( typeof $state_required != 'undefined') {
									$( 'input#wcfmmp_store_state' ).replaceWith( '<select class="wcfm-select wcfm_ele" name="wcfmmp_store_state" id="wcfmmp_store_state"></select>' );
								} else {
									$( 'input#wcfmmp_store_state' ).replaceWith( '<select class="wcfm-select wcfm_ele" name="wcfmmp_store_state" id="wcfmmp_store_state"></select>' );
								}
								$( 'select#wcfmmp_store_state' ).html( '<option value="">' + wcfmmp_store_list_messages.choose_state + ' ...</option>' + options );
							}
							//$( '#wcmarketplace_address_state' ).removeClass( 'wcmarketplace-hide' );
							//$( 'div#wcmarketplace-states-box' ).slideDown();

					}
			} else {
				if ( $statebox.is( 'select' ) ) {
					if( typeof $state_required != 'undefined') {
						$( 'select#wcfmmp_store_state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="wcfmmp_store_state" id="wcfmmp_store_state" placeholder="'+ wcfmmp_store_list_messages.choose_state +' ..." />' );
					} else {
						$( 'select#wcfmmp_store_state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="wcfmmp_store_state" id="wcfmmp_store_state" placeholder="'+ wcfmmp_store_list_messages.choose_state +' ..." />' );
					}
				}
				$( '#wcfmmp_store_state' ).val(input_csd_state);

				if ( $( '#wcfmmp_store_state' ).val() == 'N/A' ){
					$( '#wcfmmp_store_state' ).val('');
				}
				//$( '#wcmarketplace_address_state' ).removeClass( 'wcmarketplace-hide' );
				//$( 'div#wcmarketplace-states-box' ).slideDown();
			}
			
			$( document.body ).trigger( 'wcfm_store_list_country_changed' );
		}
	}
	
	wcfmmo_cs_filter_select.init();
	
	function fetchMarkers() {
		if( $('.wcfmmp-store-list-map').length > 0 ) {
			reloadMarkers();
			
			var data = {
				action                  : 'wcfmmp_stores_list_map_markers',
				pagination_base         : form.find('#pagination_base').val(),
				paged                   : form.find('#wcfm_paged').val(),
				per_row                 : $per_row,
				per_page                : $per_page,
				includes                : $includes,
				excludes                : $excludes,
				has_product             : $has_product,
				has_orderby             : $has_orderby,
				sidebar                 : $sidebar,
				theme                   : $theme,
				search_term             : $('.wcfmmp-store-search').val(),
				wcfmmp_store_category   : $('#wcfmmp_store_category').val(),
				wcfmmp_store_country    : $('#wcfmmp_store_country').val(),
				wcfmmp_store_state      : $('#wcfmmp_store_state').val(),
				search_data             : jQuery('.wcfmmp-store-search-form').serialize(),
			};
			
			xhr = $.post(wcfm_params.ajax_url, data, function(response) {
				if (response.success) {
					var locations = response.data;
					setMarkers( $.parseJSON(locations) );
				}
			});
		}
	}
	
	// Store List Map
	if( $('.wcfmmp-store-list-map').length > 0 ) {
		$('.wcfmmp-store-list-map').css( 'height', $('.wcfmmp-store-list-map').outerWidth()/2);
		
		var markers = [];
		var store_list_map = markerClusterer = markersGroup = '';
		
		function setMarkers(locations) {
			
			$icon_width = parseInt( wcfmmp_store_list_options.icon_width );
			$icon_height = parseInt( wcfmmp_store_list_options.icon_height );
			
			if( wcfm_maps.lib == 'google' ) {
				var latlngbounds = new google.maps.LatLngBounds();
				var infowindow = new google.maps.InfoWindow();
				
				$.each(locations, function( i, beach ) {
					var myLatLng = new google.maps.LatLng(beach.lat, beach.lang);
					latlngbounds.extend(myLatLng);
					var customIcon = {
														url: beach.icon,
														scaledSize: new google.maps.Size( $icon_width, $icon_height ), // scaled size
														//origin: new google.maps.Point( 0, 0 ), // origin
														//anchor: new google.maps.Point( 0, 0 ) // anchor
													};
					var marker = new google.maps.Marker({
							position: myLatLng,
							map: store_list_map,
							animation: google.maps.Animation.DROP,
							title: beach.name,
							icon: customIcon,
							zIndex: i 
					});
					
					var infoWindowContent = beach.info_window_content;
					
					google.maps.event.addListener(marker, 'click', (function(marker, i) {
						return function() {
							infowindow.setContent(infoWindowContent);
							infowindow.open(store_list_map, marker);
						}
					})(marker, i));
					
					store_list_map.setCenter(marker.getPosition());
	
					// Push marker to markers array                                   
					markers.push(marker);
				});
				
				if( wcfmmp_store_list_options.is_cluster ) {
					const imagePath = wcfmmp_store_list_options.cluster_image;
					
					if( markerClusterer )
						markerClusterer.clearMarkers();

					markerClusterer = new MarkerClusterer(store_list_map, markers, {imagePath: imagePath});
				}
				
				if( $auto_zoom && locations.length > 0 ) {
					store_list_map.fitBounds(latlngbounds);
				}
			} else {
				if( markersGroup )
					markersGroup.clearLayers();
				
				$.each(locations, function( i, beach ) {
					var customIcon = L.icon({
						iconUrl: beach.icon,
						iconSize: [ $icon_width, $icon_height ]
					});
					var marker = L.marker([beach.lat, beach.lang], {icon:customIcon}).bindPopup(beach.info_window_content);
					
					// Push marker to markers array                                   
					markers.push(marker);
					
					markersGroup = L.featureGroup(markers).addTo(store_list_map);

					if( $auto_zoom && locations.length > 0 ) {
						setTimeout(function () {
							store_list_map.fitBounds(markersGroup.getBounds());
						}, 1000);
					}
				});
			}
		}
		
		function reloadMarkers() {
			if( wcfm_maps.lib == 'google' ) {
				for( var i = 0; i < markers.length; i++ ) {
					markers[i].setMap(null);
				}
			} else {
				
			}
			markers = [];
		}
		
		if( !wcfmmp_store_list_options.is_poi ) {
			var myStyles =[
											{
													featureType: "poi",
													elementType: "labels",
													stylers: [
																{ visibility: "off" }
													]
											}
									];
		} else {
			var myStyles =[];
		}
		
		if( wcfm_maps.lib == 'google' ) {
		
			var mapOptions = {
					zoom: $map_zoom,
					center: new google.maps.LatLng(wcfmmp_store_list_options.default_lat,wcfmmp_store_list_options.default_lng,13),
					mapTypeId: wcfm_maps.map_type,
					styles: myStyles
			}
    
    	store_list_map = new google.maps.Map(document.getElementById('wcfmmp-store-list-map'), mapOptions);
    } else {
    	store_list_map = L.map( 'wcfmmp-store-list-map', {
					center: [wcfmmp_store_list_options.default_lat, wcfmmp_store_list_options.default_lng],
					minZoom: 2,
					zoom: $map_zoom,
					zoomAnimation: false
			});
			
			if( !wcfmmp_store_list_options.is_allow_scroll_zoom ) {
				store_list_map.scrollWheelZoom.disable();
			}
			
			L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					subdomains: ['a','b','c']
			}).addTo( store_list_map );
			
			if( searchControl && $('#wcfmmp_radius_addr').length > 0 ) {
				$('#wcfmmp_radius_addr').remove();
				store_list_map.addControl( searchControl );
				$('#wcfm_radius_filter_container').find('.search-input').addClass('wcfmmp-radius-addr').attr( 'id', 'wcfmmp_radius_addr' ).css( 'float', 'none' ).attr( 'placeholder', wcfmmp_store_list_options.search_location );
			}//inizialize search control
    }
    
    if( !wcfmmp_store_list_options.is_geolocate || !$current_location_fetched ) {
    	fetchMarkers();
    }
	}
});