<?php
/**
 * WCFM plugin core
 *
 * Plugin Single Product Multi Vendor Controller
 *
 * @author 		WC Lovers
 * @package 	wcfma/core
 * @version   1.0.1
 */
 
class WCFMmp_Product_Multivendor {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		if( apply_filters( 'wcfmmp_is_allow_single_product_multivendor', true ) ) {
			if( apply_filters( 'wcfm_is_pref_sell_items_catalog', true ) ) {
				// Sell Items Catalog Init
				add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_sell_items_catalog_query_vars' ), 20 );
				add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_sell_items_catalog_endpoint_title' ), 20, 2 );
				add_action( 'init', array( &$this, 'wcfm_sell_items_catalog_init' ), 20 );
				
				// Sell Items Catalog Endpoint Edit
				add_filter( 'wcfm_endpoints_slug', array( $this, 'sell_items_catalog_wcfm_endpoints_slug' ) );
				
				// Sell Items Catalog menu on WCfM dashboard
				add_filter( 'wcfm_menus', array( &$this, 'wcfm_sell_items_catalog_menus' ), 30 );
				
				// Sell Items Catalog Load Scripts
				add_action( 'wcfm_load_scripts', array( &$this, 'load_scripts' ), 30 );
				add_action( 'after_wcfm_load_scripts', array( &$this, 'load_scripts' ), 30 );
				
				// Sell Items Catalog Load Styles
				add_action( 'wcfm_load_styles', array( &$this, 'load_styles' ), 30 );
				add_action( 'after_wcfm_load_styles', array( &$this, 'load_styles' ), 30 );
				
				// Sell Items Catalog Load views
				add_action( 'wcfm_load_views', array( &$this, 'load_views' ), 30 );
				
				// Sell Items Catalog Ajax Controllers
				add_action( 'after_wcfm_ajax_controller', array( &$this, 'ajax_controller' ) );
			}
		
			// Single Product Multi seller button
			add_action( 'woocommerce_single_product_summary',	array( &$this, 'wcfmmp_product_multivendor_button' ), 36 );
		}
		
		// Disable Product Title Edit for Product Multivendor Products
		add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcfm_product_manage_title_edit_disable' ), 250, 3 );
		
		// Clone Multi selling product
		add_action('wp_ajax_wcfmmp_product_multivendor_clone', array( &$this, 'wcfmmp_product_multivendor_clone' ) );
		
		// Clone Multi selling product - bulk
		add_action('wp_ajax_wcfmmp_product_multivendor_bulk_clone', array( &$this, 'wcfmmp_product_multivendor_bulk_clone' ) );
		
		// Product More Offers Table Sorting
		add_action('wp_ajax_wcfmmp_more_offers_sorting', array( &$this, 'wcfmmp_more_offers_sorted_table' ) );
		add_action('wp_ajax_nopriv_wcfmmp_more_offers_sorting', array( &$this, 'wcfmmp_more_offers_sorted_table' ) );
		
		// Product Loop Duplicate Product Hide
		add_action('woocommerce_product_query', array( &$this, 'wcfmmp_product_loop_duplicate_hide' ) );
		
		// Product Widget Duplicate Product Hide
		add_filter( 'woocommerce_shortcode_products_query', array( &$this, 'wcfmmp_product_widget_duplicate_hide' ) );
		add_filter( 'woocommerce_recently_viewed_products_widget_query_args', array( &$this, 'wcfmmp_product_widget_duplicate_hide' ) );
		add_filter( 'woocommerce_products_widget_query_args', array( &$this, 'wcfmmp_product_widget_duplicate_hide' ) );
		add_filter( 'electro_get_products_query_args', array( &$this, 'wcfmmp_product_widget_duplicate_hide' ) );
		add_filter( 'electro_wc_live_search_query_args', array( &$this, 'wcfmmp_product_widget_duplicate_hide' ) );
		add_filter( 'electro_get_top_rated_products_query_args', array( &$this, 'wcfmmp_product_widget_duplicate_hide' ) );
		
