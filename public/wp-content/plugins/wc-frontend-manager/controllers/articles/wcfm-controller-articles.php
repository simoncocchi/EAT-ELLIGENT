<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Articles Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/articles
 * @version   3.4.6
 */

class WCFM_Articles_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
		
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
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
							'post_type'        => 'post',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array('draft', 'pending', 'publish'),
							'suppress_filters' => 0 
						);
		$for_count_args = $args;
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			$args['s'] = wc_clean($_POST['search']['value']);
		}
		
		if( isset($_POST['article_status']) && !empty($_POST['article_status']) ) $args['post_status'] = $_POST['article_status'];
  	
		if( isset($_POST['article_cat']) && !empty($_POST['article_cat']) ) {
			$args['tax_query'][] = array(
																		'taxonomy' => 'category',
																		'field' => 'term_id',
																		'terms' => array(wc_clean($_POST['article_cat'])),
																		'operator' => 'IN'
																	);
		}
		
		// Vendor Filter
		if( isset($_POST['article_vendor']) && !empty($_POST['article_vendor']) ) {
			$is_marketplace = wcfm_is_marketplace();
			if( $is_marketplace ) {
				if( !wcfm_is_vendor() ) {
					$args['author'] = wc_clean($_POST['article_vendor']);
				}
			}
		}
		
		$args = apply_filters( 'wcfm_articles_args', $args );
		
		$wcfm_articles_array = get_posts( $args );
		
		$article_count = 0;
		$filtered_article_count = 0;
		if( wcfm_is_vendor() ) {
			// Get Article Count
			$for_count_args['posts_per_page'] = -1;
			$for_count_args['offset'] = 0;
			$for_count_args = apply_filters( 'wcfm_articles_args', $for_count_args );
			$wcfm_articles_count = get_posts( $for_count_args );
			$article_count = count($wcfm_articles_count);
			
			// Get Filtered Post Count
			$args['posts_per_page'] = -1;
			$args['offset'] = 0;
			$wcfm_filterd_articles_array = get_posts( $args );
			$filtered_article_count = count($wcfm_filterd_articles_array);
		} else {
			// Get Article Count
			$wcfm_articles_counts = wp_count_posts('post');
			foreach($wcfm_articles_counts as $wcfm_articles_type => $wcfm_articles_count ) {
				if( in_array( $wcfm_articles_type, array( 'publish', 'draft', 'pending' ) ) ) {
					$article_count += $wcfm_articles_count;
				}
			}
			
			// Get Filtered Post Count
			$filtered_article_count = $article_count; 
		}
		
		// Generate Articles JSON
		$wcfm_articles_json = '';
		$wcfm_articles_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $article_count . ',
															"recordsFiltered": ' . $filtered_article_count . ',
															"data": ';
		if(!empty($wcfm_articles_array)) {
			$index = 0;
			$wcfm_articles_json_arr = array();
			foreach($wcfm_articles_array as $wcfm_articles_single) {
				
				// Thumb
				if( apply_filters( 'wcfm_is_allow_edit_articles', true ) ) {
					$wcfm_articles_json_arr[$index][] =  '<a href="' . get_wcfm_articles_manage_url( $wcfm_articles_single->ID ) . '"><img width="40" height="40" class="attachment-thumbnail size-thumbnail wp-post-image" src="' . get_the_post_thumbnail_url( $wcfm_articles_single->ID ) . '" /></a>';
				} else {
					$wcfm_articles_json_arr[$index][] =  '<img width="40" height="40" class="attachment-thumbnail size-thumbnail wp-post-image" src="' . get_the_post_thumbnail_url( $wcfm_articles_single->ID ) . '" />';
				}
				
				// Title
				if( apply_filters( 'wcfm_is_allow_edit_articles', true ) ) {
					$wcfm_articles_json_arr[$index][] =  apply_filters( 'wcfm_article_title_dashboard', '<a href="' . get_wcfm_articles_manage_url( $wcfm_articles_single->ID ) . '" class="wcfm_article_title wcfm_dashboard_item_title">' . $wcfm_articles_single->post_title . '</a>', $wcfm_articles_single->ID );
				} else {
					if( $wcfm_articles_single->post_status == 'publish' ) {
						$wcfm_articles_json_arr[$index][] =  apply_filters( 'wcfm_article_title_dashboard', $wcfm_articles_single->post_title, $wcfm_articles_single->ID );
					} elseif( apply_filters( 'wcfm_is_allow_edit_articles', true ) ) {
						$wcfm_articles_json_arr[$index][] =  apply_filters( 'wcfm_article_title_dashboard', '<a href="' . get_wcfm_articles_manage_url( $wcfm_articles_single->ID ) . '" class="wcfm_article_title wcfm_dashboard_item_title">' . $wcfm_articles_single->post_title . '</a>', $wcfm_articles_single->ID );
					} else {
						$wcfm_articles_json_arr[$index][] =  apply_filters( 'wcfm_article_title_dashboard', $wcfm_articles_single->post_title, $wcfm_articles_single->ID );
					}
				}
				
				// Status
				if( $wcfm_articles_single->post_status == 'publish' ) {
					$wcfm_articles_json_arr[$index][] =  '<span class="article-status article-status-' . $wcfm_articles_single->post_status . '">' . __( 'Published', 'wc-frontend-manager' ) . '</span>';
				} else {
					$wcfm_articles_json_arr[$index][] =  '<span class="article-status article-status-' . $wcfm_articles_single->post_status . '">' . __( ucfirst( $wcfm_articles_single->post_status ), 'wc-frontend-manager' ) . '</span>';
				}
				
				// Views
				$wcfm_articles_json_arr[$index][] =  '<span class="view_count">' . (int) get_post_meta( $wcfm_articles_single->ID, '_wcfm_article_views', true ) . '</span>';
				
				// Date
				$wcfm_articles_json_arr[$index][] =  date_i18n( wc_date_format(), strtotime($wcfm_articles_single->post_date) );
				
				// Author
				if( !wcfm_is_vendor() ) {
					if( wcfm_is_vendor( $wcfm_articles_single->post_author ) ) {
						$wcfm_articles_json_arr[$index][] = wcfm_get_vendor_store_by_post( $wcfm_articles_single->ID );
					} else {
						$author = get_user_by( 'id', $wcfm_articles_single->post_author );
						if( $author ) {
							$wcfm_articles_json_arr[$index][] =  $author->display_name;
						} else {
							$wcfm_articles_json_arr[$index][] =  '&ndash;';
						}
					}
				} else {
					$wcfm_articles_json_arr[$index][] = '&ndash;';
				}
				
				// Action
				$actions = '<a class="wcfm-action-icon" target="_blank" href="' . get_permalink( $wcfm_articles_single->ID ) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wc-frontend-manager' ) . '"></span></a>';
				
				if( $wcfm_articles_single->post_status == 'publish' ) {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_articles', true ) ) ? '<a class="wcfm-action-icon" href="' . get_wcfm_articles_manage_url( $wcfm_articles_single->ID ) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>' : '';
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_articles', true ) ) ? '<a class="wcfm-action-icon wcfm_article_delete" href="#" data-articleid="' . $wcfm_articles_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>' : '';
				} else {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_articles', true ) ) ? '<a class="wcfm-action-icon" href="' . get_wcfm_articles_manage_url( $wcfm_articles_single->ID ) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>' : '';
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_articles', true ) ) ? '<a class="wcfm_article_delete wcfm-action-icon" href="#" data-articleid="' . $wcfm_articles_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>' : '';
				}
				
				$wcfm_articles_json_arr[$index][] =  apply_filters ( 'wcfm_articles_actions',  $actions, $wcfm_articles_single );
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_articles_json_arr) ) $wcfm_articles_json .= json_encode($wcfm_articles_json_arr);
		else $wcfm_articles_json .= '[]';
		$wcfm_articles_json .= '
													}';
													
		echo $wcfm_articles_json;
	}
}