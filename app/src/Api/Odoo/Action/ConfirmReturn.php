<?php

declare(strict_types=1);

namespace App\Api\Odoo\Action;

use App\Api\Odoo\Api\OdooApiInterface;
use App\Api\Odoo\Exception\OdooApiResponseException;
use App\Api\Odoo\Factory\RefundFactoryInterface;
use App\Api\Odoo\Message\Odoo;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\OrderPaymentStates;

final class ConfirmReturn
{
    public function __construct(
        private readonly RefundFactoryInterface $paymentRefundFactory,
        private readonly OdooApiInterface $odooApi,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Order $order): void
    {
        $paymentToRefund = $order->getLastPayment(OrderPaymentStates::STATE_REFUNDED);

        if (null === $paymentToRefund) {
            throw new \InvalidArgumentException('No payment to refund');
        }

        $odooRefund = $this->paymentRefundFactory->createNew($paymentToRefund);
        $message = new Odoo($odooRefund, OdooApiInterface::API_CALL_CONFIRM_RETURN);
        $this->clearLogisticItemsFromOrder($order);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        try {
            $response = $this->odooApi->request(
                subject: $message->getSubject(),
                apiCall: $message->getApiCall(),
                method: $message->getMethod(),
            );
            $this->entityManager->flush();

            if (OdooApiInterface::API_SUCCESS_MESSAGE !== $response) {
                throw new OdooApiResponseException($response);
            }
        } catch (\Exception) {
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
