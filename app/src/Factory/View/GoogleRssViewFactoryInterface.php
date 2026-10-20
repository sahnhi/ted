<?php

namespace App\Factory\View;

use App\View\GoogleRssView;

interface GoogleRssViewFactoryInterface
{
    /**
     * @return GoogleRssView
     */
    public function create(iterable $productReviews): array;
}
