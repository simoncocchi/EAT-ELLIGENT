<?php
/**
 * WCFM Marketplace plugin core
 *
 * Plugin shortcode
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core 
 * @version   1.0.0
 */
 
class WCFMmp_Shortcode {

	public $list_product;

	public function __construct() {
		
		// WCFM Markeptlace Store List
		add_shortcode('wcfm_stores', array(&$this, 'wcfmmp_stores_shortcode'));
		
		// WCFM Markeptlace Store List Map
		add_shortcode('wcfm_stores_map', array(&$this, 'wcfmmp_stores_map_shortcode'));
		
		// WCFM Marketplace Store Carousel
		add_shortcode('wcfm_stores_carousel', array(&$this, 'wcfmmp_stores_carousel_shortcode'));
		
		// WCFM Markeplace Store Sold By
		add_shortcode('wcfm_store_sold_by', array(&$this, 'wcfmmp_store_sold_by_shortcode'));
		
		// WCFM Markeplace Store Info
		add_shortcode('wcfm_store_info', array(&$this, 'wcfmmp_store_info_shortcode'));
		
		// WCFM Markeplace Store Hours
		add_shortcode('wcfm_store_hours', array(&$this, 'wcfmmp_store_hours_shortcode'));
		
		// WCFM Markeplace Shipping Time
		add_shortcode('wcfm_shipping_time', array(&$this, 'wcfmmp_shipping_time_shortcode'));
		
		// WCFM Markeplace More Offers
		add_shortcode('wcfm_more_offers', array(&$this, 'wcfmmp_more_offers_shortcode'));
		
		// WCFM Marketplace Store FB Widget
		add_shortcode('wcfm_store_fb_feed', array(&$this, 'wcfmmp_store_fb_widget_shortcode'));
		
		// WCFM Marketplace Store Twitter Widget
		add_shortcode('wcfm_store_twitter_feed', array(&$this, 'wcfmmp_store_twitter_widget_shortcode'));
		
		// WCFM Marketplace Store Instagram Widget
		//add_shortcode('wcfm_store_instagram_feed', array(&$this, 'wcfmmp_store_instagram_widget_shortcode'));
		
		// Product List Vendor Filter
		add_shortcode('wcfm_products_store_filter', array(&$this, 'wcfmmp_products_store_filter_shortcode'));
	}

	/**
	 * WCFM Stores Short Code
	 */
	public function wcfmmp_stores_shortcode( $atts ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $includes;
		$WCFM->nocache();
		
		$defaults = array(
				'per_page'           => 12,
				'sidebar'            => 'yes',
				'orderby'            => 'newness_asc',
				'filter'             => 'yes',
				'search'             => 'yes',
				'category'           => 'yes',
				'country'            => 'yes',
				'state'              => 'yes',
				'radius'             => 'yes',
				'map'                => 'yes',
				'map_zoom'           => 5,
				'auto_zoom'          => 'yes',
				'per_row'            => 3,
				'includes'           => '',
				'excludes'           => '',
				'include_membership' => '',
				'exclude_membership' => '',
				'search_term'        => '',
				'search_country'     => '',
				'search_state'       => '',
				'search_city'        => '',
				'search_zip'         => '',
				'search_category'    => '',
				'store_category'     => '',
				'theme'              => 'classic',
				'has_orderby'        => 'yes',
				'has_product'        => 'no',
				'has_sidebar'        => 'yes',
				'has_filter'         => 'yes',
				'has_search'         => 'yes',
				'has_category'       => 'yes',
				'has_country'        => 'yes',
				'has_state'          => 'yes',
				'has_city'           => 'no',
				'has_zip'            => 'no',
				'has_radius'         => 'yes',
				'has_map'            => 'yes',
		);

		$attr   = shortcode_atts( apply_filters( 'wcfmmp_stores_default_args', $defaults ), $atts );
		$paged  = max( 1, get_query_var( 'paged' ) );
		$length = apply_filters( 'wcfmmp_stores_per_page', $attr['per_page'] );
		$offset = ( $paged - 1 ) * absint( $length );
		
		$search_country = isset( $attr['search_country'] ) ? $attr['search_country'] : '';
		$search_state   = isset( $attr['search_state'] ) ? $attr['search_state'] : '';
		
		// GEO Locate Support
		if( apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ) ) {
			if( is_user_logged_in() && !$search_country ) {
				$user_location = get_user_meta( get_current_user_id(), 'wcfm_user_location', true );
				if( $user_location ) {
					$search_country = $user_location['country'];
					$search_state   = $user_location['state'];
				}
			}
					
			if( apply_filters( 'wcfm_is_allow_wc_geolocate', true ) && class_exists( 'WC_Geolocation' ) && !$search_country ) {
				$user_location = WC_Geolocation::geolocate_ip();
				$search_country = $user_location['country'];
				$search_state   = $user_location['state'];
			}
		}

		$orderby          = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : apply_filters( 'wcfmmp_stores_default_orderby', $attr['orderby'] );
		$search_term      = isset( $_GET['wcfmmp_store_search'] ) ? sanitize_text_field( $_GET['wcfmmp_store_search'] ) : $attr['search_term'];
		
		$search_country   = isset( $_GET['wcfmmp_store_country'] ) ? sanitize_text_field( $_GET['wcfmmp_store_country'] ) : '';
		$search_state     = isset( $_GET['wcfmmp_store_state'] ) ? sanitize_text_field( $_GET['wcfmmp_store_state'] ) : '';
		$search_city      = isset( $_GET['wcfmmp_store_city'] ) ? sanitize_text_field( $_GET['wcfmmp_store_city'] ) : $attr['search_city'];
		$search_zip       = isset( $_GET['wcfmmp_store_zip'] ) ? sanitize_text_field( $_GET['wcfmmp_store_zip'] ) : $attr['search_zip'];
		
		$search_category  = isset( $_GET['wcfmmp_store_category'] ) ? sanitize_text_field( $_GET['wcfmmp_store_category'] ) : $attr['search_category'];
		$store_category   = isset( $_GET['wcfmsc_store_categories'] ) ? sanitize_text_field( $_GET['wcfmsc_store_categories'] ) : $attr['store_category'];
		
		$search_category  = apply_filters( 'wcfmmp_stores_default_search_category', $search_category );
		$has_product      = apply_filters( 'wcfmmp_stores_list_has_product', wc_string_to_bool( $attr['has_product'] ) );
		
