<?php
/**
 * WCFM plugin view
 *
 * wcfm Knowledgebase Manage View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view
 * @version   1.0.0
 */
 
global $wp, $WCFM, $WCFMu;

if( !apply_filters( 'wcfm_is_pref_knowledgebase', true ) || !apply_filters( 'wcfm_is_allow_knowledgebase', true ) || !apply_filters( 'wcfm_is_allow_manage_knowledgebase', true ) || wcfm_is_vendor() ) {
	wcfm_restriction_message_show( "Manage Knowledgebase" );
	return;
}

$knowledgebase_id = 0;
$title = '';
$content = '';

$categories = array();

if( isset( $wp->query_vars['wcfm-knowledgebase-manage'] ) && !empty( $wp->query_vars['wcfm-knowledgebase-manage'] ) ) {
	$knowledgebase_post = get_post( $wp->query_vars['wcfm-knowledgebase-manage'] );
	// Fetching Knowledgebase Data
	if($knowledgebase_post && !empty($knowledgebase_post)) {
		$knowledgebase_id = $wp->query_vars['wcfm-knowledgebase-manage'];
		
		$title = $knowledgebase_post->post_title;
		$content = $knowledgebase_post->post_content;
		
		// Knowledgebase Categories
		$pcategories = get_the_terms( $knowledgebase_id, 'wcfm_knowledgebase_category' );
		if( !empty($pcategories) ) {
			foreach($pcategories as $pkey => $pcategory) {
				$categories[] = $pcategory->term_id;
			}
		} else {
			$categories = array();
		}
		
	}
}

$knowledgebase_categories   = get_terms( 'wcfm_knowledgebase_category', 'orderby=name&hide_empty=0&parent=0' );

do_action( 'before_wcfm_knowledgebase_manage' );

?>

<div class="collapse wcfm-collapse">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-book"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Manage Knowledgebase', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
	    <h2><?php if( $knowledgebase_id ) { _e('Edit Knowledgebase', 'wc-frontend-manager' ); } else { _e('Add Knowledgebase', 'wc-frontend-manager' ); } ?></h2>
			
			<?php
			echo '<a id="add_new_knowledgebase_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_knowledgebase_url().'" data-tip="' . __('Knowledgebase', 'wc-frontend-manager') . '"><span class="wcfmfa fa-book"></span><span class="text">' . __( 'Knowledgebase', 'wc-frontend-manager') . '</span></a>';
			?>
			<div class="wcfm-clearfix"></div>
	  </div>
	  <div class="wcfm-clearfix"></div><br />
	  
		<form id="wcfm_knowledgebase_manage_form" class="wcfm">
		
			<?php do_action( 'begin_wcfm_knowledgebase_manage_form' ); ?>
			
			<!-- collapsible -->
			<div class="wcfm-container">
				<div id="wcfm_knowledgebase_manage_form_general_expander" class="wcfm-content">
				  <div class="wcfm_knowledgebase_manager_general_fields">
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'knowledgebase_manager_fields_general', array(  "title" => array( 'placeholder' => __('Title', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_full_ele wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $title),
																																															"wcfm_knowledgebase" => array( 'label' => __('Details', 'wc-frontend-manager') , 'type' => 'wpeditor', 'class' => 'wcfm-textarea wcfm_ele wcfm_wpeditor', 'label_class' => 'wcfm_title', 'rows' => 20, 'value' => $content),
																																															"knowledgebase_id" => array('type' => 'hidden', 'value' => $knowledgebase_id)
																																					) ) );
						?>
					</div>
					<div class="wcfm_knowledgebase_manager_gallery_fields">
					  <div class="wcfm_knowledgebase_manager_cats_checklist_fields">
							<p class="wcfm_title wcfm_full_ele"><strong><?php _e( 'Categories', 'wc-frontend-manager' ); ?></strong></p><label class="screen-reader-text" for="article_cats"><?php _e( 'Categories', 'wc-frontend-manager' ); ?></label>
							<ul id="knowledgebase_cats_checklist" class="knowledgebase_taxonomy_checklist wcfm_ele">
								<?php
									if ( $knowledgebase_categories ) {
										$WCFM->library->generateTaxonomyHTML( 'wcfm_knowledgebase_category', $knowledgebase_categories, $categories, '', true );
									}
								?>
							</ul>
						</div>
						<div class="wcfm_clearfix"></div>
						<div class="wcfm_add_new_category_box wcfm_add_new_taxonomy_box">
							<p class="description wcfm_full_ele wcfm_side_add_new_category wcfm_add_new_category wcfm_add_new_taxonomy">+<?php _e( 'Add new category', 'wc-frontend-manager' ); ?></p>
							<div class="wcfm_add_new_taxonomy_form wcfm_add_new_taxonomy_form_hide">
								<?php 
								$WCFM->wcfm_fields->wcfm_generate_form_field( array( "wcfm_new_cat" => array( 'type' => 'text', 'class' => 'wcfm-text wcfm_new_tax_ele wcfm_full_ele' ) ) ); 
								$args = array(
															'show_option_all'    => '',
															'show_option_none'   => __( '-- Parent category --', 'wc-frontend-manager' ),
															'option_none_value'  => '0',
															'hide_empty'         => 0,
															'hierarchical'       => 1,
															'name'               => 'wcfm_new_parent_cat',
															'class'              => 'wcfm-select wcfm_new_parent_taxt_ele wcfm_full_ele',
															'taxonomy'           => 'wcfm_knowledgebase_category',
														);
								wp_dropdown_categories( $args );
								?>
								<button type="button" data-taxonomy="wcfm_knowledgebase_category" class="button wcfm_add_category_bt wcfm_add_taxonomy_bt"><?php _e( 'Add', 'wc-frontend-manager' ); ?></button>
								<div class="wcfm_clearfix"></div>
							</div>
						</div>
						<div class="wcfm_clearfix"></div>
					</div>
				</div>
			</div>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<?php do_action( 'end_wcfm_knowledgebase_manage_form' ); ?>
			
			<div class="wcfm-message" tabindex="-1"></div>
			
			<div id="wcfm_knowledgebase_manager_submit">
				<input type="submit" name="knowledgebase-manager-data" value="<?php _e( 'Submit', 'wc-frontend-manager' ); ?>" id="wcfm_knowledgebase_manager_submit_button" class="wcfm_submit_button" />
			</div>
			<?php
			do_action( 'after_wcfm_knowledgebase_manage' );
			?>
		</form>
	</div>
</div>