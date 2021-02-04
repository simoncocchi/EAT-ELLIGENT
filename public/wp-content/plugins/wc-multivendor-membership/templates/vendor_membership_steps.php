<?php
/**
 * WCFM plugin view
 *
 * WCFMgs Memberships Registration Steps Template
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/templates
 * @version   1.0.0
 */

global $WCFM, $WCFMvm;

$steps = wcfm_membership_registration_steps();
$current_step = wcfm_membership_registration_current_step();

?>

<ol class="wc-progress-steps">
	<?php foreach ( $steps as $step_key => $step ) : ?>
		<li class="<?php
			if ( $step_key === $current_step ) {
				echo 'active';
			} elseif ( array_search( $current_step, array_keys( $steps ) ) > array_search( $step_key, array_keys( $steps ) ) ) {
				echo 'done';
			}
		?>"><?php echo esc_html( $step ); ?></li>
	<?php endforeach; ?>
</ol>