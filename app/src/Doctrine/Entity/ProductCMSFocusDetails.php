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
#[ORM\Table(name: 'app_product_cms_focus_details')]
class ProductCMSFocusDetails implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProductCMSFocus::class, inversedBy: 'productCMSFocusDetails')]
    #[ORM\JoinColumn(name: 'product_cms_focus_id', referencedColumnName: 'id')]
    private ProductCMSFocus $productCMSFocus;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTerm(): ?string
    {
        /**
         * @var ProductCMSFocusDetailsTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getTerm();
    }

    public function setTerm(?string $term): self
    {
        /**
         * @var ProductCMSFocusDetailsTranslations $trans
         */
        $trans = $this->getTranslation();

        $trans->setTerm($term);

        return $this;
    }

    public function getDescription(): ?string
    {
        /**
         * @var ProductCMSFocusDetailsTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getDescription();
    }

    public function setDescription(?string $description): self
    {
        /**
         * @var ProductCMSFocusDetailsTranslations $trans
         */
        $trans = $this->getTranslation();

        $trans->setDescription($description);

        return $this;
    }

    public function getProductCMSFocus(): ProductCMSFocus
    {
        return $this->productCMSFocus;
    }

    public function setProductCMSFocus(ProductCMSFocus $productCMSFocus): self
    {
        $this->productCMSFocus = $productCMSFocus;

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ProductCMSFocusDetailsTranslations();
    }
}
