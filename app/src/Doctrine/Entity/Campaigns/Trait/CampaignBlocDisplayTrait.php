<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Campaigns\Trait;

use App\Doctrine\Entity\Campaigns\Bloc;
use App\Doctrine\Entity\Campaigns\Bloc\CampaignBlocDisplay;
use App\Doctrine\Entity\Campaigns\Campaign;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait CampaignBlocDisplayTrait
{
    public function __construct()
    {
        $this->campaignBlocDisplay = new ArrayCollection();
    }

    public function addCampaignBlocDisplay(CampaignBlocDisplay $campaignBlocDisplay): self
    {
        if (!$this->hasCampaignBlocDisplayElement($campaignBlocDisplay)) {
            if ($this instanceof Campaign) {
                $campaignBlocDisplay->setCampaign($this);
            }
            if ($this instanceof Bloc) {
                $campaignBlocDisplay->setBloc($this);
            }
            $this->campaignBlocDisplay->add($campaignBlocDisplay);
        }

        return $this;
    }

    public function hasCampaignBlocDisplayElement(CampaignBlocDisplay $campaignBlocDisplay): bool
    {
        return $this->campaignBlocDisplay->contains($campaignBlocDisplay);
    }

    public function hasCampaignBlocDisplay(): bool
    {
        return (bool) $this->campaignBlocDisplay->isEmpty();
    }

    public function removeCampaignBlocDisplay(CampaignBlocDisplay $campaignBlocDisplay): self
    {
        if ($this->hasCampaignBlocDisplayElement($campaignBlocDisplay)) {
            $this->campaignBlocDisplay->remove($campaignBlocDisplay);
        }

        return $this;
    }

    public function getCampaignBlocDisplay(): Collection
    {
        return $this->campaignBlocDisplay;
    }
}
