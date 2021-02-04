<?php
/**
 * WCFM plugin views
 *
 * Plugin Shop Vendors New Views
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/vendors
 * @version   5.0.2
 */
global $wp, $WCFM, $WCFMu;

if( wcfm_is_vendor() || !apply_filters( 'wcfm_is_allow_manage_vendor', true ) || !apply_filters( 'wcfm_is_allow_edit_vendor', true ) ) {
	wcfm_restriction_message_show( "Vendors Manage" );
	return;
}

$vendor_id = 0;
$user_name = '';
$user_email = '';
$first_name = '';
$last_name = '';
$store_name = '';

$bfirst_name = '';
$blast_name  = '';
$bphone = '';
$baddr_1 = '';
$baddr_2 = '';
$bcountry = '';
$bcity = '';
$bstate = '';
$bzip = '';

$sfirst_name = ''; 
$slast_name = '';
$saddr_1 = '';
$saddr_2 = '';
$scountry = '';
$scity = '';
$sstate = '';
$szip = '';

if( isset( $wp->query_vars['wcfm-vendors-manage'] ) && empty( $wp->query_vars['wcfm-vendors-manage'] ) ) {
	if( !apply_filters( 'wcfm_is_allow_add_vendor', true ) ) {
		wcfm_restriction_message_show( "Add Vendor" );
		return;
	}
	if( !apply_filters( 'wcfm_is_allow_vendor_limit', true ) ) {
		wcfm_restriction_message_show( "Vendor Limit Reached" );
		return;
	}
}

do_action( 'before_wcfm_vendors_new' );

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
	    <h2><?php if( $vendor_id ) { echo apply_filters( 'wcfm_manage_vendor_heading', __('Edit', 'wc-frontend-manager' ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') ); } else { echo apply_filters( 'wcfm_manage_vendor_heading', __('Add', 'wc-frontend-manager' ) . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') ); } ?></h2>
			
			<?php
			echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_vendors_url().'" data-tip="' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendors', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-alt"></span></a>';
			
			if( $has_new = apply_filters( 'wcfm_add_new_vendor_sub_menu', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_vendors_new_url().'" data-tip="' . __('Add New', 'wc-frontend-manager') . ' ' . apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager') ) . ' ' . __( 'Vendor', 'wc-frontend-manager') . '"><span class="wcfmfa fa-user-plus"></span><span class="text">' . __( 'Add New', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
		<?php do_action( 'begin_wcfm_vendors_new' ); ?>
			
		<form id="wcfm_vendors_new_form" class="wcfm">
			
			<!-- collapsible -->
			<div class="wcfm-container">
				<div id="vendors_new_general_expander" class="wcfm-content">
						<?php
						  $WCFM->wcfm_fields->wcfm_generate_form_field(  array( "user_name" => array( 'label' => __('Username', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'custom_attributes' => array( 'required' => true ), 'value' => $user_name ) ) );
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendor_fields_general', array(  
																																						"user_email" => array( 'label' => __('Email', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'custom_attributes' => array( 'required' => true ), 'value' => $user_email),
																																						"first_name" => array( 'label' => __('First Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $first_name),
																																						"last_name" => array( 'label' => __('Last Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele ', 'label_class' => 'wcfm_ele wcfm_title', 'value' => $last_name),
																																				 ) ) );
						?>
				</div>
			</div>
			<div class="wcfm_clearfix"></div><br />
			<!-- end collapsible -->
			
			<div class="wcfm-tabWrap">
			
			  <?php do_action( 'begin_wcfm_vendors_new_form', 99999 ); ?>
			
				<div class="page_collapsible" id="wcfm_vendor_address_head">
					<label class="wcfmfa fa-address-card"></label>
					<?php _e('Address', 'wc-frontend-manager'); ?><span></span>
				</div>
				<div class="wcfm-container">
					<div id="wcfm_vendor_address_expander" class="wcfm-content">
						<?php if( apply_filters( 'wcfm_allow_vendor_billing_details', true ) ) { ?>
							<div class="wcfm_vendor_heading"><h3><?php _e( 'Billing', 'wc-frontend-manager' ); ?></h3></div>
							<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendor_fields_billing', array(
																																																	"bfirst_name" => array('label' => __('First Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $bfirst_name ),
																																																	"blast_name" => array('label' => __('Last Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $blast_name ),
																																																	"bphone" => array('label' => __('Phone', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $bphone ),
																																																	"baddr_1" => array('label' => __('Address 1', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $baddr_1 ),
																																																	"baddr_2" => array('label' => __('Address 2', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $baddr_2 ),
																																																	"bcountry" => array('label' => __('Country', 'wc-frontend-manager') , 'type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'style' => 'width: 60%;' ), 'value' => $bcountry ),
																																																	"bcity" => array('label' => __('City/Town', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $bcity ),
																																																	"bstate" => array('label' => __('State/County', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $bstate ),
																																																	"bzip" => array('label' => __('Postcode/Zip', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $bzip ),
																																																	) ) );
						}
						if( apply_filters( 'wcfm_allow_vendor_shipping_details', true ) ) {
						?>
						
						<div class="wcfm_clearfix"></div>
						<div class="wcfm_vendor_heading"><h3><?php _e( 'Shipping', 'wc-frontend-manager' ); ?></h3></div>
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendor_fields_shipping', array(
																																																"sfirst_name" => array('label' => __('First Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $sfirst_name ),
																																																"slast_name" => array('label' => __('Last Name', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $slast_name ),
																																																"saddr_1" => array('label' => __('Address 1', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $saddr_1 ),
																																																"saddr_2" => array('label' => __('Address 2', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $saddr_2 ),
																																																"scountry" => array('label' => __('Country', 'wc-frontend-manager') , 'type' => 'country', 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'style' => 'width: 60%;' ), 'value' => $scountry ),
																																																"scity" => array('label' => __('City/Town', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $scity ),
																																																"sstate" => array('label' => __('State/County', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $sstate ),
																																																"szip" => array('label' => __('Postcode/Zip', 'wc-frontend-manager') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $szip ),
																																																) ) );
							}
						?>
					</div>
				</div>
				<div class="wcfm_clearfix"></div>
				
				<?php do_action( 'end_wcfm_vendors_new_form', $vendor_id ); ?>
				
			</div>
			
			<div id="wcfm_vendor_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
				<input type="submit" name="submit-data" value="<?php _e( 'Submit', 'wc-frontend-manager' ); ?>" id="wcfm_vendor_submit_button" class="wcfm_submit_button" />
			</div>
			<?php
			do_action( 'after_wcfm_vendors_new' );
			?>
		</form>
	</div>
</div>