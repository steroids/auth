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

$message->title = \Yii::t('steroids', 'Проверочный код - {code}. {siteName}', [
    'siteName' => \Yii::$app->name,
    'code' => $confirm->code,
]);
?>

<p>
    <?= \Yii::t('steroids', 'Ваш проверочный код:')?> <?= $confirm->code ?>
</p>
<h2>

</h2>
<br />
<p>
    <?= \Yii::t('steroids', 'Код действителен до {date}', [
        'date' => \Yii::$app->formatter->asDatetime($confirm->expireTime),
    ])?>
</p>
