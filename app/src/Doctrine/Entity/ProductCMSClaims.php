<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'app_product_cms_claims')]
#[ORM\Entity]
class ProductCMSClaims implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }
    public const PLACEHOLDER = '-';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'claims')]
    private Product $product;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClaim(?string $locale = null): string
    {
        /**
         * @var ProductCMSClaimsTranslation $trans
         */
        $trans = $this->getTranslation($locale);

        return $trans->getClaim();
    }

    public function setClaim(string $claim, ?string $locale = null): self
    {
        /**
         * @var ProductCMSClaimsTranslation $trans
         */
        $trans = $this->getTranslation($locale);
        $trans->setClaim($claim);

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    protected function createTranslation(): ProductCMSClaimsTranslation
    {
        return new ProductCMSClaimsTranslation();
    }
}
