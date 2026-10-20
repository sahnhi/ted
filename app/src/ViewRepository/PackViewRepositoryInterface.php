<?php

namespace App\ViewRepository;

use App\View\PackListView;
use App\View\PackView;
use Doctrine\ORM\NonUniqueResultException;

interface PackViewRepositoryInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneByChannelAndCode(string $channelCode, string $packCode): PackView;

    public function listByChannelAndVariant(string $channelCode, string $variant): PackListView;
}
