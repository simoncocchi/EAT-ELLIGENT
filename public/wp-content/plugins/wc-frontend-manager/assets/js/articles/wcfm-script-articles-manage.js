var article_form_is_valid = true;
jQuery( document ).ready( function( $ ) {
		
	// Collapsible
  $('.wcfm-tabWrap .page_collapsible').collapsible({
		defaultOpen: 'wcfm_products_manage_form_inventory_head',
		speed: 'slow',
		loadOpen: function (elem) { //replace the standard open state with custom function
			//console.log(elem);
		  elem.next().show();
		},
		loadClose: function (elem, opts) { //replace the close state with custom function
			//console.log(elem);
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
	$('.wcfm-tabWrap .page_collapsible').each(function() {
		$(this).html('<div class="page_collapsible_content_holder">' + $(this).html() + '</div>');
		$(this).find('.page_collapsible_content_holder').after( $(this).find('span') );
	});
	$('.wcfm-tabWrap .page_collapsible').find('span').addClass('wcfmfa');
	$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
	$('.wcfm-tabWrap').find('.wcfm-container').hide();
	$('.wcfm-tabWrap').find('.page_collapsible:first').click();
	
	if( $("#article_cats").length > 0 ) {
		$("#article_cats").select2({
			placeholder: wcfm_dashboard_messages.choose_category_select2,
			maximumSelectionLength: $("#article_cats").data('catlimit')
		});
	}
	
	if( $('.article_taxonomies').length > 0 ) {
		$('.article_taxonomies').each(function() {
			$("#" + $(this).attr('id')).select2({
				placeholder: wcfm_dashboard_messages.choose_select2 + $(this).attr('id') + " ..."
			});
		});
	}
	
	if( $("#wcfm_associate_vendor").length > 0 ) {
		$("#wcfm_associate_vendor").select2({
			placeholder: wcfm_dashboard_messages.choose_vendor_select2
		});
	}
	
	if( $('#wcfm_vendor').length > 0 ) {
		$('#wcfm_vendor').select2( $wcfm_vendor_select_args );
	}
	
	// Category checklist view cat limit control
	if( $('#article_cats_checklist').length > 0 ) {
		var catlimit = $('#article_cats_checklist').data('catlimit');
		if( catlimit != -1 ) {
			$('#article_cats_checklist').find('.wcfm-checkbox').change(function() {
			  var checkedCount = $('#article_cats_checklist').find('.wcfm-checkbox:checked').length;
			  if( checkedCount > catlimit ) {
			  	$(this).attr( 'checked', false );
			  }
			});
		}
	}
	
	if( $('#article_cats_checklist').length > 0 ) {
		$('.sub_checklist_toggler').each(function() {
			if( $(this).parent().find('.article_taxonomy_sub_checklist').length > 0 ) { $(this).css( 'visibility', 'visible' ); }
		  $(this).click(function() {
		    $(this).toggleClass('fa-arrow-circle-down');
		    $(this).parent().find('.article_taxonomy_sub_checklist').toggleClass('article_taxonomy_sub_checklist_visible');
		  });
		});
		$('.article_cats_checklist_item_hide_by_cap').attr( 'disabled', true );
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
								$( '#article_cats_checklist' ).prepend( response );
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
	
	// Tag Cloud
	if( $('.wcfm_fetch_tag_cloud').length > 0 ) {
		$wcfm_tag_cloud_fetched = false;
		$('.wcfm_fetch_tag_cloud').click(function() {
		  if( !$wcfm_tag_cloud_fetched ) {
				var data = {
					action : 'get-tagcloud',
					tax    : 'post_tag'
				}	
				$.post(wcfm_params.ajax_url, data, function(response) {
					if(response) {
						$('.wcfm_fetch_tag_cloud').html(response);
						$wcfm_tag_cloud_fetched = true;
						
						$('.tag-cloud-link').each(function() {
						  $(this).click(function(event) {
						  	event.preventDefault();
						  	$tag = $(this).text();
						  	$tags = $('#article_tags').val();
						  	if( $tags.length > 0 ) {
						  		$tags += ',' + $tag;
						  	} else {
						  		$tags = $tag;
						  	}
						  	$('#article_tags').val($tags);
						  });
						});
					}
				});
			}
		});
	}
	
	if( typeof gmw_forms != 'undefined' ) {
		// Geo my WP Support
		if( typeof tinymce != 'undefined' ) {
			tinymce.PluginManager.add('geomywp', function(editor, url) {
				// Add a button that opens a window
				editor.addButton('geomywp', {
					text: 'GMW Form',
					icon: false,
					onclick: function() {
						// Open window
						editor.windowManager.open({
							title: 'GMW Form',
							body: [
								{type: 'listbox', name: 'form_type', label: 'Form Type', values: [{text: 'Form', value: 'form'}, {text: 'Map', value: 'map'}, {text: 'Results', value: 'results'}]},
								{type: 'listbox', name: 'gmw_forms', label: 'Select Form', values: gmw_forms}
							],
							onsubmit: function(e) {
								// Insert content when the window form is submitted
								if(e.data.form_type == 'results') {
									editor.insertContent('[gmw form="results"]');
								} else if(e.data.form_type == 'map') {
									editor.insertContent('[gmw map="' + e.data.gmw_forms + '"]');
								} else {
									editor.insertContent('[gmw form="' + e.data.gmw_forms + '"]');
								}
							}
						});
					}
				});
			});
		}
		
		tinyMce_toolbar += ' | geomywp';
		// TinyMCE intialize - Short description
		if( $('#excerpt').hasClass('rich_editor') ) {
			if( typeof tinymce != 'undefined' ) {
				var shdescTinyMCE = tinymce.init({
																			selector: '#excerpt',
																			height: 75,
																			menubar: false,
																			plugins: [
																				'advlist autolink lists link charmap print preview anchor',
																				'searchreplace visualblocks code fullscreen',
																				'insertdatetime image media table paste code geomywp directionality'
																			],
																			toolbar: tinyMce_toolbar,
																			content_css: '//www.tinymce.com/css/codepen.min.css',
																			statusbar: false,
																			browser_spellcheck: true,
																			entity_encoding: "raw"
																		});
			}
		}
		
		// TinyMCE intialize - Description
		if( $('#description').hasClass('rich_editor') ) {
			if( typeof tinymce != 'undefined' ) {
				var descTinyMCE = tinymce.init({
																			selector: '#description',
																			height: 75,
																			menubar: false,
																			plugins: [
																				'advlist autolink lists link charmap print preview anchor',
																				'searchreplace visualblocks code fullscreen',
																				'insertdatetime image media table paste code geomywp directionality',
																				'autoresize'
																			],
																			toolbar: tinyMce_toolbar,
																			content_css: '//www.tinymce.com/css/codepen.min.css',
																			statusbar: false,
																			browser_spellcheck: true,
																			entity_encoding: "raw"
																		});
			}
		}
	} else {
		// TinyMCE intialize - Short description
		if( $('#excerpt').hasClass('rich_editor') ) {
			if( typeof tinymce != 'undefined' ) {
				var shdescTinyMCE = tinymce.init({
																			selector: '#excerpt',
																			height: 75,
																			menubar: false,
																			plugins: [
																				'advlist autolink lists link charmap print preview anchor',
																				'searchreplace visualblocks code fullscreen',
																				'insertdatetime image media table paste code directionality'
																			],
																			toolbar: tinyMce_toolbar,
																			content_css: '//www.tinymce.com/css/codepen.min.css',
																			statusbar: false,
																			browser_spellcheck: true,
																			entity_encoding: "raw"
																		});
			}
		}
		
		// TinyMCE intialize - Description
		if( $('#description').hasClass('rich_editor') ) {
			if( typeof tinymce != 'undefined' ) {
				var descTinyMCE = tinymce.init({
																			selector: '#description',
																			//height: 75,
																			menubar: false,
																			plugins: [
																				'advlist autolink lists link charmap print preview anchor',
																				'searchreplace visualblocks code fullscreen',
																				'insertdatetime image media table paste code directionality',
																				'autoresize'
																			],
																			toolbar: tinyMce_toolbar,
																			content_css: '//www.tinymce.com/css/codepen.min.css',
																			statusbar: false,
																			browser_spellcheck: true,
																			entity_encoding: "raw"
																		});
			}
		}
	}
	
	function wcfm_articles_manage_form_validate() {
		article_form_is_valid = true;
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		var title = $.trim($('#wcfm_articles_manage_form').find('#title').val());
		$('#wcfm_articles_manage_form').find('#title').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');
		if(title.length == 0) {
			$('#wcfm_articles_manage_form').find('#title').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
			article_form_is_valid = false;
			$('#wcfm_articles_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_articles_manage_messages.no_title).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
		
		$( document.body ).trigger( 'wcfm_articles_manage_form_validate' );
		
		$wcfm_is_valid_form = article_form_is_valid;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_articles_manage_form') );
		article_form_is_valid = $wcfm_is_valid_form;
		
		return article_form_is_valid;
	}
	
	// Draft Article
	$('#wcfm_articles_simple_draft_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  // Validations
	  $is_valid = wcfm_articles_manage_form_validate();
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var excerpt = getWCFMEditorContent( 'excerpt' );
			
			var description = getWCFMEditorContent( 'description' );
			
			var data = {
				action : 'wcfm_ajax_controller',
				controller : 'wcfm-articles-manage', 
				wcfm_articles_manage_form : $('#wcfm_articles_manage_form').serialize(),
				excerpt     : excerpt,
				description : description,
				status : 'draft'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_articles_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
							if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_articles_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#article_id').val($response_json.id);
					$('#wcfm-content').unblock();
					$('.wcfm_submit_button').show();
				}
			});	
		} else {
			$('.wcfm_submit_button').show();
		}
	});
	
	// Submit Article
	$('#wcfm_articles_simple_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  // Validations
	  $is_valid = wcfm_articles_manage_form_validate();
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var excerpt = getWCFMEditorContent( 'excerpt' );
			
			var description = getWCFMEditorContent( 'description' );
			
			var data = {
				action : 'wcfm_ajax_controller',
				controller : 'wcfm-articles-manage',
				wcfm_articles_manage_form : $('#wcfm_articles_manage_form').serialize(),
				excerpt     : excerpt,
				description : description,
				status : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_articles_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_articles_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#article_id').val($response_json.id);
					wcfmMessageHide();
					$('#wcfm-content').unblock();
					$('.wcfm_submit_button').show();
				}
			});
		} else {
			$('.wcfm_submit_button').show();
		}
	});
	
	function jsUcfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }
} );