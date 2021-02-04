<?php
/**
 * WCFM plugin view
 *
 * wcfm Enquiry Manage View
 * This template can be overridden by copying it to yourtheme/wcfm/enquiry/
 *
 * @author 		WC Lovers
 * @package 	wcfm/view/enquiry
 * @version   3.2.8
 */
 
global $wp, $WCFM, $wpdb, $blog_id;

if( !apply_filters( 'wcfm_is_pref_enquiry', true ) || !apply_filters( 'wcfm_is_allow_enquiry', true ) || !apply_filters( 'wcfm_is_allow_enquiry_reply', true ) ) {
	wcfm_restriction_message_show( "Inquiry Board" );
	return;
}

$is_private = 'no';

$inquiry_id = 0;
$inquiry_content = '';
$inquiry_product_id = 0;
$inquiry_vendor_id = 0;
$inquiry_customer_id = 0;
$inquiry_customer_name = 0;
$inquiry_customer_email = 0;

if( isset( $wp->query_vars['wcfm-enquiry-manage'] ) && !empty( $wp->query_vars['wcfm-enquiry-manage'] ) ) {
	$inquiry_id = absint( $wp->query_vars['wcfm-enquiry-manage'] );
	
	if( !$inquiry_id ) {
		wcfm_restriction_message_show( "Enquiry Board" );
		return;
	}
	
	$enquiry_datas = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wcfm_enquiries WHERE `ID` = {$inquiry_id}" );
	
	if( !empty($enquiry_datas ) ) {
		foreach( $enquiry_datas as $enquiry_data ) {
			$inquiry_content = $enquiry_data->enquiry;
			$inquiry_product_id = $enquiry_data->product_id;
			$inquiry_vendor_id = $enquiry_data->vendor_id;
			$inquiry_customer_id = $enquiry_data->customer_id;
			$inquiry_customer_name = $enquiry_data->customer_name;
			$inquiry_customer_email = $enquiry_data->customer_email;
		}
	} else {
		wcfm_restriction_message_show( "Inquiry Not Found" );
		return;
	}
	
	$product_id = $enquiry_data->product_id;
	$is_private = ( $enquiry_data->is_private == 0 ) ? 'no' : 'yes';
} else {
	wcfm_restriction_message_show( "Inquiry Not Found" );
	return;
}

if( wcfm_is_vendor() ) {
	$is_inquiry_for_vendor = $WCFM->wcfm_vendor_support->wcfm_is_component_for_vendor( $inquiry_id, 'inquiry' );
	if( !$is_inquiry_for_vendor ) {
		if( apply_filters( 'wcfm_is_show_inquiry_restrict_message', true, $inquiry_id ) ) {
			wcfm_restriction_message_show( "Restricted Inquiry" );
		} else {
			echo apply_filters( 'wcfm_show_custom_inquiry_restrict_message', '', $inquiry_id );
		}
		return;
	}
}

$wcfm_options = $WCFM->wcfm_options;
$wcfm_enquiry_allow_attachment = isset( $wcfm_options['wcfm_enquiry_allow_attachment'] ) ? $wcfm_options['wcfm_enquiry_allow_attachment'] : 'yes';

do_action( 'before_wcfm_enquiry_manage' );

?>

