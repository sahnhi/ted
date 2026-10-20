<?php

namespace App\Tests\Unit\Factory\View;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\Sylius\ProductReviewRepositoryInterface;
use App\Factory\View\ImageViewFactoryInterface;
use App\Factory\View\ProductViewFactoryInterface;
use App\Factory\View\TaxonViewFactory;
use App\Tests\TestCase;
use App\View\ImageView;
use App\View\ProductView;
use App\View\TaxonView;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ProductTaxon;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Core\Model\TaxonImage;
use Sylius\Component\Taxonomy\Model\TaxonTranslation;

class TaxonViewFactoryTest extends TestCase
{
    public const LOCALE_CODE = 'fr_FR';

    /** @var Taxon */
    private $taxon;
    /** @var Channel */
    private $channel;
    /** @var string */
    private $locale = self::LOCALE_CODE;
    /** @var array */
    private $rating;

    /** @var TaxonViewFactory */
    private $factory;

    /**
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->taxon = new Taxon();
        $this->taxon->setCode('test');
        $this->setProtectedProperty($this->taxon, 'id', 1);
        $this->channel = new Channel();
        $this->rating = [
            'averageRating' => 4.5,
            'numberOfRating' => 2000,
        ];

        $imageViewFactory = $this->createMock(ImageViewFactoryInterface::class);
        $imageViewFactory->method('create')
            ->willReturn(new ImageView());
        $productViewFactory = $this->createMock(ProductViewFactoryInterface::class);
        $productViewFactory->method('list')
            ->willReturn(new ProductView());
        $productReviewRepository = $this->createMock(ProductReviewRepositoryInterface::class);
        $productReviewRepository->method('getAverageRatingsAndCountReviews')
            ->willReturn($this->rating);

        $translation = new TaxonTranslation();
        $translation->setLocale($this->locale);
        $translation->setName('test');
        $this->taxon->addTranslation($translation);

        $this->factory = new TaxonViewFactory(
            $imageViewFactory,
            $productViewFactory,
            $productReviewRepository
        );
    }

    public function testCreate(): void
    {
        $this->taxon->addImage(new TaxonImage());
        $this->taxon->addTranslation(new TaxonTranslation());
        $products = [new ProductView()];

        $view = $this->factory->create($this->taxon, $products, $this->locale);

        $this->assertInstanceOf(TaxonView::class, $view);
        $this->assertEquals($products, $view->products);
        $this->assertIsArray($view->images);
        $this->assertNotEmpty($view->images);
    }

    public function testCreateNoImages(): void
    {
        $view = $this->factory->create($this->taxon, [], $this->locale);

        $this->assertIsArray($view->images);
        $this->assertEmpty($view->images);
    }

    public function testList(): void
    {
        $productTaxon = new ProductTaxon();
        $productTaxon->setProduct(new Product());
        $products = [$productTaxon];

        $view = $this->factory->list($this->taxon, $this->channel, $products, $this->locale);

        $this->assertInstanceOf(TaxonView::class, $view);
        $this->assertIsArray($view->products);
        $this->assertNotEmpty($view->products);
    }

    public function testListWithBounds(): void
    {
        $productTaxon = new ProductTaxon();
        $productTaxon->setProduct(new Product());
        $products = [$productTaxon];
        $view = $this->factory->list($this->taxon, $this->channel, $products, $this->locale, true);

        $this->assertObjectHasAttribute('minBound', $view);
        $this->assertEquals(0, $view->minBound);
        $this->assertObjectHasAttribute('maxBound', $view);
        $this->assertEquals(0, $view->maxBound);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetBase(): void
    {
        /** @var TaxonView $view */
        $view = $this->invokeMethod(
            $this->factory,
            'getBase',
            [$this->taxon, $this->locale]
        );

        $this->assertObjectHasAttribute('code', $view);
        $this->assertEquals($this->taxon->getCode(), $view->code);
        $this->assertEquals($this->rating['averageRating'], $view->averageRating);
        $this->assertEquals($this->rating['numberOfRating'], $view->numberOfRating);
        $this->assertEquals($this->taxon->getTranslations()->get($this->locale)->getName(), $view->name);
    }
}
