<?php
/**
 * WCFM plugin view
 *
 * WCFMvm Memberships No Free Template
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/templates
 * @version   1.2.0
 */

global $WCFM, $WCFMvm;
  
?>

<div id="wcfmu-feature-missing-message" class="wcfm-warn-message wcfm-wcfmu" style="display: block;">
	<p><span class="wcfmfa fa-warning"></span>
	<?php printf( __( 'Restricted: There is no %sFREE Membership Plan%s in the system or you are not allowed to access this page. Please contact %sStore Admin%s for more details.', 'wc-multivendor-membership' ), '<strong>', '</strong>', '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
</div>