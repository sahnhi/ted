<?php

namespace App\Payum\Dalenys;

use App\Payum\Dalenys\Action\CaptureAction;
use App\Payum\Dalenys\Action\ConvertPaymentAction;
use App\Payum\Dalenys\Action\NotifyAction;
use App\Payum\Dalenys\Action\StatusAction;
use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Symfony\Component\Messenger\MessageBus;

class Dalenys3TimesGatewayFactory extends Be2BillDirectGatewayFactory
{
    public const FACTORY_NAME = 'dalenys_3times';

    protected function populateConfig(ArrayObject $config)
    {
        parent::populateConfig($config);

        $config->replace([
            'payum.factory_name' => 'dalenys_3times',
            'payum.factory_title' => 'Dalenys 3 times',
            'payum.template.obtain_credit_card' => '@App/Payum/Dalenys/obtainCreditCard.html.twig',
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            //  see service definition
            //  app.factory.gateway.dalenys.action.capture
            //  app.factory.gateway.dalenys.action.notify

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify' => new NotifyAction(new MessageBus()),
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
