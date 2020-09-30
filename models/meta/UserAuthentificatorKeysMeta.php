<?php

namespace steroids\auth\models\meta;

use steroids\core\base\Model;
use \Yii;

/**
 * @property string $id
 * @property integer $userId
 * @property string $secretKey
 * @property string $authentificatorType
 */
abstract class UserAuthentificatorKeysMeta extends Model
{
    public static function tableName()
    {
        return 'user_authentificator_keys';
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
            [['secretKey', 'authentificatorType'], 'string', 'max' => 255],
        ];
    }

    public static function meta()
    {
        return array_merge(parent::meta(), [
            'id' => [
                'appType' => 'primaryKey',
                'isPublishToFrontend' => false
            ],
            'userId' => [
                'appType' => 'integer',
                'isPublishToFrontend' => false
            ],
            'secretKey' => [
                'label' => Yii::t('steroids', 'ключ для 2fa'),
                'isPublishToFrontend' => false
            ],
            'authentificatorType' => [
                'label' => Yii::t('steroids', 'тип аутентификации'),
                'isPublishToFrontend' => false
            ]
        ]);
    }
}
