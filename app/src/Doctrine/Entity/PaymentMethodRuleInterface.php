<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface PaymentMethodRuleInterface extends ResourceInterface
{
    public const TYPE_SUPERIOR = '>';
    public const TYPE_INFERIOR = '<';

    public function getId(): ?int;

    public function getType(): ?string;

    public function setType(string $type): void;

    public function getAmount(): ?int;

    public function setAmount(int $amount): void;

    public function getPaymentMethod(): ?PaymentMethodInterface;

    public function setPaymentMethod(?PaymentMethodInterface $paymentMethod): void;
}
