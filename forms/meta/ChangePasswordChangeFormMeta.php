<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class ChangePasswordChangeFormMeta extends FormModel
{
    public $password;
    public $passwordAgain;

    public function rules()
    {
        return [
            [['password', 'passwordAgain'], 'required'],
            [['password', 'passwordAgain'], 'string', 'min' => 1,'max' => 255],
        ];
    }

    public static function meta()
    {
        return [
            'password' => [
                'label' => Yii::t('steroids', 'Новый пароль'),
                'appType' => 'password',
                'isRequired' => true
            ],
            'passwordAgain' => [
                'label' => Yii::t('steroids', 'Повтор пароля'),
                'appType' => 'password',
                'isRequired' => true
            ]
        ];
    }
}
