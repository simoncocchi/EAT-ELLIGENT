<?php
/**
 * WCFM plugin view
 *
 * wcfm Inquiry Manage View
 *
 * @author 		WC Lovers
 * @package 	wcfmu/views/inquiry
 * @version   5.0.8
 */
 
global $wp, $WCFM, $WCFMu, $wpdb, $blog_id;

if( !apply_filters( 'wcfm_is_pref_inquiry', true ) || !apply_filters( 'wcfm_is_allow_inquiry', true ) || !apply_filters( 'wcfm_is_allow_manage_inquiry', true ) ) {
	wcfm_restriction_message_show( "Manage Inquiry" );
	return;
}

$inquiry_id = 0;
$inquiry_content = '';
$inquiry_product_id = 0;
$inquiry_vendor_id = 0;
$inquiry_customer_id = 0;
$inquiry_customer_name = 0;
$inquiry_customer_email = 0;

$wcfm_myac_modified_endpoints = wcfm_get_option( 'wcfm_myac_endpoints', array() );
$wcfm_myaccount_inquiry_endpoint = ! empty( $wcfm_myac_modified_endpoints['inquiry'] ) ? $wcfm_myac_modified_endpoints['inquiry'] : 'inquiry';
$wcfm_myaccount_view_inquiry_endpoint = ! empty( $wcfm_myac_modified_endpoints['view-inquiry'] ) ? $wcfm_myac_modified_endpoints['view-inquiry'] : 'view-inquiry';

if( isset( $wp->query_vars[$wcfm_myaccount_view_inquiry_endpoint] ) && !empty( $wp->query_vars[$wcfm_myaccount_view_inquiry_endpoint] ) ) {
	$inquiry_id = absint( $wp->query_vars[$wcfm_myaccount_view_inquiry_endpoint] );
	$inquiry_post = $wpdb->get_row( "SELECT * from {$wpdb->prefix}wcfm_enquiries WHERE `ID` = " . $inquiry_id );
	// Fetching Inquiry Data
	if($inquiry_post && !empty($inquiry_post)) {
		$inquiry_content = $inquiry_post->enquiry;
		$inquiry_product_id = $inquiry_post->product_id;
		$inquiry_vendor_id = $inquiry_post->vendor_id;
		$inquiry_customer_id = $inquiry_post->customer_id;
		$inquiry_customer_name = $inquiry_post->customer_name;
		$inquiry_customer_email = $inquiry_post->customer_email;
		$customer_id = get_current_user_id();
		if( $inquiry_customer_id != $customer_id ) {
			wcfm_restriction_message_show( "Inquiry Not Found" );
			return;
		}
	} else {
		wcfm_restriction_message_show( "Inquiry Not Found" );
		return;
	}
}
$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
if ( $myaccount_page_id ) {
	$myaccount_page_url = trailingslashit( get_permalink( $myaccount_page_id ) );
}

$wcfm_options = $WCFM->wcfm_options;
$wcfm_enquiry_allow_attachment = isset( $wcfm_options['wcfm_enquiry_allow_attachment'] ) ? $wcfm_options['wcfm_enquiry_allow_attachment'] : 'yes';

do_action( 'before_my_account_wcfm_inquiry_manage' );

?>

