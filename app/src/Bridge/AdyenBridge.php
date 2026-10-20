<?php

namespace App\Bridge;

use Adyen\Util\HmacSignature;
use Payum\Core\Bridge\Spl\ArrayObject;

final class AdyenBridge implements AdyenBridgeInterface
{
    /** @var ArrayObject */
    protected $options = [
        'environment' => null,
        'endpointUrlPrefix' => null,
        'merchantAccount' => null,
        'wsUser' => null,
        'wsPassword' => null,
        'apiKey' => null,
        'clientKey' => null,
        'methodPayment' => null,
        'paypalMerchantId' => null,
        'hmacNotification' => null,
    ];

    public function __construct(array $options)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty([
            'merchantAccount',
            'apiKey',
            'clientKey',
            'methodPayment',
            'hmacNotification',
        ]);

        $this->options = $options;
    }

    protected function client()
    {
        try {
            $client = new \Adyen\Client();
            $client->setUsername($this->options['wsUser']);
            $client->setPassword($this->options['wsPassword']);
            $client->setEnvironment($this->options['environment'], $this->options['endpointUrlPrefix']);
            $client->setXApiKey($this->options['apiKey']);

            return $client;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function serviceCheckout()
    {
        $client = $this->client();

        return new \Adyen\Service\Checkout($client);
    }

    public function serviceModification()
    {
        $client = $this->client();

        return new \Adyen\Service\Modification($client);
    }

    public function getMerchantAccount(): string
    {
        return $this->options['merchantAccount'];
    }

    public function getClientKey(): string
    {
        return $this->options['clientKey'];
    }

    public function getEnvironment(): string
    {
        return $this->options['environment'];
    }

    public function getMethodPayment(): string
    {
        return $this->options['methodPayment'];
    }

    public function getPaypalMerchantId(): string
    {
        return $this->options['paypalMerchantId'];
    }

    public function getApiUrl(): string
    {
        return $this->options['endpointUrlPrefix'];
    }

    public function getApiKey(): string
    {
        return $this->options['apiKey'];
    }

    public function verifySign(array $params): bool
    {
        if (empty($params['merchantSig'])) {
            return false;
        }

        $merchantSig = $params['merchantSig'];

        unset($params['merchantSig']);

        return $merchantSig === $this->merchantSig($params);
    }

    public function merchantSig(array $params, bool $isNotify = false, string $hmacKey = 'hmacKey'): string
    {
        if (false === $isNotify) {
            ksort($params, SORT_STRING);
        }

        $escapedPairs = [];

        foreach ($params as $key => $value) {
            $escapedPairs[$key] = str_replace(':', '\\:', str_replace('\\', '\\\\', (string) $value));
        }

        if (false === $isNotify) {
            $signingString = implode(':', array_merge(array_keys($escapedPairs), array_values($escapedPairs)));
        } else {
            $signingString = implode(':', array_merge(array_values($escapedPairs)));
        }

        $binaryHmacKey = pack('H*', $this->options[$hmacKey]);

        $binaryHmac = hash_hmac('sha256', $signingString, $binaryHmacKey, true);

        return base64_encode($binaryHmac);
    }

    public function verifyNotification(array $params): bool
    {
        return (new HmacSignature())->isValidNotificationHMAC($this->options['hmacNotification'], $params);
    }
}
