<?php

namespace steroids\auth\authenticators;

use steroids\auth\models\Auth2FaValidation;

/**
 * Class BaseAuthentificator
 * @package steroids\auth\authenticators
 *  @property-read string $type
 */
abstract class BaseAuthentificator
{
    abstract public function sendCode();
    abstract public function getType();

    public function validateCode(string $code){
        $this->onCorrectCode();
        return true;
    }

    /**
     * @param Auth2FaValidation $auth2FaValidationModel
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function onCorrectCode(Auth2FaValidation $auth2FaValidationModel){
        $auth2FaValidationModel->saveOrPanic();
    }
}