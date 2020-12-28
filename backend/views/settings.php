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
?>

<?php $form = $this->beginWidget('CActiveForm');?>

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('glyphicon-transfer') .  $pageHeading;?>
            </h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
         <div class="row">
             <div class="col-lg-6">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'merchant_code');?>
                     <?php echo $form->textField($model, 'merchant_code', $model->getHtmlOptions('merchant_code')); ?>
                     <?php echo $form->error($model, 'merchant_code');?>
                 </div>
             </div>
             <div class="col-lg-6">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'base_currency_id');?>
                     <?php echo $form->dropDownList($model, 'base_currency_id', $model->getCurrencyDropDown(), $model->getHtmlOptions('base_currency_id')); ?>
                     <?php echo $form->error($model, 'base_currency_id');?>
                 </div>
             </div>
         </div>
         <div class="row">
             <div class="col-lg-6">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'status');?>
                     <?php echo $form->dropDownList($model, 'status', $model->getStatusesDropDown(), $model->getHtmlOptions('status')); ?>
                     <?php echo $form->error($model, 'status');?>
                 </div>
             </div>
             <div class="col-lg-6">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'sort_order');?>
                     <?php echo $form->dropDownList($model, 'sort_order', $model->getSortOrderDropDown(), $model->getHtmlOptions('sort_order', array('data-placement' => 'left'))); ?>
                     <?php echo $form->error($model, 'sort_order');?>
                 </div>
             </div>
         </div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <button type="submit" class="btn btn-primary btn-submit"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
        </div>
        <div class="clearfix"><!-   - --></div>
    </div>
</div>
<?php $this->endWidget(); ?>