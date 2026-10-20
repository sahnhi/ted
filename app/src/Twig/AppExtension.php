<?php

namespace App\Twig;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly OrderRepository $orderRepository
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getChannel', $this->getCurrentChannel(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            'json_decode' => new TwigFilter('json_decode', $this->jsonDecode(...)),
            'getParentOrder' => new TwigFilter('getParentOrder', $this->getParentOrder(...)),
            'getLastCompletedPayment' => new TwigFilter('getLastCompletedPayment', $this->getLastCompletedPayment(...)),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('instanceof', $this->isInstanceOf(...)),
        ];
    }

    public function getCurrentChannel(): ChannelInterface
    {
        return $this->channelContext->getChannel();
    }

    public function jsonDecode(string $json, ?bool $associative = null)
    {
        return json_decode($json, $associative);
    }

    public function isInstanceOf($var, $instance): bool
    {
        $reflexionClass = new \ReflectionClass($instance);

        return $reflexionClass->isInstance($var);
    }

    public function getParentOrder(string $parentNumber)
    {
        return $this->orderRepository->findOneByNumber($parentNumber);
    }

    public function getLastCompletedPayment(Order $order): ?PaymentInterface
    {
        return $order->getLastPayment(BasePaymentInterface::STATE_COMPLETED);
    }
}
