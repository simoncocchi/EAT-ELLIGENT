<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Marketplace Refund Request Approve Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/controllers/refund
 * @version   1.0.0
 */

class WCFMmp_Refund_Requests_Approve_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$wcfm_refund_manage_form_data = array();
	  parse_str($_POST['wcfm_refund_manage_form'], $wcfm_refund_manage_form_data);
	  
	  $commissions = array();
	  if( isset( $wcfm_refund_manage_form_data['refunds'] ) && !empty( $wcfm_refund_manage_form_data['refunds'] ) ) {
	  	$refunds     = $wcfm_refund_manage_form_data['refunds'];
	  	$refund_note = strip_tags( $wcfm_refund_manage_form_data['refund_note'] );
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_refund_manage_form_data, 'refund_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-multivendor-marketplace' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
	  	
			$order_ids = '';
			$commission_ids = '';
			$total_commission = 0;
			
			foreach( $refunds as $refund_id ) {
				
				// Update refund status
				$refund_update_status = $WCFMmp->wcfmmp_refund->wcfmmp_refund_status_update_by_refund( $refund_id, 'completed', $refund_note );
				
				if( !$refund_update_status ) {
					echo '{"status": false, "message": "' . __('Refund processing failed, please check wcfm log.', 'wc-multivendor-marketplace') . '"}';
					die;
				}
				
				do_action( 'wcfmmp_refund_request_approved', $refund_id );
			}
			echo '{"status": true, "message": "' . __('Refund requests successfully approved.', 'wc-multivendor-marketplace') . '"}';
	  } else {
	  	echo '{"status": false, "message": "' . __('No refunds selected for approve', 'wc-multivendor-marketplace') . '"}';
	  }
		
		die;
	}
}

?>

<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCfM Marketplace Refund Request Cancel Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/controllers/refund
 * @version   1.0.0
 */

class WCFMmp_Refund_Requests_Cancel_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$wcfm_refund_manage_form_data = array();
	  parse_str($_POST['wcfm_refund_manage_form'], $wcfm_refund_manage_form_data);
	  
	  $commissions = array();
	  if( isset( $wcfm_refund_manage_form_data['refunds'] ) && !empty( $wcfm_refund_manage_form_data['refunds'] ) ) {
	  	$refunds = $wcfm_refund_manage_form_data['refunds'];
	  	$refund_note = strip_tags( $wcfm_refund_manage_form_data['refund_note'] );
	  	
	  	// WCFM form custom validation filter
			$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_refund_manage_form_data, 'refund_manage' );
			if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
				$custom_validation_error = __( 'There has some error in submitted data.', 'wc-multivendor-marketplace' );
				if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
				echo '{"status": false, "message": "' . $custom_validation_error . '"}';
				die;
			}
	  	
			$order_ids = '';
			$commission_ids = '';
			$total_commission = 0;
			
			foreach( $refunds as $refund_id ) {
				
				// Update refund status
				$WCFMmp->wcfmmp_refund->wcfmmp_refund_status_update_by_refund( $refund_id, 'cancelled', $refund_note );
				
				do_action( 'wcfmmp_refund_request_cancelled', $refund_id );
			}
			echo '{"status": true, "message": "' . __('Refund request(s) successfully rejected.', 'wc-multivendor-marketplace') . '"}';
	  } else {
	  	echo '{"status": false, "message": "' . __('No refund(s) selected for approve', 'wc-multivendor-marketplace') . '"}';
	  }
		
		die;
	}
}