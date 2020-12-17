<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\forms\meta\ProviderLoginFormMeta;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthSocial;
use steroids\auth\providers\BaseAuthProvider;

class ProviderLoginForm extends ProviderLoginFormMeta
{
    /**
     * @var array
     */
    public $params = [];

    /**
     * @var BaseAuthProvider
     */
    public $provider;

    /**
     * @var AuthSocial
     */
    public $social;

    /**
     * @var string
     */
    public $accessToken;

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'social' => [
                '*',
                'uid',
                'isEmailNeed',
            ],
            'accessToken',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ...parent::rules(),
            ['name', function ($attribute) {
                $this->provider = AuthModule::getInstance()->getProvider($this->$attribute);
                if (!$this->provider) {
                    $this->addError($attribute, \Yii::t('steroids', 'Такой провайдер не найден'));
                }
            }],
            ['params', 'safe'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function login()
    {
        if (!$this->validate()) {
            return;
        }

        // Auth via provider
        $profile = $this->provider->auth($this->params);

        // Find or create AuthSocial
        $this->social = AuthSocial::findOrCreate($this->name, $profile);
        if (!\Yii::$app->user->isGuest) {
            $this->social->appendUser(\Yii::$app->user->identity);
        } elseif ($profile->email) {
            $this->social->appendEmail($profile->email);
        } elseif (AuthModule::getInstance()->registrationMainAttribute !== AuthAttributeTypeEnum::EMAIL) {
            $this->social->appendBlank();
        }

        \Yii::$app->user->login($this->social->user);
        $this->accessToken = \Yii::$app->user->accessToken;
    }
}