<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCFM Marketplace Reviews Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/reviews/wcfmmp/controllers
 * @version   1.0.0
 */

class WCFMmp_Product_Reviews_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$vendor_id = $WCFMmp->vendor_id;
		
		$length = sanitize_text_field( $_POST['length'] );
		$offset = sanitize_text_field( $_POST['start'] );
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'comment_ID';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';
		
		$status_filter = 'all';
    if( isset($_POST['status_type']) && ( $_POST['status_type'] != '' ) ) {
    	$status_filter = sanitize_text_field( $_POST['status_type'] );
    	if( $status_filter == 'approved' ) {
    		$status_filter = 'approve';
    	} elseif( $status_filter == 'pending' ) {
    		$status_filter = 'hold';
    	}
    }
		
		$args = array(
			      'offset'    => $offset,
			      'number'    => $length,
						'orderby'   => $the_orderby,
						'order'     => $the_order,
						'status'    => $status_filter,
						'type'      => 'review'
					);
		
    $reviews_vendor_filter = '';
    if( wcfm_is_vendor() && $vendor_id ) {
			$args['post_author'] = $vendor_id;
		}  elseif ( ! empty( $_POST['reviews_vendor'] ) ) {
			$reviews_vendor = sanitize_text_field( $_POST['reviews_vendor'] );
			$args['post_author'] = $reviews_vendor;
		}
		
		if( !empty( $_POST['review_product'] ) ) {
			$args['post_id'] = absint( $_POST['review_product'] );
		}
    
		$wcfm_reviews_array = get_comments( $args );
		
		// Total Count Query 
		unset( $args['offset'] );
		unset( $args['number'] );
		$args['fields'] = 'ids';
		$wcfm_review_items = get_comments( $args );
		
		if( defined('WCFM_REST_API_CALL') ) {
			return $wcfm_reviews_array;
		}
		
		// Generate Reviews JSON
		$wcfm_reviews_json = '';
		$wcfm_reviews_json = '{
															"draw": ' . sanitize_text_field( $_POST['draw'] ) . ',
															"recordsTotal": ' . count( $wcfm_review_items ) . ',
															"recordsFiltered": ' . count( $wcfm_review_items ) . ',
															"data": ';
		if(!empty($wcfm_reviews_array)) {
			$index = 0;
			$wcfm_reviews_json_arr = array();
			foreach( $wcfm_reviews_array as $wcfm_review_single ) {
				
				//$wp_user_avatar_id = get_user_meta( $wcfm_review_single->author_id, 'wp_user_avatar', true );
				//$wp_user_avatar = wp_get_attachment_url( $wp_user_avatar_id );
				//if ( !$wp_user_avatar ) {
					//$wp_user_avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
				//}
				
				// Status
				if( $wcfm_review_single->comment_approved == 1 ) {
					$wcfm_reviews_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-completed text_tip" data-tip="' . __( 'Approved', 'wc-multivendor-marketplace') . '"></span>';
				} else {
					$wcfm_reviews_json_arr[$index][] =  '<span class="payment-status tips wcicon-status-pending text_tip" data-tip="' . __( 'Waiting Approval', 'wc-multivendor-marketplace') . '"></span>';
				}
				
        // Author
        $author = '<div class="wcfmmp-author-meta">' . $wcfm_review_single->comment_author;
        if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
        	$author .= '<br />' . $wcfm_review_single->comment_author_email;
        }
        $author .= '</div>';
        $wcfm_reviews_json_arr[$index][] = $author;
        
        // Comment
        $wcfm_reviews_json_arr[$index][] = '<div class="wcfmmp-comments-content">' . $wcfm_review_single->comment_content . '</div>';
        
        // Rating
        $review_rating =  intval( get_comment_meta( $wcfm_review_single->comment_ID, 'rating', true ) );
        $wcfm_reviews_json_arr[$index][] = '<div class="wcfmmp-rating"><div style="margin: auto;" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="' . sprintf( __( 'Rated %d out of 5', 'wc-multivendor-marketplace' ), $review_rating ) . '"><span style="width:' . ( ( $review_rating / 5 ) * 100 ) . '%"><strong itemprop="ratingValue">' . $review_rating . '</strong> ' . __( 'out of 5', 'wc-multivendor-marketplace' ). '</span></div></div>';
        
        // Product
        $wcfm_reviews_json_arr[$index][] = '<a target="_blank" href="' . get_permalink($wcfm_review_single->comment_post_ID) . '" class="wcfm_product_title">' . get_the_title($wcfm_review_single->comment_post_ID) . '</a>';
        
        // Store
				if( $wcfm_review_single->comment_post_ID ) {
					$store = wcfm_get_vendor_store_by_post( absint($wcfm_review_single->comment_post_ID) );
					if( $store ) {
						$wcfm_reviews_json_arr[$index][] = $store;
					} else {
						$wcfm_reviews_json_arr[$index][] = '&ndash;';
					}
				} else {
					$wcfm_reviews_json_arr[$index][] = '&ndash;';
				}
        
        // Dated
        $wcfm_reviews_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime($wcfm_review_single->comment_date) );
        
				// Status
				$actions = '';
				if( !wcfm_is_vendor() || apply_filters( 'wcfm_is_allow_manage_review', true ) ) {
					if( $wcfm_review_single->comment_approved == 1 ) {
						$actions .= '<a class="wcfm_review_status_update wcfm-action-icon" href="#" data-status="0" data-reviewid="' . $wcfm_review_single->comment_ID . '"><span class="wcfmfa fa-times-circle text_tip" data-tip="' . esc_attr__( 'Unapprove', 'wc-multivendor-marketplace' ) . '"></span></a>';
					} else {
						$actions .= '<a class="wcfm_review_status_update wcfm-action-icon" href="#" data-status="1" data-reviewid="' . $wcfm_review_single->comment_ID . '"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . esc_attr__( 'Approve', 'wc-multivendor-marketplace' ) . '"></span></a>';
					}
					if( !wcfm_is_vendor() || apply_filters( 'wcfm_is_allow_manage_review_delete', false ) ) {
						$actions .= '<a class="wcfm_review_dalete wcfm-action-icon" href="#" data-reviewid="' . $wcfm_review_single->comment_ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-multivendor-marketplace' ) . '"></span></a>';
					}
				} else {
					$actions = '&ndash;';
				}
				$wcfm_reviews_json_arr[$index][] =  $actions;
				
				$index++;
			}												
		}
		if( !empty($wcfm_reviews_json_arr) ) $wcfm_reviews_json .= json_encode($wcfm_reviews_json_arr);
		else $wcfm_reviews_json .= '[]';
		$wcfm_reviews_json .= '
													}';
													
		echo $wcfm_reviews_json;
	}
}