<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Table(name: 'app_product_cms_technical_details')]
#[ORM\Entity]
class ProductCMSTechnicalDetails implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProductCMSTechnicalInfos::class, inversedBy: 'productCMSTechnicalDetails')]
    #[ORM\JoinColumn(name: 'product_cms_technical_infos_id', referencedColumnName: 'id')]
    private ProductCMSTechnicalInfos $productCMSTechnicalInfos;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeader(): string
    {
        /**
         * @var ProductCMSTechnicalDetailsTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getHeader();
    }

    public function setHeader(string $header): self
    {
        /**
         * @var ProductCMSTechnicalDetailsTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setHeader($header);

        return $this;
    }

    public function getText(): string
    {
        /**
         * @var ProductCMSTechnicalDetailsTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getText();
    }

    public function setText(string $text): self
    {
        /**
         * @var ProductCMSTechnicalDetailsTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setText($text);

        return $this;
    }

    public function getProductCMSTechnicalInfos(): ProductCMSTechnicalInfos
    {
        return $this->productCMSTechnicalInfos;
    }

    public function setProductCMSTechnicalInfos(ProductCMSTechnicalInfos $productCMSTechnicalInfos): self
    {
        $this->productCMSTechnicalInfos = $productCMSTechnicalInfos;

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ProductCMSTechnicalDetailsTranslations();
    }
}
