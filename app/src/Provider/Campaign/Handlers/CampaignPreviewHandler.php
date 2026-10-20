<?php

declare(strict_types=1);

namespace App\Provider\Campaign\Handlers;

use App\Doctrine\Entity\Campaigns\Campaign;
use App\Provider\Campaign\CampaignHandlerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CampaignPreviewHandler implements CampaignHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $campaignRepository,
    ) {
    }

    public function getCampaign(string $url): ?Campaign
    {
        if (!str_starts_with($url, Campaign::PREVIEW_PREFIX)) {
            return null;
        }

        $campaign = $this->fetch($url);
        if (!$campaign instanceof Campaign) {
            $campaign = null;
        }

        return $campaign;
    }

    private function fetch(string $url): ?Campaign
    {
        $uuid = str_replace(Campaign::PREVIEW_PREFIX, '', $url);

        return $this->campaignRepository->findOneBy(['uuid' => $uuid]);
    }
}
