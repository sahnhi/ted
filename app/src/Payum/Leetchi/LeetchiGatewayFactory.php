<?php

namespace App\Payum\Leetchi;

use App\Payum\Leetchi\Action\ConvertPaymentAction;
use App\Payum\Leetchi\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class LeetchiGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'leetchi',
            'payum.factory_title' => 'leetchi',
            'payum.template.capture' => '@App/Payum/Leetchi/capture.html.twig',
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        $config['payum.paths'] = array_replace([
            'App' => __DIR__ . '/../../../templates',
        ], $config['payum.paths'] ?: []);
    }
}
