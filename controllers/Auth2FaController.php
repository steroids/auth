<?php


namespace steroids\auth\controllers;

use steroids\auth\authenticators\GoogleAuthenticator;
use steroids\auth\enums\AuthenticatorEnum;
use steroids\auth\forms\Validate2FaCode;
use steroids\auth\models\UserAuthenticatorKey;
use Yii;
use PragmaRX\Google2FA\Google2FA;
use steroids\auth\AuthModule;
use yii\web\Controller;

class Auth2FaController extends Controller
{
    public static function apiMap()
    {
        return [
            'auth' => [
                'items' => [
                    'two-fa-validate-code' => 'POST /auth/auth-2fa/validate-code',
                ],
            ],
        ];
    }

    /**
     * @return Validate2FaCode
     */
    public function actionValidateCode()
    {
        $model = new Validate2FaCode();
        $model->load(Yii::$app->request->post());

        return $model;
    }

}