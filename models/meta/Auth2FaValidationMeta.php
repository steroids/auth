<?php

namespace steroids\auth\models\meta;

use steroids\core\base\Model;
use \Yii;

/**
 * @property string $id
 * @property string $createTime
 * @property string $authentificatorType
 * @property integer $userId
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
            ['authentificatorType', 'string', 'max' => 255],
            ['userId', 'integer'],
        ];
    }

    public static function meta()
    {
        return array_merge(parent::meta(), [
            'id' => [
                'label' => Yii::t('steroids', 'ID'),
                'appType' => 'primaryKey',
                'isPublishToFrontend' => false
            ],
            'createTime' => [
                'label' => Yii::t('steroids', 'Время создания'),
                'appType' => 'dateTime',
                'isPublishToFrontend' => false
            ],
            'authentificatorType' => [
                'isPublishToFrontend' => false
            ],
            'userId' => [
                'label' => Yii::t('steroids', 'id пользователя'),
                'appType' => 'integer',
                'isPublishToFrontend' => false
            ]
        ]);
    }
}
