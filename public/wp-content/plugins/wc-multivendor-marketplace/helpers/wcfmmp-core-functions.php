<?php
if(!function_exists('wcfmmp_woocommerce_inactive_notice')) {
	function wcfmmp_woocommerce_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWCFM Marketplace is inactive.%s The %sWooCommerce plugin%s must be active for the WCFM Marketplace to work. Please %sinstall & activate WooCommerce%s', 'wc-multivendor-marketplace' ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmp_wcfm_inactive_notice')) {
	function wcfmmp_wcfm_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWCFM Marketplace is inactive.%s The %sWooCommerce Frontend Manager%s must be active for the WCFM Marketplace to work. Please %sinstall & activate WooCommerce Frontend Manager%s', 'wc-multivendor-marketplace' ), '<strong>', '</strong>', '<a target="_blank" href="https://wordpress.org/plugins/wc-frontend-manager/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=wc+frontend+manager' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmp_stripe_phpversion_notice')) {
	function wcfmmp_stripe_phpversion_notice() {
		?>
		<div id="message" class="error">
			<p><?php printf(__("%WCFM Marketplace - Stripe Gateway%s requires PHP 5.6 or greater. We recommend upgrading to PHP %s or greater.", 'wc-multivendor-marketplace' ), '<strong>', '</strong>', '5.6' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmp_stripe_curl_notice')) {
	function wcfmmp_stripe_curl_notice() {
		?>
		<div id="message" class="error">
			<p><?php printf(__("%WCFM Marketplace - Stripe Gateway depends on the %s PHP extension. Please enable it, or ask your hosting provider to enable it.", 'wc-multivendor-marketplace' ), '<strong>', '</strong>', 'curl' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmp_stripe_mbstring_notice')) {
	function wcfmmp_stripe_mbstring_notice() {
		?>
		<div id="message" class="error">
			<p><?php printf(__("%WCFM Marketplace - Stripe Gateway depends on the %s PHP extension. Please enable it, or ask your hosting provider to enable it.", 'wc-multivendor-marketplace' ), '<strong>', '</strong>', 'mbstring' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmp_stripe_json_notice')) {
	function wcfmmp_stripe_json_notice() {
		?>
		<div id="message" class="error">
			<p><?php printf(__("%WCFM Marketplace - Stripe Gateway depends on the %s PHP extension. Please enable it, or ask your hosting provider to enable it.", 'wc-multivendor-marketplace' ), '<strong>', '</strong>', 'json' ); ?></p>
		</div>
		<?php
	}
}

if( !function_exists( 'wcfmmp_has_marketplace' ) ) {
	function wcfmmp_has_marketplace() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
		
		$has_marketplace = false;
		
		// WC Vendors Check
		if( !$has_marketplace )
		  $has_marketplace = ( in_array( 'wc-vendors/class-wc-vendors.php', $active_plugins ) || array_key_exists( 'wc-vendors/class-wc-vendors.php', $active_plugins ) || class_exists( 'WC_Vendors' ) ) ? 'WC Vendors' : false;
		
		// WC Marketplace Check
		if( !$has_marketplace )
			$has_marketplace = ( in_array( 'dc-woocommerce-multi-vendor/dc_product_vendor.php', $active_plugins ) || array_key_exists( 'dc-woocommerce-multi-vendor/dc_product_vendor.php', $active_plugins ) || class_exists( 'WCMp' ) ) ? 'WC Marketplace' : false;
		
		// WC Product Vendors Check
		if( !$has_marketplace )
			$has_marketplace = ( in_array( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins ) || array_key_exists( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins ) ) ? 'WC Product Vendors' : false;
		
		// Dokan Lite Check
		if( !$has_marketplace )
			$has_marketplace = ( in_array( 'dokan-lite/dokan.php', $active_plugins ) || array_key_exists( 'dokan-lite/dokan.php', $active_plugins ) || class_exists( 'WeDevs_Dokan' ) ) ? 'Dokan Multivendor' : false;
		
		return $has_marketplace;
	}
}

if(!function_exists('wcfm_refund_requests_url')) {
	function wcfm_refund_requests_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_refund_requests_url = wcfm_get_endpoint_url( 'wcfm-refund-requests', '', $wcfm_page );
		return apply_filters( 'wcfm_refund_requests_url', $wcfm_refund_requests_url );
	}
}

if(!function_exists('wcfm_reviews_url')) {
	function wcfm_reviews_url( $reviews_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_reviews_url = wcfm_get_endpoint_url( 'wcfm-reviews', '', $wcfm_page );
		if( $reviews_status ) $get_wcfm_reviews_url = add_query_arg( 'reviews_status', $reviews_status, $get_wcfm_reviews_url );
		return apply_filters( 'wcfm_reviews_url', $get_wcfm_reviews_url );
	}
}

if(!function_exists('wcfm_product_reviews_url')) {
	function wcfm_product_reviews_url( $reviews_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_reviews_url = wcfm_get_endpoint_url( 'wcfm-product-reviews', '', $wcfm_page );
		if( $reviews_status ) $get_wcfm_reviews_url = add_query_arg( 'reviews_status', $reviews_status, $get_wcfm_reviews_url );
		return apply_filters( 'wcfm_product_reviews_url', $get_wcfm_reviews_url );
	}
}

if(!function_exists('wcfm_reviews_manage_url')) {
	function wcfm_reviews_manage_url( $review_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_reviews_manage_url = wcfm_get_endpoint_url( 'wcfm-reviews-manage', $review_id, $wcfm_page );
		return apply_filters( 'wcfm_reviews_manage_url', $get_wcfm_reviews_manage_url );
	}
}

if(!function_exists('wcfm_ledger_url')) {
	function wcfm_ledger_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_ledger_url = wcfm_get_endpoint_url( 'wcfm-ledger', '', $wcfm_page );
		return apply_filters( 'wcfm_ledger_url', $get_wcfm_ledger_url );
	}
}

if(!function_exists('wcfm_media_url')) {
	function wcfm_media_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_media_url = wcfm_get_endpoint_url( 'wcfm-media', '', $wcfm_page );
		return apply_filters( 'wcfm_media_url', $get_wcfm_media_url );
	}
}

if(!function_exists('wcfm_sell_items_catalog_url')) {
	function wcfm_sell_items_catalog_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_sell_items_catalog_url = wcfm_get_endpoint_url( 'wcfm-sell-items-catalog', '', $wcfm_page );
		return apply_filters( 'wcfm_sell_items_catalog_url', $wcfm_sell_items_catalog_url );
	}
}

/**
 * Check if it's a store page
 *
 * @return boolean
 */
function wcfm_is_store_page() {
	global $wp_query;
	if( $wp_query ) {
		if( function_exists( 'wcfm_get_option' ) ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
		} else {
			$wcfm_store_url = get_option( 'wcfm_store_url', 'store' );
		}
		if ( get_query_var( $wcfm_store_url ) ) {
			return true;
		}
	}
	return false;
}

function wcfmmp_is_store_page() {
	return wcfm_is_store_page();
}

function wcfmmp_is_stores_list_page() {
	return wc_post_content_has_shortcode( 'wcfm_stores' );
}

function wcfmmp_is_stores_map_page() {
	return wc_post_content_has_shortcode( 'wcfm_stores_map' );
}

function woocommerce_dequeue_sidebar() {
	if( wcfm_is_store_page() ) 
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
add_action( 'woocommerce_sidebar', 'woocommerce_dequeue_sidebar', 9 );

add_filter( 'is_woocommerce', function( $is_woocommerce ) {
	if( apply_filters( 'wcfmmp_is_allow_store_page_as_woocommerce_page', true ) ) {
		if( wcfm_is_store_page() ) $is_woocommerce = true;
	}
	return $is_woocommerce;
});

/**
 * Get store page url of a seller
 *
 * @param int $user_id
 * @return string
 */
function wcfmmp_get_store_url( $user_id ) {
	$userdata = get_userdata( $user_id );
	$user_nicename = ( !false == $userdata ) ? $userdata->user_nicename : '';

	if( function_exists( 'wcfm_get_option' ) ) {
		$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
	} else {
		$wcfm_store_url = get_option( 'wcfm_store_url', 'store' );
	}
	return apply_filters( 'wcfmmp_get_store_url', trailingslashit( trailingslashit( home_url( $wcfm_store_url ) ) . $user_nicename ), $user_id );
}

/**
 * Get a store
 *
 * @param  integer $store_id
 *
 * @return \WCFMmp_Store
 */
function wcfmmp_get_store( $store_id = null ) {
	global $WCFMmp;
	if ( ! $store_id ) {
		$store_id = $WCFMmp->vendor_id;
	}
	return new WCFMmp_Store( $store_id );
}

/**
 * Get a store
 *
 * @param  integer $store_id
 *
 * @return \WCFMmp_Store
 */
function wcfmmp_get_store_info( $store_id = null ) {
	$wcfmmp_store = wcfmmp_get_store( $store_id );
	
	return $wcfmmp_store->get_shop_info();
}

/**
 * Author Link replace by Store URL
 */
add_filter( 'author_link', function( $link, $author_id, $author_nicename ) {
	if( apply_filters( 'wcfm_is_allow_author_link_replace_by_store_url', true ) ) {
		if( function_exists( 'wcfm_is_vendor' ) && wcfm_is_vendor( $author_id ) ) {
			if( function_exists( 'wcfmmp_get_store_url' ) ) {
				$link = wcfmmp_get_store_url( $author_id );
			}
		}
	}
  return $link;
}, 50, 3 );

/**
 * Martfury Template support
 */
if ( ! function_exists( 'martfury_get_page_base_url' ) ) :
	function martfury_get_page_base_url() {
		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) ) ) {
			$link = get_post_type_archive_link( 'product' );
		} elseif ( is_product_category() ) {
			$link = get_term_link( get_query_var( 'product_cat' ), 'product_cat' );
		} elseif ( is_product_tag() ) {
			$link = get_term_link( get_query_var( 'product_tag' ), 'product_tag' );
		} elseif( function_exists( 'wcfm_is_store_page' ) && wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$author         = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
			$seller_info    = get_user_by( 'slug', $author );
			$link           = wcfmmp_get_store_url( $seller_info->data->ID );
		} else {
			$queried_object = get_queried_object();
			$link           = get_term_link( $queried_object->slug, $queried_object->taxonomy );
		}

		return $link;
	}
