<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory\View;

use App\Doctrine\Entity\ProductGoogleRss;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Factory\View\ImageViewFactoryInterface;
use App\Factory\View\RelatedProductViewFactory;
use App\View\ImageView;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\ProductImage;
use Sylius\Component\Core\Model\ProductTranslation;

class RelatedProductViewFactoryTest extends TestCase
{
    private readonly ImageViewFactoryInterface $imageViewFactory;

    private readonly ProductVariantRepositoryInterface $productVariantRepository;

    private readonly RelatedProductViewFactory $relatedProductViewFactory;

    protected function setUp(): void
    {
        $this->relatedProductViewFactory = new RelatedProductViewFactory(
            $this->imageViewFactory = $this->createMock(ImageViewFactoryInterface::class),
            $this->productVariantRepository = $this->createMock(ProductVariantRepositoryInterface::class)
        );
    }

    public function testItCorrectlyCreatesARelatedProductView(): void
    {
        $channel = new Channel();

        $rssProduct = new ProductGoogleRss();
        $rssProduct->setGtin('123456');

        $product = new Product();
        $product->setCode('FOO_BAR');
        $product->setProductGoogleRss($rssProduct);
        $product->setAverageRating(1.5);

        $productImage = new ProductImage();
        $productImage->setType('image_type');
        $productImage->setPath('/example/path');
        $product->addImage($productImage);

        $translation = new ProductTranslation();
        $translation->setLocale('fr_FR');
        $translation->setName('Example Product');
        $translation->setSlug('example_product_slug');
        $translation->setDescription('Example Product Description');
        $translation->setShortDescription('Example Short Product Description');
        $product->addTranslation($translation);

        $channelPricing = new ChannelPricing();
        $channelPricing->setChannelCode($channel->getCode());
        $channelPricing->setPrice(1000);

        $cheapestVariant = $this->createMock(ProductVariant::class);
        $cheapestVariant
            ->expects($this->once())
            ->method('getChannelPricingForChannel')
            ->with($channel)
            ->willReturn($channelPricing);

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findCheapestPrice')
            ->with($product, $channel)
            ->willReturn($cheapestVariant);

        $imageView = new ImageView();
        $imageView->code = 'image_type';
        $imageView->path = '/example/path';

        $this->imageViewFactory
            ->expects($this->once())
            ->method('create')
            ->with($productImage)
            ->willReturn($imageView);

        $relatedProductView = $this->relatedProductViewFactory->create($product, $channel, 'fr_FR');

        self::assertSame('FOO_BAR', $relatedProductView->code);
        self::assertSame('Example Product', $relatedProductView->name);
        self::assertSame('example_product_slug', $relatedProductView->slug);
        self::assertSame('123456', $relatedProductView->gtin);
        self::assertSame(1000, $relatedProductView->lowerPrice);
        self::assertSame('1.5', $relatedProductView->averageRating);
        self::assertSame('Example Product Description', $relatedProductView->description);
        self::assertSame('Example Short Product Description', $relatedProductView->shortDescription);
        self::assertSame([$imageView], $relatedProductView->images);
    }
}
