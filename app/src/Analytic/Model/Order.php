<?php

declare(strict_types=1);

namespace App\Analytic\Model;

final class Order
{
    private bool $userAuthenticated = true;
    private string $orderCurrency = 'EUR';

    public function __construct(
        private readonly string $userExternalId,
        private readonly \DateTimeInterface $userCreatedAt,
        private readonly \DateTimeInterface $userSignedUpAt,
        private readonly string $orderExternalId,
        private readonly \DateTimeInterface $orderCreatedAt,
        private readonly string $orderDomain,
        private readonly int $orderSubTotalPrice,
        private readonly int $orderTotalPrice,
        private readonly ?\DateTimeInterface $orderUpdatedAt,
        private readonly ?string $orderCancelReason,
        private readonly OrderItemList $orderItemList
    ) {
    }

    public function userToArray(): array
    {
        return [
            'external_id' => $this->userExternalId,
            'created_at' => $this->userCreatedAt->format(\DateTimeInterface::ATOM),
            'is_authenticated' => $this->userAuthenticated,
            'signed_up_at' => $this->userSignedUpAt->format(\DateTimeInterface::ATOM),
        ];
    }

    public function orderToArray(): array
    {
        return [
            'external_id' => $this->orderExternalId,
            'created_at' => $this->orderCreatedAt->format(DATE_ATOM),
            'domain_id' => $this->orderDomain,
            'subtotal_price' => $this->orderSubTotalPrice,
            'total_price' => $this->orderTotalPrice,
            'updated_at' => $this->orderUpdatedAt->format(DATE_ATOM),
            'currency' => $this->orderCurrency,
            'cancel_reason' => $this->orderCancelReason,
            'items' => $this->orderItemList->toArray(),
        ];
    }
}
