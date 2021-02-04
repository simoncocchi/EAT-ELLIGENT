<?php
global $WCFM, $WCFMu;

$messageid = absint( $_POST['messageid'] );
$authorid = absint( $_POST['authorid'] );
?>

<div class="wcfm-clearfix"></div><br />
<div id="wcfm_message_send_reply_form_wrapper">
	<form id="wcfm_message_send_reply_form" class="wcfm_popup_wrapper">
		<div style="margin-bottom: 15px;"><h2 style="float: none;"><?php _e( 'Send Reply', 'wc-frontend-manager' ); ?></h2></div>
		<div class="wcfm-clearfix"></div><br />
		
		<p class="wcfm-wcfm-message-reply wcfm_popup_label">
			<label for="comment"><strong><?php _e( 'Message', 'wc-multivendor-marketplace' ); ?> <span class="required">*</span></strong></label>
		</p>
		<textarea id="wcfm_message_send_reply" name="wcfm_message_send_reply" class="wcfm_popup_input wcfm_popup_textarea"></textarea>
		<div class="wcfm_clearfix"></div>
		
		<?php do_action( 'after_wcfm_messages_form' ); ?>
		
		<?php
		if( !wcfm_is_vendor() && ( !function_exists( 'wcfm_is_affiliate' ) || ( function_exists( 'wcfm_is_affiliate' ) && !wcfm_is_affiliate() ) ) ) {
			echo '<input type="hidden" id="wcfm_message_send_reply_direct_to" name="wcfm_message_send_reply_direct_to" value="' . $authorid . '" />';
		}
		?>
		
		<div id="wcfm_messages_submit">
			<input type="submit" name="save-data" value="<?php _e( 'Send', 'wc-frontend-manager' ); ?>" id="wcfm_message_send_reply_button" class="submit wcfm_popup_button" />
		</div>
		<div class="wcfm-clearfix"></div>
		<div class="wcfm-message" tabindex="-1"></div>
		<div class="wcfm-clearfix"></div>
	</form>
</div>
<div class="wcfm-clearfix"></div><br />