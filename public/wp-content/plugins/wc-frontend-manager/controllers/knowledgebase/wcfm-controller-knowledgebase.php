<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Knowledgebase Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   2.3.2
 */

class WCFM_Knowledgebase_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
		$allowed_post_status = array('draft', 'pending', 'publish');
		if( wcfm_is_vendor() ) $allowed_post_status = array( 'publish' );
			
		
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
							'post_type'        => 'wcfm_knowledgebase',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => $allowed_post_status,
							'suppress_filters' => 0 
						);
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) $args['s'] = wc_clean($_POST['search']['value']);
		
		if( isset($_POST['knowledgebase_cat']) && !empty($_POST['knowledgebase_cat']) ) {
			$args['tax_query'][] = array(
																		'taxonomy' => 'wcfm_knowledgebase_category',
																		'field' => 'term_id',
																		'terms' => array(wc_clean($_POST['knowledgebase_cat'])),
																		'operator' => 'IN'
																	);
		}
		
		$args = apply_filters( 'wcfm_knowledgebase_args', $args );
		
		$wcfm_knowledgebases_array = get_posts( $args );
		
		// Get Knowledgebase Count
		$knowledgebase_count = 0;
		$filtered_knowledgebase_count = 0;
		$wcfm_knowledgebases_count = wp_count_posts('wcfm_knowledgebase');
		$knowledgebase_count = ( $wcfm_knowledgebases_count->publish + $wcfm_knowledgebases_count->pending + $wcfm_knowledgebases_count->draft );
		// Get Filtered Post Count
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_filterd_knowledgebases_array = get_posts( $args );
		$filtered_knowledgebase_count = count($wcfm_filterd_knowledgebases_array);
		
		
		// Generate Knowledgebases JSON
		$wcfm_knowledgebases_json = '';
		$wcfm_knowledgebases_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $knowledgebase_count . ',
															"recordsFiltered": ' . $filtered_knowledgebase_count . ',
															"data": ';
		if(!empty($wcfm_knowledgebases_array)) {
			$index = 0;
			$wcfm_knowledgebases_json_arr = array();
			foreach($wcfm_knowledgebases_array as $wcfm_knowledgebases_single) {
				
				// Status
				if( !wcfm_is_vendor() ) {
					if( $wcfm_knowledgebases_single->post_status != 'publish' ) {
						$wcfm_knowledgebases_json_arr[$index][] = '<span class="order-status tips wcicon-status-cancelled text_tip" data-tip="' . __( 'OFF Line', 'wc-frontend-manager' ) . '"></span>';
					} else {
						$wcfm_knowledgebases_json_arr[$index][] = '<span class="order-status tips wcicon-status-completed text_tip" data-tip="' . __( 'ON Line', 'wc-frontend-manager' ) . '"></span>'; 
					}
				} else {
					$wcfm_knowledgebases_json_arr[$index][] =  '';
				}
				
				// Knowledgebase
				if( !wcfm_is_vendor() ) {
					$wcfm_knowledgebases_json_arr[$index][] =  '<a href="' . get_wcfm_knowledgebase_manage_url($wcfm_knowledgebases_single->ID) . '" class="wcfm_dashboard_item_title">' . $wcfm_knowledgebases_single->post_title . '</a>';
				} else {
					$wcfm_knowledgebases_json_arr[$index][] =  '<span class="wcfm_dashboard_item_title">' . $wcfm_knowledgebases_single->post_title . '</span>';
				}
				
				// Category
				$pcategories = get_the_terms( $wcfm_knowledgebases_single->ID, 'wcfm_knowledgebase_category' );
				if( !empty($pcategories) ) {
					$cat_list = ''; 
					foreach($pcategories as $pkey => $pcategory) {
						if( $cat_list ) $cat_list.= ', ';
						$cat_list .= __( $pcategory->name, 'wc-frontend-manager' );
					}
					$wcfm_knowledgebases_json_arr[$index][] = $cat_list;
				} else {
					$wcfm_knowledgebases_json_arr[$index][] = '&ndash;';
				}
				
				// Action
				$actions = '<a class="wcfm-action-icon wcfm_knowledgebase_view" href="#" data-knowledgebaseid="' . $wcfm_knowledgebases_single->ID . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wc-frontend-manager' ) . '"></span></a>';
				if( !wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_edit_knowledgebase', true ) ) {
					$actions .= '<a class="wcfm-action-icon" href="' . get_wcfm_knowledgebase_manage_url($wcfm_knowledgebases_single->ID) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>';
					if( $wcfm_knowledgebases_single->post_status != 'publish' ) {
						$actions .= '<a class="wcfm_knowledgebase_publish wcfm-action-icon" href="#" data-knowledgebaseid="' . $wcfm_knowledgebases_single->ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Publish - on line this now', 'wc-frontend-manager' ) . '"></span></a>';
					} else {
						$actions .= '<a class="wcfm_knowledgebase_archive wcfm-action-icon" href="#" data-knowledgebaseid="' . $wcfm_knowledgebases_single->ID . '"><span class="wcfmfa fa-times-circle text_tip" data-tip="' . esc_attr__( 'Archive - off line for the timing', 'wc-frontend-manager' ) . '"></span></a>';
					}
					$actions .= '<a class="wcfm_knowledgebase_delete wcfm-action-icon" href="#" data-knowledgebaseid="' . $wcfm_knowledgebases_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>';
				}
				$wcfm_knowledgebases_json_arr[$index][] = apply_filters ( 'wcfm_knowledgebase_actions', $actions, $wcfm_knowledgebases_single );
				
				$index++;
			}												
		}
		if( !empty($wcfm_knowledgebases_json_arr) ) $wcfm_knowledgebases_json .= json_encode($wcfm_knowledgebases_json_arr);
		else $wcfm_knowledgebases_json .= '[]';
		$wcfm_knowledgebases_json .= '
													}';
													
		echo $wcfm_knowledgebases_json;
	}
}