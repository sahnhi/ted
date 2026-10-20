<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait RtbInfosProductTrait
{
    #[ORM\JoinTable(name: 'app_product_rtb_infos')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'rtb_infos_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: RtbInfos::class)]
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
