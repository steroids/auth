<?php


namespace steroids\auth\components\captcha;


use http\Client;
use yii\db\Exception;

class ReCaptchaV3 implements CaptchaComponentInterface
{
    public string $secretKey;

    /**
     * @param string $token
     * @return bool
     * @throws Exception
     */
    public function validate(string $token): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify' . http_build_query([
                'secret' => $this->secretKey,
                'response' => $token,
            ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!isset($result['success'])) {
            throw new Exception('Invalid recaptcha verify response.');
        }

        return (bool)$result['success'];
    }
}