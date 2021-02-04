<?php 

  global $WCFM; 
  //$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
  
  $wcfmmp_all_shiping_types = wcfmmp_get_shipping_types();
  $processing_time = wcfmmp_get_shipping_processing_times();
  
  $wcfmmp_shipping     = get_user_meta( $user_id, '_wcfmmp_shipping', true );
  
  $wcfmmp_shipping_by_country = get_user_meta( $user_id, '_wcfmmp_shipping_by_country', true );
  if( !$wcfmmp_shipping_by_country ) {
  	$wcfmmp_shipping_by_country = get_option( '_wcfmmp_shipping_by_country', array() );
  }

  $wcfmmp_country_rates       = get_user_meta( $user_id, '_wcfmmp_country_rates', true );
  if( !$wcfmmp_country_rates ) {
  	$wcfmmp_country_rates       = get_option( '_wcfmmp_country_rates', array() );
  }
  
  $wcfmmp_state_rates         = get_user_meta( $user_id, '_wcfmmp_state_rates', true );
  if( !$wcfmmp_state_rates ) {
  	$wcfmmp_state_rates         = get_option( '_wcfmmp_state_rates', array() );
  }
  
  $wcfmmp_marketplace_shipping_zone_options = get_option( 'woocommerce_wcfmmp_product_shipping_by_zone_settings', array() );
  if(!isset($wcfmmp_marketplace_shipping_zone_options['enabled']) || $wcfmmp_marketplace_shipping_zone_options['enabled'] == 'no' ) {
  	if( isset( $wcfmmp_all_shiping_types['by_zone'] ) ) unset( $wcfmmp_all_shiping_types['by_zone'] );
  }
  
  $wcfmmp_marketplace_shipping_options = get_option( 'woocommerce_wcfmmp_product_shipping_by_country_settings', array() );
  if(!isset($wcfmmp_marketplace_shipping_options['enabled']) || $wcfmmp_marketplace_shipping_options['enabled'] == 'no' ) {
  	if( isset( $wcfmmp_all_shiping_types['by_country'] ) ) unset( $wcfmmp_all_shiping_types['by_country'] );
  }
  
  $wcfmmp_marketplace_shipping_by_weight_options = get_option( 'woocommerce_wcfmmp_product_shipping_by_weight_settings', array() );
  if(!isset($wcfmmp_marketplace_shipping_by_weight_options['enabled']) || $wcfmmp_marketplace_shipping_by_weight_options['enabled'] == 'no' ) {
  	if( isset( $wcfmmp_all_shiping_types['by_weight'] ) ) unset( $wcfmmp_all_shiping_types['by_weight'] );
  }
  
  $wcfmmp_marketplace_shipping_by_distance_options = get_option( 'woocommerce_wcfmmp_product_shipping_by_distance_settings', array() );
  if(!isset($wcfmmp_marketplace_shipping_by_distance_options['enabled']) || $wcfmmp_marketplace_shipping_by_distance_options['enabled'] == 'no' ) {
  	if( isset( $wcfmmp_all_shiping_types['by_distance'] ) ) unset( $wcfmmp_all_shiping_types['by_distance'] );
  }
