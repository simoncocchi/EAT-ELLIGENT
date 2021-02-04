<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Setings Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   1.1.6
 */

class WCFM_Settings_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFM_Query, $wpdb, $_POST;
		
		$wcfm_settings_form_data = array();
	  parse_str($_POST['wcfm_settings_form'], $wcfm_settings_form);
	  
	  if( !defined('WCFM_REST_API_CALL') ) {
	  	if( isset( $wcfm_settings_form['wcfm_nonce'] ) && !empty( $wcfm_settings_form['wcfm_nonce'] ) ) {
	  		if( !wp_verify_nonce( $wcfm_settings_form['wcfm_nonce'], 'wcfm_settings' ) ) {
	  			echo '{"status": false, "message": "' . __( 'Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager' ) . '"}';
	  			die;
	  		}
	  	}
	  }
	  
	  $options = get_option( 'wcfm_options' );
	  
	  // WCFM form custom validation filter
		$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_settings_form, 'admin_setting_manage' );
		if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
			$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
			if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
			echo '{"status": false, "message": "' . $custom_validation_error . '"}';
			die;
		}
	  
	  // Quick Access Disabled
	  if( !isset($wcfm_settings_form['quick_access_disabled']) ) $options['quick_access_disabled'] = 'no';
	  else $options['quick_access_disabled'] = 'yes';
	  
	  // Dashboard Logo Disabled
	  if( !isset($wcfm_settings_form['dashboard_logo_disabled']) ) $options['dashboard_logo_disabled'] = 'no';
	  else $options['dashboard_logo_disabled'] = 'yes';
	  
	  // Welcome Box Disabled
	  if( !isset($wcfm_settings_form['welcome_box_disabled']) ) $options['welcome_box_disabled'] = 'no';
	  else $options['welcome_box_disabled'] = 'yes';
	  
	  // Menu Disabled
	  if( !isset($wcfm_settings_form['menu_disabled']) ) $options['menu_disabled'] = 'no';
	  else $options['menu_disabled'] = 'yes';
	  
	  // Theme Header Disabled
	  if( !isset($wcfm_settings_form['dashboard_theme_header_disabled']) ) $options['dashboard_theme_header_disabled'] = 'no';
	  else $options['dashboard_theme_header_disabled'] = 'yes';
	  
	  // Full View Disabled
	  if( !isset($wcfm_settings_form['dashboard_full_view_disabled']) ) $options['dashboard_full_view_disabled'] = 'no';
	  else $options['dashboard_full_view_disabled'] = 'yes';
	  
	  // Slick Menu Disabled
	  if( !isset($wcfm_settings_form['slick_menu_disabled']) ) $options['slick_menu_disabled'] = 'no';
	  else $options['slick_menu_disabled'] = 'yes';
	  
	  // Responsive FLoat Menu Disabled
	  //if( !isset($wcfm_settings_form['responsive_float_menu_disabled']) ) $options['responsive_float_menu_disabled'] = 'no';
	  $options['responsive_float_menu_disabled'] = 'yes';
	  
	  // Float Button Disabled
	  if( !isset($wcfm_settings_form['float_button_disabled']) ) $options['float_button_disabled'] = 'no';
	  else $options['float_button_disabled'] = 'yes';
	  
	  // Inquiry Button Disabled
	  //if( !isset($wcfm_settings_form['enquiry_button_disabled']) ) $options['enquiry_button_disabled'] = 'no';
	  //else $options['enquiry_button_disabled'] = 'yes';
	  
	  // Header Panel Disabled
	  if( !isset($wcfm_settings_form['headpanel_disabled']) ) $options['headpanel_disabled'] = 'no';
	  else $options['headpanel_disabled'] = 'yes';
	  
	  // Taxonomy Checklist vew Disabled
	  if( !isset($wcfm_settings_form['checklist_view_disabled']) ) $options['checklist_view_disabled'] = 'no';
	  else $options['checklist_view_disabled'] = 'yes';
	  
	  // Tags Input Box vew Disabled
	  if( !isset($wcfm_settings_form['tags_input_disabled']) ) $options['tags_input_disabled'] = 'no';
	  else $options['tags_input_disabled'] = 'yes';
	  
	  // Hover sub-menu vew Disabled
	  if( !isset($wcfm_settings_form['hover_submenu_disabled']) ) $options['hover_submenu_disabled'] = 'no';
	  else $options['hover_submenu_disabled'] = 'yes';
	  
	  // Ultimate Notice Disabled
	  if( !isset($wcfm_settings_form['wcfm_ultimate_notice_disabled']) ) $options['wcfm_ultimate_notice_disabled'] = 'no';
	  else $options['wcfm_ultimate_notice_disabled'] = 'yes';
	  
	  // Loader Disabled
	  if( !isset($wcfm_settings_form['noloader']) ) $options['noloader'] = 'no';
	  else $options['noloader'] = 'yes';
	  
	  // Set Site Logo
		if(isset($wcfm_settings_form['wcfm_logo']) && !empty($wcfm_settings_form['wcfm_logo'])) {
			$options['site_logo'] = $WCFM->wcfm_get_attachment_id($wcfm_settings_form['wcfm_logo']);
			update_option( 'wcfm_site_logo', $options['site_logo'] );
		} else {
			update_option( 'wcfm_site_logo', '' );
		}
		
		// Quick Access Icon
		if(isset($wcfm_settings_form['wcfm_quick_access_icon']) && !empty($wcfm_settings_form['wcfm_quick_access_icon'])) {
			$options['wcfm_quick_access_icon'] = $wcfm_settings_form['wcfm_quick_access_icon'];
		} else {
			$options['wcfm_quick_access_icon'] = $WCFM->plugin_url . '/assets/images/wcfm-30x30.png'; 
		}
		
		// Module Options
		if( isset($wcfm_settings_form['module_options']) ) {
			$options['module_options'] = $wcfm_settings_form['module_options'];
		} else {
			$options['module_options'] = array();
		}
		/*$wcfm_modules = $WCFM->get_wcfm_modules();
		foreach( $wcfm_modules as $wcfm_module => $wcfm_module_data ) {
			if( isset( $wcfm_settings_form['module_options'] ) && isset( $wcfm_settings_form['module_options'][$wcfm_module] ) ) {
				$options[$wcfm_module] = 'yes';
			} else {
				$options[$wcfm_module] = 'no';
			}
		}*/
		
		// Analytics Disabled
	  if( !isset($wcfm_settings_form['analytics_disabled']) ) $options['analytics_disabled'] = 'no';
	  else $options['analytics_disabled'] = 'yes';
	  
	  $color_options = $WCFM->wcfm_color_setting_options();
		foreach( $color_options as $color_option_key => $color_option ) {
			if( isset( $wcfm_settings_form[ $color_option['name'] ] ) ) { $options[$color_option['name']] = $wcfm_settings_form[ $color_option['name'] ]; } else { $options[$color_option['name']] = $color_option['default']; }
		}
		
		// Save WCFM page option
		if( isset( $wcfm_settings_form['wcfm_page_options'] ) ) {
			$wcfm_page_options = get_option("wcfm_page_options", array());
			$wcfm_page_options = array_merge( $wcfm_page_options, $wcfm_settings_form['wcfm_page_options'] );
			foreach( $wcfm_page_options as $wcfm_page_option_key => $wcfm_page_option_val ) {
				update_option( $wcfm_page_option_key, $wcfm_page_option_val );
			}
			update_option( 'wcfm_page_options', $wcfm_page_options );
		}
		
		// Save WCFMmp Store End Point Options
		if( isset( $wcfm_settings_form['wcfm_store_endpoints'] ) ) {
			wcfm_update_option( 'wcfm_store_endpoints', $wcfm_settings_form['wcfm_store_endpoints'] );
		}
		
		// Save WCFM My Account End Point Options
		if( isset( $wcfm_settings_form['wcfm_myac_endpoints'] ) ) {
			wcfm_update_option( 'wcfm_myac_endpoints', $wcfm_settings_form['wcfm_myac_endpoints'] );
		}
		
		// Save WCFM My Store Label
		if( isset( $wcfm_settings_form['wcfm_my_store_label'] ) ) {
			wcfm_update_option( 'wcfm_my_store_label', $wcfm_settings_form['wcfm_my_store_label'] );
		}
		
		// Save WCFM Home Label
		if( isset( $wcfm_settings_form['wcfm_home_menu_label'] ) ) {
			wcfm_update_option( 'wcfm_home_menu_label', $wcfm_settings_form['wcfm_home_menu_label'] );
		}
		
		// Save WCFM Menu Manager
		if( apply_filters( 'wcfm_is_pref_menu_manager', true ) ) {
			if( isset( $wcfm_settings_form['wcfm_menu_manager'] ) ) {
				$wcfm_menus = $wcfm_settings_form['wcfm_menu_manager'];
				$wcfm_formeted_menus = array(); 
				foreach( $wcfm_menus as $wcfm_menu_key => $wcfm_menu ) {
					if( empty( $wcfm_menu['slug'] ) && !empty( $wcfm_menu['label'] ) ) {
						$wcfm_menu['slug'] = sanitize_title( $wcfm_menu['label'] );
						$wcfm_menu['custom'] = 'yes';
					}
					$wcfm_formeted_menus[] = $wcfm_menu;
				}
				wcfm_update_option( 'wcfm_managed_menus', $wcfm_formeted_menus  );
			}
		}
		
		// Email From Name
		if( isset($wcfm_settings_form['email_from_name']) ) {
			$options['email_from_name'] = $wcfm_settings_form['email_from_name'];
		} else {
			$options['email_from_name'] = get_bloginfo( 'name' );
		}
		
		// Enquiry Button Label
		if( isset($wcfm_settings_form['wcfm_enquiry_button_label']) ) {
			$options['wcfm_enquiry_button_label'] = $wcfm_settings_form['wcfm_enquiry_button_label'];
		} else {
			$options['wcfm_enquiry_button_label'] = __( 'Ask a Question', 'wc-frontend-manager' );
		}
		
		// Enquiry With Login 
		if( isset($wcfm_settings_form['wcfm_enquiry_with_login']) ) {
			$options['wcfm_enquiry_with_login'] = 'yes';
		} else {
			$options['wcfm_enquiry_with_login'] = 'no';
		}
		
		// Enquiry Allow Attachment
		if( isset($wcfm_settings_form['wcfm_enquiry_allow_attachment']) ) {
			$options['wcfm_enquiry_allow_attachment'] = 'yes';
		} else {
			$options['wcfm_enquiry_allow_attachment'] = 'no';
		}
		
		// Enquiry Button Position
		if( isset($wcfm_settings_form['wcfm_enquiry_button_position']) ) {
			$options['wcfm_enquiry_button_position'] = $wcfm_settings_form['wcfm_enquiry_button_position'];
		} else {
			$options['wcfm_enquiry_button_position'] = 'bellow_atc';
		}
		
		// Enquiry Custom Fields
		if( isset($wcfm_settings_form['wcfm_enquiry_custom_fields']) ) {
			$options['wcfm_enquiry_custom_fields'] = $wcfm_settings_form['wcfm_enquiry_custom_fields'];
		} else {
			$options['wcfm_enquiry_custom_fields'] = array();
		}
		
		// Email From Address
		if( isset($wcfm_settings_form['email_from_address']) ) {
			$options['email_from_address'] = $wcfm_settings_form['email_from_address'];
		} else {
			$options['email_from_address'] = get_option('admin_email');
		}
		
		// CC Email Address
		if( isset($wcfm_settings_form['email_cc_address']) ) {
			$options['email_cc_address'] = $wcfm_settings_form['email_cc_address'];
		} else {
			$options['email_cc_address'] = '';
		}
		
		// BCC Email Address
		if( isset($wcfm_settings_form['email_bcc_address']) ) {
			$options['email_bcc_address'] = $wcfm_settings_form['email_bcc_address'];
		} else {
			$options['email_bcc_address'] = '';
		}
		
		// Save Product Type wise categories
		if( isset( $wcfm_settings_form['wcfm_product_type_categories'] ) ) {
			wcfm_update_option( 'wcfm_product_type_categories', $wcfm_settings_form['wcfm_product_type_categories'] );
		} else {
			wcfm_update_option( 'wcfm_product_type_categories', array() );
		}
		
	  update_option( 'wcfm_options', $options );
	  
		// Init WCFM Custom CSS file
		$wcfm_style_custom = $WCFM->wcfm_create_custom_css();
		 
		$upload_dir      = wp_upload_dir();
		echo '{"status": true, "message": "' . __( 'Settings saved successfully', 'wc-frontend-manager' ) . '", "file": "' . trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfm_style_custom . '"}';
		
		if( isset( $wcfm_settings_form['wcfm_refresh_permalink'] ) || apply_filters( 'wcfm_is_allow_refresh_permalink', false ) ) {
			$permalink_refresh = true;
			
			global $sitepress;
			if ( function_exists('icl_object_id') && $sitepress ) {
				$default_lang = $sitepress->get_default_language();
				$current_lang = ICL_LANGUAGE_CODE;
				if( $default_lang != $current_lang ) {
					$permalink_refresh = false;
				}
			}
			
			if( $permalink_refresh ) {
				// Intialize WCFM End points
				$WCFM_Query->init_query_vars();
				$WCFM_Query->add_endpoints();
			
				// Flush rules after endpoint update
				flush_rewrite_rules();
			}
		}
		
		do_action( 'wcfm_settings_update', $wcfm_settings_form );
		 
		die;
	}
}