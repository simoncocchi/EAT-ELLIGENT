(function($) {
	var wcfm_wcmarketplace_address_wrapper = $( '.store_address' );
	var wcfm_wcmarketplace_address_select = {
			init: function () {
				wcfm_wcmarketplace_address_wrapper.on( 'change', 'select#country', this.state_select );
				jQuery('select#country').change();
			},
			state_select: function () {
					var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
							states = $.parseJSON( states_json ),
							$statebox = $( '#state' ),
							value = $statebox.val(),
							country = $( this ).val();

					if ( states[ country ] ) {

							if ( $.isEmptyObject( states[ country ] ) ) {

								if ( $statebox.is( 'select' ) ) {
										$( 'select#state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="state" id="state" />' );
								}

								if( value ) {
									$( '#state' ).val( value );
								} else {
									$( '#state' ).val( 'N/A' );
								}

							} else {
									input_selected_state = '';

									var options = '',
											state = states[ country ];

									for ( var index in state ) {
											if ( state.hasOwnProperty( index ) ) {
													if ( selected_state ) {
															if ( selected_state == index ) {
																	var selected_value = 'selected="selected"';
															} else {
																	var selected_value = '';
															}
													}
													options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
											}
									}

									if ( $statebox.is( 'select' ) ) {
											$( 'select#state' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
									}
									if ( $statebox.is( 'input' ) ) {
											$( 'input#state' ).replaceWith( '<select class="wcfm-select wcfm_ele" name="state" id="state"></select>' );
											$( 'select#state' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
									}
									//$( '#wcmarketplace_address_state' ).removeClass( 'wcmarketplace-hide' );
									//$( 'div#wcmarketplace-states-box' ).slideDown();

							}
					} else {
						if ( $statebox.is( 'select' ) ) {
							//input_selected_state = '';
							$( 'select#state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="state" id="state" />' );
						}
						$( '#state' ).val(input_selected_state);

						if ( $( '#state' ).val() == 'N/A' ){
							$( '#state' ).val('');
						}
						//$( '#wcmarketplace_address_state' ).removeClass( 'wcmarketplace-hide' );
						//$( 'div#wcmarketplace-states-box' ).slideDown();
					}
			}
	}
	
	wcfm_wcmarketplace_address_select.init();
		
		
	$store_lat = jQuery("#store_lat").val();
	$store_lng = jQuery("#store_lng").val();
  function initialize() {
		var latlng = new google.maps.LatLng( $store_lat, $store_lng );
		var map = new google.maps.Map(document.getElementById("wcfm-wcmarketplace-map"), {
				center: latlng,
				blur : true,
				zoom: 15
		});
		var marker = new google.maps.Marker({
				map: map,
				position: latlng,
				draggable: true,
				anchorPoint: new google.maps.Point(0, -29)
		});
	
		var input = document.getElementById("find_address");
		//map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
		var geocoder = new google.maps.Geocoder();
		var autocomplete = new google.maps.places.Autocomplete(input);
		autocomplete.bindTo("bounds", map);
		var infowindow = new google.maps.InfoWindow();   
	
		autocomplete.addListener("place_changed", function() {
				infowindow.close();
				marker.setVisible(false);
				var place = autocomplete.getPlace();
				if (!place.geometry) {
						window.alert("Autocomplete returned place contains no geometry");
						return;
				}
	
				// If the place has a geometry, then present it on a map.
				if (place.geometry.viewport) {
						map.fitBounds(place.geometry.viewport);
				} else {
						map.setCenter(place.geometry.location);
						map.setZoom(17);
				}
	
				marker.setPosition(place.geometry.location);
				marker.setVisible(true);
	
				bindDataToForm(place.formatted_address,place.geometry.location.lat(),place.geometry.location.lng());
				infowindow.setContent(place.formatted_address);
				infowindow.open(map, marker);
				showTooltip(infowindow,marker,place.formatted_address);
	
		});
		google.maps.event.addListener(marker, "dragend", function() {
				geocoder.geocode({"latLng": marker.getPosition()}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
								if (results[0]) {        
										bindDataToForm(results[0].formatted_address,marker.getPosition().lat(),marker.getPosition().lng());
										infowindow.setContent(results[0].formatted_address);
										infowindow.open(map, marker);
										showTooltip(infowindow,marker,results[0].formatted_address);
										document.getElementById("searchStoreAddress");
								}
						}
				});
		});
	}
	
	function bindDataToForm(address,lat,lng){
		document.getElementById("store_location").value = address;
		document.getElementById("store_lat").value = lat;
		document.getElementById("store_lng").value = lng;
	}
	function showTooltip(infowindow,marker,address){
	 google.maps.event.addListener(marker, "click", function() { 
				infowindow.setContent(address);
				infowindow.open(map, marker);
		});
	}
	
	$is_initialize = false;
	$('#wcfm_settings_form_identity_head').click(function() {
		if( !$is_initialize && jQuery("#store_lat").length > 0 ) {
			setTimeout( function() {
				initialize();
				//google.maps.event.addDomListener(window, "load", initialize);
				$is_initialize = true;
			}, 1000 );
		}
	});
	
	// WCMp paymode settings options
	if( $('#_vendor_payment_mode').length > 0 ) {
		$('#_vendor_payment_mode').change(function() {
			$payment_mode = $(this).val();
			$('.paymode_field').hide();
			$('.paymode_' + $payment_mode).show();
			resetCollapsHeight($('#_vendor_payment_mode'));
		}).change();
	}
})(jQuery);