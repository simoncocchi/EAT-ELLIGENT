<?php
/**
 * WCFM Vendor Membership plugin core
 *
 * Plugin Pay for Product Module
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/core
 * @version   2.0.0
 */
 
class WCFMvm_Pay_For_Product {
	
	public function __construct() {
		global $WCFM, $WCFMvm;
		
		// Pay for Product Settings
		add_action( 'end_wcfm_membership_settings_form', array( &$this, 'wcfmvm_pay_per_product_settings' ), 100 );
		add_action( 'wcfm_membership_settings_update', array( &$this, 'wcfmvm_pay_per_product_update' ), 100 );
		
		if( wcfm_is_vendor() && apply_filters( 'wcfm_is_allow_pay_for_product', true ) ) {
			// Set Pay for Product Default Product Limit
			add_filter( 'wcfm_vendor_product_limit', array( &$this, 'wcfmvm_pay_per_product_limit' ), 750, 2 );
		
			// Show Pay for Product when product limit reached
			add_action( 'wcfm_product_limit_reached', array( &$this, 'wcfmvm_pay_per_product_option' ) );
		
			// Pay for Product Button Handler
			add_action( 'wp_ajax_wcfm_pay_for_product', array( &$this, 'wcfmvm_pay_for_product_checkout' ) );
		}
		
		// WC Checkout for WCfM Pay for Product process
    add_action( 'woocommerce_order_status_completed', array( &$this, 'wcfmvm_pay_per_product_process_on_order_completed' ), 20, 1 );
    
    // Pay for Product message type
		add_filter( 'wcfm_message_types', array( &$this, 'wcfm_pay_per_product_message_types' ), 52 );
		
		// Load Pay for Product Scripts
    add_action( 'wcfm_load_scripts', array( &$this, 'load_scripts' ) );
    add_action( 'after_wcfm_load_scripts', array( &$this, 'load_scripts' ) );
		
		// Load Pay for Product Styles
    add_action( 'wcfm_load_styles', array( &$this, 'load_styles' ) );
    add_action( 'after_wcfm_load_styles', array( &$this, 'load_styles' ) );
		
	}
	
	function wcfmvm_pay_per_product_settings( $wcfm_options ) {
		global $WCFM, $WCFMvm;
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );

		$pay_for_product_settings = array();
		if( isset( $wcfm_membership_options['pay_for_product_settings'] ) ) $pay_for_product_settings = $wcfm_membership_options['pay_for_product_settings'];
		$cost = isset( $pay_for_product_settings['cost'] ) ? $pay_for_product_settings['cost'] : '';
		$credit = isset( $pay_for_product_settings['credit'] ) ? $pay_for_product_settings['credit'] : '1';
		$default_limit = isset( $pay_for_product_settings['default_limit'] ) ? $pay_for_product_settings['default_limit'] : 'by_cap';
		?>
		<!-- collapsible -->
		<div class="page_collapsible" id="membership_settings_form_pay_per_product_head">
			<label class="wcfmfa fa-cube"></label>
			<?php _e('Pay for Product', 'wc-multivendor-membership'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="membership_settings_form_pay_per_product_expander" class="wcfm-content">
			  <h2><?php _e('Pay for Product', 'wc-multivendor-membership'); ?></h2>
			  <?php wcfm_video_tutorial( 'https://www.youtube.com/embed/WwFHorx93Fw' ); ?>
			  <div class="wcfm_clearfix"></div>
				<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'membership_setting_pay_per_product_fields', array(
																																											"pay_per_product_cost" => array( 'label' => __('Cost', 'wc-multivendor-membership') . '(' . get_woocommerce_currency_symbol() . ')', 'name' => 'pay_for_product_settings[cost]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => 1, 'step' => 0.1 ), 'hints' => __( 'Cost for purchasing product limit credit.', 'wc-multivendor-membership' ), 'value' => $cost ),
																																											"pay_per_product_credit" => array( 'label' => __('Product Limit Credit', 'wc-multivendor-membership'), 'name' => 'pay_for_product_settings[credit]', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => 1, 'step' => 1 ), 'hints' => __( 'No. of product(s) limit will be credited to the account.', 'wc-multivendor-membership' ), 'value' => $credit ),
																																											"pay_per_product_default_product_limit" => array( 'label' => __('Default Product Limit', 'wc-multivendor-membership'), 'name' => 'pay_for_product_settings[default_limit]', 'type' => 'select', 'options' => array( 'by_cap' => __( 'As per capability', 'wc-multivendor-membership') , 'zero' => __( 'No Limit - 0', 'wc-multivendor-membership' ) ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __( 'You may overwrite any other product limit setting by this and may set NO LIMIT to enforce vendors to purchase credit for adding new products.', 'wc-multivendor-membership' ), 'value' => $default_limit ),
																																											) ) );
			  ?>
			</div>
		</div>
		<div class="wcfm_clearfix"></div>
		<!-- end collapsible -->
		
