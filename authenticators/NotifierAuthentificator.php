<?php


namespace steroids\auth\models;

use steroids\auth\enums\AuthentificatorEnum;
use Yii;
use steroids\auth\authenticators\BaseAuthentificator;
use steroids\auth\AuthModule;
use steroids\auth\UserInterface;
use yii\base\InvalidConfigException;

/**
 * Class NotifierAuthentificator
 * @package steroids\auth\models
 */

class NotifierAuthentificator extends BaseAuthentificator
{
    public function getType()
    {
        return AuthentificatorEnum::NOTIFIER_AUTH;
    }

    /**
     * @param string $login
     * @throws InvalidConfigException
     * @throws \steroids\core\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     */
    public function sendCode(string $login)
    {
        /** @var UserInterface $user */
        $user = \Yii::$app->user->identityClass;

        if (!$user) {
            throw new InvalidConfigException('Context app user must be set before sending 2FA code');
        }

        AuthModule::getInstance()->confirm(
            $user,
            AuthModule::getNotifierAttributeTypeFromLogin($login),
            true
        );
    }

    /**
     * @param string $code
     * @param string $login
     * @return bool
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function validateCode(string $code,string $login)
    {
        $confirm = AuthConfirm::findByCode($login,$code);

        if($confirm !== null){
            $this->onCorrectCode(new Auth2FaValidation([
                'userId' => Yii::$app->user->id,
                'authentificatorType' => $this->type
            ]));

            return true;
        }

        return false;
    }
}