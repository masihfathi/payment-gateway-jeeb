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

class PaymentGatewayJeebExtModel extends FormModel
{

    const STATUS_ENABLED = 'enabled';

    const STATUS_DISABLED = 'disabled';

    const USD = 'USD';

    const EURO = 'EUR';

    protected $_extensionInstance;

    public $merchant_code;

    public $base_currency_id = 'USD';

    public $status = 'disabled';

    public $sort_order = 2;

    public function rules()
    {
        $rules = array(
            array('merchant_code', 'safe'),
            array('merchant_code, status, sort_order,base_currency_id', 'required'),
            array('status', 'in', 'range' => array_keys($this->getStatusesDropDown())),
            array('base_currency_id', 'in', 'range' => array_keys($this->getCurrencyDropDown())),
            array('sort_order', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 999),
            array('sort_order', 'length', 'min' => 1, 'max' => 3),
        );

        return CMap::mergeArray($rules, parent::rules());
    }
    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array('merchant_code', 'status', 'sort_order','base_currency_id');
        foreach ($attributes as $name) {
            $extension->setOption($name, $this->$name);
        }
        return $this;
    }

    public function populate()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array('merchant_code', 'status', 'sort_order','base_currency_id');
        foreach ($attributes as $name) {
            $this->$name = $extension->getOption($name, $this->$name);
        }
        return $this;
    }

    public function attributeLabels()
    {
        $labels = array(
            'merchant_code'  => Yii::t('ext_payment_gateway_jeeb','Merchant Code'),
            'base_currency_id' => Yii::t( 'ext_payment_gateway_jeeb', 'Base Currency Id' ),
            'status'      => Yii::t('app', 'Status'),
            'sort_order'  => Yii::t('app', 'Sort order'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array();
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'merchant_code'       => Yii::t('ext_payment_gateway_jeeb','Jeeb Merchent Code'),
            'base_currency_id'       => Yii::t('ext_payment_gateway_jeeb','Base Currency Id'),
            'status'      => Yii::t('ext_payment_gateway_jeeb','Whether this gateway is enabled and can be used for payments processing'),
            'sort_order'  => Yii::t('ext_payment_gateway_jeeb','The sort order for this gateway'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function getStatusesDropDown()
    {
        return array(
            self::STATUS_DISABLED   => Yii::t('app', 'Disabled'),
            self::STATUS_ENABLED    => Yii::t('app', 'Enabled'),
        );
    }
    public function getCurrencyDropDown()
    {
        return array(
            self::USD   => Yii::t('ext_payment_gateway_jeeb','USD'),
            self::EURO    => Yii::t('ext_payment_gateway_jeeb','EUR'),
        );        
    }

    public function getSortOrderDropDown()
    {
        $options = array();
        for ($i = 0; $i < 100; ++$i) {
            $options[$i] = $i;
        }
        return $options;
    }


    public function setExtensionInstance($instance)
    {
        $this->_extensionInstance = $instance;
        return $this;
    }

    public function getExtensionInstance()
    {
        if ($this->_extensionInstance !== null) {
            return $this->_extensionInstance;
        }
        return $this->_extensionInstance = Yii::app()->extensionsManager->getExtensionInstance('payment-gateway-jeeb');
    }
}
