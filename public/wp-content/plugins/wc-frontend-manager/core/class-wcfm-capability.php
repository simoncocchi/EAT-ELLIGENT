<?php
/**
 * WCFM plugin core
 *
 * Plugin Capability Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   2.3.1
 */
 
class WCFM_Capability {
	
	private $wcfm_capability_options = array();

	public function __construct() {
		global $WCFM;
		
		$this->wcfm_capability_options = apply_filters( 'wcfm_capability_options_rules', get_option( 'wcfm_capability_options', array() ) );
		
		// Menu Filter
		add_filter( 'wcfm_menus', array( &$this, 'wcfmcap_wcfm_menus' ), 500 );
		add_filter( 'wcfm_product_menu', array( &$this, 'wcfmcap_product_menu' ), 500 );
		
		
		// Manage Product Permission
		add_filter( 'wcfm_is_allow_manage_products', array( &$this, 'wcfmcap_is_allow_manage_products' ), 500 );
		add_filter( 'wcfm_add_new_product_sub_menu', array( &$this, 'wcfmcap_is_allow_add_products' ), 500 );
		add_filter( 'wcfm_is_allow_add_products', array( &$this, 'wcfmcap_is_allow_add_products' ), 500 );
		add_filter( 'wcfm_is_allow_publish_products', array( &$this, 'wcfmcap_is_allow_publish_products' ), 500 );
		add_filter( 'wcfm_is_allow_edit_products', array( &$this, 'wcfmcap_is_allow_edit_products' ), 500 );
		add_filter( 'wcfm_is_allow_publish_live_products', array( &$this, 'wcfmcap_is_allow_publish_live_products' ), 500 );
		add_filter( 'wcfm_is_allow_delete_products', array( &$this, 'wcfmcap_is_allow_delete_products' ), 500 );
		add_filter( 'wcfm_is_allow_space_limit', array( &$this, 'wcfmcap_is_allow_space_limit' ), 500 );
		add_filter( 'wcfm_is_allow_product_limit', array( &$this, 'wcfmcap_is_allow_product_limit' ), 500 );
		add_filter( 'wcfm_products_limit_label', array( &$this, 'wcfmcap_products_limit_label' ), 50 );
		add_filter( 'wcfm_is_allow_verification_product_limit', array( &$this, 'wcfmcap_is_allow_verificaton_product_limit' ), 500 );
		add_filter( 'wcfm_product_types', array( &$this, 'wcfmcap_is_allow_product_types'), 500 );
		add_filter( 'product_type_selector', array( &$this, 'wcfmcap_is_allow_product_types'), 500 ); // WC Product Types
		add_filter( 'wcfm_is_allow_job_package', array( &$this, 'wcfmcap_is_allow_job_package'), 500 );
		add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcfmcap_is_allow_fields_general' ), 500 );
		add_filter( 'wcfm_product_manage_fields_images', array( &$this, 'wcfmcap_is_allow_fields_images' ), 500 );
		add_filter( 'wcfm_is_allow_inventory', array( &$this, 'wcfmcap_is_allow_inventory' ), 500 );
		add_filter( 'wcfm_is_allow_shipping', array( &$this, 'wcfmcap_is_allow_shipping' ), 500 );
		add_filter( 'wcfm_is_allow_tax', array( &$this, 'wcfmcap_is_allow_tax' ), 500 );
		add_filter( 'wcfm_is_allow_attribute', array( &$this, 'wcfmcap_is_allow_attribute' ), 500 );
		add_filter( 'wcfm_is_allow_variable', array( &$this, 'wcfmcap_is_allow_variable' ), 500 );
		add_filter( 'wcfm_is_allow_linked', array( &$this, 'wcfmcap_is_allow_linked' ), 500 );
		add_filter( 'wcfm_is_allow_catalog', array( &$this, 'wcfmcap_is_allow_catalog' ), 500 );
		
		// Article Filter
		add_filter( 'wcfm_is_allow_manage_articles', array( &$this, 'wcfmcap_is_allow_manage_articles' ), 500 );
		add_filter( 'wcfm_article_menu', array( &$this, 'wcfmcap_is_allow_manage_articles' ), 500 );
		add_filter( 'wcfm_add_new_article_sub_menu', array( &$this, 'wcfmcap_is_allow_add_articles' ), 500 );
		add_filter( 'wcfm_is_allow_add_articles', array( &$this, 'wcfmcap_is_allow_add_articles' ), 500 );
		add_filter( 'wcfm_is_allow_edit_articles', array( &$this, 'wcfmcap_is_allow_edit_articles' ), 500 );
		add_filter( 'wcfm_is_allow_publish_articles', array( &$this, 'wcfmcap_is_allow_publish_articles' ), 500 );
		add_filter( 'wcfm_is_allow_publish_live_articles', array( &$this, 'wcfmcap_is_allow_publish_live_articles' ), 500 );
		add_filter( 'wcfm_is_allow_delete_articles', array( &$this, 'wcfmcap_is_allow_delete_articles' ), 500 );
		add_filter( 'wcfm_is_allow_article_limit', array( &$this, 'wcfmcap_is_allow_article_limit' ), 500 );
		add_filter( 'wcfm_articles_limit_label', array( &$this, 'wcfmcap_articles_limit_label' ), 50 );
		
		// Manage Coupon Permission
		add_filter( 'wcfm_is_allow_manage_coupons', array( &$this, 'wcfmcap_is_allow_manage_coupons' ), 500 );
		add_filter( 'wcfm_coupon_menu', array( &$this, 'wcfmcap_coupon_menu' ), 500 );
		add_filter( 'wcfm_add_new_coupon_sub_menu', array( &$this, 'wcfmcap_is_allow_add_coupons' ),500 );
		add_filter( 'wcfm_is_allow_add_coupons', array( &$this, 'wcfmcap_is_allow_add_coupons' ), 500 );
		add_filter( 'wcfm_is_allow_publish_coupons', array( &$this, 'wcfmcap_is_allow_publish_coupons' ), 500 );
		add_filter( 'wcfm_is_allow_edit_coupons', array( &$this, 'wcfmcap_is_allow_edit_coupons' ), 500 );
		add_filter( 'wcfm_is_allow_publish_live_coupons', array( &$this, 'wcfmcap_is_allow_publish_live_coupons' ), 500 );
		add_filter( 'wcfm_is_allow_delete_coupons', array( &$this, 'wcfmcap_is_allow_delete_coupons' ), 500 );
		add_filter( 'wcfm_is_allow_free_shipping_coupons', array( &$this, 'wcfmcap_is_allow_free_shipping_coupons' ), 500 );
		
		// Manage Listings Permission
		add_filter( 'wcfm_is_allow_listings', array( &$this, 'wcfmcap_is_allow_listings'), 500 );
		
		// Manage Product Export Permission - 2.4.2
		add_filter( 'woocommerce_product_export_product_default_columns', array( &$this, 'wcfmcap_is_allow_product_columns'), 500 ); // WC Product Columns
		
		// Manage Product Import Permission - 2.4.2
		//add_filter( 'woocommerce_csv_product_import_mapping_options', array( &$this, 'wcfmcap_is_allow_product_columns'), 500 ); // WC Product Columns
		
		// Manage Order Permission
		add_filter( 'wcfm_is_allow_orders', array( &$this, 'wcfmcap_is_allow_orders' ), 500 );
		add_filter( 'wcfm_is_allow_order_status_update', array( &$this, 'wcfmcap_is_allow_order_status_update' ), 500 );
		add_filter( 'wcfm_allow_order_details', array( &$this, 'wcfmcap_is_allow_order_details' ), 500 );
		add_filter( 'wcfm_is_allow_order_details', array( &$this, 'wcfmcap_is_allow_order_details' ), 500 );
		add_filter( 'wcfm_is_allow_manage_order', array( &$this, 'wcfmcap_is_allow_manage_order' ), 500 );
		add_filter( 'wcfm_is_allow_order_delete', array( &$this, 'wcfmcap_is_allow_order_delete' ), 500 );
		add_filter( 'wcfm_allow_customer_billing_details', array( &$this, 'wcfmcap_is_allow_customer_billing_details' ), 500 );
		add_filter( 'wcfm_allow_customer_shipping_details', array( &$this, 'wcfmcap_is_allow_customer_shipping_details' ), 500 );
		add_filter( 'show_customer_billing_address_in_export_orders', array( &$this, 'wcfmcap_is_allow_customer_billing_details' ), 500 ); // WC Marketplace
		add_filter( 'show_customer_shipping_address_in_export_orders', array( &$this, 'wcfmcap_is_allow_customer_shipping_details' ), 500 ); // WC Marketplace
		add_filter( 'wcfm_is_allow_export_csv', array( &$this, 'wcfmcap_is_allow_export_csv' ), 500 );
		add_filter( 'wcfm_is_allow_view_commission', array( &$this, 'wcfmcap_is_allow_view_commission' ), 500 );
		add_filter( 'wcfm_sales_report_is_allow_earning', array( &$this, 'wcfmcap_is_allow_view_commission' ), 500 );
		
		add_filter( 'wcfm_is_allow_store_invoice', array( &$this, 'wcfmcap_is_allow_store_invoice' ), 500 );
		add_filter( 'wcfm_is_allow_pdf_invoice', array( &$this, 'wcfmcap_is_allow_pdf_invoice' ), 500 );
		add_filter( 'wcfm_is_allow_pdf_packing_slip', array( &$this, 'wcfmcap_is_allow_pdf_packing_slip' ), 500 );
		
		// Customers Filter
		add_filter( 'wcfm_customer_menu', array( &$this, 'wcfmcap_is_allow_manage_customers' ), 500 );
		add_filter( 'wcfm_is_allow_customers', array( &$this, 'wcfmcap_is_allow_manage_customers' ), 500 );
		add_filter( 'wcfm_is_allow_manage_customer', array( &$this, 'wcfmcap_is_allow_manage_customers' ), 500 );
		add_filter( 'wcfm_add_new_customer_sub_menu', array( &$this, 'wcfmcap_is_allow_add_customers' ), 500 );
		add_filter( 'wcfm_is_allow_add_customer', array( &$this, 'wcfmcap_is_allow_add_customers' ), 500 );
		add_filter( 'wcfm_is_allow_edit_customer', array( &$this, 'wcfmcap_is_allow_edit_customers' ), 500 );
		add_filter( 'wcfm_is_allow_view_customer', array( &$this, 'wcfmcap_is_allow_view_customers' ), 500 );
		add_filter( 'wcfm_is_allow_delete_customer', array( &$this, 'wcfmcap_is_allow_delete_customer' ), 500 );
		add_filter( 'wcfm_is_allow_customer_details_orders', array( &$this, 'wcfmcap_is_allow_customer_details_orders' ), 500 );
		add_filter( 'show_customer_details_in_export_orders', array( &$this, 'wcfmcap_is_allow_customer_details_orders' ), 500 ); // WC Marketplace
		add_filter( 'wcfm_allow_order_customer_details', array( &$this, 'wcfmcap_is_allow_view_customer_email' ), 500 );
		add_filter( 'wcfm_allow_view_customer_email', array( &$this, 'wcfmcap_is_allow_view_customer_email' ), 500 );
		add_filter( 'wcfm_allow_view_customer_name', array( &$this, 'wcfmcap_is_allow_view_customer_name' ), 500 );
		add_filter( 'wcfm_is_allow_customer_limit', array( &$this, 'wcfmcap_is_allow_customer_limit' ), 500 );
		add_filter( 'wcfm_customers_limit_label', array( &$this, 'wcfmcap_customers_limit_label' ), 50 );
		
		// Marketplace Permission
		add_filter( 'wcfm_is_allow_show_email', array( &$this, 'wcfmcap_is_allow_show_email' ), 500 );
		add_filter( 'wcfm_is_allow_show_phone', array( &$this, 'wcfmcap_is_allow_show_phone' ), 500 );
		add_filter( 'wcfm_is_allow_show_address', array( &$this, 'wcfmcap_is_allow_show_address' ), 500 );
		add_filter( 'wcfm_is_allow_show_map', array( &$this, 'wcfmcap_is_allow_show_map' ), 500 );
		add_filter( 'wcfm_is_allow_show_social', array( &$this, 'wcfmcap_is_allow_show_social' ), 500 );
		add_filter( 'wcfm_is_allow_show_follower', array( &$this, 'wcfmcap_is_allow_show_follower' ), 500 );
		add_filter( 'wcfm_is_allow_show_policy', array( &$this, 'wcfmcap_is_allow_show_policy' ), 500 );
		add_filter( 'wcfm_is_allow_customer_support', array( &$this, 'wcfmcap_is_allow_customer_support' ), 500 );
		add_filter( 'wcfm_is_allow_refund_requests', array( &$this, 'wcfmcap_is_allow_refund_requests' ), 500 );
		add_filter( 'wcfm_is_allow_reviews', array( &$this, 'wcfmcap_is_allow_reviews' ), 500 );
		add_filter( 'wcfm_is_allow_manage_review', array( &$this, 'wcfmcap_is_allow_manage_review' ), 500 );
		add_filter( 'wcfm_is_allow_ledger', array( &$this, 'wcfmcap_is_allow_ledger_book' ), 500 );
		add_filter( 'wcfm_is_allow_store_hours', array( &$this, 'wcfmcap_is_allow_store_hours' ), 500 );
		add_filter( 'wcfmmp_is_allow_single_product_multivendor', array( &$this, 'wcfmcap_is_allow_product_multivendor' ), 500 );
		add_filter( 'wcfmmp_is_allow_video_banner', array( &$this, 'wcfmcap_is_allow_video_banner' ), 500 );
		add_filter( 'wcfmmp_is_allow_slider_banner', array( &$this, 'wcfmcap_is_allow_slider_banner' ), 500 );
		
		// Settings Inside
		add_filter( 'wcfm_is_allow_store_name', array( &$this, 'wcfmcap_is_allow_store_name' ), 500 );
		add_filter( 'wcfm_is_allow_store_phone', array( &$this, 'wcfmcap_is_allow_store_phone' ), 500 );
		add_filter( 'wcfm_is_allow_store_logo', array( &$this, 'wcfmcap_is_allow_store_logo' ), 500 );
		add_filter( 'wcfm_is_allow_store_banner', array( &$this, 'wcfmcap_is_allow_store_banner' ), 500 );
		add_filter( 'wcfm_is_allow_store_description', array( &$this, 'wcfmcap_is_allow_store_description' ), 500 );
		add_filter( 'wcfm_is_allow_store_address', array( &$this, 'wcfmcap_is_allow_store_address' ), 500 );
		
		// Withdrwal Permission
		add_filter( 'wcfm_is_allow_withdrawal', array( &$this, 'wcfmcap_is_allow_withdrawal' ), 500 );
		add_filter( 'wcfm_is_allow_payments', array( &$this, 'wcfmcap_is_allow_payments' ), 500 );
		add_filter( 'wcfm_is_allow_transaction_details', array( &$this, 'wcfmcap_is_allow_transaction_details' ), 500 );
		
		// Manage Reports Permission
		add_filter( 'wcfm_is_allow_reports', array( &$this, 'wcfmcap_is_allow_reports' ), 500 );
		
		// Vendors
		add_filter( 'wcfm_is_allow_vendors', array( &$this, 'wcfmcap_is_allow_vendors' ), 500 );
		
		// Capability Controller
		add_filter( 'wcfm_is_allow_capability_controller', array( &$this, 'wcfmcap_is_allow_capability_controller' ), 500 );
		
		// Custom Caps
		add_filter( 'wcfm_is_allow_commission_manage', array( &$this, 'wcfmcap_is_allow_commission_manage' ), 500 );
		add_filter( 'wcfm_allow_wp_admin_view', array( &$this, 'wcfmcap_is_allow_wp_admin_view' ), 500 );
		
		// Integrations
		add_filter( 'wcfm_is_allow_wc_product_scheduler', array( &$this, 'wcfmcap_is_allow_wc_product_scheduler' ), 500 );
	}
	