endif;

/**
 * Display navigation to next/previous pages when applicable
 */
if ( ! function_exists( 'wcfmmp_content_nav' ) ) {
	function wcfmmp_content_nav( $nav_id, $query = null ) {
		global $wp_query, $post;
	
		if ( $query ) {
				$wp_query = $query;
		}
	
		if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
			return;
	
		?>
		<div id="<?php echo $nav_id; ?>">
	
			<?php if ( $wp_query->max_num_pages > 1 && wcfmmp_is_store_page() ) : ?>
					<?php wcfmmp_page_navi( '', '', $wp_query ); ?>
			<?php endif; ?>
	
		</div><!-- #<?php echo $nav_id; ?> -->
		<?php
	}
}

if ( ! function_exists( 'wcfmmp_page_navi' ) ) {
	function wcfmmp_page_navi( $before = '', $after = '', $wp_query ) {

    $posts_per_page = intval( get_query_var( 'posts_per_page' ) );
    $paged = intval( get_query_var( 'paged' ) );
    $numposts = $wp_query->found_posts;
    $max_page = $wp_query->max_num_pages;
    if ( $numposts <= $posts_per_page ) {
			return;
    }
    if ( empty( $paged ) || $paged == 0 ) {
			$paged = 1;
    }
    $pages_to_show = 7;
    $pages_to_show_minus_1 = $pages_to_show - 1;
    $half_page_start = floor( $pages_to_show_minus_1 / 2 );
    $half_page_end = ceil( $pages_to_show_minus_1 / 2 );
    $start_page = $paged - $half_page_start;
    if ( $start_page <= 0 ) {
        $start_page = 1;
    }
    $end_page = $paged + $half_page_end;
    if ( ($end_page - $start_page) != $pages_to_show_minus_1 ) {
			$end_page = $start_page + $pages_to_show_minus_1;
    }
    if ( $end_page > $max_page ) {
			$start_page = $max_page - $pages_to_show_minus_1;
			$end_page = $max_page;
    }
    if ( $start_page <= 0 ) {
			$start_page = 1;
    }

    echo $before . '<div class="paginations"><ul class="wcfmmp-pagination">' . "";
    if ( $paged > 1 ) {
			$first_page_text = "&laquo;";
			echo '<li class="prev"><a href="' . get_pagenum_link() . '" title="First">' . $first_page_text . '</a></li>';
    }

    /*$prevposts = get_previous_posts_link( '&larr; Previous' );
    if ( $prevposts ) {
			echo '<li>' . $prevposts . '</li>';
    } else {
			echo '<li class="disabled"><a href="#">' . __( '&larr; Previous', 'wc-multivendor-marketplace' ) . '</a></li>';
    }*/

    for ($i = $start_page; $i <= $end_page; $i++) {
			if ( $i == $paged ) {
				echo '<li><a href="#" class="active">' . $i . '</a></li>';
			} else {
				echo '<li><a href="' . get_pagenum_link( $i ) . '">' . number_format_i18n( $i ) . '</a></li>';
			}
    }
    //echo '<li class="">';
    //next_posts_link( __('Next &rarr;', 'wc-multivendor-marketplace') );
   // echo '</li>';
    //if ( $end_page < $max_page ) {
			$last_page_text = "&raquo;";
			echo '<li class="next"><a href="' . get_pagenum_link( $max_page ) . '" title="Last">' . $last_page_text . '</a></li>';
    //}
    echo '</ul></div>' . $after . "";
  }
}

