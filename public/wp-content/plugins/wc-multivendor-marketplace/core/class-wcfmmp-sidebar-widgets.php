<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Store Sidebar and Widgets
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Sidebar_Widgets {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		$this->init();
		
		add_action( 'init', array( &$this, 'wcfmmp_register_store_sidebar' ) );
		
		add_action( 'init', array( &$this, 'wcfmmp_register_store_lists_sidebar' ) );
		
		add_action( 'widgets_init', array( &$this, 'wcfmmp_register_widgets' ) );
	}
	
	public function init() {
		global $WCFM, $WCFMmp;
		
		$default_widgets = get_wcfm_marketplace_default_widgets();
		foreach( $default_widgets as $default_widget => $default_widget_label ) {
			$this->load_widgets( $default_widget );
		}
	}
	
	/**
	 * Register Store Sidebar
	 */
	function wcfmmp_register_store_sidebar() {
		register_sidebar(
			apply_filters( 'wcfmmp_store_sidebar_args', array(
						'name'          => __( 'Vendor Store Sidebar', 'wc-multivendor-marketplace' ),
						'id'            => 'sidebar-wcfmmp-store',
						'before_widget' => '<aside id="%1$s" class="widget sidebar-box clr %2$s">',
						'after_widget'  => '</aside>',
						'before_title'  => '<div class="sidebar_heading"><h4 class="widget-title">',
						'after_title'   => '</h4></div>',
				)
			)
		);
	}
	
	/**
	 * Register Store List Sidebar
	 */
	function wcfmmp_register_store_lists_sidebar() {
		register_sidebar(
			apply_filters( 'wcfmmp_store_lists_sidebar_args', array(
						'name'          => __( 'Store List Sidebar', 'wc-multivendor-marketplace' ),
						'id'            => 'sidebar-wcfmmp-store-lists',
						'before_widget' => '<aside id="%1$s" class="widget sidebar-box clr %2$s">',
						'after_widget'  => '</aside>',
						'before_title'  => '<div class="sidebar_heading"><h4 class="widget-title">',
						'after_title'   => '</h4></div>',
				)
			)
		);
	}
	
	/**
	 * Register Store Widgets
	 */
	function wcfmmp_register_widgets() {
		
		// Store Widgets
		register_widget( 'WCFMmp_Store_Info' );
		register_widget( 'WCFMmp_Store_Location' );
		register_widget( 'WCFMmp_Store_Category' );
		register_widget( 'WCFMmp_Store_Taxonomy' );
		register_widget( 'WCFMmp_Store_Hours_Widget' );
		register_widget( 'WCFMmp_Store_Shipping_Rules' );
		register_widget( 'WCFMmp_Store_Coupons' );
		register_widget( 'WCFMmp_Store_Product_Search' );
		register_widget( 'WCFMmp_Store_Featured_Product' );
		register_widget( 'WCFMmp_Store_Top_Products' );
		register_widget( 'WCFMmp_Store_Top_Rated_Products' );
		register_widget( 'WCFMmp_Store_On_Sale_Products' );
		register_widget( 'WCFMmp_Store_Recent_Products' );
		register_widget( 'WCFMmp_Store_Recent_Articles' );
    
    // Store Lists Widgerts
    register_widget( 'WCFMmp_Store_Lists_Search' );
    register_widget( 'WCFMmp_Store_Lists_Category_Filter' );
    register_widget( 'WCFMmp_Store_Lists_Location_Filter' );
    register_widget( 'WCFMmp_Store_Lists_Radius_Filter' );
    register_widget( 'WCFMmp_Store_Lists_Meta_Filter' );
    
    // Global Widgets
    register_widget( 'WCFMmp_Store_Top_Rated_Vendors' );
    register_widget( 'WCFMmp_Store_Best_Selling_Vendors' );
    register_widget( 'WCFMmp_Products_Search_by_Vendors' );
	}
	
	public function load_widgets($widget = '') {
		global $WCFM, $WCFMmp;
		if ( '' != $widget ) {
			if( file_exists( $WCFMmp->plugin_path . 'includes/store-widgets/class-wcfmmp-widget-' . esc_attr($widget) . '.php' ) ) {
				require_once ( $WCFMmp->plugin_path . 'includes/store-widgets/class-wcfmmp-widget-' . esc_attr($widget) . '.php' );
			}
		} // End If Statement
	}
	
}