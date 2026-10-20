<?php

declare(strict_types=1);

namespace App\Provider\Campaign;

use App\Doctrine\Entity\Campaigns\Campaign;

final class CampaignProvider
{
    public function __construct(
        private readonly iterable $handlers,
    ) {
    }

    public function getCampaign(string $url): ?Campaign
    {
        $campaign = null;
        foreach ($this->handlers as $handler) {
            $campaign = $handler->getCampaign($url);
            if (null !== $campaign) {
                return $campaign;
            }
        }

        return null;
    }
}
