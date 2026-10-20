<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\SatellitePages;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_satellite_page_claim_translation')]
#[ORM\Entity]
class ClaimTranslation extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $claim;

    public function getId(): int
    {
        return $this->id;
    }

    public function getClaim(): string
    {
        return $this->claim;
    }

    public function setClaim(string $claim): self
    {
        $this->claim = $claim;

        return $this;
    }
}
