<?php
/**
 * WCFM plugin view
 *
 * WCFM WC Tiered Price Table Product Manage View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/integrations
 * @version   6.3.4
*/

global $wp, $WCFM, $WCFMu, $post, $woocommerce;

use  TierPricingTable\PriceManager ;

$product_id = 0;
		
$min_qty = 1;
$pricing_type = 'fixed';
$fixed_price_rules = array();
$percent_price_rules = array();

if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
	$product_id = absint( $wp->query_vars['wcfm-products-manage'] );
	
	if( $product_id ) {
		
		$min_qty = PriceManager::getProductQtyMin( $product_id, 'edit' );
		
		$pricing_type = PriceManager::getPricingType( $product_id );
		$price_rules = PriceManager::getFixedPriceRules( $product_id );
		
		if ( ! empty( $price_rules ) ) {
	    foreach ( $price_rules as $amount => $price ) {
	    	$fixed_price_rules[] = array( 'quantity' => $amount, 'price' => $price );
	    }
	  }
	  
	  if( WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
			$price_rules = PriceManager::getPercentagePriceRules( $product_id );
			if ( ! empty( $price_rules ) ) {
				foreach ( $price_rules as $amount => $price ) {
					$percent_price_rules[] = array( 'quantity' => $amount, 'discount' => $price );
				}
			}
		}
	}
}

$price_rules_types = array( 'fixed' => __( 'Fixed', 'tier-pricing-table' ) );
if( WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
	$price_rules_types = array( 'fixed' => __( 'Fixed', 'tier-pricing-table' ), 'percentage' => __( 'Percentage', 'tier-pricing-table' ) );
}

?>
<div class="page_collapsible products_manage_wc_tiered_price_table simple" id="wcfm_products_manage_form_wc_tiered_price_table_head"><label class="wcfmfa fa-dollar-sign"></label><?php _e('Tiered Price', 'wc-frontend-manager'); ?><span></span></div>
<div class="wcfm-container simple">
	<div id="wcfm_products_manage_form_wc_tiered_price_table_expander" class="wcfm-content">
		<?php
		 $wcfm_wc_tiered_price_table_fields = apply_filters( 'wcfm_wc_tiered_price_table_fields', array(  
			
			  "tiered_pricing_minimum" => array('label' => __( "Minimum quantity", 'tier-pricing-table' ) , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple', 'label_class' => 'wcfm_title simple', 'value' => $min_qty, 'hints' => __( 'Set if you are selling the product from qty more than 1', 'tier-pricing-table' ) ),	
			
				"tiered_price_rules_type" => array('label' => __( "Tiered pricing type", 'tier-pricing-table' ) , 'type' => 'select', 'options' => $price_rules_types, 'class' => 'wcfm-select wcfm_ele simple wcfm_non_negative_input', 'label_class' => 'wcfm_title simple', 'value' => $pricing_type ),
				
				"tiered_fixed_price_rules" => array('label' => __( "Tiered price", 'tier-pricing-table' ) , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele simple tiered_price_rule_type tiered_price_rule_type_fixed', 'label_class' => 'wcfm_title tiered_price_rule_type tiered_price_rule_type_fixed', 'value' => $fixed_price_rules, 'options' => array(
																																																										"quantity" => array('label' => __( 'Quantity', 'tier-pricing-table' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title simple' ),
																																																										"price" => array('label' => __('Price', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title simple' )
																																																										)
																																															),
				
				"tiered_percent_price_rules" => array('label' => __( "Tiered price", 'tier-pricing-table' ) , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele simple tiered_price_rule_type tiered_price_rule_type_percentage', 'label_class' => 'wcfm_title tiered_price_rule_type tiered_price_rule_type_percentage', 'value' => $percent_price_rules, 'options' => array(
																																																										"quantity" => array('label' => __( 'Quantity', 'tier-pricing-table' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title simple' ),
																																																										"discount" => array('label' => __('Percent discount', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple wcfm_non_negative_input', 'label_class' => 'wcfm_ele wcfm_title simple' )
																																																										)
																																															)
				
				
																													), $product_id );
		 
		 if( !WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
		 	 unset( $wcfm_wc_tiered_price_table_fields['tiered_pricing_minimum'] );
		 	 unset( $wcfm_wc_tiered_price_table_fields['tiered_percent_price_rules'] );
		 }
																													
		 $WCFM->wcfm_fields->wcfm_generate_form_field(	$wcfm_wc_tiered_price_table_fields );																								
		?>
		<div class="wcfm-clearfix"></div><br />
	</div>
</div>