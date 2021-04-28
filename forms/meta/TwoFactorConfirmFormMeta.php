<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;
use \Yii;

abstract class TwoFactorConfirmFormMeta extends FormModel
{
    public ?string $code = null;
    public ?string $providerName = null;


    public function rules()
    {
        return [
            ...parent::rules(),
            [['code', 'providerName'], 'string', 'max' => 255],
            [['code', 'providerName'], 'required'],
        ];
    }

    public static function meta()
    {
        return [
            'code' => [
                'label' => Yii::t('steroids', 'Код/токен'),
                'isRequired' => true,
                'isSortable' => false
            ],
            'providerName' => [
                'label' => Yii::t('steroids', 'Название провайдера'),
                'isRequired' => true,
                'isSortable' => false
            ]
        ];
    }
}
