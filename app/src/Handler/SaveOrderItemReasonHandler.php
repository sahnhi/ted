<?php

namespace App\Handler;

use App\Business\Command\SaveOrderItemReason;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\Persistence\ObjectManager;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class SaveOrderItemReasonHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ObjectManager $orderManager,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(SaveOrderItemReason $itemReasons): OrderInterface
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $itemReasons->getOrderTokenValue()]);
        Assert::notNull(
            $order,
            $this->translator->trans('app.api.order.complete.error.not_found', ['token' => $itemReasons->getOrderTokenValue()])
        );

        if (!empty($itemReasons->itemReasons())) {
            $reasons = $itemReasons->itemReasons();
            foreach ($order->getOrderReason() as $orderReason) {
                foreach ($reasons as $key => $reason) {
                    if ($orderReason->getVariant()->getCode() === $reason['code']) {
                        $orderReason->setCustomerComment($reason['reason']);
                        unset($reasons[$key]);
                    }
                }
            }
        }
        $this->orderManager->persist($order);
        $this->orderManager->flush();

        return $order;
    }
}
