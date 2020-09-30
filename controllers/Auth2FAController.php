<?php


namespace steroids\auth\controllers;

use PragmaRX\Google2FA\Google2FA;
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

    }
}