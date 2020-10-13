<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\enums\AuthAttributeTypes;
use steroids\auth\forms\meta\SocialEmailFormMeta;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthSocial;
use steroids\auth\UserInterface;
use steroids\auth\validators\VerifyCodeIsSendValidator;

class SocialEmailForm extends SocialEmailFormMeta
{
    /**
     * @var AuthSocial
     */
    public $social;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        /** @var UserInterface $userClass */
        $userClass = \Yii::$app->user->identityClass;

        return array_merge(parent::rules(), [
            ['email', 'filter', 'filter' => function($value) {
                return mb_strtolower(trim($value));
            }],
            ['email', VerifyCodeIsSendValidator::class],
            ['email', 'unique', 'targetClass' => $userClass],
            ['uid', function($attribute) {
                $this->social = AuthSocial::findOne([
                    'uid' => $this->uid,
                    'userId' => null,
                ]);
                if (!$this->social) {
                    $this->addError($attribute, \Yii::t('steroids', 'Код авторизации не найден'));
                }
            }],
        ]);
    }

    public function send()
    {
        if ($this->validate()) {
            /** @var UserInterface $userClass */
            $userClass = \Yii::$app->user->identityClass;

            $module = AuthModule::getInstance();
            $user = $userClass::findBy($this->email, [$module->getUserAttributeName(AuthAttributeTypes::EMAIL)]);
            if ($user) {
                $module->confirm($user, AuthAttributeTypes::EMAIL);
            }
            return true;
        }
        return false;
    }

}
