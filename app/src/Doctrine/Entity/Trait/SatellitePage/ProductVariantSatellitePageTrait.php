<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Trait\SatellitePage;

use App\Doctrine\Entity\SatellitePages\SatellitePage;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait ProductVariantSatellitePageTrait
{
    #[ORM\OneToMany(targetEntity: SatellitePage::class, mappedBy: 'productVariant')]
    private ?Collection $satellitePages = null;

    public function getSatellitePagesMinified(): ?Collection
    {
        if (!is_iterable($this->satellitePages)) {
            return null;
        }

        return $this->satellitePages->filter(fn (SatellitePage $satellitePage): bool => $satellitePage->isEnabled());
    }
}
