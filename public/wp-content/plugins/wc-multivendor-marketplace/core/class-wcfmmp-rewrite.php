<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Rewrite
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Rewrites {

	public $query_vars = array();
	public $wcfm_store_url = '';
	
	/**
	 * Hook into the functions
	 */
	public function __construct() {
		global $wp_query;
		
		if( function_exists( 'wcfm_get_option' ) ) {
			$this->wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
		} else {
			$this->wcfm_store_url = get_option( 'wcfm_store_url', 'store' );
		}
		
		add_action( 'init', array( $this, 'register_rule' ), 9 );
		
		add_action( 'init', array( $this, 'custom_taxonomy_register_rule' ), 11 );

		add_filter( 'template_include', array( $this, 'store_template' ), 9 );

		add_filter( 'query_vars', array( $this, 'register_query_var' ) );
		if( $wp_query ) {
			add_action( 'pre_get_posts', array( $this, 'store_query_check' ), 9 );
			add_action( 'pre_get_posts', array( $this, 'store_query_filter' ), 20, 2 );
		}
		add_action( 'woocommerce_product_query', array( $this, 'store_query_filter' ), 10, 2 );
		
		// WC Filter Term Query Store Filter
		add_filter( 'woocommerce_get_filtered_term_product_counts_query', array( &$this, 'wcfmmp_product_counts_query' ) );
		
		add_filter( 'post_type_archive_link', array( $this, 'store_archive_link' ) );
		
		add_filter( 'woocommerce_page_title', array( $this, 'store_page_title' ) );
		
		add_filter( 'woocommerce_get_breadcrumb', array( $this, 'store_page_breadcrumb'), 10 ,1  );
		
		// Ocean WP - Theme Support
		add_filter( 'ocean_title', array( $this, 'oceanwp_store_page_title' ) );
		add_filter( 'breadcrumb_trail_items', array( $this, 'oceanwp_store_page_breadcrumb'), 10 ,2  );
		
		// Store Page WPML Switcher Compatibility
		add_filter( 'icl_ls_languages', array( &$this, 'wcfmmp_store_page_wpml_language_switcher' ), 999 );
		
		// WooCommerce Widget Store Page URL
		add_filter( 'woocommerce_widget_get_current_page_url', array( $this, 'wcfmmp_widget_get_current_page_url' ), 50, 2 );
		
		// WC Filter Price Query Store Filter
		add_filter( 'woocommerce_price_filter_sql', array( &$this, 'wcfmmp_price_filter_sql' ), 500, 3 );
	}


	/**
	 * Initializes the WCFMmp_Rewrites() class
	 *
	 * @since 1.0.0
	 *
	 * Checks for an existing WCFMmp_Rewrites() instance
	 * and if it doesn't find one, creates it.
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new WCFMmp_Rewrites();
		}

		return $instance;
	}

	/**
	 * Generate breadcrumb for store page
	 *
	 * @since 1.0.0
	 *
	 * @param array $crumbs
	 *
	 * @return array $crumbs
	 */
	public function store_page_breadcrumb( $crumbs ) {
		if (  wcfm_is_store_page() ) {
			$author      = apply_filters( 'wcfmmp_store_query_var', get_query_var( $this->wcfm_store_url ) );
			$seller_info = get_user_by( 'slug', $author );
			if( $seller_info ) {
				$store_info = wcfmmp_get_store_info( $seller_info->data->ID );
				if( $store_info ) {
					$crumbs[1]   = array( __( ucwords($this->wcfm_store_url), 'wc-multivendor-marketplace' ) , site_url().'/'.$this->wcfm_store_url );
					$crumbs[2]   = array( $store_info['store_name'], wcfmmp_get_store_url( $seller_info->data->ID ) );
					if( $this->store_template_title() ) {
						$crumbs[3]   = array( strip_tags( $this->store_template_title() ), '' );
					}
				}
			}
		}

		return $crumbs;
	}
	
	/**
	 * Generate Page Title for store page - OceanWP theme support
	 *
	 * @since 1.0.0
	 *
	 * @param array $crumbs
	 *
	 * @return array $crumbs
	 */
	public function oceanwp_store_page_title( $page_title = '' ) {

		$store_name   = urldecode( get_query_var( $this->wcfm_store_url ) );
		if( $store_name ) {
			$seller_info  = get_user_by( 'slug', $store_name );
			if( $seller_info ) {
				$store_info = wcfmmp_get_store_info( $seller_info->data->ID );
				return $store_info['store_name'];
			} else {
				$store_name   = get_query_var( $this->wcfm_store_url );
				if( $store_name ) {
					$seller_info  = get_user_by( 'slug', $store_name );
					if( $seller_info ) {
						$store_info = wcfmmp_get_store_info( $seller_info->data->ID );
						return $store_info['store_name'];
					}
				}
			}
		}
		
		return $page_title;
	}
	
	/**
	 * Generate breadcrumb for store page - OceanWP Theme support
	 *
	 * @since 1.0.0
	 *
	 * @param array $crumbs
	 *
	 * @return array $crumbs
	 */
	function oceanwp_store_page_breadcrumb( $crumbs, $args ) {
		if (  wcfm_is_store_page() ) {
			$author      = get_query_var( $this->wcfm_store_url );
			$seller_info = get_user_by( 'slug', $author );
			if( $seller_info ) {
				$store_info = wcfmmp_get_store_info( $seller_info->data->ID );
				if( $store_info ) {
					$crumbs[1]   = '<a href="' . site_url().'/'.$this->wcfm_store_url . '">' .  __( ucwords($this->wcfm_store_url), 'wc-multivendor-marketplace' ) . '</a>';
					$crumbs[2]   = '<a href="' . wcfmmp_get_store_url( $seller_info->data->ID ) . '">' . $store_info['store_name'] . '</a>';
					if( $this->store_template_title() ) {
						$crumbs[3]   = strip_tags( $this->store_template_title() );
					}
				}
			}
		}
		return $crumbs;
	}
	
	/**
	 * Store Page WPML Language Switcher Compatibility
	 */
	function wcfmmp_store_page_wpml_language_switcher( $languages ) {
		
		if (  wcfm_is_store_page() ) {
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
				global $sitepress;
				$author      = get_query_var( $this->wcfm_store_url );
				$formated_languages = array();
				
				$default_lang = $sitepress->get_default_language();
				
				if( !empty( $languages ) ) {
					foreach( $languages as $lang => $language ) {
						if( $default_lang  && ( $default_lang  == $language['language_code'] ) ) {
							$language['url'] = site_url() .'/'. $this->wcfm_store_url .'/'. $author;
						} else {
							$language['url'] = site_url() .'/'. $language['language_code'] .'/'. $this->wcfm_store_url .'/'. $author;
						}
						$formated_languages[$lang] = $language;
					}
					$languages = $formated_languages;
				}
			}
		}
		
		return $languages;
	}

	/**
	 * Register the rewrite rule
	 *
	 * @return void
	 */
	function register_rule() {
		
		if( function_exists( 'wcfm_get_option' ) ) {
			$wcfm_store_modified_endpoints = wcfm_get_option( 'wcfm_store_endpoints', array() );
		} else {
			$wcfm_store_modified_endpoints = get_option( 'wcfm_store_endpoints', array() );
		}
		
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]', 'top' );
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&paged=$matches[2]', 'top' );
		
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('about').'?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&'.$this->store_endpoint('about').'=true', 'top' );
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('policies').'?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&'.$this->store_endpoint('policies').'=true', 'top' );
		
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('reviews').'?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&'.$this->store_endpoint('reviews').'=true', 'top' );
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('reviews').'/page/?([0-9]{1,})/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&paged=$matches[2]&'.$this->store_endpoint('reviews').'=true', 'top' );
		
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('followers').'?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&'.$this->store_endpoint('followers').'=true', 'top' );
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('followers').'/page/?([0-9]{1,})/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&paged=$matches[2]&'.$this->store_endpoint('followers').'=true', 'top' );
		
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('followings').'?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&'.$this->store_endpoint('followings').'=true', 'top' );
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('followings').'/page/?([0-9]{1,})/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&paged=$matches[2]&'.$this->store_endpoint('followings').'=true', 'top' );
		
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('articles').'?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&'.$this->store_endpoint('articles').'=true', 'top' );
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/'.$this->store_endpoint('articles').'/page/?([0-9]{1,})/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&paged=$matches[2]&'.$this->store_endpoint('articles').'=true', 'top' );
		
		add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/category/?([^/]*)/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&term=$matches[2]&term_section=true', 'top' );
    add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/category/?([^/]*)/page/?([0-9]{1,})/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&term=$matches[2]&paged=$matches[3]&term_section=true', 'top' );
    
		do_action( 'wcfmmp_rewrite_rules_loaded', $this->wcfm_store_url );
	}
	
	function custom_taxonomy_register_rule() {
		
		// Custom Taxonomies Filter Rules
		$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
		if( !empty( $product_taxonomies ) ) {
			foreach( $product_taxonomies as $product_taxonomy ) {
				if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) ) {
					if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
						add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/tax-'.$product_taxonomy->name.'/?([^/]*)/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&term=$matches[2]&tax-'.$product_taxonomy->name.'=true&term_section=true', 'top' );
						add_rewrite_rule( $this->wcfm_store_url.'/([^/]+)/tax-'.$product_taxonomy->name.'/?([^/]*)/page/?([0-9]{1,})/?$', 'index.php?post_type=product&'.$this->wcfm_store_url.'=$matches[1]&term=$matches[2]&paged=$matches[3]&tax-'.$product_taxonomy->name.'=true&term_section=true', 'top' );
					}
				}
			}
		}
	}
	
	/**
	 * Register the query var
	 *
	 * @param array  $vars
	 *
	 * @return array
	 */
	function register_query_var( $vars ) {
		$vars[] = $this->wcfm_store_url;
		$vars[] = 'term_section';
		$vars[] = $this->store_endpoint( 'about' );
		$vars[] = $this->store_endpoint( 'policies' );
		$vars[] = $this->store_endpoint( 'reviews' );
		$vars[] = $this->store_endpoint( 'followers' );
		$vars[] = $this->store_endpoint( 'followings' );
		$vars[] = $this->store_endpoint( 'articles' );
		
		// Custom Taxonomies Filters Query Vars
		$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
		if( !empty( $product_taxonomies ) ) {
			foreach( $product_taxonomies as $product_taxonomy ) {
				if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) ) {
					if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
						$vars[] = 'tax-'.$product_taxonomy->name;
					}
				}
			}
		}
		
		foreach ( $this->query_vars as $var ) {
			$vars[] = $var;
		}

		return $vars;
	}
	
	/**
	 * Include store template
	 *
	 * @param type  $template
	 *
	 * @return string
	 */
	function store_template_title() {
		global $WCFM, $WCFMmp;
		
		if ( !WCFMmp_Dependencies::woocommerce_plugin_active_check() ) {
			return $template;
		}
		
		$store_name = get_query_var( $this->wcfm_store_url );

		if ( !empty( $store_name ) ) {
			
			remove_filter( 'template_include', array( 'WC_Template_Loader', 'template_loader' ) );
			
			$store_user = get_user_by( 'slug', $store_name );
			
			// no user found
			if ( ! $store_user ) {
				return '';
			}

			// check if the user is seller
			if ( ! wcfm_is_vendor( $store_user->ID ) ) {
				return '';
			}
			
			// Check is store Online
			$is_store_offline = get_user_meta( $store_user->ID, '_wcfm_store_offline', true );
			$is_store_offline = apply_filters( 'wcfmmp_is_store_offline', $is_store_offline, $store_user->ID );
			if ( $is_store_offline ) {
				return '';
			}
			
			$store_tabs = $WCFMmp->wcfmmp_store->get_store_tabs( false );

			if ( get_query_var( $this->store_endpoint('about') ) ) {
				return isset( $store_tabs['about'] ) ? $store_tabs['about'] : '';
			} elseif ( get_query_var( $this->store_endpoint('policies') ) ) {
				return isset( $store_tabs['policies'] ) ? $store_tabs['policies'] : '';
			} elseif ( get_query_var( $this->store_endpoint('reviews') ) ) {
				return isset( $store_tabs['reviews'] ) ? $store_tabs['reviews'] : '';
			} elseif ( get_query_var( $this->store_endpoint('followers') ) ) {
				return isset( $store_tabs['followers'] ) ? $store_tabs['followers'] : '';
			} elseif ( get_query_var( $this->store_endpoint('followings') ) ) {
				return isset( $store_tabs['followings'] ) ? $store_tabs['followings'] : '';
			} elseif ( get_query_var( $this->store_endpoint('articles') ) ) {
				return isset( $store_tabs['articles'] ) ? $store_tabs['articles'] : '';
			} else {
				$default_qv = apply_filters( 'wcfmp_store_default_query_vars', 'products', $store_user->ID  );
				$default_qv = apply_filters( 'wcfmmp_store_default_query_vars', $default_qv, $store_user->ID  );
				return isset( $store_tabs[$default_qv] ) ? $store_tabs[$default_qv] : '';
			}
		}

		return $template;
	}

	/**
	 * Include store template
	 *
	 * @param type  $template
	 *
	 * @return string
	 */
	function store_template( $template ) {
		global $WCFM, $WCFMmp;
		
		if ( !WCFMmp_Dependencies::woocommerce_plugin_active_check() ) {
			return $template;
		}
		
		if( $WCFMmp->store_template_loaded ) return $template;
		
		$store_name = get_query_var( $this->wcfm_store_url );

		if ( !empty( $store_name ) ) {
			$store_user = get_user_by( 'slug', $store_name );
			
			remove_filter( 'template_include', array( 'WC_Template_Loader', 'template_loader' ) );
			$WCFMmp->store_template_loaded = true;
			
			// no user found
			if ( ! $store_user ) {
				return get_404_template();
			}

			// check if the user is seller
			if ( ! wcfm_is_vendor( $store_user->ID ) ) {
				return get_404_template();
			}
			
			// Disable Store URL Visit
			if( apply_filters( 'wcfm_is_disable_store_url_access', false ) ) {
				wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
				exit;
			}
			
			// Check is store Online
			$is_store_offline = get_user_meta( $store_user->ID, '_wcfm_store_offline', true );
			$is_store_offline = apply_filters( 'wcfmmp_is_store_offline', $is_store_offline, $store_user->ID );
			if ( $is_store_offline ) {
				return get_404_template();
			}
			
			// WCFM Marketplace Elementor Compatibility
			$wcfmem_template = apply_filters( 'wcfmem_locate_store_template', '' );
			if( $wcfmem_template ) return $wcfmem_template;
			
			// Dive Theme Builder Support
			if( function_exists( 'et_theme_builder_frontend_override_template' ) ) {
				$layouts         = et_theme_builder_get_template_layouts();
				$override_header = et_theme_builder_overrides_layout( ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE );
				$override_footer = et_theme_builder_overrides_layout( ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE );
				if ( $override_header || $override_footer ) {
					add_action( 'get_header', 'et_theme_builder_frontend_override_header' );
					add_action( 'get_footer', 'et_theme_builder_frontend_override_footer' );
						
					et_theme_builder_frontend_enqueue_styles( $layouts );
				}
			}
			
			if ( get_query_var( $this->store_endpoint('about') ) ) {
				return $WCFMmp->template->get_template( 'store/wcfmmp-view-store.php', array( 'store_tab' => 'about' ) );
			} elseif ( get_query_var( $this->store_endpoint('policies') ) ) {
				return $WCFMmp->template->get_template( 'store/wcfmmp-view-store.php', array( 'store_tab' => 'policies' ) );
			} elseif ( get_query_var( $this->store_endpoint('reviews') ) ) {
				return $WCFMmp->template->get_template( 'store/wcfmmp-view-store.php', array( 'store_tab' => 'reviews' ) );
			} elseif ( get_query_var( $this->store_endpoint('followers') ) ) {
				return $WCFMmp->template->get_template( 'store/wcfmmp-view-store.php', array( 'store_tab' => 'followers' ) );
			} elseif ( get_query_var( $this->store_endpoint('followings') ) ) {
				return $WCFMmp->template->get_template( 'store/wcfmmp-view-store.php', array( 'store_tab' => 'followings' ) );
			} elseif ( get_query_var( $this->store_endpoint('articles') ) ) {
				return $WCFMmp->template->get_template( 'store/wcfmmp-view-store.php', array( 'store_tab' => 'articles' ) );
			} else {
				return $WCFMmp->template->get_template( 'store/wcfmmp-view-store.php', array( 'store_tab' => apply_filters( 'wcfmmp_store_default_query_vars', apply_filters( 'wcfmp_store_default_query_vars', 'products', $store_user->ID ), 'products', $store_user->ID ) ) );
			}
		}

		return $template;
	}
	
	function store_endpoint( $endpoint ) {
		global $WCFMmp;
		$endpoint = !empty( $WCFMmp->wcfmmp_store_endpoints[$endpoint] ) ? $WCFMmp->wcfmmp_store_endpoints[$endpoint] : $endpoint;
		return sanitize_title($endpoint);
	}
	
	public function store_archive_link( $link ) {

		$store_name   = urldecode( get_query_var( $this->wcfm_store_url ) );
		$seller_info  = get_user_by( 'slug', $store_name );
		
		if( !$seller_info ) {
			$store_name   = get_query_var( $this->wcfm_store_url );
			$seller_info  = get_user_by( 'slug', $store_name );
		}
		
		$store_url = '';
		if( $seller_info ) {
			$store_url = wcfmmp_get_store_url( $seller_info->data->ID );
			if ( get_query_var( $this->store_endpoint('about') ) ) {
				$store_url .= $this->store_endpoint('about');
			} elseif ( get_query_var( $this->store_endpoint('policies') ) ) {
				$store_url .= $this->store_endpoint('policies');
			} elseif ( get_query_var( $this->store_endpoint('reviews') ) ) {
				$store_url .= $this->store_endpoint('reviews');
			} elseif ( get_query_var( $this->store_endpoint('followers') ) ) {
				$store_url .= $this->store_endpoint('followers');
			} elseif ( get_query_var( $this->store_endpoint('followings') ) ) {
				$store_url .= $this->store_endpoint('followings');
			} elseif ( get_query_var( $this->store_endpoint('articles') ) ) {
				$store_url .= $this->store_endpoint('articles');
			} else {
				$default_qv = apply_filters( 'wcfmp_store_default_query_vars', 'products', $seller_info->data->ID );
				$default_qv = apply_filters( 'wcfmmp_store_default_query_vars', $default_qv, $seller_info->data->ID );
				if( $default_qv != 'products' ) {
					$store_url .= $this->store_endpoint($default_qv);
				}
			}
		}

		return ! $seller_info ? $link : $store_url;
	}
	
	public function store_page_title( $page_title = '' ) {

		$store_name   = urldecode( get_query_var( $this->wcfm_store_url ) );
		$seller_info  = get_user_by( 'slug', $store_name );
		
		return $seller_info ? '' : $page_title;
	}
	
	/**
	 * Store Query Uncessary Hook/Filter Check
	 */
	function store_query_check( $query ) {
		if (  wcfm_is_store_page() ) {
			// Divi Theme Support
			remove_action( 'pre_get_posts', 'et_builder_wc_pre_get_posts' );
			
			// Oxygen Builder Support
			remove_filter( 'template_include', 'ct_css_output', 99 );
			remove_filter( 'template_include', 'ct_determine_render_template', 98 );
			remove_filter( 'template_include', 'ct_eval_condition_template', 100 );
			remove_filter( 'template_include', 'oxygen_vsb_global_condition_eval_template', 100 );
		}
	}

	/**
	 * Store query filter
	 *
	 * Handles the product filtering by category in store page
	 *
	 * @param object  $query
	 *
	 * @return void
	 */
	function store_query_filter( $query, $that = null ) {
		global $wp_query, $WCFMmp;
		
		if( !$wp_query )
      return;
		
		$store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $this->wcfm_store_url ) );

		if ( !is_admin() && $query->is_main_query() && !empty( $store_name ) ) {
			$seller_info  = get_user_by( 'slug', $store_name );
			if( $seller_info ) {
				
				// WC Product Query
				if ( !get_query_var( 'articles' ) ) {
					//WC()->query->product_query( $query );
				}
				
				$store_info   = wcfmmp_get_store_info( $seller_info->data->ID );
				
				if( apply_filters( 'wcfmmp_is_allow_store_ppp', true ) ) {
					$global_store_ppp = isset( $WCFMmp->wcfmmp_marketplace_options['store_ppp'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_ppp'] : get_option( 'posts_per_page', 12 );
					$post_per_page = isset( $store_info['store_ppp'] ) && !empty( $store_info['store_ppp'] ) ? $store_info['store_ppp'] : $global_store_ppp;
					$query->set( 'posts_per_page', apply_filters( 'wcfmmp_store_ppp', $post_per_page ) );
				}
				
				if ( get_query_var( 'articles' ) ) {
					$query->set( 'post_type', 'post' );
				} else {
					$query->set( 'post_type', 'product' );
					$query->set( 'wc_query', 'product_query' );
				}
				$query->set( 'post_status', 'publish' );
				$query->set( 'author_name', $store_name );
				$query->query['term_section'] = isset( $query->query['term_section'] ) ? $query->query['term_section'] : array();

				if ( $query->query['term_section'] ) {
					$is_custom_taxonomy_filter = false;
					// Custom Taxonomies Filter Rules
					$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
					if( !empty( $product_taxonomies ) ) {
						foreach( $product_taxonomies as $product_taxonomy ) {
							if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) ) {
								if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
									$query->query['tax-'.$product_taxonomy->name] = isset( $query->query['tax-'.$product_taxonomy->name] ) ? $query->query['tax-'.$product_taxonomy->name] : array();
									
									if ( $query->query['tax-'.$product_taxonomy->name] ) {
									  $is_custom_taxonomy_filter = true;
										$query->set( 'tax_query',
											array(
												'relation' => 'AND',
												array(
													'taxonomy' => $product_taxonomy->name,
													'field'    => 'slug',
													'terms'    => $query->query['term'],
													'operator' => 'IN'
												)
											)
										);
									}
								}
							}
						}
					}
					
					if( !$is_custom_taxonomy_filter ) {
						$query->set( 'tax_query',
							array(
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'slug',
									'terms'    => $query->query['term']
								)
							)
						);
					}
				}
				
				// Reset Shop Page ID - Specially Fix for Divi Theme
				$query->set( 'page_id', 0 );
				
				if( defined( 'ELEMENTOR_VERSION' )  ) {
					if( apply_filters( 'wcfmmp_is_allow_elementor_is_single_reset', true ) )
						$query->is_single               = false;
					
					if( apply_filters( 'wcfmmp_is_allow_elementor_is_singular_reset', true ) )
						$query->is_singular             = false;
					
					if( apply_filters( 'wcfmmp_is_allow_elementor_is_archive_reset', true ) )
						$query->is_archive              = false;
					
					if( apply_filters( 'wcfmmp_is_allow_elementor_is_post_type_archive_reset', true ) )
						$query->is_post_type_archive    = false;
				}
				
				//print_r($query);
				
				add_filter( 'woocommerce_page_title', array( $this, 'store_page_title' ) );
			}
		}
	}
	
	/**
	 * Store Page WC Product Filter Query
	 */
	function wcfmmp_product_counts_query( $query ) {
		global $wpdb, $WCFMmp;
		if (  wcfm_is_store_page() && !$WCFMmp->store_query_filtered ) {
			$author      = get_query_var( $this->wcfm_store_url );
			$seller_info = get_user_by( 'slug', $author );
			if( $seller_info && $seller_info->data->ID ) {
		    $query['where'] .= " AND {$wpdb->posts}.post_author = {$seller_info->data->ID}";
		    $WCFMmp->store_query_filtered = true;
		  }
		}
		return $query;
	}
	
	/**
	 * Store Page Widgets Current page URL
	 */
	function wcfmmp_widget_get_current_page_url( $url, $widget ) {
		global $wpdb, $WCFMmp;
		if (  wcfm_is_store_page() ) {
			$author      = get_query_var( $this->wcfm_store_url );
			$seller_info = get_user_by( 'slug', $author );
			if( $seller_info && $seller_info->data->ID ) {
				$shop_url  = get_permalink( wc_get_page_id( 'shop' ) );
				$store_url = wcfmmp_get_store_url( $seller_info->data->ID );
				if( $store_url ) $url = $store_url;
			}
		}
		return $url;
	}
	
	/**
	 * Store Page WC Product Price Filter Query
	 */
	function wcfmmp_price_filter_sql( $query, $meta_query_sql, $tax_query_sql ) {
		global $wpdb, $WCFMmp;
		if (  wcfm_is_store_page() && !$WCFMmp->store_query_filtered ) {
			$author      = get_query_var( $this->wcfm_store_url );
			$seller_info = get_user_by( 'slug', $author );
			if( $seller_info && $seller_info->data->ID ) {
		    //$query .= " AND {$wpdb->posts}.post_author = {$seller_info->data->ID}";
		    
		    $search     = WC_Query::get_main_search_query_sql();
		    $search_query_sql = $search ? ' AND ' . $search : '';
		    $query = "
								SELECT min( min_price ) as min_price, MAX( max_price ) as max_price
								FROM {$wpdb->wc_product_meta_lookup}
								WHERE product_id IN (
									SELECT ID FROM {$wpdb->posts}
									" . $tax_query_sql['join'] . $meta_query_sql['join'] . "
									WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
									AND {$wpdb->posts}.post_status = 'publish'
									AND {$wpdb->posts}.post_author = {$seller_info->data->ID}
									" . $tax_query_sql['where'] . $meta_query_sql['where'] . $search_query_sql . '
								)';
		    
		    $WCFMmp->store_query_filtered = true;
		  }
		}
		return $query;
	}
	
}
