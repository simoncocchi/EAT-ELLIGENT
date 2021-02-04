<?php
/**
 * WCFM plugin core
 *
 * Plugin Vendor Support Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   2.0.0
 */
 
class WCFM_Vendor_Support {

	public function __construct() {
		global $WCFM;
		
		if( $is_marketplace = wcfm_is_marketplace() ) {
			if ( !is_admin() || defined('DOING_AJAX') ) {
				if( !wcfm_is_vendor() ) {
					if( $is_allow_vendors = apply_filters( 'wcfm_is_allow_vendors', true ) ) {
						// Vendors Query Var Filter
						add_filter( 'wcfm_query_vars', array( &$this, 'vendors_wcfm_query_vars' ), 20 );
						add_filter( 'wcfm_endpoint_title', array( &$this, 'vendors_wcfm_endpoint_title' ), 20, 2 );
						add_action( 'init', array( &$this, 'vendors_wcfm_init' ), 20 );
						
						// WCFM Vendors Endpoint Edit
						add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfm_vendors_endpoints_slug' ) );
						
						// Vendors Menu Filter
						add_filter( 'wcfm_menus', array( &$this, 'vendors_wcfm_menus' ), 20 );
					}
				}
				
				
				if( wcfm_is_vendor() ) {
					add_filter( 'wcfm_orders_total_heading', array( &$this, 'wcfm_vendors_orders_total_heading' ) );
					
					add_action('pre_get_posts', array( &$this, 'wcfm_vendors_only_attachments' ) );
				}
				
				if( !wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_commission_manage', true ) && apply_filters( 'wcfm_is_allow_view_commission', true ) ) {
					// Associate Vendor
					add_action( 'after_wcfm_products_manage_tabs_content', array( &$this, 'wcfm_associate_vendor' ), 490, 4 );
					add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_associate_vendor_save' ), 490, 2 );
					
					// Commmission Manage
					if( $is_marketplace == 'wcvendors' ) {
						add_action( 'end_wcfm_products_manage', array( &$this, 'wcvendors_product_commission' ), 500 );
						add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcvendors_product_commission_save' ), 500, 2 );
					} else	if( $is_marketplace == 'wcmarketplace' ) {
						add_action( 'end_wcfm_products_manage', array( &$this, 'wcmarketplace_product_commission' ), 500 );
						
						// For Variation
						add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcmarketplace_commission_fields_variations' ), 500, 4 );
						add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcmarketplace_commission_data_variations' ), 500, 3 );
						
						// Commision Save
						add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcmarketplace_product_commission_save' ), 500, 2 );
						
						// Commision Save For Variation
						add_filter( 'wcfm_product_variation_data_factory', array( &$this, 'wcmarketplace_product_variation_commission_save' ), 500, 5 );
					}
				}
				
				// Product Vendors Manage Vendor Product Permissions
				if( $is_marketplace == 'wcpvendors' ) {
					add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcpvendors_product_manage_vendor_association' ), 10, 2 );
				}
				
				//if( $WCFM->is_marketplace == 'dokan' ) {
					add_filter( 'dokan_get_dashboard_nav', array( &$this, 'wcfm_dokan_get_dashboard_nav' ), 100 );
				//}
				
				//add_filter( 'wcfm_message_types', array( &$this, 'wcfm_store_message_types' ) );
			}
			
			// WCFM Marketplace Vendor Order Items filter
			add_filter( 'wcfm_valid_vendor_line_items', array( &$this, 'wcfm_vendor_valid_line_items' ), 10, 3 );
			add_filter( 'wcfm_valid_vendor_shipping_items', array( &$this, 'wcfm_vendor_valid_shipping_items' ), 10, 3 );
			
			// WC Vendor Capability update
			add_action( 'wcvendors_option_updates', array( &$this, 'vendors_capability_option_updates' ), 10, 2 );
			
			// Modify WCMp Vendor Backend access message
			add_filter( 'is_wcmp_backend_disabled', array( &$this, 'is_wcmp_backend_disabled_by_wcfm' ), 500 );
			
			// Remove WCMp Capability Tab
			add_filter( 'wcmp_tabs', array( &$this, 'wcmp_tabs_capability_disabled_by_wcfm' ), 500 );
			
			// Change WCMp Stripe config URL
			add_filter( 'settings_payment_stripe_gateway_tab_options', array( &$this, 'wcmp_change_stripe_config_by_wcfm' ), 500 );
			
			// WC Vendors Registration redirect
			add_filter( 'wcvendors_signup_redirect', array( &$this, 'wcfm_wcvendors_signup_redirect' ), 500 );
			
			// Dokan Navigtion URL
			add_filter( 'dokan_get_navigation_url', array( &$this, 'wcfm_dokan_get_navigation_url' ), 500, 2 );
			
			if( in_array( $is_marketplace, array('wcmarketplace', 'wcfmmarketplace') ) ) {
				add_filter( 'wcfm_is_admin_fee_mode', array( &$this, 'wcfm_is_admin_fee_mode' ) );
			}
		}
		
		// Login Redirect
		if( apply_filters( 'wcfm_is_allow_login_redirect', true ) ) {
			add_filter( 'woocommerce_login_redirect', array($this, 'wcfm_wc_vendor_login_redirect'), 50, 2 );
			add_filter( 'login_redirect', array($this, 'wcfm_vendor_login_redirect'), 50, 3 );
		}
		
		// Vendor Coupon Apply Validate
		add_filter( 'woocommerce_coupon_is_valid_for_product', array($this, 'wcfm_vendor_coupon_apply_validate'), 500, 4 );
		
		// Display Order Item Meta Vendor
		add_filter( 'woocommerce_order_item_display_meta_key', array( &$this, 'wcfm_vendor_id_display_label' ), 50, 2 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( &$this, 'wcfm_vendor_id_display_value' ), 50, 2 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( &$this, 'wcfm_order_item_meta_filter' ), 50, 2 );
		
		// Delete Post Attachment on Post Delte
		if( apply_filters( 'wcfm_is_allow_delete_post_media', false ) ) {
			add_action( 'before_delete_post', array( &$this, 'wcfm_delete_post_media' ), 50 );
		}
	}
	
	/**
   * WCFM Vendors Query Var
   */
  function vendors_wcfm_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_vendors_vars = array(
			'wcfm-vendors'                 => ! empty( $wcfm_modified_endpoints['wcfm-vendors'] ) ? $wcfm_modified_endpoints['wcfm-vendors'] : 'vendors',
			'wcfm-vendors-new'             => ! empty( $wcfm_modified_endpoints['wcfm-vendors-new'] ) ? $wcfm_modified_endpoints['wcfm-vendors-new'] : 'vendors-new',
			'wcfm-vendors-manage'          => ! empty( $wcfm_modified_endpoints['wcfm-vendors-manage'] ) ? $wcfm_modified_endpoints['wcfm-vendors-manage'] : 'vendors-manage',
			'wcfm-vendors-commission'      => ! empty( $wcfm_modified_endpoints['wcfm-vendors-commission'] ) ? $wcfm_modified_endpoints['wcfm-vendors-commission'] : 'vendors-commission',
		);
		
		$query_vars = array_merge( $query_vars, $query_vendors_vars );
		
