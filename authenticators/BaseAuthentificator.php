<?php

namespace steroids\auth\authenticators;

use steroids\auth\models\Auth2FaValidation;

abstract class BaseAuthentificator
{
    abstract public function sendCode();
    abstract public function getType();

    public function validateCode(string $code){
        $this->onCorrectCode();
        return true;
    }

    public function onCorrectCode(){
        (new Auth2FaValidation([
            'authentificatorType' => ''
        ]))->saveOrPanic();
    }

}