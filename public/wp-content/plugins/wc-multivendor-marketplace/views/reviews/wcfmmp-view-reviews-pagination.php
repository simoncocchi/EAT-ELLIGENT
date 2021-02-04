<?php
/**
 * The Template for displaying reviews pagination.
 *
 * @package WCfM Markeplace Views Reviews Pagination
 *
 * For edit coping this to yourtheme/wcfm/reviews
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $post;
?>

<div class="pagination clearfix">

  <?php
	$pagination_args = array(
		'current'   => $paged,
		'total'     => $num_of_pages,
		'base'      => $pagination_base,
		'type'      => 'array',
		'prev_text' => __( '&laquo;', 'wc-multivendor-marketplace' ),
		'next_text' => __( '&raquo;', 'wc-multivendor-marketplace' ),
	);

	$page_links = paginate_links( $pagination_args );

	if ( $page_links ) {
		?>
		<div class="paginations">
		  <ul class="wcfmmp-pagination">
		    <li>
		  	  <?php echo join( "</li>\n\t<li>", $page_links ); ?>
		  	</li>
		  </ul>
		</div>
		<?php
	}
	?>
</div>