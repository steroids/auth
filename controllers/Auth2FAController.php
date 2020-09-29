<?php


namespace steroids\auth\controllers;


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