<?php
/**
 * WCFMu plugin view
 *
 * Marketplace Dokan Support
 * This template can be overridden by copying it to yourtheme/wcfm/dashboard/
 *
 * @author 		WC Lovers
 * @package 	wcfm/views
 * @version   3.3.0
 */
 
global $WCFM, $wpdb, $start_date, $end_date;

$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );

// Get products using a query - this is too advanced for get_posts :(
$stock          = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 1 ) );
$nostock        = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );

$query_from = apply_filters( 'wcfm_report_low_in_stock_query_from', "FROM {$wpdb->posts} as posts
	INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
	INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
	WHERE 1=1
	AND posts.post_type IN ( 'product', 'product_variation' )
	AND posts.post_status = 'publish'
	AND posts.post_author = {$current_user_id}
	AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
	AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
	AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$nostock}'
", $stock, $nostock );
$lowinstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );

$query_from = apply_filters( 'wcfm_report_out_of_stock_query_from', "FROM {$wpdb->posts} as posts
	INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
	INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
	WHERE 1=1
	AND posts.post_type IN ( 'product', 'product_variation' )
	AND posts.post_status = 'publish'
	AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
	AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$nostock}'
", $nostock );

$outofstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );

// Total Sales Amount
$gross_sales = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $current_user_id, 'month' );

// Total Earned Commission
$earned = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $current_user_id, 'month' );

// Total Received Commission
//$commission = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $current_user_id, 'month', true );

// Total item sold
$total_sell = $WCFM->wcfm_vendor_support->wcfm_get_total_sell_by_vendor( $current_user_id, 'month' );

// Counts
$order_count = 0;
$on_hold_count    = 0;
$processing_count = 0;

$sql = "SELECT commission.order_id, commission.order_status FROM {$wpdb->prefix}dokan_orders AS commission LEFT JOIN {$wpdb->posts} p ON commission.order_id = p.ID";
$sql .= " WHERE 1=1";
$sql .= " AND commission.seller_id = %d";
$status = dokan_withdraw_get_active_order_status_in_comma();
$sql .= " AND commission.order_status IN ({$status})";
$sql .= " AND MONTH( p.post_date ) = MONTH( NOW() )";
$sql .= " GROUP BY commission.order_id";

$vendor_orders = $wpdb->get_results( $wpdb->prepare( $sql, $current_user_id ) );
if( !empty($vendor_orders) ) {
	$order_count = count( $vendor_orders );
	foreach( $vendor_orders as $vendor_order ) {
		// Order exists check
		$order_post_title = get_the_title( $vendor_order->order_id );
		if( !$order_post_title ) continue;
		if( $vendor_order->order_id ) {
			if( $vendor_order->order_status == 'wc-processing' ) $processing_count++;
			if( $vendor_order->order_status == 'wc-on-hold' ) $on_hold_count++;
		}
	}
}

// Awaiting shipping
$unfulfilled_products = 0;

if( !empty($vendor_orders) ) {
	$order_count = count( $vendor_orders );
	foreach( $vendor_orders as $vendor_order ) {
		if( $vendor_order->order_id ) {
			$shippers = (array) get_post_meta( $vendor_order->order_id, 'wc_pv_shipped', true );
			if( !in_array($current_user_id, $shippers) ) $unfulfilled_products++;
		}
	}
}

if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) {
	include_once( $WCFM->plugin_path . 'includes/reports/class-dokan-report-sales-by-date.php' );
	$wcfm_report_sales_by_date = new Dokan_Report_Sales_By_Date( 'month' );
	$wcfm_report_sales_by_date->calculate_current_range( 'month' );
	$report_data   = $wcfm_report_sales_by_date->get_report_data();
}

$date_diff = date_diff( date_create(date('Ymd', $start_date)), date_create(date('Ymd', $end_date)) );

// WCFM Analytics
if( $wcfm_is_allow_analytics = apply_filters( 'wcfm_is_allow_analytics', true ) ) {
	include_once( $WCFM->plugin_path . 'includes/reports/class-wcfm-report-analytics.php' );
	$wcfm_report_analytics = new WCFM_Report_Analytics();
	$wcfm_report_analytics->chart_colors = apply_filters( 'wcfm_report_analytics_chart_colors', array(
				'view_count'       => '#C79810',
			) );
	$wcfm_report_analytics->calculate_current_range( '7day' );
}

?>