/**
 * Get active withdraw order status.
 *
 * Default is 'completed', 'processing', 'on-hold'
 *
 */
function wcfmmp_withdraw_get_active_order_status() {
	$order_status  = get_option( 'wcfm_withdraw_order_status', array( 'wc-completed' ) );
	$saving_status = array();

	foreach ( $order_status as $key => $status ) {
		if ( ! empty( $status ) ) {
			$saving_status[] = $status;
		}
	}

	return apply_filters( 'wcfm_withdraw_active_status', $saving_status );
}

/**
 * get comma seperated value from "wcfmmp_withdraw_get_active_order_status()" return array
 * @param array array
 */
function wcfmmp_withdraw_get_active_order_status_in_comma() {
	$order_status = wcfmmp_withdraw_get_active_order_status();
	$status = "'" . implode("', '", $order_status ) . "'";
	return $status;
}

/**
 * get commission types
 */
function get_wcfm_marketplace_commission_types() {
	$commission_type = array(
													'percent'       => __( 'Percent', 'wc-multivendor-marketplace' ),
													'fixed'         => __( 'Fixed', 'wc-multivendor-marketplace' ),
													'percent_fixed' => __( 'Percent + Fixed', 'wc-multivendor-marketplace' ),
													'by_sales'      => __( 'By Vendor Sales', 'wc-multivendor-marketplace' ),
													'by_products'   => __( 'By Product Price', 'wc-multivendor-marketplace' ),
													'by_quantity'   => __( 'By Purchase Quantity', 'wc-multivendor-marketplace' ),
													);
	
	return apply_filters( 'wcfm_marketplace_commission_types', $commission_type );
}

if(!function_exists('get_wcfm_marketplace_withdrwal_payment_methods')) {
	function get_wcfm_marketplace_withdrwal_payment_methods() {
		$marketplace_withdrwal_payment_methods = array( 
			                                      'paypal'        => __( 'PayPal', 'wc-frontend-manager' ),
			                                      'skrill'        => __( 'Skrill', 'wc-multivendor-marketplace' ),
			                                      'bank_transfer' => __( 'Bank Transfer', 'wc-multivendor-marketplace' ),
			                                      'by_cash'       => __( 'Cash Pay', 'wc-multivendor-marketplace' ),
			                                      'wirecard'      => __( 'Wirecard (Moip)', 'wc-multivendor-marketplace' ),
			                                      'stripe'        => __( 'Stripe', 'wc-frontend-manager' ), 
			                                      'stripe_split'  => __( 'Stripe Split Pay', 'wc-multivendor-marketplace' ), 
			                                      );
		return apply_filters( 'wcfm_marketplace_withdrwal_payment_methods', $marketplace_withdrwal_payment_methods );
	}
}

if(!function_exists('get_wcfm_marketplace_active_withdrwal_payment_methods')) {
	function get_wcfm_marketplace_active_withdrwal_payment_methods() {
		$wcfm_marketplace_withdrwal_payment_methods = get_wcfm_marketplace_withdrwal_payment_methods();
		$wcfm_marketplace_active_withdrwal_payment_methods = array();
		$wcfm_withdrawal_options = get_option( 'wcfm_withdrawal_options', array() );
		$payment_methods = isset( $wcfm_withdrawal_options['payment_methods'] ) ? $wcfm_withdrawal_options['payment_methods'] : array( 'paypal', 'bank_transfer' );
		
		foreach( $wcfm_marketplace_withdrwal_payment_methods as $wcfm_marketplace_withdrwal_payment_method_key => $wcfm_marketplace_withdrwal_payment_method ) {
			if( in_array( $wcfm_marketplace_withdrwal_payment_method_key, $payment_methods ) ) {
				$wcfm_marketplace_active_withdrwal_payment_methods[$wcfm_marketplace_withdrwal_payment_method_key] = $wcfm_marketplace_withdrwal_payment_method;
			}
		}
		if( isset( $wcfm_marketplace_active_withdrwal_payment_methods['stripe_split'] ) && !isset( $wcfm_marketplace_active_withdrwal_payment_methods['stripe'] )) $wcfm_marketplace_active_withdrwal_payment_methods['stripe'] = __( 'Stripe', 'wc-frontend-manager' );
		return apply_filters( 'wcfm_marketplace_active_withdrwal_payment_methods', $wcfm_marketplace_active_withdrwal_payment_methods );
	}
}

if(!function_exists('get_wcfm_store_new_order_email_allowed_order_status')) {
	function get_wcfm_store_new_order_email_allowed_order_status() {
		$store_new_order_email_allowed_order_status = wc_get_order_statuses();
		
		//if( isset( $store_new_order_email_allowed_order_status['wc-on-hold'] ) ) unset( $store_new_order_email_allowed_order_status['wc-on-hold'] );
		if( isset( $store_new_order_email_allowed_order_status['wc-pending'] ) ) unset( $store_new_order_email_allowed_order_status['wc-pending'] );
		if( isset( $store_new_order_email_allowed_order_status['wc-cancelled'] ) ) unset( $store_new_order_email_allowed_order_status['wc-cancelled'] );
		if( isset( $store_new_order_email_allowed_order_status['wc-refunded'] ) ) unset( $store_new_order_email_allowed_order_status['wc-refunded'] );
		if( isset( $store_new_order_email_allowed_order_status['wc-failed'] ) ) unset( $store_new_order_email_allowed_order_status['wc-failed'] );
		
		return apply_filters( 'wcfmmp_store_new_order_email_allowed_order_status', $store_new_order_email_allowed_order_status );
	}
}

if(!function_exists('get_wcfm_marketplace_withdrwal_order_status')) {
	function get_wcfm_marketplace_withdrwal_order_status() {
		$marketplace_withdrwal_order_status = wc_get_order_statuses();
		
		if( isset( $marketplace_withdrwal_order_status['wc-cancelled'] ) ) unset( $marketplace_withdrwal_order_status['wc-cancelled'] );
		if( isset( $marketplace_withdrwal_order_status['wc-refunded'] ) ) unset( $marketplace_withdrwal_order_status['wc-refunded'] );
		if( isset( $marketplace_withdrwal_order_status['wc-failed'] ) ) unset( $marketplace_withdrwal_order_status['wc-failed'] );
		
		return apply_filters( 'wcfm_marketplace_withdrwal_order_status', $marketplace_withdrwal_order_status );
	}
}

