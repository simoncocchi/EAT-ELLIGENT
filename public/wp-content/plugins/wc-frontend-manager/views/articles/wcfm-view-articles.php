<?php
global $WCFM, $wp_query;

$wcfm_is_allow_manage_articles = apply_filters( 'wcfm_is_allow_manage_articles', true );
if( !$wcfm_is_allow_manage_articles ) {
	wcfm_restriction_message_show( "Articles" );
	return;
}

$wcfmu_articles_menus = apply_filters( 'wcfmu_articles_menus', array( 'any' => __( 'All', 'wc-frontend-manager'), 
																																			'publish' => __( 'Published', 'wc-frontend-manager'),
																																			'draft' => __( 'Draft', 'wc-frontend-manager'),
																																			'pending' => __( 'Pending', 'wc-frontend-manager')
																																		) );

$article_status = ! empty( $_GET['article_status'] ) ? sanitize_text_field( $_GET['article_status'] ) : 'any';

$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
if( current_user_can( 'administrator' ) ) $current_user_id = 0;
$count_articles = array();
$count_articles['publish'] = wcfm_get_user_posts_count( $current_user_id, 'post', 'publish' );
$count_articles['pending'] = wcfm_get_user_posts_count( $current_user_id, 'post', 'pending' );
$count_articles['draft']   = wcfm_get_user_posts_count( $current_user_id, 'post', 'draft' );
$count_articles['any']     = $count_articles['publish'] + $count_articles['pending'] + $count_articles['draft'];

?>

<div class="collapse wcfm-collapse" id="wcfm_articles_listing">
	
	<div class="wcfm-page-headig">
		<span class="wcfmfa fa-file-alt"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Articles', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_articles' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<ul class="wcfm_articles_menus">
				<?php
				$is_first = true;
				foreach( $wcfmu_articles_menus as $wcfmu_articles_menu_key => $wcfmu_articles_menu) {
					?>
					<li class="wcfm_articles_menu_item">
						<?php
						if($is_first) $is_first = false;
						else echo " | ";
						?>
						<a class="<?php echo ( $wcfmu_articles_menu_key == $article_status ) ? 'active' : ''; ?>" href="<?php echo get_wcfm_articles_url( $wcfmu_articles_menu_key ); ?>"><?php echo $wcfmu_articles_menu . ' ('. $count_articles[$wcfmu_articles_menu_key] .')'; ?></a>
					</li>
					<?php
				}
				?>
			</ul>
			
			<?php
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('edit.php?post_type=post'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
				<?php
			}
			
			if( $has_new = apply_filters( 'wcfm_add_new_article_sub_menu', true ) ) {
				echo '<a id="add_new_article_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_articles_manage_url().'" data-tip="' . __('Add New Article', 'wc-frontend-manager') . '"><span class="wcfmfa fa-file-pdf"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager') . '</span></a>';
			}
			?>
			
			<?php	echo apply_filters( 'wcfm_articles_limit_label', '' ); ?>
			
			<div class="wcfm-clearfix"></div>
		</div>
		<div class="wcfm-clearfix"></div><br />
		
		<div class="wcfm_articles_filter_wrap wcfm_products_filter_wrap  wcfm_filters_wrap">
			<?php	
			// Category Filtering
			if( $wcfm_is_articles_category_filter = apply_filters( 'wcfm_is_articles_category_filter', true ) ) {
				$article_categories   = get_terms( 'category', 'orderby=name&hide_empty=0&parent=0' );
				$categories = array();
				
				echo '<select id="dropdown_article_cat" name="dropdown_article_cat" class="dropdown_article_cat" style="width: 150px;">';
					echo '<option value="" selected="selected">' . __( 'Select a category', 'wc-frontend-manager' ) . '</option>';
					if ( $article_categories ) {
						$WCFM->library->generateTaxonomyHTML( 'category', $article_categories, $categories );
					}
				echo '</select>';
			}
			
			if( $wcfm_is_articles_vendor_filter = apply_filters( 'wcfm_is_articles_vendor_filter', true ) ) {
				$is_marketplace = wcfm_is_marketplace();
				if( $is_marketplace ) {
					if( !wcfm_is_vendor() ) {
						$vendor_arr = array(); //$WCFM->wcfm_vendor_support->wcfm_get_vendor_list();
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
					}
				}
			}
			?>
		</div>
		
		<div class="wcfm-container">
			<div id="wcfm_articles_listing_expander" class="wcfm-content">
				<table id="wcfm-articles" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><span class="wcfmfa fa-image text_tip" data-tip="<?php _e( 'Image', 'wc-frontend-manager' ); ?>"></span></th>
							<th style="max-width: 250px;"><?php _e( 'Name', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Status', 'wc-frontend-manager' ); ?></th>
							<th><span class="wcfmfa fa-eye text_tip" data-tip="<?php _e( 'Views', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><span class="wcfmfa fa-image text_tip" data-tip="<?php _e( 'Image', 'wc-frontend-manager' ); ?>"></span></th>
							<th style="max-width: 250px;"><?php _e( 'Name', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Status', 'wc-frontend-manager' ); ?></th>
							<th><span class="wcfmfa fa-eye text_tip" data-tip="<?php _e( 'Views', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Actions', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_articles' );
		?>
	</div>
</div>