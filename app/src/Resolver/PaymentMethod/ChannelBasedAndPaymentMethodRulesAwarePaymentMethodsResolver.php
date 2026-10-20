<?php

declare(strict_types=1);

namespace App\Resolver\PaymentMethod;

use App\Checker\PaymentMethodEligibleChecker;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethod as AppPaymentMethod;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

class ChannelBasedAndPaymentMethodRulesAwarePaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    public const TYPE = 'channel_based_rules_aware';

    public function __construct(
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly PaymentMethodEligibleChecker $paymentMethodEligibleChecker,
    ) {
    }

    /**
     * @param PaymentInterface $subject
     */
    public function getSupportedMethods(BasePaymentInterface $subject): array
    {
        /** @var Order $order */
        $order = $subject->getOrder();

        /** @var Channel $channel */
        $channel = $order->getChannel();

        /** @var AppPaymentMethod[] $paymentMethods */
        $paymentMethods = $this->paymentMethodRepository->findEnabledForChannel($channel);

        $total = $order->getTotal();

        $eligiblePaymentMethods = [];
        foreach ($paymentMethods as $paymentMethod) {
            if ($this->paymentMethodEligibleChecker->isPaymentEligible($total, $paymentMethod)) {
                $eligiblePaymentMethods[] = $paymentMethod;
            }
        }

        return $eligiblePaymentMethods;
    }

    public function supports(BasePaymentInterface $subject): bool
    {
        return $subject instanceof PaymentInterface
            && null !== $subject->getOrder()
            && null !== $subject->getOrder()->getChannel();
    }
}
