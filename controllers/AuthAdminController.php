<?php

namespace steroids\auth\controllers;

use Yii;
use steroids\auth\AuthModule;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthLogin;
use steroids\core\base\CrudApiController;
use steroids\core\base\Model;
use steroids\core\base\SearchModel;
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
                    'logins' => [
                        'label' => \Yii::t('steroids', 'История входа'),
                        'url' => ['logins'],
                        'urlRule' => "GET $baseUrl/<$idParam>/logins",
                    ],
                    'logout' => [
                        'label' => \Yii::t('steroids', 'Разлогинить на определенном устройстве'),
                        'url' => ['logout-all'],
                        'urlRule' => "POST $baseUrl/<$idParam>/logins/<loginId>/logout",
                    ],
                    'logout-all' => [
                        'label' => \Yii::t('steroids', 'Разлогинить на всех устройствах'),
                        'url' => ['logout-all'],
                        'urlRule' => "POST $baseUrl/<$idParam>/logout-all",
                    ],
                    'confirm-send' => [
                        'label' => \Yii::t('steroids', 'Повторно отправить код подтверждения'),
                        'url' => ['password'],
                        'urlRule' => "POST $baseUrl/<$idParam>/confirms",
                    ],
                    'confirms' => [
                        'label' => \Yii::t('steroids', 'История подтверждений'),
                        'url' => ['confirms'],
                        'urlRule' => "GET $baseUrl/<$idParam>/confirms",
                    ],
                    'confirm-accept' => [
                        'label' => \Yii::t('steroids', 'Отметить подтвержденным почту или телефон'),
                        'url' => ['password'],
                        'urlRule' => "POST $baseUrl/<$idParam>/confirms/<confirmId>/accept",
                    ],
                    'ban' => [
                        'label' => \Yii::t('steroids', 'Блокировка пользователя'),
                        'url' => ['ban'],
                        'urlRule' => "POST $baseUrl/<$idParam>/ban",
                    ],
                    'password' => [
                        'label' => \Yii::t('steroids', 'Обновить пароль'),
                        'url' => ['password'],
                        'urlRule' => "POST $baseUrl/<$idParam>/password",
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

    }

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

    public function actionLogoutAll()
    {
        $user = $this->findModel();

        /** @var AuthLogin $authLoginClass */
        $authLoginClass = AuthModule::resolveClass(AuthLogin::class);
        $authLoginClass::logoutAll($user->primaryKey);
    }

    public function actionPassword()
    {
        $user = $this->findModel();
        $newPassword = Yii::$app->request->post('newPassword');

        // TODO Form
    }

    public function actionConfirmSend()
    {
        $user = $this->findModel();

    }

    public function actionConfirmAccept()
    {
        $user = $this->findModel();
        $confirmId = Yii::$app->request->get('confirmId');

    }
}
