<?php

namespace App\ViewRepository;

use App\Doctrine\Repository\Sylius\ProductTaxonRepositoryInterface;
use App\Doctrine\Repository\Sylius\TaxonRepositoryInterface;
use App\Factory\View\ProductViewFactoryInterface;
use App\Factory\View\TaxonViewFactoryInterface;
use App\View\TaxonListView;
use App\View\TaxonView;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Webmozart\Assert\Assert;

class TaxonViewRepository extends AbstractViewRepository implements TaxonViewRepositoryInterface
{
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly ProductTaxonRepositoryInterface $productTaxonRepository,
        private readonly ProductViewFactoryInterface $productViewFactory,
        private readonly TaxonViewFactoryInterface $taxonViewFactory
    ) {
        $this->channelRepository = $channelRepository;
    }

    public function findAllByChannelAndLocale(
        string $channelCode,
        string $localeCode,
        ?array $slugs = null,
        bool $bounds = false
    ): TaxonListView {
        $channel = $this->getChannel($channelCode);
        $this->assertLocaleSupport($localeCode, $channel->getLocales());

        $taxon = $this->taxonRepository->findAllByLocale($localeCode, $slugs);

        $view = new TaxonListView();
        $productTaxOns = $this->productTaxonRepository->getFullProducts($localeCode, $slugs);

        foreach ($taxon as $taxonItem) {
            $partProductTaxons = $this->productTaxonRepository->getByTaxon($taxonItem, $productTaxOns);
            $view->items[] = $this->taxonViewFactory->list($taxonItem, $channel, $partProductTaxons, $localeCode, $bounds);
        }

        unset($taxon, $productTaxOns, $channel);

        return $view;
    }

    public function findOneBySlug(string $channelCode, string $slug, string $localeCode): TaxonView
    {
        $channel = $this->getChannel($channelCode);
        $this->assertLocaleSupport($localeCode, $channel->getLocales());

        $taxon = $this->taxonRepository->findOneByLocaleAndSlug($localeCode, $slug);

        Assert::notNull($taxon, sprintf('Taxon with slug %s not found in %s locale.', $slug, $localeCode));

        return $this->taxonViewFactory->create(
            $taxon,
            $this->findProductsByTaxon($channelCode, $taxon, $localeCode),
            $localeCode
        );
    }

    public function findProductsByTaxon(string $channelCode, TaxonInterface $taxon, string $localeCode): array
    {
        $channel = $this->getChannel($channelCode);

        $products = $this->productTaxonRepository->findAllByTaxon($taxon);

        $productsView = [];
        foreach ($products as $product) {
            $productsView[] = $this->productViewFactory->createForCategory(
                $product->getProduct(),
                $channel,
                $localeCode
            );
        }

        unset($products);

        return $productsView;
    }
}
