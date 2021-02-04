<?php
/**
 * WCFM Marketplace plugin core
 *
 * Plugin Frontend Controler
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Frontend {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$vendor_sold_by_position = isset( $WCFMmp->wcfmmp_marketplace_options['vendor_sold_by_position'] ) ? $WCFMmp->wcfmmp_marketplace_options['vendor_sold_by_position'] : 'below_atc';
		
		$is_look_hook_defined = false;
		
		// ReHUB Theme Compatibility
		if( function_exists( 'rehub_option' ) ) {
			$is_look_hook_defined = true;
			if( $vendor_sold_by_position == 'bellow_title' ) {
				add_action( 'rh_woo_single_product_title',array( &$this, 'wcfmmp_sold_by_single_product' ), 6 );
			} elseif( $vendor_sold_by_position == 'bellow_price' ) {
				add_action( 'rh_woo_single_product_price',	array( &$this, 'wcfmmp_sold_by_single_product' ), 15 );
			} elseif( $vendor_sold_by_position == 'bellow_sc' ) {
				add_action( 'rh_woo_single_product_description',array( &$this, 'wcfmmp_sold_by_single_product' ), 25 );
			} else {
				add_action( 'rh_woo_single_product_vendor',	array( &$this, 'wcfmmp_sold_by_single_product' ), 50 );
			}
		} 
		
		// Show Product Sold By Label
		if( !$is_look_hook_defined ) {
			if( $vendor_sold_by_position == 'bellow_title' ) {
				add_action( 'woocommerce_single_product_summary',	array( &$this, 'wcfmmp_sold_by_single_product' ), 6 );
			} elseif( $vendor_sold_by_position == 'bellow_price' ) {
				add_action( 'woocommerce_single_product_summary',	array( &$this, 'wcfmmp_sold_by_single_product' ), 15 );
			} elseif( $vendor_sold_by_position == 'bellow_sc' ) {
				add_action( 'woocommerce_single_product_summary',	array( &$this, 'wcfmmp_sold_by_single_product' ), 25 );
			} else {
				add_action( 'woocommerce_product_meta_start',	array( &$this, 'wcfmmp_sold_by_single_product' ), 50 );
			}
		}
		
		if( ( $vendor_sold_by_position == 'bellow_title' ) || ( $vendor_sold_by_position == 'bellow_price' ) || ( $vendor_sold_by_position == 'bellow_sc' ) ) {
			if( apply_filters( 'wcfm_is_allow_quick_view_sold_by', true ) ) {
				// YiTH Quick Product View Sold By
				add_action( 'yith_wcqv_product_summary',	array( &$this, 'wcfmmp_sold_by_single_product' ), 35 );
			
				// Flatsome Quick Product View Sold by
				add_action( 'woocommerce_single_product_lightbox_summary', array( &$this, 'wcfmmp_sold_by_single_product' ), 35 );
				
				// WooCommerce Quick View Pro View Sold by
				add_action( 'wc_quick_view_pro_quick_view_product_details', array( &$this, 'wcfmmp_sold_by_single_product' ), 35 );
			}
		}
		
		// Martfury Theme Compatibility
		if( function_exists( 'martfury_is_vendor_page' ) ) {
			$is_look_hook_defined = true;
			add_action('woocommerce_after_shop_loop_item_title', array( $this, 'wcfmmp_sold_by_product' ), 140 );
			add_action( 'martfury_woo_after_shop_loop_item_title', array( $this, 'wcfmmp_sold_by_product' ), 20 );
		}
		
		// Ocean WP Theme Compatibility
		if( function_exists( 'oceanwp_woo_product_elements_positioning' ) ) {
			$is_look_hook_defined = true;
			add_action('ocean_before_archive_product_add_to_cart_inner', array( $this, 'wcfmmp_sold_by_product' ), 50 );
		}
		
		// SW WooCommerce Compatibility
		if( function_exists( 'sw_woocommerce_construct' ) ) {
			$is_look_hook_defined = true;
			add_action('woocommerce_after_shop_loop_item', array( $this, 'wcfmmp_sold_by_product' ), 50 );
			add_action('sw_custom_mobile', array( $this, 'wcfmmp_sold_by_product' ), 50 );
		}
		
		// Tech Market Theme Compatibility
		add_action('woocommerce_after_grid_extended_item_title', array( $this, 'wcfmmp_sold_by_product' ), 80 );
		add_action('woocommerce_after_list_view_item_title', array( $this, 'wcfmmp_sold_by_product' ), 80 );
		add_action('woocommerce_after_list_view_large_item_title', array( $this, 'wcfmmp_sold_by_product' ), 80 );
		add_action('woocommerce_after_list_view_small_item_title', array( $this, 'wcfmmp_sold_by_product' ), 80 );
		
		// Show Product Sold By in Loop
		if( !$is_look_hook_defined ) {
			if( $vendor_sold_by_position == 'bellow_title' ) {
				add_action('woocommerce_after_shop_loop_item_title', array( $this, 'wcfmmp_sold_by_product' ), 9 );
			} else if( $vendor_sold_by_position == 'bellow_atc' ) {
				add_action('woocommerce_after_shop_loop_item', array( $this, 'wcfmmp_sold_by_product' ), 50 );
			} else {
				add_action('woocommerce_after_shop_loop_item_title', array( $this, 'wcfmmp_sold_by_product' ), 50 );
			}
		}
		
		// Show Product Sold By in Cart
		add_filter('woocommerce_get_item_data', array( &$this, 'wcfmmp_sold_by_cart' ), 50, 2 );
		
		// Checkout Location Field
		add_filter( 'wcfmmp_is_allow_checkout_user_location', array( &$this, 'wcfmmp_is_allow_checkout_user_location' ), 50 );
		add_filter( 'woocommerce_checkout_fields', array( &$this, 'wcfmmp_checkout_user_location_fields' ), 50 );
		add_action( 'woocommerce_after_checkout_billing_form', array( &$this, 'wcfmmp_checkout_user_location_map' ), 50 );
		add_action( 'woocommerce_checkout_update_order_review', array( &$this, 'wcfmmp_checkout_user_location_session_set' ), 50 );
		add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'wcfmmp_checkout_user_location_save' ), 50 );
		add_action( 'wcfm_order_details_after_address', array( &$this, 'wcfmmp_order_details_user_location_show' ), 50 );
		//add_action( 'woocommerce_admin_order_data_after_shipping_address', array( &$this, 'wcfmmp_order_details_user_location_show' ), 50 );
		add_action( 'wcfm_orderlist_shipping_address', array( &$this, 'wcfmmp_order_list_user_location_show' ), 50, 2 );
		
		// WC Products Short Code Store Attribute Compatibility
		add_filter( 'shortcode_atts_products', array( $this, 'wcfmmp_shortcode_atts_products' ), 50, 4 );
		add_filter( 'shortcode_atts_sale_products', array( $this, 'wcfmmp_shortcode_atts_products' ), 50, 4 );
		add_filter( 'shortcode_atts_recent_products', array( $this, 'wcfmmp_shortcode_atts_products' ), 50, 4 );
		add_filter( 'shortcode_atts_featured_products', array( $this, 'wcfmmp_shortcode_atts_products' ), 50, 4 );
		add_filter( 'shortcode_atts_top_rated_products', array( $this, 'wcfmmp_shortcode_atts_products' ), 50, 4 );
		add_filter( 'shortcode_atts_best_selling_products', array( $this, 'wcfmmp_shortcode_atts_products' ), 50, 4 );
		add_filter( 'woocommerce_shortcode_products_query', array( $this, 'wcfmmp_woocommerce_shortcode_products_query' ), 50, 3 );
		
		// Store Related Product Rule
		add_filter( 'woocommerce_product_related_posts_query', array( &$this, 'wcfmmp_store_related_products' ), 99, 2 );
		
		// Store Order Next-Previous Link
		if( apply_filters( 'wcfm_is_allow_header_store_order_related_orders', false ) ) { 
			add_action( 'begin_wcfm_orders_details', array( &$this, 'wcfmmp_store_order_related_orders' ),500, 1 );
		}
		add_action( 'end_wcfm_orders_details', array( &$this, 'wcfmmp_store_order_related_orders' ),500, 1 );
		
		// My Account Vendor Registration URL
		add_action( 'woocommerce_register_form_end', array( &$this, 'wcfmmp_become_vendor_link' ) );
		add_action( 'woocommerce_after_my_account', array( &$this, 'wcfmmp_become_vendor_link' ) );
		
		// My Account Dashboard Menu
		add_filter( 'woocommerce_account_menu_items', array( &$this, 'wcfm_dashboard_my_account_menu_items' ), 999 );
		add_filter( 'woocommerce_get_endpoint_url', array( &$this,  'wcfm_dashboard_my_account_endpoint_redirect'), 10, 4 );
		
		// Membership Commission Rules
		add_filter( 'membership_manager_fields_commission', array( &$this, 'wcfmmp_membership_manager_fields_commission' ) );
		
		// Product List Page Geo Location Filter
		add_action( 'woocommerce_before_shop_loop', array( $this, 'wcfmmp_product_list_geo_location_filter' ), 1 );
		add_action( 'woolentor_woocommerce_archive_product_content', array( $this, 'wcfmmp_product_list_geo_location_filter' ), 1 );
		add_action( 'woocommerce_no_products_found', array( $this, 'wcfmmp_product_list_geo_location_filter' ), 1 );
		add_filter( 'posts_clauses', array( $this, 'wcfmmp_product_list_geo_location_filter_post_clauses' ), 500, 2 );
		
		// GEO Location Disable
		add_filter( 'wcfmmp_is_allow_store_list_by_user_location', array( &$this, 'wcfmmp_is_allow_geo_locate' ) );
		
		// Store Default Logo
		add_filter( 'wcfmmp_store_default_logo', array( &$this, 'wcfmmp_store_default_logo' ) );
		
		// WCFM Store Page Body Class
		add_filter('body_class', array( &$this, 'wcfm_store_body_classes' ) );	
		
		// WCFM Store Page Title
 		//add_filter( 'the_title', array( &$this, 'wcfm_store_page_title' ) );
		
		//enqueue scripts
		add_action( 'wp_enqueue_scripts', array( &$this, 'wcfmmp_scripts' ), 20 );
		
		//enqueue styles
		add_action( 'wp_enqueue_scripts', array( &$this, 'wcfmmp_styles' ), 20 );
		
	}
	
	/**
	 * Show Sold by at Single Product Page
	 */
	public static function wcfmmp_sold_by_single_product() {
		global $WCFM, $WCFMmp, $product;
		
		if( !apply_filters( 'wcfmmp_is_allow_single_product_sold_by', true ) ) return;
		
		if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by() && method_exists( $product, 'get_id' ) ) {
			$product_id = $product->get_id();
			
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			
			if( !$vendor_id ) return;
			
			$vendor_sold_by_template = $WCFMmp->wcfmmp_vendor->get_vendor_sold_by_template();
			
			if( $vendor_sold_by_template == 'tab' ) {
				
			} elseif( ( $vendor_sold_by_template == 'advanced' ) && !defined('DOING_AJAX') ) {
				$WCFMmp->template->get_template( 'sold-by/wcfmmp-view-sold-by-advanced.php', array( 'product_id' => $product_id, 'vendor_id' => $vendor_id ) );
			} else {
				$WCFMmp->template->get_template( 'sold-by/wcfmmp-view-sold-by-simple.php', array( 'product_id' => $product_id, 'vendor_id' => $vendor_id ) );
			}
		}
	}
	
	/**
	 * Show Sold by as Tab at Single Product Page
	 */
	public static function wcfmmp_sold_by_tab_single_product() {
		global $WCFM, $WCFMmp, $product;
		
		if( !apply_filters( 'wcfmmp_is_allow_single_product_sold_by', true ) ) return;
		
		if( !$product ) return;
		if( !method_exists( $product, 'get_id' ) ) return;
		
		if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by() ) {
			$product_id = $product->get_id();
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			$WCFMmp->template->get_template( 'sold-by/wcfmmp-view-sold-by-tab.php', array( 'product_id' => $product_id, 'vendor_id' => $vendor_id ) );
		}
	}
	
	/**
	 * Show sold by at Product Page
	 */
	public static function wcfmmp_sold_by_product() {
		global $WCFM, $WCFMmp, $product;
		
		if ( wcfm_is_store_page() ) return;
		if( !$product ) return;
		if( !method_exists( $product, 'get_id' ) ) return;
		
		if( !apply_filters( 'wcfmmp_is_allow_archive_product_sold_by', true ) ) return;
		
		if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by() ) {
			$product_id = $product->get_id();
			
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			
			if( apply_filters( 'wcfmmp_is_allow_archive_sold_by_advanced', false ) ) { 
				$WCFMmp->template->get_template( 'sold-by/wcfmmp-view-sold-by-advanced.php', array( 'product_id' => $product_id, 'vendor_id' => $vendor_id ) );
			} else {
				$WCFMmp->template->get_template( 'sold-by/wcfmmp-view-sold-by-simple.php', array( 'product_id' => $product_id, 'vendor_id' => $vendor_id ) );
			}
		}
	}
	
	/**
	 * Show sold by at Cart Page
	 */
	public function wcfmmp_sold_by_cart( $cart_item_meta = array(), $cart_item ) {
		global $WCFM, $WCFMmp;
		
		if( !apply_filters( 'wcfmmp_is_allow_cart_sold_by', true ) ) return $cart_item_meta;
		
		if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by() ) {
			$product_id = $cart_item['product_id'];
			if( !$product_id ) {
				$variation_id 	= sanitize_text_field( $cart_item['variation_id'] );
				if( $variation_id ) {
					$product_id = wp_get_post_parent_id( $variation_id );
				}
			}
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			if( $vendor_id ) {
				if( apply_filters( 'wcfmmp_is_allow_sold_by', true, $vendor_id ) && wcfm_vendor_has_capability( $vendor_id, 'sold_by' ) ) {
					// Check is store Online
					$is_store_offline = get_user_meta( $vendor_id, '_wcfm_store_offline', true );
					if ( !$is_store_offline ) {
						$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( absint($vendor_id) );
						
						if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
							$store_name = wcfm_get_vendor_store( absint($vendor_id) );
						} else {
							$store_name = wcfm_get_vendor_store_name( absint($vendor_id) );
						}
						
						do_action('before_wcfmmp_sold_by_label_cart_page', $vendor_id, $product_id );
						if( !is_array( $cart_item_meta ) ) $cart_item_meta = (array) $cart_item_meta;
						$cart_item_meta = array_merge( $cart_item_meta, array( array( 'name' => $sold_by_text, 'value' => $store_name ) ) );
						do_action('after_wcfmmp_sold_by_label_cart_page', $vendor_id, $product_id );
					}
				}
			}
		}
		return $cart_item_meta;
	}
	
	function wcfmmp_is_allow_checkout_user_location( $is_allow ) {
		global $WCFM, $WCFMmp;
		
		$is_allow = false;
		
		$wcfm_marketplace_options = $WCFMmp->wcfmmp_marketplace_options;
		
		$checkout_user_location   = isset( $wcfm_marketplace_options['checkout_user_location'] ) ? $wcfm_marketplace_options['checkout_user_location'] : 'no';
		if( $checkout_user_location == 'yes' ) $is_allow = true;
		
		if( !$is_allow ) {
			$wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
			$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
			$wcfmmp_marketplace_shipping_by_distance_options = get_option( 'woocommerce_wcfmmp_product_shipping_by_distance_settings', array() );
			$wcfmmp_marketplace_shipping_by_distance_enabled = ( !empty($wcfmmp_marketplace_shipping_by_distance_options) && !empty($wcfmmp_marketplace_shipping_by_distance_options['enabled']) ) ? $wcfmmp_marketplace_shipping_by_distance_options['enabled'] : 'no';
			if( ( $wcfmmp_store_shipping_enabled == 'yes' ) && ( $wcfmmp_marketplace_shipping_by_distance_enabled == 'yes' ) ) $is_allow = true;
			
			if( $is_allow ) {
				
			}
		}
		
		return $is_allow;
	}
	
	/**
	 * Checkout User Location Field
	 */
	function wcfmmp_checkout_user_location_fields( $fields ) {
		global $WCFM, $WCFMmp;
		if( ! WC()->is_rest_api_request() ) {
			if( ( true === WC()->cart->needs_shipping() ) && apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
				$fields['billing']['wcfmmp_user_location'] = array(
						'label'     => __( 'Delivery Location', 'wc-multivendor-marketplace' ),
						'placeholder'   => _x( 'Insert your address ..', 'placeholder', 'wc-multivendor-marketplace' ),
						'required'  => true,
						'class'     => array('form-row-wide'),
						'clear'     => true,
						'priority'  => 999,
						'value'     => WC()->session->get( '_wcfmmp_user_location' )
				 );
				$fields['billing']['wcfmmp_user_location_lat'] = array(
						'required'  => false,
						'class'     => array('wcfm_custom_hide'),
						'value'     => WC()->session->get( '_wcfmmp_user_location_lat' )
				 );
				$fields['billing']['wcfmmp_user_location_lng'] = array(
						'required'  => false,
						'class'     => array('wcfm_custom_hide'),
						'value'     => WC()->session->get( '_wcfmmp_user_location_lng' )
				 );
			}
		}

     return $fields;
	}
	
	/**
	 * Checkout User Location Map
	 */
	function wcfmmp_checkout_user_location_map( $checkout ) {
		global $WCFM, $WCFMmp;
		if( ( true === WC()->cart->needs_shipping() ) && apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
			?>
			<div class="woocommerce-billing-fields__field-wrapper">
				<div class="wcfmmp-user-locaton-map" id="wcfmmp-user-locaton-map"></div>
			</div>
			<?php
		}
	}
	
	/**
	 * Checkout User Location Field Save in Session
	 */
	function wcfmmp_checkout_user_location_session_set( $post_data_raw ) {
		global $WCFM, $WCFMmp;
		if( apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
			parse_str( $post_data_raw, $post_data );
			if ( ! empty( $post_data['wcfmmp_user_location'] ) ) {
				WC()->customer->set_props( array( 'wcfmmp_user_location' => sanitize_text_field( $post_data['wcfmmp_user_location'] ) ) );
				WC()->session->set( '_wcfmmp_user_location', sanitize_text_field( $post_data['wcfmmp_user_location'] ) );
			}
			if ( ! empty( $post_data['wcfmmp_user_location_lat'] ) ) {
				WC()->session->set( '_wcfmmp_user_location_lat', sanitize_text_field( $post_data['wcfmmp_user_location_lat'] ) );
			}
			if ( ! empty( $post_data['wcfmmp_user_location_lng'] ) ) {
				WC()->session->set( '_wcfmmp_user_location_lng', sanitize_text_field( $post_data['wcfmmp_user_location_lng'] ) );
			}
		}
	}
	
	/**
	 * Checkout User Location Field Save
	 */
	function wcfmmp_checkout_user_location_save( $order_id ) {
		if( apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
			if ( ! empty( $_POST['wcfmmp_user_location'] ) ) {
				update_post_meta( $order_id, '_wcfmmp_user_location', sanitize_text_field( $_POST['wcfmmp_user_location'] ) );
			}
			if ( ! empty( $_POST['wcfmmp_user_location_lat'] ) ) {
				update_post_meta( $order_id, '_wcfmmp_user_location_lat', sanitize_text_field( $_POST['wcfmmp_user_location_lat'] ) );
			}
			if ( ! empty( $_POST['wcfmmp_user_location_lng'] ) ) {
				update_post_meta( $order_id, '_wcfmmp_user_location_lng', sanitize_text_field( $_POST['wcfmmp_user_location_lng'] ) );
			}
		}
	}
	
	/**
	 * Checkout User Location Show under Order Details
	 */
	function wcfmmp_order_details_user_location_show( $order ) {
		if( apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
			$address = get_post_meta( $order->get_id(), '_wcfmmp_user_location', true );
			$lat     = get_post_meta( $order->get_id(), '_wcfmmp_user_location_lat', true );
			$lng     = get_post_meta( $order->get_id(), '_wcfmmp_user_location_lng', true );
			if( $address ) {
				$address = '<a href="https://google.com/maps/place/' . rawurlencode( $address ) . '/@' . $lat . ',' . $lng . '" target="_blank"><span>' . $address . '</span></a>';
				echo '<p class="wcfm_order_details_delivery_location"><i class="wcfmfa fa-map-marker" style="color:#20c997;"></i>&nbsp;&nbsp;<strong>'.__( 'Delivery Location', 'wc-multivendor-marketplace' ).':</strong> ' . $address . '</p>';
			}
		}
	}
	
	function wcfmmp_order_list_user_location_show( $shipping_address, $order_id ) {
		if( apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
			$address = get_post_meta( $order_id, '_wcfmmp_user_location', true );
			$lat     = get_post_meta( $order_id, '_wcfmmp_user_location_lat', true );
			$lng     = get_post_meta( $order_id, '_wcfmmp_user_location_lng', true );
			if( $address ) {
				$address = '<a href="https://google.com/maps/place/' . rawurlencode( $address ) . '/@' . $lat . ',' . $lng . '" target="_blank"><span>' . $address . '</span></a>';
				$shipping_address .= '<br /><p class="wcfm_order_list_delivery_location"><i class="wcfmfa fa-map-marker" style="color:#20c997;"></i>&nbsp;&nbsp;<strong>'.__( 'Location', 'wc-multivendor-marketplace' ).':</strong> ' . $address . '</p>';
			}
		}
		return $shipping_address;
	}
	
	/**
	 * WC Products short code "store" attribute support added
	 */
	function wcfmmp_shortcode_atts_products( $attributes, $pairs, $atts, $shortcode ) {
		if ( array_key_exists( 'store', $atts ) ) {
			$attributes['store'] = $atts['store'];
		}
		return $attributes;
	}
	
	/**
	 * WC Products short codde store filter
	 */
	function wcfmmp_woocommerce_shortcode_products_query( $query_args, $attributes, $type = 'products' ) {
		if( isset( $attributes['store'] ) ) {
			$store = absint( $attributes['store'] );
			if( $store )
				$query_args['author'] = $store;
		}
		return $query_args;
	}
	
	/**
	 * Store related product rule
	 */
	function wcfmmp_store_related_products( $query, $product_id ) {
		global $WCFM, $WCFMmp;
		
		$store_related_products   =  isset( $WCFMmp->wcfmmp_marketplace_options['store_related_products'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_related_products'] : 'default';
		if ( 'store' == $store_related_products ) {
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			if ( $vendor_id ) {
				$query['where'] .= ' AND p.post_author = ' . $vendor_id;
			}
		}
		return $query;
	}
	
	/**
	 * Store order related orders
	 */
	function wcfmmp_store_order_related_orders( $order_id ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !apply_filters( 'wcfm_is_allow_store_order_related_orders', true ) ) return; 
		
		if( wcfm_is_vendor() ) {
			$sql = 'SELECT ID FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
			$sql .= ' WHERE 1=1';
			$sql .= " AND `vendor_id` = {$WCFMmp->vendor_id}";
			$sql .= " AND `order_id` = {$order_id}";
			
			$commission_id = 0;
			$commissions = $wpdb->get_results( $sql );
			if( !empty( $commissions ) ) {
				foreach( $commissions as $commission ) {
					$commission_id = $commission->ID;
				}
			}
			
			if( $commission_id ) {
				$allowed_status      = get_wcfm_marketplace_active_withdrwal_order_status_in_comma();
				$allowed_status      = apply_filters( 'wcfmp_order_list_allowed_status', $allowed_status ); 
				
				$sql = 'SELECT order_id FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
				$sql .= ' WHERE 1=1';
				$sql .= " AND `vendor_id` = {$WCFMmp->vendor_id}";
				$sql .= " AND `order_id` != {$order_id}";
				if( apply_filters( 'wcfmmp_is_allow_order_status_filter', false ) ) {
					$sql .= " AND commission.order_status IN ({$allowed_status})";
				}
				$sql .= ' AND `is_trashed` = 0';
				
				$next_sql = $sql . " AND ID = (select min(ID) from {$wpdb->prefix}wcfm_marketplace_orders where ID > {$commission_id})";
				$next_orders = $wpdb->get_results( $next_sql );
				
				echo '<div class="wcfm-clearfix"></div><br />';
				if( !empty( $next_orders ) ) {
					foreach( $next_orders as $next_order ) {
						$next_order_id = $next_order->order_id;
						if( $next_order_id ) {
							echo '<a href="' . get_wcfm_view_order_url($next_order_id) . '" class="wcfm_submit_button wcfm_store_next_order">' . __( 'Next', 'wc-frontend-manager' ) . ' >></a>';
						}
					}
				}
				
				$pre_sql = $sql . " AND ID = (select max(ID) from {$wpdb->prefix}wcfm_marketplace_orders where ID < {$commission_id})";
				$pre_orders = $wpdb->get_results( $pre_sql );
				
				if( !empty( $pre_orders ) ) {
					foreach( $pre_orders as $pre_order ) {
						$pre_order_id = $pre_order->order_id;
						if( $pre_order_id ) {
							echo '<a href="' . get_wcfm_view_order_url($pre_order_id) . '" class="wcfm_submit_button wcfm_store_previous_order" style="float:left;"><< ' . __( 'Previous', 'wc-frontend-manager' ) . '</a>';
						}
					}
				}
				echo '<div class="wcfm-clearfix"></div><br />';
			}
		}
	}
	
	/**
	 * WC Registration Become Vendor link
	 */
	function wcfmmp_become_vendor_link() {
		global $WCFM, $WCFMmp;
		
		$hide_become_vendor = wcfm_get_option( 'wcfmvm_hide_become_vendor', 'no' );
		
		if( apply_filters( 'wcfm_is_allow_my_account_become_vendor', true ) && ( $hide_become_vendor !== 'yes' ) && WCFMmp_Dependencies::wcfmvm_plugin_active_check() ) {
			if( wcfm_is_allowed_membership() && !wcfm_has_membership() && !wcfm_is_vendor() ) {
				echo '<div class="wcfmmp_become_vendor_link">';
				$wcfm_memberships = get_wcfm_memberships();
				if( apply_filters( 'wcfm_is_pref_membership', true ) && !empty( $wcfm_memberships ) && apply_filters( 'wcfm_is_allow_my_account_membership_subscribe', true ) ) {
					echo '<a href="' . apply_filters( 'wcfm_change_membership_url', get_wcfm_membership_url() ) . '">' . apply_filters( 'wcfm_become_vendor_label', __( 'Become a Vendor', 'wc-multivendor-marketplace' ) ) . '</a>';
				} else {
					echo '<a href="' . get_wcfm_registration_page() . '">' . apply_filters( 'wcfm_become_vendor_label', __( 'Become a Vendor', 'wc-multivendor-marketplace' ) ) . '</a>';
				}
				echo '</div>';
			}
		}
	}
	
	/**
	 * WC My Account Dashboard Link
	 */
	function wcfm_dashboard_my_account_menu_items( $items ) {
		global $WCFM, $WCFMmp;
		
		if( wcfm_is_vendor() ) {
			$dashboard_page_title = __( 'Store Manager', 'wc-multivendor-marketplace' );
			$pages = get_option("wcfm_page_options");
			if( isset($pages['wc_frontend_manager_page_id']) && $pages['wc_frontend_manager_page_id'] ) {
				$dashboard_page_title = get_the_title( $pages['wc_frontend_manager_page_id'] );
			}
			$dashboard_page_title = apply_filters( 'wcfmmp_wcmy_dashboard_page_title', $dashboard_page_title ); 
			
			if( isset( $items['wcfm-store-manager'] ) ) unset( $items['wcfm-store-manager'] );
			
			$items = array_slice($items, 0, 1, true) +
																		array(
																					"wcfm-store-manager" => __( $dashboard_page_title, 'wc-multivendor-marketplace' )
																					) +
																		array_slice($items, 1, count($items) - 1, true) ;
		}
																	
		return $items;
	}
	
	function wcfm_dashboard_my_account_endpoint_redirect( $url, $endpoint, $value, $permalink ) {
		if( $endpoint == 'wcfm-store-manager')
      $url = get_wcfm_url();
    return $url;
	}
	
	/**
	 * Membership commission rules 
	 */
	function wcfmmp_membership_manager_fields_commission( $commission_fileds ) {
		global $WCFM, $WCFMmp, $wp;
		
		$membership_id = 0;
		if( isset( $wp->query_vars['wcfm-memberships-manage'] ) && !empty( $wp->query_vars['wcfm-memberships-manage'] ) ) {
			$membership_id = absint( $wp->query_vars['wcfm-memberships-manage'] );
		}
		
		// Commission
		$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
		$wcfm_commission_for = isset( $wcfm_commission_options['commission_for'] ) ? $wcfm_commission_options['commission_for'] : 'vendor';
		
		$wcfm_commission_types = get_wcfm_marketplace_commission_types();
		$wcfm_commission_types = array_merge( array( 'global' => __( 'By Global Rule', 'wc-multivendor-marketplace' ) ), $wcfm_commission_types );
		
		$vendor_commission_mode        = 'global';
		$vendor_commission_fixed       = '';
		$vendor_commission_percent     = 90;
		$vendor_commission_by_sales    = array();
		$vendor_commission_by_products = array();
		$vendor_commission_by_quantity = array();
		$vendor_get_shipping           = 'yes';
		$vendor_get_tax                = 'yes';
		$vendor_coupon_deduct          = 'yes';
		$admin_coupon_deduct           = 'yes';
		
		$tax_enable                    = 'no';     
		$tax_name                      = '';
		$tax_percent                   = '';
		
		if( $membership_id ) {
			$commission                    = (array) get_post_meta( $membership_id, 'commission', true );
			$vendor_commission_mode        = isset( $commission['commission_mode'] ) ? $commission['commission_mode'] : 'global';
			$vendor_commission_fixed       = isset( $commission['commission_fixed'] ) ? $commission['commission_fixed'] : '';
			$vendor_commission_percent     = isset( $commission['commission_percent'] ) ? $commission['commission_percent'] : '90';
			$vendor_commission_by_sales    = isset( $commission['commission_by_sales'] ) ? $commission['commission_by_sales'] : array();
			$vendor_commission_by_products = isset( $commission['commission_by_products'] ) ? $commission['commission_by_products'] : array();
			$vendor_commission_by_quantity = isset( $commission['commission_by_quantity'] ) ? $commission['commission_by_quantity'] : array();
			$vendor_get_shipping           = isset( $commission['get_shipping'] ) ? $commission['get_shipping'] : '';
			$vendor_get_tax                = isset( $commission['get_tax'] ) ? $commission['get_tax'] : '';
			$vendor_coupon_deduct          = isset( $commission['coupon_deduct'] ) ? $commission['coupon_deduct'] : '';
			$admin_coupon_deduct           = isset( $commission['admin_coupon_deduct'] ) ? $commission['admin_coupon_deduct'] : '';
			
			$tax_enable                    = isset( $commission['tax_enable'] ) ? 'yes' : 'no';
			$tax_name                      = isset( $commission['tax_name'] ) ? $commission['tax_name'] : '';
			$tax_percent                   = isset( $commission['tax_percent'] ) ? $commission['tax_percent'] : '';
		}
		
		$commission_fileds = apply_filters( 'wcfm_marketplace_settings_fields_membership_commission', array(
			                                                                            "wcfm_commission_for" => array('label' => __('Commission For', 'wc-multivendor-marketplace'), 'type' => 'select', 'options' => array( 'vendor' => __( 'Vendor', 'wc-multivendor-marketplace' ), 'admin' => __( 'Admin', 'wc-multivendor-marketplace' ) ), 'attributes' => array( 'disabled' => true, 'style' => 'border: 0px !important;font-weight:600;color:#17a2b8;' ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $wcfm_commission_for, 'hints' => __( 'Always applicable as per global rule.', 'wc-multivendor-marketplace' ) ),
					                                                                        "vendor_commission_mode" => array('label' => __('Commission Mode', 'wc-multivendor-marketplace'), 'name' => 'commission[commission_mode]', 'type' => 'select', 'options' => $wcfm_commission_types, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $vendor_commission_mode ),
					                                                                        "vendor_commission_percent" => array('label' => __('Commission Percent(%)', 'wc-multivendor-marketplace'), 'name' => 'commission[commission_percent]', 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'value' => $vendor_commission_percent, 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        "vendor_commission_fixed" => array('label' => __('Commission Fixed', 'wc-multivendor-marketplace') . '(' . get_woocommerce_currency_symbol() . ')', 'name' => 'commission[commission_fixed]', 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'value' => $vendor_commission_fixed, 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        "vendor_commission_by_sales" => array('label' => __('Commission By Sales Rule(s)', 'wc-multivendor-marketplace'), 'name' => 'commission[commission_by_sales]', 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_by_sales', 'label_class' => 'wcfm_title wcfm_ele wcfm_full_title commission_mode_field commission_mode_by_sales', 'desc_class' => 'commission_mode_field commission_mode_by_sales instructions', 'value' => $vendor_commission_by_sales, 'desc' => sprintf( __( 'Commission rules depending upon vendors total sales. e.g 50&#37; commission when sales < %s1000, 75&#37; commission when sales > %s1000 but < %s2000 and so on. You may define any number of such rules. Please be sure, do not set conflicting rules.', 'wc-multivendor-marketplace' ), get_woocommerce_currency_symbol(), get_woocommerce_currency_symbol(), get_woocommerce_currency_symbol() ),  'options' => array( 
					                                                                        																			"sales" => array('label' => __('Sales', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '1', 'step' => '0.1') ),
					                                                                        																			"rule" => array('label' => __('Rule', 'wc-multivendor-marketplace'), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'upto' => __( 'Up to', 'wc-multivendor-marketplace' ), 'greater'   => __( 'More than', 'wc-multivendor-marketplace' ) ) ),
					                                                                        																			"type" => array('label' => __('Commission Type', 'wc-multivendor-marketplace'), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'percent' => __( 'Percent', 'wc-multivendor-marketplace' ), 'fixed'   => __( 'Fixed', 'wc-multivendor-marketplace' ), 'percent_fixed' => __( 'Percent + Fixed', 'wc-multivendor-marketplace' ) ) ),
					                                                                        																			"commission" => array('label' => __('Commission Percent(%)', 'wc-multivendor-marketplace'), 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        																			"commission_fixed" => array('label' => __('Commission Fixed', 'wc-multivendor-marketplace') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        																			) ),
					                                                                        "vendor_commission_by_products" => array('label' => __('Commission By Product Price', 'wc-multivendor-marketplace'), 'name' => 'commission[commission_by_products]', 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_by_products', 'label_class' => 'wcfm_title wcfm_ele wcfm_full_title commission_mode_field commission_mode_by_products', 'desc_class' => 'commission_mode_field commission_mode_by_products instructions', 'value' => $vendor_commission_by_products, 'desc' => sprintf( __( 'Commission rules depending upon product price. e.g 80&#37; commission when product cost < %s1000, %s100 fixed commission when product cost > %s1000 and so on. You may define any number of such rules. Please be sure, do not set conflicting rules.', 'wc-multivendor-marketplace' ), get_woocommerce_currency_symbol(), get_woocommerce_currency_symbol(), get_woocommerce_currency_symbol() ),  'options' => array( 
					                                                                        																			"cost" => array('label' => __('Product Cost', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        																			"rule" => array('label' => __('Rule', 'wc-multivendor-marketplace'), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'upto' => __( 'Up to', 'wc-multivendor-marketplace' ), 'greater'   => __( 'More than', 'wc-multivendor-marketplace' ) ) ),
					                                                                        																			"type" => array('label' => __('Commission Type', 'wc-multivendor-marketplace'), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'percent' => __( 'Percent', 'wc-multivendor-marketplace' ), 'fixed'   => __( 'Fixed', 'wc-multivendor-marketplace' ), 'percent_fixed' => __( 'Percent + Fixed', 'wc-multivendor-marketplace' ) ) ),
					                                                                        																			"commission" => array('label' => __('Commission Percent(%)', 'wc-multivendor-marketplace'), 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        																			"commission_fixed" => array('label' => __('Commission Fixed', 'wc-multivendor-marketplace') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        																			) ),
					                                                                        "vendor_commission_by_quantity" => array('label' => __('Commission By Purchase Quantity', 'wc-multivendor-marketplace'), 'name' => 'commission[commission_by_quantity]', 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_by_quantity', 'label_class' => 'wcfm_title wcfm_ele wcfm_full_title commission_mode_field commission_mode_by_quantity', 'desc_class' => 'commission_mode_field commission_mode_by_quantity instructions', 'value' => $vendor_commission_by_quantity, 'desc' => __( 'Commission rules depending upon purchased product quantity. e.g 80&#37; commission when purchase quantity 2, 80&#37; commission when purchase quantity > 2 and so on. You may define any number of such rules. Please be sure, do not set conflicting rules.', 'wc-multivendor-marketplace' ),  'options' => array( 
					                                                                        																			"quantity" => array('label' => __('Purchase Quantity', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '1', 'step' => '1') ),
					                                                                        																			"rule" => array('label' => __('Rule', 'wc-multivendor-marketplace'), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'upto' => __( 'Up to', 'wc-multivendor-marketplace' ), 'greater'   => __( 'More than', 'wc-multivendor-marketplace' ) ) ),
					                                                                        																			"type" => array('label' => __('Commission Type', 'wc-multivendor-marketplace'), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'options' => array( 'percent' => __( 'Percent', 'wc-multivendor-marketplace' ), 'fixed'   => __( 'Fixed', 'wc-multivendor-marketplace' ), 'percent_fixed' => __( 'Percent + Fixed', 'wc-multivendor-marketplace' ) ) ),
					                                                                        																			"commission" => array('label' => __('Commission Percent(%)', 'wc-multivendor-marketplace'), 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        																			"commission_fixed" => array('label' => __('Commission Fixed', 'wc-multivendor-marketplace') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'placeholder' => 0, 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        																			) ),
																																									"vendor_get_shipping" => array('label' => __('Shipping cost goes to vendor?', 'wc-multivendor-marketplace'), 'name' => 'commission[get_shipping]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $vendor_get_shipping ),
																																									"vendor_get_tax" => array('label' => __('Tax goes to vendor?', 'wc-multivendor-marketplace'), 'name' => 'commission[get_tax]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $vendor_get_tax ),
																																									"vendor_coupon_deduct" => array('label' => __('Commission after consider Vendor Coupon?', 'wc-multivendor-marketplace'), 'name' => 'commission[coupon_deduct]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $vendor_coupon_deduct, 'hints' => __( 'Generate vendor commission after deduct Vendor Coupon discounts.', 'wc-multivendor-marketplace' ) ),
																																									"admin_coupon_deduct" => array('label' => __('Commission after consider Admin Coupon?', 'wc-multivendor-marketplace'), 'name' => 'commission[admin_coupon_deduct]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $admin_coupon_deduct, 'hints' => __( 'Generate vendor commission after deduct Admin Coupon discounts.', 'wc-multivendor-marketplace' ) ),
																																									), $membership_id );
		
		
		$commission_tax_fileds = apply_filters( 'wcfm_marketplace_settings_fields_membership_commission_tax', array(  
			                                                                'tax_fields_heading' => array( 'type' => 'html', 'class' => 'commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => '<h2>' . __('Commission Tax Settings', 'wc-multivendor-marketplace') . '</h2><div class="wcfm_clearfix"></div>' ), 
																																			'tax_enable' => array( 'label' => __( 'Enable', 'wc-multivendor-marketplace' ), 'type' => 'checkbox', 'name' => 'commission[tax_enable]', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $tax_enable ),
																																			'tax_name' => array( 'label' => __( 'Tax Label', 'wc-multivendor-marketplace' ), 'placeholder' => __( 'Tax', 'wc-multivendor-marketplace' ), 'type' => 'text', 'name' => 'commission[tax_name]', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_name ),
																																			'tax_percent' => array( 'label' => __( 'Tax Percent (%)', 'wc-multivendor-marketplace' ), 'type' => 'number', 'name' => 'commission[tax_percent]', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_percent ),
																																			), $membership_id );
		
		$commission_fileds = array_merge( $commission_fileds, $commission_tax_fileds );
		
		return $commission_fileds;
	}
	
	/**
	 * WooCommerce Product List GEO Location Filter
	 */
	function wcfmmp_product_list_geo_location_filter() {
		global $WCFM, $WCFMmp, $wpdb, $wcfmmp_radius_lat, $wcfmmp_radius_lng, $wcfmmp_radius_range;
		
		if( wcfmmp_is_store_page() )
			return;
		
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}
		
		if( !apply_filters( 'wcfm_is_allow_product_list_geo_location_filter', true ) ) return;
        
		$wcfm_google_map_api           = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $wcfm_google_map_api ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$wcfm_google_map_api ) { $wcfm_map_lib = 'leaftlet'; }
		if( ($wcfm_map_lib == 'google') && empty( $wcfm_google_map_api ) ) return;
		
		$enable_wcfm_product_radius    = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] : 'no';
		if( $enable_wcfm_product_radius !== 'yes' ) return;
		
		$max_radius_to_search = isset( $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] ) ? $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] : '100';
		
		$radius_addr = isset( $_GET['radius_addr'] ) ? wc_clean( $_GET['radius_addr'] ) : '';
		$radius_range = isset( $_GET['radius_range'] ) ? wc_clean( $_GET['radius_range'] ) : (absint(apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ))/10);
		$radius_lat = isset( $_GET['radius_lat'] ) ? wc_clean( $_GET['radius_lat'] ) : '';
		$radius_lng = isset( $_GET['radius_lng'] ) ? wc_clean( $_GET['radius_lng'] ) : '';
		
		$available_vendors = array();
		if( ( !empty( $radius_lat ) && !empty( $radius_lng ) && !empty( $radius_range ) ) || is_product_taxonomy() ) {
			$wcfmmp_radius_lat = $radius_lat;
			$wcfmmp_radius_lng = $radius_lng;
			$wcfmmp_radius_range = $radius_range;
			
			$user_args = array(
				'role__in'     => apply_filters( 'wcfmmp_allwoed_vendor_user_roles', array( 'wcfm_vendor' ) ),
				'count_total'  => false,
				'fields'       => array( 'ID', 'display_name' ),
			 ); 
			
			// For Taxonomy Page
			if( is_product_taxonomy() ) {
				$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); 
				$term_id = $term->term_id;
				$wcfm_allow_vendors_list = array();
				$category_vendors = $wpdb->get_results( "SELECT vendor_id FROM {$wpdb->prefix}wcfm_marketplace_store_taxonomies WHERE term = " . absint($term_id) );
				if( !empty( $category_vendors ) ) {
					foreach( $category_vendors as $category_vendor ) {
						$wcfm_allow_vendors_list[] = $category_vendor->vendor_id;
					}
				}
				if( empty( $wcfm_allow_vendors_list ) ) {
					$wcfm_allow_vendors_list = array( 0 => -1 );
				}
				$user_args['include'] = array_filter($wcfm_allow_vendors_list);
			}
			
			$all_users = get_users( $user_args );
			
			if( !empty( $all_users ) ) {
				foreach( $all_users as $all_user ) {
					$available_vendors[$all_user->ID] = $all_user->ID;
				}
				if( isset( $_REQUEST['filter_vendor'] ) && !empty( $_REQUEST['filter_vendor'] ) ) {
					$filter_vendor = absint( $_REQUEST['filter_vendor'] );
					if( in_array( $filter_vendor, $available_vendors ) ) {
						$available_vendors = array();
						$available_vendors[$filter_vendor] = $filter_vendor;
					}
				}
			} else {
				$available_vendors = array(-1);
			}
		} else {
			if( isset( $_REQUEST['filter_vendor'] ) && !empty( $_REQUEST['filter_vendor'] ) ) {
				$filter_vendor = absint( $_REQUEST['filter_vendor'] );
				$available_vendors[$filter_vendor] = $filter_vendor;
			} else {
				
			}
		}
		
		$args = apply_filters( 'wcfmmp_product_list_geolocate_map_defalt', array(
																'map_zoom'           => apply_filters( 'wcfmmp_map_default_zoom_level', 5 ),
																'auto_zoom'          => wc_string_to_bool( apply_filters( 'wcfmmp_is_allow_map_auto_zoom', 'yes' ) ),
																'includes'           => $available_vendors
																) );
		
		echo '<div class="wcfmmp-product-geolocate-wrapper">';
		$WCFMmp->template->get_template( 'product-geolocate/wcfmmp-view-product-lists-map.php', $args );
		$WCFMmp->template->get_template( 'product-geolocate/wcfmmp-view-product-lists-search-form.php', $args );
		echo '<div class="wcfm-clearfix"></div></div>';
	}
	
	/**
	 * WooCommerce Product List GEO Location Filter Post Clause
	 */
	function wcfmmp_product_list_geo_location_filter_post_clauses( $args, $wp_query ) {
		global $WCFM, $WCFMmp, $wpdb, $wcfmmp_radius_lat, $wcfmmp_radius_lng, $wcfmmp_radius_range;
		
		// Filter by Vendor
		if ( $wp_query->is_main_query() && isset( $_GET['filter_vendor'] ) && !empty( $_GET['filter_vendor'] ) ) {
			$args['where'] .= " AND $wpdb->posts.post_author = ". absint( $_GET['filter_vendor'] );
		}
		
		$wcfm_google_map_api           = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $wcfm_google_map_api ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$wcfm_google_map_api ) { $wcfm_map_lib = 'leaftlet'; }
		if( ($wcfm_map_lib == 'google') && empty( $wcfm_google_map_api ) ) return $args;
		
		$enable_wcfm_product_radius    = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] : 'no';
		if( $enable_wcfm_product_radius !== 'yes' ) return $args;
		
		if ( ! $wp_query->is_main_query() || ( ! isset( $_GET['radius_range'] ) && ! isset( $_GET['radius_lat'] ) && ! isset( $_GET['radius_lng'] ) ) ) {
			return $args;
		}
		
		$max_radius_to_search = isset( $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] ) ? $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] : '100';
		
		$radius_addr = isset( $_GET['radius_addr'] ) ? wc_clean( $_GET['radius_addr'] ) : '';
		$radius_range = isset( $_GET['radius_range'] ) ? wc_clean( $_GET['radius_range'] ) : (absint(apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ))/10);
		$radius_lat = isset( $_GET['radius_lat'] ) ? wc_clean( $_GET['radius_lat'] ) : '';
		$radius_lng = isset( $_GET['radius_lng'] ) ? wc_clean( $_GET['radius_lng'] ) : '';
		
		if( !empty( $radius_lat ) && !empty( $radius_lng ) && !empty( $radius_range ) ) {
			$wcfmmp_radius_lat = $radius_lat;
			$wcfmmp_radius_lng = $radius_lng;
			$wcfmmp_radius_range = $radius_range;
			
			$user_args = array(
				'role__in'     => apply_filters( 'wcfmmp_allwoed_vendor_user_roles', array( 'wcfm_vendor' ) ),
				'count_total'  => false,
				'fields'       => array( 'ID', 'display_name' ),
			 ); 
			$all_users = get_users( $user_args );
			
			$available_vendors = array();
			if( !empty( $all_users ) ) {
				foreach( $all_users as $all_user ) {
					$available_vendors[$all_user->ID] = $all_user->ID;
				}
			} else {
				$available_vendors = array(0);
			}
			
			$args['where'] .= " AND $wpdb->posts.post_author in (". implode( ',', $available_vendors ).")";
		}
		
		return $args;
	}
	
	/**
	 * WCFM GEO Locate
	 */
	function wcfmmp_is_allow_geo_locate( $is_allow ) {
		global $WCFMmp;
		$enable_wcfm_geo_locate = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_geo_locate'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_geo_locate'] : 'no';
		if( $enable_wcfm_geo_locate == 'yes' ) return true;
		return false;
	}
	
	/**
	 * WCFM Default Store Logo
	 */
	function wcfmmp_store_default_logo( $default_logo ) {
		global $WCFMmp;
		$default_logo = !empty( $WCFMmp->wcfmmp_marketplace_options['store_default_logo'] ) ? wcfm_get_attachment_url($WCFMmp->wcfmmp_marketplace_options['store_default_logo']) : $default_logo;
		return $default_logo;
	}
	
	/**
	 * WCFM Store Page Body Class
	 */
	function wcfm_store_body_classes($classes) {
		if( wcfm_is_store_page() ) {
			$classes[] = 'wcfm-store-page';
			$classes[] = 'wcfmmp-store-page';
			$classes[] = 'tax-dc_vendor_shop';
			
			// Martfury Compatibility
			if ( function_exists( 'martfury_is_vendor_page' ) && martfury_is_vendor_page() ) {
				$shop_view = isset( $_COOKIE['shop_view'] ) ? $_COOKIE['shop_view'] : martfury_get_option( 'catalog_view_12' );
				$classes[] = 'shop-view-' . $shop_view;
			}
		
		} elseif( wcfmmp_is_stores_list_page() ) {
			$classes[] = 'wcfm-store-list-page';
			$classes[] = 'wcfmmp-store-list-page';
		}
		
		$classes[] = 'wcfm-theme-' . sanitize_title( str_replace( 'child', '', strtolower( wp_get_theme() ) ) );
		
		return $classes;
	}
	
	/**
	 * WCFM Store Page Title
	 */
	function wcfm_store_page_title( $title ) {
		global $WCFM, $WCFM_Query, $wp_query;
		if( ! is_null( $wp_query ) && !is_admin() && is_main_query() && in_the_loop() && wcfmmp_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
				$title = get_user_meta( $store_user->ID, 'wcfmmp_store_name', true );
				if( !$title ) $title = $store_user->display_name;
			}
		}
		return $title;
	}
	
	/**
	 * WCFMmp Store JS
	 */
	function wcfmmp_scripts() {
 		global $WCFM, $WCFMmp, $wp, $WCFM_Query;
 		
 		if( isset( $_REQUEST['fl_builder'] ) ) return;
 		
 		$wcfm_google_map_api           = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $wcfm_google_map_api ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$wcfm_google_map_api ) { $wcfm_map_lib = 'leaftlet'; }
		
		// Default Map Location
		$default_geolocation = isset( $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] ) ? $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] : array();
		$store_location      = isset( $default_geolocation['location'] ) ? esc_attr( $default_geolocation['location'] ) : '';
		$map_address         = isset( $default_geolocation['address'] ) ? esc_attr( $default_geolocation['address'] ) : '';
		$default_lat         = isset( $default_geolocation['lat'] ) ? esc_attr( $default_geolocation['lat'] ) : apply_filters( 'wcfmmp_map_default_lat', 30.0599153 );
		$default_lng         = isset( $default_geolocation['lng'] ) ? esc_attr( $default_geolocation['lng'] ) : apply_filters( 'wcfmmp_map_default_lng', 31.2620199 );
		$default_zoom        =  apply_filters( 'wcfmmp_map_default_zoom_level', 17 );
		$store_icon          = apply_filters( 'wcfmmp_map_store_icon', $WCFMmp->plugin_url . 'assets/images/wcfmmp_map_icon.png', 0, '' );
		
		$max_radius_to_search = isset( $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] ) ? $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] : '100';
		$radius_unit          = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';
 		
 		if( wcfmmp_is_store_page() ) {
 			$WCFM->library->load_blockui_lib();
 			$WCFM->library->load_datepicker_lib();
 			
			// Store JS
			wp_enqueue_script( 'wcfmmp_store_js', $WCFMmp->library->js_lib_url_min . 'store/wcfmmp-script-store.js', array('jquery' ), $WCFMmp->version, true );
			
			$WCFMmp->library->load_map_lib();
			
			$wcfm_reviews_messages = get_wcfm_reviews_messages();
			wp_localize_script( 'wcfmmp_store_js', 'wcfm_reviews_messages', $wcfm_reviews_messages );
			
			wp_localize_script( 'wcfmmp_store_js', 'wcfm_slider_banner_delay', array( "delay" => apply_filters( 'wcfmmp_slider_banner_delay', 4000 ) ) );
			
			wp_localize_script( 'wcfmmp_store_js', 'wcfmmp_store_map_options', array( 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'store_icon' => $store_icon, 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_poi' => apply_filters( 'wcfmmp_is_allow_map_poi', true ), 'is_allow_scroll_zoom' => apply_filters( 'wcfmmp_is_allow_map_scroll_zoom', true ), 'is_rtl' => is_rtl() ) );
		}
		
		if( wcfmmp_is_stores_list_page() || wcfmmp_is_stores_map_page() || is_singular( 'wcfm_vendor_groups' ) ) {
			$WCFM->library->load_select2_lib();
			wp_enqueue_script( 'wc-country-select' );
			
			$WCFMmp->library->load_map_lib();
			
			$enable_store_radius  = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_storelist_radius'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_storelist_radius'] : 'no';
			
			wp_enqueue_script( 'wcfmmp_store_list_js', $WCFMmp->library->js_lib_url_min . 'store-lists/wcfmmp-script-store-lists.js', array('jquery' ), $WCFMmp->version, true );
			
			wp_localize_script( 'wcfmmp_store_list_js', 'wcfmmp_store_list_messages', array( 'choose_category' => __( 'Choose Category', 'wc-multivendor-marketplace' ), 'choose_location' => __( 'Choose Location', 'wc-multivendor-marketplace' ), 'choose_state' => __( 'Choose State', 'wc-multivendor-marketplace' ) ) );
			wp_localize_script( 'wcfmmp_store_list_js', 'wcfmmp_store_list_options', array( 'search_location' => __( 'Insert your address ..', 'wc-multivendor-marketplace' ), 'is_geolocate' => apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ), 'max_radius' => apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ), 'radius_unit' => ucfirst( $radius_unit ), 'start_radius' => apply_filters( 'wcfmmp_radius_filter_start_distance', 10 ), 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_poi' => apply_filters( 'wcfmmp_is_allow_map_poi', true ), 'is_allow_scroll_zoom' => apply_filters( 'wcfmmp_is_allow_map_scroll_zoom', true ), 'is_cluster' => apply_filters( 'wcfmmp_is_allow_map_pointer_cluster', true ), 'cluster_image' => apply_filters( 'wcfmmp_is_cluster_image', 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m' ), 'is_rtl' => is_rtl() ) );
		}
		
		// Product List Geo Locate Filter 
		if ( is_shop() || is_product_taxonomy() ) {
			$enable_wcfm_product_radius    = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] : 'no';
			
			if( ( ( ($wcfm_map_lib == 'google') && !empty( $wcfm_google_map_api ) ) || ($wcfm_map_lib == 'leaflet') ) && ( $enable_wcfm_product_radius !== 'no' ) ) {
				$WCFMmp->library->load_map_lib();
				
				wp_enqueue_script( 'wcfmmp_product_list_js', $WCFMmp->library->js_lib_url_min . 'product-geolocate/wcfmmp-script-product-lists.js', array('jquery' ), $WCFMmp->version, true );
				
				wp_localize_script( 'wcfmmp_product_list_js', 'wcfmmp_product_list_options', array( 'search_location' => __( 'Insert your address ..', 'wc-multivendor-marketplace' ), 'is_geolocate' => apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ), 'max_radius' => apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ), 'radius_unit' => ucfirst( $radius_unit ), 'start_radius' => apply_filters( 'wcfmmp_radius_filter_start_distance', 10 ), 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_poi' => apply_filters( 'wcfmmp_is_allow_map_poi', true ), 'is_allow_scroll_zoom' => apply_filters( 'wcfmmp_is_allow_map_scroll_zoom', true ), 'is_cluster' => apply_filters( 'wcfmmp_is_allow_map_pointer_cluster', true ), 'cluster_image' => apply_filters( 'wcfmmp_is_cluster_image', 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m' ), 'is_rtl' => is_rtl() ) );
			}
			
			if( apply_filters( 'wcfm_is_allow_products_filter_by_vendor_choosen', true ) ) {
				$WCFM->library->load_select2_lib();
			}
 	  }
		
		if( apply_filters( 'wcfm_is_allow_store_shipping_countries', false ) ) {
      if(is_cart()) {
        wp_enqueue_script( 'wcfmmp_cart_js', $WCFMmp->library->js_lib_url_min . 'cart/wcfmmp-script-cart.js', array('jquery' ), $WCFMmp->version, true );
      }
      if(is_checkout()) {
        wp_enqueue_script( 'wcfmmp_checkout_js', $WCFMmp->library->js_lib_url_min . 'checkout/wcfmmp-script-checkout.js', array('jquery' ), $WCFMmp->version, true );
      }
    }
    
    if( is_checkout() && apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
    	$WCFMmp->library->load_map_lib();
    	wp_enqueue_script( 'wcfmmp_checkout_location_js', $WCFMmp->library->js_lib_url_min . 'checkout/wcfmmp-script-checkout-location.js', array('jquery' ), $WCFMmp->version, true );
    	
    	wp_localize_script( 'wcfmmp_checkout_location_js', 'wcfmmp_checkout_map_options', array( 'search_location' => __( 'Insert your address ..', 'wc-multivendor-marketplace' ), 'locate_svg' => $WCFMmp->plugin_url. 'assets/images/locate.svg', 'is_geolocate' => apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ), 'max_radius' => apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ), 'radius_unit' => ucfirst( $radius_unit ), 'start_radius' => apply_filters( 'wcfmmp_radius_filter_start_distance', 10 ), 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'store_icon' => $store_icon, 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_poi' => apply_filters( 'wcfmmp_is_allow_map_poi', true ), 'is_allow_scroll_zoom' => apply_filters( 'wcfmmp_is_allow_map_scroll_zoom', true ), 'is_cluster' => apply_filters( 'wcfmmp_is_allow_map_pointer_cluster', true ), 'cluster_image' => apply_filters( 'wcfmmp_is_cluster_image', 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m' ), 'is_rtl' => is_rtl() ) );
    }
 	}
 	
 	/**
 	 * WCFMmp Core CSS
 	 */
 	function wcfmmp_styles() {
 		global $WCFM, $WCFMmp, $wp, $WCFM_Query;
 		
 		if( isset( $_REQUEST['fl_builder'] ) ) return;
 		
 		if( is_product() || ( apply_filters( 'wcfmmp_is_allow_archive_sold_by_advanced', false ) && ( is_shop() || is_product_category() ) ) ) {
			wp_enqueue_style( 'wcfmmp_product_css',  $WCFMmp->library->css_lib_url_min . 'store/wcfmmp-style-product.css', array(), $WCFMmp->version );
		}
 		
 		if( wcfmmp_is_store_page() ) {
			// Store CSS
			if( apply_filters( 'wcfmmp_is_allow_legacy_header', false ) ) {
				wp_enqueue_style( 'wcfmmp_store_css',  $WCFMmp->library->css_lib_url_min . 'store/legacy/wcfmmp-style-store.css', array(), $WCFMmp->version );
			} else {
				wp_enqueue_style( 'wcfmmp_store_css',  $WCFMmp->library->css_lib_url_min . 'store/wcfmmp-style-store.css', array(), $WCFMmp->version );
			}

			// RTL CSS
      if( is_rtl() ) {
      	if( apply_filters( 'wcfmmp_is_allow_legacy_header', false ) ) {
         wp_enqueue_style( 'wcfmmp_store_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store/legacy/wcfmmp-style-store-rtl.css', array('wcfmmp_store_css'), $WCFMmp->version );
        } else {
        	wp_enqueue_style( 'wcfmmp_store_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store/wcfmmp-style-store-rtl.css', array('wcfmmp_store_css'), $WCFMmp->version );
        }
      }
			
			// Store Responsive CSS
			if( apply_filters( 'wcfmmp_is_allow_legacy_header', false ) ) {
			 wp_enqueue_style( 'wcfmmp_store_responsive_css',  $WCFMmp->library->css_lib_url_min . 'store/legacy/wcfmmp-style-store-responsive.css', array(), $WCFMmp->version );
			} else {
				wp_enqueue_style( 'wcfmmp_store_responsive_css',  $WCFMmp->library->css_lib_url_min . 'store/wcfmmp-style-store-responsive.css', array(), $WCFMmp->version );
			}
		}
		
		if( wcfmmp_is_stores_list_page() || is_singular( 'wcfm_vendor_groups' ) || wcfmmp_is_stores_map_page() ) {
			wp_enqueue_style( 'wcfmmp_store_list_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list.css', array(), $WCFMmp->version );
			
			if( is_rtl() ) {
        wp_enqueue_style( 'wcfmmp_store_list_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-rtl.css', array('wcfmmp_store_list_css'), $WCFMmp->version );
      }
		}
		
		// Product List Geo Locate Filter 
		if ( is_shop() || is_product_taxonomy() ) {
			$wcfm_google_map_api           = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
			$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
			if( !$wcfm_map_lib && $wcfm_google_map_api ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$wcfm_google_map_api ) { $wcfm_map_lib = 'leaftlet'; }
			$enable_wcfm_product_radius    = isset( $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] ) ? $WCFMmp->wcfmmp_marketplace_options['enable_wcfm_product_radius'] : 'no';
			if( ( ( ($wcfm_map_lib == 'google') && !empty( $wcfm_google_map_api ) ) || ($wcfm_map_lib == 'leaflet') ) && ( $enable_wcfm_product_radius !== 'no' ) ) {
				wp_enqueue_style( 'wcfmmp_store_list_css',  $WCFMmp->library->css_lib_url_min . 'product-geolocate/wcfmmp-style-product-list.css', array(), $WCFMmp->version );
				
				if( is_rtl() ) {
					wp_enqueue_style( 'wcfmmp_store_list_rtl_css',  $WCFMmp->library->css_lib_url_min . 'product-geolocate/wcfmmp-style-product-list-rtl.css', array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
				}
			}
 	  }
		
		if( wcfmmp_is_store_page() || wcfmmp_is_stores_list_page() || is_singular( 'wcfm_vendor_groups' ) ) {
			$upload_dir      = wp_upload_dir();
			
			// WCFMmp Custom CSS
			$wcfmmp_style_custom = get_option( 'wcfmmp_style_custom' );
			if( $wcfmmp_style_custom && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmmp_style_custom ) ) {
				if( wcfmmp_is_store_page() ) {
					wp_enqueue_style( 'wcfmmp_style_custom',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmmp_style_custom, array( 'wcfmmp_store_css' ), $WCFMmp->version );
				}
				
				if( wcfmmp_is_stores_list_page() || is_singular( 'wcfm_vendor_groups' ) ) {
					wp_enqueue_style( 'wcfmmp_store_list_style_custom',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmmp_style_custom, array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
				}
			}
	  }
	  
	  if( is_checkout() && apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
	  	wp_enqueue_style( 'wcfmmp_checkout_location_css',  $WCFMmp->library->css_lib_url . 'checkout/wcfmmp-style-checkout-location.css', array(), $WCFMmp->version );
	  }
	}
	
}