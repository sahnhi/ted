<?php

namespace App\Payum\Adyen;

use App\Bridge\AdyenBridge;
use App\Bridge\AdyenBridgeInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class AdyenGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'adyen';

    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'Adyen',
        ]);

        if (null === $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => AdyenBridgeInterface::ENVIRONMENT_DEVELOPMENT,
                'endpointUrlPrefix' => '',
                'merchantAccount' => '',
                'apiKey' => '',
                'clientKey' => '',
                'methodPayment' => '',
                'paypalMerchantId' => '',
                'hmacNotification' => '',
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
                'merchantAccount',
                'apiKey',
                'clientKey',
                'methodPayment',
                'hmacNotification',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new AdyenBridge([
                    'environment' => $config['environment'],
                    'endpointUrlPrefix' => $config['endpointUrlPrefix'],
                    'merchantAccount' => $config['merchantAccount'],
                    'apiKey' => $config['apiKey'],
                    'clientKey' => $config['clientKey'],
                    'methodPayment' => $config['methodPayment'],
                    'paypalMerchantId' => $config['paypalMerchantId'],
                    'hmacNotification' => $config['hmacNotification'],
                ]);
            };
        }
    }
}
