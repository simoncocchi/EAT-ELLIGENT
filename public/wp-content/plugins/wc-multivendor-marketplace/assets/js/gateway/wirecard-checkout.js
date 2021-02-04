jQuery(function($) {
    jQuery(document).on('click', '#place_order', function() {
        if (jQuery('input#payment_method_wirecard').prop('checked') !== !0) {
            return
        }
        var $card_number = $('#wirecard-card-number').val();
        var $card_cvc = $('#wirecard-card-cvc').val();
        var $card_expiry = $('#wirecard-card-expiry').val();
        var $card_expiry_month = $card_expiry.substr(0, 2);
        var $card_expiry_year = $card_expiry.substr(5);
        if (!MoipSdkJs.MoipValidator.isValidNumber($card_number)) {
            $('#wirecard-card-number').closest('p').before('<ul class="woocommerce_error woocommerce-error"><li>' + wcfmmp_wirecard_params.card_error + '</li></ul>');
            return !1
        }
        if (!MoipSdkJs.MoipValidator.isSecurityCodeValid($card_number, $card_cvc)) {
            $('#wirecard-card-cvc').closest('p').before('<ul class="woocommerce_error woocommerce-error"><li>' + wcfmmp_wirecard_params.cvc_error + '</li></ul>');
            return !1
        }
        if (!MoipSdkJs.MoipValidator.isExpiryDateValid($card_expiry_month, $card_expiry_year)) {
            $('#wirecard-card-expiry').closest('p').before('<ul class="woocommerce_error woocommerce-error"><li>' + wcfmmp_wirecard_params.expriy_error + '</li></ul>');
            return !1
        }
        var $form = jQuery("form.checkout, form#order_review");
        var hashed = $form.find('input.wirecard_hash');
        hashed.val('');
        MoipSdkJs.MoipCreditCard.setPubKey(wcfmmp_wirecard_params.public_key).setCreditCard({
            number: $card_number,
            cvc: $card_cvc,
            expirationMonth: $card_expiry_month,
            expirationYear: $card_expiry_year
        }).hash().then(function(hash) {
            $form.find('input.wirecard_hash').remove();
            $form.append("<input type='hidden' class='wirecard_hash' name='wirecard_hash' value='" + hash + "'/>");
            $form.submit()
        });
        return !1
    });
    /*jQuery(document).ready(function() {
        var $billing_cpf_span = $('#billing_cpf_field span.optional');
        var $billing_cpf = $('#billing_cpf_field');
        if ($('.payment_box.payment_method_wirecard').css('display') !== 'block') {
            $billing_cpf.hide()
        } else {
            $billing_cpf_span.html('<span style="color:red">*</span>')
        }
    });
    jQuery(document).on('click', '.wc_payment_methods.payment_methods li', function() {
        var $billing_cpf_span = $('#billing_cpf_field span.optional');
        var $billing_cpf = $('#billing_cpf_field');
        setTimeout(function() {
            if ($('.payment_box.payment_method_wirecard').css('display') == 'block') {
                $billing_cpf.show();
                $billing_cpf_span.html('<span style="color:red">*</span>')
            } else {
                $billing_cpf.hide()
            }
        }, 1000)
    })*/
})