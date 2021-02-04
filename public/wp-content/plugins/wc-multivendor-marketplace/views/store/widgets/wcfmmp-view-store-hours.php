<?php
/**
 * The Template for displaying store sidebar hours.
 *
 * @package WCfM Markeplace Views Store Sidebar Hours
 *
 * For edit coping this to yourtheme/wcfm/store/widgets
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

foreach( $wcfm_store_hours_day_times as $wcfm_store_hours_day => $wcfm_store_hours_day_time_slots ) {
	if( in_array( $wcfm_store_hours_day, $wcfm_store_hours_off_days ) ) continue;
	if( !isset( $wcfm_store_hours_day_time_slots[0] ) || !isset( $wcfm_store_hours_day_time_slots[0]['start'] ) ) return;
	if( empty( $wcfm_store_hours_day_time_slots[0]['start'] ) || empty( $wcfm_store_hours_day_time_slots[0]['end'] ) ) continue;
	
	echo '<span class="wcfmmp-store-hours-day">' . $weekdays[$wcfm_store_hours_day] . ':</span>';
	
	foreach( $wcfm_store_hours_day_time_slots as $slot => $wcfm_store_hours_day_time_slot ) {
		echo "<br />" . date_i18n( wc_time_format(), strtotime( $wcfm_store_hours_day_time_slot['start'] ) ) . " - " . date_i18n( wc_time_format(), strtotime( $wcfm_store_hours_day_time_slot['end'] ) );
	}
	echo '<br /><br />';
}

?>