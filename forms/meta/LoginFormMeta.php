<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class LoginFormMeta extends FormModel
{
    public $login;
    public $password;

    public function rules()
    {
        return [
            ['login', 'string', 'max' => 255],
            ['login', 'required'],
            ['password', 'string', 'min' => 1,'max' => 255],
        ];
    }

    public static function meta()
    {
        return [
            'login' => [
                'label' => Yii::t('steroids', 'Логин'),
                'isRequired' => true
            ],
            'password' => [
                'label' => Yii::t('steroids', 'Пароль'),
                'appType' => 'password'
            ]
        ];
    }
}
