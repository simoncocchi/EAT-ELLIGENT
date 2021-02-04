<?php
global $wp, $WCFM, $wc_product_attributes;

$product_id = 0;
$product = array();
$product_type = apply_filters( 'wcfm_default_product_type', '' );
$is_virtual = '';
$title = '';
$sku = '';
$visibility = 'visible';
$excerpt = '';
$description = '';
$regular_price = '';
$sale_price = '';
$sale_date_from = '';
$sale_date_upto = '';
$product_url = '';
$button_text = '';
$is_downloadable = '';
$downloadable_files = array();
$download_limit = '';
$download_expiry = '';
$children = array();

$featured_img = '';
$gallery_img_ids = array();
$gallery_img_urls = array();
$categories = array();
$product_tags = '';
$manage_stock = '';
$stock_qty = 0;
$backorders = '';
$stock_status = ''; 
$sold_individually = '';
$weight = '';
$length = '';
$width = '';
$height = '';
$shipping_class = '';
$tax_status = '';
$tax_class = '';
$attributes = array();
$default_attributes = '';
$attributes_select_type = array();
$variations = array();

$upsell_ids = array();
$crosssell_ids = array();

$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );

// Shipping Class List
$product_shipping_class = get_terms( 'product_shipping_class', array('hide_empty' => 0));
$product_shipping_class = apply_filters( 'wcfm_product_shipping_class', $product_shipping_class );
$variation_shipping_option_array = array('-1' => __('Same as parent', 'wc-frontend-manager'));
$shipping_option_array = array('_no_shipping_class' => __('No shipping class', 'wc-frontend-manager'));
if( $product_shipping_class && !empty( $product_shipping_class ) ) {
	foreach($product_shipping_class as $product_shipping) {
		$variation_shipping_option_array[$product_shipping->term_id] = $product_shipping->name;
		$shipping_option_array[$product_shipping->term_id] = $product_shipping->name;
	}
}

// Tax Class List
$tax_classes         = WC_Tax::get_tax_classes();
$classes_options     = array();
$variation_tax_classes_options['parent'] = __( 'Same as parent', 'wc-frontend-manager' );
$variation_tax_classes_options[''] = __( 'Standard', 'wc-frontend-manager' );
$tax_classes_options[''] = __( 'Standard', 'wc-frontend-manager' );

if ( ! empty( $tax_classes ) ) {

	foreach ( $tax_classes as $class ) {
		$tax_classes_options[ sanitize_title( $class ) ] = esc_html( $class );
		$variation_tax_classes_options[ sanitize_title( $class ) ] = esc_html( $class );
	}
}

$products_array = array();
if( !empty( $upsell_ids ) ) {
	foreach( $upsell_ids as $upsell_id ) {
		$products_array[$upsell_id] = get_the_title( $upsell_id );
	}
}
if( !empty( $crosssell_ids ) ) {
	foreach( $crosssell_ids as $crosssell_id ) {
		$products_array[$crosssell_id] = get_the_title( $crosssell_id );
	}
}


$product_types = apply_filters( 'wcfm_product_types', array('simple' => __('Simple Product', 'wc-frontend-manager'), 'variable' => __('Variable Product', 'wc-frontend-manager'), 'grouped' => __('Grouped Product', 'wc-frontend-manager'), 'external' => __('External/Affiliate Product', 'wc-frontend-manager') ) );
$product_categories    = get_terms( 'product_cat', 'orderby=name&hide_empty=0&parent=0' );
$product_defined_tags  = get_terms( 'product_tag', 'orderby=name&hide_empty=0&parent=0' );
$catlimit = apply_filters( 'wcfm_catlimit', -1 );

$product_type_class = '';
if( count( $product_types ) == 0 ) {
	$product_types = array('simple' => __('Simple Product', 'wc-frontend-manager') );
	$product_type_class = 'wcfm_custom_hide';
} elseif( count( $product_types ) == 1 ) {
	$product_type_class = 'wcfm_custom_hide';
}

$wcfm_is_translated_product = false;
$wcfm_wpml_edit_disable_element = '';
?>

