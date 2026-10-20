<?php

namespace App\Payum\WebTPE;

use Payum\Be2Bill\Api as BaseApi;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;

class Api extends BaseApi
{
    public const VERSION = '3.0';

    public const EXECCODE_WAITING_PROVIDER = '0002';

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://secure-test.be2bill.com/front/service/webtpe/prepare' :
            'https://secure-magenta1.be2bill.com/front/service/webtpe/prepare'
        ;
    }

    /**
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false === ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = json_decode($response->getBody()->getContents());
        if (null === $result) {
            throw new LogicException("Response content is not valid json: \n\n{$response->getBody()->getContents()}");
        }

        return $result;
    }

    protected function addGlobalParams(array &$params)
    {
        $params['VERSION'] = self::VERSION;
        $params['IDENTIFIER'] = $this->options['identifier'];
        $params['HASH'] = $this->calculateHash($params);
    }

    /**
     * @return array
     */
    public function payment(array $params)
    {
        $params['OPERATIONTYPE'] = static::OPERATION_PAYMENT;

        unset($params['EXTRADATA'], $params[0]);

        $this->addGlobalParams($params);

        return $this->doRequest([
            'method' => 'payment',
            'params' => $params,
        ]);
    }

    /**
     * Verify if the hash of the given parameter is correct.
     *
     * @return bool
     */
    public function verifyHash(array $params)
    {
        if (empty($params['HASH'])) {
            return false;
        }

        $hash = $params['HASH'];
        unset($params['HASH']);

        return $hash === $this->calculateHash($params);
    }

    /**
     * @return string
     */
    public function calculateHash(array $params)
    {
        // Alpha sort
        ksort($params);

        $clearString = $this->options['password'];
        foreach ($params as $key => $value) {
            $clearString .= $key . '=' . $value . $this->options['password'];
        }

        return hash('sha256', (string) $clearString);
    }
}
