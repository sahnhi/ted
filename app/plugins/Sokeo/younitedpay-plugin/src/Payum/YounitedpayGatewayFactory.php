<?php

/*
 * This file was created by developers working at Sokeo
 * Do you need more information about us and what we do? Visit our https://sokeo.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://sokeo.io/career
*/

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApi;
use Sokeo\SyliusYounitedpayPlugin\Payum\Action\StatusAction;

final class YounitedpayGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'younitedpay';
    public const FACTORY_TITLE = 'YounitedPay';

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => self::FACTORY_TITLE,
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            $younitedpayApi = new YounitedpayApi();
            $younitedpayApi->initialize($config);

            return $younitedpayApi;
        };
    }
}
