<?php

declare(strict_types=1);

namespace App\Api\AdyenPayByLink\Query;

final class AdyenPayByLinkQuery
{
    public function __construct(
        private readonly int $amount,
        private readonly string $currency,
        private readonly string $merchantAccount,
        private readonly string $shopperLocale,
        private readonly string $countryCode,
        private readonly string $customerEmail,
        private readonly string $paymentReference,
        private readonly \DateTimeInterface $expiracyDate,
    ) {
    }

    public function toArray(): array
    {
        return [
            'reference' => $this->paymentReference,
            'amount' => [
                'value' => $this->amount,
                'currency' => $this->currency,
            ],
            'shopperReference' => $this->customerEmail,
            'countryCode' => $this->countryCode,
            'merchantAccount' => $this->merchantAccount,
            'shopperLocale' => $this->shopperLocale,
            'expiresAt' => $this->expiracyDate->format('c'),
        ];
    }
}
