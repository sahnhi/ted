<?php

namespace App\Payum\Younited;

use App\Bridge\YounitedBridgeInterface;
use App\Payum\Younited\Action\ConvertPaymentAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class YounitedGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'younited',
            'payum.factory_title' => 'Younited',
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.http_client' => '@app.bridge.younited_bridge',
        ]);

        if (null === $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => '',
                'client_id' => '',
                'client_secret' => '',
                'resource' => '',
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['environment', 'client_id', 'client_secret', 'resource'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var YounitedBridgeInterface $younitedBridge */
                $younitedBridge = $config['payum.http_client'];

                $younitedBridge->setEnvironment($config['environment']);
                $younitedBridge->setClientId($config['client_id']);
                $younitedBridge->setClientSecret($config['client_secret']);
                $younitedBridge->setResource($config['resource']);

                return $younitedBridge;
            };
        }
    }
}
