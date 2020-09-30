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
     * @param $auth2FaValidationModel
     */
    public function onCorrectCode($auth2FaValidationModel){
        $auth2FaValidationModel->saveOrPanic();
    }
}