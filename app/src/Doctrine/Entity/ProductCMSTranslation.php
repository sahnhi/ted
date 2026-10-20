<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_product_cms_translation')]
#[ORM\Entity]
class ProductCMSTranslation extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'sub_title', type: 'string', length: 255, nullable: true)]
    private ?string $subTitle = null;

    #[ORM\Column(name: 'sub_info', type: 'string', length: 255, nullable: true)]
    private ?string $subInfo = null;

    #[ORM\Column(name: 'sub_info_bis', type: 'string', length: 255, nullable: true)]
    private ?string $subInfoBis = null;

    #[ORM\Column(name: 'product_details_title', type: 'string', length: 255, nullable: true)]
    private ?string $productDetailsTitle = null;

    #[ORM\Column(name: 'product_details_sub_title', type: 'string', length: 255, nullable: true)]
    private ?string $productDetailsSubTitle = null;

    #[ORM\Column(name: 'product_details_sub_claim', type: 'text', length: 255, nullable: true)]
    private ?string $productDetailsSubClaim = null;

    #[ORM\Column(name: 'product_details_sub_title_bis', type: 'string', length: 255, nullable: true)]
    private ?string $productDetailsSubTitleBis = null;

    #[ORM\Column(name: 'product_details_sub_claim_bis', type: 'text', length: 255, nullable: true)]
    private ?string $productDetailsSubClaimBis = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): self
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function getSubInfo(): ?string
    {
        return $this->subInfo;
    }

    public function setSubInfo(?string $subInfo): self
    {
        $this->subInfo = $subInfo;

        return $this;
    }

    public function getSubInfoBis(): ?string
    {
        return $this->subInfoBis;
    }

    public function setSubInfoBis(?string $subInfoBis): self
    {
        $this->subInfoBis = $subInfoBis;

        return $this;
    }

    public function getProductDetailsTitle(): ?string
    {
        return $this->productDetailsTitle;
    }

    public function setProductDetailsTitle(?string $productDetailsTitle): self
    {
        $this->productDetailsTitle = $productDetailsTitle;

        return $this;
    }

    public function getProductDetailsSubTitle(): ?string
    {
        return $this->productDetailsSubTitle;
    }

    public function setProductDetailsSubTitle(?string $productDetailsSubTitle): self
    {
        $this->productDetailsSubTitle = $productDetailsSubTitle;

        return $this;
    }

    public function getProductDetailsSubClaim(): ?string
    {
        return $this->productDetailsSubClaim;
    }

    public function setProductDetailsSubClaim(?string $productDetailsSubClaim): self
    {
        $this->productDetailsSubClaim = $productDetailsSubClaim;

        return $this;
    }

    public function getProductDetailsSubTitleBis(): ?string
    {
        return $this->productDetailsSubTitleBis;
    }

    public function setProductDetailsSubTitleBis(?string $productDetailsSubTitleBis): self
    {
        $this->productDetailsSubTitleBis = $productDetailsSubTitleBis;

        return $this;
    }

    public function getProductDetailsSubClaimBis(): ?string
    {
        return $this->productDetailsSubClaimBis;
    }

    public function setProductDetailsSubClaimBis(?string $productDetailsSubClaimBis): self
    {
        $this->productDetailsSubClaimBis = $productDetailsSubClaimBis;

        return $this;
    }
}
