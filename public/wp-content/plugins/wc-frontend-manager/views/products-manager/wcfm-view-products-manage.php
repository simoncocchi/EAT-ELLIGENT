<?php
/**
 * WCFM plugin view
 *
 * WCFM Products Manage view
 * This template can be overridden by copying it to yourtheme/wcfm/products-manager/
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/products-manager
 * @version   1.0.0
 */
 
global $wp, $WCFM, $wc_product_attributes;

if( apply_filters( 'wcfm_is_pref_restriction_check', true ) ) {
	$wcfm_is_allow_manage_products = apply_filters( 'wcfm_is_allow_manage_products', true );
	if( !$wcfm_is_allow_manage_products ) {
		wcfm_restriction_message_show( "Products" );
		return;
	}
}

if( isset( $wp->query_vars['wcfm-products-manage'] ) && empty( $wp->query_vars['wcfm-products-manage'] ) ) {
	if( !apply_filters( 'wcfm_is_allow_add_products', true ) ) {
		wcfm_restriction_message_show( "Add Product" );
		return;
	}
	if( !apply_filters( 'wcfm_is_allow_pm_add_products', true ) ) {
		return;
	}
	if( !apply_filters( 'wcfm_is_allow_product_limit', true ) ) {
		if( WCFM_Dependencies::wcfmvm_plugin_active_check() ) {
			?>
			<div class="wcfm-clearfix"></div><br />
			<div class="collapse wcfm-collapse">
				<div class="wcfm-page-headig">
					<span class="wcfmfa fa-cube"></span>
					<span class="wcfm-page-heading-text"><?php _e( 'Add Product', 'wc-frontend-manager' ); ?></span>
					<?php do_action( 'wcfm_page_heading' ); ?>
				</div>
				<div class="wcfm-collapse-content wcfm-nolimit-content">
					<div class="wcfm-container">
						<div class="wcfm-clearfix"></div><br />
						<h2><?php _e( 'You have reached your product limit!', 'wc-frontend-manager' ); ?></h2>
						<div class="wcfm-clearfix"></div><br />
						<?php 
						if( !apply_filters( 'wcfm_is_allow_verification_product_limit', true ) ) {
							do_action( 'wcfm_verification_product_limit_reached' );
						} else {
							do_action( 'wcfm_product_limit_reached' ); 
						}
						?>
						<div class="wcfm-clearfix"></div><br />
					</div>
				</div>
			</div>
			<?php
		} else {
			wcfm_restriction_message_show( "Product Limit Reached" );
		}
		return;
	}
	if( !apply_filters( 'wcfm_is_allow_space_limit', true ) ) {
		wcfm_restriction_message_show( "Space Limit Reached" );
		return;
	}
} elseif( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
	$wcfm_products_single = get_post( $wp->query_vars['wcfm-products-manage'] );
	if( $wcfm_products_single->post_status == 'publish' ) {
		if( !apply_filters( 'wcfm_is_allow_edit_products', true ) ) {
			wcfm_restriction_message_show( "Edit Product" );
			return;
		}
	}
	if( !apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) {
		wcfm_restriction_message_show( "Edit Product" );
		return;
	}
	if( wcfm_is_vendor() ) {
		$is_product_from_vendor = $WCFM->wcfm_vendor_support->wcfm_is_product_from_vendor( $wp->query_vars['wcfm-products-manage'] );
		if( !$is_product_from_vendor ) {
			if( apply_filters( 'wcfm_is_show_product_restrict_message', true, $wcfm_products_single->ID ) ) {
				wcfm_restriction_message_show( "Restricted Product" );
			} else {
				echo apply_filters( 'wcfm_show_custom_product_restrict_message', '', $wcfm_products_single->ID );
			}
			return;
		}
	}
}

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

