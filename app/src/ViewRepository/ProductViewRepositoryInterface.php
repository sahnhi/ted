<?php

namespace App\ViewRepository;

use App\View\ProductListReviewQuestionListView;
use App\View\ProductListView;
use App\View\ProductView;
use Doctrine\ORM\NonUniqueResultException;

interface ProductViewRepositoryInterface
{
    public function findAllByChannel(string $channelCode, bool $bounds = false): ProductListView;

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByCode(string $channelCode, string $code, string $localeCode, bool $withVariants = false): ProductView;

    public function findAllWithReviewRequested(string $channel, string $locale, array $productCodes, array $questionsAnswered): ProductListReviewQuestionListView;
}
