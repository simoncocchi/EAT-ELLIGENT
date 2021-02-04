<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Products Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   1.0.0
 */

class WCFM_Products_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
		
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wcfmu_products_status = apply_filters( 'wcfmu_products_menus', array(  
																																			'publish' => __( 'Published', 'wc-frontend-manager'),
																																			'draft' => __( 'Draft', 'wc-frontend-manager'),
																																			'pending' => __( 'Pending', 'wc-frontend-manager'),
																																			'archived' => __( 'Archived', 'wc-frontend-manager')
																																		) );
		
		$length = sanitize_text_field( $_POST['length'] );
		$offset = sanitize_text_field( $_POST['start'] );
		
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
							'post_status'      => array('draft', 'pending', 'publish', 'private', 'scheduled' ),
							'suppress_filters' => 0 
						);
		$for_count_args = $args;
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			$args['s'] = $_POST['search']['value'];
		}
		
		if( isset($_POST['product_status']) && !empty($_POST['product_status']) && ( $_POST['product_status'] != 'any' ) ) $args['post_status'] = sanitize_text_field( $_POST['product_status'] );
  	
  	if( isset($_POST['product_type']) && !empty($_POST['product_type']) ) {
			if ( 'downloadable' == $_POST['product_type'] ) {
				$args['meta_value']    = 'yes';
				$args['meta_key']      = '_downloadable';
			} elseif ( 'virtual' == $_POST['product_type'] ) {
				$args['meta_value']    = 'yes';
				$args['meta_key']      = '_virtual';
			} elseif ( 'variable' == $_POST['product_type'] || 'simple' == $_POST['product_type'] ) {
				$args['tax_query'][] = array(
																		'taxonomy' => 'product_type',
																		'field' => 'slug',
																		'terms' => array(wc_clean($_POST['product_type'])),
																		'operator' => 'IN'
																	);
			} else {
				$args['tax_query'][] = array(
																		'taxonomy' => 'product_type',
																		'field' => 'slug',
																		'terms' => array(wc_clean($_POST['product_type'])),
																		'operator' => 'IN'
																	);
			}
		}
		
		if( isset($_POST['product_cat']) && !empty($_POST['product_cat']) ) {
			$args['tax_query'][] = array(
																		'taxonomy' => 'product_cat',
																		'field'    => 'term_id',
																		'terms'    => array(wc_clean($_POST['product_cat'])),
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
		
		// Vendor Filter
		if( isset($_POST['product_vendor']) && !empty($_POST['product_vendor']) ) {
			$is_marketplace = wcfm_is_marketplace();
			if( $is_marketplace ) {
				if( !wcfm_is_vendor() ) {
					if( $is_marketplace == 'wcpvendors' ) {
						$args['tax_query'][] = array(
																					'taxonomy' => WC_PRODUCT_VENDORS_TAXONOMY,
																					'field' => 'term_id',
																					'terms' => wc_clean($_POST['product_vendor']),
																				);
					} elseif( $is_marketplace == 'wcvendors' ) {
						$args['author'] = $_POST['product_vendor'];
					} elseif( $is_marketplace == 'wcmarketplace' ) {
						$vendor_term = absint( get_user_meta( wc_clean($_POST['product_vendor']), '_vendor_term_id', true ) );
						$args['tax_query'][] = array(
																					'taxonomy' => 'dc_vendor_shop',
																					'field' => 'term_id',
																					'terms' => $vendor_term,
																				);
					} elseif( $is_marketplace == 'dokan' ) {
						$args['author'] = wc_clean($_POST['product_vendor']);
					} elseif( $is_marketplace == 'wcfmmarketplace' ) {
						$args['author'] = wc_clean($_POST['product_vendor']);
					}
				}
			}
		}
		
		// Order by SKU
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 3 ) ) {
			$args['meta_key'] = '_sku';
			$args['orderby']  = 'meta_value';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		// Order by Price
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 6 ) ) {
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		// Order by View Count
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 9 ) ) {
			$args['meta_key'] = '_wcfm_product_views';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		// Order by Date
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 10 ) ) {
			$args['orderby']  = 'date';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		$args = apply_filters( 'wcfm_products_args', $args );
		
		$wcfm_products_array = get_posts( $args );
		
		$pro_count = 0;
		$filtered_pro_count = 0;
		// Get Product Count
		$current_user_id  = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		if( !wcfm_is_vendor() ) $current_user_id = 0;
		$count_products = array();
		if( isset($_POST['product_status']) && !empty($_POST['product_status']) && ( $_POST['product_status'] != 'any' ) ) {
			$pro_count = wcfm_get_user_posts_count( $current_user_id, 'product', wc_clean($_POST['product_status']) );
		} else {
			$pro_count = wcfm_get_user_posts_count( $current_user_id, 'product', 'publish' );
			$pro_count += wcfm_get_user_posts_count( $current_user_id, 'product', 'pending' );
			$pro_count += wcfm_get_user_posts_count( $current_user_id, 'product', 'draft' );
			$pro_count += wcfm_get_user_posts_count( $current_user_id, 'product', 'private' );
		}
		
		// Get Filtered Post Count
		$filtered_pro_count = $pro_count; 
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			
			$args['posts_per_page'] = -1;
			$args['offset'] = 0;
			$args['fields'] = 'ids';
			
			$wcfm_products_count_array = get_posts( $args );
			$filtered_pro_count = $pro_count = count( $wcfm_products_count_array );
			
			unset( $args['s'] );
			unset( $args['fields'] );
			
			$search_ids = array();
			$terms      = explode( ',', wc_clean($_POST['search']['value']) );
	
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
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $pro_count . ',
															"recordsFiltered": ' . $filtered_pro_count . ',
															"data": ';
		if(!empty($wcfm_products_array)) {
			$index = 0;
			$wcfm_products_json_arr = array();
			foreach($wcfm_products_array as $wcfm_products_single) {
				$the_product = wc_get_product( $wcfm_products_single );
				
				if( !is_a( $the_product, 'WC_Product' ) ) continue;
				
				// Bulk Action Checkbox
				if( apply_filters( 'wcfm_is_allow_bulk_edit', true ) && WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					$wcfm_products_json_arr[$index][] =  '<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_single" name="bulk_action_checkbox[]" value="' . $wcfm_products_single->ID . '" />';
				} else {
					$wcfm_products_json_arr[$index][] =  '';
				}
				
				// Thumb
				if( ( ( $wcfm_products_single->post_status != 'publish' ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) || ( apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) ) {
					$wcfm_products_json_arr[$index][] =  '<a href="' . get_wcfm_edit_product_url($wcfm_products_single->ID, $the_product) . '">' . $the_product->get_image( 'thumbnail' ) . '</a>';
				} else {
					$wcfm_products_json_arr[$index][] =  $the_product->get_image( 'thumbnail' );
				}
				
				// Title
				if( ( ( $wcfm_products_single->post_status != 'publish' ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) || ( apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) ) {
					$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_product_title_dashboard', '<a href="' . get_wcfm_edit_product_url($wcfm_products_single->ID, $the_product) . '" class="wcfm_product_title">' . $wcfm_products_single->post_title . '</a>', $wcfm_products_single->ID );
				} else {
					$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_product_title_dashboard', $wcfm_products_single->post_title, $wcfm_products_single->ID );
				}
				
				// SKU
				$product_sku = ( get_post_meta($wcfm_products_single->ID, '_sku', true) ) ? get_post_meta( $wcfm_products_single->ID, '_sku', true ) : '-';
				$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_product_sku_dashboard', $product_sku, $wcfm_products_single->ID );
				
				// Status
				if( $wcfm_products_single->post_status == 'publish' ) {
					$wcfm_products_json_arr[$index][] =  '<span class="product-status product-status-' . $wcfm_products_single->post_status . '">' . __( 'Published', 'wc-frontend-manager' ) . '</span>';
				} else {
					if( isset( $wcfmu_products_status[$wcfm_products_single->post_status] ) ) {
						$wcfm_products_json_arr[$index][] =  '<span class="product-status product-status-' . $wcfm_products_single->post_status . '">' . $wcfmu_products_status[$wcfm_products_single->post_status] . '</span>';
					} else {
						$wcfm_products_json_arr[$index][] =  '<span class="product-status product-status-pending">' . __( ucfirst( $wcfm_products_single->post_status ), 'wc-frontend-manager' ) . '</span>';
					}
				}
				
				// Stock
				$stock_status = $the_product->get_stock_status();
				$stock_options = array('instock' => __('In stock', 'wc-frontend-manager'), 'outofstock' => __('Out of stock', 'wc-frontend-manager'), 'onbackorder' => __( 'On backorder', 'wc-frontend-manager' ) );
				if ( array_key_exists( $stock_status, $stock_options ) ) {
					$stock_html = '<span class="'.$stock_status.'">' . $stock_options[$stock_status] . '</span>';
				} else {
					$stock_html = '<span class="instock">' . __( 'In stock', 'woocommerce' ) . '</span>';
				}
		
				// If the product has children, a single stock level would be misleading as some could be -ve and some +ve, some managed/some unmanaged etc so hide stock level in this case.
				if ( $the_product->managing_stock() && ! sizeof( $the_product->get_children() ) ) {
					$stock_html .= ' (' . $the_product->get_stock_quantity() . ')';
				}
				$wcfm_products_json_arr[$index][] =  apply_filters( 'woocommerce_admin_stock_html', $stock_html, $the_product );
				
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
				if( apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
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
											if( !is_wp_error( $ptaxonomy ) ) {
												if( !$is_first ) $taxonomies .= ', ';
												$is_first = false;
												$taxonomies .= '<a style="color: #dd4b39;" href="' . get_term_link( $ptaxonomy->term_id ) . '" target="_blank">' . $ptaxonomy->name . '</a>';
											}
										}
									}
								}
							}
						}
					}
				}
				
				if( !$taxonomies ) $taxonomies = '&ndash;';
				$wcfm_products_json_arr[$index][] =  $taxonomies;
				
				// Type
				$pro_type = '';
				if ( 'grouped' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips grouped wcicon-grouped text_tip" data-tip="' . esc_attr__( 'Grouped', 'wc-frontend-manager' ) . '"></span>';
				} if ( 'groupby' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips grouped wcicon-grouped text_tip" data-tip="' . esc_attr__( 'Group By', 'wc-frontend-manager-product-hub' ) . '"></span>';
				} elseif ( 'external' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips external wcicon-external text_tip" data-tip="' . esc_attr__( 'External/Affiliate', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'simple' == $the_product->get_type() ) {
		
					if ( $the_product->is_virtual() ) {
						$pro_type = '<span class="product-type tips virtual wcicon-virtual text_tip" data-tip="' . esc_attr__( 'Virtual', 'wc-frontend-manager' ) . '"></span>';
					} elseif ( $the_product->is_downloadable() ) {
						$pro_type = '<span class="product-type tips downloadable wcicon-downloadable text_tip" data-tip="' . esc_attr__( 'Downloadable', 'wc-frontend-manager' ) . '"></span>';
					} else {
						$pro_type = '<span class="product-type tips simple wcicon-simple text_tip" data-tip="' . esc_attr__( 'Simple', 'wc-frontend-manager' ) . '"></span>';
					}
		
				} elseif ( 'variable' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips variable wcicon-variable text_tip" data-tip="' . esc_attr__( 'Variable', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'subscription' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcicon-variable text_tip" data-tip="' . esc_attr__( 'Subscription', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'variable-subscription' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcicon-variable text_tip" data-tip="' . esc_attr__( 'Variable Subscription', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'job_package' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-briefcase text_tip" data-tip="' . esc_attr__( 'Listings Package', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'resume_package' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-suitcase text_tip" data-tip="' . esc_attr__( 'Resume Package', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'auction' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-gavel text_tip" data-tip="' . esc_attr__( 'Auction', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'redq_rental' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-cab text_tip" data-tip="' . esc_attr__( 'Rental', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'booking' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-calendar text_tip" data-tip="' . esc_attr__( 'Booking', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'accommodation-booking' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-calendar text_tip" data-tip="' . esc_attr__( 'Accommodation', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'appointment' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-clock text_tip" data-tip="' . esc_attr__( 'Appointment', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'bundle' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-cubes text_tip" data-tip="' . esc_attr__( 'Bundle', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'composite' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-cubes text_tip" data-tip="' . esc_attr__( 'Composite', 'wc-frontend-manager' ) . '"></span>';
				} elseif ( 'lottery' == $the_product->get_type() ) {
					$pro_type = '<span class="product-type tips wcfmfa fa-dribbble text_tip" data-tip="' . esc_attr__( 'Lottery', 'wc-frontend-manager' ) . '"></span>';
				} else {
					// Assuming that we have other types in future
					$pro_type = '<span class="product-type tips wcicon-' . $the_product->get_type() . ' text_tip ' . $the_product->get_type() . '" data-tip="' . ucfirst( $the_product->get_type() ) . '"></span>';
				}
				$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_products_product_type_display', $pro_type, $the_product->get_type(), $the_product );
				
				// Views
				$wcfm_products_json_arr[$index][] =  '<span class="view_count">' . (int) get_post_meta( $wcfm_products_single->ID, '_wcfm_product_views', true ) . '</span>';
				
				// Date
				$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_products_date_display', date_i18n( wc_date_format(), strtotime($wcfm_products_single->post_date) ), $wcfm_products_single->ID, $the_product );
				
				// Vendor
				$vendor_name = '&ndash;';
				if( !$WCFM->is_marketplace || wcfm_is_vendor() ) {
					$wcfm_products_json_arr[$index][] =  $vendor_name;
				} else {
					$store_name = wcfm_get_vendor_store_by_post( $wcfm_products_single->ID );
					if( $store_name ) {
						$vendor_name = $store_name;
					}
					$wcfm_products_json_arr[$index][] =  $vendor_name;
				}
				
				// Additional Info
				$wcfm_products_json_arr[$index][] = apply_filters( 'wcfm_products_additonal_data', '&ndash;', $wcfm_products_single->ID );
				
				// Action
				$actions = '';
				
				if( $wcfm_products_single->post_status != 'publish' ) {
					if( !wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_publish_products', true ) ) {
						$actions .= '<a class="wcfm_product_approve wcfm-action-icon" href="#" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark Approve / Publish', 'wc-frontend-manager' ) . '"></span></a>';
						
						$wcfm_review_product_notified = get_post_meta( $wcfm_products_single->ID, '_wcfm_review_product_notified', true );
						if( $wcfm_review_product_notified ) {
							$actions .= '<a class="wcfm_product_reject wcfm-action-icon" href="#" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-times-circle text_tip" data-tip="' . esc_attr__( 'Mark Rejected', 'wc-frontend-manager' ) . '"></span></a>';	
						}
					}
				}
				
				if( apply_filters( 'wcfm_is_allow_view_product', true ) ) {
					$actions .= '<a class="wcfm-action-icon" target="_blank" href="' . apply_filters( 'wcfm_product_preview_url', get_permalink( $wcfm_products_single->ID ) ) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wc-frontend-manager' ) . '"></span></a>';
				}
				
				// Mark Featured - 3.0.1
				if( $wcfm_products_single->post_status == 'publish' ) {
					if( apply_filters( 'wcfm_is_allow_featured_product', true ) ) {
						if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
							if( has_term( 'featured', 'product_visibility', $wcfm_products_single->ID ) ) {
								$actions .= '<br/><a class="wcfm_product_featured wcfm-action-icon" href="#" data-featured="nofeatured" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-star-of-life text_tip" data-tip="' . esc_attr__( 'No Featured', 'wc-frontend-manager' ) . '"></span></a>';
							} else {
								if( apply_filters( 'wcfm_has_featured_product_limit', true ) ) {
									$actions .= '<br/><a class="wcfm_product_featured wcfm-action-icon" href="#" data-featured="featured" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-star text_tip" data-tip="' . esc_attr__( 'Mark Featured', 'wc-frontend-manager' ) . '"></span></a>';
								}
							}
						} else {
							if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
								$actions .= '<br/><a class="wcfm_product_dummy_featured wcfm-action-icon" href="#" onclick="return false;"><span class="wcfmfa fa-star-half-alt text_tip" data-tip="' . __( 'Featured Product: Upgrade your WCFM to WCFM Ultimate to avail this feature.', 'wc-frontend-manager' ) . '"></span></a>';
							}
						}
					}
				}
				
				// Duplicate - 2.5.2
				if( apply_filters( 'wcfm_is_allow_duplicate_product', true ) && apply_filters( 'wcfm_is_allow_product_limit', true ) ) {
					if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
						$actions .= '<a class="wcfm_product_duplicate wcfm-action-icon" href="#" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-copy text_tip" data-tip="' . esc_attr__( 'Duplicate', 'wc-frontend-manager' ) . '"></span></a>';
					} else {
						if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
							$actions .= '<a class="wcfm_product_dummy_duplicate wcfm-action-icon" href="#" onclick="return false;"><span class="wcfmfa fa-copy text_tip" data-tip="' . __( 'Duplicate Product: Upgrade your WCFM to WCFM Ultimate to avail this feature.', 'wc-frontend-manager' ) . '"></span></a>';
						}
					}
				}
				
				if( $wcfm_products_single->post_status == 'publish' ) {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) ? '<br/><a class="wcfm-action-icon" href="' . get_wcfm_edit_product_url($wcfm_products_single->ID, $the_product) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>' : '';
					
					// Archive Product - 6.2.5
					if( apply_filters( 'wcfm_is_allow_archive_product', true ) && apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) {
						$actions .= '<a class="wcfm_product_archive wcfm-action-icon" href="#" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-archive text_tip" data-tip="' . esc_attr__( 'Archive Product', 'wc-frontend-manager' ) . '"></span></a>';
					}
				
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_products', true ) && apply_filters( 'wcfm_is_allow_delete_specific_products', true, $wcfm_products_single->ID ) ) ? '<a class="wcfm-action-icon wcfm_product_delete" href="#" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>' : '';
				} else {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) ? '<br/><a class="wcfm-action-icon" href="' . get_wcfm_edit_product_url($wcfm_products_single->ID, $the_product) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>' : '';
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_specific_products', true, $wcfm_products_single->ID ) ) ? '<a class="wcfm_product_delete wcfm-action-icon" href="#" data-proid="' . $wcfm_products_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>' : '';
				}
				
				$wcfm_products_json_arr[$index][] =  apply_filters ( 'wcfm_products_actions',  $actions, $the_product );
				
				
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