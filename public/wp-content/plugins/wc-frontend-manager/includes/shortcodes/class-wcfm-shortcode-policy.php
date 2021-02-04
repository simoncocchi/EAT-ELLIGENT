<?php
/**
 * WCFM plugin shortcode
 *
 * Plugin Shortcode output
 *
 * @author 		WC Lovers
 * @package 	wcfm/includes/shortcode
 * @version   6.4.7
 */
 
class WCFM_Policy_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the Enquiry shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	static public function output( $attr ) {
		global $WCFM, $WCFMmp, $wp, $WCFM_Query, $post;
		$WCFM->nocache();
		
		if( !apply_filters( 'wcfm_is_pref_policies', true ) ) return;
		
		if( is_product() ) {
			$WCFM->wcfm_policy->wcfm_policies_product_tab_content();
		} elseif( function_exists( 'wcfm_is_store_page' ) && wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
			$store_id  = 0;
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
			
			if( $store_id ) {
				$store_user        = wcfmmp_get_store( $store_user->ID );
				$store_info        = $store_user->get_shop_info();
				$WCFMmp->template->get_template( 'store/wcfmmp-view-store-policies.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
			}
		}
	}
}