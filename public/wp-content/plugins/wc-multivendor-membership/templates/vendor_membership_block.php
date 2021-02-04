<?php
/**
 * WCFM plugin view
 *
 * WCFMgs Memberships BLock Template
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/templates
 * @version   1.0.0
 */

global $WCFM, $WCFMvm;
  
?>

<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
	<p><span class="wcfmfa fa-exclamation-triangle"></span>
	<?php printf( __( 'Restricted: You are not allowed to access this page. May be you already have a %sMembership Plan%s or your %sUser Role%s does not allow for this action. %sPlease contact %sStore Admin%s for more details.', 'wc-multivendor-membership' ), '<strong>', '</strong>', '<strong>', '</strong>', '<br />', '<strong>', '</strong>' ); ?></p>
</div>