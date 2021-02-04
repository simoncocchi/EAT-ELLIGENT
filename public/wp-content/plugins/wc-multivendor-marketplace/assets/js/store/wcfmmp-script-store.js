jQuery(document).ready(function($) {
	
	// Set Bootstrap full width class
	if( $('#wcfmmp-store').parent().hasClass('col-md-8') ) {
		$('#wcfmmp-store').parent().removeClass('col-md-8');
		$('#wcfmmp-store').parent().addClass('col-md-12');
	}
	if( $('#wcfmmp-store').parent().hasClass('col-md-9') ) {
		$('#wcfmmp-store').parent().removeClass('col-md-9');
		$('#wcfmmp-store').parent().addClass('col-md-12');
	}
	if( $('#wcfmmp-store').parent().hasClass('col-sm-8') ) {
		$('#wcfmmp-store').parent().removeClass('col-sm-8');
		$('#wcfmmp-store').parent().addClass('col-md-12');
	}
	if( $('#wcfmmp-store').parent().hasClass('col-sm-9') ) {
		$('#wcfmmp-store').parent().removeClass('col-sm-9');
		$('#wcfmmp-store').parent().addClass('col-md-12');
	}
	$('#wcfmmp-store').parent().removeClass('col-sm-push-3');
	$('#wcfmmp-store').parent().removeClass('col-sm-push-4');
	$('#wcfmmp-store').parent().removeClass('col-md-push-3');
	$('#wcfmmp-store').parent().removeClass('col-md-push-4');
		
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
	
	// Store Address Block
	if( $(window).width() > 768 ) {
		$wcfm_store_header_width = $('#wcfm_store_header').outerWidth();
		$('#wcfmmp-store .address').css( 'width', (2*($wcfm_store_header_width/3))-100 );
	}
		
  // Store Map
  $store_address = jQuery(".wcfm_store_address").val();
  $store_lat = jQuery(".wcfm_store_lat").val();
	$store_lng = jQuery(".wcfm_store_lng").val();
  function initialize() {
  	$('.wcfmmp-store-map').each(function() {
  		$store_map = $(this).attr('id');
  		
  		$('#'+$store_map).css( 'height', $('#'+$store_map).outerWidth());
  		
  		if( wcfm_maps.lib == 'google' ) {
				var latlng = new google.maps.LatLng( $store_lat, $store_lng );
				var map = new google.maps.Map(document.getElementById($store_map), {
						center: latlng,
						blur : true,
						mapTypeId: wcfm_maps.map_type,
						zoom: parseInt( wcfmmp_store_map_options.default_zoom )
				});
				var customIcon = {
														url: wcfmmp_store_map_options.store_icon,
														scaledSize: new google.maps.Size( wcfmmp_store_map_options.icon_width, wcfmmp_store_map_options.icon_height ), // scaled size
														//origin: new google.maps.Point( 0, 0 ), // origin
														//anchor: new google.maps.Point( 0, 0 ) // anchor
													};
				var marker = new google.maps.Marker({
						map: map,
						position: latlng,
						animation: google.maps.Animation.DROP,
						icon: customIcon,
						draggable: false,
						//anchorPoint: new google.maps.Point(0, -29)
				});
				marker.addListener('click', function() {
					if( wcfm_params.is_mobile || wcfm_params.is_tablet ) {
						window.open( 'https://maps.google.com/?q='+$store_address+',16z?hl=en-US', '_blank');
					} else {
						window.open( 'https://google.com/maps/place/'+$store_address+'/@'+$store_lat+','+$store_lng+',16z?hl=en-US', '_blank');
					}
				});
			} else {
				
				var map = L.map( $store_map, {
						center: [$store_lat, $store_lng],
						minZoom: 2,
						zoom: parseInt( wcfmmp_store_map_options.default_zoom ),
						zoomAnimation: false
				});
				
				L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						subdomains: ['a','b','c']
				}).addTo( map );
				
				L.marker([$store_lat, $store_lng]).addTo(map).on('click', function() {
					window.open( 'https://www.openstreetmap.org/?mlat='+$store_lat+'&mlon='+$store_lng+'#map=14/'+$store_lat+'/'+$store_lng, '_blank');
				});
				
				$('a[href="#tab-wcfm_location_tab"]').click(function() {
					setTimeout(function() {
						map.invalidateSize();
					}, 500 );
				});
				
				$('a[href="#tab-wcfm_product_store_tab"]').click(function() {
					setTimeout(function() {
						map.invalidateSize();
					}, 500 );
				});
			}
		});
	}
	if( $('.wcfmmp-store-map').length > 0 ) {
		initialize();
	}
	
	// Review Ratings
	$('#stars li').on('mouseover', function() {
    var onStar = parseInt($(this).data('value'), 10); // The star currently mouse on
   
    // Now highlight all the stars that's not after the current hovered star
    $(this).parent().children('li.star').each(function(e){
      if (e < onStar) {
        $(this).addClass('hover');
      }
      else {
        $(this).removeClass('hover');
      }
    });
    
  }).on('mouseout', function(){
    $(this).parent().children('li.star').each(function(e){
      $(this).removeClass('hover');
    });
  });
  
  
  /* Start Rating */
  $('.stars').each(function() {
  	$(this).find('li').on('mouseover', function(){
			var onStar = parseInt($(this).data('value'), 10); // The star currently mouse on
		 
			// Now highlight all the stars that's not after the current hovered star
			$(this).parent().children('li.star').each(function(e){
				if (e < onStar) {
					$(this).addClass('hover');
				} else {
					$(this).removeClass('hover');
				}
			});
			
		}).on('mouseout', function(){
			$(this).parent().children('li.star').each(function(e){
				$(this).removeClass('hover');
			});
		});	
  		
  	$(this).find('li').on('click', function() {
      var onStar = parseInt($(this).data('value'), 10); // The star currently selected
      var stars = $(this).parent().children('li.star');
    
			for (i = 0; i < stars.length; i++) {
				$(stars[i]).removeClass('selected');
			}
    
			for (i = 0; i < onStar; i++) {
				$(stars[i]).addClass('selected');
			}
    
			// JUST RESPONSE (Not needed)
			var ratingValue = parseInt($(this).parent().find('li.selected').last().data('value'), 10);
			$(this).parent().parent().find('.rating_value').val(ratingValue);
			$(this).parent().parent().find('.rating_text').text(ratingValue);
		});
  });
  
  $('.store_rating_value').each(function() {
  	var onStar = parseInt($(this).val());
  	var stars = $(this).parent().children('i.fa-star');
  	for (i = 0; i < onStar; i++) {
			$(stars[i]).addClass('selected');
		}
  });
  
  // New Review 
	$('.reviews_area_live').addClass('wcfm_custom_hide');
  $('.reviews_area_dummy').find('button, input[type="text"]').click(function() {
  	if( wcfm_params.is_user_logged_in ) {
			$('.reviews_area_dummy').addClass('wcfm_custom_hide');
			$('.reviews_area_live').removeClass('wcfm_custom_hide');
		} else {
			alert(wcfm_core_dashboard_messages.user_non_logged_in);
		}
  });
  $('.reviews_area_live').find('a.cancel_review_add').click(function( event ) {
  	event.preventDefault();
  	if( wcfm_params.is_user_logged_in ) {
			$('.reviews_area_live').addClass('wcfm_custom_hide');
			$('.reviews_area_dummy').removeClass('wcfm_custom_hide');
		} else {
			alert(wcfm_core_dashboard_messages.user_non_logged_in);
		}
  	return false;
  });
  
  // Review form submit
	$('#wcfmmp_store_review_submit').click(function(event) {
	  event.preventDefault();
	  
	  var	wcfmmp_store_review_comment = $('#wcfmmp_store_review_comment').val();
	  
	  $has_rating = false;
	  $('.rating_value').each(function() {
	    $rating_value = $(this).val();
	    if( $rating_value != '0' ) $has_rating = true;
	  });
  
	  // Validations
	  $is_valid = true;
	  
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		if( !$has_rating ) {
			$is_valid = false;
			$('#wcfmmp_store_review_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>'+wcfm_reviews_messages.no_rating).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		} else if(wcfmmp_store_review_comment.length == 0) {
			$is_valid = false;
			$('#wcfmmp_store_review_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>'+wcfm_reviews_messages.no_comment).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
	  
	  if($is_valid) {
			$('#wcfmmp_store_review_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action                   : 'wcfm_ajax_controller',
				controller               : 'wcfm-reviews-submit',
				wcfm_store_review_form   : jQuery('#wcfmmp_store_review_form').serialize()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfmmp_store_review_comment').val('');
						$('#wcfmmp_store_review_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" , function() {
						  setTimeout(function() {
						  	if($response_json.redirect) {
						  		window.location.reload();
						  	}
						  	$('.reviews_area_live').addClass('wcfm_custom_hide');
						  	$('.reviews_area_dummy').addClass('wcfm_custom_hide');
						  }, 2000);
						} );
					} else {
						$('#wcfmmp_store_review_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#wcfmmp_store_review_form').unblock();
				}
			});	
		}
	});
});

