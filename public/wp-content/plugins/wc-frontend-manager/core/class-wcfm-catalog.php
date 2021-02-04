<?php
/**
 * WCFM plugin core
 *
 * Catalog board core
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   3.4.0
 */
 
class WCFM_Catalog {

	public function __construct() {
		global $WCFM;
		
		if( apply_filters( 'wcfm_is_allow_catalog', true ) ) {
			add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcfm_product_manage_fields_catalog_mode' ), 100, 5 );
			add_action( 'after_wcfm_products_manage_general', array( &$this, 'wcfm_products_manage_catalog_options' ), 10, 2 );
			add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_products_manage_catalog_options_save' ), 10, 2 );
		}
		
		add_action( 'woocommerce_after_shop_loop_item_title',			array( &$this, 'wcfm_catalog_mode_loop_pricing' ), 9 );
		add_action( 'woocommerce_after_shop_loop_item',			array( &$this, 'wcfm_catalog_mode_loop_add_to_cart' ), 9 );
		add_action( 'woocommerce_single_product_summary',			array( &$this, 'wcfm_catalog_mode_pricing' ), 9 );
		add_action( 'woocommerce_single_product_summary',			array( &$this, 'wcfm_catalog_mode_add_to_cart' ), 29 );
		
		// Catalog Mode Checking
		add_filter( 'woocommerce_is_purchasable', array( &$this, 'wcfm_catalog_mode_is_purchasable' ), 100, 2 );
		
		// Loop Add to Cart URL check 
		add_filter( 'woocommerce_product_add_to_cart_url', array( &$this, 'wcfm_catalog_mode_check_loop_add_to_cart_url' ), 100, 2 );
		
		// YiTH Quick View Catalog Support
		add_action( 'yith_wcqv_product_summary',			array( &$this, 'wcfm_catalog_mode_pricing' ), 14 );
		add_action( 'yith_wcqv_product_summary',			array( &$this, 'wcfm_catalog_mode_add_to_cart' ), 24 );
		
		// Flatsome Quick View Catalog Support
		add_action( 'woocommerce_single_product_lightbox_summary',			array( &$this, 'wcfm_catalog_mode_pricing' ), 9 );
		add_action( 'woocommerce_single_product_lightbox_summary',			array( &$this, 'wcfm_catalog_mode_add_to_cart' ), 29 );
	}
	
	/**
	 * WCFMu Simple product catalog option
	 */
	function wcfm_product_manage_fields_catalog_mode( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM, $WCFMu;
		
		// Catalog options
		$is_catalog = ( get_post_meta( $product_id, '_catalog', true) == 'yes' ) ? 'yes' : '';
		
		$general_fields = array_slice($general_fields, 0, 1, true) +
																	array(
																				"is_catalog" => array( 'desc' => __( 'Catalog', 'wc-frontend-manager' ) , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple variable booking non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'desc_class' => 'wcfm_title wcfm_ele virtual_ele_title checkbox_title simple variable booking non-pw-gift-card' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => 'yes', 'dfvalue' => $is_catalog),
																				) +
																	array_slice($general_fields, 1, count($general_fields) - 1, true) ;
		return $general_fields;
	}
	
