<?php


namespace steroids\auth\authenticators;

use PragmaRX\Google2FA\Google2FA;
use steroids\auth\authenticators\BaseAuthentificator;
use Yii;

class GoogleAuthentificator extends BaseAuthentificator
{

    public function sendCode()
    {
        return '';
    }

    public function getType()
    {
        return 'GoogleAuth';
    }

    public function validateCode(string $code)
    {
        $google2fa = new Google2FA();

        if(!Yii::$app->user->google2faSecretKey){
            Yii::$app->user->model->google2faSecretKey = $google2fa->generateSecretKey();
            Yii::$app->user->model->saveOrPanic();
        }

        $valid = $google2fa->verifyKey(Yii::$app->user->google2faSecretKey, $code, 8);

        return $valid ?? false;
    }

}