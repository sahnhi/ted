<?php

namespace App\Factory\View;

use App\View\GoogleRssView;
use Doctrine\ORM\EntityManagerInterface;

final class GoogleRssViewFactory implements GoogleRssViewFactoryInterface
{
    public function __construct(
        private readonly ProductReviewViewFactoryInterface $productReviewViewFactory,
        private readonly EntityManagerInterface $productReviewManager
    ) {
    }

    public function create(iterable $productReviews): array
    {
        $view = new GoogleRssView();

        $view->version = '2.3';
        $view->xsi = ['xsi:noNamespaceSchemaLocation' => 'http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd'];
        $view->publisher = ['name' => 'Tediber'];

        $count = 0;

        foreach ($productReviews as $review) {
            $view->reviews[] = $this->productReviewViewFactory->create($review);
            $this->productReviewManager->clear();

            ++$count;
        }

        return [$count, $view];
    }
}
