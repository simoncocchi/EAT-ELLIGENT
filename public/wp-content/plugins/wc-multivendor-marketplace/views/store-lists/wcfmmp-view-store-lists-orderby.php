<?php
/**
 * The Template for displaying store sidebar category.
 *
 * @package WCfM Markeplace Views Store Order BY
 *
 * For edit coping this to yourtheme/wcfm/store-lists
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$args = array(
	  'user_count'      => $user_count,
		'stores'          => $stores,
		'per_row'         => $per_row,
		'limit'           => $limit,
		'offset'          => $offset,
		'paged'           => $paged,
		'filter'          => $filter,
		'search'          => $search,
		'category'        => $category,
		'country'         => $country,
		'state'           => $state,
		'search_query'    => $search_query,
		'search_category' => $search_category,
		'pagination_base' => $pagination_base,
		'num_of_pages'    => $num_of_pages,
		'orderby'         => $orderby,
		'has_product'     => $has_product,
		'search_data'     => $search_data
);

if( isset( $_GET['orderby'] ) ) { $orderby = sanitize_text_field($_GET['orderby']); }

?>

<div class="wcfmmp-store-lists-sorting">
  <form class="wcfm-woocommerce-ordering" action="" method="get">
		<select id="wcfmmp_store_orderby" name="orderby" class="orderby">
			<option value="newness_asc" <?php selected( $orderby, 'newness_asc' ); ?>><?php _e( 'Sort by newness: old to new', 'wc-multivendor-marketplace' ); ?></option>
			<option value="newness_desc" <?php selected( $orderby, 'newness_desc' ); ?>><?php _e( 'Sort by newness: new to old', 'wc-multivendor-marketplace' ); ?></option>
			<option value="rating_asc" <?php selected( $orderby, 'rating_asc' ); ?>><?php _e( 'Sort by average rating: low to high', 'wc-multivendor-marketplace' ); ?></option>
			<option value="rating_desc" <?php selected( $orderby, 'rating_desc' ); ?>><?php _e( 'Sort by average rating: high to low', 'wc-multivendor-marketplace' ); ?></option>
			<option value="alphabetical_asc" <?php selected( $orderby, 'alphabetical_asc' ); ?>><?php _e( 'Sort Alphabetical: A to Z', 'wc-multivendor-marketplace' ); ?></option>
			<option value="alphabetical_desc" <?php selected( $orderby, 'alphabetical_desc' ); ?>><?php _e( 'Sort Alphabetical: Z to A', 'wc-multivendor-marketplace' ); ?></option>
		</select>
		
		<?php
		if( !empty( $search_data ) ) {
			foreach( $search_data as $search_key => $search_value ) {
				if( in_array( $search_key, array( 'search_term', 'wcfmmp_store_search', 'wcfmmp_store_category', 'pagination_base', 'wcfm_paged', 'paged', 'per_row', 'per_page', 'excludes', 'orderby', 'has_product', 'nonce' ) ) ) continue;
				echo '<input type="hidden" name="'.$search_key.'" value="'.$search_value.'" />';
			}
		}
		?>
		<input type="hidden" name="wcfmmp_store_search" value="<?php echo $search_query; ?>" />
		<input type="hidden" name="wcfmmp_store_category" value="<?php echo $search_category; ?>" />
		<input type="hidden" name="wcfmsc_store_categories" value="<?php echo isset( $search_data['wcfmsc_store_categories'] ) ? $search_data['wcfmsc_store_categories'] : ''; ?>" />
		<input type="hidden" name="paged" value="1">
	</form>
	<p class="woocommerce-result-count">
		<?php printf( __( 'Showing %s–%s of %s results', 'wc-multivendor-marketplace' ), ($offset+1), ( $offset + count($stores) ), $user_count ); ?>
	</p>
	
	<?php if ( !empty( $stores ) && ( $num_of_pages > 1 ) ) { $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-pagination.php', $args ); } ?>
	
	<div class="spacer"></div>
</div>