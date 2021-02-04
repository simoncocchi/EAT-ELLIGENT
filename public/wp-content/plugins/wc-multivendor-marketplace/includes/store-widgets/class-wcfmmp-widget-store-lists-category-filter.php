<?php

/**
 * WCFM Marketplace Store List Category Filter Widget
 *
 * @since 1.0.0
 *
 */
class WCFMmp_Store_Lists_Category_Filter extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'wcfmmp-store-lists-category-filter', 'description' => __( 'Store Lists Category Filter', 'wc-multivendor-marketplace' ) );
		parent::__construct( 'wcfmmp-store-lists-category-filter', __( 'Store List: Category Filter', 'wc-multivendor-marketplace' ), $widget_ops );
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
		
		if( !apply_filters( 'wcfmmp_is_allow_store_list_category_filter', true ) ) return;
		
		$vendor_categories = $WCFMmp->wcfmmp_vendor->wcfmmp_get_vendor_taxonomy( 0, 'product_cat' );
		if ( !$vendor_categories ) return;

		extract( $args, EXTR_SKIP );

		$title        = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ) {
			$title        = apply_filters( 'widget_title', $instance['title'] );
		}
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		do_action( 'wcfmmp_store_lists_before_sidebar_category_filter' );
		
		$search_category  = isset( $_GET['wcfmmp_store_category'] ) ? sanitize_text_field( $_GET['wcfmmp_store_category'] ) : '';
		
		$display_vendor_term = array();
		
		if ( $vendor_categories ) {
			$preferred_taxonomy = 'product_cat';
			?>
			<select id="wcfmmp_store_category" name="wcfmmp_store_category" class="wcfm-select wcfm_ele">
				<option value=""><?php _e( 'Choose Category', 'wc-multivendor-marketplace' ); ?></option>
				<?php
				foreach( $vendor_categories as $vendor_category_id => $vendor_category ) {
					if( $vendor_category_id ) {
						if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_id, $preferred_taxonomy ) ) continue;
						if( is_array( $vendor_category ) && !empty( $vendor_category ) ) {
							$vendor_term = get_term( absint( $vendor_category_id ), $preferred_taxonomy ); 
							$tax_toggle_class = '';
							if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
								?>
								<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>><?php echo $vendor_term->name; ?></option>
								<?php
							}
							foreach( $vendor_category as $vendor_category_child_id => $vendor_category_child ) {
								if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_child_id, $preferred_taxonomy ) ) continue;
								$vendor_term = get_term( absint( $vendor_category_child_id ), $preferred_taxonomy );
								if( !is_array( $vendor_category_child ) ) {
									if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
										if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
										$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
										?>
										<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>><?php echo $vendor_term->name; ?></option>
										<?php
									}
								} else {
									?>
									<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>><?php echo $vendor_term->name; ?></option>
									<?php
									foreach( $vendor_category_child as $vendor_category_child2_id => $vendor_category_child2 ) {
										if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_child2_id, $preferred_taxonomy ) ) continue;
										$vendor_term = get_term( absint( $vendor_category_child2_id ), $preferred_taxonomy ); 
										if( !is_array( $vendor_category_child2 ) ) {
											if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
												if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
												$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
												?>
												<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;<?php echo $vendor_term->name; ?></option>
												<?php
											}
										} else {
											?>
											<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;<?php echo $vendor_term->name; ?></option>
											<?php
											foreach( $vendor_category_child2 as $vendor_category_child3_id => $vendor_category_child3 ) {
												if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_child3_id, $preferred_taxonomy ) ) continue;
												$vendor_term = get_term( absint( $vendor_category_child3_id ), $preferred_taxonomy ); 
												if( !is_array( $vendor_category_child3 ) ) {
													if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
														if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
														$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
														?>
														<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
														<?php
													}
												} else {
													?>
													<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
													<?php
													foreach( $vendor_category_child3 as $vendor_category_child4_id => $vendor_category_child4 ) {
														if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_child4_id, $preferred_taxonomy ) ) continue;
														$vendor_term = get_term( absint( $vendor_category_child4_id ), $preferred_taxonomy ); 
														if( !is_array( $vendor_category_child4 ) ) {
															if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
																if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
																$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
																?>
																<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
																<?php
															}
														} else {
															?>
															<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
															<?php
															foreach( $vendor_category_child4 as $vendor_category_child5_id => $vendor_category_child5 ) {
																if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_child5_id, $preferred_taxonomy ) ) continue;
																$vendor_term = get_term( absint( $vendor_category_child5_id ), $preferred_taxonomy ); 
																if( !is_array( $vendor_category_child5 ) ) {
																	if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
																		if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
																		$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
																		?>
																		<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
																		<?php
																	}
																} else {
																	?>
																	<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
																	<?php
																	foreach( $vendor_category_child5 as $vendor_category_child6_id => $vendor_category_child6 ) {
																		if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_child6_id, $preferred_taxonomy ) ) continue;
																		$vendor_term = get_term( absint( $vendor_category_child6_id ), $preferred_taxonomy ); 
																		if( !is_array( $vendor_category_child6 ) ) {
																			if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
																				if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
																				$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
																				?>
																				<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
																				<?php
																			}
																		} else {
																			?>
																			<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
																			<?php
																			foreach( $vendor_category_child6 as $vendor_category_child7_id => $vendor_category_child7 ) {
																				if( !apply_filters( 'wcfm_is_allow_store_list_taxomony_by_id', true, $vendor_category_child7_id, $preferred_taxonomy ) ) continue;
																				$vendor_term = get_term( absint( $vendor_category_child7_id ), $preferred_taxonomy ); 
																				if( !is_array( $vendor_category_child7 ) ) {
																					if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
																						if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
																						$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
																						?>
																						<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
																						<?php
																					}
																				} else {
																					?>
																					<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $vendor_term->name; ?></option>
																					<?php
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						} else {
							$vendor_term = get_term( absint( $vendor_category_id ), $preferred_taxonomy ); 
							if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
								if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
								$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
								?>
								<option value="<?php echo $vendor_term->term_id; ?>" <?php if( $vendor_term->term_id == $search_category ) echo 'selected'; ?>><?php echo $vendor_term->name; ?></option>
								<?php 
							}
						}
					} 
				}
				?>
			</select>
			<?php
		}
		
		do_action( 'wcfmmp_store_lists_after_sidebar_category_filter' );

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
					'title'      => __( 'Search by Category', 'wc-multivendor-marketplace' ),
			) );

			$title       = $instance['title'];
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc-multivendor-marketplace' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<?php
	}
}
