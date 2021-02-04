<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WCfM Markeplace Views Store review category ratings
 *
 * For edit coping this to yourtheme/wcfm/reviews 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$category_review_rating = $store_user->get_category_review_rating();

if( !apply_filters( 'wcfm_is_allow_review_rating', true ) ) return;
?>

<?php if( $category_review_rating && !empty( $category_review_rating ) && is_array( $category_review_rating ) ) { ?>
	<div class="bd_rating_area lft">
		<?php foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) { ?>
			<div class="rating_box">
				<i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i> 
				<span>
					<?php
					$avg_category_review_rating = 0;
					if( isset( $category_review_rating[$wcfm_review_cat_key] ) && isset( $category_review_rating[$wcfm_review_cat_key]['avg'] ) ) {
						$avg_category_review_rating  = $category_review_rating[$wcfm_review_cat_key]['avg'];
					}
					echo wc_format_decimal( $avg_category_review_rating, 1 );
					?>
					<?php _e( $wcfm_review_category['category'], 'wc-multivendor-marketplace' ); ?>
				</span>
				<input type="hidden" class="store_rating_value" name="wcfm_saved_store_review_category[<?php echo $wcfm_review_cat_key; ?>]" value="<?php echo round($avg_category_review_rating); ?>" />
			</div>
		<?php } ?>
	</div>
<?php } ?>