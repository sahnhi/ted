<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Services\OrderReturn\EnumOrderReturnDestination;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

#[ORM\Table(name: 'sylius_order_return_item')]
#[ORM\Entity]
class OrderReturnItem implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'quantity', type: 'integer', nullable: true)]
    private int $quantity = 0;

    #[ORM\Column(name: 'destination', type: 'string', nullable: false)]
    private ?string $destination = null;

    #[ORM\JoinColumn(name: 'orderReturn_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: OrderReturn::class, inversedBy: 'items', cascade: ['persist'])]
    private ?OrderReturn $orderReturn = null;

    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    private ?ProductVariantInterface $variant = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderReturn(): ?OrderReturn
    {
        return $this->orderReturn;
    }

    public function setOrderReturn(?OrderReturn $orderReturn): void
    {
        $this->orderReturn = $orderReturn;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    public function setVariant(?ProductVariantInterface $variant): void
    {
        $this->variant = $variant;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): void
    {
        Assert::inArray($destination, EnumOrderReturnDestination::ALL_DESTINATION_TYPE);
        $this->destination = $destination;
    }
}
