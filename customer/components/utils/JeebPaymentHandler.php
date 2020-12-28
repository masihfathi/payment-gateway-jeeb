<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Payment gateway - Jeeb
 *
 * Retrieve payments using jeeb
 *
 * @package MasihFathi
 * @subpackage Payment Gateway Jeeb.io
 * @author Masih Fathi <masihfathi@gmail.com>
 * @link https://www.avangemail.com/
 */

set_time_limit(0);
class JeebPaymentHandler extends PaymentHandlerAbstract
{
    // render the payment form
    public function renderPaymentView()
    {
        $order   = $this->controller->getData('order');
        $model   = $this->extension->getExtModel();
        $customVars = sha1(StringHelper::uniqid());
        $returnUrl = Yii::app()->createAbsoluteUrl('payment_gateway_ext_jeeb/callback')."?custom={$customVars}";
        $webhookUrl = Yii::app()->createAbsoluteUrl('payment_gateway_ext_jeeb/webhook')."?custom={$customVars}";
        $result =  $this->requestPayment($order,$model,$returnUrl,$customVars,$webhookUrl);


        $assetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias($this->extension->getPathAlias()) . '/assets/customer', false, -1, MW_DEBUG);
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/js/payment-form.js');

        $view       = $this->extension->getPathAlias() . '.customer.views.payment-form';

        $this->controller->renderPartial($view, compact('result','order','customVars','returnUrl','model'));
    }
    // validate jeeb request
    public function requestPayment($order,$model,$returnUrl,$customVars,$webhookUrl)
    {
        if(!isset($model->merchant_code)){
            return array('error' => Yii::t('payment_gateway_ext_jeeb','Token is not set'));
        }
        $options = [
            'orderNo' => (string) $customVars,
            'client' => 'Internal',
            'baseCurrencyId' =>(string)$model->base_currency_id,
            'baseAmount' => (float)$order->total,
            'webhookUrl'=>$webhookUrl,
            'callbackUrl'=>$returnUrl
            //'allowTestNets' => true
        ];
        $jeebConnection = new JeebConnection($model->merchant_code);
        try{
            $result = $jeebConnection->issue($options);
        }catch (CHttpException $e){
            Yii::log((string)$e->getMessage(), CLogger::LEVEL_WARNING);
            return array('error' => 'Error happened');
        }
        if($result['succeed'] === true){
            $url = 'https://core.jeeb.io/api/v3/payments/invoice?token='.(string)$result['result']['token'];
            return array('url'=>$url);
        }
        return array('error' => 'Error happened');
    }

    // mark the order as pending retry
    public function processOrder()
    {
        $request = Yii::app()->request;

        if (strlen($request->getPost('custom')) != 40) {
            return false;
        }
        
        $transaction = $this->controller->getData('transaction');
        $order       = $this->controller->getData('order');
        
        $order->status = PricePlanOrder::STATUS_PENDING;
        $order->save(false);

        $transaction->payment_gateway_name = 'Jeeb - www.jeeb.io';
        $transaction->payment_gateway_transaction_id = Yii::app()->ioFilter->stripPurify($request->getPost('custom'));
        $transaction->status = PricePlanOrderTransaction::STATUS_PENDING_RETRY;
        $transaction->save(false);
  
        $message = Yii::t('payment_gateway_ext_jeeb', 'Your order is in "{status}" status, it usually takes a few minutes to be processed and if everything is fine, your pricing plan will become active!', array(
            '{status}' => Yii::t('orders', $order->status),
        ));
        
        if ($request->isAjaxRequest) {
            return $this->controller->renderJson(array(
                'result'  => 'success', 
                'message' => $message,
            ));
        }
    }
}
