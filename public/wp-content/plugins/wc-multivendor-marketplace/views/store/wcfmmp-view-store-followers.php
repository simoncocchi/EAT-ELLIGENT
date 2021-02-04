<?php
/**
 * The Template for displaying all followers.
 *
 * @package WCfM Markeplace Views Followers
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

		
$followers_arr = get_user_meta( $store_user->get_id(), '_wcfm_followers_list', true );

?>

<?php do_action( 'wcfmmp_store_before_follows', $store_user->get_id() ); ?>

<div class="_area" id="follow">
 <?php
 if( $followers_arr && is_array( $followers_arr ) ) {
		echo '<table class="wcfm_vendor_followers"><tbody>';
		$tr_started = false;
		foreach( $followers_arr as $findex => $follower ) {
			$finfo = get_userdata( $follower );
			if( !$finfo ) continue;
			if( ( $findex == 0 ) || ( $findex % 2 == 0 ) ) {
				echo '<tr>';
				$tr_started = true;
			}
			echo '<td width="50%">';
			$wcfm_vendor_follower = '<div class="wcfm_vendor_follower">';
			$wp_user_avatar_id = get_user_meta( $follower, 'wp_user_avatar', true );
			$wp_user_avatar = wp_get_attachment_url( $wp_user_avatar_id );
			if ( !$wp_user_avatar ) {
				$wp_user_avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
			}
			$wcfm_vendor_follower .= '<img width="100" src="' . $wp_user_avatar . '" />';
			$wcfm_vendor_follower .= '<br /><strong>' . $finfo->display_name . '</strong>';
			$wcfm_vendor_follower .= '</div>';
			echo apply_filters( 'wcfm_vendor_follower', $wcfm_vendor_follower, $follower );
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

<?php do_action( 'wcfmmp_store_after_follows', $store_user->get_id() ); ?>