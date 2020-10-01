<?php


namespace steroids\auth\authenticators;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;
use steroids\auth\authenticators\BaseAuthentificator;
use steroids\auth\enums\AuthentificatorEnum;
use steroids\auth\models\Auth2FaValidation;
use steroids\auth\models\UserAuthentificatorKeys;
use Yii;


class GoogleAuthentificator extends BaseAuthentificator
{

    //not use for Google Authentificator
    public function sendCode(string $login)
    {
        return '';
    }

    public string $company;

    public string $holder;


    public function getType()
    {
        return AuthentificatorEnum::GOOGLE_AUTH;
    }

    public function getUser2FaInformation()
    {
        $userAuthKeys = UserAuthentificatorKeys::findOne([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => AuthentificatorEnum::GOOGLE_AUTH
        ]);

        $google2fa = new Google2FA();

        if(!$userAuthKeys){
            $userAuthKeys = new UserAuthentificatorKeys([
                'userId' => Yii::$app->user->id,
                'secretKey' => $google2fa->generateSecretKey()
            ]);

            $userAuthKeys->saveOrPanic();
        }

        //generate QR code
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $this->company,
            $this->holder,
            $userAuthKeys->secretKey
        );

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return [
            'secretKey' => $userAuthKeys->secretKey,
            'qrCodeUrl' => $writer->writeString($qrCodeUrl)
        ];
    }

    public function validateCode(string $code, $login)
    {
        $google2fa = new Google2FA();

        $userAuthKeys = UserAuthentificatorKeys::findOne([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => $this->type
        ]);

        if(!$userAuthKeys){
            return false;
        }

        $valid = $google2fa->verifyKey($userAuthKeys->secretKey, $code, 8);

        if(!$valid){
            return false;
        }

        $this->onCorrectCode(new Auth2FaValidation([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => $this->type
        ]));

        return true;
    }

}