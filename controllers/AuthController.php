<?php

namespace steroids\auth\controllers;

use steroids\auth\AuthModule;
use steroids\auth\exceptions\ConfirmCodeAlreadySentException;
use steroids\auth\forms\ConfirmForm;
use steroids\auth\models\AuthConfirm;
use Yii;
use yii\web\Controller;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
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
                    'confirm' => 'POST api/v1/auth/confirms/<uid>',
                    'resend-confirm' => 'POST api/v1/auth/confirms/<uid>/resend',
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
     * @return ConfirmForm
     * @throws \Exception
     */
    public function actionRegistrationConfirm()
    {
        return $this->actionConfirm();
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
     * Resend confirm code
     * @param string $uid
     * @return ConfirmForm
     * @throws \Exception
     */
    public function actionConfirm(string $uid = null)
    {
        /** @var ConfirmForm $model */
        $model = AuthModule::instantiateClass(ConfirmForm::class);
        $model->login = $uid;
        $model->load(Yii::$app->request->post());
        $model->confirm();
        return $model;
    }

    /**
     * Resend confirm code
     * @param string $uid
     * @return AuthConfirm|null
     * @throws ConfirmCodeAlreadySentException
     * @throws \steroids\core\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionResendConfirm(string $uid)
    {
        $prev = AuthConfirm::findOrPanic(['uid' => $uid]);
        $confirm = AuthModule::getInstance()->confirm($prev->user, $prev->type, $prev->is2Fa);
        if ($confirm->isReused) {
            throw new ConfirmCodeAlreadySentException(\Yii::t('steroids', 'Код уже был отправлен'));
        }

        return $confirm;
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
