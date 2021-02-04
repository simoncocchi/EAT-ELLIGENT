<?php
/**
 * WCFM plugin view
 *
 * WCFM Marketplace Product Reviews List View
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/views/reviews/
 * @version   1.0.0
 */
 
global $WCFM, $WCFMmp;

$wcfm_is_allow_reviews = apply_filters( 'wcfm_is_allow_reviews', true );
if( !$wcfm_is_allow_reviews || !apply_filters( 'wcfm_is_allow_manage_product_reviews', true ) ) {
	wcfm_restriction_message_show( "Reviews" );
	return;
}

$vendor_id = $WCFMmp->vendor_id;
$wcfmu_reviews_menus = apply_filters( 'wcfmu_reviews_menus', array( 'approved' => __( 'Approved', 'wc-multivendor-marketplace'), 
																																		'pending'  => __( 'Pending', 'wc-multivendor-marketplace'),
																																	) );
	
$reviews_status = ! empty( $_GET['reviews_status'] ) ? sanitize_text_field( $_GET['reviews_status'] ) : '';

if( wcfm_is_vendor() ) {
	//$review_counts['approved'] = $WCFMmp->wcfmmp_reviews->get_vendor_reviews_count( $vendor_id );
	//$review_counts['pending']  = $WCFMmp->wcfmmp_reviews->get_vendor_reviews_count( $vendor_id, 'pending' );
} else {
	//$review_counts['approved'] = $WCFMmp->wcfmmp_reviews->get_vendor_reviews_count();
	//$review_counts['pending']  = $WCFMmp->wcfmmp_reviews->get_vendor_reviews_count( 0, 'pending' );
}
?>
<div class="collapse wcfm-collapse" id="wcfm_reviews_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-comment-alt"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Product Reviews', 'wc-multivendor-marketplace' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<ul class="wcfm_reviews_menus">
			  <li class="wcfm_reviews_menu_item">
			    <a class="<?php echo ( !$reviews_status ) ? 'active' : ''; ?>" href="<?php echo wcfm_product_reviews_url(); ?>"><?php _e( 'All', 'wc-multivendor-marketplace' ); ?></a>
			  </li>
				<?php
				foreach( $wcfmu_reviews_menus as $wcfmu_reviews_menu_key => $wcfmu_reviews_menu) {
					?>
					<li class="wcfm_reviews_menu_item">
						<?php
						echo " | ";
						?>
						<a class="<?php echo ( $wcfmu_reviews_menu_key == $reviews_status ) ? 'active' : ''; ?>" href="<?php echo wcfm_product_reviews_url( $wcfmu_reviews_menu_key ); ?>"><?php echo $wcfmu_reviews_menu; ?></a>
					</li>
					<?php
				}
				?>
			</ul>
			
			<?php
			echo '<a id="add_new_product_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_reviews_url().'" data-tip="' . __('Store Reviews', 'wc-multivendor-marketplace') . '"><span class="wcfmfa fa-user"></span><span class="text">' . __( 'Store Reviews', 'wc-multivendor-marketplace' ) . '</span></a>';
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'before_wcfm_reviews' ); ?>
	  
		<div class="wcfm_reviews_filter_wrap wcfm_filters_wrap">
			<?php 
			// Vendor Filter
			if( !wcfm_is_vendor() ) {
				$vendor_arr = array();
				$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																									"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																									 ) );
			}
			
			// Product Filter
			$WCFM->wcfm_fields->wcfm_generate_form_field( array( "review_product" => array( 'type' => 'select', 'attributes' => array( 'style' => 'width: 150px;' ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'options' => array() ) ) );
			?>
		</div>
			
		<div class="wcfm-container">
			<div id="wcfm_reviews_listing_expander" class="wcfm-content">
				<table id="wcfm-reviews" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-multivendor-marketplace' ); ?>"></span></th>
							<th><?php _e( 'Author', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Comment', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Rating', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Product', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Dated', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Actions', 'wc-multivendor-marketplace' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-multivendor-marketplace' ); ?>"></span></th>
							<th><?php _e( 'Author', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Comment', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Rating', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Product', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ); ?></th>
							<th><?php _e( 'Dated', 'wc-multivendor-marketplace' ); ?></th>
							<th><?php _e( 'Actions', 'wc-multivendor-marketplace' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_reviews' );
		?>
	</div>
</div>