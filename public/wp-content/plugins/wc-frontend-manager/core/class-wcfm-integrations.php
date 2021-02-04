<?php
/**
 * WCFM plugin core
 *
 * Third Party Plugin Integrations Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   2.2.2
 */
 
class WCFM_Integrations {

	public function __construct() {
		global $WCFM;
		
    // WCFM Query Var Filter
		add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_thirdparty_query_vars' ), 20 );
		add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_thirdparty_endpoint_title' ), 20, 2 );
		add_action( 'init', array( &$this, 'wcfm_thirdparty_init' ), 20 );
		
		// WCFM Third Party Endpoint Edit
		add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfm_thirdparty_endpoints_slug' ) );
    
    // WCFM Menu Filter
    add_filter( 'wcfm_menus', array( &$this, 'wcfm_thirdparty_menus' ), 100 );
    
    // WCFM Thirdparty Product Type
		add_filter( 'wcfm_product_types', array( &$this, 'wcfm_thirdparty_product_types' ), 50 );
    
    // Third Party Product Type Capability
		add_filter( 'wcfm_capability_settings_fields_product_types', array( &$this, 'wcfmcap_product_types' ), 50, 3 );
		
		if( apply_filters( 'wcfm_is_allow_listings', true ) && apply_filters( 'wcfm_is_allow_products_for_listings', true ) ) {
			if ( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() ) {
				if( wcfm_is_allow_wcfm() && apply_filters( 'wcfm_is_allow_manage_listings_wcfm_wrapper', true ) ) {
					if( !WCFM_Dependencies::wcfm_products_mylistings_active_check() ) {
						add_filter( 'the_content', array( &$this, 'wcfm_add_listing_page' ), 20 );
						add_filter( 'wcfm_current_endpoint', array( $this, 'wcfm_add_listing_endpoint' ) );
						add_action( 'wp_enqueue_scripts', array( $this, 'wcfm_add_listing_enqueue_scripts' ) );
					}
				}
			}
		}
    
    // WC Paid Listing Support - 2.3.4
    if( $wcfm_allow_job_package = apply_filters( 'wcfm_is_allow_job_package', true ) ) {
			if ( WCFM_Dependencies::wcfm_wc_paid_listing_active_check() ) {
				// WC Paid Listing Product options
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_wcpl_product_manage_fields_pricing' ), 50, 5 );
			}
		}
		
		// WC Rental & Booking Support - 2.3.8
    if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_rental_active_check() ) {
				// WC Rental Product options
				add_filter( 'after_wcfm_products_manage_general', array( &$this, 'wcfm_wcrental_product_manage_fields' ), 80, 2 );
			}
		}
		
		// YITH AuctionsFree Support - 3.0.4
    if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFM_Dependencies::wcfm_yith_auction_free_active_check() ) {
				// YITH Auction Product options
				add_filter( 'after_wcfm_products_manage_general', array( &$this, 'wcfm_yithauction_free_product_manage_fields' ), 70, 2 );
			}
		}
		
		// Geo my WP Support - 3.2.4
    if( $wcfm_allow_geo_my_wp = apply_filters( 'wcfm_is_allow_geo_my_wp', true ) ) {
			if( WCFM_Dependencies::wcfm_geo_my_wp_plugin_active_check() ) {
				// GEO my WP Product Location options
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_geomywp_products_manage_views' ), 100 );
			}
		}
		
		// WooCommerce Product Voucher Support - 3.4.7
		if( apply_filters( 'wcfm_is_allow_wc_product_voucher', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_product_voucher_plugin_active_check() ) {
				add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcfm_wc_product_voucher_product_manage_fields_general' ), 30, 5 );
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_wc_product_voucher_product_manage_fields_pricing' ), 50, 5 );
			}
		}
		
		// Woocommerce Germanized Support - 3.3.2
    if( apply_filters( 'wcfm_is_allow_woocommerce_germanized', true ) ) {
			if( WCFM_Dependencies::wcfm_woocommerce_germanized_plugin_active_check() ) {
				// Woocommerce Germanized Product Pricing & Shipping options
				add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcfm_woocommerce_germanized_product_manage_fields_general' ), 40, 5 );
				add_filter( 'wcfm_product_manage_fields_content', array( &$this, 'wcfm_woocommerce_germanized_product_manage_fields_content' ), 50, 3 );
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_woocommerce_germanized_product_manage_fields_pricing' ), 50, 5 );
				add_filter( 'wcfm_product_manage_fields_shipping', array( &$this, 'wcfm_woocommerce_germanized_product_manage_fields_shipping' ), 50, 2 );
				
				// Woocommerce Germanized Variations Pricing & Shipping options
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_woocommerce_germanized_product_manage_fields_variations' ), 100, 4 );
				add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfm_woocommerce_germanized_product_data_variations' ), 100, 3 );
			}
		}
		
		// WC Epeken Support - 4.1.0
    if( apply_filters( 'wcfm_is_allow_epeken', true ) ) {
			if( WCFM_Dependencies::wcfm_epeken_plugin_active_check() ) {
				// WC Epeken Product options
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wcepeken_product_manage_views' ), 150 );
				
				// WC Epeken User Settings
				add_action( 'end_wcfm_vendor_settings', array( &$this, 'wcfm_wcepeken_settings_views' ), 150 );
				add_action( 'wcfm_vendor_settings_update', array( &$this, 'wcfm_wcepeken_vendor_settings_update' ), 150, 2 );
			}
		}
		
		// WooCommerce Schedular - 5.0.7
    if( apply_filters( 'wcfm_is_allow_wdm_scheduler', true ) ) {
			if( WCFM_Dependencies::wcfm_wdm_scheduler_active_check() ) {
				//add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wdm_scheduler_product_manage_views' ), 160 );
			}
		}
		
		// WooCommerce Product Schedular - 6.1.4
    if( apply_filters( 'wcfm_is_allow_wc_product_scheduler', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_product_scheduler_active_check() ) {
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_product_scheduler_product_manage_views' ), 160 );
			}
		}
		
		// WooCommerce Tiered Table Price - 6.3.4
    if( apply_filters( 'wcfm_is_allow_wc_tiered_price_table', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_tiered_price_table_active_check() || WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_tiered_price_table_product_manage_views' ), 170 );
				
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_wc_tiered_price_table_product_manage_fields_variations' ), 100, 4 );
				add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfm_wc_tiered_price_table_product_data_variations' ), 100, 3 );
			}
		}
		
		// Woo Advanced Product Size Chart - 6.4.1
    if( apply_filters( 'wcfm_is_allow_woo_product_size_chart', true ) ) {
			if( WCFM_Dependencies::wcfm_woo_product_size_chart_plugin_active_check() ) {
				add_filter( 'wcfm_product_manager_right_panel_after', array( &$this, 'wcfm_woo_product_size_chart_product_manage_views' ), 50 );
			}
		}
		
		// Post Expirator - 6.4.6
    if( apply_filters( 'wcfm_is_allow_post_expirator', true ) ) {
			if( WCFM_Dependencies::wcfm_post_expirator_plugin_active_check() ) {
				add_filter( 'wcfm_product_manager_right_panel_after', array( &$this, 'wcfm_woo_product_post_expirator_product_manage_views' ), 60 );
			}
		}
		
		// WooCommerce German Market - 6.4.8
    if( apply_filters( 'wcfm_is_allow_wc_german_market', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_german_market_plugin_active_check() ) {
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_wc_german_market_product_pricing_fields' ), 170, 3 );
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_german_market_product_manage_views' ), 170 );
				
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_wc_german_market_product_manage_fields_variations' ), 100, 4 );
				add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfm_wc_german_market_product_data_variations' ), 100, 3 );
			}
		}
		
		//  WooCommerce Country Based Restrictions - 6.5.3
    if( apply_filters( 'wcfm_is_allow_woo_country_based_restriction', true ) ) {
			if( WCFM_Dependencies::wcfm_woo_country_based_restriction_active_check() ) {
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_woo_country_based_restriction_product_manage_views' ), 160 );
			}
		}
		
		// Product Manage Third Party Plugins View
    add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_integrations_products_manage_views' ), 100 );
    
    // Listing Approve
    add_action( 'wp_ajax_approve_listing', array( &$this, 'wcfm_listing_approve' ) );
	}
	
	
	/**
   * WCFM Third Party Query Var
   */
  function wcfm_thirdparty_query_vars( $query_vars ) {
  	
  	// WP Job Manager Support
  	if( $wcfm_allow_listings = apply_filters( 'wcfm_is_allow_listings', true ) ) {
			if ( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() ) {
				$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
				$query_listing_vars = array(
					'wcfm-listings'       => ! empty( $wcfm_modified_endpoints['wcfm-listings'] ) ? $wcfm_modified_endpoints['wcfm-listings'] : 'listings',
				);
				     
				// WP Job Manager Applications Support
				if ( WCFM_Dependencies::wcfm_wp_job_manager_applications_plugin_active_check() ) {
					//$query_listing_vars['wcfm-applications'] = ! empty( $wcfm_modified_endpoints['wcfm-applications'] ) ? $wcfm_modified_endpoints['wcfm-applications'] : 'applications';
				}
		
				$query_vars = array_merge( $query_vars, $query_listing_vars );
			} else {
				if( get_option( 'wcfm_updated_end_point_wc_listings' ) ) {
					delete_option( 'wcfm_updated_end_point_wc_listings' );
				}
			}
		}
		
		return $query_vars;
  }
  
  /**
   * WCFM Third Party End Point Title
   */
  function wcfm_thirdparty_endpoint_title( $title, $endpoint ) {
  	
  	switch ( $endpoint ) {
  		case 'wcfm-listings' :
				$title = __( 'Listings Dashboard', 'wc-frontend-manager' );
			break;
			
			case 'wcfm-applications' :
				$title = __( 'Applications Dashboard', 'wc-frontend-manager' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCFM Third Party Endpoint Intialize
   */
  function wcfm_thirdparty_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_wc_listings' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_wc_listings', 1 );
		}
  }
  
  /**
	 * Thirdparty Endpoiint Edit
	 */
	function wcfm_thirdparty_endpoints_slug( $endpoints ) {
		
		// Listings
		if( $wcfm_allow_listings = apply_filters( 'wcfm_is_allow_listings', true ) ) {
			if ( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() ) {
				$listings_endpoints = array(
															'wcfm-listings'  		   => 'listings',
															);
				if ( WCFM_Dependencies::wcfm_wp_job_manager_applications_plugin_active_check() ) {
					//$listings_endpoints['wcfm-applications'] = 'applications';
				}
				$endpoints = array_merge( $endpoints, $listings_endpoints );
			}
		}
		
		return $endpoints;
	}
	
	/**
	 * WCFM Third Party Plugins Menus
	 */
	function wcfm_thirdparty_menus( $menus ) {
  	global $WCFM;
  	
  	// WP Job Manager Support
  	if( $wcfm_allow_listings = apply_filters( 'wcfm_is_allow_listings', true ) ) {
			if ( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() ) {
				$jobs_dashboard = get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) );
				$post_a_job = get_permalink ( get_option( 'job_manager_submit_job_form_page_id' ) );
				if( $jobs_dashboard && $post_a_job ) {
					$menus = array_slice($menus, 0, 3, true) +
															array( 'wcfm-listings' => array(  'label'       => __( 'Listings', 'wc-frontend-manager' ),
																																 'url'        => get_wcfm_listings_url(),
																																 'icon'       => 'briefcase',
																																 'priority'   => 10
																																) )	 +
																array_slice($menus, 3, count($menus) - 3, true) ;
						
				  // WP Job Manager Applications Support
					if ( WCFM_Dependencies::wcfm_wp_job_manager_applications_plugin_active_check() ) {
						/*$menus = array_slice($menus, 0, 4, true) +
															array( 'wcfm-applications' => array(  'label'     => __( 'Applications', 'wc-frontend-manager' ),
																																	 'url'        => get_wcfm_applications_url(),
																																	 'icon'       => 'user-tie',
																																	 'priority'   => 11
																																	) )	 +
																array_slice($menus, 4, count($menus) - 4, true) ;*/
					}
				}
			}
		}
		
  	return $menus;
  }
  
  /**
   * WCFM Third Party Product Type
   */
  function wcfm_thirdparty_product_types( $pro_types ) {
  	global $WCFM;
  	
  	// WC Paid Listing Support - 2.3.4
    if( $wcfm_allow_job_package = apply_filters( 'wcfm_is_allow_job_package', true ) ) {
			if ( WCFM_Dependencies::wcfm_wc_paid_listing_active_check() ) {
				$pro_types['job_package'] = __( 'Listing Package', 'wp-job-manager-wc-paid-listings' );
			}
		}
		
		// WC Rental & Booking Support - 2.3.8
    if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_rental_active_check() ) {
				$pro_types['redq_rental'] = __( 'Rental Product', 'wc-frontend-manager' );
			}
		}
		
		// YiTH Auctions Free - 3.0.4
  	if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFM_Dependencies::wcfm_yith_auction_free_active_check() ) {
				$pro_types['auction'] = __( 'Auction', 'wc-frontend-manager' );
			}
		}
  	
  	return $pro_types;
  }
  
  /**
	 * WCFM Capability Product Types
	 */
	function wcfmcap_product_types( $product_types, $handler = 'wcfm_capability_options', $wcfm_capability_options = array() ) {
		global $WCFM, $WCFMu;
		
		if ( WCFM_Dependencies::wcfm_wc_paid_listing_active_check() ) {
			$job_package = ( isset( $wcfm_capability_options['job_package'] ) ) ? $wcfm_capability_options['job_package'] : 'no';
		
			$product_types["job_package"] = array('label' => __('Listing Package', 'wc-frontend-manager') , 'name' => $handler . '[job_package]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $job_package);
		}
		
		if( WCFM_Dependencies::wcfm_wc_rental_active_check() ) {
			$rental = ( isset( $wcfm_capability_options['rental'] ) ) ? $wcfm_capability_options['rental'] : 'no';
			
			$product_types["rental"] = array('label' => __('Rental', 'wc-frontend-manager') , 'name' => $handler . '[rental]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $rental);
		}
		
		if( WCFM_Dependencies::wcfm_yith_auction_free_active_check() ) {
			$auction = ( isset( $wcfm_capability_options['auction'] ) ) ? $wcfm_capability_options['auction'] : 'no';
		
			$product_types["auction"] = array('label' => __('Auction', 'wc-frontend-manager') , 'name' => $handler . '[auction]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $auction);
		}
		
		return $product_types;
	}
	
	function wcfm_add_listing_page( $content ) {
		global $post, $WCFM;
		
		$job_dashboard_page = get_option( 'job_manager_job_dashboard_page_id' );
		$add_listings_page = get_option( 'job_manager_submit_job_form_page_id' );
		$post_a_job = get_permalink ( $add_listings_page );
		if( ( $add_listings_page && is_object( $post ) && ( $add_listings_page == $post->ID ) ) || ( $job_dashboard_page && is_object( $post ) && ( $job_dashboard_page == $post->ID ) && isset( $_GET['action'] ) && ( in_array( $_GET['action'], array( 'edit', 'mark_filled', 'mark_not_filled' ) ) ) ) ) {
			ob_start();
			echo '<div id="wcfm-main-contentainer">';
			echo  '<div id="wcfm-content">';
			$WCFM->template->get_template( 'wcfm-view-menu.php' );
			echo '<div class="collapse wcfm-collapse" id="wcfm_listing_job_submit">';
			echo '<div class="wcfm-page-headig">';
			echo '<span class="wcfmfa fa-chalkboard"></span>';
			echo '<span class="wcfm-page-heading-text">' . __( 'Manage Listings', 'wc-frontend-manager' ) . '</span>';
			$WCFM->template->get_template( 'wcfm-view-header-panels.php' );
			echo '</div>';
			echo '<div class="wcfm-collapse-content">';
			echo '<div class="wcfm-container wcfm-top-element-container">';
			if( isset( $_GET['action'] ) && ( $_GET['action'] == 'edit' ) ) {
				echo '<h2>' . __( 'Edit Listing', 'wc-frontend-manager' ) . '</h2>';
			} else if( isset( $_GET['action'] ) && ( in_array( $_GET['action'], array( 'mark_filled', 'mark_not_filled' ) ) ) ) {
				echo '<h2>' . __( 'Manage Listing', 'wc-frontend-manager' ) . '</h2>';
			} else {
				echo '<h2>' . __( 'Add Listing', 'wc-frontend-manager' ) . '</h2>';
			}
			if( apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				echo '<a target="_blank" class="wcfm_wp_admin_view text_tip" href="' . admin_url('edit.php?post_type=job_listing') . '" data-tip="' . __( 'WP Admin View', 'wc-frontend-manager' ) . '"><span class="fab fa-wordpress"></span></a>';
			}
			if( isset( $_GET['action'] ) && ( $_GET['action'] == 'edit' ) ) {
				echo '<a id="add_new_listing_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_listings_url().'" data-tip="' . __('Manage Listings', 'wc-frontend-manager') . '"><span class="wcfmfa fa-briefcase"></span></a>';
				echo '<a id="add_new_listing_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.$post_a_job.'" data-tip="' . __('Add New Listing', 'wc-frontend-manager') . '"><span class="wcfmfa fa-briefcase"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager') . '</span></a>';
			} else {
				echo '<a id="add_new_listing_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_listings_url().'" data-tip="' . __('Manage Listings', 'wc-frontend-manager') . '"><span class="wcfmfa fa-briefcase"></span><span class="text">' . __( 'Listings', 'wc-frontend-manager') . '</span></a>';
			}
			echo '<div class="wcfm-clearfix"></div></div><div class="wcfm-clearfix"></div></br>';
			echo '<div class="wcfm-container wcfm_listing_job_fields_container">';
			$content = ob_get_clean() . $content . '</div></div></div></div></div>';
		}
		
		return $content;
	}
	
	/**
	 * Add/Edit listing WCfM End point set
	 */
	function wcfm_add_listing_endpoint( $current_endpoint ) {
		global $WCFM, $WCFMu, $post, $_GET;
		
		$job_dashboard_page = get_option( 'job_manager_job_dashboard_page_id' );
		$add_listings_page = get_option( 'job_manager_submit_job_form_page_id' );
		if( ( $add_listings_page && is_object( $post ) && ( $add_listings_page == $post->ID ) ) || ( $job_dashboard_page && is_object( $post ) && ( $job_dashboard_page == $post->ID ) && isset( $_GET['action'] ) && ( in_array( $_GET['action'], array( 'edit', 'mark_filled', 'mark_not_filled' ) ) ) ) ) {
			$current_endpoint = 'wcfm-listings-manage';
		}
		return $current_endpoint;
	}
	
	/**
	 * Listings - WCfM Dashboard wrapper Script - 4.2.3
	 */
	function wcfm_add_listing_enqueue_scripts() {
		global $WCFM, $post, $_GET;
		
		$job_dashboard_page = get_option( 'job_manager_job_dashboard_page_id' );
		$add_listings_page = get_option( 'job_manager_submit_job_form_page_id' );
		if( ( $add_listings_page && is_object( $post ) && ( $add_listings_page == $post->ID ) ) || ( $job_dashboard_page && is_object( $post ) && ( $job_dashboard_page == $post->ID ) && isset( $_GET['action'] ) && ( in_array( $_GET['action'], array( 'edit', 'mark_filled', 'mark_not_filled' ) ) ) ) ) {
			
			if( !WCFM_Dependencies::wcfm_products_listings_active_check() && !WCFM_Dependencies::wcfm_products_mylistings_active_check() ) {
				$WCFM->library->load_scripts( 'wcfm-profile' );
				
				// Load Styles
				$WCFM->library->load_styles( 'wcfm-profile' );
				wp_enqueue_style( 'wcfm_add_listings_css', $WCFM->library->css_lib_url . 'listings/wcfm-style-listings-manage.css', array(), $WCFM->version );
			}
		}
	}
	
  /**
	 * WC Paid Listing Product General options
	 */
	function wcfm_wcpl_product_manage_fields_pricing( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$_job_listing_package_subscription_type        = '';
		$_job_listing_limit     = '';
		$_job_listing_duration       = '';
		$_job_listing_featured = 'no';
		
		if( $product_id ) {
			$_job_listing_package_subscription_type        = get_post_meta( $product_id, '_job_listing_package_subscription_type', true );
			$_job_listing_limit     = get_post_meta( $product_id, '_job_listing_limit', true );
			$_job_listing_duration       = get_post_meta( $product_id, '_job_listing_duration', true );
			$_job_listing_featured = get_post_meta( $product_id, '_job_listing_featured', true );
		}
		
		$pos_counter = 4;
		if( WCFM_Dependencies::wcfmu_plugin_active_check() ) $pos_counter = 6;
		
		$job_listing_package_fields = array( 
																				"_job_listing_package_subscription_type" => array( 'label' => __('Subscription Type', 'wp-job-manager-wc-paid-listings' ), 'type' => 'select', 'options' => array( 'package' => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-manager-wc-paid-listings' ), 'listing' => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-manager-wc-paid-listings' ) ), 'class' => 'wcfm-select wcfm_ele job_package_price_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Choose how subscriptions affect this package', 'wp-job-manager-wc-paid-listings' ), 'value' => $_job_listing_package_subscription_type ),
																				"_job_listing_limit" => array( 'label' => __('Job listing limit', 'wp-job-manager-wc-paid-listings' ), 'placeholder' => __( 'Unlimited', 'wc-frontend-manager'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele job_package_price_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'attributes' => array( 'min'   => '', 'step' 	=> '1' ), 'hints' => __( 'The number of job listings a user can post with this package.', 'wp-job-manager-wc-paid-listings' ), 'value' => $_job_listing_limit ),
																				"_job_listing_duration" => array( 'label' => __('Job listing duration', 'wp-job-manager-wc-paid-listings' ), 'placeholder' => 0, 'type' => 'number', 'class' => 'wcfm-text wcfm_ele job_package_price_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'attributes' => array( 'min'   => '', 'step' 	=> '1' ), 'hints' => __( 'The number of days that the job listing will be active.', 'wp-job-manager-wc-paid-listings' ), 'value' => $_job_listing_duration ),
																				"_job_listing_featured" => array( 'label' => __('Feature Listings?', 'wp-job-manager-wc-paid-listings' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele job_package_price_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title checkbox_title wcfm_ele job_package' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Feature this job listing - it will be styled differently and sticky.', 'wp-job-manager-wc-paid-listings' ), 'value' => 'yes', 'dfvalue' => $_job_listing_featured ),
																				);
		$general_fields = array_merge( $general_fields, $job_listing_package_fields );
		
		return $general_fields;
	}
	
  /**
	 * WC Rental Product General options
	 */
	function wcfm_wcrental_product_manage_fields( $product_id = 0, $product_type ) {
		global $WCFM;
		
		$pricing_type = '';
		$hourly_price = '';
		$general_price = '';
		
		$redq_rental_availability = array();
		
		if( $product_id ) {
			$pricing_type = get_post_meta( $product_id, 'pricing_type', true );
			$hourly_price = get_post_meta( $product_id, 'hourly_price', true );
			$general_price = get_post_meta( $product_id, 'general_price', true );
			
			$redq_rental_availability = (array) get_post_meta( $product_id, 'redq_rental_availability', true );
		}
		
		
		?>
		
		<div class="page_collapsible products_manage_redq_rental redq_rental non-variable-subscription" id="wcfm_products_manage_form_redq_rental_head"><label class="wcfmfa fa-cab"></label><?php _e('Rental', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container redq_rental non-variable-subscription">
			<div id="wcfm_products_manage_form_redq_rental_expander" class="wcfm-content">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_redq_rental_fields', array( 
					"pricing_type" => array( 'label' => __('Set Price Type', 'wc-frontend-manager') , 'type' => 'select', 'options' => apply_filters( 'wcfm_redq_rental_pricing_options', array( 'general_pricing' => __( 'General Pricing', 'wc-frontend-manager' ) ) ), 'class' => 'wcfm-select wcfm_ele redq_rental', 'label_class' => 'wcfm_title redq_rental', 'value' => $pricing_type, 'hints' => __( 'Choose a price type - this controls the schema.', 'wc-frontend-manager' ) ),
					"hourly_price" => array( 'label' => __('Hourly Price', 'wc-frontend-manager') . '(' . get_woocommerce_currency_symbol() . ')' , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele redq_rental', 'label_class' => 'wcfm_title redq_rental', 'value' => $hourly_price, 'hints' => __( 'Hourly price will be applicabe if booking or rental days min 1day', 'wc-frontend-manager' ), 'placeholder' => __( 'Enter price here', 'wc-frontend-manager' ) ),
					"general_price" => array( 'label' => __('General Price', 'wc-frontend-manager') . '(' . get_woocommerce_currency_symbol() . ')' , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele rentel_pricing rental_general_pricing redq_rental', 'label_class' => 'wcfm_title rentel_pricing rental_general_pricing redq_rental', 'value' => $general_price, 'placeholder' => __( 'Enter price here', 'wc-frontend-manager' ) ),
					) ) );
				?>
			</div>
		</div>
		
		<div class="page_collapsible products_manage_redq_rental_availabillity redq_rental non-variable-subscription" id="wcfm_products_manage_form_redq_rental_availabillity_head"><label class="wcfmfa fa-clock"></label><?php _e('Availability', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container redq_rental non-variable-subscription">
			<div id="wcfm_products_manage_form_redq_rental_availabillity_expander" class="wcfm-content">
			<?php
			$WCFM->wcfm_fields->wcfm_generate_form_field( array( 
				"redq_rental_availability" =>   array('label' => __('Product Availabilities', 'wc-frontend-manager') , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele redq_rental', 'label_class' => 'wcfm_title redq_rental', 'desc' => __( 'Please select the date range to be disabled for the product.', 'wc-frontend-manager' ), 'desc_class' => 'avail_rules_desc', 'value' => $redq_rental_availability, 'options' => array(
											"type" => array('label' => __('Type', 'wc-frontend-manager'), 'type' => 'select', 'options' => array( 'custom_date' => __( 'Custom Date', 'wc-frontend-manager' )), 'class' => 'wcfm-select wcfm_ele avail_range_type redq_rental', 'label_class' => 'wcfm_title avail_rules_ele avail_rules_label redq_rental' ),
											"from" => array('label' => __('From', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_datepicker avail_rule_field avail_rule_custom avail_rules_ele avail_rules_text', 'label_class' => 'wcfm_title avail_rule_field avail_rule_custom avail_rules_ele avail_rules_label' ),
											"to" => array('label' => __('To', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_datepicker avail_rule_field avail_rule_custom avail_rules_ele avail_rules_text', 'label_class' => 'wcfm_title avail_rule_field avail_rule_custom avail_rules_ele avail_rules_label' ),
											"rentable" => array('label' => __('Bookable', 'wc-frontend-manager'), 'type' => 'select', 'options' => array( 'no' => __('NO', 'wc-frontend-manager') ), 'class' => 'wcfm-select wcfm_ele avail_rules_ele avail_rules_text redq_rental', 'label_class' => 'wcfm_title avail_rules_ele avail_rules_label' ),
											)	)
				) );
			?>
		</div>
	</div>
	<?php	
	}
	
	/**
	 * YITH Auction Free Product General options
	 * @since 3.0.4
	 */
	function wcfm_yithauction_free_product_manage_fields( $product_id = 0, $product_type ) {
		global $WCFM;
		
		$_yith_auction_for = '';
		$_yith_auction_to = '';
		
		if( $product_id ) {
			$_yith_auction_for = get_post_meta( $product_id, '_yith_auction_for', true );
			$_yith_auction_to = get_post_meta( $product_id, '_yith_auction_to', true );
			
			if( $_yith_auction_for ) $_yith_auction_for = get_date_from_gmt( date( 'Y-m-d H:i:s', absint( $_yith_auction_for ) ) );
			if( $_yith_auction_to ) $_yith_auction_to = get_date_from_gmt( date( 'Y-m-d H:i:s', absint( $_yith_auction_to ) ) );
		}
		
		?>
		<div class="page_collapsible products_manage_yithauction_free auction non-variable-subscription" id="wcfm_products_manage_form_auction_head"><label class="wcfmfa fa-gavel"></label><?php _e('Auction', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container auction non-variable-subscription">
			<div id="wcfm_products_manage_form_yithauction_free_expander" class="wcfm-content">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( array( 
					"_yith_auction_for" => array( 'label' => __('Auction Date From', 'wc-frontend-manager') , 'type' => 'text', 'placeholder' => 'YYYY-MM-DD hh:mm:ss', 'class' => 'wcfm-text wcfm_ele auction', 'label_class' => 'wcfm_title auction', 'value' => $_yith_auction_for, 'attributes' => array( "pattern" => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01]) (0[0-9]|1[0-9]|2[0-3]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" ) ),
					"_yith_auction_to" => array( 'label' => __('Auction Date To', 'wc-frontend-manager') , 'type' => 'text', 'placeholder' => 'YYYY-MM-DD hh:mm:ss', 'class' => 'wcfm-text wcfm_ele auction', 'label_class' => 'wcfm_title auction', 'value' => $_yith_auction_to, 'attributes' => array( "pattern" => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01]) (0[0-9]|1[0-9]|2[0-3]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" ) ),
					) );
				?>
			</div>
		</div>
		<?php
	}
	
	/**
   * Product Manage GEO my WP views
   */
	function wcfm_geomywp_products_manage_views( ) {
		global $WCFM;
	  
	  $WCFM->template->get_template( 'integrations/wcfm-view-geomywp-products-manage.php' );
	}
	
	/**
   * Product Manage Woocommerce Product Voucher General Fields - General
   */
	function wcfm_wc_product_voucher_product_manage_fields_general( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$_has_voucher = '';
		
		// Voucher options
		if( $product_id ) {
			$_has_voucher = ( get_post_meta( $product_id, '_has_voucher', true ) == 'yes' ) ? 'yes' : '';
		}
		
		$general_fields = array_slice($general_fields, 0, 1, true) + 
													array(
														"_has_voucher" => array( 'desc' => __( 'Has Voucher', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'desc_class' => 'wcfm_title wcfm_ele downloadable_ele_title checkbox_title simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => 'yes', 'dfvalue' => $_has_voucher),
														) +
											array_slice($general_fields, 1, count($general_fields) - 1, true) ;
		
		return $general_fields;
	}
	
	/**
   * Product Manage Woocommerce Product Voucher Template Fields - General
   */
	function wcfm_wc_product_voucher_product_manage_fields_pricing( $pricing_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$_voucher_template_id = '';
		
		// Voucher options
		if( $product_id ) {
			$_voucher_template_id = get_post_meta( $product_id, '_voucher_template_id', true );
		}
		
		// Fetching Voucher Templates
		$args = array(  'posts_per_page'   => -1,
										'offset'           => 0,
										'post_type'        => 'wc_voucher_template',
										'post_status'      => array('any'),
										'suppress_filters' => 0 
									);
		$wc_voucher_templates = get_posts( $args ); 
		$wc_voucher_templates_array = array( '' => __( '-- Choose Template --', 'wc-frontend-manager' ) );
		foreach ( $wc_voucher_templates as $wc_voucher_template ) {
			$wc_voucher_templates_array[$wc_voucher_template->ID] = $wc_voucher_template->post_title;
		}
		
		$wcfm_voucher_template_fields = array(
																				"_voucher_template_id" => array( 'label' => __('Voucher Template', 'wc-frontend-manager'), 'type' => 'select', 'options' => $wc_voucher_templates_array, 'class' => 'wcfm-select wcfm_ele simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $_voucher_template_id, 'hints' => __( 'Select a voucher template to make this into a voucher product.', 'wc-frontend-manager') )
																				);
		
		$pricing_fields = array_merge( $pricing_fields, $wcfm_voucher_template_fields );
		
		return $pricing_fields;
	}
	
	/**
   * Product Manage Woocommerce Germanized Fields - General
   */
	function wcfm_woocommerce_germanized_product_manage_fields_general( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		// Servive options
		$_service = ( get_post_meta( $product_id, '_service', true) == 'yes' ) ? 'yes' : '';
		$_differential_taxation = ( get_post_meta( $product_id, '_differential_taxation', true) == 'yes' ) ? 'yes' : '';
		if( $product_type != 'simple' ) $_service = '';
		
		$general_fields = array_slice($general_fields, 0, 1, true) + 
													array(
														"_service" => array( 'desc' => __( 'Service', 'woocommerce-germanized') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'desc_class' => 'wcfm_title wcfm_ele virtual_ele_title checkbox_title simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => 'yes', 'dfvalue' => $_service),
														"_differential_taxation" => array( 'desc' => __( 'Diff. Taxation', 'woocommerce-germanized') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele simple variable external groupd wcfm_half_ele_checkbox simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'desc_class' => 'wcfm_title wcfm_ele simple variable external groupd downloadable_ele_title checkbox_title simple non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => 'yes', 'dfvalue' => $_differential_taxation),
														) +
											array_slice($general_fields, 1, count($general_fields) - 1, true) ;
		
		return $general_fields;
	}
	
	/**
   * Product Manage Woocommerce Germanized Fields - Content
   */
	function wcfm_woocommerce_germanized_product_manage_fields_content( $content_fields, $product_id, $product_type ) {
		global $WCFM, $WCFMu;
		
		$_mini_desc = '';
		
		if( $product_id ) {
			$_product = wc_get_product( $product_id );
			$wc_gzd_product = wc_gzd_get_product( $_product );
			$_mini_desc = get_post_meta( $product_id, '_mini_desc', true );
		}
		
		$woocommerce_germanized_content_fields =  array(
																				"_mini_desc" => array('label' => __( 'Optional Mini Description', 'woocommerce-germanized' ), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => __( 'This content will be shown as short product description within checkout and emails.', 'woocommerce-germanized' ), 'value' => $_mini_desc ),
																				);
		
		$content_fields = array_merge( $woocommerce_germanized_content_fields, $content_fields );
		
		return $content_fields;
	}
	
	/**
   * Product Manage Woocommerce Germanized Fields - Pricing
   */
	function wcfm_woocommerce_germanized_product_manage_fields_pricing( $pricing_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM, $WCFMu;
		
		$_sale_price_label = '';
		$_sale_price_regular_label = '';
		$_unit = '';
		$_unit_product = '';
		$_unit_base = '';
		$_unit_price_auto = 'no';
		$_unit_price_regular = '';
		$_unit_price_sale = '';
		$_min_age         = '';
		$_ts_gtin         = '';
		$_ts_mpn          = '';
		$age_select   = wc_gzd_get_age_verification_min_ages_select();
		
		if( $product_id ) {
			$_product = wc_get_product( $product_id );
			$wc_gzd_product = wc_gzd_get_product( $_product );
			$_sale_price_label = get_post_meta( $product_id, '_sale_price_label', true );
			$_sale_price_regular_label = get_post_meta( $product_id, '_sale_price_regular_label', true );
			$_unit = get_post_meta( $product_id, '_unit', true );
			$_unit_product = get_post_meta( $product_id, '_unit_product', true );
			$_unit_base = get_post_meta( $product_id, '_unit_base', true );
			$_unit_price_auto = get_post_meta( $product_id, '_unit_price_auto', true ) ? get_post_meta( $product_id, '_unit_price_auto', true ) : 'no';
			$_unit_price_regular = get_post_meta( $product_id, '_unit_price_regular', true );
			$_unit_price_sale = get_post_meta( $product_id, '_unit_price_sale', true );
			$_min_age         = get_post_meta( $product_id, '_min_age', true );
			$_ts_gtin         = get_post_meta( $product_id, '_ts_gtin', true );
			$_ts_mpn          = get_post_meta( $product_id, '_ts_mpn', true );
		}
		
		$woocommerce_germanized_pricing_fields =  array(
																				"_sale_price_label" => array('label' => __( 'Sale Label', 'woocommerce-germanized' ), 'type' => 'select', 'options' => array_merge( array( "-1" => __( 'Select Price Label', 'woocommerce-germanized' ) ), WC_germanized()->price_labels->get_labels() ), 'class' => 'wcfm-select wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'If the product is on sale you may want to show a price label right before outputting the old price to inform the customer.', 'woocommerce-germanized' ), 'value' => $_sale_price_label),
																				"_sale_price_regular_label" => array('label' => __( 'Sale Regular Label', 'woocommerce-germanized' ), 'type' => 'select', 'options' => array_merge( array( "-1" => __( 'Select Price Label', 'woocommerce-germanized' ) ), WC_germanized()->price_labels->get_labels() ), 'class' => 'wcfm-select wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'If the product is on sale you may want to show a price label right before outputting the new price to inform the customer.', 'woocommerce-germanized' ), 'value' => $_sale_price_regular_label),
																				"_unit" => array('label' => __( 'Unit', 'woocommerce-germanized' ), 'type' => 'select', 'options' => array_merge( array( "-1" => __( 'Select unit', 'woocommerce-germanized' ) ), WC_germanized()->units->get_units() ), 'class' => 'wcfm-select wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Needed if selling on a per unit basis', 'woocommerce-germanized' ), 'value' => $_unit ),
																				"_unit_product" => array('label' => __( 'Product Units', 'woocommerce-germanized' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title variable simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Number of units included per default product price. Example: 1000 ml.', 'woocommerce-germanized' ), 'value' => $_unit_product ),
																				"_unit_base" => array('label' => __( 'Base Price Units', 'woocommerce-germanized' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title variable simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Base price units. Example base price: 0,99 € / 100 ml. Insert 100 as base price unit amount.', 'woocommerce-germanized' ), 'value' => $_unit_base ),
																				"_unit_price_auto" => array('label' => __( 'Calculation', 'woocommerce-germanized' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele variable checkbox_title wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Calculate base prices automatically.', 'woocommerce-germanized' ), 'dfvalue' => $_unit_price_auto, 'value' => 'yes' ),
																				"_unit_price_regular" => array('label' => __( 'Regular Base Price', 'woocommerce-germanized' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $_unit_price_regular),
																				"_unit_price_sale" => array('label' => __( 'Sale Base Price', 'woocommerce-germanized' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $_unit_price_sale),
																				"_min_age" => array('label' => __( 'Minimum Age', 'woocommerce-germanized' ), 'type' => 'select', 'options' => $age_select, 'class' => 'wcfm-select wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Adds an age verification checkbox while purchasing this product.', 'woocommerce-germanized' ), 'value' => $_min_age),
																				'_ts_gtin' => array( 'label' => _x( 'GTIN', 'trusted-shops', 'woocommerce-germanized' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => _x( 'ID that allows your products to be identified worldwide. If you want to display your Trusted Shops Product Reviews in Google Shopping and paid Google adverts, Google needs the GTIN.', 'trusted-shops', 'woocommerce-germanized' ), 'value' => $_ts_gtin ),
																				'_ts_mpn' => array( 'label' => _x( 'MPN', 'trusted-shops', 'woocommerce-germanized' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => _x( 'If you don\'t have a GTIN for your products, you can pass the brand name and the MPN on to Google to use the Trusted Shops Google Integration.', 'trusted-shops', 'woocommerce-germanized' ), 'value' => $_ts_mpn )
		
																				);
		
		$pricing_fields = array_merge( $pricing_fields, $woocommerce_germanized_pricing_fields );
		
		return $pricing_fields;
	}
	
	/**
   * Product Manage Woocommerce Germanized Fields - Shipping
   */
	function wcfm_woocommerce_germanized_product_manage_fields_shipping( $shipping_fields, $product_id ) {
		global $WCFM, $WCFMu;
		
		$delivery_time = '';
		$_free_shipping = '';
		
		if( $product_id ) {
			$_product = wc_get_product( $product_id );
			$wc_gzd_product = wc_gzd_get_product( $_product );
			$_free_shipping = get_post_meta( $product_id, '_free_shipping', true ) ? get_post_meta( $product_id, '_free_shipping', true ) : 'no';
			$delivery_time = $wc_gzd_product->get_delivery_time();
			
			if( $delivery_time ) $delivery_time = $delivery_time->term_id;
		}
		
		$delivery_time_list = array( "" => __( 'Select Delivery Time', 'wc-frontend-manager' ) );
		$terms = get_terms( 'product_delivery_time', array( 'hide_empty' => false ) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term )
				$delivery_time_list[ $term->term_id ] = $term->name;
		}
		
		$woocommerce_germanized_shipping_fields =  array(
																				"delivery_time" => array('label' => __( 'Delivery Time', 'woocommerce-germanized' ), 'type' => 'select', 'options' => $delivery_time_list, 'class' => 'wcfm-select wcfm_ele simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title simple external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'value' => $delivery_time),
																				"_free_shipping" => array('label' => __( 'Free shipping?', 'woocommerce-germanized' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele checkbox_title wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => __( 'This option disables the "plus shipping costs" notice on product page', 'woocommerce-germanized' ), 'value' => 'yes', 'dfvalue' => $_free_shipping),
																				);
		
		$shipping_fields = array_merge( $shipping_fields, $woocommerce_germanized_shipping_fields );
		
		return $shipping_fields;
	}
	
	/**
   * Product Manage Woocommerce Germanized Fields - Variation
   */
	function wcfm_woocommerce_germanized_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		$delivery_time_list = array( "" => __( 'Select Delivery Time', 'wc-frontend-manager' ) );
		$terms = get_terms( 'product_delivery_time', array( 'hide_empty' => false ) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term )
				$delivery_time_list[ $term->term_id ] = $term->name;
		}
		
		$age_select   = wc_gzd_get_age_verification_min_ages_select();
		
		$woocommerce_germanized_fields =  array(
																				"_sale_price_label" => array('label' => __( 'Sale Label', 'woocommerce-germanized' ), 'type' => 'select', 'options' => array_merge( array( "-1" => __( 'Select Price Label', 'woocommerce-germanized' ) ), WC_germanized()->price_labels->get_labels() ), 'class' => 'wcfm-select wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => __( 'If the product is on sale you may want to show a price label right before outputting the old price to inform the customer.', 'woocommerce-germanized' ) ),
																				"_sale_price_regular_label" => array('label' => __( 'Sale Regular Label', 'woocommerce-germanized' ), 'type' => 'select', 'options' => array_merge( array( "-1" => __( 'Select Price Label', 'woocommerce-germanized' ) ), WC_germanized()->price_labels->get_labels() ), 'class' => 'wcfm-select wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => __( 'If the product is on sale you may want to show a price label right before outputting the new price to inform the customer.', 'woocommerce-germanized' ) ),
																				"_unit_product" => array('label' => __( 'Product Units', 'woocommerce-germanized' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => __( 'Number of units included per default product price. Example: 1000 ml.', 'woocommerce-germanized' ) ),
																				"_unit_price_regular" => array('label' => __( 'Regular Base Price', 'woocommerce-germanized' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking'),
																				"_unit_price_sale" => array('label' => __( 'Sale Base Price', 'woocommerce-germanized' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking' ),
																				"_min_age" => array('label' => __( 'Minimum Age', 'woocommerce-germanized' ), 'type' => 'select', 'options' => $age_select, 'class' => 'wcfm-select wcfm_ele variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => __( 'Adds an age verification checkbox while purchasing this product.', 'woocommerce-germanized' ) ),
																				"delivery_time" => array('label' => __( 'Delivery Time', 'woocommerce-germanized' ), 'type' => 'select', 'options' => $delivery_time_list, 'class' => 'wcfm-select wcfm_ele variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking' ),
																				'_ts_gtin' => array( 'label' => _x( 'GTIN', 'trusted-shops', 'woocommerce-germanized' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => _x( 'ID that allows your products to be identified worldwide. If you want to display your Trusted Shops Product Reviews in Google Shopping and paid Google adverts, Google needs the GTIN.', 'trusted-shops', 'woocommerce-germanized' ) ),
																				'_ts_mpn' => array( 'label' => _x( 'MPN', 'trusted-shops', 'woocommerce-germanized' ), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => _x( 'If you don\'t have a GTIN for your products, you can pass the brand name and the MPN on to Google to use the Trusted Shops Google Integration.', 'trusted-shops', 'woocommerce-germanized' ) ),
																				"_service" => array( 'label' => __( 'Service', 'woocommerce-germanized') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele variable non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'label_class' => 'wcfm_title wcfm_ele variable checkbox_title non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking', 'value' => 'yes' ),
																				"_mini_desc" => array('label' => __( 'Optional Mini Description', 'woocommerce-germanized' ), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'label_class' => 'wcfm_ele wcfm_title wcfm_full_ele variable non-variable-subscription non-auction non-redq_rental non-accommodation-booking', 'hints' => __( 'This content will be shown as short product description within checkout and emails.', 'woocommerce-germanized' ) ),
																				);
		
		$variation_fileds = array_merge( $variation_fileds, $woocommerce_germanized_fields );
		
		return $variation_fileds;
	}
	
	/**
   * Product Manage Woocommerce Germanized Fields - Variation Data
   */
	function wcfm_woocommerce_germanized_product_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMu;
		
		if( $variation_id ) {
			$_product = wc_get_product( $variation_id );
			$wc_gzd_product = wc_gzd_get_product( $_product );
			
			$delivery_time = $wc_gzd_product->get_delivery_time();
			if( $delivery_time ) $delivery_time = $delivery_time->term_id;
			
			$variations[$variation_id_key]['_service'] = ( get_post_meta( $variation_id, '_service', true) == 'yes' ) ? 'yes' : '';
		  $variations[$variation_id_key]['_sale_price_label'] = get_post_meta( $variation_id, '_sale_price_label', true );
			$variations[$variation_id_key]['_sale_price_regular_label'] = get_post_meta( $variation_id, '_sale_price_regular_label', true );
			$variations[$variation_id_key]['_unit_product'] = get_post_meta( $variation_id, '_unit_product', true );
			$variations[$variation_id_key]['_unit_price_regular'] = get_post_meta( $variation_id, '_unit_price_regular', true );
			$variations[$variation_id_key]['_unit_price_sale'] = get_post_meta( $variation_id, '_unit_price_sale', true );
			$variations[$variation_id_key]['_min_age'] = get_post_meta( $variation_id, '_min_age', true );
			$variations[$variation_id_key]['_ts_gtin'] = get_post_meta( $variation_id, '_ts_gtin', true );
			$variations[$variation_id_key]['_ts_mpn'] = get_post_meta( $variation_id, '_ts_mpn', true );
			$variations[$variation_id_key]['delivery_time'] = $delivery_time;
			$variations[$variation_id_key]['_mini_desc'] = get_post_meta( $variation_id, '_mini_desc', true );
		}
		
		return $variations;
	}
	
	/**
	 * Product manage Epeken Plugins View
	 */
	function wcfm_wcepeken_product_manage_views() {
		global $WCFM;
	  $WCFM->template->get_template( 'integrations/wcfm-view-epeken-products-manage.php' );
	}
	
	/**
	 * Product manage WDM Scheduler Plugins View
	 */
	function wcfm_wdm_scheduler_product_manage_views() {
		global $wp, $WCFM;
		
		$product_id = 0;
		if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
			$product_id = $wp->query_vars['wcfm-products-manage'];
		}
		?>
		<div class="page_collapsible products_manage_scheduler simple variable nonvirtual booking" id="wcfm_products_manage_form_scheduler_head"><label class="wcfmfa fa-clock"></label><?php _e('Scheduler Config', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container simple variable nonvirtual booking">
			<div id="wcfm_products_manage_form_scheduler_expander" class="wcfm-content">
			  <?php
				$scheduler_admin = new SchedulerAdmin();
				$scheduler_admin->wdmStartEndDate( $product_id );
				?>
			</div>
		</div>
		<?php
		wdmCpbEnqueueScripts( 'post-new.php' );
	}
	
	/**
	 * Product Manager WC Product Scheduler Plugins View
	 */
	function wcfm_wc_product_scheduler_product_manage_views() {
		global $wp, $WCFM;
		
		$product_id = 0;
		
		$status = 0;
		
		$start_date = '';
		$st_hh = '';
		$st_mn = '';
		
		$end_date = '';
		$end_hh = '';
		$end_mn = '';
		
		$countdown = 0;
		
		if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
			$product_id = absint( $wp->query_vars['wcfm-products-manage'] );
			
			if( $product_id ) {
				$status      = get_post_meta( $product_id, 'wpas_schedule_sale_status', true );   
				$start_time  = get_post_meta( $product_id, 'wpas_schedule_sale_st_time', true );   
				$end_time    = get_post_meta( $product_id, 'wpas_schedule_sale_end_time', true );   
				$mode        = get_post_meta( $product_id, 'wpas_schedule_sale_mode', true );   
				$countdown   = get_post_meta( $product_id, 'wpas_schedule_sale_countdown', true );  
				
				if( !empty($start_time) ) {
					$start_date = date('Y-m-d', $start_time);
					$st_mm      = date('m', $start_time);
					$st_dd      = date('d', $start_time);
					$st_hh      = date('H', $start_time);
					$st_mn      = date('i', $start_time);
				}
				
				if( isset($end_time) &!empty($end_time) ) {
					$end_date  = date('Y-m-d', $end_time);
					$end_mm    = date('m', $end_time);
					$end_dd    = date('d', $end_time);
					$end_hh    = date('H', $end_time);
					$end_mn    = date('i', $end_time);
				}
			}
		}
		?>
		<div class="page_collapsible products_manage_wc_product_scheduler simple variable external booking" id="wcfm_products_manage_form_wc_product_scheduler_head"><label class="wcfmfa fa-clock"></label><?php _e('Availability Scheduler', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container simple variable external booking">
			<div id="wcfm_products_manage_form_wc_product_scheduler_expander" class="wcfm-content">
			  <?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_wc_product_scheduler_fields', array(  
						
						"wpas_select_status" => array('label' => __( 'Status', 'wc-frontend-manager' ) , 'type' => 'select', 'options' => array( '0' => __( 'Disable', 'wc-frontend-manager'), '1' => __( 'Enable', 'wc-frontend-manager' ) ), 'class' => 'wcfm-select wcfm_ele simple variable external booking', 'label_class' => 'wcfm_title simple variable external booking', 'value' => $status ),
						
						"wpas_st_date" => array( 'label' => __( 'Start Time', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __('From', 'wc-frontend-manager') . '... YYYY-DD-MM', 'custom-attributes' => array( 'date_format' => 'yy-mm-dd' ), 'class' => 'wcfm-text wcfm_ele wcfm_small_ele wcfm_datepicker simple variable external booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external booking', 'value' => $start_date ),
						"wpas_st_hh" => array( 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_small_ele wcfm_non_negative_input simple variable external booking', 'label_class' => 'wcfm_title simple variable external booking', 'attributes' => array( 'min' => 0, 'max' => 12, 'step' => 1 ), 'value' => $st_hh ),
						"wpas_st_mn" => array( 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_small_ele wcfm_non_negative_input simple variable external booking', 'label_class' => 'wcfm_title simple variable external booking', 'attributes' => array( 'min' => 0, 'max' => 60, 'step' => 1 ), 'value' => $end_mn ),
						
						"wpas_end_date" => array( 'label' => __( 'End Time', 'wc-frontend-manager'), 'type' => 'text', 'placeholder' => __('Upto', 'wc-frontend-manager') . '... YYYY-DD-MM', 'custom-attributes' => array( 'date_format' => 'yy-mm-dd' ), 'class' => 'wcfm-text wcfm_ele wcfm_small_ele wcfm_datepicker simple variable external booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external booking', 'value' => $end_date ),
						"wpas_end_hh" => array( 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_small_ele wcfm_non_negative_input simple variable external booking', 'label_class' => 'wcfm_title simple variable external booking', 'attributes' => array( 'min' => 0, 'max' => 12, 'step' => 1 ), 'value' => $end_hh ),
						"wpas_end_mn" => array( 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_small_ele wcfm_non_negative_input simple variable external booking', 'label_class' => 'wcfm_title simple variable external booking', 'attributes' => array( 'min' => 0, 'max' => 60, 'step' => 1 ), 'value' => $end_mn ),
						
						"wpas_enable_countdown" => array('label' => __( 'CountDown', 'wc-frontend-manager' ) , 'type' => 'select', 'options' => array( '0' => __( 'Disable', 'wc-frontend-manager'), '1' => __( 'Enable', 'wc-frontend-manager' ) ), 'class' => 'wcfm-select wcfm_ele simple variable external booking', 'label_class' => 'wcfm_title simple variable external booking', 'value' => $countdown ),
						
						
																															), $product_id ) );
				?>
				<div class="wcfm-clearfix"></div><br />
				<p class="description instructions"><?php _e( 'Note: Start time and End time will be on GMT, Current GMT time is', 'wc-frontend-manager' );  echo ': ' .  date( "Y-m-d @ H:i", time() ); ?></p>
			</div>
		</div>
		<?php
	}
	
	/**
	 * WooCommerce Country Based Restriction
	 */
	function wcfm_woo_country_based_restriction_product_manage_views() {
		global $wp, $WCFM;
		
		$product_id = 0;
		
		$_fz_country_restriction_type = '';
		$_restricted_countries = array();
		
		if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
			$product_id = absint( $wp->query_vars['wcfm-products-manage'] );
			
			if( $product_id ) {
				$_fz_country_restriction_type      = get_post_meta( $product_id, '_fz_country_restriction_type', true );   
				$_restricted_countries             = get_post_meta( $product_id, '_restricted_countries', true );  
			}
		}
		?>
		<div class="page_collapsible products_manage_woo_country_based_restriction simple variable external booking" id="wcfm_products_manage_form_woo_country_based_restriction_head"><label class="wcfmfa fa-globe"></label><?php _e( 'Country restrictions', 'woo-product-country-base-restrictions' ); ?><span></span></div>
		<div class="wcfm-container simple variable external booking">
			<div id="wcfm_products_manage_form_woo_country_based_restriction_expander" class="wcfm-content">
			  <?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_woo_country_based_restriction_fields', array(  
						
						"_fz_country_restriction_type" => array('label' => __( 'Restriction rule', 'woo-product-country-base-restrictions' ) , 'type' => 'select', 'options' => array( 'all' => __( 'Product Available for all countries', 'woo-product-country-base-restrictions' ), 'specific'  => __( 'Product Available for selected countries', 'woo-product-country-base-restrictions' ), 'excluded' => __( 'Product not Available for selected countries', 'woo-product-country-base-restrictions' ) ), 'class' => 'wcfm-select wcfm_ele simple variable external booking', 'label_class' => 'wcfm_title simple variable external booking', 'value' => $_fz_country_restriction_type ),
						
						"_restricted_countries" => array('label' => __( 'Select countries', 'woo-product-country-base-restrictions' ), 'type' => 'country', 'wcfm_shipping_country' => true, 'attributes' => array( 'multiple' => true ), 'class' => 'wcfm-select wcfm_ele simple variable external booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external booking', 'value' => $_restricted_countries ),
						
																															), $product_id ) );
				?>
				<div class="wcfm-clearfix"></div><br />
			</div>
		</div>
		<?php
	}
	
	/**
	 * Product Manager WC Product Tiered Price Table Plugins View
	 */
	function wcfm_wc_tiered_price_table_product_manage_views() {
		global $wp, $WCFM;
		
		$WCFM->template->get_template( 'integrations/wcfm-view-wc-tiered-price-table-products-manage.php' );
	}
	
	/**
   * Product Manager WC Product Tiered Price Table Fields - Variation
   */
	function wcfm_wc_tiered_price_table_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		$price_rules_types = array( 'fixed' => __( 'Fixed', 'tier-pricing-table' ) );
		if( WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
			$price_rules_types = array( 'fixed' => __( 'Fixed', 'tier-pricing-table' ), 'percentage' => __( 'Percentage', 'tier-pricing-table' ) );
		}
		
		$wc_tiered_price_fields =  array(  
			
			  "tiered_pricing_minimum" => array('label' => __( "Minimum quantity", 'tier-pricing-table' ) , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_title variable', 'hints' => __( 'Set if you are selling the product from qty more than 1', 'tier-pricing-table' ) ),
			
				"tiered_price_rules_type" => array('label' => __( "Tiered pricing type", 'tier-pricing-table' ) , 'type' => 'select', 'options' => $price_rules_types, 'class' => 'wcfm-select wcfm_ele variable variation_tiered_price_rules_type', 'label_class' => 'wcfm_title variable' ),
				
				"tiered_fixed_price_rules" => array('label' => __( "Tiered price", 'tier-pricing-table' ) , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele variable tiered_price_rule_type tiered_price_rule_type_fixed', 'label_class' => 'wcfm_title tiered_price_rule_type tiered_price_rule_type_fixed', 'options' => array(
																																																										"quantity" => array('label' => __( 'Quantity', 'tier-pricing-table' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title variable' ),
																																																										"price" => array('label' => __('Price', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title variable' )
																																																										)
																																															),
				
				"tiered_percent_price_rules" => array('label' => __( "Tiered price", 'tier-pricing-table' ) , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele variable tiered_price_rule_type tiered_price_rule_type_percentage', 'label_class' => 'wcfm_title tiered_price_rule_type tiered_price_rule_type_percentage', 'options' => array(
																																																										"quantity" => array('label' => __( 'Quantity', 'tier-pricing-table' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title variable' ),
																																																										"discount" => array('label' => __('Percent discount', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title variable' )
																																																										)
																																															)
				
				
																													);
		
		if( !WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
			unset( $wc_tiered_price_fields['tiered_pricing_minimum'] );
		 	 unset( $wc_tiered_price_fields['tiered_percent_price_rules'] );
		 }
		
		$variation_fileds = array_slice($variation_fileds, 0, 12, true) +
																	$wc_tiered_price_fields +
																	array_slice($variation_fileds, 12, count($variation_fileds) - 1, true) ;
		
		return $variation_fileds;
	}
	
	/**
   * Product Manager WC Product Tiered Price Table Fields - Variation Data
   */
	function wcfm_wc_tiered_price_table_product_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMu;
		
		if( $variation_id ) {
			$pricing_type = get_post_meta( $variation_id, '_tiered_price_rules_type', true );
			$fixed_price_rules = array();
			
			$price_rules = get_post_meta( $variation_id, '_fixed_price_rules', true );
			
			if ( ! empty( $price_rules ) ) {
				foreach ( $price_rules as $amount => $price ) {
					$fixed_price_rules[] = array( 'quantity' => $amount, 'price' => $price );
				}
			}
			
			$variations[$variation_id_key]['tiered_price_rules_type'] = $pricing_type;
		  $variations[$variation_id_key]['tiered_fixed_price_rules'] = $fixed_price_rules;
		  
		  if( WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
		  	$percent_price_rules = array();
		  	
		  	$price_rules = get_post_meta( $variation_id, '_percentage_price_rules', true );
				if ( ! empty( $price_rules ) ) {
					foreach ( $price_rules as $amount => $price ) {
						$percent_price_rules[] = array( 'quantity' => $amount, 'discount' => $price );
					}
				}
				
				$variations[$variation_id_key]['tiered_percent_price_rules'] = $percent_price_rules;
				
				$variations[$variation_id_key]['tiered_pricing_minimum'] = get_post_meta( $variation_id, '_tiered_price_minimum_qty', true );
		  }
		}
		
		return $variations;
	}
	
	/**
	 * Product Manager WC German Market Plugins Pricing Fields
	 */
	function wcfm_wc_german_market_product_pricing_fields( $pricing_fields, $product_id, $product_type ) {
		global $wp, $WCFM;
		
		$_lieferzeit = '';
		$_suppress_shipping_notice = '';
		$_alternative_shipping_information = '';
		$_sale_label = '';
		$_gm_gtin = '';
		
		if( $product_id ) {
			$_lieferzeit          = maybe_unserialize( get_post_meta( $product_id, '_lieferzeit', TRUE ) );
			$_suppress_shipping_notice = maybe_unserialize( get_post_meta( $product_id, '_suppress_shipping_notice', TRUE ) );
			$_alternative_shipping_information = get_post_meta( $product_id, '_alternative_shipping_information', TRUE );
			$_sale_label = get_post_meta( $product_id, '_sale_label', TRUE );
			$_gm_gtin    = get_post_meta( $product_id, '_gm_gtin', TRUE );
		}
		
		$terms = get_terms( 'product_delivery_times', array( 'orderby' => 'id', 'hide_empty' => 0 ) );
		$_lieferzeit_options = array( '-1' => __( 'Select', 'woocommerce-german-market' ) );
		foreach ( $terms as $i ) {
			$_lieferzeit_options[$i->term_id] = $i->name;
		}
		
		$terms = get_terms( 'product_sale_labels', array( 'orderby' => 'id', 'hide_empty' => 0 ) );
		$_sale_label_options = array( '-2' => __( 'Select', 'woocommerce-german-market' ), '-1' => __( 'Use the default', 'woocommerce-german-market' ) );
		foreach ( $terms as $i ) {
			$_sale_label_options[$i->term_id] = $i->name;
		}
		
		$wcfm_wc_german_market_pricing_fields = apply_filters( 'wcfm_wc_german_market_pricing_fields', array(  
				"_lieferzeit" => array('label' => __( 'Delivery Time:', 'woocommerce-german-market' ) , 'type' => 'select', 'options' => $_lieferzeit_options, 'class' => 'wcfm-select wcfm_ele simple variable', 'label_class' => 'wcfm_title simple variable', 'value' => $_lieferzeit ),
				"_alternative_shipping_information" => array('label' => __( 'Alternative Shipping Information', 'woocommerce-german-market' ) , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable', 'label_class' => 'wcfm_title simple variable', 'value' => $_alternative_shipping_information, 'hints' => __( 'Instead of the general shipping information you can enter a special information just for this product.', 'woocommerce-german-market' ) ),
				"_suppress_shipping_notice" => array('label' => __( 'Disable Shipping Information', 'woocommerce-german-market' ) , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele simple variable wcfm_non_negative_input', 'label_class' => 'wcfm_title checkbox-title checkbox_title simple variable', 'value' => 'on', 'dfvalue' => $_suppress_shipping_notice, 'hints' => __( 'Don’t display shipping information for this product (e.g. if it is virtual/digital).', 'woocommerce-german-market' ) ),
				"_sale_label" => array('label' => __( 'Sale Label:', 'woocommerce-german-market' ) , 'type' => 'select', 'options' => $_sale_label_options, 'class' => 'wcfm-select wcfm_ele simple variable', 'label_class' => 'wcfm_title simple variable', 'value' => $_sale_label ),
																													), $product_id );
		
		if ( get_option( 'gm_gtin_activation', 'off' ) == 'on' ) {
			$wcfm_wc_german_market_pricing_fields['_gm_gtin'] = array('label' => __( 'GTIN', 'woocommerce-german-market' ) , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple', 'label_class' => 'wcfm_title wcfm_ele simple', 'value' => $_gm_gtin );
		}
		
		$pricing_fields = array_merge( $pricing_fields, $wcfm_wc_german_market_pricing_fields );
		
		return $pricing_fields;
	}
	
	/**
	 * Product Manager WC German Market Plugins View
	 */
	function wcfm_wc_german_market_product_manage_views() {
		global $wp, $WCFM;
		
		$WCFM->template->get_template( 'integrations/wcfm-view-wc-german-market-products-manage.php' );
	}
	
	/**
   * Product Manager WC German Market Fields - Variation
   */
	function wcfm_wc_german_market_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		$terms = get_terms( 'product_delivery_times', array( 'orderby' => 'id', 'hide_empty' => 0 ) );
		$_lieferzeit_options = array( '-1' => __( 'Same as parent', 'woocommerce-german-market' ) );
		foreach ( $terms as $i ) {
			$_lieferzeit_options[$i->term_id] = $i->name;
		}
		
		$terms = get_terms( 'product_sale_labels', array( 'orderby' => 'id', 'hide_empty' => 0 ) );
		$_sale_label_options = array( '-1' => __( 'Same as parent', 'woocommerce-german-market' ), '-2' => __( 'Select', 'woocommerce-german-market' ) );
		foreach ( $terms as $i ) {
			$_sale_label_options[$i->term_id] = $i->name;
		}
		
		$wcfm_wc_german_market_pricing_fields = apply_filters( 'wcfm_wc_german_market_variation_pricing_fields', array(  
				"_lieferzeit" => array('label' => __( 'Delivery Time:', 'woocommerce-german-market' ) , 'type' => 'select', 'options' => $_lieferzeit_options, 'class' => 'wcfm-select wcfm_ele variable', 'label_class' => 'wcfm_title variable' ),
				"variable_used_setting_ppu" => array('label' => __( 'Price per Unit', 'woocommerce-german-market' ) , 'type' => 'select', 'options' => array( '-1' => __( 'Same as parent', 'woocommerce-german-market' ) ), 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele' ),
				"_variable_used_setting_shipping_info" => array('label' => __( 'Shipping Information', 'woocommerce-german-market' ) , 'type' => 'select', 'options' => array( '-1' => __( 'Same as parent', 'woocommerce-german-market' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele' ),
				"_sale_label" => array('label' => __( 'Sale Label:', 'woocommerce-german-market' ) , 'type' => 'select', 'options' => $_sale_label_options, 'class' => 'wcfm-select wcfm_ele variable', 'label_class' => 'wcfm_title variable' ),
																													) );
		
		if ( get_option( 'gm_gtin_activation', 'off' ) == 'on' ) {
			$wcfm_wc_german_market_pricing_fields['_gm_gtin'] = array('label' => __( 'GTIN', 'woocommerce-german-market' ) , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_title variable' );
		}
		
		if ( get_option( 'german_market_age_rating', 'off' ) == 'on' ) {
			//$wcfm_wc_german_market_pricing_fields['_age_rating_age'] = array('label' => __( 'Required age to buy this product', 'woocommerce-german-market' ) . ' ('.__( 'Years', 'woocommerce-german-market' ).')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_title variable' );
		}
		
	
		
		$variation_fileds = array_slice($variation_fileds, 0, 12, true) +
																	$wcfm_wc_german_market_pricing_fields +
																	array_slice($variation_fileds, 12, count($variation_fileds) - 1, true) ;
		
		return $variation_fileds;
	}
	
	/**
   * Product Manager WC German Market Fields - Variation Data
   */
	function wcfm_wc_german_market_product_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMu;
		
		if( $variation_id ) {
			$variations[$variation_id_key]['_lieferzeit'] = get_post_meta( $variation_id, '_lieferzeit', true );
			$variations[$variation_id_key]['variable_used_setting_ppu'] = get_post_meta( $variation_id, 'variable_used_setting_ppu', true );
			$variations[$variation_id_key]['_variable_used_setting_shipping_info'] = get_post_meta( $variation_id, '_variable_used_setting_shipping_info', true );
			$variations[$variation_id_key]['_sale_label'] = get_post_meta( $variation_id, '_sale_label', true );
			$variations[$variation_id_key]['_gm_gtin'] = get_post_meta( $variation_id, '_gm_gtin', true );
			//$variations[$variation_id_key]['_age_rating_age'] = get_post_meta( $variation_id, '_age_rating_age', true );
		}
		
		return $variations;
	}
	
	/**
	 * Vendor Settings Epeken Plugins View
	 */
	function wcfm_wcepeken_settings_views( $user_id ) {
		global $WCFM;
		
		$vendor_data_asal_kota = get_user_meta(intval($user_id), 'vendor_data_kota_asal', true);
		$vendor_jne_reg = get_user_meta(intval($user_id), 'vendor_jne_reg', true);
		$vendor_jne_oke = get_user_meta(intval($user_id), 'vendor_jne_oke', true);
		$vendor_jne_yes = get_user_meta(intval($user_id), 'vendor_jne_yes', true);
		$vendor_tiki_reg = get_user_meta(intval($user_id), 'vendor_tiki_reg', true);
		$vendor_tiki_eco = get_user_meta(intval($user_id), 'vendor_tiki_eco', true);
		$vendor_tiki_ons = get_user_meta(intval($user_id), 'vendor_tiki_ons', true);
		$vendor_pos_kilat_khusus = get_user_meta(intval($user_id), 'vendor_pos_kilat_khusus', true);
		$vendor_pos_express_next_day = get_user_meta(intval($user_id), 'vendor_pos_express_next_day', true);
		$vendor_pos_valuable_goods = get_user_meta(intval($user_id), 'vendor_pos_valuable_goods', true);
		$vendor_jnt_ez = get_user_meta(intval($user_id), 'vendor_jnt_ez', true);
		$vendor_sicepat_reg = get_user_meta(intval($user_id), 'vendor_sicepat_reg', true);
		$vendor_sicepat_best = get_user_meta(intval($user_id), 'vendor_sicepat_best', true);
		$vendor_wahana = get_user_meta(intval($user_id), 'vendor_wahana', true);
		
		?>
		<div class="page_collapsible" id="wcfm_settings_form_epeken_head">
			<label class="wcfmfa fa-truck"></label>
			<?php _e('Shipping', 'wc-frontend-manager'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_epeken_expander" class="wcfm-content">
				
				<h2><?php _e('Shipment Origin Information', 'wc-frontend-manager'); ?></h2>
				<table class="form-table">
				 <tr>
					 <th>
						Kota Asal Pengiriman
					 </th>
					 <td>
						<select name="vendor_data_asal_kota" id="vendor_data_asal_kota" style="width: 50%">
							<?php
							$license = get_option('epeken_wcjne_license_key');     
							$origins = epeken_get_valid_origin($license);
							$origins = json_decode($origins,true);
							$origins = $origins["validorigin"];
							?>		
							<option value="0">None</option>
							<?php
							foreach($origins as $origin) {
								$idx=$origin['origin_code'];
								?>
								<option value=<?php echo '"'.$idx.'"'; if($vendor_data_asal_kota === $idx){echo ' selected';}?>><?php echo $origin["kota_kabupaten"]; ?></option>
						  <?php
							}
						  ?>
						</select>
						<script type='text/javascript'>
							jQuery(document).ready(function($){
									$('#vendor_data_asal_kota').select2();
							});
						</script>
					 </td>
				 </tr>
				 <tr>
					 <th>
					 Expedisi/Kurir Yang Diaktifkan
					 </th>
					 <td>
						<table>
							<tr>
								<td style="width: 33%">
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_jne_reg" <?php if($vendor_jne_reg === 'on') echo " checked"; ?>> JNE REG</input><br>
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_jne_oke" <?php if($vendor_jne_oke === 'on') echo " checked"; ?>> JNE OKE</input><br>
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_jne_yes" <?php if($vendor_jne_yes === 'on') echo " checked"; ?>> JNE YES</input><br>
								</td>
								<td style="width: 33%">
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_tiki_reg" <?php if($vendor_tiki_reg === 'on') echo " checked"; ?>> TIKI REG</input><br>
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_tiki_eco" <?php if($vendor_tiki_eco === 'on') echo " checked"; ?>> TIKI ECO</input><br>
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_tiki_ons" <?php if($vendor_tiki_ons === 'on') echo " checked"; ?>> TIKI ONS</input><br>
								</td>
								<td style="width: 33%">
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_pos_kilat_khusus" <?php if($vendor_pos_kilat_khusus === 'on') echo " checked"; ?>> POS KILAT KHUSUS</input><br>
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_pos_express_next_day" <?php if($vendor_pos_express_next_day === 'on') echo " checked"; ?>> POS Express Next Day</input><br>
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_pos_valuable_goods" <?php if($vendor_pos_valuable_goods === 'on') echo " checked"; ?>> POS Valuable Goods</input><br>
								</td>
								</tr>
								<tr>
								<td style="width: 33%">
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_jnt_ez" <?php if($vendor_jnt_ez === 'on') echo " checked"; ?>> J&T EZ</input><br>
								</td>
								<td style="width: 33%">
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_sicepat_reg" <?php if($vendor_sicepat_reg === 'on') echo " checked"; ?>> SICEPAT REG</input><br>
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_sicepat_best" <?php if($vendor_sicepat_best === 'on') echo " checked"; ?>> SICEPAT BEST</input><br>
								</td>
								<td style="width: 33%">
								 <input type="checkbox" class="wcfm-checkbox" style="margin-right: 5%;" name="vendor_wahana" <?php if($vendor_wahana === 'on') echo " checked"; ?>> Wahana </input><br>
								</td>
							</tr>
						</table>
					 </td>
				 </tr>
				</table>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Vendor Settings Epeken Plugins Data Update
	 */
	function wcfm_wcepeken_vendor_settings_update( $user_id, $wcfm_settings_form ) {
		
		$vendor_data_asal_kota = (empty($wcfm_settings_form['vendor_data_asal_kota'] )) ? '' : $wcfm_settings_form['vendor_data_asal_kota']; // code kota asal
		$vendor_jne_reg = (empty($wcfm_settings_form['vendor_jne_reg'])) ? '' : $wcfm_settings_form['vendor_jne_reg'];
		$vendor_jne_oke = (empty($wcfm_settings_form['vendor_jne_oke'])) ? '' : $wcfm_settings_form['vendor_jne_oke'];
		$vendor_jne_yes = (empty($wcfm_settings_form['vendor_jne_yes'])) ? '' : $wcfm_settings_form['vendor_jne_yes'];
		$vendor_tiki_reg = (empty($wcfm_settings_form['vendor_tiki_reg'])) ? '' : $wcfm_settings_form['vendor_tiki_reg'];
		$vendor_tiki_eco = (empty($wcfm_settings_form['vendor_tiki_eco'])) ? '' : $wcfm_settings_form['vendor_tiki_eco'];
		$vendor_tiki_ons = (empty($wcfm_settings_form['vendor_tiki_ons'])) ? '' : $wcfm_settings_form['vendor_tiki_ons'];
		$vendor_pos_kilat_khusus = (empty($wcfm_settings_form['vendor_pos_kilat_khusus'])) ? '' : $wcfm_settings_form['vendor_pos_kilat_khusus'];
		$vendor_pos_express_next_day = (empty($wcfm_settings_form['vendor_pos_express_next_day'])) ? '' : $wcfm_settings_form['vendor_pos_express_next_day'];
		$vendor_pos_valuable_goods = (empty($wcfm_settings_form['vendor_pos_valuable_goods'])) ? '' : $wcfm_settings_form['vendor_pos_valuable_goods'];
		$vendor_jnt_ez = (empty($wcfm_settings_form['vendor_jnt_ez'])) ? '' : $wcfm_settings_form['vendor_jnt_ez'];
		$vendor_sicepat_reg = (empty($wcfm_settings_form['vendor_sicepat_reg'])) ? '' : $wcfm_settings_form['vendor_sicepat_reg'];
		$vendor_sicepat_best = (empty($wcfm_settings_form['vendor_sicepat_best'])) ? '' : $wcfm_settings_form['vendor_sicepat_best'];
		$vendor_wahana = (empty($wcfm_settings_form['vendor_wahana'])) ? '' : $wcfm_settings_form['vendor_wahana'];
		
		update_user_meta( $user_id, 'vendor_data_kota_asal', $vendor_data_asal_kota);
		update_user_meta( $user_id, 'vendor_jne_reg', $vendor_jne_reg);
		update_user_meta( $user_id, 'vendor_jne_oke', $vendor_jne_oke);
		update_user_meta( $user_id, 'vendor_jne_yes', $vendor_jne_yes);
		update_user_meta( $user_id, 'vendor_tiki_reg', $vendor_tiki_reg);
		update_user_meta( $user_id, 'vendor_tiki_eco', $vendor_tiki_eco);
		update_user_meta( $user_id, 'vendor_tiki_ons', $vendor_tiki_ons);
		update_user_meta( $user_id, 'vendor_pos_kilat_khusus', $vendor_pos_kilat_khusus);
		update_user_meta( $user_id, 'vendor_pos_express_next_day', $vendor_pos_express_next_day);
		update_user_meta( $user_id, 'vendor_pos_valuable_goods', $vendor_pos_valuable_goods);
		update_user_meta( $user_id, 'vendor_jnt_ez', $vendor_jnt_ez);
		update_user_meta( $user_id, 'vendor_sicepat_reg', $vendor_sicepat_reg);
		update_user_meta( $user_id, 'vendor_sicepat_best', $vendor_sicepat_best);
		update_user_meta( $user_id, 'vendor_wahana', $vendor_wahana);
	}
		
	/**
	 * Woo Advanced Product Size Chart Plugin Views
	 */
	function wcfm_woo_product_size_chart_product_manage_views( $product_id ) {
		global $WCFM, $WCFMu, $wp;
		
		$chart_id = '';
		
		if( $product_id ) {
			$chart_id = size_chart_get_product_chart_id( $product_id );
		}
		
		$chart_list = array( '' => __( 'No Chart', 'wc-frontend-manager' ) );
		$size_chart_post_type_name = esc_attr__( 'size-chart', 'size-chart-for-woocommerce' );
		$size_chart_search_args = array(
				'post_type'              => $size_chart_post_type_name,
				'post_status'            => 'publish',
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
		);
		$size_chart_search_wp_query = new WP_Query( $size_chart_search_args );
		$found_chart = array();
		if ( $size_chart_search_wp_query->have_posts() ) {
			foreach ( $size_chart_search_wp_query->posts as $size_chart_search_chart ) {
				$chart_list[$size_chart_search_chart->ID] = sprintf( esc_html__( '%1$s (#%2$s)', 'size-chart-for-woocommerce' ), $size_chart_search_chart->post_title, $size_chart_search_chart->ID );
			}
		}
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_woo_product_size_chart_fields', array(
																																													"wcfm_prod_chart" => array('label' => __( 'Search/Select Size Chart', 'size-chart-for-woocommerce' ), 'type' => 'select', 'options' => $chart_list, 'class' => 'wcfm-select wcfm_ele wcfm_full_ele catalog_visibility_ele simple variable external grouped booking ', 'label_class' => 'wcfm_title wcfm_full_ele catalog_visibility_ele', 'value' => $chart_id ),
																																											)) );
	}
	
	/**
	 * Post Expirator Plugin Views
	 */
	function wcfm_woo_product_post_expirator_product_manage_views( $product_id ) {
		global $WCFM, $WCFMu, $wp;
		
		$expirationdatets = '';
		$firstsave = '';
		$default = '';
		$expireType = '';
		
		if( $product_id ) {
			$expirationdatets = get_post_meta( $product_id, '_expiration-date', true );
			$firstsave = get_post_meta( $product_id, '_expiration-date-status', true );
		}
		
		$defaults = get_option('expirationdateDefaultsProduct');
		if (empty($expirationdatets)) {
			$default = get_option('expirationdateDefaultDate',POSTEXPIRATOR_EXPIREDEFAULT);
			if ($default == 'null') {
				$defaultmonth 	=	date_i18n('m');
				$defaultday 	=	date_i18n('d');
				$defaulthour 	=	date_i18n('H');
				$defaultyear 	=	date_i18n('Y');
				$defaultminute 	= 	date_i18n('i');
	
			} elseif ($default == 'custom') {
				$custom = get_option('expirationdateDefaultDateCustom');
				if ($custom === false) $ts = time();
				else {
					$tz = get_option('timezone_string');
					if ( $tz ) date_default_timezone_set( $tz );
					$ts = time() + (strtotime($custom) - time());
					if ( $tz ) date_default_timezone_set('UTC');
				}
				$defaultmonth 	=	get_date_from_gmt(gmdate('Y-m-d H:i:s',$ts),'m');
				$defaultday 	=	get_date_from_gmt(gmdate('Y-m-d H:i:s',$ts),'d');
				$defaultyear 	=	get_date_from_gmt(gmdate('Y-m-d H:i:s',$ts),'Y');;
				$defaulthour 	=	get_date_from_gmt(gmdate('Y-m-d H:i:s',$ts),'H');
				$defaultminute 	=	get_date_from_gmt(gmdate('Y-m-d H:i:s',$ts),'i');
			}
	
			$enabled = '';
			$disabled = ' disabled="disabled"';
			$categories = get_option('expirationdateCategoryDefaults');
	
			if (isset($defaults['expireType'])) {
				$expireType = $defaults['expireType'];
			}
	
			if (isset($defaults['autoEnable']) && ($firstsave !== 'saved') && ($defaults['autoEnable'] === true || $defaults['autoEnable'] == 1)) { 
				$enabled = ' checked="checked"'; 
				$disabled='';
			} 
		} else {
			$defaultmonth 	=	get_date_from_gmt(gmdate('Y-m-d H:i:s',$expirationdatets),'m');
			$defaultday 	  =	get_date_from_gmt(gmdate('Y-m-d H:i:s',$expirationdatets),'d');
			$defaultyear 	  =	get_date_from_gmt(gmdate('Y-m-d H:i:s',$expirationdatets),'Y');
			$defaulthour 	  =	get_date_from_gmt(gmdate('Y-m-d H:i:s',$expirationdatets),'H');
			$defaultminute 	=	get_date_from_gmt(gmdate('Y-m-d H:i:s',$expirationdatets),'i');
			$enabled 	      = 	' checked="checked"';
			$disabled 	    = 	'';
			$opts           = array();
			if( $product_id ) {
				$opts 		= 	get_post_meta( $product_id, '_expiration-date-options', true );
			}
			if (isset($opts['expireType'])) {
				$expireType = $opts['expireType'];
			}
			$categories = isset($opts['category']) ? $opts['category'] : false;
		}
	
		$rv = array();
		$rv[] = '<p class="wcfm_title wcfm_full_ele catalog_visibility_ele"><strong>' . __( 'Post Expirator', 'post-expirator' ) . ':</strong></p>';
		$rv[] = '<p><input type="checkbox" name="enable-expirationdate" id="enable-expirationdate" class="wcfm-checkbox" style="margin-right:5px!important" value="checked"'.$enabled.' onclick="expirationdate_ajax_add_meta(\'enable-expirationdate\')" />';
		$rv[] = '<label class="wcfm-title" for="enable-expirationdate">'.__('Enable Post Expiration','post-expirator').'</label></p>';
	
		if ($default == 'publish') {
			$rv[] = '<em>'.__('The published date/time will be used as the expiration value','post-expirator').'</em><br/>';
		} else {
			$rv[] = '<table style="max-width:214px;margin:auto;margin-bottom:15px;"><tr>';
			$rv[] = '<th style="text-align: left;">'.__('Year','post-expirator').'</th>';
			$rv[] = '<th style="text-align: left;">'.__('Month','post-expirator').'</th>';
			$rv[] = '<th style="text-align: left;">'.__('Day','post-expirator').'</th>';
			$rv[] = '</tr><tr>';
			$rv[] = '<td>';
			$rv[] = '<select name="expirationdate_year" id="expirationdate_year"'.$disabled.'>';
			$currentyear = date('Y');
	
			if ($defaultyear < $currentyear) $currentyear = $defaultyear;
	
			for($i = $currentyear; $i < $currentyear + 8; $i++) {
				if ($i == $defaultyear)
					$selected = ' selected="selected"';
				else
					$selected = '';
				$rv[] = '<option'.$selected.'>'.($i).'</option>';
			}
			$rv[] = '</select>';
			$rv[] = '</td><td>';
			$rv[] = '<select name="expirationdate_month" id="expirationdate_month"'.$disabled.'>';
	
			for($i = 1; $i <= 12; $i++) {
				if ($defaultmonth == date_i18n('m',mktime(0, 0, 0, $i, 1, date_i18n('Y'))))
					$selected = ' selected="selected"';
				else
					$selected = '';
				$rv[] = '<option value="'.date_i18n('m',mktime(0, 0, 0, $i, 1, date_i18n('Y'))).'"'.$selected.'>'.date_i18n('F',mktime(0, 0, 0, $i, 1, date_i18n('Y'))).'</option>';
			}
	
			$rv[] = '</select>';
			$rv[] = '</td><td>';
			$rv[] = '<input type="number" style="width:50px!important" id="expirationdate_day" name="expirationdate_day" value="'.$defaultday.'" size="2"'.$disabled.' />,';
			$rv[] = '</td></tr><tr>';
			$rv[] = '<th style="text-align: left;"></th>';
			$rv[] = '<th style="text-align: left;">'.__('Hour','post-expirator').'('.date_i18n('T',mktime(0, 0, 0, $i, 1, date_i18n('Y'))).')</th>';
			$rv[] = '<th style="text-align: left;">'.__('Minute','post-expirator').'</th>';
			$rv[] = '</tr><tr>';
			$rv[] = '<td>@</td><td>';
			$rv[] = '<select name="expirationdate_hour" style="width:50px!important" id="expirationdate_hour"'.$disabled.'>';
	
			for($i = 1; $i <= 24; $i++) {
				if ($defaulthour == date_i18n('H',mktime($i, 0, 0, date_i18n('n'), date_i18n('j'), date_i18n('Y'))))
					$selected = ' selected="selected"';
				else
					$selected = '';
				$rv[] = '<option value="'.date_i18n('H',mktime($i, 0, 0, date_i18n('n'), date_i18n('j'), date_i18n('Y'))).'"'.$selected.'>'.date_i18n('H',mktime($i, 0, 0, date_i18n('n'), date_i18n('j'), date_i18n('Y'))).'</option>';
			}
	
			$rv[] = '</select></td><td>';
			$rv[] = '<input type="number" style="width:50px!important" id="expirationdate_minute" name="expirationdate_minute" value="'.$defaultminute.'" size="2"'.$disabled.' />';
			$rv[] = '</td></tr></table>';
		}
		$rv[] = '<input type="hidden" name="expirationdate_formcheck" value="true" />';
		echo implode("\n",$rv);
	
		echo '<br/>'.__('How to expire','post-expirator').': ';
		echo _postExpiratorExpireType(array('type' => 'page', 'name'=>'expirationdate_expiretype','selected'=>$expireType,'disabled'=>$disabled,'onchange' => 'expirationdate_toggle_category(this)'));
		echo '<br/>';
		
		/*if (isset($expireType) && ($expireType == 'category' || $expireType == 'category-add' || $expireType == 'category-remove')) {
			$catdisplay = 'block';
		} else {
			$catdisplay = 'none';
		}
		echo '<div id="expired-category-selection" style="display: '.$catdisplay.'">';
		echo '<br/>'.__('Expiration Categories','post-expirator').':<br/>';

		echo '<div class="wp-tab-panel" id="post-expirator-cat-list">';
		echo '<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">';
		$walker = new Walker_PostExpirator_Category_Checklist();
		if (!empty($disabled)) $walker->setDisabled();
		$taxonomies = get_object_taxonomies( 'product','object');
	        $taxonomies = wp_filter_object_list($taxonomies, array('hierarchical' => true));
		if (sizeof($taxonomies) == 0) {
			echo '<p>'.__('You must assign a heirarchical taxonomy to this post type to use this feature.','post-expirator').'</p>';
		} elseif (sizeof($taxonomies) > 1 && !isset($defaults['taxonomy'])) {
			echo '<p>'.__('More than 1 heirachical taxonomy detected.  You must assign a default taxonomy on the settings screen.','post-expirator').'</p>';
		} else {
			$keys = array_keys($taxonomies);
			$taxonomy = isset($defaults['taxonomy']) ? $defaults['taxonomy'] : $keys[0];
			wp_terms_checklist(0, array( 'taxonomy' => $taxonomy, 'walker' => $walker, 'selected_cats' => $categories, 'checked_ontop' => false ) );
			echo '<input type="hidden" name="taxonomy-heirarchical" value="'.$taxonomy.'" />';
		}
		echo '</ul>';
		echo '</div>';
		if (isset($taxonomy))
		echo '<p class="post-expirator-taxonomy-name">'.__('Taxonomy Name','post-expirator').': '.$taxonomy.'</p>';
		echo '</div>';*/
	
		echo '<div id="expirationdate_ajax_result"></div>';
		
		?>
		<script type="text/javascript">
		//<![CDATA[
		function expirationdate_ajax_add_meta(expireenable) {
			var expire = document.getElementById(expireenable);
		
			if (expire.checked == true) {
				var enable = 'true';
				if (document.getElementById('expirationdate_month')) {
					document.getElementById('expirationdate_month').disabled = false;
					document.getElementById('expirationdate_day').disabled = false;
					document.getElementById('expirationdate_year').disabled = false;
					document.getElementById('expirationdate_hour').disabled = false;
					document.getElementById('expirationdate_minute').disabled = false;
				}
				document.getElementById('expirationdate_expiretype').disabled = false;
				var cats = document.getElementsByName('expirationdate_category[]');
				var max = cats.length;
				for (var i=0; i<max; i++) {
					cats[i].disabled = '';
				}
			} else {
				if (document.getElementById('expirationdate_month')) {
					document.getElementById('expirationdate_month').disabled = true;
					document.getElementById('expirationdate_day').disabled = true;
					document.getElementById('expirationdate_year').disabled = true;
					document.getElementById('expirationdate_hour').disabled = true;
					document.getElementById('expirationdate_minute').disabled = true;
				}
				document.getElementById('expirationdate_expiretype').disabled = true;
				var cats = document.getElementsByName('expirationdate_category[]');
				var max = cats.length;
				for (var i=0; i<max; i++) {
					cats[i].disabled = 'disable';
				}
				var enable = 'false';
			}
			return true;
		}
		function expirationdate_toggle_category(id) {
			if (id.options[id.selectedIndex].value == 'category') {
				jQuery('#expired-category-selection').show();
			} else if (id.options[id.selectedIndex].value == 'category-add') {
				jQuery('#expired-category-selection').show(); //TEMP
			} else if (id.options[id.selectedIndex].value == 'category-remove') {
				jQuery('#expired-category-selection').show(); //TEMP
			} else {
				jQuery('#expired-category-selection').hide();
			}
		}
		function expirationdate_toggle_defaultdate(id) {
			if (id.options[id.selectedIndex].value == 'custom') {
				jQuery('#expired-custom-container').show();
			} else {
				jQuery('#expired-custom-container').hide();
			}
		
		}
		//]]>
		</script>
		<?php
	}
	
	/**
   * Product Manage Third Party Plugins views
   */
  function wcfm_integrations_products_manage_views( ) {
		global $WCFM;
	  $WCFM->template->get_template( 'integrations/wcfm-view-integrations-products-manage.php' );
	}
	
	/**
	 * Listing Approve
	 */
	function wcfm_listing_approve() {
		if ( !empty( $_GET['listing_id'] ) && !wcfm_is_vendor() ) {
			$listing_id  = absint( $_GET['listing_id'] );
			$job_data = [
				'ID'          => $listing_id,
				'post_status' => 'publish',
			];
			wp_update_post( $job_data );
			
			wp_safe_redirect( get_wcfm_listings_url() );
			die;
		}
	}
}