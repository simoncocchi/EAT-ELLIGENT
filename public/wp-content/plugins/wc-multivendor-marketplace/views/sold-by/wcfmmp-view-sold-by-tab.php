<?php
/**
 * The Template for displaying store.
 *
 * @package WCfM Markeplace Views Store Sold By as Tab
 *
 * For edit coping this to yourtheme/wcfm/sold-by
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
if( $vendor_id ) {
	if( apply_filters( 'wcfmmp_is_allow_sold_by', true, $vendor_id ) && wcfm_vendor_has_capability( $vendor_id, 'sold_by' ) ) {
		// Check is store Online
		$is_store_offline = get_user_meta( $vendor_id, '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
			
		$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( absint($vendor_id) );
		
		
		if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
			$store_name = wcfm_get_vendor_store( absint($vendor_id) );
		} else {
			$store_name = wcfm_get_vendor_store_name( absint($vendor_id) );
		}
		
		$store_user  = wcfmmp_get_store( $vendor_id );
		$store_info  = $store_user->get_shop_info();
		
		$gravatar = $store_user->get_avatar();
		$email    = $store_user->get_email();
		$phone    = $store_user->get_phone(); 
		$address  = $store_user->get_address_string(); 
		
		echo '<div class="wcfmmp_sold_by_container">';
		do_action('before_wcfmmp_sold_by_gravatar_product_page', $vendor_id );
		echo '<div class="wcfmmp_store_tab_info wcfmmp_store_info_gravatar"><img style="max-height:150px;" src="' . $store_user->get_avatar() . '" /></div>';
		do_action('before_wcfmmp_sold_by_label_product_page', $vendor_id );
		echo '<div class="wcfmmp_sold_by_wrapper">' . $store_name . '</div>';
		if( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) ) { $WCFMmp->wcfmmp_reviews->show_star_rating( 0, $vendor_id ); }
		do_action('after_wcfmmp_sold_by_label_product_page', $vendor_id );
		
		do_action('before_wcfmmp_sold_by_info_product_page', $vendor_id );
		
		if( $address && ( $store_info['store_hide_address'] == 'no' ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'vendor_address' ) ) {
			echo '<div class="wcfmmp_store_tab_info wcfmmp_store_info_address"><i class="wcfmfa fa-map-marker" aria-hidden="true"></i>' . $address . '</div>';
		}
		
		if( $email && ( $store_info['store_hide_email'] == 'no' ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'vendor_email' ) ) {
			echo '<div class="wcfmmp_store_tab_info wcfmmp_store_info_address"><i class="wcfmfa fa-envelope" aria-hidden="true"></i>' . $email . '</div>';
		}
		
		if( $phone && ( $store_info['store_hide_phone'] == 'no' ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'vendor_phone' ) ) {
			echo '<div class="wcfmmp_store_tab_info wcfmmp_store_info_address"><i class="wcfmfa fa-phone" aria-hidden="true"></i><span>' . $phone . '</div>';
		}
		
		do_action('after_wcfmmp_sold_by_info_product_page', $vendor_id );
		
		if( apply_filters( 'wcfmmp_is_allow_sold_by_social', true ) ) {
			if( !empty( $store_info['social'] ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_social' ) ) { 
				do_action('before_wcfmmp_sold_by_social_product_page', $vendor_id ); ?>
				<div class="wcfm_cearfix"></div>
				<div class="wcfmmp_store_tab_info wcfmmp_store_info_store_social">
					<?php $WCFMmp->template->get_template( 'store/wcfmmp-view-store-social.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) ); ?>
				</div>
				<div class="wcfm_cearfix"></div>
			 
				<?php do_action('after_wcfmmp_sold_by_social_product_page', $vendor_id );
			}
		}
		
		if( apply_filters( 'wcfmmp_is_allow_sold_by_about', false ) ) {
			$wcfm_shop_description = apply_filters( 'woocommerce_short_description', $store_user->get_shop_description() );
			?>
			<?php if( $wcfm_shop_description ) { ?>
				<div class="wcfm-store-about" style="font-style:italic;">
					<div class="wcfm_store_description" ><?php echo $wcfm_shop_description; ?></div>
				</div>
			<?php 
			}
		}
		
		if( apply_filters( 'wcfmmp_is_allow_sold_by_location', true ) ) {
			$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
			$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
			if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
			$store_lat    = isset( $store_info['store_lat'] ) ? esc_attr( $store_info['store_lat'] ) : 0;
			$store_lng    = isset( $store_info['store_lng'] ) ? esc_attr( $store_info['store_lng'] ) : 0;
	
			if ( ( ( ($wcfm_map_lib == 'google') && !empty( $api_key ) ) || ($wcfm_map_lib == 'leaflet') ) && !empty( $store_lat ) && !empty( $store_lng ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_map' ) ) {
				echo '<div class="wcfmmp_store_tab_info wcfmmp_store_info_store_location">';
				do_action( 'before_wcfmmp_sold_by_location_product_page', $store_user->get_id() );
			
				$WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-store-location.php', array( 
																											 'store_user' => $store_user, 
																											 'address'    => $address,
																											 'store_info' => $store_info,
																											 'store_lat'  => $store_lat,
																											 'store_lng'  => $store_lng,
																											 'map_id'     => 'wcfm_sold_by_tab_map_'.rand(10,100)
																											 ) );
		
				do_action( 'after_wcfmmp_sold_by_location_product_page', $store_user->get_id() );
				echo '</div>';
				
				wp_enqueue_script( 'wcfmmp_store_js', $WCFMmp->library->js_lib_url . 'store/wcfmmp-script-store.js', array('jquery' ), $WCFMmp->version, true );
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
		
		echo '</div>';
	}
}