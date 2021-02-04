<?php
global $wp, $WCFM, $wc_article_attributes;

$wcfm_is_allow_manage_articles = apply_filters( 'wcfm_is_allow_manage_articles', true );
if( !$wcfm_is_allow_manage_articles ) {
	wcfm_restriction_message_show( "Articles" );
	return;
}

if( isset( $wp->query_vars['wcfm-articles-manage'] ) && empty( $wp->query_vars['wcfm-articles-manage'] ) ) {
	if( !apply_filters( 'wcfm_is_allow_add_articles', true ) ) {
		wcfm_restriction_message_show( "Add Article" );
		return;
	}
	if( !apply_filters( 'wcfm_is_allow_article_limit', true ) ) {
		wcfm_restriction_message_show( "Article Limit Reached" );
		return;
	}
	if( !apply_filters( 'wcfm_is_allow_space_limit', true ) ) {
		wcfm_restriction_message_show( "Space Limit Reached" );
		return;
	}
} elseif( isset( $wp->query_vars['wcfm-articles-manage'] ) && !empty( $wp->query_vars['wcfm-articles-manage'] ) ) {
	$wcfm_articles_single = get_post( $wp->query_vars['wcfm-articles-manage'] );
	if( $wcfm_articles_single->post_status == 'publish' ) {
		if( !apply_filters( 'wcfm_is_allow_edit_articles', true ) ) {
			wcfm_restriction_message_show( "Edit Article" );
			return;
		}
	}
	if( wcfm_is_vendor() ) {
		$is_article_from_vendor = $WCFM->wcfm_vendor_support->wcfm_is_component_for_vendor( $wp->query_vars['wcfm-articles-manage'], 'article' );
		if( !$is_article_from_vendor ) {
			wcfm_restriction_message_show( "Restricted Article" );
			return;
		}
	}
}

$article_id = 0;
$wcfm_articles_single = array();
$title = '';
$excerpt = '';
$description = '';

$featured_img = '';
$categories = array();
$article_tags = '';

$wcfm_vendor = 0;
$vendor_arr = array();

if( isset( $wp->query_vars['wcfm-articles-manage'] ) && !empty( $wp->query_vars['wcfm-articles-manage'] ) ) {
	
	$article_id = $wp->query_vars['wcfm-articles-manage'];
	$wcfm_articles_single = get_post($article_id);
	// Fetching Article Data
	if($wcfm_articles_single && !empty($wcfm_articles_single)) {
		
		$title = $wcfm_articles_single->post_title;
		
		if( $wcfm_articles_single->post_type != 'post' ) {
			wcfm_restriction_message_show( "Invalid Article" );
			return;
		}
		
		$excerpt = wpautop( $wcfm_articles_single->post_excerpt );
		$description = wpautop( $wcfm_articles_single->post_content );
		
		$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
		if( !$rich_editor && apply_filters( 'wcfm_is_allow_editor_newline_replace', true ) ) {
			$breaks = apply_filters( 'wcfm_editor_newline_generators', array("<br />","<br>","<br/>") ); 
			
			$excerpt = str_ireplace( $breaks, "\r\n", $excerpt );
			$excerpt = strip_tags( $excerpt );
			
			$description = str_ireplace( $breaks, "\r\n", $description );
			$description = strip_tags( $description );
		}
		
		// Article Images
		$featured_img = (get_post_thumbnail_id($article_id)) ? get_post_thumbnail_id($article_id) : '';
		//if($featured_img) $featured_img = wp_get_attachment_url($featured_img);
		//if(!$featured_img) $featured_img = '';
		
		// Article Categories
		$pcategories = get_the_terms( $article_id, 'category' );
		if( !empty($pcategories) ) {
			foreach($pcategories as $pkey => $pcategory) {
				$categories[] = $pcategory->term_id;
			}
		} else {
			$categories = array();
		}
		
		// Article Tags
		$article_tag_list = wp_get_post_terms($article_id, 'post_tag', array("fields" => "names"));
		$article_tags = implode(',', $article_tag_list);
		
		$wcfm_vendor = $wcfm_articles_single->post_author;
		if( wcfm_is_vendor( $wcfm_vendor ) ) {
			$vendor_arr = array( $wcfm_articles_single->post_author => wcfm_get_vendor_store_name( $wcfm_articles_single->post_author ) );
		}
	}
}

$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
$article_categories   = get_terms( 'category', 'orderby=name&hide_empty=0&parent=0' );

$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
$wpeditor = apply_filters( 'wcfm_is_allow_article_wpeditor', 'wpeditor' );
if( $wpeditor && $rich_editor ) {
	$rich_editor = 'wcfm_wpeditor';
} else {
	$wpeditor = 'textarea';
}
?>

