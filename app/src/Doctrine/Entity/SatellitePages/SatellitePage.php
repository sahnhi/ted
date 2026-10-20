<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\SatellitePages;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'app_satellite_page')]
#[ORM\Entity]
#[ApiResource(
    routePrefix: '/shop',
    collectionOperations: [
        'get' => [
            'normalization_context' => [
                'groups' => [
                    'shop:satellite-page:read',
                    'shop:product:read',
                    'shop:product:read:extended',
                    'shop:satellite-page-claim:read',
                    'shop:satellite-page-technical-detail:read',
                    'shop:satellite-page-technical-info:read',
                ],
            ],
        ],
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['translations.satelliteUrl' => 'exact'])]
class SatellitePage implements ResourceInterface, TranslatableInterface
{
    use RtbInfosSatellitePageTrait;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }
    use TimestampableEntity;
    public const MAX_TECHNICAL_INFOS = 2;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Claim::class, mappedBy: 'satellitePage', cascade: ['persist', 'remove'])]
    private Collection $claims;

    #[ORM\OneToMany(targetEntity: TechnicalInfo::class, mappedBy: 'satellitePage', cascade: ['persist', 'remove'])]
    private Collection $technicalInfos;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    #[ORM\JoinColumn(name: 'product_variant_id', referencedColumnName: 'id', nullable: false)]
    private ProductVariant $productVariant;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->claims = new ArrayCollection();
        $this->technicalInfos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getTag();
    }

    public function setTag(string $tag): self
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setTag($tag);

        return $this;
    }

    public function getMainPhrase(): ?string
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getMainPhrase();
    }

    public function setMainPhrase(string $mainPhrase): self
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setMainPhrase($mainPhrase);

        return $this;
    }

    public function getFocus(): ?string
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getFocus();
    }

    public function setFocus(string $focus): self
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setFocus($focus);

        return $this;
    }

    public function getSatelliteUrl(): ?string
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getSatelliteUrl();
    }

    public function setSatelliteUrl(string $satelliteUrl): self
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setSatelliteUrl($satelliteUrl);

        return $this;
    }

    public function getQualityClaim(): ?string
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getQualityClaim();
    }

    public function setQualityClaim(string $qualityClaim): self
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setQualityClaim($qualityClaim);

        return $this;
    }

    public function getTechnicalClaim(): ?string
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getTechnicalClaim();
    }

    public function setTechnicalClaim(string $technicalClaim): self
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setTechnicalClaim($technicalClaim);

        return $this;
    }

    public function getProductVariant(): ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariant $productVariant): self
    {
        $this->productVariant = $productVariant;

        return $this;
    }

    public function getClaims(): Collection
    {
        return $this->claims;
    }

    public function addClaim(Claim $claim): self
    {
        if (!$this->claims->contains($claim)) {
            $this->claims->add($claim);
            $claim->setSatellitePage($this);
        }

        return $this;
    }

    public function removeClaim(Claim $claim): self
    {
        if ($this->claims->contains($claim)) {
            $this->claims->removeElement($claim);
            $claim->setSatellitePage(null);
        }

        return $this;
    }

    public function clearClaims(): self
    {
        foreach ($this->claims as $claim) {
            $this->removeClaim($claim);
        }

        return $this;
    }

    public function getTechnicalInfos(): Collection
    {
        return $this->technicalInfos;
    }

    public function addTechnicalInfo(TechnicalInfo $technicalInfo): self
    {
        if (!$this->technicalInfos->contains($technicalInfo)) {
            $this->technicalInfos->add($technicalInfo);
            $technicalInfo->setSatellitePage($this);
        }

        return $this;
    }

    public function removeTechnicalInfo(TechnicalInfo $technicalInfo): self
    {
        if ($this->technicalInfos->removeElement($technicalInfo)) {
            if ($technicalInfo->getSatellitePage() === $this) {
                $technicalInfo->setSatellitePage(null);
            }
        }

        return $this;
    }

    public function clearTechnicalInfos(): self
    {
        foreach ($this->technicalInfos as $technicalInfo) {
            $this->removeTechnicalInfo($technicalInfo);
        }

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    protected function createTranslation(): SatellitePageTranslation
    {
        return new SatellitePageTranslation();
    }

    public function getProduct(): Product
    {
        return $this->getProductVariant()->getProduct();
    }

    public function getProductVariantId(): int
    {
        return $this->getProductVariant()->getId();
    }

    public function getProductVariantCode(): string
    {
        return $this->getProductVariant()->getCode();
    }

    public function getName(): ?string
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getName();
    }

    public function setName(string $name): self
    {
        /** @var SatellitePageTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setName($name);

        return $this;
    }
}
