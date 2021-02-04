<?php

/**
 * WCFMvm plugin library
 *
 * Plugin intiate library
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/core
 * @version   1.0.0
 */
 
class WCFMvm_Library {
	
	public $lib_path;
  
  public $lib_url;
  
  public $php_lib_path;
  
  public $php_lib_url;
  
  public $js_lib_path;
  
  public $js_lib_url;
  
  public $css_lib_path;
  
  public $css_lib_url;
  
  public $views_path;
	
	public function __construct() {
    global $WCFMvm;
		
	  $this->lib_path = $WCFMvm->plugin_path . 'assets/';

    $this->lib_url = $WCFMvm->plugin_url . 'assets/';
    
    $this->php_lib_path = $this->lib_path . 'php/';
    
    $this->php_lib_url = $this->lib_url . 'php/';
    
    $this->js_lib_path = $this->lib_path . 'js/';
    
    $this->js_lib_url = $this->lib_url . 'js/';
    
    $this->js_lib_url_min = $this->js_lib_url . 'min/';
    
    $this->css_lib_path = $this->lib_path . 'css/';
    
    $this->css_lib_url = $this->lib_url . 'css/';
    
    $this->css_lib_url_min = $this->css_lib_url . 'min/';
    
    $this->views_path = $WCFMvm->plugin_path . 'views/';
    
    // Load wcfmvm Scripts
    add_action( 'wcfm_load_scripts', array( &$this, 'load_scripts' ) );
    add_action( 'after_wcfm_load_scripts', array( &$this, 'load_scripts' ) );
    
    // Load wcfmvm Styles
    add_action( 'wcfm_load_styles', array( &$this, 'load_styles' ) );
    add_action( 'after_wcfm_load_styles', array( &$this, 'load_styles' ) );
    
    // Load wcfmvm views
    //add_action( 'wcfm_load_views', array( &$this, 'load_views' ) );
    add_action( 'before_wcfm_load_views', array( &$this, 'load_views' ) );
  }
  
