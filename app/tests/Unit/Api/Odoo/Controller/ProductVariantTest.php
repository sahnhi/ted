<?php

declare(strict_types=1);

namespace App\Tests\Unit\Api\Odoo\Controller;

use App\Api\Odoo\Controller\ProductVariant;
use App\Api\Odoo\Service\ChannelPricingPriceUpdater;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant as ProductVariantEntity;
use App\Doctrine\Repository\ProductComboRepository;
use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Doctrine\Repository\Sylius\ShippingCategoryRepository;
use App\Services\Mail\Mailer\ProductUpdatedAlert;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProductVariantTest extends KernelTestCase
{
    private readonly ProductVariant $productVariantController;

    private readonly ProductRepositoryInterface $productRepository;

    private readonly ProductVariantRepository $productVariantRepository;

    protected function setUp(): void
    {
        $this->productVariantRepository = $this->createMock(ProductVariantRepository::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);

        $this->productVariantController = new ProductVariant(
            'foo-bar',
            $this->createMock(EntityManagerInterface::class),
            $this->productRepository,
            $this->createMock(RepositoryInterface::class),
            $this->productVariantRepository,
            $this->createMock(ProductVariantFactoryInterface::class),
            $this->createMock(ShippingCategoryRepository::class),
            $this->createMock(ChannelRepositoryInterface::class),
            $this->createMock(EntityRepository::class),
            $this->createMock(ProductComboRepository::class),
            $this->createMock(EntityRepository::class),
            $this->createMock(EntityRepository::class),
            $this->createMock(EntityRepository::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(ProductUpdatedAlert::class),
            $this->createMock(EntityRepository::class),
            $this->createMock(ChannelPricingPriceUpdater::class)
        );
    }

    public function testItDoesNotUpdateBundles(): void
    {
        $request = new Request();
        $request->headers->set('api-key', 'foo-bar');

        $request->request->set('code', 'product-code');
        $request->request->set('sku', 'product-variant-code');

        $product = $this->createMock(Product::class);

        $this->productRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'product-code'])
            ->willReturn($product);

        $productVariant = $this->createMock(ProductVariantEntity::class);

        $productVariant
            ->expects($this->once())
            ->method('isBundle')
            ->willReturn(true);

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'product-variant-code'])
            ->willReturn($productVariant);

        $response = ($this->productVariantController)($request);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('Bundle update is no longer supported', $response->getContent());
    }
}
