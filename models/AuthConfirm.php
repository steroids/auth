<?php

namespace steroids\auth\models;

use steroids\auth\AuthModule;
use steroids\auth\models\meta\AuthConfirmMeta;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use steroids\core\behaviors\TimestampBehavior;
use steroids\core\behaviors\UidBehavior;
use \steroids\exceptions\ModelSaveException;
use yii\db\ActiveQuery;

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

    public static function checkLoginIsUid(string $login): string
    {
        // Check if login is uid
        return static::find()
            ->select('value')
            ->where(['uid' => $login])
            ->scalar() ?: $login;
    }

    public static function findByCode($login, $code): ?AuthConfirm
    {
        $login = static:: checkLoginIsUid($login);

        return static::find()
            ->where(['code' => trim($code)])
            ->andWhere([
                'or',
                ['value' => mb_strtolower(trim($login))],
                ['uid' => mb_strtolower(trim($login))],
            ])
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public static function findByLogin(string $login): ?AuthConfirm
    {
        $login = static:: checkLoginIsUid($login);

        /** @var static $confirm */
        return static::find()
            ->andWhere([
                'or',
                ['value' => mb_strtolower(trim($login))],
                ['uid' => mb_strtolower(trim($login))],
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
