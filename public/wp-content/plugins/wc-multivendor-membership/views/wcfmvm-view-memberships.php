<?php
/**
 * WCFM plugin view
 *
 * WCFMgs Memberships List View
 *
 * @author 		WC Lovers
 * @package 	wcfmgs/view
 * @version   1.0.0
 */
 
global $WCFM, $WCFMu, $WCFMgs;

$wcfm_is_allow_membership = apply_filters( 'wcfm_is_allow_membership', true );
if( !$wcfm_is_allow_membership || !apply_filters( 'wcfm_is_allow_manage_groups', true ) ) {
	wcfm_restriction_message_show( "Memberships" );
	return;
}

?>
<div class="collapse wcfm-collapse" id="wcfm_memberships_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-plus"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Memberships', 'wc-multivendor-membership' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
	    <h2><?php _e( 'Manage Memberships', 'wc-multivendor-membership' ); ?></h2>
		
			<?php
			echo '<a class="wcfm_gloabl_settings text_tip" href="'.get_wcfm_memberships_settings_url().'" data-tip="' . __('Settings', 'wc-frontend-manager') . '"><span class="wcfmfa fa-cog"></span></a>';
			if( $has_new = apply_filters( 'wcfm_add_new_membership_sub_menu', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_memberships_manage_url().'" data-tip="' . __('Add New Membership', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager' ) . '</span></a>';
			}
			echo '<a class="add_new_wcfm_ele_dashboard text_tip" target="_blank" href="'.get_wcfm_membership_url().'" data-tip="' . __('Membership Plan Table', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-eye"></span></a>';
			echo '<a class="add_new_wcfm_ele_dashboard wcfm_tutorials text_tip" target="_blank" href="https://www.youtube.com/embed/0l9RAgUpV2w" data-tip="' . __('Tutorial', 'wc-multivendor-membership') . '"><span class="wcfmfa fa-video"></span></a>';
			?>
			
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
		<?php do_action( 'before_wcfm_memberships' ); ?>
		
		<div class="wcfm-container">
			<div id="wcfm_memberships_listing_expander" class="wcfm-content">
				<table id="wcfm-memberships" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th style="max-width: 250px;"><?php _e( 'Membership', 'wc-multivendor-membership' ); ?></th>
							<th><?php _e( 'Details', 'wc-multivendor-membership' ); ?></th>
							<th><?php _e( 'Pay Mode', 'wc-multivendor-membership' ); ?></th>
							<th><?php _e( 'Group', 'wc-multivendor-membership' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-multivendor-membership' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="max-width: 250px;"><?php _e( 'Membership', 'wc-multivendor-membership' ); ?></th>
							<th><?php _e( 'Details', 'wc-multivendor-membership' ); ?></th>
							<th><?php _e( 'Pay Mode', 'wc-multivendor-membership' ); ?></th>
							<th><?php _e( 'Group', 'wc-multivendor-membership' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-multivendor-membership' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_memberships' );
		?>
	</div>
</div>