	// WCFM wcfmcap Menu
  function wcfmcap_wcfm_menus( $menus ) {
  	global $WCFM;
  	
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	
  	$manage_coupons = ( isset( $this->wcfm_capability_options['submit_coupons'] ) ) ? $this->wcfm_capability_options['submit_coupons'] : 'no';
  	$manage_coupons = ( isset( $this->wcfm_capability_options['manage_coupons'] ) ) ? $this->wcfm_capability_options['manage_coupons'] : $manage_coupons;
  	
  	$view_orders  = ( isset( $this->wcfm_capability_options['view_orders'] ) ) ? $this->wcfm_capability_options['view_orders'] : 'no';
  	$view_reports  = ( isset( $this->wcfm_capability_options['view_reports'] ) ) ? $this->wcfm_capability_options['view_reports'] : 'no';
  	$manage_booking = ( isset( $this->wcfm_capability_options['manage_booking'] ) ) ? $this->wcfm_capability_options['manage_booking'] : 'no';
  	
  	if( $manage_products == 'yes' ) unset( $menus['wcfm-products'] );
  	if( ( $manage_coupons == 'yes' ) ) unset( $menus['wcfm-coupons'] );
  	if( $view_orders == 'yes' ) unset( $menus['wcfm-orders'] );
  	if( $view_reports == 'yes' ) unset( $menus['wcfm-reports'] );
  	if( $manage_booking == 'yes' ) unset( $menus['wcfm-bookings-dashboard'] );
  	
  	if( !current_user_can('administrator') ) unset( $menus['wcfm-capability'] );
  	
  	return $menus;
  }
  
