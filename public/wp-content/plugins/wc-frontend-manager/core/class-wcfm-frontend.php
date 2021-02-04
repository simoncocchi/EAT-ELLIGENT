<?php

/**
 * WCFM Frontend Class
 *
 * @version		1.0.0
 * @package		wcfm/core
 * @author 		WC Lovers
 */
class WCFM_Frontend {
	
 	public function __construct() {
 		
 		// WCFM Page Template
 		add_action( 'page_template', array( &$this, 'wcfm_dashboard_template' ) );
 		
 		// WCFM End Point Title
 		add_filter( 'the_title', array( &$this, 'wcfm_page_endpoint_title' ), 9 );
 		
 		// Check User Authentication to Access WCFM Pages
 		add_action( 'template_redirect', array(&$this, 'wcfm_template_redirect' ), 500 );
 		
 		// WCFM Icon at Shop
 		add_action( 'woocommerce_before_shop_loop', array( &$this, 'wcfm_home' ), 5 );
 		add_action( 'woocommerce_after_shop_loop', array( &$this, 'wcfm_home' ), 5 );
 		
 		// Product Manage from Archive Pages
 		add_action( 'woocommerce_before_shop_loop_item', array(&$this, 'wcfm_product_manage'), 4 );
		add_action( 'woocommerce_before_single_product_summary', array(&$this, 'wcfm_product_manage'), 4 );
		
		// WCFM Page Header panels
    add_action( 'wcfm_page_heading', array($this, 'wcfm_page_heading'), 10 );
    
    // WCFM Product Manager Taxonomy view - 3.0.3
    add_filter( 'wcfm_is_category_checklist', array(&$this, 'wcfm_is_category_checklist') );
    
    // WCFM Product Manager Tags Input view - 6.3.0
    add_filter( 'wcfm_is_tags_input', array( &$this, 'wcfm_is_tags_input' ) );
    
    // WCFM Analytics Data Save - Version 2.2.5
    add_action( 'wp_footer', array( &$this, 'wcfm_save_page_analytics_data') );
    
    // WCFM Ultimate Inactive Notice
    add_filter( 'is_wcfmu_inactive_notice_show', array( &$this, 'is_wcfmu_inactive_notice_show') );
    
    // WCfM Product Pre-defined Attributes
    add_action( 'wcfm_products_manage_attributes', array( &$this, 'wcfm_products_manage_select_attributes' ) );
    
    // WCFM Dashboard Page Body Class
		add_filter( 'body_class', array( &$this, 'wcfm_dashboard_body_classes' ) );	
		
		// Enfold Theme media library load by force
		add_filter( 'avf_enqueue_wp_mediaelement', array( &$this, 'wcfm_avf_enqueue_wp_mediaelement' ), 999, 2 );	
    
		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( &$this, 'wcfm_scripts' ), 15 );
		
		// WCFM scripts Fix
		add_action( 'wp_enqueue_scripts', array( &$this, 'wcfm_scripts_fix' ), 10000 );
		
		// Enqueue styles
		add_action( 'wp_enqueue_scripts', array( &$this, 'wcfm_styles' ), 15 );
		
