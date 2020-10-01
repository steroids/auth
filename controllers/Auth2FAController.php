<?php


namespace steroids\auth\controllers;

use Yii;
use PragmaRX\Google2FA\Google2FA;
use steroids\auth\AuthModule;
use yii\web\Controller;

class Auth2FAController extends Controller
{
    public static function apiMap()
    {
        return [
            'auth' => [
                'items' => [
                    'registration' => 'POST /auth/2fa/validate-code',
                ],
            ],
        ];
    }


    public function actionValidateCode()
    {
        $authResult = AuthModule::getInstance()->authenticate2FA(Yii::$app->user->identityClass,Yii::$app->request->post('authType'));
    }
}