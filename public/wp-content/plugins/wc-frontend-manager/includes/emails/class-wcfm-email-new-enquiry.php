<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('WCFM_Email_New_enquiry')) :

	class WCFM_Email_New_enquiry extends WC_Email {
		
		public $enquiry;
		public $enquiry_id;
		public $enquiry_for;
		public $enquiry_url;
		public $additional_info;
		
		
		/**
		 * Constructor
		 */
		function __construct( $wcfm_email, $wcfm_email_label ) {
			global $WCFM, $WCFMmp;
			$this->id = $wcfm_email;
			$this->title = 'WCFM - ' . $wcfm_email_label;
			$this->description = __('New Inquiry notification emails are sent when new inquiry raised by users.', 'wc-frontend-manager');

			$this->heading = $wcfm_email_label;
			$this->subject = '[{site_title}] ' . __( 'New enquiry for', 'wc-frontend-manager' ) . ' - {enquiry_for_label}';

			$this->template_html = 'emails/'.$wcfm_email.'.php';
			$this->template_plain = 'emails/plain/'.$wcfm_email.'.php';
			$this->template_base = $WCFM->plugin_path . 'views/';
			
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
			return apply_filters( 'wcfm_' . str_replace( '-', '_', $this->id ) . '_email_subject', $this->subject, $this->object );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return apply_filters( 'wcfm_' . str_replace( '-', '_', $this->id ) . '_email_heading', $this->heading, $this->object );
		}

		/**
		 * trigger function.
		 *
		 * @access public
		 * @return void
		 */
		function trigger( $args ) {
			global $WCFM;
			
			if( !empty( $args ) ) {
				$defaults = array(
					'enquiry_id'      => '',
					'product_id'      => '',
					'vendor_id'       => '',
					'enquiry'         => '',
					'additional_info' => '',
					'customer_name'   => '',
					'customer_email'  => '',
					'is_admin'        => false,
				);

				$args = wp_parse_args( $args, $defaults );
				
				$enquiry_id      = $args['enquiry_id'];
				$product_id      = $args['product_id'];
				$vendor_id       = $args['vendor_id'];
				$enquiry         = $args['enquiry'];
				$additional_info = $args['additional_info'];
				$customer_name   = $args['customer_name'];
				$customer_email  = $args['customer_email'];
				$is_admin        = $args['is_admin'];
			
				if( !$enquiry_id ) return;
				if( !$this->is_enabled() ) return;
				
				$enquiry_for_label =  __( 'Store', 'wc-frontend-manager' );
				if( $vendor_id ) $enquiry_for_label = wcfm_get_vendor_store_name( $vendor_id ) . ' ' . __( 'Store', 'wc-frontend-manager' );
				if( $product_id ) $enquiry_for_label = get_the_title( $product_id );
				
				$enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_url() . '">' . __( 'Store', 'wc-frontend-manager' ) . '</a>';
				if( $vendor_id ) $enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_url() . '">' . wcfm_get_vendor_store_name( $vendor_id ) . ' ' . apply_filters( 'wcfm_sold_by_label', $vendor_id, __( 'Store', 'wc-frontend-manager' ) ) . '</a>';
				if( $product_id ) $enquiry_for = '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_enquiry_url() . '">' . get_the_title( $product_id ) . '</a>';
				
				$this->object       = $enquiry_id;
	
				$this->find[]       = '{enquiry_for_label}';
				$this->replace[]    = $enquiry_for_label;
	
				$this->enquiry          = $enquiry;
				$this->enquiry_id       = $enquiry_id;
				$this->enquiry_for      = $enquiry_for;
				$this->enquiry_url      = get_wcfm_enquiry_manage_url( $enquiry_id );
				$this->additional_info  = $additional_info;
				
				if( $is_admin ) {
					$this->recipient    = apply_filters( 'wcfm_admin_email_notification_receiver', get_bloginfo( 'admin_email' ), 'enquiry' );
				} elseif( $vendor_id ) {
					$this->recipient    = wcfm_get_vendor_store_email_by_vendor( $vendor_id );
				}
				
				if ( !$this->get_recipient() ) {
					return;
				}
				
				if( apply_filters( 'wcfm_is_allow_enquiry_by_customer', true ) ) {
					if( $is_admin || ( $vendor_id && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'view_email' ) ) ) {
						$headers[] = 'Reply-to: ' . $customer_name . ' <' . $customer_email . '>';
					} else {
						$headers = $this->get_headers();
					}
				} else {
					$headers = $this->get_headers();
				}
				
				// Filter to add Group Managers in CC
				if( !$is_admin && $vendor_id ) {
					$headers = apply_filters( 'wcfmmp_store_new_order_email_header', $headers, $vendor_id );
				}
				
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $headers, $this->get_attachments() );
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
																															'email_heading'   => $this->get_heading(),
																															'blogname'        => $this->get_blogname(),
																															'enquiry'         => $this->enquiry,
																															'enquiry_id'      => $this->enquiry_id,
																															'enquiry_for'     => $this->enquiry_for,
																															'enquiry_url'     => $this->enquiry_url,
																															'additional_info' => $this->additional_info,
																															'sent_to_admin'   => false,
																															'plain_text'      => false,
																															'email'           => $this
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
																															'email_heading'   => $this->get_heading(),
																															'blogname'        => $this->get_blogname(),
																															'enquiry'         => $this->enquiry,
																															'enquiry_id'      => $this->enquiry_id,
																															'enquiry_for'     => $this->enquiry_for,
																															'enquiry_url'     => $this->enquiry_url,
																															'additional_info' => $this->additional_info,
																															'sent_to_admin'   => false,
																															'plain_text'      => false,
																															'email'           => $this
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
								'title' => __('Enable/Disable', 'wc-frontend-manager'),
								'type' => 'checkbox',
								'label' => __('Enable this email notification.', 'wc-frontend-manager'),
								'default' => 'yes'
						),
						'subject' => array(
								'title' => __('Subject', 'wc-frontend-manager'),
								'type' => 'text',
								'description' => sprintf(__('This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'wc-frontend-manager'), $this->get_default_subject()),
								'placeholder' => $this->get_default_subject(),
								'default' => ''
						),
						'heading' => array(
								'title' => __('Email Heading', 'wc-frontend-manager'),
								'type' => 'text',
								'description' => sprintf(__('This controls the main heading contained within the email notification. Leave it blank to use the default heading: <code>%s</code>.', 'wc-frontend-manager'), $this->get_default_heading()),
								'placeholder' => $this->get_default_heading(),
								'default' => ''
						),
						'email_type' => array(
								'title' => __('Email Type', 'wc-frontend-manager'),
								'type' => 'select',
								'description' => __('Choose which format of email to be sent.', 'wc-frontend-manager'),
								'default' => 'html',
								'class' => 'email_type',
								'options' => array(
										'plain' => __('Plain Text', 'wc-frontend-manager'),
										'html' => __('HTML', 'wc-frontend-manager'),
										'multipart' => __('Multipart', 'wc-frontend-manager'),
								)
						)
				);
		}

	}

endif;
