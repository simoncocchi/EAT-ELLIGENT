<?php
global $wp, $WCFM, $WCFM_Query;

$wcfm_options = $WCFM->wcfm_options;

$is_menu_disabled = isset( $wcfm_options['menu_disabled'] ) ? $wcfm_options['menu_disabled'] : 'no';
if( $is_menu_disabled == 'yes' ) return;

$wcfm_menus = $WCFM->get_wcfm_menus();
$wcfm_formeted_menus = apply_filters( 'wcfm_formeted_menus', $wcfm_menus );

$current_endpoint = $WCFM_Query->get_current_endpoint();
$current_endpoint = apply_filters( 'wcfm_current_endpoint', $current_endpoint );

$menu_active_dependent_list = apply_filters( 'wcfm_menu_dependancy_map', array(
																																			'wcfm-articles-manage'             => 'wcfm-articles',
																																			'wcfm-products-manage'             => 'wcfm-products',
																																			'wcfm-listings-manage'             => 'wcfm-listings',
																																			'wcfm-stock-manage'                => 'wcfm-products',
																																			'wcfm-products-export'             => 'wcfm-products',
																																			'wcfm-products-import'             => 'wcfm-products',
																																			'wcfm-coupons-manage'              => 'wcfm-coupons',
																																			'wcfm-orders-details'              => 'wcfm-orders',
																																			'wcfm-orders-manage'               => 'wcfm-orders',
																																			'wcfm-vendors-manage'              => 'wcfm-vendors',
																																			'wcfm-vendors-new'                 => 'wcfm-vendors',
																																			'wcfm-customers-details'           => 'wcfm-customers',
																																			'wcfm-customers-manage'            => 'wcfm-customers',
																																			'wcfm-withdrawal'                  => 'wcfm-payments',
																																			'wcfm-transaction-details'         => 'wcfm-payments',
																																			'wcfm-withdrawal-reverse'          => 'wcfm-withdrawal-requests',
																																			'wcfm-product-reviews'             => 'wcfm-reviews',
																																			'wcfm-bookings'                    => 'wcfm-bookings-dashboard',
																																			'wcfm-bookings-resources'          => 'wcfm-bookings-dashboard',
																																			'wcfm-bookings-resources-manage'   => 'wcfm-bookings-dashboard',
																																			'wcfm-bookings-manual'             => 'wcfm-bookings-dashboard',
																																			'wcfm-bookings-calendar'           => 'wcfm-bookings-dashboard',
																																			'wcfm-bookings-details'            => 'wcfm-bookings-dashboard',
																																			'wcfm-bookings-settings'           => 'wcfm-bookings-dashboard',
																																			'wcfm-booking'                     => 'wcfm-booking-dashboard',
																																			'wcfm-booking-resources'           => 'wcfm-booking-dashboard',
																																			'wcfm-booking-resources-manage'    => 'wcfm-booking-dashboard',
																																			'wcfm-booking-manual'              => 'wcfm-booking-dashboard',
																																			'wcfm-booking-calendar'            => 'wcfm-booking-dashboard',
																																			'wcfm-booking-details'             => 'wcfm-booking-dashboard',
																																			'wcfm-booking-settings'            => 'wcfm-booking-dashboard',
																																			'wcfm-appointments'                => 'wcfm-appointments-dashboard',
																																			'wcfm-appointments-staffs'         => 'wcfm-appointments-dashboard',
																																			'wcfm-appointments-staffs-manage'  => 'wcfm-appointments-dashboard',
																																			'wcfm-appointments-manual'         => 'wcfm-appointments-dashboard',
																																			'wcfm-appointments-calendar'       => 'wcfm-appointments-dashboard',
																																			'wcfm-appointments-details'        => 'wcfm-appointments-dashboard',
																																			'wcfm-appointments-settings'       => 'wcfm-appointments-dashboard',
																																			'wcfm-subscriptions-manage'        => 'wcfm-subscriptions',
																																			'wcfm-reports-sales-by-date'       => 'wcfm-reports',
																																			'wcfm-reports-out-of-stock'        => 'wcfm-reports',
																																			'wcfm-reports-sales-by-product'    => 'wcfm-reports',
																																			'wcfm-reports-sales-by-vendor'     => 'wcfm-reports',
																																			'wcfm-reports-coupons-by-date'     => 'wcfm-reports',
																																			'wcfm-reports-low-in-stock'        => 'wcfm-reports',
																																			'wcfm-rental-quote-details'        => 'wcfm-rental-quote',
																																			'wcfm-support-manage'              => 'wcfm-support',
																																			'wcfm-fncy-product-builder'        => 'wcfm-fncy-product-designer'
																																			) );