		$has_sidebar      = isset( $attr['sidebar'] ) ? $attr['sidebar'] : 'yes';
		$has_sidebar      = isset( $attr['has_sidebar'] ) ? $attr['has_sidebar'] : $has_sidebar;
		
		$has_filter       = isset( $attr['filter'] ) ? $attr['filter'] : 'yes';
		$has_filter       = isset( $attr['has_filter'] ) ? $attr['has_filter'] : $has_filter;
		
		$has_search       = isset( $attr['search'] ) ? $attr['search'] : 'yes';
		$has_search       = isset( $attr['has_search'] ) ? $attr['has_search'] : $has_search;
		
		$has_category     = isset( $attr['category'] ) ? $attr['category'] : 'yes';
		$has_category     = isset( $attr['has_category'] ) ? $attr['has_category'] : $has_category;
		
		$has_country      = isset( $attr['country'] ) ? $attr['country'] : 'yes';
		$has_country      = isset( $attr['has_country'] ) ? $attr['has_country'] : $has_country;
		
		$has_state        = isset( $attr['state'] ) ? $attr['state'] : 'yes';
		$has_state        = isset( $attr['has_state'] ) ? $attr['has_state'] : $has_state;
		
		$has_city         = isset( $attr['has_city'] ) ? $attr['has_city'] : 'no';
		
		$has_zip          = isset( $attr['has_zip'] ) ? $attr['has_zip'] : 'no';
		
		$has_radius       = isset( $attr['radius'] ) ? $attr['radius'] : 'yes';
		$has_radius       = isset( $attr['has_radius'] ) ? $attr['has_radius'] : $has_radius;
		
		$has_map          = isset( $attr['map'] ) ? $attr['map'] : 'yes';
		$has_map          = isset( $attr['has_map'] ) ? $attr['has_map'] : $has_map;
		
		$search_data     = array();
		if( $store_category ) {
			$search_data['wcfmsc_store_categories'] = $store_category;
		}
		if( $search_country ) {
			$search_data['wcfmmp_store_country'] = $search_country;
		}
		if( $search_state ) {
			$search_data['wcfmmp_store_state'] = $search_state;
		}
		if( $search_city ) {
			$search_data['wcfmmp_store_city'] = $search_city;
		}
		if( $search_zip ) {
			$search_data['wcfmmp_store_zip'] = $search_zip;
		}
		if( isset( $_POST['search_data'] ) ) {
			parse_str($_POST['search_data'], $search_data);
		} elseif( isset( $_GET['orderby'] ) ) {
			$search_data = wp_unslash($_GET);
		} else {
			$search_data['orderby'] = $orderby;
		}
		
		// Exclude Membership
		$exclude_members = array();
		$exclude_membership = isset( $attr['exclude_membership'] ) ? sanitize_text_field( $attr['exclude_membership'] ) : '';
		if( $exclude_membership ) $exclude_membership = explode(",", $exclude_membership);
		if( !empty( $exclude_membership ) && is_array( $exclude_membership ) ) {
			foreach( $exclude_membership as $wcfm_membership ) {
				$membership_users = (array) get_post_meta( $wcfm_membership, 'membership_users', true );
				$exclude_members  = array_merge( $exclude_members, $membership_users );
			}
		}
		if( $exclude_members ) $exclude_members = implode(",", $exclude_members);
		else $exclude_members = '';
		
		// Excluded Stores from List
		$excludes = !empty( $attr['excludes'] ) ? sanitize_text_field( $attr['excludes'] ) : $exclude_members;
		$search_data['excludes'] = $excludes;
		
		// Include Membership
		$include_members = array();
		$include_membership = isset( $attr['include_membership'] ) ? sanitize_text_field( $attr['include_membership'] ) : '';
		if( $include_membership ) $include_membership = explode(",", $include_membership);
		if( !empty( $include_membership ) && is_array( $include_membership ) ) {
			foreach( $include_membership as $wcfm_membership ) {
				$membership_users = (array) get_post_meta( $wcfm_membership, 'membership_users', true );
				$include_members  = array_merge( $include_members, $membership_users );
			}
		}
		if( $include_members ) $include_members = implode(",", $include_members);
		else $include_members = '';
		
		// Include Store List
		$includes = !empty( $attr['includes'] ) ? sanitize_text_field( $attr['includes'] ) : $include_members;
		if( $includes ) $includes = explode(",", $includes);
		else $includes = array();
		
