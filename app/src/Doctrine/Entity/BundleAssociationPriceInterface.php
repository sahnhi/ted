<?php

namespace App\Doctrine\Entity;

interface BundleAssociationPriceInterface
{
    public function getId(): ?int;

    public function getPrice(): int;

    public function setPrice(?int $price): void;

    public function getCreatedAt(): \DateTimeInterface;

    public function setCreatedAt(\DateTimeInterface $createdAt): void;

    public function getBundleAssociation(): BundleAssociationInterface;

    public function setBundleAssociation(BundleAssociationInterface $bundleAssociation): void;
}
