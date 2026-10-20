<?php

declare(strict_types=1);

namespace App\Provider\Campaign\Handlers;

use App\Doctrine\Entity\Campaigns\Bloc;
use App\Doctrine\Entity\Campaigns\Campaign;
use App\Provider\Campaign\CampaignHandlerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class BlocPreviewHandler implements CampaignHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $campaignBlocRepository,
        private readonly RepositoryInterface $campaignRepository,
    ) {
    }

    public function getCampaign(string $url): ?Campaign
    {
        if (!str_starts_with($url, Bloc::PREVIEW_URL_PREFIX)) {
            return null;
        }

        $bloc = $this->fetchBloc($url);
        if (!$bloc instanceof Bloc) {
            return null;
        }

        return $this->generateCampaign($bloc);
    }

    private function fetchBloc(string $url): ?Bloc
    {
        $uuid = str_replace(Bloc::PREVIEW_URL_PREFIX, '', $url);

        return $this->campaignBlocRepository->findOneBy(['offlineCode' => $uuid]);
    }

    private function generateCampaign(Bloc $bloc): Campaign
    {
        /** @var Campaign $templateCampaign */
        $templateCampaign = $this->campaignRepository->findOneBy([]);

        if (!$templateCampaign instanceof Campaign) {
            throw new \Exception('Campaign not found');
        }

        $templateCampaignBlocDisplay = $templateCampaign->getCampaignBlocDisplay()->first();
        $campaignBlocDisplay = clone $templateCampaignBlocDisplay;
        $campaignBlocDisplay->setBloc($bloc);

        $campaign = clone $templateCampaign;
        $campaign->getCampaignBlocDisplay()->clear();
        $campaign->addCampaignBlocDisplay($campaignBlocDisplay);

        return $campaign;
    }
}
