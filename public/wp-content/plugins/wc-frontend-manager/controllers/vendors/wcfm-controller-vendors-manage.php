<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Venodrs Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/vendors
 * @version   3.4.7
 */

class WCFM_Vendors_Manage_Profile_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $wcfm_vendor_manage_profile_form_data;
		
		$wcfm_vendor_manage_profile_form_data = array();
	  parse_str($_POST['wcfm_vendor_manage_profile_form'], $wcfm_vendor_manage_profile_form_data);
	  
	  // WCFM form custom validation filter
		$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_vendor_manage_profile_form_data, 'vendor_manage' );
		if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
			$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
			if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
			echo '{"status": false, "message": "' . $custom_validation_error . '"}';
			die;
		}
	  
	  $vendor_id = absint( $wcfm_vendor_manage_profile_form_data['vendor_id'] );
	  
	  if( $vendor_id ) {
	  	$wcfm_vendor_manage_profile_fields = apply_filters( 'wcfm_vendor_manage_profile_fields', array( 'first_name'          => 'first_name',
																																																			'last_name'           => 'last_name',
																																																		) );
			
			foreach( $wcfm_vendor_manage_profile_fields as $wcfm_vendor_manage_profile_field_key => $wcfm_vendor_manage_profile_field ) {
				update_user_meta( $vendor_id, $wcfm_vendor_manage_profile_field_key, $wcfm_vendor_manage_profile_form_data[$wcfm_vendor_manage_profile_field] );
			}
	  	
	  	do_action( 'wcfm_vendor_manage_profile_update', $vendor_id, $wcfm_vendor_manage_profile_form_data );
	  }
		
		echo '{"status": true, "message": "' . __( 'Profile saved successfully', 'wc-frontend-manager' ) . '"}';
		 
		die;
	}
}

class WCFM_Vendors_Manage_Badges_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $wcfm_vendor_manage_badges_form_data;
		
		$wcfm_vendor_manage_badges_form_data = array();
	  parse_str($_POST['wcfm_vendor_manage_badges_form'], $wcfm_vendor_manage_badges_form_data);
	  
	  $vendor_id = absint( $wcfm_vendor_manage_badges_form_data['vendor_id'] );
	  
	  if( $vendor_id ) {
	  	if( isset( $wcfm_vendor_manage_badges_form_data['wcfm_vendor_badges'] ) ) {
				update_user_meta( $vendor_id, 'wcfm_vendor_badges', $wcfm_vendor_manage_badges_form_data['wcfm_vendor_badges'] );
			} else {
				update_user_meta( $vendor_id, 'wcfm_vendor_badges', array( -1 => 'NO' ) );
			}
			
	  	do_action( 'wcfm_vendor_manage_badges_update', $vendor_id, $wcfm_vendor_manage_badges_form_data );
	  }
		
		echo '{"status": true, "message": "' . __( 'Badges saved successfully', 'wc-frontend-manager' ) . '"}';
		 
		die;
	}
}