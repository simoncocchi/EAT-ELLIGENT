<?php
/**
 * WCFM plugin shortcode
 *
 * Plugin Shortcode output
 *
 * @author 		WC Lovers
 * @package 	wcfm/includes/shortcode
 * @version   1.0.0
 */
 
class WCFM_Frontend_Manager_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the WC Frontend Manager shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	static public function output( $attr ) {
		global $WCFM, $wp, $WCFM_Query;
		$WCFM->nocache();
		
		$end_point = 'wcfm-dashboard';
		
		echo '<div id="wcfm-main-contentainer">';
		do_action( 'wcfm_main_contentainer_before' );
		echo  '<div id="wcfm-content">';
		
		if ( isset( $wp->query_vars['page'] ) ) {
			$WCFM->library->load_views( 'wcfm-dashboard' );
		} else {
			$wcfm_endpoints = $WCFM_Query->get_query_vars();
			$is_endpoint = false;
			foreach ( $wcfm_endpoints as $key => $value ) {
				if ( isset( $wp->query_vars[ $key ] ) ) {
					$WCFM->library->load_views( $key );
					$end_point = $key;
					$is_endpoint = true;
				}
			}
			
			if( !$is_endpoint && is_wcfm_page() ) {
				$WCFM->library->load_views( 'wcfm-dashboard' );
			}
		}
		
		echo '</div>';
		do_action( 'wcfm_main_contentainer_after', $end_point );
		echo '</div>';
	}
}
