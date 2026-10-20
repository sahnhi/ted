<?php

declare(strict_types=1);

namespace App\Tests\Unit\ApiPlatform\State\Provider;

use App\ApiPlatform\State\Order\OrderEligibleShippingMethodCategoryCollectionProvider;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\ZipCode;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderEligibleShippingMethodCategoryCollectionProviderTest extends TestCase
{
    private OrderEligibleShippingMethodCategoryCollectionProvider $provider;
    private OrderRepositoryInterface|MockObject $orderRepositoryMock;
    private RepositoryInterface|MockObject $zipCodeRepositoryMock;
    private ShippingMethodCategoryProvider|MockObject $shippingMethodCategoryProviderMock;

    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->zipCodeRepositoryMock = $this->createMock(RepositoryInterface::class);
        $this->shippingMethodCategoryProviderMock = $this->createMock(ShippingMethodCategoryProvider::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);

        $this->provider = new OrderEligibleShippingMethodCategoryCollectionProvider(
            $this->orderRepositoryMock,
            $this->shippingMethodCategoryProviderMock,
            $this->zipCodeRepositoryMock,
            $this->translatorMock
        );
    }

    public function testGetCollectionWithoutPostalCode(): void
    {
        $tokenValue = 'random-token-value';

        $expectedResult = [new ShippingMethodCategory()];

        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with($tokenValue)
            ->willReturn($this->createMock(OrderInterface::class));

        $this->shippingMethodCategoryProviderMock
            ->expects($this->once())
            ->method('provideEligibleForOrder')
            ->willReturn($expectedResult);

        $result = $this->provider->getCollection(ShippingMethodCategory::class, 'shop_get_eligible', [
            'filters' => [
                'tokenValue' => $tokenValue,
                'postalCode' => null,
            ],
        ]);

        self::assertEquals($expectedResult, $result);
    }

    public function testGetCollectionWithPostalCode(): void
    {
        $tokenValue = 'random-token-value';

        $expectedResult = [new ShippingMethodCategory()];

        $address = new Address();
        $zipCode = new ZipCode();
        $zipCode->setCode('38200');
        $order = new Order();
        $order->setShippingAddress($address);

        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with($tokenValue)
            ->willReturn($order);

        $this->zipCodeRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => '38200'])
            ->willReturn($zipCode);

        $this->shippingMethodCategoryProviderMock
            ->expects($this->once())
            ->method('provideEligibleForOrder')
            ->willReturn($expectedResult);

        $result = $this->provider->getCollection(ShippingMethodCategory::class, 'shop_get_eligible', [
            'filters' => [
                'tokenValue' => $tokenValue,
                'postalCode' => '38200',
            ],
        ]);

        self::assertEquals($expectedResult, $result);
        self::assertEquals('38200', $order->getShippingAddress()->getPostcode());
    }
}
