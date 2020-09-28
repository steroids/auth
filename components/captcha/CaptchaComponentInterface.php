<?php


namespace steroids\auth\components\captcha;


interface CaptchaComponentInterface
{
    public function validate(string $token): bool;
}