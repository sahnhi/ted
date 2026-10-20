<?php

namespace App\ViewRepository;

use App\Factory\View\ProductVariantViewFactoryInterface;
use App\View\PackListView;
use App\View\PackView;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

class PackViewRepository implements PackViewRepositoryInterface
{
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly RepositoryInterface $packRepository,
        private readonly ProductVariantViewFactoryInterface $productVariantViewFactory
    ) {
    }

    public function findOneByChannelAndCode(string $channelCode, string $packCode): PackView
    {
        $channel = $this->getChannel($channelCode);
        $localeCode = $this->getLocaleCode($channel);
        $pack = $this->packRepository->findOneBySlug($channel, $localeCode, $packCode);

        Assert::notNull($pack, sprintf('Pack with code %s not found in %s locale.', $packCode, $localeCode));

        return $this->getPackView($pack, $channel, $localeCode);
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

    public function listByChannelAndVariant(string $channelCode, string $variant): PackListView
    {
        $channel = $this->getChannel($channelCode);
        $localeCode = $this->getLocaleCode($channel);

        $listPack = $this->packRepository->findAllByVariant($channel, $localeCode, $variant);
        Assert::notNull($listPack, sprintf('Pack with variant %s not found in %s locale.', $variant, $localeCode));

        $listView = new PackListView();

        foreach ($listPack as $pack) {
            $listView->packs[] = $this->getPackView($pack, $channel, $localeCode);
        }

        return $listView;
    }

    public function getPackView($pack, $channel, $localeCode): PackView
    {
        $view = new PackView();
        $view->code = $pack->getCode();
        $view->name = $pack->getName();
        /** @var ProductVariantInterface $variant */
        foreach ($pack->getVariants() as $variant) {
            $view->variants[] = $this->productVariantViewFactory->create($variant, $channel, $localeCode);
        }

        return $view;
    }
}
