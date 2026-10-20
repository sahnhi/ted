<?php

namespace App\Services\Sponsor;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\CustomerGroupInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PromotionCoupon;
use App\Doctrine\Entity\Sylius\PromotionInterface;
use App\Doctrine\Repository\Sylius\CustomerRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\Drip\DripApiInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class SyncFilleulToParrainDrip
{
    public function __construct(
        private readonly DripApiInterface $dripApi,
        private readonly CustomerRepository $customerRepository,
        private readonly OrderRepository $orderRepository,
        private readonly RepositoryInterface $customerGroupRepository,
        private readonly ObjectManager $customerManager,
        private readonly OrderProcessorInterface $orderProcessor
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(Order $order): void
    {
        if (null === $order->getId()) {
            return;
        }

        /** @var Customer $customer */
        $customer = $order->getCustomer();

        if (null !== $customer->getGroup() && CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE === $customer->getGroup()->getCode() && ($promotionCoupon = $this->orderRepository->findOneLastOrderForGetCouponSponsor($customer))) {
            $order->setPromotionCoupon($promotionCoupon);

            $this->orderProcessor->process($order);
        }

        /** @var PromotionCoupon|null $coupon */
        $coupon = $order->getPromotionCoupon();
        if (null !== $coupon) {
            $customer = $order->getCustomer();

            /** @var CustomerGroupInterface $groupSponsorRecommendee */
            if (!$groupSponsorRecommendee = $this->customerGroupRepository->findOneBy(['code' => CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE])) {
                throw new \RuntimeException(sprintf("Group with code %s doesn't exist.", CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE));
            }

            if (null !== $customer->getGroup()
                && $customer->getGroup()->getCode() === $groupSponsorRecommendee->getCode()
                && PromotionInterface::PROMOTION_SPONSOR_CODE === $coupon->getPromotion()?->getCode()) {
                if (!$groupRecommandee = $this->customerGroupRepository->findOneBy(['code' => CustomerGroupInterface::GROUP_RECOMMENDEE_CODE])) {
                    throw new \RuntimeException(sprintf("Group %s doesn't exist.", CustomerGroupInterface::GROUP_RECOMMENDEE_CODE));
                }

                $sponsor = $this->customerRepository->findOneByPromotionCoupon($coupon->getCode());
                if (null === $sponsor) {
                    return;
                }

                $customer->setGroup($groupRecommandee);
                $customer->setSponsor($sponsor);

                $this->customerManager->persist($customer);
                $this->customerManager->flush();

                $sponsor->setNbFilleul($this->customerRepository->countNbFilleul($sponsor));

                $sponsor->setParrainOrderAmountTotal(round($this->orderRepository->getParrainOrderAmountTotal($coupon) / 100, 2));

                $this->dripApi->sendCustomer($sponsor);
            }
        }
    }
}
