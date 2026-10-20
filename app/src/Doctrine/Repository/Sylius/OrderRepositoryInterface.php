<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\Order;
use App\Identifier\OrderNumber;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface as BaseOrderRepositoryInterface;

interface OrderRepositoryInterface extends BaseOrderRepositoryInterface
{
    public function findInitialOrder(?string $number): ?OrderInterface;

    public function getOrderParent(Order $order): ?OrderInterface;

    public function getBisNumber(string $number): int;

    public function getReturnNumber(string $number): int;

    /**
     * @return array{id: int, checkoutCompletedAt: \DateTime, total: int}
     */
    public function findInvoiceIdForPaidOrderByOrderNumber(OrderNumber $orderNumber): array;

    /**
     * @return array{array{variantName: string, name: string, code: string, unit_price: string, total: string, quantity: int, variantQuantity: int, weight: string}}
     */
    public function findLogisticCodeAndNameForInvoiceByOrderItem(int $orderItemId, string $locale): array;

    public function findAssociatedOrders($number): array;

    public function findTotalWeightInKilograms(string $tokenOrder): ?string;

    public function getOneByTokenValueHydrated(string $tokenValue);

    public function getLastCreatedBisByParentNumber(string $parentNumber): ?OrderInterface;
}
