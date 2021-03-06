<?php

namespace steroids\auth\models\meta;

use steroids\core\base\Model;
use steroids\core\behaviors\TimestampBehavior;
use \Yii;

/**
 * @property string $id
 * @property integer $userId
 * @property string $value
 * @property string $code
 * @property boolean $isConfirmed
 * @property string $createTime
 * @property string $updateTime
 * @property string $expireTime
 * @property string $type
 * @property string $uid
 * @property boolean $isReused
 * @property integer $prevId
 */
abstract class AuthConfirmMeta extends Model
{
    public static function tableName()
    {
        return 'auth_confirms';
    }

    public function fields()
    {
        return [
            'value',
            'isConfirmed',
            'createTime',
            'expireTime',
            'type',
            'uid',
            'isReused',
        ];
    }

    public function rules()
    {
        return [
            ...parent::rules(),
            [['userId', 'prevId'], 'integer'],
            [['userId', 'value', 'code'], 'required'],
            ['value', 'string', 'max' => 255],
            ['code', 'string', 'max' => '32'],
            [['isConfirmed', 'isReused'], 'steroids\\core\\validators\\ExtBooleanValidator'],
            ['expireTime', 'date', 'format' => 'php:Y-m-d H:i:s'],
            ['type', 'string', 'max' => '10'],
            ['uid', 'string', 'max' => '36'],
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
                'appType' => 'primaryKey'
            ],
            'userId' => [
                'label' => Yii::t('steroids', 'Пользователь'),
                'appType' => 'integer',
                'isRequired' => true
            ],
            'value' => [
                'label' => Yii::t('steroids', 'Логин'),
                'isRequired' => true,
                'isPublishToFrontend' => true
            ],
            'code' => [
                'label' => Yii::t('steroids', 'Код'),
                'isRequired' => true,
                'isPublishToFrontend' => false,
                'stringLength' => '32'
            ],
            'isConfirmed' => [
                'label' => Yii::t('steroids', 'Подтвержден?'),
                'appType' => 'boolean',
                'isPublishToFrontend' => true
            ],
            'createTime' => [
                'label' => Yii::t('steroids', 'Время отправки'),
                'appType' => 'autoTime',
                'isPublishToFrontend' => true,
                'touchOnUpdate' => false
            ],
            'updateTime' => [
                'label' => Yii::t('steroids', 'Время обновления'),
                'appType' => 'autoTime',
                'touchOnUpdate' => true
            ],
            'expireTime' => [
                'label' => Yii::t('steroids', 'Действителен до'),
                'appType' => 'dateTime',
                'isPublishToFrontend' => true
            ],
            'type' => [
                'label' => Yii::t('steroids', 'Тип (емаил или телефон)'),
                'isPublishToFrontend' => true,
                'stringLength' => '10'
            ],
            'uid' => [
                'label' => Yii::t('steroids', 'UID'),
                'isPublishToFrontend' => true,
                'stringLength' => '36'
            ],
            'isReused' => [
                'label' => Yii::t('steroids', 'Используется повторно'),
                'appType' => 'boolean',
                'isPublishToFrontend' => true
            ],
            'prevId' => [
                'label' => Yii::t('steroids', 'Предыдущее подтверждение'),
                'appType' => 'integer',
                'isPublishToFrontend' => false,
            ],
        ]);
    }
}
