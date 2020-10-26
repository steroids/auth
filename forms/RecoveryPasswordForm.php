<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\exceptions\ConfirmCodeAlreadySentException;
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
            ['login', 'filter', 'filter' => function($value) {
                return mb_strtolower(trim($value));
            }],
            ['token', ReCaptchaValidator::class]
        ]);
    }

    public function send()
    {
        if (!$this->validate()) {
            return false;
        }

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

            $confirmAttribute = AuthModule::getNotifierAttributeTypeFromLogin($this->login);

            try {
                $this->confirm = $module->confirm($this->user, $confirmAttribute);
            } catch (ConfirmCodeAlreadySentException $e) {
                $this->addError($module->registrationMainAttribute, ConfirmCodeAlreadySentException::getDefaultMessage());
            }
        }
        return true;
    }
}
