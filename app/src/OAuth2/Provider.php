<?php

namespace App\OAuth2;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

class Provider
{
    /** @var GenericProvider */
    private $provider;
    /** @var AccessTokenInterface */
    private $token;

    public function getToken(): AccessTokenInterface
    {
        return $this->token;
    }

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $urlAuthorize,
        string $urlAccessToken
    ) {
        $this->provider = new GenericProvider([
            'clientId' => $clientId,    // The client ID assigned to you by the provider
            'clientSecret' => $clientSecret,    // The client password assigned to you by the provider
            'redirectUri' => 'https://www.tediber.com/remerciement-avis',
            'urlAuthorize' => $urlAuthorize,
            'urlAccessToken' => $urlAccessToken,
            'urlResourceOwnerDetails' => '',
        ]);
    }

    public function connect(
        string $login,
        string $password
    ): void {
        try {
            // Try to get an access token using the resource owner password credentials grant.
            $accessToken = $this->provider->getAccessToken('password', [
                'username' => $login,
                'password' => $password,
            ]);

            $this->token = $accessToken;
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token
            exit($e->getMessage());
        }
    }

    public function refresh()
    {
        $existingAccessToken = $this->token;

        if ($existingAccessToken->hasExpired()) {
            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $existingAccessToken->getRefreshToken(),
            ]);

            $this->token = $newAccessToken;
        }
    }
}
