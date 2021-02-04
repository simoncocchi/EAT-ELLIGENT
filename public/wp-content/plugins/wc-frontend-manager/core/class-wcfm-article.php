<?php
/**
 * WCFM plugin core
 *
 * Plugin Article Support Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   3.4.6
 */
 
class WCFM_Article {

	public function __construct() {
		global $WCFM;
		
		if ( !is_admin() || defined('DOING_AJAX') ) {
			if( $is_allow_articles = apply_filters( 'wcfm_is_allow_articles', true ) ) {
				// WC Article Query Var Filter
				add_filter( 'wcfm_query_vars', array( &$this, 'articles_wcfm_query_vars' ), 20 );
				add_filter( 'wcfm_endpoint_title', array( &$this, 'articles_wcfm_endpoint_title' ), 20, 2 );
				add_action( 'init', array( &$this, 'articles_wcfm_init' ), 20 );
				
				// WCFM Article Endpoint Edit
				add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfm_articles_endpoints_slug' ) );
				
				// WC Article Menu Filter
				add_filter( 'wcfm_menus', array( &$this, 'articles_wcfm_menus' ), 20 );
				
				// Articles Load WCFMu Scripts
				add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_articles_load_scripts' ), 30 );
				
				// Articles Load WCFMu Styles
				add_action( 'wcfm_load_styles', array( &$this, 'wcfm_articles_load_styles' ), 30 );
				
				// Articles Load WCFMu views
				add_action( 'wcfm_load_views', array( &$this, 'wcfm_articles_load_views' ), 30 );
				
				// Articles Ajax Controllers
				add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_articles_ajax_controller' ), 30 );
				
				// Article Delete
				add_action( 'wp_ajax_delete_wcfm_article', array( &$this, 'delete_wcfm_article' ) );
			}
		}
	}
	
	/**
   * WCFM Articles Query Var
   */
  function articles_wcfm_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_articles_vars = array(
			'wcfm-articles'                 => ! empty( $wcfm_modified_endpoints['wcfm-articles'] ) ? $wcfm_modified_endpoints['wcfm-articles'] : 'articles',
			'wcfm-articles-manage'          => ! empty( $wcfm_modified_endpoints['wcfm-articles-manage'] ) ? $wcfm_modified_endpoints['wcfm-articles-manage'] : 'articles-manage',
		);
		
		$query_vars = array_merge( $query_vars, $query_articles_vars );
		
