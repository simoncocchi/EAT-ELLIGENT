<?php
/**
 * WCFM plugin view
 *
 * WCFM Products Manage Tabs view
 * This template can be overridden by copying it to yourtheme/wcfm/products-manager/
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/products-manager
 * @version   1.0.0
 */
 
global $wp, $WCFM, $wc_product_attributes;

?>
				<?php 
				$wcfm_pm_block_class_stock = apply_filters( 'wcfm_pm_block_class_stock', 'simple variable grouped external non-job_package non-resume_package non-auction non-groupbuy non-accommodation-booking' );
				if( !apply_filters( 'wcfm_is_allow_inventory', true ) || !apply_filters( 'wcfm_is_allow_pm_inventory', true ) ) { 
					$wcfm_pm_block_class_stock = 'wcfm_block_hide';
				}
				?>
				<!-- collapsible 1 -->
				<div class="page_collapsible products_manage_inventory <?php echo $wcfm_pm_block_class_stock . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_stock', '' ); ?>" id="wcfm_products_manage_form_inventory_head"><label class="wcfmfa fa-database"></label><?php _e('Inventory', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container <?php echo $wcfm_pm_block_class_stock . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_stock', '' ); ?>">
					<div id="wcfm_products_manage_form_inventory_expander" class="wcfm-content">
					  <?php do_action( 'wcfm_products_manage_inventory_start', $product_id, $product_type ); ?>
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_fields_stock', array(
																																																		"sku" => array('label' => __('SKU', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'value' => $sku, 'hints' => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'wc-frontend-manager' )),
																																																		"manage_stock" => array('label' => __('Manage Stock?', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele simple variable manage_stock_ele non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking non-pw-gift-card', 'value' => 'enable', 'label_class' => 'wcfm_title wcfm_ele checkbox_title simple variable non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking non-pw-gift-card', 'hints' => __('Enable stock management at product level', 'wc-frontend-manager'), 'dfvalue' => $manage_stock),
																																																		"stock_qty" => array('label' => __('Stock Qty', 'wc-frontend-manager') , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple variable non_manage_stock_ele non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking non-accommodation-booking', 'label_class' => 'wcfm_title wcfm_ele simple variable non_manage_stock_ele non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'value' => $stock_qty, 'hints' => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'wc-frontend-manager' ), 'attributes' => array( 'min' => '1', 'step'=> '1' ) ),
																																																		"backorders" => array('label' => __('Allow Backorders?', 'wc-frontend-manager') , 'type' => 'select', 'options' => array('no' => __('Do not Allow', 'wc-frontend-manager'), 'notify' => __('Allow, but notify customer', 'wc-frontend-manager'), 'yes' => __('Allow', 'wc-frontend-manager')), 'class' => 'wcfm-select wcfm_ele simple variable non_manage_stock_ele non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'label_class' => 'wcfm_title wcfm_ele simple variable non_manage_stock_ele non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'value' => $backorders, 'hints' => __( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'wc-frontend-manager' )),
																																																		"stock_status" => array('label' => __('Stock status', 'wc-frontend-manager') , 'type' => 'select', 'options' => array('instock' => __('In stock', 'wc-frontend-manager'), 'outofstock' => __('Out of stock', 'wc-frontend-manager'), 'onbackorder' => __( 'On backorder', 'wc-frontend-manager' ) ), 'class' => 'wcfm-select wcfm_ele stock_status_ele simple variable grouped non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title stock_status_ele simple variable grouped non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'value' => $stock_status, 'hints' => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'wc-frontend-manager' )),
																																																		"sold_individually" => array('label' => __('Sold Individually', 'wc-frontend-manager') , 'type' => 'checkbox', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele simple variable non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'hints' => __('Enable this to only allow one of this item to be bought in a single order', 'wc-frontend-manager'), 'label_class' => 'wcfm_title wcfm_ele simple variable checkbox_title non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'dfvalue' => $sold_individually)
																																														), $product_id, $product_type ) );
						?>
						<?php do_action( 'wcfm_products_manage_inventory_end', $product_id, $product_type ); ?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
					
				<?php do_action( 'after_wcfm_products_manage_stock', $product_id, $product_type ); ?>
				
				<?php 
				$wcfm_pm_block_class_downlodable = apply_filters( 'wcfm_pm_block_class_downlodable', 'simple downlodable non-variable-subscription non-redq_rental non-appointment' );
				if( !apply_filters( 'wcfmu_is_allow_downloadable', true ) || !apply_filters( 'wcfmu_is_allow_pm_downloadable', true ) ) { 
					$wcfm_pm_block_class_downlodable = 'wcfm_block_hide';
				}
				?>
				<!-- collapsible 2 -->
				<div class="page_collapsible products_manage_downloadable <?php echo $wcfm_pm_block_class_downlodable . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_downlodable', '' ); ?>" id="wcfm_products_manage_form_downloadable_head"><label class="wcfmfa fa-cloud-download-alt"></label><?php _e('Downloadable', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container <?php echo $wcfm_pm_block_class_downlodable . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_downlodable', '' ); ?>">
					<div id="wcfm_products_manage_form_downloadable_expander" class="wcfm-content">
					  <?php do_action( 'wcfm_products_manage_downloadable_start', $product_id, $product_type ); ?>
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_fields_downloadable', array(  
																																																"downloadable_files" => array('label' => __('Files', 'wc-frontend-manager') , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele simple downlodable', 'label_class' => 'wcfm_title', 'value' => $downloadable_files, 'options' => array(
																																																												"name" => array('label' => __('Name', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple downlodable', 'label_class' => 'wcfm_ele wcfm_title simple downlodable', 'custom_attributes' => array( 'required' => 1 ) ),
																																																												"file" => array('label' => __('File', 'wc-frontend-manager'), 'type' => 'upload', 'mime' => 'Uploads', 'button_class' => 'downloadable_product', 'class' => 'wcfm-text wcfm_ele simple downlodable downlodable_file', 'label_class' => 'wcfm_ele wcfm_title simple downlodable', 'custom_attributes' => array( 'required' => 1 ) ),
																																																												"previous_hash" => array( 'type' => 'hidden', 'name' => 'id' )
																																																												)
																																																	),
																																														"download_limit" => array('label' => __('Download Limit', 'wc-frontend-manager'), 'type' => 'number', 'value' => $download_limit, 'placeholder' => __('Unlimited', 'wc-frontend-manager'), 'class' => 'wcfm-text wcfm_ele simple external', 'label_class' => 'wcfm_ele wcfm_title simple downlodable', 'attributes' => array( 'min' => '0', 'step' => '1' )),
																																														"download_expiry" => array('label' => __('Download Expiry', 'wc-frontend-manager'), 'type' => 'number', 'value' => $download_expiry, 'placeholder' => __('Never', 'wc-frontend-manager'), 'class' => 'wcfm-text wcfm_ele simple external', 'label_class' => 'wcfm_ele wcfm_title simple downlodable', 'attributes' => array( 'min' => '0', 'step' => '1' ))
																																									), $product_id, $product_type ) );
						
						?>
						<?php do_action( 'wcfm_products_manage_downloadable_end', $product_id, $product_type ); ?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
				
				<?php do_action( 'after_wcfm_products_downloadable', $product_id, $product_type ); ?>
				
				<!-- collapsible 3 - Grouped Product -->
				<div class="page_collapsible products_manage_grouped grouped" id="wcfm_products_manage_form_grouped_head"><label class="wcfmfa fa-object-group"></label><?php _e('Grouped Products', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container grouped">
					<div id="wcfm_products_manage_form_grouped_expander" class="wcfm-content">
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'product_manage_fields_grouped', array(  
																																																"grouped_products" => array('label' => __('Grouped products', 'wc-frontend-manager') , 'type' => 'select', 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'class' => 'wcfm-select wcfm_ele grouped', 'label_class' => 'wcfm_title wcfm_ele grouped', 'options' => $products_array, 'value' => $children, 'hints' => __( 'This lets you choose which products are part of this group.', 'wc-frontend-manager' ))
																																											), $product_id ) );
						?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
				
				<?php do_action( 'after_wcfm_products_manage_grouped', $product_id, $product_type ); ?>
				
				<?php 
				$wcfm_pm_block_class_shipping = apply_filters( 'wcfm_pm_block_class_shipping', 'simple variable nonvirtual booking non-accommodation-booking non-pw-gift-card' );
				if( !apply_filters( 'wcfm_is_allow_shipping', true ) || !apply_filters( 'wcfm_is_allow_pm_shipping', true ) ) { 
				  $wcfm_pm_block_class_shipping = 'wcfm_block_hide';
				}
				?>
				<!-- collapsible 4 -->
				<div class="page_collapsible products_manage_shipping <?php echo $wcfm_pm_block_class_shipping . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_shipping', '' ); ?>" id="wcfm_products_manage_form_shipping_head"><label class="wcfmfa fa-truck"></label><?php _e('Shipping', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container <?php echo $wcfm_pm_block_class_shipping . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_shipping', '' ); ?>">
					<div id="wcfm_products_manage_form_shipping_expander" class="wcfm-content">
						<?php do_action( 'wcfm_product_manage_fields_shipping_before', $product_id ); ?>
						<div class="wcfm_clearfix"></div>
						
						<?php do_action( 'wcfm_products_manage_shipping_start', $product_id, $product_type ); ?>
						
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_shipping', array(  "weight" => array( 'label' => __('Weight', 'wc-frontend-manager') . ' ('.get_option( 'woocommerce_weight_unit', 'kg' ).')' , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable booking', 'label_class' => 'wcfm_title', 'value' => $weight),
																																																	"length" => array( 'label' => __('Dimensions', 'wc-frontend-manager') . ' ('.get_option( 'woocommerce_dimension_unit', 'cm' ).')', 'placeholder' => __('Length', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable booking', 'label_class' => 'wcfm_title', 'value' => $length),
																																																	"width" => array( 'placeholder' => __('Width', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable booking', 'label_class' => 'wcfm_title', 'value' => $width),
																																																	"height" => array( 'placeholder' => __('Height', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable booking', 'label_class' => 'wcfm_title', 'value' => $height),
																																																	"shipping_class" => array('label' => __('Shipping class', 'wc-frontend-manager') , 'type' => 'select', 'options' => $shipping_option_array, 'class' => 'wcfm-select wcfm_ele simple variable booking', 'label_class' => 'wcfm_title', 'value' => $shipping_class)
																																												), $product_id ) );
						?>
						
						<?php do_action( 'wcfm_products_manage_shipping_end', $product_id, $product_type ); ?>
						
						<div class="wcfm_clearfix"></div>
						<?php do_action( 'wcfm_product_manage_fields_shipping_after', $product_id ); ?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
				
				<?php do_action( 'after_wcfm_products_manage_shipping', $product_id, $product_type ); ?>
				
				<?php if ( wc_tax_enabled() ) { ?>
					<?php 
					$wcfm_pm_block_class_tax = apply_filters( 'wcfm_pm_block_class_tax', 'simple variable booking non-groupbuy' );
					if( !apply_filters( 'wcfm_is_allow_tax', true ) || !apply_filters( 'wcfm_is_allow_pm_tax', true ) ) { 
						$wcfm_pm_block_class_tax = 'wcfm_block_hide';
					}
					?>
					<!-- collapsible 5 -->
					<div class="page_collapsible products_manage_tax <?php echo $wcfm_pm_block_class_tax . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_tax', '' ); ?>" id="wcfm_products_manage_form_tax_head"><label class="wcfmfa fa-money fa-money-bill-alt"></label><?php _e('Tax', 'wc-frontend-manager'); ?><span></span></div>
					<div class="wcfm-container <?php echo $wcfm_pm_block_class_tax . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_tax', '' ); ?>">
						<div id="wcfm_products_manage_form_tax_expander" class="wcfm-content">
						  <?php do_action( 'wcfm_products_manage_tax_start', $product_id, $product_type ); ?>
							<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_simple_fields_tax', array( 
																																																		"tax_status" => array('label' => __('Tax Status', 'wc-frontend-manager') , 'type' => 'select', 'options' => array( 'taxable' => __( 'Taxable', 'wc-frontend-manager' ), 'shipping' => __( 'Shipping only', 'wc-frontend-manager' ), 'none' => _x( 'None', 'Tax status', 'wc-frontend-manager' ) ), 'class' => 'wcfm-select wcfm_ele simple variable booking', 'label_class' => 'wcfm_title', 'value' => $tax_status, 'hints' => __( 'Define whether or not the entire product is taxable, or just the cost of shipping it.', 'wc-frontend-manager' )),
																																																		"tax_class" => array('label' => __('Tax Class', 'wc-frontend-manager') , 'type' => 'select', 'options' => $tax_classes_options, 'class' => 'wcfm-select wcfm_ele simple variable booking', 'label_class' => 'wcfm_title', 'value' => $tax_class, 'hints' => __( 'Choose a tax class for this product. Tax classes are used to apply different tax rates specific to certain types of product.', 'wc-frontend-manager' ))
																																													), $product_id ) );
							?>
							
							<?php do_action( 'wcfm_products_manage_tax_end', $product_id, $product_type ); ?>
						</div>
					</div>
					<!-- end collapsible -->
					<div class="wcfm_clearfix"></div>
				<?php } ?>
				
				<?php do_action( 'after_wcfm_products_manage_tax', $product_id, $product_type ); ?>
				
				<?php 
				$wcfm_pm_block_class_attributes = apply_filters( 'wcfm_pm_block_class_attributes', 'simple variable external grouped booking' );
				if( !apply_filters( 'wcfm_is_allow_attribute', true ) || !apply_filters( 'wcfm_is_allow_pm_attribute', true ) ) {
					$wcfm_pm_block_class_attributes = 'wcfm_block_hide';
				}	
				?>
				<!-- collapsible 6 -->
				<div class="page_collapsible products_manage_attribute <?php echo $wcfm_pm_block_class_attributes; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_attributes', '' ); ?>" id="wcfm_products_manage_form_attribute_head"><label class="wcfmfa fa-server"></label><?php _e('Attributes', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container <?php echo $wcfm_pm_block_class_attributes; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_attributes', '' ); ?>">
					<div id="wcfm_products_manage_form_attribute_expander" class="wcfm-content">
					  <?php do_action( 'wcfm_products_manage_attributes_start', $product_id, $product_type ); ?>
						<?php
						  do_action( 'wcfm_products_manage_attributes', $product_id );
						  
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'product_simple_fields_attributes', array(  
																																															"attributes" => array( 'label' => __( 'Attributes', 'wc-frontend-manager' ), 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_input_attributes wcfm_ele simple variable external grouped booking', 'has_dummy' => true, 'label_class' => 'wcfm_title', 'value' => $attributes, 'options' => array(
																																																	"term_name" => array('type' => 'hidden'),
																																																	"is_active" => array('label' => __('Active?', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele attribute_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking checkbox_title'),
																																																	"name" => array('label' => __('Name', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele attribute_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking'),
																																																	"value" => array('label' => __('Value(s):', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele simple variable external grouped booking', 'placeholder' => sprintf( __('Enter some text, some attributes by "%s" separating values.', 'wc-frontend-manager'), WC_DELIMITER ), 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking'),
																																																	"is_visible" => array('label' => __('Visible on the product page', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking checkbox_title'),
																																																	"is_variation" => array('label' => __('Use as Variation', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele variable variable-subscription', 'label_class' => 'wcfm_title checkbox_title wcfm_ele variable variable-subscription'),
																																																	"tax_name" => array('type' => 'hidden'),
																																																	"is_taxonomy" => array('type' => 'hidden')
																																															) )
																																										), $product_id ) );
						?>
						<div class="wcfm_clearfix"></div><br />
						<p>
							<?php if( apply_filters( 'wcfm_is_allow_add_attribute', true ) ) { ?>
								<select name="wcfm_attribute_taxonomy" class="wcfm-select wcfm_attribute_taxonomy">
									<option value="add_attribute"><?php _e( 'Add attribute', 'wc-frontend-manager' ); ?></option>
								</select>
								<button type="button" class="button wcfm_add_attribute"><?php _e( 'Add', 'wc-frontend-manager' ); ?></button>
							<?php } ?>
						</p>
						
						<?php do_action( 'wcfm_products_manage_attributes_end', $product_id, $product_type ); ?>
						
						<div class="wcfm_clearfix"></div><br />
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
				
				<?php do_action( 'after_wcfm_products_manage_attribute', $product_id, $product_type ); ?>
				
				<?php if( apply_filters( 'wcfm_is_allow_variable', true ) && apply_filters( 'wcfm_is_allow_pm_variable', true ) ) { ?>
				<!-- collapsible 7 -->
				<div class="page_collapsible products_manage_variations variable variations variable-subscription pw-gift-card" id="wcfm_products_manage_form_variations_head"><label class="wcfmfa fa-tasks"></label><?php _e('Variations', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container variable variable-subscription pw-gift-card">
				  <div id="wcfm_products_manage_form_variations_empty_expander" class="wcfm-content">
				    <?php printf( __( 'Before you can add a variation you need to add some variation attributes on the Attributes tab. %sLearn more%s', 'wc-frontend-manager' ), '<br /><h2><a class="wcfm_dashboard_item_title" target="_blank" href="' . apply_filters( 'wcfm_variations_help_link', 'https://docs.woocommerce.com/document/variable-product/' ) . '">', '</a></h2>' ); ?>
				  </div>
					<div id="wcfm_products_manage_form_variations_expander" class="wcfm-content">
					  <?php do_action( 'wcfm_products_manage_variable_start', $product_id, $product_type ); ?>
					  
					  <p>
							<div class="default_attributes_holder">
							  <p class="wcfm_title selectbox_title"><strong><?php _e( 'Default Form Values:', 'wc-frontend-manager' ); ?></strong></p>
								<input type="hidden" name="default_attributes_hidden" data-name="default_attributes_hidden" value="<?php echo esc_attr( $default_attributes ); ?>" />
							</div>
						</p>
						
						<?php if( apply_filters( 'wcfm_is_allow_variable_bulk_options', true ) ) { ?>
							<p>
								<p class="variations_options wcfm_title"><strong><?php _e('Variations Bulk Options', 'wc-frontend-manager'); ?></strong></p>
								<label class="screen-reader-text" for="variations_options"><?php _e('Variations Bulk Options', 'wc-frontend-manager'); ?></label>
								<select id="variations_options" name="variations_options" class="wcfm-select wcfm_ele variable-subscription variable pw-gift-card">
									<option value="" selected="selected"><?php _e( 'Choose option', 'wc-frontend-manager' ); ?></option>
									<option value="variation_auto_generate"><?php _e( 'Create variations from all attributes', 'woocommerce' ); ?></option>
									<optgroup label="<?php _e( 'Status', 'wc-frontend-manager' ); ?>">
										<option value="on_enabled"><?php _e( 'Enable all Variations', 'wc-frontend-manager' ); ?></option>
										<option value="off_enabled"><?php _e( 'Disable all Variations', 'wc-frontend-manager' ); ?></option>
										<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() && apply_filters( 'wcfmu_is_allow_downloadable', true ) && apply_filters( 'wcfmu_is_allow_pm_downloadable', true ) ) { ?>
											<option value="on_downloadable"><?php _e( 'Set variations "Downloadable"', 'wc-frontend-manager' ); ?></option>
											<option value="off_downloadable"><?php _e( 'Set variations "Non-Downloadable"', 'wc-frontend-manager' ); ?></option>
										<?php } ?>
										<?php if( apply_filters( 'wcfmu_is_allow_virtual', true ) && apply_filters( 'wcfmu_is_allow_pm_virtual', true ) ) { ?>
											<option value="on_virtual"><?php _e( 'Set variations "Virtual"', 'wc-frontend-manager' ); ?></option>
											<option value="off_virtual"><?php _e( 'Set variations "Non-Virtual"', 'wc-frontend-manager' ); ?></option>
										<?php } ?>
									</optgroup>
									<optgroup label="<?php _e( 'Pricing', 'wc-frontend-manager' ); ?>">
										<option value="set_regular_price"><?php _e( 'Regular prices', 'wc-frontend-manager' ); ?></option>
										<option value="regular_price_increase"><?php _e( 'Regular price increase', 'wc-frontend-manager' ); ?></option>
										<option value="regular_price_decrease"><?php _e( 'Regular price decrease', 'wc-frontend-manager' ); ?></option>
										<option value="set_sale_price"><?php _e( 'Sale prices', 'wc-frontend-manager' ); ?></option>
										<option value="sale_price_increase"><?php _e( 'Sale price increase', 'wc-frontend-manager' ); ?></option>
										<option value="sale_price_decrease"><?php _e( 'Sale price decrease', 'wc-frontend-manager' ); ?></option>
									</optgroup>
									<?php if( apply_filters( 'wcfm_is_allow_inventory', true ) && apply_filters( 'wcfm_is_allow_pm_inventory', true ) ) { ?>
										<optgroup label="<?php _e( 'Inventory', 'wc-frontend-manager' ); ?>">
											<option value="on_manage_stock"><?php _e( 'ON "Manage stock"', 'wc-frontend-manager' ); ?></option>
											<option value="off_manage_stock"><?php _e( 'OFF "Manage stock"', 'wc-frontend-manager' ); ?></option>
											<option value="variable_stock"><?php _e( 'Stock', 'wc-frontend-manager' ); ?></option>
											<option value="variable_increase_stock"><?php _e( 'Increase Stock', 'wc-frontend-manager' ); ?></option>
											<option value="variable_stock_status_instock"><?php _e( 'Set Status - In stock', 'wc-frontend-manager' ); ?></option>
											<option value="variable_stock_status_outofstock"><?php _e( 'Set Status - Out of stock', 'wc-frontend-manager' ); ?></option>
											<option value="variable_stock_status_onbackorder"><?php _e( 'Set Status - On backorder', 'wc-frontend-manager' ); ?></option>
										</optgroup>
									<?php } ?>
									<?php if( apply_filters( 'wcfm_is_allow_shipping', true ) && apply_filters( 'wcfm_is_allow_pm_shipping', true ) ) { ?>
										<optgroup label="<?php _e( 'Shipping', 'wc-frontend-manager' ); ?>">
											<option value="set_length"><?php _e( 'Length', 'wc-frontend-manager' ); ?></option>
											<option value="set_width"><?php _e( 'Width', 'wc-frontend-manager' ); ?></option>
											<option value="set_height"><?php _e( 'Height', 'wc-frontend-manager' ); ?></option>
											<option value="set_weight"><?php _e( 'Weight', 'wc-frontend-manager' ); ?></option>
										</optgroup>
									<?php } ?>
									<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() && apply_filters( 'wcfmu_is_allow_downloadable', true ) && apply_filters( 'wcfmu_is_allow_pm_downloadable', true ) ) { ?>
										<optgroup label="<?php _e( 'Downloadable products', 'wc-frontend-manager' ); ?>">
											<option value="variable_download_limit"><?php _e( 'Download limit', 'wc-frontend-manager' ); ?></option>
											<option value="variable_download_expiry"><?php _e( 'Download expiry', 'wc-frontend-manager' ); ?></option>
										</optgroup>
									<?php } ?>
								</select>
							</p>
						<?php } ?>
						
						<?php
						 $WCFM->wcfm_fields->wcfm_generate_form_field( array(  
																																	"variations" => array('type' => 'multiinput', 'class' => 'wcfm_ele variable variable-subscription pw-gift-card', 'label_class' => 'wcfm_title', 'value' => $variations, 'options' => 
																																			apply_filters( 'wcfm_product_manage_fields_variations', array(
																																			"id" => array('type' => 'hidden', 'class' => 'variation_id'),
																																			"enable" => array('label' => __('Enable', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'dfvalue' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele variable variable-subscription pw-gift-card', 'label_class' => 'wcfm_title checkbox_title'),
																																			"is_virtual" => array('label' => __('Virtual', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele variable variable-subscription pw-gift-card variation_is_virtual_ele', 'label_class' => 'wcfm_title checkbox_title'),
																																			"manage_stock" => array('label' => __('Manage Stock', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele variable variable-subscription pw-gift-card variation_manage_stock_ele', 'label_class' => 'wcfm_title checkbox_title'),
																																			"wcfm_element_breaker_variation_1" => array( 'type' => 'html', 'value' => '<div class="wcfm-cearfix"></div>'),
																																			"image" => array('label' => __('Image', 'wc-frontend-manager'), 'type' => 'upload', 'class' => 'wcfm-text wcfm_ele variable variable-subscription pw-gift-card', 'label_class' => 'wcfm_title wcfm_half_ele_upload_title'),
																																			"wcfm_element_breaker_variation_2" => array( 'type' => 'html', 'value' => '<div class="wcfm-cearfix"></div>'),
																																			"regular_price" => array('label' => __('Regular Price', 'wc-frontend-manager') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele variable pw-gift-card', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable pw-gift-card', 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ) ),
																																			"sale_price" => array('label' => __('Sale Price', 'wc-frontend-manager') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele variable variable-subscription pw-gift-card', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable variable-subscription pw-gift-card', 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ) ),
																																			"sale_price_dates_from" => array('label' => __('From', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __( 'From', 'wc-frontend-manager' ) . ' ... YYYY-MM-DD', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele var_sales_schedule_ele var_sale_date_from variable variable-subscription pw-gift-card', 'label_class' => 'wcfm_ele wcfm_half_ele_title var_sales_schedule_ele variable variable-subscription pw-gift-card'),
																																			"sale_price_dates_to" => array('label' => __('Upto', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __( 'To', 'wc-frontend-manager' ) . ' ... YYYY-MM-DD', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele var_sales_schedule_ele var_sale_date_upto variable variable-subscription pw-gift-card', 'label_class' => 'wcfm_ele wcfm_half_ele_title var_sales_schedule_ele wcfm_title variable variable-subscription pw-gift-card'),
																																			"stock_qty" => array('label' => __('Stock Qty', 'wc-frontend-manager') , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele variable variable-subscription pw-gift-card variation_non_manage_stock_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title variation_non_manage_stock_ele', 'attributes' => array( 'min' => '1', 'step'=> '1' ) ),
																																			"backorders" => array('label' => __('Backorders?', 'wc-frontend-manager') , 'type' => 'select', 'options' => array('no' => __('Do not Allow', 'wc-frontend-manager'), 'notify' => __('Allow, but notify customer', 'wc-frontend-manager'), 'yes' => __('Allow', 'wc-frontend-manager')), 'class' => 'wcfm-select wcfm_ele wcfm_half_ele variable variable-subscription pw-gift-card variation_non_manage_stock_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title variation_non_manage_stock_ele'),
																																			"sku" => array('label' => __('SKU', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele variable variable-subscription pw-gift-card', 'label_class' => 'wcfm_title wcfm_half_ele_title'),
																																			"stock_status" => array('label' => __('Stock status', 'wc-frontend-manager') , 'type' => 'select', 'options' => array('instock' => __('In stock', 'wc-frontend-manager'), 'outofstock' => __('Out of stock', 'wc-frontend-manager'), 'onbackorder' => __( 'On backorder', 'wc-frontend-manager' )), 'class' => 'wcfm-select wcfm_ele wcfm_half_ele variable variable-subscription pw-gift-card variation_stock_status_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title variation_stock_status_ele'), 
																																			"attributes" => array('type' => 'hidden')
																																	), $variations, $variation_shipping_option_array, $variation_tax_classes_options, $products_array, $product_id, $product_type ) )
																												) );
						?>
						
						<?php do_action( 'wcfm_products_manage_variable_end', $product_id, $product_type ); ?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
				<?php } ?>
				
				<?php do_action( 'after_wcfm_products_manage_variable', $product_id, $product_type ); ?>
				
				<?php 
				$wcfm_pm_block_class_linked = apply_filters( 'wcfm_pm_block_class_linked', 'simple variable external grouped' );
				if( !apply_filters( 'wcfm_is_allow_linked', true ) ) { 
				  $wcfm_pm_block_class_linked = 'wcfm_block_hide'; 
				}
				?>
				<!-- collapsible 8 - Linked Product -->
				<div class="page_collapsible products_manage_linked <?php echo $wcfm_pm_block_class_linked . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_linked', '' ); ?>" id="wcfm_products_manage_form_linked_head"><label class="wcfmfa fa-link"></label><?php _e('Linked', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container <?php echo $wcfm_pm_block_class_linked . ' ' . $wcfm_wpml_edit_disable_element; ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_linked', '' ); ?>">
					<div id="wcfm_products_manage_form_linked_expander" class="wcfm-content">
					  <?php do_action( 'wcfm_products_manage_linked_start', $product_id, $product_type ); ?>
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_linked', array(  
																																																"upsell_ids" => array('label' => __('Up-sells', 'wc-frontend-manager') , 'type' => 'select', 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'class' => 'wcfm-select wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'options' => $products_array, 'value' => $upsell_ids, 'hints' => __( 'Up-sells are products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.', 'wc-frontend-manager' )),
																																																"crosssell_ids" => array('label' => __('Cross-sells', 'wc-frontend-manager') , 'type' => 'select', 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'class' => 'wcfm-select wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'options' => $products_array, 'value' => $crosssell_ids, 'hints' => __( 'Cross-sells are products which you promote in the cart, based on the current product.', 'wc-frontend-manager' ))
																																											), $product_id, $products_array ) );
						?>
						<?php do_action( 'wcfm_products_manage_linked_end', $product_id, $product_type ); ?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
				
				<?php do_action( 'after_wcfm_products_manage_linked', $product_id, $product_type ); ?>