<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Media List View
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/views/media/
 * @version   1.0.0
 */
 
global $WCFM, $WCFMmp;

$wcfm_is_allow_media = apply_filters( 'wcfm_is_allow_media', true );
if( !$wcfm_is_allow_media ) {
	wcfm_restriction_message_show( "Media" );
	return;
}

$vendor_id = $WCFMmp->vendor_id;
?>
<div class="collapse wcfm-collapse" id="wcfm_media_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-images"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Media Manager', 'wc-multivendor-marketplace' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Media Manager', 'wc-multivendor-marketplace' ); ?></h2>
			
			<?php
			if( wcfm_is_vendor() ) {
				echo '<span class="wcfm_disk_limit_label">' . __('Total Disk Space Usage: ', 'wc-multivendor-marketplace' ) . $WCFM->wcfm_vendor_support->wcfm_vendor_space_limit_stat( $vendor_id ) . '</span>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'before_wcfm_media' ); ?>
	  
		<div class="wcfm_media_filter_wrap wcfm_filters_wrap">
			<?php if( apply_filters( 'wcfm_is_allow_delete_media', true ) ) { ?>
				<input type="submit" id="wcfm_bulk_mark_delete" class="wcfm_bulk_mark_delete wcfm_submit_button" value="<?php _e( 'Bulk Delete', 'wc-multivendor-marketplace' ); ?>" />
			<?php } ?>
			<?php 
			if( !wcfm_is_vendor() ) {
				$vendor_arr = array();
				$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																									"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																									 ) );
			}
			?>
		</div>
			
		<div class="wcfm-container">
			<div id="wcfm_media_listing_expander" class="wcfm-content">
				<table id="wcfm-media" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <th>
								<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select all for delete', 'wc-multivendor-marketplace' ); ?>" />
						  </th>
						  <th><span class="wcfmfa fa-image text_tip" data-tip="<?php _e( 'Media', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'File', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Associate', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Size', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Actions', 'wc-multivendor-marketplace' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <th>
								<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_all text_tip" name="bulk_action_checkbox_all_top" value="yes" data-tip="<?php _e( 'Select all for delete', 'wc-multivendor-marketplace' ); ?>" />
						  </th>
						  <th><span class="wcfmfa fa-image text_tip" data-tip="<?php _e( 'Media', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'File', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Associate', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Size', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Actions', 'wc-multivendor-marketplace' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_media' );
		?>
	</div>
</div>