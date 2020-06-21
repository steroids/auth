<?php

namespace steroids\auth\providers;

use League\OAuth2\Client\Provider\Facebook;
use Exception;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;
use steroids\auth\AuthProfile;

/**
 * Создание приложения https://developers.facebook.com/apps/
 *
 * OAuth client: https://github.com/thephpleague/oauth2-facebook
 */
class FacebookAuthProvider extends BaseAuthProvider
{
    const API_VERSION = 'v2.10';

    public string $clientId;

    public string $clientSecret;

    /**
     * @var Facebook
     */
    private $api;

    /**
     * @var AuthProfile
     */
    private $profileData;

    /**
     * @param array $params
     * @return AuthProfile
     * @throws Exception
     */
    public function auth(array $params): AuthProfile
    {
        $accessToken = $params['token'] ?? null;

        if (!$accessToken) {
            throw new Exception('FB API call isn\'t possible without token');
        }

        if (!$this->profileData) {
            $token = new AccessToken(['access_token' => $accessToken]);

            /** @var FacebookUser $user */
            $user = $this->getApi()->getResourceOwner($token);

            $this->profileData = new AuthProfile([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'avatarUrl' => $user->getPictureUrl()
            ]);
        }

        return $this->profileData;
    }

    public function getClientConfig()
    {
        return [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'graphApiVersion' => static::API_VERSION,
        ];
    }

    private function getApi(): Facebook
    {
        if (!$this->api) {
            $this->api = new Facebook($this->getClientConfig());
        }

        return $this->api;
    }
}
