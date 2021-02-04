<?php
/**
 * WCFM plugin view
 *
 * WCFM Capability Settings View
 *
 * @author 		WC Lovers
 * @package 	wcfm/view
 * @version   2.4.7
 */

global $WCFM;

$wcfm_is_allow_capability_controller = apply_filters( 'wcfm_is_allow_capability_controller', true );
if( wcfm_is_vendor() || !$wcfm_is_allow_capability_controller ) {
	wcfm_restriction_message_show( "Capability" );
	return;
}

$wcfm_capability_options = get_option( 'wcfm_capability_options', array() );

// Product Capabilities
$submit_products = ( isset( $wcfm_capability_options['submit_products'] ) ) ? $wcfm_capability_options['submit_products'] : 'no';
$add_products = ( isset( $wcfm_capability_options['add_products'] ) ) ? $wcfm_capability_options['add_products'] : 'no';
$publish_products = ( isset( $wcfm_capability_options['publish_products'] ) ) ? $wcfm_capability_options['publish_products'] : 'no';
$edit_live_products = ( isset( $wcfm_capability_options['edit_live_products'] ) ) ? $wcfm_capability_options['edit_live_products'] : 'no';
$publish_live_products = ( isset( $wcfm_capability_options['publish_live_products'] ) ) ? $wcfm_capability_options['publish_live_products'] : 'no';
$delete_products = ( isset( $wcfm_capability_options['delete_products'] ) ) ? $wcfm_capability_options['delete_products'] : 'no';

$simple = ( isset( $wcfm_capability_options['simple'] ) ) ? $wcfm_capability_options['simple'] : 'no';
$variable = ( isset( $wcfm_capability_options['variable'] ) ) ? $wcfm_capability_options['variable'] : 'no';
$grouped = ( isset( $wcfm_capability_options['grouped'] ) ) ? $wcfm_capability_options['grouped'] : 'no';
$external = ( isset( $wcfm_capability_options['external'] ) ) ? $wcfm_capability_options['external'] : 'no';

$inventory = ( isset( $wcfm_capability_options['inventory'] ) ) ? $wcfm_capability_options['inventory'] : 'no';
$shipping = ( isset( $wcfm_capability_options['shipping'] ) ) ? $wcfm_capability_options['shipping'] : 'no';
$taxes = ( isset( $wcfm_capability_options['taxes'] ) ) ? $wcfm_capability_options['taxes'] : 'no';
$linked = ( isset( $wcfm_capability_options['linked'] ) ) ? $wcfm_capability_options['linked'] : 'no';
$attributes = ( isset( $wcfm_capability_options['attributes'] ) ) ? $wcfm_capability_options['attributes'] : 'no';
$advanced = ( isset( $wcfm_capability_options['advanced'] ) ) ? $wcfm_capability_options['advanced'] : 'no';
$catalog = ( isset( $wcfm_capability_options['catalog'] ) ) ? $wcfm_capability_options['catalog'] : 'no';

// Marketplace Capabilities
$vendor_sold_by      = ( isset( $wcfm_capability_options['sold_by'] ) ) ? $wcfm_capability_options['sold_by'] : 'no';
$vendor_email        = ( isset( $wcfm_capability_options['vendor_email'] ) ) ? $wcfm_capability_options['vendor_email'] : 'no';
$vendor_phone        = ( isset( $wcfm_capability_options['vendor_phone'] ) ) ? $wcfm_capability_options['vendor_phone'] : 'no';
$vendor_address      = ( isset( $wcfm_capability_options['vendor_address'] ) ) ? $wcfm_capability_options['vendor_address'] : 'no';
$vendor_map          = ( isset( $wcfm_capability_options['vendor_map'] ) ) ? $wcfm_capability_options['vendor_map'] : 'no';
$vendor_social       = ( isset( $wcfm_capability_options['vendor_social'] ) ) ? $wcfm_capability_options['vendor_social'] : 'no';
$vendor_follower     = ( isset( $wcfm_capability_options['vendor_follower'] ) ) ? $wcfm_capability_options['vendor_follower'] : 'no';
$vendor_policy       = ( isset( $wcfm_capability_options['vendor_policy'] ) ) ? $wcfm_capability_options['vendor_policy'] : 'no';
$store_hours         = ( isset( $wcfm_capability_options['store_hours'] ) ) ? $wcfm_capability_options['store_hours'] : 'no';
$customer_support    = ( isset( $wcfm_capability_options['customer_support'] ) ) ? $wcfm_capability_options['customer_support'] : 'no';
$refund_requests     = ( isset( $wcfm_capability_options['refund_requests'] ) ) ? $wcfm_capability_options['refund_requests'] : 'no';
$review_manage       = ( isset( $wcfm_capability_options['review_manage'] ) ) ? $wcfm_capability_options['review_manage'] : 'no';
$ledger_book         = ( isset( $wcfm_capability_options['ledger_book'] ) ) ? $wcfm_capability_options['ledger_book'] : 'no';
$video_banner        = ( isset( $wcfm_capability_options['video_banner'] ) ) ? $wcfm_capability_options['video_banner'] : 'no';
$slider_banner       = ( isset( $wcfm_capability_options['slider_banner'] ) ) ? $wcfm_capability_options['slider_banner'] : 'no';
$product_multivendor = ( isset( $wcfm_capability_options['product_multivendor'] ) ) ? $wcfm_capability_options['product_multivendor'] : 'no';

// Withdrawal
$vendor_withdrwal = ( !empty( $wcfm_capability_options['vendor_withdrwal'] ) ) ? $wcfm_capability_options['vendor_withdrwal'] : '';
$vendor_transactions = ( !empty( $wcfm_capability_options['vendor_transactions'] ) ) ? $wcfm_capability_options['vendor_transactions'] : '';
$vendor_transaction_details = ( !empty( $wcfm_capability_options['vendor_transaction_details'] ) ) ? $wcfm_capability_options['vendor_transaction_details'] : '';

