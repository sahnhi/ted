<?php

declare(strict_types=1);

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\AskReviewTemplate;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface AskReviewTemplateRepositoryInterface extends RepositoryInterface
{
    public function getTemplateFromServiceName(string $serviceName): ?AskReviewTemplate;
}
