<?php

namespace App\Controller\Api\Sponsor;

use App\Controller\Api\Drip\AbstractDripAction;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Doctrine\Entity\Sylius\PromotionInterface;
use App\Doctrine\Repository\PromotionRuleRepository;
use App\Promotion\Checker\Rule\SponsoringRuleChecker;
use Doctrine\Persistence\ObjectManager;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Promotion\Generator\PromotionCouponGeneratorInstruction;
use Sylius\Component\Promotion\Generator\PromotionCouponGeneratorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class GiftAddAction extends AbstractDripAction
{
    public function __construct(
        string $token,
        private readonly RepositoryInterface $customerRepository,
        private readonly PromotionRuleRepository $promotionRuleRepository,
        private readonly ObjectManager $customerManager,
        private readonly PromotionCouponGeneratorInterface $promotionCouponGenerator,
        private readonly ViewHandlerInterface $viewHandler
    ) {
        parent::__construct($token);
    }

    public function __invoke(Request $request): Response
    {
        $email = $request->query->get('email');
        $token = $request->query->get('token');

        $this->authenticate($token);

        Assert::notNull($email, 'Email required.');

        /** @var CustomerInterface $customer */
        if (!$customer = $this->customerRepository->findOneByEmail($email)) {
            throw new \Exception("User doesn't exist.");
        }

        $nthFilleul = $this->customerRepository->countNbFilleul($customer);

        if (0 === $nthFilleul) {
            throw new \Exception('This godfather has no godchild.');
        }

        if (!$promotionParrain = $this->promotionRuleRepository->findOneByNthFilleul($nthFilleul)) {
            throw new \Exception(sprintf("Promotion with %s nth doesn't exist.", $nthFilleul));
        }

        /** @var PromotionInterface $promotionParrain */
        $promotionParrain = $promotionParrain->getPromotion();

        $coupon = false;

        /** @var PromotionCouponInterface $promotionCoupon */
        foreach ($customer->getPromotionCoupons() as $promotionCoupon) {
            foreach ($promotionCoupon->getPromotion()->getRules() as $promotionRule) {
                if (SponsoringRuleChecker::TYPE === $promotionRule->getType()) {
                    if ($promotionRule->getConfiguration()['nth'] === $nthFilleul) {
                        $coupon = $promotionCoupon;
                    }
                }
            }
        }

        if (!$coupon) {
            $promotionParrainExpiresAt = new \DateTime();
            $promotionParrainExpiresAt->modify('+1 month');

            $promotionParrainInstruction = new PromotionCouponGeneratorInstruction();
            $promotionParrainInstruction->setAmount(1);
            $promotionParrainInstruction->setCodeLength(10);
            $promotionParrainInstruction->setExpiresAt($promotionParrainExpiresAt);
            $promotionParrainInstruction->setUsageLimit(1);
            // TODO : update Sylius for add setPrefix method

            $coupon = $this->promotionCouponGenerator->generate($promotionParrain, $promotionParrainInstruction);
            $coupon = current($coupon); // Get first coupon generated only

            $customer->addPromotionCoupon($coupon);

            $this->customerManager->persist($customer);
            $this->customerManager->flush();
        }

        $view = View::create([
            'coupon' => $coupon->getCode(),
        ]);

        return $this->viewHandler->handle($view);
    }
}
