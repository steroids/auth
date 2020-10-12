<?php


namespace steroids\auth\validators;


use steroids\auth\AuthModule;
use steroids\auth\models\AuthConfirm;
use yii\validators\Validator;

class CodeAlreadySendValidator extends Validator
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
        $authConfirmAttributes = [
            'type' => $attribute,
            'value' => \Yii::$app->user->getAttribute($attribute),
            'userId' => \Yii::$app->user->getId(),
        ];

        $confirmAlreadySend = AuthConfirm::find()
            ->where($authConfirmAttributes)
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->one();

        if($confirmAlreadySend){
            $this->addError($model, $attribute, $this->message);
        }
    }
}