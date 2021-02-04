<?php
/**
 * WCFM plugin views
 *
 * Plugin Vendor Details Views
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/vendors
 * @version   3.4.1
 */
global $wp, $WCFM, $WCFMu;

$is_allow_vendors = apply_filters( 'wcfm_is_allow_vendors', true );
if( wcfm_is_vendor() || !$is_allow_vendors || !apply_filters( 'wcfm_is_allow_manage_vendor', true ) || !apply_filters( 'wcfm_is_allow_edit_vendor', true ) ) {
	wcfm_restriction_message_show( "Vendors" );
	return;
}

$vendor_id = 0;
$vendor_admin_id = 0;
$user_name = '&ndash;';
$user_email = '&ndash;';
$store_phone = '';
$store_address = '&ndash;';
$first_name = '&ndash;';
$last_name = '&ndash;';
$vendor_store = '&ndash;';
$vendor_paypal = '&ndash;';
$seller_info = '';

$logo = ( get_option( 'wcfm_site_logo' ) ) ? get_option( 'wcfm_site_logo' ) : '';
$logo_image_url = wp_get_attachment_image_src( $logo, 'thumbnail' );

if ( !empty( $logo_image_url ) ) {
	$logo_image_url = $logo_image_url[0];
} else {
	$logo_image_url = $WCFM->plugin_url . 'assets/images/your-logo-here.png';
}

$store_logo = $logo_image_url;

$has_custom_capability = 'no';

if( isset( $wp->query_vars['wcfm-vendors-manage'] ) && !empty( $wp->query_vars['wcfm-vendors-manage'] ) ) {
	$vendor_id = $wp->query_vars['wcfm-vendors-manage'];
	$vendor_id = absint($vendor_id);
	$vendor_admin_id = $vendor_id;
	
	if( $vendor_id ) {
		$store_phone = get_user_meta( $vendor_id, '_billing_phone', true );
		$vendor_store = wcfm_get_vendor_store( $vendor_id );
		$store_logo = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( $vendor_id );
		if( !$store_logo ) {
			$store_logo = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp.png' );
		}
		$marketplece = wcfm_is_marketplace();
  	if( $marketplece == 'wcvendors' ) {
  		$vendor_user = get_userdata( $vendor_id );
			$user_name = $vendor_user->user_login;
			$user_email = $vendor_user->user_email;
			$first_name = $vendor_user->first_name;
			$last_name = $vendor_user->last_name;
			$vendor_paypal = get_user_meta( $vendor_id, 'pv_paypal', true );
			$seller_info = get_user_meta( $vendor_id, 'pv_seller_info', true );
		
		} elseif( $marketplece == 'wcmarketplace' ) {
			$vendor_user = get_userdata( $vendor_id );
			$user_name = $vendor_user->user_login;
			$user_email = $vendor_user->user_email;
			$first_name = $vendor_user->first_name;
			$last_name = $vendor_user->last_name;
			$vendor_paypal = get_user_meta( $vendor_id, '_vendor_paypal_email', true );
			$seller_info = get_user_meta( $vendor_id, '_vendor_description', true );
		
		} elseif( $marketplece == 'wcpvendors' ) {
			$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
			
			if( is_array( $vendor_data['admins'] ) ) {
				$admin_ids = array_map( 'absint', $vendor_data['admins'] );
			} else {
				$admin_ids = array_filter( array_map( 'absint', explode( ',', $vendor_data['admins'] ) ) );
			}
			foreach( $admin_ids as $admin_id ) {
				if( $admin_id ) {
					if ( WC_Product_Vendors_Utils::is_admin_vendor( $admin_id ) ) {
						$vendor_admin_id = $admin_id;
						$vendor_user = get_userdata( $admin_id );
						$user_name = $vendor_user->user_login;
						$first_name = $vendor_user->first_name;
						$last_name = $vendor_user->last_name;
						break;
					}
				}
			}
			
			$user_email            = ! empty( $vendor_data['email'] ) ? $vendor_data['email'] : '';
			$vendor_paypal         = ! empty( $vendor_data['paypal'] ) ? $vendor_data['paypal'] : '';
			$seller_info           = ! empty( $vendor_data['profile'] ) ? $vendor_data['profile'] : '';
			
		} elseif( $marketplece == 'dokan' ) {
  		$vendor_user = get_userdata( $vendor_id );
  		$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
			$user_name = $vendor_user->user_login;
			$user_email = $vendor_user->user_email; //isset( $vendor_data['show_email'] ) ? esc_attr( $vendor_data['show_email'] ) : 'no';
			$first_name = $vendor_user->first_name;
			$last_name = $vendor_user->last_name;
		} elseif( $marketplece == 'wcfmmarketplace' ) {
  		$vendor_user   = get_userdata( $vendor_id );
  		//$vendor_data   = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
  		$store_user    = wcfmmp_get_store( $vendor_id );
			$user_name     = $vendor_user->user_login;
			$user_email    = $store_user->get_email();
			$first_name    = $vendor_user->first_name;
			$last_name     = $vendor_user->last_name;
			$store_phone   = $store_user->get_phone();
			$store_address = '<a style="color:#4dbd74;" href="https://maps.google.com/?q=' . rawurlencode( $store_user->get_address_string() ) . '&z=16" target="_blank">' . $store_user->get_address_string() . '</a>'; 
		}
		
		if( !$first_name ) $first_name = '&ndash;';
		if( !$last_name ) $last_name = '&ndash;';
		if( !$vendor_paypal ) $vendor_paypal = '&ndash;';
		if( !$store_phone ) $store_phone = '&ndash;';
		
		
		
		//$has_custom_capability = get_user_meta( $vendor_id, '_wcfm_user_has_custom_capability', true ) ? get_user_meta( $vendor_id, '_wcfm_user_has_custom_capability', true ) : 'no';

	}
}

