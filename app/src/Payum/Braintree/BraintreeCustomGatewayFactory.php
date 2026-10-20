<?php

namespace App\Payum\Braintree;

use App\Payum\Braintree\Action\ConvertPaymentAction;
use Payum\Braintree\BraintreeGatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;

final class BraintreeCustomGatewayFactory extends BraintreeGatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        parent::populateConfig($config);

        $config->replace([
            'payum.template.obtain_payment_method_nonce' => '@App/Payum/Braintree/obtain_payment_method_nonce.html.twig',
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);
    }
}
