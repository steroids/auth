<?php

namespace steroids\auth\forms\meta;

use \Yii;
use steroids\core\base\FormModel;

abstract class ProviderLoginFormMeta extends FormModel
{
    public $name;

    public function rules()
    {
        return [
            ['name', 'string'],
            ['name', 'required'],
        ];
    }

    public static function meta()
    {
        return [
            'name' => [
                'label' => Yii::t('steroids', 'Имя провайдера'),
                'appType' => 'string',
                'isRequired' => true,
            ],
        ];
    }
}
