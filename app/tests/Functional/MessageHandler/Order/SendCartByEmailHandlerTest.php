<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Message\Order\SendCartByEmail;
use App\MessageHandler\Order\SendCartByEmailHandler;
use App\Services\Drip\DripApiInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SendCartByEmailHandlerTest extends KernelTestCase
{
    private readonly SendCartByEmailHandler $handler;
    private readonly DripApiInterface $dripApi;
    private readonly OrderRepositoryInterface $orderRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->dripApi = $this->createMock(DripApiInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);

        $this->handler = new SendCartByEmailHandler(
            $this->dripApi,
            $this->orderRepository
        );
    }

    public function testItCorrectlyHandleTheSendCartByEmail(): void
    {
        $message = new SendCartByEmail('dev@tediber.com', 'orderToken');
        $order = new Order();
        $order->setTokenValue('orderToken');

        $this->orderRepository->expects($this->once())
            ->method('findCartByTokenValue')
            ->with($message->getOrderTokenValue())
            ->willReturn($order);

        $this->dripApi
            ->expects($this->once())
            ->method('sendOrder')
            ->with($order);

        $this->dripApi
            ->expects($this->once())
            ->method('sendTag')
            ->with($message->getEmail(), ['savedCart']);

        ($this->handler)($message);
    }
}
