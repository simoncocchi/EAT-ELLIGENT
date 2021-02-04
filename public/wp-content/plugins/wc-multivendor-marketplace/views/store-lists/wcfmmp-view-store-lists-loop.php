<?php
/**
 * The Template for displaying store sidebar category.
 *
 * @package WCfM Markeplace Views Store List Loop
 *
 * For edit coping this to yourtheme/wcfm/store-lists
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$all_stores = $WCFMmp->wcfmmp_vendor->wcfmmp_search_vendor_list( true, '', '', $search_query, $search_category, $search_data, $has_product, $includes );
$user_count   = count($all_stores);
$num_of_pages = ceil( $user_count / $limit );

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
		//'has_city'        => $has_city,
		//'has_zip'         => $has_zip,
		'search_query'    => $search_query,
		'search_category' => $search_category,
		//'store_category'  => $store_category,
		'pagination_base' => $pagination_base,
		'num_of_pages'    => $num_of_pages,
		'orderby'         => $orderby,
		'has_orderby'     => $has_orderby,
		'has_product'     => $has_product,
		'theme'           => $theme,
		'search_data'     => $search_data
);

?>

<div class="wcfmmp-stores-content-holder">
	<div class="wcfmmp-stores-content">
	
	  <?php if ( $has_orderby && apply_filters( 'wcfm_is_allow_store_list_orderby', true ) ) { $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-orderby.php', $args ); } ?>
	
		<?php if ( !empty( $stores )  ) : ?>
			<ul class="wcfmmp-store-wrap">
				<?php
				foreach ( $stores as $store_id => $store_name ) {
					$args['store_id'] = $store_id;
					$WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-card.php', $args );
				}
				?>
				<div class="wcfm-clearfix"></div>
			</ul> <!-- .wcfmmp-store-wrap -->

			<?php if ( $num_of_pages > 1 ) { $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-pagination.php', $args ); } ?>

		<?php else:  ?>
			<p class="wcfmmp-error"><?php _e( 'No store found!', 'wc-multivendor-marketplace' ); ?></p>
		<?php endif; ?>
	</div>
</div>