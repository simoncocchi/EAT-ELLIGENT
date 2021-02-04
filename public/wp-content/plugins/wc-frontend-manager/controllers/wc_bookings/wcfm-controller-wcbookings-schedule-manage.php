<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WC Booking Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/wc_boookings_schedule_manage
 * @version   4.0.7
 */

class WCFM_WCBookings_Schedule_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $WCFMu, $wpdb, $wcfm_wcb_schedule_update_form_data;
		
		$wcfm_wcb_schedule_update_form_data = array();
	  parse_str($_POST['wcfm_wcb_schedule_update_form'], $wcfm_wcb_schedule_update_form_data);
	  
	  // WCFM form custom validation filter
		$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_wcb_schedule_update_form_data, 'wcs_billing_schedule' );
		if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
			$custom_validation_error = __( 'There has some error in submitted data.', 'wc-frontend-manager' );
			if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
			echo '{"status": false, "message": "' . $custom_validation_error . '"}';
			die;
		}
	  
	  $booking_id = absint( $wcfm_wcb_schedule_update_form_data['booking_id'] );
	  
	  if( $booking_id ) {
			$booking    = new WC_Booking( $booking_id );
			
			$start_date = explode( '-', wc_clean( $wcfm_wcb_schedule_update_form_data['booking_start_date'] ) );
			$end_date   = explode( '-', wc_clean( $wcfm_wcb_schedule_update_form_data['booking_end_date'] ) );
			$start_time = explode( ':', wc_clean( $wcfm_wcb_schedule_update_form_data['booking_start_time'] ) );
			$end_time   = explode( ':', wc_clean( $wcfm_wcb_schedule_update_form_data['booking_end_time'] ) );
			$start      = mktime( $start_time[0], $start_time[1], 0, $start_date[1], $start_date[2], $start_date[0] );
			$end        = mktime( $end_time[0], $end_time[1], 0, $end_date[1], $end_date[2], $end_date[0] );
			
			$booking->set_props( array(
				'all_day'       => isset( $wcfm_wcb_schedule_update_form_data['_booking_all_day'] ),
				'end'           => $end,
				'start'         => $start,
			) );
	
			do_action( 'woocommerce_admin_process_booking_object', $booking );
	
			$booking->save();
			
			// Customer Notification
			$mailer        = WC()->mailer();
			$notification  = $mailer->emails['WC_Email_Booking_Notification'];
			$generate      = new WC_Bookings_ICS_Exporter;
			$attachments[] = $generate->get_booking_ics( $booking );
			
			$notification_subject = __( 'Booking schedule updated', 'wc-frontend-manager' );
			$notification_message = __( 'Booking schedule updated.', 'wc-frontend-manager' );
			$notification->reset_tags();
			$notification->trigger( $booking->get_id(), $notification_subject, $notification_message, $attachments );
	  	
	  	do_action( 'wcfm_wcb_schedule_update', $booking_id, $wcfm_wcb_schedule_update_form_data );
	  	
	  	echo '{"status": true, "message": "' . __( 'Booking schedule updated successfully', 'wc-frontend-manager' ) . '"}';
	  } else {
	  	echo '{"status": false, "message": "' . __( 'Booking schedule updated failed!', 'wc-frontend-manager' ) . '"}';
	  }
		 
		die;
	}
}