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

if ( empty( $stores )  ) return;

$args = array(
		'stores'          => $stores,
		'per_row'         => $per_row,
		'limit'           => $limit,
		'offset'          => $offset,
		'category'        => $category,
		'country'         => $country,
		'state'           => $state,
		'search_category' => $search_category,
		'has_product'     => $has_product,
		'theme'           => $theme
);

$responsive = array(
									'0'		=> array( 'items'	=> 1 ),
									'480'	=> array( 'items'	=> 2 ),
								);

if( absint( $per_row ) > 2 ) {
	$responsive['768']	= array( 'items' => 3 );
}

if( absint( $per_row ) > 3 ) {
	$responsive['992']	= array( 'items' => 4 );
}

if( absint( $per_row ) > 4 ) {
	$responsive['1200']	= array( 'items' => 5 );
}

$carousel_id = 'wcfmmp-stores-carousel-' . uniqid();
$carousel_args 	= apply_filters( 'wcfmmp_stores_carousel_args', array(
	'items'				      => absint( $per_row ),
	'margin'            => 10,
	'loop'              => $has_loop,
  'autoplay'          => $has_autoplay,
  'autoplayTimeout'   => 2000,
	'autoplayHoverPause'=> true,
	'nav'				      => $has_nav,
	'dots'				    => false,
	//'slideTransition' => 'linear',
	'rtl'				      => is_rtl() ? true : false,
	'paginationSpeed'	=> 400,
	'navText'			    => is_rtl() ? array( '<i class="wcfmfa fa-chevron-right"></i>', '<i class="wcfmfa fa-chevron-left"></i>' ) : array( '<i class="wcfmfa fa-chevron-left"></i>', '<i class="wcfmfa fa-chevron-right"></i>' ),
	'margin'			    => 0,
	'touchDrag'			  => false,
	'responsive'		=> $responsive
	
) );

?>

<div id="wcfmmp-stores-wrap">
	<div id="<?php echo esc_attr( $carousel_id );?>" class="wcfmmp-stores-content">
		
		<ul class="wcfmmp-store-wrap owl-carousel">
			<?php
			foreach ( $stores as $store_id => $store_name ) {
				$args['store_id'] = $store_id;
				$WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-card.php', $args );
			}
			?>
		</ul> <!-- .wcfmmp-store-wrap -->
		
		<?php	
		wp_enqueue_script( 'wcfmmp_stores_carousel_js', $WCFMmp->library->js_lib_url . 'carousel/wcfmmp-script-owl-carousel.min.js', array('jquery'), $WCFMmp->version, true );
		
		wp_enqueue_style( 'wcfmmp_store_carousel_css',  $WCFMmp->library->css_lib_url . 'carousel/wcfmmp-style-owl-carousel.min.css', array(), $WCFMmp->version );
		wp_enqueue_style( 'wcfmmp_store_list_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list.css', array(), $WCFMmp->version );
		
		if( is_rtl() ) {
			wp_enqueue_style( 'wcfmmp_store_list_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-rtl.css', array('wcfmmp_store_list_css'), $WCFMmp->version );
		}
		
		if( $theme == 'classic' ) {
			wp_enqueue_style( 'wcfmmp_store_list_classic_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-classic.css', array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
			
			if( is_rtl() ) {
				wp_enqueue_style( 'wcfmmp_store_list_classic_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-classic-rtl.css', array( 'wcfmmp_store_list_classic_css' ), $WCFMmp->version );	
			}
		}  elseif( $theme == 'compact' ) {
			wp_enqueue_style( 'wcfmmp_store_list_compact_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-compact.css', array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
			
			if( is_rtl() ) {
				wp_enqueue_style( 'wcfmmp_store_list_compact_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-compact-rtl.css', array( 'wcfmmp_store_list_compact_css' ), $WCFMmp->version );	
			}
		}
		
		// WCFMmp Custom CSS
		$upload_dir      = wp_upload_dir();
		$wcfmmp_style_custom = get_option( 'wcfmmp_style_custom' );
		if( $wcfmmp_style_custom && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmmp_style_custom ) ) {
			if( wcfmmp_is_store_page() ) {
				wp_enqueue_style( 'wcfmmp_style_custom',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmmp_style_custom, array( 'wcfmmp_store_css' ), $WCFMmp->version );
			}
			
			wp_enqueue_style( 'wcfmmp_store_list_style_custom',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmmp_style_custom, array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
		}
				
		
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$( '#<?php echo esc_attr( $carousel_id ); ?> .owl-carousel').owlCarousel(<?php echo json_encode( $carousel_args ); ?>);
			});
		</script>
		<style>
		#wcfmmp-stores-wrap ul.owl-carousel li {width: 100% !important;}
		.wcfmmp-stores-content .owl-carousel .owl-nav{display:block;}
		</style>
	</div>
</div>