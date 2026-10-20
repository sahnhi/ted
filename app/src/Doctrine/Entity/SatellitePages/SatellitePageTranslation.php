<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\SatellitePages;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_satellite_page_translation')]
#[ORM\Entity]
class SatellitePageTranslation extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $tag = null;

    #[ORM\Column(name: 'main_phrase', nullable: true, type: 'string')]
    private ?string $mainPhrase = null;

    #[ORM\Column(nullable: true, type: 'text')]
    private ?string $focus = null;

    #[ORM\Column(name: 'satellite_url', type: 'string', unique: true)]
    private string $satelliteUrl;

    #[ORM\Column(name: 'quality_claim', nullable: true, type: 'string')]
    private ?string $qualityClaim = null;

    #[ORM\Column(name: 'technical_claim', nullable: true, type: 'string')]
    private ?string $technicalClaim = null;

    #[ORM\Column(name: 'name', type: 'string', nullable: true)]
    private ?string $name = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getMainPhrase(): ?string
    {
        return $this->mainPhrase;
    }

    public function setMainPhrase(?string $mainPhrase): self
    {
        $this->mainPhrase = $mainPhrase;

        return $this;
    }

    public function getFocus(): ?string
    {
        return $this->focus;
    }

    public function setFocus(?string $focus): self
    {
        $this->focus = $focus;

        return $this;
    }

    public function getSatelliteUrl(): string
    {
        return urldecode($this->satelliteUrl);
    }

    public function setSatelliteUrl(string $satelliteUrl): self
    {
        $this->satelliteUrl = urlencode($satelliteUrl);

        return $this;
    }

    public function getQualityClaim(): ?string
    {
        return $this->qualityClaim;
    }

    public function setQualityClaim(?string $qualityClaim): self
    {
        $this->qualityClaim = $qualityClaim;

        return $this;
    }

    public function getTechnicalClaim(): ?string
    {
        return $this->technicalClaim;
    }

    public function setTechnicalClaim(?string $technicalClaim): self
    {
        $this->technicalClaim = $technicalClaim;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
