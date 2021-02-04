<?php
/**
 * WCFMvm plugin Views
 *
 * Plugin Capability View
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/views
 * @version   1.0.1
 */
?>

<?php

/**
 * WCFM advanced capability
 *
 * @since 1.0.1
 */
 
 
add_action( 'wcfm_capability_settings_miscellaneous', 'wcfmvm_capability_settings_miscellaneous_advanced', 60 );

function wcfmvm_capability_settings_miscellaneous_advanced( $wcfm_capability_options ) {
	global $WCFM, $WCFMu;
	
	$analytics = ( isset( $wcfm_capability_options['analytics'] ) ) ? $wcfm_capability_options['analytics'] : 'no';
	
	?>
	<div class="wcfm_clearfix"></div>
	<div class="vendor_capability_sub_heading"><h3><?php _e( 'Analytics', 'wc-multivendor-membership' ); ?></h3></div>
	
	<?php
	$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_capability_vendor_membership', array(  
																															 "analytics" => array('label' => __('Analytics', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_capability_options[analytics]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $analytics),
																								) ) );
}