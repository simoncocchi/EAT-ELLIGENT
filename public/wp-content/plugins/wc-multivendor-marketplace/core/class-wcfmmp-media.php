<?php
/**
 * WCFM plugin core
 *
 * WCFM Media Manager core
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Media {

	public function __construct() {
		global $WCFM, $WCFMmp;
		
		// WCFM Media Query Var Filter
		add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_media_query_vars' ), 10 );
		add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_media_endpoint_title' ), 10, 2 );
		add_action( 'init', array( &$this, 'wcfm_media_init' ), 120 );
		
		// WCFMu Media Load WCFMu Scripts
		add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_media_load_scripts' ), 10 );
		add_action( 'after_wcfm_load_scripts', array( &$this, 'wcfm_media_load_scripts' ), 10 );
		
		// WCFMu Media Load WCFMu Styles
		add_action( 'wcfm_load_styles', array( &$this, 'wcfm_media_load_styles' ), 10 );
		add_action( 'after_wcfm_load_styles', array( &$this, 'wcfm_media_load_styles' ), 10 );
		
		// WCFMu Media Load WCFMu views
		add_action( 'wcfm_load_views', array( &$this, 'wcfm_media_load_views' ), 10 );
		
		// WCFMu Media Ajax Controller
		add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_media_ajax_controller' ) );
		
		// Media menu on WCfM dashboard
		if( apply_filters( 'wcfm_is_allow_media', true ) ) {
			add_filter( 'wcfm_menus', array( &$this, 'wcfm_media_menus' ), 30 );
		}
		
		// Media Delete
		add_action( 'wp_ajax_wcfmmp_media_delete', array( &$this, 'wcfmmp_media_delete' ) );
		
		// Bulk Media Delete
		add_action( 'wp_ajax_wcfmmp_bulk_media_delete', array( &$this, 'wcfmmp_bulk_media_delete' ) );
		
	}
	
	/**
   * WCfM Media Query Var
   */
  function wcfm_media_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_wcfm_vars = array(
			'wcfm-media'        => ! empty( $wcfm_modified_endpoints['wcfm-media'] ) ? $wcfm_modified_endpoints['wcfm-media'] : 'media',
		);
		$query_vars = array_merge( $query_vars, $query_wcfm_vars );
		
		return $query_vars;
  }
  
  /**
   * WCfM Media End Point Title
   */
  function wcfm_media_endpoint_title( $title, $endpoint ) {
  	
  	switch ( $endpoint ) {
			case 'wcfm-media' :
				$title = __( 'Media', 'wc-multivendor-marketplace' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCfM Media Endpoint Intialize
   */
  function wcfm_media_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		//if( !get_option( 'wcfm_updated_end_point_payment' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_media', 1 );
		//}
  }
  
	/**
   * WCfM Media Media Menu
   */
  function wcfm_media_menus( $menus ) {
  	global $WCFM;
  		
		$menus = array_slice($menus, 0, 3, true) +
												array( 'wcfm-media' => array( 'label'  => __( 'Media', 'wc-multivendor-marketplace' ),
																										 'url'        => wcfm_media_url(),
																										 'icon'       => 'images',
																										 'priority'   => 3
																										) )	 +
													array_slice($menus, 3, count($menus) - 3, true) ;
  	return $menus;
  }
  
	/**
   * WCfM Media Scripts
   */
  public function wcfm_media_load_scripts( $end_point ) {
	  global $WCFM, $WCFMmp;
    
	  switch( $end_point ) {
      case 'wcfm-media':
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	wp_enqueue_script( 'wcfm_media_js', $WCFMmp->library->js_lib_url . 'media/wcfmmp-script-media.js', array('jquery'), $WCFMmp->version, true );
      	
      	$wcfm_screen_manager_data = array();
    		if( wcfm_is_vendor() ) {
	    		$wcfm_screen_manager_data[4] = 'yes';
	    	}
	    	$wcfm_screen_manager_data = apply_filters( 'wcfm_media_screen_manage', $wcfm_screen_manager_data );
	    	wp_localize_script( 'wcfm_media_js', 'wcfm_media_screen_manage', $wcfm_screen_manager_data );
      break;
      
	  }
	}
	
	/**
   * WCfM Media Styles
   */
	public function wcfm_media_load_styles( $end_point ) {
	  global $WCFM, $WCFMmp;
		
	  switch( $end_point ) {
		  case 'wcfm-media':
				wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
				wp_enqueue_style( 'wcfm_media_css',  $WCFMmp->library->css_lib_url . 'media/wcfmmp-style-media.css', array(), $WCFMmp->version );
		  break;
	  }
	}
	
	/**
   * WCfM Media Views
   */
  public function wcfm_media_load_views( $end_point ) {
	  global $WCFM, $WCFMmp;
	  
	  switch( $end_point ) {
      case 'wcfm-media':
      	$WCFMmp->template->get_template( 'media/wcfmmp-view-media.php' );
      break;
	  }
	}
	
	/**
   * WCfM Media Ajax Controllers
   */
  public function wcfm_media_ajax_controller() {
  	global $WCFM, $WCFMmp;
  	
  	$controllers_path = $WCFMmp->plugin_path . 'controllers/media/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = sanitize_text_field( $_POST['controller'] );
  		switch( $controller ) {
  			case 'wcfm-media':
					include_once( $controllers_path . 'wcfmmp-controller-media.php' );
					new WCFMmp_Media_Controller();
  			break;
  		}
  	}
  }
  
  /**
   * WCfM Media Delete
   */
  function wcfmmp_media_delete() {
  	global $WCFM, $WCFMmp, $_POST, $wpdb;
  	
  	$mediaid = absint($_POST['mediaid']);
  	
  	if( $mediaid ) {
  		if( wp_delete_post( $mediaid, true ) ) {
  			echo 'success';
  		} else {
  			echo 'failed';	
  		}
  	} else {
  		echo 'failed';
  	}
  	die;
  }
  
  /**
   * WCfM Media Bulk Delete
   *
   * @since 1.1.2
   */
  function wcfmmp_bulk_media_delete() {
  	global $WCFM, $wpdb, $_POST;
  	
  	if( isset($_POST['selected_media']) ) {
			$selected_medias = wp_unslash($_POST['selected_media']);
			if( is_array( $selected_medias ) && !empty( $selected_medias ) ) {
				foreach( $selected_medias as $mediaid ) {
					if( wp_delete_post( $mediaid, true ) ) {
						// Do anything
					}
				}
			}
		}
		echo '{ "status": true }';
		die;
	}
}