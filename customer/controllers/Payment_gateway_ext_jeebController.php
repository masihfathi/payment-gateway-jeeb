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

class Payment_gateway_ext_jeebController extends Controller
{

    // jeeb complete status
    const JEEB_COMPLETED = 'Completed';
    // jeeb pending status
    const JEEB_PENDING_CONFIRMATION = 'PendingConfirmation';
    // jeeb expired
    const JEEB_EXPIRED = 'Expired';
    // the extension instance
    public $extension;
    // jeeb post data
    private $post_data;
    // transaction instance
    private $transaction;
    // model instance
    private $model;
    // order instance
    private $order;
    /**
     * @return array action filters
     */
    public function filters()
    {
        $filters = array(
            'postOnly + webhook,callback',
            'accessControl'
        );

        return CMap::mergeArray($filters, parent::filters());
    }
    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('webhook'),
                // jeeb.io webhook ips https://docs.jeeb.io/fa/#webhook
                'ips' => array('35.209.237.202','52.56.239.177'),
            ),
            array('deny',
                'actions' => array('webhook'),
                'ips' => array('*'),
            ),
        );
    }
    /**
     * validate payment
     */
    public function actionCallback()
    {
        $this->post_data = $_POST;
        $this->_getTransaction();
        if(($this->post_data['state'] == 'Expired')){
            $this->_paymentChange($this->order,$this->transaction,self::JEEB_EXPIRED,'');
        }
        if(($this->post_data['state'] == 'PendingConfirmation') || ($this->post_data['state'] == 'PendingTransaction')){
            $refund = $this->post_data['refund'] ?? '';
            $this->_paymentChange($this->order,$this->transaction,self::JEEB_PENDING_CONFIRMATION,$refund);
            Yii::app()->notify->addWarning(Yii::t('payment_gateway_ext_jeeb', 'You must wait until payment confirmation'));
        }
        $this->redirect(array('price_plans/index'));
    }

    /**
     * validate webhook
     * @throws Exception
     */
    public function actionWebhook()
    {
        $this->post_data = Yii::app()->request->getRawBody();
        $this->_getTransaction();
        $data = json_decode($this->post_data,true);
        if( ($this->order->status != PricePlanOrder::STATUS_COMPLETE)){
            if(!is_null( $result = $this->_getStatus((string)$data['token']) )){
                $this->_paymentChange($this->order,$this->transaction,$result['result']['state'],$result['result']['refund']);
                if($result['result']['state'] == self::JEEB_COMPLETED) {
                    $this->_seal($result['result']['token']);
                }
            }
        }
    }
    private function _getStatus($token)
    {
        try {
            $jeebConnection = new JeebConnection($this->model->merchant_code);
            $result = $jeebConnection->status(['token' => $token]);
            return isset($result['result']['state']) ? $result : null;
        }catch (\CHttpException $e){
            Yii::log($e->getMessage(), CLogger::LEVEL_INFO);
        }
    }
    private function _seal($token)
    {
        try {
            $jeebConnection = new JeebConnection($this->model->merchant_code);
            $jeebConnection->seal(['token' => $token]);
        }catch (\CHttpException $e){
            Yii::log($e->getMessage(), CLogger::LEVEL_INFO);
        }
        return null;
    }
    private function _getTransaction()
    {
        $getData = Yii::app()->params['GET'];
        if (!$getData->itemAt('custom')) {
            Yii::app()->end();
        }
        $this->transaction = PricePlanOrderTransaction::model()->findByAttributes(array(
            'payment_gateway_transaction_id' => (string) $getData->itemAt('custom'),
            'status'                         => PricePlanOrderTransaction::STATUS_PENDING_RETRY,
        ));

        if (empty($this->transaction)) {
            Yii::app()->end();
        }

        $this->model= $this->extension->getExtModel();
        $this->order = $this->transaction->order;
    }
    private function _paymentChange($order,$transaction,$status,$refund = '')
    {
        switch ($status){
            case self::JEEB_EXPIRED:
                $order->status = PricePlanOrder::STATUS_FAILED;
                $order->save(false);
                $transaction->status = PricePlanOrderTransaction::STATUS_FAILED;
                $transaction->save(false);
                break;
            case self::JEEB_PENDING_CONFIRMATION:
                $order->status = PricePlanOrder::STATUS_PENDING;
                $order->save(false);
                $transaction->status = PricePlanOrderTransaction::STATUS_PENDING_RETRY;
                $this->_saveTransaction($transaction,$order,$refund);
                break;
            case self::JEEB_COMPLETED:
                $order->status = PricePlanOrder::STATUS_COMPLETE;
                $order->save(false);
                $transaction->status = PricePlanOrderTransaction::STATUS_SUCCESS;
                $this->_saveTransaction($transaction,$order,$refund);
                break;
        }

    }
    private function _saveTransaction($transaction,$order,$refund = '')
    {
        if($transaction->save(false)) {
            if($refund === true){
                // if no note available for this order, create new order note for this order
                if(count($order->notes) == 0) {
                    $orderNote = new PricePlanOrderNote;
                    $orderNote->order_id = $order->order_id;
                    $orderNote->customer_id = $order->customer->customer_id;
                    $orderNote->note = "The payment had been completed, but the amount paid is not equal to expected";
                    $orderNote->save();
                }
            }
        }
    }
}