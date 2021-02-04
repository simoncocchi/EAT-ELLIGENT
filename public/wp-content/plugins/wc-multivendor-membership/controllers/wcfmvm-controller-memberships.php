<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCFMvm Memberships Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/controllers
 * @version   1.0.0
 */

class WCFMvm_Memberships_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMvm;
		
		$length = $_POST['length'];
		$offset = $_POST['start'];
		
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
							'post_type'        => 'wcfm_memberships',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array('draft', 'pending', 'publish'),
							'suppress_filters' => true 
						);
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) $args['s'] = $_POST['search']['value'];
		
		$args = apply_filters( 'wcfm_vendor_memberships_args', $args );
		
		$wcfm_memberships_array = get_posts( $args );
		
		// Get Group Count
		$group_count = 0;
		$filtered_group_count = 0;
		$wcfm_memberships_count = wp_count_posts('wcfm_vendor_memberships');
		foreach($wcfm_memberships_count as $wcfm_membership_count ) {
			$group_count += $wcfm_membership_count;
		}
		// Get Filtered Post Count
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_filterd_memberships_array = get_posts( $args );
		$filtered_group_count = count($wcfm_filterd_memberships_array);
		$is_marketplace = wcfm_is_marketplace();
		
		// Generate Memberships JSON
		$wcfm_memberships_json = '';
		$wcfm_memberships_json = '{
															"draw": ' . $_POST['draw'] . ',
															"recordsTotal": ' . $group_count . ',
															"recordsFiltered": ' . $filtered_group_count . ',
															"data": ';
		if(!empty($wcfm_memberships_array)) {
			$index = 0;
			$wcfm_memberships_json_arr = array();
			foreach($wcfm_memberships_array as $wcfm_memberships_single) {
				// Group
				$wcfm_memberships_json_arr[$index][] =  '<a href="' . get_wcfm_memberships_manage_url($wcfm_memberships_single->ID) . '" class="wcfm_dashboard_item_title">' . $wcfm_memberships_single->post_title . '</a>';
				
				// Details
				$membership_details = __( 'Plan type: ', 'wc-multivendor-membership' );
				$subscription = (array) get_post_meta( $wcfm_memberships_single->ID, 'subscription', true );
				$membership_details .= isset( $subscription['is_free'] ) ? 'FREE' : '';
				if( !isset( $subscription['is_free'] ) ) {
					$membership_details .= isset( $subscription['subscription_type'] ) ? ucfirst( $subscription['subscription_type'] ) : __( 'One Time', 'wc-multivendor-membership' );
				}
				
				$commission = (array) get_post_meta( $wcfm_memberships_single->ID, 'commission', true );
				$commission_type = isset( $commission['type'] ) ? $commission['type'] : 'percent';
				$commission_value = isset( $commission['value'] ) ? $commission['value'] : '';
				if( $commission_type && $commission_value ) {
					$membership_details .= '<br />' . __( 'Commission: ', 'wc-multivendor-membership' ) . ucfirst($commission_type) . ' (' . $commission_value . ')';
				}
				
				$wcfm_memberships_json_arr[$index][] =  $membership_details;
				
				// Pay Mode
				$subscription_pay_mode = isset( $subscription['subscription_pay_mode'] ) ? $subscription['subscription_pay_mode'] : 'by_wcfm';
				$subscription_product = isset( $subscription['subscription_product'] ) ? $subscription['subscription_product'] : '';
				$pay_mods = array( 'by_wc' => __( 'WC Checkout', 'wc-multivendor-membership' ), 'by_wcfm' => __( 'Integrate Payment Options', 'wc-multivendor-membership' ) );
				$pay_mode = $pay_mods[$subscription_pay_mode];
				if( $subscription_pay_mode == 'by_wc' ) {
					$pay_mode .= '<br /><a style="color: #6BBA70;" target="_blank" href="' . get_wcfm_edit_product_url( $subscription_product ) . '"><span class="wcfmfa fa-cube"></span>&nbsp;' . get_the_title( $subscription_product ) . '</a>';
				}
				$wcfm_memberships_json_arr[$index][] =  $pay_mode;
				
				// Associated Group
				if( WCFM_Dependencies::wcfmgs_plugin_active_check() ) {
					$associated_group = get_post_meta( $wcfm_memberships_single->ID, 'associated_group', true );
					if( $associated_group ) {
						$wcfm_memberships_json_arr[$index][] =  '<span class="manager_count">' . get_the_title($associated_group) . '</span>';
					} else {
						$wcfm_memberships_json_arr[$index][] =  '<span class="manager_count">&ndash;</span>';
					}
				} else {
					$wcfm_memberships_json_arr[$index][] =  '<span class="manager_count">&ndash;</span>';
				}
				
				// User Count
				$membership_users = get_post_meta( $wcfm_memberships_single->ID, 'membership_users', true );
				if( $is_marketplace ) {
					if( $membership_users && is_array( $membership_users ) ) {
						$wcfm_memberships_json_arr[$index][] =  '<span class="vendor_count">' . apply_filters( 'wcfmvm_membership_vendor_count_display', count( $membership_users ), $wcfm_memberships_single->ID ) . '</span>';
					} else {
						$wcfm_memberships_json_arr[$index][] =  '<span class="vendor_count">&ndash;</span>';
					}
				} else {
					$wcfm_memberships_json_arr[$index][] =  '<span class="vendor_count">&ndash;</span>';
				}
				
				// Action
				$actions = '<a class="wcfm-action-icon" href="' . get_wcfm_memberships_manage_url($wcfm_memberships_single->ID) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>';
				if( $membership_users && is_array( $membership_users ) && ( count( $membership_users ) > 0 ) && apply_filters( 'wcfm_is_allow_block_membership_delete', false ) ) {
					$actions .= '<a class="wcfm_membership_restrict_delete wcfm-action-icon" href="#"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>';
				} else {
					$actions .= '<a class="wcfm_membership_delete wcfm-action-icon" href="#" data-membershipid="' . $wcfm_memberships_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>';
				}
				$wcfm_memberships_json_arr[$index][] = apply_filters ( 'wcfm_vendor_memberships_actions', $actions, $wcfm_memberships_single );
				
				$index++;
			}												
		}
		if( !empty($wcfm_memberships_json_arr) ) $wcfm_memberships_json .= json_encode($wcfm_memberships_json_arr);
		else $wcfm_memberships_json .= '[]';
		$wcfm_memberships_json .= '
													}';
													
		echo $wcfm_memberships_json;
	}
}