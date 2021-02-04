<?php
/**
 * WCFMmp plugin core
 *
 * WCfMmp Store Hours
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.1.4
 */
class WCFMmp_Store_Hours {
	
	public function __construct() {
		global $WCFM, $WCFMmp;
		
		// Store Hours Default Settings
		add_action( 'end_wcfm_settings', array( &$this, 'wcfm_store_hours_global_settings' ), 17 );
		add_action( 'wcfm_settings_update', array( &$this, 'wcfm_store_hours_global_settings_update' ), 17 );
		
		if( wcfm_is_vendor() ) {
			add_action( 'end_wcfm_vendor_settings', array( &$this, 'wcfm_store_hours_vendor_settings' ), 5 );
		}
		
		// Store Hours Setting Update
		add_action( 'wcfm_vendor_settings_update', array( &$this, 'wcfm_store_hours_vendor_settings_update' ), 5, 2 );
		
		// Store Hours Checking
		add_filter( 'woocommerce_is_purchasable', array( &$this, 'wcfmmp_store_product_is_purchasable' ), 500, 2 );
		
		// Product Loop Add to Cart Disable by Store Hours
		add_action( 'woocommerce_after_shop_loop_item', array( &$this, 'wcfmmp_store_product_after_shop_loop_item' ), 9 );
		
		// Store Close Message Show
		add_action( 'woocommerce_single_product_summary', array( &$this, 'wcfmmp_store_close_message' ), 29 );
		
		// YiTH Quick View Store Close Message Show
		add_action( 'yith_wcqv_product_summary', array( &$this, 'wcfmmp_store_close_message' ), 30 );
		
		// Flatsome Quick View Store Close Message Show
		add_action( 'woocommerce_single_product_lightbox_summary', array( &$this, 'wcfmmp_store_close_message' ), 30 );
		
		// WooCommerce Quick View Pro Store Close Message Show
		add_action( 'wc_quick_view_pro_quick_view_product_details', array( &$this, 'wcfmmp_store_close_message' ), 30 );
		
		// Store Page Close Message
		add_action( 'wcfmmp_before_store_product', array( &$this, 'wcfmmp_store_close_message' ), 25 );
	}
	
