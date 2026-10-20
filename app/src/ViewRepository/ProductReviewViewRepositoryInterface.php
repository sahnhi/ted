<?php

namespace App\ViewRepository;

use App\Model\PaginatorDetails;
use App\View\GoogleRssView;
use App\View\PageView;

interface ProductReviewViewRepositoryInterface
{
    public function getByProductCode(string $productCode, string $channelCode, PaginatorDetails $paginatorDetails, ?string $localeCode, ?array $filter, ?array $allQuestions): PageView;

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getReviewsByTaxon(string $taxonSlug, string $channelCode, PaginatorDetails $paginatorDetails, ?string $localeCode, ?array $filter): PageView;

    /**
     * @return array<int, GoogleRssView>
     */
    public function getAllActivatedReviews(int $index = 0, int $limit = 2000): array;
}
