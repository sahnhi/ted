<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Trait\ProductFeedFile;

use App\Doctrine\Entity\ProductFeedFile;
use Doctrine\ORM\Mapping as ORM;

trait ProductFeedFileTrait
{
    #[ORM\OneToOne(targetEntity: ProductFeedFile::class)]
    #[ORM\JoinColumn(name: 'product_feed_file_id', nullable: true)]
    private ?ProductFeedFile $productFeedFile = null;

    public function getProductFeedFile(): ?ProductFeedFile
    {
        return $this->productFeedFile;
    }

    public function setProductFeedFile(?ProductFeedFile $productFeedFile): self
    {
        $this->productFeedFile = $productFeedFile;

        return $this;
    }
}
