<?php
/**
 * WCFM plugin views
 *
 * Plugin Third Party Article Manage Views
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/article-manager
 * @version   6.3.4
 */
global $wp, $WCFM;

// Yoast SEO Support
$yoast_wpseo_focuskw_text_input = '';
$yoast_wpseo_metadesc = '';

// All in One SEO Support
$aiosp_title = '';
$aiosp_description = '';

// Rank Math SEO Support
$rank_math_focus_keyword = '';
$rank_math_description = '';

if( isset( $wp->query_vars['wcfm-articles-manage'] ) && !empty( $wp->query_vars['wcfm-articles-manage'] ) ) {
	$article_id = absint( $wp->query_vars['wcfm-articles-manage'] );
	if( $article_id ) {
		// Yoast SEO Support
		if( WCFM_Dependencies::wcfm_yoast_plugin_active_check() || WCFM_Dependencies::wcfm_yoast_premium_plugin_active_check() ) {
			$yoast_wpseo_focuskw_text_input = get_post_meta( $article_id, '_yoast_wpseo_focuskw', true );
			$yoast_wpseo_metadesc = get_post_meta( $article_id, '_yoast_wpseo_metadesc', true );
		}
		
		// Yoast SEO Support
		if( WCFM_Dependencies::wcfm_all_in_one_seo_plugin_active_check() || WCFM_Dependencies::wcfm_all_in_one_seo_pro_plugin_active_check() ) {
			$aiosp_title = get_post_meta( $article_id, '_aioseop_title', true );
			$aiosp_description = get_post_meta( $article_id, '_aioseop_description', true );
		}
		
		// Rank MathYoast SEO Support
		if( WCFM_Dependencies::wcfm_rankmath_seo_plugin_active_check() ) {
			$rank_math_focus_keyword = get_post_meta( $article_id, 'rank_math_focus_keyword', true );
			$rank_math_description = get_post_meta( $article_id, 'rank_math_description', true );
		}
	}
}

?>
  
  <?php if( $allow_seo = apply_filters( 'wcfm_is_allow_seo', true ) ) { ?>
			<?php if( WCFM_Dependencies::wcfm_yoast_plugin_active_check() || WCFM_Dependencies::wcfm_yoast_premium_plugin_active_check() ) { ?>
				<!-- collapsible 1 - Yoast SEO Support -->
				<div class="page_collapsible products_manage_yoast simple variable grouped external booking" id="wcfm_products_manage_form_yoast_head"><label class="fab fa-yoast"></label>&nbsp;<?php _e('Yoast SEO', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container simple variable external grouped booking">
					<div id="wcfm_products_manage_form_yoast_expander" class="wcfm-content">
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_articles_manage_fields_yoast', array(  
																																																"yoast_wpseo_focuskw_text_input" => array('label' => __('Enter a focus keyword', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $yoast_wpseo_focuskw_text_input, 'hints' => __( 'It should appear in title and first paragraph of the copy.', 'wc-frontend-manager' )),
																																																"yoast_wpseo_metadesc" => array('label' => __('Meta description', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external grouped booking', 'value' => $yoast_wpseo_metadesc, 'hints' => __( 'It should not be more than 156 characters.', 'wc-frontend-manager' ))
																																											)) );
						?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
			<?php } ?>
		<?php } ?>
		
		
		<?php if( $allow_seo = apply_filters( 'wcfm_is_allow_seo', true ) ) { ?>
			<?php if( WCFM_Dependencies::wcfm_all_in_one_seo_plugin_active_check() || WCFM_Dependencies::wcfm_all_in_one_seo_pro_plugin_active_check() ) { ?>
				<!-- collapsible 1 - All in One SEO Support -->
				<div class="page_collapsible products_manage_yoast simple variable grouped external booking" id="wcfm_products_manage_form_yoast_head"><label class="wcfmfa fa-globe"></label>&nbsp;<?php _e('SEO', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container simple variable external grouped booking">
					<div id="wcfm_products_manage_form_yoast_expander" class="wcfm-content">
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_articles_manage_fields_aiosp', array(  
																																																"aiosp_title" => array('label' => __('Title', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $aiosp_title, 'hints' => __( 'Most search engines use a maximum of 60 chars for the title.', 'wc-frontend-manager' )),
																																																"aiosp_description" => array('label' => __('Description', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external grouped booking', 'value' => $aiosp_description, 'hints' => __( 'Most search engines use a maximum of 160 chars for the description.', 'wc-frontend-manager' ))
																																											)) );
						?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
			<?php } ?>
		<?php } ?>
		
		<?php if( $allow_seo = apply_filters( 'wcfm_is_allow_seo', true ) ) { ?>
			<?php if( WCFM_Dependencies::wcfm_rankmath_seo_plugin_active_check() ) { ?>
				<!-- collapsible 1 - Rank Math SEO Support -->
				<div class="page_collapsible products_manage_yoast simple variable grouped external booking" id="wcfm_products_manage_form_yoast_head"><label class="wcfmfa fa-globe"></label>&nbsp;<?php _e('SEO', 'wc-frontend-manager'); ?><span></span></div>
				<div class="wcfm-container simple variable external grouped booking">
					<div id="wcfm_products_manage_form_yoast_expander" class="wcfm-content">
						<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_articles_manage_fields_rank_math', array(  
																																																"rank_math_focus_keyword" => array('label' => __('Enter focus keyword(s) comma separated', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_title wcfm_ele simple variable external grouped booking', 'value' => $rank_math_focus_keyword, 'hints' => __( 'It should appear in title and first paragraph of the copy.', 'wc-frontend-manager' )),
																																																"rank_math_description" => array('label' => __('Meta Description', 'wc-frontend-manager') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele simple variable external grouped booking', 'label_class' => 'wcfm_ele wcfm_title simple variable external grouped booking', 'value' => $rank_math_description, 'hints' => __( 'Most search engines use a maximum of 160 chars for the description.', 'wc-frontend-manager' ))
																																											)) );
						?>
					</div>
				</div>
				<!-- end collapsible -->
				<div class="wcfm_clearfix"></div>
			<?php } ?>
		<?php } ?>