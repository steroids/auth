<?php

namespace steroids\auth\providers;

use steroids\auth\AuthProfile;
use VK\Client\VKApiClient;
use Exception;
use VK\Exceptions\VKClientException;
use VK\Exceptions\VKOAuthException;
use VK\OAuth\VKOAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Создание приложения https://vk.com/editapp?act=create
 *
 * VK PHP-SDK
 * https://vk.com/dev/PHP_SDK
 */
class VkAuthProvider extends BaseAuthProvider
{
    const API_VERSION = '5.92';

    public string $clientId;

    public string $clientSecret;

    /**
     * @var AuthProfile
     */
    private $profileData;

    /**
     * @var VKApiClient
     */
    private $api;

    /**
     * @param array $params
     * @return AuthProfile
     * @throws Exception
     */
    public function auth(array $params): AuthProfile
    {
        $token = $params['token'] ?? null;

        if (!$token) {
            throw new Exception('VK API call isn\'t possible without token');
        }

        if (!$this->profileData) {

            $accessToken = $this->exchangeTempCodeOnAccessToken($token);
            $accountInfo = $this->getApi()->users()->get($accessToken);

            $this->profileData = new AuthProfile([
                'id' => (string)$accountInfo[0]['id'],
                'name' => $accountInfo[0]['first_name'] . ' ' . $accountInfo[0]['last_name'],
                'avatar_url' => ArrayHelper::getValue($accountInfo, "0.photo_max")
            ]);
        }

        return $this->profileData;
    }

    public function getClientConfig(): array
    {
        return [
            'clientId' => $this->clientId,
        ];
    }

    /**
     * Actually, the code we get from VK isn't an access token, but just temporal code,
     * which could be exchanged on real access token.
     * That's what this method does.
     *
     * @param string $tempCode
     * @return mixed
     *
     * @throws VKClientException
     * @throws VKOAuthException
     */
    protected function exchangeTempCodeOnAccessToken(string $tempCode)
    {
        $oauthApi = new VKOAuth();

        $response = $oauthApi->getAccessToken(
            $this->clientId,
            $this->clientSecret,
            Url::to(['/auth/auth/modal-proxy', 'version' => 'v2'], true),
            $tempCode
        );

        return $response['access_token'];
    }

    private function getApi(): VKApiClient
    {
        if (!$this->api) {
            $this->api = new VKApiClient(static::API_VERSION);
        }

        return $this->api;
    }
}
