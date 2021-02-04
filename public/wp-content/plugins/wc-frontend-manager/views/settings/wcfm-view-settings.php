<?php
/**
 * WCFM plugin view
 *
 * WCFM Settings View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view
 * @version   1.1.6
 */

global $WCFM;

$wcfm_is_allow_manage_settings = apply_filters( 'wcfm_is_allow_manage_settings', true );
if( !$wcfm_is_allow_manage_settings ) {
	wcfm_restriction_message_show( "Settings" );
	return;
}

$wcfm_options = $WCFM->wcfm_options;

$quick_access_image_url = isset( $wcfm_options['wcfm_quick_access_icon'] ) ? $wcfm_options['wcfm_quick_access_icon'] : $WCFM->plugin_url . 'assets/images/wcfm-30x30.png';
$is_quick_access_disabled = isset( $wcfm_options['quick_access_disabled'] ) ? $wcfm_options['quick_access_disabled'] : 'no';
$is_dashboard_logo_disabled = isset( $wcfm_options['dashboard_logo_disabled'] ) ? $wcfm_options['dashboard_logo_disabled'] : 'no';
$is_welcome_box_disabled = isset( $wcfm_options['welcome_box_disabled'] ) ? $wcfm_options['welcome_box_disabled'] : 'no';
$is_menu_disabled = isset( $wcfm_options['menu_disabled'] ) ? $wcfm_options['menu_disabled'] : 'no';
$is_dashboard_theme_header_disabled = isset( $wcfm_options['dashboard_theme_header_disabled'] ) ? $wcfm_options['dashboard_theme_header_disabled'] : 'no';
$is_dashboard_full_view_disabled = isset( $wcfm_options['dashboard_full_view_disabled'] ) ? $wcfm_options['dashboard_full_view_disabled'] : 'no';
$is_slick_menu_disabled = isset( $wcfm_options['slick_menu_disabled'] ) ? $wcfm_options['slick_menu_disabled'] : 'no';
$is_responsive_float_menu_disabled = isset( $wcfm_options['responsive_float_menu_disabled'] ) ? $wcfm_options['responsive_float_menu_disabled'] : 'no';
$is_headpanel_disabled = isset( $wcfm_options['headpanel_disabled'] ) ? $wcfm_options['headpanel_disabled'] : 'no';
$is_float_button_disabled = isset( $wcfm_options['float_button_disabled'] ) ? $wcfm_options['float_button_disabled'] : 'no';
$is_enquiry_button_disabled = isset( $wcfm_options['enquiry_button_disabled'] ) ? $wcfm_options['enquiry_button_disabled'] : 'no';
$is_checklist_view_disabled = isset( $wcfm_options['checklist_view_disabled'] ) ? $wcfm_options['checklist_view_disabled'] : 'no';
$is_tags_input_disabled = isset( $wcfm_options['tags_input_disabled'] ) ? $wcfm_options['tags_input_disabled'] : 'no';
$wcfm_ultimate_notice_disabled = isset( $wcfm_options['wcfm_ultimate_notice_disabled'] ) ? $wcfm_options['wcfm_ultimate_notice_disabled'] : 'no';
$wcfm_my_store_label = wcfm_get_option( 'wcfm_my_store_label', __( 'My Store', 'wc-frontend-manager' ) );
$noloader = isset( $wcfm_options['noloader'] ) ? $wcfm_options['noloader'] : 'no';
$logo = get_option( 'wcfm_site_logo' ) ? get_option( 'wcfm_site_logo' ) : '';
$logo_image_url = $logo; //wp_get_attachment_url( $logo );

if ( !$logo_image_url ) {
	$logo_image_url = '';
}

$is_analytics_disabled = isset( $wcfm_options['analytics_disabled'] ) ? $wcfm_options['analytics_disabled'] : 'no';

$email_from_name = isset( $wcfm_options['email_from_name'] ) ? $wcfm_options['email_from_name'] : get_bloginfo( 'name' );
$email_from_address = isset( $wcfm_options['email_from_address'] ) ? $wcfm_options['email_from_address'] : get_option('admin_email');
$email_cc_address = isset( $wcfm_options['email_cc_address'] ) ? $wcfm_options['email_cc_address'] : '';
$email_bcc_address = isset( $wcfm_options['email_bcc_address'] ) ? $wcfm_options['email_bcc_address'] : '';

$wcfm_enquiry_button_label     = isset( $wcfm_options['wcfm_enquiry_button_label'] ) ? $wcfm_options['wcfm_enquiry_button_label'] : __( 'Ask a Question', 'wc-frontend-manager' );
$wcfm_enquiry_with_login       = isset( $wcfm_options['wcfm_enquiry_with_login'] ) ? $wcfm_options['wcfm_enquiry_with_login'] : 'no';
$wcfm_enquiry_allow_attachment = isset( $wcfm_options['wcfm_enquiry_allow_attachment'] ) ? $wcfm_options['wcfm_enquiry_allow_attachment'] : 'yes';
$wcfm_enquiry_button_position  = isset( $wcfm_options['wcfm_enquiry_button_position'] ) ? $wcfm_options['wcfm_enquiry_button_position'] : 'bellow_atc';
$wcfm_enquiry_custom_fields    = isset( $wcfm_options['wcfm_enquiry_custom_fields'] ) ? $wcfm_options['wcfm_enquiry_custom_fields'] : array();

