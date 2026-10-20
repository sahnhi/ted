<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ProductCMSFaqTrait
{
    #[ORM\OneToOne(mappedBy: 'product', targetEntity: ProductCMSFaq::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'product_cms_faq_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?ProductCMSFaq $productCMSFaq = null;

    public function getProductCMSFaq(): ?ProductCMSFaq
    {
        return $this->productCMSFaq;
    }

    public function setProductCMSFaq(?ProductCMSFaq $productCMSFaq): self
    {
        $this->productCMSFaq = $productCMSFaq;

        return $this;
    }
}
