<?php

namespace Concrete\Package\CommunityStorePeachPayment;

use Package;
use Whoops\Exception\ErrorException;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;

class Controller extends Package
{
    protected $pkgHandle = 'community_store_peach_payment';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '1.0.0';

    protected $pkgAutoloaderRegistries = array(
        'src/CommunityStore' => 'Concrete\Package\CommunityStorePeachPayment\Src\CommunityStore',
    );

    public function getPackageDescription()
    {
        return t("Peach Payment Method for Community Store");
    }

    public function getPackageName()
    {
        return t("Peach Payment Method");
    }

    public function install()
    {
        $installed = Package::getInstalledHandles();
        if (!(is_array($installed) && in_array('community_store', $installed))) {
            throw new ErrorException(t('This package requires that Community Store be installed'));
        } else {
            $pkg = parent::install();
            $pm = new PaymentMethod();
            $pm->add('community_store_peach_payment_once_off', 'Peach Payment Once Off', $pkg);
            $pm->add('community_store_peach_payment_eft', 'Peach Payment EFT', $pkg);

        }
    }


    public function uninstall()
    {
        $pm = PaymentMethod::getByHandle('community_store_peach_payment_once_off');
        if ($pm) {
            $pm->delete();
        }
        $pm = PaymentMethod::getByHandle('community_store_peach_payment_eft');
        if ($pm) {
            $pm->delete();
        }
        parent::uninstall();
    }

    public function on_start()
    {
        \Route::register('/checkout/peach_payment_eft_response', '\Concrete\Package\CommunityStorePeachPayment\Src\CommunityStore\Payment\Methods\CommunityStorePeachPaymentEft\CommunityStorePeachPaymentEftPaymentMethod::validateCompletion');

        \Route::register('/checkout/peach_payment_get_payment_key', '\Concrete\Package\CommunityStorePeachPayment\Src\CommunityStore\Payment\Methods\CommunityStorePeachPaymentEft\CommunityStorePeachPaymentEftPaymentMethod::getPaymentKey');

    }

}

?>