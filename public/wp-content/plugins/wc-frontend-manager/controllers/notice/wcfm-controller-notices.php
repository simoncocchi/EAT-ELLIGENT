<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Notice Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   3.0.6
 */

class WCFM_Notices_Controller {
	
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
							'post_type'        => 'wcfm_notice',
							'post_mime_type'   => '',
							'post_parent'      => 0,
							//'author'	   => get_current_user_id(),
							'post_status'      => $allowed_post_status,
							'suppress_filters' => 0 
						);
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) $args['s'] = wc_clean($_POST['search']['value']);
		
		$args = apply_filters( 'wcfm_notice_args', $args );
		
		$wcfm_notices_array = get_posts( $args );
		
		// Get Notice Count
		$notice_count = 0;
		$filtered_notice_count = 0;
		$wcfm_notices_count = wp_count_posts('wcfm_notice');
		$notice_count = ( $wcfm_notices_count->publish + $wcfm_notices_count->pending + $wcfm_notices_count->draft );
		if( wcfm_is_vendor() ) { $notice_count = $wcfm_notices_count->publish; }
		// Get Filtered Post Count
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_filterd_notices_array = get_posts( $args );
		$filtered_notice_count = count($wcfm_filterd_notices_array);
		
		
		// Generate Notices JSON
		$wcfm_notices_json = '';
		$wcfm_notices_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $notice_count . ',
															"recordsFiltered": ' . $filtered_notice_count . ',
															"data": ';
		if(!empty($wcfm_notices_array)) {
			$index = 0;
			$wcfm_notices_json_arr = array();
			foreach($wcfm_notices_array as $wcfm_notices_single) {
				
				// Status
				if( !wcfm_is_vendor() ) {
					if( $wcfm_notices_single->post_status != 'publish' ) {
						$wcfm_notices_json_arr[$index][] = '<span class="order-status tips wcicon-status-cancelled text_tip" data-tip="' . __( 'OFF Line', 'wc-frontend-manager' ) . '"></span>';
					} else {
						$wcfm_notices_json_arr[$index][] = '<span class="order-status tips wcicon-status-completed text_tip" data-tip="' . __( 'ON Line', 'wc-frontend-manager' ) . '"></span>'; 
					}
				} else {
					$wcfm_notices_json_arr[$index][] = '';
				}
				
				// Notice
				$wcfm_notices_json_arr[$index][] =  '<a href="' . get_wcfm_notice_view_url($wcfm_notices_single->ID) . '" class="wcfm_dashboard_item_title">' . $wcfm_notices_single->post_title . '</a>';
				
				// Action
				$actions = '<a class="wcfm-action-icon" href="' . get_wcfm_notice_view_url($wcfm_notices_single->ID) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wc-frontend-manager' ) . '"></span></a>';
				
				if( current_user_can('administrator') ) {
					$actions .= '<a class="wcfm-action-icon" href="' . get_wcfm_notice_manage_url($wcfm_notices_single->ID) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>';
					if( $wcfm_notices_single->post_status != 'publish' ) {
						$actions .= '<a class="wcfm_notice_publish wcfm-action-icon" href="#" data-noticeid="' . $wcfm_notices_single->ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Publish - on line this now', 'wc-frontend-manager' ) . '"></span></a>';
					} else {
						$actions .= '<a class="wcfm_notice_archive wcfm-action-icon" href="#" data-noticeid="' . $wcfm_notices_single->ID . '"><span class="wcfmfa fa-times-circle text_tip" data-tip="' . esc_attr__( 'Archive - off line for the timing', 'wc-frontend-manager' ) . '"></span></a>';
					}
					$actions .= '<a class="wcfm_notice_delete wcfm-action-icon" href="#" data-noticeid="' . $wcfm_notices_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>';
				}
				
				
				$wcfm_notices_json_arr[$index][] = apply_filters ( 'wcfm_notice_actions', $actions, $wcfm_notices_single );
				
				$index++;
			}												
		}
		if( !empty($wcfm_notices_json_arr) ) $wcfm_notices_json .= json_encode($wcfm_notices_json_arr);
		else $wcfm_notices_json .= '[]';
		$wcfm_notices_json .= '
													}';
													
		echo $wcfm_notices_json;
	}
}