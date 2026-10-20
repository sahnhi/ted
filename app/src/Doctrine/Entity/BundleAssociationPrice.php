<?php

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_bundle_association_price')]
#[ORM\Entity]
class BundleAssociationPrice implements BundleAssociationPriceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'price', type: 'integer', nullable: false)]
    private int $price = 0;

    #[ORM\Column(name: 'createdAt', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\JoinColumn(name: 'bundle_association_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BundleAssociation::class, inversedBy: 'prices')]
    private ?BundleAssociationInterface $bundleAssociation = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(?int $price): void
    {
        $this->price = $price;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getBundleAssociation(): BundleAssociationInterface
    {
        return $this->bundleAssociation;
    }

    public function setBundleAssociation(BundleAssociationInterface $bundleAssociation): void
    {
        $this->bundleAssociation = $bundleAssociation;
    }
}
