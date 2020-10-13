<?php


namespace steroids\auth\authenticators;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Exceptions\Google2FAException;
use PragmaRX\Google2FA\Google2FA;
use steroids\auth\enums\AuthenticatorEnum;
use steroids\auth\models\UserAuthenticatorKeys;
use Yii;

/**
 * Class GoogleAuthentificator
 * @package steroids\auth\authenticators
 * @property-read string $secretKey
 * @property-read string $qrCode
 */
class GoogleAuthenticator extends BaseAuthenticator
{
    /**
     * Code is not sent in Google Authenticator
     *
     * @inheritDoc
     */
    public function sendCode(string $login)
    {
    }

    public string $company;

    public string $holder;


    public function getType()
    {
        return AuthenticatorEnum::GOOGLE_AUTH;
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
        $userAuthKeys = UserAuthenticatorKeys::findOne([
            'userId' => Yii::$app->user->id,
            'authenticatorType' => AuthenticatorEnum::GOOGLE_AUTH
        ]);

        if(!$userAuthKeys){
            $userAuthKeys = new UserAuthenticatorKeys([
                'userId' => Yii::$app->user->id,
                'secretKey' => (new Google2FA())->generateSecretKey()
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
        $qrCodeUrl = (new Google2FA())->getQRCodeUrl(
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

    /**
     * @inheritDoc
     * @throws \steroids\core\exceptions\ModelSaveException
     */
    public function validateCode(string $code, $login)
    {
        $userAuthKeys = UserAuthenticatorKeys::findOne([
            'userId' => Yii::$app->user->id,
            'authentificatorType' => $this->type
        ]);

        if(!$userAuthKeys){
            return false;
        }

        try {
            $isCodeValid = (new Google2FA())->verifyKey($userAuthKeys->secretKey, $code);
        } catch (Google2FAException $e) {
            return false;
        }

        if(!$isCodeValid){
            return false;
        }

        $this->onCorrectCodeValidation();

        return true;
    }

}