if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
	
	$product = wc_get_product( $wp->query_vars['wcfm-products-manage'] );
	
	if( !is_a( $product, 'WC_Product' ) ) {
		wcfm_restriction_message_show( "Invalid Product" );
		return;
	}
	
	
	// Fetching Product Data
	if($product && !empty($product)) {
		$product_id = $wp->query_vars['wcfm-products-manage'];
		$wcfm_products_single = get_post($product_id);
		$product_type = $product->get_type();
		$title = $product->get_title( 'edit' );
		$sku = $product->get_sku( 'edit' );
		//$visibility = get_post_meta( $product_id, '_visibility', true);
		$excerpt = wpautop( $product->get_short_description( 'edit' ) );
		$description = wpautop( $product->get_description( 'edit' ) );
		$regular_price = $product->get_regular_price( 'edit' );
		$sale_price = $product->get_sale_price( 'edit' );
		
		$sale_date_from = $product->get_date_on_sale_from( 'edit' ) && ( $date = $product->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_date_upto = $product->get_date_on_sale_to( 'edit' ) && ( $date = $product->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
		
		$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
		if( !$rich_editor && apply_filters( 'wcfm_is_allow_editor_newline_replace', true ) ) {
			$breaks = apply_filters( 'wcfm_editor_newline_generators', array("<br />","<br>","<br/>") ); 
			
			$excerpt = str_ireplace( $breaks, "\r\n", $excerpt );
			$excerpt = strip_tags( $excerpt );
			
			$description = str_ireplace( $breaks, "\r\n", $description );
			$description = strip_tags( $description );
		}
		
		// External product option
		$product_url = get_post_meta( $product_id, '_product_url', true);
		$button_text = get_post_meta( $product_id, '_button_text', true);
		
		// Virtual
		$is_virtual = ( get_post_meta( $product_id, '_virtual', true) == 'yes' ) ? 'enable' : '';
		
		// Download ptions
		$wcfm_downloadable_product_types = apply_filters( 'wcfm_downloadable_product_types', array( 'simple', 'subscription' ) );
		$is_downloadable = ( get_post_meta( $product_id, '_downloadable', true) == 'yes' ) ? 'enable' : '';
		if( !in_array( $product_type, $wcfm_downloadable_product_types ) ) $is_downloadable = '';
		if($is_downloadable == 'enable') {
			$downloadable_files = (array) get_post_meta( $product_id, '_downloadable_files', true);
			$download_limit = ( -1 == get_post_meta( $product_id, '_download_limit', true) ) ? '' : get_post_meta( $product_id, '_download_limit', true);
			$download_expiry = ( -1 == get_post_meta( $product_id, '_download_expiry', true) ) ? '' : get_post_meta( $product_id, '_download_expiry', true);
		}
		
		// Product Images
		$featured_img = ($product->get_image_id()) ? $product->get_image_id() : '';
		//if($featured_img) $featured_img = wp_get_attachment_url($featured_img);
		//if(!$featured_img) $featured_img = '';
		$gallery_img_ids = $product->get_gallery_image_ids();
		if(!empty($gallery_img_ids)) {
			foreach($gallery_img_ids as $gallery_img_id) {
				$gallery_img_urls[]['gimage'] = $gallery_img_id; //wp_get_attachment_url($gallery_img_id);
			}
		}
		
		// Product Categories
		$pcategories = get_the_terms( $product_id, 'product_cat' );
		if( !empty($pcategories) ) {
			foreach($pcategories as $pkey => $pcategory) {
				$categories[] = $pcategory->term_id;
			}
		} else {
			$categories = array();
		}
		
		// Product Tags
		if( apply_filters( 'wcfm_is_tags_input', true ) ) {
			$product_tag_list = wp_get_post_terms($product_id, 'product_tag', array("fields" => "names"));
		  $product_tags = apply_filters( 'wcfm_pm_product_tags_after_save', implode(',', $product_tag_list), $product_id );
		} else {
			$product_tag_list = wp_get_post_terms($product_id, 'product_tag', array("fields" => "ids"));
			$product_tags = apply_filters( 'wcfm_pm_product_tags_after_save', $product_tag_list, $product_id );
		}
		
		// Product Stock options
		$manage_stock = $product->get_manage_stock( 'edit' ) ? 'enable' : '';
		$stock_qty = $product->get_stock_quantity( 'edit' );
		$backorders = $product->get_backorders( 'edit' );
		$stock_status = $product->get_stock_status( 'edit' ); 
		$sold_individually = $product->is_sold_individually( 'edit' ) ? 'enable' : '';
		
		// Product Shipping Data
		$weight = $product->get_weight( 'edit' );
		$length = $product->get_length( 'edit' );
		$width = $product->get_width( 'edit' );
		$height = $product->get_height( 'edit' );
		$shipping_class = $product->get_shipping_class_id( 'edit' );
		
		// Product Tax Data
		$tax_status = $product->get_tax_status( 'edit' );
		$tax_class = $product->get_tax_class( 'edit' );
		
		// Product Attributes
		$wcfm_attributes = get_post_meta( $product_id, '_product_attributes', true );
		if(!empty($wcfm_attributes)) {
			$acnt = 0;
			foreach($wcfm_attributes as $wcfm_attribute) {
				
				if ( $wcfm_attribute['is_taxonomy'] ) {
					$att_taxonomy = $wcfm_attribute['name'];

					if ( ! taxonomy_exists( $att_taxonomy ) ) {
						continue;
					}
					
					$attribute_taxonomy = $wc_product_attributes[ $att_taxonomy ];
					
					$attributes[$acnt]['term_name'] = $att_taxonomy;
					$attributes[$acnt]['name'] = wc_attribute_label( $att_taxonomy );
					$attributes[$acnt]['attribute_taxonomy'] = $attribute_taxonomy;
					$attributes[$acnt]['tax_name'] = $att_taxonomy;
					$attributes[$acnt]['is_taxonomy'] = 1;
					
					if ( 'text' !== $attribute_taxonomy->attribute_type ) {
						$attributes[$acnt]['attribute_type'] = 'select';
					} else {
						$attributes[$acnt]['attribute_type'] = 'text';
						$attributes[$acnt]['value'] = esc_attr( implode( ' ' . WC_DELIMITER . ' ', wp_get_post_terms( $product_id, $att_taxonomy, array( 'fields' => 'names' ) ) ) );
					}
				} else {
					$attributes[$acnt]['term_name'] = apply_filters( 'woocommerce_attribute_label', $wcfm_attribute['name'], $wcfm_attribute['name'], $product );
					$attributes[$acnt]['name'] = apply_filters( 'woocommerce_attribute_label', $wcfm_attribute['name'], $wcfm_attribute['name'], $product );
					$attributes[$acnt]['value'] = $wcfm_attribute['value'];
					$attributes[$acnt]['tax_name'] = '';
					$attributes[$acnt]['is_taxonomy'] = 0;
					$attributes[$acnt]['attribute_type'] = 'text';
				}
				
				$attributes[$acnt]['is_active'] = 'enable';
				$attributes[$acnt]['is_visible'] = $wcfm_attribute['is_visible'] ? 'enable' : '';
				$attributes[$acnt]['is_variation'] = $wcfm_attribute['is_variation'] ? 'enable' : '';
				
				if( 'text' !== $attributes[$acnt]['attribute_type'] ) {
					$attributes_select_type[$acnt] = $attributes[$acnt];
					unset($attributes[$acnt]);
				}
				$acnt++;
			}
		}
		
		// Product Default Attributes
		$default_attributes = json_encode( (array) get_post_meta( $product_id, '_default_attributes', true ) );
		
		// Variable Product Variations
		$wcfm_variable_product_types = apply_filters( 'wcfm_variable_product_types', array( 'variable', 'variable-subscription', 'pw-gift-card' ) );
		if( in_array( $product_type, $wcfm_variable_product_types ) ) {
			$variation_ids = $product->get_children();
			if(!empty($variation_ids)) {
				foreach($variation_ids as $variation_id_key => $variation_id) {
					$variation_data = new WC_Product_Variation($variation_id);
					
					$variations[$variation_id_key]['id'] = $variation_id;
					$variations[$variation_id_key]['enable'] = in_array( $variation_data->get_status( 'edit' ), array( 'publish', false ), true ) ? 'enable' : '';
					$variations[$variation_id_key]['sku'] = $variation_data->get_sku( 'edit' );
					
					// Variation Image
					$variation_img = $variation_data->get_image_id();
					if($variation_img) $variation_img = wp_get_attachment_url($variation_img);
					else $variation_img = '';
					$variations[$variation_id_key]['image'] = $variation_img;
					
					// Variation Price
					$variations[$variation_id_key]['regular_price'] = $variation_data->get_regular_price( 'edit' );
					$variations[$variation_id_key]['sale_price'] = $variation_data->get_sale_price( 'edit' );
					
					// Variation Sales Schedule
					$variations[$variation_id_key]['sale_price_dates_from'] = $variation_data->get_date_on_sale_from( 'edit' ) && ( $date = $variation_data->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
					$variations[$variation_id_key]['sale_price_dates_to'] = $variation_data->get_date_on_sale_to( 'edit' ) && ( $date = $variation_data->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
					
					// Variation Stock Data
					$variations[$variation_id_key]['manage_stock'] = $variation_data->get_manage_stock( 'edit' ) ? 'enable' : '';
					$variations[$variation_id_key]['stock_status'] = $variation_data->get_stock_status( 'edit' );
					$variations[$variation_id_key]['stock_qty'] = $variation_data->get_stock_quantity( 'edit' );
					$variations[$variation_id_key]['backorders'] = $variation_data->get_backorders( 'edit' );
					
					// Variation Virtual Data
					$variations[$variation_id_key]['is_virtual'] = ( 'yes' == get_post_meta($variation_id, '_virtual', true) ) ? 'enable' : '';
					
					// Variation Downloadable Data
					$variations[$variation_id_key]['is_downloadable'] = ( 'yes' == get_post_meta($variation_id, '_downloadable', true) ) ? 'enable' : '';
					$variations[$variation_id_key]['downloadable_files'] = get_post_meta($variation_id, '_downloadable_files', true);
					$variations[$variation_id_key]['download_limit'] = ( -1 == get_post_meta($variation_id, '_download_limit', true) ) ? '' : get_post_meta($variation_id, '_download_limit', true);
					$variations[$variation_id_key]['download_expiry'] = ( -1 == get_post_meta($variation_id, '_download_expiry', true) ) ? '' : get_post_meta($variation_id, '_download_expiry', true);
					if(!empty($variations[$variation_id_key]['downloadable_files'])) {
						foreach($variations[$variation_id_key]['downloadable_files'] as $variations_downloadable_files) {
							$variations[$variation_id_key]['downloadable_file'] = $variations_downloadable_files['file'];
							$variations[$variation_id_key]['downloadable_file_name'] = $variations_downloadable_files['name'];
						}
					}
					
					// Variation Shipping Data
					$variations[$variation_id_key]['weight'] = $variation_data->get_weight( 'edit' );
					$variations[$variation_id_key]['length'] = $variation_data->get_length( 'edit' );
					$variations[$variation_id_key]['width'] = $variation_data->get_width( 'edit' );
					$variations[$variation_id_key]['height'] = $variation_data->get_height( 'edit' );
					$variations[$variation_id_key]['shipping_class'] = $variation_data->get_shipping_class_id( 'edit' );
					
					// Variation Tax
					$variations[$variation_id_key]['tax_class'] = $variation_data->get_tax_class( 'edit' );
					
					// Variation Attributes
					$variations[$variation_id_key]['attributes'] = json_encode( $variation_data->get_variation_attributes( 'edit' ) );
					
					// Description
					$variations[$variation_id_key]['description'] = get_post_meta($variation_id, '_variation_description', true);
					
					$variations = apply_filters( 'wcfm_variation_edit_data', $variations, $variation_id, $variation_id_key, $product_id );
				}
			}
		}
		
		$upsell_ids = get_post_meta( $product_id, '_upsell_ids', true ) ? get_post_meta( $product_id, '_upsell_ids', true ) : array();
		$crosssell_ids = get_post_meta( $product_id, '_crosssell_ids', true ) ? get_post_meta( $product_id, '_crosssell_ids', true ) : array();
		$children = get_post_meta( $product_id, '_children', true ) ? get_post_meta( $product_id, '_children', true ) : array();
	}
}

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
		$upsell_product = wc_get_product( absint($upsell_id) );
		if( is_a( $upsell_product, 'WC_Product' ) ) {
			$products_array[$upsell_id] = $upsell_product->get_title( 'edit' );	
		}
	}
}
if( !empty( $crosssell_ids ) ) {
	foreach( $crosssell_ids as $crosssell_id ) {
		$crosssell_product = wc_get_product( absint($crosssell_id) );
		if( is_a( $crosssell_product, 'WC_Product' ) ) {
			$products_array[$crosssell_id] = $crosssell_product->get_title( 'edit' );	
		}
	}
}

if( !empty( $children ) && is_array( $children ) ) {
	foreach( $children as $group_children ) {
		$group_children_product = wc_get_product( absint($group_children) );
		if( is_a( $group_children_product, 'WC_Product' ) ) {
			$products_array[$group_children] = $group_children_product->get_title( 'edit' );	
		}
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
if ( $product_id && defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
	global $sitepress, $wpml_post_translations;
	$default_language = $sitepress->get_default_language();
	$current_language = $sitepress->get_current_language();
	
	$source_language  = $wpml_post_translations->get_source_lang_code( $product_id );
	
	//echo $source_language . "::" . $current_language . "::" . $default_language;
		
	if( $source_language && ( $source_language != $current_language ) ) {
		$wcfm_is_translated_product = true;
		$wcfm_wpml_edit_disable_element = 'wcfm_wpml_hide';
	}
}
?>

<div class="collapse wcfm-collapse" id="wcfm_products_manage">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-cube"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Manage Product', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_product_simple' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<?php do_action( 'before_wcfm_products_manage_title' ); ?>
			<h2><?php if( $product_id ) { _e('Edit Product', 'wc-frontend-manager' ); } else { _e('Add Product', 'wc-frontend-manager' ); } ?></h2>
			<?php do_action( 'after_wcfm_products_manage_title' ); ?>
			
			<?php
			if( $product_id ) {
				?>
				<span class="product-status product-status-<?php echo $wcfm_products_single->post_status; ?>"><?php if( $wcfm_products_single->post_status == 'publish' ) { _e( 'Published', 'wc-frontend-manager' ); } else { _e( ucfirst( $wcfm_products_single->post_status ), 'wc-frontend-manager' ); } ?></span>
				<?php
				if( apply_filters( 'wcfm_is_allow_product_preview', true ) ) {
					if( $wcfm_products_single->post_status == 'publish' ) {
						echo '<a target="_blank" href="' . apply_filters( 'wcfm_product_preview_url', get_permalink( $wcfm_products_single->ID ) ) . '">';
						?>
						<span class="view_count"><span class="wcfmfa fa-eye text_tip" data-tip="<?php _e( 'Views', 'wc-frontend-manager' ); ?>"></span>
						<?php
						echo get_post_meta( $wcfm_products_single->ID, '_wcfm_product_views', true ) . '</span></a>';
					} else {
						echo '<a target="_blank" href="' . apply_filters( 'wcfm_product_preview_url', get_permalink( $wcfm_products_single->ID ) ) . '">';
						?>
						<span class="view_count"><span class="wcfmfa fa-eye text_tip" data-tip="<?php _e( 'Preview', 'wc-frontend-manager' ); ?>"></span>
						<?php
						echo '</a>';
					}
				}
			}
			
			do_action( 'before_wcfm_products_manage_action' );
			
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				if( $product_id ) {
					?>
					<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('post.php?post='.$product_id.'&action=edit'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
					<?php
				} else {
					?>
					<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('post-new.php?post_type=product'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
					<?php
				}
			}
			
			if( $has_new = apply_filters( 'wcfm_add_new_product_sub_menu', true ) ) {
				echo '<a id="add_new_product_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_edit_product_url().'" data-tip="' . __('Add New Product', 'wc-frontend-manager') . '"><span class="wcfmfa fa-cube"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager') . '</span></a>';
			}
			
			if( $product_id && !$wcfm_is_translated_product && apply_filters( 'wcfm_is_allow_duplicate_product', true ) && apply_filters( 'wcfm_is_allow_product_limit', true ) ) {
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					echo '<a id="wcfm_product_duplicate" class="wcfm_product_duplicate add_new_wcfm_ele_dashboard text_tip" href="#" data-proid="'. $product_id .'" data-tip="' . __('Duplicate Product', 'wc-frontend-manager') . '"><span class="wcfmfa fa-copy"></span><span class="text">' . __( 'Duplicate', 'wc-frontend-manager') . '</span></a>';
				}
			}
			
			if( $product_id && ( $wcfm_products_single->post_status == 'publish' ) ) {
				if( apply_filters( 'wcfm_is_allow_featured_product', true ) ) {
					if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
						if( has_term( 'featured', 'product_visibility', $wcfm_products_single->ID ) ) {
							echo '<a id="wcfm_product_featured" class="wcfm_product_featured add_new_wcfm_ele_dashboard text_tip" href="#" data-featured="nofeatured" data-proid="'. $product_id .'" data-tip="' . __('No Featured', 'wc-frontend-manager') . '"><span class="wcfmfa fa-star-of-life"></span><span class="text">' . __( 'No Featured', 'wc-frontend-manager') . '</span></a>';
						} else {
							if( apply_filters( 'wcfm_has_featured_product_limit', true ) ) {
								echo '<a id="wcfm_product_featured" class="wcfm_product_featured add_new_wcfm_ele_dashboard text_tip" href="#" data-featured="featured" data-proid="'. $product_id .'" data-tip="' . __('Mark Featured', 'wc-frontend-manager') . '"><span class="wcfmfa fa-star"></span><span class="text">' . __( 'Mark Featured', 'wc-frontend-manager') . '</span></a>';
							}
						}
					}
				}
			}
			
			do_action( 'after_wcfm_products_manage_action' );
			?>
			<div class="wcfm-clearfix"></div>
		</div>
		<div class="wcfm-clearfix"></div><br />
		
		<form id="wcfm_products_manage_form" class="wcfm">
		
			<?php do_action( 'begin_wcfm_products_manage_form' ); ?>
			
			<!-- collapsible -->
			<div class="wcfm-container simple variable external grouped booking">
				<div id="wcfm_products_manage_form_general_expander" class="wcfm-content">
				  <div class="wcfm_product_manager_general_fields">
				    <?php do_action( 'wcfm_product_manager_left_panel_before', $product_id ); ?>
				    
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_general', array(
																																																"product_type" => array('type' => 'select', 'options' => $product_types, 'class' => 'wcfm-select wcfm_ele wcfm_product_type wcfm_full_ele simple variable external grouped booking ' . $product_type_class . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $product_type ),
																																																"is_virtual" => array('desc' => __('Virtual', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple booking non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'desc_class' => 'wcfm_title wcfm_ele virtual_ele_title checkbox_title simple booking non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => 'enable', 'dfvalue' => $is_virtual),
																																																"is_downloadable" => array('desc' => __('Downloadable', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple appointment non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'desc_class' => 'wcfm_title wcfm_ele downloadable_ele_title checkbox_title simple appointment non-variable-subscription non-job_package non-resume_package non-redq_rental non-accommodation-booking non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => 'enable', 'dfvalue' => $is_downloadable),
																																																"pro_title" => array( 'placeholder' => __('Product Title', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_product_title wcfm_full_ele simple variable external grouped booking', 'value' => $title),
																																																//"visibility"     => array('label' => __('Visibility', 'wc-frontend-manager'), 'type' => 'select', 'options' => array('visible' => __('Catalog/Search', 'wc-frontend-manager'), 'catalog' => __('Catalog', 'wc-frontend-manager'), 'search' => __('Search', 'wc-frontend-manager'), 'hidden' => __('Hidden', 'wc-frontend-manager')), 'class' => 'wcfm-select wcfm_ele wcfm_half_ele wcfm_half_ele_right simple variable external', 'label_class' => 'wcfm_ele wcfm_half_ele_title wcfm_title simple variable external', 'value' => $visibility, 'hints' => __('Choose where this product should be displayed in your catalog. The product will always be accessible directly.', 'wc-frontend-manager'))
																																													), $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ) );
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_pricing', array(
																																																"product_url" => array('label' => __('URL', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide wcfm_half_ele external' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_ele_hide wcfm_half_ele_title wcfm_title external' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $product_url, 'hints' => __( 'Enter the external URL to the product.', 'wc-frontend-manager' )),
																																																"button_text" => array('label' => __('Button Text', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide wcfm_half_ele wcfm_half_ele_right external' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_ele_hide wcfm_half_ele_title wcfm_title external' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $button_text, 'hints' => __( 'This text will be shown on the button linking to the external product.', 'wc-frontend-manager' )),
																																																"regular_price" => array('label' => __('Price', 'wc-frontend-manager') . ' (' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_half_ele_title wcfm_title simple external non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $regular_price, 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ) ),
																																																"sale_price" => array('label' => __('Sale Price', 'wc-frontend-manager') . ' (' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm_half_ele wcfm_half_ele_right simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_half_ele_title wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $sale_price, 'desc_class' => 'wcfm_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card sales_schedule' . ' ' . $wcfm_wpml_edit_disable_element, 'desc' => __( 'schedule', 'wc-frontend-manager' ), 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ) ),
																																																"sale_date_from" => array('label' => __('From', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __('From', 'wc-frontend-manager') . '... YYYY-DD-MM', 'custom-attributes' => array( 'date_format' => 'yy-mm-dd' ), 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide wcfm_half_ele sales_schedule_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_ele_hide wcfm_half_ele_title sales_schedule_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $sale_date_from),
																																																"sale_date_upto" => array('label' => __('Upto', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __('To', 'wc-frontend-manager') . '... YYYY-DD-MM', 'custom-attributes' => array( 'date_format' => 'yy-mm-dd' ), 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide wcfm_half_ele sales_schedule_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_ele_hide wcfm_half_ele_title sales_schedule_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $sale_date_upto),
																																													), $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ) );		
							
						?>
						<div class="wcfm_clearfix"></div>
						
						<?php do_action( 'after_wcfm_products_manage_pricing_fields', $product_id ); ?>
						
						<?php if( !apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
							<?php do_action( 'before_wcfm_products_manage_taxonomies', $product_id ); ?>
						  <?php if( apply_filters( 'wcfm_is_allow_category', true ) && apply_filters( 'wcfm_is_allow_pm_category', true ) ) { ?>
						  	<?php if( apply_filters( 'wcfm_is_allow_product_category', true ) ) { $ptax_custom_arrtibutes = apply_filters( 'wcfm_taxonomy_custom_attributes', array(), 'product_cat' ); ?>
									<p class="wcfm_title"><strong><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( 'Categories', 'wc-frontend-manager' ), 'product_cat' ); ?></strong></p><label class="screen-reader-text" for="product_cats"><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( 'Categories', 'wc-frontend-manager' ), 'product_cat' ); ?></label>
									<select id="product_cats" name="product_cats[]" class="wcfm-select wcfm_ele simple variable external grouped booking" multiple="multiple" data-maximum-selection-length="<?php echo $catlimit; ?>" <?php echo implode( ' ', $ptax_custom_arrtibutes ); ?> style="width: 100%; margin-bottom: 10px;">
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
														<p class="wcfm_title taxonomy_<?php echo $product_taxonomy->name; ?>"><strong><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ); ?></strong></p><label class="screen-reader-text" for="<?php echo $product_taxonomy->name; ?>"><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ); ?></label>
														<select id="<?php echo $product_taxonomy->name; ?>" name="product_custom_taxonomies[<?php echo $product_taxonomy->name; ?>][]" class="wcfm-select product_taxonomies wcfm_ele simple variable external grouped booking" multiple="multiple" data-maximum-selection-length="<?php echo $taxonomy_limit; ?>" <?php echo implode( ' ', $ptax_custom_arrtibutes ); ?> style="width: 100%; margin-bottom: 10px;">
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
							
							if( $wcfm_is_allow_tags = apply_filters( 'wcfm_is_allow_tags', true ) ) {
								if( apply_filters( 'wcfm_is_tags_input', true ) ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_simple_fields_tag', array(  "product_tags" => array('label' => __('Tags', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'value' => $product_tags, 'placeholder' => __('Separate Product Tags with commas', 'wc-frontend-manager'), 'hints' => __( 'Product tags are descriptive labels you can add to your products. Popular search engines can use tags to get information about your store. You can add more than one tag separating them with a comma.', 'wc-frontend-manager' ), 'desc' => __( 'Choose from the most used tags', 'wc-frontend-manager' ), 'desc_class' => 'wcfm_full_ele wcfm_fetch_tag_cloud' )
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
						<?php } ?>
						
						
						
						<?php if( apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
							<div class="wcfm_clearfix"></div><br />
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
																																																			"excerpt" => array('label' => __('Short Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking ' . $rich_editor , 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'rows' => 5, 'value' => $excerpt, 'teeny' => true ),
																																																			"description" => array('label' => __('Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'rows' => 10, 'value' => $description),
																																																			"pro_id" => array('type' => 'hidden', 'value' => $product_id)
																																															), $product_id, $product_type ) );
								?>
								
								<?php do_action( 'wcfm_product_manager_left_panel_after', $product_id ); ?>
							</div>
						<?php } ?>
					</div>
					<div class="wcfm_product_manager_gallery_fields">
					  <?php do_action( 'wcfm_product_manager_right_panel_before', $product_id ); ?>
					  
					  <?php
					  if( $wcfm_is_allow_featured = apply_filters( 'wcfm_is_allow_featured', true ) ) {
					  	$gallerylimit = apply_filters( 'wcfm_gallerylimit', -1 );
					  	if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					  		//$gallerylimit = apply_filters( 'wcfm_free_gallerylimit', 4 );
					  	}
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_images', array(  "featured_img" => array( 'type' => 'upload', 'class' => 'wcfm-product-feature-upload wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'prwidth' => 250, 'value' => $featured_img),
																																																												"gallery_img"  => array( 'type' => 'multiinput', 'class' => 'wcfm-text wcfm-gallery_image_upload wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'custom_attributes' => array( 'limit' => $gallerylimit ), 'value' => $gallery_img_urls, 'options' => array(
																																																																									"gimage" => array( 'type' => 'upload', 'class' => 'wcfm_gallery_upload', 'prwidth' => 75 ),
																																																																								) )
																																													), $gallery_img_urls ) );
							
							do_action( 'wcfm_product_manager_gallery_fields_end', $product_id );
							
							// Product Gallary missing message
							if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
								if( apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
									//wcfmu_feature_help_text_show( __( 'Unlimited Image Gallery', 'wc-frontend-manager' ), false, true );
								}
							}
						}
						?>
					
						<?php if( apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
							<?php 
							if( apply_filters( 'wcfm_is_allow_category', true ) && apply_filters( 'wcfm_is_allow_pm_category', true ) ) {
								if( apply_filters( 'wcfm_is_allow_product_category', true ) ) {
									?>
									<div class="wcfm_clearfix"></div>
									<div class="wcfm_product_manager_cats_checklist_fields">
										<p class="wcfm_title wcfm_full_ele"><strong><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( 'Categories', 'wc-frontend-manager' ), 'product_cat' ); ?></strong></p><label class="screen-reader-text" for="product_cats"><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( 'Categories', 'wc-frontend-manager' ), 'product_cat' ); ?></label>
										<ul id="product_cats_checklist" class="product_taxonomy_checklist product_taxonomy_checklist_product_cat wcfm_ele simple variable external grouped booking" data-catlimit="<?php echo $catlimit; ?>">
											<?php
												if ( $product_categories ) {
													$WCFM->library->generateTaxonomyHTML( 'product_cat', $product_categories, $categories, '', true );
												}
											?>
										</ul>
									</div>
									<div class="wcfm_clearfix"></div>
								  <?php
								  if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
										if( apply_filters( 'wcfm_is_allow_add_category', true ) && apply_filters( 'wcfm_is_allow_add_taxonomy', true ) ) {
											?>
											<div class="wcfm_add_new_category_box wcfm_add_new_taxonomy_box">
												<p class="description wcfm_full_ele wcfm_side_add_new_category wcfm_add_new_category wcfm_add_new_taxonomy">+<?php _e( 'Add new category', 'wc-frontend-manager' ); ?></p>
												<div class="wcfm_add_new_taxonomy_form wcfm_add_new_taxonomy_form_hide">
													<?php 
													$WCFM->wcfm_fields->wcfm_generate_form_field( array( "wcfm_new_cat" => array( 'placeholder' => __( 'Category Name', 'wc-frontend-manager' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_new_tax_ele wcfm_full_ele' ) ) );
													if( apply_filters( 'wcfm_is_allow_add_new_product_cat_parent', true ) ) {
														$args = apply_filters( 'wcfm_wp_dropdown_categories_args', array(
																																														'show_option_all'    => '',
																																														'show_option_none'   => __( '-- Parent category --', 'wc-frontend-manager' ),
																																														'option_none_value'  => '0',
																																														'hide_empty'         => 0,
																																														'hierarchical'       => 1,
																																														'name'               => 'wcfm_new_parent_cat',
																																														'class'              => 'wcfm-select wcfm_new_parent_taxt_ele wcfm_full_ele',
																																														'taxonomy'           => 'product_cat',
																																													), 'product_cat' );
														wp_dropdown_categories( $args );
													}
													?>
													<button type="button" data-taxonomy="product_cat" class="button wcfm_add_category_bt wcfm_add_taxonomy_bt"><?php _e( 'Add', 'wc-frontend-manager' ); ?></button>
													<div class="wcfm_clearfix"></div>
												</div>
											</div>
											<div class="wcfm_clearfix"></div>
											<?php
										}
									}
								}
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
														
														$taxonomy_limit = apply_filters( 'wcfm_taxonomy_limit', -1, $product_taxonomy->name );
														?>
														<div class="wcfm_clearfix"></div>
														<div class="wcfm_product_manager_cats_checklist_fields wcfm_product_taxonomy_<?php echo $product_taxonomy->name; ?>">
															<p class="wcfm_title wcfm_full_ele"><strong><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ); ?></strong></p><label class="screen-reader-text" for="<?php echo $product_taxonomy->name; ?>"><?php echo apply_filters( 'wcfm_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ); ?></label>
															<ul id="<?php echo $product_taxonomy->name; ?>" class="product_taxonomy_checklist product_custom_taxonomy_checklist product_taxonomy_checklist_<?php echo $product_taxonomy->name; ?> wcfm_ele simple variable external grouped booking" data-catlimit="<?php echo $taxonomy_limit; ?>">
																<?php
																	$product_taxonomy_terms   = get_terms( $product_taxonomy->name, 'orderby=name&hide_empty=0&parent=0' );
																	if ( $product_taxonomy_terms ) {
																		$WCFM->library->generateTaxonomyHTML( $product_taxonomy->name, $product_taxonomy_terms, $taxonomy_values_arr, '', true, true );
																	}
																?>
															</ul>
														</div>
														<div class="wcfm_clearfix"></div>
														<?php
														if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
															if( apply_filters( 'wcfm_is_allow_add_taxonomy', true ) && apply_filters( 'wcfm_is_allow_product_add_taxonomy', true, $product_taxonomy->name ) && apply_filters( 'wcfm_is_allow_add_'.$product_taxonomy->name, true ) ) {
																?>
																<div class="wcfm_add_new_category_box wcfm_add_new_taxonomy_box">
																	<p class="description wcfm_full_ele wcfm_side_add_new_category wcfm_add_new_category wcfm_add_new_taxonomy">+<?php echo __( 'Add new', 'wc-frontend-manager' ) . ' ' . apply_filters( 'wcfm_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ); ?></p>
																	<div class="wcfm_add_new_taxonomy_form wcfm_add_new_taxonomy_form_hide">
																		<?php 
																		$WCFM->wcfm_fields->wcfm_generate_form_field( array( "wcfm_new_".$product_taxonomy->name => array( 'placeholder' =>  apply_filters( 'wcfm_add_taxonomy_custom_label', __( $product_taxonomy->label, 'wc-frontend-manager' ), $product_taxonomy->name ) . ' ' . __( 'Name', 'wc-frontend-manager' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_new_tax_ele wcfm_full_ele' ) ) );
																		if( apply_filters( 'wcfm_is_allow_add_new_'.$product_taxonomy->name.'_parent', true ) ) {
																			$args = apply_filters( 'wcfm_wp_dropdown_'.$product_taxonomy->name.'_args', array(
																										'show_option_all'    => '',
																										'show_option_none'   => __( '-- Parent taxonomy --', 'wc-frontend-manager' ),
																										'option_none_value'  => '0',
																										'hide_empty'         => 0,
																										'hierarchical'       => 1,
																										'name'               => 'wcfm_new_parent_'.$product_taxonomy->name,
																										'class'              => 'wcfm-select wcfm_new_parent_taxt_ele wcfm_full_ele',
																										'taxonomy'           => $product_taxonomy->name,
																									), $product_taxonomy->name );
																			wp_dropdown_categories( $args );
																		}
																		?>
																		<button type="button" data-taxonomy="<?php echo $product_taxonomy->name; ?>" class="button wcfm_add_category_bt wcfm_add_taxonomy_bt"><?php _e( 'Add', 'wc-frontend-manager' ); ?></button>
																		<div class="wcfm_clearfix"></div>
																	</div>
																</div>
																<div class="wcfm_clearfix"></div>
																<?php
															}
														}
													}
												}
											}
										}
									}
								}
							}
							
							if( apply_filters( 'wcfm_is_allow_tags', true ) ) {
								
								if( apply_filters( 'wcfm_is_tags_input', true ) ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_simple_fields_tag', array(  "product_tags" => array('label' => __('Tags', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele product_tags_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_full_ele product_tags_ele', 'value' => $product_tags, 'placeholder' => __('Separate Product Tags with commas', 'wc-frontend-manager'), 'hints' => __( 'Product tags are descriptive labels you can add to your products. Popular search engines can use tags to get information about your store. You can add more than one tag separating them with a comma.', 'wc-frontend-manager' ), 'desc' => __( 'Choose from the most used tags', 'wc-frontend-manager' ), 'desc_class' => 'wcfm_full_ele wcfm_side_tag_cloud wcfm_fetch_tag_cloud' )
																																															), $product_id, $product_type ) );
								} else {
									$product_all_tags = array();
									foreach( $product_defined_tags as $product_defined_tag) {
										$product_all_tags[$product_defined_tag->term_id] = $product_defined_tag->name;
									}
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_simple_fields_tag', array(  "product_tags" => array('label' => __('Tags', 'wc-frontend-manager') , 'type' => 'select', 'class' => 'wcfm-select wcfm_ele wcfm_full_ele product_tags_as_dropdown product_tags_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_full_ele product_tags_ele', 'value' => $product_tags, 'options' => $product_all_tags, 'attributes' => array( 'multiple' => true ), 'hints' => __( 'Product tags are descriptive labels you can add to your products. Popular search engines can use tags to get information about your store. You can add more than one tag separating them with a comma.', 'wc-frontend-manager' ) )
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
															$WCFM->wcfm_fields->wcfm_generate_form_field( array(  $product_taxonomy->name => array( 'label' => $product_taxonomy->label, 'name' => 'product_custom_taxonomies_flat[' . $product_taxonomy->name . '][]', 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $taxonomy_values, 'placeholder' => __('Separate Product ' . $product_taxonomy->label . ' with commas', 'wc-frontend-manager') )
																																					) );
														}
													}
												}
											}
										}
									}
								}
							?>
							
							<?php do_action( 'after_wcfm_products_manage_taxonomies', $product_id ); ?>
						<?php } ?>
						
						<?php do_action( 'wcfm_product_manager_right_panel_after', $product_id ); ?>
					</div>
				</div>
				
				<?php if( !apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
					<div class="wcfm-content">
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
																																																		"excerpt" => array('label' => __('Short Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking ' . $rich_editor , 'label_class' => 'wcfm_title wcfm_full_ele', 'rows' => 5, 'value' => $excerpt, 'teeny' => true),
																																																		"description" => array('label' => __('Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele', 'rows' => 10, 'value' => $description),
																																																		"pro_id" => array('type' => 'hidden', 'value' => $product_id)
																																														), $product_id, $product_type ) );
							?>
							
							<?php do_action( 'wcfm_product_manager_left_panel_after', $product_id ); ?>
						</div>
					</div>
				<?php } ?>
			</div>
			<!-- end collapsible -->
			<div class="wcfm_clearfix"></div><br />
			
			<!-- wrap -->
			<?php
			$wcfm_tab_class = '';
			if( $wcfm_is_translated_product ) {
				$wcfm_tab_class = 'wcfm_wpml_hide';
				?>
				<div class="wcfm-container simple variable external grouped booking">
					<p class="description" style="color:#f86c6b;font-size:15px;margin:10px 15px;width:100%;">
						<?php _e( 'It\'s a translated product, so product meta fields are blocked for editing because WPML will copy its value from the original language.', 'wc-frontend-manager' ); ?>
					</p>
				</div>
				<div class="wcfm_clearfix"></div><br />
				<?php
			}
			?>
			
			<?php do_action( 'wcfm_product_manager_before_tabs_area', $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
			
			<div class="wcfm-tabWrap">
			  <div class="wcfm-tabWrap-content <?php echo $wcfm_tab_class; ?>">
					<?php do_action( 'after_wcfm_products_manage_general', $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
				
					<?php include( 'wcfm-view-products-manage-tabs.php' ); ?>
					
					<?php do_action( 'end_wcfm_products_manage', $product_id, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
				</div>
			
				<?php do_action( 'after_wcfm_products_manage_tabs_content', $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
			</div> <!-- tabwrap -->
			
			<?php do_action( 'wcfm_product_manager_after_tabs_area', $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>
			
			<div id="wcfm_products_simple_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
			  <?php if( $product_id && !wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_publish_products', true ) && ( get_post_meta( $product_id, '_wcfm_review_product_notified', true ) ) ) { ?>
			    <input type="submit" name="submit-data" value="<?php _e( 'Approve', 'wc-frontend-manager' ); ?>" id="wcfm_products_simple_submit_button" class="wcfm_submit_button" />
			    <input type="submit" name="submit-data" value="<?php _e( 'Reject', 'wc-frontend-manager' ); ?>" id="wcfm_products_simple_reject_button" class="wcfm_submit_button" />
			  <?php } else { ?>
					<?php if( $product_id && ( $wcfm_products_single->post_status == 'publish' ) ) { ?>
						<input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_live_products', true ) ) { _e( 'Submit', 'wc-frontend-manager' ); } else { _e( 'Submit for Review', 'wc-frontend-manager' ); } ?>" id="wcfm_products_simple_submit_button" class="wcfm_submit_button" />
					<?php } else { ?>
						<?php if( apply_filters( 'wcfm_is_allow_product_limit', true ) && apply_filters( 'wcfm_is_allow_space_limit', true ) ) { ?>
							<input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_products', true ) ) { _e( 'Submit', 'wc-frontend-manager' ); } else { _e( 'Submit for Review', 'wc-frontend-manager' ); } ?>" id="wcfm_products_simple_submit_button" class="wcfm_submit_button" />
						<?php } ?>
					<?php } ?>
					<?php if( apply_filters( 'wcfm_is_allow_draft_published_products', true ) && apply_filters( 'wcfm_is_allow_add_products', true ) ) { ?>
						<input type="submit" name="draft-data" value="<?php _e( 'Draft', 'wc-frontend-manager' ); ?>" id="wcfm_products_simple_draft_button" class="wcfm_submit_button" />
					<?php } ?>
				<?php } ?>
				
				<?php
				if( apply_filters( 'wcfm_is_allow_product_preview', true ) ) {
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
				}
				?>
				<input type="hidden" name="wcfm_nonce" value="<?php echo wp_create_nonce( 'wcfm_products_manage' ); ?>" />
			</div>
		</form>
		<?php
		do_action( 'after_wcfm_products_manage' );
		?>
	</div>
</div>