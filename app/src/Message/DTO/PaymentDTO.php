<?php

namespace App\Message\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;

final class PaymentDTO extends ArrayCollection
{
    public $id;

    /** @var PaymentMethodInterface */
    public $method;

    /** @var string */
    public $currencyCode;

    /** @var int */
    public $amount = 0;

    /** @var string */
    public $state = PaymentInterface::STATE_CART;

    /** @var array */
    public $details = [];

    public function __construct(PaymentInterface $payment)
    {
        $this->id = $payment->getId();
        $this->method = new PaymentMethodDTO($payment->getMethod());
        $this->currencyCode = $payment->getCurrencyCode();
        $this->amount = $payment->getAmount();
        $this->state = $payment->getState();
        $this->details = $payment->getDetails();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMethod(): ?PaymentMethodDTO
    {
        return $this->method;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
