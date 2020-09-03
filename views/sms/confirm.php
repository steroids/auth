<?php

namespace steroids\views;

use yii\base\View;
use steroids\auth\UserInterface;
use steroids\notifier\NotifierMessage;
use steroids\auth\models\AuthConfirm;

/* @var $this View */
/* @var $message NotifierMessage */
/* @var $user UserInterface */
/* @var $confirm AuthConfirm */

echo \Yii::t('steroids', '{code} - проверочный код. {siteName}', [
    'siteName' => \Yii::$app->name,
    'code' => $confirm->code,
]);
