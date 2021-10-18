<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\forms\meta\RecoveryPasswordFormMeta;
use steroids\auth\models\AuthConfirm;
use steroids\auth\UserInterface;
use steroids\auth\validators\CaptchaValidator;
use steroids\core\base\Model;
use steroids\notifier\exceptions\InvalidPhoneNumberException;
use Yii;

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
            ['login', 'filter', 'filter' => function ($value) {
                return mb_strtolower(trim($value));
            }],
            ['token', CaptchaValidator::class],

            ['login', function ($attribute) {
                $module = AuthModule::getInstance();
                $userClass = $module->userClass;

                // Find user by email/phone/login
                $attributes = array_map(
                    fn($attribute) => $module->getUserAttributeName($attribute),
                    $module->loginAvailableAttributes
                );
                $this->user = $userClass::findBy($this->$attribute, $attributes);

                if (!$this->user) {
                    $this->addError($attribute, Yii::t('steroids', 'Пользователь с указанным логином не найден'));
                }
            }],
        ]);
    }

    public function send()
    {
        if ($this->validate()) {
            $module = AuthModule::getInstance();
            $confirmAttribute = AuthAttributeTypeEnum::resolveNotifierByLogin($this->login);

            try {
                $this->confirm = $module->confirm($this->user, $confirmAttribute);
            } catch (InvalidPhoneNumberException $e) {
                $this->addError($confirmAttribute, InvalidPhoneNumberException::getDefaultMessage());
            }
        }
    }
}
