var removed_variations = [];
var removed_person_types = [];
var product_form_is_valid = true;
var product_variation_auto_generate = '';
var product_manage_from_popup = '';
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
			
			$( document.body ).trigger( 'wcfm_product_tab_changed', elem );
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
	//$('.wcfm-tabWrap').find('.page_collapsible:first').click();
	
	if( $("#product_cats").length > 0 ) {
		$("#product_cats").select2({
			placeholder: wcfm_dashboard_messages.choose_category_select2,
		});
	}
	
	if( $('.product_taxonomies').length > 0 ) {
		$('.product_taxonomies').each(function() {
			$("#" + $(this).attr('id')).select2({
				placeholder: wcfm_dashboard_messages.choose_select2 + " " + $('.taxonomy_'+$(this).attr('id')).text() + " ...",
			});
		});
	}
	
	if( $('.product_tags_as_dropdown').length > 0 ) {
		$('.product_tags_as_dropdown').select2({
			placeholder: wcfm_dashboard_messages.choose_tags_select2,
		});
	}
	
	if( $("#upsell_ids").length > 0 ) {
		$("#upsell_ids").select2( $wcfm_product_select_args );
	}
	
	if( $("#crosssell_ids").length > 0 ) {
		$("#crosssell_ids").select2( $wcfm_product_select_args );
	}
	
	if( $("#grouped_products").length > 0 ) {
		$("#grouped_products").select2( $wcfm_product_select_args );
	}
	
	if( $("#wcfm_associate_vendor").length > 0 ) {
		$("#wcfm_associate_vendor").select2( $wcfm_vendor_select_args );
	}
	
	if( $("#wcfm_coupon_title").length > 0 ) {
		$("#wcfm_coupon_title").select2();
	}
	
	if( $("#_restricted_countries").length > 0 ) {
		$("#_restricted_countries").select2();
	}
	
	if( $(".wcfm_multi_select").length > 0 ) {
		$(".wcfm_multi_select").select2({
			placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
		});
	}
	
	if( $('#product_cats_checklist').length > 0 ) {
		$('.sub_checklist_toggler').each(function() {
			if( $(this).parent().find('.product_taxonomy_sub_checklist').length > 0 ) { $(this).css( 'visibility', 'visible' ); }
		  $(this).click(function() {
		    $(this).toggleClass('fa-arrow-circle-down');
		    $(this).parent().find('.product_taxonomy_sub_checklist').toggleClass('product_taxonomy_sub_checklist_visible');
		  });
		});
		$('.product_cats_checklist_item_hide_by_cap').attr( 'disabled', true );
	}
	
	if($('#product_type').length > 0) {
		var pro_types = [ "simple", "variable", "grouped", "external", "booking" ];
		$('#product_type').change(function() {
			var product_type = $(this).val();
			
			// Default Tab - 3.2.6
			$product_type_org = product_type;
			setTimeout(function() {
				$('.wcfm-tabWrap .page_collapsible:not(.wcfm_head_hide):first').click();
				$.each(wcfm_product_type_default_tab, function( $pro_type, pro_default_tab ) {
					if( $pro_type == $product_type_org ) {
						if( pro_default_tab != 'wcfm_products_manage_form_inventory_head' ) {
							if( $('#' + pro_default_tab).length > 0 ) {
								$('#' + pro_default_tab).click();
							}
						}
					}
				});
			}, 700 );
			
			// Product Type wise Category Filtering - 3.0.1
			if( $("#product_cats").length > 0 ) {
				$has_cat = false;
				$('#product_cats').find('option').attr( 'disabled', true ).css( 'display', 'none' );
				$.each( wcfm_product_type_categories, function( product_type_cat, allowed_categories ) {
					if( product_type == product_type_cat ) {
						$.each( allowed_categories, function( index, allowed_category ) {
							$('#product_cats').find('.wcfm_cat_option_'+allowed_category).attr( 'disabled', false ).css( 'display', 'block' );
							$has_cat = true;
						} );	
					}
				} );
				if( !$has_cat ) {
					$('#product_cats').find('option').attr( 'disabled', false ).css( 'display', 'block' );
				}
				$('#product_cats').select2('destroy');
				$("#product_cats").select2({
					placeholder: wcfm_dashboard_messages.choose_category_select2,
					maximumSelectionLength: $("#product_cats").data('catlimit')
				});
			}
			
			// Product Type wise Category Checklist Filtering - 3.0.5
			if( $("#product_cats_checklist").length > 0 ) {
				$has_cat = false;
				$('#product_cats_checklist').find('.product_cats_checklist_item').addClass('product_cats_checklist_item_hide');
				$.each( wcfm_product_type_categories, function( product_type_cat, allowed_categories ) {
					if( product_type == product_type_cat ) {
						$.each( allowed_categories, function( index, allowed_category ) {
							$('#product_cats_checklist').find('.checklist_item_'+allowed_category).removeClass('product_cats_checklist_item_hide');
							$('#product_cats_checklist').find('.checklist_item_'+allowed_category).find('.product_taxonomy_sub_checklist').find('.product_cats_checklist_item').removeClass('product_cats_checklist_item_hide');
							$has_cat = true;
						} );	
					}
				} );
				if( !$has_cat ) {
					$('#product_cats_checklist').find('.product_cats_checklist_item').removeClass('product_cats_checklist_item_hide');
				}
				$('.product_cats_checklist_item_hide_by_cap').attr( 'disabled', true );
			}
			
			$('#wcfm_products_manage_form .page_collapsible').addClass('wcfm_head_hide');
			$('#wcfm_products_manage_form .wcfm-container').addClass('wcfm_block_hide');
			$('.wcfm_ele').addClass('wcfm_ele_hide');
			
			$('.'+product_type).removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
			
			if( $.inArray( product_type, pro_types ) == -1 ) {
				$('.simple').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
				$('.non-'+product_type).addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
				product_type = 'simple';
			}
			
			if( product_type != 'simple' ) {
				$('#is_downloadable').attr( 'checked', false );
				//$('#is_virtual').attr( 'checked', false );
			}
			$('#is_downloadable').change();
			$('#is_catalog').change();
			$('#is_virtual').change();
			
			
			$( document.body ).trigger( 'wcfm_product_type_changed' );
			
			if($('.wcaddons').length > 0) { $('.wcaddons').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide'); }
			
			// Tabheight  
			collapsHeight = 0;
			$('.wcfm-tabWrap .page_collapsible').each(function() {
				if( !$(this).hasClass('wcfm_head_hide') ) {
					//console.log($(this).attr('id'));
					collapsHeight += $(this).height() + 21;
				}
			}); 
			setTimeout(function() {
				resetCollapsHeight($('.collapse-open').next('.wcfm-container').find('.wcfm_ele:not(.wcfm_title):first'));
			}, 600 );
		}).change();
		
		// Downloadable
		$('#is_downloadable').change(function() {
		  if($(this).is(':checked')) {
		  	$('.downlodable').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  	resetCollapsHeight($('.collapse-open').next('.wcfm-container').find('.wcfm_ele:not(.wcfm_title):first'));
		  } else {
		  	$('.downlodable').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  	if( $('#product_type').val() == 'variable' ) {
					$('.variable').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
				}
		  }
		}).change();
		$('.is_downloadable_hidden').change(function() {
		  if($(this).val() == 'enable') {
		  	$('.downlodable').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  	resetCollapsHeight($('.collapse-open').next('.wcfm-container').find('.wcfm_ele:not(.wcfm_title):first'));
		  } else {
		  	$('.downlodable').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  }
		}).change();
		if($('#is_downloadable').length == 0) $('.downlodable').addClass('downloadable_ele_hide');
		
		// Virtual
		$('#is_virtual').change(function() {
			if( !$(this).hasClass('wcfm_ele_hide') ) {
				if($(this).is(':checked')) {
					$('.nonvirtual').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
					$('.non-virtual').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
				} else {
					$('.nonvirtual').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
					$('.non-virtual').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
				}
			}
		}).change();
		$('.is_virtual_hidden').change(function() {
		  if($(this).val() == 'enable') {
		  	$('.nonvirtual').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  	$('.non-virtual').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  } else {
		  	$('.nonvirtual').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  	$('.non-virtual').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  }
		}).change();
		
		// Catalog
		$('#is_catalog').change(function() {
		  if($(this).is(':checked')) {
		  	$('.catalog_options').removeClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  	resetCollapsHeight($('.collapse-open').next('.wcfm-container').find('.wcfm_ele:not(.wcfm_title):first'));
		  } else {
		  	$('.catalog_options').addClass('wcfm_ele_hide wcfm_block_hide wcfm_head_hide');
		  }
		}).change();
	} else {
		$('#wcfm_products_manage_form .page_collapsible').addClass('wcfm_head_hide');
		$('#wcfm_products_manage_form .wcfm-container').addClass('wcfm_block_hide');
		$('.wcfm_ele').addClass('wcfm_ele_hide');
	}
	
	$('.variations').click(function() {
		if($(this).hasClass('collapse-open')) {
			resetVariationsAttributes();
		}
	});
	
	// Product Popup Variations Change
	$( document.body ).on( 'wcfm_product_popup_variations_option', function() {
		resetVariationsAttributes();
	});
	
	// WooCommerce Tiered Price Support
	if( $('#tiered_price_rules_type').length > 0 ) {
		$('#tiered_price_rules_type').change(function() {
			$('.tiered_price_rule_type').addClass('wcfm_custom_hide');
			$('.tiered_price_rule_type_'+$(this).val()).removeClass('wcfm_custom_hide');
		});
		$('.tiered_price_rule_type').addClass('wcfm_custom_hide');
		$('.tiered_price_rule_type_'+$('#tiered_price_rules_type').val()).removeClass('wcfm_custom_hide');
	}
	
	function addVariationManageStockProperty() {
		$('.variation_manage_stock_ele').each(function() {
			$(this).off('change').on('change', function() {
				if($(this).is(':checked')) {
					$(this).parent().find('.variation_non_manage_stock_ele').removeClass('non_stock_ele_hide');
					$(this).parent().find('.variation_stock_status_ele').addClass('non_stock_ele_hide');
					resetCollapsHeight($('#variations'));
				} else {
					$(this).parent().find('.variation_non_manage_stock_ele').addClass('non_stock_ele_hide');
					$(this).parent().find('.variation_stock_status_ele').removeClass('non_stock_ele_hide');
					resetCollapsHeight($('#variations'));
				}
			}).change();
		});
		
		$('.variation_is_virtual_ele').each(function() {
			$(this).off('change').on('change', function() {
				if($(this).is(':checked')) {
					$(this).parent().find('.variation_non_virtual_ele').addClass('non_virtual_ele_hide');
					resetCollapsHeight($('#variations'));
				} else {
					$(this).parent().find('.variation_non_virtual_ele').removeClass('non_virtual_ele_hide');
					resetCollapsHeight($('#variations'));
				}
			}).change();
		});
		
		$('.variation_is_downloadable_ele').each(function() {
			$(this).off('change').on('change', function() {
				if($(this).is(':checked')) {
					$(this).parent().find('.variation_downloadable_ele').removeClass('downloadable_ele_hide');
					$(this).parent().find('.variation_downloadable_ele').next('.upload_button').removeClass('downloadable_ele_hide');
					resetCollapsHeight($('#variations'));
				} else {
					$(this).parent().find('.variation_downloadable_ele').addClass('downloadable_ele_hide');
					$(this).parent().find('.variation_downloadable_ele').next('.upload_button').addClass('downloadable_ele_hide');
					resetCollapsHeight($('#variations'));
				}
			}).change();
		});
		
		$('.variation_tiered_price_rules_type').each(function() {
			$(this).off('change').on('change', function() {
				$(this).parent().find('.tiered_price_rule_type').addClass('wcfm_custom_hide');
				$(this).parent().find('.tiered_price_rule_type_'+$(this).val()).removeClass('wcfm_custom_hide');
				resetCollapsHeight($('#variations'));
			}).change();
		});
	}
	addVariationManageStockProperty();
	
	$('.manage_stock_ele').change(function() {
	  if($(this).is(':checked')) {
	  	$(this).parent().find('.non_manage_stock_ele').removeClass('non_stock_ele_hide wcfm_custom_hide');
	  	$(this).parent().find('.stock_status_ele').addClass('non_stock_ele_hide wcfm_custom_hide');
	  	resetCollapsHeight($('#manage_stock'));
	  } else {
	  	$(this).parent().find('.non_manage_stock_ele').addClass('non_stock_ele_hide wcfm_custom_hide');
	  	$(this).parent().find('.stock_status_ele').removeClass('non_stock_ele_hide wcfm_custom_hide');
	  }
	}).change();
	
	// On Page Load Manage Product Tab Container Height Set
	resetCollapsHeight($('.collapse-open').next('.wcfm-container').find('.wcfm_ele:not(.wcfm_title):first'));
	
	$('.sales_schedule').click( function() {
	  $('.sales_schedule_ele').toggleClass('sales_schedule_ele_show');
	} );
	
	if( $( "#sale_date_from" ).length > 0 ) {
		$( "#sale_date_from" ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			onClose: function( selectedDate ) {
				$( "#sale_date_upto" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		$( "#sale_date_upto" ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			onClose: function( selectedDate ) {
				$( "#sale_date_from" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
	}
	
	// WooCommerce Germanized Support
	if( $('#_unit_price_auto').length > 0 ) {
		if( $('#_unit_price_auto').is(':checked') ) {
			$('#_unit_price_regular').attr( 'readonly', true );
			$('#_unit_price_sale').attr( 'readonly', true );
		} else {
			$('#_unit_price_regular').removeAttr( 'readonly' );
			$('#_unit_price_sale').removeAttr( 'readonly' );
		}
		$('#_unit_price_auto').click(function() {
			if( $('#_unit_price_auto').is(':checked') ) {
				$('#_unit_price_regular').attr( 'readonly', true );
				$('#_unit_price_sale').attr( 'readonly', true );
			} else {
				$('#_unit_price_regular').removeAttr( 'readonly' );
				$('#_unit_price_sale').removeAttr( 'readonly' );
			}
		});
		$('#regular_price, #sale_price, #_unit_base').focusout( function() {
	    if( $('#_unit_price_auto').is(':checked') ) {
	    	$_unit_base = 1;
	    	$_unit_product = $_unit_base;
	    	$regular_price = parseFloat( $('#regular_price').val() );
	    	$sale_price = parseFloat( $('#sale_price').val() );
	    	
	    	if( $('#_unit_base').val() ) { $_unit_base = $('#_unit_base').val(); }
	    	if( $('#_unit_product').val() ) { $_unit_product = parseFloat( $( '#_unit_product' ).val().replace( ',', '.' ) ); } else { $_unit_product = parseFloat( $_unit_base ); $_unit_base = 1; }
	    	
	    	if( $_unit_base && $regular_price ) {
	    		$('#_unit_price_regular').val( wcfm_gzd_round_price( ( $regular_price / $_unit_product ) * $_unit_base ) );
	    	} else {
	    		$('#_unit_price_regular').val('');
	    	}
	    	if( $_unit_base && $sale_price ) {
	    		$('#_unit_price_sale').val( wcfm_gzd_round_price( ( $sale_price / $_unit_product ) * $_unit_base ) );
	    	} else {
	    		$('#_unit_price_sale').val('');
	    	}
	    }
		}).focusout();
	}
	
	function wcfm_gzd_round_price( price ) {
		var d = parseInt(2,10),
	    dx = Math.pow(10,d),
	    n = parseFloat(price),
	    f = Math.round(Math.round(n * dx * 10) / 10) / dx;

	    return f.toFixed(2);
	}
	
  $('.multi_input_holder').each(function() {
	  var multi_input_holder = $(this);
	  addMultiInputProperty(multi_input_holder);
	});
	
	function addMultiInputProperty(multi_input_holder) {
		var multi_input_limit = multi_input_holder.data('limit');
		if( typeof multi_input_limit == 'undefined' ) multi_input_limit = -1;
	  if(multi_input_holder.children('.multi_input_block').length == 1) multi_input_holder.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
	  if( multi_input_holder.children('.multi_input_block').length == multi_input_limit )  multi_input_holder.find('.add_multi_input_block').hide();
	  else multi_input_holder.find('.add_multi_input_block').show();
    multi_input_holder.children('.multi_input_block').each(function() {
      if($(this)[0] != multi_input_holder.children('.multi_input_block:last')[0]) {
        $(this).children('.add_multi_input_block').remove();
      }
      
      //$(this).children('.add_multi_input_block').addClass('img_tip');
			$(this).children('.add_multi_input_block').attr( 'title', wcfm_dashboard_messages.wcfm_multiblick_addnew_help );
			//$(this).children('.remove_multi_input_block').addClass('img_tip');
			$(this).children('.remove_multi_input_block').attr( 'title', wcfm_dashboard_messages.wcfm_multiblick_remove_help );
    });
    var multi_input_has_dummy = multi_input_holder.data('has-dummy');
    if( multi_input_has_dummy ) multi_input_holder.find('.add_multi_input_block').hide();
    
    multi_input_holder.children('.multi_input_block').children('.add_multi_input_block').off('click').on('click', function() {
      var holder_id = multi_input_holder.attr('id');
      var holder_name = multi_input_holder.data('name');
      var multi_input_blockCount = multi_input_holder.data('length');
      multi_input_blockCount++;
      var multi_input_blockEle = multi_input_holder.children('.multi_input_block:first').clone(false);
      
      multi_input_blockEle.find('textarea,input:not(input[type=button],input[type=submit],input[type=checkbox],input[type=radio])').val('');
      multi_input_blockEle.find('input[type=checkbox]').attr('checked', false);
      multi_input_blockEle.find('.select2-container').remove();
      multi_input_blockEle.find('select').select2();
      multi_input_blockEle.find('select').select2('destroy');
      multi_input_blockEle.removeClass('multi_input_block_dummy');
      multi_input_blockEle.children('.wcfm-wp-fields-uploader,.wp-picker-container,.multi_input_block_element:not(.multi_input_holder)').each(function() {
        var ele = $(this);
        var ele_name = ele.data('name');
        if(ele.hasClass('wcfm-wp-fields-uploader')) {
					var uploadEle = ele;
					ele_name = uploadEle.find('.multi_input_block_element').data('name');
					uploadEle.find('img').attr('src', uploadEle.find('img').data('placeholder')).attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_display').addClass('placeHolder');
					uploadEle.find('.multi_input_block_element').attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount).attr('name', holder_name+'['+multi_input_blockCount+']['+ele_name+']');
					uploadEle.find('.upload_button').attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_button').show();
					uploadEle.find('.remove_button').attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_remove_button').hide();
					if(uploadEle.hasClass('wcfm_gallery_upload')) {
						addWCFMMultiUploaderProperty(uploadEle);	
					} else {
						addWCFMUploaderProperty(uploadEle);	
					}
				} else if(ele.hasClass('wp-picker-container')) {
					$new_ele = ele.find('.multi_input_block_element');
					ele.replaceWith( $new_ele );
					ele_name = $new_ele.data('name');
					$new_ele.attr('name', holder_name+'['+multi_input_blockCount+']['+ele_name+']');
					$new_ele.attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount);
          $new_ele.removeClass('wp-color-picker').wpColorPicker();
				} else {
					ele.attr('name', holder_name+'['+multi_input_blockCount+']['+ele_name+']');
					ele.attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount);
        }
        
        if(ele.hasClass('wcfm_datepicker')) {
          ele.removeClass('hasDatepicker').datepicker({
            dateFormat: ele.data('date_format'),
            closeText: wcfm_datepicker_params.closeText,
						currentText: wcfm_datepicker_params.currentText,
						monthNames: wcfm_datepicker_params.monthNames,
						monthNamesShort: wcfm_datepicker_params.monthNamesShort,
						dayNames: wcfm_datepicker_params.dayNames,
						dayNamesShort: wcfm_datepicker_params.dayNamesShort,
						dayNamesMin: wcfm_datepicker_params.dayNamesMin,
						firstDay: wcfm_datepicker_params.firstDay,
						isRTL: wcfm_datepicker_params.isRTL,
            changeMonth: true,
            changeYear: true
          });
        } else if(ele.hasClass('time_picker')) {
          $('.time_picker').timepicker('remove').timepicker({ 'step': 15 });
          ele.timepicker('remove').timepicker({ 'step': 15 });
        }
      });
      
      // Nested multi-input block property
      multi_input_blockEle.children('.multi_input_holder').each(function() {
        setNestedMultiInputIndex($(this), holder_id, holder_name, multi_input_blockCount);
      });
       
      
      multi_input_blockEle.children('.remove_multi_input_block').off('click').on('click', function() {
      	var rconfirm = confirm(wcfm_dashboard_messages.multiblock_delete_confirm);
				if(rconfirm) {
					var remove_ele_parent = $(this).parent().parent();
					var addEle = remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').clone(true);
					$(this).parent().remove();
					remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').remove();
					remove_ele_parent.children('.multi_input_block:last').append(addEle);
					if( remove_ele_parent.children('.multi_input_block').length == multi_input_limit ) remove_ele_parent.find('.add_multi_input_block').hide();
					else remove_ele_parent.find('.add_multi_input_block').show();
					if( multi_input_has_dummy ) multi_input_holder.find('.add_multi_input_block').hide();
					if(remove_ele_parent.children('.multi_input_block').length == 1) remove_ele_parent.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
					if( !multi_input_holder.hasClass( 'wcfm_additional_variation_images' ) && !multi_input_holder.hasClass( 'wcfm_per_product_shipping_variation_fields' ) && !multi_input_holder.hasClass( 'wcfm_wcaddons_fields' ) )  resetCollapsHeight(multi_input_holder);
				}
			});
      
      multi_input_blockEle.children('.add_multi_input_block').remove();
      multi_input_holder.append(multi_input_blockEle);
      //initiateTip();
      multi_input_holder.children('.multi_input_block:last').find('.wcfm-select2').select2({ placeholder: wcfm_dashboard_messages.choose_select2 + ' ...' });
      multi_input_holder.children('.multi_input_block:last').append($(this));
      if(multi_input_holder.children('.multi_input_block').length > 1) multi_input_holder.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'block');
      if( multi_input_holder.children('.multi_input_block').length == multi_input_limit ) multi_input_holder.find('.add_multi_input_block').hide();
      else multi_input_holder.find('.add_multi_input_block').show();
      if( multi_input_has_dummy ) multi_input_holder.find('.add_multi_input_block').hide();
      multi_input_holder.data('length', multi_input_blockCount);
      
      addVariationManageStockProperty();
      
      if( !multi_input_holder.hasClass( 'wcfm_additional_variation_images' ) && !multi_input_holder.hasClass( 'wcfm_per_product_shipping_variation_fields' ) && !multi_input_holder.hasClass( 'wcfm_wcaddons_fields' ) )  resetCollapsHeight(multi_input_holder);
      else if( multi_input_holder.hasClass( 'wcfm_per_product_shipping_variation_fields' ) || multi_input_holder.hasClass( 'wcfm_wcaddons_fields' ) ) resetCollapsHeight( multi_input_holder.parent() );
    });
    
    if(!multi_input_holder.hasClass('multi_input_block_element')) {
			//multi_input_holder.children('.multi_input_block').css('padding-bottom', '40px');
		}
		if(multi_input_holder.children('.multi_input_block').children('.multi_input_holder').length > 0) {
			//multi_input_holder.children('.multi_input_block').css('padding-bottom', '40px');
		}
    
    multi_input_holder.children('.multi_input_block').children('.remove_multi_input_block').off('click').on('click', function() {
    	var rconfirm = confirm(wcfm_dashboard_messages.multiblock_delete_confirm);
			if(rconfirm) {
				var remove_ele_parent = $(this).parent().parent();
				var addEle = remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').clone(true);
				// For Attributes
				if( $(this).parent().find( $('input[data-name="is_taxonomy"]').data('name') == 1 ) ) {
					$taxonomy = $(this).parent().find( $('input[data-name="tax_name"]') ).val();
					$( 'select.wcfm_attribute_taxonomy' ).find( 'option[value="' + $taxonomy + '"]' ).removeAttr( 'disabled' );
				}
				$(this).parent().remove();
				remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').remove();
				remove_ele_parent.children('.multi_input_block:last').append(addEle);
				if(remove_ele_parent.children('.multi_input_block').length == 1) remove_ele_parent.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
				if( remove_ele_parent.children('.multi_input_block').length == multi_input_limit ) remove_ele_parent.find('.add_multi_input_block').hide();
				else remove_ele_parent.find('.add_multi_input_block').show();
				if( multi_input_has_dummy ) multi_input_holder.find('.add_multi_input_block').hide();
				
				if( !multi_input_holder.hasClass( 'wcfm_additional_variation_images' ) && !multi_input_holder.hasClass( 'wcfm_per_product_shipping_variation_fields' ) && !multi_input_holder.hasClass( 'wcfm_wcaddons_fields' ) ) resetCollapsHeight(multi_input_holder);
			}
    });
    
    // Gallary Image Sortable
    if( !wcfm_params.is_mobile ) {
			multi_input_holder.sortable({
				update: function( event, ui ) {
					resetMultiInputIndex(multi_input_holder);
				}
			}).disableSelection();
		}
  }
  
  function resetMultiInputIndex(multi_input_holder) {
  	var holder_id = multi_input_holder.attr('id');
		var holder_name = multi_input_holder.data('name');
		var multi_input_blockCount = 0;
		
		multi_input_holder.children('.multi_input_block').each(function() {
			$(this).children('.wcfm-wp-fields-uploader,.multi_input_block_element:not(.multi_input_holder)').each(function() {
				var ele = $(this);
				var ele_name = ele.data('name');
				if(ele.hasClass('wcfm-wp-fields-uploader')) {
					var uploadEle = ele;
					ele_name = uploadEle.find('.multi_input_block_element').data('name');
					uploadEle.find('img').attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_display');
					uploadEle.find('.multi_input_block_element').attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount).attr('name', holder_name+'['+multi_input_blockCount+']['+ele_name+']');
					uploadEle.find('.upload_button').attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_button');
					uploadEle.find('.remove_button').attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_remove_button');
				} else {
					var multiple = ele.attr('multiple');
					if (typeof multiple !== typeof undefined && multiple !== false) {
						ele.attr('name', holder_name+'['+multi_input_blockCount+']['+ele_name+'][]');
					} else {
						ele.attr('name', holder_name+'['+multi_input_blockCount+']['+ele_name+']');
					}
					ele.attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount);
				}
			});
			multi_input_blockCount++;
		});
  }
  
  function setNestedMultiInputIndex(nested_multi_input, holder_id, holder_name, multi_input_blockCount) {
		nested_multi_input.children('.multi_input_block:not(:last)').remove();
		var multi_input_id = nested_multi_input.attr('id');
		multi_input_id = multi_input_id.replace(holder_id + '_', '');
		var multi_input_id_splited = multi_input_id.split('_');
		var multi_input_name = '';
		for(var i = 0; i < (multi_input_id_splited.length -1); i++) {
		 if(multi_input_name != '') multi_input_name += '_';
		 multi_input_name += multi_input_id_splited[i];
		}
		nested_multi_input.attr('data-name', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']');
		nested_multi_input.attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount);
		var nested_multi_input_block_count = 0;
		nested_multi_input.children('.multi_input_block').children('.wcfm-wp-fields-uploader,.multi_input_block_element:not(.multi_input_holder)').each(function() {
		  var ele = $(this);
		  var ele_name = ele.data('name');
		  if(ele.hasClass('wcfm-wp-fields-uploader')) {
				var uploadEle = ele;
				ele_name = uploadEle.find('.multi_input_block_element').data('name');
				uploadEle.find('img').attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count + '_display');
				uploadEle.find('.multi_input_block_element').attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count).attr('name', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']['+nested_multi_input_block_count+']['+ele_name+']');
				uploadEle.find('.upload_button').attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count + '_button').attr('name', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count + '_button');
				uploadEle.find('.remove_button').attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count + '_remove_button').attr('name', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count + '_remove_button');
				if(uploadEle.hasClass('wcfm_gallery_upload')) {
					addWCFMMultiUploaderProperty(uploadEle);	
				} else {
					addWCFMUploaderProperty(uploadEle);	
				}
			} else {
				var multiple = ele.attr('multiple');
				if (typeof multiple !== typeof undefined && multiple !== false) {
					ele.attr('name', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']['+nested_multi_input_block_count+']['+ele_name+'][]');
				} else {
					ele.attr('name', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']['+nested_multi_input_block_count+']['+ele_name+']');
				}
				ele.attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count);
		  }
		  
		  if(ele.hasClass('wcfm_datepicker')) {
				ele.removeClass('hasDatepicker').datepicker({
					dateFormat: ele.data('date_format'),
					closeText: wcfm_datepicker_params.closeText,
					currentText: wcfm_datepicker_params.currentText,
					monthNames: wcfm_datepicker_params.monthNames,
					monthNamesShort: wcfm_datepicker_params.monthNamesShort,
					dayNames: wcfm_datepicker_params.dayNames,
					dayNamesShort: wcfm_datepicker_params.dayNamesShort,
					dayNamesMin: wcfm_datepicker_params.dayNamesMin,
					firstDay: wcfm_datepicker_params.firstDay,
					isRTL: wcfm_datepicker_params.isRTL,
					changeMonth: true,
					changeYear: true
				});
			} else if(ele.hasClass('time_picker')) {
				$('.time_picker').timepicker('remove').timepicker({ 'step': 15 });
				ele.timepicker('remove').timepicker({ 'step': 15 });
			}
			//nested_multi_input_block_count++;
		});
		
		addMultiInputProperty(nested_multi_input);
		
		if(nested_multi_input.children('.multi_input_block').children('.multi_input_holder').length > 0) nested_multi_input.children('.multi_input_block').css('padding-bottom', '40px');
		
		nested_multi_input.children('.multi_input_block').children('.multi_input_holder').each(function() {
			setNestedMultiInputIndex($(this), holder_id+'_'+multi_input_name+'_0', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']', 0);
			$(this).find('.multi_input_block_manupulate').each(function() {
				$(this).off('click').on('click', function() {
					resetCollapsHeight(nested_multi_input);
				} );
			} );
		});
	}
	
	// Add Taxonomy Attribute Rows.
	$( 'button.wcfm_add_attribute' ).on( 'click', function() {
		var attribute    = $( 'select.wcfm_attribute_taxonomy' ).val();
		
		if ( attribute ) {
			$('#attributes').children('.multi_input_block').children('.add_multi_input_block').click();
			$('#attributes').find('.remove_multi_input_block').remove();
			$('#attributes').find('.multi_input_block').each(function() {
				$(this).find('input[data-name="is_variation"]').off('change').on('change', function() {
					resetVariationsAttributes();
				});
			});
			resetMultiInputIndex($('#attributes'));
			initAttributesCollapser(false);
			$('.attributes_collapser').click();
			$('#attributes').children('.multi_input_block:last').find('input[data-name="is_active"]').click();
			$('#attributes').children('.multi_input_block:last').find('input[type="checkbox"]').attr( 'checked', true );
			$('#attributes').children('.multi_input_block:last').find('.attributes_collapser').click();
			$('#attributes').children('.multi_input_block:last').find('.attribute_ele').focus();
		}
		
		return false;
	});
	
	if($('.wcfm_category_attributes_mapping_msg').length > 0) {
		$('#attributes').append($('.wcfm_category_attributes_mapping_msg'));
		//$('.wcfm_category_attributes_mapping_msg').remove();
	}
	if($('.wcfm_select_attributes').length > 0) {
		$('.wcfm_select_attributes').each(function() {
			$('#attributes').append($(this).html());
			$(this).remove();
		});
		addMultiInputProperty($('#attributes'));
		resetMultiInputIndex($('#attributes'));
		initiateTip();
		$('#attributes').find('.remove_multi_input_block').remove();
	}
	
	if($('#text_attributes').length > 0) {
		$('#attributes').append($('#text_attributes').html());
		$('#text_attributes').remove();
		addMultiInputProperty($('#attributes'));
		resetMultiInputIndex($('#attributes'));
		initiateTip();
		$('#attributes').find('.remove_multi_input_block').remove();
	}
	
	$('#attributes').find('.multi_input_block').each(function() {
		$multi_input_block = $(this);
		$multi_input_block.prepend('<span class="fields_collapser attributes_collapser wcfmfa fa-arrow-circle-down" title="'+wcfm_dashboard_messages.wcfm_multiblick_collapse_help+'"></span>');
	  if( $multi_input_block.find( $('input[data-name="is_taxonomy"]').data('name') == 1 ) ) {
	  	$taxonomy = $multi_input_block.find( 'input[data-name="tax_name"]' ).val();
	  	$( 'select.wcfm_attribute_taxonomy' ).find( 'option[value="' + $taxonomy + '"]' ).attr( 'disabled','disabled' );
	  }
	  $multi_input_block.find('input[data-name="is_variation"]').off('change').on('change', function() {
	    resetVariationsAttributes();
	  });
	  $multi_input_block.find('input[data-name="is_active"]').off('change').on('change', function() {
	  	if( $(this).is(':checked') ) {
	      $(this).parent().find('.wcfm_ele:not(.attribute_ele), .select2, .wcfm_add_attribute_term').removeClass('variation_ele_hide');
				$(this).parent().find('input[type="checkbox"]').attr( 'checked', true ).removeClass('collapsed_checkbox');
				//$(this).parent().find('.wcfm_select_all_attributes').click();
				$(this).parent().find('.attributes_collapser').addClass('fa-arrow-circle-up');
	  	} else {
	  		$(this).parent().find('.wcfm_ele:not(.attribute_ele), .select2, .wcfm_add_attribute_term').addClass('variation_ele_hide');
				$(this).parent().find('input[type="checkbox"]').attr( 'checked', false ).addClass('collapsed_checkbox');
				$(this).parent().find('.wcfm_select_no_attributes').click();
				$(this).parent().find('.attributes_collapser').removeClass('fa-arrow-circle-up');
			}
			resetCollapsHeight($('#attributes'));
	  });
	  if( $multi_input_block.find('select').length > 0 ) {
	  	$attrlimit = $multi_input_block.find('select').data('attrlimit');
	  	if( $attrlimit != 1 ) {
	  		$multi_input_block.find('select').after($('<div class="wcfm-clearfix"></div>'));
				$multi_input_block.find('select').after($('<button type="button" class="button wcfm_add_attribute_term wcfm_select_all_attributes">'+wcfm_dashboard_messages.select_all+'</button>'));
				$multi_input_block.find('select').after($('<button type="button" class="button wcfm_add_attribute_term wcfm_select_no_attributes">'+wcfm_dashboard_messages.select_none+'</button>'));
				if( $multi_input_block.find('select').hasClass('allow_add_term') ) {
					$multi_input_block.find('select').after($('<button type="button" class="button wcfm_add_attribute_term wcfm_add_attributes_new_term">'+wcfm_dashboard_messages.add_new+'</button>'));
				}
				$multi_input_block.find('select').after($('<div class="wcfm-clearfix"></div>'));
			}
			$multi_input_block.find('select').each(function() {
				$(this).select2({
					placeholder: wcfm_dashboard_messages.search_attribute_select2,
					maximumSelectionLength: $attrlimit
				});
			});
		}
	});
	
	// Attributes Collapser
	function initAttributesCollapser($newClass) {
		$('#attributes').children('.multi_input_block').children('.attributes_collapser').each(function() {
			if($newClass) { $(this).addClass('fa-arrow-circle-up'); }
			$(this).off('click').on('click', function() {
				$(this).parent().find('.wcfm_ele:not(.attribute_ele), .select2, .wcfm_add_attribute_term').toggleClass('variation_ele_hide');
				$(this).parent().find('input[type="checkbox"]').toggleClass('collapsed_checkbox');
				$(this).toggleClass('fa-arrow-circle-up');
				resetCollapsHeight($('#attributes'));
			} ).click();
		} );
	}
	initAttributesCollapser(true);
	
	$('.wcfm_select_all_attributes').each(function() {
		$(this).on('click', function() {
		  $( this ).parent().find( 'select option' ).attr( 'selected', 'selected' );
		  $( this ).parent().find( 'select' ).change();
		});
	});
	
	$('.wcfm_select_no_attributes').each(function() {
		$(this).on('click', function() {
		  $( this ).parent().find( 'select option' ).removeAttr( 'selected' );
		  $( this ).parent().find( 'select' ).change();
		});
	});
	
	function resetVariationsAttributes() {
		$('#wcfm_products_manage_form_variations_empty_expander').removeClass('wcfm_custom_hide');
		$('#wcfm_products_manage_form_variations_expander').addClass('wcfm_custom_hide');
		$('#variations').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfm_generate_variation_attributes',
			wcfm_products_manage_form : $('#wcfm_products_manage_form').serialize()
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if(response) {
					var select_html = '';
					$.each($.parseJSON(response), function(attr_name, attr_value) {
						// Default Attributes
						var default_select_html = '<select name="default_attributes[attribute_'+attr_name.toLowerCase()+']" class="wcfm-select wcfm_ele wcfm_half_ele default_attribute_ele attribute_ele attribute_ele_new variable" data-name="default_attribute_'+attr_name.toLowerCase()+'"><option value="">' + wcfm_dashboard_messages.any_attribute + ' ' + attr_value.name + ' ..</option>';
						$.each(attr_value.data, function(k, attr_val) {
							default_select_html += '<option value="'+k+'">'+attr_val+'</option>';
						});
						default_select_html += '</select>';
						$('.default_attributes_holder').each(function() {
							if($(this).find('select[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').length > 0) {
								$attr_selected_val = $(this).find('select[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').val();
								$(this).find('select[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').replaceWith($(default_select_html));
								$(this).find('select[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').val($attr_selected_val);
							} else if($(this).find('input[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').length > 0) {
								$attr_selected_val = $(this).find('input[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').val();
								$(this).find('input[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').replaceWith($(default_select_html));
								$(this).find('select[data-name="default_attribute_'+attr_name.toLowerCase()+'"]').val($attr_selected_val);
							} else {
								$(this).append(default_select_html);
							}
						});
						
						// Variation Attributes
						select_html = '<select name="attribute_'+attr_name.toLowerCase()+'" class="wcfm-select wcfm_ele wcfm_half_ele attribute_ele attribute_ele_new variable multi_input_block_element" data-name="attribute_'+attr_name.toLowerCase()+'"><option value="">' + wcfm_dashboard_messages.any_attribute + ' ' + attr_value.name + ' ..</option>';
						$.each(attr_value.data, function(k, attr_val) {
							select_html += '<option value="'+k+'">'+attr_val+'</option>';
						});
						select_html += '</select>';
						$('#variations').children('.multi_input_block').each(function() {
							if($(this).find('select[data-name="attribute_'+attr_name.toLowerCase()+'"]').length > 0) {
								$attr_selected_val = $(this).find('select[data-name="attribute_'+attr_name.toLowerCase()+'"]').val();
								$(this).find('select[data-name="attribute_'+attr_name.toLowerCase()+'"]').replaceWith($(select_html));
								$(this).find('select[data-name="attribute_'+attr_name.toLowerCase()+'"]').val($attr_selected_val);
							} else if($(this).find('input[data-name="attribute_'+attr_name.toLowerCase()+'"]').length > 0) {
								$attr_selected_val = $(this).find('input[data-name="attribute_'+attr_name.toLowerCase()+'"]').val();
								$(this).find('input[data-name="attribute_'+attr_name.toLowerCase()+'"]').replaceWith($(select_html));
								$(this).find('select[data-name="attribute_'+attr_name.toLowerCase()+'"]').val($attr_selected_val);
							} else {
								$(this).prepend(select_html);
							}
						});
					});
					$('.default_attribute_ele').change();
					$('.attribute_ele_old').remove();
					$('.attribute_ele_new').addClass('attribute_ele_old').removeClass('attribute_ele_new');
					resetMultiInputIndex($('#variations'));
					if( select_html.length > 0 ) {
						$('#wcfm_products_manage_form_variations_empty_expander').addClass('wcfm_custom_hide');
						$('#wcfm_products_manage_form_variations_expander').removeClass('wcfm_custom_hide');
					}
				}
				$('#variations').unblock();
				if( ( ( $('#product_type').val() == 'variable' ) || ( $('#product_type').val() == 'variable-subcription' ) ) && $('.variations').hasClass('collapse-open') ) {
					resetCollapsHeight($('#variations'));
				}
			},
			dataType: 'html'
		});	
	}
	resetVariationsAttributes();
	
	// Creating Default attributes
	$default_attributes = $('input[data-name="default_attributes_hidden"]');
	if($default_attributes.length > 0) {
		$default_attributes_val = $default_attributes.val();
		if($default_attributes_val.length > 0) {
			$.each($.parseJSON($default_attributes_val), function(attr_key, attr_val) {
				attr_val = attr_val.replace( '"', '&quot;' );
				attr_val = attr_val.replace( "'", '&#039;' );
				$('.default_attributes_holder').append('<input type="hidden" name="default_attribute_'+attr_key+'" data-name="default_attribute_'+attr_key+'" value="'+attr_val+'" />');
			});
		}
	}
	
	// Creating Variation attributes
	$('#variations').children('.multi_input_block').each(function() {
		$multi_input_block = $(this);
		$multi_input_block.prepend('<div class="wcfm_clearfix"></div>');
		$multi_input_block.prepend('<span class="fields_collapser variations_collapser wcfmfa fa-arrow-circle-down" title="'+wcfm_dashboard_messages.wcfm_multiblick_collapse_help+'"></span>');
	  $attributes = $multi_input_block.find('input[data-name="attributes"]');
	  $attributes_val = $attributes.val();
	  if($attributes_val.length > 0) {
	  	$.each($.parseJSON($attributes_val), function(attr_key, attr_val) {
	  		attr_val = attr_val.replace( '"', '&quot;' );
	  		attr_val = attr_val.replace( "'", '&#039;' );
	  		$multi_input_block.prepend('<input type="hidden" name="'+attr_key+'" data-name="'+attr_key+'" value="'+attr_val+'" />');
	  	});
	  }
	  
	  $multi_input_block.find( ".var_sale_date_from" ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			onClose: function( selectedDate ) {
				$multi_input_block.find( ".var_sale_date_upto" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		$multi_input_block.find( ".var_sale_date_upto" ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			onClose: function( selectedDate ) {
				$multi_input_block.find( ".var_sale_date_from" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
		$multi_input_block.find('.var_sales_schedule').click( function() {
			$(this).parent().find('.var_sales_schedule_ele').toggleClass('var_sales_schedule_ele_show');
		} );
	});
	
	// Variations Collapser
	$('#variations').children('.multi_input_block').children('.add_multi_input_block').click(function() {
	  $('#variations').children('.multi_input_block').children('.variations_collapser').each(function() {
			$(this).off('click').on('click', function() {
				$(this).parent().find('.wcfm_ele:not(.attribute_ele), .wcfm_title').toggleClass('variation_ele_hide');
				$(this).toggleClass('fa-arrow-circle-up');
				resetCollapsHeight($('#variations'));
			} );
			$(this).parent().find('.wcfm_ele:not(.attribute_ele), .wcfm_title').addClass('variation_ele_hide');
			$(this).removeClass('fa-arrow-circle-up');
			resetCollapsHeight($('#variations'));
		} );
		$('#variations').children('.multi_input_block:last').children('.variations_collapser').click();
		$('#variations').children('.multi_input_block:last').find('input[type="checkbox"]:first').attr( 'checked', true );
		$('#variations').children('.multi_input_block:last').find('.var_sales_schedule_ele').removeClass('var_sales_schedule_ele_show');
		$('#variations').children('.multi_input_block:last').find('.var_sales_schedule').click( function() {
			$(this).parent().find('.var_sales_schedule_ele').toggleClass('var_sales_schedule_ele_show');
		} );
		$('#variations').children('.multi_input_block:last').find( ".var_sale_date_from" ).removeClass('hasDatepicker').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			onClose: function( selectedDate ) {
				$('#variations').children('.multi_input_block:last').find( ".var_sale_date_upto" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		$('#variations').children('.multi_input_block:last').find( ".var_sale_date_upto" ).removeClass('hasDatepicker').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			onClose: function( selectedDate ) {
				$('#variations').children('.multi_input_block:last').find( ".var_sale_date_from" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
	});
	$('#variations').children('.multi_input_block').children('.variations_collapser').each(function() {
		$(this).addClass('fa-arrow-circle-up');
		$(this).off('click').on('click', function() {
			$(this).parent().find('.wcfm_ele:not(.attribute_ele), .wcfm_title').toggleClass('variation_ele_hide');
			$(this).toggleClass('fa-arrow-circle-up');
			resetCollapsHeight($('#variations'));
		} );
		
		$pro_id = $('#pro_id').val();
		if( $pro_id && ( $pro_id != 0 ) ) {
			$(this).click();
		}
	} );
	
	// Track Deleting Variation
	$('#variations').children('.multi_input_block').children('.remove_multi_input_block').click(function() {
	  removed_variations.push($(this).parent().find('.variation_id').val());
	});
	
	// Variation Bulk Options
	$('#variations_options').change(function() {
		$variations_option = $(this).val();
		if( $variations_option ) {
			switch( $variations_option ) {
				case 'on_enabled':
					$('#variations').find('input[data-name="enable"]').each(function() {
						$(this).attr( "checked", true );
					});
					break;
					
				case 'off_enabled':
					$('#variations').find('input[data-name="enable"]').each(function() {
						$(this).attr( "checked", false );
					});
					break;
					
				case 'on_downloadable':
					$('#variations').find('input[data-name="is_downloadable"]').each(function() {
						$(this).attr( "checked", true ).change();
					});
					break;
					
				case 'off_downloadable':
					$('#variations').find('input[data-name="is_downloadable"]').each(function() {
						$(this).attr( "checked", false ).change();
					});
					break;
					
				case 'on_virtual':
					$('#variations').find('input[data-name="is_virtual"]').each(function() {
						$(this).attr( "checked", true ).change();
					});
					break;
					
				case 'off_virtual':
					$('#variations').find('input[data-name="is_virtual"]').each(function() {
						$(this).attr( "checked", false ).change();
					});
					break;
					
				case 'on_manage_stock':
					$('#variations').find('input[data-name="manage_stock"]').each(function() {
						$(this).attr( "checked", true ).change();
					});
					break;
					
				case 'off_manage_stock':
					$('#variations').find('input[data-name="manage_stock"]').each(function() {
						$(this).attr( "checked", false ).change();
					});
					break;
					
				case 'variable_stock':
			  	var stock_qty = prompt( wcfm_products_manage_messages.set_stock  );
			  	if( stock_qty != null ) {
			  		$('#variations').find('input[data-name="stock_qty"]').each(function() {
			  			if( !isNaN(parseFloat(stock_qty)) ) {
								$(this).val(parseFloat(stock_qty));
							}
			  		});
			  	}
			  	break;
			  	
			 case 'variable_increase_stock':
			  	var stock_qty = prompt( wcfm_products_manage_messages.increase_stock  );
			  	if( stock_qty != null ) {
			  		$('#variations').find('input[data-name="stock_qty"]').each(function() {
			  			if( !isNaN(parseFloat(stock_qty)) ) {
			  				if( $(this).val().length > 0 ) {
									$(this).val(parseFloat($(this).val()) + parseFloat(stock_qty));
								} else {
									$(this).val(parseFloat(stock_qty));
								}
							}
			  		});
			  	}
			  	break;
			  	
			  case 'variable_stock_status_instock':
					$('#variations').find('select[data-name="stock_status"]').each(function() {
						$(this).val('instock');
					});
			  	break;
			  	
			  case 'variable_stock_status_outofstock':
			  	$('#variations').find('select[data-name="stock_status"]').each(function() {
						$(this).val('outofstock');
					});
			  	break;
			  	
			  case 'variable_stock_status_onbackorder':
			  	$('#variations').find('select[data-name="stock_status"]').each(function() {
						$(this).val('onbackorder');
					});
			  	break;
					
			  case 'set_regular_price':
			  	var regular_price = prompt( wcfm_products_manage_messages.regular_price  );
			  	if( regular_price != null ) {
			  		$('#variations').find('input[data-name="regular_price"]').each(function() {
			  			if( !isNaN(parseFloat(regular_price)) ) {
			  				$(this).val(parseFloat(regular_price));
			  			}
			  		});
			  	}
			  	break;
			  	
			  case 'regular_price_increase':
			  	var regular_price = prompt( wcfm_products_manage_messages.regular_price_increase  );
			  	if( regular_price != null ) {
			  		$('#variations').find('input[data-name="regular_price"]').each(function() {
			  			if( !isNaN(parseFloat(regular_price)) ) {
								if( $(this).val().length > 0 ) {
									$(this).val(parseFloat($(this).val()) + parseFloat(regular_price));
								} else {
									$(this).val(parseFloat(regular_price));
								}
							}
			  		});
			  	}
			  	break;
			  	
			  case 'regular_price_decrease':
			  	var regular_price = prompt( wcfm_products_manage_messages.regular_price_decrease );
			  	if( regular_price != null ) {
			  		$('#variations').find('input[data-name="regular_price"]').each(function() {
			  			if( !isNaN(parseFloat(regular_price)) ) {
								if( $(this).val().length > 0 ) {
									$(this).val(parseFloat($(this).val()) - parseFloat(regular_price));
								} else {
									$(this).val(parseFloat(regular_price));
								}
							}
			  		});
			  	}
			  	break;
			  	
			  case 'set_sale_price':
			  	var sale_price = prompt( wcfm_products_manage_messages.sales_price );
			  	if( sale_price != null ) {
			  		$('#variations').find('input[data-name="sale_price"]').each(function() {
			  		  if( !isNaN(parseFloat(sale_price)) ) {
			  				$(this).val(parseFloat(sale_price));
			  			}
			  		});
			  	}
			  	break;
			  	
			  case 'sale_price_increase':
			  	var sale_price = prompt( wcfm_products_manage_messages.sales_price_increase );
			  	if( sale_price != null ) {
			  		$('#variations').find('input[data-name="sale_price"]').each(function() {
			  			if( !isNaN(parseFloat(sale_price)) ) {
								if( $(this).val().length > 0 ) {
									$(this).val(parseFloat($(this).val()) + parseFloat(sale_price));
								} else {
									$(this).val(parseFloat(sale_price));
								}
							}
			  		});
			  	}
			  	break;
			  	
			  case 'sale_price_decrease':
			  	var sale_price = prompt( wcfm_products_manage_messages.sales_price_decrease );
			  	if( sale_price != null ) {
			  		$('#variations').find('input[data-name="sale_price"]').each(function() {
			  			if( !isNaN(parseFloat(sale_price)) ) {
								if( $(this).val().length > 0 ) {
									$(this).val(parseFloat($(this).val()) - parseFloat(sale_price));
								} else {
									$(this).val(parseFloat(sale_price));
								}
							}
			  		});
			  	}
			  	break;
			  	
			  case 'set_length':
			  	var length = prompt( wcfm_products_manage_messages.length );
			  	if( length != null ) {
			  		$('#variations').find('input[data-name="length"]').each(function() {
			  		  if( !isNaN(parseFloat(length)) ) {
			  				$(this).val(parseFloat(length));
			  			}
			  		});
			  	}
			  	break;
			  	
			  case 'set_width':
			  	var width = prompt( wcfm_products_manage_messages.width );
			  	if( width != null ) {
			  		$('#variations').find('input[data-name="width"]').each(function() {
			  		  if( !isNaN(parseFloat(width)) ) {
			  				$(this).val(parseFloat(width));
			  			}
			  		});
			  	}
			  	break;
			  	
			  case 'set_height':
			  	var height = prompt( wcfm_products_manage_messages.height );
			  	if( height != null ) {
			  		$('#variations').find('input[data-name="height"]').each(function() {
			  		  if( !isNaN(parseFloat(height)) ) {
			  				$(this).val(parseFloat(height));
			  			}
			  		});
			  	}
			  	break;
			  	
			  case 'set_weight':
			  	var weight = prompt( wcfm_products_manage_messages.weight );
			  	if( weight != null ) {
			  		$('#variations').find('input[data-name="weight"]').each(function() {
			  		  if( !isNaN(parseFloat(weight)) ) {
			  				$(this).val(parseFloat(weight));
			  			}
			  		});
			  	}
			  	break;
			  	
			  case 'variable_download_limit':
			  	var download_limit = prompt( wcfm_products_manage_messages.download_limit );
			  	if( download_limit != null ) {
			  		$('#variations').find('input[data-name="download_limit"]').each(function() {
			  			if( !isNaN(parseFloat(download_limit)) ) {
								$(this).val(parseFloat(download_limit));
							}
			  		});
			  	}
			  	break;
			  	
			  case 'variable_download_expiry':
			  	var download_expiry = prompt( wcfm_products_manage_messages.download_expiry );
			  	if( download_expiry != null ) {
			  		$('#variations').find('input[data-name="download_expiry"]').each(function() {
			  			if( !isNaN(parseFloat(download_expiry)) ) {
								$(this).val(parseFloat(download_expiry));
							}
			  		});
			  	}
			  	break;
			  	
			  case 'variation_auto_generate':
			  	var rconfirm = confirm(wcfm_dashboard_messages.variation_auto_generate_confirm);
			  	if(rconfirm) {
						product_variation_auto_generate = 'yes';
						$('#wcfm_products_simple_draft_button').click();
					}
			  	break
			}
			$(this).val('');
		}
	});
	
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
		
				$wrapper.block({
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
								$( '.product_taxonomy_checklist_'+$taxonomy ).prepend( response );
								$wrapper.find('.wcfm_new_tax_ele').val('');
								$wrapper.find('.wcfm_new_parent_taxt_ele').val(0);
							}
							$wrapper.toggleClass('wcfm_add_new_taxonomy_form_hide');
							$wrapper.unblock();
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
					tax    : 'product_tag'
				}	
				$.post(wcfm_params.ajax_url, data, function(response) {
					if(response) {
						$('.wcfm_fetch_tag_cloud').html(response);
						$wcfm_tag_cloud_fetched = true;
						
						$('.tag-cloud-link').each(function() {
						  $(this).click(function(event) {
						  	event.preventDefault();
						  	$tag = $(this).text();
						  	$tags = $('#product_tags').val();
						  	if( $tags.length > 0 ) {
						  		$tags += ',' + $tag;
						  	} else {
						  		$tags = $tag;
						  	}
						  	$('#product_tags').val($tags);
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
	
	// WCFM Custom field Editor support
	$('.wcfm_custom_field_editor').each(function() {
		$wcfm_custom_field_editor = $(this);
		if( $wcfm_custom_field_editor.hasClass('rich_editor') ) {
			if( typeof tinymce != 'undefined' ) {
				var wcfm_custom_field_TinyMCE = tinymce.init({
																				selector: '#'+$wcfm_custom_field_editor.attr('id'),
																				height: 150,
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
	});
	
	function wcfm_products_manage_form_validate( $is_publish ) {
		product_form_is_valid = true;
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		var title = $.trim($('#wcfm_products_manage_form').find('#pro_title').val());
		$('#wcfm_products_manage_form').find('#pro_title').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');
		if(title.length == 0) {
			$('#wcfm_products_manage_form').find('#pro_title').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
			product_form_is_valid = false;
			$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_products_manage_messages.no_title).addClass('wcfm-error').slideDown();
			wcfm_notification_sound.play();
		}
		
		// WCFM Custom field Editor support
		if( $('.wcfm_custom_field_editor').length > 0 ) {
			$('.wcfm_custom_field_editor').each(function() {
				$wcfm_custom_field_editor = $(this);
				if( $wcfm_custom_field_editor.hasClass('rich_editor') || $wcfm_custom_field_editor.hasClass('wcfm_wpeditor') ) {
					$('#'+$wcfm_custom_field_editor.attr('id')).val(getWCFMEditorContent( $wcfm_custom_field_editor.attr('id') ));
				}
			});
		}
		
		if( $is_publish ) {
			$( document.body ).trigger( 'wcfm_products_manage_form_validate', $('#wcfm_products_manage_form') );
			
			$wcfm_is_valid_form = product_form_is_valid;
			$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_products_manage_form') );
			product_form_is_valid = $wcfm_is_valid_form;
		}
		
		return product_form_is_valid;
	}
	
	// Draft Product
	$('#wcfm_products_simple_draft_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  var excerpt = getWCFMEditorContent( 'excerpt' );
		
		var description = getWCFMEditorContent( 'description' );
		
		// WC Box Office Support
		var ticket_content = getWCFMEditorContent( '_ticket_content' );
		
		var ticket_email_html = getWCFMEditorContent( '_ticket_email_html' );
	  
	  // Validations
	  $is_valid = wcfm_products_manage_form_validate( false );
	  
	  if($is_valid) {
			$('#wcfm_products_manage_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var data = {
				action : 'wcfm_ajax_controller',
				controller : 'wcfm-products-manage', 
				wcfm_products_manage_form : $('#wcfm_products_manage_form').serialize(),
				excerpt     : excerpt,
				description : description,
				status : 'draft',
				removed_variations : removed_variations,
				removed_person_types : removed_person_types,
				ticket_content : ticket_content,
				ticket_email_html : ticket_email_html,
				product_manage_from_popup : product_manage_from_popup,
				variation_auto_generate : product_variation_auto_generate
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
							if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#pro_id').val($response_json.id);
					wcfmMessageHide();
					$('#wcfm_products_manage_form').unblock();
					$('.wcfm_submit_button').show();
				}
			});	
		} else {
			$('.wcfm_submit_button').show();
		}
	});
	
	// Submit Product
	$('#wcfm_products_simple_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  var excerpt = getWCFMEditorContent( 'excerpt' );
		
		var description = getWCFMEditorContent( 'description' );
		
		// WC Box Office Support
		var ticket_content = getWCFMEditorContent( '_ticket_content' );
		
		var ticket_email_html = getWCFMEditorContent( '_ticket_email_html' );
	  
	  // Validations
	  $is_valid = wcfm_products_manage_form_validate( true );
	  
	  if($is_valid) {
			$('#wcfm_products_manage_form').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var data = {
				action : 'wcfm_ajax_controller',
				controller : 'wcfm-products-manage',
				wcfm_products_manage_form : $('#wcfm_products_manage_form').serialize(),
				excerpt     : excerpt,
				description : description,
				status : 'submit',
				removed_variations : removed_variations,
				removed_person_types : removed_person_types,
				ticket_content : ticket_content,
				ticket_email_html : ticket_email_html,
				product_manage_from_popup : product_manage_from_popup,
				variation_auto_generate : product_variation_auto_generate
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#pro_id').val($response_json.id);
					$('#wcfm_products_manage_form').unblock();
					$('.wcfm_submit_button').show();
				}
			});
		} else {
			$('.wcfm_submit_button').show();
		}
	});
	
	// Reject Product
	$('#wcfm_products_simple_reject_button').click(function(event) {
	  event.preventDefault();
	  
	  $('.wcfm_submit_button').hide();
	  
	  var excerpt = getWCFMEditorContent( 'excerpt' );
		
		var description = getWCFMEditorContent( 'description' );
		
		// WC Box Office Support
		var ticket_content = getWCFMEditorContent( '_ticket_content' );
		
		var ticket_email_html = getWCFMEditorContent( '_ticket_email_html' );
	  
	  // Validations
	  $is_valid = wcfm_products_manage_form_validate( false );
	  
	  if($is_valid) {
	  	var reject_reason = prompt(wcfm_dashboard_messages.product_reject_confirm);
			if(reject_reason) {
	  	
				$('#wcfm_products_manage_form').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
				
				var data = {
					action : 'wcfm_ajax_controller',
					controller : 'wcfm-products-manage', 
					wcfm_products_manage_form : $('#wcfm_products_manage_form').serialize(),
					excerpt     : excerpt,
					description : description,
					status : 'draft',
					removed_variations : removed_variations,
					removed_person_types : removed_person_types,
					ticket_content : ticket_content,
					ticket_email_html : ticket_email_html,
					product_manage_from_popup : product_manage_from_popup,
					variation_auto_generate : product_variation_auto_generate,
					reject_reason           : reject_reason
				}	
				$.post(wcfm_params.ajax_url, data, function(response) {
					if(response) {
						$response_json = $.parseJSON(response);
						$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
						wcfm_notification_sound.play();
						if($response_json.status) {
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
								if( $response_json.redirect ) window.location = $response_json.redirect;	
							} );
						} else {
							$('#wcfm_products_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
						}
						if($response_json.id) $('#pro_id').val($response_json.id);
						wcfmMessageHide();
						$('#wcfm_products_manage_form').unblock();
						$('.wcfm_submit_button').show();
					}
				});	
			}
		} else {
			$('.wcfm_submit_button').show();
		}
	});
	
	// Single product multi-seller support - 3.3.7
	if( wcfm_auto_product_suggest.allow ) {
		$pro_id = $('#pro_id').val();
		if( $pro_id == 0 ) {
			$('#wcfm-main-contentainer #pro_title').after('<div id="wcfm_auto_suggest_product_title"></div>');		
			$('#wcfm-main-contentainer #pro_title').on( 'keyup focus', function(e) {
				var strtitle = $(this).val();
				if( strtitle.length >= 3 ) {
					var data = {
						action : 'wcfm_auto_search_product',
						protitle : strtitle				
					}
					$.post(wcfm_params.ajax_url, data, function(response) {
						if( response ) {
							$('#wcfm_auto_suggest_product_title').html(response).addClass('wcfm_auto_suggest_product_title_show');
							$('.wcfm_product_multi_seller_associate').each(function() {
								$(this).click(function(event) {
									event.preventDefault();
									$('#wcfm-content').block({
										message: null,
										overlayCSS: {
											background: '#fff',
											opacity: 0.6
										}
									});
									var data = {
										action : 'wcfm_product_multi_seller_associate',
										proid : $(this).data('proid')
									}	
									jQuery.ajax({
										type:		'POST',
										url: wcfm_params.ajax_url,
										data: data,
										success:	function(response) {
											if(response) {
												$response_json = $.parseJSON(response);
												if($response_json.status) {
													if( $response_json.redirect ) window.location = $response_json.redirect;	
												}
											}
										}
									});
									return false;
								});
							});
						} else {
							$('#wcfm_auto_suggest_product_title').html('').removeClass('wcfm_auto_suggest_product_title_show');
						}
					});
				} else if(strtitle.length == 0) {
					$('#wcfm_auto_suggest_product_title').html('').removeClass('wcfm_auto_suggest_product_title_show');
				}
			});
			$('body').click( function( evt ) {    
        if( evt.target.id != "wcfm_auto_suggest_product_title") {
				  $('#wcfm_auto_suggest_product_title').removeClass('wcfm_auto_suggest_product_title_show');
				}
			});
		}
	}
	
	function jsUcfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }
} );