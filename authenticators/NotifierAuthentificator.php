<?php


namespace steroids\auth\models;

use Yii;
use steroids\auth\authenticators\BaseAuthentificator;
use steroids\auth\AuthModule;
use steroids\auth\UserInterface;

class NotifierAuthentificator extends BaseAuthentificator
{
    public string $login;

    public function getType()
    {
        return 'notifierAuth';
    }

    public function sendCode()
    {
        /** @var UserInterface $userClass */
        $userClass = \Yii::$app->user->identityClass;

        $attribute = strpos($this->login, '@') !== false ? AuthModule::ATTRIBUTE_EMAIL : AuthModule::ATTRIBUTE_PHONE;
        AuthModule::getInstance()->confirm($userClass,$attribute,true);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function validateCode(string $code)
    {
        $confirm = AuthConfirm::findByCode($this->login,$code);

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