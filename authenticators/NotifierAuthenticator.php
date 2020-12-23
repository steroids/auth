<?php

namespace steroids\auth\authenticators;

use steroids\auth\AuthModule;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthTwoFactor;

/**
 * Class NotifierAuthenticator
 * @package steroids\auth\models
 */
class NotifierAuthenticator extends BaseAuthenticator
{
    public ?string $attributeType = null;

    /**
     * @inheritDoc
     * @throws \yii\base\Exception
     * @return AuthConfirm
     */
    public function start(AuthTwoFactor $twoFactor)
    {
        return AuthModule::getInstance()->confirm($twoFactor->user, $this->attributeType);
    }

    /**
     * @inheritDoc
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function check(AuthTwoFactor $twoFactor, string $code)
    {
        // Get login
        $module = AuthModule::getInstance();
        $attributeType = $this->attributeType ?: $module->registrationMainAttribute;
        $login = $twoFactor->user->getAttribute($module->getUserAttributeName($attributeType));

        // Confirm
        $confirm = AuthConfirm::findByCode($login, $code);
        if ($confirm) {
            $confirm->markConfirmed();
            return true;
        }

        return false;
    }
}