		// On Product Delete Reset Multi selling product
		add_action( 'delete_post', array( &$this, 'wcfmmp_delete_product_association' ) );
		add_action( 'wp_trash_post', array( &$this, 'wcfmmp_delete_product_association' ) );
		add_action( 'before_delete_post', array( &$this, 'wcfmmp_delete_product_association' ) );
		
		//enqueue scripts
		add_action('wp_enqueue_scripts', array(&$this, 'wcfmmp_product_multivendor_scripts'));
		//enqueue styles
		add_action('wp_enqueue_scripts', array(&$this, 'wcfmmp_product_multivendor_styles'));
	}
	
	/**
   * Sell Items Catalog Query Var
   */
  function wcfm_sell_items_catalog_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
  	if( isset( $wcfm_modified_endpoints['wcfm-sell-items-catalog'] ) && !empty( $wcfm_modified_endpoints['wcfm-sell-items-catalog'] ) && $wcfm_modified_endpoints['wcfm-sell-items-catalog'] == 'sell-items-catalog' ) $wcfm_modified_endpoints['wcfm-sell-items-catalog'] = 'add-to-my-store-catalog';
  	
		$query_sell_items_catalog_vars = array(
			'wcfm-sell-items-catalog'                 => ! empty( $wcfm_modified_endpoints['wcfm-sell-items-catalog'] ) ? $wcfm_modified_endpoints['wcfm-sell-items-catalog'] : 'add-to-my-store-catalog',
		);
		
		$query_vars = array_merge( $query_vars, $query_sell_items_catalog_vars );
		
		return $query_vars;
  }
  
  /**
   * Sell Items Catalog End Point Title
   */
  function wcfm_sell_items_catalog_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-sell-items-catalog' :
				$title = __( 'Sell Items Catalog', 'wc-multivendor-marketplace' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * Sell Items Catalog Endpoint Intialize
   */
  function wcfm_sell_items_catalog_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_sell_items_catalog' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_sell_items_catalog', 1 );
		}
  }
  
  /**
	 * Sell Items Catalog Endpoiint Edit
	 */
	function sell_items_catalog_wcfm_endpoints_slug( $endpoints ) {
		
		$sell_items_catalog_endpoints = array(
													'wcfm-sell-items-catalog'          => 'add-to-my-store-catalog',
													);
		
		$endpoints = array_merge( $endpoints, $sell_items_catalog_endpoints );
		
		return $endpoints;
	}
	
	/**
   * WCFM Sell Items Catalog Menu
   */
  function wcfm_sell_items_catalog_menus( $menus ) {
  	global $WCFM;
  		
  	if( wcfm_is_vendor() ) {
			$menus = array_slice($menus, 0, 3, true) +
													array( 'wcfm-sell-items-catalog' => array( 'label'      => __( 'Add to My Store', 'wc-multivendor-marketplace' ),
																																	   'url'        => wcfm_sell_items_catalog_url(),
																																	   'icon'       => 'hand-pointer',
																																	   'menu_for'   => 'vendor',
																																	   'priority'   => 70
																																	) )	 +
														array_slice($menus, 3, count($menus) - 3, true) ;
		}
  	return $menus;
  }  
  
  /**
   * Refund Scripts
   */
  public function load_scripts( $end_point ) {
	  global $WCFM, $WCFMmp;
    
	  switch( $end_point ) {
	  	case 'wcfm-sell-items-catalog':
	  		$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	wp_enqueue_script( 'wcfmmp_sell_items_catalog_js', $WCFMmp->library->js_lib_url_min . 'product_multivendor/wcfmmp-script-sell-items-catalog.js', array('jquery'), $WCFMmp->version, true );
      	
      	$wcfm_screen_manager_data = array();
      	if( apply_filters( 'wcfm_sell_items_catalog_additonal_data_hidden', true ) ) {
	    		$wcfm_screen_manager_data[6] = 'yes';
	    	}
	    	$wcfm_screen_manager_data = apply_filters( 'wcfm_sell_items_catalog_screen_manage', $wcfm_screen_manager_data );
	    	wp_localize_script( 'wcfmmp_sell_items_catalog_js', 'wcfm_sell_items_catalog_screen_manage', $wcfm_screen_manager_data );
      break;
	  }
	}
	
	/**
   * Refund Styles
   */
	public function load_styles( $end_point ) {
	  global $WCFM, $WCFMmp;
		
	  switch( $end_point ) {
	  	case 'wcfm-sell-items-catalog':
		    wp_enqueue_style( 'wcfmu_sell_items_catalog_css',  $WCFMmp->library->css_lib_url . 'product_multivendor/wcfmmp-style-sell-items-catalog.css', array(), $WCFMmp->version );
		  break;
	  }
	}
	
	/**
   * Refund Views
   */
  public function load_views( $end_point ) {
	  global $WCFM, $WCFMmp;
	  
	  switch( $end_point ) {
      case 'wcfm-sell-items-catalog':
        $WCFMmp->template->get_template( 'product_multivendor/wcfmmp-view-sell-items-catalog.php' );
      break;
	  }
	}
	
	/**
   * Refund Ajax Controllers
   */
  public function ajax_controller() {
  	global $WCFM, $WCFMmp;
  	
  	$controllers_path = $WCFMmp->plugin_path . 'controllers/product_multivendor/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = sanitize_text_field( $_POST['controller'] );
  		
  		switch( $controller ) {
  			case 'wcfm-sell-items-catalog':
					include_once( $controllers_path . 'wcfmmp-controller-sell-items-catalog.php' );
					new WCFMmp_Sell_Items_Catalog_Controller();
				break;
  		}
  	}
  }
  
  /**
   * Product Title Edit Disable for Product Multivendor Products
   */
  function wcfm_product_manage_title_edit_disable( $product_fields, $product_id, $product_type ) {
  	global $WCFM, $WCFMmp, $product, $wpdb;
  	
  	if( $product_id && apply_filters( 'wcfm_is_allow_product_multivendor_title_edit_disable', true ) ) {
  		if( isset( $product_fields['pro_title'] ) ) {
  			$sql     = "SELECT * FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` WHERE ( ( `product_id` = $product_id ) OR ( `parent_product_id` = $product_id ) )";
  			$results = $wpdb->get_row( $sql );
  			if ( $results ) {
  				$product_fields['pro_title']['attributes']['readonly'] = true;
  				$product_fields['pro_title']['desc'] = __( 'Title edit disabeled, it has other sellers!', 'wc-multivendor-marketplace' );
  				$product_fields['pro_title']['desc_class'] = 'instructions'; 
  			}
  		}
  	}
  	return $product_fields;
  }
	
  // WCFM Single Product Multi seller button
  function wcfmmp_product_multivendor_button() {
  	global $WCFM, $WCFMmp, $product, $post;
		
  	if( !wcfm_is_vendor() ) return;
  	
  	if( !apply_filters( 'wcfmmp_is_allow_single_product_multivendor', true ) ) return;
  	
  	if( !apply_filters( 'wcfmmp_is_allow_single_product_multivendor_by_vendor', true, $WCFMmp->vendor_id ) ) return;
  	
  	if( !apply_filters( 'wcfm_is_allow_manage_products', true ) || !apply_filters( 'wcfm_is_allow_add_products', true ) ) return;
  	if( !apply_filters( 'wcfm_is_allow_product_limit', true ) ) return;
  	if( !apply_filters( 'wcfm_is_allow_space_limit', true ) ) return;
  	
  	if( !method_exists( $product, 'get_id' ) ) return;
  	
  	$product_id = $product->get_id();
  	if( !$product_id ) {
  		if( is_product() ) {
				$product_id = $post->ID;
			}
  	}
  	
  	$wcfm_capability_options = apply_filters( 'wcfm_capability_options_rules', get_option( 'wcfm_capability_options', array() ) );
  	$product_type = $product->get_type();
  	if( isset( $wcfm_capability_options[$product_type] ) ) return;
  	
  	if( !apply_filters( 'wcfmmp_is_allow_single_product_multivendor_by_product', true, $product_id ) ) return;
  
  	$product_author = get_post_field( 'post_author', $product_id );
  	
  	if( !apply_filters( 'wcfmmp_is_allow_single_product_multivendor_by_product_vendor', true, $product_author ) ) return;
  	
  	if( $WCFMmp->vendor_id == $product_author ) return;
  	
  	if( $this->is_already_selling( $product_id ) ) return;
			
		$button_style     = '';
		$hover_color      = '';
		$hover_text_color = '#ffffff';
		$wcfm_options = $WCFM->wcfm_options;
		$wcfm_store_color_settings = get_option( 'wcfm_store_color_settings', array() );
		if( !empty( $wcfm_store_color_settings ) ) {
			if( isset( $wcfm_store_color_settings['button_bg'] ) ) { $button_style .= 'background: ' . $wcfm_store_color_settings['button_bg'] . ';border-bottom-color: ' . $wcfm_store_color_settings['button_bg'] . ';'; }
			if( isset( $wcfm_store_color_settings['button_text'] ) ) { $button_style .= 'color: ' . $wcfm_store_color_settings['button_text'] . ';'; }
			if( isset( $wcfm_store_color_settings['button_active_bg'] ) ) { $hover_color = $wcfm_store_color_settings['button_active_bg']; }
			if( isset( $wcfm_store_color_settings['button_active_text'] ) ) { $hover_text_color = $wcfm_store_color_settings['button_active_text']; }
		} else {
			if( isset( $wcfm_options['wc_frontend_manager_button_background_color_settings'] ) ) { $button_style .= 'background: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';border-bottom-color: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';'; }
			if( isset( $wcfm_options['wc_frontend_manager_button_text_color_settings'] ) ) { $button_style .= 'color: ' . $wcfm_options['wc_frontend_manager_button_text_color_settings'] . ';'; }
			if( isset( $wcfm_options['wc_frontend_manager_base_highlight_color_settings'] ) ) { $hover_color = $wcfm_options['wc_frontend_manager_base_highlight_color_settings']; }
		}
			
		$wcfm_product_multivendor_button_label  = __( 'Add to My Store', 'wc-multivendor-marketplace' );
		
		?>
		<div class="wcfm_ele_wrapper wcfm_product_multivendor_button_wrapper">
			<div class="wcfm-clearfix"></div>
			<a href="#" class="wcfm_product_multivendor" data-product_id="<?php echo $product_id; ?>" style="<?php echo $button_style; ?>"><span class="wcfmfa fa-cube"></span>&nbsp;&nbsp;<span class="product_multivendor_label"><?php _e( $wcfm_product_multivendor_button_label, 'wc-multivendor-marketplace' ); ?></span></a>
			<?php if( $hover_color ) { ?>
				<style>a.wcfm_product_multivendor:hover{background: <?php echo $hover_color; ?> !important;background-color: <?php echo $hover_color; ?> !important;border-bottom-color: <?php echo $hover_color; ?> !important;color: <?php echo $hover_text_color; ?> !important;}</style>
			<?php } ?>
			<div class="wcfm-clearfix"></div>
		</div>
		<?php
  }
  
  /**
   * Check whether vendor already selling this product or not
   */
  function is_already_selling( $product_id, $vendor_id = 0 ) {
  	global $WCFM, $WCFMmp, $wpdb;
  	
  	if( !$vendor_id ) {
  		$vendor_id = $WCFMmp->vendor_id;
  	}
  	
  	$multi_selling = get_post_meta( $product_id, '_has_multi_selling', true );
  	$multi_parent  = get_post_meta( $product_id, '_is_multi_parent', true );
  	
  	if( !$multi_parent && !$multi_selling ) return false;
  	
  	$sql     = "SELECT * FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` WHERE `vendor_id` = '$vendor_id' AND ( ( `product_id` = $product_id ) OR ( `parent_product_id` = $product_id ) )";
		$results = $wpdb->get_row( $sql );
		if ( $results ) return true;
  	
  	return false;
  }
  
  /**
   * WCFM Product Multivendor Clone
   */
  function wcfmmp_product_multivendor_clone() {
  	global $WCFM, $WCFMmp, $wp, $WCFM_Query, $_POST, $wpdb;
  	
  	if( !class_exists( 'WC_Admin_Duplicate_Product' ) ) {
			include( WC_ABSPATH . 'includes/admin/class-wc-admin-duplicate-product.php' );
		}
		$WC_Admin_Duplicate_Product = new WC_Admin_Duplicate_Product();
		
		if ( empty( $_POST['product_id'] ) ) {
			echo '{"status": false, "message": "' .  __( 'No product to duplicate has been supplied!', 'woocommerce' ) . '"}';
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : '';

		$product = wc_get_product( $product_id );

		if ( false === $product ) {
			/* translators: %s: product id */
			echo '{"status": false, "message": "' . sprintf( __( 'Product creation failed, could not find original product: %s', 'woocommerce' ), $product_id ) . '" }';
			die;
		}

		$duplicate = $this->wcfmmp_product_clone( $product_id );

		// Hook rename to match other woocommerce_product_* hooks, and to move away from depending on a response from the wp_posts table.
		do_action( 'woocommerce_product_duplicate', $duplicate, $product );
		do_action( 'after_wcfmmp_product_multivendor_clone', $duplicate->get_id(), $product );

		// Redirect to the edit screen for the new draft page
		echo '{"status": true, "redirect": "' . get_wcfm_edit_product_url( $duplicate->get_id() ) . '", "id": "' . $duplicate->get_id() . '"}';
		
		die;
  }
  
  /**
   * WCFM Product Multivendor Bulk Clone
   */
  function wcfmmp_product_multivendor_bulk_clone() {
  	global $WCFM, $WCFMmp, $wp, $WCFM_Query, $_POST, $wpdb;
  	
		if ( empty( $_POST['product_ids'] ) ) {
			echo '{"status": false, "message": "' .  __( 'No product to duplicate has been supplied!', 'woocommerce' ) . '"}';
		}

		$product_ids = isset( $_POST['product_ids'] ) ? wp_unslash($_POST['product_ids']) : '';

		if( is_array( $product_ids ) && !empty( $product_ids ) ) {
			foreach( $product_ids as $product_id ) {
				if( $product_id ) {
					$product = wc_get_product( $product_id );
	
					if ( false === $product ) {
						/* translators: %s: product id */
						//echo '{"status": false, "message": "' . sprintf( __( 'Product creation failed, could not find original product: %s', 'woocommerce' ), $product_id ) . '" }';
						continue;
					}
					
					$duplicate = $this->wcfmmp_product_clone( $product_id );
			
					// Hook rename to match other woocommerce_product_* hooks, and to move away from depending on a response from the wp_posts table.
					do_action( 'woocommerce_product_duplicate', $duplicate, $product );
					do_action( 'after_wcfmmp_product_multivendor_clone', $duplicate->get_id(), $product );
				}
			}
		}

		// Redirect to the edit screen for the new draft page
		echo '{"status": true}';
		
		die;
  }
  
  /**
   * WCFM Product Clone
   */
  function wcfmmp_product_clone( $product_id ) {
  	global $WCFM, $WCFMmp, $wp, $WCFM_Query, $_POST, $wpdb;
  	
  	if( !class_exists( 'WC_Admin_Duplicate_Product' ) ) {
			include( WC_ABSPATH . 'includes/admin/class-wc-admin-duplicate-product.php' );
		}
		$WC_Admin_Duplicate_Product = new WC_Admin_Duplicate_Product();
		
		$product = wc_get_product( $product_id );

		if ( false === $product ) {
			/* translators: %s: product id */
			echo '{"status": false, "message": "' . sprintf( __( 'Product creation failed, could not find original product: %s', 'woocommerce' ), $product_id ) . '" }';
		}

		$duplicate = $WC_Admin_Duplicate_Product->product_duplicate( $product );
		
		update_post_meta( $duplicate->get_id(), '_wcfm_product_views', 0 );
		delete_post_meta( $duplicate->get_id(), '_wcfm_review_product_notified' );
		
		wp_update_post(
				array(
						'ID' => intval( $duplicate->get_id() ),
						'post_title' => $product->get_title(),
						'post_status' => apply_filters( 'wcfmmp_product_multivendor_product_add_status', 'draft' ),
						'post_author' => $WCFMmp->vendor_id
				)
		);
		
		// For Variations
		$wcfm_variable_product_types = apply_filters( 'wcfm_variable_product_types', array( 'variable', 'variable-subscription', 'pw-gift-card' ) );
		if( in_array( $duplicate->get_type(), $wcfm_variable_product_types ) ) {
			foreach ( $duplicate->get_children() as $child_id ) {
				$arg = array(
					'ID' => $child_id,
					'post_author' => $WCFMmp->vendor_id,
				);
				wp_update_post( $arg );
			}
		}
		
		// Update WCFMmp Product Multi-vendor Table
		$parent_product_id = 0;
		$multi_selling = get_post_meta( $product_id, '_has_multi_selling', true );
  	$multi_parent  = get_post_meta( $product_id, '_is_multi_parent', true );
  	
  	if( $multi_parent ) {
  		$parent_product_id = absint($multi_parent);
  	} elseif( $multi_selling ) {
  		$parent_product_id = absint($multi_selling);
  	} elseif( !$multi_parent && !$multi_selling ) {
  		$parent_product_id = $product_id;
  	}
  	
  	$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_product_multivendor` 
									( vendor_id
									, product_id
									, parent_product_id
									) VALUES ( %d
									, %d
									, %d
									) ON DUPLICATE KEY UPDATE `created` = now()"
							, $WCFMmp->vendor_id
							, $duplicate->get_id()
							, $parent_product_id
			)
		);
		
		if( !$multi_parent && !$multi_selling ) {
  		update_post_meta( $product_id, '_is_multi_parent', $product_id );
  	}
  	update_post_meta( $duplicate->get_id(), '_has_multi_selling', $parent_product_id );
  	
  	return $duplicate;
  }
  
  /**
   * WCFMmp More Offers Table Sorted
   */
  function wcfmmp_more_offers_sorted_table() {
  	global $WCFM, $WCFMmp, $wpdb;
  	
  	if ( !empty( $_POST['product_id'] ) ) {
  		$product_id = absint( $_POST['product_id'] );
  		$sorting    = sanitize_title( $_POST['sorting'] );
  		
  		ob_start();
  		$WCFMmp->template->get_template( 'product_multivendor/wcfmmp-view-more-offers-loop.php', array( 'product_id' => $product_id, 'sorting' => $sorting ) );
  		echo ob_get_clean();
  	}
  	
  	die;
  }
  
  /**
   * WC Product Loop Duplicate Product Hide
   */
  function wcfmmp_product_loop_duplicate_hide( $q ) {
  	global $WCFM, $wpdb;
		
  	if( !wcfm_is_store_page() && apply_filters( 'wcfm_is_allow_product_loop_duplicate_hide', true ) ) {
			$more_offers = $wpdb->get_results( "SELECT GROUP_CONCAT(product_id) as products, parent_product_id FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` GROUP BY parent_product_id" );
			
			$exclude = array();
			if( !empty( $more_offers ) ) {
				foreach ($more_offers as $key => $value) {
					$product_ids = $value->products . ',' . $value->parent_product_id;
					
					$sql = "SELECT product_id, stock_status, stock_quantity FROM {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup WHERE product_id IN ({$product_ids})  ORDER BY wc_product_meta_lookup.min_price ASC";
					$product_metas = $wpdb->get_results( $sql );
					
					if( !empty( $product_metas ) ) {
						$is_first = true;
						foreach( $product_metas  as $pmkey => $product_meta ) {
							if( $product_meta->stock_status == 'outofstock' ) continue;
							$post_status = get_post_status( $product_meta->product_id );
							if( $post_status != 'publish' ) continue;
							if( $is_first ) {
								$is_first = false;
								continue;
							}
							$exclude[] = $product_meta->product_id;
						}
					}
				}
			}
			
			if( !empty( $exclude ) ) {
				$q->set( 'post__not_in', $exclude );
			}
		}
  }
  
  /**
   * WC Product Widget Duplicate Product Hide
   */
  function wcfmmp_product_widget_duplicate_hide( $query_args ) {
  	global $WCFM, $wpdb;
		if( !wcfm_is_store_page() && apply_filters( 'wcfm_is_allow_product_loop_duplicate_hide', true ) ) {
			$more_offers = $wpdb->get_results( "SELECT GROUP_CONCAT(product_id) as products, parent_product_id FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` GROUP BY parent_product_id" );
			
			$exclude = array();
			if( !empty( $more_offers ) ) {
				foreach ($more_offers as $key => $value) {
					$product_ids = $value->products . ',' . $value->parent_product_id;
					
					$sql = "SELECT product_id, stock_status, stock_quantity FROM {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup WHERE product_id IN ({$product_ids})  ORDER BY wc_product_meta_lookup.min_price ASC";
					$product_metas = $wpdb->get_results( $sql );
					
					if( !empty( $product_metas ) ) {
						$is_first = true;
						foreach( $product_metas  as $pmkey => $product_meta ) {
							if( $product_meta->stock_status == 'outofstock' ) continue;
							$post_status = get_post_status( $product_meta->product_id );
							if( $post_status != 'publish' ) continue;
							if( $is_first ) {
								$is_first = false;
								continue;
							}
							$exclude[] = $product_meta->product_id;
						}
					}
				}
			}
			
			if( !empty( $exclude ) ) {
				if( isset( $query_args['post__in'] ) ) {
					$query_args['post__in'] = array_diff( $query_args['post__in'], $exclude );
				} else {
					$query_args['post__not_in'] = $exclude;
				}
			}
		}
  	return $query_args;
  }
  
  /**
   * WCFM Product Multivendor Refresh on Delete
   */
  public function wcfmmp_delete_product_association( $product_id ) {
  	global $WCFMmp, $wpdb, $WCFM;
  	
  	if( !$product_id ) return;
  	
  	$sql  = "DELETE FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor`";
  	$sql .= " WHERE 1=1";
  	$sql .= " AND `product_id` = {$product_id}";
  	
  	$wpdb->query($sql);
  }
  
  /**
   * Single Product Page More Offers Tab
   */
  function wcfmmp_product_multivendor_tab_content() {
  	global $WCFM, $WCFMmp, $product;
  	$WCFMmp->template->get_template( 'product_multivendor/wcfmmp-view-more-offers.php' );
  }
  
  /**
	 * WCFM Enquiry JS
	 */
	function wcfmmp_product_multivendor_scripts() {
 		global $WCFM, $WCFMmp, $wp, $WCFM_Query;
 		
 		if( is_product() ) {
 			//if( !wcfm_is_vendor() ) return;
 			
 			$WCFM->library->load_blockui_lib();
 			
 			wp_enqueue_script( 'wcfm_product_multivendor_js', $WCFMmp->library->js_lib_url_min . 'product_multivendor/wcfmmp-script-product-multivendor.js', array('jquery' ), $WCFM->version, true );
 		}
 	}
 	
 	/**
 	 * WCFM Enquiry CSS
 	 */
 	function wcfmmp_product_multivendor_styles() {
 		global $WCFM, $WCFMmp, $wp, $WCFM_Query;
 		
 		if( is_product() ) {
 			wp_enqueue_style( 'wcfm_product_multivendor_css',  $WCFMmp->library->css_lib_url . 'product_multivendor/wcfmmp-style-product-multivendor.css', array(), $WCFM->version );
 		}
 	}
}