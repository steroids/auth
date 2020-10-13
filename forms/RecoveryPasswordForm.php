<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\forms\meta\RecoveryPasswordFormMeta;
use steroids\auth\models\AuthConfirm;
use steroids\auth\UserInterface;
use steroids\auth\validators\VerifyCodeIsSendValidator;
use steroids\auth\validators\ReCaptchaValidator;
use steroids\core\base\Model;

class RecoveryPasswordForm extends RecoveryPasswordFormMeta
{
    /**
     * @var UserInterface|Model
     */
    public $user;

    /**
     * @var AuthConfirm
     */
    public $confirm;

    /**
     * @var string
     */
    public $token;

    public function fields()
    {
        return [
            'confirm' => [
                'uid',
                'type',
                'value',
                'expireTime',
            ],
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['login', VerifyCodeIsSendValidator::class],
            ['login', 'filter', 'filter' => function($value) {
                return mb_strtolower(trim($value));
            }],
            ['token', ReCaptchaValidator::class]
        ]);
    }

    public function send()
    {
        if ($this->validate()) {
            /** @var UserInterface $userClass */
            $userClass = \Yii::$app->user->identityClass;
            $module = AuthModule::getInstance();

            // Find user by email/phone/login
            $attributes = array_map(
                fn($attribute) => $module->getUserAttributeName($attribute),
                $module->loginAvailableAttributes
            );
            $this->user = $userClass::findBy($this->login, $attributes);

            if ($this->user) {
                $this->confirm = $module->confirm(
                    $this->user,
                    AuthModule::getNotifierAttributeTypeFromLogin($this->login)
                );
            }
            return true;
        }
        return false;
    }
}
