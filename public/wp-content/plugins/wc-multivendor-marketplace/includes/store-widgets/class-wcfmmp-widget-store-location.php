<?php

/**
 * WCFM Marketplace Store Location Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Location extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-location', 'description' => __( 'Store Location', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-location', __( 'Vendor Store: Location', 'wc-multivendor-marketplace' ), $widget_ops );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array  An array of standard parameters for widgets in this theme
	 * @param array  An array of settings for this widget instance
	 *
	 * @return void Echoes it's output
	 */
	function widget( $args, $instance ) {
		global $WCFM, $WCFMmp;

		if ( ! wcfmmp_is_store_page() ) {
				return;
		}

		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			$title        = apply_filters( 'widget_title', $instance['title'] );
		}
		$wcfm_store_url    = wcfm_get_option( 'wcfm_store_url', 'store' );
		$wcfm_store_name   = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
		$seller_info       = get_user_by( 'slug', $wcfm_store_name );
		if( !$seller_info ) return;
		
		$store_user        = wcfmmp_get_store( $seller_info->data->ID );
		$store_info        = $store_user->get_shop_info();
		$address           = $store_user->get_address_string(); 
		$map_location      = isset( $store_info['location'] ) ? esc_attr( $store_info['location'] ) : '';
		
		$is_store_offline = get_user_meta( $store_user->get_id(), '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
		
		$is_disable_vendor = get_user_meta( $store_user->get_id(), '_disable_vendor', true );
		if ( $is_disable_vendor ) return;
		
		$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
		$store_lat    = isset( $store_info['store_lat'] ) ? esc_attr( $store_info['store_lat'] ) : 0;
		$store_lng    = isset( $store_info['store_lng'] ) ? esc_attr( $store_info['store_lng'] ) : 0;

		if ( ( ($wcfm_map_lib == 'google') && empty( $api_key ) ) || empty( $store_lat ) || empty( $store_lng ) || ( $store_info['store_hide_map'] == 'yes' ) || ( $store_info['store_hide_address'] == 'yes' )  || !$WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_map' ) ) {
			return;
		}

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'wcfmmp_store_before_sidebar_location', $store_user->get_id() );
		
		$WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-store-location.php', array( 
			                                             'store_user' => $store_user, 
			                                             'store_info' => $store_info,
			                                             'address'    => $address,
			                                             'store_lat'  => $store_lat,
			                                             'store_lng'  => $store_lng,
			                                             'map_id'     => 'wcfm_sold_by_widget_map_'.rand(10,100)
			                                             ) );

		do_action( 'wcfmmp_store_after_sidebar_location', $store_user->get_id() );

		echo $after_widget;
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 *
	 * @param array  An array of new settings as submitted by the admin
	 * @param array  An array of the previous settings
	 *
	 * @return array The validated and (if necessary) amended settings
	 */
	function update( $new_instance, $old_instance ) {

			// update logic goes here
			$updated_instance = $new_instance;
			return $updated_instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @param array  An array of the current settings for this widget
	 *
	 * @return void Echoes it's output
	 */
	function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array(
					'title' => __( 'Store Location', 'wc-multivendor-marketplace' ),
			) );

			$title = $instance['title'];
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<?php
	}
}
