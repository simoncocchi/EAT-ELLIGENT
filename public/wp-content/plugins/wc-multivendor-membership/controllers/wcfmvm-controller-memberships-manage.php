<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Memberships Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/controllers
 * @version   1.0.0
 */

class WCFMvm_Memberships_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $wcfm_membership_manager_form_data;
		
		$wcfm_membership_manager_form_data = array();
	  parse_str($_POST['wcfm_memberships_manage_form'], $wcfm_membership_manager_form_data);
	  
	  $wcfm_membership_messages = get_wcfmvm_membership_manage_messages();
	  $has_error = false;
	  
	  if(isset($wcfm_membership_manager_form_data['title']) && !empty($wcfm_membership_manager_form_data['title'])) {
	  	$is_update = false;
	  	$is_publish = false;
	  	$is_marketplace = wcfm_is_marketplace();
	  	$current_user_id = get_current_user_id();
	  	
	  	$membership_status = 'publish';
	  	
	  	// Creating new membership
			$new_membership = array(
				'post_title'   => wc_clean( $wcfm_membership_manager_form_data['title'] ),
				'post_status'  => $membership_status,
				'post_type'    => 'wcfm_memberships',
				'post_excerpt' => $wcfm_membership_manager_form_data['excerpt'],
				'post_author'  => $current_user_id
				//'post_name' => sanitize_title($wcfm_membership_manager_form_data['title'])
			);
			
			if(isset($wcfm_membership_manager_form_data['membership_id']) && $wcfm_membership_manager_form_data['membership_id'] == 0) {
				$new_membership_id = wp_insert_post( $new_membership, true );
			} else { // For Update
				$is_update = true;
				$new_membership['ID'] = $wcfm_membership_manager_form_data['membership_id'];
				$new_membership_id = wp_update_post( $new_membership, true );
			}
			
			if(!is_wp_error($new_membership_id)) {
				// For Update
				if($is_update) {
					$new_membership_id = $wcfm_membership_manager_form_data['membership_id'];
				
					// Remove Membership from old Vendors
					/*if( $is_marketplace ) {
						$old_membership_vendors = (array) get_post_meta( $new_membership_id, '_membership_vendors', true );
						if( !empty( $old_membership_vendors ) ) {
							foreach( $old_membership_vendors as $old_membership_vendor ) {
								$wcfm_vendor_memberships = (array) get_user_meta( $old_membership_vendor, '_wcfm_vendor_membership', true );
								if( !empty( $wcfm_vendor_memberships ) ) {
									if( ( $key = array_search( $new_membership_id, $wcfm_vendor_memberships ) ) !== false ) {
										unset( $wcfm_vendor_memberships[$key] );
									}
									update_user_meta( $old_membership_vendor, '_wcfm_vendor_membership', $wcfm_vendor_memberships );
								}
							}
						}
					}*/
					
				}
				
				// Update Membership Status
				if( isset( $wcfm_membership_manager_form_data['is_wcfm_membership_disable'] ) ) {
					update_post_meta( $new_membership_id, 'is_wcfm_membership_disable', 'yes' );
				} else {
					delete_post_meta( $new_membership_id, 'is_wcfm_membership_disable' );
				}
				
				// Hide from Membership Plan Table
				if( isset( $wcfm_membership_manager_form_data['is_wcfm_membership_plan_disable'] ) ) {
					update_post_meta( $new_membership_id, 'is_wcfm_membership_plan_disable', 'yes' );
				} else {
					delete_post_meta( $new_membership_id, 'is_wcfm_membership_plan_disable' );
				}
				
				// Update Subscribe Button
				if( isset( $wcfm_membership_manager_form_data['subscribe_button_label'] ) ) {
					update_post_meta( $new_membership_id, 'subscribe_button_label', $wcfm_membership_manager_form_data['subscribe_button_label'] );
				} else {
					update_post_meta( $new_membership_id, 'subscribe_button_label', __( "Subscribe Now", 'wc-multivendor-membership' ) );
				}
				
				// Update Membership Feature
				if( isset( $wcfm_membership_manager_form_data['features'] ) ) {
					update_post_meta( $new_membership_id, 'features', $wcfm_membership_manager_form_data['features'] );
				} else {
					update_post_meta( $new_membership_id, 'features', array() );
				}
				
				// Manage Subscription Pay Mode
				if( isset( $wcfm_membership_manager_form_data['subscription'] ) && isset( $wcfm_membership_manager_form_data['subscription']['subscription_pay_mode'] ) ) {
					$subscription_pay_mode = $wcfm_membership_manager_form_data['subscription']['subscription_pay_mode'];
					
					$subscription_product  = '';
					if( isset($wcfm_membership_manager_form_data['subscription']['subscription_product']) ) {
						$subscription_product  = $wcfm_membership_manager_form_data['subscription']['subscription_product'];
					}
					
					$wcfm_subcription_products = get_option( 'wcfm_subcription_products', array() );
					$old_subscription_product = get_post_meta( $new_membership_id, 'subscription_product', true );
					if( $old_subscription_product ) {
						delete_post_meta( $new_membership_id, 'subscription_product' );
						delete_post_meta( $old_subscription_product, '_wcfm_membership' );
						if( isset( $wcfm_subcription_products[$old_subscription_product] ) ) unset( $wcfm_subcription_products[$old_subscription_product] );
					}
					
					if( ($subscription_pay_mode == 'by_wc') && $subscription_product ) {
						update_post_meta( $new_membership_id, 'subscription_product', $subscription_product );
						update_post_meta( $subscription_product, '_wcfm_membership', $new_membership_id );
						update_post_meta( $subscription_product, '_sold_individually', 'yes'  );
						wp_set_object_terms( $subscription_product, array( 'exclude-from-search', 'exclude-from-catalog' ), 'product_visibility' );
						$wcfm_subcription_products[$subscription_product] = $subscription_product;
					} else {
						$wcfm_membership_manager_form_data['subscription']['subscription_pay_mode'] = 'by_wcfm';
					}
					update_option( 'wcfm_subcription_products', $wcfm_subcription_products );
				}
				
				// Update Membership Subscription
				if( isset( $wcfm_membership_manager_form_data['subscription'] ) ) {
					update_post_meta( $new_membership_id, 'subscription', $wcfm_membership_manager_form_data['subscription'] );
				} else {
					update_post_meta( $new_membership_id, 'subscription', array() );
				}
				
				// Membership Users
				$membership_users = (array) get_post_meta( $new_membership_id, 'membership_users', true );
				
				// Correct Membership User Count
				if( !empty( $membership_users ) && is_array( $membership_users ) ) {
					foreach( $membership_users as $member_id ) {
						$vendor_user = get_userdata( $member_id );
						$vendor_membership = get_user_meta( $member_id, 'wcfm_membership', true );
						if( !$vendor_user || !$vendor_membership || !wcfm_is_valid_membership( $vendor_membership ) || ( $vendor_membership != $new_membership_id ) || !wcfm_is_vendor( $member_id ) ) {
							if( ( $key = array_search( $member_id, $membership_users ) ) !== false ) {
								unset( $membership_users[$key] );
							}
						}
					}
					update_post_meta( $new_membership_id, 'membership_users', $membership_users );
				} else {
					$membership_users = array();
					update_post_meta( $new_membership_id, 'membership_users', $membership_users );
				}
				
				// Update Membership Commission
				if( isset( $wcfm_membership_manager_form_data['commission'] ) ) {
					$is_marketplace = wcfm_is_marketplace();
					
					// Update vendor specific commission setting by membership commission rulee, except WCFM Marketplace vendors
					if( $is_marketplace != 'wcfmmarketplace' ) {
						$old_commission = (array) get_post_meta( $new_membership_id, 'commission', true );
						$old_commission_type = isset( $old_commission['type'] ) ? $old_commission['type'] : '';
						$old_commission_value = isset( $old_commission['value'] ) ? $old_commission['value'] : '';
						
						$commission_type = isset( $wcfm_membership_manager_form_data['commission']['type'] ) ? $wcfm_membership_manager_form_data['commission']['type'] : 'percent';
						$commission_value = isset( $wcfm_membership_manager_form_data['commission']['value'] ) ? $wcfm_membership_manager_form_data['commission']['value'] : '';
						
						if( ( $commission_type != $old_commission_type ) || ( $commission_value != $old_commission_value ) ) {
							if( !empty( $membership_users ) ) {
								foreach( $membership_users as $member_id ) {
									if( $is_marketplace == 'wcmarketplace' ) {
										if( $commission_type ) {
											if( $commission_type == 'percent' ) {
												update_user_meta( $member_id, '_vendor_commission_percentage', $commission_value );
											} else {
												update_user_meta( $member_id, '_vendor_commission', $commission_value );
											}
										}
									} elseif( $is_marketplace == 'wcvendors' ) {
										if( $commission_type ) {
											update_user_meta( $member_id, 'pv_custom_commission_rate', $commission_value );
											if( $commission_type == 'percent' ) {
												update_user_meta( $member_id, '_wcv_commission_type', 'percent' );
												update_user_meta( $member_id, '_wcv_commission_percent', $commission_value );
											} else {
												update_user_meta( $member_id, '_wcv_commission_type', 'fixed' );
												update_user_meta( $member_id, '_wcv_commission_amount', $commission_value );
											}
										}
									} elseif( $is_marketplace == 'dokan' ) {
										if( $commission_type ) {
											if( $commission_type == 'percent' ) {
												update_user_meta( $member_id, 'dokan_admin_percentage_type', 'percentage' );
												update_user_meta( $member_id, 'dokan_admin_percentage', $commission_value );
											} else {
												update_user_meta( $member_id, 'dokan_admin_percentage_type', 'flat' );
												update_user_meta( $member_id, 'dokan_admin_percentage', $commission_value );
											}
										}
									} elseif( $is_marketplace == 'wcpvendors' ) {
										$vendor_id = get_user_meta( $member_id, '_wcpv_active_vendor', true );
										$vendor_data = get_term_meta( $vendor_id, 'vendor_data', true );
										$vendor_data['commission_type']      = ( $commission_type == 'percent' ) ? 'percentage' : 'fixed';
										$vendor_data['commission']           = $commission_value;
										update_term_meta( $vendor_id, 'vendor_data', $vendor_data );
									} elseif( $is_marketplace == 'wcfmmarketplace' ) {
										//$wcfmmp_profile_settings = get_user_meta( $member_id, 'wcfmmp_profile_settings', true );
										//$wcfmmp_profile_settings['commission'] = $wcfm_membership_manager_form_data['commission'];
										//update_user_meta( $member_id, 'wcfmmp_profile_settings', $wcfmmp_profile_settings );
									}
								}
							}
						}
					}
		
					update_post_meta( $new_membership_id, 'commission', $wcfm_membership_manager_form_data['commission'] );
				} else {
					update_post_meta( $new_membership_id, 'commission', array() );
				}
				
				// Update Membership Group
				if( isset( $wcfm_membership_manager_form_data['associated_group'] ) && !empty( $wcfm_membership_manager_form_data['associated_group'] ) ) {
					$old_associated_group = get_post_meta( $new_membership_id, 'associated_group', true );
					$old_group_vendors = array(); 
					if( $old_associated_group ) { $old_group_vendors = (array) get_post_meta( $old_associated_group, '_group_vendors', true );  }
						
					$associated_group = $wcfm_membership_manager_form_data['associated_group'];
					$group_vendors = (array) get_post_meta( $associated_group, '_group_vendors', true );
					
					if( $associated_group != $old_associated_group ) {
						if( !empty( $membership_users ) ) {
							foreach( $membership_users as $member_id ) {
								if( $old_associated_group && !empty( $old_group_vendors ) ) {
									if( ( $key = array_search( $member_id, $old_group_vendors ) ) !== false ) {
										unset( $old_group_vendors[$key] );
									}
								}
								$group_vendors[] = $member_id;
								
								$wcfm_vendor_groups = (array) get_user_meta( $member_id, '_wcfm_vendor_group', true );
								if( $old_associated_group ) {
									if( ( $key = array_search( $old_associated_group, $wcfm_vendor_groups ) ) !== false ) {
										unset( $wcfm_vendor_groups[$key] );
									}
								}
								$wcfm_vendor_groups[] = $associated_group;
								update_user_meta( $member_id, '_wcfm_vendor_group', $wcfm_vendor_groups  );
								update_user_meta( $member_id, '_wcfm_vendor_group_list', implode( ",", array_unique( $wcfm_vendor_groups ) ) );
							}
						}
						if( $old_associated_group ) {
							update_post_meta( $old_associated_group, '_group_vendors', $old_group_vendors );
						}
						update_post_meta( $associated_group, '_group_vendors', $group_vendors );
					}
					
					// Correct Associate Group Vendors Count
					$associated_group_vendors = get_post_meta( $associated_group, '_group_vendors', true );
					if( !empty( $associated_group_vendors ) && is_array( $associated_group_vendors ) ) {
						foreach( $associated_group_vendors as $member_id ) {
							$vendor_user = get_userdata( $member_id );
							if( !$vendor_user || !wcfm_is_vendor( $member_id ) ) {
								if( ( $key = array_search( $member_id, $associated_group_vendors ) ) !== false ) {
									unset( $associated_group_vendors[$key] );
								}
							}
						}
						update_post_meta( $associated_group, '_group_vendors', $associated_group_vendors );
					} else {
						$associated_group_vendors = array();
						update_post_meta( $associated_group, '_group_vendors', $associated_group_vendors );
					}
					
					update_post_meta( $new_membership_id, 'associated_group', $wcfm_membership_manager_form_data['associated_group'] );
				} else {
					update_post_meta( $new_membership_id, 'associated_group', '' );
				}
				
				// Memvership Subscription Required Admin Approval
				if( isset( $wcfm_membership_manager_form_data['required_approval'] ) ) {
					update_post_meta( $new_membership_id, 'required_approval', 'yes' );
				} else {
					update_post_meta( $new_membership_id, 'required_approval', 'no' );
				}
				
				// Memvership Application Reject Rule
				if( isset( $wcfm_membership_manager_form_data['vendor_reject_rule'] ) ) {
					update_post_meta( $new_membership_id, 'vendor_reject_rule', $wcfm_membership_manager_form_data['vendor_reject_rule'] );
				}
				
				// Thank You page content
				if( isset( $_POST['free_thankyou_content'] ) ) {
					wcfm_update_post_meta( $new_membership_id, 'free_thankyou_content', stripslashes( html_entity_decode( $_POST['free_thankyou_content'], ENT_QUOTES, 'UTF-8' ) ) );
				}
				
				// On Approval Thank You page content
				if( isset( $_POST['subscription_thankyou_content'] ) ) {
					wcfm_update_post_meta( $new_membership_id, 'subscription_thankyou_content', stripslashes( html_entity_decode( $_POST['subscription_thankyou_content'], ENT_QUOTES, 'UTF-8' ) ) );
				}
				
				// Welcome Email Subject
				if( isset( $wcfm_membership_manager_form_data['subscription_welcome_email_subject'] ) ) {
					wcfm_update_post_meta( $new_membership_id, 'subscription_welcome_email_subject', $wcfm_membership_manager_form_data['subscription_welcome_email_subject'] );
				}
				
				// Welcome Email Content
				if( isset( $_POST['subscription_welcome_email_content'] ) ) {
					wcfm_update_post_meta( $new_membership_id, 'subscription_welcome_email_content', stripslashes( html_entity_decode( $_POST['subscription_welcome_email_content'], ENT_QUOTES, 'UTF-8' ) ) );
				}
				
				// Hook for additional processing
				do_action( 'wcfm_memberships_manage_from_process', $new_membership_id, $wcfm_membership_manager_form_data );
				
				echo '{"status": true, "message": "' . $wcfm_membership_messages['membership_saved'] . '", "id": ' . $new_membership_id . '}';
				die;
			} else {
				echo '{"status": false, "message": "' . $wcfm_membership_messages['membership_failed'] . '"}';
			}
		} else {
			echo '{"status": false, "message": "' . $wcfm_membership_messages['no_title'] . '"}';
		}
		
		die;
	}
}