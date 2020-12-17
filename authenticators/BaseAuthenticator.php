<?php

namespace steroids\auth\authenticators;

use steroids\auth\models\AuthTwoFactor;

/**
 * Class BaseAuthenticator
 * @package steroids\auth\authenticators
 */
abstract class BaseAuthenticator
{
    /**
     * Send code that shall be verified later
     * @param AuthTwoFactor $twoFactor
     * @return mixed
     */
    abstract public function start(AuthTwoFactor $twoFactor);

    /**
     * With success result must call onCorrectCode
     * @param AuthTwoFactor $twoFactor
     * @param string $code
     * @return bool
     */
    abstract public function check(AuthTwoFactor $twoFactor, string $code);

    /**
     * Provider name in config
     * @var string
     */
    public string $name;

    /**
     * Code interval, while its actual
     * @var int
     */
    public int $expireSec = 120;
}