<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'sylius_payment_method_rule')]
#[ORM\Entity]
class PaymentMethodRule implements PaymentMethodRuleInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'type', type: 'string')]
    private ?string $type = null;

    #[ORM\Column(name: 'amount', type: 'integer')]
    private ?int $amount = null;

    #[ORM\JoinColumn(name: 'payment_method_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: PaymentMethod::class, inversedBy: 'rules')]
    private ?PaymentMethodInterface $paymentMethod = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getPaymentMethod(): ?PaymentMethodInterface
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethodInterface $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }
}
