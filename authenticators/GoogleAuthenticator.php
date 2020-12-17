<?php

namespace steroids\auth\authenticators;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Exceptions\Google2FAException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use steroids\auth\models\AuthTwoFactor;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use yii\base\Exception;
use yii\web\IdentityInterface;

/**
 * Class GoogleAuthenticator
 * @package steroids\auth\authenticators
 * @property-read string $qrCode
 */
class GoogleAuthenticator extends BaseAuthenticator
{
    /**
     * @var string company name that will be shown in Google Authenticator app
     */
    public string $company;

    /**
     * @inheritDoc
     */
    public int $expireSec = 0;

    /**
     * @inheritDoc
     */
    public function start(AuthTwoFactor $twoFactor)
    {
        // Code is not sent in Google Authenticator
        // Only create secret key and save it
        if (!$twoFactor->providerSecret) {
            $twoFactor->providerSecret = (new Google2FA())->generateSecretKey();
        }
    }

    /**
     * @inheritDoc
     */
    public function check(AuthTwoFactor $twoFactor, string $code)
    {
        if (!$twoFactor->providerSecret) {
            return false;
        }

        try {
            $isCodeValid = (new Google2FA())->verifyKey($twoFactor->providerSecret, $code);
        } catch (Google2FAException $e) {
            return false;
        }

        return !!$isCodeValid;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @return string
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function getQrCode($user)
    {
        // Find secret
        $secret = AuthTwoFactor::find()
            ->select('providerSecret')
            ->where([
                'providerName' => $this->name,
                'userId' => $user->getId(),
            ]);
        if (!$secret) {
            throw new Exception('Not found secret key for user');
        }

        // Resolve company name
        $company = $this->company ?: \Yii::$app->name;

        // Generate url
        $url = (new Google2FA())->getQRCodeUrl($company, $user->getName(), $secret);

        // Render image
        $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        return $writer->writeString($url);
    }

}