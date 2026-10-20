<?php

declare(strict_types=1);

namespace App\PaymentReport\Model;

final class Payment
{
    /** @var ?string */
    private $firstName;
    /** @var string */
    private $lastName;

    public function __construct(
        private readonly string $merchantReference,
        ?string $firstName,
        string $lastName,
        private readonly int $amount,
        private readonly string $createdAt,
        private readonly string $pspReference,
        private readonly string $resultCode
    ) {
        $this->firstName = null !== $firstName ? trim($firstName) : null;
        $this->lastName = trim($lastName);
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['merchantReference'],
            $state['firstName'],
            $state['lastName'],
            $state['amount'],
            $state['createdAt'],
            $state['pspReference'],
            $state['resultCode']
        );
    }

    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->createdAt);
    }

    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function toArray(): array
    {
        return [
            'merchantReference' => $this->merchantReference,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt,
            'pspReference' => $this->pspReference,
            'resultCode' => $this->resultCode,
        ];
    }
}
