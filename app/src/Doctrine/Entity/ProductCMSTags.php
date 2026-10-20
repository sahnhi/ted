<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Table(name: 'app_product_cms_tags')]
#[ORM\Entity]
class ProductCMSTags implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    use TimestampableEntity;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'productCMSTags')]
    private ResourceInterface $product;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrimaryTag(): ?string
    {
        /**
         * @var ProductCMSTagsTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getPrimaryTag();
    }

    public function setPrimaryTag(string $primaryTag): self
    {
        /**
         * @var ProductCMSTagsTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setPrimaryTag($primaryTag);

        return $this;
    }

    public function getSecondaryTag(): ?string
    {
        /**
         * @var ProductCMSTagsTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getSecondaryTag();
    }

    public function setSecondaryTag(?string $secondaryTag): self
    {
        /**
         * @var ProductCMSTagsTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setSecondaryTag($secondaryTag);

        return $this;
    }

    public function getProduct(): ResourceInterface
    {
        return $this->product;
    }

    public function setProduct(ResourceInterface $product): self
    {
        $this->product = $product;

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ProductCMSTagsTranslation();
    }
}
