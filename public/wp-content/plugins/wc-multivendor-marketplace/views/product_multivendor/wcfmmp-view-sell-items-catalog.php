<?php
global $WCFM, $wp_query;

if( !wcfm_is_vendor() || !apply_filters( 'wcfmmp_is_allow_single_product_multivendor', true ) || !apply_filters( 'wcfm_is_allow_manage_products', true ) || !apply_filters( 'wcfm_is_allow_add_products', true ) || !apply_filters( 'wcfm_is_allow_pm_add_products', true ) || !apply_filters( 'wcfm_is_allow_product_limit', true ) || !apply_filters( 'wcfm_is_allow_space_limit', true ) ) {
	wcfm_restriction_message_show( "Sell Items Catalog" );
	return;
}

?>

<div class="collapse wcfm-collapse" id="wcfm_sell_items_catalog_listing">
	
	<div class="wcfm-page-headig">
		<span class="wcfmfa fa-hand-pointer"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Add to My Store Catalog', 'wc-multivendor-marketplace' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_sell_items_catalog' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Add to My Store Catalog', 'wc-multivendor-marketplace' ); ?></h2>
			
			<?php
			if( $has_new = apply_filters( 'wcfm_add_new_product_sub_menu', true ) ) {
				echo '<a id="add_new_product_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_edit_product_url().'" data-tip="' . __('Add New Product', 'wc-frontend-manager') . '"><span class="wcfmfa fa-cube"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager') . '</span></a>';
			}
			?>
			
			<?php do_action( 'wcfm_sell_items_catalog_quick_actions' ); ?>
			
			<div class="wcfm-clearfix"></div>
		</div>
		<div class="wcfm-clearfix"></div><br />
		
		<div class="wcfm_sell_items_catalog_filter_wrap wcfm_filters_wrap">
		  <input type="submit" id="wcfm_bulk_add_to_my_store" class="wcfm_bulk_add_to_my_store wcfm_submit_button" value="<?php _e( 'Bulk Add', 'wc-multivendor-marketplace' ); ?>" />
			<?php	
			// Category Filtering
			if( apply_filters( 'wcfm_is_products_taxonomy_filter', true, 'product_cat' ) && apply_filters( 'wcfm_is_products_category_filter', true ) ) {
				$product_categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0&parent=0' );
				$categories = array();
				
				$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_product_cat" => array( 'type' => 'select', 'options' => $categories, 'custom_attributes' => array( 'taxonomy' => 'product_cat', 'parent' => '' ), 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
				
			}
			
			// Custom Taxonomy Filtering
			if( apply_filters( 'wcfm_is_products_custom_taxonomy_filter', true ) ) {
				$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
				if( !empty( $product_taxonomies ) ) {
					foreach( $product_taxonomies as $product_taxonomy ) {
						if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) ) {
							if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
								if( apply_filters( 'wcfm_is_products_taxonomy_filter', true, $product_taxonomy->name ) && apply_filters( 'wcfm_is_products_custom_taxonomy_filter_'.$product_taxonomy->name, true ) ) {
									
									$product_categories   = get_terms( $product_taxonomy->name, 'orderby=name&hide_empty=0&parent=0' );
					
									echo '<select id="dropdown_product_' . $product_taxonomy->name . '" data-taxonomy="'.$product_taxonomy->name.'" name="dropdown_product_' . $product_taxonomy->name . '" class="dropdown_product_'. $product_taxonomy->name . ' dropdown_product_custom_taxonomy" style="width: 150px;">';
										echo '<option value="" selected="selected">' . __( 'Show all ', 'wc-frontend-manager' ) . $product_taxonomy->label . '</option>';
										if ( $product_categories ) {
											$WCFM->library->generateTaxonomyHTML( $product_taxonomy->name, $product_categories, array() );
										}
									echo '</select>';
								}
							}
						}
					}
				}
			}
			
			// Type filtering
			if( $wcfm_is_products_type_filter = apply_filters( 'wcfm_is_products_type_filter', true ) ) {
				$product_types = apply_filters( 'wcfm_product_types', array('simple' => __('Simple Product', 'wc-frontend-manager'), 'variable' => __('Variable Product', 'wc-frontend-manager'), 'grouped' => __('Grouped Product', 'wc-frontend-manager'), 'external' => __('External/Affiliate Product', 'wc-frontend-manager') ) );
				$output  = '<select name="product_type" id="dropdown_product_type" style="width: 160px;">';
				$output .= '<option value="">' . __( 'Show all product types', 'wc-frontend-manager' ) . '</option>';
				
				foreach ( $product_types as $product_type_name => $product_type_label ) {
					$output .= '<option value="' . $product_type_name . '">' . $product_type_label . '</option>';
				
					if ( 'simple' == $product_type_name ) {
						
						$product_type_options = apply_filters( 'wcfm_non_allowd_product_type_options', array( 'virtual' => 'virtual', 'downloadable' => 'downloadable' ) ); 
						
						if( !empty( $product_type_options['downloadable'] ) ) {
							$output .= '<option value="downloadable" > &rarr; ' . __( 'Downloadable', 'wc-frontend-manager' ) . '</option>';
						}
						
						if( !empty( $product_type_options['virtual'] ) ) {
							$output .= '<option value="virtual" > &rarr;  ' . __( 'Virtual', 'wc-frontend-manager' ) . '</option>';
						}
					}
				}
				
				$output .= '</select>';
				
				echo apply_filters( 'woocommerce_product_filters', $output );
			}
			?>
		</div>
		
		<div class="wcfm-container">
			<div id="wcfm_sell_items_catalog_listing_expander" class="wcfm-content">
				<table id="wcfm-sell_items_catalog" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <th>
							  <input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select multiple and add to My Store', 'wc-multivendor-marketplace' ); ?>" />
						  </th>
							<th><span class="wcfmfa fa-image text_tip" data-tip="<?php _e( 'Image', 'wc-frontend-manager' ); ?>"></span></th>
							<th style="max-width: 250px;"><?php _e( 'Name', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Price', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Taxonomies', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Store', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_sell_items_catalog_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <th>
								<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select multiple and add to My Store', 'wc-multivendor-marketplace' ); ?>" />
						  </th>
							<th><span class="wcfmfa fa-image text_tip" data-tip="<?php _e( 'Image', 'wc-frontend-manager' ); ?>"></span></th>
							<th style="max-width: 250px;"><?php _e( 'Name', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Price', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Taxonomies', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Store', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( apply_filters( 'wcfm_sell_items_catalog_additional_info_column_label', __( 'Additional Info', 'wc-frontend-manager' ) ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		
		<div class="wcfm-container">
			<div id="wcfm_withdrawal_requests_actions_expander" class="wcfm-content">
				<div id="wcfm_products_simple_submit" class="wcfm_form_simple_submit_wrapper">
					<div class="wcfm-message" tabindex="-1"></div>
					<input type="submit" id="wcfm_bulk_add_to_my_store_bottom" class="wcfm_bulk_add_to_my_store_bottom wcfm_submit_button" value="<?php _e( 'Bulk Add to My Store', 'wc-multivendor-marketplace' ); ?>" />
				</div>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_sell_items_catalog' );
		?>
	</div>
</div>