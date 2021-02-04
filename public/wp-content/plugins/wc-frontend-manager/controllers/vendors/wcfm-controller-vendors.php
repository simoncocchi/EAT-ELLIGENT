<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Vendors Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/vendors/controllers
 * @version   3.2.1
 */

class WCFM_Vendors_Controller {
	
	public function __construct() {
		global $WCFM;
		
		if( !defined('WCFM_REST_API_CALL') ) {
			$this->processing();
		}
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
		$report_for = '7day';
		if( isset($_POST['report_for']) && !empty($_POST['report_for']) ) {
			$report_for = wc_clean($_POST['report_for']);
		}
		
		$filter_date_form = '';
		if( isset($_POST['filter_date_form']) && !empty($_POST['filter_date_form']) ) {
			$filter_date_form = wc_clean($_POST['filter_date_form']);
		}
		
		$filter_date_to = '';
		if( isset($_POST['filter_date_to']) && !empty($_POST['filter_date_to']) ) {
			$filter_date_to = wc_clean($_POST['filter_date_to']);
		}
		
		if( $filter_date_form && $filter_date_to ) {
			$report_for = 'custom';
		}
		
		$search_vendor = '';
		if ( ! empty( $_POST['report_vendor'] ) ) {
			$search_vendor = absint( $_POST['report_vendor'] );
		}
		
		$report_membership = '';
		if ( ! empty( $_POST['report_membership'] ) ) {
			$report_membership = absint( $_POST['report_membership'] );
			
			if( !$search_vendor ) {
				$search_vendor = (array) get_post_meta( $report_membership, 'membership_users', true );
			}
		}
		
		$vendor_search_data = array();
		if ( isset( $_POST['search_data'] ) && ! empty( $_POST['search_data'] ) ) {
			$vendor_search_data = $_POST['search_data'];
		}
		
		$wcfm_vendors_array = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list( true, $offset, $length, '', $search_vendor, true, $vendor_search_data );
		unset($wcfm_vendors_array[0]);
		
		// Get Vendor Count
		$wcfm_all_vendors = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list( true );
		unset($wcfm_all_vendors[0]);
		
		// Get Filtered Vendor Count
		$wcfm_filtered_vendors = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list( true, '', '', '', $search_vendor, true, $vendor_search_data );
		unset($wcfm_filtered_vendors[0]);
		
		$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );
		
		if( defined('WCFM_REST_API_CALL') ) {
			return $wcfm_vendors_array;
		}
		