	/**
	 * Display WCFM Product Catalog options
	 */
	function wcfm_products_manage_catalog_options( $product_id, $product_type ) {
		global $WCFM;
		
		$disable_add_to_cart = 'no';
		$disable_price = 'no';
		
		if( $product_id ) {
			$disable_add_to_cart = ( get_post_meta( $product_id, 'disable_add_to_cart', true) ) ? get_post_meta( $product_id, 'disable_add_to_cart', true) : 'no';
			$disable_price = ( get_post_meta( $product_id, 'disable_price', true) ) ? get_post_meta( $product_id, 'disable_price', true) : 'no';
		}
		?>
		
		<!-- collapsible 2.1 -->
		<div class="page_collapsible products_manage_catalog simple variable booking catalog_options" id="wcfm_products_manage_form_catalog_head"><label class="wcfmfa fa-bars"></label><?php _e('Catalog Mode', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container simple variable booking catalog_options">
			<div id="wcfm_products_manage_form_catalog_expander" class="wcfm-content">
				<?php
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_fields_catalog_options', array(
																																																"disable_add_to_cart" => array('label' => __('Disable Add to Cart?', 'wc-frontend-manager') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele simple variable booking', 'value' => 'yes', 'label_class' => 'wcfm_title wcfm_ele checkbox_title simple variable booking', 'dfvalue' => $disable_add_to_cart),
																																																"disable_price" => array('label' => __('Hide Price?', 'wc-frontend-manager') , 'type' => 'checkbox', 'value' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele simple variable booking', 'label_class' => 'wcfm_title wcfm_ele checkbox_title simple variable booking', 'dfvalue' => $disable_price)
																																												), $product_id, $product_type ) );
				?>
			</div>
		</div>
		<!-- end collapsible -->
		<div class="wcfm_clearfix"></div>
		<?php
	}
	
	/**
	 * Save WCFM Product Catalog options
	 */
	function wcfm_products_manage_catalog_options_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $wpdb, $WCFM, $_POST;
		
		$is_catalog = ( isset( $wcfm_products_manage_form_data['is_catalog'] ) ) ? 'yes' : 'no';
	
		update_post_meta( $new_product_id, '_catalog', $is_catalog );
		
		$disable_add_to_cart = ( isset( $wcfm_products_manage_form_data['disable_add_to_cart'] ) ) ? 'yes' : 'no';
	
		update_post_meta( $new_product_id, 'disable_add_to_cart', $disable_add_to_cart );
			
		$disable_price = ( isset( $wcfm_products_manage_form_data['disable_price'] ) ) ? 'yes' : 'no';
	
		update_post_meta( $new_product_id, 'disable_price', $disable_price );
		
