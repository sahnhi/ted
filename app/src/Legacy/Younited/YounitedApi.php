<?php

namespace App\Legacy\Younited;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class YounitedApi implements YounitedApiInterface
{
    /** @var string */
    private $grantType = self::GRANT_TYPE;

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $apiURL = self::DEVELOPMENT;

    /** @var string */
    private $loginURL = self::LOGIN_URL;

    /** @var array */
    private $authorization = [];

    /** @var array */
    private $parameters = [];

    /**
     * @param        $clientId     string
     * @param        $clientSecret string
     * @param        $resource     string
     * @param string $clientId
     * @param string $clientSecret
     * @param string $resource
     */
    public function __construct(
        private $clientId,
        private $clientSecret,
        private $resource
    ) {
    }

    public function getGrantType(): ?string
    {
        return $this->grantType;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setApiUrl($apiUrl)
    {
        $this->baseUrl = $apiUrl;
    }

    public function getLoginUrl()
    {
        return $this->loginURL;
    }

    public function getAuthorization()
    {
        return $this->authorization;
    }

    public function setAuthorization($bearerToken)
    {
        $this->authorization = [
            'Authorization' => 'Bearer ' . $bearerToken,
            'Accept' => 'application/json',
        ];
    }

    public function setResponse(array $httpRequest)
    {
        $this->parameters = $httpRequest;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setCredentials(array $httpRequest)
    {
        $this->parameters = $httpRequest;
    }

    public function executeRequest($routeUrl = '')
    {
        $result = [];
        $statusCode = 000;

        $headers = $this->getAuthorization();

        $client = new Client([
            'base_uri' => $this->getBaseUrl(),
            'headers' => $headers,
        ]);

        $requestType = (!$this->getAuthorization() ? 'form_params' : 'json');

        try {
            $response = $client->request('POST', $routeUrl, [
                $requestType => $this->getParameters(),
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $result = is_null($result) ? ['errors' => ''] : $result;

            $statusCode = $response->getStatusCode();
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $result = (string) $response->getBody();
            $result = json_decode($result, true);
            $result = !is_array($result) ? [$result] : $result;
            $result = ['errors' => current($result)];

            $statusCode = $response->getStatusCode();
        }

        return array_merge(['statusCode' => $statusCode], $result);
    }
}
