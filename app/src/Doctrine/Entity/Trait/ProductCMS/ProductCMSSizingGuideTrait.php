<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Trait\ProductCMS;

use App\Doctrine\Entity\ProductCMSSizingGuide;
use Doctrine\ORM\Mapping as ORM;

trait ProductCMSSizingGuideTrait
{
    #[ORM\ManyToOne(targetEntity: ProductCMSSizingGuide::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'product_cms_sizing_guide_id', referencedColumnName: 'id')]
    private ?ProductCMSSizingGuide $productCMSSizingGuide = null;

    public function getProductCMSSizingGuide(): ?ProductCMSSizingGuide
    {
        return $this->productCMSSizingGuide;
    }

    public function setProductCMSSizingGuide(?ProductCMSSizingGuide $productCMSSizingGuide): self
    {
        $this->productCMSSizingGuide = $productCMSSizingGuide;

        return $this;
    }
}
