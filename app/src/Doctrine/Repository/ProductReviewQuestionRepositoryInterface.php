<?php

declare(strict_types=1);

namespace App\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface ProductReviewQuestionRepositoryInterface extends RepositoryInterface
{
    public function findQuestionsByProductId(string $productId): QueryBuilder;
}
