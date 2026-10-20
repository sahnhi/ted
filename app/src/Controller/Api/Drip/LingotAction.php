<?php

namespace App\Controller\Api\Drip;

use App\Doctrine\Repository\PromotionRuleRepository;
use App\Services\Admin\CustomerService;
use App\Services\Drip\DripApiInterface;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Component\Promotion\Generator\PromotionCouponGeneratorInstruction;
use Sylius\Component\Promotion\Generator\PromotionCouponGeneratorInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class LingotAction extends AbstractDripAction
{
    private const PROMOTION_TYPE = 'LINGOT';

    public function __construct(
        string $token,
        private readonly PromotionRuleRepository $promotionRuleRepository,
        private readonly PromotionCouponGeneratorInterface $promotionCouponGenerator,
        private readonly DripApiInterface $dripApi,
        private readonly CustomerService $customerService,
        private readonly ReportingInterface $reporting
    ) {
        parent::__construct($token);
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->get('token');
        $email = $request->get('email');
        $lingot = $request->get('lingot');

        try {
            Assert::allNotNull([$token, $email, $lingot], 'Token, Lingot and Email cannot be null');
            $this->authenticate($token);

            $customer = $this->customerService->getCustomerByEmailOrCreate($email);

            /** @var PromotionRuleInterface $rule */
            $rule = $this->promotionRuleRepository->findOneByLingot($lingot);
            Assert::notNull($rule, 'Promotion not found');

            $promotion = $rule->getPromotion();

            $duration = $rule->getConfiguration()['duration'] ?? 2;

            $date = new \DateTime();
            $date->add(new \DateInterval('P' . $duration . 'D'));

            $promotionInstruction = new PromotionCouponGeneratorInstruction();
            $promotionInstruction->setAmount(1);
            $promotionInstruction->setCodeLength(10);
            $promotionInstruction->setExpiresAt($date);
            $promotionInstruction->setUsageLimit(1);
            $promotionInstruction->setPrefix(self::PROMOTION_TYPE);

            $coupons = $this->promotionCouponGenerator->generate($promotion, $promotionInstruction);
            Assert::notEmpty($coupons, 'Coupon could not be generated');

            $coupon = current($coupons);
            $this->dripApi->sendCustomer($customer, [], [
                $lingot => $coupon->getCode(),
            ]);

            return new JsonResponse(['code' => $coupon->getCode()]);
        } catch (\Exception $exception) {
            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                [
                    'lingot_add_token' => $token,
                    'lingot_add_email' => $email,
                ]
            );

            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
