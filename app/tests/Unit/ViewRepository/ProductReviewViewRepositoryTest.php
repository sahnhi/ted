<?php

namespace App\Tests\Unit\ViewRepository;

use App\Doctrine\Entity\Sylius\ProductReview;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use App\Doctrine\Repository\Sylius\TaxonRepositoryInterface;
use App\Factory\View\GoogleRssViewFactoryInterface;
use App\Factory\View\PageViewFactoryInterface;
use App\Factory\View\ProductReviewViewFactoryInterface;
use App\Model\PaginatorDetails;
use App\Services\Channel\ChannelCode;
use App\Tests\TestCase;
use App\View\GoogleRssView;
use App\View\PageView;
use App\View\ProductReviewView;
use App\ViewRepository\ProductReviewViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Locale\Model\Locale;

class ProductReviewViewRepositoryTest extends TestCase
{
    /** @var MockObject|ChannelRepositoryInterface */
    private $channelRepository;
    /** @var EntityManagerInterface|MockObject */
    private $productManager;
    /** @var ProductReviewRepository|MockObject */
    private $productReviewRepository;
    /** @var TaxonRepositoryInterface|MockObject */
    private $taxonRepository;
    /** @var PageViewFactoryInterface|MockObject */
    private $pageViewFactory;
    /** @var ProductReviewViewFactoryInterface|MockObject */
    private $productReviewViewFactory;
    /** @var GoogleRssViewFactoryInterface|MockObject */
    private $googleRssViewFactory;

    private Channel $channel;
    private PaginatorDetails $paginator;
    private ProductReviewViewRepository $viewRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->channel = new Channel();
        $locale = new Locale();
        $locale->setCode(self::LOCALE_CODE);
        $this->channel->setCode(ChannelCode::CHANNEL_WEB);
        $this->channel->addLocale($locale);
        $this->paginator = new PaginatorDetails('', []);

        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->productManager = $this->createMock(EntityManagerInterface::class);
        $this->productReviewRepository = $this->createMock(ProductReviewRepository::class);
        $this->productManager
            ->method('getRepository')
            ->willReturn($this->productReviewRepository);
        $this->taxonRepository = $this->createMock(TaxonRepositoryInterface::class);
        $this->pageViewFactory = $this->createMock(PageViewFactoryInterface::class);
        $this->pageViewFactory
            ->method('create')
            ->willReturn(new PageView());
        $this->productReviewViewFactory = $this->createMock(ProductReviewViewFactoryInterface::class);
        $this->productReviewViewFactory
            ->method('create')
            ->willReturn(new ProductReviewView());
        $this->googleRssViewFactory = $this->createMock(GoogleRssViewFactoryInterface::class);
    }

    private function setUpRepository()
    {
        $this->viewRepository = new ProductReviewViewRepository(
            $this->channelRepository,
            $this->productManager,
            $this->taxonRepository,
            $this->pageViewFactory,
            $this->productReviewViewFactory,
            $this->googleRssViewFactory,
            $this->productReviewRepository
        );
    }

    public function testGetByProductCode(): void
    {
        $productReview = new ProductReview();
        $productReview->setRating(4);

        $this->channelRepository
            ->method('findOneByCode')
            ->willReturn($this->channel);
        $this->productReviewRepository
            ->method('findUnrejectedByProductCodeAndChannel')
            ->willReturn([$productReview]);
        $this->setUpRepository();

        $pageView = $this->viewRepository->getByProductCode(
            'tediber',
            ChannelCode::CHANNEL_WEB,
            $this->paginator,
            self::LOCALE_CODE,
            [],
            []
        );

        $this->assertIsArray($pageView->ratingPercentage);
        $this->assertNotEmpty($pageView->ratingPercentage);
    }

    public function testGetReviewsByTaxon(): void
    {
        $productReview = new ProductReview();
        $productReview->setRating(4);

        $this->taxonRepository
            ->method('findOneByLocaleAndSlug')
            ->willReturn(new Taxon());
        $this->channelRepository
            ->method('findOneByCode')
            ->willReturn($this->channel);
        $this->productReviewRepository
            ->method('getReviewsByTaxon')
            ->willReturn([$productReview]);
        $this->setUpRepository();

        $pageView = $this->viewRepository->getReviewsByTaxon(
            'tediber',
            ChannelCode::CHANNEL_WEB,
            $this->paginator,
            self::LOCALE_CODE,
            []
        );

        $this->assertIsArray($pageView->ratingPercentage);
        $this->assertEmpty($pageView->ratingPercentage);
    }

    public function testGetAllActivatedReviews(): void
    {
        $nbReviews = 2000;

        $this->productReviewRepository
            ->method('getIterableRssReviews')
            ->willReturn($this->createMock(IterableResult::class));
        $this->googleRssViewFactory
            ->method('create')
            ->willReturn([$nbReviews, new GoogleRssView()]);
        $this->setUpRepository();

        $result = $this->viewRepository->getAllActivatedReviews();

        $this->assertEquals(2, count($result));

        $count = $result[0];
        $view = $result[1];

        $this->assertEquals($nbReviews, $count);
        $this->assertInstanceOf(GoogleRssView::class, $view);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateProductReviewPage(): void
    {
        $this->setUpRepository();

        /** @var PageView $pageView */
        $pageView = $this->invokeMethod(
            $this->viewRepository,
            'createProductReviewPage',
            [[], $this->paginator]
        );

        $this->assertIsArray($pageView->items);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateProductReviewPageWithReviews(): void
    {
        $this->setUpRepository();
        $reviews = [new ProductReview(), new ProductReview()];

        /** @var PageView $pageView */
        $pageView = $this->invokeMethod(
            $this->viewRepository,
            'createProductReviewPage',
            [$reviews, $this->paginator]
        );

        $this->assertNotEmpty($pageView->items);
        $this->assertCount(2, $pageView->items);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateProductReviewPageWithRatings(): void
    {
        $this->setUpRepository();
        $ratings = [5, 4, 3, 0, 1, 2];

        /** @var PageView $pageView */
        $pageView = $this->invokeMethod(
            $this->viewRepository,
            'createProductReviewPage',
            [[], $this->paginator, $ratings]
        );

        $this->assertIsArray($pageView->ratingPercentage);
        $this->assertEquals($ratings, $pageView->ratingPercentage);
    }
}