<div class="collapse wcfm-collapse" id="wcfm_order_details">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-chalkboard"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Dashboard', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
		<?php do_action( 'begin_wcfm_dashboard' ); ?>
		
		<?php $WCFM->template->get_template( 'dashboard/wcfm-view-dashboard-welcome-box.php' ); ?>
		
		<?php if( apply_filters( 'wcfm_is_pref_stats_box', true ) ) { ?>
			<div class="wcfm_dashboard_stats">
				<?php if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
					<?php if( apply_filters( 'wcfm_sales_report_is_allow_gross_sales', true ) ) { ?>
						<div class="wcfm_dashboard_stats_block">
							<a href="<?php echo get_wcfm_reports_url( 'month' ); ?>">
								<span class="wcfmfa fa-currency"><?php echo get_woocommerce_currency_symbol() ; ?></span>
								<div>
									<strong><?php echo wc_price( $gross_sales ); ?></strong><br />
									<?php _e( 'gross sales in this month', 'wc-frontend-manager' ); ?>
								</div>
							</a>
						</div>
					<?php } ?>
					<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) ) { ?>
						<div class="wcfm_dashboard_stats_block">
							<a href="<?php echo get_wcfm_reports_url( ); ?>">
								<span class="wcfmfa fa-money fa-money-bill-alt"></span>
								<div>
									<strong><?php echo wc_price( $earned ); ?></strong><br />
									<?php _e( 'earnings in this month', 'wc-frontend-manager' ); ?>
								</div>
							</a>
						</div>
					<?php } ?>
					<div class="wcfm_dashboard_stats_block">
						<a href="<?php echo apply_filters( 'sales_by_product_report_url', get_wcfm_reports_url( ), '' ); ?>">
							<span class="wcfmfa fa-cubes"></span>
							<div>
								<?php printf( _n( "<strong>%s item</strong>", "<strong>%s items</strong>", $total_sell, 'wc-frontend-manager' ), $total_sell ); ?>
								<br /><?php _e( 'sold in this month', 'wc-frontend-manager' ); ?>
							</div>
						</a>
					</div>
				<?php } ?>
				<?php if( $wcfm_is_allow_orders = apply_filters( 'wcfm_is_allow_orders', true ) ) { ?>
					<div class="wcfm_dashboard_stats_block">
						<a href="<?php echo get_wcfm_orders_url( ); ?>">
							<span class="wcfmfa fa-cart-plus"></span>
							<div>
								<?php printf( _n( "<strong>%s order</strong>", "<strong>%s orders</strong>", $order_count, 'wc-frontend-manager' ), $order_count ); ?>
								<br /><?php _e( 'received in this month', 'wc-frontend-manager' ); ?>
							</div>
						</a>
					</div>
				<?php } ?>
			</div>
			<div class="wcfm-clearfix"></div>
		<?php } ?>
		<?php do_action( 'wcfm_after_dashboard_stats_box' ); ?>
		
		<?php if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
			<div class="wcfm_dashboard_wc_reports_sales">
				<div class="wcfm-container">
					<div id="wcfm_dashboard_wc_reports_expander_sales" class="wcfm-content">
						<div id="poststuff" class="woocommerce-reports-wide">
							<div class="postbox">
								<div class="inside">
									<a class="chart_holder_anchor" href="<?php echo get_wcfm_reports_url( 'month' ); ?>">
										<?php $wcfm_report_sales_by_date->get_main_chart(0); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div>
		<?php } ?>
		
		<div class="wcfm_dashboard_wc_status">
			<div class="wcfm_dashboard_wc_status_data">
			
				<?php if ( $is_wcfm_analytics_enable = is_wcfm_analytics() ) { ?>
					<?php if ( $wcfm_is_allow_analytics = apply_filters( 'wcfm_is_allow_analytics', true ) ) { ?>
						<div class="wcfm_dashboard_wcfm_analytics">
							<div class="page_collapsible" id="wcfm_dashboard_wcfm_anaytics"><span class="wcfmfa fa-chart-line"></span><span class="dashboard_widget_head"><?php _e('Store Analytics', 'wc-frontend-manager'); ?></span></div>
							<div class="wcfm-container">
								<div id="wcfm_dashboard_wcfm_analytics_expander" class="wcfm-content">
									<div id="poststuff" class="woocommerce-reports-wide">
										<div class="postbox">
											<div class="inside">
												<?php if( WCFM_Dependencies::wcfma_plugin_active_check() ) { ?>
													<a class="chart_holder_anchor" href="<?php echo get_wcfm_analytics_url( 'month' ); ?>">
												<?php } ?>
														<?php $wcfm_report_analytics->get_main_chart(); ?>
												<?php if( WCFM_Dependencies::wcfma_plugin_active_check() ) { ?>
													</a>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
				
				<?php if ( !is_wcfm_analytics() || WCFM_Dependencies::wcfma_plugin_active_check() ) { ?>
					<div class="wcfm_dashboard_wcfm_product_stats">
						<div class="page_collapsible" id="wcfm_dashboard_wcfm_product_status"><span class="wcfmfa fa-cubes"></span><span class="dashboard_widget_head"><?php _e('Product Stats', 'wc-frontend-manager'); ?></span></div>
						<div class="wcfm-container">
							<div id="wcfm_dashboard_wcfm_product_stats_expander" class="wcfm-content">
								 <?php if ( apply_filters( 'wcfm_is_allow_manage_products', true ) ) { ?>
								 <a class="chart_holder_anchor" href="<?php echo get_wcfm_products_url( ); ?>">
								 <?php } ?>
									 <div id="product_stats-report"><canvas id="product_stats_report-canvas"></canvas></div>	
								 <?php if ( apply_filters( 'wcfm_is_allow_manage_products', true ) ) { ?>
								 </a>
								 <?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>
				
				<?php do_action( 'after_wcfm_dashboard_product_stats' ); ?>
				
				<div class="wcfm_dashboard_more_stats">
					<div class="page_collapsible" id="wcfm_dashboard_wc_status">
						<span class="wcfmfa fa-list fa-clock"></span>
						<span class="dashboard_widget_head"><?php _e('Store Stats', 'wc-frontend-manager'); ?></span>
					</div>
					<div class="wcfm-container">
						<div id="wcfm_dashboard_wc_status_expander" class="wcfm-content">
							<ul class="wc_status_list">
								<?php if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
									<?php
									if ( ( $top_seller = $WCFM->library->get_top_seller() ) && $top_seller->qty ) {
									?>
										<li class="best-seller-this-month">
											<a href="<?php echo apply_filters( 'sales_by_product_report_url',  get_wcfm_reports_url( ), $top_seller->product_id ); ?>">
											  <span class="wcfmfa fa-cube"></span>
												<?php printf( __( '%s top seller in last 7 days (sold %d)', 'wc-frontend-manager' ), '<strong>' . get_the_title( $top_seller->product_id ) . '</strong> - ', $top_seller->qty ); ?>
											</a>
										</li>
									<?php
									}
									?>
								<?php } ?>
								
								<?php do_action( 'after_wcfm_dashboard_sales_reports' ); ?>
								
								<?php if( $wcfm_is_allow_orders = apply_filters( 'wcfm_is_allow_orders', true ) ) { ?>
									<li class="processing-orders">
										<a href="<?php echo get_wcfm_orders_url( ); ?>">
										  <span class="wcfmfa fa-life-ring"></span>
											<?php printf( _n( "<strong>%s order</strong> - processing", "<strong>%s orders</strong> - processing", $processing_count, 'wc-frontend-manager' ), $processing_count ); ?>
										</a>
									</li>
									<?php if( apply_filters( 'wcfm_is_allow_shipping_tracking', true ) ) { ?>
										<li class="on-hold-orders">
											<a href="<?php echo get_wcfm_orders_url( ); ?>">
												<span class="wcfmfa fa-truck"></span>
												<?php printf( _n( "<strong>%s product</strong> - awaiting fulfillment", "<strong>%s products</strong> - awaiting fulfillment", $unfulfilled_products, 'wc-frontend-manager' ), $unfulfilled_products ); ?>
											</a>
										</li>
									<?php } ?>
								<?php } ?>	
								
								<?php do_action( 'after_wcfm_dashboard_orders' ); ?>
								
								<?php if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
									<li class="low-in-stock">
										<a href="<?php echo apply_filters( 'low_in_stock_report_url',  get_wcfm_reports_url( ) ); ?>">
										  <span class="wcfmfa fa-sort-amount-down"></span>
											<?php printf( _n( "<strong>%s product</strong> - low in stock", "<strong>%s products</strong> - low in stock", $lowinstock_count, 'wc-frontend-manager' ), $lowinstock_count ); ?>
										</a>
									</li>
									<li class="out-of-stock">
										<a href="<?php echo get_wcfm_reports_url( '', 'wcfm-reports-out-of-stock' ); ?>">
										  <span class="wcfmfa fa-times-circle"></span>
											<?php printf( _n( "<strong>%s product</strong> - out of stock", "<strong>%s products</strong> - out of stock", $outofstock_count, 'wc-frontend-manager' ), $outofstock_count ); ?>
										</a>
									</li>
								<?php } ?>
								
								<?php do_action( 'after_wcfm_dashboard_stock_reports' ); ?>
								
							</ul>
						</div>
					</div>
				</div>
				
			</div>
			
			<?php do_action( 'after_wcfm_dashboard_left_col' ); ?>
			
			<div class="wcfm_dashboard_wc_status_graph">
		
			  <?php if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
					
					<div class="wcfm_dashboard_wc_reports_pie">
						<div class="page_collapsible" id="wcfm_dashboard_wc_reports_pie"><span class="wcfmfa fa-chart-pie"></span><span class="dashboard_widget_head"><?php _e('Sales by Product', 'wc-frontend-manager'); ?></span></div>
						<div class="wcfm-container">
							<div id="wcfm_dashboard_wc_reports_expander_pie" class="wcfm-content">
								<a class="chart_holder_anchor" href="<?php echo apply_filters( 'sales_by_product_report_url',  get_wcfm_reports_url( ), ( $top_seller ) ? $top_seller->product_id : '' ); ?>">
									<div id="sales-piechart"><canvas id="sales-piechart-canvas"></canvas></div>
								</a>
							</div>
						</div>
					</div>
					
			    <?php do_action('after_wcfm_dashboard_sales_report'); ?>
			  <?php } ?>
			  
			  <?php if ( is_wcfm_analytics() && WCFM_Dependencies::wcfma_plugin_active_check() ) { ?>
					<?php if ( $wcfm_is_allow_analytics = apply_filters( 'wcfm_is_allow_analytics', true ) ) { ?>
						<div class="wcfm_dashboard_wcfm_region_stats">
							<div class="page_collapsible" id="wcfm_dashboard_wcfm_region_status"><span class="wcfmfa fa-globe"></span><span class="dashboard_widget_head"><?php _e('Top Regions', 'wc-frontend-manager'); ?></span></div>
							<div class="wcfm-container">
								<div id="wcfm_dashboard_wcfm_region_stats_expander" class="wcfm-content">
									 <a class="chart_holder_anchor" href="<?php echo get_wcfm_analytics_url( 'month' ); ?>">
										 <div id="wcfm_world_map_analytics_view"></div>
										 <?php
										 global $WCFMa;
										 $WCFMa->library->world_map_analytics_data(); 
										 ?>
									 </a>
								</div>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
				
				<?php do_action('after_wcfm_dashboard_zone_analytics'); ?>
			  
			  <?php if( $wcfm_is_allow_notice = apply_filters( 'wcfm_is_allow_notice', true ) ) { ?>
					<div class="wcfm_dashboard_latest_topics">
						<div class="page_collapsible" id="wcfm_dashboard_latest_topics"><span class="wcfmfa fa-bullhorn"></span><span class="dashboard_widget_head"><?php _e('Latest Topics', 'wc-frontend-manager'); ?></span></div>
						<div class="wcfm-container">
							<div id="wcfm_dashboard_latest_topics_expander" class="wcfm-content">
								<?php
								$args = array(
									'posts_per_page'   => 5,
									'offset'           => 0,
									'orderby'          => 'date',
									'order'            => 'DESC',
									'post_type'        => 'wcfm_notice',
									'post_parent'      => 0,
									'post_status'      => array('draft', 'pending', 'publish'),
									'suppress_filters' => 0 
								);
								$args = apply_filters( 'wcfm_notice_args', $args );
								$wcfm_notices_array = get_posts( $args );
								
								if( !empty( $wcfm_notices_array ) ) {
									foreach($wcfm_notices_array as $wcfm_notices_single) {
										echo '<div class="wcfm_dashboard_latest_topic"><a href="' . get_wcfm_notice_view_url($wcfm_notices_single->ID) . '" class="wcfm_dashboard_item_title"><span class="wcfmfa fa-bullhorn"></span>' . substr( $wcfm_notices_single->post_title, 0, 80 ) . ' ...</a></div>';
									}
								} else {
									_e( 'There is no topic yet!!', 'wc-frontend-manager' );
								}
								?>
							</div>
						</div>
					</div>
				<?php } ?>
				
			</div>
			<?php do_action( 'after_wcfm_dashboard_right_col' ); ?>
		</div>
	</div>
</div>