		return;
	}
	
	/**
	 * Hide Loop Product Price
	 */
	function wcfm_catalog_mode_loop_pricing() {
		global $product, $WCFM;
		
		$product_id = 0;
		if ( is_object( $product ) && method_exists( $product, 'get_id' ) ) { 
			$product_id   		= $product->get_id(); 
		}
		
		if( $product_id ) {
			$is_catalog = ( get_post_meta( $product_id, '_catalog', true) == 'yes' ) ? 'yes' : '';
			if( $is_catalog == 'yes' ) { 
				$disable_price = ( get_post_meta( $product_id, 'disable_price', true) ) ? get_post_meta( $product_id, 'disable_price', true) : 'no';
				if( ( $disable_price == 'yes' ) && has_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price') ) {
					remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
					$WCFM->wcfm_has_catalog = true;
				}
			} else {
				if( !has_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price') && $WCFM->wcfm_has_catalog ) {
					if ( apply_filters( 'wcfm_is_allow_add_to_cart_restore', true ) && !function_exists( 'rigid_related_products_args' ) && !function_exists( 'astra_header' ) && !function_exists( 'zita_post_loader' ) && !function_exists( 'oceanwp_get_sidebar' ) && !function_exists( 'x_get_stack' ) ) {
						add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
					}
				}
			}
		}
	}
	
	/**
	 * Hide Loop Product Add to Cart
	 */
	function wcfm_catalog_mode_loop_add_to_cart() {
		global $product, $WCFM;
		
		$product_id = 0;
		if ( is_object( $product ) && method_exists( $product, 'get_id' ) ) { 
			$product_id   		= $product->get_id(); 
		}
		
		if( $product_id ) {
			$is_catalog = ( get_post_meta( $product_id, '_catalog', true) == 'yes' ) ? 'yes' : '';
			if( $is_catalog == 'yes' ) {
				$disable_add_to_cart = ( get_post_meta( $product_id, 'disable_add_to_cart', true) ) ? get_post_meta( $product_id, 'disable_add_to_cart', true) : 'no';
				if( ( $disable_add_to_cart == 'yes' ) && has_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart') ) {
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
					$WCFM->wcfm_has_catalog = true;
				}
			} else {
				if( !has_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart') && $WCFM->wcfm_has_catalog ) {
					if ( apply_filters( 'wcfm_is_allow_add_to_cart_restore', true ) && !function_exists( 'astra_header' ) && !function_exists( 'zita_post_loader' ) && !function_exists( 'oceanwp_get_sidebar' ) && !function_exists( 'martfury_content_columns' ) && !function_exists( 'x_get_stack' ) ) {
						add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
					}
				}
			}
		}
	}
	
	/**
	 * Hide Single Product Price
	 */
	function wcfm_catalog_mode_pricing() {
		global $product, $WCFM;
		
		if ( is_object( $product ) && method_exists( $product, 'get_id' ) ) { 
			$product_id   		= $product->get_id(); 
		} else if ( is_product() ) {
			$product_id   		= $post->ID;
		}
		
		if( $product_id ) {
			$is_catalog = ( get_post_meta( $product_id, '_catalog', true) == 'yes' ) ? 'yes' : '';
			if( $is_catalog == 'yes' ) {
				$disable_price = ( get_post_meta( $product_id, 'disable_price', true) ) ? get_post_meta( $product_id, 'disable_price', true) : 'no';
				if( $disable_price == 'yes' ) {
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
					
					// YiTH Quick View Catalog Support
					remove_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_price', 15 );
					
					// Flatsome Quick View Catalog Support
					remove_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_price', 10 );
				}
			}
		}
	}
	
	/**
	 * Hide Single Product Add to Cart
	 */
	function wcfm_catalog_mode_add_to_cart() {
		global $product, $WCFM, $post;
		
		if ( is_object( $product ) && method_exists( $product, 'get_id' ) ) { 
			$product_id   		= $product->get_id(); 
		} else if ( is_product() ) {
			$product_id   		= $post->ID;
		}
		
		if( $product_id ) {
			$is_catalog = ( get_post_meta( $product_id, '_catalog', true) == 'yes' ) ? 'yes' : '';
			if( $is_catalog == 'yes' ) {
				$disable_add_to_cart = ( get_post_meta( $product_id, 'disable_add_to_cart', true) ) ? get_post_meta( $product_id, 'disable_add_to_cart', true) : 'no';
				if( $disable_add_to_cart == 'yes' ) {
					add_filter( 'woocommerce_is_purchasable', '__return_false' );
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
					
					// YiTH Quick View Catalog Support
					remove_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_add_to_cart', 25 );
					
					// Flatsome Quick View Catalog Support
					remove_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_add_to_cart', 30 );
				}
			}
		}
	}
	
	function wcfm_catalog_mode_is_purchasable( $is_purchasable, $product ) {
		global $WCFM, $WCFMmp;
		
		if( method_exists( $product, 'get_id' ) ) {
			$product_id = $product->get_id();
			if( $product_id ) {
				$is_catalog = ( get_post_meta( $product_id, '_catalog', true) == 'yes' ) ? 'yes' : '';
				if( $is_catalog == 'yes' ) {
					$disable_add_to_cart = ( get_post_meta( $product_id, 'disable_add_to_cart', true) ) ? get_post_meta( $product_id, 'disable_add_to_cart', true) : 'no';
					if( $disable_add_to_cart == 'yes' ) {
						$is_purchasable = false;
					}
				}
			}
		}
		
		return $is_purchasable;
	}
	
	function wcfm_catalog_mode_check_loop_add_to_cart_url( $add_to_cart_url, $product ) {
		
		if ( is_object( $product ) && method_exists( $product, 'get_id' ) ) { 
			$product_id   		= $product->get_id(); 
			if( $product_id ) {
				$is_catalog = ( get_post_meta( $product_id, '_catalog', true) == 'yes' ) ? 'yes' : '';
				if( $is_catalog == 'yes' ) {
					$disable_add_to_cart = ( get_post_meta( $product_id, 'disable_add_to_cart', true) ) ? get_post_meta( $product_id, 'disable_add_to_cart', true) : 'no';
					if( $disable_add_to_cart == 'yes' ) {
						$add_to_cart_url = get_permalink( $product_id );
					}
				}
			}
		}
		
		return $add_to_cart_url;
	}
}