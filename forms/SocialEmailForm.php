<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\exceptions\ConfirmCodeAlreadySentException;
use steroids\auth\forms\meta\SocialEmailFormMeta;
use steroids\auth\models\AuthSocial;
use steroids\auth\UserInterface;

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
        if (!$this->validate()) {
            return false;
        }

        /** @var UserInterface $userClass */
        $userClass = \Yii::$app->user->identityClass;

        $module = AuthModule::getInstance();
        $user = $userClass::findBy($this->email, [$module->getUserAttributeName(AuthAttributeTypeEnum::EMAIL)]);
        if ($user) {
            try {
                $module->confirm($user, AuthAttributeTypeEnum::EMAIL);
            } catch (ConfirmCodeAlreadySentException $e) {
                $this->addError($module->registrationMainAttribute, ConfirmCodeAlreadySentException::getDefaultMessage());
            }
        }
        return true;
    }

}
