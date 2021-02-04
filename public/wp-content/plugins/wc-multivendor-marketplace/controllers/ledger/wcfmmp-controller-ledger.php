<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WCFM Marketplace Ledger Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/ledger/wcfmmp/controllers
 * @version   1.0.0
 */

class WCFMmp_Ledger_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCFMmp;
		
		$vendor_id = $WCFMmp->vendor_id;
		
		$length = sanitize_text_field( $_POST['length'] );
		$offset = sanitize_text_field( $_POST['start'] );
		
		$the_orderby = ! empty( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'ID';
		$the_order   = ( ! empty( $_POST['order'] ) && 'asc' === $_POST['order'] ) ? 'ASC' : 'DESC';
		
    $status_filter = '';
    if( isset($_POST['status_type']) && ( $_POST['status_type'] != '' ) ) {
    	$status_filter = $status_filter = ' AND `reference_status` = "' . sanitize_text_field( $_POST['status_type'] ) . '"';
    }
    
    $type_filter = '';
    if( isset($_POST['type']) && ( $_POST['type'] != '' ) ) {
    	$type_filter = ' AND `reference` = "' . sanitize_text_field( $_POST['type'] ) . '"';
    }
    
		$sql = "SELECT COUNT(ID) from {$wpdb->prefix}wcfm_marketplace_vendor_ledger";
		$sql .= " WHERE 1=1";
		$sql .= " AND `vendor_id` = " . $vendor_id;
		$sql .= $status_filter;
		$sql .= $type_filter;
		
  	$wcfm_ledger_items = $wpdb->get_var($sql);
		if( !$wcfm_ledger_items ) $wcfm_ledger_items = 0;
		
		$sql = "SELECT * from {$wpdb->prefix}wcfm_marketplace_vendor_ledger";
		$sql .= " WHERE 1=1";
		$sql .= " AND `vendor_id` = " . $vendor_id;
		$sql .= $status_filter;
		$sql .= $type_filter;
		$sql .= " ORDER BY `{$the_orderby}` {$the_order}";
		$sql .= " LIMIT {$length}";
		$sql .= " OFFSET {$offset}";
		
		$wcfm_ledger_array = $wpdb->get_results($sql);
		
		// Generate Ledger JSON
		$wcfm_ledger_json = '';
		$wcfm_ledger_json = '{
															"draw": ' . sanitize_text_field( $_POST['draw'] ) . ',
															"recordsTotal": ' . $wcfm_ledger_items . ',
															"recordsFiltered": ' . $wcfm_ledger_items . ',
															"data": ';
		if(!empty($wcfm_ledger_array)) {
			$index = 0;
			$wcfm_ledger_json_arr = array();
			foreach( $wcfm_ledger_array as $wcfm_ledger_single ) {
				
				// Status
				$wcfm_ledger_json_arr[$index][] =  '<span class="order-status tips wcicon-status-' . sanitize_title( $wcfm_ledger_single->reference_status ) . ' text_tip" data-tip="' . $WCFMmp->wcfmmp_vendor->wcfmmp_vendor_order_status_name( $wcfm_ledger_single->reference_status ) . '"></span>';
				
				// Type
				$wcfm_ledger_json_arr[$index][] = '<div class="wcfmmp-ledger-type wcfmmp-ledger-type-' . $wcfm_ledger_single->reference . '">' . $WCFMmp->wcfmmp_ledger->wcfmmp_vendor_ledger_reference_name( $wcfm_ledger_single->reference ) . '</div>';
				
        // Details
        $wcfm_ledger_json_arr[$index][] = $wcfm_ledger_single->reference_details;
        
        // Credit
        if( $wcfm_ledger_single->credit ) {
        	$wcfm_ledger_json_arr[$index][] = '<div class="wcfmmp-ledger-credit">' . wc_price( $wcfm_ledger_single->credit ) . '</div>';
        } else {
        	$wcfm_ledger_json_arr[$index][] = '';
        }
        
        // Debit
        if( $wcfm_ledger_single->debit ) {
        	$wcfm_ledger_json_arr[$index][] = '<div class="wcfmmp-ledger-debit">' . wc_price( $wcfm_ledger_single->debit ) . '</div>';
        } else {
        	$wcfm_ledger_json_arr[$index][] = '';
        }
        
        // Dated
        $wcfm_ledger_json_arr[$index][] = date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime($wcfm_ledger_single->created) );
        
				$index++;
			}												
		}
		if( !empty($wcfm_ledger_json_arr) ) $wcfm_ledger_json .= json_encode($wcfm_ledger_json_arr);
		else $wcfm_ledger_json .= '[]';
		$wcfm_ledger_json .= '
													}';
													
		echo $wcfm_ledger_json;
	}
}