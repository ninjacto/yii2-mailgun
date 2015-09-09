<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-owner',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'owner\controllers',
    'components' => [
        'user' => [
            'identityClass' => 'common\models\ParseUser',
            'enableAutoLogin' => true,
            'loginUrl'  => ['/site/login']
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'assetManager' => [
            'linkAssets' => true,
            'appendTimestamp' => true,
            'bundles' => [
                'owner\assets\FontAwesomeAsset' => [
                    'sourcePath' => '@bower/fontawesome',   // do not publish the bundle
                    'css' => [
                        YII_ENV_DEV ? 'css/font-awesome.css' : 'css/font-awesome.min.css',
                    ]
                ]
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '.html',
            'rules' => [
                'dashboard'             => 'site/index',
                'login'                 => 'site/login',
                'logout'                => 'site/logout',
                'signup'                => 'site/signup',
                'profile'               => 'site/edit-user',
                'unsubscribe/<id:\w+>/' => 'site/delete',
                'change-subscription/<id:\w+>' => 'site/update',
                'credit-card'           => 'site/credit-card',
                'claim/step-1'          => 'site/claim-step-one',
                'claim/step-2'          => 'site/claim-step-two',
                'claim/step-3/<plan>'   => 'site/claim-step-three',
                'claim/step-4'          => 'site/claim-step-four',
            ],
        ],
    ],
    'params' => $params,
];
