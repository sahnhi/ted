<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Pack;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Core\Model\ChannelInterface;

interface PackRepositoryInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneBySlug(ChannelInterface $channel, string $localeCode, string $slug): ?Pack;

    /**
     * @return Pack[]
     */
    public function findAllByVariant(ChannelInterface $channel, string $localeCode, string $slug): array;
}