if(!function_exists('get_wcfm_marketplace_active_withdrwal_order_status')) {
	function get_wcfm_marketplace_active_withdrwal_order_status() {
		$wcfm_marketplace_withdrawal_order_status = get_wcfm_marketplace_withdrwal_order_status();
		$wcfm_marketplace_active_withdrawal_order_status = array();
		$wcfm_withdrawal_options = get_option( 'wcfm_withdrawal_options', array() );
		$order_status = isset( $wcfm_withdrawal_options['order_status'] ) ? $wcfm_withdrawal_options['order_status'] : array( 'wc-completed' );
		
		foreach( $wcfm_marketplace_withdrawal_order_status as $wcfm_marketplace_withdrawal_order_status_key => $wcfm_marketplace_withdrawal_order_stat ) {
			if( in_array( $wcfm_marketplace_withdrawal_order_status_key, $order_status ) ) {
				$wcfm_marketplace_active_withdrawal_order_status[$wcfm_marketplace_withdrawal_order_status_key] = $wcfm_marketplace_withdrawal_order_stat;
			}
		}
		
		if( apply_filters( 'wcfmmp_is_allow_withdrawal_by_shipped_status', false ) ) {
			$wcfmmp_marketplace_options   = wcfm_get_option( 'wcfm_marketplace_options', array() );
			$order_sync  = isset( $wcfmmp_marketplace_options['order_sync'] ) ? $wcfmmp_marketplace_options['order_sync'] : 'no';
			if( $order_sync == 'no' ) {
				$wcfm_marketplace_active_withdrawal_order_status['wc-shipped'] = __( 'Shipped', 'wc-multivendor-marketplace' );
			}
		}
		return apply_filters( 'wcfm_marketplace_active_withdrawal_order_status', $wcfm_marketplace_active_withdrawal_order_status );
	}
}

if(!function_exists('get_wcfm_marketplace_active_withdrwal_order_status_in_comma')) {
	function get_wcfm_marketplace_active_withdrwal_order_status_in_comma() {
		$get_wcfm_marketplace_active_withdrwal_order_status = get_wcfm_marketplace_active_withdrwal_order_status();
		$wcfm_marketplace_active_withdrwal_order_status_in_comma = "'" . implode("', '", array_keys($get_wcfm_marketplace_active_withdrwal_order_status) ) . "'";
		$wcfm_marketplace_active_withdrwal_order_status_in_comma = str_replace( "wc-", "", $wcfm_marketplace_active_withdrwal_order_status_in_comma );
		return apply_filters( 'wcfm_marketplace_active_withdrwal_order_status_in_comma', $wcfm_marketplace_active_withdrwal_order_status_in_comma );
	}
}

if(!function_exists('get_wcfm_marketplace_disallow_order_payment_methods')) {
	function get_wcfm_marketplace_disallow_order_payment_methods() {
		$wcfm_marketplace_disallow_order_payment_methods = array();
		
		if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
			foreach( $payment_gateways as $payment_method => $payment_gateway ) {
				if ( wc_string_to_bool( $payment_gateway->enabled ) ) {
					$wcfm_marketplace_disallow_order_payment_methods[$payment_method] = esc_html( $payment_gateway->get_title() );
				}
			}
		} else {
			$wcfm_marketplace_disallow_order_payment_methods = array();
		}
			                                      
		return apply_filters( 'wcfm_marketplace_disallow_order_payment_methods', $wcfm_marketplace_disallow_order_payment_methods );
	}
}

if(!function_exists('get_wcfm_marketplace_disallow_active_order_payment_methods')) {
	function get_wcfm_marketplace_disallow_active_order_payment_methods() {
		$wcfm_marketplace_disallow_order_payment_methods = get_wcfm_marketplace_disallow_order_payment_methods();
		$wcfm_marketplace_disallow_active_order_payment_methods = array();
		$wcfm_withdrawal_options = get_option( 'wcfm_withdrawal_options', array() );
		$disallow_order_payment_methods = isset( $wcfm_withdrawal_options['disallow_order_payment_methods'] ) ? $wcfm_withdrawal_options['disallow_order_payment_methods'] : array();
		
		foreach( $wcfm_marketplace_disallow_order_payment_methods as $wcfm_marketplace_disallow_order_payment_method_key => $wcfm_marketplace_disallow_order_payment_method ) {
			if( in_array( $wcfm_marketplace_disallow_order_payment_method_key, $disallow_order_payment_methods ) ) {
				$wcfm_marketplace_disallow_active_order_payment_methods[$wcfm_marketplace_disallow_order_payment_method_key] = $wcfm_marketplace_disallow_order_payment_method;
			}
		}
		return apply_filters( 'wcfm_marketplace_disallow_active_order_payment_methods', $wcfm_marketplace_disallow_active_order_payment_methods );
	}
}

if(!function_exists('get_wcfm_marketplace_default_review_categories')) {
	function get_wcfm_marketplace_default_review_categories() {
		$default_review_categories = array( 
																				array('category'       => __( 'Feature', 'wc-multivendor-marketplace' )),
																				array('category'       => __( 'Varity', 'wc-multivendor-marketplace' )),
																				array('category'       => __( 'Flexibility', 'wc-multivendor-marketplace' )),
																				array('category'       => __( 'Delivery', 'wc-multivendor-marketplace' )),
																				array('category'       => __( 'Support', 'wc-frontend-manager' )), 
																				);
		return apply_filters( 'wcfm_marketplace_default_review_categories', $default_review_categories );
	}
}

