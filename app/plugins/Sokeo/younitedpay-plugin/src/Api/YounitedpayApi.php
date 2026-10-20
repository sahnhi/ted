<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

final class YounitedpayApi implements YounitedpayApiInterface
{
    /** @var bool */
    private $sandbox;
    /** @var string */
    private $bearerApiUri;
    /** @var string */
    private $younitedpayApiBaseUrl;
    /** @var string */
    private $publishableKey;
    /** @var string */
    private $privateKey;
    /** @var string */
    private $bearerToken;
    /** @var array */
    private $authorization = [];
    /** @var array */
    private $maturities = [];
    /** @var int */
    private $minAmount = 0;
    /** @var int */
    private $maxAmount = 0;

    public function initialize(\ArrayObject $config): void
    {
        $this->setSandbox($config['sandbox']);
        $this->setPublishableKey(
            $this->sandbox ? $config['apiKeyPublicSandbox'] : $config['apiKeyPublicProd']
        );
        $this->setPrivateKey(
            $this->sandbox ? $config['apiKeyPrivateSandbox'] : $config['apiKeyPrivateProd']
        );
        $this->setBearerApiUri(
            $this->sandbox ? self::BEARER_API_URI_TEST : self::BEARER_API_URI_PROD
        );
        $this->setYounitedpayApiBaseUrl(
            $this->sandbox ? self::YOUNITED_PAY_API_URL_TEST : self::YOUNITED_PAY_API_URL_PROD
        );
        $this->setMaturities(explode(',', $config['monthlyPayments']));
        $this->setMinAmount($config['amountMin']);
        $this->setMaxAmount($config['amountMax']);
    }

    public function getSandbox(): bool
    {
        return $this->sandbox;
    }

    public function setSandbox($sandbox): void
    {
        $this->sandbox = $sandbox;
    }

    public function getBearerApiUri(): string
    {
        return $this->bearerApiUri;
    }

    public function setBearerApiUri($bearerApiUri): void
    {
        $this->bearerApiUri = $bearerApiUri;
    }

    public function getYounitedpayApiBaseUrl(): string
    {
        return $this->younitedpayApiBaseUrl;
    }

    public function setYounitedpayApiBaseUrl($younitedpayApiBaseUrl): void
    {
        $this->younitedpayApiBaseUrl = $younitedpayApiBaseUrl;
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    public function setPublishableKey($publishableKey): void
    {
        $this->publishableKey = $publishableKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function setPrivateKey($privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    public function getMaturities(): array
    {
        return $this->maturities;
    }

    public function setMaturities($maturities): void
    {
        $this->maturities = $maturities;
    }

    public function getMinAmount(): int
    {
        return (int) $this->minAmount;
    }

    public function setMinAmount($minAmount): void
    {
        $this->minAmount = $minAmount;
    }

    public function getMaxAmount(): int
    {
        return (int) $this->maxAmount;
    }

    public function setMaxAmount($maxAmount): void
    {
        $this->maxAmount = $maxAmount;
    }

    public function getGrantType(): string
    {
        return self::GRANT_TYPE;
    }

    public function getScope(): string
    {
        return self::SCOPE;
    }

    public function getAuthorization(): array
    {
        return $this->authorization;
    }

    public function setAuthorization(): void
    {
        if ($this->bearerToken) {
            $this->authorization = [
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Accept' => 'application/json',
            ];
        } else {
            $this->authorization = [];
        }
    }

    public function getBearerToken(): string
    {
        return $this->bearerToken;
    }

    public function setBearerToken($destroy = false): void
    {
        $bearerToken = null;
        if (!$destroy) {
            // Get token from session if has not expired (minus 60 seconds in case of...) and if we haven't switched sandbox since last token
            if (isset($_SESSION['get_token']['expires_at']) and time() < ($_SESSION['get_token']['expires_at'] - 60) and isset($_SESSION['get_token']['access_token'])
            and isset($_SESSION['get_token']['sandbox']) and $_SESSION['get_token']['sandbox'] === $this->sandbox) {
                $bearerToken = $_SESSION['get_token']['access_token'];
            } else {
                // API REQUEST to get a Bearer Token
                $response = $this->executeRequest(
                    self::BEARER_API_BASE_URL,
                    self::BEARER_API_METHOD,
                    $this->bearerApiUri,
                    [
                        'form_params' => [
                            'grant_type' => $this->getGrantType(),
                            'client_id' => $this->getPublishableKey(),
                            'client_secret' => $this->getPrivateKey(),
                            'scope' => $this->getScope(),
                        ],
                    ],
                    true
                );

                if (200 === $response['statusCode']) {
                    if (isset($response['access_token'])) {
                        $bearerToken = $response['access_token'];
                        $_SESSION['get_token'] = [
                            'expires_in' => $response['expires_in'],
                            'created' => time(),
                            'expires_at' => (time() + $response['expires_in']),
                            'access_token' => $response['access_token'],
                            'sandbox' => $this->sandbox,
                        ];
                    }
                }
            }
        }

        $this->bearerToken = $bearerToken;
    }

    public function executeRequest($baseUri, $method, $routeUrl, $options = [], $ignoreAuthorization = false)
    {
        $result = [];
        $statusCode = 000;

        if (!$ignoreAuthorization) {
            $this->setBearerToken();
            $this->setAuthorization();
        }

        $client = new Client([
            'base_uri' => $baseUri,
            'headers' => $this->getAuthorization(),
        ]);

        try {
            $response = $client->request($method, $routeUrl, $options);

            $result = json_decode($response->getBody()->getContents(), true);
            $result = is_null($result) ? ['errors' => ''] : $result;

            $statusCode = $response->getStatusCode();
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            if (null !== $response) {
                $result = (string) $response->getBody();
                $result = json_decode($result, true);
                $result = !is_array($result) ? [$result] : $result;
                $result = ['errors' => current($result)];
                $statusCode = $response->getStatusCode();
            } else {
                $result = ['errors' => $exception->getMessage()];
            }
        }

        return array_merge(['statusCode' => $statusCode], $result);
    }
}
