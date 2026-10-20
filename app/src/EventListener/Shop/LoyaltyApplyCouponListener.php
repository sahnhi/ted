<?php

namespace App\EventListener\Shop;

use App\Doctrine\Entity\Sylius\CustomerGroupInterface;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Services\Drip\DripApiInterface;
use App\Services\Loyalty\LoyaltyServiceInterface;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionCouponEligibilityCheckerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Webmozart\Assert\Assert;

final class LoyaltyApplyCouponListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LoyaltyServiceInterface $loyaltyService,
        private readonly RepositoryInterface $customerRepository,
        private readonly FactoryInterface $customerFactory,
        private readonly ObjectManager $customerManager,
        private readonly PromotionCouponEligibilityCheckerInterface $couponEligibilityChecker,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly SessionInterface $session,
        private readonly DripApiInterface $dripApi
    ) {
    }

    public function apply(GenericEvent $event)
    {
        /** @var OrderInterface $order */
        $order = $event->getSubject();

        Assert::isInstanceOf($order, OrderInterface::class);

        if (!$this->getRequest()->request->get('sylius_cart')['loyaltyCoupon']) {
            return;
        }

        $loyaltyCoupon = $this->getRequest()->request->get('sylius_cart')['loyaltyCoupon'];

        $email = $loyaltyCoupon['email'];
        $coupon = $loyaltyCoupon['coupon'];

        if (empty($email) || empty($coupon)) {
            return;
        }

        $verifyCustomerEligibility = $this->loyaltyService->verifyCustomerEligibilityByEmailAndCoupon($email, $coupon);

        if (!$verifyCustomerEligibility) {
            return $this->fllashErrorMessage("Vous n'êtes pas éligible à l'offre de parrainage.");
        }

        /** @var PromotionCouponInterface $promotionCoupon */
        if (!$promotionCoupon = $this->loyaltyService->verifyCouponIsValid($coupon)) {
            return $this->fllashErrorMessage("Ce coupon n'est pas valide.");
        }

        $verifyCouponEligibility = $this->couponEligibilityChecker->isEligible($order, $promotionCoupon);

        if (!$verifyCouponEligibility) {
            return $this->fllashErrorMessage("Ce coupon n'est pas valide.");
        }

        if (!$groupSponsorRecommendee = $this->loyaltyService->verifyGroupExistByCode(CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE)) {
            throw new \Exception(sprintf("Group with code %s doesn't exist.", CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE));
        }

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

        $order->setPromotionCoupon($promotionCoupon);

        $this->orderProcessor->process($order);

        /** @var FlashBagInterface $flashBag */
        $flashBag = $this->session->getBag('flashes');
        $flashBag->add('success', 'Le code parrain a bien été appliqué.');

        $this->dripApi->sendCustomer($customer, [CustomerInterface::TAG_FILLEUL]);
    }

    protected function fllashErrorMessage(string $message): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $this->session->getBag('flashes');
        $flashBag->add('error', $message);

        $referer = $this->getRequest()->headers->get('referer');

        return new RedirectResponse($referer);
    }

    protected function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
