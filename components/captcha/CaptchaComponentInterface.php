<?php

namespace steroids\auth\components\captcha;

interface CaptchaComponentInterface
{
    /**
     * @param string $token
     * @return bool
     */
    public function validate(string $token): bool;
}