	function wcfm_store_hours_global_settings( $wcfm_options ) {
		global $WCFM, $WCFMu;
		
		$wcfm_store_hours = get_option( 'wcfm_store_hours_options', array() );
		
		$wcfm_store_hours_off_days = isset( $wcfm_store_hours['off_days'] ) ? $wcfm_store_hours['off_days'] : array();
		$wcfm_store_hours_day_times = isset( $wcfm_store_hours['day_times'] ) ? $wcfm_store_hours['day_times'] : array();
		
		$wcfm_store_hours_mon_times = isset( $wcfm_store_hours_day_times[0] ) ? $wcfm_store_hours_day_times[0] : array();
		$wcfm_store_hours_tue_times = isset( $wcfm_store_hours_day_times[1] ) ? $wcfm_store_hours_day_times[1] : array();
		$wcfm_store_hours_wed_times = isset( $wcfm_store_hours_day_times[2] ) ? $wcfm_store_hours_day_times[2] : array();
		$wcfm_store_hours_thu_times = isset( $wcfm_store_hours_day_times[3] ) ? $wcfm_store_hours_day_times[3] : array();
		$wcfm_store_hours_fri_times = isset( $wcfm_store_hours_day_times[4] ) ? $wcfm_store_hours_day_times[4] : array();
		$wcfm_store_hours_sat_times = isset( $wcfm_store_hours_day_times[5] ) ? $wcfm_store_hours_day_times[5] : array();
		$wcfm_store_hours_sun_times = isset( $wcfm_store_hours_day_times[6] ) ? $wcfm_store_hours_day_times[6] : array();
		?>
		<!-- collapsible -->
		<div class="page_collapsible" id="wcfm_settings_form_store_hours_head">
			<label class="wcfmfa fa-clock fa-clock-o"></label>
			<?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ) . ' ' . __('Hours', 'wc-multivendor-marketplace'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_store_hours_expander" class="wcfm-content">
			  <div class="wcfm_clearfix"></div>
			  <h2><?php echo apply_filters( 'wcfm_sold_by_label', '', __( 'Store', 'wc-frontend-manager' ) ) . ' ' . __('Default Store Hours Setting', 'wc-multivendor-marketplace'); ?></h2>
				<div class="wcfm_clearfix"></div>
				<div class="store_address">
				  <?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_store_hours', array(
																																																								"wcfm_default_store_hours_off_days" => array( 'label' => __( 'Set Day OFF', 'wc-multivendor-marketplace'), 'type' => 'select', 'name' => 'wcfm_store_hours[off_days]', 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'options' => array( 0 => __( 'Monday', 'wc-multivendor-marketplace' ), 1 => __( 'Tuesday', 'wc-multivendor-marketplace' ), 2 => __( 'Wednesday', 'wc-multivendor-marketplace' ), 3 => __( 'Thursday', 'wc-multivendor-marketplace' ), 4 => __( 'Friday', 'wc-multivendor-marketplace' ), 5 => __( 'Saturday', 'wc-multivendor-marketplace' ), 6 => __( 'Sunday', 'wc-multivendor-marketplace') ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_store_hours_off_days ),
																																																							 ) ) );
					?>
				</div>
				
				<div class="wcfm_clearfix"></div><br />
			  <h2><?php _e( 'Daily Basis Opening & Closing Hours', 'wc-multivendor-marketplace' ); ?></h2>
				<div class="wcfm_clearfix"></div>
				<div class="store_address">
					<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_store_hours_time_slots', array( 
							"wcfm_store_hours_mon_times" => array( 'label' => __('Monday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][0]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_0', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_0', 'value' => $wcfm_store_hours_mon_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_tue_times" => array( 'label' => __('Tuesday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][1]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_1', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_1', 'value' => $wcfm_store_hours_tue_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_wed_times" => array( 'label' => __('Wednesday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][2]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_2', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_2', 'value' => $wcfm_store_hours_wed_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_thu_times" => array( 'label' => __('Thursday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][3]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_3', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_3', 'value' => $wcfm_store_hours_thu_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_fri_times" => array( 'label' => __('Friday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][4]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_4', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_4', 'value' => $wcfm_store_hours_fri_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_sat_times" => array( 'label' => __('Saturday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][5]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_5', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_5', 'value' => $wcfm_store_hours_sat_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_sun_times" => array( 'label' => __('Sunday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][6]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_6', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_6', 'value' => $wcfm_store_hours_sun_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
						) ) );
					?>
			  </div>
			</div>
		</div>
		<div class="wcfm_clearfix"></div>
		<!-- end collapsible -->
		
		<?php
		
	}
	
	function wcfm_store_hours_global_settings_update( $wcfm_settings_form ) {
		global $WCFM, $WCFMmp, $_POST;
		
		if( isset( $wcfm_settings_form['wcfm_store_hours'] ) ) {
			$wcfm_store_hours_options = $wcfm_settings_form['wcfm_store_hours'];
			update_option( 'wcfm_store_hours_options',  $wcfm_store_hours_options );
		}
	}
	
	function wcfm_store_hours_vendor_settings( $vendor_id ) {
		global $WCFM, $WCFMmp;
		
		if( !apply_filters( 'wcfm_is_allow_store_hours', true ) || !apply_filters( 'wcfm_is_allow_store_hours_settings', true ) ) return;
		
		// Global Setting
		$wcfm_store_hours = get_option( 'wcfm_store_hours_options', array() );
		
		$wcfm_global_store_hours_off_days  = isset( $wcfm_store_hours['off_days'] ) ? $wcfm_store_hours['off_days'] : array();
		$wcfm_global_store_hours_day_times = isset( $wcfm_store_hours['day_times'] ) ? $wcfm_store_hours['day_times'] : array();
		
		// Vendor wise Setting
		$wcfm_vendor_store_hours = (array) get_user_meta( $vendor_id, 'wcfm_vendor_store_hours', true );
		
		$wcfm_store_hours_enable = isset( $wcfm_vendor_store_hours['enable'] ) ? 'yes' : 'no';
		$wcfm_store_hours_disable_purchase = isset( $wcfm_vendor_store_hours['disable_purchase'] ) ? 'yes' : 'no';
		$wcfm_store_hours_off_days = isset( $wcfm_vendor_store_hours['off_days'] ) ? $wcfm_vendor_store_hours['off_days'] : $wcfm_global_store_hours_off_days;
		$wcfm_store_hours_day_times = isset( $wcfm_vendor_store_hours['day_times'] ) ? $wcfm_vendor_store_hours['day_times'] : $wcfm_global_store_hours_day_times;
		
		// Old Store Hours Migrating
		if( apply_filters( 'wcfmmp_is_allow_store_hours_old_data_migrate', false ) ) {
			$wcfm_vendor_store_hours_migrated = get_user_meta( $vendor_id, 'wcfm_vendor_store_hours_migrated', true );
			if( !empty( array_filter( $wcfm_vendor_store_hours ) ) && !$wcfm_vendor_store_hours_migrated ) {
				$wcfm_store_hours_mon_times = array( 0 => array( 'start' => isset( $wcfm_store_hours_day_times[0]['start'] ) ? $wcfm_store_hours_day_times[0]['start'] : '', 'end' => isset( $wcfm_store_hours_day_times[0]['end'] ) ? $wcfm_store_hours_day_times[0]['end'] : '' ) );
				$wcfm_store_hours_tue_times = array( 0 => array( 'start' => isset( $wcfm_store_hours_day_times[1]['start'] ) ? $wcfm_store_hours_day_times[1]['start'] : '', 'end' => isset( $wcfm_store_hours_day_times[1]['end'] ) ? $wcfm_store_hours_day_times[1]['end'] : '' ) );
				$wcfm_store_hours_wed_times = array( 0 => array( 'start' => isset( $wcfm_store_hours_day_times[2]['start'] ) ? $wcfm_store_hours_day_times[2]['start'] : '', 'end' => isset( $wcfm_store_hours_day_times[2]['end'] ) ? $wcfm_store_hours_day_times[2]['end'] : '' ) );
				$wcfm_store_hours_thu_times = array( 0 => array( 'start' => isset( $wcfm_store_hours_day_times[3]['start'] ) ? $wcfm_store_hours_day_times[3]['start'] : '', 'end' => isset( $wcfm_store_hours_day_times[3]['end'] ) ? $wcfm_store_hours_day_times[3]['end'] : '' ) );
				$wcfm_store_hours_fri_times = array( 0 => array( 'start' => isset( $wcfm_store_hours_day_times[4]['start'] ) ? $wcfm_store_hours_day_times[4]['start'] : '', 'end' => isset( $wcfm_store_hours_day_times[4]['end'] ) ? $wcfm_store_hours_day_times[4]['end'] : '' ) );
				$wcfm_store_hours_sat_times = array( 0 => array( 'start' => isset( $wcfm_store_hours_day_times[5]['start'] ) ? $wcfm_store_hours_day_times[5]['start'] : '', 'end' => isset( $wcfm_store_hours_day_times[5]['end'] ) ? $wcfm_store_hours_day_times[5]['end'] : '' ) );
				$wcfm_store_hours_sun_times = array( 0 => array( 'start' => isset( $wcfm_store_hours_day_times[6]['start'] ) ? $wcfm_store_hours_day_times[6]['start'] : '', 'end' => isset( $wcfm_store_hours_day_times[6]['end'] ) ? $wcfm_store_hours_day_times[6]['end'] : '' ) );
				
				$wcfm_store_hours_day_times = array( 0 => $wcfm_store_hours_mon_times,
																						 1 => $wcfm_store_hours_tue_times,
																						 2 => $wcfm_store_hours_wed_times,
																						 3 => $wcfm_store_hours_thu_times,
																						 4 => $wcfm_store_hours_fri_times,
																						 5 => $wcfm_store_hours_sat_times,
																						 6 => $wcfm_store_hours_sun_times
																						);
				
				$wcfm_vendor_store_hours['day_times'] = $wcfm_store_hours_day_times;
				update_user_meta( $vendor_id, 'wcfm_vendor_store_hours', $wcfm_vendor_store_hours );
				update_user_meta( $vendor_id, 'wcfm_vendor_store_hours_migrated', 'yes' );
			} else {
				update_user_meta( $vendor_id, 'wcfm_vendor_store_hours_migrated', 'yes' );
			}
		}
		
		$wcfm_store_hours_mon_times = isset( $wcfm_store_hours_day_times[0] ) ? $wcfm_store_hours_day_times[0] : array();
		$wcfm_store_hours_tue_times = isset( $wcfm_store_hours_day_times[1] ) ? $wcfm_store_hours_day_times[1] : array();
		$wcfm_store_hours_wed_times = isset( $wcfm_store_hours_day_times[2] ) ? $wcfm_store_hours_day_times[2] : array();
		$wcfm_store_hours_thu_times = isset( $wcfm_store_hours_day_times[3] ) ? $wcfm_store_hours_day_times[3] : array();
		$wcfm_store_hours_fri_times = isset( $wcfm_store_hours_day_times[4] ) ? $wcfm_store_hours_day_times[4] : array();
		$wcfm_store_hours_sat_times = isset( $wcfm_store_hours_day_times[5] ) ? $wcfm_store_hours_day_times[5] : array();
		$wcfm_store_hours_sun_times = isset( $wcfm_store_hours_day_times[6] ) ? $wcfm_store_hours_day_times[6] : array();
				
		?>
		<!-- collapsible -->
		<div class="page_collapsible" id="wcfm_settings_form_store_hours_head">
			<label class="wcfmfa fa-clock fa-clock-o"></label>
			<?php _e('Store Hours', 'wc-multivendor-marketplace'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_store_hours_expander" class="wcfm-content">
			  <div class="wcfm_clearfix"></div>
			  <h2><?php _e('Store Hours Setting', 'wc-multivendor-marketplace'); ?></h2>
				<div class="wcfm_clearfix"></div>
				<div class="store_address">
				
					<?php
						$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_store_hours', array(
																																																											"wcfm_store_hours" => array( 'label' => __( 'Enable Store Hours', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[enable]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $wcfm_store_hours_enable ),
																																																											"wcfm_disable_purchase_off_time" => array( 'label' => __('Disable Purchase During OFF Time', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[disable_purchase]', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => 'yes', 'dfvalue' => $wcfm_store_hours_disable_purchase ),
																																																											"wcfm_store_hours_off_days" => array( 'label' => __( 'Set Week OFF', 'wc-multivendor-marketplace'), 'type' => 'select', 'name' => 'wcfm_store_hours[off_days]', 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'options' => array( 0 => __( 'Monday', 'wc-multivendor-marketplace' ), 1 => __( 'Tuesday', 'wc-multivendor-marketplace' ), 2 => __( 'Wednesday', 'wc-multivendor-marketplace' ), 3 => __( 'Thursday', 'wc-multivendor-marketplace' ), 4 => __( 'Friday', 'wc-multivendor-marketplace' ), 5 => __( 'Saturday', 'wc-multivendor-marketplace' ), 6 => __( 'Sunday', 'wc-multivendor-marketplace') ), 'class' => 'wcfm-select wcfm_ele', 'label_class' => 'wcfm_title', 'value' => $wcfm_store_hours_off_days ),
																																																										 ), $vendor_id ) );
					?>
				</div>
				
				<div class="wcfm_clearfix"></div><br />
			  <h2><?php _e( 'Daily Basis Opening & Closing Hours', 'wc-multivendor-marketplace' ); ?></h2>
				<div class="wcfm_clearfix"></div>
				<div class="store_address">
					<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_vendors_settings_fields_store_hours_time_slots', array( 
							"wcfm_store_hours_mon_times" => array( 'label' => __('Monday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][0]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_0', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_0', 'value' => $wcfm_store_hours_mon_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_tue_times" => array( 'label' => __('Tuesday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][1]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_1', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_1', 'value' => $wcfm_store_hours_tue_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_wed_times" => array( 'label' => __('Wednesday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][2]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_2', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_2', 'value' => $wcfm_store_hours_wed_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_thu_times" => array( 'label' => __('Thursday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][3]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_3', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_3', 'value' => $wcfm_store_hours_thu_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_fri_times" => array( 'label' => __('Friday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][4]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_4', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_4', 'value' => $wcfm_store_hours_fri_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_sat_times" => array( 'label' => __('Saturday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][5]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_5', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_5', 'value' => $wcfm_store_hours_sat_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
							
							"wcfm_store_hours_sun_times" => array( 'label' => __('Sunday Time Slots', 'wc-multivendor-marketplace'), 'name' => 'wcfm_store_hours[day_times][6]', 'type' => 'multiinput', 'class' => 'wcfm_store_hours_fields wcfm_store_hours_fields_6', 'label_class' => 'wcfm_title wcfm_store_hours_fields wcfm_store_hours_fields_6', 'value' => $wcfm_store_hours_sun_times, 'options' => array(
								"start" => array( 'label' => __('Opening', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
								"end" => array( 'label' => __('Closing', 'wc-multivendor-marketplace'), 'type' => 'time', 'class' => 'wcfm-text wcfm_store_hours_field', 'label_class' => 'wcfm_title wcfm_store_hours_label' ),
							) ),
						), $vendor_id ) );
					?>
				</div>
		  </div>
		</div>
		<?php
	}
	
	function wcfm_store_hours_vendor_settings_update( $vendor_id, $wcfm_settings_form ) {
		global $WCFM, $WCFMmp, $_POST;
		
		if( !apply_filters( 'wcfm_is_allow_store_hours', true ) || !apply_filters( 'wcfm_is_allow_store_hours_settings', true ) ) return;
		
		if( isset( $wcfm_settings_form['wcfm_store_hours'] ) ) {
			update_user_meta( $vendor_id, 'wcfm_vendor_store_hours', $wcfm_settings_form['wcfm_store_hours'] );
			update_user_meta( $vendor_id, 'wcfm_vendor_store_hours_migrated', 'yes' );
		}
	}
	
	/**
	 * Restrict Store Product Purchase at OFF Time
	 */
	function wcfmmp_store_product_is_purchasable( $is_purchasable, $product ) {
		global $WCFM, $WCFMmp;
		
		if( method_exists( $product, 'get_id' ) ) {
			$product_id = $product->get_id();
			if( $product_id ) {
				$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
				
				if( $vendor_id ) {
					$is_store_close = $this->wcfmmp_is_store_close( $vendor_id );
					if( $is_store_close ) $is_purchasable = false;
				}
			}
		}
		
		return $is_purchasable;
	}
	
	/**
	 * Product Loop Add to Cart button Disable
	 */
	function wcfmmp_store_product_after_shop_loop_item() {
		global $WCFM, $WCFMmp, $product;
		
		if( method_exists( $product, 'get_id' ) ) {
			$product_id = $product->get_id();
			if( $product_id ) {
				$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
				
				$is_store_close = $this->wcfmmp_is_store_close( $vendor_id );
				if( $is_store_close ) {
					$WCFMmp->wcfm_is_store_close = true;
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
					
					if( !wcfm_is_store_page() && apply_filters( 'wcfm_is_allow_product_loop_store_close_message', false ) ) {
						echo '<div class="wcfm_store_close_msg">';
						echo apply_filters( 'wcfm_store_close_message', __( 'This store is now closed!', 'wc-multivendor-marketplace' ) );
						echo '</div>';
					}
				} elseif( !has_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart') && $WCFMmp->wcfm_is_store_close ) {
					if ( apply_filters( 'wcfm_is_allow_add_to_cart_restore', true ) && !function_exists( 'rehub_option' ) && !function_exists( 'astra_header' ) && !function_exists( 'zita_post_loader' ) && !function_exists( 'oceanwp_get_sidebar' ) && !function_exists( 'martfury_content_columns' ) && !function_exists( 'x_get_stack' ) ) {
						add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
					}
				}
			}
		}
	}
	
	/**
	 * WCFM Marketplace Store Close Message
	 */
	function wcfmmp_store_close_message() {
		global $WCFM, $WCFMmp, $product;
		
		if( !apply_filters( 'wcfm_is_allow_store_close_message', true ) ) return;
		
		$vendor_id = '';
		if( wcfm_is_store_page() ) {
			$custom_store_url = wcfm_get_option( 'wcfm_store_url', 'store' );
			$store_name = get_query_var( $custom_store_url );
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			if( $store_user ) {
				$vendor_id  = $store_user->ID;
			}
		} elseif( $product && method_exists( $product, 'get_id' ) ) {
			$product_id = $product->get_id();
			if( $product_id ) {
				$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			}
		}
		
		if( $vendor_id ) {
			$is_store_close = $this->wcfmmp_is_store_close( $vendor_id );
			if( $is_store_close ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				
				// YiTH Quick View Support
				remove_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_add_to_cart', 25 );
				
				// Flatsome Quick View Support
				remove_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_add_to_cart', 30 );
				
				// WooCommerce Quick View Pro Support
				remove_action( 'wc_quick_view_pro_quick_view_product_details', 'woocommerce_template_single_add_to_cart', 30 );
				
				echo '<div class="wcfm_store_close_msg">';
				echo apply_filters( 'wcfm_store_close_message', __( 'This store is now closed!', 'wc-multivendor-marketplace' ) );
				echo '</div>';
			}
		}
	}
	
	/**
	 * Check is Store CLose Now
	 */
	function wcfmmp_is_store_close( $vendor_id ) {
		global $WCFM, $WCFMmp;
		
		$is_store_close = false;
		
		if( !$WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'store_hours' ) ) return $is_store_close;
		
		if( $vendor_id ) {
			$wcfm_vendor_store_hours = get_user_meta( $vendor_id, 'wcfm_vendor_store_hours', true );
			if( !empty( $wcfm_vendor_store_hours ) ) {
				$wcfm_store_hours_enable = isset( $wcfm_vendor_store_hours['enable'] ) ? 'yes' : 'no';
				if( $wcfm_store_hours_enable == 'yes' ) {
					$wcfm_store_hours_disable_purchase = isset( $wcfm_vendor_store_hours['disable_purchase'] ) ? 'yes' : 'no';
					if( $wcfm_store_hours_disable_purchase == 'yes' ) {
						$wcfm_store_hours_off_days = isset( $wcfm_vendor_store_hours['off_days'] ) ? $wcfm_vendor_store_hours['off_days'] : array();
						$wcfm_store_hours_day_times = isset( $wcfm_vendor_store_hours['day_times'] ) ? $wcfm_vendor_store_hours['day_times'] : array();
						
						$current_time = current_time( 'timestamp' );
						
						$today = date( 'N', $current_time );
						$today -= 1;
						
						$today_date = date( 'Y-m-d', $current_time );
						
						// OFF Day Check
						if( !empty( $wcfm_store_hours_off_days ) ) {
							if( in_array( $today,  $wcfm_store_hours_off_days ) )  $is_store_close = true;
						}
						
						// Closing Hours Check
						if( !$is_store_close && !empty( $wcfm_store_hours_day_times ) ) {
							if( isset( $wcfm_store_hours_day_times[$today] ) ) {
								$wcfm_store_hours_day_time_slots = $wcfm_store_hours_day_times[$today];
								if( !empty( $wcfm_store_hours_day_time_slots ) ) {
									if( isset( $wcfm_store_hours_day_time_slots[0] ) && isset( $wcfm_store_hours_day_time_slots[0]['start'] ) ) {
										if( !empty( $wcfm_store_hours_day_time_slots[0]['start'] ) && !empty( $wcfm_store_hours_day_time_slots[0]['end'] ) ) {
											$is_store_close = true;
											foreach( $wcfm_store_hours_day_time_slots as $slot => $wcfm_store_hours_day_time_slot ) {
												$open_hours  = isset( $wcfm_store_hours_day_time_slot['start'] ) ? strtotime( $today_date . ' ' . $wcfm_store_hours_day_time_slot['start'] ) : '';
												$close_hours = isset( $wcfm_store_hours_day_time_slot['end'] ) ? strtotime( $today_date . ' ' . $wcfm_store_hours_day_time_slot['end'] ) : '';
												//wcfm_log( $current_time . " => " . $open_hours . " ::" . $close_hours );
												//wcfm_log( date( wc_date_format() . ' ' . wc_time_format(), $current_time ) . " => " . date( wc_date_format() . ' ' . wc_time_format(), $open_hours ) . " ::" . date( wc_date_format() . ' ' . wc_time_format(), $close_hours ) );
												if( $open_hours && $close_hours ) {
													if( ( $current_time > $open_hours ) && ( $current_time < $close_hours ) )  {
														$is_store_close = false;
														break;
													}
												} else {
													$is_store_close = false;
													break;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		return apply_filters( 'wcfmmp_is_store_close', $is_store_close, $vendor_id );
	}
	
}