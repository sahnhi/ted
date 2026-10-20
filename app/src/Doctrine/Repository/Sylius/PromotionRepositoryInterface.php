<?php

declare(strict_types=1);

namespace App\Doctrine\Repository\Sylius;

use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Repository\PromotionRepositoryInterface as BasePromotionRepositoryInterface;

interface PromotionRepositoryInterface extends BasePromotionRepositoryInterface
{
    public function findActiveByCodeAndChannel(array $codes, Channel $channel, bool $isAppliedAutomatically = true): iterable;
}
