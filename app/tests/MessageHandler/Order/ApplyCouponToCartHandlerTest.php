<?php

namespace App\Tests\MessageHandler\Order;

use App\Doctrine\Entity\Sylius\GiftCard;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\MessageHandler\Order\ApplyCouponToCartHandler;
use PHPUnit\Framework\TestCase;
use Setono\SyliusGiftCardPlugin\Applicator\GiftCardApplicatorInterface;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Sylius\Bundle\ApiBundle\Command\Cart\ApplyCouponToCart;
use Sylius\Component\Core\Model\Channel;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ApplyCouponToCartHandlerTest extends TestCase
{
    private GiftCardRepositoryInterface $giftCardRepository;
    private OrderRepositoryInterface $orderRepository;
    private GiftCardApplicatorInterface $giftCardApplicator;
    private MessageHandlerInterface $decoratedApplyCouponToCartHandler;
    private ApplyCouponToCartHandler $applyCouponToCartHandler;

    protected function setUp(): void
    {
        $this->giftCardRepository = $this->createMock(GiftCardRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->giftCardApplicator = $this->createMock(GiftCardApplicatorInterface::class);

        $this->decoratedApplyCouponToCartHandler = $this->getMockBuilder(MessageHandlerInterface::class)
            ->addMethods(['__invoke'])
            ->getMock();

        $this->applyCouponToCartHandler = new ApplyCouponToCartHandler(
            $this->decoratedApplyCouponToCartHandler,
            $this->giftCardRepository,
            $this->orderRepository,
            $this->giftCardApplicator
        );
    }

    public function testInvokeWithoutCart(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart doesn\'t exist');

        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with('order-token-value')
            ->willReturn(null);

        $command = new ApplyCouponToCart('foo-bar');
        $command->setOrderTokenValue('order-token-value');

        $this->applyCouponToCartHandler->__invoke($command);
    }

    public function testInvokeWithGiftCard(): void
    {
        $channel = new Channel();
        $channel->setCode('default');

        $cart = new Order();
        $cart->setChannel($channel);

        $giftCard = $this->createMock(GiftCard::class);

        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with('order-token-value')
            ->willReturn($cart);

        $this->giftCardRepository
            ->expects($this->once())
            ->method('findOneEnabledByCodeAndChannel')
            ->with('foo-bar', $channel)
            ->willReturn($giftCard);

        $this->giftCardApplicator
            ->expects($this->once())
            ->method('apply')
            ->with($cart, $giftCard);

        $command = new ApplyCouponToCart('foo-bar');
        $command->setOrderTokenValue('order-token-value');

        $this->applyCouponToCartHandler->__invoke($command);
    }

    public function testInvokeWithCoupon(): void
    {
        $channel = new Channel();
        $channel->setCode('default');

        $cart = new Order();
        $cart->setChannel($channel);

        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with('order-token-value')
            ->willReturn($cart);

        $this->giftCardRepository
            ->expects($this->once())
            ->method('findOneEnabledByCodeAndChannel')
            ->with('foo-bar', $channel)
            ->willReturn(null);

        $command = new ApplyCouponToCart('foo-bar');
        $command->setOrderTokenValue('order-token-value');

        $this->decoratedApplyCouponToCartHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with($command)
            ->willReturn($cart);

        $this->giftCardApplicator
            ->expects($this->never())
            ->method('apply');

        $this->applyCouponToCartHandler->__invoke($command);
    }
}
