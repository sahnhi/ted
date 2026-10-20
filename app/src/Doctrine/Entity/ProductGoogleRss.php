<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\ProductGoogleRssRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_product_google_rss')]
#[ORM\Entity(repositoryClass: ProductGoogleRssRepository::class)]
class ProductGoogleRss implements ProductGoogleRssInterface, ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'sku', type: 'string', length: 255, nullable: true)]
    private ?string $sku = null;

    #[ORM\Column(name: 'gtin', type: 'string', length: 255, nullable: true)]
    private ?string $gtin = null;

    #[ORM\Column(name: 'mpn', type: 'string', length: 255, nullable: true)]
    private ?string $mpn = null;

    #[ORM\Column(name: 'url_review', type: 'string', length: 255, nullable: true)]
    private ?string $urlReview = null;

    #[ORM\Column(name: 'product_url', type: 'string', length: 255, nullable: true)]
    private ?string $productUrl = null;

    #[ORM\OneToOne(mappedBy: 'productGoogleRss', targetEntity: Product::class)]
    private ?ProductInterface $product = null;

    #[ORM\OneToOne(mappedBy: 'productGoogleRss', targetEntity: ProductVariant::class)]
    private ?ProductVariantInterface $variant = null;

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function setGtin(?string $gtin): void
    {
        $this->gtin = $gtin;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): void
    {
        $this->mpn = $mpn;
    }

    public function getUrlReview(): ?string
    {
        return $this->urlReview;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): void
    {
        $this->product = $product;
    }

    public function setUrlReview(?string $urlReview): void
    {
        $this->urlReview = $urlReview;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    public function setVariant(?ProductVariantInterface $variant): void
    {
        $this->variant = $variant;
    }

    public function getProductUrl(): ?string
    {
        return $this->productUrl;
    }

    public function setProductUrl(?string $productUrl): void
    {
        $this->productUrl = $productUrl;
    }

    public function getEan13(): ?int
    {
        return $this->getVariant()->getEan13();
    }
}
