<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<div class="form-group">
    <?= $form->label('peachPaymentEftMode', t('Mode')) ?>
    <?= $form->select('peachPaymentEftMode', array('test' => t('Test'), 'live' => t('Live')), $peachPaymentEftMode) ?>
</div>

<div class="form-group">
    <label><?= t("Test URL") ?></label>
    <input type="text" name="peachPaymentEftTestURL" value="<?= $peachPaymentEftTestURL ?>" class="form-control">
</div>

<div class="form-group">
    <label><?= t("Test Username") ?></label>
    <input type="text" name="peachPaymentEftTestUsername" value="<?= $peachPaymentEftTestUsername ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Test Password") ?></label>
    <input type="text" name="peachPaymentEftTestPassword" value="<?= $peachPaymentEftTestPassword ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Live URL") ?></label>
    <input type="text" name="peachPaymentEftLiveURL" value="<?= $peachPaymentEftLiveURL ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Live Username") ?></label>
    <input type="text" name="peachPaymentEftLiveUsername" value="<?= $peachPaymentEftLiveUsername ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Live Password") ?></label>
    <input type="text" name="peachPaymentEftLivePassword" value="<?= $peachPaymentEftLivePassword ?>"
           class="form-control">
</div>

<div class="form-group">
    <label><?= t("Enable javascript popup instead of redirect ?") ?></label>
    <input type="checkbox" name="peachPaymentEftEnableJavascriptPopup"
           value="1" <?= $peachPaymentEftEnableJavascriptPopup ? 'checked' : ''; ?> >
</div>