<?php
/**
 * The Template for displaying product list radius search form.
 *
 * @package WCfM Markeplace Views Product List Search Form
 *
 * For edit coping this to yourtheme/wcfm/product-geolocate
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $post, $wp;


if ( '' === get_option( 'permalink_structure' ) ) {
	$form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
} else {
	$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
}

$max_radius_to_search = isset( $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] ) ? $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] : '100';

$radius_unit = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';

$radius_addr = isset( $_GET['radius_addr'] ) ? wc_clean( $_GET['radius_addr'] ) : '';
$radius_range = isset( $_GET['radius_range'] ) ? wc_clean( $_GET['radius_range'] ) : (absint(apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ))/apply_filters( 'wcfmmp_radius_filter_start_distance', 10));
$radius_lat = isset( $_GET['radius_lat'] ) ? wc_clean( $_GET['radius_lat'] ) : '';
$radius_lng = isset( $_GET['radius_lng'] ) ? wc_clean( $_GET['radius_lng'] ) : '';



?>

<form role="search" method="get" class="wcfmmp-product-geolocate-search-form" action="<?php echo esc_url( $form_action ); ?>">
  <?php do_action( 'wcfmmp_before_product_list_geo_locate_filter' ); ?>
  
	<div id="wcfm_radius_filter_container" class="wcfm_radius_filter_container">
		<input type="text" id="wcfmmp_radius_addr" name="radius_addr" class="wcfmmp-radius-addr" placeholder="<?php esc_attr_e( 'Insert your address ..', 'wc-multivendor-marketplace' ); ?>" value="<?php echo $radius_addr; ?>" />
		<i class="wcfmmmp_locate_icon" style="background-image: url(<?php echo $WCFMmp->plugin_url; ?>assets/images/locate.svg)"></i>
	</div>
	<div class="wcfm_radius_slidecontainer">
		<input class="wcfmmp_radius_range" name="radius_range" id="wcfmmp_radius_range" type="range" value="<?php echo $radius_range; ?>" min="0" max="<?php echo apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ); ?>" steps="6" />
		<span class="wcfmmp_radius_range_start">0</span>
		<span class="wcfmmp_radius_range_cur"><?php echo $radius_range; ?> <?php echo ucfirst( $radius_unit ); ?></span>
		<span class="wcfmmp_radius_range_end"><?php echo apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ); ?></span>
	</div>
	<input type="hidden" id="wcfmmp_radius_lat" name="radius_lat" value="<?php echo $radius_lat; ?>">
	<input type="hidden" id="wcfmmp_radius_lng" name="radius_lng" value="<?php echo $radius_lng; ?>">
	
	<button type="submit" class="button"><?php echo esc_html__( 'Filter', 'woocommerce' ); ?></button>
	
	<?php echo wc_query_string_form_fields( null, array( 'radius_addr', 'radius_range', 'radius_lat', 'radius_lng', 'paged' ), '', true ); ?>
	
	<?php do_action( 'wcfmmp_after_product_list_geo_locate_filter' ); ?>
</form>