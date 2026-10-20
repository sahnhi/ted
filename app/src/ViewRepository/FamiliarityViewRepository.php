<?php

namespace App\ViewRepository;

use App\Doctrine\Entity\Familiarity;
use App\Doctrine\Repository\FamiliarityRepositoryInterface;
use App\View\FamiliarityListView;

class FamiliarityViewRepository implements FamiliarityViewRepositoryInterface
{
    public function __construct(
        private readonly FamiliarityRepositoryInterface $familiarityRepository
    ) {
    }

    public function getAvailableByLocaleEnabled(string $locale): FamiliarityListView
    {
        $familiarityQueryBuilder = $this->familiarityRepository->createQueryBuilderByLocaleEnabled($locale);

        /** @var Familiarity $familiarity */
        $familiarities = $familiarityQueryBuilder->getQuery()->getResult();

        $view = new FamiliarityListView();
        foreach ($familiarities as $familiarity) {
            $view->items[] = [
                'id' => $familiarity->getId(),
                'code' => $familiarity->getCode(),
                'name' => $familiarity->getWording(),
            ];
        }

        return $view;
    }
}
