<?php

namespace steroids\auth\validators;

use steroids\auth\models\AuthTwoFactor;
use yii\validators\Validator;

class TwoFactorRequireValidator extends Validator
{
    const TWO_FACTOR_ERROR_VALUE_PREFIX = '2FA_REQUIRED:';

    public ?int $userId = null;
    public ?string $providerName = null;

    /**
     * @inheritDoc
     */
    public function validateAttribute($model, $attribute)
    {
        // Check has user 2fa
        if (!$this->providerName || !$this->userId) {
            return;
        }

        // Get model
        $twoFactor = AuthTwoFactor::findForUser($this->providerName, $this->userId, true);
        if (!$twoFactor->isConfirmed) {
            // Send code
            $twoFactor->start();

            // Mark 2FA required
            $this->addError($model, $attribute, self::TWO_FACTOR_ERROR_VALUE_PREFIX . $this->providerName);
        }
    }
}