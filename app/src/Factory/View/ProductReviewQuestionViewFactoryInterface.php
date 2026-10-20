<?php

namespace App\Factory\View;

use App\Doctrine\Entity\ProductReviewQuestion;
use App\View\ProductReviewQuestionView;
use Sylius\Component\Core\Model\ChannelInterface;

interface ProductReviewQuestionViewFactoryInterface
{
    public function create(ProductReviewQuestion $question, ChannelInterface $channel, string $localeCode): ProductReviewQuestionView;
}
