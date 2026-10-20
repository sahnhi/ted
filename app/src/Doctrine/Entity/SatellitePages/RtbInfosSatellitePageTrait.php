<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\SatellitePages;

use App\Doctrine\Entity\RtbInfos;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait RtbInfosSatellitePageTrait
{
    #[ORM\JoinTable(name: 'app_satellite_page_rtb_infos')]
    #[ORM\JoinColumn(name: 'satellite_page_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'rtb_infos_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: RtbInfos::class, cascade: ['all'])]
    private Collection $rtbInfos;

    public function getRtbInfos(): Collection
    {
        return $this->rtbInfos;
    }

    public function setRtbInfos(RtbInfos $rtbInfos): self
    {
        if (!$this->hasRtbInfos($rtbInfos)) {
            $this->rtbInfos->add($rtbInfos);
        }

        return $this;
    }

    public function hasRtbInfos(RtbInfos $rtbInfos): bool
    {
        return $this->rtbInfos->contains($rtbInfos);
    }

    public function removeRtbInfos(RtbInfos $rtbInfos): self
    {
        $this->rtbInfos->removeElement($rtbInfos);

        return $this;
    }

    public function clearRtbInfos(): self
    {
        $this->rtbInfos->clear();

        return $this;
    }
}