<div class="collapse wcfm-collapse" id="wcfm_product_popup_container">
	<div class="wcfm-collapse-content">
		<?php do_action( 'before_wcfm_product_simple' ); ?>

		<form id="wcfm_products_manage_form" class="wcfm">
		
			<?php do_action( 'begin_wcfm_products_manage_form' ); ?>
			
			<!-- collapsible -->
			<div class="page_collapsible products_manage_general <?php echo apply_filters( 'wcfm_pm_block_class_general', 'simple variable external grouped booking' ); ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_general', '' ); ?>" id="wcfm_products_manage_form_general_head"><label class="wcfmfa fa-cube"></label><?php _e('Add Product', 'wc-frontend-manager'); ?><span></span></div>
			<div class="wcfm-container simple variable external grouped booking">
				<div id="wcfm_products_manage_form_general_expander" class="wcfm-content">
				  <div class="wcfm_product_manager_general_fields">
				    <?php do_action( 'wcfm_product_manager_left_panel_before', $product_id ); ?>
				    
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_general', array(
																																																"product_type" => array('type' => 'select', 'options' => $product_types, 'class' => 'wcfm-select wcfm_ele wcfm_product_type wcfm_full_ele simple variable external grouped booking ' . $product_type_class, 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $product_type ),
																																																"is_virtual" => array('desc' => __('Virtual', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple booking non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card', 'desc_class' => 'wcfm_title wcfm_ele virtual_ele_title checkbox_title simple booking non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card', 'value' => 'enable', 'dfvalue' => $is_virtual),
																																																"is_downloadable" => array('desc' => __('Downloadable', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple booking appointment non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card', 'desc_class' => 'wcfm_title wcfm_ele downloadable_ele_title checkbox_title simple appointment booking non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card', 'value' => 'enable', 'dfvalue' => $is_downloadable),
																																																"pro_title" => array( 'placeholder' => __('Product Title', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_full_ele simple variable external grouped booking', 'value' => $title),
																																																//"visibility"     => array('label' => __('Visibility', 'wc-frontend-manager'), 'type' => 'select', 'options' => array('visible' => __('Catalog/Search', 'wc-frontend-manager'), 'catalog' => __('Catalog', 'wc-frontend-manager'), 'search' => __('Search', 'wc-frontend-manager'), 'hidden' => __('Hidden', 'wc-frontend-manager')), 'class' => 'wcfm-select wcfm_ele wcfm_half_ele wcfm_half_ele_right simple variable external', 'label_class' => 'wcfm_ele wcfm_half_ele_title wcfm_title simple variable external', 'value' => $visibility, 'hints' => __('Choose where this product should be displayed in your catalog. The product will always be accessible directly.', 'wc-frontend-manager'))
																																													), $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ) );
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_pricing', array(
																																																"product_url" => array('label' => __('URL', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele external', 'label_class' => 'wcfm_ele wcfm_title external', 'value' => $product_url, 'hints' => __( 'Enter the external URL to the product.', 'wc-frontend-manager' )),
																																																"button_text" => array('label' => __('Button Text', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele external', 'label_class' => 'wcfm_ele wcfm_title external', 'value' => $button_text, 'hints' => __( 'This text will be shown on the button linking to the external product.', 'wc-frontend-manager' )),
																																																"regular_price" => array('label' => __('Price', 'wc-frontend-manager') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card', 'label_class' => 'wcfm_ele wcfm_title simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card', 'value' => $regular_price, 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ) ),
																																																"sale_price" => array('label' => __('Sale Price', 'wc-frontend-manager') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card', 'label_class' => 'wcfm_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card', 'value' => $sale_price, 'desc_class' => 'wcfm_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery sales_schedule non-pw-gift-card', 'desc' => __( 'schedule', 'wc-frontend-manager' ), 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ) ),
																																																"sale_date_from" => array('label' => __('From', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __('From', 'wc-frontend-manager') . '... YYYY-MM-DD', 'custom-attributes' => array( 'date_format' => 'yy-mm-dd' ), 'class' => 'wcfm-text wcfm_ele sales_schedule_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele sales_schedule_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'value' => $sale_date_from),
																																																"sale_date_upto" => array('label' => __('Upto', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __('To', 'wc-frontend-manager') . '... YYYY-MM-DD', 'custom-attributes' => array( 'date_format' => 'yy-mm-dd' ), 'class' => 'wcfm-text wcfm_ele sales_schedule_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele sales_schedule_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'value' => $sale_date_upto),
																																													), $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ) );	
							
						?>
						
						<?php do_action( 'after_wcfm_products_manage_pricing_fields', $product_id ); ?>
						
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
			</div>
				
			<?php if( apply_filters( 'wcfm_is_allow_category', true ) || apply_filters( 'wcfm_is_allow_tags', true ) ) { ?>
				<div class="page_collapsible products_manage_taxonomy <?php echo apply_filters( 'wcfm_pm_block_class_taxonomy', 'simple variable external grouped booking' ); ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_taxonomy', '' ); ?>" id="wcfm_products_manage_form_taxonomy_head"><label class="wcfmfa fa-tags"></label><?php _e('Taxonomies', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container simple variable external grouped booking <?php echo apply_filters( 'wcfm_pm_block_custom_class_taxonomy', '' ); ?>">
					<div id="wcfm_products_manage_form_taxonomy_expander" class="wcfm-content">
						<div class="wcfm_product_manager_taxonomy_fields">
							<?php do_action( 'before_wcfm_products_manage_taxonomies', $product_id ); ?>
							<?php if( apply_filters( 'wcfm_is_allow_category', true ) && apply_filters( 'wcfm_is_allow_pm_category', true ) ) { ?>
								<?php if( apply_filters( 'wcfm_is_allow_product_category', true ) ) { $ptax_custom_arrtibutes = apply_filters( 'wcfm_taxonomy_custom_attributes', array(), 'product_cat' ); ?>
									<p class="wcfm_title"><strong><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( 'Categories', 'wc-frontend-manager' ), 'product_cat' ); ?></strong></p><label class="screen-reader-text" for="product_cats"><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( 'Categories', 'wc-frontend-manager' ), 'product_cat' ); ?></label>
									<select id="product_cats" name="product_cats[]" class="wcfm-select wcfm_ele simple variable external grouped booking" multiple="multiple" data-catlimit="<?php echo $catlimit; ?>" <?php echo implode( ' ', $ptax_custom_arrtibutes ); ?> style="width: 100%; margin-bottom: 10px;">
										<?php
											if ( $product_categories ) {
												$WCFM->library->generateTaxonomyHTML( 'product_cat', $product_categories, $categories );
											}
										?>
									</select>
								<?php } ?>
							
								<?php
								if( apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
									if( !empty( $product_taxonomies ) ) {
										foreach( $product_taxonomies as $product_taxonomy ) {
											if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) && apply_filters( 'wcfm_is_allow_product_taxonomy', true, $product_taxonomy->name ) && apply_filters( 'wcfm_is_allow_taxonomy_'.$product_taxonomy->name, true ) ) {
												if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
													if( apply_filters( 'wcfm_is_allow_custom_taxonomy_'.$product_taxonomy->name, true ) ) {
														// Fetching Saved Values
														$taxonomy_values_arr = array();
														if($product && !empty($product)) {
															$taxonomy_values = get_the_terms( $product_id, $product_taxonomy->name );
															if( !empty($taxonomy_values) ) {
																foreach($taxonomy_values as $pkey => $ptaxonomy) {
																	$taxonomy_values_arr[] = $ptaxonomy->term_id;
																}
															}
														}
														$ptax_custom_arrtibutes = apply_filters( 'wcfm_taxonomy_custom_attributes', array(), $product_taxonomy->name );
														$taxonomy_limit = apply_filters( 'wcfm_taxonomy_limit', -1, $product_taxonomy->name );
														?>
														<p class="wcfm_title"><strong><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ); ?></strong></p><label class="screen-reader-text" for="<?php echo $product_taxonomy->name; ?>"><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ); ?></label>
														<select id="<?php echo $product_taxonomy->name; ?>" name="product_custom_taxonomies[<?php echo $product_taxonomy->name; ?>][]" class="wcfm-select product_taxonomies wcfm_ele simple variable external grouped booking" multiple="multiple" data-catlimit="<?php echo $taxonomy_limit; ?>" <?php echo implode( ' ', $ptax_custom_arrtibutes ); ?> style="width: 100%; margin-bottom: 10px;">
															<?php
																$product_taxonomy_terms   = get_terms( $product_taxonomy->name, 'orderby=name&hide_empty=0&parent=0' );
																if ( $product_taxonomy_terms ) {
																	$WCFM->library->generateTaxonomyHTML( $product_taxonomy->name, $product_taxonomy_terms, $taxonomy_values_arr );
																}
															?>
														</select>
														<?php
													}
												}
											}
										}
									}
								}
							}
							
							if( apply_filters( 'wcfm_is_allow_tags', true ) ) {
								if( apply_filters( 'wcfm_is_tags_input', true ) ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_simple_fields_tag', array(  "product_tags" => array('label' => __('Tags', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'value' => $product_tags, 'hints' => __( 'Product tags are descriptive labels you can add to your products. Popular search engines can use tags to get information about your store. You can add more than one tag separating them with a comma.', 'wc-frontend-manager' ), 'placeholder' => __('Separate Product Tags with commas', 'wc-frontend-manager') )
																																														), $product_id, $product_type ) );
								} else {
									$product_all_tags = array();
									foreach( $product_defined_tags as $product_defined_tag) {
										$product_all_tags[$product_defined_tag->term_id] = $product_defined_tag->name;
									}
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_simple_fields_tag', array(  "product_tags" => array('label' => __('Tags', 'wc-frontend-manager') , 'type' => 'select', 'class' => 'wcfm-select wcfm_ele product_tags_as_dropdown simple variable external grouped booking', 'label_class' => 'wcfm_title', 'value' => $product_tags, 'options' => $product_all_tags, 'attributes' => array( 'multiple' => true ), 'hints' => __( 'Product tags are descriptive labels you can add to your products. Popular search engines can use tags to get information about your store. You can add more than one tag separating them with a comma.', 'wc-frontend-manager' ) )
																																															), $product_id, $product_type ) );
								}
								
								
								if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
									if( !empty( $product_taxonomies ) ) {
										foreach( $product_taxonomies as $product_taxonomy ) {
											if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) && apply_filters( 'wcfm_is_allow_product_taxonomy', true, $product_taxonomy->name ) && apply_filters( 'wcfm_is_allow_taxonomy_'.$product_taxonomy->name, true ) ) {
												if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && !$product_taxonomy->hierarchical ) {
													if( apply_filters( 'wcfm_is_allow_custom_taxonomy_'.$product_taxonomy->name, true ) ) {
														// Fetching Saved Values
														$taxonomy_values_arr = wp_get_post_terms($product_id, $product_taxonomy->name, array("fields" => "names"));
														$taxonomy_values = implode(',', $taxonomy_values_arr);
														$WCFM->wcfm_fields->wcfm_generate_form_field( array(  $product_taxonomy->name => array( 'label' => $product_taxonomy->label, 'name' => 'product_custom_taxonomies_flat[' . $product_taxonomy->name . '][]', 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $taxonomy_values, 'placeholder' => __('Separate Product ' . $product_taxonomy->label . ' with commas', 'wc-frontend-manager') ) ) );
													}
												}
											}
										}
									}
								}
							}
							?>
							
							<?php do_action( 'after_wcfm_products_manage_taxonomies', $product_id ); ?>
							
							<?php do_action( 'wcfm_product_manager_right_panel_after', $product_id ); ?>
							
							<div class="wcfm_clearfix"></div>
						</div>
					</div>
				</div>
			<?php } ?>
						
			<?php if( apply_filters( 'wcfm_is_allow_featured', true ) ) { ?>
				<div class="page_collapsible products_manage_gallery <?php echo apply_filters( 'wcfm_pm_block_class_gallery', 'simple variable external grouped booking' ); ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_gallery', '' ); ?>" id="wcfm_products_manage_form_gallery_head"><label class="wcfmfa fa-image"></label><?php _e('Image Gallery', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container simple variable external grouped booking <?php echo apply_filters( 'wcfm_pm_block_custom_class_gallery', '' ); ?>">
					<div id="wcfm_products_manage_form_gallery_expander" class="wcfm-content">
						<div class="wcfm_product_manager_gallery_fields">
							<?php
							$gallerylimit = apply_filters( 'wcfm_gallerylimit', -1 );
							if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
								//$gallerylimit = apply_filters( 'wcfm_free_gallerylimit', 4 );
							}
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_images', array(  "featured_img" => array( 'type' => 'upload', 'class' => 'wcfm-product-feature-upload wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'prwidth' => 250, 'value' => $featured_img),
																																																												"gallery_img"  => array( 'type' => 'multiinput', 'class' => 'wcfm-text wcfm-gallery_image_upload wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'custom_attributes' => array( 'limit' => $gallerylimit ), 'value' => $gallery_img_urls, 'options' => array(
																																																																									"gimage" => array( 'type' => 'upload', 'class' => 'wcfm_gallery_upload', 'prwidth' => 75),
																																																																								) )
																																													), $gallery_img_urls ) );
							?>
					
							<?php do_action( 'wcfm_product_manager_gallery_fields_end', $product_id ); ?>
							
							<div class="wcfm_clearfix"></div>
						</div>
					</div>
				</div>
			<?php } ?>
				
			<div class="page_collapsible products_manage_content <?php echo apply_filters( 'wcfm_pm_block_class_content', 'simple variable external grouped booking' ); ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_content', '' ); ?>" id="wcfm_products_manage_form_content_head"><label class="wcfmfa fa-font"></label><?php _e('Content', 'wc-frontend-manager'); ?><span></span></div>
			<div class="wcfm-container simple variable external grouped booking <?php echo apply_filters( 'wcfm_pm_block_custom_class_content', '' ); ?>">
				<div id="wcfm_products_manage_form_content_expander" class="wcfm-content">
					<div class="wcfm_product_manager_content_fields">
						<?php
						$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
						$wpeditor = apply_filters( 'wcfm_is_allow_product_wpeditor', 'wpeditor' );
						if( $wpeditor && $rich_editor ) {
							$rich_editor = 'wcfm_wpeditor';
						} else {
							$wpeditor = 'textarea';
						}
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_content', array(
																																																	"excerpt" => array('label' => __('Short Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking ' . $rich_editor , 'label_class' => 'wcfm_title wcfm_full_ele', 'tinymce' => false, 'value' => $excerpt),
																																																	"description" => array('label' => __('Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele', 'tinymce' => false, 'value' => $description),
																																																	"pro_id" => array('type' => 'hidden', 'value' => $product_id)
																																													), $product_id, $product_type ) );
						?>
						
						<?php do_action( 'wcfm_product_manager_left_panel_after', $product_id ); ?>
					</div>
				</div>
			</div>
			
			<?php do_action( 'after_wcfm_products_manage_general', $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
		
			<?php add_filter( 'wcfm_is_allow_wpeditor_view_mode', '__return_false' ); ?>
			<?php include( $WCFM->library->views_path . 'products-manager/wcfm-view-products-manage-tabs.php' ); ?>
			
			<?php do_action( 'end_wcfm_products_manage', $product_id, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
			
			<?php do_action( 'after_wcfm_products_manage_tabs_content', $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
			
			<div id="wcfm_products_simple_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
			  <?php if( $product_id && ( $wcfm_products_single->post_status == 'publish' ) ) { ?>
				  <input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_live_products', true ) ) { _e( 'Submit', 'wc-frontend-manager' ); } else { _e( 'Submit for Review', 'wc-frontend-manager' ); } ?>" id="wcfm_products_simple_submit_button" class="wcfm_submit_button" />
				<?php } else { ?>
					<input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_products', true ) ) { _e( 'Submit', 'wc-frontend-manager' ); } else { _e( 'Submit for Review', 'wc-frontend-manager' ); } ?>" id="wcfm_products_simple_submit_button" class="wcfm_submit_button" />
				<?php } ?>
				<?php if( apply_filters( 'wcfm_is_allow_draft_published_products', true ) && apply_filters( 'wcfm_is_allow_add_products', true ) ) { ?>
				  <input type="submit" name="draft-data" value="<?php _e( 'Draft', 'wc-frontend-manager' ); ?>" id="wcfm_products_simple_draft_button" class="wcfm_submit_button" />
				<?php } ?>
				
				<?php
				if( $product_id && ( $wcfm_products_single->post_status != 'publish' ) ) {
					echo '<a target="_blank" href="' . apply_filters( 'wcfm_product_preview_url', get_permalink( $wcfm_products_single->ID ) ) . '">';
					?>
					<input type="button" class="wcfm_submit_button" value="<?php _e( 'Preview', 'wc-frontend-manager' ); ?>" />
					<?php
					echo '</a>';
				} elseif( $product_id && ( $wcfm_products_single->post_status == 'publish' ) ) {
					echo '<a target="_blank" href="' . apply_filters( 'wcfm_product_preview_url', get_permalink( $wcfm_products_single->ID ) ) . '">';
					?>
					<input type="button" class="wcfm_submit_button" value="<?php _e( 'View', 'wc-frontend-manager' ); ?>" />
					<?php
					echo '</a>';
				}
				?>
			</div>
			<input type="hidden" name="wcfm_nonce" value="<?php echo wp_create_nonce( 'wcfm_products_manage' ); ?>" />
		</form>
		<?php
		do_action( 'after_wcfm_products_manage' );
		?>
	</div>
</div>