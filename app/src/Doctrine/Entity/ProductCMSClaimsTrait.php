<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait ProductCMSClaimsTrait
{
    #[ORM\OneToMany(targetEntity: ProductCMSClaims::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    private Collection $claims;

    public function __construct()
    {
        $this->claims = new ArrayCollection();
    }

    public function getClaims(): Collection
    {
        return $this->claims;
    }

    public function addClaim(ProductCMSClaims $claim): self
    {
        if (!$this->hasClaim($claim)) {
            $claim->setProduct($this);
            $this->claims->add($claim);
        }

        return $this;
    }

    public function removeClaim(ProductCMSClaims $claim): self
    {
        if ($this->hasClaim($claim)) {
            $this->claims->removeElement($claim);
        }

        return $this;
    }

    public function hasClaim(ProductCMSClaims $claim): bool
    {
        return $this->claims->contains($claim);
    }

    public function clearClaims(): self
    {
        foreach ($this->claims as $claim) {
            $this->removeClaim($claim);
        }

        return $this;
    }
}
