<?php

namespace Concrete\Package\CommunityStorePeachPayment\Src\CommunityStore\Payment\Methods\CommunityStorePeachPaymentEft;

use Core;
use Config;
use Exception;
use Session;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use URL;
use Concrete\Core\Routing\Redirect;


class CommunityStorePeachPaymentEftPaymentMethod extends StorePaymentMethod
{

    public function dashboardForm()
    {
        $this->set('peachPaymentEftMode', Config::get('community_store_peach_payment_eft.mode'));
        $this->set('peachPaymentEftTestURL', Config::get('community_store_peach_payment_eft.testURL'));
        $this->set('peachPaymentEftLiveURL', Config::get('community_store_peach_payment_eft.liveURL'));
        $this->set('peachPaymentEftTestUsername', Config::get('community_store_peach_payment_eft.testUsername'));
        $this->set('peachPaymentEftLiveUsername', Config::get('community_store_peach_payment_eft.liveUsername'));
        $this->set('peachPaymentEftTestPassword', Config::get('community_store_peach_payment_eft.testPassword'));
        $this->set('peachPaymentEftLivePassword', Config::get('community_store_peach_payment_eft.livePassword'));
        $this->set('peachPaymentEftEnableJavascriptPopup', Config::get('community_store_peach_payment_eft.enableJavascriptPopup'));

        $this->set('form', Core::make("helper/form"));

    }

    public function save(array $data = [])
    {
        Config::save('community_store_peach_payment_eft.mode', $data['peachPaymentEftMode']);
        Config::save('community_store_peach_payment_eft.testURL', $data['peachPaymentEftTestURL']);
        Config::save('community_store_peach_payment_eft.liveURL', $data['peachPaymentEftLiveURL']);
        Config::save('community_store_peach_payment_eft.testUsername', $data['peachPaymentEftTestUsername']);
        Config::save('community_store_peach_payment_eft.liveUsername', $data['peachPaymentEftLiveUsername']);
        Config::save('community_store_peach_payment_eft.testPassword', $data['peachPaymentEftTestPassword']);
        Config::save('community_store_peach_payment_eft.livePassword', $data['peachPaymentEftLivePassword']);
        Config::save('community_store_peach_payment_eft.enableJavascriptPopup', $data['peachPaymentEftEnableJavascriptPopup']);
    }

    public function validate($args, $e)
    {
        return $e;
    }

    public function checkoutForm()
    {
        $this->set('enableJavascriptPopup', Config::get('community_store_peach_payment_eft.enableJavascriptPopup'));
        $pmID = StorePaymentMethod::getByHandle('community_store_peach_payment_eft')->getID();
        $this->set('pmID', $pmID);
    }

    public function submitPayment()
    {

    }

    public function redirectForm()
    {

    }

    public static function validateCompletion()
    {

        $raw_post_data = file_get_contents('php://input');
        parse_str(rawurldecode($raw_post_data), $raw_post_array);
        if ($raw_post_array['success']) {
            $order = StoreOrder::getByID($raw_post_array['merchant_reference']);
            $order->completeOrder($raw_post_array['callpay_transaction_id']);
            $order->updateStatus(StoreOrderStatus::getStartingStatus()->getHandle());
        } else {
            \Log::addError("Invalid Transaction: " . $raw_post_array['callpay_transaction_id'] . "<br>Order ID: " . $raw_post_array['merchant_reference']);
        }
    }

    public function getPaymentMethodName()
    {
        return 'Peach Payment EFT';
    }

    public function getPaymentMethodDisplayName()
    {
        return $this->getPaymentMethodName();
    }

    public function isExternal()
    {
        return true;
    }

    public function getAction()
    {

        if (!Config::get('community_store_peach_payment_eft.enableJavascriptPopup')) {
            $mode = Config::get('community_store_peach_payment_eft.mode');
            $total = number_format(StoreCalculator::getGrandTotal(), 2, '.', '');

            if ($mode == 'test') {
                $url = Config::get('community_store_peach_payment_eft.testURL');
                $username = Config::get('community_store_peach_payment_eft.testUsername');
                $password = Config::get('community_store_peach_payment_eft.testPassword');
            } else {
                $url = Config::get('community_store_peach_payment_eft.liveURL');
                $username = Config::get('community_store_peach_payment_eft.liveUsername');
                $password = Config::get('community_store_peach_payment_eft.livePassword');
            }
            $fields = array("amount" => $total,
                "merchant_reference" => Session::get('orderID'),
                "notify_url" => URL::to('/checkout/peach_payment_eft_response'),
                "success_url" => URL::to('/checkout/complete'),
                "cancel_url" => URL::to('/checkout'),
                "error_url" => URL::to('/checkout'));
            $fields_string = '';
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');

            try {    //setting the curl parameters.
                $ch = curl_init();
                if (FALSE === $ch)
                    throw new Exception('failed to initialize');

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization:Basic ' . base64_encode($username . ':' . $password)));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $responseData = curl_exec($ch);
                curl_close($ch);
                $responseObj = json_decode($responseData);
                if ($responseObj->message != '') {
                    Session::set('paymentErrors', $responseObj->message);
                    $this->redirect('/checkout/failed#payment');
                    exit;
                } else {
                    return $responseObj->url;
                }
            } catch (Exception $e) {
                Session::set('paymentErrors', t('An error occurred, the transaction did not succeed'));
                $this->redirect('/checkout/failed#payment');
                exit;
            }
        }


    }

    public function getPaymentKey()
    {
        $mode = Config::get('community_store_peach_payment_eft.mode');
        $total = number_format(StoreCalculator::getGrandTotal(), 2, '.', '');

        if ($mode == 'test') {
            $url = Config::get('community_store_peach_payment_eft.testURL');
            $username = Config::get('community_store_peach_payment_eft.testUsername');
            $password = Config::get('community_store_peach_payment_eft.testPassword');
        } else {
            $url = Config::get('community_store_peach_payment_eft.liveURL');
            $username = Config::get('community_store_peach_payment_eft.liveUsername');
            $password = Config::get('community_store_peach_payment_eft.livePassword');
        }
        $fields = array("amount" => $total,
            "merchant_reference" => Session::get('orderID'),
            "notify_url" => URL::to('/checkout/peach_payment_eft_response'),
        );
        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        try {    //setting the curl parameters.
            $ch = curl_init();
            if (FALSE === $ch)
                throw new Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:Basic ' . base64_encode($username . ':' . $password)));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            curl_close($ch);
            $responseObj = json_decode($responseData);
            if ($responseObj->message != '') {
                $messages = array('success' => 0, 'message' => $responseObj->message);
            } else {
                $messages = array('success' => 1, 'key' => $responseObj->key);
            }
        } catch (Exception $e) {
            $messages = array('success' => 0, 'message' => t('An error occurred, the transaction did not succeed'));
        }
        echo json_encode($messages);

    }


}

return __NAMESPACE__;