		// Generate Vendors JSON
		$wcfm_vendors_json = '';
		$wcfm_vendors_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . count( $wcfm_all_vendors ) . ',
															"recordsFiltered": ' . count( $wcfm_filtered_vendors ) . ',
															"data": ';
		if(!empty($wcfm_vendors_array)) {
			$index = 0;
			$wcfm_vendors_json_arr = array();
			foreach($wcfm_vendors_array as $wcfm_vendors_id => $wcfm_vendors_name ) {
				
				// Status
				$disable_vendor = get_user_meta( $wcfm_vendors_id, '_disable_vendor', true );
				$is_store_offline = get_user_meta( $wcfm_vendors_id, '_wcfm_store_offline', true );
				if( $is_store_offline ) {
					$wcfm_vendors_json_arr[$index][] = '<span class="order-status tips wcfmfa fa-power-off text_tip" style="color: #ff9310;" data-tip="' . __( 'Store Off-line', 'wc-frontend-manager' ) . '"></span>';
				} else {
					if( $disable_vendor ) {
						$wcfm_vendors_json_arr[$index][] = '<span class="order-status tips wcicon-status-cancelled text_tip" data-tip="' . __( 'Disable Vendor', 'wc-frontend-manager' ) . '"></span>';
					} else {
						$wcfm_vendors_json_arr[$index][] = '<span class="order-status tips wcicon-status-completed text_tip" data-tip="' . __( 'Active Vendor', 'wc-frontend-manager' ) . '"></span>'; 
					}
				}
				
				// Verification
				$verification_label = '';
				if( apply_filters( 'wcfm_is_allow_email_verification', true ) ) {
					$email_verified = false;
					$vendor_user = get_userdata( $wcfm_vendors_id );
					$user_email = $vendor_user->user_email;
					$email_verified = get_user_meta( $wcfm_vendors_id, '_wcfm_email_verified', true );
					$wcfm_email_verified_for = get_user_meta( $wcfm_vendors_id, '_wcfm_email_verified_for', true );
					if( $email_verified && ( $user_email != $wcfm_email_verified_for ) ) $email_verified = false;
					if( $email_verified ) {
						$verification_label .= '&nbsp;<span class="wcfmfa fa-envelope wcfm_email_verified_icon text_tip" data-tip="' . __( 'Email Verified', 'wc-frontend-manager' ) . '" style="color: #008C00; margin-right: 5px;"></span>';
					} else {
						$verification_label .= '&nbsp;<span class="wcfmfa fa-envelope-open wcfm_email_verified_icon text_tip" data-tip="' . __( 'Email Verification Pending', 'wc-frontend-manager' ) . '" style="color: #FF1A00; margin-right: 5px;"></span>';
					}
				}
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					if( apply_filters( 'wcfm_is_pref_vendor_verification', true ) && apply_filters( 'wcfm_is_pref_profile', true ) ) {
						global $WCFMu;
						//$verification_label .= $WCFMu->wcfmu_vendor_support->wcfm_show_verified_seller_badge( $wcfm_vendors_id, '', 'get' );
						$verification_label .= $WCFMu->vendor_verification->show_verified_seller_badge( $wcfm_vendors_id, '', 'get' );
					}
				}
				$wcfm_vendors_json_arr[$index][] =  $verification_label;
				
				// Name
				if( apply_filters( 'wcfm_is_allow_manage_vendor', true ) ) {
					$vendor_display_name = '<a href="'. get_wcfm_vendors_manage_url($wcfm_vendors_id) . '" class="wcfm_dashboard_item_title">' . apply_filters( 'wcfm_vendors_display_name_data', $wcfm_vendors_name, $wcfm_vendors_id ) . '</a>';
				} else {
					$vendor_display_name = '<span class="wcfm_dashboard_item_title">' . apply_filters( 'wcfm_vendors_display_name_data', $wcfm_vendors_name, $wcfm_vendors_id ) . '</span>';
				}
				$wcfm_vendors_json_arr[$index][] =  $vendor_display_name;
				
				// Shop Name
				$wcfm_vendors_json_arr[$index][] =  '<span class="wcfm_vendor_store">' . apply_filters( 'wcfm_vendors_store_name_data', wcfm_get_vendor_store( $wcfm_vendors_id ), $wcfm_vendors_id ) . '</span>';
				
				// Membership
				$membership_label = '';
				if( WCFM_Dependencies::wcfmvm_plugin_active_check() && apply_filters( 'wcfm_is_pref_membership', true ) ) {
					$wcfm_membership = get_user_meta( $wcfm_vendors_id, 'wcfm_membership', true );
					if( $wcfm_membership && function_exists( 'wcfm_is_valid_membership' ) && wcfm_is_valid_membership( $wcfm_membership ) ) {
						$membership_label .= '<span class="wcfm_vendor_memvership">' . get_the_title( $wcfm_membership ) . '</span>';
						$next_schedule = get_user_meta( $wcfm_vendors_id, 'wcfm_membership_next_schedule', true );
						if( $next_schedule ) {
							$subscription = (array) get_post_meta( $wcfm_membership, 'subscription', true );
							$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
							$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
								
							if( ( $is_free == 'no' ) && ( $subscription_type != 'one_time' ) ) {
								$membership_label .= "<br />";
								$membership_label .= '<span class="wcfmfa fa-clock img_tip" data-tip="' . __( 'Next payment on', 'wc-frontend-manager' ) . '"></span>&nbsp;';
								$membership_label .= date_i18n( wc_date_format(), $next_schedule );
							}
							
							$member_billing_period = get_user_meta( $wcfm_vendors_id, 'wcfm_membership_billing_period', true );
							$member_billing_cycle = get_user_meta( $wcfm_vendors_id, 'wcfm_membership_billing_cycle', true );
							if( $member_billing_period && $member_billing_cycle ) {
								$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
								$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : '';
								$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
								$period_options = array( 'D' => 'days', 'M' => 'months', 'Y' => 'years' );
								
								if( $billing_period_count ) {
									if( $member_billing_period ) $member_billing_period = absint( $member_billing_period );
									else $member_billing_period = absint( $billing_period_count );
									if( !$member_billing_cycle ) $member_billing_cycle = 1;
									$remaining_cycle = ( $member_billing_period - $member_billing_cycle );
									if( $remaining_cycle == 0 ) {
										$membership_label .= "<br />";
										$membership_label .= '<span class="wcfmfa fa-user-times img_tip" data-tip="' . __( 'Expiry on', 'wc-frontend-manager' ) . '"></span>&nbsp;';
										$membership_label .= date_i18n( wc_date_format(), $next_schedule );
									} else {
										$expiry_time = strtotime( '+' . $remaining_cycle . ' ' . $period_options[$billing_period_type], $next_schedule );
										$membership_label .= "<br />";
										$membership_label .= '<span class="wcfmfa fa-user-times img_tip" data-tip="' . __( 'Expiry on', 'wc-frontend-manager' ) . '"></span>&nbsp;';
										$membership_label .= date_i18n( wc_date_format(), $expiry_time );
									}
								} else {
									$membership_label .= "<br />";
									if( $is_free == 'yes' ) {
										$membership_label .= '<span class="wcfmfa fa-user-times img_tip" data-tip="' . __( 'Expiry on', 'wc-frontend-manager' ) . '"></span>&nbsp;';
										$membership_label .= date_i18n( wc_date_format(), $next_schedule );
									} else {
										$membership_label .= '<span class="wcfmfa fa-user-times img_tip" data-tip="' . __( 'Expiry on', 'wc-frontend-manager' ) . '"></span>&nbsp;' . __( 'Never Expire', 'wc-frontend-manager' );
									}
								}
							}
							
							//echo $member_billing_period . "::" . $member_billing_cycle;
						} else {
							$membership_label .= "<br />";
							$membership_label .= '<span class="wcfmfa fa-user-times img_tip" data-tip="' . __( 'Expiry on', 'wc-frontend-manager' ) . '"></span>&nbsp;' . __( 'Never Expire', 'wc-frontend-manager' );
						}
					} else {
						$membership_label .= '<span class="wcfm_vendor_memvership">&ndash;</span>';
					}
				}
				$wcfm_vendors_json_arr[$index][] = $membership_label;
				
				// Products Stat
				$wcfm_vendors_json_arr[$index][] = $WCFM->wcfm_vendor_support->wcfm_vendor_product_limit_stat( $wcfm_vendors_id );
				
				// Disak Space Usage Stat
				$wcfm_vendors_json_arr[$index][] = $WCFM->wcfm_vendor_support->wcfm_vendor_space_limit_stat( $wcfm_vendors_id );
				
				// Gross Sales
				$gross_sales = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $wcfm_vendors_id, $report_for, false, 0, $filter_date_form, $filter_date_to );
				$wcfm_vendors_json_arr[$index][] = apply_filters( 'wcfm_vendors_gross_sales_data', wc_price( $gross_sales ), $wcfm_vendors_id );
				
