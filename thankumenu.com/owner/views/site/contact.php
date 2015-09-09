<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \owner\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="site-contact">
        <div class="row">
            <div class="col-md-push-2 col-md-8 col-sm-12 claim-form">
                <div class="claim-form">
                    <?php $form = ActiveForm::begin([
                        'id' => 'contact-form',
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'template' => "{label} {beginWrapper} {input} {hint}\n{error}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-4',
                                'offset' => 'col-sm-offset-4',
                                'wrapper' => 'col-sm-8',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                        'options' => ['class' => 'form-horizontal'],
                    ]); ?>
                    <ul class="easyWizardSteps">
                        <li class="current">
                            Contact
                        </li>
                    </ul>
                    <section id="step-3" class="step" data-step-title="Billing Information">
                        <?= $form->field($model, 'name') ?>

                        <?= $form->field($model, 'email') ?>

                        <?= $form->field($model, 'subject') ?>

                        <?= $form->field($model, 'body')->textArea(['rows' => 6]) ?>

                        <?= $form->field($model, 'verifyCode')->widget(Captcha::className(), [
                            'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-6">{input}</div></div>',
                        ]) ?>
                    </section>
                    <div class="easyWizardButtons" style="clear: both;">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>

    </div>

<?php
$this->registerCssFile(
    \yii\helpers\Url::base(true) . '/css/claim-form.css',
    [
        'depends' => [
            \yii\web\JqueryAsset::className(),
            \yii\bootstrap\BootstrapAsset::className()
        ],
        'position' => yii\web\View::POS_HEAD
    ]);
?>