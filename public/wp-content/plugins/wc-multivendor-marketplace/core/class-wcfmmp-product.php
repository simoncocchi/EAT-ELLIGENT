<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Product
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
 
class WCFMmp_Product {
	
	public function __construct() {
		global $WCFM;
		
		// Update Vendor Categories
		add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfmmp_update_vendor_categories' ), 10, 2 );
		
		// Update Vendor Coupon Products
		add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfmmp_update_vendor_coupon_products' ), 10, 2 );
		
		apply_filters( 'wcfm_is_allow_new_product_notification_email', array( &$this, 'wcfmmp_new_product_notification_email' ) );
		
		// Product Manage Page
		if( !wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_commission_manage', true ) ) {
			add_action( 'after_wcfm_products_manage_tabs_content', array( &$this, 'wcfmmp_product_commission' ), 500, 4 );
			add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfmmp_product_commission_save' ), 500, 2 );
			
			// Variation Commission
			add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfmmp_commission_fields_variations' ), 500, 7 );
			add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfmmp_commission_data_variations' ), 500, 3 );
			add_filter( 'wcfm_product_variation_data_factory', array( &$this, 'wcfmmp_product_variation_commission_save' ), 500, 5 );
		}
		
		// Product Specific Shipping Settings
		add_filter( 'wcfm_product_manage_fields_shipping', array( &$this, 'wcfmmp_product_manage_fields_shipping' ), 10, 2 );
		add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfmmp_shipping_product_meta_save' ), 150, 2 );
		
	}
	
	/**
	 * Update vendor category list
	 */
	function wcfmmp_update_vendor_categories( $new_product_id, $wcfm_products_manage_form_data ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$vendor_id = 0;
		if( wcfm_is_vendor( $WCFMmp->vendor_id ) ) {
			$vendor_id = $WCFMmp->vendor_id;
		} elseif( isset( $wcfm_products_manage_form_data['wcfm_associate_vendor'] ) ) {
			$vendor_id = absint( $wcfm_products_manage_form_data['wcfm_associate_vendor'] );
		}
		
		if( $vendor_id ) {
			
			$WCFMmp->wcfmmp_vendor->wcfmmp_reset_vendor_taxonomy( $vendor_id, $new_product_id );
			
			if(isset($wcfm_products_manage_form_data['product_cats']) && !empty($wcfm_products_manage_form_data['product_cats'])) {
				foreach( $wcfm_products_manage_form_data['product_cats'] as $product_cat ) {
					$WCFMmp->wcfmmp_vendor->wcfmmp_save_vendor_taxonomy( $vendor_id, $new_product_id, $product_cat );
				}
			}
			
			// Custom Taxonomies
			if(isset($wcfm_products_manage_form_data['product_custom_taxonomies']) && !empty($wcfm_products_manage_form_data['product_custom_taxonomies'])) {
				foreach($wcfm_products_manage_form_data['product_custom_taxonomies'] as $taxonomy => $taxonomy_values) {
					if( !empty( $taxonomy_values ) ) {
						foreach( $taxonomy_values as $product_tax ) {
							$WCFMmp->wcfmmp_vendor->wcfmmp_save_vendor_taxonomy( $vendor_id, $new_product_id, $product_tax, $taxonomy );
						}
					}
				}
			}
		}
	}
	
	/**
	 * Update vendor coupon prodicts
	 */
	function wcfmmp_update_vendor_coupon_products( $new_product_id, $wcfm_products_manage_form_data ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$vendor_id = 0;
		if( wcfm_is_vendor( $WCFMmp->vendor_id ) ) {
			$args = array(
					'posts_per_page'   => -1,
					'offset'           => 0,
					'category'         => '',
					'category_name'    => '',
					'orderby'          => 'date',
					'order'            => 'DESC',
					'include'          => '',
					'exclude'          => '',
					'meta_key'         => '',
					'meta_value'       => '',
					'post_type'        => 'shop_coupon',
					'post_mime_type'   => '',
					'post_parent'      => '',
					'post_status'      => array('draft', 'pending', 'publish'),
					'suppress_filters' => 0 
			);
			$args = apply_filters( 'wcfm_coupons_args', $args );
			$wcfm_vendor_coupons = get_posts( $args );
			
			if( !empty( $wcfm_vendor_coupons ) ) {
				foreach( $wcfm_vendor_coupons as $wcfm_vendor_coupon ) {
					$product_ids = get_post_meta( $wcfm_vendor_coupon->ID, 'product_ids', true );
					if( $product_ids ) $product_ids .= ',' . $new_product_id;
					else $product_ids = $new_product_id;
					update_post_meta( $wcfm_vendor_coupon->ID, 'product_ids', $product_ids );
				}
			}
		}
		
	}
	
