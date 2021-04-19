<?php

namespace steroids\auth\models;

use steroids\auth\authenticators\BaseAuthenticator;
use steroids\auth\AuthModule;
use steroids\auth\models\meta\AuthTwoFactorMeta;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use steroids\core\exceptions\ModelSaveException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Class AuthTwoFactor
 * @package steroids\auth\models
 * @property-read UserInterface|IdentityInterface|Model $user
 * @property-read BaseAuthenticator $provider
 */
class AuthTwoFactor extends AuthTwoFactorMeta
{
    /**
     * @param string $providerName
     * @param int $userId
     * @param bool $lazyCreate
     * @return AuthTwoFactor|ActiveRecord|null
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function findForUser(string $providerName, int $userId, bool $lazyCreate = false)
    {
        $query = AuthTwoFactor::find()
            ->where([
                'providerName' => $providerName,
                'userId' => $userId,
            ])
            ->andWhere([
                'or',
                ['>=', 'expireTime', date('Y-m-d H:i:s')],
                ['expireTime' => null]
            ])
            ->orderBy(['id' => SORT_DESC]);

        // Search confirmed is priority
        $model = (clone $query)->andWhere(['isConfirmed' => true])->one()
            ?: (clone $query)->andWhere(['isConfirmed' => false])->one();

        // Lazy create
        if (!$model && $lazyCreate) {
            $model = new static([
                'providerName' => $providerName,
                'userId' => $userId,
            ]);
            if ($model->provider->expireSec > 0) {
                $model->expireTime = date('Y-m-d H:i:s', strtotime('+' . $model->provider->expireSec . ' seconds'));
            }
            $model->saveOrPanic();
        }

        return $model;
    }

    /**
     * @return BaseAuthenticator
     * @throws Exception
     */
    public function getProvider()
    {
        return AuthModule::getInstance()->getTwoFactorProvider($this->providerName);
    }

    /**
     *
     */
    public function start()
    {
        if (!$this->isConfirmed) {
            return $this->provider->start($this);
        }
        return null;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function check($code)
    {
        return $this->isConfirmed || $this->provider->check($this, $code);
    }

    /**
     * @throws ModelSaveException
     */
    public function markConfirmed()
    {
        $this->isConfirmed = true;
        $this->saveOrPanic();
    }


    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        $userClass = AuthModule::getInstance()->userClass;
        return $this->hasOne($userClass, ['id' => 'userId']);
    }
}
