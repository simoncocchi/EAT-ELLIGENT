<?php
/**
 * WCFM plugin core
 *
 * WCFM Reviews core
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Reviews {

	public function __construct() {
		global $WCFM, $WCFMmp;
		
		// WCFM Reviews Query Var Filter
		add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_reviews_query_vars' ), 10 );
		add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_reviews_endpoint_title' ), 10, 2 );
		add_action( 'init', array( &$this, 'wcfm_reviews_init' ), 120 );
		
		// WCFMu Reviews Load WCFMu Scripts
		add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_reviews_load_scripts' ), 10 );
		add_action( 'after_wcfm_load_scripts', array( &$this, 'wcfm_reviews_load_scripts' ), 10 );
		
		// WCFMu Reviews Load WCFMu Styles
		add_action( 'wcfm_load_styles', array( &$this, 'wcfm_reviews_load_styles' ), 10 );
		add_action( 'after_wcfm_load_styles', array( &$this, 'wcfm_reviews_load_styles' ), 10 );
		
		// WCFMu Reviews Load WCFMu views
		add_action( 'wcfm_load_views', array( &$this, 'wcfm_reviews_load_views' ), 10 );
		
		// WCFMu Reviews Ajax Controller
		add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_reviews_ajax_controller' ) );
		
		// Reviews menu on WCfM dashboard
		if( apply_filters( 'wcfm_is_allow_reviews', true ) ) {
			add_filter( 'wcfm_menus', array( &$this, 'wcfm_reviews_menus' ), 30 );
		}
		
		// Reviews Status Update 
		add_action( 'wp_ajax_wcfmmp_reviews_status_update', array( &$this, 'wcfmmp_reviews_status_update' ) );
		
		// Product Reviews Status Update 
		add_action( 'wp_ajax_wcfmmp_product_reviews_status_update', array( &$this, 'wcfmmp_product_reviews_status_update' ) );
		
		// Reviews Delete
		add_action( 'wp_ajax_wcfmmp_reviews_delete', array( &$this, 'wcfmmp_reviews_delete' ) );
		
		// Check wheather current user can add review to the store
		add_filter( 'wcfm_is_allow_new_review', array( &$this, 'wcfmmp_check_new_review_permission' ), 10, 2 );
		
		// Vendor Product comment as Store Review
		add_action( 'comment_post', array( &$this, 'wcfmmp_add_store_review' ), 50 );
		
		// Reviews direct message type
		add_filter( 'wcfm_message_types', array( &$this, 'wcfmmp_reviews_message_types' ), 110 );
		
		// Is allow Store Review Rating
		add_filter( 'wcfm_is_allow_review_rating', array( &$this, 'wcfmmp_is_allow_review_rating' ) );
	}
	
	/**
   * WCfM Reviews Query Var
   */
  function wcfm_reviews_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_wcfm_vars = array(
			'wcfm-reviews'           => ! empty( $wcfm_modified_endpoints['wcfm-reviews'] ) ? $wcfm_modified_endpoints['wcfm-reviews'] : 'reviews',
			'wcfm-product-reviews'   => ! empty( $wcfm_modified_endpoints['wcfm-product-reviews'] ) ? $wcfm_modified_endpoints['wcfm-product-reviews'] : 'product-reviews',
			'wcfm-reviews-manage'    => ! empty( $wcfm_modified_endpoints['wcfm-reviews-manage'] ) ? $wcfm_modified_endpoints['wcfm-reviews-manage'] : 'reviews-manage',
		);
		$query_vars = array_merge( $query_vars, $query_wcfm_vars );
		
		return $query_vars;
  }
  
  /**
   * WCfM Reviews End Point Title
   */
  function wcfm_reviews_endpoint_title( $title, $endpoint ) {
  	
  	switch ( $endpoint ) {
			//case 'wcfm-payments' :
				//$title = __( 'Payments History', 'wc-multivendor-marketplace' );
			//break;
			
			case 'wcfm-reviews' :
				$title = __( 'Store Reviews', 'wc-multivendor-marketplace' );
			break;
			
			case 'wcfm-product-reviews' :
				$title = __( 'Product Reviews', 'wc-multivendor-marketplace' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCfM Reviews Endpoint Intialize
   */
  function wcfm_reviews_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_reviews' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_reviews', 1 );
		}
  }
  
	/**
   * WCfM Reviews Reviews Menu
   */
  function wcfm_reviews_menus( $menus ) {
  	global $WCFM;
  		
		$menus = array_slice($menus, 0, 3, true) +
												array( 'wcfm-reviews' => array( 'label'  => __( 'Reviews', 'wc-multivendor-marketplace' ),
																										 'url'        => wcfm_reviews_url(),
																										 'icon'       => 'comment-alt',
																										 'priority'   => 69
																										) )	 +
													array_slice($menus, 3, count($menus) - 3, true) ;
  	return $menus;
  }
  
	/**
   * WCfM Reviews Scripts
   */
  public function wcfm_reviews_load_scripts( $end_point ) {
	  global $WCFM, $WCFMmp;
    
	  switch( $end_point ) {
      case 'wcfm-reviews':
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	wp_enqueue_script( 'wcfm_reviews_js', $WCFMmp->library->js_lib_url . 'reviews/wcfmmp-script-reviews.js', array('jquery'), $WCFMmp->version, true );
      	
      	$wcfm_screen_manager_data = array();
      	if( !apply_filters( 'wcfm_is_allow_review_rating', true ) ) {
      		$wcfm_screen_manager_data[4] = 'yes';
      	}
    		if( wcfm_is_vendor() ) {
	    		$wcfm_screen_manager_data[5] = 'yes';
	    	}
	    	$wcfm_screen_manager_data = apply_filters( 'wcfm_reviews_screen_manage', $wcfm_screen_manager_data );
	    	wp_localize_script( 'wcfm_reviews_js', 'wcfm_reviews_screen_manage', $wcfm_screen_manager_data );
      break;
      
      case 'wcfm-product-reviews':
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	wp_enqueue_script( 'wcfm_reviews_js', $WCFMmp->library->js_lib_url . 'reviews/wcfmmp-script-product-reviews.js', array('jquery'), $WCFMmp->version, true );
      	
      	$wcfm_screen_manager_data = array();
      	if( !apply_filters( 'wcfm_is_allow_review_rating', true ) ) {
      		$wcfm_screen_manager_data[3] = 'yes';
      	}
    		if( wcfm_is_vendor() ) {
	    		$wcfm_screen_manager_data[5] = 'yes';
	    	}
	    	$wcfm_screen_manager_data = apply_filters( 'wcfm_reviews_screen_manage', $wcfm_screen_manager_data );
	    	wp_localize_script( 'wcfm_reviews_js', 'wcfm_reviews_screen_manage', $wcfm_screen_manager_data );
      break;
      
      case 'wcfm-reviews-manage':
      	$WCFM->library->load_datatable_lib();
      	wp_enqueue_script( 'wcfm_reviews_manage_js', $WCFMmp->library->js_lib_url . 'reviews/wcfmmp-script-reviews-manage.js', array('jquery'), $WCFMmp->version, true );
      break;
	  }
	}
	
	/**
   * WCfM Reviews Styles
   */
	public function wcfm_reviews_load_styles( $end_point ) {
	  global $WCFM, $WCFMmp;
		
	  switch( $end_point ) {
		  case 'wcfm-reviews':
				wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
				wp_enqueue_style( 'wcfm_reviews_css',  $WCFMmp->library->css_lib_url . 'reviews/wcfmmp-style-reviews.css', array(), $WCFMmp->version );
		  break;
		  
		  case 'wcfm-product-reviews':
				wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
				wp_enqueue_style( 'wcfm_reviews_css',  $WCFMmp->library->css_lib_url . 'reviews/wcfmmp-style-product-reviews.css', array(), $WCFMmp->version );
		  break;
		  
		  case 'wcfm-reviews-manage':
				wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
				wp_enqueue_style( 'wcfm_reviews_manage_css',  $WCFMmp->library->css_lib_url . 'reviews/wcfmmp-style-reviews-manage.css', array(), $WCFMmp->version );
		  break;
	  }
	}
	
	/**
   * WCfM Reviews Views
   */
  public function wcfm_reviews_load_views( $end_point ) {
	  global $WCFM, $WCFMmp;
	  
	  switch( $end_point ) {
      case 'wcfm-reviews':
      	$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews.php' );
      break;
      
      case 'wcfm-product-reviews':
      	$WCFMmp->template->get_template( 'reviews/wcfmmp-view-product-reviews.php' );
      break;
      
      case 'wcfm-reviews-manage':
      	$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-manage.php' );
      break;
	  }
	}
	
	/**
   * WCfM Reviews Ajax Controllers
   */
  public function wcfm_reviews_ajax_controller() {
  	global $WCFM, $WCFMmp;
  	
  	$controllers_path = $WCFMmp->plugin_path . 'controllers/reviews/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = sanitize_text_field( $_POST['controller'] );
  		switch( $controller ) {
  			case 'wcfm-reviews':
					include_once( $controllers_path . 'wcfmmp-controller-reviews.php' );
					if( defined('WCFM_REST_API_CALL') ) {
						$wcfm_review_object = new WCFMmp_Reviews_Controller();
						return $wcfm_review_object->processing();
          } else {
            new WCFMmp_Reviews_Controller();
          }
  			break;
  			
  			case 'wcfm-product-reviews':
					include_once( $controllers_path . 'wcfmmp-controller-product-reviews.php' );
					if( defined('WCFM_REST_API_CALL') ) {
						$wcfm_review_object = new WCFMmp_Product_Reviews_Controller();
						return $wcfm_review_object->processing();
          } else {
            new WCFMmp_Product_Reviews_Controller();
          }
  			break;
  			
  			case 'wcfm-reviews-manage':
					include_once( $controllers_path . 'wcfmmp-controller-reviews-manage.php' );
					new WCFMmp_Reviews_Manage_Controller();
  			break;
  			
  			case 'wcfm-reviews-submit':
					include_once( $controllers_path . 'wcfmmp-controller-reviews-submit.php' );
					new WCFMmp_Reviews_Submit_Controller();
  			break;
  		}
  	}
  }
  
  /**
   * WCfM Reviews Status Update
   */
  function wcfmmp_reviews_status_update() {
  	global $WCFM, $WCFMmp, $_POST, $wpdb;
  	
  	$reviewid = absint($_POST['reviewid']);
  	$status   = absint($_POST['status']);
  	
  	$wcfm_review_categories = get_wcfm_marketplace_active_review_categories();
  	
  	if( $reviewid ) {
  		$review_data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE `ID`= " . $reviewid ); 
  		$review_meta = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE `type` = 'rating_category' AND `review_id`= " . $reviewid . " ORDER BY ID ASC" );
  		if( $review_data && !empty( $review_data ) && is_object( $review_data ) ) {
				if( $status ) { // On Approve
					$total_review_count = get_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_count', true );
					if( !$total_review_count ) $total_review_count = 0;
					else $total_review_count = absint( $total_review_count );
					$total_review_count++;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_count', $total_review_count );
					
					$total_review_rating = get_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_rating', true );
					if( !$total_review_rating ) $total_review_rating = 0;
					else $total_review_rating = (float) $total_review_rating;
					$total_review_rating += (float) $review_data->review_rating;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_rating', $total_review_rating );
					
					$avg_review_rating = $total_review_rating/$total_review_count;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating );
					
					$wcfm_store_review_categories = array();
					if( !empty( $review_meta ) ) {
						foreach( $review_meta as $review_meta_cat ) {
							$wcfm_store_review_categories[] = $review_meta_cat->value;
						}
					}
					
					$category_review_rating = get_user_meta( $review_data->vendor_id, '_wcfmmp_category_review_rating', true );
					if( !$category_review_rating ) $category_review_rating = array();
					foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
						if( isset( $wcfm_store_review_categories[$wcfm_review_cat_key] ) ) {
							$total_category_review_rating = 0;
							$avg_category_review_rating = 0;
							if( $category_review_rating && !empty( $category_review_rating ) && isset( $category_review_rating[$wcfm_review_cat_key] ) ) {
								$total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
								$avg_category_review_rating   = $category_review_rating[$wcfm_review_cat_key]['avg'];
							}
							$total_category_review_rating += (float) $wcfm_store_review_categories[$wcfm_review_cat_key];
							$avg_category_review_rating    = $total_category_review_rating/$total_review_count;
							$category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = $avg_category_review_rating;
						} else {
							$category_review_rating[$wcfm_review_cat_key]['total'] = 0;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = 0;
						}
					}
					$category_review_rating = update_user_meta( $review_data->vendor_id, '_wcfmmp_category_review_rating', $category_review_rating );
					
					update_user_meta( $review_data->vendor_id, '_wcfmmp_last_author_id', $review_data->author_id );
					update_user_meta( $review_data->vendor_id, '_wcfmmp_last_author_name', $review_data->author_name );
					
					$wpdb->update("{$wpdb->prefix}wcfm_marketplace_reviews", array('approved' => 1), array('ID' => $reviewid), array('%d'), array('%d'));
				} else { // On UnApprove
					$total_review_count = get_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_count', true );
					if( !$total_review_count ) $total_review_count = 0;
					else $total_review_count = absint( $total_review_count );
					if($total_review_count)$total_review_count--;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_count', $total_review_count );
					
					$total_review_rating = get_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_rating', true );
					if( !$total_review_rating ) $total_review_rating = 0;
					else $total_review_rating = (float) $total_review_rating;
					if($total_review_rating) $total_review_rating -= (float) $review_data->review_rating;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_rating', $total_review_rating );
					
					$avg_review_rating = 0;
					if($total_review_rating && $total_review_count) $avg_review_rating = $total_review_rating/$total_review_count;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating );
					
					$wcfm_store_review_categories = array();
					if( !empty( $review_meta ) ) {
						foreach( $review_meta as $review_meta_cat ) {
							$wcfm_store_review_categories[] = $review_meta_cat->value;
						}
					}
					
					$category_review_rating = get_user_meta( $review_data->vendor_id, '_wcfmmp_category_review_rating', true );
					if( !$category_review_rating ) $category_review_rating = array();
					foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
						if( isset( $wcfm_store_review_categories[$wcfm_review_cat_key] ) ) {
							$total_category_review_rating = 0;
							$avg_category_review_rating = 0;
							if( $category_review_rating && !empty( $category_review_rating ) && isset( $category_review_rating[$wcfm_review_cat_key] ) ) {
								$total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
								$avg_category_review_rating   = $category_review_rating[$wcfm_review_cat_key]['avg'];
							}
							if($total_category_review_rating) $total_category_review_rating -= (float) $wcfm_store_review_categories[$wcfm_review_cat_key];
							if( $total_category_review_rating && $total_review_count ) $avg_category_review_rating    = $total_category_review_rating/$total_review_count;
							$category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = $avg_category_review_rating;
						} else {
							$category_review_rating[$wcfm_review_cat_key]['total'] = 0;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = 0;
						}
					}
					$category_review_rating = update_user_meta( $review_data->vendor_id, '_wcfmmp_category_review_rating', $category_review_rating );
					
					$wpdb->update("{$wpdb->prefix}wcfm_marketplace_reviews", array('approved' => 0), array('ID' => $reviewid), array('%d'), array('%d'));
				}
			}
  	}
  	
  	echo 'success';
  	die;
  }
  
  /**
   * WCFM Product Review Status Update
   */
  function wcfmmp_product_reviews_status_update() {
    global $WCFM, $WCFMmp, $_POST, $wpdb;
  	
   	$reviewid = absint($_POST['reviewid']);
		$status   = absint($_POST['status']);
		
		if( $reviewid ) {
			if( $status ) { // On Approve
				if( $status == 2 ) {
					wp_set_comment_status( $reviewid, 'trash' );
				} else {
					wp_set_comment_status( $reviewid, 'approve' );
				}
			} else {
				wp_set_comment_status( $reviewid, 'hold' );
			}
		}
		echo 'success';
  	die;
  }
  
  /**
   * WCfM Reviews Delete
   */
  function wcfmmp_reviews_delete() {
  	global $WCFM, $WCFMmp, $_POST, $wpdb;
  	
  	$reviewid = absint($_POST['reviewid']);
  	
  	$wcfm_review_categories = get_wcfm_marketplace_active_review_categories();
  	
  	if( $reviewid ) {
  		$review_data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE `ID`= " . $reviewid ); 
  		$review_meta = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE `type` = 'rating_category' AND `review_id`= " . $reviewid . " ORDER BY ID ASC" );
  		if( $review_data && !empty( $review_data ) && is_object( $review_data ) ) {
				if( $review_data->approved == 1 ) { // On Approve Review Delete reset Stats
					$total_review_count = get_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_count', true );
					if( !$total_review_count ) $total_review_count = 0;
					else $total_review_count = absint( $total_review_count );
					if($total_review_count) $total_review_count--;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_count', $total_review_count );
					
					$total_review_rating = get_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_rating', true );
					if( !$total_review_rating ) $total_review_rating = 0;
					else $total_review_rating = (float) $total_review_rating;
					if($total_review_rating) $total_review_rating -= (float) $review_data->review_rating;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_total_review_rating', $total_review_rating );
					
					$avg_review_rating = 0;
					if($total_review_rating && $total_review_count) $avg_review_rating = $total_review_rating/$total_review_count;
					update_user_meta( $review_data->vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating );
					
					$wcfm_store_review_categories = array();
					if( !empty( $review_meta ) ) {
						foreach( $review_meta as $review_meta_cat ) {
							$wcfm_store_review_categories[] = $review_meta_cat->value;
						}
					}
					
					$category_review_rating = get_user_meta( $review_data->vendor_id, '_wcfmmp_category_review_rating', true );
					if( !$category_review_rating ) $category_review_rating = array();
					foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
						if( isset( $wcfm_store_review_categories[$wcfm_review_cat_key] ) ) {
							$total_category_review_rating = 0;
							$avg_category_review_rating = 0;
							if( $category_review_rating && !empty( $category_review_rating ) && isset( $category_review_rating[$wcfm_review_cat_key] ) ) {
								$total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
								$avg_category_review_rating   = $category_review_rating[$wcfm_review_cat_key]['avg'];
							}
							if($total_category_review_rating) $total_category_review_rating -= (float) $wcfm_store_review_categories[$wcfm_review_cat_key];
							if( $total_category_review_rating && $total_review_count ) $avg_category_review_rating    = $total_category_review_rating/$total_review_count;
							$category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = $avg_category_review_rating;
						} else {
							$category_review_rating[$wcfm_review_cat_key]['total'] = 0;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = 0;
						}
					}
					$category_review_rating = update_user_meta( $review_data->vendor_id, '_wcfmmp_category_review_rating', $category_review_rating );
				}
				
				$wpdb->delete("{$wpdb->prefix}wcfm_marketplace_reviews", array('ID' => $reviewid), array('%d'));
				$wpdb->delete("{$wpdb->prefix}wcfm_marketplace_review_rating_meta", array('review_id' => $reviewid), array('%d'));
			}
  	}
  	
  	echo 'success';
  	die;
  }
  
  /**
   * Vendor Reviews Count
   */
  public function get_vendor_reviews_count( $vendor_id = 0, $status = 'approved' ) {
  	global $WCFM, $WCFMmp, $wpdb;
  	
		$reviews_count = 0;
		
		$sql = "SELECT COUNT(ID) from {$wpdb->prefix}wcfm_marketplace_reviews";
		$sql .= " WHERE 1=1";
		
		if( $vendor_id ) {
			$sql .= " AND `vendor_id` = " . $vendor_id;
		}
		
		if( $status == 'approved' ) {
			$sql .= " AND `approved` = 1";
		} else {
			$sql .= " AND `approved` = 0";
		}
  	$reviews_count = $wpdb->get_var($sql);
  	if( !$reviews_count ) $reviews_count = 0;
  		
  	return $reviews_count;
  }
  
  /**
	 * Get avarage review rating
	 *
	 * @return integer
	 */
	public function get_vendor_review_rating( $vendor_id ) {
		if( !$vendor_id ) return 0;
		$avg_review_rating = get_user_meta( $vendor_id, '_wcfmmp_avg_review_rating', true );
		if( !$avg_review_rating ) $total_review_rating = 0;
		else $avg_review_rating = round( $avg_review_rating, 2 );
		return $avg_review_rating;
	}
	
	/**
	 * Get avarage review rating
	 *
	 * @return integer
	 */
	public function show_star_rating( $store_rating = 0, $vendor_id = 0 ) {
		
		if ( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) && apply_filters( 'wcfm_is_allow_review_rating', true ) ) {
			if( $vendor_id ) {
				$store_rating = $this->get_vendor_review_rating( $vendor_id );
			}
			if( !$store_rating ) $store_rating = 0;
			$reviews_count = 0;
			
			$rating_label = sprintf( __( 'Rated %s out of 5', 'wc-multivendor-marketplace' ), $store_rating );
			if ( $store_rating && apply_filters( 'wcfm_is_allow_vendor_review_count', false ) ) {
				$reviews_count = get_user_meta( $vendor_id, '_wcfmmp_total_review_count', true );
				$rating_label = sprintf( __( 'Rated %s out of 5 based on %s review(s)', 'wc-multivendor-marketplace' ), $store_rating, $reviews_count );
			}
		  ?>
			<div style="<?php if ( $store_rating && apply_filters( 'wcfm_is_allow_vendor_review_count', false ) ) { echo 'width:7.5em!important;'; } ?>" class="wcfmmp-store-rating" title="<?php if( $store_rating ) { echo $rating_label; } else { _e( 'No reviews yet!', 'wc-multivendor-marketplace' ); } ?>">
				<span style="width: <?php echo $store_rating ? ( ( $store_rating/5 ) * 100 - 1 ) : 0; ?>%">
					<strong class="rating"><?php echo $store_rating; ?></strong> <?php _e( 'out of 5', 'wc-multivendor-marketplace' ); ?>
				</span>
				<?php 
				if ( $store_rating && apply_filters( 'wcfm_is_allow_vendor_review_count', false ) ) {
				  echo '<label style="display:inline-block;float:right;">&nbsp;('.$reviews_count.')</label>';	
				} 
				?>
			</div>
		<?php }
	}
  
  /**
   * Users Reviews Count
   */
  public function get_author_reviews_count( $author_id = 0 ) {
  	global $WCFM, $WCFMmp, $wpdb;
  	
		$reviews_count = 0;
		
		$sql = "SELECT COUNT(ID) from {$wpdb->prefix}wcfm_marketplace_reviews";
		$sql .= " WHERE 1=1";
		
		if( $author_id ) {
			$sql .= " AND `author_id` = " . $author_id;
		}
		
		$sql .= " AND `approved` = 1";
  	$reviews_count = $wpdb->get_var($sql);
  	if( !$reviews_count ) $reviews_count = 0;
  		
  	return $reviews_count;
  }
  
  /**
   * Check wheather new user allow to add new review or not
   */
  function wcfmmp_check_new_review_permission( $is_allow, $vendor_id ) {
  	global $WCFM, $WCFMmp, $wpdb;
  	
  	$author_id = get_current_user_id();
  	
  	if( $vendor_id == $author_id ) return false;
  	
  	$sql = "SELECT COUNT(ID) from {$wpdb->prefix}wcfm_marketplace_reviews";
		$sql .= " WHERE 1=1";
		$sql .= " AND `vendor_id` = " . $vendor_id;
		$sql .= " AND `author_id` = " . $author_id;
		$wcfm_review_added = $wpdb->get_var($sql);
		
		if( $wcfm_review_added ) $is_allow = false;
		
		$review_only_store_user = isset( $WCFMmp->wcfmmp_review_options['review_only_store_user'] ) ? $WCFMmp->wcfmmp_review_options['review_only_store_user'] : 'no';
		if( $is_allow && ( $review_only_store_user == 'yes' ) ) {
			$sql = "SELECT COUNT(ID) from {$wpdb->prefix}wcfm_marketplace_orders";
			$sql .= " WHERE 1=1";
			$sql .= " AND `vendor_id` = " . $vendor_id;
			$sql .= " AND `customer_id` = " . $author_id;
			$wcfm_has_order = $wpdb->get_var($sql);
			
			if( !$wcfm_has_order ) $is_allow = false;
		}
		
		return $is_allow;
  }
  
  public function wcfmmp_add_store_review( $comment_id ) {
  	global $WCFMmp, $wpdb, $WCFM;
  	
  	if( !apply_filters( 'wcfmmp_is_allow_product_review_sync_with_store_review', true ) ) return;
  	
  	$product_review_sync    = isset( $WCFMmp->wcfmmp_review_options['product_review_sync'] ) ? $WCFMmp->wcfmmp_review_options['product_review_sync'] : 'no';
  	if( $product_review_sync != 'yes' ) return;
  	
		$wcfm_review_categories = get_wcfm_marketplace_active_review_categories();
	  $review_auto_approve = isset( $WCFMmp->wcfmmp_review_options['review_auto_approve'] ) ? $WCFMmp->wcfmmp_review_options['review_auto_approve'] : 'no';
		
		$approved = 0;
		if( $review_auto_approve == 'yes' ) $approved = 1;
		
		$review_title = '';
		
		$vendor_reviews =  $wpdb->get_results(
																					"SELECT c.comment_content, c.comment_ID, c.comment_author,
																							c.comment_author_email, c.comment_author_url,
																							c.user_id, c.comment_post_ID, c.comment_approved,
																							c.comment_date
																					FROM $wpdb->comments as c
																					WHERE 
																							c.comment_ID={$comment_id}"
																			);
		
		
		if( !empty( $vendor_reviews ) ) {
			foreach( $vendor_reviews as $vendor_review ) {
				
				$product_id = $vendor_review->comment_post_ID;
				if( !$product_id ) return;
				
				if( 'product' !== get_post_type( absint( $product_id ) ) ) return;
				
				$vendor_id  = wcfm_get_vendor_id_by_post( $product_id );
				if( !$vendor_id ) return;
		
				if ( get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) {
					$review_rating =  intval( get_comment_meta( $comment_id, 'rating', true ) );
				} else {
					$review_rating = 5;
				}
				
				$current_time = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
				
				$wcfm_review_submit = "INSERT into {$wpdb->prefix}wcfm_marketplace_reviews 
													(`vendor_id`, `author_id`, `author_name`, `author_email`, `review_title`, `review_description`, `review_rating`, `approved`, `created`)
													VALUES
													({$vendor_id}, {$vendor_review->user_id}, '{$vendor_review->comment_author}', '{$vendor_review->comment_author_email}', '{$review_title}', '{$vendor_review->comment_content}', '{$review_rating}', {$approved}, '{$current_time}')";
												
				$wpdb->query($wcfm_review_submit);
				$wcfm_review_id = $wpdb->insert_id;
				
				if( $wcfm_review_id ) {
				
					// Updating Review Meta
					foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
						$wcfm_review_meta_update = "INSERT into {$wpdb->prefix}wcfm_marketplace_review_rating_meta 
																				(`review_id`, `key`, `value`, `type`)
																				VALUES
																				({$wcfm_review_id}, '{$wcfm_review_category['category']}', '{$review_rating}', 'rating_category')";
						$wpdb->query($wcfm_review_meta_update);
					}
					
					// Updating Review Meta - Product
					$wcfm_review_meta_update = "INSERT into {$wpdb->prefix}wcfm_marketplace_review_rating_meta 
																				(`review_id`, `key`, `value`, `type`)
																				VALUES
																				({$wcfm_review_id}, 'product', '{$product_id}', 'rating_product')";
					$wpdb->query($wcfm_review_meta_update);
					
					// Update user review data
					if( $review_auto_approve == 'yes' ) {
						$total_review_count = get_user_meta( $vendor_id, '_wcfmmp_total_review_count', true );
						if( !$total_review_count ) $total_review_count = 0;
						else $total_review_count = absint( $total_review_count );
						$total_review_count++;
						update_user_meta( $vendor_id, '_wcfmmp_total_review_count', $total_review_count );
						
						$total_review_rating = get_user_meta( $vendor_id, '_wcfmmp_total_review_rating', true );
						if( !$total_review_rating ) $total_review_rating = 0;
						else $total_review_rating = (float) $total_review_rating;
						$total_review_rating += (float) $review_rating;
						update_user_meta( $vendor_id, '_wcfmmp_total_review_rating', $total_review_rating );
						
						$avg_review_rating = $total_review_rating/$total_review_count;
						update_user_meta( $vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating );
					
						$category_review_rating = get_user_meta( $vendor_id, '_wcfmmp_category_review_rating', true );
						if( !$category_review_rating ) $category_review_rating = array();
						foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
							$total_category_review_rating = 0;
							$avg_category_review_rating = 0;
							if( $category_review_rating && !empty( $category_review_rating ) && isset( $category_review_rating[$wcfm_review_cat_key] ) ) {
								$total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
								$avg_category_review_rating   = $category_review_rating[$wcfm_review_cat_key]['avg'];
							}
							$total_category_review_rating += (float) $review_rating;
							$avg_category_review_rating    = $total_category_review_rating/$total_review_count;
							$category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = $avg_category_review_rating;
						}
						$category_review_rating = update_user_meta( $vendor_id, '_wcfmmp_category_review_rating', $category_review_rating );
						
						update_user_meta( $vendor_id, '_wcfmmp_last_author_id', $vendor_review->user_id );
						update_user_meta( $vendor_id, '_wcfmmp_last_author_name', $vendor_review->comment_author );
					}
					
					// Vendor Direct message
					$wcfm_messages = sprintf( __( 'You have received a new Review from <b>%s</b>', 'wc-multivendor-marketplace' ), $vendor_review->comment_author );
					$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $vendor_id, 1, 0, $wcfm_messages, 'review' );
				}
			}
		}
  	
  }
  
  function wcfmmp_reviews_message_types( $message_types ) {
		$message_types['review'] = __( 'Store Review', 'wc-multivendor-marketplace' );
		return $message_types;
	}
	
	function wcfmmp_is_allow_review_rating( $is_allow ) {
		$is_allow = false;
		if( 'yes' === get_option( 'woocommerce_enable_review_rating' ) ) {
			$is_allow = true;
		}
		return $is_allow;
	}
	
}