<?php
/**
 * WCFM plugin templates
 *
 * Header area
 *
 * @author 		WC Lovers
 * @package 	wcfm/templates/default
 * @version   3.1.2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !apply_filters( 'wcfm_is_allow_template_header', true ) ) return;
?>
<div id="wcfm-header" class="left-logo">
	<div class="wcfm-header-container">
		<div class="wcfm-header-content">
			<?php
			$blog_title = get_bloginfo( 'name' );
			$blog_link  = get_bloginfo( 'url' );
			?>
			<div class="wcfm-site-name">
			  <?php do_action( 'wcfm_dasboard_header_before' ); ?>
				<a href="<?php echo $blog_link; ?>"><?php echo $blog_title; ?></a>
				<?php do_action( 'wcfm_dasboard_header_after' ); ?>
			</div>
		</div>
	</div>
</div>
<div class="wcfm_clearfix"></div>