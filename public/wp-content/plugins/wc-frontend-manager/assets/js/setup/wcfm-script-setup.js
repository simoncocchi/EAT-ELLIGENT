jQuery(document).ready(function($) {
	$('#vendor_sold_by_template').change(function() {
		$vendor_sold_by_template = $(this).val();
		$('.vendor_sold_by_type').addClass('wcfm_ele_hide');
		$('.vendor_sold_by_type_'+$vendor_sold_by_template).removeClass('wcfm_ele_hide');
	}).change();
	
	$('#vendor_commission_mode').change(function() {
		$vendor_commission_mode = $(this).val();
		$('.commission_mode_field').addClass('wcfm_ele_hide');
		$('.commission_mode_'+$vendor_commission_mode).removeClass('wcfm_ele_hide');
	}).change();
	
	$('#withdrawal_mode').change(function() {
		$withdrawal_mode = $(this).val();
		if( $withdrawal_mode == 'by_order_status' ) {
			$('.auto_withdrawal_order_status').removeClass('wcfm_custom_hide');
			$('.manual_withdrawal_ele').addClass('wcfm_custom_hide');
			$('.withdrawal_threshold_ele').addClass('wcfm_custom_hide');
			$('.schedule_withdrawal_threshold_ele').addClass('wcfm_custom_hide');
		} else if( $withdrawal_mode == 'by_manual' ) {
			$('.auto_withdrawal_order_status').addClass('wcfm_custom_hide');
			$('.schedule_withdrawal_threshold_ele').addClass('wcfm_custom_hide');
			$('.manual_withdrawal_ele').removeClass('wcfm_custom_hide');
			$('.withdrawal_threshold_ele').removeClass('wcfm_custom_hide');
		} else if( $withdrawal_mode == 'by_schedule' ) {
			$('.auto_withdrawal_order_status').addClass('wcfm_custom_hide');
			$('.manual_withdrawal_ele').removeClass('wcfm_custom_hide');
			$('.schedule_withdrawal_threshold_ele').removeClass('wcfm_custom_hide');
			$('.withdrawal_threshold_ele').removeClass('wcfm_custom_hide');
		}
	}).change();
	
	$('#withdrawal_reverse').change(function() {
		if( $(this).is(':checked') ) {
			$('.reverse_withdrawal_ele').removeClass('wcfm_custom_hide');
		} else {
			$('.reverse_withdrawal_ele').addClass('wcfm_custom_hide');
		}
	}).change();
	
	$('#withdrawal_payment_methods').find('.payment_options').each(function() {
		$(this).change(function() {
			$payment_option = $(this).val();
			if( $(this).is(':checked') ) {
				$('.withdrawal_mode_'+$payment_option).parent().removeClass('wcfm_ele_hide');
			} else {
				$('.withdrawal_mode_'+$payment_option).parent().addClass('wcfm_ele_hide');
			}
		}).change();
	});
	
	$('#withdrawal_test_mode').change(function() {
		if( $(this).is(':checked') ) {
			$('.withdrawal_mode_live').parent().addClass('wcfm_custom_hide');
			$('.withdrawal_mode_test').parent().removeClass('wcfm_custom_hide');
		} else {
			$('.withdrawal_mode_live').parent().removeClass('wcfm_custom_hide');
			$('.withdrawal_mode_test').parent().addClass('wcfm_custom_hide');
		}
	}).change();
	
	// Stripe 3D and SCA
	$('#withdrawal_stripe_is_3d_secure').click(function() {
		if( $(this).is(':checked') ) {
			$('.withdrawal_stripe_split_pay_mode_wrapper').addClass('wcfm_wpml_hide');
		} else {
			$('.withdrawal_stripe_split_pay_mode_wrapper').removeClass('wcfm_wpml_hide');
		}
	});
	if( $('#withdrawal_stripe_is_3d_secure').is(':checked') ) {
		$('.withdrawal_stripe_split_pay_mode_wrapper').addClass('wcfm_wpml_hide');
	} else {
		$('.withdrawal_stripe_split_pay_mode_wrapper').removeClass('wcfm_wpml_hide');
	}
	
	$('#withdrawal_charge_type').change(function() {
		$withdrawal_charge_type = $(this).val();
		if( $withdrawal_charge_type == 'no' ) {
			$('.withdraw_charge_block').addClass('wcfm_custom_hide');
		} else {
			$('.withdraw_charge_block').removeClass('wcfm_custom_hide');
			$('.withdraw_charge_field').addClass('wcfm_ele_hide');
			$('.withdraw_charge_'+$withdrawal_charge_type).removeClass('wcfm_ele_hide');
		}
	}).change();
	
	// Gateway specific charge option
	$('#withdrawal_payment_methods').find('.payment_options').each(function() {
		$(this).change(function() {
			$payment_option = $(this).val();
			if( $(this).is(':checked') ) {
				$('.withdraw_charge_'+$payment_option).removeClass('wcfm_ele_hide');
			} else {
				$('.withdraw_charge_'+$payment_option).addClass('wcfm_ele_hide');
			}
		}).change();
	});
	
	$('#vendor_withdrawal_mode').change(function() {
		$vendor_withdrawal_mode = $(this).val();
		$('.withdrawal_mode_field').addClass('wcfm_ele_hide');
		$('.withdrawal_mode_'+$vendor_withdrawal_mode).removeClass('wcfm_ele_hide');
		if( $vendor_withdrawal_mode != 'global' ) {
			$('#withdrawal_charge_type').change();
		}
	}).change();
});