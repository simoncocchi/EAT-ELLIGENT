<?php
if(!function_exists('wcfm_woocommerce_inactive_notice')) {
	function wcfm_woocommerce_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWooCommerce Frontend Manager is inactive.%s The %sWooCommerce plugin%s must be active for the WooCommerce Frontend Manager to work. Please %sinstall & activate WooCommerce%s', 'wc-frontend-manager' ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfm_woocommerce_version_notice')) {
	function wcfm_woocommerce_version_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sOpps ..!!!%s You are using %sWC %s. WCFM works only with %sWC 3.0+%s. PLease upgrade your WooCommerce version now to make your life easier and peaceful by using WCFM.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<strong>', WC_VERSION . '</strong>', '<strong>', '</strong>' ); ?></p>
		</div>
		<?php
	}
}

/*if(!function_exists('wcfm_wcfmu_inactive_notice')) {
	function wcfm_wcfmu_inactive_notice() {
		$wcfm_options = get_option('wcfm_options');
	  $is_ultimate_notice_disabled = isset( $wcfm_options['ultimate_notice_disabled'] ) ? $wcfm_options['ultimate_notice_disabled'] : 'no';
		if( $is_ultimate_notice_disabled == 'no' ) {
			?>
			<div id="wcfmu_message" class="notice notice-warning">
			<p><?php printf( __( 'Are you missing anything in your front-end Dashboard !!! Then why not go for %sWCfM U >>%s', 'wc-frontend-manager' ), '<a class="primary" target="_blank" href="http://wclovers.com/product/woocommerce-frontend-manager-ultimate/">', '</a>' ); ?></p>
			</div>
			<?php
		}
	}
}*/