if(!function_exists('get_wcfm_marketplace_default_widgets')) {
	function get_wcfm_marketplace_default_widgets() {
		$default_widgets = array( 
															'store-location'             => __( 'Store Location', 'wc-multivendor-marketplace' ),
															'store-info'                 => __( 'Store Info', 'wc-multivendor-marketplace' ),
															'store-category'             => __( 'Store Category', 'wc-multivendor-marketplace' ),
															'store-taxonomy'             => __( 'Store Taxonomies', 'wc-multivendor-marketplace' ),
															'store-hours'                => __( 'Store Hours', 'wc-multivendor-marketplace' ),
															'store-shipping-rules'       => __( 'Store Shipping Rules', 'wc-multivendor-marketplace' ),
															'store-coupons'              => __( 'Store Coupons', 'wc-multivendor-marketplace' ),
															'store-product-search'       => __( 'Store Product Search', 'wc-multivendor-marketplace' ),
															'store-top-products'         => __( 'Store Top Products', 'wc-multivendor-marketplace' ),
															'store-top-rated-products'   => __( 'Store Top Rated Products', 'wc-multivendor-marketplace' ),
															'store-recent-products'      => __( 'Store Recent Products', 'wc-multivendor-marketplace' ),
															'store-featured-products'    => __( 'Store Featured Products', 'wc-multivendor-marketplace' ),
															'store-on-sale-products'     => __( 'Store On Sale Products', 'wc-multivendor-marketplace' ),
															'store-recent-articles'      => __( 'Store Recent Articles', 'wc-multivendor-marketplace' ),
                              
                              'store-lists-search'           => __( 'Store Lists Search', 'wc-multivendor-marketplace' ),
                              'store-lists-category-filter'  => __( 'Store Lists Category Filter', 'wc-multivendor-marketplace' ),
                              'store-lists-location-filter'  => __( 'Store Lists Location Filter', 'wc-multivendor-marketplace' ),
                              'store-lists-radius-filter'    => __( 'Store Lists Radius Filter', 'wc-multivendor-marketplace' ),
                              'store-lists-meta-filter'      => __( 'Store Lists Meta Filter', 'wc-multivendor-marketplace' ),
                              
                              'products-search-by-vendors'   => __( 'Search by Vendors', 'wc-multivendor-marketplace' ),
                              'store-top-rated-vendors'      => __( 'Store Top Rated Vendors', 'wc-multivendor-marketplace' ),
                              'store-best-selling-vendors'   => __( 'Store Best Selling Vendors', 'wc-multivendor-marketplace' ),
															
															);
		return apply_filters( 'wcfm_marketplace_default_widgets', $default_widgets );
	}
}

if(!function_exists('get_wcfm_marketplace_active_review_categories')) {
	function get_wcfm_marketplace_active_review_categories() {
		global $WCFMmp;
		$wcfm_default_review_categories = get_wcfm_marketplace_default_review_categories();
		$wcfm_review_categories = isset( $WCFMmp->wcfmmp_review_options['review_categories'] ) ? $WCFMmp->wcfmmp_review_options['review_categories'] : $wcfm_default_review_categories;
		return $wcfm_review_categories;
	}
}

if(!function_exists('get_wcfm_marketplace_emails')) {
	function get_wcfm_marketplace_emails() {
		$marketplace_emails = array( 
			                                      'store-new-order'        => __( 'Store New Order', 'wc-multivendor-marketplace' ),
			                                      );
		return apply_filters( 'get_wcfm_marketplace_emails', $marketplace_emails );
	}
}

