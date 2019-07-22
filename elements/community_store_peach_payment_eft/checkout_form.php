<?php defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);
if ($enableJavascriptPopup) {
    ?>
    <script>

        window.addEventListener("message", function (event) {
            if (typeof event.data == 'string') {
                try {
                    console.log(event.data);
                    eval(event.data); //handle frame events from child
                } catch (e) {
                }
            }
        });

        $(window).on('load', function () {

            $('.store-btn-complete-order').on('click', function (e) {
                // Open Checkout with further options
                var currentpmid = $('input[name="payment-method"]:checked:first').data('payment-method-id');

                if (currentpmid == <?= $pmID; ?>) {
                    $(this).prop('disabled', true);
                    $(this).val('<?= t('Processing...'); ?>');
                    $.getScript("https://eftsecure.callpay.com/ext/eftsecure/js/checkout.js?ver=2.0")
                        .done(function (script) {
                            var paymentform = $('#store-checkout-form-group-payment');
                            var data = paymentform.serialize();
                            $.ajax({
                                url: paymentform.attr('action'),
                                type: 'post',
                                cache: false,
                                data: data,
                                success: function (data) {
                                    $.ajax({
                                        url: '<?php echo URL::to('/checkout/peach_payment_get_payment_key') ?>',
                                        type: 'get',
                                        cache: false,
                                        dataType: 'text',
                                        success: function (data) {
                                            var dataObj = $.parseJSON(data);
                                            if (dataObj.success) {
                                                eftSec.checkout.init({
                                                    paymentKey: dataObj.key,
                                                    notifyUrl: '<?php echo URL::to('/checkout/peach_payment_eft_response') ?>',
                                                    onLoad: function () {
                                                        $('.store-btn-complete-order').show();
                                                    },
                                                    onComplete: function (data) {
                                                        eftSec.checkout.hideFrame();
                                                        if (data.success == true) {
                                                            window.location.href = '<?php echo URL::to('/checkout/complete') ?>';
                                                        } else {
                                                            window.location.href = '<?php echo URL::to('/checkout') ?>';
                                                        }
                                                    },
                                                });
                                            } else {
                                                alert(dataObj.message);
                                                $('.store-btn-complete-order').removeAttr('disabled');
                                                $('.store-btn-complete-order').val('Complete Order');
                                            }

                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            console.log('dffds');
                                        }
                                    });

                                }
                            });

                        })
                        .fail(function (jqxhr, settings, exception) {

                        });


                    e.preventDefault();
                }
            });

        });
    </script>
<?php } ?>