if(!function_exists('wcfm_restriction_message_show')) {
	function wcfm_restriction_message_show( $feature = '', $text_only = false, $feature_only = false ) {
		do_action( 'wcfm_restriction_message_show_before',  $feature );
		?>
		<div class="collapse wcfm-collapse">
		  <?php if( apply_filters( 'wcfm_is_allow_restriction_message_page_heading', true ) ) { ?>
				<div class="wcfm-page-headig">
					<span class="wcfmfa fa-times-circle"></span>
					<span class="wcfm-page-heading-text"><?php _e( $feature, 'wc-frontend-manager' ); ?></span>
					<?php do_action( 'wcfm_page_heading' ); ?>
				</div>
			<?php } ?>
		  <div class="wcfm-container">
			  <div class="wcfm-content">
					<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
						<p>
						  <span class="wcfmfa fa-exclamation-triangle"></span>
							<?php
							if( $feature_only ) {
								_e( $feature, 'wc-frontend-manager' );
							} else {
								if( wcfm_is_vendor() ) {
									$wcfm_vendors_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
									$wcfm_membership = get_user_meta( $wcfm_vendors_id, 'wcfm_membership', true );
									if( $wcfm_membership && function_exists( 'wcfm_is_valid_membership' ) && wcfm_is_valid_membership( $wcfm_membership ) ) {
										printf( __( '%s' . $feature . '%s: Your %s membership level doesn\'t give you permission to access this page. Please upgrade your membership, or contact the %sWebsite Manager%s for assistance.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<strong>' . get_the_title( $wcfm_membership ) . '</strong>', '<strong>', '</strong>' );	
									} else {
										printf( __( '%s' . $feature . '%s: You don\'t have permission to access this page. Please contact your %sStore Admin%s for assistance.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<strong>', '</strong>' );
									}
								} else {
									printf( __( '%s' . $feature . '%s: You don\'t have permission to access this page. Please contact your %sStore Admin%s for assistance.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<strong>', '</strong>' );
								}
							}
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		do_action( 'wcfm_restriction_message_show_after',  $feature );
	}
}

if(!function_exists('wcfmu_feature_help_text_show')) {
	function wcfmu_feature_help_text_show( $feature, $only_admin = false, $text_only = false ) {
		
		if( wcfm_is_vendor() ) {
			if( !$only_admin ) {
				if( $text_only ) {
					_e( $feature . ': Please ask your Store Admin to upgrade your dashboard to access this feature.', 'wc-frontend-manager' );
				} else {
					?>
					<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
						<p><span class="wcfmfa exclamation-triangle"></span>
						<?php printf( __( '%s' . $feature . '%s: Please ask your %sStore Admin%s to upgrade your dashboard to access this feature.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
					</div>
					<?php
				}
			}
		} else {
			if( $text_only ) {
				_e( $feature . ': Upgrade your WCFM to WCFM - Ultimate to avail this feature. Disable this notice from settings panel using "Disable Ultimate Notice" option.', 'wc-frontend-manager' );
			} else {
				?>
				<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
					<p><span class="wcfmfa exclamation-triangle"></span><?php printf( __( '%s' . $feature . '%s: Upgrade your WCFM to %sWCFM - Ultimate%s to access this feature. Disable this notice from settings panel using "Disable Ultimate Notice" option.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<a target="_blank" href="https://wclovers.com/product/woocommerce-frontend-manager-ultimate/?utm_source=wp-admin&utm_medium=banner&utm_campaign=promotion&utm_content=ultimate"><strong>', '</strong></a>' ); ?></p>
				</div>
				<?php
			}
		}
	}
}

if(!function_exists('wcfmgs_feature_help_text_show')) {
	function wcfmgs_feature_help_text_show( $feature, $only_admin = false, $text_only = false ) {
		
		if( wcfm_is_vendor() ) {
			if( !$only_admin ) {
				if( $text_only ) {
					_e( $feature . ': Please ask your Store Admin to upgrade your dashboard to access this feature.', 'wc-frontend-manager' );
				} else {
					?>
					<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
						<p><span class="wcfmfa exclamation-triangle"></span>
						<?php printf( __( '%s' . $feature . '%s: Please ask your %sStore Admin%s to upgrade your dashboard to access this feature.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
					</div>
					<?php
				}
			}
		} else {
			if( $text_only ) {
				_e( $feature . ': Associate your WCFM with WCFM - Groups & Staffs to avail this feature.', 'wc-frontend-manager' );
			} else {
				?>
				<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
					<p><span class="wcfmfa exclamation-triangle"></span><?php printf( __( '%s' . $feature . '%s: Associate your WCFM with %sWCFM - Groups & Staffs%s to access this feature.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<a target="_blank" href="https://wclovers.com/product/woocommerce-frontend-manager-groups-staffs/?utm_source=wp-admin&utm_medium=banner&utm_campaign=promotion&utm_content=groups-staffs"><strong>', '</strong></a>' ); ?></p>
				</div>
				<?php
			}
		}
	}
}

if(!function_exists('wcfma_feature_help_text_show')) {
	function wcfma_feature_help_text_show( $feature, $only_admin = false, $text_only = false ) {
		
		if( wcfm_is_vendor() ) {
			if( !$only_admin ) {
				if( $text_only ) {
					_e( $feature . ': Please contact your Store Admin to access this feature.', 'wc-frontend-manager' );
				} else {
					?>
					<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
						<p><span class="wcfmfa exclamation-triangle"></span>
						<?php printf( __( '%s' . $feature . '%s: Please contact your %sStore Admin%s to access this feature.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
					</div>
					<?php
				}
			}
		} else {
			if( $text_only ) {
				_e( $feature . ': Associate your WCFM with WCFM - Analytics to access this feature.', 'wc-frontend-manager' );
			} else {
				?>
				<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
					<p><span class="wcfmfa exclamation-triangle"></span><?php printf( __( '%s' . $feature . '%s: Associate your WCFM with %sWCFM - Analytics%s to access this feature.', 'wc-frontend-manager' ), '<strong>', '</strong>', '<a target="_blank" href="http://wclovers.com/product/woocommerce-frontend-manager-analytics/"><strong>', '</strong></a>' ); ?></p>
				</div>
				<?php
			}
		}
	}
}

if( !function_exists( 'wcfm_is_allow_wcfm' ) ) {
	function wcfm_is_allow_wcfm() {
		if( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$allowed_roles = apply_filters( 'wcfm_allwoed_user_roles',  array( 'administrator', 'shop_manager' ) );
			if ( array_intersect( $allowed_roles, (array) $user->roles ) )  {
				return true;
			}
		}
		return false;
	}
}

if( !function_exists( 'wcfm_is_marketplace' ) ) {
	function wcfm_is_marketplace() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
		
		// WCfM Multivendor Marketplace Check
		$is_marketplace = ( in_array( 'wc-multivendor-marketplace/wc-multivendor-marketplace.php', $active_plugins ) || array_key_exists( 'wc-multivendor-marketplace/wc-multivendor-marketplace.php', $active_plugins ) || class_exists( 'WCFMmp' ) ) ? 'wcfmmarketplace' : false;
		
		// WC Vendors Check
		if( !$is_marketplace )
		  $is_marketplace = ( in_array( 'wc-vendors/class-wc-vendors.php', $active_plugins ) || array_key_exists( 'wc-vendors/class-wc-vendors.php', $active_plugins ) || class_exists( 'WC_Vendors' ) ) ? 'wcvendors' : false;
		
		// WC Marketplace Check
		if( !$is_marketplace )
			$is_marketplace = ( in_array( 'dc-woocommerce-multi-vendor/dc_product_vendor.php', $active_plugins ) || array_key_exists( 'dc-woocommerce-multi-vendor/dc_product_vendor.php', $active_plugins ) || class_exists( 'WCMp' ) ) ? 'wcmarketplace' : false;
		
		// WC Product Vendors Check
		if( !$is_marketplace )
			$is_marketplace = ( in_array( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins ) || array_key_exists( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins ) ) ? 'wcpvendors' : false;
		
		// Dokan Lite Check
		if( !$is_marketplace )
			$is_marketplace = ( in_array( 'dokan-lite/dokan.php', $active_plugins ) || array_key_exists( 'dokan-lite/dokan.php', $active_plugins ) || class_exists( 'WeDevs_Dokan' ) ) ? 'dokan' : false;
		
		return $is_marketplace;
	}
}

/////////////////////////////////////////////////////////////////////////////// WCFM Marketplace Helper Funtions Start ///////////////////////////////////////////////////////////////////////////////////////

if( !function_exists( 'wcfm_is_vendor' ) ) {
	function wcfm_is_vendor( $vendor_id = '' ) {
		if( !$vendor_id ) {
			if( !function_exists( 'is_user_logged_in' ) || !is_user_logged_in() ) return false;
			$vendor_id = get_current_user_id();
		}
		
		$is_marketplace = wcfm_is_marketplace();
		
		if( $is_marketplace ) {
			if( 'wcvendors' == $is_marketplace ) {
			  if ( WCV_Vendors::is_vendor( $vendor_id ) ) return true;
			} elseif( 'wcmarketplace' == $is_marketplace ) {
				if( function_exists( 'is_user_wcmp_vendor' ) && is_user_wcmp_vendor( $vendor_id ) ) return true;
			} elseif( 'wcpvendors' == $is_marketplace ) {
				if( WC_Product_Vendors_Utils::is_vendor( $vendor_id ) && !WC_Product_Vendors_Utils::is_pending_vendor( $vendor_id ) ) return true;
				$vendor_data = get_term( $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );
				if( $vendor_data && !is_wp_error( $vendor_data ) ) {return true;}
			} elseif( 'dokan' == $is_marketplace ) {
				$user = get_userdata( $vendor_id );
				if ( in_array( 'seller', (array) $user->roles ) )  return true;
				//if( user_can( get_current_user_id(), 'seller' ) ) return true;
			} elseif( 'wcfmmarketplace' == $is_marketplace ) {
				$user = get_userdata( $vendor_id );
				if( is_a( $user, 'WP_User' ) ) {
					if ( in_array( 'wcfm_vendor', (array) $user->roles ) )  return apply_filters( 'wcfm_is_vendor', true, $vendor_id );
				}
			}
		}
		
		return apply_filters( 'wcfm_is_vendor', false, $vendor_id );
	}
}

if( !function_exists( 'wcfm_get_vendor_id_by_post' ) ) {
	function wcfm_get_vendor_id_by_post( $product_id = '' ) {
		global $WCFM;
		$vendor_id = '';
		if( $WCFM && $WCFM->wcfm_vendor_support ) {
			$vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $product_id );
		} else {
			$post_author_id = get_post_field( 'post_author', $product_id );
			if( wcfm_is_vendor( $post_author_id ) ) 
				$vendor_id = $post_author_id;
		}
		return apply_filters( 'wcfm_vendor_id_by_post', $vendor_id, $product_id );
	}
}

if( !function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
	function wcfm_get_vendor_store_by_post( $product_id = '' ) {
		$store = false;
		$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		if( $vendor_id  ) {
			if( function_exists( 'wcfm_get_vendor_store' ) )
				$store = wcfm_get_vendor_store( $vendor_id );
			return apply_filters( 'wcfm_vendor_store_by_post', $store, $product_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_by_post', $store, $product_id );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_name_by_post' ) ) {
	function wcfm_get_vendor_store_name_by_post( $product_id = '' ) {
		$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		if( $vendor_id  ) {
			$store = wcfm_get_vendor_store_name( $vendor_id );
			return apply_filters( 'wcfm_vendor_store_name_by_post', $store, $product_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_name_by_post', false, $product_id );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store' ) ) {
	function wcfm_get_vendor_store( $vendor_id = '' ) {
		global $WCFM;
		$store = '';
		if( $WCFM && $WCFM->wcfm_vendor_support ) {
			$store = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( $vendor_id );
		}
		if( $store  ) {
			return apply_filters( 'wcfm_vendor_store', $store, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store', false, $vendor_id );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_name' ) ) {
	function wcfm_get_vendor_store_name( $vendor_id = '' ) {
		global $WCFM;
		$store_name = '';
		if( $WCFM && $WCFM->wcfm_vendor_support ) {
			$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( $vendor_id );
		}
		if( $store_name  ) {
			return apply_filters( 'wcfm_vendor_store_name', $store_name, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_name', false, $vendor_id );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_logo_by_post' ) ) {
	function wcfm_get_vendor_store_logo_by_post( $product_id = '' ) {
		$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		if( $vendor_id  ) {
			$store_logo = wcfm_get_vendor_store_logo_by_vendor( $vendor_id );
			return apply_filters( 'wcfm_vendor_store_logo_by_post', $store_logo, $product_id, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_logo_by_post', false, $product_id, 0 );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ) {
	function wcfm_get_vendor_store_logo_by_vendor( $vendor_id = '' ) {
		global $WCFM;
		if( $vendor_id  ) {
			$store_logo = '';
			if( $WCFM && $WCFM->wcfm_vendor_support ) {
				$store_logo = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( $vendor_id );
			}
			return apply_filters( 'wcfm_vendor_store_logo_by_vendor', $store_logo, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_logo_by_vendor', false, 0 );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_address_by_post' ) ) {
	function wcfm_get_vendor_store_address_by_post( $product_id = '' ) {
		$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		if( $vendor_id  ) {
			$store_address = wcfm_get_vendor_store_address_by_vendor( $vendor_id );
			return apply_filters( 'wcfm_vendor_store_address_by_post', $store_address, $product_id, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_address_by_post', false, $product_id, 0 );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_address_by_vendor' ) ) {
	function wcfm_get_vendor_store_address_by_vendor( $vendor_id = '' ) {
		global $WCFM;
		if( $vendor_id  ) {
			$store_address = '';
			if( $WCFM && $WCFM->wcfm_vendor_support ) {
				$store_address = $WCFM->wcfm_vendor_support->wcfm_get_vendor_address_by_vendor( $vendor_id );
			}
			return apply_filters( 'wcfm_vendor_store_address_by_vendor', $store_address, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_address_by_vendor', false, 0 );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_email_by_post' ) ) {
	function wcfm_get_vendor_store_email_by_post( $product_id = '' ) {
		$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		if( $vendor_id  ) {
			$store_email = wcfm_get_vendor_store_email_by_vendor( $vendor_id );
			return apply_filters( 'wcfm_vendor_store_email_by_post', $store_email, $product_id, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_email_by_post', false, $product_id, 0 );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_email_by_vendor' ) ) {
	function wcfm_get_vendor_store_email_by_vendor( $vendor_id = '' ) {
		global $WCFM;
		if( $vendor_id  ) {
			$store_email = '';
			if( $WCFM && $WCFM->wcfm_vendor_support ) {
				$store_email = $WCFM->wcfm_vendor_support->wcfm_get_vendor_email_by_vendor( $vendor_id );
			}
			return apply_filters( 'wcfm_vendor_store_email_by_vendor', $store_email, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_email_by_vendor', false, 0 );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_phone_by_post' ) ) {
	function wcfm_get_vendor_store_phone_by_post( $product_id = '' ) {
		$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		if( $vendor_id  ) {
			$store_phone = wcfm_get_vendor_store_phone_by_vendor( $vendor_id );
			return apply_filters( 'wcfm_vendor_store_phone_by_post', $store_phone, $product_id, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_phone_by_post', false, $product_id, 0 );
		}
	}
}

if( !function_exists( 'wcfm_get_vendor_store_phone_by_vendor' ) ) {
	function wcfm_get_vendor_store_phone_by_vendor( $vendor_id = '' ) {
		global $WCFM;
		if( $vendor_id  ) {
			$store_phone = '';
			if( $WCFM && $WCFM->wcfm_vendor_support ) {
				$store_phone = $WCFM->wcfm_vendor_support->wcfm_get_vendor_phone_by_vendor( $vendor_id );
			}
			return apply_filters( 'wcfm_vendor_store_phone_by_vendor', $store_phone, $vendor_id );
		} else {
			return apply_filters( 'wcfm_vendor_store_phone_by_vendor', false, 0 );
		}
	}
}

if( !function_exists( 'wcfm_is_vendor_product' ) ) {
	function wcfm_is_vendor_product( $product_id = '' ) {
		if( wcfm_get_vendor_id_by_post( $product_id ) ) {
			return apply_filters( 'wcfm_is_vendor_product', true, $product_id );
		} else {
			return apply_filters( 'wcfm_is_vendor_product', false, $product_id );
		}
	}
}

if( !function_exists( 'wcfm_get_order_number' ) ) {
	function wcfm_get_order_number( $order_id = '' ) {
		if( $order_id ) {
			$order_ids = explode(',', $order_id );
			if( !empty( $order_ids ) && count( $order_ids ) > 1 ) {
				$order_ids_list = '';
				foreach( $order_ids as $order_id_single ) {
					if( $order_id_single ) {
						$order = wc_get_order( $order_id_single );
						if( is_a( $order, 'WC_Order' ) ) {
							if( $order_ids_list ) $order_ids_list .= ', ';
							$order_ids_list .= esc_attr( $order->get_order_number() );
						}
					}
				}
				$order_id = $order_ids_list;
			} else {
				$order = wc_get_order( $order_id );
				if( is_a( $order, 'WC_Order' ) ) {
					$order_id = esc_attr( $order->get_order_number() );
				}
			}
		}
		return $order_id;
	}
}

if( !function_exists( 'wcfm_vendor_has_capability' ) ) {
	function wcfm_vendor_has_capability( $vendor_id, $capability = '' ) {
		global $WCFM;
		if( !$vendor_id ) return true;
		if( !$capability ) return true;
		return $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, $capability );
	}
}

/////////////////////////////////////////////////////////////////////////////// WCFM Marketplace Helper Funtions End ///////////////////////////////////////////////////////////////////////////////////////

if( !function_exists( 'wcfm_is_mobile' ) ) {
	function wcfm_is_mobile() {
		
		include_once(dirname(__FILE__) . '/wcfm-mobile-detect.php');
		
		$detect = new WCFM_Mobile_Detect;

		if( $detect->isMobile() && !$detect->isTablet() ){
			return apply_filters( 'wcfm_is_mobile', true );
		} else {
			return apply_filters( 'wcfm_is_mobile', false );
		}
	}
}

if( !function_exists( 'wcfm_is_tablet' ) ) {
	function wcfm_is_tablet() {
		
		include_once(dirname(__FILE__) . '/wcfm-mobile-detect.php');
		
		$detect = new WCFM_Mobile_Detect;

		if( $detect->isTablet() ){
			return apply_filters( 'wcfm_is_tablet', true );
		} else {
			return apply_filters( 'wcfm_is_tablet', false );
		}
	}
}

if( !function_exists( 'wcfm_is_booking' ) ) {
	function wcfm_is_booking() {
		
		// WC Bookings Check
		$is_booking = ( WCFM_Dependencies::wcfm_bookings_plugin_active_check() ) ? 'wcbooking' : false;
		
		return $is_booking;
	}
}

if( !function_exists( 'wcfm_is_subscription' ) ) {
	function wcfm_is_subscription() {
		
		// WC Subscriptions Check
		$is_subscription = ( WCFM_Dependencies::wcfm_subscriptions_plugin_active_check() ) ? 'wcsubscriptions' : false;
		
		return $is_subscription;
	}
}

if( !function_exists( 'wcfm_is_xa_subscription' ) ) {
	function wcfm_is_xa_subscription() {
		
		// XA Subscriptions Check
		$is_xa_subscription = ( WCFM_Dependencies::wcfm_xa_subscriptions_plugin_active_check() && defined( 'HFORCE_WC_SUBSCRIPTION_VERSION' ) ) ? 'xasubscriptions' : false;
		
		return $is_xa_subscription;
	}
}

if(!function_exists('is_wcfm_page')) {
	function is_wcfm_page() {    
		$pages = get_option("wcfm_page_options");
		if( isset( $pages['wc_frontend_manager_page_id'] ) && $pages['wc_frontend_manager_page_id'] ) {
			if ( function_exists('icl_object_id') ) {
				return is_page( icl_object_id( $pages['wc_frontend_manager_page_id'], 'page', true ) ) || wc_post_content_has_shortcode( 'wc_frontend_manager' );
			} else {
				return is_page( $pages['wc_frontend_manager_page_id'] ) || wc_post_content_has_shortcode( 'wc_frontend_manager' );
			}
		}
		return false;
	}
}

if(!function_exists('get_wcfm_page')) {
	function get_wcfm_page( $language_code = '' ) {
		$pages = get_option("wcfm_page_options");
		if( isset($pages['wc_frontend_manager_page_id']) && $pages['wc_frontend_manager_page_id'] ) {
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
				if( !$language_code ) {
					global $sitepress;
					$language_code = $sitepress->get_current_language();
				}
				
				if( $language_code ) {
					//echo icl_object_id( $pages['wc_frontend_manager_page_id'], 'page', true, $language_code );
					//wcfm_log( $pages['wc_frontend_manager_page_id'] . ":bb:" . $language_code . ":cc:" . icl_object_id( $pages['wc_frontend_manager_page_id'], 'page', true, $language_code ) );
					if( defined('DOING_AJAX') ) {
						do_action( 'wpml_switch_language', $language_code );
					}

					$wcfm_page = get_permalink( icl_object_id( $pages['wc_frontend_manager_page_id'], 'page', true, $language_code ) );
					$wcfm_page = apply_filters( 'wpml_permalink', $wcfm_page, $language_code );
					return $wcfm_page;
				} else {
					return get_permalink( icl_object_id( $pages['wc_frontend_manager_page_id'], 'page', true ) );
				}
			} else {
				return  get_permalink( $pages['wc_frontend_manager_page_id'] );
			}
		}
		return false;
	}
}

if(!function_exists('get_wcfm_url')) {
	function get_wcfm_url() {
		return apply_filters( 'wcfm_dashboard_home', get_wcfm_page() );
	}
}

add_filter( 'lazyload_is_enabled', function( $is_allow ) {
	if( is_wcfm_page() ) { $is_allow = false; }
	return $is_allow;
}, 100 );

if ( ! function_exists( 'is_wcfm_endpoint_url' ) ) {

	/**
	 * is_wcfm_endpoint_url - Check if an endpoint is showing.
	 * @param  string $endpoint
	 * @return bool
	 */
	function is_wcfm_endpoint_url( $endpoint = false ) {
		global $WCFM, $WCFM_Query, $wp;

		$wcfm_endpoints = $WCFM_Query->get_query_vars();

		if ( $endpoint !== false ) {
			if ( ! isset( $wc_endpoints[ $endpoint ] ) ) {
				return false;
			} else {
				$endpoint_var = $wcfm_endpoints[ $endpoint ];
			}

			return isset( $wp->query_vars[ $endpoint_var ] );
		} else {
			foreach ( $wcfm_endpoints as $key => $value ) {
				if ( isset( $wp->query_vars[ $key ] ) ) {
					return true;
				}
			}

			return false;
		}
	}
}

if(!function_exists('is_wcfm_analytics')) {
	function is_wcfm_analytics() {
		$wcfm_options = (array) get_option( 'wcfm_options' );
		$is_analytics_disabled = isset( $wcfm_options['analytics_disabled'] ) ? $wcfm_options['analytics_disabled'] : 'no';
		if( $is_analytics_disabled == 'yes' ) return false;
		return true;
	}
}

if(!function_exists('get_wcfm_products_url')) {
	function get_wcfm_products_url( $product_status = '', $product_vendor = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_products_url = wcfm_get_endpoint_url( 'wcfm-products', '', $wcfm_page );
		if($product_status) $wcfm_products_url = add_query_arg( 'product_status', $product_status, $wcfm_products_url );
		if($product_vendor) $wcfm_products_url = add_query_arg( 'product_vendor', $product_vendor, $wcfm_products_url );
		return apply_filters( 'wcfm_products_url', $wcfm_products_url, $product_status );
	}
}

if(!function_exists('get_wcfm_edit_product_url')) {
	function get_wcfm_edit_product_url( $product_id = '', $the_product = array(), $language_code = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page( $language_code );
		$wcfm_edit_product_url = wcfm_get_endpoint_url( 'wcfm-products-manage', $product_id, $wcfm_page, $language_code );
		return apply_filters( 'wcfm_edit_product_url',  $wcfm_edit_product_url, $product_id );
	}
}

if(!function_exists('get_wcfm_stock_manage_url')) {
	function get_wcfm_stock_manage_url( $product_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_stock_manage_url = wcfm_get_endpoint_url( 'wcfm-stock-manage', '', $wcfm_page );
		if($product_status) $wcfm_stock_manage_url = add_query_arg( 'product_status', $product_status, $wcfm_stock_manage_url );
		return apply_filters( 'wcfm_stock_manage_url', $wcfm_stock_manage_url, $product_status );
	}
}

if(!function_exists('get_wcfm_import_product_url')) {
	function get_wcfm_import_product_url( $step = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_import_product_url = wcfm_get_endpoint_url( 'wcfm-products-import', '', $wcfm_page );
		if($step) $wcfm_import_product_url = add_query_arg( 'step', $step, $wcfm_import_product_url );
		return apply_filters( 'wcfm_import_product_url', $wcfm_import_product_url, $step );
	}
}

if(!function_exists('get_wcfm_export_product_url')) {
	function get_wcfm_export_product_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_export_product_url = wcfm_get_endpoint_url( 'wcfm-products-export', '', $wcfm_page );
		return apply_filters( 'wcfm_export_product_url', $wcfm_export_product_url );
	}
}

if(!function_exists('get_wcfm_coupons_url')) {
	function get_wcfm_coupons_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_coupons_url = wcfm_get_endpoint_url( 'wcfm-coupons', '', $wcfm_page );
		return apply_filters( 'wcfm_coupons_url',  $wcfm_coupons_url );
	}
}

if(!function_exists('get_wcfm_coupons_manage_url')) {
	function get_wcfm_coupons_manage_url( $coupon_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_coupon_manage_url = wcfm_get_endpoint_url( 'wcfm-coupons-manage', $coupon_id, $wcfm_page );
		return apply_filters( 'wcfm_coupon_manage_url',  $wcfm_coupon_manage_url, $coupon_id );
	}
}

if(!function_exists('get_wcfm_orders_url')) {
	function get_wcfm_orders_url( $order_status = '', $order_vendor = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_orders_url = wcfm_get_endpoint_url( 'wcfm-orders', '', $wcfm_page );
		if( $order_status ) $wcfm_orders_url = add_query_arg( 'order_status', $order_status, $wcfm_orders_url );
		if( $order_vendor ) $wcfm_orders_url = add_query_arg( 'order_vendor', $order_vendor, $wcfm_orders_url );
		return apply_filters( 'wcfm_orders_url',  $wcfm_orders_url, $order_status );
	}
}

if(!function_exists('get_wcfm_manage_order_url')) {
	function get_wcfm_manage_order_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_orders_manage_url = wcfm_get_endpoint_url( 'wcfm-orders-manage', '', $wcfm_page );
		return apply_filters( 'wcfm_manage_order_url',  $wcfm_orders_manage_url );
	}
}

if(!function_exists('get_wcfm_view_order_url')) {
	function get_wcfm_view_order_url($order_id = '') {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_view_order_url = wcfm_get_endpoint_url( 'wcfm-orders-details', $order_id, $wcfm_page );
		return apply_filters( 'wcfm_view_order_url', $wcfm_view_order_url, $order_id );
	}
}

if(!function_exists('get_wcfm_reports_url')) {
	function get_wcfm_reports_url( $range = '', $report_type = 'wcfm-reports-sales-by-date', $vendor_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_reports_url = wcfm_get_endpoint_url( $report_type, $vendor_id, $wcfm_page );
		if( $range ) $get_wcfm_reports_url = add_query_arg( 'range', $range, $get_wcfm_reports_url );
		if( $report_type == 'wcfm-reports-sales-by-date' ) $get_wcfm_reports_url = apply_filters( 'wcfm_default_reports_url', $get_wcfm_reports_url );
		return apply_filters( 'wcfm_reports_url', $get_wcfm_reports_url, $report_type, $vendor_id );
	}
}

if(!function_exists('get_wcfm_profile_url')) {
	function get_wcfm_profile_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_profile_url = wcfm_get_endpoint_url( 'wcfm-profile', '', $wcfm_page );
		return apply_filters( 'wcfm_profile_url', $get_wcfm_profile_url );
	}
}

if(!function_exists('get_wcfm_analytics_url')) {
	function get_wcfm_analytics_url( $range = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_analytics_url = wcfm_get_endpoint_url( 'wcfm-analytics', '', $wcfm_page );
		if( $range ) $get_wcfm_analytics_url = add_query_arg( 'range', $range, $get_wcfm_analytics_url );
		return apply_filters( 'wcfm_analytics_url', $get_wcfm_analytics_url, $range );
	}
}

if(!function_exists('get_wcfm_settings_url')) {
	function get_wcfm_settings_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_settings_url = wcfm_get_endpoint_url( 'wcfm-settings', '', $wcfm_page );
		return apply_filters( 'wcfm_settings_url', $get_wcfm_settings_url );
	}
}

if(!function_exists('get_wcfm_capability_url')) {
	function get_wcfm_capability_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_capability_url = wcfm_get_endpoint_url( 'wcfm-capability', '', $wcfm_page );
		return apply_filters( 'wcfm_capability_url', $get_wcfm_capability_url );
	}
}

if(!function_exists('get_wcfm_knowledgebase_url')) {
	function get_wcfm_knowledgebase_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_knowledgebase_url = wcfm_get_endpoint_url( 'wcfm-knowledgebase', '', $wcfm_page );
		return apply_filters( 'wcfm_knowledgebase_url', $get_wcfm_knowledgebase_url );
	}
}

if(!function_exists('get_wcfm_knowledgebase_manage_url')) {
	function get_wcfm_knowledgebase_manage_url( $knowledgebase_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_knowledgebase_manage_url = wcfm_get_endpoint_url( 'wcfm-knowledgebase-manage', $knowledgebase_id, $wcfm_page );
		return apply_filters( 'wcfm_knowledgebase_manage_url', $get_wcfm_knowledgebase_manage_url, $knowledgebase_id );
	}
}

if(!function_exists('get_wcfm_notices_url')) {
	function get_wcfm_notices_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_notices_url = wcfm_get_endpoint_url( 'wcfm-notices', '', $wcfm_page );
		return apply_filters( 'wcfm_notices_url', $get_wcfm_notices_url );
	}
}

if(!function_exists('get_wcfm_notice_manage_url')) {
	function get_wcfm_notice_manage_url( $topic_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_notice_manage_url = wcfm_get_endpoint_url( 'wcfm-notice-manage', $topic_id, $wcfm_page );
		return apply_filters( 'wcfm_notice_manage_url', $get_wcfm_notice_manage_url, $topic_id );
	}
}

if(!function_exists('get_wcfm_notice_view_url')) {
	function get_wcfm_notice_view_url( $topic_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_notice_view_url = wcfm_get_endpoint_url( 'wcfm-notice-view', $topic_id, $wcfm_page );
		return apply_filters( 'wcfm_notice_view_url', $get_wcfm_notice_view_url, $topic_id );
	}
}

if(!function_exists('get_wcfm_messages_url')) {
	function get_wcfm_messages_url( $message_type = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_messages_url = wcfm_get_endpoint_url( 'wcfm-messages', '', $wcfm_page );
		if( $message_type ) $get_wcfm_messages_url = add_query_arg( 'message_type', $message_type, $get_wcfm_messages_url );
		return apply_filters( 'wcfm_messages_url', $get_wcfm_messages_url, $message_type );
	}
}

if(!function_exists('get_wcfm_enquiry_url')) {
	function get_wcfm_enquiry_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_enquiry_url = wcfm_get_endpoint_url( 'wcfm-enquiry', '', $wcfm_page );
		return apply_filters( 'wcfm_enquiry_url', $get_wcfm_enquiry_url );
	}
}

if(!function_exists('get_wcfm_enquiry_manage_url')) {
	function get_wcfm_enquiry_manage_url( $topic_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_enquiry_manage_url = wcfm_get_endpoint_url( 'wcfm-enquiry-manage', $topic_id, $wcfm_page );
		return apply_filters( 'wcfm_enquiry_manage_url', $get_wcfm_enquiry_manage_url, $topic_id );
	}
}

if(!function_exists('get_wcfm_articles_url')) {
	function get_wcfm_articles_url( $article_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_articles_url = wcfm_get_endpoint_url( 'wcfm-articles', '', $wcfm_page );
		if($article_status) $get_wcfm_articles_url = add_query_arg( 'article_status', $article_status, $get_wcfm_articles_url );
		return apply_filters( 'wcfm_articles_url', $get_wcfm_articles_url, $article_status );
	}
}

if(!function_exists('get_wcfm_articles_manage_url')) {
	function get_wcfm_articles_manage_url( $article_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_articles_manage_url = wcfm_get_endpoint_url( 'wcfm-articles-manage', $article_id, $wcfm_page );
		return apply_filters( 'wcfm_articles_manage_url', $get_wcfm_articles_manage_url, $article_id );
	}
}

if(!function_exists('get_wcfm_vendors_url')) {
	function get_wcfm_vendors_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_vendors_url = wcfm_get_endpoint_url( 'wcfm-vendors', '', $wcfm_page );
		return apply_filters( 'wcfm_vendors_url', $get_wcfm_vendors_url );
	}
}

if(!function_exists('get_wcfm_vendors_new_url')) {
	function get_wcfm_vendors_new_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_vendors_new_url = wcfm_get_endpoint_url( 'wcfm-vendors-new', '', $wcfm_page );
		return apply_filters( 'wcfm_vendors_new_url', $get_wcfm_vendors_new_url );
	}
}

if(!function_exists('get_wcfm_vendors_manage_url')) {
	function get_wcfm_vendors_manage_url( $vendor_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_vendors_manage_url = wcfm_get_endpoint_url( 'wcfm-vendors-manage', $vendor_id, $wcfm_page );
		return apply_filters( 'wcfm_vendors_manage_url', $get_wcfm_vendors_manage_url, $vendor_id );
	}
}

if(!function_exists('get_wcfm_vendors_commission_url')) {
	function get_wcfm_vendors_commission_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_vendors_commission_url = wcfm_get_endpoint_url( 'wcfm-vendors-commission', '', $wcfm_page );
		return apply_filters( 'wcfm_vendors_commission_url', $get_wcfm_vendors_commission_url );
	}
}

if(!function_exists('get_wcfm_customers_url')) {
	function get_wcfm_customers_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_customers_url = wcfm_get_endpoint_url( 'wcfm-customers', '', $wcfm_page );
		return apply_filters( 'wcfm_customers_url', $get_wcfm_customers_url );
	}
}

if(!function_exists('get_wcfm_customers_manage_url')) {
	function get_wcfm_customers_manage_url( $customer_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_customers_manage_url = wcfm_get_endpoint_url( 'wcfm-customers-manage', $customer_id, $wcfm_page );
		return apply_filters( 'wcfm_customers_manage_url', $get_wcfm_customers_manage_url, $customer_id );
	}
}

if(!function_exists('get_wcfm_customers_details_url')) {
	function get_wcfm_customers_details_url( $customer_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_customers_details_url = wcfm_get_endpoint_url( 'wcfm-customers-details', $customer_id, $wcfm_page );
		return apply_filters( 'wcfm_customers_details_url', $get_wcfm_customers_details_url, $customer_id );
	}
}

if(!function_exists('get_wcfm_listings_url')) {
	function get_wcfm_listings_url( $listing_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_listings_dashboard_url = wcfm_get_endpoint_url( 'wcfm-listings', '', $wcfm_page );
		if($listing_status) $wcfm_listings_dashboard_url = add_query_arg( 'listing_status', $listing_status, $wcfm_listings_dashboard_url );
		return apply_filters( 'wcfm_listings_dashboard_url', $wcfm_listings_dashboard_url );
	}
}

if(!function_exists('get_wcfm_applications_url')) {
	function get_wcfm_applications_url( $listing_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_applications_dashboard_url = wcfm_get_endpoint_url( 'wcfm-applications', '', $wcfm_page );
		if($listing_id) $wcfm_applications_dashboard_url = add_query_arg( 'listing_id', $listing_id, $wcfm_applications_dashboard_url );
		return apply_filters( 'wcfm_applications_dashboard_url', $wcfm_applications_dashboard_url, $listing_id );
	}
}

if(!function_exists('get_wcfm_bookings_dashboard_url')) {
	function get_wcfm_bookings_dashboard_url( $booking_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_bookings_dashboard_url = wcfm_get_endpoint_url( 'wcfm-bookings-dashboard', '', $wcfm_page );
		return apply_filters( 'wcfm_bookings_dashboard_url', $wcfm_bookings_dashboard_url );
	}
}

if(!function_exists('get_wcfm_bookings_url')) {
	function get_wcfm_bookings_url( $booking_status = '') {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_bookings_url = wcfm_get_endpoint_url( 'wcfm-bookings', '', $wcfm_page );
		if( $booking_status ) $wcfm_bookings_url = add_query_arg( 'booking_status', $booking_status, $wcfm_bookings_url );
		return apply_filters( 'wcfm_bookings_url', $wcfm_bookings_url, $booking_status );
	}
}

if(!function_exists('get_wcfm_view_booking_url')) {
	function get_wcfm_view_booking_url( $booking_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_view_booking_url = wcfm_get_endpoint_url( 'wcfm-bookings-details', $booking_id, $wcfm_page );
		return apply_filters( 'wcfm_view_booking_url', $wcfm_view_booking_url, $booking_id );
	}
}

// WCMp Payments URL
if(!function_exists('wcfm_payments_url')) {
	function wcfm_payments_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_payments_url = wcfm_get_endpoint_url( 'wcfm-payments', '', $wcfm_page );
		return apply_filters( 'wcfm_payments_url', $get_wcfm_payments_url );
	}
}

// WCMp Withdrawal URL
if(!function_exists('wcfm_withdrawal_url')) {
	function wcfm_withdrawal_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_withdrawal_url = wcfm_get_endpoint_url( 'wcfm-withdrawal', '', $wcfm_page );
		return apply_filters( 'wcfm_withdrawal_url', $get_wcfm_withdrawal_url );
	}
}

// WCfM Withdrawal Request URL
if(!function_exists('wcfm_withdrawal_requests_url')) {
	function wcfm_withdrawal_requests_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_withdrawal_requests_url = wcfm_get_endpoint_url( 'wcfm-withdrawal-requests', '', $wcfm_page );
		return apply_filters( 'wcfm_withdrawal_requests_url', $get_wcfm_withdrawal_requests_url );
	}
}

// WCfM Withdrawal revers URL
if(!function_exists('wcfm_withdrawal_reverse_url')) {
	function wcfm_withdrawal_reverse_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_withdrawal_reverse_url = wcfm_get_endpoint_url( 'wcfm-withdrawal-reverse', '', $wcfm_page );
		return apply_filters( 'wcfm_withdrawal_reverse_url', $get_wcfm_withdrawal_reverse_url );
	}
}

// WCMp Transaction Details URL
if(!function_exists('wcfm_transaction_details_url')) {
	function wcfm_transaction_details_url( $transaction_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_transaction_details_url = wcfm_get_endpoint_url( 'wcfm-transaction-details', $transaction_id, $wcfm_page );
		return apply_filters( 'wcfm_transaction_details_url', $get_wcfm_transaction_details_url, $transaction_id );
	}
}

// WCfM Navigation URL
if(!function_exists('wcfm_get_navigation_url')) {
	function wcfm_get_navigation_url( $endpoint ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$navigation_url = $wcfm_page;
		
		switch( $endpoint ) {
			case 'products':
			  $navigation_url = wcfm_get_endpoint_url( 'wcfm-products', '', $wcfm_page );
			break;
			
			case 'coupons':
			  $navigation_url = wcfm_get_endpoint_url( 'wcfm-coupons', '', $wcfm_page );
			break;
			
			case 'orders':
			  $navigation_url = wcfm_get_endpoint_url( 'wcfm-orders', '', $wcfm_page );
			break;
			
			case 'withdraw':
			  $navigation_url = wcfm_get_endpoint_url( 'wcfm-withdrawal', '', $wcfm_page );
			break;
			
			case 'settings':
			  $navigation_url = wcfm_get_endpoint_url( 'wcfm-settings', '', $wcfm_page );
			break;
			
			case 'settings/payment':
			  $navigation_url = wcfm_get_endpoint_url( 'wcfm-settings', '', $wcfm_page );
			break;
			
			case 'payment':
			  $navigation_url = wcfm_get_endpoint_url( 'wcfm-payments', '', $wcfm_page );
			break;
			
			case 'reports':
			  $navigation_url = get_wcfm_reports_url();
			break;
			
			case 'support':
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					$navigation_url = wcfm_get_endpoint_url( 'wcfm-support', '', $wcfm_page );
				} else {
					$navigation_url = wcfm_get_endpoint_url( 'wcfm-enquiry', '', $wcfm_page );
				}
			break;
			
			case 'subscription':
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					$navigation_url = wcfm_get_endpoint_url( 'wcfm-subscription-packs', '', $wcfm_page );
				} else {
					$navigation_url = $wcfm_page;
				}
			break;
			
			default:
				$navigation_url = $wcfm_page;
			break;
		}
		
		return apply_filters( 'wcfm_navigation_url', $navigation_url, $endpoint );
	}
}

if(!function_exists('get_wcfm_emails')) {
	function get_wcfm_emails() {
		$wcfm_emails = array( 
												   'new-enquiry'        => __( 'New Enquiry', 'wc-frontend-manager' ),
												);
		return apply_filters( 'get_wcfm_emails', $wcfm_emails );
	}
}

if(!function_exists('get_wcfm_articles_manager_messages')) {
	function get_wcfm_articles_manager_messages() {
		global $WCFM;
		
		$messages = apply_filters( 'wcfm_validation_messages_article_manager', array(
																																								'no_title' => __('Please insert Article Title before submit.', 'wc-frontend-manager'),
																																								'article_saved' => __('Article Successfully Saved.', 'wc-frontend-manager'),
																																								'article_pending' => __( 'Article Successfully submitted for moderation.', 'wc-frontend-manager' ),
																																								'article_published' => __('Article Successfully Published.', 'wc-frontend-manager'),
																																								) );
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_products_manager_messages')) {
	function get_wcfm_products_manager_messages() {
		global $WCFM;
		
		$messages = apply_filters( 'wcfm_validation_messages_product_manager', array(
																																								'no_title' => __('Please insert Product Title before submit.', 'wc-frontend-manager'),
																																								'no_excerpt' => __('Please insert Product Short Description before submit.', 'wc-frontend-manager'),
																																								'no_description' => __('Please insert Product Description before submit.', 'wc-frontend-manager'),
																																								'sku_unique' => __('Product SKU must be unique.', 'wc-frontend-manager'),
																																								'variation_sku_unique' => __('Variation SKU must be unique.', 'wc-frontend-manager'),
																																								'product_saved' => __('Product Successfully Saved.', 'wc-frontend-manager'),
																																								'product_pending' => __( 'Product Successfully submitted for moderation.', 'wc-frontend-manager' ),
																																								'product_published' => __('Product Successfully Published.', 'wc-frontend-manager'),
																																								'set_stock'  => __('Set Stock', 'wc-frontend-manager'),
																																								'increase_stock' => __('Increase Stock', 'wc-frontend-manager'),
																																								'regular_price' => __('Regular Price', 'wc-frontend-manager'),
																																								'regular_price_increase' => __('Regular price increase by', 'wc-frontend-manager'),
																																								'regular_price_decrease' => __('Regular price decrease by', 'wc-frontend-manager'),
																																								'sales_price' => __('Sale Price', 'wc-frontend-manager'),
																																								'sales_price_increase' => __('Sale price increase by', 'wc-frontend-manager'),
																																								'sales_price_decrease' => __('Sale price decrease by', 'wc-frontend-manager'),
																																								'length' => __('Length', 'wc-frontend-manager'),
																																								'width' => __('Width', 'wc-frontend-manager'),
																																								'height' => __('Height', 'wc-frontend-manager'),
																																								'weight' => __('Weight', 'wc-frontend-manager'),
																																								'download_limit' => __('Download Limit', 'wc-frontend-manager'),
																																								'download_expiry' => __('Download Expiry', 'wc-frontend-manager'),
																																								
																																								) );
		
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_coupons_manage_messages')) {
	function get_wcfm_coupons_manage_messages() {
		global $WCFM;
		
		$messages = array(
											'no_title' => __( 'Please insert atleast Coupon Title before submit.', 'wc-frontend-manager' ),
											'coupon_saved' => __( 'Coupon Successfully Saved.', 'wc-frontend-manager' ),
											'coupon_published' => __( 'Coupon Successfully Published.', 'wc-frontend-manager' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_knowledgebase_manage_messages')) {
	function get_wcfm_knowledgebase_manage_messages() {
		global $WCFM;
		
		$messages = array(
											'no_title' => __( 'Please insert atleast Knowledgebase Title before submit.', 'wc-frontend-manager' ),
											'knowledgebase_saved' => __( 'Knowledgebase Successfully Saved.', 'wc-frontend-manager' ),
											'knowledgebase_published' => __( 'Knowledgebase Successfully Published.', 'wc-frontend-manager' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_notice_manage_messages')) {
	function get_wcfm_notice_manage_messages() {
		global $WCFM;
		
		$messages = array(
											'no_title' => __( 'Please insert atleast Topic Title before submit.', 'wc-frontend-manager' ),
											'notice_saved' => __( 'Topic Successfully Saved.', 'wc-frontend-manager' ),
											'notice_published' => __( 'Topic Successfully Published.', 'wc-frontend-manager' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_notice_view_messages')) {
	function get_wcfm_notice_view_messages() {
		global $WCFM;
		
		$messages = array(
											'no_title' => __( 'Please write something before submit.', 'wc-frontend-manager' ),
											'notice_failed' => __( 'Reply send failed, try again.', 'wc-frontend-manager' ),
											'reply_published' => __( 'Reply Successfully Send.', 'wc-frontend-manager' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_enquiry_manage_messages')) {
	function get_wcfm_enquiry_manage_messages() {
		global $WCFM;
		
		$messages = array(
											'no_name'             => __( 'Name is required.', 'wc-frontend-manager' ),
											'no_email'            => __( 'Email is required.', 'wc-frontend-manager' ),
											'no_enquiry'          => __( 'Please insert your enquiry before submit.', 'wc-frontend-manager' ),
											'no_reply'            => __( 'Please insert your reply before submit.', 'wc-frontend-manager' ),
											'enquiry_saved'       => __( 'Your enquiry successfully sent.', 'wc-frontend-manager' ),
											'enquiry_published'   => __( 'Enquiry reply successfully published.', 'wc-frontend-manager' ),
											'enquiry_reply_saved' => __( 'Your reply successfully sent.', 'wc-frontend-manager' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_vendors_new_messages')) {
	function get_wcfm_vendors_new_messages() {
		global $WCFMu;
		
		$messages = array(
											'no_username'     => __( 'Please insert Username before submit.', 'wc-frontend-manager' ),
											'no_email'        => __( 'Please insert Email before submit.', 'wc-frontend-manager' ),
											'no_store_name'   => __( 'Please insert Store Name before submit.', 'wc-frontend-manager' ),
											'username_exists' => __( 'This Username already exists.', 'wc-frontend-manager' ),
											'email_exists'    => __( 'This Email already exists.', 'wc-frontend-manager' ),
											'vendor_failed'   => __( 'Vendor Saving Failed.', 'wc-frontend-manager' ),
											'vendor_saved'    => __( 'Vendor Successfully Saved.', 'wc-frontend-manager' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_customers_manage_messages')) {
	function get_wcfm_customers_manage_messages() {
		global $WCFMu;
		
		$messages = array(
											'no_username' => __( 'Please insert Customer Username before submit.', 'wc-frontend-manager' ),
											'no_email' => __( 'Please insert Customer Email before submit.', 'wc-frontend-manager' ),
											'username_exists' => __( 'This Username already exists.', 'wc-frontend-manager' ),
											'email_exists' => __( 'This Email already exists.', 'wc-frontend-manager' ),
											'customer_failed' => __( 'Customer Saving Failed.', 'wc-frontend-manager' ),
											'customer_saved' => __( 'Customer Successfully Saved.', 'wc-frontend-manager' ),
											);
		
		return $messages;
	}
}

if(!function_exists('get_wcfm_dashboard_messages')) {
	function get_wcfm_dashboard_messages() {
		global $WCFM;
		
		$messages = array(
											"product_approve_confirm"            => __( "Are you sure and want to approve / publish this 'Product'?", "wc-frontend-manager" ),
											"product_reject_confirm"             => __( "Are you sure and want to reject this 'Product'?\nReason:", "wc-frontend-manager" ),
											"product_archive_confirm"            => __( "Are you sure and want to archive this 'Product'?", "wc-frontend-manager" ),
											"multiblock_delete_confirm"          => __( "Are you sure and want to delete this 'Block'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"article_delete_confirm"             => __( "Are you sure and want to delete this 'Article'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"product_delete_confirm"             => __( "Are you sure and want to delete this 'Product'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"message_delete_confirm"             => __( "Are you sure and want to delete this 'Message'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"order_delete_confirm"               => __( "Are you sure and want to delete this 'Order'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"enquiry_delete_confirm"             => __( "Are you sure and want to delete this 'Enquiry'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"support_delete_confirm"             => __( "Are you sure and want to delete this 'Support Ticket'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"follower_delete_confirm"            => __( "Are you sure and want to delete this 'Follower'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"following_delete_confirm"           => __( "Are you sure and want to delete this 'Following'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"resource_delete_confirm"            => __( "Are you sure and want to delete this 'Resource'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"auction_bid_delete_confirm"         => __( "Are you sure and want to delete this 'Bid'?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"order_mark_complete_confirm"        => __( "Are you sure and want to 'Mark as Complete' this Order?", "wc-frontend-manager" ),
											"booking_mark_complete_confirm"      => __( "Are you sure and want to 'Mark as Confirmed' this Booking?", "wc-frontend-manager" ),
											"booking_mark_decline_confirm"       => __( "Are you sure and want to 'Mark as Declined' this Booking?", "wc-frontend-manager" ),
											"appointment_mark_complete_confirm"  => __( "Are you sure and want to 'Mark as Complete' this Appointment?", "wc-frontend-manager" ),
											"add_new"                            => __( "Add New", "wc-frontend-manager" ),
											"select_all"                         => __( "Select all", "wc-frontend-manager" ),
											"select_none"                        => __( "Select none", "wc-frontend-manager" ),
											"any_attribute"                      => __( "Any", "wc-frontend-manager" ),
											"add_attribute_term"                 => __( "Enter a name for the new attribute term:", "wc-frontend-manager" ),
											"wcfmu_upgrade_notice"               => __( "Please upgrade your WC Frontend Manager to Ultimate version and avail this feature.", "wc-frontend-manager" ),
											"pdf_invoice_upgrade_notice"         => __( "Install WC Frontend Manager Ultimate and WooCommerce PDF Invoices & Packing Slips to avail this feature.", "wc-frontend-manager" ),
											"wcfm_bulk_action_no_option"         => __( "Please select some element first!!", "wc-frontend-manager" ),
											"wcfm_bulk_action_confirm"           => __( "Are you sure and want to do this?\nYou can't undo this action ...", "wc-frontend-manager" ),
											"review_status_update_confirm"       => __( "Are you sure and want to do this?", "wc-frontend-manager" ),
											"everywhere"                         => __( "Everywhere Else", "wc-frontend-manager" ),
											"required_message"                   => __( "This field is required.", 'wc-frontend-manager' ),
											"choose_select2"                     => __( "Choose ", "wc-frontend-manager" ),
											"category_attribute_mapping"         => __( "All Attributes", "wc-frontend-manager" ),
											"search_page_select2"                => __( "Search for a page ...", "wc-frontend-manager" ),
											"search_attribute_select2"           => __( "Search for an attribute ...", "wc-frontend-manager" ),
											"search_product_select2"             => __( "Filter by product ...", "wc-frontend-manager" ),
											"search_taxonomy_select2"            => __( "Filter by category ...", "wc-frontend-manager" ),
											"choose_category_select2"            => __( "Choose Categories ...", "wc-frontend-manager" ),
											"choose_listings_select2"            => __( "Choose Listings ...", "wc-frontend-manager" ),
											"choose_tags_select2"                => __( "Choose Tags ...", "wc-frontend-manager" ),
											"choose_vendor_select2"              => __( "Choose", "wc-frontend-manager" ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ) . ' ...',
											"no_category_select2"                => __( "No categories", "wc-frontend-manager" ),
											"select2_searching"                  => __( 'Searching ...', 'wc-frontend-manager' ),
											"select2_no_result"                  => __( 'No matching result found.', 'wc-frontend-manager' ),
											"select2_loading_more"               => __( 'Loading ...', 'wc-frontend-manager' ),
											"select2_minimum_input"              => __( 'Minimum input character ', 'wc-frontend-manager' ),
											"wcfm_product_popup_next"            => __( 'Next', 'wc-frontend-manager' ),
											"wcfm_product_popup_previous"        => __( 'Previous', 'wc-frontend-manager' ),
											"wcfm_multiblick_addnew_help"        => __( 'Add New Block', 'wc-frontend-manager' ),
											"wcfm_multiblick_remove_help"        => __( 'Remove Block', 'wc-frontend-manager' ),
											"wcfm_multiblick_collapse_help"      => __( 'Toggle Block', 'wc-frontend-manager' ),
											"wcfm_multiblick_sortable_help"      => __( 'Drag to re-arrange blocks', 'wc-frontend-manager' ),
											"sell_this_item_confirm"             => __( 'Do you want to add this item(s) to your store?', 'wc-frontend-manager' ),
											"bulk_no_itm_selected"               => __( 'Please select some product first!', 'wc-frontend-manager' ),
											"user_non_logged_in"                 => __( 'Please login to the site first!', 'wc-frontend-manager' ),
                      "shiping_method_not_selected"        => __( 'Please select a shipping method', 'wc-frontend-manager' ),
                      "shiping_method_not_found"           => __( 'Shipping method not found', 'wc-frontend-manager' ),
                      "shiping_zone_not_found"             => __( 'Shipping zone not found', 'wc-frontend-manager' ),
                      "shipping_method_del_confirm"        => __( "Are you sure you want to delete this 'Shipping Method'?\nYou can't undo this action ...", 'wc-frontend-manager' ),
                      "variation_auto_generate_confirm"    => __( "Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).", "wc-frontend-manager" )
										);
		
		return apply_filters( 'wcfm_dashboard_messages', $messages );
	}
}

if(!function_exists('get_wcfm_message_types')) {
	function get_wcfm_message_types() {
		global $WCFM;
		
		$message_types = array(
											'direct'            => __( 'Direct Message', 'wc-frontend-manager' ),
											'notice'            => __( 'Announcement', 'wc-frontend-manager' ),
											'product_review'    => __( 'Product Review', 'wc-frontend-manager' ),
											'product_lowstk'    => __( 'Low Stock Product', 'wc-frontend-manager' ),
											//'product_outofstk'  => __( 'Out of Stock Product', 'wc-frontend-manager' ),
											'status-update'     => __( 'Status Updated', 'wc-frontend-manager' ),
											'withdraw-request'  => __( 'Withdrawal Requests', 'wc-frontend-manager' ),
											'refund-request'    => __( 'Refund Requests', 'wc-frontend-manager' ),
											'new_product'       => __( 'New Product', 'wc-frontend-manager' ),
											'new_taxonomy_term' => __( 'New Category', 'wc-frontend-manager' ),
											'order'             => __( 'New Order', 'wc-frontend-manager' ),
											);
		
		return apply_filters( 'wcfm_message_types', $message_types );
	}
}

/**
 * Get endpoint URL.
 *
 * Gets the URL for an endpoint, which varies depending on permalink settings.
 *
 * @param  string $endpoint
 * @param  string $value
 * @param  string $permalink
 *
 * @return string
 */
function wcfm_get_endpoint_url( $endpoint, $value = '', $permalink = '', $lang_code = '' ) {
	global $post;
	if ( ! $permalink ) {
		$permalink = apply_filters( 'wcfm_get_base_permalink', get_permalink( $post ) );
	}
	
	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array(), $lang_code );
	$endpoint = ! empty( $wcfm_modified_endpoints[ $endpoint ] ) ? $wcfm_modified_endpoints[ $endpoint ] : str_replace( 'wcfm-', '', $endpoint );
	
	// WC 3.6 FIX
	if( $endpoint == 'orders' ) $endpoint = 'orderslist';
	if( $endpoint == 'booking' ) $endpoint = 'bookinglist';
	if( $endpoint == 'bookings' ) $endpoint = 'bookingslist';
	if( $endpoint == 'subscriptions' ) $endpoint = 'subscriptionslist';
	if( $endpoint == 'sell-items-catalog' ) $endpoint = 'add-to-my-store-catalog';
	
	$endpoint = apply_filters( 'wcfm_dashboard_modified_endpoint_slug', $endpoint );

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return apply_filters( 'wcfm_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

function wcfm_get_user_posts_count( $user_id = 0, $post_type = 'product', $post_status = 'publish', $custom_args = array() ) {
	global $WCFM;
	
	//$post_count = count_user_posts( $user_id, $post_type );
	if( !$user_id && !current_user_can( 'administrator' ) && !current_user_can( 'shop_manager' ) ) $user_id  = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	
	$args = array(
			'post_type'     => $post_type,
			'post_status'   => $post_status,
			'posts_per_page' => -1,
			'suppress_filters' => 0
	);
	$args = array_merge( $args, $custom_args );
	if( $user_id && ( $user_id != 'wcfm1990' ) ) $args['author'] = $user_id;
	if( $post_type == 'product' ) {
		$args = apply_filters( 'wcfm_products_args', $args );
	}
	$args['fields'] = 'ids';
	$ps = get_posts($args);
	return apply_filters( 'wcfm_user_posts_count', count($ps), $user_id, $post_type, $post_status, $custom_args );
}

function wcfm_query_time_range_filter( $sql, $time, $interval = '7day', $start_date = '', $end_date = '', $table_handler = 'commission' ) {
	switch( $interval ) {
		case 'year' :
			$sql .= " AND YEAR( {$table_handler}.{$time} ) = YEAR( CURDATE() )";
			break;

		case 'last_month' :
			$sql .= " AND MONTH( {$table_handler}.{$time} ) = MONTH( NOW() ) - 1";
			break;

		case 'month' :
			$sql .= " AND MONTH( {$table_handler}.{$time} ) = MONTH( NOW() )";
			break;

		case 'custom' :
			$start_date = ! empty( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : $start_date;
			$end_date = ! empty( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : $end_date;
			if( $start_date ) $start_date = wcfm_standard_date( $start_date );
			if( $end_date ) $end_date = wcfm_standard_date( $end_date );

			$sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
			break;
			
		case 'all' :
			
			break;

		case 'default' :
		case '7day' :
			$sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 7 DAY ) AND NOW()";
			break;
	}
	
	return $sql;
}

/**
 * WCFM Enquiry Tab - tab manager support
 *
 * @since		3.4.6
 */
function wcfm_enquiry_product_tab( $tabs) {
	global $WCFM;
	if( apply_filters( 'wcfm_is_pref_enquiry_tab', true ) && apply_filters( 'wcfm_is_pref_enquiry', true ) ) {
		unset( $tabs['wcmp_customer_qna'] );
		unset( $tabs['seller_enquiry_form'] );
		$tabs['wcfm_enquiry_tab'] = apply_filters( 'wcfm_enquiry_tab_element',array(
																																								'title' 	=> __( 'Enquiries', 'wc-frontend-manager' ),
																																								'priority' 	=> apply_filters( 'wcfm_enquiry_tab_priority', 100 ),
																																								'callback' 	=> array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_product_tab_content' )
																																							) );
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'wcfm_enquiry_product_tab', 100 );

/**
 * WCFM Policies Tab - tab manager support
 *
 * @since	4.1.10
 */
function wcfm_policies_product_tab( $tabs ) {
	global $WCFM;
	if( apply_filters( 'wcfm_is_pref_policies', true ) && apply_filters( 'wcfm_is_allow_product_policies', true ) ) {
		unset( $tabs['policies'] );
		$tabs['wcfm_policies_tab'] = apply_filters( 'wcfm_policies_tab_element',array(
																																								'title' 	=> $WCFM->wcfm_policy->get_policy_tab_title(),
																																								'priority' 	=> apply_filters( 'wcfm_policies_tab_priority', 99 ),
																																								'callback' 	=> array( $WCFM->wcfm_policy, 'wcfm_policies_product_tab_content' )
																																							) );
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'wcfm_policies_product_tab', 99 );

/**
 * WCFM BuddyP-ress Functions
 *
 * @since		3.4.2
 */
function bp_wcfm_user_nav_item() {
	global $bp;
	
	if( !$bp || !$bp->displayed_user || !$bp->displayed_user->userdata || !$bp->displayed_user->id ) return;
	
	$other_member_profile = false;
	
	if( is_user_logged_in() ) {
	  $current_user_id = get_current_user_id();
		if( wcfm_is_vendor( $current_user_id ) && ( $current_user_id == $bp->displayed_user->id ) ) {
			$pages = get_option("wcfm_page_options");
			$wcfm_page = get_post( $pages['wc_frontend_manager_page_id'] );
			
			$args = array(
							'name' => $wcfm_page->post_title,
							'slug' => $wcfm_page->post_name,
							'default_subnav_slug' => $wcfm_page->post_name,
							'position' => 50,
							'screen_function' => 'bp_wcfm_user_nav_item_screen',
							'item_css_id' => $wcfm_page->post_name
			);
		
			bp_core_new_nav_item( $args );
		} else {
			$other_member_profile = true;
		}
	} else {
		$other_member_profile = true;
	}
	
	if( $other_member_profile ) {
		do_action( 'wcfm_buddypress_show_vendor_store_link', $bp->displayed_user->id );
	}
}

function bp_wcfm_set_as_current_component( $is_current_component, $component ) {
	if ( empty( $component ) ) {
		return false;
	}

	if( $component == 'wcfm' ) {
		if( is_wcfm_page() ) {
			$is_current_component = true;
		}
	}
	
	return $is_current_component;
}

if( apply_filters( 'wcfm_is_pref_buddypress', true ) && WCFM_Dependencies::wcfm_biddypress_plugin_active_check() ) {
	$wcfm_options = (array) get_option( 'wcfm_options' );
	$wcfm_module_options = isset( $wcfm_options['module_options'] ) ? $wcfm_options['module_options'] : array();
	$wcfm_buddypress_off = ( isset( $wcfm_module_options['buddypress'] ) ) ? $wcfm_module_options['buddypress'] : 'no';
  if( $wcfm_buddypress_off == 'no' ) {
		add_filter( 'bp_is_current_component', 'bp_wcfm_set_as_current_component', 10, 2 );
		add_action( 'bp_setup_nav', 'bp_wcfm_user_nav_item', 99 );
	}
	
	add_filter( 'bp_user_can', function( $retval, $user_id, $capability, $site_id, $args ) {
		if( wcfm_is_vendor( $user_id ) &&  ( $capability == 'bp_moderate' ) ) { $retval = false; }
		return $retval;
	}, 50, 5 );
}

/**
 * the calback function from our nav item arguments
 */
function bp_wcfm_user_nav_item_screen() {
	add_action( 'bp_template_content', 'bp_wcfm_screen_content' );
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

/**
 * the function hooked to bp_template_content, this hook is in plugns.php
 */
function bp_wcfm_screen_content() {
	if( wcfm_is_allow_wcfm() ) {
	  echo do_shortcode( '[wcfm]' );
	}
}

/**
 * WP User Avatar Plugin Support
 */
add_filter( 'wpua_is_author_or_above', function( $is_author_or_above ) {
	global $wp_user_avatar, $wpua_is_profile, $current_user;
	
	if ( $wp_user_avatar && isset($current_user->roles) && is_array($current_user->roles) && array_intersect( array( 'wcfm_vendor', 'wcfm_affiliate', 'wcfm_delivery', 'shop_staff' ), $current_user->roles ) ) {
		$wpua_is_profile = 0;
		$is_author_or_above = true;
	}
	return $is_author_or_above;
}, 999 );

add_filter( 'media_view_settings', function( $setting ) {
	global $wp_user_avatar, $current_user;
	if ( $wp_user_avatar && isset($current_user->roles) && is_array($current_user->roles) && array_intersect( array( 'wcfm_vendor', 'wcfm_affiliate', 'wcfm_delivery', 'shop_staff' ), $current_user->roles ) ) {
		remove_filter('media_view_settings', array($wp_user_avatar, 'wpua_media_view_settings'), 10, 1);
	}
	return $setting;
}, 9, 1 );

/** 
 * Post counter plugin support 
 */
add_filter( 'pvc_get_post_views', function( $post_views, $post_id ) {
	$post_type = get_post_type( $post_id );
	if( $post_type && ( $post_type == 'product' ) ) {
		$post_views = (int) get_post_meta( $post_id, '_wcfm_product_views', true );
		if( !$post_views ) $post_views = 0;
	}
	return $post_views;
}, 50, 2);

function wcfm_unique_obj_list( $objs ) {
	$idList = array();
	
	foreach( $objs as $obj ) {
		if( !in_array( $obj->ID, array_keys( $idList ) ) ) {
			$idList[$obj->ID]= $obj;
		}
	}
	return $idList;
}

function wcfm_wp_date_format_to_js( $date_format ) {
	
	//$date_format = strtoupper( $date_format );
	//$date_format = str_replace( 'F', 'MMMM', $date_format );
	//$date_format = str_replace( 'J', 'D', $date_format );
	//$date_format = str_replace( 'Y', 'YYYY', $date_format );
	
	switch( $date_format ) {
		//Predefined WP date formats
		case 'jS F Y':
		  $date_format = 'd MM, YYYY';
		break;
		
	  case 'F j, Y':
		  $date_format = 'MM dd, yy';
		break;
		
	  case 'Y/m/d':
		  $date_format = 'yy/mm/dd';
		break;
		
	  case 'm/d/Y':
		  $date_format = 'mm/dd/yy';
		break;
		
	  case 'd/m/Y':
		  $date_format = 'dd/mm/yy';
		break;

		case 'Y-m-d':
		  $date_format = 'yy-mm-dd';
		break;
		
	  case 'm-d-Y':
		  $date_format = 'mm-dd-yy';
		break;
		
	  case 'd-m-Y':
		  $date_format = 'dd-mm-yy';
		break;

		default:
		  $date_format = 'yy-mm-dd';
	}
	
	return apply_filters( 'wcfm_wp_date_format_to_js', ( $date_format ) );
}

add_filter( 'wp_mail_content_type', function( $content_type ) {
	if( defined('DOING_WCFM_EMAIL') ) {
		return 'text/html';
	}
	
	return $content_type;
});

// WooCommerce Multilingual FIX
add_filter( 'woocommerce_email_get_option', function( $value, $email ) {
	if( defined( 'DOING_WCFM_EMAIL' ) ) {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
			remove_all_filters( 'woocommerce_email_get_option' );
		}
	}
	return $value;
}, 9, 2 );

add_filter( 'wp_mail', function( $email ) {
	if( defined('DOING_WCFM_EMAIL') && !defined('DOING_WCFM_RESTRICTED_EMAIL') ) {
		$wcfm_options = get_option( 'wcfm_options', array() );
		$email_cc_address = isset( $wcfm_options['email_cc_address'] ) ? $wcfm_options['email_cc_address'] : '';
		$email_bcc_address = isset( $wcfm_options['email_bcc_address'] ) ? $wcfm_options['email_bcc_address'] : '';
		if( is_array( $email['headers'] ) ) {
			$email['headers'][] = 'Content-Type:  text/html';
			if( $email_cc_address ) {
				$email['headers'][] = 'cc: '.$email_cc_address;
			}
			if( $email_bcc_address ) {
				$email['headers'][] = 'Bcc: '.$email_bcc_address;
			}
		} else {
			$email['headers'] .= 'Content-Type:  text/html'."\r\n";
			if( $email_cc_address ) {
				$email['headers'] .= 'cc: '.$email_cc_address."\r\n";
			}
			if( $email_bcc_address ) {
				$email['headers'] .= 'Bcc: '.$email_bcc_address."\r\n";
			}
		}
	}
	return $email;               
});

// Function to change sender name
function wcfm_email_from_name( $email_from_name ) {
	if( defined('DOING_WCFM_EMAIL') && !defined('DOING_WCFM_RESTRICTED_EMAIL') ) {
		$wcfm_options = get_option( 'wcfm_options', array() );
		$email_from_name = isset( $wcfm_options['email_from_name'] ) ? $wcfm_options['email_from_name'] : get_bloginfo( 'name' );
	}
	return $email_from_name;
}
add_filter( 'wp_mail_from_name', 'wcfm_email_from_name' );

// Function to change email address
function wcfm_email_from_address( $email_from_address ) {
	if( defined('DOING_WCFM_EMAIL') && !defined('DOING_WCFM_RESTRICTED_EMAIL') ) {
		$wcfm_options = get_option( 'wcfm_options', array() );
		$email_from_address = isset( $wcfm_options['email_from_address'] ) ? $wcfm_options['email_from_address'] : get_option('admin_email');
	}
  return $email_from_address;
}
add_filter( 'wp_mail_from', 'wcfm_email_from_address' );

// WP Mail Function Check - For Future
function wcfm_check_php_mail( $i ) {
	$status = 1;
	wcfm_log( "Mail Check Status:: " . $status );
}

function wcfm_force_user_can_richedit( $is_allow ) {
	return true;
}

/**
 * WCFM get attachment URL by ID
 */
if( !function_exists( 'wcfm_get_attachment_url') ) {
	function wcfm_get_attachment_url( $attachment_id ) {
		$attachment_url = '';
		if( $attachment_id && is_numeric( $attachment_id ) ) {
			$attachment_url = wp_get_attachment_url( $attachment_id );
		} else {
			$attachment_url = $attachment_id;
		}
		return $attachment_url;
	}
}

/**
 * WCFM Handle Form File Upload
 */
function wcfm_handle_file_upload( $is_multiple = true ) {
	$attchments = array();
	if ( ! empty( $_FILES ) ) {
		if( $is_multiple ) {
			foreach ( $_FILES as $file_key => $file_arr ) {
				if( isset( $file_arr['name'] ) && count( $file_arr['name'] ) > 0 ) {
					for( $fi = 0; $fi < count($file_arr['name']); $fi++ ) {
						if( isset( $file_arr['name'][$fi]['file'] ) && !empty( $file_arr['name'][$fi]['file'] ) ) {
							$file = array( 
														 'name'     => $file_arr['name'][$fi]['file'],
														 'type'     => $file_arr['type'][$fi]['file'],
														 'tmp_name' => $file_arr['tmp_name'][$fi]['file'],
														 'error'    => $file_arr['error'][$fi]['file'],
														 'size'     => $file_arr['size'][$fi]['file'],
														);
					
							$files_to_upload = wcfm_prepare_uploaded_files( $file );
							if( !empty( $files_to_upload ) ) {
								foreach ( $files_to_upload as $file_to_upload ) {
									$uploaded_file = wcfm_upload_file(
										$file_to_upload,
										array(
											'file_key' => $fi,
										)
									);
						
									if ( !is_wp_error( $uploaded_file ) ) {
										$attchments[$file_key][$fi] = $uploaded_file->url;
									} else {
										wcfm_log( "Inquiry Attachment Error:: " . $uploaded_file->get_error_message() );
									}
								}
							}
						}
					}
				}
			}
		} else {
			foreach ( $_FILES as $file_key => $file ) {
				$files_to_upload = wcfm_prepare_uploaded_files( $file );
				if( !empty( $files_to_upload ) ) {
					foreach ( $files_to_upload as $file_to_upload ) {
						$uploaded_file = wcfm_upload_file(
							$file_to_upload,
							array(
								'file_key' => $file_key,
							)
						);
			
						if ( !is_wp_error( $uploaded_file ) ) {
							$attchments[$file_key] = $uploaded_file->url;
						} else {
							wcfm_log( "Inquiry Attachment Error:: " . $uploaded_file->get_error_message() );
						}
					}
				}
			}
		}
	}
	
	return $attchments;
}

/**
 * WCFM Direct File Upload
 */
function wcfm_prepare_uploaded_files( $file_data ) {
	$files_to_upload = array();
	
	if( !empty( $file_data['name'] ) ) {
		if ( is_array( $file_data['name'] ) ) {
			foreach ( $file_data['name'] as $file_data_key => $file_data_value ) {
				if ( $file_data['name'][ $file_data_key ] ) {
					$type              = wp_check_filetype( $file_data['name'][ $file_data_key ] ); // Map mime type to one WordPress recognises.
					$files_to_upload[] = array(
						'name'     => time() . '-' . $file_data['name'][ $file_data_key ],
						'type'     => $type['type'],
						'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
						'error'    => $file_data['error'][ $file_data_key ],
						'size'     => $file_data['size'][ $file_data_key ],
					);
				}
			}
		} else {
			$type              = wp_check_filetype( $file_data['name'] ); // Map mime type to one WordPress recognises.
			$file_data['name'] = time() . '-' . $file_data['name'];
			$file_data['type'] = $type['type'];
			$files_to_upload[] = $file_data;
		}
	}

	return apply_filters( 'wcfm_prepare_uploaded_files', $files_to_upload );
}

/**
 * Uploads a file using WordPress file API.
 *
 * @since  5.2.7
 * @param  array|WP_Error      $file Array of $_FILE data to upload.
 * @param  string|array|object $args Optional arguments.
 * @return stdClass|WP_Error Object containing file information, or error.
 */
function wcfm_upload_file( $file, $args = array() ) {
	global $wcfm_upload, $wcfm_uploading_file;

	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/media.php';

	$args = wp_parse_args(
		$args,
		array(
			'file_key'           => '',
			'file_label'         => '',
			'allowed_mime_types' => '',
		)
	);

	$wcfm_upload         = true;
	$wcfm_uploading_file = $args['file_key'];
	$uploaded_file       = new stdClass();
	if ( '' === $args['allowed_mime_types'] ) {
		$allowed_mime_types = wcfm_get_allowed_mime_types( $wcfm_uploading_file );
	} else {
		$allowed_mime_types = $args['allowed_mime_types'];
	}

	/**
	 * Filter file configuration before upload
	 *
	 * This filter can be used to modify the file arguments before being uploaded, or return a WP_Error
	 * object to prevent the file from being uploaded, and return the error.
	 *
	 * @since 5.2.7
	 *
	 * @param array $file               Array of $_FILE data to upload.
	 * @param array $args               Optional file arguments.
	 * @param array $allowed_mime_types Array of allowed mime types from field config or defaults.
	 */
	$file = apply_filters( 'wcfm_upload_file_pre_upload', $file, $args, $allowed_mime_types );

	if ( is_wp_error( $file ) ) {
		return $file;
	}
	//print_r( $file );
	
	if ( ! in_array( $file['type'], $allowed_mime_types, true ) ) {
		if ( $args['file_label'] ) {
			// translators: %1$s is the file field label; %2$s is the file type; %3$s is the list of allowed file types.
			return new WP_Error( 'upload', sprintf( __( '"%1$s" (filetype %2$s) needs to be one of the following file types: %3$s', 'wc-frontend-manager' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $allowed_mime_types ) ) ) );
		} else {
			// translators: %s is the list of allowed file types.
			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'wc-frontend-manager' ), implode( ', ', array_keys( $allowed_mime_types ) ) ) );
		}
	} else {
		$upload = wp_handle_upload( $file, apply_filters( 'submit_file_wp_handle_upload_overrides', array( 'test_form' => false ) ) );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'] );
		} else {
			$uploaded_file->url       = $upload['url'];
			$uploaded_file->file      = $upload['file'];
			$uploaded_file->name      = basename( $upload['file'] );
			$uploaded_file->type      = $upload['type'];
			$uploaded_file->size      = $file['size'];
			$uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
		}
	}

	$wcfm_upload         = false;
	$wcfm_uploading_file = '';

	return $uploaded_file;
}

/**
 * Returns mime types specifically for WPJM.
 *
 * @since   5.2.7
 * @param   string $field Field used.
 * @return  array  Array of allowed mime types
 */
function wcfm_get_allowed_mime_types( $field = '' ) {
	
	$allowed_mime_types = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif'          => 'image/gif',
		'png'          => 'image/png',
		'pdf'          => 'application/pdf',
		'doc'          => 'application/msword',
		'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	);

	/**
	 * Mime types to accept in uploaded files.
	 *
	 * Default is image, pdf, and doc(x) files.
	 *
	 * @since 1.25.1
	 *
	 * @param array  {
	 *     Array of allowed file extensions and mime types.
	 *     Key is pipe-separated file extensions. Value is mime type.
	 * }
	 * @param string $field The field key for the upload.
	 */
	return apply_filters( 'wcfm_mime_types', $allowed_mime_types, $field );
}

// WCfM Video Tutorial
function wcfm_video_tutorial( $video_url ) {
	if( !$video_url ) return;
	if( !apply_filters( 'wcfm_is_allow_video_tutorial', true ) ) return;
	?>
	<p class="wcfm_tutorials_wrapper">
	  <a class="wcfm_tutorials" href="<?php echo $video_url; ?>">
	    <span class="wcfm_tutorials_icon wcfmfa fa-video"></span>
	    <span class='wcfm_tutorials_label'><?php _e( 'Tutorial', 'wc-frontend-manager' ); ?></span>
	  </a>
	</p>
	<?php
}

function wcfm_get_option( $key, $default_val = '', $lang_code = '' ) {
	$option_val = get_option( $key, $default_val );
	
	// WPML Support
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
		global $sitepress;
		if( !$lang_code ) {
			$current_language = $sitepress->get_current_language();
		} else {
			$current_language = $lang_code;
		}
		$option_val = get_option( $key . '_' . $current_language, $option_val );
	}
	
	return $option_val;
}

function wcfm_update_option( $key, $option_val ) {
	// WPML Support
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
		global $sitepress;
		$current_language = $sitepress->get_current_language();
		update_option( $key . '_' . $current_language, $option_val );
	} else {
		update_option( $key, $option_val );
	}
}

function wcfm_get_post_meta( $post_id, $key, $is_single = true ) {
	$meta_val = get_post_meta( $post_id, $key, $is_single );
	
	// WPML Support
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
		global $sitepress;
		$current_language = $sitepress->get_current_language();
		$option_val_wpml = get_post_meta( $post_id, $key . '_' . $current_language, $is_single );
		if( $option_val_wpml ) $meta_val = $option_val_wpml;
	}
	
	return $meta_val;
}

function wcfm_update_post_meta( $post_id, $key, $meta_val ) {
	// WPML Support
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
		global $sitepress;
		$current_language = $sitepress->get_current_language();
		update_post_meta( $post_id, $key . '_' . $current_language, $meta_val );
	} else {
		update_post_meta( $post_id, $key, $meta_val );
	}
}

function wcfm_get_user_meta( $user_id, $key, $is_single = true ) {
	$meta_val = get_user_meta( $user_id, $key, $is_single );
	
	// WPML Support
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
		global $sitepress;
		$current_language = $sitepress->get_current_language();
		$option_val_wpml = get_user_meta( $user_id, $key . '_' . $current_language, $is_single );
		if( $option_val_wpml ) $meta_val = $option_val_wpml;
	}
	
	return $meta_val;
}

function wcfm_update_user_meta( $user_id, $key, $meta_val ) {
	// WPML Support
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
		global $sitepress;
		$current_language = $sitepress->get_current_language();
		update_user_meta( $user_id, $key . '_' . $current_language, $meta_val );
	} else {
		update_user_meta( $user_id, $key, $meta_val );
	}
}

function wcfm_empty( $content ) {
	$content = wp_strip_all_tags( $content );
	if( empty( $content ) ) return true;
	return false;
}

function wcfm_removeslashes( $string ) {
	$string = implode("",explode("\\",$string));
	return stripslashes(trim($string));
}


function wcfm_strip_html( $content ) {
	$breaks = apply_filters( 'wcfm_editor_newline_generators', array("<br />","<br>","<br/>") ); 
			
	$content = str_ireplace( $breaks, "\r\n", $content );
	$content = strip_tags( $content );
	return $content;
}

function wcfm_stripe_newline( $content ) {
	$content = preg_replace("/\r\n|\r|\n/", '<br/>', wcfm_removeslashes( $content ) );
	return $content;
}
 
function wcfm_standard_date( $date_string ) {
	if( $date_string ) {
		if( wc_date_format() == 'd/m/Y' ) {
			$date_string = str_replace( '/', '-', $date_string );
		}
		$date_string = strtotime( $date_string );
		$date_string = date( 'Y-m-d', $date_string );
	}
	return $date_string;
}

function wcfm_filter_content_email_phone( $content ) {
	$patterns = array();
	$patterns[0] = '/([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/';
	$patterns[1] = '/(?:(?:\+?([1-9]|[0-9][0-9]|[0-9][0-9][0-9])\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([0-9][1-9]|[0-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?/';
	//$patterns[2] = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';

	$replacements = array();
	$replacements[0] = '';
	$replacements[1] = '';
	//$replacements[2] = '';

	// should use just one call of preg_replace for perfomance issues
	$content = preg_replace( $patterns, $replacements, $content );	
	
	return $content;
}

add_filter( 'wcfm_editor_content_before_save', function( $content ) {
	$content = str_replace( '<script>', '', $content );
	$content = str_replace( '</script>', '', $content );
	return $content;
}, 750 );

/**
 * WCFM Hide Field
 */
function wcfm_hide_field( $field, $field_group, $type = '' ) {
	if( $field && $field_group && is_array( $field_group ) && !empty( $field_group ) && isset( $field_group[$field] ) ) {
		$field_group[$field]['class'] = 'wcfm_custom_hide';
		$field_group[$field]['label_class'] = 'wcfm_custom_hide';
		$field_group[$field]['desc_class'] = 'wcfm_custom_hide';
	}
	return $field_group;
}

/**
 * WCFM Number to Text
 */
function wcfm_number_to_words( $num ) {

	$ones = array(
								 "",
								 "one",
								 "two",
								 "three",
								 "four",
								 "five",
								 "six",
								 "seven",
								 "eight",
								 "nine",
								 "ten",
								 "eleven",
								 "twelve",
								 "thirteen",
								 "fourteen",
								 "fifteen",
								 "sixteen",
								 "seventeen",
								 "eighteen",
								 "nineteen"
								);
	$tens = array(
								 "",
								 "",
								 "twenty",
								 "thirty",
								 "forty",
								 "fifty",
								 "sixty",
								 "seventy",
								 "eighty",
								 "ninety"
								);
	$hundreds = array(
										 "",
										 "thousand",
										 "million",
										 "billion",
										 "trillion",
										 "quadrillion",
										 "quintillion",
										 "sextillion",
										 "septillion",
										 "octillion",
										 "nonillion"
										);/*limit t quadrillion */
	
	$num = number_format($num,2,".",","); 
	$num_arr = explode(".",$num); 
	$wholenum = $num_arr[0]; 
	$decnum = $num_arr[1]; 
	$whole_arr = array_reverse(explode(",",$wholenum)); 
	krsort($whole_arr,1); 
	$rettxt = ""; 
	foreach($whole_arr as $key => $i){
		
		while(substr($i,0,1)=="0")
				$i=substr($i,1,5);
		if($i < 20){ 
			/* echo "getting:".$i; */
			if( isset( $ones[$i] ) ) $rettxt .= $ones[$i]; 
		}elseif($i < 100){ 
			if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
			if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)]; 
		}else{ 
			if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
			if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)]; 
			if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)]; 
		} 
		if($key > 0){ 
			$rettxt .= " ".$hundreds[$key]." "; 
		}
	}
	if($decnum > 0){
		if( $rettxt ) $rettxt .= " and ";
		if($decnum < 20) {
			if( isset( $ones[$decnum] ) ) $rettxt .= $ones[$decnum];
		} elseif($decnum < 100) {
			$rettxt .= $tens[substr($decnum,0,1)];
			$rettxt .= " ".$ones[substr($decnum,1,1)];
		}
	}
	return apply_filters( 'wcfm_number_to_words', ucfirst( $rettxt ), $num );
}

function wcfm_replace_unsupported_icons( $icon ) {
	$matching_icons = array(
		                       'codepen'          => 'file-alt',
		                       'user-o'           => 'user-alt',
		                       'user-circle-o'    => 'user-circle',
		                       'line-chart'       => 'chart-line',
		                       'calendar-check-o' => 'calendar',
		                       'pie-chart'        => 'chart-pie',
		                       'comments-o'       => 'comment-alt',
		                       'money'            => 'money-bill-alt'
								          );
	$icon = isset( $matching_icons[$icon] ) ? $matching_icons[$icon] : $icon;
	return $icon;
}

/**
 * Helper function for logging
 *
 * For valid levels, see `WC_Log_Levels` class
 *
 * Description of levels:
 *     'emergency': System is unusable.
 *     'alert': Action must be taken immediately.
 *     'critical': Critical conditions.
 *     'error': Error conditions.
 *     'warning': Warning conditions.
 *     'notice': Normal but significant condition.
 *     'info': Informational messages.
 *     'debug': Debug-level messages.
 *
 * @param string $message
 */
if(!function_exists('wcfm_create_log')) {
	function wcfm_create_log( $message, $level = 'debug', $source = 'wcfm' ) {
		$logger  = wc_get_logger();
		$context = array( 'source' => $source );

		return $logger->log( $level, $message, $context );
	}
}

if(!function_exists('wcfm_log')) {
	function wcfm_log( $message, $level = 'debug' ) {
		wcfm_create_log( $message, $level );
	}
}

/*add_filter( 'locale', function( $locale ) {
	global $_SESSION;
	if( !is_admin() ) {
		if( isset( $_SESSION['wcfm_my_locale'] ) && !empty( $_SESSION['wcfm_my_locale'] ) ) {
			$locale = $_SESSION['wcfm_my_locale'];
		}
	}
	return $locale;
});*/
?>