jQuery(document).ready(function($) {
	if( $('.wcfm_slider_area').length > 0 ) {
		var wcfmSlider = {
			slideIndex: 1,
			timeoutId: 0,
			init: function() {
				var contentAreaWidth = $('.wcfm_slider_area').width();
				$('.wcfm_slideshow_container').css({width: contentAreaWidth, overflow: 'hidden'});
				this.bindEvents();
				this.showSlides();
	
			},
			bindEvents: function() {
				that = this;
				$('body').on('click', '.wcfm_slideshow_container .next', that.CallnextSlide);
				$('body').on('click', '.wcfm_slideshow_container .prev', that.CallprevSlide);
			},
			CallnextSlide: function() {
				wcfmSlider.plusSlides(1);
			},
			CallprevSlide: function() {
				wcfmSlider.plusSlides(-1);
			},
			plusSlides: function(n) {
				//console.log(wcfmSlider.slideIndex);
				clearTimeout(that.timeoutId);
				wcfmSlider.showSlides(wcfmSlider.slideIndex += n);
			},
			showSlides: function(n) {
				var that = this;
				var i;
				var slides = $(".wcfmSlides");
				if (n > slides.length) {that.slideIndex = 1} 
				if (n < 1) {that.slideIndex = slides.length}
				for (i = 0; i < slides.length; i++) {
					slides[i].style.display = "none"; 
				}
				slides[that.slideIndex-1].style.display = "block"; 
				that.timeoutId = setTimeout(function(){
					
					that.showSlides(that.slideIndex += 1);
				}, wcfm_slider_banner_delay.delay);
			}
			
		};  
		wcfmSlider.init();
 }
});