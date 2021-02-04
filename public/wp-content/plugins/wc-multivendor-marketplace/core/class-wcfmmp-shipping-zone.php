<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Shipping Zones
 *
 * @author    WC Lovers
 * @package   wcfmmp/core
 * @version   1.0.0
 */

class WCFMmp_Shipping_Zone {

  /**
   * Get All Zone
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function get_zones($user_id = 0) {
    $data_store = WC_Data_Store::load( 'shipping-zone' );
    $raw_zones  = $data_store->get_zones();
    $zones      = array();
    if($user_id) {
      $vendor_id = $user_id;
    } else {
      $vendor_id  = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    }
    foreach ( $raw_zones as $raw_zone ) {
			$zone             = new WC_Shipping_Zone( $raw_zone );
			$enabled_methods  = $zone->get_shipping_methods( true );
			$methods_id = wp_list_pluck( $enabled_methods, 'id' );

			if ( in_array( 'wcfmmp_product_shipping_by_zone', $methods_id ) ) {
				$zones[ $zone->get_id() ]                            = $zone->get_data();
				$zones[ $zone->get_id() ]['zone_id']                 = $zone->get_id();
				$zones[ $zone->get_id() ]['formatted_zone_location'] = $zone->get_formatted_location();
				$zones[ $zone->get_id() ]['shipping_methods']        = self::get_shipping_methods( $zone->get_id(), $vendor_id );
			}
    }

    // Everywhere zone if has method called vendor shipping
    $overall_zone         = new WC_Shipping_Zone(0);
    $enabled_methods  = $overall_zone->get_shipping_methods( true );
    $methods_id = wp_list_pluck( $enabled_methods, 'id' );

    if ( in_array( 'wcfmmp_product_shipping_by_zone', $methods_id ) ) {
        $zones[ $overall_zone->get_id() ]                            = $overall_zone->get_data();
        $zones[ $overall_zone->get_id() ]['zone_id']                 = $overall_zone->get_id();
        $zones[ $overall_zone->get_id() ]['formatted_zone_location'] = $overall_zone->get_formatted_location();
        $zones[ $overall_zone->get_id() ]['shipping_methods']        = self::get_shipping_methods( $overall_zone->get_id(), $vendor_id );
    }

    return $zones;
  }

  /**
   * Get single zone info
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function get_zone( $zone_id, $user_id = 0 ) {
    $zone = array();
    if( $user_id ) {
      $vendor_id = $user_id;
    } else {
      $vendor_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    }

    $zone_obj = WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );
    $zone['data']                    = $zone_obj->get_data();
    $zone['formatted_zone_location'] = $zone_obj->get_formatted_location();
    $zone['shipping_methods']        = self::get_shipping_methods( $zone_id, $vendor_id );
    $zone['locations']               = self::get_locations( $zone_id, $user_id );

    return $zone;
  }

  /**
   * Add Shipping Method for a zone
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function add_shipping_methods( $data ) {
    global $wpdb;

    $table_name = "{$wpdb->prefix}wcfm_marketplace_shipping_zone_methods";

    if ( empty( $data['method_id'] ) ) {
        return new WP_Error( 'no-method-id', __( 'No shipping method found for adding', 'wc-multivendor-marketplace' ) );
    }
    
    if( $data['user_id'] != 0 ) {
      $vendor_id = $data['user_id'];
    } else {
      $vendor_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    }
    

    $result = $wpdb->insert(
        $table_name,
        array(
            'method_id' => $data['method_id'],
            'zone_id'   => $data['zone_id'],
            'vendor_id' => $vendor_id
        ),
        array(
            '%s',
            '%d',
            '%d'
        )
    );

    if ( ! $result ) {
        return new WP_Error( 'method-not-added', __( 'Shipping method not added successfully', 'wc-multivendor-marketplace' ) );
    }

    return $wpdb->insert_id;
  }

  /**
   * Delete shipping method
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function delete_shipping_methods( $data ) {
    global $wpdb;

    $table_name = "{$wpdb->prefix}wcfm_marketplace_shipping_zone_methods";
    $vendor_id = empty( $data['user_id'] ) ? apply_filters( 'wcfm_current_vendor_id', get_current_user_id() ) : $data['user_id'];
    $result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE zone_id=%d AND vendor_id=%d AND instance_id=%d", $data['zone_id'], $vendor_id, $data['instance_id'] ) );

    if ( ! $result ) {
        return new WP_Error( 'method-not-deleted', __( 'Shipping method not deleted', 'wc-multivendor-marketplace' ) );
    }

    return $result;
  }

  /**
   * Get Shipping Methods for a zone
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function get_shipping_methods( $zone_id, $vendor_id ) {
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_shipping_zone_methods WHERE `zone_id`={$zone_id} AND `vendor_id`={$vendor_id}";
    $results = $wpdb->get_results( $sql );

    $method = array();

    foreach ( $results as $key => $result ) {
        $default_settings = array(
            'title'       => self::get_method_label( $result->method_id ),
            'description' => __( 'Lets you charge a rate for shipping', 'wc-multivendor-marketplace' ),
            'cost'        => '0',
            'tax_status'  => 'none'
        );

        $method_id = $result->method_id .':'. $result->instance_id;
        $settings = ! empty( $result->settings ) ? maybe_unserialize( $result->settings ) : array();
        $settings = wp_parse_args( $settings, $default_settings );

        $method[$method_id]['instance_id'] = $result->instance_id;
        $method[$method_id]['id']          = $result->method_id;
        $method[$method_id]['enabled']     = ( $result->is_enabled ) ? 'yes' : 'no';
        $method[$method_id]['title']       = $settings['title'];
        $method[$method_id]['settings']    = array_map( 'stripslashes_deep', maybe_unserialize( $settings ) );
    }

    return $method;
  }

  /**
   * Update shipping method settings
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function update_shipping_method( $args ) {
    global $wpdb;
    
    $instance_id = $args['instance_id'];
    $zone_id = $args['zone_id'];
    $method_id = $args['method_id'];
    $vendor_id = empty( $args['user_id'] ) ? apply_filters( 'wcfm_current_vendor_id', get_current_user_id() ) : $args['user_id'];
    $settings = $args['settings'];
    
    // WPML Shipping Class Compatibility - 6.4.4
    if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
			$old_settings = array();
			$sql = "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_shipping_zone_methods WHERE `instance_id`={$instance_id}";
			$results = $wpdb->get_results( $sql );
			if( !empty( $results ) ) {
				foreach ( $results as $key => $result ) {
					$old_settings = ! empty( $result->settings ) ? maybe_unserialize( $result->settings ) : array();
				}
			}
			
			if( !empty( $old_settings ) ) {
				foreach( $old_settings as $key => $value ) {
					if( !isset( $settings[$key] ) ) {
						$settings[$key] = $value;
					}
				}
			}
		}

    $data = array(
        'method_id' => $method_id,
        'zone_id'   => $zone_id,
        'vendor_id' => $vendor_id,
        'settings'  => maybe_serialize( $settings )
    );

    $table_name = "{$wpdb->prefix}wcfm_marketplace_shipping_zone_methods";
    $updated = $wpdb->update( $table_name, $data, array( 'instance_id' => $args['instance_id'] ), array( '%s', '%d', '%d', '%s' ) );

    if ( $updated ) {
        return $data;
    }

    return false;
  }

  /**
   * Toggle shipping method
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function toggle_shipping_method( $data ) {
    global $wpdb;
    $table_name = "{$wpdb->prefix}wcfm_marketplace_shipping_zone_methods";
    $vendor_id = empty( $data['user_id'] ) ? apply_filters( 'wcfm_current_vendor_id', get_current_user_id() ) : $data['user_id'];
    $updated    = $wpdb->update( $table_name, array( 'is_enabled' => $data['checked']  ), array( 'instance_id' => $data['instance_id' ], 'zone_id' => $data['zone_id'], 'vendor_id' => $vendor_id ), array( '%d' ) );

    if ( ! $updated ) {
        return new WP_Error( 'method-not-toggled', __( 'Method enable or disable not working', 'wc-multivendor-marketplace' ) );
    }

    return true;
  }

  /**
   * Get zone locations
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function get_locations( $zone_id, $vendor_id = null ) {
    global $wpdb;

    $table_name = "{$wpdb->prefix}wcfm_marketplace_shipping_zone_locations";

    if ( ! $vendor_id ) {
        $vendor_id  = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    }

    $sql = "SELECT * FROM {$table_name} WHERE zone_id=$zone_id AND vendor_id=$vendor_id";

    $results = $wpdb->get_results( $sql );

    $locations = array();

    if ( $results ) {
        foreach ( $results as $key => $result ) {
            $locations[] = array(
                'code' => $result->location_code,
                'type'  => $result->location_type
            );
        }
    }

    return $locations;
  }

  /**
   * Save zone location for seller
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function save_location( $location, $zone_id, $user_id = 0  ) {
    global $wpdb;

    // Setup arrays for Actual Values, and Placeholders
    $values        = array();
    $place_holders = array();
    if( $user_id ) {
      $vendor_id = $user_id;
    } else {
      $vendor_id     = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    }
    $table_name    = "{$wpdb->prefix}wcfm_marketplace_shipping_zone_locations";

    $query = "INSERT INTO {$table_name} (vendor_id, zone_id, location_code, location_type) VALUES ";

    if ( ! empty( $location ) ) {
        foreach( $location as $key => $value ) {
            array_push( $values, $vendor_id, $zone_id, $value['code'], $value['type'] );
            $place_holders[] = "('%d', '%d', '%s', '%s')";
        }

        $query .= implode(', ', $place_holders);

        $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE zone_id=%d AND vendor_id=%d", $zone_id, $vendor_id ) );

        if ( $wpdb->query( $wpdb->prepare( "$query ", $values ) ) ) {
            return true;
        }
    } else {
        if( $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE zone_id=%d AND vendor_id=%d", $zone_id, $vendor_id ) ) ) {
            return true;
        }
    }

    return false;
  }

  /**
   * get Shipping method label
   *
   * @since 1.0.0
   *
   * @return void
   */
  public static function get_method_label( $method_id ) {
    $vendor_shipping_methods = wcfmmp_get_shipping_methods();
    if ( 'flat_rate' == $method_id ) {
        return __( $vendor_shipping_methods['flat_rate'], 'wc-multivendor-marketplace' );
    } elseif ( 'local_pickup' == $method_id ) {
        return __( $vendor_shipping_methods['local_pickup'], 'wc-multivendor-marketplace' );
    } elseif( 'free_shipping' == $method_id ) {
        return __( $vendor_shipping_methods['free_shipping'], 'wc-multivendor-marketplace' );
    } else if( isset( $vendor_shipping_methods[$method_id] ) ) {
        return __( $vendor_shipping_methods[$method_id], 'wc-multivendor-marketplace' );
    } else {
    	return '';
    }
  }

}