<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('WCFMvm_Email_Email_verification')) :

	class WCFMvm_Email_Email_verification extends WC_Email {
		
		public $user_email;
		public $verification_code;
		
		
		/**
		 * Constructor
		 */
		function __construct( $wcfm_email, $wcfm_email_label ) {
			global $WCFM, $WCFMvm;
			$this->id = $wcfm_email;
			$this->title = 'WCFM - ' . $wcfm_email_label;
			$this->description = __('Email verification emails are sent during vendor registertion.', 'wc-multivendor-membership');

			$this->heading = $wcfm_email_label;
			$this->subject = '[{site_title}] ' . __( 'Email Verification Code', 'wc-multivendor-membership' ) . ' - {verification_code}';

			$this->template_html = 'emails/'.$wcfm_email.'.php';
			$this->template_plain = 'emails/plain/'.$wcfm_email.'.php';
			$this->template_base = $WCFMvm->plugin_path . 'views/';
			
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
					'verification_code'  => '',
					'user_email'         => '',
					'is_admin'           => false,
				);

				$args = wp_parse_args( $args, $defaults );
				
				$user_email         = $args['user_email'];
				$verification_code  = $args['verification_code'];
			
				if( !$verification_code ) return;
				if( !$this->is_enabled() ) return;
				
				$this->object       = $verification_code;
	
				$this->find[]       = '{verification_code}';
				$this->replace[]    = $verification_code;
	
				$this->user_email            = $user_email;
				$this->verification_code     = $verification_code;
				
				$this->recipient    = $user_email;
				
				if ( !$this->get_recipient() ) {
					return;
				}
				
				$headers = $this->get_headers();
				
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
																															'email_heading'      => $this->get_heading(),
																															'blogname'           => $this->get_blogname(),
																															'user_email'         => $this->user_email,
																															'verification_code'  => $this->verification_code,
																															'plain_text'         => false,
																															'email'              => $this
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
																															'email_heading'      => $this->get_heading(),
																															'blogname'           => $this->get_blogname(),
																															'user_email'         => $this->user_email,
																															'verification_code'  => $this->verification_code,
																															'sent_to_admin'      => false,
																															'plain_text'         => false,
																															'email'              => $this
																															), 'wcfm/', $this->template_base);
		}

		/**
		 * Initialise Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		function init_form_fields() {
				global $WCFM, $WCFMvm;
				$this->form_fields = array(
						'enabled' => array(
								'title' => __('Enable/Disable', 'wc-multivendor-membership'),
								'type' => 'checkbox',
								'label' => __('Enable this email notification.', 'wc-multivendor-membership'),
								'default' => 'yes'
						),
						'subject' => array(
								'title' => __('Subject', 'wc-multivendor-membership'),
								'type' => 'text',
								'description' => sprintf(__('This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'wc-multivendor-membership'), $this->get_default_subject()),
								'placeholder' => $this->get_default_subject(),
								'default' => ''
						),
						'heading' => array(
								'title' => __('Email Heading', 'wc-multivendor-membership'),
								'type' => 'text',
								'description' => sprintf(__('This controls the main heading contained within the email notification. Leave it blank to use the default heading: <code>%s</code>.', 'wc-multivendor-membership'), $this->get_default_heading()),
								'placeholder' => $this->get_default_heading(),
								'default' => ''
						),
						'email_type' => array(
								'title' => __('Email Type', 'wc-multivendor-membership'),
								'type' => 'select',
								'description' => __('Choose which format of email to be sent.', 'wc-multivendor-membership'),
								'default' => 'html',
								'class' => 'email_type',
								'options' => array(
										'plain' => __('Plain Text', 'wc-multivendor-membership'),
										'html' => __('HTML', 'wc-multivendor-membership'),
										'multipart' => __('Multipart', 'wc-multivendor-membership'),
								)
						)
				);
		}

	}

endif;
