<?php


namespace steroids\auth\models;

use steroids\auth\enums\AuthentificatorEnum;
use Yii;
use steroids\auth\authenticators\BaseAuthentificator;
use steroids\auth\AuthModule;
use steroids\auth\UserInterface;

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

    public function sendCode(string $login)
    {
        /** @var UserInterface $userClass */
        $userClass = \Yii::$app->user->identityClass;

        $attribute = strpos($login, '@') !== false ? AuthModule::ATTRIBUTE_EMAIL : AuthModule::ATTRIBUTE_PHONE;
        AuthModule::getInstance()->confirm($userClass,$attribute,true);
    }

    /**
     * @param string $code
     * @param string $login
     * @return bool
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