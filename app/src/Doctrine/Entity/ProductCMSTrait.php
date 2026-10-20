<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ProductCMSTrait
{
    #[ORM\OneToOne(targetEntity: ProductCMS::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'product_cms_id', referencedColumnName: 'id', nullable: true)]
    private ?ProductCMS $productCMS = null;

    public function getProductCMS(): ?ProductCMS
    {
        return $this->productCMS;
    }

    public function setProductCMS(?ProductCMS $productCMS): self
    {
        $this->productCMS = $productCMS;

        return $this;
    }
}
