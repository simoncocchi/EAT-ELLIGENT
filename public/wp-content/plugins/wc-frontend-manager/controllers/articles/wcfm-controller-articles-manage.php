<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Articles Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   1.0.0
 */

class WCFM_Articles_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wcfm_articles_manage_form_data = array();
	  parse_str($_POST['wcfm_articles_manage_form'], $wcfm_articles_manage_form_data);
	  //print_r($wcfm_articles_manage_form_data);
	  $wcfm_articles_manage_messages = get_wcfm_articles_manager_messages();
	  $has_error = false;
	  
	  if( !defined('WCFM_REST_API_CALL') ) {
	  	if( isset( $wcfm_articles_manage_form_data['wcfm_nonce'] ) && !empty( $wcfm_articles_manage_form_data['wcfm_nonce'] ) ) {
	  		if( !wp_verify_nonce( $wcfm_articles_manage_form_data['wcfm_nonce'], 'wcfm_articles_manage' ) ) {
	  			echo '{"status": false, "message": "' . __( 'Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager' ) . '"}';
	  			die;
	  		}
	  	}
	  }
	  
	  if(isset($wcfm_articles_manage_form_data['title']) && !empty($wcfm_articles_manage_form_data['title'])) {
	  	$is_update = false;
	  	$is_publish = false;
	  	$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	  	
	  	if( function_exists( 'wcfmmp_get_store_url' ) && !wcfm_is_vendor() ) {
	  		if( isset( $wcfm_articles_manage_form_data['wcfm_vendor'] ) && !empty( $wcfm_articles_manage_form_data['wcfm_vendor'] ) ) {
	  			$current_user_id = absint( $wcfm_articles_manage_form_data['wcfm_vendor'] );
	  		}
	  	}
	  	
	  	// WCFM form custom validation filter
	  	$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_articles_manage_form_data, 'article_manage' );
	  	if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
	  		$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
	  		if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
	  		echo '{"status": false, "message": "' . $custom_validation_error . '"}';
	  		die;
	  	}
	  	                  
	  	if(isset($_POST['status']) && (wc_clean($_POST['status']) == 'draft')) {
	  		$article_status = 'draft';
	  	} else {
	  		if( apply_filters( 'wcfm_is_allow_publish_articles', true ) )
	  			$article_status = 'publish';
	  		else
	  		  $article_status = 'pending';
			}
	  	
	  	// Creating new article
			$new_article = apply_filters( 'wcfm_article_content_before_save', array(
				'post_title'   => wc_clean( $wcfm_articles_manage_form_data['title'] ),
				'post_status'  => $article_status,
				'post_type'    => 'post',
				'post_excerpt' => apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['excerpt'], ENT_QUOTES, 'UTF-8' ) ) ),
				'post_content' => apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['description'], ENT_QUOTES, 'UTF-8' ) ) ),
				'post_author'  => $current_user_id,
				'post_name' => sanitize_title($wcfm_articles_manage_form_data['title'])
			), $wcfm_articles_manage_form_data );
			
			if(isset($wcfm_articles_manage_form_data['article_id']) && $wcfm_articles_manage_form_data['article_id'] == 0) {
				if ($article_status != 'draft') {
					$is_publish = true;
				}
				$new_article_id = wp_insert_post( $new_article, true );
				
				// Article Real Author
				update_post_meta( $new_article_id, '_wcfm_article_author', get_current_user_id() );
			} else { // For Update
				$is_update = true;
				$new_article['ID'] = $wcfm_articles_manage_form_data['article_id'];
				if( wcfm_is_marketplace() && ( !function_exists( 'wcfmmp_get_store_url' ) || wcfm_is_vendor() ) ) unset( $new_article['post_author'] );
				unset( $new_article['post_name'] );
				if( ($article_status != 'draft') && (get_post_status( $new_article['ID'] ) == 'publish') ) {
					if( apply_filters( 'wcfm_is_allow_publish_live_articles', true ) ) {
						$new_article['post_status'] = 'publish';
					}
				} else if( (get_post_status( $new_article['ID'] ) == 'draft') && ($article_status != 'draft') ) {
					$is_publish = true;
				}
				$new_article_id = wp_update_post( $new_article, true );
			}
			
			if(!is_wp_error($new_article_id)) {
				// For Update
				if($is_update) $new_article_id = $wcfm_articles_manage_form_data['article_id'];
				
				// Set Article Category
				if(isset($wcfm_articles_manage_form_data['product_cats']) && !empty($wcfm_articles_manage_form_data['product_cats'])) {
					$is_first = true;
					foreach($wcfm_articles_manage_form_data['product_cats'] as $article_cats) {
						if($is_first) {
							$is_first = false;
							wp_set_object_terms( $new_article_id, (int)$article_cats, 'category' );
						} else {
							wp_set_object_terms( $new_article_id, (int)$article_cats, 'category', true );
						}
					}
				}
				
				// Set Article Custom Taxonomies
				if(isset($wcfm_articles_manage_form_data['article_custom_taxonomies']) && !empty($wcfm_articles_manage_form_data['article_custom_taxonomies'])) {
					foreach($wcfm_articles_manage_form_data['article_custom_taxonomies'] as $taxonomy => $taxonomy_values) {
						if( !empty( $taxonomy_values ) ) {
							$is_first = true;
							foreach( $taxonomy_values as $taxonomy_value ) {
								if($is_first) {
									$is_first = false;
									wp_set_object_terms( $new_article_id, (int)$taxonomy_value, $taxonomy );
								} else {
									wp_set_object_terms( $new_article_id, (int)$taxonomy_value, $taxonomy, true );
								}
							}
						}
					}
				}
				
				// Set Article Tags
				if(isset($wcfm_articles_manage_form_data['article_tags']) && !empty($wcfm_articles_manage_form_data['article_tags'])) {
					wp_set_post_terms( $new_article_id, $wcfm_articles_manage_form_data['article_tags'], 'post_tag' );
				}
				
				// Set Article Custom Taxonomies Flat
				if(isset($wcfm_articles_manage_form_data['article_custom_taxonomies_flat']) && !empty($wcfm_articles_manage_form_data['article_custom_taxonomies_flat'])) {
					foreach($wcfm_articles_manage_form_data['article_custom_taxonomies_flat'] as $taxonomy => $taxonomy_values) {
						if( !empty( $taxonomy_values ) ) {
							wp_set_post_terms( $new_article_id, $taxonomy_values, $taxonomy );
						}
					}
				}
				
				// Set Article Featured Image
				if(isset($wcfm_articles_manage_form_data['featured_img']) && !empty($wcfm_articles_manage_form_data['featured_img'])) {
					$featured_img_id = $WCFM->wcfm_get_attachment_id($wcfm_articles_manage_form_data['featured_img']);
					set_post_thumbnail( $new_article_id, $featured_img_id );
					wp_update_post( array( 'ID' => $featured_img_id, 'post_parent' => $new_article_id ) );
				} elseif(isset($wcfm_articles_manage_form_data['featured_img']) && empty($wcfm_articles_manage_form_data['featured_img'])) {
					delete_post_thumbnail( $new_article_id );
				}
				
				// Yoast SEO Support
				if( WCFM_Dependencies::wcfm_yoast_plugin_active_check() || WCFM_Dependencies::wcfm_yoast_premium_plugin_active_check() ) {
					if(isset($wcfm_articles_manage_form_data['yoast_wpseo_focuskw_text_input'])) {
						update_post_meta( $new_article_id, '_yoast_wpseo_focuskw_text_input', $wcfm_articles_manage_form_data['yoast_wpseo_focuskw_text_input'] );
						update_post_meta( $new_article_id, '_yoast_wpseo_focuskw', $wcfm_articles_manage_form_data['yoast_wpseo_focuskw_text_input'] );
					}
					if(isset($wcfm_articles_manage_form_data['yoast_wpseo_metadesc'])) {
						update_post_meta( $new_article_id, '_yoast_wpseo_metadesc', strip_tags( $wcfm_articles_manage_form_data['yoast_wpseo_metadesc'] ) );
					}
				}
				
				// All in One SEO Support
				if( WCFM_Dependencies::wcfm_all_in_one_seo_plugin_active_check() || WCFM_Dependencies::wcfm_all_in_one_seo_pro_plugin_active_check() ) {
					if(isset($wcfm_articles_manage_form_data['aiosp_title'])) {
						update_post_meta( $new_article_id, '_aioseop_title', $wcfm_articles_manage_form_data['aiosp_title'] );
						update_post_meta( $new_article_id, '_aioseop_description', $wcfm_articles_manage_form_data['aiosp_description'] );
					}
				}
				
				// Rank Math SEO Support
				if( WCFM_Dependencies::wcfm_rankmath_seo_plugin_active_check() ) {
					if(isset($wcfm_articles_manage_form_data['rank_math_focus_keyword'])) {
						update_post_meta( $new_article_id, 'rank_math_focus_keyword', $wcfm_articles_manage_form_data['rank_math_focus_keyword'] );
						update_post_meta( $new_article_id, 'rank_math_description', $wcfm_articles_manage_form_data['rank_math_description'] );
					}
				}
				
				do_action( 'after_wcfm_articles_manage_meta_save', $new_article_id, $wcfm_articles_manage_form_data );
				
				// Notify Admin on New Article Creation
				if( $is_publish ) {
					// Have to test before adding action
				} 
				
				if(!$has_error) {
					if( get_post_status( $new_article_id ) == 'publish' ) {
						if( !apply_filters( 'wcfm_is_allow_edit_articles', true ) ) {
						 if(!$has_error) echo '{"status": true, "message": "' . apply_filters( 'article_published_message', $wcfm_articles_manage_messages['article_published'], $new_article_id ) . '", "redirect": "' . apply_filters( 'wcfm_article_save_publish_redirect', get_permalink( $new_article_id ), $new_article_id ) . '", "id": "' . $new_article_id . '", "title": "' . get_the_title( $new_article_id ) . '"}';
						} else {
							if(!$has_error) echo '{"status": true, "message": "' . apply_filters( 'article_published_message', $wcfm_articles_manage_messages['article_published'], $new_article_id ) . '", "redirect": "' . apply_filters( 'wcfm_article_save_publish_redirect', get_wcfm_articles_manage_url( $new_article_id ), $new_article_id ) . '", "id": "' . $new_article_id . '", "title": "' . get_the_title( $new_article_id ) . '"}';
						}
					} elseif( get_post_status( $new_article_id ) == 'pending' ) {
						if(!$has_error) echo '{"status": true, "message": "' . apply_filters( 'article_pending_message', $wcfm_articles_manage_messages['article_pending'], $new_article_id ) . '", "redirect": "' . apply_filters( 'wcfm_article_save_pending_redirect', get_wcfm_articles_manage_url( $new_article_id ), $new_article_id ) . '", "id": "' . $new_article_id . '", "title": "' . get_the_title( $new_article_id ) . '"}';
					} else {
						if(!$has_error) echo '{"status": true, "message": "' . apply_filters( 'article_saved_message', $wcfm_articles_manage_messages['article_saved'], $new_article_id ) . '", "redirect": "' . apply_filters( 'wcfm_article_save_draft_redirect', get_wcfm_articles_manage_url( $new_article_id ), $new_article_id ) . '", "id": "' . $new_article_id . '"}';
					}
				}
				die;
			}
		} else {
			echo '{"status": false, "message": "' . $wcfm_articles_manage_messages['no_title'] . '"}';
		}
	  die;
	}
}