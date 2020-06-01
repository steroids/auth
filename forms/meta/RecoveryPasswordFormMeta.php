<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class RecoveryPasswordFormMeta extends FormModel
{
    public $login;

    public function rules()
    {
        return [
            ['login', 'string', 'max' => 255],
            ['login', 'required'],
        ];
    }

    public static function meta()
    {
        return [
            'login' => [
                'label' => Yii::t('steroids', 'Логин'),
                'isRequired' => true
            ]
        ];
    }
}
