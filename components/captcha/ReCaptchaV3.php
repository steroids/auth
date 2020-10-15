<?php


namespace steroids\auth\components\captcha;


use http\Client;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Exception;

class ReCaptchaV3 extends Component implements CaptchaComponentInterface
{
    public string $secretKey;

    public function init()
    {
        if (empty($this->secretKey)) {
            throw new InvalidConfigException('You must provide secret key to use ReCaptchaV3');
        }
    }

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
        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        if (!isset($result->success)) {
            throw new Exception('Invalid recaptcha verify response.');
        }

        return (bool)$result->success;
    }
}