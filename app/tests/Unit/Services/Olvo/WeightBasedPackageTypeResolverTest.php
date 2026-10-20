<?php

namespace App\Tests\Unit\Services\Olvo;

use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Services\Olvo\PackageTypeNotFoundException;
use App\Services\Olvo\WeightBasedPackageTypeResolver;
use App\Tests\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class WeightBasedPackageTypeResolverTest extends TestCase
{
    public function testItReturnsTheCorrectShippingType(): void
    {
        $client = new MockHttpClient(
            new MockResponse(json_encode($this->getApiPackageTypesFixtures()))
        );

        $packageTypeResolver = new WeightBasedPackageTypeResolver($client);

        $variant = new ProductVariant();
        $variant->setWeight(50);

        $orderItem = new OrderItem();
        $orderItem->setVariant($variant);

        self::assertSame(4, $packageTypeResolver->resolvePackageType($orderItem));
    }

    public function testItThrowsAPackageTypeNotFoundExceptionIfNoSuitablePackageTypeIsFound(): void
    {
        $client = new MockHttpClient(
            new MockResponse(json_encode($this->getApiPackageTypesFixtures()))
        );

        $packageTypeResolver = new WeightBasedPackageTypeResolver($client);

        $variant = new ProductVariant();
        $variant->setWeight(250);

        $orderItem = new OrderItem();
        $orderItem->setVariant($variant);

        $this->expectException(PackageTypeNotFoundException::class);
        $this->expectExceptionMessage('No suitable package type found');

        $packageTypeResolver->resolvePackageType($orderItem);
    }

    private function getApiPackageTypesFixtures(): array
    {
        return [
            ['id' => 1, 'weight' => 1],
            ['id' => 2, 'weight' => 10],
            ['id' => 3, 'weight' => 30],
            ['id' => 4, 'weight' => 60],
            ['id' => 5, 'weight' => 100],
        ];
    }
}
