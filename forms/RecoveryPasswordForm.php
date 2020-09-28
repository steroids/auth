<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\forms\meta\RecoveryPasswordFormMeta;
use steroids\auth\models\AuthConfirm;
use steroids\auth\UserInterface;
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

    public function rules()
    {
        return array_merge(parent::rules(), [
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
                $attribute = strpos($this->login, '@') !== false ? AuthModule::ATTRIBUTE_EMAIL : AuthModule::ATTRIBUTE_PHONE;
                $this->confirm = $module->confirm($this->user, $attribute);
            }
            return true;
        }
        return false;
    }
}
