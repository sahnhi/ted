<?php

declare(strict_types=1);

namespace App\Provider\Campaign;

use App\Doctrine\Entity\Campaigns\Campaign;

interface CampaignHandlerInterface
{
    public function getCampaign(string $url): ?Campaign;
}