if( !wcfm_is_vendor() ) {
	$menu_active_dependent_list['wcfm-transaction-details'] = 'wcfm-withdrawal-requests';
}

$logo = ( get_option( 'wcfm_site_logo' ) ) ? get_option( 'wcfm_site_logo' ) : '';
$logo_image_url = wp_get_attachment_image_src( $logo, 'thumbnail' );

if ( !empty( $logo_image_url ) ) {
	$logo_image_url = $logo_image_url[0];
} else {
	$logo_image_url = $WCFM->plugin_url . 'assets/images/your-logo-here.png';
}

$wcfm_my_store_label = wcfm_get_option( 'wcfm_my_store_label', __( 'My Store', 'wc-frontend-manager' ) );
$wcfm_home_menu_label = wcfm_get_option( 'wcfm_home_menu_label', __( 'Home', 'wc-frontend-manager' ) );

$logo_image_url = '<a class="wcfm_store_logo_icon" href="'.get_permalink( wc_get_page_id( 'shop' ) ).'" target="_blank"><img src="' . $logo_image_url . '" alt="Store Logo" /></a>';
$store_logo = apply_filters( 'wcfm_store_logo', $logo_image_url );
$store_name = '<a href="'.get_permalink( wc_get_page_id( 'shop' ) ).'" target="_blank">' . __( $wcfm_my_store_label, 'wc-frontend-manager' ) . '</a>';
$store_name = apply_filters( 'wcfm_store_name', $store_name );
//$store_name = __( 'My Store', 'wc-frontend-manager' );

$user_id = get_current_user_id();
$toggle_state = get_user_meta( $user_id, '_wcfm_menu_toggle_state', true );

