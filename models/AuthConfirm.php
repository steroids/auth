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

    /**
     * @param string $login
     * @param string $code
     * @return static
     */
    public static function findByCode($login, $code)
    {
        // Check if login is uid
        $login = static::find()
            ->select('value')
            ->where(['uid' => $login])
            ->scalar() ?: $login;

        /** @var static $confirm */
        $confirm = static::find()
            ->where(['code' => trim($code)])
            ->andWhere([
                'or',
                ['value' => mb_strtolower(trim($login))],
                ['uid' => mb_strtolower(trim($login))],
            ])
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one() ?: null;
        return $confirm;
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
