<?php

namespace steroids\auth\tests\mocks;

use steroids\auth\validators\TwoFactorRequireValidator;
use steroids\core\base\FormModel;

class TestPayForm extends FormModel
{
    public $user;
    public $providerName;
    public $amount;

    public function rules()
    {
        return [
            ['amount', 'integer'],
            ['amount', 'required'],
            [
                'amount',
                TwoFactorRequireValidator::class,
                'userId' => $this->user->getId(),
                'providerName' => $this->providerName,
            ],
        ];
    }
}
