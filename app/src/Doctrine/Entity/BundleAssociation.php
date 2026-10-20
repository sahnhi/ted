<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_bundle_association')]
#[ORM\Entity]
class BundleAssociation implements BundleAssociationInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'quantity', type: 'integer', nullable: false)]
    private ?int $quantity = 0;

    #[ORM\JoinColumn(name: 'bundle_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class, inversedBy: 'bundleAssociations')]
    private ?ProductVariantInterface $bundle = null;

    #[ORM\OneToMany(mappedBy: 'bundleAssociation', targetEntity: BundleAssociationPrice::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private ?Collection $prices;

    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    private ?ProductVariantInterface $variant = null;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getBundle(): ProductVariantInterface
    {
        return $this->bundle;
    }

    public function setBundle(ProductVariantInterface $bundle): void
    {
        $this->bundle = $bundle;
    }

    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    public function setVariant(?ProductVariantInterface $variant): void
    {
        $this->variant = $variant;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(BundleAssociationPriceInterface $price): void
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
        }
    }

    public function removePrice(BundleAssociationPriceInterface $price): void
    {
        if ($this->prices->contains($price)) {
            $this->prices->removeElement($price);
        }
    }

    public function getPrice(): int
    {
        return $this->getCurrentPriceObject()->getPrice();
    }

    public function getPriceAtDate(?\DateTime $date = null): int
    {
        return $date instanceof \DateTime ?
            $this->getPriceObjectAtDate($date)->getPrice() :
            $this->getCurrentPriceObject()->getPrice();
    }

    public function setPrice(int $price): void
    {
        $currentPrice = $this->getCurrentPriceObject();

        if (null === $currentPrice->getId()) {
            $currentPrice->setPrice($price);
        }

        if ($price !== $currentPrice->getPrice()) {
            $newPrice = new BundleAssociationPrice();
            $newPrice->setPrice($price);
            $newPrice->setBundleAssociation($this);
            $this->addPrice($newPrice);
        }
    }

    public function getCurrentPriceObject(): BundleAssociationPriceInterface
    {
        if ($this->prices->isEmpty()) {
            $newPrice = new BundleAssociationPrice();
            $newPrice->setBundleAssociation($this);
            $this->addPrice($newPrice);
        }

        /** @var BundleAssociationPriceInterface $currentPrice */
        $currentPrice = $this->prices->first();

        /** @var BundleAssociationPriceInterface $price */
        foreach ($this->prices as $price) {
            if ($price->getCreatedAt() > $currentPrice->getCreatedAt()) {
                $currentPrice = $price;
            }
        }

        return $currentPrice;
    }

    public function getPriceObjectAtDate(\DateTime $date): BundleAssociationPriceInterface
    {
        /** @var BundleAssociationPriceInterface $priceAtDate */
        $priceAtDate = $this->prices->last();

        /** @var BundleAssociationPriceInterface $price */
        foreach ($this->prices as $price) {
            if ($date > $price->getCreatedAt()) {
                $priceAtDate = $price;
                break;
            }
        }

        return $priceAtDate;
    }
}