  // WCFM Product Menu
  function wcfmcap_product_menu( $has_new ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) $has_new = false;
  	//if( !current_user_can( 'edit_products' ) ) $has_new = false;
  	return $has_new;
  }
  
  // WCFM wcfmcap Manage Products
  function wcfmcap_is_allow_manage_products( $allow ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) return false;
  	//if( !current_user_can( 'edit_products' ) ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Products
  function wcfmcap_is_allow_add_products( $allow ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) return false;
  	$add_products = ( isset( $this->wcfm_capability_options['add_products'] ) ) ? $this->wcfm_capability_options['add_products'] : 'no';
  	if( $add_products == 'yes' ) return false;
  	//if( !current_user_can( 'edit_products' ) ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Publish Products
  function wcfmcap_is_allow_publish_products( $allow ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) return false;
  	$publish_products = ( isset( $this->wcfm_capability_options['publish_products'] ) ) ? $this->wcfm_capability_options['publish_products'] : 'no';
  	if( $publish_products == 'yes' ) return false;
  	//if( !current_user_can( 'publish_products' ) ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Edit Products
  function wcfmcap_is_allow_edit_products( $allow ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) return false;
  	$edit_products = ( isset( $this->wcfm_capability_options['edit_live_products'] ) ) ? $this->wcfm_capability_options['edit_live_products'] : 'no';
  	$edit_products = ( isset( $this->wcfm_capability_options['edit_products'] ) ) ? $this->wcfm_capability_options['edit_products'] : $edit_products;
  	if( $edit_products == 'yes' ) return false;
  	//if( !current_user_can( 'edit_published_products' ) ) return false;
  	return $allow;
  }
  
  // WCFM auto publish live products
  function wcfmcap_is_allow_publish_live_products( $allow ) {
  	$publish_live_products = ( isset( $this->wcfm_capability_options['publish_live_products'] ) ) ? $this->wcfm_capability_options['publish_live_products'] : 'no';
  	if( $publish_live_products == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Delete Products
  function wcfmcap_is_allow_delete_products( $allow ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) return false;
  	$delete_products = ( isset( $this->wcfm_capability_options['delete_products'] ) ) ? $this->wcfm_capability_options['delete_products'] : 'no';
  	if( $delete_products == 'yes' ) return false;
  	//if( !current_user_can( 'delete_published_products' ) ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Products by Space Limit
  function wcfmcap_is_allow_space_limit( $allow ) {
  	global $WCFM;
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	$manage_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $manage_products == 'yes' && $manage_articles == 'yes' ) return false;
  	$add_products = ( isset( $this->wcfm_capability_options['add_products'] ) ) ? $this->wcfm_capability_options['add_products'] : 'no';
  	$add_articles = ( isset( $this->wcfm_capability_options['add_articles'] ) ) ? $this->wcfm_capability_options['add_articles'] : 'no';
  	if( $add_products == 'yes' && $add_articles == 'yes' ) return false;
  	
  	// Limit Restriction
  	$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
  	$spacelimit = ( isset( $this->wcfm_capability_options['spacelimit'] ) ) ? $this->wcfm_capability_options['spacelimit'] : '';
  	if( ( $spacelimit == -1 ) || ( $spacelimit == '-1' ) ) $spacelimit = -1;
  	elseif( $spacelimit ) $spacelimit = absint($spacelimit);
  	$spacelimit = apply_filters( 'wcfm_vendor_verification_space_limit', $spacelimit, $current_user_id );
  	$spacelimit = apply_filters( 'wcfm_vendor_space_limit', $spacelimit, $current_user_id );
  	if( ( $spacelimit == -1 ) || ( $spacelimit == '-1' ) ) {
  		return false;
  	} else {
			if( $spacelimit ) $spacelimit = absint($spacelimit);
			if( $spacelimit && ( $spacelimit >= 0 ) ) {
				if( $spacelimit == 1989 ) return false;
				$used_space  = $WCFM->wcfm_vendor_support->wcfm_get_used_space_by_vendor( $current_user_id );
				if( $spacelimit <= $used_space ) return false;
			}
		}
  	return $allow;
  }
  
  // WCFM wcfmcap Add Products by Product Limit
  function wcfmcap_is_allow_product_limit( $allow ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) return false;
  	$add_products = ( isset( $this->wcfm_capability_options['add_products'] ) ) ? $this->wcfm_capability_options['add_products'] : 'no';
  	if( $add_products == 'yes' ) return false;
  	
  	// Limit Restriction
  	$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
  	$productlimit = ( isset( $this->wcfm_capability_options['productlimit'] ) ) ? $this->wcfm_capability_options['productlimit'] : '';
  	if( ( $productlimit == -1 ) || ( $productlimit == '-1' ) ) $productlimit = -1;
  	elseif( $productlimit ) $productlimit = absint($productlimit);
  	$productlimit = apply_filters( 'wcfm_vendor_verification_product_limit', $productlimit, $current_user_id );
  	$productlimit = apply_filters( 'wcfm_vendor_product_limit', $productlimit, $current_user_id );
  	if( ( $productlimit == -1 ) || ( $productlimit == '-1' ) ) {
  		return false;
  	} else {
			if( $productlimit ) $productlimit = absint($productlimit);
			if( $productlimit && ( $productlimit >= 0 ) ) {
				if( $productlimit == 1989 ) return false;
				$count_products  = wcfm_get_user_posts_count( $current_user_id, 'product', apply_filters( 'wcfm_limit_check_status', 'any' ), array( 'suppress_filters' => 1 ) );
				if( $productlimit <= $count_products ) return false;
			}
		}
  	return $allow;
  }
  
  // WCFM Product Limit Label
  function wcfmcap_products_limit_label( $label ) {
  	
  	if( current_user_can( 'administrator' ) || !apply_filters( 'wcfm_is_allow_limit_label', true ) || !apply_filters( 'wcfm_is_allow_product_limit_label', true ) ) return'';
  	
  	$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
  	$label = __( 'Products Limit: ', 'wc-frontend-manager' );
  	
  	$productlimit = ( isset( $this->wcfm_capability_options['productlimit'] ) ) ? $this->wcfm_capability_options['productlimit'] : '';
  	if( ( $productlimit == -1 ) || ( $productlimit == '-1' ) ) $productlimit = 1;
  	elseif( $productlimit ) $productlimit = absint($productlimit);
  	$productlimit = apply_filters( 'wcfm_vendor_verification_product_limit', $productlimit, $current_user_id );
  	$productlimit = apply_filters( 'wcfm_vendor_product_limit', $productlimit, $current_user_id );
  	if( ( $productlimit == -1 ) || ( $productlimit == '-1' ) ) {
			$label .= ' 0 ' . __( 'remaining', 'wc-frontend-manager' );
		} else {
			if( $productlimit ) $productlimit = absint($productlimit);
			if( $productlimit && ( $productlimit >= 0 ) ) {
				if( $productlimit == 1989 ) {
					$label .= ' 0 ' . __( 'remaining', 'wc-frontend-manager' );
				} else {
					$count_products  = wcfm_get_user_posts_count( $current_user_id, 'product', apply_filters( 'wcfm_limit_check_status', 'any' ), array( 'suppress_filters' => 1 ) );
					$label .= ' ' . ( $productlimit - $count_products ) . ' ' . __( 'remaining', 'wc-frontend-manager' );
				}
			} else {
				$label .= __( 'Unlimited', 'wc-frontend-manager' );
			}
		}
  	
  	$label = '<span class="wcfm_products_limit_label">' . $label . '</span>';
  	
  	return $label;
  }
  
  // WCFM wcfmcap Add Products by Verification Product Limit
  function wcfmcap_is_allow_verificaton_product_limit( $allow ) {
  	$manage_products = ( isset( $this->wcfm_capability_options['submit_products'] ) ) ? $this->wcfm_capability_options['submit_products'] : 'no';
  	$manage_products = ( isset( $this->wcfm_capability_options['manage_products'] ) ) ? $this->wcfm_capability_options['manage_products'] : $manage_products;
  	if( $manage_products == 'yes' ) return false;
  	$add_products = ( isset( $this->wcfm_capability_options['add_products'] ) ) ? $this->wcfm_capability_options['add_products'] : 'no';
  	if( $add_products == 'yes' ) return false;
  	
  	// Limit Restriction
  	$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
  	$productlimit = '';
  	$productlimit = apply_filters( 'wcfm_vendor_verification_product_limit', $productlimit, $current_user_id );
  	if( ( $productlimit == -1 ) || ( $productlimit == '-1' ) ) {
  		return false;
  	} else {
			if( $productlimit ) $productlimit = absint($productlimit);
			if( $productlimit && ( $productlimit >= 0 ) ) {
				if( $productlimit == 1989 ) return false;
				$count_products  = wcfm_get_user_posts_count( $current_user_id, 'product', apply_filters( 'wcfm_limit_check_status', 'any' ), array( 'suppress_filters' => 1 ) );
				if( $productlimit <= $count_products ) return false;
			}
		}
  	return $allow;
  }
	
  // Product Types
  function wcfmcap_is_allow_product_types( $product_types ) {
  	
  	$simple = ( isset( $this->wcfm_capability_options['simple'] ) ) ? $this->wcfm_capability_options['simple'] : 'no';
		$variable = ( isset( $this->wcfm_capability_options['variable'] ) ) ? $this->wcfm_capability_options['variable'] : 'no';
		$grouped = ( isset( $this->wcfm_capability_options['grouped'] ) ) ? $this->wcfm_capability_options['grouped'] : 'no';
		$external = ( isset( $this->wcfm_capability_options['external'] ) ) ? $this->wcfm_capability_options['external'] : 'no';
		$booking = ( isset( $this->wcfm_capability_options['booking'] ) ) ? $this->wcfm_capability_options['booking'] : 'no';
		$accommodation = ( isset( $this->wcfm_capability_options['accommodation'] ) ) ? $this->wcfm_capability_options['accommodation'] : 'no';
		$appointment = ( isset( $this->wcfm_capability_options['appointment'] ) ) ? $this->wcfm_capability_options['appointment'] : 'no';
		$job_package = ( isset( $this->wcfm_capability_options['job_package'] ) ) ? $this->wcfm_capability_options['job_package'] : 'no';
		$resume_package = ( isset( $this->wcfm_capability_options['resume_package'] ) ) ? $this->wcfm_capability_options['resume_package'] : 'no';
		$auction = ( isset( $this->wcfm_capability_options['auction'] ) ) ? $this->wcfm_capability_options['auction'] : 'no';
		$rental = ( isset( $this->wcfm_capability_options['rental'] ) ) ? $this->wcfm_capability_options['rental'] : 'no';
		$lottery = ( isset( $this->wcfm_capability_options['lottery'] ) ) ? $this->wcfm_capability_options['lottery'] : 'no';
		$subscription = ( isset( $this->wcfm_capability_options['subscription'] ) ) ? $this->wcfm_capability_options['subscription'] : 'no';
		$variable_subscription = ( isset( $this->wcfm_capability_options['variable-subscription'] ) ) ? $this->wcfm_capability_options['variable-subscription'] : 'no';
		$attributes = ( isset( $this->wcfm_capability_options['attributes'] ) ) ? $this->wcfm_capability_options['attributes'] : 'no';
  	
  	if( $simple == 'yes' ) unset( $product_types[ 'simple' ] );
		if( $variable == 'yes' ) unset( $product_types[ 'variable' ] );
		if( $grouped == 'yes' ) unset( $product_types[ 'grouped' ] );
		if( $external == 'yes' ) unset( $product_types[ 'external' ] );
		if( $booking == 'yes' ) unset( $product_types[ 'booking' ] );
		if( $accommodation == 'yes' ) unset( $product_types[ 'accommodation-booking' ] );
		if( $appointment == 'yes' ) unset( $product_types[ 'appointment' ] );
		if( $job_package == 'yes' ) unset( $product_types[ 'job_package' ] );
		if( $resume_package == 'yes' ) unset( $product_types[ 'resume_package' ] );
		if( $auction == 'yes' ) unset( $product_types[ 'auction' ] );
		if( $rental == 'yes' ) unset( $product_types[ 'redq_rental' ] );
		if( $lottery == 'yes' ) unset( $product_types[ 'lottery' ] );
		if( $subscription == 'yes' ) unset( $product_types[ 'subscription' ] );
  	if( $variable_subscription == 'yes' ) unset( $product_types[ 'variable-subscription' ] );
  	if( $attributes == 'yes' ) unset( $product_types[ 'variable' ] );
  	if( $attributes == 'yes' ) unset( $product_types[ 'variable-subscription' ] );
  	
  	$product_types = apply_filters( 'wcfm_allowed_product_types', $product_types, $this->wcfm_capability_options );
		
		return $product_types;
  }
  
  // Job Package
  function wcfmcap_is_allow_job_package( $allow ) {
  	$job_package = ( isset( $this->wcfm_capability_options['job_package'] ) ) ? $this->wcfm_capability_options['job_package'] : 'no';
  	if( $job_package == 'yes' ) return false;
  	return $allow;
  }
  
  // General Fields
  function wcfmcap_is_allow_fields_general( $general_fields ) {
  	$virtual = ( isset( $this->wcfm_capability_options['virtual'] ) ) ? $this->wcfm_capability_options['virtual'] : 'no';
  	$downloadable = ( isset( $this->wcfm_capability_options['downloadable'] ) ) ? $this->wcfm_capability_options['downloadable'] : 'no';
  	if( $virtual == 'yes' ) unset( $general_fields['is_virtual'] );
  	if( $downloadable == 'yes' ) unset( $general_fields['is_downloadable'] );
  	return $general_fields;
  }
  
  // Image Fields
  function wcfmcap_is_allow_fields_images( $image_fields ) {
  	$gallery_img = ( isset( $this->wcfm_capability_options['gallery_img'] ) ) ? $this->wcfm_capability_options['gallery_img'] : 'no';
  	if( $gallery_img == 'yes' ) {
  		if( isset( $image_fields['gallery_img'] ) ) unset( $image_fields['gallery_img'] );
  	}
  	return $image_fields;
  }
  
  // Inventory
  function wcfmcap_is_allow_inventory( $allow ) {
  	$inventory = ( isset( $this->wcfm_capability_options['inventory'] ) ) ? $this->wcfm_capability_options['inventory'] : 'no';
  	if( $inventory == 'yes' ) return false;
  	return $allow;
  }
  
  // Shipping
  function wcfmcap_is_allow_shipping( $allow ) {
  	$shipping = ( isset( $this->wcfm_capability_options['shipping'] ) ) ? $this->wcfm_capability_options['shipping'] : 'no';
  	if( $shipping == 'yes' ) return false;
  	return $allow;
  }
  
  // Tax
  function wcfmcap_is_allow_tax( $allow ) {
  	$taxes = ( isset( $this->wcfm_capability_options['taxes'] ) ) ? $this->wcfm_capability_options['taxes'] : 'no';
  	if( $taxes == 'yes' ) return false;
  	return $allow;
  }
  
  // Attributes
  function wcfmcap_is_allow_attribute( $allow ) {
  	$attributes = ( isset( $this->wcfm_capability_options['attributes'] ) ) ? $this->wcfm_capability_options['attributes'] : 'no';
  	if( $attributes == 'yes' ) return false;
  	return $allow;
  }
  
  // Variable
  function wcfmcap_is_allow_variable( $allow ) {
  	$attributes = ( isset( $this->wcfm_capability_options['attributes'] ) ) ? $this->wcfm_capability_options['attributes'] : 'no';
  	$variable = ( isset( $this->wcfm_capability_options['variable'] ) ) ? $this->wcfm_capability_options['variable'] : 'no';
  	$variable_subscription = ( isset( $this->wcfm_capability_options['variable-subscription'] ) ) ? $this->wcfm_capability_options['variable-subscription'] : 'no';
  	
  	if( ( $attributes == 'yes' ) && ( $variable == 'yes' ) && ( $variable_subscription == 'yes' ) ) return false;
  	return $allow;
  }
  
  // Linked
  function wcfmcap_is_allow_linked( $allow ) {
  	$linked = ( isset( $this->wcfm_capability_options['linked'] ) ) ? $this->wcfm_capability_options['linked'] : 'no';
  	if( $linked == 'yes' ) return false;
  	return $allow;
  }
  
  // Catalog
  function wcfmcap_is_allow_catalog( $allow ) {
  	$catalog = ( isset( $this->wcfm_capability_options['catalog'] ) ) ? $this->wcfm_capability_options['catalog'] : 'no';
  	if( $catalog == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Manage Articles
  function wcfmcap_is_allow_manage_articles( $allow ) {
  	$manage_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $manage_articles == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Articles
  function wcfmcap_is_allow_add_articles( $allow ) {
  	$manage_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $manage_articles == 'yes' ) return false;
  	$add_articles = ( isset( $this->wcfm_capability_options['add_articles'] ) ) ? $this->wcfm_capability_options['add_articles'] : 'no';
  	if( $add_articles == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Edit Articles
  function wcfmcap_is_allow_edit_articles( $allow ) {
  	$manage_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $manage_articles == 'yes' ) return false;
  	$edit_articles = ( isset( $this->wcfm_capability_options['edit_live_articles'] ) ) ? $this->wcfm_capability_options['edit_live_articles'] : 'no';
  	if( $edit_articles == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Publish Articles
  function wcfmcap_is_allow_publish_articles( $allow ) {
  	$manage_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $manage_articles == 'yes' ) return false;
  	$publish_articles = ( isset( $this->wcfm_capability_options['publish_articles'] ) ) ? $this->wcfm_capability_options['publish_articles'] : 'no';
  	if( $publish_articles == 'yes' ) return false;                
  	return $allow;
  }
  
  // WCFM auto publish live articles
  function wcfmcap_is_allow_publish_live_articles( $allow ) {
  	$manage_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $manage_articles == 'yes' ) return false;
  	$publish_live_articles = ( isset( $this->wcfm_capability_options['publish_live_articles'] ) ) ? $this->wcfm_capability_options['publish_live_articles'] : 'no';
  	if( $publish_live_articles == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Delete Articles
  function wcfmcap_is_allow_delete_articles( $allow ) {
  	$manage_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $manage_articles == 'yes' ) return false;
  	$delete_articles = ( isset( $this->wcfm_capability_options['delete_articles'] ) ) ? $this->wcfm_capability_options['delete_articles'] : 'no';
  	if( $delete_articles == 'yes' ) return false;              
  	return $allow;
  }
  
  // WCFM wcfmcap Add Article Limit
  function wcfmcap_is_allow_article_limit( $allow ) {
  	$submit_articles = ( isset( $this->wcfm_capability_options['submit_articles'] ) ) ? $this->wcfm_capability_options['submit_articles'] : 'no';
  	if( $submit_articles == 'yes' ) return false;
  	$add_articles = ( isset( $this->wcfm_capability_options['add_articles'] ) ) ? $this->wcfm_capability_options['add_articles'] : 'no';
  	if( $add_articles == 'yes' ) return false;
  	
  	// Limit Restriction
  	$articlelimit = ( isset( $this->wcfm_capability_options['articlelimit'] ) ) ? $this->wcfm_capability_options['articlelimit'] : '';
  	if( ( $articlelimit == -1 ) || ( $articlelimit == '-1' ) ) {
  		return false;
  	} else {
			if( $articlelimit ) $articlelimit = absint($articlelimit);
			if( $articlelimit && ( $articlelimit >= 0 ) ) {
				$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
				$count_articles  = wcfm_get_user_posts_count( $current_user_id, 'post', apply_filters( 'wcfm_limit_check_status', 'any' ), array( 'suppress_filters' => 1 ) );
				if( $articlelimit <= $count_articles ) return false;
			}
		}
  	return $allow;
  }
  
  // WCFM Article Limit Label
  function wcfmcap_articles_limit_label( $label ) {
  	
  	if( current_user_can( 'administrator' ) || !apply_filters( 'wcfm_is_allow_limit_label', true ) || !apply_filters( 'wcfm_is_allow_article_limit_label', true ) ) return'';
  	
  	$label = __( 'Articles Limit: ', 'wc-frontend-manager' );
  	
  	$articlelimit = ( isset( $this->wcfm_capability_options['articlelimit'] ) ) ? $this->wcfm_capability_options['articlelimit'] : '';
  	if( ( $articlelimit == -1 ) || ( $articlelimit == '-1' ) ) {
  		$label .= ' 0 ' . __( 'remaining', 'wc-frontend-manager' );
  	} else {
			if( $articlelimit ) $articlelimit = absint($articlelimit);
			if( $articlelimit && ( $articlelimit >= 0 ) ) {
				if( $articlelimit == 1989 ) {
					$label .= ' 0 ' . __( 'remaining', 'wc-frontend-manager' );
				} else {
					$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
					$count_articles  = wcfm_get_user_posts_count( $current_user_id, 'post', apply_filters( 'wcfm_limit_check_status', 'any' ), array( 'suppress_filters' => 1 ) );
					$label .= ' ' . ( $articlelimit - $count_articles ) . ' ' . __( 'remaining', 'wc-frontend-manager' );
				}
			} else {
				$label .= __( 'Unlimited', 'wc-frontend-manager' );
			}
		}
  	
  	$label = '<span class="wcfm_articles_limit_label">' . $label . '</span>';
  	
  	return $label;
  }
  
  // WCFM wcfmcap Manage Coupon
  function wcfmcap_is_allow_manage_coupons( $allow ) {
  	$manage_coupons = ( isset( $this->wcfm_capability_options['submit_coupons'] ) ) ? $this->wcfm_capability_options['submit_coupons'] : 'no';
  	$manage_coupons = ( isset( $this->wcfm_capability_options['manage_coupons'] ) ) ? $this->wcfm_capability_options['manage_coupons'] : $manage_coupons;
  	if( $manage_coupons == 'yes' ) return false;
  	//if( !current_user_can( 'edit_shop_coupons' ) ) return false;
  	return $allow;
  }
  
  // WCFM Coupon Menu
  function wcfmcap_coupon_menu( $has_new ) {
  	$manage_coupons = ( isset( $this->wcfm_capability_options['submit_coupons'] ) ) ? $this->wcfm_capability_options['submit_coupons'] : 'no';
  	$manage_coupons = ( isset( $this->wcfm_capability_options['manage_coupons'] ) ) ? $this->wcfm_capability_options['manage_coupons'] : $manage_coupons;
  	if( $manage_coupons == 'yes' ) $has_new = false;
  	//if( !current_user_can( 'edit_shop_coupons' ) ) $has_new = false;
  	return $has_new;
  }
  
  // WCV Add New Coupon Sub menu
  function wcfmcap_is_allow_add_coupons( $allow ) {
  	$manage_coupons = ( isset( $this->wcfm_capability_options['submit_coupons'] ) ) ? $this->wcfm_capability_options['submit_coupons'] : 'no';
  	$manage_coupons = ( isset( $this->wcfm_capability_options['manage_coupons'] ) ) ? $this->wcfm_capability_options['manage_coupons'] : $manage_coupons;
  	if( $manage_coupons == 'yes' ) return false;
  	$add_coupons = ( isset( $this->wcfm_capability_options['add_coupons'] ) ) ? $this->wcfm_capability_options['add_coupons'] : 'no';
  	if( $add_coupons == 'yes' ) return false;
  	//if( !current_user_can( 'edit_shop_coupons' ) ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Publish Coupon
  function wcfmcap_is_allow_publish_coupons( $allow ) {
  	$manage_coupons = ( isset( $this->wcfm_capability_options['submit_coupons'] ) ) ? $this->wcfm_capability_options['submit_coupons'] : 'no';
  	$manage_coupons = ( isset( $this->wcfm_capability_options['manage_coupons'] ) ) ? $this->wcfm_capability_options['manage_coupons'] : $manage_coupons;
  	if( $manage_coupons == 'yes' ) return false;
  	$publish_coupons = ( isset( $this->wcfm_capability_options['publish_coupons'] ) ) ? $this->wcfm_capability_options['publish_coupons'] : 'no';
  	if( $publish_coupons == 'yes' ) return false;
  	//if( !current_user_can( 'publish_shop_coupons' ) ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Edit Coupon
  function wcfmcap_is_allow_edit_coupons( $allow ) {
  	$manage_coupons = ( isset( $this->wcfm_capability_options['submit_coupons'] ) ) ? $this->wcfm_capability_options['submit_coupons'] : 'no';
  	$manage_coupons = ( isset( $this->wcfm_capability_options['manage_coupons'] ) ) ? $this->wcfm_capability_options['manage_coupons'] : $manage_coupons;
  	if( $manage_coupons == 'yes' ) return false;
  	$edit_coupons = ( isset( $this->wcfm_capability_options['edit_coupons'] ) ) ? $this->wcfm_capability_options['edit_coupons'] : 'no';
  	if( $edit_coupons == 'yes' ) return false;
  	//if( !current_user_can( 'edit_published_shop_coupons' ) ) return false;
  	return $allow;
  }
  
  // WCFM auto publish live coupons
  function wcfmcap_is_allow_publish_live_coupons( $allow ) {
  	$publish_live_coupons = ( isset( $this->wcfm_capability_options['publish_live_coupons'] ) ) ? $this->wcfm_capability_options['publish_live_coupons'] : 'no';
  	if( $publish_live_coupons == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Delete Coupon
  function wcfmcap_is_allow_delete_coupons( $allow ) {
  	$manage_coupons = ( isset( $this->wcfm_capability_options['submit_coupons'] ) ) ? $this->wcfm_capability_options['submit_coupons'] : 'no';
  	$manage_coupons = ( isset( $this->wcfm_capability_options['manage_coupons'] ) ) ? $this->wcfm_capability_options['manage_coupons'] : $manage_coupons;
  	if( $manage_coupons == 'yes' ) return false;
  	$delete_coupons = ( isset( $this->wcfm_capability_options['delete_coupons'] ) ) ? $this->wcfm_capability_options['delete_coupons'] : 'no';
  	if( $delete_coupons == 'yes' ) return false;
  	//if( !current_user_can( 'delete_published_shop_coupons' ) ) return false;
  	return $allow;
  }
  
  // WCFM Allow Free Shipping Coupons
  function wcfmcap_is_allow_free_shipping_coupons( $allow ) {
  	$free_shipping_coupons = ( isset( $this->wcfm_capability_options['free_shipping_coupons'] ) ) ? $this->wcfm_capability_options['free_shipping_coupons'] : 'no';
  	if( $free_shipping_coupons == 'yes' ) return false;
  	return $allow;
  }
  
  // Linstings
  function wcfmcap_is_allow_listings( $allow ) {
  	$associate_listings = ( isset( $this->wcfm_capability_options['associate_listings'] ) ) ? $this->wcfm_capability_options['associate_listings'] : 'no';
  	if( $associate_listings == 'yes' ) return false;
  	return $allow;
  }
  
  // Product Columns
  function wcfmcap_is_allow_product_columns( $product_columns ) {
  	
  	$inventory = ( isset( $this->wcfm_capability_options['inventory'] ) ) ? $this->wcfm_capability_options['inventory'] : 'no';
  	$shipping = ( isset( $this->wcfm_capability_options['shipping'] ) ) ? $this->wcfm_capability_options['shipping'] : 'no';
  	$taxes = ( isset( $this->wcfm_capability_options['taxes'] ) ) ? $this->wcfm_capability_options['taxes'] : 'no';
  	//$attributes = ( isset( $this->wcfm_capability_options['attributes'] ) ) ? $this->wcfm_capability_options['attributes'] : 'no';
  	$advanced = ( isset( $this->wcfm_capability_options['advanced'] ) ) ? $this->wcfm_capability_options['advanced'] : 'no';
  	$linked = ( isset( $this->wcfm_capability_options['linked'] ) ) ? $this->wcfm_capability_options['linked'] : 'no';
  	$downloadable = ( isset( $this->wcfm_capability_options['downloadable'] ) ) ? $this->wcfm_capability_options['downloadable'] : 'no';
  	$grouped = ( isset( $this->wcfm_capability_options['grouped'] ) ) ? $this->wcfm_capability_options['grouped'] : 'no';
		$external = ( isset( $this->wcfm_capability_options['external'] ) ) ? $this->wcfm_capability_options['external'] : 'no';
		$gallery = ( isset( $this->wcfm_capability_options['gallery'] ) ) ? $this->wcfm_capability_options['gallery'] : 'no';
		$category = ( isset( $this->wcfm_capability_options['category'] ) ) ? $this->wcfm_capability_options['category'] : 'no';
		$tags = ( isset( $this->wcfm_capability_options['tags'] ) ) ? $this->wcfm_capability_options['tags'] : 'no';
		
  	
  	if( $inventory == 'yes' ) unset( $product_columns[ 'stock_status' ] );
		if( $inventory == 'yes' ) unset( $product_columns[ 'stock' ] );
		if( $inventory == 'yes' ) unset( $product_columns[ 'backorders' ] );
		if( $inventory == 'yes' ) unset( $product_columns[ 'sold_individually' ] );
		
		if( $shipping == 'yes' ) unset( $product_columns[ 'weight' ] );
		if( $shipping == 'yes' ) unset( $product_columns[ 'length' ] );
		if( $shipping == 'yes' ) unset( $product_columns[ 'width' ] );
		if( $shipping == 'yes' ) unset( $product_columns[ 'height' ] );
		if( $shipping == 'yes' ) unset( $product_columns[ 'shipping_class_id' ] );
		
		if( $taxes == 'yes' ) unset( $product_columns[ 'tax_status' ] );
		if( $taxes == 'yes' ) unset( $product_columns[ 'tax_class' ] );
		
		//if( $attributes == 'yes' ) unset( $product_columns[ 'subscription' ] );
		
  	if( $advanced == 'yes' ) unset( $product_columns[ 'reviews_allowed' ] );
  	if( $advanced == 'yes' ) unset( $product_columns[ 'purchase_note' ] );
  	
  	if( $linked == 'yes' ) unset( $product_columns[ 'upsell_ids' ] );
  	if( $linked == 'yes' ) unset( $product_columns[ 'cross_sell_ids' ] );
  	
  	if( $downloadable == 'yes' ) unset( $product_columns[ 'download_limit' ] );
  	if( $downloadable == 'yes' ) unset( $product_columns[ 'download_expiry' ] );
  	
  	if( $grouped == 'yes' ) unset( $product_columns[ 'grouped_products' ] );
  	if( $external == 'yes' ) unset( $product_columns[ 'product_url' ] );
  	if( $external == 'yes' ) unset( $product_columns[ 'button_text' ] );
  	
  	if( $gallery == 'yes' ) unset( $product_columns[ 'images' ] );
  	
  	if( $category == 'yes' ) unset( $product_columns[ 'category_ids' ] );
  	if( $tags == 'yes' ) unset( $product_columns[ 'tag_ids' ] );
		
		return $product_columns;
  }
  
  // Allow View Orders
  function wcfmcap_is_allow_orders( $allow ) {
  	$view_orders = ( isset( $this->wcfm_capability_options['view_orders'] ) ) ? $this->wcfm_capability_options['view_orders'] : 'no';
  	if( $view_orders == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Order Status Update
  function wcfmcap_is_allow_order_status_update( $allow ) {
  	$order_status_update = ( isset( $this->wcfm_capability_options['order_status_update'] ) ) ? $this->wcfm_capability_options['order_status_update'] : 'no';
  	if( $order_status_update == 'yes' ) return false;
  	return $allow;
  	
  }
  
  // Allow View Order Details
  function wcfmcap_is_allow_order_details( $allow ) {
  	$view_order_details = ( isset( $this->wcfm_capability_options['view_order_details'] ) ) ? $this->wcfm_capability_options['view_order_details'] : 'no';
  	if( $view_order_details == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Manage Order
  function wcfmcap_is_allow_manage_order( $allow ) {
  	$manage_order = ( isset( $this->wcfm_capability_options['manage_order'] ) ) ? $this->wcfm_capability_options['manage_order'] : 'no';
  	if( $manage_order == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow View Order Delete
  function wcfmcap_is_allow_order_delete( $allow ) {
  	$delete_order = ( isset( $this->wcfm_capability_options['delete_order'] ) ) ? $this->wcfm_capability_options['delete_order'] : 'no';
  	if( $delete_order == 'yes' ) return false;
  	return $allow;
  }
  
  // Custome Billing Address
  function wcfmcap_is_allow_customer_billing_details( $allow ) {
  	$view_billing_details = ( isset( $this->wcfm_capability_options['view_billing_details'] ) ) ? $this->wcfm_capability_options['view_billing_details'] : 'no';
  	if( $view_billing_details == 'yes' ) return false;
  	return $allow;
  }
  
  // Custome Shipping Address
  function wcfmcap_is_allow_customer_shipping_details( $allow ) {
  	$view_shipping_details = ( isset( $this->wcfm_capability_options['view_shipping_details'] ) ) ? $this->wcfm_capability_options['view_shipping_details'] : 'no';
  	if( $view_shipping_details == 'yes' ) return false;
  	return $allow;
  }
  
  // Order EXport CSV
  function wcfmcap_is_allow_export_csv( $allow ) {
  	$export_csv = ( isset( $this->wcfm_capability_options['export_csv'] ) ) ? $this->wcfm_capability_options['export_csv'] : 'no';
  	if( $export_csv == 'yes' ) return false;
  	return $allow;
  }
  
  // View Commission
  function wcfmcap_is_allow_view_commission( $allow ) {
  	$view_commission = ( isset( $this->wcfm_capability_options['view_commission'] ) ) ? $this->wcfm_capability_options['view_commission'] : 'no';
  	if( $view_commission == 'yes' ) return false;
  	return $allow;
  }
  
  // Vendor Store Invoice
  function wcfmcap_is_allow_store_invoice( $allow ) {
  	$store_invoice = ( isset( $this->wcfm_capability_options['store_invoice'] ) ) ? $this->wcfm_capability_options['store_invoice'] : 'no';
  	if( $store_invoice == 'yes' ) return false;
  	return $allow;
  }
  
  // Order PDF Invoice
  function wcfmcap_is_allow_pdf_invoice( $allow ) {
  	$pdf_invoice = ( isset( $this->wcfm_capability_options['pdf_invoice'] ) ) ? $this->wcfm_capability_options['pdf_invoice'] : 'no';
  	if( $pdf_invoice == 'yes' ) return false;
  	return $allow;
  }
  
  // Order PDF Packing Slip
  function wcfmcap_is_allow_pdf_packing_slip( $allow ) {
  	$pdf_packing_slip = ( isset( $this->wcfm_capability_options['pdf_packing_slip'] ) ) ? $this->wcfm_capability_options['pdf_packing_slip'] : 'no';
  	if( $pdf_packing_slip == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Manage Customers
  function wcfmcap_is_allow_manage_customers( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Customers
  function wcfmcap_is_allow_add_customers( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$add_customers = ( isset( $this->wcfm_capability_options['add_customers'] ) ) ? $this->wcfm_capability_options['add_customers'] : 'no';
  	if( $add_customers == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap View Customers
  function wcfmcap_is_allow_view_customers( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$view_customers = ( isset( $this->wcfm_capability_options['view_customers'] ) ) ? $this->wcfm_capability_options['view_customers'] : 'no';
  	if( $view_customers == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Edit Customers
  function wcfmcap_is_allow_edit_customers( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$edit_customers = ( isset( $this->wcfm_capability_options['edit_customers'] ) ) ? $this->wcfm_capability_options['edit_customers'] : 'no';
  	if( $edit_customers == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Delete Customers
  function wcfmcap_is_allow_delete_customer( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$view_customers = ( isset( $this->wcfm_capability_options['view_customers'] ) ) ? $this->wcfm_capability_options['view_customers'] : 'no';
  	if( $view_customers == 'yes' ) return false;
  	$edit_customers = ( isset( $this->wcfm_capability_options['edit_customers'] ) ) ? $this->wcfm_capability_options['edit_customers'] : 'no';
  	if( $edit_customers == 'yes' ) return false;
  	$delete_customers = ( isset( $this->wcfm_capability_options['delete_customers'] ) ) ? $this->wcfm_capability_options['delete_customers'] : 'no';
  	if( $delete_customers == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap View Customers Orders
  function wcfmcap_is_allow_customer_details_orders( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$view_customers = ( isset( $this->wcfm_capability_options['view_customers'] ) ) ? $this->wcfm_capability_options['view_customers'] : 'no';
  	if( $view_customers == 'yes' ) return false;
  	$view_customers_orders = ( isset( $this->wcfm_capability_options['view_customers_orders'] ) ) ? $this->wcfm_capability_options['view_customers_orders'] : 'no';
  	if( $view_customers_orders == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap View Customers Email
  function wcfmcap_is_allow_view_customer_email( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$view_customers = ( isset( $this->wcfm_capability_options['view_customers'] ) ) ? $this->wcfm_capability_options['view_customers'] : 'no';
  	if( $view_customers == 'yes' ) return false;
  	$view_customers_email = ( isset( $this->wcfm_capability_options['view_email'] ) ) ? $this->wcfm_capability_options['view_email'] : 'no';
  	if( $view_customers_email == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap View Customers Name
  function wcfmcap_is_allow_view_customer_name( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$view_customers = ( isset( $this->wcfm_capability_options['view_customers'] ) ) ? $this->wcfm_capability_options['view_customers'] : 'no';
  	if( $view_customers == 'yes' ) return false;
  	$view_customers_name = ( isset( $this->wcfm_capability_options['view_name'] ) ) ? $this->wcfm_capability_options['view_name'] : 'no';
  	if( $view_customers_name == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Customer Limit
  function wcfmcap_is_allow_customer_limit( $allow ) {
  	$manage_customers = ( isset( $this->wcfm_capability_options['manage_customers'] ) ) ? $this->wcfm_capability_options['manage_customers'] : 'no';
  	if( $manage_customers == 'yes' ) return false;
  	$add_customers = ( isset( $this->wcfm_capability_options['add_customers'] ) ) ? $this->wcfm_capability_options['add_customers'] : 'no';
  	if( $add_customers == 'yes' ) return false;
  	
  	// Limit Restriction
  	$customerlimit = ( isset( $this->wcfm_capability_options['customerlimit'] ) ) ? $this->wcfm_capability_options['customerlimit'] : '';
  	
  	if( ( $customerlimit == -1 ) || ( $customerlimit == '-1' ) ) {
  		return false;
  	} else {
			if( $customerlimit ) $customerlimit = absint($customerlimit);
			if( $customerlimit && ( $customerlimit >= 0 ) ) {
				$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
				$customer_user_role = apply_filters( 'wcfm_customer_user_role', 'customer' );
				$args = array(
											'role__in'     => array( $customer_user_role ),
											'orderby'      => 'ID',
											'order'        => 'ASC',
											'offset'       => 0,
											'number'       => -1,
											'count_total'  => false,
											'meta_key'     => '_wcfm_vendor',
											'meta_value'   => $current_user_id
										 );
				$wcfm_customers_array = get_users( $args );
				$count_customers  = count($wcfm_customers_array);
				if( $customerlimit <= $count_customers ) return false;
			}
		}
  	return $allow;
  }
  
  // WCFM Customer Limit Label
  function wcfmcap_customers_limit_label( $label ) {
  	
  	if( current_user_can( 'administrator' ) || !apply_filters( 'wcfm_is_allow_limit_label', true ) || !apply_filters( 'wcfm_is_allow_customer_limit_label', true ) ) return'';
  	
  	$label = __( 'Customers Limit: ', 'wc-frontend-manager' );
  	
  	$customerlimit = ( isset( $this->wcfm_capability_options['customerlimit'] ) ) ? $this->wcfm_capability_options['customerlimit'] : '';
  	if( ( $customerlimit == -1 ) || ( $customerlimit == '-1' ) ) {
  		$label .= ' 0 ' . __( 'remaining', 'wc-frontend-manager' );
  	} else {
			if( $customerlimit ) $customerlimit = absint($customerlimit);
			if( $customerlimit && ( $customerlimit >= 0 ) ) {
				if( $customerlimit == 1989 ) {
					$label .= ' 0 ' . __( 'remaining', 'wc-frontend-manager' );
				} else {
					$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
					$customer_user_role = apply_filters( 'wcfm_customer_user_role', 'customer' );
					$args = array(
												'role__in'     => array( $customer_user_role ),
												'orderby'      => 'ID',
												'order'        => 'ASC',
												'offset'       => 0,
												'number'       => -1,
												'count_total'  => false,
												'meta_key'     => '_wcfm_vendor',
												'meta_value'   => $current_user_id
											 );
					$wcfm_customers_array = get_users( $args );
					$count_customers  = count($wcfm_customers_array);
					$label .= ' ' . ( $customerlimit - $count_customers ) . ' ' . __( 'remaining', 'wc-frontend-manager' );
				}
			} else {
				$label .= __( 'Unlimited', 'wc-frontend-manager' );
			}
		}
  	
  	$label = '<span class="wcfm_customers_limit_label">' . $label . '</span>';
  	
  	return $label;
  }
  
  // Show Vendor Email
  function wcfmcap_is_allow_show_email( $allow ) {
  	$vendor_email = ( isset( $this->wcfm_capability_options['vendor_email'] ) ) ? $this->wcfm_capability_options['vendor_email'] : 'no';
  	if( $vendor_email == 'yes' ) return false;
  	return $allow;
  }
  
  // Show Vendor Phone
  function wcfmcap_is_allow_show_phone( $allow ) {
  	$vendor_phone = ( isset( $this->wcfm_capability_options['vendor_phone'] ) ) ? $this->wcfm_capability_options['vendor_phone'] : 'no';
  	if( $vendor_phone == 'yes' ) return false;
  	return $allow;
  }
  
  // Show Vendor Address
  function wcfmcap_is_allow_show_address( $allow ) {
  	$vendor_address = ( isset( $this->wcfm_capability_options['vendor_address'] ) ) ? $this->wcfm_capability_options['vendor_address'] : 'no';
  	if( $vendor_address == 'yes' ) return false;
  	return $allow;
  }
  
  // Show Vendor Map
  function wcfmcap_is_allow_show_map( $allow ) {
  	$vendor_map = ( isset( $this->wcfm_capability_options['vendor_map'] ) ) ? $this->wcfm_capability_options['vendor_map'] : 'no';
  	if( $vendor_map == 'yes' ) return false;
  	return $allow;
  }
  
  // Show Vendor Social
  function wcfmcap_is_allow_show_social( $allow ) {
  	$vendor_social = ( isset( $this->wcfm_capability_options['vendor_social'] ) ) ? $this->wcfm_capability_options['vendor_social'] : 'no';
  	if( $vendor_social == 'yes' ) return false;
  	return $allow;
  }
  
  // Show Vendor Follower
  function wcfmcap_is_allow_show_follower( $allow ) {
  	$vendor_follower = ( isset( $this->wcfm_capability_options['vendor_follower'] ) ) ? $this->wcfm_capability_options['vendor_follower'] : 'no';
  	if( $vendor_follower == 'yes' ) return false;
  	return $allow;
  }
  
  // Show Vendor Policy
  function wcfmcap_is_allow_show_policy( $allow ) {
  	$vendor_policy = ( isset( $this->wcfm_capability_options['vendor_policy'] ) ) ? $this->wcfm_capability_options['vendor_policy'] : 'no';
  	if( $vendor_policy == 'yes' ) return false;
  	return $allow;
  }
  
  // Show Vendor Customer Support
  function wcfmcap_is_allow_customer_support( $allow ) {
  	$customer_support = ( isset( $this->wcfm_capability_options['customer_support'] ) ) ? $this->wcfm_capability_options['customer_support'] : 'no';
  	if( $customer_support == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Refund Request
  function wcfmcap_is_allow_refund_requests( $allow ) {
  	$refund_requests = ( isset( $this->wcfm_capability_options['refund_requests'] ) ) ? $this->wcfm_capability_options['refund_requests'] : 'no';
  	if( $refund_requests == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Reviews
  function wcfmcap_is_allow_reviews( $allow ) {
  	$review_manage = ( isset( $this->wcfm_capability_options['review_manage'] ) ) ? $this->wcfm_capability_options['review_manage'] : 'no';
  	if( $review_manage == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Review Request
  function wcfmcap_is_allow_manage_review( $allow ) {
  	$review_manage = ( isset( $this->wcfm_capability_options['review_manage'] ) ) ? $this->wcfm_capability_options['review_manage'] : 'no';
  	if( $review_manage == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Ledger Book
  function wcfmcap_is_allow_ledger_book( $allow ) {
  	$ledger_book = ( isset( $this->wcfm_capability_options['ledger_book'] ) ) ? $this->wcfm_capability_options['ledger_book'] : 'no';
  	if( $ledger_book == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Store Hours
  function wcfmcap_is_allow_store_hours( $allow ) {
  	$store_hours = ( isset( $this->wcfm_capability_options['store_hours'] ) ) ? $this->wcfm_capability_options['store_hours'] : 'no';
  	if( $store_hours == 'yes' ) return false;
  	return $allow;
  }
  
  // Product Multivendor
  function wcfmcap_is_allow_product_multivendor( $allow ) {
  	$product_multivendor = ( isset( $this->wcfm_capability_options['product_multivendor'] ) ) ? $this->wcfm_capability_options['product_multivendor'] : 'no';
  	if( $product_multivendor == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Video Banner
  function wcfmcap_is_allow_video_banner( $allow ) {
  	$video_banner = ( isset( $this->wcfm_capability_options['video_banner'] ) ) ? $this->wcfm_capability_options['video_banner'] : 'no';
  	if( $video_banner == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Slider Banner
  function wcfmcap_is_allow_slider_banner( $allow ) {
  	$slider_banner = ( isset( $this->wcfm_capability_options['slider_banner'] ) ) ? $this->wcfm_capability_options['slider_banner'] : 'no';
  	if( $slider_banner == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Store Logo
  function wcfmcap_is_allow_store_logo( $allow ) {
  	$store_logo = ( isset( $this->wcfm_capability_options['store_logo'] ) ) ? $this->wcfm_capability_options['store_logo'] : 'no';
  	if( $store_logo == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Store Banner
  function wcfmcap_is_allow_store_banner( $allow ) {
  	$store_banner = ( isset( $this->wcfm_capability_options['store_banner'] ) ) ? $this->wcfm_capability_options['store_banner'] : 'no';
  	if( $store_banner == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Store Name
  function wcfmcap_is_allow_store_name( $allow ) {
  	$store_name = ( isset( $this->wcfm_capability_options['store_name'] ) ) ? $this->wcfm_capability_options['store_name'] : 'no';
  	if( $store_name == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Store Description
  function wcfmcap_is_allow_store_description( $allow ) {
  	$store_description = ( isset( $this->wcfm_capability_options['store_description'] ) ) ? $this->wcfm_capability_options['store_description'] : 'no';
  	if( $store_description == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Store Phone
  function wcfmcap_is_allow_store_phone( $allow ) {
  	$store_phone = ( isset( $this->wcfm_capability_options['store_phone'] ) ) ? $this->wcfm_capability_options['store_phone'] : 'no';
  	if( $store_phone == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Store Address
  function wcfmcap_is_allow_store_address( $allow ) {
  	$store_address = ( isset( $this->wcfm_capability_options['store_address'] ) ) ? $this->wcfm_capability_options['store_address'] : 'no';
  	if( $store_address == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow Withdrwal Request
  function wcfmcap_is_allow_withdrawal( $allow ) {
  	$vendor_withdrwal = ( isset( $this->wcfm_capability_options['vendor_withdrwal'] ) ) ? $this->wcfm_capability_options['vendor_withdrwal'] : 'no';
  	if( $vendor_withdrwal == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow View Transactions
  function wcfmcap_is_allow_payments( $allow ) {
  	$vendor_transactions = ( isset( $this->wcfm_capability_options['vendor_transactions'] ) ) ? $this->wcfm_capability_options['vendor_transactions'] : 'no';
  	if( $vendor_transactions == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow View Transaction Details
  function wcfmcap_is_allow_transaction_details( $allow ) {
  	$vendor_transaction_details = ( isset( $this->wcfm_capability_options['vendor_transaction_details'] ) ) ? $this->wcfm_capability_options['vendor_transaction_details'] : 'no';
  	if( $vendor_transaction_details == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Manage Vendors
  function wcfmcap_is_allow_vendors( $allow ) {
  	$manage_vendors = ( isset( $this->wcfm_capability_options['manage_vendors'] ) ) ? $this->wcfm_capability_options['manage_vendors'] : 'no';
  	if( $manage_vendors == 'yes' ) return false;
  	return $allow;
  }
  
  // Allow View Reports
  function wcfmcap_is_allow_reports( $allow ) {
  	$view_reports = ( isset( $this->wcfm_capability_options['view_reports'] ) ) ? $this->wcfm_capability_options['view_reports'] : 'no';
  	if( $view_reports == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Manage Capability
  function wcfmcap_is_allow_capability_controller( $allow ) {
  	$capability_controller = ( isset( $this->wcfm_capability_options['capability_controller'] ) ) ? $this->wcfm_capability_options['capability_controller'] : 'no';
  	if( $capability_controller == 'yes' ) return false;
  	return $allow;
  }
  
  // Commission Manage
  function wcfmcap_is_allow_commission_manage( $allow ) {
  	$manage_commission = ( isset( $this->wcfm_capability_options['manage_commission'] ) ) ? $this->wcfm_capability_options['manage_commission'] : 'no';
  	if( $manage_commission == 'yes' ) return false;
  	return $allow;
  }
  
  // WP Admin View
  function wcfmcap_is_allow_wp_admin_view( $allow ) {
  	$wp_admin_view = ( isset( $this->wcfm_capability_options['wp_admin_view'] ) ) ? $this->wcfm_capability_options['wp_admin_view'] : 'no';
  	if( $wp_admin_view == 'yes' ) return false;
  	$sm_wpadmin = ( isset( $this->wcfm_capability_options['sm_wpadmin'] ) ) ? $this->wcfm_capability_options['sm_wpadmin'] : 'no';
  	if( $sm_wpadmin == 'yes' ) return false;
  	$ss_wpadmin = ( isset( $this->wcfm_capability_options['ss_wpadmin'] ) ) ? $this->wcfm_capability_options['ss_wpadmin'] : 'no';
  	if( $ss_wpadmin == 'yes' ) return false;
  	$vnd_wpadmin = ( isset( $this->wcfm_capability_options['vnd_wpadmin'] ) ) ? $this->wcfm_capability_options['vnd_wpadmin'] : 'no';
  	if( $vnd_wpadmin == 'yes' ) return false;
  	return $allow;
  }
  
  // WooCommerce Product Schedular
  function wcfmcap_is_allow_wc_product_scheduler( $allow ) {
  	$wc_product_scheduler = ( isset( $this->wcfm_capability_options['wc_product_scheduler'] ) ) ? $this->wcfm_capability_options['wc_product_scheduler'] : 'no';
  	if( $wc_product_scheduler == 'yes' ) return false;
  	return $allow;
  }
  
}