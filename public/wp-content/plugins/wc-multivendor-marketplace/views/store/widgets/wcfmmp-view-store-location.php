<?php
/**
 * The Template for displaying store sidebar location.
 *
 * @package WCfM Markeplace Views Store Sidebar Location
 *
 * For edit coping this to yourtheme/wcfm/store/widgets
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

?>

<div id="<?php echo $map_id; ?>" class="wcfmmp-store-map"></div>
<?php
	$WCFM->wcfm_fields->wcfm_generate_form_field( array(
		                                                  "store_address" => array( 'type' => 'hidden', 'class' => 'wcfm_store_address', 'value' => rawurlencode( $address ) ),
																											"store_lat"     => array( 'type' => 'hidden', 'class' => 'wcfm_store_lat', 'value' => $store_lat ),
																											"store_lng"     => array( 'type' => 'hidden', 'class' => 'wcfm_store_lng', 'value' => $store_lng ),
																											) );
?>