<?php

namespace App\Factory;

class GoogleCloudStorageServiceFactory
{
    public $credentials;

    public function __construct(string $credentials)
    {
        $this->credentials = $credentials;
    }

    public function createService()
    {
        $client = new \Google_Client();

        $client->setAuthConfig($this->credentials);
        $client->setScopes('https://www.googleapis.com/auth/devstorage.read_write');

        return new \Google_Service_Storage($client);
    }
}
