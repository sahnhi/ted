<?php

namespace App\Controller\Api\Sponsor;

use App\Doctrine\Entity\Sylius\CustomerGroupInterface;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Doctrine\Entity\Sylius\PromotionInterface;
use App\Services\Drip\DripApiInterface;
use Doctrine\Persistence\ObjectManager;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PromotionRepositoryInterface;
use Sylius\Component\Promotion\Generator\PromotionCouponGeneratorInstruction;
use Sylius\Component\Promotion\Generator\PromotionCouponGeneratorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class AddAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly RepositoryInterface $customerGroupRepository,
        private readonly PromotionRepositoryInterface $promotionRepository,
        private readonly ObjectManager $customerManager,
        private readonly PromotionCouponGeneratorInterface $promotionCouponGenerator,
        private readonly DripApiInterface $dripApi,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        $email = $request->query->get('email');

        Assert::notNull($email, 'Email required.');

        /** @var CustomerInterface $customer */
        if (!$customer = $this->customerRepository->findOneBy(['email' => $email])) {
            throw new \Exception("User doesn't exist.");
        }

        if (!$this->orderRepository->getOrderByCustomerEmail($email)) {
            throw new \Exception('No paid orders for this customer.');
        }

        if (!$groupSponsor = $this->customerGroupRepository->findOneBy(['code' => CustomerGroupInterface::GROUP_SPONSOR_CODE])) {
            throw new \Exception(sprintf("Group %s doesn't exist.", CustomerGroupInterface::GROUP_SPONSOR_CODE));
        }

        /** @var PromotionInterface $promotionSponsor */
        if (!$promotionSponsor = $this->promotionRepository->findOneBy(['code' => PromotionInterface::PROMOTION_SPONSOR_CODE])) {
            throw new \Exception(sprintf("Promotion %s doesn't exist.", PromotionInterface::PROMOTION_SPONSOR_CODE));
        }

        $coupon = '';

        if (count($customer->getPromotionCoupons()) > 0) {
            /** @var PromotionCouponInterface $promotionCoupon */
            foreach ($customer->getPromotionCoupons() as $promotionCoupon) {
                if (PromotionInterface::PROMOTION_SPONSOR_CODE === $promotionCoupon->getPromotion()->getCode()) {
                    $coupon = $promotionCoupon;
                }
            }
        }

        if (empty($coupon)) {
            $promotionSponsorExpiresAt = new \DateTime();
            $promotionSponsorExpiresAt->modify('+3 months');

            $promotionSponsorInstruction = new PromotionCouponGeneratorInstruction();
            $promotionSponsorInstruction->setAmount(1);
            $promotionSponsorInstruction->setCodeLength(10);
            $promotionSponsorInstruction->setExpiresAt($promotionSponsorExpiresAt);
            $promotionSponsorInstruction->setUsageLimit(5);
            // TODO : update Sylius for add setPrefix method

            $coupon = $this->promotionCouponGenerator->generate($promotionSponsor, $promotionSponsorInstruction);
            $coupon = current($coupon); // Get first coupon generated only

            $customer->addPromotionCoupon($coupon);
            $this->customerManager->persist($customer);
            $this->customerManager->flush();
        }

        if (null === $customer->getGroup() || $groupSponsor->getId() !== $customer->getGroup()->getId()) {
            $customer->setGroup($groupSponsor);
            $this->customerManager->persist($customer);
            $this->customerManager->flush();
        }

        $customer->setSponsorCoupon($coupon->getCode());

        $this->dripApi->sendCustomer($customer);

        $view = View::create([
            'coupon' => $coupon->getCode(),
        ]);

        return $this->viewHandler->handle($view);
    }
}