?>

  
<div id="wcfmmp_settings_form_shipping_expander" class="wcfm-content">
  <?php

    $WCFM->wcfm_fields->wcfm_generate_form_field (
              apply_filters( 'wcfmmp_settings_fields_shipping', array(
                "wcfmmp_shipping_enable" => array('label' => __('Enable Shipping', 'wc-multivendor-marketplace') , 'in_table' => true, 'name' => 'wcfmmp_shipping[_wcfmmp_user_shipping_enable]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => isset($wcfmmp_shipping['_wcfmmp_user_shipping_enable'])? $wcfmmp_shipping['_wcfmmp_user_shipping_enable'] : 'no', 'hints' => __('Check this if you want to enable shipping for your store', 'wc-multivendor-marketplace') ),
                "wcfmmp_pt" => array('label' => __('Processing Time', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping[_wcfmmp_pt]', 'type' => 'select', 'class' => 'wcfm-select wcfm_ele hide_if_shipping_disabled', 'label_class' => 'wcfm_title wcfm_ele hide_if_shipping_disabled', 'options' => $processing_time, 'value' => isset($wcfmmp_shipping['_wcfmmp_pt']) ? $wcfmmp_shipping['_wcfmmp_pt'] : '', 'hints' => __('The time required before sending the product for delivery', 'wc-multivendor-marketplace') ),
                "wcfmmp_shipping_type" => array(
                    'label' => __('Shipping Type', 'wc-multivendor-marketplace') , 
                    'name' => 'wcfmmp_shipping[_wcfmmp_user_shipping_type]', 
                    'type' => 'select', 
                    'class' => 'wcfm-select wcfm_ele hide_if_shipping_disabled', 
                    'label_class' => 'wcfm_title select_title wcfm_ele hide_if_shipping_disabled', 
                    'options' => $wcfmmp_all_shiping_types, 
                    'value' => isset($wcfmmp_shipping['_wcfmmp_user_shipping_type'])? $wcfmmp_shipping['_wcfmmp_user_shipping_type'] : '', 
                    'hints' => __('Select shipping type for your store', 'wc-multivendor-marketplace') ) 
                ), $user_id, $wcfmmp_shipping )
            );
  ?>
</div>
  
<div id="wcfmmp_settings_form_shipping_by_country" class="wcfm-content shipping_type by_country hide_if_shipping_disabled">
  <div class="wcfm_vendor_settings_heading">
    <h3><?php _e('Shipping By Country', 'wc-multivendor-marketplace'); ?></h3>
  </div>
  <?php if(!isset($wcfmmp_marketplace_shipping_options['enabled']) || $wcfmmp_marketplace_shipping_options['enabled'] == 'no' ) {
    _e('Shipping By Country is disabled by Admin. Please contact admin for details', 'wc-multivendor-marketplace');
  } else { ?>
  <?php

    $WCFM->wcfm_fields->wcfm_generate_form_field (
        apply_filters( 'wcfmmp_settings_fields_shipping_by_country', array(
          "wcfmmp_shipping_type_price" => array('label' => __('Default Shipping Price', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_country[_wcfmmp_shipping_type_price]', 'placeholder' => '0.00', 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_country['_wcfmmp_shipping_type_price']) ? $wcfmmp_shipping_by_country['_wcfmmp_shipping_type_price'] : '', 'hints' => __('This is the base price and will be the starting shipping price for each product', 'wc-multivendor-marketplace') ),
          "wcfmmp_additional_product" => array('label' => __('Per Product Additional Price', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_country[_wcfmmp_additional_product]', 'placeholder' => '0.00', 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_country['_wcfmmp_additional_product']) ? $wcfmmp_shipping_by_country['_wcfmmp_additional_product'] : '', 'hints' => __('If a customer buys more than one type product from your store, first product of the every second type will be charged with this price', 'wc-multivendor-marketplace') ),
          "wcfmmp_additional_qty" => array('label' => __('Per Qty Additional Price', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_country[_wcfmmp_additional_qty]', 'placeholder' => '0.00', 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_country['_wcfmmp_additional_qty']) ? $wcfmmp_shipping_by_country['_wcfmmp_additional_qty'] : '', 'hints' => __('Every second product of same type will be charged with this price', 'wc-multivendor-marketplace') ),
          "wcfmmp_byc_free_shipping_amount" => array('label' => __('Free Shipping Minimum Order Amount', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_country[_free_shipping_amount]', 'placeholder' => __( 'NO Free Shipping', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_country['_free_shipping_amount']) ? $wcfmmp_shipping_by_country['_free_shipping_amount'] : '', 'hints' => __('Free shipping will be available if order amount more than this. Leave empty to disable Free Shipping.', 'wc-multivendor-marketplace') ),
          "wcfmmp_byc_enable_local_pickup" => array('label' => __('Enable Local Pickup', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_country[_enable_local_pickup]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title checkbox-title wcfm_ele', 'value' => 'yes', 'dfvalue' => isset($wcfmmp_shipping_by_country['_enable_local_pickup']) ? 'yes' : '' ),
					"wcfmmp_byc_local_pickup_cost" => array('label' => __('Local Pickup Cost', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_country[_local_pickup_cost]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_country['_local_pickup_cost']) ? $wcfmmp_shipping_by_country['_local_pickup_cost'] : '' ),
          "wcfmmp_form_location" => array('label' => __('Ships from:', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_country[_wcfmmp_form_location]','type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_country['_wcfmmp_form_location']) ? $wcfmmp_shipping_by_country['_wcfmmp_form_location'] : '', 'hints' => __( 'Location from where the products are shipped for delivery. Usually it is same as the store.', 'wc-multivendor-marketplace' ) ),
          ) )
      );

    $wcfmmp_shipping_rates = array();
    $state_options = array();
    if ( $wcfmmp_country_rates ) {
      foreach ( $wcfmmp_country_rates as $country => $country_rate ) {
        $wcfmmp_shipping_state_rates = array();
        $state_options = array();
        if ( !empty( $wcfmmp_state_rates ) && isset( $wcfmmp_state_rates[$country] ) ) {
          foreach ( $wcfmmp_state_rates[$country] as $state => $state_rate ) {
            $state_options[$state] = $state;
            $wcfmmp_shipping_state_rates[] = array( 
                'wcfmmp_state_to' => $state, 
                'wcfmmp_state_to_price' => $state_rate, 
                'option_values' => $state_options 
              );
          }
        }
        $wcfmmp_shipping_rates[] = array( 
            'wcfmmp_country_to' => $country, 
            'wcfmmp_country_to_price' => $country_rate, 
            'wcfmmp_shipping_state_rates' => $wcfmmp_shipping_state_rates 
          );
      }   
    }
    
    $WCFM->wcfm_fields->wcfm_generate_form_field( 
      apply_filters( 'wcfmmp_settings_fields_shipping_rates_by_country', array( 
        "wcfmmp_shipping_rates" => array(
            'label' => __('Shipping Rates by Country', 'wc-multivendor-marketplace') , 
            'type' => 'multiinput', 
            'label_class' => 'wcfm_title wcfm_full_title', 
            'value' => $wcfmmp_shipping_rates, 
            'desc' => __( 'Add the countries you deliver your products to. You can specify states as well. If the shipping price is same except some countries, there is an option Everywhere Else, you can use that.', 'wc-multivendor-marketplace' ), 
            'options' => array(
                "wcfmmp_country_to" => array(
                    'label' => __('Country', 'wc-multivendor-marketplace'), 
                    'type' => 'country',
                    'wcfmmp_shipping_country' => 1, 
                    'class' => 'wcfm-select wcfmmp_country_to_select', 
                    'label_class' => 'wcfm_title'
                ),
                "wcfmmp_country_to_price" => array( 
                    'label' => __('Cost', 'wc-multivendor-marketplace') . '('.get_woocommerce_currency_symbol().')', 
                    'type' => 'number',
                    'dfvalue' => 0,
                    'placeholder' => '0.00',
                    'attributes' => array( 'min' => 0, 'step' => 0.1 ),
                    'class' => 'wcfm-text wcfm_non_negative_input', 
                    'label_class' => 'wcfm_title' 
                ),
                "wcfmmp_shipping_state_rates" => array(
                    'label' => __('State Shipping Rates', 'wc-multivendor-marketplace'), 
                    'type' => 'multiinput', 
                    'label_class' => 'wcfm_title wcfmmp_shipping_state_rates_label', 
                    'options' => array(
                        "wcfmmp_state_to" => array( 
                            'label' => __('State', 'wc-multivendor-marketplace'), 
                            'type' => 'select', 'class' => 'wcfm-select wcfmmp_state_to_select', 
                            'label_class' => 'wcfm_title', 
                            'options' => $state_options 
                        ),
                        "wcfmmp_state_to_price" => array( 
                            'label' => __('Cost', 'wc-multivendor-marketplace') . '('.get_woocommerce_currency_symbol().')', 
                            'type' => 'number', 
                            'dfvalue' => 0,
                            'placeholder' => '0.00 (' . __('Free Shipping', 'wc-multivendor-marketplace') . ')', 
                            'attributes' => array( 'min' => 0, 'step' => 0.1 ),
                            'class' => 'wcfm-text wcfm_non_negative_input', 
                            'label_class' => 'wcfm_title' 
                        ),

                    ) 
                )   
            ) 
        )
      ) ) 
    );
  }
  ?>
</div>

<?php
  $vendor_all_shipping_zones = wcfmmp_get_shipping_zone( '', $user_id );

?>
<div id="wcfmmp_settings_form_shipping_by_zone" class="wcfm-content shipping_type by_zone hide_if_shipping_disabled">
  <table class="wcfmmp-table shipping-zone-table">
    <thead>
      <tr>
        <th><?php _e('Zone Name', 'wc-multivendor-marketplace'); ?></th> 
        <th><?php _e('Region(s)', 'wc-multivendor-marketplace'); ?></th> 
        <th><?php _e('Shipping Method', 'wc-multivendor-marketplace'); ?></th>
      </tr></thead> 
    <tbody>

          <?php 
          if(!empty($vendor_all_shipping_zones)) {

            foreach ($vendor_all_shipping_zones as $key => $vendor_shipping_zones ){ ?>
            <tr>
              <td>
                <a href="JavaScript:void(0);" data-user-id="<?php echo $user_id; ?>" data-zone-id="<?php echo $vendor_shipping_zones['zone_id']; ?>" class="vendor_edit_zone">
                  <?php _e( $vendor_shipping_zones['zone_name'], 'wc-multivendor-marketplace'); ?>
                </a> 
                <div class="row-actions">
                  <a href="JavaScript:void(0);" data-user-id="<?php echo $user_id; ?>" data-zone-id="<?php echo $vendor_shipping_zones['zone_id']; ?>" class="vendor_edit_zone">
                    <?php _e( 'Edit', 'wc-multivendor-marketplace' ); ?>
                  </a>
                </div>
              </td> 
              <td>
                <?php _e( $vendor_shipping_zones['formatted_zone_location'], 'wc-multivendor-marketplace'); ?>
              </td> 
              <td>
                <p>
                  <?php 
                    $vendor_shipping_methods = $vendor_shipping_zones['shipping_methods'];
                    $vendor_shipping_methods_titles = array_column($vendor_shipping_methods, 'title');
                    $vendor_shipping_methods_titles = implode(', ', $vendor_shipping_methods_titles);
                    //print_r($vendor_shipping_methods_titles);
                    if(empty($vendor_shipping_methods)) { ?>
                      <span><?php _e('No method found&nbsp;', 'wc-multivendor-marketplace'); ?> </span> 
                      <a href="JavaScript:void(0);" data-user-id="<?php echo $user_id; ?>" data-zone-id="<?php echo $vendor_shipping_zones['zone_id']; ?>" class="vendor_edit_zone"><?php _e(' Add Shipping Methods', 'wc-multivendor-marketplace'); ?></a>
                    <?php  
                    } else { ?>
                      <div><?php _e($vendor_shipping_methods_titles, 'wc-multivendor-marketplace'); ?> </div> 
                      <a href="JavaScript:void(0);" data-user-id="<?php echo $user_id; ?>" data-zone-id="<?php echo $vendor_shipping_zones['zone_id']; ?>" class="vendor_edit_zone"><?php _e(' Edit Shipping Methods', 'wc-multivendor-marketplace'); ?></a>
                    <?php }
                  ?> 

                </p>
              </td>
            </tr>
            <?php 
            }
          ?>

      <?php } else { ?>
        <tr>
          <td colspan="3">
            <?php _e('No shipping zone found for configuration. Please contact with admin for manage your store shipping', 'wc-multivendor-marketplace') ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <div id="vendor_edit_zone">
  </div>

</div>

<div id="wcfmmp_settings_form_shipping_by_weight" class="wcfm-content shipping_type by_weight hide_if_shipping_disabled">
  <div class="wcfm_vendor_settings_heading">
    <h3><?php _e('Shipping By Weight', 'wc-multivendor-marketplace'); ?></h3>
  </div>
  <?php if(!isset($wcfmmp_marketplace_shipping_by_weight_options['enabled']) || $wcfmmp_marketplace_shipping_by_weight_options['enabled'] == 'no' ) {
    _e('Shipping By Weight is disabled by Admin. Please contact admin for details', 'wc-multivendor-marketplace');
  } else { ?>
  <?php
  $weight_unit = strtolower( get_option( 'woocommerce_weight_unit' ) );
  
  $wcfmmp_shipping_by_weight           = get_user_meta( $user_id, '_wcfmmp_shipping_by_weight', true );
  if( !$wcfmmp_shipping_by_weight ) {
  	$wcfmmp_shipping_by_weight         = get_option( '_wcfmmp_shipping_by_weight', array() );
  }
  
  $wcfmmp_country_weight_rates         = get_user_meta( $user_id, '_wcfmmp_country_weight_rates', true );
  if( !$wcfmmp_country_weight_rates ) {
  	$wcfmmp_country_weight_rates       = get_option( '_wcfmmp_country_weight_rates', array() );
  }
  
  $wcfmmp_country_weight_mode          = get_user_meta( $user_id, '_wcfmmp_country_weight_mode', true );
  if( !$wcfmmp_country_weight_mode ) {
  	$wcfmmp_country_weight_mode        = get_option( '_wcfmmp_country_weight_mode', array() );
  }
  
  $wcfmmp_country_weight_unit_cost     = get_user_meta( $user_id, '_wcfmmp_country_weight_unit_cost', true );
  if( !$wcfmmp_country_weight_unit_cost ) {
  	$wcfmmp_country_weight_unit_cost   = get_option( '_wcfmmp_country_weight_unit_cost', array() );
  }
								
  $wcfmmp_country_weight_default_costs  = get_user_meta( $user_id, '_wcfmmp_country_weight_default_costs', true );
  if( !$wcfmmp_country_weight_default_costs ) {
  	$wcfmmp_country_weight_default_costs  = get_option( '_wcfmmp_country_weight_default_costs', array() );
  }
  
  $wcfmmp_country_weight_shipping_value = array();
  if( $wcfmmp_country_weight_rates ) {
    foreach ($wcfmmp_country_weight_rates as $country => $each_rate ) {
      $wcfmmp_country_weight_shipping_value[] = array(
          'wcfmmp_weightwise_country_to' => $country,
          'wcfmmp_weightwise_country_mode' => isset( $wcfmmp_country_weight_mode[$country]) ? $wcfmmp_country_weight_mode[$country] : 'by_rule',
          'wcfmmp_weightwise_country_per_unit_cost' => isset( $wcfmmp_country_weight_unit_cost[$country]) ? $wcfmmp_country_weight_unit_cost[$country] : 0,
          'wcfmmp_weightwise_country_default_cost' => isset( $wcfmmp_country_weight_default_costs[$country]) ? $wcfmmp_country_weight_default_costs[$country] : 0,
          'wcfmmp_shipping_country_weight_settings' => $each_rate
      );
    }
  }
  
  
  $WCFM->wcfm_fields->wcfm_generate_form_field( 
      apply_filters( 'wcfmmp_settings_fields_shipping_rates_by_weight', array( 
      	"wcfmmp_byw_free_shipping_amount" => array('label' => __('Free Shipping Minimum Order Amount', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_weight[_free_shipping_amount]', 'placeholder' => __( 'NO Free Shipping', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_weight['_free_shipping_amount']) ? $wcfmmp_shipping_by_weight['_free_shipping_amount'] : '', 'hints' => __('Free shipping will be available if order amount more than this. Leave empty to disable Free Shipping.', 'wc-multivendor-marketplace') ),
      	"wcfmmp_byw_enable_local_pickup" => array('label' => __('Enable Local Pickup', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_weight[_enable_local_pickup]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title checkbox-title wcfm_ele', 'value' => 'yes', 'dfvalue' => isset($wcfmmp_shipping_by_weight['_enable_local_pickup']) ? 'yes' : '' ),
				"wcfmmp_byw_local_pickup_cost" => array('label' => __('Local Pickup Cost', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_weight[_local_pickup_cost]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_weight['_local_pickup_cost']) ? $wcfmmp_shipping_by_weight['_local_pickup_cost'] : '' ),
        "wcfmmp_shipping_rates_by_weight" => array(
            'label' => __('Country and Weight wise Shipping Rate Calculation', 'wc-multivendor-marketplace') , 
            'type' => 'multiinput', 
            'label_class' => 'wcfm_title wcfm_full_ele', 
            'value' => $wcfmmp_country_weight_shipping_value, 
            'desc' => __( 'Add the countries you deliver your products to and specify rates for weight range. If the shipping price is same except some countries/states, there is an option Everywhere Else, you can use that.', 'wc-multivendor-marketplace' ), 
            'options' => array(
                "wcfmmp_weightwise_country_to" => array(
                    'label' => __('Country', 'wc-multivendor-marketplace'), 
                    'type' => 'country',
                    'wcfmmp_shipping_country' => 1, 
                    'class' => 'wcfm-select wcfmmp_weightwise_country_to_select', 
                    'label_class' => 'wcfm_title'
                ),
                "wcfmmp_weightwise_country_mode" => array(
									'label' => __('Calculate cost', 'wc-multivendor-marketplace'), 
									'type' => 'select',
									'class' => 'wcfm-select wcfmmp_weightwise_country_mode_select', 
									'label_class' => 'wcfm_title',
									'options' => array( 'by_rule' => __( 'Based on rules', 'wc-multivendor-marketplace' ), 'by_unit' => __( 'Per unit cost', 'wc-multivendor-marketplace' ) ),
							),
							"wcfmmp_weightwise_country_per_unit_cost" => array(
									'label' => __('Per unit cost', 'wc-multivendor-marketplace') . ' ('.get_woocommerce_currency_symbol().'/'. get_option( 'woocommerce_weight_unit', 'kg' ) . ')', 
									'type' => 'number', 
									'dfvalue' => 0,
									'placeholder' => '0.00',
									'class' => 'wcfm-text wcfm_ele wcfmmp_weightwise_country_default_weight_text wcfmmp_weightwise_country_mode_by_unit', 
									'label_class' => 'wcfm_title wcfmmp_weightwise_country_mode_by_unit',
									'desc' => __( 'Shipping cost will be calculated by <b>Per unit cost x Product weight</b>', 'wc-multivendor-marketplace' ),
									'desc_class' => 'wcfm_page_options_desc wcfmmp_weightwise_country_mode_by_unit',
								),
                "wcfmmp_weightwise_country_default_cost" => array(
                    'label' => __('Country default cost if no matching rule', 'wc-multivendor-marketplace') . ' ('.get_woocommerce_currency_symbol().')', 
                    'type' => 'number', 
										'dfvalue' => 0,
										'placeholder' => '0.00',
                    'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfmmp_weightwise_country_default_weight_text wcfmmp_weightwise_country_mode_by_rule', 
                    'label_class' => 'wcfm_title wcfmmp_weightwise_country_mode_by_rule',
                ),
                "wcfmmp_shipping_country_weight_settings" => array(
                    'label' => __('Weight-Cost Rules', 'wc-multivendor-marketplace'), 
                    'type' => 'multiinput', 
                    'class' => 'wcfmmp_weightwise_country_mode_by_rule',
                    'label_class' => 'wcfm_title wcfmmp_shipping_weight_rates_label wcfmmp_weightwise_country_mode_by_rule', 
                    'options' => array(
                        "wcfmmp_weight_rule" => array( 
                            'label' => __('Weight Rule', 'wc-multivendor-marketplace'), 
                            'type' => 'select', 
                            'class' => 'wcfm-select wcfmmp_weight_rule_select', 
                            'label_class' => 'wcfm_title', 
                            'options' => array(
                              'up_to' => __('Weight up to', 'wc-multivendor-marketplace'),
                              'more_than' => __('Weight more than', 'wc-multivendor-marketplace')
                            ) 
                        ),
                        "wcfmmp_weight_unit" => array( 
                            'label' => __('Weight', 'wc-multivendor-marketplace') . ' ('.$weight_unit.')', 
                            'type' => 'number', 
                            'placeholder' => '0.00 (' . __('Free Shipping', 'wc-multivendor-marketplace') . ')', 
                            'attributes' => array( 'min' => 0, 'step' => 0.1 ),
                            'class' => 'wcfm-text wcfm_non_negative_input', 
                            'label_class' => 'wcfm_title' 
                        ),
                        "wcfmmp_weight_price" => array( 
                            'label' => __('Cost', 'wc-multivendor-marketplace') . ' ('.get_woocommerce_currency_symbol().')', 
                            'type' => 'number', 
                            'placeholder' => '0.00 (' . __('Free Shipping', 'wc-multivendor-marketplace') . ')',
                            'attributes' => array( 'min' => 0, 'step' => 0.1 ),
                            'class' => 'wcfm-text wcfm_non_negative_input', 
                            'label_class' => 'wcfm_title' 
                        ),
                        
                    )
                )
            )
        )
      ))
  );
  ?>
  <?php } ?>
  <div class="wcfm-clearfix"></div>
</div>

<div id="wcfmmp_settings_form_shipping_by_distance" class="wcfm-content shipping_type by_distance hide_if_shipping_disabled">
  <div class="wcfm_vendor_settings_heading">
    <h3><?php _e('Shipping By Distance', 'wc-multivendor-marketplace'); ?></h3>
  </div>
  <?php if(!isset($wcfmmp_marketplace_shipping_by_distance_options['enabled']) || $wcfmmp_marketplace_shipping_by_distance_options['enabled'] == 'no' ) {
    _e('Shipping By Distance is disabled by Admin. Please contact admin for details', 'wc-multivendor-marketplace');
  } else { ?>
  <?php
  $radius_unit   = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';
  
  $wcfmmp_shipping_by_distance           = get_user_meta( $user_id, '_wcfmmp_shipping_by_distance', true );
  if( !$wcfmmp_shipping_by_distance ) {
  	$wcfmmp_shipping_by_distance         = get_option( '_wcfmmp_shipping_by_distance', array() );
  }
  
  $wcfmmp_shipping_by_distance_rates         = get_user_meta( $user_id, '_wcfmmp_shipping_by_distance_rates', true );
  if( !$wcfmmp_shipping_by_distance_rates ) {
  	$wcfmmp_shipping_by_distance_rates       = get_option( '_wcfmmp_shipping_by_distance_rates', array() );
  }
  
  $WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_shipping_distance', array(																																					
																																									"wcfmmp_byd_default_cost" => array('label' => __('Default Cost', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_distance[_default_cost]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_distance['_default_cost']) ? $wcfmmp_shipping_by_distance['_default_cost'] : '', 'hints' => __('Default shipping cost, will be added with distance rule cost. Leave empty to consider default cost as `0`.', 'wc-multivendor-marketplace') ),
																																									"wcfmmp_byd_max_distance" => array('label' => __('Max Distance', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_distance[_max_distance]', 'placeholder' => __('No Limit', 'wc-multivendor-marketplace'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_distance['_max_distance']) ? $wcfmmp_shipping_by_distance['_max_distance'] : '', 'hints' => __('Upto maximum distance shipping supported. Leave empty to consider no limit.', 'wc-multivendor-marketplace') ),
																																									"wcfmmp_byd_free_shipping_amount" => array('label' => __('Free Shipping Minimum Order Amount', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_distance[_free_shipping_amount]', 'placeholder' => __( 'NO Free Shipping', 'wc-multivendor-marketplace'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_distance['_free_shipping_amount']) ? $wcfmmp_shipping_by_distance['_free_shipping_amount'] : '', 'hints' => __('Free shipping will be available if order amount more than this. Leave empty to disable Free Shipping.', 'wc-multivendor-marketplace') ),
																																									"wcfmmp_byd_enable_local_pickup" => array('label' => __('Enable Local Pickup', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_distance[_enable_local_pickup]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title checkbox-title wcfm_ele', 'value' => 'yes', 'dfvalue' => isset($wcfmmp_shipping_by_distance['_enable_local_pickup']) ? 'yes' : '' ),
																																									"wcfmmp_byd_local_pickup_cost" => array('label' => __('Local Pickup Cost', 'wc-multivendor-marketplace'), 'name' => 'wcfmmp_shipping_by_distance[_local_pickup_cost]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele', 'value' => isset($wcfmmp_shipping_by_distance['_local_pickup_cost']) ? $wcfmmp_shipping_by_distance['_local_pickup_cost'] : '' ),
																																				) ) );
	
	$WCFM->wcfm_fields->wcfm_generate_form_field( 
											apply_filters( 'wcfmmp_settings_fields_shipping_rates_by_distance', array( 
												"wcfmmp_shipping_by_distance_rates" => array(
													'label'       => __('Distance-Cost Rules', 'wc-multivendor-marketplace'), 
													'type'        => 'multiinput', 
													'class'       => 'wcfmmp_distance_wise_rule',
													'label_class' => 'wcfm_title wcfmmp_shipping_distance_rates_label wcfmmp_distance_wise_rule', 
													'value'       => $wcfmmp_shipping_by_distance_rates,
													'options' => array(
															"wcfmmp_distance_rule" => array( 
																	'label' => __('Distance Rule', 'wc-multivendor-marketplace'), 
																	'type' => 'select', 
																	'class' => 'wcfm-select wcfmmp_distance_rule_select', 
																	'label_class' => 'wcfm_title', 
																	'options' => array(
																		'up_to' => __('Distance up to', 'wc-multivendor-marketplace'),
																		'more_than' => __('Distance more than', 'wc-multivendor-marketplace')
																	) 
															),
															"wcfmmp_distance_unit" => array( 
																	'label' => __('Distance', 'wc-multivendor-marketplace') . ' ('.$radius_unit.')', 
																	'type' => 'number', 
																	'class' => 'wcfm-text wcfm_non_negative_input', 
																	'label_class' => 'wcfm_title' 
															),
															"wcfmmp_distance_price" => array( 
																	'label' => __('Cost', 'wc-multivendor-marketplace') . ' ('.get_woocommerce_currency_symbol().')', 
																	'type' => 'number', 
																	'placeholder' => '0.00 (' . __('Free Shipping', 'wc-multivendor-marketplace') . ')',
																	'class' => 'wcfm-text wcfm_non_negative_input', 
																	'label_class' => 'wcfm_title' 
															),
													)
												)
											) 
									  )
									);
  ?>
  <?php } ?>
  <div class="wcfm-clearfix"></div>
</div>