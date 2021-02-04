<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCFM Marketplace Media Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/media/wcfmmp/controllers
 * @version   1.0.0
 */

class WCFMmp_Media_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$vendor_id = $WCFMmp->vendor_id;
		
		$length = sanitize_text_field( $_POST['length'] );
		$offset = sanitize_text_field( $_POST['start'] );
		
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => 'attachment',
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => 'any',
							'suppress_filters' => 0 
						);
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) $args['s'] = sanitize_text_field( $_POST['search']['value'] );
		
		$args = apply_filters( 'wcfm_media_args', $args );
		
		if( wcfm_is_vendor() ) {
			$args['author'] = $vendor_id;
		} else {
			if ( ! empty( $_POST['media_vendor'] ) ) {
				$args['author'] = sanitize_text_field( $_POST['media_vendor'] );
			}
		}
		
		$wcfm_media_array = get_posts( $args );
		
		// Get Media Count
		$filtered_coupon_count = 0;
		$args['posts_per_page'] = -1;
		$args['offset'] = 0;
		$wcfm_filterd_media_array = get_posts( $args );
		$filtered_media_count = count($wcfm_filterd_media_array);
		
		// Generate Media JSON
		$wcfm_media_json = '';
		$wcfm_media_json = '{
															"draw": ' . sanitize_text_field( $_POST['draw'] ) . ',
															"recordsTotal": ' . $filtered_media_count . ',
															"recordsFiltered": ' . $filtered_media_count . ',
															"data": ';
		if(!empty($wcfm_media_array)) {
			$index = 0;
			$wcfm_media_json_arr = array();
			foreach( $wcfm_media_array as $wcfm_media_single ) {
				
				// Bulk Delete
				if( apply_filters( 'wcfm_is_allow_delete_media', true ) ) {
					$wcfm_media_json_arr[$index][] =  '<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_single" name="bulk_action_checkbox[]" value="' . $wcfm_media_single->ID . '" />';
				} else {
					$wcfm_media_json_arr[$index][] = '';
				}
				
				// Media
				$type = get_post_mime_type( $wcfm_media_single->ID );
				$base = $WCFM->plugin_url . "assets/images/media/";
				$media = '';
				switch ($type) {
					case 'image/jpeg':
					case 'image/png':
					case 'image/gif':
						$media = wp_get_attachment_url( $wcfm_media_single->ID );
					break;
					case 'audio/mp3': 
						$media =  $base . "audio.png"; 
					break;
					case 'video/mpeg':
					case 'video/mp4': 
					case 'video/quicktime':
						$media =  $base . "video.png"; 
					break;
					case 'text/plain': 
						$media =  $base . "text.png"; 
					break;
					case 'text/csv':
					case 'text/xml':
						$media =  $base . "spreadsheet.png"; 
					break;
					case 'application/pdf':
						$media =  $base . "document.png"; 
					break;
					default:
						$media =  $base . "default.png";
				}
				$wcfm_media_json_arr[$index][] = '<a class="wcfmmp-author-img" target="_blank" href="' . wp_get_attachment_url( $wcfm_media_single->ID ) . '" ><img width="75" src="' . $media. '" /></a>';
				
				// File
				$wcfm_media_json_arr[$index][] = '<span class="wcfmmp_media_name">' . $wcfm_media_single->post_title . "</span><br />(" . $type . ")";
				
        // Associate
        if( $wcfm_media_single->post_parent ) {
					$wcfm_media_json_arr[$index][] = '<a class="wcfmmp-author-img" target="_blank" href="' . get_permalink( $wcfm_media_single->post_parent ) . '" >' . get_the_title( $wcfm_media_single->post_parent ) . '</a>';
				} else {
					$wcfm_media_json_arr[$index][] = '&ndash;';
				}
        
        // Store
        if( $wcfm_media_single->post_author && wcfm_is_vendor($wcfm_media_single->post_author) ) {
					$wcfm_media_json_arr[$index][] = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( absint($wcfm_media_single->post_author) );
				} else {
					$wcfm_media_json_arr[$index][] = '&ndash;';
				}
        
        
        // Size
        $attached_file = get_attached_file( $wcfm_media_single->ID );
				if( file_exists( $attached_file ) ) {
					$wcfm_media_json_arr[$index][] = round( filesize( $attached_file )/1024, 2 ) . ' KB';
				} else {
					$wcfm_media_json_arr[$index][] = '&ndash;';
				}
        
				// Status
				$actions = '<a class="wcfm-action-icon" target="_blank" href="' . wp_get_attachment_url( $wcfm_media_single->ID ) . '" ><span class="wcfmfa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wc-frontend-manager' ) . '"></span></a>';;
				
				if( apply_filters( 'wcfm_is_allow_delete_media', true ) ) {
					$actions .= '<a class="wcfm_media_dalete wcfm-action-icon" href="#" data-mediaid="' . $wcfm_media_single->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="' . esc_attr__( 'Delete', 'wc-multivendor-marketplace' ) . '"></span></a>';
				}
				$wcfm_media_json_arr[$index][] =  $actions;
				
				$index++;
			}												
		}
		if( !empty($wcfm_media_json_arr) ) $wcfm_media_json .= json_encode($wcfm_media_json_arr);
		else $wcfm_media_json .= '[]';
		$wcfm_media_json .= '
													}';
													
		echo $wcfm_media_json;
	}
}