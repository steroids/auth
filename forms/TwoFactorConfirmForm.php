<?php

namespace steroids\auth\forms;

use steroids\auth\forms\meta\TwoFactorConfirmFormMeta;
use steroids\auth\models\AuthTwoFactor;
use steroids\auth\UserInterface;
use yii\web\IdentityInterface;

class TwoFactorConfirmForm extends TwoFactorConfirmFormMeta
{
    /**
     * @var UserInterface|IdentityInterface
     */
    public $user;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ...parent::rules(),
            ['code', function ($attribute) {
                $twoFactor = AuthTwoFactor::findForUser($this->providerName, $this->user->getId());
                if ($twoFactor && $twoFactor->check($this->$attribute)) {
                    $twoFactor->markConfirmed();
                } else {
                    $this->addError($attribute, \Yii::t('steroids', 'Валидация не пройдена'));
                }
            }],
        ];
    }
}
