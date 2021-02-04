<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Listings Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   2.4.6
 */

class WCFM_Listings_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wcfm_listings_status = apply_filters( 'wcfm_listings_menus', array( 
																																					'publish' => __( 'Published', 'wc-frontend-manager'),
																																					'pending' => __( 'Pending', 'wc-frontend-manager'),
																																					'expired' => __( 'Expired', 'wc-frontend-manager'),
																																					'preview' => __( 'Preview', 'wc-frontend-manager'),
																																					'pending_payment' => __( 'Pending Payment', 'wc-frontend-manager')
																																				) );
		
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
							'post_type'        => 'job_listing',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array('draft', 'pending', 'publish', 'expired', 'pending_payment'),
							'suppress_filters' => 0 
						);
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) $args['s'] = wc_clean($_POST['search']['value']);
		
		if( isset($_POST['listing_status']) && !empty($_POST['listing_status']) && ( $_POST['listing_status'] != 'all' ) ) $args['post_status'] = wc_clean($_POST['listing_status']);
		
		$args = apply_filters( 'wcfm_listing_args', $args );
		
		if( isset($_POST['listing_vendor']) && !empty($_POST['listing_vendor']) ) {
			$is_marketplace = wcfm_is_marketplace();
			if( $is_marketplace ) {
				if( !wcfm_is_vendor() ) {
					$args['author'] = absint( $_POST['listing_vendor'] );
				}
			}
		}
		
		$wcfm_listings_array = get_posts( $args );
		
		// Get Filtered Post Count
		$filtered_listing_count = 0;
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_filterd_listings_array = get_posts( $args );
		$filtered_listing_count = count($wcfm_filterd_listings_array);
		
		$jobs_dashboard_url = get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) );
		
		
		// Generate Listings JSON
		$wcfm_listings_json = '';
		$wcfm_listings_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $filtered_listing_count . ',
															"recordsFiltered": ' . $filtered_listing_count . ',
															"data": ';
		if(!empty($wcfm_listings_array)) {
			$index = 0;
			$wcfm_listings_json_arr = array();
			foreach($wcfm_listings_array as $wcfm_listings_single) {
				
				// Listing
				if( $wcfm_allow_listings_edit = apply_filters( 'wcfm_is_allow_listings_edit', true ) ) {
					$wcfm_listings_json_arr[$index][] =  '<a target="_blank" href="' . add_query_arg( array( 'action' => 'edit', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ) . '" class="wcfm_listing_title">' . $wcfm_listings_single->post_title . '</a>';
				} else {
					$wcfm_listings_json_arr[$index][] =  '<span class="wcfm_dashboard_item_title">' . $wcfm_listings_single->post_title . '</span>';
				}
				
				// Store
				if( !wcfm_is_vendor() ) {
					$listing_author = $wcfm_listings_single->post_author;
					if( wcfm_is_vendor( $listing_author ) ) {
						$wcfm_listings_json_arr[$index][] = wcfm_get_vendor_store( $listing_author );
					} else {
						$wcfm_listings_json_arr[$index][] = '&ndash;';
					}
				} else {
					$wcfm_listings_json_arr[$index][] = '&ndash;';
				}
				
				// Status
				if( isset( $wcfm_listings_status[$wcfm_listings_single->post_status] ) ) {
					$wcfm_listings_json_arr[$index][] =  '<span class="listing-types listing-status-' . $wcfm_listings_single->post_status . '">' . $wcfm_listings_status[$wcfm_listings_single->post_status] . '</span>';
				} else {
					$wcfm_listings_json_arr[$index][] =  '<span class="listing-types listing-status-' . $wcfm_listings_single->post_status . '">' . __( ucfirst( $wcfm_listings_single->post_status ), 'wc-frontend-manager' ) . '</span>';
				}
				
				// Products
				if( WCFM_Dependencies::wcfmu_plugin_active_check() && WCFM_Dependencies::wcfm_products_listings_active_check() ) {
					$listing_product_list = '';
					$listing_products = (array) get_post_meta( $wcfm_listings_single->ID, '_products', true );
					if( !empty( $listing_products ) ) {
						$listing_products = array_unique( $listing_products );
						foreach( $listing_products as $listing_product ) {
							if( $listing_product_list ) $listing_product_list.= "<br/>";
							$listing_product_list .= '<a target="_blank" href="' . get_permalink( $listing_product ) . '">' . get_the_title( $listing_product ) . '</a>';
						}
					}
					if( !$listing_product_list ) $listing_product_list = '&ndash;';
					$wcfm_listings_json_arr[$index][] =  $listing_product_list;
				} else {
					$wcfm_listings_json_arr[$index][] =  '&ndash;';
				}
				
				// Applications
				if( WCFM_Dependencies::wcfm_wp_job_manager_applications_plugin_active_check() ) {
					$jobs_dashboard = get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) );
					if( $jobs_dashboard ) {
						$wcfm_listings_json_arr[$index][] =  ( $count = get_job_application_count( $wcfm_listings_single->ID ) ) ? '<a target="_permalink" class="application_count" href="' . add_query_arg( array( 'action' => 'show_applications', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard ) . '">' . $count . '</a>' : '&ndash;';
					} else { $wcfm_listings_json_arr[$index][] =  '&ndash;'; }
				} else { $wcfm_listings_json_arr[$index][] =  '&ndash;'; }
				
				// Filled?
				$wcfm_listings_json_arr[$index][] =  is_position_filled( $wcfm_listings_single ) ? '&#10004;' : '&ndash;';
				
				// Views
				$wcfm_listings_json_arr[$index][] =  '<span class="view_count">' . (int) get_post_meta( $wcfm_listings_single->ID, '_wcfm_listing_views', true ) . '</span>';
				
				// Date Posted
				$wcfm_listings_json_arr[$index][] = date_i18n( get_option( 'date_format' ), strtotime( $wcfm_listings_single->post_date ) );

				// Listing Expires
				$wcfm_listings_json_arr[$index][] = $wcfm_listings_single->_job_expires ? date_i18n( get_option( 'date_format' ), strtotime( $wcfm_listings_single->_job_expires ) ) : '&ndash;';
				
				// Additional Info
				$wcfm_listings_json_arr[$index][] = apply_filters( 'wcfm_listings_additonal_data', '&ndash;', $wcfm_listings_single->ID );

				// Action
				$actions = '';
				
				if ( in_array( $wcfm_listings_single->post_status, [ 'pending', 'pending_payment' ], true ) && !wcfm_is_vendor() ) {
					$actions .= '<a class="wcfm-action-icon" href="' . add_query_arg( array( 'action' => 'approve_listing', 'listing_id' => $wcfm_listings_single->ID ), WC()->ajax_url() ) . '"><span class="wcfmfa fa-check text_tip" data-tip="' . esc_attr__( 'Approve', 'wp-job-manager' ) . '"></span></a>';
				}
				
				$actions .= '<a target="_blank" class="wcfm-action-icon" href="' . get_permalink( $wcfm_listings_single->ID ) . '"><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wc-frontend-manager' ) . '"></span></a>';
				
				if( $wcfm_listings_single->post_status == 'publish' ) {
					// Edit
					if( $wcfm_allow_listings_edit = apply_filters( 'wcfm_is_allow_listings_edit', true ) ) {
						$actions .= '<a class="wcfm-action-icon" href="' . add_query_arg( array( 'action' => 'edit', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wc-frontend-manager' ) . '"></span></a>';
					}
				
					// Mark Filled
					if( $wcfm_allow_listings_mark_filled = apply_filters( 'wcfm_is_allow_listings_mark_filled', true ) ) {
						if ( is_position_filled( $wcfm_listings_single->ID ) ) {
							$actions .= '<a class="wcfm-action-icon" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'mark_not_filled', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ), 'job_manager_my_job_actions' ) . '"><span class="wcfmfa fa-times-circle text_tip" data-tip="' . esc_attr__( 'Mark not filled', 'wp-job-manager' ) . '"></span></a>';
						} else {
							$actions .= '<a class="wcfm-action-icon" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'mark_filled', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ), 'job_manager_my_job_actions' ) . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Mark filled', 'wp-job-manager' ) . '"></span></a>';
						}
					}
					
					// Featured
					if( apply_filters( 'wcfm_is_allow_featured_listing', true ) ) {
						if( get_post_meta( $wcfm_listings_single->ID, '_featured', true ) ) {
							$actions .= '<br/><a class="wcfm_listing_featured wcfm-action-icon" href="#" data-featured="nofeatured" data-listid="' . $wcfm_listings_single->ID . '"><span class="wcfmfa fa-star text_tip" style="font-weight:900;" data-tip="' . esc_attr__( 'No Featured', 'wc-frontend-manager' ) . '"></span></a>';
						} else {
							if( apply_filters( 'wcfm_has_featured_listing_limit', true ) ) {
								$actions .= '<br/><a class="wcfm_listing_featured wcfm-action-icon" href="#" data-featured="featured" data-listid="' . $wcfm_listings_single->ID . '"><span class="wcfmfa fa-star text_tip" data-tip="' . esc_attr__( 'Mark Featured', 'wc-frontend-manager' ) . '"></span></a>';
							}
						}
					}
					
					
					// Duplicate
					if( $wcfm_allow_listings_duplicate = apply_filters( 'wcfm_is_allow_listings_duplicate', true ) ) {
						$actions .= '<a target="_blank" class="wcfm-action-icon" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'duplicate', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ), 'job_manager_my_job_actions' ) . '"><span class="wcfmfa fa-copy text_tip" data-tip="' . esc_attr__( 'Duplicate', 'wp-job-manager' ) . '"></span></a>';
					}
				} else {
				
					// Continue Submission
					if( $wcfm_listings_single->post_status != 'expired' ) {
						$actions .= '<a class="wcfm-action-icon" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'continue', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ), 'job_manager_my_job_actions' ) . '"><span class="wcfmfa fa-edit text_tip" data-tip="' . esc_attr__( 'Continue Submission', 'wc-frontend-manager' ) . '"></span></a>';
					}
					
					// Relist
					if( $wcfm_allow_listings_relist = apply_filters( 'wcfm_is_allow_listings_relist', true ) ) {
						if( $wcfm_listings_single->post_status == 'expired' ) {
							if ( job_manager_get_permalink( 'submit_job_form' ) ) {
								$actions .= '<a class="wcfm-action-icon" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'relist', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ), 'job_manager_my_job_actions' ) . '"><span class="wcfmfa fa-retweet text_tip" data-tip="' . esc_attr__( 'Relist', 'wp-job-manager' ) . '"></span></a>';
							}
						}
					}
				}
				
				if( $wcfm_allow_listings_delete = apply_filters( 'wcfm_is_allow_listings_delete', true ) ) {
					$actions .= '<a class="wcfm-action-icon" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'job_id' => $wcfm_listings_single->ID ), $jobs_dashboard_url ), 'job_manager_my_job_actions' ) . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>';
				}
				
				
				$wcfm_listings_json_arr[$index][] = apply_filters ( 'wcfm_listings_actions', $actions, $wcfm_listings_single );
				
				$index++;
			}												
		}
		if( !empty($wcfm_listings_json_arr) ) $wcfm_listings_json .= json_encode($wcfm_listings_json_arr);
		else $wcfm_listings_json .= '[]';
		$wcfm_listings_json .= '
													}';
													
		echo $wcfm_listings_json;
	}
}