// Miscellaneous Capabilities
$associate_listings         = ( isset( $wcfm_capability_options['associate_listings'] ) ) ? $wcfm_capability_options['associate_listings'] : 'no';
$wc_pdf_vouchers            = ( isset( $wcfm_capability_options['wc_pdf_vouchers'] ) ) ? $wcfm_capability_options['wc_pdf_vouchers'] : 'no';
$woocommerce_germanized     = ( isset( $wcfm_capability_options['woocommerce_germanized'] ) ) ? $wcfm_capability_options['woocommerce_germanized'] : 'no';
$wc_box_office              = ( isset( $wcfm_capability_options['wc_box_office'] ) ) ? $wcfm_capability_options['wc_box_office'] : 'no';
$wc_lottery                 = ( isset( $wcfm_capability_options['wc_lottery'] ) ) ? $wcfm_capability_options['wc_lottery'] : 'no';
$wc_deposits                = ( isset( $wcfm_capability_options['wc_deposits'] ) ) ? $wcfm_capability_options['wc_deposits'] : 'no';
$wc_tabs_manager            = ( isset( $wcfm_capability_options['wc_tabs_manager'] ) ) ? $wcfm_capability_options['wc_tabs_manager'] : 'no';
$wc_warranty                = ( isset( $wcfm_capability_options['wc_warranty'] ) ) ? $wcfm_capability_options['wc_warranty'] : 'no';
$wc_waitlist                = ( isset( $wcfm_capability_options['wc_waitlist'] ) ) ? $wcfm_capability_options['wc_waitlist'] : 'no';
$wc_fooevents               = ( isset( $wcfm_capability_options['wc_fooevents'] ) ) ? $wcfm_capability_options['wc_fooevents'] : 'no';
$wc_measurement             = ( isset( $wcfm_capability_options['wc_measurement'] ) ) ? $wcfm_capability_options['wc_measurement'] : 'no';
$wc_wholesale               = ( isset( $wcfm_capability_options['wc_wholesale'] ) ) ? $wcfm_capability_options['wc_wholesale'] : 'no';
$wc_min_max_quantities      = ( isset( $wcfm_capability_options['wc_min_max_quantities'] ) ) ? $wcfm_capability_options['wc_min_max_quantities'] : 'no';
$wc_360_images              = ( isset( $wcfm_capability_options['wc_360_images'] ) ) ? $wcfm_capability_options['wc_360_images'] : 'no';
$wc_product_badge           = ( isset( $wcfm_capability_options['wc_product_badge'] ) ) ? $wcfm_capability_options['wc_product_badge'] : 'no';
$wc_product_addon           = ( isset( $wcfm_capability_options['wc_product_addon'] ) ) ? $wcfm_capability_options['wc_product_addon'] : 'no';
$wc_product_scheduler       = ( isset( $wcfm_capability_options['wc_product_scheduler'] ) ) ? $wcfm_capability_options['wc_product_scheduler'] : 'no';
$wc_fancy_product_designer  = ( isset( $wcfm_capability_options['wc_fancy_product_designer'] ) ) ? $wcfm_capability_options['wc_fancy_product_designer'] : 'no';
$wc_advanced_product_labels = ( isset( $wcfm_capability_options['wc_advanced_product_labels'] ) ) ? $wcfm_capability_options['wc_advanced_product_labels'] : 'no';
$wc_variaton_swatch         = ( isset( $wcfm_capability_options['wc_variaton_swatch'] ) ) ? $wcfm_capability_options['wc_variaton_swatch'] : 'no';
$wc_quotation               = ( isset( $wcfm_capability_options['wc_quotation'] ) ) ? $wcfm_capability_options['wc_quotation'] : 'no';
$wc_dynamic_pricing         = ( isset( $wcfm_capability_options['wc_dynamic_pricing'] ) ) ? $wcfm_capability_options['wc_dynamic_pricing'] : 'no';
$wc_msrp_pricing            = ( isset( $wcfm_capability_options['wc_msrp_pricing'] ) ) ? $wcfm_capability_options['wc_msrp_pricing'] : 'no';
$wc_cost_of_goods           = ( isset( $wcfm_capability_options['wc_cost_of_goods'] ) ) ? $wcfm_capability_options['wc_cost_of_goods'] : 'no';
$wc_license_manager         = ( isset( $wcfm_capability_options['wc_license_manager'] ) ) ? $wcfm_capability_options['wc_license_manager'] : 'no';
$elex_rolebased_price       = ( isset( $wcfm_capability_options['elex_rolebased_price'] ) ) ? $wcfm_capability_options['elex_rolebased_price'] : 'no';
$pw_gift_cards              = ( isset( $wcfm_capability_options['pw_gift_cards'] ) ) ? $wcfm_capability_options['pw_gift_cards'] : 'no';

// Article Capabilities
$submit_articles = ( isset( $wcfm_capability_options['submit_articles'] ) ) ? $wcfm_capability_options['submit_articles'] : 'no';
$add_articles = ( isset( $wcfm_capability_options['add_articles'] ) ) ? $wcfm_capability_options['add_articles'] : 'no';
$publish_articles = ( isset( $wcfm_capability_options['publish_articles'] ) ) ? $wcfm_capability_options['publish_articles'] : 'no';
$edit_live_articles = ( isset( $wcfm_capability_options['edit_live_articles'] ) ) ? $wcfm_capability_options['edit_live_articles'] : 'no';
$publish_live_articles = ( isset( $wcfm_capability_options['publish_live_articles'] ) ) ? $wcfm_capability_options['publish_live_articles'] : 'no';
$delete_articles = ( isset( $wcfm_capability_options['delete_articles'] ) ) ? $wcfm_capability_options['delete_articles'] : 'no';

// Coupon Capabilities
$submit_coupons = ( isset( $wcfm_capability_options['submit_coupons'] ) ) ? $wcfm_capability_options['submit_coupons'] : 'no';
$add_coupons = ( isset( $wcfm_capability_options['add_coupons'] ) ) ? $wcfm_capability_options['add_coupons'] : 'no';
$publish_coupons = ( isset( $wcfm_capability_options['publish_coupons'] ) ) ? $wcfm_capability_options['publish_coupons'] : 'no';
$edit_live_coupons = ( isset( $wcfm_capability_options['edit_live_coupons'] ) ) ? $wcfm_capability_options['edit_live_coupons'] : 'no';
$publish_live_coupons = ( isset( $wcfm_capability_options['publish_live_coupons'] ) ) ? $wcfm_capability_options['publish_live_coupons'] : 'no';
$delete_coupons = ( isset( $wcfm_capability_options['delete_coupons'] ) ) ? $wcfm_capability_options['delete_coupons'] : 'no';
$free_shipping_coupons = ( isset( $wcfm_capability_options['free_shipping_coupons'] ) ) ? $wcfm_capability_options['free_shipping_coupons'] : 'no';

