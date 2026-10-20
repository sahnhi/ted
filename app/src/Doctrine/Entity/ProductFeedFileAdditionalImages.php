<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_product_feed_file_additional_images')]
class ProductFeedFileAdditionalImages implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $url;

    #[ORM\ManyToOne(targetEntity: ProductFeedFile::class)]
    #[ORM\JoinColumn(name: 'product_feed_file_id', nullable: false)]
    private ProductFeedFile $productFeedFile;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getProductFeedFile(): ProductFeedFile
    {
        return $this->productFeedFile;
    }

    public function setProductFeedFile(ProductFeedFile $productFeedFile): self
    {
        $this->productFeedFile = $productFeedFile;

        return $this;
    }
}
