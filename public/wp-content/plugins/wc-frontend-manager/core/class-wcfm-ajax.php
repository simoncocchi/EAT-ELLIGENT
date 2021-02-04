<?php
/**
 * WCFM plugin core
 *
 * Plugin Ajax Controler
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   1.0.0
 */
 
class WCFM_Ajax {
	
	public $controllers_path;

	public function __construct() {
		global $WCFM;
		
		$this->controllers_path = $WCFM->plugin_path . 'controllers/';
		
		add_action( 'wp_ajax_wcfm_ajax_controller', array( &$this, 'wcfm_ajax_controller' ) );
		
		// Add Taxonomy New Term
    add_action('wp_ajax_wcfm_add_taxonomy_new_term', array( &$this, 'wcfm_add_taxonomy_new_term' ) );
    
    // Generate Variation Attributes
    add_action('wp_ajax_wcfm_generate_variation_attributes', array( &$this, 'wcfm_generate_variation_attributes' ) );
    
    // Product Mark as Approve
		add_action( 'wp_ajax_wcfm_product_approve', array( &$this, 'wcfm_product_approve' ) );
		
		// Product Mark as Reject
		add_action( 'wp_ajax_wcfm_product_reject', array( &$this, 'wcfm_product_reject' ) );
		
		// Product Mark as Archive - 6.2.5
		add_action( 'wp_ajax_wcfm_product_archive', array( &$this, 'wcfm_product_archive' ) );
		
		// Featured Listing - 5.4.4
    add_action('wp_ajax_wcfm_listing_featured', array( &$this, 'wcfm_listing_featured' ) );
    
    // Order Mark as Complete
		add_action( 'wp_ajax_wcfm_order_mark_complete', array( &$this, 'wcfm_order_mark_complete' ) );
    
    // Order Status Update
		add_action( 'wp_ajax_wcfm_modify_order_status', array( &$this, 'wcfm_modify_order_status' ) );
    
    // Product Delete
		add_action( 'wp_ajax_delete_wcfm_product', array( &$this, 'delete_wcfm_product' ) );
		
		// Knowledgebase Archive
		add_action( 'wp_ajax_archive_wcfm_knowledgebase', array( &$this, 'archive_wcfm_knowledgebase' ) );
		
		// Knowledgebase Publish
		add_action( 'wp_ajax_publish_wcfm_knowledgebase', array( &$this, 'publish_wcfm_knowledgebase' ) );
		
		// Knowledgebase Delete
		add_action( 'wp_ajax_delete_wcfm_knowledgebase', array( &$this, 'delete_wcfm_knowledgebase' ) );
		
		// Notice Archive
		add_action( 'wp_ajax_archive_wcfm_notice', array( &$this, 'archive_wcfm_notice' ) );
		
		// Notice Publish
		add_action( 'wp_ajax_publish_wcfm_notice', array( &$this, 'publish_wcfm_notice' ) );
		
		// Notice Topic Delete
		add_action( 'wp_ajax_delete_wcfm_notice', array( &$this, 'delete_wcfm_notice' ) );
    
		// Dismiss Add-on inactive notice
		add_action( 'wp_ajax_wcfm-dismiss-addon-inactive-notice', array( $this, 'wcfm_dismiss_inactive_addon_notice' ) );
		
		// Vendor Manager Change Vendor
		add_action( 'wp_ajax_vendor_manager_change_url', array( $this, 'vendor_manager_change_url' ) );
		
		// Sales by Vendor Change Vendor
		add_action( 'wp_ajax_sales_by_vendor_change_url', array( $this, 'sales_by_vendor_change_url' ) );
		
		// WCfM Dashboard Menu Toggle
		add_action( 'wp_ajax_wcfm_menu_toggler', array( $this, 'wcfm_menu_toggler' ) );
		
		// WCfM Ajax Pages Search
		add_action( 'wp_ajax_wcfm_json_search_pages', array( $this, 'wcfm_json_search_pages' ) );
		
		// WCfM Ajax Product Search
		add_action( 'wp_ajax_wcfm_json_search_products_and_variations', array( $this, 'wcfm_json_search_products_and_variations' ) );
		
		// WCfM Ajax Product Search
		add_action( 'wp_ajax_wcfm_json_search_products_with_variations', array( $this, 'wcfm_json_search_products_with_variations' ) );
		
		// WCfM Ajax Taxonomy Search
		add_action( 'wp_ajax_wcfm_json_search_taxonomies', array( $this, 'wcfm_json_search_taxonomies' ) );
		
		// WCfM Ajax Vendor Search
		add_action( 'wp_ajax_wcfm_json_search_vendors', array( $this, 'wcfm_json_search_vendors' ) );
		
		// Email Verification Code
		add_action( 'wp_ajax_wcfm_email_verification_code', array( &$this, 'wcfm_email_verification_code' ) );
		
		// Vendor disable
    add_action( 'wp_ajax_wcfm_vendor_disable', array( &$this, 'wcfm_vendor_disable' ) );
    
    // Vendor disable
    add_action( 'wp_ajax_wcfm_vendor_enable', array( &$this, 'wcfm_vendor_enable' ) );
    
    // Knowledgebase View
    add_action('wp_ajax_wcfm_knowledgebase_view', array( &$this, 'wcfm_knowledgebase_view' ) );
    
    // Direct Message Send Reply View
    add_action('wp_ajax_wcfm_messages_send_reply', array( &$this, 'wcfm_messages_send_reply' ) );
    
    // Generate Login Popup Form
    add_action('wp_ajax_wcfm_login_popup_form', array( &$this, 'wcfm_login_popup_form' ) );
    add_action('wp_ajax_nopriv_wcfm_login_popup_form', array( &$this, 'wcfm_login_popup_form' ) );
    
    // Login Popup Form submit
    add_action('wp_ajax_wcfm_login_popup_submit', array( &$this, 'wcfm_login_popup_submit' ) );
    add_action('wp_ajax_nopriv_wcfm_login_popup_submit', array( &$this, 'wcfm_login_popup_submit' ) );
    
    // External Product View Count Update
    add_action('wp_ajax_wcfm_store_external_product_view_update', array( &$this, 'wcfm_store_external_product_view_update' ) );
    add_action('wp_ajax_nopriv_wcfm_store_external_product_view_update', array( &$this, 'wcfm_store_external_product_view_update' ) );
  }
  
