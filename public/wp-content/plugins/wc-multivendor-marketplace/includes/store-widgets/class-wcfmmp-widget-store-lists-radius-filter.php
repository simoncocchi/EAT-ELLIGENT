<?php

/**
 * WCFM Marketplace Store List Location Filter Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Lists_Radius_Filter extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-lists-radius-filter', 'description' => __( 'Store Lists Radius Filter', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-lists-radius-filter', __( 'Store List: Radius Filter', 'wc-multivendor-marketplace' ), $widget_ops );
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

		if ( ! wcfmmp_is_stores_list_page() ) {
			return;
		}
		
		if( !apply_filters( 'wcfmmp_is_allow_store_list_radius_filter', true ) ) return;
		
		$api_key = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_google_map_api'] : '';
		$wcfm_map_lib = isset( $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] ) ? $WCFMmp->wcfmmp_marketplace_options['wcfm_map_lib'] : '';
		if( !$wcfm_map_lib && $api_key ) { $wcfm_map_lib = 'google'; } elseif( !$wcfm_map_lib && !$api_key ) { $wcfm_map_lib = 'leaftlet'; }
		if ( ($wcfm_map_lib == 'google') && !$api_key ) return;
		
		$max_radius_to_search = isset( $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] ) ? $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] : '100';
		
		$radius_unit = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';

		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			$title        = apply_filters( 'widget_title', $instance['title'] );
		}
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'wcfmmp_store_lists_before_sidebar_radius_filter' );
		
		?>
		
		<div id="wcfm_radius_filter_container" class="wcfm_radius_filter_container">
			<input type="text" id="wcfmmp_radius_addr" name="wcfmmp_radius_addr" class="wcfmmp-radius-addr" autocomplete="off"  placeholder="<?php esc_attr_e( 'Insert your address ..', 'wc-multivendor-marketplace' ); ?>" value="" />
			<i class="wcfmmmp_locate_icon" style="background-image: url(<?php echo $WCFMmp->plugin_url; ?>assets/images/locate.svg)"></i>
		</div>
		<div class="wcfm_radius_slidecontainer">
			<input class="wcfmmp_radius_range" name="wcfmmp_radius_range" id="wcfmmp_radius_range" type="range" value="<?php echo (absint(apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ))/apply_filters( 'wcfmmp_radius_filter_start_distance', 10)); ?>" min="0" max="<?php echo apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ); ?>" steps="6" />
			<span class="wcfmmp_radius_range_start">0</span>
			<span class="wcfmmp_radius_range_cur"><?php echo (absint(apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ))/apply_filters( 'wcfmmp_radius_filter_start_distance', 10)); ?> <?php echo ucfirst( $radius_unit ); ?></span>
			<span class="wcfmmp_radius_range_end"><?php echo apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ); ?></span>
		</div>
		<input type="hidden" id="wcfmmp_radius_lat" name="wcfmmp_radius_lat" value="">
		<input type="hidden" id="wcfmmp_radius_lng" name="wcfmmp_radius_lng" value="">
		<?php
		
		do_action( 'wcfmmp_store_lists_after_sidebar_radius_filter' );

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
				'title'     => __( 'Search by Radius', 'wc-multivendor-marketplace' ),
		) );

		$title     = $instance['title'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}
}
