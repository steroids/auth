<?php

namespace steroids\auth\models\meta;

use steroids\core\base\Model;
use steroids\core\behaviors\TimestampBehavior;
use \Yii;

/**
 * @property string $id
 * @property integer $userId
 * @property string $providerName
 * @property string $providerSecret
 * @property string $createTime
 * @property string $expireTime
 * @property boolean $isConfirmed
 */
abstract class AuthTwoFactorMeta extends Model
{
    public static function tableName()
    {
        return 'auth_two_factor';
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
            ['userId', 'integer'],
            ['providerName', 'string', 'max' => 255],
            ['providerSecret', 'string'],
            ['expireTime', 'date', 'format' => 'php:Y-m-d H:i:s'],
            ['isConfirmed', 'steroids\\core\\validators\\ExtBooleanValidator'],
        ];
    }

    public function behaviors()
    {
        return [
            ...parent::behaviors(),
            TimestampBehavior::class,
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
            'userId' => [
                'label' => Yii::t('steroids', 'Пользователь'),
                'appType' => 'integer',
                'isPublishToFrontend' => false
            ],
            'providerName' => [
                'label' => Yii::t('steroids', 'Название провайдера'),
                'isPublishToFrontend' => false
            ],
            'providerSecret' => [
                'label' => Yii::t('steroids', 'Секретный код/ключ'),
                'appType' => 'text',
                'isPublishToFrontend' => false
            ],
            'createTime' => [
                'label' => Yii::t('steroids', 'Время создания'),
                'appType' => 'autoTime',
                'isPublishToFrontend' => false,
                'touchOnUpdate' => false
            ],
            'expireTime' => [
                'label' => Yii::t('steroids', 'Действителен до'),
                'appType' => 'dateTime',
                'isPublishToFrontend' => false
            ],
            'isConfirmed' => [
                'label' => Yii::t('steroids', 'Подтвержден?'),
                'appType' => 'boolean',
                'isPublishToFrontend' => false
            ]
        ]);
    }
}
