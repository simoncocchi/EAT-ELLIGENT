<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('WCFMmp_Email_Store_New_Order')) :

	class WCFMmp_Email_Store_New_Order extends WC_Email {
		public $order;
		/**
		 * Constructor
		 */
		function __construct() {
			global $WCFM, $WCFMmp;
			$this->id = 'store-new-order';
			$this->title = __( 'Store New Order', 'wc-multivendor-marketplace' );
			$this->description = __('New order notification emails are sent when order is processing.', 'wc-multivendor-marketplace');

			//$this->heading = __('New Vendor Order', 'wc-multivendor-marketplace');
			//$this->subject = __('[{site_title}] New vendor order ({order_number}) - {order_date}', 'wc-multivendor-marketplace');

			$this->template_html = 'emails/store-new-order.php';
			$this->template_plain = 'emails/plain/store-new-order.php';
			$this->template_base = $WCFMmp->plugin_path . 'views/';
			
			// Call parent constructor
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return apply_filters('wcfmmp_store_new_order_email_subject', __('[{site_title}] New Store Order ({order_number}) - {order_date}', 'wc-multivendor-marketplace'), $this->object);
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return apply_filters('wcfmmp_store_new_order_email_heading', __('New Store Order', 'wc-multivendor-marketplace'), $this->object);
		}

		/**
		 * trigger function.
		 *
		 * @access public
		 * @return void
		 */
		function trigger( $order_id ) {
			global $WCFM, $WCFMmp;
			
			if( !$order_id ) return;
			if( !$this->is_enabled() ) return;
			
			$order = wc_get_order($order_id);
			$order_vendors = array(); 
			$items = $order->get_items('line_item');
			if( !empty( $items ) ) {
				foreach( $items as $item_id => $item ) {
					$order_item_id = $item->get_id();
					$line_item = new WC_Order_Item_Product( $item );
					$product  = $line_item->get_product();
					$product_id = $line_item->get_product_id();
					$variation_id = $line_item->get_variation_id();
					
					if( $product_id ) {
						$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
						if( $vendor_id && !isset( $order_vendors[$vendor_id] ) ) {
							$order_vendors[$vendor_id] = wcfm_get_vendor_store_email_by_vendor( $vendor_id );
						}
					}
				}
			}
			
			if( $order_vendors ) {
				
				if( !$WCFM->wcfm_marketplace ) {
					$WCFM->load_class( 'wcfmmarketplace' );
					$WCFM->wcfm_marketplace = new WCFM_Marketplace();
				}
				
				foreach( $order_vendors as $vendor_id => $vendor_email ) {
					
					if( !wcfm_vendor_has_capability( $vendor_id, 'view_orders' ) || !apply_filters( 'wcfmmp_is_allow_store_new_order_email', true, $vendor_id ) ) continue;

					if( $vendor_email ) {
						
						$this->object       = $this->order = $order;

						$this->find[]       = '{order_date}';
						$this->replace[]    = date_i18n(wc_date_format(), strtotime($this->order->get_date_created()));

						$this->find[]       = '{order_number}';
						$this->replace[]    = $this->order->get_order_number();
						
						$this->find[]       = '{store_name}';
						$this->replace[]    = wcfm_get_vendor_store_name( $vendor_id );
						
						$this->vendor_id    = $vendor_id;
						$this->recipient    = $vendor_email;
						$this->vendor_email = $vendor_email;
					}

					if ( !$this->get_recipient() ) {
						return;
					}
					
					$WCFM->wcfm_marketplace->vendor_id = $vendor_id;
					
					$headers = $this->get_headers();
					
					// Filter to add Group Managers in CC
					$headers = apply_filters( 'wcfmmp_store_new_order_email_header', $headers, $vendor_id );
					
					$subject = apply_filters( 'wcfmmp_store_new_order_email_subject', $this->get_subject(), $vendor_id );

					$this->send( $this->get_recipient(), $subject, $this->get_content(), $headers, $this->get_attachments() );
				}
			}
		}

		/**
		 * get_content_html function.
		 *
		 * @access public
		 * @return string
		 */
		function get_content_html() {
			return wc_get_template_html($this->template_html, array(
																															'email_heading' => $this->get_heading(),
																															'vendor_id'     => $this->vendor_id,
																															'order'         => $this->order,
																															'blogname'      => $this->get_blogname(),
																															'sent_to_admin' => false,
																															'plain_text'    => false,
																															'email'         => $this
																															), 'wcfm/', $this->template_base);
		}

		/**
		 * get_content_plain function.
		 *
		 * @access public
		 * @return string
		 */
		function get_content_plain() {
			return wc_get_template_html($this->template_plain, array(
																															'email_heading' => $this->get_heading(),
																															'vendor_id'     => $this->vendor_id,
																															'order'         => $this->order,
																															'blogname'      => $this->get_blogname(),
																															'sent_to_admin' => false,
																															'plain_text'    => true,
																															'email'         => $this
																															), 'wcfm/', $this->template_base);
		}

		/**
		 * Initialise Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		function init_form_fields() {
				global $WCFM, $WCFMmp;
				$this->form_fields = array(
						'enabled' => array(
								'title' => __('Enable/Disable', 'wc-multivendor-marketplace'),
								'type' => 'checkbox',
								'label' => __('Enable this email notification.', 'wc-multivendor-marketplace'),
								'default' => 'yes'
						),
						'subject' => array(
								'title' => __('Subject', 'wc-multivendor-marketplace'),
								'type' => 'text',
								'description' => sprintf(__('This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'wc-multivendor-marketplace'), $this->get_default_subject()),
								'placeholder' => $this->get_default_subject(),
								'default' => ''
						),
						'heading' => array(
								'title' => __('Email Heading', 'wc-multivendor-marketplace'),
								'type' => 'text',
								'description' => sprintf(__('This controls the main heading contained within the email notification. Leave it blank to use the default heading: <code>%s</code>.', 'wc-multivendor-marketplace'), $this->get_default_heading()),
								'placeholder' => $this->get_default_heading(),
								'default' => ''
						),
						'email_type' => array(
								'title' => __('Email Type', 'wc-multivendor-marketplace'),
								'type' => 'select',
								'description' => __('Choose which format of email to be sent.', 'wc-multivendor-marketplace'),
								'default' => 'html',
								'class' => 'email_type',
								'options' => array(
										'plain' => __('Plain Text', 'wc-multivendor-marketplace'),
										'html' => __('HTML', 'wc-multivendor-marketplace'),
										'multipart' => __('Multipart', 'wc-multivendor-marketplace'),
								)
						)
				);
		}

	}

endif;
