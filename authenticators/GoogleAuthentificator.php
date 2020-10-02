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

/**
 * Class GoogleAuthentificator
 * @package steroids\auth\authenticators
 * @property-read string $secretKey
 * @property-read string $qrCode
 */
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

    /**
     * @return string
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function getSecretKey()
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

        return $userAuthKeys->secretKey;
    }

    /**
     * @return string
     */
    public function getQrCode()
    {
        $google2fa = new Google2FA();

        //generate QR code
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $this->company,
            $this->holder,
            $this->secretKey
        );

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    public function validateCode(string $code, $login)
    {
        $userAuthKeys = UserAuthentificatorKeys::findOne([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => $this->type
        ]);

        if(!$userAuthKeys){
            return false;
        }

        $isCodeValid = (new Google2FA())->verifyKey($userAuthKeys->secretKey, $code);

        if(!$isCodeValid){
            return false;
        }

        $this->onCorrectCode(new Auth2FaValidation([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => $this->type
        ]));

        return true;
    }

}