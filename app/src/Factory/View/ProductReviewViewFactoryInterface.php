<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ProductReview;
use App\View\ProductReviewView;

interface ProductReviewViewFactoryInterface
{
    public function create(ProductReview $productReview): ProductReviewView;
}
