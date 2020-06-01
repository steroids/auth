<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class RecoveryPasswordConfirmFormMeta extends FormModel
{
    public $login;
    public $code;
    public $newPassword;
    public $newPasswordAgain;

    public function rules()
    {
        return [
            [['login', 'code'], 'string', 'max' => 255],
            [['login', 'code', 'newPassword', 'newPasswordAgain'], 'required'],
            [['newPassword', 'newPasswordAgain'], 'string', 'min' => 1,'max' => 255],
        ];
    }

    public static function meta()
    {
        return [
            'login' => [
                'label' => Yii::t('steroids', 'Логин'),
                'isRequired' => true
            ],
            'code' => [
                'label' => Yii::t('steroids', 'Код'),
                'isRequired' => true
            ],
            'newPassword' => [
                'label' => Yii::t('steroids', 'Новый пароль'),
                'appType' => 'password',
                'isRequired' => true
            ],
            'newPasswordAgain' => [
                'label' => Yii::t('steroids', 'Повтор пароля'),
                'appType' => 'password',
                'isRequired' => true
            ]
        ];
    }
}
