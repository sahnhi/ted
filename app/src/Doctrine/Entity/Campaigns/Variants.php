<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Campaigns;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_bloc_variants')]
#[ORM\Entity]
class Variants implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProductVariantInterface::class)]
    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id')]
    private ProductVariantInterface $variant;

    #[ORM\ManyToOne(targetEntity: Campaign::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(name: 'campaign_id', referencedColumnName: 'id')]
    private Campaign $campaign;

    #[ORM\Column(name: 'satellite_page_url', type: 'string', length: 255, nullable: true)]
    private ?string $satellitePageUrl = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getVariant(): ProductVariantInterface
    {
        return $this->variant;
    }

    public function setVariant(ProductVariantInterface $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getSatellitePageUrl(): ?string
    {
        return $this->satellitePageUrl;
    }

    public function setSatellitePageUrl(?string $satellitePageUrl): self
    {
        $this->satellitePageUrl = $satellitePageUrl;

        return $this;
    }
}
