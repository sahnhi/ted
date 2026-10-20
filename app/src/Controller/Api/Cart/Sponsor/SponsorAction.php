<?php

namespace App\Controller\Api\Cart\Sponsor;

use App\Doctrine\Entity\Sylius\CustomerGroupInterface;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Doctrine\Repository\Sylius\CustomerRepository;
use App\Services\Drip\DripApiInterface;
use App\Services\Loyalty\LoyaltyServiceInterface;
use Doctrine\Persistence\ObjectManager;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionCouponEligibilityCheckerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class SponsorAction
{
    public function __construct(
        private readonly LoyaltyServiceInterface $loyaltyService,
        private readonly CustomerRepository $customerRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FactoryInterface $customerFactory,
        private readonly ObjectManager $customerManager,
        private readonly ObjectManager $orderManager,
        private readonly PromotionCouponEligibilityCheckerInterface $couponEligibilityChecker,
        private readonly DripApiInterface $dripApi,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $email = $request->request->get('email');
        $coupon = $request->request->get('coupon');
        $cartToken = $request->attributes->get('token');

        Assert::notNull($email, $this->translator->trans('app.api.cart.sponsor.error.email_required'));
        Assert::notNull($coupon, $this->translator->trans('app.api.cart.sponsor.error.coupon_required'));

        $verifyCustomerEligibility = $this->loyaltyService->verifyCustomerEligibilityByEmailAndCoupon($email, $coupon);
        Assert::notFalse($verifyCustomerEligibility, $this->translator->trans('app.api.cart.sponsor.error.eligibility'));

        if (!empty($verifyCustomerEligibility['coupon_type'])) {
            $view = View::create($verifyCustomerEligibility);

            return $this->viewHandler->handle($view);
        }

        $promotionCoupon = $this->loyaltyService->verifyCouponIsValid($coupon);
        Assert::notFalse($promotionCoupon, $this->translator->trans('app.api.cart.sponsor.error.validity'));

        /** @var OrderInterface $order */
        $order = $this->orderRepository->getOneByTokenValueHydrated($cartToken);
        Assert::notFalse($this->couponEligibilityChecker->isEligible($order, $promotionCoupon), $this->translator->trans('app.api.cart.sponsor.error.validity'));

        $groupSponsorRecommendee = $this->loyaltyService->verifyGroupExistByCode(CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE);
        Assert::notFalse($groupSponsorRecommendee, $this->translator->trans('app.api.cart.sponsor.error.group', ['code' => CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE]));

        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->findOneByEmail($email);

        if (!$customer) {
            $customer = $this->customerFactory->createNew();
            $customer->setEmail($email);
        }

        $customer->setGroup($groupSponsorRecommendee);

        $this->customerManager->persist($customer);
        $this->customerManager->flush();

        $order->setCustomer($customer);

        $this->orderManager->persist($order);
        $this->orderManager->flush();

        $this->dripApi->sendCustomer($customer, [CustomerInterface::TAG_FILLEUL]);

        $view = View::create(['coupon_type' => 'sponsor']);

        return $this->viewHandler->handle($view);
    }
}
