<?php

namespace steroids\auth\forms\meta;

use steroids\core\base\FormModel;

abstract class Validate2FaCodeMeta extends FormModel
{
    public ?string $login = null;
    public ?string $code = null;


    public function rules()
    {
        return [
            ...parent::rules(),
            [['login', 'code'], 'string', 'max' => 255],
            ['code', 'required'],
        ];
    }

    public static function meta()
    {
        return [
            'login' => [
                'isSortable' => false
            ],
            'code' => [
                'isRequired' => true,
                'isSortable' => false
            ]
        ];
    }
}
