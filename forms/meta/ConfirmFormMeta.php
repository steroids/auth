<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class ConfirmFormMeta extends FormModel
{
    public $email;
    public $code;
    public $phone;

    public function rules()
    {
        return [
            [['email', 'code'], 'string', 'max' => 255],
            ['email', 'email'],
            ['code', 'required'],
            ['phone', 'string', 'max' => 32],
        ];
    }

    public static function meta()
    {
        return [
            'email' => [
                'label' => Yii::t('steroids', 'Email'),
                'appType' => 'email'
            ],
            'code' => [
                'label' => Yii::t('steroids', 'Код'),
                'isRequired' => true
            ],
            'phone' => [
                'label' => Yii::t('steroids', 'Телефон'),
                'appType' => 'phone',
                'isSortable' => false
            ]
        ];
    }
}
