<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class ConfirmFormMeta extends FormModel
{
    public $login;
    public $code;

    public function rules()
    {
        return [
            [['login', 'code'], 'string', 'max' => 255],
            [['login', 'code'], 'required'],
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
            ]
        ];
    }
}
