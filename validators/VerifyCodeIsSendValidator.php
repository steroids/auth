<?php


namespace steroids\auth\validators;

use steroids\auth\AuthModule;
use steroids\auth\models\AuthConfirm;
use steroids\auth\UserInterface;
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

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @throws \yii\base\Exception
     */
    public function validateAttribute($model, $attribute)
    {
        /** @var UserInterface $userClass */
        $userClass = \Yii::$app->user->identityClass;
        $module = AuthModule::getInstance();
        $user = $userClass::findBy($model->$attribute, [$module->getUserAttributeName($attribute)]);

        if (!$user) {
            throw new \Exception('User not found');
        }

        $confirmAlreadySend = AuthConfirm::find()
            ->where([
                'type' => $attribute,
                'value' => $user->getAttribute($attribute),
                'userId' => $user->getId(),
            ])
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->one();

        if($confirmAlreadySend){
            $this->addError($model, $attribute, $this->message);
        }
    }
}