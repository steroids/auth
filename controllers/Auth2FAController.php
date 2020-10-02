<?php


namespace steroids\auth\controllers;

use steroids\auth\authenticators\GoogleAuthentificator;
use steroids\auth\enums\AuthentificatorEnum;
use steroids\auth\forms\Validate2FaCode;
use steroids\auth\models\UserAuthentificatorKeys;
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
                    'two-fa-validate-code' => 'POST /auth/2fa/validate-code',
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