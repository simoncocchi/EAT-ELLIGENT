<?php
/**
 * The Template for displaying store sidebar.
 *
 * @package WCfM Markeplace Views Store Lists Sidebar
 *
 * For edit coping this to yourtheme/wcfm/store-lists 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

if( !$WCFMmp->wcfmmp_vendor->is_store_lists_sidebar() ) return;

$widget_args = apply_filters( 'wcfmmp_store_lists_sidebar_args', array(
																																					'before_widget' => '<aside class="widget">',
																																					'after_widget'  => '</aside>',
																																					'before_title'  => '<div class="sidebar_heading"><h4 class="widget-title">',
																																					'after_title'   => '</h4></div>',
																																			) );

?>

<div id="wcfmmp-store-lists-sidebar" class="lft left_sidebar widget-area sidebar">

  <form role="search" method="get" class="wcfmmp-store-search-form" action="">

		<?php do_action( 'wcfmmp_store_lists_before_sidabar' ); ?>
		
		<?php if( !dynamic_sidebar( 'sidebar-wcfmmp-store-lists' ) ) { ?>
			
			<?php the_widget( 'WCFMmp_Store_Lists_Search', array( 'title' => __( 'Search', 'wc-multivendor-marketplace' ) ), $widget_args ); ?>
			
			<?php the_widget( 'WCFMmp_Store_Lists_Category_Filter', array( 'title' => __( 'Filter by Category', 'wc-multivendor-marketplace' ) ), $widget_args ); ?>
			
			<?php 
			if( $radius ) {
				the_widget( 'WCFMmp_Store_Lists_Radius_Filter', array( 'title' => __( 'Filter by Location', 'wc-multivendor-marketplace' ) ), $widget_args );
			} else {
			  the_widget( 'WCFMmp_Store_Lists_Location_Filter', array( 'title' => __( 'Filter by Location', 'wc-multivendor-marketplace' ) ), $widget_args );
			}
			?>
			
		<?php } else { ?>
			<?php //get_sidebar( 'store' ); ?>
		<?php } ?>
		
		<?php do_action( 'wcfmmp_store_lists_after_sidebar' ); ?>
		
		<input type="hidden" id="pagination_base" name="pagination_base" value="<?php echo $pagination_base ?>" />
		<input type="hidden" id="wcfm_paged" name="wcfm_paged" value="<?php echo $paged ?>" />
		<input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce( 'wcfmmp-stores-list-search' ); ?>" />
		<div class="wcfmmp-overlay" style="display: none;"><span class="wcfmmp-ajax-loader"></span></div>
	</form>
	
</div><!-- .left_sidebar -->