<?php

declare(strict_types=1);

namespace App\Api\Odoo\Action;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Api\Odoo\Exception\OdooApiResponseException;
use App\Api\Odoo\Factory\OrderFactoryInterface;
use App\Api\Odoo\Message\Odoo;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Entity\Sylius\OrderInterface;
use Doctrine\ORM\EntityManagerInterface;

final class CreateReturnOrder
{
    public function __construct(
        private readonly OdooApiInterface $odooApi,
        private readonly OrderFactoryInterface $orderFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(BaseOrder $order): void
    {
        $odooOrder = $this->orderFactory->createNewOrderReturn(syliusOrder: $order);
        $this->clearLogisticItemsFromOrder($order);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $message = new Odoo($odooOrder, OdooApiInterface::API_CALL_RETURN);
        $response = $this->odooApi->request(
            subject: $message->getSubject(),
            apiCall: $message->getApiCall(),
            method: $message->getMethod()
        );

        if (OdooApiInterface::API_SUCCESS_MESSAGE !== $response) {
            throw new OdooApiResponseException($response);
        }
    }

    private function clearLogisticItemsFromOrder(OrderInterface $order): void
    {
        foreach ($order->getItems() as $item) {
            if (null === $item->getId()) {
                $order->removeItem($item);
            }
        }
    }
}
