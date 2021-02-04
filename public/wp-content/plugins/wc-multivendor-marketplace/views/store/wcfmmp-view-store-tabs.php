<?php
/**
 * The Template for displaying store tabs.
 *
 * @package WCfM Markeplace Views Store
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$store_tabs = $store_user->get_store_tabs();

?>

<?php do_action( 'wcfmmp_store_before_tabs', $store_user->get_id() ); ?>

<div id="tab_links_area" class="tab_links_area">
	<ul class="tab_links">
	  <?php foreach( $store_tabs as $store_tab_key => $store_tab_label ) { ?>
	  	<li class="<?php if( $store_tab_key == $store_tab ) echo 'active'; ?>"><a href="<?php echo $store_user->get_store_tabs_url( $store_tab_key ); ?>/#tab_links_area"><?php echo $store_tab_label; ?></a></li>
	  <?php } ?>
	</ul>
</div>
<div class="wcfm-clearfix"></div>

<?php do_action( 'wcfmmp_store_after_tabs', $store_user->get_id() ); ?>