<?php


namespace steroids\auth\authenticators;

use PragmaRX\Google2FA\Google2FA;
use steroids\auth\authenticators\BaseAuthentificator;
use steroids\auth\enums\AuthentificatorEnum;
use steroids\auth\models\Auth2FaValidation;
use steroids\auth\models\UserAuthentificatorKeys;
use Yii;


class GoogleAuthentificator extends BaseAuthentificator
{

    //not use for Google Authentificator
    public function sendCode()
    {
        return '';
    }

    public function getType()
    {
        return AuthentificatorEnum::GOOGLE_AUTH;
    }


    public function validateCode(string $code)
    {
        $google2fa = new Google2FA();

        $userAuthKeys = UserAuthentificatorKeys::findOne([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => $this->type
        ]);

        if(!$userAuthKeys){
            return false;
        }

        $valid = $google2fa->verifyKey($userAuthKeys->secretKey, $code, 8);
        if($valid){
            $this->onCorrectCode(new Auth2FaValidation([
                'userId' => Yii::$app->user->id,
                'authentificatorType' => $this->type
            ]));

            return true;
        }

        return false;
    }

}