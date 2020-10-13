<?php

namespace steroids\auth\authenticators;

use Yii;
use steroids\auth\models\Auth2FaValidation;

/**
 * Class BaseAuthentificator
 * @package steroids\auth\authenticators
 * @property-read string $type
 */
abstract class BaseAuthenticator
{
    /**
     * Send code that shall be verified later
     *
     * @param string $login value of the user's login attribute
     */
    abstract public function sendCode(string $login);

    /**
     * @return string one of AuthenticatorEnum values
     */
    abstract public function getType();

    /**
     * With success result must call onCorrectCode
     *
     * @param string $code code that should be validated
     * @param string $login value of the user's login attribute
     * @return bool
     */
    abstract public function validateCode(string $code, string $login);


    /**
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function onCorrectCodeValidation(){
        $auth2FaValidationModel = new Auth2FaValidation([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => $this->type
        ]);
        $auth2FaValidationModel->saveOrPanic();
    }
}