  public function wcfm_ajax_controller() {
  	global $WCFM;
  	
  	do_action( 'after_wcfm_ajax_controller' );
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = wc_clean($_POST['controller']);
  		
  		switch( $controller ) {
	  	
				case 'wc-products':
				case 'wcfm-products':
					include_once( $this->controllers_path . 'products/wcfm-controller-products.php' );
					new WCFM_Products_Controller();
			  break;
			  
			  case 'wcfm-products-manage':
			  	if( wcfm_is_booking() ) {
						include_once( $this->controllers_path . 'wc_bookings/wcfm-controller-wcbookings-products-manage.php' );
						new WCFM_WCBookings_Products_Manage_Controller();
					}
					// Third Party Plugin Support
					include_once( $this->controllers_path . 'products-manager/wcfm-controller-integrations-products-manage.php' );
					new WCFM_Integrations_Products_Manage_Controller();
					
					// Custom Field Plugin Support
					include_once( $this->controllers_path . 'products-manager/wcfm-controller-customfield-products-manage.php' );
					new WCFM_Custom_Field_Products_Manage_Controller();
					
					include_once( $this->controllers_path . 'products-manager/wcfm-controller-products-manage.php' );
					if( defined('WCFM_REST_API_CALL') ) {
           $product_manage_object = new WCFM_Products_Manage_Controller();
           return $product_manage_object->processing();
          } else {
            new WCFM_Products_Manage_Controller();
          }
					
			  break;
					
			  case 'wcfm-coupons':
					include_once( $this->controllers_path . 'coupons/wcfm-controller-coupons.php' );
					new WCFM_Coupons_Controller();
				break;
				
				case 'wcfm-coupons-manage':
					include_once( $this->controllers_path . 'coupons/wcfm-controller-coupons-manage.php' );
					new WCFM_Coupons_Manage_Controller();
				break;
				
				case 'wcfm-orders':
					if( $WCFM->is_marketplace && ( wcfm_is_vendor() || apply_filters( 'wcfm_is_show_marketplace_orders', false ) ) ) {
						if( apply_filters( 'wcfm_is_show_marketplace_itemwise_orders', false ) ) {
							include_once( $this->controllers_path . 'orders/wcfm-controller-' . $WCFM->is_marketplace . '-itemized-orders.php' );
						} else {
							include_once( $this->controllers_path . 'orders/wcfm-controller-' . $WCFM->is_marketplace . '-orders.php' );
						}
						if( $WCFM->is_marketplace == 'wcvendors' ) new WCFM_Orders_WCVendors_Controller();
						elseif( $WCFM->is_marketplace == 'wcpvendors' ) new WCFM_Orders_WCPVendors_Controller();
						elseif( $WCFM->is_marketplace == 'wcmarketplace' ) new WCFM_Orders_WCMarketplace_Controller();
						elseif( $WCFM->is_marketplace == 'dokan' ) new WCFM_Orders_Dokan_Controller();
						elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) {
              if( defined('WCFM_REST_API_CALL') ) {
                $order_wcfm_manage_object = new WCFM_Orders_WCFMMarketplace_Controller();
                return $order_wcfm_manage_object->processing();
              } else {
                 new WCFM_Orders_WCFMMarketplace_Controller();
              }
            }
					} else {
						include_once( $this->controllers_path . 'orders/wcfm-controller-orders.php' );
						if( defined('WCFM_REST_API_CALL') ) {
              $order_manage_object = new WCFM_Orders_Controller();
              return $order_manage_object->processing();
            } else {
               new WCFM_Orders_Controller();
            }
					}
				break;
				
				case 'wcfm-vendor-orders':
					if( $WCFM->is_marketplace && !wcfm_is_vendor() ) {
						include_once( $this->controllers_path . 'orders/wcfm-controller-wcfmmarketplace-orders.php' );
						new WCFM_Orders_WCFMMarketplace_Controller();
					}
				break;
				
				case 'wcfm-listings':
					include_once( $this->controllers_path . 'listings/wcfm-controller-listings.php' );
					new WCFM_Listings_Controller();
				break;
				
				case 'wcfm-applications':
					include_once( $this->controllers_path . 'listings/wcfm-controller-applications.php' );
					new WCFM_Applications_Controller();
				break;
				
				case 'wcfm-reports-out-of-stock':
					include_once( $this->controllers_path . 'reports/wcfm-controller-reports-out-of-stock.php' );
					new WCFM_Reports_Out_Of_Stock_Controller();
				break;
				
				case 'wcfm-profile':
					include_once( $this->controllers_path . 'profile/wcfm-controller-profile.php' );
					new WCFM_Profile_Controller();
				break;
					
				case 'wcfm-settings':
					if( $WCFM->is_marketplace && wcfm_is_vendor() ) {
						include_once( $this->controllers_path . 'settings/wcfm-controller-' . $WCFM->is_marketplace . '-settings.php' );
						if( $WCFM->is_marketplace == 'wcvendors' ) new WCFM_Settings_WCVendors_Controller();
						elseif( $WCFM->is_marketplace == 'wcpvendors' ) new WCFM_Settings_WCPVendors_Controller();
						elseif( $WCFM->is_marketplace == 'wcmarketplace' ) new WCFM_Settings_WCMarketplace_Controller();
						elseif( $WCFM->is_marketplace == 'dokan' ) new WCFM_Settings_Dokan_Controller();
						elseif( $WCFM->is_marketplace == 'wcfmmarketplace' ) new WCFM_Settings_Marketplace_Controller();
					} else {
						include_once( $this->controllers_path . 'settings/wcfm-controller-settings.php' );
						new WCFM_Settings_Controller();
					}
				break;
				
				case 'wcfm-capability':
					include_once( $this->controllers_path . 'capability/wcfm-controller-capability.php' );
					new WCFM_Capability_Controller();
				break;
				
				case 'wcfm-knowledgebase':
					include_once( $this->controllers_path . 'knowledgebase/wcfm-controller-knowledgebase.php' );
					new WCFM_Knowledgebase_Controller();
				break;
				
				case 'wcfm-knowledgebase-manage':
					include_once( $this->controllers_path . 'knowledgebase/wcfm-controller-knowledgebase-manage.php' );
					new wcfm_Knowledgebase_Manage_Controller();
				break;
				
				case 'wcfm-notices':
					include_once( $this->controllers_path . 'notice/wcfm-controller-notices.php' );
					new WCFM_Notices_Controller();
				break;
				
				case 'wcfm-notice-manage':
					include_once( $this->controllers_path . 'notice/wcfm-controller-notice-manage.php' );
					new wcfm_Notice_Manage_Controller();
				break;
				
				case 'wcfm-notice-reply':
					include_once( $this->controllers_path . 'notice/wcfm-controller-notice-reply.php' );
					new WCFM_Notice_Reply_Controller();
				break;
				
				case 'wcfm-messages':
					include_once( $this->controllers_path . 'messages/wcfm-controller-messages.php' );
          if( defined('WCFM_REST_API_CALL') ) {
            $notification_manage_object = new WCFM_Messages_Controller();
            return $notification_manage_object->processing();
          } else {
             new WCFM_Messages_Controller();
          }
				break;
				
				case 'wcfm-message-sent':
					include_once( $this->controllers_path . 'messages/wcfm-controller-message-sent.php' );
					new WCFM_Message_Sent_Controller();
				break;
				
				case 'wcfm-vendors':
					include_once( $this->controllers_path . 'vendors/wcfm-controller-vendors.php' );
					if( defined('WCFM_REST_API_CALL') ) {
            $vendor_list_object = new WCFM_Vendors_Controller();
            return $vendor_list_object->processing();
          } else {
            new WCFM_Vendors_Controller();
          }
				break;
				
				case 'wcfm-vendors-new':
					include_once( $this->controllers_path . 'vendors/wcfm-controller-vendors-new.php' );
					new WCFM_Vendors_New_Controller();
				break;
				
				case 'wcfm-vendors-manage':
					include_once( $this->controllers_path . 'vendors/wcfm-controller-vendors-manage.php' );
					new WCFM_Vendors_Manage_Controller();
				break;
				
				case 'wcfm-vendors-manage-profile':
					include_once( $this->controllers_path . 'vendors/wcfm-controller-vendors-manage.php' );
					new WCFM_Vendors_Manage_Profile_Controller();
				break;
				
				case 'wcfm-vendors-manage-marketplace-settings':
					include_once( $this->controllers_path . 'settings/wcfm-controller-wcfmmarketplace-settings.php' );
					new WCFM_Settings_Marketplace_Controller();
				break;
				
				case 'wcfm-vendors-manage-marketplace-shipping-settings':
					include_once( $this->controllers_path . 'vendors/wcfm-controller-wcfmmarketplace-shipping-settings.php' );
					new WCFM_Shipping_Settings_Marketplace_Controller();
				break;
				
				case 'wcfm-vendors-manage-badges':
					include_once( $this->controllers_path . 'vendors/wcfm-controller-vendors-manage.php' );
					new WCFM_Vendors_Manage_Badges_Controller();
				break;
				
				case 'wcfm-vendors-commission':
					include_once( $this->controllers_path . 'vendors/wcfm-controller-vendors-commission.php' );
					new WCFM_Vendors_Commission_Controller();
				break;
			}
  	}
  	
  	do_action( 'before_wcfm_ajax_controller' );
  	die();
  }
  
  /**
	 * Add new taxonomy term
	 */
	function wcfm_add_taxonomy_new_term() {
		global $WCFM, $WCFMu, $_POST;
		
		$taxonomy     = esc_attr( $_POST['taxonomy'] );
		$new_term     = wc_clean( $_POST['new_term'] );
		$parent_term  = wc_clean( $_POST['parent_term'] );
		$nbsp         = '&nbsp;';

		if ( taxonomy_exists( $taxonomy ) ) {

			$result = wp_insert_term( $new_term, $taxonomy, apply_filters( 'wcfm_add_taxonomy_args', array( 'parent' => $parent_term ), $taxonomy, $new_term ) );

			if ( is_wp_error( $result ) ) {
				wp_send_json( array(
					'error' => $result->get_error_message(),
				) );
			} else {
				$author_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
				
				// Set Vendor Reference 
				if( wcfm_is_vendor() )
					update_term_meta( $result['term_id'], '_wcfm_vendor', $author_id );
				
				// Addmin notification message for new_taxonomy_term 
				$author_is_admin = 0;
				$author_is_vendor = 1;
				$message_to = 0;
				$wcfm_messages = sprintf( __( 'A new %s <b>%s</b> added to the store by <b>%s</b>', 'wc-frontend-manager' ), ucfirst( $taxonomy ), $new_term, get_user_by( 'id', $author_id )->display_name );
				$WCFM->wcfm_notification->wcfm_send_direct_message( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, 'new_taxonomy_term' );
				
				// Sending front-end HTML
				$term = get_term_by( 'id', $result['term_id'], $taxonomy );
				echo '<li class="product_cats_checklist_item checklist_item_' . esc_attr( $term->term_id ) . '" data-item="' . esc_attr( $term->term_id ) . '">';
				echo '<span class="wcfmfa fa-arrow-circle-right sub_checklist_toggler"></span>';
				if( ( $taxonomy != 'product_cat' ) && ( $taxonomy != 'wcfm_knowledgebase_category' ) ) {
					echo '<label class="selectit">' . $nbsp . '<input type="checkbox" class="wcfm-checkbox" name="product_custom_taxonomies[' . $taxonomy . '][]" value="' . esc_attr( $term->term_id ) . '" checked />' . esc_html( $term->name ) . '</label>';
				} else {
					echo '<label class="selectit">' . $nbsp . '<input type="checkbox" class="wcfm-checkbox" name="product_cats[]" value="' . esc_attr( $term->term_id ) . '" checked /><span>' . esc_html( $term->name ) . '</span></label>';
				}
				echo '</li>';
				
				do_action( 'wcfm_after_add_taxonomy_new_term', $result['term_id'], $new_term, $taxonomy, $parent_term, $author_id );
			}
		}
		die();
	}
  
  public function wcfm_generate_variation_attributes() {
		global $wpdb, $WCFM;
	  
	  $wcfm_products_manage_form_data = array();
	  parse_str($_POST['wcfm_products_manage_form'], $wcfm_products_manage_form_data);
	  //print_r($wcfm_products_manage_form_data);
	  
	  if(isset($wcfm_products_manage_form_data['attributes']) && !empty($wcfm_products_manage_form_data['attributes'])) {
			$pro_attributes = '{';
			$attr_first = true;
			foreach($wcfm_products_manage_form_data['attributes'] as $attributes) {
				if(isset($attributes['is_variation'])) {
					if( isset( $attributes['is_active'] ) && !empty( $attributes['name'] ) && !empty( $attributes['value'] ) ) {
						if(!$attr_first) $pro_attributes .= ',';
						if($attr_first) $attr_first = false;
						
						if($attributes['is_taxonomy']) {
							$pro_attributes .= '"' . sanitize_title( $attributes['tax_name'] ) . '": { "name" : " ' . wcfm_removeslashes(addslashes(esc_attr(trim($attributes['name'])))) . ' ", "data" : {';
							if( !is_array($attributes['value']) ) {
								$att_values = explode( WC_DELIMITER , $attributes['value']);
								$is_first = true;
								foreach($att_values as $att_value) {
									if(!$is_first) $pro_attributes .= ',';
									if($is_first) $is_first = false;
									$pro_attributes .= '"' . sanitize_title($att_value) . '": "' . wcfm_removeslashes(addslashes(esc_attr(trim($att_value)))) . '"';
								}
							} else {
								$att_values = $attributes['value'];
								$is_first = true;
								foreach($att_values as $att_value) {
									if(!$is_first) $pro_attributes .= ',';
									if($is_first) $is_first = false;
									$att_term = get_term( absint($att_value) );
									if( $att_term ) {
										$pro_attributes .= '"' . $att_term->slug . '": "' . wcfm_removeslashes(addslashes(esc_attr($att_term->name))) . '"';
									} else {
										$pro_attributes .= '"' . sanitize_title($att_value) . '": "' . wcfm_removeslashes(addslashes(esc_attr(trim($att_value)))) . '"';
									}
								}
							}
							$pro_attributes .= '} }';
						} else {
							$pro_attributes .= '"' . sanitize_title( $attributes['name'] ) . '": { "name" : " ' . wcfm_removeslashes(addslashes(esc_attr(trim($attributes['name'])))) . ' ", "data" : {';
							$att_values = explode( WC_DELIMITER, $attributes['value']);
							$is_first = true;
							foreach($att_values as $att_value) {
								if(!$is_first) $pro_attributes .= ',';
								if($is_first) $is_first = false;
								$pro_attributes .= '"' . wcfm_removeslashes(addslashes(esc_attr(trim($att_value)))) . '": "' . wcfm_removeslashes(addslashes(esc_attr(trim($att_value)))) . '"';
							}
							$pro_attributes .= '} }';
						}
					}
				}
			}
			$pro_attributes .= '}';
			echo $pro_attributes;
		}
		
		die();
	}
  
  /**
   * Handle Product Delete
   */
  public function delete_wcfm_product() {
  	global $WCFM, $WCFMu;
  	
  	$product_id = absint($_POST['proid']);
		
		if( $product_id ) {
			$product = wc_get_product( $product_id );
			if( !$product || !is_object( $product ) ) {
				echo 'failed';
				die;
			}
			
			do_action( 'wcfm_before_product_delete', $product_id );
			if ( 'appointment' === $product->get_type() ) {
				remove_all_actions( 'before_delete_post' );
			}
			
			if( apply_filters( 'wcfm_is_allow_product_delete' , false ) ) {
				if(wp_delete_post($product_id)) {
					echo 'success';
					die;
				}
			} else {
				if(wp_trash_post($product_id)) {
					echo 'success';
					die;
				}
			}
			die;
		}
  }
  
  /**
   * Handle Product mark as Approve
   */
  function wcfm_product_approve() {
  	global $WCFM;
  	
  	if( isset( $_POST['proid'] ) && !empty( $_POST['proid'] ) ) {
  		$product_id = absint( $_POST['proid'] );
  		do_action( 'wcfm_before_product_approve', $product_id );
  		$update_product = apply_filters( 'wcfm_product_content_before_update', array(
  																																					'ID'           => $product_id,
																																						'post_status'  => 'publish',
																																						'post_type'    => 'product',
																																					), $product_id );
  		wp_update_post( $update_product, true );
  		update_post_meta( $product_id, '_wcfm_product_approved_by', get_current_user_id() );
  		do_action( 'wcfm_after_product_approve', $product_id );
  		delete_post_meta( $product_id, '_wcfm_review_product_notified' );
  	}
		
		die;
  }
  
  /**
   * Handle Product mark as Rejct
   */
  function wcfm_product_reject() {
  	global $WCFM;
  	
  	if( isset( $_POST['proid'] ) && !empty( $_POST['proid'] ) ) {
  		$product_id = absint( $_POST['proid'] );
  		$reason     = wc_clean( $_POST['reason'] );
  		do_action( 'wcfm_before_product_reject', $product_id, $reason );
  		$update_product = apply_filters( 'wcfm_product_content_before_update', array(
  																																					'ID'           => $product_id,
																																						'post_status'  => 'draft',
																																						'post_type'    => 'product',
																																					), $product_id );
  		wp_update_post( $update_product, true );
  		update_post_meta( $product_id, '_wcfm_product_rejected_by', get_current_user_id() );
  		do_action( 'wcfm_after_product_reject', $product_id, $reason );
  		delete_post_meta( $product_id, '_wcfm_review_product_notified' );
  	}
		
		die;
  }
  
  /**
   * Mark Product ss Archive
   */
  function wcfm_product_archive() {
  	global $WCFM;
  	
  	if( isset( $_POST['proid'] ) && !empty( $_POST['proid'] ) ) {
  		$product_id = absint( $_POST['proid'] );
  		do_action( 'wcfm_before_product_archived', $product_id );
  		$update_product = apply_filters( 'wcfm_product_content_before_update', array(
  																																					'ID'           => $product_id,
																																						'post_status'  => 'archived',
																																						'post_type'    => 'product',
																																					), $product_id );
  		wp_update_post( $update_product, true );
  		update_post_meta( $product_id, '_wcfm_product_archived_by', get_current_user_id() );
  		do_action( 'wcfm_after_product_archived', $product_id );
  		delete_post_meta( $product_id, '_wcfm_review_product_notified' );
  	}
		
		die;
  }
  
  /**
	 * WCFM Mark/Un-mark Listing as Featured
	 */
	public function wcfm_listing_featured() {
		global $WCFM, $WCFMu, $_POST;
		
		if( isset( $_POST['listid'] ) && !empty( $_POST['listid'] ) ) {
			$listing_id = absint($_POST['listid']);
			$is_featured = wc_clean($_POST['featured']);
			
			if( $is_featured == 'featured' ) {
				update_post_meta( $listing_id, '_featured', 1 );
			} elseif( $is_featured == 'nofeatured' ) {
				delete_post_meta( $listing_id, '_featured' );
			}
		}
		
		echo 'sucess';
		die;
	}
  
  /**
   * Handle Order status update
   */
  public function wcfm_order_mark_complete() {
  	global $WCFM;
  	
  	$order_id = absint( $_POST['orderid'] );
  	
		do_action( 'before_wcfm_order_status_update', $order_id, 'wc-completed' );
  	
  	if ( wc_is_order_status( 'wc-completed' ) && $order_id ) {
			$order = wc_get_order( $order_id );
			$order->update_status( 'completed', '', true );
			
			// Add Order Note for Log
			$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			$shop_name =  get_user_by( 'ID', $user_id )->display_name;
			if( wcfm_is_vendor() ) {
				$shop_name =  wcfm_get_vendor_store( absint($user_id) );
			}
			$wcfm_messages = sprintf( __( '<b>%s</b> order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), '#<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>', wc_get_order_status_name( 'completed' ), $shop_name );
			$is_customer_note = apply_filters( 'wcfm_is_allow_order_update_note_for_customer', '1' );
			
			if( wcfm_is_vendor() ) add_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
			$comment_id = $order->add_order_note( $wcfm_messages, $is_customer_note );
			if( wcfm_is_vendor() ) { add_comment_meta( $comment_id, '_vendor_id', $user_id ); }
			if( wcfm_is_vendor() ) remove_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
			
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'status-update' );
			
			do_action( 'woocommerce_order_edit_status', $order_id, 'completed' );
			do_action( 'wcfm_order_status_updated', $order_id, 'completed' );
		}
		die;
  }
  
  /**
   * Handle Order Details Status Update
   */
  public function wcfm_modify_order_status() {
  	global $WCFM;
  	
  	$order_id = absint( $_POST['order_id'] );
  	$order_status = wc_clean( $_POST['order_status'] );
  	
  	do_action( 'before_wcfm_order_status_update', $order_id, $order_status );
  	
  	if ( wc_is_order_status( $order_status ) && $order_id ) {
			$order = wc_get_order( $order_id );
			$order->update_status( str_replace('wc-', '', $order_status), '', true );
			
			// Add Order Note for Log
			$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			$shop_name =  get_user_by( 'ID', $user_id )->display_name;
			if( wcfm_is_vendor() ) {
				$shop_name =  wcfm_get_vendor_store( absint($user_id) );
			}
			$wcfm_messages = sprintf( __( 'Order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), wc_get_order_status_name( str_replace('wc-', '', $order_status) ), $shop_name );
			$is_customer_note = apply_filters( 'wcfm_is_allow_order_update_note_for_customer', '1' );
			
			if( wcfm_is_vendor() ) add_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
			$comment_id = $order->add_order_note( $wcfm_messages, $is_customer_note);
			if( wcfm_is_vendor() ) { add_comment_meta( $comment_id, '_vendor_id', $user_id ); }
			if( wcfm_is_vendor() ) remove_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
			
			$wcfm_messages = sprintf( __( '<b>%s</b> order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), '#<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>', wc_get_order_status_name( str_replace('wc-', '', $order_status) ), $shop_name );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'status-update' );
			
			do_action( 'woocommerce_order_edit_status', $order_id, str_replace('wc-', '', $order_status) );
			do_action( 'wcfm_order_status_updated', $order_id, str_replace('wc-', '', $order_status) );
			
			if( defined('WCFM_REST_API_CALL') ) {
				return '{"status": true, "message": "' . __( 'Order status updated.', 'wc-frontend-manager' ) . '"}';
			}
			
			echo '{"status": true, "message": "' . __( 'Order status updated.', 'wc-frontend-manager' ) . '"}';
		}
		die;
  	
  }
  
  /**
   * Handle Knowledgebase Archive
   */
  public function archive_wcfm_knowledgebase() {
  	global $WCFM, $WCFMu;
  	
  	$knowledgebaseid = absint( $_POST['knowledgebaseid'] );
		
		if($knowledgebaseid) {
			wp_update_post(array(
				'ID'            =>  $knowledgebaseid,
				'post_status'   =>  'draft'
			));
			die;
		}
  }
  
  /**
   * Handle Knowledgebase Publish
   */
  public function publish_wcfm_knowledgebase() {
  	global $WCFM, $WCFMu;
  	
  	$knowledgebaseid = absint( $_POST['knowledgebaseid'] );
		
		if($knowledgebaseid) {
			wp_update_post(array(
				'ID'            =>  $knowledgebaseid,
				'post_status'   =>  'publish'
			));
			die;
		}
  }
  
  /**
   * Handle Knowledgebase Delete
   */
  public function delete_wcfm_knowledgebase() {
  	global $WCFM, $WCFMu;
  	
  	$knowledgebaseid = absint( $_POST['knowledgebaseid'] );
		
		if($knowledgebaseid) {
			if(wp_delete_post($knowledgebaseid)) {
				echo 'success';
				die;
			}
			die;
		}
  }
  
  /**
   * Handle Notice Archive
   */
  public function archive_wcfm_notice() {
  	global $WCFM, $WCFMu;
  	
  	$noticeid = absint( $_POST['noticeid'] );
		
		if($noticeid) {
			wp_update_post(array(
				'ID'            =>  $noticeid,
				'post_status'   =>  'draft'
			));
			die;
		}
  }
  
  /**
   * Handle Notice Publish
   */
  public function publish_wcfm_notice() {
  	global $WCFM, $WCFMu;
  	
  	$noticeid = absint( $_POST['noticeid'] );
		
		if($noticeid) {
			wp_update_post(array(
				'ID'            =>  $noticeid,
				'post_status'   =>  'publish'
			));
			die;
		}
  }
  
  /**
   * Handle Notice - Topic Delete
   */
  public function delete_wcfm_notice() {
  	global $WCFM, $WCFMu;
  	
  	$noticeid = absint( $_POST['noticeid'] );
		
		if($noticeid) {
			if(wp_delete_post($noticeid)) {
				echo 'success';
				die;
			}
			die;
		}
  }
  
  /**
	 * Dismiss addon inactive notice
	 *
	 * @since 3.3.6
	 *
	 * @return void
	 */
  function wcfm_dismiss_inactive_addon_notice() {
  	if ( ! empty( $_POST['wcfm_wcfmvm_inactive'] ) ) {
			$offer_key = 'wcfm_wcfmvm_inactive04062019';
			update_option( $offer_key . '_tracking_notice', 'hide' );
		}
		
		if ( ! empty( $_POST['wcfm_wcfmu_inactive'] ) ) {
			$offer_key = 'wcfm_wcfmu_inactive09062020';
			update_option( $offer_key . '_tracking_notice', 'hide' );
		}
		
		if ( ! empty( $_POST['wcfm_wcfmgs_inactive'] ) ) {
			$offer_key = 'wcfm_wcfmgs_inactive09062020';
			update_option( $offer_key . '_tracking_notice', 'hide' );
		}
  }
  
  /**
   * Vendor manager change URL
   */
  function vendor_manager_change_url() {
  	global $WCFM, $_POST;
  	
  	if( isset( $_POST['vendor_manager_change'] ) && !empty( $_POST['vendor_manager_change'] ) ) {
  		$vendor_id = absint( $_POST['vendor_manager_change'] );
  		echo '{"status": true, "redirect": "' . get_wcfm_vendors_manage_url($vendor_id) . '"}';
  	}
  	
  	die;
  }
  
  /**
   * Vendor manager change URL
   */
  function sales_by_vendor_change_url() {
  	global $WCFM, $_POST;
  	
  	if( isset( $_POST['vendor_manager_change'] ) && !empty( $_POST['vendor_manager_change'] ) ) {
  		$vendor_id = absint( $_POST['vendor_manager_change'] );
  		echo '{"status": true, "redirect": "' . get_wcfm_reports_url( '', 'wcfm-reports-sales-by-vendor', $vendor_id) . '"}';
  	}
  	
  	die;
  }
  
  /**
   * wcfm_menu_toggler
   */
  function wcfm_menu_toggler() {
  	global $WCFM, $_POST;
  	$user_id = get_current_user_id();
  	$toggle_state = $_POST['toggle_state'];
  	update_user_meta( $user_id, '_wcfm_menu_toggle_state', $toggle_state );
  	
  	echo "success";
  	die;
  }
  
  /**
   * WCFM ajax Pages Search
   */
  function wcfm_json_search_pages() {
  	global $WCFM, $_POST, $_GET;
  	
  	$term = wc_clean( empty( $term ) ? wc_clean( $_GET['term'] ) : $term );

		if ( empty( $term ) ) {
			wp_die();
		}
		
		$args = array(
			'posts_per_page'   => 25,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'page',
			'post_mime_type'   => '',
			'post_parent'      => '',
			//'author'	   => get_current_user_id(),
			'post_status'      => array('publish', 'private'),
			'suppress_filters' => 0 
		);
		$args = apply_filters( 'wcfm_pages_args', $args );
		$args['s'] = $term;
		
		$pages_objs = get_posts( $args );
		$pages_array = array();
		$woocommerce_pages = array ( wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount'));
		foreach ( $pages_objs as $page ) {
			if(!in_array($page->ID, $woocommerce_pages)) {
				global $sitepress;
				if ( function_exists('icl_object_id') && $sitepress ) {
					$default_lang = $sitepress->get_default_language();
					$pages_array[icl_object_id( $page->ID, 'page', true, $default_lang )] = $page->post_title;
				} else {
					$pages_array[$page->ID] = $page->post_title;
				}
			}
		}
		
		wp_send_json( apply_filters( 'wcfm_json_search_pages', $pages_array ) );
  }
  
  /**
   * WCfM ajax product search
   */
  function wcfm_json_search_products_and_variations() {
  	global $WCFM, $_POST, $_GET;
  	
  	$term = wc_clean( empty( $term ) ? wc_clean( $_GET['term'] ) : $term );

		if ( empty( $term ) ) {
			wp_die();
		}
		
		$args = array(
			'posts_per_page'   => 25,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'post_parent'      => '',
			//'author'	   => get_current_user_id(),
			'post_status'      => array('publish', 'private'),
			'suppress_filters' => 0 
		);
		$args = apply_filters( 'wcfm_products_args', $args );
		$args['s'] = $term;
		
		$products_objs = get_posts( $args );
		$products_array = array();
		if( !empty($products_objs) ) {
			foreach( $products_objs as $products_obj ) {
				$product_data      = wc_get_product( $products_obj->ID );
				$products_array[esc_attr( $products_obj->ID )] = (!empty($product_data)) ? wp_kses_post( $product_data->get_formatted_name() ) : $products_obj->ID;
			}
		}
		
		wp_send_json( apply_filters( 'wcfm_json_search_products_and_variations', $products_array ) );
  }
  
  /**
   * WCfM ajax product search with Variations
   */
  function wcfm_json_search_products_with_variations() {
  	global $WCFM, $_POST, $_GET;
  	
  	$term = wc_clean( empty( $term ) ? wc_clean( $_GET['term'] ) : $term );

		if ( empty( $term ) ) {
			wp_die();
		}
		
		$args = array(
			'posts_per_page'   => 50,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'post_parent'      => '',
			//'author'	   => get_current_user_id(),
			'post_status'      => array('publish', 'private'),
			'suppress_filters' => 0 
		);
		$args = apply_filters( 'wcfm_products_args', $args );
		$args['s'] = $term;
		
		$products_objs = get_posts( $args );
		$products_array = array();
		if( !empty($products_objs) ) {
			foreach( $products_objs as $products_obj ) {
				$product_data          = wc_get_product( $products_obj->ID );
				$product_type          = $product_data->get_type();
				$wcfm_sv_product_types = apply_filters( 'wcfm_sv_product_types', array( 'simple', 'variable' ) );
				if( in_array( $product_type, $wcfm_sv_product_types ) ) {
					$variations = array();
					
					$wcfm_variable_product_types = apply_filters( 'wcfm_variable_product_types', array( 'variable', 'variable-subscription', 'pw-gift-cards' ) );
					if( in_array( $product_type, $wcfm_variable_product_types ) ) {
						$variation_ids = $product_data->get_children();
						if(!empty($variation_ids)) {
							foreach($variation_ids as $variation_id_key => $variation_id) {
								$variation_data = new WC_Product_Variation($variation_id);
								$variations[$variation_id] = $variation_data->get_formatted_name();
							}
						}
					}
					
					$products_array[esc_attr( $products_obj->ID )]['label']      = (!empty($product_data)) ? wp_kses_post( $product_data->get_formatted_name() ) : $products_obj->ID;
					$products_array[esc_attr( $products_obj->ID )]['variations'] = $variations; 
				}
			}
		}
		
		wp_send_json( apply_filters( 'wcfm_json_search_products_with_variations', $products_array ) );
  }
  
  /**
   * WCfM ajax vendor search
   */
  function wcfm_json_search_taxonomies() {
  	global $WCFM, $_POST, $_REQUEST;
  	
  	$term_list = array();
  	
  	$term = wc_clean( empty( $term ) ? sanitize_text_field( $_REQUEST['term'] ) : $term );

		$taxonomy = wc_clean( empty( $taxonomy ) ? sanitize_text_field( $_REQUEST['taxonomy'] ) : 'product_cat' );
		$parent = wc_clean( empty( $parent ) ? sanitize_text_field( $_REQUEST['parent'] ) : $parent );
		
		if ( empty( $term ) && empty( $parent ) ) {
			wp_die();
		}
		
		$default_category_id  = absint( get_option( 'default_product_cat', 0 ) );
		
		$hierarchical = empty( $parent ) ? true : false;
		
		if( ( $parent == '-1' ) || ( $parent == -1 ) ) $parent = 0;
		
		$args = array(
				'orderby'           => 'name', 
				'order'             => 'ASC',
				'hide_empty'        => false, 
				'parent'            => $parent,
				'hierarchical'      => $hierarchical, 
				'child_of'          => 0,
				'search'            => $term, 
				'cache_domain'      => 'core'
		); 
		
		$terms = get_terms( $taxonomy, $args );
		
		if( !empty( $terms ) ) {
			foreach ( $terms as $term ) {
				if( apply_filters( 'wcfm_is_allow_hide_uncatorized', false, $term->term_id ) && ( ( $term->term_id == $default_category_id ) || ( in_array( $term->slug, array( 'uncategorized', 'uncategorised' ) ) ) ) ) continue;
				if( !$parent ) {
					$wcfm_allowed_taxonomies = apply_filters( 'wcfm_allowed_taxonomies', true, $taxonomy, $term->term_id );
					if( !$wcfm_allowed_taxonomies ) continue;
				}
				$term_list[esc_attr($term->term_id)] = esc_html($term->name);
			}
	 }
		
		wp_send_json( apply_filters( 'wcfm_json_search_taxonomies', $term_list, $taxonomy ) );
  }
  
  /**
   * WCfM ajax vendor search
   */
  function wcfm_json_search_vendors() {
  	global $WCFM, $_POST, $_GET;
  	
  	$term = wc_clean( empty( $term ) ? wc_clean( $_GET['term'] ) : $term );

		if ( empty( $term ) ) {
			wp_die();
		}
		$vendor_arr = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list( false, 0, 25, $term );
		wp_send_json( apply_filters( 'wcfm_json_search_vendors', $vendor_arr ) );
  }
  
  /**
   * WCfM Profile email verification code send
   */
  function wcfm_email_verification_code() {
  	global $WCFM;
  	
  	$user_id = get_current_user_id();
  	$user_email = wc_clean( $_POST['user_email'] );
		
		if( $user_email ) {
			$email_verification_code = get_post_meta( $user_id, '_wcfm_email_verification_code', true );
			if( $email_verification_code ) {
				$verification_code = $email_verification_code;
			} else {
				$verification_code = rand( 100000, 999999 );
				update_post_meta( $user_id, '_wcfm_email_verification_code', $verification_code );
			}
			
			// Sending verification code in email
			if( !defined( 'DOING_WCFM_EMAIL' ) ) 
				define( 'DOING_WCFM_EMAIL', true );
			$verification_mail_subject = "{site_name}: " . __( "Email Verification Code", "wc-frontend-manager" ) . " - " . $verification_code;
			$verification_mail_body =    '<br/>' . __( 'Hi', 'wc-frontend-manager' ) .
																	 ',<br/><br/>' . 
																	 sprintf( __( 'Here is your email verification code - <b>%s</b>', 'wc-frontend-manager' ), '{verification_code}' ) .
																	 '<br/><br/>' . __( 'Thank You', 'wc-frontend-manager' ) .
																   '<br/><br/>';
													 
			$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $verification_mail_subject );
			$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
			$subject = str_replace( '{verification_code}', $verification_code, $subject );
			$message = str_replace( '{verification_code}', $verification_code, $verification_mail_body );
			$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Email Verification', 'wc-multivendor-membership' ) );
			
			wp_mail( $user_email, $subject, $message );
			
			echo '{"status": true, "message": "' . __( 'Email verification code send to your email.', 'wc-frontend-manager' ) . '"}';
		} else {
			echo '{"status": false, "message": "' . __( 'Email verification not working right now, please try after sometime.', 'wc-frontend-manager' ) . '"}';
		}
		die;
  }
  
  /**
	 * Vendor acount disable
	 */
	function wcfm_vendor_disable() {
		global $WCFM, $_POST, $wpdb;
		
		if( isset( $_POST['memberid'] ) ) {
			$member_id = absint( $_POST['memberid'] );
			$member_user = new WP_User( $member_id );
			$vendor_store = wcfm_get_vendor_store( $member_id );
			
			if( ( function_exists( 'wcfm_is_affiliate' ) && wcfm_is_affiliate( $member_id ) ) || apply_filters( 'wcfm_is_allow_merge_vendor_role', false ) ) {
				$member_user->remove_role('wcfm_vendor');
				$member_user->add_role('disable_vendor');
			} else {
				$member_user->set_role('disable_vendor');
			}
			
			update_user_meta( $member_id, '_disable_vendor', true );
			
			// Delete Membership Data
			do_action( 'wcfm_membership_data_reset', $member_id );
			
			// Membership Disable Admin Desktop Notification
			$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) has been disabled.', 'wc-frontend-manager' ), $member_user->first_name, $vendor_store );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'vendor-disable' );
			
			// Vendor Notification
			$wcfm_messages = sprintf( __( 'Your Store: <b>%s</b> has been disabled.', 'wc-frontend-manager' ), $vendor_store );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'vendor-disable' );
			
			do_action( 'wcfm_vendor_disable_after', $member_id );
				
			echo '{"status": true, "message": "' . __( 'Vendor successfully disabled.', 'wc-frontend-manager' ) . '"}';
			die;
		}
		echo '{"status": false, "message": "' . __( 'Vendor can not be disabled right now, please try after sometime.', 'wc-frontend-manager' ) . '"}';
		die;
	}
	
	/**
	 * Vendor acount enable
	 */
	function wcfm_vendor_enable() {
		global $WCFM, $_POST, $wpdb;
		
		if( isset( $_POST['memberid'] ) ) {
			$member_id = absint( $_POST['memberid'] );
			$member_user = new WP_User( $member_id );
			$vendor_store = wcfm_get_vendor_store( $member_id );
			
			$is_marketplace = wcfm_is_marketplace();
			if( $is_marketplace == 'wcmarketplace' ) {
				$member_user->set_role('dc_vendor');
			} elseif( $is_marketplace == 'wcpvendors' ) {
				$member_user->set_role('wc_product_vendors_admin_vendor');
			} elseif( $is_marketplace == 'wcvendors' ) {
				$member_user->set_role('vendor');
			} elseif( $is_marketplace == 'dokan' ) {
				$member_user->set_role('seller');
			} elseif( $is_marketplace == 'wcfmmarketplace' ) {
				if( ( function_exists( 'wcfm_is_affiliate' ) && wcfm_is_affiliate( $member_id ) ) || apply_filters( 'wcfm_is_allow_merge_vendor_role', false ) ) {
					$member_user->remove_role('disable_vendor');
					$member_user->add_role('wcfm_vendor');
				} else {
					$member_user->set_role('wcfm_vendor');
				}
			}
			
			delete_user_meta( $member_id, '_disable_vendor' );
			
			// Membership Enable Admin Desktop Notification
			$wcfm_messages = sprintf( __( '<b>%s</b> (Store: <b>%s</b>) has been enabled.', 'wc-frontend-manager' ), $member_user->first_name, $vendor_store );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'vendor-enable' );
			
			// Vendor Notification
			$wcfm_messages = sprintf( __( 'Your Store: <b>%s</b> has been enabled.', 'wc-frontend-manager' ), $vendor_store );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'vendor-enable' );
			
			do_action( 'wcfm_vendor_enable_after', $member_id );
				
			echo '{"status": true, "message": "' . __( 'Vendor successfully enabled.', 'wc-frontend-manager' ) . '"}';
			die;
		}
		echo '{"status": false, "message": "' . __( 'Vendor can not be enabled right now, please try after sometime.', 'wc-frontend-manager' ) . '"}';
		die;
	}
	
	/**
	 * Generate Knowledgebase View
	 */
	function wcfm_knowledgebase_view() {
		global $WCFM;
		
		$knowledgebase_id = '';
		if( isset($_POST['knowledgebaseid']) ) {
			$knowledgebase_id = absint( $_POST['knowledgebaseid'] );
			
			$knowledgebase_post = get_post( $knowledgebase_id );
			
			echo '<table><tbody><tr><td><h2 style="font-size: 18px;line-height: 20px;color:#00798b;text-decoration:underline;">';
			echo $knowledgebase_post->post_title;
			echo '</h2></td></tr><tr><td>';
			echo $knowledgebase_post->post_content;
			echo '</td></tr></tbody></table>';
		}
		die;
	}
	
	/**
	 * Generate Send Reply Message Popup
	 */
	function wcfm_messages_send_reply() {
		global $WCFM;
		$WCFM->template->get_template( 'messages/wcfm-view-messages-send-reply.php' );
		die;
	}
	
	/**
	 * Generate Login Popup Form
	 */
	function wcfm_login_popup_form() {
		global $WCFM;
		
		$WCFM->template->get_template( 'login-popup/wcfm-login-popup-form.php' );
		die;
	}
	
	/**
	 * login popup form submit
	 */
	function wcfm_login_popup_submit() {
		global $WCFM, $current_user;
		
		$wcfm_login_popup_form_data = array();
	  parse_str($_POST['wcfm_login_popup_form'], $wcfm_login_popup_form_data);
	  
	  if ( empty( $wcfm_login_popup_form_data['wcfm_login_popup_username'] ) ) {
			echo '{"status": false, "message": "' . __( 'Please insert username before submit.', 'wc-frontend-manager' ) . '"}';
			die;
		}
		
		if ( empty( $wcfm_login_popup_form_data['wcfm_login_popup_password'] ) ) {
			echo '{"status": false, "message": "' . __( 'Please insert password before submit.', 'wc-frontend-manager' ) . '"}';
			die;
		}
		
		$creds = array(
									'user_login'    => trim( wp_unslash( $wcfm_login_popup_form_data['wcfm_login_popup_username'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
									'user_password' => $wcfm_login_popup_form_data['wcfm_login_popup_password'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
									'remember'      => true, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
								);
		
		if( is_email( $wcfm_login_popup_form_data['wcfm_login_popup_username'] ) ) {
			
			if( !email_exists( $wcfm_login_popup_form_data['wcfm_login_popup_username']) ) {
				echo '{"status": false, "message": "' . __( 'Please insert a valid username / e-mail address.', 'wc-frontend-manager' ) . '"}';
				die;
			}
			
			$current_user = get_user_by( 'email', $wcfm_login_popup_form_data['wcfm_login_popup_username'] );
			if( $current_user && is_a( $current_user, 'WP_User' ) ) {
				//wp_set_auth_cookie( $current_user->ID, true );
				// Perform the login.
				$user = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					echo '{"status": false, "message": "' . __( 'Please try again!', 'wc-frontend-manager' ) . $user->get_error_message() . '"}';
				} else {
					echo '{"status": true, "message": "' . __( 'Login successfully ...', 'wc-frontend-manager' ) . '"}';
				}
			} else {
				echo '{"status": false, "message": "' . __( 'Please try again!', 'wc-frontend-manager' ) . '"}';
			}
			
		} else {
			if ( !validate_username( $wcfm_login_popup_form_data['wcfm_login_popup_username'] ) || !username_exists( $wcfm_login_popup_form_data['wcfm_login_popup_username'] ) ) {
				echo '{"status": false, "message": "' . __( 'Please insert a valid username / e-mail address.', 'wc-frontend-manager' ) . '"}';
				die;
			}
			
			$current_user = get_user_by( 'login', $wcfm_login_popup_form_data['wcfm_login_popup_username'] );
			if( $current_user && is_a( $current_user, 'WP_User' ) ) {
				//wp_set_auth_cookie( $current_user->ID, true );
				// Perform the login.
				$user = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					echo '{"status": false, "message": "' . __( 'Please try again!', 'wc-frontend-manager' ) . '"}';
				} else {
					echo '{"status": true, "message": "' . __( 'Login successfully ...', 'wc-frontend-manager' ) . '"}';
				}
			} else {
				echo '{"status": false, "message": "' . __( 'Please try again!', 'wc-frontend-manager' ) . '"}';
			}
		}
		
		
		die;
	}
	
	/**
	 * External Product View Update
	 */
	function wcfm_store_external_product_view_update() {
		global $WCFM, $wpdb, $_SERVER;
		
		if( isset( $_POST['product_id'] ) && !empty( $_POST['product_id'] ) ) {
			$product_id = absint( $_POST['product_id'] );
			
			if( !isset( $_SESSION['wcfm_pages'] ) || !isset( $_SESSION['wcfm_pages']['products'] ) || ( isset( $_SESSION['wcfm_pages'] ) && isset( $_SESSION['wcfm_pages']['products'] ) && !in_array( $product_id, $_SESSION['wcfm_pages']['products'] ) ) ) {
			
				$the_post = get_post( $product_id );
				$post_author = $the_post->post_author;
				if( !$post_author ) $post_author = 1;
				
				$todate = date('Y-m-d');
				
				$_SESSION['location']['country'] = '';
				$_SESSION['location']['state'] = '';
				$_SESSION['location']['city'] = '';
				
				// wcfm_detailed_analysis Query
				$wcfm_detailed_analysis = "INSERT into {$wpdb->prefix}wcfm_detailed_analysis 
																	(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `referer`, `ip_address`, `country`, `state`, `city`)
																	VALUES
																	(0, 0, 1, {$product_id}, {$post_author}, '{$_SERVER['HTTP_REFERER']}', '{$_SERVER['REMOTE_ADDR']}', '{$_SESSION['location']['country']}', '{$_SESSION['location']['state']}', '{$_SESSION['location']['city']}')";
				$wpdb->query($wcfm_detailed_analysis);
				
				// wcfm_daily_analysis Query
				$wcfm_daily_analysis = "INSERT into {$wpdb->prefix}wcfm_daily_analysis 
																	(`is_shop`, `is_store`, `is_product`, `product_id`, `author_id`, `count`, `visited`)
																	VALUES
																	(0, 0, 1, {$product_id}, {$post_author}, 1, '{$todate}')
																	ON DUPLICATE KEY UPDATE
																	count = count+1";
				$wpdb->query($wcfm_daily_analysis);
				
				$wcfm_product_views = (int) get_post_meta( $product_id, '_wcfm_product_views', true );
				if( !$wcfm_product_views ) $wcfm_product_views = 1;
				else $wcfm_product_views += 1;
				update_post_meta( $product_id, '_wcfm_product_views', $wcfm_product_views );
				
				// Session store
				$_SESSION['wcfm_pages']['products'][] = $product_id;
			}
		}
		die;
	}
	
}