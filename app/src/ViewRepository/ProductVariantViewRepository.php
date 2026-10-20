<?php

namespace App\ViewRepository;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Factory\View\ProductVariantViewFactoryInterface;
use App\View\ProductVariantListView;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Webmozart\Assert\Assert;

final class ProductVariantViewRepository implements ProductVariantViewRepositoryInterface
{
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly ProductVariantViewFactoryInterface $productVariantViewFactory
    ) {
    }

    public function findAllPerOptions(string $channelCode, string $productCode, array $options): ProductVariantListView
    {
        $channel = $this->getChannel($channelCode);
        $localeCode = $this->getLocaleCode($channel);

        $product = $this->productRepository->findOneByChannelAndCode($channel, $productCode);
        Assert::notNull($product, sprintf('Product with code %s not found in %s locale.', $productCode, $localeCode));

        // custom
        $productVariants = $this->productVariantRepository->findByOptions($product->getCode(), $options);
        // end custom

        $view = new ProductVariantListView();

        /** @var ProductVariantInterface $variant */
        foreach ($productVariants as $variant) {
            Assert::true($variant->isEnabled(), sprintf('No stock'));
            $view->items[] = $this->productVariantViewFactory->create($variant, $channel, $localeCode);
        }

        return $view;
    }

    private function getChannel(string $channelCode): ChannelInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByCode($channelCode);
        Assert::notNull($channel, sprintf('Channel with code %s has not been found.', $channelCode));

        return $channel;
    }

    private function getLocaleCode(ChannelInterface $channel): string
    {
        $localeCode = $channel->getDefaultLocale()->getCode();
        $this->assertLocaleSupport($localeCode, $channel->getLocales());

        return $localeCode;
    }

    private function assertLocaleSupport(string $localeCode, iterable $supportedLocales): void
    {
        $supportedLocaleCodes = [];
        foreach ($supportedLocales as $locale) {
            $supportedLocaleCodes[] = $locale->getCode();
        }

        Assert::oneOf($localeCode, $supportedLocaleCodes);
    }
}
