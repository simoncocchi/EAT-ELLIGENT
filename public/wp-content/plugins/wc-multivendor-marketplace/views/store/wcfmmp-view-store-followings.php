<?php
/**
 * The Template for displaying all followers.
 *
 * @package WCfM Markeplace Views Followings
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

		
$followers_arr = get_user_meta( $store_user->get_id(), '_wcfm_following_list', true );

?>

<?php do_action( 'wcfmmp_store_before_followings', $store_user->get_id() ); ?>

<div class="_area" id="follow">
 <?php
 if( $followers_arr && is_array( $followers_arr ) ) {
		echo '<table class="wcfm_vendor_followers"><tbody>';
		$tr_started = false;
		foreach( $followers_arr as $findex => $follower ) {
			if( ( $findex == 0 ) || ( $findex % 2 == 0 ) ) {
				echo '<tr>';
				$tr_started = true;
			}
			echo '<td width="50%">';
			echo '<div class="wcfm_vendor_follower">';
			$finfo = get_userdata( $follower );
			$wp_user_avatar_id = get_user_meta( $follower, 'wp_user_avatar', true );
			$wp_user_avatar = wp_get_attachment_url( $wp_user_avatar_id );
			if ( !$wp_user_avatar ) {
				$wp_user_avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
			}
			echo '<img src="' . $wp_user_avatar . '" />';
			echo '<br /><strong>' . $finfo->display_name . '</strong>';
			echo '</div>';
			echo '</td>';
			if( ( $findex != 0 ) && ( $findex % 2 != 0 ) ) {
				echo '</tr>';
				$tr_started = false;
			}
		}
		if( $tr_started ) echo '</tr>';
		echo '</tbody></table>';
	}
 ?>
</div>

<?php do_action( 'wcfmmp_store_after_followings', $store_user->get_id() ); ?>