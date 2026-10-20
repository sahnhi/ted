<?php

namespace App\View;

final class ProductReviewView
{
    public string $reviewId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $rating;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var \DateTime
     */
    public $date;

    /**
     * @var \DateTime
     */
    public $orderDate;

    /**
     * @var string
     */
    public $city;

    /**
     * @var int
     */
    public $age;

    /**
     * @var string
     */
    public $orderId;

    /**
     * @var bool
     */
    public $verified;

    /**
     * @var bool
     */
    public $translated;

    /**
     * @var string
     */
    public $savAnswer;

    /**
     * @var array
     */
    public $answers = [];

    /**
     * @var array
     */
    public $reviewer = [];

    /**
     * @var string
     */
    public $url;

    /**
     * @var array
     */
    public $ratings = [];

    /**
     * @var array
     */
    public $products = [];

    public function getTimestamp()
    {
        return $this->date;
    }

    public function getContent()
    {
        return $this->comment;
    }
}
