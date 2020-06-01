<?php

namespace steroids\auth\models;

use steroids\auth\AuthModule;
use steroids\auth\models\meta\AuthConfirmMeta;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use \steroids\exceptions\ModelSaveException;
use yii\db\ActiveQuery;

/**
 * @property-read UserInterface|Model $user
 */
class AuthConfirm extends AuthConfirmMeta
{
    const TEMPLATE_NAME = 'authConfirm';

    /**
     * @inheritDoc
     */
    public static function instantiate($row)
    {
        return AuthModule::instantiateClass(static::class, $row);
    }

    /**
     * @param string $email
     * @param string $code
     * @return static
     * @throws ModelSaveException
     */
    public static function findByCode($email, $code)
    {
        return static::find()
            ->where([
                'LOWER(email)' => mb_strtolower(trim($email)),
                'code' => $code,
            ])
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->limit(1)
            ->one() ?: null;
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
