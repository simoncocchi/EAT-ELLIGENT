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
 
class WCFM_Endpoint_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the Endpoint shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	static public function output( $attr ) {
		global $WCFM, $wp, $WCFM_Query;
		$WCFM->nocache();
		
		echo '<div id="wcfm-main-contentainer"> <div id="wcfm-content">';
		
		$menu = true;
		if ( isset( $attr['menu'] ) && !empty( $attr['menu'] ) && ( 'false' == $attr['menu'] ) ) { $menu = false; } 
		
		if ( !isset( $attr['endpoint'] ) || ( isset( $attr['endpoint'] ) && empty( $attr['endpoint'] ) ) ) {
			
			// Load Scripts
			$WCFM->library->load_scripts( 'wcfm-dashboard' );
			
			// Load Styles
			$WCFM->library->load_styles( 'wcfm-dashboard' );
			
			// Load View
			$WCFM->library->load_views( 'wcfm-dashboard', $menu );
		} else {
			$wcfm_endpoints = $WCFM_Query->get_query_vars();
			
			foreach ( $wcfm_endpoints as $key => $value ) {
				if ( isset( $attr['endpoint'] ) && !empty( $attr['endpoint'] ) && ( $key == $attr['endpoint'] ) ) {
					// Load Scripts
					$WCFM->library->load_scripts( $key );
					
					// Load Styles
					$WCFM->library->load_styles( $key );
					
					// Load View
					$WCFM->library->load_views( $key, $menu );
				}
			}
		}
		
		echo '</div></div>';
	}
}