jQuery(document).ready(function($) {
		
	// Collapsible
	$('.page_collapsible').collapsible({
		defaultOpen: 'wcfm_customer_address_head',
		speed: 'slow',
		loadOpen: function (elem) { //replace the standard open state with custom function
				elem.next().show();
		},
		loadClose: function (elem, opts) { //replace the close state with custom function
				elem.next().hide();
		},
		animateOpen: function(elem, opts) {
			$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
			elem.addClass('collapse-open');
			$('.collapse-close').find('span').removeClass('fa-arrow-alt-circle-right block-indicator');
			elem.find('span').addClass('fa-arrow-alt-circle-right block-indicator');
			$('.wcfm-tabWrap').find('.wcfm-container').stop(true, true).slideUp(opts.speed);
			elem.next().stop(true, true).slideDown(opts.speed);
		},
		animateClose: function(elem, opts) {
			elem.find('span').removeClass('fa-arrow-circle-up block-indicator');
			elem.next().stop(true, true).slideUp(opts.speed);
		}
	});
	$('.page_collapsible').each(function() {
		$(this).html('<div class="page_collapsible_content_holder">' + $(this).html() + '</div>');
		$(this).find('.page_collapsible_content_holder').after( $(this).find('span') );
	});
	$('.page_collapsible').find('span').addClass('wcfmfa');
	$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
	$('.wcfm-tabWrap').find('.wcfm-container').hide();
	setTimeout(function() {
		$('.wcfm-tabWrap').find('.page_collapsible:first').click();
	}, 500 );
	
	// Tabheight  
	$('.page_collapsible').each(function() {
		if( !$(this).hasClass('wcfm_head_hide') ) {
			collapsHeight += $(this).height() + 50;
		}
	});  
	
	if( $('#wcfm_vendor').length > 0 ) {
		$('#wcfm_vendor').select2( $wcfm_vendor_select_args );
	}
		
	function wcfm_customers_manage_form_validate() {
		$is_valid = true;
		$('.wcfm-message').html('').removeClass('wcfm-error').slideUp();
		var user_name = $.trim($('#wcfm_customers_manage_form').find('#user_name').val());
		var user_email = $.trim($('#wcfm_customers_manage_form').find('#user_email').val());
		if(user_name.length == 0) {
			$is_valid = false;
			$('#wcfm_customers_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_customers_manage_messages.no_username).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		} else if(user_email.length == 0) {
			$is_valid = false;
			$('#wcfm_customers_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_customers_manage_messages.no_email).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
		return $is_valid;
	}
	
	// Submit Customer
	$('#wcfm_customer_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  // Validations
	  $is_valid = wcfm_customers_manage_form_validate();
	  if( $is_valid ) {
			$wcfm_is_valid_form = true;
			$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_customers_manage_form') );
			$is_valid = $wcfm_is_valid_form;
		}
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                   : 'wcfm_ajax_controller',
				controller               : 'wcfm-customers-manage',
				wcfm_customers_manage_form : $('#wcfm_customers_manage_form').serialize(),
				status                   : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.redirect) {
						$('#wcfm_customers_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_customers_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#customer_id').val($response_json.id);
					wcfmMessageHide();
					$('#wcfm-content').unblock();
					$('.wcfm_submit_button').show();
				}
			});
		} else {
			$('.wcfm_submit_button').show();
		}
	});
	
	if( $(".country_select").length > 0 ) {
		$(".country_select").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	var wcfm_customer_billing_address_select = {
			init: function () {
				$('#wcfm_customer_address_expander').on( 'change', 'select#bcountry', this.state_select );
				jQuery('select#bcountry').change();
			},
			state_select: function () {
					var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
							states = $.parseJSON( states_json ),
							$statebox = $( '#bstate' ),
							value = $statebox.val(),
							country = $( this ).val(),
							$state_required = $statebox.data('required');

					if ( states[ country ] ) {

							if ( $.isEmptyObject( states[ country ] ) ) {

								if ( $statebox.is( 'select' ) ) {
									if( typeof $state_required != 'undefined') {
										$( 'select#bstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="bstate" id="bstate" data-required="1" data-required_message="State/County: This field is required." />' );
									} else {
										$( 'select#bstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="bstate" id="bstate" />' );
									}
								}

								if( value ) {
									$( '#bstate' ).val( value );
								} else {
									$( '#bstate' ).val( 'N/A' );
								}

							} else {
									input_selected_state = '';

									var options = '',
											state = states[ country ];

									for ( var index in state ) {
											if ( state.hasOwnProperty( index ) ) {
													if ( selected_bstate ) {
															if ( selected_bstate == index ) {
																	var selected_value = 'selected="selected"';
															} else {
																	var selected_value = '';
															}
													}
													options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
											}
									}

									if ( $statebox.is( 'select' ) ) {
											$( 'select#bstate' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
									}
									if ( $statebox.is( 'input' ) ) {
										if( typeof $state_required != 'undefined') {
											$( 'input#bstate' ).replaceWith( '<select class="wcfm-select wcfm_ele" name="bstate" id="bstate" data-required="1" data-required_message="State/County: This field is required."></select>' );
										} else {
											$( 'input#bstate' ).replaceWith( '<select class="wcfm-select wcfm_ele" name="bstate" id="bstate"></select>' );
										}
										$( 'select#bstate' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
									}
									//$( '#wcmarketplace_address_state' ).removeClass( 'wcmarketplace-hide' );
									//$( 'div#wcmarketplace-states-box' ).slideDown();

							}
					} else {
						if ( $statebox.is( 'select' ) ) {
							if( typeof $state_required != 'undefined') {
								$( 'select#bstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="bstate" id="bstate" data-required="1" data-required_message="State/County: This field is required." />' );
							} else {
								$( 'select#bstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele" name="bstate" id="bstate" />' );
							}
						}
						$( '#bstate' ).val(input_selected_bstate);

						if ( $( '#bstate' ).val() == 'N/A' ){
							$( '#bstate' ).val('');
						}
						//$( '#wcmarketplace_address_state' ).removeClass( 'wcmarketplace-hide' );
						//$( 'div#wcmarketplace-states-box' ).slideDown();
					}
			}
	}
	
	wcfm_customer_billing_address_select.init();
	
	var wcfm_customer_shipping_address_select = {
			init: function () {
				$('#wcfm_customer_address_expander').on( 'change', 'select#scountry', this.state_select );
				jQuery('select#scountry').change();
			},
			state_select: function () {
					var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
							states = $.parseJSON( states_json ),
							$statebox = $( '#sstate' ),
							value = $statebox.val(),
							country = $( this ).val(),
							$state_required = $statebox.data('required');

					if ( states[ country ] ) {

							if ( $.isEmptyObject( states[ country ] ) ) {

								if ( $statebox.is( 'select' ) ) {
									if( typeof $state_required != 'undefined') {
										$( 'select#sstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele same_as_billing_ele" name="sstate" id="sstate" data-required="1" data-required_message="State/County: This field is required." />' );
									} else {
										$( 'select#sstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele same_as_billing_ele" name="sstate" id="sstate" />' );
									}
								}

								if( value ) {
									$( '#sstate' ).val( value );
								} else {
									$( '#sstate' ).val( 'N/A' );
								}

							} else {
									input_selected_state = '';

									var options = '',
											state = states[ country ];

									for ( var index in state ) {
											if ( state.hasOwnProperty( index ) ) {
													if ( selected_sstate ) {
															if ( selected_sstate == index ) {
																	var selected_value = 'selected="selected"';
															} else {
																	var selected_value = '';
															}
													}
													options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
											}
									}

									if ( $statebox.is( 'select' ) ) {
											$( 'select#sstate' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
									}
									if ( $statebox.is( 'input' ) ) {
										if( typeof $state_required != 'undefined') {
											$( 'input#sstate' ).replaceWith( '<select class="wcfm-select wcfm_ele same_as_billing_ele" name="sstate" id="sstate" data-required="1" data-required_message="State/County: This field is required."></select>' );
										} else {
											$( 'input#sstate' ).replaceWith( '<select class="wcfm-select wcfm_ele same_as_billing_ele" name="sstate" id="sstate"></select>' );
										}
										$( 'select#sstate' ).html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
									}
									//$( '#wcmarketplace_address_state' ).removeClass( 'wcmarketplace-hide' );
									//$( 'div#wcmarketplace-states-box' ).slideDown();

							}
					} else {
						if ( $statebox.is( 'select' ) ) {
							if( typeof $state_required != 'undefined') {
								$( 'select#sstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele same_as_billing_ele" name="sstate" id="sstate" data-required="1" data-required_message="State/County: This field is required." />' );
							} else {
								$( 'select#sstate' ).replaceWith( '<input type="text" class="wcfm-text wcfm_ele same_as_billing_ele" name="sstate" id="sstate" />' );
							}
						}
						$( '#bstate' ).val(input_selected_sstate);

						if ( $( '#bstate' ).val() == 'N/A' ){
							$( '#bstate' ).val('');
						}
					}
			}
	}
	
	wcfm_customer_shipping_address_select.init();
	
	$('#same_as_billing').change(function() {
	  if( $('#same_as_billing').is(':checked') ) {
	  	$('.same_as_billing_ele').addClass('wcfm_ele_hide');
	  	$('.same_as_billing_ele').next('.select2').addClass('wcfm_ele_hide');
	  } else {
	  	$('.same_as_billing_ele').removeClass('wcfm_ele_hide');
	  	$('.same_as_billing_ele').next('.select2').removeClass('wcfm_ele_hide');
	  }
	  resetCollapsHeight($('#same_as_billing').parent());
	}).change();
} );