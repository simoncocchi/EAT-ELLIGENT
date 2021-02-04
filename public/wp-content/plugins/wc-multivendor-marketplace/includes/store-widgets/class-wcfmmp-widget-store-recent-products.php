<?php

/**
 * WCFM Marketplace Store Recent Products Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Recent_Products extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-recent-products', 'description' => __( 'Store Recent Products', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-recent-products', __( 'Vendor Store: Recent Products', 'wc-multivendor-marketplace' ), $widget_ops );
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
		global $WCFM, $WCFMmp, $post;

		if ( ! wcfmmp_is_store_page() && !is_product() ) {
			return;
		}
		
		if (  wcfm_is_store_page() ) {
			$wcfm_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
			$store_id  = 0;
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
		}
		
		if( is_product() ) {
			$store_id = $post->post_author;
		}
		
		if( !$store_id ) return;
		
		$is_store_offline = get_user_meta( $store_id, '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
		
		$is_disable_vendor = get_user_meta( $store_id, '_disable_vendor', true );
		if ( $is_disable_vendor ) return;

		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			$title        = apply_filters( 'widget_title', $instance['title'] );
		}
		$number       = !empty( $instance['number'] ) ? $instance['number'] : 10;
		$store_user   = wcfmmp_get_store( $store_id );
		$store_info   = $store_user->get_shop_info();
		
		$product_visibility_term_ids = wc_get_product_visibility_term_ids();
		
		$query_args = array(
			'posts_per_page' => $number,
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'author'         => $store_id,
			'no_found_rows'  => 1,
			'order'          => 'DESC',
			'orderby'        => 'date',
			'meta_query'     => array(),
			'tax_query'      => array(
				'relation' => 'AND',
			),
		); // WPCS: slow query ok.

		$query_args['tax_query'][] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'term_taxonomy_id',
			'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
			'operator' => 'NOT IN',
		);
		$query_args['post_parent'] = 0;
		
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_term_ids['outofstock'],
					'operator' => 'NOT IN',
				),                               
			);
		}
		
		if ( ! empty( $instance['hide_free'] ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'DECIMAL',
			);
		}
		
		$products = new WP_Query( apply_filters( 'woocommerce_products_widget_query_args', $query_args ) );
		
		if ( $products && $products->have_posts() ) {
			
			echo $before_widget;

			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			
			do_action( 'wcfmmp_store_before_sidebar_recent_products', $store_user->get_id() );

			echo wp_kses_post( apply_filters( 'woocommerce_before_widget_product_list', '<ul class="product_list_widget">' ) );

			$template_args = array(
				'widget_id'   => $args['widget_id'],
				'show_rating' => true,
			);

			while ( $products->have_posts() ) {
				$products->the_post();
				wc_get_template( 'content-widget-product.php', $template_args );
			}

			echo wp_kses_post( apply_filters( 'woocommerce_after_widget_product_list', '</ul>' ) );
			
			do_action( 'wcfmmp_store_after_sidebar_recent_products', $store_user->get_id() );

			echo $after_widget;

		}

		wp_reset_postdata();
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
				'title'     => __( 'Recent Products', 'wc-multivendor-marketplace' ),
				'number'    => 5,
				'hide_free' => '',
		) );

		$title     = $instance['title'];
		$number    = $instance['number'];
		$hide_free = $instance['hide_free'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of products to show:', 'wc-multivendor-marketplace' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" min="1" step="1" value="<?php echo esc_attr( $number ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_free' ); ?>"><?php _e( 'Hide Free Products:', 'wc-multivendor-marketplace' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'hide_free' ); ?>" name="<?php echo $this->get_field_name( 'hide_free' ); ?>" type="checkbox" <?php checked( $hide_free, "yes" ); ?> value="yes" />
		</p>
		<?php
	}
}