	/**
	 * Product waiting for approval notificatiion email to Admin
	 */
	function wcfmmp_new_product_notification_email( $is_allow ) {
		return true;
	}
	
	// Commision setup
	function wcfmmp_product_commission( $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
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

		if( $product_id  ) {
			$product_commission_data = get_post_meta( $product_id, '_wcfmmp_commission', true );
			if( empty($product_commission_data) ) $product_commission_data = array();
			
			//print_r( $product_commission_data );
			
			$vendor_commission_mode        = isset( $product_commission_data['commission_mode'] ) ? $product_commission_data['commission_mode'] : 'global';
			$vendor_commission_fixed       = isset( $product_commission_data['commission_fixed'] ) ? $product_commission_data['commission_fixed'] : '';
			$vendor_commission_percent     = isset( $product_commission_data['commission_percent'] ) ? $product_commission_data['commission_percent'] : '90';
			
			$tax_enable                    = isset( $product_commission_data['tax_enable'] ) ? 'yes' : 'no';
			$tax_name                      = isset( $product_commission_data['tax_name'] ) ? $product_commission_data['tax_name'] : '';
			$tax_percent                   = isset( $product_commission_data['tax_percent'] ) ? $product_commission_data['tax_percent'] : '';
		}
		?>
		<!-- collapsible 12 - WCV Commission Support -->
		<div class="page_collapsible products_manage_commission simple variable grouped external booking <?php echo $wcfm_wpml_edit_disable_element; ?>" id="wcfm_products_manage_form_commission_head"><label class="wcfmfa fa-percent"></label><?php _e('Commission', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container simple variable external grouped booking">
			<div id="wcfm_products_manage_form_commission_expander" class="wcfm-content">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_product_commission', array(
					                                                                        "wcfm_commission_for" => array('label' => __('Commission For', 'wc-multivendor-marketplace'), 'type' => 'select', 'options' => array( 'vendor' => __( 'Vendor', 'wc-multivendor-marketplace' ), 'admin' => __( 'Admin', 'wc-multivendor-marketplace' ) ), 'attributes' => array( 'disabled' => true, 'style' => 'border: 0px !important;font-weight:600;color:#17a2b8;' ), 'class' => 'wcfm-select wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $wcfm_commission_for, 'hints' => __( 'Always applicable as per global rule.', 'wc-multivendor-marketplace' ) ),
					                                                                        "vendor_commission_mode" => array('label' => __('Commission Mode', 'wc-multivendor-marketplace'), 'name' => 'commission[commission_mode]', 'type' => 'select', 'options' => $wcfm_commission_types, 'class' => 'wcfm-select wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $vendor_commission_mode, 'hints' => __( 'Keep this as Global to apply commission rule as per vendor or marketplace commission setup.', 'wc-multivendor-marketplace' ) ),
					                                                                        "vendor_commission_percent" => array('label' => __('Commission Percent(%)', 'wc-multivendor-marketplace'), 'name' => 'commission[commission_percent]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple variable external grouped booking commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking commission_mode_field commission_mode_percent commission_mode_percent_fixed', 'value' => $vendor_commission_percent, 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        "vendor_commission_fixed" => array('label' => __('Commission Fixed', 'wc-multivendor-marketplace') . '(' . get_woocommerce_currency_symbol() . ')', 'name' => 'commission[commission_fixed]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input simple variable external grouped booking commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking commission_mode_field commission_mode_fixed commission_mode_percent_fixed', 'value' => $vendor_commission_fixed, 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																									), $product_id ) );
				
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_marketplace_settings_fields_product_commission_tax', array(  
			                                                                'tax_fields_heading' => array( 'type' => 'html', 'class' => 'commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => '<h2>' . __('Commission Tax Settings', 'wc-multivendor-marketplace') . '</h2><div class="wcfm_clearfix"></div>' ), 
																																			'tax_enable' => array( 'label' => __( 'Enable', 'wc-multivendor-marketplace' ), 'type' => 'checkbox', 'name' => 'commission[tax_enable]', 'class' => 'wcfm-checkbox wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title checkbox_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => 'yes', 'dfvalue' => $tax_enable ),
																																			'tax_name' => array( 'label' => __( 'Tax Label', 'wc-multivendor-marketplace' ), 'placeholder' => __( 'Tax', 'wc-multivendor-marketplace' ), 'type' => 'text', 'name' => 'commission[tax_name]', 'class' => 'wcfm-text wcfm_ele commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_name ),
																																			'tax_percent' => array( 'label' => __( 'Tax Percent (%)', 'wc-multivendor-marketplace' ), 'type' => 'number', 'name' => 'commission[tax_percent]', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'label_class' => 'wcfm_title commission_mode_field commission_mode_percent commission_mode_fixed commission_mode_percent_fixed commission_mode_by_sales commission_mode_by_products commission_mode_by_quantity', 'value' => $tax_percent ),
																																			), $product_id ) );
				?>
			</div>
		</div>
		<!-- end collapsible -->
		<div class="wcfm_clearfix"></div>
		<?php
	}
	
	// Commision Save
	function wcfmmp_product_commission_save( $new_product_id, $wcfm_products_manage_form_data ) {
		if( isset( $wcfm_products_manage_form_data['commission'] ) && !empty( $wcfm_products_manage_form_data['commission'] ) ) {
			update_post_meta( $new_product_id, '_wcfmmp_commission', $wcfm_products_manage_form_data['commission'] );
		}
	}
	
	// Variation commission option
	function wcfmmp_commission_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options, $products_array, $product_id, $product_type ) {
		global $WCFM, $WCFMmp;
		
		$wcfm_commission_types = get_wcfm_marketplace_commission_types();
		$wcfm_commission_types = array_merge( array( 'global' => __( 'By Global Rule', 'wc-multivendor-marketplace' ) ), $wcfm_commission_types );
		if( isset( $wcfm_commission_types['by_sales'] ) ) unset( $wcfm_commission_types['by_sales'] );
		if( isset( $wcfm_commission_types['by_products'] ) ) unset( $wcfm_commission_types['by_products'] );
		if( isset( $wcfm_commission_types['by_quantity'] ) ) unset( $wcfm_commission_types['by_quantity'] );
		
		$wcfm_variation_commission_fields = apply_filters( 'wcfm_marketplace_settings_fields_commission', array(
			                                                                            "vendor_commission_break" => array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<h2>'. __( 'Commission Rule' , 'wc-multivendor-marketplace' ) .'</h2><div class="wcfm-clearfix"></div>' ),
					                                                                        "vendor_commission_mode" => array('label' => __('Commission Mode', 'wc-multivendor-marketplace'), 'type' => 'select', 'options' => $wcfm_commission_types, 'class' => 'wcfm-select wcfm_ele variable var_commission_mode', 'label_class' => 'wcfm_title wcfm_ele variable', 'hints' => __( 'Keep this as Global to apply commission rule as per vendor or marketplace commission setup.', 'wc-multivendor-marketplace' ) ),
					                                                                        "vendor_commission_percent" => array('label' => __('Commission Percent(%)', 'wc-multivendor-marketplace'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input variable var_commission_mode_field var_commission_mode_percent var_commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele variable var_commission_mode_field var_commission_mode_percent var_commission_mode_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
					                                                                        "vendor_commission_fixed" => array('label' => __('Commission Fixed', 'wc-multivendor-marketplace') . '(' . get_woocommerce_currency_symbol() . ')', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input variable var_commission_mode_field var_commission_mode_fixed var_commission_mode_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele variable var_commission_mode_field var_commission_mode_fixed var_commission_mode_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																																									) );
		
		$variation_fileds = array_merge( $variation_fileds, $wcfm_variation_commission_fields );
		
		return $variation_fileds;
	}
	
	function wcfmmp_commission_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMmp;
		
		if( $variation_id  ) {
			$variation_commission_data = get_post_meta( $variation_id, '_wcfmmp_commission', true );
			if( empty($variation_commission_data) ) $variation_commission_data = array();
			
			$vendor_commission_mode = isset( $variation_commission_data['commission_mode'] ) ? $variation_commission_data['commission_mode'] : 'global';
			$vendor_commission_fixed = isset( $variation_commission_data['commission_fixed'] ) ? $variation_commission_data['commission_fixed'] : '';
			$vendor_commission_percent = isset( $variation_commission_data['commission_percent'] ) ? $variation_commission_data['commission_percent'] : '90';
			
			$variations[$variation_id_key]['vendor_commission_mode'] = $vendor_commission_mode;
			$variations[$variation_id_key]['vendor_commission_percent'] = $vendor_commission_percent;
			$variations[$variation_id_key]['vendor_commission_fixed'] = $vendor_commission_fixed;
		}
		
		return $variations;
	}
	
	/**
	 * Variation Commission Save
	 */
	function wcfmmp_product_variation_commission_save( $wcfm_variation_data, $new_product_id, $variation_id, $variations, $wcfm_products_manage_form_data ) {
		global $WCFM, $WCFMmp;
		
		$variation_commission_data = get_post_meta( $variation_id, '_wcfmmp_commission', true );
		if( empty($variation_commission_data) ) $variation_commission_data = array();
			
		if( isset( $variations['vendor_commission_mode'] ) ) {
			$variation_commission_data['commission_mode'] = $variations['vendor_commission_mode'];
		}
		if( isset( $variations['vendor_commission_percent'] ) ) {
			$variation_commission_data['commission_percent'] = $variations['vendor_commission_percent'];
		}
		if( isset( $variations['vendor_commission_fixed'] ) ) {
			$variation_commission_data['commission_fixed'] = $variations['vendor_commission_fixed'];
		}
		
		update_post_meta( $variation_id, '_wcfmmp_commission', $variation_commission_data );
		
		return $wcfm_variation_data;
	}
	
	/**
	 * Return commission rule for a Product
	 */
	public function wcfmmp_get_product_commission_rule( $product_id, $variation_id = 0, $vendor_id = 0, $item_price = 0, $quantity = 1, $order_id = 0 ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$product_id ) return false;
		
		$vendor_commission_mode        = 'global';
		$vendor_commission_fixed       = '';
		$vendor_commission_percent     = '';
		$vendor_commission_by_sales    = array();
		$vendor_commission_by_products = array();
		$vendor_commission_by_quantity = array();
		
		// Variation Commission
		if( $variation_id  ) {
			$product_commission_data = get_post_meta( $variation_id, '_wcfmmp_commission', true );
			if( empty($product_commission_data) ) $product_commission_data = array();
			
			$vendor_commission_mode        = isset( $product_commission_data['commission_mode'] ) ? $product_commission_data['commission_mode'] : 'global';
			$vendor_commission_fixed       = isset( $product_commission_data['commission_fixed'] ) ? $product_commission_data['commission_fixed'] : '';
			$vendor_commission_percent     = isset( $product_commission_data['commission_percent'] ) ? $product_commission_data['commission_percent'] : '';
			
			$tax_enable                    = isset( $product_commission_data['tax_enable'] ) ? 'yes' : 'no';
			$tax_name                      = isset( $product_commission_data['tax_name'] ) ? $product_commission_data['tax_name'] : '';
			$tax_percent                   = isset( $product_commission_data['tax_percent'] ) ? $product_commission_data['tax_percent'] : '';
		}
		
		// Product Commission
		if( $product_id && ( $vendor_commission_mode == 'global' )  ) {
			$product_commission_data = function_exists( 'get_term_meta' ) ? get_post_meta( $product_id, '_wcfmmp_commission', true ) : get_metadata( 'woocommerce_term', $product_id, '_wcfmmp_commission', true );
			if( empty($product_commission_data) ) $product_commission_data = array();
			
			$vendor_commission_mode        = isset( $product_commission_data['commission_mode'] ) ? $product_commission_data['commission_mode'] : 'global';
			$vendor_commission_fixed       = isset( $product_commission_data['commission_fixed'] ) ? $product_commission_data['commission_fixed'] : '';
			$vendor_commission_percent     = isset( $product_commission_data['commission_percent'] ) ? $product_commission_data['commission_percent'] : '';
			
			$tax_enable                    = isset( $product_commission_data['tax_enable'] ) ? 'yes' : 'no';
			$tax_name                      = isset( $product_commission_data['tax_name'] ) ? $product_commission_data['tax_name'] : '';
			$tax_percent                   = isset( $product_commission_data['tax_percent'] ) ? $product_commission_data['tax_percent'] : '';
		}
		
		// Category Commission
		if( $product_id && ( $vendor_commission_mode == 'global' )  ) {
			$product_terms = wp_get_post_terms( $product_id, 'product_cat', array( 'orderby' => 'term_id', 'order' => 'DESC' ) );
			
			$cat_fixed_commissions = array();
			$cat_percent_commissions = array();
			
			if( !empty( $product_terms ) ) {
				foreach( $product_terms as $term ) {
					$category_commission_data = get_term_meta( $term->term_id, '_wcfmmp_commission', true );
					if( empty($category_commission_data) ) $category_commission_data = array();
					
					if( !empty( $category_commission_data ) && isset( $category_commission_data['commission_mode'] ) && ( $category_commission_data['commission_mode'] != 'global' ) ) {
						$vendor_commission_mode        = isset( $category_commission_data['commission_mode'] ) ? $category_commission_data['commission_mode'] : 'global';
						$cat_fixed_commissions[]       = isset( $category_commission_data['commission_fixed'] ) ? $category_commission_data['commission_fixed'] : 0;
						$cat_percent_commissions[]     = isset( $category_commission_data['commission_percent'] ) ? $category_commission_data['commission_percent'] : 0;
						
						$tax_enable                    = isset( $category_commission_data['tax_enable'] ) ? 'yes' : 'no';
						$tax_name                      = isset( $category_commission_data['tax_name'] ) ? $category_commission_data['tax_name'] : '';
						$tax_percent                   = isset( $category_commission_data['tax_percent'] ) ? $category_commission_data['tax_percent'] : '';
					}
					
				}
			}
					
			if( ( $vendor_commission_mode != 'global' ) && ( !empty( $cat_percent_commissions) || !empty( $cat_fixed_commissions ) ) ) {
				if( apply_filters( 'wcfmmp_is_allow_max_category_commission', true ) ) {
					$vendor_commission_fixed       = max( $cat_fixed_commissions );
					$vendor_commission_percent     = max( $cat_percent_commissions );
				} else {
					$vendor_commission_fixed       = min( $cat_fixed_commissions );
					$vendor_commission_percent     = min( $cat_percent_commissions );
				}
			}
		}
		
		if( !$vendor_id ) {
			$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
		}
		
		// Vendor Commission
		$vendor_data = array();
		if( $vendor_id && ( $vendor_commission_mode == 'global' ) ) {
			$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
			
			$vendor_commission_mode        = isset( $vendor_data['commission']['commission_mode'] ) ? $vendor_data['commission']['commission_mode'] : 'global';
			if( ($vendor_commission_mode == 'percentage') ) $vendor_commission_mode = 'global';
			$vendor_commission_fixed       = isset( $vendor_data['commission']['commission_fixed'] ) ? $vendor_data['commission']['commission_fixed'] : '';
			$vendor_commission_percent     = isset( $vendor_data['commission']['commission_percent'] ) ? $vendor_data['commission']['commission_percent'] : '';
			$vendor_commission_by_sales    = isset( $vendor_data['commission']['commission_by_sales'] ) ? $vendor_data['commission']['commission_by_sales'] : array();
			$vendor_commission_by_products = isset( $vendor_data['commission']['commission_by_products'] ) ? $vendor_data['commission']['commission_by_products'] : array();
			$vendor_commission_by_quantity = isset( $vendor_data['commission']['commission_by_quantity'] ) ? $vendor_data['commission']['commission_by_quantity'] : array();
			
			$tax_enable                    = isset( $vendor_data['commission']['tax_enable'] ) ? 'yes' : 'no';
			$tax_name                      = isset( $vendor_data['commission']['tax_name'] ) ? $vendor_data['commission']['tax_name'] : '';
			$tax_percent                   = isset( $vendor_data['commission']['tax_percent'] ) ? $vendor_data['commission']['tax_percent'] : '';
		}
		
		// Membership Commission
		if( $vendor_id && ( $vendor_commission_mode == 'global' ) && function_exists( 'wcfm_is_valid_membership' ) ) {
			$wcfm_membership_id = get_user_meta( $vendor_id, 'wcfm_membership', true );
			
			if( $wcfm_membership_id && wcfm_is_valid_membership( $wcfm_membership_id ) ) {
				$wcfm_memberhip_commission_options = get_post_meta( $wcfm_membership_id, 'commission', true );
				
				$vendor_commission_mode        = isset( $wcfm_memberhip_commission_options['commission_mode'] ) ? $wcfm_memberhip_commission_options['commission_mode'] : 'global';
				$vendor_commission_fixed       = isset( $wcfm_memberhip_commission_options['commission_fixed'] ) ? $wcfm_memberhip_commission_options['commission_fixed'] : '';
				$vendor_commission_percent     = isset( $wcfm_memberhip_commission_options['commission_percent'] ) ? $wcfm_memberhip_commission_options['commission_percent'] : '';
				$vendor_commission_by_sales    = isset( $wcfm_memberhip_commission_options['commission_by_sales'] ) ? $wcfm_memberhip_commission_options['commission_by_sales'] : array();
				$vendor_commission_by_products = isset( $wcfm_memberhip_commission_options['commission_by_products'] ) ? $wcfm_memberhip_commission_options['commission_by_products'] : array();
				$vendor_commission_by_quantity = isset( $wcfm_memberhip_commission_options['commission_by_quantity'] ) ? $wcfm_memberhip_commission_options['commission_by_quantity'] : array();
				
				$tax_enable                    = isset( $wcfm_memberhip_commission_options['tax_enable'] ) ? 'yes' : 'no';
				$tax_name                      = isset( $wcfm_memberhip_commission_options['tax_name'] ) ? $wcfm_memberhip_commission_options['tax_name'] : '';
				$tax_percent                   = isset( $wcfm_memberhip_commission_options['tax_percent'] ) ? $wcfm_memberhip_commission_options['tax_percent'] : '';
			}
		}
		
		// Global Commission
		if( $vendor_commission_mode == 'global' ) {
			$wcfm_commission_options = $WCFMmp->wcfmmp_commission_options;
			
			$vendor_commission_mode        = isset( $wcfm_commission_options['commission_mode'] ) ? $wcfm_commission_options['commission_mode'] : 'percent';
			$vendor_commission_fixed       = isset( $wcfm_commission_options['commission_fixed'] ) ? $wcfm_commission_options['commission_fixed'] : '';
			$vendor_commission_percent     = isset( $wcfm_commission_options['commission_percent'] ) ? $wcfm_commission_options['commission_percent'] : '';
			$vendor_commission_by_sales    = isset( $wcfm_commission_options['commission_by_sales'] ) ? $wcfm_commission_options['commission_by_sales'] : array();
			$vendor_commission_by_products = isset( $wcfm_commission_options['commission_by_products'] ) ? $wcfm_commission_options['commission_by_products'] : array();
			$vendor_commission_by_quantity = isset( $wcfm_commission_options['commission_by_quantity'] ) ? $wcfm_commission_options['commission_by_quantity'] : array();
			
			$tax_enable                    = isset( $wcfm_commission_options['tax_enable'] ) ? $wcfm_commission_options['tax_enable'] : 'no';
			$tax_name                      = isset( $wcfm_commission_options['tax_name'] ) ? $wcfm_commission_options['tax_name'] : '';
			$tax_percent                   = isset( $wcfm_commission_options['tax_percent'] ) ? $wcfm_commission_options['tax_percent'] : '';
		}
		
		$product_commission_rule = array( 'rule' => $vendor_commission_mode, 'mode' => $vendor_commission_mode, 'percent' => 0, 'fixed' => 0, 'tax_enable' => $tax_enable, 'tax_name' => $tax_name, 'tax_percent' => $tax_percent );
		
		// Vendor's own product commission
		if( $vendor_id && !apply_filters( 'wcfm_is_allow_vendor_own_product_commission', true ) ) {
			$pvendor_id = wcfm_get_vendor_id_by_post( $product_id );
			if( $pvendor_id == $vendor_id ) {
				return apply_filters( 'wcfmmp_product_commission_rule', $product_commission_rule, $product_id, $vendor_id, $item_price, $quantity, $order_id, $vendor_commission_mode );
			}
		}
		
		switch( $vendor_commission_mode ) {
			case 'percent':
				$product_commission_rule['percent'] = $vendor_commission_percent;
			break;
			
			case 'fixed':
				$product_commission_rule['fixed'] = $vendor_commission_fixed;
			break;
			
			case 'percent_fixed':
				$product_commission_rule['percent'] = $vendor_commission_percent;
				$product_commission_rule['fixed'] = $vendor_commission_fixed;
			break;
			
			case 'by_sales':
				$product_commission_rule = $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_rule_by_sales_rule( $vendor_id, $vendor_commission_by_sales, $product_commission_rule );
			break;
			
			case 'by_products':
				if( !$item_price ) {
					$product = wc_get_product( $product_id );
					$item_price = (float)$product->get_price() * (int)$quantity;
				}
				$product_commission_rule = $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_rule_by_product_rule( $product_id, $item_price, $quantity, $vendor_commission_by_products, $product_commission_rule );
			break;
			
			case 'by_quantity':
				$product_commission_rule = $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_rule_by_quantity_rule( $product_id, $item_price, $quantity, $vendor_commission_by_quantity, $product_commission_rule );
			break;
			
		}
		
		// Transaction Charge Adding to the Commission Rule
		$product_commission_rule['transaction_charge_type'] = 'no';
		$product_commission_rule['transaction_charge_percent'] = '0';
		$product_commission_rule['transaction_charge_fixed'] = '0';
		$product_commission_rule['transaction_charge_tax'] = '0';
		
		if( $order_id ) {
			$order = wc_get_order( $order_id );
			if( is_a( $order , 'WC_Order' ) ) {
				$payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
				
				if( $payment_method ) {
					$transaction_charge_type = isset( $WCFMmp->wcfmmp_withdrawal_options['transaction_charge_type'] ) ? $WCFMmp->wcfmmp_withdrawal_options['transaction_charge_type'] : 'no';
					$transaction_charge      = isset( $WCFMmp->wcfmmp_withdrawal_options['transaction_charge'] ) ? $WCFMmp->wcfmmp_withdrawal_options['transaction_charge'] : array();
					
					$transaction_charge_gateway  = isset( $transaction_charge[$payment_method] ) ? $transaction_charge[$payment_method][0] : array();
					$transaction_percent_charge  = isset( $transaction_charge_gateway['percent'] ) ? $transaction_charge_gateway['percent'] : 0;
					$transaction_fixed_charge    = isset( $transaction_charge_gateway['fixed'] ) ? $transaction_charge_gateway['fixed'] : 0;
					$transaction_charge_tax      = isset( $transaction_charge_gateway['tax'] ) ? $transaction_charge_gateway['tax'] : 0;
				
					if( $vendor_id && !empty( $vendor_data ) ) {
						$vendor_transaction_mode         = isset( $vendor_data['withdrawal']['transaction_mode'] ) ? $vendor_data['withdrawal']['transaction_mode'] : 'global';
						if( $vendor_transaction_mode != 'global' ) {
							$transaction_charge_type         = isset( $vendor_data['withdrawal']['transaction_charge_type'] ) ? $vendor_data['withdrawal']['transaction_charge_type'] : $transaction_charge_type;
							$vendor_transaction_charge       = isset( $vendor_data['withdrawal']['transaction_charge'] ) ? $vendor_data['withdrawal']['transaction_charge'] : $transaction_charge;
							
							$vendor_transaction_charge_gateway  = isset( $vendor_transaction_charge[$payment_method] ) ? $vendor_transaction_charge[$payment_method][0] : $transaction_charge_gateway;
							$transaction_percent_charge         = isset( $vendor_transaction_charge_gateway['percent'] ) ? $vendor_transaction_charge_gateway['percent'] : 0;
							$transaction_fixed_charge           = isset( $vendor_transaction_charge_gateway['fixed'] ) ? $vendor_transaction_charge_gateway['fixed'] : 0;
							$transaction_charge_tax             = isset( $vendor_transaction_charge_gateway['tax'] ) ? $vendor_transaction_charge_gateway['tax'] : 0;
						}
					}
					
					if( $transaction_charge_type != 'no' ) {
						$product_commission_rule['transaction_charge_type'] = $transaction_charge_type;
						$product_commission_rule['transaction_charge_percent'] = $transaction_percent_charge;
						$product_commission_rule['transaction_charge_fixed'] = $transaction_fixed_charge;
						$product_commission_rule['transaction_charge_tax'] = $transaction_charge_tax;
					}
				}
			}
		}
		
		return apply_filters( 'wcfmmp_product_commission_rule', $product_commission_rule, $product_id, $vendor_id, $item_price, $quantity, $order_id, $vendor_commission_mode );
	}
	
	function wcfmmp_product_manage_fields_shipping( $shipping_fields, $product_id ) {
  	global $wp, $WCFM, $WCFMmp, $wpdb;
  	
  	if( apply_filters( 'wcfm_is_allow_shipping', true ) ) {
  		$processing_time = wcfmmp_get_shipping_processing_times();
  		$disable_shipping = 'no';
  		$overwrite_shipping = 'no';
			$additional_price = '';
			$additional_qty = '';
			$wcfmmp_processing_time = '';
			$vendor_id  = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			if( $product_id ) {
				//				$disable_shipping = get_post_meta( $product_id, '_disable_shipping', true ) ? get_post_meta( $product_id, '_disable_shipping', true ) : 'no';
				$overwrite_shipping = get_post_meta( $product_id, '_overwrite_shipping', true ) ? get_post_meta( $product_id, '_overwrite_shipping', true ) : 'no';
				$additional_price = get_post_meta( $product_id, '_additional_price', true ) ? get_post_meta( $product_id, '_additional_price', true ) : '';
				$additional_qty = get_post_meta( $product_id, '_additional_qty', true ) ? get_post_meta( $product_id, '_additional_qty', true ) : '';
				$wcfmmp_processing_time = get_post_meta( $product_id, '_wcfmmp_processing_time', true ) ? get_post_meta( $product_id, '_wcfmmp_processing_time', true ) : '';
        $vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			}
			
			// Processing Time
			$wcv_shipping_processing_fileds = apply_filters( 'wcfmmp_product_manager_shipping_processing_fileds', array( 
          																								"_wcfmmp_processing_time" => array('label' => __('Processing Time', 'wc-multivendor-marketplace'), 'type' => 'select', 'class' => 'wcfm-select', 'label_class' => 'wcfm_title', 'options' => $processing_time, 'value' => $wcfmmp_processing_time, 'hints' => __('The time required before sending the product for delivery', 'wc-multivendor-marketplace') ),
          																								) );
       $shipping_fields = array_merge( $shipping_fields, $wcv_shipping_processing_fileds );
			
			//			$wcv_shipping_fileds = array( 
			//					"_disable_shipping" => array('label' => __('Disable Shipping', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $disable_shipping, 'hints' => __('Disable shipping for this product', 'wc-multivendor-marketplace') )
			//				);
			//			$shipping_fields = array_merge( $wcv_shipping_fileds, $shipping_fields );
    
    $vendor_shipping_details = get_user_meta( $vendor_id, '_wcfmmp_shipping', true );
    if( !empty($vendor_shipping_details) ) {
      $enabled = isset( $vendor_shipping_details['_wcfmmp_user_shipping_enable'] ) ? $vendor_shipping_details['_wcfmmp_user_shipping_enable'] : '';
      $type = !empty( $vendor_shipping_details['_wcfmmp_user_shipping_type'] ) ? $vendor_shipping_details['_wcfmmp_user_shipping_type'] : '';
      if ( ( !empty($enabled) && $enabled == 'yes' ) && ( !empty($type) ) && 'by_country' === $type ) {
        $wcv_shipping_fileds = apply_filters( 'wcfmmp_product_manager_shipping_fileds', array( 
          "_overwrite_shipping" => array('label' => __('Override Shipping', 'wc-multivendor-marketplace') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes', 'dfvalue' => $overwrite_shipping, 'hints' => __('Override your store\'s default shipping cost for this product', 'wc-multivendor-marketplace') ),
          "_additional_price" => array('label' => __('Additional Price', 'wc-multivendor-marketplace'), 'placeholder' => '0.00', 'type' => 'text', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'value' => $additional_price, 'hints' => __('First product of this type will be charged with this price', 'wc-multivendor-marketplace') ),
          "_additional_qty" => array('label' => __('Per Qty Additional Price', 'wc-multivendor-marketplace'), 'placeholder' => '0.00', 'type' => 'text', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'value' => $additional_qty, 'hints' => __('Every second product of same type will be charged with this price', 'wc-multivendor-marketplace') ),
                                    ) );
        $shipping_fields = array_merge( $shipping_fields, $wcv_shipping_fileds );
      }
      
      if ( ( !empty($enabled) && $enabled == 'yes' ) && ( !empty($type) && ( ( 'by_zone' !== $type ) || !apply_filters( 'wcfmmp_is_allow_store_shipping_by_shipping_classes', true ) ) ) ) {
				$shipping_fields = wcfm_hide_field( 'shipping_class', $shipping_fields );
				//$shipping_fields['shipping_class']['hints'] = __( 'Shipping classes are used by certain shipping methods to group similar products.', 'wc-multivendor-marketplace' );
      }
    }
			
			
		}
  	
  	return $shipping_fields;
  }
  
  function wcfmmp_shipping_product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $WCFMmp, $_POST, $wpdb;
		
		if( apply_filters( 'wcfm_is_allow_shipping', true ) ) {
//			if( isset( $wcfm_products_manage_form_data['_disable_shipping'] ) ) {
//				update_post_meta( $new_product_id, '_disable_shipping', $wcfm_products_manage_form_data['_disable_shipping'] );
//			} else {
//				delete_post_meta( $new_product_id, '_disable_shipping' );
//			}
			if( isset( $wcfm_products_manage_form_data['_overwrite_shipping'] ) ) {
				update_post_meta( $new_product_id, '_overwrite_shipping', $wcfm_products_manage_form_data['_overwrite_shipping'] );
			} else {
				delete_post_meta( $new_product_id, '_overwrite_shipping' );
			}
			if( isset( $wcfm_products_manage_form_data['_additional_price'] ) ) {
				update_post_meta( $new_product_id, '_additional_price', $wcfm_products_manage_form_data['_additional_price'] );
			}
			if( isset( $wcfm_products_manage_form_data['_additional_qty'] ) ) {
				update_post_meta( $new_product_id, '_additional_qty', $wcfm_products_manage_form_data['_additional_qty'] );
			}
			if( isset( $wcfm_products_manage_form_data['_wcfmmp_processing_time'] ) ) {
				update_post_meta( $new_product_id, '_wcfmmp_processing_time', $wcfm_products_manage_form_data['_wcfmmp_processing_time'] );
			}
		}
  }
	
}