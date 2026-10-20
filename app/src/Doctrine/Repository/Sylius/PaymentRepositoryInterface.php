<?php

namespace App\Doctrine\Repository\Sylius;

use App\Utils\DateRange;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface as BasePaymentRepositoryInterface;

interface PaymentRepositoryInterface extends BasePaymentRepositoryInterface
{
    public function findByLikeRequestIdAndAmountInDetails($requestId, $amount): ?PaymentInterface;

    public function findByOrderNumberAndCompletedState($orderNumber): ?PaymentInterface;

    /**
     * @return PaymentInterface[]
     */
    public function getOdooRefund(): array;

    public function findByDateRangeAmountAndSellerName(DateRange $dateRange, int $amount, string $lastName, ?string $firstName): ?PaymentInterface;
}
