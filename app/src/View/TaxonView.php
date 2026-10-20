<?php

namespace App\View;

class TaxonView
{
    /** @var string */
    public $code;
    /** @var string */
    public $name;
    /** @var string */
    public $slug;
    /** @var string */
    public $description;
    /** @var int */
    public $position;
    /** @var array */
    public $children = [];
    /** @var array */
    public $images = [];
    /** @var float */
    public $averageRating;
    /** @var int */
    public $numberOfRating;
    /** @var int */
    public $minBound;
    /** @var int */
    public $maxBound;
    /** @var array */
    public $products;
}
