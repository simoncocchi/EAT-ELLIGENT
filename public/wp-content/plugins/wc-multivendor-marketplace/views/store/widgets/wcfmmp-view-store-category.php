<?php
/**
 * The Template for displaying store sidebar category.
 *
 * @package WCfM Markeplace Views Store Sidebar Category
 *
 * For edit coping this to yourtheme/wcfm/store/widgets
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$display_parent_term = array();
$display_vendor_term = array();

//print_r($vendor_categories);
?>

<div class="categories_list">
	<ul>
	  <li class="parent_cat"><a class="" href="<?php echo $store_user->get_shop_url(); ?>"><?php _e( 'All Categories', 'wc-multivendor-marketplace' ); ?></a></li>
		<?php foreach( $vendor_categories as $vendor_category_id => $vendor_category ) {
			if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_id, $store_user->get_id(), 'product_cat' ) ) continue;
			if( $vendor_category_id ) {
				if( is_array( $vendor_category ) && !empty( $vendor_category ) ) {
					$vendor_term = get_term( absint( $vendor_category_id ), 'product_cat' ); 
					if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
						?>
						<li class="parent_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
						<?php
					}
					foreach( $vendor_category as $vendor_category_child_id => $vendor_category_child ) {
						if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_child_id, $store_user->get_id(), 'product_cat' ) ) continue;
						$vendor_term = get_term( absint( $vendor_category_child_id ), 'product_cat' );
						if( !is_array( $vendor_category_child ) ) {
							if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
								if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
								$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
								?>
								<li class="child_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
								<?php
							}
						} else {
							?>
							<li class="child_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
							<?php
							foreach( $vendor_category_child as $vendor_category_child2_id => $vendor_category_child2 ) {
								if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_child2_id, $store_user->get_id(), 'product_cat' ) ) continue;
								$vendor_term = get_term( absint( $vendor_category_child2_id ), 'product_cat' ); 
								if( !is_array( $vendor_category_child2 ) ) {
									if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
										if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
										$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
										?>
										<li class="child2_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
										<?php
									}
								} else {
									?>
									<li class="child2_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
									<?php
									foreach( $vendor_category_child2 as $vendor_category_child3_id => $vendor_category_child3 ) {
										if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_child3_id, $store_user->get_id(), 'product_cat' ) ) continue;
										$vendor_term = get_term( absint( $vendor_category_child3_id ), 'product_cat' ); 
										if( !is_array( $vendor_category_child3 ) ) {
											if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
												if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
												$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
												?>
												<li class="child3_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
												<?php
											}
										} else {
											?>
											<li class="child3_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
											<?php
											foreach( $vendor_category_child3 as $vendor_category_child4_id => $vendor_category_child4 ) {
												if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_child4_id, $store_user->get_id(), 'product_cat' ) ) continue;
												$vendor_term = get_term( absint( $vendor_category_child4_id ), 'product_cat' ); 
												if( !is_array( $vendor_category_child4 ) ) {
													if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
														if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
														$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
														?>
														<li class="child4_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
														<?php
													}
												} else {
													?>
													<li class="child4_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
													<?php
													foreach( $vendor_category_child4 as $vendor_category_child5_id => $vendor_category_child5 ) {
														if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_child5_id, $store_user->get_id(), 'product_cat' ) ) continue;
														$vendor_term = get_term( absint( $vendor_category_child5_id ), 'product_cat' ); 
														if( !is_array( $vendor_category_child5 ) ) {
															if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
																if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
																$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
																?>
																<li class="child5_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
																<?php
															}
														} else {
															?>
															<li class="child5_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
															<?php
															foreach( $vendor_category_child5 as $vendor_category_child6_id => $vendor_category_child6 ) {
																if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_child6_id, $store_user->get_id(), 'product_cat' ) ) continue;
																$vendor_term = get_term( absint( $vendor_category_child6_id ), 'product_cat' ); 
																if( !is_array( $vendor_category_child6 ) ) {
																	if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
																		if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
																		$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
																		?>
																		<li class="child6_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
																		<?php
																	}
																} else {
																	?>
																	<li class="child6_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
																	<?php
																	foreach( $vendor_category_child6 as $vendor_category_child7_id => $vendor_category_child7 ) {
																		if( !apply_filters( 'wcfm_is_allow_vendor_store_taxomony_by_id', true, $vendor_category_child7_id, $store_user->get_id(), 'product_cat' ) ) continue;
																		$vendor_term = get_term( absint( $vendor_category_child7_id ), 'product_cat' ); 
																		if( !is_array( $vendor_category_child7 ) ) {
																			if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
																				if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
																				$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
																				?>
																				<li class="child7_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
																				<?php
																			}
																		} else {
																			?>
																			<li class="child7_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>/"><?php echo $vendor_term->name; ?></a></li>
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
					$vendor_term = get_term( absint( $vendor_category_id ), 'product_cat' ); 
					if( $vendor_term && $vendor_term->term_id && $vendor_term->name ) {
						if( in_array( $vendor_term->term_id, $display_vendor_term) ) continue;
						$display_vendor_term[$vendor_term->term_id] = $vendor_term->term_id;
						?>
						<li class="parent_cat"><a class="<?php if( $vendor_term->slug == $selected_term ) echo 'active'; ?>" href="<?php echo $store_user->get_shop_url() . 'category/' . $vendor_term->slug; ?>"><?php echo $vendor_term->name; ?></a></li>
		<?php 
					}
				}
			} 
		}
		?>
	</ul>
</div>