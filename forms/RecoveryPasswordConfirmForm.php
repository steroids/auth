<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\forms\meta\RecoveryPasswordConfirmFormMeta;
use steroids\auth\models\AuthConfirm;
use steroids\core\validators\PasswordValidator;

class RecoveryPasswordConfirmForm extends RecoveryPasswordConfirmFormMeta
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
        return array_merge(parent::rules(), [
            ['newPassword', PasswordValidator::class],
            ['newPassword', 'compare', 'compareAttribute' => 'newPasswordAgain'],
            ['login', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
            ['code', function($attribute) {
                $this->confirm = AuthConfirm::findByCode($this->login, $this->code);
                if (!$this->confirm) {
                    $this->addError($attribute, \Yii::t('steroids', 'Код неверен или устарел'));
                }
            }],
        ]);
    }

    public function confirm()
    {
        if ($this->validate()) {
            $transaction = static::getDb()->beginTransaction();
            try {
                // Confirm
                $this->confirm->markConfirmed();

                // Save new password
                $module = AuthModule::getInstance();
                $this->confirm->user->setAttribute($module->passwordHashAttribute, \Yii::$app->security->generatePasswordHash($this->newPassword));
                $this->confirm->user->saveOrPanic();

                // Login
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
