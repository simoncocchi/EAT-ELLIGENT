<?php
/**
 * WCFM Marketplace plugin controllers
 *
 * Plugin Sell Items Catalog Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/controllers/product_multivendor
 * @version   3.1.0
 */

class WCFMmp_Sell_Items_Catalog_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
		
	}
	
	public function processing() {
		global $WCFM, $WCFMmp, $wpdb, $_POST;
		
		$length = sanitize_text_field($_POST['length']);
		$offset = sanitize_text_field($_POST['start']);
		
		if( class_exists('WooCommerce_simple_auction') ) {
			remove_all_filters( 'pre_get_posts' );
		}
		
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => 'product',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array('publish'),
							'suppress_filters' => 0 
						);
		$for_count_args = $args;
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			$args['s'] = sanitize_text_field($_POST['search']['value']);
		}
		
		if( isset($_POST['product_status']) && !empty($_POST['product_status']) ) $args['post_status'] = sanitize_text_field($_POST['product_status']);
  	
  	if( isset($_POST['product_type']) && !empty($_POST['product_type']) ) {
			if ( 'downloadable' == sanitize_text_field($_POST['product_type']) ) {
				$args['meta_value']    = 'yes';
				$args['meta_key']      = '_downloadable';
			} elseif ( 'virtual' == $_POST['product_type'] ) {
				$args['meta_value']    = 'yes';
				$args['meta_key']      = '_virtual';
			} elseif ( 'variable' == sanitize_text_field($_POST['product_type']) || 'simple' == sanitize_text_field($_POST['product_type']) ) {
				$args['tax_query'][] = array(
																		'taxonomy' => 'product_type',
																		'field' => 'slug',
																		'terms' => array(sanitize_text_field($_POST['product_type'])),
																		'operator' => 'IN'
																	);
			} else {
				$args['tax_query'][] = array(
																		'taxonomy' => 'product_type',
																		'field' => 'slug',
																		'terms' => array(sanitize_text_field($_POST['product_type'])),
																		'operator' => 'IN'
																	);
			}
		} else {
			$product_types = apply_filters( 'wcfm_product_types', array('simple' => __('Simple Product', 'wc-frontend-manager'), 'variable' => __('Variable Product', 'wc-frontend-manager'), 'grouped' => __('Grouped Product', 'wc-frontend-manager'), 'external' => __('External/Affiliate Product', 'wc-frontend-manager') ) );
			if( !empty( $product_types ) ) {
				$args['tax_query'][] = array(
																			'taxonomy' => 'product_type',
																			'field' => 'slug',
																			'terms' => array_keys( $product_types ),
																			'operator' => 'IN'
																		);
			}
		}
		
		if( isset($_POST['product_cat']) && !empty($_POST['product_cat']) ) {
			$args['tax_query'][] = array(
																		'taxonomy' => 'product_cat',
																		'field'    => 'term_id',
																		'terms'    => array(sanitize_text_field($_POST['product_cat'])),
																		'operator' => 'IN'
																	);
		}
		
		if( isset($_POST['product_taxonomy']) && !empty($_POST['product_taxonomy']) && is_array( $_POST['product_taxonomy'] ) ) {
			foreach( $_POST['product_taxonomy'] as $custom_taxonomy => $taxonomy_id ) {
				if( $taxonomy_id ) {
					$args['tax_query'][] = array(
																				'taxonomy' => $custom_taxonomy,
																				'field'    => 'term_id',
																				'terms'    => array($taxonomy_id),
																				'operator' => 'IN'
																			);
				}
			}
		}
		
		// Exclude Hidden Products
		if( apply_filters( 'wcfm_is_allow_exclude_hidden_products_from_add_to_my_store_catalog', true ) ) {
			$product_visibility_terms  = wc_get_product_visibility_term_ids();
			$product_visibility_not_in = array( $product_visibility_terms['exclude-from-search'], $product_visibility_terms['exclude-from-catalog'] );
			
			if( apply_filters( 'wcfm_is_allow_exclude_out_of_stock_products_from_add_to_my_store_catalog', true ) ) {
				if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
					$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
				}
			}
			
			if ( ! empty( $product_visibility_not_in ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_not_in,
					'operator' => 'NOT IN',
				);
			}
		}
		
		
		// Vendor Filter
		$args['author__not_in'] = array( $WCFMmp->vendor_id );
		
		// Exclude current vendor products
		$exclude = array();
		$more_offers = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` WHERE `vendor_id` = {$WCFMmp->vendor_id}" );
		foreach ($more_offers as $more_offer) {
			$exclude[] = $more_offer->parent_product_id;
		}
		$args['exclude'] = $exclude;
		
		// Order by Price
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 3 ) ) {
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = sanitize_text_field($_POST['order'][0]['dir']);
		}
		
		$args = apply_filters( 'wcfm_sell_items_catalog_args', $args );
		
		$wcfm_products_array = get_posts( $args );
		
		$pro_count = 0;
		$filtered_pro_count = 0;
		// Get Product Count
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$args['fields'] = 'ids';
		$wcfm_products_count_array = get_posts( $args );
		$filtered_pro_count = $pro_count = count( $wcfm_products_count_array );
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			unset( $args['s'] );
			unset( $args['fields'] );
			
			$search_ids = array();
			$terms      = explode( ',', sanitize_text_field($_POST['search']['value']) );
	
			foreach ( $terms as $term ) {
				if ( is_numeric( $term ) ) {
					$search_ids[] = $term;
				}
	
				// Attempt to get a SKU
				$sku_to_id = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_parent FROM {$wpdb->posts} LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE meta_key='_sku' AND meta_value LIKE %s;", '%' . $wpdb->esc_like( wc_clean( $term ) ) . '%' ) );
				$sku_to_id = array_merge( wp_list_pluck( $sku_to_id, 'ID' ), wp_list_pluck( $sku_to_id, 'post_parent' ) );
	
				if ( ( $sku_to_id != 0 ) && sizeof( $sku_to_id ) > 0 ) {
					$search_ids = array_merge( $search_ids, $sku_to_id );
				}
			}
			
			if( !empty( $search_ids ) ) {
				if( ( !is_array( $args['include'] ) && $args['include'] == '' ) || ( is_array($args['include']) && empty( $args['include'] ) ) ) {
					$args['include'] = $search_ids;
				} elseif( is_array($args['include']) && !empty( $args['include'] ) ) {
					$args['include'] = array_merge( $args['include'], $search_ids );
				}
			
				$wcfm_sku_search_products_array = get_posts( $args );
				
				if( count( $wcfm_sku_search_products_array ) > 0 ) {
					$wcfm_products_array = array_merge( $wcfm_products_array, $wcfm_sku_search_products_array );
					$wcfm_products_array = wcfm_unique_obj_list( $wcfm_products_array );
					$filtered_pro_count += count( $wcfm_products_array );
				}
			}
		}
		
		// Generate Products JSON
		$wcfm_products_json = '';
		$wcfm_products_json = '{
															"draw": ' . sanitize_text_field($_POST['draw']) . ',
															"recordsTotal": ' . $pro_count . ',
															"recordsFiltered": ' . $filtered_pro_count . ',
															"data": ';
		if(!empty($wcfm_products_array)) {
			$index = 0;
			$wcfm_products_json_arr = array();
			foreach($wcfm_products_array as $wcfm_products_single) {
				$the_product = wc_get_product( $wcfm_products_single );
				
				if( !is_a( $the_product, 'WC_Product' ) ) continue;
				
				// Multi select Action Checkbox
				$wcfm_products_json_arr[$index][] =  '<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_single" name="bulk_action_checkbox[]" value="' . $wcfm_products_single->ID . '" />';
				
				// Thumb
				$wcfm_products_json_arr[$index][] =  $the_product->get_image( 'thumbnail' );
				
				// Title
				$wcfm_products_json_arr[$index][] =  '<a target="_blank" href="' . get_permalink($wcfm_products_single->ID) . '" class="wcfm_product_title">' . $wcfm_products_single->post_title . '</a>';
				
				// Price
				$wcfm_products_json_arr[$index][] =  $the_product->get_price_html() ? $the_product->get_price_html() : '<span class="na">&ndash;</span>';
				
				// Taxonomies
				$taxonomies = '';
				$pcategories = get_the_terms( $the_product->get_id(), 'product_cat' );
				if( !empty($pcategories) ) {
					$taxonomies .= '<strong>' . __( 'Categories', 'wc-frontend-manager' ) . '</strong>: ';
					$is_first = true;
					foreach($pcategories as $pkey => $pcategory) {
						if( !$is_first ) $taxonomies .= ', ';
						$is_first = false;
						$taxonomies .= '<a style="color: #5B9A68" href="' . get_term_link( $pcategory->term_id ) . '" target="_blank">' . $pcategory->name . '</a>';
					}
				}
				
				// Custom Taxonomies
				$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
				if( !empty( $product_taxonomies ) ) {
					foreach( $product_taxonomies as $product_taxonomy ) {
						if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) ) {
							if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
								// Fetching Saved Values
								$taxonomy_values = get_the_terms( $the_product->get_id(), $product_taxonomy->name );
								if( !empty($taxonomy_values) ) {
									$taxonomies .= "<br /><strong>" . __( $product_taxonomy->label, 'wc-frontend-manager' ) . '</strong>: ';
									$is_first = true;
									foreach($taxonomy_values as $pkey => $ptaxonomy) {
										if( !$is_first ) $taxonomies .= ', ';
										$is_first = false;
										$taxonomies .= '<a style="color: #dd4b39;" href="' . get_term_link( $ptaxonomy->term_id ) . '" target="_blank">' . $ptaxonomy->name . '</a>';
									}
								}
							}
						}
					}
				}
				
				if( !$taxonomies ) $taxonomies = '&ndash;';
				$wcfm_products_json_arr[$index][] =  $taxonomies;
				
				// Store
				$vendor_name = '&ndash;';
				$vendor_id = wcfm_get_vendor_id_by_post( $wcfm_products_single->ID );
				$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( $vendor_id );
				if( $store_name ) {
					$vendor_name = $store_name;
				}
				$wcfm_products_json_arr[$index][] =  $vendor_name;
				
				// Additional Info
				$wcfm_products_json_arr[$index][] = apply_filters( 'wcfm_sell_items_catalog_additonal_data', '&ndash;', $wcfm_products_single->ID );
				
				// Action
				$actions  = '<a class="wcfm-action-icon" target="_blank" href="' . apply_filters( 'wcfm_product_preview_url', get_permalink( $wcfm_products_single->ID ) ) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wc-frontend-manager' ) . '"></span></a>';
				$actions .= '<br/><a class="wcfm_sell_this_item wcfm-action-icon text_tip" href="#" data-proid="' . $wcfm_products_single->ID . '" data-tip="' . esc_attr__( 'Click here add to your store', 'wc-multivendor-marketplace' ) . '"><span class="wcfmfa fa-hand-pointer"></span>&nbsp;<span class="">' . __( 'Add to My Store', 'wc-multivendor-marketplace' ) . '</span></a>';
					
				$wcfm_products_json_arr[$index][] =  apply_filters ( 'wcfm_sell_items_catalog_actions',  $actions, $the_product );
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_products_json_arr) ) $wcfm_products_json .= json_encode($wcfm_products_json_arr);
		else $wcfm_products_json .= '[]';
		$wcfm_products_json .= '
													}';
													
		echo $wcfm_products_json;
	}
}