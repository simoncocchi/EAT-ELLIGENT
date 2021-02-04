<?php

/**
 * WCFM Marketplace Store Product Search Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Product_Search extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-product-search', 'description' => __( 'Store Product Search', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-product-search', __( 'Vendor Store: Product Search', 'wc-multivendor-marketplace' ), $widget_ops );
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
		global $WCFM, $WCFMmp, $wcfmmp_product_search_form_index;;

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
		if ( empty( $wcfm_store_name ) ) return;
		$seller_info       = get_user_by( 'slug', $wcfm_store_name );
		if( !$seller_info ) return;
		
		$store_user        = wcfmmp_get_store( $seller_info->ID );
		$store_info        = $store_user->get_shop_info();
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		if ( empty( $wcfmmp_product_search_form_index ) ) {
			$wcfmmp_product_search_form_index = 0;
		}
		
		do_action( 'wcfmmp_store_before_sidebar_product_search', $store_user->get_id() );
		
		$WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-store-product-search.php', array( 
			                                             'index'              => $wcfmmp_product_search_form_index, 
			                                             'store_info'         => $store_info,
			                                             'store_user'         => $store_user
			                                             ) );

		do_action( 'wcfmmp_store_after_sidebar_product_search', $store_user->get_id() );

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
					'title' => __( 'Product Search', 'wc-multivendor-marketplace' ),
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
