<?php

namespace App\Services\Review\Seek\Seeker;

use App\Doctrine\Entity\AskReviewTemplate;

class NotFoundException extends \Exception
{
    public function __construct(AskReviewTemplate $askReviewTemplate)
    {
        parent::__construct('Review seeker not found for code ' . $askReviewTemplate->getServiceName());
    }
}
