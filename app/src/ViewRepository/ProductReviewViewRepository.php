<?php

namespace App\ViewRepository;

use App\Doctrine\Repository\Sylius\ProductReviewRepositoryInterface;
use App\Doctrine\Repository\Sylius\TaxonRepositoryInterface;
use App\Factory\View\GoogleRssViewFactoryInterface;
use App\Factory\View\PageViewFactoryInterface;
use App\Factory\View\ProductReviewViewFactoryInterface;
use App\Model\PaginatorDetails;
use App\View\GoogleRssView;
use App\View\PageView;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Webmozart\Assert\Assert;

final class ProductReviewViewRepository extends AbstractViewRepository implements ProductReviewViewRepositoryInterface
{
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        private readonly EntityManagerInterface $productReviewManager,
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly PageViewFactoryInterface $pageViewFactory,
        private readonly ProductReviewViewFactoryInterface $productReviewViewFactory,
        private readonly GoogleRssViewFactoryInterface $googleRssViewFactory,
        private readonly ProductReviewRepositoryInterface $productReviewRepository
    ) {
        $this->channelRepository = $channelRepository;
    }

    public function getByProductCode(
        string $productCode,
        string $channelCode,
        PaginatorDetails $paginatorDetails,
        ?string $localeCode,
        ?array $filter,
        ?array $allQuestions
    ): PageView {
        $ratings = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];
        $channel = $this->getChannel($channelCode);
        $localeCode ??= $channel->getDefaultLocale()->getCode();
        $this->assertLocaleSupport($localeCode, $channel->getLocales());
        $reviews = $this->productReviewRepository->findUnrejectedByProductCodeAndChannel($productCode, $localeCode, $channel, $filter, $allQuestions);

        if (!empty($reviews)) {
            foreach ($reviews as $review) {
                if (!empty($review->getRating())) {
                    ++$ratings[$review->getRating()];
                }
            }
            $totalRating = array_sum($ratings);
            foreach ($ratings as &$rating) {
                $rating = (float) number_format(($rating * 100) / $totalRating, 2);
            }
        }

        $paginatorDetails->addToParameters('code', $productCode);

        return $this->createProductReviewPage($reviews, $paginatorDetails, $ratings);
    }

    public function getReviewsByTaxon(
        string $taxonSlug,
        string $channelCode,
        PaginatorDetails $paginatorDetails,
        ?string $localeCode,
        ?array $filter
    ): PageView {
        $channel = $this->getChannel($channelCode);
        $localeCode ??= $channel->getDefaultLocale()->getCode();
        $this->assertLocaleSupport($localeCode, $channel->getLocales());

        $taxon = $this->taxonRepository->findOneByLocaleAndSlug($localeCode, $taxonSlug);
        Assert::notNull($taxon, sprintf('Taxon with slug %s not found in %s locale.', $taxonSlug, $localeCode));

        $reviews = $this->productReviewRepository->getReviewsByTaxon($taxon, $localeCode, $channel, $filter);

        return $this->createProductReviewPage($reviews, $paginatorDetails);
    }

    /**
     * @return array<int, GoogleRssView>
     */
    public function getAllActivatedReviews(int $index = 0, int $limit = 2000): array
    {
        $this->productReviewManager->clear();
        $iterable = $this->productReviewRepository->getIterableRssReviews($index, $limit);

        return $this->googleRssViewFactory->create($iterable);
    }

    private function createProductReviewPage(array $reviews, PaginatorDetails $paginatorDetails, array $ratings = []): PageView
    {
        $pagerfanta = new Pagerfanta(new ArrayAdapter($reviews));

        $pagerfanta->setMaxPerPage($paginatorDetails->limit());
        $pagerfanta->setCurrentPage($paginatorDetails->page());

        $pageView = $this->pageViewFactory->create($pagerfanta, $paginatorDetails->route(), $paginatorDetails->parameters());
        if (!empty($ratings)) {
            $pageView->ratingPercentage = $ratings;
        }
        foreach ($pagerfanta->getCurrentPageResults() as $currentPageResult) {
            $pageView->items[] = $this->productReviewViewFactory->create($currentPageResult);
        }

        return $pageView;
    }
}
