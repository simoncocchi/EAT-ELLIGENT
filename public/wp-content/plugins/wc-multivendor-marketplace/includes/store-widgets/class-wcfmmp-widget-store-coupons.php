<?php

/**
 * WCFM Marketplace Store Coupons Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Coupons extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-coupons', 'description' => __( 'Store Coupons', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-coupons', __( 'Vendor Store: Coupons', 'wc-multivendor-marketplace' ), $widget_ops );
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
		
		if( !apply_filters( 'wcfm_is_pref_store_coupons', true ) ) return;
		
		if ( !function_exists( 'wc_coupons_enabled' ) || ( function_exists( 'wc_coupons_enabled' ) && !wc_coupons_enabled() ) ) return;

		if ( ! wcfmmp_is_store_page() && !is_product() ) {
			return;
		}

		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			$title        = apply_filters( 'widget_title', $instance['title'] );
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
		
		if( !$WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_id, 'manage_coupons' ) ) return;
		
		$coupon_args = array(
							'posts_per_page'   => -1,
							'offset'           => 0,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => 'show_on_store',
							'meta_value'       => 'yes',
							'post_type'        => 'shop_coupon',
							'post_mime_type'   => '',
							'post_parent'      => '',
							'author'	   			 => $store_id,
							'post_status'      => array('publish'),
							'suppress_filters' => 0 
						);
		
		$wcfm_store_coupons = get_posts( $coupon_args );
		if( empty( $wcfm_store_coupons ) ) return;
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'wcfmmp_store_before_sidebar_store_coupons', $store_id );
		
		$content = '<div class="wcfmmp_store_coupons">';
		
		foreach( $wcfm_store_coupons as $wcfm_store_coupon ) {
			$wc_coupon = new WC_Coupon( $wcfm_store_coupon->ID );
			
			if ( $wc_coupon->get_date_expires() && ( current_time( 'timestamp', true ) > $wc_coupon->get_date_expires()->getTimestamp() ) ) continue;
			
			$free_shipping = ( get_post_meta( $wcfm_store_coupon->ID, 'free_shipping', true) == 'yes' ) ? 'enable' : '';
			
			if( $free_shipping ) {
				$content .= '<span class="wcfmmp-store-coupon-single tips text_tip" data-tip="' . __( 'FREE Shipping Coupon', 'wc-multivendor-marketplace' ) . ($wc_coupon->get_date_expires() ? '<br>' . __( 'Expiry Date: ', 'wc-multivendor-marketplace' ) . $wc_coupon->get_date_expires()->date_i18n( 'F j, Y' ) : '' ) . '<br>' . $wcfm_store_coupon->post_excerpt . '">' . $wcfm_store_coupon->post_title . '</span>';
			} else {
				$content .= '<span class="wcfmmp-store-coupon-single tips text_tip" data-tip="' . esc_html( wc_get_coupon_type( $wc_coupon->get_discount_type() ) ) . ': ' . esc_html( wc_format_localized_price( $wc_coupon->get_amount() ) ) . ($wc_coupon->get_date_expires() ? '<br>' . __( 'Expiry Date: ', 'wc-multivendor-marketplace' ) . $wc_coupon->get_date_expires()->date_i18n( 'F j, Y' ) : '' ) . '<br>' . $wcfm_store_coupon->post_excerpt . '">' . $wcfm_store_coupon->post_title . '</span>';
			}
		}
		
		$content .= '</div>';
		
		echo $content;

		do_action( 'wcfmmp_store_after_sidebar_store_coupons', $store_id );

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
					'title' => __( 'Store Coupons', 'wc-multivendor-marketplace' ),
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
