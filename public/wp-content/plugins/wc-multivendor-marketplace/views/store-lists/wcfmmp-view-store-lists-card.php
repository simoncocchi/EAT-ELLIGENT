<?php
/**
 * The Template for displaying store sidebar category.
 *
 * @package WCfM Markeplace Views Store List Card
 *
 * For edit coping this to yourtheme/wcfm/store-lists
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

if( !$store_id ) return;
if( !wcfm_is_vendor( $store_id ) ) return;

if( !apply_filters( 'wcfmmp_store_list_card_valid', $store_id ) ) return;

$is_store_offline = get_user_meta( $store_id, '_wcfm_store_offline', true );
if ( $is_store_offline ) return;

$is_disable_vendor = get_user_meta( $store_id, '_disable_vendor', true );
if ( $is_disable_vendor ) return;


$store_user      = wcfmmp_get_store( $store_id );
$store_info      = $store_user->get_shop_info();
$gravatar        = $store_user->get_avatar();
$banner_type     = $store_user->get_list_banner_type();
if( $banner_type == 'video' ) {
	$banner_video = $store_user->get_list_banner_video();
} else {
	$banner          = $store_user->get_list_banner();
	if( !$banner ) {
		$banner = !empty( $WCFMmp->wcfmmp_marketplace_options['store_list_default_banner'] ) ? wcfm_get_attachment_url($WCFMmp->wcfmmp_marketplace_options['store_list_default_banner']) : $WCFMmp->plugin_url . 'assets/images/default_banner.jpg';
		$banner = apply_filters( 'wcfmmp_list_store_default_bannar', $banner );
	}
}
$store_name      = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __( 'N/A', 'wc-multivendor-marketplace' );
$store_name      = apply_filters( 'wcfmmp_store_title', $store_name , $store_id );
$store_url       = wcfmmp_get_store_url( $store_id );
$store_address   = $store_user->get_address_string(); 
$store_description = $store_user->get_shop_description();
?>

<li class="wcfmmp-single-store item woocommerce coloum-<?php echo $per_row; ?>">
	<div class="store-wrapper">
		<div class="store-content">
		  <?php if( apply_filters( 'wcfmmp_is_allow_full_store_card_linked', false ) && apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?><a href="<?php echo $store_url; ?>"><?php } ?>
				<?php if( $banner_type == 'video' ) { ?>
					<div class="store-info"><?php echo preg_replace("/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i", "<iframe width=\"100%\" height=\"315\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" src=\"//www.youtube.com/embed/$2?iv_load_policy=3&enablejsapi=1&disablekb=1&autoplay=0&controls=0&showinfo=0&rel=0&loop=1&wmode=transparent&widgetid=1\" allowfullscreen></iframe>", $banner_video); ?></div>
				<?php } else { ?>
					<div class="store-info" style="background-image: url( '<?php echo $banner; ?>');"></div>
				<?php } ?>
			<?php if( apply_filters( 'wcfmmp_is_allow_full_store_card_linked', false ) && apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?></a><?php } ?>
		</div>
		<div class="store-footer">
		
			<div class="store-avatar lft">
				<img src="<?php echo $gravatar; ?>" alt="Logo"/>
			</div>
			
			<div class="store-data-container rgt">
				<div class="store-data">
					<h2>
					  <?php if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?>
					  	<a href="<?php echo $store_url; ?>"><?php echo $store_name; ?></a>
					  <?php } else { ?>
					  	<a href="#" onclick="return false;"><?php echo $store_name; ?></a>
					  <?php } ?>
					  <?php do_action( 'after_wcfmmp_store_list_rating', $store_id, $store_info ); ?>
				  </h2>
					
					<div class="bd_rating">
						<?php if( apply_filters( 'wcfm_is_allow_review_rating', true ) ) { $store_user->show_star_rating(); } ?>
						<div class="spacer"></div>
						<div class="spacer"></div>
					</div>
					
					<?php if ( $store_address && ( $store_info['store_hide_address'] == 'no' ) && wcfm_vendor_has_capability( $store_id, 'vendor_address' ) ): ?>
						<p class="store-address"><?php echo $store_address; ?></p>
					<?php endif ?>
					
					<?php if ( !empty( $store_user->get_email() ) && ( $store_info['store_hide_email'] == 'no' ) && wcfm_vendor_has_capability( $store_id, 'vendor_email' ) ) { ?>
						<p class="store-phone">
							<i class="wcfmfa fa-envelope" aria-hidden="true"></i> <?php echo esc_html( $store_user->get_email() ); ?>
						</p>
					<?php } ?>

					<?php if ( !empty( $store_info['phone'] ) && ( $store_info['store_hide_phone'] == 'no' ) && wcfm_vendor_has_capability( $store_id, 'vendor_phone' ) ) { ?>
						<p class="store-phone">
							<i class="wcfmfa fa-phone" aria-hidden="true"></i> <?php echo esc_html( $store_info['phone'] ); ?>
						</p>
					<?php } ?>
					<?php if ( apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) && apply_filters( 'wcfm_is_allow_store_list_distance', true ) ) {
					$distance = wcfmmp_get_user_vendor_distance( $store_id );	
					$radius_unit   = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';
					if( $distance ) {
						?>
						<p class="store-phone">
							<i class="wcfmfa fa-map-marker-alt" aria-hidden="true"></i> <?php echo $distance . ' ' . $radius_unit . ' ' . __( 'away', 'wc-multivendor-marketplace' ); ?>
						</p>
					<?php } 
					} ?>
					<?php if ( apply_filters( 'wcfm_is_allow_store_list_product_count', false ) ) {
					$total_products = wcfm_get_user_posts_count( $store_id, 'product', apply_filters( 'wcfm_limit_check_status', 'publish' ) );	
					?>
					<p class="store-phone">
						<i class="wcfmfa fa-cube" aria-hidden="true"></i> <?php echo $total_products . ' ' . __( 'products', 'wc-frontend-manager' ); ?>
					</p>
					<?php } ?>
					<?php if ( $store_description && apply_filters( 'wcfm_is_allow_store_list_about', false ) ) { ?>
						<p class="store-phone">
							<?php 
							$pos = strpos( $store_description, ' ', 100 );
							echo substr( $store_description, 0, $pos ) . '...'; 
							?>
						</p>
					<?php } ?>
					<?php do_action( 'wcfmmp_store_list_after_store_info', $store_id, $store_info ); ?>
					
					<div class="wcfm-clearfix"></div>
				</div>
			</div>
			<div class="spacer"></div>
			
			<?php if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) && apply_filters( 'wcfmmp_is_allow_visit_store_button', true ) ) { ?>
			  <a href="<?php echo $store_url; ?>" class="wcfmmp-visit-store"><?php _e( 'Visit <span>Store</span>', 'wc-multivendor-marketplace' ); ?></a>
			<?php } ?>
			
			<?php do_action( 'wcfmmp_store_list_footer', $store_id, $store_info ); ?>
		</div>
	</div>
</li>