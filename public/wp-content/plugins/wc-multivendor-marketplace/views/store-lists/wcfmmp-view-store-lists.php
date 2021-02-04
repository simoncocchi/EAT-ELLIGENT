<?php
/**
 * The Template for displaying store list.
 *
 * @package WCfM Markeplace Views Store Lists
 *
 * For edit coping this to yourtheme/wcfm/store-lists
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $post;

$pagination_base = '';
if( $post )
	$pagination_base = str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) );

//$search_country = '';
//$search_state   = '';

// GEO Locate Support
if( apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ) ) {
	if( is_user_logged_in() && !$search_country ) {
		$user_location = get_user_meta( get_current_user_id(), 'wcfm_user_location', true );
		if( $user_location ) {
			$search_country = $user_location['country'];
			$search_state   = $user_location['state'];
		}
	}
			
	if( apply_filters( 'wcfm_is_allow_wc_geolocate', true ) && class_exists( 'WC_Geolocation' ) && !$search_country ) {
		$user_location = WC_Geolocation::geolocate_ip();
		$search_country = $user_location['country'];
		$search_state   = $user_location['state'];
	}
}

$search_query     = isset( $_GET['wcfmmp_store_search'] ) ? sanitize_text_field( $_GET['wcfmmp_store_search'] ) : $search_term;
$search_category  = isset( $_GET['wcfmmp_store_category'] ) ? sanitize_text_field( $_GET['wcfmmp_store_category'] ) : $search_category;
$store_category   = isset( $_GET['wcfmsc_store_categories'] ) ? sanitize_text_field( $_GET['wcfmsc_store_categories'] ) : $store_category;

$search_country = isset( $_GET['wcfmmp_store_country'] ) ? sanitize_text_field( $_GET['wcfmmp_store_country'] ) : $search_country;
$search_state   = isset( $_GET['wcfmmp_store_state'] ) ? sanitize_text_field( $_GET['wcfmmp_store_state'] ) : $search_state;
$search_city    = isset( $_GET['wcfmmp_store_city'] ) ? sanitize_text_field( $_GET['wcfmmp_store_city'] ) : $search_city;
$search_zip     = isset( $_GET['wcfmmp_store_zip'] ) ? sanitize_text_field( $_GET['wcfmmp_store_zip'] ) : $search_zip;

$orderby          = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : $orderby;

$search_data     = array();
if( $store_category ) {
	$search_data['wcfmsc_store_categories'] = $store_category;
}
if( $search_country ) {
	$search_data['wcfmmp_store_country'] = $search_country;
}
if( $search_state ) {
	$search_data['wcfmmp_store_state'] = $search_state;
}
if( $search_city ) {
	$search_data['wcfmmp_store_city'] = $search_city;
}
if( $search_zip ) {
	$search_data['wcfmmp_store_zip'] = $search_zip;
}
if( isset( $_POST['search_data'] ) ) {
	parse_str($_POST['search_data'], $search_data);
} elseif( isset( $_GET['orderby'] ) ) {
	$search_data = wp_unslash($_GET);
}
$search_data['excludes'] = $excludes;

$args = array(
		'stores'          => $stores,
		'limit'           => $limit,
		'offset'          => $offset,
		'paged'           => $paged,
		'sidebar'         => $sidebar,
		'filter'          => $filter,
		'search'          => $search,
		'category'        => $category,
		'country'         => $country,
		'state'           => $state,
		'has_city'        => $has_city,
		'has_zip'         => $has_zip,
		'radius'          => $radius,
		'map'             => $map,
		'map_zoom'        => $map_zoom,
		'auto_zoom'       => $auto_zoom,
		'search_query'    => $search_query,
		'search_term'     => $search_query,
		'search_country'  => $search_country,
		'search_state'    => $search_state,
		'search_city'     => $search_city,
		'search_zip'      => $search_zip,
		'search_category' => $search_category,
		'store_category'  => $store_category,
		'pagination_base' => $pagination_base,
		'per_row'         => $per_row,
		'search_enabled'  => $search,
		'image_size'      => $image_size,
		'includes'        => $includes,
		'excludes'        => $excludes, 
		'orderby'         => $orderby,
		'has_orderby'     => $has_orderby,
		'has_product'     => $has_product,
		'theme'           => $theme,
		'search_data'     => $search_data
);

$store_sidebar_pos = isset( $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] : 'left';

$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }

$wcfm_store_lists_wrapper_class = apply_filters( 'wcfm_store_lists_wrapper_class', '' );

if( $theme == 'classic' ) {
	wp_enqueue_style( 'wcfmmp_store_list_classic_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-classic.css', array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
	
	if( is_rtl() ) {
		wp_enqueue_style( 'wcfmmp_store_list_classic_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-classic-rtl.css', array( 'wcfmmp_store_list_classic_css' ), $WCFMmp->version );	
	}
} elseif( $theme == 'compact' ) {
	wp_enqueue_style( 'wcfmmp_store_list_compact_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-compact.css', array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
	
	if( is_rtl() ) {
		wp_enqueue_style( 'wcfmmp_store_list_compact_rtl_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-compact-rtl.css', array( 'wcfmmp_store_list_compact_css' ), $WCFMmp->version );	
	}
}

?>

<?php if( $sidebar && $WCFMmp->wcfmmp_vendor->is_store_lists_sidebar() && ($store_sidebar_pos != 'left' ) ) { ?>
	<style>
		#wcfmmp-stores-lists .right_side{float:left !important;}
		#wcfmmp-stores-lists .left_sidebar{float:right !important;}
	</style>
<?php } ?>

<?php do_action( 'wcfmmp_store_lists_before' ); ?>

<div id="wcfmmp-stores-lists" class="wcfmmp-stores-listing <?php echo $wcfm_store_lists_wrapper_class; ?>">

	<?php do_action( 'wcfmmp_store_lists_start' ); ?>

  <?php do_action( 'wcfmmp_store_lists_before_map' ); ?>

  <?php if( $map && apply_filters( 'wcfmmp_is_allow_map_full_width', true ) ) { $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-map.php', $args ); } ?>
  
  <?php do_action( 'wcfmmp_store_lists_after_map' ); ?>

	<?php if( (!$WCFMmp->wcfmmp_vendor->is_store_lists_sidebar() || !$sidebar ) && $filter && ($search || $category || $radius || $country || $state || $has_city || $has_zip) ) { $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-search-form.php', $args ); } ?>
	
	<?php if( $sidebar && !apply_filters( 'wcfmmp_is_allow_mobile_sidebar_at_bottom', true ) ) { ?>
    <?php $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-sidebar.php', $args ); ?>
  <?php } ?>
  
  <?php do_action( 'wcfmmp_store_lists_before_loop' ); ?>
  
  <div id="wcfmmp-stores-wrap-holder" class="rgt right_side <?php if( !$sidebar || !$WCFMmp->wcfmmp_vendor->is_store_lists_sidebar() ) echo 'right_side_full'; ?>">
    <?php if( $map && !apply_filters( 'wcfmmp_is_allow_map_full_width', true ) ) { $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-map.php', $args ); } ?>
    
    <div id="wcfmmp-stores-wrap">
	  	<?php $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-loop.php', $args ); ?>
	  </div>
	</div>
	
	<?php do_action( 'wcfmmp_store_lists_after_loop' ); ?>
	
	<?php if( $sidebar && apply_filters( 'wcfmmp_is_allow_mobile_sidebar_at_bottom', true ) ) { ?>
	  <?php $WCFMmp->template->get_template( 'store-lists/wcfmmp-view-store-lists-sidebar.php', $args ); ?>
	<?php } ?>
	
	<?php do_action( 'wcfmmp_store_lists_end' ); ?>
	
	<div class="spacer"></div>
</div>

<?php do_action( 'wcfmmp_store_lists_after' ); ?>

<?php  if( !$map || ( ($wcfm_map_lib == 'google') && !$api_key ) ) { ?>
	<script>
		$per_row     = '<?php echo $per_row; ?>';
		$per_page    = '<?php echo $limit; ?>';
		$includes    = '<?php echo implode(",", $includes ); ?>';
		$excludes    = '<?php echo $excludes; ?>';
		$has_product = '<?php echo $has_product; ?>';
		$sidebar     = '<?php echo $sidebar; ?>';
		$has_orderby = '<?php echo $has_orderby; ?>';
		$theme       = '<?php echo $theme; ?>';
	</script>
<?php } ?>