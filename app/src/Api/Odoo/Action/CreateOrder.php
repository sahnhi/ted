<?php

declare(strict_types=1);

namespace App\Api\Odoo\Action;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Api\Odoo\Exception\OdooApiResponseException;
use App\Api\Odoo\Factory\OrderFactoryInterface;
use App\Api\Odoo\Message\Odoo;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;

final class CreateOrder
{
    public function __construct(
        private readonly OdooApiInterface $odooApi,
        private readonly OrderFactoryInterface $orderFactory,
    ) {
    }

    public function __invoke(BaseOrder $order): void
    {
        $odooOrder = $this->orderFactory->createNew(syliusOrder: $order);
        $message = new Odoo($odooOrder, OdooApiInterface::API_CALL_NEW_ORDER);
        $response = $this->odooApi->request(
            subject: $message->getSubject(),
            apiCall: $message->getApiCall(),
            method: $message->getMethod()
        );

        if (OdooApiInterface::API_SUCCESS_MESSAGE !== $response) {
            throw new OdooApiResponseException($response);
        }
    }
}