		<?php
		
	}
	
	function wcfmvm_pay_per_product_update( $wcfm_membership_settings_form_data ) {
		global $WCFM, $WCFMvm;
		if( isset( $wcfm_membership_settings_form_data['pay_for_product_settings'] ) ) {
			$pay_for_product_settings = $wcfm_membership_settings_form_data['pay_for_product_settings'];
			$cost = isset( $pay_for_product_settings['cost'] ) ? $pay_for_product_settings['cost'] : 0;
			$credit = isset( $pay_for_product_settings['credit'] ) ? $pay_for_product_settings['credit'] : '1';
			$default_limit = isset( $pay_for_product_settings['default_limit'] ) ? $pay_for_product_settings['default_limit'] : 'by_cap';
			
			//if( !$cost ) return;
			
			$wcfm_pay_for_product_settings = get_option( 'wcfm_pay_for_product_settings', array() );
			$pay_for_product_id = isset( $wcfm_pay_for_product_settings['product'] ) ? absint( $wcfm_pay_for_product_settings['product'] ) : '';
			
			$payfor_product_exist = false;
			if( $pay_for_product_id ) {
				$pay_for_product = wc_get_product( $pay_for_product_id );
				if( $pay_for_product && !is_wp_error( $pay_for_product ) && is_object( $pay_for_product ) )  $payfor_product_exist = true;
			}
			
			if( !$pay_for_product_id || !$payfor_product_exist ) {
				$new_product = array(
															'post_title'   => __('Product limit credit purchase', 'wc-multivendor-membership'),
															'post_status'  => 'publish',
															'post_type'    => 'product',
															'post_name'    => '_par_for_product'
														);
				$new_product_id = wp_insert_post( $new_product, true );
				if( !is_wp_error( $new_product_id ) ) {
					wp_set_object_terms( $new_product_id, 'simple', 'product_type' );
					$pay_for_product_id = $new_product_id;
				}
			}
			
			if( $pay_for_product_id ) {
				$classname    = WC_Product_Factory::get_product_classname( $pay_for_product_id, 'simple' );
				$product      = new $classname( $pay_for_product_id );
				$product->set_props( array( 'regular_price' => wc_clean( $cost ) ) );
				$product->set_props( array( 'virtual' => true ) );
				$product->save();
				
				update_post_meta( $pay_for_product_id, '_wcfm_pay_for_product', true );
				update_post_meta( $pay_for_product_id, '_product_credit_limit', $credit );
				update_post_meta( $pay_for_product_id, '_sold_individually', 'yes'  );
				wp_set_object_terms( $pay_for_product_id, array( 'exclude-from-search', 'exclude-from-catalog' ), 'product_visibility' );
				$pay_for_product_settings['product'] = $pay_for_product_id;
			}
			
			update_option( 'wcfm_pay_for_product_settings', $pay_for_product_settings );
	  }
	}
	
	function wcfmvm_pay_per_product_limit( $product_limit, $current_user_id = 0 ) {
		global $WCFM, $WCFMvm;
		
		// Fetching current user's pay for product credit limit
		if( !$current_user_id ) {
			$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		}
		
		$wcfm_pay_for_product_settings = get_option( 'wcfm_pay_for_product_settings', array() );
		if( !empty( $wcfm_pay_for_product_settings ) ) {
			$cost = isset( $wcfm_pay_for_product_settings['cost'] ) ? $wcfm_pay_for_product_settings['cost'] : '';
			$credit = isset( $wcfm_pay_for_product_settings['credit'] ) ? $wcfm_pay_for_product_settings['credit'] : '1';
			$default_limit = isset( $wcfm_pay_for_product_settings['default_limit'] ) ? $wcfm_pay_for_product_settings['default_limit'] : 'by_cap';
			
			$wcfm_pay_for_product_credit = get_user_meta( $current_user_id, '_wcfm_pay_for_product_credit', true );
			$wcfm_pay_for_product_credit = absint( $wcfm_pay_for_product_credit );
			
			if( $default_limit == 'by_cap' ) {
				if( ( $product_limit == -1 ) || ( $product_limit == '-1' ) ) {
					$product_limit = $wcfm_pay_for_product_credit;
				} elseif( $product_limit && ( $product_limit != 1989 ) ) { 
					$product_limit = absint($product_limit);
					$product_limit += $wcfm_pay_for_product_credit; 
				}
			} elseif( $wcfm_pay_for_product_credit ) {
				$product_limit = $wcfm_pay_for_product_credit;
			} else {
				$product_limit = -1;
			}
		}
		
		return $product_limit;
	}
	
	function wcfmvm_pay_per_product_option() {
		global $WCFM, $WCFMvm;
		$wcfm_pay_for_product_settings = get_option( 'wcfm_pay_for_product_settings', array() );
		if( !empty( $wcfm_pay_for_product_settings ) ) {
			$cost = isset( $wcfm_pay_for_product_settings['cost'] ) ? $wcfm_pay_for_product_settings['cost'] : '';
			$credit = isset( $wcfm_pay_for_product_settings['credit'] ) ? $wcfm_pay_for_product_settings['credit'] : '1';
			$default_limit = isset( $wcfm_pay_for_product_settings['default_limit'] ) ? $wcfm_pay_for_product_settings['default_limit'] : 'by_cap';
			$pay_for_product_id = isset( $wcfm_pay_for_product_settings['product'] ) ? absint( $wcfm_pay_for_product_settings['product'] ) : '';
			
			if( $pay_for_product_id ) {
				do_action( 'wcfm_product_limit_pay_for_product_before' );
				?>
				<div class="wcfm_pay_for_product_container">
				  <div class="wcfm_pay_for_product_message">
				    <?php _e( 'Increase your product limit ..', 'wc-multivendor-membership' ); ?>
				  </div>
				  <div class="wcfm_pay_for_product_content">
				    <div class="wcfm_pay_for_product_cost">
				      <?php echo wc_price( $cost ); ?>
				    </div>
				    <div class="wcfm_pay_for_product_description">
							<div class="wcfm_pay_for_product_credit_info">
								<?php printf( __( 'Increase Limit By %s', 'wc-multivendor-membership' ), '<span class="wcfm_pay_for_product_credit_limit">' . $credit . '</span>' ); ?>
							</div>
							<div class="wcfm_pay_for_product_button_container">
								<input class="wcfm_pay_for_product_button wcfm_submit_button button" type="button" value="<?php _e( "Buy Now", 'wc-multivendor-membership' ); ?>">
							</div>
							<div class="wcfm-clearfix"></div><br />
						</div>
				  </div>
				</div>
				<?php
				do_action( 'wcfm_product_limit_pay_for_product_after' );
			}
		}
	}
	
	function wcfmvm_pay_for_product_checkout() {
		global $WCFM, $WCFMvm;
		$wcfm_pay_for_product_settings = get_option( 'wcfm_pay_for_product_settings', array() );
		if( !empty( $wcfm_pay_for_product_settings ) ) {
			$cost = isset( $wcfm_pay_for_product_settings['cost'] ) ? $wcfm_pay_for_product_settings['cost'] : '';
			$credit = isset( $wcfm_pay_for_product_settings['credit'] ) ? $wcfm_pay_for_product_settings['credit'] : '1';
			$default_limit = isset( $wcfm_pay_for_product_settings['default_limit'] ) ? $wcfm_pay_for_product_settings['default_limit'] : 'by_cap';
			$pay_for_product_id = isset( $wcfm_pay_for_product_settings['product'] ) ? absint( $wcfm_pay_for_product_settings['product'] ) : '';
			
			if( $pay_for_product_id ) {
				WC()->cart->empty_cart();
				WC()->cart->add_to_cart( $pay_for_product_id );
				echo '{"status": true, "message": "' . __( 'Pay for Product succes!', 'wc-multivendor-membership' ) . '", "redirect": "' . wc_get_checkout_url() . '"}';
				die;
			}
		}
		echo '{"status": false, "message": "' . __( 'Pay for Product failed!', 'wc-multivendor-membership' ) . '"}';
		die;
	}
	
	function wcfmvm_pay_per_product_process_on_order_completed( $order_id ) {
		global $WCFM, $WCFMvm, $wpdb;
		
		$wcfm_pay_for_product_settings = get_option( 'wcfm_pay_for_product_settings', array() );
		if( !empty( $wcfm_pay_for_product_settings ) ) {
			$cost = isset( $wcfm_pay_for_product_settings['cost'] ) ? $wcfm_pay_for_product_settings['cost'] : '';
			$credit = isset( $wcfm_pay_for_product_settings['credit'] ) ? $wcfm_pay_for_product_settings['credit'] : '1';
			$default_limit = isset( $wcfm_pay_for_product_settings['default_limit'] ) ? $wcfm_pay_for_product_settings['default_limit'] : 'by_cap';
			$pay_for_product_id = isset( $wcfm_pay_for_product_settings['product'] ) ? absint( $wcfm_pay_for_product_settings['product'] ) : '';
			
			if( $pay_for_product_id ) {
				$order         = new WC_Order( $order_id );
				$line_items    = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
				foreach ( $line_items as $item_id => $item ) {
					if( $item->get_product_id() == $pay_for_product_id ) {
						$member_id       = absint( $order->get_user_id() );
						$member_user     = new WP_User( absint( $member_id ) );
						
						// Fetching current user's pay for product credit limit
						$wcfm_pay_for_product_credit = get_user_meta( $member_id, '_wcfm_pay_for_product_credit', true );
						$wcfm_pay_for_product_credit = absint( $wcfm_pay_for_product_credit );
						$wcfm_pay_for_product_credit += absint($credit);
						update_user_meta( $member_id, '_wcfm_pay_for_product_credit', $wcfm_pay_for_product_credit );
						
						if( !defined( 'DOING_WCFM_EMAIL' ) ) 
							 define( 'DOING_WCFM_EMAIL', true );
							
						// Vendor Pay for Product Admin Email Notification
						if( apply_filters( 'wcfm_is_allow_pay_per_product_admin_email', true ) ) {
							$mail_to = apply_filters( 'wcfm_admin_email_notification_receiver', get_bloginfo( 'admin_email' ), 'pay_for_product' );
							$pay_for_product_admin_notication_subject = '{site_name}: {vendor_name} - ' . __( 'Pay for Product Credit Limit', 'wc-multivendor-membership' );
							$pay_for_product_admin_notication_content = __( 'Hi', 'wc-frontend-manager' ) .
																												 ',<br/><br/>' . 
																												 sprintf( __( '%s has just purchased product credit limit: %s.', 'wc-multivendor-membership' ), '{vendor_name}', '<b>{credit_limit}</b>' ) .
																												 '<br /><br/>' . __( 'Thank You', 'wc-frontend-manager' ) .
																												 '<br /><br/>';
																			 
							$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $pay_for_product_admin_notication_subject );
							$subject = str_replace( '{vendor_name}', $member_user->first_name, $subject );
							$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
							$message = str_replace( '{vendor_name}', $member_user->first_name, $pay_for_product_admin_notication_content );
							$message = str_replace( '{credit_limit}', $credit, $message );
							$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Pay for Product', 'wc-multivendor-membership' ) );
							
							wp_mail( $mail_to, $subject, $message ); 
						}
						
						// Vendor Pay for Product Vendor Email Notification
						if( apply_filters( 'wcfm_is_allow_pay_per_product_vendor_email', true ) ) {
							$pay_for_product_vendor_notication_subject = '{site_name}: ' . __( 'Product Limit Credited', 'wc-multivendor-membership' );
							$pay_for_product_vendor_notication_content = __( 'Hi', 'wc-frontend-manager' ) .
																													 ',<br/><br/>' . 
																													 sprintf( __( 'You have just received product credit limit: %s', 'wc-multivendor-membership' ), '<b>{credit_limit}</b>' ) .
																													 '<br /><br/>' . __( 'Thank You', 'wc-frontend-manager' ) .
																													 '<br /><br/>';
																			 
							$subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $pay_for_product_vendor_notication_subject );
							$subject = apply_filters( 'wcfm_email_subject_wrapper', $subject );
							$message = str_replace( '{vendor_name}', $member_user->first_name, $pay_for_product_vendor_notication_content );
							$message = str_replace( '{credit_limit}', $credit, $message );
							$message = apply_filters( 'wcfm_email_content_wrapper', $message, __( 'Pay for Product', 'wc-multivendor-membership' ) );
							
							wp_mail( $member_user->user_email, $subject, $message ); 
						}
						
						// Pay for Product Desktop Notification
						if( apply_filters( 'wcfm_is_allow_pay_per_product_notification', true ) ) {
							$wcfm_messages = sprintf( __( '%s has just received product credit limit: %s', 'wc-multivendor-membership' ), $member_user->first_name, $credit );
							$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'pay_for_product', false );
							
							// For Vendor
							$wcfm_messages = sprintf( __( 'You have just received product credit limit: %s', 'wc-multivendor-membership' ), $credit );
							$WCFM->wcfm_notification->wcfm_send_direct_message( -1, $member_id, 1, 0, $wcfm_messages, 'pay_for_product', false );
						}
						
						break;
					}
				}
			}
		}
	}
	
	function wcfm_pay_per_product_message_types( $message_types ) {
  	$message_types['pay_for_product']         = __( 'Pay for Product Credit', 'wc-multivendor-membership' );
		return $message_types;
	}
	
	public function load_scripts( $end_point ) {
	  global $WCFM, $WCFMvm;
	  
	  switch( $end_point ) {
	  	
	  	case 'wcfm-products-manage':
	  	case 'wcfm-products-import':
	    	wp_enqueue_script( 'wcfmvm_per_for_product_js', $WCFMvm->library->js_lib_url . 'wcfmvm-script-per-for-product.js', array('jquery'), $WCFMvm->version, true );
      break;
    }
  }
	
	public function load_styles( $end_point ) {
	  global $WCFM, $WCFMvm;
		
	  switch( $end_point ) {
	  	
	  	case 'wcfm-products-manage':
	  	case 'wcfm-products-import':
	    	wp_enqueue_style( 'wcfmvm_per_for_product_css',  $WCFMvm->library->css_lib_url . 'wcfmvm-style-pay-for-product.css', array(), $WCFMvm->version );
		  break;
		}
	}
}