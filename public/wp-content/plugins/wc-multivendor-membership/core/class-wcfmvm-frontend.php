<?php
/**
 * WCFM Vendor Membership plugin core
 *
 * Plugin Frontend Controler
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/core
 * @version   1.0.0
 */
 
class WCFMvm_Frontend {
	
	public function __construct() {
		global $WCFM, $WCFMvm;
		
		// WCFM Membership Page Template
 		add_action( 'page_template', array( &$this, 'wcfm_membership_template' ) );
		
 		if( apply_filters( 'wcfm_is_allow_manage_groups', true ) ) {
			// WCFM Membership End Points
			add_filter( 'wcfm_query_vars', array( &$this, 'wcfmvm_vendor_membership_wcfm_query_vars' ), 90 );
			add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfmvm_vendor_membership_endpoint_title' ), 90, 2 );
			add_action( 'init', array( &$this, 'wcfmvm_vendor_membership_init' ), 90 );
			
			// WCFM Membership Page
			add_filter( 'wcfm_settings_fields_pages', array( $this, 'wcfmvm_settings_fields_pages' ) ); 
			
			// WCFM Membership Endpoint Edit
			add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfmvm_vendor_membership_endpoints_slug' ) );
			
			// WCFM Menu Filter
			add_filter( 'wcfm_menus', array( &$this, 'wcfmvm_vendor_membership_menus' ), 300 );
			add_filter( 'wcfm_menu_dependancy_map', array( &$this, 'wcfmvm_vendor_membership_menu_dependancy_map' ) );
		}
		
		// Vendor Details Page
		if( !wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_manage_groups', true ) ) {
			//add_action( 'after_wcfm_vendors_manage_form', array( &$this, 'wcfmvm_vendor_manage_profile_additional_info' ), 12 );
			add_action( 'after_wcfm_vendors_manage_membership_details', array( &$this, 'wcfmvm_vendor_manage_membership_additional_info' ), 12 );
			add_action( 'wcfm_vendor_manage_membrship_details', array( &$this, 'wcfmvm_vendor_manage_membrship_details' ), 12 );
		}
		
		// Membership Details in Profile 
		if( wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_vendor_membership', true ) ) {
			if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
				add_action( 'wcfm_dashboard_after_username', array( &$this, 'wcfmvm_vendor_dashboard_username' ), 12 );
				//add_action( 'end_wcfm_vendor_settings', array( &$this, 'wcfmvm_vendor_membership_user_setting_block' ), 12 );
				add_action( 'wcfm_vendor_setting_header_after', array( &$this, 'wcfmvm_vendor_membership_user_setting_header' ), 12 );
				add_action( 'end_wcfm_user_profile', array( &$this, 'wcfmvm_vendor_membership_user_profile' ), 12 );
				if( apply_filters( 'wcfm_is_allow_membership_manage_under_setting', false ) ) {
					add_action( 'end_wcfm_vendor_settings', array( &$this, 'wcfmvm_vendor_membership_user_profile' ), 12 );
				}
			}
		}
		
		// Show Membership Plan change option when product limit reached
		if( wcfm_is_vendor() ) {
			add_action( 'wcfm_product_limit_pay_for_product_after', array( &$this, 'wcfmvm_after_pay_per_product_option' ) );
			add_action( 'wcfm_product_limit_reached', array( &$this, 'wcfmvm_change_membership_option' ), 20 );
		}
		
		// Membership direct message type
		add_filter( 'wcfm_message_types', array( &$this, 'wcfm_membership_message_types' ), 50 );
		
		// WC Checkout for WCfM Membership products thank you redirect
		add_action( 'woocommerce_thankyou', array( &$this, 'wcfmvm_thankyou_redirect_on_membership_purchase' ) );
		
		// Custom Plan Page Redirect support added
		add_filter( 'wcfm_change_membership_url', array( &$this, 'wcfm_membership_custom_plan_url' ), 50 );
		
		// Custom Thank You Page Redirect support added
		add_filter( 'wcfm_registration_thankyou_url', array( &$this, 'wcfm_membership_custom_thankyou_url' ), 50 );
		
		// Membership Subscription First Step
		add_filter( 'wcfmvm_is_allow_registration_first', array( &$this, 'wcfmvm_registration_first_step' ), 50 );
		
