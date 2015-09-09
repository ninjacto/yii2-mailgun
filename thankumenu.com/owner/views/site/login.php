<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
?>

<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6 separator social-login-box">
                            <br /><br />
                            <a href="<?= $fbLoginUrl; ?>" class="btn facebook btn-block" role="button"><i class="pull-left fa fa-fw fa-facebook"></i> Log in using Facebook</a>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6 login-box">
                            <div class="input-group field-loginform-username required <?php if($model->hasErrors('username')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
                                <input id="loginform-username" name="LoginForm[username]" type="text" class="form-control text-input" placeholder="Username" required autofocus />
                            </div>
                            <?php if($model->hasErrors('username')):?><div class="help-block help-block-error "><?= $model->getFirstError('username');?></div><?php endif;?>
                            <div class="input-group field-loginform-password required <?php if($model->hasErrors('password')):?>has-error<?php endif;?>">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
                                <input id="loginform-password" name="LoginForm[password]" type="password" class="form-control text-input" placeholder="Password" required />
                            </div>
                            <?php if($model->hasErrors('password')):?><div class="help-block help-block-error "><?= $model->getFirstError('password');?></div><?php endif;?>
                            <p><?= Html::a('Forget password?', ['site/request-password-reset']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <?= $form->field($model, 'rememberMe')->checkbox() ?>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <?= Html::submitButton('<span class="btn-label"><i class="glyphicon glyphicon-ok"></i></span>Login', ['class' => 'btn btn-labeled btn-success', 'name' => 'login-button']) ?>
                            <?= Html::a('<span class="btn-label"><i class="glyphicon glyphicon-ok"></i></span>Signup',['/site/signup'], ['class' => 'btn btn-labeled btn-danger pull-right', 'name' => 'signup-button']) ?>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>