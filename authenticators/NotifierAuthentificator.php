<?php


namespace steroids\auth\models;


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

        AuthModule::getInstance()->confirm($userClass,null,true);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function validateCode(string $code)
    {
        $confirm = AuthConfirm::findByCode($this->login,$code);

        return $confirm !== null;
    }
}