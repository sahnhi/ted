<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Table(name: 'app_product_cms')]
#[ORM\Entity]
class ProductCMS implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubTitle(): string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getSubTitle();
    }

    public function setSubTitle(string $subTitle): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setSubTitle($subTitle);

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ProductCMSTranslation();
    }

    public function getSubInfo(): ?string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getSubInfo();
    }

    public function setSubInfo(?string $subInfo): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setSubInfo($subInfo);

        return $this;
    }

    public function getSubInfoBis(): ?string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getSubInfoBis();
    }

    public function setSubInfoBis(?string $subInfoBis): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setSubInfoBis($subInfoBis);

        return $this;
    }

    public function getProductDetailsTitle(): ?string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getProductDetailsTitle();
    }

    public function setProductDetailsTitle(?string $productDetailsTitle): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setProductDetailsTitle($productDetailsTitle);

        return $this;
    }

    public function getProductDetailsSubTitle(): ?string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getProductDetailsSubTitle();
    }

    public function setProductDetailsSubTitle(?string $productDetailsSubTitle): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setProductDetailsSubTitle($productDetailsSubTitle);

        return $this;
    }

    public function getProductDetailsSubClaim(): ?string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getProductDetailsSubClaim();
    }

    public function setProductDetailsSubClaim(?string $productDetailsSubClaim): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setProductDetailsSubClaim($productDetailsSubClaim);

        return $this;
    }

    public function getProductDetailsSubTitleBis(): ?string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getProductDetailsSubTitleBis();
    }

    public function setProductDetailsSubTitleBis(?string $productDetailsSubTitleBis): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setProductDetailsSubTitleBis($productDetailsSubTitleBis);

        return $this;
    }

    public function getProductDetailsSubClaimBis(): ?string
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getProductDetailsSubClaimBis();
    }

    public function setProductDetailsSubClaimBis(?string $productDetailsSubClaimBis): self
    {
        /**
         * @var ProductCMSTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setProductDetailsSubClaimBis($productDetailsSubClaimBis);

        return $this;
    }
}
