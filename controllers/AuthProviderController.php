<?php

namespace steroids\auth\controllers;

use Yii;
use yii\web\Controller;
use steroids\auth\forms\SocialEmailConfirmForm;
use steroids\auth\forms\SocialEmailForm;
use steroids\auth\forms\AuthProviderLoginForm;

class AuthProviderController extends Controller
{
    public static function apiMap()
    {
        return [
            'auth-social' => [
                'items' => [
                    'login' => 'POST api/<version>/auth/social',
                    'email' => 'POST api/<version>/auth/social/email',
                    'email-confirm' => 'POST api/<version>/auth/social/email/confirm',
                    'proxy' => 'GET api/<version>/auth/social/proxy',
                ],
            ],
        ];
    }

    /**
     * Login
     * @return AuthProviderLoginForm
     * @throws \Exception
     */
    public function actionLogin()
    {
        $model = new AuthProviderLoginForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->login();
        }
        return $model;
    }

    /**
     * Login
     * @return SocialEmailForm
     * @throws \Exception
     */
    public function actionEmail()
    {
        $model = new SocialEmailForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->send();
        }
        return $model;
    }

    /**
     * Login
     * @return SocialEmailConfirmForm
     * @throws \Exception
     */
    public function actionEmailConfirm()
    {
        $model = new SocialEmailConfirmForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->confirm();
        }
        return $model;
    }

    /**
     * Oauth proxy for modal windows
     * @return string
     * @throws \Exception
     */
    public function actionProxy()
    {
        return $this->renderFile(Yii::getAlias('@steroids/auth/views/proxy.php'));
    }
}
