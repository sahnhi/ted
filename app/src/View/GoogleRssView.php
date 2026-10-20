<?php

namespace App\View;

final class GoogleRssView
{
    /**
     * @var string
     */
    public $version;

    /**
     * @var string[]
     */
    public $xsi;

    /**
     * @var array
     */
    public $publisher = [];

    /**
     * @var array|ProductReviewView
     */
    public $reviews = [];
}
