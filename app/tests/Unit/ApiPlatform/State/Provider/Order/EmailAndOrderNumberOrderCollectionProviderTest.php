<?php

declare(strict_types=1);

namespace App\Tests\Unit\ApiPlatform\State\Provider\Order;

use App\ApiPlatform\State\Order\EmailAndOrderNumberOrderCollectionProvider;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;

class EmailAndOrderNumberOrderCollectionProviderTest extends TestCase
{
    private readonly OrderRepositoryInterface $orderRepository;

    private readonly EmailAndOrderNumberOrderCollectionProvider $provider;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);

        $this->provider = new EmailAndOrderNumberOrderCollectionProvider($this->orderRepository);
    }

    public function testItReturnsAnEmptyArrayIfTheOrderIsNotFound(): void
    {
        $context = [
            'filters' => [
                'email' => 'foo@bar.com',
                'orderNumber' => '1234',
            ],
        ];

        $this->orderRepository
            ->expects($this->once())
            ->method('findOneByNumber')
            ->with('1234')
            ->willReturn(null);

        $result = $this->provider->getCollection(Order::class, context: $context);

        self::assertEmpty($result);
    }

    public function testItReturnsAnEmptyArrayIfTheEmailAddressDoesNotMatch(): void
    {
        $context = [
            'filters' => [
                'email' => 'foo@bar.com',
                'orderNumber' => '1234',
            ],
        ];

        $order = new Order();

        $customer = new Customer();
        $customer->setEmail('notbatman@wayne.com');

        $order->setCustomer($customer);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOneByNumber')
            ->with('1234')
            ->willReturn($order);

        $result = $this->provider->getCollection(Order::class, context: $context);

        self::assertEmpty($result);
    }

    public function testItReturnsACollectionWithOnlyTheCorrespondingOrder(): void
    {
        $context = [
            'filters' => [
                'email' => 'foo@bar.com',
                'orderNumber' => '1234',
            ],
        ];

        $order = new Order();

        $customer = new Customer();
        $customer->setEmail('foo@bar.com');

        $order->setCustomer($customer);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOneByNumber')
            ->with('1234')
            ->willReturn($order);

        $result = $this->provider->getCollection(Order::class, context: $context);

        self::assertCount(1, $result);
        self::assertSame([$order], $result);
    }

    public function testSupports(): void
    {
        self::assertFalse($this->provider->supports('RandomClass', 'shop_get_by_email_and_order_number'));
        self::assertFalse($this->provider->supports(Order::class, 'foo-bar'));
        self::assertFalse($this->provider->supports(Order::class, 'shop_get_by_email_and_order_number'));

        $invalidContext = [
            'filters' => [
                'email' => null,
                'orderNumber' => '1234',
            ],
        ];

        self::assertFalse($this->provider->supports(Order::class, 'shop_get_by_email_and_order_number', $invalidContext));

        $validContext = [
            'filters' => [
                'email' => 'foo@bar.com',
                'orderNumber' => '1234',
            ],
        ];

        self::assertTrue($this->provider->supports(Order::class, 'shop_get_by_email_and_order_number', $validContext));
    }
}
