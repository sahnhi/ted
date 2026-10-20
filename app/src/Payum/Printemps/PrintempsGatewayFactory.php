<?php

namespace App\Payum\Printemps;

use App\Payum\Printemps\Action\ConvertPaymentAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Offline\Action\AuthorizeAction;
use Payum\Offline\Action\RefundAction;
use Payum\Offline\Action\StatusAction;

class PrintempsGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'printemps',
            'payum.factory_title' => 'Printemps',
            'payum.template.capture' => '@App/Payum/Printemps/capture.html.twig',
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        $config['payum.paths'] = array_replace([
            'App' => __DIR__ . '/../../../templates',
        ], $config['payum.paths'] ?: []);
    }
}
