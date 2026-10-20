<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Campaigns\Bloc;

use App\Doctrine\Entity\Campaigns\Bloc;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_campaign_bloc_variants')]
#[ORM\Entity]
class Variant implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id')]
    private ProductVariantInterface $variant;

    #[ORM\ManyToOne(targetEntity: Bloc::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(name: 'bloc_id', referencedColumnName: 'id')]
    private Bloc $bloc;

    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position;

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

    public function getBloc(): Bloc
    {
        return $this->bloc;
    }

    public function setBloc(Bloc $bloc): self
    {
        $this->bloc = $bloc;

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
