<?php

namespace steroids\auth\providers;

use Google_Client;
use Exception;
use Google_Service_Oauth2;
use steroids\auth\AuthProfile;

/**
 * For get credentials go to https://console.developers.google.com
 *
 * Google PHP-SDK
 * https://developers.google.com/docs/api/quickstart/php
 */
class GoogleAuthProvider extends BaseAuthProvider
{
    public string $clientId;

    public string $clientSecret;

    /**
     * @var Google_Client
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
            throw new Exception('Google API call isn\'t possible without token');
        }

        if (!$this->profileData) {
            $this->getApi()->setAccessToken($accessToken);
            $googleOauth = new Google_Service_Oauth2($this->getApi());
            $accountInfo = $googleOauth->userinfo->get();

            if (!$accountInfo) {
                throw new Exception('Provided id_token for Google is not verified.');
            } else {
                $this->profileData = new AuthProfile([
                    'id' => $accountInfo->id,
                    'name' => $accountInfo->name,
                    'email' => $accountInfo->email,
                    'avatarUrl' => $accountInfo->picture
                ]);
            }
        }

        return $this->profileData;
    }

    public function getClientConfig()
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
    }

    private function getApi(): Google_Client
    {
        if (!$this->api) {
            $this->api = new Google_Client($this->getClientConfig());
        }

        return $this->api;
    }
}
