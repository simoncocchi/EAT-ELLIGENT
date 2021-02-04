<?php
/**
 * WCFM plugin core
 *
 * WCfM Policy Module
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   4.1.10
 */
 
class WCFM_Policy {

	public function __construct() {
		global $WCFM;
		
		add_action( 'end_wcfm_settings', array( &$this, 'wcfm_policy_settings' ), 16 );
		add_action( 'wcfm_settings_update', array( &$this, 'wcfm_policy_settings_update' ), 16 );
		
		if( $is_marketplace = wcfm_is_marketplace() ) {
			
			if( $is_marketplace = 'wcfmmarketplace' ) {
			  add_action( 'wcfm_vendor_settings_after_seo', array( &$this, 'wcfm_policy_vendor_settings' ), 15 );
			} else {
				add_action( 'end_wcfm_vendor_settings', array( &$this, 'wcfm_policy_vendor_settings' ), 15 );
			}
			add_action( 'wcfm_vendor_settings_update', array( &$this, 'wcfm_policy_vendor_settings_update' ), 15, 2 );
			
			if( $is_marketplace = 'wcmarketplace' ) {
				if( apply_filters( 'wcfm_is_allow_disable_wcmp_policies', true ) ) {
					add_filter('wcmp_general_tab_filds', array( &$this, 'wcmp_general_policies_field_disable' ), 15 );
					add_action( 'wcmp_get_subtabs', array( &$this, 'wcmp_general_policies_tab_disable' ), 15, 2 );
				}
			}
		}
		
		add_action( 'after_wcfm_products_manage_tabs_content', array( &$this, 'wcfm_policy_product_settings' ), 100, 4 );
		add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcfm_policy_product_settings_update' ), 480, 2 );
	}
	
	function wcfm_policy_settings( $wcfm_options ) {
		global $WCFM, $WCFMu;
		
		if( !apply_filters( 'wcfm_is_allow_policy_settings', true ) ) return; 
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_policy_tab_title = isset( $wcfm_policy_options['policy_tab_title'] ) ? $wcfm_policy_options['policy_tab_title'] : '';
		$_wcfm_shipping_policy = isset( $wcfm_policy_options['shipping_policy'] ) ? $wcfm_policy_options['shipping_policy'] : '';
		$_wcfm_refund_policy = isset( $wcfm_policy_options['refund_policy'] ) ? $wcfm_policy_options['refund_policy'] : '';
		$_wcfm_cancellation_policy = isset( $wcfm_policy_options['cancellation_policy'] ) ? $wcfm_policy_options['cancellation_policy'] : '';
		
		$is_marketplace = wcfm_is_marketplace();
		if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
			$wcmp_policy_settings = get_option("wcmp_general_policies_settings_name");
			$_wcmp_shipping_policy = isset( $wcmp_policy_settings['shipping_policy'] ) ? $wcmp_policy_settings['shipping_policy'] : '';
			if( wcfm_empty($_wcfm_shipping_policy) ) $_wcfm_shipping_policy = $_wcmp_shipping_policy;
			$_wcmp_refund_policy = isset( $wcmp_policy_settings['refund_policy'] ) ? $wcmp_policy_settings['refund_policy'] : '';
			if( wcfm_empty($_wcfm_refund_policy ) ) $_wcfm_refund_policy = $_wcmp_refund_policy;
			$_wcmp_cancellation_policy = isset( $wcmp_policy_settings['cancellation_policy'] ) ? $wcmp_policy_settings['cancellation_policy'] : '';
			if( wcfm_empty($_wcfm_cancellation_policy ) ) $_wcfm_cancellation_policy = $_wcmp_cancellation_policy;
		}
		
