(function($) {
	var wcfm_dokan_address_wrapper = $( '.store_address' );
	var wcfm_dokan_address_select = {
		init: function () {
			wcfm_dokan_address_wrapper.on( 'change', 'select#country', this.state_select );
			jQuery('select#country').change();
		},
		state_select: function () {
			var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
					states = $.parseJSON( states_json ),
					$statebox = $( '#state' ),
					value = $statebox.val(),
					country = $( this ).val();
					$state_required = $statebox.data('required');

			if ( states[ country ] ) {
				if ( $.isEmptyObject( states[ country ] ) ) {
					if ( $statebox.is( 'select' ) ) {
						if( typeof $state_required != 'undefined') {
							$( 'select#state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="address[state]" id="state" data-required="1" data-required_message="State/County: This field is required." />' );
						} else {
							$( 'select#state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="address[state]" id="state" />' );
						}
					}
					$( '#state' ).val( 'N/A' );
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
						if( typeof $state_required != 'undefined') {
							$( 'input#state' ).replaceWith( '<select class="wcfm-select wcfm_ele" name="address[state]" id="state" data-required="1" data-required_message="State/County: This field is required."></select>' );
						} else {
							$( 'input#state' ).replaceWith( '<select class="wcfm-select wcfm_ele" name="address[state]" id="state"></select>' );
						}
						$( 'select#state' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
					}

				}
			} else {
				if ( $statebox.is( 'select' ) ) {
					//input_selected_state = '';
					if( typeof $state_required != 'undefined') {
						$( 'select#state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="address[state]" id="state" data-required="1" data-required_message="State/County: This field is required." />' );
					} else {
						$( 'select#state' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="address[state]" id="state" />' );
					}
				}
				$( '#state' ).val(input_selected_state);

				if ( $( '#state' ).val() == 'N/A' ) {
					$( '#state' ).val('');
				}
			}
		}
	}

	$(function() {
			wcfm_dokan_address_select.init();

			$('#phone').keydown(function(e) {
				// Allow: backspace, delete, tab, escape, enter and .
				if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||
						 // Allow: Ctrl+A
						(e.keyCode == 65 && e.ctrlKey === true) ||
						 // Allow: home, end, left, right
						(e.keyCode >= 35 && e.keyCode <= 39)) {
								 // let it happen, don't do anything
						return;
				}

				// Ensure that it is a number and stop the keypress
				if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
						e.preventDefault();
				}
			});
			
			
			try {
					var curpoint = new google.maps.LatLng(def_latval, def_longval),
							geocoder   = new window.google.maps.Geocoder(),
							$map_area = $('#wcfm-dokan-map'),
							$input_area = $( '#location' ),
							$input_add = $( '#find_address' );
					var gmap = '';
					var marker = '';
							
					setTimeout(function() {
						gmap = new google.maps.Map( $map_area[0], {
								center: curpoint,
								zoom: def_zoomval,
								mapTypeId: window.google.maps.MapTypeId.ROADMAP
						});
	
						marker = new window.google.maps.Marker({
								position: curpoint,
								map: gmap,
								draggable: true
						});
					
					
						window.google.maps.event.addListener( gmap, 'click', function ( event ) {
								marker.setPosition( event.latLng );
								updatePositionInput( event.latLng );
						} );
	
						window.google.maps.event.addListener( marker, 'drag', function ( event ) {
								updatePositionInput(event.latLng );
						} );
					}, 1000 );
			} catch( e ) {
					console.log( 'Google API not found.' );
			}
			
		
			autoCompleteAddress();
			

			function updatePositionInput( latLng ) {
					$input_area.val( latLng.lat() + ',' + latLng.lng() );
			}

			function updatePositionMarker() {
					var coord = $input_area.val(),
							pos, zoom;

					if ( coord ) {
							pos = coord.split( ',' );
							marker.setPosition( new window.google.maps.LatLng( pos[0], pos[1] ) );

							zoom = pos.length > 2 ? parseInt( pos[2], 10 ) : 12;

							gmap.setCenter( marker.position );
							gmap.setZoom( zoom );
					}
			}

			function geocodeAddress( address ) {
					geocoder.geocode( {'address': address}, function ( results, status ) {
							if ( status == window.google.maps.GeocoderStatus.OK ) {
									updatePositionInput( results[0].geometry.location );
									marker.setPosition( results[0].geometry.location );
									gmap.setCenter( marker.position );
									gmap.setZoom( 15 );
							}
					} );
			}

			function autoCompleteAddress() {
				if (!$input_add) return null;

				$input_add.autocomplete({
						source: function(request, response) {
								// TODO: add 'region' option, to help bias geocoder.
								geocoder.geocode( {'address': request.term }, function(results, status) {
										response(jQuery.map(results, function(item) {
												return {
														label     : item.formatted_address,
														value     : item.formatted_address,
														latitude  : item.geometry.location.lat(),
														longitude : item.geometry.location.lng()
												};
										}));
								});
						},
						select: function(event, ui) {

								$input_area.val(ui.item.latitude + ',' + ui.item.longitude );

								var location = new window.google.maps.LatLng(ui.item.latitude, ui.item.longitude);

								gmap.setCenter(location);
								// Drop the Marker
								setTimeout( function(){
										marker.setValues({
												position    : location,
												animation   : window.google.maps.Animation.DROP
										});
								}, 1500);
						}
				});
			}
			
	});
	
	
	
	// Shipping rates country change
	function setStateBoxforCountry( countryBox ) {
		var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
				states = $.parseJSON( states_json ),
				country = countryBox.val();

		if ( states[ country ] ) {
			if ( $.isEmptyObject( states[ country ] ) ) {
				countryBox.parent().find('.dps_state_to_select').each(function() {
					$statebox = $(this);
					$statebox_id = $statebox.attr('id');
					$statebox_name = $statebox.attr('name');
					$statebox_val = $statebox.val();
					if( $statebox_val === null ) $statebox_val = '';
					$statebox_dataname = $statebox.data('name');
					
					if ( $statebox.is( 'select' ) ) {
						$statebox.replaceWith( '<input type="text" name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" value="'+$statebox_val+'" class="wcfm-text dps_state_to_select multi_input_block_element" />' );
					}
				});
			} else {
				input_selected_state = '';
				var options = '',
						state = states[ country ];

				countryBox.parent().find('.dps_state_to_select').each(function() {
					$statebox = $(this);
					$statebox_id = $statebox.attr('id');
					$statebox_name = $statebox.attr('name');
					$statebox_val = $statebox.val();
					if( $statebox_val === null ) $statebox_val = '';
					$statebox_dataname = $statebox.data('name');
					
					for ( var index in state ) {
						if ( state.hasOwnProperty( index ) ) {
							if ( $statebox_val ) {
								if ( $statebox_val == index ) {
									var selected_value = 'selected="selected"';
								} else {
									var selected_value = '';
								}
							}
							options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
						}
					}
					
					if ( $statebox.is( 'select' ) ) {
						$statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option><optgroup label="-------------------------------------"><option value="everywhere">'+wcfm_dashboard_messages.everywhere+'</option></optgroup><optgroup label="-------------------------------------">' + options + '</optgroup>' );
					}
					if ( $statebox.is( 'input' ) ) {
						$statebox.replaceWith( '<select name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" class="wcfm-select dps_state_to_select multi_input_block_element"></select>' );
						$statebox = $('#'+$statebox_id);
						$statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option><optgroup label="-------------------------------------"><option value="everywhere">'+wcfm_dashboard_messages.everywhere+'</option></optgroup><optgroup label="-------------------------------------">' + options + '</optgroup>' );
					}
					$statebox.val( $statebox_val );
				});
			}
		} else {
			countryBox.parent().find('.dps_state_to_select').each(function() {
				$statebox = $(this);
				$statebox_id = $statebox.attr('id');
				$statebox_name = $statebox.attr('name');
				$statebox_val = $statebox.val();
				if( $statebox_val === null ) $statebox_val = '';
				$statebox_dataname = $statebox.data('name');
				
				if ( $statebox.is( 'select' ) ) {
					$statebox.replaceWith( '<input type="text" name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" value="'+$statebox_val+'" class="wcfm-text dps_state_to_select multi_input_block_element" />' );
				}
			});
		}
		
		if( country == 'everywhere' ) {
			countryBox.parent().find('.dps_shipping_state_rates_label').addClass('wcfm_custom_hide');
			countryBox.parent().find('.multi_input_holder').addClass('wcfm_custom_hide');
		} else {
			countryBox.parent().find('.dps_shipping_state_rates_label').removeClass('wcfm_custom_hide');
			countryBox.parent().find('.multi_input_holder').removeClass('wcfm_custom_hide');
		}
	}
	
	$('.dps_country_to_select').each(function() {
	  $(this).change(function() {
	    setStateBoxforCountry( $(this) );
	  }).change();
	});
	
	setTimeout(function() {
		$('#dps_shipping_rates').children('.multi_input_block').children('.add_multi_input_block').click(function() {
			$('#dps_shipping_rates').children('.multi_input_block:last').find('.dps_country_to_select').select2();
			$('#dps_shipping_rates').children('.multi_input_block:last').find('.dps_country_to_select').change(function() {
				setStateBoxforCountry( $(this) );
			}).change();
		});
	}, 1000 );
	
	$('input[name="wcfm_dokan_regular_shipping"]').change(function() {
	  if( $(this).is(':checked') ) {
	  	$('.wcfm_dokan_non_regular_shipping').hide();
	  	$('.wcfm_dokan_regular_shipping').show();
	  } else {
	  	$('.wcfm_dokan_non_regular_shipping').show();
	  	$('.wcfm_dokan_regular_shipping').hide();
	  }
	  resetCollapsHeight($('#dps_shipping_type_price'));
	}).change();
	
	function blockModalClose() {
		$('button.modal-close').off('click').on('click', function(e) {
			e.preventDefault();
			return false;
		});
		setTimeout(function() {
			blockModalClose();
		}, 100 );
	}
	blockModalClose();
	
})(jQuery);