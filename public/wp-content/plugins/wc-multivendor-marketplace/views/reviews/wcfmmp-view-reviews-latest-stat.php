<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WCfM Markeplace Views Store review latest stat
 *
 * For edit coping this to yourtheme/wcfm/reviews 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$author_id = $store_user->get_last_review_author_id();
$author_name = $store_user->get_last_review_author_name();

$latest_avatars = array();
foreach( $latest_reviews as $key => $latest_review ) {
	$wp_user_avatar_id = get_user_meta( $latest_review->author_id, 'wp_user_avatar', true );
	$wp_user_avatar = wp_get_attachment_url( $wp_user_avatar_id );
	if ( !$wp_user_avatar ) {
		$wp_user_avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
	}
	$latest_avatars[] = $wp_user_avatar;
	if( $key > 1 ) break; 
}

if( empty( $latest_avatars ) || ( count( $latest_avatars ) < 3 ) ) {
	for( $i = count($latest_avatars); $i < 3; $i++ ) {
		$latest_avatars[] = $WCFM->plugin_url . 'assets/images/avatar.png';
	}
}

$latest_avatars = array_reverse( $latest_avatars );

?>

<div class="famous_reviewers">
	<div class="famous_reviewers_pictures lft">
	  <?php foreach( $latest_avatars as $latest_avatar ) { ?>
		  <div class="famous_reviewers_picture lft">
			  <img class="avatar image" src="<?php echo $latest_avatar; ?>" data-original="<?php echo $latest_avatar; ?>" style="display: block;">
			</div>
		<?php } ?>
		<div class="spacer"></div>    
	</div>
	<div class="lft m10 reviews_count">
	  <?php if( $author_name ) { ?><a onClick="return false;" href="#"><?php echo apply_filters( 'wcfmmp_review_author_name', $author_name, $latest_review ); ?></a> <?php } ?>
	  <?php if( $total_review_count > 1 ) { ?> <?php _e('and', 'wc-multivendor-marketplace' ); ?> <a onClick="return false;" href="#"><?php echo ($total_review_count-1); ?> <?php _e('others have', 'wc-multivendor-marketplace' ); ?></a>
    <?php } elseif( !$total_review_count ) { ?><?php _e('No user has', 'wc-multivendor-marketplace' ); ?></a>
    <?php } else { _e( 'has', 'wc-multivendor-marketplace' ); } ?>
    <?php _e( 'reviewed this store', 'wc-multivendor-marketplace' ); ?>
  </div>
	<div class="spacer"></div>    
</div>