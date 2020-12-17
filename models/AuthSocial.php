<?php

namespace steroids\auth\models;

use steroids\auth\AuthModule;
use steroids\auth\AuthProfile;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\models\meta\AuthSocialMeta;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use steroids\core\behaviors\UidBehavior;
use steroids\core\exceptions\ModelSaveException;
use yii\db\ActiveQuery;
use yii\helpers\Json;
use yii\web\IdentityInterface;

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
     * Create blank user without email
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     */
    public function appendBlank()
    {
        /** @var UserInterface|Model $userClass */
        $userClass = \Yii::$app->user->identityClass;

        /** @var UserInterface|Model $user */
        $user = new $userClass();
        $user->setAttribute(AuthModule::getInstance()->nameAttribute, $this->profile->name);
        $user->saveOrPanic();

        $this->appendUser($user);
    }

    /**
     * Find or create user by email and append it
     * @param string $email
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     */
    public function appendEmail(string $email)
    {
        // Skip already appended
        if ($this->userId) {
            return;
        }

        /** @var UserInterface|Model $userClass */
        $userClass = \Yii::$app->user->identityClass;

        $user = $userClass::findBy($email, [AuthAttributeTypeEnum::EMAIL]);
        if (!$user) {
            /** @var UserInterface|Model $user */
            $user = new $userClass();
            $user->setAttribute(AuthModule::getInstance()->emailAttribute, $email);
            $user->setAttribute(AuthModule::getInstance()->nameAttribute, $this->profile->name);
            $user->saveOrPanic();
        }

        $this->appendUser($user);
    }

    /**
     * Append exists user
     * @param IdentityInterface|UserInterface|Model $user
     * @throws ModelSaveException
     */
    public function appendUser($user)
    {
        $this->userId = $user->getId();
        $this->populateRelation('user', $user);
        $this->saveOrPanic();
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