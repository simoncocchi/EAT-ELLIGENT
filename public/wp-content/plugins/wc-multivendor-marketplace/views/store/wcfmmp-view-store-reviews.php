<?php
/**
 * The Template for displaying all store reviews.
 *
 * @package WCfM Markeplace Views Store
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $post;

if( $post ) {
	$pagination_base = str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) );
} else {
	$pagination_base = str_replace( 1, '%#%', esc_url( get_pagenum_link( 1 ) ) );
}

$paged  = max( 1, get_query_var( 'paged' ) );
$length = 10;
$offset = ( $paged - 1 ) * $length;

$wcfm_review_categories = get_wcfm_marketplace_active_review_categories();

$total_review_count = $store_user->get_total_review_count();
$latest_reviews     = $store_user->get_lastest_reviews( $offset, $length );
?>

<div class="_area" id="reviews">

  <?php do_action( 'wcfmmp_store_before_reviews', $store_user->get_id() ); ?>
  
  <?php do_action( 'wcfmmp_store_before_new_review', $store_user->get_id() ); ?>

  <?php
  // New Review form
  if( apply_filters( 'wcfm_is_allow_new_review', true, $store_user->get_id() ) ) {
  	$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-new.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'wcfm_review_categories' => $wcfm_review_categories ) );
  }
  ?>
  
  <?php do_action( 'wcfmmp_store_after_new_review', $store_user->get_id() ); ?>
			
	<div class="reviews_area">
		<div class="reviews_heading"><?php _e( 'reviews', 'wc-multivendor-marketplace' ); ?></div>
		
		<div class="recent_reviews">
		
		  <?php do_action( 'wcfmmp_store_before_review_stat', $store_user->get_id() ); ?>
			
			<?php
			// Reviews latest stat
			$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-latest-stat.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'total_review_count' => $total_review_count, 'latest_reviews' => $latest_reviews ) );
			?>
			
			<?php do_action( 'wcfmmp_store_after_review_stat', $store_user->get_id() ); ?>
					
			<div class="bd_rating_sec">
			
				<?php do_action( 'wcfmmp_store_before_rating', $store_user->get_id() ); ?>
				
				<?php
				// Review category ratings
				$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-category-ratings.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'wcfm_review_categories' => $wcfm_review_categories ) );
				?>
				
				<?php
				// Review total rating
				$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-ratings.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
				?>
				
				<?php do_action( 'wcfmmp_store_after_rating', $store_user->get_id() ); ?>
				
				<div class="spacer"></div>    
			</div>
								
			<div class="bd_review_section">
			  <?php do_action( 'wcfmmp_store_before_latest_reviews', $store_user->get_id() ); ?>
			  
			  <?php
				// Reviews latest review
				$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-latest-review.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'total_review_count' => $total_review_count, 'latest_reviews' => $latest_reviews ) );
				
				if( $total_review_count > $length ) {
					$num_of_pages = ceil( $total_review_count / $length );

					$args = array(
							'paged'           => $paged,
							'pagination_base' => $pagination_base,
							'num_of_pages'    => $num_of_pages,
					);
					$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-pagination.php', $args );
				}
				?>
				
				<?php do_action( 'wcfmmp_store_after_latest_reviews', $store_user->get_id() ); ?>
			</div>
		</div>
	</div>
	
	<?php do_action( 'wcfmmp_store_after_reviews', $store_user->get_id() ); ?>
	
</div>