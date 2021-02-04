<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Review Submit Form Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/controllers/reviews
 * @version   1.0.0
 */

class WCFMmp_Reviews_Submit_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMmp, $wpdb, $_POST;
		
		$wcfm_store_review_data = array();
	  parse_str($_POST['wcfm_store_review_form'], $wcfm_store_review_data);
	  
	  $wcfm_reviews_messages = get_wcfm_reviews_messages();
	  $has_error = false;
	  
	  if(isset($wcfm_store_review_data['wcfmmp_store_review_comment']) && !empty($wcfm_store_review_data['wcfmmp_store_review_comment'])) {
	  	$wcfm_review_categories = get_wcfm_marketplace_active_review_categories();
	  	$review_auto_approve = isset( $WCFMmp->wcfmmp_review_options['review_auto_approve'] ) ? $WCFMmp->wcfmmp_review_options['review_auto_approve'] : 'no';
	  	
			$vendor_id      = absint($wcfm_store_review_data['wcfm_review_store_id']);
	  	$author_id      = absint($wcfm_store_review_data['wcfm_review_author_id']);
	  	
			$userdata = get_userdata( $author_id );
			$first_name = $userdata->first_name;
			$last_name  = $userdata->last_name;
			$display_name  = $userdata->display_name;
			if( $first_name ) {
				$author_name = $first_name . ' ' . $last_name;
			} else {
				$author_name = $display_name;
			}
			$author_email = $userdata->user_email;
			
			$review_title       = '';
			$review_description      = apply_filters( 'wcfm_editor_content_before_save', strip_tags( wcfm_stripe_newline( $wcfm_store_review_data['wcfmmp_store_review_comment'] ) ) );
			$review_description_mail = wp_unslash( $review_description );
			$review_description      = esc_sql( wp_unslash( $review_description ) );
			
			
			$review_rating = 0;
			$review_total  = 0;
			$wcfm_review_cat_count = count( $wcfm_review_categories );
			$wcfm_store_review_categories = $wcfm_store_review_data['wcfm_store_review_category'];
			foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
				if( isset( $wcfm_store_review_categories[$wcfm_review_cat_key] ) ) {
					$review_total += absint( $wcfm_store_review_categories[$wcfm_review_cat_key] );
				}
			}
			$review_rating = $review_total/$wcfm_review_cat_count;
			
			$approved = 0;
			if( $review_auto_approve == 'yes' ) $approved = 1;
			
			$current_time = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
	  	
	  	$wcfm_review_submit = "INSERT into {$wpdb->prefix}wcfm_marketplace_reviews 
														(`vendor_id`, `author_id`, `author_name`, `author_email`, `review_title`, `review_description`, `review_rating`, `approved`, `created`)
														VALUES
														({$vendor_id}, {$author_id}, '{$author_name}', '{$author_email}', '{$review_title}', '{$review_description}', '{$review_rating}', {$approved}, '{$current_time}')";
													
			$wpdb->query($wcfm_review_submit);
			$wcfm_review_id = $wpdb->insert_id;
			
			// Updating Review Meta
			foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
				if( isset( $wcfm_store_review_categories[$wcfm_review_cat_key] ) ) {
					$wcfm_review_meta_update = "INSERT into {$wpdb->prefix}wcfm_marketplace_review_rating_meta 
																			(`review_id`, `key`, `value`, `type`)
																			VALUES
																			({$wcfm_review_id}, '{$wcfm_review_category['category']}', '{$wcfm_store_review_categories[$wcfm_review_cat_key]}', 'rating_category')";
					$wpdb->query($wcfm_review_meta_update);
				}
			}
			
			// Update user review data
			if( $review_auto_approve == 'yes' ) {
				$total_review_count = get_user_meta( $vendor_id, '_wcfmmp_total_review_count', true );
				if( !$total_review_count ) $total_review_count = 0;
				else $total_review_count = absint( $total_review_count );
				$total_review_count++;
				update_user_meta( $vendor_id, '_wcfmmp_total_review_count', $total_review_count );
				
				$total_review_rating = get_user_meta( $vendor_id, '_wcfmmp_total_review_rating', true );
				if( !$total_review_rating ) $total_review_rating = 0;
				else $total_review_rating = (float) $total_review_rating;
				$total_review_rating += (float) $review_rating;
				update_user_meta( $vendor_id, '_wcfmmp_total_review_rating', $total_review_rating );
				
				$avg_review_rating = $total_review_rating/$total_review_count;
				update_user_meta( $vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating );
				
				$category_review_rating = get_user_meta( $vendor_id, '_wcfmmp_category_review_rating', true );
				if( !$category_review_rating ) $category_review_rating = array();
				foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
					if( isset( $wcfm_store_review_categories[$wcfm_review_cat_key] ) ) {
						$total_category_review_rating = 0;
						$avg_category_review_rating = 0;
						if( $category_review_rating && !empty( $category_review_rating ) && isset( $category_review_rating[$wcfm_review_cat_key] ) ) {
							$total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
							$avg_category_review_rating   = $category_review_rating[$wcfm_review_cat_key]['avg'];
						}
						$total_category_review_rating += (float) $wcfm_store_review_categories[$wcfm_review_cat_key];
						$avg_category_review_rating    = $total_category_review_rating/$total_review_count;
						$category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
						$category_review_rating[$wcfm_review_cat_key]['avg']   = $avg_category_review_rating;
					} else {
						$category_review_rating[$wcfm_review_cat_key]['total'] = 0;
						$category_review_rating[$wcfm_review_cat_key]['avg']   = 0;
					}
				}
				$category_review_rating = update_user_meta( $vendor_id, '_wcfmmp_category_review_rating', $category_review_rating );
				
				update_user_meta( $vendor_id, '_wcfmmp_last_author_id', $author_id );
				update_user_meta( $vendor_id, '_wcfmmp_last_author_name', $author_name );
			}
			
			
			
			// Direct message
			$wcfm_messages = sprintf( __( '%s has received a new Review from <b>%s</b>', 'wc-multivendor-marketplace' ), $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( $vendor_id ), $author_name );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'review' );
			
			// Vendor Direct message
			$wcfm_messages = sprintf( __( 'You have received a new Review from <b>%s</b>', 'wc-multivendor-marketplace' ), $author_name );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'review' );
			
			if( $review_auto_approve == 'yes' ) {
				echo '{"status": true, "message": "' . $wcfm_reviews_messages['review_published'] . '", "redirect" : true}';
			} else {
				echo '{"status": true, "message": "' . $wcfm_reviews_messages['review_saved'] . '", "redirect" : false}';
			}
		} else {
			echo '{"status": false, "message": "' . $wcfm_reviews_messages['no_comment'] . '"}';
		}
		
		die;
	}
}