<?php

namespace steroids\auth\models;

use steroids\auth\AuthModule;
use steroids\auth\models\meta\AuthConfirmMeta;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use steroids\core\behaviors\UidBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property-read UserInterface|Model $user
 */
class AuthConfirm extends AuthConfirmMeta
{
    const TEMPLATE_NAME = 'auth/confirm';

    /**
     * @inheritDoc
     */
    public static function instantiate($row)
    {
        return AuthModule::instantiateClass(static::class, $row);
    }

    /**
     * @param string $login
     * @param string|null $code
     * @return AuthConfirm|ActiveRecord|null
     */
    public static function findByCode(string $login, string $code = null): ?AuthConfirm
    {
        $login = mb_strtolower(trim($login));

        /** @var static $confirm */
        return static::find()
            ->andFilterWhere(['code' => trim($code)])
            ->andWhere([
                'or',
                ['LOWER(value)' => $login],
                ['uid' => $login],
            ])
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    /**
     * @param string $type
     * @param string $value
     * @return bool
     */
    public static function checkIsConfirmed(string $type, string $value)
    {
        return (bool) AuthConfirm::find()
            ->where([
                'type' => $type,
                'value' => $value,
                'isConfirmed' => true,
            ])
            ->exists();
    }

    public function behaviors()
    {
        return [
            ...parent::behaviors(),
            UidBehavior::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $expireMins = AuthModule::getInstance()->confirmExpireMins;
        $expireTime = date('Y-m-d H:i:s', strtotime('+' . $expireMins . ' minutes'));

        return [
            ...parent::rules(),
            ['value', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
            ['isConfirmed', 'default', 'value' => false],
            ['expireTime', 'default', 'value' => $expireTime],
        ];
    }

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
        /** @var UserInterface|Model $userClass */
        $userClass = \Yii::$app->user->identityClass;

        return $this->hasOne($userClass, ['id' => 'userId']);
    }
}
