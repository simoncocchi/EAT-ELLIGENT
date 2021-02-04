<?php
/**
 * The Template for displaying product list.
 *
 * @package WCfM Markeplace Views Product Lists Geo locate
 *
 * For edit coping this to yourtheme/wcfm/product-geolocate
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $post;

$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
if ( ($wcfm_map_lib == 'google') && !$api_key ) return;
if( !apply_filters( 'wcfmmp_is_allow_product_list_map', true ) ) return;
?>

<div id="wcfmmp-product-list-map" class="wcfmmp-product-list-map"></div>

<script>
  $map_zoom    = <?php echo absint($map_zoom); ?>;
  $auto_zoom   = '<?php echo $auto_zoom; ?>';
  $per_row     = '';
	$per_page    = '-1';
	$includes    = '<?php echo implode(",", $includes ); ?>';
	$excludes    = '';
	$has_product = 'yes';
	$sidebar     = 'no';
	$has_orderby = '';
	$theme       = '';
</script>