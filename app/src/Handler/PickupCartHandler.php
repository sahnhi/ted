<?php

namespace App\Handler;

use App\Business\Command\PickupCart;
use App\Doctrine\Entity\FrontData;
use App\Doctrine\Entity\Sylius\Order;
use App\Factory\OrderFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

class PickupCartHandler
{
    public function __construct(
        private readonly OrderFactoryInterface $cartFactory,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly EntityManagerInterface $cartManager
    ) {
    }

    public function handle(PickupCart $pickupCart): Order
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByCode($pickupCart->channelCode());
        Assert::notNull($channel, sprintf('Channel with %s code has not been found.', $pickupCart->channelCode()));

        /** @var OrderInterface $cart */
        $cart = $this->cartFactory->createNew();
        $cart->setChannel($channel);
        $cart->setCurrencyCode($channel->getBaseCurrency()->getCode());
        $cart->setLocaleCode($pickupCart->localeCode());
        $cart->setTokenValue($pickupCart->orderToken());

        if ($pickupCart->hasFrontData()) {
            $frontData = new FrontData();
            $frontData->setFrontVersion($pickupCart->body()['version']);
            $frontData->setUserAgent($pickupCart->body()['user-agent']);
            $frontData->setState('created');

            $cart->addFrontData($frontData);
        }

        $this->cartManager->persist($cart);

        return $cart;
    }
}
