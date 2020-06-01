<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class RegistrationFormMeta extends FormModel
{
    public $email;
    public $phone;
    public $login;
    public $password;
    public $passwordAgain;

    public function rules()
    {
        return [
            [['email', 'login'], 'string', 'max' => 255],
            ['email', 'email'],
            ['phone', 'string', 'max' => 32],
            [['password', 'passwordAgain'], 'string', 'min' => 1,'max' => 255],
        ];
    }

    public static function meta()
    {
        return [
            'email' => [
                'label' => Yii::t('steroids', 'Email'),
                'appType' => 'email'
            ],
            'phone' => [
                'label' => Yii::t('steroids', 'Телефон'),
                'appType' => 'phone',
                'isSortable' => false
            ],
            'login' => [
                'label' => Yii::t('steroids', 'Логин'),
                'isSortable' => false
            ],
            'password' => [
                'label' => Yii::t('steroids', 'Пароль'),
                'appType' => 'password'
            ],
            'passwordAgain' => [
                'label' => Yii::t('steroids', 'Повтор пароля'),
                'appType' => 'password'
            ]
        ];
    }
}
