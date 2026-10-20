<?php

namespace App\Payum\PagaMasTarde;

use App\Payum\PagaMasTarde\Action\CaptureAction;
use App\Payum\PagaMasTarde\Action\ConvertPaymentAction;
use App\Payum\PagaMasTarde\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class PagaMasTardeGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'paga_mas_tarde',
            'payum.factory_title' => 'paga_mas_tarde',
            'payum.template.capture' => '@App/Payum/PagaMasTarde/capture.html.twig',
            'payum.action.capture' => fn (ArrayObject $config): \App\Payum\PagaMasTarde\Action\CaptureAction => new CaptureAction($config['payum.template.capture']),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        $config['payum.paths'] = array_replace([
            'App' => __DIR__ . '/../../Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