if(!function_exists('get_wcfm_reviews_messages')) {
	function get_wcfm_reviews_messages() {
		global $WCFM;
		
		$messages = array(
											'no_comment'                  => __( 'Please insert your comment before submit.', 'wc-multivendor-marketplace' ),
											'no_rating'                   => __( 'Please rate atleast one category before submit.', 'wc-multivendor-marketplace' ),
											'review_saved'       		      => __( 'Your review successfully submited, will publish after approval!', 'wc-multivendor-marketplace' ),
											'review_published'       		  => __( 'Your review successfully submited.', 'wc-multivendor-marketplace' ),
											'review_response_saved'       => __( 'Your review response successfully submited.', 'wc-multivendor-marketplace' ),
											'refund_requests_failed'      => __( 'Your refund request failed, please try after sometime.', 'wc-multivendor-marketplace' ),
											'refund_requests_approved'    => __( 'Refund requests successfully approved.', 'wc-multivendor-marketplace' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_refund_requests_messages')) {
	function get_wcfm_refund_requests_messages() {
		global $WCFM;
		
		$messages = array(
											'no_refund_reason'            => __( 'Please insert your refund reason before submit.', 'wc-multivendor-marketplace' ),
											'refund_requests_saved'       => __( 'Your refund request successfully sent.', 'wc-multivendor-marketplace' ),
											'refund_requests_failed'      => __( 'Your refund request failed, please try after sometime.', 'wc-multivendor-marketplace' ),
											'refund_requests_approved'    => __( 'Refund requests successfully approved.', 'wc-multivendor-marketplace' ),
											);
		
		return $messages;
	}
}

function wcfmmp_status_labels( $status ) {
	$all_status = array(
											'pending'    => __( 'Pending', 'wc-multivendor-marketplace' ),
											'shipped'    => __( 'Shipped', 'wc-multivendor-marketplace' ),
											'completed'  => __( 'Completed', 'wc-multivendor-marketplace' ),
											'cancelled'  => __( 'Cancelled', 'wc-multivendor-marketplace' ),
											'requested'  => __( 'Requested', 'wc-multivendor-marketplace' ),
											);
	$status = isset( $all_status[$status] ) ? $all_status[$status] : ucfirst( $status );
	return $status;
}

/**
 * Store link in BuddyPress members menu
 */
function bp_wcfmmp_store_nav_item() {
	global $bp, $WCFMmp;
	
	if( !$bp || !$bp->displayed_user || !$bp->displayed_user->userdata || !$bp->displayed_user->id ) return;
	
	if( wcfm_is_vendor( $bp->displayed_user->id ) ) {
		$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( $bp->displayed_user->id );
		$wcfmmp_store_bp_menu_class = apply_filters( 'wcfmmp_store_bp_menu_classes', 'yz-navbar-item' );
		?>
		<li id="wcfmmp-store-personal-li" class="bp-personal-tab <?php echo $wcfmmp_store_bp_menu_class; ?>">
		  <?php do_action( 'wcfmmp_store_bp_menu_before', $bp->displayed_user->id ); ?>
		  <a href="<?php echo wcfmmp_get_store_url( $bp->displayed_user->id ); ?>">
		    <?php do_action( 'wcfmmp_store_bp_menu_anchor_before', $bp->displayed_user->id ); ?>
		    <?php echo $sold_by_text; ?> 
		    <?php do_action( 'wcfmmp_store_bp_menu_anchor_after', $bp->displayed_user->id ); ?>
		  </a>
		  <?php do_action( 'wcfmmp_store_bp_menu_after', $bp->displayed_user->id ); ?>
		</li>
		<?php 
	}
}

if( WCFMmp_Dependencies::wcfm_plugin_active_check() && WCFMmp_Dependencies::wcfm_biddypress_plugin_active_check()  && apply_filters( 'wcfm_is_allow_bp_store_nav_display', true ) ) {
	add_action( 'bp_member_options_nav', 'bp_wcfmmp_store_nav_item', 99 );
}

/**
 * WCFM Product Mutivendor Tab - tab manager support
 *
 * @since		1.0.1
 */
function wcfm_product_multivendor_tab( $tabs) {
	global $WCFM, $WCFMmp;
	
	// Sold by Template as Tab
	if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by() ) {
		$vendor_sold_by_template = $WCFMmp->wcfmmp_vendor->get_vendor_sold_by_template();
		if( $vendor_sold_by_template == 'tab' ) {
			$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label();
			$tabs['wcfm_product_store_tab'] = apply_filters( 'wcfm_product_store_tab_element',array(
																																								'title' 	=> apply_filters( 'wcfm_product_store_tab_title', $sold_by_text ),
																																								'priority' 	=> apply_filters( 'wcfm_product_store_tab_priority', 97 ),
																																								'callback' 	=> array( $WCFMmp->frontend, 'wcfmmp_sold_by_tab_single_product' )
																																							) );
		}
	}
	
	// Single Product Multi-Vendor Tab
	if( apply_filters( 'wcfm_is_pref_product_multivendor', true ) ) {
		$tabs['wcfm_product_multivendor_tab'] = apply_filters( 'wcfm_product_multivendor_tab_element',array(
																																								'title' 	=> apply_filters( 'wcfm_product_multivendor_tab_title', __( 'More Offers', 'wc-multivendor-marketplace' ) ),
																																								'priority' 	=> apply_filters( 'wcfm_product_multivendor_tab_priority', 98 ),
																																								'callback' 	=> array( $WCFMmp->wcfmmp_product_multivendor, 'wcfmmp_product_multivendor_tab_content' )
																																							) );
	}
	return $tabs;
}
if( WCFMmp_Dependencies::wcfm_plugin_active_check() ) {
	add_filter( 'woocommerce_product_tabs', 'wcfm_product_multivendor_tab', 98 );
}

/**
 * Single Product Location - GEO my WP suppport
 */
function wcfmmp_geo_product_location_show() {
	global $product;
	if( $product && is_object( $product ) ) {
		echo do_shortcode( '[gmw_single_location map_width="100%"]' );
	}
}

function wcfmmp_product_location_show() {
	global $product, $WCFMmp, $WCFM;
	if( $product && is_object( $product ) && method_exists( $product, 'get_id' ) ) {
		$vendor_id = wcfm_get_vendor_id_by_post( $product->get_id() );
		if( !$vendor_id ) return;
		
		$store_user  = wcfmmp_get_store( $vendor_id );
		$store_info  = $store_user->get_shop_info();
		$address     = $store_user->get_address_string(); 
		
		$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
		$store_lat    = isset( $store_info['store_lat'] ) ? esc_attr( $store_info['store_lat'] ) : 0;
		$store_lng    = isset( $store_info['store_lng'] ) ? esc_attr( $store_info['store_lng'] ) : 0;
		
		if ( ( ( ($wcfm_map_lib == 'google') && !empty( $api_key ) ) || ($wcfm_map_lib == 'leaflet') ) && !empty( $store_lat ) && !empty( $store_lng ) ) {
			echo '<div class="wcfmmp_store_tab_info wcfmmp_store_info_store_location">';
			do_action( 'before_wcfmmp_sold_by_location_product_page', $store_user->get_id() );
			
			$map_id = 'wcfm_sold_by_tab_map_'.rand(10,100);
		
			$WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-store-location.php', array( 
																										 'store_user' => $store_user, 
																										 'store_info' => $store_info,
																										 'address'    => $address,
																										 'store_lat'  => $store_lat,
																										 'store_lng'  => $store_lng,
																										 'map_id'     => $map_id
																										 ) );
	
			do_action( 'after_wcfmmp_sold_by_location_product_page', $store_user->get_id() );
			echo '</div>';
			?>
			<style>
			  #<?php echo $map_id; ?>{width:100%!important;height:450px!important;}
			</style>
			<?php
			wp_enqueue_script( 'wcfmmp_store_js', $WCFMmp->library->js_lib_url_min . 'store/wcfmmp-script-store.js', array('jquery' ), $WCFMmp->version, true );
			$WCFMmp->library->load_map_lib();
			
			// Default Map Location
			$default_geolocation = isset( $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] ) ? $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] : array();
			$store_location      = isset( $default_geolocation['location'] ) ? esc_attr( $default_geolocation['location'] ) : '';
			$map_address         = isset( $default_geolocation['address'] ) ? esc_attr( $default_geolocation['address'] ) : '';
			$default_lat         = isset( $default_geolocation['lat'] ) ? esc_attr( $default_geolocation['lat'] ) : apply_filters( 'wcfmmp_map_default_lat', 30.0599153 );
			$default_lng         = isset( $default_geolocation['lng'] ) ? esc_attr( $default_geolocation['lng'] ) : apply_filters( 'wcfmmp_map_default_lng', 31.2620199 );
			$default_zoom        =  apply_filters( 'wcfmmp_map_default_zoom_level', 17 );
			$store_icon          = apply_filters( 'wcfmmp_map_store_icon', $WCFMmp->plugin_url . 'assets/images/wcfmmp_map_icon.png', 0, '' );
			
			wp_localize_script( 'wcfmmp_store_js', 'wcfmmp_store_map_options', array( 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'store_icon' => $store_icon, 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_poi' => apply_filters( 'wcfmmp_is_allow_map_poi', true ), 'is_allow_scroll_zoom' => apply_filters( 'wcfmmp_is_allow_map_scroll_zoom', true ), 'is_rtl' => is_rtl() ) );
		}
	}
}
function wcfmmp_location_product_tab( $tabs ) {
	global $WCFM, $WCFMmp, $product;
	
	if( WCFMmp_Dependencies::wcfm_plugin_active_check() && WCFMmp_Dependencies::wcfm_geo_my_wp_plugin_active_check() ) {
		if( $product && apply_filters( 'wcfm_is_allow_product_location_display', false ) ) {
			$tabs['wcfm_location_tab'] = array(
																				'title' 	  => __( 'Location', 'wc-multivendor-marketplace' ),
																				'priority' 	=> apply_filters( 'wcfm_location_tab_priority', 97 ),
																				'callback' 	=> 'wcfmmp_geo_product_location_show'
																				);
		}
	} else {
		if( $product && method_exists( $product, 'get_id' ) && apply_filters( 'wcfm_is_allow_product_location_display', true ) ) {
			$wcfm_marketplace_options = $WCFMmp->wcfmmp_marketplace_options;
			$show_product_location    = isset( $wcfm_marketplace_options['show_product_location'] ) ? $wcfm_marketplace_options['show_product_location'] : 'no';
			
			if( $show_product_location == 'yes' ) {
				$vendor_id = wcfm_get_vendor_id_by_post( $product->get_id() );
				if( $vendor_id ) {
					$store_user  = wcfmmp_get_store( $vendor_id );
					$store_info  = $store_user->get_shop_info();
					
					$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
					$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
					if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
					$store_lat    = isset( $store_info['store_lat'] ) ? esc_attr( $store_info['store_lat'] ) : 0;
					$store_lng    = isset( $store_info['store_lng'] ) ? esc_attr( $store_info['store_lng'] ) : 0;
					
					if ( ( ( ($wcfm_map_lib == 'google') && !empty( $api_key ) ) || ($wcfm_map_lib == 'leaflet') ) && !empty( $store_lat ) && !empty( $store_lng ) && ( $store_info['store_hide_map'] != 'yes' ) && ( $store_info['store_hide_address'] != 'yes' ) ) {
			
			
						$tabs['wcfm_location_tab'] = array(
																							'title' 	  => __( 'Location', 'wc-multivendor-marketplace' ),
																							'priority' 	=> apply_filters( 'wcfm_location_tab_priority', 97 ),
																							'callback' 	=> 'wcfmmp_product_location_show'
																							);
					}
				}
			}
		}
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'wcfmmp_location_product_tab', 97 );

// WooCommerce POS Compatibility
function wcfmmp_pos_pre_get_posts( $query ) {
	$author = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	$query->set( 'author', $author );
}
function wcfmmp_pos_rest_request_before_callbacks( $response, $handler, $request ) {
	if( apply_filters( 'wcfm_is_allow_parse_pos_request', false ) ) {
		if( $handler && is_array( $handler ) && isset( $handler['callback'] ) && isset( $handler['callback'][0] ) ) {
			$wc_api_handler = get_class($handler['callback'][0]);
			if( $wc_api_handler == 'WC_REST_Products_Controller' ) {
				if( is_user_logged_in() && wcfm_is_vendor() ) {
					add_action( 'pre_get_posts', 'wcfmmp_pos_pre_get_posts', 50 );
				}
			}
		}
	}
	return $response;
}
add_filter( 'rest_request_before_callbacks', 'wcfmmp_pos_rest_request_before_callbacks', 9, 3 );

add_filter( 'woocommerce_pos_menu', function( $pos_menus ) {
	$pos_menus = array(
											array(
														'id'     => 'pos',
														'label'  => __( 'POS', 'woocommerce-pos' ),
														'href'   => '#'
													)
										);
	
	return $pos_menus;
});

// FooEvents App Compatibility
add_action( 'pre_get_posts', function( $query ) {
	global $_SERVER;
	if( function_exists('wcfm_is_vendor') && wcfm_is_vendor() ) {
		if( $query->get( 'post_type' ) == 'product' ) {
			if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
				global $HTTP_RAW_POST_DATA;
				$message = new IXR_Message($HTTP_RAW_POST_DATA);
				if( $message->parse() && ( $message->messageType == 'methodCall' ) && in_array( $message->methodName, array( 'fooevents.get_all_data', 'fooevents.get_list_of_events' ) ) ) {
					$vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
					$query->set( 'author', $vendor_id );
				}
			} elseif( isset( $_SERVER['REQUEST_URI'] ) && isset( $_SERVER['HTTP_USER_AGENT'] ) && ( $_SERVER['HTTP_USER_AGENT'] == 'FooEvents_app' ) ) {
				if (strpos( $_SERVER['REQUEST_URI'], 'get_list_of_events') !== false) {
					$vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
					$query->set( 'author', $vendor_id );
				}
			}
		}
	}
});

