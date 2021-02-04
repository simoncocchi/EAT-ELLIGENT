<?php
/**
 * The Template for displaying all store articles.
 *
 * @package WCfM Markeplace Views Store/articles
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$counter = 0;
?>

<?php do_action( 'wcfmmp_store_before_articles', $store_user->get_id() ); ?>

<div class="" id="articles">
	<div class="article_area">
	
		<?php do_action( 'wcfmmp_before_store_article', $store_user->get_id(), $store_info ); ?>
	
		<?php if ( have_posts() ) { ?>
			
			<?php do_action( 'wcfmmp_before_store_article_loop', $store_user->get_id(), $store_info ); ?>
	
			
			<?php do_action( 'wcfmmp_after_store_article_loop_start', $store_user->get_id(), $store_info ); ?>
			
			<?php while ( have_posts() ) { the_post(); ?>
				
				<?php do_action( 'wcfmmp_store_article_loop_in_before', $store_user->get_id(), $store_info, $counter ); ?>

				<?php do_action( 'wcfmmp_store_article_template' ); ?>
				
				<?php do_action( 'wcfmmp_store_article_loop_in_after', $store_user->get_id(), $store_info, $counter ); ?>
				
				<?php $counter++; ?>

			<?php }  ?>
					
			<?php do_action( 'wcfmmp_before_store_article_loop_end', $store_user->get_id(), $store_info ); ?>
					
			<?php do_action( 'wcfmmp_after_store_article_loop', $store_user->get_id(), $store_info ); ?>
			
			<?php do_action( 'wcfmmp_woocommerce_after_shop_loop_before', $store_user->get_id(), $store_info ); ?>
			<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			<?php do_action( 'wcfmmp_woocommerce_after_shop_loop_after', $store_user->get_id(), $store_info ); ?>
			
			<?php //wcfmmp_content_nav( 'nav-below' ); ?>
	
		<?php } else { ?>
			<?php do_action( 'wcfmmp_store_article_template_none' ); ?>
		<?php } ?>
		
		<?php do_action( 'wcfmmp_after_store_article', $store_user->get_id(), $store_info ); ?>
		
	</div><!-- #articles -->
</div><!-- .article_area -->

<?php do_action( 'wcfmmp_store_after_articles', $store_user->get_id() ); ?>