// Bookings Capabilities
$manage_booking = ( isset( $wcfm_capability_options['manage_booking'] ) ) ? $wcfm_capability_options['manage_booking'] : 'no';
$manual_booking = ( isset( $wcfm_capability_options['manual_booking'] ) ) ? $wcfm_capability_options['manual_booking'] : 'no';
$manage_resource = ( isset( $wcfm_capability_options['manage_resource'] ) ) ? $wcfm_capability_options['manage_resource'] : 'no';
$booking_list = ( isset( $wcfm_capability_options['booking_list'] ) ) ? $wcfm_capability_options['booking_list'] : 'no';
$booking_calendar = ( isset( $wcfm_capability_options['booking_calendar'] ) ) ? $wcfm_capability_options['booking_calendar'] : 'no';

// Appointment Capabilities
$manage_appointment = ( isset( $wcfm_capability_options['manage_appointment'] ) ) ? $wcfm_capability_options['manage_appointment'] : 'no';
$manual_appointment = ( isset( $wcfm_capability_options['manual_appointment'] ) ) ? $wcfm_capability_options['manual_appointment'] : 'no';
$manage_appointment_staff = ( isset( $wcfm_capability_options['manage_appointment_staff'] ) ) ? $wcfm_capability_options['manage_appointment_staff'] : 'no';
$appointment_list = ( isset( $wcfm_capability_options['appointment_list'] ) ) ? $wcfm_capability_options['appointment_list'] : 'no';
$appointment_calendar = ( isset( $wcfm_capability_options['appointment_calendar'] ) ) ? $wcfm_capability_options['appointment_calendar'] : 'no';

// Subscription Capabilities
$manage_subscription = ( isset( $wcfm_capability_options['manage_subscription'] ) ) ? $wcfm_capability_options['manage_subscription'] : 'no';
$subscription_list = ( isset( $wcfm_capability_options['subscription_list'] ) ) ? $wcfm_capability_options['subscription_list'] : 'no';
$subscription_details = ( isset( $wcfm_capability_options['subscription_details'] ) ) ? $wcfm_capability_options['subscription_details'] : 'no';
$subscription_status_update = ( isset( $wcfm_capability_options['subscription_status_update'] ) ) ? $wcfm_capability_options['subscription_status_update'] : 'no';
$subscription_schedule_update = ( isset( $wcfm_capability_options['subscription_schedule_update'] ) ) ? $wcfm_capability_options['subscription_schedule_update'] : 'no';

// Orders Capabilities
$view_orders  = ( isset( $wcfm_capability_options['view_orders'] ) ) ? $wcfm_capability_options['view_orders'] : 'no';
$order_status_update  = ( isset( $wcfm_capability_options['order_status_update'] ) ) ? $wcfm_capability_options['order_status_update'] : 'no';
$view_order_details = ( isset( $wcfm_capability_options['view_order_details'] ) ) ? $wcfm_capability_options['view_order_details'] : 'no';
$manage_order  = ( isset( $wcfm_capability_options['manage_order'] ) ) ? $wcfm_capability_options['manage_order'] : 'no';
$delete_order  = ( isset( $wcfm_capability_options['delete_order'] ) ) ? $wcfm_capability_options['delete_order'] : 'no';
$view_comments  = ( isset( $wcfm_capability_options['view_comments'] ) ) ? $wcfm_capability_options['view_comments'] : 'no';
$submit_comments  = ( isset( $wcfm_capability_options['submit_comments'] ) ) ? $wcfm_capability_options['submit_comments'] : 'no';
$export_csv  = ( isset( $wcfm_capability_options['export_csv'] ) ) ? $wcfm_capability_options['export_csv'] : 'no';
$view_commission  = ( isset( $wcfm_capability_options['view_commission'] ) ) ? $wcfm_capability_options['view_commission'] : 'no';

$store_invoice = ( isset( $wcfm_capability_options['store_invoice'] ) ) ? $wcfm_capability_options['store_invoice'] : 'no';
$pdf_invoice = ( isset( $wcfm_capability_options['pdf_invoice'] ) ) ? $wcfm_capability_options['pdf_invoice'] : 'no';
$pdf_packing_slip = ( isset( $wcfm_capability_options['pdf_packing_slip'] ) ) ? $wcfm_capability_options['pdf_packing_slip'] : 'no';

// Customer Capabilities
$manage_customers      = ( isset( $wcfm_capability_options['manage_customers'] ) ) ? $wcfm_capability_options['manage_customers'] : 'no';
$add_customers         = ( isset( $wcfm_capability_options['add_customers'] ) ) ? $wcfm_capability_options['add_customers'] : 'no';
$view_customers        = ( isset( $wcfm_capability_options['view_customers'] ) ) ? $wcfm_capability_options['view_customers'] : 'no';
$edit_customers        = ( isset( $wcfm_capability_options['edit_customers'] ) ) ? $wcfm_capability_options['edit_customers'] : 'no';
$delete_customers      = ( isset( $wcfm_capability_options['delete_customers'] ) ) ? $wcfm_capability_options['delete_customers'] : 'no';
$view_customers_orders = ( isset( $wcfm_capability_options['view_customers_orders'] ) ) ? $wcfm_capability_options['view_customers_orders'] : 'no';
$view_customers_name   = ( isset( $wcfm_capability_options['view_name'] ) ) ? $wcfm_capability_options['view_name'] : 'no';
$view_customers_email  = ( isset( $wcfm_capability_options['view_email'] ) ) ? $wcfm_capability_options['view_email'] : 'no';
$view_billing_details  = ( isset( $wcfm_capability_options['view_billing_details'] ) ) ? $wcfm_capability_options['view_billing_details'] : 'no';
$view_shipping_details =  ( isset( $wcfm_capability_options['view_shipping_details'] ) ) ? $wcfm_capability_options['view_shipping_details'] : 'no';
$customerlimit         = ( !empty( $wcfm_capability_options['customerlimit'] ) ) ? $wcfm_capability_options['customerlimit'] : '';

$view_reports  = ( isset( $wcfm_capability_options['view_reports'] ) ) ? $wcfm_capability_options['view_reports'] : 'no';