		// Radious Search
		$enable_wcfm_storelist_radius  = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_storelist_radius'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_storelist_radius'] : 'no';
		$has_radius = wc_string_to_bool( $has_radius);
		if( ( $enable_wcfm_storelist_radius == 'yes' ) && $has_radius ) $has_radius = true;
		else $has_radius = false;
		$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
		if ( ($wcfm_map_lib == 'google') && empty( $api_key ) ) $has_radius = false;
		if( $has_radius ) {
			$has_country = 'no';
			$has_state   = 'no';
			$has_city    = 'no';
			$has_zipcode = 'no';
		}
		
		$search_data = apply_filters( 'wcfmmp_stores_search_data', $search_data );
		
		$stores = $WCFMmp->wcfmmp_vendor->wcfmmp_search_vendor_list( true, $offset, $length, $search_term, $search_category, $search_data, $has_product, $includes );

		$template_args = apply_filters( 'wcfmmp_stores_args', array(
				'stores'          => $stores,
				'limit'           => $length,
				'offset'          => $offset,
				'includes'        => $includes,
				'excludes'        => $excludes,
				'paged'           => $paged,
				'image_size'      => 'full',
				'orderby'         => $orderby,
				'search_term'     => $search_term,
				'search_country'  => $search_country,
				'search_state'    => $search_state,
				'search_city'     => $search_city,
				'search_zip'      => $search_zip,
				'search_category' => $search_category,
				'store_category'  => $store_category,
				'search_data'     => $search_data,
				'has_product'     => $has_product,
				'has_orderby'     => wc_string_to_bool( $attr['has_orderby'] ),
				'sidebar'         => wc_string_to_bool( $has_sidebar ),
				'filter'          => wc_string_to_bool( $has_filter ),
				'search'          => wc_string_to_bool( $has_search ),
				'category'        => wc_string_to_bool( $has_category ),
				'country'         => wc_string_to_bool( $has_country ),
				'state'           => wc_string_to_bool( $has_state ),
				'has_city'        => wc_string_to_bool( $has_city ),
				'has_zip'         => wc_string_to_bool( $has_zip ),
				'map'             => wc_string_to_bool( $has_map ), 
				'radius'          => $has_radius,
				'map_zoom'        => apply_filters( 'wcfmmp_map_default_zoom_level', $attr['map_zoom'] ),
				'auto_zoom'       => wc_string_to_bool( apply_filters( 'wcfmmp_is_allow_map_auto_zoom', $attr['auto_zoom'] ) ),
				'per_row'         => $attr['per_row'],
				'theme'           => $attr['theme']
		), $attr, $search_data );
		
		ob_start();
		$WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists.php', $template_args );
		return ob_get_clean();
	}
	
		/**
	 * WCFM Stores Map Short Code
	 */
	public function wcfmmp_stores_map_shortcode( $atts ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query;
		$WCFM->nocache();
		
		$defaults = array(
				'filter'             => 'yes',
				'search'             => 'yes',
				'category'           => 'yes',
				'country'            => 'yes',
				'state'              => 'yes',
				'radius'             => 'yes',
				'map'                => 'yes',
				'map_zoom'           => 5,
				'auto_zoom'          => 'yes',
				'limit'              => 10,
				'per_row'            => 3,
				'includes'           => '',
				'excludes'           => '',
				'include_membership' => '',
				'exclude_membership' => '',
				'search_term'        => '',
				'search_city'        => '',
				'search_zip'         => '',
				'search_category'    => '',
				'store_category'     => '',
				'theme'              => 'classic',
				'has_product'        => 'no',
				'has_sidebar'        => 'yes',
				'has_filter'         => 'yes',
				'has_search'         => 'yes',
				'has_category'       => 'yes',
				'has_country'        => 'yes',
				'has_state'          => 'yes',
				'has_city'           => 'no',
				'has_zip'            => 'no',
				'has_radius'         => 'yes',
				'has_map'            => 'yes',
		);
		
		$attr   = shortcode_atts( apply_filters( 'wcfmmp_stores_map_default_args', $defaults ), $atts );
		
		$search_term      = isset( $_GET['wcfmmp_store_search'] ) ? sanitize_text_field( $_GET['wcfmmp_store_search'] ) : $attr['search_term'];
		
		$search_country   = isset( $_GET['wcfmmp_store_country'] ) ? sanitize_text_field( $_GET['wcfmmp_store_country'] ) : '';
		$search_state     = isset( $_GET['wcfmmp_store_state'] ) ? sanitize_text_field( $_GET['wcfmmp_store_state'] ) : '';
		$search_city      = isset( $_GET['wcfmmp_store_city'] ) ? sanitize_text_field( $_GET['wcfmmp_store_city'] ) : $attr['search_city'];
		$search_zip       = isset( $_GET['wcfmmp_store_zip'] ) ? sanitize_text_field( $_GET['wcfmmp_store_zip'] ) : $attr['search_zip'];
		
		$search_category  = isset( $_GET['wcfmmp_store_category'] ) ? sanitize_text_field( $_GET['wcfmmp_store_category'] ) : $attr['search_category'];
		$store_category   = isset( $_GET['wcfmsc_store_categories'] ) ? sanitize_text_field( $_GET['wcfmsc_store_categories'] ) : $attr['store_category'];
		
		$has_product      = apply_filters( 'wcfmmp_stores_list_has_product', wc_string_to_bool( $attr['has_product'] ) );

		$has_filter       = isset( $attr['filter'] ) ? $attr['filter'] : 'yes';
		$has_filter       = isset( $attr['has_filter'] ) ? $attr['has_filter'] : $has_filter;
		
		$has_search       = isset( $attr['search'] ) ? $attr['search'] : 'yes';
		$has_search       = isset( $attr['has_search'] ) ? $attr['has_search'] : $has_search;
		
		$has_category     = isset( $attr['category'] ) ? $attr['category'] : 'yes';
		$has_category     = isset( $attr['has_category'] ) ? $attr['has_category'] : $has_category;
		
		$has_country      = isset( $attr['country'] ) ? $attr['country'] : 'yes';
		$has_country      = isset( $attr['has_country'] ) ? $attr['has_country'] : $has_country;
		
		$has_state        = isset( $attr['state'] ) ? $attr['state'] : 'yes';
		$has_state        = isset( $attr['has_state'] ) ? $attr['has_state'] : $has_state;
		
		$has_city         = isset( $attr['has_city'] ) ? $attr['has_city'] : 'no';
		
		$has_zip          = isset( $attr['has_zip'] ) ? $attr['has_zip'] : 'no';
		
		$has_radius       = isset( $attr['radius'] ) ? $attr['radius'] : 'yes';
		$has_radius       = isset( $attr['has_radius'] ) ? $attr['has_radius'] : $has_radius;
		
		$has_map          = isset( $attr['map'] ) ? $attr['map'] : 'yes';
		$has_map          = isset( $attr['has_map'] ) ? $attr['has_map'] : $has_map;
		
		$search_data     = array();
		if( $store_category ) {
			$search_data['wcfmsc_store_categories'] = $store_category;
		}
		if( $search_country ) {
			$search_data['wcfmmp_store_country'] = $search_country;
		}
		if( $search_state ) {
			$search_data['wcfmmp_store_state'] = $search_state;
		}
		if( $search_city ) {
			$search_data['wcfmmp_store_city'] = $search_city;
		}
		if( $search_zip ) {
			$search_data['wcfmmp_store_zip'] = $search_zip;
		}
		if( isset( $_POST['search_data'] ) ) {
			parse_str($_POST['search_data'], $search_data);
		}
		
		// Exclude Membership
		$exclude_members = array();
		$exclude_membership = isset( $attr['exclude_membership'] ) ? sanitize_text_field( $attr['exclude_membership'] ) : '';
		if( $exclude_membership ) $exclude_membership = explode(",", $exclude_membership);
		if( !empty( $exclude_membership ) && is_array( $exclude_membership ) ) {
			foreach( $exclude_membership as $wcfm_membership ) {
				$membership_users = (array) get_post_meta( $wcfm_membership, 'membership_users', true );
				$exclude_members  = array_merge( $exclude_members, $membership_users );
			}
		}
		if( $exclude_members ) $exclude_members = implode(",", $exclude_members);
		else $exclude_members = '';
		
		// Excluded Stores from List
		$excludes = !empty( $attr['excludes'] ) ? sanitize_text_field( $attr['excludes'] ) : $exclude_members;
		$search_data['excludes'] = $excludes;
		
		// Include Membership
		$include_members = array();
		$include_membership = isset( $attr['include_membership'] ) ? sanitize_text_field( $attr['include_membership'] ) : '';
		if( $include_membership ) $include_membership = explode(",", $include_membership);
		if( !empty( $include_membership ) && is_array( $include_membership ) ) {
			foreach( $include_membership as $wcfm_membership ) {
				$membership_users = (array) get_post_meta( $wcfm_membership, 'membership_users', true );
				$include_members  = array_merge( $include_members, $membership_users );
			}
		}
		if( $include_members ) $include_members = implode(",", $include_members);
		else $include_members = '';
		
		// Include Store List
		$includes = !empty( $attr['includes'] ) ? sanitize_text_field( $attr['includes'] ) : $include_members;
		if( $includes ) $includes = explode(",", $includes);
		else $includes = array();
		
		$pagination_base = '';
		
		// Radious Search
		$enable_wcfm_storelist_radius  = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_storelist_radius'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_storelist_radius'] : 'no';
		$has_radius = wc_string_to_bool( $has_radius);
		if( ( $enable_wcfm_storelist_radius == 'yes' ) && $has_radius ) $has_radius = true;
		else $has_radius = false;
		$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
		if ( ($wcfm_map_lib == 'google') && empty( $api_key ) ) $has_radius = false;
		if( $has_radius ) {
			$has_country = 'no';
			$has_state   = 'no';
			$has_city    = 'no';
			$has_zipcode = 'no';
		}
		
		$stores = $WCFMmp->wcfmmp_vendor->wcfmmp_search_vendor_list( true, '', $attr['limit'], $search_term, $search_category, $search_data, $has_product, $includes );
			

		$template_args = apply_filters( 'wcfmmp_stores_map_args', array(
			  'stores'          => $stores,
			  'offset'          => 0,
				'excludes'        => $excludes,
				'search_term'     => $search_term,
				'search_country'  => $search_country,
				'search_state'    => $search_state,
				'search_city'     => $search_city,
				'search_zip'      => $search_zip,
				'search_category' => $search_category,
				'store_category'  => $store_category,
				'search_data'     => $search_data,
				'pagination_base' => $pagination_base,
				'has_product'     => $has_product,
				'filter'          => wc_string_to_bool( $has_filter ),
				'search'          => wc_string_to_bool( $has_search ),
				'category'        => wc_string_to_bool( $has_category ),
				'country'         => wc_string_to_bool( $has_country ),
				'state'           => wc_string_to_bool( $has_state ),
				'has_city'        => wc_string_to_bool( $has_city ),
				'has_zip'         => wc_string_to_bool( $has_zip ),
				'map'             => wc_string_to_bool( $has_map ), 
				'radius'          => $has_radius,
				'map_zoom'        => apply_filters( 'wcfmmp_map_default_zoom_level', $attr['map_zoom'] ),
				'auto_zoom'       => wc_string_to_bool( apply_filters( 'wcfmmp_is_allow_map_auto_zoom', $attr['auto_zoom'] ) ),
				'includes'        => $includes,
				'per_row'         => $attr['per_row'],
				'limit'           => $attr['limit'],
				'orderby'         => '',
				'has_orderby'     => wc_string_to_bool( 'no' ),
				'sidebar'         => wc_string_to_bool( 'no' ),
				'theme'           => 'simple'
		) );
		
		ob_start();
		echo '<div id="wcfmmp-stores-map-wrapper">';
		$WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-map.php', $template_args );
		if( wc_string_to_bool( $has_filter ) ) {
			if( wc_string_to_bool( $has_search ) || $has_radius || wc_string_to_bool( $has_category ) || wc_string_to_bool( $has_country ) || wc_string_to_bool( $has_state ) || wc_string_to_bool( $has_city ) || wc_string_to_bool( $has_zip ) ) { $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-search-form.php', $template_args ); }
		}
		echo '</div>';
		echo '<div class="wcfm-clearfix"></div>';
		return ob_get_clean();
	}
	
	
	/**
	 * WCFM Stores Carousal Short Code
	 */
	public function wcfmmp_stores_carousel_shortcode( $atts ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $includes;
		$WCFM->nocache();
		
		$defaults = array(
				'per_page'           => 20,
				'category'           => 'yes',
				'country'            => 'yes',
				'state'              => 'yes',
				'has_category'       => 'yes',
				'has_country'        => 'yes',
				'has_state'          => 'yes',
				'has_city'           => 'no',
				'has_zip'            => 'no',
				'has_loop'           => 'yes',
				'has_autoplay'       => 'yes',
				'has_nav'            => 'yes',
				'per_row'            => 3,
				'includes'           => '',
				'excludes'           => '',
				'include_membership' => '',
				'exclude_membership' => '',
				'search_term'        => '',
				'search_city'        => '',
				'search_zip'         => '',
				'search_category'    => '',
				'store_category'     => '',
				'has_product'        => '',
				'theme'              => 'classic'
		);

		$attr   = shortcode_atts( apply_filters( 'wcfmmp_stores_carousel_default_args', $defaults ), $atts );
		$paged  = max( 1, get_query_var( 'paged' ) );
		$length = apply_filters( 'wcfmmp_stores_per_page', $attr['per_page'] );
		$offset = ( $paged - 1 ) * $length;
		
		$search_country = '';
		$search_state   = '';
		
		// GEO Locate Support
		if( apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ) ) {
			if( is_user_logged_in() && !$search_country ) {
				$user_location = get_user_meta( get_current_user_id(), 'wcfm_user_location', true );
				if( $user_location ) {
					$search_country = $user_location['country'];
					$search_state   = $user_location['state'];
				}
			}
					
			if( apply_filters( 'wcfm_is_allow_wc_geolocate', true ) && class_exists( 'WC_Geolocation' ) && !$search_country ) {
				$user_location = WC_Geolocation::geolocate_ip();
				$search_country = $user_location['country'];
				$search_state   = $user_location['state'];
			}
		}

		$search_term      = isset( $_GET['wcfmmp_store_search'] ) ? sanitize_text_field( $_GET['wcfmmp_store_search'] ) : $attr['search_term'];
		
		$search_country   = isset( $_GET['wcfmmp_store_country'] ) ? sanitize_text_field( $_GET['wcfmmp_store_country'] ) : $search_country;
		$search_state     = isset( $_GET['wcfmmp_store_state'] ) ? sanitize_text_field( $_GET['wcfmmp_store_state'] ) : $search_state;
		$search_city      = isset( $_GET['wcfmmp_store_city'] ) ? sanitize_text_field( $_GET['wcfmmp_store_city'] ) : $attr['search_city'];
		$search_zip       = isset( $_GET['wcfmmp_store_zip'] ) ? sanitize_text_field( $_GET['wcfmmp_store_zip'] ) : $attr['search_zip'];
		
		$search_category  = isset( $_GET['wcfmmp_store_category'] ) ? sanitize_text_field( $_GET['wcfmmp_store_category'] ) : $attr['search_category'];
		$store_category   = isset( $_GET['wcfmsc_store_categories'] ) ? sanitize_text_field( $_GET['wcfmsc_store_categories'] ) : $attr['store_category'];
		
		$search_category  = apply_filters( 'wcfmmp_stores_default_search_category', $search_category );
		$has_product      = apply_filters( 'wcfmmp_stores_list_has_product', $attr['has_product'] );
		
		$has_category     = isset( $attr['category'] ) ? $attr['category'] : 'yes';
		$has_category     = isset( $attr['has_category'] ) ? $attr['has_category'] : $has_category;
		
		$has_country      = isset( $attr['country'] ) ? $attr['country'] : 'yes';
		$has_country      = isset( $attr['has_country'] ) ? $attr['has_country'] : $has_country;
		
		$has_state        = isset( $attr['state'] ) ? $attr['state'] : 'yes';
		$has_state        = isset( $attr['has_state'] ) ? $attr['has_state'] : $has_state;
		
		$has_city         = isset( $attr['has_city'] ) ? $attr['has_city'] : 'no';
		
		$has_zip          = isset( $attr['has_zip'] ) ? $attr['has_zip'] : 'no';
		
		$search_data     = array();
		if( $store_category ) {
			$search_data['wcfmsc_store_categories'] = $store_category;
		}
		if( $search_country ) {
			$search_data['wcfmmp_store_country'] = $search_country;
		}
		if( $search_state ) {
			$search_data['wcfmmp_store_state'] = $search_state;
		}
		if( $search_city ) {
			$search_data['wcfmmp_store_city'] = $search_city;
		}
		if( $search_zip ) {
			$search_data['wcfmmp_store_zip'] = $search_zip;
		}
		if( isset( $_POST['search_data'] ) ) {
			parse_str($_POST['search_data'], $search_data);
		}
		
		// Exclude Membership
		$exclude_members = array();
		$exclude_membership = isset( $attr['exclude_membership'] ) ? sanitize_text_field( $attr['exclude_membership'] ) : '';
		if( $exclude_membership ) $exclude_membership = explode(",", $exclude_membership);
		if( !empty( $exclude_membership ) && is_array( $exclude_membership ) ) {
			foreach( $exclude_membership as $wcfm_membership ) {
				$membership_users = (array) get_post_meta( $wcfm_membership, 'membership_users', true );
				$exclude_members  = array_merge( $exclude_members, $membership_users );
			}
		}
		if( $exclude_members ) $exclude_members = implode(",", $exclude_members);
		else $exclude_members = '';
		
		// Excluded Stores from List
		$excludes = !empty( $attr['excludes'] ) ? sanitize_text_field( $attr['excludes'] ) : $exclude_members;
		$search_data['excludes'] = $excludes;
		
		// Include Membership
		$include_members = array();
		$include_membership = isset( $attr['include_membership'] ) ? sanitize_text_field( $attr['include_membership'] ) : '';
		if( $include_membership ) $include_membership = explode(",", $include_membership);
		if( !empty( $include_membership ) && is_array( $include_membership ) ) {
			foreach( $include_membership as $wcfm_membership ) {
				$membership_users = (array) get_post_meta( $wcfm_membership, 'membership_users', true );
				$include_members  = array_merge( $include_members, $membership_users );
			}
		}
		if( $include_members ) $include_members = implode(",", $include_members);
		else $include_members = '';
		
		// Include Store List
		$includes = !empty( $attr['includes'] ) ? sanitize_text_field( $attr['includes'] ) : $include_members;
		if( $includes ) $includes = explode(",", $includes);
		else $includes = array();
			

		$stores = $WCFMmp->wcfmmp_vendor->wcfmmp_search_vendor_list( true, $offset, $length, $search_term, $search_category, $search_data, $has_product, $includes );

		$template_args = apply_filters( 'wcfmmp_stores_args', array(
				'stores'          => $stores,
				'limit'           => $length,
				'offset'          => $offset,
				'includes'        => $includes,
				'excludes'        => $excludes,
				'search_term'     => $search_term,
				'search_country'  => $search_country,
				'search_state'    => $search_state,
				'search_city'     => $search_city,
				'search_zip'      => $search_zip,
				'search_category' => $search_category,
				'store_category'  => $store_category,
				'search_data'     => $search_data,
				'has_product'     => $has_product,
				'category'        => wc_string_to_bool( $has_category ),
				'country'         => wc_string_to_bool( $has_country ),
				'state'           => wc_string_to_bool( $has_state ),
				'has_city'        => wc_string_to_bool( $has_city ),
				'has_zip'         => wc_string_to_bool( $has_zip ),
				'has_loop'        => wc_string_to_bool( $attr['has_loop'] ),
				'has_autoplay'    => wc_string_to_bool( $attr['has_autoplay'] ),
				'has_nav'         => wc_string_to_bool( $attr['has_nav'] ),
				'per_row'         => $attr['per_row'],
				'theme'           => $attr['theme']
		), $attr, $search_data);
		
		ob_start();
		$WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-carousel.php', $template_args );
		return ob_get_clean();
	}
	
	/**
	 * WCFM Marketplace Store Sold By Shortcode
	 */
	public function wcfmmp_store_sold_by_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $post;

		if( !$post ) return;
		if( !wcfm_is_vendor( $post->post_author ) ) return;
		
		$store_user   = wcfmmp_get_store( $post->post_author );
		$store_info   = $store_user->get_shop_info();
		
		$is_store_offline = get_user_meta( $store_user->get_id(), '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
		
		ob_start();
		
		do_action( 'wcfmmp_store_before_sidebar_info', $store_user->get_id() );
		
		$vendor_sold_by_template = $WCFMmp->wcfmmp_vendor->get_vendor_sold_by_template();
		
		if( $vendor_sold_by_template == 'advanced' ) {
			$WCFMmp->template->get_template( 'sold-by/wcfmmp-view-sold-by-advanced.php', array( 'vendor_id' => $store_user->get_id() ) );
		} else {
			$WCFMmp->template->get_template( 'sold-by/wcfmmp-view-sold-by-simple.php', array( 'vendor_id' => $store_user->get_id() ) );
		}
		wp_enqueue_style( 'wcfmmp_product_css',  $WCFMmp->library->css_lib_url_min . 'store/wcfmmp-style-product.css', array(), $WCFMmp->version );

		do_action( 'wcfmmp_store_after_sidebar_info', $store_user->get_id() );
		
		$content = ob_get_clean();
		return $content;
	}
	
	/**
	 * WCFM Marketplace Store Info Shortcode
	 */
	public function wcfmmp_store_info_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $post;
		
		$store_id = '';
		if ( isset( $attr['id'] ) && !empty( $attr['id'] ) ) { $store_id = absint($attr['id']); }
		
		if ( !$store_id && wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
			$store_id  = 0;
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
		}
		
		if( !$store_id && is_product() ) {
			$store_id = $post->post_author;
		}
		
		if( !$store_id && is_single() && $post && is_object( $post ) && wcfm_is_vendor( $post->post_author ) ) {
			$store_id = $post->post_author;
		}
		
		$data_info = '';
		if ( isset( $attr['data'] ) && !empty( $attr['data'] ) ) { $data_info = $attr['data']; }
		
		if( !$store_id ) return;
		if( !$data_info ) return;
		
		$is_store_offline = get_user_meta( $store_id, '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
		
		$label = '';
		if ( isset( $attr['label'] ) && !empty( $attr['label'] ) ) { $label = $attr['label']; }
		
		$icon = '';
		if ( isset( $attr['icon'] ) && !empty( $attr['icon'] ) ) { $icon = $attr['icon']; }
		
		$store_user  = wcfmmp_get_store( $store_id );
		$store_info  = $store_user->get_shop_info();
		
		$content = '<div class="wcfmmp_store_info wcfmmp_store_info_' . $data_info . '">';
		
		if( $icon ) {
			$content .= '<i style="display:inline-block" class="wcfmfa fa-' . $icon . ' wcfmmp_store_info_icon wcfmmp_store_info_iconl_' . $data_info . '"></i>&nbsp&nbsp';
		}
		
		if( $label ) {
			$content .= '<span style="display:inline-block" class="wcfmmp_store_info_label wcfmmp_store_info_label_' . $data_info . '">' . $label . '</span>:&nbsp';
		}
		
		switch( $data_info ) {
			case 'store_name':
				$content .= $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( $store_id );
				break;
				
			case 'store_url':
				$content .=  $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( $store_id );
				break;
				
			case 'store_address':
				$content .= '<span style="display:inline-block" class="wcfmmp_store_info_content wcfmmp_store_info_content_' . $data_info . '">' . apply_filters( 'wcfmmp_additional_store_info', $store_user->get_address_string(), $data_info, $store_id ) . '</span>';
				break;
				
		  case 'store_email':
				$content .= '<span style="display:inline-block" class="wcfmmp_store_info_content wcfmmp_store_info_content_' . $data_info . '">' . apply_filters( 'wcfmmp_additional_store_info', apply_filters( 'wcfmmp_mailto_email', $store_user->get_email(), $store_id ), $data_info, $store_id ) . '</span>';
				break;
			
			case 'store_phone':
				$content .=  '<span style="display:inline-block" class="wcfmmp_store_info_content wcfmmp_store_info_content_' . $data_info . '">' . apply_filters( 'wcfmmp_additional_store_info', $store_user->get_phone(), $data_info, $store_id ) . '</span>';
				break;
				
			case 'store_gravatar':
				$content .=  '<img src="' . $store_user->get_avatar() . '" />';
				break;
				
			case 'store_banner':
				$content .=  '<img src="' . $store_user->get_banner() . '" />';
				break;
				
			case 'store_support':
				$content .=  $store_user->get_customer_support_details();
				break;
				
		  case 'store_social':
		  	ob_start();
		  	$WCFMmp->template->get_template( 'store/wcfmmp-view-store-social.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
				$content .=  ob_get_clean();
				if( !wcfmmp_is_store_page() && !is_product() ) {
					wp_enqueue_style( 'wcfmmp_product_css',  $WCFMmp->library->css_lib_url . 'store/wcfmmp-style-product.css', array(), $WCFMmp->version );
				}
				break;
				
			case 'store_location':
				$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
				$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
				if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
				$store_lat    = isset( $store_info['store_lat'] ) ? esc_attr( $store_info['store_lat'] ) : 0;
				$store_lng    = isset( $store_info['store_lng'] ) ? esc_attr( $store_info['store_lng'] ) : 0;
	
				if ( ( ( ($wcfm_map_lib == 'google') && !empty( $api_key ) ) || ($wcfm_map_lib == 'leaflet') ) && !empty( $store_lat ) && !empty( $store_lng ) ) {
					ob_start();
					$WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-store-location.php', array( 
																												 'store_user' => $store_user, 
																												 'store_info' => $store_info,
																												 'store_lat'  => $store_lat,
																												 'store_lng'  => $store_lng,
																												 'map_id'     => 'wcfm_sold_by_widget_map_'.rand(10,100)
																												 ) );
					$content .=  ob_get_clean();
					
					if( !wcfmmp_is_store_page() ) {
						wp_enqueue_style( 'wcfmmp_product_css',  $WCFMmp->library->css_lib_url . 'store/wcfmmp-style-product.css', array(), $WCFMmp->version );
						wp_enqueue_script( 'wcfmmp_store_js', $WCFMmp->library->js_lib_url . 'store/wcfmmp-script-store.js', array('jquery' ), $WCFMmp->version, true );
						$WCFMmp->library->load_map_lib();
					}
				}
				break;
				
			case 'store_rating':
				ob_start();
				$store_user->show_star_rating();
				$content .=  ob_get_clean();
				break;
				
			case 'store_badges':
				ob_start();
				do_action('wcfmmp_single_product_sold_by_badges', $store_id );
				$content .=  ob_get_clean();
				break;
				
			case 'register_on':
				$data_value = get_user_meta( $store_id, 'wcfm_register_on', true );
				if( $data_value ) {
					$content .=  '<span style="display:inline-block" class="wcfmmp_store_info_content wcfmmp_store_info_content_' . $data_info . '">' . apply_filters( 'wcfmmp_additional_store_info', date( wc_date_format(), $data_value ), $data_info, $store_id ) . '</span>';
				}
				break;
				
			default:
				$data_value = get_user_meta( $store_id, $data_info, true );
				if( $data_value && is_array( $data_value ) ) {
					$data_value = implode( ", ", $data_value );
				}
				$content .=  '<span style="display:inline-block" class="wcfmmp_store_info_content wcfmmp_store_info_content_' . $data_info . '">' . apply_filters( 'wcfmmp_additional_store_info', $data_value, $data_info, $store_id ) . '</span>';
			  break;
		}
		
		$content .= '</div>';
		
		return $content;
		
	}
	
	/**
	 * WCFM Marketplace Store Hours Shortcode
	 */
	public function wcfmmp_store_hours_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $post;
		
		if( !apply_filters( 'wcfm_is_pref_store_hours', true ) ) return;
		
		$store_id = '';
		if ( isset( $attr['id'] ) && !empty( $attr['id'] ) ) { $store_id = absint($attr['id']); }
		
		if (  wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = get_query_var( $wcfm_store_url );
			$store_id  = 0;
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
		}
		
		if( is_product() ) {
			$store_id = $post->post_author;
		}
		
		if( !$store_id ) return;
		
		$is_store_offline = get_user_meta( $store_id, '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
		
		if( !$WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_id, 'store_hours' ) ) return;
		
		$store_user  = wcfmmp_get_store( $store_id );
		
		$wcfm_vendor_store_hours = get_user_meta( $store_id, 'wcfm_vendor_store_hours', true );
		if( !$wcfm_vendor_store_hours ) {
			return;
		}
		
		$wcfm_store_hours_enable = isset( $wcfm_vendor_store_hours['enable'] ) ? 'yes' : 'no';
		if( $wcfm_store_hours_enable != 'yes' ) return;
		
		$wcfm_store_hours_off_days  = isset( $wcfm_vendor_store_hours['off_days'] ) ? $wcfm_vendor_store_hours['off_days'] : array();
		$wcfm_store_hours_day_times = isset( $wcfm_vendor_store_hours['day_times'] ) ? $wcfm_vendor_store_hours['day_times'] : array();
		if( empty( $wcfm_store_hours_day_times ) ) return;
		
		$weekdays = array( 0 => __( 'Monday', 'wc-multivendor-marketplace' ), 1 => __( 'Tuesday', 'wc-multivendor-marketplace' ), 2 => __( 'Wednesday', 'wc-multivendor-marketplace' ), 3 => __( 'Thursday', 'wc-multivendor-marketplace' ), 4 => __( 'Friday', 'wc-multivendor-marketplace' ), 5 => __( 'Saturday', 'wc-multivendor-marketplace' ), 6 => __( 'Sunday', 'wc-multivendor-marketplace') );
		
		$content = '<div class="wcfmmp_store_hours">';
		$content .= '<span class="wcfmmp-store-hours widget-title"><span class="wcfmfa fa-clock"></span>&nbsp;' . apply_filters( 'wcfm_store_hours_label', __( 'Store Hours', 'wc-multivendor-marketplace' ) ) . '</span><div class="wcfm_clearfix"></div>';
		
		ob_start();
		$WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-store-hours.php', array( 
			                                             'wcfm_store_hours_day_times' => $wcfm_store_hours_day_times, 
			                                             'wcfm_store_hours_off_days'  => $wcfm_store_hours_off_days,
			                                             'weekdays' => $weekdays,
			                                             'store_id' => $store_id,
			                                             ) );
		$content .= ob_get_clean();
		
		$content .= '</div>';
		
		return $content;
	}
	
	/**
	 * WCFM Marketplace Shipping Time Shortcode
	 */
	function wcfmmp_shipping_time_shortcode() {
		global $WCFM, $WCFMmp, $post;
  	
  	if( !apply_filters( 'wcfm_is_allow_store_shipping', true ) ) return; 
		if( !apply_filters( 'wcfm_is_allow_shipping_processing_time_info', true ) ) return;
		if( !is_product() ) return;
		
		$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return;
			
		$vendor_id = 0;
		$product_id = 0;
		if( is_product() && $post && is_object( $post ) ) {
			$product_id = $post->ID;
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		}
		if( !$product_id ) return;
		if( !$WCFM || !$WCFM->frontend || !$WCFM->frontend->is_wcfm_needs_shipping( $product_id ) ) return;
		if( !$vendor_id ) return;
		
		if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by( $vendor_id ) ) {
			ob_start();
			$WCFMmp->template->get_template( 'shipping/wcfmmp-view-shipping-info.php', array( 'vendor_id' => $vendor_id, 'product_id' => $product_id ) );
			$content = ob_get_clean();
			return $content;
    }
	}
	
	/**
	 * WCFM Marketplace Store Hours Shortcode
	 */
	public function wcfmmp_more_offers_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $post;
		
		if( !apply_filters( 'wcfm_is_pref_product_multivendor', true ) ) return;
		
		if( !is_product() ) return;
		
		//$content = '<div class="wcfmmp_more_offers">';
		//$content .= '<span class="wcfmmp-more-offers widget-title"><span class="wcfmfa fa-clock"></span>&nbsp;' . apply_filters( 'wcfm_store_hours_label', __( 'Store Hours', 'wc-multivendor-marketplace' ) ) . '</span><div class="wcfm_clearfix"></div>';
		
		ob_start();
		$WCFMmp->template->get_template( 'product_multivendor/wcfmmp-view-more-offers.php' );
		$content = ob_get_clean();
		
		//$content .= '</div>';
		
		return $content;
	}
	
	/**
	 * WCFM Marketplace Store FB Widget Shortcode
	 */
	public function wcfmmp_store_fb_widget_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $post;
		
		$store_id  = 0;
		if (  wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = get_query_var( $wcfm_store_url );
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
		}
		
		if( is_product() ) {
			$store_id = $post->post_author;
		}
		
		if( !$store_id ) return;
		
		$store_user   = wcfmmp_get_store( $store_id );
		$store_info   = $store_user->get_shop_info();
		
		if( !isset( $store_info['social']['fb'] ) || empty( $store_info['social']['fb'] ) ) return;
		
		$facebook_url = wcfmmp_generate_social_url( $store_info['social']['fb'], 'facebook' );
		
		if( !$facebook_url ) return;
		
		$custom_css = apply_filters( 'wcfmmp_fb_widget_custom_style', '' );
		
		$content = '';
		
		ob_start();
		
		echo '<div class="wcfm_store_fb_widget" style="margin-bottom:50px;">';
		
		echo '<div id="fb-root"></div>';
    echo '<div class="fb-page" data-href="' . $facebook_url . '" 
                               data-width="400" 
                               data-height="500" 
                               data-small-header="true" 
                               data-adapt-container-width="true" 
                               data-hide-cover="false" 
                               data-show-facepile="false" 
                               data-show-posts="true" 
                               style="' . $custom_css . '"></div>';
		
		echo '</div>';
		
		$content .= ob_get_clean();
		
		wp_register_script( 'wcfm_store_fb_widget_script', $WCFMmp->library->js_lib_url_min . 'store/wcfmmp-script-fb-widget.js', array('jquery') );
    wp_enqueue_script( 'wcfm_store_fb_widget_script' );
    $local_variables = array( 'app_id' => '503595753002055', 'select_lng' => 'en_US' );
    wp_localize_script( 'wcfm_store_fb_widget_script', 'wcfm_store_fb_widget_vars', $local_variables );
		
		return $content;
	}
	
	/**
	 * WCFM Marketplace Store Twitter Widget Shortcode
	 */
	public function wcfmmp_store_twitter_widget_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $post;
		
		$store_id  = 0;
		if (  wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = get_query_var( $wcfm_store_url );
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
		}
		
		if( is_product() ) {
			$store_id = $post->post_author;
		}
		
		if( !$store_id ) return;
		
		$store_user   = wcfmmp_get_store( $store_id );
		$store_info   = $store_user->get_shop_info();
		
		if( !isset( $store_info['social']['twitter'] ) || empty( $store_info['social']['twitter'] ) ) return;
		
		$twitter_url = wcfmmp_generate_social_url( $store_info['social']['twitter'], 'twitter' );
		
		if( !$twitter_url ) return;
		
		$custom_css = apply_filters( 'wcfmmp_twitter_widget_custom_style', '' );
		
		$oembed_params = array( 'omit_script' => 1, 'lang' => 'en', 'maxwidth' => 300, 'maxheight' => 400, 'chrome' => '', 'url' => $twitter_url );
		
		$QUEUE_HANDLE = 'twitter-wjs';
		$URI = 'https://platform.twitter.com/widgets.js';
		$script = 'window.twttr=(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};if(d.getElementById(id))return t;js=d.createElement(s);js.id=id;js.src=' . wp_json_encode( $URI ) . ';fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){t._e.push(f);};return t;}(document,"script",' . wp_json_encode( $QUEUE_HANDLE ) . '));';
		
		$content = '';
		
		ob_start();
		
		echo '<div class="wcfm_store_twitter_widget" style="margin-bottom:50px;">';
		
    echo '<div class=" wcfmmp_twitter twitter-timeline-profile">
          <a class="twitter-timeline" data-lang="en" 
                                      data-width="100%" 
                                      data-height="400" 
                                      href="' . $twitter_url . '?ref_src=twsrc%5Etfw">Recent Tweets</a></div>';
                                      
		echo '<script>' . $script . '</script>';
		
		echo '</div>';
		
		$content .= ob_get_clean();
		
		return $content;
	}
	
	/**
	 * WCFM Marketplace Store Instagram Widget Shortcode
	 */
	public function wcfmmp_store_instagram_widget_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $post;
		
		$store_id  = 0;
		if (  wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = get_query_var( $wcfm_store_url );
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
		}
		
		if( is_product() ) {
			$store_id = $post->post_author;
		}
		
		if( !$store_id ) return;
		
		$store_user   = wcfmmp_get_store( $store_id );
		$store_info   = $store_user->get_shop_info();
		
		if( !isset( $store_info['social']['instagram'] ) || empty( $store_info['social']['instagram'] ) ) return;
		
		$instagram_url = wcfmmp_generate_social_url( $store_info['social']['instagram'], 'instagram' );
		
		if( !$instagram_url ) return;
		
		ob_start();
		
		echo '<div class="wcfm_store_instagram_widget" style="margin-bottom:50px;">';
		
    
		
		echo '</div>';
		
		$content .= ob_get_clean();
		
		return $content;
	}
	
	/**
	 * WCFM Marketplace Product List Store Filter Widget Shortcode
	 */
	function wcfmmp_products_store_filter_shortcode( $attr ) {
		global $WCFM, $WCFMmp, $wp;

		if ( ! is_shop() && ! is_product_taxonomy() ) {
				return;
		}
		
		$args = array(); // For future use
		
		ob_start();
		$WCFMmp->template->get_template( 'product-geolocate/wcfmmp-view-product-lists-vendor-filter.php', $args );
		return ob_get_clean();
	}
	
	/**
	 * Helper Functions
	 */

	/**
	 * Shortcode Wrapper
	 *
	 * @access public
	 * @param mixed $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public function shortcode_wrapper($function, $atts = array()) {
		ob_start();
		call_user_func($function, $atts);
		return ob_get_clean();
	}

	/**
	 * Shortcode CLass Loader
	 *
	 * @access public
	 * @param mixed $class_name
	 * @return void
	 */
	public function load_class($class_name = '') {
		global $WCFM;
		if ('' != $class_name && '' != $WCFM->token) {
			require_once ( $WCFM->plugin_path . 'includes/shortcodes/class-' . esc_attr($WCFM->token) . '-shortcode-' . esc_attr($class_name) . '.php' );
		}
	}

}
?>