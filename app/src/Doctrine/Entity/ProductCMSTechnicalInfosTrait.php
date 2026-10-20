<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait ProductCMSTechnicalInfosTrait
{
    #[ORM\OneToMany(targetEntity: ProductCMSTechnicalInfos::class, mappedBy: 'product', cascade: ['all'])]
    private Collection $productCMSTechnicalInfos;

    public function __construct()
    {
        $this->productCMSTechnicalInfos = new ArrayCollection();
    }

    public function getProductCMSTechnicalInfos(): Collection
    {
        return $this->productCMSTechnicalInfos;
    }

    public function addProductCMSTechnicalInfo(ProductCMSTechnicalInfos $productCMSTechnicalInfos): self
    {
        if (!$this->hasProductCMSTechnicalInfos($productCMSTechnicalInfos)) {
            $productCMSTechnicalInfos->setProduct($this);
            $this->productCMSTechnicalInfos->add($productCMSTechnicalInfos);
        }

        return $this;
    }

    public function removeProductCMSTechnicalInfo(ProductCMSTechnicalInfos $productCMSTechnicalInfos): self
    {
        if ($this->hasProductCMSTechnicalInfos($productCMSTechnicalInfos)) {
            $this->productCMSTechnicalInfos->removeElement($productCMSTechnicalInfos);
        }

        return $this;
    }

    public function hasProductCMSTechnicalInfos(ProductCMSTechnicalInfos $productCMSTechnicalInfos): bool
    {
        return $this->productCMSTechnicalInfos->contains($productCMSTechnicalInfos);
    }

    public function clearProductCMSTechnicalInfos(): self
    {
        foreach ($this->productCMSTechnicalInfos as $productCMSTechnicalInfos) {
            $this->removeProductCMSTechnicalInfo($productCMSTechnicalInfos);
        }

        return $this;
    }
}
