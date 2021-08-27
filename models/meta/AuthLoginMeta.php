<?php

namespace steroids\auth\models\meta;

use steroids\core\base\Model;
use steroids\core\behaviors\TimestampBehavior;
use \Yii;
use yii\db\ActiveQuery;
use steroids\auth\models\AuthSocial;

/**
 * @property string $id
 * @property integer $userId
 * @property integer $authId
 * @property string $accessToken
 * @property string $wsToken
 * @property string $ipAddress
 * @property string $location
 * @property string $userAgent
 * @property string $createTime
 * @property string $expireTime
 * @property-read AuthSocial $auth
 */
abstract class AuthLoginMeta extends Model
{
    public static function tableName()
    {
        return 'auth_logins';
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
            [['userId', 'authId'], 'integer'],
            [['userId', 'accessToken', 'ipAddress', 'userAgent'], 'required'],
            [['accessToken', 'ipAddress'], 'string', 'max' => '64'],
            ['wsToken', 'string', 'max' => '16'],
            ['location', 'string', 'max' => 255],
            ['userAgent', 'string', 'max' => '2000'],
            ['expireTime', 'date', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    public function behaviors()
    {
        return [
            ...parent::behaviors(),
            TimestampBehavior::class,
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAuth()
    {
        return $this->hasOne(AuthSocial::class, ['id' => 'authId']);
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
            'authId' => [
                'label' => Yii::t('steroids', 'Социальный профиль'),
                'appType' => 'integer'
            ],
            'accessToken' => [
                'label' => Yii::t('steroids', 'Access Token'),
                'isRequired' => true,
                'stringLength' => '64'
            ],
            'wsToken' => [
                'label' => Yii::t('steroids', 'Ws Token'),
                'stringLength' => '16'
            ],
            'ipAddress' => [
                'label' => Yii::t('steroids', 'IP адрес'),
                'isRequired' => true,
                'stringLength' => '64'
            ],
            'location' => [
                'label' => Yii::t('steroids', 'Месторасположение')
            ],
            'userAgent' => [
                'label' => Yii::t('steroids', 'Браузер'),
                'isRequired' => true,
                'stringLength' => '2000'
            ],
            'createTime' => [
                'label' => Yii::t('steroids', 'Время входа'),
                'appType' => 'autoTime',
                'touchOnUpdate' => false
            ],
            'expireTime' => [
                'label' => Yii::t('steroids', 'Действителен до'),
                'appType' => 'dateTime'
            ]
        ]);
    }
}