// Remove WPML term filters - 3.4.1
if ( function_exists('icl_object_id') ) {
	global $sitepress;
	//remove_filter('get_terms_args', array( $sitepress, 'get_terms_args_filter'));
	//remove_filter('get_term', array($sitepress,'get_term_adjust_id'));
	//remove_filter('terms_clauses', array($sitepress,'terms_clauses'));
	
	$product_categories = array();
	$product_category_lists = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => 0, 'fields' => 'id=>name' ) );
	if( !empty( $product_category_lists ) ) {
		foreach( $product_category_lists as $product_category_id => $product_category_name ) {
			$product_category_list = get_term( $product_category_id );
			$product_category_list->term_id = $product_category_id;
			$product_category_list->name = $product_category_name;
			$product_categories[$product_category_id] = $product_category_list;
		}
	}
} else {
	$product_categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0&parent=0' );
}
$wcfm_product_type_categories = wcfm_get_option( 'wcfm_product_type_categories', array() );

$is_marketplace = wcfm_is_marketplace();
?>

<div class="collapse wcfm-collapse" id="">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-cogs"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Settings', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
		<?php do_action( 'before_wcfm_settings' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('Settings', 'wc-frontend-manager' ); ?></h2>
			
			<?php
			if( apply_filters( 'wcfm_is_allow_documentation_help', true ) && apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				echo '<a id="wcfm_video_tutorials" class="add_new_wcfm_ele_dashboard text_tip" target="_blank" href="https://wclovers.com/wcfm-tutorials/" data-tip="' . __('Video Tutorial', 'wc-frontend-manager') . '"><span class="wcfmfa fa-video"></span></a>';
				echo '<a id="wcfm_documentation" class="add_new_wcfm_ele_dashboard text_tip" target="_blank" href="http://wclovers.com/knowledgebase/" data-tip="' . __('Documentation', 'wc-frontend-manager') . '"><span class="wcfmfa fa-graduation-cap"></span></a>';
			}
			?>
			
			<?php if( wcfm_is_booking() ) { ?>
				<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() ) { ?>
					<a class="wcfm_gloabl_settings text_tip" href="<?php echo get_wcfm_bookings_settings_url(); ?>" data-tip="<?php _e( 'Bookings Global Settings', 'wc-frontend-manager' ); ?>"><span class="wcfmfa fa-cog"></span></a>
				<?php } else { ?>
					<a class="wcfm_gloabl_settings text_tip" href="#" onClick="return false;" data-tip="<?php wcfmu_feature_help_text_show( 'Bookings Global Settings', false, true ); ?>"><span class="wcfmfa fa-cog"></span></a>
				<?php } ?>
			<?php } ?>
			
			<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() ) { ?>
				<?php if( WCFMu_Dependencies::wcfm_wc_appointments_active_check() ) { ?>
					<a class="wcfm_gloabl_settings text_tip" href="<?php echo get_wcfm_appointment_settings_url(); ?>" data-tip="<?php _e( 'Appointments Global Settings', 'wc-frontend-manager' ); ?>"><span class="wcfmfa fa-cog"></span></a>
				<?php } ?>
			<?php } ?>
			
			<?php 
			if( apply_filters( 'wcfm_is_allow_capability_controller', true ) ) {
				echo '<a id="wcfm_capability_settings" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_capability_url().'" data-tip="' . __('Capability Controller', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-times"></span><span class="text">' . __( 'Capability', 'wc-frontend-manager') . '</span></a>';
			}
			if( WCFM_Dependencies::wcfmvm_plugin_active_check() && apply_filters( 'wcfm_is_pref_membership', true ) ) {
				echo '<a id="wcfm_membership_settings" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_memberships_settings_url().'" data-tip="' . __('Membership Settings', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Membership', 'wc-frontend-manager') . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
		<div class="wcfm_clearfix"></div><br />
		
		<form id="wcfm_settings_form" class="wcfm">
	
			<?php do_action( 'begin_wcfm_settings_form' ); ?>
			
			<div class="wcfm-tabWrap">
				
				<?php do_action( 'begin_wcfm_settings_form_dashboard', $wcfm_options ); ?>
			
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_dashboard_head">
					<label class="wcfmfa fa-chalkboard"></label>
					<?php _e('Dashboard', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_dashboard_expander" class="wcfm-content">
					  <h2><?php _e('Dashboard Setting', 'wc-frontend-manager'); ?></h2>
						<div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_style', array(
																																																"wcfm_logo" => array('label' => __('Logo', 'wc-frontend-manager') , 'type' => 'upload', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 150, 'value' => $logo_image_url ),
																																																"wcfm_quick_access_icon" => array('label' => __('Quick access icon', 'wc-frontend-manager') , 'type' => 'upload', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 75, 'value' => $quick_access_image_url ),
																																																"wcfm_my_store_label" => array( 'label' => __('My Store Label', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_my_store_label ),
																																																"quick_access_disabled" => array('label' => __('Disable Quick Access', 'wc-frontend-manager') , 'name' => 'quick_access_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_quick_access_disabled),
																																																//"dashboard_logo_disabled" => array('label' => __('Disable Sidebar Logo', 'wc-frontend-manager') , 'name' => 'dashboard_logo_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_dashboard_logo_disabled),
																																																"welcome_box_disabled" => array('label' => __('Disable Welcome Box', 'wc-frontend-manager') , 'name' => 'welcome_box_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_welcome_box_disabled),
																																																"menu_disabled" => array('label' => __('Disable WCFM Menu', 'wc-frontend-manager') , 'name' => 'menu_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_menu_disabled),
																																																"dashboard_theme_header_disabled" => array('label' => __('Disable Theme Header', 'wc-frontend-manager') , 'name' => 'dashboard_theme_header_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_dashboard_theme_header_disabled),
																																																"dashboard_full_view_disabled" => array('label' => __('Disable WCFM Full View', 'wc-frontend-manager') , 'name' => 'dashboard_full_view_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_dashboard_full_view_disabled),
																																																"slick_menu_disabled" => array('label' => __('Disable WCFM Slick Menu', 'wc-frontend-manager') , 'name' => 'slick_menu_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_slick_menu_disabled),
																																																//"responsive_float_menu_disabled" => array('label' => __('Disable Responsive Float Menu', 'wc-frontend-manager') , 'name' => 'responsive_float_menu_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_responsive_float_menu_disabled),
																																																"headpanel_disabled" => array('label' => __('Disable WCFM Header Panel', 'wc-frontend-manager') , 'name' => 'headpanel_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_headpanel_disabled),
																																																"float_button_disabled" => array('label' => __('Disable Float Button', 'wc-frontend-manager') , 'name' => 'float_button_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_float_button_disabled),
																																																//"enquiry_button_disabled" => array('label' => __('Disable Ask a Question Button', 'wc-frontend-manager') , 'name' => 'enquiry_button_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_enquiry_button_disabled),
																																																"checklist_view_disabled" => array('label' => __('Disable Category Checklist', 'wc-frontend-manager') , 'name' => 'checklist_view_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_checklist_view_disabled, 'hints' => __( 'Disable this to have product manager category/custom taxonomy selector as search-list.', 'wc-frontend-manager' ) ),
																																																"tags_input_disabled" => array('label' => __('Disable Tags Input Box', 'wc-frontend-manager') , 'name' => 'tags_input_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $is_tags_input_disabled, 'hints' => __( 'Disable this to have product manager tags input as drop-down, which will restrict to use only pre-defined tags.', 'wc-frontend-manager' ) ),
																																																"wcfm_ultimate_notice_disabled" => array('label' => __('Disable Ultimate Notice', 'wc-frontend-manager') , 'name' => 'wcfm_ultimate_notice_disabled','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wcfm_ultimate_notice_disabled),
																																																//"noloader" => array('label' => __('Disabled WCFM Loader', 'wc-frontend-manager') , 'name' => 'noloader','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $noloader),
																																																) ) );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php do_action( 'begin_wcfm_settings_form_modules', $wcfm_options ); ?>
				
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_form_module_head">
					<label class="fab fa-modx"></label>
					<?php _e('Modules', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_modules_expander" class="wcfm-content">
					  <h2><?php _e('Module Controller', 'wc-frontend-manager'); ?></h2>
						<div class="wcfm_clearfix"></div>
						<div class="module_head_message"><?php _e( 'Configure what to hide from your dashboard', 'wc-frontend-manager' ); ?></div>
						<?php
							$wcfm_modules = $WCFM->get_wcfm_modules();
							$wcfm_module_options = isset( $wcfm_options['module_options'] ) ? $wcfm_options['module_options'] : array();
							$wcfm_module_options = apply_filters( 'wcfm_module_options', $wcfm_module_options );
							foreach( $wcfm_modules as $wcfm_module => $wcfm_module_data ) {
								$wcfm_module_value = isset( $wcfm_module_options[$wcfm_module] ) ? $wcfm_module_options[$wcfm_module] : 'no';
								$hints = $wcfm_module_data['label'];
								if( isset( $wcfm_module_data['hints'] ) ) { $hints .= ': ' . $wcfm_module_data['hints']; }
								$background_image = 'background-image: url(' . $WCFM->plugin_url . 'assets/images/modules/' . $wcfm_module . '.png);';
								echo '<div class="wcfm_module_boxes"><div class="wcfm_module_box wcfm_module_' . $wcfm_module . ' text_tip" data-tip="' . $hints . '" style="' . $background_image . '">';
								$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																																		$wcfm_module => array( 'name' => 'module_options[' . $wcfm_module . ']', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'dfvalue' => $wcfm_module_value ),
																																		) );
								echo '<div class="wcfm-clearfix"></div></div></div>';
								
								if( isset( $wcfm_module_data['notice'] ) ) {
									if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
										if( apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
											wcfmu_feature_help_text_show( $wcfm_module_data['label'] );
										}
									}
								}
							}
							
							$hints = __('Analytics', 'wc-frontend-manager');
							$background_image = 'background-image: url(' . $WCFM->plugin_url . 'assets/images/modules/analytics.png);';
							echo '<div class="wcfm_module_boxes"><div class="wcfm_module_box wcfm_module_analytics_disabled text_tip" data-tip="' . $hints . '" style="' . $background_image . '">';
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_analytics', array(
																																																"analytics_disabled" => array( 'name' => 'analytics_disabled','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title module_options_title', 'dfvalue' => $is_analytics_disabled),
																																																) ) );
							echo '<div class="wcfm-clearfix"></div></div></div>';
							
							if( WCFM_Dependencies::wcfma_plugin_active_check() ) {
								do_action( 'wcfm_analytics_settings' );
							} else {
								if( $is_wcfma_inactive_notice_show = apply_filters( 'is_wcfma_inactive_notice_show', true ) ) {
									wcfma_feature_help_text_show( __( 'Analytics', 'wc-frontend-manager' ) );
								}
							}
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php do_action( 'begin_wcfm_settings_form_style', $wcfm_options ); ?>
			
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_form_style_head">
					<label class="wcfmfa fa-image"></label>
					<?php _e('Dashboard Style', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_style_expander" class="wcfm-content">
					  <h2><?php _e('Dashboard Display Setting', 'wc-frontend-manager'); ?></h2>
						<div class="wcfm_clearfix"></div>
						<?php
							$color_options = $WCFM->wcfm_color_setting_options();
							$color_options_array = array();
			
							foreach( $color_options as $color_option_key => $color_option ) {
								$color_options_array[$color_option['name']] = array( 'label' => $color_option['label'] , 'type' => 'colorpicker', 'class' => 'wcfm-text wcfm_ele colorpicker', 'label_class' => 'wcfm_title wcfm_ele', 'value' => ( isset($wcfm_options[$color_option['name']]) ) ? $wcfm_options[$color_option['name']] : $color_option['default'] );
							}
							$WCFM->wcfm_fields->wcfm_generate_form_field( $color_options_array );
						?>
						<div class="wcfm_clearfix"></div>
						<input type="submit" name="reset-color-settings" value="<?php _e( 'Reset to Default', 'wc-frontend-manager' ); ?>" id="wcfm_color_setting_reset_button" class="wcfm_submit_button" />
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php do_action( 'begin_wcfm_settings_form_pages', $wcfm_options ); ?>
			
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_form_pages_head">
					<label class="wcfmfa fa-newspaper"></label>
					<?php _e('Dashboard Pages', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_pages_expander" class="wcfm-content">
					  <h2><?php _e('Dashboard Page/Endpoint Setting', 'wc-frontend-manager'); ?></h2>
						<div class="wcfm_clearfix"></div>
						<?php
							$wcfm_page_options = get_option( 'wcfm_page_options', array() );
							$pages_array = array();
							if( isset( $wcfm_page_options['wc_frontend_manager_page_id'] ) ) {
								if ( get_post_status ( $wcfm_page_options['wc_frontend_manager_page_id'] ) ) {
								  $pages_array[$wcfm_page_options['wc_frontend_manager_page_id']] = get_post( $wcfm_page_options['wc_frontend_manager_page_id'] )->post_title;
								}
							}
							if( isset( $wcfm_page_options['wcfm_vendor_membership_page_id'] ) ) {
								if ( get_post_status ( $wcfm_page_options['wcfm_vendor_membership_page_id'] ) ) {
									$pages_array[$wcfm_page_options['wcfm_vendor_membership_page_id']] = get_post( $wcfm_page_options['wcfm_vendor_membership_page_id'] )->post_title;
								}
							}
							if( isset( $wcfm_page_options['wcfm_vendor_registration_page_id'] ) ) {
								if ( get_post_status ( $wcfm_page_options['wcfm_vendor_registration_page_id'] ) ) {
									$pages_array[$wcfm_page_options['wcfm_vendor_registration_page_id']] = get_post( $wcfm_page_options['wcfm_vendor_registration_page_id'] )->post_title;
								}
							}
							if( isset( $wcfm_page_options['wcfm_affiliate_registration_page_id'] ) ) {
								if ( get_post_status ( $wcfm_page_options['wcfm_affiliate_registration_page_id'] ) ) {
									$pages_array[$wcfm_page_options['wcfm_affiliate_registration_page_id']] = get_post( $wcfm_page_options['wcfm_affiliate_registration_page_id'] )->post_title;
								}
							}
							
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_pages', array(
																																																"wcfm_refresh_permalink" => array('label' => __('Refresh Permalink', 'wc-frontend-manager') , 'name' => 'wcfm_refresh_permalink','type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'hints' => __( 'Check to refresh WCfM page permalinks. Only apply if you are getting error (e.g. 404 not found) for any pages.', 'wc-frontend-manager' ) ),
																																																"wc_frontend_manager_page_id" => array( 'label' => __('Dashboard', 'wc-frontend-manager'), 'type' => 'select', 'name' => 'wcfm_page_options[wc_frontend_manager_page_id]', 'options' => $pages_array, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_page_options['wc_frontend_manager_page_id'], 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'This page should have shortcode - wc_frontend_manager', 'wc-frontend-manager') . '<br /><span style="color: #dd3333;">' . __( 'DO NOT USE WCFM DASHBOARD PAGE FOR OTHER PAGE SETTINGS, you will break your site if you do.', 'wc-frontend-manager' ) . '</span>' )
																																																) ) );
						
							if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
								if( apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
									wcfmu_feature_help_text_show( __( 'WCFM Endpoints', 'wc-frontend-manager' ) );
								}
							} else {
								?>
								<h2><?php _e( 'Dashboard End Points', 'wc-frontend-manager' ); ?></h2>
								<div class="wcfm_clearfix"></div>
								<div class="store_address">
								  <?php do_action( 'wcfm_settings_endpoints' ); ?>
								</div>
								<?php
							}
						?>
						
						<?php if( $WCFM->is_marketplace && ( $WCFM->is_marketplace == 'wcfmmarketplace' ) ) { ?>
							<h2><?php _e( 'Store End Points', 'wc-frontend-manager' ); ?></h2>
							<div class="wcfm_clearfix"></div>
							<div class="store_address">
								<?php
									// WCFMmp Store End Points Slug
									$wcfm_store_modified_endpoints = wcfm_get_option( 'wcfm_store_endpoints', array() );
									
									$wcfm_store_endpoints = apply_filters( 'wcfm_store_endpoints_slug', array( 
																																								'about'           => 'about', 
																																								'articles'        => 'articles',
																																								'policies'        => 'policies',
																																								'reviews'         => 'reviews',
																																								'followers'       => 'followers',
																																								'followings'      => 'followings'
																																								) );
									$wcfm_store_endpoints_edit_fileds = array();
									
									foreach( $wcfm_store_endpoints as $wcfm_endpoint_key => $wcfm_endpoint_val ) {
										$wcfm_store_endpoints_edit_fileds[$wcfm_endpoint_key] = array( 'label' => $wcfm_endpoint_key, 'name' => 'wcfm_store_endpoints[' . $wcfm_endpoint_key . ']','type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_slug_input', 'placeholder' => $wcfm_endpoint_val, 'value' => ! empty( $wcfm_store_modified_endpoints[$wcfm_endpoint_key] ) ? $wcfm_store_modified_endpoints[$wcfm_endpoint_key] : '', 'label_class' => 'wcfm_title' );
									}
									$WCFM->wcfm_fields->wcfm_generate_form_field( $wcfm_store_endpoints_edit_fileds );
								?>
							</div>
						<?php } ?>
							
						<h2><?php _e( 'My Account End Points', 'wc-frontend-manager' ); ?></h2>
						<div class="wcfm_clearfix"></div>
						<div class="store_address">
							<?php
								// My Account End Points Slug
								$wcfm_myac_modified_endpoints = wcfm_get_option( 'wcfm_myac_endpoints', array() );
								
								$wcfm_myac_endpoints = apply_filters( 'wcfm_myac_endpoints_slug', array( 
																																							'inquiry'             => 'inquiry', 
																																							'view-inquiry'        => 'view-inquiry',
																																							'followings'          => 'followings',
																																							'support-tickets'     => 'support-tickets',
																																							'view-support-ticket' => 'view-support-ticket'
																																							) );
								if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									unset( $wcfm_myac_endpoints['followings'] );
									unset( $wcfm_myac_endpoints['support-tickets'] );
									unset( $wcfm_myac_endpoints['view-support-ticket'] );
								}
								$wcfm_myac_endpoints_edit_fileds = array();
								
								foreach( $wcfm_myac_endpoints as $wcfm_endpoint_key => $wcfm_endpoint_val ) {
									$wcfm_myac_endpoints_edit_fileds[$wcfm_endpoint_key] = array( 'label' => $wcfm_endpoint_key, 'id' => 'wcfm_myac_endpoints_' . $wcfm_endpoint_key, 'name' => 'wcfm_myac_endpoints[' . $wcfm_endpoint_key . ']','type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_slug_input', 'placeholder' => $wcfm_endpoint_val, 'value' => ! empty( $wcfm_myac_modified_endpoints[$wcfm_endpoint_key] ) ? $wcfm_myac_modified_endpoints[$wcfm_endpoint_key] : '', 'label_class' => 'wcfm_title' );
								}
								$WCFM->wcfm_fields->wcfm_generate_form_field( $wcfm_myac_endpoints_edit_fileds );
							?>
						</div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php do_action( 'begin_wcfm_settings_form_menu_manager', $wcfm_options ); ?>
				
				<!-- collapsible -->
				<?php if( apply_filters( 'wcfm_is_pref_menu_manager', true ) ) { ?>
					<div class="page_collapsible" id="wcfm_settings_form_menu_manager_head">
						<label class="wcfmfa fa-server"></label>
						<?php _e('Menu Manager', 'wc-frontend-manager'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="wcfm_settings_form_manu_manager_expander" class="wcfm-content">
							<h2><?php _e('Dashboard Menu Manager', 'wc-frontend-manager'); ?></h2>
							<div class="wcfm_clearfix"></div>
							<?php
							$wcfm_home_menu_label = wcfm_get_option( 'wcfm_home_menu_label', __( 'Home', 'wc-frontend-manager' ) );
							$wcfm_menus = $WCFM->get_wcfm_menus();
							
							if( apply_filters( 'wcfm_is_pref_withdrawal', true ) ) {
								if( $is_marketplace && in_array( $is_marketplace, array( 'dokan', 'wcmarketplace', 'wcfmmarketplace' ) ) ) {
									$wcfm_menus['wcfm-payments'] = array(  'label'  => __( 'Payments', 'wc-frontend-manager' ),
																												 'url'        => wcfm_payments_url(),
																												 'icon'       => 'credit-card',
																												 'menu_for'   => 'vendor',
																												 'priority'   => 38
																												);
									$wcfm_menus['wcfm-withdrawal-requests'] = array(  'label'  => __( 'Withdrawal', 'wc-frontend-manager' ),
																																		 'url'        => wcfm_withdrawal_requests_url(),
																																		 'icon'       => 'credit-card',
																																		 'menu_for'   => 'admin',
																																		 'priority'   => 38
																																		);
								}
							}
							
							if( $is_marketplace && WCFM_Dependencies::wcfmu_plugin_active_check() && apply_filters( 'wcfm_is_pref_vendor_followers', true ) && function_exists('wcfm_followers_url') ) {
								$wcfm_menus['wcfm-followers'] = array( 'label'  => __( 'Followers', 'wc-frontend-manager-ultimate' ),
																											 'url'        => wcfm_followers_url(),
																											 'icon'       => 'child',
																											 'menu_for'   => 'vendor',
																											 'priority'   => 69.4
																											);
							}
							
							if( $is_marketplace && WCFM_Dependencies::wcfmu_plugin_active_check() && apply_filters( 'wcfm_is_pref_chatbox', true ) && function_exists('wcfm_chatbox_url') ) {
								$wcfm_menus['wcfm-chatbox'] = array(   'label'  => __( 'Chat Box', 'wc-frontend-manager-ultimate' ),
																											 'url'        => wcfm_chatbox_url(),
																											 'icon'       => 'comments',
																											 'menu_for'   => 'vendor',
																											 'priority'   => 70
																											);
							}
							
							if( $is_marketplace && ( $is_marketplace == 'wcfmmarketplace' ) ) {
								$wcfm_menus['wcfm-ledger'] = array( 'label'  => __( 'Ledger Book', 'wc-multivendor-marketplace' ),
																										 'url'        => wcfm_ledger_url(),
																										 'icon'       => 'money',
																										 'menu_for'   => 'vendor',
																										 'priority'   => 69
																										);
								$wcfm_menus['wcfm-media'] = array( 'label'  => __( 'Media', 'wc-multivendor-marketplace' ),
																										 'url'        => wcfm_media_url(),
																										 'icon'       => 'image',
																										 'menu_for'   => 'both',
																										 'priority'   => 3
																										);
								$wcfm_menus['wcfm-sell-items-catalog'] = array( 'label'  => __( 'Add to My Store', 'wc-multivendor-marketplace' ),
																										 'url'        => wcfm_sell_items_catalog_url(),
																										 'icon'       => 'hand-pointer',
																										 'menu_for'   => 'vendor',
																										 'priority'   => 70
																										);
							}
							
							uasort( $wcfm_menus, array( $WCFM, 'wcfm_sort_by_priority' ) );
							
							$wcfm_managed_menus = wcfm_get_option( 'wcfm_managed_menus', array() );
							$wcfm_formeted_menus = array(); 
							if( empty( $wcfm_managed_menus ) ) {
								foreach( $wcfm_menus as $wcfm_menu_key => $wcfm_menu ) {
									$wcfm_menu['slug'] = $wcfm_menu_key;
									$wcfm_menu['enable'] = 'yes';
									$wcfm_menu['custom'] = 'no';
									$wcfm_menu['menu_for'] = 'both';
									$wcfm_menu['new_tab'] = 'no';
									$wcfm_formeted_menus[$wcfm_menu['priority']] = $wcfm_menu;
								}
							} else {
								$wcfm_formeted_menus = $wcfm_managed_menus;
								foreach( $wcfm_menus as $wcfm_menu_key => $wcfm_menu ) {
									$has_managed = false;
									foreach( $wcfm_managed_menus as $wcfm_managed_menu_key => $wcfm_managed_menu ) {
										if( !empty( $wcfm_managed_menu['slug'] ) && ( $wcfm_menu_key == $wcfm_managed_menu['slug'] ) ) {
											$has_managed = true;
										}
									}
									if( !$has_managed ) {
										$wcfm_menu['slug'] = $wcfm_menu_key;
										$wcfm_menu['enable'] = 'yes';
										$wcfm_menu['custom'] = 'no';
										$wcfm_menu['menu_for'] = 'both';
										$wcfm_menu['new_tab'] = 'no';
										$wcfm_formeted_menus[$wcfm_menu['priority']] = $wcfm_menu;
									}
								}
							}
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_menu_manager_fields', array(
																																												"wcfm_home_menu_label" => array( 'label' => __('Home Menu Label', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_home_menu_label ),
																																												"wcfm_menu_manager" => array( 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_menu_manager_wrapper', 'label_class' => 'wcfm_full_title', 'value' => $wcfm_formeted_menus, 'options' => array(
																																																"enable"   => array('label' => __('Enable', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele menu_manager_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes'),
																																																"label" => array( 'label' => __('Label', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele menu_manager_ele', 'label_class' => 'wcfm_title'),
																																																"icon" => array( 'label' => __('Icon', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'hints' => __('Insert a valid Font-awesome icon class.', 'wc-frontend-manager')),
																																																"slug" => array( 'label' => __('Slug', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide', 'label_class' => 'wcfm_title wcfm_ele_hide'),
																																																"url" => array( 'label' => __('URL', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'),
																																																"capability" => array( 'label' => __('Capability', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide', 'label_class' => 'wcfm_title wcfm_ele_hide' ),
																																																"has_new" => array( 'label' => __('Has New?', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes' ),
																																																"new_label" => array( 'label' => __('New Menu Class', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide', 'label_class' => 'wcfm_title wcfm_ele_hide' ),
																																																"new_url" => array( 'label' => __('New Menu URL', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title' ),
																																																"new_class" => array( 'label' => __('New Menu Class', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide', 'label_class' => 'wcfm_title wcfm_ele_hide' ),
																																																"submenu_capability" => array( 'label' => __('Sub Menu Capability', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide', 'label_class' => 'wcfm_title wcfm_ele_hide' ),
																																																"menu_for" => array( 'label' => __('Menu For', 'wc-frontend-manager'), 'type' => 'select', 'options' => array( 'both' => __( 'All Users', 'wc-frontend-manager' ), 'admin' => __( 'Only Admin', 'wc-frontend-manager' ), 'vendor' => __( 'Only Vendors', 'wc-frontend-manager' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title'),
																																																"new_tab" => array( 'label' => __('Open in new tab?', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes' ),
																																																"custom"   => array( 'type' => 'hidden' ),
																																													) )
																																													) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
				<?php } ?>
				<!-- end collapsible -->
				
				<?php do_action( 'end_wcfm_settings_form_menu_manager', $wcfm_options ); ?>
				
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_form_email_from_head">
					<label class="wcfmfa fa-envelope"></label>
					<?php _e('Email Setting', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_email_from_expander" class="wcfm-content">
					  <h2><?php _e('Email Setting', 'wc-frontend-manager'); ?></h2>
						<div class="wcfm_clearfix"></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_email_from', array(
																																																"email_from_name" => array('label' => __('Email from name', 'wc-frontend-manager') , 'name' => 'email_from_name','type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'value' => $email_from_name, 'label_class' => 'wcfm_title', 'hints' => __( 'Notification emails will be triggered with this name. By default Site Name will be used', 'wc-frontend-manager' ) ),
																																																"email_from_address" => array('label' => __('Email from address', 'wc-frontend-manager') , 'name' => 'email_from_address','type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'value' => $email_from_address, 'label_class' => 'wcfm_title', 'hints' => __( 'Notification emails will be triggered from this email address. By default Site Admin Email will be used', 'wc-frontend-manager' ) ),
																																																"email_cc_address" => array('label' => __('CC Email address', 'wc-frontend-manager') , 'name' => 'email_cc_address','type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'value' => $email_cc_address, 'label_class' => 'wcfm_title', 'hints' => __( 'Notification emails will be CC to this email address.', 'wc-frontend-manager' ) ),
																																																"email_bcc_address" => array('label' => __('BCC Email address', 'wc-frontend-manager') , 'name' => 'email_bcc_address','type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'value' => $email_bcc_address, 'label_class' => 'wcfm_title', 'hints' => __( 'Notification emails will be BCC to this email address.', 'wc-frontend-manager' ) ),
																																																) ) );
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
				
				<?php if( apply_filters( 'wcfm_is_pref_enquiry', true ) ) { ?>
					<!-- collapsible -->
					<div class="page_collapsible" id="wcfm_settings_form_enquiry_head">
						<label class="wcfmfa fa-question-circle"></label>
						<?php _e('Inquiry Settings', 'wc-frontend-manager'); ?><span></span>
					</div>
					<div class="wcfm-container">
						<div id="wcfm_settings_form_enquiry_expander" class="wcfm-content">
							<h2><?php _e('Inquiry Module', 'wc-frontend-manager'); ?></h2>
							<?php wcfm_video_tutorial( 'https://www.youtube.com/embed/JeIvHgcVuGU' ); ?>
							<div class="wcfm_clearfix"></div>
							<?php
							$field_types = apply_filters( 'wcfm_product_custom_filed_types', array( 'text' => 'Text', 'number' => 'Number', 'textarea' => 'textarea', 'datepicker' => 'Date Picker', 'timepicker' => 'Time Picker', 'checkbox' => 'Check Box', 'select' => 'Select' ) ); //, 'upload' => 'File/Image' ) );
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_enquiry_custom_fields', array(
																																																"wcfm_enquiry_button_label" => array( 'label' => __('Button Label', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_enquiry_button_label ),
																																																"wcfm_enquiry_with_login" => array( 'label' => __('Require Login?', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => $wcfm_enquiry_with_login, 'hints' => __( 'Whether customer has to be logged-in to submit inquiry.', 'wc-frontend-manager' ) ),
																																																"wcfm_enquiry_allow_attachment" => array( 'label' => __('Reply Attachment?', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes', 'dfvalue' => $wcfm_enquiry_allow_attachment, 'hints' => __( 'Whether vendors and customers are allowed to add attachment(s) with inquiry reply.', 'wc-frontend-manager' ) ),
																																																"wcfm_enquiry_button_position" => array( 'label' => __('Button Position', 'wc-frontend-manager'), 'type' => 'select', 'options' => array( 'bellow_price' => __( 'Below Price', 'wc-frontend-manager' ), 'bellow_sc' => __( 'Below Short Description', 'wc-frontend-manager' ), 'bellow_atc' => __( 'Below Add to Cart', 'wc-frontend-manager' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_enquiry_button_position, 'desc_class' => 'wcfm_page_options_desc', 'desc' => __( 'Inquiry button display position at Single Product Page.', 'wc-frontend-manager' ) ),
																																																"wcfm_enquiry_custom_fields" => array('label' => __( 'Inquiry Form Custom Fields', 'wc-frontend-manager'), 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_enquiry_custom_fields, 'options' => array(
																																																								"enable"   => array('label' => __('Enable', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes'),
																																																								"type" => array( 'label' => __('Field Type', 'wc-frontend-manager'), 'type' => 'select', 'options' => $field_types, 'class' => 'wcfm-select wcfm_ele field_type_options', 'label_class' => 'wcfm_title'),           
																																																								"label" => array( 'label' => __('Label', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'),
																																																								"options" => array( 'label' => __('Options', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele field_type_select_options', 'label_class' => 'wcfm_title field_type_select_options', 'placeholder' => __( 'Insert option values | separated', 'wc-frontend-manager' ) ),
																																																								"help_text" => array( 'label' => __('Help Content', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title' ),
																																																								"required" => array( 'label' => __('Required?', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes' ),
																																																	) )
																																													) ) );
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
					<!-- end collapsible -->
				<?php } ?>
			
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_settings_form_product_wise_cats_head">
					<label class="wcfmfa fa-tags"></label>
					<?php _e('Product Type Categories', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_settings_form_product_wise_cats_expander" class="wcfm-content">
					  <h2><?php _e('Product Type Specific Category Setup', 'wc-frontend-manager'); ?></h2>
						<div class="wcfm_clearfix"></div>
						<?php
							$product_types = apply_filters( 'wcfm_product_types', array( 'simple' => __( 'Simple Product', 'wc-frontend-manager' ), 'variable' => __( 'Variable Product', 'wc-frontend-manager' ), 'grouped' => __( 'Grouped Product', 'wc-frontend-manager' ), 'external' => __( 'External/Affiliate Product', 'wc-frontend-manager' ) ) );
							
							if( !empty( $product_types ) ) {
								foreach( $product_types as $product_type => $product_type_label ) {
									$product_type_categories = isset( $wcfm_product_type_categories[$product_type] ) ? $wcfm_product_type_categories[$product_type] : array();
								?>
								<p class="wcfm_title catlimit_title"><strong><?php echo $product_type_label . ' '; _e( 'Categories', 'wc-frontend-manager' ); ?></strong></p><label class="screen-reader-text" for="vendor_product_cats"><?php echo $product_type_label . ' '; _e( 'Categories', 'wc-frontend-manager' ); ?></label>
								<select id="wcfm_product_type_categories<?php echo $product_type; ?>" name="wcfm_product_type_categories[<?php echo $product_type; ?>][]" class="wcfm-select wcfm_ele wcfm_product_type_categories" multiple="multiple" data-catlimit="-1" style="width: 60%; margin-bottom: 10px;">
									<?php
										if ( $product_categories ) {
											$WCFM->library->generateTaxonomyHTML( 'product_cat', $product_categories, $product_type_categories, '', false, false, false );
										}
									?>
								</select>
								<?php
								}
							}
						?>
						<p class="description instructions"><?php _e( 'Create group of your Store Categories as per Product Types. Product Manager will work according to that.', 'wc-frontend-manager' ); ?></p>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				<!-- end collapsible -->
			
			  <?php do_action( 'end_wcfm_settings', $wcfm_options ); ?>
			</div>
			
			<div id="wcfm_settings_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="save-data" value="<?php _e( 'Save', 'wc-frontend-manager' ); ?>" id="wcfm_settings_save_button" class="wcfm_submit_button" />
			</div>
			<input type="hidden" name="wcfm_nonce" value="<?php echo wp_create_nonce( 'wcfm_settings' ); ?>" />
		</form>	
		<?php
		do_action( 'after_wcfm_settings' );
		?>
	</div>
</div>