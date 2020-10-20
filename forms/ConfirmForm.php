<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\forms\meta\ConfirmFormMeta;
use steroids\auth\models\AuthConfirm;

class ConfirmForm extends ConfirmFormMeta
{
    /**
     * @var AuthConfirm
     */
    public $confirm;

    /**
     * @var string
     */
    public $accessToken;

    public function fields()
    {
        return [
            'accessToken',
        ];
    }

    public function rules()
    {
        $rules = array_merge(parent::rules(), [
            ['login', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
        ]);

        if (!YII_DEBUG || !AuthModule::getInstance()->debugSkipConfirmCodeCheck) {
            $rules = array_merge($rules, [
                ['code', function($attribute) {
                    $this->confirm = AuthConfirm::findByCode($this->login, $this->code);
                    if (!$this->confirm) {
                        $this->addError($attribute, \Yii::t('steroids', 'Код неверен или устарел'));
                    }
                }],
            ]);
        }

        return $rules;
    }

    public function confirm()
    {
        if ($this->validate()) {
            $transaction = static::getDb()->beginTransaction();
            try {
                // Confirm
                $this->confirm->markConfirmed();

                // Access token
                \Yii::$app->user->login($this->confirm->user);
                $this->accessToken = \Yii::$app->user->accessToken;

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
    }
}
