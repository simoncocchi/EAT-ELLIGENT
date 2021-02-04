<?php

/**
 * WCFM Marketplace Store List Location Filter Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Lists_Location_Filter extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-lists-location-filter', 'description' => __( 'Store Lists Location Filter', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-lists-location-filter', __( 'Store List: Location Filter', 'wc-multivendor-marketplace' ), $widget_ops );
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
		
		if( !apply_filters( 'wcfmmp_is_allow_store_list_country_filter', true ) && !apply_filters( 'wcfmmp_is_allow_store_list_state_filter', true ) && !apply_filters( 'wcfmmp_is_allow_store_list_city_filter', true ) && !apply_filters( 'wcfmmp_is_allow_store_list_zip_filter', true ) ) return;

		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			$title        = apply_filters( 'widget_title', $instance['title'] );
		}
		$is_state     = isset( $instance['is_state'] ) ? $instance['is_state'] : '';
		$is_city      = isset( $instance['is_city'] ) ? $instance['is_city'] : '';
		$is_zip       = isset( $instance['is_zip'] ) ? $instance['is_zip'] : '';
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'wcfmmp_store_lists_before_sidebar_location_filter' );
		
		$search_country = '';
		$search_state   = '';
		$search_city    = '';
		
		// GEO Locate Support added
		if( apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ) ) {
			if( is_user_logged_in() && !$search_country ) {
				$user_location = get_user_meta( get_current_user_id(), 'wcfm_user_location', true );
				if( $user_location ) {
					$search_country = $user_location['country'];
					$search_state   = $user_location['state'];
					$search_city    = $user_location['city'];
				}
			}
			
			if( apply_filters( 'wcfm_is_allow_wc_geolocate', true ) && class_exists( 'WC_Geolocation' ) && !$search_country ) {
				$user_location = WC_Geolocation::geolocate_ip();
				$search_country = $user_location['country'];
				$search_state   = $user_location['state'];
				$search_city    = !empty($user_location['city']) ? $user_location['city'] : '';
			}
		}
		
		$search_country = isset( $_GET['wcfmmp_store_country'] ) ? sanitize_text_field( $_GET['wcfmmp_store_country'] ) : $search_country;
		$search_state   = isset( $_GET['wcfmmp_store_state'] ) ? sanitize_text_field( $_GET['wcfmmp_store_state'] ) : $search_state;
		$search_city   = isset( $_GET['wcfmmp_store_city'] ) ? sanitize_text_field( $_GET['wcfmmp_store_city'] ) : $search_city;
		$search_zip   = isset( $_GET['wcfmmp_store_zip'] ) ? sanitize_text_field( $_GET['wcfmmp_store_zip'] ) : '';
		
		if( apply_filters( 'wcfmmp_is_allow_store_list_country_filter', true ) ) {
			$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfmmp_store_list_search_by_country_field', array( "wcfmmp_store_country" => array( 'type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'value' => $search_country ) ) ) );
		}
  
		if( apply_filters( 'wcfmmp_is_allow_store_list_state_filter', true ) && !$is_state ) {
			
			// Country -> States
			$country_obj   = new WC_Countries();
			$countries     = $country_obj->countries;
			$states        = $country_obj->states;
			$state_options = array( '' => __( 'Choose State', 'wc-multivendor-marketplace' ) );
			if( isset( $states[$search_country] ) && is_array( $states[$search_country] ) ) {
				$state_options = $states[$search_country];
			}
			if( $search_state && empty( $state_options ) ) $state_options[$search_state] = $search_state;
			
			if( !empty( $state_options ) ) { $state_options = array( '' => __( 'Choose State', 'wc-multivendor-marketplace' ) ) + $state_options; }
			
			$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfmmp_store_list_search_by_state_field', array( "wcfmmp_store_state" => array( 'type' => 'select', 'class' => 'wcfm-select wcfm_ele', 'options' => $state_options, 'value' => $search_state ) ) ) );
  	}
  	
  	if( apply_filters( 'wcfmmp_is_allow_store_list_city_filter', true ) && !$is_city ) {
			$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfmmp_store_list_search_by_city_field', array( "wcfmmp_store_city" => array( 'placeholder' => __( 'Search by City', 'wc-multivendor-marketplace' ), 'type' => 'text', 'class' => 'wcfm-text wcfm-search-field wcfm_ele', 'value' => $search_city ) ) ) );
  	}
  	
  	if( apply_filters( 'wcfmmp_is_allow_store_list_zip_filter', true ) && !$is_zip ) {
			$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfmmp_store_list_search_by_zip_field', array( "wcfmmp_store_zip" => array( 'placeholder' => __( 'Search by ZIP', 'wc-multivendor-marketplace' ), 'type' => 'text', 'class' => 'wcfm-text wcfm-search-field wcfm_ele', 'value' => $search_zip ) ) ) );
  	}
		
		do_action( 'wcfmmp_store_lists_after_sidebar_location_filter' );

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
					'title'     => __( 'Search by Location', 'wc-multivendor-marketplace' ),
					'is_state' => __( 'State Filter', 'wc-multivendor-marketplace' ),
					'is_city'   => __( 'City Filter', 'wc-multivendor-marketplace' ),
					'is_zip'    => __( 'ZIP Code Filter', 'wc-multivendor-marketplace' ),
			) );

			$title     = $instance['title'];
			$is_state  = $instance['is_state'];
			$is_city   = $instance['is_city'];
			$is_zip    = $instance['is_zip'];
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p>
				<input class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'is_state' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'is_state' ) ); ?>" type="checkbox" value="1" <?php checked( $is_state, 1 ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'is_state' ) ); ?>"><?php echo __( 'Disable State Filter', 'wc-multivendor-marketplace' ); ?></label>
			</p>
			<p>
				<input class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'is_city' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'is_city' ) ); ?>" type="checkbox" value="1" <?php checked( $is_city, 1 ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'is_city' ) ); ?>"><?php echo __( 'Disable City Filter', 'wc-multivendor-marketplace' ); ?></label>
			</p>
			<p>
				<input class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'is_zip' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'is_zip' ) ); ?>" type="checkbox" value="1" <?php checked( $is_zip, 1 ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'is_zip' ) ); ?>"><?php echo __( 'Disable ZIP Code Filter', 'wc-multivendor-marketplace' ); ?></label>
			</p>
			<?php
	}
}