<div id="wcfm-main-contentainer">
	<div class="collapse wcfm-collapse">
		<div class="wcfm-collapse-content">
			<div class="wcfm-container wcfm-top-element-container">
				<h2><?php echo __( 'Inquiry', 'wc-frontend-manager' ) . ' #' . sprintf( '%06u', $inquiry_id ); ?></h2>
				
				<?php
				echo '<a id="add_new_inquiry_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="' . $myaccount_page_url . $wcfm_myaccount_inquiry_endpoint. '" data-tip="' . __('Inquiries', 'wc-frontend-manager') . '"><span class="wcfmfa fa-question-circle fa-question-circle"></span><span class="text">' . __( 'Inquiries', 'wc-frontend-manager') . '</span></a>';
				?>
				<div class="wcfm-clearfix"></div>
			</div>
			<div class="wcfm-clearfix"></div><br />
			
			<?php do_action( 'begin_my_account_wcfm_inquiry_manage_form' ); ?>
			
			<!-- collapsible -->
			<div class="wcfm-container">
				<div id="inquiry_manage_general_expander" class="wcfm-content">
					<div class="inquiry_content">
						<?php echo $inquiry_content; ?>
						<div class="wcfm_clearfix"></div>
					</div>
					
					<div class="inquiry_content_details">
						<div class="inquiry_content_for">
							<?php
							
							if( $inquiry_product_id || $inquiry_vendor_id ) {
								echo "<div style=\"width:auto;min-width:350px;\"><h2>" . __( 'Inquiry For', 'wc-frontend-manager' ) . "</h2><div class=\"wcfm_clearfix\"></div>";
							}
							
							if( $inquiry_product_id ) {
								$post_obj = get_post( $inquiry_product_id );
								if( $post_obj->post_type == 'product' ) {
									$the_product = wc_get_product( $inquiry_product_id );
									$thumbnail = $the_product->get_image( 'thumbnail' );
									$datatip_msg = __( 'Inquiry for Product', 'wc-frontend-manager' );
								} else {
									$thumbnail = '';
									$datatip_msg = sprintf( __( 'Inquiry for %s', 'wc-frontend-manager' ), $post_obj->post_type );
								}
								echo '<div class="wcfm_product_for_inquiry">' . $thumbnail . '&nbsp;<a class="img_tip" data-tip="'. $datatip_msg .'" href="'. get_permalink($inquiry_product_id) .'" target="_blank">'.get_the_title($inquiry_product_id).'</a></div>';
							}
							
							if( $inquiry_vendor_id ) {
								if( apply_filters( 'wcfmmp_is_allow_sold_by', true, $inquiry_vendor_id ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $inquiry_vendor_id, 'sold_by' ) ) {
									if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
										$store_name = wcfm_get_vendor_store( absint($inquiry_vendor_id) );
									} else {
										$store_name = wcfm_get_vendor_store_name( absint($inquiry_vendor_id) );
									}
									$store_logo = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( absint($inquiry_vendor_id) );
									echo '<div class="wcfm_store_for_inquiry"><img class="wcfmmp_sold_by_logo img_tip" src="' . $store_logo . '" data-tip="'. __( 'Inquiry for', 'wc-frontend-manager' ) . ' ' . apply_filters( 'wcfm_sold_by_label', $inquiry_vendor_id, __( 'Store', 'wc-frontend-manager' ) ) .'" />&nbsp;'.$store_name.'</div>';
								}
							}
							
							if( $inquiry_product_id || $inquiry_vendor_id ) {
								echo "</div><div class=\"wcfm_clearfix\"></div><br />";
							}
							
							$additional_info = '';
							$wcfm_enquiry_meta_values = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcfm_enquiries_meta WHERE `enquiry_id` = " . $inquiry_id);
							if( !empty( $wcfm_enquiry_meta_values ) ) {
								echo "<div style=\"margin-top: 30px;width:auto;min-width:350px;\"><h2>" . __( 'Additional Info', 'wc-frontend-manager' ) . "</h2><div class=\"wcfm_clearfix\"></div>";
								foreach( $wcfm_enquiry_meta_values as $wcfm_enquiry_meta_value ) {
									?>
									<p class="store_name wcfm_ele wcfm_title"><strong><?php _e( $wcfm_enquiry_meta_value->key, 'wc-frontend-manager'); ?></strong></p>
									<span class="wcfm_vendor_store_info"><?php echo $wcfm_enquiry_meta_value->value; ?></span>
									<div class="wcfm_clearfix"></div>
									<?php
								}
								echo "</div><div class=\"wcfm_clearfix\"></div><br />";
							}
							?>
						</div>
						<div class="inquiry_info">
							<div class="inquiry_date"><span class="wcfmfa fa-clock"></span>&nbsp;<?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $inquiry_post->posted ) ); ?></div>
						</div>
					</div>
					<div class="wcfm_clearfix"></div>
				</div>
			</div>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<?php 
			if( $wcfm_is_allow_view_inquiry_reply_view = apply_filters( 'wcfmcap_is_allow_inquiry_reply_view', true ) ) {
				$wcfm_inquiry_replies = $wpdb->get_results( "SELECT * from {$wpdb->prefix}wcfm_enquiries_response WHERE `enquiry_id` = " . $inquiry_id );
				
				echo '<h2>' . __( 'Replies', 'wc-frontend-manager' ) . ' (' . count( $wcfm_inquiry_replies ) . ')</h2><div class="wcfm_clearfix"></div>';
				
				if( !empty( $wcfm_inquiry_replies ) ) {
					foreach( $wcfm_inquiry_replies as $wcfm_inquiry_reply ) {
					?>
					<!-- collapsible -->
					<div class="wcfm-container">
						<div id="inquiry_reply_<?php echo $wcfm_inquiry_reply->ID; ?>" class="inquiry_reply wcfm-content">
							<div class="inquiry_reply_author">
								<?php
								$author_id = $wcfm_inquiry_reply->reply_by;
								if( wcfm_is_vendor( $author_id ) ) {
									$wp_user_avatar = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( $author_id );
									if( !$wp_user_avatar ) {
										$wp_user_avatar = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp.png' );
									}
								} else {
									$wp_user_avatar_id = get_user_meta( $author_id, $wpdb->get_blog_prefix($blog_id).'user_avatar', true );
									$wp_user_avatar = wp_get_attachment_url( $wp_user_avatar_id );
									if ( !$wp_user_avatar ) {
										$wp_user_avatar = apply_filters( 'wcfm_default_user_image', $WCFM->plugin_url . 'assets/images/user.png' );
									}
								}
								?>
								<img src="<?php echo $wp_user_avatar; ?>" /><br />
								<?php
								if( ( apply_filters( 'wcfmmp_is_allow_sold_by', true, $inquiry_vendor_id ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $inquiry_vendor_id, 'sold_by' ) && apply_filters( 'wcfm_allow_view_vendor_name', true ) ) || ( $author_id == $inquiry_customer_id ) ) {
									$author_label = '';
									if( wcfm_is_vendor( $author_id ) ) {
										$author_label = wcfm_get_vendor_store_name( $author_id );
									} elseif( $author_id != $wcfm_inquiry_reply->customer_id ) {
										echo get_bloginfo( 'name' );
									} else {
										$userdata = get_userdata( $author_id );
										$first_name = $userdata->first_name;
										$last_name  = $userdata->last_name;
										$display_name  = $userdata->display_name;
										if( $first_name ) {
											$author_label .= $first_name . ' ' . $last_name;
										} else {
											$author_label .= $display_name;
										}
									}
									echo $author_label;
								} else {
									_e( 'Keymaster', 'wc-frontend-manager' );
								}
								?>
								<br /><?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_inquiry_reply->posted ) ); ?>
							</div>
							<div class="inquiry_reply_content">
								<?php echo $wcfm_inquiry_reply->reply; ?>
								
								<?php
								// Attachments
								$WCFM->wcfm_enquiry->wcfm_enquiry_reply_attachments( $wcfm_inquiry_reply->ID );
								?>
							</div>
						</div>
					</div>
					<div class="wcfm_clearfix"></div><br />
					<!-- end collapsible -->
					<?php
					}
				}
			} 
			?>
			
			<?php if( $wcfm_is_allow_view_inquiry_reply = apply_filters( 'wcfmcap_is_allow_inquiry_reply', true ) ) { ?>
				<?php do_action( 'before_wcfm_inquiry_reply_form' ); ?>
				<form id="wcfm_inquiry_reply_form" class="wcfm">
					<h2><?php _e('New Reply', 'wc-frontend-manager' ); ?></h2>
					<div class="wcfm-clearfix"></div>
					<div class="wcfm-container">
						<div id="wcfm_new_reply_listing_expander" class="wcfm-content">
							<?php
							$wcfm_enquiry_reply_fields =  apply_filters( 'wcfm_enquiry_reply_fields', array(
																																															"inquiry_reply"           => array( 'label' => __( 'Message', 'wc-frontend-manager'), 'type' => 'wpeditor', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele wcfm_wpeditor', 'label_class' => 'wcfm_title wcfm_full_ele_title', 'media_buttons' => false, 'teeny' => true ),
																																															"inquiry_reply_break1"    => array( 'type' => 'html', 'value' => '<div class="wcfm-clearfix" style="margin-bottom: 25px;"></div>' ),
																																															"inquiry_attachments"     => array( 'label' => __( 'Attachment(s)', 'wc-frontend-manager'), 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele wcfm_non_sortable', 'label_class' => 'wcfm_title', 'value' => array(), 'options' => array(
																																																																	"file" => array( 'label' => __('Add File', 'wc-frontend-manager'), 'type' => 'file', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'),
																																																																 ), 'desc' => sprintf( __( 'Please upload any of these file types: %1$s', 'wc-frontend-manager' ), '<b style="color:#f86c6b;">' . implode( ', ', array_keys( wcfm_get_allowed_mime_types() ) ) . '</b>' ) ),
																																															"inquiry_reply_break2"    => array( 'type' => 'html', 'value' => '<div class="wcfm-clearfix" style="margin-bottom: 25px;"></div>' ),
																																															"inquiry_id"              => array( 'type' => 'hidden', 'value' => $inquiry_id ),
																																															"inquiry_product_id"      => array( 'type' => 'hidden', 'value' => $inquiry_product_id ),
																																															"inquiry_vendor_id"       => array( 'type' => 'hidden', 'value' => $inquiry_vendor_id ),
																																															"inquiry_customer_id"     => array( 'type' => 'hidden', 'value' => $inquiry_customer_id ),
																																															"inquiry_customer_name"   => array( 'type' => 'hidden', 'value' => $inquiry_customer_name ),
																																															"inquiry_customer_email"  => array( 'type' => 'hidden', 'value' => $inquiry_customer_email )
																																															), $inquiry_id );
							
							if( ( $wcfm_enquiry_allow_attachment == 'no' ) || !apply_filters( 'wcfm_is_allow_enquiry_reply_attachment', true ) ) {
								if( isset( $wcfm_enquiry_reply_fields['inquiry_attachments'] ) ) unset( $wcfm_enquiry_reply_fields['inquiry_attachments'] );
								if( isset( $wcfm_enquiry_reply_fields['inquiry_reply_break2'] ) ) unset( $wcfm_enquiry_reply_fields['inquiry_reply_break2'] );
							}
							
							$WCFM->wcfm_fields->wcfm_generate_form_field( $wcfm_enquiry_reply_fields );
							?>
							<div class="wcfm-clearfix"></div>
							<div class="wcfm-message" tabindex="-1"></div>
							<div class="wcfm-clearfix"></div>
							<div id="wcfm_inquiry_reply_submit">
								<input type="submit" name="save-data" value="<?php _e( 'Send', 'wc-frontend-manager' ); ?>" id="wcfm_inquiry_reply_send_button" class="wcfm_submit_button" />
							</div>
							<div class="wcfm-clearfix"></div>
						</div>
					</div>
				</form>
				<?php do_action( 'after_wcfm_inquiry_reply_form' ); ?>
				<div class="wcfm-clearfix"></div><br />
			<?php } ?>
			
			<?php do_action( 'end_my_account_wcfm_inquiry_manage_form' ); ?>
			
			<?php
			do_action( 'after_my_account_wcfm_inquiry_manage' );
			?>
		</div>
	</div>
</div>