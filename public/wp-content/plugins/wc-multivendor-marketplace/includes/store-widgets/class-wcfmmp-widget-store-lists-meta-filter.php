<?php

/**
 * WCFM Marketplace Store List Meta Filter Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Lists_Meta_Filter extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-lists-meta-filter', 'description' => __( 'Store Lists Meta Filter', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-lists-meta-filter', __( 'Store List: Meta Filter', 'wc-multivendor-marketplace' ), $widget_ops );
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
		
		$wcfmmp_addition_info_fields = wcfm_get_option( 'wcfmvm_registration_custom_fields', array() );
		if( empty( $wcfmmp_addition_info_fields ) ) return;
		
		$has_addition_field = false;
		if( !empty( $wcfmmp_addition_info_fields ) ) {
			foreach( $wcfmmp_addition_info_fields as $wcfmvm_registration_custom_field ) {
				if( !isset( $wcfmvm_registration_custom_field['enable'] ) ) continue;
				if( !$wcfmvm_registration_custom_field['label'] ) continue;
				if( !in_array( $wcfmvm_registration_custom_field['type'], array( 'text', 'number', 'textarea', 'select', 'mselect' ) ) ) continue;
				$has_addition_field = true;
				break;
			}
		}
		if( !$has_addition_field ) return;

		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			if( apply_filters( 'wcfmmp_is_allow_meta_filter_widget_title', true ) )
				$title        = apply_filters( 'widget_title', $instance['title'] );
		}
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'wcfmmp_store_lists_before_sidebar_meta_filter' );
		
		if( $has_addition_field ) {
			foreach( $wcfmmp_addition_info_fields as $wcfmvm_registration_custom_field ) {
				if( !isset( $wcfmvm_registration_custom_field['enable'] ) ) continue;
				if( !$wcfmvm_registration_custom_field['label'] ) continue;
				if( !in_array( $wcfmvm_registration_custom_field['type'], array( 'text', 'number', 'textarea', 'select', 'mselect' ) ) ) continue;
				
				$wcfmvm_registration_custom_field['name'] = sanitize_title( $wcfmvm_registration_custom_field['label'] );
				$field_name = 'wcfmmp_store_' . $wcfmvm_registration_custom_field['name'];
				$field_id   = md5( $field_name );
				
				$field_label = __( 'Search by', 'wc-multivendor-marketplace' ) . ' ' . __($wcfmvm_registration_custom_field['label'], 'wc-multivendor-membership');
				
				$field_enable  = isset( $instance[$field_name] ) ? $instance[$field_name] : '';
				
				if( !$field_enable ) continue;
				
				$field_value     = isset( $_REQUEST[$field_name] ) ? sanitize_text_field( $_REQUEST[$field_name] ) : '';
				
				$custom_attributes = array( 'title' => $field_label );
				
				switch( $wcfmvm_registration_custom_field['type'] ) {
				  case 'text':
				  case 'number':
				  case 'textarea':
				  	$WCFM->wcfm_fields->wcfm_generate_form_field(  array( $field_id => array( 'label' => $field_label, 'placeholder' => $field_label, 'name' => $field_name, 'type' => 'text', 'custom_attributes' => $custom_attributes, 'class' => 'wcfm-text wcfmmp-search-box wcfm-custom-search-input-field wcfmmp-store-' . $field_name, 'label_class' => 'wcfm_title', 'value' => $field_value ) ) );
					?>
					<?php
					break;
					
					case 'select':
					case 'mselect':
						$select_opt_vals = array( '' => __( '--Choose Option--', 'wc-multivendor-marketplace' ) );
						$select_options = explode( '|', $wcfmvm_registration_custom_field['options'] );
						if( !empty ( $select_options ) ) {
							foreach( $select_options as $select_option ) {
								if( $select_option ) {
									$select_opt_vals[$select_option] = __(ucfirst( str_replace( "-", " " , $select_option ) ), 'wc-multivendor-membership');
								}
							}
						}
						$WCFM->wcfm_fields->wcfm_generate_form_field(  array( $field_id => array( 'label' => $field_label, 'name' => $field_name, 'type' => 'select', 'custom_attributes' => $custom_attributes, 'class' => 'wcfm-select wcfmmp-search-box wcfm-custom-search-select-field wcfmmp-store-' . $field_name, 'label_class' => 'wcfm_title', 'options' => $select_opt_vals, 'value' => $field_value ) ) );
					break;
				}
			}
		}
		?>
		<?php
		
		do_action( 'wcfmmp_store_lists_after_sidebar_meta_filter' );

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
		$meta_instance = array( 'title'      => __( 'Meta Filter', 'wc-multivendor-marketplace' ), );
		
		$wcfmmp_addition_info_fields = wcfm_get_option( 'wcfmvm_registration_custom_fields', array() );
		
		$has_addition_field = false;
		if( !empty( $wcfmmp_addition_info_fields  ) ) {
			if( !empty( $wcfmmp_addition_info_fields ) ) {
				foreach( $wcfmmp_addition_info_fields as $wcfmvm_registration_custom_field ) {
					if( !isset( $wcfmvm_registration_custom_field['enable'] ) ) continue;
					if( !$wcfmvm_registration_custom_field['label'] ) continue;
					if( !in_array( $wcfmvm_registration_custom_field['type'], array( 'text', 'number', 'textarea', 'select', 'mselect' ) ) ) continue;
					
					$wcfmvm_registration_custom_field['name'] = sanitize_title( $wcfmvm_registration_custom_field['label'] );
					$field_name = 'wcfmmp_store_' . $wcfmvm_registration_custom_field['name'];
					
					$meta_instance[$field_name] = $wcfmvm_registration_custom_field['label'];
					
					$has_addition_field = true;
				}
			}
		}
		
		$instance = wp_parse_args( (array) $instance, $meta_instance );

		$title = $instance['title'];
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<?php
		if( $has_addition_field ) {
			foreach( $wcfmmp_addition_info_fields as $wcfmvm_registration_custom_field ) {
				if( !isset( $wcfmvm_registration_custom_field['enable'] ) ) continue;
				if( !$wcfmvm_registration_custom_field['label'] ) continue;
				if( !in_array( $wcfmvm_registration_custom_field['type'], array( 'text', 'number', 'textarea', 'select', 'mselect' ) ) ) continue;
				
				$wcfmvm_registration_custom_field['name'] = sanitize_title( $wcfmvm_registration_custom_field['label'] );
				$field_name = 'wcfmmp_store_' . $wcfmvm_registration_custom_field['name'];
				$field_id   = md5( $field_name );
				
				$field_value  = $instance[$field_name];
				?>
				<p>
					<input class="checkbox" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_name ) ); ?>" type="checkbox" value="1" <?php checked( $field_value, 1 ); ?> />
					<label for="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>"><?php echo $wcfmvm_registration_custom_field['label']; ?></label>
				</p>
				<?php
			}
		}
	}
}
