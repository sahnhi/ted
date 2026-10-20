<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Doctrine\Entity\Familiarity;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\FamiliarityRepositoryInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\MessageHandler\Familiarity\AddFamiliaritiesMessageHandler;
use App\Model\Familiarity\AddFamiliarities;
use PHPUnit\Framework\TestCase;

class AddFamiliaritiesMessageHandlerTest extends TestCase
{
    private readonly OrderRepositoryInterface $orderRepository;

    private readonly FamiliarityRepositoryInterface $familiarityRepository;

    private readonly AddFamiliaritiesMessageHandler $addFamiliaritiesMessageHandler;

    protected function setUp(): void
    {
        $this->familiarityRepository = $this->createMock(FamiliarityRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);

        $this->addFamiliaritiesMessageHandler = new AddFamiliaritiesMessageHandler(
            $this->orderRepository,
            $this->familiarityRepository
        );
    }

    public function testItThrowsAnInvalidExceptionIfTheOrderIsNotFound(): void
    {
        $this->orderRepository
            ->expects($this->once())
            ->method('findOneByTokenValue')
            ->with('unknown-order-token')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order not found for token: unknown-order-token');

        $this->addFamiliaritiesMessageHandler->__invoke(
            new AddFamiliarities('unknown-order-token', [
                'petanque',
                'chalet',
            ])
        );
    }

    public function testItThrowsAnInvalidArgumentExceptionIfTheOrderHasNoCustomer(): void
    {
        $order = $this->createMock(Order::class);
        $order
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn(null);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOneByTokenValue')
            ->with('valid-order-token')
            ->willReturn($order);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order has no customer');

        $this->addFamiliaritiesMessageHandler->__invoke(
            new AddFamiliarities('valid-order-token', [
                'petanque',
                'chalet',
            ])
        );
    }

    public function testItCorrectlyAddsTheFamiliarities(): void
    {
        $customer = new Customer();

        $order = $this->createMock(Order::class);
        $order
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOneByTokenValue')
            ->with('valid-order-token')
            ->willReturn($order);

        $petanqueFamiliarity = new Familiarity();
        $petanqueFamiliarity->setCode('petanque');

        $chaletFamiliarity = new Familiarity();
        $chaletFamiliarity->setCode('chalet');

        $this->familiarityRepository
            ->method('findOneByCode')
            // Since ->withConsecutive() is deprecated, we have to be creative
            ->willReturnCallback(fn (string $code): Familiarity => match ($code) {
                'petanque' => $petanqueFamiliarity,
                'chalet' => $chaletFamiliarity,
            });

        $this->addFamiliaritiesMessageHandler->__invoke(
            new AddFamiliarities('valid-order-token', [
                'petanque',
                'chalet',
            ])
        );

        self::assertCount(2, $customer->getFamiliarities());
        self::assertTrue($customer->hasFamiliarity($petanqueFamiliarity));
        self::assertTrue($customer->hasFamiliarity($chaletFamiliarity));
    }
}
