<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\SatellitePages;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'app_satellite_page_claim')]
#[ORM\Entity]
class Claim implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    use TimestampableEntity;
    public const MAX_CLAIMS = 5;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: SatellitePage::class, inversedBy: 'claims')]
    private ?SatellitePage $satellitePage = null;

    public function __construct(
    ) {
        $this->initializeTranslationsCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSatellitePage(): SatellitePage
    {
        return $this->satellitePage;
    }

    public function setSatellitePage(?SatellitePage $satellitePage): self
    {
        $this->satellitePage = $satellitePage;

        return $this;
    }

    public function getClaim(): string
    {
        /** @var ClaimTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getClaim();
    }

    public function setClaim(string $claim): self
    {
        /** @var ClaimTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setClaim($claim);

        return $this;
    }

    protected function createTranslation(): ClaimTranslation
    {
        return new ClaimTranslation();
    }
}
