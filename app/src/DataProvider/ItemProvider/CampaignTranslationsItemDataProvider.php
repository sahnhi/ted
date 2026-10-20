<?php

declare(strict_types=1);

namespace App\DataProvider\ItemProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\Campaigns\CampaignTranslations;
use App\Provider\Campaign\CampaignProvider;

final class CampaignTranslationsItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly CampaignProvider $campaignProvider,
    ) {
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return CampaignTranslations::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $url, ?string $operationName = null, array $context = []): mixed
    {
        return $this->campaignProvider->getCampaign($url);
    }
}