  public function load_scripts( $end_point ) {
	  global $WCFM, $WCFMvm;
	  
	  do_action( 'before_wcfmvm_load_scripts', $end_point );
    
	  switch( $end_point ) {
	  	
	  	case 'wcfm-memberships':
      	$WCFM->library->load_datatable_lib();
	    	wp_enqueue_script( 'wcfmvm_memberships_js', $this->js_lib_url . 'wcfmvm-script-memberships.js', array('jquery', 'dataTables_js'), $WCFMvm->version, true );
      break;
      
      case 'wcfm-memberships-manage':
      	$WCFM->library->load_collapsible_lib();
      	$WCFM->library->load_select2_lib();
	    	wp_enqueue_script( 'wcfmvm_membership_manage_js', $this->js_lib_url . 'wcfmvm-script-memberships-manage.js', array('jquery'), $WCFMvm->version, true );
	    	// Localized Script
        $wcfm_messages = get_wcfmvm_membership_manage_messages();
			  wp_localize_script( 'wcfmvm_membership_manage_js', 'wcfm_memberships_manage_messages', $wcfm_messages );
      break;
      
      case 'wcfm-memberships-settings':
      	$WCFM->library->load_collapsible_lib();
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_upload_lib();
      	
      	$WCFM->library->load_colorpicker_lib();
				wp_enqueue_script( 'iris', admin_url('js/iris.min.js'),array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), false, 1);
				wp_enqueue_script( 'wp-color-picker', admin_url('js/color-picker.min.js'), array('iris'), false,1);
				
				$colorpicker_l10n = array('clear' => __('Clear'), 'defaultString' => __('Default'), 'pick' => __('Select Color'));
				wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
				
				$wcfmvm_color_setting_options = $WCFMvm->wcfmvm_membership_color_setting_options();
				wp_localize_script( 'wp-color-picker', 'wcfmvm_color_setting_options', $wcfmvm_color_setting_options );
					
				$WCFM->library->load_multiinput_lib();
	    	wp_enqueue_script( 'wcfmvm_membership_settings_js', $this->js_lib_url . 'wcfmvm-script-memberships-settings.js', array('jquery', 'wcfm_multiinput_js'), $WCFMvm->version, true );
      break;
      
      case 'wcfm-messages':
      	wp_enqueue_script( 'wcfmvm_messages_js', $this->js_lib_url . 'wcfmvm-script-membership-approval.js', array('jquery', 'wcfm_messages_js' ), $WCFMvm->version, true );
      break;
      
      case 'wcfm-settings':
		  	if( wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_membership_manage_under_setting', false ) ) {
	    		wp_enqueue_script( 'wcfmvm_membership_cancel_js', $this->js_lib_url . 'wcfmvm-script-membership-cancel.js', array('jquery' ), $WCFMvm->version, true );
	    		
	    		wp_localize_script( 'wcfmvm_membership_cancel_js', 'wcfm_memberships_cancel_messages', array( "cancel_confirmation" => __( "Do you want to cancel this membership?\r\nYou can't undo this action ...", "wc-multivendor-membership" ) ) );
	    	}
		  break;
      
      case 'wcfm-profile':
	    	wp_enqueue_script( 'wcfmvm_profile_js', $this->js_lib_url . 'wcfmvm-script-membership-cancel.js', array('jquery', 'wcfm_profile_js' ), $WCFMvm->version, true );
	    	
	    	wp_localize_script( 'wcfmvm_profile_js', 'wcfm_memberships_cancel_messages', array( "cancel_confirmation" => __( "Do you want to cancel this membership?\r\nYou can't undo this action ...", "wc-multivendor-membership" ) ) );
		  break;
		  
		  case 'wcfm-vendors-manage':
		  	$WCFM->library->load_datepicker_lib();
		  	wp_enqueue_script( 'wcfmvm_vendor_membership_details_js', $this->js_lib_url . 'wcfmvm-script-membership-cancel.js', array('jquery' ), $WCFMvm->version, true );
		  	
		  	wp_localize_script( 'wcfmvm_vendor_membership_details_js', 'wcfm_memberships_cancel_messages', array( "cancel_confirmation" => __( "Do you want to cancel this membership?\r\nYou can't undo this action ...", "wc-multivendor-membership" ) ) );
		  break;
      
    }
  }
  
  public function load_styles( $end_point ) {
	  global $WCFM, $WCFMvm;
		
	  switch( $end_point ) {
	  	
	  	case 'wcfm-memberships':
	    	wp_enqueue_style( 'wcfmvm_memberships_css',  $this->css_lib_url . 'wcfmvm-style-memberships.css', array(), $WCFMvm->version );
		  break;
		  
		  case 'wcfm-memberships-manage':
	    	wp_enqueue_style( 'wcfmvm_memberships_manage_css',  $this->css_lib_url . 'wcfmvm-style-memberships-manage.css', array(), $WCFMvm->version );
		  break;
		  
		  case 'wcfm-memberships-settings':
	    	wp_enqueue_style( 'wcfmvm_memberships_settings_css',  $this->css_lib_url . 'wcfmvm-style-memberships-settings.css', array(), $WCFMvm->version );
		  break;
		  
		  case 'wcfm-settings':
		  	if( wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_membership_manage_under_setting', false ) ) {
	    		wp_enqueue_style( 'wcfmvm_memberships_details_css',  $this->css_lib_url . 'wcfmvm-style-membership-payment.css', array(), $WCFMvm->version );
	    	}
		  break;
		  
		  case 'wcfm-profile':
		  case 'wcfm-vendors-manage':
	    	wp_enqueue_style( 'wcfmvm_memberships_details_css',  $this->css_lib_url . 'wcfmvm-style-membership-payment.css', array(), $WCFMvm->version );
		  break;
		}
	}
	
	public function load_views( $end_point ) {
	  global $WCFM, $WCFMvm;
	  
	  switch( $end_point ) {
	  	
	  	case 'wcfm-memberships':
	  		if( !wcfm_is_marketplace() ) {
					include( $this->views_path . 'wcfmvm-view-memberships-disable.php' );
				} else {
					include( $this->views_path . 'wcfmvm-view-memberships.php' );
				}
      break;
      
      case 'wcfm-memberships-manage':
      	if( !wcfm_is_marketplace() ) {
					include( $this->views_path . 'wcfmvm-view-memberships-disable.php' );
				} else {
					include( $this->views_path . 'wcfmvm-view-memberships-manage.php' );
				}
      break;
      
      case 'wcfm-memberships-settings':
      	if( !wcfm_is_marketplace() ) {
					include( $this->views_path . 'wcfmvm-view-memberships-disable.php' );
				} else {
					include( $this->views_path . 'wcfmvm-view-memberships-settings.php' );
				}
      break;
      
      case 'wcfm-capability':
      	if( !wcfm_is_vendor() ) {
					//include_once( $this->views_path . 'wcfmvm-view-capability.php' );
				}
      break;
    }
  }
}