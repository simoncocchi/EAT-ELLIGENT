<?php

	//bp_load_theme_functions();

	global $bp;
	
	$bp->displayed_user->id = get_current_user_id();
	$bp->displayed_user->userdata = wp_get_current_user();
	$bp->displayed_user->domain = $bp->loggedin_user->domain;
	
	// Adding Menu Item
	$pages = get_option("wcfm_page_options", array());
	if( !isset( $pages['wc_frontend_manager_page_id'] ) ) return;
	$wcfm_page = get_post( $pages['wc_frontend_manager_page_id'] );
	
	$args = array(
					'name' => $wcfm_page->post_title,
					'slug' => $wcfm_page->post_name,
					'default_subnav_slug' => $wcfm_page->post_name,
					'position' => 50,
					'screen_function' => 'bp_wcfm_user_nav_item_screen',
					'item_css_id' => $wcfm_page->post_name
	);

	bp_core_new_nav_item( $args );
	
	// Avatar height - padding - 1/2 avatar height.
	$top_offset    = 150;
	$avatar_height = apply_filters( 'bp_core_avatar_full_height', $top_offset );
	
	if ( $avatar_height > $top_offset ) {
		$top_offset = $avatar_height;
	}
	
	$avatar_offset = $avatar_height - 5; // - round( (int) bp_core_avatar_full_height() / 2 );

	// Header content offset + spacing.
	$top_offset  = bp_core_avatar_full_height() - 10;
	$left_offset = bp_core_avatar_full_width() + 20;
	
	$params["height"] = $top_offset + round( $avatar_height / 2 );
	
	$params['cover_image'] = bp_attachments_get_attachment('url', array(
																																			'object_dir' => 'members',
																																			'item_id' => $bp->displayed_user->id,
																																		));

	$cover_image = ( !empty( $params['cover_image'] ) ) ? 'background-image: url(' . $params['cover_image'] . ');' : '';

	$hide_avatar_style = '';

	// Adjust the cover image header, in case avatars are completely disabled.
	if ( ! buddypress()->avatar->show_avatars ) {
		$hide_avatar_style = '
			#wcfm-main-content #item-header-cover-image #item-header-avatar {
				display:  none;
			}
		';

		if ( bp_is_user() ) {
			$hide_avatar_style = '
				#wcfm-main-content #item-header-cover-image #item-header-avatar a {
					display: block;
					height: ' . $top_offset . 'px;
					margin: 0 15px 19px 0;
				}

				#wcfm-main-content div#item-header #item-header-cover-image #item-header-content {
					margin-left: auto;
				}
			';
		}
	}
?>

<div id="buddypress" class="buddypress-wrap bp-dir-hori-nav">
  <div class="bp-wrap">
		<div id="item-nav">
			<nav class="main-navs no-ajax bp-navs single-screen-navs horizontal users-nav" id="object-nav" role="navigation" aria-label="Member menu">
				<ul>
					<?php bp_get_displayed_user_nav(); ?>
					<?php do_action( 'bp_member_options_nav' ); ?>
				</ul>
			</nav>
		</div><!-- #item-nav -->
	</div>
</div>
<div class="wcfm-clearfix"></div><br />