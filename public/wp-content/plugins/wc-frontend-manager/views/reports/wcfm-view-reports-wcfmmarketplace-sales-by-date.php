<?php
/**
 * WCFM plugin view
 *
 * WCFM Reports - WCfM Marketplace Sales by Date View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view/reports/
 * @version   5.0.0
 */
 
$wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true );
if( !$wcfm_is_allow_reports ) {
	wcfm_restriction_message_show( "Reports" );
	return;
}

global $wp, $WCFM, $wpdb, $WCFMmp;

if( isset( $wp->query_vars['wcfm-reports-sales-by-date'] ) && !empty( $wp->query_vars['wcfm-reports-sales-by-date'] ) ) {
	$wcfm_report_type = $wp->query_vars['wcfm-reports-sales-by-date'];
}

$sales_by_vendor_mode = false;
$sales_by_no_vendor_mode = false;
if( $WCFM->is_marketplace && ( $WCFM->is_marketplace == 'wcfmmarketplace' ) ) {
	if( isset( $wp->query_vars['wcfm-reports-sales-by-vendor'] ) ) {
		if( !wcfm_is_vendor() ) {
			if( !empty( $wp->query_vars['wcfm-reports-sales-by-vendor'] ) ) {
				$wcfm_vendor = $wp->query_vars['wcfm-reports-sales-by-vendor'];
				if( $wcfm_vendor && wcfm_is_vendor( $wcfm_vendor ) ) {
					$WCFMmp->vendor_id = absint($wcfm_vendor);
					$sales_by_vendor_mode = true;
				} else {
					wcfm_restriction_message_show( "Invalid Vendor" );
					return;
				}
			} else {
				$sales_by_no_vendor_mode = true;
			}
		} else {
			wcfm_restriction_message_show( "Sales by Vendor" );
			return;
		}
	}
} else {
	wcfm_restriction_message_show( "Sales by Vendor" );
	return;
}

include_once( $WCFM->plugin_path . 'includes/reports/class-wcfmmarketplace-report-sales-by-date.php' );

$wcfm_report_sales_by_date = new WCFM_Marketplace_Report_Sales_By_Date();

$ranges = array(
	'year'         => __( 'Year', 'wc-frontend-manager' ),
	'last_month'   => __( 'Last Month', 'wc-frontend-manager' ),
	'month'        => __( 'This Month', 'wc-frontend-manager' ),
	'7day'         => __( 'Last 7 Days', 'wc-frontend-manager' )
);

$wcfm_report_sales_by_date->chart_colors = apply_filters( 'wcfm_vendor_sales_by_date_chart_colors', array(
			'average'            => '#95a5a6',
			'order_count'        => '#f8cb00',
			'item_count'         => '#ffc107',
			'tax_amount'         => '#73818f',
			'shipping_amount'    => '#6f42c1',
			'earned'             => '#20a8d8',
			'commission'         => '#20c997',
			'gross_sales_amount' => '#3498db',
			'refund'             => '#e83e8c',
		) );

$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';

if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
	$current_range = '7day';
}

$wcfm_report_sales_by_date->calculate_current_range( $current_range );

?>

