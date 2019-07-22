<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<div class="form-group">
    <?= $form->label('peachPaymentOnceOffCurrency', t('Currency')) ?>
    <?= $form->select('peachPaymentOnceOffCurrency', $peachPaymentOnceOffCurrencies, $peachPaymentOnceOffCurrency) ?>
</div>
<div class="form-group">
    <?= $form->label('peachPaymentOnceOffPaymentType', t('Payment Type')) ?>
    <?= $form->select('peachPaymentOnceOffPaymentType', $peachPaymentOnceOffPaymentTypes, $peachPaymentOnceOffPaymentType) ?>
</div>

<div class="form-group">
    <?= $form->label('peachPaymentOnceOffMode', t('Mode')) ?>
    <?= $form->select('peachPaymentOnceOffMode', array('test' => t('Test'), 'live' => t('Live')), $peachPaymentOnceOffMode) ?>
</div>

<div class="form-group">
    <label><?= t("Test URL") ?></label>
    <input type="text" name="peachPaymentOnceOffTestURL" value="<?= $peachPaymentOnceOffTestURL ?>" class="form-control">
</div>

<div class="form-group">
    <label><?= t("Test Entity ID") ?></label>
    <input type="text" name="peachPaymentOnceOffTestEntityID" value="<?= $peachPaymentOnceOffTestEntityID ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Test Authorization Bearer") ?></label>
    <input type="text" name="peachPaymentOnceOffTestAuthorization" value="<?= $peachPaymentOnceOffTestAuthorization ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Live URL") ?></label>
    <input type="text" name="peachPaymentOnceOffLiveURL" value="<?= $peachPaymentOnceOffLiveURL ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Live Entity ID") ?></label>
    <input type="text" name="peachPaymentOnceOffLiveEntityID" value="<?= $peachPaymentOnceOffLiveEntityID ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Live Authorization Bearer") ?></label>
    <input type="text" name="peachPaymentOnceOffLiveAuthorization" value="<?= $peachPaymentOnceOffLiveAuthorization ?>"
           class="form-control">
</div>


