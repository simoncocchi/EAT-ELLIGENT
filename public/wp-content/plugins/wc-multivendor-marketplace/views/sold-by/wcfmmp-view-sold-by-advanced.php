<?php
/**
 * The Template for displaying store.
 *
 * @package WCfM Markeplace Views Store Sold By Advanced
 *
 * For edit coping this to yourtheme/wcfm/sold-by
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

if( empty($product_id) && empty($vendor_id) ) return;

if( empty($vendor_id) && $product_id ) {
	$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
}
if( !$vendor_id ) return;

if( !apply_filters( 'wcfmmp_is_allow_sold_by', true, $vendor_id ) || !wcfm_vendor_has_capability( $vendor_id, 'sold_by' ) ) return;

// Check is store Online
$is_store_offline = get_user_meta( $vendor_id, '_wcfm_store_offline', true );
if ( $is_store_offline ) {
	return;
}

$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( absint($vendor_id) );
if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) && !apply_filters( 'wcfmmp_is_allow_full_sold_by_linked', false ) ) {
	$store_name = wcfm_get_vendor_store( absint($vendor_id) );
} else {
	$store_name = wcfm_get_vendor_store_name( absint($vendor_id) );
}

$store_logo = wcfm_get_vendor_store_logo_by_vendor( $vendor_id );
if( !$store_logo ) {
	$store_logo = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp-blue.png' );
}
?>

<div class="wcfm-clearfix"></div>
<div class="wcfmmp_sold_by_container_advanced">
  
	<?php if( apply_filters( 'wcfmmp_is_allow_full_sold_by_linked', false ) && apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?><a href="<?php echo wcfmmp_get_store_url( $vendor_id ); ?>"><?php } ?>

  <div class="wcfmmp_sold_by_label">
		<?php 
		if( apply_filters( 'wcfmmp_is_allow_single_product_sold_by_label', true ) ) {
			echo $sold_by_text; 
		}	
		?>
		
		<?php 
		if( apply_filters( 'wcfmmp_is_allow_single_product_sold_by_badges', true ) ) {
			do_action('wcfmmp_single_product_sold_by_badges', $vendor_id );
		}
		?>
	</div>
			
  <div class="wcfmmp_sold_by_container_left">
    <?php if( apply_filters( 'wcfmmp_is_allow_single_product_sold_by_logo', true ) ) { ?>
    	<img src="<?php echo $store_logo; ?>" />
    <?php } ?>
  </div>
  <div class="wcfmmp_sold_by_container_right">
		<?php do_action('before_wcfmmp_sold_by_label_single_product', $vendor_id ); ?>
		
		<div class="wcfmmp_sold_by_wrapper">
			<div class="wcfmmp_sold_by_store"><?php echo $store_name; ?></div> 
		</div>
		
		<?php if( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) && apply_filters( 'wcfmmp_is_allow_single_product_sold_by_review', true ) ) { $WCFMmp->wcfmmp_reviews->show_star_rating( 0, $vendor_id ); } ?>
		
		<?php do_action('after_wcfmmp_sold_by_label_single_product', $vendor_id ); ?>
	</div>
	
	<?php if( apply_filters( 'wcfmmp_is_allow_full_sold_by_linked', false ) && apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?></a><?php } ?>
	
</div>
<div class="wcfm-clearfix"></div>