		// Set User Locale
		/*if( is_user_logged_in() ) {
			global $_SESSION;
			$user_id = get_current_user_id();
			$ulocale = get_user_meta( $user_id, 'locale', true );
			if ( 'en_US' === $ulocale || 'en' === $ulocale ) {
				$ulocale = 'en';
			} elseif ( '' === $ulocale ) {
				//$locale = 'site-default';
			}
			$_SESSION['wcfm_my_locale'] = $ulocale;
		}*/
 	}
 	
 	/**
 	 * WCFM Page template if full screen selected
 	 */
	function wcfm_dashboard_template( $page_template ) {
		global $WCFM, $post;
		
		// WCfM Dashboard template
		if ( wc_post_content_has_shortcode( 'wc_frontend_manager' ) && is_user_logged_in() ) {
			if( function_exists( 'et_theme_builder_frontend_override_template' ) && apply_filters( 'wcfm_is_allow_divi_builder_template', true ) ) {
				$layouts         = et_theme_builder_get_template_layouts();
				$override_header = et_theme_builder_overrides_layout( ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE );
				$override_footer = et_theme_builder_overrides_layout( ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE );
				if ( $override_header || $override_footer ) {
					return $page_template;
				}
			}
			
			$wcfm_options = $WCFM->wcfm_options;
			$is_dashboard_full_view_disabled = isset( $wcfm_options['dashboard_full_view_disabled'] ) ? $wcfm_options['dashboard_full_view_disabled'] : 'no';
			$is_dashboard_theme_header_disabled = isset( $wcfm_options['dashboard_theme_header_disabled'] ) ? $wcfm_options['dashboard_theme_header_disabled'] : 'no';
			if( $is_dashboard_full_view_disabled != 'yes' ) {
				$template_path = WC()->template_path();
				$skin_path     = $WCFM->plugin_path . 'templates/classic/';
				if( $is_dashboard_theme_header_disabled == 'yes' ) $skin_path     = $WCFM->plugin_path . 'templates/default/';
				$page_template = wc_locate_template( 'wcfm-content.php', $template_path, $skin_path );
			}
		}
		
		// WCFM Marketplace Store Setup
		if( wcfm_is_vendor() && isset( $_GET['store-setup'] ) ) {
			$template_path = WC()->template_path();
			$skin_path     = $WCFM->plugin_path . 'templates/classic/';
			$skin_path     = $WCFM->plugin_path . 'templates/default/';
			$page_template = wc_locate_template( 'wcfm-content.php', $template_path, $skin_path );
		}
		
		// Add/Edit Listings page template
		if( apply_filters( 'wcfm_is_allow_manage_listings_wcfm_wrapper', true ) && wcfm_is_allow_wcfm() ) {
			if ( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() && is_user_logged_in() ) {
				$job_dashboard_page = get_option( 'job_manager_job_dashboard_page_id' );
				$add_listings_page = get_option( 'job_manager_submit_job_form_page_id' );
				if( ( $add_listings_page && is_object( $post ) && ( $add_listings_page == $post->ID ) ) || ( $job_dashboard_page && is_object( $post ) && ( $job_dashboard_page == $post->ID ) && isset( $_GET['action'] ) && ( in_array( $_GET['action'], array( 'edit', 'mark_filled', 'mark_not_filled' ) ) ) ) ) {
					$wcfm_options = $WCFM->wcfm_options;
					$is_dashboard_full_view_disabled = isset( $wcfm_options['dashboard_full_view_disabled'] ) ? $wcfm_options['dashboard_full_view_disabled'] : 'no';
					$is_dashboard_theme_header_disabled = isset( $wcfm_options['dashboard_theme_header_disabled'] ) ? $wcfm_options['dashboard_theme_header_disabled'] : 'no';
					if( $is_dashboard_full_view_disabled != 'yes' ) {
						$template_path = WC()->template_path();
						$skin_path     = $WCFM->plugin_path . 'templates/classic/';
						if( $is_dashboard_theme_header_disabled == 'yes' ) $skin_path     = $WCFM->plugin_path . 'templates/default/';
						$page_template = wc_locate_template( 'wcfm-content.php', $template_path, $skin_path );
					}
				}
			}
		}
		
		// WCFM Marketplace page template
		/*if ( $WCFM->is_marketplace && ( $WCFM->is_marketplace == 'wcfmmarketplace' ) && wcfm_is_store_page() ) {
			$template_path = WC()->template_path();
			$skin_path     = $WCFM->plugin_path . 'templates/classic/';
			$page_template = wc_locate_template( 'wcfm-content.php', $template_path, $skin_path );
		}*/

		return $page_template;
	}
 	
 	/**
	 * Replace a page title with the endpoint title.
	 * @param  string $title
	 * @return string
	 */
	function wcfm_page_endpoint_title( $title ) {
		global $WCFM, $WCFM_Query, $wp_query;
	
		if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_wcfm_page() && is_wcfm_endpoint_url() ) {
			$endpoint = $WCFM_Query->get_current_endpoint();
	
			if ( $endpoint_title = $WCFM_Query->get_endpoint_title( $endpoint ) ) {
				$title = $endpoint_title;
			}
			
			remove_filter( 'the_title', array( &$this, 'wcfm_page_endpoint_title' ), 9 );
		}
	
		return $title;
	}
	
	/**
	 * Template redirect function
	 * @return void
	*/
	function wcfm_template_redirect() {
		global $WCFM, $wp, $WCFM_Query;
		
		// WCfM old log permalink slug support 
		if( apply_filters( 'wcfm_is_allow_old_urls_redirect', true ) ) {
			$is_wcfm = strpos( $wp->request, 'wcfm-' );
			if( $is_wcfm !== false ) {
				wp_safe_redirect( home_url( str_replace( 'wcfm-', '', $wp->request ) ) );
				exit();
			}
		}
		
		// If user not loggedin then reirect to Home page
		if( !is_user_logged_in() && is_wcfm_page() ) {
      wp_safe_redirect( apply_filters( 'wcfm_restrict_redirect_url', get_permalink( wc_get_page_id( 'myaccount' ) ) ) );
      exit();
    }
    
    // If user loggedin and applied for vendor
    if( is_user_logged_in() && is_wcfm_page() && function_exists( 'get_wcfm_membership_url' ) ) {
    	$user = wp_get_current_user();
			$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
			if ( !array_intersect( $allowed_roles, (array) $user->roles ) )  {
				$member_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
				$application_status = get_user_meta( $member_id, 'wcfm_membership_application_status', true );
				if( $application_status && ( $application_status == 'pending' ) ) {
					wp_safe_redirect( apply_filters( 'wcfm_change_membership_url', get_wcfm_membership_url() ) );
					exit;
				} else {
					wp_safe_redirect( apply_filters( 'wcfm_restrict_redirect_url', get_permalink( wc_get_page_id( 'myaccount' ) ) ) );
					exit;
				}
			}
		}
    
    // If user loggedin but not admin or shop manager then reirect to MyAccount page
		if( is_user_logged_in() && is_wcfm_page() ) {
			$user = wp_get_current_user();
			$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
			if ( !array_intersect( $allowed_roles, (array) $user->roles ) )  {
				wp_safe_redirect( apply_filters( 'wcfm_restrict_redirect_url', get_permalink( wc_get_page_id( 'myaccount' ) ) ) );
				exit();
			}
		}
		
		// If user loggedin but not admin or shop manager then reirect to MyAccount page
		if( is_user_logged_in() && is_wcfm_page() ) {
			$user = wp_get_current_user();
			if ( in_array( apply_filters( 'wcfm_delivery_boy_user_role', 'wcfm_delivery_boy' ), $user->roles ) && function_exists( 'get_wcfm_deliveries_url' ) ) {
				$wcfm_endpoints = $WCFM_Query->get_query_vars();
				$is_endpoint = false;
				foreach ( $wcfm_endpoints as $key => $value ) {
					if ( isset( $wp->query_vars[ $key ] ) ) {
						$is_endpoint = true;
					}
				}
				if( !$is_endpoint ) {
					wp_safe_redirect(  get_wcfm_deliveries_url() );
					exit();
				}
			}
		}
		
		/** Vendor Dashboard conflict redirect **/
		if( apply_filters( 'wcfm_is_allow_multivendor_dashboard_redirect', true ) ) {
			// WCMp
			if( class_exists('WCMp') && function_exists( 'is_vendor_dashboard' ) ) {
				if( is_user_logged_in() && is_vendor_dashboard() ) {
					$user = wp_get_current_user();
					$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
					if ( array_intersect( $allowed_roles, (array) $user->roles ) )  {
						wp_safe_redirect(  get_wcfm_url() );
						exit();
					}
				}
			}
			
			// WC Vendors
			if( class_exists('WC_Vendors') ) {
				if( is_user_logged_in() ) {
					if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
						$vendor_dashboard_page = WC_Vendors::$pv_options->get_option( 'vendor_dashboard_page' );
						$shop_settings_page    = WC_Vendors::$pv_options->get_option( 'shop_settings_page' );
					} else {
						$vendor_dashboard_page = get_option('wcvendors_display_label_sold_by_enable');
						$shop_settings_page    = get_option('wcvendors_shop_settings_page_id');
					}
					if ( $vendor_dashboard_page && is_page( $vendor_dashboard_page ) || $shop_settings_page && is_page( $shop_settings_page ) ) {
						$user = wp_get_current_user();
						$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
						if ( array_intersect( $allowed_roles, (array) $user->roles ) )  {
							wp_safe_redirect(  get_wcfm_url() );
							exit();
						}
					}
				}
			}
			
			// WC Vendors Pro
			if( class_exists('WCVendors_Pro') ) {
				if( is_user_logged_in() ) {
					if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
						$pro_dashboard_page 	= WCVendors_Pro::get_option( 'dashboard_page_id' );
					} else {
						$pro_dashboard_page 	= get_option( 'wcvendors_dashboard_page_id' );
					}
					if ( $pro_dashboard_page && is_page( $pro_dashboard_page ) ) {
						$user = wp_get_current_user();
						$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
						if ( array_intersect( $allowed_roles, (array) $user->roles ) )  {
							wp_safe_redirect(  get_wcfm_url() );
							exit();
						}
					}
				}
			}
			
			// Dokan
			if( class_exists('WeDevs_Dokan') ) {
				if( is_user_logged_in() ) {
					$seller_dashboard = dokan_get_option( 'dashboard', 'dokan_pages' );
					if ( $seller_dashboard && is_page( $seller_dashboard ) ) {
						$user = wp_get_current_user();
						$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
						if ( array_intersect( $allowed_roles, (array) $user->roles ) )  {
							wp_safe_redirect(  get_wcfm_url() );
							exit();
						}
					}
				}
			}
		}
	}
	
	/**
	 * WCFM Home at Archive Pages
	 */
	function wcfm_home() {
 		global $WCFM;
 		
 		$wcfm_options = $WCFM->wcfm_options;
 		$is_quick_access_disabled = isset( $wcfm_options['quick_access_disabled'] ) ? $wcfm_options['quick_access_disabled'] : 'no';
 		if( $is_quick_access_disabled == 'yes' ) return;
 			
 		if( !is_user_logged_in() ) return;
 		if( !apply_filters( 'wcfm_is_allow_catalog_quick_access', true ) ) return;
		$user = wp_get_current_user();
		$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
		if ( !array_intersect( $allowed_roles, (array) $user->roles ) )  return;
		
		$quick_access_image_url = isset( $wcfm_options['wcfm_quick_access_icon'] ) ? wcfm_get_attachment_url( $wcfm_options['wcfm_quick_access_icon'] ) : $WCFM->plugin_url . '/assets/images/wcfm-30x30.png';
 		echo '<a href="' . get_wcfm_page() . '"><img class="text_tip" data-tip="' . __( 'Dashboard', 'wc-frontend-manager' ) . '" id="wcfm_home" src="' . $quick_access_image_url . '" width="30" alt="' . __( 'Dashboard', 'wc-frontend-manager' ) . '" /></a>';
 	}
	
	/**
	 * WCFM Product Manage from Archive Pages
	 */
	function wcfm_product_manage() {
		global $WCFM, $post, $woocommerce_loop;
		
		if( !$post || !is_object( $post ) ) return;
		
		if( class_exists( 'WCMp' ) ) {
			global $WCMp;
			if( $WCMp ) {
				remove_action( 'woocommerce_before_shop_loop_item', array( $WCMp->product, 'forntend_product_edit' ), 5 );
				remove_action( 'woocommerce_before_single_product_summary', array( $WCMp->product, 'forntend_product_edit' ), 5 );
			}
		}

		if( !is_user_logged_in() ) return;
		if( !apply_filters( 'wcfm_is_allow_catalog_product_manage', true ) ) return;
		$user = wp_get_current_user();
		$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
		if ( !array_intersect( $allowed_roles, (array) $user->roles ) )  return;
				
		$current_user = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		if( wcfm_is_vendor() && ( $current_user != $post->post_author ) ) return;
		
		$pro_id = $post->ID;
		$_product = wc_get_product($pro_id);
		
		?>
		<div class="wcfm_buttons">
		  <?php do_action( 'wcfm_product_manage', $pro_id, $_product ); ?>
		  <?php if( apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $pro_id ) ) { ?>
				<a class="wcfm_button" href="<?php echo get_wcfm_edit_product_url( $pro_id, $_product ); ?>"> <span class="wcfmfa fa-edit text_tip" data-tip="<?php echo esc_attr__( 'Edit', 'wc-frontend-manager' ); ?>"></span> </a>
		  <?php } ?>
		  <?php if( apply_filters( 'wcfm_is_allow_delete_products', true ) && apply_filters( 'wcfm_is_allow_delete_specific_products', true, $pro_id ) ) { ?>
		  	<span class="wcfm_button_separator">|</span>
		  	<a class="wcfm_button wcfm_delete_product" href="#" data-proid="<?php echo $pro_id; ?>"><span class="wcfmfa fa-trash-alt text_tip" data-tip="<?php echo esc_attr__( 'Delete', 'wc-frontend-manager' ); ?>"></span> </a>
		  <?php } ?>
		</div>
		<?php
		
	}
	
	/**
	 * WCFM Pages Header Panels
	 * 
	 * @since 2.3.2
	 */
	function wcfm_page_heading() {
		global $WCFM, $wpdb;
		$WCFM->template->get_template( 'wcfm-view-header-panels.php' );
	}
	
	/**
	 * WCFM Category view as Checklist
	 */
	function wcfm_is_category_checklist( $is_checklist_view ) {
		global $WCFM;
		$wcfm_options = $WCFM->wcfm_options;
	  $is_checklist_view_disabled = isset( $wcfm_options['checklist_view_disabled'] ) ? $wcfm_options['checklist_view_disabled'] : 'no';
	  if( $is_checklist_view_disabled == 'yes' ) $is_checklist_view = false;
		
		return $is_checklist_view;
	}
	
	/**
	 * WCFM Tags Input as Drop-down
	 */
	function wcfm_is_tags_input( $is_tags_input ) {
		global $WCFM;
		$wcfm_options = $WCFM->wcfm_options;
	  $is_tags_input_disabled = isset( $wcfm_options['tags_input_disabled'] ) ? $wcfm_options['tags_input_disabled'] : 'no';
	  if( $is_tags_input_disabled == 'yes' ) $is_tags_input = false;
		
		return $is_tags_input;
	}
	
	function getIP() {
		if( apply_filters( 'wcfm_is_allow_wc_geolocate', true ) && class_exists( 'WC_Geolocation' ) ) {
			return WC_Geolocation::get_external_ip_address();
		} else {
			foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
				 if (array_key_exists($key, $_SERVER) === true) {
					foreach (explode(',', $_SERVER[$key]) as $ip) {
						if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
							return $ip;
						}
					 }
				 }
			 }
		 }
   }
   
	/**
	 * Saving WCFM Page Analytics Data
	 * @since 2.2.5
	 */
	function wcfm_save_page_analytics_data() {
		global $WCFM, $_SERVER, $post, $wpdb, $_SESSION, $wp;
		
		//$_SESSION['wcfm_pages'] = array( 'shop' => 'no', 'stores' => array(), 'products' => array() );
		//if( !session_id() ) session_start();
		$todate = date( 'Y-m-d', current_time( 'timestamp', 0 ) );
		
		if( !isset($_SERVER['HTTP_REFERER']) ) $_SERVER['HTTP_REFERER'] = '';
		
		$ip_address = $this->getIP();
		
		if( is_user_logged_in() && !isset( $_SESSION['location'] ) ) {
			$user_location = get_user_meta( get_current_user_id(), 'wcfm_user_location', true );
			if( $user_location ) $_SESSION['location'] = $user_location;
		}
		
		if( !isset( $_SESSION['location'] ) || !isset( $_SESSION['location']['country'] ) || !isset( $_SESSION['location']['state'] ) || !isset( $_SESSION['location']['city'] ) ) {
			if( apply_filters( 'wcfm_is_allow_geo_lookup', true ) && ( is_user_logged_in() || WCFM_Dependencies::wcfma_plugin_active_check() ) ) {
				$target_url = 'http://getcitydetails.geobytes.com/GetCityDetails?fqcn='. $ip_address;
				$request    = wp_remote_get( $target_url );
				if( !is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) == 200 ) {
					$data = json_decode( wp_remote_retrieve_body( $request ) );
					if( $data && is_object( $data ) ) {
						$_SESSION['location']['country'] = $data->geobytesinternet;
						$_SESSION['location']['state'] = $data->geobytescode;
						$_SESSION['location']['city'] = $data->geobytescity;
						
						// Save Users Location for Future
						if( is_user_logged_in() ) {
							$user_location = array( 'country' => $data->geobytesinternet, 'state' => $data->geobytescode, 'city' => $data->geobytescity );
							update_user_meta( get_current_user_id(), 'wcfm_user_location', $user_location );
						}
					} else {
						$_SESSION['location']['country'] = '';
						$_SESSION['location']['state'] = '';
						$_SESSION['location']['city'] = '';
					}
				} else {
					$_SESSION['location']['country'] = '';
					$_SESSION['location']['state'] = '';
					$_SESSION['location']['city'] = '';
				}
				//echo '<b>'. $this->getIP() .'</b> resolves to: '. $data->geobytesinternet . "::" . $data->geobytescode . "::" . $data->geobytescity . "::" . var_dump($data);
			} else {
				$_SESSION['location']['country'] = '';
				$_SESSION['location']['state'] = '';
				$_SESSION['location']['city'] = '';
			}
		}
		
		// vendor store
		$is_marketplace = wcfm_is_marketplace();
		
		if( is_shop() ) {
			$wc_shop = true;
			if( $is_marketplace == 'wcvendors' ) {
		  	if ( WCV_Vendors::is_vendor_page() ) {
		  		$wc_shop = false;
		  		$vendor_shop 		= urldecode( get_query_var( 'vendor_shop' ) );
		  		$vendor_id   		= WCV_Vendors::get_vendor_id( $vendor_shop );
		  		if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['stores'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['stores'] ) && !in_array( $vendor_id, $_SESSION['wcfm_pages']['stores'] ) ) ) {
						// wcfm_detailed_analysis Query
						$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																			VALUES
																			(0, 1, 0, 0, {$vendor_id}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
						$wpdb->query($wcfm_detailed_analysis);
						
						// wcfm_daily_analysis Query
						$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																			VALUES
																			(0, 1, 0, 0, {$vendor_id}, 1, '{$todate}')
																			ON DUPLICATE KEY UPDATE
																			count = count+1";
						$wpdb->query($wcfm_daily_analysis);
						
						// Session store
						$_SESSION['wcfm_pages']['stores'][] = $vendor_id;
					}
		  	}
		  } elseif( $is_marketplace == 'wcfmmarketplace' ) {
				if( function_exists( 'wcfm_is_store_page' ) && wcfm_is_store_page() ) {
					$wc_shop = false;
					$custom_store_url = get_option( 'wcfm_store_url', 'store' );
					$store_name = get_query_var( $custom_store_url );
					$vendor_id  = 0;
					if ( !empty( $store_name ) ) {
						$store_user = get_user_by( 'slug', $store_name );
					}
					if( $store_user ) {
						$vendor_id   		= $store_user->ID;
					}
					if( $vendor_id ) {
						if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['stores'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['stores'] ) && !in_array( $vendor_id, $_SESSION['wcfm_pages']['stores'] ) ) ) {
							// wcfm_detailed_analysis Query
							$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																				(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																				VALUES
																				(0, 1, 0, 0, {$vendor_id}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
							$wpdb->query($wcfm_detailed_analysis);
							
							// wcfm_daily_analysis Query
							$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																				(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																				VALUES
																				(0, 1, 0, 0, {$vendor_id}, 1, '{$todate}')
																				ON DUPLICATE KEY UPDATE
																				count = count+1";
							$wpdb->query($wcfm_daily_analysis);
							
							// Session store
							$_SESSION['wcfm_pages']['stores'][] = $vendor_id;
						}
					}
				}
			}
		  
		  if( $wc_shop && $post ) {
		  	if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['shop'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['shop'] ) && ( 'no' == $_SESSION['wcfm_pages']['shop'] ) ) ) {
		  		$post_author = $post->post_author;
		  		if( !$post_author ) $post_author = 1;
					// wcfm_detailed_analysis Query
					$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																		(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																		VALUES
																		(1, 0, 0, 0, {$post_author}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
					$wpdb->query($wcfm_detailed_analysis);
					
					// wcfm_daily_analysis Query
					$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																		(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																		VALUES
																		(1, 0, 0, 0, {$post_author}, 1, '{$todate}')
																		ON DUPLICATE KEY UPDATE
																		count = count+1";
					$wpdb->query($wcfm_daily_analysis);
					
					// Session store
					$_SESSION['wcfm_pages']['shop'] = 'yes';
				}
			}
		} elseif( is_product_category() || ( is_tax() && !is_tax('dc_vendor_shop') && !is_tax('wcpv_product_vendors') ) ) {
			$product_category = get_queried_object()->term_id;
			//echo get_query_var( 'taxonomy' );
			//echo get_query_var( 'term' );
			//echo "aaa:::" . $product_category;
			if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['product_category'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['product_category'] ) && !in_array( $product_category, $_SESSION['wcfm_pages']['product_category'] ) ) ) {
				// wcfm_detailed_analysis Query
				$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																	(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																	VALUES
																	(9, 0, 0, {$product_category}, 1, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
				$wpdb->query($wcfm_detailed_analysis);
				
				// wcfm_daily_analysis Query
				$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																	(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																	VALUES
																	(9, 0, 0, {$product_category}, 1, 1, '{$todate}')
																	ON DUPLICATE KEY UPDATE
																	count = count+1";
				$wpdb->query($wcfm_daily_analysis);
				
				// Session store
				$_SESSION['wcfm_pages']['product_category'][] = $product_category;
			}
		} elseif( is_product() ) {
			if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['products'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['products'] ) && !in_array( $post->ID, $_SESSION['wcfm_pages']['products'] ) ) ) {
				$post_author = $post->post_author;
		  	if( !$post_author ) $post_author = 1;
				// wcfm_detailed_analysis Query
				$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																	(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																	VALUES
																	(0, 0, 1, {$post->ID}, {$post_author}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
				$wpdb->query($wcfm_detailed_analysis);
				
				// wcfm_daily_analysis Query
				$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																	(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																	VALUES
																	(0, 0, 1, {$post->ID}, {$post_author}, 1, '{$todate}')
																	ON DUPLICATE KEY UPDATE
																	count = count+1";
				$wpdb->query($wcfm_daily_analysis);
				
				$wcfm_product_views = (int) get_post_meta( $post->ID, '_wcfm_product_views', true );
				if( !$wcfm_product_views ) $wcfm_product_views = 1;
				else $wcfm_product_views += 1;
				update_post_meta( $post->ID, '_wcfm_product_views', $wcfm_product_views );
				
				// Session store
				$_SESSION['wcfm_pages']['products'][] = $post->ID;
			}
		} elseif( is_singular( 'job_listing' ) ) {
			if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['listings'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['listings'] ) && !in_array( $post->ID, $_SESSION['wcfm_pages']['listings'] ) ) ) {
				$wcfm_listing_views = (int) get_post_meta( $post->ID, '_wcfm_listing_views', true );
				if( !$wcfm_listing_views ) $wcfm_listing_views = 1;
				else $wcfm_listing_views += 1;
				update_post_meta( $post->ID, '_wcfm_listing_views', $wcfm_listing_views );
				
				// Session store
				$_SESSION['wcfm_pages']['listings'][] = $post->ID;
			}
		} elseif( is_singular( 'post' ) ) {
			if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['articles'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['articles'] ) && !in_array( $post->ID, $_SESSION['wcfm_pages']['articles'] ) ) ) {
				$wcfm_article_views = (int) get_post_meta( $post->ID, '_wcfm_article_views', true );
				if( !$wcfm_article_views ) $wcfm_article_views = 1;
				else $wcfm_article_views += 1;
				update_post_meta( $post->ID, '_wcfm_article_views', $wcfm_article_views );
				
				// Session store
				$_SESSION['wcfm_pages']['articles'][] = $post->ID;
			}
		} else {
		  if( $is_marketplace == 'wcmarketplace' ) {
		  	if (is_tax('dc_vendor_shop')) {
		  		$vendor = get_wcmp_vendor_by_term(get_queried_object()->term_id);
		  		if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['stores'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['stores'] ) && !in_array( $vendor->id, $_SESSION['wcfm_pages']['stores'] ) ) ) {
						// wcfm_detailed_analysis Query
						$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																			VALUES
																			(0, 1, 0, 0, {$vendor->id}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
						$wpdb->query($wcfm_detailed_analysis);
						
						// wcfm_daily_analysis Query
						$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																			VALUES
																			(0, 1, 0, 0, {$vendor->id}, 1, '{$todate}')
																			ON DUPLICATE KEY UPDATE
																			count = count+1";
						$wpdb->query($wcfm_daily_analysis);
						
						// Session store
						$_SESSION['wcfm_pages']['stores'][] = $vendor->id;
					}
		  	}
		  } elseif( $is_marketplace == 'wcpvendors' ) {
		  	if (is_tax('wcpv_product_vendors')) {
		  		$vendor_shop = get_queried_object()->term_id;
		  		if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['stores'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['stores'] ) && !in_array( $vendor_shop, $_SESSION['wcfm_pages']['stores'] ) ) ) {
						// wcfm_detailed_analysis Query
						$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																			VALUES
																			(0, 1, 0, 0, {$vendor_shop}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
						$wpdb->query($wcfm_detailed_analysis);
						
						// wcfm_daily_analysis Query
						$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																			VALUES
																			(0, 1, 0, 0, {$vendor_shop}, 1, '{$todate}')
																			ON DUPLICATE KEY UPDATE
																			count = count+1";
						$wpdb->query($wcfm_daily_analysis);
						
						// Session store
						$_SESSION['wcfm_pages']['stores'][] = $vendor_shop;
					}
		  	}
		  } elseif( $is_marketplace == 'dokan' ) {
		  	if( dokan_is_store_page() ) {
		  		$custom_store_url = dokan_get_option( 'custom_store_url', 'dokan_general', 'store' );
		  		$store_name = get_query_var( $custom_store_url );
		  		$vendor_id  = 0;
		  		if ( !empty( $store_name ) ) {
            $store_user = get_user_by( 'slug', $store_name );
          }
		  		$vendor_id   		= $store_user->ID;
		  		if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['stores'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['stores'] ) && !in_array( $vendor_id, $_SESSION['wcfm_pages']['stores'] ) ) ) {
						// wcfm_detailed_analysis Query
						$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																			VALUES
																			(0, 1, 0, 0, {$vendor_id}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
						$wpdb->query($wcfm_detailed_analysis);
						
						// wcfm_daily_analysis Query
						$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																			(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																			VALUES
																			(0, 1, 0, 0, {$vendor_id}, 1, '{$todate}')
																			ON DUPLICATE KEY UPDATE
																			count = count+1";
						$wpdb->query($wcfm_daily_analysis);
						
						// Session store
						$_SESSION['wcfm_pages']['stores'][] = $vendor_id;
					}
				}
			} elseif( $is_marketplace == 'wcfmmarketplace' ) {
				if( wcfm_is_store_page() ) {
					$custom_store_url = get_option( 'wcfm_store_url', 'store' );
					$store_name = get_query_var( $custom_store_url );
					$vendor_id  = 0;
					if ( !empty( $store_name ) ) {
						$store_user = get_user_by( 'slug', $store_name );
					}
					if( $store_user ) {
						$vendor_id   		= $store_user->ID;
					}
					if( $vendor_id ) {
						if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['stores'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['stores'] ) && !in_array( $vendor_id, $_SESSION['wcfm_pages']['stores'] ) ) ) {
							// wcfm_detailed_analysis Query
							$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																				(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																				VALUES
																				(0, 1, 0, 0, {$vendor_id}, '{$_SERVER['HTTP_REFERER']}', '{$ip_address}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
							$wpdb->query($wcfm_detailed_analysis);
							
							// wcfm_daily_analysis Query
							$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																				(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																				VALUES
																				(0, 1, 0, 0, {$vendor_id}, 1, '{$todate}')
																				ON DUPLICATE KEY UPDATE
																				count = count+1";
							$wpdb->query($wcfm_daily_analysis);
							
							// Session store
							$_SESSION['wcfm_pages']['stores'][] = $vendor_id;
						}
					}
				}
			}
		}
		
		//print_R($_SERVER);
	}
	
	/**
	 * Check is a product shipping or not
	 */
	function is_wcfm_needs_shipping( $product ) {
		$needs_shipping = true; 
		
		if( is_numeric( $product ) ) $product = wc_get_product( $product );
		               
		$non_shipped_product_types = apply_filters( 'wcfm_non_shipped_product_types', array( 'booking', 'external', 'grouped', 'accommodation-booking', 'appointment', 'job_package', 'resume_package' ) ); 
		if ( !is_object( $product ) ) {
			$needs_shipping = false; 
		} elseif ( is_object( $product ) && ( $product->is_virtual() || $product->is_downloadable() || in_array( $product->get_type(), $non_shipped_product_types ) ) ) {
			$needs_shipping = false; 
		}
		
		return $needs_shipping;
	}
	
	/**
	 * Is WCFM Ultimate Inactive Notice Show
	 */
	function is_wcfmu_inactive_notice_show( $show ) {
		global $WCFM;
		
		$wcfm_options = $WCFM->wcfm_options;
	  $is_ultimate_notice_disabled = isset( $wcfm_options['wcfm_ultimate_notice_disabled'] ) ? $wcfm_options['wcfm_ultimate_notice_disabled'] : 'no';
	  if( $is_ultimate_notice_disabled == 'yes' ) $show = false;
	  if( wcfm_is_vendor() ) $show = false;
		return $show;
	}
	
		/**
	 * WCFMu Product Select Attributes using WC Taxonomy Attribute
	 */
	function wcfm_products_manage_select_attributes( $product_id = 0 ) {
		global $WCFM, $WCFMu, $wc_product_attributes;
		
		$wcfm_attributes = array();
		if( $product_id ) {
			$wcfm_attributes = get_post_meta( $product_id, '_product_attributes', true );
		}
		
		if( apply_filters( 'wcfm_is_force_category_attributes_mapping', false ) ) {
			echo '<p class="wcfm_category_attributes_mapping_msg description instructions">' . __( 'First choose product category to get associated attributes.', 'wc-frontend-manager' ) . '</p>';
		}
		
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$attributes = array();
		$acnt = 0;
		if ( ! empty( $attribute_taxonomies ) ) {
			foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
				if ( ( 'text' !== $attribute_taxonomy->attribute_type ) && $attribute_taxonomy->attribute_name ) {
					$att_taxonomy = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );
					
					if( !apply_filters( 'wcfm_is_allowed_attributes', true, $att_taxonomy ) ) continue;
					
					$attributes[$acnt]['term_name'] = $att_taxonomy;
					$attributes[$acnt]['name'] = wc_attribute_label( $att_taxonomy );
					$attributes[$acnt]['attribute_taxonomy'] = $attribute_taxonomy;
					$attributes[$acnt]['tax_name'] = $att_taxonomy;
					$attributes[$acnt]['is_taxonomy'] = 1;
					$attributes[$acnt]['wrapper_class'] = 'wcfm_attributes_blocks wcfm_defined_attributes wcfm_attributes_block_'.$att_taxonomy;
				
					$args = array(
												'orderby'    => ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name',
												'hide_empty' => 0
											);
					$all_terms = get_terms( $att_taxonomy, apply_filters( 'wcfm_product_attribute_terms', apply_filters( 'woocommerce_product_attribute_terms', $args ) ) );
					$attributes_option = array();
					if ( $all_terms ) {
						foreach ( $all_terms as $term ) {
							$attributes_option[$term->term_id] = esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) );
						}
					}
					$attributes[$acnt]['option_values']  = $attributes_option;
					$attributes[$acnt]['value']          = wp_get_post_terms( $product_id, $att_taxonomy, array( 'fields' => 'ids' ) );
					$attributes[$acnt]['is_active']      = '';
					$attributes[$acnt]['is_visible']     = '';
					$attributes[$acnt]['is_variation']   = '';
					
					if( $product_id && !empty( $wcfm_attributes ) ) {
						foreach( $wcfm_attributes as $wcfm_attribute ) {
							if ( $wcfm_attribute['is_taxonomy'] ) {
								if( $att_taxonomy == $wcfm_attribute['name'] ) {
									$attributes[$acnt]['is_active'] = 'enable';
									$attributes[$acnt]['is_visible'] = $wcfm_attribute['is_visible'] ? 'enable' : '';
									$attributes[$acnt]['is_variation'] = $wcfm_attribute['is_variation'] ? 'enable' : '';
								}
							}
						}
					}
				
					// Global Level
					$allow_add_term = '';
					if( WCFM_Dependencies::wcfmu_plugin_active_check() && apply_filters( 'wcfm_is_allow_add_attribute_term', true ) ) {
						$allow_add_term = 'wc_attribute_values allow_add_term';
					}
					$attrlimit = apply_filters( 'wcfm_attribute_limit', -1 );
					
					// Attribute wise level
					if( !apply_filters( 'wcfm_is_allow_add_attribute_term_'.$att_taxonomy, true, $att_taxonomy ) ) {
						$allow_add_term = '';
					}
					$attrlimit = apply_filters( 'wcfm_attribute_limit_'.$att_taxonomy, $attrlimit, $att_taxonomy );
					
					$attributes = apply_filters( 'wcfm_product_custom_attributes_data', apply_filters( 'wcfm_product_custom_attribute_date_'.$att_taxonomy, $attributes, $att_taxonomy ) );
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_custom_attributes', apply_filters( 'wcfm_product_custom_attribute_'.$att_taxonomy, array(  
																																																	"select_attributes_".$att_taxonomy => array( 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_select_attributes wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title', 'value' => $attributes, 'options' => array(
																																																			"term_name" => array('type' => 'hidden'),
																																																			"is_active" => array('label' => __('Active?', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'attributes' => array( 'title' => __( 'Check to associate this attribute with the product', 'wc-frontend-manager' ) ), 'class' => 'wcfm-checkbox wcfm_ele attribute_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking checkbox_title'),
																																																			"name" => array('label' => __('Name', 'wc-frontend-manager'), 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'variation_ele_hide', 'label_class' => 'variation_ele_hide'),
																																																			"value" => array('label' => wc_attribute_label( $att_taxonomy ), 'type' => 'select', 'custom_attributes' => array( 'attrlimit' => $attrlimit ), 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'class' => 'wcfm-select wcfm_ele simple variable external grouped booking ' . $allow_add_term, 'label_class' => 'wcfm_title wcfm_ele attribute_ele simple variable external grouped booking'),
																																																			"is_visible" => array('label' => __('Visible on the product page', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking checkbox_title'),
																																																			"is_variation" => array('label' => __('Use as Variation', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'enable', 'class' => 'wcfm-checkbox wcfm_ele variable variable-subscription', 'label_class' => 'wcfm_title checkbox_title wcfm_ele variable variable-subscription'),
																																																			"tax_name" => array('type' => 'hidden'),
																																																			"is_taxonomy" => array('type' => 'hidden')
																																																	))
																																												), 'select_attributes_'.$att_taxonomy, $att_taxonomy, $attributes ), 'select_attributes_'.$att_taxonomy, $att_taxonomy, $attributes ) );
				}
			}
		}
	}
	
	/**
	 * WCFM Dashboard Page Body Class
	 */
	function wcfm_dashboard_body_classes($classes) {
		if( is_wcfm_page() ) {
			$classes[] = 'wcfm-dashboard-page';
		}
		return $classes;
	}
	
	/**
	 * Enfold Theme media library load by force
	 */
	function wcfm_avf_enqueue_wp_mediaelement( $is_allow, $options ) {
		return true;
	}
	
	/**
	 * WCFM Core JS
	 */
	function wcfm_scripts() {
 		global $WCFM, $wp, $WCFM_Query;
 		
 		if( isset( $_REQUEST['fl_builder'] ) ) return;
 		
 		// Libs
	  //$WCFM->library->load_qtip_lib();
	  
	  // Block UI
	  if( apply_filters( 'wcfm_is_allow_blockui', true ) ) {
	  	$WCFM->library->load_blockui_lib();
	  }
	  
	  // Colorbox
	  //$WCFM->library->load_colorbox_lib();
	  
	  // Date Picker
	  $WCFM->library->load_datepicker_lib();
 		
 		// Core JS
	  wp_enqueue_script( 'wcfm_core_js', $WCFM->library->js_lib_url_min . 'wcfm-script-core.js', array( 'jquery' ), $WCFM->version, true );
	  
	  // Localized Script
	  if( apply_filters( 'wcfm_is_allow_sound', true ) ) {
			if( apply_filters( 'wcfm_is_allow_notification_sound', true ) ) {
				wp_localize_script( 'wcfm_core_js', 'wcfm_notification_sound', apply_filters( 'wcfm_notification_sound', $WCFM->library->lib_url . 'sounds/notification.mp3' ) );
			} else {
				//wp_localize_script( 'wcfm_core_js', 'wcfm_notification_sound', $WCFM->library->lib_url . 'sounds/empty_audio.mp3' );
			}
			if( apply_filters( 'wcfm_is_allow_new_message_check', false ) && apply_filters( 'wcfm_is_allow_desktop_notification', true ) && apply_filters( 'wcfm_is_allow_desktop_notification_sound', true ) ) {
				wp_localize_script( 'wcfm_core_js', 'wcfm_desktop_notification_sound', apply_filters( 'wcfm_desktop_notification_sound', $WCFM->library->lib_url . 'sounds/desktop_notification.mp3' ) );
			} else {
				//wp_localize_script( 'wcfm_core_js', 'wcfm_desktop_notification_sound', $WCFM->library->lib_url . 'sounds/empty_audio.mp3' );
			}
		} else {
			//wp_localize_script( 'wcfm_core_js', 'wcfm_notification_sound', $WCFM->library->lib_url . 'sounds/empty_audio.mp3' );
		}
		
		$wcfm_dashboard_messages = get_wcfm_dashboard_messages();
		wp_localize_script( 'wcfm_core_js', 'wcfm_core_dashboard_messages', $wcfm_dashboard_messages );
	  
		$unread_message = 0;
		$unread_enquiry = 0;
		if( apply_filters( 'wcfm_is_allow_new_message_check', false ) ) {
			$unread_message = $WCFM->wcfm_notification->wcfm_direct_message_count( 'message' );
			$unread_enquiry = $WCFM->wcfm_notification->wcfm_direct_message_count( 'enquiry' );
		}
		
		$ajax_url = WC()->ajax_url();
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
			global $sitepress;
			$language_code = $sitepress->get_current_language();
			$ajax_url = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $ajax_url );
			
		}
	  
	  // Localize Script
	  $tinyMCE_toolbar_options = 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify |  bullist numlist outdent indent | link image | ltr rtl';
	  wp_localize_script( 'wcfm_core_js', 'wcfm_params', array( 'ajax_url'                                 => $ajax_url, 
	  																													'wc_ajax_url'                              => WC()->ajax_url(), 
	  	                                                        'shop_url'                                 => get_permalink( wc_get_page_id( 'shop' ) ), 
	  	                                                        'wcfm_is_allow_wcfm'                       => wcfm_is_allow_wcfm(), 
	  	                                                        'wcfm_is_vendor'                           => wcfm_is_vendor(), 
	  	                                                        'is_user_logged_in'                        => is_user_logged_in(), 
	  	                                                        'wcfm_allow_tinymce_options'               => apply_filters( 'wcfm_allow_tinymce_options', $tinyMCE_toolbar_options ),
	  	                                                        'unread_message'                           => $unread_message, 
	  	                                                        'unread_enquiry'                           => $unread_enquiry, 
	  	                                                        'wcfm_is_allow_new_message_check'          => apply_filters( 'wcfm_is_allow_new_message_check', false ),
	  	                                                        'wcfm_new_message_check_duration'          => apply_filters( 'wcfm_new_message_check_duration', 60000 ),
	  	                                                        'wcfm_is_desktop_notification'             => apply_filters( 'wcfm_is_allow_desktop_notification', true ), 
	  	                                                        'is_mobile_desktop_notification'           => apply_filters( 'wcfm_is_allow_mobile_desktop_notification', false ),
	  	                                                        'wcfm_is_allow_external_product_analytics' => apply_filters( 'wcfm_is_allow_external_product_analytics', false ),
	  	                                                        'is_mobile'                                => wcfm_is_mobile(),
	  	                                                        'is_tablet'                                => wcfm_is_tablet()
	  	                                                       ) );
	  
	  // Inquery Localized Script
		$wcfm_inquiry_messages = get_wcfm_enquiry_manage_messages();
		wp_localize_script( 'wcfm_core_js', 'wcfm_enquiry_manage_messages', $wcfm_inquiry_messages );
		
		// WCFM Ultimate Localize Script
	  $wcfm_core_messages = get_wcfm_products_manager_messages();
		wp_localize_script( 'wcfm_core_js', 'wcfmu_products_manage_messages', $wcfm_core_messages );
	  
	  
	  // Load End Point Scripts
	  if( is_wcfm_page() ) {
			if ( isset( $wp->query_vars['page'] ) ) {
				$WCFM->library->load_scripts( 'wcfm-dashboard' );
			} else {
				$wcfm_endpoints = $WCFM_Query->get_query_vars();
				$is_endpoint = false;
				foreach ( $wcfm_endpoints as $key => $value ) {
					if ( isset( $wp->query_vars[ $key ] ) ) {
						$WCFM->library->load_scripts( $key );
						$is_endpoint = true;
					}
				}
				if( !$is_endpoint ) {
					// Load dashboard Scripts
					$WCFM->library->load_scripts( 'wcfm-dashboard' );
				}
			}
		}
 	}
 	
 	/**
 	 * WCFM Script Fix
 	 */
 	function wcfm_scripts_fix() {
 		if( is_wcfm_page() ) {
 			// WilCity Theme Char JS error fix - 6.2.2
			wp_dequeue_script('chartjs');
			
			// Electro Theme Fix - 6.4.1 
			if( function_exists( 'electro_get_footer' ) && defined('FPD_PLUGIN_DIR') ) {
				wp_dequeue_script( 'modaljs' );
				wp_dequeue_script( 'electro-js' );
				wp_dequeue_script( 'bootstrap-js' );
				wp_dequeue_script( 'bootstrap-hover-dropdown-js' );
			}
		}
 	}
 	
 	/**
 	 * WCFM Core CSS
 	 */
 	function wcfm_styles() {
 		global $WCFM, $wp, $WCFM_Query;
 		
 		if( isset( $_REQUEST['fl_builder'] ) ) return;
 		
 		// WC Icon set
	  //wp_enqueue_style( 'wcfm_wc_icon_css',  $WCFM->library->css_lib_url_min . 'wcfm-style-icon.css', array(), $WCFM->version );
	  
	  // Font Awasome Icon set
	  if( apply_filters( 'wcfm_is_allow_font_awesome', true ) ) {
			if( apply_filters( 'wcfm_is_allow_old_icons', false ) ) {
				wp_enqueue_style( 'wcfm_fa_icon_css',  $WCFM->plugin_url . 'assets/fonts/font-awesome-old/css/font-awesome.min.css', array(), $WCFM->version );
			} else {
				//wp_enqueue_style( 'wcfm_fab_icon_css',  $WCFM->plugin_url . 'assets/fonts/font-awesome/css/brands.min.css', array(), $WCFM->version );
				//wp_enqueue_style( 'wcfm_fa_icon_css',  $WCFM->plugin_url . 'assets/fonts/font-awesome/css/regular.min.css', array(), $WCFM->version );
				//wp_enqueue_style( 'wcfm_fas_icon_css',  $WCFM->plugin_url . 'assets/fonts/font-awesome/css/solid.min.css', array(), $WCFM->version );
				wp_enqueue_style( 'wcfm_fa_icon_css',  $WCFM->plugin_url . 'assets/fonts/font-awesome/css/wcfmicon.min.css', array(), $WCFM->version );
			}
		}
	  
	  // Admin Bar CSS
	  //wp_enqueue_style( 'wcfm_admin_bar_css',  $WCFM->library->css_lib_url_min . 'wcfm-style-adminbar.css', array(), $WCFM->version );
	  
	  // WCFM Core CSS
	  wp_enqueue_style( 'wcfm_core_css',  $WCFM->library->css_lib_url_min . 'wcfm-style-core.css', array(), $WCFM->version );
	  
	  // Load End Point CSS
	  if( is_wcfm_page() ) {
			if ( isset( $wp->query_vars['page'] ) ) {
				$WCFM->library->load_styles( 'wcfm-dashboard' );
			} else {
				$wcfm_endpoints = $WCFM_Query->get_query_vars();
				$is_endpoint = false;
				foreach ( $wcfm_endpoints as $key => $value ) {
					if ( isset( $wp->query_vars[ $key ] ) ) {
						$WCFM->library->load_styles( $key );
						$is_endpoint = true;
					}
				}
				if( !$is_endpoint ) {
					// Load dashboard Scripts
					$WCFM->library->load_styles( 'wcfm-dashboard' );
				}
			}
		}
 	}
 	
}