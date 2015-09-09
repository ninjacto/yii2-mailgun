<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \owner\models\SignupForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = 'Signup';
?>
<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
                </div>
                <?php $form = ActiveForm::begin([
                    'id' => 'edit-user-form',
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
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 signup-box">
                            <p>Please fill the following form and hit the Signup button. (all fields are mandatory)</p>
                            <div class="input-group field-signupform-name required <?php if($model->hasErrors('name')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="fa fa-fw fa-user"></span></span>
                                <input id="signupform-name" name="SignupForm[name]" type="text" class="form-control text-input" value="<?= $model->name;?>" placeholder="Full Name" required autofocus />
                            </div>
                            <?php if($model->hasErrors('name')):?><div class="help-block help-block-error "><?= $model->getFirstError('name');?></div><?php endif;?>
                            <div class="input-group field-signupform-address required <?php if($model->hasErrors('address')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="fa fa-fw fa-map-marker"></span></span>
                                <input id="signupform-address" name="SignupForm[address]" type="text" class="form-control text-input" value="<?= $model->address;?>" placeholder="address" required autofocus />
                            </div>
                            <?php if($model->hasErrors('address')):?><div class="help-block help-block-error "><?= $model->getFirstError('address');?></div><?php endif;?>
                            <div class="input-group field-signupform-phone required <?php if($model->hasErrors('phone')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="fa fa-fw fa-phone"></span></span>
                                <input id="signupform-phone" name="SignupForm[phone]" type="text" class="form-control text-input" value="<?= $model->phone;?>" placeholder="Phone Number" required autofocus />
                            </div>
                            <?php if($model->hasErrors('phone')):?><div class="help-block help-block-error "><?= $model->getFirstError('phone');?></div><?php endif;?>
                            <div class="input-group field-signupform-email required <?php if($model->hasErrors('email')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="fa fa-fw fa-envelope"></span></span>
                                <input id="signupform-email" name="SignupForm[email]" type="email" class="form-control text-input" value="<?= $model->email;?>" placeholder="Email" required autofocus />
                            </div>
                            <?php if($model->hasErrors('email')):?><div class="help-block help-block-error "><?= $model->getFirstError('email');?></div><?php endif;?>
                            <div class="input-group field-signupform-password required <?php if($model->hasErrors('password')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="fa fa-fw fa-lock"></span></span>
                                <input id="signupform-password" name="SignupForm[password]" type="password" class="form-control text-input" placeholder="Password" required />
                            </div>
                            <?php if($model->hasErrors('password')):?><div class="help-block help-block-error "><?= $model->getFirstError('password');?></div><?php endif;?>
                            <div class="input-group field-signupform-password_repeat required <?php if($model->hasErrors('password_repeat')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="fa fa-fw fa-lock"></span></span>
                                <input id="signupform-password_repeat" name="SignupForm[password_repeat]" type="password" class="form-control text-input" placeholder="Repeat Password" required />
                            </div>
                            <?php if($model->hasErrors('password_repeat')):?><div class="help-block help-block-error "><?= $model->getFirstError('password_repeat');?></div><?php endif;?>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-xs-12">
                            <?= Html::a('<span class="btn-label"><i class="glyphicon glyphicon-ok"></i></span>Login',['/site/login'], ['class' => 'btn btn-labeled btn-success pull-left', 'name' => 'signup-button']) ?>
                            <?= Html::submitButton('<span class="btn-label"><i class="glyphicon glyphicon-ok"></i></span>Signup', ['class' => 'btn btn-labeled btn-danger pull-right', 'name' => 'login-button']) ?>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>