<div class="collapse wcfm-collapse" id="wcfm_report_details">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-chart-line"></span>
		<span class="wcfm-page-heading-text">
		  <?php if( $sales_by_vendor_mode ) { ?>
		  	<?php if ( 'custom' === $current_range && isset( $_GET['start_date'], $_GET['end_date'] ) ) : ?>
				<?php echo __('Sales by', 'wc-frontend-manager') . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ); ?> - <?php echo esc_html( sprintf( _x( 'From %s to %s', 'start date and end date', 'wc-frontend-manager' ), wc_clean( $_GET['start_date'] ), wc_clean( $_GET['end_date'] ) ) ); ?><span></span>
				<?php else : ?>
					<?php echo __('Sales by', 'wc-frontend-manager') . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ); ?> - <?php echo esc_html( $ranges[ $current_range ] ); ?><span></span>
				<?php endif; ?>
		  <?php } else { ?>
				<?php if ( 'custom' === $current_range && isset( $_GET['start_date'], $_GET['end_date'] ) ) : ?>
				<?php _e('Sales by Date', 'wc-frontend-manager'); ?> - <?php echo esc_html( sprintf( _x( 'From %s to %s', 'start date and end date', 'wc-frontend-manager' ), wc_clean( $_GET['start_date'] ), wc_clean( $_GET['end_date'] ) ) ); ?><span></span>
				<?php else : ?>
					<?php _e('Sales by Date', 'wc-frontend-manager'); ?> - <?php echo esc_html( $ranges[ $current_range ] ); ?><span></span>
				<?php endif; ?>
			<?php } ?>
		</span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<?php $WCFM->template->get_template( 'reports/wcfm-view-reports-menu.php' ); ?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div>
	  
	  <?php if( $sales_by_vendor_mode ) { ?>
	  	<div class="wcfm-container wcfm-top-element-container">
	  	  <?php
	  	  $vendor_store = wcfm_get_vendor_store_name( $WCFMmp->vendor_id );
				$store_logo = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( $WCFMmp->vendor_id );
				if( !$store_logo ) {
					$store_logo = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp.png' );
				}
	  	  ?>
				<img class="vendor_store_logo" src="<?php echo $store_logo; ?>" alt="Store Logo" />
				<h2>
					<?php 
						echo $vendor_store;
					?>
				</h2>
				
				<label class="wcfm_vendor_manage_change_vendor">
					<?php
					if( apply_filters( 'wcfm_is_products_vendor_filter', true ) ) {
						if( !wcfm_is_vendor() ) {
							$vendor_arr = array(); //$WCFM->wcfm_vendor_support->wcfm_get_vendor_list( true );
							$vendor_arr[$WCFMmp->vendor_id] = $vendor_store;
							$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 250px;' ), 'value' => $WCFMmp->vendor_id )
																												 ) );
						}
					}
					?>
				</label>
				
				<?php
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_vendors_manage_url($WCFMmp->vendor_id).'" data-tip="' . __( 'Manage', 'wc-frontend-manager' ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . '"><span class="wcfmfa fa-user-alt"></span></a>';
				?>
				<div class="wcfm-clearfix"></div>
			</div>
			<div class="wcfm-clearfix"></div><br />
	  	
	  <?php } elseif( $sales_by_no_vendor_mode) { ?>
	  	<div class="wcfm-container wcfm-top-element-container">
	  	  <h2>
					<?php _e( 'Choose Vendor', 'wc-frontend-manager' ) ; ?>
				</h2>
				
				<label class="wcfm_vendor_manage_change_vendor">
					<?php
					if( apply_filters( 'wcfm_is_products_vendor_filter', true ) ) {
						if( !wcfm_is_vendor() ) {
							$vendor_arr = array();
							$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 250px;' ), 'value' => $WCFMmp->vendor_id )
																												 ) );
						}
					}
					?>
				</label>
				
				<?php
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_vendors_url().'" data-tip="' . __( 'Vendors', 'wc-frontend-manager' ) . '"><span class="wcfmfa fa-user-alt"></span></a>';
				?>
	  	  <div class="wcfm-clearfix"></div>
	  	</div>
	  	<div class="wcfm-clearfix"></div><br />
	  <?php } else { ?>
	  	<br />
	  <?php } ?>
	  
	  <?php if( !$sales_by_no_vendor_mode ) { ?>
			<div class="wcfm-container">
				<div id="wcfm_reports_sales_by_date_expander" class="wcfm-content">
				
					<?php
						include( $WCFM->plugin_path . '/views/reports/wcfm-html-report-sales-by-date.php');
					?>
				
				</div>
			</div>
		<?php } ?>
	</div>
	
	<?php do_action( 'wcfm_wcfmmarketplace_report_sales_by_date_after' ); ?>
</div>