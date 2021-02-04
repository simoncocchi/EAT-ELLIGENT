<?php
/**
 * The Template for displaying all store description.
 *
 * @package WCfM Markeplace Views Description
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$wcfm_shop_description = apply_filters( 'wcfmmp_store_about', apply_filters( 'woocommerce_short_description', $store_user->get_shop_description() ), $store_user->get_shop_description() );

?>

<div class="_area" id="wcfmmp_store_about">
	<div class="wcfmmp-store-description">
	 
	  <?php do_action( 'wcfmmp_store_before_about', $store_user->get_id() ); ?>
	
		<?php if( $wcfm_shop_description ) { ?>
			<div class="wcfm-store-about">
				<div class="wcfm_store_description" ><?php echo $wcfm_shop_description; ?></div>
			</div>
		<?php } ?>
		
		<?php do_action( 'wcfmmp_store_after_about', $store_user->get_id() ); ?>
		
	</div>
</div>