<?php

namespace App\Services\Review\Seek;

use App\Doctrine\Entity\AskReviewTemplate;
use App\Services\Review\Seek\Seeker\NotFoundException;
use App\Services\Review\Seek\Seeker\TrustPilot;
use App\Services\Review\Seek\Seeker\VerifiedReviews;

class SeekerFactory
{
    /** @var AbstractSeeker[] */
    private array $seekers;

    public function __construct(
        private readonly TrustPilot $trustPilot,
        private readonly VerifiedReviews $verifiedReviews
    ) {
        $this->seekers = [
            TrustPilot::NAME => $this->trustPilot,
            VerifiedReviews::NAME => $this->verifiedReviews,
        ];
    }

    /**
     * @throws NotFoundException
     */
    public function get(AskReviewTemplate $askReviewTemplate): AbstractSeeker
    {
        if (!array_key_exists($askReviewTemplate->getServiceName(), $this->seekers)) {
            throw new NotFoundException($askReviewTemplate);
        }

        return $this->seekers[$askReviewTemplate->getServiceName()]
            ->setAskReviewTemplate($askReviewTemplate);
    }
}
