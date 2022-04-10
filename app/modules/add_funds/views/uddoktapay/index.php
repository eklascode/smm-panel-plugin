<div id="uddoktapay" class="tab-pane fade">
    <form class="form actionAddFundsForm" action method="POST">
        <div class="row">
            <div class="col-md-12">
                <?php
                if (get_option("uddoktapay_api_key", '') != "") {
                ?>
                    <div class="for-group text-center">
                        <img src="<?= BASE ?>/assets/images/payments/uddoktapay.png" style="max-width: 250px;" alt="uddoktapay icon">
                        <p class="p-t-10"><small><?= sprintf(lang("you_can_deposit_funds_with_uddoktapay_they_will_be_automaticly_added_into_your_account"), 'UddoktaPay') ?></small></p>
                    </div>
                    <div class="form-group">
                        <label><?= sprintf(lang("amount_usd"), 'USD') ?></label>
                        <input class="form-control square" type="number" name="amount" placeholder="10">
                        <input type="hidden" name="payment_method" value="uddoktapay">
                    </div>
                    <div class="form-group">
                        <p><strong>Note:</strong></p>
                        <ul>
                            <li><?= lang("transaction_fee") ?>:<span> </span><strong><?= (get_option("uddoktapay_chagre_fee", 1)) ?>%</strong></li>
                            <li><strong>THIS PAYMENT IS FOR SERVICES AND IT IS NOT REFUNDABLE</strong></li>

                        </ul>
                    </div>




                    <div class="form-group">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="agree" value="1">
                            <span class="custom-control-label"><?= lang("yes_i_understand_after_the_funds_added_i_will_not_ask_fraudulent_dispute_or_chargeback") ?></span>
                        </label>
                    </div>
                    <div class="form-actions left">
                        <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1">
                            <?= lang("Pay") ?>
                        </button>
                    </div>

                <?php } else { ?>
                    <div class="form-group">
                        <div class="alert alert-danger p-t-10" role="alert">
                            <?= lang("this_payment_gateway_is_not_already_active_at_the_present") ?>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </form>
</div>

<script>
    $(document).on("submit", ".actionAddFundsForm", function() {
        pageOverlay.show();
        event.preventDefault();
        _that = $(this);
        _payment_type = $("#paymentFrom").find("input[name=payment_type]").val();
        _action = PATH + 'add_funds/' + '/process';
        _redirect = _that.data("redirect");
        _data = _that.serialize();
        _data = _data + '&' + $.param({
            token: token
        });

        $.post(_action, _data, function(_result) {
            setTimeout(function() {
                pageOverlay.hide();
            }, 1500)
            if (is_json(_result)) {
                _result = JSON.parse(_result);
                setTimeout(function() {
                    notify(_result.message, _result.status);
                }, 1500)
                setTimeout(function() {
                    if (_result.status == 'success' && typeof _redirect != "undefined") {
                        reloadPage(_redirect);
                    }
                }, 2000)
            } else {
                setTimeout(function() {
                    $(".actionAddFundsForm").html(_result);
                }, 1500)
            }
        })
        return false;
    })
</script>