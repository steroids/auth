<?php

namespace steroids\auth\controllers;

use steroids\auth\AuthModule;
use steroids\auth\forms\ConfirmForm;
use Yii;
use yii\web\Controller;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
use steroids\auth\forms\RegistrationConfirmForm;
use steroids\auth\forms\RegistrationForm;
use steroids\auth\forms\LoginForm;

class AuthController extends Controller
{
    public static function apiMap()
    {
        return [
            'auth' => [
                'items' => [
                    'registration' => 'POST api/v1/auth/registration',
                    'registration-confirm' => 'POST api/v1/auth/registration/confirm',
                    'login' => 'POST api/v1/auth/login',
                    'recovery' => 'POST api/v1/auth/recovery',
                    'recovery-confirm' => 'POST api/v1/auth/recovery/confirm',
                    'logout' => 'POST api/v1/auth/logout',
                    'ws' => 'GET api/v1/auth/ws',
                ],
            ],
        ];
    }

    /**
     * Registration
     * @return RegistrationForm
     * @throws \Exception
     */
    public function actionRegistration()
    {
        /** @var RegistrationForm $model */
        $model = AuthModule::instantiateClass(RegistrationForm::class);
        $model->load(Yii::$app->request->post());
        $model->register();
        return $model;
    }

    /**
     * Registration
     * @return RegistrationConfirmForm
     * @throws \Exception
     */
    public function actionRegistrationConfirm()
    {
        /** @var RegistrationConfirmForm $model */
        $model = AuthModule::instantiateClass(ConfirmForm::class);
        $model->load(Yii::$app->request->post());
        $model->confirm();
        return $model;
    }

    /**
     * Login
     * @return LoginForm
     * @throws \Exception
     */
    public function actionLogin()
    {
        /** @var LoginForm $model */
        $model = AuthModule::instantiateClass(LoginForm::class);
        $model->load(Yii::$app->request->post());
        $model->login();
        return $model;
    }

    /**
     * Recovery request (send email)
     * @return RecoveryPasswordForm
     * @throws \Exception
     */
    public function actionRecovery()
    {
        /** @var RecoveryPasswordForm $model */
        $model = AuthModule::instantiateClass(RecoveryPasswordForm::class);
        $model->load(Yii::$app->request->post());
        $model->send();
        return $model;
    }

    /**
     * Recovery request (confirm and change password)
     * @return RecoveryPasswordConfirmForm
     * @throws \Exception
     */
    public function actionRecoveryConfirm()
    {
        /** @var RecoveryPasswordConfirmForm $model */
        $model = AuthModule::instantiateClass(RecoveryPasswordConfirmForm::class);
        $model->load(Yii::$app->request->post());
        $model->confirm();
        return $model;
    }

    /**
     * Logout
     * @return array
     */
    public function actionLogout()
    {
        return [
            'success' => Yii::$app->user->logout(),
        ];
    }

    public function actionWs()
    {
        return [
            'token' => \Yii::$app->user->refreshWsToken(),
        ];
    }
}
