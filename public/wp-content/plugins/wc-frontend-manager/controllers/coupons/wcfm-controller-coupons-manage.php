<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Coupons Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   1.0.0
 */

class WCFM_Coupons_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $wcfm_coupon_manager_form_data;
		
		$wcfm_coupon_manager_form_data = array();
	  parse_str($_POST['wcfm_coupons_manage_form'], $wcfm_coupon_manager_form_data);
	  
	  $wcfm_coupon_messages = get_wcfm_coupons_manage_messages();
	  $has_error = false;
	  
	  if( !defined('WCFM_REST_API_CALL') ) {
	  	if( isset( $wcfm_coupon_manager_form_data['wcfm_nonce'] ) && !empty( $wcfm_coupon_manager_form_data['wcfm_nonce'] ) ) {
	  		if( !wp_verify_nonce( $wcfm_coupon_manager_form_data['wcfm_nonce'], 'wcfm_coupons_manage' ) ) {
	  			echo '{"status": false, "message": "' . __( 'Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager' ) . '"}';
	  			die;
	  		}
	  	}
	  }
	  
	  if(isset($wcfm_coupon_manager_form_data['title']) && !empty($wcfm_coupon_manager_form_data['title'])) {
	  	$is_update = false;
	  	$is_publish = false;
	  	$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	  	
	  	if( function_exists( 'wcfmmp_get_store_url' ) && !wcfm_is_vendor() ) {
	  		if( isset( $wcfm_coupon_manager_form_data['wcfm_vendor'] ) && !empty( $wcfm_coupon_manager_form_data['wcfm_vendor'] ) ) {
	  			$current_user_id = absint( $wcfm_coupon_manager_form_data['wcfm_vendor'] );
	  		}
	  	}
	  	
	  	// WCFM form custom validation filter
	  	$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_coupon_manager_form_data, 'coupon_manage' );
	  	if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
	  		$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
	  		if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
	  		echo '{"status": false, "message": "' . $custom_validation_error . '"}';
	  		die;
	  	}
	  	
	  	if(isset($_POST['status']) && ($_POST['status'] == 'draft')) {
	  		$coupon_status = 'draft';
	  	} else {
	  		if( current_user_can( 'publish_shop_coupons' ) && apply_filters( 'wcfm_is_allow_publish_coupons', true ) )
	  			$coupon_status = 'publish';
	  		else
	  		  $coupon_status = 'pending';
			}
	  	
	  	// Creating new coupon
			$new_coupon = apply_filters( 'wcfm_coupon_content_before_save', array(
																																					'post_title'   => wc_clean( $wcfm_coupon_manager_form_data['title'] ),
																																					'post_status'  => $coupon_status,
																																					'post_type'    => 'shop_coupon',
																																					'post_excerpt' => apply_filters( 'wcfm_editor_content_before_save', $wcfm_coupon_manager_form_data['description'] ),
																																					'post_author'  => $current_user_id,
																																					'post_name'    => sanitize_title($wcfm_coupon_manager_form_data['title'])
																																				), $wcfm_coupon_manager_form_data );
			
			if(isset($wcfm_coupon_manager_form_data['coupon_id']) && $wcfm_coupon_manager_form_data['coupon_id'] == 0) {
				if ($coupon_status != 'draft') {
					$is_publish = true;
				}
				$new_coupon_id = wp_insert_post( $new_coupon, true );
				
				// Coupon Real Author
				update_post_meta( $new_coupon_id, '_wcfm_coupon_author', get_current_user_id() );
			} else { // For Update
				$is_update = true;
				$new_coupon['ID'] = $wcfm_coupon_manager_form_data['coupon_id'];
				if( wcfm_is_marketplace() && ( !function_exists( 'wcfmmp_get_store_url' ) || wcfm_is_vendor() ) ) unset( $new_coupon['post_author'] );
				unset( $new_coupon['post_name'] );
				if( ($coupon_status != 'draft') && (get_post_status( $new_coupon['ID'] ) == 'publish') ) {
					if( apply_filters( 'wcfm_is_allow_publish_live_coupons', true ) ) {
						$new_coupon['post_status'] = 'publish';
					} else {
						$new_coupon['post_status'] = 'pending';
					}
				} else if( (get_post_status( $new_coupon['ID'] ) == 'draft') && ($coupon_status != 'draft') ) {
					$is_publish = true;
				}
				$new_coupon_id = wp_update_post( $new_coupon, true );
			}
			
			if(!is_wp_error($new_coupon_id)) {
				// For Update
				if($is_update) $new_coupon_id = $wcfm_coupon_manager_form_data['coupon_id'];
				
				
				// Check for dupe coupons
				$coupon_code  = wc_format_coupon_code( wc_clean( $wcfm_coupon_manager_form_data['title'] ) );
				$id_from_code = wc_get_coupon_id_by_code( $coupon_code, $new_coupon_id );
		
				if ( $id_from_code ) {
					if(!$is_update) {
						echo '{"status": false, "message": "' . __( 'Coupon code already exists - customers will use the latest coupon with this code.', 'woocommerce' ) . '", "id": "' . $new_coupon_id . '"}';
						$has_error = true;
					}
				}
				
				$wc_coupon = new WC_Coupon( $new_coupon_id );
				$wc_coupon->set_props( apply_filters( 'wcfm_coupon_data_factory', array(
					'code'                        => wc_clean( $wcfm_coupon_manager_form_data['title'] ),
					'discount_type'               => wc_clean( $wcfm_coupon_manager_form_data['discount_type'] ),
					'amount'                      => wc_format_decimal( $wcfm_coupon_manager_form_data['coupon_amount'] ),
					'date_expires'                => wcfm_standard_date( wc_clean( $wcfm_coupon_manager_form_data['expiry_date'] ) ),
					'free_shipping'               => isset( $wcfm_coupon_manager_form_data['free_shipping'] ),
				), $new_coupon_id, $wcfm_coupon_manager_form_data ) );
				
				if( wcfm_is_marketplace() && !WCFM_Dependencies::wcfmu_plugin_active_check() && ( wcfm_is_vendor() || ( function_exists( 'wcfmmp_get_store_url' ) && !wcfm_is_vendor() ) ) ) {
					$products_objs = $WCFM->wcfm_vendor_support->wcfm_get_products_by_vendor( $current_user_id, 'publish' );
					$product_ids = array( 0 => -1 );
					if( !empty($products_objs) ) {
						$product_ids = array();
						foreach( $products_objs as $products_obj ) {
							$product_ids[] = esc_attr( $products_obj->ID );
						}
					}
					update_post_meta( $new_coupon_id, '_wcfm_vendor_coupon_all_product', 'yes' );
					$wc_coupon->set_props( array( 'product_ids' => $product_ids ) );
				}
				
				$wc_coupon->save();
				
				// For Dokan Pro Only
				if( WCFM_Dependencies::dokanpro_plugin_active_check() || function_exists( 'wcfmmp_get_store_url' ) ) {
					if( isset( $wcfm_coupon_manager_form_data['show_on_store'] ) ) {
						update_post_meta( $new_coupon_id, 'show_on_store', 'yes' );
						
						// Smart Coupon Support
						if( apply_filters( 'wcfm_is_allow_added_coupon_as_global_coupon', true, $new_coupon_id ) ) {
							update_post_meta( $new_coupon_id, 'sc_is_visible_storewide', 'yes' );
							$global_coupons_list = get_option( 'sc_display_global_coupons' );
							$global_coupons      = ( ! empty( $global_coupons_list ) ) ? explode( ',', $global_coupons_list ) : array();
							$global_coupons[] = $new_coupon_id;
							update_option( 'sc_display_global_coupons', implode( ',', array_unique( $global_coupons ) ), 'no' );
						}
				
					} else {
						update_post_meta( $new_coupon_id, 'show_on_store', 'no' );
						
						// Smart Coupon Support
						if( apply_filters( 'wcfm_is_allow_added_coupon_as_global_coupon', true, $new_coupon_id ) ) {
							update_post_meta( $new_coupon_id, 'sc_is_visible_storewide', 'yes' );
							$global_coupons_list = get_option( 'sc_display_global_coupons' );
							$global_coupons      = ( ! empty( $global_coupons_list ) ) ? explode( ',', $global_coupons_list ) : array();
							$key                 = array_search( (string) $new_coupon_id, $global_coupons, true );
							if ( false !== $key ) {
								unset( $global_coupons[ $key ] );
								update_option( 'sc_display_global_coupons', implode( ',', array_unique( $global_coupons ) ), 'no' );
							}
						}
					}
				}
				
				// Hook for additional processing
				do_action( 'wcfm_coupons_manage_from_process', $new_coupon_id, $wcfm_coupon_manager_form_data );
				
				if(!$has_error) {
					if( get_post_status( $new_coupon_id ) == 'draft' ) {
						if(!$has_error) echo '{"status": true, "message": "' . $wcfm_coupon_messages['coupon_saved'] . '", "id": "' . $new_coupon_id . '"}';
					} else {
						if(!$has_error) echo '{"status": true, "message": "' . $wcfm_coupon_messages['coupon_published'] . '", "redirect": "' . get_wcfm_coupons_manage_url($new_coupon_id) . '"}';
					}
				}
				die;
			}
		} else {
			echo '{"status": false, "message": "' . $wcfm_coupon_messages['no_title'] . '"}';
		}
		die;
	}
}