$vnd_wpadmin = ( isset( $wcfm_capability_options['vnd_wpadmin'] ) ) ? $wcfm_capability_options['vnd_wpadmin'] : 'no';

$is_marketplace = wcfm_is_marketplace();

?>

<div class="collapse wcfm-collapse" id="">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-times"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Capability Controller', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('Capability Settings', 'wc-frontend-manager' ); ?></h2>
			
			<?php 
			echo '<a id="wcfm_settings" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_settings_url().'" data-tip="' . __('Dashboard Settings', 'wc-frontend-manager') . '"><span class="wcfmfa fa-cogs"></span><span class="text">' . __( 'Settings', 'wc-frontend-manager') . '</span></a>';
			?>
			<div class="wcfm_clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'before_wcfm_capability' ); ?>
		
		<form id="wcfm_capability_form" class="wcfm">
	
			<?php do_action( 'begin_wcfm_capability_form' ); ?>
			
			<?php if( $is_marketplace ) { ?>
				<!-- collapsible -->
				<div class="page_collapsible" id="wcfm_capability_form_vendor_head">
					<label class="wcfmfa fa-user fa-user-alt"></label>
					<?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __('Vendors Capability', 'wc-frontend-manager'); ?>
					<span></span>
				</div>                                                                            
				<div class="wcfm-container">
					<div id="wcfm_settings_form_vendor_expander" class="wcfm-content">
						<div class="capability_head_message"><?php _e( "Configure what to hide from all Vendors", 'wc-frontend-manager' ); ?></div>
					
						<div class="vendor_capability">
							
							<div class="vendor_product_capability">
								<div class="vendor_capability_heading"><h3><?php _e( 'Products', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_products', array(
																																																													 "submit_products" => array('label' => __('Manage Products', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[submit_products]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_products),
																																																													 "add_products" => array('label' => __('Add Products', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[add_products]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $add_products),
																																																													 "publish_products" => array('label' => __('Publish Products', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_products]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_products),
																																																													 "edit_live_products" => array('label' => __('Edit Live Products', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[edit_live_products]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_live_products),
																																																													 "publish_live_products" => array('label' => __('Auto Publish Live Products', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_live_products]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_live_products),
																																																													 "delete_products" => array('label' => __('Delete Products', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_products]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_products)
																													) ) );
								?>
								
								<div class="wcfm_clearfix"></div>
								<div class="vendor_capability_sub_heading"><h3><?php _e( 'Types', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_product_types', array(
																																																																"simple" => array('label' => __('Simple', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[simple]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $simple),
																																																																"variable" => array('label' => __('Variable', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[variable]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $variable),
																																																																"grouped" => array('label' => __('Grouped', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[grouped]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $grouped),
																																																																"external" => array('label' => __('External / Affiliate', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[external]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $external),
																													), 'wcfm_capability_options', $wcfm_capability_options ) );
								?>
								
								<div class="wcfm_clearfix"></div>
								<div class="vendor_capability_sub_heading"><h3><?php _e( 'Panels', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_product_panels', array(
																																																																 "inventory" => array('label' => __('Inventory', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[inventory]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $inventory),
																																																																 "shipping" => array('label' => __('Shipping', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[shipping]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $shipping),
																																																																 "taxes" => array('label' => __('Taxes', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[taxes]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $taxes),
																																																																 "linked" => array('label' => __('Linked', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[linked]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $linked),
																																																																 "attributes" => array('label' => __('Attributes', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[attributes]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $attributes),
																																																																 "advanced" => array('label' => __('Advanced', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[advanced]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $advanced),
																																																																 "catalog" => array('label' => __('Catalog', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[catalog]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $catalog),
																													) ) );
								
								do_action( 'wcfm_capability_settings_product', $wcfm_capability_options );
								
								do_action( 'wcfm_capability_manager_left_panel', $wcfm_capability_options );
								?>
							</div>
							
							<div class="vendor_other_capability">
							
								<div class="vendor_capability_heading"><h3><?php _e( 'Access', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_access', array(  
																																						 "vnd_wpadmin" => array('label' => __('Backend Access', 'wc-frontend-manager') . ' (wp-admin)', 'name' => 'wcfm_capability_options[vnd_wpadmin]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vnd_wpadmin),
																															) ) );
								?>
								
								<?php if( $is_marketplace == 'wcfmmarketplace' ) { ?>
									<div class="wcfm_clearfix"></div>
									<div class="vendor_capability_sub_heading"><h3><?php _e( 'Marketplace', 'wc-frontend-manager' ); ?></h3></div>
									
									<?php
									 $wcfm_wcfmmarkerplace_capability_settings_fields = apply_filters( 'wcfm_capability_settings_fields_wcfmmarkerplace',
																																								array( 
																																											 "vendor_sold_by" => array('label' => __('Show Sold By', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[sold_by]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_sold_by),
																																											 "vendor_email" => array('label' => __('Show Email', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_email]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_email),
																																											 "vendor_phone" => array('label' => __('Show Phone', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_phone]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_phone),
																																											 "vendor_address" => array('label' => __('Show Address', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_address]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_address),
																																											 "vendor_map" => array('label' => __('Show Map', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_map]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_map),
																																											 "vendor_social" => array('label' => __('Show Social', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_social]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_social),
																																											 "vendor_follower" => array('label' => __('Show Follower', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_follower]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_follower),
																																											 "vendor_policy" => array('label' => __('Show Policy', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_policy]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_policy),
																																											 "store_hours"       => array('label' => __('Store Hours', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[store_hours]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $store_hours),
																																											 "customer_support" => array('label' => __('Customer Support', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[customer_support]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $customer_support),
																																											 "refund_requests"     => array('label' => __('Refund Requests', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[refund_requests]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $refund_requests),
																																											 "review_manage"       => array('label' => __('Reviews Manage', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[review_manage]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $review_manage),
																																											 "ledger_book"       => array('label' => __('Ledger Book', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[ledger_book]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $ledger_book),
																																											 "product_multivendor"       => array('label' => __('Product Multivendor', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[product_multivendor]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $product_multivendor),
																																											 "video_banner"       => array('label' => __('Video Banner', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[video_banner]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $video_banner),
																																											 "slider_banner"       => array('label' => __('Slider Banner', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[slider_banner]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $slider_banner),
																														) );
									
									if( !apply_filters( 'wcfm_is_pref_policies', true ) ) {
										if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['vendor_policy'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['vendor_policy'] );
										//if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['customer_support'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['customer_support'] );
									}
									
									if( !WCFM_Dependencies::wcfmu_plugin_active_check() || !apply_filters( 'wcfm_is_pref_vendor_followers', true ) ) {
										if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['vendor_follower'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['vendor_follower'] );
									}
									
									if( !apply_filters( 'wcfm_is_pref_ledger_book', true ) ) {
										if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['ledger_book'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['ledger_book'] );
									}
									
									if( !apply_filters( 'wcfm_is_pref_store_hours', true ) ) {
										if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['store_hours'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['store_hours'] );
									}
									
									if( !apply_filters( 'wcfm_is_pref_vendor_reviews', true ) ) {
										if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['review_manage'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['review_manage'] );
									}
									
									if( !apply_filters( 'wcfm_is_pref_refund', true ) ) {
										if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['refund_requests'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['refund_requests'] );
									}
									
									if( !apply_filters( 'wcfm_is_pref_product_multivendor', true ) ) {
										if( isset( $wcfm_wcfmmarkerplace_capability_settings_fields['product_multivendor'] ) ) unset( $wcfm_wcfmmarkerplace_capability_settings_fields['product_multivendor'] );
									}
									
									$WCFM->wcfm_fields->wcfm_generate_form_field( $wcfm_wcfmmarkerplace_capability_settings_fields );
									?>
									
								<?php } ?>
								
								<?php
								if( apply_filters( 'wcfm_is_pref_withdrawal', true ) ) {
								  if( $is_marketplace && in_array( $is_marketplace, array( 'dokan', 'wcmarketplace', 'wcfmmarketplace' ) ) ) {
								  	?>
								  	<div class="wcfm_clearfix"></div>
										<div class="vendor_capability_sub_heading"><h3><?php _e( 'Withdrawal', 'wc-frontend-manager' ); ?></h3></div>
										
										<?php
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_withdrwal', array(
																																		"vendor_withdrwal" => array('label' => __('Withdrawal Request', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_withdrwal]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_withdrwal),
																																		"vendor_transactions" => array('label' => __('Transactions', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_transactions]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_transactions),
																																		"vendor_transaction_details" => array('label' => __('Transaction Details', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[vendor_transaction_details]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $vendor_transaction_details),
																																 ) ) );
										
										?>
								  	<?php
								  }
								}	
								?>
								
								<div class="wcfm_clearfix"></div>
								<div class="vendor_capability_sub_heading"><h3><?php _e( 'Integrations', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								/*if( !wcfm_is_booking() ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_booking', array(  "manage_booking" => array('label' => __('Manage Bookings', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_booking]', 'type' => 'checkboxoffon', 'custom_tags' => array( 'disabled' => 'disabled' ), 'desc' => __( 'Install WC Bookings to enable this feature.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm-checkbox-disabled wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_booking),
																															) ) );
								}
								
								if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									if( !WCFMu_Dependencies::wcfm_wc_appointments_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_appointment', array(  "manage_appointment" => array('label' => __('Manage Appointments', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_appointment]', 'type' => 'checkboxoffon', 'custom_tags' => array( 'disabled' => 'disabled' ), 'desc' => __( 'Install WC Appointments to enable this feature.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm-checkbox-disabled wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_appointment),
																																) ) );
									}
								}
								
								if( !wcfm_is_subscription() && !wcfm_is_xa_subscription() ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_subscription', array(  "manage_subscription" => array('label' => __('Manage Subscriptions', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_subscription]', 'type' => 'checkboxoffon', 'custom_tags' => array( 'disabled' => 'disabled' ), 'desc' => __( 'Install WC Subscriptions to enable this feature.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm-checkbox-disabled wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_subscription),
																															) ) );
								}*/
								
								if( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_listings', array(  "associate_listings" => array('label' => __('Associate Listings', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[associate_listings]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'desc' => __( 'by WP Job Manager.', 'wc-frontend-manager' ), 'dfvalue' => $associate_listings),
																															) ) );
								}
								
								if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									if( WCFM_Dependencies::wcfm_wc_product_voucher_plugin_active_check() || WCFMu_Dependencies::wcfm_wc_pdf_voucher_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_pdf_vouchers', array(  "wc_pdf_vouchers" => array('label' => __('PDF Vouchers', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_pdf_vouchers]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'desc' => __( 'by WC PDF Vouchers.', 'wc-frontend-manager' ), 'dfvalue' => $wc_pdf_vouchers),
																																) ) );
									}
									
									if( WCFM_Dependencies::wcfm_woocommerce_germanized_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_woocommerce_germanized', array(  "woocommerce_germanized" => array('label' => __('WooCommerce Germanized', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[woocommerce_germanized]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Germanized.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $woocommerce_germanized),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_box_office_active_check' ) && WCFMu_Dependencies::wcfm_wc_box_office_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_box_office', array(  "wc_box_office" => array('label' => __('Box Office', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_box_office]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Box Office.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_box_office),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_lottery_active_check' ) && WCFMu_Dependencies::wcfm_wc_lottery_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_lottery', array(  "wc_lottery" => array('label' => __('Lottery', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_lottery]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Lottery.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_lottery),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_deposits_active_check' ) && WCFMu_Dependencies::wcfm_wc_deposits_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_deposits', array(  "wc_deposits" => array('label' => __('Deposits', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_deposits]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Deposits.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_deposits),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_tabs_manager_plugin_active_check' ) && WCFMu_Dependencies::wcfm_wc_tabs_manager_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_tabs_manager', array(  "wc_tabs_manager" => array('label' => __('Tabs Manager', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_tabs_manager]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Tabs Manager.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_tabs_manager),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_warranty_plugin_active_check' ) && WCFMu_Dependencies::wcfm_wc_warranty_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_warranty', array(  "wc_warranty" => array('label' => __('Warranty', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_warranty]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Warranty.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_warranty),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_waitlist_plugin_active_check' ) && WCFMu_Dependencies::wcfm_wc_waitlist_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_waitlist', array(  "wc_waitlist" => array('label' => __('Waitlist', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_waitlist]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Waitlist.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_waitlist),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_fooevents' ) && WCFMu_Dependencies::wcfm_wc_fooevents() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_fooevents', array(  "wc_fooevents" => array('label' => __('FooEvents', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_fooevents]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce FooEvents.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_fooevents),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_measurement_price_calculator' ) && WCFMu_Dependencies::wcfm_wc_measurement_price_calculator() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_measurement', array(  "wc_measurement" => array('label' => __('Measurement Calculator', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_measurement]', 'type' => 'checkboxoffon', 'desc' => __( 'by WC Measurement & Price Calculator.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_measurement),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_advanced_product_labels_active_check' ) && WCFMu_Dependencies::wcfm_wc_advanced_product_labels_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_advanced_product_labels', array(  "wc_advanced_product_labels" => array('label' => __('Product Labels', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_advanced_product_labels]', 'type' => 'checkboxoffon', 'desc' => __( 'by WC Advanced Product Labels.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_advanced_product_labels),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wholesale_active_check' ) && WCFMu_Dependencies::wcfm_wholesale_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wholesale', array(  "wc_wholesale" => array('label' => __('Wholesale Price', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_wholesale]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Wholesale Price.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_wholesale),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_min_max_quantities_active_check' ) && WCFMu_Dependencies::wcfm_wc_min_max_quantities_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_min_max_quantities', array(  "wc_min_max_quantities" => array('label' => __('Min/Max Quantities', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_min_max_quantities]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Min/Max Quantities.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_min_max_quantities),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_360_images_active_check' ) && ( WCFMu_Dependencies::wcfm_wc_360_images_active_check() || function_exists( 'woodmart_360_metabox_output' ) ) ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_360_images', array(  "wc_360_images" => array('label' => __('360° Images', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_360_images]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce 360° Images.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_360_images),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_product_badge_manager_active_check' ) && WCFMu_Dependencies::wcfm_wc_product_badge_manager_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_product_badge', array(  "wc_product_badge" => array('label' => __('Product Badge', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_product_badge]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Product Badge.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_product_badge),
																																) ) );
									}
									
									if( WCFMu_Dependencies::wcfm_wc_addons_active_check() || WCFMu_Dependencies::wcfm_wc_appointments_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_product_addon', array(  "wc_product_addon" => array('label' => __('Product Addon', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_product_addon]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Product Addon.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_product_addon),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_fancy_product_designer_active_check' ) && WCFMu_Dependencies::wcfm_wc_fancy_product_designer_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_fancy_product_designer', array(  "wc_fancy_product_designer" => array('label' => __('Fancy Product Designer', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_fancy_product_designer]', 'type' => 'checkboxoffon', 'desc' => __( 'by Fancy Product Designer.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_fancy_product_designer),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_variaton_swatch_active_check' ) && WCFMu_Dependencies::wcfm_wc_variaton_swatch_active_check() && WCFMu_Dependencies::wcfm_wc_variaton_swatch_pro_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_variaton_swatch', array(  "wc_variaton_swatch" => array('label' => __('Variation Swatch', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_variaton_swatch]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Variation Swatches.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_variaton_swatch),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_quotation_active_check' ) && WCFMu_Dependencies::wcfm_wc_quotation_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_quotation', array(  "wc_quotation" => array('label' => __('WooCommerce Quotation', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_quotation]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Quotation.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_quotation ),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_dynamic_pricing_active_check' ) && WCFMu_Dependencies::wcfm_wc_dynamic_pricing_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_dynamic_pricing', array(  "wc_dynamic_pricing" => array('label' => __('Dynamic Pricing', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_dynamic_pricing]', 'type' => 'checkboxoffon', 'desc' => __( 'by WooCommerce Dynamic Pricing.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_dynamic_pricing ),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_msrp_for_wc_plugin_active_check' ) && WCFMu_Dependencies::wcfm_msrp_for_wc_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_msrp_for_wc', array(  "wc_msrp_pricing" => array('label' => __('MSRP Pricing', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_msrp_pricing]', 'type' => 'checkboxoffon', 'desc' => __( 'by MSRP for WooCommerce.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_msrp_pricing ),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_cost_of_goods_plugin_active_check' ) && WCFMu_Dependencies::wcfm_wc_cost_of_goods_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_cost_of_goods', array(  "wc_cost_of_goods" => array('label' => __('Cost of Goods', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_cost_of_goods]', 'type' => 'checkboxoffon', 'desc' => __( 'by Cost of Goods for WooCommerce.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_cost_of_goods ),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_license_manager_plugin_active_check' ) && WCFMu_Dependencies::wcfm_wc_license_manager_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_license_manager', array(  "wc_license_manager" => array('label' => __('License Manager', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_license_manager]', 'type' => 'checkboxoffon', 'desc' => __( 'by Licence Manager for WooCommerce.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_license_manager ),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_elex_rolebased_price_plugin_active_check' ) && WCFMu_Dependencies::wcfm_elex_rolebased_price_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_elex_rolebased_price', array(  "elex_rolebased_price" => array('label' => __('Role based Price', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[elex_rolebased_price]', 'type' => 'checkboxoffon', 'desc' => __( 'by ELEX Role based Price.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $elex_rolebased_price ),
																																) ) );
									}
									
									if( method_exists( 'WCFMu_Dependencies', 'wcfm_wc_pw_gift_cards_plugin_active_check' ) && WCFMu_Dependencies::wcfm_wc_pw_gift_cards_plugin_active_check() ) {
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_pw_gift_cards', array(  "pw_gift_cards" => array('label' => __('Gift Cards', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[pw_gift_cards]', 'type' => 'checkboxoffon', 'desc' => __( 'by PW Gift Cards.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $pw_gift_cards ),
																																) ) );
									}
								}
								
								if( method_exists( 'WCFM_Dependencies', 'wcfm_wc_product_scheduler_active_check' ) && WCFM_Dependencies::wcfm_wc_product_scheduler_active_check() ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_wc_product_scheduler', array(  "wc_product_scheduler" => array('label' => __('Product Scheduler', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[wc_product_scheduler]', 'type' => 'checkboxoffon', 'desc' => __( 'by WC Product Scheduler.', 'wc-frontend-manager' ), 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_product_scheduler),
																															) ) );
								}
								?>
								
								<?php if( apply_filters( 'wcfm_is_pref_article', true ) ) { ?>
									<div class="vendor_capability_sub_heading"><h3><?php _e( 'Articles', 'wc-frontend-manager' ); ?></h3></div>
									
									<?php
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_articles', array("submit_articles" => array('label' => __('Manage Articles', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[submit_articles]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_articles),
																																																														 "add_articles" => array('label' => __('Add Articles', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[add_articles]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $add_articles),
																																																														 "publish_articles" => array('label' => __('Publish Articles', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_articles]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_articles),
																																																														 "edit_live_articles" => array('label' => __('Edit Live Articles', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[edit_live_articles]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_live_articles),
																																																														 "publish_live_articles" => array('label' => __('Auto Publish Live Articles', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_live_articles]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_live_articles),
																																																														 "delete_articles" => array('label' => __('Delete Articles', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_articles]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_articles)
																														) ) );
									?>
								<?php } ?>
								
								<div class="wcfm_clearfix"></div>
								
								<div class="wcfm_clearfix"></div>
								<div class="vendor_capability_sub_heading"><h3><?php _e( 'Coupons', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_coupons', array( "submit_coupons" => array('label' => __('Manage Coupons', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[submit_coupons]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_coupons),
																																																													 "add_coupons" => array('label' => __('Add Coupons', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[add_coupons]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $add_coupons),
																																																													 "publish_coupons" => array('label' => __('Publish Coupons', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_coupons]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_coupons),
																																																													 "edit_live_coupons" => array('label' => __('Edit Live Coupons', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[edit_live_coupons]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_live_coupons),
																																																													 "publish_live_coupons" => array('label' => __('Auto Publish Live Coupons', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_live_coupons]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_live_coupons),
																																																													 "delete_coupons" => array('label' => __('Delete Coupons', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_coupons]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_coupons),
																																																													 "free_shipping_coupons" => array('label' => __('Allow Free Shipping', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[free_shipping_coupons]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $free_shipping_coupons)
																													) ) );
								?>
								
								<?php
								if( ( wcfm_is_booking() || WCFM_Dependencies::wcfm_tych_booking_active_check() ) && WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									?>
									<div class="wcfm_clearfix"></div>
									<div class="vendor_capability_sub_heading"><h3><?php _e( 'Bookings', 'wc-frontend-manager' ); ?></h3></div>
									<?php
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_bookings', array(
																																																									 "manage_booking" => array('label' => __('Manage Bookings', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_booking]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_booking),
																																																									 "manual_booking" => array('label' => __('Manual Booking', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manual_booking]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manual_booking),
																																																									 "manage_resource" => array('label' => __('Manage Resource', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_resource]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_resource),
																																																									 "booking_list" => array('label' => __('Bookings List', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[booking_list]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $booking_list),
																																																									 "booking_calendar" => array('label' => __('Bookings Calendar', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[booking_calendar]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $booking_calendar),
																												) ) );
								}
								?>
								
								<?php
								if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									if( WCFMu_Dependencies::wcfm_wc_appointments_active_check() ) {
										?>
										<div class="wcfm_clearfix"></div>
										<div class="vendor_capability_sub_heading"><h3><?php _e( 'Appointments', 'wc-frontend-manager' ); ?></h3></div>
										<?php
										$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_appointment', array(
																																																									 "manage_appointment" => array('label' => __('Manage Appointments', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_appointment]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_appointment),
																																																									 "manual_appointment" => array('label' => __('Manual Appointment', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manual_appointment]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manual_appointment),
																																																									 "manage_appointment_staff" => array('label' => __('Manage Staff', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_appointment_staff]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_appointment_staff),
																																																									 "appointment_list" => array('label' => __('Appointments List', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[appointment_list]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $appointment_list),
																																																									 "appointment_calendar" => array('label' => __('Appointments Calendar', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[appointment_calendar]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $appointment_calendar),
																												) ) );
									}
								}
								?>
								
								<?php
								if( wcfm_is_subscription() && WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									?>
									<div class="wcfm_clearfix"></div>
									<div class="vendor_capability_sub_heading"><h3><?php _e( 'Subscriptions', 'wc-frontend-manager' ); ?></h3></div>
									<?php
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_bookings', array(
																																																									 "manage_subscription" => array('label' => __('Manage Subscriptions', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_subscription]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_subscription),
																																																									 "subscription_list" => array('label' => __('Subscriptions List', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[subscription_list]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $subscription_list),
																																																									 "subscription_details" => array('label' => __('Subscription Details', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[subscription_details]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $subscription_details),
																																																									 "subscription_status_update" => array('label' => __('Subscription Status Update', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[subscription_status_update]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $subscription_status_update),
																																																									 "subscription_schedule_update" => array('label' => __('Subscription Schedule Update', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[subscription_schedule_update]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $subscription_schedule_update),
																												) ) );
								}
								?>
								
								<div class="wcfm_clearfix"></div>
								<div class="vendor_capability_sub_heading"><h3><?php _e( 'Orders', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_orders', array(  
									                                                                                                         "view_orders" => array('label' => __('View Orders', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_orders]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_orders),
																																																													 "order_status_update" => array('label' => __('Status Update', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[order_status_update]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $order_status_update),
																																																													 "view_order_details" => array('label' => __('View Details', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_order_details]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_order_details),
																																																													 "manage_order" => array('label' => __('Add/Edit Order', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_order]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_order),
																																																													 "delete_order" => array('label' => __('Delete Order', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_order]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_order),
																																																													 "view_comments" => array('label' => __('View Comments', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_comments]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_comments),
																																																													 "submit_comments" => array('label' => __('Submit Comments', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[submit_comments]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_comments),
																																																													 "export_csv" => array('label' => __('Export CSV', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[export_csv]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $export_csv),
																																																													 "view_commission" => array('label' => __('View Commission', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_commission]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_commission),
																														 ) ) );
								?>
								
								<?php if( apply_filters( 'wcfm_is_pref_vendor_invoice', true ) && WCFM_Dependencies::wcfmu_plugin_active_check() && WCFM_Dependencies::wcfm_wc_pdf_invoices_packing_slips_plugin_active_check() ) { ?>
								  <div class="wcfm_clearfix"></div>
									<div class="vendor_capability_sub_heading"><h3><?php _e( 'PDF Invoice', 'wc-frontend-manager' ); ?></h3></div>
									
									<?php
									$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_invoice', array(  
																																							 "store_invoice" => array('label' => __('Store Invoice', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[store_invoice]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $store_invoice, 'hints' => __( 'Send out vendor store specific invoice to customer.', 'wc-frontend-manager-ultimate' ) ),
																																							 "pdf_invoice" => array('label' => __('Commission Invoice', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[pdf_invoice]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $pdf_invoice),
																																							 "pdf_packing_slip" => array('label' => __('PDF Packing Slip', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[pdf_packing_slip]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $pdf_packing_slip),
																																) ) );
									?>
								<?php } ?>
								
								<div class="wcfm_clearfix"></div>
								<div class="vendor_capability_sub_heading"><h3><?php _e( 'Customers', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_customers', array("manage_customers" => array('label' => __('Manage Customers', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[manage_customers]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $manage_customers),
																																																										 "add_customers" => array('label' => __('Add Customer', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[add_customers]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $add_customers),
																																																										 "view_customers" => array('label' => __('View Customer', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_customers]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_customers),
																																																										 "edit_customers" => array('label' => __('Edit Customer', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[edit_customers]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_customers),
																																																										 "delete_customers" => array('label' => __('Delete Customer', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_customers]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_customers),
																																																										 "view_customers_orders" => array('label' => __('View Customer Orders', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_customers_orders]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_customers_orders),
																																																										 "view_name" => array('label' => __('View Customer Name', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_name]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_customers_name),
																																																										 "view_email" => array('label' => __('View Customer Email', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_email]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_customers_email),
																																																										 "view_billing_details" => array('label' => __('Billing Address', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_billing_details]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_billing_details),
																																																										 "view_shipping_details" => array('label' => __('Shipping Address', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_shipping_details]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_shipping_details),
																													) ) );
								if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
									$WCFM->wcfm_fields->wcfm_generate_form_field(  array(
																																		"customerlimit" => array( 'label' => __('Customer Limit', 'wc-frontend-manager'), 'placeholder' => __('Unlimited', 'wc-frontend-manager-ultimate'), 'name' => 'wcfm_capability_options[customerlimit]','type' => 'number', 'class' => 'wcfm-text wcfm_ele gallerylimit_ele', 'label_class' => 'wcfm_title gallerylimit_title', 'value' => $customerlimit, 'hints' => __( 'No. of Customers allow to add by an user.', 'wc-frontend-manager-groups-staffs' ) . ' ' . __( 'Set `-1` if you want to restrict limit at `0`.', 'wc-frontend-manager-groups-staffs' ) )
																																		) );
								}																																														 
								?>
								
								<div class="wcfm_clearfix"></div>
								<div class="vendor_capability_sub_heading"><h3><?php _e( 'Reports', 'wc-frontend-manager' ); ?></h3></div>
								
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_reports', array("view_reports" => array('label' => __('View Reports', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[view_reports]', 'type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $view_reports),
																														 ) ) );
								
								do_action( 'wcfm_capability_settings_miscellaneous', $wcfm_capability_options );
								
								do_action( 'wcfm_capability_manager_right_panel', $wcfm_capability_options );
								?>
							</div>
						</div>
						
						<div class="vendor_advanced_capability">
							<?php
							if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) {
								if( $is_wcfmu_inactive_notice_show = apply_filters( 'is_wcfmu_inactive_notice_show', true ) ) {
									wcfmu_feature_help_text_show( __( 'Advanced Capability', 'wc-frontend-manager' ) );
								}
							} else {
								do_action( 'wcfm_settings_capability', $wcfm_capability_options );
							}
							?>
						</div>
					</div>
				</div>
				<div class="wcfm_clearfix"></div><br />
				<!-- end collapsible -->
			<?php } ?>
			
			
			<!-- collapsible -->
			<div class="page_collapsible" id="wcfm_capability_form_shop_manager_head">
				<label class="wcfmfa fa-user-secret"></label>
				<?php _e('Shop Managers Capability', 'wc-frontend-manager'); ?>
				<span></span>
			</div>                                                                            
			<div class="wcfm-container">
				<div id="wcfm_settings_form_shop_manager_expander" class="wcfm-content">
				  <?php
					if( WCFM_Dependencies::wcfmgs_plugin_active_check() ) {
						do_action( 'wcfm_shop_manager_capability_settings' );
					} else {
						if( $is_wcfmgs_inactive_notice_show = apply_filters( 'is_wcfmgs_inactive_notice_show', true ) ) {
							wcfmgs_feature_help_text_show( __( 'Shop Managers Capability', 'wc-frontend-manager' ) );
						}
					}
					?>
				</div>
			</div>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<!-- collapsible -->
			<div class="page_collapsible" id="wcfm_capability_form_shop_staff_head">
				<label class="wcfmfa fa-user"></label>
				<?php _e('Shop Staffs Capability', 'wc-frontend-manager'); ?>
				<span></span>
			</div>                                                                            
			<div class="wcfm-container">
				<div id="wcfm_settings_form_shop_staff_expander" class="wcfm-content">
				  <?php
					if( WCFM_Dependencies::wcfmgs_plugin_active_check() ) {
						do_action( 'wcfm_shop_staff_capability_settings' );
					} else {
						if( $is_wcfmgs_inactive_notice_show = apply_filters( 'is_wcfmgs_inactive_notice_show', true ) ) {
							wcfmgs_feature_help_text_show( __( 'Shop Staffs Capability', 'wc-frontend-manager' ) );
						}
					}
					?>
				</div>
			</div>
			<?php
			if( WCFM_Dependencies::wcfmgs_plugin_active_check() && $is_marketplace && ( $is_marketplace == 'wcpvendors' ) ) {
				?>
				<div style="color: #00897b;"><?php _e( '*** Vendor Managers are treated as Shop Staff for a Vendor Store.', 'wc-frontend-manager' ); ?></div>
				<?php
			}
			?>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<?php do_action( 'end_wcfm_capability', $wcfm_capability_options ); ?>
			
			<div id="wcfm_capability_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="save-data" value="<?php _e( 'Save', 'wc-frontend-manager' ); ?>" id="wcfm_capability_save_button" class="wcfm_submit_button" />
			</div>
			
			<?php do_action( 'end_wcfm_capability_form' ); ?>
		</form>	
		<?php
		do_action( 'after_wcfm_capability' );
		?>
	</div>
</div>