// WC Customer Note Reply to Vendor Email
add_filter( 'woocommerce_email_headers', function( $header, $mail_id, $mail_object ) {
	global $WCFM, $WCFMmp;
	if( function_exists( 'wcfm_is_vendor' ) && wcfm_is_vendor() && ( $mail_id == 'customer_note' ) && apply_filters( 'wcfm_is_allow_order_note_reply_to_vendor', true ) ) {
		$vendor_email = $WCFM->wcfm_vendor_support->wcfm_get_vendor_email_by_vendor( $WCFMmp->vendor_id );
		if( $vendor_email ) {
			if( $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $WCFMmp->vendor_id, 'view_email' ) ) {
				$header   = 'Content-Type: ' . WC()->mailer()->emails['WC_Email_Customer_Note']->get_content_type() . "\r\n";
				$header  .= 'Reply-to: ' . $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( $WCFMmp->vendor_id ) . ' ' . apply_filters( 'wcfm_sold_by_label', $WCFMmp->vendor_id, __( 'Store', 'wc-frontend-manager' ) ) . ' <' . $vendor_email . '>';
			}
		}
	}
	return $header;
}, 50, 3 );


//Shipping Functions

if(!function_exists('wcfmmp_get_shipping_types')) {
  function wcfmmp_get_shipping_types() {
    $shipping_type = array(
        '' => __( 'Select Shipping Type...', 'wc-multivendor-marketplace' ),
        'by_country' => __( 'Shipping by Country', 'wc-multivendor-marketplace' ),
        'by_zone' => __( 'Shipping by Zone', 'wc-multivendor-marketplace' ),
        'by_weight' => __( 'Shipping by Weight', 'wc-multivendor-marketplace' ),
        'by_distance' => __( 'Shipping by Distance', 'wc-multivendor-marketplace' ),
    );
    return apply_filters( 'wcfmmp_shipping_types', $shipping_type );
  }
}

if(!function_exists('wcfmmp_get_shipping_processing_times')) {
  function wcfmmp_get_shipping_processing_times() {
		$processing_times = array(
															'' => __( 'Ready to ship in...', 'wc-multivendor-marketplace' ),
															'1' => __( '1 business day', 'wc-multivendor-marketplace' ),
															'2' => __( '1-2 business days', 'wc-multivendor-marketplace' ),
															'3' => __( '1-3 business days', 'wc-multivendor-marketplace' ),
															'4' => __( '3-5 business days', 'wc-multivendor-marketplace' ),
															'5' => __( '1-2 weeks', 'wc-multivendor-marketplace' ),
															'6' => __( '2-3 weeks', 'wc-multivendor-marketplace' ),
															'7' => __( '3-4 weeks', 'wc-multivendor-marketplace' ),
															'8' => __( '4-6 weeks', 'wc-multivendor-marketplace' ),
															'9' => __( '6-8 weeks', 'wc-multivendor-marketplace' ),
													);

		return apply_filters( 'wcfmmp_shipping_processing_times', $processing_times );
  }
}

if(!function_exists('wcfmmp_get_shipping_methods')) {
  function wcfmmp_get_shipping_methods( ) {
    return apply_filters( 'wcfmmp_vendor_shipping_methods', apply_filters( 'vendor_shipping_methods', array(
																														''  => __('-- Select a Method --', 'wc-multivendor-marketplace'),
																														'flat_rate' => __('Flat Rate', 'wc-multivendor-marketplace'),
																														'local_pickup' => __('Local Pickup', 'wc-multivendor-marketplace'),
																														'free_shipping' => __('Free Shipping', 'wc-multivendor-marketplace')
																													) ) );
  }
}

