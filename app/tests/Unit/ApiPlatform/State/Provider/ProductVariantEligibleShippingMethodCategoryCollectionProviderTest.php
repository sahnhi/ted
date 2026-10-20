<?php

declare(strict_types=1);

namespace App\Tests\Unit\ApiPlatform\State\Provider;

use App\ApiPlatform\State\Order\ProductVariantEligibleShippingMethodCategoryCollectionProvider;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Entity\ZipCode;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Matcher\Addressing\ZoneMatcher;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Component\Core\Factory\AddressFactoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ProductVariantEligibleShippingMethodCategoryCollectionProviderTest extends TestCase
{
    private ProductVariantEligibleShippingMethodCategoryCollectionProvider $provider;
    private ProductVariantRepositoryInterface $productVariantRepository;
    private ShippingMethodCategoryProvider $categoryProvider;
    private ZoneMatcher $zoneMatcher;
    private AddressFactoryInterface $addressFactory;
    private RepositoryInterface $zipCodeRepository;

    protected function setUp(): void
    {
        $this->productVariantRepository = $this->createMock(ProductVariantRepositoryInterface::class);
        $this->categoryProvider = $this->createMock(ShippingMethodCategoryProvider::class);
        $this->zoneMatcher = $this->createMock(ZoneMatcher::class);
        $this->addressFactory = $this->createMock(AddressFactoryInterface::class);
        $this->zipCodeRepository = $this->createMock(RepositoryInterface::class);

        $this->provider = new ProductVariantEligibleShippingMethodCategoryCollectionProvider(
            $this->productVariantRepository,
            $this->categoryProvider,
            $this->zoneMatcher,
            $this->addressFactory,
            $this->zipCodeRepository,
        );
    }

    public function testGetCollectionWithNoVariants(): void
    {
        $this->productVariantRepository->expects($this->once())
            ->method('findEnabledByCode')
            ->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No variants found');

        $this->provider->getCollection(\stdClass::class, null, ['filters' => ['productVariants' => []]]);
    }

    public function testGetCollectionWithVariants(): void
    {
        $variant = new ProductVariant();

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findEnabledByCode')
            ->with(['code-variant-1'])
            ->willReturn([$variant]);

        $this->zipCodeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => '12345'])
            ->willReturn(new ZipCode());

        $address = new Address();
        $this->addressFactory
            ->expects($this->once())
            ->method('createNew')
            ->willReturn($address);

        $zone = new Zone();
        $this->zoneMatcher
            ->expects($this->once())
            ->method('matchAll')
            ->with($address, Scope::SHIPPING)
            ->willReturn([$zone]);

        $variantWithQuantity = [
            ['variant' => $variant, 'quantity' => 1],
        ];

        $channel = $this->createMock(ChannelInterface::class);
        $shippingMethodCategory = new ShippingMethodCategory();
        $this->categoryProvider->expects($this->once())
            ->method('provideEligibleForProductVariantsAnZones')
            ->with($variantWithQuantity, $channel, [$zone])
            ->willReturn([$shippingMethodCategory]);

        $result = $this->provider->getCollection(ShippingMethodCategory::class, 'foo-bar', [
            'filters' => [
                'productVariants' => ['code-variant-1'],
                'postalCode' => '12345',
                'countryCode' => 'FR',
            ],
            ContextKeys::CHANNEL => $channel,
        ]);

        self::assertEquals([$shippingMethodCategory], $result);
    }

    public function testItThrowsAnUnprocessableEntityWhenTheSuppliedZipCodeDoesNotExist(): void
    {
        $variant = new ProductVariant();

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findEnabledByCode')
            ->with(['code-variant-1'])
            ->willReturn([$variant]);

        $this->zipCodeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => '12345'])
            ->willReturn(null);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Zip code "12345" does not exist');

        $this->provider->getCollection(ShippingMethodCategory::class, 'foo-bar', [
            'filters' => [
                'productVariants' => ['code-variant-1'],
                'postalCode' => '12345',
                'countryCode' => 'FR',
            ],
            ContextKeys::CHANNEL => $this->createMock(ChannelInterface::class),
        ]);
    }
}
