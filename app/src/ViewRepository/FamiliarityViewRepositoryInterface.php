<?php

namespace App\ViewRepository;

use App\View\FamiliarityListView;

interface FamiliarityViewRepositoryInterface
{
    public function getAvailableByLocaleEnabled(string $locale): FamiliarityListView;
}