if(!function_exists('wcfmmp_is_shipping_enabled')) {
  function wcfmmp_is_shipping_enabled( $vendor_id ) {
    global  $WCFMmp;
    
    $wcfm_shipping_options = get_option( 'wcfm_shipping_options', array() );
		$wcfmmp_store_shipping_enabled = isset( $wcfm_shipping_options['enable_store_shipping'] ) ? $wcfm_shipping_options['enable_store_shipping'] : 'yes';
		if( $wcfmmp_store_shipping_enabled != 'yes' ) return apply_filters( 'wcfmmp_is_shipping_enabled', false, $vendor_id );
    
    $vendor_shipping_details = get_user_meta( $vendor_id, '_wcfmmp_shipping', true );
    if( !empty($vendor_shipping_details) ){
      $enabled = isset( $vendor_shipping_details['_wcfmmp_user_shipping_enable'] ) ? $vendor_shipping_details['_wcfmmp_user_shipping_enable'] : '';
      $type = !empty( $vendor_shipping_details['_wcfmmp_user_shipping_type'] ) ? $vendor_shipping_details['_wcfmmp_user_shipping_type'] : '';
      if ( ( !empty($enabled) && $enabled == 'yes' ) && ( !empty($type) ) && '' != $type ) {
         return apply_filters( 'wcfmmp_is_shipping_enabled', true, $vendor_id );
      }
    }

    return apply_filters( 'wcfmmp_is_shipping_enabled', false, $vendor_id );
  }
}

/**
 * Calculate Store - User Distance
 */
function wcfmmp_get_user_vendor_distance( $store_id ) {
	global $WCFM, $WCFMmp, $wpdb, $wcfmmp_radius_lat, $wcfmmp_radius_lng, $wcfmmp_radius_range;
	
	$distance = '';
	if( $wcfmmp_radius_lat && $wcfmmp_radius_lng ) {
		$radius_unit   = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';
		$earth_surface = ( 'mi' === $radius_unit ) ? 3959 : 6371;
		
		$store_query = " SELECT (
			{$earth_surface} * acos(
				cos( radians( {$wcfmmp_radius_lat} ) ) *
				cos( radians( wcfmmplat.meta_value ) ) *
				cos(
						radians( wcfmmplong.meta_value ) - radians( {$wcfmmp_radius_lng} )
				) +
				sin( radians( {$wcfmmp_radius_lat} ) ) *
				sin( radians( wcfmmplat.meta_value ) )
			)
		) as wcfmmp_distance FROM {$wpdb->users}";
		
		$store_query .= " inner join {$wpdb->usermeta} as wcfmmplat on {$wpdb->users}.ID = wcfmmplat.user_id and wcfmmplat.meta_key = '_wcfm_store_lat'";
		$store_query .= " inner join {$wpdb->usermeta} as wcfmmplong on {$wpdb->users}.ID = wcfmmplong.user_id and wcfmmplong.meta_key = '_wcfm_store_lng'";
		$store_query .= " WHERE {$wpdb->users}.ID = {$store_id}";
		
		$distance = $wpdb->get_results( $store_query );
		if( isset($distance[0] ) ) {
			if( $distance[0]->wcfmmp_distance ) {
				$distance = round( $distance[0]->wcfmmp_distance, 2 );
			} else {
				$distance = 0.01;
			}
		} else {
			$distance = '';
		}
	}
	return apply_filters( 'wcfm_user_vendor_distance', $distance, $store_id );
}

/**
  * Get shipping zone
  *
  * @since 1.0.0
  *
  * @return void
  */

if(!function_exists('wcfmmp_get_shipping_zone')) {
  function wcfmmp_get_shipping_zone($zoneID = '', $user_id = 0 ) {
    if ( isset( $zoneID ) && $zoneID != '' ) {
        $zones = WCFMmp_Shipping_Zone::get_zone( $zoneID );
    } else {
        $zones = WCFMmp_Shipping_Zone::get_zones( $user_id );
    }
    return $zones;
  }
}

function wcfmmp_convert_to_array($a) {
  return (array) $a;
}

if(!function_exists('wcfmmp_state_key_alter')) {
  function wcfmmp_state_key_alter(&$value, $key) {
    $value = array_combine(
    array_map(function($k) use ($key){ return $key. ':' .$k; }, array_keys($value)),
        $value
    );
  }
}

function wcfmmp_generate_social_url( $social_handle, $social ) {
	switch( $social ) {
		case 'fb':
		case 'facebook':
		  if( strpos( $social_handle, 'facebook' ) === false) {
		  	$social_handle = 'https://facebook.com/' . $social_handle;
		  }
		break;
		
		case 'twitter' :
		  if( strpos( $social_handle, 'twitter' ) === false) {
		  	$social_handle = 'https://twitter.com/' . $social_handle;
		  }
		break;
		
		case 'instagram' :
		  if( strpos( $social_handle, 'instagram' ) === false) {
		  	$social_handle = 'https://instagram.com/' . $social_handle;
		  }
		break;
		
		case 'linkedin' :
		  if( strpos( $social_handle, 'linkedin' ) === false) {
		  	//$social_handle = 'https://linkedin.com/' . $social_handle;
		  }
		break;
		
		case 'youtube' :
		  if( strpos( $social_handle, 'youtube' ) === false) {
		  	$social_handle = 'https://youtube.com/channel/' . $social_handle;
		  }
		break;
		
	}
	return apply_filters( 'wcfm_social_url', $social_handle, $social );
}

if(!function_exists('wcfmmp_log')) {
	function wcfmmp_log( $message, $level = 'debug' ) {
		wcfm_create_log( $message, $level );
	}
}

if(!function_exists('wcfm_stripe_log')) {
	function wcfm_stripe_log( $message, $level = 'debug' ) {
		wcfm_create_log( $message, $level, 'wcfm-stripe' );
	}
}

if(!function_exists('wcfm_wirecard_log')) {
	function wcfm_wirecard_log( $message, $level = 'debug' ) {
		wcfm_create_log( $message, $level, 'wcfm-wirecard' );
	}
}

if(!function_exists('wcfm_cleanup_log')) {
	function wcfm_cleanup_log( $message, $level = 'debug' ) {
		wcfm_create_log( $message, $level, 'wcfm-data-cleanup' );
	}
}

if(!function_exists('wcfm_withdrawal_log')) {
	function wcfm_withdrawal_log( $message, $level = 'debug' ) {
		wcfm_create_log( $message, $level, 'wcfm-withdrawal' );
	}
}
?>