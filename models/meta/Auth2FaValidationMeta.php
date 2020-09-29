<?php

namespace steroids\auth\models\meta;

use steroids\core\base\Model;
use \Yii;

/**
 * @property string $id
 * @property string $createTime
 * @property string $authentificatorType
 */
abstract class Auth2FaValidationMeta extends Model
{
    public static function tableName()
    {
        return 'auth_2fa_validation';
    }

    public function fields()
    {
        return [
        ];
    }

    public function rules()
    {
        return [
            ...parent::rules(),
            ['createTime', 'date', 'format' => 'php:Y-m-d H:i:s'],
            ['authentIficatorType', 'string', 'max' => 255],
        ];
    }

    public static function meta()
    {
        return array_merge(parent::meta(), [
            'id' => [
                'label' => Yii::t('app', 'ID'),
                'appType' => 'primaryKey',
                'isPublishToFrontend' => false
            ],
            'createTime' => [
                'label' => Yii::t('app', 'Время создания'),
                'appType' => 'dateTime',
                'isPublishToFrontend' => false
            ],
            'authentificatorType' => [
                'isPublishToFrontend' => false
            ]
        ]);
    }
}
