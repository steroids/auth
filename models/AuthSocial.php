<?php

namespace steroids\auth\models;

use steroids\auth\AuthModule;
use steroids\auth\AuthProfile;
use steroids\auth\models\meta\AuthSocialMeta;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use steroids\core\behaviors\UidBehavior;
use steroids\core\exceptions\ModelSaveException;
use yii\db\ActiveQuery;
use yii\helpers\Json;

/**
 * @property-read AuthProfile $profile
 * @property-read bool $isEmailNeed
 * @property-read UserInterface|Model $user
 */
class AuthSocial extends AuthSocialMeta
{
    /**
     * @inheritDoc
     */
    public static function instantiate($row)
    {
        return AuthModule::instantiateClass(static::class, $row);
    }

    public static function findOrCreate($name, AuthProfile $profile)
    {
        $params = [
            'socialName' => $name,
            'externalId' => $profile->id,
        ];
        $model = static::findOne($params) ?: static::instantiate($params);
        $model->profileJson = Json::encode($profile);
        $model->saveOrPanic();

//        if ($profile->email) {
//            $model->appendUser($profile->email);
//        }

        return $model;
    }

    public function fields()
    {
        return array_merge(parent::fields(), [
            'profile',
        ]);
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            UidBehavior::class,
        ]);
    }

    /**
     * @return bool
     */
    public function getIsEmailNeed()
    {
        return !$this->userId;
    }

    public function getProfile()
    {
        return $this->profileJson
            ? AuthModule::instantiateClass(AuthProfile::class, Json::decode($this->profileJson))
            : null;
    }

    /**
     * @param string $email
     * @throws ModelSaveException
     */
    public function appendUser()
    {
        $user = $this->findOrCreateUser();

        $this->userId = $user->primaryKey;
        $this->populateRelation('user', $user);
        $this->saveOrPanic();
    }

    private function findOrCreateUser()
    {
        if (!\Yii::$app->user->isGuest) {
            return \Yii::$app->user->identity;
        }

        $socialUser = $this->user;
        if ($socialUser) {
            return $socialUser;
        }

        /** @var UserInterface|Model $userClass */
        $userClass = \Yii::$app->user->identityClass;

        /** @var UserInterface|Model $user */
        $user = new $userClass();
        $user->attributes = [
            'role' => \Yii::$app->user->defaultRole,
            'username' => $this->profile->name,
        ];
        $user->saveOrPanic();

        return $user;
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        /** @var UserInterface|Model $userClass */
        $userClass = \Yii::$app->user->identityClass;

        return $this->hasOne($userClass, ['id' => 'userId']);
    }
}