<?php

declare(strict_types=1);

namespace App\Provider\Campaign\Handlers;

use App\Doctrine\Entity\Campaigns\Campaign;
use App\Doctrine\Entity\Campaigns\CampaignTranslations;
use App\Provider\Campaign\CampaignHandlerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CampaignHandler implements CampaignHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $campaignTranslationRepository,
    ) {
    }

    public function getCampaign(string $url): ?Campaign
    {
        // first case : no campaign has been found for this url -> 404
        $campaignTranslations = $this->campaignTranslationRepository->findOneBy(['url' => $url]);
        if (null === $campaignTranslations) {
            return null;
        }

        // second case : campaign is not enabled or timeframe is not valid we must redirect the user
        if (!$this->isEnabled($campaignTranslations)
        || !$this->isTimeframeValid($campaignTranslations)) {
            $campaignTranslations->getTranslatable()->setError(true);
        }

        // third case : campaign is enabled and timeframe is valid -> return the campaign
        return $campaignTranslations->getTranslatable();
    }

    private function isTimeframeValid(CampaignTranslations $campaignTranslations): bool
    {
        $today = new \DateTime();

        $canStart = null === $campaignTranslations->getStartDate() || $campaignTranslations->getStartDate() < $today;
        $canEnd = null === $campaignTranslations->getEndDate() || $campaignTranslations->getEndDate() > $today;

        return $canStart && $canEnd;
    }

    private function isEnabled(CampaignTranslations $campaignTranslations): bool
    {
        /** @var Campaign $campaign */
        $campaign = $campaignTranslations->getTranslatable();

        return $campaignTranslations->isEnabled() && $campaign->isEnabled();
    }
}
