<?php


namespace steroids\auth\validators;

use steroids\auth\models\AuthConfirm;
use yii\validators\Validator;

class VerifyCodeIsSendValidator extends Validator
{

    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = \Yii::t('steroids', 'Код уже был отправлен');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        $confirmAlreadySend = AuthConfirm::find()
            ->where([
                'type' => $attribute,
                'value' => \Yii::$app->user->getAttribute($attribute),
                'userId' => \Yii::$app->user->getId(),
            ])
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->one();

        if($confirmAlreadySend){
            $this->addError($model, $attribute, $this->message);
        }
    }
}