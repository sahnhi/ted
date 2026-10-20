<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Campaigns\Bloc;

use App\Doctrine\Entity\Campaigns\Bloc;
use App\Doctrine\Entity\Campaigns\Campaign;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_campaign_bloc_display')]
class CampaignBlocDisplay implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Bloc::class, inversedBy: 'campaignBlocDisplay')]
    private Bloc $bloc;

    #[ORM\ManyToOne(targetEntity: Campaign::class, inversedBy: 'campaignBlocDisplay')]
    private Campaign $campaign;

    #[ORM\Column(type: 'integer')]
    private int $position;

    #[ORM\Column(type: 'boolean')]
    private bool $seoBlock = false;

    public function getId(): int
    {
        return $this->id;
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

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): self
    {
        $this->campaign = $campaign;

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

    public function isSeoBlock(): bool
    {
        return $this->seoBlock;
    }

    public function setSeoBlock(bool $seoBlock): self
    {
        $this->seoBlock = $seoBlock;

        return $this;
    }
}
