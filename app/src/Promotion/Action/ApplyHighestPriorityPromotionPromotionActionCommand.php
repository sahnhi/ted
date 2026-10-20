<?php

declare(strict_types=1);

namespace App\Promotion\Action;

use App\Doctrine\Repository\Sylius\PromotionRepositoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Action\PromotionApplicatorInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionEligibilityCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;

class ApplyHighestPriorityPromotionPromotionActionCommand implements PromotionActionCommandInterface
{
    public const TYPE = 'apply_highest_priority_promotion';

    public function __construct(
        private readonly PromotionRepositoryInterface $promotionRepository,
        private readonly PromotionApplicatorInterface $promotionApplicator,
        private readonly PromotionEligibilityCheckerInterface $promotionEligibilityChecker,
    ) {
    }

    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnexpectedTypeException($subject, OrderInterface::class);
        }

        /** @var Channel $channel */
        $channel = $subject->getChannel();

        $promotions = $this->promotionRepository->findActiveByCodeAndChannel($configuration['promotions'], $channel, false);
        foreach ($promotions as $promotionToApply) {
            if ($this->promotionEligibilityChecker->isEligible($subject, $promotionToApply)) {
                $this->promotionApplicator->apply($subject, $promotionToApply);

                $subject->addPromotion($promotionToApply);

                return true;
            }
        }

        return false;
    }

    public function revert(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): void
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnexpectedTypeException($subject, OrderInterface::class);
        }

        /** @var Channel $channel */
        $channel = $subject->getChannel();

        $promotions = $this->promotionRepository->findActiveByCodeAndChannel($configuration['promotions'], $channel, false);
        foreach ($promotions as $promotionToRevert) {
            if ($subject->hasPromotion($promotionToRevert)) {
                $this->promotionApplicator->revert($subject, $promotionToRevert);

                $subject->removePromotion($promotionToRevert);
            }
        }
    }
}
