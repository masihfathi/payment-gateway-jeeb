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
    // the extension instance
    public $extension;

    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-payment-gateway-jeeb.backend.views');
    }
    
    /**
     * Default action.
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = $this->extension->getExtModel();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            if ($model->validate()) {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                $model->save();
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('ext_payment_gateway_jeeb', 'Jeeb.io payment gateway'),
            'pageHeading'       =>  Yii::t('ext_payment_gateway_jeeb', 'Jeeb.io payment gateway'),
            'pageBreadcrumbs'   => array(
                'Payment gateways' => $this->createUrl('payment_gateways/index'),
                Yii::t('ext_payment_gateway_jeeb','Jeeb.io payment gateway'),
            )
        ));

        $this->render('settings', compact('model'));
    }
}