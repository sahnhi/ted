<?php

declare(strict_types=1);

namespace App\Api\Odoo\Action;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Api\Odoo\Exception\OdooApiResponseException;
use App\Doctrine\Entity\Sylius\Order;

final class CancelOrder
{
    public function __construct(
        private readonly OdooApiInterface $odooApi,
    ) {
    }

    public function __invoke(Order $order): void
    {
        $response = $this->odooApi->request(
            subject: null,
            apiCall: OdooApiInterface::API_CALL_CANCEL . $order->getNumber(),
            method: 'delete',
        );

        if (OdooApiInterface::API_SUCCESS_MESSAGE !== $response) {
            throw new OdooApiResponseException($response);
        }
    }
}
