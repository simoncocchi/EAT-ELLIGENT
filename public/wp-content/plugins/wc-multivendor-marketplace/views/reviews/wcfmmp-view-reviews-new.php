<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WCfM Markeplace Views Store new review form
 *
 * For edit coping this to yourtheme/wcfm/reviews 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$author_id = get_current_user_id();

$wp_user_avatar_id = get_user_meta( $author_id, 'wp_user_avatar', true );
$wp_user_avatar = wp_get_attachment_url( $wp_user_avatar_id );
if ( !$wp_user_avatar ) {
	$wp_user_avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
}

$is_start_with_full_rating = apply_filters( 'wcfm_add_review_start_with_full_rating', true );

$rating_block_class = '';
if( !apply_filters( 'wcfm_is_allow_review_rating', true ) ) { $rating_block_class = ' wcfm_custom_hide'; }
?>

  <div class="reviews_area reviews_add_area reviews_area_dummy">
		<div class="reviews_heading"><?php _e( 'write a review', 'wc-multivendor-marketplace' ); ?></div>
		<div class="add_review">
			<input name="" type="text" placeholder="<?php _e('your review', 'wc-multivendor-marketplace' ); ?>">
			<button><?php _e( 'Add Your Review', 'wc-multivendor-marketplace' ); ?></button>
		</div>
	</div>
           
  <div class="reviews_area reviews_add_area reviews_area_live">
		<div class="reviews_heading"><?php _e( 'write a review', 'wc-multivendor-marketplace' ); ?> <a class="cancel_review_add" href="#"><?php _e( 'Cancel', 'wc-multivendor-marketplace' ); ?></a></div>
		<div class="write_review">
			<div class="lft review_photo"><img src="<?php echo $wp_user_avatar; ?>" alt="Review"/></div>
			<div class="rgt review_text">
			  <form method="post" name="wcfmmp_store_review_form" id="wcfmmp_store_review_form">
					<div class="rating_area <?php echo $rating_block_class; ?>">
						<?php foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) { ?>
							<div class="rating_box rating-stars">
								<ul class='stars'>
									<li class='star <?php if( $is_start_with_full_rating ) echo "selected"; ?>' title="<?php _e('Poor', 'wc-multivendor-marketplace' ); ?>" data-value='1'>
										<i class='wcfmfa fa-star fa-fw'></i>
									</li>
									<li class='star <?php if( $is_start_with_full_rating ) echo "selected"; ?>' title="<?php _e('Fair', 'wc-multivendor-marketplace' ); ?>" data-value='2'>
										<i class='wcfmfa fa-star fa-fw'></i>
									</li>
									<li class='star <?php if( $is_start_with_full_rating ) echo "selected"; ?>' title="<?php _e('Good', 'wc-multivendor-marketplace' ); ?>" data-value='3'>
										<i class='wcfmfa fa-star fa-fw'></i>
									</li>
									<li class='star <?php if( $is_start_with_full_rating ) echo "selected"; ?>' title="<?php _e('Excellent', 'wc-multivendor-marketplace' ); ?>" data-value='4'>
										<i class='wcfmfa fa-star fa-fw'></i>
									</li>
									<li class='star <?php if( $is_start_with_full_rating ) echo "selected"; ?>' title="<?php _e('WOW!!!', 'wc-multivendor-marketplace' ); ?>" data-value='5'>
										<i class='wcfmfa fa-star fa-fw'></i>
									</li>
								</ul>
								<span><span class="rating_text"><?php if( $is_start_with_full_rating ) { echo '5'; } else { echo '0'; } ?></span>.0 <?php _e( $wcfm_review_category['category'], 'wc-multivendor-marketplace' ); ?></span>
								<input type="hidden" class="rating_value" name="wcfm_store_review_category[<?php echo $wcfm_review_cat_key; ?>]" value="<?php if( $is_start_with_full_rating ) { echo '5'; } else { echo '0'; } ?>" />
							</div>
						<?php } ?>
					</div>
					<div class="add_review add_review_box">
					  <input type="hidden" name="wcfm_review_store_id" value="<?php echo $store_user->get_id(); ?>" />
					  <input type="hidden" name="wcfm_review_author_id" value="<?php echo $author_id; ?>" />
						<textarea name="wcfmmp_store_review_comment" id="wcfmmp_store_review_comment" cols="" rows=""></textarea>
						<button id="wcfmmp_store_review_submit"><?php _e( 'Publish Review', 'wc-multivendor-marketplace' ); ?></button>
						<div class="wcfm-clearfix"></div>
						<div class="wcfm-message" tabindex="-1"></div>
					</div>
				</form>
			</div>
			<div class="spacer"></div>        
		</div>
	</div>