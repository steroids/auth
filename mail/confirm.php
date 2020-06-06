<?php

namespace steroids\views;

use steroids\auth\UserInterface;
use yii\web\View;
use yii\mail\BaseMessage;
use steroids\auth\models\AuthConfirm;

/* @var $this View */
/* @var $message BaseMessage */
/* @var $user UserInterface */
/* @var $confirm AuthConfirm */

$message->setSubject(\Yii::t('steroids', 'Проверочный код - {code}. {siteName}', [
    'siteName' => \Yii::$app->name,
    'code' => $confirm->code,
]));
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