		return $query_vars;
  }
  
  /**
   * WCFM Vendors End Point Title
   */
  function vendors_wcfm_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-vendors' :
				$title = __( 'Vendors Dashboard', 'wc-frontend-manager' );
			break;
			case 'wcfm-vendors-new' :
				$title = __( 'New Vendor', 'wc-frontend-manager' );
			break;
			case 'wcfm-vendors-manage' :
				$title = __( 'Vendors Manager', 'wc-frontend-manager' );
			break;
			case 'wcfm-vendors-commission' :
				$title = __( 'Vendors Commission', 'wc-frontend-manager' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCFM Vendors Endpoint Intialize
   */
  function vendors_wcfm_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_wcfm_vendors' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_wcfm_vendors', 1 );
		}
  }
  
  /**
	 * WCFM Vendors Endpoiint Edit
	 */
  function wcfm_vendors_endpoints_slug( $endpoints ) {
		
		$vendors_endpoints = array(
													'wcfm-vendors'  		      => 'vendors',
													'wcfm-vendors-new'  	    => 'vendors-new',
													'wcfm-vendors-manage'  	  => 'vendors-manage',
													//'wcfm-vendors-commission' => 'vendors-commission'
													);
		$endpoints = array_merge( $endpoints, $vendors_endpoints );
		
		return $endpoints;
	}
  
  /**
   * WCFM Vendors Menu
   */
  function vendors_wcfm_menus( $menus ) {
  	global $WCFM;
  	
  	if( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
			$menus = array_slice($menus, 0, 3, true) +
													array( 'wcfm-vendors' => array(  'label'      => apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendors', 'wc-frontend-manager'),
																													 'url'        => get_wcfm_vendors_url(),
																													 'icon'       => 'user-alt',
																													 'has_new'    => 'yes',
																													 'new_class'  => 'wcfm_sub_menu_items_vendors_manage',
																													 'new_url'    => get_wcfm_vendors_new_url(),
																													 'capability' => 'wcfm_vendors_menu',
																													 'submenu_capability' => 'wcfm_add_new_vendors_sub_menu',
																													 'priority'  => 45
																													) )	 +
														array_slice($menus, 3, count($menus) - 3, true) ;
  	} else {
			$menus = array_slice($menus, 0, 3, true) +
													array( 'wcfm-vendors' => array(   'label'  => __( 'Vendors', 'wc-frontend-manager'),
																											 'url'       => get_wcfm_vendors_url(),
																											 'icon'      => 'user-alt',
																											 'priority'  => 45
																											) )	 +
														array_slice($menus, 3, count($menus) - 3, true) ;
		}
		
  	return $menus;
  }
  
	/**
	 * WCFM WC Vendor Login redirect
	 */
	function wcfm_wc_vendor_login_redirect( $redirect_to, $user ) {
		if ( isset($user->roles) && is_array($user->roles) ) {
			if ( in_array( 'vendor', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			} elseif ( in_array( 'seller', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			} elseif ( in_array( 'wcfm_vendor', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			} elseif ( in_array( 'dc_vendor', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			} elseif ( in_array( 'wc_product_vendors_admin_vendor', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			} elseif ( in_array( 'wc_product_vendors_manager_vendor', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			} elseif ( in_array( 'shop_manager', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			} elseif ( in_array( 'shop_staff', $user->roles ) ) {
				$redirect_to = get_wcfm_url();
			}
		}
		
		if ( $user && isset($user->ID) ) {
			$current_login = get_user_meta( $user->ID, '_current_login', true );
			update_user_meta( $user->ID, '_previous_login', $current_login );
			update_user_meta( $user->ID, '_current_login', current_time( 'timestamp' ) );
		}
		
		return apply_filters( 'wcfm_login_redirect', $redirect_to, $user );
	}
	
	/**
	 * WCFM Vendor Login redirect
	 */
	function wcfm_vendor_login_redirect( $redirect_to, $request = '', $user = '' ) {
		if( $user ) {
			if ( isset($user->roles) && is_array($user->roles) ) {
				if ( in_array( 'vendor', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				} elseif ( in_array( 'seller', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				} elseif ( in_array( 'wcfm_vendor', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				} elseif ( in_array( 'dc_vendor', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				} elseif ( in_array( 'wc_product_vendors_admin_vendor', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				} elseif ( in_array( 'wc_product_vendors_manager_vendor', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				} elseif ( in_array( 'shop_manager', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				} elseif ( in_array( 'shop_staff', $user->roles ) ) {
					$redirect_to = get_wcfm_url();
				}
			}
			
			if ( $user && isset($user->ID) ) {
				$current_login = get_user_meta( $user->ID, '_current_login', true );
				update_user_meta( $user->ID, '_previous_login', $current_login );
				update_user_meta( $user->ID, '_current_login', current_time( 'timestamp' ) );
			}
		}
		
		return apply_filters( 'wcfm_login_redirect', $redirect_to, $user );
	}
	
	/**
	 * Vendor coupon apply validate
	 */
	function wcfm_vendor_coupon_apply_validate( $valid, $product, $coupon, $values ) {
		if( $valid ) {
			$coupon_id = $coupon->get_id();
			$coupon_vendor = wcfm_get_vendor_id_by_post( $coupon_id );
			if( $coupon_vendor ) {
				$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
				$product_vendor = wcfm_get_vendor_id_by_post( $product_id );
				if( $coupon_vendor != $product_vendor ) $valid = false;
			}
		}
		return $valid;
	}
	
	function wcfm_vendor_id_display_label( $display_key, $meta = '' ) {
		if( ($display_key == '_vendor_id') || ($display_key == 'vendor_id') || ($display_key == '_seller_id') || ($display_key == 'seller_id') ) {
			if( $meta && is_object( $meta ) && $meta->value ) {
				$display_key = apply_filters( 'wcfm_sold_by_label', $meta->value, __( 'Store', 'wc-frontend-manager' ) );
			} else {
				$display_key = apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) );
			}
		}
		if( $display_key == '_wcfmmp_order_item_processed' ) {
			$display_key = __( 'Store Order ID', 'wc-frontend-manager' );
		}
		if( $display_key == 'package_qty' ) {
			$display_key = __( 'Package Qty', 'wc-frontend-manager' );
		}
		if( $display_key == 'method_slug' ) {
			$display_key = __( 'Shipping Method', 'wc-frontend-manager' );
		}
		if( $display_key == 'processing_time' ) {
			$display_key = __( 'Processing Time', 'wc-frontend-manager' );
		}
		if( $display_key == 'description' ) {
			$display_key = __( 'Description', 'wc-frontend-manager' );
		}
		if( $display_key == 'pickup_address' ) {
			$display_key = __( 'Pickup Address', 'wc-frontend-manager' );
		}
		if( $display_key == 'wcfm_delivery_boy' ) {
			$display_key = __( 'Delivery Boy', 'wc-frontend-manager-delivery' );
		}
		return $display_key;
	}
	
	function wcfm_vendor_id_display_value( $display_value, $meta = '' ) {
		if( $meta && is_object( $meta ) && ( ($meta->key == '_vendor_id') || ($meta->key == 'vendor_id') || ($meta->key == '_seller_id') || ($meta->key == 'seller_id') ) ) {
			$display_value = $this->wcfm_get_vendor_store_by_vendor( absint($display_value) );
		}
		if( $meta && is_object( $meta ) && ($meta->key == 'method_slug') ) {
			if( function_exists( 'wcfmmp_get_shipping_methods' ) ) {
				$shipping_mthods = wcfmmp_get_shipping_methods();
				if( isset( $shipping_mthods[$display_value] ) ) {
					$display_value =  $shipping_mthods[$display_value];
				} elseif( $display_value == 'wcfmmp_product_shipping_by_country' ) {
					$display_value = __( 'Store Shipping by Country', 'wc-frontend-manager' );
				} elseif( $display_value == 'wcfmmp_product_shipping_by_weight' ) {
					$display_value = __( 'Store Shipping by Weight', 'wc-frontend-manager' );
				} elseif( $display_value == 'wcfmmp_product_shipping_by_distance' ) {
					$display_value = __( 'Store Shipping by Distance', 'wc-frontend-manager' );
				} else {
					$display_value = ucfirst( str_replace( '_', ' ', $display_value ) );
				}
			} else {
				$display_value = ucfirst( str_replace( '_', ' ', $display_value ) );
			}
		}
		if( $meta && is_object( $meta ) && ($meta->key == 'wcfm_delivery_boy') ) {
			$wcfm_delivery_boy_user = get_userdata( $display_value );
			if( $wcfm_delivery_boy_user ) {
				$display_value = apply_filters( 'wcfm_delivery_boy_display', $wcfm_delivery_boy_user->first_name . ' ' . $wcfm_delivery_boy_user->last_name, $display_value );
			} else {
				$display_value = '&ndash;';
			}
		}
		return $display_value;
	}
	
	function wcfm_order_item_meta_filter( $formatted_meta, $order_item ) {
		$formatted_meta_arr = $formatted_meta;
		if( !empty( $formatted_meta_arr ) ) {
			foreach( $formatted_meta_arr as $meta_id => $meta ) {
				if( isset( $meta->key ) && in_array( $meta->key, array( '_wcfm_affiliate_id', '_wcfm_affiliate_order', '_wcfm_affiliate_commission', '_wcfm_affiliate_commission_rule' ) ) ) {
					unset( $formatted_meta[$meta_id] );
				}
			}
		}
		return $formatted_meta;
	}
	
	function wcfm_delete_post_media( $product_id ) {
		if( $product_id ) {
			$product = wc_get_product( $product_id );
			if( $product && is_object( $product ) ) {
				
				$thumbnail_id = get_post_meta( $product_id, '_thumbnail_id', true );
				if( $thumbnail_id ) {
					if ( false === wp_delete_attachment( $thumbnail_id, true ) ) {
						wcfm_log( "Attachment Delete Failed: " . $product_id . "=>" . $thumbnail_id );
					}
				}
				
				$attachments = get_post_meta( $product_id, '_product_image_gallery', true );
				if( $attachments ) {
					$attachments = explode( ",", $attachments );
					if( !empty( $attachments ) ) {
						foreach ( $attachments as $attachment ) {
							if ( false === wp_delete_attachment( $attachment, true ) ) {
								wcfm_log( "Attachment Delete Failed: " . $product_id . "=>" . $attachment );
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * Orders total heading as commission for vendors
	 */
	function wcfm_vendors_orders_total_heading( $heading ) {
		global $WCFM;
		
		$heading = __( 'Commission', 'wc-frontend-manager');
		return $heading;
	}
	
	/**
	 * Restrict vendors to see only their attachments
	 */
	function wcfm_vendors_only_attachments( $wp_query_obj ) {
		global $current_user, $pagenow;

    $is_attachment_request = ($wp_query_obj->get('post_type')=='attachment');

    if( !$is_attachment_request )
        return;

    if( !is_a( $current_user, 'WP_User') )
        return;

    if( !in_array( $pagenow, array( 'upload.php', 'admin-ajax.php' ) ) )
        return;

    if( !current_user_can('delete_pages') )
        $wp_query_obj->set('author', $current_user->ID );

    return;
	}
	
	// WCFM Associate Vendor to Product
	function wcfm_associate_vendor( $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$is_marketplace = wcfm_is_marketplace();
		if( $is_marketplace ) {
			$wcfm_associate_vendor = $this->wcfm_get_vendor_id_from_product( $product_id );
			$vendor_arr = array(); 
			if( $wcfm_associate_vendor ) $vendor_arr = array( $wcfm_associate_vendor => $this->wcfm_get_vendor_store_name_by_vendor($wcfm_associate_vendor) ); 
			?>
			<!-- collapsible 11.5 - WCFM Vendor Association -->
			<div class="page_collapsible products_manage_vendor_association simple variable grouped external booking <?php echo $wcfm_wpml_edit_disable_element; ?>" id="wcfm_products_manage_form_vendor_association_head"><label class="wcfmfa fa-user-alt fa-user-alt"></label><?php echo apply_filters( 'wcfm_sold_by_label', $wcfm_associate_vendor, __( 'Store', 'wc-frontend-manager' ) ); ?><span></span></div>
			<div class="wcfm-container simple variable external grouped booking">
				<div id="wcfm_products_manage_form_vendor_association_expander" class="wcfm-content">
					<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'product_manage_fields_commission', array(  
																																															"wcfm_associate_vendor" => array( 'label' => apply_filters( 'wcfm_sold_by_label', $wcfm_associate_vendor, __( 'Store', 'wc-frontend-manager' ) ), 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 60%;' ), 'class' => 'wcfm-select', 'label_class' => 'wcfm_title', 'value' => $wcfm_associate_vendor ),
																																										), $product_id ) );
					?>
				</div>
			</div>
			<!-- end collapsible -->
			<div class="wcfm_clearfix"></div>
			<?php
		}
	}
	
	// WCFM Associate Vendor to Product Save
	function wcfm_associate_vendor_save( $new_product_id, $wcfm_products_manage_form_data ) {
		
		$is_marketplace = wcfm_is_marketplace();
		if( $is_marketplace ) {
			if( !wcfm_is_vendor() || defined('DOING_WCFM_WPML') ) {
				if( $is_marketplace == 'wcpvendors' ) {
					if( isset( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) && !empty( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) ) {
						$vnd_term = absint( $wcfm_products_manage_form_data['wcfm_associate_vendor'] );
						wp_delete_object_term_relationships( $new_product_id, WC_PRODUCT_VENDORS_TAXONOMY );
						wp_set_object_terms( $new_product_id, $vnd_term, WC_PRODUCT_VENDORS_TAXONOMY, true );
					} else {
						wp_delete_object_term_relationships( $new_product_id, WC_PRODUCT_VENDORS_TAXONOMY );
					}
					// Pass Shipping/Tax to vendor
					update_post_meta( $new_product_id, '_wcpv_product_default_pass_shipping_tax', 'yes' );
				} elseif( $is_marketplace == 'wcmarketplace' ) {
					if( isset( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) && !empty( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) ) {
						$vnd_term = absint( $wcfm_products_manage_form_data['wcfm_associate_vendor'] );
						$vendor_term = get_user_meta( $vnd_term, '_vendor_term_id', true );
						$vendor_term = absint( $vendor_term );
						wp_delete_object_term_relationships( $new_product_id, 'dc_vendor_shop' );
						wp_set_object_terms( $new_product_id, $vendor_term, 'dc_vendor_shop', true );
						
						// Set author as well
						$arg = array(
							'ID' => $new_product_id,
							'post_author' => $vnd_term,
						);
						wp_update_post( $arg );
					} else {
						wp_delete_object_term_relationships( $new_product_id, 'dc_vendor_shop' );
						// Set author as well
						$arg = array(
							'ID' => $new_product_id,
							'post_author' => get_current_user_id(),
						);
						wp_update_post( $arg );
					}
				} elseif( $is_marketplace == 'wcvendors' ) {
					if( isset( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) && !empty( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) ) {
						$vnd_term = absint( $wcfm_products_manage_form_data['wcfm_associate_vendor'] );
						$arg = array(
							'ID' => $new_product_id,
							'post_author' => $vnd_term,
						);
						wp_update_post( $arg );
					} else {
						$arg = array(
							'ID' => $new_product_id,
							'post_author' => get_current_user_id(),
						);
						wp_update_post( $arg );
					}
				} elseif( $is_marketplace == 'dokan' ) {
					if( isset( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) && !empty( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) ) {
						$vnd_term = absint( $wcfm_products_manage_form_data['wcfm_associate_vendor'] );
						$arg = array(
							'ID' => $new_product_id,
							'post_author' => $vnd_term,
						);
						wp_update_post( $arg );
					} else {
						$arg = array(
							'ID' => $new_product_id,
							'post_author' => get_current_user_id(),
						);
						wp_update_post( $arg );
					}
				} elseif( $is_marketplace == 'wcfmmarketplace' ) {
					if( isset( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) && !empty( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) ) {
						$vnd_term = absint( $wcfm_products_manage_form_data['wcfm_associate_vendor'] );
						$arg = array(
							'ID' => $new_product_id,
							'post_author' => $vnd_term,
						);
						wp_update_post( $arg );
						
						// For Variations
						$product = wc_get_product( $new_product_id );
						$wcfm_variable_product_types = apply_filters( 'wcfm_variable_product_types', array( 'variable', 'variable-subscription', 'pw-gift-card' ) );
						if( in_array( $product->get_type(), $wcfm_variable_product_types ) ) {
							foreach ( $product->get_children() as $child_id ) {
								$arg = array(
									'ID' => $child_id,
									'post_author' => $vnd_term,
								);
								wp_update_post( $arg );
							}
						}
					} else {
						$old_vendor_id = wcfm_get_vendor_id_by_post( $new_product_id );
						if( $old_vendor_id && wcfm_is_vendor( $old_vendor_id ) ) {
							$arg = array(
								'ID' => $new_product_id,
								'post_author' => get_current_user_id(),
							);
							wp_update_post( $arg );
						}
					}
				}
			}
		}
	}
	
	// WCV Vendor Commission
	function wcvendors_product_commission( $product_id ) {
		global $WCFM;
		
		$pv_commission_rate = '';
		if( $product_id  ) {
			$pv_commission_rate = get_post_meta( $product_id , 'pv_commission_rate', true );
		}
		?>
		<!-- collapsible 12 - WCV Commission Support -->
		<div class="page_collapsible products_manage_commission simple variable grouped external booking" id="wcfm_products_manage_form_commission_head"><label class="wcfmfa fa-percent"></label><?php _e('Commission', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container simple variable external grouped booking">
			<div id="wcfm_products_manage_form_commission_expander" class="wcfm-content">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'product_manage_fields_commission', array(  
																																														"pv_commission_rate" => array('label' => __('Commission(%)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $pv_commission_rate ),
																																									)) );
				?>
			</div>
		</div>
		<!-- end collapsible -->
		<div class="wcfm_clearfix"></div>
		<?php
	}
	
	// WCV Vendor Commision Save
	function wcvendors_product_commission_save( $new_product_id, $wcfm_products_manage_form_data ) {
		
		if( isset( $wcfm_products_manage_form_data['pv_commission_rate'] ) && !empty( $wcfm_products_manage_form_data['pv_commission_rate'] ) ) {
			update_post_meta( $new_product_id, 'pv_commission_rate', $wcfm_products_manage_form_data['pv_commission_rate'] );
		}
	}
	
	// WCMp Vendor Product Commission
	function wcmarketplace_product_commission( $product_id ) {
		global $WCFM, $WCMp;
		
		$commission_per_poduct = '';
		$commission_percentage_per_poduct = '';
		$commission_fixed_with_percentage = '';
		$commission_fixed_with_percentage_qty = '';
		if( $product_id  ) {
			$commission_per_poduct = get_post_meta( $product_id, '_commission_per_product', true);
			$commission_percentage_per_poduct = get_post_meta( $product_id, '_commission_percentage_per_product', true);
			$commission_fixed_with_percentage = get_post_meta( $product_id, '_commission_fixed_with_percentage', true);
			$commission_fixed_with_percentage_qty = get_post_meta( $product_id, '_commission_fixed_with_percentage_qty', true);
		}
		?>
		<!-- collapsible 12 - WCMp Commission Support -->
		<div class="page_collapsible products_manage_commission simple variable grouped external booking" id="wcfm_products_manage_form_commission_head"><label class="wcfmfa fa-percent"></label><?php _e('Commission', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container simple variable external grouped booking">
			<div id="wcfm_products_manage_form_commission_expander" class="wcfm-content">
				<?php
				if ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'product_manage_fields_commission', array(  
																																															"_commission_percentage_per_product" => array('label' => __('Commission(%)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $commission_percentage_per_poduct ),
																																															"_commission_fixed_with_percentage" => array('label' => __('Fixed (per transaction)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $commission_fixed_with_percentage )
																																										)) );
				} elseif ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'product_manage_fields_commission', array(  
																																															"_commission_percentage_per_product" => array('label' => __('Commission(%)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $commission_percentage_per_poduct ),
																																															"_commission_fixed_with_percentage_qty" => array('label' => __('Fixed (per unit)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $commission_fixed_with_percentage_qty )
																																										)) );
				} else {
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'product_manage_fields_commission', array(  
																																															"_commission_per_product" => array('label' => __('Commission', 'wc-frontend-manager') . ' ('.$WCMp->vendor_caps->payment_cap['commission_type'].')' , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $commission_per_poduct ),
																																										)) );
				}
				?>
			</div>
		</div>
		<!-- end collapsible -->
		<div class="wcfm_clearfix"></div>
		<?php
	}
	
	/**
	 * WCFMu Variation aditional options
	 */
	function wcmarketplace_commission_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCMp;
		
		if ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {
			$wcfmu_variation_commission_fields = array(  
																									"_commission_percentage_per_product" => array('label' => __('Commission(%)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking' ),
																									"_commission_fixed_with_percentage" => array('label' => __('Fixed (per transaction)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking' )
																				         );
		} elseif ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {
			$wcfmu_variation_commission_fields = array(  
																									"_commission_percentage_per_product" => array('label' => __('Commission(%)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking' ),
																									"_commission_fixed_with_percentage_qty" => array('label' => __('Fixed (per unit)', 'wc-frontend-manager') , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking' )
																								);
		} else {
			$wcfmu_variation_commission_fields = array(  
																									"_commission_per_product" => array('label' => __('Commission', 'wc-frontend-manager') . ' ('.$WCMp->vendor_caps->payment_cap['commission_type'].')' , 'type' => 'number', 'attributes' => array( 'min' => '', 'steps' => 1 ), 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking' ),
																					       );
		}
		
		$variation_fileds = array_merge( $variation_fileds, $wcfmu_variation_commission_fields );
		
		return $variation_fileds;
	}
	
	/**
	 * Variaton commission edit data
	 */
	function wcmarketplace_commission_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCMp;
		
		if( $variation_id  ) {
			$variations[$variation_id_key]['description'] = get_post_meta($variation_id, '_product_vendors_commission', true);
			$variations[$variation_id_key]['_commission_percentage_per_product'] = get_post_meta( $variation_id, '_product_vendors_commission_percentage', true);
			$variations[$variation_id_key]['_commission_fixed_with_percentage'] = get_post_meta( $variation_id, '_product_vendors_commission_fixed_per_trans', true);
			$variations[$variation_id_key]['_commission_fixed_with_percentage_qty'] = get_post_meta( $variation_id, '_product_vendors_commission_fixed_per_qty', true);
		}
		
		return $variations;
	}
	
	// WCMp Vendor Product Commision Save
	function wcmarketplace_product_commission_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $WCFM, $WCMp;
		
		if ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {
			if( isset( $wcfm_products_manage_form_data['_commission_percentage_per_product'] ) && !empty( $wcfm_products_manage_form_data['_commission_percentage_per_product'] ) ) {
				update_post_meta( $new_product_id, '_commission_percentage_per_product', $wcfm_products_manage_form_data['_commission_percentage_per_product'] );
			}
			if( isset( $wcfm_products_manage_form_data['_commission_fixed_with_percentage'] ) && !empty( $wcfm_products_manage_form_data['_commission_fixed_with_percentage'] ) ) {
				update_post_meta( $new_product_id, '_commission_fixed_with_percentage', $wcfm_products_manage_form_data['_commission_fixed_with_percentage'] );
			}
		} elseif ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {
			if( isset( $wcfm_products_manage_form_data['_commission_percentage_per_product'] ) && !empty( $wcfm_products_manage_form_data['_commission_percentage_per_product'] ) ) {
				update_post_meta( $new_product_id, '_commission_percentage_per_product', $wcfm_products_manage_form_data['_commission_percentage_per_product'] );
			}
			if( isset( $wcfm_products_manage_form_data['_commission_fixed_with_percentage_qty'] ) && !empty( $wcfm_products_manage_form_data['_commission_fixed_with_percentage_qty'] ) ) {
				update_post_meta( $new_product_id, '_commission_fixed_with_percentage_qty', $wcfm_products_manage_form_data['_commission_fixed_with_percentage_qty'] );
			}
		} else {
			if( isset( $wcfm_products_manage_form_data['_commission_per_product'] ) && !empty( $wcfm_products_manage_form_data['_commission_per_product'] ) ) {
				update_post_meta( $new_product_id, '_commission_per_product', $wcfm_products_manage_form_data['_commission_per_product'] );
			}
		}
	}
	
	// WCMp Vendor Product Variation Commision Save
	function wcmarketplace_product_variation_commission_save( $wcfm_variation_data, $new_product_id, $variation_id, $variations, $wcfm_products_manage_form_data ) {
		global $WCFM, $WCMp;
		
		if ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {
			if( isset( $variations['_commission_percentage_per_product'] ) && !empty( $variations['_commission_percentage_per_product'] ) ) {
				update_post_meta( $variation_id, '_product_vendors_commission_percentage', $variations['_commission_percentage_per_product'] );
			}
			if( isset( $variations['_commission_fixed_with_percentage'] ) && !empty( $variations['_commission_fixed_with_percentage'] ) ) {
				update_post_meta( $variation_id, '_product_vendors_commission_fixed_per_trans', $variations['_commission_fixed_with_percentage'] );
			}
		} elseif ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {
			if( isset( $variations['_commission_percentage_per_product'] ) && !empty( $variations['_commission_percentage_per_product'] ) ) {
				update_post_meta( $variation_id, '_product_vendors_commission_percentage', $variations['_commission_percentage_per_product'] );
			}
			if( isset( $variations['_commission_fixed_with_percentage_qty'] ) && !empty( $variations['_commission_fixed_with_percentage_qty'] ) ) {
				update_post_meta( $variation_id, '_product_vendors_commission_fixed_per_qty', $variations['_commission_fixed_with_percentage_qty'] );
			}
		} else {
			if( isset( $variations['_commission_per_product'] ) && !empty( $variations['_commission_per_product'] ) ) {
				update_post_meta( $variation_id, '_product_vendors_commission', $variations['_commission_per_product'] );
			}
		}
		
		return $wcfm_variation_data;
	}
	
	// Filter Order Details Line Items as Per Vendor
  function wcfm_vendor_valid_line_items( $items, $order_id, $vendor_id ) {
  	global $WCFM, $wpdb;
  	
  	$sql = "SELECT `product_id`, `item_id` FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE `vendor_id` = {$vendor_id} AND `order_id` = {$order_id}";
  	$valid_products = $wpdb->get_results($sql);
  	$valid_items = array();
  	if( !empty($valid_products) ) {
  		foreach( $valid_products as $valid_product ) {
  			$valid_items[] = $valid_product->item_id;
  			$valid_items[] = $valid_product->product_id;
  		}
  	}
  	
  	$valid = array();
  	foreach ($items as $key => $value) {
			if ( in_array( $value->get_variation_id(), $valid_items ) || in_array( $value->get_product_id(), $valid_items ) || in_array( $value->get_id(), $valid_items ) ) {
				$valid[$key] = $value;
			} elseif( $value->get_product_id() == 0 ) {
				$_product_id = wc_get_order_item_meta( $key, '_product_id', true );
				if ( in_array( $_product_id, $valid_items ) ) {
					$valid[$key] = $value;
				}
			}
		}
  	return $valid;
  }
  
  // Filter Shipping Line Items as Per Vendor
  function wcfm_vendor_valid_shipping_items( $shipping_items, $order_id, $vendor_id ) {
  	global $WCFM, $WCFMmp, $wpdb;
  	
  	$vendor_sipping_items = array();
  	
  	foreach ($shipping_items as $shipping_item_id => $shipping_item) {
			$order_item_shipping = new WC_Order_Item_Shipping($shipping_item_id);
			$shipping_vendor_id = $order_item_shipping->get_meta('vendor_id', true);
			if( $shipping_vendor_id && ( $shipping_vendor_id == $vendor_id ) ) {
				$vendor_sipping_items[$shipping_item_id] = $shipping_item;
			}
		}
		
		return $vendor_sipping_items;
  }
	
	// Vendors Capability Options update
  function vendors_capability_option_updates( $options = array(), $tabname = 'capabilities' ) {
  	
  	if( $tabname == 'capabilities' ) {
  		$options = get_option( 'wcfm_capability_options' );
  		$is_marketplace = wcfm_is_marketplace();
  		
  		if( $is_marketplace ) {
  		
				if( $is_marketplace == 'wcvendors' ) {
					$vendor_role = get_role( 'vendor' );
				} elseif( $is_marketplace == 'wcmarketplace' ) {
					$vendor_role = get_role( 'dc_vendor' );
				} elseif( $is_marketplace == 'wcpvendors' ) {
					$vendor_role = get_role( 'wc_product_vendors_admin_vendor' );
				} elseif( $is_marketplace == 'dokan' ) {
					$vendor_role = get_role( 'seller' );
				} elseif( $is_marketplace == 'wcfmmarketplace' ) {
					$vendor_role = get_role( 'wcfm_vendor' );
				}
				
				// Delete Media Capability
				if( isset( $options['delete_media'] ) && $options[ 'delete_media' ] == 'yes' ) {
					$vendor_role->remove_cap( 'delete_attachments' );
					$vendor_role->remove_cap( 'delete_posts' );
				} else {
					$vendor_role->add_cap( 'delete_attachments' );
					$vendor_role->add_cap( 'delete_posts' );
				}
				
				// Product Terms
				$vendor_role->add_cap( 'manage_product_terms' );
				
				// Import & Export Capability
				$vendor_role->add_cap( 'export' );
				$vendor_role->add_cap( 'import' );
				
				// Ensure Vendors Media Upload Capability
				$vendor_role->add_cap('edit_posts');
				$vendor_role->add_cap('edit_post');
				$vendor_role->add_cap('edit_others_posts');
        $vendor_role->add_cap('edit_others_pages');
        $vendor_role->add_cap('edit_published_posts');
        $vendor_role->add_cap('edit_published_pages');
        $vendor_role->add_cap( 'upload_files' );
        $vendor_role->add_cap( 'unfiltered_html' );
        
        // WooCommerce
        $vendor_role->add_cap( 'manage_woocommerce' );
        $vendor_role->add_cap( 'publish_shop_orders' );
        $vendor_role->add_cap( 'list_users' );
        $vendor_role->add_cap( 'create_users' );
        $vendor_role->remove_cap( 'edit_users' );
        $vendor_role->remove_cap( 'bp_moderate' );
        
        // WooCommerce POS Capability
        $vendor_role->add_cap( 'manage_woocommerce_pos' );
        $vendor_role->add_cap( 'access_woocommerce_pos' );
        $vendor_role->add_cap( 'read_private_posts' );
        $vendor_role->add_cap( 'read_private_products' );
        $vendor_role->add_cap( 'read_private_shop_orders' );
				
				// Booking Capability
				if( wcfm_is_booking() ) {
					if( isset( $options['manage_booking'] ) && $options[ 'manage_booking' ] == 'yes' ) {
						$vendor_role->remove_cap( 'manage_bookings' );
						$vendor_role->remove_cap( 'manage_bookings_settings' );
						$vendor_role->remove_cap( 'manage_bookings_timezones' );
						$vendor_role->remove_cap( 'manage_bookings_connection' );
					} else {
						$vendor_role->add_cap( 'manage_bookings' );
						$vendor_role->add_cap( 'manage_bookings_settings' );
						$vendor_role->add_cap( 'manage_bookings_timezones' );
						$vendor_role->add_cap( 'manage_bookings_connection' );
					}
				}
				
				// Appointment Capability
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					if( WCFMu_Dependencies::wcfm_wc_appointments_active_check() ) {
						if( isset( $options['manage_appointment'] ) && $options[ 'manage_appointment' ] == 'yes' ) {
							$vendor_role->remove_cap( 'manage_appointments' );
							$vendor_role->remove_cap( 'manage_others_appointments' );
							$vendor_role->remove_cap( 'manage_options' );
						} else {
							$vendor_role->add_cap( 'manage_appointments' );
							$vendor_role->add_cap( 'manage_others_appointments' );
							$vendor_role->add_cap( 'manage_options' );
						}
					}
				}
				
				// PDF Voucher Capability
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					if( WCFMu_Dependencies::wcfm_wc_pdf_voucher_active_check() ) {
						$vendor_role->add_cap( 'woo_vendor_options' );
					}
				}
				
				// FooSales Capability
				$vendor_role->add_cap( 'publish_foosales' );
				
				// FooEvent Capability
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					if( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
						$vendor_role->add_cap( 'publish_event_magic_tickets' );
						$vendor_role->add_cap( 'edit_event_magic_tickets' );
						$vendor_role->add_cap( 'edit_published_event_magic_tickets' );
						$vendor_role->add_cap( 'edit_others_event_magic_tickets' );
						$vendor_role->add_cap( 'delete_event_magic_tickets' );
						$vendor_role->add_cap( 'delete_others_event_magic_tickets' );
						$vendor_role->add_cap( 'read_private_event_magic_tickets' );
						$vendor_role->add_cap( 'edit_event_magic_ticket' );
						$vendor_role->add_cap( 'delete_event_magic_ticket' );
						$vendor_role->add_cap( 'read_event_magic_ticket' );
						$vendor_role->add_cap( 'edit_published_event_magic_ticket' );
						$vendor_role->add_cap( 'publish_event_magic_ticket' );
						$vendor_role->add_cap( 'delete_others_event_magic_ticket' );
						$vendor_role->add_cap( 'delete_published_event_magic_ticket' );
						$vendor_role->add_cap( 'delete_published_event_magic_tickets' );
					}
				}
				
				// Submit Products
				if( isset( $options[ 'submit_products' ] ) && $options[ 'submit_products' ] == 'yes' ) {
					$vendor_role->remove_cap( 'edit_products' );
					$vendor_role->remove_cap( 'manage_products' );
					$vendor_role->remove_cap( 'read_products' );
				} else {
					$vendor_role->add_cap( 'edit_products' );
					$vendor_role->add_cap( 'manage_products' );
					$vendor_role->add_cap( 'read_products' );
				}
				
				// Publish Products
				if( isset( $options[ 'publish_products' ] ) && $options[ 'publish_products' ] == 'yes' ) {
					$vendor_role->remove_cap( 'publish_products' );
				} else {
					$vendor_role->add_cap( 'publish_products' );
				}
				
				// Live Products Edit
				if( isset( $options[ 'edit_live_products' ] ) && $options[ 'edit_live_products' ] == 'yes' ) {
					$vendor_role->remove_cap( 'edit_published_products' );
				} else {
					$vendor_role->add_cap( 'edit_published_products' );
				}
				
				// Delete Products
				if( isset( $options[ 'delete_products' ] ) && $options[ 'delete_products' ] == 'yes' ) {
					$vendor_role->remove_cap( 'delete_published_products' );
					$vendor_role->remove_cap( 'delete_products' );
				} else {
					$vendor_role->add_cap( 'delete_published_products' );
					$vendor_role->add_cap( 'delete_products' );
				}
				
				// Submit Cuopon
				if( isset( $options[ 'submit_coupons' ] ) && $options[ 'submit_coupons' ] == 'yes' ) {
					$vendor_role->remove_cap( 'edit_shop_coupons' );
					$vendor_role->remove_cap( 'manage_shop_coupons' );
					$vendor_role->remove_cap( 'read_shop_coupons' );
				} else {
					$vendor_role->add_cap( 'edit_shop_coupons' );
					$vendor_role->add_cap( 'manage_shop_coupons' );
					$vendor_role->add_cap( 'read_shop_coupons' );
				}
				
				// Publish Coupon
				if( isset( $options[ 'publish_coupons' ] ) && $options[ 'publish_coupons' ] == 'yes' ) {
					$vendor_role->remove_cap( 'publish_shop_coupons' );
				} else {
					$vendor_role->add_cap( 'publish_shop_coupons' );
				}
				
				// Live Coupon Edit
				if( isset( $options[ 'edit_live_coupons' ] ) && $options[ 'edit_live_coupons' ] == 'yes' ) {
					$vendor_role->remove_cap( 'edit_published_shop_coupons' );
				} else {
					$vendor_role->add_cap( 'edit_published_shop_coupons' );
				}
				
				// Delete Coupon
				if( isset( $options[ 'delete_coupons' ] ) && $options[ 'delete_coupons' ] == 'yes' ) {
					$vendor_role->remove_cap( 'delete_published_shop_coupons' );
					$vendor_role->remove_cap( 'delete_shop_coupons' );
				} else {
					$vendor_role->add_cap( 'delete_published_shop_coupons' );
					$vendor_role->add_cap( 'delete_shop_coupons' );
				}
			}
		}
  }
  
  // Product Vendor association on Product save
  function wcpvendors_product_manage_vendor_association( $new_product_id, $wcfm_products_manage_form_data ) {
  	global $WCFM, $WCMp;
  	
  	
		// check post type to be product
		if ( 'product' === get_post_type( $new_product_id ) ) {
			
			$product_post = get_post( $new_product_id );
			
			if ( WC_Product_Vendors_Utils::is_vendor( $product_post->post_author ) ) {
				$vendor_data = WC_Product_Vendors_Utils::get_all_vendor_data( $product_post->post_author );
				if( $vendor_data && !empty( $vendor_data ) ) {
					$vendor_data_term = key( $vendor_data );
		
					// automatically set the vendor term for this product
					wp_set_object_terms( $new_product_id, $vendor_data_term, WC_PRODUCT_VENDORS_TAXONOMY );
				}
			}
		}
  }
  
  function wcfm_dokan_get_dashboard_nav( $dokan_urls ) {
  	$dokan_new_urls = array();
  	if( apply_filters( 'wcfm_is_allow_multivendor_dashboard_redirect', true ) ) {
			if( !empty( $dokan_urls ) ) {
				foreach( $dokan_urls as $dokan_nav_key => $dokan_url ) {
					$dokan_url['url'] = wcfm_get_navigation_url( $dokan_nav_key );
					$dokan_new_urls[$dokan_nav_key] = $dokan_url;
				}
			}
		} else {
			$dokan_new_urls = $dokan_urls;
		}
  	return $dokan_new_urls;
  }
  
  function wcfm_get_vendor_list( $all = false, $offset = '', $number = '', $search = '', $allow_vendors_list = '', $is_disabled_vendors = true, $vendor_search_data = array() ) {
  	global $WCFM;
  	
  	$is_marketplace = wcfm_is_marketplace();
  	$vendor_arr = array();
		if( $is_marketplace ) {
			if( !wcfm_is_vendor() || apply_filters( 'wcfm_is_allow_get_all_vendor_list_by_force', true ) ) {
				if( $all ) {
					$vendor_arr = array( 0 => __('All', 'wc-frontend-manager' ) );
				} else {
					$vendor_arr = array( '' => __('Choose Vendor ...', 'wc-frontend-manager' ) );
				}
				$wcfm_allow_vendors_list = apply_filters( 'wcfm_allow_vendors_list', $allow_vendors_list, $is_marketplace );
				if( $is_marketplace == 'wcpvendors' ) {
					$args = array(
						'hide_empty'   => false,
						'hierarchical' => false,
					);
					if( $number ) {
						$args['offset'] = $offset;
						$args['number'] = $number;
					}
					if( $search ) {
						$args['search'] = $search;
					}
					if( $wcfm_allow_vendors_list ) {
						$args['include']  = $wcfm_allow_vendors_list;
					}
					$vendors = get_terms( WC_PRODUCT_VENDORS_TAXONOMY, $args );
					
					if( !empty( $vendors ) ) {
						foreach ( $vendors as $vendor ) {
							$vendor_arr[$vendor->term_id] = esc_html( $vendor->name );
						}
					}
				} else {
					$vendor_user_roles = apply_filters( 'wcfm_allwoed_vendor_user_roles', array( 'dc_vendor', 'vendor', 'seller', 'wcfm_vendor' ) );
					if( $is_disabled_vendors ) {
						$vendor_user_roles = apply_filters( 'wcfm_allwoed_vendor_user_roles', array( 'dc_vendor', 'vendor', 'seller', 'wcfm_vendor', 'disable_vendor' ) );
					}
					$args = array(
						'role__in'     => $vendor_user_roles,
						'orderby'      => 'login',
						'order'        => 'ASC',
						'include'      => $wcfm_allow_vendors_list,
						'count_total'  => false,
						'fields'       => array( 'ID', 'display_name', 'user_login' )
					 ); 
					if( $number ) {
						$args['offset'] = $offset;
						$args['number'] = $number;
					}
					if( $search ) {
						//$args['search'] = $search;
						$args['meta_query'] = array(
																			 'relation' => 'OR',
																				array(
																						'key'     => 'first_name',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																				array(
																						'key'     => 'last_name',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																				array(
																						'key'     => 'nickname',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																				array(
																						'key'     => 'pv_shop_name',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																				array(
																						'key'     => '_vendor_page_title',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																				array(
																						'key'     => 'dokan_store_name',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																				array(
																						'key'     => 'wcfmmp_store_name',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																				array(
																						'key'     => 'store_name',
																						'value'   => $search,
																						'compare' => 'LIKE'
																				),
																		);
					}
					
					if( !empty( $vendor_search_data ) && is_array( $vendor_search_data ) ) {
						foreach( $vendor_search_data as $search_key => $search_value ) {
							if( !$search_value ) continue;
							if( in_array( $search_key, apply_filters( 'wcfmmp_vendor_list_exclude_search_keys', array( 'v', 'search_term', 'wcfmmp_store_search', 'wcfmmp_store_category', 'wcfmmp_radius_addr', 'wcfmmp_radius_lat', 'wcfmmp_radius_lng', 'wcfmmp_radius_range', 'pagination_base', 'wcfm_paged', 'paged', 'per_row', 'per_page', 'excludes', 'orderby', 'has_product', 'theme', 'nonce', 'lang' ) ) ) ) continue;
							if( $search ) $args['meta_query']['relation'] = 'AND';
							$args['meta_query'][] = array(
																						 'relation' => 'OR',
																						 array(
																								'key'     => str_replace( 'wcfmmp_store_', '', $search_key ),
																								'value'   => $search_value,
																								'compare' => 'LIKE'
																						),
																						array(
																								'key'     => str_replace( 'wcfmmp_store_', '_wcfm_', $search_key ),
																								'value'   => $search_value,
																								'compare' => 'LIKE'
																						)
																					);
						}
					}
					
					$all_users = get_users( $args );
					if( !empty( $all_users ) ) {
						foreach( $all_users as $all_user ) {
							$vendor_arr[$all_user->ID] = $this->wcfm_get_vendor_store_name_by_vendor( $all_user->ID ) . ' - ' . $all_user->display_name . ' (#' . $all_user->ID . ' - ' . $all_user->user_login . ')';
						}
					}
				}
			}
		}
		
		return $vendor_arr;
	}
	
	public function wcfm_get_vendor_logo_by_vendor( $vendor_id ) {
		global $WCFM, $wpdb, $WCMp;
  	
  	$store_logo = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp-blue.png' );
  	
  	if( !$vendor_id ) return $store_logo;
  	$vendor_id = absint( $vendor_id );
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$logo = get_user_meta( $vendor_id, '_wcv_store_icon_id', true );
  		$logo_image_url = wp_get_attachment_image_src( $logo, 'thumbnail' );
  		if ( !empty( $logo_image_url ) ) {
  			$store_logo = $logo_image_url[0];
  		}
		} elseif( $marketplece == 'wcmarketplace' ) {
			$vendor = get_wcmp_vendor($vendor_id);
			if ( $vendor && $vendor->image ) {
				$store_logo = $vendor->image;
			}
		} elseif( $marketplece == 'wcpvendors' ) {
			$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
			$logo = ! empty( $vendor_data['logo'] ) ? $vendor_data['logo'] : '';
			$logo_image_url = wp_get_attachment_image_src( $logo, 'full' );
			if ( !empty( $logo_image_url ) ) {
				$store_logo = $logo_image_url[0];
			}
		} elseif( $marketplece == 'dokan' ) {
			$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
			$gravatar       = isset( $vendor_data['gravatar'] ) ? absint( $vendor_data['gravatar'] ) : 0;
			$gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : '';
	
			if ( !empty( $gravatar_url ) ) {
				$store_logo = $gravatar_url;
			}
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
			$gravatar       = isset( $vendor_data['gravatar'] ) ? absint( $vendor_data['gravatar'] ) : 0;
			$gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : '';
	
			if ( !empty( $gravatar_url ) ) {
				$store_logo = $gravatar_url;
			}
		}
		
		return apply_filters( 'wcfmmp_store_logo', $store_logo, $vendor_id );
	}
	
	public function wcfm_get_vendor_store_name_by_vendor( $vendor_id ) {
		global $WCFM, $wpdb, $WCMp;
  	
  	$vendor_store = '';
  	
  	if( !$vendor_id ) return $vendor_store;
  	$vendor_id = absint( $vendor_id );
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$shop_name = get_user_meta( $vendor_id, 'pv_shop_name', true );
			if( $shop_name ) $vendor_store = $shop_name;
		} elseif( $marketplece == 'wcmarketplace' ) {
			$vendor = get_wcmp_vendor( $vendor_id );
			if( $vendor ) {
				$shop_name = get_user_meta( $vendor_id , '_vendor_page_title', true);
				$store_name = get_user_meta( $vendor_id, 'store_name', true );
				if( $shop_name ) { $vendor_store = $shop_name; }
			}
		} elseif( $marketplece == 'wcpvendors' ) {
			$vendor_data = get_term( $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );
			$shop_name = $vendor_data->name;
			if( $shop_name ) { $vendor_store = $shop_name; }
		} elseif( $marketplece == 'dokan' ) {
			$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
			$shop_name     = isset( $vendor_data['store_name'] ) ? esc_attr( $vendor_data['store_name'] ) : '';
			$vendor_user   = get_user_by( 'id', $vendor_id );
			if(  empty( $shop_name ) && $vendor_user ) {
				$shop_name     = $vendor_user->display_name;
			}
			if( $shop_name ) { $vendor_store = $shop_name; }
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$shop_name     = get_user_meta( $vendor_id, 'store_name', true );
			$vendor_user   = get_user_by( 'id', $vendor_id );
			if(  empty( $shop_name ) && $vendor_user ) {
				$shop_name     = $vendor_user->display_name;
			}
			if( $shop_name ) { $vendor_store = esc_attr($shop_name); }
		}
		
		return $vendor_store;
	}
	
	public function wcfm_get_vendor_store_by_vendor( $vendor_id ) {
		global $WCFM, $wpdb, $WCMp;
  	
  	$vendor_store = '&ndash;';
  	
  	if( !$vendor_id ) return $vendor_store;
  	$vendor_id = absint( $vendor_id );
  	
  	$store_open_by = apply_filters( 'wcfm_shop_permalink_open_by', 'target="_blank"', $vendor_id );
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$shop_name = get_user_meta( $vendor_id, 'pv_shop_name', true );
			if( $shop_name ) $vendor_store = $shop_name;
			$shop_link       = WCV_Vendors::get_vendor_shop_page( $vendor_id );
			if( $shop_name ) { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('wcv_vendor_shop_permalink', $shop_link) . '">' . $shop_name . '</a>'; }
			else { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('wcv_vendor_shop_permalink', $shop_link) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
		} elseif( $marketplece == 'wcmarketplace' ) {
			$vendor = get_wcmp_vendor( $vendor_id );
			if( $vendor ) {
				$shop_name = get_user_meta( $vendor_id , '_vendor_page_title', true);
				$store_name = get_user_meta( $vendor_id, 'store_name', true );
				if( $shop_name ) { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('wcmp_vendor_shop_permalink', $vendor->permalink) . '">' . $shop_name . '</a>'; }
				elseif( $store_name ) { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('wcmp_vendor_shop_permalink', $vendor->permalink) . '">' . $store_name . '</a>'; }
				else { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('wcmp_vendor_shop_permalink', $vendor->permalink) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
			}
		} elseif( $marketplece == 'wcpvendors' ) {
			$vendor_data = get_term( $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );
			$shop_name = $vendor_data->name;
			$shop_link = get_term_link( $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );
			if( !is_wp_error( $shop_link ) ) {
				if( $shop_name ) { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('wcpv_vendor_shop_permalink', $shop_link) . '">' . $shop_name . '</a>'; }
				else { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('wcpv_vendor_shop_permalink', $shop_link) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
			}
		} elseif( $marketplece == 'dokan' ) {
			$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
			$shop_name     = isset( $vendor_data['store_name'] ) ? esc_attr( $vendor_data['store_name'] ) : '';
			$vendor_user   = get_user_by( 'id', $vendor_id );
			if(  empty( $shop_name ) && $vendor_user ) {
				$shop_name     = $vendor_user->display_name;
			}
			$shop_link     = dokan_get_store_url( $vendor_id );
			if( $shop_name ) { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('dokan_vendor_shop_permalink', $shop_link) . '">' . $shop_name . '</a>'; }
			else { $vendor_store = '<a ' . $store_open_by . ' href="' . apply_filters('dokan_vendor_shop_permalink', $shop_link) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$shop_name     = get_user_meta( $vendor_id, 'store_name', true );
			$vendor_user   = get_user_by( 'id', $vendor_id );
			if(  empty( $shop_name ) && $vendor_user ) {
				$shop_name     = $vendor_user->display_name;
			}
			$shop_link     = wcfmmp_get_store_url( $vendor_id );
			if( $shop_name ) { $vendor_store = '<a class="wcfm_dashboard_item_title" ' . $store_open_by . ' href="' . apply_filters( 'wcfmmp_vendor_shop_permalink', $shop_link, $vendor_id ) . '">' . esc_attr( $shop_name ) . '</a>'; }
			else { $vendor_store = '<a class="wcfm_dashboard_item_title" ' . $store_open_by . ' href="' . apply_filters( 'wcfmmp_vendor_shop_permalink', $shop_link, $vendor_id ) . '">' . __('Shop', 'wc-frontend-manager') . '</a>'; }
		}
		
		return $vendor_store;
	}
	
	public function wcfm_get_vendor_address_by_vendor( $vendor_id ) {
		global $WCFM, $wpdb, $WCMp;
  	
  	$vendor_address = '';
  	$addr_1 = '';
		$addr_2 = '';
		$country = '';
		$city  = '';
		$state = '';
		$zip  = '';
  	
  	if( !$vendor_id ) return $vendor_address;
  	$vendor_id = absint( $vendor_id );
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$addr_1  = get_user_meta( $vendor_id, '_wcv_store_address1', true );
			$addr_2  = get_user_meta( $vendor_id, '_wcv_store_address2', true );
			$country  = get_user_meta( $vendor_id, '_wcv_store_country', true );
			$city  = get_user_meta( $vendor_id, '_wcv_store_city', true );
			$state  = get_user_meta( $vendor_id, '_wcv_store_state', true );
			$zip  = get_user_meta( $vendor_id, '_wcv_store_postcode', true );
		} elseif( $marketplece == 'wcmarketplace' ) {
			$addr_1  = get_user_meta( $vendor_id, '_vendor_address_1', true );
			$addr_2  = get_user_meta( $vendor_id, '_vendor_address_2', true );
			$country  = get_user_meta( $vendor_id, '_vendor_country', true );
			$city  = get_user_meta( $vendor_id, '_vendor_city', true );
			$state  = get_user_meta( $vendor_id, '_vendor_state', true );
			$zip  = get_user_meta( $vendor_id, '_vendor_postcode', true );
		} elseif( $marketplece == 'wcpvendors' ) {
			
		} elseif( $marketplece == 'dokan' ) {
			$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
			$address         = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
			$addr_1 = isset( $vendor_data['address']['street_1'] ) ? $vendor_data['address']['street_1'] : '';
			$addr_2 = isset( $vendor_data['address']['street_2'] ) ? $vendor_data['address']['street_2'] : '';
			$city    = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
			$zip     = isset( $vendor_data['address']['zip'] ) ? $vendor_data['address']['zip'] : '';
			$country = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
			$state   = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
			$address         = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
			$addr_1 = isset( $vendor_data['address']['street_1'] ) ? $vendor_data['address']['street_1'] : '';
			$addr_2 = isset( $vendor_data['address']['street_2'] ) ? $vendor_data['address']['street_2'] : '';
			$city    = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
			$zip     = isset( $vendor_data['address']['zip'] ) ? $vendor_data['address']['zip'] : '';
			$country = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
			$state   = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
		}
		
		// Country -> States
		$country_obj   = new WC_Countries();
		$countries     = $country_obj->countries;
		$states        = $country_obj->states;
		$country_name = '';
		$state_name = '';
		if( $country ) $country_name = $country;
		if( $state ) $state_name = $state;
		if( $country && isset( $countries[$country] ) ) {
			$country_name = $countries[$country];
		}
		if( $state && isset( $states[$country] ) && is_array( $states[$country] ) ) {
			$state_name = isset($states[$country][$state]) ? $states[$country][$state] : '';
		}
		
		if( $addr_1 ) $vendor_address .= $addr_1;
		if( $addr_2 ) $vendor_address .= "<br />" . $addr_2;
		if( $city ) $vendor_address .= "<br />" . $city;
		if( $state_name ) $vendor_address .= ", " . $state_name;
		if( $country_name ) $vendor_address .= "<br />" . $country_name;
		if( $zip ) $vendor_address .= " - " . $zip;
		
		return $vendor_address;
	}
	
	public function wcfm_get_vendor_email_by_vendor( $vendor_id ) {
		global $WCFM, $wpdb;
		
		$vendor_email = '';
  	if ( $vendor_id ) {
			if( $WCFM->is_marketplace ) {
				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
					$vendor_data = get_userdata( $vendor_id );
					if ( ! empty( $vendor_data ) ) {
						$vendor_email = $vendor_data->user_email;
					}
				} elseif( $WCFM->is_marketplace == 'wcvendors' ) {
					if( WCV_Vendors::is_vendor( $vendor_id ) ) {
						$vendor_data = get_userdata( $vendor_id );
						if ( ! empty( $vendor_data ) ) {
							$vendor_email = $vendor_data->user_email;
						}
					}
				} elseif( $WCFM->is_marketplace == 'wcpvendors' ) {
					$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
					if ( ! empty( $vendor_id ) && ! empty( $vendor_data ) ) {
						$vendor_email = $vendor_data['email'];
					}
				} elseif( $WCFM->is_marketplace == 'dokan' ) {
					if( dokan_is_user_seller( $vendor_id ) ) {
						$vendor_data = get_userdata( $vendor_id );
						if ( ! empty( $vendor_data ) ) {
							$vendor_email = $vendor_data->user_email;
						}
					}
				} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
					if( wcfm_is_vendor( $vendor_id ) ) {
						$vendor_data = get_userdata( $vendor_id );
						if ( ! empty( $vendor_data ) ) {
							$shop_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
							$vendor_email = !empty( $shop_data['store_email'] ) ? $shop_data['store_email'] : $vendor_data->user_email;
							$vendor_email = apply_filters( 'wcfmmp_store_email', $vendor_email, $vendor_id );
						}
					}
				}
			}
		}
		
		if( !$vendor_email || empty( $vendor_email ) ) $vendor_email = '';
		
		return $vendor_email;
		
	}
	
	public function wcfm_get_vendor_phone_by_vendor( $vendor_id ) {
		global $WCFM, $wpdb;
		
		$vendor_phone = '';
  	if ( $vendor_id ) {
			if( $WCFM->is_marketplace ) {
				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
					$vendor_phone = get_user_meta( $vendor_id, '_vendor_phone', true );
				} elseif( $WCFM->is_marketplace == 'wcvendors' ) {
					if( WCV_Vendors::is_vendor( $vendor_id ) ) {
						$vendor_phone = get_user_meta( $vendor_id, '_wcv_store_phone', true );
					}
				} elseif( $WCFM->is_marketplace == 'wcpvendors' ) {
					// No store phone setting
				} elseif( $WCFM->is_marketplace == 'dokan' ) {
					if( dokan_is_user_seller( $vendor_id ) ) {
						$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
						if ( ! empty( $vendor_data ) ) {
							$vendor_phone = isset( $vendor_data['phone'] ) ? esc_attr( $vendor_data['phone'] ) : '';
						}
					}
				} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
					if( wcfm_is_vendor( $vendor_id ) ) {
						$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
						if ( ! empty( $vendor_data ) ) {
							$vendor_phone = isset( $vendor_data['phone'] ) ? esc_attr( $vendor_data['phone'] ) : '';
						}
					}
				}
			}
		}
		
		if( !$vendor_phone || empty( $vendor_phone ) ) $vendor_phone = '';
		
		return $vendor_phone;
	}
	
	/**
   * WCFM is admin fee mode?
   */
  function wcfm_is_admin_fee_mode( $is_admin_fee ) {
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcmarketplace' ) {
  		global $WCMp;
			if (isset($WCMp->vendor_caps->payment_cap['revenue_sharing_mode'])) {
				if ($WCMp->vendor_caps->payment_cap['revenue_sharing_mode'] == 'admin') {
					$is_admin_fee = true;
				}
			}
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
			$vendor_commission_for = isset( $wcfm_commission_options['commission_for'] ) ? $wcfm_commission_options['commission_for'] : 'vendor';
			if( $vendor_commission_for == 'admin' ) $is_admin_fee = true;
		}
  	return $is_admin_fee;
  }
  
  /**
   * Gross sales by Vendor
   */
  function wcfm_get_gross_sales_by_vendor( $vendor_id = '', $interval = '7day', $is_paid = false, $order_id = 0, $filter_date_form = '', $filter_date_to = '' ) {
  	global $WCFM, $wpdb, $WCMp, $WCFMmp;
  	
  	if( $vendor_id ) $vendor_id = absint($vendor_id);
  	
  	$gross_sales = 0;
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$sql = "SELECT order_id, GROUP_CONCAT(product_id) product_ids, SUM( commission.total_shipping ) AS total_shipping FROM {$wpdb->prefix}pv_commission AS commission";
			$sql .= " WHERE 1=1";
			if( $vendor_id ) $sql .= " AND `vendor_id` = {$vendor_id}";
			if( $order_id ) {
				$sql .= " AND `order_id` = {$order_id}";
			} else {
				if( $is_paid ) {
					$sql .= " AND commission.status = 'paid'";
				}
				$sql = wcfm_query_time_range_filter( $sql, 'time', $interval, $filter_date_form, $filter_date_to );
			}
			$sql .= " GROUP BY commission.order_id";
			
			$gross_sales_whole_week = $wpdb->get_results( $sql );
			if( !empty( $gross_sales_whole_week ) ) {
				foreach( $gross_sales_whole_week as $net_sale_whole_week ) {
					if( $net_sale_whole_week->order_id ) {
						$order_post_title = get_the_title( $net_sale_whole_week->order_id );
						if( !$order_post_title ) continue;
						try {
							$order       = wc_get_order( $net_sale_whole_week->order_id );
							$line_items  = $order->get_items( 'line_item' );
							$valid_items = (array) $order_item_ids = explode( ",", $net_sale_whole_week->product_ids );
							
							foreach( $line_items as $key => $line_item ) {
								if( $line_item->get_product_id() == 0 ) {
									$_product_id = wc_get_order_item_meta( $key, '_product_id', true );
									$_variation_id = wc_get_order_item_meta( $key, '_variation_id', true );
									if ( in_array( $_product_id, $valid_items ) || in_array( $_variation_id, $valid_items ) ) {
										$gross_sales += (float) sanitize_text_field( $line_item->get_total() );
										if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
											if(WC_Vendors::$pv_options->get_option( 'give_tax' )) {
												$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
											}
										} else {
											if( get_option('wcvendors_vendor_give_taxes') ) {
												$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
											}
										}
									}
								} elseif ( in_array( $line_item->get_variation_id(), $valid_items ) || in_array( $line_item->get_product_id(), $valid_items ) ) {
									$gross_sales += (float) sanitize_text_field( $line_item->get_total() );
									if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
										if(WC_Vendors::$pv_options->get_option( 'give_tax' )) {
											$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
										}
									} else {
										if( get_option('wcvendors_vendor_give_taxes') ) {
											$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
										}
									}
								}
							}
						} catch (Exception $e) {
							continue;
						}
					}
					if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
						if(WC_Vendors::$pv_options->get_option( 'give_shipping' )) {
							$gross_sales += (float) $net_sale_whole_week->total_shipping;
						}
					} else {
						if( get_option('wcvendors_vendor_give_shipping') ) {
							$gross_sales += (float) $net_sale_whole_week->total_shipping;
						}
					}
				}
			}
		} elseif( $marketplece == 'wcmarketplace' ) {
			$sql = "SELECT order_item_id, shipping, shipping_tax_amount FROM {$wpdb->prefix}wcmp_vendor_orders AS commission";
			$sql .= " WHERE 1=1";
			if( $vendor_id ) $sql .= " AND `vendor_id` = {$vendor_id}";
			if( $order_id ) {
				$sql .= " AND `order_id` = {$order_id}";
			} else {
				$sql .= " AND `line_item_type` = 'product' AND `commission_id` != 0 AND `commission_id` != '' AND `is_trashed` != 1";
				if( $is_paid ) {
					$sql .= " AND commission.commission_status = 'paid'";
					$sql = wcfm_query_time_range_filter( $sql, 'commission_paid_date', $interval, $filter_date_form, $filter_date_to );
				} else {
					$sql = wcfm_query_time_range_filter( $sql, 'created', $interval, $filter_date_form, $filter_date_to );
				}
			}
			
			$gross_sales_whole_week = $wpdb->get_results( $sql );
			if( !empty( $gross_sales_whole_week ) ) {
				foreach( $gross_sales_whole_week as $net_sale_whole_week ) {
					if( $net_sale_whole_week->order_item_id ) {
						try {
							$line_item = new WC_Order_Item_Product( $net_sale_whole_week->order_item_id );
							$gross_sales += (float) sanitize_text_field( $line_item->get_total() );
							if($WCMp->vendor_caps->vendor_payment_settings('give_tax')) {
								$gross_sales += (float) sanitize_text_field( $line_item->get_total_tax() );
								$gross_sales += (float) $net_sale_whole_week->shipping_tax_amount;
							}
							if($WCMp->vendor_caps->vendor_payment_settings('give_shipping')) {
								$gross_sales += (float) $net_sale_whole_week->shipping;
							}
						} catch (Exception $e) {
							continue;
						}
					}
				}
			}
		} elseif( $marketplece == 'wcpvendors' ) {
			$sql = "SELECT SUM( commission.product_amount ) AS total_product_amount, SUM( commission.product_shipping_amount ) AS product_shipping_amount, SUM( commission.product_shipping_tax_amount ) AS product_shipping_tax_amount, SUM( commission.product_tax_amount ) AS product_tax_amount FROM " . WC_PRODUCT_VENDORS_COMMISSION_TABLE . " AS commission";
			$sql .= " WHERE 1=1";
			if( $vendor_id )  $sql .= " AND commission.vendor_id = {$vendor_id}";
			if( $order_id ) {
				$sql .= " AND `order_id` = {$order_id}";
			} else {
				if( $is_paid ) {
					$sql .= " AND commission.commission_status = 'paid'";
					$sql = wcfm_query_time_range_filter( $sql, 'paid_date', $interval, $filter_date_form, $filter_date_to );
				} else {
					$sql = wcfm_query_time_range_filter( $sql, 'order_date', $interval, $filter_date_form, $filter_date_to );
				}
			}
			
			$total_sales = $wpdb->get_results( $sql );
			if( !empty($total_sales) ) {
				foreach( $total_sales as $total_sale ) {
					$gross_sales = $total_sale->total_product_amount + $total_sale->product_shipping_amount + $total_sale->product_shipping_tax_amount + $total_sale->product_tax_amount;
				}
			}
		} elseif( $marketplece == 'dokan' ) {
			$sql = "SELECT SUM( commission.order_total ) AS total_order_amount FROM {$wpdb->prefix}dokan_orders AS commission LEFT JOIN {$wpdb->posts} p ON commission.order_id = p.ID";
			$sql .= " WHERE 1=1";
			if( $vendor_id )  $sql .= " AND commission.seller_id = {$vendor_id}";
			if( $order_id ) {
				$sql .= " AND `commission.order_id` = {$order_id}";
			} else {   
				$status = dokan_withdraw_get_active_order_status_in_comma();
				$sql .= " AND commission.order_status IN ({$status})";
				$sql = wcfm_query_time_range_filter( $sql, 'post_date', $interval, '', '', 'p' );
			}
			
			$total_sales = $wpdb->get_results( $sql );
			if( !empty($total_sales) ) {
				foreach( $total_sales as $total_sale ) {
					$gross_sales = $total_sale->total_order_amount;
				}
			}
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$sql = "SELECT ID, order_id, item_id, item_total, item_sub_total, refunded_amount, shipping, tax, shipping_tax_amount FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
			$sql .= " WHERE 1=1";
			if( $vendor_id ) $sql .= " AND `vendor_id` = {$vendor_id}";
			if( $order_id ) {
				$sql .= " AND `order_id` = {$order_id}";
				//$sql .= " AND `is_refunded` != 1";
			} else {
				$sql .= apply_filters( 'wcfm_order_status_condition', '', 'commission' );
				$sql .= " AND `is_trashed` = 0";
				if( $is_paid ) {
					$sql .= " AND commission.withdraw_status = 'completed'";
					$sql = wcfm_query_time_range_filter( $sql, 'commission_paid_date', $interval, $filter_date_form, $filter_date_to );
				} else {
					$sql = wcfm_query_time_range_filter( $sql, 'created', $interval, $filter_date_form, $filter_date_to );
				}
			}
			
			$gross_sales_whole_week = $wpdb->get_results( $sql );
			$gross_commission_ids = array();
			$gross_total_refund_amount = 0;
			if( !empty( $gross_sales_whole_week ) ) {
				foreach( $gross_sales_whole_week as $net_sale_whole_week ) {
					$gross_commission_ids[] = $net_sale_whole_week->ID;
					$gross_total_refund_amount += (float) sanitize_text_field( $net_sale_whole_week->refunded_amount );
				}
			
			  if( !empty( $gross_commission_ids ) ) {
					try {
						if( apply_filters( 'wcfmmmp_gross_sales_respect_setting', true ) ) {
							$gross_sales = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta_sum( $gross_commission_ids, 'gross_total' );
						} else {
							$gross_sales = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta_sum( $gross_commission_ids, 'gross_sales_total' );
						}
						
						/*if( $WCFMmp->wcfmmp_vendor->is_vendor_deduct_discount( $vendor_id, $net_sale_whole_week->order_id ) ) {
							$gross_sales += (float) sanitize_text_field( $net_sale_whole_week->item_total );
						} else {
							$gross_sales += (float) sanitize_text_field( $net_sale_whole_week->item_sub_total );
						}
						if( $is_vendor_get_tax = $WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $vendor_id ) ) {
							//$gross_sales += (float) $net_sale_whole_week->tax;
							
							// WC Refund Support - 3.0.4
							$gross_sales += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $net_sale_whole_week->ID, 'gross_tax_cost' );
						}
						if( $WCFMmp->wcfmmp_vendor->is_vendor_get_shipping( $vendor_id ) ) {
							$gross_sales += (float) apply_filters( 'wcfmmmp_gross_sales_shipping_cost', $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $net_sale_whole_week->ID, 'gross_shipping_cost' ), $vendor_id );
							if( $is_vendor_get_tax ) {
								$gross_sales += (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $net_sale_whole_week->ID, 'gross_shipping_tax' );
							}
						}*/
						
						
						// Deduct Refunded Amount
						$gross_sales -= (float) $gross_total_refund_amount;
					} catch (Exception $e) {
						//continue;
					}
				}
			}
		}

		if( !$gross_sales ) $gross_sales = 0;
		
		return $gross_sales;
  }
	
	/**
   * Total commission paid by Admin
   */
  function wcfm_get_commission_by_vendor( $vendor_id = '', $interval = '7day', $is_paid = false, $order_id = 0, $filter_date_form = '', $filter_date_to = '' ) {
  	global $WCFM, $wpdb, $WCMp;
  	
  	if( $vendor_id ) $vendor_id = absint($vendor_id);
  	
  	$commission = 0;
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$commission_table = 'pv_commission'; 
  		$total_due = 'total_due';
  		$total_shipping = 'total_shipping';
  		$tax = 'tax';
  		$shipping_tax = 'tax';
  		$status = 'status';
  		$time = 'time';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
		} elseif( $marketplece == 'wcmarketplace' ) {
			$commission_table = 'wcmp_vendor_orders'; 
  		$total_due = 'commission_amount';
  		$total_shipping = 'shipping';
  		$tax = 'tax';
  		$shipping_tax = 'shipping_tax_amount';
  		$status = 'commission_status';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
  		if( $is_paid )
  			$time = 'commission_paid_date';
  		else
  			$time = 'created';
		} elseif( $marketplece == 'wcpvendors' ) {
			$commission_table = 'wcpv_commissions'; 
  		$total_due = 'total_commission_amount';
  		$total_shipping = 'product_shipping_amount';
  		$tax = 'product_tax_amount';
  		$shipping_tax = 'product_shipping_tax_amount';
  		$status = 'commission_status';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
  		if( $is_paid )
  			$time = 'paid_date';
  		else
  		  $time = 'order_date';
		} elseif( $marketplece == 'dokan' ) {
			$order_status = apply_filters( 'wcfm_dokan_allowed_order_status', array( 'completed', 'processing', 'on-hold' ) );
			$commission_table = 'dokan_orders'; 
  		$total_due = 'net_amount';
  		$time = 'post_date';
  		$vendor_handler = 'seller_id';
  		$table_handler = 'p';
  		if( $is_paid ) {
  			$sql = "SELECT SUM( withdraw.amount ) AS amount FROM {$wpdb->prefix}dokan_withdraw AS withdraw";
  			$sql .= " WHERE 1=1";
  			if( $vendor_id ) $sql .= " AND withdraw.user_id = {$vendor_id}";
  			$sql .= " AND withdraw.status = 1";
  			$sql = wcfm_query_time_range_filter( $sql, 'date', $interval, $filter_date_form, $filter_date_to, 'withdraw' );
  			$total_commissions = $wpdb->get_results( $sql );
  			$commission = 0;
				if( !empty($total_commissions) ) {
					foreach( $total_commissions as $total_commission ) {
						$commission += $total_commission->amount;
					}
				}
				if( !$commission ) $commission = 0;
				return $commission;
  		}
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$commission_table = 'wcfm_marketplace_orders'; 
  		$total_due = 'total_commission';
  		$total_shipping = 'shipping';
  		$tax = 'tax';
  		$shipping_tax = 'shipping_tax_amount';
  		$status = 'withdraw_status';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
  		if( $is_paid )
  			$time = 'commission_paid_date';
  		else
  			$time = 'created';
		}
  	
		if( $marketplece == 'dokan' ) {
			$order_status = apply_filters( 'wcfm_dokan_allowed_order_status', array( 'completed', 'processing', 'on-hold' ) );
			$sql = "SELECT SUM( commission.{$total_due} ) AS total_due FROM {$wpdb->prefix}{$commission_table} AS commission LEFT JOIN {$wpdb->posts} p ON commission.order_id = p.ID";
		} else {
		  $sql = "SELECT SUM( commission.{$total_due} ) AS total_due, SUM( commission.{$total_shipping} ) AS total_shipping, SUM( commission.{$tax} ) AS tax, SUM( commission.{$shipping_tax} ) AS shipping_tax FROM {$wpdb->prefix}{$commission_table} AS commission";
		}
		
		$sql .= " WHERE 1=1";
		if( $vendor_id ) $sql .= " AND commission.{$vendor_handler} = {$vendor_id}";
		if( $is_paid ) $sql .= " AND (commission.{$status} = 'paid' OR commission.{$status} = 'completed')";
		if( $marketplece == 'wcmarketplace' ) { $sql .= " AND commission.commission_id != 0 AND commission.commission_id != '' AND `is_trashed` != 1"; }
		if( $marketplece == 'dokan' ) {
			$status = dokan_withdraw_get_active_order_status_in_comma();
			$sql .= " AND commission.order_status IN ({$status})";
		}
		if( $marketplece == 'wcfmmarketplace' ) { 
			if( $order_id ) {
				$sql .= " AND `order_id` = {$order_id}";
			} else {
				$sql .= apply_filters( 'wcfm_order_status_condition', '', 'commission' );
				$sql .= " AND `is_refunded` = 0 AND `is_trashed` = 0";
			}
		}
		if( !$order_id )
			$sql = wcfm_query_time_range_filter( $sql, $time, $interval, $filter_date_form, $filter_date_to, $table_handler );
		
		$total_commissions = $wpdb->get_results( $sql );
		$commission = 0;
		if( !empty($total_commissions) ) {
			foreach( $total_commissions as $total_commission ) {
				$commission += $total_commission->total_due;
				if( $marketplece == 'wcvendors' ) {
					if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
						if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) { $commission += $total_commission->total_shipping; } 
						if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) { $commission += $total_commission->tax; }
					} else {
						if ( get_option('wcvendors_vendor_give_taxes') ) { $commission += $total_commission->total_shipping; } 
						if ( get_option('wcvendors_vendor_give_shipping') ) { $commission += $total_commission->tax; }
					}
				} elseif( $marketplece == 'wcmarketplace' ) {
					if($WCMp->vendor_caps->vendor_payment_settings('give_shipping')) { $commission += ( $total_commission->total_shipping == 'NAN' ) ? 0 : $total_commission->total_shipping; } 
					if($WCMp->vendor_caps->vendor_payment_settings('give_tax')) { 
						$commission += ( $total_commission->tax == 'NAN' ) ? 0 : $total_commission->tax;
						$commission += ( $total_commission->shipping_tax == 'NAN' ) ? 0 : $total_commission->shipping_tax;
					}
				}
			}
		}
		if( !$commission ) $commission = 0;
		
		return $commission;
  }
  
  /**
   * Total withdrawal by Vendor
   */
  function wcfm_get_withdrawal_by_vendor( $vendor_id = '', $interval = '7day', $filter_date_form = '', $filter_date_to = '' ) {
  	if( !$vendor_id ) return 0;
  	$withdrawal = $this->wcfm_get_commission_by_vendor( $vendor_id, $interval, true, 0, $filter_date_form, $filter_date_to );
  	return apply_filters( 'wcfm_vendor_withdrawal', $withdrawal, $vendor_id );
  }
  
  /**
   * Total pending amount for Vendor
   */
  function wcfm_get_pending_withdrawal_by_vendor( $vendor_id = '', $interval = '7day', $filter_date_form = '', $filter_date_to = '' ) {
  	global $WCFM, $wpdb, $_POST, $WCFMmp;
  	
  	if( !$vendor_id ) return 0;
  	
  	if( !function_exists( 'wcfmmp_get_store_url' ) ) {
			$earned = $this->wcfm_get_commission_by_vendor( $vendor_id, $interval, false, 0, $filter_date_form, $filter_date_to );
			$withdrawal = $this->wcfm_get_withdrawal_by_vendor( $vendor_id, $interval, $filter_date_form, $filter_date_to );
			$pending_withdrawal = (float)$earned - (float)$withdrawal;
  	} else {
			$pending_withdrawal = 0;
			
			$withdrawal_thresold = $WCFMmp->wcfmmp_withdraw->get_withdrawal_thresold( $vendor_id );
			
			$sql = 'SELECT order_id, total_commission FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
			$sql .= ' WHERE 1=1';
			$sql .= " AND `vendor_id` = {$vendor_id}";
			$sql .= apply_filters( 'wcfm_order_status_condition', '', 'commission' );
			$sql .= " AND commission.withdraw_status IN ('pending', 'cancelled')";
			$sql .= " AND commission.refund_status != 'requested'";
			$sql .= ' AND `is_withdrawable` = 1 AND `is_auto_withdrawal` = 0 AND `is_refunded` = 0 AND `is_trashed` = 0';
			if( $withdrawal_thresold ) $sql .= " AND commission.created <= NOW() - INTERVAL {$withdrawal_thresold} DAY";
			if( $filter_date_form && $filter_date_to ) {
				$sql .= " AND DATE( commission.created ) BETWEEN '" . $filter_date_form . "' AND '" . $filter_date_to . "'";
			}
			
			$wcfm_withdrawals_array = $wpdb->get_results( $sql );
			
			if(!empty($wcfm_withdrawals_array)) {
				foreach($wcfm_withdrawals_array as $wcfm_withdrawals_single) {
					$order_id = $wcfm_withdrawals_single->order_id;
					$order = wc_get_order( $order_id );
					if( !is_a( $order , 'WC_Order' ) ) continue;
					$pending_withdrawal += (float) $wcfm_withdrawals_single->total_commission;
				}
			}
		}
  	
  	return apply_filters( 'wcfm_vendor_pending_withdrawal', $pending_withdrawal, $vendor_id );
  }
  
  /**
   * Total sales by vendor items
   */
  function wcfm_get_total_sell_by_vendor( $vendor_id = '', $interval = '7day' ) {
  	global $WCFM, $wpdb, $WCMp;
  	
  	if( $vendor_id ) $vendor_id = absint($vendor_id);
  	
  	$total_sell = 0;
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$commission_table = 'pv_commission'; 
  		$qty = 'qty';
  		$time = 'time';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
  		$func = 'SUM';
		} elseif( $marketplece == 'wcmarketplace' ) {
			$commission_table = 'wcmp_vendor_orders'; 
  		$qty = 'quantity';
  		$time = 'created';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
  		$func = 'SUM';
		} elseif( $marketplece == 'wcpvendors' ) {
			$commission_table = 'wcpv_commissions'; 
  		$qty = 'product_quantity';
  		$time = 'order_date';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
  		$func = 'SUM';
		} elseif( $marketplece == 'dokan' ) {
			include_once( $WCFM->plugin_path . 'includes/reports/class-dokan-report-sales-by-date.php' );
			$wcfm_report_sales_by_date = new Dokan_Report_Sales_By_Date( $interval );
			$wcfm_report_sales_by_date->calculate_current_range( $interval );
			$report_data   = $wcfm_report_sales_by_date->get_report_data();
			return $report_data->total_items;
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$commission_table = 'wcfm_marketplace_orders'; 
  		$qty = 'quantity';
  		$time = 'created';
  		$vendor_handler = 'vendor_id';
  		$table_handler = 'commission';
  		$func = 'SUM';
		}
  	
  	$sql = "SELECT {$func}( commission.{$qty} ) AS qty FROM {$wpdb->prefix}{$commission_table} AS commission";
		$sql .= " WHERE 1=1";
		if( $vendor_id ) $sql .= " AND commission.{$vendor_handler} = {$vendor_id}";
		if( $marketplece == 'wcmarketplace' ) { $sql .= " AND commission.commission_id != 0 AND commission.commission_id != '' AND `is_trashed` != 1"; }
		if( $marketplece == 'wcfmmarketplace' ) { 
			$sql .= apply_filters( 'wcfm_order_status_condition', '', 'commission' );
			$sql .= " AND `is_refunded` != 1 AND `is_trashed` != 1";
		}
		$sql = wcfm_query_time_range_filter( $sql, $time, $interval, '', '', $table_handler );
		
		$total_sell = $wpdb->get_var( $sql );
		if( !$total_sell ) $total_sell = 0;
		
		return $total_sell;
  }
  
  /**
   * Total commission for an Order
   */
  function wcfm_get_commission_by_order( $order_id = '', $is_paid = false ) {
  	global $WCFM, $wpdb, $WCMp;
  	
  	$order_id = absint($order_id);
  	
  	$commission = 0;
  	
  	$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$commission_table = 'pv_commission'; 
  		$total_due = 'total_due';
  		$total_shipping = 'total_shipping';
  		$tax = 'tax';
  		$shipping_tax = 'tax';
  		$status = 'status';
  		$time = 'time';
  		$table_handler = 'commission';
		} elseif( $marketplece == 'wcmarketplace' ) {
			$commission_table = 'wcmp_vendor_orders'; 
  		$total_due = 'commission_amount';
  		$total_shipping = 'shipping';
  		$tax = 'tax';
  		$shipping_tax = 'shipping_tax_amount';
  		$status = 'commission_status';
  		$table_handler = 'commission';
  		if( $is_paid )
  			$time = 'commission_paid_date';
  		else
  			$time = 'created';
		} elseif( $marketplece == 'wcpvendors' ) {
			$commission_table = 'wcpv_commissions'; 
  		$total_due = 'total_commission_amount';
  		$total_shipping = 'product_shipping_amount';
  		$tax = 'product_tax_amount';
  		$shipping_tax = 'product_shipping_tax_amount';
  		$status = 'commission_status';
  		$table_handler = 'commission';
  		if( $is_paid )
  			$time = 'paid_date';
  		else
  		  $time = 'order_date';
		} elseif( $marketplece == 'dokan' ) {
			$commission_table = 'dokan_orders'; 
  		$total_due = 'net_amount';
  		$status = 'order_status';
  		$table_handler = 'p';
  		if( $is_paid )
  			$is_paid = '';
		} elseif( $marketplece == 'wcfmmarketplace' ) {
			$commission_table = 'wcfm_marketplace_orders'; 
  		$total_due = 'total_commission';
  		$total_shipping = 'shipping';
  		$tax = 'tax';
  		$shipping_tax = 'shipping_tax_amount';
  		$status = 'withdraw_status';
  		$table_handler = 'commission';
  		if( $is_paid )
  			$time = 'commission_paid_date';
  		else
  			$time = 'created';
		}
  	
		if( $marketplece == 'dokan' ) {
			$sql = "SELECT SUM( commission.{$total_due} ) AS total_due FROM {$wpdb->prefix}{$commission_table} AS commission";
		} else {
  		$sql = "SELECT SUM( commission.{$total_due} ) AS total_due, SUM( commission.{$total_shipping} ) AS total_shipping, SUM( commission.{$tax} ) AS tax, SUM( commission.{$shipping_tax} ) AS shipping_tax FROM {$wpdb->prefix}{$commission_table} AS commission";
  	}
		$sql .= " WHERE 1=1";
		if( $order_id ) $sql .= " AND commission.order_id = {$order_id}";
		if( $is_paid ) $sql .= " AND (commission.{$status} = 'paid' OR commission.{$status} = 'completed')";
		if( $marketplece == 'wcmarketplace' ) { $sql .= " AND commission.commission_id != 0 AND commission.commission_id != '' AND `is_trashed` != 1"; }
		if( $marketplece == 'wcfmmarketplace' ) { 
			//$status = get_wcfm_marketplace_active_withdrwal_order_status_in_comma();
			//$sql .= " AND commission.order_status IN ({$status})";
			$sql .= " AND `is_refunded` != 1 AND `is_trashed` != 1";
		}
		
		$total_commissions = $wpdb->get_results( $sql );
		if( !empty($total_commissions) ) {
			foreach( $total_commissions as $total_commission ) {
				$commission = $total_commission->total_due;
				if( $marketplece == 'wcvendors' ) {
					if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
						if ( WC_Vendors::$pv_options->get_option( 'give_tax' ) ) { $commission += $total_commission->total_shipping; } 
						if ( WC_Vendors::$pv_options->get_option( 'give_shipping' ) ) { $commission += $total_commission->tax; }
					} else {
						if ( get_option('wcvendors_vendor_give_taxes') ) { $commission += $total_commission->total_shipping; } 
						if ( get_option('wcvendors_vendor_give_shipping') ) { $commission += $total_commission->tax; }
					}
				} elseif( $marketplece == 'wcmarketplace' ) {
					if($WCMp->vendor_caps->vendor_payment_settings('give_shipping')) { $commission += ( $total_commission->total_shipping == 'NAN' ) ? 0 : $total_commission->total_shipping; } 
					if($WCMp->vendor_caps->vendor_payment_settings('give_tax')) { 
					  $commission += ( $total_commission->tax == 'NAN' ) ? 0 : $total_commission->tax;
					  $commission += ( $total_commission->shipping_tax == 'NAN' ) ? 0 : $total_commission->shipping_tax;
					}
				}
			}
		}
		if( !$commission ) $commission = 0;
		
		return $commission;
  }
  
  function wcfm_get_products_by_vendor( $vendor_id = 0, $post_status = 'any', $custom_args = array() ) {
		global $WCFM;
		
		$vendor_product_list = array();
		
		if( !$vendor_id ) return $vendor_product_list;
		$vendor_id = absint( $vendor_id );
		
		if( $post_status == 'any' ) { $post_status = array('draft', 'pending', 'publish', 'private'); }
		
		$post_count = 9999;
  	$post_loop_offset = 0;
  	$products_arr = array(0);
  	while( $post_loop_offset < $post_count ) {
			$args = array(
								'posts_per_page'   => apply_filters( 'wcfm_break_loop_offset', 100 ),
								'offset'           => $post_loop_offset,
								'orderby'          => 'date',
								'order'            => 'DESC',
								'post_type'        => 'product',
								//'author'	   => get_current_user_id(),
								'post_status'      => $post_status,
								'suppress_filters' => 0,
								'fields'           => 'ids'
							);
			$args = array_merge( $args, $custom_args );
			$is_marketplace = wcfm_is_marketplace();
			if( $is_marketplace ) {
				if( $is_marketplace == 'wcpvendors' ) {
					$args['tax_query'][] = array(
																				'taxonomy' => WC_PRODUCT_VENDORS_TAXONOMY,
																				'field' => 'term_id',
																				'terms' => $vendor_id,
																			);
				} elseif( $is_marketplace == 'wcvendors' ) {
					$args['author'] = $vendor_id;
				} elseif( $is_marketplace == 'wcmarketplace' ) {
					$vendor_term = absint( get_user_meta( $vendor_id, '_vendor_term_id', true ) );
					$args['tax_query'][] = array(
																				'taxonomy' => 'dc_vendor_shop',
																				'field' => 'term_id',
																				'terms' => $vendor_term,
																			);
				} elseif( $is_marketplace == 'dokan' ) {
					$args['author'] = $vendor_id;
				} elseif( $is_marketplace == 'wcfmmarketplace' ) {
					$args['author'] = $vendor_id;
				}
			}
			$args = apply_filters( 'wcfm_products_by_vendor_args', $args );
			
			if( class_exists('WooCommerce_simple_auction') ) {
				remove_all_filters( 'pre_get_posts' );
			}
			
			$vendor_products = get_posts($args);
			if( !empty( $vendor_products ) ) {
				foreach( $vendor_products as $vendor_product ) {
					$vendor_product_list[$vendor_product] = get_post( $vendor_product );
				}
				$post_loop_offset += apply_filters( 'wcfm_break_loop_offset', 100 );
			} else {
				break;
			}
		}
		
		return $vendor_product_list;
	}
	
	/**
	 * Reset Vendor Product Status
	 */
	public function reset_vendor_product_status( $vendor_id, $product_status = 'archived', $status_form = 'publish' ) {
		global $WCFM, $WCFMvm, $wpdb;
		
		$vendor_product_list = $this->wcfm_get_products_by_vendor( $vendor_id, $status_form, array( 'suppress_filters' => true ) );
		if( !empty( $vendor_product_list ) ) {
			foreach( $vendor_product_list as $vendor_product_id => $vendor_product ) {
				
				if( $status_form == 'archived' ) { // Escape manual Archived Products
					$is_product_offline = get_post_meta( $vendor_product_id, '_wcfm_product_offline', true ); 
					if( !$is_product_offline ) continue;
				}
				$update_product = array(
																'ID'           => $vendor_product_id,
																'post_status'  => $product_status,
																'post_type'    => 'product'
															);
				wp_update_post( $update_product, true );
				
				if( $product_status == 'archived' ) {
					update_post_meta( $vendor_product_id, '_wcfm_product_offline', 'yes' );
				} else {
					delete_post_meta( $vendor_product_id, '_wcfm_product_offline' );
				}
			}
		}
	}
	
	function wcfm_get_orders_by_vendor( $vendor_id = 0 ) {
  	global $WCFM, $wpdb;
  	
  	$vendor_order_list = array();
		
		if( !$vendor_id ) return $vendor_order_list;
		$vendor_id = absint($vendor_id);
  	
  	if( $WCFM->is_marketplace == 'wcvendors' ) {
  		$commission_table = 'pv_commission'; 
  		$vendor_handler = 'vendor_id';
		} elseif( $WCFM->is_marketplace == 'wcmarketplace' ) {
			$commission_table = 'wcmp_vendor_orders'; 
  		$vendor_handler = 'vendor_id';
		} elseif( $WCFM->is_marketplace == 'wcpvendors' ) {
			$commission_table = 'wcpv_commissions'; 
  		$vendor_handler = 'vendor_id';
		} elseif( $WCFM->is_marketplace == 'dokan' ) {
			$commission_table = 'dokan_orders'; 
  		$vendor_handler = 'seller_id';
		} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
			$commission_table = 'wcfm_marketplace_orders'; 
  		$vendor_handler = 'vendor_id';
		}
  	
		$sql = "SELECT order_id FROM {$wpdb->prefix}{$commission_table}";
		$sql .= " WHERE 1=1";
		$sql .= " AND {$vendor_handler} = {$vendor_id}";
		$vendor_orders = $wpdb->get_results( $sql );
		
		if( !empty( $vendor_orders ) ) {
			foreach( $vendor_orders as $vendor_order ) {
				$vendor_order_list[] = $vendor_order->order_id;
			}
		}
		
		return $vendor_order_list;
	}
	
	function wcfm_is_article_from_vendor( $article_id, $current_vendor = '' ) {
  	global $WCFM, $wpdb;
  	
  	$is_article_from_vendor = false;

		$article = get_post( $article_id );
		$author_id = $article->post_author;
		if( $author_id && !$current_vendor ) {
			$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			if( $current_vendor == $author_id ) $is_article_from_vendor = true;
		}
		
		return $is_article_from_vendor;
  }
  
  function wcfm_get_vendor_id_from_product( $product_id = '' ) {
  	global $WCFM, $wpdb;
  	
  	$vendor_id = 0;
  	
  	if( $product_id ) {
			if( $WCFM->is_marketplace == 'wcmarketplace' ) {
				$vendor = get_wcmp_product_vendors( $product_id );
				if( $vendor ) $vendor_id = $vendor->id;
			} elseif( $WCFM->is_marketplace == 'wcvendors' ) {
				$author = WCV_Vendors::get_vendor_from_product( $product_id );
				if ( WCV_Vendors::is_vendor( $author ) ) $vendor_id = $author;
			} elseif( $WCFM->is_marketplace == 'wcpvendors' ) {
				$vendor_id = WC_Product_Vendors_Utils::get_vendor_id_from_product( $product_id );
			} elseif( $WCFM->is_marketplace == 'dokan' ) {
				$product = get_post( $product_id );
				$author = $product->post_author;
				if ( dokan_is_user_seller( $author ) ) $vendor_id = $author;
			} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
				$product = get_post( $product_id );
				if( $product ) {
					$author = $product->post_author;
					if ( wcfm_is_vendor( $author ) ) $vendor_id = $author;
				}
			}
		}
		
		if( !$vendor_id || empty( $vendor_id ) ) $vendor_id = 0;
		
		return $vendor_id;
  }
  
  function wcfm_is_product_from_vendor( $product_id, $current_vendor = '' ) {
  	global $WCFM, $wpdb;
  	
  	$vendor_id = 0;
  	$is_product_from_vendor = false;
  	if( $WCFM->is_marketplace == 'wcmarketplace' ) {
  		$vendor = get_wcmp_product_vendors( $product_id );
  		if( $vendor ) $vendor_id = $vendor->id;
  		if( $vendor_id && !$current_vendor ) {
  			$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
  			$current_vendor_term = get_user_meta( $current_vendor, '_vendor_term_id', true );
  			if( $current_vendor == $vendor_id ) $is_product_from_vendor = true;
  		}
		} elseif( $WCFM->is_marketplace == 'wcvendors' ) {
			$author = WCV_Vendors::get_vendor_from_product( $product_id );
			if ( WCV_Vendors::is_vendor( $author ) ) $vendor_id = $author;
			if( $vendor_id && !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
				if( $current_vendor == $vendor_id ) $is_product_from_vendor = true;
			}
		} elseif( $WCFM->is_marketplace == 'wcpvendors' ) {
			$vendor_id = WC_Product_Vendors_Utils::get_vendor_id_from_product( $product_id );
			if( $vendor_id && !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', WC_Product_Vendors_Utils::get_logged_in_vendor() );
				if( $current_vendor == $vendor_id ) $is_product_from_vendor = true;
			}
		} elseif( $WCFM->is_marketplace == 'dokan' ) {
			$product = get_post( $product_id );
			$author = $product->post_author;
			if ( dokan_is_user_seller( $author ) ) $vendor_id = $author;
			if( $vendor_id && !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
				if( $current_vendor == $vendor_id ) $is_product_from_vendor = true;
			}
		} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
			$product = get_post( $product_id );
			$author = $product->post_author;
			if ( wcfm_is_vendor( $author ) ) $vendor_id = $author;
			if( $vendor_id && !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
				if( $current_vendor == $vendor_id ) $is_product_from_vendor = true;
			}
		}
		
		return $is_product_from_vendor;
  }
  
  function wcfm_is_order_for_vendor( $order_id, $current_vendor = '' ) {
  	global $WCFM, $wpdb;
  	
  	$is_order_for_vendor = true;
  	if( !wcfm_is_marketplace() ) return $is_order_for_vendor;
  	if( !wcfm_is_vendor() ) return $is_order_for_vendor;
  	
  	$order_id = absint($order_id);
  	
  	if( $WCFM->is_marketplace == 'wcvendors' ) {
  		$commission_table = 'pv_commission'; 
  		$vendor_handler = 'vendor_id';
  		if( !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			}
		} elseif( $WCFM->is_marketplace == 'wcmarketplace' ) {
			$commission_table = 'wcmp_vendor_orders'; 
  		$vendor_handler = 'vendor_id';
  		if( !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			}
		} elseif( $WCFM->is_marketplace == 'wcpvendors' ) {
			$commission_table = 'wcpv_commissions'; 
  		$vendor_handler = 'vendor_id';
  		if( !$current_vendor ) {
  			$current_vendor   = apply_filters( 'wcfm_current_vendor_id', WC_Product_Vendors_Utils::get_logged_in_vendor() );
  		}
		} elseif( $WCFM->is_marketplace == 'dokan' ) {
			$commission_table = 'dokan_orders'; 
  		$vendor_handler = 'seller_id';
  		if( !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			}
		} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
			$commission_table = 'wcfm_marketplace_orders'; 
  		$vendor_handler = 'vendor_id';
  		if( !$current_vendor ) {
				$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			}
		}
  	
		$sql = "SELECT * FROM {$wpdb->prefix}{$commission_table} AS commission";
		$sql .= " WHERE 1=1";
		if( $order_id ) $sql .= " AND commission.order_id = {$order_id}";
		if( $current_vendor ) $sql .= " AND commission.{$vendor_handler} = {$current_vendor}";
		$vendor_order_data = $wpdb->get_results( $sql );
		if( empty($vendor_order_data) ) { $is_order_for_vendor = false; }
		
		return $is_order_for_vendor;
	}
	
	function wcfm_is_component_for_vendor( $component_id, $component = '', $current_vendor = '' ) {
  	global $WCFM, $wpdb;
  	
  	$is_component_for_vendor = true;
  	if( !wcfm_is_marketplace() ) return $is_component_for_vendor;
  	if( !wcfm_is_vendor() ) return $is_component_for_vendor;
  	if( !$component ) return $is_component_for_vendor;
  	
  	$component_id = absint($component_id);
  	
  	if( !$component_id ) return $is_component_for_vendor;
  	
  	if( !$current_vendor ) {
			$current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		}
  	
  	switch( $component ) {
  		
  		case 'article':
				$article = get_post( $component_id );
				$author_id = $article->post_author;
				if( $author_id ) {
					if( $current_vendor != $author_id ) $is_component_for_vendor = false;
				}
			break;
			
			case 'product':
				$article = get_post( $component_id );
				$author_id = $article->post_author;
				if( $author_id ) {
					if( $current_vendor != $author_id ) $is_component_for_vendor = false;
				}
			break;
			
			case 'coupon':
				$coupon = get_post( $component_id );
				$author_id = $coupon->post_author;
				if( $author_id ) {
					if( $current_vendor != $author_id ) $is_component_for_vendor = false;
				}
			break;
			
			case 'resource':
				$resource = get_post( $component_id );
				$author_id = $resource->post_author;
				if( $author_id ) {
					if( $current_vendor != $author_id ) $is_component_for_vendor = false;
				}
			break;
  		
  	  case 'order':
				$sql = "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
				$sql .= " WHERE 1=1";
				$sql .= " AND commission.order_id = {$component_id}";
				$sql .= " AND commission.vendor_id = {$current_vendor}";
				$vendor_order_data = $wpdb->get_results( $sql );
				if( empty($vendor_order_data) ) { $is_component_for_vendor = false; }
			break;
			
			case 'booking':
				$booking           = new WC_Booking( $component_id );
				$product_id        = $booking->get_product_id( 'edit' );
				$booking_vendor_id = $this->wcfm_get_vendor_id_from_product( $product_id );
				if( empty($booking_vendor_id) || ( $booking_vendor_id != $current_vendor ) ) { $is_component_for_vendor = false; }
			break;
			
			case 'inquiry':
				$sql = "SELECT * FROM {$wpdb->prefix}wcfm_enquiries AS commission";
				$sql .= " WHERE 1=1";
				$sql .= " AND commission.ID = {$component_id}";
				$sql .= " AND commission.vendor_id = {$current_vendor}";
				$vendor_order_data = $wpdb->get_results( $sql );
				if( empty($vendor_order_data) ) { $is_component_for_vendor = false; }
			break;
			
			case 'support':
				$sql = "SELECT * FROM {$wpdb->prefix}wcfm_support AS commission";
				$sql .= " WHERE 1=1";
				$sql .= " AND commission.ID = {$component_id}";
				$sql .= " AND commission.vendor_id = {$current_vendor}";
				$vendor_support_data = $wpdb->get_results( $sql );
				if( empty($vendor_support_data) ) { $is_component_for_vendor = false; }
			break;
			
			case 'delivery':
				$delivery_vendor_id = get_user_meta( $component_id, '_wcfm_vendor', true );
				if( empty($delivery_vendor_id) || ( $delivery_vendor_id != $current_vendor ) ) { $is_component_for_vendor = false; }
			break;
			
			case 'customer':
				$customer_vendor_id = get_user_meta( $component_id, '_wcfm_vendor', true );
				if( empty($customer_vendor_id) || ( $customer_vendor_id != $current_vendor ) ) { $is_component_for_vendor = false; }
			break;
			
			case 'staff':
				$staff_vendor_id = get_user_meta( $component_id, '_wcfm_vendor', true );
				if( empty($staff_vendor_id) || ( $staff_vendor_id != $current_vendor ) ) { $is_component_for_vendor = false; }
			break;
		}
		
		return apply_filters( 'wcfm_is_component_for_vendor', $is_component_for_vendor, $component_id, $component, $current_vendor );
	}
  
  function wcfm_get_vendor_email_from_product( $product_id ) {
  	global $WCFM, $wpdb;
  	
  	$vendor_email = 0;
  	if ( $product_id ) {
			if( $WCFM->is_marketplace ) {
				if( $WCFM->is_marketplace == 'wcmarketplace' ) {
				  $vendor = get_wcmp_product_vendors( $product_id );
					if( $vendor ) {
						$vendor_id = $vendor->id;
						$vendor_data = get_userdata( $vendor_id );
						if ( ! empty( $vendor_data ) ) {
							$vendor_email = $vendor_data->user_email;
						}
					}
				} elseif( $WCFM->is_marketplace == 'wcvendors' ) {
					$vendor_id = WCV_Vendors::get_vendor_from_product( $product_id );
					if( WCV_Vendors::is_vendor( $vendor_id ) ) {
						$vendor_data = get_userdata( $vendor_id );
						if ( ! empty( $vendor_data ) ) {
							$vendor_email = $vendor_data->user_email;
						}
					}
				} elseif( $WCFM->is_marketplace == 'wcpvendors' ) {
					$vendor_id = WC_Product_Vendors_Utils::get_vendor_id_from_product( $product_id );
					$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
					if ( ! empty( $vendor_id ) && ! empty( $vendor_data ) ) {
						$vendor_email = $vendor_data['email'];
					}
				} elseif( $WCFM->is_marketplace == 'dokan' ) {
					$product = get_post( $product_id );
					$vendor_id = $product->post_author;
					if( dokan_is_user_seller( $vendor_id ) ) {
						$vendor_data = get_userdata( $vendor_id );
						if ( ! empty( $vendor_data ) ) {
							$vendor_email = $vendor_data->user_email;
						}
					}
				} elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
					$product = get_post( $product_id );
					$vendor_id = $product->post_author;
					if( wcfm_is_vendor( $vendor_id ) ) {
						$vendor_data = get_userdata( $vendor_id );
						if ( ! empty( $vendor_data ) ) {
							$vendor_email = $vendor_data->user_email;
						}
					}
				}
			}
		}
		
		if( !$vendor_email || empty( $vendor_email ) ) $vendor_email = '';
		
		return $vendor_email;
  }
  
  public function wcfm_vendor_has_capability( $vendor_id, $capability = '' ) {
  	if( !$capability ) return true;
  	
  	$cacke_key = 'wcfm-vendor-capabilities-' . $vendor_id;
  	$vendor_capability_options = wp_cache_get( $cacke_key, 'wcfm-vendor-capabilities' );
  	
  	if( empty( $vendor_capability_options ) ) {
  		$vendor_capability_options = (array) apply_filters( 'wcfmgs_user_capability', get_option( 'wcfm_capability_options' ), $vendor_id );
  		wp_cache_set( $cacke_key, $vendor_capability_options, 'wcfm-vendor-capabilities' );
  	}
  	
  	
  	$has_capability = ( isset( $vendor_capability_options[$capability] ) ) ? $vendor_capability_options[$capability] : 'no';
		if ( $has_capability == 'yes' ) return false;
		return true;
  }
  
  public function wcfm_vendor_allowed_element_capability( $vendor_id, $capability = '', $element = '' ) {
  	if( !$capability ) return true;
  	if( !$element ) return true;
  	
  	$cacke_key = 'wcfm-vendor-capabilities-' . $vendor_id;
  	$vendor_capability_options = wp_cache_get( $cacke_key, 'wcfm-vendor-capabilities' );
  	
  	if( empty( $vendor_capability_options ) ) {
  		$vendor_capability_options = (array) apply_filters( 'wcfmgs_user_capability', get_option( 'wcfm_capability_options' ), $vendor_id );
  		wp_cache_set( $cacke_key, $vendor_capability_options, 'wcfm-vendor-capabilities' );
  	}
  	
  	$allowed_capabilities    = ( !empty( $vendor_capability_options[$capability] ) ) ? $vendor_capability_options[$capability] : array();
		if( is_array( $allowed_capabilities ) && !empty( $allowed_capabilities ) ) {
			if( !in_array( $element, $allowed_capabilities ) ) return false;
		}
		return true;
  }
  
  public function wcfm_vendor_product_limit( $vendor_id ) {
  	if( !$vendor_id ) return -1;
  	
  	$cacke_key = 'wcfm-vendor-capabilities-' . $vendor_id;
  	$vendor_capability_options = wp_cache_get( $cacke_key, 'wcfm-vendor-capabilities' );
  	
  	if( empty( $vendor_capability_options ) ) {
  		$vendor_capability_options = (array) apply_filters( 'wcfmgs_user_capability', get_option( 'wcfm_capability_options' ), $vendor_id );
  		wp_cache_set( $cacke_key, $vendor_capability_options, 'wcfm-vendor-capabilities' );
  	}
  	
  	$productlimit = ( isset( $vendor_capability_options['productlimit'] ) ) ? $vendor_capability_options['productlimit'] : '';
  	if( ( $productlimit == -1 ) || ( $productlimit == '-1' ) ) $productlimit = -1;
  	elseif( $productlimit ) $productlimit = absint($productlimit);
  	$productlimit = apply_filters( 'wcfm_vendor_verification_product_limit', $productlimit, $vendor_id );
  	$productlimit = apply_filters( 'wcfm_vendor_product_limit', $productlimit, $vendor_id );
  	
  	if( ( $productlimit == -1 ) || ( $productlimit == '-1' ) ) {
			return 0;
		} else {
			if( $productlimit ) $productlimit = absint($productlimit);
			if( $productlimit && ( $productlimit >= 0 ) ) {
				if( $productlimit == 1989 ) {
					return 0;
				}
			} else {
				return '';
			}
		}
		return $productlimit;
  }
  
  public function wcfm_vendor_product_limit_stat( $vendor_id ) {
  	global $WCFM;
  	
  	if( wcfm_is_marketplace() == 'wcpvendors' ) {
  		$vendor_id = apply_filters( 'wcfm_current_vendor_id', WC_Product_Vendors_Utils::get_logged_in_vendor() );
  	}
  	
  	//$products_list  = $this->wcfm_get_products_by_vendor( $vendor_id, apply_filters( 'wcfm_limit_check_status', 'any' ), array( 'suppress_filters' => 1 ) );
		$total_products = wcfm_get_user_posts_count( $vendor_id, 'product', apply_filters( 'wcfm_limit_check_status', 'any' ) ); //count( $products_list );
		$product_limit = $this->wcfm_vendor_product_limit( $vendor_id );
		if( !$product_limit ) $product_limit = '&#8734;';
		$product_limit_stat = '<span class="wcfm_user_usage_stat">' . apply_filters( 'wcfm_vendors_total_products_data', $total_products, $vendor_id ) . '</span> / <span class="wcfm_user_usage_stat_limit">' . $product_limit . '</span>';
		return $product_limit_stat;
  }
  
  function wcfm_get_used_space_by_vendor( $vendor_id = 0 ) {
		global $WCFM;
		
		$vendor_attachment_size = 0;
		
		if( !$vendor_id ) return 0;
		$vendor_id = absint( $vendor_id );
		
		$post_count = 9999;
  	$post_loop_offset = 0;
  	while( $post_loop_offset < $post_count ) {
			$args = array(
								'posts_per_page'   => apply_filters( 'wcfm_break_loop_offset', 100 ),
								'offset'           => $post_loop_offset,
								'orderby'          => 'date',
								'order'            => 'DESC',
								'post_type'        => 'attachment',
								'post_status'      => 'any',
								'suppress_filters' => 0,
								'author'           => $vendor_id,
								'fields'           => 'ids'
							);
			$vendor_attachments = get_posts($args);
			if( !empty( $vendor_attachments ) ) {
				foreach( $vendor_attachments as $vendor_attachment ) {
					if( !class_exists( 'Amazon_Web_Services' ) && !function_exists( 'ud_get_stateless_media' ) ) {
						$attached_file = get_attached_file( $vendor_attachment );
						if( file_exists( $attached_file ) ) {
							$vendor_attachment_size += filesize( $attached_file );
						}
					} else {
						$vendor_attachment_size += 24; // Consider avarage file size 24KB
					}
				}
				$post_loop_offset += apply_filters( 'wcfm_break_loop_offset', 100 );
			} else {
				break;
			}
		}
		
		if( $vendor_attachment_size ) {
			$vendor_attachment_size = round( ($vendor_attachment_size/1024)/1024, 2 );
		}
		
		return $vendor_attachment_size;
	}
  
  public function wcfm_vendor_space_limit( $vendor_id ) {
  	if( !$vendor_id ) return -1;
  	
  	if( wcfm_is_marketplace() == 'wcpvendors' ) {
  		$vendor_id = apply_filters( 'wcfm_current_vendor_id', WC_Product_Vendors_Utils::get_logged_in_vendor() );
  	}
  	
  	$cacke_key = 'wcfm-vendor-capabilities-' . $vendor_id;
  	$vendor_capability_options = wp_cache_get( $cacke_key, 'wcfm-vendor-capabilities' );
  	
  	if( empty( $vendor_capability_options ) ) {
  		$vendor_capability_options = (array) apply_filters( 'wcfmgs_user_capability', get_option( 'wcfm_capability_options' ), $vendor_id );
  		wp_cache_set( $cacke_key, $vendor_capability_options, 'wcfm-vendor-capabilities' );
  	}
  	
  	
  	$spacelimit = ( isset( $vendor_capability_options['spacelimit'] ) ) ? $vendor_capability_options['spacelimit'] : '';
  	if( ( $spacelimit == -1 ) || ( $spacelimit == '-1' ) ) $spacelimit = -1;
  	elseif( $spacelimit ) $spacelimit = absint($spacelimit);
  	$spacelimit = apply_filters( 'wcfm_vendor_verification_space_limit', $spacelimit, $vendor_id );
  	$spacelimit = apply_filters( 'wcfm_vendor_space_limit', $spacelimit, $vendor_id );
  	
  	if( ( $spacelimit == -1 ) || ( $spacelimit == '-1' ) ) {
			return 0;
		} else {
			if( $spacelimit ) $spacelimit = absint($spacelimit);
			if( $spacelimit && ( $spacelimit >= 0 ) ) {
				if( $spacelimit == 1989 ) {
					return 0;
				}
			} else {
				return '';
			}
		}
		return $spacelimit;
  }
  
  public function wcfm_vendor_space_limit_stat( $vendor_id ) {
  	global $WCFM;
  	$used_space  = $this->wcfm_get_used_space_by_vendor( $vendor_id );
		$spacelimit = $this->wcfm_vendor_space_limit( $vendor_id );
		if( !$spacelimit ) $spacelimit = '&#8734;';
		else {
			if( $used_space > $spacelimit ) $used_space = $spacelimit;
			$spacelimit .= ' MB';
		}
		$space_limit_stat = '<span class="wcfm_user_usage_stat">' .apply_filters( 'wcfm_vendors_used_space_data', $used_space, $vendor_id ) . ' MB</span> / <span class="wcfm_user_usage_stat_limit">' . $spacelimit . '</span>';
		return $space_limit_stat;
  }
  
  function wcfm_store_message_types( $message_types ) {
  	$message_types['product_review']    = __( 'Review Product', 'wc-frontend-manager' );
  	$message_types['new_product']       = __( 'New Product', 'wc-frontend-manager' );
  	$message_types['new_taxonomy_term'] = __( 'New Category', 'wc-frontend-manager' );
  	
  	return $message_types;
  }
  
  function is_wcmp_backend_disabled_by_wcfm( $backend_disable_field ) {
  	//$backend_disable_field['text'] = 'wcfm_custom_hide';
  	$backend_disable_field['text'] = __( 'You may manage this using WCfM Capability Controller.', 'wc-frontend-manager' );
  	$backend_disable_field['text'] = sprintf( __('Manage vendor backend access from <a href="%s">WCfM Capability Controller</a>.', 'wc-frontend-manager'), get_wcfm_capability_url() );
  	return $backend_disable_field;
  }
  
  function wcmp_tabs_capability_disabled_by_wcfm( $wcmp_tabs ) {
  	if( apply_filters( 'wcfm_is_allow_wcmp_tabs_manage', true ) ) {
  		if( isset( $wcmp_tabs['capabilities'] ) ) unset( $wcmp_tabs['capabilities'] );
  	}
  	return $wcmp_tabs;
  }
  
  function wcmp_change_stripe_config_by_wcfm( $settings_tab_options ) {
  	if( isset( $settings_tab_options['sections'] ) && isset( $settings_tab_options['sections']['default_settings_section'] ) && isset( $settings_tab_options['sections']['default_settings_section']['fields'] ) && isset( $settings_tab_options['sections']['default_settings_section']['fields']['config_redirect_uri'] ) ) {
  		$settings_tab_options['sections']['default_settings_section']['fields']['config_redirect_uri']['value'] = get_wcfm_settings_url();
  	}
  	
  	return $settings_tab_options;
  }
  
  function wcfm_wcvendors_signup_redirect( $vendor_dashboard_page ) {
  	if( apply_filters( 'wcfm_is_allow_multivendor_dashboard_redirect', true ) ) {
  		return get_wcfm_url();
  	} else {
  		return $vendor_dashboard_page;
  	}
  }
  
  function wcfm_dokan_get_navigation_url( $url, $name ) {
  	if( apply_filters( 'wcfm_is_allow_multivendor_dashboard_redirect', true ) ) {
  		return wcfm_get_navigation_url( $name );
  	} else {
  		return $url;
  	}
  }
}