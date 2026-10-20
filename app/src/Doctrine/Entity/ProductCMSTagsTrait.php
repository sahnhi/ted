<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ProductCMSTagsTrait
{
    #[ORM\OneToOne(targetEntity: ProductCMSTags::class, mappedBy: 'product', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'product_cms_tags_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?ProductCMSTags $productCMSTags = null;

    public function getProductCMSTags(): ?ProductCMSTags
    {
        return $this->productCMSTags;
    }

    public function setProductCMSTags(?ProductCMSTags $productCMSTags): self
    {
        $this->productCMSTags = $productCMSTags;

        return $this;
    }
}
