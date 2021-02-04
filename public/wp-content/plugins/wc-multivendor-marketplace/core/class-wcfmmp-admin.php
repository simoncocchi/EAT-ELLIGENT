<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Admin
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
class WCFMmp_Admin {
	
	public function __construct() {
 		global $WCFM, $WCFMmp;
 		
 		// Browse WCFM Marketplace setup page
 		add_action( 'admin_init', array( &$this, 'wcfmmp_redirect_to_setup' ), 5 );
 		
 		// WCFM - Membership inactive notice 
		if(!WCFMmp_Dependencies::wcfm_plugin_active_check()) {
			add_action( 'admin_notices', array( &$this, 'wcfmmp_wcfm_inactive_notice' ) );
		}
 		
 		if(WCFMmp_Dependencies::woocommerce_plugin_active_check() && WCFMmp_Dependencies::wcfm_plugin_active_check()) {
 			
 			// Multi-vendor Conflict check
 			if( wcfmmp_has_marketplace() ) {
 				if( apply_filters( 'wcfmmp_has_marketplace_notice_show', true ) ) {
 					add_action( 'admin_notices', array( &$this, 'wcfmmp_has_marketplace_notice' ) );
 				}
 			}
 			
 			// WCFM - Membership inactive notice 
			if(!WCFM_Dependencies::wcfmvm_plugin_active_check()) {
				if( apply_filters( 'wcfmmp_wcfmvm_inactive_notice_show', true ) ) {
					add_action( 'admin_notices', array( &$this, 'wcfmmp_wcfmvm_inactive_notice' ) );
				}
			}
 			
			/**
			 * Register our WCFM Marketplace to the admin_menu action hook
			 */
			if( apply_filters( 'wcfmmp_is_allow_admin_menu', true ) ) {
				add_action( 'admin_menu', array( &$this, 'wcfmmp_options_page' ) );
			}
			
			// Vendor Column in Post List
			add_filter( 'manage_edit-post_columns', array( &$this, 'wcfmmp_store_post_columns' ) );
			add_filter( 'manage_edit-product_columns', array( &$this, 'wcfmmp_store_post_columns' ) );
			add_filter( 'manage_edit-shop_coupon_columns', array( &$this, 'wcfmmp_store_post_columns' ) );
			add_filter( 'manage_edit-job_listing_columns', array( &$this, 'wcfmmp_store_post_columns' ) );
			add_filter( 'manage_edit-shop_order_columns', array( &$this, 'wcfmmp_store_post_columns' ) );
			add_filter( 'manage_edit-shop_subscription_columns', array( &$this, 'wcfmmp_store_post_columns' ) );
			add_filter( 'manage_edit-wc_booking_columns', array( &$this, 'wcfmmp_store_post_columns' ), 99 );
			add_filter( 'manage_edit-wc_appointment_columns', array( &$this, 'wcfmmp_store_post_columns' ), 99 );
			
			// Vendor Column Data Under Post List
			add_action( 'manage_post_posts_custom_column' , array( &$this, 'wcfmmp_store_post_custom_column' ), 10, 2 );
			add_action( 'manage_product_posts_custom_column' , array( &$this, 'wcfmmp_store_post_custom_column' ), 10, 2 );
			add_action( 'manage_shop_coupon_posts_custom_column' , array( &$this, 'wcfmmp_store_post_custom_column' ), 10, 2 );
			add_action( 'manage_job_listing_posts_custom_column' , array( &$this, 'wcfmmp_store_post_custom_column' ), 10, 2 );
			add_action( 'manage_shop_order_posts_custom_column' , array( &$this, 'wcfmmp_store_shop_order_custom_column' ), 10, 2 );
			add_action( 'manage_shop_subscription_posts_custom_column' , array( &$this, 'wcfmmp_store_shop_subscription_custom_column' ), 10, 2 );
			add_action( 'manage_wc_booking_posts_custom_column' , array( &$this, 'wcfmmp_store_wc_booking_custom_column' ), 10, 2 );
			add_action( 'manage_wc_appointment_posts_custom_column' , array( &$this, 'wcfmmp_store_wc_appointment_custom_column' ), 10, 2 );
			
			// Vendor data tab at Product Page
			add_action( 'admin_head', array( &$this, 'wcfmmp_store_tab_style' ) );
			add_filter( 'woocommerce_product_data_tabs', array( &$this, 'wcfmmp_store_product_data_tab' ), 500 );
			add_action( 'woocommerce_product_data_panels', array( &$this, 'wcfmmp_store_product_data_fields' ) );
			
			// Product Commission
			add_action( 'woocommerce_product_data_panels', array( &$this, 'wcfmmp_store_commission_product_data_fields' ) );
			add_action( 'woocommerce_process_product_meta', array( &$this, 'wcfmmp_store_product_data_save' ), 500 );
			
			// Variation Commission
			add_action( 'woocommerce_product_after_variable_attributes', array( &$this, 'wcfmmp_store_variation_settings_fields' ), 500, 3 );
			add_action( 'woocommerce_save_product_variation', array( &$this, 'wcfmmp_store_save_variation_settings_fields' ), 10, 2 );
			
			// Category Commission
			add_action( 'product_cat_add_form_fields', array( &$this, 'wcfmmp_add_product_cat_commission_fields' ) );
			add_action( 'product_cat_edit_form_fields', array( &$this, 'wcfmmp_edit_product_cat_commission_fields' ), 100 );
			add_action( 'created_term', array( &$this, 'wcfmmp_save_product_cat_commission_fields' ), 100, 3 );
			add_action( 'edit_term', array( &$this, 'wcfmmp_save_product_cat_commission_fields' ), 100, 3 );
			
			// Remove Stripe WC Setting Fields
			//add_action( 'load-woocommerce_page_wc-settings', array( &$this, 'wcfmmp_disable_stripe_split_settings_admin_option' ), 500 );
		}
		
		// WCFM Admin Style
		add_action( 'admin_enqueue_scripts', array( &$this, 'wcfmmp_admin_script' ), 25 );
 	}
 	
