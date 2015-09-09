<?php

/* @var $this yii\web\View */

use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Owners Area, Thanku Menu';
?>
<div class="site-index">

    <div class="body-content">
        <h1>Restaurants</h1>
        <div class="row">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'], // <-- here
                    'name',
                    [
                        'attribute' => 'phone',
                        'contentOptions'=>['style'=>'width: 140px;']
                    ],
                    'address',
                    'plan',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template'=>'{update}&nbsp;&nbsp;&nbsp;{delete}',
                        'contentOptions' => [
                            'class'=>'text-center',
                            'style'=>'width: 60px;'
                        ],
                        'buttons' => [
                            'update' => function ($url, $model) {
                                $url = Url::to(['/site/update','id'=>$model['objectId']]);
                                $options = [
                                    'title' => Yii::t('yii', 'Update'),
                                    'aria-label' => Yii::t('yii', 'Update'),
                                    'data-pjax' => '0',
                                ];
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, $options);
                            },
                            'delete' => function ($url, $model) {
                                $url = Url::toRoute(['/site/delete', 'id' => $model['objectId']]);
                                $options = [
                                    'title' => Yii::t('yii', 'Delete'),
                                    'aria-label' => Yii::t('yii', 'Delete'),
                                    'data-confirm' => Yii::t('yii', 'Are you sure you want to unsubscribe this restaurant?'),
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                ];
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                            }
                        ],
                    ],
                ],
            ]) ?>

            <?= Html::a('Add Restaurant', ['/site/claim-step-one'],['class'=>'btn btn-default'])?>
        </div>

    </div>
</div>
