<?php

/**
 * WCFM Marketplace Store Taxonomy Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Taxonomy extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-taxonomy', 'description' => __( 'Store Taxonomies', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-taxonomy', __( 'Vendor Store: Taxonomy', 'wc-multivendor-marketplace' ), $widget_ops );
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
		$has_toggle   = isset( $instance['has_toggle'] ) ? $instance['has_toggle'] : '';
		$taxonomy     = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : '';
		if( !$taxonomy ) return;
		
		$wcfm_store_url    = wcfm_get_option( 'wcfm_store_url', 'store' );
		$wcfm_store_name   = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
		if ( empty( $wcfm_store_name ) ) return;
		$seller_info       = get_user_by( 'slug', $wcfm_store_name );
		if( !$seller_info ) return;
		
		$store_user        = wcfmmp_get_store( $seller_info->ID );
		$store_info        = $store_user->get_shop_info();
		
		if( !$taxonomy ) return;
		
		$is_store_offline = get_user_meta( $store_user->get_id(), '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
		
		$vendor_categories = $store_user->get_store_taxonomies( $taxonomy );

		$selected_term = get_query_var( 'term' );

		if ( empty( $vendor_categories ) ) {
			return;
		}

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'wcfmmp_store_before_sidebar_taxonomy', $store_user->get_id() );
		
		$WCFMmp->template->get_template( 'store/widgets/wcfmmp-view-store-taxonomy.php', array( 
			                                             'store_user'         => $store_user, 
			                                             'store_info'         => $store_info,
			                                             'vendor_categories'  => $vendor_categories,
			                                             'selected_term'      => $selected_term,
			                                             'has_toggle'         => $has_toggle,
			                                             'preferred_taxonomy' => $taxonomy,
			                                             'url_base'           => 'tax-'.$taxonomy
			                                             ) );

		do_action( 'wcfmmp_store_after_sidebar_taxonomy', $store_user->get_id() );

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
					'title'      => __( 'Store Taxonomies', 'wc-multivendor-marketplace' ),
					'taxonomy'   => __( 'Choose Taxonomy', 'wc-multivendor-marketplace' ),
					'has_toggle' => __( 'Enable Toggle', 'wc-multivendor-marketplace' ),
			) );

			$title       = $instance['title'];
			$taxonomy    = $instance['taxonomy'];
			$has_toggle  = $instance['has_toggle'];
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:', 'wc-multivendor-marketplace' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
				  <option value=""><?php _e( '-- Taxonomy --', 'wc-multivendor-marketplace' ); ?></option>
				  <?php
				  	$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
						if( !empty( $product_taxonomies ) ) {
							foreach( $product_taxonomies as $product_taxonomy ) {
								if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) ) {
									if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
										echo '<option value="'.$product_taxonomy->name.'" ' . selected( $taxonomy, $product_taxonomy->name ) . '>'. $product_taxonomy->label .'</option>';
									}
								}
							}
						}
				  ?>
				</select>
			</p>
			<p>
				<input class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'has_toggle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'has_toggle' ) ); ?>" type="checkbox" value="1" <?php checked( $has_toggle, 1 ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'has_toggle' ) ); ?>"><?php echo __( 'Enable toggle to show child taxonomies', 'wc-multivendor-marketplace' ); ?></label>
			</p>
			<?php
	}
}