		?>
		<!-- collapsible -->
		<div class="page_collapsible" id="wcfm_settings_form_policies_head">
			<label class="wcfmfa fa-ambulance"></label>
			<?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ) . ' ' . __('Policies', 'wc-frontend-manager'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_policies_expander" class="wcfm-content">
			  <h2><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ) . ' ' . __('Store Policies Setting', 'wc-frontend-manager'); ?></h2>
				<?php wcfm_video_tutorial( 'https://wclovers.com/knowledgebase/wcfm-store-policies/' ); ?>
				<div class="wcfm_clearfix"></div>
				<?php
				$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
				$wpeditor = apply_filters( 'wcfm_is_allow_product_wpeditor', 'wpeditor' );
				if( $wpeditor && $rich_editor ) {
					$rich_editor = 'wcfm_wpeditor';
				} else {
					$wpeditor = 'textarea';
				}
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_policies', array(
					                                                                        "wcfm_policy_tab_title" => array('label' => __('Policy Tab Label', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $_wcfm_policy_tab_title ),
																																									"wcfm_shipping_policy" => array('label' => __('Shipping Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title', 'value' => $_wcfm_shipping_policy ),
																																									"wcfm_refund_policy" => array('label' => __('Refund Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title', 'value' => $_wcfm_refund_policy ),
																																									"wcfm_cancellation_policy" => array('label' => __('Cancellation/Return/Exchange Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_title', 'value' => $_wcfm_cancellation_policy ),
																																									) ) );
			  ?>
			</div>
		</div>
		<div class="wcfm_clearfix"></div>
		<!-- end collapsible -->
		
		<?php
		
	}
	
	function wcfm_policy_settings_update( $wcfm_settings_form ) {
		global $WCFM, $WCFMu, $_POST;
		
		if( !apply_filters( 'wcfm_is_allow_policy_settings', true ) ) return; 
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		if( isset( $wcfm_settings_form['wcfm_policy_tab_title'] ) ) {
			$wcfm_policy_options['policy_tab_title'] = $wcfm_settings_form['wcfm_policy_tab_title'];
		}
		
		if( isset( $_POST['shipping_policy'] ) ) {
			$wcfm_policy_options['shipping_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['shipping_policy'], ENT_QUOTES, 'UTF-8' ) ) );
		}
		
		if( isset( $_POST['refund_policy'] ) ) {
			$wcfm_policy_options['refund_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['refund_policy'], ENT_QUOTES, 'UTF-8' ) ) );
		}
		
		if( isset( $_POST['cancellation_policy'] ) ) {
			$wcfm_policy_options['cancellation_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['cancellation_policy'], ENT_QUOTES, 'UTF-8' ) ) );
		}
		
		wcfm_update_option( 'wcfm_policy_options', $wcfm_policy_options );
	}
	
	function wcfm_policy_vendor_settings( $vendor_id ) {
		global $WCFM, $WCFMu;
		
		if( !apply_filters( 'wcmp_vendor_can_overwrite_policies', true ) || !apply_filters( 'wcfm_is_allow_policy_settings', true ) ) return; 
		
		$is_marketplace = wcfm_is_marketplace();
		
		$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $vendor_id, 'wcfm_policy_vendor_options', true );
		
		$_wcfm_vendor_policy_tab_title = isset( $wcfm_policy_vendor_options['policy_tab_title'] ) ? $wcfm_policy_vendor_options['policy_tab_title'] : '';
		$_wcfm_vendor_shipping_policy = isset( $wcfm_policy_vendor_options['shipping_policy'] ) ? $wcfm_policy_vendor_options['shipping_policy'] : '';
		$_wcfm_vendor_refund_policy = isset( $wcfm_policy_vendor_options['refund_policy'] ) ? $wcfm_policy_vendor_options['refund_policy'] : '';
		$_wcfm_vendor_cancellation_policy = isset( $wcfm_policy_vendor_options['cancellation_policy'] ) ? $wcfm_policy_vendor_options['cancellation_policy'] : '';
		
		if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
			$vendor_policy_tab_title = get_user_meta( $vendor_id, '_vendor_policy_tab_title', true ); 
			if( wcfm_empty($_wcfm_vendor_policy_tab_title) ) $_wcfm_vendor_policy_tab_title = $vendor_policy_tab_title;
			$vendor_shipping_policy = get_user_meta( $vendor_id, '_vendor_shipping_policy', true ); 
			if( wcfm_empty($_wcfm_vendor_shipping_policy) ) $_wcfm_vendor_shipping_policy = $vendor_shipping_policy;
			$vendor_refund_policy = get_user_meta( $vendor_id, '_vendor_refund_policy', true );
			if( wcfm_empty($_wcfm_vendor_refund_policy) ) $_wcfm_vendor_refund_policy = $vendor_refund_policy;
			$vendor_cancellation_policy = get_user_meta( $vendor_id, '_vendor_cancellation_policy', true );
			if( wcfm_empty($_wcfm_vendor_cancellation_policy) ) $_wcfm_vendor_cancellation_policy = $vendor_cancellation_policy;
		} elseif( $is_marketplace && ($is_marketplace == 'dokan') ) {
			$vendor_shipping_policy = get_user_meta( $vendor_id, '_dps_ship_policy', true );
			if( wcfm_empty($_wcfm_vendor_shipping_policy) ) $_wcfm_vendor_shipping_policy = $vendor_shipping_policy;
			$vendor_refund_policy   = get_user_meta( $vendor_id, '_dps_refund_policy', true );
			if( wcfm_empty($_wcfm_vendor_refund_policy) ) $_wcfm_vendor_refund_policy = $vendor_refund_policy;
		}
		
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_policy_tab_title = isset( $wcfm_policy_options['policy_tab_title'] ) ? $wcfm_policy_options['policy_tab_title'] : '';
		if( wcfm_empty($_wcfm_vendor_policy_tab_title) ) $_wcfm_vendor_policy_tab_title = $_wcfm_policy_tab_title;
		$_wcfm_shipping_policy = isset( $wcfm_policy_options['shipping_policy'] ) ? $wcfm_policy_options['shipping_policy'] : '';
		if( wcfm_empty($_wcfm_vendor_shipping_policy) ) $_wcfm_vendor_shipping_policy = $_wcfm_shipping_policy;
		$_wcfm_refund_policy = isset( $wcfm_policy_options['refund_policy'] ) ? $wcfm_policy_options['refund_policy'] : '';
		if( wcfm_empty($_wcfm_vendor_refund_policy) ) $_wcfm_vendor_refund_policy = $_wcfm_refund_policy;
		$_wcfm_cancellation_policy = isset( $wcfm_policy_options['cancellation_policy'] ) ? $wcfm_policy_options['cancellation_policy'] : '';
		if( wcfm_empty($_wcfm_vendor_cancellation_policy) ) $_wcfm_vendor_cancellation_policy = $_wcfm_cancellation_policy;
		
		if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
			$wcmp_policy_settings = get_option("wcmp_general_policies_settings_name");
			$_wcmp_shipping_policy = isset( $wcmp_policy_settings['shipping_policy'] ) ? $wcmp_policy_settings['shipping_policy'] : '';
			if( wcfm_empty($_wcfm_vendor_shipping_policy) ) $_wcfm_vendor_shipping_policy = $_wcmp_shipping_policy;
			$_wcmp_refund_policy = isset( $wcmp_policy_settings['refund_policy'] ) ? $wcmp_policy_settings['refund_policy'] : '';
			if( wcfm_empty($_wcfm_vendor_refund_policy) ) $_wcfm_vendor_refund_policy = $_wcmp_refund_policy;
			$_wcmp_cancellation_policy = isset( $wcmp_policy_settings['cancellation_policy'] ) ? $wcmp_policy_settings['cancellation_policy'] : '';
			if( wcfm_empty($_wcfm_vendor_cancellation_policy) ) $_wcfm_vendor_cancellation_policy = $_wcmp_cancellation_policy;
		}
		
		?>
		<!-- collapsible -->
		<div class="page_collapsible" id="wcfm_settings_form_policies_head">
			<label class="wcfmfa fa-ambulance"></label>
			<?php _e('Store Policies', 'wc-frontend-manager'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_policies_expander" class="wcfm-content">
			  <h2><?php _e('Policies Setting', 'wc-frontend-manager'); ?></h2>
				<div class="wcfm_clearfix"></div>
				<?php
				$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
				$wpeditor = apply_filters( 'wcfm_is_allow_product_wpeditor', 'wpeditor' );
				if( $wpeditor && $rich_editor ) {
					$rich_editor = 'wcfm_wpeditor';
				} else {
					$wpeditor = 'textarea';
					$_wcfm_vendor_shipping_policy     = wcfm_strip_html( $_wcfm_vendor_shipping_policy );
					$_wcfm_vendor_refund_policy       = wcfm_strip_html( $_wcfm_vendor_refund_policy );
					$_wcfm_vendor_cancellation_policy = wcfm_strip_html( $_wcfm_vendor_cancellation_policy );
				}
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendor_settings_fields_policies', array(
					                                                                        "wcfm_policy_tab_title" => array('label' => __('Policy Tab Label', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $_wcfm_vendor_policy_tab_title ),
																																									"wcfm_shipping_policy" => array('label' => __('Shipping Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_title', 'value' => $_wcfm_vendor_shipping_policy ),
																																									"wcfm_refund_policy" => array('label' => __('Refund Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_title', 'value' => $_wcfm_vendor_refund_policy ),
																																									"wcfm_cancellation_policy" => array('label' => __('Cancellation/Return/Exchange Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_title', 'value' => $_wcfm_vendor_cancellation_policy ),
																																									), $vendor_id ) );
			  ?>
			</div>
		</div>
		<div class="wcfm_clearfix"></div>
		<!-- end collapsible -->
		
		<?php
		
	}
	
	function wcfm_policy_vendor_settings_update( $vendor_id, $wcfm_settings_form ) {
		global $WCFM, $WCFMu, $_POST;
		
		if( !apply_filters( 'wcmp_vendor_can_overwrite_policies', true ) || !apply_filters( 'wcfm_is_allow_policy_settings', true ) ) return; 
		
		$is_marketplace = wcfm_is_marketplace();
		
		$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $vendor_id, 'wcfm_policy_vendor_options', true );
		
		if( isset( $wcfm_settings_form['wcfm_policy_tab_title'] ) ) {
			$wcfm_policy_vendor_options['policy_tab_title'] = $wcfm_settings_form['wcfm_policy_tab_title'];
		}
		
		if( isset( $_POST['shipping_policy'] ) ) {
			$wcfm_policy_vendor_options['shipping_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['shipping_policy'], ENT_QUOTES, 'UTF-8' ) ) );
			if( $is_marketplace && ( $is_marketplace == 'dokan' ) ) {
				update_user_meta( $vendor_id, '_dps_ship_policy', $wcfm_policy_vendor_options['shipping_policy'] );
			}
		}
		
		if( isset( $_POST['refund_policy'] ) ) {
			$wcfm_policy_vendor_options['refund_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['refund_policy'], ENT_QUOTES, 'UTF-8' ) ) );
			if( $is_marketplace && ( $is_marketplace == 'dokan' ) ) {
				update_user_meta( $vendor_id, '_dps_refund_policy', $wcfm_policy_vendor_options['refund_policy'] );
			}
		}
		
		if( isset( $_POST['cancellation_policy'] ) ) {
			$wcfm_policy_vendor_options['cancellation_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['cancellation_policy'], ENT_QUOTES, 'UTF-8' ) ) );
		}
		
		wcfm_update_user_meta( $vendor_id, 'wcfm_policy_vendor_options', $wcfm_policy_vendor_options );
	}
	
	function wcfm_policy_product_settings( $product_id, $product_type = '', $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		if( !apply_filters( 'wcfm_is_allow_policy_settings', true ) || !apply_filters( 'wcfm_is_allow_policy_product_settings', true ) || !apply_filters( 'wcfm_is_allow_product_policies', true ) ) return;
		
		if( wcfm_is_vendor() && !apply_filters( 'wcmp_vendor_can_overwrite_policies', true ) ) return;
		
		$_wcfm_product_policy_tab_title = '';
		$_wcfm_product_shipping_policy = '';
		$_wcfm_product_refund_policy = '';
		$_wcfm_product_cancellation_policy = '';
		
		$is_marketplace = wcfm_is_marketplace();
		
		if( $product_id  ) {
			$wcfm_policy_product_options = (array) get_post_meta( $product_id, 'wcfm_policy_product_options', true );
			
			$_wcfm_product_policy_tab_title = isset( $wcfm_policy_product_options['policy_tab_title'] ) ? $wcfm_policy_product_options['policy_tab_title'] : '';
			$_wcfm_product_shipping_policy = isset( $wcfm_policy_product_options['shipping_policy'] ) ? $wcfm_policy_product_options['shipping_policy'] : '';
			$_wcfm_product_refund_policy = isset( $wcfm_policy_product_options['refund_policy'] ) ? $wcfm_policy_product_options['refund_policy'] : '';
			$_wcfm_product_cancellation_policy = isset( $wcfm_policy_product_options['cancellation_policy'] ) ? $wcfm_policy_product_options['cancellation_policy'] : '';
			
			if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
				$_wcmp_shipping_policy = get_post_meta( $product_id, '_wcmp_shipping_policy', true);
				if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcmp_shipping_policy;
				$_wcmp_refund_policy = get_post_meta( $product_id, '_wcmp_refund_policy', true);
				if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcmp_refund_policy;
				$_wcmp_cancellation_policy = get_post_meta( $product_id, '_wcmp_cancellation_policy', true);
				if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcmp_cancellation_policy;
			}
		}
		
		if( $is_marketplace && wcfm_is_vendor() ) {
			$vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $vendor_id, 'wcfm_policy_vendor_options', true );
			
			$_wcfm_vendor_policy_tab_title = isset( $wcfm_policy_vendor_options['policy_tab_title'] ) ? $wcfm_policy_vendor_options['policy_tab_title'] : '';
			if( wcfm_empty($_wcfm_product_policy_tab_title) ) $_wcfm_product_policy_tab_title = $_wcfm_vendor_policy_tab_title;
			$_wcfm_vendor_shipping_policy = isset( $wcfm_policy_vendor_options['shipping_policy'] ) ? $wcfm_policy_vendor_options['shipping_policy'] : '';
			if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcfm_vendor_shipping_policy;
			$_wcfm_vendor_refund_policy = isset( $wcfm_policy_vendor_options['refund_policy'] ) ? $wcfm_policy_vendor_options['refund_policy'] : '';
			if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcfm_vendor_refund_policy;
			$_wcfm_vendor_cancellation_policy = isset( $wcfm_policy_vendor_options['cancellation_policy'] ) ? $wcfm_policy_vendor_options['cancellation_policy'] : '';
			if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcfm_vendor_cancellation_policy;
			
			if( $is_marketplace == 'wcmarketplace' ) {
				$vendor_policy_tab_title = get_user_meta( $vendor_id, '_vendor_policy_tab_title', true ); 
				if( wcfm_empty($_wcfm_product_policy_tab_title) ) $_wcfm_product_policy_tab_title = $vendor_policy_tab_title;
				$vendor_shipping_policy = get_user_meta( $vendor_id, '_vendor_shipping_policy', true ); 
				if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $vendor_shipping_policy;
				$vendor_refund_policy = get_user_meta( $vendor_id, '_vendor_refund_policy', true );
				if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $vendor_refund_policy;
				$vendor_cancellation_policy = get_user_meta( $vendor_id, '_vendor_cancellation_policy', true );
				if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $vendor_cancellation_policy;
			} elseif( $is_marketplace == 'dokan' ) {
				$vendor_shipping_policy = get_user_meta( $vendor_id, '_dps_ship_policy', true );
				if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $vendor_shipping_policy;
				$vendor_refund_policy   = get_user_meta( $vendor_id, '_dps_refund_policy', true );
				if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $vendor_refund_policy;
			}
		}
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_policy_tab_title = isset( $wcfm_policy_options['policy_tab_title'] ) ? $wcfm_policy_options['policy_tab_title'] : '';
		if( wcfm_empty($_wcfm_product_policy_tab_title) ) $_wcfm_product_policy_tab_title = $_wcfm_policy_tab_title;
		$_wcfm_shipping_policy = isset( $wcfm_policy_options['shipping_policy'] ) ? $wcfm_policy_options['shipping_policy'] : '';
		if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcfm_shipping_policy;
		$_wcfm_refund_policy = isset( $wcfm_policy_options['refund_policy'] ) ? $wcfm_policy_options['refund_policy'] : '';
		if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcfm_refund_policy;
		$_wcfm_cancellation_policy = isset( $wcfm_policy_options['cancellation_policy'] ) ? $wcfm_policy_options['cancellation_policy'] : '';
		if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcfm_cancellation_policy;
		
		if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
			$wcmp_policy_settings = get_option("wcmp_general_policies_settings_name");
			$_wcmp_shipping_policy = isset( $wcmp_policy_settings['shipping_policy'] ) ? $wcmp_policy_settings['shipping_policy'] : '';
			if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcmp_shipping_policy;
			$_wcmp_refund_policy = isset( $wcmp_policy_settings['refund_policy'] ) ? $wcmp_policy_settings['refund_policy'] : '';
			if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcmp_refund_policy;
			$_wcmp_cancellation_policy = isset( $wcmp_policy_settings['cancellation_policy'] ) ? $wcmp_policy_settings['cancellation_policy'] : '';
			if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcmp_cancellation_policy;
		}
		
		?>
		<!-- collapsible - WCMp Policies -->
		<div class="page_collapsible products_manage_policies simple variable grouped external booking" id="wcfm_products_manage_form_policies_head"><label class="wcfmfa fa-ambulance"></label><?php _e('Product Policies', 'wc-frontend-manager'); ?><span></span></div>
		<div class="wcfm-container simple variable external grouped booking">
			<div id="wcfm_products_manage_form_policies_expander" class="wcfm-content">
				<?php
				$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
				$wpeditor = apply_filters( 'wcfm_is_allow_product_wpeditor', 'wpeditor' );
				if( $wpeditor && $rich_editor ) {
					$rich_editor = 'wcfm_wpeditor';
				} else {
					$wpeditor = 'textarea';
					$_wcfm_product_shipping_policy     = wcfm_strip_html( $_wcfm_product_shipping_policy );
					$_wcfm_product_refund_policy       = wcfm_strip_html( $_wcfm_product_refund_policy );
					$_wcfm_product_cancellation_policy = wcfm_strip_html( $_wcfm_product_cancellation_policy );
				}
				$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_product_manage_fields_policies', array(  
																																														"wcfm_policy_tab_title" => array('label' => __('Policy Tab Label', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $_wcfm_product_policy_tab_title ),
																																														"wcfm_shipping_policy" => array('label' => __('Shipping Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele simple variable external grouped booking wcfm_full_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $_wcfm_product_shipping_policy ),
																																														"wcfm_refund_policy" => array('label' => __('Refund Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele simple variable external grouped booking wcfm_full_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $_wcfm_product_refund_policy ),
																																														"wcfm_cancellation_policy" => array('label' => __('Cancellation/Return/Exchange Policy', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele simple variable external grouped booking wcfm_full_ele wcfm_custom_field_editor ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $_wcfm_product_cancellation_policy ),
																																									), $product_id ) );
				?>
			</div>
		</div>
		<!-- end collapsible -->
		<div class="wcfm_clearfix"></div>
		<?php
	}
	
	function wcfm_policy_product_settings_update( $new_product_id, $wcfm_products_manage_form_data ) {
		global $WCFM;
		
		if( !apply_filters( 'wcmp_vendor_can_overwrite_policies', true ) || !apply_filters( 'wcfm_is_allow_policy_settings', true ) ) return; 
		
		$is_marketplace = wcfm_is_marketplace();
		
		$wcfm_policy_product_options = (array) get_post_meta( $new_product_id, 'wcfm_policy_product_options', true );
		
		if( isset( $wcfm_products_manage_form_data['wcfm_policy_tab_title'] ) && !empty( $wcfm_products_manage_form_data['wcfm_policy_tab_title'] ) ) {
			$wcfm_policy_product_options['policy_tab_title'] = $wcfm_products_manage_form_data['wcfm_policy_tab_title'];
		}
		
		if( isset( $wcfm_products_manage_form_data['wcfm_shipping_policy'] ) && !empty( $wcfm_products_manage_form_data['wcfm_shipping_policy'] ) ) {
			$wcfm_policy_product_options['shipping_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $wcfm_products_manage_form_data['wcfm_shipping_policy'], ENT_QUOTES, 'UTF-8' ) ) );
			if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
				update_post_meta( $new_product_id, '_wcmp_shipping_policy', $wcfm_products_manage_form_data['wcfm_shipping_policy'] );
			}
		}
		if( isset( $wcfm_products_manage_form_data['wcfm_refund_policy'] ) && !empty( $wcfm_products_manage_form_data['wcfm_refund_policy'] ) ) {
			$wcfm_policy_product_options['refund_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $wcfm_products_manage_form_data['wcfm_refund_policy'], ENT_QUOTES, 'UTF-8' ) ) );
			if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
				update_post_meta( $new_product_id, '_wcmp_refund_policy', $wcfm_products_manage_form_data['wcfm_refund_policy'] );
			}
		}
		if( isset( $wcfm_products_manage_form_data['wcfm_cancellation_policy'] ) && !empty( $wcfm_products_manage_form_data['wcfm_cancellation_policy'] ) ) {
			$wcfm_policy_product_options['cancellation_policy'] = apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $wcfm_products_manage_form_data['wcfm_cancellation_policy'], ENT_QUOTES, 'UTF-8' ) ) );
			if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
				update_post_meta( $new_product_id, '_wcmp_cancellation_policy', $wcfm_products_manage_form_data['wcfm_cancellation_policy'] );
			}
		}
		
		update_post_meta( $new_product_id, 'wcfm_policy_product_options', $wcfm_policy_product_options );
	}
	
	public function get_policy_tab_title( $product_id = 0 ) {
		global $WCFM, $product;
		
		$_wcfm_product_policy_tab_title = '';
		
		$is_marketplace = wcfm_is_marketplace();
		
		if( $product && is_object( $product ) && method_exists( $product, 'get_id' ) ) {
			$product_id = $product->get_id();
		}
		
		if( $product_id  ) {
			$wcfm_policy_product_options = (array) get_post_meta( $product_id, 'wcfm_policy_product_options', true );
			$_wcfm_product_policy_tab_title = isset( $wcfm_policy_product_options['policy_tab_title'] ) ? $wcfm_policy_product_options['policy_tab_title'] : '';
		}
		
		if( $is_marketplace ) {
			$vendor_id   = wcfm_get_vendor_id_by_post( $product_id );
			if( $vendor_id && wcfm_vendor_has_capability( $vendor_id, 'policy' ) && wcfm_vendor_has_capability( $vendor_id, 'vendor_policy' ) ) {
				$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $vendor_id, 'wcfm_policy_vendor_options', true );
				
				$_wcfm_vendor_policy_tab_title = isset( $wcfm_policy_vendor_options['policy_tab_title'] ) ? $wcfm_policy_vendor_options['policy_tab_title'] : '';
				if( wcfm_empty($_wcfm_product_policy_tab_title) ) $_wcfm_product_policy_tab_title = $_wcfm_vendor_policy_tab_title;
				
				if( $is_marketplace == 'wcmarketplace' ) {
					$vendor_policy_tab_title = get_user_meta( $vendor_id, '_vendor_policy_tab_title', true ); 
					if( wcfm_empty($_wcfm_product_policy_tab_title) ) $_wcfm_product_policy_tab_title = $vendor_policy_tab_title;
				}
			}
		}
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_policy_tab_title = isset( $wcfm_policy_options['policy_tab_title'] ) ? $wcfm_policy_options['policy_tab_title'] : '';
		if( wcfm_empty($_wcfm_product_policy_tab_title) ) $_wcfm_product_policy_tab_title = $_wcfm_policy_tab_title;
		if( wcfm_empty($_wcfm_product_policy_tab_title) ) $_wcfm_product_policy_tab_title = __( 'Store Policies', 'wc-frontend-manager' );
		
		return apply_filters( 'wcfm_product_policy_tab_title', $_wcfm_product_policy_tab_title, $product_id );
	}
	
	public function get_shipping_policy( $product_id ) {
		global $WCFM;
		
		//if( !apply_filters( 'wcmp_vendor_can_overwrite_policies', true ) || !apply_filters( 'wcfm_is_allow_policy_settings', true ) || !apply_filters( 'wcfm_is_allow_show_policy', true ) || !apply_filters( 'wcfm_is_allow_show_shipping_policy', true ) ) return; 
		
		$_wcfm_product_shipping_policy = '';
		
		$is_marketplace = wcfm_is_marketplace();
		
		if( $product_id  ) {
			$wcfm_policy_product_options = (array) get_post_meta( $product_id, 'wcfm_policy_product_options', true );
			$_wcfm_product_shipping_policy = isset( $wcfm_policy_product_options['shipping_policy'] ) ? $wcfm_policy_product_options['shipping_policy'] : '';
			
			if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
				$_wcmp_shipping_policy = get_post_meta( $product_id, '_wcmp_shipping_policy', true);
				if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcmp_shipping_policy;
			}
		}
		
		if( $is_marketplace ) {
			$vendor_id   = wcfm_get_vendor_id_by_post( $product_id );
			if( $vendor_id && wcfm_vendor_has_capability( $vendor_id, 'policy' ) && wcfm_vendor_has_capability( $vendor_id, 'vendor_policy' ) ) {
				$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $vendor_id, 'wcfm_policy_vendor_options', true );
				
				$_wcfm_vendor_shipping_policy = isset( $wcfm_policy_vendor_options['shipping_policy'] ) ? $wcfm_policy_vendor_options['shipping_policy'] : '';
				if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcfm_vendor_shipping_policy;
				
				if( $is_marketplace == 'wcmarketplace' ) {
					$vendor_shipping_policy = get_user_meta( $vendor_id, '_vendor_shipping_policy', true ); 
					if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $vendor_shipping_policy;
				} elseif( $is_marketplace == 'dokan' ) {
					$vendor_shipping_policy = get_user_meta( $vendor_id, '_dps_ship_policy', true );
					if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_vendor_shipping_policy = $vendor_shipping_policy;
				}
			}
		}
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_shipping_policy = isset( $wcfm_policy_options['shipping_policy'] ) ? $wcfm_policy_options['shipping_policy'] : '';
		if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcfm_shipping_policy;
		
		if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
			$wcmp_policy_settings = get_option("wcmp_general_policies_settings_name");
			$_wcmp_shipping_policy = isset( $wcmp_policy_settings['shipping_policy'] ) ? $wcmp_policy_settings['shipping_policy'] : '';
			if( wcfm_empty($_wcfm_product_shipping_policy) ) $_wcfm_product_shipping_policy = $_wcmp_shipping_policy;
		}
		
		return apply_filters( 'wcfm_product_shipping_policy', $_wcfm_product_shipping_policy, $product_id );
	}
	
	public function get_refund_policy( $product_id ) {
		global $WCFM;
		
		//if( !apply_filters( 'wcmp_vendor_can_overwrite_policies', true ) || !apply_filters( 'wcfm_is_allow_policy_settings', true ) || !apply_filters( 'wcfm_is_allow_show_policy', true ) || !apply_filters( 'wcfm_is_allow_show_refund_policy', true ) ) return; 
		
		$_wcfm_product_refund_policy = '';
		
		$is_marketplace = wcfm_is_marketplace();
		
		if( $product_id  ) {
			$wcfm_policy_product_options = (array) get_post_meta( $product_id, 'wcfm_policy_product_options', true );
			$_wcfm_product_refund_policy = isset( $wcfm_policy_product_options['refund_policy'] ) ? $wcfm_policy_product_options['refund_policy'] : '';
			
			if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
				$_wcmp_refund_policy = get_post_meta( $product_id, '_wcmp_refund_policy', true);
				if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcmp_refund_policy;
			}
		}
		
		if( $is_marketplace ) {
			$vendor_id   = wcfm_get_vendor_id_by_post( $product_id );
			if( $vendor_id && wcfm_vendor_has_capability( $vendor_id, 'policy' ) && wcfm_vendor_has_capability( $vendor_id, 'vendor_policy' ) ) {
				$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $vendor_id, 'wcfm_policy_vendor_options', true );
				
				$_wcfm_vendor_refund_policy = isset( $wcfm_policy_vendor_options['refund_policy'] ) ? $wcfm_policy_vendor_options['refund_policy'] : '';
				if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcfm_vendor_refund_policy;
				
				if( $is_marketplace == 'wcmarketplace' ) {
					$vendor_refund_policy = get_user_meta( $vendor_id, '_vendor_refund_policy', true );
					if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $vendor_refund_policy;
				} elseif( $is_marketplace == 'dokan' ) {
					$vendor_refund_policy   = get_user_meta( $vendor_id, '_dps_refund_policy', true );
					if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $vendor_refund_policy;
				}
			}
		}
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_refund_policy = isset( $wcfm_policy_options['refund_policy'] ) ? $wcfm_policy_options['refund_policy'] : '';
		if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcfm_refund_policy;
		
		if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
			$wcmp_policy_settings = get_option("wcmp_general_policies_settings_name");
			$_wcmp_refund_policy = isset( $wcmp_policy_settings['refund_policy'] ) ? $wcmp_policy_settings['refund_policy'] : '';
			if( wcfm_empty($_wcfm_product_refund_policy) ) $_wcfm_product_refund_policy = $_wcmp_refund_policy;
		}
		
		return apply_filters( 'wcfm_product_refund_policy', $_wcfm_product_refund_policy, $product_id );
	}
		
	public function get_cancellation_policy( $product_id ) {
		global $WCFM;
		
		//if( !apply_filters( 'wcmp_vendor_can_overwrite_policies', true ) || !apply_filters( 'wcfm_is_allow_policy_settings', true ) || !apply_filters( 'wcfm_is_allow_show_policy', true ) || !apply_filters( 'wcfm_is_allow_show_cancel_policy', true ) ) return; 
		
		$_wcfm_product_cancellation_policy = '';
		
		$is_marketplace = wcfm_is_marketplace();
		
		if( $product_id  ) {
			$wcfm_policy_product_options = (array) get_post_meta( $product_id, 'wcfm_policy_product_options', true );
			$_wcfm_product_cancellation_policy = isset( $wcfm_policy_product_options['cancellation_policy'] ) ? $wcfm_policy_product_options['cancellation_policy'] : '';
			
			if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
				$_wcmp_cancellation_policy = get_post_meta( $product_id, '_wcmp_cancellation_policy', true);
				if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcmp_cancellation_policy;
			}
		}
		
		if( $is_marketplace ) {
			$vendor_id   = wcfm_get_vendor_id_by_post( $product_id );
			if( $vendor_id && wcfm_vendor_has_capability( $vendor_id, 'policy' ) && wcfm_vendor_has_capability( $vendor_id, 'vendor_policy' ) ) {
				$wcfm_policy_vendor_options = (array) wcfm_get_user_meta( $vendor_id, 'wcfm_policy_vendor_options', true );
				
				$_wcfm_vendor_cancellation_policy = isset( $wcfm_policy_vendor_options['cancellation_policy'] ) ? $wcfm_policy_vendor_options['cancellation_policy'] : '';
				if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcfm_vendor_cancellation_policy;
				
				if( $is_marketplace == 'wcmarketplace' ) {
					$vendor_cancellation_policy = get_user_meta( $vendor_id, '_vendor_cancellation_policy', true );
					if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $vendor_cancellation_policy;
				}
			}
		}
		
		$wcfm_policy_options = wcfm_get_option( 'wcfm_policy_options', array() );
		
		$_wcfm_cancellation_policy = isset( $wcfm_policy_options['cancellation_policy'] ) ? $wcfm_policy_options['cancellation_policy'] : '';
		if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcfm_cancellation_policy;
		
		if( $is_marketplace && ($is_marketplace == 'wcmarketplace') ) {
			$wcmp_policy_settings = get_option("wcmp_general_policies_settings_name");
			$_wcmp_cancellation_policy = isset( $wcmp_policy_settings['cancellation_policy'] ) ? $wcmp_policy_settings['cancellation_policy'] : '';
			if( wcfm_empty($_wcfm_product_cancellation_policy) ) $_wcfm_product_cancellation_policy = $_wcmp_cancellation_policy;
		}
		
		return apply_filters( 'wcfm_product_cancellation_policy', $_wcfm_product_cancellation_policy, $product_id );
	}
	
	public function wcfm_policies_product_tab_content() {
		global $WCFM, $product;
		
		$shipping_policy     = '';
		$refund_policy       = '';
		$cancellation_policy = '';
		
		if( $product && is_object( $product ) && method_exists( $product, 'get_id' ) ) {
			$shipping_policy     = $this->get_shipping_policy( $product->get_id() );
			$refund_policy       = $this->get_refund_policy( $product->get_id() );
			$cancellation_policy = $this->get_cancellation_policy( $product->get_id() );
		}
		?>
		<div class="wcfm-product-policies">
		  <?php do_action( 'wcfm_policy_content_before', $product->get_id() ); ?>
		  
			<?php if( !wcfm_empty($shipping_policy) ) { ?>
			  <div class="wcfm-shipping-policies">
					<h2 class="wcfm_policies_heading"><?php echo apply_filters('wcfm_shipping_policies_heading', __('Shipping Policy', 'wc-frontend-manager')); ?></h2>
					<div class="wcfm_policies_description" ><?php echo $shipping_policy; ?></div>
			  </div>
			<?php } if( !wcfm_empty( $refund_policy ) ) { ?>
			  <div class="wcfm-refund-policies">
					<h2 class="wcfm_policies_heading"><?php echo apply_filters('wcfm_refund_policies_heading', __('Refund Policy', 'wc-frontend-manager')); ?></h2>
					<div class="wcfm_policies_description" ><?php echo $refund_policy; ?></div>
			  </div>
			<?php } if( !wcfm_empty( $cancellation_policy ) ) { ?>
			  <div class="wcfm-cancellation-policies">
					<h2 class="wcfm_policies_heading"><?php echo apply_filters('wcfm_cancellation_policies_heading', __('Cancellation / Return / Exchange Policy', 'wc-frontend-manager')); ?></h2>
					<div class="wcfm_policies_description" ><?php echo $cancellation_policy; ?></div>
			  </div>
			<?php } ?>
			
			<?php do_action( 'wcfm_policy_content_after', $product->get_id() ); ?>
		</div>
		<?php
	}
	
	function wcmp_general_policies_field_disable( $general_fields ) {
		if( isset( $general_fields['is_policy_on'] ) ) {
			unset( $general_fields['is_policy_on'] );
		}
		return $general_fields;
	}
	
	function wcmp_general_policies_tab_disable( $subtabs, $tab ) {
		global $WCMp;
		if( $tab == 'general' ) {
			if( isset( $subtabs['policies'] ) ) unset($subtabs['policies']);
		}
		return $subtabs;
	}
}