<?php


namespace auth\components\captcha;


class ReCaptchaV3 implements CaptchaComponentInterface
{
    public function validate(string $token): bool
    {
        // @todo
        return true;
    }
}