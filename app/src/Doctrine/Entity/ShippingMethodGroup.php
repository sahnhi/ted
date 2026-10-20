<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_shipping_method_group')]
class ShippingMethodGroup implements ResourceInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int|null $id = null;

    #[ORM\Column(name: 'code', type: 'string', nullable: false)]
    private string $code;

    /** @var Collection<ShippingMethod> */
    #[ORM\OneToMany(mappedBy: 'shippingMethodGroup', targetEntity: ShippingMethod::class)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $shippingMethods;

    #[ORM\Column(name: 'max_per_day', type: 'integer', nullable: true)]
    private ?int $maxPerDay = null;

    #[ORM\Column(name: 'max_per_slot', type: 'integer', nullable: true)]
    private ?int $maxPerSlot = null;

    public function __construct()
    {
        $this->shippingMethods = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getCode();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getShippingMethods(): Collection
    {
        return $this->shippingMethods;
    }

    public function setShippingMethods(Collection $shippingMethods): self
    {
        $this->shippingMethods = $shippingMethods;

        return $this;
    }

    public function addShippingMethod(ShippingMethod $shippingMethod): self
    {
        if (!$this->shippingMethods->contains($shippingMethod)) {
            $shippingMethod->setShippingMethodGroup($this);
            $this->shippingMethods->add($shippingMethod);
        }

        return $this;
    }

    public function removeShippingMethod(ShippingMethod $shippingMethod): self
    {
        if ($this->shippingMethods->contains($shippingMethod)) {
            $this->shippingMethods->removeElement($shippingMethod);
            $shippingMethod->setShippingMethodGroup(null);
        }

        return $this;
    }

    public function getMaxPerDay(): ?int
    {
        return $this->maxPerDay;
    }

    public function setMaxPerDay(?int $maxPerDay): self
    {
        $this->maxPerDay = $maxPerDay;

        foreach ($this->shippingMethods as $shippingMethod) {
            $shippingMethod->setMaxPerDay($maxPerDay);
        }

        return $this;
    }

    public function getMaxPerSlot(): ?int
    {
        return $this->maxPerSlot;
    }

    public function setMaxPerSlot(?int $maxPerSlot): self
    {
        $this->maxPerSlot = $maxPerSlot;

        foreach ($this->shippingMethods as $shippingMethod) {
            $shippingMethod->setMaxPerSlot($maxPerSlot);
        }

        return $this;
    }
}
