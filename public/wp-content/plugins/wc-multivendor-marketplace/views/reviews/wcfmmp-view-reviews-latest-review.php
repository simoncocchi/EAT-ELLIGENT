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

global $WCFM, $WCFMmp, $wpdb;

if( !$total_review_count ) return;
if( empty( $latest_reviews ) ) return;

?>

<?php foreach( $latest_reviews as $latest_review ) { ?>
	<div class="review_section">
		<div class="lft user_photo">
			<div class="review_photo">
			  <?php
				$wp_user_avatar_id = get_user_meta( $latest_review->author_id, 'wp_user_avatar', true );
				$wp_user_avatar = wp_get_attachment_url( $wp_user_avatar_id );
				if ( !$wp_user_avatar ) {
					$wp_user_avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
				}
				?>
			  <img src="<?php echo apply_filters( 'wcfmmp_review_author_avatar', $wp_user_avatar, $latest_review ); ?>" alt="Review">
			</div>
			<?php if( apply_filters( 'wcfm_is_allow_review_rating', true ) ) { ?>
				<div class="rated">
					<strong><?php _e('rated', 'wc-multivendor-marketplace' ); ?></strong>
					<div class="user_rated"><?php echo apply_filters( 'wcfmmp_review_rating', wc_format_decimal( $latest_review->review_rating, 1 ), $latest_review ); ?></div>
				</div>
			<?php } ?>
		</div>
		<div class="rgt user_review_sec">
		  <div class="user_review_sec_left" style="display: inline-block;float: left;width: 60%;">
				<div class="user_name"><?php echo apply_filters( 'wcfmmp_review_author_name', $latest_review->author_name, $latest_review ); ?></div>
				<div class="user_review_area">
					<span><?php echo $WCFMmp->wcfmmp_reviews->get_author_reviews_count($latest_review->author_id); ?> <?php _e( 'reviews', 'wc-multivendor-marketplace' ); ?></span> <span class="user_date"><?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime($latest_review->created) ) ; ?></span>
				</div>
				<div class="user_review_text">
					<p><?php echo $latest_review->review_description; ?></p>
					<?php 
					if( apply_filters( 'wcfm_is_allow_review_product', true ) ) {
						$product_id = $wpdb->get_var( "SELECT `value` FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE `review_id` = {$latest_review->ID} AND `key` = 'product' AND `type` = 'rating_product'" );
						if( $product_id ) {
							echo '<p><i class="wcfmfa fa-cube img_tip" data-tip="'. __( 'Review via Product', 'wc-multivendor-marketplace' ) .'"></i>&nbsp;<a style="display: inline-block;color:#17a2b8;" href="'. get_permalink($product_id) .'" target="_blank">'.get_the_title($product_id).'</a></p>';
						}
					} 
					?>
				</div>
				<?php if( apply_filters( 'wcfm_is_allow_review_reply', false ) ) { ?>
					<div class="reply_bt"><button><?php _e('Reply', 'wc-multivendor-marketplace' ); ?></button></div>
				<?php } ?>
			</div>
			<?php
			if( apply_filters( 'wcfm_is_allow_review_rating', true ) && apply_filters( 'wcfmmp_is_allow_review_categories', true ) ) {
				$wcfm_review_categories = get_wcfm_marketplace_active_review_categories();
				$category_review_rating = $store_user->get_review_meta( $latest_review->ID );
				if( $category_review_rating && !empty( $category_review_rating ) && is_array( $category_review_rating ) ) {
					echo '<div class="bd_rating_area" style="float: right;width: 35%;">';
					foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
						if( isset( $category_review_rating[$wcfm_review_cat_key] ) ) {
							?>
							<div class="rating_box">
								<i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i><i class="wcfmfa fa-star" aria-hidden="true"></i>
								<span>
									<?php
									echo wc_format_decimal( $category_review_rating[$wcfm_review_cat_key], 1 ) . '&nbsp;';
									_e( $wcfm_review_category['category'], 'wc-multivendor-marketplace' );
									?>
								</span>
								<input type="hidden" class="store_rating_value" name="wcfm_saved_store_review_category[<?php echo $wcfm_review_cat_key; ?>]" value="<?php echo wc_format_decimal( $category_review_rating[$wcfm_review_cat_key], 1 ); ?>" />
							</div>
							<?php
						}
					}
					echo '</div>';
				}
			}
			?>
		</div>
		<div class="spacer"></div>    
	</div>
<?php } ?>