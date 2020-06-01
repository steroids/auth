<?php

define('STEROIDS_ROOT_DIR', realpath(__DIR__ . '/../../..'));
define('YII_ENV', 'test');

$config = require __DIR__ . '/../../../bootstrap.php';
$config = \yii\helpers\ArrayHelper::merge($config, [
    'components' => [
        'user' => [
            'class' => '\steroids\core\tests\mocks\TestWebUser',
        ],
    ],
]);

new \steroids\core\base\ConsoleApplication($config);
