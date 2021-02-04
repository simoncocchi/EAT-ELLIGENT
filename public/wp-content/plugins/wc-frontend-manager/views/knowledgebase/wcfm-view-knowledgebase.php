<?php
/**
 * WCFMu plugin view
 *
 * WCFM Knowledgebase view
 *
 * @author 		WC Lovers
 * @package 	wcfm/views
 * @version   2.3.2
 */
 
global $WCFM;


if( !apply_filters( 'wcfm_is_pref_knowledgebase', true ) || !apply_filters( 'wcfm_is_allow_knowledgebase', true ) ) {
	wcfm_restriction_message_show( "Knowledgesbase" );
	return;
}

?>

<div class="collapse wcfm-collapse" id="wcfm_knowledgebase_listing">
	
	<div class="wcfm-page-headig">
		<span class="wcfmfa fa-book"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Knowledgebase', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('Guidelines for Store Users', 'wc-frontend-manager' ); ?></h2>
			
			<?php
			if( !wcfm_is_vendor() ) {
				if( $has_new = apply_filters( 'wcfm_add_new_knowledgebase_sub_menu', true ) ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_knowledgebase_manage_url().'" data-tip="' . __('Add New Knowledgebase', 'wc-frontend-manager') . '"><span class="wcfmfa fa-book"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager' ) . '</span></a>';
				}
			}
			?>
		
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	 <div class="wcfm_products_filter_wrap wcfm_filters_wrap">
			<?php	
			// Buk Edit Button action 
			do_action( 'wcfm_knowledgebase_filters_before' );
			
			// Category Filtering
			$knowledgebase_categories   = get_terms( 'wcfm_knowledgebase_category', 'orderby=name&hide_empty=0&parent=0' );
			$categories = array();
			
			echo '<select id="dropdown_knowledgebase_cat" name="dropdown_knowledgebase_cat" class="dropdown_knowledgebase_cat" style="width: 150px;">';
				echo '<option value="" selected="selected">' . __( 'Show all category', 'wc-frontend-manager' ) . '</option>';
				if ( $knowledgebase_categories ) {
					$WCFM->library->generateTaxonomyHTML( 'wcfm_knowledgebase_category', $knowledgebase_categories, $categories );
				}
			echo '</select>';
			?>
		</div>
	  
		<?php do_action( 'before_wcfm_knowledgebase' ); ?>
		<div class="wcfm-container">
			<div id="wcfm_knowledgebase_listing_expander" class="wcfm-content">
				<table id="wcfm-knowledgebase" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <?php if( wcfm_is_vendor() ) { ?>
						    <th></th>
						  <?php } else { ?>
						  	<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
						  <?php } ?>
							<th style="max-width: 300px;"><?php _e( 'Title', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Category', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <?php if( wcfm_is_vendor() ) { ?>
						    <th></th>
						  <?php } else { ?>
						  	<th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
						  <?php } ?>
							<th style="max-width: 300px;"><?php _e( 'Title', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Category', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php do_action( 'after_wcfm_knowledgebase' ); ?>
	</div>
</div>
