<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WC Vendors Setings Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   2.1.1
 */

class WCFM_Settings_WCVendors_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
		$wcfm_settings_form_data = array();
	  parse_str($_POST['wcfm_settings_form'], $wcfm_settings_form);
	  
	  // WCFM form custom validation filter
		$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_settings_form, 'vendor_setting_manage' );
		if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
			$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
			if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
			echo '{"status": false, "message": "' . $custom_validation_error . '"}';
			die;
		}
	  
	  // sanitize
		//$wcfm_settings_form = array_map( 'sanitize_text_field', $wcfm_settings_form );
		//$wcfm_settings_form = array_map( 'stripslashes', $wcfm_settings_form );
		
		// sanitize html editor content
		$wcfm_settings_form['shop_description'] = ! empty( $_POST['profile'] ) ? apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['profile'], ENT_QUOTES, 'UTF-8' ) ) ) : '';
		
		if( apply_filters( 'wcfm_is_allow_store_name', true ) ) {
			update_user_meta( $user_id, 'pv_shop_name', $wcfm_settings_form['shop_name'] );
		}
		if( apply_filters( 'wcfm_is_allow_store_description', true ) ) {
			update_user_meta( $user_id, 'pv_seller_info', apply_filters( 'wcfm_editor_content_before_save', $wcfm_settings_form['seller_info'] ) );
			update_user_meta( $user_id, 'pv_shop_description', apply_filters( 'wcfm_editor_content_before_save', $wcfm_settings_form['shop_description'] ) );
		}
		if( apply_filters( 'wcfm_is_allow_store_phone', true ) ) {
			update_user_meta( $user_id, '_wcv_store_phone', $wcfm_settings_form['_wcv_store_phone'] );
		}
		update_user_meta( $user_id, 'pv_paypal', $wcfm_settings_form['paypal'] );
		update_user_meta( $user_id, '_wcv_company_url', $wcfm_settings_form['_wcv_company_url'] );
		
		// Set Vendor Store Logo
		if( apply_filters( 'wcfm_is_allow_store_logo', true ) ) {
			if(isset($wcfm_settings_form['wcfm_logo']) && !empty($wcfm_settings_form['wcfm_logo'])) {
				$wcfm_settings_form['wcfm_logo'] = $WCFM->wcfm_get_attachment_id($wcfm_settings_form['wcfm_logo']);
			} else {
				$wcfm_settings_form['wcfm_logo'] = '';
			}
			update_user_meta( $user_id, '_wcv_store_icon_id', $wcfm_settings_form['wcfm_logo'] );
		}
		
		// Bank Details Support - 4.1.0 
		if ( apply_filters( 'wcfm_is_allow_billing_bank_settings', true ) && apply_filters( 'wcvendors_vendor_dashboard_bank_details_enable', true ) ) {
			$wcfm_mangopay_setting_fields = array( 
																						'wcv_bank_account_name'      => 'wcv_bank_account_name',
																						'wcv_bank_account_number'    => 'wcv_bank_account_number',
																						'wcv_bank_name'              => 'wcv_bank_name',
																						'wcv_bank_routing_number'    => 'wcv_bank_routing_number',
																						'wcv_bank_iban'              => 'wcv_bank_iban',
																						'wcv_bank_bic_swift'         => 'wcv_bank_bic_swift',
																					);
			foreach( $wcfm_mangopay_setting_fields as $wcfm_setting_store_key => $wcfm_setting_store_field ) {
				if( isset( $wcfm_settings_form[$wcfm_setting_store_field] ) ) {
					update_user_meta( $user_id, $wcfm_setting_store_key, $wcfm_settings_form[$wcfm_setting_store_field] );
				}
			}
		}
		
		// MangoPay Support - 3.4.3 
		if( apply_filters( 'wcfm_is_allow_billing_mangopay_settings', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_mangopay_plugin_active_check() ) {
				$wcfm_mangopay_setting_fields = array( 
																							'vendor_account_type'        => 'vendor_account_type',
																							'vendor_iban'                => 'vendor_iban',
																							'vendor_bic'                 => 'vendor_bic',
																							'vendor_gb_accountnumber'    => 'vendor_gb_accountnumber',
																							'vendor_gb_sortcode'         => 'vendor_gb_sortcode',
																							'vendor_us_accountnumber'    => 'vendor_us_accountnumber',
																							'vendor_us_aba'              => 'vendor_us_aba',
																							'vendor_us_datype'           => 'vendor_us_datype',
																							'vendor_ca_bankname'         => 'vendor_ca_bankname',
																							'vendor_ca_instnumber'       => 'vendor_ca_instnumber',
																							'vendor_ca_branchcode'       => 'vendor_ca_branchcode',
																							'vendor_ca_accountnumber'    => 'vendor_ca_accountnumber',
																							'vendor_ot_country'          => 'vendor_ot_country',
																							'vendor_ot_bic'              => 'vendor_ot_bic',
																							'vendor_ot_accountnumber'    => 'vendor_ot_accountnumber',
																							'vendor_account_name'        => 'vendor_account_name',
																							'vendor_account_address1'    => 'vendor_account_address1',
																							'vendor_account_address2'    => 'vendor_account_address2',
																							'vendor_account_city'        => 'vendor_account_city',
																							'vendor_account_postcode'    => 'vendor_account_postcode',
																							'vendor_account_country'     => 'vendor_account_country',
																							'vendor_account_region'      => 'vendor_account_region'
																						);
				foreach( $wcfm_mangopay_setting_fields as $wcfm_setting_store_key => $wcfm_setting_store_field ) {
					if( isset( $wcfm_settings_form[$wcfm_setting_store_field] ) ) {
						update_user_meta( $user_id, $wcfm_setting_store_key, $wcfm_settings_form[$wcfm_setting_store_field] );
					}
				}
			}
		}
		
		do_action( 'wcfm_vendor_settings_update', $user_id, $wcfm_settings_form );
		do_action( 'wcfm_wcvendors_settings_update', $user_id, $wcfm_settings_form );
		
		echo '{"status": true, "message": "' . __( 'Settings saved successfully', 'wc-frontend-manager' ) . '"}';
		 
		die;
	}
}