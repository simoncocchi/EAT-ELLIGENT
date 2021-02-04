<?php
/**
 * WCFM plugin shortcode
 *
 * Plugin Shortcode output
 *
 * @author 		WC Lovers
 * @package 	wcfm/includes/shortcode
 * @version   1.0.0
 */
 
class WCFM_Enquiry_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the Enquiry shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	static public function output( $attr ) {
		global $WCFM, $wp, $WCFM_Query, $post;
		$WCFM->nocache();
		
		if( !apply_filters( 'wcfm_is_pref_enquiry', true ) ) return;
		
		$wcfm_options = $WCFM->wcfm_options;
		
		$ask_question_label  = isset( $wcfm_options['wcfm_enquiry_button_label'] ) ? $wcfm_options['wcfm_enquiry_button_label'] : __( 'Ask a Question', 'wc-frontend-manager' );
		if ( isset( $attr['label'] ) && !empty( $attr['label'] ) ) { $ask_question_label = $attr['label']; } 
		
		$product_id = 0;
		if ( isset( $attr['product'] ) && !empty( $attr['product'] ) ) { $product_id = absint($attr['product']); }
		if( !$product_id && $post && is_object( $post ) ) {
			$product_id = $post->ID;
		}
		
		$vendor_id  = 0;
		if ( isset( $attr['store'] ) && !empty( $attr['store'] ) ) { $vendor_id = absint($attr['store']); }
		if( !$vendor_id && ( function_exists( 'wcfmmp_is_store_page' ) && wcfmmp_is_store_page() ) ) {
			$vendor_id = get_query_var( 'author' );
		}
		
		if( !$vendor_id && is_single() && $post && is_object( $post ) && wcfm_is_vendor( $post->post_author ) ) {
			$vendor_id = $post->post_author;
		}
		
		
		$button_style = '';
		$background_color = '';
		$color = '';
		$hover_color = '';
		$hover_text_color = '#ffffff';
		$alignment = '';
		
		$wcfm_store_color_settings = get_option( 'wcfm_store_color_settings', array() );
		
		if ( isset( $attr['background'] ) && !empty( $attr['background'] ) ) { $background_color = $attr['background']; }
		if( $background_color ) { $button_style .= 'background: ' . $background_color . ';border-bottom-color: ' . $background_color . ';'; }
		else {
			if( !empty( $wcfm_store_color_settings ) ) {
				if( isset( $wcfm_store_color_settings['button_bg'] ) ) { $button_style .= 'background: ' . $wcfm_store_color_settings['button_bg'] . ';border-bottom-color: ' . $wcfm_store_color_settings['button_bg'] . ';'; }
			} else {
				if( isset( $wcfm_options['wc_frontend_manager_button_background_color_settings'] ) ) { $button_style .= 'background: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';border-bottom-color: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';'; }
			}
		}
		if ( isset( $attr['color'] ) && !empty( $attr['color'] ) ) { $color = $attr['color']; }
		if( $color ) { $button_style .= 'color: ' . $color . ';'; }
		else {
			if( !empty( $wcfm_store_color_settings ) ) {
				if( isset( $wcfm_store_color_settings['button_text'] ) ) { $button_style .= 'color: ' . $wcfm_store_color_settings['button_text'] . ';'; }
			} else {
				if( isset( $wcfm_options['wc_frontend_manager_button_text_color_settings'] ) ) { $button_style .= 'color: ' . $wcfm_options['wc_frontend_manager_button_text_color_settings'] . ';'; }
			}
		}
		
		if ( isset( $attr['hover'] ) && !empty( $attr['hover'] ) ) { $hover_color = $attr['hover']; }
		else {
			if( !empty( $wcfm_store_color_settings ) ) {
				if( isset( $wcfm_store_color_settings['button_active_bg'] ) ) { $hover_color = $wcfm_store_color_settings['button_active_bg']; }
				if( isset( $wcfm_store_color_settings['button_active_text'] ) ) { $hover_text_color = $wcfm_store_color_settings['button_active_text']; }
			} else {
				if( isset( $wcfm_options['wc_frontend_manager_base_highlight_color_settings'] ) ) { $hover_color = $wcfm_options['wc_frontend_manager_base_highlight_color_settings']; }
			}
		}
		
		if ( isset( $attr['align'] ) && !empty( $attr['align'] ) ) { $button_style .= 'float: ' . $attr['align'] . ';'; }
		
		$button_class = '';
		if( !is_user_logged_in() && apply_filters( 'wcfm_is_allow_enquiry_with_login', false ) ) { $button_class = ' wcfm_login_popup'; }
		?>
		<div class="wcfm_ele_wrapper wcfm_enquiry_widget">
			<div class="wcfm-clearfix"></div>
			<a href="#" class="wcfm_catalog_enquiry <?php echo $button_class; ?>" data-store="<?php echo $vendor_id; ?>" data-product="<?php echo $product_id; ?>" style="<?php echo $button_style; ?>"><span class="wcfmfa fa-question-circle"></span>&nbsp;&nbsp;<span class="add_enquiry_label"><?php _e( $ask_question_label, 'wc-frontend-manager' ); ?></span></a>
			<?php if( $hover_color ) { ?>
				<style>a.wcfm_catalog_enquiry:hover{background: <?php echo $hover_color; ?> !important;background-color: <?php echo $hover_color; ?> !important;border-bottom-color: <?php echo $hover_color; ?> !important;color: <?php echo $hover_text_color; ?> !important;}</style>
			<?php } ?>
			<div class="wcfm-clearfix"></div><br />
		</div>
		<?php
	}
}