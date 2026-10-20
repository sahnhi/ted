<?php

namespace App\Factory;

use App\Doctrine\Entity\Quality;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\QualityRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

class QualityFactory implements FactoryInterface
{
    public function __construct(
        private readonly QualityRepository $qualityRepository,
        private readonly OrderRepository $orderRepository
    ) {
    }

    public function createNew(): Quality
    {
        return new Quality();
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createFromOrder($orderId): Quality
    {
        /** @var Order $order */
        $order = $this->orderRepository->find((int) $orderId);
        Assert::notNull($order);

        $number = $this->qualityRepository->countForOrder($order);
        $qualityNumber = 0 === $number ?
            1 :
            $number + 1;

        $quality = new Quality();
        $quality->setNumber($order->getNumber() . Quality::SUFFIX . $qualityNumber);

        return $quality;
    }
}