		return $query_vars;
  }
  
  /**
   * WCFM Articles End Point Title
   */
  function articles_wcfm_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-articles' :
				$title = __( 'Articles Dashboard', 'wc-frontend-manager' );
			break;
			case 'wcfm-articles-manage' :
				$title = __( 'Articles Manager', 'wc-frontend-manager' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCFM Articles Endpoint Intialize
   */
  function articles_wcfm_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_wcfm_articles' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_wcfm_articles', 1 );
		}
  }
  
  /**
	 * WCFM Articles Endpoiint Edit
	 */
  function wcfm_articles_endpoints_slug( $endpoints ) {
		
		$articles_endpoints = array(
													'wcfm-articles'  		      => 'articles',
													'wcfm-articles-manage'  	=> 'articles-manage',
													);
		$endpoints = array_merge( $endpoints, $articles_endpoints );
		
		return $endpoints;
	}
  
  /**
   * WCFM Articles Menu
   */
  function articles_wcfm_menus( $menus ) {
  	global $WCFM;
  	
		$articles_menus = array( 'wcfm-articles' => array(   'label'  => __( 'Articles', 'wc-frontend-manager'),
																												 'url'       => get_wcfm_articles_url(),
																												 'icon'      => 'file-alt',
																												 'has_new'    => 'yes',
																												 'new_class'  => 'wcfm_sub_menu_items_article_manage',
																												 'new_url'    => get_wcfm_articles_manage_url(),
																												 'capability' => 'wcfm_article_menu',
																												 'submenu_capability' => 'wcfm_add_new_article_sub_menu',
																												 'priority'  => 4
																												) );
		
		$menus = array_merge( $menus, $articles_menus );
  	return $menus;
  }
  
  /**
   * Articles Scripts
   */
  public function wcfm_articles_load_scripts( $end_point ) {
	  global $WCFM;
    
	  switch( $end_point ) {
	  	case 'wcfm-articles':
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_select2_lib();
	    	wp_enqueue_script( 'wcfm_articles_js', $WCFM->library->js_lib_url . 'articles/wcfm-script-articles.js', array('jquery', 'dataTables_js'), $WCFM->version, true );
	    	
	    	// Screen manager
	    	$wcfm_screen_manager = get_option( 'wcfm_screen_manager', array() );
	    	$wcfm_screen_manager_data = array();
	    	if( isset( $wcfm_screen_manager['article'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['article'];
	    	if( !isset( $wcfm_screen_manager_data['admin'] ) ) {
					$wcfm_screen_manager_data['admin'] = $wcfm_screen_manager_data;
					$wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
				}
				if( wcfm_is_vendor() ) {
					$wcfm_screen_manager_data = $wcfm_screen_manager_data['vendor'];
					$wcfm_screen_manager_data[5] = 'yes';
				} else {
					$wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
				}
				$wcfm_screen_manager_data    = apply_filters( 'wcfm_screen_manager_data_columns', $wcfm_screen_manager_data, 'articles' );
	    	wp_localize_script( 'wcfm_articles_js', 'wcfm_articles_screen_manage', $wcfm_screen_manager_data );
      break;
      
      case 'wcfm-articles-manage':
      	if( !apply_filters( 'wcfm_is_allow_article_wpeditor', 'wpeditor' ) ) {
      		$WCFM->library->load_tinymce_lib();
      	}
      	$WCFM->library->load_upload_lib();
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_collapsible_lib();
	  		wp_enqueue_script( 'wcfm_articles_manage_js', $WCFM->library->js_lib_url . 'articles/wcfm-script-articles-manage.js', array('jquery'), $WCFM->version, true );
	  		
	  		// Localized Script
        $wcfm_messages = get_wcfm_articles_manager_messages();
			  wp_localize_script( 'wcfm_articles_manage_js', 'wcfm_articles_manage_messages', $wcfm_messages );
	  	break;
	  }
	}
	
	/**
   * Articles Styles
   */
	public function wcfm_articles_load_styles( $end_point ) {
	  global $WCFM, $WCFMu;
		
	  switch( $end_point ) {
	    case 'wcfm-articles':
	    	wp_enqueue_style( 'wcfm_articles_css',  $WCFM->library->css_lib_url . 'articles/wcfm-style-articles.css', array(), $WCFM->version );
		  break;
		  
		  case 'wcfm-articles-manage':
		  	wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
	    	wp_enqueue_style( 'wcfm_articles_manage_css',  $WCFM->library->css_lib_url . 'articles/wcfm-style-articles-manage.css', array(), $WCFM->version );
		  break;
	  }
	}
	
	/**
   * Articles Views
   */
  public function wcfm_articles_load_views( $end_point ) {
	  global $WCFM, $WCFMu;
	  
	  switch( $end_point ) {
	  	case 'wcfm-articles':
        $WCFM->template->get_template( 'articles/wcfm-view-articles.php' );
      break;
      
      case 'wcfm-articles-manage':
        $WCFM->template->get_template( 'articles/wcfm-view-articles-manage.php' );
      break;
	  }
	}
	
	/**
   * Articles Ajax Controllers
   */
  public function wcfm_articles_ajax_controller() {
  	global $WCFM, $WCFMu;
  	
  	$controllers_path = $WCFM->plugin_path . 'controllers/articles/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = wc_clean( $_POST['controller'] );
  		
  		switch( $controller ) {
  			case 'wcfm-articles':
					include_once( $controllers_path . 'wcfm-controller-articles.php' );
					new WCFM_Articles_Controller();
				break;
				
				case 'wcfm-articles-manage':
					include_once( $controllers_path . 'wcfm-controller-articles-manage.php' );
					new WCFM_Articles_Manage_Controller();
				break;
  		}
  	}
  }
  
  /**
   * Handle Article Delete
   */
  public function delete_wcfm_article() {
  	global $WCFM;
  	
  	$articleid = absint( $_POST['articleid'] );
		
		if( $articleid ) {
			do_action( 'wcfm_before_article_delete', $articleid );
			if( apply_filters( 'wcfm_is_allow_article_delete' , false ) ) {
				if(wp_delete_post($articleid)) {
					echo 'success';
					die;
				}
			} else {
				if(wp_trash_post($articleid)) {
					echo 'success';
					die;
				}
			}
			die;
		}
  }
}