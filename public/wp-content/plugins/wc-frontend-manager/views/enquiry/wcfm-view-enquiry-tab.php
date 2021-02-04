<?php
/**
 * WCFM plugin view
 *
 * wcfm Enquiry Tab View
 * This template can be overridden by copying it to yourtheme/wcfm/enquiry/
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/enquiry
 * @version   3.2.8
 */
 
global $wp, $WCFM, $WCFMu, $post, $wpdb;

$product_id = $post->ID;
$vendor_id  = 0;

if( !$product_id ) return;

$vendor_id = 0;
$vendor_id = wcfm_get_vendor_id_by_post( $product_id );

$wcfm_options = $WCFM->wcfm_options;
$wcfm_enquiry_button_label  = isset( $wcfm_options['wcfm_enquiry_button_label'] ) ? $wcfm_options['wcfm_enquiry_button_label'] : __( 'Ask a Question', 'wc-frontend-manager' );

?>

<?php
// Fetching existing Enquries
if( apply_filters( 'wcfm_is_pref_enquiry_tab', true ) ) {
	$enquiries = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wcfm_enquiries WHERE `is_private` = 0 AND `reply` != '' AND `product_id` = {$product_id} ORDER BY `ID` DESC" );
	?>
	
	<h2 class="wcfm-enquiries-heading"><?php _e( 'General Enquiries', 'wc-frontend-manager' ); ?></h2>
	
	<?php
	if( empty( $enquiries ) ) {
		?>
		<p class="woocommerce-noreviews wcfm-noenquiries"><?php _e( 'There are no enquiries yet.', 'wc-frontend-manager' ); ?></p>
	<?php } ?>	


	<?php if( !apply_filters( 'wcfm_is_pref_enquiry_button', true ) ) { ?>
		<div class="wcfm-clearfix"></div>
		<p><span class="add_enquiry" data-store="<?php echo $vendor_id; ?>" data-product="<?php echo $product_id; ?>"><span class="wcfmfa fa-question-circle fa-question-circle"></span>&nbsp;<span class="add_enquiry_label"><?php _e( $wcfm_enquiry_button_label, 'wc-frontend-manager' ); ?></span></span></p>
		<div class="wcfm-clearfix"></div>
	<?php } ?>
<?php } ?>
	
<?php 
if( apply_filters( 'wcfm_is_pref_enquiry_tab', true ) ) {
	if( !empty( $enquiries ) ) {
		?><p class="woocommerce-noreviews wcfm-enquiries-count"><?php echo count( $enquiries ) . ' ' . __( 'Enquiries', 'wc-frontend-manager' ); ?></p><?php
		echo '<div id="reviews" class="wcfm_enquiry_reviews enquiry_reviews"><ol class="wcfm_enquiry_list commentlist">';
		foreach( $enquiries as $enquiry_data ) {
			?>
			<li class="wcfm_enquiry_item comment byuser comment-author-vnd bypostauthor even thread-even depth-1" id="li-enquiry-<?php echo $enquiry_data->ID; ?>">
				<div id="enquiry-<?php echo $enquiry_data->ID; ?>" class="wcfm_enquiry_container comment_container">
					<div class="comment-text">
						<div class="enquiry-by"><span style="width:60%"><span class="wcfmfa fa-clock"></span> <?php echo date_i18n( wc_date_format(), strtotime( $enquiry_data->posted ) ); ?></span></div>
						<p class="meta">
							<strong class="woocommerce-review__author"><?php echo $enquiry_data->enquiry; ?></strong> 
							<?php if( apply_filters( 'wcfm_is_allow_enquery_tab_customer_show', true ) ) { ?>
								<span class="woocommerce-review__dash">&ndash;</span> 
								<time class="woocommerce-review__published-date"><?php _e( 'by', 'wc-frontend-manager' ); ?> <?php echo apply_filters( 'wcfm_enquiry_customer_name_display',  $enquiry_data->customer_name, $enquiry_data->customer_id, $enquiry_data->ID ); ?></time>
							<?php } ?>
						</p>
						<div class="description">
							<?php
							echo $enquiry_data->reply;
							
							if( $enquiry_data->reply_by && apply_filters( 'wcfm_is_allow_enquiry_tab_reply_by_show', false ) ) {
								echo '<time class="woocommerce-review__published-date">';
								_e( 'Reply by', 'wc-frontend-manager' );
								echo '&nbsp;' . wcfm_get_vendor_store( $enquiry_data->reply_by );
								echo '</time>';
							}
							?>
						</div>
					</div>
				</div>
			</li><!-- #comment-## -->
		<?php
		}
		echo '</ol></div><div class="wcfm-clearfix"></div>';
	}
} 
?>