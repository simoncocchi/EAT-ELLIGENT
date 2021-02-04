<?php

/**
 * WCFM Marketplace Store Top Rated Vendors Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Top_Rated_Vendors extends WP_Widget {
  /**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-top-rated-vendors', 'description' => __( 'Store Top Rated Vendors', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-top-rated-vendors', __( 'Marketplace: Top Rated Vendors', 'wc-multivendor-marketplace' ), $widget_ops );
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


		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			$title        = apply_filters( 'widget_title', $instance['title'] );
		}
		$number       = !empty( $instance['number'] ) ? $instance['number'] : 10;
    
    $vendors = $WCFMmp->wcfmmp_vendor->wcfmmp_get_vendor_list( true, '', $number, '', '', 'DESC', 'avg_review_rating' );
    if ( isset( $vendors ) && count($vendors) ) {
      
    	echo $before_widget;
      
      if ( ! empty( $title ) ) {
        echo $args['before_title'] . $title . $args['after_title'];
      }
      do_action( 'wcfmmp_store_before_sidebar_top_rated_vendors', $vendors  );
      $template_args = array(
				'widget_id'   => $args['widget_id'],
				'show_rating' => true,
        'vendors' => $vendors
			);
      $WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-top-rated-vendors.php', $template_args );

      do_action( 'wcfmmp_store_after_sidebar_top_rated_vendors', $vendors );
      
      echo $after_widget;
    //print_r($stores);
    }
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
				'title'     => __( 'Top Rated Vendors', 'wc-multivendor-marketplace' ),
				'number'    => 5,
		) );

		$title     = $instance['title'];
		$number    = $instance['number'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of vendors to show:', 'wc-multivendor-marketplace' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" min="1" step="1" max="10" value="<?php echo esc_attr( $number ); ?>" />
		</p>
		
		<?php
	}
  
}