<?php
/**
 * WCFMvm plugin core
 *
 * Plugin non Ajax Controler
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/core
 * @version   1.0.0
 */
 
class WCFMvm_Non_Ajax {

	public function __construct() {
		global $WCFM, $WCFMvm;
		
		// Plugins page help links
		add_filter( 'plugin_action_links_' . $WCFMvm->plugin_base_name, array( &$this, 'wcfmvm_plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'wcfmvm_plugin_row_meta' ), 10, 2 );
	}
	
	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public function wcfmvm_plugin_action_links( $links ) {
		global $WCFMvm;
		$action_links = array(
			'wcfmvm_settings' => '<a href="' . esc_url( get_wcfm_memberships_settings_url() ) . '" aria-label="' . esc_attr__( 'View WCFM Membership Settings', 'wc-multivendor-membership' ) . '">' . esc_html__( 'Settings', 'wc-frontend-manager' ) . '</a>',
		);
		
		$action_links = array_merge( $action_links, $links );
		
		$groups_staffs_meta = array();
		if(!WCFM_Dependencies::wcfmgs_plugin_active_check()) {
			$groups_staffs_meta = array( 'groups-staffs' => '<a target="_blank" href="' . esc_url( apply_filters( 'wcfm_groups_stsffs_url', 'http://wclovers.com/product/woocommerce-frontend-manager-groups-staffs/' ) ) . '" aria-label="' . esc_attr__( 'Add custom capability with each Membership Level.', 'wc-multivendor-membership' ) . '">' . esc_html__( 'WCFM - Groups & Staffs', 'wc-multivendor-membership' ) . '</a>' );
			$action_links = array_merge( $action_links, $groups_staffs_meta );
		}

		return $action_links;
	}
	
	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public function wcfmvm_plugin_row_meta( $links, $file ) {
		global $WCFM, $WCFMvm;
		if ( $WCFMvm->plugin_base_name == $file ) {
			$row_meta = array(
				'docs'            => '<a target="_blank" href="' . esc_url( apply_filters( 'wcfmvm_docs_url', 'https://wclovers.com/knowledgebase/wcfm-membership/' ) ) . '" aria-label="' . esc_attr__( 'View WCFM Membership documentation', 'wc-multivendor-membership' ) . '">' . esc_html__( 'Documentation', 'wc-frontend-manager' ) . '</a>',
				'videotutorialm'  => '<a target="_blank" href="' . esc_url( apply_filters( 'wcfm_vtutorial_url', 'https://wclovers.com/wcfm-tutorials/' ) ) . '" aria-label="' . esc_attr__( 'View WCFM Video Tutorial', 'wc-frontend-manager' ) . '">' . esc_html__( 'Video Tutorial', 'wc-frontend-manager' ) . '</a>',
				'support'         => '<a target="_blank" href="' . esc_url( apply_filters( 'wcfmvm_support_url', 'https://wclovers.com/forums/forum/wcfm-membership/' ) ) . '" aria-label="' . esc_attr__( 'Visit premium customer support', 'woocommerce' ) . '">' . esc_html__( 'Support', 'woocommerce' ) . '</a>',
				//'contactus'     => '<a href="' . esc_url( apply_filters( 'wcfm_contactus_url', 'http://wclovers.com/contact-us/' ) ) . '" aria-label="' . esc_attr__( 'Any WC help feel free to contact us', 'wc-frontend-manager' ) . '">' . esc_html__( 'Contact US', 'wc-frontend-manager' ) . '</a>'
				'customizationm'  => '<a target="_blank" href="' . esc_url( apply_filters( 'wcfm_customization_url', 'https://wclovers.com/woocommerce-multivendor-customization/' ) ) . '" aria-label="' . esc_attr__( 'Any WC help feel free to contact us', 'wc-frontend-manager' ) . '">' . esc_html__( 'Customization Help', 'wc-frontend-manager' ) . '</a>'
			);
			
			$groups_staffs_meta = array();
			if(!WCFM_Dependencies::wcfmgs_plugin_active_check()) {
				$groups_staffs_meta = array( 'groups-staffs' => '<a target="_blank" href="' . esc_url( apply_filters( 'wcfm_groups_stsffs_url', 'http://wclovers.com/product/woocommerce-frontend-manager-groups-staffs/' ) ) . '" aria-label="' . esc_attr__( 'Add custom capability with each Membership Level.', 'wc-multivendor-membership' ) . '">' . esc_html__( 'WCFM - Groups & Staffs', 'wc-multivendor-membership' ) . '</a>' );
			}

			return array_merge( $links, $row_meta, $groups_staffs_meta );
		}

		return (array) $links;
	}
}