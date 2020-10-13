<?php


namespace steroids\auth\models;

use steroids\auth\enums\AuthenticatorEnum;
use steroids\auth\authenticators\BaseAuthenticator;
use steroids\auth\AuthModule;
use steroids\auth\UserInterface;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class NotifierAuthenticator
 * @package steroids\auth\models
 */

class NotifierAuthenticator extends BaseAuthenticator
{
    public function getType()
    {
        return AuthenticatorEnum::NOTIFIER_AUTH;
    }

    /**
     * @inheritDoc
     * @throws \yii\base\Exception
     */
    public function sendCode(string $login)
    {
        /** @var UserInterface $user */
        $user = Yii::$app->user->identityClass;

        if (!$user) {
            throw new InvalidConfigException('Context app user must be set before sending 2FA code');
        }

        $userAttributeType = AuthModule::getNotifierAttributeTypeFromLogin($login);
        AuthModule::getInstance()->confirm($user, $userAttributeType, true);
    }

    /**
     * @inheritDoc
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function validateCode(string $code, string $login)
    {
        if (!AuthConfirm::findByCode($login, $code)){
            return false;
        }

        $this->onCorrectCodeValidation();

        return true;
    }
}