<div class="collapse wcfm-collapse" id="">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-file-alt"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Manage Article', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_article_simple' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php if( $article_id ) { _e('Edit Article', 'wc-frontend-manager' ); } else { _e('Add Article', 'wc-frontend-manager' ); } ?></h2>
			<?php
			if( $article_id ) {
				?>
				<span class="article-status article-status-<?php echo $wcfm_articles_single->post_status; ?>"><?php if( $wcfm_articles_single->post_status == 'publish' ) { _e( 'Published', 'wc-frontend-manager' ); } else { _e( ucfirst( $wcfm_articles_single->post_status ), 'wc-frontend-manager' ); } ?></span>
				<?php
				if( $wcfm_articles_single->post_status == 'publish' ) {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_articles_single->ID ) . '">';
					?>
					<span class="view_count"><span class="wcfmfa fa-eye text_tip" data-tip="<?php _e( 'Views', 'wc-frontend-manager' ); ?>"></span>
					<?php
					echo get_post_meta( $wcfm_articles_single->ID, '_wcfm_article_views', true ) . '</span></a>';
				} else {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_articles_single->ID ) . '">';
					?>
					<span class="view_count"><span class="wcfmfa fa-eye text_tip" data-tip="<?php _e( 'Preview', 'wc-frontend-manager' ); ?>"></span>
					<?php
					echo '</a>';
				}
			}
			
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('post-new.php?post_type=post'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
				<?php
			}
			
			if( $has_new = apply_filters( 'wcfm_add_new_article_sub_menu', true ) ) {
				echo '<a id="add_new_article_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_articles_manage_url().'" data-tip="' . __('Add New Article', 'wc-frontend-manager') . '"><span class="wcfmfa fa-file-pdf"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager') . '</span></a>';
			}
			?>
			
			<div class="wcfm-clearfix"></div>
		</div>
		<div class="wcfm-clearfix"></div><br />
		
		<form id="wcfm_articles_manage_form" class="wcfm">
		
			<?php do_action( 'begin_wcfm_articles_manage_form' ); ?>
			
			<!-- collapsible -->
			<div class="wcfm-container">
				<div id="wcfm_articles_manage_form_general_expander" class="wcfm-content">
				  <div class="wcfm_article_manager_general_fields">
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_article_manage_fields_general', array(
																																																"title" => array( 'placeholder' => __('Article Title', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_article_title wcfm_full_ele', 'value' => $title),
																																													), $article_id ) );
							
						?>
						<div class="wcfm_clearfix"></div>
						
						<?php if( !$wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
						  <?php if( $wcfm_is_allow_category = apply_filters( 'wcfm_is_allow_category', true ) ) { $catlimit = apply_filters( 'wcfm_article_catlimit', -1 ); ?>
								<p class="wcfm_title"><strong><?php _e( 'Categories', 'wc-frontend-manager' ); ?></strong></p><label class="screen-reader-text" for="article_cats"><?php _e( 'Categories', 'wc-frontend-manager' ); ?></label>
								<select id="article_cats" name="product_cats[]" class="wcfm-select wcfm_ele" multiple="multiple" data-catlimit="<?php echo $catlimit; ?>" style="width: 100%; margin-bottom: 10px;">
									<?php
										if ( $article_categories ) {
											$WCFM->library->generateTaxonomyHTML( 'category', $article_categories, $categories );
										}
									?>
								</select>
							
								<?php
								if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$article_taxonomies = get_object_taxonomies( 'post', 'objects' );
									if( !empty( $article_taxonomies ) ) {
										foreach( $article_taxonomies as $article_taxonomy ) {
											if( !in_array( $article_taxonomy->name, array( 'category', 'post_tag' ) ) ) {
												if( $article_taxonomy->public && $article_taxonomy->show_ui && $article_taxonomy->meta_box_cb && $article_taxonomy->hierarchical ) {
													// Fetching Saved Values
													$taxonomy_values_arr = array();
													if($article && !empty($article)) {
														$taxonomy_values = get_the_terms( $article_id, $article_taxonomy->name );
														if( !empty($taxonomy_values) ) {
															foreach($taxonomy_values as $pkey => $ptaxonomy) {
																$taxonomy_values_arr[] = $ptaxonomy->term_id;
															}
														}
													}
													?>
													<p class="wcfm_title"><strong><?php _e( $article_taxonomy->label, 'wc-frontend-manager' ); ?></strong></p><label class="screen-reader-text" for="<?php echo $article_taxonomy->name; ?>"><?php _e( $article_taxonomy->label, 'wc-frontend-manager' ); ?></label>
													<select id="<?php echo $article_taxonomy->name; ?>" name="article_custom_taxonomies[<?php echo $article_taxonomy->name; ?>][]" class="wcfm-select article_taxonomies wcfm_ele" multiple="multiple" style="width: 100%; margin-bottom: 10px;">
														<?php
															$article_taxonomy_terms   = get_terms( $article_taxonomy->name, 'orderby=name&hide_empty=0&parent=0' );
															if ( $article_taxonomy_terms ) {
																$WCFM->library->generateTaxonomyHTML( $article_taxonomy->name, $article_taxonomy_terms, $taxonomy_values_arr );
															}
														?>
													</select>
													<?php
												}
											}
										}
									}
								}
							}
							
							if( $wcfm_is_allow_tags = apply_filters( 'wcfm_is_allow_tags', true ) ) {
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'article_simple_fields_tag', array(  "article_tags" => array('label' => __('Tags', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $article_tags, 'placeholder' => __('Separate Article Tags with commas', 'wc-frontend-manager'), 'desc' => __( 'Choose from the most used tags', 'wc-frontend-manager' ), 'desc_class' => 'wcfm_full_ele wcfm_fetch_tag_cloud' )
																																														) ) );
								
								if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$article_taxonomies = get_object_taxonomies( 'post', 'objects' );
									if( !empty( $article_taxonomies ) ) {
										foreach( $article_taxonomies as $article_taxonomy ) {
											if( !in_array( $article_taxonomy->name, array( 'category', 'post_tag' ) ) ) {
												if( $article_taxonomy->public && $article_taxonomy->show_ui && $article_taxonomy->meta_box_cb && !$article_taxonomy->hierarchical ) {
													// Fetching Saved Values
													$taxonomy_values_arr = wp_get_post_terms($article_id, $article_taxonomy->name, array("fields" => "names"));
													$taxonomy_values = implode(',', $taxonomy_values_arr);
													$WCFM->wcfm_fields->wcfm_generate_form_field( array(  $article_taxonomy->name => array( 'label' => $article_taxonomy->label, 'name' => 'article_custom_taxonomies_flat[' . $article_taxonomy->name . '][]', 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele', 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $taxonomy_values, 'placeholder' => __('Separate Article ' . $article_taxonomy->label . ' with commas', 'wc-frontend-manager') )
																																			) );
												}
											}
										}
									}
								}
							}
							?>
						<?php } ?>
						<?php if( $wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
							<div class="wcfm_clearfix"></div><br />
							<div class="wcfm_article_manager_content_fields">
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_article_manage_fields_content', array(
																																																			"excerpt" => array('label' => __('Short Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'rows' => 5, 'value' => $excerpt, 'teeny' => true ),
																																																			"description" => array('label' => __('Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'value' => $description),
																																																			"article_id" => array('type' => 'hidden', 'value' => $article_id)
																																															), $article_id ) );
								?>
							</div>
						<?php } ?>
						
						<?php
						if( function_exists( 'wcfmmp_get_store_url' ) && !wcfm_is_vendor() ) {
							$WCFM->wcfm_fields->wcfm_generate_form_field( array(  
																																"wcfm_vendor" => array( 'label' => apply_filters( 'wcfm_sold_by_label', $wcfm_vendor, __( 'Store', 'wc-frontend-manager' ) ), 'type' => 'select', 'options' => $vendor_arr, 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_vendor ),
																															) );
						}
						?>
					</div>
					<div class="wcfm_article_manager_gallery_fields">
					  <?php
					  if( $wcfm_is_allow_featured = apply_filters( 'wcfm_is_allow_featured', true ) ) {
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_article_manage_fields_gallery', array(  "featured_img" => array( 'type' => 'upload', 'class' => 'wcfm-article-feature-upload wcfm_ele', 'label_class' => 'wcfm_title', 'prwidth' => 250, 'value' => $featured_img)
																																													), $article_id ) );
						}
						?>
					
						<?php if( $wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
							<?php 
							if( $wcfm_is_allow_category = apply_filters( 'wcfm_is_allow_category', true ) ) {
								$catlimit = apply_filters( 'wcfm_article_catlimit', -1 ); 
								?>
								<div class="wcfm_clearfix"></div>
								<div class="wcfm_article_manager_cats_checklist_fields">
									<p class="wcfm_title wcfm_full_ele"><strong><?php _e( 'Categories', 'wc-frontend-manager' ); ?></strong></p><label class="screen-reader-text" for="article_cats"><?php _e( 'Categories', 'wc-frontend-manager' ); ?></label>
									<ul id="article_cats_checklist" class="article_taxonomy_checklist wcfm_ele" data-catlimit="<?php echo $catlimit; ?>">
										<?php
											if ( $article_categories ) {
												$WCFM->library->generateTaxonomyHTML( 'category', $article_categories, $categories, '', true );
											}
										?>
									</ul>
								</div>
								<div class="wcfm_clearfix"></div>
								<?php
								if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									if( apply_filters( 'wcfm_is_allow_add_category', true ) && apply_filters( 'wcfm_is_allow_add_taxonomy', true ) ) {
										?>
										<div class="wcfm_add_new_category_box wcfm_add_new_taxonomy_box">
											<p class="description wcfm_full_ele wcfm_side_add_new_category wcfm_add_new_category wcfm_add_new_taxonomy">+<?php _e( 'Add new category', 'wc-frontend-manager' ); ?></p>
											<div class="wcfm_add_new_taxonomy_form wcfm_add_new_taxonomy_form_hide">
												<?php 
												$WCFM->wcfm_fields->wcfm_generate_form_field( array( "wcfm_new_cat" => array( 'type' => 'text', 'class' => 'wcfm-text wcfm_new_tax_ele wcfm_full_ele' ) ) ); 
												$args = array(
																			'show_option_all'    => '',
																			'show_option_none'   => __( '-- Parent category --', 'wc-frontend-manager' ),
																			'option_none_value'  => '0',
																			'hide_empty'         => 0,
																			'hierarchical'       => 1,
																			'name'               => 'wcfm_new_parent_cat',
																			'class'              => 'wcfm-select wcfm_new_parent_taxt_ele wcfm_full_ele',
																			'taxonomy'           => 'category',
																		);
												wp_dropdown_categories( $args );
												?>
												<button type="button" data-taxonomy="category" class="button wcfm_add_category_bt wcfm_add_taxonomy_bt"><?php _e( 'Add', 'wc-frontend-manager' ); ?></button>
												<div class="wcfm_clearfix"></div>
											</div>
										</div>
										<div class="wcfm_clearfix"></div>
										<?php
									}
								}
								?>
									
								<?php
								if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$article_taxonomies = get_object_taxonomies( 'post', 'objects' );
									if( !empty( $article_taxonomies ) ) {
										foreach( $article_taxonomies as $article_taxonomy ) {
											if( !in_array( $article_taxonomy->name, array( 'category', 'post_tag' ) ) ) {
												if( $article_taxonomy->public && $article_taxonomy->show_ui && $article_taxonomy->meta_box_cb && $article_taxonomy->hierarchical ) {
													// Fetching Saved Values
													$taxonomy_values_arr = array();
													if($wcfm_articles_single && !empty($wcfm_articles_single)) {
														$taxonomy_values = get_the_terms( $article_id, $article_taxonomy->name );
														if( !empty($taxonomy_values) ) {
															foreach($taxonomy_values as $pkey => $ptaxonomy) {
																$taxonomy_values_arr[] = $ptaxonomy->term_id;
															}
														}
													}
													?>
													<div class="wcfm_clearfix"></div>
													<div class="wcfm_article_manager_cats_checklist_fields wcfm_article_taxonomy_<?php echo $article_taxonomy->name; ?>">
														<p class="wcfm_title wcfm_full_ele"><strong><?php _e( $article_taxonomy->label, 'wc-frontend-manager' ); ?></strong></p><label class="screen-reader-text" for="<?php echo $article_taxonomy->name; ?>"><?php _e( $article_taxonomy->label, 'wc-frontend-manager' ); ?></label>
														<ul id="<?php echo $article_taxonomy->name; ?>" class="article_taxonomy_checklist wcfm_ele">
															<?php
																$article_taxonomy_terms   = get_terms( $article_taxonomy->name, 'orderby=name&hide_empty=0&parent=0' );
																if ( $article_taxonomy_terms ) {
																	$WCFM->library->generateTaxonomyHTML( $article_taxonomy->name, $article_taxonomy_terms, $taxonomy_values_arr, '', true, true );
																}
															?>
														</ul>
													</div>
													<?php
												}
											}
										}
									}
								}
							}
							
							if( $wcfm_is_allow_tags = apply_filters( 'wcfm_is_allow_tags', true ) ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'article_simple_fields_tag', array(  "article_tags" => array('label' => __('Tags', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele article_tags_ele', 'label_class' => 'wcfm_title wcfm_full_ele article_tags_ele', 'value' => $article_tags, 'placeholder' => __('Separate Article Tags with commas', 'wc-frontend-manager'), 'desc' => __( 'Choose from the most used tags', 'wc-frontend-manager' ), 'desc_class' => 'wcfm_full_ele wcfm_side_tag_cloud wcfm_fetch_tag_cloud' )
																																															) ) );
									
									if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
										$article_taxonomies = get_object_taxonomies( 'post', 'objects' );
										if( !empty( $article_taxonomies ) ) {
											foreach( $article_taxonomies as $article_taxonomy ) {
												if( !in_array( $article_taxonomy->name, array( 'category', 'post_tag' ) ) ) {
													if( $article_taxonomy->public && $article_taxonomy->show_ui && $article_taxonomy->meta_box_cb && !$article_taxonomy->hierarchical ) {
														// Fetching Saved Values
														$taxonomy_values_arr = wp_get_post_terms($article_id, $article_taxonomy->name, array("fields" => "names"));
														$taxonomy_values = implode(',', $taxonomy_values_arr);
														$WCFM->wcfm_fields->wcfm_generate_form_field( array(  $article_taxonomy->name => array( 'label' => $article_taxonomy->label, 'name' => 'article_custom_taxonomies_flat[' . $article_taxonomy->name . '][]', 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele', 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $taxonomy_values, 'placeholder' => __('Separate Article ' . $article_taxonomy->label . ' with commas', 'wc-frontend-manager') )
																																				) );
													}
												}
											}
										}
									}
								}
							?>
						<?php } ?>
						
						<?php do_action( 'wcfm_article_manager_gallery_fields_end', $article_id ); ?>
					</div>
				</div>
				
				<?php if( !$wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
					<div class="wcfm-content">
						<div class="wcfm_article_manager_content_fields">
							<?php
							$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_article_manage_fields_content', array(
																																																		"excerpt" => array('label' => __('Short Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele ' . $rich_editor , 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'rows' => 5, 'value' => $excerpt),
																																																		"description" => array('label' => __('Description', 'wc-frontend-manager') , 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'value' => $description),
																																																		"article_id" => array('type' => 'hidden', 'value' => $article_id)
																																														), $article_id ) );
							?>
						</div>
					</div>
				<?php } ?>
			</div>
			<!-- end collapsible -->
			<div class="wcfm_clearfix"></div><br />
			
			<!-- wrap -->
			<div class="wcfm-tabWrap">
			  <?php do_action( 'after_wcfm_articles_manage_general', $article_id ); ?>
			
			  <?php include( 'wcfm-view-articles-manage-tabs.php' ); ?>
				
				<?php do_action( 'end_wcfm_articles_manage', $article_id ); ?>
			
			</div> <!-- tabwrap -->
			
			<div id="wcfm_articles_simple_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
			  <?php if( $article_id && ( $wcfm_articles_single->post_status == 'publish' ) ) { ?>
				  <input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_live_articles', true ) ) { _e( 'Submit', 'wc-frontend-manager' ); } else { _e( 'Submit for Review', 'wc-frontend-manager' ); } ?>" id="wcfm_articles_simple_submit_button" class="wcfm_submit_button" />
				<?php } else { ?>
					<input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_articles', true ) ) { _e( 'Submit', 'wc-frontend-manager' ); } else { _e( 'Submit for Review', 'wc-frontend-manager' ); } ?>" id="wcfm_articles_simple_submit_button" class="wcfm_submit_button" />
				<?php } ?>
				<?php if( apply_filters( 'wcfm_is_allow_draft_published_articles', true ) && apply_filters( 'wcfm_is_allow_add_articles', true ) ) { ?>
				  <input type="submit" name="draft-data" value="<?php _e( 'Draft', 'wc-frontend-manager' ); ?>" id="wcfm_articles_simple_draft_button" class="wcfm_submit_button" />
				<?php } ?>
				
				<?php
				if( $article_id && ( $wcfm_articles_single->post_status != 'publish' ) ) {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_articles_single->ID ) . '">';
					?>
					<input type="button" class="wcfm_submit_button" value="<?php _e( 'Preview', 'wc-frontend-manager' ); ?>" />
					<?php
					echo '</a>';
				} elseif( $article_id && ( $wcfm_articles_single->post_status == 'publish' ) ) {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_articles_single->ID ) . '">';
					?>
					<input type="button" class="wcfm_submit_button" value="<?php _e( 'View', 'wc-frontend-manager' ); ?>" />
					<?php
					echo '</a>';
				}
				?>
			</div>
			<input type="hidden" name="wcfm_nonce" value="<?php echo wp_create_nonce( 'wcfm_articles_manage' ); ?>" />
		</form>
		<?php
		do_action( 'after_wcfm_articles_manage' );
		?>
	</div>
</div>