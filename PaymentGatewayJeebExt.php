<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Payment gateway - Jeeb.io
 *
 * Retrieve payments using cryptocurrency
 *
 * @package MasihFathi
 * @subpackage Payment Gateway Jeeb
 * @author Masih Fathi <masihfathi@gmail.com>
 * @link https://www.avangemail.com/
 * @license The MIT License
 * @Copyright AvangEmail. https://avangemail.com
 */
 
class PaymentGatewayJeebExt extends ExtensionInit
{
    // name of the extension as shown in the backend panel
    public $name = 'Payment gateway - Jeeb.io';
    
    // description of the extension as shown in backend panel
    public $description = 'Retrieve payments using Jeeb.io';
    
    // current version of this extension
    public $version = '1.0';
    
    // the author name
    public $author = 'Masih Fathi';
    
    // author website
    public $website = 'https://www.avangemail.com/';
    
    // contact email address
    public $email = 'masihfathi@gmail.com';
    
    // in which apps this extension is allowed to run
    public $allowedApps = array('customer', 'backend');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = true;
    
    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;
    
    // the extension model
    protected $_extModel;
    
    // run the extension
    public function run()
    {
        Yii::import('ext-payment-gateway-jeeb.common.models.*');
        
        if ($this->isAppName('backend')) {
            
            // handle all backend related tasks
            $this->backendApp();
        
        } elseif ($this->isAppName('customer') && $this->getOption('status', 'disabled') == 'enabled') {
        
            // handle all customer related tasks
            $this->customerApp();
        }
    }
    
    // Add the landing page for this extension (settings/general info/etc)
    public function getPageUrl()
    {
        return Yii::app()->createUrl('payment_gateway_ext_jeeb/index');
    }
    
    // handle all backend related tasks
    protected function backendApp()
    {
        $hooks = Yii::app()->hooks;
        
        // register the url rule to resolve the extension page.
        Yii::app()->urlManager->addRules(array(
            array('payment_gateway_ext_jeeb/index', 'pattern' => 'payment-gateways/jeeb'),
            array('payment_gateway_ext_jeeb/<action>', 'pattern' => 'payment-gateways/jeeb/*'),
        ));
        
        // add the backend controller
        Yii::app()->controllerMap['payment_gateway_ext_jeeb'] = array(
            'class'     => 'ext-payment-gateway-jeeb.backend.controllers.Payment_gateway_ext_jeebController',
            'extension' => $this,
        );
        
        // register the gateway in the list of available gateways.
        $hooks->addFilter('backend_payment_gateways_display_list', array($this, '_registerGatewayForBackendDisplay'));
    }

    // register the gateway in the available gateways list
    public function _registerGatewayForBackendDisplay(array $registeredGateways = array())
    {
        if (isset($registeredGateways['jeeb'])) {
            return $registeredGateways;
        }
        
        $registeredGateways['jeeb'] = array(
            'id'            => 'jeeb',
            'name'          => Yii::t('ext_payment_gateway_jeeb','Jeeb'),
            'description'   => Yii::t('ext_payment_gateway_jeeb','Retrieve payments using jeeb'),
            'status'        => $this->getOption('status', 'disabled'),
            'sort_order'    => (int)$this->getOption('sort_order', 1),
            'page_url'      => $this->getPageUrl(),
        );
        
        return $registeredGateways;
    }
    
    // handle all customer related tasks
    protected function customerApp()
    {
        $hooks = Yii::app()->hooks;
        
        // import the utils
        Yii::import('ext-payment-gateway-jeeb.customer.components.utils.*');

        // register the url rule to validate payment.
        Yii::app()->urlManager->addRules(array(
            array('payment_gateway_ext_jeeb/callback', 'pattern' => 'payment-gateways/callback'),
        ));
        Yii::app()->urlManager->addRules(array(
            array('payment_gateway_ext_jeeb/webhook', 'pattern' => 'payment-gateways/webhook'),
        ));
        
        // add the customer controller
        Yii::app()->controllerMap['payment_gateway_ext_jeeb'] = array(
            'class'     => 'ext-payment-gateway-jeeb.customer.controllers.Payment_gateway_ext_jeebController',
            'extension' => $this,
        );
        
        // set the controller unprotected so jeeb can post freely
        $unprotected = (array)Yii::app()->params->itemAt('unprotectedControllers');
        array_push($unprotected, 'payment_gateway_ext_jeeb');
        Yii::app()->params->add('unprotectedControllers', $unprotected);

        // remove the csrf token validation
        $request = Yii::app()->request;
        if ($request->enableCsrfValidation) {
            $url = Yii::app()->urlManager->parseUrl($request);
            $routes = array('price_plans', 'payment_gateway_ext_jeeb/callback','payment_gateway_ext_jeeb/webhook');
            foreach ($routes as $route) {
                if (strpos($url, $route) === 0) {
                    Yii::app()->detachEventHandler('onBeginRequest', array($request, 'validateCsrfToken'));
                    Yii::app()->attachEventHandler('onBeginRequest', array($this, 'validateCsrfToken'));
                    break;
                }
            }
        }
        // hook into drop down list and add the jeeb option
        $hooks->addFilter('customer_price_plans_payment_methods_dropdown', array($this, '_registerGatewayInCustomerDropDown'));
    }

    
    // this is called by the customer app to process the payment
    // must be implemented by all payment gateways
    public function getPaymentHandler()
    {
        return Yii::createComponent(array(
            'class' => 'ext-payment-gateway-jeeb.customer.components.utils.JeebPaymentHandler',
        ));
    }
    
    // extension main model
    public function getExtModel()
    {
        if ($this->_extModel !== null) {
            return $this->_extModel;
        }
        
        $this->_extModel = new PaymentGatewayJeebExtModel();
        return $this->_extModel->setExtensionInstance($this)->populate();
    }
    // this replacement is needed to avoid csrf token validation and other errors
    public function validateCsrfToken()
    {
        Yii::app()->request->enableCsrfValidation = false;
    }
    //
    public function _registerGatewayInCustomerDropDown($paymentMethods)
    {
        if (isset($paymentMethods['jeeb'])) {
            return $paymentMethods;
        }
        $paymentMethods['jeeb'] = Yii::t('ext_payment_gateway_jeeb','CryptoCurrency Payment');
        return $paymentMethods;
    }
}