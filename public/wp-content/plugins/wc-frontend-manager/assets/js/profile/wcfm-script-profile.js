jQuery(document).ready( function($) {
	// Collapsible
	$('.page_collapsible').collapsible({
		defaultOpen: 'wcfm_profile_personal_head',
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
		if(window.location.hash) {
			$('.wcfm-tabWrap').find(window.location.hash).click();
		} else {
			$('.wcfm-tabWrap').find('.page_collapsible:first').click();
		}
	}, 500 );
	
	// Tabheight  
	$('.page_collapsible').each(function() {
		if( !$(this).hasClass('wcfm_head_hide') ) {
			collapsHeight += $(this).height() + 100;
		}
	});  
	
	// Email Verification
	if( $('.wcfm_email_verified_button').length > 0 ) {
		$('#email').on( 'blur', function() {
			if( $(this).hasClass( 'wcfm_verification_code_sender' ) ) {
				sendEmailVerificationCode();
			}
		});
		$('.wcfm_email_verified_button').on( 'click', function(e) {
			e.preventDefault();
			sendEmailVerificationCode();
			return false;
		});
	}
	
	function sendEmailVerificationCode() {
		
		$('#email').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
	  $is_valid = true;
	  if( $is_valid ) {
	  	$user_email = $('#email').val();
	  	if( !$user_email ) {
	  		$('#email').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
	  		$('#wcfm_profile_form .wcfm-message').html( '<span class="wcicon-status-cancelled"></span>' + $('#email').data('required_message') ).addClass('wcfm-error').slideDown();
	  		$is_valid = false;
	  	} else {
	  		$('#email').addClass('wcfm_validation_success').removeClass('wcfm_validation_failed');
	  	}
	  }
	  
		if( $is_valid ) {
			var data = {
				action                             : 'wcfm_email_verification_code',
				user_email                         : $('#email').val()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_profile_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						$('#wcfm_profile_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					wcfmMessageHide();
					$('#email').unblock();
				}
			});
		} else {
			$('#email').unblock();
		}
	}
	
	if( $(".wcfm_multi_select").length > 0 ) {
		$(".wcfm_multi_select").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	if( $(".country_select").length > 0 ) {
		$(".country_select").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	var wcfm_customer_billing_address_select = {
			init: function () {
				$('#wcfm_profile_address_expander').on( 'change', 'select#bcountry', this.state_select );
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
				$('#wcfm_profile_address_expander').on( 'change', 'select#scountry', this.state_select );
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
	  	resetCollapsHeight($('.collapse-open').next('.wcfm-container').find('.wcfm_ele:not(.wcfm_title):first'));
	  }
	}).change();
	
	$('#password').keyup(function() {
		if( wcfm_profile_params.is_strength_check ) {
			checkStrength($('#password').val());
		}
	});
	
	function checkStrength( password ) {
		var strength = 0
		if (password.length < 6) {
			$('#password_strength').removeClass();
			$('#password_strength').addClass('short')
			$('#password_strength').html(wcfm_profile_params.short);
			return 'short';
		}
		if (password.length > 7) strength += 1
		// If password contains both lower and uppercase characters, increase strength value.
		if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
		// If it has numbers and characters, increase strength value.
		if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1
		// If it has one special character, increase strength value.
		if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// If it has two special characters, increase strength value.
		if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// Calculated strength value, we can return messages
		// If value is less than 2
		if (strength < 2) {
			$('#password_strength').removeClass()
			$('#password_strength').addClass('weak')
			$('#password_strength').html( wcfm_profile_params.weak );
			return 'weak';
		} else if (strength == 2) {
			$('#password_strength').removeClass()
			$('#password_strength').addClass('good')
			$('#password_strength').html( wcfm_profile_params.good );
			return 'good';
		} else {
			$('#password_strength').removeClass()
			$('#password_strength').addClass('strong')
			$('#password_strength').html( wcfm_profile_params.strong );
			return 'strong';
		}
	}
	
	// Save Profile
	$('#wcfmprofile_save_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  var about = getWCFMEditorContent( 'about' );
  
	  // Validations
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
	  $wcfm_is_valid_form = true;
	  $( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_profile_form') );
	  $is_valid = $wcfm_is_valid_form;
	  
	  if( $is_valid ) {
	  	$passowrd = $('#password').val();
	  	if( $passowrd && wcfm_profile_params.is_strength_check ) {
				$password_strength = checkStrength( $passowrd );
				if( ( $password_strength == 'short') || ( $password_strength == 'weak' ) ) {
					$('#passoword').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
					$('#wcfm_profile_form .wcfm-message').html( '<span class="wcicon-status-cancelled"></span>' + wcfm_profile_params.passowrd_failed ).addClass('wcfm-error').slideDown();
					$is_valid = false;
				}
			}
	  }
	  
	  if($is_valid) {
			$('#wcfm_profile_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action             : 'wcfm_ajax_controller',
				controller         : 'wcfm-profile',
				wcfm_profile_form  : $('#wcfm_profile_form').serialize(),
				user_email         : $('#email').val(),
				about              : about
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						wcfm_notification_sound.play();
						$('#wcfm_profile_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						wcfm_notification_sound.play();
						$('#wcfm_profile_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfm_profile_form').unblock();
					$('.wcfm_submit_button').show();
				}
			});	
		} else {
			$('.wcfm_submit_button').show();
		}
	});
});