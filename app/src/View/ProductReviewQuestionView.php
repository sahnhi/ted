<?php

namespace App\View;

use App\Doctrine\Entity\ProductReviewAnswerChoices;

class ProductReviewQuestionView
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $questionType;

    /**
     * @var int
     */
    public $position;

    /**
     * @var ProductReviewAnswerChoices
     */
    public $answers = [];
}
