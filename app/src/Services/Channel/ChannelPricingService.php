<?php

namespace App\Services\Channel;

use App\Doctrine\Entity\ProductCombo;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\ProductComboRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ChannelPricingService
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly RepositoryInterface $channelPricingRepository,
        private readonly ProductComboRepositoryInterface $productComboRepository
    ) {
    }

    public function getPriceByChannel(string $code, ChannelInterface $channel): int
    {
        $productVariant = $this->productVariantRepository->findOneByCode($code);

        if (!$productVariant instanceof ProductVariantInterface) {
            throw new NotFoundResourceException('Product variant not found');
        }

        $channelPricing = $this->channelPricingRepository->findOneBy(['channelCode' => $channel->getCode(), 'productVariant' => $productVariant]);

        return $channelPricing instanceof ChannelPricingInterface && $channelPricing->getPrice() > 0 ? $channelPricing->getPrice() :
            $this->getFirstNonLogisticItemPriceForChannel($code, $channel);
    }

    public function getFirstNonLogisticItemPriceForChannel(string $code, ChannelInterface $channel): int
    {
        $productVariant = $this->productVariantRepository->findOneByCode($code);

        if (!$productVariant instanceof ProductVariantInterface) {
            throw new NotFoundResourceException('Product variant not found');
        }

        $combos = $this->productComboRepository->findBy(['productVariantLogistic' => $productVariant]);

        /** @var ProductCombo $combo */
        foreach ($combos as $combo) {
            $channelPricing = $this->channelPricingRepository->findOneBy(['channelCode' => $channel->getCode(), 'productVariant' => $combo->getProductVariant()]);

            if ($channelPricing instanceof ChannelPricingInterface && $channelPricing->getPrice() > 0) {
                return $channelPricing->getPrice();
            }
        }

        return 10000; // Random value, we can't have line with no price
    }
}
