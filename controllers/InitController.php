<?php

namespace steroids\auth\controllers;

use Yii;
use yii\web\Controller;

class InitController extends Controller
{
    public $enableCsrfValidation = false;

    public static function apiMap()
    {
        return [
            'init' => 'api/v1/init',
        ];
    }

    /**
     * Init data
     * @return array
     * @throws \Exception
     */
    public function actionInit()
    {
        return $this->getData(array_merge(
            Yii::$app->request->get(),
            Yii::$app->request->post()
        ));
    }

    protected function getData($body)
    {
        $body = array_merge([
            'timestamp' => null,
            'models' => [],
            'enums' => [],
        ], $body);

        return [
            'config' => [
                'http' => [
                    'csrfToken' => Yii::$app->request->csrfToken,
                ],
                'locale' => [
                    'language' => Yii::$app->language,
                    'backendTimeZone' => Yii::$app->timeZone === 'Europe/Moscow' ? '+0300' : '+0000',
                    'backendTimeDiff' => $body['timestamp'] ? round(microtime(true) * 1000 - $body['timestamp']) : null,
                ],
            ],
            'meta' => Yii::$app->types->getFrontendMeta($body['models'], $body['enums']),
            'user' => !Yii::$app->user->isGuest ? Yii::$app->user->identity : null,
        ];
    }
}
