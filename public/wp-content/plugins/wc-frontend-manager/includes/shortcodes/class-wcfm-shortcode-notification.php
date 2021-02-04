<?php
/**
 * WCFM plugin shortcode
 *
 * Plugin Shortcode output
 *
 * @author 		WC Lovers
 * @package 	wcfm/includes/shortcode
 * @version   1.0.0
 */
 
class WCFM_Notification_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the Notification shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	static public function output( $attr ) {
		global $WCFM, $wp, $WCFM_Query;
		$WCFM->nocache();
		
		if( !$WCFM || !$WCFM->frontend ) return;
		
		if( is_admin() ) return;
		
		if( !wcfm_is_allow_wcfm() ) return;
		
		$message = true;
		if ( isset( $attr['message'] ) && !empty( $attr['message'] ) && ( 'false' == $attr['message'] ) ) { $message = false; }
		
		$enquiry = true;
		if ( isset( $attr['enquiry'] ) && !empty( $attr['enquiry'] ) && ( 'false' == $attr['enquiry'] ) ) { $enquiry = false; }
		
		$notice = true;
		if ( isset( $attr['notice'] ) && !empty( $attr['notice'] ) && ( 'false' == $attr['notice'] ) ) { $notice = false; }
		
		$unread_notice = $WCFM->wcfm_notification->wcfm_direct_message_count( 'notice' );
		$unread_message = $WCFM->wcfm_notification->wcfm_direct_message_count( 'message' ); 
		$unread_enquiry = $WCFM->wcfm_notification->wcfm_direct_message_count( 'enquiry' );
		
		?>
		<div class="wcfm_sc_notifications">
			<?php if( $message && apply_filters( 'wcfm_is_pref_direct_message', true ) && apply_filters( 'wcfm_is_allow_notifications', true ) && apply_filters( 'wcfm_is_allow_sc_notifications', true ) ) { ?>
				<a href="<?php echo get_wcfm_messages_url( ); ?>" class="wcfmfa fa-bell text_tip" data-tip="<?php _e( 'Notification Board', 'wc-frontend-manager' ); ?>"><span class="unread_notification_count message_count"><?php echo $unread_message; ?></span></a>
			<?php } ?>
			
			<?php if( $enquiry && apply_filters( 'wcfm_is_pref_enquiry', true ) && apply_filters( 'wcfm_is_allow_enquiry', true ) && apply_filters( 'wcfm_is_allow_sc_enquiry_notifications', true ) ) { ?>
				<a href="<?php echo get_wcfm_enquiry_url(); ?>" class="wcfmfa fa-question-circle text_tip" data-tip="<?php _e( 'Enquiry Board', 'wc-frontend-manager' ); ?>"><span class="unread_notification_count enquiry_count"><?php echo $unread_enquiry; ?></span></a>
			<?php } ?>
			
			<?php if( $notice && apply_filters( 'wcfm_is_pref_notice', true ) && apply_filters( 'wcfm_is_allow_notice', true ) && apply_filters( 'wcfm_is_allow_sc_notice_notifications', true ) ) { ?>
				<a href="<?php echo get_wcfm_notices_url( ); ?>" class="wcfmfa fa-bullhorn text_tip" data-tip="<?php _e( 'Notice Board', 'wc-frontend-manager' ); ?>"><?php if( wcfm_is_vendor() ) { ?><span class="unread_notification_count notice_count"><?php echo $unread_notice; ?></span><?php } ?></a>
			<?php } ?>
		</div>
		<?php
	}
}