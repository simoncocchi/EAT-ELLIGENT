<?php
/**
 * The Template for displaying store list pagination.
 *
 * @package WCfM Markeplace Views Store Lists Pagination
 *
 * For edit coping this to yourtheme/wcfm/store/store-lists
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $post;
?>

<div class="wcfmmp-pagination clearfix">

  <?php
	$pagination_args = array(
		'current'   => $paged,
		'total'     => $num_of_pages,
		'base'      => $pagination_base,
		'type'      => 'list',
		'prev_text' => __( '&laquo;', 'wc-multivendor-marketplace' ),
		'next_text' => __( '&raquo;', 'wc-multivendor-marketplace' ),
	);

	if ( ! empty( $search_query ) || ! empty( $search_category ) ) {
		$pagination_args['add_args'] = array(
				'wcfmmp_store_search'     => $search_query,
				'wcfmmp_store_category'   => $search_category,
				'orderby'                 => $orderby
		);
	}
	
	$page_links = paginate_links( apply_filters( 'wcfm_store_list_pagination_args', $pagination_args ) );

	if ( $page_links ) {
		?>
		<nav class="paginations woocommerce-pagination">
		  <?php echo $page_links; ?>
		</nav>
		<?php
	}
	?>
</div>