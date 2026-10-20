<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Payum\Core\Model\GatewayConfigInterface;

final class PaymentMethodDTO extends ArrayCollection
{
    public $id;

    /** @var string */
    public $code;

    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

    /** @var string */
    public $environment;

    /** @var int */
    public $position;

    /** @var Collection */
    public $channels;

    /** @var GatewayConfigInterface */
    public $gatewayConfig;

    /** @var ArrayCollection */
    public $rules;

    public function __construct(PaymentMethodInterface $paymentMethod)
    {
        $this->id = $paymentMethod->getId();
        $this->code = $paymentMethod->getCode();
        $this->name = $paymentMethod->getName();
        $this->description = $paymentMethod->getDescription();
        $this->environment = $paymentMethod->getEnvironment();
        $this->position = $paymentMethod->getPosition();
        $this->channels = $paymentMethod->getChannels();
        $this->gatewayConfig = $paymentMethod->getGatewayConfig();
        $this->rules = $paymentMethod->getRules();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @return Collection
     */
    public function getChannels()
    {
        return $this->channels;
    }

    public function getGatewayConfig(): ?GatewayConfigInterface
    {
        return $this->gatewayConfig;
    }

    /**
     * @return ArrayCollection
     */
    public function getRules()
    {
        return $this->rules;
    }
}