 	/**
	 * WCFM Marketplace activation redirect transient
	 */
	function wcfmmp_redirect_to_setup(){
		if ( get_transient( '_wc_activation_redirect' ) ) {
			delete_transient( '_wc_activation_redirect' );
			return;
		}
		if ( get_transient( '_wcfmmp_activation_redirect' ) ) {
			delete_transient( '_wcfmmp_activation_redirect' );
			if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'wcfmmp-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || apply_filters( 'wcfmmp_prevent_automatic_setup_redirect', false ) ) {
			  return;
			}
			wp_safe_redirect( admin_url( 'index.php?page=wcfmmp-setup' ) );
			exit;
		}
	}
	
	/**
	 * WCFM Marketplace detect other multi-vendor in site notice
	 */
	function wcfmmp_has_marketplace_notice() {
		global $WCFM, $WCFMmp;
		$offer_msg = __( '<h2>
										   WCFM Marketplace: Multi Vendor Plugin Conflict Detected !!!
										 </h2>', 'wc-multivendor-marketplace' );
		$offer_msg .= sprintf( __( '<p %s>WCFM - Marketplace is installed and active. But there is another multi-vendor plugin found in your site. Now this is not possible to run a site with more than one multi-vendor plugins at a time. %sDisable <b><u>%s</u></b> to make your site stable and run smoothly.</p>', 'wc-multivendor-marketplace' ), 'style="width: 100%;"', '<br/>', wcfmmp_has_marketplace() );
		?>
			<div class="notice wcfm_addon_inactive_notice_box wcfmmp_addon_inactive_notice_box" id="wcfm-conflict-notice">
				<img style="margin-top: 8px;" src="<?php echo $WCFMmp->plugin_url . 'assets/images/'; ?>Conflict-larger-word-400x.png" alt="">
				<?php echo $offer_msg; ?>
			</div>
		<?php
	}
	
	/**
	 * WCFM - Missing notice
	 *
	 * @since  1.1.5
	 *
	 * @return void
	 */
	public function wcfmmp_wcfm_inactive_notice() {
		global $WCFM, $WCFMmp;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$offer_msg = __( '<h2>
										   WCFM Marketplace Inactive: WCFM Core Missing !!!
										 </h2>', 'wc-multivendor-marketplace' );
		$offer_msg .= __( '<p>WCFM Marketplace is inactive. WooCommerce Frontend Manager (WCFM Core) must be active for the WCFM Marketplace to work. Please install & activate WooCommerce Frontend Manager.</p>', 'wc-multivendor-marketplace' );
		?>
			<div class="notice wcfm_addon_inactive_notice_box wcfmmp_addon_inactive_notice_box" id="wcfm-groups-sttafs-notice">
				<img src="<?php echo $WCFMmp->plugin_url . 'assets/images/'; ?>wcfm_marketplace_white_ogo.png" alt="">
				<?php echo $offer_msg; ?>
				<a href="<?php echo 'https://downloads.wordpress.org/plugin/wc-frontend-manager.zip'; //admin_url( 'plugin-install.php?tab=search&s=wc+frontend+manager' ); ?>" class="button button-primary promo-btn" target="_blank"><?php _e( 'GET IT NOW', 'wc-multivendor-marketplace' ); ?></a>
			</div>
		<?php
	}
	
	/**
	 * WCFM - Membership notice
	 *
	 * @since  1.1.5
	 *
	 * @return void
	 */
	public function wcfmmp_wcfmvm_inactive_notice() {
		global $WCFM, $WCFMmp;
		if ( ! current_user_can( 'manage_options' ) || !apply_filters( 'wcfmmp_is_allow_membership_notice', true ) ) {
			return;
		}

		$offer_msg = __( '<h2>
										   WCFM Marketplace: Vendor Registration Disable !!!
										 </h2>', 'wc-multivendor-marketplace' );
		$offer_msg .= __( '<p>WCFM - Membership is essential for WCFM Marketplace to register new vendors. You may additionally setup vendor membership using this as well. Recurring subscription also possible using PayPal and Stripe.</p>', 'wc-multivendor-marketplace' );
		?>
			<div class="notice wcfm_addon_inactive_notice_box wcfmmp_addon_inactive_notice_box" id="wcfm-groups-sttafs-notice">
				<img src="<?php echo $WCFMmp->plugin_url . 'assets/images/'; ?>wcfmgs-icon-128x128.jpg" alt="">
				<?php echo $offer_msg; ?>
				<a href="<?php echo admin_url( 'plugin-install.php?tab=search&s=wcfm+membership' ); ?>" class="button button-primary promo-btn" target="_blank"><?php _e( 'GET IT NOW', 'wc-multivendor-marketplace' ); ?></a>
			</div>
		<?php
	}
	
	/**
	 * WCFM Marketplace Menu at WP Menu
	 */
	function wcfmmp_options_page() {
    global $menu, $WCFMmp;
    
    if( function_exists( 'get_wcfm_settings_url' ) ) {
    	add_menu_page( __( 'Marketplace', 'wc-multivendor-marketplace' ), __( 'Marketplace', 'wc-multivendor-marketplace' ), 'manage_options', 'wcfm_settings_form_marketplace_head', null, null, '55' );
    	$menu[55] = array( __( 'Marketplace', 'wc-multivendor-marketplace' ), 'manage_options', get_wcfm_settings_url() . '#wcfm_settings_form_marketplace_head', '', 'open-if-no-js menu-top', '', $WCFMmp->plugin_url . 'assets/images/wcfmmp_icon.svg' );
    }
  }  
  
  function wcfmmp_store_post_columns( $columns ) {
  	global $WCFM, $WCFMmp;
  	if( function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
  		$columns['wcfm_vendor'] = apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-multivendor-marketplace' ) );
  	}
    return $columns;
  }
  
  function wcfmmp_store_post_custom_column( $column, $post_id ) {
  	global $WCFM, $WCFMmp;
  	
  	switch ( $column ) {
			case 'wcfm_vendor' :
				$vendor_name = '&ndash;';
				if( function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
					$store_name = wcfm_get_vendor_store_by_post( $post_id );
					if( $store_name ) {
						$vendor_name = '<span class="dashicons dashicons-businessperson"></span>' . $store_name;
					}
				}
				echo $vendor_name;
			break;
		}
  }
  
  function wcfmmp_store_shop_order_custom_column( $column, $order_id ) {
  	global $WCFM, $WCFMmp;
  	
  	switch ( $column ) {
			case 'wcfm_vendor' :
				$vendor_name = '';
				$processed_vendors = array();
				if( function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
					$order = wc_get_order( $order_id );
					if( is_a( $order , 'WC_Order' ) ) {
						$items = $order->get_items( 'line_item' );
						if( !empty( $items ) ) {
							foreach( $items as $order_item_id => $item ) {
								$line_item = new WC_Order_Item_Product( $item );
								$product  = $line_item->get_product();
								$product_id = $line_item->get_product_id();
								$vendor_id  = wcfm_get_vendor_id_by_post( $product_id );
								
								if( !$vendor_id ) continue;
								if( in_array( $vendor_id, $processed_vendors ) ) continue;
								
								$store_name = wcfm_get_vendor_store( $vendor_id );
								if( $store_name ) {
									if( $vendor_name ) $vendor_name .= "<br />";
									$vendor_name .= '<span class="dashicons dashicons-businessperson"></span>' . $store_name;
									$processed_vendors[$vendor_id] = $vendor_id;
								}
							}
						}
					}
				}
				
				if( !$vendor_name ) { $vendor_name = '&ndash;'; }
				
				echo $vendor_name;
			break;
		}
  }
  
  function wcfmmp_store_shop_subscription_custom_column( $column, $subscription_id ) {
  	global $WCFM, $WCFMmp;
  	
  	switch ( $column ) {
			case 'wcfm_vendor' :
				$vendor_name = '';
				$processed_vendors = array();
				if( function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
					$the_subscription = wcs_get_subscription( $subscription_id );
					$subscription_items = $the_subscription->get_items();
					if( !empty( $subscription_items ) ) {
						foreach( $subscription_items as $item_id => $item ) {
							$_product   = apply_filters( 'woocommerce_order_item_product', $the_subscription->get_product_from_item( $item ), $item );
							$product_id = $_product->get_ID();
							$vendor_id  = wcfm_get_vendor_id_by_post( $product_id );
							
							if( !$vendor_id ) continue;
							if( in_array( $vendor_id, $processed_vendors ) ) continue;
							
							$store_name = wcfm_get_vendor_store( $vendor_id );
							if( $store_name ) {
								if( $vendor_name ) $vendor_name .= "<br />";
								$vendor_name .= '<span class="dashicons dashicons-businessperson"></span>' . $store_name;
								$processed_vendors[$vendor_id] = $vendor_id;
							}
						}
					}
				}
				if( !$vendor_name ) { $vendor_name = '&ndash;'; }
				
				echo $vendor_name;
			break;
		}
  }
  
  function wcfmmp_store_wc_booking_custom_column( $column, $booking_id ) {
  	global $WCFM, $WCFMmp;
  	
  	switch ( $column ) {
			case 'wcfm_vendor' :
				$vendor_name = '&ndash;';
				if( function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
					$the_booking = new WC_Booking( $booking_id );
					$product_id  = $the_booking->get_product_id( 'edit' );
					$store_name = wcfm_get_vendor_store_by_post( $product_id );
					if( $store_name ) {
						$vendor_name = '<span class="dashicons dashicons-businessperson"></span>' . $store_name;
					}
				}
				echo $vendor_name;
			break;
		}
  }
  
  function wcfmmp_store_wc_appointment_custom_column( $column, $booking_id ) {
  	global $WCFM, $WCFMmp;
  	
  	switch ( $column ) {
			case 'wcfm_vendor' :
				$vendor_name = '&ndash;';
				if( function_exists( 'wcfm_get_vendor_store_by_post' ) ) {
					$the_appointment = new WC_Appointment( $booking_id );
					$product_id  = $the_appointment->get_product_id( 'edit' );
					$store_name = wcfm_get_vendor_store_by_post( $product_id );
					if( $store_name ) {
						$vendor_name = '<span class="dashicons dashicons-businessperson"></span>' . $store_name;
					}
				}
				echo $vendor_name;
			break;
		}
  }
  
  function wcfmmp_store_product_data_tab( $product_data_tabs ) {
  	global $WCFM, $WCFMmp;
  	
  	$product_data_tabs['wcfmmp-store-tab'] = array(
        'label' => apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-multivendor-marketplace' ) ),
        'target' => 'wcfmmp_store_product_data',
    );
    
    $product_data_tabs['wcfmmp-store-commission-tab'] = array(
        'label' => __( 'Commission', 'wc-multivendor-marketplace' ),
        'target' => 'wcfmmp_store_commission_product_data',
    );
    return $product_data_tabs;
  }
  
  function wcfmmp_store_tab_style() {?>
		<style>
		#woocommerce-product-data ul.wc-tabs li.wcfmmp-store-tab_options a:before { font-family: WooCommerce; content: '\e038'; }
		#woocommerce-product-data ul.wc-tabs li.wcfmmp-store-commission-tab_options a:before { font-family: WooCommerce; content: '\e604'; }
		</style>
		<script>
		jQuery(document).ready(function($) {
			$('#vendor_commission_mode').change(function() {
				$vendor_commission_mode = $(this).val();
				$('.commission_mode_field').hide();
				$('.commission_mode_'+$vendor_commission_mode).show();
			}).change();
			
			$(document).on('woocommerce_variations_loaded', function(event) {
				$('.var_commission_mode').each(function() {
					$(this).change(function() {
						$vendor_commission_mode = $(this).val();
						$(this).parent().parent().find('.var_commission_mode_field').hide();
						$(this).parent().parent().find('.var_commission_mode_'+$vendor_commission_mode).show();
					}).change();
				});
			});
			
			if( $("#wcfmmp_store").length > 0 ) {
				$("#wcfmmp_store").select2({
					placeholder: '<?php echo __( "Choose", "wc-frontend-manager" ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-multivendor-marketplace' ) ) . ' ...'; ?>',
					allowClear:  true,
				});
			}
		});
		</script>
		<?php
	}
  
  function wcfmmp_store_product_data_fields() {
  	global $WCFM, $WCFMmp, $post;
  	
  	echo '<div id ="wcfmmp_store_product_data" class="panel woocommerce_options_panel"><div class="options_group"><p class="form-field _wcfmmp_store_field">';
  	echo '<label for="wcfmmp_store">' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-multivendor-marketplace' ) ) . '</label>';
  	$vendor_arr = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list();
  	$vendor_id = wcfm_get_vendor_id_by_post( $post->ID );
  	$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"wcfmmp_store" => array( 'type' => 'select', 'class' => 'select short', 'options' => $vendor_arr, 'value' => $vendor_id, 'attributes' => array( 'style' => 'width:400px;' ) )
																											 ) );
		echo '</p></div></div>';
  }
  
  function wcfmmp_store_commission_product_data_fields() {
  	global $WCFM, $WCFMmp, $post;
  	
  	echo '<div id ="wcfmmp_store_commission_product_data" class="panel woocommerce_options_panel"><div class="options_group">';
  	
  	$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
		$wcfm_commission_for = isset( $wcfm_commission_options['commission_for'] ) ? $wcfm_commission_options['commission_for'] : 'vendor';
  	
  	$wcfm_commission_types = get_wcfm_marketplace_commission_types();
		$wcfm_commission_types = array_merge( array( 'global' => __( 'By Global Rule', 'wc-multivendor-marketplace' ) ), $wcfm_commission_types );
		if( isset( $wcfm_commission_types['by_sales'] ) ) unset( $wcfm_commission_types['by_sales'] );
		if( isset( $wcfm_commission_types['by_products'] ) ) unset( $wcfm_commission_types['by_products'] );
		if( isset( $wcfm_commission_types['by_quantity'] ) ) unset( $wcfm_commission_types['by_quantity'] );
		
		$vendor_commission_mode = 'global';
		$vendor_commission_fixed = '';
		$vendor_commission_percent = '';
		
		$tax_enable     = 'no';  
		$tax_name       = '';
		$tax_percent    = '';

		if( $post  ) {
			$product_commission_data = get_post_meta( $post->ID, '_wcfmmp_commission', true );
			if( empty($product_commission_data) ) $product_commission_data = array();
			
			$vendor_commission_mode        = isset( $product_commission_data['commission_mode'] ) ? $product_commission_data['commission_mode'] : 'global';
			$vendor_commission_fixed       = isset( $product_commission_data['commission_fixed'] ) ? $product_commission_data['commission_fixed'] : '';
			$vendor_commission_percent     = isset( $product_commission_data['commission_percent'] ) ? $product_commission_data['commission_percent'] : '90';
			
			$tax_enable                    = isset( $product_commission_data['tax_enable'] ) ? 'yes' : 'no';
			$tax_name                      = isset( $product_commission_data['tax_name'] ) ? $product_commission_data['tax_name'] : '';
			$tax_percent                   = isset( $product_commission_data['tax_percent'] ) ? $product_commission_data['tax_percent'] : '';
		}
		
		echo '<p class="form-field _wcfmmp_store_field"><label for="vendor_commission_mode">' . __( 'Commission For', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
			                                                "wcfm_commission_for" => array( 'type' => 'select', 'options' => array( 'vendor' => __( 'Vendor', 'wc-multivendor-marketplace' ), 'admin' => __( 'Admin', 'wc-multivendor-marketplace' ) ), 'attributes' => array( 'disabled' => true, 'style' => 'border: 0px !important;font-weight:600;color:#17a2b8;background:transparent;' ), 'class' => 'wcfm-select wcfm_ele select short', 'value' => $wcfm_commission_for )
			                                                ) );
		echo '<br/><br><span class="desciption">' . __( 'Always applicable as per global rule.', 'wc-multivendor-marketplace' ) . '</span>';
		echo '</p>';
		
		echo '<p class="form-field _wcfmmp_store_field"><label for="vendor_commission_mode">' . __( 'Commission Mode', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					                                              "vendor_commission_mode" => array('name' => 'commission[commission_mode]', 'type' => 'select', 'options' => $wcfm_commission_types, 'class' => 'wcfm-select wcfm_ele select short variable external grouped booking', 'value' => $vendor_commission_mode )
					                                              ) );
		echo '<br/><br><span class="desciption">' . __( 'Keep this as Global to apply commission rule as per vendor or marketplace commission setup.', 'wc-multivendor-marketplace' ) . '</span>';
		echo '</p>';
		
		echo '<p class="form-field _wcfmmp_commission_percent_field commission_mode_field commission_mode_percent commission_mode_percent_fixed"><label for="vendor_commission_percent">' . __( 'Commission Percent(%)', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					                                              "vendor_commission_percent" => array('name' => 'commission[commission_percent]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'value' => $vendor_commission_percent, 'attributes' => array( 'min' => '0', 'step' => '0.1') ),
					                                              ) );
		echo '</p>';
		
		echo '<p class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_fixed commission_mode_percent_fixed"><label for="vendor_commission_fixed">' . __( 'Commission Fixed', 'wc-multivendor-marketplace' ) . '(' . get_woocommerce_currency_symbol() . ')' . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					                                                                        "vendor_commission_fixed" => array('name' => 'commission[commission_fixed]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple variable external grouped booking commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'value' => $vendor_commission_fixed, 'attributes' => array( 'min' => '0', 'step' => '0.1') ),
																																									) );
		echo '</p>';
		
		echo '<h3 class="form-field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity" style="color:#17a2b8;margin-left:15px;">' . __('Commission Tax Settings', 'wc-multivendor-marketplace') . '</h3>';
		
		echo '<p class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity"><label for="vendor_commission_fixed">' . __( 'Enable', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_enable' => array( 'type' => 'checkbox', 'name' => 'commission[tax_enable]', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $tax_enable ),
																												) );
		echo '</p>';
		
		echo '<p class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity"><label for="vendor_commission_fixed">' . __( 'Tax Label', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_name' => array( 'placeholder' => __( 'Tax', 'wc-multivendor-marketplace' ), 'type' => 'text', 'name' => 'commission[tax_name]', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_name ),
																												) );
		echo '</p>';
		
		echo '<p class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity"><label for="vendor_commission_fixed">' . __( 'Tax Percent (%)', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_percent' => array( 'type' => 'number', 'name' => 'commission[tax_percent]', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_percent ),
																												) );
		echo '</p>';
  	
  	echo '</div></div>';
  }
  
  function wcfmmp_store_product_data_save( $product_id ) {
  	global $WCFM, $WCFMmp;
  	
  	$wcfmmp_store = isset( $_POST['wcfmmp_store'] ) ? absint( $_POST['wcfmmp_store'] ) : '';
  	if( $wcfmmp_store ) {
  		$arg = array(
							'ID' => $product_id,
							'post_author' => $wcfmmp_store,
						);
			wp_update_post( $arg );
			
			$WCFMmp->wcfmmp_vendor->wcfmmp_reset_vendor_taxonomy( $wcfmmp_store, $product_id );
			
			// Update vendor category list
			$pcategories = get_the_terms( $product_id, 'product_cat' );
			if( !empty($pcategories) ) {
				foreach($pcategories as $pkey => $pcategory) {
					$WCFMmp->wcfmmp_vendor->wcfmmp_save_vendor_taxonomy( $wcfmmp_store, $product_id, $pcategory->term_id );
				}
			}
			
			// For Variations
			$product = wc_get_product( $product_id );
			$wcfm_variable_product_types = apply_filters( 'wcfm_variable_product_types', array( 'variable', 'variable-subscription', 'pw-gift-card' ) );
			if( in_array( $product->get_type(), $wcfm_variable_product_types ) ) {
				foreach ( $product->get_children() as $child_id ) {
					$arg = array(
						'ID' => $child_id,
						'post_author' => $wcfmmp_store,
					);
					wp_update_post( $arg );
				}
			}
  	} else {
  		$old_vendor_id = wcfm_get_vendor_id_by_post( $product_id );
  		if( $old_vendor_id && wcfm_is_vendor( $old_vendor_id ) ) {
				$arg = array(
					'ID' => $product_id,
					'post_author' => get_current_user_id(),
				);
				wp_update_post( $arg );
			}
		}
		
		// Update Product Commission
		if( isset( $_POST['commission'] ) && !empty( $_POST['commission'] ) ) {
			update_post_meta( $product_id, '_wcfmmp_commission', wp_unslash($_POST['commission']) );
		}
  }
  
  function wcfmmp_store_variation_settings_fields( $loop, $variation_data, $variation ) {
  	global $WCFM, $WCFMmp;
  	
  	$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
		$wcfm_commission_for = isset( $wcfm_commission_options['commission_for'] ) ? $wcfm_commission_options['commission_for'] : 'vendor';
  	
  	$wcfm_commission_types = get_wcfm_marketplace_commission_types();
		$wcfm_commission_types = array_merge( array( 'global' => __( 'By Global Rule', 'wc-multivendor-marketplace' ) ), $wcfm_commission_types );
		if( isset( $wcfm_commission_types['by_sales'] ) ) unset( $wcfm_commission_types['by_sales'] );
		if( isset( $wcfm_commission_types['by_products'] ) ) unset( $wcfm_commission_types['by_products'] );
		if( isset( $wcfm_commission_types['by_quantity'] ) ) unset( $wcfm_commission_types['by_quantity'] );
  	
  	$variation_commission_data = get_post_meta( $variation->ID, '_wcfmmp_commission', true );
		if( empty($variation_commission_data) ) $variation_commission_data = array();
		
		$vendor_commission_mode = isset( $variation_commission_data['commission_mode'] ) ? $variation_commission_data['commission_mode'] : 'global';
		$vendor_commission_fixed = isset( $variation_commission_data['commission_fixed'] ) ? $variation_commission_data['commission_fixed'] : '';
		$vendor_commission_percent = isset( $variation_commission_data['commission_percent'] ) ? $variation_commission_data['commission_percent'] : '90';
		
  	// Commission Mode
		woocommerce_wp_select( 
		array( 
			'id'            => 'vendor_commission_mode[' . $variation->ID . ']', 
			'label'         => __( 'Commission Mode', 'wc-multivendor-marketplace' ), 
			'desc_tip'      => 'true',
			'wrapper_class' => 'form-row form-row-full',
			'class'         => 'var_commission_mode',
			'description'   => __( 'Keep this as Global to apply commission rule as per vendor or marketplace commission setup.', 'wc-multivendor-marketplace' ),
			'value'         => $vendor_commission_mode,
			'options'       => $wcfm_commission_types
			)
		);
  	
		// Commission Percent
		woocommerce_wp_text_input( 
			array( 
				'id'                => 'vendor_commission_percent[' . $variation->ID . ']', 
				'label'             => __( 'Commission Percent(%)', 'wc-multivendor-marketplace' ), 
				'value'             => $vendor_commission_percent,
				'wrapper_class'     => 'var_commission_mode_field var_commission_mode_percent var_commission_mode_percent_fixed',
				'custom_attributes' => array(
								'step' 	=> '0.1',
								'min'	=> '0'
							) 
			)
		);
		
		// Commission Fixed
		woocommerce_wp_text_input( 
			array( 
				'id'                => 'vendor_commission_fixed[' . $variation->ID . ']', 
				'label'             => __( 'Commission Fixed', 'wc-multivendor-marketplace' ) . '(' . get_woocommerce_currency_symbol() . ')', 
				'value'             => $vendor_commission_fixed,
				'wrapper_class'     => 'var_commission_mode_field var_commission_mode_fixed var_commission_mode_percent_fixed',
				'custom_attributes' => array(
								'step' 	=> '0.1',
								'min'	=> '0'
							) 
			)
		);
		
	}
	
	function wcfmmp_store_save_variation_settings_fields( $variation_id ) {
		global $WCFM, $WCFMmp;
		
		$variation_commission_data = get_post_meta( $variation_id, '_wcfmmp_commission', true );
		if( empty($variation_commission_data) ) $variation_commission_data = array();
			
		if( isset( $_POST['vendor_commission_mode'][$variation_id] ) ) {
			$variation_commission_data['commission_mode'] = wp_unslash($_POST['vendor_commission_mode'][$variation_id]);
		}
		if( isset( $_POST['vendor_commission_percent'][$variation_id] ) ) {
			$variation_commission_data['commission_percent'] = wp_unslash($_POST['vendor_commission_percent'][$variation_id]);
		}
		if( isset( $_POST['vendor_commission_fixed'][$variation_id] ) ) {
			$variation_commission_data['commission_fixed'] = wp_unslash($_POST['vendor_commission_fixed'][$variation_id]);
		}
		
		update_post_meta( $variation_id, '_wcfmmp_commission', $variation_commission_data );
	}
	
	/**
	 * Add commission field in create new category page
	 */
	public function wcfmmp_add_product_cat_commission_fields() {
		global $WCFM, $WCFMmp;
		
		$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
		$wcfm_commission_for = isset( $wcfm_commission_options['commission_for'] ) ? $wcfm_commission_options['commission_for'] : 'vendor';
		
		$wcfm_commission_types = get_wcfm_marketplace_commission_types();
		$wcfm_commission_types = array_merge( array( 'global' => __( 'By Global Rule', 'wc-multivendor-marketplace' ) ), $wcfm_commission_types );
		if( isset( $wcfm_commission_types['by_sales'] ) ) unset( $wcfm_commission_types['by_sales'] );
		if( isset( $wcfm_commission_types['by_products'] ) ) unset( $wcfm_commission_types['by_products'] );
		if( isset( $wcfm_commission_types['by_quantity'] ) ) unset( $wcfm_commission_types['by_quantity'] );
		
		$vendor_commission_mode = 'global';
		$vendor_commission_fixed = '';
		$vendor_commission_percent = '';
		
		$tax_enable   = 'no';
		$tax_name     = '';
		$tax_percent  = '';
		
		echo '<h3 class="form-field" style="color:#17a2b8;">' . __('Commission Settings', 'wc-multivendor-marketplace') . '</h3>';
		
		echo '<div class="form-field _wcfmmp_store_field"><label for="vendor_commission_mode">' . __( 'Commission For', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
			                                                "wcfm_commission_for" => array( 'type' => 'select', 'options' => array( 'vendor' => __( 'Vendor', 'wc-multivendor-marketplace' ), 'admin' => __( 'Admin', 'wc-multivendor-marketplace' ) ), 'attributes' => array( 'disabled' => true, 'style' => 'border: 0px !important;font-weight:600;color:#00897b;background:transparent;' ), 'class' => 'wcfm-select wcfm_ele select short', 'value' => $wcfm_commission_for )
			                                                ) );
		echo '<br/><br><span class="desciption">' . __( 'Always applicable as per global rule.', 'wc-multivendor-marketplace' ) . '</span>';
		echo '</div>';
		
		echo '<div class="form-field _wcfmmp_store_field"><label for="vendor_commission_mode">' . __( 'Commission Mode', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					                                              "vendor_commission_mode" => array('name' => 'commission[commission_mode]', 'type' => 'select', 'options' => $wcfm_commission_types, 'class' => 'wcfm-select wcfm_ele select short variable external grouped booking', 'value' => $vendor_commission_mode )
					                                              ) );
		echo '<br/><br><span class="desciption">' . __( 'Keep this as Global to apply commission rule as per vendor or marketplace commission setup.', 'wc-multivendor-marketplace' ) . '</span>';
		echo '</div>';
		
		echo '<div class="form-field _wcfmmp_commission_percent_field commission_mode_field commission_mode_percent commission_mode_percent_fixed"><label for="vendor_commission_percent">' . __( 'Commission Percent(%)', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					                                              "vendor_commission_percent" => array('name' => 'commission[commission_percent]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'value' => $vendor_commission_percent, 'attributes' => array( 'min' => '0', 'step' => '0.1') ),
					                                              ) );
		echo '</div>';
		
		echo '<div class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_fixed commission_mode_percent_fixed"><label for="vendor_commission_fixed">' . __( 'Commission Fixed', 'wc-multivendor-marketplace' ) . '(' . get_woocommerce_currency_symbol() . ')' . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												"vendor_commission_fixed" => array('name' => 'commission[commission_fixed]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple variable external grouped booking commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'value' => $vendor_commission_fixed, 'attributes' => array( 'min' => '0', 'step' => '0.1') ),
																												) );
		echo '</div>';
		
		echo '<h3 class="form-field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity" style="color:#17a2b8;">' . __('Commission Tax Settings', 'wc-multivendor-marketplace') . '</h3>';
		
		echo '<p class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity"><label for="vendor_commission_fixed">' . __( 'Enable', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_enable' => array( 'type' => 'checkbox', 'name' => 'commission[tax_enable]', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $tax_enable ),
																												) );
		echo '</p>';
		
		echo '<p class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity"><label for="vendor_commission_fixed">' . __( 'Tax Label', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_name' => array( 'placeholder' => __( 'Tax', 'wc-multivendor-marketplace' ), 'type' => 'text', 'name' => 'commission[tax_name]', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_name ),
																												) );
		echo '</p>';
		
		echo '<p class="form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity"><label for="vendor_commission_fixed">' . __( 'Tax Percent (%)', 'wc-multivendor-marketplace' ) . '</label>';
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_percent' => array( 'type' => 'number', 'name' => 'commission[tax_percent]', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_percent ),
																												) );
		echo '</p>';
	}

	/**
	 * Add commission field in edit category page
	 * @param Object $term
	 */
	public function wcfmmp_edit_product_cat_commission_fields( $term ) {
		global $WCFM, $WCFMmp;
		
		$wcfm_commission_options = get_option( 'wcfm_commission_options', array() );
		$wcfm_commission_for = isset( $wcfm_commission_options['commission_for'] ) ? $wcfm_commission_options['commission_for'] : 'vendor';
		
		$wcfm_commission_types = get_wcfm_marketplace_commission_types();
		$wcfm_commission_types = array_merge( array( 'global' => __( 'By Global Rule', 'wc-multivendor-marketplace' ) ), $wcfm_commission_types );
		if( isset( $wcfm_commission_types['by_sales'] ) ) unset( $wcfm_commission_types['by_sales'] );
		if( isset( $wcfm_commission_types['by_products'] ) ) unset( $wcfm_commission_types['by_products'] );
		if( isset( $wcfm_commission_types['by_quantity'] ) ) unset( $wcfm_commission_types['by_quantity'] );
		
		$category_commission_data = function_exists( 'get_term_meta' ) ? get_term_meta( $term->term_id, '_wcfmmp_commission', true ) : get_metadata( 'woocommerce_term', $term->term_id, '_wcfmmp_commission', true );
		if( empty($category_commission_data) ) $category_commission_data = array();
		
		$vendor_commission_mode        = isset( $category_commission_data['commission_mode'] ) ? $category_commission_data['commission_mode'] : 'global';
		$vendor_commission_fixed       = isset( $category_commission_data['commission_fixed'] ) ? $category_commission_data['commission_fixed'] : '';
		$vendor_commission_percent     = isset( $category_commission_data['commission_percent'] ) ? $category_commission_data['commission_percent'] : '90';
		
		$tax_enable                    = isset( $category_commission_data['tax_enable'] ) ? 'yes' : 'no';
		$tax_name                      = isset( $category_commission_data['tax_name'] ) ? $category_commission_data['tax_name'] : '';
		$tax_percent                   = isset( $category_commission_data['tax_percent'] ) ? $category_commission_data['tax_percent'] : '';
		
		echo '<tr><th colspan="2"><h3 class="form-field" style="color:#17a2b8;">' . __('Commission Settings', 'wc-multivendor-marketplace') . '</h3></th></tr>';
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
			                                                "wcfm_commission_for" => array( 'label' => __( 'Commission For', 'wc-multivendor-marketplace' ), 'type' => 'select', 'in_table' => true, 'options' => array( 'vendor' => __( 'Vendor', 'wc-multivendor-marketplace' ), 'admin' => __( 'Admin', 'wc-multivendor-marketplace' ) ), 'attributes' => array( 'disabled' => true, 'style' => 'border: 0px !important;font-weight:600;color:#00897b;background:transparent;' ), 'class' => 'wcfm-select wcfm_ele select short', 'wrapper_class' => "form-field _wcfmmp_store_field", 'value' => $wcfm_commission_for, 'desc' => __( 'Always applicable as per global rule.', 'wc-multivendor-marketplace' ) )
			                                                ) );
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					                                              "vendor_commission_mode" => array( 'label' => __( 'Commission Mode', 'wc-multivendor-marketplace' ), 'name' => 'commission[commission_mode]', 'type' => 'select', 'in_table' => true, 'options' => $wcfm_commission_types, 'class' => 'wcfm-select wcfm_ele select short variable external grouped booking', 'value' => $vendor_commission_mode, 'wrapper_class' => "form-field _wcfmmp_store_field", 'desc' => __( 'Keep this as Global to apply commission rule as per vendor or marketplace commission setup.', 'wc-multivendor-marketplace' ) )
					                                              ) );
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
					                                              "vendor_commission_percent" => array( 'label' => __( 'Commission Percent(%)', 'wc-multivendor-marketplace' ), 'name' => 'commission[commission_percent]', 'type' => 'number', 'in_table' => true, 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'value' => $vendor_commission_percent, 'attributes' => array( 'min' => '0', 'step' => '0.1'), 'wrapper_class' => "form-field _wcfmmp_commission_percent_field commission_mode_field commission_mode_percent commission_mode_percent_fixed" ),
					                                              ) );
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												"vendor_commission_fixed" => array( 'label' => __( 'Commission Fixed', 'wc-multivendor-marketplace' ) . '(' . get_woocommerce_currency_symbol() . ')', 'name' => 'commission[commission_fixed]', 'type' => 'number', 'in_table' => true, 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'value' => $vendor_commission_fixed, 'attributes' => array( 'min' => '0', 'step' => '0.1'), 'wrapper_class' => "form-field _wcfmmp_commission_fixed_field commission_mode_field commission_mode_fixed commission_mode_percent_fixed" ),
																												) );
		
		echo '<tr><th colspan="2"><h3 class="form-field commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity" style="color:#17a2b8;">' . __('Commission Tax Settings', 'wc-multivendor-marketplace') . '</h3></th></tr>';
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_enable' => array( 'label' => __( 'Enable', 'wc-multivendor-marketplace' ), 'type' => 'checkbox', 'name' => 'commission[tax_enable]', 'in_table' => true, 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $tax_enable ),
																												) );
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_name' => array( 'label' => __( 'Tax Label', 'wc-multivendor-marketplace' ), 'placeholder' => __( 'Tax', 'wc-multivendor-marketplace' ), 'type' => 'text', 'in_table' => true, 'name' => 'commission[tax_name]', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_name ),
																												) );
		
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												'tax_percent' => array( 'label' => __( 'Tax Percent (%)', 'wc-multivendor-marketplace' ), 'type' => 'number', 'name' => 'commission[tax_percent]', 'in_table' => true, 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_percent ),
																												) );
		echo '</p>';
	}

	/**
	 * Save commission settings for product category
	 * @param int $term_id
	 * @param int $tt_id
	 * @param string $taxonomy
	 */
	public function wcfmmp_save_product_cat_commission_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
		if( isset( $_POST['commission'] ) && 'product_cat' === $taxonomy ) {
			if( function_exists( 'update_term_meta' ) ) {
				update_term_meta( $term_id, '_wcfmmp_commission', wp_unslash($_POST['commission']) );
			} else {
				update_metadata( 'woocommerce_term', $term_id, '_wcfmmp_commission', wp_unslash($_POST['commission']) );
			}
		}
	}
 	
	function wcfmmp_disable_stripe_split_settings_admin_option() {
		if(class_exists('WCFMmp_Gateway_Stripe_Split')) : 
			$stripe_split_pay = new WCFMmp_Gateway_Stripe_Split();
			foreach (WC()->payment_gateways->payment_gateways as $key => $gateway) {
				if ($gateway->id == $stripe_split_pay->id) {
					unset(WC()->payment_gateways->payment_gateways[$key]);
				}
			}
		endif;
	}
	
	function wcfmmp_admin_script() {
  	global $WCFMmp;
  	
 	  //$screen = get_current_screen(); 
 	 
	  // Admin Bar CSS
	  wp_enqueue_style( 'wcfmmp_admin_css',  $WCFMmp->plugin_url . 'assets/css/admin/wcfmmp-style-admin.css', array(), $WCFMmp->version );
  }
}