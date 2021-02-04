jQuery(document).ready( function($) {
		
	if( $('#knowledgebase_cats_checklist').length > 0 ) {
		$('.sub_checklist_toggler').each(function() {
			if( $(this).parent().find('.product_taxonomy_sub_checklist').length > 0 ) { $(this).css( 'visibility', 'visible' ); }
		  $(this).click(function() {
		    $(this).toggleClass('fa-arrow-circle-down');
		    $(this).parent().find('.product_taxonomy_sub_checklist').toggleClass('product_taxonomy_sub_checklist_visible');
		  });
		});
		$('.product_cats_checklist_item_hide_by_cap').attr( 'disabled', true );
	}
		
	// Add New Taxonomy
	$('.wcfm_add_new_taxonomy').each(function() {
		$(this).on('click', function() {
			$(this).parent().find('.wcfm_add_new_taxonomy_form').toggleClass('wcfm_add_new_taxonomy_form_hide');
		});
	});
	$('.wcfm_add_taxonomy_bt').each(function() {
		$(this).on('click', function() {
			$wrapper = $(this).parent();
			if( $wrapper.find('.wcfm_new_tax_ele').val() ) {
				$taxonomy = $(this).data('taxonomy');
				$new_term = $wrapper.find('.wcfm_new_tax_ele').val();
				$parent_term = $wrapper.find('.wcfm_new_parent_taxt_ele').val();
				var data         = {
					action:       'wcfm_add_taxonomy_new_term',
					taxonomy:     $taxonomy,
					new_term:     $new_term,
					parent_term:  $parent_term
				};
		
				$('.wcfm_add_new_taxonomy_box').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
				
				$.ajax({
					type:		'POST',
					url: wcfm_params.ajax_url,
					data: data,
					success:	function(response) {
						if(response) {
							if ( response.error ) {
								// Error.
								window.alert( response.error );
							} else {
								// Success.
								$( '#knowledgebase_cats_checklist' ).prepend( response );
								$wrapper.find('.wcfm_new_tax_ele').val('');
								$wrapper.find('.wcfm_new_parent_taxt_ele').val(0);
							}
			
							$( '.wcfm_add_new_taxonomy_box' ).unblock();
						}
					}
				});
			}
		});
	});
		
	function wcfm_knowledgebase_manage_form_validate() {
		$is_valid = true;
		$('.wcfm-message').html('').removeClass('wcfm-error').slideUp();
		var title = $.trim($('#wcfm_knowledgebase_manage_form').find('#title').val());
		if(title.length == 0) {
			$is_valid = false;
			$('#wcfm_knowledgebase_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_knowledgebase_manage_messages.no_title).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
		return $is_valid;
	}
	
	// Submit Knowledgebase
	$('#wcfm_knowledgebase_manager_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
	  $is_valid = wcfm_knowledgebase_manage_form_validate();
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var content = getWCFMEditorContent( 'wcfm_knowledgebase' );
			
			var data = {
				action                   : 'wcfm_ajax_controller',
				controller               : 'wcfm-knowledgebase-manage',
				wcfm_knowledgebase_manage_form : $('#wcfm_knowledgebase_manage_form').serialize(),
				content                  : content,
				status                   : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_knowledgebase_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_knowledgebase_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#knowledgebase_id').val($response_json.id);
					wcfmMessageHide();
					$('#wcfm-content').unblock();
				}
			});
		}
	});
} );