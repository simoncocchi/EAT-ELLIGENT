<?php
/**
 * WCFM plugin core
 *
 * Plugin Product Popup Support Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   4.1.0
 */
 
class WCFM_Product_Popup {

	public function __construct() {
		global $WCFM;
		
		if ( !is_admin() || defined('DOING_AJAX') ) {
			if( apply_filters( 'wcfm_is_pref_restriction_check', true ) ) {
				if( apply_filters( 'wcfm_is_allow_manage_products', true ) ) {
					if( apply_filters( 'wcfm_is_allow_add_products', true ) && apply_filters( 'wcfm_is_allow_pm_add_products', true ) ) {
						if( apply_filters( 'wcfm_is_allow_product_popup', true ) ) {
							if( apply_filters( 'wcfm_is_allow_product_limit', true ) ) {
								if( apply_filters( 'wcfm_is_allow_space_limit', true ) ) {
				
									// Product Popup Load WCFM Scripts
									add_action( 'after_wcfm_load_scripts', array( &$this, 'wcfm_product_popup_load_scripts' ), 30 );
									
									// Product Popup Load WCFM Styles
									add_action( 'after_wcfm_load_styles', array( &$this, 'wcfm_product_popup_load_styles' ), 30 );
									
									// Product Popup Load WCFM views
									add_action( 'wcfm_load_views', array( &$this, 'wcfm_product_popup_load_views' ), 300 );
									
									add_action( 'wcfm_main_contentainer_after', array( &$this, 'wcfm_product_popup_botton' ), 10, 1 );
								}
							}
						}
					}
				}
			}
		}
	}
	
	function get_wcfm_blocked_product_popup_views() {
		return apply_filters( 'wcfm_blocked_product_popup_views', array( 'wcfm-products-manage', 
																																		 'wcfm-articles-manage', 
																																		 'wcfm-coupons-manage',
																																		 'wcfm-orders-manage',
																																		 'wcfm-orders-details',
																																		 'wcfm-customers-manage',
																																		 'wcfm-customers-details',
																																		 'wcfm-vendors-new',
																																		 'wcfm-vendors-manage',
																																		 'wcfm-managers-manage',
																																		 'wcfm-staffs-manage',
																																		 'wcfm-groups-manage',
																																		 'wcfm-memberships-manage',
																																		 'wcfm-memberships-settings',
																																		 'wcfm-bookings-resources-manage',
																																		 'wcfm-bookings-manual',
																																		 'wcfm-bookings-settings',
																																		 'wcfm-booking-resources-manage',
																																		 'wcfm-booking-manual',
																																		 'wcfm-booking-settings',
																																		 'wcfm-appointments-staffs-manage',
																																		 'wcfm-appointments-manual',
																																		 'wcfm-appointments-settings',
																																		 'wcfm-enquiry-manage',
																																		 'wcfm-support-manage',
																																		 'wcfm-notice-manage',
																																		 'wcfm-notice-view',
																																		 'wcfm-knowledgebase-manage',
																																		 'wcfm-messages',
																																		 'wcfm-capability',
																																		 'wcfm-settings', 
																																		 'wcfm-profile',
																																		 'wcfm-withdrawal',
																																		 'wcfm-refund-requests',
																																		 //'wcfm-products-import',
																																		 //'wcfm-products-export',
																																		 'wcfm-delivery-boys-manage',
																																		 'wcfm-delivery-boys-stats',
																																		 'wcfm-analytics',
																																		 //'wcfm-listings-stats',
																																		 'wcfm-fncy-product-designer',
																																		 'wcfm-fncy-product-builder',
																																		 ) );
	}
	
	/**
   * Product_popup Scripts
   */
  public function wcfm_product_popup_load_scripts( $end_point ) {
	  global $WCFM;
    
	  $wcfm_blocked_product_popup_views = $this->get_wcfm_blocked_product_popup_views();
	  
	  if( !in_array( $end_point, $wcfm_blocked_product_popup_views ) ) {
	  	$WCFM->library->load_scripts( 'wcfm-products-manage' );
	  	wp_enqueue_script( 'wcfm_product_popup_js', $WCFM->library->js_lib_url . 'products-popup/wcfm-script-product-popup.js', array('jquery', 'wcfm_products_manage_js'), $WCFM->version, true );
	  }
	}
	
	/**
   * Product_popup Styles
   */
	public function wcfm_product_popup_load_styles( $end_point ) {
	  global $WCFM, $WCFMu;
		
	  $wcfm_blocked_product_popup_views = $this->get_wcfm_blocked_product_popup_views();
	  
	  if( !in_array( $end_point, $wcfm_blocked_product_popup_views ) ) {
	  	$WCFM->library->load_styles( 'wcfm-products-manage' );
	  	$wcfm_options = $WCFM->wcfm_options;
	  	// Load Float Button Style
	  	//$is_float_button_disabled = isset( $wcfm_options['float_button_disabled'] ) ? $wcfm_options['float_button_disabled'] : 'no';
	  	//if( $is_float_button_disabled != 'yes' ) {
	  		//wp_enqueue_style( 'wcfm_float_button_css',  $WCFM->library->css_lib_url . 'wcfm-style-float-button.css', array( 'wcfm_menu_css' ), $WCFM->version );
	  	//}
	    wp_enqueue_style( 'wcfm_product_popup_css',  $WCFM->library->css_lib_url . 'products-popup/wcfm-style-product-popup.css', array( 'wcfm_products_manage_css' ), $WCFM->version );
		}
	}
	
	/**
   * Product_popup Views
   */
  public function wcfm_product_popup_load_views( $end_point ) {
	  global $WCFM, $WCFMu;
	  
	  $wcfm_blocked_product_popup_views = $this->get_wcfm_blocked_product_popup_views();
	  
	  if( !in_array( $end_point, $wcfm_blocked_product_popup_views ) ) {
      //$WCFM->template->get_template( 'products-popup/wcfm-view-product-popup.php' );
    }
  }
	
	function wcfm_product_popup_botton( $end_point ) {
		global $WCFM;
		
		$wcfm_blocked_product_popup_views = $this->get_wcfm_blocked_product_popup_views();
		
		if( !in_array( $end_point, $wcfm_blocked_product_popup_views ) ) {
			$WCFM->template->get_template( 'products-popup/wcfm-view-product-popup-button.php' );
			$WCFM->template->get_template( 'products-popup/wcfm-view-product-popup.php' );
		}
	}
}