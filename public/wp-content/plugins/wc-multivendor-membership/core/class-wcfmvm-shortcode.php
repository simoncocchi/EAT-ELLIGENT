<?php
/**
 * WCFM plugin core
 *
 * Plugin shortcode
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/core
 * @version   1.0.0
 */
 
class WCFMvm_Shortcode {

	public $list_product;

	public function __construct() {
		// WC Frontend Manager Membership Shortcode
		add_shortcode('wcfm_vendor_membership', array(&$this, 'wcfm_vendor_membership'));
		
		// WC Frontend Manager Registration Shortcode
		add_shortcode('wcfm_vendor_registration', array(&$this, 'wcfm_vendor_registraion'));
		
		// WC Frontend Manager Subscribe Button Shortcode
		add_shortcode('wcfmvm_subscribe', array(&$this, 'wcfmvm_subscribe'));
	}

	public function wcfm_vendor_membership($attr) {
		global $WCFM;
		$this->load_class('vendor-membership');
		return $this->shortcode_wrapper(array('WCFM_Vendor_Membership_Shortcode', 'output'));
	}
	
	public function wcfm_vendor_registraion($attr) {
		global $WCFM;
		$this->load_class('vendor-registration');
		return $this->shortcode_wrapper(array('WCFM_Vendor_Registration_Shortcode', 'output'));
	}
	
	public function wcfmvm_subscribe( $attr ) {
		global $WCFM, $WCFMvm;
		
		if( is_admin() ) return;
		
		if( !apply_filters( 'wcfm_is_pref_membership', true ) ) return;
		
		if( current_user_can( 'administrator' ) ) {
			ob_start();
			_e( 'Kindly logout from Admin account to have "Subscribe Now" button.', 'wc-multivendor-membership' );
			return ob_get_clean();
		}
		
		if( !wcfm_is_allowed_membership() ) return;
		
		$membership_id = 0;
		if ( isset( $attr['id'] ) && !empty( $attr['id'] ) ) { $membership_id = $attr['id']; }
		if( !$membership_id ) return;
		
		$current_plan = wcfm_get_membership();
		if( $current_plan && ( $current_plan == $membership_id ) ) {
			?>
			<h4 class="wcfm_membership_your_plan_label"><?php _e( 'Your Plan', 'wc-multivendor-membership' ); ?></h4>
			<?php
			return;
		}
		
		$subscribe_now_label = __( "Subscribe Now", 'wc-multivendor-membership' );
		if ( isset( $attr['subscribe_now'] ) && !empty( $attr['subscribe_now'] ) ) { 
		  $subscribe_now_label = $attr['subscribe_now']; 
		} elseif ( isset( $attr['label'] ) && !empty( $attr['label'] ) ) { 
		  $subscribe_now_label = $attr['label']; 
		} else {
			$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );

			$membership_type_settings = array();
			if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
			$default_subscribe_button = isset( $membership_type_settings['subscribe_button_label'] ) ? $membership_type_settings['subscribe_button_label'] : __( "Subscribe Now", 'wc-multivendor-membership' );
			
			$subscribe_now_label = get_post_meta( (int) $membership_id, 'subscribe_button_label', true );
    	if( !$subscribe_now_label ) $subscribe_now_label = $default_subscribe_button;
		}
		
		$background_color = $color = $style ='';
		if ( isset( $attr['background'] ) && !empty( $attr['background'] ) ) { $background_color = $attr['background']; }
		if( $background_color ) { $style .= 'background-color: ' . $background_color . ';'; }
		if ( isset( $attr['color'] ) && !empty( $attr['color'] ) ) { $color = $attr['color']; }
		if( $color ) { $style .= 'color: ' . $color . ';'; }
		
		ob_start();
		?>
		<div class="wcfm_ele_wrapper wcfm_membership_subscribe_button_wrapper">
		  <input class="wcfm_membership_subscribe_button wcfm_submit_button button" type="button" data-membership="<?php echo $membership_id; ?>" style="<?php echo $style; ?>" value="<?php _e( $subscribe_now_label, 'wc-multivendor-membership' ); ?>">
		</div>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Shortcode Wrapper
	 *
	 * @access public
	 * @param mixed $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public function shortcode_wrapper($function, $atts = array()) {
		ob_start();
		call_user_func($function, $atts);
		return ob_get_clean();
	}

	/**
	 * Shortcode CLass Loader
	 *
	 * @access public
	 * @param mixed $class_name
	 * @return void
	 */
	public function load_class($class_name = '') {
		global $WCFMvm;
		if ('' != $class_name && '' != $WCFMvm->token) {
			include_once ( $WCFMvm->plugin_path . 'includes/shortcodes/class-' . esc_attr($WCFMvm->token) . '-shortcode-' . esc_attr($class_name) . '.php' );
		}
	}

}
?>