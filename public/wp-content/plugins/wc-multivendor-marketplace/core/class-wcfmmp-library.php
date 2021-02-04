<?php

/**
 * WCFMmp plugin library
 *
 * Plugin intiate library
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Library {
	
	public $lib_path;
  
  public $lib_url;
  
  public $php_lib_path;
  
  public $php_lib_url;
  
  public $js_lib_path;
  
  public $js_lib_url;
  
  public $css_lib_path;
  
  public $css_lib_url;
  
  public $views_path;
	
	public function __construct() {
    global $WCFMmp;
		
	  $this->lib_path = $WCFMmp->plugin_path . 'assets/';

    $this->lib_url = $WCFMmp->plugin_url . 'assets/';
    
    $this->php_lib_path = $this->lib_path . 'php/';
    
    $this->php_lib_url = $this->lib_url . 'php/';
    
    $this->js_lib_path = $this->lib_path . 'js/';
    
    $this->js_lib_url = $this->lib_url . 'js/';
    
    $this->js_lib_url_min = $this->js_lib_url . 'min/';
    
    $this->css_lib_path = $this->lib_path . 'css/';
    
    $this->css_lib_url = $this->lib_url . 'css/';
    
    $this->css_lib_url_min = $this->css_lib_url . 'min/';
    
    $this->views_path = $WCFMmp->plugin_path . 'views/';
    
    // Load wcfmmp Scripts
    add_action( 'wcfm_load_scripts', array( &$this, 'load_scripts' ) );
    add_action( 'after_wcfm_load_scripts', array( &$this, 'load_scripts' ) );
    
    // Load wcfmmp Styles
    add_action( 'wcfm_load_styles', array( &$this, 'load_styles' ) );
    add_action( 'after_wcfm_load_styles', array( &$this, 'load_styles' ) );
    
    // Load wcfmmp views
    add_action( 'wcfm_load_views', array( &$this, 'load_views' ) );
  }
  
  public function load_scripts( $end_point ) {
	  global $WCFM, $WCFMmp;
    
	  switch( $end_point ) {
	  	
	    case 'wcfm-dashboard':
	    	
	  	break;
	  	
	  	case 'wcfm-products-manage':
	  		if( !wcfm_is_vendor() ) {
	  			wp_enqueue_script( 'wcfmmp_settings_js', $this->js_lib_url . 'wcfmmp-script-settings.js', array('jquery'), $WCFMmp->version, true );
	  		}
      break;
	  	
	  	case 'wcfm-settings':
	  		if( !wcfm_is_vendor() ) {
	  			wp_enqueue_script( 'wcfmmp_settings_js', $this->js_lib_url . 'wcfmmp-script-settings.js', array('jquery'), $WCFMmp->version, true );
	  			
	  			$wcfm_store_color_setting_options = $WCFMmp->wcfmmp_settings->wcfmmp_store_color_setting_options();
					wp_localize_script( 'wcfmmp_settings_js', 'wcfm_store_color_setting_options', $wcfm_store_color_setting_options );
					
					wp_enqueue_script( 'jquery-ui-autocomplete' );
					$this->load_map_lib();
					
					// Default Map Location
					$default_geolocation = isset( $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] ) ? $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] : array();
					$default_lat         = isset( $default_geolocation['lat'] ) ? esc_attr( $default_geolocation['lat'] ) : apply_filters( 'wcfmmp_map_default_lat', 30.0599153 );
					$default_lng         = isset( $default_geolocation['lng'] ) ? esc_attr( $default_geolocation['lng'] ) : apply_filters( 'wcfmmp_map_default_lng', 31.2620199 );
					$default_zoom        =  apply_filters( 'wcfmmp_map_default_zoom_level', 15 );
					
					$store_icon = apply_filters( 'wcfmmp_map_store_icon', $WCFMmp->plugin_url . 'assets/images/wcfmmp_map_icon.png', 0, '' );
					
					wp_localize_script( 'wcfmmp_settings_js', 'wcfmmp_setting_map_options', array( 'search_location' => __( 'Insert your address ..', 'wc-multivendor-marketplace' ), 'is_geolocate' => apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ), 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'store_icon' => $store_icon, 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_rtl' => is_rtl() ) );
					
					//$wcfm_marketplace_options = $WCFMmp->wcfmmp_marketplace_options;
					//$api_key = isset( $wcfm_marketplace_options['wcfm_google_map_api'] ) ? $wcfm_marketplace_options['wcfm_google_map_api'] : '';
					//if ( $api_key ) {
						//$scheme  = is_ssl() ? 'https' : 'http';
						//wp_enqueue_script( 'jquery-ui-autocomplete' );
						//wp_enqueue_script( 'wcfmmp-setting-google-maps', $scheme . '://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places' );
					//}
	  		}
      break;
      
      case 'wcfm-memberships-manage':
      case 'wcfm-vendors-new': 
	  		if( !wcfm_is_vendor() ) {
	  			$WCFM->library->load_multiinput_lib();
	  			wp_enqueue_script( 'wcfmmp_settings_js', $this->js_lib_url . 'wcfmmp-script-settings.js', array('jquery'), $WCFMmp->version, true );
	  		}
      break;
      
      case 'wcfm-vendors-manage':  
      	if( !wcfm_is_vendor() ) {
	  			$WCFM->library->load_multiinput_lib();
	  			wp_enqueue_script( 'wcfmmp_settings_js', $this->js_lib_url . 'wcfmmp-script-settings.js', array('jquery'), $WCFMmp->version, true );
	  			
	  			$WCFM->library->load_datatable_lib();
	  			$WCFM->library->load_daterangepicker_lib();
	  			$WCFM->library->load_datatable_download_lib();
	  			wp_enqueue_script( 'wcfmmp_vendors_manager_js', $this->js_lib_url . 'vendors/wcfmmp-script-vendors-manage.js', array('jquery', 'dataTables_js'), $WCFMmp->version, true );
	  			
	  			// Order Columns Defs
					$wcfm_datatable_column_defs = '[{ "targets": 0, "orderable" : false }, { "targets": 1, "orderable" : false }, { "targets": 2, "orderable" : false }, { "targets": 3, "orderable" : false }, { "targets": 4, "orderable" : false },{ "targets": 5, "orderable" : false },{ "targets": 6, "orderable" : false },{ "targets": 7, "orderable" : false },{ "targets": 8, "orderable" : false },{ "targets": 9, "orderable" : false },{ "targets": 10, "orderable" : false },{ "targets": 11, "orderable" : false },{ "targets": 12, "orderable" : false }]';
																		
					$wcfm_datatable_column_defs = apply_filters( 'wcfm_datatable_column_defs', $wcfm_datatable_column_defs, 'order' );
					
					// Order Columns Priority
					$wcfm_datatable_column_priority = '[{ "responsivePriority": 2 },{ "responsivePriority": 1 },{ "responsivePriority": 4 },{ "responsivePriority": 10 },{ "responsivePriority": 6 },{ "responsivePriority": 5 },{ "responsivePriority": 7 },{ "responsivePriority": 11 },{ "responsivePriority": 3 },{ "responsivePriority": 12 },{ "responsivePriority": 8 },{ "responsivePriority": 9 },{ "responsivePriority": 1 }]';
					$wcfm_datatable_column_priority = apply_filters( 'wcfm_datatable_column_priority', $wcfm_datatable_column_priority, 'order' );
					
					wp_localize_script( 'dataTables_js', 'wcfm_datatable_columns', array( 'defs' => $wcfm_datatable_column_defs, 'priority' => $wcfm_datatable_column_priority ) );
					
					// Screen manager
					$wcfm_screen_manager = get_option( 'wcfm_screen_manager', array() );
					$wcfm_screen_manager_data = array();
					if( isset( $wcfm_screen_manager['order'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['order'];
					if( !isset( $wcfm_screen_manager_data['admin'] ) ) {
						$wcfm_screen_manager_data['admin'] = $wcfm_screen_manager_data;
						$wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
					}
					
					$wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
					if( !$WCFM->is_marketplace || !apply_filters( 'wcfm_is_allow_view_commission', true ) ) {
						$wcfm_screen_manager_data[8] = 'yes';
					}
					if( apply_filters( 'wcfm_orders_additonal_data_hidden', true ) ) {
						$wcfm_screen_manager_data[10] = 'yes';
					}
					if( WCFM_Dependencies::wcfmd_plugin_active_check() ) {
						//$wcfm_screen_manager_data[13] = 'yes';
					} else {
						//$wcfm_screen_manager_data[12] = 'yes';
					}
					$wcfm_screen_manager_data    = apply_filters( 'wcfm_screen_manager_data_columns', $wcfm_screen_manager_data, 'vendor-orders' );
					wp_localize_script( 'wcfmmp_vendors_manager_js', 'wcfm_orders_screen_manage', $wcfm_screen_manager_data );
					
					$wcfm_screen_manager_hidden_data = array();
					$wcfm_screen_manager_hidden_data[3] = 'yes';
					$wcfm_screen_manager_hidden_data[7] = 'yes';
					$wcfm_screen_manager_hidden_data[9] = 'yes';
					$wcfm_screen_manager_hidden_data    = apply_filters( 'wcfm_screen_manager_hidden_columns', $wcfm_screen_manager_hidden_data );
					wp_localize_script( 'wcfmmp_vendors_manager_js', 'wcfm_orders_screen_manage_hidden', $wcfm_screen_manager_hidden_data );
					
					wp_localize_script( 'wcfmmp_vendors_manager_js', 'wcfm_orders_auto_refresher', array( 'is_allow' => apply_filters( 'wcfm_orders_is_allow_auto_refresher', false ) ) );
	  		}
      break;
	  	
    }
  }
  
  public function load_styles( $end_point ) {
	  global $WCFM, $WCFMmp;
		
	  switch( $end_point ) {
	  	
	  	case 'wcfm-memberships-manage':
	    	// wp_enqueue_style( 'wcfm_settings_css',  $WCFM->library->css_lib_url . 'settings/wcfm-style-settings.css', array(), $WCFM->version );
		  break;
		  
		  case 'wcfm-vendors-manage':  
		    wp_enqueue_style( 'wcfm_orders_css',  $WCFM->library->css_lib_url . 'orders/wcfm-style-orders.css', array(), $WCFM->version );
		  break;
		}
	}
	
	public function load_views( $end_point ) {
	  global $WCFM, $WCFMmp;
	  
	  switch( $end_point ) {
	  	
	  	case 'wcfm-analytics':
        //$WCFMmp->template->get_template( 'wcfmmp-view-analytics.php' );
      break;
    }
  }
  
  /**
	 * WCFM Map library
	*/
	public function load_map_lib() {
	  global $WCFM, $WCFMmp;
	  
	  $api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
	  if( ($wcfm_map_lib == 'google') && !empty( $api_key ) ) {
			$this->load_google_map_lib();
		} else {
			$this->load_leaflet_map_lib();
			$this->load_leaflet_search_lib();
		}
	}
  
  /**
	 * Google Map library
	*/
	public function load_google_map_lib() {
	  global $WCFM, $WCFMmp;
	  $api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
	  if( $api_key ) {
	  	$scheme  = is_ssl() ? 'https' : 'http';
	  	wp_enqueue_script( 'wcfm-store-google-maps', apply_filters( 'wcfm_google_map_api_url', $scheme . '://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places', $api_key ) );
	  	wp_localize_script( 'wcfm-store-google-maps', 'wcfm_maps', array( 'lib' => 'google', 'map_type' => apply_filters( 'wcfm_google_map_type', 'roadmap' ) ) );
	  	
	  	if( apply_filters( 'wcfmmp_is_allow_map_pointer_cluster', true ) ) {
	  		wp_enqueue_script( 'wcfm-store-google-maps-cluster', 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js', array('jquery' ), $WCFMmp->version, true );
	  	}
	  }
	}
  
  
  /**
	 * Leaflet Map library
	*/
	public function load_leaflet_map_lib() {
	  global $WCFM, $WCFMmp;
	  wp_enqueue_script( 'wcfm-leaflet-map-js', $WCFM->plugin_url . 'includes/libs/leaflet/leaflet.js', array('jquery'), $WCFM->version, true );
	  wp_enqueue_style( 'wcfm-leaflet-map-style', $WCFM->plugin_url . 'includes/libs/leaflet/leaflet.css', array(), $WCFM->version );
	  wp_localize_script( 'wcfm-leaflet-map-js', 'wcfm_maps', array( 'lib' => 'leaflet', 'map_type' => apply_filters( 'wcfm_google_map_type', 'roadmap' ) ) );
	}
	
	/**
	 * Leaflet Search library
	*/
	public function load_leaflet_search_lib() {
	  global $WCFM;
	  wp_enqueue_script( 'wcfm-leaflet-search-js', $WCFM->plugin_url . 'includes/libs/leaflet/leaflet-search.js', array('jquery'), $WCFM->version, true );
	  wp_enqueue_style( 'wcfm-leaflet-search-style', $WCFM->plugin_url . 'includes/libs/leaflet/leaflet-search.css', array(), $WCFM->version );
	}
}