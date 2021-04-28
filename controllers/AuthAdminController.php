<?php

namespace steroids\auth\controllers;

use Yii;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\forms\ChangePasswordForm;
use steroids\auth\forms\LoginForm;
use steroids\auth\AuthModule;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthLogin;
use steroids\core\base\CrudApiController;
use steroids\core\base\Model;
use steroids\core\base\SearchModel;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AuthAdminController extends CrudApiController
{
    public static function modelClass()
    {
        return AuthModule::getInstance()->userClass;
    }

    public static function apiMap($baseUrl = '/api/v1/admin/auth')
    {
        /** @var Model $modelClass */
        $modelClass = static::modelClass();
        $idParam = $modelClass::getRequestParamName();

        return [
            'admin.auth' => static::apiMapCrud($baseUrl, [
                'items' => [
                    'login' => [
                        'label' => \Yii::t('steroids', 'Вход для администратора'),
                        'urlRule' => "POST $baseUrl/login",
                    ],
                    'logins' => [
                        'label' => \Yii::t('steroids', 'История входа'),
                        'urlRule' => "GET $baseUrl/<$idParam:\d+>/logins",
                    ],
                    'logout' => [
                        'label' => \Yii::t('steroids', 'Разлогинить на определенном устройстве'),
                        'urlRule' => "POST $baseUrl/<$idParam:\d+>/logins/<loginId>/logout",
                    ],
                    'logout-all' => [
                        'label' => \Yii::t('steroids', 'Разлогинить на всех устройствах'),
                        'urlRule' => "POST $baseUrl/<$idParam:\d+>/logout-all",
                    ],
                    'confirm-send' => [
                        'label' => \Yii::t('steroids', 'Повторно отправить код подтверждения'),
                        'urlRule' => "POST $baseUrl/<$idParam:\d+>/confirms",
                    ],
                    'confirms' => [
                        'label' => \Yii::t('steroids', 'История подтверждений'),
                        'urlRule' => "GET $baseUrl/<$idParam:\d+>/confirms",
                    ],
                    'confirm-accept' => [
                        'label' => \Yii::t('steroids', 'Отметить подтвержденным почту или телефон'),
                        'urlRule' => "POST $baseUrl/<$idParam:\d+>/confirms/<confirmId>/accept",
                    ],
                    'ban' => [
                        'label' => \Yii::t('steroids', 'Блокировка пользователя'),
                        'urlRule' => "POST $baseUrl/<$idParam:\d+>/ban",
                    ],
                    'password' => [
                        'label' => \Yii::t('steroids', 'Обновить пароль'),
                        'urlRule' => "POST $baseUrl/<$idParam:\d+>/password",
                    ],
                ],
            ]),
        ];
    }

    public function fields()
    {
        /** @var Model $modelClass */
        $modelClass = static::modelClass();
        $module = AuthModule::getInstance();

        return array_filter([
            '*',
            $modelClass::getRequestParamName(),
            'name',
            $module->loginAttribute,
            $module->emailAttribute,
            $module->phoneAttribute,
        ]);
    }

    public function detailFields()
    {
        return $this->fields();
    }

    /**
     * @return LoginForm
     * @throws \yii\base\Exception
     */
    public function actionLogin()
    {
        AuthModule::getInstance()->registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;
        AuthModule::getInstance()->isPasswordAvailable = true;

        /** @var LoginForm $model */
        $model = AuthModule::instantiateClass(LoginForm::class);
        $model->load(Yii::$app->request->post());
        $model->login();
        return $model;
    }

    /**
     * @return SearchModel
     * @throws \yii\base\Exception
     */
    public function actionLogins()
    {
        $searchModel = new SearchModel([
            'model' => AuthModule::resolveClass(AuthLogin::class),
            'user' => false,
            'fields' => [
                'id',
                'ipAddress',
                'location',
                'userAgent',
                'createTime',
                'expireTime',
                'isExpired',
            ],
        ]);
        $searchModel->search(Yii::$app->request->get());
        $searchModel->dataProvider->query
            ->andWhere(['userId' => \Yii::$app->request->get('userId')])
            ->orderBy(['id' => SORT_DESC]);

        return $searchModel;
    }

    /**
     * @return SearchModel
     * @throws \yii\base\Exception
     */
    public function actionConfirms()
    {
        $searchModel = new SearchModel([
            'model' => AuthModule::resolveClass(AuthConfirm::class),
            'user' => false,
            'fields' => [
                'id',
                'type',
                'value',
                'code',
                'isConfirmed',
                'createTime',
                'updateTime',
                'expireTime',
            ],
        ]);
        $searchModel->search(Yii::$app->request->get());
        $searchModel->dataProvider->query
            ->andWhere(['userId' => \Yii::$app->request->get('userId')])
            ->orderBy(['id' => SORT_DESC]);

        return $searchModel;
    }

    public function actionBan()
    {
        $user = $this->findModel();
        // TODO
    }

    /**
     * @throws NotFoundHttpException
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function actionLogout()
    {
        $user = $this->findModel();
        $authLogin = AuthLogin::findOne([
            'id' => Yii::$app->request->get('logoutId'),
            'userId' => $user->primaryKey,
        ]);
        if (!$authLogin) {
            throw new NotFoundHttpException();
        }

        $authLogin->logout();
    }

    /**
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionLogoutAll()
    {
        $user = $this->findModel();

        /** @var AuthLogin $authLoginClass */
        $authLoginClass = AuthModule::resolveClass(AuthLogin::class);
        $authLoginClass::logoutAll($user->primaryKey);
    }

    /**
     * @return ChangePasswordForm
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionPassword()
    {
        $user = $this->findModel();
        if (!$user->canUpdate(Yii::$app->user->identity)) {
            throw new ForbiddenHttpException();
        }

        /** @var ChangePasswordForm $model */
        $model = AuthModule::instantiateClass(ChangePasswordForm::class);
        $model->user = $user;
        $model->load(Yii::$app->request->post());
        $model->change();
        return $model;
    }

    /**
     * @return AuthConfirm|null
     * @throws NotFoundHttpException
     * @throws \steroids\core\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     */
    public function actionConfirmSend()
    {
        $user = $this->findModel();
        return AuthModule::getInstance()->confirm($user);
    }

    /**
     * @return AuthConfirm
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionConfirmAccept()
    {
        $user = $this->findModel();
        $confirmId = Yii::$app->request->get('confirmId');

        /** @var AuthConfirm $authConfirmClass */
        $authConfirmClass = AuthModule::resolveClass(AuthConfirm::class);
        $confirm = $authConfirmClass::findOrPanic(['id' => $confirmId]);
        $confirm->markConfirmed();
        return $confirm;
    }
}
