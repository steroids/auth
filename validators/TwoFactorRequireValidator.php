<?php

namespace steroids\auth\validators;

use steroids\auth\models\AuthTwoFactor;
use yii\validators\Validator;

class TwoFactorRequireValidator extends Validator
{
    const TWO_FACTOR_ERROR_VALUE_PREFIX = '2FA_REQUIRED:';

    /**
     * ID пользователя
     * @var int|null
     */
    public ?int $userId = null;

    /**
     * Имя 2fa провайдера (notifier, google, ...)
     * @var string|null
     */
    public ?string $providerName = null;

    /**
     * Имя атрибута формы, в котором будет передаваться код
     * @var string|null
     */
    public ?string $codeAttribute = null;

    /**
     * @inheritDoc
     */
    public $skipOnEmpty = false;

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
            // Check sent code
            $codeAttribute = $this->codeAttribute;
            $code = $model->$codeAttribute;
            if ($code) {
                // Validate code
                if (!$twoFactor->check($code)) {
                    $this->addError($model, $codeAttribute, \Yii::t('steroids', 'Неверный код'));
                }
                return;
            }

            // Mark 2FA required
            $this->addError($model, $attribute, self::TWO_FACTOR_ERROR_VALUE_PREFIX . $this->providerName);
        }
    }
}