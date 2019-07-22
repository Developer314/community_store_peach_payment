<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<script src="<?= str_replace('/index.php/', '/', URL::to('packages/community_store_peach_payment/js/jquery.payment.min.js')); ?>"></script>
<script>

    function an_responseHandler(response) {
        if (response.messages.resultCode === 'Error') {
            an_handleError(response);
        } else {
            an_handleSuccess(response.opaqueData)
        }
    }

    function an_handleSuccess(responseData) {
        var form = $('#store-checkout-form-group-payment');

        $('<input>')
            .attr({type: 'hidden', name: 'dataValue'})
            .val(responseData.dataValue)
            .appendTo(form);

        $('<input>')
            .attr({type: 'hidden', name: 'dataDesc'})
            .val(responseData.dataDescriptor)
            .appendTo(form);

        // Resubmit the form to the server
        //
        // Only the card_token will be submitted to your server. The
        // browser ignores the original form inputs because they don't
        // have their 'name' attribute set.
        form.get(0).submit();
    }

    function an_handleError(response) {

        var form = $('#store-checkout-form-group-payment'),
            submitButton = form.find("[data-payment-method-id=\"<?= $pmID; ?>\"] .store-btn-complete-order"),
            errorContainer = form.find('.an-payment-errors');

        for (var i = 0; i < response.messages.message.length; i++) {
            $('<p class="alert alert-danger">').text(response.messages.message[i].text).appendTo(errorContainer);
        }

        errorContainer.show();

        // Re-enable the submit button
        submitButton.removeAttr('disabled');
        submitButton.val('<?= t('Complete Order'); ?>');
    }

    // 1. Wait for the page to load
    $(window).on('load', function () {

        $('#ppoo-cc-number').payment('formatCardNumber');
        $('#ppoo-cc-exp').payment('formatCardExpiry');
        $('#ppoo-cc-cvc').payment('formatCardCVC');

        $('#ppoo-cc-number').bind("keyup change", function (e) {
            var validcard = $.payment.validateCardNumber($(this).val());

            if (validcard) {
                $(this).closest('.form-group').removeClass('has-error');
            }
        });

        $('#ppoo-cc-exp').bind("keyup change", function (e) {
            var validcard = $.payment.validateCardNumber($(this).val());

            var expiry = $(this).payment('cardExpiryVal');
            var validexpiry = $.payment.validateCardExpiry(expiry.month, expiry.year);

            if (validexpiry) {
                $(this).closest('.form-group').removeClass('has-error');
            }
        });

        $('#ppoo-cc-cvc').bind("keyup change", function (e) {
            var validcv = $.payment.validateCardCVC($(this).val());

            if (validcv) {
                $('#ppoo-cc-cvc').closest('.form-group').removeClass('has-error');
            }
        });


        var form = $('#store-checkout-form-group-payment'),
            submitButton = form.find("[data-payment-method-id=\"<?= $pmID; ?>\"] .store-btn-complete-order"),
            errorContainer = form.find('.an-payment-errors');

        // 3. Add a submit handler
        form.submit(function (e) {
            var currentpmid = $('input[name="payment-method"]:checked:first').data('payment-method-id');

            if (currentpmid == <?= $pmID; ?>) {
                var allvalid = true;

                var validcard = $.payment.validateCardNumber($('#ppoo-cc-number').val());

                if (!validcard) {
                    $('#ppoo-cc-number').closest('.form-group').addClass('has-error');
                    allvalid = false;
                } else {
                    $('#ppoo-cc-number').closest('.form-group').removeClass('has-error');
                }

                var expiry = $('#ppoo-cc-exp').payment('cardExpiryVal');
                var validexpiry = $.payment.validateCardExpiry(expiry.month, expiry.year);

                if (!validexpiry) {
                    $('#ppoo-cc-exp').closest('.form-group').addClass('has-error');
                    allvalid = false;
                } else {
                    $('#ppoo-cc-exp').closest('.form-group').removeClass('has-error');
                }

                var validcv = $.payment.validateCardCVC($('#ppoo-cc-cvc').val());

                if (!validcv) {
                    $('#ppoo-cc-cvc').closest('.form-group').addClass('has-error');
                    allvalid = false;
                } else {
                    $('#ppoo-cc-cvc').closest('.form-group').removeClass('has-error');
                }

                if (!allvalid) {
                    if (!validcard) {
                        $('#ppoo-cc-number').focus()
                    } else {
                        if (!validexpiry) {
                            $('#ppoo-cc-exp').focus()
                        } else {
                            if (!validcv) {
                                $('#ppoo-cc-cvc').focus()
                            }
                        }
                    }

                    return false;
                } else {
                    return true;
                }
            }

        });


    });


</script>


<div class="panel panel-default credit-card-box">
    <div class="panel-body">
        <div style="display:none;" class="store-payment-errors an-payment-errors">
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label for="cardNumber"><?= t('Card Number'); ?></label>
                    <div class="input-group">
                        <input name="once_off_number"
                                type="tel"
                                class="form-control"
                                id="ppoo-cc-number"
                                placeholder="<?= t('Card Number'); ?>"
                                autocomplete="cc-number"
                        />
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-7 col-md-7">
                <div class="form-group">
                    <label for="cardExpiry"><?= t('Expiration Date'); ?></label>
                    <input name="once_off_expiry"
                            type="tel"
                            class="form-control"
                            id="ppoo-cc-exp"
                            placeholder="MM / YYYY"
                            autocomplete="cc-exp"
                    />
                </div>
            </div>
            <div class="col-xs-5 col-md-5 pull-right">
                <div class="form-group">
                    <label for="cardCVV"><?= t('CVV'); ?></label>
                    <input name="once_off_cvc"
                            type="tel"
                            class="form-control"
                            id="ppoo-cc-cvc"
                            placeholder="<?= t('CVV'); ?>"
                            autocomplete="off"
                    />
                </div>
            </div>
        </div>
    </div>
</div>