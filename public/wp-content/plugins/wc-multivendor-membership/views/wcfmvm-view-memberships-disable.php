<?php
/**
 * WCFM plugin view
 *
 * WCFMgs Memberships Disable Template
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/views
 * @version   2.0.8
 */

global $WCFM, $WCFMvm;
  
?>

<div class="collapse wcfm-collapse" id="wcfm_memberships_listing">
  <div class="wcfm-collapse-content">
    <div class="wcfm-container">
			<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
				<p><span class="wcfmfa fa-warning"></span>
				<?php printf( __( 'Restricted: You are not allowed to access this page. May be you do not have a %sMulti-vendor Plugin%s on your site.', 'wc-multivendor-membership' ), '<strong>', '</strong>' ); ?></p>
			</div>
		</div>
	</div>
</div>