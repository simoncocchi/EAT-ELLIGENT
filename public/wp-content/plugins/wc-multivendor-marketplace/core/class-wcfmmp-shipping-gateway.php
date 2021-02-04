<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Shipping Gateway
 *
 * @author    WC Lovers
 * @package   wcfmmp/core
 * @version   1.0.0
 */


class WCFMmp_Shipping_Gateway {
  public function __construct() {
    global $WCFMmp;
    add_action( 'woocommerce_shipping_init', array( $this, 'load_shipping_methods' ) );
    add_action( 'woocommerce_shipping_methods', array( $this, 'register_shipping_methods' ) );
  }
  
  public function load_shipping_methods() {
    $this->load_gateway('shipping-by-country');
    $this->load_gateway('shipping-by-zone');
    $this->load_gateway('shipping-by-weight');
    $this->load_gateway('shipping-by-distance');
  }
  
  /**
    * Register the shipping method.
    *
    * @param array $methods Shipping methods.
    *
    * @return array Shipping methods.
    */
  
  public function register_shipping_methods( $methods ) {
    $methods['wcfmmp_product_shipping_by_country'] = 'WCFMmp_Shipping_By_Country';
    $methods['wcfmmp_product_shipping_by_zone'] = 'WCFMmp_Shipping_By_Zone';
    $methods['wcfmmp_product_shipping_by_weight'] = 'WCFMmp_Shipping_By_Weight';
    $methods['wcfmmp_product_shipping_by_distance'] = 'WCFMmp_Shipping_By_Distance';
    return $methods;
  }
  
  public function load_gateway( $class_name = '' ) {
    global $WCFMmp;
    if ('' != $class_name && '' != $WCFMmp->token) {
      require_once ( $WCFMmp->plugin_path . 'includes/shipping-gateways/' . 'class-' . esc_attr($WCFMmp->token) . '-' . esc_attr( $class_name ) . '.php');
    }
  }
  
}