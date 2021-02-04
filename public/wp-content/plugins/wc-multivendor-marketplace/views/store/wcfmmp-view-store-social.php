<?php
/**
 * The Template for displaying all store social
 *
 * @package WCfM Markeplace Views Store Social
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;
?>

<ul>
	<?php do_action( 'wcfmmp_store_before_social', $store_user->get_id() ); ?>
	
	<?php if( isset( $store_info['social']['fb'] ) && !empty( $store_info['social']['fb'] ) ) { ?>
		<li><a href="<?php echo wcfmmp_generate_social_url( $store_info['social']['fb'], 'facebook' ); ?>" target="_blank"><i class="fab fa-facebook-f" aria-hidden="true"></i></a></li>
	<?php } ?>
	<?php if( isset( $store_info['social']['twitter'] ) && !empty( $store_info['social']['twitter'] ) ) { ?>
		<li><a href="<?php echo wcfmmp_generate_social_url( $store_info['social']['twitter'], 'twitter' ); ?>" target="_blank"><i class="fab fa-twitter" aria-hidden="true" target="_blank"></i></a></li>
	<?php } ?>
	<?php if( isset( $store_info['social']['linkedin'] ) && !empty( $store_info['social']['linkedin'] ) ) { ?>
		<li><a href="<?php echo wcfmmp_generate_social_url( $store_info['social']['linkedin'], 'linkedin' ); ?>" target="_blank"><i class="fab fa-linkedin-in" aria-hidden="true" target="_blank"></i></a></li>
	<?php } ?>
	<?php if( isset( $store_info['social']['instagram'] ) && !empty( $store_info['social']['instagram'] ) ) { ?>
		<li><a href="<?php echo wcfmmp_generate_social_url( $store_info['social']['instagram'], 'instagram' ); ?>" target="_blank"><i class="fab fa-instagram" aria-hidden="true" target="_blank"></i></a></li>
	<?php } ?>
	<?php if( isset( $store_info['social']['pinterest'] ) && !empty( $store_info['social']['pinterest'] ) ) { ?>
		<li><a href="<?php echo wcfmmp_generate_social_url( $store_info['social']['pinterest'], 'pinterest' ); ?>" target="_blank"><i class="fab fa-pinterest" aria-hidden="true" target="_blank"></i></a></li>
	<?php } ?>
	<?php if( isset( $store_info['social']['youtube'] ) && !empty( $store_info['social']['youtube'] ) ) { ?>
		<li><a href="<?php echo wcfmmp_generate_social_url( $store_info['social']['youtube'], 'youtube' ); ?>" target="_blank"><i class="fab fa-youtube" aria-hidden="true" target="_blank"></i></a></li>
	<?php } ?>
	<?php if( isset( $store_info['social']['snapchat'] ) && !empty( $store_info['social']['snapchat'] ) ) { ?>
		<li><a href="<?php echo wcfmmp_generate_social_url( $store_info['social']['snapchat'], 'snapchat' ); ?>" target="_blank"><i class="fab fa-snapchat" aria-hidden="true" target="_blank"></i></a></li>
	<?php } ?>

  <?php do_action( 'wcfmmp_store_after_social', $store_user->get_id() ); ?>
</ul>
							