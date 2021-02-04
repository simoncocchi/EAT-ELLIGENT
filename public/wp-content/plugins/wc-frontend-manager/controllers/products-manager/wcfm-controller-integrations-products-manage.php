<?php
/**
 * WCFM plugin controllers
 *
 * WCFM Integrations Products Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   2.2.2
 */

class WCFM_Integrations_Products_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		// WC Paid Listing Support - 2.3.4
    if( $wcfm_allow_job_package = apply_filters( 'wcfm_is_allow_job_package', true ) ) {
			if ( WCFM_Dependencies::wcfm_wc_paid_listing_active_check() ) {
				// WC Paid Listing Product Meta Data Save
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wcpl_product_meta_save' ), 50, 2 );
			}
		}
		
		// WC Rental & Booking Support - 2.3.8
    if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_rental_active_check() ) {
				// WC Rental Product Meta Data Save
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wcrental_product_meta_save' ), 80, 2 );
			}
		}
		
		// YITH AuctionsFree Support - 3.0.4
    if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFM_Dependencies::wcfm_yith_auction_free_active_check() ) {
				// YITH Auction Product Meta Data Save
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_yith_auction_free_product_meta_save' ), 70, 2 );
			}
		}
		
		// Geo my WP Support - 3.2.4
    if( $wcfm_allow_geo_my_wp = apply_filters( 'wcfm_is_allow_geo_my_wp', true ) ) {
			if( WCFM_Dependencies::wcfm_geo_my_wp_plugin_active_check() ) {
				// GEO my WP Product Location DataSave
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_geomywp_product_meta_save' ), 100, 2 );
			}
		}
		
		// Woocommerce Germanized Support - 3.3.2
    if( $wcfm_allow_woocommerce_germanized = apply_filters( 'wcfm_is_allow_woocommerce_germanized', true ) ) {
			if( WCFM_Dependencies::wcfm_woocommerce_germanized_plugin_active_check() ) {
				// Woocommerce Germanized Product Pricing & Shipping DataSave
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_woocommerce_germanized_product_meta_save' ), 100, 2 );
				
				// Woocommerce Germanized Variation Pricing & Shipping DataSave
				add_action( 'wcfm_product_variation_data_factory', array( &$this, 'wcfm_woocommerce_germanized_variations_product_meta_save' ), 100, 5 );
			}
		}
		
		// Woocommerce PDF Voucher Support - 3.4.7
    if( apply_filters( 'wcfm_is_allow_wc_product_voucher', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_product_voucher_plugin_active_check() ) {
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wc_product_voucher_product_meta_save' ), 100, 2 );
			}
		}
		
		// Woocommerce PDF Voucher Support - 4.0.0
    if( apply_filters( 'wcfm_is_allow_wc_sku_generator', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_sku_generator_plugin_active_check() ) {
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wc_sku_generator_product_meta_save' ), 100, 2 );
			}
		}
		
		// Woocommerce Epeken Support - 4.1.0
    if( apply_filters( 'wcfm_is_allow_epeken', true ) ) {
			if( WCFM_Dependencies::wcfm_epeken_plugin_active_check() ) {
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wcepeken_product_meta_save' ), 150, 2 );
			}
		}
		
		// WooCommerce Product Schedular - 6.1.4
    if( apply_filters( 'wcfm_is_allow_wc_product_scheduler', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_product_scheduler_active_check() ) {
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wc_product_scheduler_product_meta_save' ), 160, 2 );
			}
		}
		
		// WooCommerce Tiered Table Price - 6.3.4
    if( apply_filters( 'wcfm_is_allow_wc_tiered_price_table', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_tiered_price_table_active_check() || WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wc_tiered_price_table_product_meta_save' ), 170, 2 );
				
				add_action( 'wcfm_product_variation_data_factory', array( &$this, 'wcfm_wc_tiered_price_table_variations_product_meta_save' ), 110, 5 );
			}
		}
		
		// Post Expirator - 6.1.4
    if( apply_filters( 'wcfm_is_allow_post_expirator', true ) ) {
			if( WCFM_Dependencies::wcfm_post_expirator_plugin_active_check() ) {
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_post_expirator_product_meta_save' ), 180, 2 );
			}
		}
		
		// Woocommerce German Market Support - 3.3.2
    if( apply_filters( 'wcfm_is_allow_wc_german_market', true ) ) {
			if( WCFM_Dependencies::wcfm_wc_german_market_plugin_active_check() ) {
				// Woocommerce Germanized Product Pricing & Shipping DataSave
				add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_wc_german_market_product_meta_save' ), 100, 2 );
				
				// Woocommerce Germanized Variation Pricing & Shipping DataSave
				add_action( 'wcfm_product_variation_data_factory', array( &$this, 'wcfm_wc_german_market_variations_product_meta_save' ), 100, 5 );
			}
		}
		
		// Third Party Product Meta Data Save
    add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_thirdparty_products_manage_meta_save' ), 100, 2 );
	}
	
	/**
	 * WC Paid Listing Product Meta data save
	 */
	function wcfm_wcpl_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if( $wcfm_products_manage_form_data['product_type'] == 'job_package' ) {
	
			$job_package_fields = array(
				'_job_listing_package_subscription_type',
				'_job_listing_limit',
				'_job_listing_duration'
			);
	
			foreach ( $job_package_fields as $field_name ) {
				if ( isset( $wcfm_products_manage_form_data[ $field_name ] ) ) {
					update_post_meta( $new_product_id, $field_name, stripslashes( $wcfm_products_manage_form_data[ $field_name ] ) );
				}
			}
			
			// Featured
			$is_featured = ( isset( $wcfm_products_manage_form_data['_job_listing_featured'] ) ) ? 'yes' : 'no';
	
			update_post_meta( $new_product_id, '_job_listing_featured', $is_featured );
		}
	}
	
	/**
	 * WC Rental Product Meta data save
	 */
	function wcfm_wcrental_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if( $wcfm_products_manage_form_data['product_type'] == 'redq_rental' ) {
			$rental_fields = array(
				'pricing_type',
				'hourly_price',
				'general_price',
				'redq_rental_availability'
			);
	
			foreach ( $rental_fields as $field_name ) {
				if ( isset( $wcfm_products_manage_form_data[ $field_name ] ) ) {
					$rental_fields[ str_replace( 'redq_', '', $field_name ) ] = $wcfm_products_manage_form_data[ $field_name ];
					update_post_meta( $new_product_id, $field_name, $wcfm_products_manage_form_data[ $field_name ] );
				}
			}
			
			update_post_meta( $new_product_id, '_price', $wcfm_products_manage_form_data[ 'general_price' ] );
			update_post_meta( $new_product_id, 'redq_all_data', $rental_fields );
		}
	}
	
	/**
	 * WC Rental Product Meta data save
	 */
	function wcfm_yith_auction_free_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if( $wcfm_products_manage_form_data['product_type'] == 'auction' ) {
			
			$auction_product = wc_get_product($new_product_id);
			
			if (isset($wcfm_products_manage_form_data['_yith_auction_for'])) {
				$my_date = $wcfm_products_manage_form_data['_yith_auction_for'];
				$gmt_date = get_gmt_from_date($my_date);
				yit_save_prop($auction_product, '_yith_auction_for', strtotime($gmt_date),true);
			}
			if (isset($wcfm_products_manage_form_data['_yith_auction_to'])) {
				$my_date = $wcfm_products_manage_form_data['_yith_auction_to'];
				$gmt_date = get_gmt_from_date($my_date);
				yit_save_prop($auction_product, '_yith_auction_to', strtotime($gmt_date),true);
			}
			
			// Stock Update
			update_post_meta( $new_product_id, '_manage_stock', 'yes' );
			update_post_meta( $new_product_id, '_stock_status', 'instock' );
			update_post_meta( $new_product_id, '_stock', 1 );
			
			//Prevent issues with orderby in shop loop
			$bids = YITH_Auctions()->bids;
			$exist_auctions = $bids->get_max_bid($new_product_id);
			if (!$exist_auctions) {
				yit_save_prop($auction_product, '_yith_auction_start_price',0);
				yit_save_prop($auction_product, '_price',0);
			}
		}
	}
	
	/**
	 * GEO my WP Product Meta data save
	 */
	function wcfm_geomywp_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if( !isset( $wcfm_products_manage_form_data['gmw_location_form'] ) || empty( $wcfm_products_manage_form_data['gmw_location_form'] ) ) {
			return;
		}
		
		// Submitted location values
		$location = $wcfm_products_manage_form_data['gmw_location_form'];

		// abort if no location found
		if ( empty( $location['latitude'] ) || empty( $location['longitude'] ) ) {
			return;
		}
		
		$location['object_id'] = $new_product_id;

		// location meta
		$location_meta = ! empty( $location['location_meta'] ) ? $location['location_meta'] : array();

		// map icon if exists
		$location['map_icon'] = ! empty( $location['map_icon'] ) ? $location['map_icon'] : '_default.png';
		
		$location_args = array(
			'object_type'		=> $location['object_type'],
			'object_id'			=> (int) $location['object_id'],
			'user_id'			=> (int) $location['user_id'],
			'parent'			=> 0,
			'status'        	=> 1,
			'featured'			=> 0,
			'title'				=> ! empty( $location['title'] ) ? $location['title'] : '',
			'latitude'          => $location['latitude'],
			'longitude'         => $location['longitude'],
			'street_number'     => $location['street_number'],
			'street_name'       => $location['street_name'],
			'street' 			=> $location['street'],
			'premise'       	=> $location['premise'],
			'neighborhood'  	=> $location['neighborhood'],
			'city'              => $location['city'],
			'county'            => $location['county'],
			'region_name'   	=> $location['region_name'],
			'region_code'   	=> $location['region_code'],
			'postcode'      	=> $location['postcode'],
			'country_name'  	=> $location['country_name'],
			'country_code'  	=> $location['country_code'],
			'address'           => $location['address'],
			'formatted_address' => $location['formatted_address'],
			'place_id'			=> $location['place_id'],
			'map_icon'			=> $location['map_icon'],
		);

		// filter location args before updating location
		$location_args = apply_filters( 'gmw_lf_location_args_before_location_updated', $location_args, $location, $wcfm_products_manage_form_data );
	  $location_args = apply_filters( 'gmw_lf_'.$location['object_type'].'_location_args_before_location_updated', $location_args, $location, $wcfm_products_manage_form_data );

	    // run custom functions before updating location
		do_action( 'gmw_lf_before_location_updated', $location, $location_args, $wcfm_products_manage_form_data );
	  do_action( 'gmw_lf_before_'.$location['object_type'].'_location_updated', $location, $location_args, $wcfm_products_manage_form_data );

		// save location
		$location['ID'] = gmw_update_location_data( $location_args );

		// filter location meta before updating
		$location_meta = apply_filters( 'gmw_lf_location_meta_before_location_updated', $location_meta, $location, $wcfm_products_manage_form_data );
	  $location_meta = apply_filters( 'gmw_lf_'.$location['object_type'].'_location_meta_before_location_updated', $location_meta, $location, $wcfm_products_manage_form_data );

		// save location meta
		if ( ! empty( $location_meta ) ) {

			foreach ( $location_meta as $meta_key => $meta_value ) {

				if ( ! is_array( $meta_value ) ) {
					$meta_value = trim( $meta_value );
				}

				if ( empty( $meta_value ) || ( is_array( $meta_value ) && ! array_filter( $meta_value ) ) ) {
					gmw_delete_location_meta( $location['ID'], $meta_key );
				} else {
					gmw_update_location_meta( $location['ID'], $meta_key, $meta_value );
				}
			}
		}

		//do something after location updated
		do_action( 'gmw_lf_after_location_updated', $location, $wcfm_products_manage_form_data );
	  do_action( 'gmw_lf_after_'.$location['object_type'].'_location_updated', $location, $wcfm_products_manage_form_data );
	}
	
	public function get_wcfm_woocommerce_germanized_default_product_data( $product ) {
		$fields = array(
			'product-type'           => $product->get_type(),
			'sale_date_from' => '',
			'sale_date_upto'   => '',
			'_is_on_sale'            => $product->is_on_sale(),
			'sale_price'            => $product->get_sale_price(),
		);

		if ( is_a( $fields['sale_date_from'], 'WC_DateTime' ) ) {
			$fields['sale_date_from'] = $fields['_sale_price_dates_from']->date_i18n();
		}

		if ( is_a( $fields['sale_date_upto'], 'WC_DateTime' ) ) {
			$fields['sale_date_upto'] = $fields['_sale_price_dates_to']->date_i18n();
		}

		return $fields;
	}
	
	/**
	 * Woocommerce Germanized Product Meta data save
	 */
	function wcfm_woocommerce_germanized_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		$is_variation = false;
		$product = wc_get_product( $new_product_id );
		
		$data = apply_filters( 'woocommerce_gzd_product_saveable_data', $wcfm_products_manage_form_data, $product );

		$data = wp_parse_args( $data, array(
			'save'    => true,
			'is_rest' => false,
		) );
		
		$data = array_replace_recursive( $this->get_wcfm_woocommerce_germanized_default_product_data( $product ), $data );

		$unit_data         = $data;
		$unit_data['save'] = false;
		
		
		$gzd_product       = wc_gzd_get_product( $product );
		$product_type = ( ! isset( $wcfm_products_manage_form_data['product_type'] ) || empty( $wcfm_products_manage_form_data['product_type'] ) ) ? 'simple' : sanitize_title( stripslashes( $wcfm_products_manage_form_data['product_type'] ) );
		
		
		if ( isset( $data['_unit'] ) ) {
			if ( empty( $data['_unit'] ) || in_array( $data['_unit'], array( 'none', '-1' ) ) ) {
				$gzd_product->set_unit( '' );
			} else {
				$gzd_product->set_unit( wc_clean( $data['_unit'] ) );
			}
		}

		if ( isset( $data['_unit_base'] ) ) {
			$gzd_product->set_unit_base( $data['_unit_base'] );
		}

		if ( isset( $data['_unit_product'] ) ) {
			$gzd_product->set_unit_product( $data['_unit_product'] );
		}

		$gzd_product->set_unit_price_auto( ( isset( $data['_unit_price_auto'] ) ) ? 'yes' : 'no' );

		if ( isset( $data['_unit_price_regular'] ) ) {
			$gzd_product->set_unit_price_regular( $data['_unit_price_regular'] );
			$gzd_product->set_unit_price( $data['_unit_price_regular'] );
		}

		if ( isset( $data['_unit_price_sale'] ) ) {
			// Unset unit price sale if no product sale price has been defined
			if ( ! isset( $data['sale_price'] ) || $data['sale_price'] === '' ) {
				$data['_unit_price_sale'] = '';
			}

			$gzd_product->set_unit_price_sale( $data['_unit_price_sale'] );
		}

		// Ignore variable data
		if ( in_array( $product_type, array( 'variable', 'grouped' ) ) && ! $is_variation ) {

			$gzd_product->set_unit_price_regular( '' );
			$gzd_product->set_unit_price_sale( '' );
			$gzd_product->set_unit_price( '' );
			$gzd_product->set_unit_price_auto( false );

		} else {

			$date_from  = isset( $data['sale_date_from'] ) ? wc_clean( $data['sale_date_from'] ) : '';
			$date_to    = isset( $data['sale_date_upto'] ) ? wc_clean( $data['sale_date_upto'] ) : '';
			$is_on_sale = isset( $data['_is_on_sale'] ) ? $data['_is_on_sale'] : null;

			// Update price if on sale
			if ( isset( $data['_unit_price_sale'] ) ) {
				if ( ! is_null( $is_on_sale ) ) {
					if ( $is_on_sale ) {
						$gzd_product->set_unit_price( $data['_unit_price_sale'] );
					} else {
						$gzd_product->set_unit_price( $data['_unit_price_regular'] );
					}
				} else {
					if ( '' !== $data['_unit_price_sale'] && '' == $date_to && '' == $date_from ) {
						$gzd_product->set_unit_price( $data['_unit_price_sale'] );
					} else {
						$gzd_product->set_unit_price( $data['_unit_price_regular'] );
					}

					if ( '' !== $data['_unit_price_sale'] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
						$gzd_product->set_unit_price( $data['_unit_price_sale'] );
					}

					if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
						$gzd_product->set_unit_price( $data['_unit_price_regular'] );
					}
				}
			}
		}

		if ( $data['save'] ) {
			$product->save();
		}
		
		
		$sale_price_labels = array( '_sale_price_label', '_sale_price_regular_label' );

		foreach ( $sale_price_labels as $label ) {
			if ( isset( $data[ $label ] ) ) {
				$setter = "set{$label}";

				if ( is_callable( array( $gzd_product, $setter ) ) ) {
					if ( empty( $data[ $label ] ) || in_array( $data[ $label ], array( 'none', '-1' ) ) ) {
						$gzd_product->$setter( '' );
					} else {
						$gzd_product->$setter( wc_clean( $data[ $label ] ) );
					}
				}
			}
		}

		if ( isset( $data['_mini_desc'] ) ) {
			$gzd_product->set_mini_desc( $data['_mini_desc'] === '' ? '' : wc_gzd_sanitize_html_text_field( $data['_mini_desc'] ) );
		}

		if ( isset( $data['_min_age'] ) && array_key_exists( (int) $data['_min_age'], wc_gzd_get_age_verification_min_ages() ) ) {
			$gzd_product->set_min_age( absint( $data['_min_age'] ) );
		} else {
			$gzd_product->set_min_age( '' );
		}

		if ( isset( $data['delivery_time'] ) && ! empty( $data['delivery_time'] ) ) {
			$product->update_meta_data( '_product_delivery_time', $data['delivery_time'] );
		} else {
			$product->update_meta_data( '_delete_product_delivery_time', true );
		}

		// Free shipping
		$gzd_product->set_free_shipping( isset( $data['_free_shipping'] ) ? 'yes' : 'no' );

		// Is a service?
		$gzd_product->set_service( isset( $data['_service'] ) ? 'yes' : 'no' );

		// Applies to differential taxation?
		$gzd_product->set_differential_taxation( isset( $data['_differential_taxation'] ) ? 'yes' : 'no' );

		if ( $gzd_product->is_differential_taxed() ) {
			/**
			 * Filter the tax status of a differential taxed product.
			 *
			 * @param string     $tax_status The tax status, e.g. none or shipping.
             * @param WC_Product $product The product instance.
			 *
			 * @since 3.0.7
			 */
		    $tax_status_diff = apply_filters( 'woocommerce_gzd_product_differential_taxed_tax_status', 'none', $product );

			$product->set_tax_status( $tax_status_diff );
		}

		// Ignore variable data
		if ( in_array( $product_type, array( 'variable', 'grouped' ) ) && ! $is_variation ) {
			$gzd_product->set_mini_desc( '' );
		}
		
		if ( isset( $data['_ts_gtin'] ) ) {
			$product = wc_ts_set_crud_data( $product, '_ts_gtin', wc_clean( $data['_ts_gtin'] ) );
		}

		if ( isset( $data['_ts_mpn'] ) ) {
			$product = wc_ts_set_crud_data( $product, '_ts_mpn', wc_clean( $data['_ts_mpn'] ) );
		}

		if ( $data['save'] ) {
			$product->save();
		}
		
	}
	
	/**
	 * Woocommerce Germanized Variations Meta data save
	 */
	function wcfm_woocommerce_germanized_variations_product_meta_save( $wcfm_variation_data, $new_product_id, $variation_id, $variations, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		$is_variation = true;
		$product = wc_get_product( $variation_id );
		
		$data = apply_filters( 'woocommerce_gzd_product_saveable_data', $variations, $product );

		$data = wp_parse_args( $data, array(
			'save'    => true,
			'is_rest' => false,
		) );
		
		$data = array_replace_recursive( $this->get_wcfm_woocommerce_germanized_default_product_data( $product ), $data );

		$unit_data         = $data;
		$unit_data['save'] = false;
		
		
		$gzd_product       = wc_gzd_get_product( $product );
		$product_type = ( ! isset( $wcfm_products_manage_form_data['product_type'] ) || empty( $wcfm_products_manage_form_data['product_type'] ) ) ? 'simple' : sanitize_title( stripslashes( $wcfm_products_manage_form_data['product_type'] ) );
		
		
		if ( isset( $data['_unit'] ) ) {
			if ( empty( $data['_unit'] ) || in_array( $data['_unit'], array( 'none', '-1' ) ) ) {
				$gzd_product->set_unit( '' );
			} else {
				$gzd_product->set_unit( wc_clean( $data['_unit'] ) );
			}
		}

		if ( isset( $data['_unit_base'] ) ) {
			$gzd_product->set_unit_base( $data['_unit_base'] );
		}

		if ( isset( $data['_unit_product'] ) ) {
			$gzd_product->set_unit_product( $data['_unit_product'] );
		}

		$gzd_product->set_unit_price_auto( ( isset( $data['_unit_price_auto'] ) ) ? 'yes' : 'no' );

		if ( isset( $data['_unit_price_regular'] ) ) {
			$gzd_product->set_unit_price_regular( $data['_unit_price_regular'] );
			$gzd_product->set_unit_price( $data['_unit_price_regular'] );
		}

		if ( isset( $data['_unit_price_sale'] ) ) {
			// Unset unit price sale if no product sale price has been defined
			if ( ! isset( $data['sale_price'] ) || $data['sale_price'] === '' ) {
				$data['_unit_price_sale'] = '';
			}

			$gzd_product->set_unit_price_sale( $data['_unit_price_sale'] );
		}

		// Ignore variable data
		if ( in_array( $product_type, array( 'variable', 'grouped' ) ) && ! $is_variation ) {

			$gzd_product->set_unit_price_regular( '' );
			$gzd_product->set_unit_price_sale( '' );
			$gzd_product->set_unit_price( '' );
			$gzd_product->set_unit_price_auto( false );

		} else {

			$date_from  = isset( $data['sale_date_from'] ) ? wc_clean( $data['sale_date_from'] ) : '';
			$date_to    = isset( $data['sale_date_upto'] ) ? wc_clean( $data['sale_date_upto'] ) : '';
			$is_on_sale = isset( $data['_is_on_sale'] ) ? $data['_is_on_sale'] : null;

			// Update price if on sale
			if ( isset( $data['_unit_price_sale'] ) ) {
				if ( ! is_null( $is_on_sale ) ) {
					if ( $is_on_sale ) {
						$gzd_product->set_unit_price( $data['_unit_price_sale'] );
					} else {
						$gzd_product->set_unit_price( $data['_unit_price_regular'] );
					}
				} else {
					if ( '' !== $data['_unit_price_sale'] && '' == $date_to && '' == $date_from ) {
						$gzd_product->set_unit_price( $data['_unit_price_sale'] );
					} else {
						$gzd_product->set_unit_price( $data['_unit_price_regular'] );
					}

					if ( '' !== $data['_unit_price_sale'] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
						$gzd_product->set_unit_price( $data['_unit_price_sale'] );
					}

					if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
						$gzd_product->set_unit_price( $data['_unit_price_regular'] );
					}
				}
			}
		}

		if ( $data['save'] ) {
			$product->save();
		}
		
		
		$sale_price_labels = array( '_sale_price_label', '_sale_price_regular_label' );

		foreach ( $sale_price_labels as $label ) {
			if ( isset( $data[ $label ] ) ) {
				$setter = "set{$label}";

				if ( is_callable( array( $gzd_product, $setter ) ) ) {
					if ( empty( $data[ $label ] ) || in_array( $data[ $label ], array( 'none', '-1' ) ) ) {
						$gzd_product->$setter( '' );
					} else {
						$gzd_product->$setter( wc_clean( $data[ $label ] ) );
					}
				}
			}
		}

		if ( isset( $data['_mini_desc'] ) ) {
			$gzd_product->set_mini_desc( $data['_mini_desc'] === '' ? '' : wc_gzd_sanitize_html_text_field( $data['_mini_desc'] ) );
		}

		if ( isset( $data['_min_age'] ) && array_key_exists( (int) $data['_min_age'], wc_gzd_get_age_verification_min_ages() ) ) {
			$gzd_product->set_min_age( absint( $data['_min_age'] ) );
		} else {
			$gzd_product->set_min_age( '' );
		}

		if ( isset( $data['delivery_time'] ) && ! empty( $data['delivery_time'] ) ) {
			$product->update_meta_data( '_product_delivery_time', $data['delivery_time'] );
		} else {
			$product->update_meta_data( '_delete_product_delivery_time', true );
		}

		// Free shipping
		$gzd_product->set_free_shipping( isset( $data['_free_shipping'] ) ? 'yes' : 'no' );

		// Is a service?
		$gzd_product->set_service( isset( $data['_service'] ) ? 'yes' : 'no' );

		// Applies to differential taxation?
		$gzd_product->set_differential_taxation( isset( $data['_differential_taxation'] ) ? 'yes' : 'no' );

		if ( $gzd_product->is_differential_taxed() ) {
			/**
			 * Filter the tax status of a differential taxed product.
			 *
			 * @param string     $tax_status The tax status, e.g. none or shipping.
             * @param WC_Product $product The product instance.
			 *
			 * @since 3.0.7
			 */
		    $tax_status_diff = apply_filters( 'woocommerce_gzd_product_differential_taxed_tax_status', 'none', $product );

			$product->set_tax_status( $tax_status_diff );
		}

		// Ignore variable data
		if ( in_array( $product_type, array( 'variable', 'grouped' ) ) && ! $is_variation ) {
			$gzd_product->set_mini_desc( '' );
		}
		
		if ( isset( $data['_ts_gtin'] ) ) {
			$product = wc_ts_set_crud_data( $product, '_ts_gtin', wc_clean( $data['_ts_gtin'] ) );
		}

		if ( isset( $data['_ts_mpn'] ) ) {
			$product = wc_ts_set_crud_data( $product, '_ts_mpn', wc_clean( $data['_ts_mpn'] ) );
		}

		if ( $data['save'] ) {
			$product->save();
		}
		
		return $wcfm_variation_data;
	}
	
	/**
	 * WooCommerce PDF Voucher Meta Data Save
	 */
	function wcfm_wc_product_voucher_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if(isset($wcfm_products_manage_form_data['_has_voucher'])) {
			update_post_meta( $new_product_id, '_has_voucher', 'yes' );
			update_post_meta( $new_product_id, '_voucher_template_id', $wcfm_products_manage_form_data['_voucher_template_id'] );
		}
	}
	
	/**
	 * WC SKU Generator Meta Data Save
	 */
	function wcfm_wc_sku_generator_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if ( is_numeric( $new_product_id ) ) {
			$product = wc_get_product( absint( $new_product_id ) );
		}

		// Generate the SKU for simple / external / variable parent products
		switch( get_option( 'wc_sku_generator_simple' ) ) {

			case 'slugs':
				$product_sku = urldecode( get_post( $product->get_id() )->post_name );
			break;

			case 'ids':
				$product_sku = $product->get_id();
			break;

			// use the original product SKU if we're not generating it
			default:
				$product_sku = $product->get_sku();
		}
		$product_sku = apply_filters( 'wc_sku_generator_sku', $product_sku, $product );

		// Only generate / save variation SKUs when we should
		if ( $product->is_type( 'variable' ) && 'never' !== get_option( 'wc_sku_generator_variation' ) ) {
			
			$args = apply_filters( 'wc_sku_generator_variation_query_args', array(
				'post_parent' => $new_product_id,
				'post_type'   => 'product_variation',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'fields'      => 'ids',
				'post_status' => array( 'publish', 'private' ),
				'numberposts' => -1,
			) );
	
			$variations = get_posts( $args );

			foreach ( $variations as $variation_id ) {
				
				$variation  = wc_get_product( $variation_id );
				$parent_sku = $product_sku ? $product_sku : $product->get_sku();
		
				if ( $variation->is_type( 'variation' ) && ! empty( $product_sku ) ) {
		
					$variation_data = $product->get_available_variation( $variation );
					$variation_sku  = '';
					
					if ( 'slugs' === get_option( 'wc_sku_generator_variation' ) ) {

						// replace spaces in attributes depending on settings
						switch ( get_option( 'wc_sku_generator_attribute_spaces' ) ) {
			
							case 'underscore':
								$variation_data['attributes'] = str_replace( ' ', '_', $variation_data['attributes'] );
							break;
			
							case 'dash':
								$variation_data['attributes'] = str_replace( ' ', '-', $variation_data['attributes'] );
							break;
			
							case 'none':
								$variation_data['attributes'] = str_replace( ' ', '', $variation_data['attributes'] );
							break;
			
						}
			
						if ( apply_filters( 'wc_sku_generator_force_attribute_sorting', false ) ) {
							ksort( $variation_data['attributes'] );
						}
			
						$separator = apply_filters( 'wc_sku_generator_attribute_separator', apply_filters( 'wc_sku_generator_sku_separator', '-' ) );
			
						$variation_sku = implode( $variation_data['attributes'], $separator );
						$variation_sku = str_replace( 'attribute_', '', $variation_sku );
					}
			
					if ( 'ids' === get_option( 'wc_sku_generator_variation') ) {
						$variation_sku = $variation_data['variation_id'];
					}
			
					$variation_sku = apply_filters( 'wc_sku_generator_variation_sku', $variation_sku, $variation_data );
					
					$sku           = $parent_sku . apply_filters( 'wc_sku_generator_sku_separator', '-' ) . $variation_sku;
		
					$sku           = apply_filters( 'wc_sku_generator_variation_sku_format', $sku, $parent_sku, $variation_sku );
		
					update_post_meta( $variation_id, '_sku', $sku );
				}
			}
		}

		// Save the SKU for simple / external / parent products if we should
		if ( 'never' !== get_option( 'wc_sku_generator_simple' ) )  {
			update_post_meta( $product->get_id(), '_sku', $product_sku );
		}
	}
	
	/**
	 * WC Epeken Product Manage data save
	 */
	function wcfm_wcepeken_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if( apply_filters( 'wcfm_is_allow_epeken', true ) ) {
			$product_origin_selected = isset($wcfm_products_manage_form_data['epeken_valid_origin_option']) ? $wcfm_products_manage_form_data['epeken_valid_origin_option'] : '';
			$product_origin = get_post_meta($new_product_id,'product_origin',true);
			$data_asal_kota = get_option('epeken_data_asal_kota');
			if (empty($product_origin) && !empty($data_asal_kota)) {
				update_post_meta( $new_product_id, 'product_origin', $data_asal_kota);
			} else {
				update_post_meta( $new_product_id, 'product_origin', $product_origin_selected);
			}
			$product_insurance_mandatory = isset($wcfm_products_manage_form_data['epeken_product_insurance_mandatory']) ? $wcfm_products_manage_form_data['epeken_product_insurance_mandatory'] : '';
			update_post_meta( $new_product_id, 'product_insurance_mandatory', $product_insurance_mandatory);

			$product_wood_pack_mandatory = isset($wcfm_products_manage_form_data['epeken_product_wood_pack_mandatory']) ? $wcfm_products_manage_form_data['epeken_product_wood_pack_mandatory'] : '';
			update_post_meta( $new_product_id, 'product_wood_pack_mandatory', $product_wood_pack_mandatory);

			$product_free_ongkir = isset($wcfm_products_manage_form_data['epeken_product_free_ongkir']) ? $wcfm_products_manage_form_data['epeken_product_free_ongkir'] : '';
			update_post_meta( $new_product_id, 'product_free_ongkir', $product_free_ongkir);
		}
	}
	
	/**
	 * Product Manager WC Product Scheduler Product Meta data save
	 */
	function wcfm_wc_product_scheduler_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		$wpas_error=false;
		$wpas_st_hh = 00;
		$wpas_st_mn = 00;
		$wpas_end_hh = 00;
		$wpas_end_mn = 00;
		$wpas_status = sanitize_text_field($wcfm_products_manage_form_data['wpas_select_status']);
		$countdown = sanitize_text_field($wcfm_products_manage_form_data['wpas_enable_countdown']);
		
		$wpas_st_date = sanitize_text_field($wcfm_products_manage_form_data['wpas_st_date']);
		if(!empty($wcfm_products_manage_form_data['wpas_st_hh'])) $wpas_st_hh = sanitize_text_field($wcfm_products_manage_form_data['wpas_st_hh']);
		if( absint($wpas_st_hh) > 12 ) $wpas_st_hh = 11;
		if(!empty($wcfm_products_manage_form_data['wpas_st_mn'])) $wpas_st_mn = sanitize_text_field($wcfm_products_manage_form_data['wpas_st_mn']);
		if( absint($wpas_st_mn) > 60 ) $wpas_st_mn = 59;
		
		$wpas_end_date=sanitize_text_field($wcfm_products_manage_form_data['wpas_end_date']);
		if(!empty($wcfm_products_manage_form_data['wpas_end_hh'])) $wpas_end_hh = sanitize_text_field($wcfm_products_manage_form_data['wpas_end_hh']);
		if( absint($wpas_end_hh) > 12 ) $wpas_end_hh = 11;
		if(!empty($wcfm_products_manage_form_data['wpas_end_mn'])) $wpas_end_mn = sanitize_text_field($wcfm_products_manage_form_data['wpas_end_mn']);
		if( absint($wpas_end_mn) > 60 ) $wpas_end_mn = 59;
		
		$wpas_start_schedule_hook = "wpas_start_shedule_sale";
		$wpas_end_schedule_hook = "wpas_end_shedule_sale";	
		//Y-m-d H:i:s
		$wpas_st_time = strtotime($wpas_st_date." ".$wpas_st_hh.":".$wpas_st_mn.":00"); 
		
		//echo "start time".$wpas_st_time;
		$wpas_end_time = strtotime($wpas_end_date." ".$wpas_end_hh.":".$wpas_end_mn.":00");
		if($wpas_status == 1) {		
			wp_clear_scheduled_hook( $wpas_start_schedule_hook, array($new_product_id) );
			wp_clear_scheduled_hook( $wpas_end_schedule_hook, array($new_product_id) );	
			wp_schedule_single_event( $wpas_st_time, $wpas_start_schedule_hook, array($new_product_id) );
			wp_schedule_single_event( $wpas_end_time, $wpas_end_schedule_hook, array($new_product_id) );
		}
		// Save Data
		
		if (!empty($wpas_st_date) && !empty($wpas_end_date)) {
			
			update_post_meta( $new_product_id, 'wpas_schedule_sale_status', $wpas_status );   
			update_post_meta( $new_product_id, 'wpas_schedule_sale_st_time', $wpas_st_time );   
			update_post_meta( $new_product_id, 'wpas_schedule_sale_end_time', $wpas_end_time );   
			update_post_meta( $new_product_id, 'wpas_schedule_sale_countdown', $countdown );   
			
			if($wpas_st_time > time()) {
				update_post_meta( $new_product_id, 'wpas_schedule_sale_mode', 0 );   	
			}
			
		}
	}
	
	/**
	 * Product Manager WC Tiered Price Table Product Meta data save
	 */
	function wcfm_wc_tiered_price_table_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		$fixed_price_rules = array();
		$percent_price_rules = array();
		
		if ( isset( $wcfm_products_manage_form_data['tiered_price_rules_type'] ) ) {
			update_post_meta( $new_product_id, '_tiered_price_rules_type', $wcfm_products_manage_form_data['tiered_price_rules_type'] );
		}
		
		if ( isset( $wcfm_products_manage_form_data['tiered_fixed_price_rules'] ) ) {
			$price_rules = $wcfm_products_manage_form_data['tiered_fixed_price_rules'];
			
			if( !empty( $price_rules ) ) {
				foreach( $price_rules as $price_rule ) {
					if ( !empty($price_rule['quantity']) && !empty($price_rule['price']) && !key_exists( $price_rule['quantity'], $fixed_price_rules ) ) {
						$fixed_price_rules[$price_rule['quantity']] = wc_format_decimal( $price_rule['price'] );
					}
				}
			}
			update_post_meta( $new_product_id, '_fixed_price_rules', $fixed_price_rules );
		}
		
		if( WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
			if ( isset( $wcfm_products_manage_form_data['tiered_percent_price_rules'] ) ) {
				$price_rules = $wcfm_products_manage_form_data['tiered_percent_price_rules'];
				
				if( !empty( $price_rules ) ) {
					foreach( $price_rules as $price_rule ) {
						if ( !empty($price_rule['quantity']) && !empty($price_rule['discount']) && !key_exists( $price_rule['quantity'], $percent_price_rules ) && ( $price_rule['discount'] < 99 ) ) {
							$percent_price_rules[$price_rule['quantity']] = $price_rule['discount'];
						}
					}
				}
				update_post_meta( $new_product_id, '_percentage_price_rules', $percent_price_rules );
			}
			
			if ( isset( $wcfm_products_manage_form_data['tiered_pricing_minimum'] ) ) {
				update_post_meta( $new_product_id, '_tiered_price_minimum_qty', $wcfm_products_manage_form_data['tiered_pricing_minimum'] );
			}
		}
	}
	
	/**
	 * WC Tiered Price Table Variations Meta data save
	 */
	function wcfm_wc_tiered_price_table_variations_product_meta_save( $wcfm_variation_data, $new_product_id, $variation_id, $variations, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		$fixed_price_rules = array();
		$percent_price_rules = array();
		
		if ( isset( $variations['tiered_fixed_price_rules'] ) ) {
			$price_rules = $variations['tiered_fixed_price_rules'];
			
			if( !empty( $price_rules ) ) {
				foreach( $price_rules as $price_rule ) {
					if ( !empty($price_rule['quantity']) && !empty($price_rule['price']) && !key_exists( $price_rule['quantity'], $fixed_price_rules ) ) {
						$fixed_price_rules[$price_rule['quantity']] = $price_rule['price'];
					}
				}
			}
			update_post_meta( $variation_id, '_fixed_price_rules', $fixed_price_rules );
		}
		
		if( WCFM_Dependencies::wcfm_wc_tiered_price_table_premium_active_check() ) {
			if ( isset( $variations['tiered_percent_price_rules'] ) ) {
				$price_rules = $variations['tiered_percent_price_rules'];
				
				if( !empty( $price_rules ) ) {
					foreach( $price_rules as $price_rule ) {
						if ( !empty($price_rule['quantity']) && !empty($price_rule['discount']) && !key_exists( $price_rule['quantity'], $percent_price_rules ) && ( $price_rule['discount'] < 99 ) ) {
							$percent_price_rules[$price_rule['quantity']] = $price_rule['discount'];
						}
					}
				}
				update_post_meta( $variation_id, '_percentage_price_rules', $percent_price_rules );
			}
			
			if ( isset( $variations['tiered_pricing_minimum'] ) ) {
				update_post_meta( $variation_id, '_tiered_price_minimum_qty', $variations['tiered_pricing_minimum'] );
			}
			
			if ( isset( $variations['tiered_price_rules_type'] ) ) {
				update_post_meta( $variation_id, '_tiered_price_rules_type', $variations['tiered_price_rules_type'] );
			}
		}
		
		return $wcfm_variation_data;
	}
	
	/**
	 * Post Expirator Product Meta Save
	 */
	function wcfm_post_expirator_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if (isset($wcfm_products_manage_form_data['enable-expirationdate'])) {
      $default = get_option('expirationdateDefaultDate',POSTEXPIRATOR_EXPIREDEFAULT);
			if ($default == 'publish') {
				$month 	 = intval($wcfm_products_manage_form_data['mm']);
				$day 	 = intval($wcfm_products_manage_form_data['jj']);
				$year 	 = intval($wcfm_products_manage_form_data['aa']);
				$hour 	 = intval($wcfm_products_manage_form_data['hh']);
				$minute  = intval($wcfm_products_manage_form_data['mn']);
			} else {
				$month	 = intval($wcfm_products_manage_form_data['expirationdate_month']);
				$day 	 = intval($wcfm_products_manage_form_data['expirationdate_day']);
				$year 	 = intval($wcfm_products_manage_form_data['expirationdate_year']);
				$hour 	 = intval($wcfm_products_manage_form_data['expirationdate_hour']);
				$minute  = intval($wcfm_products_manage_form_data['expirationdate_minute']);
			}
			$category = isset($wcfm_products_manage_form_data['expirationdate_category']) ? $wcfm_products_manage_form_data['expirationdate_category'] : 0;
	
			$ts = get_gmt_from_date("$year-$month-$day $hour:$minute:0",'U');
	
			
			$opts = array();

			// Schedule/Update Expiration
			$opts['expireType'] = $wcfm_products_manage_form_data['expirationdate_expiretype'];
			$opts['id'] = $new_product_id;

			/*if ($opts['expireType'] == 'category' || $opts['expireType'] == 'category-add' || $opts['expireType'] == 'category-remove') {
							if (isset($category) && !empty($category)) {
					if (!empty($category)) {
						$opts['category'] = $category;
						$opts['categoryTaxonomy'] = $wcfm_products_manage_form_data['taxonomy-heirarchical'];
					}
				}
			}*/
				
			_scheduleExpiratorEvent( $new_product_id, $ts, $opts);
		} else {
			_unscheduleExpiratorEvent( $new_product_id );
		}
	}
	
	/**
	 * WooCommerce German Marketplace Product Meta Save
	 */
	function wcfm_wc_german_market_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if(isset($wcfm_products_manage_form_data['_lieferzeit'])) {
			update_post_meta( $new_product_id, '_lieferzeit', $wcfm_products_manage_form_data['_lieferzeit'] );
		}
		
		if(isset($wcfm_products_manage_form_data['_alternative_shipping_information'])) {
			update_post_meta( $new_product_id, '_alternative_shipping_information', $wcfm_products_manage_form_data['_alternative_shipping_information'] );
		}
		
		if(isset($wcfm_products_manage_form_data['_suppress_shipping_notice'])) {
			update_post_meta( $new_product_id, '_suppress_shipping_notice', 'on' );
		} else {
			delete_post_meta( $new_product_id, '_suppress_shipping_notice' );
		}
		
		if(isset($wcfm_products_manage_form_data['_sale_label'])) {
			update_post_meta( $new_product_id, '_sale_label', $wcfm_products_manage_form_data['_sale_label'] );
		}
		
		if(isset($wcfm_products_manage_form_data['_gm_gtin'])) {
			update_post_meta( $new_product_id, '_gm_gtin', $wcfm_products_manage_form_data['_gm_gtin'] );
		}
		
		if(isset($wcfm_products_manage_form_data['_unit_regular_price_per_unit'])) {
			update_post_meta( $new_product_id, '_unit_regular_price_per_unit', $wcfm_products_manage_form_data['_unit_regular_price_per_unit'] );
		}
		
		if(isset($wcfm_products_manage_form_data['_auto_ppu_complete_product_quantity'])) {
			update_post_meta( $new_product_id, '_auto_ppu_complete_product_quantity', $wcfm_products_manage_form_data['_auto_ppu_complete_product_quantity'] );
		}
		
		if(isset($wcfm_products_manage_form_data['_unit_regular_price_per_unit_mult'])) {
			update_post_meta( $new_product_id, '_unit_regular_price_per_unit_mult', $wcfm_products_manage_form_data['_unit_regular_price_per_unit_mult'] );
		}
		
		if(isset($wcfm_products_manage_form_data['_age_rating_age'])) {
			update_post_meta( $new_product_id, '_age_rating_age', $wcfm_products_manage_form_data['_age_rating_age'] );
		}
	}
	
	/**
	 * WooCommerce German Market Variation Meta Save
	 */
	function wcfm_wc_german_market_variations_product_meta_save( $wcfm_variation_data, $new_product_id, $variation_id, $variations, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		if ( isset( $variations['_lieferzeit'] ) ) {
			update_post_meta( $variation_id, '_lieferzeit', $variations['_lieferzeit'] );
		}
		
		if ( isset( $variations['variable_used_setting_ppu'] ) ) {
			update_post_meta( $variation_id, 'variable_used_setting_ppu', $variations['variable_used_setting_ppu'] );
		}
		
		if ( isset( $variations['_variable_used_setting_shipping_info'] ) ) {
			update_post_meta( $variation_id, '_variable_used_setting_shipping_info', $variations['_variable_used_setting_shipping_info'] );
		}
		
		if ( isset( $variations['_sale_label'] ) ) {
			update_post_meta( $variation_id, '_sale_label', $variations['_sale_label'] );
		}
		
		if ( isset( $variations['_gm_gtin'] ) ) {
			update_post_meta( $variation_id, '_gm_gtin', $variations['_gm_gtin'] );
		}
		
		if(isset($variations['_age_rating_age'])) {
			//update_post_meta( $variation_id, '_age_rating_age', $variations['_age_rating_age'] );
		}
		
		return $wcfm_variation_data;
	}
	
	/**
	 * Third Party Product Meta data save
	 */
	function wcfm_thirdparty_products_manage_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		// Yoast SEO Support
		if( WCFM_Dependencies::wcfm_yoast_plugin_active_check() || WCFM_Dependencies::wcfm_yoast_premium_plugin_active_check() ) {
			if(isset($wcfm_products_manage_form_data['yoast_wpseo_focuskw_text_input'])) {
				update_post_meta( $new_product_id, '_yoast_wpseo_focuskw_text_input', $wcfm_products_manage_form_data['yoast_wpseo_focuskw_text_input'] );
				update_post_meta( $new_product_id, '_yoast_wpseo_focuskw', $wcfm_products_manage_form_data['yoast_wpseo_focuskw_text_input'] );
			}
			if(isset($wcfm_products_manage_form_data['yoast_wpseo_metadesc'])) {
				update_post_meta( $new_product_id, '_yoast_wpseo_metadesc', strip_tags( $wcfm_products_manage_form_data['yoast_wpseo_metadesc'] ) );
			}
		}
		
		// All in One SEO Support
		if( WCFM_Dependencies::wcfm_all_in_one_seo_plugin_active_check() || WCFM_Dependencies::wcfm_all_in_one_seo_pro_plugin_active_check() ) {
			if(isset($wcfm_products_manage_form_data['aiosp_title'])) {
				update_post_meta( $new_product_id, '_aioseop_title', $wcfm_products_manage_form_data['aiosp_title'] );
				update_post_meta( $new_product_id, '_aioseop_description', $wcfm_products_manage_form_data['aiosp_description'] );
			}
		}
		
		// Rank Math SEO Support
		if( WCFM_Dependencies::wcfm_rankmath_seo_plugin_active_check() ) {
			if(isset($wcfm_products_manage_form_data['rank_math_focus_keyword'])) {
				update_post_meta( $new_product_id, 'rank_math_focus_keyword', $wcfm_products_manage_form_data['rank_math_focus_keyword'] );
				update_post_meta( $new_product_id, 'rank_math_description', $wcfm_products_manage_form_data['rank_math_description'] );
			}
		}
		
		// WooCommerce Custom Product Tabs Lite Support
		if(WCFM_Dependencies::wcfm_wc_tabs_lite_plugin_active_check()) {
			if(isset($wcfm_products_manage_form_data['product_tabs'])) {
				$frs_woo_product_tabs = array();
				if( !empty( $wcfm_products_manage_form_data['product_tabs'] ) ) {
					foreach( $wcfm_products_manage_form_data['product_tabs'] as $frs_woo_product_tab ) {
						if( $frs_woo_product_tab['title'] ) {
							// convert the tab title into an id string
							$tab_id = strtolower( wc_clean( $frs_woo_product_tab['title'] ) );
		
							// remove non-alphas, numbers, underscores or whitespace
							$tab_id = preg_replace( "/[^\w\s]/", '', $tab_id );
		
							// replace all underscores with single spaces
							$tab_id = preg_replace( "/_+/", ' ', $tab_id );
		
							// replace all multiple spaces with single dashes
							$tab_id = preg_replace( "/\s+/", '-', $tab_id );
		
							// prepend with 'tab-' string
							$tab_id = 'tab-' . $tab_id;
							
							$frs_woo_product_tabs[] = array(
																							'title'   => wc_clean( $frs_woo_product_tab['title'] ),
																							'id'      => $tab_id,
																							'content' => $frs_woo_product_tab['content']
																						);
						}
					}
					update_post_meta( $new_product_id, 'frs_woo_product_tabs', $frs_woo_product_tabs );
				} else {
					delete_post_meta( $new_product_id, 'frs_woo_product_tabs' );
				}
			}
		}
		
		// WooCommerce barcode & ISBN Support
		if(WCFM_Dependencies::wcfm_wc_barcode_isbn_plugin_active_check()) {
			if(isset($wcfm_products_manage_form_data['barcode'])) {
				update_post_meta( $new_product_id, 'barcode', $wcfm_products_manage_form_data['barcode'] );
				update_post_meta( $new_product_id, 'ISBN', $wcfm_products_manage_form_data['ISBN'] );
			}
		}
		
		// WooCommerce MSRP Pricing Support
		if(WCFM_Dependencies::wcfm_wc_msrp_pricing_plugin_active_check()) {
			if(isset($wcfm_products_manage_form_data['_msrp_price'])) {
				update_post_meta( $new_product_id, '_msrp_price', strip_tags( $wcfm_products_manage_form_data['_msrp_price'] ) );
			}
		}
		
		// Quantities and Units for WooCommerce Support 
		if( $allow_quantities_units = apply_filters( 'wcfm_is_allow_quantities_units', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_quantities_units_plugin_active_check()) {
				if(isset($wcfm_products_manage_form_data['_wpbo_override'])) {
					update_post_meta( $new_product_id, '_wpbo_override', 'on' );
					update_post_meta( $new_product_id, '_wpbo_deactive', isset( $wcfm_products_manage_form_data['_wpbo_deactive'] ) ? 'on' : '' );
					update_post_meta( $new_product_id, '_wpbo_step', strip_tags( $wcfm_products_manage_form_data['_wpbo_step'] ) );
					update_post_meta( $new_product_id, '_wpbo_minimum', strip_tags( $wcfm_products_manage_form_data['_wpbo_minimum'] ) );
					update_post_meta( $new_product_id, '_wpbo_maximum', strip_tags( $wcfm_products_manage_form_data['_wpbo_maximum'] ) );
					update_post_meta( $new_product_id, '_wpbo_minimum_oos', strip_tags( $wcfm_products_manage_form_data['_wpbo_minimum_oos'] ) );
					update_post_meta( $new_product_id, '_wpbo_maximum_oos', strip_tags( $wcfm_products_manage_form_data['_wpbo_maximum_oos'] ) );
					update_post_meta( $new_product_id, 'unit', strip_tags( $wcfm_products_manage_form_data['unit'] ) );
				} else {
					update_post_meta( $new_product_id, '_wpbo_override', '' );
				}
			}
		}
		
		// WooCommerce Product Fees Support
		if( $allow_product_fees = apply_filters( 'wcfm_is_allow_product_fees', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_product_fees_plugin_active_check()) {
				update_post_meta( $new_product_id, 'product-fee-name', $wcfm_products_manage_form_data['product-fee-name'] );
				update_post_meta( $new_product_id, 'product-fee-amount', $wcfm_products_manage_form_data['product-fee-amount'] );
				$product_fee_multiplier = ( $wcfm_products_manage_form_data['product-fee-multiplier'] ) ? 'yes' : 'no';
				update_post_meta( $new_product_id, 'product-fee-multiplier', $product_fee_multiplier );
			}
		}
		
		// WooCommerce Bulk Discount Support
		if( $allow_bulk_discount = apply_filters( 'wcfm_is_allow_bulk_discount', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_bulk_discount_plugin_active_check()) {
				$_bulkdiscount_enabled = ( $wcfm_products_manage_form_data['_bulkdiscount_enabled'] ) ? 'yes' : 'no';
				update_post_meta( $new_product_id, '_bulkdiscount_enabled', $_bulkdiscount_enabled );
				update_post_meta( $new_product_id, '_bulkdiscount_text_info', $wcfm_products_manage_form_data['_bulkdiscount_text_info'] );
				update_post_meta( $new_product_id, '_bulkdiscounts', $wcfm_products_manage_form_data['_bulkdiscounts'] );
				
				$bulk_discount_rule_counter = 0;
				foreach( $wcfm_products_manage_form_data['_bulkdiscounts'] as $bulkdiscount ) {
					$bulk_discount_rule_counter++;
					update_post_meta( $new_product_id, '_bulkdiscount_quantity_'.$bulk_discount_rule_counter, $bulkdiscount['quantity'] );
					update_post_meta( $new_product_id, '_bulkdiscount_discount_'.$bulk_discount_rule_counter, $bulkdiscount['discount'] );
				}
				
				if( $bulk_discount_rule_counter < 5 ) {
					for( $bdrc = ($bulk_discount_rule_counter+1); $bdrc <= 5; $bdrc++ ) {
						update_post_meta( $new_product_id, '_bulkdiscount_quantity_'.$bdrc, '' );
						update_post_meta( $new_product_id, '_bulkdiscount_discount_'.$bdrc, '' );
					}
				}
			}
		}
		
		// WooCommerce Product Fees Support
		if( apply_filters( 'wcfm_is_allow_role_based_price', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_role_based_price_active_check()) {
				if( isset( $wcfm_products_manage_form_data['role_based_price'] ) ) {
					update_post_meta( $new_product_id, '_role_based_price', $wcfm_products_manage_form_data['role_based_price'] );	
					update_post_meta( $new_product_id, '_enable_role_based_price', 1 );
				}
			}
		}
		
		
		// Woo Advanced Product Size Chart - 6.4.1
    if( apply_filters( 'wcfm_is_allow_woo_product_size_chart', true ) ) {
			if( WCFM_Dependencies::wcfm_woo_product_size_chart_plugin_active_check() ) {
				if( isset( $wcfm_products_manage_form_data['wcfm_prod_chart'] ) ) {
					update_post_meta( $new_product_id, 'prod-chart', $wcfm_products_manage_form_data['wcfm_prod_chart'] );	
				}
			}
		}
		
		// WooCommerce Country Based Restrictions - 6.5.3
    if( apply_filters( 'wcfm_is_allow_woo_country_based_restriction', true ) ) {
			if( WCFM_Dependencies::wcfm_woo_country_based_restriction_active_check() ) {
				if( isset( $wcfm_products_manage_form_data['_fz_country_restriction_type'] ) ) {
					update_post_meta( $new_product_id, '_fz_country_restriction_type', sanitize_text_field( $wcfm_products_manage_form_data['_fz_country_restriction_type'] ) );	
				}
				
				$countries = array();
				if(isset($wcfm_products_manage_form_data["_restricted_countries"])) {
					$countries = wc_clean( $wcfm_products_manage_form_data['_restricted_countries'] );
					update_post_meta( $new_product_id, '_restricted_countries', $countries );
				}
			}
		}
	}
}