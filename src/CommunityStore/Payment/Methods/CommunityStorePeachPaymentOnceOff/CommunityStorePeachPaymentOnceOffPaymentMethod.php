<?php

namespace Concrete\Package\CommunityStorePeachPayment\Src\CommunityStore\Payment\Methods\CommunityStorePeachPaymentOnceOff;

use Core;
use Config;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;


class CommunityStorePeachPaymentOnceOffPaymentMethod extends StorePaymentMethod
{

    public function dashboardForm()
    {
        $this->set('peachPaymentOnceOffMode', Config::get('community_store_peach_payment_once_off.mode'));
        $this->set('peachPaymentOnceOffCurrency', Config::get('community_store_peach_payment_once_off.currency'));
        $this->set('peachPaymentOnceOffPaymentType', Config::get('community_store_peach_payment_once_off.paymentType'));
        $this->set('peachPaymentOnceOffTestURL', Config::get('community_store_peach_payment_once_off.testURL'));
        $this->set('peachPaymentOnceOffLiveURL', Config::get('community_store_peach_payment_once_off.liveURL'));
        $this->set('peachPaymentOnceOffTestEntityID', Config::get('community_store_peach_payment_once_off.testEntityID'));
        $this->set('peachPaymentOnceOffLiveEntityID', Config::get('community_store_peach_payment_once_off.liveEntityID'));
        $this->set('peachPaymentOnceOffTestAuthorization', Config::get('community_store_peach_payment_once_off.testAuthorization'));
        $this->set('peachPaymentOnceOffLiveAuthorization', Config::get('community_store_peach_payment_once_off.liveAuthorization'));

        $this->set('form', Core::make("helper/form"));

        $currencies = [
            'ZAR' => t('South African rand'),
            'USD' => t('United States Dollar'),
            'CAD' => t('Canadian Dollar'),
            'CHF' => t('Swiss Franc'),
            'DKK' => t('Danish Krone'),
            'EUR' => t('Euro'),
            'GBP' => t('Pound Sterling'),
            'NOK' => t('Norwegian Krone'),
            'PLN' => t('Polish Zloty'),
            'SEK' => t('Swedish Krona'),
            'AUD' => t('Australian Dollar'),
            'NZD' => t('New Zealand Dollar')
        ];

        $this->set('peachPaymentOnceOffCurrencies', $currencies);

        $paymentTypes = [
            'PA' => t('Preauthorization'),
            'DB' => t('Debit'),
            'CD' => t('Credit'),
            'CP' => t('Capture'),
            'RV' => t('Reversal'),
            'RF' => t('Refund')
        ];

        $this->set('peachPaymentOnceOffPaymentTypes', $paymentTypes);

    }

    public function save(array $data = [])
    {
        Config::save('community_store_peach_payment_once_off.mode', $data['peachPaymentOnceOffMode']);
        Config::save('community_store_peach_payment_once_off.currency', $data['peachPaymentOnceOffCurrency']);
        Config::save('community_store_peach_payment_once_off.paymentType', $data['peachPaymentOnceOffPaymentType']);
        Config::save('community_store_peach_payment_once_off.testURL', $data['peachPaymentOnceOffTestURL']);
        Config::save('community_store_peach_payment_once_off.liveURL', $data['peachPaymentOnceOffLiveURL']);
        Config::save('community_store_peach_payment_once_off.testEntityID', $data['peachPaymentOnceOffTestEntityID']);
        Config::save('community_store_peach_payment_once_off.liveEntityID', $data['peachPaymentOnceOffLiveEntityID']);
        Config::save('community_store_peach_payment_once_off.testAuthorization', $data['peachPaymentOnceOffTestAuthorization']);
        Config::save('community_store_peach_payment_once_off.liveAuthorization', $data['peachPaymentOnceOffLiveAuthorization']);


    }

    public function validate($args, $e)
    {
        return $e;
    }

    public function checkoutForm()
    {
        $pmID = StorePaymentMethod::getByHandle('community_store_peach_payment_once_off')->getID();
        $this->set('pmID', $pmID);
    }

    public function submitPayment()
    {
        $customer = new StoreCustomer();
        $currency = Config::get('community_store_peach_payment_once_off.currency');
        $paymentType = Config::get('community_store_peach_payment_once_off.paymentType');
        $mode = Config::get('community_store_peach_payment_once_off.mode');
        $total = number_format(StoreCalculator::getGrandTotal(), 2, '.', '');

        if ($mode == 'test') {
            $url = Config::get('community_store_peach_payment_once_off.testURL');
            $entityId = Config::get('community_store_peach_payment_once_off.testEntityID');
            $authorization = Config::get('community_store_peach_payment_once_off.testAuthorization');
        } else {
            $url = Config::get('community_store_peach_payment_once_off.liveURL');
            $entityId = Config::get('community_store_peach_payment_once_off.liveEntityID');
            $authorization = Config::get('community_store_peach_payment_once_off.liveAuthorization');
        }
        $expiry_arr = @explode('/', $_POST['once_off_expiry']);

        $fields = array("entityId" => $entityId,
            "amount" => $total,
            "currency" => $currency,
            "paymentType" => $paymentType,
            "card.number" => str_replace(' ', '', $_POST['once_off_number']),
            "card.expiryMonth" => trim($expiry_arr[0]),
            "card.expiryYear" => trim($expiry_arr[1]),
            "card.cvv" => $_POST['once_off_cvc'],
            "customer.givenName" => $customer->getValue('billing_first_name'),
            "customer.surname" => $customer->getValue('billing_last_name'),
            "customer.mobile" => $customer->getValue('billing_phone'),
            "customer.email" => $customer->getEmail(),
            "customer.ip" => $_SERVER['REMOTE_ADDR']
        );

        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer ' . $authorization));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = json_decode(curl_exec($ch));
        curl_close($ch);
        $resultCode = $responseData->result->code;
        if (in_array($resultCode, array('000.000.000', '000.100.110', '000.100.111', '000.100.112'))) {
            return array('error' => 0, 'transactionReference' => $responseData->id);
        } else {
            return array('error' => 1, 'errorMessage' => $responseData->result->description);
        }
    }

    public function getPaymentMethodName()
    {
        return 'Peach Payment Once Off';
    }

    public function getPaymentMethodDisplayName()
    {
        return $this->getPaymentMethodName();
    }

}

return __NAMESPACE__;