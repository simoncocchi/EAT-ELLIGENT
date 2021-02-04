jQuery(document).ready(function($) {
		
	var form = $('.wcfmmp-product-geolocate-search-form');
	var xhr;
	var timer = null;
	
	if( $('.wcfmmp-product-geolocate-search-form').length > 0 ) {
		
		if( $('#wcfmmp_radius_addr').length > 0 ) {
			var max_radius = parseInt( wcfmmp_product_list_options.max_radius );
			var wcfmmp_radius_addr_input = document.getElementById("wcfmmp_radius_addr");
			
			if( wcfm_maps.lib == 'google' ) {
				var geocoder = new google.maps.Geocoder;
				var awcfmmp_radius_addr_autocomplete = new google.maps.places.Autocomplete(wcfmmp_radius_addr_input);
				awcfmmp_radius_addr_autocomplete.addListener("place_changed", function() {
					var place = awcfmmp_radius_addr_autocomplete.getPlace();
					$('#wcfmmp_radius_lat').val(place.geometry.location.lat());
					$('#wcfmmp_radius_lng').val(place.geometry.location.lng());
					//form.submit();
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
															//refreshStoreList();
														},
														//autoCollapse: true,
														initial: false,
														collapsed:false,
														autoType: false,
														minLength: 2
													});
			}
			
			$('#wcfmmp_radius_range').on('input', function() {
				$('.wcfmmp_radius_range_cur').html(this.value+wcfmmp_product_list_options.radius_unit);
				if( wcfmmp_product_list_options.is_rtl ) {
					$('.wcfmmp_radius_range_cur').css( 'right', ((this.value/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
				} else {
					$('.wcfmmp_radius_range_cur').css( 'left', ((this.value/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
				}
				$wcfmmp_radius_lat = $('#wcfmmp_radius_lat').val();
				if( $wcfmmp_radius_lat ) {
					//setTimeout(function() {form.submit();}, 100);
				}
			});
			
			if( wcfmmp_product_list_options.is_rtl ) {
				$('.wcfmmp_radius_range_cur').css( 'right', (($('#wcfmmp_radius_range').val()/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
			} else {
				$('.wcfmmp_radius_range_cur').css( 'left', (($('#wcfmmp_radius_range').val()/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
			}
			
			if ( navigator.geolocation ) {
				$('.wcfmmmp_locate_icon').on( 'click', function () {
					setUser_CurrentLocation();
				});
				
				if( wcfmmp_product_list_options.is_geolocate ) {
					if( !$('#wcfmmp_radius_lat').val() ) {
						$('.wcfmmmp_locate_icon').click();
					}
				}
				
				function setUser_CurrentLocation() {
					navigator.geolocation.getCurrentPosition( function( position ) {
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
										//if( wcfmmp_product_list_options.is_geolocate ) {
											form.submit();
										//}
									}
							} )
						} else {
							$.get('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+position.coords.latitude+'&lon='+position.coords.longitude, function(data) {
								$('#wcfm_radius_filter_container').find('.search-input').val( data.address.road );
								$('#wcfmmp_radius_lat').val( position.coords.latitude );
								$('#wcfmmp_radius_lng').val( position.coords.longitude );
								//if( wcfmmp_product_list_options.is_geolocate ) {
									form.submit();
								//}
							});
						}
					});
				}
			}
		}
	}
	
	function fetchMarkers() {
		if( $('.wcfmmp-product-list-map').length > 0 ) {
			reloadMarkers();
			
			var data = {
				search_term             : '',
				wcfmmp_store_category   : '',
				wcfmmp_store_country    : '',
				wcfmmp_store_state      : '',
				action                  : 'wcfmmp_stores_list_map_markers',
				pagination_base         : 1,
				paged                   : 1,
				per_row                 : $per_row,
				per_page                : $per_page,
				includes                : $includes,
				excludes                : $excludes,
				has_product             : $has_product,
				has_orderby             : $has_orderby,
				sidebar                 : $sidebar,
				theme                   : $theme,
				search_data             : '' //jQuery('.wcfmmp-product-geolocate-search-form').serialize(),
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
	if( $('.wcfmmp-product-list-map').length > 0 ) {
		$('.wcfmmp-product-list-map').css( 'height', $('.wcfmmp-product-list-map').outerWidth()/2);
		
		var markers = [];
		var store_list_map = markerClusterer = '';
		
		function setMarkers(locations) {
			
			$icon_width = parseInt( wcfmmp_product_list_options.icon_width );
			$icon_height = parseInt( wcfmmp_product_list_options.icon_height );
			
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
				
				if( wcfmmp_product_list_options.is_cluster ) {
					const imagePath = wcfmmp_product_list_options.cluster_image;
					
					if( markerClusterer )
						markerClusterer.clearMarkers();

					markerClusterer = new MarkerClusterer(store_list_map, markers, {imagePath: imagePath});
				}
				
				if( $auto_zoom && locations.length > 0 ) {
					store_list_map.fitBounds(latlngbounds);
				}
			} else {
					$.each(locations, function( i, beach ) {
					var customIcon = L.icon({
						iconUrl: beach.icon,
						iconSize: [$icon_width, $icon_height]
					});
					var marker = L.marker([beach.lat, beach.lang], {icon:customIcon}).bindPopup(beach.info_window_content);
					
					// Push marker to markers array                                   
					markers.push(marker);
					
					var group = L.featureGroup(markers).addTo(store_list_map);

					if( $auto_zoom && locations.length > 0 ) {
						setTimeout(function () {
							store_list_map.fitBounds(group.getBounds());
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
		
		if( !wcfmmp_product_list_options.is_poi ) {
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
					center: new google.maps.LatLng(wcfmmp_product_list_options.default_lat,wcfmmp_product_list_options.default_lng,13),
					mapTypeId: wcfm_maps.map_type,
					styles: myStyles
			}
	
			store_list_map = new google.maps.Map(document.getElementById('wcfmmp-product-list-map'), mapOptions);
		} else {
			store_list_map = L.map( 'wcfmmp-product-list-map', {
					center: [wcfmmp_product_list_options.default_lat, wcfmmp_product_list_options.default_lng],
					minZoom: 2,
					zoom: 13,
					zoomAnimation: false
			});
			
			L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					subdomains: ['a','b','c']
			}).addTo( store_list_map );
			
			if( searchControl && $('#wcfmmp_radius_addr').length > 0 ) {
				store_list_map.addControl( searchControl );
				$('#wcfm_radius_filter_container').find('.search-input').addClass('wcfmmp-radius-addr').attr( 'id', 'wcfmmp_radius_addr' ).attr( 'name', 'radius_addr' ).css( 'float', 'none' ).attr( 'placeholder', wcfmmp_product_list_options.search_location ).val($('#wcfmmp_radius_addr').val());
				$('#wcfmmp_radius_addr').remove();
			}//inizialize search control
		}
		//if( !wcfmmp_product_list_options.is_geolocate ) {
      fetchMarkers();
    //}
	}
});