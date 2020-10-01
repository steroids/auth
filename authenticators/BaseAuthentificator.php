<?php

namespace steroids\auth\authenticators;

use Yii;
use steroids\auth\models\Auth2FaValidation;

/**
 * Class BaseAuthentificator
 * @package steroids\auth\authenticators
 * @property-read string $type
 */
abstract class BaseAuthentificator
{
    abstract public function sendCode(string $login);
    abstract public function getType();

    /**
     * @param string $code
     * @param string $login
     * With success result must call onCorrectCode
     * @return bool
     */
    abstract public function validateCode(string $code, string $login);


    /**
     * @param Auth2FaValidation $auth2FaValidationModel
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function onCorrectCode(Auth2FaValidation $auth2FaValidationModel){
        $auth2FaValidationModel->saveOrPanic();
    }
}