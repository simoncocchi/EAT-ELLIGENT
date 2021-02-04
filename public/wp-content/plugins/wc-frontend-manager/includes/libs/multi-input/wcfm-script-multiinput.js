jQuery(document).ready(function($) {
	$('.multi_input_holder').each(function() {
	  var multi_input_holder = $(this);
	  addMultiInputProperty(multi_input_holder);
	});
	initiateTip();
	
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
      $(this).children('.wcfm_multiblock_sortable').remove();
      if( multi_input_limit != 1 ) {
      	if( !multi_input_holder.hasClass( 'wcfm_non_sortable' ) && !wcfm_params.is_mobile ) {
      		$(this).prepend('<span class="wcfmfa fa-arrows-alt wcfm_multiblock_sortable" title="'+wcfm_dashboard_messages.wcfm_multiblick_sortable_help+'"></span><div class="wcfm_clearfix"></div>');
      	}
      }
    });
    
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
            dateFormat : ele.data('date_format'),
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
					if(remove_ele_parent.children('.multi_input_block').length == 1) remove_ele_parent.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
				}
			});
      
      multi_input_blockEle.children('.add_multi_input_block').remove();
      multi_input_holder.append(multi_input_blockEle);
      initiateTip();
      multi_input_holder.children('.multi_input_block:last').find('.wcfm-select2').select2({
																																														placeholder: wcfm_dashboard_messages.choose_select2 + ' ...'
																																													});
      multi_input_holder.children('.multi_input_block:last').append($(this));
      if(multi_input_holder.children('.multi_input_block').length > 1) multi_input_holder.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'block');
      if( multi_input_holder.children('.multi_input_block').length == multi_input_limit ) multi_input_holder.find('.add_multi_input_block').hide();
      else multi_input_holder.find('.add_multi_input_block').show();
      multi_input_holder.data('length', multi_input_blockCount);
      
      // Fields Type Property
			multi_input_holder.find('.field_type_options').each(function() {
				$(this).off('change').on('change', function() {
					$(this).parent().find('.field_type_select_options').hide();
					$(this).parent().find('.field_type_html_options').hide();
					if( $(this).val() == 'select' ) $(this).parent().find('.field_type_select_options').show();
					else if( $(this).val() == 'mselect' ) $(this).parent().find('.field_type_select_options').show();
					else if( $(this).val() == 'dropdown' ) $(this).parent().find('.field_type_select_options').show();
					else if( $(this).val() == 'html' ) $(this).parent().find('.field_type_html_options').show();
				} ).change();
			} );
			
			// Group Name
			multi_input_holder.find('.custom_field_is_group').each( function() {
				$(this).change( function() {
					if( $(this).is(':checked') ) {
						$(this).parent().find('.custom_field_is_group_name').css('visibility', 'visible');
					} else {
						$(this).parent().find('.custom_field_is_group_name').css('visibility', 'hidden');
					}
				} ).change();
			} );
			
			// Fields Collaper
			multi_input_holder.find('.fields_collapser').each(function() {
				$(this).off('click').on('click', function() {
				  $(this).parent().parent().parent().find('.multi_input_holder:not(.wcfm_menu_manager_wrapper)').toggleClass('wcfm_ele_hide');
				  $(this).toggleClass('fa-arrow-circle-up');
				  resetCollapsHeight(multi_input_holder);
				} );
			} );
			
			if( $('.wcfm-tabWrap').length > 0 ) {
				if( multi_input_holder.hasClass('multi_input_block_element') ) {
					resetCollapsHeight(multi_input_holder.parent().parent());
				} else {
					if( multi_input_holder.parent().hasClass('store_address') ) {
						resetCollapsHeight(multi_input_holder.parent());
					} else {
						resetCollapsHeight(multi_input_holder);
					}
				}
			}
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
				$(this).parent().remove();
				remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').remove();
				remove_ele_parent.children('.multi_input_block:last').append(addEle);
				if(remove_ele_parent.children('.multi_input_block').length == 1) remove_ele_parent.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
				if( remove_ele_parent.children('.multi_input_block').length == multi_input_limit ) remove_ele_parent.find('.add_multi_input_block').hide();
				else remove_ele_parent.find('.add_multi_input_block').show();
			}
    });
    
    // Fields Type Property
		multi_input_holder.find('.field_type_options').each(function() {
			$(this).off('change').on('change', function() {
				$(this).parent().find('.field_type_select_options').hide();
				$(this).parent().find('.field_type_html_options').hide();
				if( $(this).val() == 'select' ) $(this).parent().find('.field_type_select_options').show();
				else if( $(this).val() == 'mselect' ) $(this).parent().find('.field_type_select_options').show();
				else if( $(this).val() == 'dropdown' ) $(this).parent().find('.field_type_select_options').show();
				else if( $(this).val() == 'html' ) $(this).parent().find('.field_type_html_options').show();
			} ).change();
		} );
		
		// Group Name
		multi_input_holder.find('.custom_field_is_group').each( function() {
			$(this).change( function() {
				if( $(this).is(':checked') ) {
					$(this).parent().find('.custom_field_is_group_name').css('visibility', 'visible');
				} else {
					$(this).parent().find('.custom_field_is_group_name').css('visibility', 'hidden');
				}
			} ).change();
		} );
		
		// Sortable
		if( multi_input_limit != 1 ) {
      if( !multi_input_holder.hasClass( 'wcfm_non_sortable' ) && !wcfm_params.is_mobile ) {
				multi_input_holder.sortable({
					update: function( event, ui ) {
						resetMultiInputIndex(multi_input_holder);
					}
				}).disableSelection();
			}
		}
  }
  
  // Fields Collapser
	$('.wcfm_title').find('.fields_collapser').each(function() {
		$(this).addClass('fa-arrow-circle-up');
		$(this).off('click').on('click', function() {
			$(this).parent().parent().parent().find('.multi_input_holder:not(.wcfm_menu_manager_wrapper)').toggleClass('wcfm_ele_hide');
			$(this).toggleClass('fa-arrow-circle-up');
			resetCollapsHeight($(this).parent().parent().parent().parent().parent().find('.multi_input_holder'));
		} ).click();
	} );
  
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
			$(this).children('.multi_input_holder').each(function() {
				setNestedMultiInputIndex($(this), holder_id, holder_name, multi_input_blockCount);
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
					dateFormat : ele.data('date_format'),
					changeMonth: true,
					changeYear: true
				});
			} else if(ele.hasClass('time_picker')) {
				$('.time_picker').timepicker('remove').timepicker({ 'step': 15 });
				ele.timepicker('remove').timepicker({ 'step': 15 });
			} else if(ele.hasClass('colorpicker')) {
				ele.removeClass('wp-color-picker').iris();
			}
			//nested_multi_input_block_count++;
		});
		
		addMultiInputProperty(nested_multi_input);
		
		if(nested_multi_input.children('.multi_input_block').children('.multi_input_holder').length > 0) nested_multi_input.children('.multi_input_block').css('padding-bottom', '40px');
		
		nested_multi_input.children('.multi_input_block').children('.multi_input_holder').each(function() {
			setNestedMultiInputIndex($(this), holder_id+'_'+multi_input_name+'_0', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']', 0);
		});
	}
});