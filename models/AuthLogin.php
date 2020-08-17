<?php

namespace steroids\auth\models;

use steroids\auth\AuthModule;
use steroids\auth\models\meta\AuthLoginMeta;
use steroids\auth\UserInterface;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\Request;

/**
 * @property-read UserInterface $user
 * @property-read bool $isExpired
 */
class AuthLogin extends AuthLoginMeta
{
    const ACCESS_TOKEN_LENGTH = 64;
    const WS_TOKEN_LENGTH = 16;
    const LOGIN_DURATION_DAYS = 90; // 90 days

    /**
     * @inheritDoc
     */
    public static function instantiate($row)
    {
        return AuthModule::instantiateClass(static::class, $row);
    }

    /**
     * @param IdentityInterface $identity
     * @param Request $request
     * @param AuthSocial $social
     * @return static
     * @throws \Exception
     */
    public static function create($identity, $request, $social = null)
    {
        $model = static::instantiate([
            'userId' => $identity->getId(),
            'authId' => $social ? $social->primaryKey : null,
            'ipAddress' => $request->userIP,
            'userAgent' => $request->userAgent,
            'accessToken' => \Yii::$app->security->generateRandomString(static::ACCESS_TOKEN_LENGTH),
            'wsToken' => \Yii::$app->security->generateRandomString(static::WS_TOKEN_LENGTH),
            'expireTime' => date('Y-m-d H:i:s', strtotime('+' . static::LOGIN_DURATION_DAYS . ' days')),
        ]);
        $model->saveOrPanic();
        return $model;
    }

    /**
     * @param string $token
     * @return AuthLogin|null|ActiveRecord
     */
    public static function findByToken($token)
    {
        // Check db
        if (!\Yii::$app->db->getTableSchema(static::tableName())) {
            return null;
        }

        return static::find()
            ->joinWith('user user')
            ->where(['accessToken' => $token])
            ->andWhere(['not', ['user.isBanned' => true]])
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->one() ?: null;
    }

    /**
     * @param $userId
     * @return int
     */
    public static function logoutAll($userId)
    {
        return static::updateAll([
            'expireTime' => date('Y-m-d H:i:s'),
        ], [
            'and',
            ['>=', 'expireTime', date('Y-m-d H:i:s')],
            ['userId' => $userId]
        ]);
    }

    /**
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function logout()
    {
        $this->expireTime = date('Y-m-d H:i:s');
        $this->saveOrPanic();
    }


    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        /** @var UserInterface $userClass */
        $userClass = \Yii::$app->user->identityClass;

        return $this->hasOne($userClass, ['id' => 'userId']);
    }

    public function getIsExpired()
    {
        return date('Y-m-d H:i:s') > $this->expireTime;
    }
}
