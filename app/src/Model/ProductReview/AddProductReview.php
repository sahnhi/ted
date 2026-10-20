<?php

declare(strict_types=1);

namespace App\Model\ProductReview;

use Sylius\Bundle\ApiBundle\Command\AddProductReview as BaseAddProductReview;

class AddProductReview extends BaseAddProductReview
{
    public function __construct(
        ?string $title,
        ?int $rating,
        ?string $comment,
        string $productCode,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $cartToken,
        public readonly array $answers,
        public readonly \DateTime|null $birthDay,
        ?string $email = null,
    ) {
        parent::__construct($title, $rating, $comment, $productCode, $email);
    }
}
