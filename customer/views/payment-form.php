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
if (isset($result['url'])) {
    echo CHtml::form($result['url'], 'get', array(
        'id'=>'jeeb-hidden-form',
        'data-order' => Yii::app()->createUrl('price_plans/order'),
    ));
    echo CHtml::hiddenField('custom', $customVars);
    ?>
    <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
        Our service supports cryptocurrency payments.<br/>
        You can pay with cryptocurrency as well as other payment methods but we encourage you to use cryptocurrency because we are into digital ages. <br/>
        <?php echo Yii::t('ext_payment_gateway_jeeb','You will be redirected to pay securely on jeeb.io website!').'<br />'; ?>
    </p>
    <p>
        <button class="btn btn-success pull-right"><i
                    class="fa fa-credit-card"></i> <?php echo Yii::t('ext_payment_gateway_jeeb', 'Submit payment'); ?></button>
    </p>

    <?php

    echo CHtml::endForm();
} else {
    echo 'Error during payment, plz try again later: ' . CHtml::encode(serialize($result['error']));
}

?>

