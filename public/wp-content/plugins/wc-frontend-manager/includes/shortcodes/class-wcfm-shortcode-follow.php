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
 
class WCFM_Follow_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the Follow shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	static public function output( $attr ) {
		global $WCFM, $WCFMu, $wp, $WCFM_Query, $post;
		$WCFM->nocache();
		
		if( !apply_filters( 'wcfm_is_pref_vendor_followers', true ) ) return;
		
		$wcfm_options = $WCFM->wcfm_options;
		
		$follow_button_label  = __( 'Follow Me', 'wc-frontend-manager' );
		if ( isset( $attr['label'] ) && !empty( $attr['label'] ) ) { $follow_button_label = $attr['label']; } 
		
		$vendor_id  = 0;
		if ( isset( $attr['store'] ) && !empty( $attr['store'] ) ) { $vendor_id = absint($attr['store']); }
		if( !$vendor_id && ( function_exists( 'wcfmmp_is_store_page' ) && wcfmmp_is_store_page() ) ) {
			$vendor_id = get_query_var( 'author' );
		}
		if( !$vendor_id && is_single() && $post && is_object( $post ) && wcfm_is_vendor( $post->post_author ) ) {
			$product_id = $post->ID;
			$vendor_id = $post->post_author;
		}
		
		if( !$vendor_id ) return;
		
		$followers = 0;
		$followers_arr = get_user_meta( $vendor_id, '_wcfm_followers_list', true );
		if( $followers_arr && is_array( $followers_arr ) ) {
			$followers = count( $followers_arr );
		}

		$user_id = 0;
		$is_following = false;
		if( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$user_following_arr = get_user_meta( $user_id, '_wcfm_following_list', true );
			if( $user_id == $vendor_id ) $is_following = true;
			if( $user_following_arr && is_array( $user_following_arr ) && in_array( $vendor_id, $user_following_arr ) ) {
				$is_following = true;
			}
		}	
		
		if( !$user_id || $is_following ) return;
		
		$button_style = '';
		$background_color = '';
		$color = '';
		$base_color = '';
		$alignment = '';
		
		if ( isset( $attr['background'] ) && !empty( $attr['background'] ) ) { $background_color = $attr['background']; }
		if( $background_color ) { $button_style .= 'background: ' . $background_color . ';border-bottom-color: ' . $background_color . ';'; }
		elseif( isset( $wcfm_options['wc_frontend_manager_button_background_color_settings'] ) ) { $button_style .= 'background: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';border-bottom-color: ' . $wcfm_options['wc_frontend_manager_button_background_color_settings'] . ';'; }
		if ( isset( $attr['color'] ) && !empty( $attr['color'] ) ) { $color = $attr['color']; }
		if( $color ) { $button_style .= 'color: ' . $color . ';'; }
		elseif( isset( $wcfm_options['wc_frontend_manager_button_text_color_settings'] ) ) { $button_style .= 'color: ' . $wcfm_options['wc_frontend_manager_button_text_color_settings'] . ';'; }
		
		if ( isset( $attr['hover'] ) && !empty( $attr['hover'] ) ) { $base_color = $attr['hover']; }
		elseif( isset( $wcfm_options['wc_frontend_manager_base_highlight_color_settings'] ) ) { $base_color = $wcfm_options['wc_frontend_manager_base_highlight_color_settings']; }
		
		if ( isset( $attr['align'] ) && !empty( $attr['align'] ) ) { $button_style .= 'float: ' . $attr['align'] . ';'; }
		
		?>
		<div class="wcfm_ele_wrapper wcfm_follow_widget">
			<div class="wcfm-clearfix"></div>
			<a href="#" class="wcfm_follow_me" data-count="<?php echo $followers; ?>" data-vendor_id="<?php echo $vendor_id; ?>" data-user_id="<?php echo $user_id; ?>" style="<?php echo $button_style; ?>"><span class="wcfmfa fa-child"></span>&nbsp;&nbsp;<span class="add_enquiry_label wcfm_follow_me_lable"><?php echo $follow_button_label; ?></span></a>
			<?php if( $base_color ) { ?>
				<style>a.wcfm_catalog_enquiry:hover{background: <?php echo $base_color; ?> !important;border-bottom-color: <?php echo $base_color; ?> !important;}</style>
			<?php } ?>
			<div class="wcfm-clearfix"></div><br />
			<script>
				jQuery(document).ready(function($) {
					$('.wcfm_follow_me').each(function() {
						$(this).click(function(event) {
							event.preventDefault();
							
							$wcfm_follow_me = $(this);
							$user_id   = $(this).data('user_id');
							$vendor_id = $(this).data('vendor_id');
							$count     = $(this).data('count');
							
							$wcfm_follow_me.block({
								message: null,
								overlayCSS: {
									background: '#fff',
									opacity: 0.6
								}
							});
							var data = {
								action    : 'wcfmu_vendors_followers_update',
								user_id   : $user_id,
								vendor_id : $vendor_id,
								count     : $count
							}	
							$.post(wcfm_params.ajax_url, data, function(response) {
								if(response) {
									$count = $count + 1;
									//$('.wcfm_followers_count').text( $count );
									$wcfm_follow_me.hide();
								}
							});
							
							return false;
						});
					});
				});
			</script>
			<div class="wcfm-clearfix"></div>
		</div>
		<?php
	}
}