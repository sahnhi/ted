<?php

namespace App\Payum\WebTPE;

use App\Payum\WebTPE\Action\ConvertPaymentAction;
use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;

class DalenysWebTpeGatewayFactory extends Be2BillDirectGatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        parent::populateConfig($config);

        $config->replace([
            'payum.factory_name' => 'dalenys_webtpe',
            'payum.factory_title' => 'Dalenys Web TPE',
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            // see service definition
            // app.factory.gateway.webtpe.action.capture
            // app.factory.gateway.webtpe.action.notify
            // app.factory.gateway.webtpe.action.status

            'payum.api' => function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api(
                    ['identifier' => $config['identifier'], 'password' => $config['password'], 'sandbox' => $config['sandbox']],
                    $config['payum.http_client'],
                    $config['httplug.message_factory']
                );
            },
        ]);
    }
}
