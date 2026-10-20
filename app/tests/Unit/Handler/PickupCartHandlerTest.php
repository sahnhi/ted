<?php

namespace App\Tests\Unit\Handler;

use App\Business\Command\PickupCart;
use App\Doctrine\Entity\Sylius\Order;
use App\Factory\OrderFactoryInterface;
use App\Handler\PickupCartHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Currency\Model\Currency;

class PickupCartHandlerTest extends TestCase
{
    private PickupCartHandler $pickupCartHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $cartFactory = $this->createMock(OrderFactoryInterface::class);
        $cartFactory->method('createNew')
            ->willReturn(new Order());

        $baseCurrency = new Currency();
        $baseCurrency->setCode('EUR');
        $channel = new Channel();
        $channel->setCode('default');
        $channel->setBaseCurrency($baseCurrency);
        $channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $channelRepository->method('findOneByCode')
            ->willReturn($channel);

        $cartManager = $this->createMock(EntityManagerInterface::class);
        $cartManager->method('persist')
            ->willReturn(1);

        $this->pickupCartHandler = new PickupCartHandler(
            $cartFactory,
            $channelRepository,
            $cartManager
        );
    }

    public function testHandleWithFrontData(): void
    {
        $pickupCart = new PickupCart(
            'default',
            'fr_FR',
            ['version' => 'test', 'user-agent' => 'test']
        );
        $cart = $this->pickupCartHandler->handle($pickupCart);

        $this->assertEquals($pickupCart->localeCode(), $cart->getLocaleCode());
        $this->assertEquals($pickupCart->orderToken(), $cart->getTokenValue());
        $this->assertNotEmpty($cart->getFrontData());
    }

    public function testHandleWithoutFrontData(): void
    {
        $pickupCart = new PickupCart(
            'default',
            'fr_FR',
            ['version' => null, 'user-agent' => null]
        );
        $cart = $this->pickupCartHandler->handle($pickupCart);

        $this->assertEquals($pickupCart->localeCode(), $cart->getLocaleCode());
        $this->assertEquals($pickupCart->orderToken(), $cart->getTokenValue());
        $this->assertEmpty($cart->getFrontData());
    }
}
