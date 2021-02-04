<?php

/**
 * WCFMvm plugin Install
 *
 * Plugin install script which adds default pages, taxonomies, and database tables to WordPress. Runs on activation and upgrade.
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/helpers
 * @version   1.0.0
 */
 
class WCFMvm_Install {

	public $arr = array();

	public function __construct() {
		global $WCFM, $WCFMvm, $WCFM_Query;
		if ( get_option("wcfmvm_page_install") == 1 ) {
			$wcfm_page_options = get_option( 'wcfm_page_options', array() );
			if( isset($wcfm_page_options['wcfm_vendor_membership_page_id']) ) {
				wp_update_post(array('ID' => $wcfm_page_options['wcfm_vendor_membership_page_id'], 'post_content' => '[wcfm_vendor_membership]'));
			}
			if( isset($wcfm_page_options['wcfm_vendor_registration_page_id']) ) {
				wp_update_post(array('ID' => $wcfm_page_options['wcfm_vendor_registration_page_id'], 'post_content' => '[wcfm_vendor_registration]'));
			}
			//update_option('wcfm_page_options', $wcfm_page_options);
		}
		
		if ( !get_option("wcfmvm_page_install") ) {
			$this->wcfmvm_create_pages();
			update_option("wcfmvm_page_install", 1);
		}
		
		
		if ( !get_option( 'wcfmvm_table_install' ) ) {
			$this->wcfmvm_create_tables();
			update_option("wcfmvm_table_install", 1);
		}
		
		// Intialize WCFM End points
		if(WCFMvm_Dependencies::wcfm_plugin_active_check()) {
			$WCFM_Query->init_query_vars();
			$WCFM_Query->add_endpoints();
			
			// Flush rules after install
			flush_rewrite_rules();
		}
		
	}
	
	/**
	 * Create a page
	 *
	 * @access public
	 * @param mixed $slug Slug for the new page
	 * @param mixed $option Option name to store the page's ID
	 * @param string $page_title (default: '') Title for the new page
	 * @param string $page_content (default: '') Content for the new page
	 * @param int $post_parent (default: 0) Parent for the new page
	 * @return void
	 */
	function wcfmvm_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0) {
		global $wpdb;
		$option_value = get_option($option);
		if ($option_value > 0 && get_post($option_value))
				return;
		$page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '$slug' LIMIT 1;");
		if ($page_found) :
				if (!$option_value)
						update_option($option, $page_found);
				return;
		endif;
		$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => 1,
				'post_name' => $slug,
				'post_title' => $page_title,
				'post_content' => $page_content,
				'post_parent' => $post_parent,
				'comment_status' => 'closed'
		);
		$page_id = wp_insert_post($page_data);
		update_option($option, $page_id);
	}

	/**
	 * Create pages that the plugin relies on, storing page id's in variables.
	 *
	 * @access public
	 * @return void
	 */
	function wcfmvm_create_pages() {
		global $WCFM;

		// WCFM page
		$this->wcfmvm_create_page(esc_sql(_x('vendor-membership', 'page_slug', 'vendor-membership')), 'wcfm_vendor_membership_page_id', __('Vendor Membership', 'wc-multivendor-membership'), '[wcfm_vendor_membership]');
		$this->wcfmvm_create_page(esc_sql(_x('vendor-register', 'page_slug', 'vendor-register')), 'wcfm_vendor_registration_page_id', __('Vendor Registration', 'wc-multivendor-membership'), '[wcfm_vendor_registration]');
		
		$array_pages = get_option( 'wcfm_page_options', array() );
		$array_pages['wcfm_vendor_membership_page_id'] = get_option('wcfm_vendor_membership_page_id');
		$array_pages['wcfm_vendor_registration_page_id'] = get_option('wcfm_vendor_registration_page_id');

		update_option('wcfm_page_options', $array_pages);
	}
	
	/**
	 * Create WCFMvm Membership Subscription tables
	 * @global object $wpdb
	 * From Version 1.0.0
	 */
	function wcfmvm_create_tables() {
		global $wpdb;
		$collate = '';
		if ($wpdb->has_cap('collation')) {
				$collate = $wpdb->get_charset_collate();
		}
		$create_tables_query = array();
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_membership_subscription` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL default 0,
															`membership_id` bigint(20) NOT NULL default 0,
															`subscription_type` VARCHAR(50) NOT NULL,
															`subscription_amt` int(10) NOT NULL default 0,
															`subscription_interval` VARCHAR(50) NOT NULL,
															`event` VARCHAR(50) NOT NULL,
															`pay_mode` VARCHAR(50) NOT NULL,
															`transaction_id` VARCHAR(100) NOT NULL,
															`transaction_type` VARCHAR(100) NOT NULL,
															`transaction_status` VARCHAR(100) NOT NULL,
															`transaction_details` text NOT NULL,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,			
															PRIMARY KEY (`ID`),
															CONSTRAINT membership_subscription UNIQUE ( vendor_id, membership_id, transaction_id, transaction_type )
															) $collate;";
															
		foreach ($create_tables_query as $create_table_query) {
			$wpdb->query($create_table_query);
		}
	}
}

?>