<?php

namespace App\Tests\Unit\ViewRepository;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\Sylius\ProductTaxonRepositoryInterface;
use App\Doctrine\Repository\Sylius\TaxonRepositoryInterface;
use App\Factory\View\ProductViewFactoryInterface;
use App\Factory\View\TaxonViewFactoryInterface;
use App\Services\Channel\ChannelCode;
use App\Tests\TestCase;
use App\View\TaxonView;
use App\ViewRepository\TaxonViewRepository;
use Doctrine\ORM\NonUniqueResultException;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ProductTaxon;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Locale\Model\Locale;
use Webmozart\Assert\InvalidArgumentException;

class TaxonViewRepositoryTest extends TestCase
{
    /** @var MockObject|ChannelRepositoryInterface */
    private $channelRepository;
    /** @var TaxonRepositoryInterface|MockObject */
    private $taxonRepository;
    /** @var MockObject|ProductTaxonRepositoryInterface */
    private $productTaxonRepository;
    /** @var ProductTaxonRepositoryInterface|MockObject */
    private $taxonViewFactory;
    /** @var TaxonViewFactoryInterface|MockObject */
    private $productViewFactory;

    /** @var TaxonViewRepository */
    private $viewRepository;

    public function setUp(): void
    {
        parent::setUp();

        $locale = new Locale();
        $locale->setCode(self::LOCALE_CODE);
        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_WEB);
        $channel->setDefaultLocale($locale);
        $channel->addLocale($locale);

        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->channelRepository
            ->method('findOneByCode')
            ->willReturn($channel);
        $this->taxonRepository = $this->createMock(TaxonRepositoryInterface::class);
        $this->taxonRepository
            ->method('findAllByLocale')
            ->willReturn([new Taxon()]);
        $this->productTaxonRepository = $this->createMock(ProductTaxonRepositoryInterface::class);
        $productTaxon = new ProductTaxon();
        $productTaxon->setProduct(new Product());
        $this->productTaxonRepository
            ->method('findBy')
            ->willReturn([$productTaxon]);
        $this->productTaxonRepository
            ->method('findAllByTaxon')
            ->willReturn([$productTaxon]);
        $this->productViewFactory = $this->createMock(ProductViewFactoryInterface::class);
        $this->taxonViewFactory = $this->createMock(TaxonViewFactoryInterface::class);
        $this->taxonViewFactory
            ->method('list')
            ->willReturn(new TaxonView());
        $this->taxonViewFactory
            ->method('create')
            ->willReturn(new TaxonView());
    }

    private function setUpObject()
    {
        $this->viewRepository = new TaxonViewRepository(
            $this->channelRepository,
            $this->taxonRepository,
            $this->productTaxonRepository,
            $this->productViewFactory,
            $this->taxonViewFactory
        );
    }

    public function testFindAllByChannelAndLocale(): void
    {
        $this->setUpObject();

        $view = $this->viewRepository
            ->findAllByChannelAndLocale(ChannelCode::CHANNEL_WEB, self::LOCALE_CODE);

        $this->assertNotEmpty($view->items);
        $this->assertCount(1, $view->items);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testFindOneBySlug(): void
    {
        $this->taxonRepository
            ->method('findOneByLocaleAndSlug')
            ->willReturn(new Taxon());
        $this->setUpObject();

        $view = $this->viewRepository
            ->findOneBySlug(ChannelCode::CHANNEL_WEB, 'slug', self::LOCALE_CODE);

        $this->assertInstanceOf(TaxonView::class, $view);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testFindOneBySlugNotFound(): void
    {
        $this->taxonRepository
            ->method('findOneByLocaleAndSlug')
            ->willReturn(null);
        $this->setUpObject();

        $this->expectException(InvalidArgumentException::class);
        $view = $this->viewRepository
            ->findOneBySlug(ChannelCode::CHANNEL_WEB, 'slug', self::LOCALE_CODE);
    }

    public function testFindProductsByTaxon(): void
    {
        $this->setUpObject();

        $views = $this->viewRepository
            ->findProductsByTaxon(ChannelCode::CHANNEL_WEB, new Taxon(), self::LOCALE_CODE);

        $this->assertNotEmpty($views);
        $this->assertCount(1, $views);
    }
}