				// Earned Commission
				$earned = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $wcfm_vendors_id, $report_for, false, 0, $filter_date_form, $filter_date_to );
				if( $admin_fee_mode ) {
					$earned = $gross_sales - $earned;
				}
				$wcfm_vendors_json_arr[$index][] = apply_filters( 'wcfm_vendors_earned_commission_data', wc_price( $earned ), $wcfm_vendors_id, $report_for, $filter_date_form, $filter_date_to );

				// Received Commission
				$received_commission = $WCFM->wcfm_vendor_support->wcfm_get_withdrawal_by_vendor( $wcfm_vendors_id, $report_for, $filter_date_form, $filter_date_to );
				if( $admin_fee_mode ) {
					$net_paid_sales = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $wcfm_vendors_id, $report_for, true, 0, $filter_date_form, $filter_date_to );
					$received_commission = $net_paid_sales - $received_commission;
				}
				$wcfm_vendors_json_arr[$index][] = apply_filters( 'wcfm_vendors_received_commission_data', wc_price( $received_commission ), $wcfm_vendors_id, $report_for, $filter_date_form, $filter_date_to );
				
				// Additional Info
				$wcfm_vendors_json_arr[$index][] = apply_filters( 'wcfm_vendors_additonal_data', '&ndash;', $wcfm_vendors_id );

				// Action
				$actions = '';
				if( apply_filters( 'wcfm_is_allow_manage_vendor', true ) ) {
					if( !$disable_vendor ) {
						$actions = '<a href="'. get_wcfm_vendors_manage_url($wcfm_vendors_id) .'" class="wcfm-action-icon"><span class="wcfmfa fa-eye text_tip" data-tip="' . __( 'Details', 'wc-frontend-manager' ) . '"></span></a>';
						if( $WCFM->is_marketplace && ( $WCFM->is_marketplace == 'wcfmmarketplace' ) && apply_filters( 'wcfm_is_allow_reports', true ) ) {
							$actions .= '<a href="'. get_wcfm_vendors_manage_url($wcfm_vendors_id) .'#wwcfm_orders_listing_expander" class="wcfm-action-icon"><span class="wcfmfa fa-shopping-cart text_tip" data-tip="' . __( 'Orders', 'wc-frontend-manager' ) . '"></span></a>';
							$actions .= '<a href="'. get_wcfm_reports_url( '', 'wcfm-reports-sales-by-vendor', $wcfm_vendors_id ) .'" class="wcfm-action-icon"><span class="wcfmfa fa-chart-line text_tip" data-tip="' . __( 'Sales Report', 'wc-frontend-manager' ) . '"></span></a>';
						}
						
						if( apply_filters( 'wcfm_is_allow_disable_enable_vendor', true ) ) {
							$actions .= '<a href="#" data-memberid="'.$wcfm_vendors_id.'" class="wcfm_vendor_disable_button wcfm-action-icon"><span class="wcfmfa fa-times-circle text_tip" data-tip="' . __( 'Disable Vendor Account', 'wc-frontend-manager' ) . '"></span></a>';
						}
					} else {
						if( apply_filters( 'wcfm_is_allow_disable_enable_vendor', true ) ) {
							$actions .= '<a href="#" data-memberid="'.$wcfm_vendors_id.'" class="wcfm_vendor_enable_button wcfm-action-icon"><span class="wcfmfa fa-check-circle text_tip" data-tip="' . __( 'Enable Vendor Account', 'wc-frontend-manager' ) . '"></span></a>';
						}
					}
					$wcfm_vendors_json_arr[$index][] = apply_filters ( 'wcfm_vendors_actions', $actions, $wcfm_vendors_id );
				} else {
					$wcfm_vendors_json_arr[$index][] = '&ndash;';
				}
				
				$index++;
			}												
		}
		if( !empty($wcfm_vendors_json_arr) ) $wcfm_vendors_json .= json_encode($wcfm_vendors_json_arr);
		else $wcfm_vendors_json .= '[]';
		$wcfm_vendors_json .= '
													}';
													
		echo $wcfm_vendors_json;
	}
}