if( !$vendor_id ) {
	wcfm_restriction_message_show( "No Vendor" );
	return;
}

if( !wcfm_is_vendor( $vendor_id ) ) {
	wcfm_restriction_message_show( "Invalid Vendor" );
	return;
}

$vendor_capability_options = (array) apply_filters( 'wcfmgs_user_capability', get_option( 'wcfm_capability_options' ), $vendor_id );

if( apply_filters( 'wcfm_is_allow_email_verification', true ) ) {
	$email_verified = false;
	if( $vendor_id ) {
		$email_verified = get_user_meta( $vendor_id, '_wcfm_email_verified', true );
		$wcfm_email_verified_for = get_user_meta( $vendor_id, '_wcfm_email_verified_for', true );
		if( $email_verified && ( $user_email != $wcfm_email_verified_for ) ) $email_verified = false;
	}
}

$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );

$stat_box_link = '';
if( $WCFM->is_marketplace && ( $WCFM->is_marketplace == 'wcfmmarketplace' ) && apply_filters( 'wcfm_is_allow_reports', true ) ) {
	$stat_box_link = get_wcfm_reports_url( '', 'wcfm-reports-sales-by-vendor', $vendor_id );
}

?>

<div class="collapse wcfm-collapse">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-user-alt"></span>
		<span class="wcfm-page-heading-text"><?php echo apply_filters( 'wcfm_manage_vendor_title', __( 'Manage', 'wc-frontend-manager' ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
	    <img class="vendor_store_logo" src="<?php echo $store_logo; ?>" alt="Store Logo" />
	    <h2>
	      <?php 
	        echo strip_tags( $vendor_store );
	      	if( $first_name ) echo "&nbsp;&ndash;&nbsp;" . $first_name;
	      	if( $last_name ) echo "&nbsp;" . $last_name;
	        if( apply_filters( 'wcfm_is_allow_email_verification', true ) ) {
						if( $email_verified ) {
							echo '&nbsp;<span class="wcfmfa fa-envelope wcfm_email_verified_icon text_tip" data-tip="' . __( 'Email Verified', 'wc-frontend-manager' ) . '" style="color: #008C00; margin-right: 5px;"></span>';
						} else {
							echo '&nbsp;<span class="wcfmfa fa-envelope-open wcfm_email_verified_icon text_tip" data-tip="' . __( 'Email Verification Pending', 'wc-frontend-manager' ) . '" style="color: #FF1A00; margin-right: 5px;"></span>';
						}
					}
	      ?>
	    </h2>
	    
	    <label class="wcfm_vendor_manage_change_vendor">
				<?php
				if( $wcfm_is_products_vendor_filter = apply_filters( 'wcfm_is_products_vendor_filter', true ) ) {
					$is_marketplace = wcfm_is_marketplace();
					if( $is_marketplace ) {
						if( !wcfm_is_vendor() ) {
							$vendor_arr = array();
							if( $vendor_id ) $vendor_arr[$vendor_id] = strip_tags( $vendor_store );
							$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																												"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 250px;' ), 'value' => $vendor_id )
																												 ) );
						}
					}
				}
				?>
			</label>
			
			<?php
			echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_vendors_url().'" data-tip="' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-alt"></span></a>';
			
			if( ($WCFM->is_marketplace == 'wcfmmarketplace' ) && apply_filters( 'wcfm_add_new_vendor_sub_menu', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_vendors_new_url().'" data-tip="' . __('Add New', 'wc-frontend-manager') . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
		<?php do_action( 'begin_wcfm_vendors_manage' ); ?>
		
		<?php if( apply_filters( 'wcfm_is_pref_stats_box', true ) ) { ?>
			<div class="wcfm_dashboard_stats">
				<div class="wcfm_dashboard_stats_block">
				  <a href="<?php echo $stat_box_link; ?>">
						<span class="wcfmfa fa-currency"><?php echo get_woocommerce_currency_symbol() ; ?></span>
						<div>
							<strong>
								<?php
								$gross_sales = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $vendor_id, 'month' );
								echo apply_filters( 'wcfm_vendors_gross_sales_data', wc_price( $gross_sales ), $vendor_id );
								?>
							</strong><br />
							<?php _e( 'gross sales in this month', 'wc-frontend-manager' ); ?>
						</div>
					</a>
				</div>
				
				<div class="wcfm_dashboard_stats_block">
				  <a href="<?php echo $stat_box_link; ?>">
						<span class="wcfmfa fa-money fa-money-bill-alt"></span>
						<div>
							<strong>
								<?php 
								$earned = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $vendor_id, 'month' );
								if( $admin_fee_mode ) {
									$earned = $gross_sales - $earned;
								}
								echo apply_filters( 'wcfm_vendors_earned_commission_data', wc_price( $earned ), $vendor_id, 'month' );
								?>
							</strong><br />
							<?php if( $admin_fee_mode ) { _e( 'admin fees in this month', 'wc-frontend-manager' ); } else { _e( 'earnings in this month', 'wc-frontend-manager' ); } ?>
						</div>
					</a>
				</div>
				
				<div class="wcfm_dashboard_stats_block">
					<a href="<?php echo get_wcfm_products_url( '', $vendor_id ); ?>">
						<span class="wcfmfa fa-cube"></span>
						<div>
							<?php 
							$products_list  = $WCFM->wcfm_vendor_support->wcfm_get_products_by_vendor( $vendor_id, apply_filters( 'wcfm_limit_check_status', 'any' ), array( 'suppress_filters' => 1 ) ); // wcfm_get_user_posts_count( $vendor_id, 'product', 'any' );
							$total_products = count( $products_list );
							$total_products = apply_filters( 'wcfm_vendors_total_products_data', $total_products, $vendor_id );
							printf( _n( "<strong>%s product</strong><br />", "<strong>%s products</strong><br />", $total_products, 'wc-frontend-manager' ), $total_products ); 
							?>
							<?php _e( 'total products posted', 'wc-frontend-manager' ); ?>
						</div>
					</a>
				</div>
				
				<div class="wcfm_dashboard_stats_block">
				  <a href="<?php echo get_wcfm_orders_url( '', $vendor_id ); ?>">
						<span class="wcfmfa fa-cart-plus"></span>
						<div>
							<?php 
							$total_item_sales = $WCFM->wcfm_vendor_support->wcfm_get_total_sell_by_vendor( $vendor_id, 'month' );
							$total_item_sales = apply_filters( 'wcfm_vendors_total_item_sales_data', $total_item_sales, $vendor_id, 'month' );
							printf( _n( "<strong>%s item</strong><br />", "<strong>%s items</strong><br />", $total_item_sales, 'wc-frontend-manager' ), $total_item_sales ); 
							?>
							<?php _e( 'sold in this month', 'wc-frontend-manager' ); ?>
						</div>
					</a>
				</div>
			</div>
			<div class="wcfm-clearfix"></div>
		<?php } ?>
			
		<div id="wcfm-vendor-manager-wrapper">
			
			<?php do_action( 'begin_wcfm_vendors_general_details', $vendor_admin_id, $vendor_id ); ?>
			
			<!-- collapsible -->
			<div class="wcfm-container">
				<div id="vendors_manage_general_expander" class="wcfm-content">
					<p class="store_name wcfm_ele wcfm_title"><strong><?php _e( 'Store', 'wc-frontend-manager' ); ?></strong></p>
					<span class="wcfm_vendor_store"><?php echo $vendor_store ?></span>
					<div class="wcfm_clearfix"></div>
					<?php
						do_action( 'before_wcfm_vendor_vendor_fields_general', $vendor_admin_id, $vendor_id );
					
						if( $vendor_id ) {
							$WCFM->wcfm_fields->wcfm_generate_form_field(  array( "user_name" => array( 'label' => __('Store Admin', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $first_name . ' ' . $last_name . ' (#' . $vendor_id . ' - ' . $user_name . ')' ) ) );
						} else {
							$WCFM->wcfm_fields->wcfm_generate_form_field(  array( "user_name" => array( 'label' => __('Store Admin', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $first_name . ' ' . $last_name . ' (#' . $vendor_id . ' - ' . $user_name . ')' ) ) );
						}
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendor_vendor_fields_general', array(  
																																					"user_email" => array( 'label' => __('Email', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $user_email),
																																					"store_phone" => array( 'label' => __('Phone', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $store_phone),
																																					"store_address" => array( 'label' => __('Address', 'wc-frontend-manager') , 'type' => 'html', 'attributes' => array( 'style' => 'display:inline-block;' ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $store_address),
																																					//"first_name" => array( 'label' => __('First Name', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $first_name),
																																					//"last_name" => array( 'label' => __('Last Name', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $last_name),
																																					//"paypal_email" => array( 'label' => __('PayPal Email', 'wc-frontend-manager') , 'type' => 'text', 'attributes' => array( 'readonly' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $vendor_paypal),
																																					"vendor_id" => array('type' => 'hidden', 'value' => $vendor_id )
																																			 ), $vendor_admin_id, $vendor_id ) );
						
						if( $seller_info ) {
						?>
							<p class="store_name wcfm_ele wcfm_title"><strong><?php _e( 'Seller Info', 'wc-frontend-manager' ); ?></strong></p>
							<span class="wcfm_vendor_store_info"><?php echo $seller_info ?></span>
							<div class="wcfm_clearfix"></div>
						<?php
						}
						
						do_action( 'after_wcfm_vendor_general_details', $vendor_admin_id, $vendor_id );
						
						$disable_vendor = get_user_meta( $vendor_id, '_disable_vendor', true );
						echo "<div class=\"wcfm_clearfix\"></div><br />";
						if( !$disable_vendor ) {
							
							if( apply_filters( 'wcfm_is_allow_disable_enable_vendor', true ) ) {
								echo '<a href="#" style="padding: 10px !important;" data-memberid="'.$vendor_id.'" id="wcfm_vendor_disable_button" class="wcfm_vendor_disable_button wcfm_submit_button">' . __( 'Disable Account', 'wc-frontend-manager' ) . '</a>';
							}
							
							if( $stat_box_link ) {
								echo '<a href="'.$stat_box_link.'" style="padding: 10px !important;" class="wcfm_submit_button"><span class="wcfmfa fa-chart-line"></span>&nbsp;' . __( 'Sales Report', 'wc-frontend-manager' ) . '</a>';
							}
						} else {
							if( apply_filters( 'wcfm_is_allow_disable_enable_vendor', true ) ) {
								echo '<a href="#" style="padding: 10px !important;" data-memberid="'.$vendor_id.'" id="wcfm_vendor_enable_button" class="wcfm_vendor_enable_button wcfm_submit_button">' . __( 'Enable Account', 'wc-frontend-manager' ) . '</a>';
							}
						}
						echo "<div class=\"wcfm_clearfix\"></div><br />";
					?>
				</div>
			</div>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<?php do_action( 'end_wcfm_vendors_general_details', $vendor_admin_id, $vendor_id ); ?>
			
			<?php do_action( 'begin_wcfm_vendors_manage_form', $vendor_admin_id, $vendor_id ); ?>
			
			
			<!-- collapsible -->
			<div class="page_collapsible vendor_manage_profile" id="wcfm_vendor_manage_form_profile_head"><label class="wcfmfa fa-user-alt"></label><?php _e( 'Profile', 'wc-frontend-manager' ); ?><span></span></div>
			<div class="wcfm-container">
				<div id="wcfm_vendor_manage_form_profile_expander" class="wcfm-content">
					<form id="wcfm_vendor_manage_profile_form" class="wcfm">
						<?php
						do_action( 'before_wcfm_vendors_manage_form', $vendor_admin_id, $vendor_id );
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_profile_field_vendor_manage', array(
																																																		"first_name" => array( 'label' => __('First Name', 'wc-frontend-manager') , 'type' => 'text', 'custom_attributes' => array( 'required' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $first_name),
																																																		"last_name" => array( 'label' => __('Last Name', 'wc-frontend-manager') , 'type' => 'text', 'custom_attributes' => array( 'required' => true ), 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $last_name),
																																																		"vendor_id" => array( 'type' => 'hidden', 'value' => $vendor_admin_id ),
																																																		), $vendor_admin_id, $vendor_id ) );
						
						do_action( 'after_wcfm_vendors_manage_form', $vendor_admin_id, $vendor_id );
						
						if( !WCFM_Dependencies::wcfmgs_plugin_active_check() ) {
							if( apply_filters( 'is_wcfmgs_inactive_notice_show', true ) ) {
								wcfmgs_feature_help_text_show( __( 'Vendors Capability', 'wc-frontend-manager' ) );
							}
						}
						?>
						<div class="wcfm-clearfix"></div>
						<div class="wcfm-message" tabindex="-1"></div>
						<div class="wcfm-clearfix"></div>
						<div id="wcfm_messages_submit">
							<input type="submit" name="save-data" value="<?php _e( 'Update', 'wc-frontend-manager' ); ?>" id="wcfm_profile_save_button" class="wcfm_submit_button" />
						</div>
						<div class="wcfm-clearfix"></div>
					</form>
				</div>
			</div>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<?php do_action( 'end_wcfm_vendors_manage_form', $vendor_admin_id, $vendor_id ); ?>
			
			<?php 
			$disable_vendor = get_user_meta( $vendor_id, '_disable_vendor', true );
			if( !$disable_vendor ) {
				?>
			
				<?php do_action( 'before_wcfm_vendor_membership_details', $vendor_admin_id, $vendor_id ); ?>
				
				<?php if( apply_filters( 'wcfm_is_pref_membership', true ) ) { ?>
					<!-- collapsible -->
					<div class="page_collapsible vendor_manage_membership" id="wcfm_vendor_manage_form_membership_head"><label class="wcfmfa fa-user-plus"></label><?php _e( 'Membership', 'wc-frontend-manager' ); ?><span></span></div>
					<div class="wcfm-container">
						<div id="wcfm_vendor_manage_form_membership_expander" class="wcfm-content">
							<?php 
							if( WCFM_Dependencies::wcfmvm_plugin_active_check() ) {
								do_action( 'wcfm_vendor_manage_membrship_details', $vendor_admin_id, $vendor_id );
							} else {
								echo "<h2>";
								_e( 'Vendor not yet subscribed for a membership!', 'wc-frontend-manager' );
								echo "</h2><div class=\"wcfm_clearfix\"></div><br />";
							}
							?>
						</div>
					</div>
					<div class="wcfm_clearfix"></div><br />
					<!-- end collapsible -->
				<?php } ?>
				
				<?php do_action( 'after_wcfm_vendor_membership_details', $vendor_admin_id, $vendor_id ); ?>
				
				<!-- collapsible -->
				<?php if( apply_filters( 'wcfm_is_allow_direct_message', true ) ) { ?>
					<div class="page_collapsible vendor_manage_message" id="wcfm_vendor_manage_form_message_head"><label class="fab fa-telegram-plane"></label>&nbsp;<?php _e( 'Send Message', 'wc-frontend-manager' ); ?><span></span></div>
					<div class="wcfm-container">
						<div id="wcfm_vendor_manage_form_message_expander" class="wcfm-content">
							<?php
							$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
							$wpeditor = apply_filters( 'wcfm_is_allow_profile_wpeditor', 'wpeditor' );
							if( $wpeditor && $rich_editor ) {
								$rich_editor = 'wcfm_wpeditor';
							} else {
								$wpeditor = 'textarea';
							}
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_messages_field_vendor_manage', array(
																																																			"wcfm_messages" => array( 'type' => $wpeditor, 'class' => 'wcfm-textarea wcfm_ele ' . $rich_editor, 'label_class' => 'wcfm_title' ),
																																																			"direct_to" => array( 'type' => 'hidden', 'value' => $vendor_id ),
																																																			) ) );
							?>
							<div id="wcfm_messages_submit">
								<input type="submit" name="save-data" value="<?php _e( 'Send', 'wc-frontend-manager' ); ?>" id="wcfm_messages_save_button" class="wcfm_submit_button" />
							</div>
							<div class="wcfm-clearfix"></div>
							<div class="wcfm-message" tabindex="-1"></div>
							<div class="wcfm-clearfix"></div>
						</div>
					</div>
					<div class="wcfm_clearfix"></div><br />
				<?php } ?>
				<!-- end collapsible -->
				
				<?php do_action( 'after_wcfm_vendor_direct_message_details', $vendor_admin_id, $vendor_id ); ?>
				
				<?php
				if( $stat_box_link ) {
					echo '<a href="'.$stat_box_link.'" style="padding: 10px !important;" class="wcfm_submit_button"><span class="wcfmfa fa-chart-line"></span>&nbsp;' . __( 'Sales Report', 'wc-frontend-manager' ) . '</a>';
				}
				?>
			<?php } ?>
			
		</div>
		
		<?php
		do_action( 'after_wcfm_vendors_manage' );
		?>
	</div>
</div>