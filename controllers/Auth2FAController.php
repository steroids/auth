<?php


namespace steroids\auth\controllers;

use steroids\auth\authenticators\GoogleAuthentificator;
use steroids\auth\enums\AuthentificatorEnum;
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
                    'two-fa' => 'POST /auth/2fa/validate-code/<code>',
                    'two-fa-information' => '/auth/2fa/information',
                ],
            ],
        ];
    }

    /**
     * @param $code
     * @throws \yii\base\Exception
     * @return array
     */
    public function actionValidateCode($code)
    {
        $validate = AuthModule::getInstance()->authenticate2FA(
            Yii::$app->user,
            $code,
            Yii::$app->request->post('authType')
        );
        return $validate
            ? ['validate code success']
            : ['errors' => 'validate code error'];
    }

    /**
     * @return array
     */
    public function actionInformation()
    {
        return GoogleAuthentificator::generateUserSecretKey();
    }

}