<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_product_cms_sizing_guide_item')]
class ProductCMSSizingGuideItem implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationCollection;
    }

    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProductCMSSizingGuide::class, inversedBy: 'productCMSSizingGuideItems')]
    #[ORM\JoinColumn(name: 'product_cms_sizing_guide_id', referencedColumnName: 'id')]
    private ProductCMSSizingGuide $productCMSSizingGuide;

    public function __construct()
    {
        $this->initializeTranslationCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->getTrans()->getTitle();
    }

    public function setTitle(?string $title): self
    {
        $this->getTrans()->setTitle($title);

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->getTrans()->getImagePath();
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->getTrans()->setImagePath($imagePath);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->getTrans()->getDescription();
    }

    public function setDescription(?string $description): self
    {
        $this->getTrans()->setDescription($description);

        return $this;
    }

    public function getTrans(): ProductCMSSizingGuideItemTranslation
    {
        /** @var ProductCMSSizingGuideItemTranslation $translation */
        $translation = $this->getTranslation();

        return $translation;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ProductCMSSizingGuideItemTranslation();
    }

    public function getProductCMSSizingGuide(): ProductCMSSizingGuide
    {
        return $this->productCMSSizingGuide;
    }

    public function setProductCMSSizingGuide(ProductCMSSizingGuide $productCMSSizingGuide): self
    {
        $this->productCMSSizingGuide = $productCMSSizingGuide;

        return $this;
    }
}