		// Membership enqueue scripts
		add_action('wp_enqueue_scripts', array(&$this, 'wcfmvm_scripts'));
		// Membership enqueue styles
		add_action('wp_enqueue_scripts', array(&$this, 'wcfmvm_styles'));
		
	}
	
	/**
 	 * WCFM Page template if full screen selected
 	 */
	function wcfm_membership_template( $page_template ) {
		global $WCFM;
		if ( wc_post_content_has_shortcode( 'wcfm_vendor_membership' ) && apply_filters( 'wcfm_is_allow_membership_empty_template', true )  ) {
			if( function_exists( 'et_theme_builder_frontend_override_template' ) && apply_filters( 'wcfm_is_allow_divi_builder_template', true ) ) {
				$layouts         = et_theme_builder_get_template_layouts();
				$override_header = et_theme_builder_overrides_layout( ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE );
				$override_footer = et_theme_builder_overrides_layout( ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE );
				if ( $override_header || $override_footer ) {
					return $page_template;
				}
			}
			
			
			$wcfm_options = get_option( 'wcfm_options', array() );
			$is_dashboard_full_view_disabled = isset( $wcfm_options['dashboard_full_view_disabled'] ) ? $wcfm_options['dashboard_full_view_disabled'] : 'no';
			$is_dashboard_theme_header_disabled = isset( $wcfm_options['dashboard_theme_header_disabled'] ) ? $wcfm_options['dashboard_theme_header_disabled'] : 'no';
			if( $is_dashboard_full_view_disabled != 'yes' ) {
				$template_path = WC()->template_path();
				$skin_path     = $WCFM->plugin_path . 'templates/classic/';
				//if( $is_dashboard_theme_header_disabled == 'yes' ) $skin_path     = $WCFM->plugin_path . 'templates/default/';
				$page_template = wc_locate_template( 'wcfm-content.php', $template_path, $skin_path );
			}
		}

		return $page_template;
	}
	
	/**
   * WCFM Membership Query Var
   */
  function wcfmvm_vendor_membership_wcfm_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = get_option( 'wcfm_endpoints', array() );
  	
		$query_vendor_membership_vars = array(
			'wcfm-memberships'          => ! empty( $wcfm_modified_endpoints['wcfm-memberships'] ) ? $wcfm_modified_endpoints['wcfm-memberships'] : 'memberships',
			'wcfm-memberships-manage'    => ! empty( $wcfm_modified_endpoints['wcfm-memberships-manage'] ) ? $wcfm_modified_endpoints['wcfm-memberships-manage'] : 'memberships-manage',
			'wcfm-memberships-settings'  => ! empty( $wcfm_modified_endpoints['wcfm-memberships-settings'] ) ? $wcfm_modified_endpoints['wcfm-memberships-settings'] : 'memberships-settings',
		);
		
		$query_vars = array_merge( $query_vars, $query_vendor_membership_vars );
		
		return $query_vars;
  }
  
  /**
   * WCFM Membership End Point Title
   */
  function wcfmvm_vendor_membership_endpoint_title( $title, $endpoint ) {
  	
  	switch ( $endpoint ) {
			case 'wcfm-memberships' :
				$title = __( 'Memberships', 'wc-multivendor-membership' );
			break;
			case 'wcfm-memberships-manage' :
				$title = __( 'Membership Manage', 'wc-multivendor-membership' );
			break;
			case 'wcfm-memberships-settings' :
				$title = __( 'Membership Settings', 'wc-multivendor-membership' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCFM Membership Endpoint Intialize
   */
  function wcfmvm_vendor_membership_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_wcfmvm_vendor_membership' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_wcfmvm_vendor_membership', 1 );
		}
  }
  
  /**
	 * WCFM Membership Pages Edit
	 */
  function wcfmvm_settings_fields_pages( $wcfm_pages ) {
  	$wcfm_page_options = get_option( 'wcfm_page_options', array() );
  	$wcfm_pages["wcfm_vendor_membership_page_id"] = array( 'label' => __('Membership', 'wc-multivendor-membership'), 'type' => 'select', 'name' => 'wcfm_page_options[wcfm_vendor_membership_page_id]', 'options' => $wcfm_pages["wc_frontend_manager_page_id"]['options'], 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => isset($wcfm_page_options['wcfm_vendor_membership_page_id']) ? $wcfm_page_options['wcfm_vendor_membership_page_id'] : '', 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'This page should have shortcode - wcfm_vendor_membership', 'wc-frontend-manager') );
  	$wcfm_pages["wcfm_vendor_registration_page_id"] = array( 'label' => __('Registration', 'wc-multivendor-membership'), 'type' => 'select', 'name' => 'wcfm_page_options[wcfm_vendor_registration_page_id]', 'options' => $wcfm_pages["wc_frontend_manager_page_id"]['options'], 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => isset($wcfm_page_options['wcfm_vendor_registration_page_id']) ? $wcfm_page_options['wcfm_vendor_registration_page_id'] : '', 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'This is an optional page, you may use this as Free Membership registration page. This page should have shortcode - wcfm_vendor_registration', 'wc-frontend-manager') );
  	
  	return $wcfm_pages;
  }
  
  /**
	 * WCFM Membership Endpoiint Edit
	 */
	function wcfmvm_vendor_membership_endpoints_slug( $endpoints ) {
		
		$wcfmvm_vendor_membership_endpoints = array(
													'wcfm-memberships'            => 'memberships',
													'wcfm-memberships-manage'     => 'memberships-manage',
													'wcfm-memberships-settings'   => 'memberships-settings',
													);
		
		$endpoints = array_merge( $endpoints, $wcfmvm_vendor_membership_endpoints );
		
		return $endpoints;
	}
  
  /**
   * WCFM Membership Menu
   */
  function wcfmvm_vendor_membership_menus( $menus ) {
  	global $WCFM;
  	
		if( !apply_filters( 'wcfm_is_allow_membership', true ) ) return $menus;
		
		if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
			$menus = array_slice($menus, 0, 3, true) +
											array( 'wcfm-memberships' => array(   'label'     => __( 'Memberships', 'wc-multivendor-membership'),
																													 'url'        => get_wcfm_memberships_url( ),
																													 'icon'       => 'user-plus',
																													 'priority'   => 62
																													) )	 +
														array_slice($menus, 3, count($menus) - 3, true) ;
		}
		
  	return $menus;
  }
  
  /**
   * WCFM Membership Menu Dependency
   */
  function wcfmvm_vendor_membership_menu_dependancy_map( $menu_dependency_mapping ) {
  	$menu_dependency_mapping['wcfm-memberships-manage'] = 'wcfm-memberships';
  	$menu_dependency_mapping['wcfm-memberships-settings'] = 'wcfm-memberships';
  	return $menu_dependency_mapping;
  }
  
  /**
   * Vendor Details page Membership
   */
  function wcfmvm_vendor_manage_membrship_details( $vendor_id ) {
  	global $WCFM, $WCFMvm;
		
		if( !$vendor_id ) return;
		
		$disable_vendor = get_user_meta( $vendor_id, '_disable_vendor', true );
		if( $disable_vendor ) return;
		
		$wcfm_membership_id = get_user_meta( $vendor_id, 'wcfm_membership', true );
		$next_schedule = get_user_meta( $vendor_id, 'wcfm_membership_next_schedule', true );
		
		$is_recurring = false;
		if( $wcfm_membership_id && wcfm_is_valid_membership( $wcfm_membership_id ) ) {
			$membership_post = get_post( $wcfm_membership_id );
			$title = htmlspecialchars($membership_post->post_title);
			$description = $membership_post->post_excerpt;

			$subscription = (array) get_post_meta( $wcfm_membership_id, 'subscription', true );
			$features = (array) get_post_meta( $wcfm_membership_id, 'features', true );
			
			$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
			$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
			$one_time_amt = isset( $subscription['one_time_amt'] ) ? floatval($subscription['one_time_amt']) : '1';
			$trial_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '';
			$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
			$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
			$billing_amt = isset( $subscription['billing_amt'] ) ? floatval($subscription['billing_amt']) : '1';
			$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
			$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
			$period_options = array( 'D' => __( 'Day(s)', 'wc-multivendor-membership' ), 'M' => __( 'Month(s)', 'wc-multivendor-membership' ), 'Y' => __( 'Year(s)', 'wc-multivendor-membership' ) );
			
			$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
			$membership_feature_lists = array();
			if( isset( $wcfm_membership_options['membership_features'] ) ) $membership_feature_lists = $wcfm_membership_options['membership_features'];
			
			?>
			<h2><?php _e( 'Vendor\'s subscription details:', 'wc-multivendor-membership' ); ?></h2>
			<div class="wcfm_clearfix"></div><br />
			<div class="wcfm_membership_review_pay">
				<div class="wcfm_membership_review_plan">
					<div class="wcfm_review_plan_title"><?php echo $title; ?></div>
					<div class="wcfm_review_plan_description"><?php echo $description; ?></div>
					<div class="wcfm_review_plan_features">
						<?php
						if( !empty( $membership_feature_lists ) ) {
							foreach( $membership_feature_lists as $membership_feature_key => $membership_feature_list ) {
								if( isset( $membership_feature_list['feature'] ) && !empty( $membership_feature_list['feature'] ) ) {
									$feature_val = '';
									$feature_name = sanitize_title($membership_feature_list['feature']);
									if( !empty( $features ) && isset( $features[$feature_name] ) && !empty( $features[$feature_name] ) ) $feature_val = $features[$feature_name];
									if( !empty( $features ) && !$feature_val && isset( $features[$membership_feature_list['feature']] ) ) $feature_val = $features[$membership_feature_list['feature']];
									if( !$feature_val ) $feature_val = 'x';
									?>
									<div class="wcfm_review_plan_feature"><?php echo wcfm_removeslashes( __( $membership_feature_list['feature'], 'wc-multivendor-membership' ) ); ?></div>
									<div class="wcfm_review_plan_feature_val"><?php echo wcfm_removeslashes( __( $feature_val, 'wc-multivendor-membership' ) ); ?></div>
									<?php
								}
							}
						}
						?>
					</div>
				</div>
				
				<div class="wcfm_membership_pay">
					<div class="wcfm_review_pay_welcome"><?php _e( 'Pricing Details: ', 'wc-multivendor-membership' ); ?></div>
					<?php
					if( $is_free == 'yes' ) {
						?>
						<div class="wcfm_review_pay_free">
							<?php _e( 'FREE Plan!!! There is no payment option for this.', 'wc-multivendor-membership' ); ?>
							<?php
							echo "<div class=\"wcfm_clearfix\"></div><br /><div>";
							_e( 'Expire on: ', 'wc-multivendor-membership' );
							if( $next_schedule ) {
								echo '<span class="wcfmvm_next_renewal_display">' . date_i18n( wc_date_format(), $next_schedule ) . '</span>';
							}  else {
								echo '<span class="wcfmvm_next_renewal_display">';
								_e( 'Never Expire', 'wc-multivendor-membership' );
								echo '</span>';
							}
							$next_schedule_formt = $next_schedule ? date_i18n( wc_date_format(), $next_schedule ) : '';
							echo '<span id="wcfmvm_change_next_renewal" class="wcfmfa fa-edit tips text_tip wcfmvm_change_next_renewal" data-member="'.$vendor_id.'" data-schedule="' . $next_schedule_formt . '" data-tip="' . __( 'Set or update member next renewal date.', 'wc-multivendor-membership' ) . '"></span>';
							echo "</div><div class=\"wcfm_clearfix\"></div><br />";
						echo '</div>';
					} else {
						echo '<div class="wcfm_review_pay_non_free">';
						$wcfm_membership_payment_methods = get_wcfm_membership_payment_methods();
						$paymode = get_user_meta( $vendor_id, 'wcfm_membership_paymode', true );
						if( in_array( $paymode, array( 'paypal_subs', 'paypal_subs_subs' ) ) ) $paymode = 'paypal';
						if( in_array( $paymode, array( 'stripe', 'stripe_subs', 'stripe_subs_subs' ) ) ) $paymode = 'stripe';
						if( in_array( $paymode, array( 'bank_transfer', 'bank_transfer_subs' ) ) ) $paymode = 'bank_transfer';
						if( !$paymode ) $paymode = 'bank_transfer';
						if( isset( $wcfm_membership_payment_methods[$paymode] ) ) {
							$paymode = $wcfm_membership_payment_methods[$paymode];
						} else {
							if ( function_exists('icl_object_id') ) {
								global $sitepress;
								remove_filter('get_terms_args', array( $sitepress, 'get_terms_args_filter'));
								remove_filter('get_term', array($sitepress,'get_term_adjust_id'));
								remove_filter('terms_clauses', array($sitepress,'terms_clauses'));
							}
							if ( WC()->payment_gateways() ) {
								$payment_gateways = WC()->payment_gateways->payment_gateways();
								$paymode = isset( $payment_gateways[ $paymode ] ) ? esc_html( $payment_gateways[ $paymode ]->get_title() ) : __( 'FREE', 'wc-multivendor-membership' );
							}
						}
						echo "<div>";
						_e( 'Pay Mode: ', 'wc-multivendor-membership' );
						echo $paymode;
						echo "</div><div class=\"wcfm_clearfix\"></div><br /><div>";
						if( $next_schedule ) {
							if( $subscription_type == 'one_time' ) {
								_e( 'Expire on: ', 'wc-multivendor-membership' );
							} else {
								_e( 'Next payment on: ', 'wc-multivendor-membership' );
							}
							echo '<span class="wcfmvm_next_renewal_display">' . date_i18n( wc_date_format(), $next_schedule ) . '</span>';
						} else {
							_e( 'Expire on: ', 'wc-multivendor-membership' );
							echo '<span class="wcfmvm_next_renewal_display">';
							_e( 'Never Expire', 'wc-multivendor-membership' );
							echo '</span>';
						}
						$next_schedule_formt = $next_schedule ? date_i18n( wc_date_format(), $next_schedule ) : '';
						echo '<span id="wcfmvm_change_next_renewal" class="wcfmfa fa-edit tips text_tip wcfmvm_change_next_renewal" data-member="'.$vendor_id.'" data-schedule="' . $next_schedule_formt . '" data-tip="' . __( 'Set or update member next renewal date.', 'wc-multivendor-membership' ) . '"></span>';
						echo "</div><div class=\"wcfm_clearfix\"></div><br />";
						if( $subscription_type == 'one_time' ) {
							echo wc_price( wcfmvm_membership_tax_price( $one_time_amt ) );
							wcfmvm_membership_table_tax_display( 'span' );
							echo ' <span class="wcfm_membership_price_description">(' . __( 'One time payment', 'wc-multivendor-membership' ) . ')</span>';
						} else {
							$is_recurring = true;
							echo wc_price( wcfmvm_membership_tax_price( $billing_amt ) );
							$price_description = sprintf( __( 'for each %s %s', 'wc-multivendor-membership' ), $billing_period, $period_options[$billing_period_type] );
							if( !empty( $trial_period ) && !empty( $trial_amt ) ) {
								$price_description .= ' ' . sprintf( __( 'with %s for first %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $trial_amt, $trial_period, $period_options[$trial_period_type] );
							} elseif( !empty( $trial_period ) && empty( $trial_amt ) ) {
								$price_description .= ' ' . sprintf( __( 'with %s %s free trial', 'wc-multivendor-membership' ), $trial_period, $period_options[$trial_period_type] );
							}
							wcfmvm_membership_table_tax_display( 'span' );
							echo ' <span class="wcfm_membership_price_description">(' . $price_description . ')</span>';
							
							// Show PayPal Recurring profile details
							$paymode = get_user_meta( $vendor_id, 'wcfm_membership_paymode', true );
							if( $paymode && ( $paymode == 'paypal_subs' ) ) {
								$transaction_id = get_user_meta( $vendor_id, 'wcfm_transaction_id', true );
								if( $transaction_id ) {
									
								}
							}
						}
						
						echo "<div class=\"wcfm_clearfix\"></div><br />";
						_e( 'Cancel vendor membership: ', 'wc-multivendor-membership' );
						echo '<a href="#" style="float: none; padding: 10px !important;" data-memberid="'.$vendor_id.'" data-membershipid="'.$wcfm_membership_id.'" id="wcfm_membership_cancel_button" class="wcfm_membership_cancel_button wcfm_submit_button">' . __( 'Cancel', 'wc-multivendor-membership' ) . '</a>';
						echo "<div class=\"wcfm_clearfix\"></div><br />";
						echo '</div>';
					}
					?>
				</div>
			</div>
			<?php
		} else {
			echo "<h2>";
			_e( 'Vendor not yet subscribed for a membership!', 'wc-multivendor-membership' );
			echo "</h2><div class=\"wcfm_clearfix\"></div><br />";
		}
		
		do_action( 'after_wcfm_vendors_manage_membership_details', $vendor_id );
		
		$wcfm_memberships_list = get_wcfm_memberships();
		//if( !$is_recurring ) {
		if( count( $wcfm_memberships_list ) >= 1 ) {
			echo "</h2><div class=\"wcfm_clearfix\"></div><br /><p class=\"wcfm_title\" style=\"width: auto; \">";
			_e( 'Change or Upgrade:', 'wc-multivendor-membership' );
			echo "</p>";
			?>
			<select id="wcfm_change_vendor_membership" name="vendor_membership">
				<option value=""><?php _e( '-- Choose Membership --', 'wc-multivendor-membership' ); ?></option>
				<?php
				$membership_options = '';
				if( !empty( $wcfm_memberships_list ) ) {
					foreach( $wcfm_memberships_list as $wcfm_membership_list ) {
						$membership_options .= '<option value="' . esc_attr( $wcfm_membership_list->ID ) . '" ' . selected( $wcfm_membership_list->ID, $wcfm_membership_id, false ) . '>' . esc_html( $wcfm_membership_list->post_title ) . '</option>';
					}
				}
				echo $membership_options;
				?>
			</select>
			<button class="wcfm_modify_vendor_membership button" id="wcfm_modify_vendor_membership" data-memberid="<?php echo $vendor_id; ?>"><?php _e( 'Update', 'wc-frontend-manager' ); ?></button>
			<div class="wcfm_clearfix"></div>
			<div class="wcfm-message" tabindex="-1"></div>
			<div class="wcfm_clearfix"></div>
			<?php
		}
		//} else {
			//if( count( $wcfm_memberships_list ) > 1 ) {
			//	printf( __( '%sChange or Upgrade: First cancel your current subscription.%s', 'wc-multivendor-membership' ), '<span style="text-decoration: underline; margin-left: 10px;">', '</span>' );
			//}
		//}
  }
  
  /**
   * Vendor Profile Additional Info
   */
  function wcfmvm_vendor_manage_membership_additional_info( $vendor_id ) {
  	global $WCFM, $WCFMvm;
  	
  	if( !$vendor_id ) return;
  		
  	$wcfmvm_registration_custom_fields = wcfm_get_option( 'wcfmvm_registration_custom_fields', array() );
		$wcfmvm_custom_infos = (array) get_user_meta( $vendor_id, 'wcfmvm_custom_infos', true );
		
		if( !empty( $wcfmvm_registration_custom_fields ) ) {
			echo "<div style=\"margin-top: 30px;\"><h2>" . __( 'Additional Info', 'wc-multivendor-membership' ) . "</h2><div class=\"wcfm_clearfix\"></div>";
			foreach( $wcfmvm_registration_custom_fields as $wcfmvm_registration_custom_field ) {
				if( !isset( $wcfmvm_registration_custom_field['enable'] ) ) continue;
				if( !$wcfmvm_registration_custom_field['label'] ) continue;
				$field_value = '&ndash;';
				$wcfmvm_registration_custom_field['name'] = sanitize_title( $wcfmvm_registration_custom_field['label'] );
			
				if( !empty( $wcfmvm_custom_infos ) ) {
					if( $wcfmvm_registration_custom_field['type'] == 'checkbox' ) {
						$field_value = isset( $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] ) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : 'no';
					} elseif( $wcfmvm_registration_custom_field['type'] == 'upload' ) {
						$field_name  = 'wcfmvm_custom_infos[' . $wcfmvm_registration_custom_field['name'] . ']';
						$field_id    = md5( $field_name );
						$field_value = isset( $wcfmvm_custom_infos[$field_id] ) ? $wcfmvm_custom_infos[$field_id] : '';
					} else {
						$field_value = isset( $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] ) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : '';
					}
				}
				?>
				<p class="store_name wcfm_ele wcfm_title"><strong><?php _e( $wcfmvm_registration_custom_field['label'], 'WCfM'); ?></strong></p>
				<span class="wcfm_vendor_store_info">
				  <?php 
				  if( $field_value && $wcfmvm_registration_custom_field['type'] == 'upload' ) {
				    echo '<a class="wcfm-wp-fields-uploader wcfm_linked_images" target="_blank" style="width: 32px; height: 32px;" href="' . wcfm_get_attachment_url( $field_value ) . '"><span style="width: 32px; height: 32px; display: inline-block;" class="placeHolderDocs"></span></a>';
				  } else {
				  	if( !$field_value ) $field_value = '&ndash;';
				  	if( is_array( $field_value ) ) echo implode( ', ', $field_value ); 
				  	else echo $field_value;
				  }
				  ?>
				</span>
				<div class="wcfm_clearfix"></div>
				<?php
			}
			echo "</div><div class=\"wcfm_clearfix\"></div><br />";
		}
  }
  
  function wcfmvm_vendor_dashboard_username( $vendor_id = 0 ) {
  	global $WCFM, $WCFMvm;
		
  	if( !$vendor_id ) {
  		$vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
  	}
		$wcfm_membership_id = get_user_meta( $vendor_id, 'wcfm_membership', true );
		if( $wcfm_membership_id && wcfm_is_valid_membership( $wcfm_membership_id ) ) {
			$membership_post = get_post( $wcfm_membership_id );
			$title = htmlspecialchars($membership_post->post_title);
			
			$membership_block_id = apply_filters( 'wcfm_membership_block_id', 'sm_profile_manage_form_membership_head' );
			
			if( apply_filters( 'wcfm_is_allow_profile_membership_upgrade', true ) ) {
				echo '<span class="wcfm_welcomebox_member">( ' . __( 'Membership', 'wc-multivendor-membership' ) . ': <a href="' . get_wcfm_profile_url() . '#' . $membership_block_id . '"><mark>' . $title . '</mark></a> )</span>';
			} else {
				echo '<span class="wcfm_welcomebox_member">( ' . __( 'Membership', 'wc-multivendor-membership' ) . ': <mark>' . $title . '</mark> )</span>';
			}
		}
  }
  
  function wcfmvm_vendor_membership_user_setting_block( $user_id ) {
  	$membership_block_id = apply_filters( 'wcfm_membership_block_id', 'sm_profile_manage_form_membership_head' );
  	?>
  	<a href="<?php echo get_wcfm_profile_url(); ?>#<?php echo $membership_block_id; ?>" class="page_collapsible page_collapsible_dummy" id="<?php echo $membership_block_id; ?>"><label class="wcfmfa fa-user-plus"></label><?php _e( 'Membership', 'wc-multivendor-membership' ); ?><span></span></a>
		<div class="wcfm-container">
			<div id="wcfm_profile_manage_form_membership_expander" class="wcfm-content"></div>
		</div>
		<?php
  }
  
  function wcfmvm_vendor_membership_user_setting_header( $user_id ) {
  	
  	if( !apply_filters( 'wcfm_is_allow_profile_membership_upgrade', true ) ) return;
  	
  	if( !apply_filters( 'wcfm_is_pref_profile', true ) || !apply_filters( 'wcfm_is_allow_profile', true ) ) return;
		
		$wcfm_memberships_list = get_wcfm_memberships();
		if( empty( $wcfm_memberships_list ) ) return;
		
		$membership_block_id = apply_filters( 'wcfm_membership_block_id', 'sm_profile_manage_form_membership_head' );
		
  	echo '<a id="wcfm_membership_settings" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_profile_url().'#' . $membership_block_id . '" data-tip="' . __( 'Membership', 'wc-multivendor-membership' ) . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Membership', 'wc-multivendor-membership' ) . '</span></a>';
  }
  
  function wcfmvm_vendor_membership_user_profile() {
		global $WCFM, $WCFMvm;
		
		$wcfm_memberships_list = get_wcfm_memberships();
		if( empty( $wcfm_memberships_list ) ) return;
		
		$vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		$wcfm_membership_id = get_user_meta( $vendor_id, 'wcfm_membership', true );
		$next_schedule = get_user_meta( $vendor_id, 'wcfm_membership_next_schedule', true );
		$member_billing_period = get_user_meta( $vendor_id, 'wcfm_membership_billing_period', true );
		$member_billing_cycle = get_user_meta( $vendor_id, 'wcfm_membership_billing_cycle', true );
		
		if( !apply_filters( 'wcfm_is_allow_profile_membership_upgrade', true, $vendor_id, $wcfm_membership_id ) ) return;
		
		$membership_block_id = apply_filters( 'wcfm_membership_block_id', 'sm_profile_manage_form_membership_head' );
		
		?>
		<div class="page_collapsible profile_manage_membership" id="<?php echo $membership_block_id; ?>"><label class="wcfmfa fa-user-plus"></label><?php _e( 'Membership', 'wc-multivendor-membership' ); ?><span></span></div>
		<div class="wcfm-container">
			<div id="wcfm_profile_manage_form_membership_expander" class="wcfm-content">
			  <?php
			  $is_recurring = false;
			  if( $wcfm_membership_id && wcfm_is_valid_membership( $wcfm_membership_id ) ) {
			  	$membership_post = get_post( $wcfm_membership_id );
					$title = htmlspecialchars($membership_post->post_title);
					$description = $membership_post->post_excerpt;
 		
			  	$subscription = (array) get_post_meta( $wcfm_membership_id, 'subscription', true );
					$features = (array) get_post_meta( $wcfm_membership_id, 'features', true );
					
					$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
					$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
					$one_time_amt = isset( $subscription['one_time_amt'] ) ? floatval($subscription['one_time_amt']) : '1';
					$trial_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '';
					$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
					$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
					$billing_amt = isset( $subscription['billing_amt'] ) ? floatval($subscription['billing_amt']) : '1';
					$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
					$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
					$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : '999';
					$period_options = array( 'D' => __( 'Day(s)', 'wc-multivendor-membership' ), 'M' => __( 'Month(s)', 'wc-multivendor-membership' ), 'Y' => __( 'Year(s)', 'wc-multivendor-membership' ) );
					
					$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
					$membership_feature_lists = array();
					if( isset( $wcfm_membership_options['membership_features'] ) ) $membership_feature_lists = $wcfm_membership_options['membership_features'];
					
					do_action( 'wcfm_before_membership_details', $vendor_id );
					?>
					<h2><?php _e( 'Your subscription details:', 'wc-multivendor-membership' ); ?></h2>
					<div class="wcfm_clearfix"></div><br />
					<div class="wcfm_membership_review_pay">
						<div class="wcfm_membership_review_plan">
							<div class="wcfm_review_plan_title"><?php echo $title; ?></div>
							<div class="wcfm_review_plan_description"><?php echo $description; ?></div>
							<div class="wcfm_review_plan_features">
							  <?php
							  if( !empty( $membership_feature_lists ) ) {
									foreach( $membership_feature_lists as $membership_feature_key => $membership_feature_list ) {
										if( isset( $membership_feature_list['feature'] ) && !empty( $membership_feature_list['feature'] ) ) {
											$feature_val = '';
											$feature_name = sanitize_title($membership_feature_list['feature']);
											if( !empty( $features ) && isset( $features[$feature_name] ) && !empty( $features[$feature_name] ) ) $feature_val = $features[$feature_name];
											if( !empty( $features ) && !$feature_val && isset( $features[$membership_feature_list['feature']] ) ) $feature_val = $features[$membership_feature_list['feature']];
											if( !$feature_val ) $feature_val = 'x';
											?>
											<div class="wcfm_review_plan_feature"><?php echo wcfm_removeslashes( __( $membership_feature_list['feature'], 'wc-multivendor-membership' ) ); ?></div>
											<div class="wcfm_review_plan_feature_val"><?php echo wcfm_removeslashes( __( $feature_val, 'wc-multivendor-membership' ) ); ?></div>
											<?php
										}
									}
							  }
							  ?>
							</div>
						</div>
						
						<?php do_action( 'wcfm_before_membership_pricing_details', $vendor_id ); ?>
						
						<div class="wcfm_membership_pay">
						  <div class="wcfm_review_pay_welcome"><?php _e( 'Pricing Details: ', 'wc-multivendor-membership' ); ?></div>
							<?php
							if( $is_free == 'yes' ) {
								?>
								<div class="wcfm_review_pay_free">
									<?php _e( 'FREE Plan!!! There is no payment option for this.', 'wc-multivendor-membership' ); ?>
									<?php
									echo "<div class=\"wcfm_clearfix\"></div><br /><div>";
									_e( 'Expire on: ', 'wc-multivendor-membership' );
									if( $next_schedule ) {
										echo date_i18n( wc_date_format(), $next_schedule );
									} else {
										_e( 'Never Expire', 'wc-multivendor-membership' );
									}
									echo "</div><div class=\"wcfm_clearfix\"></div><br />";
								echo '</div>';
							} else {
								echo '<div class="wcfm_review_pay_non_free">';
								$wcfm_membership_payment_methods = get_wcfm_membership_payment_methods();
								$paymode = get_user_meta( $vendor_id, 'wcfm_membership_paymode', true );
								if( in_array( $paymode, array( 'paypal_subs', 'paypal_subs_subs' ) ) ) $paymode = 'paypal';
								if( in_array( $paymode, array( 'stripe', 'stripe_subs', 'stripe_subs_subs' ) ) ) $paymode = 'stripe';
								if( in_array( $paymode, array( 'bank_transfer', 'bank_transfer_subs' ) ) ) $paymode = 'bank_transfer';
								if( !$paymode ) $paymode = 'bank_transfer';
								if( isset( $wcfm_membership_payment_methods[$paymode] ) ) {
									$paymode = $wcfm_membership_payment_methods[$paymode];
								} else {
									if ( function_exists('icl_object_id') ) {
										global $sitepress;
										remove_filter('get_terms_args', array( $sitepress, 'get_terms_args_filter'));
										remove_filter('get_term', array($sitepress,'get_term_adjust_id'));
										remove_filter('terms_clauses', array($sitepress,'terms_clauses'));
									}
									if ( WC()->payment_gateways() ) {
										$payment_gateways = WC()->payment_gateways->payment_gateways();
										$paymode = isset( $payment_gateways[ $paymode ] ) ? esc_html( $payment_gateways[ $paymode ]->get_title() ) : __( 'FREE', 'wc-multivendor-membership' );
									}
								}
								echo "<div>";
								_e( 'Pay Mode: ', 'wc-multivendor-membership' );
								echo $paymode;
								echo "</div><div class=\"wcfm_clearfix\"></div><br /><div>";
								if( $next_schedule ) {
									if( $subscription_type == 'one_time' ) {
										_e( 'Expire on: ', 'wc-multivendor-membership' );
									} else {
										_e( 'Next payment on: ', 'wc-multivendor-membership' );
									}  
									echo date_i18n( wc_date_format(), $next_schedule );
								} else {
									_e( 'Expire on: ', 'wc-multivendor-membership' );
									_e( 'Never Expire', 'wc-multivendor-membership' );
								}
								echo "</div><div class=\"wcfm_clearfix\"></div><br />";
								if( $subscription_type == 'one_time' ) {
									echo wc_price( wcfmvm_membership_tax_price($one_time_amt));
									wcfmvm_membership_table_tax_display( 'span' );
									echo ' <span class="wcfm_membership_price_description">(' . __( 'One time payment', 'wc-multivendor-membership' ) . ')</span>';
								} else {
									$is_recurring = true;
									echo wc_price( wcfmvm_membership_tax_price($billing_amt) );
									$price_description = sprintf( __( 'for each %s %s', 'wc-multivendor-membership' ), $billing_period, $period_options[$billing_period_type] );
									if( !empty( $trial_period ) && !empty( $trial_amt ) ) {
										$price_description .= ' ' . sprintf( __( 'with %s for first %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $trial_amt, $trial_period, $period_options[$trial_period_type] );
									} elseif( !empty( $trial_period ) && empty( $trial_amt ) ) {
										$price_description .= ' ' . sprintf( __( 'with %s %s free trial', 'wc-multivendor-membership' ), $trial_period, $period_options[$trial_period_type] );
									}
									wcfmvm_membership_table_tax_display( 'span' );
									echo ' <span class="wcfm_membership_price_description">(' . $price_description . ')</span>';
									
									// Show PayPal Recurring profile details
									$paymode = get_user_meta( $vendor_id, 'wcfm_membership_paymode', true );
									if( $paymode && ( $paymode == 'paypal_subs' ) ) {
										$transaction_id = get_user_meta( $vendor_id, 'wcfm_transaction_id', true );
										if( $transaction_id ) {
											
										}
									}
								}
								
								if( apply_filters( 'wcfm_is_allow_cancel_membership', true, $vendor_id, $wcfm_membership_id ) ) {
									echo "<div class=\"wcfm_clearfix\"></div><br />";
									_e( 'Cancel my membership: ', 'wc-multivendor-membership' );
									echo '<a href="#" style="float: none; padding: 10px !important;" data-memberid="'.$vendor_id.'" data-membershipid="'.$wcfm_membership_id.'" id="wcfm_membership_cancel_button" class="wcfm_membership_cancel_button wcfm_submit_button">' . __( 'Cancel', 'wc-multivendor-membership' ) . '</a>';
									echo "<div class=\"wcfm_clearfix\"></div><br />";
									echo '</div>';
								}
							}
							?>
						</div>
						<?php do_action( 'wcfm_after_membership_pricing_details', $vendor_id ); ?> 
					</div>
					<?php
					
					do_action( 'wcfm_after_membership_details', $vendor_id );
			  } else {
			  	echo "<h2>";
			  	_e( 'You are not subscribed for a membership yet!', 'wc-multivendor-membership' );
			  	echo "</h2><div class=\"wcfm_clearfix\"></div><br />";
			  }
			  
			  $wcfmvm_registration_custom_fields = get_option( 'wcfmvm_registration_custom_fields', array() );
				$wcfmvm_custom_infos = (array) get_user_meta( $vendor_id, 'wcfmvm_custom_infos', true );
				
				if( !empty( $wcfmvm_registration_custom_fields ) ) {
					echo "<div class='wcfm_profile_membership_additional_info_label' style=\"margin-top: 30px;\"><h2>" . __( 'Additional Info', 'wc-multivendor-membership' ) . "</h2><div class=\"wcfm_clearfix\"></div>";
					foreach( $wcfmvm_registration_custom_fields as $wcfmvm_registration_custom_field ) {
						if( !isset( $wcfmvm_registration_custom_field['enable'] ) ) continue;
						if( !$wcfmvm_registration_custom_field['label'] ) continue;
						$field_value = '&ndash;';
						$wcfmvm_registration_custom_field['name'] = sanitize_title( $wcfmvm_registration_custom_field['label'] );
					
						if( !empty( $wcfmvm_custom_infos ) ) {
							if( $wcfmvm_registration_custom_field['type'] == 'checkbox' ) {
								$field_value = isset( $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] ) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : 'no';
							} elseif( $wcfmvm_registration_custom_field['type'] == 'upload' ) {
								$field_name  = 'wcfmvm_custom_infos[' . $wcfmvm_registration_custom_field['name'] . ']';
								$field_id    = md5( $field_name );
								$field_value = isset( $wcfmvm_custom_infos[$field_id] ) ? $wcfmvm_custom_infos[$field_id] : '';
							} else {
								$field_value = isset( $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] ) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : '';
							}
						}
						?>
						<p class="store_name wcfm_ele wcfm_title"><strong><?php _e( $wcfmvm_registration_custom_field['label'], 'WCfM'); ?></strong></p>
						<span class="wcfm_vendor_store_info">
						  <?php 
							if( $field_value && $wcfmvm_registration_custom_field['type'] == 'upload' ) {
								echo '<a class="wcfm-wp-fields-uploader wcfm_linked_images" target="_blank" style="width: 32px; height: 32px;" href="' . $field_value . '"><span style="width: 32px; height: 32px; display: inline-block;" class="placeHolderDocs"></span></a>';
							} else {
								if( !$field_value ) $field_value = '&ndash;';
								if( is_array( $field_value ) ) echo implode( ', ', $field_value ); 
								else echo $field_value;
							}
							?>
						</span>
						<div class="wcfm_clearfix"></div>
						<?php
					}
					echo "</div><div class=\"wcfm_clearfix\"></div><br />";
				}
			  
				if( apply_filters( 'wcfm_is_allow_change_membership', true, $vendor_id ) ) {
					if( count( $wcfm_memberships_list ) > 1 ) {
						do_action( 'wcfm_before_change_membership_link', $vendor_id );
						if( !$is_recurring ) {
							printf( __( '%sChange or Upgrade your current membership plan >>%s', 'wc-multivendor-membership' ), '<a style="text-decoration: underline; margin-left: 10px; color: #00897b;" target="_blank" href="' . apply_filters( 'wcfm_change_membership_url', get_wcfm_membership_url() ) . '">', '</a>' );
						} else {
							printf( __( '%sChange or Upgrade: First cancel your current subscription.%s', 'wc-multivendor-membership' ), '<span style="text-decoration: underline; margin-left: 10px;">', '</span>' );
						}
						do_action( 'wcfm_after_change_membership_link', $vendor_id );
					}
				}
			  ?>
			</div>
		</div>
		<?php
	}
	
	function wcfmvm_after_pay_per_product_option() {
		$wcfm_memberships_list = get_wcfm_memberships();
		if( count( $wcfm_memberships_list ) > 1 ) {
			?>
			<div class="choose_pay_for_product_or_membership">
			  - &nbsp;<?php _e( 'OR', 'wc-multivendor-membership' ); ?>&nbsp; - 
			</div>
			<?php
		}
	}
	
	function wcfmvm_change_membership_option() {
		$wcfm_memberships_list = get_wcfm_memberships();
		if( count( $wcfm_memberships_list ) > 1 ) {
			printf( __( '%sChange or Upgrade your current membership plan >>%s', 'wc-multivendor-membership' ), '<a style="text-decoration: underline; margin-left: 10px; color: #00897b;" target="_blank" href="'. apply_filters( 'wcfm_change_membership_url', get_wcfm_membership_url() ) .'">', '</a>' );
		}
	}
  
  function wcfm_membership_message_types( $message_types ) {
  	$message_types['registration']         = __( 'New', 'wc-multivendor-membership' ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Vendor', 'wc-frontend-manager' ) );
  	$message_types['membership']           = __( 'New Membership', 'wc-multivendor-membership' );
		$message_types['vendor_approval']      = __( 'Approve Membership', 'wc-multivendor-membership' );
		$message_types['membership-reminder']  = __( 'Reminder Membership', 'wc-multivendor-membership' );
		$message_types['membership-cancel']    = __( 'Cancel Membership', 'wc-multivendor-membership' );
		$message_types['membership-expired']   = __( 'Expire Membership', 'wc-multivendor-membership' );
		$message_types['vendor-disable']       = __( 'Disable', 'wc-multivendor-membership' ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Vendor', 'wc-frontend-manager' ) );
		$message_types['vendor-enable']        = __( 'Enable', 'wc-multivendor-membership' ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Vendor', 'wc-frontend-manager' ) );
		return $message_types;
	}
	
	function wcfmvm_thankyou_redirect_on_membership_purchase( $order_id ) {
		$wcfm_subcription_products = get_option( 'wcfm_subcription_products', array() );
		
		if( !empty( $wcfm_subcription_products ) ) {
			$order         = new WC_Order( $order_id );
			$line_items    = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
			foreach ( $line_items as $item_id => $item ) {
				$product_id = $item->get_product_id();
				
				// WPML Support
				if ( $product_id && defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
					global $sitepress;
					$default_language = $sitepress->get_default_language();
					$product_id = icl_object_id( $product_id, 'product', false, $default_language );
				}
				
				if( in_array( $product_id, $wcfm_subcription_products ) ) {
					// Reset Membership Session
					if( WC()->session && WC()->session->get( 'wcfm_membership' ) ) {
						WC()->session->__unset( 'wcfm_membership' );
					}
					wp_redirect(add_query_arg( 'vmstep', 'thankyou', get_wcfm_membership_url() ));
					exit;
				}
			}
		}
	}
	
	function wcfm_membership_custom_plan_url( $plan_page_url ) {
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
		$membership_type_settings = array();
		if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
		$wcfm_custom_plan_page = isset( $membership_type_settings['wcfm_custom_plan_page'] ) ? $membership_type_settings['wcfm_custom_plan_page'] : '';
		
		if( $wcfm_custom_plan_page ) {
			$plan_page_url = get_permalink( $wcfm_custom_plan_page );
		}
		
		return $plan_page_url;
	}
	
	function wcfm_membership_custom_thankyou_url( $thank_you_page_url ) {
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
		$membership_type_settings = array();
		if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
		$wcfm_custom_thankyou_page = isset( $membership_type_settings['wcfm_custom_thankyou_page'] ) ? $membership_type_settings['wcfm_custom_thankyou_page'] : '';
		
		if( $wcfm_custom_thankyou_page ) {
			$thank_you_page_url = get_permalink( $wcfm_custom_thankyou_page );
		}
		
		return $thank_you_page_url;
	}
	
	function wcfmvm_registration_first_step( $is_registration ) {
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
		$membership_type_settings = array();
		if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
		$first_step = isset( $membership_type_settings['first_step'] ) ? $membership_type_settings['first_step'] : 'plan';
		if( $first_step != 'plan' ) $is_registration = true;
		return $is_registration;
	}
  
  /**
	 * WCFMvm Core JS
	 */
	function wcfmvm_scripts() {
 		global $WCFM, $WCFMvm, $wp, $WCFM_Query;
 		
 		if( isset( $_REQUEST['fl_builder'] ) ) return;
 		
 		// Memmebrship Subscribe Button
 		if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
 			//wp_enqueue_script( 'wcfm_membership_subscribe_js', $WCFMvm->library->js_lib_url_min . 'wcfmvm-script-membership-subscribe.js', array('jquery' ), $WCFMvm->version, true );
 		}
 		
 		// Load End Point Scripts
	  if( is_wcfm_membership_page() ) {
	  	$current_step = wcfm_membership_registration_current_step();
			
			switch( $current_step ) {
				case 'registration':
					//$WCFM->library->load_upload_lib();
					$WCFM->library->load_select2_lib();
					wp_enqueue_script( 'wc-country-select' );
					wp_enqueue_script( 'wcfm_membership_registration_js', $WCFMvm->library->js_lib_url_min . 'wcfmvm-script-membership-registration.js', array('jquery' ), $WCFMvm->version, true );
					
					$wcfm_registration_params = array( 'your_store' => __( 'your_store', 'wc-multivendor-membership' ), 'is_strength_check' => apply_filters( 'wcfm_is_allow_password_strength_check', true ), 'short' => __( 'Too short', 'wc-frontend-manager' ), 'weak' => __( 'Weak', 'wc-frontend-manager' ), 'good' => __( 'Good', 'wc-frontend-manager' ), 'strong' => __( 'Strong', 'wc-frontend-manager' ), 'password_failed' => __( 'Password strength should be atleast "Good".', 'wc-frontend-manager' ), "choose_select2" => __( "Choose ", "wc-frontend-manager" ) );
					wp_localize_script( 'wcfm_membership_registration_js', 'wcfm_registration_params', $wcfm_registration_params );
					
					if( apply_filters( 'wcfm_is_allow_registration_recaptcha', true ) ) {
						if ( class_exists( 'anr_captcha_class' ) && function_exists( 'anr_captcha_form_field' ) && function_exists( 'anr_get_option' ) ) {
							$site_key = trim( anr_get_option( 'site_key' ) );
							$theme    = anr_get_option( 'theme', 'light' );
							$size     = anr_get_option( 'size', 'normal' );
							$language = trim( anr_get_option( 'language' ) );
							$badge    = esc_js( anr_get_option( 'badge', 'bottomright' ) );
							
							$wcfm_registration_captcha_params = array( 'site_key' => $site_key, 'theme' => $theme, 'size' => $size, 'language' => $language, 'badge' => $badge );
							wp_localize_script( 'wcfm_membership_registration_js', 'wcfm_registration_captcha_params', $wcfm_registration_captcha_params );
						}
					}
				break;
				
				case 'payment':
					wp_enqueue_script( 'wcfm_membership_payment_js', $WCFMvm->library->js_lib_url_min . 'wcfmvm-script-membership-payment.js', array('jquery' ), $WCFMvm->version, true );
				break;
				
				default:
					wp_enqueue_script( 'wcfm_membership_js', $WCFMvm->library->js_lib_url_min . 'wcfmvm-script-membership-display.js', array('jquery' ), $WCFMvm->version, true );
				break;
			}
	  }
	  
	  if( is_wcfm_registration_page() ) {
			$WCFM->library->load_select2_lib();
			$WCFM->library->load_datepicker_lib();
			wp_enqueue_script( 'wc-country-select' );
			wp_enqueue_script( 'wcfm_membership_registration_js', $WCFMvm->library->js_lib_url_min . 'wcfmvm-script-membership-registration.js', array('jquery' ), $WCFMvm->version, true );
			
			$wcfm_registration_params = array( 'is_strength_check' => apply_filters( 'wcfm_is_allow_password_strength_check', true ), 'short' => __( 'Too short', 'wc-frontend-manager' ), 'weak' => __( 'Weak', 'wc-frontend-manager' ), 'good' => __( 'Good', 'wc-frontend-manager' ), 'strong' => __( 'Strong', 'wc-frontend-manager' ), 'password_failed' => __( 'Password strength should be atleast "Good".', 'wc-frontend-manager' ), "choose_select2" => __( "Choose ", "wc-frontend-manager" ) );
			wp_localize_script( 'wcfm_membership_registration_js', 'wcfm_registration_params', $wcfm_registration_params );
			
			if( apply_filters( 'wcfm_is_allow_registration_recaptcha', true ) ) {
				if ( class_exists( 'anr_captcha_class' ) && function_exists( 'anr_captcha_form_field' ) && function_exists( 'anr_get_option' ) ) {
					$site_key = trim( anr_get_option( 'site_key' ) );
					$theme    = anr_get_option( 'theme', 'light' );
					$size     = anr_get_option( 'size', 'normal' );
					$language = trim( anr_get_option( 'language' ) );
					$badge    = esc_js( anr_get_option( 'badge', 'bottomright' ) );
					
					$wcfm_registration_captcha_params = array( 'site_key' => $site_key, 'theme' => $theme, 'size' => $size, 'language' => $language, 'badge' => $badge );
					wp_localize_script( 'wcfm_membership_registration_js', 'wcfm_registration_captcha_params', $wcfm_registration_captcha_params );
				}
			}
		}
 	}
 	
 	/**
 	 * WCFMvm Core CSS
 	 */
 	function wcfmvm_styles() {
 		global $WCFM, $WCFMvm, $wp, $WCFM_Query;
 		
 		if( isset( $_REQUEST['fl_builder'] ) ) return;
 		
 		$wcfm_options = $WCFM->wcfm_options;
 		
 		$upload_dir      = wp_upload_dir();
 		
 		if( apply_filters( 'wcfm_is_pref_membership', true ) ) {
			//wp_enqueue_style( 'wcfm_subscribe_button_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-subscribe-button.css', array(), $WCFMvm->version );
			
			$wcfmvm_style_custom_subscribe_button = get_option( 'wcfmvm_style_custom_subscribe_button' );
			if( $wcfmvm_style_custom_subscribe_button && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmvm_style_custom_subscribe_button ) ) {
				wp_enqueue_style( 'wcfmvm_custom_subscribe_button_css',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmvm_style_custom_subscribe_button, array( 'wcfm_core_css' ), $WCFMvm->version );
			}
		}
		
		$current_step = wcfm_membership_registration_current_step();
 		
 		// Load End Point Scripts
	  if( is_wcfm_membership_page() ) {
			
			wp_enqueue_style( 'wcfm_membership_steps_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-steps.css', array(), $WCFMvm->version );
			
			switch( $current_step ) {
				case 'registration':
					wp_enqueue_style( 'wcfm_membership_registration_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-registration.css', array(), $WCFMvm->version );
					
					if( is_rtl() ) {
						wp_enqueue_style( 'wcfm_membership_registration_rtl_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-registration-rtl.css', array( 'wcfm_membership_registration_css' ), $WCFMvm->version );
					}
				break;
				
				case 'payment':
					wp_enqueue_style( 'wcfm_membership_payment_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-payment.css', array(), $WCFMvm->version );
				break;
				
				case 'thankyou':
					wp_enqueue_style( 'wcfm_membership_thankyou_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-thankyou.css', array(), $WCFMvm->version );
					
					if( is_rtl() ) {
						wp_enqueue_style( 'wcfm_membership_thankyou_rtl_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-thankyou-rtl.css', array('wcfm_membership_thankyou_css'), $WCFMvm->version );
					}
				break;
				
				default:
					wp_enqueue_style( 'wcfm_membership_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-display.css', array(), $WCFMvm->version );
					
					if( is_rtl() ) {
						wp_enqueue_style( 'wcfm_membership_rtl_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-display-rtl.css', array( 'wcfm_membership_css' ), $WCFMvm->version );
					}
				break;
			}
			
			// Load Template Style
			$is_dashboard_full_view_disabled = isset( $wcfm_options['dashboard_full_view_disabled'] ) ? $wcfm_options['dashboard_full_view_disabled'] : 'no';
			if( $is_dashboard_full_view_disabled != 'yes' ) {
				wp_enqueue_style( 'wcfmvm_template_css',  $WCFM->plugin_url . 'templates/classic/template-style.css', array( ), $WCFMvm->version );
			}
		}
		
		if( is_wcfm_registration_page() ) {
			if( isset( $_REQUEST['vmstep'] ) && $current_step && ( $current_step == 'thankyou' ) ) {
				wp_enqueue_style( 'wcfm_membership_thankyou_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-thankyou.css', array(), $WCFMvm->version );
				
				if( is_rtl() ) {
					wp_enqueue_style( 'wcfm_membership_thankyou_rtl_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-thankyou-rtl.css', array('wcfm_membership_thankyou_css'), $WCFMvm->version );
				}
			} else {
				wp_enqueue_style( 'wcfm_membership_registration_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-registration.css', array(), $WCFMvm->version );
				
				if( is_rtl() ) {
					wp_enqueue_style( 'wcfm_membership_registration_rtl_css',  $WCFMvm->library->css_lib_url_min . 'wcfmvm-style-membership-registration-rtl.css', array( 'wcfm_membership_registration_css' ), $WCFMvm->version );
				}
			}
		}
			
		if( is_wcfm_registration_page() || is_wcfm_membership_page() ) {
			// WCFMvm Custom CSS
			$wcfmvm_style_custom = get_option( 'wcfmvm_style_custom' );
			if( $wcfmvm_style_custom && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmvm_style_custom ) ) {
				wp_enqueue_style( 'wcfmvm_custom_css',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmvm_style_custom, array( 'wcfm_membership_css' ), $WCFMvm->version );
				wp_enqueue_style( 'wcfmvm_registration_custom_css',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmvm_style_custom, array( 'wcfm_membership_registration_css' ), $WCFMvm->version );
				wp_enqueue_style( 'wcfmvm_payment_custom_css',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmvm_style_custom, array( 'wcfm_membership_payment_css' ), $WCFMvm->version );
				wp_enqueue_style( 'wcfmvm_thank_you_custom_css',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmvm_style_custom, array( 'wcfm_membership_thankyou_css' ), $WCFMvm->version );
			}
	  }
 	}
 	
 	/**
 	 * PayPal request form
 	 */
 	function generate_paypal_request_form( $membership_id, $member_id ) {
 		global $WCFM, $WCFMvm;
 		
 		$membership_post = get_post( $membership_id );
 		$title = htmlspecialchars($membership_post->post_title);
 		$description = htmlspecialchars($membership_post->post_excerpt);
 		
 		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
 		$membership_payment_settings = array();
		if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
		$paypal_email = ( $membership_payment_settings['paypal_email'] ) ? $membership_payment_settings['paypal_email'] : '';
		$paypal_sandbox = isset( $membership_payment_settings['paypal_sandbox'] ) ? 'yes' : 'no';
 		
 		$subscription = (array) get_post_meta( $membership_id, 'subscription', true );
 		$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
		$one_time_amt = isset( $subscription['one_time_amt'] ) ? floatval($subscription['one_time_amt']) : '1';
		$trial_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '0';
		$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
		$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
		$billing_amt = isset( $subscription['billing_amt'] ) ? floatval($subscription['billing_amt']) : '1';
		$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
		$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
		$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : 1;
		$re_attempt = isset( $subscription['re_attempt'] ) ? 'yes' : 'no';
		
		$payment_currency = strtoupper(get_woocommerce_currency());
		$payment_currency = apply_filters( 'wcfm_membership_payment_currency', $payment_currency );
		
		if( !empty( $trial_period ) && empty( $trial_amt ) ) {
			$trial_amt = 1;
		}
		
		?>
		<form id="wcfm_membership_payment_form_paypal" class="wcfm wcfm_membership_payment_form wcfm_membership_payment_form_non_free" action="https://www<?php if( $paypal_sandbox == 'yes' ) echo '.sandbox'; ?>.paypal.com/cgi-bin/webscr" method="post">
		  <?php if( $subscription_type == 'one_time' ) { ?>
		  	<input type="hidden" name="cmd" value="_xclick">
		  <?php } else { ?>
		  	<input type="hidden" name="cmd" value="_xclick-subscriptions">
		  <?php } ?>
			<input type="hidden" name="charset" value="utf-8">
			<input type="hidden" name="bn" value="TipsandTricks_SP">
			<input type="hidden" name="business" value="<?php echo $paypal_email; ?>">
			<input type="hidden" name="currency_code" value="<?php echo $payment_currency; ?>">
			<input type="hidden" name="item_number" value="<?php echo $membership_id; ?>">
			<input type="hidden" name="item_name" value="<?php echo $title . ' - ' . $description; ?>">
			
			<?php if( $subscription_type == 'one_time' ) { ?>
		  	<input type="hidden" name="amount" value="<?php echo wcfmvm_membership_tax_price($one_time_amt); ?>" />
		  <?php } else { ?>
				<?php if( !empty( $trial_period ) ) { ?>
					<input type="hidden" name="a1" value="<?php echo wcfmvm_membership_tax_price($trial_amt); ?>">
					<input type="hidden" name="p1" value="<?php echo $trial_period; ?>">
					<input type="hidden" name="t1" value="<?php echo $trial_period_type; ?>">
				<?php } ?>
				
				<?php if( !empty( $billing_period ) && !empty( $billing_amt ) ) { ?>
					<input type="hidden" name="a3" value="<?php echo wcfmvm_membership_tax_price($billing_amt); ?>">
					<input type="hidden" name="p3" value="<?php echo $billing_period; ?>">
					<input type="hidden" name="t3" value="<?php echo $billing_period_type; ?>">
				<?php } ?>
				
				<?php if( $re_attempt == 'yes' ) { ?>
					<input type="hidden" name="sra" value="1">
				<?php } ?>
				
				<?php if ( $billing_period_count > 1 ) { ?>
					<input type="hidden" name="src" value="1" />
					<input type="hidden" name="srt" value="<?php echo $billing_period_count; ?>" />
				<?php } else if ( empty( $billing_period_count ) ) { ?>
					<input type="hidden" name="src" value="1" />
				<?php } ?>
			<?php } ?>
	
			<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="notify_url" value="<?php echo add_query_arg( 'wcfmvm_process_ipn', 'paypal_ipn', get_wcfm_membership_url() ); ?>">
			<input type="hidden" name="return" value="<?php echo apply_filters( 'wcfm_registration_thankyou_url', add_query_arg( 'vmstep', 'thankyou', get_wcfm_membership_url() ) ); ?>">
			<input type="hidden" name="cancel_return" value="<?php echo add_query_arg( 'vmstep', 'cancel', apply_filters( 'wcfm_change_membership_url', get_wcfm_membership_url() ) ); ?>">
			<input type="hidden" name="custom" value="<?php echo $member_id; ?>">
		<?php
 	}
 	
 	/**
 	 * Stripe request form
 	 */
 	function generate_stripe_request_form( $membership_id, $member_id ) {
 		global $WCFM, $WCFMvm;
 		
 		$membership_post = get_post( $membership_id );
 		$title = htmlspecialchars($membership_post->post_title);
 		$description = htmlspecialchars($membership_post->post_excerpt);
 		
 		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
 		$membership_payment_settings = array();
		if( isset( $wcfm_membership_options['membership_payment_settings'] ) ) $membership_payment_settings = $wcfm_membership_options['membership_payment_settings'];
		$payment_sandbox = isset( $membership_payment_settings['paypal_sandbox'] ) ? 'yes' : 'no';
		$stripe_published_key_live = isset( $membership_payment_settings['stripe_published_key_live'] ) ? $membership_payment_settings['stripe_published_key_live'] : '';
		$stripe_secret_key_live = isset( $membership_payment_settings['stripe_secret_key_live'] ) ? $membership_payment_settings['stripe_secret_key_live'] : '';
		$stripe_published_key_test = isset( $membership_payment_settings['stripe_published_key_test'] ) ? $membership_payment_settings['stripe_published_key_test'] : '';
		$stripe_secret_key_test = isset( $membership_payment_settings['stripe_secret_key_test'] ) ? $membership_payment_settings['stripe_secret_key_test'] : '';
		if ($payment_sandbox == 'yes') {
			$secret_key = $stripe_secret_key_test;
			$publishable_key = $stripe_published_key_test; //Use sandbox API key
    } else {
    	$secret_key = $stripe_secret_key_live;
			$publishable_key = $stripe_published_key_live; //Use live API key
    }
 		
 		$subscription = (array) get_post_meta( $membership_id, 'subscription', true );
 		$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
		$one_time_amt = isset( $subscription['one_time_amt'] ) ? floatval($subscription['one_time_amt']) : '1';
		$stripe_plan_id = isset( $subscription['stripe_plan_id'] ) ? $subscription['stripe_plan_id'] : '';
		$trial_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '0';
		$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
		$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
		$billing_amt = isset( $subscription['billing_amt'] ) ? floatval($subscription['billing_amt']) : '1';
		$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
		$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
		$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : 1;
		$period_options = array( 'D' => __( 'Day(s)', 'wc-multivendor-membership' ), 'M' => __( 'Month(s)', 'wc-multivendor-membership' ), 'Y' => __( 'Year(s)', 'wc-multivendor-membership' ) );
		$re_attempt = isset( $subscription['re_attempt'] ) ? 'yes' : 'no';
		$payment_currency = strtoupper(get_woocommerce_currency());
		$payment_currency = apply_filters( 'wcfm_membership_payment_currency', $payment_currency );
		
		if( !empty( $trial_period ) ) {
			$billing_amt = 0;
		}
		
		$zero_cents_currency = array('JPY', 'MGA', 'VND', 'KRW');
		if (in_array($payment_currency, $zero_cents_currency)) {
			$price_in_cents = $one_time_amt;
			$payment_amount = $billing_amt;
    } else {
			$price_in_cents = $one_time_amt * 100; //The amount (in cents). This value is passed to Stripe API.
			$payment_amount = $billing_amt; // / 100;
    }
    
    $button_label = __( 'Pay', 'wc-multivendor-membership' );
    
    if( $subscription_type == 'one_time' ) {
    	$payment_amount = $one_time_amt;
    	$pay_description = wcfmvm_membership_tax_price($one_time_amt) . ' ' . $payment_currency;
    	$notify_url = add_query_arg( 'wcfmvm_process_ipn', 'stripe_ipn', get_wcfm_membership_page() );
    } elseif( $stripe_plan_id ) {
    	// Stripe Plan Data Fetching
    	$plan_data = get_post_meta( $membership_id, 'stripe_plan_data', true );
    	if ( empty($plan_data) ) {
        if (version_compare(PHP_VERSION, '5.4.0', '>')) {
        	include_once( $WCFMvm->plugin_path . 'includes/libs/stripe-gateway/stripe-util-functions.php' );
					$result = WCFMvmStripeUtilFunctions::get_stripe_plan_info($secret_key, $stripe_plan_id);
					if ($result['success'] === false) {
						// some error occured, let's display it and stop processing the shortcode further
						wcfmvm_create_log( 'Stripe error occured: ' . $result['error_msg'] );
					} else {
						// plan data has been successfully retreived
						$plan_data = $result['plan_data'];
						// Let's update post_meta in order to not re-request the data again on each button display
						update_post_meta( $membership_id, 'stripe_plan_data', $plan_data );
					}
				}
      }
    	
      // Plan Pricing description
      $price_in_cents = $billing_amt * 100;
    	$pay_description = wcfmvm_membership_tax_price($billing_amt) . ' ' . $payment_currency;
    	if ($billing_period_count == 1) {
        $description .= ' / ' . $period_options[$billing_period_type];
			} else {
				$description .= ' every ' . $billing_period_count . ' ' . $period_options[$billing_period_type];
			}
			$button_label = __( 'Subscribe', 'wc-multivendor-membership' );
			
    	$notify_url = add_query_arg( 'wcfmvm_process_ipn', 'stripe_subs_ipn', get_wcfm_membership_page() );
    } else {
    	echo '<div class="wcfm-message wcfm-warning" tabindex="-1" style="display:block;"><span class="wcicon-status-pending"></span>';
    	_e( 'Stripe Plan ID missing.', 'wc-multivendor-membership' );
    	printf( __( '%sHow can I have this?%s', 'wc-multivendor-membership' ), '&nbsp;&nbsp;<a style="font-weight:400;color:#00798b;" target="_blank" href="https://wclovers.com/blog/how-can-i-have-stripe-plan-id-for-recurring-membership-plan/">', '</a>' );
    	echo '</div>';
    	return false;
    }
    
    
    // SCA Enable
    
    $uniqid = md5( uniqid() );
		$ref_id = 'wcfm_' . $uniqid . '|' . $membership_id . '|' . $member_id;
	
		 // home_url() . '/?swpm_process_stripe_sca_subscription=1&ref_id=' . $ref_id; //We are going to use it to do post payment processing.
	
		$current_url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		
		//Include the Stripe library.
		if( !class_exists( 'Stripe\Stripe' ) ) {
			include( $WCFMvm->plugin_path . 'includes/libs/stripe-gateway/init.php');
		}
		
		try {
			\Stripe\Stripe::setApiKey( $secret_key );
			
			if( $subscription_type == 'one_time' ) {
				//Return, cancel, notifiy URLs
				$notify_url = add_query_arg( array( 'wcfmvm_process_ipn' => 'stripe_sca_ipn',
																				'ref_id' => $ref_id
																			), get_wcfm_membership_page() );
		
				$opts = array(
					'payment_method_types'       => array( 'card' ),
					'client_reference_id'        => $ref_id,
					'billing_address_collection' => 'auto',
					'line_items'                 => array(
						array(
							'name'        => $title,
							'description' => number_format( $payment_amount, 2 ) . ' ' . $payment_currency,
							'amount'      => wcfmvm_membership_tax_price($price_in_cents),
							'currency'    => $payment_currency,
							'quantity'    => 1,
						),
					),
					'success_url'                => $notify_url,
					'cancel_url'                 => $current_url,
				);
			} else {
				//Return, cancel, notifiy URLs
				$notify_url = add_query_arg( array( 'wcfmvm_process_ipn' => 'stripe_sca_subs_ipn',
																				'ref_id' => $ref_id
																			), get_wcfm_membership_page() );
				$opts = array(
					'payment_method_types'       => array( 'card' ),
					'client_reference_id'        => $ref_id,
					'billing_address_collection' => 'auto',
					'subscription_data'          => array(
						'items' => array( array( 'plan' => $stripe_plan_id ) ),
					),
					'success_url'                => $notify_url,
					'cancel_url'                 => $current_url,
				);
		
				$trial_period = absint( $trial_period );
				if ( $trial_period ) {
					$opts['subscription_data']['trial_period_days'] = $trial_period;
				}
			}
			
			$session = \Stripe\Checkout\Session::create( $opts );
		} catch ( Exception $e ) {
			$err = $e->getMessage();
			echo '<p class="swpm-red-box">' . $err . '</p>';
			return false;
		}
	
		/* === Stripe Buy Now Button Form === */
		$output  = '';
		$output .= "<form id='wcfm_membership_payment_form_stripe' class='wcfm wcfm_membership_payment_form wcfm_membership_payment_form_non_free' action='" . $notify_url . "' METHOD='POST'> ";
		$output .= "<div style='display: none !important'>";
		$output .= '<script src="https://js.stripe.com/v3/"></script>';
		$output .= "<link rel='stylesheet' href='https://checkout.stripe.com/v3/checkout/button.css' type='text/css' media='all' />";
		ob_start();
		?>
		<script>
			var stripe = Stripe('<?php echo esc_js( $publishable_key ); ?>');
			jQuery('#wcfm_membership_payment_form_stripe').on('submit',function(e) {
				e.preventDefault();
				stripe.redirectToCheckout({
					sessionId: '<?php echo esc_js( $session->id ); ?>'
				}).then(function (result) {
				});
			});
		</script>
		<?php
		$output .= ob_get_clean();
		$output .= '</div>';
	
		echo $output;
		return true;
    
		?>
		<form id="wcfm_membership_payment_form_stripe" class="wcfm wcfm_membership_payment_form wcfm_membership_payment_form_non_free" action="<?php echo $notify_url; ?>" method="post">
		  <div style="display: none !important">
		  <script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="<?php echo $publishable_key; ?>"
																																									data-panel-label="<?php echo $button_label; ?>"
																																									data-amount="<?php echo wcfmvm_membership_tax_price($price_in_cents); ?>"
																																									data-name="<?php echo $title; ?>"
																																									data-description="<?php echo $pay_description; ?>"
																																									data-label="<?php _e( 'Proceed', 'wc-multivendor-membership' ); ?>"
																																									data-currency="<?php echo $payment_currency; ?>"
																																									data-locale="auto"
																																									></script>
			</div>
		  
			<input type="hidden" name="item_price" value="<?php echo wcfmvm_membership_tax_price($payment_amount); ?>">
			<input type="hidden" name="currency_code" value="<?php echo $payment_currency; ?>">
			<input type="hidden" name="item_number" value="<?php echo $membership_id; ?>">
			<input type="hidden" name="item_name" value="<?php echo $title . ' - ' . $pay_description; ?>">
			<input type="hidden" value="<?php echo $member_id; ?>" name="custom" />
		<?php	
		return true;
	}
}