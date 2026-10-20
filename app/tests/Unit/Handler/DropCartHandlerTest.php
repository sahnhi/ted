<?php

namespace App\Tests\Unit\Handler;

use App\Business\Command\DropCart;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Handler\DropCartHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;

class DropCartHandlerTest extends TestCase
{
    /** @var EntityManagerInterface */
    private $cartManager;

    protected function setUp(): void
    {
        parent::setUp();
        $cartManager = $this->createMock(EntityManagerInterface::class);
        $this->cartManager = $cartManager;
    }

    public function testHandleSuccess(): void
    {
        $order = new Order();
        $order->setTokenValue('test');
        $order->setState(OrderInterface::STATE_CART);
        $dropCart = new DropCart($order->getTokenValue());

        $cartRepository = $this->createMock(OrderRepository::class);
        $cartRepository->method('findOneBy')
            ->willReturn($order);
        $dropCartHandler = new DropCartHandler($cartRepository, $this->cartManager);
        $this->assertNull($dropCartHandler->handle($dropCart));
    }

    public function testHandleCartIsNull(): void
    {
        $cartRepository = $this->createMock(OrderRepository::class);
        $cartRepository->method('findOneBy')
            ->willReturn(null);
        $dropCartHandler = new DropCartHandler($cartRepository, $this->cartManager);

        $this->expectException(\InvalidArgumentException::class);
        $dropCart = new DropCart('failed');
        $dropCartHandler->handle($dropCart);
    }

    public function testHandleDifferentState(): void
    {
        $order = new Order();
        $order->setTokenValue('test');
        $order->setState(OrderInterface::STATE_NEW);

        $cartRepository = $this->createMock(OrderRepository::class);
        $cartRepository->method('findOneBy')
            ->willReturn($order);
        $dropCartHandler = new DropCartHandler($cartRepository, $this->cartManager);

        $this->expectException(\InvalidArgumentException::class);
        $dropCart = new DropCart('test');
        $dropCartHandler->handle($dropCart);
    }
}