if( wcfm_is_mobile() || wcfm_is_tablet() ) {
	$toggle_state = 'yes'; 
}
?>
<div id="wcfm_menu" <?php if( $toggle_state && ( $toggle_state == 'yes' ) ) { echo 'class="wcfm_menu_toggle"'; } ?>>

  <?php if( apply_filters( 'wcfm_is_pref_dashboard_logo', true ) ) { ?>
		<div class="wcfm_menu_logo"> 
			<h4>
			  <?php echo $store_logo; ?>
			  <?php _e( $store_name );?>
			</h4>
		</div>
	<?php } else { ?>
		<div class="wcfm_menu_no_logo"> 
			<h4><?php _e( $store_name );?></h4>
		</div>
	<?php } ?>

	<?php if( apply_filters( 'wcfm_is_allow_home_in_menu', true ) ) { ?>
		<div class="wcfm_menu_items wcfm_menu_home">
			<a class="wcfm_menu_item <?php if( !$current_endpoint ) echo 'active'; ?>" href="<?php echo apply_filters( 'wcfm_dashboard_home', get_wcfm_page() ); ?>">
				<span class="wcfmfa fa-chalkboard"></span>
				<span class="text"><?php _e( $wcfm_home_menu_label, 'wc-frontend-manager' ); ?></span>
			</a>
		</div>
	<?php } ?>
  
  <?php 
  if( !empty($wcfm_formeted_menus) ) {
  	foreach( $wcfm_formeted_menus as $wcfm_menu_key => $wcfm_menu_data ) {
  		$wcfm_menu_for = 'both';
			if( isset( $wcfm_menu_data['menu_for'] ) ) $wcfm_menu_for = $wcfm_menu_data['menu_for'];
			if( ( $wcfm_menu_for == 'admin' ) && wcfm_is_vendor() ) continue;
			if( ( $wcfm_menu_for == 'vendor' ) && !wcfm_is_vendor() ) continue;
			
  		if( !empty( $wcfm_menu_data['label'] ) && !empty( $wcfm_menu_data['url'] ) ) {
				if( !isset( $wcfm_menu_data['capability'] ) || empty( $wcfm_menu_data['capability'] ) || apply_filters( $wcfm_menu_data['capability'], true ) ) {
					$is_active = false;
					if( isset( $wp->query_vars[$wcfm_menu_key] ) ) $is_active = true;
					if( !$is_active && $current_endpoint && isset( $menu_active_dependent_list[$current_endpoint] ) && ( $menu_active_dependent_list[$current_endpoint] == $wcfm_menu_key ) ) $is_active = true;
				?>
					<div class="wcfm_menu_items wcfm_menu_<?php echo $wcfm_menu_key; ?>">
						<a class="wcfm_menu_item <?php if( $is_active ) echo 'active'; ?>" href="<?php echo $wcfm_menu_data['url']; ?>" <?php if( isset( $wcfm_menu_data['new_tab'] ) && ( $wcfm_menu_data['new_tab'] == 'yes' ) ) echo 'target="_blank"'; ?>>
							<span class="wcfmfa fa-<?php echo str_replace( 'fa-', '', $wcfm_menu_data['icon'] ); ?>"></span>
							<span class="text">
							  <?php 
							  $wcfm_menu_lable_length = (int) apply_filters( 'wcfm_is_allow_menu_lable_length', 25 );
							  if( strlen( $wcfm_menu_data['label'] ) > $wcfm_menu_lable_length ) {
							  	echo substr( $wcfm_menu_data['label'], 0, $wcfm_menu_lable_length ) . ' ..';
							  } else {
							  	_e( $wcfm_menu_data['label'], 'wc-frontend-manager' );
							  }
							  ?>
							</span>
						</a>
						<?php if( apply_filters( 'wcfm_is_pref_hover_submenu', true ) ) { ?>
							<?php if( !isset( $wcfm_menu_data['submenu_capability'] ) || empty( $wcfm_menu_data['submenu_capability'] ) || apply_filters( $wcfm_menu_data['submenu_capability'], true ) ) { ?>
								<?php if( isset( $wcfm_menu_data['has_new'] ) ) { ?>
									<span class="wcfm_sub_menu_items <?php echo $wcfm_menu_data['new_class']; ?> moz_class">
										<a href="<?php echo $wcfm_menu_data['new_url']; ?>" <?php if( isset( $wcfm_menu_data['new_tab'] ) && ( $wcfm_menu_data['new_tab'] == 'yes' ) ) echo 'target="_blank"'; ?>>
											<?php if( isset( $wcfm_menu_data['new_label'] ) && !empty( $wcfm_menu_data['new_label'] ) ) { _e( $wcfm_menu_data['new_label'], 'wc-frontend-manager' ); } else { _e( 'Add New', 'wc-frontend-manager' ); } ?>
										</a>
									</span>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</div>
				<?php
				}
			}
		}
	}
	?>
	<?php if( apply_filters( 'wcfm_is_allow_logout_in_menu', true ) ) { ?>
		<div class="wcfm_menu_items wcfm_menu_logout">
			<a class="wcfm_menu_item" href="<?php echo esc_url(wc_logout_url( apply_filters( 'wcfm_logout_url', get_wcfm_url() ) ) ); ?>">
				<span class="wcfmfa fa-power-off"></span>
				<span class="text"><?php _e( 'Logout', 'wc-frontend-manager' ); ?></span>
			</a>
		</div>
	<?php } ?>
</div>