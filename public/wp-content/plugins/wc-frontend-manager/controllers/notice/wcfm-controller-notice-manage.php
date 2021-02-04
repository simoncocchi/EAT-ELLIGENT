<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Notice Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   3.0.6
 */

class wcfm_Notice_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $wcfm_notice_manager_form_data;
		
		$wcfm_notice_manager_form_data = array();
	  parse_str($_POST['wcfm_notice_manage_form'], $wcfm_notice_manager_form_data);
	  
	  $wcfm_notice_messages = get_wcfm_notice_manage_messages();
	  $has_error = false;
	  
	  if(isset($wcfm_notice_manager_form_data['title']) && !empty($wcfm_notice_manager_form_data['title'])) {
	  	$is_update = false;
	  	$is_publish = false;
	  	$current_user_id = get_current_user_id();
	  	
	  	$notice_status = 'publish';
	  	
	  	// Creating new notice
			$new_notice = array(
				'post_title'   => wc_clean( $wcfm_notice_manager_form_data['title'] ),
				'post_status'  => $notice_status,
				'post_type'    => 'wcfm_notice',
				'post_content' => apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['content'], ENT_QUOTES, 'UTF-8' ) ) ),
				'post_author'  => $current_user_id
			);
			
			if(isset($wcfm_notice_manager_form_data['notice_id']) && $wcfm_notice_manager_form_data['notice_id'] == 0) {
				$new_notice_id = wp_insert_post( $new_notice, true );
			} else { // For Update
				$is_update = true;
				$new_notice['ID'] = $wcfm_notice_manager_form_data['notice_id'];
				$new_notice_id = wp_update_post( $new_notice, true );
			}
			
			if(!is_wp_error($new_notice_id)) {
				
				if( isset( $wcfm_notice_manager_form_data['allow_reply'] ) ) {
					update_post_meta( $new_notice_id, 'allow_reply', 'yes' );
				} else {
					update_post_meta( $new_notice_id, 'allow_reply', 'no' );
				}
				
				if( isset( $wcfm_notice_manager_form_data['close_new_reply'] ) ) {
					update_post_meta( $new_notice_id, 'close_new_reply', 'yes' );
				} else {
					update_post_meta( $new_notice_id, 'close_new_reply', 'no' );
				}
				
				// Vendor Notification
				if( !$is_update ) {
					$author_id = apply_filters( 'wcfm_message_author', get_current_user_id() );
					$message_to = -1; // Receive to all
					$author_is_admin = 1;
					$author_is_vendor = 0;
					
					$wcfm_messages = sprintf( __( 'A new announcement submitted. <a target="_blank" class="wcfm_dashboard_item_title" href="%s">%s</a>', 'wc-frontend-manager' ), get_wcfm_notice_view_url( $new_notice_id ), $wcfm_notice_manager_form_data['title'] );
					$wcfm_messages = apply_filters( 'wcfm_notice_message_notification_content', $wcfm_messages, $new_notice_id );
					
					$WCFM->wcfm_notification->wcfm_send_direct_message( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, 'notice' );
					
					$cache_key = 'wcfm-notification-notice';
					delete_transient( $cache_key );
				}
				
				echo '{"status": true, "message": "' . $wcfm_notice_messages['notice_saved'] . '", "redirect": "' . get_wcfm_notice_view_url( $new_notice_id ) . '"}';
				die;
			} else {
				echo '{"status": false, "message": "' . $wcfm_notice_messages['notice_failed'] . '"}';
			}
		} else {
			echo '{"status": false, "message": "' . $wcfm_notice_messages['no_title'] . '"}';
		}
		
		die;
	}
}