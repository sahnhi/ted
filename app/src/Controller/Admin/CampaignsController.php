<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CampaignsController extends AbstractController
{
    public function __construct(
        private readonly RepositoryInterface $campaignRepository,
        private readonly RepositoryInterface $productVariantRepository,
    ) {
    }

    public function addVariantAction(Request $request): Response
    {
        $variantId = $request->get('variant_id');
        $campaignId = $request->get('campaign_id');

        $variant = $this->productVariantRepository->find($variantId);
        $campaign = $this->campaignRepository->find(3);

        $campaign->addVariant($variant);
        $this->campaignRepository->add($campaign);

        return $this->render(
            '@SyliusAdmin/Campaigns/Partial/_variant.html.twig',
            [
                'campaign' => $campaign,
                'variant' => $variant,
            ]
        );
    }
}
