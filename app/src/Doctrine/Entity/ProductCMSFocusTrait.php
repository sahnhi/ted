<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ProductCMSFocusTrait
{
    #[ORM\OneToOne(targetEntity: ProductCMSFocus::class, mappedBy: 'product', cascade: ['persist'])]
    private ?ProductCMSFocus $productCMSFocus = null;

    public function getProductCMSFocus(): ?ProductCMSFocus
    {
        return $this->productCMSFocus;
    }

    public function setProductCMSFocus(?ProductCMSFocus $productCMSFocus): self
    {
        $this->productCMSFocus = $productCMSFocus;
        $productCMSFocus->setProduct($this);

        return $this;
    }
}