<div class="collapse wcfm-collapse">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-question-circle"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Manage Enquiry', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
	  	<h2><?php if( $inquiry_id ) { _e('Edit Enquiry', 'wc-frontend-manager' ); } else { _e('Add Enquiry', 'wc-frontend-manager' ); } ?></h2>
			
			<?php
			echo '<a id="add_new_enquiry_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_enquiry_url().'" data-tip="' . __('Enquiries', 'wc-frontend-manager') . '"><span class="wcfmfa fa-question-circle"></span><span class="text">' . __( 'Enquiries', 'wc-frontend-manager') . '</span></a>';
			if( $inquiry_id && $product_id ) { echo '<a class="add_new_wcfm_ele_dashboard text_tip" target="_permalink" href="'.get_permalink($product_id).'" data-tip="' . __('View Product', 'wc-frontend-manager') . '"><span class="wcfmfa fa-cube"></span></a>'; }
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'before_wcfm_enquiry_manage' ); ?>
	  
		<?php do_action( 'begin_wcfm_enquiry_manage_form' ); ?>
		
		<!-- collapsible -->
		<div class="wcfm-container">
			<div id="enquiry_manage_general_expander" class="wcfm-content">
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
							if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
								$store_name = wcfm_get_vendor_store( absint($inquiry_vendor_id) );
							} else {
								$store_name = wcfm_get_vendor_store_name( absint($inquiry_vendor_id) );
							}
							$store_logo = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( absint($inquiry_vendor_id) );
							echo '<div class="wcfm_store_for_inquiry"><img class="wcfmmp_sold_by_logo img_tip" src="' . $store_logo . '" data-tip="'. __( 'Inquiry for', 'wc-frontend-manager' ) . ' ' . apply_filters( 'wcfm_sold_by_label', $inquiry_vendor_id, __( 'Store', 'wc-frontend-manager' ) ) .'" />&nbsp;' . $store_name . '</div>';
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
						<div class="inquiry_date"><span class="wcfmfa fa-clock"></span>&nbsp;<?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $enquiry_data->posted ) ); ?></div>
						<?php if( apply_filters( 'wcfm_allow_view_customer_name', true ) ) { ?>
							<div class="inquiry_by">
								<span class="wcfmfa fa-user"></span>&nbsp;
								<span class="inquiry_by_customer">
								<?php if( $inquiry_customer_id && apply_filters( 'wcfm_is_allow_view_customer', true ) ) { ?>
									<?php echo '<a target="_blank" href="' . get_wcfm_customers_details_url($inquiry_customer_id) . '" class="wcfm_inquiry_by_customer inquiry_by_customer">' . apply_filters( 'wcfm_enquiry_customer_name_display', $inquiry_customer_name, $inquiry_customer_id, $inquiry_id ) . '</a>'; ?>
								<?php } else { ?>
									<?php echo apply_filters( 'wcfm_enquiry_customer_name_display', $inquiry_customer_name, $inquiry_customer_id, $inquiry_id ); ?>
								<?php } ?>
								<?php if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) { ?>
									 <br /><?php echo $inquiry_customer_email; ?>
								<?php } ?>
								</span>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
			</div>
		</div>
		<div class="wcfm_clearfix"></div><br />
		<!-- end collapsible -->
		
		<?php 
		if( $wcfm_is_allow_view_enquiry_reply_view = apply_filters( 'wcfmcap_is_allow_enquiry_reply_view', true ) ) {
			$wcfm_enquiry_replies = $wpdb->get_results( "SELECT * from {$wpdb->prefix}wcfm_enquiries_response WHERE `enquiry_id` = " . $inquiry_id );
			
			echo '<h2>' . __( 'Replies', 'wc-frontend-manager' ) . ' (' . count( $wcfm_enquiry_replies ) . ')</h2><div class="wcfm_clearfix"></div>';
			
			if( !empty( $wcfm_enquiry_replies ) ) {
				foreach( $wcfm_enquiry_replies as $wcfm_enquiry_reply ) {
				?>
				<!-- collapsible -->
				<div class="wcfm-container">
					<div id="inquiry_reply_<?php echo $wcfm_enquiry_reply->ID; ?>" class="inquiry_reply wcfm-content">
						<div class="inquiry_reply_author">
							<?php
							$author_id = $wcfm_enquiry_reply->reply_by;
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
							if( apply_filters( 'wcfm_allow_view_customer_name', true ) || ( $author_id == $inquiry_vendor_id ) ) {
								if( wcfm_is_vendor( $author_id ) ) {
									echo wcfm_get_vendor_store_name( $author_id );
								} elseif( $author_id != $wcfm_enquiry_reply->customer_id ) {
									echo get_bloginfo( 'name' );
								} else {
									$userdata = get_userdata( $author_id );
									$first_name = $userdata->first_name;
									$last_name  = $userdata->last_name;
									$display_name  = $userdata->display_name;
									if( $first_name ) {
										echo $first_name . ' ' . $last_name;
									} else {
										echo $display_name;
									}
								}
							}
							?>
							<br /><?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $wcfm_enquiry_reply->posted ) ); ?>
						</div>
						<div class="inquiry_reply_content">
							<?php echo $wcfm_enquiry_reply->reply; ?>
							
							<?php
							// Attachments
							$WCFM->wcfm_enquiry->wcfm_enquiry_reply_attachments( $wcfm_enquiry_reply->ID );
							?>
						</div>
						<?php
						if( apply_filters( 'wcfm_is_allow_eniquiry_dalate', true ) ) {
							echo '<div class="wcfm_clearfix"></div><br /><a class="wcfm_enquiry_response_delete wcfm-action-icon" href="#" data-enquiryresponseid="' . $wcfm_enquiry_reply->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-frontend-manager' ) . '"></span></a>';
						}
						?>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div><br />
				<!-- end collapsible -->
				<?php
				}
			}
		} 
		?>
		
		<?php if( $wcfm_is_allow_view_enquiry_reply = apply_filters( 'wcfmcap_is_allow_enquiry_reply', true ) ) { ?>
			<?php do_action( 'before_wcfm_enquiry_reply_form' ); ?>
			<form id="wcfm_inquiry_reply_form" class="wcfm">
				<h2><?php _e('New Reply', 'wc-frontend-manager' ); ?></h2>
				<div class="wcfm-clearfix"></div>
				<div class="wcfm-container">
					<div id="wcfm_new_reply_listing_expander" class="wcfm-content">
						<?php
						$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
						$wpeditor = apply_filters( 'wcfm_is_allow_profile_wpeditor', 'wpeditor' );
						if( $wpeditor && $rich_editor ) {
							$rich_editor = 'wcfm_wpeditor';
						} else {
							$wpeditor = 'textarea';
						}
						$inquiry_stick_class = '';
						if( !$inquiry_product_id ) $inquiry_stick_class = ' wcfm_custom_hide';
						 $wcfm_enquiry_reply_fields = apply_filters( 'wcfm_enquiry_reply_fields', array(
																																														"inquiry_reply"           => array( 'label' => __( 'Message', 'wc-frontend-manager'), 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele_title', 'media_buttons' => false, 'teeny' => true ),
																																														"inquiry_reply_break"     => array( 'type' => 'html', 'value' => '<div class="wcfm-clearfix" style="margin-bottom: 25px;"></div>' ),
																																														"inquiry_attachments"     => array( 'label' => __( 'Attachment(s)', 'wc-frontend-manager'), 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele wcfm_non_sortable', 'label_class' => 'wcfm_title', 'value' => array(), 'options' => array(
																																																																		"file" => array( 'label' => __('Add File', 'wc-frontend-manager'), 'type' => 'file', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'),
																																																							                 ), 'desc' => sprintf( __( 'Please upload any of these file types: %1$s', 'wc-frontend-manager' ), '<b style="color:#f86c6b;">' . implode( ', ', array_keys( wcfm_get_allowed_mime_types() ) ) . '</b>' ) ),
																																														"inquiry_reply_break2"    => array( 'type' => 'html', 'value' => '<div class="wcfm-clearfix" style="margin-bottom: 15px;"></div>' ),
																																														"inquiry_stick"           => array( 'label' => __( 'Stick at Product Page', 'wc-frontend-manager' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox' . $inquiry_stick_class, 'label_class' => 'wcfm_title checkbox_title' . $inquiry_stick_class, 'value' => 'yes', 'hints' => __( 'Enable to stick this reply at product page', 'wc-frontend-manager' ) ),
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
						<div id="wcfm_enquiry_reply_submit">
							<input type="submit" name="save-data" value="<?php _e( 'Send', 'wc-frontend-manager' ); ?>" id="wcfm_inquiry_reply_send_button" class="wcfm_submit_button" />
						</div>
						<div class="wcfm-clearfix"></div>
					</div>
				</div>
			</form>
			<?php do_action( 'after_wcfm_enquiry_reply_form' ); ?>
			<div class="wcfm-clearfix"></div><br />
		<?php } ?>
		
		<?php do_action( 'end_wcfm_enquiry_manage_form' ); ?>
		
		<?php
		do_action( 'after_wcfm_enquiry_manage' );
		?>
	</div>
</div>