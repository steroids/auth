<?php


namespace steroids\auth\authenticators;

use steroids\auth\authenticators\BaseAuthentificator;

class GoogleAuthentificator extends BaseAuthentificator
{

    public function sendCode()
    {
        return '';
    }

    public function getType()
    {
        return 'GoogleAuthentificator';
    }

    public function validateCode(string $code)
    {
        //@todo реализовать провреку введеного кода через api Google Authentificator
        return true;
    }
}